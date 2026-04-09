( function() {
	const widget = document.getElementById( 'stem-widget' );
	if ( ! widget ) return;

	const knoppen = widget.querySelectorAll( '.stem-btn' );

	knoppen.forEach( function( knop ) {
		knop.addEventListener( 'click', function() {
			if ( widget.classList.contains( 'gestemd' ) ) return;

			const formData = new FormData();
			formData.append( 'action',  'kaslek_stem' );
			formData.append( 'nonce',   kaslekArtikel.nonce );
			formData.append( 'post_id', kaslekArtikel.postId );
			formData.append( 'stem',    knop.dataset.stem );

			fetch( kaslekArtikel.ajaxUrl, {
				method: 'POST',
				body:   formData,
			} )
			.then( r => r.json() )
			.then( data => {
				if ( ! data.success ) return;

				const stemmen = data.data;
				const totaal  = stemmen.ja + stemmen.nee;
				const pctJa   = Math.round( ( stemmen.ja / totaal ) * 100 );
				const pctNee  = 100 - pctJa;

				let resultaat = widget.querySelector( '.stem-resultaat' );
				if ( ! resultaat ) {
					resultaat = document.createElement( 'div' );
					resultaat.className = 'stem-resultaat';
					widget.appendChild( resultaat );
				}
				resultaat.textContent = totaal + ' stemmen · ' + pctJa + '% ja, ' + pctNee + '% nee';

				widget.classList.add( 'gestemd' );
				knop.classList.add( 'actief' );
			} );
		} );
	} );
} )();
