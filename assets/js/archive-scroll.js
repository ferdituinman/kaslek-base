( function() {
	const cards = document.querySelectorAll( '.archive-card-fadein' );
	if ( ! cards.length ) return;

	const observer = new IntersectionObserver( function( entries ) {
		entries.forEach( function( entry ) {
			if ( entry.isIntersecting ) {
				setTimeout( function() {
					entry.target.classList.add( 'visible' );
				}, 160 );
				observer.unobserve( entry.target );
			}
		} );
	}, { rootMargin: '0px 0px -60px 0px', threshold: 0 } );

	setTimeout( function() {
		cards.forEach( function( card ) {
			observer.observe( card );
		} );
	}, 200 );
} )();
