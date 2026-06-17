# Event Platform

Event Platform is a full-stack event management application built with Symfony, React, PostgreSQL, and RabbitMQ.

The project serves both as a portfolio application and a playground for learning production-oriented backend and full-stack engineering concepts such as asynchronous processing, transactional consistency, pessimistic locking, validation, testing, and modern frontend development.

---

## Features

### Events

- List all events
- View event details
- Create new events
- DTO-based request handling
- Request validation using Symfony Validator

### Event Registration

- Register attendees for events
- Duplicate registration prevention
- Event capacity validation
- User-friendly validation and error handling

### Asynchronous Processing

Registration confirmation emails are processed asynchronously using Symfony Messenger and RabbitMQ.

Implemented features:

- RabbitMQ integration
- Symfony Messenger
- Background workers
- Retry strategy
- Failed message transport
- Message recovery commands

### Concurrency Protection

The registration process is protected against race conditions.

Implemented using:

- Database transactions
- Pessimistic locking (`PESSIMISTIC_WRITE`)
- Transactional registration flow

This prevents overbooking and duplicate registrations when multiple users register simultaneously.

### Testing

Functional API tests cover:

- Event listing
- Event details
- Missing events
- Event creation
- Validation errors
- Event registration
- Capacity limits
- Duplicate registrations

---

## Tech Stack

### Backend

- PHP 8.4
- Symfony 7
- Doctrine ORM
- PostgreSQL
- Symfony Messenger
- Symfony Mailer
- Twig
- PHPUnit

### Frontend

- React
- TypeScript
- Vite
- React Router
- Axios

### Infrastructure

- Docker
- RabbitMQ

---

## Architecture

### Backend

The backend follows a feature-based architecture.

```text
Application/
└── Event/
    ├── CreateEvent/
    ├── GetEventDetails/
    ├── ListEvents/
    └── RegisterForEvent/
```

Controllers remain intentionally thin and are responsible for:

```text
Request
↓
DTO
↓
Handler
↓
Response
```

Business logic lives inside application handlers.

---

## Registration Flow

```text
User submits registration
            ↓
RegisterForEventHandler
            ↓
Transaction starts
            ↓
Event row lock acquired
            ↓
Capacity validation
            ↓
Duplicate email validation
            ↓
Registration saved
            ↓
Transaction committed
            ↓
RegistrationCreated message dispatched
            ↓
RabbitMQ
            ↓
Messenger Worker
            ↓
RegistrationCreatedHandler
            ↓
Confirmation email sent
```

---

## Screenshots

### Event Create Form

![Event Create Form](image-1.png)

### Event List

![Event List](image.png)

### Event Details

![Event Details](image-2.png)

### Registration Form

![Registration Form](image-3.png)

---

## Local Development

### Requirements

- Docker Desktop
- Node.js (LTS)
- npm

### Start Infrastructure

```bash
docker compose up -d --build
```

### Backend

```bash
docker compose exec php bash

cd /app/backend

composer install

php bin/console doctrine:migrations:migrate

php -S 0.0.0.0:8000 -t public
```

Backend API:

```text
http://localhost:8000
```

### Frontend

```bash
cd frontend

npm install

npm run dev
```

Frontend:

```text
http://localhost:5173
```

### RabbitMQ UI

```text
http://localhost:15672
```

Credentials:

```text
guest
guest
```

## Running Background Workers

Start the Messenger worker:

```bash
docker compose exec php php bin/console messenger:consume async -vv
```

The worker is responsible for processing asynchronous tasks such as registration confirmation emails.

---

## Running Tests

Backend tests should be run from inside the PHP container so the required PHP extensions and Docker service hostnames are available:

```bash
docker compose exec php bash

cd /app/backend

php bin/phpunit
```

Frontend checks can be run from the frontend directory:

```bash
cd frontend

npm run lint

npm run build
```

---

## Environment Setup

Copy the example environment files:

```bash
cp backend/.env.example backend/.env.local
cp frontend/.env.example frontend/.env
```

The default values are configured for the provided Docker environment and should work without additional changes.

## Environment Variables

### Backend

```env
APP_ENV=dev

DATABASE_URL="postgresql://event_user:event_password@postgres:5432/event_platform?serverVersion=16&charset=utf8"

MAILER_DSN=null://null

MESSENGER_TRANSPORT_DSN=amqp://guest:guest@rabbitmq:5672/%2f/messages

MESSENGER_FAILED_TRANSPORT_DSN=doctrine://default?queue_name=failed
```

### Frontend

```env
VITE_API_BASE_URL=http://localhost:8000
```

---

## Future Improvements

- Authentication and authorization
- Organizer accounts
- Event ownership
- QR code check-in
- Redis caching
- Dashboard / administration panel
- Azure deployment
- CI/CD pipelines
- Monitoring and logging
