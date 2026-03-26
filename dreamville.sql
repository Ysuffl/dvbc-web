--
-- PostgreSQL database dump
--

\restrict sytA57BvjPOGdFRHooysfjIY3Y1Xjpvj00VQEaNrflPI9dusp5k8fnivS8nl8Xu

-- Dumped from database version 18.2
-- Dumped by pg_dump version 18.2

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
-- Name: bookingstatus; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.bookingstatus AS ENUM (
    'PENDING',
    'CONFIRMED',
    'ARRIVED',
    'CANCELLED',
    'COMPLETED',
    'BILLED'
);


ALTER TYPE public.bookingstatus OWNER TO postgres;

--
-- Name: customercategory; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.customercategory AS ENUM (
    'REGULER',
    'EVENT',
    'PRIORITAS',
    'BIG_SPENDER',
    'DRINKER',
    'PARTY',
    'DINNER',
    'LUNCH',
    'FAMILY',
    'YOUNGSTER'
);


ALTER TYPE public.customercategory OWNER TO postgres;

--
-- Name: tablestatus; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.tablestatus AS ENUM (
    'AVAILABLE',
    'BOOKED',
    'OCCUPIED',
    'BILLED',
    'OUT_OF_SERVICE'
);


ALTER TYPE public.tablestatus OWNER TO postgres;

--
-- Name: userrole; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.userrole AS ENUM (
    'ADMIN',
    'STAFF'
);


ALTER TYPE public.userrole OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: bookings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.bookings (
    id integer NOT NULL,
    table_id integer NOT NULL,
    customer_id integer NOT NULL,
    pax integer NOT NULL,
    start_time timestamp without time zone NOT NULL,
    end_time timestamp without time zone NOT NULL,
    billed_at timestamp without time zone,
    billed_price double precision,
    status public.bookingstatus,
    notes character varying,
    cancel_reason character varying
);


ALTER TABLE public.bookings OWNER TO postgres;

--
-- Name: bookings_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.bookings_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.bookings_id_seq OWNER TO postgres;

--
-- Name: bookings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.bookings_id_seq OWNED BY public.bookings.id;


--
-- Name: customers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.customers (
    id integer NOT NULL,
    name character varying NOT NULL,
    phone character varying,
    category public.customercategory,
    created_at timestamp without time zone,
    last_status public.bookingstatus,
    last_visit timestamp without time zone,
    age integer,
    total_spending double precision DEFAULT 0.0,
    master_level character varying(20) DEFAULT 'Bronze'::character varying
);


ALTER TABLE public.customers OWNER TO postgres;

--
-- Name: customers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.customers_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.customers_id_seq OWNER TO postgres;

--
-- Name: customers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.customers_id_seq OWNED BY public.customers.id;


--
-- Name: master_categories; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.master_categories (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    icon character varying(255),
    bg_color character varying(255),
    text_color character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.master_categories OWNER TO postgres;

--
-- Name: master_categories_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.master_categories_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.master_categories_id_seq OWNER TO postgres;

--
-- Name: master_categories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.master_categories_id_seq OWNED BY public.master_categories.id;


--
-- Name: master_levels; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.master_levels (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    min_spending numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    badge_color character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.master_levels OWNER TO postgres;

--
-- Name: master_levels_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.master_levels_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.master_levels_id_seq OWNER TO postgres;

--
-- Name: master_levels_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.master_levels_id_seq OWNED BY public.master_levels.id;


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
-- Name: tables; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tables (
    id integer NOT NULL,
    code character varying NOT NULL,
    x_pos double precision NOT NULL,
    y_pos double precision NOT NULL,
    shape character varying NOT NULL,
    status public.tablestatus,
    area_id character varying NOT NULL
);


ALTER TABLE public.tables OWNER TO postgres;

--
-- Name: tables_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tables_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tables_id_seq OWNER TO postgres;

--
-- Name: tables_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tables_id_seq OWNED BY public.tables.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id integer NOT NULL,
    username character varying NOT NULL,
    hashed_password character varying NOT NULL,
    role public.userrole,
    is_active boolean
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    AS integer
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
-- Name: bookings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bookings ALTER COLUMN id SET DEFAULT nextval('public.bookings_id_seq'::regclass);


--
-- Name: customers id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.customers ALTER COLUMN id SET DEFAULT nextval('public.customers_id_seq'::regclass);


--
-- Name: master_categories id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.master_categories ALTER COLUMN id SET DEFAULT nextval('public.master_categories_id_seq'::regclass);


--
-- Name: master_levels id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.master_levels ALTER COLUMN id SET DEFAULT nextval('public.master_levels_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: tables id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tables ALTER COLUMN id SET DEFAULT nextval('public.tables_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: bookings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.bookings (id, table_id, customer_id, pax, start_time, end_time, billed_at, billed_price, status, notes, cancel_reason) FROM stdin;
59	560	23	2	2026-03-24 15:45:00	2026-03-24 16:35:00	\N	\N	PENDING	\N	\N
60	552	24	2	2026-03-24 15:55:00	2026-03-24 17:55:00	2026-03-24 15:56:40.864558	2000000	COMPLETED	\N	\N
61	566	25	2	2026-03-25 14:52:00	2026-03-25 16:52:00	2026-03-25 14:57:36.280181	8000000	COMPLETED	\N	\N
63	566	24	2	2026-03-25 17:00:00	2026-03-25 18:30:00	\N	\N	PENDING	\N	\N
62	552	23	2	2026-03-25 16:00:00	2026-03-25 18:00:00	\N	\N	CONFIRMED	\N	\N
\.


--
-- Data for Name: customers; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.customers (id, name, phone, category, created_at, last_status, last_visit, age, total_spending, master_level) FROM stdin;
23	Muhammad Yusuf	087784415346	PRIORITAS	2026-03-24 15:37:43.958549	\N	\N	18	0	Bronze
24	arif	081384716616	REGULER	2026-03-24 15:56:02.354235	\N	\N	24	0	Bronze
25	Nafil	087965476348	PRIORITAS	2026-03-25 14:54:23.428989	\N	\N	18	8000000	Platinum
\.


--
-- Data for Name: master_categories; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.master_categories (id, name, icon, bg_color, text_color, created_at, updated_at) FROM stdin;
1	REGULER	user	bg-slate-50	text-slate-500	\N	\N
2	EVENT	megaphone	bg-indigo-50/50	text-indigo-600	\N	\N
3	PRIORITAS	crown	bg-amber-50/50	text-amber-600	\N	\N
4	BIG_SPENDER	dollar-sign	bg-emerald-50/50	text-emerald-600	\N	\N
5	DRINKER	glass-water	bg-blue-50/50	text-blue-600	\N	\N
6	PARTY	sparkles	bg-purple-50/50	text-purple-600	\N	\N
7	DINNER	utensils-crossed	bg-orange-50/50	text-orange-600	\N	\N
8	LUNCH	utensils	bg-rose-50/50	text-rose-600	\N	\N
9	FAMILY	users	bg-cyan-50/50	text-cyan-600	\N	\N
10	YOUNGSTER	smile	bg-pink-50/50	text-pink-600	\N	\N
\.


--
-- Data for Name: master_levels; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.master_levels (id, name, min_spending, badge_color, created_at, updated_at) FROM stdin;
1	Bronze	0.00	bg-orange-100 text-orange-800	\N	\N
2	Silver	1000000.00	bg-slate-200 text-slate-800	\N	\N
4	Platinum	7000000.00	bg-blue-100 text-blue-800	\N	\N
3	Gold	3000000.00	bg-yellow-200 text-yellow-800	\N	\N
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	2026_03_24_145139_create_master_tables	1
\.


--
-- Data for Name: tables; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tables (id, code, x_pos, y_pos, shape, status, area_id) FROM stdin;
557	07	260	480	rectangle	AVAILABLE	VIP_OTIC
558	05	150	530	rectangle	AVAILABLE	VIP_OTIC
575	BS10	350	150	rectangle	AVAILABLE	MOON AREA
576	BS9	450	150	rectangle	AVAILABLE	MOON AREA
577	BS8	550	150	rectangle	AVAILABLE	MOON AREA
578	BS7	650	150	rectangle	AVAILABLE	MOON AREA
579	BS6	750	150	rectangle	AVAILABLE	MOON AREA
580	BS5	850	150	rectangle	AVAILABLE	MOON AREA
581	BS4	950	150	rectangle	AVAILABLE	MOON AREA
582	BS3	1050	150	rectangle	AVAILABLE	MOON AREA
649	CB1	120	820	rectangle	AVAILABLE	CUP ARENA
650	W1	450	300	rectangle	AVAILABLE	CUP ARENA
651	W2	415	400	rectangle	AVAILABLE	CUP ARENA
652	W3	380	300	rectangle	AVAILABLE	CUP ARENA
653	W4	415	200	rectangle	AVAILABLE	CUP ARENA
654	VC1	200	80	rectangle	AVAILABLE	VIP CABANA & STAR
655	VC2	650	80	rectangle	AVAILABLE	VIP CABANA & STAR
656	VC3	1200	80	rectangle	AVAILABLE	VIP CABANA & STAR
657	VC4	1650	80	rectangle	AVAILABLE	VIP CABANA & STAR
658	P10	50	250	circle	AVAILABLE	VIP CABANA & STAR
659	P9	50	550	circle	AVAILABLE	VIP CABANA & STAR
660	P8	180	950	circle	AVAILABLE	VIP CABANA & STAR
661	P7	350	950	circle	AVAILABLE	VIP CABANA & STAR
662	P6	620	950	circle	AVAILABLE	VIP CABANA & STAR
663	P5	840	950	circle	AVAILABLE	VIP CABANA & STAR
664	P4	1060	950	circle	AVAILABLE	VIP CABANA & STAR
665	P3	1280	950	circle	AVAILABLE	VIP CABANA & STAR
666	P2	1500	950	circle	AVAILABLE	VIP CABANA & STAR
667	P1	1720	950	circle	AVAILABLE	VIP CABANA & STAR
668	TG8	750	300	rectangle	AVAILABLE	VIP CABANA & STAR
669	TG9	620	300	rectangle	AVAILABLE	VIP CABANA & STAR
670	TG10	490	300	rectangle	AVAILABLE	VIP CABANA & STAR
671	TG11	360	300	rectangle	AVAILABLE	VIP CABANA & STAR
672	TG12	230	300	rectangle	AVAILABLE	VIP CABANA & STAR
673	TG3	620	480	rectangle	AVAILABLE	VIP CABANA & STAR
674	TG4	490	480	rectangle	AVAILABLE	VIP CABANA & STAR
675	TG5	360	480	rectangle	AVAILABLE	VIP CABANA & STAR
566	M1	1020	200	rectangle	BOOKED	VIP_OTIC
552	09	150	200	rectangle	OCCUPIED	VIP_OTIC
676	TG6	230	480	rectangle	AVAILABLE	VIP CABANA & STAR
567	M2	1020	300	rectangle	AVAILABLE	VIP_OTIC
647	CB3	120	660	rectangle	AVAILABLE	CUP ARENA
648	CB2	120	740	rectangle	AVAILABLE	CUP ARENA
553	010	260	200	rectangle	AVAILABLE	VIP_OTIC
563	R3	610	590	rectangle	AVAILABLE	VIP_OTIC
559	R5	450	480	rectangle	AVAILABLE	VIP_OTIC
562	R4	500	590	rectangle	AVAILABLE	VIP_OTIC
565	R1	860	590	rectangle	AVAILABLE	VIP_OTIC
601	D20	1000	420	rectangle	AVAILABLE	MOON AREA
561	R7	860	480	rectangle	AVAILABLE	VIP_OTIC
556	06	150	400	rectangle	AVAILABLE	VIP_OTIC
641	CB9	120	180	rectangle	AVAILABLE	CUP ARENA
642	CB8	120	260	rectangle	AVAILABLE	CUP ARENA
643	CB7	120	340	rectangle	AVAILABLE	CUP ARENA
573	03	1350	360	circle	AVAILABLE	VIP_OTIC
574	04	1330	550	rectangle	AVAILABLE	VIP_OTIC
570	M5	1020	600	rectangle	AVAILABLE	VIP_OTIC
568	M3	1020	400	rectangle	AVAILABLE	VIP_OTIC
572	02	1260	450	rectangle	AVAILABLE	VIP_OTIC
569	M4	1020	500	rectangle	AVAILABLE	VIP_OTIC
571	01	1150	450	rectangle	AVAILABLE	VIP_OTIC
554	011	370	200	rectangle	AVAILABLE	VIP_OTIC
583	BS2	1150	150	rectangle	AVAILABLE	MOON AREA
584	BS1	1250	150	rectangle	AVAILABLE	MOON AREA
585	D1	130	320	rectangle	AVAILABLE	MOON AREA
586	D2	210	320	rectangle	AVAILABLE	MOON AREA
587	D3	290	320	rectangle	AVAILABLE	MOON AREA
588	D4	370	320	rectangle	AVAILABLE	MOON AREA
589	D5	450	320	rectangle	AVAILABLE	MOON AREA
590	D6	530	320	rectangle	AVAILABLE	MOON AREA
591	D7	610	320	rectangle	AVAILABLE	MOON AREA
592	D8	690	320	rectangle	AVAILABLE	MOON AREA
593	D10	150	500	rectangle	AVAILABLE	MOON AREA
594	D11	300	500	rectangle	AVAILABLE	MOON AREA
595	D12	380	650	rectangle	AVAILABLE	MOON AREA
596	D13	300	800	rectangle	AVAILABLE	MOON AREA
597	D14	150	800	rectangle	AVAILABLE	MOON AREA
598	D15	30	800	rectangle	AVAILABLE	MOON AREA
599	D9	100	650	rectangle	AVAILABLE	MOON AREA
600	D16	30	650	rectangle	AVAILABLE	MOON AREA
602	D21	1120	420	rectangle	AVAILABLE	MOON AREA
603	D22	1240	420	rectangle	AVAILABLE	MOON AREA
604	D23	1000	570	rectangle	AVAILABLE	MOON AREA
605	D24	1120	570	rectangle	AVAILABLE	MOON AREA
606	D25	1240	570	rectangle	AVAILABLE	MOON AREA
607	D26	1000	720	rectangle	AVAILABLE	MOON AREA
608	D27	1120	720	rectangle	AVAILABLE	MOON AREA
609	D28	1240	720	rectangle	AVAILABLE	MOON AREA
610	D29	1000	870	rectangle	AVAILABLE	MOON AREA
611	D30	1120	870	rectangle	AVAILABLE	MOON AREA
612	S1	100	100	rectangle	AVAILABLE	POOL AREA
613	S2	220	100	rectangle	AVAILABLE	POOL AREA
614	S3	100	280	rectangle	AVAILABLE	POOL AREA
615	S4	220	280	rectangle	AVAILABLE	POOL AREA
616	S5	340	280	rectangle	AVAILABLE	POOL AREA
617	S6	460	280	rectangle	AVAILABLE	POOL AREA
618	S7	580	280	rectangle	AVAILABLE	POOL AREA
619	S14	100	450	rectangle	AVAILABLE	POOL AREA
620	S13	180	450	rectangle	AVAILABLE	POOL AREA
621	S12	260	450	rectangle	AVAILABLE	POOL AREA
622	S11	340	450	rectangle	AVAILABLE	POOL AREA
623	S10	420	450	rectangle	AVAILABLE	POOL AREA
624	S9	500	450	rectangle	AVAILABLE	POOL AREA
625	S8	580	450	rectangle	AVAILABLE	POOL AREA
626	S20	100	580	rectangle	AVAILABLE	POOL AREA
627	S21	180	580	rectangle	AVAILABLE	POOL AREA
628	S22	260	580	rectangle	AVAILABLE	POOL AREA
629	S23	340	580	rectangle	AVAILABLE	POOL AREA
630	S24	420	580	rectangle	AVAILABLE	POOL AREA
631	S25	500	580	rectangle	AVAILABLE	POOL AREA
632	S26	580	580	rectangle	AVAILABLE	POOL AREA
633	PB1	750	480	rectangle	AVAILABLE	POOL AREA
634	PB2	810	480	rectangle	AVAILABLE	POOL AREA
635	PB3	870	480	rectangle	AVAILABLE	POOL AREA
636	PB4	930	480	rectangle	AVAILABLE	POOL AREA
637	PB5	750	580	rectangle	AVAILABLE	POOL AREA
638	PB6	810	580	rectangle	AVAILABLE	POOL AREA
639	PB7	870	580	rectangle	AVAILABLE	POOL AREA
640	PB8	930	580	rectangle	AVAILABLE	POOL AREA
644	CB6	120	420	rectangle	AVAILABLE	CUP ARENA
645	CB5	120	500	rectangle	AVAILABLE	CUP ARENA
646	CB4	120	580	rectangle	AVAILABLE	CUP ARENA
564	R2	750	590	rectangle	AVAILABLE	VIP_OTIC
555	08	260	350	rectangle	AVAILABLE	VIP_OTIC
677	TG7	750	300	rectangle	AVAILABLE	VIP CABANA & STAR
678	TG1	750	480	rectangle	AVAILABLE	VIP CABANA & STAR
679	TG2	620	480	rectangle	AVAILABLE	VIP CABANA & STAR
680	SB1	950	150	rectangle	AVAILABLE	VIP CABANA & STAR
681	SB2	950	330	rectangle	AVAILABLE	VIP CABANA & STAR
682	SB3	950	510	rectangle	AVAILABLE	VIP CABANA & STAR
683	SB4	950	690	rectangle	AVAILABLE	VIP CABANA & STAR
684	SB5	1100	150	rectangle	AVAILABLE	VIP CABANA & STAR
685	SB6	1100	330	rectangle	AVAILABLE	VIP CABANA & STAR
686	SB7	1100	510	rectangle	AVAILABLE	VIP CABANA & STAR
687	SB8	1100	690	rectangle	AVAILABLE	VIP CABANA & STAR
688	G9	830	300	rectangle	AVAILABLE	VIP CABANA & STAR
689	G8	830	480	rectangle	AVAILABLE	VIP CABANA & STAR
690	G1	1250	380	rectangle	AVAILABLE	VIP CABANA & STAR
691	G2	1250	580	rectangle	AVAILABLE	VIP CABANA & STAR
692	G3	1250	780	rectangle	AVAILABLE	VIP CABANA & STAR
693	G4	1050	780	rectangle	AVAILABLE	VIP CABANA & STAR
694	G5	830	700	rectangle	AVAILABLE	VIP CABANA & STAR
695	G6	620	700	rectangle	AVAILABLE	VIP CABANA & STAR
696	G7	410	700	rectangle	AVAILABLE	VIP CABANA & STAR
697	B1	1900	320	rectangle	AVAILABLE	VIP CABANA & STAR
698	B2	2120	320	rectangle	AVAILABLE	VIP CABANA & STAR
699	B3	2340	320	rectangle	AVAILABLE	VIP CABANA & STAR
700	B4	1900	640	rectangle	AVAILABLE	VIP CABANA & STAR
701	B5	2120	640	rectangle	AVAILABLE	VIP CABANA & STAR
702	B6	2340	640	rectangle	AVAILABLE	VIP CABANA & STAR
703	B7	1900	960	rectangle	AVAILABLE	VIP CABANA & STAR
704	B8	2120	960	rectangle	AVAILABLE	VIP CABANA & STAR
705	B9	2340	960	rectangle	AVAILABLE	VIP CABANA & STAR
706	B10	1900	320	rectangle	AVAILABLE	VIP CABANA & STAR
707	CB10	2700	1140	rectangle	AVAILABLE	VIP CABANA & STAR
708	CB11	2700	960	rectangle	AVAILABLE	VIP CABANA & STAR
709	CB12	2700	780	rectangle	AVAILABLE	VIP CABANA & STAR
710	CB13	2700	600	rectangle	AVAILABLE	VIP CABANA & STAR
560	R6	750	480	rectangle	AVAILABLE	VIP_OTIC
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, username, hashed_password, role, is_active) FROM stdin;
1	admin	$2b$12$yD6jCQY1nipcqbuE6sxIDOoGE/Vjv3KH.yo.xhe0vM8ezEGd4BMFO	ADMIN	t
\.


--
-- Name: bookings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.bookings_id_seq', 63, true);


--
-- Name: customers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.customers_id_seq', 25, true);


--
-- Name: master_categories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.master_categories_id_seq', 10, true);


--
-- Name: master_levels_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.master_levels_id_seq', 4, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 1, true);


--
-- Name: tables_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tables_id_seq', 710, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 1, true);


--
-- Name: bookings bookings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_pkey PRIMARY KEY (id);


--
-- Name: customers customers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_pkey PRIMARY KEY (id);


--
-- Name: master_categories master_categories_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.master_categories
    ADD CONSTRAINT master_categories_name_unique UNIQUE (name);


--
-- Name: master_categories master_categories_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.master_categories
    ADD CONSTRAINT master_categories_pkey PRIMARY KEY (id);


--
-- Name: master_levels master_levels_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.master_levels
    ADD CONSTRAINT master_levels_name_unique UNIQUE (name);


--
-- Name: master_levels master_levels_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.master_levels
    ADD CONSTRAINT master_levels_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: tables tables_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tables
    ADD CONSTRAINT tables_pkey PRIMARY KEY (id);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: ix_bookings_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX ix_bookings_id ON public.bookings USING btree (id);


--
-- Name: ix_customers_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX ix_customers_id ON public.customers USING btree (id);


--
-- Name: ix_tables_code; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX ix_tables_code ON public.tables USING btree (code);


--
-- Name: ix_tables_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX ix_tables_id ON public.tables USING btree (id);


--
-- Name: ix_users_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX ix_users_id ON public.users USING btree (id);


--
-- Name: ix_users_username; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX ix_users_username ON public.users USING btree (username);


--
-- Name: bookings bookings_customer_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_customer_id_fkey FOREIGN KEY (customer_id) REFERENCES public.customers(id);


--
-- Name: bookings bookings_table_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_table_id_fkey FOREIGN KEY (table_id) REFERENCES public.tables(id);


--
-- PostgreSQL database dump complete
--

\unrestrict sytA57BvjPOGdFRHooysfjIY3Y1Xjpvj00VQEaNrflPI9dusp5k8fnivS8nl8Xu

