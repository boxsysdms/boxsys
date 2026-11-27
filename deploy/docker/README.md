# Docker

Docker configuration for the monorepo. Each component has its own Dockerfile in
`apps/<component>/Dockerfile`. The stack currently includes the portal, API,
PostgreSQL, Redis and an Nginx gateway.

## Layout

| File | Purpose |
| --- | --- |
| `docker-compose.yaml` | Database, cache, volumes and network |
| `docker-compose.infrastructure.yaml` | PostgreSQL, Redis and gateway templates |
| `docker-compose.application.yaml` | Server and portal templates |
| `docker-compose.dev.yaml` | Development targets with local application source |
| `docker-compose.build.yaml` | Production targets with source included in the images |
| `gateway/nginx.conf` | HTTP routing to the portal and API |
| `server/` | API Nginx, PHP-FPM and startup configuration |

Services reuse templates through `extends`. Templates do not start on their
own. Their required services, networks and volumes must be declared in the stack.

## Configuration

From the monorepo root:

```sh
cp deploy/docker/.env.example deploy/docker/.env
```

Set the Laravel application key and passwords before starting. Keep the same
`SERVER_APP_KEY` across deployments.

| Variable | Purpose | Default value |
| --- | --- | --- |
| `GATEWAY_HTTP_PORT` | The only published host port | `80` |
| `SERVER_APP_ENV` | Laravel environment | `production`; `local` in dev |
| `SERVER_APP_KEY` | Laravel encryption key | Required |
| `SERVER_APP_DEBUG` | Detailed errors | `false`; `true` in dev |
| `SERVER_APP_URL` | Public API URL | `http://api.boxsys.local` |
| `SERVER_LOG_LEVEL` | Laravel log level | `info` |
| `SERVER_DB_NAME` | Database name | `boxsys` |
| `SERVER_DB_USER` | PostgreSQL user | `boxsys` |
| `SERVER_DB_PASSWORD` | PostgreSQL password | Required |
| `SERVER_CACHE_PASSWORD` | Redis password | Required |
| `POSTGRES_IMAGE` | PostgreSQL image | `postgres:18-alpine` |
| `REDIS_IMAGE` | Redis image | `redis:8-alpine` |
| `NGINX_IMAGE` | Gateway image | `nginx:alpine` |

Add these entries to the hosts file on the machine running your browser:

```text
127.0.0.1 boxsys.local
127.0.0.1 api.boxsys.local
```

On Windows, the file is `C:\Windows\System32\drivers\etc\hosts`.
On Linux/macOS, it is `/etc/hosts`. For a remote Docker host, use that host's
IP address instead of `127.0.0.1`.

## Usage

Run from the monorepo root with Docker Compose and pnpm available:

| Command | Purpose |
| --- | --- |
| `pnpm docker:up` | Start the database and cache only |
| `pnpm docker:dev` | Build and start the local stack |
| `pnpm docker:build` | Build and start the production stack |
| `pnpm docker:down` | Stop and remove containers while keeping volumes |

| URL | Destination |
| --- | --- |
| `http://boxsys.local` | Portal |
| `http://api.boxsys.local` | Server API |

All external HTTP traffic enters through Nginx. The portal, API, database and
cache have no published host ports. The gateway forwards the requested host,
client address and protocol, and supports Next.js development WebSockets.
Only the two configured hostnames are routed. Both hostnames also resolve to
the gateway inside Docker, including API requests made by the portal server.

Use one scenario per machine: dev locally, build in production. If you change
`GATEWAY_HTTP_PORT`, include that port in both URLs and `SERVER_APP_URL`.
The gateway serves HTTP; TLS is not configured. Run migrations separately
during deployment.

## Images and volumes

Both application Dockerfiles provide `development` and `production` targets.
The portal uses its standalone Next.js image in production. The dev scenario
mounts local application source and keeps dependencies and caches in volumes.

Volume names do not change between environments. PostgreSQL, Redis and
`server-app-storage` retain data when containers are recreated. After changing
a lockfile in dev, run `composer install` in `server` or `pnpm install` in
`portal` to update its dependencies. Rebuild the portal image after changing
shared packages in `packages/`.

Logs are available through container logs. Stopping the stack keeps its volumes.

## Adding components

1. Create `apps/<component>/Dockerfile`.
2. Add its template to `docker-compose.application.yaml`.
3. Extend the template in the dev and build scenarios.
4. Declare its volumes, network and dependencies.
5. Add its hostname and upstream to `gateway/nginx.conf`, and include the service
   in the gateway's `depends_on` list.

The root `.dockerignore` contains shared exclusions. Component-specific rules
belong in `apps/<component>/Dockerfile.dockerignore`.

Compose paths are relative to `deploy/docker/docker-compose.yaml`.
