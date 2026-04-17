<?php $c = get_the_category(); ?>
<article class="card-horizontal">
	<a href="<?php the_permalink(); ?>" class="card-horizontal-img" tabindex="-1" aria-hidden="true">
		<?php if ( has_post_thumbnail() ) : ?>
		<img src="<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'medium' ) ); ?>"
		     alt="<?php echo esc_attr( get_the_title() ); ?>"
		     style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;display:block;max-width:none;">
		<?php endif; ?>
	</a>
	<div class="card-horizontal-content">
		<?php if ( $c ) echo '<a href="' . esc_url( get_category_link( $c[0]->term_id ) ) . '" class="cat-label ' . esc_attr($c[0]->slug) . '">' . esc_html($c[0]->name) . '</a>'; ?>
		<a href="<?php the_permalink(); ?>" class="card-horizontal-title"><?php echo esc_html( get_the_title() ); ?></a>
	</div>
</article>
