<?php

/**
 * VCA_ASM_Frontend_Activities class
 *
 * This class contains properties and methods
 * to display activities in the frontend.
 *
 * @package VcA Activity & Supporter Management
 * @since 1.3
 */

if ( ! class_exists( 'VCA_ASM_Frontend_Activities' ) ) :

class VCA_ASM_Frontend_Activities {

	/**
	 * Class Properties
	 *
	 * @since 1.3
	 */
	private $default_args = array(
		'echo' => false,
		'with_filter' => false,
		'with_sorting' => false,
		'eligibility_check' => false,
		'action' => false,
		'container_class' => '',
		'heading' => '',
		'heading_class' => '',
		'pre_text' => '',
		'minimalistic' => false,
		'fullrow' => true
	);
	private $args = array();
	private $activities = array();

	private $cities = array();
	private $months = array();
	private $nations = array();
	private $nations_nicenames = array();
	private $sort_terms = array();
	private $order = 'ASC';
	private $toggle_order = 'DESC';
	private $types = array();
	private $types_nicenames = array();

	/**
	 * Constructor
	 *
	 * @since 1.3
	 * @access public
	 */
	public function __construct( $activities, $args = array() ) {
		$this->activities = $activities;
		$this->args = wp_parse_args( $args, $this->default_args );
		$this->sort_terms['date'] = __( 'Date', 'vca-asm' );
	}

	/**
	 * Sorting Methods
	 *
	 * @since 1.3
	 * @access public
	 */
	public function sort_posts_array_asc_nation_nicename( $a, $b ) {
		return $a->nation_nicename == $b->nation_nicename ? 0 : ( $a->nation_nicename > $b->nation_nicename ) ? 1 : -1;
	}
	public function sort_posts_array_desc_nation_nicename( $a, $b ) {
		return $a->nation_nicename == $b->nation_nicename ? 0 : ( $a->nation_nicename < $b->nation_nicename ) ? 1 : -1;
	}
	public function sort_posts_array_asc_type_nicename( $a, $b ) {
		return $a->type_nicename == $b->type_nicename ? 0 : ( $a->type_nicename > $b->type_nicename ) ? 1 : -1;
	}
	public function sort_posts_array_desc_type_nicename( $a, $b ) {
		return $a->type_nicename == $b->type_nicename ? 0 : ( $a->type_nicename < $b->type_nicename ) ? 1 : -1;
	}

	/**
	 * Constructs output HTML,
	 * echoes or returns it
	 *
	 * @since 1.3
	 * @access public
	 */
	public function output() {
		global $current_user,
			$vca_asm_activities, $vca_asm_geography, $vca_asm_utilities;

		extract( $this->args );

		if( empty( $this->activities ) ) {

			return;

		} elseif ( ! $minimalistic ) {

			$user_city = get_user_meta( $current_user->ID, 'city', true );
			$user_cg = ! empty( $user_city ) ? $vca_asm_geography->get_city_group( $user_city ) : 0;
			$user_city_has_activity = false;
			$user_cg_has_activity = false;
			$user_cg_month = array();
			$user_mem_status = get_user_meta( $current_user->ID, 'membership', true );
			$user_lang = get_user_meta( $current_user->ID, 'pool_lang', true );

			$i = 0;
			while ( $this->activities->have_posts() ) : $this->activities->the_post();

				$cur_month = date( 'n', intval( get_post_meta( get_the_ID(), 'start_act', true ) ) );
				if( ! in_array( $cur_month, $this->months ) ) {
					$this->months[] = $cur_month;
				}

				$cur_city = get_post_meta( get_the_ID(), 'city', true );

				if( ! empty( $cur_city ) && ! in_array( $cur_city, $this->cities ) ) {
					if ( false === $user_city_has_activity && ! empty( $user_city ) && $cur_city === $user_city ) {
						$user_city_has_activity = true;
					}
					if ( false === $user_cg_has_activity && ! empty( $user_cg ) && $user_cg === $vca_asm_geography->get_city_group( $cur_city ) ) {
						$user_cg_has_activity = true;
						if ( ! in_array( $cur_month, $user_cg_month ) ) {
							$user_cg_month[] = $cur_month;
						}
					}
					$this->cities[] = $cur_city;
				}
				if ( is_numeric( $cur_city ) && ! empty( $cur_city ) ) {
					$cur_nat = $vca_asm_geography->has_nation( $cur_city );
				} else {
					$cur_nat = get_post_meta( get_the_ID(), 'nation', true );
				}

				if ( ! empty( $cur_nat ) ) {
					$cur_nat_nicename = $vca_asm_geography->get_name( $cur_nat );
					if ( ! in_array( $cur_nat, $this->nations ) ) {
						$this->nations[] = $cur_nat;
						$this->nations_nicenames[$cur_nat] = $cur_nat_nicename;
					}
					$this->activities->posts[$i]->nation_nicename = $cur_nat_nicename;
				} else {
					$this->activities->posts[$i]->nation_nicename = 'ZZZZZ';
				}

				$cur_type = get_post_type();
				$cur_type_nicename = $vca_asm_activities->activities_to_plural_nicename[$cur_type];
				if( ! in_array( $cur_type, $this->types ) ) {
					$this->types[] = $cur_type;
					$this->types_nicenames[$cur_type] = $cur_type_nicename;
				}
				$this->activities->posts[$i]->type_nicename = ! empty( $cur_type_nicename ) ? $cur_type_nicename : 'ZZZZZ';

				$i++;
			endwhile;
			wp_reset_postdata();

			$mnth_qs = '';
			$geo_qs = '';
			$default_switch = true;
			if ( $with_filter ) {
				if ( isset( $_GET['selection'] ) && 'goldeimer' === $_GET['selection'] ) {
					$mnth_filter = 0;
					$mnth_qs = '&mnth=' . $mnth_filter;
				} elseif ( isset( $_GET['mnth'] ) && is_numeric( $_GET['mnth'] ) ) {
					$mnth_filter = $_GET['mnth'];
					$mnth_qs = '&mnth=' . $mnth_filter;
					$default_switch = false;
				} else {
					$mnth_filter = date( 'n' );
					$mnth_qs = '&mnth=' . $mnth_filter;
				}
				if ( isset( $_GET['selection'] ) && 'goldeimer' === $_GET['selection'] ) {
					$geo_filter = 'all';
					$geo_qs = '&ctr=' . 0;
				} elseif ( isset( $_GET['ctr'] ) && ( is_numeric( $_GET['ctr'] ) ) ) {
					$default_switch = false;
					$geo_filter = $_GET['ctr'];
					$geo_qs = '&ctr=' . $geo_filter;
				} else {
					/* if ( $user_city_has_activity ) {
						$geo_filter = 'own-cty';
						$geo_qs = '&ctr=' . $geo_filter;
					} else */ if ( $user_cg_has_activity ) {
						$geo_filter = 'own-cg';
						$geo_qs = '&ctr=' . $geo_filter;
					} else {
						if ( ! empty( $_SERVER['HTTP_HOST'] ) ) {
							$domain = $_SERVER['HTTP_HOST'];
						} elseif ( ! isset( $domain ) && ! empty( $_SERVER['SERVER_NAME'] ) ) {
							$domain = $_SERVER['SERVER_NAME'];
						}

						if ( isset( $domain ) && 'pool.vivaconagua.ch' === $domain ) {
							$geo_filter = 42;
						} else {
							$geo_filter = 40;
						}
						$geo_qs = '&ctr=' . $geo_filter;
					}
				}
				if ( isset( $_GET['selection'] ) && 'goldeimer' === $_GET['selection'] ) {
					$type_filter = 'goldeimerfestival';
					$type_qs = '&type=' . $type_filter;
				} elseif ( isset( $_GET['type'] ) ) {
					$type_filter = $_GET['type'];
					$type_qs = '&type=' . $type_filter;
					$default_switch = false;
				} else {
					$type_filter = 'all';
					$type_qs = '&type=' . $type_filter;
				}
				if ( isset( $_GET['sort'] ) ) {
					$sort_by = $_GET['sort'];
					$sort_qs = '&sort=' . $sort_by;
					$default_switch = false;
				} else {
					$sort_by = 'date';
					$sort_qs = '&sort=' . $sort_by;
				}
				if( isset( $_GET['dir'] ) ) {
					$order = $_GET['dir'];
					if ( 'DESC' === $order ) {
						$toggle_order = 'ASC';
					} else {
						$toggle_order = 'DESC';
						if ( $order !== 'ASC' ) {
							$order = 'ASC';
						}
					}
				} else {
					$order = 'ASC';
					$toggle_order = 'DESC';
				}
			}

			if ( count( $this->months ) > 1 ) {
				sort( $this->months );
			}
			if ( count( $this->nations ) > 1 ) {
				$this->sort_terms['ctr'] = __( 'Country', 'vca-asm' );
			}
			if ( count( $this->types ) > 1 ) {
				$this->sort_terms['type'] = __( 'Type', 'vca-asm' );
			}

			if (
				( ! isset( $_GET['selection'] ) || 'goldeimer' !== $_GET['selection'] ) &&
				( ! isset( $mnth_filter ) || ! in_array( $mnth_filter, $this->months ) )
			) {
				$mnth_filter = ! empty( $this->months ) ? $this->months[0] : 0;
				$mnth_qs = '&mnth=' . $mnth_filter;
			}

			if ( ! isset( $geo_filter ) || ( ! in_array( $geo_filter, $this->nations ) && ! in_array( $geo_filter, array( 'own-cty', 'own-cg' ) ) ) ) {
				$geo_filter = 0;
				$geo_qs = '&ctr=' . $geo_filter;
			}

			if ( ! isset( $type_filter ) || ! in_array( $type_filter, $this->types ) ) {
				$type_filter = 'all';
				$type_qs = '&type=' . $type_filter;
			}

			if ( ! isset( $sort_by ) || ! array_key_exists( $sort_by, $this->sort_terms ) ) {
				$sort_by = 'date';
				$sort_qs = '&sort=' . $sort_by;
			}

			if ( $sort_by === 'type' ) {
				if ( $order === 'DESC' ) {
					usort( $this->activities->posts, array( $this, 'sort_posts_array_desc_type_nicename' ) );
				} else {
					usort( $this->activities->posts, array( $this, 'sort_posts_array_asc_type_nicename' ) );
				}
			} else if ( $sort_by === 'ctr' ) {
				if ( $order === 'DESC' ) {
					usort( $this->activities->posts, array( $this, 'sort_posts_array_desc_nation_nicename' ) );
				} else {
					usort( $this->activities->posts, array( $this, 'sort_posts_array_asc_nation_nicename' ) );
				}
			}

			$headput = '';

			if ( ! empty( $heading ) ) {
				if ( $fullrow ) {
					$headput .= '<div class="grid-row break-heading"><div class="grid-block col12">';
				}
				$headput .= '<h2';
				if ( ! empty( $heading_class ) || ! $fullrow ) {
					$headput .= ' class="';
					if ( ! $fullrow ) {
						$headput .= 'inline-break-heading';
						if ( ! empty( $heading_class ) ) {
							$headput .= ' ';
						}
					}
					if ( ! empty( $heading_class ) ) {
						$headput .= $heading_class;
					}
					$headput .= '"';
				}
				$headput .= '>' . $heading . '</h2>';
				if ( $fullrow ) {
					$headput .= '</div></div>';
				}
			}

			if ( ! empty( $pre_text ) ) {
				$headput .= '<div class="grid-row"><div class="col12"><p class="message">' .
					$pre_text .
					'</p></div></div>';
			}

			$output = '';

			/* list & loop through posts (activities) */
			$output = '<div class="grid-row activities-row"><div class="col12 activities-container-wrap';
			if ( ! empty( $container_class ) ) {
				$output .= ' ' . $container_class;
			}
			$output .= '"><div class="activities-container toggle-list-wrapper">';

			while ( $this->activities->have_posts() ) : $this->activities->the_post();

				$the_activity = new VCA_ASM_Activity( get_the_ID() );
				$eligible_quota = $the_activity->is_eligible( $current_user->ID );

				if ( true === $eligibility_check && ! is_numeric( $eligible_quota ) ) {
					continue;
				}

				$cur_month = date( 'n', intval( get_post_meta( get_the_ID(), 'start_act', true ) ) );

				$cur_city = get_post_meta( get_the_ID(), 'city', true );
				if ( is_numeric( $cur_city ) && ! empty( $cur_city ) ) {
					$cur_nat = $vca_asm_geography->has_nation( $cur_city );
					$cur_cg = $vca_asm_geography->get_city_group( $cur_city );
				} else {
					$cur_cg = 0;
					$cur_nat = get_post_meta( get_the_ID(), 'nation', true );
				}

				$cur_type = get_post_type();

				if (
					true === $default_switch
					||
					(
						( ! isset( $mnth_filter ) || 0 == $mnth_filter || $mnth_filter == $cur_month ) &&
						( ! isset( $geo_filter ) || 0 == $geo_filter || $geo_filter == $cur_nat || ( 'own-cty' === $geo_filter && $cur_city === $user_city ) || ( 'own-cg' === $geo_filter && $cur_cg === $user_cg ) ) &&
						( ! isset( $type_filter ) || 'all' === $type_filter || $type_filter == $cur_type )
					)
				) {

					if ( 'en' === $user_lang ) {
						$start_act_string = strftime( '%A, %e/%m/%Y, %H:%M', $the_activity->start_act );
						$end_act_string = strftime( '%A, %e/%m/%Y, %H:%M', $the_activity->end_act );
						$start_app_string = strftime( '%A, %e/%m/%Y', $the_activity->start_app );
						$end_app_string = strftime( '%A, %e/%m/%Y', $the_activity->end_app );
					} else {
						$start_act_string = strftime( '%A, %e.%m.%Y, %H:%M', $the_activity->start_act );
						$end_act_string = strftime( '%A, %e.%m.%Y, %H:%M', $the_activity->end_act );
						$start_app_string = strftime( '%A, %e.%m.%Y', $the_activity->start_app );
						$end_app_string = strftime( '%A, %e.%m.%Y', $the_activity->end_app );
					}

					$type_addition = ! empty( $the_activity->nation_name ) ? ' (' . $the_activity->nation_name . ')' : '';

					$output .= '<div class="activity activity-' . $the_activity->type . ' month-' . $cur_month . ' ctr-' . $cur_nat . ' type-' . $cur_type;
					$output .= $cur_cg === $user_cg ? ' own-cg' : '';
					$output .= $cur_city === $user_city ? ' own-cty' : '';

					if (
						true === $default_switch
						&&
						(
							(
								isset( $mnth_filter ) && is_numeric( $mnth_filter ) && 0 < $mnth_filter && $mnth_filter != $cur_month
							) || (
								isset( $geo_filter ) &&
								(
									( is_numeric( $geo_filter ) && 0 < $geo_filter && $geo_filter != $cur_nat )
									||
									( 'own-cty' === $geo_filter && $cur_city != $user_city )
								)
							) || (
								isset( $type_filter ) && 'all' !== $type_filter && $type_filter != $cur_type
							)
						)
					) {
						$output .= ' js-toggle';
					}

					$output .= '"><div class="activity-island toggle-wrapper">' .

						'<span class="date hidden">' . $the_activity->start_act . '</span>' .
						'<span class="ctr hidden">' . str_replace( ' ', '_', $the_activity->nation_name ) . '</span>' .
						'<span class="cty hidden">' . str_replace( ' ', '_', $the_activity->city_name ) . '</span>' .
						'<span class="type hidden">' . str_replace( ' ', '_', $the_activity->nice_type ) . '</span>' .

						'<img class="activity-icon" alt="' . $the_activity->nice_type . '" src="' . $the_activity->icon_url . '" />' .

						'<h4><a title="' . __( 'Single view', 'vca-asm' ) . '" href="' . get_permalink() . '">' . get_the_title() . '</a></h4>' .
						'<p class="type">' . $the_activity->nice_type . $type_addition . '</p>' .

						'<table class="meta-table">' .
							'<tr>' .
								'<td><p class="label">' . __( 'Timeframe', 'vca-asm' ) . '</p>' .
								'<p class="metadata">';

								if ( strftime( '%e.%m.%Y', $the_activity->start_act ) === strftime( '%e.%m.%Y', $the_activity->end_act ) ) {
									$output .= $start_act_string .
										' ' . __( 'until', 'vca-asm' ) . ' ' .
										strftime( '%H:%M', $the_activity->end_act );
								} else {
									$output .= $start_act_string .
										' ' . __( 'until', 'vca-asm' ) . ' ' .
										$end_act_string;
								}

					$output .= '</p></td>' .
							'</tr>' .
							'<tr>' .
								'<td><p class="label">' . __( 'Location', 'vca-asm' ) . '</p>' .
								'<p class="metadata">' .
									get_post_meta( get_the_ID(), 'location', true ) .
								'</p></td>' .
							'</tr>' .
						'</table>';

					$output .= '<div class="toggle-element"><div class="measuring-wrapper">' .
						'<table class="meta-table">' .
							'<tr>' .
								'<td><p class="label">' . __( 'Application Deadline', 'vca-asm' ) . '</p>' .
								'<p class="metadata">' .
									$end_app_string .
								'</p></td>' .
							'</tr>' .
							'<tr>' .
								'<td><p class="label">' . _x( 'available Slots', 'i.e. max. participants', 'vca-asm' ) . '</p>' .
								'<p class="metadata">' .
									get_post_meta( get_the_ID(), 'total_slots', true ) .
								'</p></td>' .
							'</tr>' .
						'</table>';

					$subput = '';

					$tools_enc = get_post_meta( get_the_ID(), 'tools', true );
					$special_desc =  get_post_meta( get_the_ID(), 'special', true );

					if( ! empty( $tools_enc ) ) {
						$tools = array();
						if( in_array( '1', $tools_enc ) ) {
							$tools[] = _x( 'Cups', 'VcA Tools', 'vca-asm' );
						}
						if( in_array( '2', $tools_enc ) ) {
							$tools[] = _x( 'Guest List', 'VcA Tools', 'vca-asm' );
						}
						if( in_array( '3', $tools_enc ) ) {
							$tools[] = _x( 'Info Counter', 'VcA Tools', 'vca-asm' );
						}
						if( in_array( '4', $tools_enc ) ) {
							$tools[] = _x( 'Water Bottles', 'VcA Tools', 'vca-asm' );
						}
						if( in_array( '5', $tools_enc ) ) {
							if( isset( $special_desc ) && ! empty( $special_desc ) ) {
								$tools[] = $special_desc;
							} else {
								$tools[] = _x( 'Special', 'VcA Tools', 'vca-asm' );
							}
						}
						$tools = implode( ', ', $tools );

						$subput .= '<tr>' .
								'<td><p class="label">' . __( 'VcA Activities', 'vca-asm' ) . '</p>' .
								'<p class="metadata">' .
									$tools .
								'</p></td>' .
							'</tr>';
					}

					$site = get_post_meta( get_the_ID(), 'website', true );
					if ( ! empty( $site ) ) {
						$subput .= '<tr>' .
								'<td><p class="label">' . __( 'Website', 'vca-asm' ) . '</p>' .
								'<p class="metadata">' .
									$vca_asm_utilities->urls_to_links( $site ) .
								'</p></td>' .
							'</tr>';
					}

					$directions = get_post_meta( get_the_ID(), 'directions', true );
					if ( ! empty( $directions ) ) {
						$subput .= '<tr>' .
								'<td><p class="label">' . __( 'Directions', 'vca-asm' ) . '</p>' .
								'<p class="metadata">' .
									preg_replace( '#(<br */?>\s*){2,}#i', '<br><br>' , preg_replace( '/[\r|\n]/', '<br>' , $vca_asm_utilities->urls_to_links( $directions ) ) ) .
								'</p></td>' .
							'</tr>';
					}

					$notes = get_post_meta( get_the_ID(), 'notes', true );
					if ( ! empty( $notes ) ) {
						$subput .= '<tr>' .
								'<td><p class="label">' . __( 'additional Notes', 'vca-asm' ) . '</p>' .
								'<p class="metadata">' .
									preg_replace( '#(<br */?>\s*){2,}#i', '<br><br>' , preg_replace( '/[\r|\n]/', '<br>' , $vca_asm_utilities->urls_to_links( $notes ) ) ) .
								'</p></td>' .
							'</tr>';
					}

					if ( ! empty( $subput ) ) {
						$output .= '<h5>' . __( 'Further Info', 'vca-asm' ) . '</h5>' .
							'<table class="meta-table">' . $subput . '</table>';
						$subput = '';
					}

					if( ! empty( $action ) && 'app' === $action ) {

						$output .= '<h5>' . __( 'Participate', 'vca-asm' ) . '</h5>' .
							'<form method="post" action="">' .
							'<input type="hidden" name="unique_id" value="[' . md5( uniqid() ) . ']">' .
							'<input type="hidden" name="todo" id="todo" value="apply" />' .
							'<input type="hidden" name="activity" id="activity" value="' . get_the_ID() . '" />' .
							'<div class="form-row">' .
								'<textarea name="notes" id="notes" rows="5"></textarea>' .
								'<br class="no-js-toggle" /><span class="description no-js-toggle">' .
									_x( 'If you wish to send a message with your application, do so here.', 'Frontend: Application Process', 'vca-asm' ) .
								'</span>' .
							'</div><div class="form-row">' .
								'<input type="submit" id="submit_form" name="submit_form" value="';
								if ( 'goldeimerfestival' === $the_activity->type ) {
									$output .= __( 'Apply', 'vca-asm' );
								} else {
									$output .= __( 'Apply', 'vca-asm' );
								}
								$output .= '" />' .
							'</div></form>';
					}

					if( ! empty( $action ) && 'rev_app' === $action ) {

						$output .= '<form method="post" action="">' .
							'<input type="hidden" name="todo" id="todo" value="revoke_app" />' .
							'<input type="hidden" name="activity" id="activity" value="' . get_the_ID() . '" />' .
							'<div class="form-row">' .
								'<input type="submit" id="submit_form" name="submit_form" value="' . __( 'Revoke Application', 'vca-asm' ) . '" />' .
							'</div></form>';

					}

					$output .= '</div></div><div class="toggle-arrows-wrap no-js-hide">' .
						'<a class="toggle-link toggle-arrows toggle-arrows-more" title="' . __( 'Toggle additional info', 'vca-asm' ) . '" ' . 'href="#">' .
							'<img alt="' . __( 'More/Less', 'vca-asm' ) . '"src="' .
								get_bloginfo( 'template_url' ) . '/images/arrows.png" />' .
						'</a></div>' .
						'<div class="more-link-wrap no-js-toggle"><a href="' . get_permalink() . '" title="' . __( 'Participate!', 'vca-asm' ) . '">&rarr; ' . __( 'Further Info &amp; Participation', 'vca-asm' ) . '</a></div>';

					$output .= '</div></div>';
				}

			endwhile;
			wp_reset_postdata();

			$output .= '</div></div></div>';

			$output .= '<div class="grid-row no-results-row" style="display:none"><div class="col12"><div class="island system-error">' .
					'<p>' . __( 'No activities for the selected filter criteria...', 'vca-asm' ) . '</p>' .
				'</div></div></div>';

			$selector = '';

			if ( $with_filter ) {
				if ( count( $this->months ) > 1 || count( $this->nations ) > 1 || count( $this->types ) > 1 || in_array( $user_city, $this->cities ) ) {
					$filters_count = 0;
					$selector .= '<div class="filter-container">' .
						'<div class="grid-row filter-row">' .
							'<div class="filter-wrap">' .
								'<div class="filter-inner options-table talign-center">';

					$anchors = '<div class="anchors-wrap">';

					$dropdowns = '<form id="activity-selector" action="" method="get">' .
						'<div class="grid-block ';
					$dropdowns .= true === $with_sorting ? 'col6' : 'col12';
					$dropdowns .= '">' .
							'<h3 class="talign-center">' . _x( 'Filter', 'Frontend Box', 'vca-asm' ) . '</h3>';

					$position_class = $filters_count % 2 === 0 ? '' : ' last';
					if ( count( $this->months ) > 1 ) {
						$filters_count++;
						$dropdown = '<select id="month-filter-dd" name="mnth">';
						$anchors .= '<div class="grid-block col6' . $position_class . '"><p class="label js-hide">' . __( 'Filter by months', 'vca-asm' ) . '</p>' .
							'<p';
						if ( true === $default_switch ) {
							$anchors .=  ' id="month-filter"';
						}
						$dropdown .= '<option ';
						$anchors .= ' class="metadata js-hide"><a ';
						if ( ! isset( $mnth_filter ) || 0 == $mnth_filter || ( $user_cg_has_activity && ! in_array( reset($this->months), $user_cg_month ) ) ) {
							$anchors .= 'class="active-option" ';
							$dropdown .= 'class="active-option" selected="selected" ';
						}
						$dropdown .= 'value="0" data-filter="*">' . __( 'All months', 'vca-asm' ) . '</option>';
						$anchors .= 'title="' . __( 'All months', 'vca-asm' ) . '" href="' . get_bloginfo( 'url' ) . '?mnth=0' . $geo_qs . $type_qs . $sort_qs . '" data-filter="*">' . __( 'All', 'vca-asm' ) . '</a> | ';
						$i = 0;
						foreach ( $this->months as $month ) {
							$dropdown .= '<option ';
							$anchors .= '<a ';
							if ( isset( $mnth_filter ) && $month == $mnth_filter && ( ! $user_cg_has_activity || in_array( $month, $user_cg_month ) ) ) {
								$anchors .= 'class="active-option" ';
								$dropdown .= 'class="active-option" selected="elected" ';
							}
							$dropdown .= 'value="' . $month . '" data-filter=".month-' . $month . '">' . strftime( '%B', mktime( 0, 0, 0, $month, 13 ) ) . '</option>';
							$anchors .= 'title="' . __( 'Filter by month', 'vca-asm' ) . '" href="' . get_bloginfo( 'url' ) . '?mnth=' . $month . $geo_qs . $type_qs . $sort_qs . '" data-filter=".month-' . $month . '">' . strftime( '%B', mktime( 0, 0, 0, $month, 13 ) ) . '</a>';
							if ( $i + 1 < count( $this->months ) ) {
								$anchors .= ' | ';
							}
							$i++;
						}
						$dropdown .= '</select>';
						$anchors .= '</p></div>';
						$dropdowns .= $dropdown;
					}

					$position_class = $filters_count % 2 === 0 ? '' : ' last';
					if ( count( $this->nations ) > 1 || /* $user_city_has_activity || */ $user_cg_has_activity ) {
						asort( $this->nations_nicenames );
						$filters_count++;
						$dropdown = '<select id="ctr-filter-dd" name="ctr">';
						$anchors .= '<div class="grid-block col6' . $position_class . '"><p class="label js-hide">' . __( 'Filter by countries', 'vca-asm' ) . '</p>' .
							'<p';
						if ( true === $default_switch ) {
							$anchors .=  ' id="ctr-filter"';
						}
						$dropdown .= '<option ';
						$anchors .= ' class="metadata js-hide"><a ';
						if ( 0 == $geo_filter ) {
							$anchors .= 'class="active-option" ';
							$dropdown .= 'class="active-option" selected="selected" ';
						}
						$dropdown .= 'value="0" data-filter="*">' . __( 'All countries', 'vca-asm' ) . '</option>';
						$anchors .= 'title="' . __( 'All countries&apos; activities', 'vca-asm' ) . '" href="' . get_bloginfo( 'url' ) . '?ctr=0' . $mnth_qs . $type_qs . $sort_qs . '" data-filter="*">' . __( 'All', 'vca-asm' ) . '</a> | ';
						$i = 0;
						foreach ( $this->nations_nicenames as $nation => $nation_nicename ) {
							$dropdown .= '<option ';
							$anchors .= '<a ';
							if ( $nation == $geo_filter ) {
								$anchors .= 'class="active-option" ';
								$dropdown .= 'class="active-option" selected="selected" ';
							}
							$dropdown .= 'value="' . $nation . '" data-filter=".ctr-' . $nation . '">' . $nation_nicename . '</option>';
							$anchors .= 'title="' . __( 'Filter by geography', 'vca-asm' ) . '" href="' . get_bloginfo( 'url' ) . '?ctr=' . $nation . $mnth_qs . $type_qs . $sort_qs . '" data-filter=".ctr-' . $nation . '">' . $nation_nicename . '</a>';
							if ( $i + 1 < count( $this->nations ) ) {
								$anchors .= ' | ';
							}
							$i++;
						}
						if ( $user_cg_has_activity ) {
							if ( count( $this->nations_nicenames ) > 0 ) {
								$anchors .= ' | ';
							}
							$dropdown .= '<option ';
							$anchors .= '<a ';
							if ( 'own-cg' === $geo_filter ) {
								$anchors .= 'class="active-option" ';
								$dropdown .= 'class="active-option" selected="selected" ';
							}
							$dropdown .= 'value="own-cg" data-filter=".own-cg">' . __( 'My Region', 'vca-asm' ) . '</option>';
							$anchors .= 'title="' . __( 'Filter by own city', 'vca-asm' ) . '" href="' . get_bloginfo( 'url' ) . '?ctr=own-cg' . $mnth_qs . $type_qs . $sort_qs . '" data-filter=".own-cg">' . __( 'My Region', 'vca-asm' ) . '</a>';
						}
						if ( $user_city_has_activity ) {
							if ( count( $this->nations_nicenames ) > 0 || $user_cg_has_activity ) {
								$anchors .= ' | ';
							}
							$dropdown .= '<option ';
							$anchors .= '<a ';
							if ( 'own-cty' === $geo_filter ) {
								$anchors .= 'class="active-option" ';
								$dropdown .= 'class="active-option" selected="selected" ';
							}
							$dropdown .= 'value="own-cty" data-filter=".own-cty">' . __( 'My city', 'vca-asm' ) . '</option>';
							$anchors .= 'title="' . __( 'Filter by own city', 'vca-asm' ) . '" href="' . get_bloginfo( 'url' ) . '?ctr=own-cty' . $mnth_qs . $type_qs . $sort_qs . '" data-filter=".own-cty">' . __( 'My city', 'vca-asm' ) . '</a>';
						}
						$dropdown .= '</select>';
						$anchors .= '</p></div>';
						$dropdowns .= $dropdown;
					}

					$position_class = $filters_count % 2 === 0 ? '' : ' last';
					if ( count( $this->types ) > 1 ) {
						asort( $this->types_nicenames );
						$filters_count++;
						$dropdown = '<select id="type-filter-dd" name="type">';
						$anchors .= '<div class="grid-block col6' . $position_class . '"><p class="label js-hide">' . __( 'Filter by type', 'vca-asm' ) . '</p>' .
							'<p';
						if ( true === $default_switch ) {
							$anchors .=  ' id="type-filter"';
						}
						$dropdown .= '<option ';
						$anchors .= ' class="metadata js-hide"><a ';
						if ( 'all' == $type_filter ) {
							$anchors .= 'class="active-option" ';
							$dropdown .= 'class="active-option" selected="selected" ';
						}
						$dropdown .= 'value="all" data-filter="*">' . __( 'All types of activity', 'vca-asm' ) . '</option>';
						$anchors .= 'title="' . __( 'All types of activity', 'vca-asm' ) . '" href="' . get_bloginfo( 'url' ) . '?type=all' . $mnth_qs . $geo_qs . $sort_qs . '" data-filter="*">' . __( 'All', 'vca-asm' ) . '</a> | ';
						$i = 0;
						foreach ( $this->types_nicenames as $type => $type_nicename ) {
							$dropdown .= '<option ';
							$anchors .= '<a ';
							if ( $type == $type_filter ) {
								$anchors .= 'class="active-option" ';
								$dropdown .= 'class="active-option" selected="selected" ';
							}
							$dropdown .= 'value="' . $type . '" data-filter=".type-' . $type . '">' . $type_nicename . '</option>';
							$anchors .= 'title="' . __( 'Filter by type', 'vca-asm' ) . '" href="' . get_bloginfo( 'url' ) . '?type=' . $type . $mnth_qs . $geo_qs . $sort_qs . '" data-filter=".type-' . $type . '">' . $type_nicename . '</a>';
							if ( $i + 1 < count( $this->types ) ) {
								$anchors .= ' | ';
							}
							$i++;
						}
						$dropdown .= '</select>';
						$anchors .= '</p></div>';
						$dropdowns .= $dropdown;
					}

					$position_class = $filters_count % 2 === 0 ? '' : ' last';
					if ( true === $with_sorting && count( $this->sort_terms ) > 1 ) {
						$dropdowns .= '</div><div class="grid-block col6 last">' .
							'<h3 class="talign-center">' . __( 'Sort', 'vca-asm' ) . '</h3>';
						asort( $this->sort_terms );
						$filters_count++;
						$dropdown = '<select id="sort-by-selector-dd" name="sort">';
						$anchors .= '<div class="grid-block col6' . $position_class . '"><p class="label js-hide">' . __( 'Sort by', 'vca-asm' ) . '<span class="csscolumns-notice"> (' . __( 'Left column first', 'vca-asm' ) . ')</span></p>' .
							'<p';
						if ( true === $default_switch ) {
							$anchors .=  ' id="sort-by-selector"';
						}
						$dropdown .= '<option ';
						$anchors .= ' class="metadata js-hide"><a ';
						$i = 0;
						foreach ( $this->sort_terms as $term => $nice_term ) {
							$dropdown .= '<option ';
							$anchors .= '<a ';
							$term_param = $term;
							$order_param = $order;
							if ( $term === $sort_by ) {
								$anchors .= 'class="active-option" ';
								$term_param .= '&dir=' . $toggle_order;
								$order_param = $toggle_order;
								$dropdown .= 'class="active-option" selected="selected" ';
							}
							$dropdown .= 'value="' . $term . '" data-sort="' . $term . '">' . $nice_term . '</option>';
							$anchors .= 'title="' . __( 'Sort by this', 'vca-asm' ) . '" href="' . get_bloginfo( 'url' ) . '?sort=' . $term_param . $mnth_qs . $geo_qs . $type_qs . '" data-sort="' . $term . '" data-order="' . $order_param . '">' . $nice_term . '</a>';
							if ( $i + 1 < count( $this->sort_terms ) ) {
								$anchors .= ' | ';
							}
							$i++;
						}
						$dropdown .= '</select>';
						$anchors .= '</p></div>';
						$dropdowns .= $dropdown;
						$dropdowns .= '<select id="sort-order-selector-dd" name="dir">' .
							'<option';
							if ( 'ASC' === $order ) {
								$dropdowns .= ' selected="selected"';
							}
							$dropdowns .= ' value="ASC" data-order="ASC">' . __( 'Ascending', 'vca-asm' ) . '</option>' .
							'<option';
							if ( 'DESC' === $order ) {
								$dropdowns .= ' selected="selected"';
							}
						$dropdowns .= ' value="DESC" data-order="DESC">' . __( 'Descending', 'vca-asm' ) . '</option>' .
						'</select>';
					}

					if ( $filters_count %2 === ( 2 - 1 ) ) {
						$anchors .= '<div class="grid-block col6 last"></div>';
					}

					$anchors .= '</div>';

					$dropdowns .= '</div>' .
							'<input class="js-hide" type="submit" value="' . __( 'Apply to selection', 'vca-asm' ) . '" />' .
						'</form>';

					$selector .= '<div class="toggle-wrapper toggle-mobile-sticky">' .
							'<div class="toggle-element">' .
								'<div class="measuring-wrapper">' .
									$anchors . $dropdowns .
								'</div>' .
							'</div>' .
							'<div class="toggle-arrows-wrap no-js-hide">' .
								'<a class="toggle-link toggle-arrows toggle-arrows-more" title="' . __( 'Toggle additional info', 'vca-asm' ) . '" ' . 'href="#">' .
									'<img alt="' . __( 'More/Less', 'vca-asm' ) . '"src="' .
										get_bloginfo( 'template_url' ) . '/images/arrows-blue.png" />' .
								'</a>' .
							'</div>' .
						'</div>';

					$selector .= '</div>' .
							'</div>' .
							'<div class="filter-bottom"></div>' .
						'</div>' .
					'</div>';
				}
			}

			$output = $headput . $selector . $output;

		} else { // minimalistic === true

			$output = '';

			if ( ! empty( $heading ) ) {
				if ( $fullrow ) {
					$output .= '<div class="grid-row break-heading"><div class="col12">';
				}
				$output .= '<h2';
				if ( ! empty( $heading_class ) || ! $fullrow ) {
					$output .= ' class="';
					if ( ! $fullrow ) {
						$output .= 'inline-break-heading';
						if ( ! empty( $heading_class ) ) {
							$output .= ' ';
						}
					}
					if ( ! empty( $heading_class ) ) {
						$output .= $heading_class;
					}
					$output .= '"';

				}
				$output .= '>' . $heading . '</h2>';
				if ( $fullrow ) {
					$output .= '</div></div>';
				}
			}

			if ( ! empty( $pre_text ) ) {
				$output .= '<p class="message">' .
					$pre_text .
					'</p>';
			}

			$output .=  '<ul class="' . $container_class . '">';

			while ( $this->activities->have_posts() ) : $this->activities->the_post();

				$the_activity = new VCA_ASM_Activity( get_the_ID() );

				$output .= '<li>' .
						'<div class="activity activity-' . $the_activity->type . ' minimalistic-activity activity-full-width"><div class="activity-island">' .

							'<img class="activity-icon" alt="' . $the_activity->nice_type . '" src="' . $the_activity->icon_url . '" />' .

							'<h4><a title="' . __( 'Give me more information!', 'vca-asm' ) . '" href="' . get_permalink() . '">' . get_the_title() . '</a></h4>' .
							'<p class="type">' . $the_activity->nice_type . '</p>' .
							'<p class="no-margin">' .
								strftime( '%B %Y', intval( get_post_meta( get_the_ID(), 'start_act', true ) ) ) .
							'</p>' .

						'</div></div>' .
					'</li>';

			endwhile;

			$output .= '</ul>';

		}

		if ( true === $echo ) {
			echo $output;
		}
		return $output;
	}

} // class

endif; // class exists

?>