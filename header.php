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
		Dit jaar <strong>&euro; <?php echo $kaslek_total_formatted; ?></strong> blootgelegd
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
			<span class="mobile-menu-logo">KasLek</span>
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
			<a href="https://x.com/kasleknl" target="_blank" rel="noopener" aria-label="X" class="mobile-menu-social-btn">
				<svg viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.736-8.851L1.254 2.25H8.08l4.253 5.622 5.911-5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
			</a>
			<a href="https://www.facebook.com/people/KasLek/61586491733022/" target="_blank" rel="noopener" aria-label="Facebook" class="mobile-menu-social-btn">
				<svg viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
			</a>
			<a href="https://bsky.app/profile/kaslek.bsky.social" target="_blank" rel="noopener" aria-label="Bluesky" class="mobile-menu-social-btn">
				<svg viewBox="0 0 24 24"><path d="M12 10.8c-1.087-2.114-4.046-6.053-6.798-7.995C2.566.944 1.561 1.266.902 1.565.139 1.908 0 3.08 0 3.768c0 .69.378 5.65.624 6.479.815 2.736 3.713 3.66 6.383 3.364.136-.02.275-.039.415-.056-.138.022-.276.04-.415.056-3.912.58-7.387 2.005-2.83 7.078 5.013 5.19 6.87-1.113 7.823-4.308.953 3.195 2.05 9.271 7.733 4.308 4.267-4.308 1.172-6.498-2.74-7.078a8.741 8.741 0 0 1-.415-.056c.14.017.279.036.415.056 2.67.297 5.568-.628 6.383-3.364.246-.828.624-5.79.624-6.478 0-.69-.139-1.861-.902-2.204-.659-.299-1.664-.62-4.3 1.24C16.046 4.748 13.087 8.687 12 10.8z"/></svg>
			</a>
			<a href="https://t.me/+quqoaFfhaJs5NjE0" target="_blank" rel="noopener" aria-label="Telegram" class="mobile-menu-social-btn">
				<svg viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
			</a>
		</div>
		<div class="mobile-menu-footer">
			<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'over-kaslek' ) ) ); ?>">Over KasLek</a>
			<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'nieuwstip-insturen' ) ) ); ?>">Nieuwstip insturen</a>
		</div>
	</nav>
</div>

<header class="site-header">
	<div class="header-top">
		<div class="header-brand">
			<div class="header-logo-box">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="KasLek home">
					<?php if ( has_custom_logo() ) : the_custom_logo(); else : ?>
						<div class="header-logo-icon">K</div>
					<?php endif; ?>
				</a>
				<span class="header-logo-name">KasLek</span>
			</div>
			<div class="header-tagline">
				De overheid deelt uit.<br>
				<span class="tagline-jij">Jij betaalt.</span><br><span>Wij kijken mee.</span>
			</div>
		</div>
		<div class="header-social">
			<a href="https://x.com/kasleknl" class="social-btn" target="_blank" rel="noopener" aria-label="X">
				<svg viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.736-8.851L1.254 2.25H8.08l4.253 5.622 5.911-5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
			</a>
			<a href="https://www.facebook.com/people/KasLek/61586491733022/" class="social-btn" target="_blank" rel="noopener" aria-label="Facebook">
				<svg viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
			</a>
			<a href="https://bsky.app/profile/kaslek.bsky.social" class="social-btn" target="_blank" rel="noopener" aria-label="Bluesky">
				<svg viewBox="0 0 24 24"><path d="M12 10.8c-1.087-2.114-4.046-6.053-6.798-7.995C2.566.944 1.561 1.266.902 1.565.139 1.908 0 3.08 0 3.768c0 .69.378 5.65.624 6.479.815 2.736 3.713 3.66 6.383 3.364.136-.02.275-.039.415-.056-.138.022-.276.04-.415.056-3.912.58-7.387 2.005-2.83 7.078 5.013 5.19 6.87-1.113 7.823-4.308.953 3.195 2.05 9.271 7.733 4.308 4.267-4.308 1.172-6.498-2.74-7.078a8.741 8.741 0 0 1-.415-.056c.14.017.279.036.415.056 2.67.297 5.568-.628 6.383-3.364.246-.828.624-5.79.624-6.478 0-.69-.139-1.861-.902-2.204-.659-.299-1.664-.62-4.3 1.24C16.046 4.748 13.087 8.687 12 10.8z"/></svg>
			</a>
			<a href="https://t.me/+quqoaFfhaJs5NjE0" class="social-btn" target="_blank" rel="noopener" aria-label="Telegram">
				<svg viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
			</a>
		</div>
	</div>

	<div class="teller">
		Dit jaar <strong>&euro; <?php echo $kaslek_total_formatted; ?></strong> aan overheidsuitgaven blootgelegd
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
