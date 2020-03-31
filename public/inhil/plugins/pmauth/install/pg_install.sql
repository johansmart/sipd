----------------------------------------------------
-- create pmatuh_roles table
----------------------------------------------------
CREATE TABLE pmauth_roles (
    id integer DEFAULT 1 NOT NULL,
    role character varying NOT NULL
);

COMMENT ON TABLE pmauth_roles IS 'Table for role uers';

ALTER TABLE ONLY pmauth_roles
    ADD CONSTRAINT pkey_pmauth_roles PRIMARY KEY (id);

ALTER TABLE ONLY pmauth_roles
    ADD CONSTRAINT unique_pmauth_roles UNIQUE (role);

-- insert first data
INSERT INTO pmauth_roles (id, role) VALUES (0, 'admin');
INSERT INTO pmauth_roles (id, role) VALUES (1, 'login');


----------------------------------------------------
-- create table pmauth_users
----------------------------------------------------

CREATE TABLE pmauth_users (
    id integer NOT NULL,
    username character varying,
    password character varying(32),
    id_role integer
);

COMMENT ON TABLE pmauth_users IS 'Users table';

CREATE SEQUENCE pmauth_users_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

ALTER SEQUENCE pmauth_users_id_seq OWNED BY pmauth_users.id;

SELECT pg_catalog.setval('pmauth_users_id_seq', 8, true);

ALTER TABLE pmauth_users ALTER COLUMN id SET DEFAULT nextval('pmauth_users_id_seq'::regclass);

ALTER TABLE ONLY pmauth_users
    ADD CONSTRAINT pk_pmauth_users PRIMARY KEY (id);

ALTER TABLE ONLY pmauth_users
    ADD CONSTRAINT unic_username_pmauth_users UNIQUE (username);

ALTER TABLE ONLY pmauth_users
    ADD CONSTRAINT fkey_pmauth_users FOREIGN KEY (id_role) REFERENCES pmauth_roles(id);

-- Insert data

INSERT INTO pmauth_users (id, username, password, id_role) VALUES (0, 'admin', '21232f297a57a5a743894a0e4a801fc3', 0);



----------------------------------------------------
-- create table pmauth_configs
----------------------------------------------------
CREATE TABLE pmauth_configs (
    id integer NOT NULL,
    id_users integer,
    configs character varying
);



COMMENT ON TABLE pmauth_configs IS 'Configs for users';

-- create sequence for table pmauth_configs
CREATE SEQUENCE pmauth_configs_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

ALTER SEQUENCE pmauth_configs_id_seq OWNED BY pmauth_configs.id;

SELECT pg_catalog.setval('pmauth_configs_id_seq', 0, true);

ALTER TABLE pmauth_configs ALTER COLUMN id SET DEFAULT nextval('pmauth_configs_id_seq'::regclass);

ALTER TABLE ONLY pmauth_configs
    ADD CONSTRAINT fk_pmauth_configs FOREIGN KEY (id_users) REFERENCES pmauth_users(id) ON DELETE CASCADE;

-- insert first data
INSERT INTO pmauth_configs (id, id_users, configs) VALUES (0, 0, 'a:2:{s:3:"def";s:7:"default";s:4:"cfgs";a:1:{i:0;s:7:"default";}}');

