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
	 * Returns an activities free slots for a region
	 * defaults to global slots if no regional slots are assigned
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_free_slots( $activity, $region ) {
		global $wpdb;
		
		$slots_arr = get_post_meta( $activity, 'slots', true );
		
		if( ! array_key_exists( $region, $slots_arr ) ) {
			$region = 0;
		}
		
		$registrations = $wpdb->get_results(
			"SELECT contingent FROM " .
			$wpdb->prefix . "vca_asm_registrations " .
			"WHERE activity=" . $activity, ARRAY_A
		);
		$reg_count = 0;
		foreach( $registrations as $supporter ) {
			if( $region == $supporter['contingent'] ) {
				$reg_count++;
			}
		}
		$free_slots = intval( $slots_arr[$region] ) - $reg_count;
		return $free_slots;
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
	public function get_activity_application_count( $activity, $region = 'all' ) {
		global $wpdb;
		
		if( $region === 'all' ) {
			$count = $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM " .
				$wpdb->prefix . "vca_asm_applications " .
				"WHERE activity=" . $activity . " AND state = 0"
			) );
		} else {
			$count = 0;
			$applications = $wpdb->get_results(
				"SELECT supporter FROM " .
				$wpdb->prefix . "vca_asm_applications " .
				"WHERE activity=" . $activity . " AND state = 0", ARRAY_A
			);
			foreach( $applications as $supporter ) {
				$supp_region = get_user_meta( $supporter['supporter'], 'region', true );
				$supp_mem_status = get_user_meta( $supporter['supporter'], 'membership', true );
				if( $supp_region == $region && $supp_mem_status == 2 ) {
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
			"WHERE activity = " . $activity . " AND state = 1", ARRAY_N
		);
		$supporters = array();
		foreach( $applications as $application ) {
			$supporters[] = $application[0];
		}
		
		return $supporters;
	}

	/**
	 * Returns the count of current supporters on the waiting list for an activity
	 *
	 * @since 1.1
	 * @access public
	 */
	public function get_activity_waiting_count( $activity, $region = 'all' ) {
		global $wpdb;
		
		if( $region === 'all' ) {
			$count = $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM " .
				$wpdb->prefix . "vca_asm_applications " .
				"WHERE activity=" . $activity . " AND state = 1"
			) );
		} else {
			$count = 0;
			$waiting = $wpdb->get_results(
				"SELECT supporter FROM " .
				$wpdb->prefix . "vca_asm_applications " .
				"WHERE activity=" . $activity . " AND state = 1", ARRAY_A
			);
			foreach( $waiting as $supporter ) {
				$supp_region = get_user_meta( $supporter['supporter'], 'region', true );
				$supp_mem_status = get_user_meta( $supporter['supporter'], 'membership', true );
				if( $supp_region == $region && $supp_mem_status == 2 ) {
					$count++;
				}
			}
		}
		
		return $count;
	}

	/**
	 * Returns an array containing all supporters that are registered for an activity
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_activity_registrations( $activity ) {
		global $wpdb;
		
		$registrations = $wpdb->get_results(
			"SELECT supporter FROM " .
			$wpdb->prefix . "vca_asm_registrations " .
			"WHERE activity = " . $activity , ARRAY_A
		);
		$supporters = array();
		foreach( $registrations as $registration ) {
			$supporters[] = $registration['supporter'];
		}
		
		return $supporters;
	}

	/**
	 * Returns the count of current registrations to an activity
	 *
	 * @since 1.1
	 * @access public
	 */
	public function get_activity_registration_count( $activity, $region = 'all' ) {
		global $wpdb;
		
		if( $region === 'all' ) {
			$count = $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM " .
				$wpdb->prefix . "vca_asm_registrations " .
				"WHERE activity=" . $activity
			) );
		} else {
			$count = 0;
			$registrations = $wpdb->get_results(
				"SELECT contingent FROM " .
				$wpdb->prefix . "vca_asm_registrations " .
				"WHERE activity=" . $activity, ARRAY_A
			);
			foreach( $registrations as $supporter ) {
				if( $region == $supporter['contingent'] ) {
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
			$start_date = intval( get_post_meta( $activity, 'start_date', true ) ) + 82800;
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
			$start_date = intval( get_post_meta( $activity, 'start_date', true ) ) + 82800;
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
	 * Old registrations are saved as serialized arrays
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
			$end_date = intval( get_post_meta( $activity, 'end_date', true ) ) + 82800;
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
		if( $supporter === NULL ) {
			global $current_user;
			get_currentuserinfo();
			$supporter = $current_user->ID;
		}
			
		$registrations = $wpdb->get_results(
			"SELECT activities FROM " .
			$wpdb->prefix . "vca_asm_registrations_old " .
			"WHERE supporter = " . $supporter .
			" LIMIT 1", ARRAY_A
		);
		
		$registrations = $registrations[0]['activities'];
		
		if( empty( $registrations ) ) {
			$registrations = array();
		} else {
			$registrations = unserialize( $registrations );
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
			
		$applications = $wpdb->get_results(
			"SELECT activities FROM " .
			$wpdb->prefix . "vca_asm_applications_old " .
			"WHERE supporter = " . $supporter .
			" LIMIT 1", ARRAY_A
		);
		
		$applications = $applications[0]['activities'];
		
		if( empty( $applications ) ) {
			$applications = array();
		} else {
			$applications = unserialize( $applications );
		}
		
		return $applications;
	}

	/**
	 * Writes an application to database
	 *
	 * @since 1.0
	 * @access public
	 */
	public function set_application( $activity, $notes = NULL, $supporter = NULL ) {
		global $wpdb, $vca_asm_mailer;
		
		/* default action (if called from frontend) */
		if( $supporter === NULL ) {
			global $current_user;
			get_currentuserinfo();
			$supporter = $current_user->ID;
		}
		
		$wpdb->insert( 
			$wpdb->prefix . 'vca_asm_applications',
			array(
				'activity' 		=> $activity,
				'supporter' 	=> $supporter,
				'notes'			=> $notes,
				'state'			=> 0
			), 
			array( 
				'%d', 
				'%d',
				'%s',
				'%d' 
			)
		);
		
		$vca_asm_mailer->auto_response( $supporter, 'applied', get_the_title($activity) );
	
		return true;
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
		global $wpdb, $vca_asm_mailer;
		
		$wpdb->update( 
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
		
		$vca_asm_mailer->auto_response( $supporter, 'denied', get_the_title($activity) );
		
		return true;
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
		
		$note = $wpdb->get_results(
			"SELECT notes FROM " .
			$wpdb->prefix . "vca_asm_applications " .
			"WHERE activity=" . $activity . " AND supporter=" . $supporter . ' LIMIT 1', ARRAY_A
		);
		$note = $note[0]['notes'];
		
		$wpdb->query(
			'DELETE FROM ' . $wpdb->prefix . 'vca_asm_applications ' .
			'WHERE activity = ' . $activity . ' AND supporter = ' . $supporter . ' LIMIT 1'
		);
		
		$avoid_dupes = $wpdb->get_results(
			"SELECT id FROM " .
			$wpdb->prefix . "vca_asm_registrations " .
			"WHERE activity=" . $activity . " AND supporter=" . $supporter . ' LIMIT 1', ARRAY_A
		);
		
		if( empty( $avoid_dupes[0]['id'] ) ) {
			$slots_arr = get_post_meta( $activity, 'slots', true );
			$supp_region = get_user_meta( $supporter, 'region', true );
			$supp_mem_status = get_user_meta( $supporter, 'membership', true );
			
			if( $supp_mem_status == 2 && array_key_exists( $supp_region, $slots_arr ) ) {
				$contingent = intval( $supp_region );
			} else {
				$contingent = 0;
			}
			
			$wpdb->insert( 
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
			$vca_asm_mailer->auto_response( $supporter, 'accepted', get_the_title($activity) );
			return true;
		}
		
		return false;
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
		
		$wpdb->query(
			'DELETE FROM ' . $wpdb->prefix . 'vca_asm_registrations ' .
			'WHERE activity = ' . $activity . ' AND supporter = ' . $supporter . ' LIMIT 1'
		);
		
		$registrations_old_before = $this->get_supporter_registrations_old( $supporter );
		$registrations = $registrations_old_before;
		array_unshift( $registrations, $activity );
		$serialized_regs = serialize( $registrations );
		
		if( ! empty( $registrations_old_before ) ) {
			$wpdb->update( 
				$wpdb->prefix . 'vca_asm_registrations_old',
				array(
					'activities'	=> $serialized_regs
				),
				array(
					'supporter'		=> $supporter
				),
				array( 
					'%s'
				),
				array( 
					'%d'
				)
			);
		} else {
			$wpdb->insert( 
				$wpdb->prefix . 'vca_asm_registrations_old',
				array(
					'supporter'		=> $supporter,
					'activities'	=> $serialized_regs
				),
				array( 
					'%d',
					'%s'
				)
			);
		}
		
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
		
		$wpdb->query(
			'DELETE FROM ' . $wpdb->prefix . 'vca_asm_applications ' .
			'WHERE activity = ' . $activity . ' AND supporter = ' . $supporter . ' LIMIT 1'
		);
		
		$applications_old_before = $this->get_supporter_applications_old( $supporter );
		$applications = $applications_old_before;
		array_unshift( $applications, $activity );
		$serialized_apps = serialize( $applications );
		
		if( ! empty( $applications_old_before ) ) {
			$wpdb->update( 
				$wpdb->prefix . 'vca_asm_applications_old',
				array(
					'activities'	=> $serialized_apps
				),
				array(
					'supporter'		=> $supporter
				),
				array( 
					'%s'
				),
				array( 
					'%d'
				)
			);
		} else {
			$wpdb->insert( 
				$wpdb->prefix . 'vca_asm_applications_old',
				array(
					'supporter'		=> $supporter,
					'activities'	=> $serialized_apps
				),
				array( 
					'%d',
					'%s'
				)
			);
		}
		
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
		
		$wpdb->query(
			'DELETE FROM ' . $wpdb->prefix . 'vca_asm_applications ' .
			'WHERE activity = ' . $activity . ' AND supporter = ' . $supporter . ' LIMIT 1'
		);
		
		return true;
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
		
		$wpdb->query(
			'DELETE FROM ' . $wpdb->prefix . 'vca_asm_registrations ' .
			'WHERE activity = ' . $activity . ' AND supporter = ' . $supporter . ' LIMIT 1'
		);
		
		$vca_asm_mailer->auto_response( $supporter, 'reg_revoked', get_the_title($activity) );
		
		return true;
	}
	
} // class

endif; // class exists

?>