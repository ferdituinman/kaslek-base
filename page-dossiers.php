<?php
/**
 * Template Name: Dossiers
 */
get_header(); ?>

<?php
$dossiers = [
	[ 'slug' => 'defensie-en-militaire-steun',       'label' => 'Defensie & militaire steun',     'size' => 'large',  'img' => 'https://kaslek.nl/wp-content/uploads/2026/03/bSFRPThPwd2WN5wlRiW1w_PW3dOQOp.jpg' ],
	[ 'slug' => 'energie-en-klimaat',                'label' => 'Energie & klimaat',               'size' => 'large',  'img' => 'https://kaslek.nl/wp-content/uploads/2026/03/WAqQLiA51rLV7iSOfuRsF_CkyFhG9K.jpg' ],
	[ 'slug' => 'migratie-en-opvang',                'label' => 'Migratie & opvang',               'size' => 'medium', 'img' => 'https://kaslek.nl/wp-content/uploads/2026/02/qDHMzHcc2TRQGeFawEmTc.jpg' ],
	[ 'slug' => 'infrastructuurprojecten',            'label' => 'Infrastructuurprojecten',          'size' => 'medium', 'img' => 'https://kaslek.nl/wp-content/uploads/2026/01/ePKhHFKYy536Xt0prF-q_.jpg' ],
	[ 'slug' => 'consultants-en-externe-inhuur',     'label' => 'Consultants & externe inhuur',    'size' => 'medium', 'img' => 'https://kaslek.nl/wp-content/uploads/2026/03/ipivJ9c_E2z_OCE10g6Vx_hCrB324i.jpg' ],
	[ 'slug' => 'subsidies-en-fondsen',              'label' => 'Subsidies & fondsen',             'size' => 'small',  'img' => 'https://kaslek.nl/wp-content/uploads/2026/03/SYcFJQRksSWO470_aA10f_7UKCIBz4.jpg' ],
	[ 'slug' => 'overheid-ict-en-digitalisering',    'label' => 'Overheid ICT & digitalisering',   'size' => 'small',  'img' => 'https://kaslek.nl/wp-content/uploads/2026/03/wfTNbfR1d4tWoGyHm3CrQ_9ISJn4V4.jpg' ],
	[ 'slug' => 'onderwijs',                         'label' => 'Onderwijs',                       'size' => 'half',   'img' => 'https://kaslek.nl/wp-content/uploads/2026/03/hHRihQVeSpGoi_gOxzV5W_t8nM4PeB.jpg' ],
	[ 'slug' => 'veiligheid-en-handhaving',          'label' => 'Veiligheid & handhaving',         'size' => 'half',   'img' => 'https://kaslek.nl/wp-content/uploads/2026/03/ToJDLRoOczcgLgmgnhIly_hnZNE51r.jpg' ],
	[ 'slug' => 'zorg-en-sociaal',                   'label' => 'Zorg & sociaal',                  'size' => 'third-wide', 'img' => 'https://kaslek.nl/wp-content/uploads/2026/03/ooXXtVz2seglpncDBt2xb_t8rN6by5.jpg' ],
	[ 'slug' => 'stikstof-en-natuur',                'label' => 'Stikstof & natuur',               'size' => 'third',  'img' => 'https://kaslek.nl/wp-content/compressx-nextgen/uploads/2026/03/SYcFJQRksSWO470_aA10f_7UKCIBz4.jpg.webp' ],
	[ 'slug' => 'belastingen-en-heffingen',          'label' => 'Belastingen & heffingen',         'size' => 'third',  'img' => 'https://kaslek.nl/wp-content/compressx-nextgen/uploads/2026/03/LL5hLHw2Mi9ulWbk1q_Fn_K4cJsGux.jpg.webp' ],
];
?>

<div class="dossiers-wrap">

	<div class="dossiers-hero">
		<div class="dossiers-hero-inner">
			<div class="dossiers-hero-label">Verdieping</div>
			<h1 class="dossiers-hero-title">Dossiers</h1>
			<p class="dossiers-hero-sub">Elk dossier bundelt alle verhalen over één onderwerp. Van aanbestedingen tot klimaatbeleid: geordend, doorzoekbaar, controleerbaar.</p>
		</div>
	</div>

	<div class="dossiers-content">
		<?php
		$main_dossiers  = array_filter( $dossiers, fn($d) => ! in_array( $d['size'], [ 'third', 'third-wide' ] ) );
		$third_dossiers = array_filter( $dossiers, fn($d) => in_array( $d['size'], [ 'third', 'third-wide' ] ) );
		?>
		<div class="dossiers-grid">
			<?php foreach ( $main_dossiers as $dossier ) :
				$tag   = get_term_by( 'slug', $dossier['slug'], 'post_tag' );
				$url   = $tag ? get_tag_link( $tag->term_id ) : home_url( '/dossiers/' . $dossier['slug'] . '/' );
				$count = $tag ? $tag->count : 0;
			?>
			<a href="<?php echo esc_url( $url ); ?>"
			   class="dossier-card dossier-card--<?php echo esc_attr( $dossier['size'] ); ?>"
			   style="background-image:url('<?php echo esc_url( $dossier['img'] ); ?>');">
				<div class="dossier-card-overlay"></div>
				<div class="dossier-card-body">
					<h2 class="dossier-card-title"><?php echo esc_html( $dossier['label'] ); ?></h2>
					<?php if ( $count > 0 ) : ?>
					<span class="dossier-card-count"><?php echo $count; ?> verhalen</span>
					<?php endif; ?>
					<span class="dossier-card-arrow">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
					</span>
				</div>
			</a>
			<?php endforeach; ?>
		</div>

		<?php if ( $third_dossiers ) : ?>
		<div class="dossiers-grid-thirds">
			<?php foreach ( $third_dossiers as $dossier ) :
				$tag   = get_term_by( 'slug', $dossier['slug'], 'post_tag' );
				$url   = $tag ? get_tag_link( $tag->term_id ) : home_url( '/dossiers/' . $dossier['slug'] . '/' );
				$count = $tag ? $tag->count : 0;
			?>
			<a href="<?php echo esc_url( $url ); ?>"
			   class="dossier-card dossier-card--third"
			   style="background-image:url('<?php echo esc_url( $dossier['img'] ); ?>');">
				<div class="dossier-card-overlay"></div>
				<div class="dossier-card-body">
					<h2 class="dossier-card-title"><?php echo esc_html( $dossier['label'] ); ?></h2>
					<?php if ( $count > 0 ) : ?>
					<span class="dossier-card-count"><?php echo $count; ?> verhalen</span>
					<?php endif; ?>
					<span class="dossier-card-arrow">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
					</span>
				</div>
			</a>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
	</div>


</div>

<?php get_footer(); ?>
