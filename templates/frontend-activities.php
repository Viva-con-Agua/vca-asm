<?php

	/**
	 * Template to display activities in the supporter's activities view
	 *
	 * This template is not satisfied with a simple array,
	 * it must be fed $activities, a WP_Query object
	 **/

	global $current_user, $vca_asm_utilities;
	get_currentuserinfo();

	$user_city = get_user_meta( $current_user->ID, 'region', true );
	$user_mem_status = get_user_meta( $current_user->ID, 'membership', true );

	if( ! isset( $output ) ) {
		$output = '';
	}
	if( ! isset( $list_class ) ) {
		$list_class = '';
	}
	if( empty( $activities ) ) {
		return;
	}
	if( isset( $split_months ) && $split_months === true ) {
		$month_cur = 0;
	}

	/* list & loop through posts (activities) */
	$output .=  '<ul class="' . $list_class . '">';

	while ( $activities->have_posts() ) : $activities->the_post();

		$the_activity = new VCA_ASM_Activity( get_the_ID() );
		$eligible_quota = $the_activity->is_eligible( $current_user->ID );

		if ( ! is_numeric( $eligible_quota ) ) {
			continue;
		}

		if( isset( $split_months ) && $split_months === true ) {
			$stamp = intval( get_post_meta( get_the_ID(), 'start_act', true ) );
			$month_num = date( 'n', $stamp );
			if( $month_num != $month_cur ) {
				$month_cur = $month_num;
				$output .= '<li class="activity-month"><h3>' .
					_x( 'Activities in', 'in name of month', 'vca-asm' ) . ' ' . strftime( '%B', $stamp ) . ':' .
					'</h3></li>';
			}
		}

		$output .= '<li class="activity toggle-wrapper">' .
			'<h4>';
		if ( 'concert' === $the_activity->post_object->post_type ) {
			$output .= __( 'Concert', 'vca-asm' ) . ': ';
		}
		$output .= get_the_title() . '</h4>' .
			'<ul class="head-block"><li>' .
				__( 'Timeframe', 'vca-asm' ) . ': ' .
				strftime( '%A, %e.%m.%Y, %H:%M', intval( get_post_meta( get_the_ID(), 'start_act', true ) ) ) .
				' ' . __( 'until', 'vca-asm' ) . ' ' .
				strftime( '%A, %e.%m.%Y, %H:%M', intval( get_post_meta( get_the_ID(), 'end_act', true ) ) ) .
			'</li><li>' .
				__( 'Location', 'vca-asm' ) . ': ' .
				get_post_meta( get_the_ID(), 'location', true ) .
			'</li><li>' .
				__( 'Application Deadline', 'vca-asm' ) . ': ' .
				strftime( '%A, %e.%m.%Y', intval( get_post_meta( get_the_ID(), 'end_app', true ) ) ) .
			'</li></ul>' .
			'<div class="toggle-element"><div class="measuring-wrapper">' .
			'<h5>' . __( 'Further Info', 'vca-asm' ) . '</h5>' .
			'<ul><li>' .
				__( 'Website', 'vca-asm' ) . ': ';
		$website = get_post_meta( get_the_ID(), 'website', true );
		if( substr( $website, 0, 11 ) === 'http://www.' ) {
			$url = $website;
			$name = substr( $website, 11 );
		} elseif( substr( $website, 0, 7 ) === 'http://' ) {
			$url = $website;
			$name = substr( $website, 7 );
		} elseif( substr( $website, 0, 4 ) === 'www.' ) {
			$url = 'http://' . $website;
			$name = substr( $website, 4 );
		} else {
			$url = 'http://' . $website;
			$name = $website;
		}
		$output .= '<a title="' .
			sprintf( __( 'Visit &quot;%s&quot;', 'vca-asm' ), $name ) .
			'" href="' . $url . '" target="_blank">' .
			$name . '</a>' .
			'</li><li>' .
				__( 'VcA Activities', 'vca-asm' ) . ': ';
		$tools_enc = get_post_meta( get_the_ID(), 'tools', true );
		$special_desc =  get_post_meta( get_the_ID(), 'special', true );
		if( ! empty( $tools_enc ) ) {
			$tools = array();
			if( in_array( '1', $tools_enc ) ) {
				$tools[] = _x( 'Cups', 'VcA Tools', 'vca-asm' );
			}
			if( in_array( '2', $tools_enc ) ) {
				$tools[] = _x( 'Guest List', 'VcA Tools', 'vca-asm' );
			}
			if( in_array( '3', $tools_enc ) ) {
				$tools[] = _x( 'Info Counter', 'VcA Tools', 'vca-asm' );
			}
			if( in_array( '4', $tools_enc ) ) {
				$tools[] = _x( 'Water Bottles', 'VcA Tools', 'vca-asm' );
			}
			if( in_array( '5', $tools_enc ) ) {
				if( isset( $special_desc ) && ! empty( $special_desc ) ) {
					$tools[] = $special_desc;
				} else {
					$tools[] = _x( 'Special', 'VcA Tools', 'vca-asm' );
				}
			}
			$tools = implode( ', ', $tools );
		} else {
			$tools = __( 'none', 'vca-asm' );
		}
		$output .= $tools . '</li><li>' .
				__( 'Directions', 'vca-asm' ) . ': ' .
				nl2br( $vca_asm_utilities->urls_to_links( get_post_meta( get_the_ID(), 'directions', true ) ) ) .
			'</li></ul>';
		if( isset( $show_xtra_info ) && $show_xtra_info === true ) {
			$output .= '<h5>' . __( 'Contact Person(s)', 'vca-asm' ) . '</h5>';
			$contacts = get_post_meta( get_the_ID(), 'contact_name', true );
			$contact_emails = get_post_meta( get_the_ID(), 'contact_email', true );
			$contact_mobiles = get_post_meta( get_the_ID(), 'contact_mobile', true );
			$i = 0;
			if ( is_array( $contacts ) ) {
				foreach( $contacts as $contact_name ) {
					if( empty( $contact_name ) ) {
						if( $i === 0 ) {
							$output .= '<p>' . __( 'Not set yet...', 'vca-asm' ) . '</p>';
						}
						continue;
					}
					$output .= '<ul><li>' .
						__( 'Name', 'vca-asm' ) . ': ' . $contact_name;
					if( ! empty( $contact_emails[$i] ) ) {
						$output .= '</li><li>' . __( 'E-Mail', 'vca-asm' ) . ': ' . $contact_emails[$i];
					}
					if( ! empty( $contact_mobiles[$i] ) ) {
						$output .= '</li><li>' . __( 'Mobile Number', 'vca-asm' ) . ': ' . $contact_mobiles[$i];
					}
					$output .= '</li></ul>';
					$i++;
				}
			}
		}

		if( isset( $show_app ) && $show_app === true ) {

			$output .= '<h5>' . __( 'Note', 'vca-asm' ) . '</h5>' .
				'<form method="post" action="">' .
				'<input type="hidden" name="unique_id" value="[' . md5( uniqid() ) . ']">' .
				'<input type="hidden" name="todo" id="todo" value="apply" />' .
				'<input type="hidden" name="activity" id="activity" value="' . get_the_ID() . '" />' .
				'<div class="form-row">' .
					'<div class="no-js-toggle">' .
						'<textarea name="notes" id="notes" rows="4"></textarea>' .
						'<br /><span class="description">' .
							_x( 'If you wish to send a message with your application, do so here.', 'Frontend: Application Process', 'vca-asm' ) .
						'</span>' .
					'</div>' .
					'<div class="js-toggle">' .
						'<textarea name="notes" id="notes" class="textarea-hint" rows="5">' .
							_x( 'If you wish to send a message with your application, do so here.', 'Frontend: Application Process', 'vca-asm' ) .
							"\n\n" .
							_x( "For insatance if you're applying with a friend, cannot reach on time, or the like.", 'Frontend: Application Process', 'vca-asm' ) .
						'</textarea>' .
					'</div>' .
				'</div><div class="form-row">' .
					'<input type="submit" id="submit_form" name="submit_form" value="' . __( 'Apply', 'vca-asm' ) . '" />' .
				'</div></form>';
		}

		if( isset( $show_rev_app ) && $show_rev_app === true ) {

			$output .= '<form method="post" action="">' .
				'<input type="hidden" name="todo" id="todo" value="revoke_app" />' .
				'<input type="hidden" name="activity" id="activity" value="' . get_the_ID() . '" />' .
				'<div class="form-row">' .
					'<input type="submit" id="submit_form" name="submit_form" value="' . __( 'Revoke Application', 'vca-asm' ) . '" />' .
				'</div></form>';
		}

		$output .= '</div></div><div class="toggle-arrows-wrap">' .
			'<a class="toggle-link toggle-arrows toggle-arrows-more" title="' . __( 'Toggle additional info', 'vca-asm' ) . '" ' . 'href="#">' .
				'<img alt="' . __( 'More/Less', 'vca-asm' ) . '"src="' .
					get_bloginfo( 'template_url' ) . '/images/arrows.png" />' .
			'</a></div>' .
			'</li>';

	endwhile;

	$output .= '</ul>';

?>