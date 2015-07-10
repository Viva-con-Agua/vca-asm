<?php

/**
 * VCA_ASM_Geography class.
 *
 * This class contains properties and methods for
 * the handling of geographical units & their hierarchy.
 *
 * Further it provides a method that returns
 * an array of regions for use in other classes.
 *
 * @package VcA Activity & Supporter Management
 * @since 1.0
 *
 * Structure:
 * - Properties
 * - Constructor
 * - Geography Management (Deleting & Sanitization)
 * - Core Hierarchy
 * - Fetching Data
 * - Boolean Tests
 * - Options for HTML select tags
 */

if ( ! class_exists( 'VCA_ASM_Geography' ) ) :

class VCA_ASM_Geography
{

	/* ============================= CLASS PROPERTIES ============================= */

	/**
	 * Nested associative array of national and municipal data
	 *
	 * @var array $national_hierarchy
	 * @see constructor
	 * @since 1.3
	 * @access public
	 */
	public $national_hierarchy = array();

	/**
	 * Array of all available countries
	 * with (int) ID => (string) name key/value pairs
	 *
	 * @var array $countries
	 * @see constructor
	 * @since 1.3
	 * @access public
	 */
	public $countries = array();

	/**
	 * Array of all available cities
	 * with (int) ID => (string) name key/value pairs
	 *
	 * @var array $cities
	 * @see constructor
	 * @since 1.3
	 * @access public
	 */
	public $cities = array();

	/* ============================= CONSTRUCTOR ============================= */

	/**
	 * Constructor
	 *
	 * Sets the three class properties
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct()
	{
		/* national hierarchy, nested associative array of national and municipal data */
		$nations = $this->get_all( 'name', 'ASC', 'nation' );
		$i = 0;
		foreach ( $nations as $nation ) {
			$this->national_hierarchy[$i] = array(
				'name' => $nation['name'],
				'id' => $nation['id']
			);
			$this->countries[$nation['id']] = $nation['name'];

			$this->national_hierarchy[$i]['cities'] = array();
			$cities = $this->get_descendants( $nation['id'], array( 'data' => 'all', 'sorted' => true ) );

			foreach ( $cities as $city ) {
				$this->national_hierarchy[$i]['cities'][] = array(
					'name' => $city['name'],
					'id' => $city['id']
				);
				$this->cities[$city['id']] = $city['name'];
			}

			$i++;
		}
	}

	/* ============================= MANAGE GEOGRAPHY ============================= */

	/**
	 * Deletes a geographical unit
	 * and sanitizes dependent database entries
	 *
	 * @param int $id				the ID in the DB table of the unit to delete
	 * @return bool $success
	 *
	 * @global object $wpdb
	 * @global object $vca_asm_finances
	 * @global object $vca_asm_admin_settings
	 *
	 * @since 1.3
	 * @access public
	 */
	public function delete( $id )
	{
		global $wpdb,
			$vca_asm_finances,
			$vca_asm_admin_settings;

		$pre_delete_query = $wpdb->get_results(
			"SELECT has_user, user_id, pass, user, type FROM " .
			$wpdb->prefix . "vca_asm_geography " .
			"WHERE id = " . $id . " LIMIT 1", ARRAY_A
		);
		$pre_delete_data = isset( $pre_delete_query[0] ) ? $pre_delete_query[0] : '';

		$wpdb->query(
			"DELETE FROM " .
			$wpdb->prefix . "vca_asm_geography " .
			"WHERE id = " . $id . " LIMIT 1"
		);
		if ( is_array( $pre_delete_data ) && 1 == $pre_delete_data['has_user'] ) {
			wp_delete_user( $pre_delete_data['user_id'] );
		}
		if ( is_array( $pre_delete_data ) && 'nation' === $pre_delete_data['type'] ) {
			$vca_asm_admin_settings->delete_autoresponses( $id );
		}
		$wpdb->query(
			"DELETE FROM " .
			$wpdb->prefix . "vca_asm_geography_hierarchy " .
			"WHERE descendant = " . $id . " OR ancestor = " . $id
		);

		$vca_asm_finances->delete_account( $id, 'all', true );

		return true;
	}

	/**
	 * Updates the supporter and member count in the geography table
	 *
	 * @return void
	 *
	 * @global object $wpdb
	 *
	 * @todo mem_count in single SQL query
	 * @todo check general validity and reliability
	 *
	 * @since 1.0
	 * @access public
	 */
	public function update_member_count()
	{
		global $wpdb;

		$raw = $this->get_all();

		$level1 = array();
		$level2 = array();
		foreach( $raw as $region ) {
			if ( 'cg' === $region['type'] || 'nation' === $region['type'] ) {
				$level1[] = $region;
			} elseif ( 'ng' === $region['type'] ) {
				$level2[] = $region;
			} else {
				$supporters = $wpdb->get_results(
					"SELECT user_id FROM " .
					$wpdb->prefix . "usermeta " .
					"WHERE meta_key = 'city' AND meta_value = " .
					$region['id'], ARRAY_A
				);
				$supp_count = count( $supporters );
				$mem_count = 0;
				foreach( $supporters as $supporter ) {
					$user = new WP_User( $supporter['user_id'] );
					if ( ! in_array( 'city', $user->roles ) ) {
						$mem_status = get_user_meta( $supporter['user_id'], 'membership', true );
						if ( $mem_status == 2 ) {
							$mem_count++;
						}
					} else {
						$supp_count--;
					}
				}

				$wpdb->update(
					$wpdb->prefix.'vca_asm_geography',
					array(
						'supporters' => $supp_count,
						'members' => $mem_count
					),
					array( 'id'=> $region['id'] ),
					array( '%d', '%d' ),
					array( '%d' )
				);
			}
		}
		foreach ( $level1 as $region ) {
			$supp_count = 0;
			$mem_count = 0;
			$descendants = $this->get_descendants( $region['id'], array( 'data' => 'id', 'format' => 'array', 'type' => 'city' ) );
			foreach ( $descendants as $descendant ) {
				$count_query = $wpdb->get_results(
						"SELECT supporters, members FROM " .
						$wpdb->prefix . "vca_asm_geography " .
						"WHERE id = " . $descendant . " LIMIT 1", ARRAY_A
				);
				$supp_count = $supp_count + intval( $count_query[0]['supporters'] );
				$mem_count = $mem_count + intval( $count_query[0]['members'] );
			}
			$wpdb->update(
				$wpdb->prefix.'vca_asm_geography',
				array(
					'supporters' => $supp_count,
					'members' => $mem_count
				),
				array( 'id'=> $region['id'] ),
				array( '%d', '%d' ),
				array( '%d' )
			);
		}
		foreach ( $level2 as $region ) {
			$supp_count = 0;
			$mem_count = 0;
			$descendants = $this->get_descendants( $region['id'], array( 'data' => 'id', 'format' => 'array' ) );
			foreach ( $descendants as $descendant ) {
				$count_query = $wpdb->get_results(
						"SELECT supporters, members FROM " .
						$wpdb->prefix . "vca_asm_geography " .
						"WHERE id = " . $descendant . " LIMIT 1", ARRAY_A
				);
				$supp_count = $supp_count + intval( $count_query[0]['supporters'] );
				$mem_count = $mem_count + intval( $count_query[0]['members'] );
			}
			$wpdb->update(
				$wpdb->prefix.'vca_asm_geography',
				array(
					'supporters' => $supp_count,
					'members' => $mem_count
				),
				array( 'id'=> $region['id'] ),
				array( '%d', '%d' ),
				array( '%d' )
			);
		}
	}

	/* ============================= CORE HIERARCHY ============================= */

	/**
	 * Returns a region's ancestors (if any)
	 *
	 * @param int $id				ID of a geographical unit that can have ancestors (such as a city, city group or nation)
	 * @param array $args			(optional) arguments of how to format the result, see code
	 * @return mixed $ancestors
	 *
	 * @global object $wpdb
	 * @global object $vca_asm_utilities
	 *
	 * @since 1.3
	 * @access public
	 */
	public function get_ancestors( $id, $args = array() )
	{
		global $wpdb,
			$vca_asm_utilities;

		$default_args = array(
			'data' => 'name',		// what type of data to return per geo-unit
			'format' => 'string',	// whether to return an array or a concatenated string
			'concat' => ', ',		// what string to concatenate by
			'deep' => false,		// whether to return multiple levels of descendants as a nested array
			'type' => 'all'			// what type of descendant to return
		);
		extract( wp_parse_args( $args, $default_args ), EXTR_SKIP );

		$where = "WHERE descendant = " . $id;
		if ( 'all' !== $type && true !== $deep ) {
			$where .= " AND ancestor_type = '" .  $type . "'";
		}

		$ancestors_query = $wpdb->get_results(
			"SELECT * FROM " .
			$wpdb->prefix . "vca_asm_geography_hierarchy " .
			$where, ARRAY_A
		);

		$ngs = array();
		$nations = array();
		$ancestors_arr = array();
		foreach ( $ancestors_query as $ancestor ) {
			if ( 'nation' === $ancestor['ancestor_type'] && true === $deep && in_array( $type, array( 'all', 'ng' ) ) ) {
				$ng_query = $wpdb->get_results(
					"SELECT * FROM " .
					$wpdb->prefix . "vca_asm_geography_hierarchy " .
					" WHERE descendant = " . $ancestor['ancestor'], ARRAY_A
				);
				foreach ( $ng_query as $ng ) {
					if ( 'name' === $data ) {
						$ngs[] = $this->get_name( $ng['ancestor'] );
					} elseif ( 'both' === $data ) {
						$ngs[$ng['ancestor']] = $this->get_name( $ng['ancestor'] );
					} elseif ( 'all' === $data ) {
						$ng['name'] = $this->get_name( $ng['ancestor'] );
						$ngs[] = $ng;
					} else {
						$ngs[] = $ng['ancestor'];
					}
				}
			}
			if ( 'cg' === $ancestor['ancestor_type'] && in_array( $type, array( 'all', 'cg' ) ) ) {
				if ( 'name' === $data ) {
					$ancestors_arr[] = $this->get_name( $ancestor['ancestor'] );
				} elseif ( 'both' === $data ) {
					$ancestors_arr[$ancestor['ancestor']] = $this->get_name( $ancestor['ancestor'] );
				} elseif ( 'all' === $data ) {
					$ancestor['name'] = $this->get_name( $ancestor['ancestor'] );
					$ancestors_arr[] = $ancestor;
				} else {
					$ancestors_arr[] = $ancestor['ancestor'];
				}
			} elseif ( in_array( $type, array( 'all', 'nation' ) ) ) {
				if ( 'name' === $data ) {
					$nations[] = $this->get_name( $ancestor['ancestor'] );
				} elseif ( 'both' === $data ) {
					$nations[$ancestor['ancestor']] = $this->get_name( $ancestor['ancestor'] );
				} elseif ( 'all' === $data ) {
					$ancestor['name'] = $this->get_name( $ancestor['ancestor'] );
					$nations[] = $ancestor;
				} else {
					$nations[] = $ancestor['ancestor'];
				}
			}
		}
		if ( 'name' === $data ) {
			sort( $ancestors_arr );
			sort( $nations );
			sort( $ngs );
		} elseif ( 'both' === $data ) {
			asort( $ancestors_arr );
			asort( $nations );
			asort( $ngs );
		} elseif ( 'all' === $data ) {
			$ancestors_arr = $vca_asm_utilities->sort_by_key( $ancestors_arr, 'name' );
			$nations = $vca_asm_utilities->sort_by_key( $nations, 'name' );
			$ngs = $vca_asm_utilities->sort_by_key( $ngs, 'name' );
		}
		if ( 'both' === $data  ) {
			$ancestors_arr = $ancestors_arr + $nations + $ngs;
		} else {
			$ancestors_arr = array_merge( $ancestors_arr, $nations, $ngs );
		}

		if ( 'array' === $format ) {
			$ancestors = $ancestors_arr;
		} else {
			$ancestors = implode( $concat, $ancestors_arr );
		}

		return $ancestors;
	}

	/**
	 * Returns a region's descendants (if any)
	 *
	 * @param int $id				ID of a geographical unit that can have descendants (such as a city group, nation or nation group)
	 * @param array $args			(optional) arguments of how to format the result
	 * @return mixed $descendants
	 *
	 * @global object $wpdb
	 *
	 * @since 1.3
	 * @access public
	 */
	public function get_descendants( $id, $args = array() )
	{
		global $wpdb;

		$default_args = array(
			'data' => 'name',		// what type of data to return per geo-unit
			'format' => 'array',	// whether to return an array or a concatenated string
			'concat' => ', ',		// what string to concatenate by
			'type' => 'all',		// what type of descendant to return
			'grouped' => true,		// whether type should be 'city' or split into 'cell' & 'lc', if applicable
			'sorted'=> false		// whether to sort the output array by name of geographical unit
		);
		extract( wp_parse_args( $args, $default_args ), EXTR_SKIP );

		$where = "WHERE ancestor = " . $id;
		$descendants_query = $wpdb->get_results(
			"SELECT * FROM " .
			$wpdb->prefix . "vca_asm_geography_hierarchy " .
			$where, ARRAY_A
		);

		$descendants_raw = $descendants_query;

		if ( ! empty( $descendants_query ) && 'ng' === $descendants_query[0]['ancestor_type'] && in_array( $type, array( 'all', 'city' ) ) ) {
			foreach ( $descendants_query as $descendant ) {
				$sub_query = $wpdb->get_results(
					"SELECT * FROM " .
					$wpdb->prefix . "vca_asm_geography_hierarchy " .
					"WHERE ancestor = " . $descendant['descendant'], ARRAY_A
				);
				if ( ! empty( $sub_query ) ) {
					$descendants_raw = array_merge( $descendants_raw, $sub_query );
				}
			}
		}

		$descendants_arr = array();
		$names = array();
		foreach ( $descendants_raw as $descendant ) {
			if (
				'all' === $type ||
				$this->get_type( $descendant['descendant'], false, true ) === $type
			) {
				if ( 'name' === $data ) {
					$descendants_arr[] = $this->get_name( $descendant['descendant'] );
				} elseif ( 'all' === $data ) {
					$name = $this->get_name( $descendant['descendant'] );
					$names[] = $name;
					$descendants_arr[] = array(
						'id' => $descendant['descendant'],
						'name' => $name,
						'type' => $this->get_type( $descendant['descendant'], false, $grouped )
					);
				} else {
					$descendants_arr[] = $descendant['descendant'];
				}
			}
		}

		if ( true === $sorted && 'name' === $data ) {
			usort( $descendants_arr, 'strnatcasecmp' );
		} elseif ( true === $sorted && 'all' === $data ) {
			$lowercase_names = array_map( 'strtolower', $names );
			array_multisort( $lowercase_names, SORT_ASC, $descendants_arr );
		}

		if ( 'string' === $format ) {
			$descendants = implode( $concat, $descendants_arr );
		} else {
			$descendants = $descendants_arr;
		}

		return $descendants;
	}

	/* ============================= FETCHING DATA ============================= */

	/**
	 * Returns the name of a geographical unit by its ID
	 *
	 * @param int $id				(geographical) ID
	 * @return string $geo_name		name of the city, nation or group
	 *
	 * @global object $wpdb
	 * @global object $vca_asm_utilities
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_name( $id )
	{
		global $wpdb,
			$vca_asm_utilities;

		$geo_query = $wpdb->get_results(
			"SELECT name FROM " .
			$wpdb->prefix . "vca_asm_geography " .
			"WHERE id = " . $id, ARRAY_A
		);
		$geo_name = isset( $geo_query[0] ) ? $geo_query[0]['name'] : '';
		if( empty( $geo_name ) ) {
			$geo_name = sprintf( __( 'Error: Geographical unit of ID %s does not exist.', 'vca-asm' ), $id );
		}

		$geo_name = $vca_asm_utilities->convert_strings( $geo_name );

		return $geo_name;
	}

	/**
	 * Returns the currency name of a geographical unit by its ID
	 *
	 * @param int $id
	 * @param string $type			(optional)
	 * @return string $currency
	 *
	 * @since 1.3
	 * @access public
	 */
	public function get_currency( $id, $type = 'name' )
	{
		global $wpdb;

		if ( ! in_array( $type, array( 'name', 'minor_name', 'code' ) ) ) {
			$type = 'name';
		}

		if ( $this->is_city( $id ) ) {
			$nat_id = $this->has_nation( $id );
			if ( ! $nat_id ) {
				return false;
			}
		} elseif ( $this->is_nation( $id ) ) {
			$nat_id = $id;
		} else {
			return false;
		}

		$currency_query = $wpdb->get_results(
				"SELECT currency_" . $type . " FROM " .
				$wpdb->prefix . "vca_asm_geography " .
				"WHERE id = " . $nat_id . " LIMIT 1", ARRAY_A
		);
		$currency = ! empty( $currency_query[0]['currency_'.$type] ) ? $currency_query[0]['currency_'.$type] : ( 'minor_name' === $type ? 'Cent' : 'Euro' );

		return $currency;
	}

	/**
	 * Returns the 2-letter ISO 3166-1 code of a country or
	 * a somewhat more arbitraty code of any other geographical unit if fed its ID
	 *
	 * @param int $id			(geographical) ID
	 * @return string $alpha
	 *
	 * @since 1.3
	 * @access public
	 */
	public function get_alpha_code( $id )
	{
		global $wpdb;

		$alpha_query = $wpdb->get_results(
				"SELECT alpha_code FROM " .
				$wpdb->prefix . "vca_asm_geography " .
				"WHERE id = " . $id . " LIMIT 1", ARRAY_A
		);
		$alpha = ( ! empty( $alpha_query ) && isset( $alpha_query[0]['alpha_code'] ) ) ? $alpha_query[0]['alpha_code'] : 'de';

		return $alpha;
	}

	/**
	 * Returns the phone extension geographical unit by its ID
	 *
	 * @param int $id			(geographical) ID
	 * @return string $ext
	 *
	 * @since 1.3
	 * @access public
	 */
	public function get_phone_extension( $id )
	{
		global $wpdb;

		$ext_query = $wpdb->get_results(
				"SELECT phone_code FROM " .
				$wpdb->prefix . "vca_asm_geography " .
				"WHERE id = " . $id . " LIMIT 1", ARRAY_A
		);
		$ext = $ext_query[0]['phone_code'];

		return $ext;
	}

	/**
	 * Returns an array of raw region data
	 *
	 * @param string $orderby			(optional) the DB column to order by, defaults to 'name'
	 * @param string $order				(optional) either 'ASC' or 'DESC', defaults to 'ASC'
	 * @param string $type				(optional) the type of data to return, defaults to 'all'
	 * @return array $regions
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_all( $orderby = 'name', $order = 'ASC', $type = 'all' )
	{
		global $wpdb;

		if ( 'all' === $type ) {
			$regions = $wpdb->get_results(
				"SELECT * FROM " .
				$wpdb->prefix . "vca_asm_geography " .
				"ORDER BY " .
				$orderby . " " . $order, ARRAY_A
			);
		} else {
			$where = "WHERE type = ";
			$where .= 'city' === $type ? "'lc' OR type = 'cell' OR type = 'city' " : "'" . $type . "'";
			$regions = $wpdb->get_results(
				"SELECT * FROM " .
				$wpdb->prefix . "vca_asm_geography " .
				$where .
				"ORDER BY " .
				$orderby . " " . $order, ARRAY_A
			);
		}

		for ( $i = 0; $i < count( $regions ); $i++ ) {
			$regions[$i]['groups'] = $this->get_ancestors( $regions[$i]['id'], array( 'deep' => true ) );
		}

		return $regions;
	}

	/**
	 * Fetches cities that are not part of a specified parent type
	 *
	 * @param string $without		(optional) the (non-existent) parent type, defaults to 'nation'
	 * @return array $cities
	 *
	 * @since 1.3
	 * @access public
	 */
	public function get_cities_without( $without = 'nation' )
	{
		global $wpdb;

		$all_cities = $wpdb->get_results(
			"SELECT id FROM " .
			$wpdb->prefix . "vca_asm_geography " .
			"WHERE type = 'lc' OR type = 'cell' OR type = 'city'", ARRAY_A
		);

		$cities = array( 0 );
		foreach ( $all_cities as $city ) {
			if ( 'cg' === $without && ! $this->has_cg( $city['id'] ) ) {
				$cities[] = $city['id'];
			} elseif ( 'nation' === $without && ! $this->has_nation( $city['id'] ) ) {
				$cities[] = $city['id'];
			} elseif ( 'ng' === $without && ! $this->has_ng( $city['id'] ) ) {
				$cities[] = $city['id'];
			}
		}

		return $cities;
	}

	/**
	 * Fetches nations that are not part of a group
	 *
	 * @param string $without		(optional) the (non-existent) parent type, defaults to 'ng' (so far the only option)
	 * @return array $nations
	 *
	 * @since 1.3
	 * @access public
	 */
	public function get_nations_without( $without = 'ng' )
	{
		global $wpdb;

		$all_nations = $wpdb->get_results(
			"SELECT id FROM " .
			$wpdb->prefix . "vca_asm_geography " .
			"WHERE type = 'nation'", ARRAY_A
		);

		$nations = array( 0 );
		foreach ( $all_nations as $nation ) {
			if ( 'ng' === $without && ! $this->has_ng( $nation['id'] ) ) {
				$nations[] = $nation['id'];
			}
		}

		return $nations;
	}

	/**
	 * Returns a city's first found city group
	 * (group to city relationships are not unique, a city may be part of several groups)
	 *
	 * @param int $city_id		the ID of the city to get the (first found) group of
	 * @return int $group		the ID of the group
	 *
	 * @since 1.4
	 * @access public
	 */
	public function get_city_group( $city_id )
	{
		$result = $this->get_ancestors( $city_id, array(
			'data' => 'both',
			'format' => 'array',
			'type' => 'cg'
		));

		if ( empty( $result ) ) {
			return false;
		} else {
			$group = key($result);
		}

		return $group;
	}

	/**
	 * Converts type string to proper name
	 *
	 * @param string $type		slug, lower-case type string
	 * @param bool $short		(optional) whether to return short or long from, defaults to false
	 * @return string $name		properly formatted and translatable name
	 *
	 * @since 1.0
	 * @access public
	 */
	public function convert_type( $type, $short = false )
	{
		switch( $type ) {
			case 'cell':
				return __( 'Cell', 'vca-asm' );
			break;
			case 'lc':
				return $short ? __( 'LC', 'vca-asm' ) : __( 'Local Crew', 'vca-asm' );
			break;
			case 'cg':
				return __( 'City Group', 'vca-asm' );
			break;
			case 'nation':
				return __( 'Country', 'vca-asm' );
			break;
			case 'ng':
				return __( 'Country Group', 'vca-asm' );
			break;
			case 'none':
				return __( 'does not exist', 'vca-asm' );
			break;
			case 'city':
			default:
				return __( 'City', 'vca-asm' );
			break;
		}
	}

	/**
	 * Returns an array of geographical units with id as key and name as value
	 *
	 * @param string $type		(optional) type of unit, i.e. 'city', 'cg', 'nation' or 'ng', defaults to 'all'
	 * @return string[] $regions
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_names( $type = 'all' )
	{
		$raw = $this->get_all( 'name', 'ASC', $type );
		$regions = array();

		foreach( $raw as $region ) {
			$regions[$region['id']] = $region['name'];
		}
		if ( 'city' === $type ) {
			$regions['0'] = __( 'not chosen...', 'vca-asm' );
		} elseif ( 'nation' === $type ) {
			$regions['0'] = __( 'other, non-listed country', 'vca-asm' );
			$regions['empty'] = __( 'not chosen...', 'vca-asm' );
		} else {
			$regions['0'] = _x( 'no regional info', 'Geography', 'vca-asm' );
		}

		return $regions;
	}

	/**
	 * Returns the type of a region by ID
	 *
	 * @param int $id				the geographical ID
	 * @param bool $converted		(optional) whether to convert into human readable form, defaults to true
	 * @param bool $grouped			(optional) whether to split cities into LCs and cells, defaults to false
	 * @param bool $short			(optional) whether to use short or long from ("LC" vs. "Local Crew"), defaults to false
	 * @return string $type
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_type( $id, $converted = true, $grouped = false, $short = false )
	{
		global $wpdb;

		$type_query = $wpdb->get_results(
			"SELECT type FROM " .
			$wpdb->prefix . "vca_asm_geography " .
			"WHERE id = " . $id, ARRAY_A
		);
		$type = isset( $type_query[0]['type'] ) ? $type_query[0]['type'] : '';
		if ( empty( $type ) ) {
			$type = 'none';
		} elseif ( true === $grouped && in_array( $type, array( 'lc', 'cell' ) ) ) {
			$type = 'city';
		}

		if ( $converted ) {
			return $this->convert_type( $type, $short );
		} else {
			return $type;
		}
	}

	/**
	 * Returns the meta-type of a region by ID
	 * (i.e. 'city' instead of 'lc' or 'cell')
	 *
	 * @param int $id			the geographical ID
	 * @return string $type
	 *
	 * @since 1.3
	 * @access public
	 */
	public function get_meta_type( $id )
	{
		$type = $this->get_type( $id, false );

		if ( $this->is_city( $id ) ) {
			return 'city';
		} else {
			return $type;
		}
	}

	/**
	 * Returns an array of regions with id as key and human readable type as value
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_types()
	{
		$raw = $this->get_all();
		$regions = array();

		foreach( $raw as $region ) {
			$regions[$region['id']] = $region['type'];
		}

		return $regions;
	}

	/**
	 * Returns an array of regions with id as key and human readable type as value
	 *
	 * @return string[] $regions
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_region_id_to_type()
	{
		$raw = $this->get_all();
		$regions = array();

		foreach( $raw as $region ) {
			$regions[$region['id']] = $this->convert_type( $region['type'] );
		}

		return $regions;
	}

	/* ============================= TEMPLATE TAGS / BOOLEAN TESTS ============================= */

	/**
	 * Checks whether the geographical unit is a city
	 *
	 * @param integer $id		// geographical ID
	 * @return bool				// whether a city
	 *
	 * @since 1.3
	 * @access public
	 */
	public function is_city( $id )
	{
		$type = $this->get_type( $id, false );
		if ( in_array( $type, array( 'city', 'lc', 'cell' ) ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Checks whether the geographical unit is a city group
	 *
	 * @param integer $id		// geographical ID
	 * @return bool				// whether a city group
	 *
	 * @since 1.3
	 * @access public
	 */
	public function is_city_group( $id )
	{
		$type = $this->get_type( $id, false );
		if ( 'cg' === $type ) {
			return true;
		}
		return false;
	}

	/**
	 * Checks whether the geographical unit is a nation
	 *
	 * @param integer $id		// geographical ID
	 * @return bool				// whether a nation
	 *
	 * @since 1.3
	 * @access public
	 */
	public function is_nation( $id )
	{
		$type = $this->get_type( $id, false );
		if ( 'nation' === $type ) {
			return true;
		}
		return false;
	}

	/**
	 * Checks whether the geographical unit is a nation group
	 *
	 * @param integer $id		// geographical ID
	 * @return bool				// whether a nation group
	 *
	 * @since 1.3
	 * @access public
	 */
	public function is_nation_group( $id )
	{
		$type = $this->get_type( $id, false );
		if ( 'ng' === $type ) {
			return true;
		}
		return false;
	}

	/**
	 * Returns false if the unit has no parent nation and the parent nation's ID if so
	 *
	 * @param int $id				the ID of the city or city group in question
	 * @return bool|int $nation		the ID of the parent nation (or fals if not found)
	 *
	 * @since 1.3
	 * @access public
	 */
	public function has_nation( $id )
	{
		if ( is_numeric( $id ) ) {
			$nation = $this->get_ancestors( $id , array(
				'data' => 'id',
				'type' => 'nation'
			));
		}
		if ( empty( $nation ) ) {
			return false;
		}
		return intval( $nation );
	}

	/**
	 * Whether the unit question is part of a city group
	 *
	 * @param int $id		(geographical) ID
	 * @return bool			whether the unit is part of a city group
	 *
	 * @since 1.3
	 * @access public
	 */
	public function has_cg( $id )
	{
		$cgs = $this->get_ancestors( $id , array(
			'data' => 'id',
			'type' => 'cg',
			'format' => 'array'
		));
		if ( empty( $cgs ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Whether the unit question is part of a nation group
	 *
	 * @param int $id		(geographical) ID
	 * @return bool			whether the unit is part of a nation group
	 *
	 * @since 1.3
	 * @access public
	 */
	public function has_ng( $id )
	{
		$ngs = $this->get_ancestors( $id , array(
			'data' => 'id',
			'type' => 'ng',
			'format' => 'array',
			'deep' => true
		));
		if ( empty( $ngs ) ) {
			return false;
		}
		return true;
	}

	/* ============================= OPTIONS FOR HTML SELECT TAGS ============================= */

	/**
	 * Returns a nested array of values and labels for dropdowns (HTML select tags)
	 *
	 * @param array $args				(optional) arguments, see code
	 * @return array $options_array		data for select population
	 *
	 * @global object $vca_asm_utilities
	 *
	 * @see template VCA_ASM_Admin_Form
	 *
	 * @since 1.0
	 * @access public
	 */
	public function options_array( $args = array() )
	{
		global $vca_asm_utilities;

		$default_args = array(
			'global_option' => '',					// (string) name of global select option, none if empty
			'global_option_last' => '',				// (string) name of global select option at the end of the dropdown
			'orderby' => 'name',					// (string) what to order the returned list by
			'order' => 'ASC',						// (string) what direction to sort in
			'please_select' => false,				// (bool) whether the first option is a non-value
			'please_select_value' => 'please_select',	// (string|int) the value of the non-value option
			'please_select_text' => __( 'Please select...', 'vca-asm' ),	// the readable label of the non-value option
			'type' => 'all',						// (string) type of geographical unit, i.e 'city', default: all
			'descendants_of' => false,				// (bool|int) only list the descendents of parent ID
			'not_has_nation' => false,				// (bool) only list cities without nation (utility used once)
			'grouped' => true						// whether type should be 'city' or split into 'cell' & 'lc', if applicable
		);
		extract( wp_parse_args( $args, $default_args ), EXTR_SKIP );

		$all = $this->get_all( $orderby, $order, $type );
		if( is_numeric( $descendants_of ) ) {
			$desc = $this->get_descendants( intval( $descendants_of ), array( 'data' => 'all', 'grouped' => $grouped ) );
			$raw = $desc;
		} else {
			$raw = $all;
		}
		if( $not_has_nation ) {
			$no_nations = array();
			foreach ( $all as $geo_unit ) {
				$nation = $this->has_nation( $geo_unit['id'] );
				if ( ! $nation ) {
					$no_nations[] = $geo_unit;
				}
			}
			if ( is_numeric( $descendants_of ) ) {
				$raw = array_merge( $raw, $no_nations );
			} else {
				$raw = $no_nations;
			}
		}

		$options_array = array();
		if( true === $please_select ) {
			$options_array[0] = array(
				'label' => $please_select_text,
				'value' => $please_select_value,
				'class' => 'please-select'
			);
		}

		if( ! empty( $global_option ) ) {
			$options_array[] = array(
				'label' => $global_option,
				'value' => 0,
				'class' => 'global'
			);
		}

		foreach( $raw as $geo_unit ) {
			$options_array[] = array(
				'label' => $vca_asm_utilities->convert_strings( $geo_unit['name'] ),
				'value' => $geo_unit['id'],
				'class' => $geo_unit['type']
			);
		}

		$first = array();
		if ( true === $please_select ) {
			$first[] = array_shift( $options_array );
		}
		if ( ! empty( $global_option ) ) {
			$first[] = array_shift( $options_array );
		}

		$options_array = $vca_asm_utilities->sort_by_key( $options_array, 'label' );
		if ( ! empty( $first ) ) {
			$options_array = array_merge( $first, $options_array );
		}

		if( ! empty( $global_option_last ) ) {
			$options_array[] = array(
				'label' => $global_option_last,
				'value' => 0,
				'class' => 'global'
			);
		}

		return $options_array;
	}

} // class

endif; // class exists

?>