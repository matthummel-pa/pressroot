<p align="center">
  <img src="brand/pressroot-logo.svg" alt="Pressroot" width="380">
</p>

<h1 align="center">Pressroot</h1>

<p align="center"><strong>A premium Sage 11 (Roots) WordPress theme framework.</strong><br>
Server-rendered, accessible, deliberately light on plugins — with a deep, point-and-click options system.</p>

<p align="center">Stack: Sage 11 · Blade · Tailwind CSS v4 · Vite · Acorn (Laravel-in-WordPress) · PHP 8.3</p>

---

> **Experimental / marketable build.** Pressroot is a rebrandable productization of a bespoke Sage theme — a starting point for a commercial premium theme. The internals (`prt_` prefix, `prt-` CSS classes, `pressroot` text domain) are namespaced for clean distribution.

## About

Pressroot started as a bespoke Sage theme built for the author's own portfolio site. Rather than keep it as a one-off, it's been generalized into a reusable framework other developers can pick up and rebrand — the design system, admin experience, and block library aren't tied to any one site's content.

Part of that generalization is **Site Types** (Appearance → Pressroot → Site Types): rather than a single portfolio layout, the theme ships matching design + starter-page profiles for several common business categories — Agency/Studio, Freelance/Portfolio, SaaS/Startup, Blog/Content site, and Marketing/Landing page — so it's a fast starting point whether the next project looks nothing like the original one.

## Features

### Design system & theming
- **Tokens-first design** — colors, type scale, spacing as Tailwind v4 `@theme` variables; re-skin the whole site from one place.
- **Style Kits** — one-click presets (Editorial, Classic, Warm Sand, Midnight, Mono Slate).
- **Colors / Typography** — brand + surfaces, 10 font families, base size, line-heights, letter-spacing, plus advanced per-element weights and responsive base sizes.
- **Dark mode** — light / dark / auto with no-flash loading and a dedicated dark navbar surface.
- **`theme.json`** — unlocks spacing, border, shadow, fluid typography, gradient, and duotone controls in the editor.

### Hero & animations
- **Hero builder** — editable eyebrow / H1 / sub-paragraph, 1–3 columns (content + side/2nd image), content position (H/V), max-width + spacing with tablet/mobile overrides, and full flexbox control.
- **Hero media** — side image / illustration, or a background cover image with overlay + min-height.
- **Built-in image finder** — search **Openverse** (no key), **Unsplash** and **Pexels** (optional keys), or **generate an AI image** (free, no key) right in the Customizer; the pick imports into the Media Library and sets the hero image.
- **On-scroll animations** — site-wide reveal (fade/zoom/pop/blur/slide) with speed + a hero entrance animation; respects `prefers-reduced-motion`.

### Header, nav, footer & responsive
- **Header Layout** — full-width menu, sizing, element placement + alignment, bar order, sticky / shrink / transparent behaviors, header CTA.
- **Navigation** — full flexbox control + per-item styling; off-canvas popout with breakpoints, panel style, columns, item styling; menu-item icons + mega menu.
- **Social Icons** — appearance + account URLs in one place; **Top bar** + **scheduled announcement bar**.
- **Footer builder** — 1–4 widget-area columns, palette/custom colors, tagline.
- **Responsive controls** — per-device hide toggles (social, buttons, "Menu" label, logo shrink) and per-breakpoint widths. Mobile ≤640px · tablet 641–1024px.

### Blocks, content & performance
- **Blocks** — Social Icons, Icon (Blade), Post Grid, GitHub repo card/grid/stats/releases + 22 general-purpose starter patterns, browsable from a dedicated **Pattern Library** admin page.
- **Live GitHub project pages** — repo metadata, stars/forks, latest release, README intro (cached); device-flow "Connect with GitHub".
- **Reading UX** — auto table of contents, reading-progress bar, estimated reading time, and copy buttons on code blocks for single posts.
- **Plugin-free contact form**, newsletter, cookie notice, code injection.
- **Performance** — disable bloat, split block CSS, critical CSS, Prism syntax highlighting on code blocks.
- **Fonts** — self-host the active families (Appearance → Local Fonts), or browse & self-host any of 1,500+ Google Fonts families via the native block-editor Font Library.
- **SEO** — Open Graph, Twitter, JSON-LD; auto-off under Rank Math/Yoast.
- **White-label & onboarding** — branded login screen, admin footer credit, and a "Get started" dashboard widget checklist.

### Pressroot AI
- **Site Types** — pick a business category (Agency/Studio, Freelance/Portfolio, SaaS/Startup, Blog/Content site, Marketing/Landing page) to apply its matching Style Kit and create starter pages together, pre-filled with 26 dedicated, hand-built patterns (two swappable variants per page) rather than generic filler.
- **Regenerate** — swap any starter page (or a whole site type at once) to its other hand-built variant with one click, with a live preview before committing.
- **Starter hero copy generator** — a one-line business description in, a draft headline + subheadline out.
- **AI Connectors** — bring your own free API key for Google Gemini, Groq, or OpenRouter, alongside the always-on, keyless Pollinations default — picked per generation from a model dropdown.
- **AI in the block editor** — a "Generate with AI" toolbar button on paragraph/heading/list blocks for everyday content writing, not just the one-time setup screen.
- Switchable off entirely as a **Theme Addon** if a site doesn't want the AI surface at all.

### Settings, developer tools & support
- **Appearance → Pressroot** — one consolidated, left-sidebar settings page: **Site Types** (above), **GitHub** (owner, token, cache, OAuth device-flow connect), and **Support** (live status — stats, languages, releases, open issues — for the theme's own repo, plus curated links to its docs).
- **Export / Import / Reset** — back up every theme setting as JSON and restore it on another install.
- **WP-CLI suite** (`wp pressroot ...`) — settings export/import/reset, Style Kit list/apply, clear compiled views, and a hook registry printer.
- **Hook Registry** — every custom filter/action the theme exposes, documented in one place for child themes and mu-plugins to extend.
- **Dev Mode** — a one-click admin-bar toggle plus a debug panel (environment, template, query count, memory, load time).

## Requirements

| Tool | Version |
|---|---|
| PHP | 8.3+ |
| Composer | 2.x |
| Node | 20.19+ or 22.12+ |
| WordPress | 6.6+ |

## Quick start

```bash
composer install
npm install
npm run build      # or: npm run dev  (Vite HMR)
```

Activate **Pressroot**, then open **Appearance → Pressroot** (Site Types tab) to pick a site type — it applies a matching Style Kit and creates starter pages together — and fine-tune under **Customize → Theme Options**.

## License

MIT © Matt Hummel
