---
applyTo: "{composer.json,package.json,plugin.php,readme.md,readme.txt}"
description: "Use when: preparing a production plugin release zip, removing Composer dev dependencies, building assets, creating plugin zip, and deleting local zip after release upload."
---

# Production Release Packaging Workflow

Use this workflow when preparing a GitHub release artifact.

## 1) Prepare Composer for release artifact

- Remove Composer dev dependencies in the release context.
- Preferred approach: use `composer install --no-dev --optimize-autoloader` during release packaging.
- If release packaging requires a production `composer.json` snapshot, ensure `require-dev` is removed from that release snapshot before zipping.
- Do not remove required runtime dependencies.

## 2) Build production assets

- Run `npm run build` before creating any release zip.
- Ensure built assets in `build/` are up to date and included.

## 3) Create zip artifact

- Run `npm run plugin-zip` to generate the release zip.
- Attach that zip to the GitHub release.

## 4) Cleanup local artifact

- Delete the generated zip file locally after successful release attachment.
- Keep repository tree clean after release tasks.

## 5) Verification checklist

- Plugin activates with production dependencies only.
- Updater bootstrap remains intact in `plugin.php`.
- Release zip contains production assets and excludes unnecessary development files.
