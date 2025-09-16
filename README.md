# Minimal PHP Project for take-home assignment

A minimal PHP project with a tiny router, middlewares, client area, and an admin panel for settling bets (win/lose) and tracking wallets with transaction history.

## Features
- Client & admin layouts (`/dashboard`, `/panel`).
- Wallets per currency + transaction log (`wallet_tx` table).
- Bet settlement in admin: **win** credits payout, **lose** leaves balance unchanged.
- CSRF protection for forms.
- Clean grid-based UI (no HTML like `<table>`), mobile-friendly.

## Requirements
- PHP 8.1+ (PDO).
- MySQL/MariaDB.
- Web server pointing **DocumentRoot** to `public/` (Apache .htaccess included).

## Installation
1. Unzip or clone the project.
2. Create a database and import SQL:
    - Base migration: `app/Migrations/20250905_000000.sql`
    - Plus the tables below if not included yet (clients, client_contacts, wallets, wallet_tx, roles, users).
3. Configure DB/PDO bootstrap (where you create the `Database` instance) to point to your DB.
4. Ensure your vhost points to `public/` and URL rewriting is enabled.
5. To run the project (database access), rename config/app_default.php to config/app.php and enter your own DB credentials.
6. (Optional) If using Composer autoload, ensure `vendor/autoload.php` is available or adjust bootstrap accordingly.

## Tables (DDL)

```sql

-- Roles
CREATE TABLE `roles` (
     `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
     `name` varchar(50) NOT NULL,
     `role` varchar(50) NOT NULL,
     `home_path` varchar(255) NOT NULL DEFAULT '/',
     PRIMARY KEY (`id`),
     UNIQUE KEY `role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Users
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Clients
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

-- Client contacts
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

-- Wallets
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

-- Wallet transactions
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
```

## Directory Structure
```
app/                # Core, Controllers, Middleware, Repositories, Services
public/             # Public web root (index.php, assets, js, css)
views/              # View templates (client/login/panel)
app/src/helpers.php # Helpers (csrf(), view(), repoAdmin(), wallet service)
```

## Routing (key endpoints)
- **Client**
    - `GET /dashboard` — client UI.
- **Admin (panel)**
    - `GET /panel/clients` — clients list.
    - `GET /panel/clients/{id}` — client details (contacts, wallets, bet history).
    - `POST /panel/bets/settle` — settle a bet. Params:
        - `stake_id` (int), `result` = `win` | `lose` (CSRF required).
        - Response JSON includes `{ ok: true, result, wallet}` with updated balance.

## Bet Settlement Logic
- **lose** — marks stake as settled with `result=lose`; balance stays unchanged.
- **win** — credits payout into wallet (new `wallet_tx` row of type `payout`) and updates wallet balance atomically.

## Frontend Notes
- Bet list uses CSS Grid (no HTML like `<table>`).
- Mobile layout: header hidden, rows transform into cards (grid-areas).
- JS: a single delegated handler intercepts `.settle-form` submit, posts via `post()`, updates row status + wallet balance without reload.

## Security
- CSRF middleware: include `<?= csrf()->field(); ?>` Cross-Site Request Forgery, in forms.
- Prepared statements in DB layer.
- Transactions around balance updates and settlements.
