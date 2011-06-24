var timeout;

jQuery(document).ready(
	function() {
		// If a timer is already running
		if( typeof( old_id ) != 'undefined' && old_id != -1 ) {
			setTimeout( 'updateTime( old_id );', 36000 );
		}
		jQuery( 'a.timer' ).click(
			function() {
				jQuery.post( 
					jQuery(this).attr( 'title' ) + '/wp-admin/admin-ajax.php',
					{ 
						action: 'timer_toggle', 
						cookie: encodeURIComponent(document.cookie), 
						timer_id: jQuery(this).attr( 'rel' )
					},
					function(data, statusText) {
						clearTimeout( timeout );					
						if( data != old_id ) {
							jQuery( '#timer_toggle_' + old_id ).toggleClass( 'timer_on' );
							old_id = data;
							timeout = setTimeout( 'updateTime( ' + data + ' );', 36000 );
						} else {
							old_id = -1;
						}
						jQuery( '#timer_toggle_' + data ).toggleClass( 'timer_on' );
					}
				);
				return false;
			}
		);
	}
);

function updateTime( id ) {
	jQuery( '#timer_time_' + id ).text( parseFloat( jQuery( '#timer_time_' + id ).text() ) + .01 );
	setTimeout( 'updateTime( ' + id + ' );', 36000 );
}