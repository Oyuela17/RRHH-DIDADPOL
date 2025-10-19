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
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.roles_id_seq', 81, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 61, true);


--
-- PostgreSQL database dump complete
--

