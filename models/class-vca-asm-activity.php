<?php

/**
 * VCA_ASM_Activity class
 *
 * Model
 * An instance of this class holds all information on a single activity
 *
 * Terminology
 * "Quota": Cumulative slots of a geographical unit
 * "Slots": Direct slots of a geographical unit
 *
 * Example
 * Germany has been allocated a quota of 15 tickets for supporters.
 * The event is a festival in northern Germany
 * and 4 tickets have been allocated to Bremen, 4 to Hamburg and 4 to Kiel.
 * 3 tickets are available to supporters from other cells.
 * Hence Germany's got a quota of 15, but only 3 slots.
 * The other slots are assigned to a city ID.
 *
 * @package VcA Activity & Supporter Management
 * @since 1.3
 *
 * Structure:
 * - Properties
 * - Constructor
 * - Property Population
 * - Reset
 * - Utility
 */

if ( ! class_exists( 'VCA_ASM_Activity' ) ) :

class VCA_ASM_Activity
{

	/* ============================= CLASS PROPERTIES ============================= */

	/**
	 * Default arguments used if not set and passed externally
	 *
	 * @var array $default_args
	 * @see constructor
	 * @since 1.5
	 * @access private
	 */
	private $default_args = array(
		'minimalistic' => false
	);

	/**
	 * Arguments passed to the object in the constructor
	 *
	 * @var array $args
	 * @see constructor
	 * @since 1.5
	 * @access private
	 */
	private $args = array();

	/**
	 * Holds the ID of the post (activity) in question
	 *
	 * @var int $id
	 * @see constructor
	 * @since 1.5
	 * @access public
	 */
	public $id = 0;

	/**
	 * Holds the ID of the post (activity) in question
	 *
	 * @var int $ID
	 * @see constructor
	 * @since 1.5
	 * @access public
	 */
	public $ID = 0;

	/**
	 * Whether a post of the passed ID exists
	 *
	 * @var bool $exists
	 * @see method exists
	 * @since 1.5
	 * @access public
	 */
	public $exists = false;

	/**
	 * Whether the post is of type "activity"
	 *
	 * @var bool $is_activity
	 * @see method is_activity
	 * @since 1.5
	 * @access public
	 */
	public $is_activity = false;

	/**
	 * The department the activity belongs to
	 *
	 * @var string $department
	 * @since 1.5
	 * @access public
	 */
	public $department = 'actions';

	/**
	 * WordPress post object
	 *
	 * @var object $post_object			WP_Post object
	 * @since 1.5
	 * @access public
	 */
	public $post_object = object;

	/**
	 * The name of the event / activity
	 *
	 * @var string $name
	 * @since 1.5
	 * @access public
	 */
	public $name = '';

	/**
	 * The activity's metadata
	 *
	 * @var array $meta
	 * @since 1.5
	 * @access public
	 */
	public $meta = array();

	/**
	 * The type of activity
	 *
	 * @var string $type
	 * @since 1.5
	 * @access public
	 */
	public $type = 'festival';

	/**
	 * The type of activity in translatable, human-readable form
	 *
	 * @var string $nice_type
	 * @since 1.5
	 * @access public
	 */
	public $nice_type = 'Festival';

	/**
	 * URL of the icon image representing the activity
	 *
	 * @var string $icon_url
	 * @since 1.5
	 * @access public
	 */
	public $icon_url = 'http://vivaconagua.org/wp-content/plugins/vca-asm/img/icon-festival_32.png';

	/**
	 * The ID of the nation the activity is associated with
	 *
	 * @var int $nation
	 * @since 1.5
	 * @access public
	 */
	public $nation = 0;

	/**
	 * The name of the nation the activity is associated with, in translatable, human-readable form
	 *
	 * @var string $nice_type
	 * @since 1.5
	 * @access public
	 */
	public $nation_name = '';

	/**
	 * The ID of the city the activity is associated with
	 *
	 * @var int $city
	 * @since 1.5
	 * @access public
	 */
	public $city = 0;

	/**
	 * The name of the city the activity is associated with, in translatable, human-readable form
	 *
	 * @var string $nice_type
	 * @since 1.5
	 * @access public
	 */
	public $city_name = '';

	/**
	 * Whether the activity has been delegated to a city account
	 *
	 * @var bool $delegation
	 * @since 1.5
	 * @access public
	 */
	public $delegation = false;

	/**
	 * Whether only supporters with "membership status" can apply
	 *
	 * @var bool $membership_required
	 * @since 1.5
	 * @access public
	 */
	public $membership_required = false;

	/**
	 * Timestamp of the start of the application phase
	 *
	 * @var int $start_app
	 * @since 1.5
	 * @access public
	 */
	public $start_app = 0;

	/**
	 * Timestamp of the end of the application phase
	 *
	 * @var int $end_app
	 * @since 1.5
	 * @access public
	 */
	public $end_app = 0;

	/**
	 * Timestamp of the start of the activity itself
	 *
	 * @var int $start_act
	 * @since 1.5
	 * @access public
	 */
	public $start_act = 0;

	/**
	 * Timestamp of the end of the activity itself
	 *
	 * @var int $end_act
	 * @since 1.5
	 * @access public
	 */
	public $end_act = 0;

	/**
	 * Whether the start of the activity lies in the future
	 *
	 * @var bool $upcoming
	 * @since 1.5
	 * @access public
	 */	public $upcoming = true;

	/**
	 * Holds all participants of the activity
	 *
	 * Supporters with accepted applications
	 *
	 * @var int[] $participants						array of user IDs
	 * @since 1.5
	 * @access public
	 */
	public $participants = array();

	/**
	 * Count of all participants of the activity
	 *
	 * Supporters with accepted applications
	 *
	 * @var int $participants_count
	 * @since 1.5
	 * @access public
	 */
	public $participants_count = 0;

	/**
	 * Holds all supporters on the waiting list for the activity
	 *
	 * Supporters with denied applications
	 *
	 * @var int[] $waiting							array of user IDs
	 * @since 1.5
	 * @access public
	 */
	public $waiting = array();

	/**
	 * Count of all supporters on the waiting list for the activity
	 *
	 * Supporters with denied applications
	 *
	 * @var int $waiting_count
	 * @since 1.5
	 * @access public
	 */
	public $waiting_count = 0;

	/**
	 * Holds all supporters currently applying for the activity
	 *
	 * Supporters with unadministered applications
	 *
	 * @var int[] $applicants						array of user IDs
	 * @since 1.5
	 * @access public
	 */
	public $applicants = array();

	/**
	 * Count of all supporters currently applying for the activity
	 *
	 * Supporters with unadministered applications
	 *
	 * @var int $applicants_count
	 * @since 1.5
	 * @access public
	 */
	public $applicants_count = 0;

	/**
	 * Holds all participants of the activity
	 *
	 * Supporters with accepted applications
	 * Slots (geographical IDs) as keys
	 *
	 * @var int[] $participants_by_slots			array of user IDs
	 * @since 1.5
	 * @access public
	 */
	public $participants_by_slots = array();

	/**
	 * Holds counts of participants of the activity
	 *
	 * Supporters with accepted applications
	 * Slots (geographical IDs) as keys
	 *
	 * @var int[] $participants_count_by_slots
	 * @since 1.5
	 * @access public
	 */
	public $participants_count_by_slots = array();

	/**
	 * Holds all supporters on the waiting list for the activity
	 *
	 * Supporters with denied applications
	 * Slots (geographical IDs) as keys
	 *
	 * @var int[] $waiting_by_slots					array of user IDs
	 * @since 1.5
	 * @access public
	 */
	public $waiting_by_slots = array();

	/**
	 * Holds counts of supporters on the waiting list for the activity
	 *
	 * Supporters with denied applications
	 * Slots (geographical IDs) as keys
	 *
	 * @var int[] $waiting_count_by_slots
	 * @since 1.5
	 * @access public
	 */
	public $waiting_count_by_slots = array();

	/**
	 * Holds all applicants for the activity
	 *
	 * Supporters with unadministered applications
	 * Slots (geographical IDs) as keys
	 *
	 * @var int[] $applicants_by_slots				array of user IDs
	 * @since 1.5
	 * @access public
	 */
	public $applicants_by_slots = array();

	/**
	 * Holds counts of all applicants for the activity
	 *
	 * Supporters with unadministered applications
	 * Slots (geographical IDs) as keys
	 *
	 * @var int[] $applicants_count_by_slots
	 * @since 1.5
	 * @access public
	 */
	public $applicants_count_by_slots = array();

	/**
	 * Holds all participants of the activity
	 *
	 * Supporters with accepted applications
	 * Quotas (geographical IDs, cumulative slots) as keys
	 *
	 * @var array $participants_by_quota
	 * @since 1.5
	 * @access public
	 */
	public $participants_by_quota = array( 0 => array() );

	/**
	 * Holds counts of participants of the activity
	 *
	 * Supporters with accepted applications
	 * Quotas (geographical IDs, cumulative slots) as keys
	 *
	 * @var int[] $participants_count_by_quota
	 * @since 1.5
	 * @access public
	 */
	public $participants_count_by_quota = array( 0 => 0 );

	/**
	 * Holds all supporters on the waiting list for the activity
	 *
	 * Supporters with denied applications
	 * Quotas (geographical IDs, cumulative slots) as keys
	 *
	 * @var int[] $waiting_by_quota					array of user IDs
	 * @since 1.5
	 * @access public
	 */
	public $waiting_by_quota = array( 0 => array() );

	/**
	 * Holds counts of supporters on the waiting list for the activity
	 *
	 * Supporters with denied applications
	 * Quotas (geographical IDs, cumulative slots) as keys
	 *
	 * @var int[] $waiting_count_by_quota
	 * @since 1.5
	 * @access public
	 */
	public $waiting_count_by_quota = array( 0 => 0 );

	/**
	 * Holds all applicants for the activity
	 *
	 * Supporters with unadministered applications
	 * Quotas (geographical IDs, cumulative slots) as keys
	 *
	 * @var int[] $applicants_by_quota				array of user IDs
	 * @since 1.5
	 * @access public
	 */
	public $applicants_by_quota = array( 0 => array() );

	/**
	 * Holds counts of all applicants for the activity
	 *
	 * Supporters with unadministered applications
	 * Quotas (geographical IDs, cumulative slots) as keys
	 *
	 * @var int[] $applicants_count_by_quota
	 * @since 1.5
	 * @access public
	 */
	public $applicants_count_by_quota = array( 0 => 0 );

	/**
	 *
	 *
	 * @var array $
	 * @since 1.5
	 * @access public
	 */
	public $minimum_quotas = array();
	public $non_global_participants = false;

	/**
	 *
	 *
	 * @var int $
	 * @since 1.5
	 * @access public
	 */
	public $total_slots = 0;

	/**
	 *
	 *
	 * @var int $
	 * @since 1.5
	 * @access public
	 */
	public $global_slots = 0;

	/**
	 *
	 *
	 * @var array $
	 * @since 1.5
	 * @access public
	 */
	public $ctr_quotas = array();

	/**
	 *
	 *
	 * @var array $
	 * @since 1.5
	 * @access public
	 */
	public $ctr_slots = array();

	/**
	 *
	 *
	 * @var string $
	 * @since 1.5
	 * @access public
	 */
	public $ctr_quotas_switch = 'nay';

	/**
	 *
	 *
	 * @var array $
	 * @since 1.5
	 * @access public
	 */
	public $cty_slots = array();

	/**
	 *
	 *
	 * @var array $
	 * @since 1.5
	 * @access public
	 */
	public $ctr_cty_switch = array();

	/**
	 *
	 *
	 * @var array $
	 * @since 1.5
	 * @access public
	 */
	public $slots = array();

	/* ============================= CONSTRUCTOR ============================= */

	/**
	 * Constructor
	 *
	 * @param int $id						(post-)ID of the activity post type
	 * @param array $args					(optional) array of arguments, see property $default_args
	 *
	 * @since 1.3
	 * @access public
	 */
	public function __construct( $id, $args = array() )
	{
		$this->args = wp_parse_args( $args, $this->default_args );
		$this->id = intval( $id );
		$this->ID = $this->id;
		$this->is_activity( $this->id );
	}

	/* ============================= POPULATE PROPERTIES ============================= */

	/**
	 * Assigns values to class properties
	 *
	 * @param int $id						(post-)ID of the activity post type
	 * @return void
	 *
	 * @global object $wpdb
	 * @global object $vca_asm_activities
	 * @global object $vca_asm_geography
	 * @global object $vca_asm_registrations
	 * @global object $vca_asm_utilities
	 *
	 * @since 1.3
	 * @access public
	 */
	public function gather_meta( $id )
	{
		global $wpdb,
			$vca_asm_activities, $vca_asm_geography, $vca_asm_registrations, $vca_asm_utilities;

		$this->post_object = get_post( $id );
		$this->name = $this->post_object->post_title;
		$this->meta = get_post_meta( $id );

		$this->type = $this->post_object->post_type;
		if ( 'concert' === $this->type ) {
			$this->nice_type = __( 'Concert', 'vca-asm' );
			$this->icon_url = VCA_ASM_RELPATH . 'img/icon-concert_32.png';
		} elseif ( 'festival' === $this->type ) {
			$this->nice_type = __( 'Festival', 'vca-asm' );
			$this->icon_url = VCA_ASM_RELPATH . 'img/icon-festival_32.png';
		} elseif ( 'nwgathering' === $this->type ) {
			$this->nice_type = __( 'Network Gathering', 'vca-asm' );
			$this->icon_url = VCA_ASM_RELPATH . 'img/icon-network_32.png';
		} elseif ( 'miscactions' === $this->type ) {
			$this->nice_type = __( 'Miscellaneous', 'vca-asm' );
			$this->icon_url = VCA_ASM_RELPATH . 'img/icon-miscaction_32.png';
		} elseif ( 'goldeimerfestival' === $this->type ) {
			$this->nice_type = __( 'Goldeimer Compost-Toilets @ Festivals', 'vca-asm' );
			$this->icon_url = VCA_ASM_RELPATH . 'img/icon-goldeimer_32.png';
		}

		$this->membership_required = ( 1 == get_post_meta( $id, 'membership_required', true ) ) ? true : false;

		$this->department = $vca_asm_activities->departments_by_activity[$this->post_object->post_type] ?
			$vca_asm_activities->departments_by_activity[$this->post_object->post_type] :
			'actions';

		$this->nation = get_post_meta( $id, 'nation', true );
		$this->nation_name = $this->nation > 0 ? $vca_asm_geography->get_name( $this->nation ) : '';
		$this->city = get_post_meta( $id, 'city', true );
		$this->city_name = $this->city > 0 ? $vca_asm_geography->get_name( $this->city ) : '';
		$this->delegation = get_post_meta( $id, 'delegate', true );

		$this->total_slots = get_post_meta( $id, 'total_slots', true );
		$this->global_slots = get_post_meta( $id, 'global_slots', true );
		$this->ctr_quotas_switch = get_post_meta( $id, 'ctr_quotas_switch', true );
		$this->ctr_quotas = get_post_meta( $id, 'ctr_quotas', true );
		$this->ctr_quotas = empty( $this->ctr_quotas ) ? array() : $this->ctr_quotas;
		$this->ctr_slots = get_post_meta( $id, 'ctr_slots', true );
		$this->ctr_slots = empty( $this->ctr_slots ) ? array() : $this->ctr_slots;
		$this->ctr_cty_switch = get_post_meta( $id, 'ctr_cty_switch', true );
		$this->ctr_cty_switch = empty( $this->ctr_cty_switch ) ? array() : $this->ctr_cty_switch;
		$this->cty_slots = get_post_meta( $id, 'cty_slots', true );
		$this->cty_slots = empty( $this->cty_slots ) ? array() : $this->cty_slots;
		$this->slots = array_merge( array( 0 => $this->global_slots ), $this->ctr_slots, $this->cty_slots );

		$this->start_app = get_post_meta( $id, 'start_app', true );
		$this->end_app = get_post_meta( $id, 'end_app', true );
		$this->start_act = get_post_meta( $id, 'start_act', true );
		$this->end_act = get_post_meta( $id, 'end_act', true );

		if ( time() > $this->end_act ) {
			$this->upcoming = false;
		}

		if ( true === $this->args['minimalistic'] ) {
			return;
		}

		if ( $this->upcoming ) {

			$this->participants_by_slots = $vca_asm_registrations->get_activity_participants( $id, array( 'by_contingent' => true ) );
			$this->waiting = $vca_asm_registrations->get_activity_waiting( $id );
			$this->applicants = $vca_asm_registrations->get_activity_applications( $id );

			foreach ( $this->participants_by_slots as $geo_id => $participants_bs ) {
				if ( $geo_id !== 0 && ! $this->non_global_participants && ! empty( $participants_bs ) ) {
					$this->non_global_participants = true;
				}
				if ( ! array_key_exists( $geo_id, $this->participants_count_by_slots ) ) {
					$this->participants_count_by_slots[$geo_id] = 0;
				}
				if ( ! array_key_exists( $geo_id, $this->participants_by_quota ) ) {
					$this->participants_by_quota[$geo_id] = array();
				}
				if ( ! array_key_exists( $geo_id, $this->participants_count_by_quota ) ) {
					$this->participants_count_by_quota[$geo_id] = 0;
				}
				foreach ( $participants_bs as $participant ) {
					$this->participants[] = $participant;
					$this->participants_count++;
					$this->participants_count_by_slots[$geo_id]++;
					$this->participants_by_quota[$geo_id][] = $participant;
					$this->participants_count_by_quota[$geo_id]++;
					if ( $geo_id != 0 ) {
						$this->participants_by_quota[0][] = $participant;
						$this->participants_count_by_quota[0]++;
					}
					if ( $vca_asm_geography->is_city( $geo_id ) ) {
						$nation_query = $vca_asm_geography->get_ancestors( $geo_id, array(
							'data' => 'id',
							'format' => 'array',
							'type' => 'nation'
						));
						$nation = $nation_query[0];
						if ( ! array_key_exists( $nation, $this->participants_by_quota ) ) {
							$this->participants_by_quota[$nation] = array();
						}
						if ( ! array_key_exists( $nation, $this->participants_count_by_quota ) ) {
							$this->participants_count_by_quota[$nation] = 0;
						}
						$this->participants_by_quota[$nation][] = $participant;
						$this->participants_count_by_quota[$nation]++;
					}
				}
			}
			$this->minimum_quotas =& $this->participants_count_by_quota;

			foreach ( $this->waiting as $waiter /* LOL */ ) {
				$city_id = get_user_meta( $waiter, 'city', true );
				$nation_id = get_user_meta( $waiter, 'nation', true );
				$nation_id = ! empty( $nation_id ) ? $nation_id : ( $vca_asm_geography->has_nation( $city_id ) ? $vca_asm_geography->has_nation( $city_id ) : 'not_existent' );
				$nation_id = ( is_string( $nation_id ) || is_int( $nation_id ) || is_array( $nation_id ) ) ? $nation_id : 0;
				$level = 0;

				if ( $city_id && array_key_exists( $city_id, $this->cty_slots ) ) {
					$level = $city_id;
					if ( ! array_key_exists( $city_id, $this->waiting_by_slots ) ) {
						$this->waiting_by_slots[$city_id] = array();
					}
					if ( ! array_key_exists( $city_id, $this->waiting_count_by_slots ) ) {
						$this->waiting_count_by_slots[$city_id] = 0;
					}
					if ( ! array_key_exists( $city_id, $this->waiting_by_quota ) ) {
						$this->waiting_by_quota[$city_id] = array();
					}
					if ( ! array_key_exists( $city_id, $this->waiting_count_by_quota ) ) {
						$this->waiting_count_by_quota[$city_id] = 0;
					}
					if ( ! array_key_exists( $nation_id, $this->waiting_by_quota ) ) {
						$this->waiting_by_quota[$nation_id] = array();
					}
					if ( ! array_key_exists( $nation_id, $this->waiting_count_by_quota ) ) {
						$this->waiting_count_by_quota[$nation_id] = 0;
					}
				} elseif ( $nation_id && array_key_exists( $nation_id, $this->ctr_slots ) ) {
					$level = $nation_id;
					if ( ! array_key_exists( $nation_id, $this->waiting_by_slots ) ) {
						$this->waiting_by_slots[$nation_id] = array();
					}
					if ( ! array_key_exists( $nation_id, $this->waiting_count_by_slots ) ) {
						$this->waiting_count_by_slots[$nation_id] = 0;
					}
					if ( ! array_key_exists( $nation_id, $this->waiting_by_quota ) ) {
						$this->waiting_by_quota[$nation_id] = array();
					}
					if ( ! array_key_exists( $nation_id, $this->waiting_count_by_quota ) ) {
						$this->waiting_count_by_quota[$nation_id] = 0;
					}
				} else {
					$level = 0;
					if ( ! array_key_exists( 0, $this->waiting_by_slots ) ) {
						$this->waiting_by_slots[0] = array();
					}
					if ( ! array_key_exists( 0, $this->waiting_count_by_slots ) ) {
						$this->waiting_count_by_slots[0] = 0;
					}
				}

				$this->waiting_count++;
				$this->waiting_by_slots[$level][] = $waiter;
				$this->waiting_count_by_slots[$level]++;
				$this->waiting_by_quota[$level][] = $waiter;
				$this->waiting_count_by_quota[$level]++;
				if ( $level === $city_id ) {
					$this->waiting_by_quota[$nation_id][] = $waiter;
					$this->waiting_count_by_quota[$nation_id]++;
				}
				if ( in_array( $level, array( $city_id, $nation_id ) ) ) {
					$this->waiting_by_quota[0][] = $waiter;
					$this->waiting_count_by_quota[0]++;
				}
			}

			foreach ( $this->applicants as $applicant ) {
				$city_id = get_user_meta( $applicant, 'city', true );
				$nation_id = get_user_meta( $applicant, 'nation', true );
				$nation_id = ! empty( $nation_id ) ? $nation_id : ( $vca_asm_geography->has_nation( $city_id ) ? $vca_asm_geography->has_nation( $city_id ) : 'not_existent' );
				$level = 0;

				if ( $city_id && array_key_exists( $city_id, $this->cty_slots ) ) {
					$level = $city_id;
					if ( ! array_key_exists( $city_id, $this->applicants_by_slots ) ) {
						$this->applicants_by_slots[$city_id] = array();
					}
					if ( ! array_key_exists( $city_id, $this->applicants_count_by_slots ) ) {
						$this->applicants_count_by_slots[$city_id] = 0;
					}
					if ( ! array_key_exists( $city_id, $this->applicants_by_quota ) ) {
						$this->applicants_by_quota[$city_id] = array();
					}
					if ( ! array_key_exists( $city_id, $this->applicants_count_by_quota ) ) {
						$this->applicants_count_by_quota[$city_id] = 0;
					}
					if ( ! array_key_exists( $nation_id, $this->applicants_by_quota ) ) {
						$this->applicants_by_quota[$nation_id] = array();
					}
					if ( ! array_key_exists( $nation_id, $this->applicants_count_by_quota ) ) {
						$this->applicants_count_by_quota[$nation_id] = 0;
					}
				} elseif ( $nation_id && array_key_exists( $nation_id, $this->ctr_slots ) ) {
					$level = $nation_id;
					if ( ! array_key_exists( $nation_id, $this->applicants_by_slots ) ) {
						$this->applicants_by_slots[$nation_id] = array();
					}
					if ( ! array_key_exists( $nation_id, $this->applicants_count_by_slots ) ) {
						$this->applicants_count_by_slots[$nation_id] = 0;
					}
					if ( ! array_key_exists( $nation_id, $this->applicants_by_quota ) ) {
						$this->applicants_by_quota[$nation_id] = array();
					}
					if ( ! array_key_exists( $nation_id, $this->applicants_count_by_quota ) ) {
						$this->applicants_count_by_quota[$nation_id] = 0;
					}
				} else {
					$level = 0;
					if ( ! array_key_exists( 0, $this->applicants_by_slots ) ) {
						$this->applicants_by_slots[0] = array();
					}
					if ( ! array_key_exists( 0, $this->applicants_count_by_slots ) ) {
						$this->applicants_count_by_slots[0] = 0;
					}
				}

				$this->applicants_count++;
				$this->applicants_by_slots[$level][] = $applicant;
				$this->applicants_count_by_slots[$level]++;
				$this->applicants_by_quota[$level][] = $applicant;
				$this->applicants_count_by_quota[$level]++;
				if ( $level === $city_id ) {
					$this->applicants_by_quota[$nation_id][] = $applicant;
					$this->applicants_count_by_quota[$nation_id]++;
				}
				if ( in_array( $level, array( $city_id, $nation_id ) ) ) {
					$this->applicants_by_quota[0][] = $applicant;
					$this->applicants_count_by_quota[0]++;
				}
			}

		} else { // activity lies in the past

			$parts = $vca_asm_registrations->get_activity_participants( $id );
			if ( is_array( $parts ) && ! empty( $parts ) ) {
				foreach ( $parts as $part ) {
					$vca_asm_registrations->move_registration_to_old( $id, $part );
				}
			}
			$waits = $vca_asm_registrations->get_activity_waiting( $id );
			if ( is_array( $waits ) && ! empty( $waits ) ) {
				foreach ( $waits as $waiter ) {
					$vca_asm_registrations->move_application_to_old( $id, $waiter );
				}
			}
			$apps = $vca_asm_registrations->get_activity_applications( $id );
			if ( is_array( $apps ) && ! empty( $apps ) ) {
				foreach ( $apps as $app ) {
					$vca_asm_registrations->move_application_to_old( $id, $app );
				}
			}

			$this->participants_by_slots = $vca_asm_registrations->get_activity_participants_old( $id, array( 'by_contingent' => true ) );
			$this->applicants = $vca_asm_registrations->get_activity_applications_old( $id );

			foreach ( $this->participants_by_slots as $geo_id => $participants_bs ) {
				if ( $geo_id !== 0 && ! $this->non_global_participants && ! empty( $participants_bs ) ) {
					$this->non_global_participants = true;
				}
				if ( ! array_key_exists( $geo_id, $this->participants_count_by_slots ) ) {
					$this->participants_count_by_slots[$geo_id] = 0;
				}
				if ( ! array_key_exists( $geo_id, $this->participants_by_quota ) ) {
					$this->participants_by_quota[$geo_id] = array();
				}
				if ( ! array_key_exists( $geo_id, $this->participants_count_by_quota ) ) {
					$this->participants_count_by_quota[$geo_id] = 0;
				}
				foreach ( $participants_bs as $participant ) {
					$this->participants[] = $participant;
					$this->participants_count++;
					$this->participants_count_by_slots[$geo_id]++;
					$this->participants_by_quota[$geo_id][] = $participant;
					$this->participants_count_by_quota[$geo_id]++;
					if ( $geo_id != 0 ) {
						$this->participants_by_quota[0][] = $participant;
						$this->participants_count_by_quota[0]++;
					}
					if ( $vca_asm_geography->is_city( $geo_id ) ) {
						$nation_query = $vca_asm_geography->get_ancestors( $geo_id, array(
							'data' => 'id',
							'format' => 'array',
							'type' => 'nation'
						));
						$nation = $nation_query[0];
						if ( ! array_key_exists( $nation, $this->participants_by_quota ) ) {
							$this->participants_by_quota[$nation] = array();
						}
						if ( ! array_key_exists( $nation, $this->participants_count_by_quota ) ) {
							$this->participants_count_by_quota[$nation] = 0;
						}
						$this->participants_by_quota[$nation][] = $participant;
						$this->participants_count_by_quota[$nation]++;
					}
				}
			}

			foreach ( $this->applicants as $applicant ) {
				$city_id = get_user_meta( $applicant, 'city', true );
				$nation_id = get_user_meta( $applicant, 'nation', true );
				$nation_id = ! empty( $nation_id ) ? $nation_id : ( $vca_asm_geography->has_nation( $city_id ) ? $vca_asm_geography->has_nation( $city_id ) : 'not_existent' );
				$level = 0;

				if ( $city_id && array_key_exists( $city_id, $this->cty_slots ) ) {
					$level = $city_id;
					if ( ! array_key_exists( $city_id, $this->applicants_by_slots ) ) {
						$this->applicants_by_slots[$city_id] = array();
					}
					if ( ! array_key_exists( $city_id, $this->applicants_count_by_slots ) ) {
						$this->applicants_count_by_slots[$city_id] = 0;
					}
					if ( ! array_key_exists( $city_id, $this->applicants_by_quota ) ) {
						$this->applicants_by_quota[$city_id] = array();
					}
					if ( ! array_key_exists( $city_id, $this->applicants_count_by_quota ) ) {
						$this->applicants_count_by_quota[$city_id] = 0;
					}
					if ( ! array_key_exists( $nation_id, $this->applicants_by_quota ) ) {
						$this->applicants_by_quota[$nation_id] = array();
					}
					if ( ! array_key_exists( $nation_id, $this->applicants_count_by_quota ) ) {
						$this->applicants_count_by_quota[$nation_id] = 0;
					}
				} elseif ( $nation_id && array_key_exists( $nation_id, $this->ctr_slots ) ) {
					$level = $nation_id;
					if ( ! array_key_exists( $nation_id, $this->applicants_by_slots ) ) {
						$this->applicants_by_slots[$nation_id] = array();
					}
					if ( ! array_key_exists( $nation_id, $this->applicants_count_by_slots ) ) {
						$this->applicants_count_by_slots[$nation_id] = 0;
					}
					if ( ! array_key_exists( $nation_id, $this->applicants_by_quota ) ) {
						$this->applicants_by_quota[$nation_id] = array();
					}
					if ( ! array_key_exists( $nation_id, $this->applicants_count_by_quota ) ) {
						$this->applicants_count_by_quota[$nation_id] = 0;
					}
				} else {
					$level = 0;
					if ( ! array_key_exists( 0, $this->applicants_by_slots ) ) {
						$this->applicants_by_slots[0] = array();
					}
					if ( ! array_key_exists( 0, $this->applicants_count_by_slots ) ) {
						$this->applicants_count_by_slots[0] = 0;
					}
				}

				$this->applicants_count++;
				$this->applicants_by_slots[$level][] = $applicant;
				$this->applicants_count_by_slots[$level]++;
				$this->applicants_by_quota[$level][] = $applicant;
				$this->applicants_count_by_quota[$level]++;
				if ( $level === $city_id ) {
					$this->applicants_by_quota[$nation_id][] = $applicant;
					$this->applicants_count_by_quota[$nation_id]++;
				}
				if ( in_array( $level, array( $city_id, $nation_id ) ) ) {
					$this->applicants_by_quota[0][] = $applicant;
					$this->applicants_count_by_quota[0]++;
				}
			}
		}
	}

	/* ============================= RESET OBJECT ============================= */

	/**
	 * Resets object
	 *
	 * Calls gather_meta after clean up
	 *
	 * @return void
	 *
	 * @since 1.3
	 * @access public
	 */
	public function reset()
	{
		$this->department = 'actions';

		$this->post_object = object;
		$this->name = '';
		$this->meta = array();

		$this->type = 'festival';
		$this->nice_type = 'Festival';
		$this->icon_url = 'http://vivaconagua.org/wp-content/plugins/vca-asm/img/icon-festivals_32.png';

		$this->nation = 0;
		$this->nation_name = '';
		$this->city = 0;
		$this->city_name = '';
		$this->delegation = false;

		$this->membership_required = false;

		$this->start_app = 0;
		$this->end_app = 0;
		$this->start_act = 0;
		$this->end_act = 0;
		$this->upcoming = true;

		$this->participants = array();
		$this->participants_count = 0;
		$this->waiting = array();
		$this->waiting_count = 0;
		$this->applicants = array();
		$this->applicants_count = 0;

		$this->participants_by_slots = array();
		$this->participants_count_by_slots = array();
		$this->waiting_by_slots = array();
		$this->waiting_count_by_slots = array();
		$this->applicants_by_slots = array();
		$this->applicants_count_by_slots = array();

		$this->participants_by_quota = array( 0 => array() );
		$this->participants_count_by_quota = array( 0 => 0 );
		$this->waiting_by_quota = array( 0 => array() );
		$this->waiting_count_by_quota = array( 0 => 0 );
		$this->applicants_by_quota = array( 0 => array() );
		$this->applicants_count_by_quota = array( 0 => 0 );

		$this->minimum_quotas = array();
		$this->non_global_participants = false;

		$this->total_slots = 0;
		$this->global_slots = 0;
		$this->ctr_quotas = array();
		$this->ctr_slots = array();
		$this->ctr_quotas_switch = 'nay';
		$this->cty_slots = array();
		$this->ctr_cty_switch = array();
		$this->slots = array();

		$this->gather_meta( $this->id );
	}

	/* ============================= UTILITY METHODS ============================= */

	/**
	 * Checks whether an activity of id exists
	 *
	 * Calls gather_meta method if supplied post ID matches an activity
	 *
	 * @param int $id						(post-)ID of the activity post type
	 *
	 * @global object $wpdb
	 * @global object $vca_asm_activities
	 *
	 * @since 1.3
	 * @access public
	 */
	public function is_activity( $id )
	{
		global $wpdb,
			$vca_asm_activities;

		$post_type = get_post_type( $id );

		if ( $post_type ) {
			$this->exists = true;
			if ( in_array( $post_type, $vca_asm_activities->activity_types ) ) {
				$this->is_activity = true;
				$this->gather_meta( $id );
			}
		}

		return $this->is_activity;
	}

	/**
	 * Determines whether a supporter has applied for this activity
	 *
	 * @param int $supporter_id
	 *
	 * @return (bool)
	 *
	 * @since 1.3
	 * @access public
	 */
	public function has_applied( $supporter_id )
	{
		return in_array( $supporter_id, $this->applicants );
	}

	/**
	 * Determines whether a supporter is a partcipant of this activity
	 *
	 * @param int $supporter_id
	 *
	 * @return (bool)
	 *
	 * @since 1.3
	 * @access public
	 */
	public function is_participant( $supporter_id )
	{
		return in_array( $supporter_id, $this->participants );
	}

	/**
	 * Determines whether a supporter is eligible to this activity
	 *
	 * @param int $supporter_id
	 *
	 * @return mixed (bool) false if not, (int) quota (geo-unit) id if so
	 *
	 * @since 1.3
	 * @access public
	 */
	public function is_eligible( $supporter_id )
	{
		global $vca_asm_geography;

		$membership_status = get_user_meta( $supporter_id, 'membership', true );
		$city = get_user_meta( $supporter_id, 'city', true );
		$nation = get_user_meta( $supporter_id, 'nation', true );

		if (
			! $this->membership_required ||
			2 == $membership_status
		) {
			if ( array_key_exists( $city, $this->cty_slots ) && 0 < intval( $this->cty_slots[$city] ) ) {
				return $city;
			} elseif ( array_key_exists( $nation, $this->ctr_slots ) && 0 < intval( $this->ctr_slots[$nation] ) ) {
				return $nation;
			} elseif ( 0 < $this->global_slots ) {
				return 0;
			}
		}
		return false;
	}

} // class

endif; // class exists

?>