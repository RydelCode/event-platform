# Event Platform

Event Platform is a full-stack event management and booking platform built with Symfony, React, PostgreSQL, Docker, and Azure.

The project serves both as a portfolio application and a backend/frontend engineering learning platform focused on real-world architecture, scalability, and infrastructure concepts.

## Current Features

- Dockerized local development environment
- Symfony REST API backend
- React + TypeScript frontend
- PostgreSQL database integration
- Event entity with Doctrine migrations
- Event listing API endpoint
- Frontend integration with backend API

## Planned Features

### Backend
- Event creation and editing
- DTO-based request handling
- Validation and error handling
- Registration system
- Capacity management
- Queue processing with Symfony Messenger
- Async notifications and reminders
- Query optimization and N+1 mitigation
- Background workers
- Caching strategies
- File uploads and ticket generation

### Frontend
- Event listing and details pages
- Event creation forms
- Routing and layouts
- Form validation
- Async API handling
- Loading and error states
- Component architecture improvements

### Infrastructure
- Azure deployment
- CI/CD pipelines
- Staging and production environments
- Blob storage integration
- Monitoring and logging
- Secret management

## Tech Stack

### Backend
- PHP 8.3
- Symfony 7
- Doctrine ORM
- PostgreSQL

### Frontend
- React
- TypeScript
- Vite
- Axios
- React Router

### Infrastructure
- Docker
- Azure (planned)

## Architecture

```text
React Frontend
       ↓
Symfony REST API
       ↓
PostgreSQL
```

## Local Development

### Requirements
- Docker Desktop
- WSL2
- Node.js (LTS)
- npm


### Start Docker containers
```
docker compose up -d --build
```

### Start Symfony backend

```
docker compose exec php bash
cd /app/backend
php -S 0.0.0.0:8000 -t public
```

Backend available at:

```
http://localhost:8000
```

### Start React frontend

```
cd frontend
npm install
npm run dev
```

Frontend available at:

```
http://localhost:5173
```

### Learning Goals

This project is intentionally designed to explore:

- Full-stack application architecture
- Advanced Symfony backend concepts
- React application structure
- Queueing and background workers
- Database optimization and N+1 mitigation
- Concurrency and transactions
- CI/CD and cloud deployment
- Production-oriented development practices