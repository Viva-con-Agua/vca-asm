<?php

/**
 * Template for forms used in the backend
 * (not to be used for CPTs & CFs)
 *
 **/

if ( ! isset( $output ) ) {
	$output = '';
}

$output .= '<table class="form-table pool-form"><tbody>';

if ( isset ( $fields ) &&  ! empty( $fields ) ) {
	foreach ( $fields as $field ) {

		if ( ! isset( $field['value'] ) ) {
			$field['value'] = '';
		}

		$output .= '<tr valign="top"';
		if( isset( $field['row-class'] ) && ! empty( $field['row-class'] ) ) {
			$output .= ' class="' . $field['row-class'] . '"';
		}
		$output .= '><th scope="row">';
		if( $field['type'] != 'section' && isset( $field['label'] ) && ! empty( $field['label'] ) ) {
			$output .= '<label for="' .
				$field['id'] .
				'">' .
				$field['label'] .
				'</label>';
		}
		$output .= '</th><td>';

		switch( $field['type'] ) {
			case 'section':
				$output .= '<h3>' . $field['label'] . '</h3>';
			break;

			case 'hidden':
				$output .= '<input type="hidden" ' .
					'name="' . $field['id'] .
					'" id="' . $field['id'] .
					'" class="input' .
					'" value="' . $field['value'] . '" />';
			break;

			case 'tel':
				$output .= '<input type="tel" class="input input-tel"' .
					'name="' . $field['id'] .
					'" id="' . $field['id'] .
					'" value="' . $field['value'] . '" size="40"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= ' />';
			break;

			case 'email':
				$output .= '<input type="email" class="input input-email"' .
					'name="' . $field['id'] .
					'" id="' . $field['id'] .
					'" value="' . $field['value'] . '" size="40"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= ' />';
			break;

			case 'textarea':
				$output .= '<textarea name="' . $field['id'] .
					'" id="' . $field['id'] .
					'" cols="100" rows="10"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= '>' . $field['value'] . '</textarea>';
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
					if( ( $field['value'] == $option['value'] && $option['value'] != 0 ) || $field['value'] === $option['value'] ) {
						$output .= ' selected="selected"';
					}
					$output .= ' value="' . $option['value'] . '">' . $option['label'] . '&nbsp;</option>';
				}
				$output .= '</select>';
			break;

			case 'checkbox':
				$output .= '<input type="checkbox"' .
					'name="' . $field['id'] .
					'" id="' . $field['id'] . '" ';
				if( isset( $field['value'] ) && ! empty( $field['value'] ) ) {
					$output .= ' checked="checked"';
				}
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= '/><label for="' . $field['id'] . '">' . $field['label'] . '</label>';
			break;

			case 'radio':
				$end = count( $field['options'] );
				$i = 1;
				foreach ( $field['options'] as $option ) {
					$output .= '<input type="radio"' .
						'name="' . $field['id'] .
						'" id="' . $field['id'] . '_' . $option['value'] .
						'" value="' . $option['value'] . '" ';

					if( $field['value'] == $option['value'] ) {
						$output .= ' checked="checked"';
					}
					if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
						$output .= ' disabled="disabled"';
					}
					$output .= ' /><label for="' . $field['id'] . '_' . $option['value'] . '">' . $option['label'] . '</label>';
					if( $i < $end ) {
						$output .= '<br />';
					}
					$i++;
				}
			break;

			case 'checkbox_group':

				if( isset( $field['cols'] ) ) {
					$cols = $field['cols'];
				} else {
					$cols = 3;
				}

				if( $cols !== 1 ) {
					$output .= '<table class="table-inside-table table-mobile-collapse"><tr><td>';
					$i = 1;
					$end = count( $field['options'] );
				}
				foreach( $field['options'] as $option ) {

					$output .= '<input type="checkbox"' .
						'value="' . $option['value'] . '" ' .
						'name="' . $field['id'] . '[]" ' .
						'class="' . $field['id'] . '" ' .
						'id="' . $field['id'] . '_' . $option['value'] . '"';

					if( ( isset( $field['value'] ) && is_array( $field['value'] ) && in_array( $option['value'], $field['value'] ) )
						|| ( isset( $option['checked'] ) && $option['checked'] === true ) ) {
						$output .= ' checked="checked"';
					}
					if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
						$output .= ' disabled="disabled"';
					}

					$output .= ' /><label for="' . $field['id'] . '_' . $option['value'] . '">' .
						$option['label'] .
						'</label>';

					if( $cols !== 1 ) {
						if( ( $i % $cols ) === 0 ) {
							if( $i === $end ) {
								$output .= '</td></tr></table>';
							} else {
								$output .= '</td></tr><tr><td>';
							}
						} elseif( $i === $end ) {
							$empty_cell = '</td><td>';
							for( $i = 0; $i < ( $i % $cols ); $i++ ) {
								$$output .= $empty_cell;
							}
							$output .= '</td></tr></table>';
						} else {
							$output .= '</td><td>';
						}
						$i++;
					} else {
						$output .= '<br />';
					}
				}

				if ( isset( $field['extra'] ) && 'bulk_deselect' === $field['extra'] ) {
					$output .= '<input type="submit" name="" class="button-secondary bulk-deselect" value="' .
								__( 'Deselect all', 'vca-asm' ) . '" /><br />';
				}
			break;

			case 'text':
			default:
				$output .= '<input type="text"' .
					'name="' . $field['id'] .
					'" id="' . $field['id'] .
					'" class="input regular-text"' .
					'" value="' . $field['value'] . '" size="40"';
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
		$output .= '</td></tr>';
	} // foreach field
	$output .= '</tbody></table>';
} // if ! empty

?>
