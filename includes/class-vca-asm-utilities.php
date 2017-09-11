<?php

/**
 * VCA_ASM_Utilities class.
 *
 * This class contains utility methods used here and there.
 *
 * @package VcA Activity & Supporter Management
 * @since 1.0
 *
 * Structure:
 * - Properties
 * - Various Methods (no sensible sections - entire class is utilitarian)
 */

if ( ! class_exists( 'VCA_ASM_Utilities' ) ) :

class VCA_ASM_Utilities
{

	/* ============================= CLASS PROPERTIES ============================= */

	/**
	 * Array key to sort by
	 *
	 * Used in 3 different methods and not passable - hence a property
	 *
	 * @var string $sort_key
	 * @see method sort_by_key
	 * @since 1.3
	 * @access private
	 */
	private $sort_key = '';

	/* ============================= METHODS ============================= */

	/**
	 * Calculates age,
	 * i.e. the difference between two Unix Timestamps
	 *
	 * @param int $d1			timestamp, date one
	 * @param int $d2			timestamp, date two
	 * @return array $diff		difference as array of time units (seconds to years)
	 *
	 * @since 1.0
	 * @access public
	 */
	public function date_diff( $d1, $d2 )
	{
		if( $d1 < $d2 ) {
				$temp = $d2;
				$d2 = $d1;
				$d1 = $temp;
		} else {
				$temp = $d1;
		}
		$d1 = date_parse( date( "Y-m-d H:i:s", $d1 ) );
		$d2 = date_parse( date( "Y-m-d H:i:s", $d2 ) );
		/* seconds */
		if ( $d1['second'] >= $d2['second'] ){
				$diff['second'] = $d1['second'] - $d2['second'];
		} else {
				$d1['minute']--;
				$diff['second'] = 60-$d2['second']+$d1['second'];
		}
		/* minutes */
		if ( $d1['minute'] >= $d2['minute'] ){
				$diff['minute'] = $d1['minute'] - $d2['minute'];
		} else {
				$d1['hour']--;
				$diff['minute'] = 60-$d2['minute']+$d1['minute'];
		}
		/* hours */
		if ( $d1['hour'] >= $d2['hour'] ){
				$diff['hour'] = $d1['hour'] - $d2['hour'];
		} else {
				$d1['day']--;
				$diff['hour'] = 24-$d2['hour']+$d1['hour'];
		}
		/* days */
		if ( $d1['day'] >= $d2['day'] ){
				$diff['day'] = $d1['day'] - $d2['day'];
		} else {
				$d1['month']--;
				$diff['day'] = date("t",$temp)-$d2['day']+$d1['day'];
		}
		/* months */
		if ( $d1['month'] >= $d2['month'] ){
				$diff['month'] = $d1['month'] - $d2['month'];
		} else {
				$d1['year']--;
				$diff['month'] = 12-$d2['month']+$d1['month'];
		}
		/* years */
		$diff['year'] = $d1['year'] - $d2['year'];
		return $diff;
	}

	/**
	 * Replaces in-text URLs with working Links
	 *
	 * @param string $string		URL / URI
	 * @return string $string		properly formatted link (ready for insertion as href attribute)
	 *
	 * @since 1.0
	 * @access public
	 */
	public function urls_to_links( $string )
	{
		/* make sure there is an http:// on all URLs */
		$string = rtrim( preg_replace( "/([^\w\/])(www\.[a-z0-9\-]+\.[a-z0-9\-]+)/i", "$1http://$2", $string ), "/" );
		/* create links */
		$string = preg_replace( "/([\w]+:\/\/[\w-?&;%#~=\.\/\@]+[\w\/])/i", "<a target=\"_blank\" title=\"" . __( 'Visit Site', 'vca-asm' ) . "\" href=\"$1\">$1</a>", $string );

		return $string;
	}

	/**
	 * Converts DB gender strings into translatable strings
	 *
	 * @param string $string
	 * @return string $string
	 *
	 * @since 1.0
	 * @access public
	 */
	public function convert_strings( $string )
	{
		if( $string === 'male' ) {
			$string = __( 'male', 'vca-asm' );
		} elseif( $string === 'female' ) {
			$string = __( 'female', 'vca-asm' );
		} elseif( $string === 0 || $string === '0' ) {
			$string = __( 'No', 'vca-asm' );
		} elseif( $string == 1 ) {
			$string = __( 'has applied...', 'vca-asm' );
		} elseif( $string == 2 ) {
			$string = __( 'Active Member', 'vca-asm' );
		} elseif ( $string === 'Switzerland' ) {
			$string = __( 'Switzerland', 'vca-asm' );
		} elseif ( $string === 'Germany' ) {
			$string = __( 'Germany', 'vca-asm' );
		} elseif ( $string === 'Austria' ) {
			$string = __( 'Austria', 'vca-asm' );
		} elseif( empty( $string ) ) {
			$string = __( 'not set', 'vca-asm' );
		}

		return $string;
	}

	/**
	 * Returns a phone number without whitespaces, zeroes or a plus sign
	 *
	 * @param int|string $number		the phone number
	 * @param array $args				(optional) parameters determining how to format the generated output
	 * @return string $number
	 *
	 * @global object $vca_asm_geography
	 *
	 * @since 1.2
	 * @access public
	 */
	public function normalize_phone_number( $number, $args = array() )
	{
		global $vca_asm_geography;

		$default_args = array(
			'nice' => false,
			'ext' => '49',
			'nat_id' => 0
		);
		extract( wp_parse_args( $args, $default_args ), EXTR_SKIP );

		if ( is_numeric( $nat_id ) && 0 != $nat_id ) {
			$ext = $vca_asm_geography->get_phone_extension( $nat_id );
		}

		$number = preg_replace( "/[^0-9+]/", "", $number );

		if( ! empty( $number ) ) {

			if( mb_substr( $number, 0, 2 ) == '00' ) {
				$number = mb_substr( $number, 2 );
			} elseif( mb_substr( $number, 0, 1 ) == '+' ) {
				$number = mb_substr( $number, 1 );
			} elseif( mb_substr( $number, 0, 1 ) == '0' ) {
				$number = $ext . mb_substr( $number, 1 );
			}

			if( $nice === true ) {
				$number = '+' . mb_substr( $number, 0, 2 ) . ' ' . mb_substr( $number, 2, 3 ) . ' ' . mb_substr( $number, 5, 3 ) . ' ' . mb_substr( $number, 8, 3 ) . ' ' . mb_substr( $number, 11, 3 ) . ' ' . mb_substr( $number, 14 );
			}
		} else {
			$number = __( 'not set', 'vca-asm' );
		}
		return $number;
	}

    /**
     * Handles determination of how to order tabular data
     * (Often recurring code block in Administrative Backend)
     *
     * @param string $default_orderby (optional) DB column to sort by, used if $_GET['orderby'] is not set, defaults to 'name'
     * @return array
     * @since 1.3
     * @access public
     */
	public function table_order( $default_orderby = 'name' )
	{
		if( isset( $_GET['orderby'] ) ) {
			$orderby = $_GET['orderby'];
		} else {
			$orderby = $default_orderby;
		}
		if( isset( $_GET['order'] ) ) {
			$order = $_GET['order'];
			if( 'ASC' == $order ) {
				$toggle_order = 'DESC';
			} else {
				$toggle_order = 'ASC';
			}
		} else {
			$order = 'ASC';
			$toggle_order = 'DESC';
		}

		return array(
			'order' => $order,
			'orderby' => $orderby,
			'toggle_order' => $toggle_order
		);
	}

    /**
     * Sort a nested associative array by the value of a given key
     *
     * @param array @arr        the array to sort
     * @param string $key the key of whose value to sort by
     * @param string $order (optional) what direction to sort in (either 'ASC' oder 'DESC')
     *
     * @since 1.3
     * @access public
     * @return mixed
     */
	public function sort_by_key( $arr, $key, $order = 'ASC' )
	{
		$this->sort_key = $key;
		if ( 'DESC' === $order ) {
			usort( $arr, array( $this, 'sbk_cmp_desc' ) );
		} else {
			usort( $arr, array( $this, 'sbk_cmp_asc' ) );
		}
		return ( $arr );
	}

	/**
	 * usort callback
	 *
	 * @param array $a			one (sub-)array
	 * @param array $b			the other (sub-)array
	 * @return int
	 *
	 * @since 1.3
	 * @access private
	 */
	private function sbk_cmp_asc( $a, $b )
	{
		$encoding = mb_internal_encoding();
		return strcmp( mb_strtolower( $a[$this->sort_key], $encoding ), mb_strtolower( $b[$this->sort_key], $encoding ) );
	}

	/**
	 * usort callback
	 *
	 * @param array $a			one (sub-)array
	 * @param array $b			the other (sub-)array
	 * @return int
	 *
	 * @since 1.3
	 * @access private
	 */
	private function sbk_cmp_desc( $b, $a )
	{
		$encoding = mb_internal_encoding();
		return strcmp( mb_strtolower( $a[$this->sort_key], $encoding ), mb_strtolower( $b[$this->sort_key], $encoding ) );
	}

	/**
	 * Custom do_settings_sections (originally WP-core function)
	 *
	 * @param string $page				WP_Post object
	 *
	 * @global $wp_settings_sections
	 * @global $wp_settings_fields
	 *
	 * @since 1.3
	 * @access public
	 */
	public function do_settings_sections( $page )
	{
		global $wp_settings_sections, $wp_settings_fields;

		if ( ! isset( $wp_settings_sections ) || ! isset( $wp_settings_sections[$page] ) ) {
			return;
		}

		foreach ( (array) $wp_settings_sections[$page] as $section ) {
			if ( $section['title'] ) {
				echo '<div class="postbox"><h3 class="no-hover"><span>' . $section['title'] . '</span></h3><div class="inside">';
			}
			if ( $section['callback'] ) {
				call_user_func( $section['callback'], $section );
			}
			if ( ! isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section['id']] ) ) {
				continue;
			}
			echo '<table class="form-table">';
			do_settings_fields( $page, $section['id'] );
			echo '</table></div></div>';
		}
	}

	/**
	 * Returns a country-alpha-code (ISO 3166-1-alpha-2),
	 * depending on the URL/Domain used to reach the site
	 *
	 * @return string
	 *
	 * @todo this feels hacky!
	 * @see http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2
	 *
	 * @since 1.3
	 * @access public
	 */
	public function current_country()
	{
		if ( ! empty( $_SERVER['SERVER_NAME'] ) ) {
			$domain = $_SERVER['SERVER_NAME'];
		} elseif ( ! isset( $domain ) && ! empty( $_SERVER['HTTP_HOST'] ) ) {
			$domain = $_SERVER['HTTP_HOST'];
		}

		if ( 'pool.vivaconagua.ch' === $domain ) {
			return 'ch';
		}

		return 'de';
	}

	/**
	 * Checks whether a session has already been started
	 * (pre PHP 5.4)
	 *
	 * @return boolean
	 *
	 * @since 1.3
	 * @access public
	 */
	function session_is_active()
	{
		$setting = 'session.use_trans_sid';
		$current = ini_get( $setting );
		if ( false === $current ) {
			throw new UnexpectedValueException(sprintf('Setting %s does not exists.', $setting));
		}
		$result = @ini_set( $setting, $current );
		return $result !== $current;
	}

} // class

endif; // class exists

?>