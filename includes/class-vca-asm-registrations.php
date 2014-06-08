<?php

/**
 * VcA_ASM_Registrations class.
 * This class contains properties and methods to allocate supporters to events/activities.
 *
 * @package VcA Activity & Supporter Management
 * @since 1.0
 *
 * @todo more efficient SQL
 */

if ( ! class_exists( 'VcA_ASM_Registrations' ) ) :

class VcA_ASM_Registrations {

	/**
	 * Returns an activities free slots for a certain user
	 * defaults to global slots if no regional slots are assigned
	 *
	 * @param int $activity_id
	 * @param int $user_id
	 *
	 * @return in $free
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_free_slots( $activity_id, $user_id ) {
		global $wpdb, $vca_asm_geography;

		$the_activity = new VcA_ASM_Activity( $activity_id );

		$city = intval( get_user_meta( $user_id, 'city', true ) );
		$nation = intval( get_user_meta( $user_id, 'nation', true ) );

		if ( ! empty( $city ) && array_key_exists( $city, $the_activity->cty_slots ) ) {
			$quota = $city;
			$participants = isset( $the_activity->participants_count_by_slots[$city] ) ?
				$the_activity->participants_count_by_slots[$city] :
				0;
			$free = $the_activity->cty_slots[$city] - $participants;
		} else {
			if ( ! empty( $nation ) && array_key_exists( $nation, $the_activity->ctr_slots ) ) {
				$quota = $nation;
				$participants = isset( $the_activity->participants_count_by_slots[$nation] ) ?
					$the_activity->participants_count_by_slots[$nation] :
					0;
				$free = $the_activity->ctr_slots[$nation] - $participants;
			} else {
				$quota = 0;
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
	 * @since 1.0
	 * @access public
	 */
	public function get_activity_applications( $activity ) {
		global $wpdb;

		$applications = $wpdb->get_results(
			"SELECT supporter FROM " .
			$wpdb->prefix . "vca_asm_applications " .
			"WHERE activity = " . $activity . " AND state = 0", ARRAY_A
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
	 * @since 1.1
	 * @access public
	 */
	public function get_activity_application_count( $activity, $city = 'all' ) {
		global $wpdb;

		if( $city === 'all' ) {
			$count = $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM " .
				$wpdb->prefix . "vca_asm_applications " .
				"WHERE activity= %d AND state = 0", $activity
			) );
		} else {
			$count = 0;
			$applications = $wpdb->get_results(
				"SELECT supporter FROM " .
				$wpdb->prefix . "vca_asm_applications " .
				"WHERE activity=" . $activity . " AND state = 0", ARRAY_A
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
	 * @since 1.0
	 * @access public
	 */
	public function get_activity_waiting( $activity ) {
		global $wpdb;

		$applications = $wpdb->get_results(
			"SELECT supporter FROM " .
			$wpdb->prefix . "vca_asm_applications " .
			"WHERE activity = " . $activity . " AND state = 1", ARRAY_A
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
	 * @since 1.1
	 * @access public
	 */
	public function get_activity_waiting_count( $activity, $city = 'all' ) {
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
	 * @since 1.0
	 * @access public
	 */
	public function get_activity_registrations( $activity ) {
		return $this->get_activity_participants( $activity );
	}
	public function get_activity_participants( $activity, $args = array() ) {
		global $wpdb;

		$default_args = array(
			'by_contingent' => false
		);
		extract( wp_parse_args( $args, $default_args ), EXTR_SKIP );

		$participants_query = $wpdb->get_results(
			"SELECT supporter, contingent FROM " .
			$wpdb->prefix . "vca_asm_registrations " .
			"WHERE activity = " . $activity , ARRAY_A
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
	 * @since 1.3
	 * @access public
	 */
	public function get_activity_applications_old( $activity ) {
		global $wpdb;

		$applications = $wpdb->get_results(
			"SELECT supporter FROM " .
			$wpdb->prefix . "vca_asm_applications_old " .
			"WHERE activity = " . $activity, ARRAY_A
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
	 * @since 1.3
	 * @access public
	 */
	public function get_activity_participants_old( $activity, $args = array() ) {
		global $wpdb;

		$default_args = array(
			'by_contingent' => false
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
	 * @since 1.1
	 * @access public
	 */
	public function get_activity_registration_count( $activity, $city = 'all' ) {
		global $wpdb, $vca_asm_activities;

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
			$end_date = intval( get_post_meta( $activity, 'end_act', true ) );
			$today = time();
			if( $today < $end_date ) {
				$dummy = 0;
			}
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

	/**
	 * Returns an array containing a all of a supporter's non-past events
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_supporter_all( $supporter = NULL ) {
		global $wpdb;

		/* default action (if called from frontend) */
		if( $supporter === NULL ) {
			global $current_user;
			get_currentuserinfo();
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
	 * @since 1.0
	 * @access public
	 */
	public function get_supporter_applications( $supporter = NULL ) {
		global $wpdb;

		/* default action (if called from frontend) */
		if( $supporter === NULL ) {
			global $current_user;
			get_currentuserinfo();
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
	 * @since 1.0
	 * @access public
	 */
	public function get_supporter_waiting( $supporter = NULL ) {
		global $wpdb;

		/* default action (if called from frontend) */
		if( $supporter === NULL ) {
			global $current_user;
			get_currentuserinfo();
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
	 * @since 1.0
	 * @access public
	 */
	public function get_supporter_registrations( $supporter = NULL ) {
		global $wpdb;

		/* default action (if called from frontend) */
		if( $supporter === NULL ) {
			global $current_user;
			get_currentuserinfo();
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
	 * @since 1.0
	 * @access public
	 */
	public function get_supporter_registrations_old( $supporter = NULL ) {
		global $wpdb;

		/* default action (if called from frontend) */
		if ( $supporter === NULL ) {
			global $current_user;
			get_currentuserinfo();
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
	 * @since 1.0
	 * @access public
	 */
	public function get_supporter_applications_old( $supporter = NULL ) {
		global $wpdb;

		/* default action (if called from frontend) */
		if( $supporter === NULL ) {
			global $current_user;
			get_currentuserinfo();
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

	/**
	 * Writes an application to the database
	 *
	 * @since 1.0
	 * @access public
	 */
	public function set_application( $activity, $notes = '', $supporter = NULL ) {
		global $current_user, $wpdb,
			$vca_asm_mailer;

		/* default action (if called from frontend) */
		if( $supporter === NULL ) {
			global $current_user;
			get_currentuserinfo();
			$supporter = $current_user->ID;
		}
		$activity = intval( $activity );

		$metadata = $this->scope_from_activity( $activity );
		extract( $metadata );

		$applications_query = $wpdb->get_results(
			"SELECT id FROM " .
			$wpdb->prefix . "vca_asm_applications " .
			"WHERE supporter = " . $supporter . " AND activity = " . $activity .
			" LIMIT 1", ARRAY_A
		);

		if ( empty( $applications_query[0]['id'] ) ) {
			$success = $wpdb->insert(
				$wpdb->prefix."vca_asm_applications",
				array(
					'activity' => $activity,
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
				'activity' => get_the_title( $activity ),
				'activity_id' => $activity
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
	 * @since 1.0
	 * @access public
	 */
	public function deny_application( $activity, $supporter ) {
		global $wpdb,
			$vca_asm_mailer;

		$success = $wpdb->update(
			$wpdb->prefix . 'vca_asm_applications',
			array(
				'state'			=> 1
			),
			array(
				'activity' 		=> $activity,
				'supporter'		=> $supporter
			),
			array(
				'%d'
			),
			array(
				'%d',
				'%d'
			)
		);

		$metadata = $this->scope_from_activity( $activity );
		extract( $metadata );

		$vca_asm_mailer->auto_response(
			$supporter,
			'denied',
			array(
				'scope' => $scope,
				'from_name' => $from_name,
				'from_email' => $from_email,
				'activity' => get_the_title( $activity ),
				'activity_id' => $activity
			)
		);

		return $success;
	}

	/**
	 * Accepts an application
	 *
	 * Moves a supporter's application entry to the wp_vca_asm_registrations table
	 *
	 * @since 1.0
	 * @access public
	 */
	public function accept_application( $activity, $supporter ) {
		global $wpdb, $vca_asm_mailer;

		$the_activity = new VCA_ASM_Activity( $activity, array( 'minimalistic' => true ) );

		$note = $wpdb->get_results(
			"SELECT notes FROM " .
			$wpdb->prefix . "vca_asm_applications " .
			"WHERE activity=" . $activity . " AND supporter=" . $supporter . ' LIMIT 1', ARRAY_A
		);
		$note = isset( $note[0]['notes'] ) ? $note[0]['notes'] : '';

		$wpdb->query(
			'DELETE FROM ' . $wpdb->prefix . 'vca_asm_applications ' .
			'WHERE activity = ' . $activity . ' AND supporter = ' . $supporter . ' LIMIT 1'
		);

		$avoid_dupes = $wpdb->get_results(
			"SELECT id FROM " .
			$wpdb->prefix . "vca_asm_registrations " .
			"WHERE activity=" . $activity . " AND supporter=" . $supporter . ' LIMIT 1', ARRAY_A
		);

		$success = false;
		if( empty( $avoid_dupes[0]['id'] ) ) {
			$contingent = $the_activity->is_eligible( $supporter );

			if ( is_numeric( $contingent ) ) {
				$success = $wpdb->insert(
					$wpdb->prefix . 'vca_asm_registrations',
					array(
						'activity' 		=> $activity,
						'supporter' 	=> $supporter,
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

				$metadata = $this->scope_from_activity( $activity );
				extract( $metadata );

				$vca_asm_mailer->auto_response(
					$supporter,
					'accepted',
					array(
						'scope' => $scope,
						'from_name' => $from_name,
						'from_email' => $from_email,
						'activity' => get_the_title( $activity ),
						'activity_id' => $activity
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
	 * @since 1.0
	 * @access public
	 */
	public function move_registration_to_old( $activity, $supporter ) {
		global $wpdb;

		$data = $wpdb->get_results(
			"SELECT notes, contingent FROM " .
			$wpdb->prefix . "vca_asm_registrations " .
			"WHERE activity = " . $activity . " AND supporter = " . $supporter . " LIMIT 1", ARRAY_A
		);
		$note = $data[0]['notes'];
		$quota = $data[0]['contingent'];

		$wpdb->query(
			"DELETE FROM " . $wpdb->prefix . "vca_asm_registrations " .
			"WHERE activity = " . $activity . " AND supporter = " . $supporter . " LIMIT 1"
		);

		$wpdb->insert(
			$wpdb->prefix . 'vca_asm_registrations_old',
			array(
				'supporter' => $supporter,
				'activity' => $activity,
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
	 * @since 1.0
	 * @access public
	 */
	public function move_application_to_old( $activity, $supporter ) {
		global $wpdb;

		$note = $wpdb->get_results(
			"SELECT notes FROM " .
			$wpdb->prefix . "vca_asm_applications " .
			"WHERE activity = " . $activity . " AND supporter = " . $supporter . " LIMIT 1", ARRAY_A
		);
		$note = $note[0]['notes'];
		$wpdb->query(
			'DELETE FROM ' . $wpdb->prefix . 'vca_asm_applications ' .
			'WHERE activity = ' . $activity . ' AND supporter = ' . $supporter . ' LIMIT 1'
		);

		$wpdb->insert(
			$wpdb->prefix . 'vca_asm_applications_old',
			array(
				'supporter'		=> $supporter,
				'activity'	=> $activity,
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
	 * @since 1.0
	 * @access public
	 */
	public function revoke_application( $activity, $supporter = NULL ) {
		global $wpdb;

		/* default action (if called from frontend) */
		if( $supporter === NULL ) {
			global $current_user;
			get_currentuserinfo();
			$supporter = $current_user->ID;
		}

		$success = $wpdb->query(
			'DELETE FROM ' . $wpdb->prefix . 'vca_asm_applications ' .
			'WHERE activity = ' . $activity . ' AND supporter = ' . $supporter . ' LIMIT 1'
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
	 * @since 1.0
	 * @access public
	 */
	public function revoke_registration( $activity, $supporter ) {
		global $wpdb, $vca_asm_mailer;

		$success = $wpdb->query(
			'DELETE FROM ' . $wpdb->prefix . 'vca_asm_registrations ' .
			'WHERE activity = ' . $activity . ' AND supporter = ' . $supporter . ' LIMIT 1'
		);

		$metadata = $this->scope_from_activity( $activity );
		extract( $metadata );

		$vca_asm_mailer->auto_response(
			$supporter,
			'reg_revoked',
			array(
				'scope' => $scope,
				'from_name' => $from_name,
				'from_email' => $from_email,
				'activity' => get_the_title( $activity ),
				'activity_id' => $activity
			)
		);

		return $success;
	}

	/********** UTILITY METHODS **********/

	/**
	 * Writes an application to the database
	 *
	 * @since 1.0
	 * @access public
	 */
	public function scope_from_activity( $activity ) {
		global $current_user, $wpdb,
			$vca_asm_activities, $vca_asm_mailer;

		$type = get_post_type( $activity );

		if ( ! empty( $vca_asm_activities->departments_by_activity[$type] ) && 'goldeimer' === $vca_asm_activities->departments_by_activity[$type] ) {
			$scope = 'ge';
			$from_name = __( 'Goldeimer', 'vca-asm' );
			$from_email = _x( 'no-reply@goldeimer.de', 'Utility Translation', 'vca-asm' );
		} else {
			$scope = get_post_meta( $activity, 'nation', true );
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