--
-- PostgreSQL database dump
--

-- Dumped from database version 17.4
-- Dumped by pg_dump version 17.4

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: estado_civil_enum; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.estado_civil_enum AS ENUM (
    'Soltero',
    'Casado',
    'Divorciado',
    'Union Libre',
    'Viudo'
);


ALTER TYPE public.estado_civil_enum OWNER TO postgres;

--
-- Name: estado_marca_enum; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.estado_marca_enum AS ENUM (
    'Entrada',
    'Salida'
);


ALTER TYPE public.estado_marca_enum OWNER TO postgres;

--
-- Name: estado_vacacion; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.estado_vacacion AS ENUM (
    'PENDIENTE',
    'APROBADA',
    'RECHAZADA',
    'CANCELADA'
);


ALTER TYPE public.estado_vacacion OWNER TO postgres;

--
-- Name: genero_enum; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.genero_enum AS ENUM (
    'Masculino',
    'Femenino'
);


ALTER TYPE public.genero_enum OWNER TO postgres;

--
-- Name: tipo_ausencia; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.tipo_ausencia AS ENUM (
    'VACACIONES',
    'PERMISO',
    'INCAPACIDAD'
);


ALTER TYPE public.tipo_ausencia OWNER TO postgres;

--
-- Name: dias_habiles(date, date); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dias_habiles(p_inicio date, p_fin date) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
    cur DATE;
    count INT := 0;
    dow INT;
BEGIN
    IF p_fin < p_inicio THEN
        RETURN 0;
    END IF;
    cur := p_inicio;
    WHILE cur <= p_fin LOOP
        dow := EXTRACT(DOW FROM cur); -- 0=domingo, 6=sábado
        IF dow NOT IN (0,6) AND NOT es_feriado(cur) THEN
            count := count + 1;
        END IF;
        cur := cur + INTERVAL '1 day';
    END LOOP;
    RETURN count;
END $$;


ALTER FUNCTION public.dias_habiles(p_inicio date, p_fin date) OWNER TO postgres;

--
-- Name: dias_rango(date, date); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dias_rango(p_inicio date, p_fin date) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
    d INT;
BEGIN
    d := GREATEST(1, (p_fin - p_inicio) + 1);
    RETURN d;
END $$;


ALTER FUNCTION public.dias_rango(p_inicio date, p_fin date) OWNER TO postgres;

--
-- Name: ensure_saldo(integer, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.ensure_saldo(p_empleado integer, p_anio integer) RETURNS void
    LANGUAGE plpgsql
    AS $$
BEGIN
    INSERT INTO saldo_vacaciones(cod_empleado, anio, dias_disponibles, dias_tomados)
    SELECT p_empleado, p_anio, 15, 0
    ON CONFLICT (cod_empleado, anio) DO NOTHING;
END $$;


ALTER FUNCTION public.ensure_saldo(p_empleado integer, p_anio integer) OWNER TO postgres;

--
-- Name: es_feriado(date); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.es_feriado(p_fecha date) RETURNS boolean
    LANGUAGE sql
    AS $$
    SELECT EXISTS (SELECT 1 FROM feriados f WHERE f.fecha = p_fecha);
$$;


ALTER FUNCTION public.es_feriado(p_fecha date) OWNER TO postgres;

--
-- Name: tg_vacaciones_saldo_asistencia(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.tg_vacaciones_saldo_asistencia() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    anio_i INT;
    anio_f INT;
    d INT;
    cur DATE;
BEGIN
    -- Revertir efecto anterior si era Aprobada (en UPDATE/DELETE)
    IF (TG_OP = 'UPDATE' AND OLD.estado = 'APROBADA') OR TG_OP = 'DELETE' THEN
        anio_i := EXTRACT(YEAR FROM OLD.fecha_inicio);
        anio_f := EXTRACT(YEAR FROM OLD.fecha_fin);
        cur := OLD.fecha_inicio;
        WHILE cur <= OLD.fecha_fin LOOP
            DELETE FROM control_asistencia WHERE cod_empleado = OLD.cod_empleado AND fecha = cur;
            cur := cur + INTERVAL '1 day';
        END LOOP;

        PERFORM ensure_saldo(OLD.cod_empleado, anio_i);
        UPDATE saldo_vacaciones
          SET dias_tomados = GREATEST(0, dias_tomados - dias_rango(OLD.fecha_inicio, OLD.fecha_fin)),
              dias_disponibles = dias_disponibles + dias_rango(OLD.fecha_inicio, OLD.fecha_fin)
        WHERE cod_empleado = OLD.cod_empleado AND anio = anio_i;
    END IF;

    -- Aplicar nuevo efecto si es Aprobada (en INSERT/UPDATE)
    IF (TG_OP = 'INSERT' AND NEW.estado = 'APROBADA') OR (TG_OP = 'UPDATE' AND NEW.estado = 'APROBADA') THEN
        anio_i := EXTRACT(YEAR FROM NEW.fecha_inicio);
        anio_f := EXTRACT(YEAR FROM NEW.fecha_fin);

        -- Marcar asistencia
        cur := NEW.fecha_inicio;
        WHILE cur <= NEW.fecha_fin LOOP
            INSERT INTO control_asistencia(cod_empleado, fecha)
            VALUES (NEW.cod_empleado, cur, 'VACACIONES')
            ON CONFLICT DO NOTHING;
            cur := cur + INTERVAL '1 day';
        END LOOP;

        -- Ajustar saldo del año de inicio (simple; si cruza años, adáptalo)
        PERFORM ensure_saldo(NEW.cod_empleado, anio_i);
        UPDATE saldo_vacaciones
          SET dias_tomados = dias_tomados + dias_rango(NEW.fecha_inicio, NEW.fecha_fin),
              dias_disponibles = GREATEST(0, dias_disponibles - dias_rango(NEW.fecha_inicio, NEW.fecha_fin))
        WHERE cod_empleado = NEW.cod_empleado AND anio = anio_i;
    END IF;

    IF TG_OP = 'DELETE' THEN
        RETURN OLD;
    ELSE
        RETURN NEW;
    END IF;
END $$;


ALTER FUNCTION public.tg_vacaciones_saldo_asistencia() OWNER TO postgres;

--
-- Name: tg_vacaciones_saldo_asistencia_v3(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.tg_vacaciones_saldo_asistencia_v3() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    d INT;
    cur DATE;
    anio_i INT;
BEGIN
    -- Revertir efecto anterior si OLD era Aprobada
    IF (TG_OP IN ('UPDATE','DELETE') AND OLD.estado = 'APROBADA') THEN
        -- borrar marcas de asistencia previas (solo días hábiles si era VACACIONES)
        cur := OLD.fecha_inicio;
        WHILE cur <= OLD.fecha_fin LOOP
            IF EXTRACT(DOW FROM cur) NOT IN (0,6) AND NOT es_feriado(cur) THEN
                DELETE FROM control_asistencia WHERE cod_empleado = OLD.cod_empleado AND fecha = cur;
            END IF;
            cur := cur + INTERVAL '1 day';
        END LOOP;

        -- revertir saldo si tipo=VACACIONES
        IF OLD.tipo = 'VACACIONES' THEN
            anio_i := EXTRACT(YEAR FROM OLD.fecha_inicio);
            PERFORM ensure_saldo(OLD.cod_empleado, anio_i);
            UPDATE saldo_vacaciones
                SET dias_tomados = GREATEST(0, dias_tomados - dias_habiles(OLD.fecha_inicio, OLD.fecha_fin)),
                    dias_disponibles = dias_disponibles + dias_habiles(OLD.fecha_inicio, OLD.fecha_fin)
            WHERE cod_empleado = OLD.cod_empleado AND anio = anio_i;
        END IF;
    END IF;

    -- Aplicar efecto nuevo si NEW es Aprobada
    IF (TG_OP IN ('INSERT','UPDATE') AND NEW.estado = 'APROBADA') THEN
        -- marcar asistencia por días hábiles si tipo VACACIONES
        cur := NEW.fecha_inicio;
        WHILE cur <= NEW.fecha_fin LOOP
            IF EXTRACT(DOW FROM cur) NOT IN (0,6) AND NOT es_feriado(cur) THEN
                INSERT INTO control_asistencia(cod_empleado, fecha)
                VALUES (NEW.cod_empleado, cur, 'VACACIONES')
                ON CONFLICT DO NOTHING;
            END IF;
            cur := cur + INTERVAL '1 day';
        END LOOP;

        -- saldo para VACACIONES
        IF NEW.tipo = 'VACACIONES' THEN
            anio_i := EXTRACT(YEAR FROM NEW.fecha_inicio);
            PERFORM ensure_saldo(NEW.cod_empleado, anio_i);
            UPDATE saldo_vacaciones
                SET dias_tomados = dias_tomados + dias_habiles(NEW.fecha_inicio, NEW.fecha_fin),
                    dias_disponibles = GREATEST(0, dias_disponibles - dias_habiles(NEW.fecha_inicio, NEW.fecha_fin))
            WHERE cod_empleado = NEW.cod_empleado AND anio = anio_i;
        END IF;
    END IF;

    IF TG_OP = 'DELETE' THEN
        RETURN OLD;
    ELSE
        RETURN NEW;
    END IF;
END $$;


ALTER FUNCTION public.tg_vacaciones_saldo_asistencia_v3() OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: auditoria; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.auditoria (
    id integer NOT NULL,
    tabla_afectada character varying(100) NOT NULL,
    accion character varying(20) NOT NULL,
    id_registro_afectado integer,
    datos_antes jsonb,
    datos_despues jsonb,
    usuario_id integer,
    ip_origen character varying(45),
    navegador text,
    fecha timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.auditoria OWNER TO postgres;

--
-- Name: auditoria_cod_auditoria_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.auditoria_cod_auditoria_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.auditoria_cod_auditoria_seq OWNER TO postgres;

--
-- Name: auditoria_cod_empresa_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.auditoria_cod_empresa_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.auditoria_cod_empresa_seq OWNER TO postgres;

--
-- Name: auditoria_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.auditoria_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.auditoria_id_seq OWNER TO postgres;

--
-- Name: auditoria_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.auditoria_id_seq OWNED BY public.auditoria.id;


--
-- Name: backup; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.backup (
    id integer NOT NULL,
    nombre_archivo character varying(255) NOT NULL,
    ruta_archivo text NOT NULL,
    fecha timestamp with time zone DEFAULT now(),
    usuario_id bigint,
    tipo_backup character varying(50) DEFAULT 'solo_bd'::character varying,
    tamano bigint,
    estado character varying(20) DEFAULT 'listo'::character varying
);


ALTER TABLE public.backup OWNER TO postgres;

--
-- Name: backup_cod_usuario_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.backup_cod_usuario_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.backup_cod_usuario_seq OWNER TO postgres;

--
-- Name: backup_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.backup_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.backup_id_seq OWNER TO postgres;

--
-- Name: backup_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.backup_id_seq OWNED BY public.backup.id;


--
-- Name: comprobante_pago; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.comprobante_pago (
    cod_tipo_comprobante integer NOT NULL,
    tipo_comprobante character varying(35) NOT NULL,
    icon character varying(50) NOT NULL,
    cod_empleado integer NOT NULL
);


ALTER TABLE public.comprobante_pago OWNER TO postgres;

--
-- Name: comprobante_pago_cod_empleado_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.comprobante_pago_cod_empleado_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.comprobante_pago_cod_empleado_seq OWNER TO postgres;

--
-- Name: comprobante_pago_cod_empleado_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.comprobante_pago_cod_empleado_seq OWNED BY public.comprobante_pago.cod_empleado;


--
-- Name: comprobante_pago_cod_tipo_comprobante_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.comprobante_pago_cod_tipo_comprobante_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.comprobante_pago_cod_tipo_comprobante_seq OWNER TO postgres;

--
-- Name: comprobante_pago_cod_tipo_comprobante_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.comprobante_pago_cod_tipo_comprobante_seq OWNED BY public.comprobante_pago.cod_tipo_comprobante;


--
-- Name: control_asistencia; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.control_asistencia (
    id integer NOT NULL,
    cod_empleado integer NOT NULL,
    fecha date NOT NULL,
    hora_entrada time without time zone,
    hora_salida time without time zone,
    tipo_registro public.estado_marca_enum NOT NULL,
    observacion text,
    creado_en timestamp without time zone DEFAULT now()
);


ALTER TABLE public.control_asistencia OWNER TO postgres;

--
-- Name: control_asistencia_cod_empleado_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.control_asistencia_cod_empleado_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.control_asistencia_cod_empleado_seq OWNER TO postgres;

--
-- Name: control_asistencia_cod_empleado_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.control_asistencia_cod_empleado_seq OWNED BY public.control_asistencia.cod_empleado;


--
-- Name: control_asistencia_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.control_asistencia_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.control_asistencia_id_seq OWNER TO postgres;

--
-- Name: control_asistencia_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.control_asistencia_id_seq OWNED BY public.control_asistencia.id;


--
-- Name: datos_empresa; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.datos_empresa (
    cod_empresa integer NOT NULL,
    nom_empresa character varying NOT NULL,
    contacto character varying NOT NULL,
    direccion text NOT NULL,
    pais character varying NOT NULL,
    ciudad character varying NOT NULL,
    departamento character varying NOT NULL,
    cod_postal character varying,
    email character varying NOT NULL,
    num_fijo character varying NOT NULL,
    num_celular character varying NOT NULL,
    fax character varying,
    pag_web character varying,
    fec_registro timestamp without time zone,
    usr_registro character varying NOT NULL,
    cod_municipio integer NOT NULL
);


ALTER TABLE public.datos_empresa OWNER TO postgres;

--
-- Name: datos_empresa_cod_empresa_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.datos_empresa_cod_empresa_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.datos_empresa_cod_empresa_seq OWNER TO postgres;

--
-- Name: datos_empresa_cod_empresa_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.datos_empresa_cod_empresa_seq OWNED BY public.datos_empresa.cod_empresa;


--
-- Name: datos_empresa_cod_municipio_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.datos_empresa_cod_municipio_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.datos_empresa_cod_municipio_seq OWNER TO postgres;

--
-- Name: datos_empresa_cod_municipio_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.datos_empresa_cod_municipio_seq OWNED BY public.datos_empresa.cod_municipio;


--
-- Name: departamentos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.departamentos (
    cod_depto integer NOT NULL,
    nom_depto character varying(50) NOT NULL,
    zona character varying(10)
);


ALTER TABLE public.departamentos OWNER TO postgres;

--
-- Name: departamentos_cod_depto_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.departamentos_cod_depto_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.departamentos_cod_depto_seq OWNER TO postgres;

--
-- Name: departamentos_cod_depto_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.departamentos_cod_depto_seq OWNED BY public.departamentos.cod_depto;


--
-- Name: direcciones; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.direcciones (
    cod_direccion integer NOT NULL,
    cod_persona integer NOT NULL,
    direccion text NOT NULL,
    fec_registro timestamp without time zone,
    usr_registro character varying,
    cod_municipio integer,
    fec_modificacion timestamp without time zone,
    usr_modificacion character varying(50)
);


ALTER TABLE public.direcciones OWNER TO postgres;

--
-- Name: direcciones_cod_direccion_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.direcciones_cod_direccion_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.direcciones_cod_direccion_seq OWNER TO postgres;

--
-- Name: direcciones_cod_direccion_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.direcciones_cod_direccion_seq OWNED BY public.direcciones.cod_direccion;


--
-- Name: direcciones_cod_direccion_seq1; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.direcciones ALTER COLUMN cod_direccion ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.direcciones_cod_direccion_seq1
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: empleados; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.empleados (
    cod_empleado integer NOT NULL,
    cod_persona integer NOT NULL,
    cod_tipo_modalidad integer NOT NULL,
    cod_puesto integer NOT NULL,
    cod_oficina integer,
    cod_nivel_educativo integer,
    cod_horario integer,
    es_jefe boolean DEFAULT false,
    fecha_contratacion date,
    fecha_notificacion date,
    cod_tipo_terminacion integer,
    email_trabajo character varying(100),
    fec_registro timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    usr_registro character varying,
    fec_modificacion timestamp without time zone,
    usr_modificacion character varying(255),
    cod_tipo_empleado integer
);


ALTER TABLE public.empleados OWNER TO postgres;

--
-- Name: empleados_cod_empleado_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.empleados_cod_empleado_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.empleados_cod_empleado_seq OWNER TO postgres;

--
-- Name: empleados_cod_empleado_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.empleados_cod_empleado_seq OWNED BY public.empleados.cod_empleado;


--
-- Name: empleados_contratos_histor; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.empleados_contratos_histor (
    cod_contrato integer NOT NULL,
    cod_empleado integer NOT NULL,
    cod_tipo_empleado integer NOT NULL,
    cod_puesto integer NOT NULL,
    fecha_inicio_contrato date NOT NULL,
    fecha_final_contrato date,
    salario numeric(12,2) NOT NULL,
    contrato_activo boolean DEFAULT true,
    observaciones character varying,
    usr_registro character varying DEFAULT 'sistema'::character varying NOT NULL,
    fec_registro timestamp without time zone NOT NULL,
    usr_modificacion character varying,
    fec_modificacion timestamp without time zone,
    cod_terminacion_contrato integer
);


ALTER TABLE public.empleados_contratos_histor OWNER TO postgres;

--
-- Name: empleados_contratos_histor_cod_contrato_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.empleados_contratos_histor_cod_contrato_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.empleados_contratos_histor_cod_contrato_seq OWNER TO postgres;

--
-- Name: empleados_contratos_histor_cod_contrato_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.empleados_contratos_histor_cod_contrato_seq OWNED BY public.empleados_contratos_histor.cod_contrato;


--
-- Name: empleados_contratos_histor_cod_terminacion_contrato_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.empleados_contratos_histor_cod_terminacion_contrato_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.empleados_contratos_histor_cod_terminacion_contrato_seq OWNER TO postgres;

--
-- Name: empleados_contratos_histor_cod_terminacion_contrato_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.empleados_contratos_histor_cod_terminacion_contrato_seq OWNED BY public.empleados_contratos_histor.cod_terminacion_contrato;


--
-- Name: eventos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.eventos (
    id integer NOT NULL,
    titulo character varying(255) NOT NULL,
    fecha_inicio timestamp without time zone NOT NULL,
    fecha_fin timestamp without time zone,
    todo_el_dia boolean DEFAULT false,
    descripcion text,
    lugar character varying(255),
    color_fondo character varying(7) DEFAULT '#3788d8'::character varying,
    color_texto character varying(7) DEFAULT '#ffffff'::character varying,
    tipo character varying(50),
    enlace text,
    recurrente boolean DEFAULT false,
    cod_empleado integer NOT NULL
);


ALTER TABLE public.eventos OWNER TO postgres;

--
-- Name: eventos_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.eventos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.eventos_id_seq OWNER TO postgres;

--
-- Name: eventos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.eventos_id_seq OWNED BY public.eventos.id;


--
-- Name: feriados; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.feriados (
    id integer NOT NULL,
    fecha date NOT NULL,
    descripcion text NOT NULL
);


ALTER TABLE public.feriados OWNER TO postgres;

--
-- Name: feriados_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.feriados_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.feriados_id_seq OWNER TO postgres;

--
-- Name: feriados_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.feriados_id_seq OWNED BY public.feriados.id;


--
-- Name: fuentes_financiamiento; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fuentes_financiamiento (
    cod_fuente_financiamiento integer NOT NULL,
    nom_fuente character varying(255) NOT NULL,
    fec_registro timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    usr_registro character varying(150) DEFAULT 'ADMIN'::character varying,
    fec_modificacion timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    usr_modificacion character varying(150)
);


ALTER TABLE public.fuentes_financiamiento OWNER TO postgres;

--
-- Name: fuentes_financiamiento_cod_fuente_financiamiento_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fuentes_financiamiento_cod_fuente_financiamiento_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.fuentes_financiamiento_cod_fuente_financiamiento_seq OWNER TO postgres;

--
-- Name: fuentes_financiamiento_cod_fuente_financiamiento_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fuentes_financiamiento_cod_fuente_financiamiento_seq OWNED BY public.fuentes_financiamiento.cod_fuente_financiamiento;


--
-- Name: horarios_laborales; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.horarios_laborales (
    cod_horario integer NOT NULL,
    nom_horario character varying(255),
    hora_inicio time without time zone,
    hora_final time without time zone,
    dias_semana jsonb,
    fec_registro timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    usr_registro character varying DEFAULT 'ADMIN'::character varying
);


ALTER TABLE public.horarios_laborales OWNER TO postgres;

--
-- Name: horarios_laborales_cod_horario_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.horarios_laborales_cod_horario_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.horarios_laborales_cod_horario_seq OWNER TO postgres;

--
-- Name: horarios_laborales_cod_horario_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.horarios_laborales_cod_horario_seq OWNED BY public.horarios_laborales.cod_horario;


--
-- Name: i_s_r_planillas; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.i_s_r_planillas (
    id integer NOT NULL,
    sueldo_inicio numeric(10,2) NOT NULL,
    sueldo_fin numeric(10,2) NOT NULL,
    porcentaje numeric(5,2) NOT NULL,
    tipo character varying(20) NOT NULL,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.i_s_r_planillas OWNER TO postgres;

--
-- Name: i_s_r_planillas_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.i_s_r_planillas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.i_s_r_planillas_id_seq OWNER TO postgres;

--
-- Name: i_s_r_planillas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.i_s_r_planillas_id_seq OWNED BY public.i_s_r_planillas.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: modulos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.modulos (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL
);


ALTER TABLE public.modulos OWNER TO postgres;

--
-- Name: modulos_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.modulos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.modulos_id_seq OWNER TO postgres;

--
-- Name: modulos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.modulos_id_seq OWNED BY public.modulos.id;


--
-- Name: municipios; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.municipios (
    cod_municipio integer NOT NULL,
    cod_depto integer NOT NULL,
    nom_municipio character varying(150) NOT NULL,
    codigo character varying(5) NOT NULL
);


ALTER TABLE public.municipios OWNER TO postgres;

--
-- Name: municipios_cod_municipio_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.municipios_cod_municipio_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.municipios_cod_municipio_seq OWNER TO postgres;

--
-- Name: municipios_cod_municipio_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.municipios_cod_municipio_seq OWNED BY public.municipios.cod_municipio;


--
-- Name: niveles_educativos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.niveles_educativos (
    cod_nivel_educativo integer NOT NULL,
    nom_nivel character varying,
    descripcion text,
    fec_registro timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    usr_registro character varying DEFAULT 'ADMIN'::character varying,
    fec_modificacion timestamp without time zone,
    usr_modificacion character varying NOT NULL
);


ALTER TABLE public.niveles_educativos OWNER TO postgres;

--
-- Name: niveles_educativos_cod_nivel_educativo_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.niveles_educativos_cod_nivel_educativo_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.niveles_educativos_cod_nivel_educativo_seq OWNER TO postgres;

--
-- Name: niveles_educativos_cod_nivel_educativo_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.niveles_educativos_cod_nivel_educativo_seq OWNED BY public.niveles_educativos.cod_nivel_educativo;


--
-- Name: oficinas; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.oficinas (
    cod_oficina integer NOT NULL,
    cod_municipio integer NOT NULL,
    direccion character varying(255),
    nom_oficina character varying(255),
    a_cargo character varying(255),
    num_telefono character varying(20),
    fec_registro timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    usr_registro character varying(150) DEFAULT 'ADMIN'::character varying,
    fec_modificacion timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    usr_modificacion character varying(255),
    direccion_corta character varying,
    asignable_empleados boolean DEFAULT true
);


ALTER TABLE public.oficinas OWNER TO postgres;

--
-- Name: oficinas_cod_oficina_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.oficinas_cod_oficina_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.oficinas_cod_oficina_seq OWNER TO postgres;

--
-- Name: oficinas_cod_oficina_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.oficinas_cod_oficina_seq OWNED BY public.oficinas.cod_oficina;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- Name: password_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_tokens (
    id integer NOT NULL,
    user_id integer NOT NULL,
    token character varying(100) NOT NULL,
    expires_at timestamp without time zone NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.password_tokens OWNER TO postgres;

--
-- Name: password_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.password_tokens_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.password_tokens_id_seq OWNER TO postgres;

--
-- Name: password_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.password_tokens_id_seq OWNED BY public.password_tokens.id;


--
-- Name: permisos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.permisos (
    id integer NOT NULL,
    rol_id integer NOT NULL,
    modulo_id integer NOT NULL,
    tiene_acceso boolean DEFAULT false NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    puede_crear boolean DEFAULT false,
    puede_actualizar boolean DEFAULT false,
    puede_eliminar boolean DEFAULT false
);


ALTER TABLE public.permisos OWNER TO postgres;

--
-- Name: permisos_cod_permiso_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.permisos_cod_permiso_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.permisos_cod_permiso_seq OWNER TO postgres;

--
-- Name: permisos_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.permisos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.permisos_id_seq OWNER TO postgres;

--
-- Name: permisos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.permisos_id_seq OWNED BY public.permisos.id;


--
-- Name: personas; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.personas (
    cod_persona integer NOT NULL,
    genero public.genero_enum NOT NULL,
    estado_civil public.estado_civil_enum NOT NULL,
    nombre_completo character varying(255) NOT NULL,
    fec_nacimiento date,
    lugar_nacimiento character varying(100),
    nacionalidad character varying(100),
    dni character varying(20),
    foto_persona character varying(200),
    fec_registro timestamp without time zone NOT NULL,
    fec_modificacion timestamp without time zone,
    usr_modificacion character varying(100),
    usr_registro character varying(50) DEFAULT 'admin'::character varying,
    rtn character varying(20)
);


ALTER TABLE public.personas OWNER TO postgres;

--
-- Name: personas_cod_persona_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.personas_cod_persona_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.personas_cod_persona_seq OWNER TO postgres;

--
-- Name: personas_cod_persona_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.personas_cod_persona_seq OWNED BY public.personas.cod_persona;


--
-- Name: planillas; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.planillas (
    id integer NOT NULL,
    cod_persona integer NOT NULL,
    dd integer DEFAULT 0 NOT NULL,
    dt integer DEFAULT 0 NOT NULL,
    salario_bruto numeric(10,2) DEFAULT 0.00 NOT NULL,
    ihss numeric(10,2) DEFAULT 0.00,
    isr numeric(10,2) DEFAULT 0.00,
    injupemp numeric(10,2) DEFAULT 0.00,
    impuesto_vecinal numeric(10,2) DEFAULT 0.00,
    dias_descargados integer DEFAULT 0,
    injupemp_reingresos numeric(10,2) DEFAULT 0.00,
    injupemp_prestamos numeric(10,2) DEFAULT 0.00,
    prestamo_banco_atlantida numeric(10,2) DEFAULT 0.00,
    pagos_deducibles numeric(10,2) DEFAULT 0.00,
    colegio_admon_empresas numeric(10,2) DEFAULT 0.00,
    cuota_coop_elga numeric(10,2) DEFAULT 0.00,
    total_deducciones numeric(10,2),
    total_a_pagar numeric(10,2),
    creado_en timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.planillas OWNER TO postgres;

--
-- Name: planillas_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.planillas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.planillas_id_seq OWNER TO postgres;

--
-- Name: planillas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.planillas_id_seq OWNED BY public.planillas.id;


--
-- Name: puestos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.puestos (
    cod_puesto integer NOT NULL,
    nom_puesto character varying NOT NULL,
    fec_registro timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    usr_registro character varying DEFAULT 'ADMIN'::character varying,
    cod_fuente_financiamiento integer NOT NULL,
    funciones_puesto character varying,
    sueldo_base numeric
);


ALTER TABLE public.puestos OWNER TO postgres;

--
-- Name: puestos_cod_fuente_financiamiento_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.puestos_cod_fuente_financiamiento_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.puestos_cod_fuente_financiamiento_seq OWNER TO postgres;

--
-- Name: puestos_cod_fuente_financiamiento_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.puestos_cod_fuente_financiamiento_seq OWNED BY public.puestos.cod_fuente_financiamiento;


--
-- Name: puestos_cod_puesto_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.puestos_cod_puesto_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.puestos_cod_puesto_seq OWNER TO postgres;

--
-- Name: puestos_cod_puesto_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.puestos_cod_puesto_seq OWNED BY public.puestos.cod_puesto;


--
-- Name: regionales; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.regionales (
    cod_regional integer NOT NULL,
    cod_municipio integer NOT NULL,
    direccion character varying(255),
    nom_regional character varying(255),
    fec_registro timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    usr_registro character varying(150) DEFAULT 'ADMIN'::character varying,
    fec_modificacion timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    usr_modificacion character varying(255)
);


ALTER TABLE public.regionales OWNER TO postgres;

--
-- Name: regionales_cod_regional_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.regionales_cod_regional_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.regionales_cod_regional_seq OWNER TO postgres;

--
-- Name: regionales_cod_regional_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.regionales_cod_regional_seq OWNED BY public.regionales.cod_regional;


--
-- Name: role_user; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.role_user (
    id integer NOT NULL,
    user_id bigint NOT NULL,
    role_id integer NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.role_user OWNER TO postgres;

--
-- Name: role_user_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.role_user_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.role_user_id_seq OWNER TO postgres;

--
-- Name: role_user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.role_user_id_seq OWNED BY public.role_user.id;


--
-- Name: roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.roles (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    estado character varying(20) DEFAULT 'Activo'::character varying
);


ALTER TABLE public.roles OWNER TO postgres;

--
-- Name: roles_cod_rol_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.roles_cod_rol_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.roles_cod_rol_seq OWNER TO postgres;

--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.roles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.roles_id_seq OWNER TO postgres;

--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- Name: saldo_vacaciones; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.saldo_vacaciones (
    id integer NOT NULL,
    cod_empleado integer NOT NULL,
    anio integer NOT NULL,
    dias_disponibles integer DEFAULT 15 NOT NULL,
    dias_tomados integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.saldo_vacaciones OWNER TO postgres;

--
-- Name: saldo_vacaciones_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.saldo_vacaciones_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.saldo_vacaciones_id_seq OWNER TO postgres;

--
-- Name: saldo_vacaciones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.saldo_vacaciones_id_seq OWNED BY public.saldo_vacaciones.id;


--
-- Name: secciones_area; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.secciones_area (
    cod_seccion integer NOT NULL,
    cod_jefe integer,
    nom_seccion character varying(255),
    fec_registro timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    usr_registro character varying(150) DEFAULT 'ADMIN'::character varying,
    fec_modificacion timestamp without time zone,
    usr_modificacion character varying(255)
);


ALTER TABLE public.secciones_area OWNER TO postgres;

--
-- Name: secciones_area_cod_seccion_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.secciones_area_cod_seccion_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.secciones_area_cod_seccion_seq OWNER TO postgres;

--
-- Name: secciones_area_cod_seccion_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.secciones_area_cod_seccion_seq OWNED BY public.secciones_area.cod_seccion;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: solicitudes_observaciones; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.solicitudes_observaciones (
    cod_solicitud integer NOT NULL,
    observacion text,
    fec_registro timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    usr_registro character varying(150) NOT NULL,
    fec_modificacion timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    usr_modificacion character varying(150)
);


ALTER TABLE public.solicitudes_observaciones OWNER TO postgres;

--
-- Name: telefonos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.telefonos (
    cod_telefono integer NOT NULL,
    cod_persona integer NOT NULL,
    numero character varying(20) NOT NULL,
    fec_registro timestamp without time zone NOT NULL,
    usr_registro character varying,
    telefono_emergencia character varying(20),
    nombre_contacto_emergencia character varying(100),
    fec_modificacion timestamp without time zone,
    usr_modificacion character varying(50)
);


ALTER TABLE public.telefonos OWNER TO postgres;

--
-- Name: telefonos_cod_telefono_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.telefonos_cod_telefono_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.telefonos_cod_telefono_seq OWNER TO postgres;

--
-- Name: telefonos_cod_telefono_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.telefonos_cod_telefono_seq OWNED BY public.telefonos.cod_telefono;


--
-- Name: telefonos_cod_telefono_seq1; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.telefonos ALTER COLUMN cod_telefono ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.telefonos_cod_telefono_seq1
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: tipo_terminacion_contrato; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tipo_terminacion_contrato (
    cod_terminacion_contrato integer NOT NULL,
    nombre_tipo_term_contrato character varying(50) NOT NULL,
    descripcion text,
    cod_contrato integer NOT NULL
);


ALTER TABLE public.tipo_terminacion_contrato OWNER TO postgres;

--
-- Name: tipo_terminacion_contrato_cod_contrato_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tipo_terminacion_contrato_cod_contrato_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tipo_terminacion_contrato_cod_contrato_seq OWNER TO postgres;

--
-- Name: tipo_terminacion_contrato_cod_contrato_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tipo_terminacion_contrato_cod_contrato_seq OWNED BY public.tipo_terminacion_contrato.cod_contrato;


--
-- Name: tipo_terminacion_contrato_cod_terminacion_contrato_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tipo_terminacion_contrato_cod_terminacion_contrato_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tipo_terminacion_contrato_cod_terminacion_contrato_seq OWNER TO postgres;

--
-- Name: tipo_terminacion_contrato_cod_terminacion_contrato_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tipo_terminacion_contrato_cod_terminacion_contrato_seq OWNED BY public.tipo_terminacion_contrato.cod_terminacion_contrato;


--
-- Name: tipos_empleados; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tipos_empleados (
    cod_tipo_empleado integer NOT NULL,
    nom_tipo character varying(255),
    descripcion text,
    fec_registro timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    usr_registro character varying DEFAULT 'ADMIN'::character varying
);


ALTER TABLE public.tipos_empleados OWNER TO postgres;

--
-- Name: tipos_empleados_cod_tipo_empleado_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tipos_empleados_cod_tipo_empleado_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tipos_empleados_cod_tipo_empleado_seq OWNER TO postgres;

--
-- Name: tipos_empleados_cod_tipo_empleado_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tipos_empleados_cod_tipo_empleado_seq OWNED BY public.tipos_empleados.cod_tipo_empleado;


--
-- Name: tipos_modalidades; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tipos_modalidades (
    cod_tipo_modalidad integer NOT NULL,
    nom_tipo character varying(255),
    fec_registro timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    usr_registro character varying DEFAULT 'ADMIN'::character varying
);


ALTER TABLE public.tipos_modalidades OWNER TO postgres;

--
-- Name: tipos_modalidades_cod_tipo_modalidad_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tipos_modalidades_cod_tipo_modalidad_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tipos_modalidades_cod_tipo_modalidad_seq OWNER TO postgres;

--
-- Name: tipos_modalidades_cod_tipo_modalidad_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tipos_modalidades_cod_tipo_modalidad_seq OWNED BY public.tipos_modalidades.cod_tipo_modalidad;


--
-- Name: titulos_empleados; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.titulos_empleados (
    cod_titulo integer NOT NULL,
    titulo character varying(50) NOT NULL,
    abreviatura character varying(30) NOT NULL,
    descripcion character varying(70) NOT NULL
);


ALTER TABLE public.titulos_empleados OWNER TO postgres;

--
-- Name: titulos_empleados_cod_titulo_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.titulos_empleados_cod_titulo_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.titulos_empleados_cod_titulo_seq OWNER TO postgres;

--
-- Name: titulos_empleados_cod_titulo_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.titulos_empleados_cod_titulo_seq OWNED BY public.titulos_empleados.cod_titulo;


--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    estado character varying(20) DEFAULT 'ACTIVO'::character varying,
    cod_persona integer
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: usuarios; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.usuarios (
    cod_usuario integer NOT NULL,
    cod_persona integer NOT NULL,
    cod_rol integer NOT NULL,
    cod_estado integer DEFAULT 5 NOT NULL,
    uid character varying(100) NOT NULL,
    nom_usuario character varying(50) NOT NULL,
    email character varying(150) NOT NULL,
    contrasena character varying(255) NOT NULL,
    primer_ingreso integer DEFAULT 0 NOT NULL,
    fec_ultima_conexion timestamp with time zone,
    fec_vencimiento date,
    tfa_isactive boolean DEFAULT false,
    tfa_secretkey character varying(75),
    tfa_qr text,
    fec_registro timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    usr_registro character varying(100) NOT NULL,
    fec_modificacion timestamp without time zone,
    usr_modificacion character varying(100),
    cod_empleado integer NOT NULL
);


ALTER TABLE public.usuarios OWNER TO postgres;

--
-- Name: usuarios_cod_empleado_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.usuarios_cod_empleado_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.usuarios_cod_empleado_seq OWNER TO postgres;

--
-- Name: usuarios_cod_empleado_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.usuarios_cod_empleado_seq OWNED BY public.usuarios.cod_empleado;


--
-- Name: usuarios_cod_usuario_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.usuarios_cod_usuario_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.usuarios_cod_usuario_seq OWNER TO postgres;

--
-- Name: usuarios_cod_usuario_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.usuarios_cod_usuario_seq OWNED BY public.usuarios.cod_usuario;


--
-- Name: vacaciones; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.vacaciones (
    id integer NOT NULL,
    cod_empleado integer NOT NULL,
    fecha_inicio date NOT NULL,
    fecha_fin date NOT NULL,
    dias_solicitados integer,
    estado public.estado_vacacion DEFAULT 'PENDIENTE'::public.estado_vacacion NOT NULL,
    comentario text,
    fecha_solicitud timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    aprobado_por integer
);


ALTER TABLE public.vacaciones OWNER TO postgres;

--
-- Name: vacaciones_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.vacaciones_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vacaciones_id_seq OWNER TO postgres;

--
-- Name: vacaciones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.vacaciones_id_seq OWNED BY public.vacaciones.id;


--
-- Name: vacaciones_periodos_historial; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.vacaciones_periodos_historial (
    cod_periodo integer NOT NULL,
    cod_empleado integer,
    fecha_inicio date NOT NULL,
    fecha_final date NOT NULL,
    dias_otorgados integer NOT NULL,
    dias_tomados integer DEFAULT 0,
    dias_adelantados integer DEFAULT 0,
    dias_acumulados integer DEFAULT 0,
    periodo_vigente boolean DEFAULT true,
    dias_vencidos integer DEFAULT 0,
    usr_registro character varying(100),
    fec_registro timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    usr_modificacion character varying(100),
    fec_modificacion timestamp without time zone
);


ALTER TABLE public.vacaciones_periodos_historial OWNER TO postgres;

--
-- Name: vacaciones_periodos_historial_cod_periodo_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.vacaciones_periodos_historial_cod_periodo_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vacaciones_periodos_historial_cod_periodo_seq OWNER TO postgres;

--
-- Name: vacaciones_periodos_historial_cod_periodo_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.vacaciones_periodos_historial_cod_periodo_seq OWNED BY public.vacaciones_periodos_historial.cod_periodo;


--
-- Name: auditoria id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.auditoria ALTER COLUMN id SET DEFAULT nextval('public.auditoria_id_seq'::regclass);


--
-- Name: backup id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.backup ALTER COLUMN id SET DEFAULT nextval('public.backup_id_seq'::regclass);


--
-- Name: comprobante_pago cod_tipo_comprobante; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comprobante_pago ALTER COLUMN cod_tipo_comprobante SET DEFAULT nextval('public.comprobante_pago_cod_tipo_comprobante_seq'::regclass);


--
-- Name: comprobante_pago cod_empleado; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comprobante_pago ALTER COLUMN cod_empleado SET DEFAULT nextval('public.comprobante_pago_cod_empleado_seq'::regclass);


--
-- Name: control_asistencia id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.control_asistencia ALTER COLUMN id SET DEFAULT nextval('public.control_asistencia_id_seq'::regclass);


--
-- Name: control_asistencia cod_empleado; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.control_asistencia ALTER COLUMN cod_empleado SET DEFAULT nextval('public.control_asistencia_cod_empleado_seq'::regclass);


--
-- Name: datos_empresa cod_empresa; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.datos_empresa ALTER COLUMN cod_empresa SET DEFAULT nextval('public.datos_empresa_cod_empresa_seq'::regclass);


--
-- Name: datos_empresa cod_municipio; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.datos_empresa ALTER COLUMN cod_municipio SET DEFAULT nextval('public.datos_empresa_cod_municipio_seq'::regclass);


--
-- Name: departamentos cod_depto; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departamentos ALTER COLUMN cod_depto SET DEFAULT nextval('public.departamentos_cod_depto_seq'::regclass);


--
-- Name: empleados cod_empleado; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empleados ALTER COLUMN cod_empleado SET DEFAULT nextval('public.empleados_cod_empleado_seq'::regclass);


--
-- Name: empleados_contratos_histor cod_contrato; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empleados_contratos_histor ALTER COLUMN cod_contrato SET DEFAULT nextval('public.empleados_contratos_histor_cod_contrato_seq'::regclass);


--
-- Name: empleados_contratos_histor cod_terminacion_contrato; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empleados_contratos_histor ALTER COLUMN cod_terminacion_contrato SET DEFAULT nextval('public.empleados_contratos_histor_cod_terminacion_contrato_seq'::regclass);


--
-- Name: eventos id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.eventos ALTER COLUMN id SET DEFAULT nextval('public.eventos_id_seq'::regclass);


--
-- Name: feriados id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.feriados ALTER COLUMN id SET DEFAULT nextval('public.feriados_id_seq'::regclass);


--
-- Name: fuentes_financiamiento cod_fuente_financiamiento; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuentes_financiamiento ALTER COLUMN cod_fuente_financiamiento SET DEFAULT nextval('public.fuentes_financiamiento_cod_fuente_financiamiento_seq'::regclass);


--
-- Name: horarios_laborales cod_horario; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.horarios_laborales ALTER COLUMN cod_horario SET DEFAULT nextval('public.horarios_laborales_cod_horario_seq'::regclass);


--
-- Name: i_s_r_planillas id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_s_r_planillas ALTER COLUMN id SET DEFAULT nextval('public.i_s_r_planillas_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: modulos id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.modulos ALTER COLUMN id SET DEFAULT nextval('public.modulos_id_seq'::regclass);


--
-- Name: municipios cod_municipio; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.municipios ALTER COLUMN cod_municipio SET DEFAULT nextval('public.municipios_cod_municipio_seq'::regclass);


--
-- Name: niveles_educativos cod_nivel_educativo; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.niveles_educativos ALTER COLUMN cod_nivel_educativo SET DEFAULT nextval('public.niveles_educativos_cod_nivel_educativo_seq'::regclass);


--
-- Name: oficinas cod_oficina; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.oficinas ALTER COLUMN cod_oficina SET DEFAULT nextval('public.oficinas_cod_oficina_seq'::regclass);


--
-- Name: password_tokens id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_tokens ALTER COLUMN id SET DEFAULT nextval('public.password_tokens_id_seq'::regclass);


--
-- Name: permisos id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permisos ALTER COLUMN id SET DEFAULT nextval('public.permisos_id_seq'::regclass);


--
-- Name: personas cod_persona; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personas ALTER COLUMN cod_persona SET DEFAULT nextval('public.personas_cod_persona_seq'::regclass);


--
-- Name: planillas id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.planillas ALTER COLUMN id SET DEFAULT nextval('public.planillas_id_seq'::regclass);


--
-- Name: puestos cod_puesto; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.puestos ALTER COLUMN cod_puesto SET DEFAULT nextval('public.puestos_cod_puesto_seq'::regclass);


--
-- Name: puestos cod_fuente_financiamiento; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.puestos ALTER COLUMN cod_fuente_financiamiento SET DEFAULT nextval('public.puestos_cod_fuente_financiamiento_seq'::regclass);


--
-- Name: regionales cod_regional; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.regionales ALTER COLUMN cod_regional SET DEFAULT nextval('public.regionales_cod_regional_seq'::regclass);


--
-- Name: role_user id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_user ALTER COLUMN id SET DEFAULT nextval('public.role_user_id_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- Name: saldo_vacaciones id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.saldo_vacaciones ALTER COLUMN id SET DEFAULT nextval('public.saldo_vacaciones_id_seq'::regclass);


--
-- Name: secciones_area cod_seccion; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.secciones_area ALTER COLUMN cod_seccion SET DEFAULT nextval('public.secciones_area_cod_seccion_seq'::regclass);


--
-- Name: tipo_terminacion_contrato cod_terminacion_contrato; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipo_terminacion_contrato ALTER COLUMN cod_terminacion_contrato SET DEFAULT nextval('public.tipo_terminacion_contrato_cod_terminacion_contrato_seq'::regclass);


--
-- Name: tipo_terminacion_contrato cod_contrato; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipo_terminacion_contrato ALTER COLUMN cod_contrato SET DEFAULT nextval('public.tipo_terminacion_contrato_cod_contrato_seq'::regclass);


--
-- Name: tipos_empleados cod_tipo_empleado; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipos_empleados ALTER COLUMN cod_tipo_empleado SET DEFAULT nextval('public.tipos_empleados_cod_tipo_empleado_seq'::regclass);


--
-- Name: tipos_modalidades cod_tipo_modalidad; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipos_modalidades ALTER COLUMN cod_tipo_modalidad SET DEFAULT nextval('public.tipos_modalidades_cod_tipo_modalidad_seq'::regclass);


--
-- Name: titulos_empleados cod_titulo; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.titulos_empleados ALTER COLUMN cod_titulo SET DEFAULT nextval('public.titulos_empleados_cod_titulo_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: usuarios cod_usuario; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios ALTER COLUMN cod_usuario SET DEFAULT nextval('public.usuarios_cod_usuario_seq'::regclass);


--
-- Name: usuarios cod_empleado; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios ALTER COLUMN cod_empleado SET DEFAULT nextval('public.usuarios_cod_empleado_seq'::regclass);


--
-- Name: vacaciones id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vacaciones ALTER COLUMN id SET DEFAULT nextval('public.vacaciones_id_seq'::regclass);


--
-- Name: vacaciones_periodos_historial cod_periodo; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vacaciones_periodos_historial ALTER COLUMN cod_periodo SET DEFAULT nextval('public.vacaciones_periodos_historial_cod_periodo_seq'::regclass);


--
-- Data for Name: auditoria; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.auditoria (id, tabla_afectada, accion, id_registro_afectado, datos_antes, datos_despues, usuario_id, ip_origen, navegador, fecha) FROM stdin;
\.


--
-- Data for Name: backup; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.backup (id, nombre_archivo, ruta_archivo, fecha, usuario_id, tipo_backup, tamano, estado) FROM stdin;
12	backup_solo_bd_20250814_002621.zip	C:\\backups\\miapp\\backup_solo_bd_20250814_002621.zip	2025-08-14 00:26:21.779407-06	45	solo_bd	21500	listo
14	backup_solo_bd_20250814_004012.zip	C:\\backups\\miapp\\backup_solo_bd_20250814_004012.zip	2025-08-14 00:40:12.604216-06	45	solo_bd	21578	listo
15	backup_solo_bd_20250814_010307.zip	C:\\backups\\miapp\\backup_solo_bd_20250814_010307.zip	2025-08-14 01:03:08.795633-06	45	solo_bd	21609	listo
16	backup_solo_bd_20250814_011148.zip	C:\\backups\\miapp\\backup_solo_bd_20250814_011148.zip	2025-08-14 01:11:49.758218-06	45	solo_bd	21642	listo
17	backup_solo_bd_20250814_013055.zip	C:\\backups\\miapp\\backup_solo_bd_20250814_013055.zip	2025-08-14 01:30:57.268116-06	45	solo_bd	21668	listo
18	backup_solo_bd_20250814_013418.zip	C:\\backups\\miapp\\backup_solo_bd_20250814_013418.zip	2025-08-14 01:34:19.169025-06	45	solo_bd	21694	listo
\.


--
-- Data for Name: comprobante_pago; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.comprobante_pago (cod_tipo_comprobante, tipo_comprobante, icon, cod_empleado) FROM stdin;
\.


--
-- Data for Name: control_asistencia; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) FROM stdin;
1	1	2025-05-30	08:30:00	17:00:00	Entrada	Asistencia normal	2025-05-30 21:12:39.72089
8	1	2025-07-24	17:07:24	17:08:04	Salida	Horas incompletas	2025-07-24 17:08:04.40876
18	84	2025-07-25	13:53:50.824026	13:54:36.336072	Salida	Horas incompletas	2025-07-25 13:54:36.336072
19	84	2025-07-26	20:02:24.22418	20:03:00.220553	Salida	Horas incompletas	2025-07-25 20:03:00.220553
20	84	2025-07-27	18:17:39.841614	18:19:05.62235	Salida	Horas incompletas	2025-07-26 18:19:05.62235
21	84	2025-07-28	22:17:20.600516	22:18:52.943028	Salida	Horas incompletas	2025-07-27 22:18:52.943028
22	84	2025-08-04	20:50:48.23654	20:51:46.799662	Salida	Horas incompletas	2025-08-03 20:51:46.799662
23	84	2025-08-05	13:43:51.688778	13:44:27.597731	Salida	Horas incompletas	2025-08-05 13:44:27.597731
32	84	2025-08-08	23:30:16.440999	\N	Entrada		2025-08-08 23:30:16.440999
33	84	2025-08-09	00:03:38.335926	01:10:59.804515	Salida	Horas incompletas	2025-08-09 01:10:59.804515
80	84	2025-08-01	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:35:52.059039
81	84	2025-08-02	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:35:52.059039
82	84	2025-08-03	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:35:52.059039
83	84	2025-08-04	08:00:00	19:00:00	Entrada	Horas extra	2025-08-12 10:35:52.059039
84	84	2025-08-05	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:35:52.059039
85	84	2025-08-06	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:35:52.059039
86	84	2025-08-07	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:35:52.059039
87	84	2025-08-08	08:00:00	19:00:00	Entrada	Horas extra	2025-08-12 10:35:52.059039
88	84	2025-08-09	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:35:52.059039
95	84	2025-08-16	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:35:52.059039
96	84	2025-08-17	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:35:52.059039
97	84	2025-08-18	08:00:00	19:00:00	Entrada	Horas extra	2025-08-12 10:35:52.059039
98	84	2025-08-19	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:35:52.059039
99	84	2025-08-20	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:35:52.059039
100	84	2025-08-21	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:35:52.059039
101	84	2025-08-22	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:35:52.059039
102	84	2025-08-23	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:35:52.059039
103	84	2025-08-24	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:35:52.059039
104	84	2025-08-25	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:35:52.059039
105	84	2025-08-26	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:35:52.059039
106	84	2025-08-27	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:35:52.059039
107	84	2025-08-28	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:35:52.059039
108	84	2025-08-29	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:35:52.059039
109	84	2025-08-30	08:00:00	19:00:00	Entrada	Horas extra	2025-08-12 10:35:52.059039
110	1	2025-08-01	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:37:14.459842
111	1	2025-08-02	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:37:14.459842
112	1	2025-08-03	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:37:14.459842
113	1	2025-08-04	08:00:00	19:00:00	Entrada	Horas extra	2025-08-12 10:37:14.459842
114	1	2025-08-05	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:37:14.459842
115	1	2025-08-06	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:37:14.459842
116	1	2025-08-07	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:37:14.459842
117	1	2025-08-08	08:00:00	19:00:00	Entrada	Horas extra	2025-08-12 10:37:14.459842
118	1	2025-08-09	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:37:14.459842
123	1	2025-08-14	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:37:14.459842
124	1	2025-08-15	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:37:14.459842
125	1	2025-08-16	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:37:14.459842
126	1	2025-08-17	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:37:14.459842
127	1	2025-08-18	08:00:00	19:00:00	Entrada	Horas extra	2025-08-12 10:37:14.459842
128	1	2025-08-19	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:37:14.459842
129	1	2025-08-20	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:37:14.459842
130	1	2025-08-21	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:37:14.459842
131	1	2025-08-22	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:37:14.459842
132	1	2025-08-23	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:37:14.459842
133	1	2025-08-24	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:37:14.459842
134	1	2025-08-25	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:37:14.459842
135	1	2025-08-26	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:37:14.459842
136	1	2025-08-27	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:37:14.459842
137	1	2025-08-28	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:37:14.459842
138	1	2025-08-29	08:00:00	17:00:00	Entrada	\N	2025-08-12 10:37:14.459842
139	1	2025-08-30	08:00:00	19:00:00	Entrada	Horas extra	2025-08-12 10:37:14.459842
140	84	2025-08-12	11:01:43.272256	11:02:39.619348	Salida	Horas incompletas	2025-08-12 11:02:39.619348
141	84	2025-08-13	22:26:28.294499	\N	Entrada		2025-08-13 22:26:28.294499
\.


--
-- Data for Name: datos_empresa; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.datos_empresa (cod_empresa, nom_empresa, contacto, direccion, pais, ciudad, departamento, cod_postal, email, num_fijo, num_celular, fax, pag_web, fec_registro, usr_registro, cod_municipio) FROM stdin;
1	Dirección de Asuntos Disciplinarios Policiales (DIDADPOL)	Unidad de Tecnologías de Información	Centro Cívico gubernamental, Torre 1, Piso 19 y 20, Boulevard Juan Pablo II, Esquina República de Corea, Tegucigaba, M.D.C., Honduras, C.A.	Honduras	Tegucigaba	Francisco Morazan	11101	sistema@didadpol.gob.hn	2242-8645	9999-8889	818-978-7102	https://www.didadpol.gob.hn	2025-08-12 21:23:25.792369	Daniel Oyuela estrada 	110
\.


--
-- Data for Name: departamentos; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.departamentos (cod_depto, nom_depto, zona) FROM stdin;
1	ATLÁNTIDA	\N
2	COLÓN	\N
3	COMAYAGUA	\N
4	COPÁN	\N
5	CORTÉS	\N
6	CHOLUTECA	\N
7	EL PARAÍSO	\N
8	FRANCISCO MORAZÁN	\N
9	GRACIAS A DIOS	\N
10	INTIBUCÁ	\N
11	ISLAS DE LA BAHÍA	\N
12	LA PAZ	\N
13	LEMPIRA	\N
14	OCOTEPEQUE	\N
15	OLANCHO	\N
16	SANTA BÁRBARA	\N
17	VALLE	\N
18	YORO	\N
\.


--
-- Data for Name: direcciones; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.direcciones (cod_direccion, cod_persona, direccion, fec_registro, usr_registro, cod_municipio, fec_modificacion, usr_modificacion) FROM stdin;
1	1	TGU	2025-05-30 22:51:08.987256	ADMIN	\N	\N	\N
92	112	AMARATECA	2025-07-20 14:40:52.349824	\N	310	2025-08-11 14:32:37.51626	\N
98	118	AMARATECA	2025-08-12 21:28:58.943249	\N	110	\N	\N
\.


--
-- Data for Name: empleados; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.empleados (cod_empleado, cod_persona, cod_tipo_modalidad, cod_puesto, cod_oficina, cod_nivel_educativo, cod_horario, es_jefe, fecha_contratacion, fecha_notificacion, cod_tipo_terminacion, email_trabajo, fec_registro, usr_registro, fec_modificacion, usr_modificacion, cod_tipo_empleado) FROM stdin;
1	1	1	1	1	1	1	f	2025-05-30	\N	\N	j@d.hn	2025-05-30 00:00:00	ADMIN	\N	\N	\N
84	112	3	3	4	3	1	\N	2002-02-17	\N	\N	danieloyuela51@gmail.com	2025-07-20 14:40:52.349824	\N	2025-08-11 14:32:37.51626	\N	1
90	118	3	3	10	3	1	\N	2025-08-12	\N	\N	danieloyuela27@gmail.com	2025-08-12 21:28:58.943249	\N	\N	\N	1
\.


--
-- Data for Name: empleados_contratos_histor; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.empleados_contratos_histor (cod_contrato, cod_empleado, cod_tipo_empleado, cod_puesto, fecha_inicio_contrato, fecha_final_contrato, salario, contrato_activo, observaciones, usr_registro, fec_registro, usr_modificacion, fec_modificacion, cod_terminacion_contrato) FROM stdin;
90	84	1	3	2002-12-12	2020-12-12	25000.00	t	\N	sistema	2025-07-20 14:40:52.349824	\N	2025-08-11 14:32:37.51626	\N
96	90	1	3	2025-08-13	2026-08-12	20000.00	t	\N	sistema	2025-08-12 21:28:58.943249	\N	\N	\N
\.


--
-- Data for Name: eventos; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.eventos (id, titulo, fecha_inicio, fecha_fin, todo_el_dia, descripcion, lugar, color_fondo, color_texto, tipo, enlace, recurrente, cod_empleado) FROM stdin;
4	Cita médica	2025-06-11 10:00:00	2025-06-11 11:00:00	f	Chequeo general	Clínica Central	#00a65a	#ffffff	cita médica	\N	f	1
43	Reunión de seguimiento	2025-08-14 08:00:00	2025-08-14 09:00:00	t			#28a745	#ffffff			f	1
44	Reunión de seguimiento	2025-08-07 08:00:00	2025-08-07 09:00:00	f			#dc3545	#ffffff			f	1
59	Graduacion	2025-08-12 08:00:00	2025-08-12 09:00:00	f	\N	\N	#007bff	#ffffff	\N	\N	f	84
\.


--
-- Data for Name: feriados; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.feriados (id, fecha, descripcion) FROM stdin;
1	2025-01-01	Año Nuevo
\.


--
-- Data for Name: fuentes_financiamiento; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.fuentes_financiamiento (cod_fuente_financiamiento, nom_fuente, fec_registro, usr_registro, fec_modificacion, usr_modificacion) FROM stdin;
1	FONDOS NACIONALES	2025-05-26 11:28:00.063334	ADMIN	2025-05-26 11:28:00.063334	\N
2	INL	2025-05-26 11:28:00.063334	ADMIN	2025-05-26 11:28:00.063334	\N
\.


--
-- Data for Name: horarios_laborales; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.horarios_laborales (cod_horario, nom_horario, hora_inicio, hora_final, dias_semana, fec_registro, usr_registro) FROM stdin;
1	Horario de Oficina	07:30:00	15:30:00	["Lunes", "Martes", "Miércoles", "Jueves", "Viernes"]	2025-05-26 08:00:00	admin
8	Horario de Oficina	10:24:00	10:24:00	["Lunes", "Martes", "Miércoles", "Jueves"]	2025-08-12 10:24:38.795444	DANIEL EDUARDO OYUELA ESTRADA
9	Horario de Oficina	08:00:00	13:00:00	["Lunes", "Martes", "Miércoles", "Jueves", "Viernes"]	2025-08-12 21:30:07.333114	DANIEL EDUARDO OYUELA ESTRADA
\.


--
-- Data for Name: i_s_r_planillas; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.i_s_r_planillas (id, sueldo_inicio, sueldo_fin, porcentaje, tipo, created_at, updated_at) FROM stdin;
1	0.01	21457.76	0.00	ISR	2025-08-11 11:49:00	2025-08-11 11:49:00
2	21457.77	30969.88	15.00	ISR	2025-08-11 11:49:00	2025-08-11 11:49:00
3	30969.89	67604.36	20.00	ISR	2025-08-11 11:49:00	2025-08-11 11:49:00
4	67604.37	99999999.99	25.00	ISR	2025-08-11 11:49:00	2025-08-11 11:49:00
5	0.01	500000.00	0.30	Vecinal	2025-08-11 11:49:00	2025-08-11 11:49:00
6	500000.01	10000000.00	0.40	Vecinal	2025-08-11 11:49:00	2025-08-11 11:49:00
7	10000000.01	20000000.00	0.30	Vecinal	2025-08-11 11:49:00	2025-08-11 11:49:00
8	20000000.01	30000000.00	0.20	Vecinal	2025-08-11 11:49:00	2025-08-11 11:49:00
9	30000000.01	99999999.99	0.15	Vecinal	2025-08-11 11:49:00	2025-08-11 11:49:00
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
\.


--
-- Data for Name: modulos; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.modulos (id, nombre) FROM stdin;
10	CALENDARIO
11	RECURSOS HUMANOS
12	EMPLEADOS
13	USUARIO
14	PLANILLA
15	ASISTENCIA
16	CONTROL DE ASISTENCIA
\.


--
-- Data for Name: municipios; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) FROM stdin;
9	2	TRUJILLO	0201
10	2	BALFATE	0202
11	2	IRIONA	0203
12	2	LIMÓN	0204
13	2	SABA	0205
14	2	SANTA FE	0206
15	2	SANTA ROSA DE AGUÁN	0207
16	2	SONAGUERA	0208
17	2	TOCOA	0209
18	2	BONITO ORIENTAL	0210
19	3	COMAYAGUA	0301
20	3	AJUTERIQUE	0302
21	3	EL ROSARIO	0303
22	3	ESQUÍAS	0304
23	3	HUMUYA	0305
24	3	LA LIBERTAD	0306
25	3	LAMANÍ	0307
26	3	LA TRINIDAD	0308
27	3	LEJAMANÍ	0309
28	3	MEAMBAR	0310
29	3	MINAS DE ORO	0311
30	3	OJOS DE AGUA	0312
31	3	SAN JERÓNIMO	0313
32	3	SAN JOSÉ DE COMAYAGUA	0314
33	3	SAN JOSÉ DEL POTRERO	0315
34	3	SAN LUIS	0316
35	3	SAN SEBASTIÁN	0317
36	3	SIGUATEPEQUE	0318
37	3	VILLA DE SAN ANTONIO	0319
38	3	LAS LAJAS	0320
39	3	TAULABÉ	0321
40	4	SANTA ROSA DE COPÁN	0401
41	4	CABAÑAS	0402
42	4	CONCEPCIÓN	0403
43	4	COPÁN RUINAS	0404
44	4	CORQUÍN	0405
45	4	CUCUYAGUA	0406
46	4	DOLORES	0407
47	4	DULCE NOMBRE	0408
48	4	EL PARAÍSO	0409
49	4	FLORIDA	0410
50	4	LA JIGUA	0411
51	4	LA UNIÓN	0412
52	4	NUEVA ARCADIA	0413
53	4	SAN AGUSTÍN	0414
54	4	SAN ANTONIO	0415
55	4	SAN JERÓNIMO	0416
56	4	SAN JOSÉ	0417
57	4	SAN JUAN DE OPOA	0418
58	4	SAN NICOLÁS	0419
59	4	SAN PEDRO	0420
60	4	SANTA RITA	0421
61	4	TRINIDAD DE COPÁN	0422
62	4	VERACRUZ	0423
63	5	SAN PEDRO SULA	0501
64	5	CHOLOMA	0502
65	5	OMOA	0503
66	5	PIMIENTA	0504
67	5	POTRERILLOS	0505
68	5	PUERTO CORTÉS	0506
69	5	SAN ANTONIO DE CORTÉS	0507
70	5	SAN FRANCISCO DE YOJOA	0508
71	5	SAN MANUEL	0509
72	5	SANTA CRUZ DE YOJOA	0510
73	5	VILLANUEVA	0511
74	5	LA LIMA	0512
75	6	CHOLUTECA	0601
76	6	APACILAGUA	0602
77	6	CONCEPCIÓN DE MARÍA	0603
78	6	DUYURE	0604
79	6	EL CORPUS	0605
80	6	EL TRIUNFO	0606
81	6	MARCOVIA	0607
82	6	MOROLICA	0608
83	6	NAMASIGUE	0609
84	6	OROCUINA	0610
85	6	PESPIRE	0611
86	6	SAN ANTONIO DE FLORES	0612
87	6	SAN ISIDRO	0613
88	6	SAN JOSÉ	0614
89	6	SAN MARCOS DE COLÓN	0615
90	6	SANTA ANA DE YUSGUARE	0616
91	7	YUSCARÁN	0701
92	7	ALAUCA	0702
93	7	DANLÍ	0703
94	7	EL PARAÍSO	0704
95	7	GUINOPE	0705
96	7	JACALEAPA	0706
97	7	LUIRE	0707
98	7	MOROCELÍ	0708
99	7	OROPOLÍ	0709
100	7	POTRERILLOS	0710
101	7	SAN ANTONIO DE FLORES	0711
102	7	SAN LUCAS	0712
103	7	SAN MATÍAS	0713
104	7	SOLEDAD	0714
105	7	TEUPASENTI	0715
106	7	TEXIGUAT	0716
107	7	VADO ANCHO	0717
108	7	YAUYUPE	0718
109	7	TROJES	0719
110	8	DISTRITO CENTRAL	0801
111	8	ALUBARÉN	0802
113	8	CEDROS	0803
112	8	CURARÉN	0804
114	8	EL PORVENIR	0805
115	8	GUAMACA	0806
116	8	LA LIBERTAD	0807
117	8	LA VENTA	0808
118	8	LEPATERIQUE	0809
119	8	MARAITA	0810
120	8	MARALE	0811
121	8	NUEVA ARMENIA	0812
122	8	OJOJONA	0813
123	8	ORICA	0814
124	8	REITOCA	0815
125	8	SABANAGRANDE	0816
126	8	SAN ANTONIO DE ORIENTE	0817
127	8	SAN BUENAVENTURA	0818
128	8	SAN IGNACIO	0819
129	8	SAN JUAN DE FLORES	0820
130	8	SAN MIGUELITO	0821
131	8	SANTA ANA	0822
132	8	SANTA LUCÍA	0823
133	8	TALANGA	0824
134	8	TATUMBLA	0825
135	8	VALLE DE ÁNGELES	0826
136	8	VILLA DE SAN FRANCISCO	0827
137	8	VALLECILLO	0828
138	9	PUERTO LEMPIRA	0901
139	9	BRUS LAGUNA	0902
140	9	AHUAS	0903
141	9	JUAN FRANCISCO BULNES	0904
142	9	RAMÓN VILLEDA MORALES	0905
143	9	WAMPUSIRPE	0906
144	10	LA ESPERANZA	1001
145	10	CAMASCA	1002
146	10	COLOMONCAGUA	1003
147	10	CONCEPCIÓN	1004
148	10	DOLORES	1005
149	10	INTIBUCA	1006
150	10	JESÚS DE OTORO	1007
151	10	MAGDALENA	1008
152	10	MASAGUARA	1009
153	10	SAN ANTONIO	1010
154	10	SAN ISIDRO	1011
155	10	SAN JUAN	1012
156	10	SAN MARCOS DE LA SIERRA	1013
157	10	SAN MIGUEL GUANCAPLA	1014
158	10	SANTA LUCÍA	1015
159	10	YAMARANGUILLA	1016
160	10	SAN FRANCISCO DE OPALA	1017
1	1	LA CEIBA	0101
8	1	EL PORVENIR	0102
7	1	ESPARTA	0103
3	1	JUTIAPA	0104
4	1	LA MASICA	0105
5	1	SAN FRANCISCO	0106
2	1	TELA	0107
6	1	ARIZONA	0108
161	11	ROATAN	1101
162	11	GUANAJA	1102
163	11	JOSÉ SANTOS GUARDIOLA	1103
164	11	UTILA	1104
165	12	LA PAZ	1201
166	12	AGUANQUETERIQUE	1202
167	12	CABAÑAS	1203
168	12	CANE	1204
169	12	CHINACLA	1205
170	12	GUAJIQUIRO	1206
171	12	LAUTERIQUE	1207
172	12	MARCALA	1208
173	12	MERCEDES DE ORIENTE	1209
174	12	OPATORO	1210
175	12	SAN ANTONIO DEL NORTE	1211
176	12	SAN JOSÉ	1212
177	12	SAN JUAN	1213
178	12	SAN PEDRO DE TUTULE	1214
179	12	SANTA ANA	1215
180	12	SANTA ELENA	1216
181	12	SANTA MARÍA	1217
182	12	SANTIAGO DE PURINGLA	1218
183	12	YARULA	1219
184	13	GRACIAS	1301
185	13	BELÉN	1302
186	13	CANDELARIA	1303
187	13	COLOLACA	1304
188	13	ERANDIQUE	1305
189	13	GUALCINCE	1306
190	13	GUARITA	1307
191	13	LA CAMPA	1308
192	13	LA IGUALA	1309
193	13	LAS FLORES	1310
194	13	LA UNIÓN	1311
195	13	LA VIRTUD	1312
196	13	LEPAERA	1313
197	13	MAPULACA	1314
198	13	PIRAERA	1315
199	13	SAN ANDRÉS	1316
200	13	SAN FRANCISCO	1317
201	13	SAN JUAN GUARITA	1318
202	13	SAN MANUEL COLOHETE	1319
203	13	SAN RAFAEL	1320
204	13	SAN SEBASTIÁN	1321
205	13	SANTA CRUZ	1322
206	13	TALGUA	1323
207	13	TAMBLA	1324
208	13	TOMALÁ	1325
209	13	VALLADOLID	1326
210	13	VIRGINIA	1327
211	13	SAN MARCOS DE CAIQUÍN	1328
238	15	Juticalpa	1501
239	15	Campamento	1502
240	15	Catacamas	1503
241	15	Concordia	1504
242	15	Dulce Nombre de Culmí	1505
243	15	El Rosario	1506
244	15	Esquipulas del Norte	1507
245	15	Gualaco	1508
246	15	Guarizama	1509
247	15	Guata	1510
248	15	Guayape	1511
249	15	Jano	1512
250	15	La Unión	1513
251	15	Mangulile	1514
252	15	Manto	1515
253	15	Salamá	1516
254	15	San Esteban	1517
255	15	San Francisco de Becerra	1518
256	15	San Francisco de La Paz	1519
257	15	Santa María del Real	1520
258	15	Silca	1521
259	15	Yocón	1522
260	15	Patuca	1523
261	16	Santa Bárbara	1601
262	16	Arada	1602
263	16	Atima	1603
264	16	Azacualpa	1604
265	16	Ceguaca	1605
266	16	Colinas	1606
267	16	Concepción del Norte	1607
268	16	Concepción del Sur	1608
269	16	Chinda	1609
270	16	El Níspero	1610
271	16	Gualala	1611
272	16	Ilama	1612
273	16	Macuelizo	1613
274	16	Naranjito	1614
275	16	Nueva Celilac	1615
276	16	Petoa	1616
277	16	Protección	1617
278	16	Quimistán	1618
279	16	San Francisco de Ojuera	1619
280	16	San Luis	1620
281	16	San Marcos	1621
282	16	San Nicolás	1622
283	16	San Pedro Zacapa	1623
284	16	Santa Rita	1624
285	16	San Vicente Centenario	1625
286	16	Trinidad	1626
287	16	Las Vegas	1627
288	16	Nueva Frontera	1628
289	17	Nacaome	1701
290	17	Alianza	1702
291	17	Amapala	1703
292	17	Aramecina	1704
293	17	Caridad	1705
294	17	Goascorán	1706
295	17	Langue	1707
296	17	San Francisco de Coray	1708
297	17	San Lorenzo	1709
298	18	Yoro	1801
299	18	Arenal	1802
300	18	El Negrito	1803
301	18	El Progreso	1804
302	18	Jocón	1805
303	18	Morazán	1806
304	18	Olanchito	1807
305	18	Santa Rita	1808
306	18	Sulaco	1809
307	18	Victoria	1810
308	18	Yorito	1811
309	14	Nueva Ocotepeque	1401
310	14	Belén Gualcho	1402
311	14	Concepción	1403
312	14	Dolores Merendón	1404
313	14	Fraternidad	1405
314	14	La Encarnación	1406
315	14	La Labor	1407
316	14	Lucerna	1408
317	14	Mercedes	1409
318	14	San Fernando	1410
319	14	San Francisco del Valle	1411
320	14	San Jorge	1412
321	14	San Marcos	1413
322	14	Santa Fé	1414
323	14	Sensenti	1415
324	14	Sinuapa	1416
\.


--
-- Data for Name: niveles_educativos; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.niveles_educativos (cod_nivel_educativo, nom_nivel, descripcion, fec_registro, usr_registro, fec_modificacion, usr_modificacion) FROM stdin;
1	Primaria	Educación básica primaria (1° a 6° grado)	2025-05-26 00:00:00	ADMIN	2025-05-26 00:00:00	ADMIN
2	Secundaria	Educación secundaria o bachillerato (7° a 12° grado)	2025-05-26 00:00:00	ADMIN	2025-05-26 00:00:00	ADMIN
3	CARRERA UNIVERSITARIA	CARRERA	2025-07-17 00:00:00	admin	2025-07-17 18:22:54.393	admin
7	RRHH	TEMPORAL	2025-08-12 16:24:53.999	admin	2025-08-12 16:24:53.999	admin
\.


--
-- Data for Name: oficinas; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.oficinas (cod_oficina, cod_municipio, direccion, nom_oficina, a_cargo, num_telefono, fec_registro, usr_registro, fec_modificacion, usr_modificacion, direccion_corta, asignable_empleados) FROM stdin;
1	110	Centro Cívico Gubernamental, Torre 1, Piso 20, Boulevard Juan Pablo II, Esquina República de Corea, Tegucigalpa, M.D.C., Honduras, C...	Oficina Principal	Silvia Marcela Amaya Escoto	2242-8645	2025-05-26 09:49:03	ADMIN	2025-05-26 09:49:03	ADMIN	Tegucigalpa, M.D.C.	t
2	63	Col. Trejo 12 y 13 calle, 23 avenida, S.O. San Pedro Sula, Cortes.	Regional Norte	Susana Patricia Rodriguez	2556-5454	2025-05-31 10:12:20.027088	ADMIN	\N	\N	San Pedro Sula, Cortes	t
4	110	El Sauce, Tegucigalpa, M.D.C., Honduras.	El Sauce	\N	\N	2025-05-31 10:12:20.027088	ADMIN	\N	\N	El Sauce	f
8	110	Colonia El Centro, calle principal	Oficina Central	daniel oyuela	2234-5678	2025-07-17 02:13:10.00417	admin	2025-07-17 02:23:54.73699	\N	El Centro	t
10	93	tegucigalpa	central	DANIEL OYUELA  ESTRADA	+504 9475-5664	2025-07-17 02:34:31.138856	admin	2025-08-10 18:53:10.163757	\N	central	t
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: password_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_tokens (id, user_id, token, expires_at, created_at, updated_at) FROM stdin;
49	45	6a228821-e4b1-4e0c-9c06-f6e8c4b34e99	2025-08-13 11:10:14.441	2025-08-13 10:10:14.459744	2025-08-13 10:10:14.459744
\.


--
-- Data for Name: permisos; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.permisos (id, rol_id, modulo_id, tiene_acceso, created_at, updated_at, puede_crear, puede_actualizar, puede_eliminar) FROM stdin;
60	69	15	t	2025-08-10 01:09:47.521134	2025-08-10 01:09:47.521134	f	f	f
53	69	10	t	2025-06-29 17:02:33.479911	2025-08-10 01:09:47.532831	f	f	f
61	69	16	f	2025-08-10 01:09:47.540092	2025-08-10 01:09:47.540092	f	f	f
54	69	12	t	2025-06-29 17:02:33.489861	2025-08-10 01:09:47.545608	f	f	f
55	69	14	f	2025-06-29 17:02:33.49457	2025-08-10 01:09:47.55061	f	f	f
56	69	11	t	2025-06-29 17:02:33.499352	2025-08-10 01:09:47.556722	f	f	f
57	69	13	f	2025-06-29 17:02:33.504399	2025-08-10 01:09:47.561747	f	f	f
58	57	15	t	2025-08-10 01:06:49.529837	2025-08-12 21:36:10.098964	f	f	f
48	57	10	t	2025-06-25 16:31:20	2025-08-12 21:36:10.104113	f	f	f
59	57	16	t	2025-08-10 01:06:49.548962	2025-08-12 21:36:10.108749	f	f	f
50	57	12	t	2025-06-25 16:31:20	2025-08-12 21:36:10.112417	f	f	f
52	57	14	t	2025-06-25 16:31:20	2025-08-12 21:36:10.11653	f	f	f
49	57	11	t	2025-06-25 16:31:20	2025-08-12 21:36:10.120192	f	f	f
51	57	13	t	2025-06-25 16:31:20	2025-08-12 21:36:10.124017	t	t	t
\.


--
-- Data for Name: personas; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.personas (cod_persona, genero, estado_civil, nombre_completo, fec_nacimiento, lugar_nacimiento, nacionalidad, dni, foto_persona, fec_registro, fec_modificacion, usr_modificacion, usr_registro, rtn) FROM stdin;
1	Femenino	Soltero	SILVIA MARCELA AMAYA ESCOTO	1984-01-06	Se ignora	Hondureño (a)	0801-1984-20353	\N	2025-05-26 10:00:52	2025-05-26 10:00:52	ADMIN	\N	08011992054321
112	Masculino	Soltero	DANIEL EDUARDO OYUELA ESTRADA	2002-02-17	VALLE DE ANGELES	HONDUREÑO A	0801-2002-08924	\N	2025-07-20 14:40:52.349824	2025-08-11 14:32:37.51626	\N	\N	08011985012345
118	Masculino	Soltero	DANIEL EDUARDO OYUELA ESTRADA	2002-04-17	VALLE DE ANGELES	HONDUREÑO A	0801-2000-08102	\N	2025-08-12 21:28:58.943249	\N	\N	\N	\N
\.


--
-- Data for Name: planillas; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.planillas (id, cod_persona, dd, dt, salario_bruto, ihss, isr, injupemp, impuesto_vecinal, dias_descargados, injupemp_reingresos, injupemp_prestamos, prestamo_banco_atlantida, pagos_deducibles, colegio_admon_empresas, cuota_coop_elga, total_deducciones, total_a_pagar, creado_en) FROM stdin;
\.


--
-- Data for Name: puestos; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.puestos (cod_puesto, nom_puesto, fec_registro, usr_registro, cod_fuente_financiamiento, funciones_puesto, sueldo_base) FROM stdin;
1	DIRECTORA	2025-05-26 08:01:05.698475	ADMIN	1	Dirigir y supervisar todas las operaciones de la institución según las leyes de Honduras	30000.00
2	ASISTENTE EJECUTIVO DE DIRECCIÓN	2025-05-26 08:01:05.698475	ADMIN	1	Apoyo administrativo a la dirección, coordinación de agendas y seguimiento de proyectos	16000.00
10	ASISTENTE DE AREA	2025-07-17 00:00:00	DANIEL OYUELA ESTRADA	1	Atender llamadas, manejar archivos y coordinar reuniones.	10500
3	ASISTENTE ADMINISTRATIVO	2025-07-17 17:30:58.354	admin	1	Atender llamadas, manejar archivos y coordinar reuniones.	10500
\.


--
-- Data for Name: regionales; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.regionales (cod_regional, cod_municipio, direccion, nom_regional, fec_registro, usr_registro, fec_modificacion, usr_modificacion) FROM stdin;
1	110	Centro Cívico Gubernamental, Torre 1, Piso 20, Boulevard Juan Pablo II, Esquina República de Corea, Tegucigalpa, M.D.C., Honduras, C.A.	Oficina Principal	2025-05-30 14:11:26.187578	ADMIN	\N	\N
2	63	Colonia Trejo, 4ta etapa, 21 calle, 24 avenida. San Pedro Sula, Cortes.	Regional Norte	2025-05-31 10:17:22.805816	ADMIN	\N	\N
\.


--
-- Data for Name: role_user; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.role_user (id, user_id, role_id, created_at) FROM stdin;
22	39	57	2025-06-21 00:14:35
23	40	57	2025-06-21 00:14:42
21	38	69	2025-06-25 08:12:42
27	45	57	2025-07-24 00:47:50
24	41	57	2025-07-27 00:16:39
18	34	69	2025-08-10 07:09:16
31	49	57	2025-08-13 03:34:45
\.


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.roles (id, nombre, descripcion, created_at, updated_at, estado) FROM stdin;
57	ADMINISTRADOR	CONTROL TOTAL	2025-06-17 17:30:08	2025-06-20 19:34:05	ACTIVO
72	OBSERVADOR	TEMPORAL	2025-06-25 08:00:57	2025-06-25 08:00:57	ACTIVO
73	PRUEBA	TEMPORAL	2025-07-14 23:11:59	2025-07-26 22:46:30	ACTIVO
69	EMPLEADO	TEMPORAL	2025-06-21 03:01:56	2025-07-27 00:16:14	ACTIVO
62	GESTION	TEMPORAL	2025-06-18 23:13:20	2025-08-11 01:04:38	ACTIVO
78	RRHH	TEMPORAL	2025-08-11 01:10:35	2025-08-11 01:10:35	ACTIVO
\.


--
-- Data for Name: saldo_vacaciones; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.saldo_vacaciones (id, cod_empleado, anio, dias_disponibles, dias_tomados) FROM stdin;
1	86	2002	381	0
2	84	2025	30	0
4	1	2025	26	0
3	86	2025	43	0
\.


--
-- Data for Name: secciones_area; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.secciones_area (cod_seccion, cod_jefe, nom_seccion, fec_registro, usr_registro, fec_modificacion, usr_modificacion) FROM stdin;
1	\N	DIRECCIÓN	2025-05-31 10:08:50.092933	ADMIN	\N	\N
2	\N	ADMINISTRACIÓN FINANCIERA	2025-05-31 10:08:50.092933	ADMIN	\N	\N
3	\N	SECRETARÍA GENERAL	2025-05-31 10:08:50.092933	ADMIN	\N	\N
4	\N	INVESTIGACIÓN	2025-05-31 10:08:50.092933	ADMIN	\N	\N
5	\N	SERVICIOS LEGALES	2025-05-31 10:08:50.092933	ADMIN	\N	\N
6	\N	PREVENCIÓN, EVALUACIÓN Y CERTIFICACIÓN	2025-05-31 10:08:50.092933	ADMIN	\N	\N
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
52TlXqBT1q3g27RV7RrGftTp0h32fCA6CAJIa1MZ	45	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36	YTo1OntzOjY6Il90b2tlbiI7czo0MDoiQ245Q3diVGhjT0lQMmdWRzFPNmYwSWIweHJEOTE5RFpvZXcxZ2xTbyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NDU7czoxMDoibm9tYnJlX3JvbCI7czoxMzoiQURNSU5JU1RSQURPUiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9iYWNrdXBzIjt9fQ==	1755157214
\.


--
-- Data for Name: solicitudes_observaciones; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.solicitudes_observaciones (cod_solicitud, observacion, fec_registro, usr_registro, fec_modificacion, usr_modificacion) FROM stdin;
1	Solicitud de vacaciones creada	2025-05-30 10:00:00	ADMIN	\N	\N
\.


--
-- Data for Name: telefonos; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.telefonos (cod_telefono, cod_persona, numero, fec_registro, usr_registro, telefono_emergencia, nombre_contacto_emergencia, fec_modificacion, usr_modificacion) FROM stdin;
1	1	9539-8069	2025-05-30 19:13:31.634121	ADMIN	\N	\N	\N	\N
85	112	+504 9475-5664	2025-07-20 14:40:52.349824	\N	+504 8989-2020	LESTER ARMANDO OYUELA ESTRADA	2025-08-11 14:32:37.51626	\N
91	118	+504 9475-5664	2025-08-12 21:28:58.943249	\N	+504 8989-2020	LESTER ARMANDO OYUELA ESTRADA	\N	\N
\.


--
-- Data for Name: tipo_terminacion_contrato; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tipo_terminacion_contrato (cod_terminacion_contrato, nombre_tipo_term_contrato, descripcion, cod_contrato) FROM stdin;
1	Dimisión	El empleado ha renunciado voluntariamente.	1
2	Despido	El empleado ha sido despedido por la empresa.	1
3	Jubilación	El empleado se ha jubilado.	1
4	Terminación de Contrato	El contrato del empleado ha llegado a su fin.	1
\.


--
-- Data for Name: tipos_empleados; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tipos_empleados (cod_tipo_empleado, nom_tipo, descripcion, fec_registro, usr_registro) FROM stdin;
1	Permanente	Empleados con contrato permanente	2025-05-26 00:00:00	admin
2	Contrato	Empleados contratados por un periodo determinado	2025-05-26 00:00:00	admin
4	Por Servicios Profesionales	Empleados contratados para realizar servicios específicos	2025-05-26 00:00:00	admin
5	Contratista	Empleados que trabajan bajo contrato para proyectos específicos	2025-05-26 00:00:00	admin
6	Eventual	Empleados que se contratan por eventos específicos o necesidades temporales	2025-05-26 00:00:00	admin
7	Consultor	Profesional externo contratado para proporcionar asesoría especializada y soluciones en áreas específicas de la organización.	2025-05-26 00:00:00	admin
8	Pasante	Empleado en proceso de aprendizaje y formación práctica en el entorno laboral, generalmente un estudiante o recién graduado	2025-05-26 00:00:00	admin
\.


--
-- Data for Name: tipos_modalidades; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tipos_modalidades (cod_tipo_modalidad, nom_tipo, fec_registro, usr_registro) FROM stdin;
1	Presencial	2025-05-26 00:00:00	admin
2	Remote	2025-05-26 00:00:00	admin
3	Híbrido	2025-05-26 00:00:00	admin
4	Por Proyecto	2025-05-26 00:00:00	admin
\.


--
-- Data for Name: titulos_empleados; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.titulos_empleados (cod_titulo, titulo, abreviatura, descripcion) FROM stdin;
1	ABOGADO	ABG.	PROFESIONAL DE LEYES
5	ARQUITECTO	ARQ.	DISEÑADOR DE EDIFICIOS
4	DOCTOR	DR.	MÉDICO
3	INGENIERO	ING.	PROFESIONAL TÉCNICO
2	LICENCIADO	LIC.	GRADUADO UNIVERSITARIO
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, estado, cod_persona) FROM stdin;
38	NANCY KARINA CHAVARRIA TACHE	chavarriakarina57@gmail.com	\N	$2y$10$xxAWnYjEPQAci2nPfZm9xOsmj3V70SCtCpUK4TnEMPBQemYO6fHOa	\N	2025-06-20 23:13:32	2025-06-25 08:12:42	ACTIVO	\N
39	MAGDIEL SARAI ORELLANA VALLADARES	Magdielorellana0@gmail.com	\N	$2y$10$4E7mZalt8/BiOX7Jma7xjuxEE56jtmLfbaB2XOS9L7VgNjRzmN1Ym	\N	2025-06-20 23:16:48	2025-06-21 00:14:35	ACTIVO	\N
40	MARIA ELIZABETH AVILA TURCIOS	elizabethavila891@gmail.com	\N	$2y$10$0av0pP3.yLk19ZqM2Y4afeihziooMziwmq5kLlyGoAB665cAY1RPu	\N	2025-06-20 23:17:34	2025-06-21 00:14:42	ACTIVO	\N
45	DANIEL EDUARDO OYUELA ESTRADA	doyuela@didadpol.gob.hn	2025-07-24 00:47:26	$2b$10$tLecCGJWYexh5O4s99gZseK24YBqkmVtX3dIirfUx.pwI0KtEPWJ.	\N	2025-07-24 00:47:02	2025-07-24 00:47:50	ACTIVO	112
41	BRANDY JULEISY TORRES MARTINEZ	juleisy2003tm@gmail.com	\N	$2y$10$vqUguw9di4WU5nIJi.5QauJbb1T2uE9hmAk0qw2mp5nqtInf7pVBC	\N	2025-06-20 23:18:14	2025-07-27 00:16:39	INACTIVO	\N
34	DANIEL EDUARDO OYUELA ESTRADA	danieloyuela51@gmail.com	2025-06-20 22:47:24	$2b$10$ZwvJ8NRutx/bkiys12J1tuPT./cjIkB7qHMzpYjWxwTRss8nUBI7G	\N	2025-06-20 22:46:24	2025-08-10 07:09:16	ACTIVO	\N
49	DANIEL EDUARDO OYUELA ESTRADA	doyuela1@didadpol.gob.hn	2025-08-13 03:33:15	$2b$10$h2D9J5E3/3HdRxLSK9YXfeXZh1blKqAx9moHVqKETsW5/1lZxN97S	\N	2025-08-13 03:31:39	2025-08-13 03:34:45	ACTIVO	118
\.


--
-- Data for Name: usuarios; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.usuarios (cod_usuario, cod_persona, cod_rol, cod_estado, uid, nom_usuario, email, contrasena, primer_ingreso, fec_ultima_conexion, fec_vencimiento, tfa_isactive, tfa_secretkey, tfa_qr, fec_registro, usr_registro, fec_modificacion, usr_modificacion, cod_empleado) FROM stdin;
1	1	1	1	c82f2d67-5849-499e-bb59-024fb442ca12	ADMIN	sistema@didacpol.gob.hn	$2a$10$baj64wqN9agYWQ20tnk7uQju8t6aATSj18eem0Y	49	2025-05-30 11:38:06.61503-06	2025-05-30	f	\N	\N	2025-05-30 09:38:07.460884	SAMAYA	2025-05-30 22:50:12.247096	ADMIN	1
\.


--
-- Data for Name: vacaciones; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.vacaciones (id, cod_empleado, fecha_inicio, fecha_fin, dias_solicitados, estado, comentario, fecha_solicitud, aprobado_por) FROM stdin;
11	84	2025-08-12	2025-08-17	\N	PENDIENTE	solicitud personal	2025-08-13 03:25:27	\N
\.


--
-- Data for Name: vacaciones_periodos_historial; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.vacaciones_periodos_historial (cod_periodo, cod_empleado, fecha_inicio, fecha_final, dias_otorgados, dias_tomados, dias_adelantados, dias_acumulados, periodo_vigente, dias_vencidos, usr_registro, fec_registro, usr_modificacion, fec_modificacion) FROM stdin;
1	1	2025-05-30	2025-05-30	12	12	0	0	t	0	ADMIN	2025-05-30 13:33:34.929807	\N	\N
\.


--
-- Name: auditoria_cod_auditoria_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.auditoria_cod_auditoria_seq', 1, false);


--
-- Name: auditoria_cod_empresa_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.auditoria_cod_empresa_seq', 1, false);


--
-- Name: auditoria_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.auditoria_id_seq', 1, false);


--
-- Name: backup_cod_usuario_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.backup_cod_usuario_seq', 1, false);


--
-- Name: backup_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.backup_id_seq', 18, true);


--
-- Name: comprobante_pago_cod_empleado_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.comprobante_pago_cod_empleado_seq', 1, false);


--
-- Name: comprobante_pago_cod_tipo_comprobante_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.comprobante_pago_cod_tipo_comprobante_seq', 1, false);


--
-- Name: control_asistencia_cod_empleado_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.control_asistencia_cod_empleado_seq', 1, false);


--
-- Name: control_asistencia_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.control_asistencia_id_seq', 141, true);


--
-- Name: datos_empresa_cod_empresa_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.datos_empresa_cod_empresa_seq', 1, false);


--
-- Name: datos_empresa_cod_municipio_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.datos_empresa_cod_municipio_seq', 1, false);


--
-- Name: departamentos_cod_depto_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.departamentos_cod_depto_seq', 1, false);


--
-- Name: direcciones_cod_direccion_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.direcciones_cod_direccion_seq', 1, true);


--
-- Name: direcciones_cod_direccion_seq1; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.direcciones_cod_direccion_seq1', 98, true);


--
-- Name: empleados_cod_empleado_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.empleados_cod_empleado_seq', 90, true);


--
-- Name: empleados_contratos_histor_cod_contrato_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.empleados_contratos_histor_cod_contrato_seq', 96, true);


--
-- Name: empleados_contratos_histor_cod_terminacion_contrato_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.empleados_contratos_histor_cod_terminacion_contrato_seq', 7, true);


--
-- Name: eventos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.eventos_id_seq', 59, true);


--
-- Name: feriados_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.feriados_id_seq', 1, true);


--
-- Name: fuentes_financiamiento_cod_fuente_financiamiento_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.fuentes_financiamiento_cod_fuente_financiamiento_seq', 1, false);


--
-- Name: horarios_laborales_cod_horario_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.horarios_laborales_cod_horario_seq', 9, true);


--
-- Name: i_s_r_planillas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.i_s_r_planillas_id_seq', 9, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 3, true);


--
-- Name: modulos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.modulos_id_seq', 16, true);


--
-- Name: municipios_cod_municipio_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.municipios_cod_municipio_seq', 324, true);


--
-- Name: niveles_educativos_cod_nivel_educativo_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.niveles_educativos_cod_nivel_educativo_seq', 7, true);


--
-- Name: oficinas_cod_oficina_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.oficinas_cod_oficina_seq', 14, true);


--
-- Name: password_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.password_tokens_id_seq', 49, true);


--
-- Name: permisos_cod_permiso_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.permisos_cod_permiso_seq', 1, false);


--
-- Name: permisos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.permisos_id_seq', 61, true);


--
-- Name: personas_cod_persona_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.personas_cod_persona_seq', 118, true);


--
-- Name: planillas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.planillas_id_seq', 36, true);


--
-- Name: puestos_cod_fuente_financiamiento_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.puestos_cod_fuente_financiamiento_seq', 1, false);


--
-- Name: puestos_cod_puesto_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.puestos_cod_puesto_seq', 17, true);


--
-- Name: regionales_cod_regional_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.regionales_cod_regional_seq', 1, false);


--
-- Name: role_user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.role_user_id_seq', 31, true);


--
-- Name: roles_cod_rol_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.roles_cod_rol_seq', 1, false);


--
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.roles_id_seq', 78, true);


--
-- Name: saldo_vacaciones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.saldo_vacaciones_id_seq', 10, true);


--
-- Name: secciones_area_cod_seccion_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.secciones_area_cod_seccion_seq', 1, false);


--
-- Name: telefonos_cod_telefono_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.telefonos_cod_telefono_seq', 1, true);


--
-- Name: telefonos_cod_telefono_seq1; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.telefonos_cod_telefono_seq1', 91, true);


--
-- Name: tipo_terminacion_contrato_cod_contrato_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tipo_terminacion_contrato_cod_contrato_seq', 1, false);


--
-- Name: tipo_terminacion_contrato_cod_terminacion_contrato_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tipo_terminacion_contrato_cod_terminacion_contrato_seq', 1, false);


--
-- Name: tipos_empleados_cod_tipo_empleado_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tipos_empleados_cod_tipo_empleado_seq', 14, true);


--
-- Name: tipos_modalidades_cod_tipo_modalidad_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tipos_modalidades_cod_tipo_modalidad_seq', 1, false);


--
-- Name: titulos_empleados_cod_titulo_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.titulos_empleados_cod_titulo_seq', 9, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 49, true);


--
-- Name: usuarios_cod_empleado_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.usuarios_cod_empleado_seq', 1, false);


--
-- Name: usuarios_cod_usuario_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.usuarios_cod_usuario_seq', 1, false);


--
-- Name: vacaciones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.vacaciones_id_seq', 11, true);


--
-- Name: vacaciones_periodos_historial_cod_periodo_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.vacaciones_periodos_historial_cod_periodo_seq', 1, false);


--
-- Name: auditoria auditoria_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.auditoria
    ADD CONSTRAINT auditoria_pkey PRIMARY KEY (id);


--
-- Name: backup backup_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.backup
    ADD CONSTRAINT backup_pkey PRIMARY KEY (id);


--
-- Name: comprobante_pago comprobante_pago_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comprobante_pago
    ADD CONSTRAINT comprobante_pago_pkey PRIMARY KEY (cod_tipo_comprobante);


--
-- Name: empleados_contratos_histor contrato_unico; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empleados_contratos_histor
    ADD CONSTRAINT contrato_unico UNIQUE (cod_contrato);


--
-- Name: control_asistencia control_asistencia_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.control_asistencia
    ADD CONSTRAINT control_asistencia_pkey PRIMARY KEY (id);


--
-- Name: datos_empresa datos_empresa_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.datos_empresa
    ADD CONSTRAINT datos_empresa_pkey PRIMARY KEY (cod_empresa);


--
-- Name: departamentos departamentos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departamentos
    ADD CONSTRAINT departamentos_pkey PRIMARY KEY (cod_depto);


--
-- Name: direcciones direcciones_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.direcciones
    ADD CONSTRAINT direcciones_pkey PRIMARY KEY (cod_direccion);


--
-- Name: empleados_contratos_histor empleados_contratos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empleados_contratos_histor
    ADD CONSTRAINT empleados_contratos_pkey PRIMARY KEY (cod_empleado, cod_tipo_empleado);


--
-- Name: empleados empleados_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT empleados_pkey PRIMARY KEY (cod_empleado);


--
-- Name: eventos eventos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.eventos
    ADD CONSTRAINT eventos_pkey PRIMARY KEY (id);


--
-- Name: feriados feriados_fecha_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.feriados
    ADD CONSTRAINT feriados_fecha_key UNIQUE (fecha);


--
-- Name: feriados feriados_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.feriados
    ADD CONSTRAINT feriados_pkey PRIMARY KEY (id);


--
-- Name: fuentes_financiamiento fuentes_financiamiento_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuentes_financiamiento
    ADD CONSTRAINT fuentes_financiamiento_pkey PRIMARY KEY (cod_fuente_financiamiento);


--
-- Name: horarios_laborales horarios_laborales_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.horarios_laborales
    ADD CONSTRAINT horarios_laborales_pkey PRIMARY KEY (cod_horario);


--
-- Name: i_s_r_planillas i_s_r_planillas_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_s_r_planillas
    ADD CONSTRAINT i_s_r_planillas_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: modulos modulos_nombre_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.modulos
    ADD CONSTRAINT modulos_nombre_key UNIQUE (nombre);


--
-- Name: modulos modulos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.modulos
    ADD CONSTRAINT modulos_pkey PRIMARY KEY (id);


--
-- Name: municipios municipios_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.municipios
    ADD CONSTRAINT municipios_pkey PRIMARY KEY (cod_municipio);


--
-- Name: niveles_educativos niveles_educativos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.niveles_educativos
    ADD CONSTRAINT niveles_educativos_pkey PRIMARY KEY (cod_nivel_educativo);


--
-- Name: solicitudes_observaciones observaciones_solicitudes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.solicitudes_observaciones
    ADD CONSTRAINT observaciones_solicitudes_pkey PRIMARY KEY (cod_solicitud);


--
-- Name: oficinas oficinas_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.oficinas
    ADD CONSTRAINT oficinas_pkey PRIMARY KEY (cod_oficina);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: password_tokens password_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_tokens
    ADD CONSTRAINT password_tokens_pkey PRIMARY KEY (id);


--
-- Name: password_tokens password_tokens_token_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_tokens
    ADD CONSTRAINT password_tokens_token_key UNIQUE (token);


--
-- Name: permisos permisos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permisos
    ADD CONSTRAINT permisos_pkey PRIMARY KEY (id);


--
-- Name: personas personas_dni_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personas
    ADD CONSTRAINT personas_dni_key UNIQUE (dni);


--
-- Name: personas personas_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personas
    ADD CONSTRAINT personas_pkey PRIMARY KEY (cod_persona);


--
-- Name: planillas planillas_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.planillas
    ADD CONSTRAINT planillas_pkey PRIMARY KEY (id);


--
-- Name: puestos puestos_nom_puesto_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.puestos
    ADD CONSTRAINT puestos_nom_puesto_key UNIQUE (nom_puesto);


--
-- Name: puestos puestos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.puestos
    ADD CONSTRAINT puestos_pkey PRIMARY KEY (cod_puesto);


--
-- Name: regionales regionales_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.regionales
    ADD CONSTRAINT regionales_pkey PRIMARY KEY (cod_regional);


--
-- Name: role_user role_user_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_user
    ADD CONSTRAINT role_user_pkey PRIMARY KEY (id);


--
-- Name: roles roles_nombre_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_nombre_key UNIQUE (nombre);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: saldo_vacaciones saldo_vacaciones_cod_empleado_anio_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.saldo_vacaciones
    ADD CONSTRAINT saldo_vacaciones_cod_empleado_anio_key UNIQUE (cod_empleado, anio);


--
-- Name: saldo_vacaciones saldo_vacaciones_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.saldo_vacaciones
    ADD CONSTRAINT saldo_vacaciones_pkey PRIMARY KEY (id);


--
-- Name: secciones_area secciones_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.secciones_area
    ADD CONSTRAINT secciones_pkey PRIMARY KEY (cod_seccion);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: telefonos telefonos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.telefonos
    ADD CONSTRAINT telefonos_pkey PRIMARY KEY (cod_telefono);


--
-- Name: tipo_terminacion_contrato tipo_terminacion_contrato_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipo_terminacion_contrato
    ADD CONSTRAINT tipo_terminacion_contrato_pkey PRIMARY KEY (cod_terminacion_contrato);


--
-- Name: tipos_empleados tipos_empleados_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipos_empleados
    ADD CONSTRAINT tipos_empleados_pkey PRIMARY KEY (cod_tipo_empleado);


--
-- Name: tipos_modalidades tipos_modalidades_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipos_modalidades
    ADD CONSTRAINT tipos_modalidades_pkey PRIMARY KEY (cod_tipo_modalidad);


--
-- Name: titulos_empleados titulos_empleados_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.titulos_empleados
    ADD CONSTRAINT titulos_empleados_pkey PRIMARY KEY (cod_titulo);


--
-- Name: role_user unique_user; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_user
    ADD CONSTRAINT unique_user UNIQUE (user_id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: usuarios usuarios_nom_usuario_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_nom_usuario_key UNIQUE (nom_usuario);


--
-- Name: usuarios usuarios_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (cod_usuario);


--
-- Name: usuarios usuarios_uid_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_uid_key UNIQUE (uid);


--
-- Name: vacaciones_periodos_historial vacaciones_periodos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vacaciones_periodos_historial
    ADD CONSTRAINT vacaciones_periodos_pkey PRIMARY KEY (cod_periodo);


--
-- Name: vacaciones vacaciones_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vacaciones
    ADD CONSTRAINT vacaciones_pkey PRIMARY KEY (id);


--
-- Name: idx_backup_estado; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_backup_estado ON public.backup USING btree (estado);


--
-- Name: idx_backup_fecha; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_backup_fecha ON public.backup USING btree (fecha);


--
-- Name: idx_backup_tipo; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_backup_tipo ON public.backup USING btree (tipo_backup);


--
-- Name: idx_vacaciones_empleado_fecha; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_vacaciones_empleado_fecha ON public.vacaciones USING btree (cod_empleado, fecha_inicio, fecha_fin);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: vacaciones tr_vacaciones_saldo_asistencia; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER tr_vacaciones_saldo_asistencia AFTER INSERT OR DELETE OR UPDATE ON public.vacaciones FOR EACH ROW EXECUTE FUNCTION public.tg_vacaciones_saldo_asistencia();


--
-- Name: backup backup_usuario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.backup
    ADD CONSTRAINT backup_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: municipios cdo_munic_depto; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.municipios
    ADD CONSTRAINT cdo_munic_depto FOREIGN KEY (cod_depto) REFERENCES public.departamentos(cod_depto);


--
-- Name: datos_empresa cod_municipio; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.datos_empresa
    ADD CONSTRAINT cod_municipio FOREIGN KEY (cod_municipio) REFERENCES public.municipios(cod_municipio);


--
-- Name: telefonos cod_telef_persona; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.telefonos
    ADD CONSTRAINT cod_telef_persona FOREIGN KEY (cod_persona) REFERENCES public.personas(cod_persona);


--
-- Name: comprobante_pago comprobante_cod_empleado_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comprobante_pago
    ADD CONSTRAINT comprobante_cod_empleado_fkey FOREIGN KEY (cod_empleado) REFERENCES public.empleados(cod_empleado);


--
-- Name: empleados_contratos_histor contrato_cod_puesto_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empleados_contratos_histor
    ADD CONSTRAINT contrato_cod_puesto_fkey FOREIGN KEY (cod_puesto) REFERENCES public.puestos(cod_puesto);


--
-- Name: direcciones direccion_cod_persona_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.direcciones
    ADD CONSTRAINT direccion_cod_persona_fkey FOREIGN KEY (cod_persona) REFERENCES public.personas(cod_persona);


--
-- Name: empleados_contratos_histor empleados_cod_empleado_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empleados_contratos_histor
    ADD CONSTRAINT empleados_cod_empleado_fkey FOREIGN KEY (cod_empleado) REFERENCES public.empleados(cod_empleado);


--
-- Name: empleados empleados_cod_horario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT empleados_cod_horario_fkey FOREIGN KEY (cod_horario) REFERENCES public.horarios_laborales(cod_horario);


--
-- Name: empleados empleados_cod_nivel_educativo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT empleados_cod_nivel_educativo_fkey FOREIGN KEY (cod_nivel_educativo) REFERENCES public.niveles_educativos(cod_nivel_educativo);


--
-- Name: empleados empleados_cod_oficina_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT empleados_cod_oficina_fkey FOREIGN KEY (cod_oficina) REFERENCES public.oficinas(cod_oficina);


--
-- Name: empleados empleados_cod_persona_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT empleados_cod_persona_fkey FOREIGN KEY (cod_persona) REFERENCES public.personas(cod_persona);


--
-- Name: empleados empleados_cod_puesto_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT empleados_cod_puesto_fkey FOREIGN KEY (cod_puesto) REFERENCES public.puestos(cod_puesto);


--
-- Name: empleados empleados_cod_tipo_modalidad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT empleados_cod_tipo_modalidad_fkey FOREIGN KEY (cod_tipo_modalidad) REFERENCES public.tipos_modalidades(cod_tipo_modalidad);


--
-- Name: empleados_contratos_histor empleados_contratos_cod_tipo_empleado_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empleados_contratos_histor
    ADD CONSTRAINT empleados_contratos_cod_tipo_empleado_fkey FOREIGN KEY (cod_tipo_empleado) REFERENCES public.tipos_empleados(cod_tipo_empleado);


--
-- Name: eventos eventos_cod_empleado_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.eventos
    ADD CONSTRAINT eventos_cod_empleado_fkey FOREIGN KEY (cod_empleado) REFERENCES public.empleados(cod_empleado);


--
-- Name: direcciones fk_direccion_municipio; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.direcciones
    ADD CONSTRAINT fk_direccion_municipio FOREIGN KEY (cod_municipio) REFERENCES public.municipios(cod_municipio);


--
-- Name: control_asistencia fk_empleados; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.control_asistencia
    ADD CONSTRAINT fk_empleados FOREIGN KEY (cod_empleado) REFERENCES public.empleados(cod_empleado) ON DELETE CASCADE;


--
-- Name: password_reset_tokens fk_password_reset_user; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT fk_password_reset_user FOREIGN KEY (email) REFERENCES public.users(email) ON DELETE CASCADE;


--
-- Name: permisos fk_permiso_modulo; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permisos
    ADD CONSTRAINT fk_permiso_modulo FOREIGN KEY (modulo_id) REFERENCES public.modulos(id) ON DELETE CASCADE;


--
-- Name: permisos fk_permiso_rol; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permisos
    ADD CONSTRAINT fk_permiso_rol FOREIGN KEY (rol_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: sessions fk_sessions_user; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT fk_sessions_user FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: password_tokens fk_user; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_tokens
    ADD CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: auditoria fk_usuario; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.auditoria
    ADD CONSTRAINT fk_usuario FOREIGN KEY (usuario_id) REFERENCES public.users(id);


--
-- Name: oficinas oficinas_cod_municipio_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.oficinas
    ADD CONSTRAINT oficinas_cod_municipio_fkey FOREIGN KEY (cod_municipio) REFERENCES public.municipios(cod_municipio);


--
-- Name: puestos puestos_financiam_cod_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.puestos
    ADD CONSTRAINT puestos_financiam_cod_fkey FOREIGN KEY (cod_fuente_financiamiento) REFERENCES public.fuentes_financiamiento(cod_fuente_financiamiento);


--
-- Name: regionales regionales_cod_municipio_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.regionales
    ADD CONSTRAINT regionales_cod_municipio_fkey FOREIGN KEY (cod_municipio) REFERENCES public.municipios(cod_municipio);


--
-- Name: role_user role_user_role_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_user
    ADD CONSTRAINT role_user_role_id_fkey FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: role_user role_user_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_user
    ADD CONSTRAINT role_user_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: secciones_area seccion_cod_jefe_empleados; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.secciones_area
    ADD CONSTRAINT seccion_cod_jefe_empleados FOREIGN KEY (cod_jefe) REFERENCES public.empleados(cod_empleado);


--
-- Name: empleados_contratos_histor termin_cod_contrato_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empleados_contratos_histor
    ADD CONSTRAINT termin_cod_contrato_fkey FOREIGN KEY (cod_terminacion_contrato) REFERENCES public.tipo_terminacion_contrato(cod_terminacion_contrato);


--
-- Name: usuarios usuarios_cod_empleado_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_cod_empleado_fkey FOREIGN KEY (cod_empleado) REFERENCES public.empleados(cod_empleado);


--
-- Name: vacaciones_periodos_historial vacac_historial_cod_empleado_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vacaciones_periodos_historial
    ADD CONSTRAINT vacac_historial_cod_empleado_fkey FOREIGN KEY (cod_empleado) REFERENCES public.empleados(cod_empleado);


--
-- Name: vacaciones vacaciones_cod_empleado_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vacaciones
    ADD CONSTRAINT vacaciones_cod_empleado_fkey FOREIGN KEY (cod_empleado) REFERENCES public.empleados(cod_empleado) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

