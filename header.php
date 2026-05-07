<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="site-wrapper">

<!-- MOBIELE TOPBAR -->
<div class="mobile-topbar" aria-hidden="true">
	<?php $kaslek_total_formatted = do_shortcode( '[kaslek_total]' ); ?>
	<div class="mobile-topbar-counter">
		Dit jaar <strong>&euro; <?php echo $kaslek_total_formatted; ?></strong> blootgelegd.
	</div>
	<button class="mobile-hamburger" aria-label="Menu openen" aria-expanded="false" aria-controls="mobile-menu">
		<span class="mobile-hamburger-bar"></span>
		<span class="mobile-hamburger-bar"></span>
		<span class="mobile-hamburger-bar"></span>
	</button>
</div>

<!-- MOBIEL MENU OVERLAY -->
<div id="mobile-menu" class="mobile-menu" role="dialog" aria-modal="true" aria-label="Navigatiemenu" hidden>
	<div class="mobile-menu-backdrop"></div>
	<nav class="mobile-menu-panel" aria-label="Primaire navigatie mobiel">
		<div class="mobile-menu-header">
			<span class="mobile-menu-logo"><?= KASLEK_SITE_NAME ?></span>
			<button class="mobile-menu-close" aria-label="Menu sluiten">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
			</button>
		</div>
		<div class="mobile-menu-nav">
			<?php wp_nav_menu( [
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'mobile-nav-list',
				'fallback_cb'    => 'kaslek_fallback_nav',
			] ); ?>
		</div>
		<div class="mobile-menu-social">
			<?php kaslek_social_icons( 'mobile-menu-social-btn' ); ?>
		</div>
		<div class="mobile-menu-footer">
			<a href="<?php echo esc_url( get_permalink( get_page_by_path( KASLEK_PAGE_ABOUT ) ) ); ?>">Over <?= KASLEK_SITE_NAME ?></a>
			<a href="<?php echo esc_url( get_permalink( get_page_by_path( KASLEK_PAGE_TIP ) ) ); ?>">Nieuwstip insturen</a>
		</div>
	</nav>
</div>

<header class="site-header">
	<div class="header-top">
		<div class="header-brand">
			<div class="header-logo-box">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?= KASLEK_SITE_NAME ?> home">
					<?php if ( has_custom_logo() ) : the_custom_logo(); else : ?>
						<div class="header-logo-icon">K</div>
					<?php endif; ?>
				</a>
				<span class="header-logo-name"><?= KASLEK_SITE_NAME ?></span>
			</div>
			<div class="header-tagline">
				<?= KASLEK_TAGLINE_1 ?><br>
				<span class="tagline-jij"><?= KASLEK_TAGLINE_2 ?></span><br><span><?= KASLEK_TAGLINE_3 ?></span>
			</div>
		</div>
		<div class="header-social">
			<?php kaslek_social_icons( 'social-btn' ); ?>
		</div>
	</div>

	<div class="teller">
		Dit jaar <strong>&euro; <?php echo $kaslek_total_formatted; ?></strong> aan overheidsuitgaven blootgelegd.
	</div>

	<nav class="site-nav" aria-label="Primaire navigatie">
		<?php wp_nav_menu( [
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => 'nav-list',
			'fallback_cb'    => 'kaslek_fallback_nav',
		] ); ?>
	</nav>

	<nav class="tag-slider" aria-label="Onderwerpen">
		<ul class="tag-slider-list">
			<li><a href="<?php echo esc_url( home_url( '/dossiers/defensie-en-militaire-steun/' ) ); ?>">defensie</a></li>
			<li><a href="<?php echo esc_url( home_url( '/dossiers/energie-en-klimaat/' ) ); ?>">klimaat</a></li>
			<li><a href="<?php echo esc_url( home_url( '/dossiers/migratie-en-opvang/' ) ); ?>">migratie</a></li>
			<li><a href="<?php echo esc_url( home_url( '/dossiers/onderwijs/' ) ); ?>">onderwijs</a></li>
			<li><a href="<?php echo esc_url( home_url( '/dossiers/ict-en-digitalisering/' ) ); ?>">ICT</a></li>
			<li><a href="<?php echo esc_url( home_url( '/dossiers/veiligheid-en-handhaving/' ) ); ?>">veiligheid</a></li>
			<li><a href="<?php echo esc_url( home_url( '/dossiers/infrastructuurprojecten/' ) ); ?>">Infra</a></li>
			<li><a href="<?php echo esc_url( home_url( '/dossiers/stikstof-en-natuur/' ) ); ?>">natuur</a></li>
			<li><a href="<?php echo esc_url( home_url( '/dossiers/cultuur-en-npo/' ) ); ?>">Cultuur</a></li>
			<li><a href="<?php echo esc_url( home_url( '/dossiers/subsidies-en-fondsen/' ) ); ?>">subsidies</a></li>
			<li><a href="<?php echo esc_url( home_url( '/dossiers/politiek-en-bestuur/' ) ); ?>">Bestuur</a></li>
			<li><a href="<?php echo esc_url( home_url( '/dossiers/zorg-en-sociaal/' ) ); ?>">sociaal</a></li>
		</ul>
	</nav>
</header>

<script>
(function () {
	var btn     = document.querySelector('.mobile-hamburger');
	var closeBtn = document.querySelector('.mobile-menu-close');
	var backdrop = document.querySelector('.mobile-menu-backdrop');
	var menu    = document.getElementById('mobile-menu');
	var panel   = document.querySelector('.mobile-menu-panel');
	var focusableSelectors = 'a[href], button:not([disabled])';
	var previousFocus;

	function getFocusables() {
		return Array.from(panel.querySelectorAll(focusableSelectors));
	}

	function openMenu() {
		previousFocus = document.activeElement;
		menu.hidden = false;
		document.body.style.overflow = 'hidden';
		btn.setAttribute('aria-expanded', 'true');
		requestAnimationFrame(function () {
			menu.classList.add('is-open');
			var focusables = getFocusables();
			if (focusables.length) focusables[0].focus();
		});
	}

	function closeMenu() {
		menu.classList.remove('is-open');
		btn.setAttribute('aria-expanded', 'false');
		document.body.style.overflow = '';
		panel.addEventListener('transitionend', function handler() {
			menu.hidden = true;
			panel.removeEventListener('transitionend', handler);
			if (previousFocus) previousFocus.focus();
		});
	}

	btn.addEventListener('click', openMenu);
	closeBtn.addEventListener('click', closeMenu);
	backdrop.addEventListener('click', closeMenu);

	document.addEventListener('keydown', function (e) {
		if (menu.hidden) return;
		if (e.key === 'Escape') { closeMenu(); return; }
		if (e.key === 'Tab') {
			var focusables = getFocusables();
			var first = focusables[0];
			var last  = focusables[focusables.length - 1];
			if (e.shiftKey && document.activeElement === first) {
				e.preventDefault(); last.focus();
			} else if (!e.shiftKey && document.activeElement === last) {
				e.preventDefault(); first.focus();
			}
		}
	});
})();
</script>
