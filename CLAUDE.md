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

## Aanpassingsregels

- "Ausgabenspur" of "DE" = aanpassen in kaslek-de, nooit in kaslek-base
- "KasLek" of "NL" = aanpassen in kaslek-nl, nooit in kaslek-base
- kaslek-base alleen aanpassen als fix niet in een child theme kan

## Config-systeem

Child functions.php laadt als eerste → definieert constanten → parent gebruikt die.
Parent laadt config via: `require_once get_stylesheet_directory() . '/config.php';`
Fallbacks staan bovenaan `functions.php`. Constanten staan gedocumenteerd in config.php per child.

## Breakpoints

- Mobiel: `max-width: 767px`
- Desktop: `min-width: 768px`

## Template overrides

Templates met zichtbare tekst staan als override in de child theme.
De parent versie is fallback — nooit de enige bron van zichtbare copy.

**KRITISCH:** Page templates in kaslek-base bestaan NIET. Ze staan uitsluitend in de child themes. Zet nooit een page template in kaslek-base — dat trekt naar beide sites en breekt één ervan.

Child theme template override werkt alleen als `Template Name:` in de child-file **exact** overeenkomt met die in de base. Een andere naam = aparte template, geen override.

## Deploy-architectuur

kaslek-base wordt gedeployd **vanuit de NL lokale map** (`kaslek\...\kaslek-base`) naar beide servers.
De ausgabenspur lokale kaslek-base (`ausgabenspur\...\kaslek-base`) wordt **nooit** gedeployd — wijzigingen daar hebben geen effect op productie.

Volgorde bij wijzigingen:
1. Wijzig in kaslek-nl of kaslek-de — nooit direct in kaslek-base tenzij het beide sites raakt
2. Deploy via het bat-bestand
3. Beide sites trekken dezelfde kaslek-base; child themes zijn per site

## Remotes (GitHub)

- kaslek-base: `git@github.com:ferdituinman/kaslek-base.git`
- kaslek-nl: `git@github.com:ferdituinman/kaslek-nl.git`
- kaslek-de: `git@github.com:ferdituinman/kaslek-de.git`

## Deploy

Bat-bestanden staan op: `C:\Users\ferdi\Desktop\deploy\beidesites\`
- `deploy-base.bat` — deployed kaslek-base naar kaslek.nl server
- `deploy-nl.bat` — deployed kaslek-nl naar kaslek.nl server
- `deploy-de.bat` — deployed kaslek-de (DE server nog onbekend)

## Debug-volgorde

Bij onverwacht gedrag altijd in deze volgorde:
1. CSS — grep op `display:none` in child theme en kaslek-base binnen de juiste breakpoint
2. JS — zoek op `classList`, `style.display`, `hide`
3. PHP — condities in templates

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

## Local development — OPcache

Local by Flywheel gebruikt PHP OPcache. **PHP-template wijzigingen komen pas door na een OPcache-flush.**

Diagnose: CSS-change werkt wel maar PHP-template change niet → OPcache.
Oplossing: gebruik `wp_add_inline_style()` in `functions.php` in plaats van inline styles in PHP-templates.

## AdSense

Publisher ID en slot staan in WordPress opties (option-based, niet hardcoded).
Ad-containers gebruiken `display:flex` — `<ins>` heeft altijd `width:100%` nodig anders `offsetWidth=0`.
Altijd 50px margin boven en onder elke ad placement.
