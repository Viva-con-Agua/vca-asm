<?php

/**
 * Template for custom post types
 * (could be used for regular post's custom fields as well)
 *
 **/

global $post, $wpdb, $current_user, $vca_asm_regions, $vca_asm_registrations, $vca_asm_admin_supporters;
get_currentuserinfo();
$admin_region = get_user_meta( $current_user->ID, 'region', true );

if ( ! isset( $output ) ) {
	$output = '';
}

/* table & loop through fields */
$output .=  '<table class="form-table">';

if ( isset ( $fields ) &&  ! empty( $fields ) ) {
	foreach ( $fields as $field ) {

		/* get value of this field if it exists for this post */
		$meta = get_post_meta($post->ID, $field['id'], true);

		switch( $field['type'] ) {
			case 'contact':
				$output .= '';
			break;

			default:
				$output .= '<tr><th><label for="'.$field['id'].'">'.$field['label'].'</label></th><td>';
			break;
		}

		switch( $field['type'] ) {

			case 'hidden':
				$output .= '<input type="hidden" ' .
					'name="' . $field['id'] .
					'" id="' . $field['id'] .
					'" class="input' .
					'" value="' . $field['value'] . '" />';
			break;

			case 'textarea':
				$output .= '<textarea name="'. $field['id'] .
					'" id="' . $field['id'] .
					'" cols="60" rows="4"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= '>' . $meta . '</textarea>';
			break;

			case 'select':
				$output .= '<select name="' . $field['id'] .
				'" id="' . $field['id'] . '"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= '>';

				foreach ($field['options'] as $option) {
					$output .= '<option';
					if( $meta == $option['value'] ) {
						$output .= ' selected="selected"';
					}
					$output .= ' value="' . $option['value'] . '">' . $option['label'] . '&nbsp;</option>';
				}
				$output .= '</select>';
			break;

			case 'checkbox':
				$output .= '<input type="checkbox"' .
					'name="' . $field['id'] .
					'" id="' . $field['id'] . '" ' .
					'value="' . $field['value'] . '" ';
				if( isset ( $meta ) && $meta == $field['value'] ) {
					$output .= ' checked="checked"';
				}
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= '/><label for="' . $field['id'] . '">' . $field['option'] . '</label>';
			break;

			case 'radio':
				foreach ( $field['options'] as $option ) {
					$output .= '<input type="radio"' .
						'name="' . $field['id'] .
						'" id="' . $option['value'] .
						'" value="' . $option['value'] . '" ';

					if( $meta == $option['value'] ) {
						$output .= ' checked="checked"';
					}
					if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
						$output .= ' disabled="disabled"';
					}

					$output .= ' /><label for="' . $option['value'] . '">' . $option['label'] . '</label><br />';
				}
			break;

			case 'checkbox_group':
				foreach( $field['options'] as $option ) {
					$output .= '<input type="checkbox"' .
						'value="' . $option['value'] .
						'" name="' . $field['id'] . '[]' .
						'" class="' . $field['id'] . '"';

					if( isset( $meta ) && is_array( $meta ) && in_array( $option['value'], $meta ) ) {
							$output .= ' checked="checked"';
					}
					if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
						$output .= ' disabled="disabled"';
					}

					$output .= ' /><label>' .
						$option['label'] .
						'</label><br />';
				}
			break;

			case 'tax_select':
				$output .= '<select name="' . $field['id'] .
					'" id="' . $field['id'] . '"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= '><option value="">' .
					_x( 'Select One', 'taxonomy selection for acitivity', 'vca-asm' ) .
					'</option>';

				$terms = get_terms( $field['id'], 'get=all' );

				$selected = wp_get_object_terms( $post->ID, $field['id'] );

				foreach( $terms as $term ) {
					if ( ! empty( $selected ) && ! strcmp( $term->slug, $selected[0]->slug ) ) {
						$output .= '<option value="'.$term->slug.'" selected="selected">'.$term->name.'</option>';
					} else {
						$output .= '<option value="'.$term->slug.'">'.$term->name.'</option>';
					}
				}

				$taxonomy = get_taxonomy($field['id']);

				$output .= '</select>';
			break;

			case 'repeatable':
				$output .= '<ul id="'.$field['id'].'-repeatable" class="repeatable-cf">';
				$i = 0;
				if ( ! empty( $meta ) ) {
					foreach( $meta as $row ) {
						$output .= '<li><span class="sort handle">|||</span>' .
							'<input type="text" name="'.$field['id'].'['.$i.']" id="'.$field['id'].'" value="'.$row.'" size="30" />' .
							'<a class="repeatable-cf-remove button" href="#">-</a></li>';
						$i++;
					}
				} else {
					$output .= '<li><span class="sort handle">|||</span>' .
						'<input type="text" name="'.$field['id'].'['.$i.']" id="'.$field['id'].'" value="" size="30" />' .
						'<a class="repeatable-cf-remove button" href="#">-</a></li>';
				}
				$output .= '</ul>' .
					 '<a class="repeatable-cf-add button" href="#">+</a>';
			break;

			case 'contact':
				$i = 0;
				if ( ! empty( $meta ) ) {
					$emails = get_post_meta($post->ID, 'contact_email', true);
					$mobiles = get_post_meta($post->ID, 'contact_mobile', true);
					foreach( $meta as $row ) {
						$output .= '<tbody>' .
							'<tr><th><label for="contact_name['.$i.']">'. _x( 'Name', 'Contact Person Meta Box', 'vca-asm' ) .'</label></th><td>' .
							'<input type="text" name="contact_name'.'['.$i.']" id="contact_name" value="'.$row.'" size="30" /></td></tr>' .
							'<tr><th><label for="contact_email['.$i.']">'. _x( 'E-Mail', 'Contact Person Meta Box', 'vca-asm' ) .'</label></th><td>' .
							'<input type="text" name="contact_email'.'['.$i.']" id="contact_email" value="'.$emails[$i].'" size="30" /></td></tr>' .
							'<tr><th><label for="contact_mobile['.$i.']">'. _x( 'Mobile Phone', 'Contact Person Meta Box', 'vca-asm' ) .'</label></th><td>' .
							'<input type="text" name="contact_mobile'.'['.$i.']" id="contact_mobile" value="'.$mobiles[$i].'" size="30" />' .
							'<a class="contact-cf-remove button" href="#">-</a></td></tr></tbody>';
						$i++;
					}
				} else {
					$output .= '<tbody>' .
						'<tr><th><label for="contact_name['.$i.']">'. _x( 'Name', 'Contact Person Meta Box', 'vca-asm' ) .'</label></th><td>' .
						'<input type="text" name="contact_name'.'['.$i.']" id="contact_name" value="'.$row.'" size="30" /></td></tr>' .
						'<tr><th><label for="contact_email['.$i.']">'. _x( 'E-Mail', 'Contact Person Meta Box', 'vca-asm' ) .'</label></th><td>' .
						'<input type="text" name="contact_email'.'['.$i.']" id="contact_email" value="'.$emails[$i].'" size="30" /></td></tr>' .
						'<tr><th><label for="contact_mobile['.$i.']">'. _x( 'Mobile Phone', 'Contact Person Meta Box', 'vca-asm' ) .'</label></th><td>' .
						'<input type="text" name="contact_mobile'.'['.$i.']" id="contact_mobile" value="'.$mobiles[$i].'" size="30" />' .
						'<a class="contact-cf-remove button" href="#">-</a></td></tr></tbody>';
				}
				$output .= '<tbody><tr><td><a class="contact-cf-add button" href="#">+</a></td></tr></tbody>';
			break;

			case 'slots':
				$output .= '<ul class="slots-cf">';
				$i = 0;
				if ( ! empty( $meta ) ) {
					foreach( $meta as $region => $slots ) {
						$output .= '<li><span class="sort hndle">|||</span>' .
							'<select name="slots-region['.$i.']" id="slots-region"';
						if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
							$output .= ' disabled="disabled"';
						}
						$output .= '>';
						foreach( $field['options'] as $option ) {
							$output .= '<option value="' . $option['value'] . '"';
							if( $region == $option['value'] ) {
								$output .= ' selected="selected"';
							};
							$output .= '>' . $option['label'] . '</option>';
						}
						$output .= '</select><br />' .
							'<div id="slots-slider"></div>' .
							'<input type="text" name="slots['.$i.']' .
							'" id="slots' .
							'" value="' . $slots . '" size="5"';
						if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
							$output .= ' disabled="disabled"';
						}
						$output .= ' />' .
							'<a class="slots-cf-remove button" href="#">-</a></li>';
						$i++;
					}
				} else {
					$output .= '<li><span class="sort hndle">|||</span>' .
						'<select name="slots-region [' . $i . ']" id="slots-region"';
						if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
							$output .= ' disabled="disabled"';
						}
						$output .= '>';
					foreach( $field['options'] as $option ) {
						$output .= '<option value="' . $option['value'] . '">' . $option['label'] . '</option>';
					}
					$output .= '</select><br />' .
						'<div id="slots-slider"></div>' .
						'<input type="text" name="slots['.$i.']' .
						'" id="slots' .
						'" value="' . $field['min'] . '" size="5"';
						if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
							$output .= ' disabled="disabled"';
						}
						$output .= ' />' .
						'<a class="slots-cf-remove button" href="#">-</a></li>';
				}
				$output .= '</ul>' .
					 '<a class="slots-cf-add button" href="#">+</a>';
			break;

			case 'applications':
				$slots_arr = get_post_meta( $post->ID, 'slots', true );
				$applications = $vca_asm_registrations->get_activity_applications( $post->ID );
				if( ! empty( $slots_arr ) && is_array( $slots_arr ) ) {

					$supp_arr = array();
					foreach( $slots_arr as $region => $slots ) {
						$supp_arr[$region] = array();
					}
					foreach( $applications as $supporter ) {
						$supp_region = intval( get_user_meta( $supporter, 'region', true ) );
						$supp_mem_status = intval( get_user_meta( $supporter, 'membership', true ) );

						if( $supp_mem_status == 2 && array_key_exists( $supp_region, $supp_arr ) ) {
							$supp_arr[$supp_region][] = $supporter;
						} else {
							$supp_arr[0][] = $supporter;
						}
					}

					foreach( $slots_arr as $region => $slots ) {
						if( ! in_array( 'head_of', $current_user->roles ) || $admin_region == $region ) {
							$free = $vca_asm_registrations->get_free_slots( $post->ID, $region );
							$test_for_none = true;
							$region_name = $vca_asm_regions->get_name($region);
							$output .= '<h4>';
							if( $region == 0 ) {
								$output .= __( 'Global Applications', 'vca-asm' );
							} else {
								$output .= sprintf( __( 'Applications for region "%s":', 'vca-asm' ), $region_name );
							}
							$output .= '<br />' .
									sprintf( __( 'Slots: %1$s, of which %2$s are free', 'vca-asm' ), $slots, $free ) .
								'</h4>' .
								'<ul>';
							$regional_applications = $supp_arr[$region];
							foreach( $regional_applications as $supporter ) {
								$test_for_none = false;
								$supp_info = get_userdata( $supporter );
								$supporter_quick_info ='';
								$output .= '<li><input type="checkbox" id="applications" name="applications[]"' .
									'value="' . $supporter . '" /><label>' .
										'<span class="supporter-tooltip" onmouseover="tooltip(' . $supporter_quick_info . ');" onmouseout="exit();">' .
											$supp_info->first_name . ' ' . $supp_info->last_name .
										'</span>' .
									'</label></li>';
							}
							if( $test_for_none === true ) {
								$output .= '<p>' . __( 'No current applications...', 'vca-asm' ) . '</p>';
							}
							$output .= '</ul>';
						}
					}

					$waiting = $vca_asm_registrations->get_activity_waiting( $post->ID );
					$test_for_none = true;
					$output .= '<h4>' .
							__( 'Waiting List:', 'vca-asm' ) .
						'</h4>' .
						'<ul>';
					foreach( $waiting as $supporter ) {
						$supp_region = get_user_meta( $supporter, 'region', true );
						if( ! in_array( 'head_of', $current_user->roles ) || $admin_region == $supp_region ) {
							$test_for_none = false;
							$supp_info = get_userdata( $supporter );
							$supporter_quick_info = '';

							$output .= '<li><input type="checkbox" id="applications" name="applications[]"' .
								'value="' . $supporter . '" /><label>' .
									'<span class="supporter-tooltip" onmouseover="tooltip(' . $supporter_quick_info . ');" onmouseout="exit();">' .
										$supp_info->first_name . ' ' . $supp_info->last_name .
									'</span>' .
								'</label></li>';
						}
					}
					if( $test_for_none === true ) {
						$output .= '<p>' . __( 'Currently no supporters on the waiting list...', 'vca-asm' ) . '</p>';
					}
					$output .= '</ul>';

					$output .= '<input type="radio" id="todo_app" name="todo_app" value="apply" />' .
								'<label><strong>' . __( 'Accept selected applications', 'vca-asm' ) . '</strong></label><br />' .
								'<input type="radio" id="todo_app" name="todo_app" value="deny" />' .
								'<label><strong>' . __( 'Deny selected applications', 'vca-asm' ) . '</strong></label>';
				} else {
					$output .= '<p>' . __( 'Please allocate slots first', 'vca-asm' ) . '</p>';
				}
			break;

			case 'registrations':
				$slots_arr = get_post_meta( $post->ID, 'slots', true );
				$registrations = $vca_asm_registrations->get_activity_registrations( $post->ID );
				if( ! empty( $slots_arr ) && is_array( $slots_arr ) ) {

					$supp_arr = array();
					foreach( $slots_arr as $region => $slots ) {
						$supp_arr[$region] = array();
					}
					foreach( $registrations as $supporter ) {
						$contingent = $wpdb->get_results(
							"SELECT contingent FROM " .
							$wpdb->prefix . "vca_asm_registrations " .
							"WHERE activity = " . $post->ID . " AND supporter = " . $supporter .
							" LIMIT 1", ARRAY_A
						);
						$contingent = $contingent[0]['contingent'];
						$supp_arr[$contingent][] = $supporter;
					}

					foreach( $slots_arr as $region => $slots ) {
						$test_for_none = true;
						$region_name = $vca_asm_regions->get_name($region);
						$free = $vca_asm_registrations->get_free_slots( $post->ID, $region );
						$reg_count = intval($slots) - $free;
						$output .= '<h4>';
						if( $region == 0 ) {
							$output .= sprintf( __( 'Accepted global applications (%1$s / %2$s):', 'vca-asm' ), $reg_count, $slots );
						} else {
							$output .= sprintf( __( 'Accepted applications for region "%1$s" (%2$s / %3$s):', 'vca-asm' ), $region_name, $reg_count, $slots );
						}
						$output .= '</h4>' .
							'<ul>';

						$regional_registrations = $supp_arr[$region];
						foreach( $regional_registrations as $supporter ) {
							$test_for_none = false;
							$supp_info = get_userdata( $supporter );
							$supporter_quick_info = '';

							$output .= '<li><input type="checkbox" id="registrations" name="registrations[]"' .
								'value="' . $supporter . '" /><label>' .
									'<span class="supporter-tooltip" onmouseover="tooltip(' . $supporter_quick_info . ');" onmouseout="exit();">' .
										$supp_info->first_name . ' ' . $supp_info->last_name .
									'</span>' .
								'</label></li>';
						}
						if( $test_for_none === true ) {
							$output .= '<p>' . __( 'No accepted applications yet...', 'vca-asm' ) . '</p>';
						}
						$output .= '</ul>';
					}
					$output .= '<input type="radio" id="todo_revoke" name="todo_revoke" value="revoke" />' .
								'<label><strong>' . __( 'Revoke selected accepted applications!', 'vca-asm' ) . '</strong></label>';
				} else {
					$output .= '<p>' . __( 'Please allocate slots first', 'vca-asm' ) . '</p>';
				}
			break;

			case 'email_link':
				$output .= '<a href="' .
					get_bloginfo('url') . '/wp-admin/admin.php?page=vca-asm-compose&activity=' . $post->ID . '&group=' . $field['group'] .
					'">' . $field['text'] . '</a>';
			break;

			case 'excel_link':
				$output .= '<a id="excel-download" href="#excel-download" onclick="exportExcel();">' .
						__( 'Download participant data as an MS Excel spreadsheet', 'vca-asm' ) .
					'</a>' .
					'<iframe id="excel-frame" src="" style="display:none; visibility:hidden;"></iframe>';
			break;

			case 'date':
				if( ! empty( $meta ) ) {
					$meta = intval( $meta );
					$value = date( 'd.m.Y', $meta );
				} else {
					$value = '';
				}
				$output .= '<input type="text" class="datepicker"' .
					'name="' . $field['id'] .
					'" id="' . $field['id'] .
					'" value="' . $value .
					'" size="30"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= ' />';
			break;

			case 'slider':
				if( empty( $meta ) ) {
					$meta = $field['min'];
				}
				$output .= '<div id="' . $field['id'] . '-slider"></div>' .
					'<input type="text" name="'. $field['id'] .
					'" id="' . $field['id'] .
					'" value="' . $meta . '" size="5"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= ' />';
			break;

			case 'text':
			default:
				$output .= '<input type="text"' .
					'name="' . $field['id'] .
					'" id="' . $field['id'] .
					'" value="' . $meta .
					'" size="30"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= ' />';
			break;
		} // type switch

		if( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {
			if( ! in_array( $field['type'], array( 'hidden', 'checkbox_group' ) ) ) {
				$output .= '<br />';
			}
			$output .= '<span class="description">' . $field['desc'] . '</span>';
		}

		switch( $field['type'] ) {
			case 'contact':
				$output .= '';
			break;

			default:
				$output .= '</td></tr>';
			break;
		}
	} // foreach field
}// if ! empty

$output .= '</table>';

?>