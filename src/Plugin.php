<?php

namespace OneAtATime;

defined( 'ABSPATH' ) || exit;   // Exit if accessed directly.

/**
 * Plugin Class.
 *
 * @since 1.0.0
 */
final class Plugin {

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * One At A Time Constructor.
	 */
	public function __construct() {

		// Load plugin text domain.
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'init', array( $this, 'update_last_login' ) );
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/one-at-a-time/one-at-a-time-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/one-at-a-time-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'one-at-a-time' );

		load_textdomain( 'one-at-a-time', WP_LANG_DIR . '/one-at-a-time/one-at-a-time-' . $locale . '.mo' );
		load_plugin_textdomain( 'one-at-a-time', false, plugin_basename( dirname( COME_BACK ) ) . '/languages' );
	}

	/**
	 * Store last login info in usermeta table.
	 *
	 * @since  1.0.0
	 *
	 * @return void.
	 */
	public function update_last_login() {

		$user_id = get_current_user_id();

		if ( $user_id ) {
			update_user_meta( $user_id, 'last_login', time() );
		}
	}
}
