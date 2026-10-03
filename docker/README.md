# Docker Setup Guide - BoxSys Monorepo

This guide explains how Docker is organized and used within the BoxSys monorepo.

## Overview

The Docker setup is composed of:

- `docker-compose.services.yaml` - Infrastructure services (PostgreSQL, Redis, etc.)
- `docker-compose.components.yaml` - Application component templates
- `docker-compose.dev.yaml` - Development configuration with hot reload
- `docker-compose.build.yaml` - Production-like configuration
- `.env` - Environment variables

All containers communicate through the shared `boxsys-network`.

---

## Quick Start

### Infrastructure Only

```bash
npm run docker:services
```

Starts only infrastructure services such as the database and cache.

### Development Mode

```bash
npm run docker:dev
```

Starts the full development environment with hot reload enabled.

### Production Build

```bash
npm run docker:build
```

Builds and starts containers using a production-like setup.

### Stop All Services

```bash
npm run docker:down
```

Stops and removes all containers.

---

## Structure

```text
docker/
├── docker-compose.services.yaml
├── docker-compose.components.yaml
├── docker-compose.dev.yaml
├── docker-compose.build.yaml
├── docker-compose.override.yaml
├── .env.example
└── .env
```

---

## How It Works

The setup uses layered Docker Compose files:

- **Templates** define reusable services and components.
- **Overlays** add environment-specific settings.
- **NPM scripts** combine the required files automatically.

Example:

```bash
docker compose \
  -f docker/docker-compose.services.yaml \
  -f docker/docker-compose.dev.yaml \
  up -d
```

---

## Modes

### Development (`docker:dev`)

- Hot reload enabled
- Source code mounted as volumes
- Real-time file synchronization

### Build (`docker:build`)

- No source code mounts
- Application packaged into the image
- Production-like behavior

### Services (`docker:services`)

- Infrastructure services only
- Intended for local application development

---

## Environment Configuration

Create your environment file:

```bash
cp docker/.env.example docker/.env
```

Update `.env` to customize ports, credentials, and runtime settings.

Changes are applied on the next container startup.

---

## Advanced Usage

### Local Overrides

Create `docker/docker-compose.override.yaml` (gitignored):

```yaml
services:
  server-app-debug:
    extends:
      file: docker-compose.components.yaml
      service: server-app

    ports:
      - "8001:80"

    environment:
      APP_DEBUG: "true"
```

### Enable Optional Profiles

```bash
COMPOSE_PROFILES=ai docker compose \
  -f docker/docker-compose.services.yaml \
  -f docker/docker-compose.dev.yaml \
  up -d
```

### View Logs

```bash
# All containers
docker compose logs -f

# Specific container
docker compose logs -f server-app
```

### Rebuild Images

```bash
docker compose \
  -f docker/docker-compose.services.yaml \
  -f docker/docker-compose.build.yaml \
  up --build -d
```
