-- ============================================================
-- ATLAS CAR RENTAL — Database Schema
-- MySQL / MariaDB (utf8mb4)
-- ============================================================

CREATE DATABASE IF NOT EXISTS atlas_car_rental
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE atlas_car_rental;

-- ------------------------------------------------------------
-- ADMINS
-- ------------------------------------------------------------
CREATE TABLE admins (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name           VARCHAR(120) NOT NULL,
  email          VARCHAR(190) NOT NULL,
  password_hash  VARCHAR(255) NOT NULL,
  role           ENUM('admin','superadmin') NOT NULL DEFAULT 'admin',
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admins_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- VEHICLES
-- ------------------------------------------------------------
CREATE TABLE vehicles (
  id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug                VARCHAR(190) NOT NULL,
  brand               VARCHAR(80)  NOT NULL,
  model               VARCHAR(80)  NOT NULL,
  year                SMALLINT UNSIGNED NOT NULL,
  type                ENUM('SUV','Sedan','Hatchback','Pickup','Van','Coupe','Convertible','Wagon','Minibus') NOT NULL DEFAULT 'SUV',
  price_per_day       DECIMAL(12,2) NOT NULL,
  transmission        ENUM('Automatic','Manual') NOT NULL DEFAULT 'Automatic',
  fuel_type           ENUM('Petrol','Diesel','Hybrid','Electric') NOT NULL DEFAULT 'Petrol',
  seats               TINYINT UNSIGNED NOT NULL DEFAULT 5,
  doors               TINYINT UNSIGNED NOT NULL DEFAULT 4,
  location            VARCHAR(120) NOT NULL,
  description         TEXT NULL,
  availability_status ENUM('available','rented','unavailable') NOT NULL DEFAULT 'available',
  created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_vehicles_slug (slug),
  KEY idx_vehicles_status (availability_status),
  KEY idx_vehicles_type (type),
  KEY idx_vehicles_location (location),
  KEY idx_vehicles_price (price_per_day)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- VEHICLE IMAGES
-- ------------------------------------------------------------
CREATE TABLE vehicle_images (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  vehicle_id    INT UNSIGNED NOT NULL,
  image_url     VARCHAR(255) NOT NULL,
  display_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_images_vehicle (vehicle_id, display_order),
  CONSTRAINT fk_images_vehicle
    FOREIGN KEY (vehicle_id) REFERENCES vehicles (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- INQUIRIES
-- ------------------------------------------------------------
CREATE TABLE inquiries (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  vehicle_id       INT UNSIGNED NULL,
  customer_name    VARCHAR(120) NULL,
  customer_phone   VARCHAR(40)  NULL,
  requested_dates  VARCHAR(120) NULL,
  status           ENUM('new','contacted','pending','completed','cancelled') NOT NULL DEFAULT 'new',
  created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_inquiries_status (status),
  KEY idx_inquiries_vehicle (vehicle_id),
  CONSTRAINT fk_inquiries_vehicle
    FOREIGN KEY (vehicle_id) REFERENCES vehicles (id)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- SETTINGS (key/value)
-- ------------------------------------------------------------
CREATE TABLE settings (
  `key`   VARCHAR(80) NOT NULL,
  `value` TEXT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;