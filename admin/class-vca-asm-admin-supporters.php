<?php

/**
 * VCA_ASM_Admin_Supporters class.
 * 
 * This class contains properties and methods for
 * the supporter management.
 * Only admin users have access to the global
 * wordpress user management section.
 * Via the admin menu entry created by this class,
 * Head-Ofs and Department Managers can manage all users
 * that are in the "supporter" user group
 * (or role, as in correct wp lingo).
 *
 * @package VcA Activity & Supporter Management
 * @since 1.0
 */

if ( ! class_exists( 'VCA_ASM_Admin_Supporters' ) ) :

class VCA_ASM_Admin_Supporters {

	/**
	 * Sorting Methods
	 *
	 * @since 1.0
	 * @access private
	 */	
	private function sort_by_key( $arr, $key, $order ) { 
	    global $vca_asm_key2sort; 
		$vca_asm_key2sort = $key;
		if( $order == 'DESC' ) {
			uasort( $arr, array(&$this, 'sbk_cmp_desc') );
		} else {
			uasort( $arr, array(&$this, 'sbk_cmp_asc') );
		}
		return ( $arr ); 
	}
	private function sbk_cmp_asc( $a, $b ) {
		global $vca_asm_key2sort;
		return( strcasecmp( $a[$vca_asm_key2sort], $b[$vca_asm_key2sort] ) );
	}
	private function sbk_cmp_desc( $b, $a ) {
		global $vca_asm_key2sort;
		return( strcasecmp( $a[$vca_asm_key2sort], $b[$vca_asm_key2sort] ) );
	}

	/**
	 * Fetches membership status from databse and converts to human readable form
	 *
	 * @since 1.0
	 * @access private
	 */
	private function get_membership_status( $id, $region_status ) {
		$status = get_user_meta( $id, 'membership', true );
		if( $region_status != 'region' ) {
			switch( $status ) {
				case '1':
					return __( 'Pending...', 'vca-asm' );
				break;
				case '2':
					return __( 'Ja', 'vca-asm' );
				break;
				case '0':
				default:
					return __( 'Nein', 'vca-asm' );
				break;
			}
		} else {
			return '---';
		}
	}
	
	/**
	 * Builds the HTML for the quickinfo pop-up on mouseover for the backend
	 *
	 * @since 1.0
	 * @access public
	 */
	public function quickinfo( $activity, $supporter, $state = 'applied' ) {
		global $wpdb, $vca_asm_regions, $vca_asm_utilities;
		
		$supp_region = get_user_meta( $supporter, 'region', true );
		$supp_age = $vca_asm_utilities->date_diff( time(), get_user_meta( $supporter, 'birthday', true ) );
		$supp_info = get_userdata( $supporter );
		if( $state === 'applied' ) {
			$notes = $wpdb->get_results(
				"SELECT notes FROM " .
				$wpdb->prefix . "vca_asm_applications " .
				"WHERE activity=" . $activity . " AND supporter=" . $supporter . ' LIMIT 1', ARRAY_A
			);
		} else {
			$notes = $wpdb->get_results(
				"SELECT notes FROM " .
				$wpdb->prefix . "vca_asm_registrations " .
				"WHERE activity=" . $activity . " AND supporter=" . $supporter . ' LIMIT 1', ARRAY_A
			);
		}
		$note = $notes[0]['notes'];
		$note = str_replace( '"', '&quot;', $note );
		$note = str_replace( "'", '&apos;', $note );
		$avatar = preg_replace( '/"/', '&quot;', get_avatar( $supporter ) );
		$avatar = preg_replace( '/\'/', '&quot;', $avatar );
		$supporter_quick_info = '\'' .
			'<p><strong>' .
				str_replace( "'", "", $supp_info->first_name ) . ' ' . str_replace( "'", "", $supp_info->last_name ) . '</strong><br />' .
				$avatar . '<br />' .
				__( 'Age', 'vca-asm' ) . ': ' . $supp_age['year'] . '<br />' .
				__( 'City', 'vca-asm' ) . ': ' .
					str_replace( "'", "", get_user_meta( $supporter, 'city', true ) ) . '<br />' .
				__( 'Mobile Phone', 'vca-asm' ) . ': ' .
					get_user_meta( $supporter, 'mobile', true ) . '<br />' .
				__( 'Region', 'vca-asm' ) . ': ' . $vca_asm_regions->get_name($supp_region);
			if( isset( $note ) && ! empty( $note ) ) {
				$supporter_quick_info .= '<br /><br />' .
					__( 'Note', 'vca-asm' ) . ':<br />' .
					str_replace( "'", "", preg_replace( "/\r|\n/", "", trim( nl2br( $note, true ) ) ) );
			} else {
				$supporter_quick_info .= '<br /><br />' .
					__( 'Note', 'vca-asm' ) . ': ' .
					__( 'No Notes', 'vca-asm' );
			}
		$supporter_quick_info .= '</p>\'';
		
		return $supporter_quick_info;
	}

	/**
	 * Lists all supporters
	 *
	 * @todo JOINed SQL query to save resources!
	 *
	 * @since 1.0
	 * @access public
	 */
	public function list_supporters() {
		global $current_user, $vca_asm_regions, $vca_asm_utilities;
		get_currentuserinfo();
		
		$url = "admin.php?page=vca-asm-supporters";
		
		if( isset( $_GET['orderby'] ) ) {
			$orderby = $_GET['orderby'];
		} else {
			$orderby = 'first_name';
		}
		if( isset( $_GET['order'] ) ) {
			$order = $_GET['order'];
			if( $order == 'ASC') {
				$toggle_order = 'DESC';
			} else {
				$toggle_order = 'ASC';	
			} 
		} else {
			$order = 'ASC';
			$toggle_order = 'DESC';
		}
		
		$regions = $vca_asm_regions->get_ids();
		$stati = $vca_asm_regions->get_stati();
		$stati_conv = $vca_asm_regions->get_stati_conv();
		
		if( $current_user->has_cap('vca_asm_view_all_supporters') ) {
			$args = 'role=supporter';
		} else {
			$admin_region = get_user_meta( $current_user->ID, 'region', true );
			$args = array(
				'role' => 'supporter',
				'meta_key' => 'region',
				'meta_value' => intval( $admin_region )
			);
		}
		$supporters = get_users( $args );
		
		$rows = array();
		$scount = count( $supporters );
		for ( $i = 0; $i < $scount; $i++ ) {
			$supp_region = get_user_meta( $supporters[$i]->ID, 'region', true );
			$supp_age = $vca_asm_utilities->date_diff( time(), intval( get_user_meta( $supporters[$i]->ID, 'birthday', true ) ) );
			if( empty ( $supp_region ) ) {
				$supp_region = '0';
			}
			$rows[$i]['first_name'] = get_user_meta( $supporters[$i]->ID, 'first_name', true );
			$rows[$i]['last_name'] = get_user_meta( $supporters[$i]->ID, 'last_name', true );
			$rows[$i]['user_email'] = '<a title="' . __( 'Send an email to this supporter', 'vca-asm' ) .
				'" href="' . get_bloginfo('url') . '/wp-admin/admin.php?page=vca-asm-emails&email=' . $supporters[$i]->user_email . '"> ' .
				$supporters[$i]->user_email . '</a>';
			$rows[$i]['mobile'] = get_user_meta( $supporters[$i]->ID, 'mobile', true );
			$rows[$i]['region'] = $regions[$supp_region];
			if( $supp_region != 0 ) {
				$rows[$i]['region'] .= ' (' . $stati_conv[$supp_region] . ')';
			}
			$rows[$i]['membership'] = $this->get_membership_status( $supporters[$i]->ID, $stati[$supp_region] );
			$rows[$i]['age'] = $supp_age['year'] . '<br />&nbsp;'; //--> dirty space fix
		}
		
		$rows = $this->sort_by_key( $rows, $orderby, $order );
		
		$columns = array(
			array(
				'id' => 'first_name',
				'title' => __( 'First Name', 'vca-asm' ),
				'sortable' => true,
				'strong' => true
			),
			array(
				'id' => 'last_name',
				'title' => __( 'Last Name', 'vca-asm' ),
				'sortable' => true,
				'strong' => true
			),
			array(
				'id' => 'user_email',
				'title' => __( 'Email Address', 'vca-asm' ),
				'sortable' => true
			),
			array(
				'id' => 'mobile',
				'title' => __( 'Mobile Phone', 'vca-asm' ),
				'sortable' => true
			),
			array(
				'id' => 'region',
				'title' => __( 'Region', 'vca-asm' ),
				'sortable' => true
			),
			array(
				'id' => 'membership',
				'title' => __( 'Membership Status', 'vca-asm' ),
				'sortable' => true
			),
			array(
				'id' => 'age',
				'title' => __( 'Age', 'vca-asm' ),
				'sortable' => true
			)
		);
		
		$headline = __( 'Supporters', 'vca-asm' );
		
		require( VCA_ASM_ABSPATH . '/templates/admin-table.php' );
	}

	/**
	 * Interface to accept supporter's cell / local-crew membership requests
	 * 
	 * @todo reload page after database manipulations
	 * 		to account for new menu values
	 * 		(or find other solution!)
	 *
	 * @since 1.0
	 * @access public
	 */
	public function promotions() {
		global $vca_asm_regions, $vca_asm_mailer, $vca_asm_utilities, $current_user;
		get_currentuserinfo();
		
		$url = "admin.php?page=vca-asm-supporter-memberships";
		
		$regions = $vca_asm_regions->get_ids();
		
		/* execute promotion */
		if( isset( $_GET['todo'] ) && isset( $_GET['id'] ) && $_GET['todo'] == 'promote' ) {
			update_user_meta( $_GET['id'], 'membership', '2' );
			$region_name = $regions[ get_user_meta( $_GET['id'], 'region', true ) ];
			$vca_asm_mailer->auto_response( $_GET['id'], 'mem_accepted', $region_name );
		}
		
		/* execute denial */
		if( isset( $_GET['todo'] ) && isset( $_GET['id'] ) && $_GET['todo'] == 'deny' ) {
			update_user_meta( $_GET['id'], 'membership', '0' );
			$region_name = $regions[ get_user_meta( $_GET['id'], 'region', true ) ];
			$vca_asm_mailer->auto_response( $_GET['id'], 'mem_denied', $region_name );
		}
		
		if( isset( $_GET['orderby'] ) ) {
			$orderby = $_GET['orderby'];
		} else {
			$orderby = 'first_name';
		}
		if( isset( $_GET['order'] ) ) {
			$order = $_GET['order'];
			if( $order == 'ASC') {
				$toggle_order = 'DESC';
			} else {
				$toggle_order = 'ASC';	
			} 
		} else {
			$order = 'ASC';
			$toggle_order = 'DESC';
		}
		
		$stati_conv = $vca_asm_regions->get_stati_conv();
		
		$args = array(
			'role' => 'supporter',
			'meta_key' => 'membership',
			'meta_value' => '1'
		);
		$supporters = get_users( $args );
		
		$rows = array();
		$scount = count( $supporters );
		$admin_region = get_user_meta( $current_user->ID, 'region', true );
		for ( $i = 0; $i < $scount; $i++ ) {
			$supp_region = get_user_meta( $supporters[$i]->ID, 'region', true );
			$supp_age = $vca_asm_utilities->date_diff( time(), intval( get_user_meta( $supporters[$i]->ID, 'birthday', true ) ) );
			
			if( $current_user->has_cap('vca_asm_promote_all_supporters') || $admin_region === $supp_region ) {
				$rows[$i]['first_name'] = get_user_meta( $supporters[$i]->ID, 'first_name', true );
				$rows[$i]['last_name'] = get_user_meta( $supporters[$i]->ID, 'last_name', true );
				$rows[$i]['user_email'] = $supporters[$i]->user_email;
				$rows[$i]['mobile'] = get_user_meta( $supporters[$i]->ID, 'mobile', true );
				$rows[$i]['region'] = $regions[$supp_region] . ' (' .
					 $stati_conv[$supp_region] . ')';
				$rows[$i]['age'] = $supp_age['year'];
				$rows[$i]['id'] = $supporters[$i]->ID;
			}
		}
		
		$rows = $this->sort_by_key( $rows, $orderby, $order );
		
		$columns = array(
			array(
				'id' => 'first_name',
				'title' => __( 'First Name', 'vca-asm' ),
				'sortable' => true,
				'strong' => true,
				'promotable' => true
			),
			array(
				'id' => 'last_name',
				'title' => __( 'Last Name', 'vca-asm' ),
				'sortable' => true,
				'strong' => true
			),
			array(
				'id' => 'user_email',
				'title' => __( 'Email Address', 'vca-asm' ),
				'sortable' => true
			),
			array(
				'id' => 'mobile',
				'title' => __( 'Mobile Phone', 'vca-asm' ),
				'sortable' => true
			),
			array(
				'id' => 'region',
				'title' => __( 'Region', 'vca-asm' ),
				'sortable' => true
			),
			array(
				'id' => 'age',
				'title' => __( 'Age', 'vca-asm' ),
				'sortable' => true
			)
		);
		
		$headline = __( 'Membership Applications', 'vca-asm' );
		
		require( VCA_ASM_ABSPATH . '/templates/admin-table.php' );
	}
	
} // class

endif; // class exists

?>