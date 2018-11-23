<?php
/**
 * Sample Data
 *
 * @since 1.0.0
 * @package Sample Data
 */

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'Sample_Data' ) ) :

	/**
	 * Sample Data
	 *
	 * @since 1.0.0
	 */
	class Sample_Data {

		/**
		 * Instance of Sample_Data
		 *
		 * @since 1.0.0
		 * @var Sample_Data
		 */
		private static $_instance = null;

		/**
		 * Instantiate Sample_Data
		 *
		 * @since 1.0.0
		 * @return (Object) Sample_Data.
		 */
		public static function instance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		private function __construct() {

			if ( ! class_exists( 'WP_Importer' ) ) {
				defined( 'WP_LOAD_IMPORTERS' ) || define( 'WP_LOAD_IMPORTERS', true );
				require ABSPATH . '/wp-admin/includes/class-wp-importer.php';
			}

			// Helper.
			require_once SAMPLE_DATA_DIR . 'classes/class-sample-data-page.php';

			// WP Importer 2.
			require_once SAMPLE_DATA_DIR . 'classes/importer/class-logger.php';
			require_once SAMPLE_DATA_DIR . 'classes/importer/class-wp-importer-logger-serversentevents.php';
			require_once SAMPLE_DATA_DIR . 'classes/importer/class-wxr-importer.php';
			require_once SAMPLE_DATA_DIR . 'classes/importer/class-wxr-import-info.php';

			add_action( 'wp_ajax_sample-data-wxr-import', array( $this, 'sse_import' ) );
			add_action( 'wp_ajax_sample-data-prepare-import', array( $this, 'prepare_xml_data' ) );
		}

		/**
		 * Prepare XML Data.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		function prepare_xml_data() {

			$file = ( isset( $_REQUEST['file'] ) ) ? urldecode( $_REQUEST['file'] ) : '';
			$data = Sample_Data::instance()->get_xml_data( $file );

			if ( is_wp_error( $data ) ) {
				wp_send_json_error( $data );
			}

			wp_send_json_success( $data );
		}

		/**
		 * SSE Import.
		 *
		 * @since 1.0.0
		 */
		function sse_import() {

			// Start the event stream.
			header( 'Content-Type: text/event-stream' );

			// Turn off PHP output compression.
			$previous = error_reporting( error_reporting() ^ E_WARNING );
			ini_set( 'output_buffering', 'off' );
			ini_set( 'zlib.output_compression', false );
			error_reporting( $previous );

			if ( $GLOBALS['is_nginx'] ) {
				// Setting this header instructs Nginx to disable fastcgi_buffering
				// and disable gzip for this request.
				header( 'X-Accel-Buffering: no' );
				header( 'Content-Encoding: none' );
			}

			$xml_url = urldecode( $_REQUEST['xml_url'] );
			if ( empty( $xml_url ) ) {
				exit;
			}

			// 2KB padding for IE
			echo ':' . str_repeat( ' ', 2048 ) . "\n\n";

			// Time to run the import!
			set_time_limit( 0 );

			// Ensure we're not buffered.
			wp_ob_end_flush_all();
			flush();

			// Are we allowed to create users?
			// Keep track of our progress.
			add_action( 'wxr_importer.processed.post', array( $this, 'imported_post' ), 10, 2 );
			add_action( 'wxr_importer.process_failed.post', array( $this, 'imported_post' ), 10, 2 );
			add_action( 'wxr_importer.process_already_imported.post', array( $this, 'already_imported_post' ), 10, 2 );
			add_action( 'wxr_importer.process_skipped.post', array( $this, 'already_imported_post' ), 10, 2 );
			add_action( 'wxr_importer.processed.comment', array( $this, 'imported_comment' ) );
			add_action( 'wxr_importer.process_already_imported.comment', array( $this, 'imported_comment' ) );
			add_action( 'wxr_importer.processed.term', array( $this, 'imported_term' ) );
			add_action( 'wxr_importer.process_failed.term', array( $this, 'imported_term' ) );
			add_action( 'wxr_importer.process_already_imported.term', array( $this, 'imported_term' ) );
			add_action( 'wxr_importer.processed.user', array( $this, 'imported_user' ) );
			add_action( 'wxr_importer.process_failed.user', array( $this, 'imported_user' ) );
			// Flush once more.
			flush();

			$importer = $this->get_importer();
			$response = $importer->import( $xml_url );

			// Let the browser know we're done.
			$complete = array(
				'action' => 'complete',
				'error'  => false,
			);
			if ( is_wp_error( $response ) ) {
				$complete['error'] = $response->get_error_message();
			}

			$this->emit_sse_message( $complete );
			exit;
		}

		/**
		 * Start the xml import.
		 *
		 * @since 1.0.0
		 *
		 * @param  (String) $path Absolute path to the XML file.
		 */
		public function get_xml_data( $path ) {

			$args = array(
				'action'  => 'sample-data-wxr-import',
				'id'      => '1',
				'xml_url' => $path,
			);
			$url  = add_query_arg( urlencode_deep( $args ), admin_url( 'admin-ajax.php' ) );

			$data = $this->get_data( $path );

			return array(
				'count' => array(
					'posts'    => $data->post_count,
					'media'    => $data->media_count,
					'users'    => count( $data->users ),
					'comments' => $data->comment_count,
					'terms'    => $data->term_count,
				),
				'url'   => $url,
			);
		}

		/**
		 * Get XML data.
		 *
		 * @since 1.0.0
		 * @param  string $url Downloaded XML file absolute URL.
		 * @return array  XML file data.
		 */
		function get_data( $url ) {
			$importer = $this->get_importer();
			$data     = $importer->get_preliminary_information( $url );
			if ( is_wp_error( $data ) ) {
				return $data;
			}
			return $data;
		}

		/**
		 * Get Importer
		 *
		 * @since 1.0.0
		 * @return object   Importer object.
		 */
		public function get_importer() {
			$options  = array(
				'fetch_attachments' => true,
				'default_author'    => get_current_user_id(),
			);
			$importer = new WXR_Importer( $options );
			$logger   = new WP_Importer_Logger_ServerSentEvents();

			$importer->set_logger( $logger );
			return $importer;
		}

		/**
		 * Send message when a post has been imported.
		 *
		 * @since 1.0.0
		 * @param int   $id Post ID.
		 * @param array $data Post data saved to the DB.
		 */
		public function imported_post( $id, $data ) {
			$this->emit_sse_message(
				array(
					'action' => 'updateDelta',
					'type'   => ( 'attachment' === $data['post_type'] ) ? 'media' : 'posts',
					'delta'  => 1,
				)
			);
		}

		/**
		 * Send message when a post is marked as already imported.
		 *
		 * @since 1.0.0
		 * @param array $data Post data saved to the DB.
		 */
		public function already_imported_post( $data ) {
			$this->emit_sse_message(
				array(
					'action' => 'updateDelta',
					'type'   => ( 'attachment' === $data['post_type'] ) ? 'media' : 'posts',
					'delta'  => 1,
				)
			);
		}

		/**
		 * Send message when a comment has been imported.
		 *
		 * @since 1.0.0
		 */
		public function imported_comment() {
			$this->emit_sse_message(
				array(
					'action' => 'updateDelta',
					'type'   => 'comments',
					'delta'  => 1,
				)
			);
		}

		/**
		 * Send message when a term has been imported.
		 *
		 * @since 1.0.0
		 */
		public function imported_term() {
			$this->emit_sse_message(
				array(
					'action' => 'updateDelta',
					'type'   => 'terms',
					'delta'  => 1,
				)
			);
		}

		/**
		 * Send message when a user has been imported.
		 *
		 * @since 1.0.0
		 */
		public function imported_user() {
			$this->emit_sse_message(
				array(
					'action' => 'updateDelta',
					'type'   => 'users',
					'delta'  => 1,
				)
			);
		}

		/**
		 * Emit a Server-Sent Events message.
		 *
		 * @since 1.0.0
		 * @param mixed $data Data to be JSON-encoded and sent in the message.
		 */
		public function emit_sse_message( $data ) {
			echo "event: message\n";
			echo 'data: ' . wp_json_encode( $data ) . "\n\n";

			// Extra padding.
			echo ':' . str_repeat( ' ', 2048 ) . "\n\n";

			flush();
		}

	}

	/**
	 * Initialize class object with 'instance()' method
	 */
	Sample_Data::instance();

endif;
