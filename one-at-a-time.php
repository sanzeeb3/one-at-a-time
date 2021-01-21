<?php
/**
 * Plugin Name: One At A Time
 * Description: Only one administrator at a time can log into your site.
 * Version: 1.1.0
 * Author: Sanjeev Aryal
 * Author URI: http://www.sanjeebaryal.com.np
 * Text Domain: one-at-a-time
 * Domain Path: /languages/
 *
 * @package    One At A Time
 * @author     Sanjeev Aryal
 * @link       https://github.com/sanzeeb3/one-at-a-time
 * @since      1.0.0
 * @license    GPL-3.0+
 */

defined( 'ABSPATH' ) || exit;   // Exit if accessed directly.

define( 'ONE_AT_A_TIME', __FILE__ );

/**
 * Plugin version.
 *
 * @var string
 */
const ONE_AT_A_TIME_VERSION = '1.1.0';

require_once __DIR__ . '/src/Plugin.php';

/**
 * Return the main instance of Plugin Class.
 *
 * @since  1.0.0
 *
 * @return Plugin.
 */
function one_at_a_time() {
	return \OneAtATime\Plugin::get_instance();
}

one_at_a_time();
