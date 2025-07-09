# Advanced Real-Time Sales Analytics System

This project is a lightweight, real-time backend system for managing and analyzing sales data.  
It includes a RESTful API, WebSocket support, AI-generated recommendations, and external weather API integration.

## Features

- Add & retrieve products and orders.
- Real-time analytics via WebSocket.
- AI-based product recommendations (OpenAI).
- Weather-based dynamic suggestions (OpenWeather API).
- Manually implemented (no frameworks or ORMs).

## 🛠️ Installation

```bash
git clone https://github.com/abdelrhman-saeed/analytics-sys.git
cd analytics-sys
composer install
```

Install sqlite localy:
```bash
sudo apt install sqlite3 php-sqlite3 -y
```

run migrations:

```bash
./vendor/bin/migrate
```

Start the PHP server:

```bash
php -S 127.0.0.1:8000 index.php
```

Start the PHP Socket server:

```bash
php SocketServer.php
```

> PHP 8.1+ is required with SQLite enabled.

## 🔗 API Endpoints

### POST /products

Add a new product.

**Request JSON:**

```json
{
  "name" : "product_name",
  "price": 500
}
```

### PUT /products/:id

update a product.

**Request JSON:**

```json
{
  "name" : "product_name",
  "price": 500
}
```

### GET /products

get the products.

**Response JSON:**

```json
"products": [
    {
        "id": 1,
        "name": "product_name",
        "price": 500
    }
]
```

### GET /products/:id

get a single product.

**Response JSON:**

```json
{
    "id": 1,
    "name": "product_name",
    "price": 500
}
```

### POST /orders

Add a new order.

**Request JSON:**

```json
{
  "products": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 2, "quantity": 1 }
  ]
}
```

### GET /analytics

Returns real-time sales data:

```json
{
    "total_revenue": 435,
    "top_products": [
        {
            "id": 1,
            "name": "product-001",
            "total_quantity_sold": 90
        },
        {
            "id": 2,
            "name": "product-002",
            "total_quantity_sold": 25
        },
        {
            "id": 3,
            "name": "product-003",
            "total_quantity_sold": 5
        }
    ],
    "revenue_in_last_minute": 32,
    "orders_count_in_last_minute": 2
}
```

### GET /recommendations

Returns AI-based product suggestions and weather-based tips.

```json
{
  "best_products": [
    {
        "product_id": 1
    }
  ],
  "weather_temp": 34.2,
  "weather_tip": "It is hot today. Promote cold drinks..."
}
```

> Requires valid OpenAI & OpenWeather API keys.

### WebSocket (ws://127.0.0.1:8081)

Clients can subscribe to:

- `new_order`
- `updated_analytics`

Messages are broadcast in real-time when new orders are created.

##  Configuration

Rename `.env.example` to `.env` and add:

```
OPENAI_K="your_openai_key"
OPENAI_URL="https://api.openai.com/v1/chat/completions"

OPEN_WEATHER_K="your_openweather_key"
OPEN_WEATHER_URL="https://api.openweathermap.org/data/2.5/weather?q=$city&units=metric&appid="

WEATHER_CITY=Cairo
```
## AI-Assisted Parts

- Prompt generation and message structure for OpenAI.
- Initial boilerplate suggestions.
- README formatting and structure.

**All core logic, database queries, WebSocket implementation, and external API integrations were written manually.**

## Running Tests

```bash
vendor/bin/phpunit ./tests
```
## Postman Collection

You can test the API using the provided Postman collection:

[🔗 Download Collection](./analytics.postman_collection.json)

## Contact

**Abdelrhman Saeed**  
[abdelrhmansaeed001@gmail.com](mailto:abdelrhmansaeed001@gmail.com)