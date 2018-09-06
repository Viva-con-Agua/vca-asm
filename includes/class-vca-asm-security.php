<?php

/**
 * VCA_ASM_Security class.
 *
 * This class extends the overall security of the system.
 * It handles password security,
 * reset cycles
 * and automatic logout when idle.
 *
 * @package VcA Activity & Supporter Management
 * @since 1.2
 *
 * Structure:
 * - Properties
 * - Constructor
 * - Utility
 */

if ( ! class_exists( 'VCA_ASM_Security' ) ) :

class VCA_ASM_Security
{

	/* ============================= CLASS PROPERTIES ============================= */

	/**
	 * Holds the security related options (DB: WordPress options table)
	 *
	 * @var array $options
	 * @see constructor / init method
	 * @since 1.3
	 * @access private
	 */
	private $options = array();

	/**
	 * Holds the mode related options (DB: WordPress options table)
	 *
	 * @var array $mode_options
	 * @see constructor / init method
	 * @since 1.3
	 * @access private
	 */
	private $mode_options = array();

	/**
	 * Holds (translatable, human-readable) terms for the below strength classes
	 *
	 * @var string[] $strength_terms
	 * @see constructor / init method
	 * @since 1.3
	 * @access private
	 */
	private $strength_terms = array();

	/**
	 * Holds the varying levels of password strength
	 *
	 * @var string[] $strength_classes
	 * @see constructor / init method
	 * @since 1.3
	 * @access private
	 */
	private $strength_classes = array();

	/* ============================= CONSTRUCTOR (AND IMMEDIATELY RELATED CODE) ============================= */

    /**
     * Constructor
     *
     * @since 1.0
     * @access public
     */
	public function __construct()
	{
		$this->init();
		add_action( 'user_profile_update_errors', array( $this, 'enforce_pass_strength' ), 0, 3 );
		add_action( 'wp_login', array( $this, 'on_login' ), 1, 2 );
		add_filter( 'login_redirect', array( $this, 'pass_reset_redirect' ), 10, 3 );
		add_action( 'get_header', array( $this, 'on_pageload' ), 1 );
		add_action( 'admin_init', array( $this, 'on_pageload' ), 1 );
		add_shortcode( 'vca-asm-logout-message', array( $this, 'logout_message' ) );
		add_shortcode( 'vca-asm-pass-reset', array( $this, 'pass_reset_form' ) );
	}

	/**
	 * Assigns values to class properties
	 *
	 * @return void
	 *
	 * @since 1.2
	 * @access private
	 */
	private function init()
	{
		$this->options = get_option( 'vca_asm_security_options' );
		$this->mode_options = get_option( 'vca_asm_mode_options' );
		$this->strength_terms = array(
			1 => __( 'very weak', 'vca-asm' ),
			2 => __( 'weak', 'vca-asm' ),
			3 => __( 'medium', 'vca-asm' ),
			4 => __( 'strong', 'vca-asm' ),
			5 => __( 'mismatch', 'vca-asm' )
		);
		$this->strength_classes = array(
			1 => 'short',
			2 => 'bad',
			3 => 'good',
			4 => 'strong',
			5 => 'mismatch'
		);
	}

	/* ============================= UTILITY METHODS ============================= */

	/**
	 * Processed every pageload
	 *
	 * checks idle time against max duration
	 * also checks pass age
	 * Hooked with 'get_header' (frontend) and 'admin_init' (backend)
	 *
	 * @return void
	 *
	 * @since 1.2
	 * @access public
	 */
	public function on_pageload()
	{
		if ( is_user_logged_in() ) {
			$last_activity = $this->get_last_activity();
			$max_idle_duration = $this->options['automatic_logout_period'] * 60;
			$boundary = $last_activity + $max_idle_duration;
			if( $max_idle_duration > 0 && $boundary < time() ) {
				wp_logout();
				wp_redirect( get_site_option( 'home' ) . '/?logged_out=1' );
			} elseif ( ! $this->check_pass_age() && ! is_page( 'bitte-passwort-erneuern' ) ) {
				wp_redirect( get_site_option('home') . '/bitte-passwort-erneuern/' );
			} else {
				$this->update_last_activity();
			}
		}
	}

	/**
	 * Processed when a user logs in
	 *
	 * Sets last activity user meta
	 * Hooked with 'wp_login'
	 *
	 * @param string $user_login		username (not used in this method)
	 * @param object $user				WP_User object
	 * @return void
	 *
	 * @since 1.2
	 * @access public
	 */
	public function on_login( $user_login, $user )
	{
		if ( $user->ID != null && $user->ID > 0 ) {
			if (
				'maintenance' === $this->mode_options['mode'] &&
				! in_array( 'administrator', $user->roles ) &&
				! in_array( 'management_global', $user->roles )
			) {
				wp_logout();
			} else {
				update_user_meta( $user->ID, 'vca_asm_last_activity', time() );
			}
		}
	}

	/**
	 * Enforces password strength as set in options
	 *
	 * @param object $errors		WP_Errors object
	 * @param bool $update			whether an existing user is updated or a new one created (not used in below action callback)
	 * @param object $user			WP_User object
	 * @return object $errors		(modified) WP_Errors object
	 *
	 * @since 1.2
	 * @access public
	 */
	public function enforce_pass_strength( $errors, $update, $user )
	{
		$supp_level = $this->options['pass_strength_supporter'];
		$admin_level = $this->options['pass_strength_admin'];
		$user_id = $user->ID;
		$user_login = ! empty( $_POST['user_login'] ) ? $_POST['user_login'] : ( ! empty( $user->user_login ) ? $user->user_login : '' );

		if ( $user_id ) {
			$user_obj = new WP_User( $user_id );
			if( in_array( 'supporter', $user_obj->roles ) ) {
				$level = $supp_level;
			} else {
				$level = $admin_level;
			}
		} else {
			$level = $supp_level;
		}

		/* Hack for stupid-ass Kevin */
		$is_kevin = ( 79 /* Micha */ === $user_id || 615 /* Benny */ === $user_id );
		/* End Kevin */

		if ( ! $errors->get_error_data('pass') && empty ( $_POST['pass1'] ) && is_page( 'bitte-passwort-erneuern' ) ) {
			$error = __( 'You must enter something...', 'vca-asm' );
			$errors->add( 'pass', $error );
		} elseif ( ! $errors->get_error_data('pass') &&
			isset($_POST['pass1']) && isset($_POST['pass2']) &&
			$_POST['pass1'] !== $_POST['pass2']
		) {
			$error = __( 'The passwords you entered do not match...', 'vca-asm' );
			$errors->add( 'pass', $error );
		} elseif ( ! $errors->get_error_data('pass') &&
            isset($_POST['pass1']) && isset($_POST['pass2']) &&
			$this->password_strength( $_POST['pass1'], $user_login, $is_kevin ) < $level
		) {
			$error = __( 'The password you have chosen is not strong enough.', 'vca-asm' ) . '<br />' .
				sprintf(
					__( 'It must at least be &quot;%s&quot;. See the strength indicator below the password-fields.', 'vca-asm' ),
					$this->strength_terms[$level]
				);
			$errors->add( 'pass', $error );
		} elseif ( ! $errors->get_error_data('pass') && isset($_POST['pass1']) && isset($_POST['pass2'])  ) {
			$same = wp_check_password( $_POST['pass1'], $user_obj->user_pass );
			if ( false !== $same && ! $is_kevin ) {
				$error = __( 'You cannot replace your old password with itsself...', 'vca-asm' );
				$errors->add( 'pass', $error );
			}
		}
		if ( empty( $errors->errors ) && isset( $user_obj ) && ! empty ( $_POST['pass1'] ) ) {
			update_user_meta( $user_obj->ID, 'vca_asm_last_pass_reset', time() );
			if( in_array( 'head_of', $user_obj->roles ) || in_array( 'city', $user_obj->roles ) ) {
				global $wpdb;
				$wpdb->update(
					$wpdb->prefix.'vca_asm_geography',
					array( 'pass' => base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5(REGION_KEY), $_POST['pass1'], MCRYPT_MODE_CBC, md5(md5(REGION_KEY)) ) ) ),
					array( 'user_id' => $user_obj->ID ),
					array( '%s' ),
					array( '%d' )
				);
			}
		}
		return $errors;
	}

    /**
     * Processed when a user logs in
     * Hooked with 'login_redirect'
     *
     * @since 1.2
     * @access public
     * @param $redirect_to
     * @param string $url_redirect_to
     * @param null $user
     * @return string
     */
	public function pass_reset_redirect( $redirect_to, $url_redirect_to = '', $user = null )
	{
		if( isset( $user->ID ) && ! $this->check_pass_age( $user, true ) ) {
			return get_site_option('home') . '/bitte-passwort-erneuern/';
		}
		return $redirect_to;
	}

	/* ============================= SHORTCODE HANDLERS ============================= */

	/**
	 * Shortcode handler to output
	 * the form for resetting the password
	 *
	 * @param array $atts			shortcode attributes
	 * @return string $output		HTML formatted output string
	 *
	 * @see constructor
	 *
	 * @since 1.2
	 * @access public
	 */
	public function pass_reset_form( $atts = array() )
	{
		if ( ! is_user_logged_in() ) {
			return 'Really?';
		}

		global $current_user;
		if( $current_user->user_login === $_POST['user_login'] ) {
			$errors = new WP_Error();
			$errors = $this->enforce_pass_strength( $errors, true, $current_user );
			$errors = $errors->get_error_messages( 'pass' );
			if( empty( $errors ) ) {
				wp_update_user(
					array(
						'ID' => $current_user->ID,
						'user_pass' => $_POST['pass1']
					)
				);
			}
		}

		if( in_array( 'supporter', $current_user->roles ) ) {
			$level = $this->options['pass_strength_supporter'];
			$cycle = $this->options['pass_reset_cycle_supporter'];
		} else {
			$level = $this->options['pass_strength_admin'];
			$cycle = $this->options['pass_reset_cycle_admin'];
		}

		if( isset( $errors ) && empty( $errors ) ) {
			$output = '<div class="system-message"><h3>' .
						__( 'Password updated!', 'vca-asm' ) .
					'</h3><p>' .
						__( 'You have successfully updated your password.', 'vca-asm' ) . '<br />' .
						'<a href="' . get_site_option( 'home' ) .  '" title="' .
							__( 'Back to the Pool!', 'vca-asm' ) . '" >' .
								'&larr; ' .__( 'Log in with the new password', 'vca-asm' ) .
						'</a>' .
				'</p></div>';
			return $output;
		} else {
			$output = '<div class="system-error"><h3>' .
						__( 'Please renew your password', 'vca-asm' ) .
					'</h3><p>' .
						sprintf( __( 'Users of the Pool are required to renew their password every %d months. Either your password is that old or a global password reset has been initiated.', 'vca-asm' ), $cycle ).
				'</p></div>';
		}

		wp_enqueue_script( 'password-strength-meter' );
		wp_enqueue_script( 'vca-asm-strength-meter-init' );
		$params = array(
			'classes' => $this->strength_classes,
			'terms' => $this->strength_terms
		);
		wp_localize_script( 'vca-asm-strength-meter-init', 'VCAasmMeter', $params );

		$output .= '<div class="island">';
		if( ! empty( $errors ) ) {
			foreach( $errors as $error ) {
				$output .= '<p class="error">' . $error . '</p>';
			}
		}
		$output .= '<form name="resetpasswordform" id="resetpasswordform" class="stand-alone-form" action="" method="post">' .
				'<div class="form-row">' .
					'<label for="pass1">' . __( 'New password', 'vca-asm' ) . '</label>' .
					'<input autocomplete="off" name="pass1" id="pass1" class="input" size="20" value="" type="password" />' .
				'</div><div class="form-row">' .
					'<label for="pass2">' . __( 'Confirm new password', 'vca-asm' ) . '</label>' .
					'<input autocomplete="off" name="pass2" id="pass2" class="input" size="20" value="" type="password" />' .
				'</div><div class="form-row">' .
					'<div id="pass-strength-result" class="no-js-hide">' . __( 'Strength indicator', 'vca-asm' ) . '</div>' .
				'</div><div class="form-row">' .
				'<p class="description indicator-hint">' .
					sprintf(
						__( 'The password must at least be &quot;%s&quot;.', 'vca-asm' ),
						$this->strength_terms[$level]
					) .
				'</p>' .
			'</div>';

		do_action( 'resetpassword_form' );

		$output .= '<div class="form-row">' .
				'<input type="submit" name="wp-submit" id="wp-submit" value="' . __( 'Set new Password', 'vca-asm' ) . '" />' .
				'<input type="hidden" name="user_login" id="user_login" value="' . $current_user->user_login . '" />' .
			'</div></form></div></div>';

		return $output;
	}

	/**
	 * Shortcode handler to output the message upon automatic logout
	 *
	 * @param array $atts			shortcode attributes
	 * @return string $output		HTML formatted output string
	 *
	 * @see constructor
	 *
	 * @since 1.2
	 * @access public
	 */
	public function logout_message( $atts = array() ) {
		$output = '';
		if ( 'maintenance' === $this->mode_options['mode'] ) {
			$output = '<div class="system-error"><h3>' .
						__( 'Maintenance Mode', 'vca-asm' ) .
					'</h3><p>' .
						__( 'The Pool is currently in maintenance mode. Please come back in 24 hours.', 'vca-asm' ) .
					'</p><p>' .
						__( 'Your friendly neighborhood Pool-Administration.', 'vca-asm' ) .
					'</p>' .
				'</div>';
		} elseif ( ! is_user_logged_in() && isset( $_GET['logged_out'] ) && 1 == $_GET['logged_out'] ) {
			$output = '<div class="system-error"><h3>' .
						__( 'Logged out...', 'vca-asm' ) .
					'</h3><p>' .
						sprintf( __( 'You have been logged out. After %d minutes of inactivity, users of the Pool are automatically logged out of the system.', 'vca-asm' ), $this->options['automatic_logout_period'] ) .
				'</p></div>';
		}
		return $output;
	}

	/* ============================= UTILITY METHODS ============================= */

	/**
	 * Updates the last activity timestamp
	 *
	 * @param object $user		WP_User object
	 *
	 * @see on_pageload
	 *
	 * @since 1.2
	 * @access private
	 */
	private function update_last_activity( $user = null )
	{
		if ( empty( $user ) ) {
			global $current_user;
			$user = $current_user;
		}
		update_user_meta( $user->ID, 'vca_asm_last_activity', time() );
	}

	/**
	 * Retrieves the last activity timestamp
	 *
	 * @param NULL|object $user		(optional) WP_User object
	 * @return integer				timestamp of last login from the user_meta table
	 *
	 * @since 1.2
	 * @access private
	 */
	private function get_last_activity( $user = null )
	{
		if ( empty( $user ) ) {
			global $current_user;
			$user = $current_user;
		}
		return get_user_meta( $user->ID, 'vca_asm_last_activity',true );
	}

	/**
	 * Checks the age of the current password against current date and
	 *
	 * @param NULL|object $user		(optional) WP_User object
	 * @param bool $set_it			(optional) whether to set a new stamp in user metadata
	 * @return bool					whether the passowrd is still young enough
	 *
	 * @since 1.2
	 * @access private
	 */
	private function check_pass_age( $user = null, $set_it = false )
	{
		if ( empty( $user ) ) {
			global $current_user;
			$user = $current_user;
		}
		if ( in_array( 'supporter', $user->roles ) ) {
			$max_pass_age = $this->options['pass_reset_cycle_supporter'];
		} else {
			$max_pass_age = $this->options['pass_reset_cycle_admin'];
		}
		if( empty( $max_pass_age ) ) {
			return true;
		}
		$max_pass_age = $max_pass_age * 2678400;
		$global_reset = isset( $this->options['global_pass_reset'] ) ? $this->options['global_pass_reset'] : false;
		$last_reset = get_user_meta( $user->ID, 'vca_asm_last_pass_reset', true );
		if( true === $set_it && '' === $last_reset && empty( $global_reset ) ) {
			$last_reset = time();
			update_user_meta( $user->ID, 'vca_asm_last_pass_reset', $last_reset );
		}
		$last_reset = '' ? 0 : floatval( $last_reset );
		if ( ! empty( $global_reset ) ) {
			$max_pass_age = ( $max_pass_age < ( time() - $global_reset ) ) ? $max_pass_age : ( time() - $global_reset );
		}
		$boundary = $last_reset + $max_pass_age;
		if ( $boundary < time() ) {
			return false;
		}
		return true;
	}

	/**
	 * Determines pass strength
	 *
	 * Returns integer value between 1 and 4
	 * depending on the strength coefficient of the password calculated in this method.
	 * Always returns 4 (strong password) if the user in question is one of the two daftest computer users ever.
	 *
	 * @param string $pass			the password, (plain text) form of the user-input
	 * @param string $username		(optional) the username for comparison with pass (should), defaults to empty string
	 * @param bool $is_kevin		(optional) whether the user in question is a Kevin, defaults to false
	 * @return int $strength		password strength as integer value between 1 (weak) and 4 (strong)
	 *
	 * @since 1.2
	 * @access private
	 */
	private function password_strength( $pass, $username = '', $is_kevin = false )
	{
		/* Hack for stupid-ass Kevin */
		if ( $is_kevin ) {
			return 4;
		}
		/* End Kevin */

		$str_coeff = 0;

		if ( strlen( $pass ) < 4 ) {
			return 1;
		} elseif ( strtolower( $pass ) == strtolower( $username ) ) {
			return 1;
		}

		if ( preg_match( "/[0-9]/", $pass ) ) {
			$str_coeff += 10;
		}
		if ( preg_match( "/[a-z]/", $pass ) ) {
			$str_coeff += 26;
		}
		if ( preg_match( "/[A-Z]/", $pass ) ) {
			$str_coeff += 26;
		}
		if ( preg_match( "/[^a-zA-Z0-9]/", $pass ) ) {
			$str_coeff += 31;
		}

		$str_log = log( pow( $str_coeff, strlen( $pass ) ) ) / log( 2 );

		if ( $str_log > 55 ) {
			$strength = 4;
		} elseif ( $str_log > 39 ) {
			$strength = 3;
		} else {
			$strength = 2;
		}

		return $strength;
	}

} // class

endif; // class exists

?>