# CLAUDE.md

This file provides guidance to Claude Code when working in this repository.

## Werkmap & architectuur

Dit is `kaslek-base` — de **parent theme**. Alle logica, templates en CSS leven hier.
Wijzigingen hier gelden voor **alle sites**. Nooit in kaslek-base werken voor iets wat alleen één site raakt.

Twee child themes:
| Child | Site | Taal | Lokaal pad |
|-------|------|------|------------|
| kaslek-nl | kaslek.nl | Nederlands (NL) | `C:\Users\ferdi\Local Sites\kaslek\app\public\wp-content\themes\kaslek-nl\` |
| kaslek-de | ausgabenspur.de | Duits (DE) | `C:\Users\ferdi\Local Sites\ausgabenspur\app\public\wp-content\themes\kaslek-de\` |

kaslek-base moet in **beide** lokale themes-mappen aanwezig zijn:
- `C:\Users\ferdi\Local Sites\kaslek\app\public\wp-content\themes\kaslek-base\`
- `C:\Users\ferdi\Local Sites\ausgabenspur\app\public\wp-content\themes\kaslek-base\`

Na elke wijziging in kaslek-base: kopieer de map ook naar de ausgabenspur themes-map.

## Config-systeem

Child functions.php laadt als eerste → definieert constanten → parent gebruikt die.
Parent laadt config via: `require_once get_stylesheet_directory() . '/config.php';`
Fallbacks staan bovenaan `functions.php`.

| Constante | NL waarde | DE waarde |
|-----------|-----------|-----------|
| KASLEK_SITE_NAME | KasLek | Ausgabenspur |
| KASLEK_LANG | nl | de |
| KASLEK_LOCALE | nl-NL | de-DE |
| KASLEK_GA_ID | G-1DZQ6K8M6V | (nog leeg) |
| KASLEK_EMAIL | redactie@kaslek.nl | (nog leeg) |
| KASLEK_TAGLINE_1 | De overheid deelt uit. | Der Staat verteilt. |
| KASLEK_TAGLINE_2 | Jij betaalt. | Du zahlst. |
| KASLEK_TAGLINE_3 | Wij kijken mee. | Wir schauen hin. |
| KASLEK_LOCAL_URL | http://kaslek.local | http://ausgabenspur.local |
| KASLEK_PRODUCTION_URL | https://www.kaslek.nl | https://www.kaslek.nl (tijdelijk) |
| KASLEK_PAGE_ABOUT | over-kaslek | ueber-ausgabenspur |
| KASLEK_PAGE_TIP | nieuwstip-insturen | hinweis-einreichen |

## Template overrides

Templates met zichtbare tekst staan als override in de child theme.
De parent versie is fallback — nooit de enige bron van zichtbare copy.

Overrides in kaslek-nl en kaslek-de:
- header.php
- footer.php
- front-page.php
- single.php
- archive.php
- page-dossiers.php
- page-over-kaslek.php
- page-nieuwstip.php
- template-parts/card-horizontal.php
- template-parts/archive-row.php

## Remotes (GitHub)

- kaslek-base: `git@github.com:ferdituinman/kaslek-base.git`
- kaslek-nl: `git@github.com:ferdituinman/kaslek-nl.git`
- kaslek-de: `git@github.com:ferdituinman/kaslek-de.git`

## Deploy

Bat-bestanden staan op: `C:\Users\ferdi\Desktop\deploy\beidesites\`
- `deploy-base.bat` — deployed kaslek-base naar kaslek.nl server
- `deploy-nl.bat` — deployed kaslek-nl naar kaslek.nl server
- `deploy-de.bat` — deployed kaslek-de (DE server nog onbekend)

Server setup instructies: `C:\Users\ferdi\Desktop\deploy\beidesites\server-en-github-setup.txt`

## What This Is

Classic (non-block) WordPress parent theme. No build system, no preprocessors. PHP templates, single CSS file, vanilla JS.

## Core Systems in functions.php

**Dossier System** — Tags worden gerouteerd naar `/dossiers/{slug}/` via custom rewrite rules. Slugs staan in `KASLEK_DOSSIER_SLUGS` (config.php per child). Nooit rewrite rules aanpassen zonder permalinks te flushen.

**KasLek Total / Spending Tracker** — Bijhoudt overheidsuitgaven in post meta:
- Meta key: `kaslek_amount_cents` (in euro cents)
- Constante `KASLEK_TOTAL_YEAR` = 2026
- REST endpoint: `GET /kaslek/v1/total`
- Shortcode: `[kaslek_total format="%s" decimals="0"]`

**Image URL swap** — Output-buffer vervangt `KASLEK_LOCAL_URL` door `KASLEK_PRODUCTION_URL` on the fly. Lokale sites hebben geen afbeeldingen — komen altijd van productie.

**AJAX Endpoints:**
- `kaslek_infinite_scroll` — Front-page paginering
- `kaslek_archive_scroll` — Archive infinite scroll
- `kaslek_view_count` — Post view counter via sendBeacon

## AdSense

Publisher ID en slot staan in WordPress opties (option-based, niet hardcoded).
Ad-containers gebruiken `display:flex` — `<ins>` heeft altijd `width:100%` nodig anders `offsetWidth=0`.
Altijd 50px margin boven en onder elke ad placement.
