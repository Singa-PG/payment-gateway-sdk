# Build & Development Guide

## Prerequisites

```bash
node --version  # v14.0.0 or higher
npm --version   # v6.0.0 or higher
```

## Installation

```bash
npm install
```

## Development

### Run Examples

```bash
npm run dev
# or
node examples/basic-usage.js
```

### Watch Mode (Node 18+)

```bash
node --watch examples/basic-usage.js
```

## Building

### Clean Build

```bash
npm run clean
```

### Full Build

```bash
npm run build
```

This will:

1. Clean dist/ directory
2. Generate TypeScript definitions (.d.ts files)
3. Build CommonJS bundle (dist/index.js)
4. Build ESM bundle (dist/index.mjs)

### Individual Builds

```bash
# TypeScript definitions only
npm run build:types

# CommonJS only
npm run build:cjs

# ESM only
npm run build:esm
```

## Testing

```bash
# Run all tests
npm test

# Run with coverage
npm test -- --coverage

# Watch mode
npm test -- --watch
```

## Linting

```bash
npm run lint
```

## Publishing

### Dry Run

```bash
npm publish --dry-run
```

### Publish to NPM

```bash
npm publish --access public
```

The `prepublishOnly` script will automatically run build before publishing.

## Verifying Build

### Check Output Files

```bash
ls -la dist/
# Should show:
# - index.js (CommonJS)
# - index.mjs (ESM)
# - index.d.ts (TypeScript definitions)
# - *.d.ts.map (Source maps)
```

### Test Imports

```bash
# CommonJS
node -e "const {SingaPay} = require('./dist/index.js'); console.log(SingaPay.name)"

# ESM
node -e "import('./dist/index.mjs').then(m => console.log(m.SingaPay.name))"

# TypeScript
echo "import { SingaPay } from './dist/index'; const s = new SingaPay({} as any);" > test.ts
npx tsc test.ts --noEmit
```

## Troubleshooting

### Issue: TypeScript errors during build

```bash
# Check TypeScript version
npm list typescript

# Reinstall TypeScript
npm install --save-dev typescript@latest
```

### Issue: esbuild errors

```bash
# Check esbuild version
npm list esbuild

# Clear cache and reinstall
rm -rf node_modules package-lock.json
npm install
```

### Issue: Missing .d.ts files

```bash
# Ensure TypeScript is installed
npm install --save-dev typescript

# Run types build explicitly
npm run build:types
```
