<?php

/**
 * VCA_ASM_Mail class.
 *
 * An instance of this class holds all information on a single (sent) e-mail
 *
 * @package VcA Activity & Supporter Management
 * @since 1.3.3
 *
 * Structure:
 * - Properties
 */

if ( ! class_exists( 'VCA_ASM_Mail' ) ) :

class VCA_ASM_Mail
{

	/* ============================= CLASS PROPERTIES ============================= */
	private $default_args = array(
		'minimalistic' => false
	);
	private $args = array();

	public $id = 0;
	public $ID = 0;

	public $exists = false;

	public $receipient_id = 0;
	public $receipient_type = 'region';
	public $receipient_display_name = '';
	public $receipient_membership = false;
	public $receipient_pref_override = false;

	public $sender_id = 0;
	public $sender_email = 'no-reply@vivaconagua.org';
	public $sender_first_name = '';
	public $sender_last_name = '';
	public $sender_display_name = 'Viva con Agua';

	public $time_stamp = 480068100;
	public $time_time = '';
	public $time_date = '';
	public $time_full = '';

	public $subject = 'Newsletter';
	public $message_body = 'Lipsum';

	/* ============================= CLASS PROPERTIES ============================= */

	/**
	 * Checks whether an activity of id exists
	 *
	 * @since 1.3
	 * @access public
	 */
	public function init( $id ) {
		global $wpdb;

		$mail_query = $wpdb->get_results(
			"SELECT * FROM " . $wpdb->prefix . "vca_asm_emails " .
			"WHERE id = " . $id . " LIMIT 1", ARRAY_A
		);

		if ( ! empty( $mail_query ) ) {
			$this->exists = true;

			$the_mail = $mail_query[0];

			$this->receipient_id = $the_mail['receipient_id'];
			$this->receipient_type = $the_mail['receipient_type'];
			$this->receipient_membership = $the_mail['membership'];
			$this->receipient_pref_override = $the_mail['pref_override'];

			$this->sender_id = $the_mail['sent_by'];
			$this->sender_email = $the_mail['from'];

			$this->time_stamp = $the_mail['from'];

			$this->subject = $the_mail['subject'];
			$this->message_body = $the_mail['message'];

			/* TMP */

			if ( 'region' === $this->receipient_type ) {
				global $vca_asm_geography;

				$new_type = 'region2';

				$wpdb->update(
					array( 'receipient_type' => $new_type ),
					array( 'id' => $the_mail['id'] ),
					array( '%s' ),
					array( '%d' )
				);
			}

			if ( empty( $the_mail['pref_override'] ) && 0 !==  $the_mail['pref_override'] && '0' !== $the_mail['pref_override'] ) {
				$wpdb->update(
					array( 'pref_override' => 0 ),
					array( 'id' => $the_mail['id'] ),
					array( '%d' ),
					array( '%d' )
				);
			}

			/* END TMP */

			$this->receipient_display_name = '';

			$this->sender_first_name = '';

			$this->sender_last_name = '';

			$this->sender_display_name = 'Viva con Agua';

			$this->time_time = '';

			$this->time_date = '';

			$this->time_full = '';
		}
	}

	/**
	 * PHP5 style constructor
	 *
	 * @since 1.3.3
	 * @access public
	 */
	public function __construct( $id, $args = array() ) {
		$this->args = wp_parse_args( $args, $this->default_args );
		$this->id = intval( $id );
		$this->ID = $this->id;
		$this->init( $this->id );
	}

} // class

endif; // class exists

?>