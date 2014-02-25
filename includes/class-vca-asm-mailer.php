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
	 * Is called from other objects to send auto responses for user actions.
	 *
	 * Checks databse for custom auto response texts,
	 * otherwise sends generic mail.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function auto_response( $id, $action, $message_args = array() ) {
		global $wpdb;

		$emails_options = get_option( 'vca_asm_emails_options' );
		$format = ! empty( $emails_options['email_format_auto'] ) ? $emails_options['email_format_auto'] : 'plain';

		$default_args = array(
			'event' => __( 'Festival', 'vca-asm' ),
			'region' => __( 'Cell', 'vca-asm' ),
			'name' => __( 'Supporter', 'vca-asm' )
		);

		$placeholders = array( '%event%', '%region%', '%name%' );

		if ( is_array( $message_args ) ) {
			$message_args = wp_parse_args( $message_args, $default_args );
			extract( $message_args, EXTR_SKIP );
		} else {
			extract( $default_args, EXTR_SKIP );
			$event = $message_args;
			$region = $message_args;
		}

		$this_user = new WP_User( intval( $id ) );

		/* get email address */
		$to = $this_user->user_email;

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

		$replacements = array( $event, $region, $name );

		/* grab action options from database */
		$options_query = $wpdb->get_results(
			"SELECT * FROM " . $wpdb->prefix . "vca_asm_auto_responses " .
			"WHERE action = '" . $action . "' LIMIT 1", ARRAY_A
		);
		$options = $options_query[0];

		/* do nothing if notifications have been disabled for this action */
		if( $options['switch'] == 0 ) {
			return false;
		/* otherwise send message */
		} else {

			$default_responses = array(
				'applied'	=>	array(
					'subject'	=>	__( 'Successful application!', 'vca-asm' ),
					'message'	=>	sprintf( __( 'You have successfully applied to support Viva con Agua at "%s"', 'vca-asm' ), $event )
				),
				'accepted'	=>	array(
					'subject'	=>	__( 'Your application has been accepted!', 'vca-asm' ),
					'message'	=>	sprintf( __( 'Your application to support us at "%s" has been accepted. Please login to the Pool to check who your contact person(s) is/are.', 'vca-asm' ), $event )
				),
				'denied'	=>	array(
					'subject'	=>	__( 'Your application has been denied.', 'vca-asm' ),
					'message'	=>	sprintf( __( 'Sorry this time there weren\'t enough open slots for "%s" and your application had to be denied. You have been moved to the waiting list.', 'vca-asm' ), $event )
				),
				'reg_revoked'	=>	array(
					'subject'	=>	__( 'Registration revoked.', 'vca-asm' ),
					'message'	=>	sprintf( __( 'Your registration to "%s" has been revoked.', 'vca-asm' ), $event )
				),
				'mem_accepted'	=>	array(
					'subject'	=>	__( 'Membership confirmed.', 'vca-asm' ),
					'message'	=>	sprintf( __( 'Your membership to "%s" has been confirmed.', 'vca-asm' ), $region )
				),
				'mem_denied'	=>	array(
					'subject'	=>	__( 'Membership denied.', 'vca-asm' ),
					'message'	=>	sprintf( __( 'Your membership to "%s" has been denied.', 'vca-asm' ), $region )
				),
				'mem_cancelled'	=>	array(
					'subject'	=>	__( 'Membership cancelled.', 'vca-asm' ),
					'message'	=>	sprintf( __( 'Your membership to "%s" has been cencelled, either by yourself or a Head Of', 'vca-asm' ), $region )
				)
			);

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

			$this->send( $to, $subject, $message, NULL, NULL, $format, false, 0, $name, 0, 'auto' );

		} // if 'switch'
	}

	/**
	 * Sends mails
	 *
	 * @todo add attachments and finalize html messages
	 *
	 * @since 1.0
	 * @access public
	 */
	public function send( $receipient, $subject, $message, $from_name = NULL, $from_email = NULL, $content_type = NULL, $save = false, $membership = 0, $receipient_group = 'all', $receipient_id = 0, $type = 'manual' ) {
		global $wpdb;
		$save_message = trim ( $message );
		$message = wordwrap( $save_message, 70 );

		if( ! is_array( $receipient ) ) {
			$receipient = array( $receipient );
		}

		if( $from_name == NULL ) {
			$from_name = "Viva con Agua";
		}
		if( $from_email == NULL ) {
			$from_email = "no-reply@vivaconagua.org";
		}

		$time = time();

		$pool_link = '<a title="' . __( 'To the Pool!', 'vca-asm' ) . '" href="' . get_option( 'siteurl' ) . '" style="color:#0B0B0B;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;text-decoration:none;border-bottom: 1px dotted #008fc1;"><span style="color:#0B0B0B;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;text-decoration:none;border-bottom: 1px dotted #008fc1;"><span>' . __( 'Pool', 'vca-asm' ) . '</span></span></a>';
		if( 'applicants' === $receipient_group || 'waiting' === $receipient_group || 'participants' === $receipient_group ) {
			$reason = str_replace( '%POOL%', $pool_link, __( 'You are getting this message because are registered to the %POOL% and have applied to or are participating in an activity.', 'vca-asm' ) );
		} else {
			$reason = str_replace( '%POOL%', $pool_link, __( 'You are getting this message because are registered to the %POOL% and have chosen to receive newsletters.<br />You can change your newsletter preferences on your &quot;Profile &amp; Settings&quot; page.', 'vca-asm' ) );
		}

		$lid = $wpdb->get_results(
			"SELECT id FROM " . $wpdb->prefix . "vca_asm_emails" .
			" ORDER BY id DESC LIMIT 1", ARRAY_A
		);
		$nid = intval( $lid[0]['id'] ) + 1;

		$append = '<hr style="margin-top:0;margin-right:0;margin-bottom:21px;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;"><p style="color:#0B0B0B;margin-top:0;margin-right:0;margin-bottom:21px;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;font-size:13px;line-height:21px;font-family:Verdana,Geneva,Arial,Helvetica,sans-serif;">' .
				__( 'Is this email not easily readable or otherwise obscured?', 'vca-asm' ) . '<br />' .
				'<a title="' . __( 'Read the mail in your browser', 'vca-asm' ) . '" href="' . get_option( 'siteurl' ) . '/email?id=' . $nid . '&amp;hash=' . md5( $time ) . '" style="color:#0B0B0B;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;text-decoration:none;border-bottom: 1px dotted #008fc1;"><span style="color:#0B0B0B;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;text-decoration:none;border-bottom: 1px dotted #008fc1;"><span>' .
					__( 'Read it in your browser.', 'vca-asm' ) .
				'</span></span></a>' .
			'</p><p style="margin-top:0;margin-right:0;margin-bottom:21px;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;font-size:13px;line-height:21px;font-family:Verdana,Geneva,Arial,Helvetica,sans-serif;">' .
				$reason .
			'</p>';

		$lf = "\n";
		$eol = "\r\n";
		$semi_rand = md5( time() );

		$headers = array();
		$headers[] = "From: " . $from_name . " <" . $from_email . ">";
		$headers[] = "Reply-To: " . $from_name . " <" . $from_email . ">";
		$headers[] = "Return-Path: " . $from_name . " <" . $from_email . ">";
		$headers[] = "Message-ID: <" . $semi_rand . "@" . $_SERVER['SERVER_NAME'] . ">";
		$headers[] = "X-Priority: 3";
		$headers[] = "X-Mailer: PHPMailer " . phpversion() . " (http://code.google.com/a/apache-extras.org/p/phpmailer/)";
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

				strip_tags( preg_replace( '/<br[^>]*?>/i', $lf, preg_replace( '/(?:<\/p>)|(?:<\/h[1-6]>)/i', $lf.$lf, $message ) ) ). $lf . $lf .

				"--" . $mime_boundary_alt .  $lf .
				"Content-Type: text/html; charset=\"UTF-8\"" .  $lf .
				"Content-Transfer-Encoding: 8bit" . $lf . $lf;

			if ( $type === 'auto' ) {
				require( VCA_ASM_ABSPATH . '/templates/notifications.php' );
			} elseif ( $type === 'imageless' ) {
				require( VCA_ASM_ABSPATH . '/templates/newsletter.php' );
			} else {
				require( VCA_ASM_ABSPATH . '/templates/newsletter-with-images.php' );
			}

			$mpart_message .= $lf . $lf ."--" . $mime_boundary_alt . "--" . $lf . $lf . $lf .
				"--" . $mime_boundary_mxd . "--" . $eol . $eol . $eol;

			$message = $mpart_message;
		} else {
			$headers[] = "Content-Type: text/plain; charset=\"UTF-8\"";
			$message = strip_tags( preg_replace( '/<br[^>]*?>/i', $lf, preg_replace( '/(?:<\/p>)|(?:<\/h[1-6]>)/i', $lf.$lf, $message ) ) ) . $eol . $eol . $eol;
			$save_message = $message;
		}

		$hs = '';
		foreach( $headers as $header ) {
			$hs .= $header . $eol;
		}
		$headers = $hs . $eol. $eol . $eol;
		global $current_user;
		ini_set( 'sendmail_from', $from_email );

		$total_count = 0;
		$success_count = 0;
		$fail_count = 0;
		foreach( $receipient as $to ) {
			if( ! empty( $to ) ) {
				$user_id = email_exists( $to );
				if( $user_id ) {
					$first_name = get_user_meta( $user_id, 'first_name', true );
					$last_name = get_user_meta( $user_id, 'last_name', true );
					if( ! empty( $first_name ) && ! empty( $last_name ) ) {
						$to = $first_name . " " . $last_name . " <" . $to . ">";
					} elseif( ! empty( $first_name ) ) {
						$to = $first_name . " <" . $to . ">";
					} elseif( ! empty( $last_name ) ) {
						$to = $last_name . " <" . $to . ">";
					}
				}
				if ( 1 !== $current_user->ID && 479 !== $current_user->ID ) {
					$mail_bool = mail( $to, $subject, $message, $headers );
				} else {
					//print_r( '<p>To: |' . str_replace('<','&lt;',str_replace('>','&gt;',$to)) . '|<br />Headers: |' . str_replace('<','&lt;',//str_replace('>','&gt;',preg_replace("/\r|\n/","|EOL|",$headers))) . '|<br /></p>' );
					//$mail_bool = mail( $to, $subject, $message, $headers );
					$mail_bool = true;
				}
				$total_count++;
				if( $mail_bool ) {
					$success_count++;
				} else {
					$fail_count++;
				}
			}
		}
		ini_restore( 'sendmail_from' );

		if( true === $save ) {
			global $current_user;
			get_currentuserinfo();
			if ( 1 !== $current_user->ID && 479 !== $current_user->ID ) {
				$wpdb->insert(
					$wpdb->prefix . 'vca_asm_emails',
					array(
						'time' => $time,
						'sent_by' => $current_user->ID,
						'from' => $from_email,
						'subject' => $subject,
						'message' => $save_message,
						'membership' => $membership,
						'receipient_group' => $receipient_group,
						'receipient_id' => $receipient_id
					),
					array( '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%d' )
				);
			}
		}

		return array( $total_count, $success_count, $fail_count );
	}
} // class

endif; // class exists

?>
