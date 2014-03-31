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
 */

if ( ! class_exists( 'VCA_ASM_Geography' ) ) :

class VCA_ASM_Geography
{

	/**
	 * Class Properties
	 *
	 * @since 1.3
	 */
	public $national_hierarchy = array();
	public $countries = array();
	public $cities = array();

	/**
	 * Returns the name of a geographical unit if fed its ID
	 *
	 * @param int $id
	 * @return string $region
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
		$geo = isset( $geo_query[0] ) ? $geo_query[0]['name'] : '';
		if( empty( $geo ) ) {
			$geo = sprintf( __( 'Error: Geographical unit of ID %s does not exist.', 'vca-asm' ), $id );
		}

		$geo = $vca_asm_utilities->convert_strings( $geo );

		return $geo;
	}

	/**
	 * Returns the currency name of a geographical unit if fed its ID
	 *
	 * @param int $id
	 * @param string $type
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
	/* Wrappers for the above */
	public function get_currency_name( $id ) {
		return $this->get_currency( $id, 'name' );
	}
	public function get_currency_code( $id ) {
		return $this->get_currency( $id, 'code' );
	}

	/**
	 * Returns the 2-letter ISO 3166-1 code of a country or
	 * a somewhat more arbitraty code of any other geographical unit if fed its ID
	 *
	 * @param int $id
	 * @return string $alpha
	 *
	 * @since 1.3
	 * @access public
	 */
	public function get_alpha_code( $id ) {
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
	 * Returns the phone extension geographical unit if fed its ID
	 *
	 * @param int $id
	 * @return string $ext
	 *
	 * @since 1.3
	 * @access public
	 */
	public function get_phone_extension( $id ) {
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
	 * @param string $order
	 * @param string $orderby
	 * @param string $type
	 *
	 * @return array $regions
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_all( $orderby = 'name', $order = 'ASC', $type = 'all' ) {
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
	 * Returns an array of raw region data
	 *
	 * @param string $without
	 * @return array $cities
	 *
	 * @since 1.3
	 * @access public
	 */
	public function get_cities_without( $without = 'nation' ) {
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
	 * Returns an array of raw region data
	 *
	 * @param string $without
	 * @return array $nations
	 *
	 * @since 1.3
	 * @access public
	 */
	public function get_nations_without( $without = 'ng' ) {
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
	 * Returns a region's ancestors (if any)
	 *
	 * @param int $id
	 * @param array $args
	 * @return mixed $ancestors
	 *
	 * @since 1.3
	 * @access public
	 */
	public function get_ancestors( $id, $args = array() ) {
		global $wpdb, $vca_asm_utilities;

		$default_args = array(
			'data' => 'name',
			'format' => 'string',
			'concat' => ', ',
			'deep' => false,
			'type' => 'all'
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
	 * Returns a city's first found city group
	 * (group to city relationships are not unique,
	 * a city may be part of several groups)
	 *
	 * @param int $city
	 * @return int $group
	 *
	 * @since 1.4
	 * @access public
	 */
	public function get_city_group( $city ) {
		$result = $this->get_ancestors( $city, array(
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
	 * Returns false if the unit has no parent nation
	 * parent nation's id if so
	 *
	 * @since 1.3
	 * @access public
	 */
	public function has_nation( $id ) {
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
	 * Returns a boolean signaling whether
	 * the unit question is part of a city group
	 *
	 * @since 1.3
	 * @access public
	 */
	public function has_cg( $id ) {
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
	 * Returns a boolean signaling whether
	 * the unit question is part of a country group
	 *
	 * @since 1.3
	 * @access public
	 */
	public function has_ng( $id ) {
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

	/**
	 * Returns a regions descendants (if any)
	 *
	 * @param array $args
	 * @see $default_args
	 * @return mixed $descendants
	 *
	 * @since 1.3
	 * @access public
	 */
	public function get_descendants( $id, $args = array() ) {
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
			sort( $descendants_arr );
		} elseif ( true === $sorted && 'all' === $data ) {
			array_multisort( $names, SORT_ASC, $descendants_arr );
		}

		if ( 'string' === $format ) {
			$descendants = implode( $concat, $descendants_arr );
		} else {
			$descendants = $descendants_arr;
		}

		return $descendants;
	}

	/**
	 * Converts type string to proper name
	 *
	 * @param string $type
	 * @return string $name
	 *
	 * @since 1.0
	 * @access public
	 */
	public function convert_type( $type ) {
		switch( $type ) {
			case 'cell':
				return __( 'Cell', 'vca-asm' );
			break;
			case 'lc':
				return __( 'Local Crew', 'vca-asm' );
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
	 * Returns an array of regions with id as key and name as value
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_ids( $type = 'all' ) {

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
	 * @since 1.0
	 * @access public
	 */
	public function get_status( $id ) {
		return $this->get_type( $id );
	}
	public function get_type( $id, $converted = true, $grouped = false ) {
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
			return $this->convert_type( $type );
		} else {
			return $type;
		}
	}

	/**
	 * Returns the meta-type of a region by ID
	 *
	 * @since 1.3
	 * @access public
	 */
	public function get_meta_type( $id ) {
		$type = $this->get_type( $id, false );

		if ( $this->is_city( $id ) ) {
			return 'city';
		} else {
			return $type;
		}
	}

	/**
	 * Checks whether the geographical unit is a city
	 *
	 * @param integer $id
	 * @return boolean
	 *
	 * @since 1.3
	 * @access public
	 */
	public function is_city( $id ) {
		$type = $this->get_type( $id, false );
		if ( in_array( $type, array( 'city', 'lc', 'cell' ) ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Checks whether the geographical unit is a region
	 *
	 * @param integer $id
	 * @return boolean
	 *
	 * @since 1.3
	 * @access public
	 */
	public function is_city_group( $id ) {
		$type = $this->get_type( $id, false );
		if ( 'cg' === $type ) {
			return true;
		}
		return false;
	}

	/**
	 * Checks whether the geographical unit is a nation
	 *
	 * @param integer $id
	 * @return boolean
	 *
	 * @since 1.3
	 * @access public
	 */
	public function is_nation( $id ) {
		$type = $this->get_type( $id, false );
		if ( 'nation' === $type ) {
			return true;
		}
		return false;
	}

	/**
	 * Checks whether the geographical unit is a nation group
	 *
	 * @param integer $id
	 * @return boolean
	 *
	 * @since 1.3
	 * @access public
	 */
	public function is_nation_group( $id ) {
		$type = $this->get_type( $id, false );
		if ( 'ng' === $type ) {
			return true;
		}
		return false;
	}

	/**
	 * Returns an array of regions with id as key and human readable type as value
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_stati() {
		return $this->get_types();
	}
	public function get_types() {

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
	 * @since 1.0
	 * @access public
	 */
	public function get_stati_conv() {
		return $this->get_regions_by_type();
	}
	public function get_regions_by_type() {

		$raw = $this->get_all();
		$regions = array();

		foreach( $raw as $region ) {
			$regions[$region['id']] = $this->convert_type( $region['type'] );
		}

		return $regions;
	}

	/**
	 * Updates the supporter and member count in the regions table
	 *
	 * @todo mem_count in single SQL query
	 *
	 * @since 1.0
	 * @access public
	 */
	public function update_member_count() {
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

	/**
	 * Deletes a geographical unit
	 * and sanitizes dependent database entries
	 *
	 * @param int $id
	 * @return bool $success
	 *
	 * @since 1.3
	 * @access public
	 */
	public function delete( $id ) {
		global $wpdb;

		$geo_user_query = $wpdb->get_results(
			"SELECT has_user, user_id, pass, user FROM " .
			$wpdb->prefix . "vca_asm_geography " .
			"WHERE id = " . $_GET['id'] . " LIMIT 1", ARRAY_A
		);
		$geo_user = isset( $geo_user_query[0] ) ? $geo_user_query[0] : '';

		$wpdb->query(
			"DELETE FROM " .
			$wpdb->prefix . "vca_asm_geography " .
			"WHERE id = " . $_GET['id'] . " LIMIT 1"
		);
		if ( is_array( $geo_user ) && 1 == $geo_user['has_user'] ) {
			wp_delete_user( $geo_user['user_id'] );
		}
		$wpdb->query(
			"DELETE FROM " .
			$wpdb->prefix . "vca_asm_geography_hierarchy " .
			"WHERE descendant = " . $_GET['id'] . " OR ancestor = " . $_GET['id']
		);

		return true;
	}
	/**
	 * Returns an array of region data to be used in a dropdown menu
	 *
	 * @todo deprecate this and replace by @method options_array
	 *
	 * @since 1.0
	 * @access public
	 */
	public function select_options( $global_option = '', $orderby = 'name', $order = 'ASC', $please_select = false ) {

		$raw = $this->get_all( $orderby, $order );
		$regions = array();
		if( $please_select === true ) {
			$regions[0] = array(
				'label' => __( 'Please select...', 'vca-asm' ),
				'value' => 'please_select', // js alert if selected on save, @see frontend-profile template
				'class' => 'please-select'
			);
		}

		if( ! empty( $global_option ) ) {
			$regions[] = array(
				'label' => $global_option,
				'value' => 0,
				'class' => 'global'
			);
		}

		foreach( $raw as $region ) {
			$regions[] = array(
				'label' => $region['name'],
				'value' => $region['id'],
				'class' => $region['type']
			);
		}

		return $regions;
	}

	/**
	 * Returns an array of region data
	 * to be used in either a dropdown menu or a checkbox group
	 *
	 * @since 1.0 (modified for 1.3)
	 * @access public
	 */
	public function options_array( $args ) {
		global $vca_asm_utilities;

		$default_args = array(
			'global_option' => '',
			'global_option_last' => '',
			'orderby' => 'name',
			'order' => 'ASC',
			'please_select' => false,
			'please_select_value' => 'please_select',
			'please_select_text' => __( 'Please select...', 'vca-asm' ),
			'type' => 'all',
			'descendants_of' => false,
			'not_has_nation' => false,
			'grouped' => true
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

	/**
	 * PHP5 style constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
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

} // class

endif; // class exists

?>