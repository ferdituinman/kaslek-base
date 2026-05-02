<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ─────────────────────────────────────────
   THEMA SETUP
───────────────────────────────────────── */
function kaslek_setup() {
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ] );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'custom-logo' );

	register_nav_menus( [
		'primary' => __( 'Primaire navigatie', 'kaslek' ),
	] );
}
add_action( 'after_setup_theme', 'kaslek_setup' );

if ( ! isset( $content_width ) ) {
	$content_width = 860;
}

/* ─────────────────────────────────────────
   BLOAT VERWIJDEREN
───────────────────────────────────────── */
add_action( 'init', function() {
	remove_action( 'wp_head',         'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail',          'wp_staticize_emoji_for_email' );
} );

add_action( 'wp_enqueue_scripts', function() {
	wp_deregister_script( 'wp-embed' );
}, 100 );

/* ─────────────────────────────────────────
   SCRIPTS & STYLES
───────────────────────────────────────── */
function kaslek_scripts() {
	wp_enqueue_style(
		'kaslek-main',
		get_template_directory_uri() . '/assets/css/kaslek.css',
		[],
		filemtime( get_template_directory() . '/assets/css/kaslek.css' )
	);

	if ( is_archive() ) {
		wp_enqueue_script(
			'kaslek-archive-scroll',
			get_template_directory_uri() . '/assets/js/archive-scroll.js',
			[],
			'1.0.0',
			true
		);
		wp_localize_script( 'kaslek-archive-scroll', 'kaslekData', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'kaslek_infinite_scroll' ),
		] );
	}


}
add_action( 'wp_enqueue_scripts', 'kaslek_scripts' );

/* ─────────────────────────────────────────
   EXCERPT
───────────────────────────────────────── */
add_filter( 'excerpt_length', fn() => 20 );
add_filter( 'excerpt_more',   fn() => '' );

/* ─────────────────────────────────────────
   HELPERS
───────────────────────────────────────── */
function kaslek_leestijd( $post_id = null ) {
	$content = get_post_field( 'post_content', $post_id ?: get_the_ID() );
	$minuten = max( 1, round( str_word_count( strip_tags( $content ) ) / 200 ) );
	return $minuten . ' min';
}

/* ─────────────────────────────────────────
   POST VIEWS
───────────────────────────────────────── */
function kaslek_view_handler() {
	check_ajax_referer( 'kaslek_view', 'nonce' );
	$post_id = absint( $_POST['post_id'] );
	if ( ! $post_id ) wp_die();
	$views = (int) get_post_meta( $post_id, 'post_views_count', true );
	update_post_meta( $post_id, 'post_views_count', $views + 1 );
	wp_die();
}
add_action( 'wp_ajax_kaslek_view',        'kaslek_view_handler' );
add_action( 'wp_ajax_nopriv_kaslek_view', 'kaslek_view_handler' );

function kaslek_view_footer_script() {
	if ( ! is_singular( 'post' ) ) return;
	$nonce   = wp_create_nonce( 'kaslek_view' );
	$post_id = get_the_ID();
	$ajax    = admin_url( 'admin-ajax.php' );
	echo "<script>
(function(){
  if(window.requestIdleCallback){
    requestIdleCallback(function(){ sendView(); });
  } else {
    setTimeout(sendView, 1000);
  }
  function sendView(){
    var fd = new FormData();
    fd.append('action','kaslek_view');
    fd.append('nonce','" . esc_js( $nonce ) . "');
    fd.append('post_id','" . (int) $post_id . "');
    navigator.sendBeacon ? navigator.sendBeacon('" . esc_url( $ajax ) . "', fd) : fetch('" . esc_url( $ajax ) . "',{method:'POST',body:fd,keepalive:true});
  }
})();
</script>";
}
add_action( 'wp_footer', 'kaslek_view_footer_script' );

function kaslek_counter_script() {
	if ( ! wp_script_is( 'kaslek-counter', 'done' ) ) :
	?>
	<script>
	(function () {
		var els = document.querySelectorAll('.kaslek-total[data-total]');
		if ( ! els.length ) return;
		els.forEach(function (el) {
			var target   = parseFloat(el.getAttribute('data-total'));
			if ( isNaN(target) || target <= 0 ) return;
			var duration = 1500;
			var start    = null;
			function fmt(n) {
				return Math.round(n).toLocaleString('nl-NL');
			}
			function step(ts) {
				if (!start) start = ts;
				var p = Math.min((ts - start) / duration, 1);
				var eased = 1 - Math.pow(1 - p, 3);
				el.textContent = fmt(eased * target);
				if (p < 1) {
					requestAnimationFrame(step);
				} else {
					el.textContent = fmt(target);
				}
			}
			setTimeout(function() { requestAnimationFrame(step); }, 500);
		});
	})();
	</script>
	<?php
	endif;
}
add_action( 'wp_footer', 'kaslek_counter_script' );

/* ─────────────────────────────────────────
   DOSSIER REWRITE RULES
───────────────────────────────────────── */
function kaslek_dossier_rewrite_rules() {
	add_rewrite_rule(
		'^dossiers/([^/]+)/?$',
		'index.php?tag=$matches[1]',
		'top'
	);
	add_rewrite_rule(
		'^dossiers/([^/]+)/page/([0-9]+)/?$',
		'index.php?tag=$matches[1]&paged=$matches[2]',
		'top'
	);
}
add_action( 'init', 'kaslek_dossier_rewrite_rules' );

function kaslek_flush_rewrite_rules() {
	kaslek_dossier_rewrite_rules();
	kaslek_remove_category_base_rules();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'kaslek_flush_rewrite_rules' );

function kaslek_dossier_tag_link( $termlink, $term, $taxonomy ) {
	if ( $taxonomy === 'post_tag' ) {
		$dossier_slugs = [
			'belastingen-en-heffingen', 'consultants-en-externe-inhuur', 'defensie-en-militaire-steun',
			'energie-en-klimaat', 'infrastructuurprojecten', 'migratie-en-opvang',
			'onderwijs', 'overheid-ict-en-digitalisering', 'politiek-en-bestuur',
			'stikstof-en-natuur', 'subsidies-en-fondsen', 'veiligheid-en-handhaving',
			'zorg-en-sociaal',
		];
		if ( in_array( $term->slug, $dossier_slugs, true ) ) {
			return home_url( '/dossiers/' . $term->slug . '/' );
		}
	}
	return $termlink;
}
add_filter( 'term_link', 'kaslek_dossier_tag_link', 10, 3 );

function kaslek_dossier_canonical( $canonical ) {
	if ( is_tag() ) {
		$term = get_queried_object();
		$dossier_slugs = [
			'belastingen-en-heffingen', 'consultants-en-externe-inhuur', 'defensie-en-militaire-steun',
			'energie-en-klimaat', 'infrastructuurprojecten', 'migratie-en-opvang',
			'onderwijs', 'overheid-ict-en-digitalisering', 'politiek-en-bestuur',
			'stikstof-en-natuur', 'subsidies-en-fondsen', 'veiligheid-en-handhaving',
			'zorg-en-sociaal',
		];
		if ( $term && in_array( $term->slug, $dossier_slugs, true ) ) {
			return home_url( '/dossiers/' . $term->slug . '/' );
		}
	}
	return $canonical;
}
add_action( 'save_post', function( $post_id, $post ) {
	if ( ! isset( $post->post_type ) || $post->post_type !== 'post' ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) return;
	if ( ! in_array( get_post_status( $post_id ), [ 'publish', 'future', 'draft', 'pending', 'private' ], true ) ) return;

	$yoast_key = '_yoast_wpseo_metadesc';
	$existing  = get_post_meta( $post_id, $yoast_key, true );
	if ( is_string( $existing ) && trim( $existing ) !== '' ) return;

	$excerpt = get_post_field( 'post_excerpt', $post_id );
	if ( ! is_string( $excerpt ) || trim( $excerpt ) === '' ) return;

	$text = wp_strip_all_tags( strip_shortcodes( $excerpt ) );
	$text = html_entity_decode( $text, ENT_QUOTES, get_bloginfo( 'charset' ) );
	$text = preg_replace( '/\s+/u', ' ', trim( $text ) );

	if ( mb_strlen( $text ) > 145 ) {
		$text = rtrim( mb_substr( $text, 0, 145 ), " \t\n\r\0\x0B,.;:!?)\"]" ) . '…';
	}

	update_post_meta( $post_id, $yoast_key, $text );
}, 10, 2 );

add_filter( 'wpseo_canonical', 'kaslek_dossier_canonical' );
add_filter( 'wpseo_opengraph_url', 'kaslek_dossier_canonical' );

add_filter( 'wpseo_twitter_image', function( $image ) {
	$post_id = get_the_ID();
	if ( ! $post_id ) return $image;
	$thumbnail_id = get_post_thumbnail_id( $post_id );
	if ( ! $thumbnail_id ) return $image;
	$src = wp_get_attachment_image_src( $thumbnail_id, 'large' );
	return $src ? $src[0] : $image;
} );

/* ─────────────────────────────────────────
   CATEGORIE-URLs (geen /category/ prefix)
───────────────────────────────────────── */
add_filter( 'option_category_base', '__return_empty_string' );

function kaslek_remove_category_base_rules() {
	$categories = get_categories( [ 'hide_empty' => false ] );
	foreach ( $categories as $cat ) {
		add_rewrite_rule(
			'^' . $cat->slug . '/page/([0-9]+)/?$',
			'index.php?category_name=' . $cat->slug . '&paged=$matches[1]',
			'top'
		);
		add_rewrite_rule(
			'^' . $cat->slug . '/?$',
			'index.php?category_name=' . $cat->slug,
			'top'
		);
	}
}
add_action( 'init', 'kaslek_remove_category_base_rules' );

function kaslek_category_permalink( $termlink, $term, $taxonomy ) {
	if ( $taxonomy === 'category' ) {
		return home_url( '/' . $term->slug . '/' );
	}
	return $termlink;
}
add_filter( 'term_link', 'kaslek_category_permalink', 10, 3 );

/* ─────────────────────────────────────────
   KASLEK TOTAL
───────────────────────────────────────── */

define( 'KASLEK_TOTAL_YEAR', 2026 );

add_action( 'init', function () {
	register_post_meta( 'post', 'kaslek_amount_cents', [
		'type'          => 'integer',
		'single'        => true,
		'show_in_rest'  => true,
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	] );
} );

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'kaslek_amount_box', 'Bedrag (in euro\'s)', function ( $post ) {
		$cents = (int) get_post_meta( $post->ID, 'kaslek_amount_cents', true );
		$euros = $cents ? number_format( $cents / 100, 2, ',', '' ) : '';
		wp_nonce_field( 'kaslek_save_amount', 'kaslek_amount_nonce' );
		echo '<label for="kaslek_amount_eur">Bedrag (bijv. 12345,67):</label>';
		echo '<input type="text" id="kaslek_amount_eur" name="kaslek_amount_eur" value="' . esc_attr( $euros ) . '" style="width:100%;">';
	}, 'post', 'side', 'default' );
} );

add_action( 'save_post_post', function ( $post_id, $post, $update ) {
	if ( ! isset( $_POST['kaslek_amount_nonce'] ) || ! wp_verify_nonce( $_POST['kaslek_amount_nonce'], 'kaslek_save_amount' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
	if ( isset( $_POST['kaslek_amount_eur'] ) && $_POST['kaslek_amount_eur'] !== '' ) {
		$raw   = str_replace( [ '.', ' ' ], [ '', '' ], $_POST['kaslek_amount_eur'] );
		$raw   = str_replace( ',', '.', $raw );
		$cents = (int) round( floatval( $raw ) * 100 );
		update_post_meta( $post_id, 'kaslek_amount_cents', $cents );
	} else {
		delete_post_meta( $post_id, 'kaslek_amount_cents' );
	}
	kaslek_recompute_total();
}, 10, 3 );

function kaslek_recompute_total() {
	$q = new WP_Query( [
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'meta_key'       => 'kaslek_amount_cents',
		'meta_compare'   => 'EXISTS',
		'date_query'     => [ [ 'year' => (int) KASLEK_TOTAL_YEAR ] ],
		'no_found_rows'  => true,
		'fields'         => 'ids',
	] );
	$sum = 0;
	foreach ( $q->posts as $id ) {
		$c = (int) get_post_meta( $id, 'kaslek_amount_cents', true );
		if ( $c > 0 ) $sum += $c;
	}
	update_option( 'kaslek_total_cents', $sum, false );
	update_option( 'kaslek_total_updated', current_time( 'mysql' ), false );
	return $sum;
}

add_action( 'transition_post_status', function ( $new, $old, $post ) {
	if ( $post->post_type !== 'post' ) return;
	if ( in_array( $new, [ 'publish', 'draft', 'pending', 'trash' ], true ) ||
	     in_array( $old, [ 'publish', 'draft', 'pending', 'trash' ], true ) ) {
		kaslek_recompute_total();
	}
}, 10, 3 );

add_action( 'deleted_post', 'kaslek_recompute_total' );

add_action( 'wp', function () {
	if ( ! wp_next_scheduled( 'kaslek_hourly_recompute' ) ) {
		wp_schedule_event( time(), 'hourly', 'kaslek_hourly_recompute' );
	}
} );
add_action( 'kaslek_hourly_recompute', 'kaslek_recompute_total' );

add_shortcode( 'kaslek_total', function ( $atts ) {
	$a        = shortcode_atts( [ 'format' => '%s', 'decimals' => '0' ], $atts, 'kaslek_total' );
	$cents    = (int) get_option( 'kaslek_total_cents', 0 );
	$dec      = max( 0, (int) $a['decimals'] );
	$eur      = $cents / 100;
	$formatted = number_format( $eur, $dec, ',', '.' );
	$output   = sprintf( $a['format'], $formatted );
	return sprintf(
		'<span class="kaslek-total" data-total="%s" data-year="%d">%s</span>',
		esc_attr( number_format( $eur, 2, '.', '' ) ),
		(int) KASLEK_TOTAL_YEAR,
		esc_html( $output )
	);
} );

add_shortcode( 'kaslek_ad', function() {
	if ( get_option( 'kaslek_adsense_enabled', '1' ) !== '1' ) return '';
	$formats = get_option( 'kaslek_adsense_formats' );
	if ( $formats === false ) {
		// Nog niet geconfigureerd — hardcoded fallback
		return '<div class="kaslek-ad-shortcode"><ins class="adsbygoogle" style="display:block;width:100%" data-ad-client="ca-pub-6115912536653612" data-ad-slot="4772512111" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script></div>';
	}
	if ( empty( $formats ) ) return '';
	$code = trim( $formats[0]['code'] ?? '' );
	return $code ? '<div class="kaslek-ad-shortcode">' . $code . '</div>' : '';
} );

add_action( 'rest_api_init', function () {
	register_rest_route( 'kaslek/v1', '/total', [
		'methods'             => 'GET',
		'callback'            => function () {
			$cents = (int) get_option( 'kaslek_total_cents', 0 );
			return [
				'year'        => (int) KASLEK_TOTAL_YEAR,
				'total_cents' => $cents,
				'total_eur'   => number_format( $cents / 100, 2, '.', '' ),
				'updated'     => get_option( 'kaslek_total_updated' ),
			];
		},
		'permission_callback' => '__return_true',
	] );
} );

add_action( 'admin_init', function () {
	if ( isset( $_GET['kaslek_recompute'] ) && current_user_can( 'manage_options' ) && ( $_GET['page'] ?? '' ) === 'kaslek-total-tool' ) {
		check_admin_referer( 'kaslek_recompute' );
		kaslek_recompute_total();
		wp_redirect( admin_url( 'tools.php?page=kaslek-total-tool&recomputed=1' ) );
		exit;
	}
} );

add_action( 'admin_menu', function () {
	add_management_page( 'Kaslek Totaal', 'Kaslek Totaal', 'manage_options', 'kaslek-total-tool', function () {
		$url = wp_nonce_url( admin_url( 'tools.php?page=kaslek-total-tool&kaslek_recompute=1' ), 'kaslek_recompute' );
		if ( isset( $_GET['recomputed'] ) ) echo '<div class="updated notice"><p>Herberekend.</p></div>';
		$cents = (int) get_option( 'kaslek_total_cents', 0 );
		echo '<div class="wrap"><h1>Kaslek Totaal</h1>';
		echo '<p>Huidig totaal (' . (int) KASLEK_TOTAL_YEAR . '): <strong>' . number_format( $cents / 100, 0, ',', '.' ) . '</strong></p>';
		echo '<p><a class="button button-primary" href="' . esc_url( $url ) . '">Nu herberekenen</a></p></div>';
	} );
} );

add_filter( 'manage_post_posts_columns', function ( $columns ) {
	$new = [];
	foreach ( $columns as $key => $label ) {
		if ( $key === 'date' ) $new['kaslek_amount'] = 'Bedrag';
		$new[ $key ] = $label;
	}
	if ( ! isset( $new['kaslek_amount'] ) ) $new['kaslek_amount'] = 'Bedrag';
	return $new;
} );

add_action( 'manage_post_posts_custom_column', function ( $column, $post_id ) {
	if ( $column !== 'kaslek_amount' ) return;
	$cents = (int) get_post_meta( $post_id, 'kaslek_amount_cents', true );
	echo $cents > 0 ? esc_html( number_format( $cents / 100, 0, ',', '.' ) ) : '—';
}, 10, 2 );

add_filter( 'manage_edit-post_sortable_columns', function ( $sortable ) {
	$sortable['kaslek_amount'] = 'kaslek_amount';
	return $sortable;
} );

add_action( 'pre_get_posts', function ( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) return;
	if ( $query->get( 'orderby' ) === 'kaslek_amount' ) {
		$query->set( 'meta_key', 'kaslek_amount_cents' );
		$query->set( 'orderby', 'meta_value_num' );
	}
} );

add_action( 'admin_head-edit.php', function () {
	$screen = get_current_screen();
	if ( $screen && $screen->id === 'edit-post' ) {
		echo '<style>.column-kaslek_amount{width:110px;text-align:right}</style>';
	}
} );

add_action( 'wp_head', function() {
	echo '<meta name="referrer" content="no-referrer">' . "\n";
	echo '<meta name="theme-color" content="#1B3E5F">' . "\n";
} );

// gtag: laadt na eerste interactie of na 5 seconden
add_action( 'wp_footer', function() {
	?>
	<script>
	(function () {
		var loaded = false;
		var passiveOpts = { passive: true, once: true };

		function loadGtag() {
			if ( loaded ) return;
			loaded = true;
			removeListeners();
			var s = document.createElement( 'script' );
			s.async = true;
			s.src = 'https://www.googletagmanager.com/gtag/js?id=G-1DZQ6K8M6V';
			document.head.appendChild( s );
			s.onload = function () {
				window.dataLayer = window.dataLayer || [];
				function gtag(){ dataLayer.push( arguments ); }
				gtag( 'js', new Date() );
				gtag( 'config', 'G-1DZQ6K8M6V' );
			};
		}

		function removeListeners() {
			window.removeEventListener( 'scroll',      loadGtag, passiveOpts );
			window.removeEventListener( 'pointerdown', loadGtag, passiveOpts );
			window.removeEventListener( 'touchstart',  loadGtag, passiveOpts );
			window.removeEventListener( 'keydown',     loadGtag, false );
		}

		window.addEventListener( 'scroll',      loadGtag, passiveOpts );
		window.addEventListener( 'pointerdown', loadGtag, passiveOpts );
		window.addEventListener( 'touchstart',  loadGtag, passiveOpts );
		window.addEventListener( 'keydown',     loadGtag, { once: true } );
		window.setTimeout( loadGtag, 5000 );
	})();
	</script>
	<?php
} );

add_filter( 'wp_robots', function( array $robots ): array {
	$robots['max-image-preview'] = 'large';
	return $robots;
}, 999 );

// Niet-homepage: AdSense direct in <head>
add_action( 'wp_head', function() {
	if ( is_front_page() ) return;
	if ( get_option( 'kaslek_adsense_enabled', '1' ) !== '1' ) return;
	$default = '<meta name="google-adsense-account" content="ca-pub-6115912536653612">' . "\n" . '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6115912536653612" crossorigin="anonymous"></script>';
	$script  = get_option( 'kaslek_adsense_head_script', $default );
	if ( $script ) echo $script . "\n";
} );

// Homepage: AdSense laden via IntersectionObserver zodra eerste ad-slot in beeld komt
add_action( 'wp_footer', function() {
	if ( ! is_front_page() ) return;
	if ( get_option( 'kaslek_adsense_enabled', '1' ) !== '1' ) return;
	?>
	<script>
	(function () {
		var loaded = false;
		function loadAdsense() {
			if ( loaded ) return;
			loaded = true;
			var s = document.createElement( 'script' );
			s.async = true;
			s.src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6115912536653612';
			s.crossOrigin = 'anonymous';
			document.head.appendChild( s );
		}
		var firstAd = document.querySelector( 'ins.adsbygoogle' );
		if ( ! firstAd ) return;
		if ( 'IntersectionObserver' in window ) {
			var observer = new IntersectionObserver( function( entries ) {
				if ( entries[0].isIntersecting ) {
					loadAdsense();
					observer.disconnect();
				}
			}, { rootMargin: '200px 0px' } );
			observer.observe( firstAd );
		} else {
			[ 'scroll', 'click', 'touchstart' ].forEach( function( e ) {
				window.addEventListener( e, loadAdsense, { once: true, passive: true } );
			} );
		}
	})();
	</script>
	<?php
} );














/* ─────────────────────────────────────────
   IMAGE UPLOAD LIMIET
───────────────────────────────────────── */
add_filter( 'wp_generate_attachment_metadata', 'ft_limit_upload_width_1280', 10, 2 );
function ft_limit_upload_width_1280( $metadata, $attachment_id ) {
	$mime = get_post_mime_type( $attachment_id );
	if ( strpos( $mime, 'image/' ) !== 0 ) return $metadata;

	$file = get_attached_file( $attachment_id );
	if ( ! $file || ! file_exists( $file ) ) return $metadata;

	$editor = wp_get_image_editor( $file );
	if ( is_wp_error( $editor ) ) return $metadata;

	$size = $editor->get_size();
	if ( empty( $size['width'] ) || $size['width'] <= 1280 ) return $metadata;

	$editor->resize( 1280, null );
	$saved = $editor->save( $file );

	if ( ! is_wp_error( $saved ) ) {
		if ( isset( $saved['width'] ) )    $metadata['width']    = $saved['width'];
		if ( isset( $saved['height'] ) )   $metadata['height']   = $saved['height'];
		if ( isset( $saved['filesize'] ) ) $metadata['filesize'] = $saved['filesize'];
	}

	return $metadata;
}

/* ─────────────────────────────────────────
   FALLBACK NAV
───────────────────────────────────────── */
function kaslek_fallback_nav() {
	echo '<ul>';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">Home</a></li>';
	wp_list_pages( [ 'title_li' => '' ] );
	echo '</ul>';
}

/* ─────────────────────────────────────────
   AJAX: INFINITE SCROLL
───────────────────────────────────────── */
function kaslek_infinite_scroll_handler() {
	check_ajax_referer( 'kaslek_infinite_scroll', 'nonce' );

	$paged = isset( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1;

	$args = [
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 6,
		'author__in'     => [ 2, 4, 5 ],
		'offset'         => 14 + ( ( $paged - 1 ) * 6 ),
	];

	$query = new WP_Query( $args );

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			get_template_part( 'template-parts/card', 'horizontal' );
		}
		wp_reset_postdata();
	}

	wp_die();
}
add_action( 'wp_ajax_kaslek_infinite_scroll',        'kaslek_infinite_scroll_handler' );
add_action( 'wp_ajax_nopriv_kaslek_infinite_scroll', 'kaslek_infinite_scroll_handler' );

/* ─────────────────────────────────────────
   AJAX: ARCHIVE INFINITE SCROLL
───────────────────────────────────────── */
function kaslek_archive_scroll_handler() {
	check_ajax_referer( 'kaslek_infinite_scroll', 'nonce' );

	$paged    = absint( $_POST['paged'] ?? 1 );
	$type     = sanitize_key( $_POST['type'] ?? '' );
	$term     = sanitize_text_field( $_POST['term'] ?? '' );
	$author   = absint( $_POST['author'] ?? 0 );
	$per_page = absint( $_POST['per_page'] ?? 9 );
	$exclude  = array_filter( array_map( 'absint', explode( ',', $_POST['exclude'] ?? '' ) ) );

	$args = [
		'posts_per_page' => $per_page,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
		'paged'          => $paged,
	];

	if ( ! empty( $exclude ) ) {
		$args['post__not_in'] = $exclude;
	}

	if ( $type === 'tag' && $term ) {
		$args['tag_slug__in'] = [ $term ];
	} elseif ( $type === 'category' && $term ) {
		$args['category_name'] = $term;
	} elseif ( $type === 'author' && $author ) {
		$args['author'] = $author;
	} else {
		wp_die();
	}

	$q = new WP_Query( $args );

	if ( ! $q->have_posts() ) {
		wp_die();
	}

	$posts = [];
	while ( $q->have_posts() ) {
		$q->the_post();
		$posts[] = get_post();
	}
	wp_reset_postdata();

	$rows = array_chunk( $posts, 3 );
	foreach ( $rows as $row ) {
		set_query_var( 'archive_row_posts', $row );
		get_template_part( 'template-parts/archive', 'row' );
	}

	wp_die();
}
add_action( 'wp_ajax_kaslek_archive_scroll',        'kaslek_archive_scroll_handler' );
add_action( 'wp_ajax_nopriv_kaslek_archive_scroll', 'kaslek_archive_scroll_handler' );



/* ─────────────────────────────────────────
   POLL
───────────────────────────────────────── */
function kaslek_enqueue_poll_script() {
	if ( ! is_singular( 'post' ) ) return;
	wp_enqueue_script(
		'kaslek-poll',
		get_template_directory_uri() . '/assets/js/poll.js',
		[ 'jquery' ],
		'1.0.3',
		true
	);
	wp_localize_script( 'kaslek-poll', 'ftPollData', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'kaslek_enqueue_poll_script' );

add_action( 'wp_ajax_ft_poll_vote',        'ft_ajax_poll_vote' );
add_action( 'wp_ajax_nopriv_ft_poll_vote', 'ft_ajax_poll_vote' );

function ft_ajax_poll_vote() {
	$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
	$vote    = isset( $_POST['vote'] )    ? sanitize_text_field( $_POST['vote'] ) : '';
	$nonce   = isset( $_POST['nonce'] )   ? sanitize_text_field( $_POST['nonce'] ) : '';

	if ( ! $post_id || ! in_array( $vote, [ 'yes', 'no' ], true ) || ! wp_verify_nonce( $nonce, 'ft_poll_vote_action_' . $post_id ) ) {
		wp_send_json( [ 'success' => false ] );
		wp_die();
	}

	if ( ! isset( $_COOKIE[ 'ft_poll_' . $post_id ] ) ) {
		$meta_key = ( $vote === 'yes' ) ? '_ft_poll_yes' : '_ft_poll_no';
		$current  = (int) get_post_meta( $post_id, $meta_key, true );
		update_post_meta( $post_id, $meta_key, $current + 1 );
		setcookie( 'ft_poll_' . $post_id, 'voted', [
			'expires'  => time() + ( 86400 * 30 ),
			'path'     => '/',
			'samesite' => 'Lax',
		] );
		$_COOKIE[ 'ft_poll_' . $post_id ] = 'voted';
	}

	wp_send_json( [ 'success' => true, 'html' => ft_render_poll_results( $post_id ) ] );
}

function ft_render_poll_results( $post_id ) {
	$yes   = (int) get_post_meta( $post_id, '_ft_poll_yes', true );
	$no    = (int) get_post_meta( $post_id, '_ft_poll_no', true );
	$total = $yes + $no;

	$yes_pct = $total > 0 ? round( ( $yes / $total ) * 100 ) : 0;
	$no_pct  = $total > 0 ? round( ( $no  / $total ) * 100 ) : 0;

	ob_start(); ?>
	<div class="ft-poll-results">
		<div class="ft-poll-bar-row">
			<span class="ft-poll-label">Ja</span>
			<div class="ft-poll-bar"><div class="ft-poll-bar-fill ft-poll-bar-yes" style="width:<?php echo esc_attr( $yes_pct ); ?>%;"></div></div>
			<span class="ft-poll-percent"><?php echo esc_html( $yes_pct ); ?>%</span>
		</div>
		<div class="ft-poll-bar-row">
			<span class="ft-poll-label">Nee</span>
			<div class="ft-poll-bar"><div class="ft-poll-bar-fill ft-poll-bar-no" style="width:<?php echo esc_attr( $no_pct ); ?>%;"></div></div>
			<span class="ft-poll-percent"><?php echo esc_html( $no_pct ); ?>%</span>
		</div>
		<p class="ft-poll-voters-count">Aantal stemmers: <?php echo intval( $total ); ?></p>
	</div>
	<?php
	return ob_get_clean();
}

function ft_render_post_poll( $post_id ) {
	$has_voted = isset( $_COOKIE[ 'ft_poll_' . $post_id ] );
	$nonce     = wp_create_nonce( 'ft_poll_vote_action_' . $post_id );
	ob_start(); ?>
	<div class="ft-poll-wrapper" id="ft-poll-wrapper-<?php echo esc_attr( $post_id ); ?>">
		<h3 class="ft-poll-title">Is dit een nuttige geldbesteding?</h3>
		<div class="ft-poll-buttons" id="ft-poll-buttons-<?php echo esc_attr( $post_id ); ?>"<?php echo $has_voted ? ' style="display:none;"' : ''; ?>>
			<button type="button" class="ft-poll-btn"
				data-post-id="<?php echo esc_attr( $post_id ); ?>"
				data-vote="yes"
				data-nonce="<?php echo esc_attr( $nonce ); ?>">Ja</button>
			<button type="button" class="ft-poll-btn"
				data-post-id="<?php echo esc_attr( $post_id ); ?>"
				data-vote="no"
				data-nonce="<?php echo esc_attr( $nonce ); ?>">Nee</button>
		</div>
		<div id="ft-poll-results-<?php echo esc_attr( $post_id ); ?>"<?php echo $has_voted ? '' : ' style="display:none;"'; ?>>
			<?php if ( $has_voted ) echo ft_render_poll_results( $post_id ); ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

add_filter( 'the_content', function( $content ) {
	if ( is_singular( 'post' ) && in_the_loop() && is_main_query() ) {
		$content .= ft_render_post_poll( get_the_ID() );
	}
	return $content;
} );

function ft_replace_emdash_with_dash( $content ) {
	return str_replace( "\u{2014}", '-', $content );
}
add_filter( 'the_content',  'ft_replace_emdash_with_dash' );
add_filter( 'widget_text',  'ft_replace_emdash_with_dash' );


/* ─────────────────────────────────────────
   TELEGRAM
───────────────────────────────────────── */
if ( ! defined( 'TELEGRAM_BOT_TOKEN' ) )     define( 'TELEGRAM_BOT_TOKEN',     '8592703253:AAGoVKMjmWAtx_0yE-wCLKzslj9NIzYKfAs' );
if ( ! defined( 'TELEGRAM_CHAT_ID' ) )       define( 'TELEGRAM_CHAT_ID',       '-1003153108932' );
if ( ! defined( 'TELEGRAM_SENT_META_KEY' ) ) define( 'TELEGRAM_SENT_META_KEY', '_wptelegram_sent_on_first_publish' );

add_action( 'admin_menu', function() {
	add_menu_page( 'Telegram', 'Telegram', 'manage_options', 'kaslek-telegram', 'kaslek_telegram_settings_page', 'dashicons-share', 3 );
} );

function kaslek_telegram_settings_page() {
	if ( isset( $_POST['kaslek_telegram_nonce'] ) && wp_verify_nonce( $_POST['kaslek_telegram_nonce'], 'kaslek_telegram_save' ) ) {
		update_option( 'kaslek_telegram_enabled', isset( $_POST['telegram_enabled'] ) ? '1' : '0' );
		echo '<div class="updated notice"><p>Instellingen opgeslagen.</p></div>';
	}
	$enabled = get_option( 'kaslek_telegram_enabled', '1' );
	?>
	<div class="wrap">
		<h1>Telegram notificaties</h1>
		<form method="post">
			<?php wp_nonce_field( 'kaslek_telegram_save', 'kaslek_telegram_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th>Notificaties</th>
					<td>
						<label class="kaslek-toggle">
							<input type="checkbox" name="telegram_enabled" value="1" <?php checked( $enabled, '1' ); ?>>
							<span class="kaslek-toggle-slider"></span>
						</label>
						<p class="description" style="margin-top:8px;">Aan: nieuwe posts worden naar Telegram gestuurd. Uit: geen verzending.</p>
					</td>
				</tr>
			</table>
			<?php submit_button( 'Opslaan' ); ?>
		</form>
	</div>
	<style>
		.kaslek-toggle { position:relative; display:inline-block; width:50px; height:26px; }
		.kaslek-toggle input { opacity:0; width:0; height:0; }
		.kaslek-toggle-slider { position:absolute; cursor:pointer; inset:0; background:#ccc; border-radius:26px; transition:.3s; }
		.kaslek-toggle-slider:before { content:''; position:absolute; width:20px; height:20px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.3s; }
		.kaslek-toggle input:checked + .kaslek-toggle-slider { background:#2271b1; }
		.kaslek-toggle input:checked + .kaslek-toggle-slider:before { transform:translateX(24px); }
	</style>
	<?php
}

add_action( 'transition_post_status', 'wptelegram_send_on_first_publish', 10, 3 );

function wptelegram_send_on_first_publish( $new_status, $old_status, $post ) {
	if ( get_option( 'kaslek_telegram_enabled', '1' ) !== '1' ) return;
	if ( $new_status !== 'publish' || $old_status === 'publish' ) return;
	if ( ! is_a( $post, 'WP_Post' ) || $post->post_type !== 'post' ) return;
	if ( get_post_meta( $post->ID, TELEGRAM_SENT_META_KEY, true ) ) return;

	$post_date_gmt    = strtotime( $post->post_date_gmt . ' +0000' );
	$current_time_gmt = gmdate( 'U' );
	if ( $post_date_gmt > ( $current_time_gmt + 30 ) ) return;

	$token   = trim( TELEGRAM_BOT_TOKEN );
	$chat_id = trim( TELEGRAM_CHAT_ID );
	if ( empty( $token ) || $token === 'VUL_HIER_JE_BOT_TOKEN_IN' ) return;

	$content  = $post->post_content;
	$full_tag = "\x3C\x21\x2D\x2D" . "more" . "\x2D\x2D\x3E";

	if ( ! empty( $content ) && strpos( $content, $full_tag ) !== false ) {
		$parts  = explode( $full_tag, $content );
		$teaser = $parts[0];
	} else {
		$teaser = wp_trim_words( $content, 45 );
	}

	$teaser = wp_strip_all_tags( $teaser );
	$title  = get_the_title( $post->ID );
	$url    = get_permalink( $post->ID );

	$text  = '<b>' . esc_html( $title ) . "</b>\n\n";
	$text .= '<i>' . esc_html( $teaser ) . "</i>\n\n";
	$text .= "<a href='" . esc_url( $url ) . "'>Lees het volledige bericht</a>";

	wp_remote_post( "https://api.telegram.org/bot{$token}/sendMessage", [
		'method'   => 'POST',
		'timeout'  => 10,
		'blocking' => false,
		'headers'  => [ 'Content-Type' => 'application/json' ],
		'body'     => json_encode( [ 'chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'HTML' ] ),
	] );

	update_post_meta( $post->ID, TELEGRAM_SENT_META_KEY, '1' );
}

/* ─────────────────────────────────────────
   ADS ROTATOR
───────────────────────────────────────── */
if ( ! class_exists( 'KasLek_Ads_Rotator' ) ) {
final class KasLek_Ads_Rotator {
	const CPT              = 'kaslek_ad';
	const META_CATEGORY    = '_kaslek_ad_category';
	const META_URL         = '_kaslek_ad_url';
	const META_HTML        = '_kaslek_ad_html';
	const META_IMPRESSIONS = '_kaslek_ad_impressions';
	const META_CLICKS      = '_kaslek_ad_clicks';

	public static function init() {
		self::register_cpt();
		add_action( 'add_meta_boxes', [ __CLASS__, 'add_metaboxes' ] );
		add_action( 'save_post_' . self::CPT, [ __CLASS__, 'save_ad_metabox' ], 10, 2 );
		add_action( 'admin_footer', [ __CLASS__, 'admin_footer_js' ] );
		add_action( 'template_redirect', [ __CLASS__, 'handle_click_redirect' ] );
		add_filter( 'manage_edit-' . self::CPT . '_columns', [ __CLASS__, 'admin_columns' ] );
		add_action( 'manage_' . self::CPT . '_posts_custom_column', [ __CLASS__, 'admin_column_values' ], 10, 2 );
		add_action( 'admin_menu', [ __CLASS__, 'add_report_menu' ] );
	}

	public static function register_cpt() {
		register_post_type( self::CPT, [
			'labels' => [
				'name'          => 'Ads',
				'singular_name' => 'Ad',
				'menu_name'     => 'Ads Manager',
				'all_items'     => 'Alle Ads',
				'add_new'       => 'Nieuwe Ad',
				'add_new_item'  => 'Nieuwe Ad toevoegen',
			],
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'menu_position'   => 2,
			'menu_icon'       => 'dashicons-megaphone',
			'supports'        => [ 'title', 'thumbnail', 'author' ],
			'capability_type' => 'post',
			'map_meta_cap'    => true,
			'hierarchical'    => false,
			'query_var'       => true,
		] );
	}

	public static function add_metaboxes() {
		add_meta_box( 'kaslek_settings', 'Ad Instellingen', [ __CLASS__, 'render_metabox' ], self::CPT, 'normal', 'high' );
	}

	public static function render_metabox( $post ) {
		wp_nonce_field( 'kaslek_save', 'kaslek_nonce' );
		$cat  = get_post_meta( $post->ID, self::META_CATEGORY, true ) ?: 'eigen';
		$url  = get_post_meta( $post->ID, self::META_URL, true );
		$html = get_post_meta( $post->ID, self::META_HTML, true );
		?>
		<div style="margin-bottom:15px;">
			<label><strong>Type Ad:</strong></label><br>
			<select name="kaslek_cat" id="kaslek_cat" style="width:100%; max-width:400px;">
				<option value="eigen"     <?php selected( $cat, 'eigen' ); ?>>Eigen Ad (Afbeelding + Link)</option>
				<option value="affiliate" <?php selected( $cat, 'affiliate' ); ?>>Affiliate (HTML/Bol.com)</option>
			</select>
		</div>
		<div id="section_url" style="margin-bottom:15px;">
			<label><strong>Doel URL:</strong></label><br>
			<input type="url" name="kaslek_url" value="<?php echo esc_attr( $url ); ?>" style="width:100%">
		</div>
		<div id="section_html">
			<label><strong>HTML Code:</strong></label><br>
			<textarea name="kaslek_html" style="width:100%;height:150px;font-family:monospace;"><?php echo esc_textarea( $html ); ?></textarea>
		</div>
		<?php
	}

	public static function admin_footer_js() {
		$screen = get_current_screen();
		if ( ! $screen || $screen->post_type !== self::CPT ) return;
		?>
		<script>jQuery(document).ready(function($){
			function t(){
				var v=$('#kaslek_cat').val();
				if(v==='eigen'){$('#section_url').show();$('#section_html').hide();}
				else{$('#section_url').hide();$('#section_html').show();}
			}
			$('#kaslek_cat').change(t); t();
		});</script>
		<?php
	}

	public static function save_ad_metabox( $post_id ) {
		if ( ! isset( $_POST['kaslek_nonce'] ) || ! wp_verify_nonce( $_POST['kaslek_nonce'], 'kaslek_save' ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( isset( $_POST['kaslek_cat'] ) )  update_post_meta( $post_id, self::META_CATEGORY, sanitize_text_field( $_POST['kaslek_cat'] ) );
		if ( isset( $_POST['kaslek_url'] ) )  update_post_meta( $post_id, self::META_URL, esc_url_raw( $_POST['kaslek_url'] ) );
		if ( isset( $_POST['kaslek_html'] ) ) update_post_meta( $post_id, self::META_HTML, $_POST['kaslek_html'] );
	}

	public static function render_shortcode() {
		$ads = get_posts( [ 'post_type' => self::CPT, 'post_status' => 'publish', 'posts_per_page' => -1 ] );

		$idx = [ 'eigen' => [], 'affiliate' => [] ];
		foreach ( $ads as $ad ) {
			$c = get_post_meta( $ad->ID, self::META_CATEGORY, true ) ?: 'eigen';
			if ( isset( $idx[ $c ] ) ) $idx[ $c ][] = $ad->ID;
		}

		$avail = [];
		foreach ( [ 'eigen' => 50, 'affiliate' => 50 ] as $k => $w ) {
			if ( ! empty( $idx[ $k ] ) ) $avail[ $k ] = $w;
		}
		if ( ! $avail ) return '';

		$state  = get_option( 'kaslek_rr_state', [ 'eigen' => 0, 'affiliate' => 0 ] );
		$chosen = array_key_first( $avail );
		$max    = -999999;
		foreach ( $avail as $k => $w ) {
			$state[ $k ] = ( isset( $state[ $k ] ) ? $state[ $k ] : 0 ) + $w;
			if ( $state[ $k ] > $max ) { $max = $state[ $k ]; $chosen = $k; }
		}
		$state[ $chosen ] -= 100;
		update_option( 'kaslek_rr_state', $state );

		$ad_id = $idx[ $chosen ][ array_rand( $idx[ $chosen ] ) ];

		if ( $chosen === 'eigen' ) {
			$current_impr = (int) get_post_meta( $ad_id, self::META_IMPRESSIONS, true );
			update_post_meta( $ad_id, self::META_IMPRESSIONS, $current_impr + 1 );
			$url = add_query_arg( [ 'kaslek_click' => $ad_id ], home_url( '/' ) );
			return sprintf( '<a href="%s" target="_blank" rel="sponsored">%s</a>', esc_url( $url ), get_the_post_thumbnail( $ad_id, 'full' ) );
		}

		// Affiliate
		$raw_html = get_post_meta( $ad_id, self::META_HTML, true );
		preg_match_all( '/<script\b[^>]*>(.*?)<\/script>/is', $raw_html, $inline_matches );
		$ins_only = preg_replace( '/<script\b[^>]*>.*?<\/script>/is', '', $raw_html );
		foreach ( $inline_matches[1] as $js ) {
			$js = trim( $js );
			if ( $js ) {
				add_action( 'wp_footer', function() use ( $js ) {
					echo '<script>' . $js . '</script>' . "\n";
				}, 5 );
			}
		}
		return '<div class="kaslek-ad-external">' . $ins_only . '</div>';
	}

	public static function handle_click_redirect() {
		if ( isset( $_GET['kaslek_click'] ) ) {
			$id             = absint( $_GET['kaslek_click'] );
			$current_clicks = (int) get_post_meta( $id, self::META_CLICKS, true );
			update_post_meta( $id, self::META_CLICKS, $current_clicks + 1 );
			$target = get_post_meta( $id, self::META_URL, true );
			if ( $target ) { wp_redirect( esc_url_raw( $target ) ); exit; }
		}
	}

	public static function admin_columns( $cols ) {
		return [
			'cb'      => $cols['cb'],
			'title'   => $cols['title'],
			'ad_type' => 'Type',
			'stats'   => 'Impressies / Kliks',
			'author'  => $cols['author'],
			'date'    => $cols['date'],
		];
	}

	public static function admin_column_values( $col, $post_id ) {
		if ( $col === 'ad_type' ) {
			echo esc_html( ucfirst( get_post_meta( $post_id, self::META_CATEGORY, true ) ?: 'eigen' ) );
		}
		if ( $col === 'stats' ) {
			$impr  = (int) get_post_meta( $post_id, self::META_IMPRESSIONS, true );
			$click = (int) get_post_meta( $post_id, self::META_CLICKS, true );
			echo '<strong>' . $impr . '</strong> / <strong>' . $click . '</strong>';
		}
	}

	public static function add_report_menu() {
		add_submenu_page( 'edit.php?post_type=' . self::CPT, 'Rapportage', 'Rapportage', 'edit_posts', 'kaslek-report', [ __CLASS__, 'render_report_page' ] );
	}

	public static function render_report_page() {
		$ads   = get_posts( [ 'post_type' => self::CPT, 'posts_per_page' => -1 ] );
		$stats = [];
		foreach ( $ads as $ad ) {
			$author = get_the_author_meta( 'display_name', $ad->post_author );
			if ( ! isset( $stats[ $author ] ) ) $stats[ $author ] = [ 'impr' => 0, 'click' => 0 ];
			$stats[ $author ]['impr']  += (int) get_post_meta( $ad->ID, self::META_IMPRESSIONS, true );
			$stats[ $author ]['click'] += (int) get_post_meta( $ad->ID, self::META_CLICKS, true );
		}
		?>
		<div class="wrap">
			<h1>Rapportage per auteur (Eigen Ads)</h1>
			<table class="widefat striped" style="margin-top:20px;">
				<thead>
					<tr>
						<th>Auteur</th>
						<th>Totaal Impressies</th>
						<th>Totaal Kliks</th>
						<th>CTR</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $stats as $name => $d ) :
					$ctr = $d['impr'] > 0 ? round( ( $d['click'] / $d['impr'] ) * 100, 2 ) : 0; ?>
					<tr>
						<td><strong><?php echo esc_html( $name ); ?></strong></td>
						<td><?php echo (int) $d['impr']; ?></td>
						<td><?php echo (int) $d['click']; ?></td>
						<td><?php echo esc_html( $ctr ); ?>%</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}

}
add_action( 'init', [ KasLek_Ads_Rotator::class, 'init' ] );

/* ─────────────────────────────────────────
   GROK LINK AUDIT
───────────────────────────────────────── */
add_action( 'save_post', 'ferdi_schedule_grok_link_audit', 10, 3 );
function ferdi_schedule_grok_link_audit( $post_id, $post, $update ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( wp_is_post_revision( $post_id ) ) return;
	if ( $post->post_status !== 'draft' ) return;
	wp_clear_scheduled_hook( 'ferdi_run_delayed_link_check', [ $post_id ] );
	wp_schedule_single_event( time() + 1, 'ferdi_run_delayed_link_check', [ $post_id ] );
}

add_action( 'ferdi_run_delayed_link_check', 'ferdi_execute_grok_link_audit' );
function ferdi_execute_grok_link_audit( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post ) return;

	preg_match_all( '/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $post->post_content, $matches );
	$links         = array_unique( $matches['href'] );
	$audit_results = [];

	foreach ( $links as $link ) {
		if ( strpos( $link, 'http' ) !== 0 || strpos( $link, get_site_url() ) === 0 ) continue;

		$response = wp_remote_head( $link, [
			'timeout'     => 10,
			'redirection' => 5,
			'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
		] );
		$code = wp_remote_retrieve_response_code( $response );

		if ( in_array( $code, [ 403, 405 ], true ) ) {
			$response = wp_remote_get( $link, [
				'timeout'    => 10,
				'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
			] );
			$code = wp_remote_retrieve_response_code( $response );
		}

		$audit_results[ $link ] = $code ?: 'Conn Error';
	}

	delete_post_meta( $post_id, '_link_audit_manual_ok' );
	update_post_meta( $post_id, '_link_audit_results', $audit_results );
	update_post_meta( $post_id, '_link_audit_last_run', current_time( 'mysql' ) );
}

add_action( 'wp_ajax_ferdi_mark_links_ok', 'ferdi_ajax_mark_links_ok' );
function ferdi_ajax_mark_links_ok() {
	if ( ! isset( $_POST['post_id'], $_POST['nonce'] ) ) wp_send_json_error( 'Ongeldige aanvraag.' );
	$post_id = intval( $_POST['post_id'] );
	if ( ! wp_verify_nonce( $_POST['nonce'], 'ferdi_mark_ok_' . $post_id ) ) wp_send_json_error( 'Beveiligingscontrole mislukt.' );
	if ( ! current_user_can( 'edit_post', $post_id ) ) wp_send_json_error( 'Geen rechten.' );
	update_post_meta( $post_id, '_link_audit_manual_ok', 1 );
	update_post_meta( $post_id, '_link_audit_manual_ok_by', get_current_user_id() );
	update_post_meta( $post_id, '_link_audit_manual_ok_at', current_time( 'mysql' ) );
	delete_post_meta( $post_id, '_aivd_grok_blocked' );
	wp_send_json_success( 'Gemarkeerd als OK.' );
}

add_filter( 'manage_post_posts_columns', 'ferdi_add_grok_link_column' );
function ferdi_add_grok_link_column( $columns ) {
	$columns['grok_links'] = 'Grok-links';
	return $columns;
}

add_action( 'manage_post_posts_custom_column', 'ferdi_fill_grok_link_column', 10, 2 );
function ferdi_fill_grok_link_column( $column, $post_id ) {
	if ( $column !== 'grok_links' ) return;
	$results   = get_post_meta( $post_id, '_link_audit_results', true );
	$last_run  = get_post_meta( $post_id, '_link_audit_last_run', true );
	$manual_ok = get_post_meta( $post_id, '_link_audit_manual_ok', true );
	if ( empty( $last_run ) ) return;
	if ( $manual_ok ) {
		echo '<strong style="color:#46b450;background:#e7f7ed;padding:2px 8px;border-radius:4px;">OK*</strong>';
		return;
	}
	$has_errors = false;
	if ( is_array( $results ) ) {
		foreach ( $results as $code ) {
			if ( ! in_array( $code, [ 200, 201, 301, 302 ], true ) ) { $has_errors = true; break; }
		}
	}
	if ( ! $has_errors && ! empty( $results ) ) {
		echo '<strong style="color:#46b450;background:#e7f7ed;padding:2px 8px;border-radius:4px;">OK</strong>';
	} elseif ( $has_errors ) {
		$blocked = get_post_meta( $post_id, '_aivd_grok_blocked', true );
		echo '<strong style="color:#dc3232;background:#fbeaea;padding:2px 8px;border-radius:4px;">Let op</strong>';
		if ( $blocked ) echo ' <strong style="color:#fff;background:#dc3232;padding:2px 6px;border-radius:4px;font-size:11px;">&#9888; geblokkeerd</strong>';
	} else {
		echo '<span style="color:#ccc;">–</span>';
	}
}

add_action( 'add_meta_boxes', 'ferdi_add_grok_audit_metabox' );
function ferdi_add_grok_audit_metabox() {
	$post_id   = get_the_ID();
	$results   = get_post_meta( $post_id, '_link_audit_results', true );
	$manual_ok = get_post_meta( $post_id, '_link_audit_manual_ok', true );
	$title     = 'Grok Link Audit';
	if ( ! $manual_ok && is_array( $results ) ) {
		foreach ( $results as $code ) {
			if ( ! in_array( $code, [ 200, 201, 301, 302 ], true ) ) {
				$title = '<span style="color:#dc3232;">&#10069;</span> ' . $title;
				break;
			}
		}
	}
	add_meta_box( 'ferdi_grok_link_errors', $title, 'ferdi_render_grok_audit_metabox', 'post', 'normal', 'high' );
}

function ferdi_render_grok_audit_metabox( $post ) {
	$results      = get_post_meta( $post->ID, '_link_audit_results', true );
	$manual_ok    = get_post_meta( $post->ID, '_link_audit_manual_ok', true );
	$ok_by        = get_post_meta( $post->ID, '_link_audit_manual_ok_by', true );
	$ok_at        = get_post_meta( $post->ID, '_link_audit_manual_ok_at', true );
	$grok_blocked = get_post_meta( $post->ID, '_aivd_grok_blocked', true );

	if ( $grok_blocked && ! $manual_ok ) {
		echo '<div style="background:#fbeaea;border-left:4px solid #dc3232;padding:10px 14px;margin-bottom:14px;">';
		echo '<strong style="color:#dc3232;">&#9888; Geblokkeerd door silent observer</strong><br>';
		echo '<span style="font-size:12px;color:#333;">Dit artikel kan niet live gaan zolang er links met status <em>Let op</em> zijn. Beoordeel de links hieronder en klik op <strong>Markeer als OK</strong> om vrij te geven.</span>';
		echo '</div>';
	}

	$errors = [];
	if ( is_array( $results ) ) {
		foreach ( $results as $link => $code ) {
			if ( ! in_array( $code, [ 200, 201, 301, 302 ], true ) ) $errors[ $link ] = $code;
		}
	}

	$nonce = wp_create_nonce( 'ferdi_mark_ok_' . $post->ID );

	if ( $manual_ok ) {
		$user_info = $ok_by ? get_userdata( $ok_by ) : null;
		$username  = $user_info ? esc_html( $user_info->display_name ) : 'onbekend';
		echo '<p style="color:#46b450;">&#10003; Handmatig gemarkeerd als OK door <strong>' . $username . '</strong> op ' . esc_html( $ok_at ) . '.</p>';
		echo '<p style="color:#999;font-size:12px;">Bij de volgende auditrun wordt deze markering automatisch gereset.</p>';
		return;
	}

	if ( empty( $errors ) ) {
		echo '<p style="color:#46b450;">&#10003; Alle links in dit concept werken correct.</p>';
		return;
	}

	echo '<p style="color:#dc3232;font-weight:bold;">&#9888; Sommige links konden niet worden gevalideerd.</p>';
	echo '<table class="wp-list-table widefat fixed striped" style="margin-top:10px;">';
	echo '<thead><tr><th>Link URL</th><th>Linktekst</th><th>Code</th><th>Actie</th></tr></thead><tbody>';

	foreach ( $errors as $url => $code ) {
		$link_text = 'Onbekend';
		if ( preg_match( '/<a[^>]+href=[\'"]' . preg_quote( $url, '/' ) . '[\'"][^>]*>(.*?)<\/a>/i', $post->post_content, $m ) ) {
			$link_text = strip_tags( $m[1] );
		}
		$search_terms = str_replace( [ 'https://', 'http://', 'www.', '.nl', '.com', '.de', '.eu', '.fr', '-', '_', '/', '?', '=' ], ' ', $url );
		$search_terms = preg_replace( '/utm_.*?($|\s)/', '', $search_terms );
		$google_url   = 'https://www.google.com/search?q=' . urlencode( trim( preg_replace( '/\s+/', ' ', $search_terms ) ) );
		echo '<tr>';
		echo '<td><a href="' . esc_url( $url ) . '" target="_blank" rel="noreferrer">' . esc_html( $url ) . '</a></td>';
		echo '<td>' . esc_html( $link_text ) . '</td>';
		echo '<td>' . esc_html( $code ) . '</td>';
		echo '<td><a href="' . esc_url( $google_url ) . '" target="_blank" class="button button-secondary">Opzoeken</a></td>';
		echo '</tr>';
	}

	echo '</tbody></table>';
	echo '<p style="margin-top:16px;">';
	echo '<button type="button" id="ferdi-mark-ok-btn" class="button button-primary" data-post-id="' . esc_attr( $post->ID ) . '" data-nonce="' . esc_attr( $nonce ) . '">Markeer als OK</button>';
	echo '<span id="ferdi-mark-ok-msg" style="margin-left:12px;display:none;"></span>';
	echo '</p>';
	?>
	<script>
	(function() {
		var btn = document.getElementById('ferdi-mark-ok-btn');
		if (!btn) return;
		btn.addEventListener('click', function() {
			btn.disabled = true;
			btn.textContent = 'Bezig...';
			var data = new FormData();
			data.append('action', 'ferdi_mark_links_ok');
			data.append('post_id', btn.dataset.postId);
			data.append('nonce', btn.dataset.nonce);
			fetch(ajaxurl, { method: 'POST', body: data })
				.then(function(r) { return r.json(); })
				.then(function(json) {
					var msg = document.getElementById('ferdi-mark-ok-msg');
					if (json.success) {
						msg.style.color = '#46b450';
						msg.textContent = 'Gemarkeerd als OK. Herlaad de pagina om de status te zien.';
					} else {
						msg.style.color = '#dc3232';
						msg.textContent = 'Fout: ' + json.data;
						btn.disabled = false;
						btn.textContent = 'Markeer als OK';
					}
					msg.style.display = 'inline';
				})
				.catch(function() {
					var msg = document.getElementById('ferdi-mark-ok-msg');
					msg.style.color = '#dc3232';
					msg.textContent = 'Verbindingsfout. Probeer opnieuw.';
					msg.style.display = 'inline';
					btn.disabled = false;
					btn.textContent = 'Markeer als OK';
				});
		});
	})();
	</script>
	<?php
}

add_action( 'admin_head', 'ferdi_grok_column_style' );
function ferdi_grok_column_style() {
	echo '<style>.column-grok_links{width:110px!important;text-align:center}</style>';
}

/* ─────────────────────────────────────────
   QUOTE CHECK
───────────────────────────────────────── */
add_action( 'save_post', 'ferdi_run_quote_check', 20, 3 );
function ferdi_run_quote_check( $post_id, $post, $update ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( wp_is_post_revision( $post_id ) ) return;
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;

	$content = isset( $_POST['content'] ) ? wp_kses_post( wp_unslash( $_POST['content'] ) ) : $post->post_content;

	preg_match_all( '/<[^>]*>(*SKIP)(*FAIL)|(?:&#8220;|\x{201C}|")([^"&#\x{201D}]+)(?:&#8221;|\x{201D}|")/u', $content, $matches );

	$quotes = array_unique( array_map( 'trim', $matches[1] ) );
	$quotes = array_filter( $quotes );

	if ( empty( $quotes ) ) {
		delete_post_meta( $post_id, '_quote_check_quotes' );
		delete_post_meta( $post_id, '_quote_check_status' );
		return;
	}

	update_post_meta( $post_id, '_quote_check_quotes', array_values( $quotes ) );
	update_post_meta( $post_id, '_quote_check_status', 'unchecked' );
}

add_action( 'wp_ajax_ferdi_mark_quotes_ok', 'ferdi_ajax_mark_quotes_ok' );
function ferdi_ajax_mark_quotes_ok() {
	if ( ! isset( $_POST['post_id'] ) || ! isset( $_POST['nonce'] ) ) {
		wp_send_json_error( 'Ongeldige aanvraag.' );
	}
	$post_id = intval( $_POST['post_id'] );
	if ( ! wp_verify_nonce( $_POST['nonce'], 'ferdi_quote_ok_' . $post_id ) ) {
		wp_send_json_error( 'Beveiligingscontrole mislukt.' );
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		wp_send_json_error( 'Geen rechten.' );
	}
	update_post_meta( $post_id, '_quote_check_status', 'ok' );
	update_post_meta( $post_id, '_quote_check_ok_by', get_current_user_id() );
	update_post_meta( $post_id, '_quote_check_ok_at', current_time( 'mysql' ) );
	wp_send_json_success( 'Quotes gemarkeerd als OK.' );
}

add_filter( 'manage_post_posts_columns', 'ferdi_add_quote_column' );
function ferdi_add_quote_column( $columns ) {
	$columns['quote_check'] = 'Quotes';
	return $columns;
}

add_action( 'manage_post_posts_custom_column', 'ferdi_fill_quote_column', 10, 2 );
function ferdi_fill_quote_column( $column, $post_id ) {
	if ( $column !== 'quote_check' ) return;
	$quotes = get_post_meta( $post_id, '_quote_check_quotes', true );
	$status = get_post_meta( $post_id, '_quote_check_status', true );
	if ( empty( $quotes ) ) return;
	if ( $status === 'ok' ) {
		echo '<strong style="color:#46b450;background:#e7f7ed;padding:2px 8px;border-radius:4px;">Ok</strong>';
	} else {
		echo '<strong style="color:#dc3232;background:#fbeaea;padding:2px 8px;border-radius:4px;">Quote</strong>';
	}
}

add_action( 'add_meta_boxes', 'ferdi_add_quote_metabox' );
function ferdi_add_quote_metabox() {
	add_meta_box( 'ferdi_quote_check', 'Quote Check', 'ferdi_render_quote_metabox', 'post', 'normal', 'high' );
}

function ferdi_render_quote_metabox( $post ) {
	$quotes = get_post_meta( $post->ID, '_quote_check_quotes', true );
	$status = get_post_meta( $post->ID, '_quote_check_status', true );
	$ok_by  = get_post_meta( $post->ID, '_quote_check_ok_by', true );
	$ok_at  = get_post_meta( $post->ID, '_quote_check_ok_at', true );

	if ( empty( $quotes ) ) {
		echo '<p style="color:#999;">Geen quotes gevonden in dit artikel.</p>';
		return;
	}

	$nonce = wp_create_nonce( 'ferdi_quote_ok_' . $post->ID );

	if ( $status === 'ok' ) {
		$user_info = $ok_by ? get_userdata( $ok_by ) : null;
		$username  = $user_info ? esc_html( $user_info->display_name ) : 'onbekend';
		echo '<p style="color:#46b450;">&#10003; Quotes gemarkeerd als OK door <strong>' . $username . '</strong> op ' . esc_html( $ok_at ) . '.</p>';
		return;
	}

	echo '<table class="wp-list-table widefat fixed striped" style="margin-top:10px;">';
	echo '<thead><tr><th>Quote</th><th>Actie</th></tr></thead><tbody>';
	foreach ( $quotes as $quote ) {
		$google_url = 'https://www.google.com/search?q=' . urlencode( '"' . $quote . '"' );
		echo '<tr>';
		echo '<td>"' . esc_html( $quote ) . '"</td>';
		echo '<td><a href="' . esc_url( $google_url ) . '" target="_blank" rel="noreferrer noopener" class="button button-secondary">Opzoeken</a></td>';
		echo '</tr>';
	}
	echo '</tbody></table>';

	echo '<p style="margin-top:16px;">';
	echo '<button type="button" id="ferdi-quotes-ok-btn" class="button button-primary" data-post-id="' . esc_attr( $post->ID ) . '" data-nonce="' . esc_attr( $nonce ) . '">Quotes okay</button>';
	echo '<span id="ferdi-quotes-ok-msg" style="margin-left:12px;display:none;"></span>';
	echo '</p>';
	?>
	<script>
	(function() {
		var btn = document.getElementById('ferdi-quotes-ok-btn');
		if (!btn) return;
		btn.addEventListener('click', function() {
			btn.disabled = true;
			btn.textContent = 'Bezig...';
			var data = new FormData();
			data.append('action', 'ferdi_mark_quotes_ok');
			data.append('post_id', btn.dataset.postId);
			data.append('nonce', btn.dataset.nonce);
			fetch(ajaxurl, { method: 'POST', body: data })
				.then(function(r) { return r.json(); })
				.then(function(json) {
					var msg = document.getElementById('ferdi-quotes-ok-msg');
					if (json.success) {
						msg.style.color = '#46b450';
						msg.textContent = 'OK. Herlaad de pagina om de status te zien.';
					} else {
						msg.style.color = '#dc3232';
						msg.textContent = 'Fout: ' + json.data;
						btn.disabled = false;
						btn.textContent = 'Quotes okay';
					}
					msg.style.display = 'inline';
				})
				.catch(function() {
					var msg = document.getElementById('ferdi-quotes-ok-msg');
					msg.style.color = '#dc3232';
					msg.textContent = 'Verbindingsfout. Probeer opnieuw.';
					msg.style.display = 'inline';
					btn.disabled = false;
					btn.textContent = 'Quotes okay';
				});
		});
	})();
	</script>
	<?php
}

add_action( 'admin_head', 'ferdi_quote_column_style' );
function ferdi_quote_column_style() {
	echo '<style>.column-quote_check{width:100px!important;text-align:center}</style>';
}

add_filter( 'manage_edit-post_sortable_columns', 'ferdi_quote_sortable_column' );
function ferdi_quote_sortable_column( $columns ) {
	$columns['quote_check'] = 'quote_check';
	return $columns;
}

add_action( 'restrict_manage_posts', 'ferdi_quote_filter_dropdown' );
function ferdi_quote_filter_dropdown( $post_type ) {
	if ( $post_type !== 'post' ) return;
	$current = isset( $_GET['quote_status'] ) ? $_GET['quote_status'] : '';
	echo '<select name="quote_status">';
	echo '<option value="">Alle quotes</option>';
	echo '<option value="unchecked"' . selected( $current, 'unchecked', false ) . '>Quote (niet gecheckt)</option>';
	echo '<option value="ok"' . selected( $current, 'ok', false ) . '>Ok (gecheckt)</option>';
	echo '<option value="none"' . selected( $current, 'none', false ) . '>Geen quotes</option>';
	echo '</select>';
}

add_action( 'pre_get_posts', 'ferdi_quote_filter_query' );
function ferdi_quote_filter_query( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) return;
	if ( $query->get( 'post_type' ) !== 'post' ) return;
	$status = isset( $_GET['quote_status'] ) ? $_GET['quote_status'] : '';
	if ( ! $status ) return;
	if ( $status === 'none' ) {
		$query->set( 'meta_query', [ [ 'key' => '_quote_check_quotes', 'compare' => 'NOT EXISTS' ] ] );
	} elseif ( $status === 'unchecked' ) {
		$query->set( 'meta_query', [ [ 'key' => '_quote_check_status', 'value' => 'unchecked' ] ] );
	} elseif ( $status === 'ok' ) {
		$query->set( 'meta_query', [ [ 'key' => '_quote_check_status', 'value' => 'ok' ] ] );
	}
}

add_filter( 'bulk_actions-edit-post', 'ferdi_add_bulk_quote_check' );
function ferdi_add_bulk_quote_check( $actions ) {
	$actions['run_quote_check'] = 'Quote check uitvoeren';
	return $actions;
}

add_filter( 'handle_bulk_actions-edit-post', 'ferdi_handle_bulk_quote_check', 10, 3 );
function ferdi_handle_bulk_quote_check( $redirect_url, $action, $post_ids ) {
	if ( $action !== 'run_quote_check' ) return $redirect_url;
	foreach ( $post_ids as $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) continue;
		preg_match_all( '/<[^>]*>(*SKIP)(*FAIL)|(?:&#8220;|\x{201C}|")([^"&#\x{201D}]+)(?:&#8221;|\x{201D}|")/u', $post->post_content, $matches );
		$quotes = array_unique( array_map( 'trim', $matches[1] ) );
		$quotes = array_filter( $quotes );
		if ( empty( $quotes ) ) {
			delete_post_meta( $post_id, '_quote_check_quotes' );
			delete_post_meta( $post_id, '_quote_check_status' );
		} else {
			update_post_meta( $post_id, '_quote_check_quotes', array_values( $quotes ) );
			update_post_meta( $post_id, '_quote_check_status', 'unchecked' );
		}
	}
	return add_query_arg( 'quote_check_done', count( $post_ids ), $redirect_url );
}

add_action( 'admin_notices', 'ferdi_bulk_quote_check_notice' );
function ferdi_bulk_quote_check_notice() {
	if ( ! isset( $_GET['quote_check_done'] ) ) return;
	$count = intval( $_GET['quote_check_done'] );
	echo '<div class="notice notice-success is-dismissible"><p>' . $count . ' artikelen gecheckt op quotes.</p></div>';
}

/* ─────────────────────────────────────────
   FACTCHECK AUDIT (GEMINI)
───────────────────────────────────────── */
add_action( 'init', function () {
	foreach ( [
		'kaslek_factcheck_score'              => 'integer',
		'kaslek_factcheck_verdict'            => 'string',
		'kaslek_factcheck_herschrijfadvies'   => 'string',
	] as $key => $type ) {
		register_post_meta( 'post', $key, [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => $type,
			'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
		] );
	}
} );

add_action( 'add_meta_boxes', function (): void {
	add_meta_box(
		'kaslek_factcheck_full_audit',
		'KasLek Factcheck Resultaten',
		function ( $post ): void {
			$score   = get_post_meta( $post->ID, 'kaslek_factcheck_score', true );
			$verdict = get_post_meta( $post->ID, 'kaslek_factcheck_verdict', true );
			$suggest = get_post_meta( $post->ID, 'kaslek_factcheck_herschrijfadvies', true );

			echo '<div style="padding:10px 0;">';
			echo '<p style="margin:0;text-transform:uppercase;font-size:10px;color:#666;font-weight:bold;">Score</p>';
			if ( $score !== '' ) {
				$color = ( $score < 70 ) ? '#d63638' : '#2271b1';
				printf( "<p style='font-size:24px;font-weight:bold;margin:0 0 15px 0;color:%s;'>%s/100</p>", $color, esc_html( $score ) );
			}
			echo '<p style="margin:0;text-transform:uppercase;font-size:10px;color:#666;font-weight:bold;">Verdict</p>';
			echo '<div style="background:#f6f7f7;border-left:4px solid #2271b1;padding:8px 12px;margin:5px 0 15px 0;">' . ( $verdict ? wp_kses_post( wpautop( $verdict ) ) : '–' ) . '</div>';
			echo '<p style="margin:0;text-transform:uppercase;font-size:10px;color:#666;font-weight:bold;">Herschrijfadvies</p>';
			echo '<div style="background:#fff8e5;border-left:4px solid #ffb900;padding:8px 12px;margin:5px 0 0 0;">' . ( $suggest ? wp_kses_post( wpautop( $suggest ) ) : '–' ) . '</div>';
			echo '</div>';
		},
		'post', 'normal', 'high'
	);
} );

/* ─────────────────────────────────────────
   AUTEUR RANDOMIZER
───────────────────────────────────────── */
add_action( 'save_post', 'kaslek_randomize_author_on_save', 10, 3 );
function kaslek_randomize_author_on_save( $post_id, $post, $update ) {
	if ( wp_is_post_revision( $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) return;
	if ( $post->post_type !== 'post' ) return;
	if ( intval( $post->post_author ) === 7 && in_array( $post->post_status, [ 'future', 'publish' ], true ) ) {
		$random_author = [ 2, 4, 5 ][ array_rand( [ 2, 4, 5 ] ) ];
		remove_action( 'save_post', 'kaslek_randomize_author_on_save', 10 );
		wp_update_post( [ 'ID' => $post_id, 'post_author' => $random_author ] );
		add_action( 'save_post', 'kaslek_randomize_author_on_save', 10, 3 );
	}
}

/* ─────────────────────────────────────────
   AUTOQUE
───────────────────────────────────────── */
function ferdi_post_is_ready_to_publish( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post ) return false;
	if ( empty( trim( $post->post_title ) ) ) return false;
	if ( empty( trim( $post->post_content ) ) ) return false;
	if ( ! has_post_thumbnail( $post_id ) ) return false;
	return true;
}

function ferdi_count_posts_on_date( $timestamp ) {
	$offset    = (int) get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
	$day_start = gmdate( 'Y-m-d 00:00:00', $timestamp + $offset );
	$day_end   = gmdate( 'Y-m-d 23:59:59', $timestamp + $offset );
	$q = new WP_Query( [
		'post_type'      => 'post',
		'post_status'    => [ 'publish', 'future' ],
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => false,
		'date_query'     => [ [ 'after' => $day_start, 'before' => $day_end, 'inclusive' => true, 'column' => 'post_date' ] ],
	] );
	return (int) $q->found_posts;
}

function ferdi_execute_autoque_logic( $post_id ) {
	if ( ! ferdi_post_is_ready_to_publish( $post_id ) ) return false;

	$now        = time();
	$buffer     = rand( 4 * HOUR_IN_SECONDS, 5 * HOUR_IN_SECONDS );
	$upcoming   = get_posts( [
		'numberposts' => 1,
		'post_status' => 'future',
		'exclude'     => [ $post_id ],
		'orderby'     => 'post_date',
		'order'       => 'DESC',
		'date_query'  => [ [ 'after' => gmdate( 'Y-m-d H:i:s', $now ), 'inclusive' => true ] ],
	] );

	$base_time = ! empty( $upcoming ) ? strtotime( $upcoming[0]->post_date_gmt . ' +0000' ) : $now;
	$new_time  = $base_time + $buffer + rand( 60, 720 );

	$offset      = (int) get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
	$max_per_day = (int) get_option( 'aivd_max_per_day', 3 );

	$attempts = 0;
	while ( $attempts < 7 ) {
		$time_of_day = gmdate( 'H:i', $new_time + $offset );
		if ( $time_of_day >= '21:00' || $time_of_day < '08:00' ) {
			$target_day = ( $time_of_day >= '21:00' ) ? '+1 day' : 'today';
			$new_time   = strtotime( $target_day . ' 08:00', $new_time + $offset ) - $offset + rand( 0, 900 );
		}
		if ( ferdi_count_posts_on_date( $new_time ) < $max_per_day ) break;
		$new_time = strtotime( '+1 day 08:00', $new_time + $offset ) - $offset + rand( 0, 900 );
		$attempts++;
	}

	if ( $new_time < $now ) $new_time = $now + rand( 60, 720 );

	$post_data = [
		'ID'            => $post_id,
		'post_date'     => gmdate( 'Y-m-d H:i:s', $new_time + $offset ),
		'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $new_time ),
		'post_status'   => ( $new_time <= $now ) ? 'publish' : 'future',
		'edit_date'     => true,
	];

	remove_action( 'save_post', 'ferdi_execute_autoque_logic' );
	$result = wp_update_post( $post_data );

	if ( $result !== 0 && ! is_wp_error( $result ) ) {
		clean_post_cache( $post_id );
		if ( $new_time > $now ) {
			wp_clear_scheduled_hook( 'publish_future_post', [ $post_id ] );
			wp_schedule_single_event( $new_time, 'publish_future_post', [ $post_id ] );
		}
		return gmdate( 'd-m-Y H:i', $new_time + $offset );
	}
	return false;
}

add_filter( 'cron_schedules', function( $schedules ) {
	if ( ! isset( $schedules['every_3_minutes'] ) ) {
		$schedules['every_3_minutes'] = [ 'interval' => 180, 'display' => 'Elke 3 minuten' ];
	}
	return $schedules;
} );

add_action( 'init', function() {
	if ( ! wp_next_scheduled( 'ferdi_silent_observer_run' ) ) {
		wp_schedule_event( time(), 'every_3_minutes', 'ferdi_silent_observer_run' );
	}
} );

add_action( 'ferdi_silent_observer_run', 'ferdi_silent_observer_execute' );
function ferdi_silent_observer_execute() {
	if ( ! get_option( 'aivd_autopost_enabled', '1' ) ) return;

	$check_score = get_option( 'aivd_check_factcheck_score', '1' );
	$threshold   = (int) get_option( 'aivd_factcheck_threshold', 100 );

	$args = [
		'post_type'   => 'post',
		'post_status' => 'draft',
		'numberposts' => 20,
	];

	if ( $check_score ) {
		$args['meta_query'] = [ [
			'key'     => 'kaslek_factcheck_score',
			'value'   => $threshold,
			'compare' => '>=',
			'type'    => 'NUMERIC',
		] ];
	}

	$drafts = get_posts( $args );
	if ( empty( $drafts ) ) return;

	foreach ( $drafts as $post ) {
		$post_id = $post->ID;
		if ( ! ferdi_post_is_ready_to_publish( $post_id ) ) continue;
		if ( get_option( 'aivd_check_grok_links', '1' ) && ! ferdi_observer_links_are_ok( $post_id ) ) {
			update_post_meta( $post_id, '_aivd_grok_blocked', 1 );
			continue;
		}
		if ( get_option( 'aivd_check_quotes', '1' )     && ! ferdi_observer_quotes_are_ok( $post_id ) ) continue;
		if ( get_post_status( $post_id ) !== 'draft' ) continue;
		ferdi_execute_autoque_logic( $post_id );
	}
}

function ferdi_observer_links_are_ok( $post_id ) {
	if ( get_post_meta( $post_id, '_link_audit_manual_ok', true ) ) return true;
	$last_run = get_post_meta( $post_id, '_link_audit_last_run', true );
	if ( empty( $last_run ) ) return false;
	$results = get_post_meta( $post_id, '_link_audit_results', true );
	if ( ! is_array( $results ) ) return false;
	if ( empty( $results ) ) return true;
	foreach ( $results as $code ) {
		if ( ! in_array( $code, [ 200, 201, 301, 302 ], true ) ) return false;
	}
	return true;
}

function ferdi_observer_quotes_are_ok( $post_id ) {
	$quotes = get_post_meta( $post_id, '_quote_check_quotes', true );
	if ( empty( $quotes ) ) return true;
	return get_post_meta( $post_id, '_quote_check_status', true ) === 'ok';
}

add_action( 'post_submitbox_misc_actions', 'ferdi_add_autoque_button' );
function ferdi_add_autoque_button() {
	global $post;
	if ( ! $post || $post->post_type !== 'post' || $post->post_status !== 'draft' ) return;

	$has_title   = ! empty( trim( $post->post_title ) );
	$has_content = ! empty( trim( $post->post_content ) );
	$has_thumb   = has_post_thumbnail( $post->ID );
	$ready       = ferdi_post_is_ready_to_publish( $post->ID );

	$btn_base  = 'width:100%;text-align:center;margin-bottom:6px;display:block;';
	$btn_white = $btn_base . 'background:#fff;color:#2271b1;border-color:#2271b1;';
	$btn_blue  = $btn_base . 'background:#2271b1;color:#fff;border-color:#135e96;';

	echo '<div id="ferdi-autoque-container" class="misc-pub-section" style="border-top:1px solid #ddd;padding-top:10px;margin-top:10px;">';
	echo '<div id="ferdi-autoque-main-ui">';
	echo '<button type="button" id="ferdi-autoque-btn" class="button button-large" style="' . $btn_white . '"' . ( ! $ready ? ' disabled' : '' ) . '>';
	echo '<span class="dashicons dashicons-clock" style="vertical-align:middle;margin-right:5px;"></span> Autoque';
	echo '</button>';
	echo '</div>';

	if ( ! $ready ) {
		echo '<ul style="margin:0 0 8px 0;padding:0;list-style:none;font-size:11px;line-height:1.6;">';
		echo '<li style="color:' . ( $has_title   ? '#00a32a' : '#d63638' ) . ';">' . ( $has_title   ? '&#10003;' : '&#10005;' ) . ' Titel</li>';
		echo '<li style="color:' . ( $has_content ? '#00a32a' : '#d63638' ) . ';">' . ( $has_content ? '&#10003;' : '&#10005;' ) . ' Bodytekst</li>';
		echo '<li style="color:' . ( $has_thumb   ? '#00a32a' : '#d63638' ) . ';">' . ( $has_thumb   ? '&#10003;' : '&#10005;' ) . ' Featured image</li>';
		echo '</ul>';
	}

	echo '<p id="ferdi-autoque-res" style="margin:0 0 6px 0;font-weight:bold;font-size:11px;color:#00a32a;"></p>';
	echo '<div id="ferdi-autoque-warning" style="display:none;color:#d63638;font-size:11px;line-height:1.3;margin-bottom:6px;">';
	echo '<span class="dashicons dashicons-warning" style="font-size:14px;width:14px;height:14px;vertical-align:text-bottom;margin-right:3px;"></span> Sla op als concept om te kunnen Autoquen.';
	echo '</div>';
	echo '<button type="button" id="ferdi-factcheck-reset-btn" class="button button-large" style="' . $btn_blue . '">';
	echo '<span class="dashicons dashicons-yes" style="vertical-align:middle;margin-right:5px;"></span> Factcheck gedaan';
	echo '</button>';
	echo '</div>';
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		var $main    = $('#ferdi-autoque-main-ui');
		var $warning = $('#ferdi-autoque-warning');
		var $btn     = $('#ferdi-autoque-btn');

		function invalidateAutoque() {
			if ($main.is(':visible')) { $main.hide(); $warning.fadeIn(150); }
		}

		if (typeof tinymce !== 'undefined') {
			setTimeout(function() {
				if (tinymce.get('content')) {
					tinymce.get('content').on('change keyup input', function() { invalidateAutoque(); });
				}
			}, 1000);
		}
		$(document).on('input change keydown', '#content, #title, .postbox input, .postbox textarea', function() {
			invalidateAutoque();
		});

		$btn.on('click', function(e) {
			e.preventDefault();
			$btn.prop('disabled', true).text('Plannen...');
			$.post(ajaxurl, {
				action:  'ferdi_manual_autoque',
				post_id: <?php echo (int) $post->ID; ?>,
				nonce:   '<?php echo wp_create_nonce( 'autoque_nonce_' . $post->ID ); ?>'
			}, function(response) {
				if (response.success) {
					$('#ferdi-autoque-res').html('Gepland voor: ' + response.data);
					$main.hide();
					setTimeout(function(){ location.reload(); }, 1000);
				} else {
					alert('Fout: ' + response.data);
					$btn.prop('disabled', false).html('<span class="dashicons dashicons-clock" style="vertical-align:middle;margin-right:5px;"></span> Autoque');
				}
			});
		});

		$('#ferdi-factcheck-reset-btn').on('click', function(e) {
			e.preventDefault();
			var $r = $(this);
			$r.prop('disabled', true).text('Bezig...');
			$.post(ajaxurl, {
				action:  'ferdi_reset_factcheck',
				post_id: <?php echo (int) $post->ID; ?>,
				nonce:   '<?php echo wp_create_nonce( 'factcheck_reset_nonce_' . $post->ID ); ?>'
			}, function(response) {
				if (response.success) {
					location.reload();
				} else {
					alert('Fout: ' + response.data);
					$r.prop('disabled', false).html('<span class="dashicons dashicons-yes" style="vertical-align:middle;margin-right:5px;"></span> Factcheck gedaan');
				}
			});
		});
	});
	</script>
	<?php
}

add_action( 'wp_ajax_ferdi_manual_autoque', function() {
	$post_id = intval( $_POST['post_id'] );
	check_ajax_referer( 'autoque_nonce_' . $post_id, 'nonce' );
	if ( ! current_user_can( 'edit_post', $post_id ) ) wp_send_json_error( 'Geen rechten' );
	if ( ! ferdi_post_is_ready_to_publish( $post_id ) ) wp_send_json_error( 'Niet gereed: titel, bodytekst of featured image ontbreekt.' );
	if ( get_option( 'aivd_check_grok_links', '1' ) && ! ferdi_observer_links_are_ok( $post_id ) ) wp_send_json_error( 'Grok-links staan op Let op. Corrigeer de links of markeer ze als OK voor je dit artikel inplant.' );
	if ( get_option( 'aivd_check_quotes', '1' ) && ! ferdi_observer_quotes_are_ok( $post_id ) ) wp_send_json_error( 'Quotes zijn nog niet gecheckt. Markeer de quotes als OK voor je dit artikel inplant.' );
	$date = ferdi_execute_autoque_logic( $post_id );
	if ( $date ) wp_send_json_success( $date );
	else wp_send_json_error( 'Planning mislukt' );
} );

add_action( 'wp_ajax_ferdi_reset_factcheck', function() {
	$post_id = intval( $_POST['post_id'] );
	check_ajax_referer( 'factcheck_reset_nonce_' . $post_id, 'nonce' );
	if ( ! current_user_can( 'edit_post', $post_id ) ) wp_send_json_error( 'Geen rechten' );
	delete_post_meta( $post_id, 'kaslek_factcheck_score' );
	delete_post_meta( $post_id, 'kaslek_factcheck_verdict' );
	delete_post_meta( $post_id, 'kaslek_factcheck_herschrijfadvies' );
	wp_update_post( [ 'ID' => $post_id, 'post_status' => 'draft' ] );
	wp_send_json_success();
} );

add_filter( 'manage_post_posts_columns', function( $cols ) {
	unset( $cols['tags'], $cols['comments'] );
	$new = [];
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( $k === 'title' ) $new['kaslek_factcheck_score'] = 'Score';
	}
	return $new;
} );

add_action( 'manage_post_posts_custom_column', function( $col, $id ) {
	if ( $col !== 'kaslek_factcheck_score' ) return;
	wp_cache_delete( $id, 'post_meta' );
	$score = get_post_meta( $id, 'kaslek_factcheck_score', true );
	if ( $score === '' || $score === false ) {
		echo '<span style="color:#ccc;">–</span>';
	} else {
		$color = ( intval( $score ) < 70 ) ? '#d63638' : '#00a32a';
		printf( '<strong style="color:%s;">%d</strong>', $color, intval( $score ) );
	}
}, 10, 2 );

/* ─────────────────────────────────────────
   AIVD INSTELLINGEN
───────────────────────────────────────── */
add_action( 'admin_menu', function() {
	add_menu_page( 'AIVD', 'AIVD', 'manage_options', 'aivd', 'aivd_settings_page', 'dashicons-shield', 3 );
	add_submenu_page( 'aivd', 'Instellingen', 'Instellingen', 'manage_options', 'aivd', 'aivd_settings_page' );
	add_submenu_page( 'aivd', 'Infinite Scroll', 'Infinite Scroll', 'manage_options', 'kaslek-infinite-scroll', 'kaslek_infinite_scroll_settings_page' );
} );

function aivd_settings_page() {
	if ( isset( $_POST['aivd_nonce'] ) && wp_verify_nonce( $_POST['aivd_nonce'], 'aivd_save' ) ) {
		update_option( 'aivd_autopost_enabled',      isset( $_POST['aivd_autopost_enabled'] )      ? '1' : '0' );
		update_option( 'aivd_check_grok_links',      isset( $_POST['aivd_check_grok_links'] )      ? '1' : '0' );
		update_option( 'aivd_check_quotes',          isset( $_POST['aivd_check_quotes'] )          ? '1' : '0' );
		update_option( 'aivd_check_factcheck_score', isset( $_POST['aivd_check_factcheck_score'] ) ? '1' : '0' );
		$threshold = (int) ( $_POST['aivd_factcheck_threshold'] ?? 100 );
		update_option( 'aivd_factcheck_threshold', max( 0, min( 100, (int) round( $threshold / 10 ) * 10 ) ) );
		$max = (int) ( $_POST['aivd_max_per_day'] ?? 3 );
		update_option( 'aivd_max_per_day', max( 1, min( 5, $max ) ) );
		echo '<div class="updated notice"><p>Instellingen opgeslagen.</p></div>';
	}

	$autopost  = get_option( 'aivd_autopost_enabled',      '1' );
	$grok      = get_option( 'aivd_check_grok_links',      '1' );
	$quotes    = get_option( 'aivd_check_quotes',          '1' );
	$factcheck   = get_option( 'aivd_check_factcheck_score', '1' );
	$threshold   = (int) get_option( 'aivd_factcheck_threshold', 100 );
	$max_per_day = (int) get_option( 'aivd_max_per_day', 3 );
	?>
	<div class="wrap">
		<h1>AIVD — Instellingen</h1>
		<form method="post">
			<?php wp_nonce_field( 'aivd_save', 'aivd_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row">Autopost</th>
					<td>
						<label class="kaslek-toggle">
							<input type="checkbox" name="aivd_autopost_enabled" value="1" <?php checked( $autopost, '1' ); ?>>
							<span class="kaslek-toggle-slider"></span>
						</label>
						<p class="description" style="margin-top:8px;">Aan: artikelen gaan live als aan alle voorwaarden is voldaan. Uit: niets gaat live.</p>
					</td>
				</tr>
				<tr>
					<th scope="row">Controleer Grok-links</th>
					<td>
						<label class="kaslek-toggle">
							<input type="checkbox" name="aivd_check_grok_links" value="1" <?php checked( $grok, '1' ); ?>>
							<span class="kaslek-toggle-slider"></span>
						</label>
						<p class="description" style="margin-top:8px;">Aan: artikelen met Grok-links status <em>Let op</em> worden geblokkeerd voor livegang. Een redacteur moet de links beoordelen en handmatig vrijgeven.</p>
					</td>
				</tr>
				<tr>
					<th scope="row">Blokkeer quotes</th>
					<td>
						<label class="kaslek-toggle">
							<input type="checkbox" name="aivd_check_quotes" value="1" <?php checked( $quotes, '1' ); ?>>
							<span class="kaslek-toggle-slider"></span>
						</label>
						<p class="description" style="margin-top:8px;">Aan: artikelen met niet-gecheckte quotes worden geblokkeerd. Geen quotes of status <em>ok</em> → mag live. Status <em>niet gecheckt</em> → geblokkeerd.</p>
					</td>
				</tr>
				<tr>
					<th scope="row">Controleer factcheck-score</th>
					<td>
						<label class="kaslek-toggle">
							<input type="checkbox" name="aivd_check_factcheck_score" value="1" <?php checked( $factcheck, '1' ); ?>>
							<span class="kaslek-toggle-slider"></span>
						</label>
						<p class="description" style="margin-top:8px;">Aan: alleen drafts met een factcheck-score ≥ drempel (zie hieronder) worden meegenomen voor autopost. Geen score of te laag → wordt overgeslagen.</p>
					</td>
				</tr>
				<tr>
					<th scope="row">Max. artikelen per dag</th>
					<td>
						<input type="number" name="aivd_max_per_day" value="<?php echo esc_attr( $max_per_day ); ?>" min="1" max="5" step="1" style="width:60px;">
						<p class="description">Maximaal aantal artikelen dat per dag live gaat (1–5, default 3).</p>
					</td>
				</tr>
				<tr>
					<th scope="row">Factcheck-drempel</th>
					<td>
						<input type="number" name="aivd_factcheck_threshold" value="<?php echo esc_attr( $threshold ); ?>" min="0" max="100" step="10" style="width:80px;">
						<p class="description">Minimale score om te publiceren (0–100, stappen van 10).</p>
					</td>
				</tr>
			</table>
			<?php submit_button( 'Opslaan' ); ?>
		</form>
	</div>
	<style>
		.kaslek-toggle { position:relative; display:inline-block; width:50px; height:26px; }
		.kaslek-toggle input { opacity:0; width:0; height:0; }
		.kaslek-toggle-slider { position:absolute; cursor:pointer; inset:0; background:#ccc; border-radius:26px; transition:.3s; }
		.kaslek-toggle-slider:before { content:''; position:absolute; width:20px; height:20px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.3s; }
		.kaslek-toggle input:checked + .kaslek-toggle-slider { background:#2271b1; }
		.kaslek-toggle input:checked + .kaslek-toggle-slider:before { transform:translateX(24px); }
	</style>
	<?php
}

function kaslek_infinite_scroll_settings_page() {
	if ( isset( $_POST['kaslek_scroll_nonce'] ) && wp_verify_nonce( $_POST['kaslek_scroll_nonce'], 'kaslek_scroll_save' ) ) {
		update_option( 'kaslek_scroll_max', absint( $_POST['kaslek_scroll_max'] ?? 0 ) );
		echo '<div class="updated notice is-dismissible"><p>Instellingen opgeslagen.</p></div>';
	}
	$max = (int) get_option( 'kaslek_scroll_max', 0 );
	?>
	<div class="wrap">
		<h1>Infinite Scroll</h1>
		<form method="post">
			<?php wp_nonce_field( 'kaslek_scroll_save', 'kaslek_scroll_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row">Maximum artikelen per archief</th>
					<td>
						<input type="number" name="kaslek_scroll_max" value="<?php echo esc_attr( $max ); ?>" min="0" step="1" style="width:80px;">
						<p class="description">0 = onbeperkt (alle artikelen laden). Bij een getal worden maximaal dat aantal artikelen getoond.</p>
					</td>
				</tr>
			</table>
			<?php submit_button( 'Opslaan' ); ?>
		</form>
	</div>
	<?php
}

/* ─────────────────────────────────────────
   ADSENSE BEHEER
───────────────────────────────────────── */
add_action( 'admin_menu', function() {
	add_submenu_page(
		'edit.php?post_type=kaslek_ad',
		'Adsense',
		'Adsense',
		'manage_options',
		'kaslek-adsense',
		'kaslek_adsense_settings_page'
	);
} );

function kaslek_adsense_settings_page() {
	if ( isset( $_POST['kaslek_adsense_nonce'] ) && wp_verify_nonce( $_POST['kaslek_adsense_nonce'], 'kaslek_adsense_save' ) ) {
		update_option( 'kaslek_adsense_enabled', isset( $_POST['kaslek_adsense_enabled'] ) ? '1' : '0' );
		update_option( 'kaslek_adsense_head_script', wp_unslash( $_POST['kaslek_adsense_head_script'] ?? '' ) );

		$formats = [];
		if ( isset( $_POST['kaslek_formats'] ) && is_array( $_POST['kaslek_formats'] ) ) {
			foreach ( $_POST['kaslek_formats'] as $f ) {
				$label = sanitize_text_field( wp_unslash( $f['label'] ?? '' ) );
				$code  = wp_unslash( $f['code'] ?? '' );
				if ( $label !== '' || $code !== '' ) {
					$formats[] = [ 'label' => $label, 'code' => $code ];
				}
			}
		}
		update_option( 'kaslek_adsense_formats', $formats );
		echo '<div class="updated notice is-dismissible"><p>Instellingen opgeslagen.</p></div>';
	}

	$enabled      = get_option( 'kaslek_adsense_enabled', '1' );
	$default_head = '<meta name="google-adsense-account" content="ca-pub-6115912536653612">' . "\n" . '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6115912536653612" crossorigin="anonymous"></script>';
	$head_script  = get_option( 'kaslek_adsense_head_script', $default_head );
	$formats      = get_option( 'kaslek_adsense_formats' );

	if ( $formats === false ) {
		$formats = [ [
			'label' => 'Display – vierkant (slot 4772512111)',
			'code'  => '<ins class="adsbygoogle" style="display:block;width:100%" data-ad-client="ca-pub-6115912536653612" data-ad-slot="4772512111" data-ad-format="auto" data-full-width-responsive="true"></ins>' . "\n" . '<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>',
		] ];
	}
	?>
	<div class="wrap">
		<h1>Adsense</h1>
		<form method="post">
			<?php wp_nonce_field( 'kaslek_adsense_save', 'kaslek_adsense_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row">Ads aan/uit</th>
					<td>
						<label class="kaslek-toggle">
							<input type="checkbox" name="kaslek_adsense_enabled" value="1" <?php checked( $enabled, '1' ); ?>>
							<span class="kaslek-toggle-slider"></span>
						</label>
						<p class="description" style="margin-top:8px;">Aan: alles werkt zoals normaal. Uit: AdSense-script wordt niet geladen en <code>[kaslek_ad]</code> geeft niets terug. Eigen Ads Manager wordt niet geraakt.</p>
					</td>
				</tr>
				<tr>
					<th scope="row">AdSense head-fragment</th>
					<td>
						<textarea name="kaslek_adsense_head_script" rows="5" style="width:100%;font-family:monospace;font-size:12px;"><?php echo esc_textarea( $head_script ); ?></textarea>
						<p class="description">HTML-fragment dat in <code>&lt;head&gt;</code> wordt geplaatst op alle pagina's behalve de homepage (homepage laadt AdSense via IntersectionObserver).</p>
					</td>
				</tr>
			</table>

			<h2 style="margin-top:30px;">Ad-formats</h2>
			<p class="description" style="margin-bottom:16px;">HTML-codes van individuele ad-formats. De eerste in de lijst wordt gebruikt door <code>[kaslek_ad]</code>.</p>

			<div id="kaslek-formats-list">
				<?php foreach ( $formats as $i => $fmt ) : ?>
				<div class="kaslek-format-row" style="background:#fafafa;border:1px solid #ddd;border-radius:4px;padding:16px;margin-bottom:12px;">
					<div style="display:flex;gap:10px;align-items:center;margin-bottom:8px;">
						<input type="text" name="kaslek_formats[<?php echo $i; ?>][label]" value="<?php echo esc_attr( $fmt['label'] ); ?>" placeholder="Label (bijv. Display vierkant)" style="flex:1;font-size:13px;">
						<button type="button" class="button kaslek-remove-format" style="color:#a00;flex-shrink:0;">Verwijder</button>
					</div>
					<textarea name="kaslek_formats[<?php echo $i; ?>][code]" rows="4" style="width:100%;font-family:monospace;font-size:12px;"><?php echo esc_textarea( $fmt['code'] ); ?></textarea>
				</div>
				<?php endforeach; ?>
			</div>

			<p><button type="button" id="kaslek-add-format" class="button" style="margin-bottom:20px;">+ Format toevoegen</button></p>

			<?php submit_button( 'Opslaan' ); ?>
		</form>
	</div>

	<template id="kaslek-format-tpl">
		<div class="kaslek-format-row" style="background:#fafafa;border:1px solid #ddd;border-radius:4px;padding:16px;margin-bottom:12px;">
			<div style="display:flex;gap:10px;align-items:center;margin-bottom:8px;">
				<input type="text" name="kaslek_formats[__I__][label]" value="" placeholder="Label (bijv. Display vierkant)" style="flex:1;font-size:13px;">
				<button type="button" class="button kaslek-remove-format" style="color:#a00;flex-shrink:0;">Verwijder</button>
			</div>
			<textarea name="kaslek_formats[__I__][code]" rows="4" style="width:100%;font-family:monospace;font-size:12px;"></textarea>
		</div>
	</template>

	<style>
		.kaslek-toggle { position:relative; display:inline-block; width:50px; height:26px; }
		.kaslek-toggle input { opacity:0; width:0; height:0; }
		.kaslek-toggle-slider { position:absolute; cursor:pointer; inset:0; background:#ccc; border-radius:26px; transition:.3s; }
		.kaslek-toggle-slider:before { content:''; position:absolute; width:20px; height:20px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.3s; }
		.kaslek-toggle input:checked + .kaslek-toggle-slider { background:#2271b1; }
		.kaslek-toggle input:checked + .kaslek-toggle-slider:before { transform:translateX(24px); }
	</style>
	<script>
	(function () {
		var list = document.getElementById('kaslek-formats-list');
		var tpl  = document.getElementById('kaslek-format-tpl').innerHTML;
		var idx  = <?php echo count( $formats ); ?>;

		document.getElementById('kaslek-add-format').addEventListener('click', function () {
			var html = tpl.replace(/__I__/g, idx++);
			var tmp  = document.createElement('div');
			tmp.innerHTML = html;
			list.appendChild(tmp.firstElementChild);
		});

		list.addEventListener('click', function (e) {
			if (e.target.classList.contains('kaslek-remove-format')) {
				e.target.closest('.kaslek-format-row').remove();
			}
		});
	})();
	</script>
	<?php
}

/* ─────────────────────────────────────────
   N8N WORKFLOW TRIGGER
───────────────────────────────────────── */
if ( ! defined( 'KASLEK_N8N_WEBHOOK_URL' ) ) {
	define( 'KASLEK_N8N_WEBHOOK_URL', 'http://n8n.ferdituinman.nl:5678/webhook/verwerk-teksten' );
}

add_action( 'wp_dashboard_setup', function() {
	if ( ! current_user_can( 'manage_options' ) ) return;
	wp_add_dashboard_widget( 'kaslek_n8n_trigger_widget', 'Verwerk nieuwe teksten', 'kaslek_n8n_dashboard_widget_render' );
} );

function kaslek_n8n_dashboard_widget_render() {
	$nonce      = wp_create_nonce( 'kaslek_n8n_trigger' );
	$action_url = admin_url( 'admin-post.php' );
	?>
	<p>Start handmatig de workflow die nieuwe teksten verwerkt via n8n.</p>
	<form method="post" action="<?php echo esc_url( $action_url ); ?>">
		<input type="hidden" name="action" value="kaslek_n8n_trigger">
		<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( $nonce ); ?>">
		<p><button type="submit" class="button button-primary button-hero">Verwerk nieuwe teksten</button></p>
	</form>
	<?php
}

add_action( 'admin_post_kaslek_n8n_trigger', function() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Geen toegang' );
	check_admin_referer( 'kaslek_n8n_trigger' );

	$response = wp_remote_post( KASLEK_N8N_WEBHOOK_URL, [
		'timeout' => 15,
		'headers' => [ 'Content-Type' => 'application/json' ],
		'body'    => wp_json_encode( [
			'source' => 'wordpress',
			'user'   => wp_get_current_user()->user_login,
			'time'   => current_time( 'mysql' ),
		] ),
	] );

	if ( is_wp_error( $response ) ) {
		$notice = [ 'type' => 'error', 'text' => 'Fout bij starten van n8n workflow: ' . $response->get_error_message() ];
	} else {
		$code   = wp_remote_retrieve_response_code( $response );
		$notice = ( $code >= 200 && $code < 300 )
			? [ 'type' => 'success', 'text' => 'n8n workflow is gestart.' ]
			: [ 'type' => 'error',   'text' => 'n8n gaf statuscode ' . $code . ' terug.' ];
	}

	set_transient( 'kaslek_n8n_notice', $notice, 30 );
	wp_safe_redirect( admin_url( 'index.php' ) );
	exit;
} );

add_action( 'admin_notices', function() {
	if ( ! current_user_can( 'manage_options' ) ) return;
	$notice = get_transient( 'kaslek_n8n_notice' );
	if ( ! $notice ) return;
	delete_transient( 'kaslek_n8n_notice' );
	$class = $notice['type'] === 'success' ? 'notice-success' : 'notice-error';
	echo '<div class="notice ' . esc_attr( $class ) . ' is-dismissible"><p>' . esc_html( $notice['text'] ) . '</p></div>';
} );
