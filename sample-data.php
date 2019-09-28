<?php
/**
 * Plugin Name: Sample Data
 * Description: Download sample data of Theme Unit Test, WooCommerce, bbPress etc with one click.
 * Plugin URI: https://profiles.wordpress.org/mahesh901122/
 * Author: Mahesh M. Waghmare
 * Author URI: https://maheshwaghmare.wordpress.com/
 * Version: 1.1.0
 * License: GNU General Public License v2.0
 * Text Domain: sample-data
 *
 * @package Sample Data
 */

// Set constants.
define( 'SAMPLE_DATA_VER', '1.1.0' );
define( 'SAMPLE_DATA_FILE', __FILE__ );
define( 'SAMPLE_DATA_BASE', plugin_basename( SAMPLE_DATA_FILE ) );
define( 'SAMPLE_DATA_DIR', plugin_dir_path( SAMPLE_DATA_FILE ) );
define( 'SAMPLE_DATA_URI', plugins_url( '/', SAMPLE_DATA_FILE ) );

require_once SAMPLE_DATA_DIR . 'classes/class-sample-data.php';
