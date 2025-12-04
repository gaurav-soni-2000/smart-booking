# Smart Booking Scheduler

## Requirements

-   PHP 8.1+, Composer, Node 18+, npm, MySQL

## Setup

1. Clone repo
2. Copy `.env.example` -> `.env` and set DB credentials
3. `composer install`
4. `php artisan key:generate`
5. `php artisan migrate --seed`
6. `npm install`
7. `npm run dev` (for frontend hot)
8. `php artisan serve`
9. Visit `http://127.0.0.1:8000`

## API

-   GET `/api/services`
-   GET `/api/slots?date=YYYY-MM-DD&service_id=1`
-   POST `/api/book` with `{ service_id, date, start_time (HH:MM:SS), client_email, client_name? }`
-   GET `/api/admin/rules`
-   POST `/api/admin/rules` `{ weekday (0-6), start_time, end_time, slot_interval }`
-   DELETE `/api/admin/rules/{id}`

## Notes

-   Bookings prevented when overlapping or in the past.
-   Use DB transactions and `lockForUpdate` to reduce race conditions.
