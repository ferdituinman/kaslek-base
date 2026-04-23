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

function kaslek_tijdlabel( $post_id = null ) {
	$datum    = get_the_date( 'U', $post_id ?: get_the_ID() );
	$verschil = floor( ( time() - $datum ) / DAY_IN_SECONDS );
	if ( $verschil === 0 ) return 'Vandaag';
	if ( $verschil === 1 ) return 'Gisteren';
	return $verschil . ' dagen geleden';
}

function kaslek_categorie_label( $post_id = null ) {
	$cats = get_the_category( $post_id ?: get_the_ID() );
	if ( empty( $cats ) ) return '';
	$naam  = esc_html( $cats[0]->name );
	$slug  = esc_attr( $cats[0]->slug );
	$link  = esc_url( get_category_link( $cats[0]->term_id ) );
	return '<a href="' . $link . '" class="cat-label ' . $slug . '">' . $naam . '</a>';
}

/* ─────────────────────────────────────────
   POST VIEWS
───────────────────────────────────────── */
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
add_filter( 'wpseo_canonical', 'kaslek_dossier_canonical' );
add_filter( 'wpseo_opengraph_url', 'kaslek_dossier_canonical' );

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
	ob_start();
	?>
	<div class="kaslek-ad-shortcode">
		<ins class="adsbygoogle"
		     style="display:block;width:100%"
		     data-ad-client="ca-pub-6115912536653612"
		     data-ad-slot="4772512111"
		     data-ad-format="auto"
		     data-full-width-responsive="true"></ins>
		<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
	</div>
	<?php
	return ob_get_clean();
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

// Niet-homepage: AdSense direct in <head>
add_action( 'wp_head', function() {
	if ( is_front_page() ) return;
	echo '<meta name="google-adsense-account" content="ca-pub-6115912536653612">' . "\n";
	echo '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6115912536653612" crossorigin="anonymous"></script>' . "\n";
} );

// Homepage: AdSense laden via IntersectionObserver zodra eerste ad-slot in beeld komt
add_action( 'wp_footer', function() {
	if ( ! is_front_page() ) return;
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
		'post__not_in'   => [ 1080 ],
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


function kaslek_stem_handler() {
	check_ajax_referer( 'kaslek_stem', 'nonce' );

	$post_id = absint( $_POST['post_id'] );
	$stem    = sanitize_text_field( $_POST['stem'] );

	if ( ! in_array( $stem, [ 'ja', 'nee' ], true ) ) wp_die();

	$stemmen          = get_post_meta( $post_id, 'kaslek_stemmen', true ) ?: [ 'ja' => 0, 'nee' => 0 ];
	$stemmen[ $stem ] = ( $stemmen[ $stem ] ?? 0 ) + 1;
	update_post_meta( $post_id, 'kaslek_stemmen', $stemmen );

	wp_send_json_success( $stemmen );
}
add_action( 'wp_ajax_kaslek_stem',        'kaslek_stem_handler' );
add_action( 'wp_ajax_nopriv_kaslek_stem', 'kaslek_stem_handler' );

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
