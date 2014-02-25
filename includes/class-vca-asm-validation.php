<?php

/**
 * VCA_ASM_Validation class.
 *
 * This class contains properties and methods
 * to validate user input
 *
 * @package VcA Activity & Supporter Management
 * @since 1.3
 */

if ( ! class_exists( 'VCA_ASM_Validation' ) ) :

class VCA_ASM_Validation {

	/**
	 * Class Properties
	 *
	 * @since 1.3
	 */
	private $the_errors = array();

	public $has_errors = false;
	public $errors = array();
	public $erroneous_fields = array();
	public $sanitized_val = '';

	/**
	 * Validates a single input
	 *
	 * @param mixed $input
	 * @param string $type
	 *
	 * @return bool $is_valid
	 *
	 * @since 1.3
	 * @access public
	 */
	public function is_valid( $input, $args ) {
		$default_args = array(
			'type' => 'required',
			'id' => 'the_field'
		);
		extract( wp_parse_args( $args, $default_args ) );


		switch ( $type ) {

			case 'numbers':
				if ( ! is_numeric( $input ) ) {
					$this->has_errors = true;
					if ( ! in_array( $type, $this->errors ) ) {
						$this->errors[] = $type;
					}
					$this->erroneous_fields[] = $id;
				}
				$this->sanitized_val = preg_replace( '/[^0-9,.]/', '', $input );
			break;

			case 'phone':
				$phone = preg_replace( '/\s/', '', $input );
				if ( ! empty( $phone ) ) {
					$phone_first = substr( $phone, 0, 1 );
					$phone_rest = substr( $phone, 1 );
					if (
						preg_match( '/\D/', $phone_rest ) ||
						preg_match( '/[^0-9+]/', $phone_first ) ||
						6 > $phone
					) {
						$this->has_errors = true;
						if ( ! in_array( $type, $this->errors ) ) {
							$this->errors[] = $type;
						}
						$this->erroneous_fields[] = $id;
					}
					$this->sanitized_val = preg_replace( '/[^0-9+]/', '', $phone_first ) .  preg_replace( '/\D/', '', $phone_rest );
				} else {
					$this->sanitized_val = '';
				}
			break;

			/*
			 * Application Phase & Event Date/Time Order Validation
			 * --> non-ideal: the fields/metaboxes array iterated over must have dates in correct chronological order...
			 */

			case 'start_app':
				$sanitized = $this->is_date( $input );
				if ( false === $sanitized ) {
					$this->has_errors = true;
					if ( ! in_array( 'date_time', $this->errors ) ) {
						$this->errors[] = $type;
					}
					$this->erroneous_fields[] = $id;
					$sanitized = time();
				}
				$this->sanitized_val = $sanitized;
				$_POST[$id] = $sanitized;
			break;

			case 'end_app':
				$sanitized = $this->is_date( $input );
				if ( false === $sanitized ) {
					$this->has_errors = true;
					if ( ! in_array( 'date_time', $this->errors ) ) {
						$this->errors[] = $type;
					}
					$this->erroneous_fields[] = $id;
					$sanitized = $_POST['start_app'] + 24*60*60;
				} elseif ( $sanitized < $_POST['start_app'] ) {
					$this->has_errors = true;
					if ( ! in_array( $type, $this->errors ) ) {
						$this->errors[] = $type;
					}
					$this->erroneous_fields[] = $id;
					$sanitized = $_POST['start_app'] + 24*60*60;
				}
				$this->sanitized_val = $sanitized;
				$_POST[$id] = $sanitized;
			break;

			case 'start_act':
				$sanitized_date = $this->is_date( $input );
				if ( false === $sanitized_date ) {
					$this->has_errors = true;
					if ( ! in_array( 'date_time', $this->errors ) ) {
						$this->errors[] = $type;
					}
					$this->erroneous_fields[] = $id;
					$sanitized_date = $_POST['end_app'] + 24*60*60;
				}
				$sanitized_hour = $_POST['start_act_hour'];
				if ( ! is_numeric( $sanitized_hour ) ) {
					$this->has_errors = true;
					if ( ! in_array( 'date_time', $this->errors ) ) {
						$this->errors[] = $type;
					}
					$this->erroneous_fields[] = $id;
				}
				$sanitized_hour = intval( $sanitized_hour );
				$sanitized_hour = ( 0 > $sanitized_hour ) ? 0 : ( ( 23 < $sanitized_hour ) ? 23 : $sanitized_hour );
				$sanitized_minutes = $_POST['start_act_minutes'];
				if ( ! is_numeric( $sanitized_minutes ) ) {
					$this->has_errors = true;
					if ( ! in_array( 'date_time', $this->errors ) ) {
						$this->errors[] = $type;
					}
					$this->erroneous_fields[] = $id;
				}
				$sanitized_minutes = intval( $sanitized_minutes );
				$sanitized_minutes = in_array( $sanitized_minutes, array( 0, 15, 30, 45 ) ) ? $sanitized_minutes : round( $sanitized_minutes / 15 ) * 15;

				$stamp = $sanitized_date + $sanitized_hour*60*60 + $sanitized_minutes*60;

				if ( $stamp < $_POST['end_app'] ) {
					$this->has_errors = true;
					if ( ! in_array( $type, $this->errors ) ) {
						$this->errors[] = $type;
					}
					$this->erroneous_fields[] = $id;
					$stamp = $_POST['end_app'] + 44*60*60;
				}
				$this->sanitized_val = $stamp;
				$_POST[$id] = $stamp;
			break;

			case 'end_act':
				$sanitized_date = $this->is_date( $input );
				if ( false === $sanitized_date ) {
					$this->has_errors = true;
					if ( ! in_array( 'date_time', $this->errors ) ) {
						$this->errors[] = $type;
					}
					$this->erroneous_fields[] = $id;
					$sanitized_date = ceil( $_POST['start_act'] / (24*60*60) ) * 24*60*60;
				}
				$sanitized_hour = $_POST['end_act_hour'];
				if ( ! is_numeric( $sanitized_hour ) ) {
					$this->has_errors = true;
					if ( ! in_array( 'date_time', $this->errors ) ) {
						$this->errors[] = $type;
					}
					$this->erroneous_fields[] = $id;
				}
				$sanitized_hour = intval( $sanitized_hour );
				$sanitized_hour = ( 0 > $sanitized_hour ) ? 0 : ( ( 23 < $sanitized_hour ) ? 23 : $sanitized_hour );
				$sanitized_minutes = $_POST['end_act_minutes'];
				if ( ! is_numeric( $sanitized_minutes ) ) {
					$this->has_errors = true;
					if ( ! in_array( 'date_time', $this->errors ) ) {
						$this->errors[] = $type;
					}
					$this->erroneous_fields[] = $id;
				}
				$sanitized_minutes = intval( $sanitized_minutes );
				$sanitized_minutes = in_array( $sanitized_minutes, array( 0, 15, 30, 45 ) ) ? $sanitized_minutes : round( $sanitized_minutes / 15 ) * 15;

				$stamp = $sanitized_date + $sanitized_hour*60*60 + $sanitized_minutes*60;

				if ( $stamp < $_POST['start_act'] ) {
					$this->has_errors = true;
					if ( ! in_array( $type, $this->errors ) ) {
						$this->errors[] = $type;
					}
					$this->erroneous_fields[] = $id;
					$stamp = ceil( $_POST['start_act'] / (24*60*60) ) * 24*60*60 + 2*60*60;
				}
				$this->sanitized_val = $stamp;
			break;

			case 'required':
			default:
				if ( empty( $input ) && 0 != $input ) {
					$this->has_errors = true;
					if ( ! in_array( $type, $this->errors ) ) {
						$this->errors[] = $type;
					}
					$this->erroneous_fields[] = $id;
				}
				$this->sanitized_val = $input;
			break;
		}

		return true;
	}

	/**
	 * Sets an error message transient
	 *
	 * @since 1.3
	 * @access public
	 */
	public function set_errors() {
		global $current_user;
		get_currentuserinfo();

		$errors = array();
		if ( $this->has_errors ) {
			foreach ( $this->errors as $type ) {
				$errors[] = array(
					'type' => 'error',
					'message' => $this->the_errors[$type] . '.<br />' . _x( 'The system attempted to automatically correct this. Please check again.', 'Validation Error', 'vca-asm' )
				);
			}
		}
		if ( ! empty( $errors ) ) {
			set_transient( 'admin_notices_'.$current_user->ID, $errors, 120 );
			set_transient( 'admin_warnings_'.$current_user->ID, $this->erroneous_fields, 120 );
		}
	}

	/***** utility methods *****/

	/**
	 * Checks whether the format is NN.NN.NNNN,
	 * where N is a digit
	 *
	 * @since 1.3
	 * @access private
	 */
	private function is_date( $input ) {
		$date = explode( '.', $input );

		if (
			count( $date ) === 3 &&
			1 === preg_match( '/^\d\d$/', $date[0]) &&
			1 === preg_match( '/^\d\d$/', $date[1]) &&
			1 === preg_match( '/^\d\d\d\d$/', $date[2])
		) {
			$stamp = mktime( 0, 0, 0,
				intval( $date[1] ),
				intval( $date[0] ),
				intval( $date[2] )
			);
			return $stamp;
		}
		return false;
	}

	/**
	 * PHP4 style constructor
	 *
	 * @since 1.3
	 * @access public
	 */
	public function VCA_ASM_Validation() {

	}

	/**
	 * PHP5 style constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
		$this->the_errors = array(
			'required' => _x( 'You have not filled out all the required fields', 'Validation Error', 'vca-asm' ),
			'numbers' => _x( 'Some fields only accept numeric values', 'Validation Error', 'vca-asm' ),
			'phone' => _x( 'A phone number you have entered is not valid', 'Validation Error', 'vca-asm' ),
			'end_app' => _x( 'The end of the application phase must come after its beginning', 'Validation Error', 'vca-asm' ),
			'start_act' => _x( 'The application phase must have ended before the start of the activity', 'Validation Error', 'vca-asm' ),
			'end_act' => _x( 'The activity can\'t be over before it has started', 'Validation Error', 'vca-asm' ),
			'date_time' => _x( 'There is an error in the date- and/or time-formatting', 'Validation Error', 'vca-asm' )
		);
	}

} // class

endif; // class exists

?>