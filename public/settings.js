(function ( $, settings ) {

	$( function () {

		var $responseContainers = $( '.response-containers' ), $submitButton = $( '.request-submit' ),
			$template = $( "#response-container-template" );

		$submitButton.click( function () {

			makeSampleRequest().done( function ( data ) {

				var response = data.response, start = data.start, end = data.end, output = response;

				if ( response.id ) {
					output = {
						id     : response.id,
						date   : response.date,
						title  : response.title,
						content: response.content,
					};
				}

				var $el = $template.clone();

				$el.removeClass( 'hidden' ).prop( 'id', '' );
				$( '.response-body', $el ).text( JSON.stringify( output, null, 2 ) );
				$( '.response-start time', $el ).text(
					start.getHours() + ':' + start.getMinutes() + ':' + start.getSeconds() + '.' + start.getMilliseconds()
				);
				$( '.response-end time', $el ).text(
					end.getHours() + ':' + end.getMinutes() + ':' + end.getSeconds() + '.' + end.getMilliseconds()
				);

				$responseContainers.append( $el );
			} );
		} );

	} );

	function makeSampleRequest() {
		var url = settings.restRoute, method = 'POST', json = $( "#request-body" ).text(), $header = $( '.key-header' );

		var deferred = $.Deferred();

		var now = new Date;

		$.ajax( url, {
			method     : method,
			contentType: 'application/json',
			data       : json,
			beforeSend : function ( request ) {

				if ( $header.length ) {
					request.setRequestHeader( 'X-' + $header.data( 'name' ), $header.data( 'key' ) );
				}
			},
			success    : function ( response ) {
				deferred.resolve( {
					response: response,
					start   : now,
					end     : new Date,
				} );
			},
			error      : function ( response ) {
				deferred.resolve( {
					response: JSON.parse( response.responseText ),
					start   : now,
					end     : new Date,
				} );
			},
		} );

		return deferred.promise();
	}
})( jQuery, wpApiIdempotenceSettings );