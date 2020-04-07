<?php
/**
 * Plugin Name: Site Demo Content
 * Description: One click import dummy content for your website. Dummy content includes posts, pages, comments etc. Also, Import demo content for different plugins such as WooCommerce, bbPress etc.
 * Plugin URI: https://profiles.wordpress.org/mahesh901122/
 * Author: Mahesh M. Waghmare
 * Author URI: https://maheshwaghmare.com/
 * Version: 1.1.1
 * License: GNU General Public License v2.0
 * Text Domain: sample-data
 *
 * @package Sample Data
 */

// Set constants.
define( 'SAMPLE_DATA_VER', '1.1.1' );
define( 'SAMPLE_DATA_FILE', __FILE__ );
define( 'SAMPLE_DATA_BASE', plugin_basename( SAMPLE_DATA_FILE ) );
define( 'SAMPLE_DATA_DIR', plugin_dir_path( SAMPLE_DATA_FILE ) );
define( 'SAMPLE_DATA_URI', plugins_url( '/', SAMPLE_DATA_FILE ) );

require_once SAMPLE_DATA_DIR . 'classes/class-sample-data.php';
