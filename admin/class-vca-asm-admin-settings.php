<?php

/**
 * VCA_ASM_Admin_Settings class.
 *
 * This class contains properties and methods for
 * the activities management
 *
 * @package VcA Activity & Supporter Management
 * @since 1.2
 */

if ( ! class_exists( 'VCA_ASM_Admin_Settings' ) ) :

class VCA_ASM_Admin_Settings {

	/**
	 * Class Properties
	 *
	 * @since 1.2
	 * @access public
	 */
	public $security_options_values = array();
	public $security_options = array();
	public $emails_options_values = array();

	/**
	 * Assigns values to class properties
	 *
	 * @since 1.2
	 * @access private
	 */
	private function init() {

		$this->security_options_values = get_option( 'vca_asm_security_options' );
		$this->emails_options_values = get_option( 'vca_asm_emails_options' );

		$pass_strength_classes = array(
			1 => 'short',
			2 => 'bad',
			3 => 'good',
			4 => 'strong'
		);
		$pass_strength_contents =  array(
			1 => __( 'very weak', 'vca-asm' ),
			2 => __( 'weak', 'vca-asm' ),
			3 => __( 'medium', 'vca-asm' ),
			4 => __( 'strong', 'vca-asm' )
		);
		$values = array(
			( is_numeric( $this->security_options_values['pass_strength_supporter'] ) ) ? $this->security_options_values['pass_strength_supporter'] : 3,
			( is_numeric( $this->security_options_values['pass_strength_admin'] ) ) ? $this->security_options_values['pass_strength_admin'] : 4,
			( is_numeric( $this->security_options_values['pass_reset_cycle_supporter'] ) ) ? $this->security_options_values['pass_reset_cycle_supporter'] : 0,
			( is_numeric( $this->security_options_values['pass_reset_cycle_admin'] ) ) ? $this->security_options_values['pass_reset_cycle_admin'] : 6,
			( is_numeric( $this->security_options_values['automatic_logout_period'] ) ) ? $this->security_options_values['automatic_logout_period'] : 20
		);
		$this->security_options = array(
			0 => array(
				'id' => 'pass_strength_supporter',
				'section' => 'pass_strength',
				'title' => _x( 'Supporter', 'Settings Admin Menu', 'vca-asm' ),
				'min' => 1,
				'max' => 4,
				'step' => 1,
				'value' => $values[0],
				'callback' => 'class_change',
				'classes' => $pass_strength_classes,
				'content' => $pass_strength_contents,
				'append' => '<div id="pass-strength-result" style="display: block;" class="no-js-hide ' . $pass_strength_classes[$values[0]] . '">' .
						$pass_strength_contents[$values[0]] .
					'</div>'
			),
			1 => array(
				'id' => 'pass_strength_admin',
				'section' => 'pass_strength',
				'title' => _x( 'Access to Administration', 'Settings Admin Menu', 'vca-asm' ),
				'min' => 1,
				'max' => 4,
				'step' => 1,
				'value' => $values[1],
				'callback' => 'class_change',
				'classes' => $pass_strength_classes,
				'content' => $pass_strength_contents,
				'append' => '<div id="pass-strength-result" style="display: block;" class="no-js-hide ' . $pass_strength_classes[$values[1]] . '">' .
						$pass_strength_contents[$values[1]] .
					'</div>'
			),
			2 => array(
				'id' => 'pass_reset_cycle_supporter',
				'section' => 'pass_reset_cycle',
				'title' => _x( 'Supporter', 'Settings Admin Menu', 'vca-asm' ),
				'min' => 0,
				'max' => 12,
				'step' => 1,
				'value' => $values[2],
				'callback' => 'number',
				'append' => ' ' . __( 'Months', 'vca-asm' ),
				'never' => ' ' . __( 'never', 'vca-asm' )
			),
			3 => array(
				'id' => 'pass_reset_cycle_admin',
				'section' => 'pass_reset_cycle',
				'title' => _x( 'Access to Administration', 'Settings Admin Menu', 'vca-asm' ),
				'min' => 0,
				'max' => 12,
				'step' => 1,
				'value' => $values[3],
				'callback' => 'number',
				'append' => ' ' . __( 'Months', 'vca-asm' ),
				'never' => ' ' . __( 'never', 'vca-asm' )
			),
			4 => array(
				'id' => 'automatic_logout_period',
				'section' => 'automatic_logout',
				'title' => _x( 'Automatic Logout', 'Settings Admin Menu', 'vca-asm' ),
				'min' => 0,
				'max' => 60,
				'step' => 5,
				'value' => $values[4],
				'callback' => 'number',
				'append' => ' ' . __( 'Minutes', 'vca-asm' ),
				'never' => ' ' . __( 'never', 'vca-asm' )
			)
		);
	}

	/******************** MENU OUTPUT ********************/

	/**
	 * Controller for the Settings Admin Menus
	 *
	 * @since 1.2
	 * @access public
	 */
	public function control() {

		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'emails';

		echo '<div class="wrap">' .
			'<div id="icon-settings" class="icon32-pa"></div><h2>' . _x( 'Settings', 'Settings Admin Menu', 'vca-asm' ) . '</h2><br />';

		settings_errors();

		echo '<h2 class="nav-tab-wrapper">' .
				'<a href="?page=vca-asm-settings&tab=emails" class="nav-tab ' . ( $active_tab == 'emails' ? 'nav-tab-active' : '' ) . '">' .
					'<div class="nav-tab-icon nt-icon-emails"></div>' .
					_x( 'Emails', 'Settings Admin Menu', 'vca-asm' ) .
				'</a>' .
				'<a href="?page=vca-asm-settings&tab=responses" class="nav-tab ' . ( $active_tab == 'responses' ? 'nav-tab-active' : '' ) . '">' .
					'<div class="nav-tab-icon nt-icon-emails"></div>' .
					_x( 'Automatic Responses', 'Settings Admin Menu', 'vca-asm' ) .
				'</a>' .
				'<a href="?page=vca-asm-settings&tab=security" class="nav-tab ' . ( $active_tab == 'security' ? 'nav-tab-active' : '' ) . '">' .
					'<div class="nav-tab-icon nt-icon-security"></div>' .
					_x( 'Security', 'Settings Admin Menu', 'vca-asm' ) .
				'</a>' .
			'</h2>';

		if( $active_tab == 'responses' ) {
			$this->autoresponses_edit();
		} elseif( $active_tab == 'security' ) {
			$this->security_menu();
		} else {
			$this->emails_menu();
		}

		echo '</div>';
	}

	/**
	 * Outputs form to edit autoresponse texts and saves them to the database
	 *
	 * @since 1.0
	 * @access public
	 */
	public function autoresponses_edit() {
		global $wpdb;

		$url = "admin.php?page=vca-asm-settings&amp;tab=responses";
		$form_action = $url . "&amp;todo=save";
		$output = '';

		$fields = array(
			array(
				'type' => 'section',
				'label' => _x( 'Application Confirmation', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'checkbox',
				'label' => _x( 'Send Mail Switch', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'applied-switch',
				'desc' => _x( 'Enable/disable application confirmations', 'Admin Email Interface', 'vca-asm' ) . _x( '(this goes for the default responses as well)', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'text',
				'label' => _x( 'Subject', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'applied-subject',
				'desc' => _x( 'Subject line for application confirmations', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'textarea',
				'label' => _x( 'Message', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'applied-message',
				'desc' => _x( 'Message body for application confirmations', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'section',
				'label' => _x( 'Registration Confirmation / Application Acceptance', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'checkbox',
				'label' => _x( 'Send Mail Switch', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'accepted-switch',
				'desc' => _x( 'Enable/disable registration confirmations', 'Admin Email Interface', 'vca-asm' ) . _x( '(this goes for the default responses as well)', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'text',
				'label' => _x( 'Subject', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'accepted-subject',
				'desc' => _x( 'Subject line for acceptance to event', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'textarea',
				'label' => _x( 'Message', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'accepted-message',
				'desc' => _x( 'Message body for acceptance to event', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'section',
				'label' => _x( 'Deny Application', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'checkbox',
				'label' => _x( 'Send Mail Switch', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'denied-switch',
				'desc' => _x( 'Enable/disable application denial notifications', 'Admin Email Interface', 'vca-asm' ) . _x( '(this goes for the default responses as well)', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'text',
				'label' => _x( 'Subject', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'denied-subject',
				'desc' => _x( 'Subject line for application denial', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'textarea',
				'label' => _x( 'Message', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'denied-message',
				'desc' => _x( 'Message body for application denial', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'section',
				'label' => _x( 'Withdraw Registration', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'checkbox',
				'label' => _x( 'Send Mail Switch', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'reg_revoked-switch',
				'desc' => _x( 'Enable/disable notifications of revoked registrations', 'Admin Email Interface', 'vca-asm' ) . _x( '(this goes for the default responses as well)', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'text',
				'label' => _x( 'Subject', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'reg_revoked-subject',
				'desc' => _x( 'Subject line for notifications of revoked registrations', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'textarea',
				'label' => _x( 'Message', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'reg_revoked-message',
				'desc' => _x( 'Message body for notifications of revoked registrations', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'section',
				'label' => _x( 'Accept Membership', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'checkbox',
				'label' => _x( 'Send Mail Switch', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'mem_accepted-switch',
				'desc' => _x( 'Enable/disable notifications of accepted memberships to Cell / Local Crew', 'Admin Email Interface', 'vca-asm' ) . _x( '(this goes for the default responses as well)', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'text',
				'label' => _x( 'Subject', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'mem_accepted-subject',
				'desc' => _x( 'Subject line for notifications of accepted memberships to Cell / Local Crew', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'textarea',
				'label' => _x( 'Message', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'mem_accepted-message',
				'desc' => _x( 'Message body for notifications of accepted memberships to Cell / Local Crew', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'section',
				'label' => _x( 'Deny Membership', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'checkbox',
				'label' => _x( 'Send Mail Switch', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'mem_denied-switch',
				'desc' => _x( 'Enable/disable notifications of denied memberships to Cell / Local Crew', 'Admin Email Interface', 'vca-asm' ) . _x( '(this goes for the default responses as well)', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'text',
				'label' => _x( 'Subject', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'mem_denied-subject',
				'desc' => _x( 'Subject line for notifications of denied memberships to Cell / Local Crew', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'textarea',
				'label' => _x( 'Message', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'mem_denied-message',
				'desc' => _x( 'Message body for notifications of denied memberships to Cell / Local Crew', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'section',
				'label' => _x( 'Cancel Membership', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'checkbox',
				'label' => _x( 'Send Mail Switch', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'mem_cancelled-switch',
				'desc' => _x( 'Enable/disable notifications when memberships to Cell / Local Crew are cancelled', 'Admin Email Interface', 'vca-asm' ) . _x( '(this goes for the default responses as well)', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'text',
				'label' => _x( 'Subject', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'mem_cancelled-subject',
				'desc' => _x( 'Subject line for notifications when memberships to Cell / Local Crew are cancelled', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'textarea',
				'label' => _x( 'Message', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'mem_cancelled-message',
				'desc' => _x( 'Message body for notifications when memberships to Cell / Local Crew are cancelled', 'Admin Email Interface', 'vca-asm' )
			)
		);

		if( isset( $_GET['todo'] ) && $_GET['todo'] == 'save' ) {
			$output .= '<div class="message"><p>' .
				__( 'Options successfully updated!', 'vca-asm' ) .
				'</p></div>';
		}

		$output .= '<p>' .
				_x( 'Here you can enable or disable, as well as overwrite the default autoresponses sent when a supporter completes a certain action.', 'Admin Email Autoresponse Options', 'vca-asm' ) .
				'</p><p>' .
				_x( 'You can use the following placeholders (if applicable)', 'Admin Email Autoresponse Options', 'vca-asm' ) .
				' :</p><p>' .
				'%event% - ' . _x( 'The title of the event in question', 'Admin Email Autoresponse Options', 'vca-asm' ) . '<br />' .
				'%region% - ' . _x( 'The name of the region in question', 'Admin Email Autoresponse Options', 'vca-asm' ) . '<br />' .
				'%name% - ' . _x( 'The name of the supporter', 'Admin Email Autoresponse Options', 'vca-asm' ) .
				'</p><form name="vca_asm_responses_edit_form" method="post" action="' . $form_action . '">' .
					get_submit_button() .
					'<input type="hidden" name="submitted" value="y"/>';

		/* populate fields */
		$fcount = count($fields);
		for ( $i = 0; $i < $fcount; $i++ ) {
			if( $fields[$i]['type'] != 'section' ) {
				$id = explode( '-', $fields[$i]['id'] );
				$action = $id[0];
				$column = $id[1];
				if( ! isset( $_POST['submitted'] ) ) {
					$data = $wpdb->get_results(
						"SELECT " . $column . " FROM " .
						$wpdb->prefix . "vca_asm_auto_responses " .
						"WHERE action = '" . $action . "' LIMIT 1", ARRAY_A
					);
					$fields[$i]['value'] = $data[0][$column];
				} elseif( $fields[$i]['type'] == 'checkbox' ) {
					if( isset( $_POST[$fields[$i]['id']] ) ) {
						$fields[$i]['value'] = 1;
					} else {
						$fields[$i]['value'] = 0;
					}
				} else {
					$fields[$i]['value'] = $_POST[$fields[$i]['id']];
				}
			}
			/* save */
			if( isset( $_GET['todo'] ) && $_GET['todo'] == 'save' && $fields[$i]['type'] != 'section' ) {
				if( $fields[$i]['type'] != 'checkbox'  ) {
					$wpdb->update(
						$wpdb->prefix . 'vca_asm_auto_responses',
						array(
							$column => $_POST[$fields[$i]['id']]
						),
						array( 'action'=> $action ),
							array( '%s' ),
							array( '%s' )
					);
				} elseif( $fields[$i]['type'] == 'checkbox' && isset( $_POST[$fields[$i]['id']] ) ) {
					$wpdb->update(
						$wpdb->prefix . 'vca_asm_auto_responses',
						array(
							$column => 1
						),
						array( 'action'=> $action ),
							array( '%d' ),
							array( '%s' )
					);
				} elseif( $fields[$i]['type'] == 'checkbox' ) {
					$wpdb->update(
						$wpdb->prefix . 'vca_asm_auto_responses',
						array(
							$column => 0
						),
						array( 'action'=> $action ),
							array( '%d' ),
							array( '%s' )
					);
				}
			}
		}

		require( VCA_ASM_ABSPATH . '/templates/admin-form.php' );

		$output .= get_submit_button() . '</form>';

		echo $output;
	}

	/**
	 * Output of the Security Settings Admin Menu
	 *
	 * @since 1.2
	 * @access private
	 */
	private function security_menu() {

		wp_enqueue_script( 'vca-asm-admin-settings' );
		wp_localize_script( 'vca-asm-admin-settings', 'VCAasmAdmin', $this->security_options );

		echo '<form method="post" action="options.php">';
			submit_button();
			settings_fields( 'vca_asm_security_options' );
			do_settings_sections( 'vca_asm_security_options' );
			submit_button();
		echo '</form>';
	}

	/**
	 * Output of the Emails Settings Admin Menu
	 *
	 * @since 1.2
	 * @access private
	 */
	private function emails_menu() {

		echo '<form method="post" action="options.php">';
			submit_button();
			settings_fields( 'vca_asm_emails_options' );
			do_settings_sections( 'vca_asm_emails_options' );
			submit_button();
		echo '</form>';
	}

	/******************** WP OPTIONS ********************/

	/**
	 * WordPress Option Initialization
	 *
	 * @since 1.2
	 * @access public
	 */
	public function initialize_options() {

		$this->init();

		if( false == get_option( 'vca_asm_security_options' ) ) {
			add_option( 'vca_asm_security_options' );
		}
		if( false == get_option( 'vca_asm_emails_options' ) ) {
			add_option( 'vca_asm_emails_options' );
		}
		add_settings_section(
			'pass_strength',
			_x( '(Minimum) Password Strength', 'Settings Admin Menu', 'vca-asm' ),
			array( &$this, 'pass_strength_section' ),
			'vca_asm_security_options'
		);
		add_settings_section(
			'pass_reset_cycle',
			_x( 'Password Reset Cycle', 'Settings Admin Menu', 'vca-asm' ),
			array( &$this, 'pass_reset_cycle_section' ),
			'vca_asm_security_options'
		);
		add_settings_section(
			'automatic_logout',
			_x( 'Automatic Logout', 'Settings Admin Menu', 'vca-asm' ),
			array( &$this, 'automatic_logout_section' ),
			'vca_asm_security_options'
		);
		add_settings_section(
			'email_format',
			_x( 'Email Format', 'Settings Admin Menu', 'vca-asm' ),
			array( &$this, 'email_format_section' ),
			'vca_asm_emails_options'
		);

		foreach( $this->security_options as $option ) {
			add_settings_field(
				$option['id'],
				$option['title'],
				array( &$this, 'security_options_fields' ),
				'vca_asm_security_options',
				$option['section'],
				$option
			);
		}

		add_settings_field(
			'global_pass_reset',
			_x( 'Global Reset', 'Settings Admin Menu', 'vca-asm' ),
			array( &$this, 'global_reset_field' ),
			'vca_asm_security_options',
			'pass_reset_cycle',
			array( 'id' => 'global_pass_reset' )
		);
		add_settings_field(
			'email_format_admin',
			_x( 'Office / Administrators', 'Settings Admin Menu', 'vca-asm' ),
			array( &$this, 'email_format_fields' ),
			'vca_asm_emails_options',
			'email_format',
			array( 'id' => 'email_format_admin', 'value' =>  ! empty( $this->emails_options_values['email_format_admin'] ) ? $this->emails_options_values['email_format_admin'] : 'html' )
		);
		add_settings_field(
			'email_format_ho',
			_x( 'Head Ofs', 'Settings Admin Menu', 'vca-asm' ),
			array( &$this, 'email_format_fields' ),
			'vca_asm_emails_options',
			'email_format',
			array( 'id' => 'email_format_ho', 'value' =>  ! empty( $this->emails_options_values['email_format_ho'] ) ? $this->emails_options_values['email_format_ho'] : 'plain' )
		);
		add_settings_field(
			'email_format_auto',
			_x( 'Automatic Responses', 'Settings Admin Menu', 'vca-asm' ),
			array( &$this, 'email_format_fields' ),
			'vca_asm_emails_options',
			'email_format',
			array( 'id' => 'email_format_auto', 'value' =>  ! empty( $this->emails_options_values['email_format_auto'] ) ? $this->emails_options_values['email_format_auto'] : 'plain' )
		);

		register_setting(
			'vca_asm_security_options',
			'vca_asm_security_options'
		);
		register_setting(
			'vca_asm_emails_options',
			'vca_asm_emails_options'
		);
	}
	/**
	 * WordPress Option Callbacks
	 *
	 * @since 1.2
	 * @access public
	 */
	public function pass_strength_section() {
		echo '<p>' . _x( 'The minimal strength of newly set passwords:', 'Settings Admin Menu', 'vca-asm' ) . '</p>';
	}
	public function pass_reset_cycle_section() {
		echo '<p>' . _x( 'After this period, users are prompted to reset their password to a new one:', 'Settings Admin Menu', 'vca-asm' ) . '</p>';
	}
	public function automatic_logout_section() {
		echo '<p>' . _x( 'After being idle for this period, users are automatically logged out of the Pool:', 'Settings Admin Menu', 'vca-asm' ) . '</p>';
	}
	public function email_format_section() {
		echo '<p>' . _x( 'For each type of Email or Sender, set the format of the outgoing Email.', 'Settings Admin Menu', 'vca-asm' ) . '</p>';
	}
	public function security_options_fields( $args ) {
		$output = '<div id="' . $args['id'] . '-slider"></div>' .
			'<input type="text" class="js-hide" id="' . $args['id'] . '" ' .
				'name="vca_asm_security_options[' . $args['id'] . ']" value="' .
					$args['value'] .
				'" />' .
			'&nbsp;&nbsp;&nbsp;';
		if( $args['callback'] === 'number' ) {
			if( $args['value'] == 0 ) {
				$output .= '<span id="' . $args['id'] . '-slider_result">' . $args['never'] . '</span>';
			} else {
				$output .= '<span id="' . $args['id'] . '-slider_result">' . $args['value'] . ' ' . $args['append'] . '</span>';
			}
		} else {
			$output .= $args['append'];
		}
		echo $output;
	}
	public function global_reset_field( $args ) {
		$output = '<input type="checkbox" value="' . time() .
				'" onclick="' .
					'if ( confirm(\'' .
							__( 'Really force a global pass reset?', 'vca-asm' ) .
						'\') ) { return true; } return false;' .
				'" id="' . $args['id']  . '" name="vca_asm_security_options[' . $args['id'] . ']" />' .
		'<br />' . _x( 'When activating this, all users, whose settings above are not set to &quot;never&quot;, will be prompted to reset their password on their next login, regardless of how old their passwords are.', 'Settings Admin Menu', 'vca-asm' );
		if( ! empty( $this->security_options_values['global_pass_reset'] ) ) {
			$output .= '<br />' . _x( 'Last global reset', 'Settings Admin Menu', 'vca-asm' ) . ': ' . strftime( '%e. %B %G', $this->security_options_values['global_pass_reset'] );
		}
		echo $output;
	}
	public function email_format_fields( $args ) {
		$output = '<input type="radio" id="' . $args['id']  . '_html" name="vca_asm_emails_options[' . $args['id'] . ']" value="html"';
		if ( $args['value'] === 'html' ) {
			$output .= ' checked="checked"';
		}
		$output .= ' />' .
			'<label for="' . $args['id']  . '_html">' . _x( 'Rich Text (HTML)', 'Settings Admin Menu', 'vca-asm' ) . '</label>' .
			'<br />' .
			'<input type="radio" id="' . $args['id']  . '_plain" name="vca_asm_emails_options[' . $args['id'] . ']" value="plain"';
		if ( $args['value'] === 'plain' ) {
			$output .= ' checked="checked"';
		}
		$output .= ' />' .
			'<label for="' . $args['id']  . '_plain">' . _x( 'Plain Text', 'Settings Admin Menu', 'vca-asm' ) . '</label>';
		echo $output;
	}

	/******************** CONSTRUCTORS ********************/

	/**
	 * PHP4 style constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function VCA_ASM_Settings() {
		$this->__construct();
	}

	/**
	 * PHP5 style constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
		$this->init();
		add_action( 'admin_init', array( &$this, 'initialize_options' ) );
	}

} // class

endif; // class exists

?>