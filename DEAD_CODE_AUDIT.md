# DEAD CODE AUDIT — kaslek-theme
**Audit date:** 2026-04-16  
**Auditor:** Claude Code (static analysis — no files were modified)  
**Scope:** All PHP, CSS, and JS files in the theme root and sub-directories  

> This document is read-only reference. No files were changed.

---

## CATEGORY 1 — Unused PHP Functions

---

CATEGORY: Unused PHP Function  
FILE: functions.php:113–119  
CODE:
```php
function kaslek_tijdlabel( $post_id = null ) {
    $datum    = get_the_date( 'U', $post_id ?: get_the_ID() );
    $verschil = floor( ( time() - $datum ) / DAY_IN_SECONDS );
    if ( $verschil === 0 ) return 'Vandaag';
    if ( $verschil === 1 ) return 'Gisteren';
    return $verschil . ' dagen geleden';
}
```
REASON: Defined in functions.php but never called in any template file. A grep across all 12 PHP files finds zero calls to `kaslek_tijdlabel`. Templates display dates via `get_the_date()` directly (single.php:24).  
SAFE TO REMOVE: yes

---

CATEGORY: Unused PHP Function  
FILE: functions.php:121–128  
CODE:
```php
function kaslek_categorie_label( $post_id = null ) {
    $cats = get_the_category( $post_id ?: get_the_ID() );
    if ( empty( $cats ) ) return '';
    $naam  = esc_html( $cats[0]->name );
    $slug  = esc_attr( $cats[0]->slug );
    $link  = esc_url( get_category_link( $cats[0]->term_id ) );
    return '<a href="' . $link . '" class="cat-label ' . $slug . '">' . $naam . '</a>';
}
```
REASON: Defined as a helper for generating category label HTML but never called anywhere. The `template-parts/card-horizontal.php` (line 11) duplicates this logic inline instead of calling this function.  
SAFE TO REMOVE: yes

---

CATEGORY: Unused PHP Function  
FILE: functions.php:133–143  
CODE:
```php
function kaslek_register_view_script() {
    if ( ! is_singular( 'post' ) ) return;
    wp_localize_script( 'kaslek-artikel', 'kaslekArtikel', array_merge(
        (array) ( isset( $GLOBALS['kaslek_artikel_data'] ) ? $GLOBALS['kaslek_artikel_data'] : [] ),
        [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'kaslek_view' ),
            'postId'  => get_the_ID(),
        ]
    ) );
}
```
REASON: Function is defined but never registered to any hook (no `add_action` call references it anywhere in functions.php or any other file). View nonce handling was moved to the inline script in `kaslek_view_footer_script()` (functions.php:156–177), which renders the same data directly via `echo "<script>`. This function is a dead predecessor.  
SAFE TO REMOVE: yes

---

## CATEGORY 2 — Orphaned Enqueued Scripts/Styles

---

CATEGORY: Orphaned Enqueued Script  
FILE: functions.php:54–65 (enqueue) / assets/js/infinite-scroll.js (script file)  
CODE:
```php
if ( is_front_page() ) {
    wp_enqueue_script(
        'kaslek-infinite-scroll',
        get_template_directory_uri() . '/assets/js/infinite-scroll.js',
        [],
        '1.0.0',
        true
    );
    wp_localize_script( 'kaslek-infinite-scroll', 'kaslekData', [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'kaslek_infinite_scroll' ),
    ] );
}
```
REASON: `infinite-scroll.js` requires two DOM elements to do anything: `#infinite-scroll-container` and `#infinite-scroll-trigger`. The front-page template (`front-page.php`) contains `#infinite-scroll-container` but has **no** `#infinite-scroll-trigger` element anywhere. The script's first five lines immediately `return` when the trigger is not found:
```js
const trigger = document.getElementById( 'infinite-scroll-trigger' );
if ( ! container || ! trigger ) return;
```
The script is loaded on every front-page request, creates an observer object, then exits silently. The `kaslekData` nonce object is also emitted to the page but never consumed. The front-page uses a statically pre-loaded set of posts with no pagination mechanism.  
SAFE TO REMOVE: maybe (the file exists; the AJAX handler `kaslek_infinite_scroll_handler` is still needed if the trigger is ever added back — but as-is, the enqueue is wasted)

---

CATEGORY: Orphaned Enqueued Script  
FILE: functions.php:81–94 (enqueue) / assets/js/artikel.js (script file)  
CODE:
```php
if ( is_singular( 'post' ) ) {
    wp_enqueue_script(
        'kaslek-artikel',
        get_template_directory_uri() . '/assets/js/artikel.js',
        [],
        '1.0.0',
        true
    );
    wp_localize_script( 'kaslek-artikel', 'kaslekArtikel', [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'kaslek_stem' ),
        'postId'  => get_the_ID(),
    ] );
}
```
REASON: `artikel.js` begins with:
```js
const widget = document.getElementById( 'stem-widget' );
if ( ! widget ) return;
```
No element with `id="stem-widget"` exists anywhere in `single.php` or any template part loaded on singular posts. The script is enqueued on every single-post page, emits the `kaslekArtikel` localization object (including a stem nonce), and then silently exits. The voting/stem UI was removed from the single-post template but the enqueue, nonce, and AJAX handler (`kaslek_stem_handler`) were not cleaned up. View counting on single posts is handled by a separate inline script via `kaslek_view_footer_script()` and does not depend on `artikel.js`.  
SAFE TO REMOVE: maybe (the file exists; if the stem widget UI is intended to return, the backend is ready — but currently the script is dead weight on every article page load)

---

## CATEGORY 3 — Unused CSS Classes

The following classes are defined in `assets/css/kaslek.css` but appear in **no** PHP template file. A grep across all 12 theme PHP files confirms zero occurrences of each class name. They are grouped by likely origin.

---

### Group A — Breadcrumb navigation (lines 113–116)

CATEGORY: Unused CSS Class  
FILE: assets/css/kaslek.css:113–116  
CODE:
```css
.breadcrumb { padding: 12px 48px; font-family: var(--font); font-size: 11px; color: var(--text-light); display: flex; gap: 6px; align-items: center; }
.breadcrumb a { color: var(--primary); text-decoration: none; font-weight: 400; }
.breadcrumb a:hover { text-decoration: underline; }
.breadcrumb span { opacity: 0.4; }
```
REASON: No template file outputs a `.breadcrumb` element. Likely prepared for a breadcrumb feature (e.g., Yoast SEO breadcrumbs) that was never implemented or hooked.  
SAFE TO REMOVE: yes

---

### Group B — Featured strip / Spraakmakend section (lines 150–159)

CATEGORY: Unused CSS Class  
FILE: assets/css/kaslek.css:150–159  
CODE:
```css
.featured-strip { ... }
.featured-strip-header { ... }
.featured-strip-label { ... }
.featured-strip-line { ... }
.featured-strip-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0; border-left: 3px solid var(--primary); }
.featured-col { ... }
.featured-col:last-child { border-right: none; }
.featured-col-num { ... }
.featured-col-title { ... }
```
REASON: Zero occurrences of any `.featured-strip*` or `.featured-col*` class in any PHP template. The front-page template (`front-page.php`) has a "Spraakmakend" column in `.cat-grid` using `.cat-col` classes, but uses a completely different markup structure. This entire section appears to be from a prior homepage layout.  
SAFE TO REMOVE: yes

---

### Group C — Populair section on article pages (lines 311–327)

CATEGORY: Unused CSS Class  
FILE: assets/css/kaslek.css:311–327  
CODE:
```css
.populair-sectie { padding: 32px 0 48px; background: var(--bg); }
.populair-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; padding: 0 48px; }
.populair-card { display: flex; flex-direction: column; background: var(--white); ... }
.populair-card:hover .populair-card-title { text-decoration: underline; }
.populair-card-img { ... }
.populair-card-content { ... }
.populair-card-title { ... }
```
REASON: `single.php` uses `.sidebar-trending-item` inside `.article-sidebar` for popular posts. No `.populair-sectie`, `.populair-grid`, or `.populair-card` element is rendered in any template.  
SAFE TO REMOVE: yes

---

### Group D — Old mobile layout component system (lines 524–575)

CATEGORY: Unused CSS Class (block)  
FILE: assets/css/kaslek.css:524–575  
CODE:
```css
/* Topbar */
.topbar { ... }
.topbar-logo { ... }
.topbar-social { ... }
.topbar-social-btn { ... }
.topbar-menu { ... }

/* Section label */
.section-label { ... }
.section-label h2 { ... }
.section-label-line { ... }

/* hcard */
.hcard { ... }
.hcard-text { ... }
.hcard-title { ... }
.hcard-divider { ... }

/* grid2 */
.grid2 { ... }
.grid2-card { ... }
.grid2-content { ... }
.grid2-title { ... }

/* textitem */
.textitem { ... }
.textitem-num { ... }
.textitem-content { ... }
.textitem-title { ... }

/* featured-full */
.featured-full { ... }
.featured-full-content { ... }
.featured-full-kicker { ... }
.featured-full-title { ... }
.featured-full-excerpt { ... }

/* dark-featured */
.dark-featured { ... }
.dark-featured-content { ... }
.dark-featured-title { ... }
.dark-list-item { ... }
.dark-list-num { ... }
.dark-list-title { ... }

/* photostrip */
.photostrip { ... }
.photostrip-card { ... }
.photostrip-title { ... }
```
REASON: None of these 30+ class names appear in any template. The current mobile layout uses `.mobile-topbar`, `.mobile-menu`, `.archive-mobile-hero`, etc. This entire block (52 lines) is a prior-generation mobile component system that was replaced wholesale when the current responsive layout was built.  
SAFE TO REMOVE: yes

---

### Group E — Old mobile footer variants (lines 347–349, 583–627)

CATEGORY: Unused CSS Class (block)  
FILE: assets/css/kaslek.css:347–349, 583–627  
CODE:
```css
/* Desktop footer social row (lines 347-349) */
.footer-social { display: flex; gap: 8px; margin-top: 12px; }
.footer-social-btn { width: 30px; height: 30px; background: rgba(255,255,255,0.1); ... }
.footer-social-btn svg { ... }

/* Footer mobile social block (lines 583-619) */
.footer-social-mobile-block { display: none; }
.footer-social-mobile-block-title { ... }
.footer-social-mobile-block-icons { ... }
.footer-social-mobile-block-btn { ... }

/* Footer mobile text/links block (lines 621-627) */
.footer-bottom-mobile { ... }
.footer-links-mobile a { ... }
.footer-desc-mobile { ... }
.footer-social-mobile { display: flex; gap: 8px; margin-bottom: 20px; }
.footer-social-mobile-btn { ... }
```
REASON: `footer.php` uses `.footer-grid`, `.footer-col`, `.footer-col-title`, and `.footer-bottom` but no `.footer-social`, `.footer-social-btn`, `.footer-social-mobile-block`, `.footer-bottom-mobile`, `.footer-links-mobile`, `.footer-desc-mobile`, `.footer-social-mobile`, or `.footer-social-mobile-btn`. These are from a prior footer design that included social icons directly in the footer markup.  
SAFE TO REMOVE: yes

---

### Group F — Miscellaneous individual unused classes

CATEGORY: Unused CSS Class  
FILE: assets/css/kaslek.css:232  
CODE: `.meta-sep { display: none; }`  
REASON: Not output by any template. `single.php` article meta uses `.meta-author`, `.meta-date`, `.meta-readtime` with no separator element.  
SAFE TO REMOVE: yes

---

CATEGORY: Unused CSS Class  
FILE: assets/css/kaslek.css:293  
CODE: `.fotocredit { font-family: var(--font); font-size: 10px; color: var(--text-light); font-style: italic; padding: 6px 0; }`  
REASON: No template file outputs a `.fotocredit` element. Prepared for photo credit captions but never implemented in `single.php`.  
SAFE TO REMOVE: yes

---

CATEGORY: Unused CSS Class  
FILE: assets/css/kaslek.css:303  
CODE: `.sidebar-item-meta { font-family: var(--font); font-size: 10px; color: var(--text-light); margin-top: 3px; }`  
REASON: The sidebar in `single.php` uses `.sidebar-item` containers but never renders a `.sidebar-item-meta` element inside them.  
SAFE TO REMOVE: yes

---

CATEGORY: Unused CSS Class  
FILE: assets/css/kaslek.css:337–338  
CODE:
```css
.mv-card-cat { display: none; }
.mv-card-meta { display: none; }
```
REASON: Both are already `display: none` and no template produces `.mv-card-cat` or `.mv-card-meta` elements. The `single.php` related-posts section uses only `.mv-card` and `.mv-card-content` and `.mv-card-title`.  
SAFE TO REMOVE: yes

---

CATEGORY: Unused CSS Class  
FILE: assets/css/kaslek.css:362  
CODE: `.ad-hide-mobile { display: none; }` (inside `@media (max-width: 767px)`)  
REASON: The companion class `.ad-show-mobile` IS used in `archive.php` and `front-page.php`. `.ad-hide-mobile` appears in zero template files.  
SAFE TO REMOVE: yes

---

CATEGORY: Unused CSS Class  
FILE: assets/css/kaslek.css:1018–1020  
CODE:
```css
.archive-dossier-link { display: inline-flex; align-items: center; gap: 8px; ... }
.archive-dossier-link:hover { background: rgba(255,255,255,0.2); text-decoration: none; }
.archive-dossier-link svg { ... }
```
REASON: `archive.php` computes `$dossier_link` (line 32) but never renders it as an anchor with `.archive-dossier-link`. The variable is set but no template outputs the link element.  
SAFE TO REMOVE: yes

---

CATEGORY: Unused CSS Class  
FILE: assets/css/kaslek.css:1029  
CODE: `.infinite-loader { font-family: var(--font); font-size: 13px; color: var(--text-light); cursor: default; }`  
REASON: Referenced in `infinite-scroll.js` (`trigger.querySelector('.infinite-loader')`), but since `#infinite-scroll-trigger` does not exist in `front-page.php`, the element is never rendered. See also Category 2 finding for `kaslek-infinite-scroll`.  
SAFE TO REMOVE: maybe (remove when the trigger element situation is resolved)

---

CATEGORY: Unused CSS Class  
FILE: assets/css/kaslek.css:1082  
CODE: `.archive-text-meta { font-family: var(--font); font-size: 11px; color: var(--text-light); margin-top: 8px; }`  
REASON: `.archive-text-card` and `.scroll-card-content` are used in archive templates but no template ever outputs a `.archive-text-meta` child element.  
SAFE TO REMOVE: yes

---

CATEGORY: Unused CSS Class  
FILE: assets/css/kaslek.css:1128  
CODE: `.card-horizontal-meta { font-family: var(--font); font-size: 11px; color: var(--text-light); }`  
REASON: `template-parts/card-horizontal.php` outputs `.card-horizontal-content` and `.card-horizontal-title` but never `.card-horizontal-meta`.  
SAFE TO REMOVE: yes

---

CATEGORY: Unused CSS Class  
FILE: assets/css/kaslek.css:1135–1137  
CODE:
```css
.scroll-grid-new {
  animation: kaslek-fadein 0.4s ease forwards;
}
```
REASON: No template or JS file adds this class to any element. The `archive-scroll.js` adds `.visible` to `.archive-card-fadein` elements. `.scroll-grid-new` is not referenced anywhere.  
SAFE TO REMOVE: yes

---

CATEGORY: Unused CSS Class  
FILE: assets/css/kaslek.css:177–178  
CODE:
```css
.textlist-readmore { display: inline-block; font-family: var(--font); font-size: 14px; font-weight: 700; color: var(--primary); text-decoration: underline; }
.textlist-readmore:hover { opacity: 0.75; }
```
REASON: `front-page.php` uses `.textlist-item`, `.textlist-img`, `.textlist-text`, `.textlist-title`, and `.textlist-excerpt` — but never `.textlist-readmore`. The class also appears in a mobile override at line 195 (still dead for the same reason).  
SAFE TO REMOVE: yes

---

## CATEGORY 4 — Unreachable Template Files

No unreachable template files found.

All 12 PHP files are reachable through the standard WordPress template hierarchy or via explicit `get_template_part()` / `get_header()` / `get_footer()` calls:

| File | How it loads |
|---|---|
| `front-page.php` | WordPress: `is_front_page()` |
| `single.php` | WordPress: single post |
| `archive.php` | WordPress: tag / category / author archive |
| `index.php` | WordPress: ultimate fallback (pages with no specific template) |
| `header.php` | `get_header()` in all templates |
| `footer.php` | `get_footer()` in all templates |
| `page-dossiers.php` | WordPress: page assigned "Dossiers" template |
| `page-nieuwstip.php` | WordPress: page assigned "Nieuwstip insturen" template |
| `page-over-kaslek.php` | WordPress: page assigned "Over KasLek" template |
| `template-parts/archive-row.php` | `get_template_part('template-parts/archive', 'row')` in archive.php and functions.php |
| `template-parts/card-horizontal.php` | `get_template_part('template-parts/card', 'horizontal')` in front-page.php and functions.php |

**Note:** `index.php` contains `.content-wrapper` (line 4) which is not defined in `kaslek.css`. This is not dead code but a missing CSS class on the fallback template.

---

## CATEGORY 5 — Ghost Shortcode Handlers

---

CATEGORY: Ghost Shortcode Handler  
FILE: functions.php:445–450  
CODE:
```php
add_shortcode( 'kaslek_ad', function() {
    return '<div class="kaslek-ad-sc" style="margin:50px 0">'
         . '<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="4772512111" data-ad-format="auto" data-full-width-responsive="true"></ins>'
         . '<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>'
         . '</div>';
} );
```
REASON: No PHP template file uses `do_shortcode('[kaslek_ad]')` or outputs `[kaslek_ad]`. All AdSense placements in templates are hardcoded inline `<ins>` blocks (footer.php:1–3, single.php:163–166, single.php:193–196, archive.php:158, etc.). This shortcode could theoretically be inserted into post body content via the WordPress editor — that cannot be confirmed or ruled out without database access. It is not documented as a user-facing shortcode.  
SAFE TO REMOVE: maybe (verify in database whether any post content contains `[kaslek_ad]` before removing; if not, safe to remove)

---

## SUMMARY TABLE

| # | Category | Findings | Safe to Remove | Maybe | No |
|---|---|---|---|---|---|
| 1 | Unused PHP Functions | 3 | 3 | 0 | 0 |
| 2 | Orphaned Enqueued Scripts | 2 | 0 | 2 | 0 |
| 3 | Unused CSS Classes | 22 groups/classes | 19 | 3 | 0 |
| 4 | Unreachable Template Files | 0 | — | — | — |
| 5 | Ghost Shortcode Handlers | 1 | 0 | 1 | 0 |
| | **TOTALS** | **28** | **22** | **6** | **0** |

**CSS dead code volume estimate:** ~150–180 lines of CSS in `kaslek.css` cover classes with zero template usage.

---

## ROLLBACK NOTE

The following files were **inspected (read-only)** during this audit. No files were modified.

| File | Inspected at |
|---|---|
| `functions.php` | 2026-04-16 |
| `header.php` | 2026-04-16 |
| `footer.php` | 2026-04-16 |
| `index.php` | 2026-04-16 |
| `single.php` | 2026-04-16 |
| `archive.php` | 2026-04-16 |
| `front-page.php` | 2026-04-16 |
| `page-dossiers.php` | 2026-04-16 |
| `page-nieuwstip.php` | 2026-04-16 |
| `page-over-kaslek.php` | 2026-04-16 |
| `template-parts/archive-row.php` | 2026-04-16 |
| `template-parts/card-horizontal.php` | 2026-04-16 |
| `assets/css/kaslek.css` | 2026-04-16 |
| `assets/js/infinite-scroll.js` | 2026-04-16 |
| `assets/js/archive-scroll.js` | 2026-04-16 |
| `assets/js/artikel.js` | 2026-04-16 |
| `assets/js/poll.js` | 2026-04-16 |
| `style.css` | 2026-04-16 |

**Git status at time of audit:** Repository is tracked under `.git/` in the theme root. The audit file `DEAD_CODE_AUDIT.md` is the only new file created by this session.

If any finding turns out to be incorrect (e.g., a class is injected dynamically by a plugin, or `[kaslek_ad]` is present in post content), this document should be updated before any removal action is taken.
