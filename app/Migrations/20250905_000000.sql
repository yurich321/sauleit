CREATE TABLE `clients` (
                           `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                           `email` varchar(255) NOT NULL,
                           `password_hash` varchar(255) NOT NULL,
                           `name` varchar(200) NOT NULL,
                           `is_active` tinyint(1) NOT NULL DEFAULT 1,
                           `created_at` timestamp NULL DEFAULT current_timestamp(),
                           `gender` enum('male','female','other','unknown') DEFAULT NULL,
                           `birth_date` date DEFAULT NULL,
                           PRIMARY KEY (`id`),
                           UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `client_contacts` (
                                   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                   `client_id` int(10) unsigned NOT NULL,
                                   `type` enum('email','phone') NOT NULL,
                                   `value` varchar(255) NOT NULL,
                                   `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                                   PRIMARY KEY (`id`),
                                   UNIQUE KEY `uq_client_type_value` (`client_id`,`type`,`value`),
                                   KEY `idx_client` (`client_id`),
                                   CONSTRAINT `fk_contact_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `roles` (
                         `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                         `name` varchar(50) NOT NULL,
                         `role` varchar(50) NOT NULL,
                         `home_path` varchar(255) NOT NULL DEFAULT '/',
                         PRIMARY KEY (`id`),
                         UNIQUE KEY `role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
                         `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                         `email` varchar(255) NOT NULL,
                         `password_hash` varchar(255) NOT NULL,
                         `name` varchar(200) NOT NULL,
                         `role_id` int(11) unsigned NOT NULL,
                         `is_active` tinyint(1) NOT NULL DEFAULT 1,
                         `created_at` timestamp NULL DEFAULT current_timestamp(),
                         PRIMARY KEY (`id`),
                         UNIQUE KEY `email` (`email`),
                         KEY `users_roles` (`role_id`),
                         CONSTRAINT `users_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `wallets` (
                           `id` bigint(20) NOT NULL AUTO_INCREMENT,
                           `client_id` int(10) unsigned NOT NULL,
                           `currency` char(3) NOT NULL,
                           `balance_minor` bigint(20) NOT NULL DEFAULT 0,
                           `version` int(11) NOT NULL DEFAULT 0,
                           `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                           PRIMARY KEY (`id`),
                           UNIQUE KEY `uniq_user_currency` (`client_id`,`currency`),
                           CONSTRAINT `fk_wallet_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `wallet_tx` (
                             `id` bigint(20) NOT NULL AUTO_INCREMENT,
                             `wallet_id` bigint(20) NOT NULL,
                             `type` enum('credit','debit','stake','payout','adjust') NOT NULL,
                             `amount_minor` bigint(20) NOT NULL,
                             `balance_after` bigint(20) NOT NULL,
                             `meta_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta_json`)),
                             `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                             PRIMARY KEY (`id`),
                             KEY `idx_wallet_created` (`wallet_id`,`created_at`),
                             CONSTRAINT `fk_tx_wallet` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO roles (NAME,role,home_path) VALUES('admin','admin','/panel/clients');
INSERT INTO users (email,password_hash,NAME,role_id) VALUES
('admin@test.ee', '$2y$10$eOfoi3wYwF4G7aTcdftJdeW0zc4OvaZzkAxZ67j03oz0mjXoQru/6', 'Oliver', 1);

INSERT INTO clients (email,password_hash,NAME,gender, birth_date) VALUES
('olga@test.ee', '$2y$10$eOfoi3wYwF4G7aTcdftJdeW0zc4OvaZzkAxZ67j03oz0mjXoQru/6', 'Olga', 'female','2005-02-19'),
('kirill@test.ee', '$2y$10$eOfoi3wYwF4G7aTcdftJdeW0zc4OvaZzkAxZ67j03oz0mjXoQru/6', 'Kirill', 'male','2005-01-19');

INSERT INTO wallets (client_id,currency,balance_minor) VALUES
(1,'EUR',50000),(1,'USD',40000),(1,'RUB',1000000),
(2,'EUR',30000),(2,'USD',80000),(2,'RUB',1050000);

INSERT INTO client_contacts(client_id, TYPE, VALUE) VALUES
                (1,'phone','+3721568754'),
                (1,'phone','+376516568754');

