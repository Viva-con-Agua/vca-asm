<?php

/**
 * VCA_ASM_Activities class.
 *
 * This class contains properties and methods for the activity post types,
 * namely defining its metadata, a saving routine, restrictions based on user capabilities and the like.
 *
 * @link http://codex.wordpress.org/Post_Types Intro to Custom Post Types (in the following: CPT)
 *
 * @package VcA Activity & Supporter Management
 * @since 1.0
 *
 * Structure:
 * - Properties
 * - Constructor
 * - Custom Post Types
 * - Utility
 */

if ( ! class_exists( 'VCA_ASM_Activities' ) ) :

class VCA_ASM_Activities
{

	/* ============================= CLASS PROPERTIES ============================= */

	/**
	 * Holds the current activity
	 *
	 * @var object $the_activity
	 * @see constructor
	 * @see class VCA_ASM_Activity (models)
	 * @since 1.3
	 * @access public
	 */
	public $the_activity = false;

	/**
	 * Array of (slugs of) valid CPTs
	 *
	 * @var array $activity_types
	 * @since 1.3
	 * @access public
	 */
	public $activity_types = array(
		'concert',
		'festival',
		'miscactions',
		'nwgathering',
		'goldeimerfestival'
	);

	/**
	 * Pairs CPTs with organizational departments
	 *
	 * @var array $departments_by_activity
	 * @since 1.3
	 * @access public
	 */
	public $departments_by_activity = array(
		'concert' => 'actions',
		'festival' => 'actions',
		'miscactions' => 'actions',
		'misceducation' => 'education',
		'miscnetwork' => 'network',
		'nwgathering' => 'network',
		'goldeimerfestival' => 'goldeimer'
	);

	/**
	 * Pairs activity slugs with department nice names (as translatable strings)
	 *
	 * @var array $activities_by_departement
	 * @see constructor
	 * @since 1.3
	 * @access public
	 */
	public $activities_by_department = array();

	/**
	 * Pairs acticity slugs with activity nice names (as translatable strings)
	 *
	 * @var array $to_nicename
	 * @see constructor
	 * @since 1.3
	 * @access public
	 */
	public $to_nicename = array();

	/**
	 * Pairs acticity slugs with activity nice names (as translatable strings)
	 *
	 * @var array $activities_to_nicename
	 * @see constructor
	 * @since 1.3
	 * @access public
	 */
	public $activities_to_nicename = array();

	/**
	 * Pairs activity slugs with plural forms of their nice names (as translatable strings)
	 *
	 * @var array $to_plural_nicename
	 * @see constructor
	 * @since 1.3
	 * @access public
	 */
	public $to_plural_nicename = array();

	/**
	 * Pairs activity slugs with plural forms of their nice names (as translatable strings)
	 *
	 * @var array $activities_to_plural_nicename
	 * @see constructor
	 * @since 1.3
	 * @access public
	 */
	public $activities_to_plural_nicename = array();

	/**
	 * Holds all activity types in a standardized format,
	 * such that it will be readily accepted by the plugins template classes
	 * for use  in a dropdown menu.
	 *
	 * @var array $options_array
	 * @see constructor
	 * @since 1.3
	 * @access public
	 */
	public $options_array = array();

	/**
	 * Holds all activity types in a standardized format,
	 * such that it will be readily accepted by the plugins template classes
	 * for use  in a dropdown menu.
	 * Includes an "all" option in the list.
	 *
	 * @var array $options_array_with_all
	 * @see constructor
	 * @since 1.3
	 * @access public
	 */
	public $options_array_with_all = array();

	/* ============================= CONSTRUCTOR ============================= */

	/**
	 * Constructor
	 *
	 * @global object $post
	 * @global object $vca_asm_utilities
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct()
	{
        /** @var vca_asm_utilities $vca_asm_utilities */
		global $post, $vca_asm_utilities;

		/* Populate translatable/dynamic class properties, which can't be set in the class head */

		$this->activities_by_department = array(
			'actions' => array(
				array(
					'slug' => 'festival',
					'name' => __( 'Festival', 'vca-asm' )
				),
				array(
					'slug' => 'concert',
					'name' => __( 'Concert', 'vca-asm' )
				),
				array(
					'slug' => 'miscactions',
					'name' => __( 'Miscellaneous Activities', 'vca-asm' )
				)
			),
			'education' => array(),
			'network' => array(
				array(
					'slug' => 'nwgathering',
					'name' => __( 'Network Gathering', 'vca-asm' )
				)
			),
			'goldeimer' => array(
				array(
					'slug' => 'goldeimerfestival',
					'name' => __( 'Goldeimer Festival', 'vca-asm' )
				)
			)
		);

		$this->to_nicename = array(
			'concert' => __( 'Concert', 'vca-asm' ),
			'festival' => __( 'Festival', 'vca-asm' ),
			'miscactions' => __( 'Miscellaneous action', 'vca-asm' ),
			'misceducation' => __( 'Miscellaneous education activity', 'vca-asm' ),
			'miscnetwork' => __( 'Miscellaneous network activity', 'vca-asm' ),
			'nwgathering' => __( 'Network Gathering', 'vca-asm' ),
			'goldeimerfestival' => __( 'Goldeimer Festival', 'vca-asm' )
		);
		asort( $this->to_nicename );
		$this->activities_to_nicename = $this->to_nicename;

		$this->to_plural_nicename = array(
			'concert' => __( 'Concerts', 'vca-asm' ),
			'festival' => __( 'Festivals', 'vca-asm' ),
			'miscactions' => __( 'Miscellaneous activities', 'vca-asm' ),
			'misceducation' => __( 'Miscellaneous activities', 'vca-asm' ),
			'miscnetwork' => __( 'Miscellaneous activities', 'vca-asm' ),
			'nwgathering' => __( 'Network Gatherings', 'vca-asm' ),
			'goldeimerfestival' => __( 'Goldeimer Festivals', 'vca-asm' )
		);
		asort( $this->to_plural_nicename );
		$this->activities_to_plural_nicename = $this->to_plural_nicename;

		foreach ( $this->to_plural_nicename as $type => $nicename ) {
			if ( in_array( $type, $this->activity_types ) ) {
				$this->options_array[] = array(
					'label' => $nicename,
					'value' => $type
				);
			}
		}

		$this->options_array_with_all[] = array(
			'label' => __( 'All', 'vca-asm' ),
			'value' => 'all'
		);
		$this->options_array_with_all = array_merge( $this->options_array_with_all, $this->options_array );

		$acts_by_dep = array();
		foreach ( $this->activities_by_department as $dep => $acts ) {
			$acts_by_dep[$dep] = $vca_asm_utilities->sort_by_key( $acts, 'name' );
		}
		$this->activities_by_department = $acts_by_dep;

		if ( ! empty( $post->ID ) ) {
			$this->the_activity = new VCA_ASM_Activity( $post->ID );
		}

		/* Action Hooks */

		add_action( 'admin_notices', array( $this, 'notice_handler' ), 11 );
		add_action( 'admin_footer', array( $this, 'clear_notices' ) );
		$this->setup_activities();
	}

	/* ============================= CUSTOM POST TYPES ============================= */

	/**
	 * Returns nested arrays of custom fields
	 *
	 * @param string $group				return either 'all' custom fields or those in a specific subset
	 * @return array $custom_fields
	 *
	 * @global object $current_user
	 * @global string $post_type
	 * @global object $vca_asm_geography
	 *
	 * @see templates/admin-custom fields.php
	 *
	 * @since 1.0
	 * @access private
	 */
	private function custom_fields( $group = 'all' )
	{
        /** @var vca_asm_geography $vca_asm_geography */
		global $current_user, $post_type, $vca_asm_geography;

		$admin_nation = get_user_meta( $current_user->ID, 'nation', true );
		$admin_city = get_user_meta( $current_user->ID, 'city', true );
		$department = ! empty( $this->departments_by_activity[$post_type] ) ? $this->departments_by_activity[$post_type] : 'actions';

		$admin_level = 'global';
		if ( ! $current_user->has_cap( 'vca_asm_manage_' . $department . '_global' ) ) {
			$admin_level = 'nation';
			if ( ! $current_user->has_cap( 'vca_asm_manage_' . $department . '_nation' ) ) {
				$admin_level = 'city';
			}
		}

		$custom_fields = array(
			'tools' => array (
				array (
					'label' => _x( 'What tools do we employ?', 'Tools Meta Box', 'vca-asm' ),
					'desc'  => _x( 'Choose which of the common VcA Activities are employed this time.', 'Tools Meta Box', 'vca-asm' ),
					'id'    => 'tools',
					'type'  => 'checkbox_group',
					'options' => array (
						array (
							'label' => _x( 'Cups', 'Tools Meta Box', 'vca-asm' ),
							'value' => 1
						),
						array (
							'label' => _x( 'Guest List', 'Tools Meta Box', 'vca-asm' ),
							'value' => 2
						),
						array (
							'label' => _x( 'Info Counter', 'Tools Meta Box', 'vca-asm' ),
							'value' => 3
						),
						array (
							'label' => _x( 'Water Bottles', 'Tools Meta Box', 'vca-asm' ),
							'value' => 4
						),
						array (
							'label' => _x( 'Special', 'Tools Meta Box', 'vca-asm' ),
							'value' => 5
						)
					)
				),
				array(
					'label' => _x( 'What special action?', 'Tools Meta Box', 'vca-asm' ),
					'desc'  => _x( 'Description of the special action. If "Special" is checked above and you leave this field blank, the list of tools will include "Special", otherwise it will list what you\'ve written here. If "Special" is not checked above, this field is irrelevant.', 'Tools Meta Box', 'vca-asm' ),
					'id'    => 'special',
					'type'	=> 'text'
				)
			),
			'applications' => array(
				array(
					'label'	=> _x( 'Start of application phase', 'Applications Meta Box', 'vca-asm' ),
					'desc'	=> _x( 'Before this date, the activity will not be displayed to supporters', 'Applications', 'vca-asm' ),
					'id'	=> 'start_app',
					'type'	=> 'date',
					'required' => true,
					'validation' => 'start_app'
				),
				array(
					'label'	=> _x( 'Application Deadline', 'Applications Meta Box', 'vca-asm' ),
					'desc'	=> _x( 'The end of the application phase', 'Applications', 'vca-asm' ),
					'id'	=> 'end_app',
					'type'	=> 'date',
					'required' => true,
					'validation' => 'end_app'
				)
			),
			'date' => array (
				array(
					'label'	=> _x( 'Start', 'Timeframe Meta Box', 'vca-asm' ),
					'desc'	=> _x( 'The beginning of the activity', 'Timeframe', 'vca-asm' ),
					'id'	=> 'start_act',
					'type'	=> 'date_time',
					'required' => true,
					'validation' => 'start_act'
				),
				array(
					'label'	=> _x( 'End', 'Timeframe Meta Box', 'vca-asm' ),
					'desc'	=> _x( 'The end of the activity', 'Timeframe', 'vca-asm' ),
					'id'	=> 'end_act',
					'type'	=> 'date_time',
					'required' => true,
					'validation' => 'end_act'
				)
			),
			'geo' => array (
				'nation' => array(
					'label'	=> __( 'Country', 'vca-asm' ),
					'desc'	=> _x( 'Associate the activity with a country.', 'Geo Meta Box', 'vca-asm' ) . ' ' . _x( 'This is irrelevant to slots &amp; participants and only matters for categorization and sorting.', 'Geo Meta Box', 'vca-asm' ),
					'id'	=> 'nation',
					'type'	=> 'select',
					'options' => $vca_asm_geography->options_array( array( 'type' => 'nation' ) ),
					'default' => ! empty( $admin_nation ) ? $admin_nation : 0,
					'disabled' => 'global' === $admin_level ? false : true
				),
				'city' => array(
					'label'	=> _x( 'City', 'Geo Meta Box', 'vca-asm' ),
					'desc'	=> _x( 'Associate the activity with a city.', 'Geo Meta Box', 'vca-asm' ) . ' ' . _x( 'This is irrelevant to slots &amp; participants and only matters for categorization and sorting.', 'Geo Meta Box', 'vca-asm' ),
					'id'	=> 'city',
					'type'	=> 'select',
					'options' => $vca_asm_geography->options_array( array(
						'global_option' => _x( 'no specific city', 'Regions', 'vca-asm' ),
						'type' => 'city',
						'descendants_of' => ( is_object( $this->the_activity ) && ! empty( $this->the_activity->nation ) ) ?
							$this->the_activity->nation : ( ! empty( $admin_nation ) ? $admin_nation : 40 )
					)),
					'default' => ! empty( $admin_city ) ? $admin_city : 0,
					'disabled' => ( in_array( $admin_level, array( 'global', 'nation' ) ) ) ? false : true
				),
				'delegation' => array(
					'label'	=> _x( 'Delegation', 'Region Meta Box', 'vca-asm' ),
					'desc'	=> _x( 'Delegate to the selected city\'s SPOCs (Single Person(s) of Contact). If you choose to do so, the city\'s administrative user can edit the activity, as well as accept and deny applications globally. If the selected city does not have an administrative user assigned, this option will be ignored.', 'Region Meta Box', 'vca-asm' ),
					'id'	=> 'delegate',
					'type'	=> 'checkbox',
					'option' => _x( 'Yes, delegate', 'Region Meta Box', 'vca-asm' ),
					'value' => 'delegate',
					'disabled' => ( in_array( $admin_level, array( 'global', 'nation' ) ) ) ? false : true
				)
			),
			'meta' => array (
				array(
					'label' => _x( 'Location', 'Festival Data Meta Box', 'vca-asm' ),
					'desc'	=> _x( 'Where the activity takes place', 'Festival Data Meta Box', 'vca-asm' ),
					'id'	=> 'location',
					'type'	=> 'text',
					'required' => true,
					'validation' => 'required'
				),
				array(
					'label'=> _x( 'Website', 'Festival Data Meta Box', 'vca-asm' ),
					'desc'	=> _x( "The activities&apos; Website", 'Festival Data Meta Box', 'vca-asm' ),
					'id'	=> 'website',
					'type'	=> 'text'
				),
				array(
					'label' => _x( 'Directions', 'Festival Data Meta Box', 'vca-asm' ),
					'desc'	=> _x( 'Description of how to reach the festival grounds', 'Festival Data Meta Box', 'vca-asm' ),
					'id'	=> 'directions',
					'type'	=> 'textarea'
				),
				array(
					'label' => _x( 'additional Notes', 'Activity Data Meta Box', 'vca-asm' ),
					'desc'	=> _x( 'Got anything else to say?', 'Festival Data Meta Box', 'vca-asm' ),
					'id'	=> 'notes',
					'type'	=> 'textarea'
				)
			),
			'slots-settings' => array(
				array(
					'label' => _x( 'Membership', 'Quotas Meta Box', 'vca-asm' ),
					'desc'	=> _x( 'Should this activity be limited to &quot;active members&quot; or open to all supporters?', 'Quotas Meta Box', 'vca-asm' ),
					'id'	=> 'membership_required',
					'type'	=> 'radio',
					'options' => array(
						array(
							'label' => _x( 'all', 'Slot Allocation Meta Box', 'vca-asm' ),
							'value' => 0
						),
						array(
							'label' => _x( '&quot;active members&quot; only', 'Slot Allocation Meta Box', 'vca-asm' ),
							'value' => 1
						)
					),
					'required' => true
				),
				array(
					'label' => _x( 'Total Slots', 'Quotas Meta Box', 'vca-asm' ),
					'desc' => _x( 'Set the total amount of open slots for this activity', 'Quotas Meta Box', 'vca-asm' ),
					'id' => 'total_slots',
					'type' => 'total_slots',
					'min' => 1,
					'max' => ( 'nwgathering' === $post_type ) ? 5000 : 1000,
					'step' => 1,
					'required' => true,
					'validation' => 'numbers'
				),
				array(
					'label' => _x( 'Global Slots', 'Quotas Meta Box', 'vca-asm' ),
					'desc' => _x( 'These slots are open to applications from the entire network.', 'Quotas Meta Box', 'vca-asm' ),
					'id' => 'global_slots',
					'type' => 'global_slots'
				),
				array(
					'label' => _x( 'Country Quotas?', 'Quotas Meta Box', 'vca-asm' ),
					'desc'	=> _x( 'Split some or all of the total slots into country specific quotas?', 'Quotas Meta Box', 'vca-asm' ),
					'id'	=> 'ctr_quotas_switch',
					'type'	=> 'ctr_quotas_switch',
					'options' => array(
						array(
							'label' => _x( 'No, the entire network may apply for all slots', 'Slot Allocation Meta Box', 'vca-asm' ),
							'value' => 'nay',
							'default' => true
						),
						array(
							'label' => _x( 'Yes, split into country quotas', 'Slot Allocation Meta Box', 'vca-asm' ),
							'value' => 'yay'
						)
					)
				),
				array(
					'label' => _x( 'Country Quotas', 'Quotas Meta Box', 'vca-asm' ),
					'desc' => _x( 'You may define individual quotas per country. These slots can only be given to supporters registered in the country in question. If you want the activity to be limited to one or more countries, make sure the all slots are used up and global slots is zero.', 'Quotas Meta Box', 'vca-asm' ),
					'id' => 'ctr_quotas',
					'type' => 'ctr_quotas',
					'min' => 0,
					'max' => 50,
					'step' => 1
				),
				array(
					'label' => _x( 'City Quotas', 'Quotas Meta Box', 'vca-asm' ),
					'desc' => _x( 'You may define individual quotas per city. These slots can only be given to supporters registered in the city in question. If you want the activity to be limited to one or more cities of a particular country, make sure the all countries\' slots are used up and the number of slots available via the country quota is zero.', 'Quotas Meta Box', 'vca-asm' ),
					'id' => 'cty_slots',
					'type' => 'cty_slots',
					'min' => 0,
					'max' => 50,
					'step' => 1
				)
			),
			/*
			 * The whole 'contact' deal ain't the slickest solution.
			 *
			 * The 'contact' metabox (and its one field of type 'contact') triggers the template
			 * to create the entire 3-field section
			 * 'contact_utility' is only used in the saving routine...
			 */
			'contact' => array(
				array(
					'id' => 'contact_name',
					'type' => 'contact',
					'desc'	=> _x( 'First or full name, email address and mobile phone number of the contact person(s)', 'Contact Person Meta Box', 'vca-asm' ) . ' ' . _x( '(This information will be visible to registered supporters only)', 'Contact Person Meta Box', 'vca-asm' )
				)
			),
			'contact_utility' => array(
				array(
					'id' => 'contact_email',
					'type' => 'contact'
				),
				array(
					'id' => 'contact_mobile',
					'type' => 'contact'
				)
			)
		);

		if ( isset( $this->the_activity->upcoming ) && true === $this->the_activity->upcoming ) {
			$custom_fields['participants'] = array(
				array(
					'label' => _x( 'Applicants', 'Registrations Meta Box', 'vca-asm' ),
					'id' => 'applicants',
					'type' => 'applicants',
					'desc' => __( 'Supporters currently applying to this activity', 'vca-asm' )
				),
				array(
					'label' => _x( 'Waiting List', 'Registrations Meta Box', 'vca-asm' ),
					'id' => 'waiting',
					'type' => 'waiting',
					'desc' => __( 'Supporters currently on the Waiting List to this activity', 'vca-asm' )
				),
				array(
					'label' => _x( 'Participants', 'Registrations Meta Box', 'vca-asm' ),
					'id' => 'participants',
					'type' => 'participants',
					'desc' => __( 'Supporters that are participating in this activity (i.e. accepted applications)', 'vca-asm' )
				),
				array(
					'label' => _x( 'Lists &amp; Mailing', 'Registrations Meta Box', 'vca-asm' ),
					'id' => 'data-links',
					'type' => 'data-links',
					'desc' => __( 'Contact the supporters', 'vca-asm' )
				)
			);
		} else {
			$custom_fields['participants'] = array(
				array(
					'label' => _x( '(denied) Applicants', 'Registrations Meta Box', 'vca-asm' ),
					'id' => 'applicants',
					'type' => 'applicants',
					'desc' => __( 'Supporters that had unsuccessfully applied to this activity', 'vca-asm' )
				),
				array(
					'label' => _x( 'Participants', 'Registrations Meta Box', 'vca-asm' ),
					'id' => 'participants',
					'type' => 'participants',
					'desc' => __( 'Supporters that participated in this activity', 'vca-asm' )
				),
				array(
					'label' => _x( 'Lists &amp; Mailing', 'Registrations Meta Box', 'vca-asm' ),
					'id' => 'data-links',
					'type' => 'data-links',
					'desc' => __( 'Contact the supporters', 'vca-asm' )
				)
			);
		}

		if ( 'global' !== $admin_level ) {
			$custom_fields['geo']['nation']['type'] = 'hidden-with-text';
			$custom_fields['geo']['nation']['value'] = $custom_fields['geo']['nation']['default'];
			$custom_fields['geo']['nation']['text'] = $vca_asm_geography->get_name( $custom_fields['geo']['nation']['default'] );
			$custom_fields['geo']['nation']['disabled'] = false;

			if ( 'nation' !== $admin_level ) {
				$custom_fields['geo']['city']['type'] = 'hidden-with-text';
				$custom_fields['geo']['city']['value'] = $custom_fields['geo']['city']['default'];
				$custom_fields['geo']['city']['text'] = $vca_asm_geography->get_name( $custom_fields['geo']['city']['default'] );
				$custom_fields['geo']['city']['disabled'] = false;

				$custom_fields['geo']['delegation']['type'] = 'hidden-with-text';
				$custom_fields['geo']['delegation']['text'] = _x( 'Yes, delegate', 'Region Meta Box', 'vca-asm' );
				$custom_fields['geo']['delegation']['disabled'] = false;
			}
		}

		if( 'all' === $group ) {
			return $custom_fields;
		} elseif( 'all-flat' === $group ) {
			$flat_fields = array();
			foreach ( $custom_fields as $field_group ) {
				$flat_fields = array_merge( $flat_fields, $field_group );
			}
			return $flat_fields;
		} elseif( isset( $custom_fields[$group] ) ) {
			return $custom_fields[$group];
		} else {
			return array();
		}
	}

	/**
	 * Sets up all activities
	 *
	 * Registers all activity post types.
	 * Initiates creation of custom fields and metaboxes, initiates meta capability mapping.
	 *
	 * @return void
	 *
	 * @see constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function setup_activities()
	{

		/* Capabilities */

		$actions_capabilities = array(
			'publish_posts' => 'vca_asm_publish_actions_activities',
			'edit_posts' => 'vca_asm_edit_actions_activities',
			'edit_others_posts' => 'vca_asm_edit_others_actions_activities',
			'delete_posts' => 'vca_asm_delete_actions_activities',
			'delete_others_posts' => 'vca_asm_delete_others_actions_activities',
			'read_private_posts' => 'vca_asm_read_private_actions_activities',
			'edit_post' => 'vca_asm_edit_actions_activity',
			'delete_post' => 'vca_asm_delete_actions_activity',
			'read_post' => 'vca_asm_read_actions_activity'
		);
		$network_capabilities = array(
			'publish_posts' => 'vca_asm_publish_network_activities',
			'edit_posts' => 'vca_asm_edit_network_activities',
			'edit_others_posts' => 'vca_asm_edit_others_network_activities',
			'delete_posts' => 'vca_asm_delete_network_activities',
			'delete_others_posts' => 'vca_asm_delete_others_network_activities',
			'read_private_posts' => 'vca_asm_read_private_network_activities',
			'edit_post' => 'vca_asm_edit_network_activity',
			'delete_post' => 'vca_asm_delete_network_activity',
			'read_post' => 'vca_asm_read_network_activity'
		);
		$goldeimer_capabilities = array(
			'publish_posts' => 'vca_asm_publish_goldeimer_activities',
			'edit_posts' => 'vca_asm_edit_goldeimer_activities',
			'edit_others_posts' => 'vca_asm_edit_others_goldeimer_activities',
			'delete_posts' => 'vca_asm_delete_goldeimer_activities',
			'delete_others_posts' => 'vca_asm_delete_others_goldeimer_activities',
			'read_private_posts' => 'vca_asm_read_private_goldeimer_activities',
			'edit_post' => 'vca_asm_edit_goldeimer_activity',
			'delete_post' => 'vca_asm_delete_goldeimer_activity',
			'read_post' => 'vca_asm_read_goldeimer_activity'
		);

		/* Labels */

		$actions_concert_labels = array(
			'name' => _x( 'Concerts', 'post type general name', 'vca-asm' ),
			'singular_name' => _x( 'Concert', 'post type singular name', 'vca-asm' ),
			'menu_name' => _x( 'Concerts', 'post type general name', 'vca-asm' ),
			'add_new' => _x( 'Add New', 'activity', 'vca-asm' ),
			'add_new_item' => __( 'Add New Concert', 'vca-asm' ),
			'edit_item' => __( 'Edit Concert', 'vca-asm' ),
			'new_item' => __( 'New Concert', 'vca-asm' ),
			'all_items' => __( 'Concerts', 'vca-asm' ),
			'view_item' => __( 'View Concert', 'vca-asm' ),
			'search_items' => __( 'Search Concerts', 'vca-asm' ),
			'not_found' =>  __( 'No Concerts found', 'vca-asm' ),
			'not_found_in_trash' => __( 'No Concerts found in Trash', 'vca-asm' ),
			'parent_item_colon' => ''
		);
		$actions_festival_labels = array(
			'name' => _x( 'Festivals', 'post type general name', 'vca-asm' ),
			'singular_name' => _x( 'Festival', 'post type singular name', 'vca-asm' ),
			'menu_name' => _x( 'Festivals', 'post type general name', 'vca-asm' ),
			'add_new' => _x( 'Add New', 'activity', 'vca-asm' ),
			'add_new_item' => __( 'Add New Festival', 'vca-asm' ),
			'edit_item' => __( 'Edit Festival', 'vca-asm' ),
			'new_item' => __( 'New Festival', 'vca-asm' ),
			'all_items' => __( 'Festivals', 'vca-asm' ),
			'view_item' => __( 'View Festival', 'vca-asm' ),
			'search_items' => __( 'Search Festivals', 'vca-asm' ),
			'not_found' =>  __( 'No Festivals found', 'vca-asm' ),
			'not_found_in_trash' => __( 'No Festivals found in Trash', 'vca-asm' ),
			'parent_item_colon' => ''
		);
		$actions_miscellaneous_labels = array(
			'name' => _x( 'Miscellaneous activities', 'post type general name', 'vca-asm' ),
			'singular_name' => _x( 'Miscellaneous Activity', 'post type singular name', 'vca-asm' ),
			'menu_name' => _x( 'Miscellaneous', 'activity', 'vca-asm' ),
			'add_new' => _x( 'Add New', 'activity', 'vca-asm' ),
			'add_new_item' => __( 'Add New miscellaneous activity', 'vca-asm' ),
			'edit_item' => __( 'Edit miscellaneous activity', 'vca-asm' ),
			'new_item' => __( 'New miscellaneous activity', 'vca-asm' ),
			'all_items' => __( 'Miscellaneous', 'vca-asm' ),
			'view_item' => __( 'View miscellaneous activity', 'vca-asm' ),
			'search_items' => __( 'Search miscellaneous activities', 'vca-asm' ),
			'not_found' =>  __( 'No miscellaneous activities found', 'vca-asm' ),
			'not_found_in_trash' => __( 'No miscellaneous activities found in Trash', 'vca-asm' ),
			'parent_item_colon' => ''
		);
		$network_nwgathering_labels = array(
			'name' => _x( 'Network Gatherings', 'post type general name', 'vca-asm' ),
			'singular_name' => _x( 'Network Gathering', 'post type singular name', 'vca-asm' ),
			'menu_name' => _x( 'Network Gatherings', 'post type general name', 'vca-asm' ),
			'add_new' => _x( 'Add New', 'activity', 'vca-asm' ),
			'add_new_item' => __( 'Add New Network Gathering', 'vca-asm' ),
			'edit_item' => __( 'Edit Network Gathering', 'vca-asm' ),
			'new_item' => __( 'New Network Gathering', 'vca-asm' ),
			'all_items' => __( 'Network Gatherings', 'vca-asm' ),
			'view_item' => __( 'View Network Gathering', 'vca-asm' ),
			'search_items' => __( 'Search Network Gatherings', 'vca-asm' ),
			'not_found' =>  __( 'No Network Gatherings found', 'vca-asm' ),
			'not_found_in_trash' => __( 'No Network Gatherings found in Trash', 'vca-asm' ),
			'parent_item_colon' => ''
		);
		$goldeimer_festival_labels = array(
			'name' => _x( 'Goldeimer Festivals', 'post type general name', 'vca-asm' ),
			'singular_name' => _x( 'Goldeimer Festival', 'post type singular name', 'vca-asm' ),
			'menu_name' => _x( 'Goldeimer Festival', 'post type general name', 'vca-asm' ),
			'add_new' => _x( 'Add New', 'activity', 'vca-asm' ),
			'add_new_item' => __( 'Add New Goldeimer Festival', 'vca-asm' ),
			'edit_item' => __( 'Edit Goldeimer Festival', 'vca-asm' ),
			'new_item' => __( 'New Goldeimer Festival', 'vca-asm' ),
			'all_items' => __( 'Goldeimer Festivals', 'vca-asm' ),
			'view_item' => __( 'View Goldeimer Festival', 'vca-asm' ),
			'search_items' => __( 'Search Goldeimer Festivals', 'vca-asm' ),
			'not_found' =>  __( 'No Goldeimer Festivals found', 'vca-asm' ),
			'not_found_in_trash' => __( 'No Goldeimer Festivals found in Trash', 'vca-asm' ),
			'parent_item_colon' => ''
		);

		/* Arguments/Parameters to be used in register_post_type() calls */

		$concert_args = array(
			'labels' => $actions_concert_labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => 'vca-asm-actions',
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'vca_asm_actions',
			'capabilities' => $actions_capabilities,
			'has_archive' => true,
			'hierarchical' => false,
			'menu_position' => 2,
			'menu_icon' => VCA_ASM_RELPATH . 'admin/img/icon-actions_32.png',
			'supports' => array( 'title' )
		);
		$festival_args = array(
			'labels' => $actions_festival_labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => 'vca-asm-actions',
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'vca_asm_actions',
			'capabilities' => $actions_capabilities,
			'has_archive' => true,
			'hierarchical' => false,
			'menu_position' => 1,
			'menu_icon' => VCA_ASM_RELPATH . 'img/icon-actions_32.png',
			'supports' => array( 'title' )
		);
		$miscactions_args = array(
			'labels' => $actions_miscellaneous_labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => 'vca-asm-actions',
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'vca_asm_actions',
			'capabilities' => $actions_capabilities,
			'has_archive' => true,
			'hierarchical' => false,
			'menu_position' => 3,
			'menu_icon' => VCA_ASM_RELPATH . 'img/icon-actions_32.png',
			'supports' => array( 'title' )
		);
		$nwgathering_args = array(
			'labels' => $network_nwgathering_labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => 'vca-asm-network',
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'vca_asm_network',
			'capabilities' => $network_capabilities,
			'has_archive' => true,
			'hierarchical' => false,
			'menu_position' => 1,
			'menu_icon' => VCA_ASM_RELPATH . 'img/icon-actions_32.png',
			'supports' => array( 'title' )
		);
		$goldeimer_festival_args = array(
			'labels' => $goldeimer_festival_labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => 'vca-asm-goldeimer',
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'vca_asm_goldeimer',
			'capabilities' => $goldeimer_capabilities,
			'has_archive' => true,
			'hierarchical' => false,
			'menu_position' => 2,
			'menu_icon' => VCA_ASM_RELPATH . 'img/icon-actions_32.png',
			'supports' => array( 'title' )
		);

		add_filter( 'map_meta_cap', array( $this, 'vca_asm_map_meta_cap' ), 10, 4 );

		/* post type registration in alphabetical order of German name */
		register_post_type( 'festival', $festival_args );
		register_post_type( 'concert', $concert_args );
		register_post_type( 'miscactions', $miscactions_args );

		register_post_type( 'nwgathering', $nwgathering_args );

		register_post_type( 'goldeimerfestival', $goldeimer_festival_args );

		add_action( 'add_meta_boxes', array( $this, 'meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta' ), 10, 2 );

		add_filter( 'manage_edit-concert_columns', array( $this, 'concert_columns' ) );
		add_filter( 'manage_edit-festival_columns', array( $this, 'festival_columns' ) );
		add_filter( 'manage_edit-miscactions_columns', array( $this, 'miscactions_columns' ) );
		add_filter( 'manage_edit-nwgathering_columns', array( $this, 'nwgathering_columns' ) );
		add_filter( 'manage_edit-goldeimerfestival_columns', array( $this, 'goldeimerfestival_columns' ) );

		add_action( 'manage_posts_custom_column',  array( $this, 'custom_column' ) );
		add_filter( 'gettext', array( $this, 'admin_ui_text_alterations' ), 10, 2 );
		add_filter( 'post_updated_messages', array( $this, 'admin_ui_updated_messages' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'set_script_params' ) );
		add_action( 'admin_notices', array( $this, 'notice_handler' ) );
	}

	/**
	 * Alters UI strings to fit post type
	 *
	 * 'gettext' filter callback
	 *
	 * @param string $translation
	 * @param string $text
	 * @return string $translation
	 *
	 * @since 1.1
	 * @access public
	 */
	public function admin_ui_text_alterations( $translation, $text )
	{
		global $post_type;

		if ( is_admin() && in_array( $post_type, $this->activity_types ) ) {
			switch ( $post_type ) {
				case 'concert':
					if( 'Enter title here' == $text ) {
						return __( 'Name of Concert', 'vca-asm' );
					}
					if( 'Update' == $text ) {
						return __( 'Update Concert', 'vca-asm' );
					}
					if( 'Publish' == $text ) {
						return __( 'Publish Concert', 'vca-asm' );
					}
				break;
				case 'festival':
					if( 'Enter title here' == $text ) {
						return __( 'Name of Festival', 'vca-asm' );
					}
					if( 'Update' == $text ) {
						return __( 'Update Festival', 'vca-asm' );
					}
					if( 'Publish' == $text ) {
						return __( 'Publish Festival', 'vca-asm' );
					}
				break;
				case 'miscactions':
					if( 'Enter title here' == $text ) {
						return __( 'Name of miscellaneous activity', 'vca-asm' );
					}
					if( 'Update' == $text ) {
						return __( 'Update miscellaneous activity', 'vca-asm' );
					}
					if( 'Publish' == $text ) {
						return __( 'Publish miscellaneous activity', 'vca-asm' );
					}
				break;
				case 'nwgathering':
					if( 'Enter title here' == $text ) {
						return __( 'Name of Network Gathering', 'vca-asm' );
					}
					if( 'Update' == $text ) {
						return __( 'Update Network Gathering', 'vca-asm' );
					}
					if( 'Publish' == $text ) {
						return __( 'Publish Network Gathering', 'vca-asm' );
					}
				break;
				case 'goldeimerfestival':
					if( 'Enter title here' == $text ) {
						return __( 'Name of Festival', 'vca-asm' );
					}
					if( 'Update' == $text ) {
						return __( 'Update Goldeimer Festival', 'vca-asm' );
					}
					if( 'Publish' == $text ) {
						return __( 'Publish Goldeimer Festival', 'vca-asm' );
					}
				break;
			}
			if( 'Submit for Review' == $text ) {
				return __( 'Submit for review', 'vca-asm' );
			}
		}

		return $translation;
	}

	/**
	 * Alters message strings to fit post type
	 *
	 * 'post_updated_messages' filter callback
	 *
	 * @param array $messages Holds default translatable strings
	 * @return array $messages Takes up new strings depending on post type
	 *
	 * @since 1.1
	 * @access public
	 */
	public function admin_ui_updated_messages( $messages )
	{
		global $post, $post_ID;

		$messages['concert'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __( 'Concert updated. <a href="%s">View Concert</a>', 'vca-asm' ), esc_url( get_permalink($post_ID) ) ),
			2 => __( 'Custom field updated.', 'vca-asm' ),
			3 => __( 'Custom field deleted.', 'vca-asm' ),
			4 => __( 'Concert updated.', 'vca-asm' ),
			5 => isset($_GET['revision']) ? sprintf( __( 'Concert restored to revision from %s', 'vca-asm' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( 'Concert published. <a href="%s">View Concert</a>', 'vca-asm' ), esc_url( get_permalink($post_ID) ) ),
			7 => __( 'Concert saved.', 'vca-asm' ),
			8 => sprintf( __( 'Concert submitted. <a target="_blank" href="%s">Preview Concert</a>', 'vca-asm' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			9 => sprintf( __( 'Concert scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Concert</a>', 'vca-asm' ),
			date_i18n( get_option( 'date_format' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			10 => sprintf( __( 'Concert draft updated. <a target="_blank" href="%s">Preview Concert</a>', 'vca-asm' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) )
		);

		$messages['festival'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __( 'Festival updated. <a href="%s">View Festival</a>', 'vca-asm' ), esc_url( get_permalink($post_ID) ) ),
			2 => __( 'Custom field updated.', 'vca-asm' ),
			3 => __( 'Custom field deleted.', 'vca-asm' ),
			4 => __( 'Festival updated.', 'vca-asm' ),
			5 => isset($_GET['revision']) ? sprintf( __( 'Festival restored to revision from %s', 'vca-asm' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( 'Festival published. <a href="%s">View Festival</a>', 'vca-asm' ), esc_url( get_permalink($post_ID) ) ),
			7 => __( 'Festival saved.', 'vca-asm' ),
			8 => sprintf( __( 'Festival submitted. <a target="_blank" href="%s">Preview Festival</a>', 'vca-asm' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			9 => sprintf( __( 'Festival scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Festival</a>', 'vca-asm' ),
			date_i18n( get_option( 'date_format' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			10 => sprintf( __( 'Festival draft updated. <a target="_blank" href="%s">Preview Festival</a>', 'vca-asm' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) )
		);

		$messages['miscactions'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __( 'Miscellaneous activity updated. <a href="%s">View miscellaneous activity</a>', 'vca-asm' ), esc_url( get_permalink($post_ID) ) ),
			2 => __( 'Custom field updated.', 'vca-asm' ),
			3 => __( 'Custom field deleted.', 'vca-asm' ),
			4 => __( 'Miscellaneous activity updated.', 'vca-asm' ),
			5 => isset($_GET['revision']) ? sprintf( __( 'miscellaneous activity restored to revision from %s', 'vca-asm' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( 'Miscellaneous activity published. <a href="%s">View miscellaneous activity</a>', 'vca-asm' ), esc_url( get_permalink($post_ID) ) ),
			7 => __( 'Miscellaneous activity saved.', 'vca-asm' ),
			8 => sprintf( __( 'Miscellaneous activity submitted. <a target="_blank" href="%s">Preview miscellaneous activity</a>', 'vca-asm' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			9 => sprintf( __( 'Miscellaneous activity scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview miscellaneous activity</a>', 'vca-asm' ),
			date_i18n( get_option( 'date_format' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			10 => sprintf( __( 'Miscellaneous activity draft updated. <a target="_blank" href="%s">Preview miscellaneous activity</a>', 'vca-asm' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) )
		);
		$messages['misceducation'] = $messages['miscactions'];
		$messages['miscnetwork'] = $messages['miscactions'];

		$messages['nwgathering'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __( 'Network Gathering updated. <a href="%s">View Network Gathering</a>', 'vca-asm' ), esc_url( get_permalink($post_ID) ) ),
			2 => __( 'Custom field updated.', 'vca-asm' ),
			3 => __( 'Custom field deleted.', 'vca-asm' ),
			4 => __( 'Network Gathering updated.', 'vca-asm' ),
			5 => isset($_GET['revision']) ? sprintf( __( 'Network Gathering restored to revision from %s', 'vca-asm' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( 'Network Gathering published. <a href="%s">View Network Gathering</a>', 'vca-asm' ), esc_url( get_permalink($post_ID) ) ),
			7 => __( 'Network Gathering saved.', 'vca-asm' ),
			8 => sprintf( __( 'Network Gathering submitted. <a target="_blank" href="%s">Preview Network Gathering</a>', 'vca-asm' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			9 => sprintf( __( 'Network Gathering scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Network Gathering</a>', 'vca-asm' ),
			date_i18n( get_option( 'date_format' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			10 => sprintf( __( 'Network Gathering draft updated. <a target="_blank" href="%s">Preview Network Gathering</a>', 'vca-asm' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) )
		);

		$messages['goldeimerfestival'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __( 'Festival updated. <a href="%s">View Festival</a>', 'vca-asm' ), esc_url( get_permalink($post_ID) ) ),
			2 => __( 'Custom field updated.', 'vca-asm' ),
			3 => __( 'Custom field deleted.', 'vca-asm' ),
			4 => __( 'Festival updated.', 'vca-asm' ),
			5 => isset($_GET['revision']) ? sprintf( __( 'Festival restored to revision from %s', 'vca-asm' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( 'Festival published. <a href="%s">View Festival</a>', 'vca-asm' ), esc_url( get_permalink($post_ID) ) ),
			7 => __( 'Festival saved.', 'vca-asm' ),
			8 => sprintf( __( 'Festival submitted. <a target="_blank" href="%s">Preview Festival</a>', 'vca-asm' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			9 => sprintf( __( 'Festival scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Festival</a>', 'vca-asm' ),
			date_i18n( get_option( 'date_format' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			10 => sprintf( __( 'Festival draft updated. <a target="_blank" href="%s">Preview Festival</a>', 'vca-asm' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) )
		);

		return $messages;
	}

	/**
	 * Localize enqueued Javascript with post specific parameters
	 *
	 * Hooked with 'admin_enqueue_scripts'
	 *
	 * @return void
	 *
	 * @global object $current_user
	 * @global object $pagenow
	 * @global object $post
	 * @global object $vca_asm_geography
	 *
	 * @since 1.3
	 * @access public
	 */
	public function set_script_params()
	{
        /** @var vca_asm_geography $vca_asm_geography */
		global $pagenow, $post,	$vca_asm_geography;

		if( is_admin() && ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) ) {

			$validation_params = array(
				'errors' => array(
					'required' => _x( 'You have not filled out all the required fields', 'Validation Error', 'vca-asm' ),
					'numbers' => _x( 'Some fields only accept numeric values', 'Validation Error', 'vca-asm' ),
					'phone' => _x( 'A phone number you have entered is not valid', 'Validation Error', 'vca-asm' ),
					'end_app' => _x( 'The end of the application phase must come after its beginning', 'Validation Error', 'vca-asm' ),
					'date' => _x( 'Some of the entered dates are in an invalid order', 'Validation Error', 'vca-asm' )
				)
			);

			if ( $this->the_activity && $this->the_activity->is_activity ) {
				$activity_data = $this->the_activity->array_dump();
			} else {
				$this->the_activity = new VCA_ASM_Activity( $post->ID );
				if ( $this->the_activity && $this->the_activity->is_activity ) {
					$activity_data = $this->the_activity->array_dump();
				} else {
					$activity_data = array();
				}
			}

			$activity_data['strings'] = array(
				'confirmed_participants' => __( 'confirmed participants', 'vca-asm'),
				'split_into_cty' => _x( 'Split into city quotas?', 'Quotas', 'vca-asm' ),
				'cty_quotas_enabled' => _x( 'City Quotas have been enabled.', 'Quotas', 'vca-asm' ),
				'cty_quotas_current' => _x( 'Currently, %participants% participants are already registered by city quota(s).', 'Quotas', 'vca-asm' ),
				'cty_quotas_cannot' => _x( 'Quotas cannot be disabled anymore, unless those participants are removed again...', 'Quotas', 'vca-asm' ),
				'quota_total_slots' => _x( 'Total slots', 'Quotas', 'vca-asm' ),
				'ctr_available_direct' => _x( 'Available via country quota', 'Quotas', 'vca-asm' ),
				'add' => _x( 'add', 'Quotas', 'vca-asm' ),
				'remove' => _x( 'remove', 'Quotas', 'vca-asm' ),
				'no_cities' => _x( 'Can\'t.', 'Quotas', 'vca-asm' ) . ' ' . _x( 'This country does not have cities associated with it...', 'Quotas', 'vca-asm' )
			);

			$custom_fields = $this->custom_fields('all');

			$jqui_dynamic_params = array(
				'sliders' => array()
			);

			$sliders_cnt = 0;
			foreach( $custom_fields as $fields ) {
				foreach( $fields as $field ) {
					if ( 'slider' === $field['type'] ) {
						$value = get_post_meta( $post->ID, $field['id'], true );
						if ( '' == $value ) {
							$value = $field['min'];
						}
						$jqui_dynamic_params['sliders'][$sliders_cnt] = array(
							'id' => $field['id'],
							'value' => $value,
							'min' => $field['min'],
							'max' => $field['max'],
							'step' => $field['step']
						);
						$sliders_cnt++;
					} elseif ( 'total_slots' === $field['type'] ) {
						$activity_data['total_slider'] = array(
							'id' => $field['id'],
							'min' => $field['min'],
							'max' => $field['max'],
							'step' => $field['step']
						);
					} elseif ( 'ctr_quotas' === $field['type'] ) {
						$activity_data['ctr_slider'] = array(
							'id' => $field['id'],
							'min' => $field['min'],
							'max' => $field['max'],
							'step' => $field['step']
						);
						$activity_data['ctr_cty_switch_options'] = array(
							array(
								'label' => _x( 'No', 'Slots Selection', 'vca-asm' ),
								'value' => 'no'
							),
							array(
								'label' => _x( "Yes, define city quotas myself", 'Slots Selection', 'vca-asm' ),
								'value' => 'yes'
							),
							array(
								'label' => _x( 'Yes, notify relevant countries&apos; users to split available slots', 'Slots Selection', 'vca-asm' ) . ' ' . __( '(not yet implemented...)', 'vca-asm' ),
								'value' => 'notify'
							)
						);
					} elseif ( 'cty_slots' === $field['type'] ) {
						$activity_data['cty_slider'] = array(
							'id' => $field['id'],
							'min' => $field['min'],
							'max' => $field['max'],
							'step' => $field['step']
						);
						$activity_data['national_hierarchy'] = $vca_asm_geography->national_hierarchy;
						$activity_data['countries'] = $vca_asm_geography->countries;
						$activity_data['cities'] = $vca_asm_geography->cities;
					}
				}
			}

			wp_localize_script( 'vca-asm-admin-validation', 'validationParams', $validation_params );
			wp_localize_script( 'vca-asm-admin-jquery-ui-integration', 'jquiDynamicParams', $jqui_dynamic_params );
			wp_localize_script( 'vca-asm-admin-quotas', 'quotasParams', $activity_data );
			wp_localize_script( 'vca-asm-ctr-to-cty', 'nationalHierarchy', $vca_asm_geography->national_hierarchy );
		}

		if ( is_object( $post ) ) {
			$excel_params = array(
				'relpath' => VCA_ASM_RELPATH,
				'pID' => $post->ID
			);
			wp_localize_script( 'vca-asm-excel-export', 'excelParams', $excel_params );
		}
	}

    /**
     * Columns of post type tables in the backend
     *
     * Here: concerts
     * 'manage_edit-%post_type%_columns' filter callback
     * @return array $columns
     *
     * @internal param array $columns
     * @since 1.0
     * @access public
     */
	public function concert_columns()
	{
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Concert', 'vca-asm' ),
			'location' => __( 'Location', 'vca-asm' ),
			'timeframe' => __( 'Timeframe', 'vca-asm' ),
			'phase' => __( 'Application Phase', 'vca-asm' ),
			'slots' => __( 'Slots (Apps)', 'vca-asm' ),
			'registrations' => __( 'Accepted Applications', 'vca-asm' )
		);
		return $columns;
	}

    /**
     * Columns of post type tables in the backend
     *
     * Here: festivals
     * 'manage_edit-%post_type%_columns' filter callback
     * @return array $columns
     *
     * @internal param array $columns
     * @since 1.0
     * @access public
     */
	public function festival_columns()
	{
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Festival', 'vca-asm' ),
			'location' => __( 'Location', 'vca-asm' ),
			'timeframe' => __( 'Timeframe', 'vca-asm' ),
			'phase' => __( 'Application Phase', 'vca-asm' ),
			'slots' => __( 'Slots (Apps)', 'vca-asm' ),
			'registrations' => __( 'Accepted Applications', 'vca-asm' )
		);
		return $columns;
	}

    /**
     * Columns of post type tables in the backend
     *
     * Here: miscellaneous actions
     * 'manage_edit-%post_type%_columns' filter callback
     * @return array $columns
     *
     * @internal param array $columns
     * @since 1.0
     * @access public
     */
	public function miscactions_columns()
	{
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Activity', 'vca-asm' ),
			'location' => __( 'Location', 'vca-asm' ),
			'timeframe' => __( 'Timeframe', 'vca-asm' ),
			'phase' => __( 'Application Phase', 'vca-asm' ),
			'slots' => __( 'Slots (Apps)', 'vca-asm' ),
			'registrations' => __( 'Accepted Applications', 'vca-asm' )
		);
		return $columns;
	}

    /**
     * Columns of post type tables in the backend
     *
     * Here: network gathering
     * 'manage_edit-%post_type%_columns' filter callback
     * @return array $columns
     *
     * @internal param array $columns
     * @since 1.0
     * @access public
     */
	public function nwgathering_columns()
	{
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Network Gathering', 'vca-asm' ),
			'location' => __( 'Location', 'vca-asm' ),
			'timeframe' => __( 'Timeframe', 'vca-asm' ),
			'phase' => __( 'Application Phase', 'vca-asm' ),
			'slots' => __( 'Slots (Apps)', 'vca-asm' ),
			'registrations' => __( 'Accepted Applications', 'vca-asm' )
		);
		return $columns;
	}

    /**
     * Columns of post type tables in the backend
     *
     * Here: goldeimer events
     * 'manage_edit-%post_type%_columns' filter callback
     * @return array $columns
     *
     * @internal param array $columns
     * @since 1.0
     * @access public
     */
	public function goldeimerfestival_columns()
	{
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Festival', 'vca-asm' ),
			'location' => __( 'Location', 'vca-asm' ),
			'timeframe' => __( 'Timeframe', 'vca-asm' ),
			'phase' => __( 'Application Phase', 'vca-asm' ),
			'slots' => __( 'Slots (Apps)', 'vca-asm' ),
			'registrations' => __( 'Accepted Applications', 'vca-asm' )
		);
		return $columns;
	}

	/**
	 * Populate custom columns
	 *
	 * Hooked with 'manage_posts_custom_column'
	 *
	 * @param string $column
	 * @return void
	 *
	 * @since 1.0
	 * @access public
	 */
	public function custom_column( $column )
	{
        /** @var vca_asm_registrations $vca_asm_registrations */
		global $post, $vca_asm_registrations;

		switch ($column) {
		    case 'location':
				$meta = get_post_meta( $post->ID, 'location', true );
				echo $meta;
			break;

		    case 'timeframe':
				$meta = date( 'd.m.Y', intval( get_post_meta( $post->ID, 'start_act', true ) ) ) .
				' - ' . date( 'd.m.Y', intval( get_post_meta( $post->ID, 'end_act', true ) ) );
				echo $meta;
			break;

		    case 'phase':
				$meta = date( 'd.m.Y', intval( get_post_meta( $post->ID, 'start_app', true ) ) ) .
				' - ' . date( 'd.m.Y', intval( get_post_meta( $post->ID, 'end_app', true ) ) );
				echo $meta;
			break;

		    case 'slots':
				$slots = get_post_meta( $post->ID, 'total_slots', true );
				$apps = $vca_asm_registrations->get_activity_application_count( $post->ID );
				echo $slots . ' (' . $apps . ')';
			break;

		    case 'registrations':
				$reg_count = $vca_asm_registrations->get_activity_registration_count( $post->ID );
				echo $reg_count;
			break;
		}
	}

	/**
	 * Meta Boxes
	 *
	 * @return void
	 *
	 * @since 1.0
	 * @access public
	 */
	public function meta_boxes()
	{
		global $pagenow;
		$current_user = wp_get_current_user();
		$roles = $current_user->roles;

		add_meta_box(
			'vca-asm-meta',
			_x( 'The Concert', 'meta box title, concert', 'vca-asm' ),
			array( $this, 'box_meta' ),
			'concert',
			'normal',
			'high'
		);
		add_meta_box(
			'vca-asm-meta',
			_x( 'The Festival', 'meta box title, festival', 'vca-asm' ),
			array( $this, 'box_meta' ),
			'festival',
			'normal',
			'high'
		);
		add_meta_box(
			'vca-asm-meta',
			_x( 'The miscellaneous activity', 'meta box title, miscellaneous activity', 'vca-asm' ),
			array( $this, 'box_meta' ),
			'miscactions',
			'normal',
			'high'
		);
		add_meta_box(
			'vca-asm-meta',
			_x( 'The Network Gathering', 'meta box title, network gathering', 'vca-asm' ),
			array( $this, 'box_meta' ),
			'nwgathering',
			'normal',
			'high'
		);
		add_meta_box(
			'vca-asm-meta',
			_x( 'The Festival', 'meta box title, festival', 'vca-asm' ),
			array( $this, 'box_meta' ),
			'goldeimerfestival',
			'normal',
			'high'
		);

		foreach ( $this->activity_types as $activity_type ) {
			add_meta_box(
				'vca-asm-date',
				_x( 'Timeframe', 'meta box title, festival', 'vca-asm' ),
				array( $this, 'box_date' ),
				$activity_type,
				'normal',
				'high'
			);
			add_meta_box(
				'vca-asm-geo',
				_x( 'Association with Network Geography', 'meta box title, festival', 'vca-asm' ),
				array( $this, 'box_geo' ),
				$activity_type,
				'normal',
				'low'
			);
			add_meta_box(
				'vca-asm-contact-person',
				_x( 'Contact Person', 'meta box title, festival', 'vca-asm' ),
				array( $this, 'box_contact' ),
				$activity_type,
				'normal',
				'low'
			);
			if ( 'goldeimerfestival' !== $activity_type ) {
				add_meta_box(
					'vca-asm-tools',
					_x( 'Tools', 'meta box title, festival', 'vca-asm' ),
					array( $this, 'box_tools' ),
					$activity_type,
					'normal',
					'low'
				);
			}
			add_meta_box(
				'vca-asm-applications',
				_x( 'Application Phase', 'meta box title, festival', 'vca-asm' ),
				array( $this, 'box_application_phase' ),
				$activity_type,
				'advanced',
				'high'
			);
			if ( 'nwgathering' !== $activity_type ) {
				add_meta_box(
					'vca-asm-slots',
					_x( 'Applicant Pool &amp; Participant Slots', 'meta box title, festival', 'vca-asm' ),
					array( $this, 'box_slots_settings' ),
					$activity_type,
					'advanced',
					'high'
				);
			} else {
				add_meta_box(
					'vca-asm-slots',
					_x( 'Applicant Pool &amp; Participant Slots', 'meta box title, festival', 'vca-asm' ),
					array( $this, 'box_slots_settings_nwgathering' ),
					$activity_type,
					'advanced',
					'high'
				);
			}
			if( 'post-new.php' !== $pagenow ) {
				add_meta_box(
					'vca-asm-participants',
					_x( 'Applicants &amp; Participants', 'meta box title, festival', 'vca-asm' ),
					array( $this, 'box_participants' ),
					$activity_type,
					'advanced',
					'high'
				);
			}
		}
	}

	/**
	 * Custom Fields / Meta Box Content
	 *
	 * "Tools" Metabox
	 *
	 * @return void
	 *
	 * @since 1.0
	 * @access public
	 */
	public function box_tools()
	{
		$fields = $this->custom_fields('tools');
		require( VCA_ASM_ABSPATH . '/templates/admin-custom-fields.php' );
		echo $output;
	}

	/**
	 * Custom Fields / Meta Box Content
	 *
	 * "Slots Settings" Metabox
	 *
	 * @return void
	 *
	 * @since 1.0
	 * @access public
	 */
	public function box_slots_settings()
	{
		$fields = $this->custom_fields( 'slots-settings' );
		require( VCA_ASM_ABSPATH . '/templates/admin-custom-fields.php' );
		echo $output;
	}

	/**
	 * Custom Fields / Meta Box Content
	 *
	 * "Slots (Network Gathering)" Metabox
	 *
	 * @return void
	 *
	 * @since 1.0
	 * @access public
	 */
	public function box_slots_settings_nwgathering()
	{
		$fields = $this->custom_fields( 'slots-settings' );
		require( VCA_ASM_ABSPATH . '/templates/admin-custom-fields.php' );
		echo $output;
	}

	/**
	 * Custom Fields / Meta Box Content
	 *
	 * "Application Phase" Metabox
	 *
	 * @return void
	 *
	 * @since 1.0
	 * @access public
	 */
	public function box_application_phase()
	{
		$fields = $this->custom_fields( 'applications' );
		require( VCA_ASM_ABSPATH . '/templates/admin-custom-fields.php' );
		echo $output;
	}

	/**
	 * Custom Fields / Meta Box Content
	 *
	 * "Participants" Metabox
	 *
	 * @return void
	 *
	 * @since 1.0
	 * @access public
	 */
	public function box_participants()
	{
		$fields = $this->custom_fields( 'participants' );
		require( VCA_ASM_ABSPATH . '/templates/admin-custom-fields.php' );
		echo $output;
	}

	/**
	 * Custom Fields / Meta Box Content
	 *
	 * "Date / Timeframe" Metabox
	 *
	 * @return void
	 *
	 * @since 1.0
	 * @access public
	 */
	public function box_date()
	{
		$fields = $this->custom_fields( 'date' );
		require( VCA_ASM_ABSPATH . '/templates/admin-custom-fields.php' );
		echo $output;
	}

	/**
	 * Custom Fields / Meta Box Content
	 *
	 * "Metadata" Metabox
	 *
	 * @return void
	 *
	 * @since 1.0
	 * @access public
	 */
	public function box_meta()
	{
		$fields = $this->custom_fields( 'meta' );
		require( VCA_ASM_ABSPATH . '/templates/admin-custom-fields.php' );
		echo $output;
	}

	/**
	 * Custom Fields / Meta Box Content
	 *
	 * "Geographical Affiliation" Metabox
	 *
	 * @return void
	 *
	 * @since 1.0
	 * @access public
	 */
	public function box_geo()
	{
		$fields = $this->custom_fields( 'geo' );
		require( VCA_ASM_ABSPATH . '/templates/admin-custom-fields.php' );
		echo $output;
	}

	/**
	 * Custom Fields / Meta Box Content
	 *
	 * "Contact" Metabox
	 *
	 * @return void
	 *
	 * @since 1.0
	 * @access public
	 */
	public function box_contact()
	{
		$fields = $this->custom_fields( 'contact' );
		require( VCA_ASM_ABSPATH . '/templates/admin-custom-fields.php' );
		echo $output;
	}

    /**
     * Maps meta capabilities to wordpress core capabilities
     *
     * Meta capabilities are capabilities a user is granted on a per-post basis.
     *
     * @see: http://codex.wordpress.org/Function_Reference/map_meta_cap
     *
     * @since 1.0
     * @access public
     * @param $caps
     * @param $cap
     * @param $user_id
     * @param $args
     * @return array
     */
	public function vca_asm_map_meta_cap( $caps, $cap, $user_id, $args )
	{

		/* If editing, deleting, or reading an activity, get the post and post type object. */
		if (
			'vca_asm_edit_actions_activity' == $cap ||
			'vca_asm_delete_actions_activity' == $cap ||
			'vca_asm_read_actions_activity' == $cap ||
			'vca_asm_edit_education_activity' == $cap ||
			'vca_asm_delete_education_activity' == $cap ||
			'vca_asm_read_education_activity' == $cap ||
			'vca_asm_edit_network_activity' == $cap ||
			'vca_asm_delete_network_activity' == $cap ||
			'vca_asm_read_network_activity' == $cap ||
			'vca_asm_edit_goldeimer_activity' == $cap ||
			'vca_asm_delete_goldeimer_activity' == $cap ||
			'vca_asm_read_goldeimer_activity' == $cap
		) {
			$post = get_post( $args[0] );
			$post_type = get_post_type_object( $post->post_type );

			/* Set an empty array for the caps. */
			$caps = array();
		}

		/* If editing an activity, assign the required capability. */
		if (
			'vca_asm_edit_actions_activity' == $cap ||
			'vca_asm_edit_education_activity' == $cap ||
			'vca_asm_edit_network_activity' == $cap ||
			'vca_asm_edit_goldeimer_activity' == $cap
		) {
			if ( $user_id == $post->post_author ) {
				$caps[] = $post_type->cap->edit_posts;
			} else {
				$caps[] = $post_type->cap->edit_others_posts;
			}
		}

		/* If deleting an activity, assign the required capability. */
		elseif (
			'vca_asm_delete_actions_activity' == $cap ||
			'vca_asm_delete_education_activity' == $cap ||
			'vca_asm_delete_network_activity' == $cap ||
			'vca_asm_delete_goldeimer_activity' == $cap
		) {
			if ( $user_id == $post->post_author ) {
				$caps[] = $post_type->cap->delete_posts;
			} else {
				$caps[] = $post_type->cap->delete_others_posts;
			}
		}

		/* If reading a private activity, assign the required capability. */
		elseif (
			'vca_asm_read_actions_activity' == $cap ||
			'vca_asm_read_education_activity' == $cap ||
			'vca_asm_read_network_activity' == $cap ||
			'vca_asm_read_goldeimer_activity' == $cap
		) {
			if ( 'private' != $post->post_status ) {
				$caps[] = 'read';
			} elseif ( $user_id == $post->post_author ) {
				$caps[] = 'read';
			} else {
				$caps[] = $post_type->cap->read_private_posts;
			}
		}

		/* Return the capabilities required by the user. */
		return $caps;
	}

    /**
     * Saves the data
     *
     * @param bool|int $ID Post ID
     * @param bool|object $post WordPress Post Object
     *
     * @since 1.0
     * @access public
     * @return bool
     */
	public function save_meta( $ID = false, $post = false )
	{
        /** @var vca_asm_geography $vca_asm_geography */
	    global $pagenow, $wpdb, $vca_asm_geography;
		$current_user = wp_get_current_user();

		$all_fields = $this->custom_fields( 'all' );

		/* check autosave */
		if (
			( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) ||
			wp_is_post_revision( $post->ID) ||
			! in_array( $post->post_type, $this->activity_types ) ||
			! isset( $post->post_status ) ||
			! in_array( $post->post_status, array( 'publish', 'pending', 'draft', 'private', 'future' ) ) ||
			! isset(  $_POST['start_app'] ) // hacky fix for problem when moving activity to trash
		) {
			return isset( $post->ID ) ? $post->ID : false;
		}

		$current_post_type = isset( $_POST['post_type'] ) ? $_POST['post_type'] : $post->post_type;
		/* check permissions */
		if ( in_array( $current_post_type, array( 'concert', 'festival', 'miscactions' ) ) ) {
			if( ! current_user_can( 'vca_asm_edit_actions_activity', $post->ID ) ) {
				return $post->ID;
			}
		} elseif ( in_array( $current_post_type, array( 'misceducation' ) ) ) {
			if( ! current_user_can( 'vca_asm_edit_education_activity', $post->ID ) ) {
				return $post->ID;
			}
		} elseif ( in_array( $current_post_type, array( 'miscnetwork', 'nwgathering' ) ) ) {
			if( ! current_user_can( 'vca_asm_edit_network_activity', $post->ID ) ) {
				return $post->ID;
			}
		}

		$non_post_meta = array( 'applicants', 'waiting', 'participants' );

		$validation = new VCA_ASM_Validation();

		/* loop through fields and save the data */
		foreach ( $all_fields as $fields ) {
			foreach( $fields as $field ) {

				if( ! in_array( $field['type'], $non_post_meta ) && ( ! isset( $field['disabled'] ) || $field['disabled'] !== true ) ) {

					$old = get_post_meta( $post->ID, $field['id'], true );

					$new = isset( $_POST[$field['id']] ) ? $_POST[$field['id']] : '';

					if ( isset( $field['validation'] ) && 'post-new.php' !== $pagenow ) {
						$validation->is_valid( $_POST[$field['id']], array( 'type' => $field['validation'], 'id' => $field['validation'] ) );
						$new = $validation->sanitized_val ? $validation->sanitized_val : $new;
					}

					if( $field['id'] === 'contact_name' ) {
						$new = array();
						$new_email = array();
						$new_mobile = array();
						if( isset( $_POST['contact_name'] ) && is_array( $_POST['contact_name'] ) ) {
							foreach( $_POST['contact_name'] as $key => $name ) {
								if ( ! empty( $name ) ) {
									$new[] = $name;
									$new_email[] = $_POST['contact_email'][$key];
									$new_mobile[] = $_POST['contact_mobile'][$key];
								}
							}
						}
						$_POST['contact_email'] = $new_email;
						$_POST['contact_mobile'] = $new_mobile;
					}

					if( $field['type'] == 'ctr_quotas' ) {
						$new = array();
						$new_switch = array();
						if( isset( $_POST['ctr_quotas'] ) && is_array( $_POST['ctr_quotas'] ) ) {
							foreach( $_POST['ctr_quotas'] as $key => $quota ) {
								if ( 0 < $quota && isset( $_POST['quotas-ctr'][$key] ) ) {
									$new[$_POST['quotas-ctr'][$key]] = $quota;
									if ( isset( $_POST['ctr_cty_switch'][$key] ) ) {
										$new_switch[$_POST['quotas-ctr'][$key]] = $_POST['ctr_cty_switch'][$key];
									}
								}
							}
						}
						$ctr_quotas = $new;
					}

					if( $field['type'] == 'cty_slots' ) {
						$new = array();
						$ctr_slots = array();
						if( isset( $_POST['cty_slots'] ) && is_array( $_POST['cty_slots'] ) ) {
							foreach( $_POST['cty_slots'] as $key => $quota ) {
								if ( 0 < $quota && isset( $_POST['quotas-cty'][$key] ) ) {
									$new[$_POST['quotas-cty'][$key]] = $quota;
								}
							}
						}
						if ( ! empty( $new ) && ! empty( $ctr_quotas ) ) {
							$ctr_slots = $ctr_quotas;
							foreach ( $new as $geo => $slots ) {
								$nation = $vca_asm_geography->has_nation( $geo );
								if ( $nation ) {
									$ctr_slots[$nation] = $ctr_slots[$nation] - $slots;
								}
							}
						} elseif ( ! empty( $ctr_quotas ) ) {
							$ctr_slots = $ctr_quotas;
						}
					}

					if ( ( ! empty( $new ) || 0 === $new || '0' === $new ) && $new != $old ) {
						update_post_meta( $post->ID, $field['id'], $new );
					} elseif ( empty( $new ) && 0 !== $new && '0' !== $new && $old ) {
						delete_post_meta( $post->ID, $field['id'], $old );
					}

					if( isset( $new_switch ) ) {
						$old_switch =  get_post_meta( $post->ID, 'ctr_cty_switch', true );
						if( ! empty( $new_switch ) && $new_switch != $old_switch ) {
							update_post_meta( $post->ID, 'ctr_cty_switch', $new_switch );
						} elseif ( empty( $new_switch ) && $old_switch ) {
							delete_post_meta( $post->ID, 'ctr_cty_switch', $old_switch );
						}
						unset( $new_switch );
					}
					if( isset( $ctr_slots ) ) {
						$old_slots =  get_post_meta( $post->ID, 'ctr_slots', true );
						if( ! empty( $ctr_slots ) && $ctr_slots != $old_slots ) {
							update_post_meta( $post->ID, 'ctr_slots', $ctr_slots );
						} elseif ( empty( $ctr_slots ) && $old_slots ) {
							delete_post_meta( $post->ID, 'ctr_slots', $old_slots );
						}
						unset( $ctr_slots );
					}

					if( $field['id'] == 'delegate' ) {
						if( $new && $new != $old ) {
							$region_user_id = $wpdb->get_results(
								"SELECT user_id FROM " .
								$wpdb->prefix . "vca_asm_geography " .
								"WHERE id = " . $_POST['city'], ARRAY_A
							);
							$region_user_id = $region_user_id[0]['user_id'];
							if( ! empty( $region_user_id ) ) {
								$activity_data = array();
								$activity_data['ID'] = $post->ID;
								$activity_data['post_author'] = $region_user_id;
								if( $post->post_author != $region_user_id ) {
									// unhook this method so it doesn't loop infinitely
									remove_action( 'save_post', array( $this, 'save_meta' ) );
									wp_update_post( $activity_data );
									// re-hook this method
									add_action( 'save_post', array( $this, 'save_meta' ), 10, 2 );
								}
							}
						} elseif ( empty( $new ) && $old ) {
							$activity_data = array();
							$activity_data['ID'] = $post->ID;
							$activity_data['post_author'] = $current_user->ID;
							if( $post->post_author != $current_user->ID ) {
									// unhook this method so it doesn't loop infinitely
									remove_action( 'save_post', array( $this, 'save_meta' ) );
									wp_update_post( $activity_data );
									// re-hook this method
									add_action( 'save_post', array( $this, 'save_meta' ), 10, 2 );
							}
						}
					}

					if( $field['id'] == 'city' ) {
						if( $new && $new != $old ) {
							$old_delegation = get_post_meta( $post->ID, 'delegate', true );
							$new_delegation = isset( $_POST['delegate'] ) ? $_POST['delegate'] : '';
							if( $old_delegation == $new_delegation && $new_delegation == 'delegate' ) {
								$geo_user_id = $wpdb->get_results(
									"SELECT user_id FROM " .
									$wpdb->prefix . "vca_asm_geography " .
									"WHERE id = " . $_POST['city'], ARRAY_A
								);
								$geo_user_id = $geo_user_id[0]['user_id'];
								$activity_data = array();
								$activity_data['ID'] = $post->ID;
								if( ! empty( $geo_user_id ) ) {
									$activity_data['post_author'] = $geo_user_id;
									if( $post->post_author != $geo_user_id ) {
										// unhook this method so it doesn't loop infinitely
										remove_action( 'save_post', array( $this, 'save_meta' ) );
										wp_update_post( $activity_data );
										// re-hook this method
										add_action( 'save_post', array( $this, 'save_meta' ), 10, 2 );
									}
								} else {
									$activity_data['post_author'] = $current_user->ID;
									if( $post->post_author != $current_user->ID ) {
										// unhook this method so it doesn't loop infinitely
										remove_action( 'save_post', array( $this, 'save_meta' ) );
										wp_update_post( $activity_data );
										// re-hook this method
										add_action( 'save_post', array( $this, 'save_meta' ), 10, 2 );
									}
								}
							}
						}
					}
				}
			}
		}

		if ( $validation->has_errors ) {
			$validation->set_errors();
		}
	}

	/**
	 * Displays errors, if any
	 *
	 * Hooked with 'admin_notices'
	 *
	 * @return void
	 *
	 * @since 1.3
	 * @access public
	 */
	public function notice_handler()
	{
        /** @var vca_asm_admin $vca_asm_admin */
		global $current_user, $vca_asm_admin;

		$notices = get_transient( 'admin_notices_'.$current_user->ID );

		$output = '';

	    if ( $notices ) {
			unset( $_GET['message'] );
			$output = $vca_asm_admin->convert_messages( $notices );
		}
		echo $output;

		$this->clear_notices();
	}

	/**
	 * Clears errors from the database
	 *
	 * Posts are saved (by Post/Redirect/Get),
	 * errors are generated and output during consecutive but different requests.
	 * Hooked with 'admin_footer'
	 *
	 * @return void
	 *
	 * @since 1.3
	 * @access public
	 */
	public function clear_notices()
	{
		global $current_user;

		delete_transient( 'admin_notices_'.$current_user->ID );
	}

	/* ============================= UTILITY METHODS ============================= */

	/**
	 * Determines whether an activity is relevant to an (administrative) user
	 *
	 * @param int $activity	ID of the activity checked against
	 * @param mixed $user		(optional) WP_User Object or ID of the user in question
	 * @param array $args		(optional) arguments, i.e. what to check for
	 *
	 * @return bool		Is the activity relevant to the user?
	 *
	 * @since 1.5
	 * @access public
	 */
	public function is_relevant_to_user( $activity, $user = 0, $args = array() )
	{
		if ( empty( $user ) ) {
			global $current_user;
			$user = $current_user->ID;
			$user_obj = $current_user;
		} else {
			if ( is_object( $user ) ) {
				$user_obj = $user;
				$user = $user_obj->ID;
			} else {
				$user_obj = get_userdata( $user );
			}
		}

		$default_args = array(
			'delegations' => true,
			'quotas' => true
		);
		extract( $args = wp_parse_args( $args, $default_args ) );

		$activity_type = get_post_type( $activity );
		$department = $this->departments_by_activity[$activity_type];

		if ( $user_obj->has_cap( 'vca_asm_manage_' . $department . '_global' ) ) {
			return true;
		}

		if ( $user_obj->has_cap( 'vca_asm_manage_' . $department . '_nation' ) ) {
			$user_nation = get_user_meta( $user, 'nation', true );
			$activity_nation = get_post_meta( $activity, 'nation', true );
			if ( $user_nation === $activity_nation ) {
				return true;
			}
		}

		if ( true === $delegations || true === $quotas ) {
			$user_city = get_user_meta( $user, 'city', true );
		}

		if ( true === $delegations && $user_obj->has_cap( 'vca_asm_manage_' . $department ) ) {
			$activity_city = get_post_meta( $activity, 'city', true );
			$activity_delegation = get_post_meta( $activity, 'delegate', true );
			if ( 'delegate' === $activity_delegation && $user_city === $activity_city ) {
				return true;
			}
		}

		if ( true === $quotas && $user_obj->has_cap( 'vca_asm_manage_' . $department ) ) {
			$cty_slots = get_post_meta( $activity, 'cty_slots', true );
			if ( is_array( $cty_slots ) && array_key_exists( $user_city, $cty_slots ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns array of activity IDs based on phase & type
	 *
	 * @param array $args				(optional) arguments - phase and type
	 * @return array $activities	Array of (int) activity IDs
	 *
	 * @since 1.3
	 * @access public
	 */
	public function query_activities( $args = array() )
	{
		$default_args = array(
			'phase' => 'all',
			'type' => 'all'
		);
		extract( wp_parse_args( $args, $default_args ), EXTR_SKIP );

		$type = in_array( $type, $this->activity_types ) ? $type : $this->activity_types;

		$meta_query = array();
		if ( in_array( $phase, array( 'bf', 'app', 'ft', 'pst' ) ) ) {
			switch ( $phase ) {
				case 'bf':
					$meta_query[] = array(
						'key' => 'start_app',
						'value' => time(),
						'compare' => '>',
						'type' => 'numeric'
					);
				break;

				case 'app':
					$meta_query['relation'] = 'AND';
					$meta_query[] = array(
						'key' => 'start_app',
						'value' => time(),
						'compare' => '<=',
						'type' => 'numeric'
					);
					$meta_query[] = array(
						'key' => 'end_app',
						'value' => time() - 86400,
						'compare' => '>',
						'type' => 'numeric'
					);
				break;

				case 'ft':
					$meta_query['relation'] = 'AND';
					$meta_query[] = array(
						'key' => 'end_app',
						'value' => time() - 86400,
						'compare' => '<=',
						'type' => 'numeric'
					);
					$meta_query[] = array(
						'key' => 'end_act',
						'value' => time(),
						'compare' => '>=',
						'type' => 'numeric'
					);
				break;

				case 'pst':
					$meta_query[] = array(
						'key' => 'end_act',
						'value' => time(),
						'compare' => '<',
						'type' => 'numeric'
					);
				break;
			}

			$activities = get_posts( array(
				'post_type' => $type,
				'post_status' => 'publish',
				'numberposts' => -1,
				'orderby' => 'title',
				'order' => 'ASC',
				'meta_query' => $meta_query
			));

		} else {

			$activities = get_posts( array(
				'post_type' => $type,
				'post_status' => 'publish',
				'numberposts' => -1,
				'orderby' => 'title',
				'order' => 'ASC'
			));

		}

		return $activities;
	}

	/**
	 * Returns activities to populate dropdown menu
	 *
	 * Based on phase & type
	 *
	 * @param array $args				(optional) arguments - phase, type, and whether capabilities matter
	 * @return array $options_array		Nested array of strings to populate HTML select tags
	 *
	 * @since 1.3
	 * @access public
	 */
	public function options_array_activities( $args = array() )
	{
		global $current_user;

		$default_args = array(
			'phase' => 'all',
			'type' => 'all',
			'check_caps' => false
		);
		extract( wp_parse_args( $args, $default_args ), EXTR_SKIP );

		$activities = $this->query_activities( array(
			'phase' => $phase,
			'type' => $type
		));

		$options_array = array();

		foreach ( $activities as $activity ) {
			if (
				false === $check_caps ||
				$this->is_relevant_to_user( $activity->ID, $current_user )
			) {
				$options_array[] = array(
					'label' => $activity->post_title . ' (' . strftime( '%d.%m.%Y', get_post_meta( $activity->ID, 'start_act', true ) ) . ')',
					'value' => $activity->ID
				);
			}
		}

		return $options_array;
	}

	/**
	 * Returns the phase of an activity
	 *
	 * @since 1.3
	 * @access public
	 *
	 * @param int $id
	 *
	 * @return string $phase
	 */
	public function get_phase( $id )
	{

		$start_app = get_post_meta( $id, 'start_app', true );
		$end_app = get_post_meta( $id, 'end_app', true );
		$end_act = get_post_meta( $id, 'end_act', true );
		$current = time();

		if ( $current > $end_act ) {
			return 'pst';
		} elseif ( $current > $end_app ) {
			return 'ft';
		} elseif ( $current > $start_app ) {
			return 'app';
		} else {
			return 'bf';
		}
	}
}

endif; // class exists

?>