<?php

/**
 * VCA_ASM_Lists class.
 * This class contains properties and methods for the listing (frontend) of events.
 *
 * Two wordpress shortcodes are defined here: One to list all events currently in the application phase
 * and one to list all events a user/supporter is registered for.
 * 
 * @package VcA Activity & Supporter Management
 * @see VcA_ASM_Registrations
 * @since 1.0
 */

if ( ! class_exists( 'VCA_ASM_Lists' ) ) :

class VCA_ASM_Lists {

	/**
	 * Verifies whether a supporter's profile contains
	 * enough information to register for events
	 *
	 * @since 1.0
	 * @access public
	 */	
	public function verify_profile() {
		global $current_user;
	    get_currentuserinfo();
		
		$birthday = get_user_meta( $current_user->ID, 'birthday', true );
		$city = get_user_meta( $current_user->ID, 'city', true );
		$gender = get_user_meta( $current_user->ID, 'gender', true );
		$mobile = get_user_meta( $current_user->ID, 'mobile', true );
		
		if( ! empty( $current_user->user_firstname ) &&
			! empty( $current_user->user_lastname ) &&
			! empty( $mobile ) &&
			$birthday !== '' &&
			! empty( $city ) &&
			! empty( $gender ) ) {
			return true;
		} else {
			return __( "In order to be able to register for any festivals, you should at least have filled out first- and lastname as well as your, location, mobile phone, gender and birthday in your user profile.", 'vca-asm' );
		}
	}

	/**
	 * Returns the list of all activities with open applications
	 *
	 * @since 1.0
	 * @access public
	 */	
	public function list_activities( $atts ) {
		global $vca_asm_registrations;

		$faq_link = get_bloginfo( 'url' ) . '/faq';
		
		if( $this->verify_profile() !== true ) {
			$output = '<p class="error">' . $this->verify_profile() . '</p>' .
				'<p class="error">' .
					sprintf( __( 'Why this is a neccessity is explained in the <a href="%s" title="Read the FAQs">FAQ</a>.', 'vca-asm' ), $faq_link );
				'</p>';
			return $output;
		}
		
		extract( shortcode_atts( array(
			'class' => ''
		), $atts ) );
		
		$exclude = $vca_asm_registrations->get_supporter_all();
		
		$output = '<p class="message">' .
				sprintf( __( 'Before you apply for a festival, please make sure you will indeed have spare time on your hands in the given timeframe. For general infos about festivals and VcA, please refer to the <a href="%s" title="Read the FAQs">FAQ</a>.', 'vca-asm' ), $faq_link ) .
				'</p>';
		
		$args = array(
			'posts_per_page' 	=>	-1,
			'post_type'         =>	'festival',
			'post_status'       =>	'publish',
			'post__not_in'		=>	$exclude,
			'meta_key'			=>	'start_date',
			'orderby'           =>	'meta_value_num',
			'order'             =>	'ASC',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'start_app',
					'value' => time(),
					'compare' => '<=',
					'type' => 'numeric'
				),
				array(
					'key' => 'end_app',
					'value' => time(),
					'compare' => '>=',
					'type' => 'numeric'
				)
			)		
		);
		
		$activities = new WP_Query( $args );
		
		$list_class = 'activities activities-open';
		$show_app = true;
		$split_months = true;
		
		if( ! empty( $activities->posts ) ) {
			require( VCA_ASM_ABSPATH . '/templates/frontend-activities.php' );
			$output .= '<p class="message">' .
				__( "Of course that's not the entire summer, further festivals will follow soon...", 'vca-asm' ) .
				'</p>';
		} else {
			$output = '<p class="message">' .
				__( 'Currently there are no festivals in the registration phase', 'vca-asm' ) .
				'</p>';
		}

		/* reset the post data, required when using the WP_Query class */
		wp_reset_postdata();
		
		return $output;
	}

	/**
	 * Returns the list of all activities a user is registered to
	 *
	 * @since 1.0
	 * @access public
	 */	
	public function my_activities( $atts ) {
		global $vca_asm_registrations;
		
		extract( shortcode_atts( array(
			'class' => ''
		), $atts ) );
		
		$registrations = $vca_asm_registrations->get_supporter_registrations();
		$applications = $vca_asm_registrations->get_supporter_applications();
		$waiting = $vca_asm_registrations->get_supporter_waiting();
		$registrations_old = $vca_asm_registrations->get_supporter_registrations_old();
		
		$output = '';
		
		if( ! empty( $registrations ) ) {
			
			$output .= '<p class="message">' .
				__( 'These are the festivals you are participating in:', 'vca-asm' ) .
				'</p>';
			
			$show_xtra_info = true;
			
			$args = array(
				'posts_per_page' 	=>	-1,
				'post_type'         =>	'festival',
				'post_status'       =>	'publish',
				'post__in'			=>	$registrations,
				'meta_key'			=>	'end_date',
				'orderby'           =>	'meta_value_num',
				'order'             =>	'ASC'
				
			);
			
			$activities = new WP_Query( $args );
			$list_class = 'activities activities-registrations';
			
			require( VCA_ASM_ABSPATH . '/templates/frontend-activities.php' );
			
			$show_xtra_info = false;
		
		} else {
			
			$output .= '<p class="message">' .
				__( 'You are currently not registered for future festivals.', 'vca-asm' ) .
				'</p>';
			
		}
		
		if( ! empty( $applications ) ) {
			
			$show_rev_app = true;
			
			$output .= '<h2 class="underline h2-margin">' . __( 'Current Applications', 'vca-asm' ) . '</h2>' .
				'<p class="message">' .
				__( 'You have applied to participate in the following festivals. You will get an answer at the latest one day after the application deadline has passed.', 'vca-asm' ) .
				'</p>';
			
			$args = array(
				'posts_per_page' 	=>	-1,
				'post_type'         =>	'festival',
				'post_status'       =>	'publish',
				'post__in'			=>	$applications,
				'meta_key'			=>	'end_date',
				'orderby'           =>	'meta_value_num',
				'order'             =>	'ASC'
				
			);
			
			$activities = new WP_Query( $args );
			$list_class = 'activities activities-applications';
		
			require( VCA_ASM_ABSPATH . '/templates/frontend-activities.php' );
			
			$show_rev_app = false;
		}
		
		if( ! empty( $waiting ) ) {
			
			$output .= '<h2 class="underline h2-margin">' . __( 'Waiting List', 'vca-asm' ) . '</h2>' .
				'<p class="message">' .
				__( 'Your application to these festivals was denied. You are now on the waiting list and will be contacted, if slots open up again.', 'vca-asm' ) .
				'</p>';
			
			$args = array(
				'posts_per_page' 	=>	-1,
				'post_type'         =>	'festival',
				'post_status'       =>	'publish',
				'post__in'			=>	$waiting,
				'meta_key'			=>	'end_date',
				'orderby'           =>	'meta_value_num',
				'order'             =>	'ASC'
				
			);
			
			$activities = new WP_Query( $args );
			$list_class = 'activities activities-waiting';
		
			require( VCA_ASM_ABSPATH . '/templates/frontend-activities.php' );
		
		}
		
		if( ! empty( $registrations_old ) ) {
			
			$output .= '<h2 class="underline h2-margin">' . __( 'Past Festivals', 'vca-asm' ) . '</h2>' .
				'<p class="message">' .
				__( 'These are the festivals you have participated in in the past.', 'vca-asm' ) .
				'</p>';
			
			$args = array(
				'posts_per_page' 	=>	-1,
				'post_type'         =>	'festival',
				'post_status'       =>	'publish',
				'post__in'			=>	$registrations_old,
				'meta_key'			=>	'end_date',
				'orderby'           =>	'meta_value_num',
				'order'             =>	'DSC'
				
			);
			
			$activities = new WP_Query( $args );
			$list_class = 'activities activities-registrations-old';
		
			require( VCA_ASM_ABSPATH . '/templates/frontend-activities-past.php' );
		
		}
		
		return $output;
	}

	/**
	 * Checks for application requests before adding listing shortcodes
	 *
	 * Calls neccessary application method, if applicable
	 *
	 * @todo add temporary hash to avoid application via URL manipulation
	 *
	 * @since 1.0
	 * @access private
	 */
	private function handle_applications() {
		global $vca_asm_registrations;
		
		if( isset( $_POST['todo'] ) && $_POST['todo'] == 'apply' && isset( $_POST['activity'] ) ) {
			
			/* Avoid form resubmission after page refresh */
			session_start();
			if( isset( $_POST['unique_id'] ) ) {
				$unique_id = $_POST['unique_id'];
				$allow_submission = isset( $_SESSION['allow_submission'] ) ? $_SESSION['allow_submission'] : array();
				if(isset($allow_submission[$unique_id])){
					unset($_POST['submit_form']);
					session_destroy();
					header('Location: ' . $_SERVER['HTTP_REFERER']);
				} else{
					$allow_submission[$unique_id] = TRUE;
					$_SESSION['allow_submission'] = $allow_submission;
				}
			}
			
			if( 'If you wish to send a message with your application' === substr( $_POST['notes'], 0, 51) ||
			   'Wenn du eine Nachricht mit deiner Bewerbung schicken willst' === substr( $_POST['notes'], 0, 59 ) ) {
				$notes = '';
			} else {
				$notes = $_POST['notes'];
			}
			
			if( isset( $_POST['submit_form'] ) ) {
				$vca_asm_registrations->set_application( $_POST['activity'], $notes );
			}
		}
		
		if( isset( $_POST['todo'] ) && $_POST['todo'] == 'revoke_app' && isset( $_POST['activity'] ) ) {
			$vca_asm_registrations->revoke_application( $_POST['activity'] );
		}
		
		add_shortcode( 'vca-asm-list-activities', array( &$this, 'list_activities' ) );
		add_shortcode( 'vca-asm-my-activities', array( &$this, 'my_activities' ) );
	}

	/**
	 * PHP4 style constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function VcA_ASM_Lists() {
		$this->__construct();
	}

	/**
	 * PHP5 style constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
		$this->handle_applications();
	}
	
} // class

endif; // class exists

?>