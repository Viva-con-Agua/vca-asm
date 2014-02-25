<?php
	
/**
 * Template for forms used in the backend
 * (not to be used for CPTs & CFs)
 * 
 **/

if ( ! isset( $output ) ) {
	$output = '';
}

$output .= '<table class="form-table"><tbody>';

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
					'" class="input"' .
					'" value="' . $field['value'] . '" />';
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
				foreach ( $field['options'] as $option ) {
					$output .= '<input type="radio"' .
						'name="' . $field['id'] .
						'" id="' . $option['value'] .
						'" value="' . $option['value'] . '" ';
					
					if( $field['value'] == $option['value'] ) {
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
						'value="' . $option['value'] . '" ' .
						'name="' . $field['id'] . '[]" ' .
						'id="' . $option['value'] . '"';
						
					if( ( isset( $field['value'] ) && is_array( $field['value'] ) && in_array( $option['value'], $field['value'] ) )
						|| ( isset( $option['checked'] ) && $option['checked'] === true ) ) {
						$output .= ' checked="checked"';
					}
					if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
						$output .= ' disabled="disabled"';
					}
					
					$output .= ' /><label for="' .$option['value'] . '">' .
						$option['label'] .
						'</label><br />';  
				}
			break;
		
			case 'text':
			default:
				$output .= '<input type="text" class="regular-text"' .
					'name="' . $field['id'] .
					'" id="' . $field['id'] .
					'" class="input"' .
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
