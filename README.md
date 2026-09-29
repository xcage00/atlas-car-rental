# Atlas Automotive Services

A PHP and MySQL car-rental website for Atlas Automotive Services in Kigali, Rwanda.

## Local setup with XAMPP

1. Copy this folder to `C:\xampp\htdocs\atlas-car-rental`.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. In phpMyAdmin, import `sql/schema.sql`, then import `sql/seed.sql`.
4. Open `http://localhost/atlas-car-rental/`.

The sample fleet photos are included under `assets/uploads/sample/`. Database settings can be overridden with `ATLAS_DB_HOST`, `ATLAS_DB_NAME`, `ATLAS_DB_USER`, and `ATLAS_DB_PASS` environment variables. Keep production credentials outside the repository and set `ATLAS_DEBUG=0` in production.

## Create the first administrator

The setup script only runs from PHP CLI. In PowerShell, set these variables for the current terminal session, then run `php setup-admin.php` from the project folder:

```powershell
$env:ATLAS_ADMIN_NAME = "Your Name"
$env:ATLAS_ADMIN_EMAIL = "you@example.com"
$env:ATLAS_ADMIN_PASSWORD = "use-a-unique-password-at-least-12-characters"
php setup-admin.php
Remove-Item Env:ATLAS_ADMIN_NAME, Env:ATLAS_ADMIN_EMAIL, Env:ATLAS_ADMIN_PASSWORD
```

The command refuses to create another administrator if an admin account already exists. Do not put real credentials in source files, SQL dumps, or GitHub.

## Current scope

- Browse and filter the vehicle fleet.
- View vehicle details and photos.
- Contact Atlas through pre-filled WhatsApp messages.
- Store vehicle, image, inquiry, and site settings data in MySQL.

Pickup and return dates are passed to WhatsApp for confirmation; the current database does not yet maintain a reservation calendar.
