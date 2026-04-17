<?php get_header(); ?>

<main class="site-main">

<?php
$shown_ids = [];

// ── HERO ──
$hero_q = new WP_Query([
	'posts_per_page' => 5,
	'post_status'    => 'publish',
	'post__not_in'   => [1080],
	'orderby'        => 'date',
	'order'          => 'DESC',
]);
$hero_posts = [];
while ( $hero_q->have_posts() ) {
	$hero_q->the_post();
	$hero_posts[] = get_post();
}
wp_reset_postdata();
$hero_main  = $hero_posts[0] ?? null;
$hero_stack = array_slice( $hero_posts, 1, 4 );
if ( $hero_main ) $shown_ids[] = $hero_main->ID;
foreach ( $hero_stack as $s ) $shown_ids[] = $s->ID;
?>

<?php if ( $hero_main ) : ?>
<section class="hero-section">

	<a href="<?php echo esc_url( get_permalink( $hero_main ) ); ?>" class="hero-main" style="position:relative;display:block;aspect-ratio:10/7;background:var(--primary);">
		<div class="hero-img-wrap">
		<?php if ( has_post_thumbnail( $hero_main ) ) : ?>
			<img src="<?php echo esc_url( get_the_post_thumbnail_url( $hero_main, 'large' ) ); ?>"
			     alt="<?php echo esc_attr( get_the_title( $hero_main ) ); ?>"
			     style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;display:block;max-width:none;"
			     loading="eager"
			     fetchpriority="high"
			     decoding="async">
		<?php endif; ?>
		<div class="hero-overlay"></div>
		</div>
		<div class="hero-main-content" style="position:absolute;bottom:0;left:0;right:0;z-index:2;padding:28px;">
			<h2 class="hero-main-title"><?php echo esc_html( get_the_title( $hero_main ) ); ?></h2>
			<div class="hero-main-excerpt"><?php echo esc_html( get_the_excerpt( $hero_main ) ); ?></div>
			<span class="hero-main-btn">Verhaal lezen</span>
		</div>
	</a>

	<div class="hero-stack">
		<?php foreach ( $hero_stack as $item ) : ?>
		<a href="<?php echo esc_url( get_permalink( $item ) ); ?>" class="hero-stack-item">
			<div class="hero-stack-img" style="width:110px;height:73px;flex-shrink:0;overflow:hidden;position:relative;">
				<?php if ( has_post_thumbnail( $item ) ) : ?>
					<img src="<?php echo esc_url( get_the_post_thumbnail_url( $item, 'medium_large' ) ); ?>"
					     alt="<?php echo esc_attr( get_the_title( $item ) ); ?>"
					     style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;display:block;max-width:none;">
				<?php endif; ?>
			</div>
			<div class="hero-stack-text">
				<div class="hero-stack-title"><?php echo esc_html( get_the_title( $item ) ); ?></div>
			</div>
		</a>
		<?php endforeach; ?>
	</div>

</section>
<?php endif; ?>

<div class="content">

	<?php
	// ── MEEST GELEZEN (7 dagen, mag doubleures) ──
	$trending_q = new WP_Query([
		'posts_per_page' => 5,
		'post_status'    => 'publish',
		'meta_key'       => 'post_views_count',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
		'date_query'     => [ [ 'after' => '7 days ago', 'inclusive' => true ] ],
	]);
	$trending_posts = [];
	while ( $trending_q->have_posts() ) {
		$trending_q->the_post();
		$trending_posts[] = get_post();
	}
	wp_reset_postdata();
	?>

	<!-- DRIE CATEGORIEKOLOMMEN -->
	<div class="cat-grid">
		<?php
		$kolommen = [
			[ 'label' => 'Laatste nieuws',     'cat' => 'nieuws' ],
			[ 'label' => 'Bijzondere feitjes', 'cat' => 'bijzonder' ],
			[ 'label' => 'Spraakmakend',        'cat' => 'spraakmakend' ],
		];
		foreach ( $kolommen as $kol ) :
			$kol_q = new WP_Query([
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				'category_name'  => $kol['cat'],
				'post__not_in'   => $shown_ids,
				'orderby'        => 'date',
				'order'          => 'DESC',
			]);
			$kol_posts = [];
			while ( $kol_q->have_posts() ) {
				$kol_q->the_post();
				$kol_posts[] = get_post();
				$shown_ids[] = get_the_ID();
			}
			wp_reset_postdata();
			if ( ! $kol_posts ) continue;
			$kol_featured = $kol_posts[0];
			$kol_rest     = array_slice( $kol_posts, 1 );
		?>
		<div class="cat-col">
			<div class="cat-col-header">
				<h2><a href="<?php echo esc_url( get_category_link( get_cat_ID( $kol['cat'] ) ) ); ?>" style="color:inherit;text-decoration:none;"><?php echo esc_html( $kol['label'] ); ?></a></h2>
			</div>
			<a href="<?php echo esc_url( get_permalink( $kol_featured ) ); ?>" class="col-featured" style="display:block;text-decoration:none;">
				<div style="height:160px;overflow:hidden;position:relative;margin-bottom:10px;background:#1B3E5F;">
					<?php if ( has_post_thumbnail( $kol_featured ) ) : ?>
						<img src="<?php echo esc_url( get_the_post_thumbnail_url( $kol_featured, 'medium_large' ) ); ?>"
						     alt="<?php echo esc_attr( get_the_title( $kol_featured ) ); ?>"
						     style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;display:block;max-width:none;">
					<?php endif; ?>
				</div>
				<div class="col-featured-title"><?php echo esc_html( get_the_title( $kol_featured ) ); ?></div>
			</a>
			<!-- ads rotator -->
			<?php foreach ( $kol_rest as $item ) : ?>
			<a href="<?php echo esc_url( get_permalink( $item ) ); ?>" class="col-item" style="text-decoration:none;">
				<?php if ( has_post_thumbnail( $item ) ) : ?>
				<div style="width:96px;height:64px;flex-shrink:0;overflow:hidden;position:relative;background:#1B3E5F;">
					<img src="<?php echo esc_url( get_the_post_thumbnail_url( $item, 'medium_large' ) ); ?>"
					     alt="<?php echo esc_attr( get_the_title( $item ) ); ?>"
					     style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;display:block;max-width:none;">
				</div>
				<?php endif; ?>
				<div class="col-item-title"><?php echo esc_html( get_the_title( $item ) ); ?></div>
			</a>
			<?php endforeach; ?>
		</div>
		<?php endforeach; ?>
	</div>

	<?php if ( wp_is_mobile() ) : ?><div class="kaslek-ad-between"><ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="4772512111" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script></div><?php endif; ?>

	<!-- MEEST GELEZEN MOBIEL -->
	<div class="sidebar-widget ad-show-mobile mobile-trending">
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

	<?php if ( wp_is_mobile() ) : ?><div class="kaslek-ad-between"><ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="4772512111" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script></div><?php endif; ?>

	<?php if ( ! wp_is_mobile() ) : ?><div class="kaslek-ad-desktop-row"><ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="4772512111" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script></div><?php endif; ?>

	<!-- MEER TRANSPARANTIE -->
	<?php
	$transparantie_q = new WP_Query([
		'posts_per_page' => 4,
		'post_status'    => 'publish',
		'post__not_in'   => $shown_ids,
		'orderby'        => 'date',
		'order'          => 'DESC',
	]);
	$tr_posts = [];
	while ( $transparantie_q->have_posts() ) {
		$transparantie_q->the_post();
		$tr_posts[]  = get_post();
		$shown_ids[] = get_the_ID();
	}
	wp_reset_postdata();
	?>
	<div class="main-sidebar">
		<div class="main-col">
			<div class="main-col-header">
				<h2>Meer transparantie</h2>
			</div>
			<?php foreach ( $tr_posts as $i => $post ) : ?>
			<div class="textlist-item">
				<div class="textlist-num"><?php echo $i + 1; ?></div>
				<div class="textlist-img" style="width:180px;height:144px;flex-shrink:0;overflow:hidden;position:relative;background:#1B3E5F;">
					<?php if ( has_post_thumbnail( $post ) ) : ?>
						<img src="<?php echo esc_url( get_the_post_thumbnail_url( $post, 'medium_large' ) ); ?>"
						     alt="<?php echo esc_attr( get_the_title( $post ) ); ?>"
						     style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;display:block;max-width:none;">
					<?php endif; ?>
				</div>
				<div class="textlist-text">
					<a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="textlist-title"><?php echo esc_html( get_the_title( $post ) ); ?></a>
					<p class="textlist-excerpt"><?php echo esc_html( get_the_excerpt( $post ) ); ?></p>
				</div>
			</div>
			<?php endforeach; ?>
		</div>

		<div class="sidebar">
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

		</div>
	</div>

	<?php if ( wp_is_mobile() ) : ?><div class="kaslek-ad-between"><ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="4772512111" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script></div><?php endif; ?>
	<?php if ( ! wp_is_mobile() ) : ?><div class="kaslek-ad-desktop-row"><ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="4772512111" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script></div><?php endif; ?>

	<!-- MEER VERHALEN -->
	<?php
	$scroll_q = new WP_Query([
		'posts_per_page' => 6,
		'post_status'    => 'publish',
		'post__not_in'   => $shown_ids,
		'orderby'        => 'date',
		'order'          => 'DESC',
	]);
	$scroll_posts = [];
	while ( $scroll_q->have_posts() ) {
		$scroll_q->the_post();
		$scroll_posts[] = get_post();
		$shown_ids[]    = get_the_ID();
	}
	wp_reset_postdata();

	$extra_q = new WP_Query([
		'posts_per_page' => 2,
		'post_status'    => 'publish',
		'post__not_in'   => $shown_ids,
		'orderby'        => 'date',
		'order'          => 'DESC',
	]);
	$extra_posts = [];
	while ( $extra_q->have_posts() ) {
		$extra_q->the_post();
		$extra_posts[] = get_post();
		$shown_ids[]   = get_the_ID();
	}
	wp_reset_postdata();
	?>
	<div class="scroll-section">
		<div class="scroll-header">
			<h2>Meer verhalen</h2>
			<div class="scroll-header-line"></div>
		</div>
		<div class="scroll-grid">
			<?php foreach ( array_slice( $scroll_posts, 0, 3 ) as $post ) : ?>
			<a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="scroll-card" style="text-decoration:none;">
				<div style="aspect-ratio:3/2;overflow:hidden;position:relative;background:#1B3E5F;">
					<?php if ( has_post_thumbnail( $post ) ) : ?>
						<img src="<?php echo esc_url( get_the_post_thumbnail_url( $post, 'medium_large' ) ); ?>"
						     alt="<?php echo esc_attr( get_the_title( $post ) ); ?>"
						     style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;display:block;max-width:none;">
					<?php endif; ?>
				</div>
				<div class="scroll-card-content">
					<span class="scroll-card-title"><?php echo esc_html( get_the_title( $post ) ); ?></span>
					<p class="scroll-card-excerpt"><?php echo esc_html( get_the_excerpt( $post ) ); ?></p>
				</div>
			</a>
			<?php endforeach; ?>
		</div>
		<?php if ( wp_is_mobile() ) : ?><div class="kaslek-ad-between"><ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="4772512111" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script></div><?php endif; ?>
		<?php if ( ! wp_is_mobile() ) : ?><div class="kaslek-ad-desktop-row"><ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="4772512111" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script></div><?php endif; ?>
		<?php if ( $extra_posts ) : ?>
		<?php if ( wp_is_mobile() ) : ?>
			<?php foreach ( $extra_posts as $i => $post ) : ?>
			<div class="scroll-grid">
				<a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="scroll-card" style="text-decoration:none;">
					<div style="aspect-ratio:3/2;overflow:hidden;position:relative;background:#1B3E5F;">
						<?php if ( has_post_thumbnail( $post ) ) : ?>
							<img src="<?php echo esc_url( get_the_post_thumbnail_url( $post, 'medium_large' ) ); ?>"
							     alt="<?php echo esc_attr( get_the_title( $post ) ); ?>"
							     style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;display:block;max-width:none;">
						<?php endif; ?>
					</div>
					<div class="scroll-card-content">
						<span class="scroll-card-title"><?php echo esc_html( get_the_title( $post ) ); ?></span>
						<p class="scroll-card-excerpt"><?php echo esc_html( get_the_excerpt( $post ) ); ?></p>
					</div>
				</a>
			</div>
			<?php endforeach; ?>
		<?php else : ?>
		<div class="scroll-grid scroll-grid-2">
			<?php foreach ( $extra_posts as $post ) : ?>
			<a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="scroll-card" style="text-decoration:none;">
				<div style="aspect-ratio:3/2;overflow:hidden;position:relative;background:#1B3E5F;">
					<?php if ( has_post_thumbnail( $post ) ) : ?>
						<img src="<?php echo esc_url( get_the_post_thumbnail_url( $post, 'medium_large' ) ); ?>"
						     alt="<?php echo esc_attr( get_the_title( $post ) ); ?>"
						     style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;display:block;max-width:none;">
					<?php endif; ?>
				</div>
				<div class="scroll-card-content">
					<span class="scroll-card-title"><?php echo esc_html( get_the_title( $post ) ); ?></span>
					<p class="scroll-card-excerpt"><?php echo esc_html( get_the_excerpt( $post ) ); ?></p>
				</div>
			</a>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
		<?php endif; ?>
		<?php if ( wp_is_mobile() ) : ?><div class="kaslek-ad-between"><ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="4772512111" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script></div><?php endif; ?>
		<?php if ( ! wp_is_mobile() ) : ?><div class="kaslek-ad-desktop-row"><ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="4772512111" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script></div><?php endif; ?>
		<?php if ( count( $scroll_posts ) > 3 ) : ?>
		<div class="scroll-grid">
			<?php foreach ( array_slice( $scroll_posts, 3 ) as $post ) : ?>
			<a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="scroll-card" style="text-decoration:none;">
				<div style="aspect-ratio:3/2;overflow:hidden;position:relative;background:#1B3E5F;">
					<?php if ( has_post_thumbnail( $post ) ) : ?>
						<img src="<?php echo esc_url( get_the_post_thumbnail_url( $post, 'medium_large' ) ); ?>"
						     alt="<?php echo esc_attr( get_the_title( $post ) ); ?>"
						     style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;display:block;max-width:none;">
					<?php endif; ?>
				</div>
				<div class="scroll-card-content">
					<span class="scroll-card-title"><?php echo esc_html( get_the_title( $post ) ); ?></span>
					<p class="scroll-card-excerpt"><?php echo esc_html( get_the_excerpt( $post ) ); ?></p>
				</div>
			</a>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
	</div>


</div><!-- /content -->

</main>

<?php get_footer(); ?>
