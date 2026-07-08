<p align="center">
  <img src="brand/pressroot-logo.svg" alt="Pressroot" width="380">
</p>

<h1 align="center">Pressroot</h1>

<p align="center"><strong>Your brand in. Your site out.</strong><br>
A premium Sage 11 (Roots) WordPress theme framework with an AI-assisted design generator —<br>
deep enough for developers, sharp enough for marketers, simple enough to run solo.</p>

<p align="center">Stack: Sage 11 · Blade · Tailwind CSS v4 · Vite · Acorn (Laravel-in-WordPress) · PHP 8.3</p>

---

> **Experimental / marketable build.** Pressroot is a rebrandable productization of a bespoke Sage theme — a starting point for a commercial premium theme. The internals (`prt_` prefix, `prt-` CSS classes, `pressroot` text domain) are namespaced for clean distribution.

## About

Pressroot started as a bespoke Sage theme built for the author's own portfolio site. Rather than keep it as a one-off, it's been generalized into a reusable framework other developers can pick up and rebrand — the design system, admin experience, and block library aren't tied to any one site's content.

Part of that generalization is the **design generator** (Appearance → Pressroot): answer a short **Brand** questionnaire, pick one of **eight Site Types** — Agency/Studio, Freelance/Portfolio, SaaS/Startup, Blog/Content site, Marketing/Landing page, Affiliate Marketing, Restaurant/Café, Real Estate — and the theme deals a complete site: pages, layouts, a design kit, and a current design trend, re-rollable with one 🎲 click until it fits. Gutenberg is the page builder; AI (switchable off entirely) writes the copy and images from your brand answers. The whole theme wears the [Repofolio](https://github.com/matthummel-pa/repofolio) design language, and Repofolio itself ships inside as a theme addon.

## Features

### Design system & theming
- **Tokens-first design** — colors, type scale, spacing as Tailwind v4 `@theme` variables; re-skin the whole site from one place.
- **Style Kits** — 13 one-click presets: the six originals plus a Repofolio family (Iris Dark, Pink Pop, Coral Cream, Mint Fresh, Cyan Sky, Amber Toast) and a reserved **Core Marketing** kit only the Marketing site type can deal.
- **Design trends** — six switchable CSS-only trend layers (Bento spectrum, Glassmorphism, Neo-brutalist, Editorial serif, Swiss minimal, Retro pop), randomized per refresh or locked from the Brand tab.
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
- **Repofolio built in** — live GitHub repo grid block, project case-study post type, OAuth "Connect with GitHub" (PAT fallback for local dev); ships as a theme addon and yields to the standalone Repofolio plugin when active.
- **Reading UX** — auto table of contents, reading-progress bar, estimated reading time, and copy buttons on code blocks for single posts.
- **Plugin-free contact form**, newsletter, cookie notice, code injection.
- **Performance** — disable bloat, split block CSS, critical CSS, Prism syntax highlighting on code blocks.
- **Fonts** — self-host the active families (Appearance → Local Fonts), or browse & self-host any of 1,500+ Google Fonts families via the native block-editor Font Library.
- **SEO** — Open Graph, Twitter, JSON-LD; auto-off under Rank Math/Yoast.
- **White-label & onboarding** — branded login screen, admin footer credit, and a "Get started" dashboard widget checklist.

### The design generator (Pressroot AI)
- **Brand tab** — a plain-language questionnaire (name, one-liner, brand color, light/dark, personality, audience, industry, voice words, goal, imagery style, content density, design trend) that steers everything the generator produces. One checkbox syncs your answers to the site title & tagline.
- **Site Types ×8** — Agency/Studio, Freelance/Portfolio, SaaS/Startup, Blog/Content, Marketing/Landing, Affiliate Marketing, Restaurant/Café, Real Estate. Picker cards show **live previews of all four designs per page**, rendered in each type's own kit.
- **🎲 Refresh = a whole new theme** — every apply/refresh deals a random pattern variant per page (2 hand-built + 2 generated remix variants, ~50 generated patterns), a random design kit from the type's pool, and a design trend — filtered by your brand answers, never repeating the current deal, with branding re-asserted last and caches flushed so it paints instantly.
- **✨ AI Builder** — "Write with AI" per page or per site type: your selected model rewrites every text segment from the brand profile (audience, tone, goal, density). AI supplies **text only** — block markup is never AI-touched, so pages can't break. Plus one-click AI brand-image generation into the Media Library.
- **Gutenberg is the page builder** — every generated page is plain blocks with an "Edit blocks" shortcut; the AI and the block editor share the same canvas.
- **AI Connectors** — bring a free key for Google Gemini, Groq, or OpenRouter, alongside the keyless Pollinations default; all calls are proxied server-side.
- **"Powered by AI — or not"** — one Brand-tab switch turns off every feature that calls an AI service; the design generator keeps working without it. The whole module is also removable as a Theme Addon.

### Settings, developer tools & support
- **Appearance → Pressroot** — one consolidated settings page in the Repofolio design language: **Brand**, **Site Types**, **GitHub** (the bundled Repofolio addon: OAuth connect, repo grid block, project case studies — the standalone plugin takes over automatically if activated), and **Support** (live repo status + docs links).
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
