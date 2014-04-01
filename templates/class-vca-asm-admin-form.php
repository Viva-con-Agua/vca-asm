<?php

/**
 * VCA_ASM_Admin_Form class.
 *
 * This class contains properties and methods
 * to display user input forms in the administrative backend
 *
 * @package VcA Activity & Supporter Management
 * @since 1.3
 */

if ( ! class_exists( 'VCA_ASM_Admin_Form' ) ) :

class VCA_ASM_Admin_Form {

	/**
	 * Class Properties
	 *
	 * @since 1.3
	 */
	private $default_args = array(
		'echo' => true,
		'form' => false,
		'name' => 'vca-asm-form',
		'method' => 'post',
		'metaboxes' => false,
		'js' => false,
		'url' => '#',
		'action' => '',
		'nonce' => 'vca-asm',
		'id' => 0,
		'button' => 'Save',
		'button_id' => 'submit',
		'top_button' => true,
		'confirm' => false,
		'confirm_text' => 'Really?',
		'back' => false,
		'back_url' => '#',
		'has_cap' => true,
		'fields' => array()
	);
	private $args = array();

	/**
	 * PHP4 style constructor
	 *
	 * @since 1.3
	 * @access public
	 */
	public function VCA_ASM_Admin_Form( $args ) {
		$this->__construct( $args );
	}

	/**
	 * PHP5 style constructor
	 *
	 * @since 1.3
	 * @access public
	 */
	public function __construct( $args ) {
		$this->default_args['button'] = __( 'Save', 'vca-asm' );

		$this->args = wp_parse_args( $args, $this->default_args );

		if ( true === $this->args['js'] ) {
			wp_enqueue_script( 'postbox' );
			add_action( 'admin_footer', array( $this, 'print_script' ) );
		}
	}
	public function print_script() {
		echo '<script>jQuery(document).ready(function(){ postboxes.add_postbox_toggles(pagenow); });</script>';
	}

	/**
	 * Constructs the form HTML,
	 * echoes or returns it
	 *
	 * @since 1.3
	 * @access public
	 */
	public function output() {
		extract( $this->args );

		$output = '';

		$the_button = '<input type="submit" name="submit" id="' . $button_id . '" class="button-primary" value="' . $button . '"';
		if ( $confirm ) {
			$the_button .= ' onclick="' .
				'if ( confirm(\'' .
					$confirm_text .
				'\') ) { return true; } return false;"';
		}
		$the_button .= '>';

		if ( $form ) {
			$output .= '<form name="' . $name . '" method="' . $method . '" action="' . $action . '">';
			if ( $back ) {
				$output .= '<a href="' . $back_url . '" class="button-secondary margin" title="' . __( 'Back to where you came from...', 'vca-asm' ) . '">' .
						'&larr; ' . __( 'back', 'vca-asm' ) .
					'</a>';
			}
			if ( $top_button && $has_cap && ! $back ) {
				$output .= '<br />';
			}
			if ( $top_button && $has_cap ) {
				$output .=  $the_button;
			}
			$output .= '<input type="hidden" name="submitted" value="y"/>' .
					'<input type="hidden" name="edit_val" value="' . $id . '"/>' ;
			if ( 'post' === $method ) {
				$output .= wp_nonce_field( $nonce, $nonce . '-nonce', false, false ) .
					wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false, false ) .
			        wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false, false );
			}
		}

		if ( $metaboxes ) {
			$output .= '<div id="poststuff"';
			if ( $js ) {
				$output .= ' class="noflow"';
			}
			$output .= '><div id="post-body" class="metabox-holder columns-1"><div id="postbox-container-1" class="postbox-container">';
			if ( $js ) {
				$output .= '<div id="normal-sortables" class="meta-box-sortables ui-sortable">';
			}
			foreach ( $fields as $box ) {
				$output .= '<div class="postbox">';
				if ( $js ) {
					$output .= '<div class="handlediv" title="' . esc_attr__( 'Click to toggle', 'vca-asm' ) . '"><br></div>' .
						'<h3 class="hndle"';
				} else {
					$output .= '<h3 class="no-hover"';
				}
				$output .= '><span>' . $box['title'] . '</span></h3>' .
					'<div class="inside">' .
						'<table class="form-table pool-form"><tbody>';

				foreach ( $box['fields'] as $field ) {
					$output .= $this->field( $field );
				}
				$output .= '</tbody></table></div></div>';
			}
		} else {
			$output .= '<table class="form-table pool-form"><tbody>';
			foreach ( $fields as $field ) {
				$output .= $this->field( $field );
			}
			$output .= '</tbody></table>';
		}

		if ( $metaboxes ) {
			if ( $js ) {
				$output .= '</div>';
			}
			$output .= '</div></div></div>';
		}

		if ( $form ) {
			if ( $has_cap ) {
				$output .= $the_button;
			}
			$output .= '</form>';
		}

		if ( $echo ) {
			echo $output;
		}
		return $output;
	}

	/**
	 * Returns the HTML
	 * for a single form table row
	 *
	 * @since 1.3
	 * @access private
	 */
	private function field( $field ) {

		$output = '';

		$field['name'] = ( ! isset( $field['name'] ) || empty( $field['name'] ) ) ? $field['id'] : $field['name'];

		if ( ! isset( $field['value'] ) ) {
			$field['value'] = '';
		}

		if ( 'hidden' !== $field['type'] ) {
			$output .= '<tr valign="top" id="row-' . $field['id'] . '"';
			if ( ( isset( $field['row-class'] ) && isset( $field['js-only'] ) && true === $field['js-only'] ) || ( isset( $field['row-class'] ) && ! empty( $field['row-class'] ) ) ) {
				$output .= 'class="';
				if ( isset( $field['row-class'] ) && ! empty( $field['row-class'] ) ) {
					$output .= $field['row-class'];
				}
				if ( isset( $field['js-only'] ) && true === $field['js-only'] ) {
					if ( isset( $field['row-class'] ) && ! empty( $field['row-class'] ) ) {
						$output .= ' ';
					}
					$output .= 'no-js-hide';
				}
				$output .= '"';
			}
			$output .= '><th scope="row">';
			if( $field['type'] != 'section' && isset( $field['label'] ) && ! empty( $field['label'] ) ) {
				$output .= '<label for="' .
					$field['id'] .
					'">' .
					$field['label'];
				if ( isset( $field['required'] ) ) {
					$output .= ' <span class="required">*</span>';
				}
				$output .= '</label>';
			}
			$output .= '</th><td>';
		}

		switch( $field['type'] ) {
			case 'section':
				$output .= '<h3>' . $field['label'] . '</h3>';
			break;

			case 'note':
				$output .= '<p class="note">' . $field['value'] . '</p>';
			break;

			case 'note_hidden':
				$output .= '<p class="note">' . $field['value'] . '</p>' .
					'<input type="hidden" ' .
						'name="' . $field['name'] .
						'" id="' . $field['id'] .
						'" class="input' .
						'" value="' . esc_attr( $field['value'] ) . '" />';
			break;

			case 'hidden':
				$output .= '<input type="hidden" ' .
					'name="' . $field['name'] .
					'" id="' . $field['id'] .
					'" class="input';
				if ( ! empty( $field['class'] ) ) {
					$output .= ' ' . $field['class'];
				}
				$output .= '" value="' . esc_attr( $field['value'] ) . '" />';
			break;

			case 'tel':
				$output .= '<input type="tel" class="input input-tel';
				if ( ! empty( $field['class'] ) ) {
					$output .= ' ' . $field['class'];
				}
				$output .= '" name="' . $field['name'] .
					'" id="' . $field['id'] .
					'" value="' . esc_attr( $field['value'] ) . '" size="40"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= ' />';
			break;

			case 'email':
				$output .= '<input type="email" class="input input-email';
				if ( ! empty( $field['class'] ) ) {
					$output .= ' ' . $field['class'];
				}
				$output .= '" name="' . $field['name'] .
					'" id="' . $field['id'] .
					'" value="' . esc_attr( $field['value'] ) . '" size="40"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= ' />';
			break;

			case 'cash_amount':
				$value_minor = ! empty( $field['value'] ) ? str_pad( abs( $field['value'] % 100 ), 2, '0', STR_PAD_LEFT ) : '';
				$value_major = ! empty( $field['value'] ) ? floor( abs( $field['value'] / 100 ) ) : '';
				$output .= '<input type="text"' .
					'name="' . $field['name'] .
					'_major" id="' . $field['id'] .
					'_major" " maxlength="5" size="5" class="input cash-major-text';
				if ( ! empty( $field['class'] ) ) {
					$output .= ' ' . $field['class'];
				}
				$output .= '" value="' . esc_attr( $value_major ) . '" size="40"';
				if ( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= ' />';
				$output .= '&nbsp;' . $field['currency_major'] . '&nbsp;&nbsp;&nbsp;';
				$output .= '<input type="text"' .
					'name="' . $field['name'] .
					'_minor" id="' . $field['id'] .
					'_minor" maxlength="2" size="2' .
					'" class="input cash-minor-text';
				if ( ! empty( $field['class'] ) ) {
					$output .= ' ' . $field['class'];
				}
				$output .= '" value="' . esc_attr( $value_minor ) . '" size="40"';
				if ( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= ' />';
				$output .= '&nbsp;' . $field['currency_minor'];
			break;

			case 'textarea':
				$output .= '<textarea name="' . $field['name'] .
					'" id="' . $field['id'] . '" class="textarea';
				if ( ! empty( $field['class'] ) ) {
					$output .= ' ' . $field['class'];
				}
				$output .= '" cols="100" rows="10"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= '>' . esc_html( $field['value'] ) . '</textarea>';
			break;

			case 'tinymce':
				$field['args'] = empty( $field['args'] ) ? array() : $field['args'];
				$field['id'] = empty( $field['args'] ) ? 'vca-editor' : $field['id'];
				add_filter( 'wp_default_editor', create_function( '', 'return "tinymce";' ) );
				ob_start();
				wp_editor( $field['value'], $field['id'], $field['args'] );
				$output .= ob_get_clean();
			break;

			case 'select':
				$output .= '<select name="' . $field['name'] .
				'" class="select';
				if ( ! empty( $field['class'] ) ) {
					$output .= ' ' . $field['class'];
				}
				$output .= '" id="' . $field['id'] . '"';
				if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= '>';

				foreach ($field['options'] as $option) {
					$output .= '<option';
					if ( ( $field['value'] == $option['value'] && $option['value'] != 0 ) || $field['value'] === $option['value'] ) {
						$output .= ' selected="selected"';
					}
					if ( ! empty( $option['class'] ) ) {
						$output .= ' class="' . $option['class'] . '"';
					}
					$output .= ' value="' . $option['value'] . '">' . $option['label'] . '&nbsp;</option>';
				}
				$output .= '</select>';
			break;

			case 'checkbox':
				$output .= '<input type="checkbox"' .
					'name="' . $field['name'] .
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
				if( isset( $field['cols'] ) ) {
					$cols = $field['cols'];
				} else {
					$cols = 1;
				}

				if ( ! empty( $field['options'] ) ) {

					if( $cols !== 1 ) {
						$output .= '<table class="table-inside-table table-mobile-collapse subtable subtable-' . $field['id'] . '"><tr><td>';
					}
					$i = 1;
					$end = count( $field['options'] );

					foreach ( $field['options'] as $option ) {
						$output .= '<input type="radio"' .
							'name="' . $field['name'] .
							'" id="' . $field['id'] . '_' . $option['value'] .
							'" value="' . $option['value'] . '" ';

						if( isset( $field['value'] ) && $field['value'] == $option['value'] ) {
							$output .= ' checked="checked"';
						}
						if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
							$output .= ' disabled="disabled"';
						}
						$output .= ' /><label for="' . $field['id'] . '_' . $option['value'] . '">' . $option['label'] . '</label>';

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
									$output .= $empty_cell;
								}
								$output .= '</td></tr></table>';
							} else {
								$output .= '</td><td>';
							}
						} else {
							$output .= '<br />';
						}
						$i++;
					}
				} else {
					$output .= '<p>' . __( 'There is no data to select...', 'vca-asm' ) . '</p>';
				}
			break;

			case 'manual_radio1':
				if( isset( $field['cols'] ) ) {
					$cols = $field['cols'];
				} else {
					$cols = 1;
				}

				if ( ! empty( $field['options'] ) ) {

					if( $cols !== 1 ) {
						$output .= '<table class="table-inside-table table-mobile-collapse subtable subtable-' . $field['id'] . '"><tr><td>';
					}
					$i = 1;
					$end = count( $field['options'] );

					foreach ( $field['options'] as $option ) {
						$output .= '<input type="radio"' .
							'name="' . $field['name'] .
							'" id="' . $field['id'] . '_' . $option['value'] .
							'" value="' . $option['value'] . '" ';

						if( $field['value'] == $option['value'] ) {
							$output .= ' checked="checked"';
						}
						if( isset( $field['disabled'] ) && $field['disabled'] === true ) {
							$output .= ' disabled="disabled"';
						}
						$output .= ' /><label for="' . $field['id'] . '_' . $option['value'] . '">' . $option['label'] . '</label>';

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
									$output .= $empty_cell;
								}
								$output .= '</td></tr></table>';
							} else {
								$output .= '</td><td>';
							}
						} else {
							$output .= '<br />';
						}
						$i++;
					}
				} else {
					$output .= '<p>' . __( 'There is no data to select...', 'vca-asm' ) . '</p>';
				}
			break;

			case 'checkbox_group':
			case 'checkbox-group':

				if( isset( $field['cols'] ) ) {
					$cols = $field['cols'];
				} else {
					$cols = 3;
				}

				if ( ! empty( $field['options'] ) ) {

					if( $cols !== 1 ) {
						$output .= '<table class="table-inside-table table-mobile-collapse subtable subtable-' . $field['id'] . '"><tr><td>';
					}
					$i = 1;
					$end = count( $field['options'] );
					foreach( $field['options'] as $option ) {

						$output .= '<input type="checkbox"' .
							'value="' . $option['value'] . '" ' .
							'name="' . $field['name'] . '[]" ' .
							'class="' . $field['id'] . '" ' .
							'id="' . $field['id'] . '_' . $option['value'] . '"';

						if( ( isset( $field['value'] ) && is_array( $field['value'] ) && in_array( $option['value'], $field['value'] ) )
							|| ( isset( $option['checked'] ) && true === $option['checked'] ) ) {
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
									$output .= $empty_cell;
								}
								$output .= '</td></tr></table>';
							} else {
								$output .= '</td><td>';
							}
						} else {
							$output .= '<br />';
						}
						$i++;
					}
				} else {
					$output .= '<p>' . __( 'There is no data to select...', 'vca-asm' ) . '</p>';
				}

				if ( isset( $field['extra'] ) && 'bulk_deselect' === $field['extra'] ) {
					$default_btn = isset( $this->args['button'] ) ? $this->args['button'] : __( 'Save', 'vca-asm' );
					$output .= '<input type="submit" class="button-primary default-button" name="submit" value="' . $default_btn . '">' .
						'<input type="submit" name="" class="button-secondary bulk-deselect" value="' .
								__( 'Deselect all', 'vca-asm' ) . '" /><br />';
				}
			break;

			case 'groups':
				global $current_user;
				get_currentuserinfo();

				if ( ! empty( $field['value'] ) && is_array( $field['value'] ) ) {
					$fc = count( $field['value'] );
					$i = 0;
					foreach ( $field['value'] as $ancestor ) {
						$i++;
						$output .=  $ancestor['name'];
						if ( $ancestor['ancestor_type'] === 'cg' || $current_user->has_cap( 'vca_asm_manage_network_global' ) ) {
							$output .=  ' (<a title="' . sprintf( __( 'Edit %s', 'vca-asm' ), $ancestor['name'] ) . '" ' .
								'href="admin.php?page=vca-asm-geography&todo=edit&id=' . $ancestor['ancestor'] . '">' . __( 'edit', 'vca-asm' ) . '</a>)';
						}
						if ( $i < $fc ) {
							$output .= '<br />';
						}
					}
				} else {
					$output .= '<em>' . __( 'not part of any group', 'vca-asm' ) . '</em>';
				}
			break;

			case 'date':
				if( ! empty( $field['value'] ) ) {
					if ( preg_match( '/^\d+$/', $field['value'] ) ) {
						$field['value'] =  intval( $field['value'] );
						$day_val = date( 'd', $field['value'] );
						$month_val = date( 'm', $field['value'] );
						$year_val = date( 'Y', $field['value'] );
						$value = $day_val . '.' . $month_val . '.' . $year_val;
					} else {
						$arr = explode( '.', $field['value'] );
						$day_val = ! empty( $arr[0] ) ? $arr[0] : date( 'd' );
						$month_val = ! empty( $arr[1] ) ? $arr[1] : date( 'm' );
						$year_val = ! empty( $arr[2] ) ? $arr[2] : date( 'Y' );
						$value = $day_val . '.' . $month_val . '.' . $year_val;
					}
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
				if ( isset( $field['mindate'] ) ) {
					$output .= ' mindate';
				}
				if ( isset( $field['maxdate'] ) ) {
					$output .= ' maxdate';
				}
				$output .= '" name="' . $field['id'] .
					'" id="' . $field['id'] .
					'" value="' . $value .
					'" size="30"';
				if ( isset( $field['mindate'] ) ) {
					$output .= ' data-min="' . intval( $field['mindate'] ) * 1000 . '"';
				}
				if ( isset( $field['maxdate'] ) ) {
					$output .= ' data-max="' . intval( $field['maxdate'] ) * 1000 . '"';
				}
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

			case 'text':
			default:
				$output .= '<input type="text"' .
					'name="' . $field['name'] .
					'" id="' . $field['id'] .
					'" class="input regular-text';
				if ( ! empty( $field['class'] ) ) {
					$output .= ' ' . $field['class'];
				}
				$output .= '" value="' . esc_attr( $field['value'] ) . '" size="40"';
				if ( isset( $field['disabled'] ) && $field['disabled'] === true ) {
					$output .= ' disabled="disabled"';
				}
				$output .= ' />';
				if ( ! empty( $field['unit'] ) ) {
					$output .= '&nbsp;' . $field['unit'];
				}
			break;
		} // type switch

		if( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {
			if ( ! in_array( $field['type'], array( 'hidden', 'checkbox_group', 'checkbox-group', 'radio', 'note', 'note_hidden' ) ) ) {
				$output .= '<br />';
			}
			$output .= '<span class="description">' . $field['desc'] . '</span>';
		}
		$output .= '</td></tr>';

		return $output;
	}

} // class

endif; // class exists

?>