(function($){

	var SampleDataSSEImport = {
		complete: {
			posts: 0,
			media: 0,
			users: 0,
			comments: 0,
			terms: 0,
		},

		updateDelta: function (type, delta) {
			this.complete[ type ] += delta;

			var self = this;
			requestAnimationFrame(function () {
				self.render();
			});
		},
		updateProgress: function ( type, complete, total ) {
			var text = complete + '/' + total;
			document.getElementById( 'completed-' + type ).innerHTML = text;

			if( 'undefined' !== type && 'undefined' !== text ) {
				total = parseInt( total, 10 );
				if ( 0 === total || isNaN( total ) ) {
					total = 1;
				}
				var percent = parseInt( complete, 10 ) / total;
				document.getElementById( 'progress-' + type ).innerHTML = Math.round( percent * 100 ) + '%';
				document.getElementById( 'progressbar-' + type ).value = percent * 100;
				var progress     = Math.round( percent * 100 ) + '%';
				var progress_bar = percent * 100;
			}
		},
		render: function () {
			var types = Object.keys( this.complete );
			var complete = 0;
			var total = 0;

			for (var i = types.length - 1; i >= 0; i--) {
				var type = types[i];
				this.updateProgress( type, this.complete[ type ], this.data.count[ type ] );

				complete += this.complete[ type ];
				total += this.data.count[ type ];
			}

			this.updateProgress( 'total', complete, total );
		}
	};

	SampleData = {

		init: function()
		{
			this._bind();
		},

		/**
		 * Binds events
		 *
		 * @since 1.0.0
		 * @access private
		 * @method _bind
		 */
		_bind: function()
		{
			$( document ).on('click' , '.get-started', SampleData._processImport);
			$( document ).on('click' , '.back-to-importer-content', SampleData._backToImporterContent);
			$( document ).on('click' , '.start-import', SampleData._startImport);
		},

		_processImport:function( event ) {
			event.preventDefault();
			$('#importer-content').slideUp();
			$('#importer-screen').slideDown();

			var file = $(this).attr('data-file') || '';
			if( '' === file ) {
				return;
			}

			$.ajax({
				url  : SampleDataVars.ajaxurl,
				type : 'POST',
				dataType: 'json',
				data : {
					action  : 'sample-data-prepare-import',
					file : file,
				},
				beforeSend: function() {
				},
			})
			.fail(function( jqXHR ){
		    })
			.done(function ( xml_data ) {
				console.log( xml_data );
				setTimeout(function() {
					if( false === xml_data.success ) {
						$('#loading-import-content').html( xml_data.data );
					} else {
						$('#loading-import-content').slideUp();
						$('#loaded-import-content').slideDown();

						// Import XML though Event Source.
						SampleDataSSEImport.data = xml_data.data;
						SampleDataSSEImport.render();
					}
				}, 1000);

			});
		},

		_startImport:function( event ) {
			event.preventDefault();

			// Proceed?
			if( ! confirm( SampleDataVars.importWarning ) ) {
				return;
			}

			$("#import-log").slideDown();
			$(".import-status-indicator").slideDown();

			$('#loaded-import-content').prepend( '<div class="notice notice-info"><p>'+SampleDataVars.importStarted+'</p></div>' );
			$(".import-status-indicator").slideDown();

			var btn = $(this);
			
			btn.addClass( 'updating-message' ).text( SampleDataVars.importingText );

			var evtSource = new EventSource( SampleDataSSEImport.data.url );
			evtSource.onmessage = function ( message ) {
				var data = JSON.parse( message.data );
				switch ( data.action ) {
					case 'updateDelta':
							SampleDataSSEImport.updateDelta( data.type, data.delta );
						break;

					case 'complete':
						evtSource.close();

						btn.addClass( 'disabled' ).removeClass( 'start-import updating-message' ).text( SampleDataVars.importComplete );
						
						$('.back-to-importer-content').remove();
						$('#loaded-import-content').find( '.notice' ).remove();
						$('#loaded-import-content').prepend( '<div class="notice notice-success"><p>'+SampleDataVars.importComplete+'</p></div>' );

						break;
				}
			};
			evtSource.addEventListener( 'log', function ( message ) {
				var data = JSON.parse( message.data );
				var row = document.createElement('tr');
				var level = document.createElement( 'td' );
				level.appendChild( document.createTextNode( data.level ) );
				row.appendChild( level );

				var message = document.createElement( 'td' );
				message.appendChild( document.createTextNode( data.message ) );
				row.appendChild( message );

				$('#import-log').append( row );
			});
		},

		_backToImporterContent:function( event ) {
			event.preventDefault();
			$('#importer-content').slideDown();
			$('#importer-screen').slideUp();

			$('#loading-import-content').slideDown();
			$('#loaded-import-content').slideUp();
		}

	};

	/**
	 * Initialize SampleData
	 */
	$(function(){
		SampleData.init();
	});

})(jQuery);