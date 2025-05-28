# URL Shortener and Tracker API (WIP)

![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white)
![MongoDB](https://img.shields.io/badge/MongoDB-%234ea94b.svg?style=for-the-badge&logo=mongodb&logoColor=white)

Lightweight PHP service for creating and tracking short URLs. Implements (my interpretation of) Clean Architecture with strict layering and PSR compliance.

#### OBS: This is not a real project. For studying purposes only. Definitely not production code.

[![Ask DeepWiki](https://deepwiki.com/badge.svg)](https://deepwiki.com/moisesmmele/shortener-api)

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
   * Persistence through PDO or MongoDB repositories (defaults to MongoDB)
   * Logging via PSR-3 compliant logger, using MongoDB documents for some observability

### Design Patterns and Best Practices

* **Repository Pattern**: Abstracts data access, supports multiple backends
* **Adapter Pattern**: Integrates third‑party routing library behind a uniform interface
* **Factory Pattern**: Centralizes use case instantiation
* **Strategy Pattern**: Defines routing behavior for different endpoints
  
* **Dependency Injection**: Constructor injection throughout, container built with PHP‑DI
* **Decoupled Code**: Use of Contracts throughout the Application Layer 
* **DTOs**: Immutable data transfer objects between layers

### PSR Compliance

* **PSR-4**: Namespace autoloading
* **PSR-7**: HTTP message interfaces
* **PSR-11**: Container interfaces
* **PSR-3**: Logging interfaces

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
* Errors logged via PSR-3 logger (Using MongoDB documents) 

## Technology Stack

* PHP 8.0+
* league/route, laminas-diactoros, laminas-httphandlerrunner
* php-di/php-di, mongodb/mongodb, vlucas/phpdotenv
* symfony/var-dumper, phpstan/phpstan
