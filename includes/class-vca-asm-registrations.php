<?php

/**
 * VCA_ASM_Registrations class.
 * This class contains properties and methods to allocate supporters to events/activities.
 *
 * @todo more efficient SQL
 *
 * @package VcA Activity & Supporter Management
 * @since 1.0
 *
 * Structure:
 * - Controllers / DB Operations
 * - Activity Methods (Supporter Data via Activity ID)
 * - Supporter Methods (Activity Data via User ID)
 * - Utility
 */

if ( ! class_exists( 'VCA_ASM_Registrations' ) ) :

class VCA_ASM_Registrations
{

	/* ============================= CONTROLLERS / DB OPERATIONS ============================= */

    /**
     * Writes an application to the database
     *
     * @param NULL|int $activity_id (optional) the (user-)ID of the supporter
     * @param string $notes (optional) notes sent with the application by the supporter
     * @global object $current_user
     * @return bool $success
     *
     * @global object $current_user
     * @global object $wpdb
     * @global object $vca_asm_mailer
     *
     * @since 1.0
     * @access public
     */
	public function set_application( $activity_id, $notes = '', $supporter = NULL )
	{
        /** @var vca_asm_mailer $vca_asm_mailer */
		global $current_user, $wpdb, $vca_asm_mailer;

		/* default action (if called from frontend) */
		if( $supporter === NULL ) {
			$current_user = wp_get_current_user();
			$supporter = $current_user->ID;
		}
		$activity_id = intval( $activity_id );

		$metadata = $this->scope_from_activity( $activity_id );
		extract( $metadata );

		$applications_query = $wpdb->get_results(
			"SELECT id FROM " .
			$wpdb->prefix . "vca_asm_applications " .
			"WHERE supporter = " . $supporter . " AND activity = " . $activity_id .
			" LIMIT 1", ARRAY_A
		);

		if ( empty( $applications_query[0]['id'] ) ) {
			$success = $wpdb->insert(
				$wpdb->prefix."vca_asm_applications",
				array(
					'activity' => $activity_id,
					'state' => 0,
					'supporter' => $supporter,
					'notes' => $notes
				),
				array( '%d', '%d', '%d', '%s' )
			);
		}

		$vca_asm_mailer->auto_response(
			$supporter,
			'applied',
			array(
				'scope' => $scope,
				'from_name' => $from_name,
				'from_email' => $from_email,
				'activity' => get_the_title( $activity_id ),
				'activity_id' => $activity_id
			)
		);

		return $success;
	}

	/**
	 * Deny application
	 *
	 * Sets a supporters application state to 1 (denied application / waiting list)
	 * The supporter is "moved to the waiting list"
	 *
	 * @param int $activity_id				the (post-)ID of the activity
	 * @param int $supporter_id				the (user-)ID of the supporter
	 * @return bool $success
	 *
	 * @global object $wpdb
	 * @global object $vca_asm_mailer
	 *
	 * @since 1.0
	 * @access public
	 */
	public function deny_application( $activity_id, $supporter_id ) {
        /** @var vca_asm_mailer $vca_asm_mailer */
		global $wpdb, $vca_asm_mailer;

		$success = $wpdb->update(
			$wpdb->prefix . 'vca_asm_applications',
			array(
				'state'			=> 1
			),
			array(
				'activity' 		=> $activity_id,
				'supporter'		=> $supporter_id
			),
			array(
				'%d'
			),
			array(
				'%d',
				'%d'
			)
		);

		$metadata = $this->scope_from_activity( $activity_id );
		extract( $metadata );

		$vca_asm_mailer->auto_response(
			$supporter_id,
			'denied',
			array(
				'scope' => $scope,
				'from_name' => $from_name,
				'from_email' => $from_email,
				'activity' => get_the_title( $activity_id ),
				'activity_id' => $activity_id
			)
		);

		return $success;
	}

	/**
	 * Accepts an application
	 *
	 * Moves a supporter's application entry to the wp_vca_asm_registrations table
	 *
	 * @param int $activity_id				the (post-)ID of the activity
	 * @param int $supporter_id				the (user-)ID of the supporter
	 * @return bool $success
	 *
	 * @global object $wpdb
	 * @global object $vca_asm_mailer
	 *
	 * @since 1.0
	 * @access public
	 */
	public function accept_application( $activity_id, $supporter_id )
	{
        /** @var vca_asm_mailer $vca_asm_mailer */
		global $wpdb, $vca_asm_mailer;

		$the_activity = new VCA_ASM_Activity( $activity_id, array( 'minimalistic' => true ) );

		$note = $wpdb->get_results(
			"SELECT notes FROM " .
			$wpdb->prefix . "vca_asm_applications " .
			"WHERE activity=" . $activity_id . " AND supporter=" . $supporter_id . ' LIMIT 1', ARRAY_A
		);
		$note = isset( $note[0]['notes'] ) ? $note[0]['notes'] : '';

		$wpdb->query(
			'DELETE FROM ' . $wpdb->prefix . 'vca_asm_applications ' .
			'WHERE activity = ' . $activity_id . ' AND supporter = ' . $supporter_id . ' LIMIT 1'
		);

		$avoid_dupes = $wpdb->get_results(
			"SELECT id FROM " .
			$wpdb->prefix . "vca_asm_registrations " .
			"WHERE activity=" . $activity_id . " AND supporter=" . $supporter_id . ' LIMIT 1', ARRAY_A
		);

		$success = false;
		if( empty( $avoid_dupes[0]['id'] ) ) {
			$contingent = $the_activity->is_eligible( $supporter_id );

			if ( is_numeric( $contingent ) ) {
				$success = $wpdb->insert(
					$wpdb->prefix . 'vca_asm_registrations',
					array(
						'activity' 		=> $activity_id,
						'supporter' 	=> $supporter_id,
						'contingent'	=> $contingent,
						'notes' 		=> $note
					),
					array(
						'%d',
						'%d',
						'%d',
						'%s'
					)
				);

				$metadata = $this->scope_from_activity( $activity_id );
				extract( $metadata );

				$vca_asm_mailer->auto_response(
					$supporter_id,
					'accepted',
					array(
						'scope' => $scope,
						'from_name' => $from_name,
						'from_email' => $from_email,
						'activity' => get_the_title( $activity_id ),
						'activity_id' => $activity_id
					)
				);
			}
		}

		return $success;
	}

	/**
	 * Moves a registration to the table for past events
	 *
	 * Moves a supporter's registration entry to the wp_vca_asm_registrations_old table
	 * Old registrations are saved as serialized arrays
	 *
	 * @param int $activity_id				the (post-)ID of the activity
	 * @param int $supporter_id				the (user-)ID of the supporter
	 * @return bool $success
	 *
	 * @global object $wpdb
	 *
	 * @since 1.0
	 * @access public
	 */
	public function move_registration_to_old( $activity_id, $supporter_id )
	{
		global $wpdb;

		$data = $wpdb->get_results(
			"SELECT notes, contingent FROM " .
			$wpdb->prefix . "vca_asm_registrations " .
			"WHERE activity = " . $activity_id . " AND supporter = " . $supporter_id . " LIMIT 1", ARRAY_A
		);
		$note = $data[0]['notes'];
		$quota = $data[0]['contingent'];

		$wpdb->query(
			"DELETE FROM " . $wpdb->prefix . "vca_asm_registrations " .
			"WHERE activity = " . $activity_id . " AND supporter = " . $supporter_id . " LIMIT 1"
		);

		$wpdb->insert(
			$wpdb->prefix . 'vca_asm_registrations_old',
			array(
				'supporter' => $supporter_id,
				'activity' => $activity_id,
				'notes' => $note,
				'quota' => $quota
			),
			array(
				'%d',
				'%s',
				'%s',
				'%d'
			)
		);

		return true;
	}

	/**
	 * Moves an application to the table for past applications
	 *
	 * Moves a supporter's application entry to the wp_vca_asm_applications_old table
	 * Old applications are saved as serialized arrays
	 *
	 * @param int $activity_id				the (post-)ID of the activity
	 * @param int $supporter_id				the (user-)ID of the supporter
	 * @return bool $success
	 *
	 * @global object $wpdb
	 *
	 * @since 1.0
	 * @access public
	 */
	public function move_application_to_old( $activity_id, $supporter_id )
	{
		global $wpdb;

		$note = $wpdb->get_results(
			"SELECT notes FROM " .
			$wpdb->prefix . "vca_asm_applications " .
			"WHERE activity = " . $activity_id . " AND supporter = " . $supporter_id . " LIMIT 1", ARRAY_A
		);
		$note = $note[0]['notes'];
		$wpdb->query(
			'DELETE FROM ' . $wpdb->prefix . 'vca_asm_applications ' .
			'WHERE activity = ' . $activity_id . ' AND supporter = ' . $supporter_id . ' LIMIT 1'
		);

		$wpdb->insert(
			$wpdb->prefix . 'vca_asm_applications_old',
			array(
				'supporter'		=> $supporter_id,
				'activity'	=> $activity_id,
				'notes' => $note
			),
			array(
				'%d',
				'%s',
				'%s'
			)
		);

		return true;
	}

	/**
	 * Revokes a supporter's application
	 *
	 * As long as a supporter's application has not been accepted yet,
	 * he or she may cancel his or her application at any time.
	 * This method is used in application items displayed in a supporter's
	 * "my activities" view
	 *
	 * @param int $activity_id				the (post-)ID of the activity
	 * @param int $supporter_id				the (user-)ID of the supporter
	 * @return bool $success
	 *
	 * @global object $wpdb
	 *
	 * @since 1.0
	 * @access public
	 */
	public function revoke_application( $activity_id, $supporter_id = NULL )
	{
		global $wpdb;

		/* default action (if called from frontend) */
		if( $supporter_id === NULL ) {
			$current_user = wp_get_current_user();
			$supporter_id = $current_user->ID;
		}

		$success = $wpdb->query(
			'DELETE FROM ' . $wpdb->prefix . 'vca_asm_applications ' .
			'WHERE activity = ' . $activity_id . ' AND supporter = ' . $supporter_id . ' LIMIT 1'
		);

		return $success;
	}

	/**
	 * Revokes a supporter's registration
	 *
	 * Once a supporter is registered to an event,
	 * He or she can no longer remove him/herself from the event.
	 * This function is used in the admin backend.
	 *
	 * @param int $activity_id				the (post-)ID of the activity
	 * @param int $supporter_id				the (user-)ID of the supporter
	 * @return bool $success
	 *
	 * @global object $wpdb
	 *
	 * @since 1.0
	 * @access public
	 */
	public function revoke_registration( $activity_id, $supporter_id )
	{
        /** @var vca_asm_mailer $vca_asm_mailer */
		global $wpdb, $vca_asm_mailer;

		$success = $wpdb->query(
			'DELETE FROM ' . $wpdb->prefix . 'vca_asm_registrations ' .
			'WHERE activity = ' . $activity_id . ' AND supporter = ' . $supporter_id . ' LIMIT 1'
		);

		$metadata = $this->scope_from_activity( $activity_id );
		extract( $metadata );

		$vca_asm_mailer->auto_response(
			$supporter_id,
			'reg_revoked',
			array(
				'scope' => $scope,
				'from_name' => $from_name,
				'from_email' => $from_email,
				'activity' => get_the_title( $activity_id ),
				'activity_id' => $activity_id
			)
		);

		return $success;
	}

	/* ============================= ACTIVITY METHODS (SUPPORTER DATA VIA ACTIVITY ID) ============================= */

	/**
	 * Returns an activities free slots for a certain user
	 * defaults to global slots if no regional slots are assigned
	 *
	 * @param int $activity_id			the (post-)ID of the activity
	 * @param int $user_id				the user ID
	 * @return int $free
	 *
	 * @global object $wpdb
	 * @global object $vca_asm_geography
	 *
	 * @see template class VCA_ASM_Activity
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_free_slots( $activity_id, $user_id )
	{

		$the_activity = new VcA_ASM_Activity( $activity_id );

		$city = intval( get_user_meta( $user_id, 'city', true ) );
		$nation = intval( get_user_meta( $user_id, 'nation', true ) );

		if ( ! empty( $city ) && array_key_exists( $city, $the_activity->cty_slots ) ) {
			$participants = isset( $the_activity->participants_count_by_slots[$city] ) ?
				$the_activity->participants_count_by_slots[$city] :
				0;
			$free = $the_activity->cty_slots[$city] - $participants;
		} else {
			if ( ! empty( $nation ) && array_key_exists( $nation, $the_activity->ctr_slots ) ) {
				$participants = isset( $the_activity->participants_count_by_slots[$nation] ) ?
					$the_activity->participants_count_by_slots[$nation] :
					0;
				$free = $the_activity->ctr_slots[$nation] - $participants;
			} else {
				$participants = isset( $the_activity->participants_count_by_slots[0] ) ?
					$the_activity->participants_count_by_slots[0] :
					0;
				$free = $the_activity->global_slots - $participants;
			}
		}

		return $free;
	}

	/**
	 * Returns an array containing all supporters that have applied to an activity
	 *
	 * @param int $activity_id		the (post-)ID of the activity
	 * @return array
     *
	 * @global object $wpdb
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_activity_applications( $activity_id )
	{
		global $wpdb;

		$applications = $wpdb->get_results(
			"SELECT supporter FROM " .
			$wpdb->prefix . "vca_asm_applications " .
			"WHERE activity = " . $activity_id . " AND state = 0", ARRAY_A
		);
		$supporters = array();
		foreach( $applications as $application ) {
			$supporters[] = $application['supporter'];
		}

		return $supporters;
	}

	/**
	 * Returns the count of current applications to an activity
	 *
	 * @param int $activity_id		the (post-)ID of the activity
	 * @param string|int $city		(optional) (geographical) ID of the city, defaults to 'all'
	 * @return int $count
	 *
	 * @global object $wpdb
	 *
	 * @since 1.1
	 * @access public
	 */
	public function get_activity_application_count( $activity_id, $city = 'all' )
	{
		global $wpdb;

		if( $city === 'all' ) {
			$count = $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM " .
				$wpdb->prefix . "vca_asm_applications " .
				"WHERE activity= %d AND state = 0", $activity_id
			) );
		} else {
			$count = 0;
			$applications = $wpdb->get_results(
				"SELECT supporter FROM " .
				$wpdb->prefix . "vca_asm_applications " .
				"WHERE activity=" . $activity_id . " AND state = 0", ARRAY_A
			);
			foreach( $applications as $supporter ) {
				$supp_region = get_user_meta( $supporter['supporter'], 'city', true );
				$supp_mem_status = get_user_meta( $supporter['supporter'], 'membership', true );
				if( $supp_region == $city && $supp_mem_status == 2 ) {
					$count++;
				}
			}
		}

		return $count;
	}

	/**
	 * Returns an array containing all supporters that are on the waiting list for an activity
	 *
	 * @param int $activity_id		the (post-)ID of the activity
	 * @return array $supporters
	 *
	 * @global object $wpdb
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_activity_waiting( $activity_id )
	{
		global $wpdb;

		$applications = $wpdb->get_results(
			"SELECT supporter FROM " .
			$wpdb->prefix . "vca_asm_applications " .
			"WHERE activity = " . $activity_id . " AND state = 1", ARRAY_A
		);
		$supporters = array();
		foreach( $applications as $application ) {
			$supporters[] = $application['supporter'];
		}

		return $supporters;
	}

	/**
	 * Returns the count of current supporters on the waiting list for an activity
	 *
	 * @param int $activity		the (post-)ID of the activity
	 * @param string|int $city		(optional) (geographical) ID of the city, defaults to 'all'
	 * @return int $count
	 *
	 * @global object $wpdb
	 *
	 * @since 1.1
	 * @access public
	 */
	public function get_activity_waiting_count( $activity, $city = 'all' )
	{
		global $wpdb;

		if( $city === 'all' ) {
			$count = $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM " .
				$wpdb->prefix . "vca_asm_applications " .
				"WHERE activity= %d AND state = 1", $activity
			) );
		} else {
			$count = 0;
			$waiting = $wpdb->get_results(
				"SELECT supporter FROM " .
				$wpdb->prefix . "vca_asm_applications " .
				"WHERE activity=" . $activity . " AND state = 1", ARRAY_A
			);
			foreach( $waiting as $supporter ) {
				$supp_region = get_user_meta( $supporter['supporter'], 'city', true );
				$supp_mem_status = get_user_meta( $supporter['supporter'], 'membership', true );
				if( $supp_region == $city && $supp_mem_status == 2 ) {
					$count++;
				}
			}
		}

		return $count;
	}

	/**
	 * Returns an array containing all supporters that are registered for an activity,
	 * i.e. whose applications have been accepted
	 *
	 * @param int $activity_id		the (post-)ID of the activity
	 * @param array $args			(optional) parameters defining the format of the return, see code
	 * @return array $participants
	 *
	 * @global object $wpdb
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_activity_participants( $activity_id, $args = array() )
	{
		global $wpdb;

		$default_args = array(
			'by_contingent' => false	// whether to nest the returned array in quota related chunks
		);
		extract( wp_parse_args( $args, $default_args ), EXTR_SKIP );

		$participants_query = $wpdb->get_results(
			"SELECT supporter, contingent FROM " .
			$wpdb->prefix . "vca_asm_registrations " .
			"WHERE activity = " . $activity_id , ARRAY_A
		);
		$participants = array();
		foreach( $participants_query as $participant ) {
			if ( ! $by_contingent ) {
				$participants[] = $participant['supporter'];
			} else {
				if ( ! array_key_exists( $participant['contingent'], $participants ) ) {
					$participants[$participant['contingent']] = array();
				}
				$participants[$participant['contingent']][] = $participant['supporter'];
			}
		}

		return $participants;
	}

	/**
	 * Returns an array containing all supporters that has unsuccessfully applied to a past activity
	 *
	 * @param int $activity_id		the (post-)ID of the activity
	 * @return array $supporters
	 *
	 * @global object $wpdb
	 *
	 * @since 1.3
	 * @access public
	 */
	public function get_activity_applications_old( $activity_id )
	{
		global $wpdb;

		$applications = $wpdb->get_results(
			"SELECT supporter FROM " .
			$wpdb->prefix . "vca_asm_applications_old " .
			"WHERE activity = " . $activity_id, ARRAY_A
		);
		$supporters = array();
		foreach( $applications as $application ) {
			$supporters[] = $application['supporter'];
		}

		return $supporters;
	}

	/**
	 * Returns an array containing all supporters that have participated in a past activity
	 *
	 * @param int $activity		the (post-)ID of the activity
	 * @param array $args			(optional) parameters defining the format of the return, see code
	 * @return array $participants
	 *
	 * @global object $wpdb
	 *
	 * @since 1.3
	 * @access public
	 */
	public function get_activity_participants_old( $activity, $args = array() )
	{
		global $wpdb;

		$default_args = array(
			'by_contingent' => false	// whether to nest the returned array in quota related chunks
		);
		extract( wp_parse_args( $args, $default_args ), EXTR_SKIP );

		$participants_query = $wpdb->get_results(
			"SELECT supporter, quota FROM " .
			$wpdb->prefix . "vca_asm_registrations_old " .
			"WHERE activity = " . $activity, ARRAY_A
		);
		$participants = array();
		foreach( $participants_query as $participant ) {
			if ( ! $by_contingent ) {
				$participants[] = $participant['supporter'];
			} else {
				if ( ! array_key_exists( $participant['quota'], $participants ) ) {
					$participants[$participant['quota']] = array();
				}
				$participants[$participant['quota']][] = $participant['supporter'];
			}
		}

		return $participants;
	}

	/**
	 * Returns the count of current registrations to an activity
	 *
	 * @param int $activity		the (post-)ID of the activity
	 * @param string|int $city		(optional) (geographical) ID of the city, defaults to 'all'
	 * @return int $count
	 *
	 * @since 1.1
	 * @access public
	 */
	public function get_activity_registration_count( $activity, $city = 'all' )
	{
		global $wpdb;

		if ( time() > get_post_meta( $activity, 'end_act', true ) ) {
			$tbl_name = 'vca_asm_registrations_old';
			$quota_str = 'quota';
		} else {
			$tbl_name = 'vca_asm_registrations';
			$quota_str = 'contingent';
		}

		if( $city === 'all' ) {
			$count = $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM " .
				$wpdb->prefix . $tbl_name . " " .
				"WHERE activity= %d", $activity
			) );
		} else {
			$count = 0;
			$registrations = $wpdb->get_results(
				"SELECT " . $quota_str . " FROM " .
				$wpdb->prefix . $tbl_name . " " .
				"WHERE activity=" . $activity, ARRAY_A
			);
			foreach( $registrations as $supporter ) {
				if( $city == $supporter['contingent'] ) {
					$count++;
				}
			}
		}

		return $count;
	}

	/* ============================= SUPPORTER METHODS (ACTIVITY DATA VIA SUPPORTER ID) ============================= */

	/**
	 * Returns an array containing a all of a supporter's non-past events
	 *
	 * @param NULL|int $supporter		the (user-)ID in question
	 * @return array $events			array of activity (post-)IDs
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_supporter_all( $supporter = NULL )
	{
		global $wpdb;

		/* default action (if called from frontend) */
		if( $supporter === NULL ) {
			global $current_user;
			$supporter = $current_user->ID;
		}

		$applications = $wpdb->get_results(
			"SELECT activity FROM " .
			$wpdb->prefix . "vca_asm_applications " .
			"WHERE supporter = " . $supporter, ARRAY_N
		);
		$registrations = $wpdb->get_results(
			"SELECT activity FROM " .
			$wpdb->prefix . "vca_asm_registrations " .
			"WHERE supporter = " . $supporter, ARRAY_N
		);
		$events = array();
		foreach( $applications as $application ) {
			$events[] = $application[0];
		}
		foreach( $registrations as $registration ) {
			$events[] = $registration[0];
		}

		return $events;
	}

	/**
	 * Returns an array containing a supporter's applications
	 *
	 * @param NULL|int $supporter		the (user-)ID in question
	 * @return array $events			array of activity (post-)IDs
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_supporter_applications( $supporter = NULL )
	{
		global $wpdb;

		/* default action (if called from frontend) */
		if( $supporter === NULL ) {
			global $current_user;
			$supporter = $current_user->ID;
		}

		$applications = $wpdb->get_results(
			"SELECT activity FROM " .
			$wpdb->prefix . "vca_asm_applications " .
			"WHERE supporter = " . $supporter . " AND state = 0", ARRAY_A
		);
		$events = array();
		foreach( $applications as $application ) {
			$activity = intval( $application['activity'] );
			$start_date = intval( get_post_meta( $activity, 'start_act', true ) );
			$current_time = time();
			if( $start_date > $current_time ) {
				$events[] = $activity;
			} else {
				$this->move_application_to_old( $activity, $supporter );
			}
		}

		return $events;
	}

	/**
	 * Returns an array containing a supporter's denied applications
	 *
	 * @param NULL|int $supporter		the (user-)ID in question
	 * @return array $events			array of activity (post-)IDs
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_supporter_waiting( $supporter = NULL )
	{
		global $wpdb;

		/* default action (if called from frontend) */
		if( $supporter === NULL ) {
			global $current_user;
			$supporter = $current_user->ID;
		}

		$waiting = $wpdb->get_results(
			"SELECT activity FROM " .
			$wpdb->prefix . "vca_asm_applications " .
			"WHERE supporter = " . $supporter . " AND state = 1", ARRAY_A
		);
		$events = array();
		foreach( $waiting as $wait ) {
			$activity = intval( $wait['activity'] );
			$start_date = intval( get_post_meta( $activity, 'start_act', true ) );
			$current_time = time();
			if( $start_date > $current_time ) {
				$events[] = $activity;
			} else {
				$this->move_application_to_old( $activity, $supporter );
			}
		}

		return $events;
	}

	/**
	 * Returns an array containing a supporter's current (with an end date later or equal today's) registrations
	 *
	 * If it finds a registration to an event that lies in the past,
	 * it moves the db entry to wp_vca_asm_registrations_old
	 *
	 * @param NULL|int $supporter		the (user-)ID in question
	 * @return array $events			array of activity (post-)IDs
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_supporter_registrations( $supporter = NULL )
	{
		global $wpdb;

		/* default action (if called from frontend) */
		if( $supporter === NULL ) {
			global $current_user;
			$supporter = $current_user->ID;
		}

		$registrations = $wpdb->get_results(
			"SELECT activity FROM " .
			$wpdb->prefix . "vca_asm_registrations " .
			"WHERE supporter = " . $supporter, ARRAY_A
		);
		$events = array();
		foreach( $registrations as $registration ) {
			$activity = intval( $registration['activity'] );
			$end_date = intval( get_post_meta( $activity, 'end_act', true ) );
			$current_time = time();
			if( $end_date > $current_time ) {
				$events[] = $activity;
			} else {
				$this->move_registration_to_old( $activity, $supporter );
			}
		}

		return $events;
	}

	/**
	 * Returns an array containing a supporter's past registrations
	 *
	 * @param NULL|int $supporter		the (user-)ID in question
	 * @return array $registrations			array of activity (post-)IDs
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_supporter_registrations_old( $supporter = NULL )
	{
		global $wpdb;

		/* default action (if called from frontend) */
		if ( $supporter === NULL ) {
			global $current_user;
			$supporter = $current_user->ID;
		}

		$registrations_query = $wpdb->get_results(
			"SELECT activity FROM " .
			$wpdb->prefix . "vca_asm_registrations_old " .
			"WHERE supporter = " . $supporter, ARRAY_A
		);

		$registrations = array();
		if ( ! empty( $registrations_query ) ) {
			foreach ( $registrations_query as $registration ) {
				$registrations[] = $registration['activity'];
			}
		}

		return $registrations;
	}

	/**
	 * Returns an array containing a supporter's past (denied) applications
	 *
	 * @param NULL|int $supporter		the (user-)ID in question
	 * @return array $applications			array of activity (post-)IDs
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_supporter_applications_old( $supporter = NULL )
	{
		global $wpdb;

		/* default action (if called from frontend) */
		if( $supporter === NULL ) {
			$current_user = wp_get_current_user();
			$supporter = $current_user->ID;
		}

		$applications_query = $wpdb->get_results(
			"SELECT activity FROM " .
			$wpdb->prefix . "vca_asm_applications_old " .
			"WHERE supporter = " . $supporter, ARRAY_A
		);

		$applications = array();
		if ( ! empty( $applications_query ) ) {
			foreach ( $applications_query as $application ) {
				$applications[] = $application['activity'];
			}
		}

		return $applications;
	}

	/* ============================= UTILITY METHODS ============================= */

	/**
	 * Prepares (and returns) metadata to write an application to the database
	 *
	 * @param int $activity_id				the (post-)ID of the activity
	 * @return array $metadata
	 *
	 * @global object $current_user
	 * @global object $wpdb
	 * @global object $vca_asm_activities
	 * @global object $vca_asm_mailer
	 *
	 * @since 1.0
	 * @access public
	 */
	public function scope_from_activity( $activity_id )
	{
        /** @var vca_asm_activities $vca_asm_activities */
		global $current_user, $vca_asm_activities;

		$type = get_post_type( $activity_id );

		if ( ! empty( $vca_asm_activities->departments_by_activity[$type] ) && 'goldeimer' === $vca_asm_activities->departments_by_activity[$type] ) {
			$scope = 'ge';
			$from_name = __( 'Goldeimer', 'vca-asm' );
			$from_email = _x( 'no-reply@goldeimer.de', 'Utility Translation', 'vca-asm' );
		} else {
			$scope = get_post_meta( $activity_id, 'nation', true );
			$scope = ! empty( $scope ) ? $scope : get_user_meta( $current_user->ID, 'nation', true );
			/* ToDo: Move into Database (Geo Settings) */
			switch ( $scope ) {
				case 42:
					$from_name = 'Viva con Agua';
					$from_email = 'no-reply@vivaconagua.ch';
				break;

				case 68:
					$from_name = 'Viva con Agua';
					$from_email = 'no-reply@vivaconagua.at';
				break;

				case 40:
				default:
					$from_name = 'Viva con Agua';
					$from_email = 'no-reply@vivaconagua.org';
				break;
			}
		}

		$metadata = array(
			'scope' => $scope,
			'from_name' => $from_name,
			'from_email' => $from_email
		);

		return $metadata;
	}

} // class

endif; // class exists

?>