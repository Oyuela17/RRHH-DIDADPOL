require('dotenv').config();

const express = require('express');
const cors = require('cors');
const multer = require('multer');
const path = require('path');
const fs = require('fs');
const { exec, spawn } = require('child_process');
const { Pool } = require('pg');
const bcrypt = require('bcrypt');
const crypto = require('crypto');
const { v4: uuidv4 } = require('uuid');
const nodemailer = require('nodemailer');
const archiver = require('archiver');
const dayjs = require('dayjs');

const app = express();
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// ===== Requiere (agregar junto a tus const ...) =====
const helmet = require('helmet');
const cookieParser = require('cookie-parser');
const rateLimit = require('express-rate-limit');

// ===== Middlewares (después de tus app.use(cors/json/urlencoded)) =====
app.use(helmet());
app.use(cookieParser());

// Rate limit básico para rutas sensibles (login, 2FA, recuperación)
const authLimiter = rateLimit({
  windowMs: 10 * 60 * 1000, // 10 min
  max: 100,                 // 100 req por IP/ventana
  standardHeaders: true,
  legacyHeaders: false
});
app.use(['/api/2fa/start','/api/2fa/verify','/api/2fa/resend','/api/recuperar-contrasena'], authLimiter);

// =========================
// 🗄️ PostgreSQL (Render)
// =========================

const pool = new Pool({
  host: process.env.DB_HOST,           // tomado del entorno Render
  port: process.env.DB_PORT,           // 5432
  user: process.env.DB_USER,           // rrhh_didadpol_db_user
  password: process.env.DB_PASSWORD,   // tu password Render
  database: process.env.DB_NAME,       // rrhh_didadpol_db
  ssl: {
    rejectUnauthorized: false,         // obligatorio en Render
  },
});

pool.connect()
  .then(() => console.log('✅ Conectado a PostgreSQL Render'))
  .catch(err => console.error('❌ Error al conectar a PostgreSQL:', err));



// =========================
// GET: Mostrar registros de bitácora (versión minimalista)
// =========================
app.get('/api/bitacora', async (req, res) => {
  try {
    const query = `
      SELECT 
        fecha, 
        usuario_nombre, 
        accion, 
        tabla, 
        descripcion, 
        ip_origen
      FROM public.bitacora
      ORDER BY fecha DESC;
    `;

    const { rows } = await pool.query(query);
    res.json(rows);
  } catch (error) {
    console.error('Error al obtener la bitácora:', error);
    res.status(500).json({ error: 'Error al obtener los registros de la bitácora' });
  }
});


// =========================
// Subidas (multer)
// =========================
const UPLOADS_DIR = path.join(__dirname, 'uploads');
try { fs.mkdirSync(UPLOADS_DIR, { recursive: true }); } catch {}
const storage = multer.diskStorage({
  destination: (_req, _file, cb) => cb(null, UPLOADS_DIR),
  filename: (_req, file, cb) => cb(null, Date.now() + '-' + file.originalname),
});
const upload = multer({ storage });


// =========================
// Auth simple para backups
// =========================
function backupsAuth(req, res, next) {
  const token = req.header('X-Admin-Token');
  if (!token || token !== (process.env.ADMIN_TOKEN || '')) {
    return res.status(401).json({ error: 'Unauthorized' });
  }
  next();
}

// =========================
// RESTORE (usar archivo existente)
// =========================
app.post('/api/restore/use', backupsAuth, async (req, res) => {
  const filePath = String(req.body?.path || '').trim();
  if (!filePath) return res.status(400).json({ error: 'path requerido' });

  try {
    const result = await restoreFromFile(filePath);
    return res.json({ ok: true, restored_from: filePath, ...result });
  } catch (e) {
    return res.status(500).json({ error: 'Falló restauración', detail: String(e?.message || e), logs: e?.logs || null });
  }
});

// =========================
// RESTORE (subiendo archivo)
// =========================
const uploadRestore = multer({ dest: path.join(__dirname, 'tmp') });
app.post('/api/restore/upload', backupsAuth, uploadRestore.single('file'), async (req, res) => {
  if (!req.file) return res.status(400).json({ error: 'Archivo requerido' });
  try {
    const result = await restoreFromFile(req.file.path, req.file.originalname);
    return res.json({ ok: true, uploaded: req.file.originalname, ...result });
  } catch (e) {
    return res.status(500).json({ error: 'Falló restauración', detail: String(e?.message || e), logs: e?.logs || null });
  } finally {
    try { fs.unlinkSync(req.file.path); } catch {}
  }
});

// =========================
// Helper: restaurar (zip/sql/dump)
// =========================
async function restoreFromFile(inputPath, originalName) {
  const isZip = (originalName || inputPath).toLowerCase().endsWith('.zip');

 const dbHost = process.env.DB_HOST || 'dpg-d3ned2ur433s73bgn4tg-a';
 const dbPort = Number(process.env.DB_PORT) || 5432;
 const dbUser = process.env.DB_USER || 'rrhh_didadpol_db_user';
 const dbName = process.env.DB_NAME || 'rrhh_didadpol_db';
 const dbPass = process.env.DB_PASSWORD || 'aqJ8I5Y5wpEN7am4z5niHCeO69pqyyk7';

  const psqlBin = process.env.PSQL_PATH
    ? `"${String(process.env.PSQL_PATH).replace(/"/g, '')}"`
    : 'psql';
  const pgRestoreBin = process.env.PG_RESTORE_PATH || 'pg_restore';

  const execCmd = (cmd) => new Promise((ok, bad) => {
    exec(cmd, { env: { ...process.env, PGPASSWORD: dbPass } }, (err, stdout, stderr) => {
      if (err) return bad({ err, stdout, stderr });
      ok({ stdout, stderr });
    });
  });

  async function runSqlFile(filePath) {
    const cmd = `${psqlBin} -h ${dbHost} -p ${dbPort} -U ${dbUser} -d ${dbName} -v ON_ERROR_STOP=1 -f "${filePath}"`;
    return execCmd(cmd);
  }

  async function runDumpFile(filePath) {
    const cmd = `${pgRestoreBin} -h ${dbHost} -p ${dbPort} -U ${dbUser} ` +
                `--clean --if-exists --no-owner --no-privileges -d ${dbName} "${filePath}"`;
    return execCmd(cmd);
  }

  async function disableConstraints() {
    const sql = `DO $$
BEGIN
  EXECUTE 'SET session_replication_role = replica';
END $$;`;
    const tmp = path.join(__dirname, 'tmp', `disable_fk_${Date.now()}.sql`);
    fs.mkdirSync(path.dirname(tmp), { recursive: true });
    fs.writeFileSync(tmp, sql);
    try { await runSqlFile(tmp); } finally { try { fs.unlinkSync(tmp); } catch {} }
  }

  async function enableConstraints() {
    const sql = `DO $$
BEGIN
  EXECUTE 'SET session_replication_role = DEFAULT';
END $$;`;
    const tmp = path.join(__dirname, 'tmp', `enable_fk_${Date.now()}.sql`);
    fs.mkdirSync(path.dirname(tmp), { recursive: true });
    fs.writeFileSync(tmp, sql);
    try { await runSqlFile(tmp); } finally { try { fs.unlinkSync(tmp); } catch {} }
  }

  const useForce = (String(process.env.RESTORE_FORCE || '').toLowerCase() === 'true');

  // === No es ZIP ===
  if (!isZip) {
    const lower = inputPath.toLowerCase();
    try {
      if (useForce) await disableConstraints();

      let out;
      if (lower.endsWith('.dump') || lower.endsWith('.backup')) {
        out = await runDumpFile(inputPath);
      } else if (lower.endsWith('.sql')) {
        out = await runSqlFile(inputPath);
      } else {
        throw new Error('Formato no soportado. Usa .sql, .dump/.backup o .zip.');
      }

      if (useForce) await enableConstraints();
      return { ok: true, logs: [out] };
    } catch (e) {
      if (useForce) { try { await enableConstraints(); } catch {} }
      const detail = e.stderr || String(e.err || e);
      const err = new Error(`Error al restaurar: ${detail}`);
      err.logs = [{ stderr: detail }];
      throw err;
    }
  }

  // === Es ZIP: extraer y ejecutar TODO ===
  const unzipDir = path.join(__dirname, 'tmp', 'restore_' + Date.now());
  fs.mkdirSync(unzipDir, { recursive: true });
  await new Promise((ok, bad) => {
    const unzip = require('unzipper').Extract({ path: unzipDir });
    fs.createReadStream(inputPath).pipe(unzip).on('close', ok).on('error', bad);
  });

  const walk = (d) => fs.readdirSync(d, { withFileTypes: true })
    .flatMap(de => de.isDirectory() ? walk(path.join(d, de.name)) : [path.join(d, de.name)]);
  const all = walk(unzipDir);

  const sqlFiles  = all.filter(p => p.toLowerCase().endsWith('.sql'));
  const dumpFiles = all.filter(p => {
    const l = p.toLowerCase();
    return l.endsWith('.dump') || l.endsWith('.backup');
  });

  if (!sqlFiles.length && !dumpFiles.length) {
    try { fs.rmSync(unzipDir, { recursive: true, force: true }); } catch {}
    throw new Error('ZIP sin .sql ni .dump');
  }

  // Ordenar: primero estructura (si los nombras con schema/estructura/01), luego data
  sqlFiles.sort((a, b) => {
    const key = (p) => (/(schema|estructura|create|01)/i.test(p) ? 'a' : 'z') + p.toLowerCase();
    return key(a).localeCompare(key(b));
  });

  const logs = [];
  try {
    if (useForce) await disableConstraints();

    // 1) dumps primero
    for (const f of dumpFiles) {
      try {
        const out = await runDumpFile(f);
        logs.push({ file: f, ok: true, ...out });
      } catch (e) {
        const detail = e.stderr || String(e.err || e);
        logs.push({ file: f, ok: false, stderr: detail });
        const err = new Error(`pg_restore falló en ${path.basename(f)}`);
        err.logs = logs;
        throw err;
      }
    }

    // 2) luego todos los .sql
    for (const f of sqlFiles) {
      try {
        const out = await runSqlFile(f);
        logs.push({ file: f, ok: true, ...out });
      } catch (e) {
        const detail = e.stderr || String(e.err || e);
        logs.push({ file: f, ok: false, stderr: detail });
        const err = new Error(`psql falló en ${path.basename(f)}`);
        err.logs = logs;
        throw err;
      }
    }
  } finally {
    if (useForce) await enableConstraints();
    try { fs.rmSync(unzipDir, { recursive: true, force: true }); } catch {}
  }

  return { ok: true, logs };
}

// =========================
// LISTAR Backups (con nombre de usuario)
// =========================
app.get('/api/backups', backupsAuth, async (_req, res) => {
  try {
    const { rows } = await pool.query(`
      SELECT
        b.id,
        b.nombre_archivo,
        b.ruta_archivo,
        b.fecha,
        b.usuario_id,
        COALESCE(u.name::text, CONCAT('#', b.usuario_id::text)) AS usuario_nombre,
        b.tipo_backup,
        b.tamano,
        b.estado
      FROM public.backup b
      LEFT JOIN public.users u ON u.id = b.usuario_id
      ORDER BY b.fecha DESC
    `);
    res.json(rows);
  } catch (e) {
    console.error('Error al listar backups:', e);
    res.status(500).json({ error: 'No se pudo listar', detail: String(e) });
  }
});

// =========================
// DESCARGAR Backup por id
// =========================
app.get('/api/backups/:id/download', backupsAuth, async (req, res) => {
  try {
    const { rows } = await pool.query(`SELECT * FROM public.backup WHERE id = $1`, [req.params.id]);
    if (!rows.length) return res.status(404).json({ error: 'No existe' });

    const f = rows[0];
    if (!fs.existsSync(f.ruta_archivo)) return res.status(404).json({ error: 'Archivo no encontrado en disco' });

    return res.download(f.ruta_archivo, f.nombre_archivo);
  } catch (e) {
    res.status(500).json({ error: 'No se pudo descargar', detail: String(e) });
  }
});

// =========================
// ELIMINAR Backup por id
// =========================
app.delete('/api/backups/:id', backupsAuth, async (req, res) => {
  try {
    const { rows } = await pool.query(`SELECT * FROM public.backup WHERE id = $1`, [req.params.id]);
    if (!rows.length) return res.status(404).json({ error: 'No existe' });

    const f = rows[0];
    try {
      if (f.ruta_archivo && fs.existsSync(f.ruta_archivo)) fs.unlinkSync(f.ruta_archivo);
    } catch {}
    await pool.query(`DELETE FROM public.backup WHERE id = $1`, [req.params.id]);

    res.json({ ok: true });
  } catch (e) {
    res.status(500).json({ error: 'No se pudo eliminar', detail: String(e) });
  }
});

// =========================
// CREAR Backup (solo BD) → ZIP con .dump
// Body: { "tipo": "solo_bd", "usuario_id": 1 }
// =========================
app.post('/api/backups', backupsAuth, async (req, res) => {
  const tipo = (req.body && req.body.tipo) ? String(req.body.tipo) : 'solo_bd';
  const usuario_id = (req.body && req.body.usuario_id) ? Number(req.body.usuario_id) : null;

  const BACKUP_DIR = process.env.BACKUP_DIR ||
    (process.platform === 'win32' ? 'C:/backups/miapp' : path.join(__dirname, 'backups'));

  try { fs.mkdirSync(BACKUP_DIR, { recursive: true }); }
  catch (e) { return res.status(500).json({ error: 'No se pudo crear BACKUP_DIR', detail: String(e) }); }

  const ts = dayjs().format('YYYYMMDD_HHmmss');
  const zipName = `backup_${tipo}_${ts}.zip`;
  const zipPath = path.join(BACKUP_DIR, zipName);
  const tmpDump = path.join(BACKUP_DIR, `db_${ts}.dump`); // formato custom

const dbHost = process.env.DB_HOST || 'dpg-d3ned2ur433s73bgn4tg-a';
const dbPort = Number(process.env.DB_PORT) || 5432;
const dbUser = process.env.DB_USER || 'rrhh_didadpol_db_user';
const dbName = process.env.DB_NAME || 'rrhh_didadpol_db';
const dbPass = process.env.DB_PASSWORD || 'aqJ8I5Y5wpEN7am4z5niHCeO69pqyyk7';

  const pgDumpBin = process.env.PG_DUMP_PATH || 'pg_dump';

  // Args → custom format (mejor para pg_restore)
  const extraArgs = (process.env.PG_DUMP_EXTRA_ARGS || '').trim();
  const args = [
    '-h', dbHost,
    '-p', dbPort,
    '-U', dbUser,
    '-F', 'c',            // formato custom
    '--no-owner',
    '--no-privileges',
    ...(extraArgs ? extraArgs.split(' ').filter(Boolean) : []),
    dbName,
  ];

  const outStream = fs.createWriteStream(tmpDump);
  let stderrBuf = '';

  const child = spawn(pgDumpBin, args, {
    env: { ...process.env, PGPASSWORD: dbPass },
    stdio: ['ignore', 'pipe', 'pipe'],
  });

  child.stdout.pipe(outStream);
  child.stderr.on('data', (c) => { stderrBuf += c.toString(); });

  child.on('error', async (err) => {
    try {
      await pool.query(
        `INSERT INTO public.backup(nombre_archivo, ruta_archivo, fecha, usuario_id, tipo_backup, estado)
         VALUES ($1,$2,NOW(),$3,$4,'fallido')`,
        [zipName, zipPath, usuario_id, tipo]
      );
    } catch {}
    return res.status(500).json({ error: 'No se pudo invocar pg_dump', detail: String(err) });
  });

  child.on('close', async (code) => {
    outStream.close();

    if (code !== 0) {
      try {
        await pool.query(
          `INSERT INTO public.backup(nombre_archivo, ruta_archivo, fecha, usuario_id, tipo_backup, estado)
           VALUES ($1,$2,NOW(),$3,$4,'fallido')`,
          [zipName, zipPath, usuario_id, tipo]
        );
      } catch {}
      return res.status(500).json({ error: 'Falló pg_dump', detail: stderrBuf || `exit code ${code}` });
    }

    // 2) Comprimir a ZIP
    const output = fs.createWriteStream(zipPath);
    const archive = archiver('zip', { zlib: { level: 9 } });

    output.on('close', async () => {
      try {
        const stats = fs.statSync(zipPath);
        await pool.query(
          `INSERT INTO public.backup(nombre_archivo, ruta_archivo, fecha, usuario_id, tipo_backup, tamano, estado)
           VALUES ($1,$2,NOW(),$3,$4,$5,'listo')`,
          [zipName, zipPath, usuario_id, tipo, stats.size]
        );
      } catch (e) {
        return res.status(500).json({ error: 'ZIP creado, pero no se pudo registrar en DB', detail: String(e) });
      } finally {
        try { fs.unlinkSync(tmpDump); } catch {}
      }
      return res.json({ ok: true, file: zipName, path: zipPath });
    });

    archive.on('error', async (e) => {
      try {
        await pool.query(
          `INSERT INTO public.backup(nombre_archivo, ruta_archivo, fecha, usuario_id, tipo_backup, estado)
           VALUES ($1,$2,NOW(),$3,$4,'fallido')`,
          [zipName, zipPath, usuario_id, tipo]
        );
      } catch {}
      return res.status(500).json({ error: 'Falló compresión de backup', detail: String(e) });
    });

    archive.pipe(output);
    // Agrega el dump en formato custom
    archive.file(tmpDump, { name: `db_${ts}.dump` });

    // Si más adelante agregas un PDF/HTML "como la vista", lo añades aquí:
    // archive.file(rutaPdf, { name: `reporte_${ts}.pdf` });

    archive.finalize();
  });
});

// =========================
// Health
// =========================
app.get('/health', (_req, res) => res.json({ ok: true, at: new Date().toISOString() }));


// 🔧 Quitar acentos
const quitarAcentos = (texto) => {
  return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/Ñ/g, "N");
};

// ==========================
// CONFIG GLOBAL (fuera del handler)
// ==========================

// Base del FRONT (Laravel) para construir el link
const WEB_BASE_URL =
  process.env.WEB_BASE_URL ||
  process.env.FRONTEND_URL ||
  'https://rrhh-didadpol-main-khmtlb.laravel.cloud';

console.log('[ENV] WEB_BASE_URL =', WEB_BASE_URL);

// Helper: normalizar correo
function normalizeEmail(email) {
  return String(email || '').trim().toLowerCase();
}

// ==========================
// REGISTRAR USUARIO (personal o institucional)
// ==========================
app.post('/api/registrar-usuario', async (req, res) => {
  const {
    nombre_completo,
    correo_personal,
    cod_persona,
    usar_correo_institucional = true, // bandera desde el front
  } = req.body;

  if (!nombre_completo || !correo_personal || !cod_persona) {
    return res.status(400).json({
      error: 'Todos los campos son requeridos: nombre_completo, correo_personal y cod_persona',
    });
  }

  const nombre = String(nombre_completo).trim();
  const correoPersonal = normalizeEmail(correo_personal);
  const ahora = new Date().toISOString();

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correoPersonal)) {
    return res.status(400).json({ error: 'El correo personal no tiene un formato válido.' });
  }

  const client = await pool.connect();
  try {
    await client.query('BEGIN');

    // 1) Verificar si la persona ya tiene usuario
    const existePersona = await client.query(
      'SELECT id FROM users WHERE cod_persona = $1 LIMIT 1',
      [cod_persona]
    );
    if (existePersona.rowCount > 0) {
      await client.query('ROLLBACK');
      return res
        .status(409)
        .json({ error: `La persona con código ${cod_persona} ya tiene un usuario registrado.` });
    }

    // 2) Determinar correo final
    let correoFinal;
    if (usar_correo_institucional) {
      // usa el mismo client (transacción)
      correoFinal = await generarCorreoInstitucional(nombre, client);
    } else {
      // Validar duplicado de correo personal
      const existeCorreo = await client.query(
        'SELECT id FROM users WHERE LOWER(email) = LOWER($1) LIMIT 1',
        [correoPersonal]
      );
      if (existeCorreo.rowCount > 0) {
        await client.query('ROLLBACK');
        return res.status(409).json({
          error: `El correo ${correoPersonal} ya está registrado.`,
        });
      }
      correoFinal = correoPersonal;
    }

    // 3) Insertar usuario (sin contraseña aún)
    const nuevoUsuario = await client.query(
      `INSERT INTO users (name, email, password, created_at, updated_at, cod_persona)
       VALUES ($1, $2, $3, $4, $4, $5)
       RETURNING id`,
      [nombre, correoFinal.toLowerCase(), '', ahora, cod_persona]
    );
    const userId = nuevoUsuario.rows[0].id;

    // 4) Token de definición de contraseña (24h)
    const token = crypto.randomBytes(32).toString('hex');
    const expires = new Date(Date.now() + 24 * 60 * 60 * 1000);

    // Limpiar tokens previos e insertar
    await client.query('DELETE FROM password_tokens WHERE user_id = $1', [userId]);
    await client.query(
      `INSERT INTO password_tokens (user_id, token, expires_at, created_at)
       VALUES ($1, $2, $3, NOW())`,
      [userId, token, expires]
    );

    // 5) Enlace al FRONT
    const link = `${WEB_BASE_URL}/definir-contrasena?token=${encodeURIComponent(
      token
    )}&email=${encodeURIComponent(correoFinal)}`;

    // 6) Plantilla del correo
    const html = `
      <div style="max-width: 600px; margin: auto; border-radius: 8px; overflow: hidden; font-family: Arial, sans-serif;">
        <div style="background-color: #003366; padding: 20px; text-align: center;">
          <h1 style="color: #ffffff; margin: 0; font-size: 24px;">DIDADPOL</h1>
        </div>
        <div style="background-color: #ffffff; padding: 30px; color: #333;">
          <h2 style="color: #003366;">Hola ${nombre}</h2>
          <p style="font-size: 16px;">Has sido registrado(a) en el sistema de Recursos Humanos de <strong>DIDADPOL</strong>.</p>
          <p style="font-size: 16px; margin-top: 15px;">
            <strong>Tu correo de acceso es:</strong>
            <a href="mailto:${correoFinal}" style="color: #0056b3;">${correoFinal}</a>
          </p>
          <p style="font-size: 16px; margin-top: 20px;">Haz clic en el siguiente botón para definir tu contraseña:</p>
          <div style="text-align: center; margin: 30px 0;">
            <a href="${link}" style="
              background-color: #ff6b35;
              color: #ffffff;
              padding: 12px 25px;
              border-radius: 6px;
              text-decoration: none;
              font-size: 16px;
              font-weight: bold;">
              Definir contraseña
            </a>
          </div>
          <p style="font-size: 14px;">Este enlace expirará en <strong>24 horas</strong>.</p>
        </div>
        <div style="background-color: #003366; color: #ffffff; text-align: center; padding: 15px; font-size: 13px;">
          © ${new Date().getFullYear()} DIDADPOL · Todos los derechos reservados
        </div>
      </div>
    `;

    // 7) Enviar correo con Brevo al correo personal SIEMPRE
    if (typeof enviarCorreoBrevo !== 'function') {
      throw new Error('enviarCorreoBrevo no está definido/importado en este archivo');
    }

    const envio = await enviarCorreoBrevo(
      correoPersonal,
      'Definir tu contraseña de acceso - DIDADPOL',
      html
    );

    if (!envio || envio.ok !== true) {
      await client.query('ROLLBACK');
      return res
        .status(502)
        .json({ error: 'No se pudo enviar el correo de definición de contraseña.' });
    }

    await client.query('COMMIT');

    return res.status(201).json({
      mensaje: usar_correo_institucional
        ? 'Usuario creado con correo institucional. Enlace enviado al correo personal.'
        : 'Usuario creado con correo personal. Enlace enviado.',
      correo_final: correoFinal,
    });
  } catch (error) {
    await client.query('ROLLBACK').catch(() => {});
    console.error('❌ Error al registrar usuario:', error);

    if (error.code === '23505') {
      return res
        .status(409)
        .json({ error: 'Ya existe un usuario con ese correo o código de persona.' });
    }

    return res.status(500).json({
      error: 'Error interno al registrar usuario',
      detalle: error.message,
    });
  } finally {
    client.release();
  }
});

// ==========================
// FUNCIÓN: Enviar correo con Brevo (HTTP API)
// ==========================
const axios = require('axios');

async function enviarCorreoBrevo(to, subject, html) {
  try {
    const payload = {
      sender: { 
        name: process.env.MAIL_FROM_NAME || 'DIDADPOL',
        email: process.env.MAIL_FROM_ADDRESS || 'no-reply@didadpol.gob.hn'
      },
      to: (Array.isArray(to) ? to : [to]).map(email => ({ email })),
      subject,
      htmlContent: html,
    };

    const res = await axios.post(
      'https://api.brevo.com/v3/smtp/email',
      payload,
      {
        headers: {
          'api-key': process.env.BREVO_API_KEY,
          'Content-Type': 'application/json',
        },
      }
    );

    console.log('📨 Correo enviado via Brevo:', to);
    return { ok: true, messageId: res.data?.messageId || 'ok' };

  } catch (err) {
    console.error('[Brevo API Error]', err.response?.data || err.message);
    return { ok: false, error: err.response?.data || err.message };
  }
}


// ==========================
// DEFINIR CONTRASEÑA
// ==========================
app.post('/api/definir-contrasena', async (req, res) => {
  const { email, token, password } = req.body;

  if (!email || !token || !password) {
    return res.status(400).json({ error: 'Faltan datos requeridos' });
  }

  try {
    // Verificar que el usuario exista
    const usuario = await pool.query('SELECT id FROM users WHERE email = $1', [email]);
    if (usuario.rows.length === 0) {
      return res.status(404).json({ error: 'Correo no registrado' });
    }

    const userId = usuario.rows[0].id;

    // Verificar token válido y no expirado
    const result = await pool.query(`
      SELECT * FROM password_tokens 
      WHERE user_id = $1 AND token = $2 AND expires_at > NOW()
    `, [userId, token]);

    if (result.rows.length === 0) {
      return res.status(400).json({ error: 'Token inválido o expirado' });
    }

    // Encriptar contraseña y actualizar usuario
    const hashedPassword = await bcrypt.hash(password, 10);
    const ahora = new Date().toISOString();

    await pool.query(`
      UPDATE users 
      SET password = $1, email_verified_at = $2, updated_at = $2 
      WHERE id = $3
    `, [hashedPassword, ahora, userId]);

    // Eliminar token usado
    await pool.query('DELETE FROM password_tokens WHERE user_id = $1', [userId]);

    res.status(200).json({ mensaje: 'Contraseña definida y correo verificado correctamente' });

  } catch (error) {
    console.error('❌ Error al definir contraseña:', error);
    res.status(500).json({ error: 'Error al definir contraseña', detalle: error.message });
  }
});

// ==========================
// RECUPERAR CONTRASEÑA
// ==========================
app.post('/api/recuperar-contrasena', async (req, res) => {
  const { email } = req.body;

  if (!email) {
    return res.status(400).json({ error: 'Correo requerido' });
  }

  try {
    // Verificar que el usuario exista
    const usuario = await pool.query('SELECT id FROM users WHERE email = $1', [email]);
    if (usuario.rows.length === 0) {
      return res.status(404).json({ error: 'Correo no encontrado' });
    }

    const userId = usuario.rows[0].id;
    const token = uuidv4();
    const expiresAt = new Date(Date.now() + 60 * 60 * 1000); // Expira en 1 hora

    // Eliminar tokens anteriores
    await pool.query('DELETE FROM password_tokens WHERE user_id = $1', [userId]);

    // Insertar nuevo token
    await pool.query(`
      INSERT INTO password_tokens (user_id, token, expires_at, created_at)
      VALUES ($1, $2, $3, NOW())
    `, [userId, token, expiresAt]);

    // ✅ CORREGIDO: dominio correcto de tu FRONTEND (Laravel Cloud)
    const resetUrl = `https://rrhh-didadpol-main-khmtlb.laravel.cloud/definir-contrasena?token=${token}&email=${encodeURIComponent(email)}`;

    // Enviar correo (Brevo API)
    await enviarCorreoBrevo(
      email,
      'Restablecer tu contraseña',
      `
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto;">
          <h2 style="color: #003366;">Solicitud para restablecer contraseña</h2>
          <p>Hola, hemos recibido una solicitud para restablecer tu contraseña en el sistema de <strong>DIDADPOL</strong>.</p>
          <p>Haz clic en el siguiente botón para definir una nueva contraseña:</p>
          <div style="text-align: center; margin: 30px 0;">
            <a href="${resetUrl}" style="background-color: #ff6b35; padding: 12px 25px; color: white; border-radius: 6px; text-decoration: none;">
              Definir nueva contraseña
            </a>
          </div>
          <p>Este enlace expirará en 1 hora. Si no solicitaste este cambio, puedes ignorar este mensaje.</p>
          <p style="color: #888; font-size: 12px;">© ${new Date().getFullYear()} DIDADPOL · Todos los derechos reservados</p>
        </div>
      `
    );

    res.json({ mensaje: 'Enlace de restablecimiento enviado' });

  } catch (error) {
    console.error('❌ Error en recuperar-contrasena:', error);
    res.status(500).json({ error: 'Error al enviar enlace', detalle: error.message });
  }
});

// ============================
// 🔐 VERIFICACIÓN EN DOS PASOS (2FA)
// ============================

// ⛑️ CORS (ajusta dominios permitidos)
app.use(cors({
  origin: [
    'https://rrhh-didadpol-main-khmtlb.laravel.cloud', // Laravel Cloud (prod)
    // agrega otros orígenes si necesitas
  ],
  credentials: true,
}));

// 🧯 Rate limiting específico para 2FA (por IP)
const limiter2FA = rateLimit({
  windowMs: 10 * 60 * 1000, // 10 minutos
  max: 60,                  // 60 req/10min por IP en endpoints 2FA
  standardHeaders: true,
  legacyHeaders: false,
});

// 🧠 Memoria temporal (RAM)
const challenges = Object.create(null); // { challengeId: { email, codeHash, expiresAt, attempts, resendCount, lastSentAt } }

// ⚙️ Parámetros
const COOLDOWN_SECONDS = 60;
const EXPIRES_MINUTES  = 5;
const MAX_ATTEMPTS     = 5;

// 🔧 Helpers
const sha256 = (txt) => crypto.createHash('sha256').update(txt).digest('hex');
const generate6 = () => String(crypto.randomInt(0, 1_000_000)).padStart(6, '0');
const maskEmail = (email) => {
  const [user, domain] = String(email).split('@');
  return `${(user || '').slice(0, 2)}${'*'.repeat(Math.max((user || '').length - 2, 0))}@${domain || ''}`;
};
const nowMs = () => Date.now();

// 🧹 Limpieza automática de challenges vencidos (cada 2 min)
setInterval(() => {
  const t = nowMs();
  for (const id in challenges) {
    if (Object.hasOwn(challenges, id) && t > challenges[id].expiresAt) {
      delete challenges[id];
    }
  }
}, 120_000);

// (Opcional) salud para debug
app.get('/api/2fa/_status', (req, res) => {
  res.json({ ok: true, active: Object.keys(challenges).length });
});

// ============================
// 1️⃣ Iniciar challenge (enviar código)
// ============================
app.post('/api/2fa/start', limiter2FA, async (req, res) => {
  try {
    const email = String(req.body?.email || '').trim().toLowerCase();
    if (!email) return res.status(400).json({ error: 'Correo requerido' });

    // 👉 Si quieres validar usuario existente, descomenta esto:
    // const user = await pool.query('SELECT id FROM users WHERE email = $1', [email]);
    // if (user.rows.length === 0) return res.status(404).json({ error: 'Usuario no encontrado' });

    const code       = generate6();
    const codeHash   = sha256(code);
    const challengeId = uuidv4();
    const t          = nowMs();
    const expiresAt  = t + EXPIRES_MINUTES * 60 * 1000;

    challenges[challengeId] = {
      email,
      codeHash,
      expiresAt,
      attempts: 0,
      resendCount: 0,
      lastSentAt: t,
    };

    // ✉️ Enviar correo (usa tu función ya existente)
    const html = `
      <div style="font-family:Arial,sans-serif;max-width:600px;margin:auto;">
        <h2 style="color:#003366;">Código de verificación</h2>
        <p>Tu código para ingresar a <strong>DIDADPOL</strong> es:</p>
        <div style="font-size:28px;letter-spacing:6px;font-weight:bold;margin:20px 0;">${code}</div>
        <p>El código expira en ${EXPIRES_MINUTES} minutos.</p>
      </div>
    `;
    await enviarCorreoBrevo(email, 'Código de verificación (2FA)', html);

    return res.status(202).json({
      challenge_id: challengeId,
      masked_email: maskEmail(email),
      expires_in: EXPIRES_MINUTES * 60,
      cooldown_resend: COOLDOWN_SECONDS,
    });
  } catch (err) {
    console.error('❌ Error /api/2fa/start:', err);
    return res.status(500).json({ error: 'Error al iniciar verificación' });
  }
});

// ============================
// 2️⃣ Reenviar código (cooldown)
// ============================
app.post('/api/2fa/resend', limiter2FA, async (req, res) => {
  try {
    const challenge_id = String(req.body?.challenge_id || '').trim();
    const ch = challenges[challenge_id];
    if (!ch) return res.status(404).json({ error: 'Challenge no encontrado' });

    const t = nowMs();
    if (t > ch.expiresAt) return res.status(400).json({ error: 'Challenge expirado' });

    const diff = Math.floor((t - ch.lastSentAt) / 1000);
    if (diff < COOLDOWN_SECONDS) {
      return res.status(429).json({
        error: 'Debes esperar antes de reenviar',
        retry_in: COOLDOWN_SECONDS - diff,
      });
    }

    const code = generate6();
    ch.codeHash    = sha256(code);
    ch.resendCount += 1;
    ch.lastSentAt  = t;

    const html = `
      <div style="font-family:Arial,sans-serif;max-width:600px;margin:auto;">
        <h2 style="color:#003366;">Nuevo código 2FA</h2>
        <p>Tu nuevo código de verificación es:</p>
        <div style="font-size:28px;letter-spacing:6px;font-weight:bold;margin:20px 0;">${code}</div>
        <p>Este challenge expira en ${Math.max(0, Math.floor((ch.expiresAt - t) / 60000))} minutos.</p>
      </div>
    `;
    await enviarCorreoBrevo(ch.email, 'Reenvío de código (2FA)', html);

    return res.json({ mensaje: 'Código reenviado', cooldown_resend: COOLDOWN_SECONDS });
  } catch (err) {
    console.error('❌ Error /api/2fa/resend:', err);
    return res.status(500).json({ error: 'Error al reenviar código' });
  }
});

// ============================
// 3️⃣ Verificar código
// ============================
app.post('/api/2fa/verify', limiter2FA, async (req, res) => {
  try {
    const challenge_id = String(req.body?.challenge_id || '').trim();
    const codeRaw      = String(req.body?.code || '').trim();
    if (!challenge_id || !codeRaw) {
      return res.status(400).json({ error: 'Parámetros incompletos' });
    }

    const ch = challenges[challenge_id];
    if (!ch) return res.status(404).json({ error: 'Challenge no encontrado' });

    const t = nowMs();
    if (t > ch.expiresAt) return res.status(400).json({ error: 'Challenge expirado' });
    if (ch.attempts >= MAX_ATTEMPTS) return res.status(403).json({ error: 'Demasiados intentos fallidos' });

    // Normaliza el código a 6 dígitos (solo números)
    const code = codeRaw.replace(/\D/g, '').slice(0, 6);
    ch.attempts += 1;

    const inputHash = sha256(code);
    if (inputHash !== ch.codeHash) {
      return res.status(401).json({
        error: 'Código incorrecto',
        attempts_left: Math.max(0, MAX_ATTEMPTS - ch.attempts),
      });
    }

    // ✅ Código correcto
    delete challenges[challenge_id];

    return res.json({ ok: true, mensaje: 'Verificación exitosa' });
  } catch (err) {
    console.error('❌ Error /api/2fa/verify:', err);
    return res.status(500).json({ error: 'Error al verificar' });
  }
});


// ✅ CRUD ROLES

// Obtener todos los roles
app.get('/api/roles', async (req, res) => {
  try {
    const resultado = await pool.query('SELECT * FROM roles ORDER BY id');
    res.json(resultado.rows);
  } catch (error) {
    console.error('❌ Error al obtener roles:', error);
    res.status(500).json({ error: 'Error al obtener roles' });
  }
});

// Obtener un solo rol por ID
app.get('/api/roles/:id', async (req, res) => {
  const { id } = req.params;
  try {
    const resultado = await pool.query('SELECT * FROM roles WHERE id = $1', [id]);
    if (resultado.rows.length === 0) {
      return res.status(404).json({ error: 'Rol no encontrado' });
    }
    res.json(resultado.rows[0]);
  } catch (error) {
    console.error('❌ Error al obtener rol:', error);
    res.status(500).json({ error: 'Error al obtener rol' });
  }
});

// Crear nuevo rol (con estado)
app.post('/api/roles', async (req, res) => {
  const { nombre, descripcion, estado } = req.body;
  if (!nombre) return res.status(400).json({ error: 'El nombre es obligatorio' });

  try {
    const ahora = new Date().toISOString();
    const nuevo = await pool.query(
      `INSERT INTO roles (nombre, descripcion, estado, created_at, updated_at)
       VALUES ($1, $2, $3, $4, $4) RETURNING *`,
      [nombre, descripcion || '', estado || 'Activo', ahora]
    );
    res.status(201).json({ mensaje: 'Rol creado exitosamente', rol: nuevo.rows[0] });
  } catch (error) {
    console.error('❌ Error al crear rol:', error);
    res.status(500).json({ error: 'Error al crear rol' });
  }
});

// Actualizar rol (incluye estado)
app.put('/api/roles/:id', async (req, res) => {
  const { id } = req.params;
  const { nombre, descripcion, estado } = req.body;

  try {
    const ahora = new Date().toISOString();
    const actualizado = await pool.query(
      `UPDATE roles SET nombre = $1, descripcion = $2, estado = $3, updated_at = $4 WHERE id = $5 RETURNING *`,
      [nombre, descripcion || '', estado || 'Activo', ahora, id]
    );

    if (actualizado.rowCount === 0) {
      return res.status(404).json({ error: 'Rol no encontrado' });
    }

    res.json({ mensaje: 'Rol actualizado', rol: actualizado.rows[0] });
  } catch (error) {
    console.error('❌ Error al actualizar rol:', error);
    res.status(500).json({ error: 'Error al actualizar rol' });
  }
});

// Eliminar rol
app.delete('/api/roles/:id', async (req, res) => {
  const { id } = req.params;

  try {
    const eliminado = await pool.query('DELETE FROM roles WHERE id = $1 RETURNING *', [id]);

    if (eliminado.rowCount === 0) {
      return res.status(404).json({ error: 'Rol no encontrado' });
    }

    res.json({ mensaje: 'Rol eliminado correctamente' });
  } catch (error) {
    console.error('❌ Error al eliminar rol:', error);
    res.status(500).json({ error: 'Error al eliminar rol' });
  }
});

// Obtener todos los usuarios con su rol y estado
app.get('/api/usuarios', async (req, res) => {
  try {
    const resultado = await pool.query(`
      SELECT u.id, u.name, u.email, u.estado, r.nombre AS nombre_rol, r.id AS role_id
      FROM users u
      LEFT JOIN role_user ru ON ru.user_id = u.id
      LEFT JOIN roles r ON r.id = ru.role_id
      ORDER BY u.id;
    `);
    res.json(resultado.rows);
  } catch (err) {
    console.error('❌ Error al obtener usuarios:', err);
    res.status(500).json({ error: 'Error interno del servidor' });
  }
});


// Cambiar estado del usuario
app.put('/api/usuarios/:id/estado', async (req, res) => {
  const { id } = req.params;
  const { estado } = req.body;

  try {
    await pool.query(
      'UPDATE users SET estado = UPPER($1), updated_at = NOW() WHERE id = $2',
      [estado, id]
    );
    res.json({ message: 'Estado actualizado correctamente' });
  } catch (err) {
    console.error('❌ Error al cambiar estado:', err);
    res.status(500).json({ error: 'No se pudo actualizar el estado' });
  }
});


// Cambiar el rol de un usuario
app.put('/api/usuarios/:id/rol', async (req, res) => {
  const { id } = req.params;
  const { nuevoRolId } = req.body;

  try {
    await pool.query('UPDATE role_user SET role_id = $1, created_at = NOW() WHERE user_id = $2', [nuevoRolId, id]);
    res.json({ message: 'Rol asignado correctamente' });
  } catch (err) {
    console.error('Error al asignar rol:', err);
    res.status(500).json({ error: 'No se pudo asignar el rol' });
  }
});
// Asignar un nuevo rol (solo si aún no existe)
app.post('/api/usuarios/:id/rol', async (req, res) => {
  const { id } = req.params;
  const { role_id } = req.body;

  if (!role_id || isNaN(role_id)) {
    return res.status(400).json({ error: 'El role_id es obligatorio y debe ser un número válido' });
  }

  try {
    const existe = await pool.query('SELECT * FROM role_user WHERE user_id = $1', [id]);

    if (existe.rowCount > 0) {
      return res.status(409).json({ error: 'El usuario ya tiene un rol asignado. Usa PUT para editarlo.' });
    }

    await pool.query(
      'INSERT INTO role_user (user_id, role_id, created_at) VALUES ($1, $2, NOW())',
      [id, role_id]
    );

    res.json({ message: '✅ Rol asignado correctamente por primera vez.' });
  } catch (err) {
    console.error('❌ Error al asignar nuevo rol:', err);
    res.status(500).json({ error: 'No se pudo asignar el rol.' });
  }
});

// Editar rol existente
app.put('/api/usuarios/:id/rol', async (req, res) => {
  const { id } = req.params;
  const { role_id } = req.body;

  if (!role_id || isNaN(role_id)) {
    return res.status(400).json({ error: 'El role_id es obligatorio y debe ser un número válido' });
  }

  try {
    const existe = await pool.query('SELECT * FROM role_user WHERE user_id = $1', [id]);

    if (existe.rowCount === 0) {
      return res.status(404).json({ error: 'El usuario no tiene un rol asignado. Usa POST para asignarlo.' });
    }

    await pool.query(
      'UPDATE role_user SET role_id = $1, created_at = NOW() WHERE user_id = $2',
      [role_id, id]
    );

    res.json({ message: '✅ Rol actualizado correctamente.' });
  } catch (err) {
    console.error('❌ Error al actualizar rol:', err);
    res.status(500).json({ error: 'No se pudo actualizar el rol.' });
  }
});

// ========================
// MODULOS - Obtener todos
// ========================
app.get('/api/modulos', async (req, res) => {
  try {
    const resultado = await pool.query('SELECT id, nombre FROM modulos ORDER BY nombre');
    res.json(resultado.rows);
  } catch (error) {
    console.error('❌ Error al obtener módulos:', error);
    res.status(500).json({ error: 'Error al obtener los módulos' });
  }
});

// ==========================
// PERMISOS - Obtener por rol
// ==========================
app.get('/api/permisos/:rol_id', async (req, res) => {
  const { rol_id } = req.params;

  try {
    const resultado = await pool.query(`
      SELECT 
        m.id AS modulo_id, 
        m.nombre, 
        COALESCE(p.tiene_acceso, FALSE) AS tiene_acceso,
        COALESCE(p.puede_crear, FALSE) AS puede_crear,
        COALESCE(p.puede_actualizar, FALSE) AS puede_actualizar,
        COALESCE(p.puede_eliminar, FALSE) AS puede_eliminar
      FROM modulos m
      LEFT JOIN permisos p ON p.modulo_id = m.id AND p.rol_id = $1
      ORDER BY m.nombre
    `, [rol_id]);

    res.json(resultado.rows);
  } catch (error) {
    console.error('❌ Error al obtener permisos:', error);
    res.status(500).json({ error: 'Error al obtener permisos' });
  }
});


// ========================================
// PERMISOS - Crear o actualizar por módulo
// ========================================
app.post('/api/permisos', async (req, res) => {
  const {
    rol_id,
    modulo_id,
    tiene_acceso = false,
    puede_crear = false,
    puede_actualizar = false,
    puede_eliminar = false
  } = req.body;

  if (!rol_id || !modulo_id) {
    return res.status(400).json({ error: 'rol_id y modulo_id son obligatorios' });
  }

  try {
    const existe = await pool.query(
      'SELECT id FROM permisos WHERE rol_id = $1 AND modulo_id = $2',
      [rol_id, modulo_id]
    );

    if (existe.rowCount > 0) {
      // Actualizar
      await pool.query(
        `UPDATE permisos 
         SET tiene_acceso = $1,
             puede_crear = $2,
             puede_actualizar = $3,
             puede_eliminar = $4,
             updated_at = NOW()
         WHERE rol_id = $5 AND modulo_id = $6`,
        [tiene_acceso, puede_crear, puede_actualizar, puede_eliminar, rol_id, modulo_id]
      );
    } else {
      // Insertar
      await pool.query(
        `INSERT INTO permisos 
         (rol_id, modulo_id, tiene_acceso, puede_crear, puede_actualizar, puede_eliminar, created_at, updated_at)
         VALUES ($1, $2, $3, $4, $5, $6, NOW(), NOW())`,
        [rol_id, modulo_id, tiene_acceso, puede_crear, puede_actualizar, puede_eliminar]
      );
    }

    res.json({ mensaje: 'Permisos guardados correctamente' });
  } catch (error) {
    console.error('❌ Error al guardar permiso:', error);
    res.status(500).json({ error: 'Error al guardar permiso' });
  }
});

// Gestión de empleados - GET
app.get('/api/empleados', async (req, res) => {
  const query = `
    SELECT
      p.cod_persona,
      p.nombre_completo,
      p.genero,
      p.estado_civil,
      p.fec_nacimiento,
      p.lugar_nacimiento,
      p.nacionalidad,
      p.dni,
      p.foto_persona,

      d.direccion,
      m.nom_municipio,
      dept.nom_depto AS departamento,

      t.numero AS telefono,
      t.telefono_emergencia,
      t.nombre_contacto_emergencia,

      e.cod_empleado,
      e.email_trabajo,
      e.es_jefe,
      e.fecha_contratacion,
      e.cod_horario,
      hl.nom_horario AS nombre_horario,
      e.cod_oficina,
      o.nom_oficina AS nombre_oficina,
      e.cod_nivel_educativo,
      ne.nom_nivel AS nivel_educativo,

      tm.nom_tipo AS modalidad,
      pu.nom_puesto AS puesto,

      ch.salario,
      ch.fecha_inicio_contrato,
      ch.fecha_final_contrato,
      ch.contrato_activo

    FROM empleados e
    LEFT JOIN personas p ON e.cod_persona = p.cod_persona
    LEFT JOIN direcciones d ON p.cod_persona = d.cod_persona
    LEFT JOIN municipios m ON d.cod_municipio = m.cod_municipio
    LEFT JOIN departamentos dept ON m.cod_depto = dept.cod_depto
    LEFT JOIN telefonos t ON p.cod_persona = t.cod_persona
    LEFT JOIN tipos_modalidades tm ON e.cod_tipo_modalidad = tm.cod_tipo_modalidad
    LEFT JOIN puestos pu ON e.cod_puesto = pu.cod_puesto
    LEFT JOIN oficinas o ON e.cod_oficina = o.cod_oficina
    LEFT JOIN niveles_educativos ne ON e.cod_nivel_educativo = ne.cod_nivel_educativo
    LEFT JOIN horarios_laborales hl ON e.cod_horario = hl.cod_horario
    LEFT JOIN empleados_contratos_histor ch ON e.cod_empleado = ch.cod_empleado AND ch.contrato_activo = true;
  `;

  try {
    const result = await pool.query(query);
    res.json(result.rows);
  } catch (error) {
    console.error('Error al obtener lista de empleados:', error);
    res.status(500).json({ error: 'Error interno del servidor' });
  }
});

// RUTA PARA REGISTRAR EMPLEADO

app.post('/api/empleados', upload.single('foto_persona'), async (req, res) => {
  const client = await pool.connect();
  try {
    await client.query('BEGIN');

    const {
      nombre_completo, genero, estado_civil, fec_nacimiento,
      lugar_nacimiento, nacionalidad, dni,
      direccion, cod_municipio,
      telefono, telefono_emergencia, nombre_contacto_emergencia,
      cod_tipo_modalidad, cod_puesto, cod_oficina, cod_nivel_educativo,
      cod_horario, es_jefe, fecha_contratacion, fecha_notificacion,
      cod_tipo_empleado, email_trabajo, salario,
      fecha_inicio_contrato, fecha_final_contrato,
      contrato_activo, cod_terminacion_contrato,
      usr_registro
    } = req.body;

    const foto_persona = req.file ? req.file.filename : null;

    // 1. Insertar en personas
    const personaResult = await client.query(`
      INSERT INTO personas (
        nombre_completo, genero, estado_civil, fec_nacimiento,
        lugar_nacimiento, nacionalidad, dni, foto_persona,
        fec_registro, usr_registro
      ) VALUES ($1,$2,$3,$4,$5,$6,$7,$8,NOW(),$9) RETURNING cod_persona
    `, [
      nombre_completo, genero, estado_civil, fec_nacimiento,
      lugar_nacimiento, nacionalidad, dni, foto_persona,
      usr_registro
    ]);

    const cod_persona = personaResult.rows[0].cod_persona;

    // 2. Direcciones
    await client.query(`
      INSERT INTO direcciones (
        cod_persona, direccion, cod_municipio, fec_registro, usr_registro
      ) VALUES ($1, $2, $3, NOW(), $4)
    `, [cod_persona, direccion, cod_municipio, usr_registro]);

    // 3. Teléfonos
    await client.query(`
      INSERT INTO telefonos (
        cod_persona, numero, telefono_emergencia, nombre_contacto_emergencia, fec_registro, usr_registro
      ) VALUES ($1, $2, $3, $4, NOW(), $5)
    `, [cod_persona, telefono, telefono_emergencia, nombre_contacto_emergencia, usr_registro]);

    // 4. Empleados
    const empleadoResult = await client.query(`
      INSERT INTO empleados (
        cod_persona, cod_tipo_modalidad, cod_puesto, cod_oficina,
        cod_nivel_educativo, cod_horario, es_jefe, fecha_contratacion,
        fecha_notificacion, email_trabajo,
        fec_registro, usr_registro, cod_tipo_empleado
      ) VALUES (
        $1,$2,$3,$4,$5,$6,$7,$8,$9,$10,NOW(),$11,$12
      ) RETURNING cod_empleado
    `, [
      cod_persona, cod_tipo_modalidad, cod_puesto, cod_oficina,
      cod_nivel_educativo, cod_horario, es_jefe, fecha_contratacion,
      fecha_notificacion, email_trabajo,
      usr_registro, cod_tipo_empleado
    ]);

    const cod_empleado = empleadoResult.rows[0].cod_empleado;

    // Asegurar valor por defecto para usr_registro
    const usuarioRegistro = usr_registro || 'sistema';

    // 5. Contrato
    await client.query(`
      INSERT INTO empleados_contratos_histor (
        cod_empleado, cod_tipo_empleado, cod_puesto,
        fecha_inicio_contrato, fecha_final_contrato,
        salario, contrato_activo, usr_registro, fec_registro,
        cod_terminacion_contrato
      ) VALUES (
        $1, $2, $3, $4, $5, $6, $7, $8, NOW(), $9
      )
    `, [
      cod_empleado, cod_tipo_empleado, cod_puesto,
      fecha_inicio_contrato, fecha_final_contrato,
      salario, contrato_activo, usuarioRegistro,
      contrato_activo ? null : cod_terminacion_contrato
    ]);
    await client.query('COMMIT');
    res.json({ mensaje: 'Empleado registrado exitosamente' });

  } catch (error) {
    await client.query('ROLLBACK');
    console.error('Error al registrar empleado:', error);
    res.status(500).json({ error: 'Error al registrar empleado' });
  } finally {
    client.release();
  }
});


// Gestion de empleados - PUT (editar)
app.put('/api/empleados/:id', upload.single('foto_persona'), async (req, res) => {
  const client = await pool.connect();
  const cod_empleado = req.params.id;

  try {
    await client.query('BEGIN');

    const {
      nombre_completo, genero, estado_civil, fec_nacimiento,
      lugar_nacimiento, nacionalidad, dni,
      direccion, cod_municipio,
      telefono, telefono_emergencia, nombre_contacto_emergencia,
      cod_tipo_modalidad, cod_puesto, cod_oficina, cod_nivel_educativo,
      cod_horario, es_jefe, fecha_contratacion, fecha_notificacion,
      cod_tipo_empleado, email_trabajo, salario,
      fecha_inicio_contrato, fecha_final_contrato,
      contrato_activo, cod_terminacion_contrato,
      usr_modificacion
    } = req.body;

    const foto_persona = req.file ? req.file.filename : null;

    // Obtener cod_persona desde empleados
    const personaRes = await client.query(
      `SELECT cod_persona FROM empleados WHERE cod_empleado = $1`,
      [cod_empleado]
    );
    const cod_persona = personaRes.rows[0]?.cod_persona;
    if (!cod_persona) throw new Error('Empleado no encontrado');

    // 1. Actualizar persona
    await client.query(`
      UPDATE personas SET
        nombre_completo = $1,
        genero = $2,
        estado_civil = $3,
        fec_nacimiento = $4,
        lugar_nacimiento = $5,
        nacionalidad = $6,
        dni = $7,
        ${foto_persona ? `foto_persona = '${foto_persona}',` : ''}
        fec_modificacion = NOW(),
        usr_modificacion = $8
      WHERE cod_persona = $9
    `, [
      nombre_completo, genero, estado_civil, fec_nacimiento,
      lugar_nacimiento, nacionalidad, dni,
      usr_modificacion, cod_persona
    ]);

    // 2. Actualizar dirección
    await client.query(`
      UPDATE direcciones SET
        direccion = $1,
        cod_municipio = $2,
        fec_modificacion = NOW(),
        usr_modificacion = $3
      WHERE cod_persona = $4
    `, [direccion, cod_municipio, usr_modificacion, cod_persona]);

    // 3. Actualizar teléfonos
    await client.query(`
      UPDATE telefonos SET
        numero = $1,
        telefono_emergencia = $2,
        nombre_contacto_emergencia = $3,
        fec_modificacion = NOW(),
        usr_modificacion = $4
      WHERE cod_persona = $5
    `, [telefono, telefono_emergencia, nombre_contacto_emergencia, usr_modificacion, cod_persona]);

    // 4. Actualizar empleados
    await client.query(`
      UPDATE empleados SET
        cod_tipo_modalidad = $1,
        cod_puesto = $2,
        cod_oficina = $3,
        cod_nivel_educativo = $4,
        cod_horario = $5,
        es_jefe = $6,
        fecha_contratacion = $7,
        fecha_notificacion = $8,
        email_trabajo = $9,
        cod_tipo_empleado = $10,
        fec_modificacion = NOW(),
        usr_modificacion = $11
      WHERE cod_empleado = $12
    `, [
      cod_tipo_modalidad, cod_puesto, cod_oficina, cod_nivel_educativo,
      cod_horario, es_jefe, fecha_contratacion, fecha_notificacion,
      email_trabajo, cod_tipo_empleado, usr_modificacion, cod_empleado
    ]);

    // 5. Actualizar contrato actual (último activo)
    await client.query(`
      UPDATE empleados_contratos_histor SET
        cod_tipo_empleado = $1,
        cod_puesto = $2,
        fecha_inicio_contrato = $3,
        fecha_final_contrato = $4,
        salario = $5,
        contrato_activo = $6,
        cod_terminacion_contrato = $7,
        fec_modificacion = NOW(),
        usr_modificacion = $8
      WHERE cod_empleado = $9 AND contrato_activo = true
    `, [
      cod_tipo_empleado, cod_puesto,
      fecha_inicio_contrato, fecha_final_contrato,
      salario, contrato_activo,
      contrato_activo ? null : cod_terminacion_contrato,
      usr_modificacion, cod_empleado
    ]);

    await client.query('COMMIT');
    res.json({ mensaje: 'Empleado actualizado correctamente' });

  } catch (error) {
    await client.query('ROLLBACK');
    console.error('Error al editar empleado:', error);
    res.status(500).json({ error: 'Error al editar empleado' });
  } finally {
    client.release();
  }
});

app.get('/api/personas/dni/:dni', async (req, res) => {
  const { dni } = req.params;
  try {
    const result = await pool.query(
      'SELECT cod_persona FROM personas WHERE dni = $1',
      [dni]
    );
    res.json({ existe: result.rows.length > 0 });
  } catch (error) {
    console.error('Error al verificar el DNI:', error);
    res.status(500).json({ error: 'Error al verificar el DNI' });
  }
});



// Gestion de empleado - Eliminar 
app.delete('/api/empleados/:id', async (req, res) => {
  const client = await pool.connect();
  const cod_empleado = req.params.id;

  try {
    await client.query('BEGIN');

    // 1. Obtener cod_persona relacionado al empleado
    const result = await client.query(`
      SELECT cod_persona FROM empleados WHERE cod_empleado = $1
    `, [cod_empleado]);

    if (result.rows.length === 0) {
      throw new Error('Empleado no encontrado');
    }

    const cod_persona = result.rows[0].cod_persona;

    // 2. Eliminar contratos históricos
    await client.query(`
      DELETE FROM empleados_contratos_histor WHERE cod_empleado = $1
    `, [cod_empleado]);

    // 3. Eliminar de empleados
    await client.query(`
      DELETE FROM empleados WHERE cod_empleado = $1
    `, [cod_empleado]);

    // 4. Eliminar direcciones
    await client.query(`
      DELETE FROM direcciones WHERE cod_persona = $1
    `, [cod_persona]);

    // 5. Eliminar teléfonos
    await client.query(`
      DELETE FROM telefonos WHERE cod_persona = $1
    `, [cod_persona]);

    // 6. Eliminar persona
    await client.query(`
      DELETE FROM personas WHERE cod_persona = $1
    `, [cod_persona]);

    await client.query('COMMIT');
    res.json({ mensaje: 'Empleado y datos relacionados eliminados correctamente' });

  } catch (error) {
    await client.query('ROLLBACK');
    console.error('Error al eliminar empleado:', error);
    res.status(500).json({ error: 'Error al eliminar empleado' });
  } finally {
    client.release();
  }
});

// Obtener todos los municipios
app.get('/api/municipios', async (req, res) => {
  try {
    const result = await pool.query(`
      SELECT cod_municipio, nom_municipio AS nombre FROM public.municipios ORDER BY nom_municipio
    `);
    res.json(result.rows);
  } catch (error) {
    console.error('Error al obtener municipios:', error.message);
    res.status(500).json({ error: 'Error al obtener municipios' });
  }
});


// Obtener todos los géneros posibles
app.get('/api/generos', (req, res) => {
  const generos = [
    { nombre: 'Masculino' },
    { nombre: 'Femenino' }
  ];
  res.json(generos);
});

// Obtener todos los estados civiles posibles
app.get('/api/estados-civiles', (req, res) => {
  const estados = [
    { nombre: 'Soltero' },
    { nombre: 'Casado' },
    { nombre: 'Divorciado' },
    { nombre: 'Unión Libre' },
    { nombre: 'Viudo' }
  ];
  res.json(estados);
});

// Obtener tipo modalidad

app.get('/api/modalidades', async (req, res) => {
  try {
    const result = await pool.query(`
      SELECT cod_tipo_modalidad, nom_tipo 
      FROM tipos_modalidades 
      ORDER BY nom_tipo ASC
    `);
    const modalidades = result.rows.map(m => ({
      cod_tipo_modalidad: m.cod_tipo_modalidad,
      nombre: m.nom_tipo
    }));
    res.json(modalidades);
  } catch (error) {
    console.error('Error al obtener modalidades:', error);
    res.status(500).json({ error: 'Error al obtener modalidades' });
  }
});

// Obtener puestos (modo simple o detallado)
app.get('/api/puestos', async (req, res) => {
  const detalles = req.query.detalles === 'true';

  const query = detalles
    ? `
      SELECT cod_puesto, nom_puesto, fec_registro, usr_registro, cod_fuente_financiamiento, funciones_puesto, sueldo_base
      FROM puestos
      ORDER BY fec_registro DESC
    `
    : `
      SELECT cod_puesto, nom_puesto
      FROM puestos
      ORDER BY nom_puesto ASC
    `;

  try {
    const result = await pool.query(query);

    if (!detalles) {
      // Formato para <select>
      const puestos = result.rows.map(p => ({
        cod_puesto: p.cod_puesto,
        nombre: p.nom_puesto
      }));
      res.json(puestos);
    } else {
      // Modo detallado (devuelve todos los campos)
      res.json(result.rows);
    }

  } catch (error) {
    console.error('Error al obtener puestos:', error);
    res.status(500).json({ error: 'Error al obtener puestos' });
  }
});
app.post('/api/puestos', async (req, res) => {
  const {
    nom_puesto,
    fec_registro,
    usr_registro,
    cod_fuente_financiamiento,
    funciones_puesto,
    sueldo_base
  } = req.body;

  try {
    const result = await pool.query(`
      INSERT INTO puestos (
        nom_puesto,
        fec_registro,
        usr_registro,
        cod_fuente_financiamiento,
        funciones_puesto,
        sueldo_base
      )
      VALUES ($1, $2, $3, $4, $5, $6)
      RETURNING cod_puesto
    `, [
      nom_puesto,
      fec_registro,
      usr_registro,
      cod_fuente_financiamiento,
      funciones_puesto,
      sueldo_base
    ]);

    res.status(201).json({
      mensaje: 'Puesto registrado correctamente',
      cod_puesto: result.rows[0].cod_puesto
    });
  } catch (error) {
    console.error('Error al registrar puesto:', error);
    res.status(500).json({ error: 'Error al registrar puesto' });
  }
});

app.put('/api/puestos/:id', async (req, res) => {
  const cod_puesto = req.params.id;
  const {
    nom_puesto,
    fec_registro,
    usr_registro,
    cod_fuente_financiamiento,
    funciones_puesto,
    sueldo_base
  } = req.body;

  try {
    const result = await pool.query(`
      UPDATE puestos
      SET
        nom_puesto = $1,
        fec_registro = $2,
        usr_registro = $3,
        cod_fuente_financiamiento = $4,
        funciones_puesto = $5,
        sueldo_base = $6
      WHERE cod_puesto = $7
      RETURNING cod_puesto
    `, [
      nom_puesto,
      fec_registro,
      usr_registro,
      cod_fuente_financiamiento,
      funciones_puesto,
      sueldo_base,
      cod_puesto
    ]);

    if (result.rowCount === 0) {
      return res.status(404).json({ error: 'Puesto no encontrado' });
    }

    res.json({
      mensaje: 'Puesto actualizado correctamente',
      cod_puesto: result.rows[0].cod_puesto
    });
  } catch (error) {
    console.error('Error al actualizar puesto:', error);
    res.status(500).json({ error: 'Error al actualizar puesto' });
  }
});

app.delete('/api/puestos/:id', async (req, res) => {
  const cod_puesto = req.params.id;

  try {
    const result = await pool.query(`
      DELETE FROM puestos
      WHERE cod_puesto = $1
      RETURNING cod_puesto
    `, [cod_puesto]);

    if (result.rowCount === 0) {
      return res.status(404).json({ error: 'Puesto no encontrado' });
    }

    res.json({
      mensaje: 'Puesto eliminado correctamente',
      cod_puesto: result.rows[0].cod_puesto
    });
  } catch (error) {
    console.error('Error al eliminar puesto:', error);
    res.status(500).json({ error: 'Error al eliminar puesto' });
  }
});


// Obtener niveles educativos (modo simple o detallado)
app.get('/api/niveles-educativos', async (req, res) => {
  const detalles = req.query.detalles === 'true';

  const query = detalles
    ? `
      SELECT cod_nivel_educativo, nom_nivel, descripcion, fec_registro, usr_registro, fec_modificacion, usr_modificacion
      FROM niveles_educativos
      ORDER BY fec_registro DESC
    `
    : `
      SELECT cod_nivel_educativo, nom_nivel
      FROM niveles_educativos
      ORDER BY nom_nivel ASC
    `;

  try {
    const result = await pool.query(query);

    if (!detalles) {
      const niveles = result.rows.map(n => ({
        cod_nivel_educativo: n.cod_nivel_educativo,
        nombre: n.nom_nivel
      }));
      res.json(niveles);
    } else {
      res.json(result.rows);
    }

  } catch (error) {
    console.error('Error al obtener niveles educativos:', error);
    res.status(500).json({ error: 'Error al obtener niveles educativos' });
  }
});

app.post('/api/niveles-educativos', async (req, res) => {
  const {
    nom_nivel,
    descripcion,
    fec_registro,
    usr_registro
  } = req.body;

  try {
    const result = await pool.query(`
      INSERT INTO niveles_educativos (
        nom_nivel,
        descripcion,
        fec_registro,
        usr_registro,
        fec_modificacion,
        usr_modificacion
      )
      VALUES ($1, $2, $3, $4, $5, $6)
      RETURNING cod_nivel_educativo
    `, [
      nom_nivel,
      descripcion,
      fec_registro,
      usr_registro,
      fec_registro,    // igual a fec_registro inicialmente
      usr_registro     // igual a usr_registro inicialmente
    ]);

    res.status(201).json({
      mensaje: 'Nivel educativo registrado correctamente',
      cod_nivel_educativo: result.rows[0].cod_nivel_educativo
    });
  } catch (error) {
    console.error('Error al registrar nivel educativo:', error);
    res.status(500).json({ error: 'Error al registrar nivel educativo' });
  }
});

app.put('/api/niveles-educativos/:id', async (req, res) => {
  const cod_nivel_educativo = req.params.id;
  const {
    nom_nivel,
    descripcion,
    fec_modificacion,
    usr_modificacion
  } = req.body;

  try {
    const result = await pool.query(`
      UPDATE niveles_educativos
      SET
        nom_nivel = $1,
        descripcion = $2,
        fec_modificacion = $3,
        usr_modificacion = $4
      WHERE cod_nivel_educativo = $5
      RETURNING cod_nivel_educativo
    `, [
      nom_nivel,
      descripcion,
      fec_modificacion,
      usr_modificacion,
      cod_nivel_educativo
    ]);

    if (result.rowCount === 0) {
      return res.status(404).json({ error: 'Nivel educativo no encontrado' });
    }

    res.json({
      mensaje: 'Nivel educativo actualizado correctamente',
      cod_nivel_educativo: result.rows[0].cod_nivel_educativo
    });
  } catch (error) {
    console.error('Error al actualizar nivel educativo:', error);
    res.status(500).json({ error: 'Error al actualizar nivel educativo' });
  }
});

app.delete('/api/niveles-educativos/:id', async (req, res) => {
  const cod_nivel_educativo = req.params.id;

  try {
    const result = await pool.query(`
      DELETE FROM niveles_educativos
      WHERE cod_nivel_educativo = $1
      RETURNING cod_nivel_educativo
    `, [cod_nivel_educativo]);

    if (result.rowCount === 0) {
      return res.status(404).json({ error: 'Nivel educativo no encontrado' });
    }

    res.json({
      mensaje: 'Nivel educativo eliminado correctamente',
      cod_nivel_educativo: result.rows[0].cod_nivel_educativo
    });
  } catch (error) {
    console.error('Error al eliminar nivel educativo:', error);
    res.status(500).json({ error: 'Error al eliminar nivel educativo' });
  }
});


// Obtener horarios laborales (modo simple o detallado)
app.get('/api/horarios', async (req, res) => {
  const detalles = req.query.detalles === 'true';

  const query = detalles
    ? `
      SELECT cod_horario, nom_horario, hora_inicio, hora_final, dias_semana, fec_registro, usr_registro
      FROM horarios_laborales
      ORDER BY fec_registro DESC
    `
    : `
      SELECT cod_horario, nom_horario
      FROM horarios_laborales
      ORDER BY nom_horario ASC
    `;

  try {
    const result = await pool.query(query);

    if (!detalles) {
      // Solo transformar el resultado para los selects
      const horarios = result.rows.map(h => ({
        cod_horario: h.cod_horario,
        nombre: h.nom_horario
      }));
      res.json(horarios);
    } else {
      // En modo detallado, devuelve todos los campos
      res.json(result.rows);
    }

  } catch (error) {
    console.error('Error al obtener horarios:', error);
    res.status(500).json({ error: 'Error al obtener horarios' });
  }
});

app.post('/api/horarios', async (req, res) => {
  const {
    nom_horario,
    hora_inicio,
    hora_final,
    dias_semana,
    usr_registro
  } = req.body;

  try {
    await pool.query(`
      INSERT INTO horarios_laborales (
        nom_horario, hora_inicio, hora_final, dias_semana, fec_registro, usr_registro
      )
      VALUES ($1, $2, $3, $4, NOW(), $5)
    `, [
      nom_horario,
      hora_inicio,
      hora_final,
      JSON.stringify(dias_semana), // ✅ Se convierte a JSON válido
      usr_registro
    ]);

    res.status(201).json({ mensaje: 'Horario laboral creado correctamente' });
  } catch (error) {
    console.error('Error al crear horario:', error);
    res.status(500).json({ error: 'Error al crear horario' });
  }
});


app.put('/api/horarios/:cod_horario', async (req, res) => {
  const { cod_horario } = req.params;
  const {
    nom_horario,
    hora_inicio,
    hora_final,
    dias_semana
  } = req.body;

  try {
    await pool.query(`
      UPDATE horarios_laborales
      SET nom_horario = $1,
          hora_inicio = $2,
          hora_final = $3,
          dias_semana = $4
      WHERE cod_horario = $5
    `, [
      nom_horario,
      hora_inicio,
      hora_final,
      JSON.stringify(dias_semana), // ✅ importante para arrays tipo JSON
      cod_horario
    ]);

    res.json({ mensaje: 'Horario actualizado correctamente' });
  } catch (error) {
    console.error('Error al actualizar horario:', error);
    res.status(500).json({ error: 'Error al actualizar horario' });
  }
});

app.delete('/api/horarios/:cod_horario', async (req, res) => {
  const { cod_horario } = req.params;

  try {
    await pool.query(`
      DELETE FROM horarios_laborales
      WHERE cod_horario = $1
    `, [cod_horario]);

    res.json({ mensaje: 'Horario eliminado correctamente' });
  } catch (error) {
    console.error('Error al eliminar horario:', error);
    res.status(500).json({ error: 'Error al eliminar horario' });
  }
});



// Obtener todos los tipos de empleados
app.get('/api/tipos-empleados', async (req, res) => {
  try {
    const query = `
      SELECT cod_tipo_empleado, nom_tipo, descripcion, fec_registro, usr_registro
      FROM public.tipos_empleados
    `;
    const result = await pool.query(query);
    res.json(result.rows);
  } catch (error) {
    console.error('Error al obtener tipos de empleados:', error);
    res.status(500).json({ error: 'Error al obtener tipos de empleados' });
  }
});

// Oficinas
app.get('/api/oficinas', async (req, res) => {
  const detalles = req.query.detalles === 'true';

  try {
    const result = await pool.query(
      detalles
        ? `
          SELECT cod_oficina, cod_municipio, direccion, nom_oficina, a_cargo,
                 num_telefono, fec_registro, usr_registro, fec_modificacion,
                 usr_modificacion, direccion_corta, asignable_empleados
          FROM public.oficinas
          ORDER BY fec_registro DESC
        `
        : `
          SELECT cod_oficina, nom_oficina
          FROM public.oficinas
          ORDER BY nom_oficina ASC
        `
    );

    // 🔁 DEVOLUCIÓN CORRECTA según valor de "detalles"
    if (detalles) {
      // 🟢 Modo detallado → devuelve todo tal cual
      res.json(result.rows);
    } else {
      // 🟡 Modo simple → formatear para select
      const oficinas = result.rows.map(ofi => ({
        cod_oficina: ofi.cod_oficina,
        nombre: ofi.nom_oficina
      }));
      res.json(oficinas);
    }

  } catch (error) {
    console.error('Error al obtener oficinas:', error);
    res.status(500).json({ error: 'Error al obtener oficinas' });
  }
});

app.post('/api/oficinas', async (req, res) => {
  const {
    cod_municipio,
    direccion,
    nom_oficina,
    a_cargo,
    num_telefono,
    usr_registro,
    direccion_corta,
    asignable_empleados
  } = req.body;

  try {
    const result = await pool.query(
      `
      INSERT INTO public.oficinas (
        cod_municipio,
        direccion,
        nom_oficina,
        a_cargo,
        num_telefono,
        usr_registro,
        fec_registro,
        direccion_corta,
        asignable_empleados
      ) VALUES ($1, $2, $3, $4, $5, $6, NOW(), $7, $8)
      RETURNING cod_oficina;
      `,
      [
        cod_municipio,
        direccion,
        nom_oficina,
        a_cargo,
        num_telefono,
        usr_registro,
        direccion_corta,
        asignable_empleados
      ]
    );

    res.status(201).json({
      mensaje: 'Oficina registrada correctamente',
      cod_oficina: result.rows[0].cod_oficina
    });
  } catch (error) {
    console.error('Error al registrar oficina:', error);
    res.status(500).json({ error: 'Error al registrar oficina' });
  }
});

app.put('/api/oficinas/:id', async (req, res) => {
  const { id } = req.params;
  const {
    cod_municipio,
    direccion,
    nom_oficina,
    a_cargo,
    num_telefono,
    usr_modificacion,
    direccion_corta,
    asignable_empleados
  } = req.body;

  try {
    const result = await pool.query(
      `
      UPDATE public.oficinas
      SET
        cod_municipio = $1,
        direccion = $2,
        nom_oficina = $3,
        a_cargo = $4,
        num_telefono = $5,
        usr_modificacion = $6,
        fec_modificacion = NOW(),
        direccion_corta = $7,
        asignable_empleados = $8
      WHERE cod_oficina = $9
      RETURNING *;
      `,
      [
        cod_municipio,
        direccion,
        nom_oficina,
        a_cargo,
        num_telefono,
        usr_modificacion,
        direccion_corta,
        asignable_empleados,
        id
      ]
    );

    if (result.rowCount === 0) {
      return res.status(404).json({ error: 'Oficina no encontrada' });
    }

    res.status(200).json({
      mensaje: 'Oficina actualizada correctamente',
      oficina: result.rows[0]
    });
  } catch (error) {
    console.error('Error al actualizar oficina:', error);
    res.status(500).json({ error: 'Error al actualizar oficina' });
  }
});

app.delete('/api/oficinas/:id', async (req, res) => {
  const { id } = req.params;

  try {
    const result = await pool.query(
      `DELETE FROM public.oficinas WHERE cod_oficina = $1 RETURNING *;`,
      [id]
    );

    if (result.rowCount === 0) {
      return res.status(404).json({ error: 'Oficina no encontrada' });
    }

    res.status(200).json({
      mensaje: 'Oficina eliminada correctamente',
      oficina_eliminada: result.rows[0]
    });
  } catch (error) {
    console.error('Error al eliminar oficina:', error);
    res.status(500).json({ error: 'Error al eliminar oficina' });
  }
});

//DATOS DE LA  EMPRESA 


// GET: Obtener todos los registros de la tabla datos_empresa
app.get('/api/datos_empresa', async (req, res) => {
  try {
    const result = await pool.query('SELECT * FROM datos_empresa LIMIT 1');
    res.status(200).json(result.rows[0]);
  } catch (error) {
    console.error('Error al obtener datos de la empresa:', error);
    res.status(500).json({ mensaje: 'Error al obtener datos de la empresa' });
  }
});



// PUT: Actualizar un registro de datos_empresa por cod_empresa
app.put('/api/datos_empresa/:id', async (req, res) => {
  const { id } = req.params;
  const {
    nom_empresa,
    contacto,
    direccion,
    pais,
    ciudad,
    departamento,
    cod_postal,
    email,
    num_fijo,
    num_celular,
    fax,
    pag_web,
    cod_municipio
  } = req.body;

  try {
    const sql = `
      UPDATE public.datos_empresa SET
        nom_empresa   = $1,
        contacto      = $2,
        direccion     = $3,
        pais          = $4,
        ciudad        = $5,
        departamento  = $6,
        cod_postal    = $7,
        email         = $8,
        num_fijo      = $9,
        num_celular   = $10,
        fax           = $11,
        pag_web       = $12,
        fec_registro  = NOW(),
        cod_municipio = $13
      WHERE cod_empresa = $14
      RETURNING cod_empresa, nom_empresa, contacto, direccion, pais, ciudad,
                departamento, cod_postal, email, num_fijo, num_celular,
                fax, pag_web, cod_municipio, fec_registro;
    `;

    const params = [
      nom_empresa,
      contacto,
      direccion,
      pais,
      ciudad,
      departamento,
      cod_postal,
      email,
      num_fijo,
      num_celular,
      fax,
      pag_web,
      cod_municipio, 
      id             
    ];

    const { rows } = await pool.query(sql, params);

    if (rows.length === 0) {
      return res.status(404).json({ mensaje: 'Empresa no encontrada' });
    }

    res.status(200).json({
      mensaje: 'Datos de empresa actualizados correctamente',
      data: rows[0]
    });
  } catch (error) {
    console.error('Error al actualizar datos de la empresa:', error);
    res.status(500).json({ mensaje: 'Error al actualizar datos de la empresa' });
  }
});

//Personas 
 
app.get('/api/personas/detalle', async (req, res) => {
  try {
    const query = `
      SELECT 
        p.cod_persona,
        p.nombre_completo,
        p.dni,
        p.genero,
        p.estado_civil,
        p.fec_nacimiento,
        p.lugar_nacimiento,
        p.nacionalidad,
        p.foto_persona,
        p.fec_registro AS fec_registro_persona,

        -- Dirección y Municipio
        d.direccion,
        d.cod_municipio AS municipio_direccion,
        m.nom_municipio AS nombre_municipio,
        dept.nom_depto AS nombre_departamento,

        -- Teléfonos
        t.numero,
        t.telefono_emergencia,
        t.nombre_contacto_emergencia,

        -- Email institucional desde empleados
        e.email_trabajo

      FROM personas p
      LEFT JOIN direcciones d ON p.cod_persona = d.cod_persona
      LEFT JOIN municipios m ON d.cod_municipio = m.cod_municipio
      LEFT JOIN departamentos dept ON m.cod_depto = dept.cod_depto
      LEFT JOIN telefonos t ON p.cod_persona = t.cod_persona
      LEFT JOIN empleados e ON p.cod_persona = e.cod_persona

      ORDER BY p.cod_persona;
    `;

    const resultado = await pool.query(query);
    res.status(200).json(resultado.rows);
  } catch (error) {
    console.error('Error al obtener datos detallados de personas:', error);
    res.status(500).json({ error: 'Error al obtener datos detallados de personas' });
  }
});

//TIPOS EMPLEADOS
app.get('/api/tipos-empleados', async (req, res) => {
  const detalles = req.query.detalles === 'true';

  const query = detalles
    ? `
      SELECT cod_tipo_empleado, nom_tipo, descripcion, fec_registro, usr_registro
      FROM tipos_empleados
      ORDER BY fec_registro DESC
    `
    : `
      SELECT cod_tipo_empleado, nom_tipo
      FROM tipos_empleados
      ORDER BY nom_tipo ASC
    `;

  try {
    const result = await pool.query(query);

    if (!detalles) {
      // Modo resumido: ideal para llenar <select>
      const tipos = result.rows.map(t => ({
        cod_tipo_empleado: t.cod_tipo_empleado,
        nombre: t.nom_tipo
      }));
      res.json(tipos);
    } else {
      // Modo detallado
      res.json(result.rows);
    }

  } catch (error) {
    console.error('Error al obtener tipos de empleados:', error);
    res.status(500).json({ error: 'Error al obtener tipos de empleados' });
  }
});

//POST
app.post('/api/tipos-empleados', async (req, res) => {
  try {
    const { nom_tipo, descripcion, usr_registro } = req.body;

    const result = await pool.query(`
      INSERT INTO tipos_empleados (nom_tipo, descripcion, fec_registro, usr_registro)
      VALUES ($1, $2, NOW(), $3)
      RETURNING *
    `, [nom_tipo, descripcion, usr_registro]);

    res.status(201).json(result.rows[0]);
  } catch (error) {
    console.error('Error al registrar tipo de empleados:', error); // 👈 esto nos da la pista
    res.status(500).json({ error: 'Error al registrar tipo de empleado' });
  }
});

//PUT
app.put('/api/tipos-empleados/:id', async (req, res) => {
  try {
    const id = req.params.id;
    const { nom_tipo, descripcion } = req.body;

    const result = await pool.query(`
      UPDATE tipos_empleados
      SET nom_tipo = $1,
          descripcion = $2
      WHERE cod_tipo_empleado = $3
      RETURNING *
    `, [nom_tipo, descripcion, id]);

    res.status(200).json(result.rows[0]);
  } catch (error) {
    console.error('Error al actualizar tipo de empleado:', error.message);
    res.status(500).json({ error: 'Error al actualizar tipo de empleado' });
  }
});


//DELETE
app.delete('/api/tipos-empleados/:id', async (req, res) => {
  try {
    const id = req.params.id;

    await pool.query(`
      DELETE FROM public.tipos_empleados
      WHERE cod_tipo_empleado = $1
    `, [id]);

    res.status(200).json({ mensaje: 'Tipo de empleado eliminado correctamente' });
  } catch (error) {
    console.error('Error al eliminar tipo de empleado:', error);
    res.status(500).json({ error: 'Error al eliminar tipo de empleado' });
  }
});

// GET asistencias por mes para todos los empleados (con detalles)
app.get('/api/control-asistencia/mes', async (req, res) => {
  const { mes, anio } = req.query;

  if (!mes || !anio) {
    return res.status(400).json({ error: 'Mes y año son requeridos' });
  }

  try {
    const empleadosQuery = `
      SELECT 
        e.cod_empleado, 
        p.nombre_completo, 
        p.dni,
        pu.nom_puesto AS puesto
      FROM empleados e
      LEFT JOIN personas p ON e.cod_persona = p.cod_persona
      LEFT JOIN puestos pu ON e.cod_puesto = pu.cod_puesto
      ORDER BY p.nombre_completo;
    `;
    const empleadosResult = await pool.query(empleadosQuery);
    const empleados = empleadosResult.rows;

    const asistenciasQuery = `
      SELECT cod_empleado, fecha, hora_entrada, hora_salida, observacion
      FROM control_asistencia
      WHERE EXTRACT(MONTH FROM fecha) = $1
        AND EXTRACT(YEAR FROM fecha) = $2
    `;
    const asistenciasResult = await pool.query(asistenciasQuery, [mes, anio]);

    const asistenciasPorEmpleado = {};
    asistenciasResult.rows.forEach(row => {
      const fechaStr = new Date(row.fecha).toISOString().split('T')[0];

      if (!asistenciasPorEmpleado[row.cod_empleado]) {
        asistenciasPorEmpleado[row.cod_empleado] = [];
      }

      asistenciasPorEmpleado[row.cod_empleado].push({
        fecha: fechaStr,
        hora_entrada: row.hora_entrada,
        hora_salida: row.hora_salida,
        observacion: row.observacion
      });
    });

    const diasEnMes = new Date(anio, mes, 0).getDate();

    const resultado = empleados.map(emp => {
      return {
        cod_empleado: emp.cod_empleado,
        nombre: emp.nombre_completo,
        dni: emp.dni || '-',
        puesto: emp.puesto || '-',
        registros: asistenciasPorEmpleado[emp.cod_empleado] || []
      };
    });

    res.json({ dias: diasEnMes, empleados: resultado });

  } catch (error) {
    console.error('Error al obtener asistencia mensual:', error);
    res.status(500).json({ error: 'Error al obtener asistencia mensual' });
  }
});

// control de asistencia-pdf con entrada, salida y observación (tiempo laborado)
app.get('/api/control-asistencia/pdf', async (req, res) => {
  const { mes, anio } = req.query;

  if (!mes || !anio) {
    return res.status(400).json({ error: 'Mes y año son requeridos' });
  }

  try {
    const empleadosQuery = `
      SELECT 
        e.cod_empleado, 
        p.nombre_completo, 
        p.dni,
        pu.nom_puesto AS puesto
      FROM empleados e
      LEFT JOIN personas p ON e.cod_persona = p.cod_persona
      LEFT JOIN puestos pu ON e.cod_puesto = pu.cod_puesto
      ORDER BY p.nombre_completo;
    `;
    const empleadosResult = await pool.query(empleadosQuery);
    const empleados = empleadosResult.rows;

    const asistenciasQuery = `
      SELECT cod_empleado, fecha, hora_entrada, hora_salida, observacion
      FROM control_asistencia
      WHERE EXTRACT(MONTH FROM fecha) = $1
        AND EXTRACT(YEAR FROM fecha) = $2
    `;
    const asistenciasResult = await pool.query(asistenciasQuery, [mes, anio]);

    // 🔁 Función para formatear hora a 12 horas con AM/PM
    const formatearHora = (hora) => {
      if (!hora || hora === '-') return '-';
      const fecha = new Date(`1970-01-01T${hora}`);
      return fecha.toLocaleTimeString('es-HN', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
      });
    };

    const asistenciasPorEmpleado = {};
    asistenciasResult.rows.forEach(row => {
      const fecha = new Date(row.fecha);
      const dia = fecha.getDate();

      if (!asistenciasPorEmpleado[row.cod_empleado]) {
        asistenciasPorEmpleado[row.cod_empleado] = {};
      }

      asistenciasPorEmpleado[row.cod_empleado][dia] = {
        entrada: formatearHora(row.hora_entrada),
        salida: formatearHora(row.hora_salida),
        observacion: row.observacion || '-'
      };
    });

    const diasEnMes = new Date(anio, mes, 0).getDate();

    const resultado = empleados.map((emp, index) => {
      const dias = {};
      const asistencias = asistenciasPorEmpleado[emp.cod_empleado] || {};

      for (let d = 1; d <= diasEnMes; d++) {
        if (asistencias[d]) {
          dias[d] = asistencias[d];
        } else {
          dias[d] = {
            entrada: '-',
            salida: '-',
            observacion: '-'
          };
        }
      }

      return {
        nro: index + 1,
        dni: emp.dni || '-',
        nombre: emp.nombre_completo || '-',
        puesto: emp.puesto || '-',
        dias
      };
    });

    res.json({ dias: diasEnMes, empleados: resultado });

  } catch (error) {
    console.error('Error al generar reporte PDF:', error);
    res.status(500).json({ error: 'Error al generar reporte PDF' });
  }
});



// Obtener todas las asistencias
app.get('/api/control-asistencia', async (req, res) => {
  try {
    const result = await pool.query('SELECT * FROM control_asistencia ORDER BY fecha DESC');
    res.json(result.rows);
  } catch (error) {
    console.error('Error al obtener asistencias:', error);
    res.status(500).json({ error: 'Error al obtener datos' });
  }
});

// ✅ PUT /api/control-asistencia/admin/manual
app.put('/api/control-asistencia/admin/manual', async (req, res) => {
  const {
    cod_empleado,
    fecha,
    hora_entrada,
    hora_salida,
    observacion
  } = req.body;

  // === VALIDACIONES BÁSICAS ===
  if (!cod_empleado || !fecha) {
    return res.status(400).json({ error: 'Faltan datos obligatorios: cod_empleado o fecha.' });
  }

  const reHora = /^([01]\d|2[0-3]):[0-5]\d$/;
  if (hora_entrada && !reHora.test(hora_entrada)) {
    return res.status(400).json({ error: 'hora_entrada inválida (formato HH:MM).' });
  }
  if (hora_salida && !reHora.test(hora_salida)) {
    return res.status(400).json({ error: 'hora_salida inválida (formato HH:MM).' });
  }

  // Si no hay ninguna hora, no se debe crear fila (tu modelo refleja ausencia como "no registro")
  if (!hora_entrada && !hora_salida) {
    return res.status(400).json({ error: 'Debe indicar al menos una hora (entrada o salida).' });
  }

  // Coherencia: no puede haber salida sin entrada
  if (hora_salida && !hora_entrada) {
    return res.status(400).json({ error: 'Para registrar salida, primero debe registrar la entrada.' });
  }

  // Coherencia: salida no menor que entrada
  if (hora_entrada && hora_salida && hora_salida < hora_entrada) {
    return res.status(400).json({ error: 'La hora de salida no puede ser menor que la hora de entrada.' });
  }

  try {
    // === tipo_registro (ENUM) SIEMPRE válido ===
    // - solo entrada: "Entrada"
    // - entrada + salida: "Salida" (estado final del día)
    let enumTipo = 'Entrada';
    if (hora_entrada && hora_salida) enumTipo = 'Salida';

    // === Observación automática ===
    let obs = observacion || null;
    if (hora_entrada && hora_salida) {
      const [eh, em] = hora_entrada.split(':').map(Number);
      const [sh, sm] = hora_salida.split(':').map(Number);
      const horas = (sh + sm / 60) - (eh + em / 60);

      if (!obs) {
        if (horas < 8)        obs = 'Horas incompletas';
        else if (horas > 8.1) obs = 'Horas extra';
        else                  obs = 'Asistencia normal';
      }
    }

    // === UPDATE primero; si no existe fila, INSERT ===
    const params = [
      cod_empleado,
      fecha,
      hora_entrada || null,
      hora_salida  || null,
      enumTipo,
      obs
    ];

    const updateSql = `
      UPDATE control_asistencia
         SET hora_entrada = COALESCE($3, hora_entrada),
             hora_salida  = COALESCE($4, hora_salida),
             tipo_registro= $5,
             observacion  = COALESCE($6, observacion),
             creado_en    = NOW()
       WHERE cod_empleado = $1 AND fecha = $2
       RETURNING id;
    `;
    const up = await pool.query(updateSql, params);

    let id;
    if (up.rowCount > 0) {
      id = up.rows[0].id;
    } else {
      const insertSql = `
        INSERT INTO control_asistencia
          (cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en)
        VALUES ($1, $2, $3, $4, $5, $6, NOW())
        RETURNING id;
      `;
      const ins = await pool.query(insertSql, params);
      id = ins.rows[0].id;
    }

    return res.json({
      ok: true,
      message: 'Registro de asistencia manual guardado correctamente.',
      id,
      registro: {
        cod_empleado,
        fecha,
        hora_entrada: hora_entrada || null,
        hora_salida : hora_salida  || null,
        tipo_registro: enumTipo,
        observacion : obs
      }
    });
  } catch (err) {
    console.error('❌ Error en PUT /admin/manual:', err);
    return res.status(500).json({ error: 'Error al registrar la asistencia manual.' });
  }
});



// Obtener asistencia por empleado
app.get('/api/control-asistencia/:cod_empleado', async (req, res) => {
  const { cod_empleado } = req.params;
  try {
    const result = await pool.query(
      'SELECT * FROM control_asistencia WHERE cod_empleado = $1 ORDER BY fecha DESC',
      [cod_empleado]
    );
    res.json(result.rows);
  } catch (error) {
    console.error('Error al obtener asistencia:', error);
    res.status(500).json({ error: 'Error al obtener asistencia' });
  }
});

// REGISTRAR ASISTENCIA

app.post('/api/control-asistencia', async (req, res) => {
  const { cod_empleado, tipo_registro, observacion } = req.body;
  if (!cod_empleado || !tipo_registro) {
    return res.status(400).json({ error: 'Faltan datos requeridos' });
  }

  try {
    if (tipo_registro === 'Entrada') {
      // ¿ya hay entrada HOY?
      const { rows } = await pool.query(
        `SELECT 1
           FROM control_asistencia
          WHERE cod_empleado = $1
            AND fecha = CURRENT_DATE
          LIMIT 1`,
        [cod_empleado]
      );
      if (rows.length) {
        return res.status(400).json({ error: 'Ya registraste una entrada hoy.' });
      }

      await pool.query(
        `INSERT INTO control_asistencia
           (cod_empleado, fecha, hora_entrada, tipo_registro, observacion, creado_en)
         VALUES ($1, CURRENT_DATE, LOCALTIME, 'Entrada', $2, NOW())`,
        [cod_empleado, observacion || '']
      );

    } else if (tipo_registro === 'Salida') {
      // cerrar la ENTRADA de HOY
      const { rows } = await pool.query(
        `SELECT id
           FROM control_asistencia
          WHERE cod_empleado = $1
            AND fecha = CURRENT_DATE
            AND hora_salida IS NULL
          ORDER BY hora_entrada DESC
          LIMIT 1`,
        [cod_empleado]
      );

      if (!rows.length) {
        return res.status(400).json({ error: 'No se encontró una entrada pendiente de salida hoy.' });
      }

      await pool.query(
        `UPDATE control_asistencia
            SET hora_salida = LOCALTIME,
                tipo_registro = 'Salida',
                observacion   = $1,
                creado_en     = NOW()
          WHERE id = $2`,
        [observacion || '', rows[0].id]
      );
    }

    res.json({ mensaje: 'Asistencia registrada correctamente' });
  } catch (error) {
    console.error('Error al registrar asistencia:', error);
    res.status(500).json({ error: 'Error al registrar asistencia' });
  }
});


// ESTADO DE HOY (tarjeta)

app.get('/api/control-asistencia/:cod_empleado/hoy', async (req, res) => {
  const { cod_empleado } = req.params;
  try {
    const { rows } = await pool.query(
      `SELECT *
         FROM control_asistencia
        WHERE cod_empleado = $1
          AND fecha = CURRENT_DATE
        ORDER BY hora_entrada DESC
        LIMIT 1`,
      [cod_empleado]
    );
    res.json(rows[0] || null);
  } catch (error) {
    console.error('Error al obtener punch:', error);
    res.status(500).json({ error: 'Error al obtener punch del día' });
  }
});



// ESTADÍSTICAS (hoy / semana / mes)

app.get('/api/control-asistencia/:cod_empleado/estadisticas', async (req, res) => {
  const { cod_empleado } = req.params;
  const redondear = n => Math.round(((parseFloat(n || 0)) + Number.EPSILON) * 100) / 100;

  try {
    // HOY (si no hay salida, usamos LOCALTIME)
    const { rows: h } = await pool.query(
      `SELECT COALESCE(
                SUM(EXTRACT(EPOCH FROM (COALESCE(hora_salida, LOCALTIME) - hora_entrada)) / 3600),
                0
              ) AS horas
         FROM control_asistencia
        WHERE cod_empleado = $1
          AND fecha = CURRENT_DATE`,
      [cod_empleado]
    );
    const horasHoy = redondear(h[0].horas);

    // SEMANA (si es hoy y no hay salida, LOCALTIME; días pasados: solo cerrados)
    const { rows: s } = await pool.query(
      `SELECT COALESCE(
                SUM(EXTRACT(EPOCH FROM (
                  CASE
                    WHEN fecha = CURRENT_DATE
                      THEN (COALESCE(hora_salida, LOCALTIME) - hora_entrada)
                    ELSE (hora_salida - hora_entrada)
                  END
                )) / 3600),
                0
              ) AS horas
         FROM control_asistencia
        WHERE cod_empleado = $1
          AND fecha >= date_trunc('week', CURRENT_DATE)`,
      [cod_empleado]
    );
    const horasSemana = redondear(s[0].horas);

    // MES (misma lógica)
    const { rows: m } = await pool.query(
      `SELECT COALESCE(
                SUM(EXTRACT(EPOCH FROM (
                  CASE
                    WHEN fecha = CURRENT_DATE
                      THEN (COALESCE(hora_salida, LOCALTIME) - hora_entrada)
                    ELSE (hora_salida - hora_entrada)
                  END
                )) / 3600),
                0
              ) AS horas
         FROM control_asistencia
        WHERE cod_empleado = $1
          AND date_trunc('month', fecha) = date_trunc('month', CURRENT_DATE)`,
      [cod_empleado]
    );
    const horasMes = redondear(m[0].horas);

    const horasExtra     = redondear(horasMes > 160 ? horasMes - 160 : 0);
    const horasRestantes = redondear(Math.max(160 - horasMes, 0));

    res.json({
      hoy: horasHoy,
      semana: horasSemana,
      mes: horasMes,
      extra: horasExtra,
      restantes: horasRestantes
    });
  } catch (error) {
    console.error('Error en estadísticas:', error);
    res.status(500).json({ error: 'Error al calcular estadísticas' });
  }
});


// ---------------------- RUTAS PARA EVENTOS ----------------------

// Middleware para obtener el código de empleado del header
function obtenerCodEmpleado(req, res, next) {
  const codEmpleado = req.header('X-Employee-Code');
  if (!codEmpleado) {
    return res.status(401).json({ error: 'Falta código de empleado en la solicitud' });
  }
  req.codEmpleado = codEmpleado;
  next();
}

// Obtener todos los eventos del usuario logueado
app.get('/api/eventos', obtenerCodEmpleado, async (req, res) => {
  try {
    const result = await pool.query(
      'SELECT * FROM public.eventos WHERE cod_empleado = $1 ORDER BY fecha_inicio DESC',
      [req.codEmpleado]
    );
    res.json(result.rows);
  } catch (error) {
    res.status(500).json({ error: 'Error al obtener eventos' });
  }
});

// Obtener evento por ID (solo si pertenece al usuario)
app.get('/api/eventos/:id', obtenerCodEmpleado, async (req, res) => {
  try {
    const result = await pool.query(
      'SELECT * FROM public.eventos WHERE id = $1 AND cod_empleado = $2',
      [req.params.id, req.codEmpleado]
    );
    if (result.rows.length === 0) return res.status(404).json({ error: 'Evento no encontrado' });
    res.json(result.rows[0]);
  } catch (error) {
    res.status(500).json({ error: 'Error al obtener el evento' });
  }
});

// Crear nuevo evento
app.post('/api/eventos', obtenerCodEmpleado, async (req, res) => {
  const {
    titulo,
    fecha_inicio,
    fecha_fin,
    todo_el_dia,
    descripcion,
    lugar,
    color_fondo,
    color_texto,
    tipo,
    enlace,
    recurrente
  } = req.body;

  try {
    const result = await pool.query(
      `INSERT INTO public.eventos 
        (titulo, fecha_inicio, fecha_fin, todo_el_dia, descripcion, lugar, color_fondo, color_texto, tipo, enlace, recurrente, cod_empleado)
      VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12)
      RETURNING *`,
      [titulo, fecha_inicio, fecha_fin, todo_el_dia, descripcion, lugar, color_fondo, color_texto, tipo, enlace, recurrente, req.codEmpleado]
    );
    res.status(201).json(result.rows[0]);
  } catch (error) {
    res.status(500).json({ error: 'Error al crear el evento' });
  }
});

// Actualizar evento (solo si pertenece al usuario)
app.put('/api/eventos/:id', obtenerCodEmpleado, async (req, res) => {
  const {
    titulo,
    fecha_inicio,
    fecha_fin,
    todo_el_dia,
    descripcion,
    lugar,
    color_fondo,
    color_texto,
    tipo,
    enlace,
    recurrente
  } = req.body;

  try {
    const result = await pool.query(
      `UPDATE public.eventos SET 
        titulo = $1,
        fecha_inicio = $2,
        fecha_fin = $3,
        todo_el_dia = $4,
        descripcion = $5,
        lugar = $6,
        color_fondo = $7,
        color_texto = $8,
        tipo = $9,
        enlace = $10,
        recurrente = $11
      WHERE id = $12 AND cod_empleado = $13
      RETURNING *`,
      [titulo, fecha_inicio, fecha_fin, todo_el_dia, descripcion, lugar, color_fondo, color_texto, tipo, enlace, recurrente, req.params.id, req.codEmpleado]
    );
    if (result.rows.length === 0) return res.status(404).json({ error: 'Evento no encontrado o no autorizado' });
    res.json(result.rows[0]);
  } catch (error) {
    res.status(500).json({ error: 'Error al actualizar el evento' });
  }
});

// Eliminar evento (solo si pertenece al usuario)
app.delete('/api/eventos/:id', obtenerCodEmpleado, async (req, res) => {
  try {
    const result = await pool.query(
      'DELETE FROM public.eventos WHERE id = $1 AND cod_empleado = $2 RETURNING *',
      [req.params.id, req.codEmpleado]
    );
    if (result.rows.length === 0) return res.status(404).json({ error: 'Evento no encontrado o no autorizado' });
    res.json({ message: 'Evento eliminado correctamente' });
  } catch (error) {
    res.status(500).json({ error: 'Error al eliminar el evento' });
  }
});


// Ruta para obtener el total de empleados
app.get('/api/empleados/total', async (req, res) => {
  try {
    const result = await pool.query(`
      SELECT COUNT(*) AS total_empleados
      FROM public.empleados;
    `);
    res.json({ total_empleados: result.rows[0].total_empleados });
  } catch (error) {
    console.error('Error al obtener total de empleados:', error);
    res.status(500).json({ error: 'Error al obtener total de empleados' });
  }
});

// Ruta para obtener el total de usuarios y su estado (activos/inactivos)
app.get('/api/usuarios/total', async (req, res) => {
  try {
    const result = await pool.query(`
      SELECT 
        COUNT(*) AS total_usuarios,
        SUM(CASE WHEN estado = 'ACTIVO' THEN 1 ELSE 0 END) AS usuarios_activos,
        SUM(CASE WHEN estado = 'INACTIVO' THEN 1 ELSE 0 END) AS usuarios_inactivos
      FROM public.users;
    `);
    res.json({
      total_usuarios: result.rows[0].total_usuarios,
      usuarios_activos: result.rows[0].usuarios_activos,
      usuarios_inactivos: result.rows[0].usuarios_inactivos
    });
  } catch (error) {
    console.error('Error al obtener total de usuarios:', error);
    res.status(500).json({ error: 'Error al obtener total de usuarios' });
  }
});

// NO rompe tus rutas actuales
app.get('/api/asistencia/:cod_empleado/status-hoy', async (req, res) => {
  const { cod_empleado } = req.params;
  try {
    const { rows } = await pool.query(
      `SELECT hora_entrada, hora_salida
         FROM control_asistencia
        WHERE cod_empleado = $1
          AND fecha = CURRENT_DATE
        ORDER BY hora_entrada DESC
        LIMIT 1`,
      [cod_empleado]
    );

    if (!rows.length) return res.json({ status: 'sin-registro' });

    const r = rows[0];
    if (r.hora_salida == null) {
      return res.json({ status: 'pendiente-salida', hora_entrada: r.hora_entrada });
    }
    return res.json({ status: 'completo', hora_entrada: r.hora_entrada, hora_salida: r.hora_salida });
  } catch (e) {
    console.error('status-hoy error:', e);
    res.status(500).json({ error: 'No se pudo obtener el estado de hoy' });
  }
});






//planilla 

// ========= POST: Crear/Actualizar Planilla =========
const round2 = n => Math.round((Number(n) + Number.EPSILON) * 100) / 100;

async function calcISR(client, salarioMensual) {
  const { rows } = await client.query(
    `SELECT sueldo_inicio, sueldo_fin, porcentaje
       FROM i_s_r_planillas
      WHERE tipo='ISR'
      ORDER BY sueldo_inicio`
  );
  let base = Number(salarioMensual) || 0, total = 0;
  for (const r of rows) {
    const ini = Number(r.sueldo_inicio), fin = Number(r.sueldo_fin), pct = Number(r.porcentaje);
    let grav = 0;
    if (base > fin) grav = fin - ini;
    else if (base > ini) grav = base - ini;
    total += grav * (pct / 100);
  }
  return round2(total);
}

async function calcVecinal(client, salarioMensual) {
  const ingresoAnual = Number(salarioMensual) * 12;
  const { rows } = await client.query(
    `SELECT porcentaje
       FROM i_s_r_planillas
      WHERE tipo='Vecinal' AND $1 BETWEEN sueldo_inicio AND sueldo_fin
      LIMIT 1`,
    [ingresoAnual]
  );
  if (!rows.length) return 0;
  const impAnual = (ingresoAnual / 1000) * Number(rows[0].porcentaje);
  return round2(impAnual / 12);
}

app.post('/api/planillas', async (req, res) => {
  const client = await pool.connect();
  try {
    const {
      cod_persona,
      injupemp_reingresos = 0,
      injupemp_prestamos = 0,
      prestamo_banco_atlantida = 0,
      pagos_deducibles = 0,
      colegio_admon_empresas = 0,
      cuota_coop_elga = 0
    } = req.body;

    if (!cod_persona) return res.status(400).json({ error: 'cod_persona es requerido' });

    await client.query('BEGIN');

    // Persona + empleado
    const per = await client.query(
      `SELECT p.cod_persona, p.nombre_completo, p.rtn, p.dni,
              e.cod_empleado, e.cod_puesto
         FROM personas p
         JOIN empleados e ON e.cod_persona = p.cod_persona
        WHERE p.cod_persona = $1
        LIMIT 1`, [cod_persona]
    );
    if (!per.rowCount) throw new Error('Persona/Empleado no encontrado');
    const { cod_empleado, cod_puesto, nombre_completo, rtn, dni } = per.rows[0];

    // Puesto
    const pu = await client.query(
      `SELECT nom_puesto FROM puestos WHERE cod_puesto = $1 LIMIT 1`, [cod_puesto]
    );
    const nom_puesto = pu.rows[0]?.nom_puesto || null;

    // Contrato activo
    const cont = await client.query(
      `SELECT salario, fecha_inicio_contrato
         FROM empleados_contratos_histor
        WHERE cod_empleado = $1 AND contrato_activo = true
        ORDER BY fecha_inicio_contrato DESC
        LIMIT 1`, [cod_empleado]
    );
    const salarioBase = Number(cont.rows[0]?.salario || 0);
    const fecha_inicio_contrato = cont.rows[0]?.fecha_inicio_contrato || null;

    // Asistencia → DT/DD (igual que en tu controlador Laravel)
    const dtQ = await client.query(
      `SELECT COUNT(DISTINCT fecha)::int AS dt
         FROM control_asistencia
        WHERE cod_empleado = $1 AND tipo_registro = 'Entrada'`,
      [cod_empleado]
    );
    const dt = Math.max(0, Math.min(30, Number(dtQ.rows[0]?.dt || 0)));
    const dd = 30 - dt;

    // Cálculos de ley (no editables)
    const salario_bruto = round2((salarioBase / 30) * dt);
    const ihss = round2(salario_bruto * 0.025);
    const injupemp = round2(salario_bruto * 0.095);
    const isr = await calcISR(client, salario_bruto);
    const impuesto_vecinal = await calcVecinal(client, salario_bruto);

    // Suma de autorizadas (manuales del modal)
    const autorizadas =
      Number(injupemp_reingresos) +
      Number(injupemp_prestamos) +
      Number(prestamo_banco_atlantida) +
      Number(pagos_deducibles) +
      Number(colegio_admon_empresas) +
      Number(cuota_coop_elga);

    const total_deducciones = round2(ihss + isr + injupemp + impuesto_vecinal + autorizadas);
    const total_a_pagar = Math.max(round2(salario_bruto - total_deducciones), 0);

    // Upsert por cod_persona
    const ex = await client.query(
      `SELECT id FROM planillas WHERE cod_persona = $1 LIMIT 1`, [cod_persona]
    );

    if (ex.rowCount) {
      await client.query(
        `UPDATE planillas SET
           dd=$2, dt=$3, salario_bruto=$4,
           ihss=$5, isr=$6, injupemp=$7, impuesto_vecinal=$8,
           dias_descargados=$2,
           injupemp_reingresos=$9, injupemp_prestamos=$10, prestamo_banco_atlantida=$11,
           pagos_deducibles=$12, colegio_admon_empresas=$13, cuota_coop_elga=$14,
           total_deducciones=$15, total_a_pagar=$16, creado_en=NOW()
         WHERE id=$1`,
        [
          ex.rows[0].id, dd, dt, salario_bruto,
          ihss, isr, injupemp, impuesto_vecinal,
          injupemp_reingresos, injupemp_prestamos, prestamo_banco_atlantida,
          pagos_deducibles, colegio_admon_empresas, cuota_coop_elga,
          total_deducciones, total_a_pagar
        ]
      );
    } else {
      await client.query(
        `INSERT INTO planillas
         (cod_persona, dd, dt, salario_bruto, ihss, isr, injupemp, impuesto_vecinal, dias_descargados,
          injupemp_reingresos, injupemp_prestamos, prestamo_banco_atlantida, pagos_deducibles,
          colegio_admon_empresas, cuota_coop_elga, total_deducciones, total_a_pagar, creado_en)
         VALUES
         ($1,$2,$3,$4,$5,$6,$7,$8,$2,$9,$10,$11,$12,$13,$14,$15,$16,NOW())`,
        [
          cod_persona, dd, dt, salario_bruto,
          ihss, isr, injupemp, impuesto_vecinal,
          injupemp_reingresos, injupemp_prestamos, prestamo_banco_atlantida,
          pagos_deducibles, colegio_admon_empresas, cuota_coop_elga,
          total_deducciones, total_a_pagar
        ]
      );
    }

    await client.query('COMMIT');

    // Respuesta para llenar el modal (no editables + datos básicos)
    res.json({
      ok: true,
      persona: { cod_persona, nombre_completo, rtn, dni },
      puesto: { nom_puesto },
      contrato: { fecha_inicio_contrato, salario: salarioBase },
      calculados: {
        dd, dt, salario_bruto, ihss, isr, injupemp, impuesto_vecinal,
        total_deducciones, total_a_pagar
      }
    });
  } catch (err) {
    await client.query('ROLLBACK');
    console.error('❌ POST /api/planillas:', err);
    res.status(500).json({ ok: false, error: err.message });
  } finally {
    client.release();
  }
});

// Actualizar deducciones autorizadas de una planilla (por cod_persona)
app.put('/api/planillas/by-persona/:cod_persona', async (req, res) => {
  const { cod_persona } = req.params;

  const {
    injupemp_reingresos = 0,
    injupemp_prestamos = 0,
    prestamo_banco_atlantida = 0,
    pagos_deducibles = 0,
    colegio_admon_empresas = 0,
    cuota_coop_elga = 0
  } = req.body;

  const client = await pool.connect();
  try {
    const base = await client.query(
      `SELECT id, salario_bruto, ihss, isr, injupemp, impuesto_vecinal
         FROM planillas
        WHERE cod_persona = $1
        LIMIT 1`,
      [cod_persona]
    );
    if (!base.rowCount) {
      return res.status(404).json({ ok: false, message: 'Planilla no encontrada para esa persona' });
    }

    const row = base.rows[0];
    const autorizadas =
      Number(injupemp_reingresos) +
      Number(injupemp_prestamos) +
      Number(prestamo_banco_atlantida) +
      Number(pagos_deducibles) +
      Number(colegio_admon_empresas) +
      Number(cuota_coop_elga);

    const total_deducciones =
      Number(row.ihss) + Number(row.isr) + Number(row.injupemp) + Number(row.impuesto_vecinal) + autorizadas;

    const total_a_pagar = Math.max(Number(row.salario_bruto) - Number(total_deducciones), 0);

    const upd = await client.query(
      `UPDATE planillas
          SET injupemp_reingresos      = $1,
              injupemp_prestamos       = $2,
              prestamo_banco_atlantida = $3,
              pagos_deducibles         = $4,
              colegio_admon_empresas   = $5,
              cuota_coop_elga          = $6,
              total_deducciones        = $7,
              total_a_pagar            = $8,
              creado_en                = NOW()
        WHERE id = $9
        RETURNING *`,
      [
        injupemp_reingresos,
        injupemp_prestamos,
        prestamo_banco_atlantida,
        pagos_deducibles,
        colegio_admon_empresas,
        cuota_coop_elga,
        total_deducciones,
        total_a_pagar,
        row.id
      ]
    );

    res.json({ ok: true, message: 'Planilla actualizada', data: upd.rows[0] });
  } catch (err) {
    console.error('❌ PUT /api/planillas/by-persona/:cod_persona', err);
    res.status(500).json({ ok: false, error: 'Error al actualizar la planilla' });
  } finally {
    client.release();
  }
});



// DELETE: borrar planilla por COD_PERSONA
app.delete('/api/planillas/by-persona/:cod_persona', async (req, res) => {
  // validar que sea número
  const cod_persona = parseInt(req.params.cod_persona, 10);
  if (Number.isNaN(cod_persona)) {
    return res.status(400).json({ ok: false, error: 'cod_persona inválido' });
  }

  try {
    const result = await pool.query(
      'DELETE FROM planillas WHERE cod_persona = $1 RETURNING *',
      [cod_persona]
    );

    if (result.rowCount === 0) {
      return res.status(404).json({ ok: false, message: 'No existe planilla para ese cod_persona' });
    }

    res.json({ ok: true, message: 'Planilla eliminada', deleted: result.rows });
  } catch (err) {
    // Si hay FK, puede venir 23503 (foreign_key_violation)
    if (err.code === '23503') {
      return res.status(409).json({
        ok: false,
        error: 'No se puede eliminar porque está referenciada por otros registros (FK).'
      });
    }
    console.error('DELETE /api/planillas/by-persona:', err);
    res.status(500).json({ ok: false, error: err.message || 'Error al eliminar la planilla' });
  }
});

// ===============================
// REPORTE GENERAL - EMPLEADOS
// ===============================
app.get('/api/reportes/empleados/general', async (req, res) => {
  try {
    // KPIs: activos/inactivos/sin contrato, salario promedio/mediana, antigüedad promedio
    const kpisQuery = `
      WITH base AS (
        SELECT
          e.cod_empleado,
          e.fecha_contratacion,
          ch.contrato_activo,
          NULLIF(ch.salario, '0')::numeric AS salario
        FROM empleados e
        LEFT JOIN empleados_contratos_histor ch
          ON ch.cod_empleado = e.cod_empleado
         AND ch.contrato_activo = true
      )
      SELECT
        COUNT(*)                                                  AS total,
        COUNT(*) FILTER (WHERE contrato_activo = true)            AS activos,
        COUNT(*) FILTER (WHERE contrato_activo = false)           AS inactivos,
        COUNT(*) FILTER (WHERE contrato_activo IS NULL)           AS sin_contrato,

        -- avg(salario) ya es numeric, pero lo casteamos explícitamente por seguridad
        ROUND(CAST(AVG(salario) AS numeric), 2)                   AS salario_promedio,

        -- percentile_cont puede devolver double precision -> casteo a numeric antes de redondear
        ROUND(
          CAST(percentile_cont(0.5) WITHIN GROUP (ORDER BY salario) AS numeric),
          2
        )                                                         AS salario_mediana,

        -- EXTRACT/AGE -> double precision; se castea a numeric antes de round
        ROUND(
          CAST(AVG(EXTRACT(YEAR FROM AGE(CURRENT_DATE, fecha_contratacion))) AS numeric),
          2
        )                                                         AS antiguedad_promedio_anios
      FROM base;
    `;

    // Barras: empleados por oficina
    const porOficinaQuery = `
      SELECT
        COALESCE(o.nom_oficina, 'Sin oficina') AS nombre_oficina,
        COUNT(*)                                AS total
      FROM empleados e
      LEFT JOIN oficinas o ON o.cod_oficina = e.cod_oficina
      GROUP BY 1
      ORDER BY total DESC, nombre_oficina ASC;
    `;

    // Pastel: distribución por modalidad
    const porModalidadQuery = `
      SELECT
        COALESCE(tm.nom_tipo, 'Sin modalidad') AS modalidad,
        COUNT(*)                                AS total
      FROM empleados e
      LEFT JOIN tipos_modalidades tm ON tm.cod_tipo_modalidad = e.cod_tipo_modalidad
      GROUP BY 1
      ORDER BY total DESC, modalidad ASC;
    `;

    // Pastel/barras: distribución por nivel educativo
    const porNivelEduQuery = `
      SELECT
        COALESCE(ne.nom_nivel, 'Sin nivel') AS nivel_educativo,
        COUNT(*)                            AS total
      FROM empleados e
      LEFT JOIN niveles_educativos ne ON ne.cod_nivel_educativo = e.cod_nivel_educativo
      GROUP BY 1
      ORDER BY total DESC, nivel_educativo ASC;
    `;

    // Barras horizontales: top puestos por cantidad
    const topPuestosQuery = `
      SELECT
        COALESCE(pu.nom_puesto, 'Sin puesto') AS puesto,
        COUNT(*)                               AS total
      FROM empleados e
      LEFT JOIN puestos pu ON pu.cod_puesto = e.cod_puesto
      GROUP BY 1
      ORDER BY total DESC, puesto ASC
      LIMIT 12;
    `;

    // Tabla-resumen (para exportar/mostrar)
    const tablaQuery = `
      SELECT
        p.nombre_completo,
        p.dni,
        COALESCE(o.nom_oficina, 'Sin oficina')        AS nombre_oficina,
        COALESCE(pu.nom_puesto, 'Sin puesto')         AS puesto,
        COALESCE(tm.nom_tipo, 'Sin modalidad')        AS modalidad,
        COALESCE(ne.nom_nivel, 'Sin nivel')           AS nivel_educativo,
        e.fecha_contratacion,
        ch.contrato_activo,
        NULLIF(ch.salario, '0')::numeric              AS salario
      FROM empleados e
      LEFT JOIN personas p              ON p.cod_persona = e.cod_persona
      LEFT JOIN oficinas o              ON o.cod_oficina = e.cod_oficina
      LEFT JOIN puestos pu              ON pu.cod_puesto = e.cod_puesto
      LEFT JOIN tipos_modalidades tm    ON tm.cod_tipo_modalidad = e.cod_tipo_modalidad
      LEFT JOIN niveles_educativos ne   ON ne.cod_nivel_educativo = e.cod_nivel_educativo
      LEFT JOIN empleados_contratos_histor ch
             ON ch.cod_empleado = e.cod_empleado AND ch.contrato_activo = true
      ORDER BY p.nombre_completo ASC;
    `;

    const [kpis, ofis, mods, niveles, puestos, tabla] = await Promise.all([
      pool.query(kpisQuery),
      pool.query(porOficinaQuery),
      pool.query(porModalidadQuery),
      pool.query(porNivelEduQuery),
      pool.query(topPuestosQuery),
      pool.query(tablaQuery)
    ]);

    res.json({
      kpis: kpis.rows[0] || null,
      charts: {
        por_oficina: ofis.rows,
        por_modalidad: mods.rows,
        por_nivel_educativo: niveles.rows,
        top_puestos: puestos.rows
      },
      tabla: tabla.rows
    });
  } catch (error) {
    console.error('Error en reporte general de empleados:', error);
    res.status(500).json({ error: 'Error al generar reporte de empleados' });
  }
});

// ===================================
// REPORTE GENERAL - ASISTENCIA (MES)
// ===================================
app.get('/api/reportes/asistencia/general', async (req, res) => {
  try {
    // 1) Resolver mes/año con defaults (mes actual si no envían)
    const now = new Date();
    const mes  = Number.parseInt(req.query.mes ?? (now.getMonth() + 1), 10);
    const anio = Number.parseInt(req.query.anio ?? now.getFullYear(), 10);

    if (!Number.isInteger(mes) || !Number.isInteger(anio) || mes < 1 || mes > 12 || anio < 1900) {
      return res.status(400).json({ error: 'Mes y año son requeridos y válidos' });
    }

    // 2) Rango de fecha del mes [inicio, fin)
    const desde = new Date(anio, mes - 1, 1);  // inclusive
    const hasta = new Date(anio, mes, 1);      // exclusivo
    const diasEnMes = new Date(anio, mes, 0).getDate();

    // 3) Empleados (para cruzar oficina/puesto)
    const empleadosQuery = `
      SELECT
        e.cod_empleado,
        p.nombre_completo,
        p.dni,
        COALESCE(o.nom_oficina, 'Sin oficina') AS nombre_oficina,
        COALESCE(pu.nom_puesto,  'Sin puesto') AS puesto
      FROM empleados e
      LEFT JOIN personas p ON p.cod_persona = e.cod_persona
      LEFT JOIN oficinas o ON o.cod_oficina = e.cod_oficina
      LEFT JOIN puestos  pu ON pu.cod_puesto  = e.cod_puesto
      ORDER BY p.nombre_completo;
    `;
    const { rows: empleados } = await pool.query(empleadosQuery);

    // 4) Asistencias en el rango (mejor que EXTRACT por índices)
    const asistenciasQuery = `
      SELECT
        cod_empleado,
        fecha::date AS fecha,
        hora_entrada,
        hora_salida,
        observacion
      FROM control_asistencia
      WHERE fecha >= $1::date
        AND fecha <  $2::date
      ORDER BY fecha ASC, cod_empleado ASC;
    `;
    const { rows: asistencias } = await pool.query(asistenciasQuery, [desde, hasta]);

    // 5) Agregados en memoria
    const idxEmpleado = new Map(empleados.map(e => [e.cod_empleado, e]));
    const presentesPorEmpleado = new Map(); // Set de días presentes
    const horasPorEmpleado = new Map();     // Horas acumuladas

    const diffHoras = (entrada, salida, fecha) => {
      if (!entrada || !salida) return 0;
      const ymd = fecha.toISOString().slice(0, 10);
      const e = new Date(`${ymd}T${entrada}`);
      const s = new Date(`${ymd}T${salida}`);
      const h = (s - e) / 3600000;
      return Number.isFinite(h) && h > 0 ? h : 0;
    };

    let horasTotales = 0;

    for (const a of asistencias) {
      const setDias = presentesPorEmpleado.get(a.cod_empleado) || new Set();
      setDias.add(a.fecha.getDate());
      presentesPorEmpleado.set(a.cod_empleado, setDias);

      const prev = horasPorEmpleado.get(a.cod_empleado) || 0;
      const h    = diffHoras(a.hora_entrada, a.hora_salida, a.fecha); // solo cerrados
      horasPorEmpleado.set(a.cod_empleado, prev + h);
      horasTotales += h;
    }

    const totalEmpleados = empleados.length;
    const asistenciasEfectivas = [...presentesPorEmpleado.values()]
      .reduce((acc, s) => acc + s.size, 0);

    const asistenciasEsperadas = totalEmpleados * diasEnMes;
    const asistenciaPct = asistenciasEsperadas > 0
      ? Math.round(((asistenciasEfectivas / asistenciasEsperadas) * 100 + Number.EPSILON) * 100) / 100
      : 0;

    const horasTotalesRed = Math.round((horasTotales + Number.EPSILON) * 100) / 100;
    const horasPromedioEmpleado = totalEmpleados > 0
      ? Math.round(((horasTotales / totalEmpleados) + Number.EPSILON) * 100) / 100
      : 0;

    // Ranking por ausencias
    const rankingAusencias = empleados.map(e => {
      const presentes = (presentesPorEmpleado.get(e.cod_empleado) || new Set()).size;
      const ausencias = Math.max(diasEnMes - presentes, 0);
      return { cod_empleado: e.cod_empleado, nombre: e.nombre_completo, dni: e.dni || '-', nombre_oficina: e.nombre_oficina, puesto: e.puesto, presentes, ausencias };
    }).sort((a, b) => b.ausencias - a.ausencias).slice(0, 12);

    // Barras por oficina
    const empleadosPorOficina = new Map();
    empleados.forEach(e => empleadosPorOficina.set(e.nombre_oficina, (empleadosPorOficina.get(e.nombre_oficina) || 0) + 1));

    const presentesPorOficina = new Map();
    presentesPorEmpleado.forEach((diasSet, cod) => {
      const e = idxEmpleado.get(cod);
      if (!e) return;
      const key = e.nombre_oficina;
      presentesPorOficina.set(key, (presentesPorOficina.get(key) || 0) + diasSet.size);
    });

    const asistenciaPorOficina = [...empleadosPorOficina.entries()].map(([oficina, count]) => {
      const pres = presentesPorOficina.get(oficina) || 0;
      const esper = count * diasEnMes;
      const pct = esper > 0 ? Math.round(((pres / esper) * 100 + Number.EPSILON) * 100) / 100 : 0;
      return { nombre_oficina: oficina, empleados: count, presentes: pres, asistencia_pct: pct };
    }).sort((a, b) => b.asistencia_pct - a.asistencia_pct);

    // Tabla detalle
    const tabla = empleados.map(e => ({
      cod_empleado: e.cod_empleado,
      nombre: e.nombre_completo,
      dni: e.dni || '-',
      nombre_oficina: e.nombre_oficina,
      puesto: e.puesto,
      dias_presentes: (presentesPorEmpleado.get(e.cod_empleado) || new Set()).size,
      horas_mes: Math.round(((horasPorEmpleado.get(e.cod_empleado) || 0) + Number.EPSILON) * 100) / 100
    })).sort((a, b) => a.nombre.localeCompare(b.nombre));

    res.json({
      periodo: { mes, anio, dias_mes: diasEnMes },
      kpis: {
        empleados: totalEmpleados,
        total_registros: asistencias.length,
        asistencia_pct: asistenciaPct,
        horas_totales: horasTotalesRed,
        horas_promedio_empleado: horasPromedioEmpleado
      },
      charts: {
        asistencia_por_oficina: asistenciaPorOficina,
        ranking_ausencias: rankingAusencias
      },
      tabla
    });
  } catch (error) {
    console.error('Error en reporte general de asistencia:', error);
    res.status(500).json({ error: 'Error al generar reporte de asistencia' });
  }
});

// =========================
// 🚀 INICIAR SERVIDOR (Render Compatible)
// =========================
const PORT = process.env.PORT || 3000; // Render asigna el puerto automáticamente

app.listen(PORT, '0.0.0.0', () => {
  console.log(`✅ Servidor corriendo correctamente en el puerto ${PORT}`);
});
