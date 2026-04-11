<?php get_header(); ?>

<?php
/* ── Context bepalen ── */
$archive_type  = '';
$archive_title = '';
$archive_desc  = '';
$archive_tax   = '';
$archive_term  = '';
$dossier_link  = '';

$is_mobile = ( isset( $_SERVER['HTTP_X_WP_VIEWPORT_WIDTH'] ) && (int) $_SERVER['HTTP_X_WP_VIEWPORT_WIDTH'] < 768 )
	|| wp_is_mobile();

$dossier_slugs = [
	'belastingen-en-heffingen', 'consultants-en-externe-inhuur', 'defensie-en-militaire-steun',
	'energie-en-klimaat', 'feitje', 'infrastructuurprojecten', 'migratie-en-opvang',
	'onderwijs', 'overheid-ict-en-digitalisering', 'politiek-en-bestuur',
	'stikstof-en-natuur', 'subsidies-en-fondsen', 'veiligheid-en-handhaving', 'zorg-en-sociaal',
];

if ( is_tag() ) {
	$archive_type  = 'tag';
	$term          = get_queried_object();
	$archive_title = $term->name;
	$archive_desc  = $term->description;
	$archive_tax   = 'post_tag';
	$archive_term  = $term->slug;
	if ( in_array( $term->slug, $dossier_slugs, true ) ) {
		$dossier_page = get_page_by_path( 'dossiers' );
		if ( $dossier_page ) {
			$dossier_link = get_permalink( $dossier_page ) . '#' . $term->slug;
		}
	}
} elseif ( is_category() ) {
	$archive_type  = 'category';
	$term          = get_queried_object();
	$archive_title = $term->name;
	$archive_tax   = 'category';
	$archive_term  = $term->slug;
} elseif ( is_author() ) {
	$archive_type  = 'author';
	$author        = get_queried_object();
	$archive_title = $author->display_name;
	$archive_desc  = get_the_author_meta( 'description', $author->ID );
}

/* ── Alle posts laden ── */
$query_args = [
	'posts_per_page' => -1,
	'post_status'    => 'publish',
	'orderby'        => 'date',
	'order'          => 'DESC',
];

if ( $archive_type === 'tag' ) {
	$query_args['tag_slug__in'] = [ $archive_term ];
} elseif ( $archive_type === 'category' ) {
	$query_args['category_name'] = $archive_term;
} elseif ( $archive_type === 'author' ) {
	$query_args['author'] = get_queried_object_id();
}

$archive_q    = new WP_Query( $query_args );
$archive_posts = [];
while ( $archive_q->have_posts() ) {
	$archive_q->the_post();
	$archive_posts[] = get_post();
}
wp_reset_postdata();

$total_posts     = count( $archive_posts );
$mobile_hero_post = $archive_posts[0] ?? null;

/* ── Meest gelezen: gefilterd op archief + 21 dagen, fallback 90 dagen binnen zelfde archief ── */
$trending_args = [
	'posts_per_page' => 5,
	'post_status'    => 'publish',
	'meta_key'       => 'post_views_count',
	'orderby'        => 'meta_value_num',
	'order'          => 'DESC',
	'date_query'     => [ [ 'after' => '21 days ago', 'inclusive' => true ] ],
];
if ( $archive_type === 'tag' && $archive_term ) {
	$trending_args['tag_slug__in'] = [ $archive_term ];
} elseif ( $archive_type === 'category' && $archive_term ) {
	$trending_args['category_name'] = $archive_term;
} elseif ( $archive_type === 'author' ) {
	$trending_args['author'] = get_queried_object_id();
}
$trending_q = new WP_Query( $trending_args );
$trending_posts = [];
while ( $trending_q->have_posts() ) {
	$trending_q->the_post();
	$trending_posts[] = get_post();
}
wp_reset_postdata();
// Fallback: zelfde categorie/tag, 90 dagen
if ( count( $trending_posts ) < 5 ) {
	$trending_fallback_args = [
		'posts_per_page' => 5,
		'post_status'    => 'publish',
		'meta_key'       => 'post_views_count',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
		'date_query'     => [ [ 'after' => '90 days ago', 'inclusive' => true ] ],
	];
	if ( $archive_type === 'tag' && $archive_term ) {
		$trending_fallback_args['tag_slug__in'] = [ $archive_term ];
	} elseif ( $archive_type === 'category' && $archive_term ) {
		$trending_fallback_args['category_name'] = $archive_term;
	} elseif ( $archive_type === 'author' ) {
		$trending_fallback_args['author'] = get_queried_object_id();
	}
	$trending_q2 = new WP_Query( $trending_fallback_args );
	$trending_posts = [];
	while ( $trending_q2->have_posts() ) {
		$trending_q2->the_post();
		$trending_posts[] = get_post();
	}
	wp_reset_postdata();
}
?>

<div class="archive-wrap">

	<!-- CATEGORIE LABEL (mobiel) -->
	<?php if ( $is_mobile && $archive_title ) : ?>
	<div class="archive-mobile-label ad-show-mobile">
		<span class="archive-mobile-label-type"><?php
			if ( $archive_type === 'tag' )          echo 'Dossier';
			elseif ( $archive_type === 'category' ) echo 'Categorie';
			elseif ( $archive_type === 'author' )   echo 'Auteur';
		?></span>
		<span class="archive-mobile-label-title"><?php echo esc_html( $archive_title ); ?></span>
	</div>
	<?php endif; ?>

	<!-- MOBILE HERO (meest recente artikel) -->
	<?php if ( $mobile_hero_post ) : ?>
	<a href="<?php echo esc_url( get_permalink( $mobile_hero_post ) ); ?>" class="archive-mobile-hero ad-show-mobile" style="text-decoration:none;">
		<div class="archive-mobile-hero-content">
			<h1 class="archive-mobile-hero-title"><?php echo esc_html( get_the_title( $mobile_hero_post ) ); ?></h1>
			<p class="archive-mobile-hero-excerpt"><?php echo esc_html( get_the_excerpt( $mobile_hero_post ) ); ?></p>
			<span class="hero-main-btn">Verhaal lezen</span>
		</div>
		<div class="archive-mobile-hero-img">
			<div class="archive-mobile-hero-overlay"></div>
			<?php if ( has_post_thumbnail( $mobile_hero_post ) ) : ?>
			<img src="<?php echo esc_url( get_the_post_thumbnail_url( $mobile_hero_post, 'large' ) ); ?>"
			     alt="<?php echo esc_attr( get_the_title( $mobile_hero_post ) ); ?>"
			     style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;object-position:center top;display:block;max-width:none;">
			<?php endif; ?>
		</div>
	</a>
	<?php endif; ?>

	<?php if ( $is_mobile ) : ?><div class="kaslek-ad-between"><ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="4772512111" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script></div><?php endif; ?>

	<!-- HEADER -->
	<?php if ( ! $is_mobile ) : ?>
	<div class="archive-hero">
		<div class="archive-hero-inner">
			<div class="archive-hero-label">
				<?php
				if ( $archive_type === 'tag' )      echo 'Dossier';
				elseif ( $archive_type === 'category' ) echo 'Categorie';
				elseif ( $archive_type === 'author' )   echo 'Auteur';
				?>
			</div>
			<h1 class="archive-hero-title"><?php echo esc_html( $archive_title ); ?></h1>
			<?php if ( $archive_desc ) : ?>
			<p class="archive-hero-desc"><?php echo esc_html( $archive_desc ); ?></p>
			<?php endif; ?>
			<div class="archive-hero-count"><?php echo number_format_i18n( $total_posts ); ?> verhalen</div>
		</div>
	</div>
	<?php endif; ?>

	<!-- CONTENT + SIDEBAR -->
	<div class="archive-layout">
		<div class="archive-main">

			<?php if ( empty( $archive_posts ) ) : ?>
			<p class="archive-empty">Geen verhalen gevonden.</p>
			<?php else : ?>


			<div id="infinite-scroll-container">
			<?php
			// Op mobiel: post 1 staat al in de hero, dus lijst begint bij post 2
			$list_posts = $is_mobile ? array_slice( $archive_posts, 1 ) : $archive_posts;
			$p0   = $list_posts[0] ?? null;
			$p1   = $list_posts[1] ?? null;
			$p2   = $list_posts[2] ?? null;
			$row2 = array_slice( $list_posts, 3, 3 );
			$row3 = array_slice( $list_posts, 6, 3 );
			$rest = array_slice( $list_posts, 9 );

			// Post 1: grote featured card, volle breedte
			if ( $p0 ) : ?>
			<a href="<?php echo esc_url( get_permalink( $p0 ) ); ?>" class="archive-featured" style="text-decoration:none;">
				<div class="archive-featured-img">
					<?php if ( has_post_thumbnail( $p0 ) ) : ?>
					<img src="<?php echo esc_url( get_the_post_thumbnail_url( $p0, 'large' ) ); ?>"
					     alt="<?php echo esc_attr( get_the_title( $p0 ) ); ?>"
					     style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;object-position:center top;display:block;max-width:none;">
					<?php endif; ?>
					<div class="archive-featured-overlay"></div>
					<div class="archive-featured-body">
						<h2 class="archive-featured-title"><?php echo esc_html( get_the_title( $p0 ) ); ?></h2>
						<p class="archive-featured-excerpt"><?php echo esc_html( get_the_excerpt( $p0 ) ); ?></p>
					</div>
				</div>
			</a>
			<?php endif; ?>

			<?php if ( $is_mobile ) : ?><div class="kaslek-ad-between"><ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="4772512111" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script></div><?php endif; ?>

<?php // Posts 2+3: 2 middelgrote cards naast elkaar
			if ( $p1 || $p2 ) : ?>
			<div class="archive-duo">
				<?php foreach ( array_filter( [ $p1, $p2 ] ) as $post ) : ?>
				<a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="archive-duo-card" style="text-decoration:none;">
					<div class="archive-duo-img" style="<?php if ( has_post_thumbnail( $post ) ) echo 'background-image:url(' . esc_url( get_the_post_thumbnail_url( $post, 'medium_large' ) ) . ');'; ?>"></div>
					<div class="archive-duo-body">
						<h2 class="archive-duo-title"><?php echo esc_html( get_the_title( $post ) ); ?></h2>
						<p class="archive-duo-excerpt"><?php echo esc_html( get_the_excerpt( $post ) ); ?></p>
					</div>
				</a>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<?php if ( $is_mobile ) : ?><div class="kaslek-ad-between"><ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="4772512111" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script></div><?php endif; ?>

<?php // Posts 4-6: rij van 3, alleen titel
			if ( $row2 ) : ?>
			<div class="scroll-grid archive-text-row">
				<?php foreach ( $row2 as $post ) : ?>
				<a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="scroll-card archive-text-card" style="text-decoration:none;">
					<div class="scroll-card-content">
						<span class="scroll-card-title"><?php echo esc_html( get_the_title( $post ) ); ?></span>
					</div>
				</a>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<?php if ( $is_mobile ) : ?><div class="kaslek-ad-between"><ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="4772512111" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script></div><?php endif; ?>
			<?php if ( ! $is_mobile ) : ?><div class="kaslek-ad-desktop-row"><ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="9402022262" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script></div><?php endif; ?>

<?php // Posts 7-9: rij van 3 met afbeelding + titel
			if ( $row3 ) :
				set_query_var( 'archive_row_posts', $row3 );
				get_template_part( 'template-parts/archive', 'row' );
			endif; ?>

			<?php if ( $is_mobile ) : ?><div class="kaslek-ad-between"><ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="4772512111" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script></div><?php endif; ?>
			<?php if ( ! $is_mobile ) : ?><div class="kaslek-ad-desktop-row"><ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="9402022262" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script></div><?php endif; ?>

<?php // Rest: afwisselend patroon
			if ( $rest ) :
				$chunks        = array_chunk( $rest, $is_mobile ? 4 : 11 );
				$block_size_a  = $is_mobile ? 3 : 5;
				foreach ( $chunks as $chunk_idx => $chunk ) :
					if ( $chunk_idx > 0 ) : ?>
					<?php if ( $is_mobile ) : ?><div class="kaslek-ad-between"><ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="4772512111" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script></div><?php endif; ?>
					<?php if ( ! $is_mobile ) : ?><div class="kaslek-ad-desktop-row"><ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="9402022262" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script></div><?php endif; ?>
					<?php endif;
					$part_a = array_slice( $chunk, 0, $block_size_a );
					$part_b = array_slice( $chunk, $block_size_a );

					// Blok A: card-horizontal
					foreach ( $part_a as $post ) :
						setup_postdata( $post ); ?>
					<div class="archive-card-fadein">
						<?php get_template_part( 'template-parts/card', 'horizontal' ); ?>
					</div>
					<?php endforeach;

					// Blok B: featured stijl
					if ( $part_b ) : ?>
					<div class="archive-alt-block archive-card-fadein">
						<?php foreach ( $part_b as $i => $post ) :
							setup_postdata( $post ); ?>
						<?php if ( $is_mobile ) : ?>
						<a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="archive-alt-card-mobile" style="text-decoration:none;">
							<?php if ( has_post_thumbnail( $post ) ) : ?>
							<div class="archive-alt-img-mobile">
								<img src="<?php echo esc_url( get_the_post_thumbnail_url( $post, 'large' ) ); ?>"
								     alt="<?php echo esc_attr( get_the_title( $post ) ); ?>"
								     style="width:100%;height:100%;object-fit:cover;object-position:center top;display:block;">
							</div>
							<?php endif; ?>
							<div class="archive-alt-body-mobile">
								<h2 class="archive-alt-title-mobile"><?php echo esc_html( get_the_title( $post ) ); ?></h2>
								<p class="archive-alt-excerpt-mobile"><?php echo esc_html( get_the_excerpt( $post ) ); ?></p>
							</div>
						</a>
						<?php else : ?>
						<?php if ( $i === 0 ) : ?>
						<a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="archive-alt-wide" style="text-decoration:none;">
							<?php if ( has_post_thumbnail( $post ) ) : ?>
							<div class="archive-alt-wide-img">
								<img src="<?php echo esc_url( get_the_post_thumbnail_url( $post, 'large' ) ); ?>"
								     alt="<?php echo esc_attr( get_the_title( $post ) ); ?>"
								     style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;display:block;max-width:none;">
							</div>
							<?php endif; ?>
							<h2 class="archive-alt-wide-title"><?php echo esc_html( get_the_title( $post ) ); ?></h2>
							<p class="archive-alt-wide-excerpt"><?php echo esc_html( get_the_excerpt( $post ) ); ?></p>
						</a>
						<?php elseif ( $i === 1 || $i === 2 ) : ?>
						<?php if ( $i === 1 ) echo '<div class="archive-alt-duo">'; ?>
						<a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="archive-duo-card" style="text-decoration:none;">
							<div class="archive-duo-img" style="<?php if ( has_post_thumbnail( $post ) ) echo 'background-image:url(' . esc_url( get_the_post_thumbnail_url( $post, 'medium_large' ) ) . ');'; ?>"></div>
							<div class="archive-duo-body">
								<h2 class="archive-duo-title"><?php echo esc_html( get_the_title( $post ) ); ?></h2>
								<p class="archive-duo-excerpt"><?php echo esc_html( get_the_excerpt( $post ) ); ?></p>
							</div>
						</a>
						<?php if ( $i === 2 ) echo '</div>'; ?>
						<?php else : ?>
						<?php if ( $i === 3 ) echo '<div class="scroll-grid archive-text-row">'; ?>
						<a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="scroll-card archive-text-card" style="text-decoration:none;">
							<div class="scroll-card-content">
								<span class="scroll-card-title"><?php echo esc_html( get_the_title( $post ) ); ?></span>
							</div>
						</a>
						<?php if ( $i === 5 || $i === count( $part_b ) - 1 ) echo '</div>'; ?>
						<?php endif; ?>
						<?php endif; ?>
						<?php endforeach; wp_reset_postdata(); ?>
					</div>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>

			</div>

			<?php endif; ?>
		</div>

		<div class="archive-sidebar">
			<?php if ( $trending_posts ) : ?>
			<div class="sidebar-widget">
				<div class="sidebar-widget-title">Meest gelezen</div>
				<?php foreach ( $trending_posts as $i => $post ) : ?>
				<div class="sidebar-item">
					<div class="sidebar-num"><?php echo $i + 1; ?></div>
					<div>
						<a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="sidebar-title"><?php echo esc_html( get_the_title( $post ) ); ?></a>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>
	</div>

</div>

<?php get_footer(); ?>
