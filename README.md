# Landing Page API

REST API for a developer landing page with contact form, health check, metrics, and AI-powered sentiment analysis.

---

## Technology Stack

| Layer | Technology |
|---|---|
| **Framework** | Laravel 13 (PHP 8.1+) |
| **AI** | OpenAI GPT-4o-mini via Guzzle HTTP client |
| **Cache** | File driver (no Redis required) |
| **Mail** | Laravel Mail (log driver by default, SMTP configurable) |
| **Logging** | Monolog (separate channels: `api`, `ai`, `single`) |

---

## Architecture

```
┌──────────────┐      ┌──────────────────┐      ┌─────────────────┐
│   Routes     │─────▶│   Controllers    │─────▶│   Services      │
│   (api.php)  │      │ ContactController│      │ ContactService  │
│              │      │ HealthController │      │ AIService       │
│              │      │ MetricsController│      │ MetricsService  │
└──────────────┘      └──────────────────┘      └─────────────────┘
                              │                         │
                      ┌───────┴───────┐        ┌────────┴────────┐
                      │   Middleware  │        │   File Storage  │
                      │  RateLimit    │        │   metrics.json  │
                      │  ApiLogger    │        │   storage/app/  │
                      │  Cors         │        └─────────────────┘
                      └───────────────┘
```

### Request Flow (POST /api/contact)

1. **CorsMiddleware** — adds CORS headers
2. **RateLimit** — checks file cache for attempts from this IP (max 3/min)
3. **ApiLogger** — logs request start, passes to controller
4. **ContactFormRequest** — validates input, returns 422 on failure
5. **ContactController** → **ContactService** → orchestrates:
   - `AIService.analyzeSentiment()` — calls OpenAI API with fallback to "neutral"
   - Sends email to site owner + confirmation to user
   - `MetricsService.increment()` — updates `metrics.json`
6. Returns JSON with message + sentiment

---

## API Endpoints

| Method | Path | Description | Rate Limited |
|---|---|---|---|
| `POST` | `/api/contact` | Submit contact form | ✅ (3/min per IP) |
| `GET` | `/api/health` | Health check | ❌ |
| `GET` | `/api/metrics` | Contact statistics | ❌ |

---

## Setup Instructions

### Prerequisites

- PHP 8.1 or higher
- Composer
- (Optional) OpenAI API key for sentiment analysis

### Installation

```bash
# 1. Clone / navigate to the project directory
cd testTask

# 2. Install dependencies
composer install

# 3. Create .env from example
copy .env.example .env   # Windows
# cp .env.example .env   # Linux/macOS

# 4. Generate application key
php artisan key:generate

# 5. Create required storage directories
mkdir -p storage/framework/cache/data
mkdir -p storage/logs
mkdir -p storage/app
mkdir -p storage/api-docs

# 6. (Optional) Set your OpenAI API key in .env
#    OPENAI_API_KEY=sk-your-key-here

# 7. Start the development server
php artisan serve
```

The API will be available at `http://localhost:8000`.

---

## Configuration (.env)

```env
# Mail — use 'log' for testing, 'smtp' for real emails
MAIL_MAILER=log
MAIL_OWNER=admin@example.com

# OpenAI
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-4o-mini

# Rate limiting
RATE_LIMIT_ATTEMPTS=3
RATE_LIMIT_DECAY=60       # seconds
```

---

## API Examples (cURL)

### Health Check

```bash
curl -X GET http://localhost:8000/api/health
```

**Response:**
```json
{
  "status": "ok",
  "timestamp": "2025-01-15T10:30:00+00:00"
}
```

### Submit Contact Form

```bash
curl -X POST http://localhost:8000/api/contact \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "phone": "+1234567890",
    "email": "john@example.com",
    "comment": "Great work on the landing page! Really impressed with the quality."
  }'
```

**Response (200):**
```json
{
  "message": "Your message has been sent successfully.",
  "sentiment": "positive"
}
```

**Response (422 — Validation Error):**
```json
{
  "message": "The name field is required.",
  "errors": {
    "name": ["The name field is required."]
  }
}
```

**Response (429 — Rate Limited):**
```json
{
  "message": "Too many requests. Please try again later."
}
```

### Get Metrics

```bash
curl -X GET http://localhost:8000/api/metrics
```

**Response:**
```json
{
  "today": 5,
  "week": 12,
  "total": 42
}
```

---

## AI Integration

### Sentiment Analysis

Every contact form submission is analyzed by OpenAI's GPT-4o-mini model to detect the sentiment of the comment field.

**How it works:**

1. The `AIService` sends the comment text to the OpenAI Chat Completions API.
2. The system prompt instructs the model to respond with only one word: `positive`, `negative`, or `neutral`.
3. The response is normalized and returned in the API response.

**Graceful Fallback:**

If the OpenAI API is unavailable (timeout, error, missing key), the service:

- Returns `"neutral"` as the default sentiment
- Logs the error to `storage/logs/ai.log`
- **Does NOT** throw an exception — the contact form still succeeds

This ensures the contact form works even without an API key or during outages.

**AI Log Example** (`storage/logs/ai.log`):
```
[2025-01-15 10:30:00] local.WARNING: OpenAI API key not configured. Returning default sentiment. {"default":"neutral"}
[2025-01-15 10:30:01] local.ERROR: Request error during sentiment analysis {"error":"Connection timed out","code":28}
```

---

## Logging

All API requests are logged to `storage/logs/api.log`:

```
[2025-01-15 10:30:00] POST http://localhost:8000/api/contact 127.0.0.1 200 45.2ms
[2025-01-15 10:30:01] GET http://localhost:8000/api/health 127.0.0.1 200 2.1ms
```

Format: `[timestamp] METHOD URL IP STATUS response_time_ms`

---

## Rate Limiting

- **Endpoint:** `POST /api/contact`
- **Limit:** 3 requests per minute per IP (configurable via `.env`)
- **Storage:** File cache (`storage/framework/cache/data`)
- **Response on exceed:** `429 Too Many Requests`

---

---

## Postman Collection

Import `collection.json` into Postman to test all endpoints. The collection includes:

- Health check
- Contact form submission (valid and invalid)
- Metrics retrieval

---

## Project Structure

```
testTask/
├── app/
│   ├── Console/Kernel.php
│   ├── Exceptions/Handler.php          # Global JSON error handler
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ContactController.php   # POST /api/contact
│   │   │   ├── HealthController.php    # GET /api/health
│   │   │   └── MetricsController.php   # GET /api/metrics
│   │   ├── Kernel.php                  # HTTP kernel (middleware)
│   │   ├── Middleware/
│   │   │   ├── ApiLogger.php           # Request logging
│   │   │   ├── CorsMiddleware.php      # CORS headers
│   │   │   └── RateLimit.php           # Spam protection
│   │   └── Requests/
│   │       └── ContactFormRequest.php  # Validation rules
│   ├── Mail/
│   │   └── ContactNotification.php     # Email mailable
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── RouteServiceProvider.php
│   │   └── EventServiceProvider.php
│   └── Services/
│       ├── AIService.php               # OpenAI sentiment analysis
│       ├── ContactService.php          # Contact form orchestration
│       └── MetricsService.php          # File-based metrics
├── bootstrap/app.php
├── config/
│   ├── app.php, cache.php, cors.php, logging.php, mail.php
├── resources/views/emails/contact.blade.php
├── routes/
│   ├── api.php, web.php, console.php
├── storage/
│   ├── app/metrics.json                # Auto-created statistics
│   └── logs/
│       ├── api.log                     # API request log
│       └── ai.log                      # AI service log
├── .env.example
├── collection.json                     # Postman collection
└── README.md
```

---

## What Was Generated by AI

This entire project was generated with the assistance of an AI coding assistant (Qwen Code / Claude). All code, configuration, documentation, and architecture decisions were made by AI based on the requirements specification.

Specifically AI-generated:
- All PHP source files (controllers, services, middleware, requests, mail)
- All configuration files
- Email templates
- README.md
- Postman collection
- `.env.example`

---

## License

MIT
