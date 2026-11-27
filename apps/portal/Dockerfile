# syntax=docker/dockerfile:1
ARG NODE_IMAGE=node:24-alpine


# --- Stage 1: shared Node.js base ---
FROM ${NODE_IMAGE} AS base

WORKDIR /app

ENV NEXT_TELEMETRY_DISABLED=1


# --- Stage 2: pnpm, shared by development and dependency installation ---
FROM base AS package-manager

ARG PNPM_VERSION=11.25.0

ENV PNPM_HOME=/pnpm

ENV PATH="$PNPM_HOME:$PATH"

RUN --mount=type=cache,target=/root/.npm \
    npm install --global pnpm@${PNPM_VERSION} \
    && pnpm config set store-dir /pnpm/store


# --- Stage 3: isolate the portal; keep the global Turbo installation here ---
FROM package-manager AS pruner

ARG TURBO_VERSION=2.11.10

RUN --mount=type=cache,target=/root/.npm \
    npm install --global turbo@${TURBO_VERSION}

COPY . .

RUN turbo prune @boxsys/portal --docker


# --- Stage 4: install locked dependencies for the pruned workspace ---
FROM package-manager AS dependencies

COPY --from=pruner /app/out/json/ ./

COPY --from=pruner /app/out/pnpm-lock.yaml ./pnpm-lock.yaml

RUN --mount=type=cache,id=boxsys-pnpm,target=/pnpm/store \
    --mount=type=cache,id=boxsys-pnpm-metadata,target=/root/.cache/pnpm \
    --mount=type=cache,target=/tmp/node-compile-cache \
    pnpm install --frozen-lockfile


# --- Stage 5: local development with source, tests, lint and TypeScript ---
FROM dependencies AS development

COPY --from=pruner /app/out/full/ ./

EXPOSE 3000

CMD ["pnpm", "--filter", "@boxsys/portal", "dev", "--hostname", "0.0.0.0"]

# --- Stage 6: compile Next.js and any upstream workspace build tasks ---
FROM dependencies AS builder

COPY --from=pruner /app/out/full/ ./

RUN --mount=type=cache,id=boxsys-portal-next,target=/app/apps/portal/.next/cache \
    pnpm exec turbo run build --filter=@boxsys/portal...


# --- Stage 7: minimal production runtime, without pnpm or build tools ---
FROM base AS production

ENV NODE_ENV=production \
    HOSTNAME=0.0.0.0 \
    PORT=3000

COPY --from=builder --chown=node:node /app/apps/portal/.next/standalone/ ./

COPY --from=builder --chown=node:node /app/apps/portal/.next/static/ ./apps/portal/.next/static/

COPY --from=builder --chown=node:node /app/apps/portal/public/ ./apps/portal/public/

USER node

EXPOSE 3000

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD node -e "fetch('http://127.0.0.1:' + process.env.PORT).then(r => process.exit(r.ok ? 0 : 1)).catch(() => process.exit(1))"
    
CMD ["node", "apps/portal/server.js"]
