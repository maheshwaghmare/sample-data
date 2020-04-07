<?php
/**
 * Import Page
 *
 * @package Import Page
 * @since 1.0.0
 */

if ( ! class_exists( 'Sample_Data_Page' ) ) :

	/**
	 * Sample_Data_Page
	 *
	 * @since 1.0.0
	 */
	class Sample_Data_Page {

		/**
		 * Instance
		 *
		 * @since 1.0.0
		 *
		 * @access private
		 * @var object Class object.
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.0.0
		 *
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
			add_action( 'plugin_action_links_' . SAMPLE_DATA_BASE, array( $this, 'action_links' ) );
		}

		/**
		 * Show action links on the plugin screen.
		 *
		 * @param   mixed $links Plugin Action links.
		 * @return  array
		 */
		function action_links( $links ) {
			$action_links = apply_filters( 'sample_data_action_links', array(
				'settings' => '<a href="' . admin_url( 'tools.php?page=sample-data' ) . '" aria-label="' . esc_attr__( 'Import Content', 'sample-data' ) . '">' . esc_html__( 'Import Content', 'sample-data' ) . '</a>',
			));

			return array_merge( $action_links, $links );
		}

		/**
		 * Enqueue admin scripts.
		 *
		 * @param  string $hook Current hook name.
		 * @return void
		 */
		public function admin_enqueue( $hook = '' ) {

			wp_enqueue_script( 'data-importer-js', SAMPLE_DATA_URI . 'assets/js/importer.js', array( 'jquery', 'wp-util', 'updates' ), SAMPLE_DATA_VER, true );
			wp_enqueue_style( 'data-importer-css', SAMPLE_DATA_URI . 'assets/css/importer.css', array(), SAMPLE_DATA_VER, 'all' );

			$data = apply_filters( 'sample_data_localize_vars', array(
				'ajaxurl'        => esc_url( admin_url( 'admin-ajax.php' ) ),
				'importWarning'  => __( "Ready to Import!\n\nDo you want to import the data?\n\nClick 'Ok' to start the import.\nClick 'Cancel' to exit.", 'sample-data' ),
				'importStarted' => __( 'Import started!', 'sample-data' ),
				'importComplete' => __( 'Import complete!', 'sample-data' ),
				'importingText' => __( 'Importing..', 'sample-data' ),
			));

			wp_localize_script( 'data-importer-js', 'SampleDataVars', $data );
		}

		/**
		 * Register menu
		 *
		 * @since 1.0.0
		 * @return void
		 */
		function register_admin_menu() {
			add_submenu_page( 'tools.php', __( 'Site Demo Content', 'sample-data' ), __( 'Site Demo Content', 'sample-data' ), 'manage_options', 'sample-data', array( $this, 'options_page' ) );
		}

		/**
		 * Option Page
		 *
		 * @since 1.0.0
		 * @return void
		 */
		function options_page() {
			?>
			<div class="wrap sample-data" id="sync-post">
				<h1><?php _e( 'Site Demo Content', 'sample-data' ); ?></h1>
				<p><?php _e( 'Simply click on the get started to import the dummy content..', 'sample-data' ); ?></p>
				<hr>
				<div class="wrap">
					<div id="poststuff">
						<div id="post-body" class="columns-2">
							<div id="post-body-content">

								<div id="importer-content">


									<div class="themes">
										<div class="grid">
											<div class="item">
												<div class="card-top">
													<div class="name column-name">
														<h3><?php _e( 'Sample Content', 'sample-data' ); ?></h3>
													</div>
													<div class="desc column-description">
														<?php /* translators: %s is the link of theme unit test data. */ ?>
														<p><?php esc_html_e( 'Import dummy posts, pages, comments etc.', 'sample-data' ); ?></p>
														<p><a href='#' class="get-started" data-file="<?php echo esc_attr( SAMPLE_DATA_DIR . 'data\themeunittestdata.WordPress.xml' ); ?>"><i><?php _e( 'Import »', 'sample-data' ); ?></i></a></p>
													</div>
												</div>
											</div>
										</div>
									</div>

									<br class="clear">
									<hr>

									<div class="plugins">

										<h2 class="title"><?php _e( 'Plugins', 'sample-data' ); ?></h2>

										<div class="grid">
											<div class="item">
												<img src="https://ps.w.org/woocommerce/assets/icon-256x256.png?rev=1440831">
												<div class="card-top">
													<div class="name column-name">
														<h3><a href="<?php echo admin_url( 'plugin-install.php?tab=plugin-information&plugin=woocommerce&TB_iframe=true&width=772&height=327' ); ?>" class="thickbox open-plugin-details-modal"><?php _e( 'WooCommerce', 'sample-data' ); ?></a></h3>
													</div>
													<div class="desc column-description">
														<p><?php _e( 'WooCommerce is a powerful, extendable eCommerce plugin that helps you sell anything. Beautifully.', 'sample-data' ); ?></p>
														<?php if ( class_exists( 'WooCommerce' ) ) {
															if( defined( 'WC_ABSPATH' ) ) {
																$woo_xml_url = WC_ABSPATH . 'sample-data\sample_products.xml';
															} else {
																$woo_xml_url = SAMPLE_DATA_DIR . 'data\woocommerce-sample_products.xml';
															}
															?>
															<p><a href='#' class="get-started" data-file="<?php echo esc_attr( $woo_xml_url ); ?>"><i><?php _e( 'Get Started »', 'sample-data' ); ?></i></a></p>
														<?php } else { ?>
															<p><a href='<?php echo admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term', 'sample-data' ); ?>'><i><?php _e( 'Install & Activate »', 'sample-data' ); ?></i></a></p>
														<?php } ?>
													</div>
												</div>
											</div>
											<div class="item">
												<img src="https://ps.w.org/bbpress/assets/icon.svg?rev=978290">
												<div class="card-top">
													<div class="name column-name">
														<h3><a href="<?php echo admin_url( 'plugin-install.php?tab=plugin-information&plugin=bbpress&TB_iframe=true&width=772&height=327' ); ?>" class="thickbox open-plugin-details-modal"><?php _e( 'bbPress.', 'sample-data' ); ?></a></h3>
													</div>
													<div class="desc column-description">
														<p><?php _e( 'bbPress is forum software, made the WordPress way.', 'sample-data' ); ?></p>
														<?php if ( class_exists( 'bbPress' ) ) { ?>
															<p><a href='#' class="get-started" data-file="<?php echo esc_attr( SAMPLE_DATA_DIR . 'data\bbpress-unit-test-data.xml' ); ?>"><i><?php _e( 'Get Started »', 'sample-data' ); ?></i></a></p>
														<?php } else { ?>
															<p><a href='<?php echo admin_url( 'plugin-install.php?s=bbpress&tab=search&type=term', 'sample-data' ); ?>'><i><?php _e( 'Install & Activate »', 'sample-data' ); ?></i></a></p>
														<?php } ?>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>

								<div id="importer-screen">
									<div class="welcome-panel">
										<div class="welcome-panel-content">
											<a href="#" class="back-to-importer-content"><?php _e( '❮ Back', 'sample-data' ); ?></a>

											<div id="loading-import-content">
												<p class="description"><?php _e( 'Wait for a moment!', 'sample-data' ); ?><br><small><?php _e( 'Getting import content.', 'sample-data' ); ?></small></p>
												<span class="spinner is-active"></span>
											</div>

											<div id="loaded-import-content">
												<h3><?php _e( 'Import Screen', 'sample-data' ); ?></h3>
												<hr>

												<table class="import-status widefat striped">
													<thead>
														<tr>
															<th><?php _e( 'Import Summary', 'sample-data' ); ?></th>
															<th><?php _e( 'Completed', 'sample-data' ); ?></th>
															<th><?php _e( 'Progress', 'sample-data' ); ?></th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td>
																<span class="dashicons dashicons-admin-post"></span>
															</td>
															<td>
																<span id="completed-posts" class="completed">0/0</span>
															</td>
															<td>
																<progress id="progressbar-posts" max="100" value="0"></progress>
																<span id="progress-posts" class="progress">0%</span>
															</td>
														</tr>
														<tr>
															<td>
																<span class="dashicons dashicons-admin-media"></span>
															</td>
															<td>
																<span id="completed-media" class="completed">0/0</span>
															</td>
															<td>
																<progress id="progressbar-media" max="100" value="0"></progress>
																<span id="progress-media" class="progress">0%</span>
															</td>
														</tr>

														<tr>
															<td>
																<span class="dashicons dashicons-admin-users"></span>
															</td>
															<td>
																<span id="completed-users" class="completed">0/0</span>
															</td>
															<td>
																<progress id="progressbar-users" max="100" value="0"></progress>
																<span id="progress-users" class="progress">0%</span>
															</td>
														</tr>

														<tr>
															<td>
																<span class="dashicons dashicons-admin-comments"></span>
															</td>
															<td>
																<span id="completed-comments" class="completed">0/0</span>
															</td>
															<td>
																<progress id="progressbar-comments" max="100" value="0"></progress>
																<span id="progress-comments" class="progress">0%</span>
															</td>
														</tr>

														<tr>
															<td>
																<span class="dashicons dashicons-category"></span>
															</td>
															<td>
																<span id="completed-terms" class="completed">0/0</span>
															</td>
															<td>
																<progress id="progressbar-terms" max="100" value="0"></progress>
																<span id="progress-terms" class="progress">0%</span>
															</td>
														</tr>
														<tr>
															<td>
																<span class="dashicons dashicons-admin-links"></span>
															</td>
															<td>
																<span id="completed-links" class="completed">0/0</span>
															</td>
															<td>
																<progress id="progressbar-links" max="100" value="0"></progress>
																<span id="progress-links" class="progress">0%</span>
															</td>
														</tr>
													</tbody>
												</table>

												<div class="import-status-indicator">
													<div class="progress">
														<progress id="progressbar-total" max="100" value="0"></progress>
													</div>
													<div class="status">
														<span id="completed-total" class="completed">0/0</span>
														<span id="progress-total" class="progress">0%</span>
													</div>
												</div>

												<hr>

												<div class="process-import"><button class="start-import button button-primary button-hero"><?php _e( 'Start Import!', 'sample-data' ); ?></button></div>

												<table id="import-log" class="widefat striped">
													<thead>
														<tr>
															<th class='type'><?php _e( 'Type', 'sample-data' ); ?></th>
															<th><?php _e( 'Message', 'sample-data' ); ?></th>
														</tr>
													</thead>
													<tbody>
													</tbody>
												</table>
											</div>
										</div>
									</div>

								</div>

							</div>

							<div class="postbox-container" id="postbox-container-1">
								<div id="side-sortables" style="">
									<div class="postbox">
										<h2 class="hndle"><span><?php _e( 'Getting Started', 'sample-data' ); ?></span></h2>
										<div class="inside">
											<p><?php _e( 'Follow below simple steps to improt the dummy content:', 'sample-data' ); ?></p>
											<ul>
												<li><?php _e( 'Click on <i>Get Started »</i>', 'sample-data' ); ?></li>
												<li><?php _e( 'It read the file and get content.', 'sample-data' ); ?></li>
												<li><?php _e( 'Click on <i>Start Import</i>.', 'sample-data' ); ?></li>
											</ul>
											<p><?php _e( 'For more details check <a href="https://youtu.be/q77CJDKAnmg" target="_blank">video tutorial</a>', 'sample-data' ); ?></p>
										</div>
									</div>
									<div class="postbox">
										<h2 class="hndle"><span><?php _e( 'Support', 'sample-data' ); ?></span></h2>
										<div class="inside">
											<p><?php _e( 'Plugin import the data form the XML file. For now plugin added in-build support for: ', 'sample-data' ); ?></p>
											<ul>
												<li><?php _e( 'Theme Unit Test Data', 'sample-data' ); ?></li>
												<li><?php _e( 'WooCommerce', 'sample-data' ); ?></li>
												<li><?php _e( 'bbPress', 'sample-data' ); ?></li>
											</ul>
											<p><?php _e( 'Do you want another plugin support? <a href="http://maheshwaghmare.wordpress.com/?p=999" target="_blank">Request for support »</a>', 'sample-data' ); ?></p>
										</div>
									</div>
									<div class="postbox">
										<h2 class="hndle"><span><?php _e( 'Donate', 'sample-data' ); ?></span></h2>
										<div class="inside">
											<p><?php _e( 'Would you like to support the advancement of this plugin?', 'sample-data' ); ?></p>
											<a href="https://www.paypal.me/mwaghmare7/" target="_blank" class="button button-primary"><?php _e( 'Donate Now!', 'sample-data' ); ?></a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Initialize class object with 'get_instance()' method
	 */
	Sample_Data_Page::get_instance();

endif;
