<?php
/**
 * Archive row: renders exactly 3 scroll-cards for use in archive + AJAX infinite scroll.
 * Expects $posts array of 3 WP_Post objects passed via set_query_var / get_query_var.
 */
$row_posts = get_query_var( 'archive_row_posts', [] );
if ( empty( $row_posts ) ) return;
?>
<div class="scroll-grid">
	<?php foreach ( $row_posts as $post ) : setup_postdata( $post ); ?>
	<a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="scroll-card" style="text-decoration:none;">
		<div style="aspect-ratio:3/2;overflow:hidden;position:relative;background:#1B3E5F;">
			<?php if ( has_post_thumbnail( $post ) ) : ?>
				<img src="<?php echo esc_url( get_the_post_thumbnail_url( $post, 'medium_large' ) ); ?>"
				     alt="<?php echo esc_attr( get_the_title( $post ) ); ?>"
				     style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;display:block;max-width:none;"
				     loading="lazy">
			<?php endif; ?>
		</div>
		<div class="scroll-card-content">
			<span class="scroll-card-title"><?php echo esc_html( get_the_title( $post ) ); ?></span>
			<p class="scroll-card-excerpt"><?php echo esc_html( get_the_excerpt( $post ) ); ?></p>
		</div>
	</a>
	<?php endforeach; wp_reset_postdata(); ?>
</div>
