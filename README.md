 # Compta API

Laravel REST API for managing student accounting.

## Features

- Authentication with Laravel Sanctum
- Students management (CRUD)
- Invoices management (CRUD)
- Payments management (CRUD)
- Automatic invoice recalculation after payments
- Protection against overpayment
- API Resources for clean JSON responses
- Feature Tests for Students, Invoices, and Payments
- Factories and Seeders for demo data

## Tech Stack

- Laravel 11
- MySQL
- Laravel Sanctum
- PHPUnit

## Installation

1. Install dependencies

2. Generate application key

3. Run migrations and seed demo data

## Demo User

Use the following account to test the API:

email: demo@example.com  
password: 12345678

## Run the Server

Start the Laravel development server: 
php artisan serve
The API will be available at: 
http://127.0.0.1:8000

## API Endpoints

POST /api/login  
GET /api/me  

GET /api/students  
POST /api/students  
PATCH /api/students/{id}  
DELETE /api/students/{id}  

GET /api/invoices  
POST /api/invoices  
PATCH /api/invoices/{id}  
DELETE /api/invoices/{id}  

GET /api/payments  
POST /api/payments  
PATCH /api/payments/{id}  
DELETE /api/payments/{id}




