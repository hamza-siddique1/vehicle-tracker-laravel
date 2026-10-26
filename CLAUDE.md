# VehicleTracker

Laravel app for tracking salvage vehicles bought at auction (Copart / IAAI),
from purchase through transit, title and sale. Includes an NDTC order integration.

## Stack
- Laravel (TODO: version), PHP (TODO: version)
- Database: MySQL
- Frontend: Blade, Bootstrap, jQuery, Select2, daterangepicker (TODO: confirm)
- Layout: resources/views/layouts/app.blade.php

## Data model
- `Vehicle` (app/Models/Vehicle.php): main columns such as vin, source,
  location, purchase_lot, auction_lot, left_location, date_paid, description.
  `Vehicle::STATUSES` holds the allowed statuses.
- `VehicleMetas`: key/value rows (vehicle_id, meta_key, meta_value) for
  claim_number, status, primary_damage, keys, drivability_rating, odometer,
  odometer_brand, days_in_yard, secondary_damage, sale_title_state,
  sale_title_type, sale_price.
- Notes: TODO (model name, table, columns, relation to Vehicle and User).

## Key files
- Vehicle controller: TODO path (show/update, plus the old modal HTML methods
  `generateHtml`, `generateHtmlForMobile`, `getHtmlTwo_TD`, `getMetaHtmlTwo_TD`,
  `getVehicleNotes`)
- Vehicle detail page: resources/views/vehicles/show.blade.php (replaces the modal)
- NDTC integration: TODO path

## Conventions
- Dates are stored as Y-m-d. Show dates to the user as DD.MM.YYYY.
- Existing notes are editable only by their author; any user can add a new note.
- Do not edit `id`, `created_at` or `updated_at` from forms.
- Prefer Blade views over building HTML strings in controllers.
- Follow PSR-12.

## Commands
- php artisan serve
- php artisan migrate
- php artisan route:list --name=vehicles
