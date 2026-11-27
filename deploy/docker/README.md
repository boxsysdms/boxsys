# Docker

Docker configuration for the monorepo. Each component has its own Dockerfile in
`apps/<component>/Dockerfile` and its services in `deploy/docker`.
Currently, only the `server` component is configured.

## Layout

| File | Purpose |
| --- | --- |
| `docker-compose.yaml` | Infrastructure, volumes and network |
| `docker-compose.infrastructure.yaml` | PostgreSQL and Redis templates |
| `docker-compose.application.yaml` | Component templates; currently, `server-template` |
| `docker-compose.dev.yaml` | Local services with source code mounted from the host |
| `docker-compose.build.yaml` | Services with source code included in their images |
| `server/` | Nginx, PHP-FPM and server startup configuration |

Services reuse templates through `extends`. Templates are not started on their
own. Their required volumes, networks and services must be declared in the stack.

## Configuration

From the monorepo root:

```sh
cp deploy/docker/.env.example deploy/docker/.env
```

Set the Laravel application key and passwords before starting. Keep the same
`SERVER_APP_KEY` across deployments.

| Variable | Purpose | Default value |
| --- | --- | --- |
| `SERVER_APP_ENV` | Laravel environment | `production`; `local` in dev |
| `SERVER_APP_KEY` | Laravel encryption key | Required |
| `SERVER_APP_DEBUG` | Detailed errors | `false`; `true` in dev |
| `SERVER_APP_URL` | Full application URL | `http://localhost:8000` |
| `SERVER_APP_BIND_ADDRESS` | HTTP interface on the host | `127.0.0.1` |
| `SERVER_APP_PORT` | HTTP port on the host | `8000` |
| `SERVER_LOG_LEVEL` | Laravel log level | `info` |
| `SERVER_DB_NAME` | Database name | `boxsys` |
| `SERVER_DB_BIND_ADDRESS` | PostgreSQL interface on the host | `127.0.0.1` |
| `SERVER_DB_PORT` | PostgreSQL port on the host | `5432` |
| `SERVER_DB_USER` | PostgreSQL user | `boxsys` |
| `SERVER_DB_PASSWORD` | PostgreSQL password | Required |
| `SERVER_CACHE_BIND_ADDRESS` | Redis interface on the host | `127.0.0.1` |
| `SERVER_CACHE_PORT` | Redis port on the host | `6379` |
| `SERVER_CACHE_PASSWORD` | Redis password | Required |
| `POSTGRES_IMAGE` | PostgreSQL image | `postgres:18-alpine` |
| `REDIS_IMAGE` | Redis image | `redis:8-alpine` |

The ports above are host ports. Between containers, the server uses
`server-db:5432` and `server-cache:6379`. To expose HTTP on another
interface, adjust `SERVER_APP_BIND_ADDRESS`.

## Usage

Run from the monorepo root with Docker Compose and pnpm available:

| Command | Purpose |
| --- | --- |
| `pnpm docker:up` | Start infrastructure only |
| `pnpm docker:dev` | Build and start the local environment |
| `pnpm docker:build` | Build and start the production environment |
| `pnpm docker:down` | Stop and remove containers while keeping their data |

The `http://localhost:8000/up` endpoint checks that the server has started.
Use one scenario per machine: dev locally, build in production.
Migrations run separately during deployment.

## Server image and volumes

The Dockerfile provides `development` and `production` targets. Production
includes only production Composer dependencies; dev also includes Composer and
development tools. Local source code is mounted only in dev.

Volume names are the same in both environments. PostgreSQL, Redis and
`server-app-storage` retain data when containers are recreated. In dev,
`vendor` and `bootstrap/cache` also use volumes to avoid host dependencies
and caches. After changing `composer.lock` in dev, run `composer install`
inside the container to update the dependency volume.

The local `vendor` directory is excluded from the build to prevent development
packages from being copied back into the image. Logs are available through
container logs.

## Adding components

1. Create `apps/<component>/Dockerfile`.
2. Add its template to `docker-compose.application.yaml`, specifying the
   Dockerfile path and that component's settings.
3. Add the service to the dev and build scenarios, using `extends` to reference
   its template.
4. Declare the required volumes, network and dependencies in the stack. Add
   the component's variables to `.env.example`.

The root `.dockerignore` contains only shared exclusions. Component-specific
rules belong in `apps/<component>/Dockerfile.dockerignore`, which takes
precedence over the root file. The server already uses this mechanism to limit
its build context without restricting future components.

Compose paths are relative to the first file in the stack,
`deploy/docker/docker-compose.yaml`. Keep templates in `deploy/docker`.
