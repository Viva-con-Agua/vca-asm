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
	public function auto_response( $id, $action, $event_name = '' ) {
		global $wpdb;
		
		$this_user = new WP_User( $id );
		
		/* get email address */
		$to = $this_user->user_email;
		
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
					'message'	=>	sprintf( __( 'You have successfully applied to support Viva con Agua at "%s"', 'vca-asm' ), $event_name )
				),
				'accepted'	=>	array(
					'subject'	=>	__( 'Your application has been accepted!', 'vca-asm' ),
					'message'	=>	sprintf( __( 'Your application to support us at "%s" has been accepted. Please login to the Pool to check who your contact person(s) is/are.', 'vca-asm' ), $event_name )
				),
				'denied'	=>	array(
					'subject'	=>	__( 'Your application has been denied.', 'vca-asm' ),
					'message'	=>	sprintf( __( 'Sorry this time there weren\'t enough open slots for "%s" and your application had to be denied. You have been moved to the waiting list.', 'vca-asm' ), $event_name )
				),
				'reg_revoked'	=>	array(
					'subject'	=>	__( 'Registration revoked.', 'vca-asm' ),
					'message'	=>	sprintf( __( 'Your registration to "%s" has been revoked.', 'vca-asm' ), $event_name )
				),
				'mem_accepted'	=>	array(
					'subject'	=>	__( 'Membership confirmed.', 'vca-asm' ),
					'message'	=>	sprintf( __( 'Your membership to "%s" has been confirmed.', 'vca-asm' ), $event_name )
				),
				'mem_denied'	=>	array(
					'subject'	=>	__( 'Membership denied.', 'vca-asm' ),
					'message'	=>	sprintf( __( 'Your membership to "%s" has been denied.', 'vca-asm' ), $event_name )
				),
				'mem_cancelled'	=>	array(
					'subject'	=>	__( 'Membership cancelled.', 'vca-asm' ),
					'message'	=>	sprintf( __( 'Your membership to "%s" has been cencelled, either by yourself or a Head Of', 'vca-asm' ), $event_name )
				)
			);
			
			if( ! empty( $options['subject'] ) ) {
				$subject = sprintf( $options['subject'], $event_name );
			} else {
				$subject = '[Viva con Agua] ' . $default_responses[$action]['subject'];
			}
			
			if( ! empty( $options['message'] ) ) {
				$message = sprintf( $options['message'], $event_name );
			} else {
				$message = $default_responses[$action]['message'];
			}
			
			$this->send( $to, $subject, $message );
			
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
	public function send( $receipient, $subject, $message_pre, $from_name = NULL, $from_email = NULL, $content_type = NULL ) {
		$message = wordwrap($message_pre, 70);
		
		if( ! is_array( $receipient ) ) {
			$receipient = array( $receipient );
		}
		
		$headers = "From: ";
		if( $from_name == NULL ) {
			$headers .= "Viva con Agua ";
		} else {
			$headers .= $from_name . " ";
		}
		if( $from_email == NULL ) {
			$headers .= "<no-reply@vivaconagua.org>" . "\r\n";
			$headers .= "X-Sender: <no-reply@vivaconagua.org>" . "\r\n";
		} else {
			$headers .= "<" . $from_email . ">\r\n";
			$headers .= "X-Sender: <" . $from_email . ">" . "\r\n";
		}
		$headers .= "X-Mailer: PHP" . "\r\n";
		$headers .= "X-Priority: 1" . "\r\n";
		$headers .= "Mime-Version: 1.0" . "\r\n";
		if( $content_type == 'html' ) {
			$headers .= "Content-Type: text/plain; charset=UTF-8" . "\r\n"; // change to html
		} else {
			$headers .= "Content-Type: text/plain; charset=UTF-8" . "\r\n";
		}
		
		foreach( $receipient as $to ) {
			wp_mail( $to, $subject, $message, $headers );
		}
	}
	
} // class

endif; // class exists

?>
