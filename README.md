# Subify - SaaS Subscription Management API

A robust, high-performance SaaS subscription management API built for scalability and flexibility.

## 🛠 Tech Stack

- **Framework**: Laravel 12
- **Database**: PostgreSQL
- **Cache & Queues**: Redis
- **Architecture**: Modular Monolith with DDD principles

## ✨ Key Features

- **Pipeline Pattern**: robust and extensible subscription processing workflows.
- **Strategy Pattern**: seamless interchangeability of payment providers.
- **JSONB Optimization**: high-performance storage for dynamic subscription metadata.

## 🚀 Installation

```bash
# Navigate to the project directory
cd subify

# Start the application
docker compose up -d
```

## 🔌 API Example

### Create a Subscription

**Endpoint**
`POST /api/subscriptions`

**Request**
```json
{
    "user_id": 1,
    "plan": "premium_yearly",
    "payment_gateway": "stripe",
    "options": {
        "tax_id": "US123456789",
        "coupon": "LAUNCH2026"
    }
}
```

**Response**
```json
{
    "data": {
        "id": "sub_01HM8X...",
        "status": "active",
        "starts_at": "2026-01-21T10:00:00Z",
        "renews_at": "2027-01-21T10:00:00Z"
    }
}
```
