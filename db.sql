-- Usuarios

CREATE TABLE
    `users` (
        `id` varchar(50) NOT NULL,
        `nombre` varchar(20) NOT NULL,
        `saldo` float DEFAULT 0,
        `created_at` datetime NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Transacciones entre usuarios

CREATE TABLE
    `transactions` (
        `id` varchar(50) NOT NULL,
        `mount` float NOT NULL COMMENT 'Monto a depositar',
        `created_at` datetime NOT NULL DEFAULT current_timestamp(),
        `user_id` varchar(50) NOT NULL COMMENT 'Usuario que deposita',
        `target_id` varchar(50) NOT NULL COMMENT 'Usuario a depositar',
        PRIMARY KEY (`id`),
        KEY `fk_user_transactions_1` (`user_id`),
        CONSTRAINT `fk_user_transactions_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;