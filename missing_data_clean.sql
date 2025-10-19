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
-- Data for Name: modulos; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.modulos (id, nombre) VALUES (10, 'CALENDARIO');
INSERT INTO public.modulos (id, nombre) VALUES (11, 'RECURSOS HUMANOS');
INSERT INTO public.modulos (id, nombre) VALUES (12, 'EMPLEADOS');
INSERT INTO public.modulos (id, nombre) VALUES (13, 'USUARIO');
INSERT INTO public.modulos (id, nombre) VALUES (14, 'PLANILLA');
INSERT INTO public.modulos (id, nombre) VALUES (15, 'ASISTENCIA');
INSERT INTO public.modulos (id, nombre) VALUES (16, 'CONTROL DE ASISTENCIA');


--
-- Name: modulos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.modulos_id_seq', 16, true);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: permisos; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.permisos (id, rol_id, modulo_id, tiene_acceso, created_at, updated_at, puede_crear, puede_actualizar, puede_eliminar) VALUES (60, 69, 15, true, '2025-08-10 01:09:47.521134', '2025-08-10 01:09:47.521134', false, false, false);
INSERT INTO public.permisos (id, rol_id, modulo_id, tiene_acceso, created_at, updated_at, puede_crear, puede_actualizar, puede_eliminar) VALUES (53, 69, 10, true, '2025-06-29 17:02:33.479911', '2025-08-10 01:09:47.532831', false, false, false);
INSERT INTO public.permisos (id, rol_id, modulo_id, tiene_acceso, created_at, updated_at, puede_crear, puede_actualizar, puede_eliminar) VALUES (61, 69, 16, false, '2025-08-10 01:09:47.540092', '2025-08-10 01:09:47.540092', false, false, false);
INSERT INTO public.permisos (id, rol_id, modulo_id, tiene_acceso, created_at, updated_at, puede_crear, puede_actualizar, puede_eliminar) VALUES (54, 69, 12, true, '2025-06-29 17:02:33.489861', '2025-08-10 01:09:47.545608', false, false, false);
INSERT INTO public.permisos (id, rol_id, modulo_id, tiene_acceso, created_at, updated_at, puede_crear, puede_actualizar, puede_eliminar) VALUES (55, 69, 14, false, '2025-06-29 17:02:33.49457', '2025-08-10 01:09:47.55061', false, false, false);
INSERT INTO public.permisos (id, rol_id, modulo_id, tiene_acceso, created_at, updated_at, puede_crear, puede_actualizar, puede_eliminar) VALUES (56, 69, 11, true, '2025-06-29 17:02:33.499352', '2025-08-10 01:09:47.556722', false, false, false);
INSERT INTO public.permisos (id, rol_id, modulo_id, tiene_acceso, created_at, updated_at, puede_crear, puede_actualizar, puede_eliminar) VALUES (57, 69, 13, false, '2025-06-29 17:02:33.504399', '2025-08-10 01:09:47.561747', false, false, false);
INSERT INTO public.permisos (id, rol_id, modulo_id, tiene_acceso, created_at, updated_at, puede_crear, puede_actualizar, puede_eliminar) VALUES (58, 57, 15, true, '2025-08-10 01:06:49.529837', '2025-10-06 20:59:33.070082', false, false, false);
INSERT INTO public.permisos (id, rol_id, modulo_id, tiene_acceso, created_at, updated_at, puede_crear, puede_actualizar, puede_eliminar) VALUES (48, 57, 10, true, '2025-06-25 16:31:20', '2025-10-06 20:59:33.075919', false, false, false);
INSERT INTO public.permisos (id, rol_id, modulo_id, tiene_acceso, created_at, updated_at, puede_crear, puede_actualizar, puede_eliminar) VALUES (59, 57, 16, true, '2025-08-10 01:06:49.548962', '2025-10-06 20:59:33.080284', false, false, false);
INSERT INTO public.permisos (id, rol_id, modulo_id, tiene_acceso, created_at, updated_at, puede_crear, puede_actualizar, puede_eliminar) VALUES (50, 57, 12, true, '2025-06-25 16:31:20', '2025-10-06 20:59:33.087072', false, false, false);
INSERT INTO public.permisos (id, rol_id, modulo_id, tiene_acceso, created_at, updated_at, puede_crear, puede_actualizar, puede_eliminar) VALUES (52, 57, 14, true, '2025-06-25 16:31:20', '2025-10-06 20:59:33.092243', false, false, false);
INSERT INTO public.permisos (id, rol_id, modulo_id, tiene_acceso, created_at, updated_at, puede_crear, puede_actualizar, puede_eliminar) VALUES (49, 57, 11, true, '2025-06-25 16:31:20', '2025-10-06 20:59:33.097224', false, false, false);
INSERT INTO public.permisos (id, rol_id, modulo_id, tiene_acceso, created_at, updated_at, puede_crear, puede_actualizar, puede_eliminar) VALUES (51, 57, 13, true, '2025-06-25 16:31:20', '2025-10-06 20:59:33.10348', true, true, true);
INSERT INTO public.permisos (id, rol_id, modulo_id, tiene_acceso, created_at, updated_at, puede_crear, puede_actualizar, puede_eliminar) VALUES (62, 81, 15, true, '2025-10-06 21:06:57.821333', '2025-10-06 21:14:01.223976', false, false, false);
INSERT INTO public.permisos (id, rol_id, modulo_id, tiene_acceso, created_at, updated_at, puede_crear, puede_actualizar, puede_eliminar) VALUES (63, 81, 10, true, '2025-10-06 21:06:57.83058', '2025-10-06 21:14:01.232058', false, false, false);
INSERT INTO public.permisos (id, rol_id, modulo_id, tiene_acceso, created_at, updated_at, puede_crear, puede_actualizar, puede_eliminar) VALUES (64, 81, 16, true, '2025-10-06 21:06:57.835468', '2025-10-06 21:14:01.23591', false, false, false);
INSERT INTO public.permisos (id, rol_id, modulo_id, tiene_acceso, created_at, updated_at, puede_crear, puede_actualizar, puede_eliminar) VALUES (65, 81, 12, true, '2025-10-06 21:06:57.840429', '2025-10-06 21:14:01.240624', false, false, false);
INSERT INTO public.permisos (id, rol_id, modulo_id, tiene_acceso, created_at, updated_at, puede_crear, puede_actualizar, puede_eliminar) VALUES (66, 81, 14, true, '2025-10-06 21:06:57.845785', '2025-10-06 21:14:01.244947', false, false, false);
INSERT INTO public.permisos (id, rol_id, modulo_id, tiene_acceso, created_at, updated_at, puede_crear, puede_actualizar, puede_eliminar) VALUES (67, 81, 11, true, '2025-10-06 21:06:57.851545', '2025-10-06 21:14:01.250158', false, false, false);
INSERT INTO public.permisos (id, rol_id, modulo_id, tiene_acceso, created_at, updated_at, puede_crear, puede_actualizar, puede_eliminar) VALUES (68, 81, 13, true, '2025-10-06 21:06:57.856539', '2025-10-06 21:14:01.253896', false, false, false);


--
-- Name: permisos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.permisos_id_seq', 68, true);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: municipios; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (9, 2, 'TRUJILLO', '0201');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (10, 2, 'BALFATE', '0202');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (11, 2, 'IRIONA', '0203');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (12, 2, 'LIM├ôN', '0204');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (13, 2, 'SABA', '0205');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (14, 2, 'SANTA FE', '0206');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (15, 2, 'SANTA ROSA DE AGU├üN', '0207');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (16, 2, 'SONAGUERA', '0208');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (17, 2, 'TOCOA', '0209');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (18, 2, 'BONITO ORIENTAL', '0210');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (19, 3, 'COMAYAGUA', '0301');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (20, 3, 'AJUTERIQUE', '0302');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (21, 3, 'EL ROSARIO', '0303');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (22, 3, 'ESQU├ìAS', '0304');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (23, 3, 'HUMUYA', '0305');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (24, 3, 'LA LIBERTAD', '0306');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (25, 3, 'LAMAN├ì', '0307');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (26, 3, 'LA TRINIDAD', '0308');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (27, 3, 'LEJAMAN├ì', '0309');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (28, 3, 'MEAMBAR', '0310');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (29, 3, 'MINAS DE ORO', '0311');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (30, 3, 'OJOS DE AGUA', '0312');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (31, 3, 'SAN JER├ôNIMO', '0313');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (32, 3, 'SAN JOS├ë DE COMAYAGUA', '0314');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (33, 3, 'SAN JOS├ë DEL POTRERO', '0315');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (34, 3, 'SAN LUIS', '0316');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (35, 3, 'SAN SEBASTI├üN', '0317');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (36, 3, 'SIGUATEPEQUE', '0318');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (37, 3, 'VILLA DE SAN ANTONIO', '0319');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (38, 3, 'LAS LAJAS', '0320');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (39, 3, 'TAULAB├ë', '0321');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (40, 4, 'SANTA ROSA DE COP├üN', '0401');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (41, 4, 'CABA├æAS', '0402');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (42, 4, 'CONCEPCI├ôN', '0403');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (43, 4, 'COP├üN RUINAS', '0404');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (44, 4, 'CORQU├ìN', '0405');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (45, 4, 'CUCUYAGUA', '0406');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (46, 4, 'DOLORES', '0407');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (47, 4, 'DULCE NOMBRE', '0408');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (48, 4, 'EL PARA├ìSO', '0409');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (49, 4, 'FLORIDA', '0410');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (50, 4, 'LA JIGUA', '0411');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (51, 4, 'LA UNI├ôN', '0412');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (52, 4, 'NUEVA ARCADIA', '0413');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (53, 4, 'SAN AGUST├ìN', '0414');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (54, 4, 'SAN ANTONIO', '0415');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (55, 4, 'SAN JER├ôNIMO', '0416');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (56, 4, 'SAN JOS├ë', '0417');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (57, 4, 'SAN JUAN DE OPOA', '0418');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (58, 4, 'SAN NICOL├üS', '0419');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (59, 4, 'SAN PEDRO', '0420');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (60, 4, 'SANTA RITA', '0421');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (61, 4, 'TRINIDAD DE COP├üN', '0422');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (62, 4, 'VERACRUZ', '0423');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (63, 5, 'SAN PEDRO SULA', '0501');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (64, 5, 'CHOLOMA', '0502');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (65, 5, 'OMOA', '0503');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (66, 5, 'PIMIENTA', '0504');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (67, 5, 'POTRERILLOS', '0505');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (68, 5, 'PUERTO CORT├ëS', '0506');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (69, 5, 'SAN ANTONIO DE CORT├ëS', '0507');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (70, 5, 'SAN FRANCISCO DE YOJOA', '0508');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (71, 5, 'SAN MANUEL', '0509');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (72, 5, 'SANTA CRUZ DE YOJOA', '0510');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (73, 5, 'VILLANUEVA', '0511');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (74, 5, 'LA LIMA', '0512');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (75, 6, 'CHOLUTECA', '0601');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (76, 6, 'APACILAGUA', '0602');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (77, 6, 'CONCEPCI├ôN DE MAR├ìA', '0603');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (78, 6, 'DUYURE', '0604');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (79, 6, 'EL CORPUS', '0605');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (80, 6, 'EL TRIUNFO', '0606');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (81, 6, 'MARCOVIA', '0607');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (82, 6, 'MOROLICA', '0608');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (83, 6, 'NAMASIGUE', '0609');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (84, 6, 'OROCUINA', '0610');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (85, 6, 'PESPIRE', '0611');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (86, 6, 'SAN ANTONIO DE FLORES', '0612');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (87, 6, 'SAN ISIDRO', '0613');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (88, 6, 'SAN JOS├ë', '0614');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (89, 6, 'SAN MARCOS DE COL├ôN', '0615');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (90, 6, 'SANTA ANA DE YUSGUARE', '0616');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (91, 7, 'YUSCAR├üN', '0701');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (92, 7, 'ALAUCA', '0702');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (93, 7, 'DANL├ì', '0703');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (94, 7, 'EL PARA├ìSO', '0704');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (95, 7, 'GUINOPE', '0705');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (96, 7, 'JACALEAPA', '0706');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (97, 7, 'LUIRE', '0707');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (98, 7, 'MOROCEL├ì', '0708');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (99, 7, 'OROPOL├ì', '0709');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (100, 7, 'POTRERILLOS', '0710');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (101, 7, 'SAN ANTONIO DE FLORES', '0711');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (102, 7, 'SAN LUCAS', '0712');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (103, 7, 'SAN MAT├ìAS', '0713');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (104, 7, 'SOLEDAD', '0714');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (105, 7, 'TEUPASENTI', '0715');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (106, 7, 'TEXIGUAT', '0716');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (107, 7, 'VADO ANCHO', '0717');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (108, 7, 'YAUYUPE', '0718');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (109, 7, 'TROJES', '0719');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (110, 8, 'DISTRITO CENTRAL', '0801');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (111, 8, 'ALUBAR├ëN', '0802');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (113, 8, 'CEDROS', '0803');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (112, 8, 'CURAR├ëN', '0804');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (114, 8, 'EL PORVENIR', '0805');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (115, 8, 'GUAMACA', '0806');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (116, 8, 'LA LIBERTAD', '0807');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (117, 8, 'LA VENTA', '0808');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (118, 8, 'LEPATERIQUE', '0809');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (119, 8, 'MARAITA', '0810');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (120, 8, 'MARALE', '0811');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (121, 8, 'NUEVA ARMENIA', '0812');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (122, 8, 'OJOJONA', '0813');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (123, 8, 'ORICA', '0814');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (124, 8, 'REITOCA', '0815');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (125, 8, 'SABANAGRANDE', '0816');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (126, 8, 'SAN ANTONIO DE ORIENTE', '0817');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (127, 8, 'SAN BUENAVENTURA', '0818');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (128, 8, 'SAN IGNACIO', '0819');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (129, 8, 'SAN JUAN DE FLORES', '0820');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (130, 8, 'SAN MIGUELITO', '0821');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (131, 8, 'SANTA ANA', '0822');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (132, 8, 'SANTA LUC├ìA', '0823');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (133, 8, 'TALANGA', '0824');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (134, 8, 'TATUMBLA', '0825');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (135, 8, 'VALLE DE ├üNGELES', '0826');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (136, 8, 'VILLA DE SAN FRANCISCO', '0827');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (137, 8, 'VALLECILLO', '0828');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (138, 9, 'PUERTO LEMPIRA', '0901');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (139, 9, 'BRUS LAGUNA', '0902');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (140, 9, 'AHUAS', '0903');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (141, 9, 'JUAN FRANCISCO BULNES', '0904');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (142, 9, 'RAM├ôN VILLEDA MORALES', '0905');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (143, 9, 'WAMPUSIRPE', '0906');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (144, 10, 'LA ESPERANZA', '1001');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (145, 10, 'CAMASCA', '1002');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (146, 10, 'COLOMONCAGUA', '1003');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (147, 10, 'CONCEPCI├ôN', '1004');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (148, 10, 'DOLORES', '1005');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (149, 10, 'INTIBUCA', '1006');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (150, 10, 'JES├ÜS DE OTORO', '1007');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (151, 10, 'MAGDALENA', '1008');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (152, 10, 'MASAGUARA', '1009');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (153, 10, 'SAN ANTONIO', '1010');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (154, 10, 'SAN ISIDRO', '1011');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (155, 10, 'SAN JUAN', '1012');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (156, 10, 'SAN MARCOS DE LA SIERRA', '1013');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (157, 10, 'SAN MIGUEL GUANCAPLA', '1014');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (158, 10, 'SANTA LUC├ìA', '1015');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (159, 10, 'YAMARANGUILLA', '1016');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (160, 10, 'SAN FRANCISCO DE OPALA', '1017');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (1, 1, 'LA CEIBA', '0101');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (8, 1, 'EL PORVENIR', '0102');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (7, 1, 'ESPARTA', '0103');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (3, 1, 'JUTIAPA', '0104');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (4, 1, 'LA MASICA', '0105');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (5, 1, 'SAN FRANCISCO', '0106');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (2, 1, 'TELA', '0107');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (6, 1, 'ARIZONA', '0108');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (161, 11, 'ROATAN', '1101');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (162, 11, 'GUANAJA', '1102');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (163, 11, 'JOS├ë SANTOS GUARDIOLA', '1103');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (164, 11, 'UTILA', '1104');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (165, 12, 'LA PAZ', '1201');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (166, 12, 'AGUANQUETERIQUE', '1202');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (167, 12, 'CABA├æAS', '1203');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (168, 12, 'CANE', '1204');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (169, 12, 'CHINACLA', '1205');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (170, 12, 'GUAJIQUIRO', '1206');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (171, 12, 'LAUTERIQUE', '1207');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (172, 12, 'MARCALA', '1208');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (173, 12, 'MERCEDES DE ORIENTE', '1209');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (174, 12, 'OPATORO', '1210');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (175, 12, 'SAN ANTONIO DEL NORTE', '1211');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (176, 12, 'SAN JOS├ë', '1212');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (177, 12, 'SAN JUAN', '1213');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (178, 12, 'SAN PEDRO DE TUTULE', '1214');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (179, 12, 'SANTA ANA', '1215');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (180, 12, 'SANTA ELENA', '1216');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (181, 12, 'SANTA MAR├ìA', '1217');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (182, 12, 'SANTIAGO DE PURINGLA', '1218');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (183, 12, 'YARULA', '1219');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (184, 13, 'GRACIAS', '1301');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (185, 13, 'BEL├ëN', '1302');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (186, 13, 'CANDELARIA', '1303');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (187, 13, 'COLOLACA', '1304');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (188, 13, 'ERANDIQUE', '1305');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (189, 13, 'GUALCINCE', '1306');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (190, 13, 'GUARITA', '1307');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (191, 13, 'LA CAMPA', '1308');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (192, 13, 'LA IGUALA', '1309');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (193, 13, 'LAS FLORES', '1310');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (194, 13, 'LA UNI├ôN', '1311');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (195, 13, 'LA VIRTUD', '1312');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (196, 13, 'LEPAERA', '1313');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (197, 13, 'MAPULACA', '1314');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (198, 13, 'PIRAERA', '1315');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (199, 13, 'SAN ANDR├ëS', '1316');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (200, 13, 'SAN FRANCISCO', '1317');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (201, 13, 'SAN JUAN GUARITA', '1318');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (202, 13, 'SAN MANUEL COLOHETE', '1319');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (203, 13, 'SAN RAFAEL', '1320');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (204, 13, 'SAN SEBASTI├üN', '1321');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (205, 13, 'SANTA CRUZ', '1322');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (206, 13, 'TALGUA', '1323');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (207, 13, 'TAMBLA', '1324');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (208, 13, 'TOMAL├ü', '1325');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (209, 13, 'VALLADOLID', '1326');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (210, 13, 'VIRGINIA', '1327');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (211, 13, 'SAN MARCOS DE CAIQU├ìN', '1328');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (238, 15, 'Juticalpa', '1501');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (239, 15, 'Campamento', '1502');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (240, 15, 'Catacamas', '1503');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (241, 15, 'Concordia', '1504');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (242, 15, 'Dulce Nombre de Culm├¡', '1505');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (243, 15, 'El Rosario', '1506');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (244, 15, 'Esquipulas del Norte', '1507');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (245, 15, 'Gualaco', '1508');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (246, 15, 'Guarizama', '1509');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (247, 15, 'Guata', '1510');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (248, 15, 'Guayape', '1511');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (249, 15, 'Jano', '1512');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (250, 15, 'La Uni├│n', '1513');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (251, 15, 'Mangulile', '1514');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (252, 15, 'Manto', '1515');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (253, 15, 'Salam├í', '1516');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (254, 15, 'San Esteban', '1517');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (255, 15, 'San Francisco de Becerra', '1518');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (256, 15, 'San Francisco de La Paz', '1519');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (257, 15, 'Santa Mar├¡a del Real', '1520');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (258, 15, 'Silca', '1521');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (259, 15, 'Yoc├│n', '1522');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (260, 15, 'Patuca', '1523');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (261, 16, 'Santa B├írbara', '1601');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (262, 16, 'Arada', '1602');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (263, 16, 'Atima', '1603');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (264, 16, 'Azacualpa', '1604');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (265, 16, 'Ceguaca', '1605');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (266, 16, 'Colinas', '1606');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (267, 16, 'Concepci├│n del Norte', '1607');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (268, 16, 'Concepci├│n del Sur', '1608');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (269, 16, 'Chinda', '1609');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (270, 16, 'El N├¡spero', '1610');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (271, 16, 'Gualala', '1611');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (272, 16, 'Ilama', '1612');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (273, 16, 'Macuelizo', '1613');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (274, 16, 'Naranjito', '1614');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (275, 16, 'Nueva Celilac', '1615');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (276, 16, 'Petoa', '1616');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (277, 16, 'Protecci├│n', '1617');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (278, 16, 'Quimist├ín', '1618');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (279, 16, 'San Francisco de Ojuera', '1619');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (280, 16, 'San Luis', '1620');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (281, 16, 'San Marcos', '1621');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (282, 16, 'San Nicol├ís', '1622');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (283, 16, 'San Pedro Zacapa', '1623');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (284, 16, 'Santa Rita', '1624');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (285, 16, 'San Vicente Centenario', '1625');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (286, 16, 'Trinidad', '1626');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (287, 16, 'Las Vegas', '1627');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (288, 16, 'Nueva Frontera', '1628');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (289, 17, 'Nacaome', '1701');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (290, 17, 'Alianza', '1702');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (291, 17, 'Amapala', '1703');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (292, 17, 'Aramecina', '1704');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (293, 17, 'Caridad', '1705');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (294, 17, 'Goascor├ín', '1706');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (295, 17, 'Langue', '1707');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (296, 17, 'San Francisco de Coray', '1708');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (297, 17, 'San Lorenzo', '1709');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (298, 18, 'Yoro', '1801');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (299, 18, 'Arenal', '1802');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (300, 18, 'El Negrito', '1803');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (301, 18, 'El Progreso', '1804');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (302, 18, 'Joc├│n', '1805');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (303, 18, 'Moraz├ín', '1806');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (304, 18, 'Olanchito', '1807');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (305, 18, 'Santa Rita', '1808');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (306, 18, 'Sulaco', '1809');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (307, 18, 'Victoria', '1810');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (308, 18, 'Yorito', '1811');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (309, 14, 'Nueva Ocotepeque', '1401');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (310, 14, 'Bel├®n Gualcho', '1402');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (311, 14, 'Concepci├│n', '1403');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (312, 14, 'Dolores Merend├│n', '1404');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (313, 14, 'Fraternidad', '1405');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (314, 14, 'La Encarnaci├│n', '1406');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (315, 14, 'La Labor', '1407');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (316, 14, 'Lucerna', '1408');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (317, 14, 'Mercedes', '1409');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (318, 14, 'San Fernando', '1410');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (319, 14, 'San Francisco del Valle', '1411');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (320, 14, 'San Jorge', '1412');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (321, 14, 'San Marcos', '1413');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (322, 14, 'Santa F├®', '1414');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (323, 14, 'Sensenti', '1415');
INSERT INTO public.municipios (cod_municipio, cod_depto, nom_municipio, codigo) VALUES (324, 14, 'Sinuapa', '1416');


--
-- Name: municipios_cod_municipio_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.municipios_cod_municipio_seq', 324, true);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: regionales; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.regionales (cod_regional, cod_municipio, direccion, nom_regional, fec_registro, usr_registro, fec_modificacion, usr_modificacion) VALUES (1, 110, 'Centro C├¡vico Gubernamental, Torre 1, Piso 20, Boulevard Juan Pablo II, Esquina Rep├║blica de Corea, Tegucigalpa, M.D.C., Honduras, C.A.', 'Oficina Principal', '2025-05-30 14:11:26.187578', 'ADMIN', NULL, NULL);
INSERT INTO public.regionales (cod_regional, cod_municipio, direccion, nom_regional, fec_registro, usr_registro, fec_modificacion, usr_modificacion) VALUES (2, 63, 'Colonia Trejo, 4ta etapa, 21 calle, 24 avenida. San Pedro Sula, Cortes.', 'Regional Norte', '2025-05-31 10:17:22.805816', 'ADMIN', NULL, NULL);


--
-- Name: regionales_cod_regional_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.regionales_cod_regional_seq', 1, false);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: departamentos; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.departamentos (cod_depto, nom_depto, zona) VALUES (1, 'ATL├üNTIDA', NULL);
INSERT INTO public.departamentos (cod_depto, nom_depto, zona) VALUES (2, 'COL├ôN', NULL);
INSERT INTO public.departamentos (cod_depto, nom_depto, zona) VALUES (3, 'COMAYAGUA', NULL);
INSERT INTO public.departamentos (cod_depto, nom_depto, zona) VALUES (4, 'COP├üN', NULL);
INSERT INTO public.departamentos (cod_depto, nom_depto, zona) VALUES (5, 'CORT├ëS', NULL);
INSERT INTO public.departamentos (cod_depto, nom_depto, zona) VALUES (6, 'CHOLUTECA', NULL);
INSERT INTO public.departamentos (cod_depto, nom_depto, zona) VALUES (7, 'EL PARA├ìSO', NULL);
INSERT INTO public.departamentos (cod_depto, nom_depto, zona) VALUES (8, 'FRANCISCO MORAZ├üN', NULL);
INSERT INTO public.departamentos (cod_depto, nom_depto, zona) VALUES (9, 'GRACIAS A DIOS', NULL);
INSERT INTO public.departamentos (cod_depto, nom_depto, zona) VALUES (10, 'INTIBUC├ü', NULL);
INSERT INTO public.departamentos (cod_depto, nom_depto, zona) VALUES (11, 'ISLAS DE LA BAH├ìA', NULL);
INSERT INTO public.departamentos (cod_depto, nom_depto, zona) VALUES (12, 'LA PAZ', NULL);
INSERT INTO public.departamentos (cod_depto, nom_depto, zona) VALUES (13, 'LEMPIRA', NULL);
INSERT INTO public.departamentos (cod_depto, nom_depto, zona) VALUES (14, 'OCOTEPEQUE', NULL);
INSERT INTO public.departamentos (cod_depto, nom_depto, zona) VALUES (15, 'OLANCHO', NULL);
INSERT INTO public.departamentos (cod_depto, nom_depto, zona) VALUES (16, 'SANTA B├üRBARA', NULL);
INSERT INTO public.departamentos (cod_depto, nom_depto, zona) VALUES (17, 'VALLE', NULL);
INSERT INTO public.departamentos (cod_depto, nom_depto, zona) VALUES (18, 'YORO', NULL);


--
-- Name: departamentos_cod_depto_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.departamentos_cod_depto_seq', 1, false);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: oficinas; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.oficinas (cod_oficina, cod_municipio, direccion, nom_oficina, a_cargo, num_telefono, fec_registro, usr_registro, fec_modificacion, usr_modificacion, direccion_corta, asignable_empleados) VALUES (1, 110, 'Centro C├¡vico Gubernamental, Torre 1, Piso 20, Boulevard Juan Pablo II, Esquina Rep├║blica de Corea, Tegucigalpa, M.D.C., Honduras, C...', 'Oficina Principal', 'Silvia Marcela Amaya Escoto', '2242-8645', '2025-05-26 09:49:03', 'ADMIN', '2025-05-26 09:49:03', 'ADMIN', 'Tegucigalpa, M.D.C.', true);
INSERT INTO public.oficinas (cod_oficina, cod_municipio, direccion, nom_oficina, a_cargo, num_telefono, fec_registro, usr_registro, fec_modificacion, usr_modificacion, direccion_corta, asignable_empleados) VALUES (2, 63, 'Col. Trejo 12 y 13 calle, 23 avenida, S.O. San Pedro Sula, Cortes.', 'Regional Norte', 'Susana Patricia Rodriguez', '2556-5454', '2025-05-31 10:12:20.027088', 'ADMIN', NULL, NULL, 'San Pedro Sula, Cortes', true);
INSERT INTO public.oficinas (cod_oficina, cod_municipio, direccion, nom_oficina, a_cargo, num_telefono, fec_registro, usr_registro, fec_modificacion, usr_modificacion, direccion_corta, asignable_empleados) VALUES (4, 110, 'El Sauce, Tegucigalpa, M.D.C., Honduras.', 'El Sauce', NULL, NULL, '2025-05-31 10:12:20.027088', 'ADMIN', NULL, NULL, 'El Sauce', false);
INSERT INTO public.oficinas (cod_oficina, cod_municipio, direccion, nom_oficina, a_cargo, num_telefono, fec_registro, usr_registro, fec_modificacion, usr_modificacion, direccion_corta, asignable_empleados) VALUES (10, 93, 'tegucigalpa', 'central', 'DANIEL OYUELA  ESTRADA', '+504 9475-5664', '2025-07-17 02:34:31.138856', 'admin', '2025-08-10 18:53:10.163757', NULL, 'central', true);


--
-- Name: oficinas_cod_oficina_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.oficinas_cod_oficina_seq', 15, true);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: puestos; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.puestos (cod_puesto, nom_puesto, fec_registro, usr_registro, cod_fuente_financiamiento, funciones_puesto, sueldo_base) VALUES (1, 'DIRECTORA', '2025-05-26 08:01:05.698475', 'ADMIN', 1, 'Dirigir y supervisar todas las operaciones de la instituci├│n seg├║n las leyes de Honduras', 30000.00);
INSERT INTO public.puestos (cod_puesto, nom_puesto, fec_registro, usr_registro, cod_fuente_financiamiento, funciones_puesto, sueldo_base) VALUES (2, 'ASISTENTE EJECUTIVO DE DIRECCI├ôN', '2025-05-26 08:01:05.698475', 'ADMIN', 1, 'Apoyo administrativo a la direcci├│n, coordinaci├│n de agendas y seguimiento de proyectos', 16000.00);
INSERT INTO public.puestos (cod_puesto, nom_puesto, fec_registro, usr_registro, cod_fuente_financiamiento, funciones_puesto, sueldo_base) VALUES (10, 'ASISTENTE DE AREA', '2025-07-17 00:00:00', 'DANIEL OYUELA ESTRADA', 1, 'Atender llamadas, manejar archivos y coordinar reuniones.', 10500);
INSERT INTO public.puestos (cod_puesto, nom_puesto, fec_registro, usr_registro, cod_fuente_financiamiento, funciones_puesto, sueldo_base) VALUES (3, 'ASISTENTE ADMINISTRATIVO', '2025-07-17 17:30:58.354', 'admin', 1, 'Atender llamadas, manejar archivos y coordinar reuniones.', 10500);


--
-- Name: puestos_cod_puesto_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.puestos_cod_puesto_seq', 17, true);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: niveles_educativos; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.niveles_educativos (cod_nivel_educativo, nom_nivel, descripcion, fec_registro, usr_registro, fec_modificacion, usr_modificacion) VALUES (1, 'Primaria', 'Educaci├│n b├ísica primaria (1┬░ a 6┬░ grado)', '2025-05-26 00:00:00', 'ADMIN', '2025-05-26 00:00:00', 'ADMIN');
INSERT INTO public.niveles_educativos (cod_nivel_educativo, nom_nivel, descripcion, fec_registro, usr_registro, fec_modificacion, usr_modificacion) VALUES (2, 'Secundaria', 'Educaci├│n secundaria o bachillerato (7┬░ a 12┬░ grado)', '2025-05-26 00:00:00', 'ADMIN', '2025-05-26 00:00:00', 'ADMIN');
INSERT INTO public.niveles_educativos (cod_nivel_educativo, nom_nivel, descripcion, fec_registro, usr_registro, fec_modificacion, usr_modificacion) VALUES (3, 'CARRERA UNIVERSITARIA', 'CARRERA', '2025-07-17 00:00:00', 'admin', '2025-07-17 18:22:54.393', 'admin');
INSERT INTO public.niveles_educativos (cod_nivel_educativo, nom_nivel, descripcion, fec_registro, usr_registro, fec_modificacion, usr_modificacion) VALUES (8, 'RRHH', 'TEMPORAL', '2025-08-14 16:10:02.125', 'admin', '2025-08-14 16:10:02.125', 'admin');
INSERT INTO public.niveles_educativos (cod_nivel_educativo, nom_nivel, descripcion, fec_registro, usr_registro, fec_modificacion, usr_modificacion) VALUES (9, 'PRUEBA', 'TEMPORAL', '2025-08-14 16:17:33.056', 'admin', '2025-08-14 16:17:33.056', 'admin');


--
-- Name: niveles_educativos_cod_nivel_educativo_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.niveles_educativos_cod_nivel_educativo_seq', 9, true);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: tipos_empleados; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.tipos_empleados (cod_tipo_empleado, nom_tipo, descripcion, fec_registro, usr_registro) VALUES (1, 'Permanente', 'Empleados con contrato permanente', '2025-05-26 00:00:00', 'admin');
INSERT INTO public.tipos_empleados (cod_tipo_empleado, nom_tipo, descripcion, fec_registro, usr_registro) VALUES (2, 'Contrato', 'Empleados contratados por un periodo determinado', '2025-05-26 00:00:00', 'admin');
INSERT INTO public.tipos_empleados (cod_tipo_empleado, nom_tipo, descripcion, fec_registro, usr_registro) VALUES (4, 'Por Servicios Profesionales', 'Empleados contratados para realizar servicios espec├¡ficos', '2025-05-26 00:00:00', 'admin');
INSERT INTO public.tipos_empleados (cod_tipo_empleado, nom_tipo, descripcion, fec_registro, usr_registro) VALUES (5, 'Contratista', 'Empleados que trabajan bajo contrato para proyectos espec├¡ficos', '2025-05-26 00:00:00', 'admin');
INSERT INTO public.tipos_empleados (cod_tipo_empleado, nom_tipo, descripcion, fec_registro, usr_registro) VALUES (6, 'Eventual', 'Empleados que se contratan por eventos espec├¡ficos o necesidades temporales', '2025-05-26 00:00:00', 'admin');
INSERT INTO public.tipos_empleados (cod_tipo_empleado, nom_tipo, descripcion, fec_registro, usr_registro) VALUES (7, 'Consultor', 'Profesional externo contratado para proporcionar asesor├¡a especializada y soluciones en ├íreas espec├¡ficas de la organizaci├│n.', '2025-05-26 00:00:00', 'admin');
INSERT INTO public.tipos_empleados (cod_tipo_empleado, nom_tipo, descripcion, fec_registro, usr_registro) VALUES (8, 'Pasante', 'Empleado en proceso de aprendizaje y formaci├│n pr├íctica en el entorno laboral, generalmente un estudiante o reci├®n graduado', '2025-05-26 00:00:00', 'admin');


--
-- Name: tipos_empleados_cod_tipo_empleado_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tipos_empleados_cod_tipo_empleado_seq', 14, true);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: tipos_modalidades; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.tipos_modalidades (cod_tipo_modalidad, nom_tipo, fec_registro, usr_registro) VALUES (1, 'Presencial', '2025-05-26 00:00:00', 'admin');
INSERT INTO public.tipos_modalidades (cod_tipo_modalidad, nom_tipo, fec_registro, usr_registro) VALUES (2, 'Remote', '2025-05-26 00:00:00', 'admin');
INSERT INTO public.tipos_modalidades (cod_tipo_modalidad, nom_tipo, fec_registro, usr_registro) VALUES (3, 'H├¡brido', '2025-05-26 00:00:00', 'admin');
INSERT INTO public.tipos_modalidades (cod_tipo_modalidad, nom_tipo, fec_registro, usr_registro) VALUES (4, 'Por Proyecto', '2025-05-26 00:00:00', 'admin');


--
-- Name: tipos_modalidades_cod_tipo_modalidad_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tipos_modalidades_cod_tipo_modalidad_seq', 1, false);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: tipo_terminacion_contrato; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.tipo_terminacion_contrato (cod_terminacion_contrato, nombre_tipo_term_contrato, descripcion, cod_contrato) VALUES (1, 'Dimisi├│n', 'El empleado ha renunciado voluntariamente.', 1);
INSERT INTO public.tipo_terminacion_contrato (cod_terminacion_contrato, nombre_tipo_term_contrato, descripcion, cod_contrato) VALUES (2, 'Despido', 'El empleado ha sido despedido por la empresa.', 1);
INSERT INTO public.tipo_terminacion_contrato (cod_terminacion_contrato, nombre_tipo_term_contrato, descripcion, cod_contrato) VALUES (3, 'Jubilaci├│n', 'El empleado se ha jubilado.', 1);
INSERT INTO public.tipo_terminacion_contrato (cod_terminacion_contrato, nombre_tipo_term_contrato, descripcion, cod_contrato) VALUES (4, 'Terminaci├│n de Contrato', 'El contrato del empleado ha llegado a su fin.', 1);


--
-- Name: tipo_terminacion_contrato_cod_terminacion_contrato_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tipo_terminacion_contrato_cod_terminacion_contrato_seq', 1, false);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: secciones_area; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.secciones_area (cod_seccion, cod_jefe, nom_seccion, fec_registro, usr_registro, fec_modificacion, usr_modificacion) VALUES (1, NULL, 'DIRECCI├ôN', '2025-05-31 10:08:50.092933', 'ADMIN', NULL, NULL);
INSERT INTO public.secciones_area (cod_seccion, cod_jefe, nom_seccion, fec_registro, usr_registro, fec_modificacion, usr_modificacion) VALUES (2, NULL, 'ADMINISTRACI├ôN FINANCIERA', '2025-05-31 10:08:50.092933', 'ADMIN', NULL, NULL);
INSERT INTO public.secciones_area (cod_seccion, cod_jefe, nom_seccion, fec_registro, usr_registro, fec_modificacion, usr_modificacion) VALUES (3, NULL, 'SECRETAR├ìA GENERAL', '2025-05-31 10:08:50.092933', 'ADMIN', NULL, NULL);
INSERT INTO public.secciones_area (cod_seccion, cod_jefe, nom_seccion, fec_registro, usr_registro, fec_modificacion, usr_modificacion) VALUES (4, NULL, 'INVESTIGACI├ôN', '2025-05-31 10:08:50.092933', 'ADMIN', NULL, NULL);
INSERT INTO public.secciones_area (cod_seccion, cod_jefe, nom_seccion, fec_registro, usr_registro, fec_modificacion, usr_modificacion) VALUES (5, NULL, 'SERVICIOS LEGALES', '2025-05-31 10:08:50.092933', 'ADMIN', NULL, NULL);
INSERT INTO public.secciones_area (cod_seccion, cod_jefe, nom_seccion, fec_registro, usr_registro, fec_modificacion, usr_modificacion) VALUES (6, NULL, 'PREVENCI├ôN, EVALUACI├ôN Y CERTIFICACI├ôN', '2025-05-31 10:08:50.092933', 'ADMIN', NULL, NULL);


--
-- Name: secciones_area_cod_seccion_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.secciones_area_cod_seccion_seq', 1, false);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: fuentes_financiamiento; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.fuentes_financiamiento (cod_fuente_financiamiento, nom_fuente, fec_registro, usr_registro, fec_modificacion, usr_modificacion) VALUES (1, 'FONDOS NACIONALES', '2025-05-26 11:28:00.063334', 'ADMIN', '2025-05-26 11:28:00.063334', NULL);
INSERT INTO public.fuentes_financiamiento (cod_fuente_financiamiento, nom_fuente, fec_registro, usr_registro, fec_modificacion, usr_modificacion) VALUES (2, 'INL', '2025-05-26 11:28:00.063334', 'ADMIN', '2025-05-26 11:28:00.063334', NULL);


--
-- Name: fuentes_financiamiento_cod_fuente_financiamiento_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.fuentes_financiamiento_cod_fuente_financiamiento_seq', 1, false);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: datos_empresa; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.datos_empresa (cod_empresa, nom_empresa, contacto, direccion, pais, ciudad, departamento, cod_postal, email, num_fijo, num_celular, fax, pag_web, fec_registro, usr_registro, cod_municipio) VALUES (1, 'Direcci├│n de Asuntos Disciplinarios Policiales (DIDADPOL)', 'Unidad de Tecnolog├¡as de Informaci├│n', 'Centro C├¡vico gubernamental, Torre 1, Piso 19 y 20, Boulevard Juan Pablo II, Esquina Rep├║blica de Corea, Tegucigaba, M.D.C., Honduras, C.A.', 'Honduras', 'Tegucigaba', 'Francisco Morazan', '11101', 'sistema@didadpol.gob.hn', '2242-8645', '9999-8889', '818-978-7102', 'https://www.didadpol.gob.hn', '2025-08-12 21:23:25.792369', 'Daniel Oyuela estrada ', 110);


--
-- Name: datos_empresa_cod_empresa_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.datos_empresa_cod_empresa_seq', 1, false);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, estado, cod_persona, intentos_fallidos, ultimo_fallo) VALUES (39, 'MAGDIEL SARAI ORELLANA VALLADARES', 'Magdielorellana0@gmail.com', NULL, '$2y$10$4E7mZalt8/BiOX7Jma7xjuxEE56jtmLfbaB2XOS9L7VgNjRzmN1Ym', NULL, '2025-06-20 23:16:48', '2025-06-21 00:14:35', 'ACTIVO', NULL, 0, NULL);
INSERT INTO public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, estado, cod_persona, intentos_fallidos, ultimo_fallo) VALUES (40, 'MARIA ELIZABETH AVILA TURCIOS', 'elizabethavila891@gmail.com', NULL, '$2y$10$0av0pP3.yLk19ZqM2Y4afeihziooMziwmq5kLlyGoAB665cAY1RPu', NULL, '2025-06-20 23:17:34', '2025-06-21 00:14:42', 'ACTIVO', NULL, 0, NULL);
INSERT INTO public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, estado, cod_persona, intentos_fallidos, ultimo_fallo) VALUES (41, 'BRANDY JULEISY TORRES MARTINEZ', 'juleisy2003tm@gmail.com', NULL, '$2y$10$vqUguw9di4WU5nIJi.5QauJbb1T2uE9hmAk0qw2mp5nqtInf7pVBC', NULL, '2025-06-20 23:18:14', '2025-10-06 06:41:14', 'ACTIVO', NULL, 0, NULL);
INSERT INTO public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, estado, cod_persona, intentos_fallidos, ultimo_fallo) VALUES (34, 'DANIEL EDUARDO OYUELA ESTRADA', 'danieloyuela51@gmail.com', '2025-10-07 02:57:17', '$2b$10$.ThgaC51q88eIUHnkSe5qevWbKzVWm8MgdINKmT0ecZvf03./ZUgO', '5dRrMpJj62VIvtnE6ZXoJNX6AxjVbZzYu6GsVAyN43N9z7zKliTFz0MVE83L', '2025-06-20 22:46:24', '2025-10-07 02:57:17', 'ACTIVO', NULL, 0, '2025-10-06 20:57:30.599909-06');
INSERT INTO public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, estado, cod_persona, intentos_fallidos, ultimo_fallo) VALUES (58, 'EDUARDO DANIEL OYUELA ESTRADA', 'eoyuela@didadpol.gob.hn', '2025-10-07 02:51:29', '$2b$10$tZ3XIV4pvq5d7PCuPDccQOZVjQDMrE7MmAdc4OtvTAkrVbdpxR3oy', NULL, '2025-10-07 02:50:23', '2025-10-07 02:51:29', 'ACTIVO', 119, 0, NULL);
INSERT INTO public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, estado, cod_persona, intentos_fallidos, ultimo_fallo) VALUES (38, 'NANCY KARINA CHAVARRIA TACHE', 'chavarriakarina57@gmail.com', NULL, '$2y$10$xxAWnYjEPQAci2nPfZm9xOsmj3V70SCtCpUK4TnEMPBQemYO6fHOa', NULL, '2025-06-20 23:13:32', '2025-10-07 03:07:26', 'ACTIVO', NULL, 0, '2025-10-06 00:41:41.258413-06');
INSERT INTO public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, estado, cod_persona, intentos_fallidos, ultimo_fallo) VALUES (45, 'DANIEL EDUARDO OYUELA ESTRADA', 'doyuela@didadpol.gob.hn', '2025-07-24 00:47:26', '$2b$10$tLecCGJWYexh5O4s99gZseK24YBqkmVtX3dIirfUx.pwI0KtEPWJ.', NULL, '2025-07-24 00:47:02', '2025-07-24 00:47:50', 'ACTIVO', 112, 0, '2025-10-06 00:22:28.142189-06');


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 61, true);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.roles (id, nombre, descripcion, created_at, updated_at, estado) VALUES (57, 'ADMINISTRADOR', 'CONTROL TOTAL', '2025-06-17 17:30:08', '2025-06-20 19:34:05', 'ACTIVO');
INSERT INTO public.roles (id, nombre, descripcion, created_at, updated_at, estado) VALUES (72, 'OBSERVADOR', 'TEMPORAL', '2025-06-25 08:00:57', '2025-06-25 08:00:57', 'ACTIVO');
INSERT INTO public.roles (id, nombre, descripcion, created_at, updated_at, estado) VALUES (69, 'EMPLEADO', 'TEMPORAL', '2025-06-21 03:01:56', '2025-07-27 00:16:14', 'ACTIVO');
INSERT INTO public.roles (id, nombre, descripcion, created_at, updated_at, estado) VALUES (62, 'GESTION', 'TEMPORAL', '2025-06-18 23:13:20', '2025-08-11 01:04:38', 'ACTIVO');
INSERT INTO public.roles (id, nombre, descripcion, created_at, updated_at, estado) VALUES (78, 'RRHH', 'TEMPORAL', '2025-08-11 01:10:35', '2025-08-11 01:10:35', 'ACTIVO');
INSERT INTO public.roles (id, nombre, descripcion, created_at, updated_at, estado) VALUES (79, 'ADMINISTRADORRR', 'TEMPORAL', '2025-08-14 16:18:03', '2025-10-06 06:24:46', 'ACTIVO');
INSERT INTO public.roles (id, nombre, descripcion, created_at, updated_at, estado) VALUES (81, 'UNAH', 'TEMPORAL', '2025-10-07 03:06:07', '2025-10-07 03:06:07', 'ACTIVO');


--
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.roles_id_seq', 81, true);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: role_user; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.role_user (id, user_id, role_id, created_at) VALUES (22, 39, 57, '2025-06-21 00:14:35');
INSERT INTO public.role_user (id, user_id, role_id, created_at) VALUES (23, 40, 57, '2025-06-21 00:14:42');
INSERT INTO public.role_user (id, user_id, role_id, created_at) VALUES (27, 45, 57, '2025-07-24 00:47:50');
INSERT INTO public.role_user (id, user_id, role_id, created_at) VALUES (18, 34, 69, '2025-08-10 07:09:16');
INSERT INTO public.role_user (id, user_id, role_id, created_at) VALUES (24, 41, 72, '2025-07-27 00:16:39');
INSERT INTO public.role_user (id, user_id, role_id, created_at) VALUES (21, 38, 81, '2025-06-25 08:12:42');


--
-- Name: role_user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.role_user_id_seq', 33, true);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: personas; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.personas (cod_persona, genero, estado_civil, nombre_completo, fec_nacimiento, lugar_nacimiento, nacionalidad, dni, foto_persona, fec_registro, fec_modificacion, usr_modificacion, usr_registro, rtn) VALUES (1, 'Femenino', 'Soltero', 'SILVIA MARCELA AMAYA ESCOTO', '1984-01-06', 'Se ignora', 'Hondure├▒o (a)', '0801-1984-20353', NULL, '2025-05-26 10:00:52', '2025-05-26 10:00:52', 'ADMIN', NULL, '08011992054321');
INSERT INTO public.personas (cod_persona, genero, estado_civil, nombre_completo, fec_nacimiento, lugar_nacimiento, nacionalidad, dni, foto_persona, fec_registro, fec_modificacion, usr_modificacion, usr_registro, rtn) VALUES (112, 'Masculino', 'Soltero', 'DANIEL EDUARDO OYUELA ESTRADA', '2002-02-17', 'VALLE DE ANGELES', 'HONDURE├æO A', '0801-2002-08924', NULL, '2025-07-20 14:40:52.349824', '2025-08-11 14:32:37.51626', NULL, NULL, '08011985012345');
INSERT INTO public.personas (cod_persona, genero, estado_civil, nombre_completo, fec_nacimiento, lugar_nacimiento, nacionalidad, dni, foto_persona, fec_registro, fec_modificacion, usr_modificacion, usr_registro, rtn) VALUES (119, 'Masculino', 'Soltero', 'EDUARDO DANIEL OYUELA ESTRADA', '2002-02-17', 'VALLE DE ANGELES', 'HONDURE├æO A', '0801-2000-08102', NULL, '2025-08-14 10:12:45.899378', NULL, NULL, NULL, NULL);


--
-- Name: personas_cod_persona_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.personas_cod_persona_seq', 119, true);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: telefonos; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.telefonos (cod_telefono, cod_persona, numero, fec_registro, usr_registro, telefono_emergencia, nombre_contacto_emergencia, fec_modificacion, usr_modificacion) OVERRIDING SYSTEM VALUE VALUES (1, 1, '9539-8069', '2025-05-30 19:13:31.634121', 'ADMIN', NULL, NULL, NULL, NULL);
INSERT INTO public.telefonos (cod_telefono, cod_persona, numero, fec_registro, usr_registro, telefono_emergencia, nombre_contacto_emergencia, fec_modificacion, usr_modificacion) OVERRIDING SYSTEM VALUE VALUES (85, 112, '+504 9475-5664', '2025-07-20 14:40:52.349824', NULL, '+504 8989-2020', 'LESTER ARMANDO OYUELA ESTRADA', '2025-08-11 14:32:37.51626', NULL);
INSERT INTO public.telefonos (cod_telefono, cod_persona, numero, fec_registro, usr_registro, telefono_emergencia, nombre_contacto_emergencia, fec_modificacion, usr_modificacion) OVERRIDING SYSTEM VALUE VALUES (92, 119, '+504 9475-5664', '2025-08-14 10:12:45.899378', NULL, '+504 8989-2020', 'LESTER ARMANDO OYUELA ESTRADA', NULL, NULL);


--
-- Name: telefonos_cod_telefono_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.telefonos_cod_telefono_seq', 1, true);


--
-- Name: telefonos_cod_telefono_seq1; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.telefonos_cod_telefono_seq1', 92, true);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: direcciones; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.direcciones (cod_direccion, cod_persona, direccion, fec_registro, usr_registro, cod_municipio, fec_modificacion, usr_modificacion) OVERRIDING SYSTEM VALUE VALUES (1, 1, 'TGU', '2025-05-30 22:51:08.987256', 'ADMIN', NULL, NULL, NULL);
INSERT INTO public.direcciones (cod_direccion, cod_persona, direccion, fec_registro, usr_registro, cod_municipio, fec_modificacion, usr_modificacion) OVERRIDING SYSTEM VALUE VALUES (92, 112, 'AMARATECA', '2025-07-20 14:40:52.349824', NULL, 310, '2025-08-11 14:32:37.51626', NULL);
INSERT INTO public.direcciones (cod_direccion, cod_persona, direccion, fec_registro, usr_registro, cod_municipio, fec_modificacion, usr_modificacion) OVERRIDING SYSTEM VALUE VALUES (99, 119, 'CENTRO C├ìVICO GUBERNAMENTAL, TORRE 1, PISO 19 Y 20, BOULEVARD JUAN PABLO II, ESQUINA REP├ÜBLICA DE COREA, TEGUCIGABA, M.D.C., HONDURAS, C.A.', '2025-08-14 10:12:45.899378', NULL, 167, NULL, NULL);


--
-- Name: direcciones_cod_direccion_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.direcciones_cod_direccion_seq', 1, true);


--
-- Name: direcciones_cod_direccion_seq1; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.direcciones_cod_direccion_seq1', 99, true);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: empleados; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.empleados (cod_empleado, cod_persona, cod_tipo_modalidad, cod_puesto, cod_oficina, cod_nivel_educativo, cod_horario, es_jefe, fecha_contratacion, fecha_notificacion, cod_tipo_terminacion, email_trabajo, fec_registro, usr_registro, fec_modificacion, usr_modificacion, cod_tipo_empleado, id_rol) VALUES (1, 1, 1, 1, 1, 1, 1, false, '2025-05-30', NULL, NULL, 'j@d.hn', '2025-05-30 00:00:00', 'ADMIN', NULL, NULL, NULL, NULL);
INSERT INTO public.empleados (cod_empleado, cod_persona, cod_tipo_modalidad, cod_puesto, cod_oficina, cod_nivel_educativo, cod_horario, es_jefe, fecha_contratacion, fecha_notificacion, cod_tipo_terminacion, email_trabajo, fec_registro, usr_registro, fec_modificacion, usr_modificacion, cod_tipo_empleado, id_rol) VALUES (84, 112, 3, 3, 4, 3, 1, NULL, '2002-02-17', NULL, NULL, 'danieloyuela51@gmail.com', '2025-07-20 14:40:52.349824', NULL, '2025-08-11 14:32:37.51626', NULL, 1, NULL);
INSERT INTO public.empleados (cod_empleado, cod_persona, cod_tipo_modalidad, cod_puesto, cod_oficina, cod_nivel_educativo, cod_horario, es_jefe, fecha_contratacion, fecha_notificacion, cod_tipo_terminacion, email_trabajo, fec_registro, usr_registro, fec_modificacion, usr_modificacion, cod_tipo_empleado, id_rol) VALUES (91, 119, 3, 3, 10, 3, 1, NULL, '2002-02-16', NULL, NULL, 'danieloyuela51@gmail.com', '2025-08-14 10:12:45.899378', NULL, NULL, NULL, 1, NULL);


--
-- Name: empleados_cod_empleado_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.empleados_cod_empleado_seq', 91, true);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: empleados_contratos_histor; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.empleados_contratos_histor (cod_contrato, cod_empleado, cod_tipo_empleado, cod_puesto, fecha_inicio_contrato, fecha_final_contrato, salario, contrato_activo, observaciones, usr_registro, fec_registro, usr_modificacion, fec_modificacion, cod_terminacion_contrato) VALUES (90, 84, 1, 3, '2002-12-12', '2020-12-12', 25000.00, true, NULL, 'sistema', '2025-07-20 14:40:52.349824', NULL, '2025-08-11 14:32:37.51626', NULL);
INSERT INTO public.empleados_contratos_histor (cod_contrato, cod_empleado, cod_tipo_empleado, cod_puesto, fecha_inicio_contrato, fecha_final_contrato, salario, contrato_activo, observaciones, usr_registro, fec_registro, usr_modificacion, fec_modificacion, cod_terminacion_contrato) VALUES (97, 91, 1, 3, '2002-02-12', '2002-02-12', 200000.00, true, NULL, 'sistema', '2025-08-14 10:12:45.899378', NULL, NULL, NULL);


--
-- Name: empleados_contratos_histor_cod_contrato_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.empleados_contratos_histor_cod_contrato_seq', 97, true);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: planillas; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.planillas (id, cod_persona, dd, dt, salario_bruto, ihss, isr, injupemp, impuesto_vecinal, dias_descargados, injupemp_reingresos, injupemp_prestamos, prestamo_banco_atlantida, pagos_deducibles, colegio_admon_empresas, cuota_coop_elga, total_deducciones, total_a_pagar, creado_en, isr_rango_id, periodo) VALUES (38, 1, 3, 27, 0.00, 0.00, 0.00, 0.00, 0.00, 3, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '2025-08-14 10:29:19.878855', NULL, NULL);


--
-- Name: planillas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.planillas_id_seq', 38, true);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: i_s_r_planillas; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.i_s_r_planillas (id, sueldo_inicio, sueldo_fin, porcentaje, tipo, created_at, updated_at) VALUES (1, 0.01, 21457.76, 0.00, 'ISR', '2025-08-11 11:49:00', '2025-08-11 11:49:00');
INSERT INTO public.i_s_r_planillas (id, sueldo_inicio, sueldo_fin, porcentaje, tipo, created_at, updated_at) VALUES (2, 21457.77, 30969.88, 15.00, 'ISR', '2025-08-11 11:49:00', '2025-08-11 11:49:00');
INSERT INTO public.i_s_r_planillas (id, sueldo_inicio, sueldo_fin, porcentaje, tipo, created_at, updated_at) VALUES (3, 30969.89, 67604.36, 20.00, 'ISR', '2025-08-11 11:49:00', '2025-08-11 11:49:00');
INSERT INTO public.i_s_r_planillas (id, sueldo_inicio, sueldo_fin, porcentaje, tipo, created_at, updated_at) VALUES (4, 67604.37, 99999999.99, 25.00, 'ISR', '2025-08-11 11:49:00', '2025-08-11 11:49:00');
INSERT INTO public.i_s_r_planillas (id, sueldo_inicio, sueldo_fin, porcentaje, tipo, created_at, updated_at) VALUES (5, 0.01, 500000.00, 0.30, 'Vecinal', '2025-08-11 11:49:00', '2025-08-11 11:49:00');
INSERT INTO public.i_s_r_planillas (id, sueldo_inicio, sueldo_fin, porcentaje, tipo, created_at, updated_at) VALUES (6, 500000.01, 10000000.00, 0.40, 'Vecinal', '2025-08-11 11:49:00', '2025-08-11 11:49:00');
INSERT INTO public.i_s_r_planillas (id, sueldo_inicio, sueldo_fin, porcentaje, tipo, created_at, updated_at) VALUES (7, 10000000.01, 20000000.00, 0.30, 'Vecinal', '2025-08-11 11:49:00', '2025-08-11 11:49:00');
INSERT INTO public.i_s_r_planillas (id, sueldo_inicio, sueldo_fin, porcentaje, tipo, created_at, updated_at) VALUES (8, 20000000.01, 30000000.00, 0.20, 'Vecinal', '2025-08-11 11:49:00', '2025-08-11 11:49:00');
INSERT INTO public.i_s_r_planillas (id, sueldo_inicio, sueldo_fin, porcentaje, tipo, created_at, updated_at) VALUES (9, 30000000.01, 99999999.99, 0.15, 'Vecinal', '2025-08-11 11:49:00', '2025-08-11 11:49:00');


--
-- Name: i_s_r_planillas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.i_s_r_planillas_id_seq', 9, true);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: control_asistencia; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (1, 1, '2025-05-30', '08:30:00', '17:00:00', 'Entrada', 'Asistencia normal', '2025-05-30 21:12:39.72089');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (8, 1, '2025-07-24', '17:07:24', '17:08:04', 'Salida', 'Horas incompletas', '2025-07-24 17:08:04.40876');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (18, 84, '2025-07-25', '13:53:50.824026', '13:54:36.336072', 'Salida', 'Horas incompletas', '2025-07-25 13:54:36.336072');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (19, 84, '2025-07-26', '20:02:24.22418', '20:03:00.220553', 'Salida', 'Horas incompletas', '2025-07-25 20:03:00.220553');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (20, 84, '2025-07-27', '18:17:39.841614', '18:19:05.62235', 'Salida', 'Horas incompletas', '2025-07-26 18:19:05.62235');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (21, 84, '2025-07-28', '22:17:20.600516', '22:18:52.943028', 'Salida', 'Horas incompletas', '2025-07-27 22:18:52.943028');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (22, 84, '2025-08-04', '20:50:48.23654', '20:51:46.799662', 'Salida', 'Horas incompletas', '2025-08-03 20:51:46.799662');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (23, 84, '2025-08-05', '13:43:51.688778', '13:44:27.597731', 'Salida', 'Horas incompletas', '2025-08-05 13:44:27.597731');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (32, 84, '2025-08-08', '23:30:16.440999', NULL, 'Entrada', '', '2025-08-08 23:30:16.440999');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (33, 84, '2025-08-09', '00:03:38.335926', '01:10:59.804515', 'Salida', 'Horas incompletas', '2025-08-09 01:10:59.804515');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (80, 84, '2025-08-01', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:35:52.059039');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (81, 84, '2025-08-02', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:35:52.059039');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (82, 84, '2025-08-03', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:35:52.059039');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (83, 84, '2025-08-04', '08:00:00', '19:00:00', 'Entrada', 'Horas extra', '2025-08-12 10:35:52.059039');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (84, 84, '2025-08-05', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:35:52.059039');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (85, 84, '2025-08-06', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:35:52.059039');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (86, 84, '2025-08-07', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:35:52.059039');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (87, 84, '2025-08-08', '08:00:00', '19:00:00', 'Entrada', 'Horas extra', '2025-08-12 10:35:52.059039');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (88, 84, '2025-08-09', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:35:52.059039');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (95, 84, '2025-08-16', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:35:52.059039');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (96, 84, '2025-08-17', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:35:52.059039');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (97, 84, '2025-08-18', '08:00:00', '19:00:00', 'Entrada', 'Horas extra', '2025-08-12 10:35:52.059039');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (98, 84, '2025-08-19', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:35:52.059039');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (99, 84, '2025-08-20', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:35:52.059039');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (100, 84, '2025-08-21', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:35:52.059039');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (101, 84, '2025-08-22', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:35:52.059039');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (102, 84, '2025-08-23', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:35:52.059039');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (103, 84, '2025-08-24', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:35:52.059039');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (104, 84, '2025-08-25', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:35:52.059039');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (105, 84, '2025-08-26', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:35:52.059039');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (106, 84, '2025-08-27', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:35:52.059039');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (107, 84, '2025-08-28', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:35:52.059039');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (108, 84, '2025-08-29', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:35:52.059039');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (109, 84, '2025-08-30', '08:00:00', '19:00:00', 'Entrada', 'Horas extra', '2025-08-12 10:35:52.059039');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (110, 1, '2025-08-01', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (111, 1, '2025-08-02', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (112, 1, '2025-08-03', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (113, 1, '2025-08-04', '08:00:00', '19:00:00', 'Entrada', 'Horas extra', '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (114, 1, '2025-08-05', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (115, 1, '2025-08-06', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (116, 1, '2025-08-07', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (117, 1, '2025-08-08', '08:00:00', '19:00:00', 'Entrada', 'Horas extra', '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (118, 1, '2025-08-09', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (123, 1, '2025-08-14', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (124, 1, '2025-08-15', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (125, 1, '2025-08-16', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (126, 1, '2025-08-17', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (127, 1, '2025-08-18', '08:00:00', '19:00:00', 'Entrada', 'Horas extra', '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (128, 1, '2025-08-19', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (129, 1, '2025-08-20', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (130, 1, '2025-08-21', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (131, 1, '2025-08-22', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (132, 1, '2025-08-23', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (133, 1, '2025-08-24', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (134, 1, '2025-08-25', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (135, 1, '2025-08-26', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (136, 1, '2025-08-27', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (137, 1, '2025-08-28', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (138, 1, '2025-08-29', '08:00:00', '17:00:00', 'Entrada', NULL, '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (139, 1, '2025-08-30', '08:00:00', '19:00:00', 'Entrada', 'Horas extra', '2025-08-12 10:37:14.459842');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (140, 84, '2025-08-12', '11:01:43.272256', '11:02:39.619348', 'Salida', 'Horas incompletas', '2025-08-12 11:02:39.619348');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (141, 84, '2025-08-13', '22:26:28.294499', NULL, 'Entrada', '', '2025-08-13 22:26:28.294499');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (142, 84, '2025-08-14', '03:26:46.718331', '04:06:54.877283', 'Salida', 'Horas incompletas', '2025-08-14 04:06:54.877283');
INSERT INTO public.control_asistencia (id, cod_empleado, fecha, hora_entrada, hora_salida, tipo_registro, observacion, creado_en) VALUES (143, 84, '2025-08-15', '01:18:49.39435', '01:19:04.757748', 'Salida', 'Horas incompletas', '2025-08-15 01:19:04.757748');


--
-- Name: control_asistencia_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.control_asistencia_id_seq', 143, true);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: eventos; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.eventos (id, titulo, fecha_inicio, fecha_fin, todo_el_dia, descripcion, lugar, color_fondo, color_texto, tipo, enlace, recurrente, cod_empleado) VALUES (4, 'Cita m├®dica', '2025-06-11 10:00:00', '2025-06-11 11:00:00', false, 'Chequeo general', 'Cl├¡nica Central', '#00a65a', '#ffffff', 'cita┬ám├®dica', NULL, false, 1);
INSERT INTO public.eventos (id, titulo, fecha_inicio, fecha_fin, todo_el_dia, descripcion, lugar, color_fondo, color_texto, tipo, enlace, recurrente, cod_empleado) VALUES (43, 'Reuni├│n de seguimiento', '2025-08-14 08:00:00', '2025-08-14 09:00:00', true, '', '', '#28a745', '#ffffff', '', '', false, 1);
INSERT INTO public.eventos (id, titulo, fecha_inicio, fecha_fin, todo_el_dia, descripcion, lugar, color_fondo, color_texto, tipo, enlace, recurrente, cod_empleado) VALUES (44, 'Reuni├│n de seguimiento', '2025-08-07 08:00:00', '2025-08-07 09:00:00', false, '', '', '#dc3545', '#ffffff', '', '', false, 1);


--
-- Name: eventos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.eventos_id_seq', 59, true);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: bitacora; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (1, '2025-10-06 11:55:28.279233', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'users', 'EVENT', '45', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"email": "doyuela@didadpol.gob.hn"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (2, '2025-10-06 11:55:28.306436', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', '0inMck0G15jobKWer7PAdFpQzCYWW5G4OON2Zx6L', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiSXhycFlVWDVXOHNTc242MFlYSzJiZHFBR0lYZEFRa0VKYlkzYkpOciI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ1O3M6MTA6Im5vbWJyZV9yb2wiO3M6MTM6IkFETUlOSVNUUkFET1IiO30=", "last_activity": "2025-10-06T11:55:28-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (3, '2025-10-06 11:55:28.306436', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', '0inMck0G15jobKWer7PAdFpQzCYWW5G4OON2Zx6L', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiSXhycFlVWDVXOHNTc242MFlYSzJiZHFBR0lYZEFRa0VKYlkzYkpOciI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ1O3M6MTA6Im5vbWJyZV9yb2wiO3M6MTM6IkFETUlOSVNUUkFET1IiO30=", "last_activity": "2025-10-06T11:55:28-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (4, '2025-10-06 12:11:52.564825', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'SESSION_ROTATE', 'sessions', 'DELETE', '8hrZWw4ABin4BN3jtzzH1oeYLSirwFeJxw68Fucj', 'Sesi├│n antigua eliminada por rotaci├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T11:31:39-06:00", "last_activity_epoch": 1759771899}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (5, '2025-10-06 12:11:52.564825', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'SESSION_ROTATE', 'sessions', 'DELETE', '8hrZWw4ABin4BN3jtzzH1oeYLSirwFeJxw68Fucj', 'Sesi├│n antigua eliminada por rotaci├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T11:31:39-06:00", "last_activity_epoch": 1759771899}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (6, '2025-10-06 12:50:58.959032', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', '0inMck0G15jobKWer7PAdFpQzCYWW5G4OON2Zx6L', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T12:50:55-06:00", "last_activity_epoch": 1759776655}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (7, '2025-10-06 12:50:58.959032', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', '0inMck0G15jobKWer7PAdFpQzCYWW5G4OON2Zx6L', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T12:50:55-06:00", "last_activity_epoch": 1759776655}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (8, '2025-10-06 12:51:05.88745', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'users', 'EVENT', '45', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"email": "doyuela@didadpol.gob.hn"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (9, '2025-10-06 12:51:05.911636', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'Q6NtzFgBoq17T3bagURDq8zxVFYja2PNtk2yTiRk', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoidVFDMFhOdkVGcU5PREI3OUlFZnRSbHFFclRBaE5zZE9BTlZJeTBKUiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ1O3M6MTA6Im5vbWJyZV9yb2wiO3M6MTM6IkFETUlOSVNUUkFET1IiO30=", "last_activity": "2025-10-06T12:51:05-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (10, '2025-10-06 12:51:05.911636', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'Q6NtzFgBoq17T3bagURDq8zxVFYja2PNtk2yTiRk', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoidVFDMFhOdkVGcU5PREI3OUlFZnRSbHFFclRBaE5zZE9BTlZJeTBKUiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ1O3M6MTA6Im5vbWJyZV9yb2wiO3M6MTM6IkFETUlOSVNUUkFET1IiO30=", "last_activity": "2025-10-06T12:51:05-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (11, '2025-10-06 13:04:43.451782', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'Q6NtzFgBoq17T3bagURDq8zxVFYja2PNtk2yTiRk', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T13:04:40-06:00", "last_activity_epoch": 1759777480}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (12, '2025-10-06 13:04:43.451782', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'Q6NtzFgBoq17T3bagURDq8zxVFYja2PNtk2yTiRk', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T13:04:40-06:00", "last_activity_epoch": 1759777480}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (13, '2025-10-06 13:04:54.22615', 38, 'NANCY KARINA CHAVARRIA TACHE', 'LOGIN', 'users', 'EVENT', '38', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"email": "chavarriakarina57@gmail.com"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (14, '2025-10-06 13:04:54.24937', 38, 'NANCY KARINA CHAVARRIA TACHE', 'LOGIN', 'sessions', 'INSERT', 'wMXBVKrLpO99sx9mqTUj1TKRIzR7KCATBqCjSkJs', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiRTJVYnhyZ2hpWWtNb0hxY2JiRUNUNTV1OWNMSXBXMmQ5eUlJYjlDQyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM4O3M6MTA6Im5vbWJyZV9yb2wiO3M6ODoiRU1QTEVBRE8iO30=", "last_activity": "2025-10-06T13:04:54-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (15, '2025-10-06 13:04:54.24937', 38, 'NANCY KARINA CHAVARRIA TACHE', 'LOGIN', 'sessions', 'INSERT', 'wMXBVKrLpO99sx9mqTUj1TKRIzR7KCATBqCjSkJs', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiRTJVYnhyZ2hpWWtNb0hxY2JiRUNUNTV1OWNMSXBXMmQ5eUlJYjlDQyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM4O3M6MTA6Im5vbWJyZV9yb2wiO3M6ODoiRU1QTEVBRE8iO30=", "last_activity": "2025-10-06T13:04:54-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (16, '2025-10-06 13:07:46.099937', 38, 'NANCY KARINA CHAVARRIA TACHE', 'LOGOUT', 'sessions', 'DELETE', 'wMXBVKrLpO99sx9mqTUj1TKRIzR7KCATBqCjSkJs', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T13:07:41-06:00", "last_activity_epoch": 1759777661}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (17, '2025-10-06 13:07:46.099937', 38, 'NANCY KARINA CHAVARRIA TACHE', 'LOGOUT', 'sessions', 'DELETE', 'wMXBVKrLpO99sx9mqTUj1TKRIzR7KCATBqCjSkJs', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T13:07:41-06:00", "last_activity_epoch": 1759777661}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (18, '2025-10-06 13:49:01.747064', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'users', 'EVENT', '45', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"email": "doyuela@didadpol.gob.hn"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (19, '2025-10-06 13:49:01.794924', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'W6h9Gs4zHJGQdjaUS1fr2JHY1hu1a1jZWKNBpFed', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiRWNQSkJQRWpLa1g1QjBOdFZDbnh0cEVWTm5pcDl4VkRONUtXcjM0biI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ1O3M6MTA6Im5vbWJyZV9yb2wiO3M6MTM6IkFETUlOSVNUUkFET1IiO30=", "last_activity": "2025-10-06T13:49:01-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (20, '2025-10-06 13:49:01.794924', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'W6h9Gs4zHJGQdjaUS1fr2JHY1hu1a1jZWKNBpFed', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiRWNQSkJQRWpLa1g1QjBOdFZDbnh0cEVWTm5pcDl4VkRONUtXcjM0biI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ1O3M6MTA6Im5vbWJyZV9yb2wiO3M6MTM6IkFETUlOSVNUUkFET1IiO30=", "last_activity": "2025-10-06T13:49:01-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (21, '2025-10-06 13:54:42.79037', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'W6h9Gs4zHJGQdjaUS1fr2JHY1hu1a1jZWKNBpFed', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T13:54:41-06:00", "last_activity_epoch": 1759780481}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (22, '2025-10-06 13:54:42.79037', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'W6h9Gs4zHJGQdjaUS1fr2JHY1hu1a1jZWKNBpFed', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T13:54:41-06:00", "last_activity_epoch": 1759780481}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (23, '2025-10-06 14:48:47.450958', 34, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN_FAIL', 'users', 'EVENT', '34', 'Contrase├▒a incorrecta', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"motivo": "PASSWORD_INCORRECTO", "intentos_fallidos": 2}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (24, '2025-10-06 14:49:05.523008', 34, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'users', 'EVENT', '34', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"email": "danieloyuela51@gmail.com"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (25, '2025-10-06 14:49:05.554096', 34, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'cIzuxCmHgrGD92qn2PiAJhFX3hRvPNwPaBHiwhnB', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoibXNLM3ROcUx2Ung2MUVWUkpmYUhDdlVEeGlQckp3YUhmSGU4TGRrRyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM0O3M6MTA6Im5vbWJyZV9yb2wiO3M6ODoiRU1QTEVBRE8iO30=", "last_activity": "2025-10-06T14:49:05-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (26, '2025-10-06 14:49:05.554096', 34, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'cIzuxCmHgrGD92qn2PiAJhFX3hRvPNwPaBHiwhnB', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoibXNLM3ROcUx2Ung2MUVWUkpmYUhDdlVEeGlQckp3YUhmSGU4TGRrRyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM0O3M6MTA6Im5vbWJyZV9yb2wiO3M6ODoiRU1QTEVBRE8iO30=", "last_activity": "2025-10-06T14:49:05-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (27, '2025-10-06 14:49:14.71113', 34, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'cIzuxCmHgrGD92qn2PiAJhFX3hRvPNwPaBHiwhnB', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T14:49:06-06:00", "last_activity_epoch": 1759783746}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (28, '2025-10-06 14:49:14.71113', 34, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'cIzuxCmHgrGD92qn2PiAJhFX3hRvPNwPaBHiwhnB', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T14:49:06-06:00", "last_activity_epoch": 1759783746}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (29, '2025-10-06 15:08:21.294829', 34, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'users', 'EVENT', '34', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"email": "danieloyuela51@gmail.com"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (30, '2025-10-06 15:08:21.330156', 34, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'wP5kMBdYuKMy11iciu6OgqQ7PwCTfmcGA7Ocgwpg', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiVXd5WHdFVGdLQ1hiV3ZZTkFMSHI3WmQ0bnJuQlVtdVZmQTJRVm9vZyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM0O3M6MTA6Im5vbWJyZV9yb2wiO3M6ODoiRU1QTEVBRE8iO30=", "last_activity": "2025-10-06T15:08:21-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (31, '2025-10-06 15:08:21.330156', 34, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'wP5kMBdYuKMy11iciu6OgqQ7PwCTfmcGA7Ocgwpg', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiVXd5WHdFVGdLQ1hiV3ZZTkFMSHI3WmQ0bnJuQlVtdVZmQTJRVm9vZyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM0O3M6MTA6Im5vbWJyZV9yb2wiO3M6ODoiRU1QTEVBRE8iO30=", "last_activity": "2025-10-06T15:08:21-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (32, '2025-10-06 15:08:24.197553', 34, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'wP5kMBdYuKMy11iciu6OgqQ7PwCTfmcGA7Ocgwpg', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T15:08:21-06:00", "last_activity_epoch": 1759784901}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (33, '2025-10-06 15:08:24.197553', 34, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'wP5kMBdYuKMy11iciu6OgqQ7PwCTfmcGA7Ocgwpg', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T15:08:21-06:00", "last_activity_epoch": 1759784901}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (34, '2025-10-06 15:12:54.556963', 34, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'users', 'EVENT', '34', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"email": "danieloyuela51@gmail.com"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (35, '2025-10-06 15:12:54.612364', 34, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'xw0xRnuQmpuNXnZSdeaEqYiukkWjh5GDbMvR7TnK', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoid1VCbll4bXdoVzJqSkVJc3gwVm9FVVhqQ3ZWN3hCZ0RCelJBYVFYdSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM0O3M6MTA6Im5vbWJyZV9yb2wiO3M6ODoiRU1QTEVBRE8iO30=", "last_activity": "2025-10-06T15:12:54-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (36, '2025-10-06 15:12:54.612364', 34, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'xw0xRnuQmpuNXnZSdeaEqYiukkWjh5GDbMvR7TnK', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoid1VCbll4bXdoVzJqSkVJc3gwVm9FVVhqQ3ZWN3hCZ0RCelJBYVFYdSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM0O3M6MTA6Im5vbWJyZV9yb2wiO3M6ODoiRU1QTEVBRE8iO30=", "last_activity": "2025-10-06T15:12:54-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (37, '2025-10-06 15:13:14.898995', 34, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'xw0xRnuQmpuNXnZSdeaEqYiukkWjh5GDbMvR7TnK', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T15:13:06-06:00", "last_activity_epoch": 1759785186}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (38, '2025-10-06 15:13:14.898995', 34, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'xw0xRnuQmpuNXnZSdeaEqYiukkWjh5GDbMvR7TnK', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T15:13:06-06:00", "last_activity_epoch": 1759785186}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (39, '2025-10-06 20:27:30.666832', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'users', 'EVENT', '45', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"email": "doyuela@didadpol.gob.hn"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (40, '2025-10-06 20:27:30.722121', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'GDvHIb85DBMdwvfNCireSBeKU2e9BL1mp99qgv0M', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWHhXbDVnMURybUVmeDI4OFdxNmlZeUtubDBOMUE5c3dYMjVIekRGcCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NDU7czoxMDoibm9tYnJlX3JvbCI7czoxMzoiQURNSU5JU1RSQURPUiI7fQ==", "last_activity": "2025-10-06T20:27:30-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (41, '2025-10-06 20:27:30.722121', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'GDvHIb85DBMdwvfNCireSBeKU2e9BL1mp99qgv0M', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWHhXbDVnMURybUVmeDI4OFdxNmlZeUtubDBOMUE5c3dYMjVIekRGcCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NDU7czoxMDoibm9tYnJlX3JvbCI7czoxMzoiQURNSU5JU1RSQURPUiI7fQ==", "last_activity": "2025-10-06T20:27:30-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (42, '2025-10-06 20:33:37.025378', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'GDvHIb85DBMdwvfNCireSBeKU2e9BL1mp99qgv0M', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T20:33:34-06:00", "last_activity_epoch": 1759804414}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (43, '2025-10-06 20:33:37.025378', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'GDvHIb85DBMdwvfNCireSBeKU2e9BL1mp99qgv0M', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T20:33:34-06:00", "last_activity_epoch": 1759804414}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (44, '2025-10-06 20:37:22.62179', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'users', 'EVENT', '45', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"email": "doyuela@didadpol.gob.hn"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (45, '2025-10-06 20:37:22.693389', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'XsE9zhDKEBiQWd27hmPbhFb31G2AsGzSVYyFqO7j', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiUE9oVmc5YWp4U3N0TUxNUnJuc3lzWTlmV25GT1BHTWk3RHhMaERWSiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ1O3M6MTA6Im5vbWJyZV9yb2wiO3M6MTM6IkFETUlOSVNUUkFET1IiO30=", "last_activity": "2025-10-06T20:37:22-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (46, '2025-10-06 20:37:22.693389', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'XsE9zhDKEBiQWd27hmPbhFb31G2AsGzSVYyFqO7j', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiUE9oVmc5YWp4U3N0TUxNUnJuc3lzWTlmV25GT1BHTWk3RHhMaERWSiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ1O3M6MTA6Im5vbWJyZV9yb2wiO3M6MTM6IkFETUlOSVNUUkFET1IiO30=", "last_activity": "2025-10-06T20:37:22-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (47, '2025-10-06 20:38:25.037205', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'XsE9zhDKEBiQWd27hmPbhFb31G2AsGzSVYyFqO7j', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T20:38:23-06:00", "last_activity_epoch": 1759804703}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (48, '2025-10-06 20:38:25.037205', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'XsE9zhDKEBiQWd27hmPbhFb31G2AsGzSVYyFqO7j', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T20:38:23-06:00", "last_activity_epoch": 1759804703}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (49, '2025-10-06 20:39:08.525735', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'users', 'EVENT', '45', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"email": "doyuela@didadpol.gob.hn"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (50, '2025-10-06 20:39:08.574021', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'QBZeDGIVsW8d6Gz7jXk5iT7dMwiAbT9KSBNdtmUi', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiSzhqQWpOa3NOb0MzbkRHWFJMUHFRQndnZ0VLS1JuQURxNkw5MFZGVCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ1O3M6MTA6Im5vbWJyZV9yb2wiO3M6MTM6IkFETUlOSVNUUkFET1IiO30=", "last_activity": "2025-10-06T20:39:08-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (51, '2025-10-06 20:39:08.574021', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'QBZeDGIVsW8d6Gz7jXk5iT7dMwiAbT9KSBNdtmUi', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiSzhqQWpOa3NOb0MzbkRHWFJMUHFRQndnZ0VLS1JuQURxNkw5MFZGVCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ1O3M6MTA6Im5vbWJyZV9yb2wiO3M6MTM6IkFETUlOSVNUUkFET1IiO30=", "last_activity": "2025-10-06T20:39:08-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (52, '2025-10-06 20:43:52.587405', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'QBZeDGIVsW8d6Gz7jXk5iT7dMwiAbT9KSBNdtmUi', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T20:43:49-06:00", "last_activity_epoch": 1759805029}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (53, '2025-10-06 20:43:52.587405', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'QBZeDGIVsW8d6Gz7jXk5iT7dMwiAbT9KSBNdtmUi', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T20:43:49-06:00", "last_activity_epoch": 1759805029}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (54, '2025-10-06 20:50:06.871672', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'users', 'EVENT', '45', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"email": "doyuela@didadpol.gob.hn"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (55, '2025-10-06 20:50:06.902902', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'SWmywP61h8kGYF8dTvWQuvInrUcgyBPkiTDbbhI3', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiVWhrQlJ0YUd0WGVDbzVHSHNGT3l2RWJHTlF3UWphSExCbk9ibDkyTyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ1O3M6MTA6Im5vbWJyZV9yb2wiO3M6MTM6IkFETUlOSVNUUkFET1IiO30=", "last_activity": "2025-10-06T20:50:06-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (56, '2025-10-06 20:50:06.902902', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'SWmywP61h8kGYF8dTvWQuvInrUcgyBPkiTDbbhI3', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiVWhrQlJ0YUd0WGVDbzVHSHNGT3l2RWJHTlF3UWphSExCbk9ibDkyTyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ1O3M6MTA6Im5vbWJyZV9yb2wiO3M6MTM6IkFETUlOSVNUUkFET1IiO30=", "last_activity": "2025-10-06T20:50:06-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (57, '2025-10-06 20:52:00.219227', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'SWmywP61h8kGYF8dTvWQuvInrUcgyBPkiTDbbhI3', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T20:51:48-06:00", "last_activity_epoch": 1759805508}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (58, '2025-10-06 20:52:00.219227', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'SWmywP61h8kGYF8dTvWQuvInrUcgyBPkiTDbbhI3', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T20:51:48-06:00", "last_activity_epoch": 1759805508}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (59, '2025-10-06 20:52:25.375689', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'users', 'EVENT', '45', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"email": "doyuela@didadpol.gob.hn"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (60, '2025-10-06 20:52:25.40401', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'j5YoI2GB3uy6ELw9LeV0s2TG1Ssk9O7FJu3JRdN6', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiRmxROEg3ZFJPMkFyT0R2WTRwYTZhVjhaZnI2T2NpQVBHdGNkemY4NiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ1O3M6MTA6Im5vbWJyZV9yb2wiO3M6MTM6IkFETUlOSVNUUkFET1IiO30=", "last_activity": "2025-10-06T20:52:25-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (61, '2025-10-06 20:52:25.40401', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'j5YoI2GB3uy6ELw9LeV0s2TG1Ssk9O7FJu3JRdN6', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiRmxROEg3ZFJPMkFyT0R2WTRwYTZhVjhaZnI2T2NpQVBHdGNkemY4NiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ1O3M6MTA6Im5vbWJyZV9yb2wiO3M6MTM6IkFETUlOSVNUUkFET1IiO30=", "last_activity": "2025-10-06T20:52:25-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (62, '2025-10-06 20:52:50.554896', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'j5YoI2GB3uy6ELw9LeV0s2TG1Ssk9O7FJu3JRdN6', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T20:52:46-06:00", "last_activity_epoch": 1759805566}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (63, '2025-10-06 20:52:50.554896', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'j5YoI2GB3uy6ELw9LeV0s2TG1Ssk9O7FJu3JRdN6', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T20:52:46-06:00", "last_activity_epoch": 1759805566}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (64, '2025-10-06 20:53:11.00681', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'users', 'EVENT', '45', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"email": "doyuela@didadpol.gob.hn"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (65, '2025-10-06 20:53:11.042298', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'OsVYXPrjfXtcVmN7mdZvVVn7vYO3531sao7OvJoc', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiS2VKTDJqUEN4QnV4and3b3lmOXNydlhYaXhqeWhVUVAza0twUUdDMyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ1O3M6MTA6Im5vbWJyZV9yb2wiO3M6MTM6IkFETUlOSVNUUkFET1IiO30=", "last_activity": "2025-10-06T20:53:11-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (66, '2025-10-06 20:53:11.042298', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'OsVYXPrjfXtcVmN7mdZvVVn7vYO3531sao7OvJoc', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiS2VKTDJqUEN4QnV4and3b3lmOXNydlhYaXhqeWhVUVAza0twUUdDMyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ1O3M6MTA6Im5vbWJyZV9yb2wiO3M6MTM6IkFETUlOSVNUUkFET1IiO30=", "last_activity": "2025-10-06T20:53:11-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (67, '2025-10-06 20:56:38.873861', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'OsVYXPrjfXtcVmN7mdZvVVn7vYO3531sao7OvJoc', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T20:56:34-06:00", "last_activity_epoch": 1759805794}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (68, '2025-10-06 20:56:38.873861', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'OsVYXPrjfXtcVmN7mdZvVVn7vYO3531sao7OvJoc', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T20:56:34-06:00", "last_activity_epoch": 1759805794}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (69, '2025-10-06 20:57:30.599909', 34, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN_FAIL', 'users', 'EVENT', '34', 'Contrase├▒a incorrecta', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"motivo": "PASSWORD_INCORRECTO", "intentos_fallidos": 1}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (70, '2025-10-06 20:57:42.031058', 34, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'users', 'EVENT', '34', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"email": "danieloyuela51@gmail.com"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (71, '2025-10-06 20:57:42.056874', 34, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', '2BzKrZLQWSgRRyKrQOqPNzvSLHW5zNsJKcmUjk1C', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiVE43OXYxeVp4a2lmdmZ5bldQZzdONzhSN0xlZjhFTGgzTGtCamE1cyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM0O3M6MTA6Im5vbWJyZV9yb2wiO3M6ODoiRU1QTEVBRE8iO30=", "last_activity": "2025-10-06T20:57:42-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (72, '2025-10-06 20:57:42.056874', 34, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', '2BzKrZLQWSgRRyKrQOqPNzvSLHW5zNsJKcmUjk1C', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiVE43OXYxeVp4a2lmdmZ5bldQZzdONzhSN0xlZjhFTGgzTGtCamE1cyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM0O3M6MTA6Im5vbWJyZV9yb2wiO3M6ODoiRU1QTEVBRE8iO30=", "last_activity": "2025-10-06T20:57:42-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (73, '2025-10-06 20:58:27.17499', 34, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', '2BzKrZLQWSgRRyKrQOqPNzvSLHW5zNsJKcmUjk1C', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T20:58:25-06:00", "last_activity_epoch": 1759805905}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (74, '2025-10-06 20:58:27.17499', 34, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', '2BzKrZLQWSgRRyKrQOqPNzvSLHW5zNsJKcmUjk1C', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T20:58:25-06:00", "last_activity_epoch": 1759805905}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (75, '2025-10-06 20:58:35.182636', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'users', 'EVENT', '45', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"email": "doyuela@didadpol.gob.hn"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (76, '2025-10-06 20:58:35.212123', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'XAPA25x83F8g4sgKd88doCeAjzpC2fCr57wwYTOi', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiVmdLbzZ4ZmpjdzNIWVEybHdING1QanZGTUtWcGtZWnhYVlR1S1hEZCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ1O3M6MTA6Im5vbWJyZV9yb2wiO3M6MTM6IkFETUlOSVNUUkFET1IiO30=", "last_activity": "2025-10-06T20:58:35-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (77, '2025-10-06 20:58:35.212123', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'XAPA25x83F8g4sgKd88doCeAjzpC2fCr57wwYTOi', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiVmdLbzZ4ZmpjdzNIWVEybHdING1QanZGTUtWcGtZWnhYVlR1S1hEZCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ1O3M6MTA6Im5vbWJyZV9yb2wiO3M6MTM6IkFETUlOSVNUUkFET1IiO30=", "last_activity": "2025-10-06T20:58:35-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (78, '2025-10-06 21:07:47.81314', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'XAPA25x83F8g4sgKd88doCeAjzpC2fCr57wwYTOi', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T21:07:40-06:00", "last_activity_epoch": 1759806460}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (79, '2025-10-06 21:07:47.81314', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'XAPA25x83F8g4sgKd88doCeAjzpC2fCr57wwYTOi', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T21:07:40-06:00", "last_activity_epoch": 1759806460}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (80, '2025-10-06 21:08:03.571233', 38, 'NANCY KARINA CHAVARRIA TACHE', 'LOGIN', 'users', 'EVENT', '38', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"email": "chavarriakarina57@gmail.com"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (81, '2025-10-06 21:08:03.599476', 38, 'NANCY KARINA CHAVARRIA TACHE', 'LOGIN', 'sessions', 'INSERT', 'E4Dpb6NhGqqeGVrGVCoo3IaoCwBYZ3YvqC3h44yT', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiWVB0TURvNjhKbkF6WVZqMWRuUkQ2NmhVeWZNbjhOejRpWlJka3UySyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM4O3M6MTA6Im5vbWJyZV9yb2wiO3M6NDoiVU5BSCI7fQ==", "last_activity": "2025-10-06T21:08:03-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (82, '2025-10-06 21:08:03.599476', 38, 'NANCY KARINA CHAVARRIA TACHE', 'LOGIN', 'sessions', 'INSERT', 'E4Dpb6NhGqqeGVrGVCoo3IaoCwBYZ3YvqC3h44yT', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiWVB0TURvNjhKbkF6WVZqMWRuUkQ2NmhVeWZNbjhOejRpWlJka3UySyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM4O3M6MTA6Im5vbWJyZV9yb2wiO3M6NDoiVU5BSCI7fQ==", "last_activity": "2025-10-06T21:08:03-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (83, '2025-10-06 21:18:35.247134', 38, 'NANCY KARINA CHAVARRIA TACHE', 'LOGOUT', 'sessions', 'DELETE', 'E4Dpb6NhGqqeGVrGVCoo3IaoCwBYZ3YvqC3h44yT', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T21:18:27-06:00", "last_activity_epoch": 1759807107}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (84, '2025-10-06 21:18:35.247134', 38, 'NANCY KARINA CHAVARRIA TACHE', 'LOGOUT', 'sessions', 'DELETE', 'E4Dpb6NhGqqeGVrGVCoo3IaoCwBYZ3YvqC3h44yT', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '{"last_activity": "2025-10-06T21:18:27-06:00", "last_activity_epoch": 1759807107}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (85, '2025-10-17 00:58:49.517056', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'users', 'EVENT', '45', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '{"email": "doyuela@didadpol.gob.hn"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (86, '2025-10-17 00:58:49.90793', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'sIgGj21bNAlUuAVX3oDt0m5KXt7hEdtl70VNLaCa', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiTmtBRFhsMVZOemVYRHczOUVYb2xUTXB2N0VVTUM5MEc2WlNRaEtjdSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ1O3M6MTA6Im5vbWJyZV9yb2wiO3M6MTM6IkFETUlOSVNUUkFET1IiO30=", "last_activity": "2025-10-17T00:58:49-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (87, '2025-10-17 00:58:49.90793', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'sIgGj21bNAlUuAVX3oDt0m5KXt7hEdtl70VNLaCa', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiTmtBRFhsMVZOemVYRHczOUVYb2xUTXB2N0VVTUM5MEc2WlNRaEtjdSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ1O3M6MTA6Im5vbWJyZV9yb2wiO3M6MTM6IkFETUlOSVNUUkFET1IiO30=", "last_activity": "2025-10-17T00:58:49-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (88, '2025-10-17 00:59:27.701381', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'users', 'EVENT', '45', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '{"email": "doyuela@didadpol.gob.hn"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (89, '2025-10-17 00:59:27.71243', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'sIgGj21bNAlUuAVX3oDt0m5KXt7hEdtl70VNLaCa', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '{"last_activity": "2025-10-17T00:59:22-06:00", "last_activity_epoch": 1760684362}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (90, '2025-10-17 00:59:27.71243', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'sIgGj21bNAlUuAVX3oDt0m5KXt7hEdtl70VNLaCa', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '{"last_activity": "2025-10-17T00:59:22-06:00", "last_activity_epoch": 1760684362}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (91, '2025-10-17 00:59:27.730386', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'rYNiAc6KcZNzr9ckuU2xfMyo7Qmvm9ollMGreKGU', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiVjlNOFVZeWhyaUhzV2twTHdzWDVTelJYVzlJYVJ4VlBxNGV3VGhoeCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ1O3M6MTA6Im5vbWJyZV9yb2wiO3M6MTM6IkFETUlOSVNUUkFET1IiO30=", "last_activity": "2025-10-17T00:59:27-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (92, '2025-10-17 00:59:27.730386', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'rYNiAc6KcZNzr9ckuU2xfMyo7Qmvm9ollMGreKGU', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiVjlNOFVZeWhyaUhzV2twTHdzWDVTelJYVzlJYVJ4VlBxNGV3VGhoeCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ1O3M6MTA6Im5vbWJyZV9yb2wiO3M6MTM6IkFETUlOSVNUUkFET1IiO30=", "last_activity": "2025-10-17T00:59:27-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (93, '2025-10-17 01:05:47.293642', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'rYNiAc6KcZNzr9ckuU2xfMyo7Qmvm9ollMGreKGU', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '{"last_activity": "2025-10-17T01:05:45-06:00", "last_activity_epoch": 1760684745}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (94, '2025-10-17 01:05:47.293642', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'rYNiAc6KcZNzr9ckuU2xfMyo7Qmvm9ollMGreKGU', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '{"last_activity": "2025-10-17T01:05:45-06:00", "last_activity_epoch": 1760684745}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (95, '2025-10-18 14:37:22.606588', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'users', 'EVENT', '45', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '{"email": "doyuela@didadpol.gob.hn"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (96, '2025-10-18 14:37:22.807486', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'kTw3iVoJY5z1XHnlMGXueNWYazSssFVBEINFMsIk', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiVHVaN2RMZ0hzQ2JyUnpHa081aXI4YUlQRUxzZ2k2Qkh1c21uTWNmWSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ1O3M6MTA6Im5vbWJyZV9yb2wiO3M6MTM6IkFETUlOSVNUUkFET1IiO30=", "last_activity": "2025-10-18T14:37:22-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (97, '2025-10-18 14:37:22.807486', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGIN', 'sessions', 'INSERT', 'kTw3iVoJY5z1XHnlMGXueNWYazSssFVBEINFMsIk', 'Usuario inici├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '{"payload": "YTo1OntzOjY6Il90b2tlbiI7czo0MDoiVHVaN2RMZ0hzQ2JyUnpHa081aXI4YUlQRUxzZ2k2Qkh1c21uTWNmWSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ1O3M6MTA6Im5vbWJyZV9yb2wiO3M6MTM6IkFETUlOSVNUUkFET1IiO30=", "last_activity": "2025-10-18T14:37:22-06:00"}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (98, '2025-10-18 14:53:02.70433', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'kTw3iVoJY5z1XHnlMGXueNWYazSssFVBEINFMsIk', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '{"last_activity": "2025-10-18T14:53:00-06:00", "last_activity_epoch": 1760820780}');
INSERT INTO public.bitacora (id, fecha, usuario_id, usuario_nombre, tipo_evento, tabla, accion, id_registro, descripcion, ip_origen, navegador, extra) VALUES (99, '2025-10-18 14:53:02.70433', 45, 'DANIEL EDUARDO OYUELA ESTRADA', 'LOGOUT', 'sessions', 'DELETE', 'kTw3iVoJY5z1XHnlMGXueNWYazSssFVBEINFMsIk', 'Usuario cerr├│ sesi├│n', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '{"last_activity": "2025-10-18T14:53:00-06:00", "last_activity_epoch": 1760820780}');


--
-- Name: bitacora_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.bitacora_id_seq', 99, true);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: backup; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.backup (id, nombre_archivo, ruta_archivo, fecha, usuario_id, tipo_backup, tamano, estado) VALUES (28, 'backup_solo_bd_20251006_115939.zip', 'C:\backups\miapp\backup_solo_bd_20251006_115939.zip', '2025-10-06 11:59:40.984519-06', 45, 'solo_bd', 32213, 'listo');
INSERT INTO public.backup (id, nombre_archivo, ruta_archivo, fecha, usuario_id, tipo_backup, tamano, estado) VALUES (29, 'backup_solo_bd_20251006_130459.zip', 'C:\backups\miapp\backup_solo_bd_20251006_130459.zip', '2025-10-06 13:05:00.06074-06', 38, 'solo_bd', 32624, 'listo');
INSERT INTO public.backup (id, nombre_archivo, ruta_archivo, fecha, usuario_id, tipo_backup, tamano, estado) VALUES (30, 'backup_solo_bd_20251006_210411.zip', 'C:\backups\miapp\backup_solo_bd_20251006_210411.zip', '2025-10-06 21:04:11.602431-06', 45, 'solo_bd', 34973, 'listo');


--
-- Name: backup_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.backup_id_seq', 30, true);


--
-- PostgreSQL database dump complete
--

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
-- Data for Name: comprobante_pago; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: comprobante_pago_cod_tipo_comprobante_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.comprobante_pago_cod_tipo_comprobante_seq', 1, false);


--
-- PostgreSQL database dump complete
--

