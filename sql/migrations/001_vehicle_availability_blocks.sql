USE atlas_car_rental;

CREATE TABLE IF NOT EXISTS vehicle_availability_blocks (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  vehicle_id  INT UNSIGNED NOT NULL,
  start_date  DATE NOT NULL,
  end_date    DATE NOT NULL,
  note        VARCHAR(255) NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_blocks_vehicle_dates (vehicle_id, start_date, end_date),
  CONSTRAINT fk_blocks_vehicle
    FOREIGN KEY (vehicle_id) REFERENCES vehicles (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT chk_blocks_date_range CHECK (end_date >= start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
