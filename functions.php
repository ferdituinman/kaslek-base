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
   SCRIPTS & STYLES
───────────────────────────────────────── */
function kaslek_scripts() {
	wp_enqueue_style(
		'kaslek-main',
		get_template_directory_uri() . '/assets/css/kaslek.css',
		[],
		'1.0.0'
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
   GOOGLE ADS
───────────────────────────────────────── */

function kaslek_inject_adsense() {
	$script = get_option( 'kaslek_adsense_script', '' );
	if ( ! empty( trim( $script ) ) ) {
		echo "\n" . $script . "\n";
	}
}
add_action( 'wp_head', 'kaslek_inject_adsense' );

function kaslek_get_ad( $slot ) {
	$key     = 'kaslek_ad_' . $slot;
	$snippet = get_option( $key, '' );
	if ( empty( trim( $snippet ) ) ) return;
	echo $snippet;
}

function kaslek_ads_menu() {
	add_submenu_page(
		'edit.php?post_type=' . KASLEK_AD_CPT,
		'AdSense Codes',
		'AdSense Codes',
		'manage_options',
		'kaslek-ads',
		'kaslek_ads_page'
	);
}
add_action( 'admin_menu', 'kaslek_ads_menu' );













function kaslek_ads_save() {
	if (
		! isset( $_POST['kaslek_ads_nonce'] ) ||
		! wp_verify_nonce( $_POST['kaslek_ads_nonce'], 'kaslek_ads_save' ) ||
		! current_user_can( 'manage_options' )
	) return;

	$fields = [
		'kaslek_adsense_script',
		'kaslek_ad_horizontaal',
		'kaslek_ad_horizontaal_2',
		'kaslek_ad_horizontaal_3',
		'kaslek_ad_vierkant',
		'kaslek_ad_verticaal',
	];

	foreach ( $fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_option( $field, wp_unslash( $_POST[ $field ] ) );
		}
	}
}
add_action( 'admin_post_kaslek_ads_save', 'kaslek_ads_save' );

function kaslek_ads_page() {
	if ( isset( $_POST['kaslek_ads_nonce'] ) ) {
		kaslek_ads_save();
		echo '<div class="notice notice-success is-dismissible"><p>Instellingen opgeslagen.</p></div>';
	}

	$defaults = [
		'kaslek_adsense_script'   => '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6115912536653612" crossorigin="anonymous"></script>',
		'kaslek_ad_horizontaal'   => '<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="9402022262" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script>',
		'kaslek_ad_horizontaal_2' => '<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="6846276880" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script>',
		'kaslek_ad_horizontaal_3' => '<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="3283312109" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script>',
		'kaslek_ad_vierkant'      => '<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="4772512111" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script>',
		'kaslek_ad_verticaal'     => '<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="6787442841" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script>',
	];

	foreach ( $defaults as $key => $value ) {
		if ( get_option( $key ) === false ) {
			update_option( $key, $value );
		}
	}

	$fields = [
		'kaslek_adsense_script' => [
			'label' => 'AdSense-script (globaal)',
			'desc'  => 'Wordt automatisch in &lt;head&gt; geladen. Bevat alleen het &lt;script async src="..."&gt; blok, geen &lt;ins&gt;.',
		],
		'kaslek_ad_horizontaal' => [
			'label' => 'Display — Horizontaal 1 (responsief)',
			'desc'  => 'Leaderboard bovenaan de pagina.',
		],
		'kaslek_ad_horizontaal_2' => [
			'label' => 'Display — Horizontaal 2 (responsief)',
			'desc'  => 'Midden pagina (ad-centered).',
		],
		'kaslek_ad_horizontaal_3' => [
			'label' => 'Display — Horizontaal 3 (responsief)',
			'desc'  => 'Footer-strip onderaan.',
		],
		'kaslek_ad_vierkant' => [
			'label' => 'Display — Vierkant / In-article (responsief)',
			'desc'  => 'Wordt gebruikt in de [kaslek_ad] shortcode.',
		],
		'kaslek_ad_verticaal' => [
			'label' => 'Display — Verticaal (responsief)',
			'desc'  => 'Sidebar, tweede positie.',
		],
	];
	?>
	<div class="wrap">
		<h1>AdSense Codes</h1>
		<p>Plak hier de snippets uit je Google AdSense-account.</p>
		<form method="post" action="">
			<?php wp_nonce_field( 'kaslek_ads_save', 'kaslek_ads_nonce' ); ?>
			<table class="form-table" role="presentation">
				<?php foreach ( $fields as $key => $field ) : ?>
				<tr>
					<th scope="row">
						<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
					</th>
					<td>
						<textarea
							id="<?php echo esc_attr( $key ); ?>"
							name="<?php echo esc_attr( $key ); ?>"
							rows="5"
							style="width:100%;font-family:monospace;font-size:13px;"
						><?php echo esc_textarea( get_option( $key, '' ) ); ?></textarea>
						<p class="description"><?php echo esc_html( $field['desc'] ); ?></p>
					</td>
				</tr>
				<?php endforeach; ?>
			</table>
			<?php submit_button( 'Opslaan' ); ?>
		</form>
	</div>
	<?php
}


/* ─────────────────────────────────────────
   ADS ROTATOR + CPT
───────────────────────────────────────── */

define( 'KASLEK_AD_CPT',          'kaslek_ad' );
define( 'KASLEK_AD_META_CAT',     '_kaslek_ad_category' );
define( 'KASLEK_AD_META_URL',     '_kaslek_ad_url' );
define( 'KASLEK_AD_META_HTML',    '_kaslek_ad_html' );
define( 'KASLEK_AD_META_IMPR',    '_kaslek_ad_impressions' );
define( 'KASLEK_AD_META_CLICKS',  '_kaslek_ad_clicks' );

function kaslek_rotator_register_cpt() {
	register_post_type( KASLEK_AD_CPT, [
		'labels' => [
			'name'          => 'Eigen & Affiliate Ads',
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
	] );
}
add_action( 'init', 'kaslek_rotator_register_cpt' );

function kaslek_rotator_add_metabox() {
	add_meta_box( 'kaslek_ad_settings', 'Ad Instellingen', 'kaslek_rotator_render_metabox', KASLEK_AD_CPT, 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'kaslek_rotator_add_metabox' );

function kaslek_rotator_render_metabox( $post ) {
	wp_nonce_field( 'kaslek_ad_save', 'kaslek_ad_nonce' );
	$cat  = get_post_meta( $post->ID, KASLEK_AD_META_CAT, true ) ?: 'eigen';
	$url  = get_post_meta( $post->ID, KASLEK_AD_META_URL, true );
	$html = get_post_meta( $post->ID, KASLEK_AD_META_HTML, true );
	?>
	<div style="margin-bottom:15px;">
		<label><strong>Type Ad:</strong></label><br>
		<select name="kaslek_ad_cat" id="kaslek_ad_cat" style="width:100%;max-width:400px;">
			<option value="eigen"     <?php selected( $cat, 'eigen' ); ?>>Eigen Ad (Afbeelding + Link)</option>
			<option value="affiliate" <?php selected( $cat, 'affiliate' ); ?>>Affiliate (HTML/Bol.com)</option>
		</select>
	</div>
	<div id="kaslek_section_url" style="margin-bottom:15px;">
		<label><strong>Doel URL:</strong></label><br>
		<input type="url" name="kaslek_ad_url" value="<?php echo esc_attr( $url ); ?>" style="width:100%">
	</div>
	<div id="kaslek_section_html">
		<label><strong>HTML Code:</strong></label><br>
		<textarea name="kaslek_ad_html" style="width:100%;height:150px;font-family:monospace;"><?php echo esc_textarea( $html ); ?></textarea>
	</div>
	<script>
	(function(){
		function t(){
			var v = document.getElementById('kaslek_ad_cat').value;
			document.getElementById('kaslek_section_url').style.display  = v === 'eigen' ? '' : 'none';
			document.getElementById('kaslek_section_html').style.display = v === 'eigen' ? 'none' : '';
		}
		document.getElementById('kaslek_ad_cat').addEventListener('change', t);
		t();
	})();
	</script>
	<?php
}

function kaslek_rotator_save_metabox( $post_id ) {
	if ( ! isset( $_POST['kaslek_ad_nonce'] ) || ! wp_verify_nonce( $_POST['kaslek_ad_nonce'], 'kaslek_ad_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( isset( $_POST['kaslek_ad_cat'] ) )  update_post_meta( $post_id, KASLEK_AD_META_CAT,  sanitize_text_field( $_POST['kaslek_ad_cat'] ) );
	if ( isset( $_POST['kaslek_ad_url'] ) )  update_post_meta( $post_id, KASLEK_AD_META_URL,  esc_url_raw( $_POST['kaslek_ad_url'] ) );
	if ( isset( $_POST['kaslek_ad_html'] ) ) update_post_meta( $post_id, KASLEK_AD_META_HTML, $_POST['kaslek_ad_html'] );
}
add_action( 'save_post_' . KASLEK_AD_CPT, 'kaslek_rotator_save_metabox' );

function kaslek_rotator_admin_columns( $cols ) {
	return [
		'cb'      => $cols['cb'],
		'title'   => $cols['title'],
		'ad_type' => 'Type',
		'stats'   => 'Impressies / Kliks',
		'author'  => $cols['author'],
		'date'    => $cols['date'],
	];
}
add_filter( 'manage_edit-' . KASLEK_AD_CPT . '_columns', 'kaslek_rotator_admin_columns' );

function kaslek_rotator_admin_column_values( $col, $post_id ) {
	if ( $col === 'ad_type' ) {
		$cat = get_post_meta( $post_id, KASLEK_AD_META_CAT, true ) ?: 'eigen';
		echo esc_html( ucfirst( $cat ) );
	} elseif ( $col === 'stats' ) {
		$impr  = (int) get_post_meta( $post_id, KASLEK_AD_META_IMPR, true );
		$click = (int) get_post_meta( $post_id, KASLEK_AD_META_CLICKS, true );
		echo '<strong>' . $impr . '</strong> / <strong>' . $click . '</strong>';
	}
}
add_action( 'manage_' . KASLEK_AD_CPT . '_posts_custom_column', 'kaslek_rotator_admin_column_values', 10, 2 );

function kaslek_rotator_report_menu() {
	add_submenu_page( 'edit.php?post_type=' . KASLEK_AD_CPT, 'Rapportage', 'Rapportage', 'edit_posts', 'kaslek-rotator-report', 'kaslek_rotator_report_page' );
}
add_action( 'admin_menu', 'kaslek_rotator_report_menu' );

function kaslek_rotator_report_page() {
	$ads   = get_posts( [ 'post_type' => KASLEK_AD_CPT, 'posts_per_page' => -1 ] );
	$stats = [];
	foreach ( $ads as $ad ) {
		$author = get_the_author_meta( 'display_name', $ad->post_author );
		if ( ! isset( $stats[ $author ] ) ) $stats[ $author ] = [ 'impr' => 0, 'click' => 0 ];
		$stats[ $author ]['impr']  += (int) get_post_meta( $ad->ID, KASLEK_AD_META_IMPR, true );
		$stats[ $author ]['click'] += (int) get_post_meta( $ad->ID, KASLEK_AD_META_CLICKS, true );
	}
	?>
	<div class="wrap">
		<h1>Rapportage per auteur (Eigen Ads)</h1>
		<table class="widefat striped" style="margin-top:20px;">
			<thead><tr><th>Auteur</th><th>Impressies</th><th>Kliks</th><th>CTR</th></tr></thead>
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

// Shortcode [kaslek_ad] — gebruikt echo zodat scripts niet worden gefilterd
function kaslek_rotator_shortcode() {
	$ads = get_posts( [ 'post_type' => KASLEK_AD_CPT, 'post_status' => 'publish', 'posts_per_page' => -1 ] );

	$idx = [ 'eigen' => [], 'affiliate' => [] ];
	foreach ( $ads as $ad ) {
		$c = get_post_meta( $ad->ID, KASLEK_AD_META_CAT, true ) ?: 'eigen';
		if ( isset( $idx[ $c ] ) ) $idx[ $c ][] = $ad->ID;
	}

	$has_google = ! empty( trim( get_option( 'kaslek_ad_vierkant', '' ) ) );

	$avail = [];
	foreach ( [ 'eigen' => 34, 'google' => 33, 'affiliate' => 33 ] as $k => $w ) {
		if ( $k === 'google' && $has_google )           { $avail[ $k ] = $w; continue; }
		if ( $k !== 'google' && ! empty( $idx[ $k ] ) ) { $avail[ $k ] = $w; }
	}
	if ( ! $avail ) return '';

	$state  = get_option( 'kaslek_rr_state', [ 'eigen' => 0, 'google' => 0, 'affiliate' => 0 ] );
	$chosen = array_key_first( $avail );
	$max    = -999999;
	foreach ( $avail as $k => $w ) {
		$state[ $k ] = ( $state[ $k ] ?? 0 ) + $w;
		if ( $state[ $k ] > $max ) { $max = $state[ $k ]; $chosen = $k; }
	}
	$state[ $chosen ] -= 100;
	update_option( 'kaslek_rr_state', $state );

	// Google: ob_start vangt echo van kaslek_get_ad() op zodat het als return werkt
	if ( $chosen === 'google' ) {
		ob_start();
		kaslek_get_ad( 'vierkant' );
		return '<div class="kaslek-ad-external">' . ob_get_clean() . '</div>';
	}

	$ad_id = $idx[ $chosen ][ array_rand( $idx[ $chosen ] ) ];

	if ( $chosen === 'eigen' ) {
		$impr = (int) get_post_meta( $ad_id, KASLEK_AD_META_IMPR, true );
		update_post_meta( $ad_id, KASLEK_AD_META_IMPR, $impr + 1 );
		$url = add_query_arg( [ 'kaslek_click' => $ad_id ], home_url( '/' ) );
		return sprintf( '<a href="%s" target="_blank" rel="sponsored">%s</a>', esc_url( $url ), get_the_post_thumbnail( $ad_id, 'full' ) );
	}

	// Affiliate
	return '<div class="kaslek-ad-external">' . get_post_meta( $ad_id, KASLEK_AD_META_HTML, true ) . '</div>';
}
add_shortcode( 'kaslek_ad', 'kaslek_rotator_shortcode' );

function kaslek_rotator_click_redirect() {
	if ( isset( $_GET['kaslek_click'] ) ) {
		$id     = absint( $_GET['kaslek_click'] );
		$clicks = (int) get_post_meta( $id, KASLEK_AD_META_CLICKS, true );
		update_post_meta( $id, KASLEK_AD_META_CLICKS, $clicks + 1 );
		$target = get_post_meta( $id, KASLEK_AD_META_URL, true );
		if ( $target ) { wp_redirect( esc_url_raw( $target ) ); exit; }
	}
}
add_action( 'template_redirect', 'kaslek_rotator_click_redirect' );


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
