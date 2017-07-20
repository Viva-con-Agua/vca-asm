<?php

/**
 * Template for custom post types metadata fields
 * (could be used for regular post's custom fields as well)
 *
 * Currently in use for activities
 **/

global $post, $wpdb, $vca_asm_activities, $vca_asm_geography, $vca_asm_registrations, $vca_asm_admin_supporters;
$current_user = wp_get_current_user();

$admin_city = get_user_meta( $current_user->ID, 'city', true );
$admin_region = $admin_city;
$admin_nation = get_user_meta( $current_user->ID, 'nation', true );

$post_city = get_post_meta( $post->ID, 'city', true );
$post_nation = get_post_meta( $post->ID, 'nation', true );
$post_delegation = get_post_meta( $post->ID, 'delegate', true );
$post_delegation = 'delegate' === $post_delegation ? true : false;

if ( isset( $this->the_activity ) && true === $this->the_activity->is_activity ) {
	$the_activity = $this->the_activity;
	$is_activity = true;
} else {
	$the_activity = new VCA_ASM_Activity( $post->ID );
	$is_activity = $the_activity->is_activity;
}

$department = $vca_asm_activities->departments_by_activity[$post->post_type] ?
	$vca_asm_activities->departments_by_activity[$post->post_type] :
	'actions';

if ( ! isset( $output ) ) {
	$output = '';
}

/* table & loop through fields */
$output .=  '<table class="form-table">';

if ( isset ( $fields ) &&  ! empty( $fields ) ) {
	foreach ( $fields as $field ) {

		/* get value of this field if it exists for this post */
		$meta = get_post_meta( $post->ID, $field['id'], true );
		$meta = ( ! empty( $meta ) || '0' === $meta || 0 === $meta ) ? $meta : ( ! empty( $field['default'] ) ? $field['default'] : $meta );

		switch( $field['type'] ) {
			case 'contact':
			case 'hidden':
				$output .= '';
			break;

			case 'ctr_quotas':
			case 'cty_quotas':
			case 'cty_slots':
			case 'quotas':
				$output .= '<tr id="' . $field['type'] . '-wrap" class="quotas-wrap"><th>' .
						'<label for="'.$field['id'].'">'.$field['label'].'</label>' .
					'</th><td>';
			break;

			default:
				$output .= '<tr><th><label for="'.$field['id'].'">'.$field['label'];
				if ( isset( $field['required'] ) ) {
					$output .= ' <span class="required">*</span>';
				}
				$output .= '</label></th><td>';
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

			case 'hidden-with-text':
				$output .= '<input type="hidden" ' .
					'name="' . $field['id'] .
					'" id="' . $field['id'] .
					'" class="input' .
					'" value="' . $field['value'] . '" />' .
					'<em>' . $field['text'] . '</em>';
			break;

			case 'textarea':
				$output .= '<textarea name="'. $field['id'] .
					'" id="' . $field['id'] . '" ';
				if ( isset( $field['required'] ) ) {
					$output .= ' class="required"';
				}
				$output .= 'cols="60" rows="4"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= '>' . $meta . '</textarea>';
			break;

			case 'select':
				$output .= '<select name="' . $field['id'] .
				'" id="' . $field['id'] . '" ';
				if ( isset( $field['required'] ) ) {
					$output .= ' class="required"';
				}
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
				$last_val = end( $field['options'] );
				foreach ( $field['options'] as $option ) {
					$output .= '<input type="radio" ' .
						'name="' . $field['id'] .
						'" id="' . $option['value'] .
						'" value="' . $option['value'] . '" ';
					if( $meta == $option['value'] || empty( $meta ) && isset( $option['default'] ) && true === $option['default'] ) {
						$output .= ' checked="checked"';
					}
					if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
						$output .= ' disabled="disabled"';
					}

					$output .= ' /><label for="' . $option['value'] . '">' . $option['label'] . '</label>';
					if ( $option['value'] !== $last_val['value'] ) {
						$output .= '<br />';
					}
				}
			break;

			case 'checkbox_group':
				foreach( $field['options'] as $option ) {
					$output .= '<input type="checkbox"' .
						'value="' . $option['value'] .
						'" name="' . $field['id'] . '[]' .
						'" class="' . $field['id'] . '"' .
						' id="' . $field['id'] . '_' . $option['value'] . '"';

					if( isset( $meta ) && is_array( $meta ) && in_array( $option['value'], $meta ) ||
					    empty( $meta ) && isset( $option['default'] ) && true === $option['default']
					) {
							$output .= ' checked="checked"';
					}
					if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
						$output .= ' disabled="disabled"';
					}

					$output .= ' /><label for="' . $field['id'] . '_' . $option['value'] . '">' .
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
				$output .= '<ul id="'.$field['id'].'-repeatable" class="repeatable-cf no-margins">';
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
							'<input type="text" name="contact_name'.'['.$i.']" id="contact_name_'.$i.'" value="'.$row.'" size="30" /></td></tr>' .
							'<tr><th><label for="contact_email['.$i.']">'. _x( 'E-Mail', 'Contact Person Meta Box', 'vca-asm' ) .'</label></th><td>' .
							'<input type="text" name="contact_email'.'['.$i.']" id="contact_email_'.$i.'" value="'.$emails[$i].'" size="30" /></td></tr>' .
							'<tr><th><label for="contact_mobile['.$i.']">'. _x( 'Mobile Phone', 'Contact Person Meta Box', 'vca-asm' ) .'</label></th><td>' .
							'<input type="text" name="contact_mobile'.'['.$i.']" id="contact_mobile_'.$i.'" class="phone-number" value="'.$mobiles[$i].'" size="30" />' .
							'<a class="contact-cf-remove no-js-hide" href="#"';
						if ( 2 > count( $meta ) ) {
							$output .= ' style="display:none"';
						}
						$output .= '>' . _x( 'remove', 'Quotas', 'vca-asm' ) . '</a></td></tr></tbody>';
						$i++;
					}
				} else {
					$output .= '<tbody>' .
						'<tr><th><label for="contact_name['.$i.']">'. _x( 'Name', 'Contact Person Meta Box', 'vca-asm' ) .'</label></th><td>' .
						'<input type="text" name="contact_name'.'['.$i.']" id="contact_name" value="" size="30" /></td></tr>' .
						'<tr><th><label for="contact_email['.$i.']">'. _x( 'E-Mail', 'Contact Person Meta Box', 'vca-asm' ) .'</label></th><td>' .
						'<input type="text" name="contact_email'.'['.$i.']" id="contact_email" value="" size="30" /></td></tr>' .
						'<tr><th><label for="contact_mobile['.$i.']">'. _x( 'Mobile Phone', 'Contact Person Meta Box', 'vca-asm' ) .'</label></th><td>' .
						'<input type="text" name="contact_mobile'.'['.$i.']" id="contact_mobile" class="phone-number" value="" size="30" />' .
						'<a class="contact-cf-remove no-js-hide" href="#" style="display:none">' .
							_x( 'remove', 'Quotas', 'vca-asm' ) .
						'</a></td></tr></tbody>';
				}
				$output .= '<tbody><tr><td><a class="contact-cf-add no-js-hide" href="#">' .
						'+ ' . _x( 'add', 'Quotas', 'vca-asm' ) .
					'</a></td></tr></tbody>';
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
					$day_val = date( 'd', $meta );
					$month_val = date( 'm', $meta );
					$year_val = date( 'Y', $meta );
				} else {
					$value = '';
					$day_val = date( 'd' );
					$month_val = date( 'm' );
					$year_val = date( 'Y' );
				}
				$output .= '<input type="text" class="no-js-hide datepicker date';
				if ( isset( $field['required'] ) ) {
					$output .= ' required';
				}
				$output .= '" name="' . $field['id'] .
					'" id="' . $field['id'] .
					'" value="' . $value .
					'" size="30"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= ' /><select class="day js-hide js-hide" id="' . $field['id'] . '_day" name="' . $field['id'] . '_day"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= '>';
				for ( $i = 1; $i < 32; $i++ ) {
					$string = str_pad( $i, 2, '0', STR_PAD_LEFT );
					$output .= '<option value="' . $string . '"';
					if ( $day_val === $string ) {
						$output .= ' selected="selected"';
					}
					$output .= '>' .
							$string . '&nbsp;' .
						'</option>';
				}
				$output .= '</select><select class="months js-hide js-hide" id="' . $field['id'] . '_month" name="' . $field['id'] . '_month"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= '>';
				for ( $i = 1; $i < 13; $i++ ) {
					$string = str_pad( $i, 2, '0', STR_PAD_LEFT );
					$output .= '<option value="' . $string . '"';
					if ( $month_val === $string ) {
						$output .= ' selected="selected"';
					}
					$output .= '>' .
							$string . '&nbsp;' .
						'</option>';
				}
				$output .= '</select><select class="year js-hide js-hide" id="' . $field['id'] . '_year" name="' . $field['id'] . '_year"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= '>';
				for ( $i = 0; $i < 20; $i++ ) {
					$string = strval( 2012 + $i );
					$output .= '<option value="' . $string . '"';
					if ( $year_val === $string ) {
						$output .= ' selected="selected"';
					}
					$output .= '>' .
							$string . '&nbsp;' .
						'</option>';
				}
				$output .= '</select>';
			break;

			case 'date_time':
				if( ! empty( $meta ) ) {
					$meta = intval( $meta );
					$date_val = date( 'd.m.Y', $meta );
					$day_val = date( 'd', $meta );
					$month_val = date( 'm', $meta );
					$year_val = date( 'Y', $meta );
					$hour_val = date( 'H', $meta );
					$minutes_val = str_pad( round( intval( date( 'i', $meta ) ) / 15 ) * 15, 2, '0', STR_PAD_LEFT ) ;
				} else {
					$date_val = '';
					$day_val = date( 'd' );
					$month_val = date( 'm' );
					$year_val = date( 'Y' );
					$hour_val = '12';
					$minutes_val = '00';
				}
				$output .= '<select class="day js-hide js-hide" id="' . $field['id'] . '_day" name="' . $field['id'] . '_day"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= '>';
				for ( $i = 1; $i < 32; $i++ ) {
					$string = str_pad( $i, 2, '0', STR_PAD_LEFT );
					$output .= '<option value="' . $string . '"';
					if ( $day_val === $string ) {
						$output .= ' selected="selected"';
					}
					$output .= '>' .
							$string . '&nbsp;' .
						'</option>';
				}
				$output .= '</select><select class="months js-hide js-hide" id="' . $field['id'] . '_month" name="' . $field['id'] . '_month"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= '>';
				for ( $i = 1; $i < 13; $i++ ) {
					$string = str_pad( $i, 2, '0', STR_PAD_LEFT );
					$output .= '<option value="' . $string . '"';
					if ( $month_val === $string ) {
						$output .= ' selected="selected"';
					}
					$output .= '>' .
							$string . '&nbsp;' .
						'</option>';
				}
				$output .= '</select><select class="year js-hide js-hide" id="' . $field['id'] . '_year" name="' . $field['id'] . '_year"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= '>';
				for ( $i = 0; $i < 20; $i++ ) {
					$string = strval( 2012 + $i );
					$output .= '<option value="' . $string . '"';
					if ( $year_val === $string ) {
						$output .= ' selected="selected"';
					}
					$output .= '>' .
							$string . '&nbsp;' .
						'</option>';
				}
				$output .= '</select><input type="text" class="no-js-hide datepicker date';
				if ( isset( $field['required'] ) ) {
					$output .= ' required';
				}
				$output .= '" name="' . $field['id'] .
					'" id="' . $field['id'] .
					'" value="' . $date_val .
					'" size="30"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= ' /> @ <select class="hour" id="' . $field['id'] . '_hour" name="' . $field['id'] . '_hour"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= '>';
				for ( $i = 0; $i < 24; $i++ ) {
					$string = str_pad( $i, 2, '0', STR_PAD_LEFT );
					$output .= '<option value="' . $string . '"';
					if ( $hour_val === $string ) {
						$output .= ' selected="selected"';
					}
					$output .= '>' .
							$string . '&nbsp;' .
						'</option>';
				}
				$output .= '</select> : ';
				$output .= '<select class="minutes" id="' . $field['id'] . '_minutes" name="' . $field['id'] . '_minutes"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= '>';
				for ( $i = 0; $i < 4; $i++ ) {
					$string = str_pad( $i * 15, 2, '0', STR_PAD_LEFT );
					$output .= '<option value="' . $string . '"';
					if ( $minutes_val === $string ) {
						$output .= ' selected="selected"';
					}
					$output .= '>' .
							$string . '&nbsp;' .
						'</option>';
				}
				$output .= '</select>';
			break;

			case 'slider':
				$meta = ! empty( $meta ) ? $meta : $field['min'];
				$output .= '<div id="' . $field['id'] . '-slider"></div>' .
					'<input type="text" name="'. $field['id'] .
					'" id="' . $field['id'] .
					'" value="' . $meta . '" size="5"';
					if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
						$output .= ' disabled="disabled"';
					}
				$output .= ' />';
			break;

			/* BEGIN: Slots */

			case 'total_slots':
				$value = ! empty( $meta ) ? $meta : $field['min'];
				$output .= '<div id="' . $field['id'] . '-slider"></div>' .
					'<span class="value total_slots-value no-js-hide" id="total_slots-value">' . $meta . '</span>' .
					'<input class="js-hide" type="text" name="'. $field['id'] .
					'" id="' . $field['id'] .
					'" value="' . $value . '"';
					if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
						$output .= ' disabled="disabled"';
					}
				$output .= ' />';
				if ( $is_activity && 0 < $the_activity->participants_count_by_quota[0] ) {
					$output .= ' (' . __( 'confirmed participants', 'vca-asm') . ': ' .
						$the_activity->participants_count_by_quota[0] . ')';
				}
			break;

			case 'global_slots':
				$meta = ( '' !== $meta ) ? $meta : 1;
				$output .= '<span class="value global_slots-value" id="global_slots-value">' . $meta . '</span>';
				if ( $is_activity && isset( $the_activity->participants_count_by_slots[0] ) && 0 < $the_activity->participants_count_by_slots[0] ) {
					$output .= ' (' . __( 'confirmed participants', 'vca-asm') . ': ' .
						$the_activity->participants_count_by_slots[0] . ')';
				}
				$output .= '<input type="hidden" ' .
						'name="' . $field['id'] .
						'" id="' . $field['id'] .
						'" class="input' .
						'" value="' . $meta . '" />';
			break;

			case 'ctr_quotas_switch':
				if ( $is_activity && $the_activity->non_global_participants ) {
					$non_global_participants_count = $the_activity->participants_count_by_quota[0];
					if ( isset( $the_activity->participants_count_by_slots[0] ) ) {
						$non_global_participants_count = $non_global_participants_count - $the_activity->participants_count_by_slots[0];
					}
					$output .= _x( 'Quotas have been enabled.', 'Slots Settings', 'vca-asm' ) . '<br />' .
					sprintf(
						_x( 'Currently, %d participants are already registered by country and/or city quotas.', 'Slots Settings', 'vca-asm' ),
						$non_global_participants_count
					) . ' ' .
					_x( 'Quotas cannot be disabled anymore, unless those participants are removed again...', 'Slots Settings', 'vca-asm' ) .
					'<input name="' . $field['id'] . '" id="' . $field['id'] . '" type="hidden" value="yay"/>';
				} else {
					$last_val = end( $field['options'] );
					foreach ( $field['options'] as $option ) {
						$output .= '<input type="radio" class="no-js-hide" ' .
							'name="' . $field['id'] .
							'" id="' . $option['value'] .
							'" value="' . $option['value'] . '" ';
						if( $meta == $option['value'] || empty( $meta ) && true === $option['default'] ) {
							$output .= ' checked="checked"';
						}
						if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
							$output .= ' disabled="disabled"';
						}

						$output .= ' /><label for="' . $option['value'] . '" class="no-js-hide">' . $option['label'] . '</label>';
						if ( $option['value'] !== $last_val['value'] ) {
							$output .= '<br class="no-js-hide" />';
						}
					}
				}
			break;

			case 'ctr_quotas':
				$output .= '<div class="no-js-fallback-vals">';
				$output .= '</div>';
			break;

			case 'cty_slots':
				$output .= '<div class="no-js-fallback-vals">';
				$output .= '</div>';
			break;

			/* END: Slots */

			case 'data-links':
					$data_string = __( 'Send an email or print a list', 'vca-asm' );
					$output .= '<a ' .
						'href="admin.php?page=vca-asm-' . $department . '-slot-allocation&activity=' . $the_activity->ID . '&tab=data' . '" ' .
						'title"' . $data_string . '"' .
					'>' . $data_string . '</a>';
			break;

			case 'applicants':
			case 'waiting':
			case 'participants':
				global $vca_asm_registrations;

				$supporters = array();

				if (
					$current_user->has_cap( 'vca_asm_manage_' . $department . '_global' ) ||
					(
						$current_user->has_cap( 'vca_asm_manage_' . $department . '_nation' ) &&
						$admin_nation &&
						$admin_nation === $post_nation
					) ||
					(
						$current_user->has_cap( 'vca_asm_manage_' . $department ) &&
						$post_delegation &&
						$admin_city &&
						$admin_city === $post_city
					)
				) {
					if ( 0 < $the_activity->{$field['type'].'_count'} ) {
						$supporters = $the_activity->{$field['type']};
					}
				} elseif (
					$current_user->has_cap( 'vca_asm_manage_' . $department . '_nation' ) &&
					$admin_nation
				) {
					if (
						array_key_exists( $admin_nation, $the_activity->{$field['type'].'_count_by_quota'} ) &&
						0 < $the_activity->{$field['type'].'_count_by_quota'}[$admin_nation]
					) {
						$supporters = $the_activity->{$field['type'].'_by_quota'}[$admin_nation];
					}
				} elseif (
					$current_user->has_cap( 'vca_asm_manage_' . $department ) &&
					$admin_city
				) {
					if (
						array_key_exists( $admin_city, $the_activity->{$field['type'].'_count_by_slots'} ) &&
						0 < $the_activity->{$field['type'].'_count_by_slots'}[$admin_city]
					) {
						$supporters = $the_activity->{$field['type'].'_by_slots'}[$admin_city];
					}
				}

				$output .= '<table class="table-inside-table table-mobile-collapse subtable">' .
						'<tr>' .
							'<td>' .
								__( 'Current total', 'vca-asm' ) .
							': ' .
								$the_activity->{$field['type'].'_count'} .
							'</td>' .
						'</tr>';
				if ( $admin_nation && array_key_exists( $admin_nation, $the_activity->{$field['type'].'_count_by_quota'} ) ) {
					$output .= '<tr>' .
							'<td>' .
								__( 'via your country quota', 'vca-asm' ) .
							': ' .
								$the_activity->{$field['type'].'_count_by_quota'}[$admin_nation] .
							'</td>' .
						'</tr>';
				}
				if ( array_key_exists( $admin_city, $the_activity->{$field['type'].'_count_by_slots'} ) ) {
					$output .= '<tr>' .
							'<td>' .
								__( 'via your city quota', 'vca-asm' ) .
							': ' .
								$the_activity->{$field['type'].'_count_by_slots'}[$admin_city] .
							'</td>' .
						'</tr>';
				}
				$output .= '</table>';

				if ( ! empty( $supporters ) ) {
					$output .= '<table class="table-inside-table table-mobile-collapse subtable">';

					$ordered_supps = array();
					foreach ( $supporters as $supporter ) {
						$the_supporter = new VCA_ASM_Supporter( $supporter );
						$ordered_supps[$supporter] = $the_supporter->nice_name;
					}
					usort( $ordered_supps, 'strnatcasecmp' );

					$i = 0;
					$last = count( $ordered_supps );
					foreach( $ordered_supps as $supp_id => $supp ) {
						if ( $i % 3 === 0 ) {
							$output .= '<tr><td>';
						} else {
							$output .= '<td>';
						}

						$output .= $supp;

						if ( $i % 3 === 2 ) {
							$output .= '</td></tr>';
						} else {
							$output .= '</td>';
						}

						if ( $i % 3 !== 2 && $i + 1 === $last ) {
							if ( $i % 3 !== 0 ) {
								$output .= '<td>&nbsp;</td>';
							}
							$output .= '<td>&nbsp;</td></tr>';
						}

						$i++;
					}

					if ( 'waiting' === $field['type'] ) {
						$mng_string = __( 'Manage this activities&apos; Waiting List', 'vca-asm' );
						$active_tab = 'waiting';
					} elseif ( 'participants' === $field['type'] ) {
						$mng_string = __( 'Manage this activities&apos; participants', 'vca-asm' );
						$active_tab = 'accepted';
					} else {
						$mng_string = __( 'Manage this activities&apos; applications', 'vca-asm' );
						$active_tab = 'apps';
					}

					$output .= '<tr><td colspan="3"><a ' .
						'href="admin.php?page=vca-asm-' . $department . '-slot-allocation&activity=' . $the_activity->ID . '&tab=' . $active_tab . '" ' .
						'title="' . $mng_string  . '"' .
					'>' .
						$mng_string .
					'</a></td></tr>';

					//if ( 'participants' === $field['type'] ) {
					//	$output .= '<tr><td colspan="3">' .
					//		'<a id="excel-download" href="#spreadsheet-full" onclick="p1exportExcel();">' .
					//			__( 'Download participant data as an MS Excel spreadsheet', 'vca-asm' ) .
					//			' (' . _x( 'including sensitive data, never (!) forward', 'non-sensitive data', 'vca-asm' ) . ')' .
					//		'</a>' .
					//	'</td></tr>' .
					//	'<tr><td colspan="3">' .
					//		'<a id="excel-download-minimal" href="#spreadsheet-minimal" onclick="p1exportExcelMin();">' .
					//			__( 'Download participant data as an MS Excel spreadsheet', 'vca-asm' ) .
					//			' (' . _x( 'safe to forward', 'non-sensitive data', 'vca-asm' ) . ')' .
					//		'</a>' .
					//		'<iframe id="excel-frame" src="" style="display:none; visibility:hidden;"></iframe>' .
					//	'</td></tr>';
					//}

					$output .= '</table>';
				}
			break;

			case 'text':
			default:
				$output .= '<input type="text"' .
					'name="' . $field['id'] .
					'" id="' . $field['id'] . '" ';
				if ( isset( $field['required'] ) ) {
					$output .= ' class="required"';
				}
				$output .= 'value="' . $meta .
					'" size="30"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= ' />';
			break;
		} // type switch

		if( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {
			if( ! in_array( $field['type'], array( 'hidden', 'checkbox_group', 'ctr_quotas', 'cty_slots', 'ctr_quotas_switch', 'applicants', 'waiting', 'participants' ) ) ) {
				$output .= '<br />';
			}
			if ( 'ctr_quotas_switch' !== $field['type'] ) {
				$output .= '<span class="description">' . $field['desc'] . '</span>';
			} else {
				$output .= '<br /><span class="description no-js-hide">' . $field['desc'] . '</span>' .
					'<span class="description js-hide">' . __( 'For this feature of the Pool to work properly, you need to have javascript enabled or have a javascript-capable browser, respectively. Unfortunately you cannot set further options here.', 'vca-asm' ) . '<br />' . __( 'Setting / Editing the slots without javascript will soon be possible via the current department\'s &quot;Slots &amp; Participants&quot; menu.', 'vca-asm' ) . '</span>';
			}
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