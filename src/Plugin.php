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
		add_action( 'init', array( $this, 'update_online_users_status' ) );
		add_action( 'init', array( $this, 'logout' ) );
		add_filter( 'wp_authenticate_user', array( $this, 'check_availability' ) );
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
		load_plugin_textdomain( 'one-at-a-time', false, plugin_basename( dirname( ONE_AT_A_TIME ) ) . '/languages' );
	}

	/**
	 * Checks if any administator (other than the currently logged in) is online or not.
	 *
	 * @since  1.0.0
	 *
	 * @return bool|int false or User ID.
	 */
	public function is_any_administrator_online() {

		$args = array(
			'role' => 'administrator',
		);

		$users = get_users( $args );

		// Get the online users list
		$logged_in_users = get_transient( 'online_status' );
		$current_user_id = get_current_user_id();

		foreach ( $users as $user ) {

			if ( (int) $current_user_id === (int) $user->ID ) {
				continue;
			}

			if ( isset( $logged_in_users[ $user->ID ] ) && ( $logged_in_users[ $user->ID ] > ( time() - ( 1 * 60 ) ) ) ) {
				return $user->ID;
			}
		}

		return false;
	}

	/**
	 * Just in case...
	 *
	 * Actually in case if the previously logged-in user was inactive for some time and is re-active now.
	 *
	 * @since  1.0.0
	 *
	 * @return void.
	 */
	public function logout() {

		// Don't do this for non-administrators.
		if( ! current_user_can('administrator') ) {
			return;
		}

		if ( $this->is_any_administrator_online() ) {

			// Get all sessions for user with ID $user_id
			$sessions = \WP_Session_Tokens::get_instance( get_current_user_id() );

			// We have got the sessions, destroy them all!
			$sessions->destroy_all();
		}
	}

	/**
	 * Display error on login if any administator is logged in.
	 *
	 * @since  1.0.0
	 *
	 * @param $user WP_User Object
	 */
	public function check_availability( \WP_User $user ) {

		// Don't do this for non-administrators.
		if( ! in_array( 'administrator', $user->roles, true ) ) {
			return $user;
		}

		if ( $this->is_any_administrator_online() && $user->ID !== $this->is_any_administrator_online() ) {

			$user_info = \get_userdata( $this->is_any_administrator_online() );

			$message = sprintf( 
							esc_html__( 'Another administrator %1s is currently logged in.', 'one-at-a-time' ),
							$user_info->first_name
						);

			return new \WP_Error( 'another_admin_is_currently_logged_in', $message );
		}

		return $user;
	}

	/**
	 * Store last login info in usermeta table.
	 *
	 * This method is extracted from https://github.com/sanzeeb3/wp-force-logout/blob/master/includes/class-wp-force-logout-process.php
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

	/**
	 * Update online users status. Store in transient.
	 *
	 * This method is extracted from https://github.com/sanzeeb3/wp-force-logout/blob/master/includes/class-wp-force-logout-process.php
	 *
	 * @since  1.0.0
	 *
	 * @return void.
	 */
	public function update_online_users_status() {

		// Get the user online status list.
		$logged_in_users = get_transient( 'online_status' );

		// Get current user ID
		$user = wp_get_current_user();

		// Check if the current user needs to update his online status;
		// Needs if user is not in the list.
		$no_need_to_update = isset( $logged_in_users[ $user->ID ] )

			// And if his "last activity" was less than let's say ...6 seconds ago
			&& $logged_in_users[ $user->ID ] > ( time() - ( 1 * 60 ) );

		// Update the list if needed
		if ( ! $no_need_to_update ) {
			$logged_in_users[ $user->ID ] = time();
			set_transient( 'online_status', $logged_in_users, $expire_in = ( 60 * 60 ) ); // 60 mins
		}
	}
}
