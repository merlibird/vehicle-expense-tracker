-- =====================================================================
--  vehicle-expense-tracker
--  MySQL / MariaDB (InnoDb) Database Initialization + Testdata
--  Import: ddev import-db --file=etc/sql/vehicle-expense-tracker.sql
-- =====================================================================

-- =====================================================================
--  TABLES
-- =====================================================================

-- ---------------------------------------------------------------------
--  user
-- ---------------------------------------------------------------------
CREATE TABLE user (
    id            INT UNSIGNED         NOT NULL AUTO_INCREMENT,
    user_name     VARCHAR(50)          NOT NULL,
    password_hash VARCHAR(255)         NOT NULL,
    first_name    VARCHAR(100)         NOT NULL,
    last_name     VARCHAR(100)         NOT NULL,
    profile_pic   VARCHAR(255)             NULL,
    role          ENUM('user','admin') NOT NULL DEFAULT 'user',
    is_active     TINYINT(1)           NOT NULL DEFAULT 1,
    created_at    DATETIME             NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME             NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_user_user_name (user_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  expense_category
-- ---------------------------------------------------------------------
CREATE TABLE expense_category (
    id    INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name  VARCHAR(100)    NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_category_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  vehicle
-- ---------------------------------------------------------------------
CREATE TABLE vehicle (
    id                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id           INT UNSIGNED    NOT NULL,
    brand             VARCHAR(100)    NOT NULL,
    model             VARCHAR(100)    NOT NULL,
    license_plate     VARCHAR(20)     NOT NULL,
    init_registration DATE            NOT NULL,
    is_active         TINYINT(1)      NOT NULL DEFAULT 1,
    created_at        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_vehicle_user FOREIGN KEY (user_id) REFERENCES user (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  expense
-- ---------------------------------------------------------------------
CREATE TABLE expense (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    vehicle_id      INT UNSIGNED    NOT NULL,
    date            DATE            NOT NULL,
    cost            DECIMAL(10,2)   NOT NULL,
    note            VARCHAR(500)        NULL,
    mileage         INT UNSIGNED        NULL,
    is_active       TINYINT(1)      NOT NULL DEFAULT 1,
    is_fuel_expense TINYINT(1)      NOT NULL DEFAULT 0,
    liters          DECIMAL(7,2)        NULL,
    price_per_liter DECIMAL(6,3)        NULL,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_expense_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicle (id),
    CONSTRAINT chk_fuel_fields CHECK (
            (is_fuel_expense = 1 AND liters IS NOT NULL AND price_per_liter IS NOT NULL)
         OR (is_fuel_expense = 0 AND liters IS NULL     AND price_per_liter IS NULL)
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  expense_category_map
-- ---------------------------------------------------------------------
CREATE TABLE expense_category_map (
    expense_id    INT UNSIGNED    NOT NULL,
    category_id   INT UNSIGNED    NOT NULL,
    PRIMARY KEY (expense_id, category_id),
    CONSTRAINT fk_map_expense FOREIGN KEY (expense_id) REFERENCES expense (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_map_category FOREIGN KEY (category_id) REFERENCES expense_category (id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

-- ---------------------------------------------------------------------
--  log_entry
-- ---------------------------------------------------------------------
CREATE TABLE log_entry (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id       INT UNSIGNED        NULL,
    username      VARCHAR(50)     NOT NULL,
    ip_address    VARCHAR(45)     NOT NULL,
    action        VARCHAR(255)    NOT NULL,
    time_stamp    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
--  TESTDATA
-- =====================================================================
 
-- Users (Password for all users: test1234)
INSERT INTO user
    (id,user_name,password_hash,first_name,last_name,profile_pic,role,is_active) VALUES
    (1,'admin',      '$2y$10$ip0e5KAsYABHvPni55PQAOcjY4hHForfvqG3Y7Zr9rH7.xb4UUjT6','System','Admin', NULL,'admin',1),
    (2,'maxmuster',  '$2y$10$ip0e5KAsYABHvPni55PQAOcjY4hHForfvqG3Y7Zr9rH7.xb4UUjT6','Max','Mustermann','max.jpg','user',1),
    (3,'erikamuster','$2y$10$ip0e5KAsYABHvPni55PQAOcjY4hHForfvqG3Y7Zr9rH7.xb4UUjT6','Erika','Musterfrau',NULL,'user',1),
    (4,'inaktiv',    '$2y$10$ip0e5KAsYABHvPni55PQAOcjY4hHForfvqG3Y7Zr9rH7.xb4UUjT6','Inaktiver','Nutzer',NULL,'user',0);
 
-- Categories
INSERT INTO expense_category (id,name) VALUES
    (1,'Tanken'),
    (2,'Werkstatt'),
    (3,'Versicherung'),
    (4,'Steuer'),
    (5,'Sonstiges');
 
-- Vehicles
INSERT INTO vehicle
    (id,user_id,brand,model,license_plate,init_registration,is_active) VALUES
    (1,2,'Volkswagen','Golf 1.6 TDI','W-12345A','2019-03-15',1),
    (2,2,'BMW','320d','W-99887B','2021-06-01',1),
    (3,3,'Audi','A3 Sportback','L-55421C','2018-11-20',1),
    (4,3,'Opel','Corsa','L-10010D','2012-04-10',0),
    (5,2,'Skoda','Octavia Combi','W-44521E','2020-09-12',1),
    (6,2,'Tesla','Model 3','W-30303F','2023-02-28',1),
    (7,2,'Ford','Focus 1.0','W-77810G','2017-05-22',1),
    (8,2,'Mercedes-Benz','C 220 d','W-66600H','2022-11-05',1),
    (9,3,'Volkswagen','Polo 1.0','L-21210J','2020-07-14',1);

-- Expenses
INSERT INTO expense
    (id,vehicle_id,date,cost,note,mileage,is_active,is_fuel_expense,liters,price_per_liter) VALUES
    -- Max / VW Golf (vehicle 1, Diesel)
    (1, 1,'2025-07-05', 75.20,'Tankfuellung Diesel',  41000,1, 1, 44.10, 1.705),
    (2, 1,'2025-08-12', 79.80,'Tankfuellung Diesel',  41850,1, 1, 46.00, 1.735),
    (3, 1,'2025-09-03',189.00,'Service + Oelwechsel', 42400,1, 0, NULL,  NULL),
    (4, 1,'2025-10-20', 82.10,'Tankfuellung Diesel',  43200,1, 1, 47.20, 1.739),
    (5, 1,'2025-12-01',520.00,'KFZ-Versicherung Jahr',NULL ,1, 0, NULL,  NULL),
    (6, 1,'2026-01-08', 78.45,'Tankfuellung Diesel',  45200,1, 1, 46.15, 1.699),
    (7, 1,'2026-02-05',249.90,'Bremsen + Inspektion', 46550,1, 0, NULL,  NULL),
    (8, 1,'2026-03-14', 83.75,'Tankfuellung Diesel',  48050,1, 1, 48.30, 1.733),
    -- Max / BMW 320d (vehicle 2, Diesel)
    (9, 2,'2025-09-10', 95.20,'Tankfuellung Diesel',  28000,1, 1, 52.10, 1.827),
    (10,2,'2025-10-28', 90.40,'Tankfuellung Diesel',  28900,1, 1, 49.50, 1.826),
    (11,2,'2025-11-15',182.00,'KFZ-Steuer',           NULL ,1, 0, NULL,  NULL),
    (12,2,'2026-02-10', 96.10,'Tankfuellung Diesel',  30150,1, 1, 52.40, 1.834),
    (13,2,'2026-03-12', 92.60,'Tankfuellung Diesel',  31020,1, 1, 50.05, 1.850),
    (14,2,'2026-04-02',320.00,'Storniert (Soft-Delete-Demo)',31500,0, 0, NULL, NULL),
    -- Max / Skoda Octavia (vehicle 5, Diesel)
    (15,5,'2025-07-20', 70.30,'Tankfuellung Diesel',  60000,1, 1, 41.10, 1.710),
    (16,5,'2025-08-25', 72.80,'Tankfuellung Diesel',  60800,1, 1, 42.00, 1.733),
    (17,5,'2025-10-05', 74.10,'Tankfuellung Diesel',  61650,1, 1, 42.70, 1.735),
    (18,5,'2026-01-18',410.00,'KFZ-Versicherung Jahr',NULL ,1, 0, NULL,  NULL),
    (19,5,'2026-03-22', 76.50,'Tankfuellung Diesel',  63100,1, 1, 43.50, 1.759),
    -- Max / Tesla Model 3 (vehicle 6, Elektro -> keine Tankbuchungen)
    (20,6,'2025-11-02', 45.00,'Ladekosten Schnelllader',15000,1, 0, NULL, NULL),
    (21,6,'2025-12-15',480.00,'KFZ-Versicherung Jahr',NULL ,1, 0, NULL,  NULL),
    (22,6,'2026-02-08',150.00,'Reifen einlagern',     16500,1, 0, NULL,  NULL),
    (23,6,'2026-04-10', 52.00,'Ladekosten',           17800,1, 0, NULL,  NULL),
    -- Max / Ford Focus (vehicle 7, Benzin)
    (24,7,'2025-08-01', 60.20,'Tankfuellung Super',   72000,1, 1, 35.40, 1.700),
    (25,7,'2025-09-18', 63.50,'Tankfuellung Super',   72800,1, 1, 37.10, 1.712),
    (26,7,'2025-11-22', 95.00,'Service klein',        73900,1, 0, NULL,  NULL),
    (27,7,'2026-01-30', 66.10,'Tankfuellung Super',   75100,1, 1, 38.00, 1.740),
    (28,7,'2026-03-08', 98.00,'KFZ-Steuer',           NULL ,1, 0, NULL,  NULL),
    -- Max / Mercedes C 220 d (vehicle 8, Diesel)
    (29,8,'2025-10-12', 99.00,'Tankfuellung Diesel',  20000,1, 1, 54.00, 1.833),
    (30,8,'2025-12-20',101.20,'Tankfuellung Diesel',  21000,1, 1, 55.10, 1.837),
    (31,8,'2026-02-25',610.00,'KFZ-Versicherung Jahr',NULL ,1, 0, NULL,  NULL),
    (32,8,'2026-04-15',103.40,'Tankfuellung Diesel',  22100,1, 1, 55.80, 1.853),
    -- Erika / Audi A3 (vehicle 3, Benzin)
    (33,3,'2025-07-08', 62.00,'Tankfuellung Super',   85000,1, 1, 36.50, 1.698),
    (34,3,'2025-08-19', 64.30,'Tankfuellung Super',   85800,1, 1, 37.40, 1.719),
    (35,3,'2025-09-30',210.00,'Inspektion',           86500,1, 0, NULL,  NULL),
    (36,3,'2025-11-10', 65.80,'Tankfuellung Super',   87400,1, 1, 38.00, 1.732),
    (37,3,'2025-12-05',460.00,'KFZ-Versicherung Jahr',NULL ,1, 0, NULL,  NULL),
    (38,3,'2026-01-15', 65.00,'Tankfuellung Super',   88200,1, 1, 38.24, 1.700),
    (39,3,'2026-02-20',310.50,'Bremsen vorne neu',    89100,1, 0, NULL,  NULL),
    (40,3,'2026-03-05', 67.40,'Tankfuellung Super',   89950,1, 1, 39.65, 1.700),
    (41,3,'2026-04-12',120.00,'KFZ-Steuer',           NULL ,1, 0, NULL,  NULL),
    (42,3,'2026-05-01', 68.90,'Tankfuellung Super',   90800,1, 1, 39.90, 1.727),
    -- Erika / VW Polo (vehicle 9, Benzin)
    (43,9,'2025-09-05', 48.00,'Tankfuellung Super',   32000,1, 1, 28.50, 1.684),
    (44,9,'2025-10-22', 50.20,'Tankfuellung Super',   32700,1, 1, 29.40, 1.707),
    (45,9,'2025-12-18',130.00,'Service klein',        33500,1, 0, NULL,  NULL),
    (46,9,'2026-02-14', 51.50,'Tankfuellung Super',   34300,1, 1, 30.00, 1.717),
    (47,9,'2026-03-28', 35.00,'Parkstrafe',           34900,1, 0, NULL,  NULL),
    (48,9,'2026-05-10', 52.80,'Tankfuellung Super',   35600,1, 1, 30.50, 1.731);

-- Expense-Category-Mapping
INSERT INTO expense_category_map (expense_id,category_id) VALUES
    -- Tanken (Kategorie 1)
    (1,1),(2,1),(4,1),(6,1),(8,1),(9,1),(10,1),(12,1),(13,1),(15,1),(16,1),(17,1),(19,1),
    (24,1),(25,1),(27,1),(29,1),(30,1),(32,1),(33,1),(34,1),(36,1),(38,1),(40,1),(42,1),
    (43,1),(44,1),(46,1),(48,1),
    -- Werkstatt (Kategorie 2)
    (3,2),(7,2),(14,2),(22,2),(26,2),(35,2),(39,2),(45,2),
    -- Versicherung (Kategorie 3)
    (5,3),(18,3),(21,3),(31,3),(37,3),
    -- Steuer (Kategorie 4)
    (11,4),(28,4),(41,4),
    -- Sonstiges (Kategorie 5); 22 zusaetzlich zu Werkstatt = m:n-Demo
    (20,5),(22,5),(23,5),(47,5);
 
-- Audit-Log
INSERT INTO log_entry
    (user_id,username,ip_address,action,time_stamp) VALUES
    (1,'admin',      '192.168.0.10','LOGIN_SUCCESS',           '2026-03-30 08:00:12'),
    (1,'admin',      '192.168.0.10','USER_DEACTIVATED id=4',   '2026-03-30 08:02:45'),
    (2,'maxmuster',  '192.168.0.21','LOGIN_SUCCESS',           '2026-03-30 09:15:03'),
    (2,'maxmuster',  '192.168.0.21','EXPENSE_CREATED id=8',    '2026-03-30 09:16:40'),
    (2,'maxmuster',  '192.168.0.21','EXPENSE_SOFTDELETED id=14','2026-03-30 09:18:10'),
    (3,'erikamuster','10.0.0.55',   'LOGIN_FAILED',            '2026-03-30 10:01:22'),
    (3,'erikamuster','10.0.0.55',   'LOGIN_SUCCESS',           '2026-03-30 10:01:35');