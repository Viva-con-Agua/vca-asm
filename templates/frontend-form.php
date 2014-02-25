<?php

/**
 * Template for forms used in the frontend
 *
 **/

if( ! isset( $output ) ) {
	$output = '';
}

/* loop through fields */
if( isset ( $fields ) &&  ! empty( $fields ) ) {
	foreach ( $fields as $field ) {

		if ( $field['type'] == 'section' ) {
			$output .= '<h3>' . $field['label'] . '</h3>';
			continue;
		}

		if( ! isset( $field['value'] ) ) {
			$field['value'] = '';
		}
		$output .= '<div class="form-row'
		if( isset( $field['row-class'] ) && ! empty( $field['row-class'] ) ) {
			$output .= ' ' . $field['row-class'];
		}
		if( isset( $field['label'] ) && ! empty( $field['label'] ) ) {
			$output .= '">' .
				'<label for="' . $field['id'] . '">' .
					$field['label'];
				if( isset ( $field['tooltip'] ) && ! empty( $field['tooltip'] ) ) {
					$output .= '<span class="tip" onmouseover="tooltip(\'' .
						$field['tooltip'] .
						'\');" onmouseout="exit();">?</span>';
				}
			$output .= '</label>' ;
		}

		switch( $field['type'] ) {
			case 'textarea':
				$output .= '<textarea name="' . $field['id'] .
					'" id="' . $field['id'] .
					'" cols="60" rows="4"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= '>' . $field['value'] . '</textarea>';
			break;

			case 'hidden':
				$output .= '<input type="hidden" ' .
					'name="' . $field['id'] .
					'" id="' . $field['id'] .
					'" class="input"' .
					'" value="' . $field['value'] . '" />';
			break;

			case 'tel':
				$output .= '<input type="tel" class="input input-tel"' .
					'name="' . $field['id'] .
					'" id="' . $field['id'] .
					'" value="' . $field['value'] . '" size="30"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= ' />';
			break;

			case 'email':
				$output .= '<input type="email" class="input input-email"' .
					'name="' . $field['id'] .
					'" id="' . $field['id'] .
					'" value="' . $field['value'] . '" size="30"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= ' />';
			break;

			case 'select':
				$output .= '<select name="' . $field['id'] .
				'" id="' . $field['id'] . '"' .
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= '>';

				foreach ($field['options'] as $option) {
					$output .= '<option';
					if( $field['value'] == $option['value'] ) {
						$output .= ' selected="selected"';
					}
					$output .= ' value="' . $option['value'] . '">' . $option['label'] . '&nbsp;</option>';
				}
				$output .= '</select>';
			break;

			case 'checkbox':
				$output .= '<span class="box-test"></span><input type="checkbox"' .
					'name="' . $field['id'] .
					'" id="' . $field['id'] . '" ';
				if( isset ( $field['value'] ) ) {
					$output .= ' checked="checked"';
				}
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= '/><label for="' . $field['id'] . '"><span class="box"></span>' . $field['option'] . '</label>';
			break;

			case 'radio':
				foreach ( $field['options'] as $option ) {
					$output .= '<input type="radio"' .
						'name="' . $field['id'] .
						'" id="' . $field['id'] . '_' . $option['value'] .
						'" class="' . $field['id'] .
						'" value="' . $option['value'] . '" ';

					if( $field['value'] == $option['value'] ) {
						$output .= ' checked="checked"';
					}
					if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
						$output .= ' disabled="disabled"';
					}

					$output .= ' /><label for="' . $field['id'] . '_' . $option['value'] . '">' . $option['label'] . '</label><br />';
				}
			break;

			case 'checkbox_group':
				foreach( $field['options'] as $option ) {
					$output .= '<input type="checkbox"' .
						'value="' . $option['value'] .
						'" name="' . $field['id'] . '[]' .
						'" class="' . $field['id'] .
						'" id="' . $field['id'] . '_' . $option['value'] . '"';

					if( isset( $field['value'] ) && is_array( $field['value'] ) && in_array( $option['value'], $field['value'] ) ) {
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

			case 'text':
			default:
				$output .= '<input type="text" class="input regular-text"' .
					'name="' . $field['id'] .
					'" id="' . $field['id'] .
					'" value="' . $field['value'] . '" size="30"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= ' />';
			break;
		} // type switch
		if( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {
			$output .= '<br /><span class="description">' . $field['desc'] . '</span>';
		}
		$output .= '</div>';
	} // foreach field
} // if ! empty

?>
