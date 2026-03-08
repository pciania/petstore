# Petstore — Laravel CRUD Application
- Layered architecture (Controller → Service → API Client → External API)
- Dependency Injection via Laravel's IoC container
- Custom Data Transfer Objects (DTOs)
- Unified error handling with a custom exception class
- Form Request validation
- Blade views with Bootstrap 5
- Full test coverage using **Pest**
---
## Architecture
```
HTTP Request
    └── PetController          (app/Http/Controllers)
            └── PetService     (app/Services)
                    └── PetstoreClient   (app/Integrations/Petstore)
                                └── Swagger Petstore API (external)
```
| Layer | Responsibility |
|---|---|
| `PetController` | Handle HTTP input/output, catch exceptions, flash messages |
| `PetService` | Orchestrate operations, build DTOs, call the client |
| `PetstoreClient` | Communicate with the external API, wrap Guzzle, throw `PetstoreException` |
| `PetData` | Typed DTO: map API response → app object, app object → API payload |
| `PetstoreException` | Unified exception for all API errors (HTTP errors, network failures, invalid responses) |

The `PetstoreClient` exposes the following API operations:

| Method | API endpoint | Description |
|---|---|---|
| `createPet` | `POST /pet` | Create a new pet |
| `getPet` | `GET /pet/{id}` | Fetch a pet by ID |
| `updatePet` | `PUT /pet` | Update an existing pet |
| `deletePet` | `DELETE /pet/{id}` | Delete a pet |
| `findByStatus` | `GET /pet/findByStatus` | List pets by status |
| `uploadImage` | `POST /pet/{id}/uploadFile` | Upload a photo for a pet |
---
## Requirements
- PHP ^8.1
- Composer
- A working internet connection (for calling the external Petstore API)
---
## Installation
```bash
git clone <repository-url> petstore
cd petstore
composer install
cp .env.example .env
php artisan key:generate
```
All data comes from the external API -no database needed.
---
## Environment Configuration
Open `.env` and review the Petstore settings:
```dotenv
PETSTORE_BASE_URL=https://petstore.swagger.io/v2
PETSTORE_TIMEOUT=10
PETSTORE_RETRIES=2
```
| Variable | Default | Description |
|---|---|---|
| `PETSTORE_BASE_URL` | `https://petstore.swagger.io/v2` | Base URL of the Petstore API |
| `PETSTORE_TIMEOUT` | `10` | HTTP request timeout in seconds |
| `PETSTORE_RETRIES` | `2` | Number of automatic retries on network/server errors |
---
## Running the Application
```bash
php artisan serve
```
Then open [http://localhost:8000](http://localhost:8000) — it redirects to `/pets`.
### Available pages
| Route | Description |
|---|---|
| `GET /pets` | List pets by status (filter via dropdown) |
| `GET /pets/create` | Create a new pet |
| `GET /pets/{id}` | View a single pet |
| `GET /pets/{id}/edit` | Edit a pet |
| `GET /pets/{id}/upload` | Upload an image for a pet |
---
## Running Tests
```bash
php artisan test
# or directly:
php vendor/bin/pest
```
### Test structure
| File | Type | Description |
|---|---|---|
| `tests/Unit/PetstoreClientTest.php` | Unit | Tests the API client with a Guzzle `MockHandler` — no real HTTP calls |
| `tests/Feature/PetControllerTest.php` | Feature | Tests all controller routes, validation, error handling |
Run only unit tests:
```bash
php vendor/bin/pest tests/Unit
```
Run only feature tests:
```bash
php vendor/bin/pest tests/Feature
```
---
## Project Structure
```
app/
├── Data/
│   └── PetData.php                  # DTO: API response ↔ application object
├── Http/
│   ├── Controllers/
│   │   └── PetController.php        # Thin resource controller
│   └── Requests/
│       ├── StorePetRequest.php      # Validation for create
│       ├── UpdatePetRequest.php     # Validation for update
│       └── UploadPetImageRequest.php # Validation for image upload (max 10 MB, jpg/png/gif)
├── Integrations/
│   └── Petstore/
│       ├── PetstoreClient.php       # Guzzle-based API client
│       └── PetstoreException.php    # Unified exception
├── Providers/
│   └── AppServiceProvider.php       # IoC bindings for PetstoreClient
└── Services/
    └── PetService.php               # Business logic & orchestration
resources/views/pets/
├── _form.blade.php                  # Shared form partial
├── create.blade.php
├── edit.blade.php
├── index.blade.php
├── show.blade.php
└── upload.blade.php                 # Image upload form
tests/
├── Feature/PetControllerTest.php
└── Unit/PetstoreClientTest.php
```
---
## External API Dependency
This application integrates with the **Swagger Petstore API** (`https://petstore.swagger.io/v2`), a publicly available demo API.
> **Important:** The Petstore API is a shared, public demo service. It does not persist data reliably — records created by one user may be overwritten or deleted by others at any time. IDs are not guaranteed to remain valid between requests.
>
> This is expected behavior for a demo API and does not indicate a bug in this application. All error responses from the API are caught and displayed as user-friendly flash messages.
The application handles the following error cases gracefully:
- `404` — Pet not found (redirect with message)
- `4xx` — Invalid input (redirect back with message)
- `5xx` — Server error (with automatic retry)
- Network timeouts and connection failures (redirect with message)
---
## Code Style
The project follows [Laravel coding conventions](https://laravel.com/docs/contributions#coding-style) and PSR-12. You can run Laravel Pint to check formatting:
```bash
./vendor/bin/pint --test
```
