<?php

/**
 * VcA_ASM_Activities class.
 *
 * This class contains properties and methods for the activity post types.
 *
 * @package VcA Activity & Supporter Management
 * @since 1.0
 */

if ( ! class_exists( 'VcA_ASM_Activities' ) ) :

class VcA_ASM_Activities {

	/**
	 * Nested arrays of custom fields
	 *
	 * @since 1.0
	 * @access private
	 */
	private function custom_fields( $group = 'all' ) {
		global $vca_asm_regions;

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
			'date' => array (
				array(
					'label'	=> _x( 'Start Date', 'Timeframe Meta Box', 'vca-asm' ),
					'desc'	=> _x( 'The beginning of the festival', 'Timeframe', 'vca-asm' ),
					'id'	=> 'start_date',
					'type'	=> 'date'
				),
				array(
					'label'	=> _x( 'End Date', 'Timeframe Meta Box', 'vca-asm' ),
					'desc'	=> _x( 'The last day of the festival', 'Timeframe', 'vca-asm' ),
					'id'	=> 'end_date',
					'type'	=> 'date'
				)
			),
			'finances' => array (
				array(
					'label'	=> _x( 'Cups', 'Finances Meta Box', 'vca-asm' ),
					'desc'	=> _x( 'Amount of cups collected', 'Finances Meta Box', 'vca-asm' ),
					'id'	=> 'finances_amount_cups',
					'type'	=> 'text'
				),
				array(
					'label'	=> _x( 'Donations via Cups', 'Finances Meta Box', 'vca-asm' ),
					'desc'	=> _x( 'Donations gathered via cup collecting (in Euro)', 'Finances Meta Box', 'vca-asm' ),
					'id'	=> 'finances_donations_cups',
					'type'	=> 'text'
				),
				array(
					'label'	=> _x( 'Extra Donations', 'Finances Meta Box', 'vca-asm' ),
					'desc'	=> _x( 'Donations gathered in addition to collected cups (in Euro)', 'Finances Meta Box', 'vca-asm' ),
					'id'	=> 'finances_donations_extra',
					'type'	=> 'text'
				),
				array(
					'label'	=> _x( 'Total Donations', 'Finances Meta Box', 'vca-asm' ),
					'desc'	=> _x( 'Cups x Deposit + Extra Donations (in Euro), calculated automatically', 'Finances Meta Box', 'vca-asm' ),
					'id'	=> 'finances_donations_total',
					'type'	=> 'text',
					'disabled' => true
				),
				array(
					'label'	=> _x( 'Merch Sales', 'Finances Meta Box', 'vca-asm' ),
					'desc'	=> _x( 'Merchandising Revenue (in Euro)', 'Finances Meta Box', 'vca-asm' ),
					'id'	=> 'finances_merch',
					'type'	=> 'text'
				)
			),
			'region' => array (
				array(
					'label'	=> _x( 'Region', 'Region Meta Box', 'vca-asm' ),
					'desc'	=> _x( 'Associate with the activity with a region. This is irrelevant to slot allocation and only matters for categorization and sorting.', 'Region Meta Box', 'vca-asm' ),
					'id'	=> 'region',
					'type'	=> 'select',
					'options' => $vca_asm_regions->select_options( _x( 'global', 'Regions', 'vca-asm' ) )
				),
				array(
					'label'	=> _x( 'Delegation', 'Region Meta Box', 'vca-asm' ),
					'desc'	=> _x( "Delegate to the selected region's Head Of(s). If you choose to do so, the region's Head Of User can edit the festival, as well as accept and deny applications globally. If the selected region does not have a Head Of User assigned, this option is irrelevant.", 'Region Meta Box', 'vca-asm' ),
					'id'	=> 'delegate',
					'type'	=> 'checkbox',
					'option' => _x( 'Yes, delegate', 'Region Meta Box', 'vca-asm' ),
					'value' => 'delegate'
				)
			),
			'meta' => array (
				array(
					'label'=> _x( 'Location', 'Festival Data Meta Box', 'vca-asm' ),
					'desc'	=> _x( 'Where the festival takes place', 'Festival Data Meta Box', 'vca-asm' ),
					'id'	=> 'location',
					'type'	=> 'text'
				),
				array(
					'label'=> _x( 'Website', 'Festival Data Meta Box', 'vca-asm' ),
					'desc'	=> _x( "The festival's Website", 'Festival Data Meta Box', 'vca-asm' ),
					'id'	=> 'website',
					'type'	=> 'text'
				),
				array(
					'label'=> _x( 'Directions', 'Festival Data Meta Box', 'vca-asm' ),
					'desc'	=> _x( 'Description of how to reach the festival grounds', 'Festival Data Meta Box', 'vca-asm' ) . ' ' . __( 'You can use &lt;br /&gt; tags for line breaks.', 'vca-asm' ),
					'id'	=> 'directions',
					'type'	=> 'textarea'
				),
				array(
					'label'=> _x( 'Parking Lot(s)', 'Festival Data Meta Box', 'vca-asm' ),
					'desc'	=> _x( 'How many parking lots are where?', 'Festival Data Meta Box', 'vca-asm' ) . ' ' . __( 'You can use &lt;br /&gt; tags for line breaks.', 'vca-asm' ),
					'id'	=> 'parking',
					'type'	=> 'textarea'
				)
			),
			'slot-allocation' => array(
				array(
					'label' => _x( 'Open Slots', 'Applications Meta Box', 'vca-asm' ),
					'desc' => _x( 'Allocate open slots either globally, regionally, or both', 'Applications Meta Box', 'vca-asm' ),
					'id' => 'slots',
					'type' => 'slots',
					'options' => $vca_asm_regions->select_options( _x( 'global', 'Regions', 'vca-asm' ) ),
					'min' => 1,
					'max' => 50,
					'step' => 1
				)
			),
			'applications' => array(
				array(
					'label'	=> _x( 'Start of application phase', 'Applications Meta Box', 'vca-asm' ),
					'desc'	=> _x( 'Before this date, the activity will not be displayed to supporters', 'Applications', 'vca-asm' ),
					'id'	=> 'start_app',
					'type'	=> 'date'
				),
				array(
					'label'	=> _x( 'Application Deadline', 'Applications Meta Box', 'vca-asm' ),
					'desc'	=> _x( 'The end of the application phase', 'Applications', 'vca-asm' ),
					'id'	=> 'end_app',
					'type'	=> 'date'
				),
				array(
					'label' => _x( 'Applications', 'Applications Meta Box', 'vca-asm' ),
					'id' => 'applications',
					'type' => 'applications'
				),
				array(
					'label' => _x( 'Email', 'Registrations Meta Box', 'vca-asm' ),
					'id' => 'email_link',
					'type' => 'email_link',
					'group' => 'applicants',
					'text' => __( 'Send an email to all current applicants', 'vca-asm' )
				),
				array(
					'label' => _x( 'Email', 'Registrations Meta Box', 'vca-asm' ),
					'id' => 'email_link',
					'type' => 'email_link',
					'group' => 'applicants_global',
					'text' => __( 'Send an email to all current applicants to the global contingent', 'vca-asm' )
				),
				array(
					'label' => _x( 'Email', 'Registrations Meta Box', 'vca-asm' ),
					'id' => 'email_link',
					'type' => 'email_link',
					'group' => 'waiting',
					'text' => __( 'Send an email to all supporters currently on the waiting list', 'vca-asm' )
				)
			),
			'registrations' => array(
				array(
					'label' => _x( 'Accepted Applicants', 'Registrations Meta Box', 'vca-asm' ),
					'id' => 'registrations',
					'type' => 'registrations'
				),
				array(
					'label' => _x( 'Email', 'Registrations Meta Box', 'vca-asm' ),
					'id' => 'email_link',
					'type' => 'email_link',
					'group' => 'participants',
					'text' => __( 'Send an email to all accepted applicants', 'vca-asm' )
				)
				,array(
					'label' => _x( 'Download Data', 'Registrations Meta Box', 'vca-asm' ),
					'id' => 'excel_link',
					'type' => 'excel_link'
				)
			),
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

		if( $group === 'all' ) {
			return $custom_fields;
		} elseif( isset( $custom_fields[$group] ) ) {
			return $custom_fields[$group];
		} else {
			return array();
		}
	}

	/**
	 * Sets up all activities
	 *
	 * Registers all activity post types
	 * Initiates creation of custom fields and metaboxes, initiates meta capability mapping.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function setup_activities() {

		$capabilities = array(
			'publish_posts' => 'vca_asm_publish_activities',
			'edit_posts' => 'vca_asm_edit_activities',
			'edit_others_posts' => 'vca_asm_edit_others_activities',
			'delete_posts' => 'vca_asm_delete_activities',
			'delete_others_posts' => 'vca_asm_delete_others_activities',
			'read_private_posts' => 'vca_asm_read_private_activities',
			'edit_post' => 'vca_asm_edit_activity',
			'delete_post' => 'vca_asm_delete_activity',
			'read_post' => 'vca_asm_read_activity'
		);

		$labels = array(
			'name' => _x( 'Festivals', 'post type general name', 'vca-asm' ),
			'singular_name' => _x( 'Festival', 'post type singular name', 'vca-asm' ),
			'add_new' => _x( 'Add New', 'festival', 'vca-asm' ),
			'add_new_item' => __( 'Add New Festival', 'vca-asm' ),
			'edit_item' => __( 'Edit Festival', 'vca-asm' ),
			'new_item' => __( 'New Festival', 'vca-asm' ),
			'all_items' => __( 'Festivals', 'vca-asm' ),
			'view_item' => __( 'View Festival', 'vca-asm' ),
			'search_items' => __( 'Search Festivals', 'vca-asm' ),
			'not_found' =>  __( 'No Festivals found', 'vca-asm' ),
			'not_found_in_trash' => __( 'No Festivals found in Trash', 'vca-asm' ),
			'parent_item_colon' => '',
			'menu_name' => 'Festivals'
		);

		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => 'vca-asm-activities',
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'vca_asm_activity',
			'capabilities' => $capabilities,
			'has_archive' => true,
			'hierarchical' => false,
			'menu_position' => 10001,
			'menu_icon' => VCA_ASM_RELPATH . 'admin/festivals-icon.png',
			'supports' => array( 'title' )
		);

		add_filter( 'map_meta_cap', array( &$this, 'vca_asm_map_meta_cap' ), 10, 4 );
		register_post_type( 'festival', $args );
		add_action( 'add_meta_boxes', array( &$this, 'meta_boxes' ) );
		add_action( 'save_post', array( &$this, 'save_meta' ) );
		add_action( 'admin_head', array( &$this, 'custom_scripts' ) );
		add_filter( 'manage_edit-festival_columns', array( &$this, 'columns' ) );
		add_action( 'manage_posts_custom_column',  array( &$this, 'custom_column' ) );
		add_filter( 'gettext', array( &$this, 'admin_ui_text_alterations' ), 10, 2 );
		add_filter( 'post_updated_messages', array( &$this, 'admin_ui_updated_messages' ) );
	}

	/**
	 * Alters UI strings to fit post type
	 *
	 * @since 1.1
	 * @access public
	 */
	public function admin_ui_text_alterations( $translation, $text ) {
		global $post_type;

		if ( is_admin() && 'festival' == $post_type ) {
			if( 'Enter title here' == $text ) {
				return __( 'Name of Festival', 'vca-asm' );
			}
			if( 'Update' == $text ) {
				return __( 'Update Festival', 'vca-asm' );
			}
			if( 'Publish' == $text ) {
				return __( 'Publish Festival', 'vca-asm' );
			}
			if( 'Submit for Review' == $text ) {
				return __( 'Submit to Byrro for Review', 'vca-asm' );
			}
		}

		return $translation;
	}

	/**
	 * Alters message strings to fit post type
	 *
	 * @since 1.1
	 * @access public
	 */
	public function admin_ui_updated_messages( $messages ) {
		global $post, $post_ID;

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
			10 => sprintf( __( 'Festival draft updated. <a target="_blank" href="%s">Preview Festival</a>', 'vca-asm' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		);

		return $messages;
	}

	/**
	 * Datepicker & Slider (jQuery-UI) integration
	 *
	 * @todo move to external js file
	 *
	 * @since 1.0
	 * @access public
	 */
	public function custom_scripts() {
		global $post, $current_user;
		get_currentuserinfo();
		$custom_fields = $this->custom_fields('all');

		$output = '<script type="text/javascript">';

		$limit_date = 0;

		foreach( $custom_fields as $fields ) {
			foreach( $fields as $field ) {
				if( $field['type'] == 'date' && $limit_date != 1 ) {
					$output .= "jQuery(function() {
						jQuery( '.datepicker' ).datepicker({
							dateFormat: 'dd.mm.yy',
							monthNames: [
								'Januar', 'Februar', 'März',
								'April', 'Mai', 'Juni',
								'Juli', 'August', 'September',
								'Oktober', 'November', 'December'
							],
							dayNamesMin: [ 'So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa' ]
						});
					});";
					$limit_date = 1;
				} elseif ( $field['type'] == 'slider' ) {
					$value = get_post_meta( $post->ID, $field['id'], true );
					if ( $value == '' ) {
						$value = $field['min'];
					}
					$output .= 'jQuery(function() {
							jQuery( "#'.$field['id'].'-slider" ).slider({
								value: '.$value.',
								min: '.$field['min'].',
								max: '.$field['max'].',
								step: '.$field['step'].',
								slide: function( event, ui ) {
									jQuery( "#'.$field['id'].'" ).val( ui.value );
								}
							});
						});';
				} elseif ( $field['type'] == 'slots' ) {
					$output .= '
						function slotsSlider() {
							jQuery( ".slots-cf li" ).each(function(){
								var sliderPosition = jQuery(this).children("#slots").val();
								jQuery(this).children("#slots-slider").slider({
									value: sliderPosition,
									min: '.$field['min'].',
									max: '.$field['max'].',
									step: '.$field['step'].',
									slide: function( event, ui ) {
										jQuery(this).siblings("#slots").val( ui.value );
									}
								});
							});
						}
						jQuery(document).ready(function() {
							slotsSlider();
						});';
				}
			}
		}

		$output .= '</script>';

		$output .='<script type="text/javascript">' .
				'function exportExcel() {' .
					'jQuery("#excel-frame").attr("src","' .
						VCA_ASM_RELPATH . 'ajax/export-excel.php?activity=' . $post->ID .
					'");' .
					'return false;' .
				'}' .
			'</script>';

		echo $output;
	}

	/**
	 * Columns of festival table in the backend
	 *
	 * @since 1.0
	 * @access public
	 */
	public function columns( $columns ) {
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
	 * @since 1.0
	 * @access public
	 */
	public function custom_column( $column ){
		global $post, $wpdb, $vca_asm_registrations;

		switch ($column) {
		    case 'location':
				$meta = get_post_meta( $post->ID, 'location', true );
				echo $meta;
			break;

		    case 'timeframe':
				$meta = date( 'd.m.Y', intval( get_post_meta( $post->ID, 'start_date', true ) ) ) .
				' - ' . date( 'd.m.Y', intval( get_post_meta( $post->ID, 'end_date', true ) ) );
				echo $meta;
			break;

		    case 'phase':
				$meta = date( 'd.m.Y', intval( get_post_meta( $post->ID, 'start_app', true ) ) ) .
				' - ' . date( 'd.m.Y', intval( get_post_meta( $post->ID, 'end_app', true ) ) );
				echo $meta;
			break;

		    case 'slots':
				$slots_arr = get_post_meta( $post->ID, 'slots', true );
				$slots = 0;
				if( ! empty( $slots_arr ) ) {
					foreach( $slots_arr as $slots_partial ) {
						$slots = $slots + $slots_partial;
					}
				}
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
	 * @since 1.0
	 * @access public
	 */
	public function meta_boxes() {
		global $current_user;
		get_currentuserinfo();

		add_meta_box(
			'vca-asm-meta',
			_x( 'The Festival', 'meta box title, festival', 'vca-asm' ),
			array( &$this, 'box_meta' ),
			'festival',
			'normal',
			'high'
		);
		add_meta_box(
			'vca-asm-date',
			_x( 'Timeframe', 'meta box title, festival', 'vca-asm' ),
			array( &$this, 'box_date' ),
			'festival',
			'normal',
			'high'
		);
		if( $current_user->has_cap('vca_asm_edit_others_activities') ) {
			add_meta_box(
				'vca-asm-region',
				_x( 'Region', 'meta box title, festival', 'vca-asm' ),
				array( &$this, 'box_region' ),
				'festival',
				'normal',
				'low'
			);
		}
		add_meta_box(
			'vca-asm-tools',
			_x( 'Tools', 'meta box title, festival', 'vca-asm' ),
			array( &$this, 'box_tools' ),
			'festival',
			'normal',
			'low'
		);
		add_meta_box(
			'vca-asm-contact-person',
			_x( 'Contact Person', 'meta box title, festival', 'vca-asm' ),
			array( &$this, 'box_contact' ),
			'festival',
			'normal',
			'low'
		);
		add_meta_box(
			'vca-asm-slots',
			_x( 'Slot Allocation', 'meta box title, festival', 'vca-asm' ),
			array( &$this, 'box_slot_allocation' ),
			'festival',
			'advanced',
			'high'
		);
		add_meta_box(
			'vca-asm-applications',
			_x( 'Applications', 'meta box title, festival', 'vca-asm' ),
			array( &$this, 'box_applications' ),
			'festival',
			'advanced',
			'high'
		);
		add_meta_box(
			'vca-asm-registrations',
			_x( 'Accepted Applications', 'meta box title, festival', 'vca-asm' ),
			array( &$this, 'box_registrations' ),
			'festival',
			'advanced',
			'high'
		);
		//add_meta_box(
		//	'vca-asm-finances',
		//	_x( 'Finances', 'meta box title, festival', 'vca-asm' ),
		//	array( &$this, 'box_finances' ),
		//	'festival',
		//	'advanced',
		//	'low'
		//);
	}

	/**
	 * Custom Fields / Meta Box Content
	 * One function per meta box
	 *
	 * @since 1.0
	 * @access public
	 */
	public function box_tools() {
		global $current_user;
		get_currentuserinfo();

		$fields = $this->custom_fields('tools');
		$region = intval( get_user_meta( $current_user->ID, 'region', true ) );

		/* Region + Head Of Hack, dirty, to be moved */
		if( ! $current_user->has_cap('vca_asm_edit_others_activities') ) {
			$fields[] = array(
				'id'	=> 'region',
				'type'	=> 'hidden',
				'value' => $region
			);
			$fields[] = array(
				'id'	=> 'delegate',
				'type'	=> 'hidden',
				'value' => 'delegate'
			);
		}

		require( VCA_ASM_ABSPATH . '/templates/admin-custom-fields.php' );
		echo $output;
	}

	public function box_slot_allocation() {
		$fields = $this->custom_fields('slot-allocation');

		require( VCA_ASM_ABSPATH . '/templates/admin-custom-fields.php' );
		echo $output;
	}

	public function box_applications() {
		$fields = $this->custom_fields('applications');

		require( VCA_ASM_ABSPATH . '/templates/admin-custom-fields.php' );
		echo $output;
	}

	public function box_registrations() {
		$fields = $this->custom_fields('registrations');

		require( VCA_ASM_ABSPATH . '/templates/admin-custom-fields.php' );
		echo $output;
	}

	public function box_date() {
		$fields = $this->custom_fields('date');

		require( VCA_ASM_ABSPATH . '/templates/admin-custom-fields.php' );
		echo $output;
	}

	public function box_meta() {
		$fields = $this->custom_fields( 'meta' );

		require( VCA_ASM_ABSPATH . '/templates/admin-custom-fields.php' );
		echo $output;
	}

	public function box_region() {
		$fields = $this->custom_fields('region');

		require( VCA_ASM_ABSPATH . '/templates/admin-custom-fields.php' );
		echo $output;
	}
	public function box_contact() {
		$fields = $this->custom_fields('contact');

		require( VCA_ASM_ABSPATH . '/templates/admin-custom-fields.php' );
		echo $output;
	}

	//public function box_finances() {
	//	$fields = $this->custom_fields('finances');
	//
	//	require( VCA_ASM_ABSPATH . '/templates/admin-custom-fields.php' );
	//	echo $output;
	//}

	/**
	 * Maps meta capabilities to wordpress core capabilities
	 *
	 * Meta capabilities are capabilities a user is granted on a per-post basis.
	 * See: http://codex.wordpress.org/Function_Reference/map_meta_cap
	 *
	 * @since 1.0
	 * @access public
	 */
	public function vca_asm_map_meta_cap( $caps, $cap, $user_id, $args ) {

		/* If editing, deleting, or reading an activity, get the post and post type object. */
		if ( 'vca_asm_edit_activity' == $cap || 'vca_asm_delete_activity' == $cap || 'vca_asm_read_activity' == $cap ) {
			$post = get_post( $args[0] );
			$post_type = get_post_type_object( $post->post_type );

			/* Set an empty array for the caps. */
			$caps = array();
		}

		/* If editing an activity, assign the required capability. */
		if ( 'vca_asm_edit_activity' == $cap ) {
			if ( $user_id == $post->post_author )
				$caps[] = $post_type->cap->edit_posts;
			else
				$caps[] = $post_type->cap->edit_others_posts;
		}

		/* If deleting an activity, assign the required capability. */
		elseif ( 'vca_asm_delete_activity' == $cap ) {
			if ( $user_id == $post->post_author )
				$caps[] = $post_type->cap->delete_posts;
			else
				$caps[] = $post_type->cap->delete_others_posts;
		}

		/* If reading a private activity, assign the required capability. */
		elseif ( 'vca_asm_read_activity' == $cap ) {
			if ( 'private' != $post->post_status )
				$caps[] = 'read';
			elseif ( $user_id == $post->post_author )
				$caps[] = 'read';
			else
				$caps[] = $post_type->cap->read_private_posts;
		}

		/* Return the capabilities required by the user. */
		return $caps;
	}

	/**
	 * Saves the data
	 *
	 * @since 1.0
	 * @access public
	 */
	public function save_meta( $post_id ) {
	    global $current_user, $post, $post_type, $wpdb, $vca_asm_registrations;
		get_currentuserinfo();

		/* check autosave */
		if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
			return $post->ID;
		}

		/* check permissions */
		if ( 'festival' == $_POST['post_type'] ) {
			if( ! current_user_can( 'vca_asm_edit_activity', $post->ID ) ) {
				return $post->ID;
			}
		}

		/* loop through fields and save the data */
		foreach ( $this->custom_fields('all') as $fields ) {
			foreach( $fields as $field ) {

				if( $field['type'] != 'applications' && $field['type'] != 'registrations' && $field['disabled'] != true ) {

					$old = get_post_meta( $post->ID, $field['id'], true );
					if( $field['type'] == 'date' ) {
						$date = explode( '.', $_POST[$field['id']] );
						$new = mktime( 0, 0, 0,
							intval( $date[1] ),
							intval( $date[0] ),
							intval( $date[2] )
						);
					} elseif( $field['type'] == 'slots' ) {
						$new = array();
						if( isset( $_POST['slots'] ) && is_array( $_POST['slots'] ) ) {
							foreach( $_POST['slots'] as $key => $slots ) {
								$new[$_POST['slots-region'][$key]] = $slots;
							}
						}
					} else {
						$new = $_POST[$field['id']];
					}

					if( $new && $new != $old ) {
						update_post_meta( $post->ID, $field['id'], $new );
					} elseif ( '' == $new && $old ) {
						delete_post_meta( $post->ID, $field['id'], $old );
					}

					if( $field['id'] == 'delegate' ) {
						if( $new && $new != $old ) {
							$region_user_id = $wpdb->get_results(
								"SELECT user_id FROM " .
								$wpdb->prefix . "vca_asm_regions " .
								"WHERE id = " . $_POST['region'], ARRAY_A
							);
							$region_user_id = $region_user_id[0]['user_id'];
							if( ! empty( $region_user_id ) ) {
								$activity_data = array();
								$activity_data['ID'] = $post->ID;
								$activity_data['post_author'] = $region_user_id;
								if( $post->post_author != $region_user_id ) {
									wp_update_post( $activity_data );
								}
							}
						} elseif ( '' == $new && $old ) {
							$activity_data = array();
							$activity_data['ID'] = $post->ID;
							$activity_data['post_author'] = $current_user->ID;
							if( $post->post_author != $current_user->ID ) {
								wp_update_post( $activity_data );
							}
						}
					}

					if( $field['id'] == 'region' ) {
						if( $new && $new != $old ) {
							$old_delegation = get_post_meta( $post->ID, 'delegate', true );
							$new_delegation = $_POST['delegate'];
							if( $old_delegation == $new_delegation && $new_delegation == 'delegate' ) {
								$region_user_id = $wpdb->get_results(
									"SELECT user_id FROM " .
									$wpdb->prefix . "vca_asm_regions " .
									"WHERE id = " . $_POST['region'], ARRAY_A
								);
								$region_user_id = $region_user_id[0]['user_id'];
								$activity_data = array();
								$activity_data['ID'] = $post->ID;
								if( ! empty( $region_user_id ) ) {
									$activity_data['post_author'] = $region_user_id;
									if( $post->post_author != $region_user_id ) {
										wp_update_post( $activity_data );
									}
								} else {
									$activity_data['post_author'] = $current_user->ID;
									if( $post->post_author != $current_user->ID ) {
										wp_update_post( $activity_data );
									}
								}
							}
						}
					}
				}
			}
		}

		/* Save application stati */
		if( isset( $_POST['applications'] ) && isset( $_POST['todo_app'] ) ) {
			foreach( $_POST['applications'] as $app_user_id ) {
				if( $_POST['todo_app'] == 'deny' ) {
					$vca_asm_registrations->deny_application( $post->ID, intval( $app_user_id ) );
				} else {
					$user_mem_status =  intval( get_user_meta( intval( $app_user_id ), 'membership', true ) );
					if( $user_mem_status == 2 ) {
						$region = intval( get_user_meta( intval( $app_user_id ), 'region', true ) );
					} else {
						$region = 0;
					}
					$free = $vca_asm_registrations->get_free_slots( $post->ID, $region );
					if( $free > 0 ) {
						$vca_asm_registrations->accept_application( $post->ID, intval( $app_user_id ) );
					}
				}
			}
		}

		/* Revoke registrations */
		if( isset( $_POST['registrations'] ) && isset( $_POST['todo_revoke'] ) ) {
			foreach( $_POST['registrations'] as $app_user_id ) {
				if( $_POST['todo_revoke'] == 'revoke' ) {
					$vca_asm_registrations->revoke_registration( $post->ID, intval( $app_user_id ) );
				}
			}
		}
	}

	/**
	 * PHP4 style constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function VCA_ASM_Activities() {
		$this->__construct();
	}

	/**
	 * PHP5 style constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
		$this->setup_activities();
	}
}

endif; // class exists

?>