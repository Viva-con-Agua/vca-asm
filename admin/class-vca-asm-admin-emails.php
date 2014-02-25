<?php

/**
 * VCA_ASM_Admin_Emails class.
 *
 * This class contains properties and methods for
 * the email/newsletter interface in the administration backend.
 *
 * Attention: It does not actually handle the sending of emails.
 * @see class VCA_ASM_Mailer for that,
 * contained in /includes/vca-asm-mailer.php
 *
 * @since 1.0
 */

if ( ! class_exists( 'VCA_ASM_Admin_Emails' ) ) :

class VCA_ASM_Admin_Emails {

	/**
	 * Outputs form to send mails
	 *
	 * @since 1.0
	 * @access public
	 */
	public function mail_form() {
		global $wpdb, $current_user, $vca_asm_regions;
		get_currentuserinfo();
		$admin_region = get_user_meta( $current_user->ID, 'region', true );

		/* form parameters */
		$url = "admin.php?page=vca-asm-emails";
		$form_action = $url . "&amp;todo=send";

		if( isset( $_GET['email'] ) ) {
			$user_obj = get_user_by( 'email', $_GET['email'] );
			$name = $user_obj->first_name . ' ' . $user_obj->last_name;
			$receipient_field = array(
				'type' => 'hidden',
				'label' => _x( 'Receipient', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'receipient',
				'value' => 'single'.$_GET['email'],
				'desc' => sprintf( _x( 'You are writing to a single supporter: %s.', 'Admin Email Interface', 'vca-asm' ), $name )
			);
		} elseif( isset( $_GET['group'] ) && ( $_GET['group'] == 'participants' || $_GET['group'] == 'applicants' || $_GET['group'] == 'applicants_global' || $_GET['group'] == 'waiting' ) && isset( $_GET['activity'] ) ) {
			$name = get_the_title($_GET['activity']);
			$receipient_field = array(
				'type' => 'hidden',
				'label' => _x( 'Receipient', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'receipient',
				'value' => 'act'.$_GET['activity'],
				'desc' => sprintf( _x( 'You are writing to supporters with accepted applications to %s.', 'Admin Email Interface', 'vca-asm' ), $name )
			);
			if( $_GET['group'] == 'applicants' ) {
				$receipient_field['desc'] = sprintf( _x( 'You are writing to supporters currently applying to %s.', 'Admin Email Interface', 'vca-asm' ), $name );
			}
			if( $_GET['group'] == 'applicants_global' ) {
				$receipient_field['desc'] = sprintf( _x( 'You are writing to supporters currently applying to %s via the global contingent.', 'Admin Email Interface', 'vca-asm' ), $name );
			}
			if( $_GET['group'] == 'waiting' ) {
				$receipient_field['desc'] = sprintf( _x( 'You are writing to supporters currently on the waiting list for %s.', 'Admin Email Interface', 'vca-asm' ), $name );
			}
			$participants = array();
			get_currentuserinfo();
			$testmail = $current_user->user_email;
			$participants[0] = array(
				'label' => __( 'Testmail to yourself', 'vca-asm' ),
				'value' => $testmail
			);
			if( $_GET['group'] == 'participants' ) {
				$supporters = $wpdb->get_results(
					"SELECT supporter FROM " .
					$wpdb->prefix . "vca_asm_registrations " .
					"WHERE activity = " . $_GET['activity'], ARRAY_A
				);
			} elseif( $_GET['group'] == 'applicants' ) {
				$supporters = $wpdb->get_results(
					"SELECT supporter FROM " .
					$wpdb->prefix . "vca_asm_applications " .
					"WHERE activity = " . $_GET['activity'] . " AND state = 0", ARRAY_A
				);
			} elseif( $_GET['group'] == 'applicants_global' ) {
				$supporters = $wpdb->get_results(
					"SELECT supporter FROM " .
					$wpdb->prefix . "vca_asm_applications " .
					"WHERE activity = " . $_GET['activity'] . " AND state = 0", ARRAY_A
				);
			} elseif( $_GET['group'] == 'waiting' ) {
				$supporters = $wpdb->get_results(
					"SELECT supporter FROM " .
					$wpdb->prefix . "vca_asm_applications " .
					"WHERE activity = " . $_GET['activity'] . " AND state = 1", ARRAY_A
				);
			}
			foreach( $supporters as $supporter ) {
				$supp_obj = get_userdata( $supporter['supporter'] );
				if( $_GET['group'] == 'applicants_global' ) {
					$slots_arr = get_post_meta( intval( $_GET['activity'] ), 'slots', true );
					$user_region = get_user_meta( $supp_obj->ID, 'region', true );
					if( $user_region != 0 && array_key_exists( $user_region, $slots_arr ) ) {
						continue;
					}
				}
				$participant = array(
					'label' => $supp_obj->first_name . ' ' . $supp_obj->last_name . ' (' . $supp_obj->user_email . ')',
					'value' => $supp_obj->user_email,
					'checked' => true
				);
				$participants[] = $participant;
			}
			$extra_selection = array(
				'type' => 'checkbox_group',
				'label' => _x( 'Partial Selection', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'receipients_partial',
				'options' => $participants,
				'desc' => _x( 'Select which of the supporters to send the mail to.', 'Admin Email Interface', 'vca-asm' )
			);
		} else {
			/* receipients (regions) array for select */
			$regions = $vca_asm_regions->get_all();
			$receipients = array();
			$receipients[0] = array(
				'label' => __( 'Testmail to yourself', 'vca-asm' ),
				'value' => 'tm'
			);
			if( current_user_can( 'vca_asm_send_global_emails' ) ) {
				$receipients[1] = array(
					'label' => __( 'All users of the Pool', 'vca-asm' ),
					'value' => 'all'
				);
				$receipients[2] = array(
					'label' => __( 'All members of cells and local crews', 'vca-asm' ),
					'value' => 'am'
				);
				$receipients[3] = array(
					'label' => __( 'All Head Ofs', 'vca-asm' ),
					'value' => 'ho'
				);
				$receipients[4] = array(
					'label' => __( 'Supporters with no specific region', 'vca-asm' ),
					'value' => 0
				);
			}
			foreach( $regions as $region ) {
				if( current_user_can( 'vca_asm_send_global_emails' ) || $admin_region == $region['id'] ) {
					/* members only option if region is a Cell or LC */
					switch( $region['status'] ) {
						case 'cell':
							$receipients[] = array(
								'label' => $region['name'] . ' (' . __( 'Cell', 'vca-asm' ) . ')',
								'value' => 'm' . $region['id']
							);
						break;
						case 'lc':
							$receipients[] = array(
								'label' => $region['name'] . ' (' . __( 'Local Crew', 'vca-asm' ) . ')',
								'value' => 'm' . $region['id']
							);
						break;
					}
					/* regular entry (all supporters living in region) */
					$receipients[] = array(
						'label' => $region['name'] . ' (' . __( 'all', 'vca-asm' ) . ')',
						'value' => $region['id']
					);
				}
			}
			$receipient_field = array(
				'type' => 'select',
				'label' => _x( 'Receipient', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'receipient',
				'options' => $receipients,
				'desc' => _x( 'Select who receives the email. If the region is a Cell or Local Crew, you may chose to only write to the supporters with membership status. Choose the "Testmail to yourself" to see how it will look in your own inbox.', 'Admin Email Interface', 'vca-asm' )
			);
			$extra_selection = array(
				'type' => 'checkbox',
				'label' => _x( 'Ignore user settings?', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'ignore_switch',
				'desc' => _x( 'As you know from your own profile, users may select which news to receive - general news, regional ones, both or none. In rare cases you have a message so important, that you might want to ignore the users wishes and reach everyone within your selected group. Tick this box to do so. Please do not make use of this feature frequently!', 'Admin Email Interface', 'vca-asm' )
			);
		}

		$fields = array(
			$receipient_field,
			array(
				'type' => 'select',
				'label' => _x( 'Sender', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'sender',
				'options' => array(
					array(
						'label' => 'no-reply@vivaconagua.org',
						'value' => 'nr'
					),
					array(
						'label' => _x( 'My own email address', 'Admin Email Interface', 'vca-asm' ),
						'value' => 'own'
					)
				),
				'desc' => _x( 'Send the email either from your personal email address or select the generic no-reply.', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'text',
				'label' =>  _x( 'Subject', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'subject',
				'desc' => _x( "The email's subject line", 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'textarea',
				'label' =>  _x( 'Message', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'message',
				'desc' => _x( 'Message Body', 'Admin Email Interface', 'vca-asm' )
			)
		);

		/* this is somewhat legacy, needs improvement */
		if( isset( $extra_selection ) ) {
			$first = array_shift($fields);
			array_unshift( $fields, $first, $extra_selection );
		}

		$output = '<div class="wrap">' .
				'<h2>' . __( 'Send an email', 'vca-asm' ) . '</h2>';

		if( isset( $_GET['todo'] ) && $_GET['todo'] == 'send' ) {
			/* send it! */
			$output .= $this->mail_send();
		}

		$output .= '<form name="vca_asm_groupmail_form" method="post" action="' . $form_action . '">' .
					'<input type="hidden" name="submitted" value="y"/>';
						require( VCA_ASM_ABSPATH . '/templates/admin-form.php' );
				$output .= '<p class="submit">' .
					'<input type="submit" name="submit" id="submit" class="button-primary"' .
					' onclick="if ( confirm(\'' .
						__( 'Send Email now?', 'vca-asm' ) .
						'\') ) { return true; } return false;"' .
					' value="' .
					__( 'Send Mail!', 'vca-asm' ) .
					'"></p></form>' .
			'</div>';

		echo $output;
	}

	/**
	 * Prepares groupmail for sending
	 *
	 * @since 1.0
	 * @access private
	 */
	private function mail_send() {
		global $current_user, $wpdb, $vca_asm_mailer, $vca_asm_regions;

		if( isset( $_POST['receipient'] ) && $_POST['receipient'] == 'all' ) {
			if( isset( $_POST['ignore_switch'] ) ) {
				$users_all = get_users();
				foreach( $users_all as $user ) {
					$to[] = $user->user_email;
				}
			} else {
				$args_all = array(
					'meta_key' => 'mail_switch',
					'meta_value' => 'all'
				);
				$users_all = get_users($args_all);
				$args_global = array(
					'meta_key' => 'mail_switch',
					'meta_value' => 'global'
				);
				$users_global = get_users($args_global);
				$to = array();
				foreach( $users_all as $user ) {
					$to[] = $user->user_email;
				}
				foreach( $users_global as $user ) {
					$to[] = $user->user_email;
				}
			}
		} elseif( isset( $_POST['receipient'] ) && substr( $_POST['receipient'], 0, 6 ) === 'single' ) {
			$to = substr( $_POST['receipient'], 6 );
		} elseif( isset( $_POST['receipient'] ) && $_POST['receipient'] == 'tm' ) {
			get_currentuserinfo();
			$to = $current_user->user_email;
		} elseif( isset( $_POST['receipient'] ) && $_POST['receipient'] == 'am' ) {
			$args = array(
				'meta_key' => 'membership',
				'meta_value' => 2
			);
			$supporters = get_users( $args );
			$to = array();
			foreach( $supporters as $supporter ) {
				if( isset( $_POST['ignore_switch'] ) ) {
					$to[] = $supporter->user_email;
				} else {
					$mail_switch = get_user_meta( $supporter->ID, 'mail_switch', true );
					if( $mail_switch == 'all' || $mail_switch == 'global' ) {
						$to[] = $supporter->user_email;
					}
				}
			}
		} elseif( isset( $_POST['receipient'] ) && $_POST['receipient'] == 'ho' ) {
			$args = array(
				'role' => 'head_of'
			);
			$supporters = get_users( $args );
			$to = array();
			foreach( $supporters as $supporter ) {
				$to[] = $supporter->user_email;
			}
		} elseif( isset( $_POST['receipient'] ) && substr( $_POST['receipient'], 0, 3 ) === 'act' ) {
			$to = array();
			foreach( $_POST['receipients_partial'] as $email ) {
				$to[] = $email;
			}
		} elseif( isset( $_POST['receipient'] ) && substr( $_POST['receipient'], 0, 1 ) === 'm' ) {
			$args = array(
				'meta_key' => 'region',
				'meta_value' => substr( $_POST['receipient'], 1 )
			);
			$supporters = get_users( $args );
			$to = array();
			foreach( $supporters as $supporter ) {
				$membership = get_user_meta( $supporter->ID, 'membership', true );
				if( $membership == 2 ) {
					if( isset( $_POST['ignore_switch'] ) ) {
						$to[] = $supporter->user_email;
					} else {
						$mail_switch = get_user_meta( $supporter->ID, 'mail_switch', true );
						if( $mail_switch == 'all' || $mail_switch == 'regional' ) {
							$to[] = $supporter->user_email;
						}
					}
				}
			}
		} elseif( isset( $_POST['receipient'] ) ) {
			$args = array(
				'meta_key' => 'region',
				'meta_value' => $_POST['receipient']
			);
			$supporters = get_users( $args );
			$to = array();
			foreach( $supporters as $supporter) {
				if( isset( $_POST['ignore_switch'] ) ) {
					$to[] = $supporter->user_email;
				} else {
					$mail_switch = get_user_meta( $supporter->ID, 'mail_switch', true );
					if( $mail_switch == 'all' || $mail_switch == 'regional' ) {
						$to[] = $supporter->user_email;
					}
				}
			}
		}

		if( isset( $_POST['sender'] ) && $_POST['sender'] === 'own' ) {
			get_currentuserinfo();
			if( ! in_array( 'head_of', $current_user->roles ) ) {
				$from_name = $current_user->first_name;
				$from_email = $current_user->user_email;
			} else {
				$region_id = get_user_meta( $current_user->ID, 'region', true );
				$region_name = $vca_asm_regions->get_name( $region_id );
				$from_name = 'Viva con Agua ' . $region_name;
				$from_email = $current_user->user_email;
			}
		} else {
			$from_name = NULL;
			$from_email = NULL;
		}

		$vca_asm_mailer->send( $to, $_POST['subject'], $_POST['message'], $from_name, $from_email );

		$success = '<div class="updated"><p><strong>' .
			sprintf(
				_x( 'The Email titled "%s" has been successfully sent.', 'Admin Email Interface', 'vca-asm' ),
				$_POST['subject']
			) .
			'</strong></p></div>';

		return $success;
	}

	/**
	 * Outputs form to edit autoresponse texts and saves them to the database
	 *
	 * @since 1.0
	 * @access public
	 */
	public function autoresponses_edit() {
		global $wpdb;

		$url = "admin.php?page=vca-asm-emails-autoresponses";
		$form_action = $url . "&amp;todo=save";

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

		$output = '<div class="wrap">' .
				'<h2>' . _x( 'Edit Automatic Responses', 'Admin Email Interface', 'vca-asm' ) . '</h2>' .
				'<p>' .
				_x( 'Here you can enable or disable, as well as overwrite the default autoresponses sent when a supporter completes a certain action. The title of the activity in question may be inserted via the placeholder "%s" (without quotation marks).', 'Admin Email Autoresponse Options', 'vca-asm' ) .
				'</p><form name="vca_asm_autoresponses_edit_form" method="post" action="' . $form_action . '">' .
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

		if( isset( $_GET['todo'] ) && $_GET['todo'] == 'save' ) {
			$output .= '<div class="updated"><p><strong>' .
				__( 'Options successfully updated!', 'vca-asm' ) .
				'</strong></p></div>';
		}

		require( VCA_ASM_ABSPATH . '/templates/admin-form.php' );

		$output .= '<p class="submit">' .
					'<input type="submit" name="submit" id="submit" class="button-primary"' .
					' value="' .
					__( 'Save Automatic Response Texts', 'vca-asm' ) .
					'"></p></form>' .
			'</div>';

		echo $output;
	}

} // class

endif; // class exists

?>
