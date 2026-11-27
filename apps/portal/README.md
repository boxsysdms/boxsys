This is a [Next.js](https://nextjs.org) project bootstrapped with [`create-next-app`](https://nextjs.org/docs/app/api-reference/cli/create-next-app).

## Getting Started

First, run the development server:

```bash
npm run dev
# or
yarn dev
# or
pnpm dev
# or
bun dev
```

Open [http://localhost:3000](http://localhost:3000) with your browser to see the result.

You can start editing the page by modifying `app/page.tsx`. The page auto-updates as you edit the file.

This project uses [`next/font`](https://nextjs.org/docs/app/building-your-application/optimizing/fonts) to automatically optimize and load [Geist](https://vercel.com/font), a new font family for Vercel.

## Testing

Run the unit tests with Vitest:

```bash
pnpm test
```

## Learn More

To learn more about Next.js, take a look at the following resources:

- [Next.js Documentation](https://nextjs.org/docs) - learn about Next.js features and API.
- [Learn Next.js](https://nextjs.org/learn) - an interactive Next.js tutorial.

You can check out [the Next.js GitHub repository](https://github.com/vercel/next.js) - your feedback and contributions are welcome!

## Deploy on Vercel

The easiest way to deploy your Next.js app is to use the [Vercel Platform](https://vercel.com/new?utm_medium=default-template&filter=next.js&utm_source=create-next-app&utm_campaign=create-next-app-readme) from the creators of Next.js.

Check out our [Next.js deployment documentation](https://nextjs.org/docs/app/building-your-application/deploying) for more details.

## Docker

Run from the monorepo root:

```sh
docker build -f apps/portal/Dockerfile -t boxsys-portal .
docker run --rm -p 3000:3000 boxsys-portal
```

The default `production` target runs the Next.js standalone server as a non-root
user. The image contains only runtime dependencies and static assets. The
`development` target includes pnpm and development tools. Downloaded packages,
registry metadata and npm caches use BuildKit cache mounts rather than image
layers. Turbo is installed globally only in the pruning stage; the workspace
uses its locked local version.

The development image is approximately 735 MB (previously 1.01 GB). Most of its
remaining size comes from Next.js, its native compiler and development tools,
including TypeScript, lint and tests.

The build uses the root pnpm lockfile and prunes the workspace to the portal and
its dependencies. Local dependencies, build output and environment files are
excluded through `Dockerfile.dockerignore`.
