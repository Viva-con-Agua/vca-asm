<?php

/**
 * VCA_ASM_List_Activities class.
 * This class contains properties and methods for the listing (frontend) of events.
 *
 * Two wordpress shortcodes are defined here: One to list all events currently in the application phase
 * and one to list all events a user/supporter is registered for.
 *
 * @see VcA_ASM_Registrations
 *
 * @package VcA Activity & Supporter Management
 * @since 1.0
 *
 * Structure:
 * - Constructor
 * - Application Request Handler
 * - Shortcode Handlers
 * - Utility
 */

if ( ! class_exists( 'VCA_ASM_List_Activities' ) ) :

class VCA_ASM_List_Activities
{

	/* ============================= CONSTRUCTOR ============================= */

	/**
	 * Constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
		$this->handle_applications();
	}

	/* ============================= APPLICATION REQUEST HANDLER ============================= */

	/**
	 * Checks for application requests before adding listing shortcodes
	 *
	 * Calls neccessary application method, if applicable
	 *
	 * @global object $vca_asm_registration
	 * @global object $vca_asm_utilities
	 *
	 * @see constructor
	 *
	 * @since 1.0
	 * @access private
	 */
	private function handle_applications() {
        /** @var vca_asm_utilities $vca_asm_utilities */
        /** @var vca_asm_registrations $vca_asm_registrations */
		global $vca_asm_registrations, $vca_asm_utilities;

		if ( ! is_admin() ) {
			if ( isset( $_POST['todo'] ) && $_POST['todo'] == 'apply' && isset( $_POST['activity'] ) && is_numeric( $_POST['activity'] ) ) {

				/* Avoid form resubmission after page refresh */
				if ( ! $vca_asm_utilities->session_is_active() ) {
					session_start();
				}
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

				if( 'If you wish to send' === mb_substr( $_POST['notes'], 0, 19 ) ||
				   'Wenn du eine Nachri' === mb_substr( $_POST['notes'], 0, 19 ) ) {
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

			add_shortcode( 'vca-asm-list-activities', array( $this, 'list_activities' ) );
			add_shortcode( 'vca-asm-my-activities', array( $this, 'my_activities' ) );
		}
	}

	/* ============================= SHORTCODE HANDLERS ============================= */

	/**
	 * Returns the list of all activities with open applications
	 *
	 * @param array $atts		(optional) shortcode attributes
	 * @return string $output	formatted HTML output
	 *
	 * @see handle_applications
	 *
	 * @global object $vca_asm_activities
	 * @global object $vca_asm_registration
	 */
	public function list_activities( $atts = array() )
	{
        /** @var vca_asm_activities $vca_asm_activities */
        /** @var vca_asm_registrations $vca_asm_registrations */
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
			'class' => '',
			'filter' => '',
			'heading' => 1
		), $atts ) );

		$exclude = $vca_asm_registrations->get_supporter_all();

		if ( isset( $_GET['dir'] ) && isset( $_GET['sort'] ) && 'date' === $_GET['sort'] ) {
			$order = $_GET['dir'];
		} else {
			$order = 'ASC';
		}

		$args = array(
			'posts_per_page' 	=>	-1,
			'post_type'         =>	$vca_asm_activities->activity_types,
			'post_status'       =>	'publish',
			'post__not_in'		=>	$exclude,
			'meta_key'			=>	'start_act',
			'orderby'           =>	'meta_value_num',
			'order'             =>	$order,
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

		if ($filter == 'camps') {
		    $args['post_type'] = array('nwgathering');
		    $args['meta_query'] = array(
                array(
                    'key' => 'end_app',
                    'value' => time(),
                    'compare' => '>=',
                    'type' => 'numeric'
                )
            );
		    unset($args['post__not_in']);
        }

		$activities = new WP_Query( $args );

		$output = '';

		if( ! empty( $activities->posts ) ) {

			wp_enqueue_script( 'isotope-metafizzy' );
			wp_enqueue_script( 'vca-asm-activities' );

			wp_enqueue_style( 'vca-asm-activities-style' );
			wp_enqueue_style( 'vca-asm-isotope-style' );


            if (empty($filter)) {
                $pre_text = sprintf(__('Before you apply for an activity, please make sure you will indeed have spare time on your hands in the given timeframe. For general infos about festivals and VcA, please refer to the %s.', 'vca-asm'), $faq_link) . '<br />' . __('The following activities are currently in the application phase:', 'vca-asm');
            } else {
                $pre_text = __( 'Here you can see all upcoming camps and network gatherings.', 'vca-asm' );
            }

			$template = new VCA_ASM_Frontend_Activities(
				$activities,
				array(
					'action' => 'app',
					'container_class' => 'activities-open',
					'with_filter' => empty($filter),
					'eligibility_check' => true,
					'heading' => ! empty( $heading ) ? __( 'Current Activities', 'vca-asm' ) : '',
					'pre_text' => $pre_text
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
	 * @param array $atts		(optional) shortcode attributes
	 * @return string $output	formatted HTML output
	 *
	 * @global object $vca_asm_activities
	 * @global object $vca_asm_registrations
	 *
	 * @see handle_applications
	 *
	 * @since 1.0
	 * @access public
	 */
	public function my_activities( $atts = array() )
	{
        /** @var vca_asm_activities $vca_asm_activities */
        /** @var vca_asm_registrations $vca_asm_registrations */
		global $vca_asm_activities, $vca_asm_registrations;

		wp_enqueue_style( 'vca-asm-activities-style' );

		extract( shortcode_atts( array(
			'class' => '',
			'heading' => 1
		), $atts ) );

		$registrations = $vca_asm_registrations->get_supporter_registrations();
		$applications = $vca_asm_registrations->get_supporter_applications();
		$waiting = $vca_asm_registrations->get_supporter_waiting();
		$registrations_old = $vca_asm_registrations->get_supporter_registrations_old();

		$output = '';

		if ( ! empty( $heading ) ) {
			$output .= '<div class="break-heading first"><div class="grid-block"><h2>' . __( 'My Activities', 'vca-asm' ) . '</h2></div></div>';
		}
		$output .= '<section id="section_regs"><p class="pointer">&#9654;</p><h5><a href="#section_regs">' .
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
					'container_class' => 'activities-registrations',
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
					'container_class' => 'activities-applications',
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
					'container_class' => 'activities-waiting',
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
					'container_class' => 'activities-registrations-old',
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

	/* ============================= UTILITY METHODS ============================= */

	/**
	 * Verifies whether a supporter's profile contains enough information to register for events
	 *
	 * @return bool|string 		true if profile is complete enough to apply for activities, message string if not
	 *
	 * @global object $current_user
	 *
	 * @since 1.0
	 * @access public
	 */
	public function verify_profile()
	{
		global $current_user;

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
			return __( 'In order to be able to register for activities, you should at least have filled out first- and lastname, your mobile phone and date of birth, as well as selected a country in your user profile.', 'vca-asm' );
		}
	}

} // class

endif; // class exists

?>