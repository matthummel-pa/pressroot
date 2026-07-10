# Local dev — modern, app-free (Node + Playground CLI)

No Local app, no Docker, no MAMP. This uses **Playground CLI** (`@wp-playground/cli`, the successor to wp-now): it runs a real WordPress on **WASM PHP** straight from Node, with WP-CLI built
in. Sage's **Vite** dev server gives you live hot-reload while you edit.

## Requirements

- **Node 20.19+ / 22.12+** (you have it)
- **Composer** — needed once to install Sage's framework (Acorn). It's a CLI, not an
  app. On Windows: https://getcomposer.org/Composer-Setup.exe (or `winget install Composer`).

## One-time setup

From inside the theme folder (the `pressroot` checkout):

```bash
composer install            # installs Acorn (Sage's Laravel framework) -> vendor/
npm install                 # installs Vite, Tailwind, Playground CLI, etc.
npm run build               # compiles CSS/JS -> public/build/
```

## Run it (live local)

```bash
npm run wp                  # boots WordPress via Playground CLI (6 workers, no 502s)
```

Playground CLI prints a URL like `http://localhost:8881` — open it. Admin is at `/wp-admin`
(user `admin`, pass `password`). The theme is already active and permalinks are pretty.

For **live hot-reload** while you style, open a second terminal and run:

```bash
npm run dev                 # Vite dev server — edits to Blade/CSS refresh instantly
```

## Seed content (automatic)

There's no import step. The first time you load `/wp-admin`, the seed mu-plugins
rebuild all preview content, menus, and settings automatically. To re-stamp seeded
pages from the current pattern files, hit any admin URL with **`?prt_reseed=1`**
(all main pages) or **`?prt_reseed=projects`** (one page). See `app/seed-pages.php`.

## Day-to-day

- Edit templates in `resources/views/*.blade.php`, styles/tokens in
  `resources/css/app.css`. With `npm run dev` running, changes hot-reload.
- `npm run build` before committing/deploying for production assets.

## Daily commands

| Command | What it does |
|---|---|
| `npm run wp` | Boots WordPress via Playground CLI on `localhost:8881` (admin / password) |
| `npm run dev` | Vite dev server — Blade/CSS hot reload |
| `npm run build` | Compile CSS/JS → `public/build/` |
| `npm run lint` | ESLint (`resources/js`) + Pint (`app/`) |
| `npm run refresh` | One shot: lint + build |

## Refreshing designs & content

- **Site Types tab** (Appearance → Pressroot): applying a category refreshes/creates
  its starter pages; the 🎲 buttons deal random new designs + a new site-wide kit.
  Critical CSS is auto-flushed on every deal.
- **`?prt_reseed=1`** on any admin URL re-stamps seeded pages from the current pattern
  files (use `?prt_reseed=projects` for one page).
- Fill in the **Brand tab first** — it steers kit selection, accent color, and AI copy voice.

## Gotchas

- Hard-refresh after `npm run build` (the hashed CSS filename changes).
- Repofolio OAuth can't call back to `localhost:8881` — use the manual PAT field on the GitHub tab.
- Footer/kit changes are *defaults*: values saved in the Customizer win.
- If a page still shows an old design, it's frozen DB content — hit 🎲 refresh or `?prt_reseed=1`.

## Honest caveat (Sage on WASM PHP)

Sage is heavy — it boots Laravel (Acorn). Playground's WASM PHP usually runs it, but if you
hit a white screen or an Acorn/extension error on first load, that's the WASM runtime
missing something Laravel wants. Reliable fallbacks, in order of effort:

1. **wp-env** (`npm i -g @wordpress/env`, needs Docker Desktop) — full native PHP.
2. A real **PHP 8.3 + MySQL** locally (XAMPP-free: `php -S` + SQLite plugin, or your host's SSH).

If you'd rather avoid Composer/Acorn entirely, I can also convert this to a **classic
(non-Acorn) theme** — same khaki/Fraunces design, same live GitHub data, plain PHP
templates + the same Vite/Tailwind build — which runs under Playground with zero Composer.
Just ask.

## Deploying to matthummel.com later

Build, then upload the theme **with `vendor/` and `public/build/`** (or build on the
host). Activate, then Settings → Permalinks → Save.


## Playground CLI notes (2026-07)

`npm run wp` now uses `@wp-playground/cli server` instead of the deprecated
wp-now. Details:

- Serves on **http://127.0.0.1:8881** with `--workers=6` (parallel requests —
  fixes the Customizer "Bad Gateway"). Avoid `--workers=auto`: in CLI 3.1.x it
  spawns workers with the wrong PHP version.
- Site data persists in `.playground/` (database + uploads are gitignored;
  `blueprint.json` + dev mu-plugins are committed).
- `.playground/mu-plugins/00-acorn-storage.php` carries the WASM fixes,
  including forcing `APP_RUNNING_IN_CONSOLE=false` — Playground's PHP reports
  SAPI `cli`, which otherwise makes Acorn skip `bootHttp()` and fatal on the
  front end.
- Fresh reset: stop the server, `rm -rf .playground/database/*`, start, then
  load `/wp-admin` once — the seed mu-plugins rebuild all preview content,
  menus, and settings automatically.
