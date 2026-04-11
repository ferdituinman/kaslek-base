<div style="margin:50px 0">
	<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6115912536653612" data-ad-slot="4772512111" data-ad-format="auto" data-full-width-responsive="true"></ins>
	<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
</div>

<footer class="site-footer">
	<div class="footer-grid">
		<div class="footer-col">
			<div class="footer-col-title">Over KasLek</div>
			<p>KasLek is een onafhankelijk platform voor burgerjournalistiek. Wij leggen bloot waar jouw belastinggeld naartoe gaat.</p>
		</div>
		<div class="footer-col">
			<div class="footer-col-title">Rubrieken</div>
			<a href="<?php echo esc_url( get_category_link( get_cat_ID( 'Nieuws' ) ) ); ?>">Nieuws</a>
			<a href="<?php echo esc_url( get_category_link( get_cat_ID( 'Opinie' ) ) ); ?>">Opinie</a>
			<a href="<?php echo esc_url( get_category_link( get_cat_ID( 'Bijzonder' ) ) ); ?>">Bijzonder</a>
			<a href="<?php echo esc_url( get_category_link( get_cat_ID( 'Spraakmakend' ) ) ); ?>">Spraakmakend</a>
		</div>
		<div class="footer-col">
			<div class="footer-col-title">Contact</div>
			<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'over-kaslek' ) ) ); ?>">Over KasLek</a>
			<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'nieuwstip-insturen' ) ) ); ?>">Nieuwstip insturen</a>
		</div>
	</div>
	<div class="footer-bottom">
		<p>&copy; <?php echo date( 'Y' ); ?> KasLek. Alle rechten voorbehouden.</p>
		<p>KasLek is een onafhankelijk platform voor burgerjournalistiek.</p>
	</div>
</footer>

</div><!-- /site-wrapper -->

<?php wp_footer(); ?>
</body>
</html>
