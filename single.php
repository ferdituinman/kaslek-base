<?php get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>

<div class="article-layout">

	<main class="article-main">

		<div class="article-white-block">

			<?php if ( has_post_thumbnail() ) : ?>
			<div style="width:100%;aspect-ratio:10/7;overflow:hidden;position:relative;background:#1B3E5F;">
				<img src="<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'large' ) ); ?>"
				     alt="<?php echo esc_attr( get_the_title() ); ?>"
				     style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;display:block;max-width:none;"
				     loading="eager">
			</div>
			<?php endif; ?>

			<div class="article-header">
				<h1 class="article-title"><?php the_title(); ?></h1>
				<div class="article-meta">
					<span class="meta-author"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg><?php the_author(); ?></span>
					<span class="meta-date"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg><?php echo get_the_date(); ?></span>
					<span class="meta-readtime"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><?php echo kaslek_leestijd( get_the_ID() ); ?></span>
				</div>
			</div>

			<?php
			$content = apply_filters( 'the_content', get_the_content() );
			preg_match( '/<p>(.*?)<\/p>/s', $content, $m );
			$intro = $m[0] ?? '';
			$rest  = $intro ? substr( $content, strpos( $content, $intro ) + strlen( $intro ) ) : $content;
			?>
			<?php if ( $intro ) : ?>
			<div class="article-inleiding">
				<p class="article-intro"><?php echo wp_kses_post( strip_tags( $intro, '<a><strong><em>' ) ); ?></p>
			</div>
			<?php endif; ?>

			<?php $vogelvlucht = get_post_meta( get_the_ID(), 'kaslek_vogelvlucht', true ); ?>
			<?php if ( $vogelvlucht ) : ?>
			<div class="vogelvlucht">
				<h2 class="vogelvlucht-title">In vogelvlucht</h2>
				<?php echo wp_kses_post( wpautop( $vogelvlucht ) ); ?>
			</div>
			<?php endif; ?>

			<div class="article-body">
				<?php echo $rest; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>

			<?php $bronnen = get_post_meta( get_the_ID(), 'kaslek_bronnen', true ); ?>
			<?php if ( $bronnen ) : ?>
			<div class="bronnen">
				<div class="bronnen-title">Bronnen</div>
				<?php echo wp_kses_post( wpautop( $bronnen ) ); ?>
			</div>
			<?php endif; ?>

			<div style="padding: 24px 32px 32px;">
				<h3 class="kaslek-share-title">Deel dit artikel:</h3><div class="kaslek-share-buttons" role="list" aria-label="Deel dit artikel">
  <a class="kaslek-share-btn" href="#" role="listitem" aria-label="Deel op Facebook (opent in nieuw tabblad)" data-share-platform="facebook" data-share-base="https://www.facebook.com/sharer/sharer.php?u=" target="_blank" rel="noopener noreferrer">
    <svg aria-hidden="true" focusable="false" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M504 256C504 119 393 8 256 8S8 119 8 256c0 123.78 90.69 226.38 209.25 245V327.69h-63V256h63v-54.64c0-62.15 37-96.48 93.67-96.48 27.14 0 55.52 4.84 55.52 4.84v61h-31.28c-30.8 0-40.41 19.12-40.41 38.73V256h68.78l-11 71.69h-57.78V501C413.31 482.38 504 379.78 504 256z"/></svg>
  </a>

  <a class="kaslek-share-btn" href="#" role="listitem" aria-label="Deel op X (opent in nieuw tabblad)" data-share-platform="x_twitter" data-share-base="https://twitter.com/intent/tweet?url=" target="_blank" rel="noopener noreferrer">
    <svg aria-hidden="true" focusable="false" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/></svg>
  </a>

  <a class="kaslek-share-btn" href="#" role="listitem" aria-label="Deel op Bluesky (opent in nieuw tabblad)" data-share-platform="bluesky" data-share-base="https://bsky.app/intent/compose?text=" target="_blank" rel="noopener noreferrer">
    <svg aria-hidden="true" focusable="false" viewBox="0 0 512 452.265" xmlns="http://www.w3.org/2000/svg"><path fill-rule="nonzero" d="M110.985 30.442c58.696 44.217 121.837 133.855 145.013 181.96 23.177-48.105 86.323-137.744 145.017-181.96C443.376-1.455 512-26.142 512 52.402c0 15.681-8.962 131.775-14.223 150.628-18.273 65.515-84.873 82.228-144.113 72.116 103.551 17.679 129.89 76.237 73.001 134.799-108.041 111.223-155.288-27.905-167.386-63.554-3.488-10.262-2.991-10.498-6.56 0-12.098 35.649-59.342 174.777-167.383 63.554-56.889-58.562-30.551-117.12 73-134.799-59.24 10.112-125.841-6.601-144.113-72.116C8.962 184.177 0 68.083 0 52.402c0-78.544 68.633-53.857 110.985-21.96z"/></svg>
  </a>

  <a class="kaslek-share-btn" href="#" role="listitem" aria-label="Deel via Telegram (opent in nieuw tabblad)" data-share-platform="telegram" data-share-base="https://t.me/share/url?url=" target="_blank" rel="noopener noreferrer">
    <svg aria-hidden="true" focusable="false" viewBox="0 0 496 512" xmlns="http://www.w3.org/2000/svg"><path d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm121.8 169.9l-40.7 191.8c-3 13.6-11.1 16.9-22.4 10.5l-62-45.7-29.9 28.8c-3.3 3.3-6.1 6.1-12.5 6.1l4.4-63.1 114.9-103.8c5-4.4-1.1-6.9-7.7-2.5l-142 89.4-61.2-19.1c-13.3-4.2-13.6-13.3 2.8-19.7l239.1-92.2c11.1-4 20.8 2.7 17.2 19.5z"/></svg>
  </a>

  <a class="kaslek-share-btn" href="#" role="listitem" aria-label="Deel via WhatsApp (opent in nieuw tabblad)" data-share-platform="whatsapp" data-share-base="https://wa.me/?text=" target="_blank" rel="noopener noreferrer">
    <svg aria-hidden="true" focusable="false" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>
  </a>

</div>

<script>
(function () {
  var url = encodeURIComponent(window.location.href);
  document.querySelectorAll('.kaslek-share-btn[data-share-base]').forEach(function (btn) {
    btn.href = btn.getAttribute('data-share-base') + url;
    btn.addEventListener('click', function () {
      if (typeof gtag === 'function') {
        gtag('event', 'share', {
          method: btn.getAttribute('data-share-platform'),
          content_type: 'post',
          item_id: window.location.href
        });
      }
    });
  });
})();
</script>
			</div>

			<?php if ( wp_is_mobile() ) : ?>
			<div class="kaslek-ad-mobile-share">
				<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6115912536653612" crossorigin="anonymous"></script>
				<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="4772512111" data-ad-format="auto" data-full-width-responsive="true"></ins>
				<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
			</div>
			<?php endif; ?>

			</div><!-- /article-white-block -->

		<div class="nieuwstip">
			<svg viewBox="0 0 24 24" stroke-width="1.5" style="width:20px;height:20px;stroke:#1B3E5F;fill:none;flex-shrink:0;"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
			<p><strong>Nieuwstip?</strong> Mail de redactie: <a href="mailto:redactie@kaslek.nl">redactie@kaslek.nl</a></p>
		</div>

	</main>

	<aside class="article-sidebar">

		<?php
		// Meer verhalen: 21 dagen, fallback zonder datum
		$sidebar_q = new WP_Query([
			'posts_per_page' => 4,
			'post_status'    => 'publish',
			'post__not_in'   => [ get_the_ID() ],
			'date_query'     => [ [ 'after' => '21 days ago', 'inclusive' => true ] ],
		]);
		if ( ! $sidebar_q->have_posts() ) {
			$sidebar_q = new WP_Query([
				'posts_per_page' => 4,
				'post_status'    => 'publish',
				'post__not_in'   => [ get_the_ID() ],
			]);
		}
		?>
		<?php if ( $sidebar_q->have_posts() ) : ?>
		<div class="sidebar-block">
			<div class="sidebar-block-title">Meer verhalen</div>
			<?php while ( $sidebar_q->have_posts() ) : $sidebar_q->the_post(); ?>
			<a href="<?php the_permalink(); ?>" class="sidebar-item" style="text-decoration:none;">
				<div style="width:64px;height:52px;flex-shrink:0;overflow:hidden;position:relative;background:#1B3E5F;">
					<?php if ( has_post_thumbnail() ) : ?>
						<img src="<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ) ); ?>"
						     alt="<?php echo esc_attr( get_the_title() ); ?>"
						     style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;display:block;max-width:none;">
					<?php endif; ?>
				</div>
				<div class="sidebar-item-content">
					<div class="sidebar-item-title"><?php the_title(); ?></div>
				</div>
			</a>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
		<?php endif; ?>

		<?php if ( ! wp_is_mobile() ) : ?>
		<div style="margin:50px 0;">
			<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6115912536653612" crossorigin="anonymous"></script>
			<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="4772512111" data-ad-format="auto" data-full-width-responsive="true"></ins>
			<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
		</div>
		<?php endif; ?>

		<?php
		$trending_q = new WP_Query([
			'posts_per_page' => 4,
			'post_status'    => 'publish',
			'meta_key'       => 'post_views_count',
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
			'post__not_in'   => [ get_the_ID() ],
		]);
		?>
		<?php if ( $trending_q->have_posts() ) : ?>
		<div class="sidebar-block">
			<div class="sidebar-block-title">Meest gelezen</div>
			<?php $n = 1; while ( $trending_q->have_posts() ) : $trending_q->the_post(); ?>
			<div class="sidebar-trending-item">
				<div class="sidebar-num"><?php echo $n++; ?></div>
				<div>
					<a href="<?php the_permalink(); ?>" class="sidebar-trending-title"><?php the_title(); ?></a>
				</div>
			</div>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
		<?php endif; ?>

	</aside>

</div><!-- /article-layout -->

<?php
$tags    = get_the_tags( get_the_ID() );
$tag_ids = $tags ? array_map( fn($t) => $t->term_id, $tags ) : [];
$gerelateerd_q = new WP_Query([
	'posts_per_page' => 3,
	'post_status'    => 'publish',
	'post__not_in'   => [ get_the_ID() ],
	'tag__in'        => $tag_ids,
	'orderby'        => 'date',
	'order'          => 'DESC',
]);
?>
<?php if ( $gerelateerd_q->have_posts() ) : ?>
<div class="meer-verhalen">
	<div class="meer-verhalen-header">
		<h2>Gerelateerde verhalen</h2>
		<div class="meer-verhalen-line"></div>
	</div>
	<div class="meer-verhalen-grid">
		<?php while ( $gerelateerd_q->have_posts() ) : $gerelateerd_q->the_post(); ?>
		<div class="mv-card" style="display:flex;flex-direction:column;">
			<div style="height:160px;overflow:hidden;position:relative;background:#1B3E5F;flex-shrink:0;">
				<?php if ( has_post_thumbnail() ) : ?>
					<img src="<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'medium_large' ) ); ?>"
					     alt="<?php echo esc_attr( get_the_title() ); ?>"
					     style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;display:block;max-width:none;">
				<?php endif; ?>
			</div>
			<div class="mv-card-content">
				<a href="<?php the_permalink(); ?>" class="mv-card-title"><?php the_title(); ?></a>
			</div>
		</div>
		<?php endwhile; wp_reset_postdata(); ?>
	</div>
</div>
<?php endif; ?>

<?php if ( ! wp_is_mobile() ) : ?>
<div class="kaslek-ad-desktop-bottom">
	<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6115912536653612" crossorigin="anonymous"></script>
	<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="4772512111" data-ad-format="auto" data-full-width-responsive="true"></ins>
	<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
</div>
<?php endif; ?>

<?php endwhile; ?>

<?php get_footer(); ?>
