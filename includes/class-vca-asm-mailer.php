<?php

/**
 * VCA_ASM_Mailer class.
 *
 * This class contains properties and methods to
 * send emails.
 *
 * It receives instructions from
 * @see class VCA_ASM_Admin_Emails
 * and
 * @see class VCA_ASM_Registrations
 *
 * @since 1.0
 */

if ( ! class_exists( 'VCA_ASM_Mailer' ) ) :

class VCA_ASM_Mailer {

	/**
	 * Class Properties
	 *
	 * @since 1.2
	 * @access private
	 */
	public $use_packets = true;

	public $packet_size = 100;
	private $sending_interval = 2;

	private $protocol = 'sendmail';
	private $url = 'smtp.vivaconagua.org';
	private $port = 25;
	private $user = 'no-reply@vivaconagua.org';
	private $pass = '';

	private $format_admin = 'plain';
	private $format_city = 'plain';
	private $format_auto = 'plain';

	/**
	 * Queues Mails in the outbox
	 *
	 * Bulk Mails are not sent directly, but placed in
	 * an outbox queue and sent in concrete packages by a cronjob.
	 *
	 * @param array $args
	 * @return array Maildata: DB ID, counts
	 *
	 * @since 1.4
	 * @access public
	 */
	public function queue( $args = array() ) {
		global $current_user, $wpdb,
			$vca_asm_geography;

		$default_args = array(
			'receipients' => 1,
			'subject' => 'The Subject',
			'message' => 'Lorem Ipsum',
			'from_name' => 'Viva con Agua',
			'from_email' => 'no-reply@vivaconagua.org',
			'format' => 'plain',
			'save' => true,
			'membership' => 0,
			'receipient_group' => 'all',
			'receipient_id' => 0,
			'type' => 'manual',
			'time' => time()
		);
		extract( wp_parse_args( $args, $default_args ), EXTR_SKIP );
		if ( ! is_array( $receipients ) ) $receipients = array( $receipients );

		$save_message = trim( $message );
		$message = wordwrap( $save_message, 70 );

		$wpdb->insert(
			$wpdb->prefix . 'vca_asm_emails',
			array(
				'time' => $time,
				'sent_by' => $current_user->ID,
				'from' => $from_email,
				'from_name' => $from_name,
				'subject' => $subject,
				'message' => $save_message,
				'membership' => $membership,
				'receipient_group' => $receipient_group,
				'receipient_id' => $receipient_id,
				'format' => $format,
				'type' => $type
			),
			array( '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%s' )
		);
		$mail_id = $wpdb->insert_id;

		$wpdb->insert(
			$wpdb->prefix . 'vca_asm_emails_queue',
			array(
				'mail_id' => $mail_id,
				'receipients' => serialize( $receipients ),
				'total_receipients' => count( $receipients )
			),
			array( '%d', '%s', '%d' )
		);

		return $mail_id;
	}

	/**
	 * Determines which sending callback to call
	 *
	 * @param array $args
	 * @return array $return what the callbacks return
	 *
	 * @since 1.4
	 * @access public
	 */
	public function send_pre( $args = array() ) {

		$default_args = array(
			'mail_id' => 1,
			'receipients' => array( 1 ),
			'subject' => 'The Subject',
			'message' => 'Lorem Ipsum',
			'from_name' => 'Viva con Agua',
			'from_email' => 'no-reply@vivaconagua.org',
			'content_type' => 'plain',
			'input_type' => 'plain',
			'for' => __( 'Supporters', 'vca-asm' ),
			'time' => time(),
			'mail_nation' => 'de',
			'reason' => 'newsletter',
			'auto_action' => '',
			'user_id' => 0
		);
		$args = wp_parse_args( $args, $default_args );
		if ( ! is_array( $args['receipients'] ) ) $args['receipients'] = array( $args['receipients'] );

		// DEBUG SPOT: Comment for safe testing, to prevent possible accidental sending
		switch ( $this->protocol ) {
			case 'smtp':
				$return = $this->send_smtp( $args );
			break;
		
			case 'sendmail':
			default:
				$return = $this->send_sendmail( $args );
		}

		return $return;
	}

	/**
	 * Sends mails
	 *
	 * First abstraction layer ontop of phpMailer.
	 *
	 * @param array $args
	 * @return array $results
	 *
	 * @since 1.4
	 * @access public
	 */
	public function send_smtp( $args = array() ) {
		global $current_user,
			$vca_asm_geography, $vca_asm_utilities;

		$emails_options = get_option( 'vca_asm_emails_options' );

		$results = array(
			'total' => 0,
			'successes' => 0,
			'failures' => 0,
			'failed_ids' => array(),
			'error_msgs' => array()
		);

		$lf = "\n";
		$eol = "\r\n";

		if ( ! class_exists( 'PHPMailer' ) ) {
			require( ABSPATH . '/wp-includes/class-phpmailer.php' );
		}

		$default_args = array(
			'mail_id' => 1,
			'receipients' => array( 1 ),
			'subject' => 'The Subject',
			'message' => 'Lorem Ipsum',
			'from_name' => 'Viva con Agua',
			'from_email' => 'no-reply@vivaconagua.org',
			'content_type' => 'plain',
			'input_type' => 'plain',
			'for' => __( 'Supporters', 'vca-asm' ),
			'time' => time(),
			'mail_nation' => 'de',
			'reason' => 'newsletter',
			'auto_action' => '',
			'user_id' => 0
		);
		extract( wp_parse_args( $args, $default_args ), EXTR_SKIP );
		if ( ! is_array( $receipients ) ) $receipients = array( $receipients );

		/***** PREPARE MESSAGE BODY *****/

		$message = trim( $message );
		if ( 'html' === $content_type ) {
			if ( $content_type === $input_type ) {
				$html_message = $message;
				$plain_message = strip_tags(
					preg_replace(
						'/<br[^>]*?>/i',
						$lf,
						preg_replace(
							'/(?:<\/p>)|(?:<\/h[1-6]>)/i',
							$lf.$lf,
							$message
						)
					)
				);
			} else {
				$html_message = '<p>' .
						preg_replace( '#(<br */?>\s*){2,}#i', '<br><br>' , preg_replace( '/[\r|\n]/', '<br>' , $vca_asm_utilities->urls_to_links( $message ) ) ) .
					'</p>';
				$plain_message = $message;
			}
		} else {
			if ( $input_type === 'html' ) {
				$html_message = $message;
				$plain_message = strip_tags(
					preg_replace(
						'/<br[^>]*?>/i',
						$lf,
						preg_replace(
							'/(?:<\/p>)|(?:<\/h[1-6]>)/i',
							$lf.$lf,
							$message
						)
					)
				);
			} else {
				$html_message = '<p>' .
						preg_replace( '#(<br */?>\s*){2,}#i', '<br><br>' , preg_replace( '/[\r|\n]/', '<br>' , $vca_asm_utilities->urls_to_links( $message ) ) ) .
					'</p>';
				$plain_message = $message;
			}
		}

		/***** PRE- AND APPEND MESSAGE BODY W/ STANDARDIZED HEADER & FOOTER *****/

		if ( 'html' === $content_type ) {
			$html_generator = new VCA_ASM_Email_Html( array(
				'mail_id' => $mail_id,
				'message' => $html_message,
				'subject' => $subject,
				'from_name' => $from_name,
				'time' => $time,
				'mail_nation' => $mail_nation,
				'for' => $for,
				'reason' => $reason,
				'auto_action' => $auto_action,
				'user_id' => $user_id
			));
			$html_message = $html_generator->output();
		} //add else / plain alternative

		/***** SETUP PHPMailer *****/

		$mailer = new PHPMailer();

		$mailer->IsSMTP();

		//$mailer->SMTPDebug  = 2;
		//$mailer->Debugoutput = 'html';

		$mailer->CharSet = 'UTF-8';

		$mailer->Host = ! empty( $emails_options['email_protocol_url'] ) ? $emails_options['email_protocol_url'] : 'smtp.vivaconagua.org';
		$mailer->Port = ! empty( $emails_options['email_protocol_port'] ) ? $emails_options['email_protocol_port'] : 25;
		$mailer->SMTPAuth = true;
		$mailer->SMTPKeepAlive = true;
		$mailer->Username = ! empty( $emails_options['email_protocol_username'] ) ? $emails_options['email_protocol_username'] : 'no-reply@vivaconagua.org';
		$mailer->Password = ! empty( $emails_options['email_protocol_pass'] ) ? $emails_options['email_protocol_pass'] : 'Opuhobema571';

		$mailer->SetFrom( $from_email, $from_name );
		$mailer->AddReplyTo( $from_email, $from_name );

		$mailer->Subject = html_entity_decode( $subject, ENT_QUOTES, 'UTF-8' );

		$mailer->WordWrap = 70;

		/***** SENDING LOOP *****/

		foreach ( $receipients as $receipient ) {

			/* Translation of User ID into readable information */
			$receipient_data = get_userdata( $receipient );
			if( ! empty( $receipient_data->user_firstname ) && ! empty( $receipient_data->user_lastname ) ) {
				$receipient_name = $receipient_data->user_firstname . ' ' . $receipient_data->user_lastname;
			} elseif( ! empty( $receipient_data->user_firstname ) ) {
				$receipient_name = $receipient_data->user_firstname;
			} elseif( ! empty( $receipient_data->user_lastname ) ) {
				$receipient_name = $receipient_data->user_lastname;
			} else {
				$receipient_name = __( 'Supporter', 'vca-asm' );
			}
			// DEBUG SPOT
			$receipient_email = $receipient_data->user_email;

			if ( 'html' === $content_type ) {
				$mailer->AltBody = $plain_message;
				$mailer->MsgHTML( $html_message );
			} else {
				$mailer->Body = $plain_message;
			}

			$mailer->AddAddress( $receipient_email, $receipient_name );

			// DEBUG SPOT
			$results['total']++;
			if ( ! $mailer->Send() ) {
				$results['failures']++;
				$results['failed_ids'][] = $receipient;
				$results['error_msgs'][] = 'Mailer Error (' . str_replace( '@', '&#64;', $receipient_email ) . ') ' . $mailer->ErrorInfo;
			} else {
				$results['successes']++;
			}

			$mailer->ClearAddresses();
		}

		return $results;
	}

	/**
	 * Sends mails via unix sendmail
	 * should not be used anymore in favor of send_smtp
	 *
	 * @since 1.0
	 * @access public
	 */
	public function send_sendmail( $args = array() ) {
		global $current_user,
			$vca_asm_geography, $vca_asm_utilities;

		$results = array(
			'total' => 0,
			'successes' => 0,
			'failures' => 0,
			'failed_ids' => array(),
			'error_msgs' => array()
		);

		$results = array(
			'total' => 0,
			'successes' => 0,
			'failures' => 0,
			'failed_ids' => array(),
			'error_msgs' => array()
		);

		$lf = "\n";
		$eol = "\r\n";

		if ( ! class_exists( 'PHPMailer' ) ) {
			require( ABSPATH . '/wp-includes/class-phpmailer.php' );
		}

		$default_args = array(
			'mail_id' => 1,
			'receipients' => array( 1 ),
			'subject' => 'The Subject',
			'message' => 'Lorem Ipsum',
			'from_name' => 'Viva con Agua',
			'from_email' => 'no-reply@vivaconagua.org',
			'content_type' => 'plain',
			'input_type' => 'plain',
			'for' => __( 'Supporters', 'vca-asm' ),
			'time' => time(),
			'mail_nation' => 'de',
			'reason' => 'newsletter',
			'auto_action' => '',
			'user_id' => 0
		);
		extract( wp_parse_args( $args, $default_args ), EXTR_SKIP );

		/***** PREPARE MESSAGE BODY *****/

		$message = trim( $message );
		if ( 'html' === $content_type ) {
			if ( $content_type === $input_type ) {
				$html_message = $message;
				$plain_message = strip_tags(
					preg_replace(
						'/<br[^>]*?>/i',
						$lf,
						preg_replace(
							'/(?:<\/p>)|(?:<\/h[1-6]>)/i',
							$lf.$lf,
							$message
						)
					)
				);
			} else {
				$html_message = '<p>' .
						preg_replace( '#(<br */?>\s*){2,}#i', '<br><br>' , preg_replace( '/[\r|\n]/', '<br>' , $vca_asm_utilities->urls_to_links( $message ) ) ) .
					'</p>';
				$plain_message = $message;
			}
		} else {
			if ( $input_type === 'html' ) {
				$html_message = $message;
				$plain_message = strip_tags(
					preg_replace(
						'/<br[^>]*?>/i',
						$lf,
						preg_replace(
							'/(?:<\/p>)|(?:<\/h[1-6]>)/i',
							$lf.$lf,
							$message
						)
					)
				);
			} else {
				$html_message = '<p>' .
						preg_replace( '#(<br */?>\s*){2,}#i', '<br><br>' , preg_replace( '/[\r|\n]/', '<br>' , $vca_asm_utilities->urls_to_links( $message ) ) ) .
					'</p>';
				$plain_message = $message;
			}
		}

		/***** PRE- AND APPEND MESSAGE BODY W/ STANDARDIZED HEADER & FOOTER *****/

		if ( 'html' === $content_type ) {
			$html_generator = new VCA_ASM_Email_Html( array(
				'mail_id' => $mail_id,
				'message' => $html_message,
				'subject' => $subject,
				'from_name' => $from_name,
				'time' => $time,
				'mail_nation' => $mail_nation,
				'for' => $for,
				'reason' => $reason,
				'auto_action' => $auto_action,
				'user_id' => $user_id
			));
			$html_message = $html_generator->output();
		} //add else / plain alternative

		/***** SENDMAIL *****/

		$lf = "\n";
		$eol = "\r\n";
		$semi_rand = md5( $time );

		$headers = array();
		$headers[] = "From: " . $from_name . " <" . $from_email . ">";
		$headers[] = "Reply-To: " . $from_name . " <" . $from_email . ">";
		$headers[] = "Return-Path: " . $from_name . " <" . $from_email . ">";
		$headers[] = "Message-ID: <" . $semi_rand . "@" . $_SERVER['SERVER_NAME'] . ">";
		$headers[] = "X-Priority: 3";
		$headers[] = "X-Mailer: Sendmail";
		$headers[] = "X-Sender: " . $from_email;
		$headers[] = "MIME-Version: 1.0";

		if( $content_type == 'html' ) {

			$mime_boundary_mxd = 'b1_' . $semi_rand;
			$mime_boundary_alt = 'b2_' . $semi_rand;

			$headers[] = "Content-Type: multipart/mixed;";
			$headers[] = "	boundary=\"" . $mime_boundary_mxd . "\"";

			$mpart_message = "--" . $mime_boundary_mxd . $lf .
				"Content-Type: multipart/alternative;" . $lf .
				"	boundary=\"" . $mime_boundary_alt . "\"" . $lf . $lf .

				"--" . $mime_boundary_alt . $lf .
				"Content-Type: text/plain; charset=\"UTF-8\"" . $lf .
				"Content-Transfer-Encoding: 8bit" . $lf . $lf .

				$plain_message .

				$lf . $lf . "--" . $mime_boundary_alt .  $lf .
				"Content-Type: text/html; charset=\"UTF-8\"" .  $lf .
				"Content-Transfer-Encoding: 8bit" . $lf . $lf .

				$html_message .

				$lf . $lf ."--" . $mime_boundary_alt . "--" . $lf . $lf . $lf .
				"--" . $mime_boundary_mxd . "--" . $eol . $eol . $eol;

			$message = $mpart_message;

		} else {
			$headers[] = "Content-Type: text/plain; charset=\"UTF-8\"";
			$message = $plain_message . $eol . $eol . $eol;
		}

		$hs = '';
		foreach( $headers as $header ) {
			$hs .= $header . $eol;
		}
		$headers = $hs . $eol. $eol . $eol;

		ini_set( 'sendmail_from', $from_email );

		$results = array(
			'total' => 0,
			'successes' => 0,
			'failures' => 0,
			'failed_ids' => array(),
			'error_msgs' => array()
		);
		foreach( $receipients as $receipient ) {

			$receipient_data = get_userdata( $receipient );
			if( ! empty( $receipient_data->user_firstname ) && ! empty( $receipient_data->user_lastname ) ) {
				$receipient_name = $receipient_data->user_firstname . ' ' . $receipient_data->user_lastname;
			} elseif( ! empty( $receipient_data->user_firstname ) ) {
				$receipient_name = $receipient_data->user_firstname;
			} elseif( ! empty( $receipient_data->user_lastname ) ) {
				$receipient_name = $receipient_data->user_lastname;
			} else {
				$receipient_name = __( 'Supporter', 'vca-asm' );
			}
			$receipient_email = $receipient_data->user_email;

			$mail_bool = mail( $receipient_name.' <'.$receipient_email.'>', $subject, $message, $headers );

			if( $mail_bool ) {
				$results['successes']++;
			} else {
				$results['failures']++;
				$results['failed_ids'][] = $user_id;
				$results['error_messages'][] = 'Sendmail failed.';
			}
			$results['total']++;

		}

		ini_restore( 'sendmail_from' );

		return $results;
	}

	/**
	 * Is called from other objects to send auto responses for user actions.
	 *
	 * Checks database for custom auto response texts,
	 * otherwise sends generic mail.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function auto_response( $id, $action, $message_args = array() ) {
		global $current_user, $wpdb;

		$emails_options = get_option( 'vca_asm_emails_options' );
		$format = ! empty( $emails_options['email_format_auto'] ) ? $emails_options['email_format_auto'] : 'plain';

		$default_args = array(
			'scope' => 0,
			'from_name' => 'Viva con Agua',
			'from_email' => 'no-reply@vivaconagua.org',
			'activity_id' => 0,
			'activity' => __( 'Festival', 'vca-asm' ),
			'city_id' => 0,
			'city' => __( 'Cell', 'vca-asm' ),
			'name' => __( 'Supporter', 'vca-asm' )
		);

		$placeholders = array( '%event%', '%region%', '%name%' );

		if ( is_array( $message_args ) ) {
			$message_args = wp_parse_args( $message_args, $default_args );
			extract( $message_args, EXTR_SKIP );
		} else { /* lagacy | backwards compatibility */
			extract( $default_args, EXTR_SKIP );
			$activity = $message_args;
			$city = $message_args;
		}
		/* lagacy | backwards compatibility */
		$scope = empty( $scope ) ? get_user_meta( $current_user->ID, 'nation', true ) : $scope;

		$this_user = new WP_User( intval( $id ) );

		/* construct name, if not given in args */
		if ( $name === __( 'Supporter', 'vca-asm' ) ) {
			$first_name = $this_user->first_name;
			$last_name = $this_user->last_name;
			if( ! empty( $first_name ) && ! empty( $last_name ) ) {
				$name = $first_name . " " . $last_name;
			} elseif( ! empty( $first_name ) ) {
				$name = $first_name;
			} elseif( ! empty( $last_name ) ) {
				$name = $last_name;
			}
		}

		$default_responses = array(
			'applied'	=>	array(
				'subject'	=>	__( 'Successful application!', 'vca-asm' ),
				'message'	=>	sprintf( __( 'You have successfully applied to support Viva con Agua at "%s"', 'vca-asm' ), $activity )
			),
			'accepted'	=>	array(
				'subject'	=>	__( 'Your application has been accepted!', 'vca-asm' ),
				'message'	=>	sprintf( __( 'Your application to support us at "%s" has been accepted. Please login to the Pool to check who your contact person(s) is/are.', 'vca-asm' ), $activity )
			),
			'denied'	=>	array(
				'subject'	=>	__( 'Your application has been denied.', 'vca-asm' ),
				'message'	=>	sprintf( __( 'Sorry this time there weren\'t enough open slots for "%s" and your application had to be denied. You have been moved to the waiting list.', 'vca-asm' ), $activity )
			),
			'reg_revoked'	=>	array(
				'subject'	=>	__( 'Registration revoked.', 'vca-asm' ),
				'message'	=>	sprintf( __( 'Your registration to "%s" has been revoked.', 'vca-asm' ), $activity )
			),
			'mem_accepted'	=>	array(
				'subject'	=>	__( 'Membership confirmed.', 'vca-asm' ),
				'message'	=>	sprintf( __( 'Your membership to "%s" has been confirmed.', 'vca-asm' ), $city )
			),
			'mem_denied'	=>	array(
				'subject'	=>	__( 'Membership denied.', 'vca-asm' ),
				'message'	=>	sprintf( __( 'Your membership to "%s" has been denied.', 'vca-asm' ), $city )
			),
			'mem_cancelled'	=>	array(
				'subject'	=>	__( 'Membership cancelled.', 'vca-asm' ),
				'message'	=>	sprintf( __( 'Your membership to &quot;%s&quot; has been cencelled, either by yourself or a city user.', 'vca-asm' ), $city )
			)
		);

		$replacements = array( $activity, $city, $name );

		/* grab action options from database */
		$options_query = $wpdb->get_results(
			"SELECT * FROM " . $wpdb->prefix . "vca_asm_auto_responses " .
			"WHERE action = '" . $action . "' AND scope = '" . $scope . "' LIMIT 1", ARRAY_A
		);
		$options = $options_query[0];

		/* do nothing if notifications have been disabled for this action */
		if( $options['switch'] == 0 ) {
			return false;
		/* otherwise send message */
		} else {

			if( ! empty( $options['subject'] ) ) {
				$subject = str_replace( $placeholders, $replacements, $options['subject'] );
			} else {
				$subject = '[Viva con Agua] ' . $default_responses[$action]['subject'];
			}

			if( ! empty( $options['message'] ) ) {
				$message = str_replace( $placeholders, $replacements, $options['message'] );
			} else {
				$message = $default_responses[$action]['message'];
			}

			$mail_id = ! empty( $activity_id ) ? $activity_id : $city_id;
			$mail_nation = get_post_meta( $action );
			$mail_nation = ! empty( $mail_nation ) ? $mail_nation : 'de';
			$reason = in_array( $action, array( 'mem_accepted', 'mem_denied', 'mem_cancelled' ) ) ? 'membership' : 'activity';

			$this->send_pre( array(
				'mail_id' => $mail_id,
				'receipients' => $id,
				'subject' => stripcslashes( $subject ),
				'message' => stripcslashes( $message ),
				'from_name' => $from_name,
				'from_email' => $from_email,
				'content_type' => $format,
				'input_type' => 'plain',
				'for' => $name,
				'time' => time(),
				'mail_nation' => $mail_nation,
				'reason' => $reason,
				'auto_action' => $action,
				'user_id' => $id
			));
		}
	}

	/******************** THE OUTBOX ********************/

	/**
	 * WP-Cron Callback
	 * Checks for queued mails.
	 * Sends a parcel, if mails are scheduled for sending.
	 *
	 * @since 1.3
	 * @access public
	 */
	public function check_outbox() {
		global $wpdb,
			$vca_asm_geography;

		$queued = $wpdb->get_results(
			"SELECT * FROM " .
			$wpdb->prefix . "vca_asm_emails_queue " .
			"ORDER BY id ASC LIMIT 1", ARRAY_A
		);
		$queued = isset( $queued[0] ) ? $queued[0] : NULL;
		if ( empty( $queued ) ) {
			return false;
		}

		$the_mail = $wpdb->get_results(
			"SELECT * FROM " .
			$wpdb->prefix . "vca_asm_emails " .
			"WHERE id = " . $queued['mail_id'] . " LIMIT 1", ARRAY_A
		);
		$the_mail = isset( $the_mail[0] ) ? $the_mail[0] : NULL;
		if ( empty( $the_mail ) ) {
			return false;
		}

		$receipients = unserialize( $queued['receipients'] );

		$queue_count = count( $receipients );

		$end = $this->packet_size < $queue_count ? $this->packet_size : $queue_count;
		$end = $this->use_packets ? $end : $queue_count;

		if ( $end === $queue_count ) {
			$current_batch = $receipients;
			$wpdb->query(
				"DELETE FROM " . $wpdb->prefix."vca_asm_emails_queue " .
				"WHERE id = " . $queued['id']
			);
		} else {
			$current_batch = array_slice( $receipients, 0, $end );
			$receipients = array_slice( $receipients, $end );
			$wpdb->update(
				$wpdb->prefix.'vca_asm_emails_queue',
				array(
					'receipients' => serialize( $receipients )
				),
				array( 'id' => $queued['id'] ),
				array( '%s' ),
				array( '%d' )
			);
		}

		$log_file = VCA_ASM_ABSPATH . '/logs/mailer.log';
		$log_msg = 'logged';
		file_put_contents( $log_file, $log_msg . "\n\n", FILE_APPEND | LOCK_EX );

		$type = ( isset( $the_mail['type'] ) && in_array( $the_mail['type'], array( 'newsletter', 'activity' ) ) ) ? $the_mail['type'] : 'newsletter';

		$mailer_return = $this->send_pre( array(
			'mail_id' => $queued['mail_id'],
			'receipients' => $current_batch,
			'subject' => $the_mail['subject'],
			'message' => $the_mail['message'],
			'from_name' => $the_mail['from_name'],
			'from_email' => $the_mail['from'],
			'content_type' => $the_mail['format'],
			'input_type' => $the_mail['format'],
			'for' => $this->determine_for_field( $the_mail['receipient_group'], $the_mail['receipient_id'], $the_mail['membership'] ),
			'time' => $the_mail['time'],
			'mail_nation' => $vca_asm_geography->get_alpha_code( get_user_meta( $the_mail['sent_by'], 'nation', true ) ),
			'reason' => $type
		));

		$log_file = VCA_ASM_ABSPATH . '/logs/mailer.log';
		$log_msg = 'Time: ' . time() . "\n" .
			'Total sent: ' . $mailer_return[total] . "\n" .
			'Successes: ' . $mailer_return[successes] . "\n" .
			'Failures: ' . $mailer_return[failures];
		if ( ! empty( $mailer_return[failures] ) ) {
			$i = 1;
			foreach ( $mailer_return[failed_ids] as $user_id ) {
				$log_msg .= "\n" . 'Failure #' . $i . ', User ID: ' . $user_id;
				if ( isset( $mailer_return[error_msgs][$i-1] ) ) {
					$log_msg .= "\n" . 'Failure #' . $i . ', msg: ' . $mailer_return[error_msgs][$i-1];
				}
				$i++;
			}
		}
		file_put_contents( $log_file, $log_msg . "\n\n", FILE_APPEND | LOCK_EX );
	}

	/******************** UTILITY METHODS ********************/

	/**
	 * Returns a string for the "for" field for the e-mail body's headline
	 * previously done within template file
	 *
	 * @since 1.4
	 * @access public
	 */
	public function determine_for_field( $receipient_group, $receipient_id, $membership ) {
		global $vca_asm_geography;

		switch( $receipient_group ) {
			case 'all':
				if ( 'active' === $membership ) {
					$for = __( 'All Active Members', 'vca-asm' );
				} elseif ( 'inactive' === $membership ) {
					$for = __( 'Pool Users', 'vca-asm' );
				} else {
					$for = __( 'All Supporters', 'vca-asm' );
				}
			break;

			case 'alln':
				if( 'active' === $membership ) {
					$for = sprintf( __( 'Active Members from %s', 'vca-asm' ), $vca_asm_geography->get_name( $receipient_id ) );
				} elseif( 'inactive' === $membership ) {
					$for = sprintf( __( 'Pool Users from %s', 'vca-asm' ), $vca_asm_geography->get_name( $receipient_id ) );
				} else {
					$for = sprintf( __( 'All Supporters from %s', 'vca-asm' ), $vca_asm_geography->get_name( $receipient_id ) );
				}
			break;

			case 'ho': /* leagcy */
			case 'cu':
				$for = __( 'All City Users', 'vca-asm' );
			break;

			case 'cun':
				$for = sprintf( __( 'All City Users from %s', 'vca-asm' ), $vca_asm_geography->get_name( $receipient_id ) );
			break;

			case 'admins':
				$for = __( 'Office / Administrators', 'vca-asm' );
			break;

			case 'adminsn':
				$for = sprintf( __( 'Office / Administrators from %s', 'vca-asm' ), $vca_asm_geography->get_name( $receipient_id ) );
			break;

			case 'region':  /* leagcy */
			case 'city':
			case 'cg':
			case 'nation':
			case 'ng':
				if( 'active' === $membership ) {
					$for = sprintf( __( 'Active Members from %s', 'vca-asm' ), $vca_asm_geography->get_name( $receipient_id ) );
				} elseif( 'inactive' === $membership ) {
					$for = sprintf( __( 'Pool Users from %s', 'vca-asm' ), $vca_asm_geography->get_name( $receipient_id ) );
				} else {
					$for = sprintf( __( 'Supporters from %s', 'vca-asm' ), $vca_asm_geography->get_name( $receipient_id ) );
				}
			break;

			case 'apps':
				$for = sprintf( __( 'Applicants to %s', 'vca-asm' ), get_the_title( $receipient_id ) );
			break;

			case 'parts':
				$for = sprintf( __( 'Participants of %s', 'vca-asm' ), get_the_title( $receipient_id ) );
			break;

			case 'waiting':
				$for = sprintf( __( 'Waiting List for %s', 'vca-asm' ), get_the_title( $receipient_id ) );
			break;

			default:
				$for = __( 'Selection', 'vca-asm' );
			break;
		}

		return $for;
	}

	/**
	 * Returns the id of a receipient group
	 * based on the kind of group
	 *
	 * @since 1.4
	 * @access public
	 */
	public function receipient_id_from_group( $receipient_group, $with_users = false, $ignore_switch, $membership ) {
		global $current_user,
			$vca_asm_activities, $vca_asm_geography;

		$admin_nation = get_user_meta( $current_user->ID, 'nation', true );
		$receipient_id = 0;
		$receipients = array();

		switch ( $receipient_group ) {
			case 'all':
			case 'alln':
				$receipient_id = 'alln' === $receipient_group ? $admin_nation : 0;
				if ( true === $with_users ) {
					$metaqueries = array( 'relation' => 'AND' );
					if ( ! $ignore_switch ) {
						$metaqueries[] = array(
							'key' => 'mail_switch',
							'value' => array( 'all', 'global' ),
							'compare' => 'IN'
						);
					}
					if ( 'active' === $membership ) {
						$metaqueries[] = array(
							'key' => 'membership',
							'value' => 2
						);
					} elseif ( 'inactive' === $membership ) {
						$metaqueries[] = array(
							'key' => 'membership',
							'value' => array( 0, 1 ),
							'compare' => 'IN'
						);
					}
					if ( 'alln' == $receipient_group ) {
						$metaqueries[] = array(
							'key' => 'nation',
							'value' => $admin_nation
						);
					}
					$args = array(
						'meta_query' => $metaqueries
					);
					$users = get_users( $args );
					foreach( $users as $user ) {
						if ( ! in_array( 'pending', $user->roles ) && ! in_array( 'city', $user->roles ) ) {
							$receipients[] = $user->ID;
						}
					}
				}
			break;

			case 'ho': /* leagcy */
			case 'cu':
			case 'cun':
				$receipient_id = 'cun' === $receipient_group ? $admin_nation : 0;
				if ( true === $with_users ) {
					$args = array(
						'role' => 'city'
					);
					$users = get_users( $args );
					foreach( $users as $user ) {
						$cu_nation = get_user_meta( $user->ID, 'nation', true );
						if ( 'cun' !== $receipient_group || $admin_nation === $cu_nation ) {
							$receipients[] = $user->ID;
						}
					}
				}
			break;

			case 'admins':
			case 'adminsn':
				$receipient_id = 'adminsn' === $receipient_group ? $admin_nation : 0;
				if ( true === $with_users ) {
					$users = get_users();
					foreach( $users as $user ) {
						$user_nation = get_user_meta( $user->ID, 'nation', true );
						if (
							! in_array( 'pending', $user->roles ) &&
							! in_array( 'supporter', $user->roles ) &&
							! in_array( 'city', $user->roles ) &&
							( 'adminsn' !== $receipient_group || $admin_nation === $user_nation )
						) {
							$receipients[] = $user->ID;
						}
					}
				}
			break;

			case 'region':  /* leagcy */
			case 'city':
				$receipient_id = isset( $_POST['city-id'] ) ? $_POST['city-id'] : 0;
				if ( true === $with_users ) {
					$metaqueries = array( 'relation' => 'AND' );
					if ( ! $ignore_switch ) {
						$metaqueries[] = array(
							'key' => 'mail_switch',
							'value' => array( 'all', 'regional' ),
							'compare' => 'IN'
						);
					}
					if ( 'active' === $membership ) {
						$metaqueries[] = array(
							'key' => 'membership',
							'value' => 2
						);
					} elseif ( 'inactive' === $membership ) {
						$metaqueries[] = array(
							'key' => 'membership',
							'value' => array( 0, 1 ),
							'compare' => 'IN'
						);
					}
					$metaqueries[] = array(
						'key' => 'city',
						'value' => $receipient_id
					);
					$args = array(
						'meta_query' => $metaqueries
					);
					$users = get_users( $args );
					foreach ( $users as $user ) {
						if ( ! in_array( 'city', $user->roles ) && ! in_array( 'pending', $user->roles ) ) {
							$receipients[] = $user->ID;
						}
					}
				}
			break;

			case 'cg':
				$receipient_id = isset( $_POST['cg-id'] ) ? $_POST['cg-id'] : 0;
				if ( true === $with_users ) {
					$metaqueries = array( 'relation' => 'AND' );
					if ( ! $ignore_switch ) {
						$metaqueries[] = array(
							'key' => 'mail_switch',
							'value' => array( 'all', 'regional' ),
							'compare' => 'IN'
						);
					}
					if ( 'active' === $membership ) {
						$metaqueries[] = array(
							'key' => 'membership',
							'value' => 2
						);
					} elseif ( 'inactive' === $membership ) {
						$metaqueries[] = array(
							'key' => 'membership',
							'value' => array( 0, 1 ),
							'compare' => 'IN'
						);
					}
					$metaqueries[] = array(
						'key' => 'city',
						'value' => $vca_asm_geography->get_descendants( $receipient_id, array(
							'data' => 'id',
							'format' => 'array',
							'type' => 'city'
						)),
						'compare' => 'IN'
					);
					$args = array(
						'meta_query' => $metaqueries
					);
					$users = get_users( $args );
					foreach ( $users as $user ) {
						if ( ! in_array( 'city', $user->roles ) && ! in_array( 'pending', $user->roles ) ) {
							$receipients[] = $user->ID;
						}
					}
				}
			break;

			case 'nation':
				$receipient_id = isset( $_POST['nation-id'] ) ? $_POST['nation-id'] : 0;
				if ( true === $with_users ) {
					$metaqueries = array( 'relation' => 'AND' );
					if ( ! $ignore_switch ) {
						$metaqueries[] = array(
							'key' => 'mail_switch',
							'value' => array( 'all', 'regional' ),
							'compare' => 'IN'
						);
					}
					if ( 'active' === $membership ) {
						$metaqueries[] = array(
							'key' => 'membership',
							'value' => 2
						);
					} elseif ( 'inactive' === $membership ) {
						$metaqueries[] = array(
							'key' => 'membership',
							'value' => array( 0, 1 ),
							'compare' => 'IN'
						);
					}
					$metaqueries[] = array(
						'key' => 'nation',
						'value' => $receipient_id
					);
					$args = array(
						'meta_query' => $metaqueries
					);
					$users = get_users( $args );
					foreach ( $users as $user ) {
						if ( ! in_array( 'city', $user->roles ) && ! in_array( 'pending', $user->roles ) ) {
							$receipients[] = $user->ID;
						}
					}
				}
			break;

			case 'ng':
				$receipient_id = isset( $_POST['ng-id'] ) ? $_POST['ng-id'] : 0;
				if ( true === $with_users ) {
					$metaqueries = array( 'relation' => 'AND' );
					if ( ! $ignore_switch ) {
						$metaqueries[] = array(
							'key' => 'mail_switch',
							'value' => array( 'all', 'regional' ),
							'compare' => 'IN'
						);
					}
					if ( 'active' === $membership ) {
						$metaqueries[] = array(
							'key' => 'membership',
							'value' => 2
						);
					} elseif ( 'inactive' === $membership ) {
						$metaqueries[] = array(
							'key' => 'membership',
							'value' => array( 0, 1 ),
							'compare' => 'IN'
						);
					}
					$metaqueries[] = array(
						'key' => 'city',
						'value' => $vca_asm_geography->get_descendants( $receipient_id, array(
							'data' => 'id',
							'format' => 'array',
							'type' => 'nation'
						)),
						'compare' => 'IN'
					);
					$args = array(
						'meta_query' => $metaqueries
					);
					$users = get_users( $args );
					foreach ( $users as $user ) {
						if ( ! in_array( 'city', $user->roles ) && ! in_array( 'pending', $user->roles ) ) {
							$receipients[] = $user->ID;
						}
					}
				}
			break;

			case 'apps':
				$receipient_id = isset( $_POST['activity'] ) ? $_POST['activity'] : 0;
				if ( true === $with_users ) {
					// is the sending user allowed to target this activity?
					if ( $vca_asm_activities->is_relevant_to_user( $receipient_id, $current_user ) ) {
						$the_activity = new VCA_ASM_Activity( $receipient_id );
						// is the user eligible as a sender because of his capabilities or because of a relevant quota only?
						if ( $vca_asm_activities->is_relevant_to_user( $receipient_id, $current_user, array( 'quotas' => false ) ) ) {
							$receipients = $the_activity->applicants;
						} else {
							$admin_city = get_user_meta( $current_user->ID, 'city', true );
							$receipients = $the_activity->applicants_by_quota[$admin_city];
						}
					} else {
						$receipients = array();
					}
				}
			break;

			case 'parts':
				$receipient_id = isset( $_POST['activity'] ) ? $_POST['activity'] : 0;
				if ( true === $with_users ) {
					// is the sending user allowed to target this activity?
					if ( $vca_asm_activities->is_relevant_to_user( $receipient_id, $current_user ) ) {
						$the_activity = new VCA_ASM_Activity( $receipient_id );
						// is the user eligible as a sender because of his capabilities or because of a relevant quota only?
						if ( $vca_asm_activities->is_relevant_to_user( $receipient_id, $current_user, array( 'quotas' => false ) ) ) {
							$receipients = $the_activity->participants;
						} else {
							$admin_city = get_user_meta( $current_user->ID, 'city', true );
							$receipients = $the_activity->participants_by_quota[$admin_city];
						}
					} else {
						$receipients = array();
					}
				}
			break;

			case 'waiting':
				$receipient_id = isset( $_POST['activity'] ) ? $_POST['activity'] : 0;
				if ( true === $with_users ) {
					// is the sending user allowed to target this activity?
					if ( $vca_asm_activities->is_relevant_to_user( $receipient_id, $current_user ) ) {
						$the_activity = new VCA_ASM_Activity( $receipient_id );
						// is the user eligible as a sender because of his capabilities or because of a relevant quota only?
						if ( $vca_asm_activities->is_relevant_to_user( $receipient_id, $current_user, array( 'quotas' => false ) ) ) {
							$receipients = $the_activity->waiting;
						} else {
							$admin_city = get_user_meta( $current_user->ID, 'city', true );
							$receipients = $the_activity->waiting_by_quota[$admin_city];
						}
					} else {
						$receipients = array();
					}
				}
			break;
		}

		if ( true === $with_users ) {
			return array( $receipient_id, $receipients );
		}

		return $receipient_id;
	}

	/******************** CLASS CONSTRUCTOR ********************/

	/**
	 * Constructor
	 *
	 * @since 1.3
	 * @access public
	 */
	public function __construct() {
		global $vca_asm_cron;

		$options = get_option( 'vca_asm_emails_options' );
		$this->use_packets = ! empty( $options['email_sending_packet_switch'] ) && 1 == $options['email_sending_packet_switch'];
		$this->packet_size = ! empty( $options['email_sending_packet_size'] ) ? $options['email_sending_packet_size'] : $this->packet_size;
		$this->sending_interval = ! empty( $options['email_sending_interval'] ) ? $options['email_sending_interval'] : $this->sending_interval;
		$this->protocol = ! empty( $options['email_protocol_type'] ) ? $options['email_protocol_type'] : $this->protocol;
		$this->url = ! empty( $options['email_protocol_url'] ) ? $options['email_protocol_url'] : $this->url;
		$this->port = ! empty( $options['email_protocol_port'] ) ? $options['email_protocol_port'] : $this->port;
		$this->user = ! empty( $options['email_protocol_username'] ) ? $options['email_protocol_username'] : $this->user;
		$this->pass = ! empty( $options['email_protocol_pass'] ) ? $options['email_protocol_pass'] : $this->pass;
		$this->format_admin = ! empty( $options['email_format_admin'] ) ? $options['email_format_admin'] : $this->format_admin;
		$this->format_city = ! empty( $options['email_format_city'] ) ? $options['email_format_city'] : $this->format_city;
		$this->format_auto = ! empty( $options['email_format_auto'] ) ? $options['email_format_auto'] : $this->format_auto;

		add_action( 'vca_asm_check_outbox', array( $this, 'check_outbox' ) );
		/* $vca_asm_cron holds all active cron hooks */
		$vca_asm_cron->hooks[] = 'vca_asm_check_outbox';
		if ( ! wp_next_scheduled( 'vca_asm_check_outbox' ) ) {
			wp_schedule_event( time(), $this->sending_interval.'minutely', 'vca_asm_check_outbox' );
		} elseif ( rtrim( wp_get_schedule( 'vca_asm_check_outbox' ), 'minutely' ) != $this->sending_interval ) {
			wp_unschedule_event( wp_next_scheduled( 'vca_asm_check_outbox' ), 'vca_asm_check_outbox' );
			wp_schedule_event( time(), $this->sending_interval.'minutely', 'vca_asm_check_outbox' );
		}
		//wp_unschedule_event( wp_next_scheduled( 'vca_asm_check_outbox' ), 'vca_asm_check_outbox' );
	}

} // class

endif; // class exists

?>