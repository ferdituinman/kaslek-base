( function() {
	const container = document.getElementById( 'infinite-scroll-container' );
	const trigger   = document.getElementById( 'infinite-scroll-trigger' );

	if ( ! container || ! trigger ) return;

	let loading = false;

	const observer = new IntersectionObserver( function( entries ) {
		if ( entries[0].isIntersecting && ! loading ) {
			loading = true;
			const paged = parseInt( trigger.dataset.paged, 10 );

			trigger.querySelector( '.infinite-loader' ).textContent = 'Laden...';

			const formData = new FormData();
			formData.append( 'action', 'kaslek_infinite_scroll' );
			formData.append( 'nonce',  kaslekData.nonce );
			formData.append( 'paged',  paged );

			fetch( kaslekData.ajaxUrl, {
				method: 'POST',
				body:   formData,
			} )
			.then( r => r.text() )
			.then( html => {
				if ( html.trim() === '' ) {
					trigger.style.display = 'none';
					return;
				}
				container.insertAdjacentHTML( 'beforeend', html );
				trigger.dataset.paged = paged + 1;
				trigger.querySelector( '.infinite-loader' ).textContent = 'Meer laden...';
				loading = false;
			} )
			.catch( () => {
				trigger.querySelector( '.infinite-loader' ).textContent = 'Meer laden...';
				loading = false;
			} );
		}
	}, { rootMargin: '200px' } );

	observer.observe( trigger );
} )();
