<?php

/**
 * VCA_ASM_Validation class.
 *
 * This class contains properties and methods
 * to validate user input
 *
 * @package VcA Activity & Supporter Management
 * @since 1.3
 *
 * Structure:
 * - Properties
 * - Constructor
 * - Validation
 * - Utility
 */

if ( ! class_exists( 'VCA_ASM_Validation' ) ) :

class VCA_ASM_Validation
{

	/* ============================= CLASS PROPERTIES ============================= */

	/**
	 * Holds localized error messages in string format
	 *
	 * @var array $the_errors
	 * @since 1.3
	 * @access public
	 */
	private $the_errors = array();

	/**
	 * Whether errors have been detected
	 *
	 * @var bool $has_errors
	 * @since 1.3
	 * @access public
	 */
	public $has_errors = false;

	/**
	 * Array of found errors, duplictes removed
	 *
	 * @var array $errors
	 * @since 1.3
	 * @access public
	 */
	public $errors = array();

	/**
	 * IDs of fields that triggered an error
	 *
	 * @var array $erroneous_fields
	 * @since 1.3
	 * @access public
	 */
	public $erroneous_fields = array();

	/**
	 * The final value
	 *
	 * @var array $sanitized_val
	 * @since 1.3
	 * @access public
	 */
	public $sanitized_val = '';

	/* ============================= CONSTRUCTOR ============================= */

	/**
	 * Constructor
	 *
	 * @since 1.3
	 * @access public
	 */
	public function __construct()
	{
		/* populate translatable, human-readable error messages */
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

	/* ============================= THE VALIDATION ============================= */

	/**
	 * Validates a single input
	 *
	 * @param mixed $input		the input to validate
	 * @param array $args		(optional) arguments, see code
	 * @return bool $is_valid
	 *
	 * @since 1.3
	 * @access public
	 */
	public function is_valid( $input, $args = array() )
	{
		$default_args = array(
			'type' => 'required',
			'id' => 'the_field'
		);
		extract( wp_parse_args( $args, $default_args ) );

		$input = wp_kses_data( $input );

		switch ( $type ) {

			case 'date_conv':
				$sanitized = $this->is_date( $input );
				if ( false === $sanitized ) {
					$this->has_errors = true;
					if ( ! in_array( 'date_time', $this->errors ) ) {
						$this->errors[] = 'date_time';
					}
					$this->erroneous_fields[] = $id;
					$sanitized = time();
				}
				$this->sanitized_val = $sanitized;
				$_POST[$id] = $sanitized;
			break;

			case 'date':
				$sanitized = $this->is_date( $input, false );
				if ( false === $sanitized ) {
					$this->has_errors = true;
					if ( ! in_array( 'date_time', $this->errors ) ) {
						$this->errors[] = 'date_time';
					}
					$this->erroneous_fields[] = $id;
					$sanitized = strftime( '%d.%m.%Y', time() );
				}
				$this->sanitized_val = $sanitized;
				$_POST[$id] = $sanitized;
			break;

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
						$this->errors[] = 'date_time';
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
				if ( empty( $input ) && 0 !== $input && '0' !== $input ) {
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
     * @param bool $as_transient
     * @param array $force_errors
     * @return array $errors
     *
     * @internal param bool $set_transient
     * @global object $current_user
     *
     * @since 1.3
     * @access public
     */
	public function set_errors( $as_transient = true, $force_errors = array() )
	{
		global $current_user;

		$errors = array();
		if ( ! empty( $force_errors ) ) {
			$this->has_errors = true;
			$this->errors = $force_errors;
		}
		if ( $this->has_errors ) {
			foreach ( $this->errors as $type ) {
				if ( 'required' !== $type ) {
					$append = '.<br />' . _x( 'The system attempted to automatically correct this. Please check again.', 'Validation Error', 'vca-asm' );
				} else {
					$append = '.';
				}
				$errors[] = array(
					'type' => 'error',
					'message' => $this->the_errors[$type] . $append
				);
			}
		}
		if ( ! empty( $errors ) ) {
			if ( $as_transient ) {
				set_transient( 'admin_notices_'.$current_user->ID, $errors, 120 );
				set_transient( 'admin_warnings_'.$current_user->ID, $this->erroneous_fields, 120 );
			} else {
				return $errors;
			}
		}
	}

	/* ============================= UTILITY METHODS ============================= */

	/**
	 * Checks whether the format is NN.NN.NNNN,
	 * where N is a digit
	 *
	 * @param string $input		the date being checked
	 * @param bool $convert		(optional) whether to convert to timestamp, defaults to true
	 * @return bool
	 *
	 * @since 1.3
	 * @access public
	 */
	public function is_date( $input, $convert = true )
	{
		$date = explode( '.', $input );

		if (
			count( $date ) === 3 &&
			1 === preg_match( '/^\d\d$/', $date[0]) &&
			1 === preg_match( '/^\d\d$/', $date[1]) &&
			1 === preg_match( '/^\d\d\d\d$/', $date[2])
		) {
			if ( $convert ) {
				$stamp = mktime( 0, 0, 0,
					intval( $date[1] ),
					intval( $date[0] ),
					intval( $date[2] )
				);
				return $stamp;
			} else {
				return $input;
			}
		}
		return false;
	}

} // class

endif; // class exists

?>