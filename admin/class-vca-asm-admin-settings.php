<?php

/**
 * VCA_ASM_Admin_Settings class.
 *
 * This class contains properties and methods for
 * the setting of system-wide options
 * in the administrative backend
 *
 * @todo Bring up to current template standard
 * @todo Way too much markup!
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
	 * @access private
	 */
	private $security_options_values = array();
	private $security_options = array();
	private $emails_options_values = array();
	private $emails_sending_options = array();
	private $mode_options_values = array();
	private $has_cap = false;

	private $admin_nation = 0;

	/**
	 * Assigns values to class properties
	 *
	 * @since 1.2
	 * @access private
	 */
	private function init() {

		$this->security_options_values = get_option( 'vca_asm_security_options' );
		$this->emails_options_values = get_option( 'vca_asm_emails_options' );
		$this->mode_options_values = get_option( 'vca_asm_mode_options' );

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
		$this->emails_sending_options = array(
			0 => array(
				'id' => 'email_sending_packet_size',
				'min' => 50,
				'max' => 500,
				'step' => 50,
				'value' => ! empty( $this->emails_options_values['email_sending_packet_size'] ) ? $this->emails_options_values['email_sending_packet_size'] : 100,
				'callback' => 'number',
				'classes' => '',
				'content' => '',
				'append' => ' ' . __( 'E-Mails', 'vca-asm' ),
				'never' => ' ' . __( 'never', 'vca-asm' )
			),
			1 => array(
				'id' => 'email_sending_interval',
				'min' => 2,
				'max' => 30,
				'step' => 2,
				'value' => ! empty( $this->emails_options_values['email_sending_interval'] ) ? $this->emails_options_values['email_sending_interval'] : 5,
				'callback' => 'number',
				'classes' => '',
				'content' => '',
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

		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'emails';

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
	 * Controller for the Maintenance Mode Admin Menu
	 *
	 * @since 1.3
	 * @access public
	 */
	public function mode_control() {
        /** @var vca_asm_utilities $vca_asm_utilities */
		global $vca_asm_utilities;

		$page = new VCA_ASM_Admin_Page( array(
			'echo' => true,
			'icon' => 'icon-settings',
			'title' => __( 'Settings', 'vca-asm' ) . ': ' . __( 'Maintenance Mode', 'vca-asm' ),
			'url' => '?page=admin.php',
			'messages' => array()
		));

		$page->top();

		$mb_env = new VCA_ASM_Admin_Metaboxes( 'echo=true' );

		echo '<form method="post" action="options.php">';
		$mb_env->top();

		if ( $this->has_cap ) {
			submit_button();
		}
		settings_fields( 'vca_asm_mode_options' );
		$vca_asm_utilities->do_settings_sections( 'vca_asm_mode_options' );
		if ( $this->has_cap ) {
			submit_button();
		}

		$mb_env->bottom();
		echo '</form>';

		$page->bottom();
	}

	/**
	 * Outputs form to edit autoresponse texts and saves them to the database
	 *
	 * @since 1.0
	 * @access public
	 */
	public function autoresponses_edit() {
		global $current_user, $wpdb;

		$scope = isset( $_POST['scope'] ) ? $_POST['scope'] : ( isset( $_GET['scope'] ) ? $_GET['scope'] : ( in_array( 'goldeimer_global', $current_user->roles ) ? 'ge' : $this->admin_nation ) );

		$url = 'admin.php?page=vca-asm-settings&tab=responses';
		$form_action = $url . '&todo=save' . '&scope=' . $scope;
		$output = '';

		$fields = array(
			array(
				'title' => _x( 'Application Confirmation', 'Admin Email Interface', 'vca-asm' ),
				'fields' => array(
					array(
						'type' => 'checkbox',
						'label' => _x( 'Send Mail Switch', 'Admin Email Interface', 'vca-asm' ),
						'id' => 'applied-switch',
						'desc' => _x( 'Enable/disable application confirmations', 'Admin Email Interface', 'vca-asm' ) . '<br />' . _x( '(this goes for the default responses as well)', 'Admin Email Interface', 'vca-asm' )
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
					)
				)
			),
			array(
				'title' => _x( 'Registration Confirmation / Application Acceptance', 'Admin Email Interface', 'vca-asm' ),
				'fields' => array(
					array(
						'type' => 'checkbox',
						'label' => _x( 'Send Mail Switch', 'Admin Email Interface', 'vca-asm' ),
						'id' => 'accepted-switch',
						'desc' => _x( 'Enable/disable registration confirmations', 'Admin Email Interface', 'vca-asm' ) . '<br />' . _x( '(this goes for the default responses as well)', 'Admin Email Interface', 'vca-asm' )
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
					)
				)
			),
			array(
				'title' => _x( 'Deny Application', 'Admin Email Interface', 'vca-asm' ),
				'fields' => array(
					array(
						'type' => 'checkbox',
						'label' => _x( 'Send Mail Switch', 'Admin Email Interface', 'vca-asm' ),
						'id' => 'denied-switch',
						'desc' => _x( 'Enable/disable application denial notifications', 'Admin Email Interface', 'vca-asm' ) . '<br />' . _x( '(this goes for the default responses as well)', 'Admin Email Interface', 'vca-asm' )
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
					)
				)
			),
			array(
				'title' => _x( 'Withdraw Registration', 'Admin Email Interface', 'vca-asm' ),
				'fields' => array(
					array(
						'type' => 'checkbox',
						'label' => _x( 'Send Mail Switch', 'Admin Email Interface', 'vca-asm' ),
						'id' => 'reg_revoked-switch',
						'desc' => _x( 'Enable/disable notifications of revoked registrations', 'Admin Email Interface', 'vca-asm' ) . '<br />' . _x( '(this goes for the default responses as well)', 'Admin Email Interface', 'vca-asm' )
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
					)
				)
			)
		);
		if ( 'ge' !== $scope ) {
			$fields[] = array(
				'title' =>  _x( 'Accept Membership', 'Admin Email Interface', 'vca-asm' ),
				'fields' => array(
					array(
						'type' => 'checkbox',
						'label' => _x( 'Send Mail Switch', 'Admin Email Interface', 'vca-asm' ),
						'id' => 'mem_accepted-switch',
						'desc' => _x( 'Enable/disable notifications of accepted memberships to Crew', 'Admin Email Interface', 'vca-asm' ) . '<br />' . _x( '(this goes for the default responses as well)', 'Admin Email Interface', 'vca-asm' )
					),
					array(
						'type' => 'text',
						'label' => _x( 'Subject', 'Admin Email Interface', 'vca-asm' ),
						'id' => 'mem_accepted-subject',
						'desc' => _x( 'Subject line for notifications of accepted memberships to Crew', 'Admin Email Interface', 'vca-asm' )
					),
					array(
						'type' => 'textarea',
						'label' => _x( 'Message', 'Admin Email Interface', 'vca-asm' ),
						'id' => 'mem_accepted-message',
						'desc' => _x( 'Message body for notifications of accepted memberships to Crew', 'Admin Email Interface', 'vca-asm' )
					)
				)
			);
			$fields[] = array(
				'title' => _x( 'Deny Membership', 'Admin Email Interface', 'vca-asm' ),
				'fields' => array(
					array(
						'type' => 'checkbox',
						'label' => _x( 'Send Mail Switch', 'Admin Email Interface', 'vca-asm' ),
						'id' => 'mem_denied-switch',
						'desc' => _x( 'Enable/disable notifications of denied memberships to Crew', 'Admin Email Interface', 'vca-asm' ) . '<br />' . _x( '(this goes for the default responses as well)', 'Admin Email Interface', 'vca-asm' )
					),
					array(
						'type' => 'text',
						'label' => _x( 'Subject', 'Admin Email Interface', 'vca-asm' ),
						'id' => 'mem_denied-subject',
						'desc' => _x( 'Subject line for notifications of denied memberships to Crew', 'Admin Email Interface', 'vca-asm' )
					),
					array(
						'type' => 'textarea',
						'label' => _x( 'Message', 'Admin Email Interface', 'vca-asm' ),
						'id' => 'mem_denied-message',
						'desc' => _x( 'Message body for notifications of denied memberships to Crew', 'Admin Email Interface', 'vca-asm' )
					)
				)
			);
			$fields[] = array(
				'title' => _x( 'Cancel Membership', 'Admin Email Interface', 'vca-asm' ),
				'fields' => array(
					array(
						'type' => 'checkbox',
						'label' => _x( 'Send Mail Switch', 'Admin Email Interface', 'vca-asm' ),
						'id' => 'mem_cancelled-switch',
						'desc' => _x( 'Enable/disable notifications when memberships to Crew are cancelled', 'Admin Email Interface', 'vca-asm' ) . '<br />' . _x( '(this goes for the default responses as well)', 'Admin Email Interface', 'vca-asm' )
					),
					array(
						'type' => 'text',
						'label' => _x( 'Subject', 'Admin Email Interface', 'vca-asm' ),
						'id' => 'mem_cancelled-subject',
						'desc' => _x( 'Subject line for notifications when memberships to Crew are cancelled', 'Admin Email Interface', 'vca-asm' )
					),
					array(
						'type' => 'textarea',
						'label' => _x( 'Message', 'Admin Email Interface', 'vca-asm' ),
						'id' => 'mem_cancelled-message',
						'desc' => _x( 'Message body for notifications when memberships to Crew are cancelled', 'Admin Email Interface', 'vca-asm' )
					)
				)
			);
		}

		if( isset( $_GET['todo'] ) && $_GET['todo'] == 'save' ) {
			$output .= '<div class="message"><p>' .
				__( 'Options successfully updated!', 'vca-asm' ) .
				'</p></div>';
		}

		$output .= $this->scope_selector( 'responses', true );

		$output .= '<div id="poststuff"><div id="post-body" class="metabox-holder columns-1"><div id="postbox-container-99" class="postbox-container"><div class="postbox ">' .
			'<h3 class="no-hover"><span>' . __( 'Help', 'vca-asm' ) . '</span></h3>' .
			'<div class="inside">' .
				'<p>' .
					_x( 'Here you can enable or disable, as well as overwrite the default autoresponses sent when a supporter completes a certain action.', 'Admin Email Autoresponse Options', 'vca-asm' ) .
					'</p><p>' .
					_x( 'You can use the following placeholders (if applicable)', 'Admin Email Autoresponse Options', 'vca-asm' ) .
					' :</p><p>' .
					'%event% - ' . _x( 'The title of the event in question', 'Admin Email Autoresponse Options', 'vca-asm' ) . '<br />' .
					'%region% - ' . _x( 'The name of the region in question', 'Admin Email Autoresponse Options', 'vca-asm' ) . '<br />' .
					'%name% - ' . _x( 'The name of the supporter', 'Admin Email Autoresponse Options', 'vca-asm' ) .
					'</p>' .
			'</div></div></div></div></div>';

		/* populate fields */
		$bcount = count($fields);
		for ( $i = 0; $i < $bcount; $i++ ) {
			$fcount = count($fields[$i]['fields']);
			for ( $j = 0; $j < $fcount; $j++ ) {
				if ( ! $this->has_cap ) {
					$fields[$i]['fields'][$j]['disabled'] = true;
				}
				$id = explode( '-', $fields[$i]['fields'][$j]['id'] );
				$action = $id[0];
				$column = $id[1];
				if( ! isset( $_POST['submitted'] ) ) {
					$data = $wpdb->get_results(
						"SELECT " . $column . " FROM " .
						$wpdb->prefix . "vca_asm_auto_responses " .
						"WHERE action = '" . $action . "' AND scope = '" . $scope . "' LIMIT 1", ARRAY_A
					);
					$fields[$i]['fields'][$j]['value'] = stripslashes( $data[0][$column] );
				} elseif( $fields[$i]['fields'][$j]['type'] === 'checkbox' ) {
					if( isset( $_POST[$fields[$i]['fields'][$j]['id']] ) ) {
						$fields[$i]['fields'][$j]['value'] = 1;
					} else {
						$fields[$i]['fields'][$j]['value'] = 0;
					}
				} else {
					$fields[$i]['fields'][$j]['value'] = $_POST[$fields[$i]['fields'][$j]['id']];
				}

				/* save */
				if( isset( $_GET['todo'] ) && $_GET['todo'] === 'save' ) {
					if( $fields[$i]['fields'][$j]['type'] !== 'checkbox'  ) {
						$wpdb->update(
							$wpdb->prefix . 'vca_asm_auto_responses',
							array(
								$column => $_POST[$fields[$i]['fields'][$j]['id']]
							),
							array( 'action'=> $action, 'scope'=> $scope ),
							array( '%s' ),
							array( '%s', '%s' )
						);
					} elseif ( $fields[$i]['fields'][$j]['type'] === 'checkbox' && isset( $_POST[$fields[$i]['fields'][$j]['id']] ) ) {
						$wpdb->update(
							$wpdb->prefix . 'vca_asm_auto_responses',
							array(
								$column => 1
							),
							array( 'action'=> $action, 'scope'=> $scope ),
							array( '%d' ),
							array( '%s', '%s' )
						);
					} elseif ( $fields[$i]['fields'][$j]['type'] === 'checkbox' ) {
						$wpdb->update(
							$wpdb->prefix . 'vca_asm_auto_responses',
							array(
								$column => 0
							),
							array( 'action'=> $action, 'scope'=> $scope ),
							array( '%d' ),
							array( '%s', '%s' )
						);
					}
				}
			}
		}
		$args = array(
			'echo' => false,
			'form' => true,
			'metaboxes' => true,
			'action' => $form_action,
			'fields' => $fields,
			'has_cap' => $this->has_cap
		);
		$form = new VCA_ASM_Admin_Form( $args );
		$output .= $form->output();

		echo $output;
	}

	/**
	 * Output of the Security Settings Admin Menu
	 *
	 * @since 1.2
	 * @access private
	 */
	private function security_menu() {
        /** @var vca_asm_utilities $vca_asm_utilities */
		global $vca_asm_utilities;

		wp_enqueue_script( 'vca-asm-admin-settings' );
		wp_localize_script( 'vca-asm-admin-settings', 'settingsOptions', $this->security_options );
		$bool = 0;
		if ( $this->has_cap ) {
			$bool = 1;
		}
		wp_localize_script( 'vca-asm-admin-settings', 'hasCap', array( 'bool' => $bool ) );
		$mb_env = new VCA_ASM_Admin_Metaboxes( 'echo=true' );

		echo '<form method="post" action="options.php">';
		$mb_env->top();

		if ( $this->has_cap ) {
			submit_button();
		}
		settings_fields( 'vca_asm_security_options' );
		$vca_asm_utilities->do_settings_sections( 'vca_asm_security_options' );
		if ( $this->has_cap ) {
			submit_button();
		}

		$mb_env->bottom();
		echo '</form>';
	}

	/**
	 * Output of the Emails Settings Admin Menu
	 *
	 * @since 1.2
	 * @access private
	 */
	private function emails_menu() {
        /** @var vca_asm_utilities $vca_asm_utilities */
		global $vca_asm_utilities;

		wp_enqueue_script( 'vca-asm-admin-settings' );
		wp_localize_script( 'vca-asm-admin-settings', 'settingsOptions', $this->emails_sending_options );
		$bool = 0;
		if ( $this->has_cap ) {
			$bool = 1;
		}
		wp_localize_script( 'vca-asm-admin-settings', 'hasCap', array( 'bool' => $bool ) );

		$mb_env = new VCA_ASM_Admin_Metaboxes( 'echo=true' );

		echo '<form method="post" action="options.php">';
		$mb_env->top();

		if ( $this->has_cap ) {
			submit_button();
		}
		settings_fields( 'vca_asm_emails_options' );
		$vca_asm_utilities->do_settings_sections( 'vca_asm_emails_options' );
		if ( $this->has_cap ) {
			submit_button();
		}

		$mb_env->bottom();
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
		
		$current_user = wp_get_current_user();

		/* check capabilities */
		if ( $current_user->has_cap( 'vca_asm_set_mode' ) && isset( $_GET['page'] ) && 'vca-asm-mode-settings' === $_GET['page'] ) {
			$this->has_cap = true;
		} elseif ( $current_user->has_cap( 'vca_asm_manage_options' ) && isset( $_GET['page'] ) && 'vca-asm-settings' === $_GET['page'] ) {
			$this->has_cap = true;
		}

		$this->init();

		if( false == get_option( 'vca_asm_security_options' ) ) {
			add_option( 'vca_asm_security_options' );
		}
		if( false == get_option( 'vca_asm_mode_options' ) ) {
			add_option( 'vca_asm_mode_options' );
		}
		if( false == get_option( 'vca_asm_emails_options' ) ) {
			add_option( 'vca_asm_emails_options' );
		}
		add_settings_section(
			'pass_strength',
			_x( '(Minimum) Password Strength', 'Settings Admin Menu', 'vca-asm' ),
			array( $this, 'pass_strength_section' ),
			'vca_asm_security_options'
		);
		add_settings_section(
			'pass_reset_cycle',
			_x( 'Password Reset Cycle', 'Settings Admin Menu', 'vca-asm' ),
			array( $this, 'pass_reset_cycle_section' ),
			'vca_asm_security_options'
		);
		add_settings_section(
			'automatic_logout',
			_x( 'Automatic Logout', 'Settings Admin Menu', 'vca-asm' ),
			array( $this, 'automatic_logout_section' ),
			'vca_asm_security_options'
		);
		add_settings_section(
			'email_restrictions',
			_x( 'User Restrictions', 'Settings Admin Menu', 'vca-asm' ),
			array( $this, 'email_restrictions_section' ),
			'vca_asm_emails_options'
		);
		add_settings_section(
			'email_format',
			_x( 'Email Format', 'Settings Admin Menu', 'vca-asm' ),
			array( $this, 'email_format_section' ),
			'vca_asm_emails_options'
		);
		add_settings_section(
			'email_sending',
			_x( 'Sending Options', 'Settings Admin Menu', 'vca-asm' ),
			array( $this, 'email_sending_section' ),
			'vca_asm_emails_options'
		);
		add_settings_section(
			'email_protocol',
			_x( 'Server / Protocol', 'Settings Admin Menu', 'vca-asm' ),
			array( $this, 'email_protocol_section' ),
			'vca_asm_emails_options'
		);
		add_settings_section(
			'mode',
			__( 'Maintenance Mode', 'vca-asm' ),
			array( $this, 'mode_section' ),
			'vca_asm_mode_options'
		);

		foreach( $this->security_options as $option ) {
			add_settings_field(
				$option['id'],
				$option['title'],
				array( $this, 'security_options_fields' ),
				'vca_asm_security_options',
				$option['section'],
				$option
			);
		}

		add_settings_field(
			'global_pass_reset',
			_x( 'Global Reset', 'Settings Admin Menu', 'vca-asm' ),
			array( $this, 'global_reset_field' ),
			'vca_asm_security_options',
			'pass_reset_cycle',
			array( 'id' => 'global_pass_reset' )
		);
		add_settings_field(
			'email_restrictions_city',
			_x( 'Waiting Period', 'Settings Admin Menu', 'vca-asm' ),
			array( $this, 'email_restrictions_fields' ),
			'vca_asm_emails_options',
			'email_restrictions',
			array( 'id' => 'email_restrictions_city', 'value' =>  ! empty( $this->emails_options_values['email_restrictions_city'] ) ? $this->emails_options_values['email_restrictions_city'] : 144 )
		);
		add_settings_field(
			'email_format_admin',
			_x( 'Office / Administrators', 'Settings Admin Menu', 'vca-asm' ),
			array( $this, 'email_format_fields' ),
			'vca_asm_emails_options',
			'email_format',
			array( 'id' => 'email_format_admin', 'value' =>  ! empty( $this->emails_options_values['email_format_admin'] ) ? $this->emails_options_values['email_format_admin'] : 'html' )
		);
		add_settings_field(
			'email_format_ho',
			_x( 'City User', 'Settings Admin Menu', 'vca-asm' ),
			array( $this, 'email_format_fields' ),
			'vca_asm_emails_options',
			'email_format',
			array( 'id' => 'email_format_ho', 'value' =>  ! empty( $this->emails_options_values['email_format_ho'] ) ? $this->emails_options_values['email_format_ho'] : 'plain' )
		);
		add_settings_field(
			'email_format_auto',
			_x( 'Automatic Responses', 'Settings Admin Menu', 'vca-asm' ),
			array( $this, 'email_format_fields' ),
			'vca_asm_emails_options',
			'email_format',
			array( 'id' => 'email_format_auto', 'value' =>  ! empty( $this->emails_options_values['email_format_auto'] ) ? $this->emails_options_values['email_format_auto'] : 'plain' )
		);
		add_settings_field(
			'email_sending_packet_switch',
			_x( 'Send in packets?', 'Settings Admin Menu', 'vca-asm' ),
			array( $this, 'email_sending_packet_switch_field' ),
			'vca_asm_emails_options',
			'email_sending',
			array( 'id' => 'email_sending_packet_switch', 'value' =>  ! empty( $this->emails_options_values['email_sending_packet_switch'] ) ? $this->emails_options_values['email_sending_packet_switch'] : 1 )
		);
		add_settings_field(
			'email_sending_packet_size',
			_x( 'Packet size', 'Settings Admin Menu', 'vca-asm' ),
			array( $this, 'slider_fields' ),
			'vca_asm_emails_options',
			'email_sending',
			$this->emails_sending_options[0]
		);
		add_settings_field(
			'email_sending_interval',
			_x( 'Interval', 'Settings Admin Menu', 'vca-asm' ),
			array( $this, 'slider_fields' ),
			'vca_asm_emails_options',
			'email_sending',
			$this->emails_sending_options[1]
		);
		add_settings_field(
			'email_protocol_type',
			_x( 'How?', 'Settings Admin Menu', 'vca-asm' ),
			array( $this, 'email_protocol_type_field' ),
			'vca_asm_emails_options',
			'email_protocol',
			array( 'id' => 'email_protocol_type', 'value' =>  ! empty( $this->emails_options_values['email_protocol_type'] ) ? $this->emails_options_values['email_protocol_type'] : 'sendmail' )
		);
		add_settings_field(
			'email_protocol_url',
			_x( 'Server URL', 'Settings Admin Menu', 'vca-asm' ),
			array( $this, 'email_protocol_text_fields' ),
			'vca_asm_emails_options',
			'email_protocol',
			array( 'id' => 'email_protocol_url', 'value' =>  ! empty( $this->emails_options_values['email_protocol_url'] ) ? $this->emails_options_values['email_protocol_url'] : '' )
		);
		add_settings_field(
			'email_protocol_port',
			_x( 'Port', 'Settings Admin Menu', 'vca-asm' ),
			array( $this, 'email_protocol_text_fields' ),
			'vca_asm_emails_options',
			'email_protocol',
			array( 'id' => 'email_protocol_port', 'value' =>  ! empty( $this->emails_options_values['email_protocol_port'] ) ? $this->emails_options_values['email_protocol_port'] : 25 )
		);
		add_settings_field(
			'email_protocol_username',
			_x( 'Username', 'Settings Admin Menu', 'vca-asm' ),
			array( $this, 'email_protocol_text_fields' ),
			'vca_asm_emails_options',
			'email_protocol',
			array( 'id' => 'email_protocol_username', 'value' =>  ! empty( $this->emails_options_values['email_protocol_username'] ) ? $this->emails_options_values['email_protocol_username'] : '' )
		);
		add_settings_field(
			'email_protocol_pass',
			_x( 'Password', 'Settings Admin Menu', 'vca-asm' ),
			array( $this, 'email_protocol_text_fields' ),
			'vca_asm_emails_options',
			'email_protocol',
			array( 'id' => 'email_protocol_pass', 'value' =>  ! empty( $this->emails_options_values['email_protocol_pass'] ) ? $this->emails_options_values['email_protocol_pass'] : '' )
		);
		add_settings_field(
			'mode',
			__( 'Maintenance Mode', 'vca-asm' ),
			array( $this, 'mode_fields' ),
			'vca_asm_mode_options',
			'mode',
			array( 'id' => 'mode', 'value' =>  ! empty( $this->mode_options_values['mode'] ) ? $this->mode_options_values['mode'] : 'normal' )
		);

		register_setting(
			'vca_asm_security_options',
			'vca_asm_security_options'
		);
		register_setting(
			'vca_asm_emails_options',
			'vca_asm_emails_options'
		);
		register_setting(
			'vca_asm_mode_options',
			'vca_asm_mode_options'
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
	public function secure_login_section() {
		echo '<p>' . _x( 'If this is enabled, users of the type in question will be forced to login via the secure HTTPS protocol.', 'Settings Admin Menu', 'vca-asm' ) . '</p>';
	}
	public function email_restrictions_section() {
		echo '<p>' . _x( 'Choose how long city users have to wait after sending a newsletter before being allowed to write another one.', 'Settings Admin Menu', 'vca-asm' ) . '</p>';
	}
	public function email_format_section() {
		echo '<p>' . _x( 'For each type of Email or Sender, set the format of the outgoing Email.', 'Settings Admin Menu', 'vca-asm' ) . '</p>';
	}
	public function email_sending_section() {
		echo '<p>' . _x( 'Emails can either be sent in bulk, or split in packets in pre-defined intervals.', 'Settings Admin Menu', 'vca-asm' ) . '</p>';
	}
	public function email_protocol_section() {
		echo '<p>' . _x( 'The Emails can be sent via Unix Sendmail or SMTP. In the latter case, server data is required. When using sendmail, the server fields maybe left blank.', 'Settings Admin Menu', 'vca-asm' ) . '</p>';
	}
	public function mode_section() {
		echo '<p>' . _x( 'The Pool can be put into Maintenance Mode. If in this mode, non-management users will be prohibited to login. Activity registrations are not possible.', 'Settings Admin Menu', 'vca-asm' ) . '</p>';
	}

	public function security_options_fields( $args ) {
		$output = '<div id="' . $args['id'] . '-slider"></div>' .
			'<input type="text" ';
		if ( ! $this->has_cap ) {
			$output .= 'disabled="disabled" ';
		}
		$output .= 'class="js-hide" id="' . $args['id'] . '" ' .
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
		$output = '<input id="global_reset_switch" type="checkbox" ';
		if ( ! $this->has_cap ) {
			$output .= 'disabled="disabled" ';
		}
		$output .= 'value="' . time() .
				'" onclick="' .
					'if ( confirm(\'' .
							__( 'Really force a global pass reset?', 'vca-asm' ) .
						'\') ) { return true; } return false;' .
				'" id="' . $args['id']  . '" name="vca_asm_security_options[' . $args['id'] . ']" />' .
			'<label for="global_reset_switch">' . _x( 'Force reset now', 'Settings Admin Menu', 'vca-asm' ) . '</label>' .
		'<br /><span class="description">' . _x( 'When activating this, all users, whose settings above are not set to &quot;never&quot;, will be prompted to reset their password on their next login, regardless of how old their passwords are.', 'Settings Admin Menu', 'vca-asm' );
		if( ! empty( $this->security_options_values['global_pass_reset'] ) ) {
			$output .= '<br />' . _x( 'Last global reset', 'Settings Admin Menu', 'vca-asm' ) . ': ' . strftime( '%e. %B %G', $this->security_options_values['global_pass_reset'] );
		}
		$output .= '</span>';
		echo $output;
	}
	public function email_restrictions_fields( $args ) {
		$output = '<select id="' . $args['id']  . '" name="vca_asm_emails_options[' . $args['id'] . ']">';

		foreach(
			array(
				1 => _x( '1 Hour', 'Settings Admin Menu', 'vca-asm' ),
				2 => _x( '2 Hours', 'Settings Admin Menu', 'vca-asm' ),
				3 => _x( '3 Hours', 'Settings Admin Menu', 'vca-asm' ),
				6 => _x( '6 Hours', 'Settings Admin Menu', 'vca-asm' ),
				12 => _x( '12 Hours', 'Settings Admin Menu', 'vca-asm' ),
				24 => _x( '1 Day', 'Settings Admin Menu', 'vca-asm' ),
				72 => _x( '3 Days', 'Settings Admin Menu', 'vca-asm' ),
				144 => _x( '6 Days', 'Settings Admin Menu', 'vca-asm' ),
				288 => _x( '12 Days', 'Settings Admin Menu', 'vca-asm' )
			)
			as $value => $string
		) {
			$output .= '<option value="' . $value . '"';

			if ( intval( $args['value'] ) === $value ) {
				$output .= ' selected="selected"';
			}
			if ( ! $this->has_cap ) {
				$output .= ' disabled="disabled" ';
			}

			$output .= '>' .
					$string .
				'</option>';
		}

		$output .= '</select>';
		echo $output;
	}
	public function email_format_fields( $args ) {
		$output = '<input type="radio" id="' . $args['id']  . '_html" name="vca_asm_emails_options[' . $args['id'] . ']" value="html"';
		if ( $args['value'] === 'html' ) {
			$output .= ' checked="checked"';
		}
		if ( ! $this->has_cap ) {
			$output .= ' disabled="disabled" ';
		}
		$output .= ' />' .
			'<label for="' . $args['id']  . '_html">' . _x( 'Rich Text (HTML)', 'Settings Admin Menu', 'vca-asm' ) . '</label>' .
			'<br />' .
			'<input type="radio" id="' . $args['id']  . '_plain" name="vca_asm_emails_options[' . $args['id'] . ']" value="plain"';
		if ( $args['value'] === 'plain' ) {
			$output .= ' checked="checked"';
		}
		if ( ! $this->has_cap ) {
			$output .= 'disabled="disabled" ';
		}
		$output .= ' />' .
			'<label for="' . $args['id']  . '_plain">' . _x( 'Plain Text', 'Settings Admin Menu', 'vca-asm' ) . '</label>';
		echo $output;
	}
	public function email_sending_packet_switch_field( $args ) {
		$output = '<input type="radio" id="' . $args['id']  . '_0" name="vca_asm_emails_options[' . $args['id'] . ']" value="0"';
		if ( $args['value'] == 0 ) {
			$output .= ' checked="checked"';
		}
		if ( ! $this->has_cap ) {
			$output .= ' disabled="disabled" ';
		}
		$output .= ' />' .
			'<label for="' . $args['id']  . '_0">' . __( 'No', 'vca-asm' ) . '</label>' .
			'<br />' .
			'<input type="radio" id="' . $args['id']  . '_1" name="vca_asm_emails_options[' . $args['id'] . ']" value="1"';
		if ( $args['value'] == 1 ) {
			$output .= ' checked="checked"';
		}
		if ( ! $this->has_cap ) {
			$output .= 'disabled="disabled" ';
		}
		$output .= ' />' .
			'<label for="' . $args['id']  . '_1">' . __( 'Yes', 'vca-asm' ) . '</label>';
		echo $output;
	}
	public function slider_fields( $args ) {
		$output = '<div id="' . $args['id'] . '-slider"></div>' .
			'<input type="text" ';
		if ( ! $this->has_cap ) {
			$output .= 'disabled="disabled" ';
		}
		$output .= 'class="js-hide" id="' . $args['id'] . '" ' .
				'name="vca_asm_emails_options[' . $args['id'] . ']" value="' .
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
	public function email_protocol_type_field( $args ) {
		$output = '<input type="radio" id="' . $args['id']  . '_sendmail" name="vca_asm_emails_options[' . $args['id'] . ']" value="sendmail"';
		if ( $args['value'] === 'sendmail' ) {
			$output .= ' checked="checked"';
		}
		if ( ! $this->has_cap ) {
			$output .= ' disabled="disabled" ';
		}
		$output .= ' />' .
			'<label for="' . $args['id']  . '_sendmail">' . _x( 'Unix Sendmail', 'Settings Admin Menu', 'vca-asm' ) . '</label>' .
			'<br />' .
			'<input type="radio" id="' . $args['id']  . '_smtp" name="vca_asm_emails_options[' . $args['id'] . ']" value="smtp"';
		if ( $args['value'] === 'smtp' ) {
			$output .= ' checked="checked"';
		}
		if ( ! $this->has_cap ) {
			$output .= 'disabled="disabled" ';
		}
		$output .= ' />' .
			'<label for="' . $args['id']  . '_smtp">' . _x( 'SMTP', 'Settings Admin Menu', 'vca-asm' ) . '</label>';
		echo $output;
	}
	public function email_protocol_text_fields( $args ) {
		$output = '<input type="text" id="' . $args['id']  . '" name="vca_asm_emails_options[' . $args['id'] . ']" value="' . $args['value'] . '"';
		if ( ! $this->has_cap ) {
			$output .= ' disabled="disabled" ';
		}
		$output .= ' />';
		echo $output;
	}
	public function secure_login_fields( $args ) {
		$output = '<input type="radio" id="' . $args['id']  . '_yes" name="vca_asm_security_options[' . $args['id'] . ']" value="yes"';
		if ( $args['value'] === 'yes' ) {
			$output .= ' checked="checked"';
		}
		if ( ! $this->has_cap ) {
			$output .= ' disabled="disabled" ';
		}
		$output .= ' />' .
			'<label for="' . $args['id']  . '_yes">' . _x( 'Login only via SSL/HTTPS', 'Settings Admin Menu', 'vca-asm' ) . '</label>' .
			'<br />' .
			'<input type="radio" id="' . $args['id']  . '_no" name="vca_asm_security_options[' . $args['id'] . ']" value="no"';
		if ( $args['value'] === 'no' ) {
			$output .= ' checked="checked"';
		}
		if ( ! $this->has_cap ) {
			$output .= ' disabled="disabled" ';
		}
		$output .= ' />' .
			'<label for="' . $args['id']  . '_no">' . _x( 'normal login', 'Settings Admin Menu', 'vca-asm' ) . '</label>';
		echo $output;
	}
	public function mode_fields( $args ) {
		$output = '<input type="radio" id="' . $args['id']  . '_normal" name="vca_asm_mode_options[' . $args['id'] . ']" value="normal"';
		if ( $args['value'] === 'normal' ) {
			$output .= ' checked="checked"';
		}
		if ( ! $this->has_cap ) {
			$output .= ' disabled="disabled" ';
		}
		$output .= ' />' .
			'<label for="' . $args['id']  . '_normal">' . _x( 'off', 'Settings Admin Menu', 'vca-asm' ) . '</label>' .
			'<br />' .
			'<input type="radio" id="' . $args['id']  . '_maintenance" name="vca_asm_mode_options[' . $args['id'] . ']" value="maintenance"';
		if ( $args['value'] === 'maintenance' ) {
			$output .= ' checked="checked"';
		}
		if ( ! $this->has_cap ) {
			$output .= ' disabled="disabled"';
		}
		$output .= ' />' .
			'<label for="' . $args['id']  . '_maintenance">' . _x( 'on', 'Settings Admin Menu', 'vca-asm' ) . '</label>';
		echo $output;
	}

	/******************** CONSTRUCTOR ********************/

	/**
	 * Constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
		global $current_user;

		$this->admin_nation = get_user_meta( $current_user->ID, 'nation', true );

		$this->init();
		add_action( 'admin_init', array( $this, 'initialize_options' ) );
	}

	/******************** UTILITY METHODS ********************/

    /**
     * Scope Selector
     * ( Countries + Goldeimer )
     *
     * @since 1.5
     * @access public
     * @param $tab
     * @param bool $include_goldeimer
     * @return string
     */
	private function scope_selector( $tab, $include_goldeimer = true )
	{
        /** @var vca_asm_geography $vca_asm_geography */
		global $current_user, $vca_asm_geography;

		$options = $vca_asm_geography->options_array( array( 'type' => 'nation' ));

		$scope = isset( $_POST['scope'] ) ? $_POST['scope'] : ( isset( $_GET['scope'] ) ? $_GET['scope'] : ( in_array( 'goldeimer_global', $current_user->roles ) ? 'ge' : $this->admin_nation ) );

		$nice_scope = 'ge' === $scope ? __( 'Goldeimer', 'vca-asm' ) : $vca_asm_geography->get_name( $scope );

		if ( $include_goldeimer ) {
			$new_options = array();
			$i = 0;
			foreach ( $options as $option ) {
				$new_options[$i] = $option;
				$new_options[$i]['label'] = 'VcA ' . $option['label'];
				$i++;
			}
			$options = $new_options;
			$options[] = array(
				'label' => __( 'Goldeimer', 'vca-asm' ),
				'value' => 'ge'
			);
		}

		$metabox_environment = new VCA_ASM_Admin_Metaboxes( array(
			'echo' => false,
			'columns' => 1,
			'running' => 113,
			'id' => 'vca-asm-settings-scope-selector-box',
			'title' => __( 'Context', 'vca-asm' ),
			'js' => false
		));

		$form = new VCA_ASM_Admin_Form( array(
			'echo' => false,
			'form' => true,
			'name' => 'vca-asm-settings-scope-selector',
			'method' => 'post',
			'metaboxes' => false,
			'js' => false,
			'url' => '?page=vca-asm-finances-settings&tab=' . $tab,
			'action' => '?page=vca-asm-settings&tab=' . $tab,
			'button' => __( 'Switch Context', 'vca-asm' ),
			'button_id' => 'submit',
			'top_button' => false,
			'submitted_field' => false,
			'has_cap' => true,
			'fields' => array(
				array(
					'type' => 'select',
					'id' => 'scope',
					'options' => $options,
					'value' => $scope,
					'label' => __( 'New context', 'vca-asm' ),
					'desc' => __( 'The context in which you want to edit this setting', 'vca-asm' )
				),
				array(
					'type' => 'note',
					'id' => 'current_context',
					'value' => $nice_scope,
					'label' => __( 'Currently active context', 'vca-asm' ),
					'desc' => __( 'The context in which the currently shown options are used', 'vca-asm' )
				)
			)
		));

		$output = $metabox_environment->top();
		$output .= $metabox_environment->mb_top();

		$output .= $form->output();

		$output .= $metabox_environment->mb_bottom();
		$output .= $metabox_environment->bottom();

		return $output;
	}

    /**
     * Inserts a new set of autoresponses
     *
     * @since 1.5
     * @access public
     * @param $scope
     * @param bool $preset
     */
	public function insert_autoresponses( $scope, $preset = false )
	{
		global $wpdb;

		$actions = array(
			'applied',
			'accepted',
			'denied',
			'reg_revoked',
			'mem_accepted',
			'mem_denied',
			'mem_cancelled'
		);
		foreach( $actions as $action ) {
			$subject = '';
			$message = '';
			if ( ! empty( $preset ) ) {
				$preset_query = $wpdb->get_results(
					"SELECT subject, message FROM " .
					$wpdb->prefix . "vca_asm_auto_responses " .
					"WHERE scope = '" . $preset . "' LIMIT 1", ARRAY_A
				);
				$subject = isset( $preset_query[0] ) ? $preset_query[0]['subject'] : $subject;
				$message = isset( $preset_query[0] ) ? $preset_query[0]['message'] : $message;
			}
			$wpdb->insert(
				$wpdb->prefix . 'vca_asm_auto_responses',
				array(
					'action' => $action,
					'scope' => $scope,
					'switch' => 1,
					'subject' => stripcslashes( $subject ),
					'message' => stripcslashes( $message )
				),
				array( '%s', '%s', '%d', '%s', '%s' )
			);
		}
	}

    /**
     * Deletes a set of autoresponses
     *
     * @since 1.5
     * @access public
     * @param string $scope
     */
	public function delete_autoresponses( $scope = 'dummy' )
	{
		global $wpdb;

		$wpdb->query(
			"DELETE FROM " .
			$wpdb->prefix . "vca_asm_auto_responses " .
			"WHERE scope = '" . $scope . "'"
		);
	}

} // class

endif; // class exists

?>