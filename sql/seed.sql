-- ============================================================
-- ATLAS CAR RENTAL — Seed Data
-- Run AFTER schema.sql
-- ============================================================

USE atlas_car_rental;

-- ------------------------------------------------------------
-- SETTINGS
-- ------------------------------------------------------------
INSERT INTO settings (`key`, `value`) VALUES
  ('business_name',   'Atlas Car Rental'),
  ('whatsapp_number', '250788000000'),
  ('phone',           '+250 788 000 000'),
  ('email',           'hello@atlascarrental.rw'),
  ('location',        'Kigali, Rwanda')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- ------------------------------------------------------------
-- VEHICLES  (6 sample cars from the brief)
-- RWF pricing is realistic for the Kigali market.
-- ------------------------------------------------------------
INSERT INTO vehicles
  (slug, brand, model, year, type, price_per_day, transmission, fuel_type,
   seats, doors, location, description, availability_status)
VALUES
  (
    'toyota-rav4-2022', 'Toyota', 'RAV4', 2022, 'SUV', 85000.00, 'Automatic', 'Petrol',
    5, 5, 'Kigali',
    'A dependable compact SUV that handles city streets and upcountry roads with ease. Comfortable for five, generous boot space, and low fuel consumption for its class. Ideal for weekend trips and business travel alike.',
    'available'
  ),
  (
    'toyota-prado-2021', 'Toyota', 'Prado', 2021, 'SUV', 150000.00, 'Automatic', 'Diesel',
    7, 5, 'Kigali',
    'A full-size 4x4 built for serious travel. Seven seats, strong diesel torque, and true off-road capability. The right choice for safari routes, upcountry site visits, and larger groups.',
    'available'
  ),
  (
    'mercedes-benz-c-class-2023', 'Mercedes-Benz', 'C-Class', 2023, 'Sedan', 180000.00, 'Automatic', 'Petrol',
    5, 4, 'Kigali',
    'A refined executive sedan with a quiet cabin, modern driver assistance, and a presence that suits formal occasions. Popular for airport pickups, weddings, and corporate use.',
    'available'
  ),
  (
    'bmw-x5-2022', 'BMW', 'X5', 2022, 'SUV', 220000.00, 'Automatic', 'Petrol',
    5, 5, 'Kigali',
    'A luxury SUV that balances power with comfort. Leather interior, advanced infotainment, and confident highway manners. Suited to executives and special occasions.',
    'rented'
  ),
  (
    'toyota-corolla-2022', 'Toyota', 'Corolla', 2022, 'Sedan', 60000.00, 'Automatic', 'Petrol',
    5, 4, 'Kigali',
    'The reliable everyday sedan. Economical, easy to drive, and comfortable for daily commuting or short trips around the city. Our most requested vehicle for good reason.',
    'available'
  ),
  (
    'hyundai-tucson-2023', 'Hyundai', 'Tucson', 2023, 'SUV', 95000.00, 'Automatic', 'Petrol',
    5, 5, 'Kigali',
    'A modern mid-size SUV with sharp styling, a spacious interior, and a smooth ride. A strong all-rounder for families and business travellers who want something a little different.',
    'unavailable'
  )
ON DUPLICATE KEY UPDATE
  brand = VALUES(brand),
  model = VALUES(model),
  year  = VALUES(year);

-- ------------------------------------------------------------
-- VEHICLE IMAGES
-- Placeholder paths — drop real images into
--   /public/assets/uploads/sample/   with these names,
-- or replace the paths via the admin dashboard.
-- Front-end falls back to a branded placeholder if a file is missing.
-- ------------------------------------------------------------
INSERT INTO vehicle_images (vehicle_id, image_url, display_order)
SELECT id, CONCAT('/assets/uploads/sample/', slug, '-1.jpg'), 1 FROM vehicles
UNION ALL
SELECT id, CONCAT('/assets/uploads/sample/', slug, '-2.jpg'), 2 FROM vehicles
UNION ALL
SELECT id, CONCAT('/assets/uploads/sample/', slug, '-3.jpg'), 3 FROM vehicles;
