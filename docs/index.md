---
title: Support
---

# Pressroot — support & help

Everything you need to set up, configure, and get help with **Pressroot**.
Built on **Sage 11 (Roots)** — server-rendered, accessible, and deliberately
light on plugins.

[Settings reference]({{ site.baseurl }}/THEME-SETTINGS.html){: .btn }
[Report an issue](https://github.com/matthummel-pa/pressroot/issues){: .btn }

---

## About

Pressroot started as a bespoke Sage theme built for the author's own
portfolio site, then generalized into a rebrandable framework other
developers can start a project from. **Site Types** (Appearance → Pressroot
→ Site Types) is the part of that generalization aimed specifically at
future-proofing it beyond one portfolio: matching design + starter pages for
Agency/Studio, Freelance/Portfolio, SaaS/Startup, Blog/Content site, and
Marketing/Landing page, so it's a real starting point across different
kinds of businesses.

## Getting started

1. Download or clone the theme into `wp-content/themes/`.
2. From the theme folder, install dependencies and build assets:
   ```bash
   composer install
   npm install
   npm run build
   ```
3. In WordPress, go to **Appearance → Themes** and activate **Matt Hummel**.
4. Open **Appearance → Pressroot** (Site Types tab, the default) and pick a
   **site type** to get a polished starting point — colors, fonts, and
   starter pages all applied together — then fine-tune in the Customizer.

## Where settings live

| Area | What's there |
|---|---|
| **Customize → Theme Options** | Colors, typography, layout, header, navigation, menu/popout, announcement bar, dark mode, footer, SEO, performance, custom code, newsletter, cookie notice, white-label, theme addons. |
| **Appearance → Pressroot** | One page, left-sidebar menu with three sections: **Site Types** (site-type picker + matching Style Kit, regenerate, AI connectors, hero copy, and an Advanced backup/restore section for export/import/reset), **GitHub** (owner, API token, cache hours, OAuth Client ID, Connect with GitHub), **Support** (live status for the theme's own repo — stats, releases, open issues — plus links to its documentation). |
| **Appearance → Local Fonts** | Download + self-host Google Fonts (removes the external request). |

The full catalog of every control is in the **[settings reference]({{ site.baseurl }}/THEME-SETTINGS.html)**.

## Common tasks

- **Re-skin the whole site** — Appearance → Pressroot → Site Types tab → pick a site type (each applies its own matching Style Kit — Editorial, Sage Classic, Warm Sand, Midnight, Mono Slate — automatically).
- **Self-host fonts** — Appearance → Local Fonts → *Download fonts now*, then toggle *Serve fonts locally*.
- **Connect GitHub for project pages** — Appearance → Pressroot → GitHub tab → paste your OAuth App Client ID → *Connect with GitHub* (device-flow login). This raises the API rate limit for the live repo data.
- **Add social icons to a post/page** — insert the **Social Icons** block; it pulls from your site social links by default.
- **Add any icon** — insert the **Icon (Blade)** block and type a name like `si-github`, `heroicon-o-rocket`, or `lucide-zap`.
- **Show a posts/projects grid** — insert the **Post Grid** block.

## FAQ

**Do I need a page builder?**
No. The theme ships starter **patterns** (Hero, Pricing, Testimonials, Logo cloud, Feature grid, CTA band, FAQ, Stat strip) and dynamic blocks you compose in the normal block editor.

**Will it conflict with my SEO plugin?**
No. The built-in SEO/schema output automatically disables itself when Rank Math or Yoast is active.

**Does it work without Docker?**
Yes — there's a full no-Docker local stack (WP-CLI + SQLite + `wp server`). See the development docs.

**How do I move settings to another site?**
Appearance → Pressroot → Site Types tab → *Advanced: Backup & restore settings* → **Export** a JSON file, then **Import** it on the other site.

## Documentation

- [Settings reference]({{ site.baseurl }}/THEME-SETTINGS.html) — every control
- [Development](https://github.com/matthummel-pa/pressroot/blob/main/docs/DEVELOPMENT.md) — local environment, build, deploy
- [Architecture](https://github.com/matthummel-pa/pressroot/blob/main/docs/ARCHITECTURE.md) — how the theme works
- [Brand & design system](https://github.com/matthummel-pa/pressroot/blob/main/docs/BRAND-DESIGN-SYSTEM.md)

## Get help

Found a bug or have a question? **[Open an issue](https://github.com/matthummel-pa/pressroot/issues)** on GitHub — or, from inside wp-admin, **Appearance → Pressroot → Support tab**, which shows the same repo's live status (stats, releases, open issues) and links straight into its docs, without leaving the dashboard.
