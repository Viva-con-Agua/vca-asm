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
		$nation = get_user_meta( $current_user->ID, 'nation', true );
		$mobile = get_user_meta( $current_user->ID, 'mobile', true );

		if (
			! empty( $current_user->user_firstname ) &&
			! empty( $current_user->user_lastname ) &&
			! empty( $mobile ) &&
			$birthday !== '' &&
			( ! empty( $nation ) || 0 === $nation || '0' === $nation )
		) {
			return true;
		} else {
			return __( "In order to be able to register for activities, you should at least have filled out first- and lastname, your mobile phone and date of birth, as well as selected a country in your user profile.", 'vca-asm' );
		}
	}

	/**
	 * Returns the list of all activities with open applications
	 *
	 * @since 1.0
	 * @access public
	 */
	public function list_activities( $atts ) {
		global $vca_asm_activities, $vca_asm_registrations;

		$faq_link = '<a href="' . get_bloginfo( 'url' ) . '/faq" title="' . __( 'Read the FAQ', 'vca-asm' ) . '">' . __( 'FAQ', 'vca-asm' ) . '</a>';

		if( $this->verify_profile() !== true ) {
			$output = '<div class="system-error"><p>' . $this->verify_profile() . '</p>' .
				'<p>' .
					sprintf( __( 'Why this is a neccessity is explained in the %s.', 'vca-asm' ), $faq_link ) .
				'</p></div>';
			return $output;
		}

		extract( shortcode_atts( array(
			'class' => ''
		), $atts ) );

		$exclude = $vca_asm_registrations->get_supporter_all();

		$args = array(
			'posts_per_page' 	=>	-1,
			'post_type'         =>	$vca_asm_activities->activity_types,
			'post_status'       =>	'publish',
			'post__not_in'		=>	$exclude,
			'meta_key'			=>	'start_act',
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
					'value' => time() - 60*60*22,
					'compare' => '>=',
					'type' => 'numeric'
				)
			)
		);

		$activities = new WP_Query( $args );

		$output = '';

		if( ! empty( $activities->posts ) ) {

			$template = new VCA_ASM_Frontend_Activities(
				$activities,
				array(
					'action' => 'app',
					'list_class' => 'activities-open',
					'with_filter' => true,
					'eligibility_check' => true,
					'pre_text' => sprintf( __( 'Before you apply for an activity, please make sure you will indeed have spare time on your hands in the given timeframe. For general infos about festivals and VcA, please refer to the %s.', 'vca-asm' ), $faq_link )
				)
			);

			$output .= $template->output();

		} else {
			$output = '<p>' .
				__( 'Currently there are no activities in the registration phase.', 'vca-asm' ) .
				'</p>';
		}

		return $output;
	}

	/**
	 * Returns the list of all activities a user is registered to
	 *
	 * @since 1.0
	 * @access public
	 */
	public function my_activities( $atts ) {
		global $vca_asm_activities, $vca_asm_registrations;

		extract( shortcode_atts( array(
			'class' => ''
		), $atts ) );

		$registrations = $vca_asm_registrations->get_supporter_registrations();
		$applications = $vca_asm_registrations->get_supporter_applications();
		$waiting = $vca_asm_registrations->get_supporter_waiting();
		$registrations_old = $vca_asm_registrations->get_supporter_registrations_old();

		$output = '<section id="section_regs"><p class="pointer">&#9654;</p><h5><a href="#section_regs">' .
				__( 'Activities you are participating in', 'vca-asm' ) . ' <span class="thin">(' . count( $registrations ) . ')</span>' .
			'</a></h5><div class="acc-body"><div class="measuring-wrapper">';

		if( ! empty( $registrations ) ) {

			$output .= '<p>' .
					__( 'These are the future activities you are participating in.', 'vca-asm' ) .
				'</p>';

			$args = array(
				'posts_per_page' 	=>	-1,
				'post_type'         =>	$vca_asm_activities->activity_types,
				'post_status'       =>	'publish',
				'post__in'			=>	$registrations,
				'meta_key'			=>	'end_act',
				'orderby'           =>	'meta_value_num',
				'order'             =>	'ASC'

			);

			$activities = new WP_Query( $args );

			$template = new VCA_ASM_Frontend_Activities(
				$activities,
				array(
					'list_class' => 'activities-registrations',
					'minimalistic' => true
				)
			);

			$output .= $template->output();

		} else {

			$output .= '<p>' .
				__( 'You are currently not registered for future activities.', 'vca-asm' ) .
				'</p>';

		}

		$output .= '</div></div></section>' .
			'<section id="section_apps"><p class="pointer">&#9654;</p><h5><a href="#section_apps">' .
					__( 'Current Applications', 'vca-asm' ) . ' <span class="thin">(' . count( $applications ) . ')</span>' .
				'</a></h5><div class="acc-body"><div class="measuring-wrapper">';

		if( ! empty( $applications ) ) {

			$output .= '<p>' .
					__( 'You have applied to participate in the following activities. You will get an answer at the latest one day after the application deadline has passed.', 'vca-asm' ) .
				'</p>';

			$args = array(
				'posts_per_page' 	=>	-1,
				'post_type'         =>	$vca_asm_activities->activity_types,
				'post_status'       =>	'publish',
				'post__in'			=>	$applications,
				'meta_key'			=>	'end_act',
				'orderby'           =>	'meta_value_num',
				'order'             =>	'ASC'

			);

			$activities = new WP_Query( $args );

			$template = new VCA_ASM_Frontend_Activities(
				$activities,
				array(
					'action' => 'rev_app',
					'list_class' => 'activities-applications',
					'minimalistic' => true
				)
			);

			$output .= $template->output();
		} else {

			$output .= '<p>' .
				__( 'You currently have not applied to any future activities.', 'vca-asm' ) .
				'</p>';

		}

		$output .= '</div></div></section>' .
			'<section id="section_waiting"><p class="pointer">&#9654;</p><h5><a href="#section_waiting">' .
					__( 'Waiting List', 'vca-asm' ) . ' <span class="thin">(' . count( $waiting ) . ')</span>' .
				'</a></h5><div class="acc-body"><div class="measuring-wrapper">';

		if( ! empty( $waiting ) ) {

			$output .= '<p>' .
					__( 'Your application to these activites was denied. You are now on the waiting list and will be contacted, if slots open up again.', 'vca-asm' ) .
				'</p>';

			$args = array(
				'posts_per_page' 	=>	-1,
				'post_type'         =>	$vca_asm_activities->activity_types,
				'post_status'       =>	'publish',
				'post__in'			=>	$waiting,
				'meta_key'			=>	'end_act',
				'orderby'           =>	'meta_value_num',
				'order'             =>	'ASC'

			);

			$activities = new WP_Query( $args );

			$template = new VCA_ASM_Frontend_Activities(
				$activities,
				array(
					'list_class' => 'activities-waiting',
					'minimalistic' => true
				)
			);

			$output .= $template->output();

		} else {

			$output .= '<p>' .
				__( 'You currently are not on any waiting lists.', 'vca-asm' ) .
				'</p>';

		}

		$output .= '</div></div></section>' .
			'<section id="section_past"><p class="pointer">&#9654;</p><h5><a href="#section_past">' .
					__( 'Past Activities', 'vca-asm' ) . ' <span class="thin">(' . count( $registrations_old ) . ')</span>' .
				'</a></h5><div class="acc-body"><div class="measuring-wrapper">';

		if( ! empty( $registrations_old ) ) {

			$output .= '<p>' .
					__( 'These are the activities you have participated in in the past.', 'vca-asm' ) .
				'</p>';

			$args = array(
				'posts_per_page' 	=>	-1,
				'post_type'         =>	$vca_asm_activities->activity_types,
				'post_status'       =>	'publish',
				'post__in'			=>	$registrations_old,
				'meta_key'			=>	'end_act',
				'orderby'           =>	'meta_value_num',
				'order'             =>	'DSC'

			);

			$activities = new WP_Query( $args );

			$template = new VCA_ASM_Frontend_Activities(
				$activities,
				array(
					'list_class' => 'activities-registrations-old',
					'minimalistic' => true
				)
			);

			$output .= $template->output();

		} else {

			$output .= '<p>' .
				__( 'So far, you have not participated in any activities.', 'vca-asm' ) .
				'</p>';

		}

		$output .= '</div></div></section>';

		if ( ! empty( $output ) ) {
			$output = '<div class="accordion activity-accordion">' . $output . '</div>';
		}

		return $output;
	}

	/**
	 * Checks for application requests before adding listing shortcodes
	 *
	 * Calls neccessary application method, if applicable
	 *
	 * @since 1.0
	 * @access private
	 */
	private function handle_applications() {
		global $vca_asm_registrations;

		if ( ! is_admin() ) {
			if ( isset( $_POST['todo'] ) && $_POST['todo'] == 'apply' && isset( $_POST['activity'] ) && is_numeric( $_POST['activity'] ) ) {

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

				if( 'If you wish to send a message with your application' === substr( $_POST['notes'], 0, 51 ) ||
				   'Wenn du eine Nachricht mit deiner Bewerbung schicken willst' === substr( $_POST['notes'], 0, 59 ) ) {
					$notes = '';
				} else {
					$notes = $_POST['notes'];
				}

				if( isset( $_POST['submit_form'] ) ) {
					$vca_asm_registrations->set_application( $_POST['activity'], $notes );
				}
			}

			if( isset( $_POST['todo'] ) && $_POST['todo'] == 'revoke_app' && isset( $_POST['activity'] ) && is_numeric( $_POST['activity'] ) ) {
				$vca_asm_registrations->revoke_application( $_POST['activity'] );
			}

			add_shortcode( 'vca-asm-list-activities', array( &$this, 'list_activities' ) );
			add_shortcode( 'vca-asm-my-activities', array( &$this, 'my_activities' ) );
		}
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