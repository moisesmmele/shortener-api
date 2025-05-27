# URL Shortener and Tracker API

[![Ask DeepWiki](https://deepwiki.com/badge.svg)](https://deepwiki.com/moisesmmele/shortener-api)
Lightweight PHP service for creating and tracking short URLs. Implements (my interpretation of) Clean Architecture with strict layering and PSR compliance.

## Features

* Shorten URLs to 6-character alphanumeric codes
* Redirect short codes to original URLs
* Track clicks: logs timestamp, referrer, client IP
* Validate inputs: URL length ≤ 2048, codes exactly 6 alphanumeric characters
* Swapable persistence: SQLite (PDO) or MongoDB via repository implementations

## Architecture and Standards

### Layered Structure

1. **Domain Layer**

   * Core entities (e.g., `Link`) and business rules
   * Validation logic centralized in domain models
2. **Application Layer**

   * Use cases encapsulate operations (create link, log click)
   * Services orchestrate domain entities and repositories
3. **Infrastructure Layer**

   * Controllers handle HTTP requests/responses
   * Routing via League Route adapter
   * Persistence through PDO or MongoDB repositories
   * Logging via PSR-3 compliant logger

### Design Patterns

* **Repository Pattern**: Abstracts data access, supports multiple backends
* **Adapter Pattern**: Integrates third‑party routing library behind a uniform interface
* **Factory Pattern**: Centralizes use case instantiation
* **DTOs**: Immutable data transfer objects between layers
* **Strategy Pattern**: Defines routing behavior for different endpoints
* **Dependency Injection**: Constructor injection throughout, container built with PHP‑DI

### PSR Compliance

* **PSR-4**: Namespace autoloading
* **PSR-7**: HTTP message interfaces via Laminas Diactoros
* **PSR-11**: Container interface via PHP‑DI
* **PSR-3**: Logging interface for error and event reporting

## API Endpoints

### Create Short Link

```
POST /links
Content-Type: application/json
{ "url": "https://example.com/long-url" }
```

Response:

```json
{ "id": "...", "url": "https://example.com/long-url", "code": "a1B2c3" }
```

### Redirect and Track

```
GET /{code}
```

* Issues HTTP 302 to original URL
* Records click details
* 404 if code not found

## Validation and Error Handling

* 400 Bad Request for invalid inputs
* 404 Not Found for missing resources
* 500 Internal Server Error for unhandled exceptions
* Errors logged via PSR-3 logger

## Technology Stack

* PHP 8.0+
* league/route, laminas-diactoros, laminas-httphandlerrunner
* php-di/php-di, mongodb/mongodb, vlucas/phpdotenv
* symfony/var-dumper, phpstan/phpstan
