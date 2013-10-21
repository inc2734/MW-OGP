jQuery( function( $ ) {
	var custom_uploader;
	$( '#mwogp-media' ).click( function( e ) {
		e.preventDefault();
		if ( custom_uploader ) {
			custom_uploader.open();
			return;
		}
		custom_uploader = wp.media( {
		title: mwogp.title,
		library: {
			type: 'image'
		},
		button: {
			text: mwogp.title
		},
		multiple: false
	} );

	custom_uploader.on( 'select', function() {
		var images = custom_uploader.state().get( 'selection' );
		images.each( function( file ){
			$( '#mwogp-images' ).append( '<img src="' + file.toJSON().url + '" />' );
			$( '#mwogp-hidden' ).val( file.toJSON().id );
			$( '#mwogp-media' ).removeClass().addClass( 'mwogp-image-hide' );
			$( '#mwogp-delete' ).removeClass().addClass( 'mwogp-image-show' );
		} );
	} );

	custom_uploader.open();
	} );

	$( '#mwogp-delete' ).click( function() {
		$( '#mwogp-images' ).text( '' );
		$( '#mwogp-hidden' ).val( '' );
		$( '#mwogp-media' ).removeClass().addClass( 'mwogp-image-show' );;
		$( this ).removeClass().addClass( 'mwogp-image-hide' );
	} );
} );