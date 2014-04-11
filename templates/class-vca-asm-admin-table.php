<?php

/**
 * VCA_ASM_Admin_Table class.
 *
 * This class contains properties and methods
 * to display tabular data in the administrative backend
 *
 * @package VcA Activity & Supporter Management
 * @since 1.3
 */

if ( ! class_exists( 'VCA_ASM_Admin_Table' ) ) :

class VCA_ASM_Admin_Table {

	/**
	 * Class Properties
	 *
	 * @since 1.3
	 */
	public $default_args = array(
		'echo' => true,
		'orderby' => 'name',
		'order' => 'ASC',
		'toggle_order' => 'DESC',
		'page_slug' => 'vca-asm-supporters',
		'base_url' => '',
		'sort_url' => '',
		'profile_url' => '',
		'pagination_url' => '',
		'with_wrap' => false,
		'icon' => 'icon-supporter',
		'headline' => 'Data Table',
		'data_name' => NULL,
		'headspace' => false,
		'messages' => array(),
		'show_empty_message' => true,
		'empty_message' => '',
		'pagination' => false,
		'prev_text' => '&laquo; Previous',
		'next_text' => 'Next &raquo;',
		'total_pages' => 1,
		'current_page' => 1,
		'end_size' => 1,
		'mid_size' => 2,
		'dspl_cnt' => false,
		'count' => 0,
		'cnt_txt' => '',
		'with_bulk' => false,
		'bulk_btn' => 'Execute',
		'bulk_confirm' => '',
		'bulk_name' => 'bulk',
		'bulk_param' => 'todo',
		'bulk_desc' => '',
		'extra_bulk_html' => '',
		'bulk_actions' => array()
	);
	private $set_args = array();
	public $args = array();
	public $columns = array();
	public $rows = array();

	/* state */
	public $has_data = false;

	/**
	 * Constructor
	 *
	 * @since 1.3
	 * @access public
	 */
	public function __construct( $args, $columns, $rows ) {

		$this->default_args['prev_text'] = __( '&laquo; Previous', 'vca-asm' );
		$this->default_args['next_text'] = __( 'Next &raquo;', 'vca-asm' );
		$this->default_args['bulk_btn'] = __( 'Execute', 'vca-asm' );

		$this->set_args = $args;
		$this->args = wp_parse_args( $args, $this->default_args );

		if ( isset( $_GET['orderby'] ) ) {
			$this->args['orderby'] = $_GET['orderby'];
		}
		if ( isset( $_GET['order'] ) && ( 'ASC' === $_GET['order'] || 'DESC' === $_GET['order'] ) ) {
			$order = $_GET['order'];
			if( 'ASC' === $order ) {
				$toggle_order = 'DESC';
			} else {
				$toggle_order = 'ASC';
			}
			$this->args['order'] = $order;
			$this->args['toggle_order'] = $toggle_order;
		}

		$this->args['base_url'] = ! empty( $this->args['base_url'] ) ? $this->args['base_url'] : 'admin.php?page='.$this->args['page_slug'];
		$this->args['sort_url'] = ! empty( $this->args['sort_url'] ) ? $this->args['sort_url'] : $this->args['base_url'];
		$this->args['profile_url'] = ! empty( $this->args['profile_url'] ) ? $this->args['profile_url'] :
			$this->args['sort_url'] . '&orderby=' . $this->args['orderby'] . '&order=' . $this->args['order'];
		$this->args['pagination_url'] = ! empty( $this->args['pagination_url'] ) ? $this->args['pagination_url'] :
			str_replace( '{', '%lcurl%',
				str_replace( '}', '%rcurl%',
					str_replace( ':', '%colon%',
						$this->args['sort_url']
					)
				)
			) .
			'&orderby=' . $this->args['orderby'] . '&order=' . $this->args['order'] . '%_%';

		$this->columns = $columns;
		$this->rows = $rows;
	}

	/**
	 * Constructs the table HTML,
	 * echoes or returns it
	 *
	 * @since 1.3
	 * @access public
	 */
	public function output() {
		global $current_user, $vca_asm_geography, $vca_asm_admin;

		extract( $this->args, EXTR_SKIP );
		$columns = $this->columns;
		$rows = $this->rows;

		$output = '';

		if ( $with_wrap ) {
			$output .= '<div class="wrap">' .
				'<div id="' . $icon . '" class="icon32-pa"><br></div>' .
				'<h2>' . $headline . '</h2><br />';

			if( ! empty( $messages ) ) {
				$output .= $vca_asm_admin->convert_messages( $messages );
			}
		}

		if ( $headspace ) {
			$output .= '<br />';
		}

		if (
			! $with_wrap &&
			isset( $this->set_args['headline'] ) &&
			! empty( $this->set_args['headline'] ) &&
			(
				$with_bulk ||
				( ! $with_bulk && ! $dspl_cnt && ! $pagination )
			)
		) {
			$output .= '<h3 class="table-headline">';
			if ( isset( $this->set_args['icon'] ) && ! empty( $this->set_args['icon'] ) ) {
				$output .= '<div class="tbl-icon nt-' . $icon . '"></div>';
			}
			$output .= $headline . '</h3>';
		}

		if ( empty( $rows ) ) {

			if ( $show_empty_message ) {
				if ( ! empty( $empty_message ) ) {
					$message = $empty_message;
				} elseif ( ! empty( $data_name ) ) {
					$message = sprintf( __( 'Currently there is no data of type &quot;%s&quot;...', 'vca-asm' ), $data_name );
				} else {
					$message = __( 'Currently there is no data to be displayed here...', 'vca-asm' );
				}

				$output .= $vca_asm_admin->convert_messages( array ( array (
					'type' => 'message',
					'message' => $message
				) ) );
			}

		} else {

			$this->has_data = true;

			/* BEGIN: Table Header */

			$table_head = '<tr>';

			if ( $with_bulk && is_array( $bulk_actions ) && ! empty( $bulk_actions ) ) {
				$table_head .= '<th id="bulk" class="manage-column column-bulk check-column" style="" scope="col">'.
						'<input type="checkbox">' .
					'</th>';
			}

			foreach( $columns as $column ) {

				$table_head .= '<th id="' . $column['id'] . '" class="manage-column column-' . $column['id'];

				/* classes to be used in css media queries */
				if( isset( $column['legacy-screen'] ) && false === $column['legacy-screen'] ) {
					$table_head .= ' legacy-screen-hide-pa';
				} elseif( isset( $column['tablet'] ) && false === $column['tablet'] ) {
					$table_head .= ' tablet-hide-pa';
				} elseif( isset( $column['mobile'] ) && false === $column['mobile'] ) {
					$table_head .= ' mobile-hide-pa';
				} elseif( isset( $column['legacy-mobile'] ) && false === $column['legacy-mobile'] ) {
					$table_head .= ' legacy-mobile-hide-pa';
				}

				/* is the table sortable via the data in this column? */
				if( isset( $column['sortable'] ) && true === $column['sortable'] ) {
					/* default initial sorting */
					if( $column['id'] !== $orderby ) {
						$col_order = 'DESC';
						$col_toggle_order = 'ASC';
					/* sorting if this is the column currently sorted by */
					} else {
						$col_order = $order;
						$col_toggle_order = $toggle_order;
					}
					$table_head .= ' sortable ' . strtolower( $col_order );
				}

				$table_head .= '" style="" scope="col">';

				if( isset( $column['sortable'] ) && true === $column['sortable'] ) {
					$table_head .= '<a href="' .
							get_option( 'site_url' ) . '/wp-admin/' .
							$sort_url . '&amp;orderby=' . $column['id'] . '&amp;order=' . $col_toggle_order .
						'">';
				}

				$table_head .= '<span>' . $column['title'] . '</span>';

				if( isset( $column['sortable'] ) && true === $column['sortable'] ) {
					$table_head .= '<span class="sorting-indicator"></span></a>';
				}

				$table_head .= '</th>';
			}

			$table_head .= '</tr>';

			/* END: Table Header | BEGIN: Output */

			if( $with_bulk && is_array( $bulk_actions ) && ! empty( $bulk_actions ) ) {
				$output .= '<form action="" class="blk-action-form" method="get">' .
					'<input type="hidden" name="page" value="' . $page_slug . '" />';
				if ( ! empty( $extra_bulk_html ) ) {
					$output .= $extra_bulk_html;
				}
			}

			if(
				( $with_bulk && is_array( $bulk_actions ) && ! empty( $bulk_actions ) ) ||
				$dspl_cnt ||
				$pagination
			) {
				$output .= '<div class="tablenav top">';
			}

			if( $with_bulk && is_array( $bulk_actions ) && ! empty( $bulk_actions ) ) {
				$output .= '<div class="alignleft actions">';
				if ( ! empty( $bulk_desc ) ) {
					$output .= '<span class="desc">' . $bulk_desc . ':&nbsp;</span>';
				}
				if ( 1 < count( $bulk_actions ) ) {
					$output .= '<select name="' . $bulk_param . '" id="' . $bulk_param . '" class="bulk-action simul-select">';
					foreach ( $bulk_actions as $bulk_action ) {
						$output .= '<option value="' . $bulk_action['value'] . '">' . $bulk_action['label'] . '&nbsp;</option>';
					}
					$output .= '</select>';
				} else {
					$bulk_action = $bulk_actions[0];
					$output .= '<input type="hidden" name="' . $bulk_param . '" value="' . $bulk_action['value'] . '" />';
				}
				$output .= '<input type="submit" name="" id="bulk-action-submit" class="button-secondary do-bulk-action" value="' .
						$bulk_btn . '"';
				if ( ! empty( $bulk_confirm ) ) {
					$output .= ' onclick="if ( confirm(\'' . $bulk_confirm . '\') ) { return true; } return false;"';
				}
				$output .= '/></div>';
			} elseif (
				! $with_wrap &&
				isset( $this->set_args['headline'] ) &&
				! empty( $this->set_args['headline'] ) &&
				( $dspl_cnt || $pagination )
			) {
				$output .= '<div class="alignleft"><h3 class="table-headline">';
				if ( isset( $this->set_args['icon'] ) && ! empty( $this->set_args['icon'] ) ) {
					$output .= '<div class="tbl-icon nt-' . $icon . '"></div>';
				}
				$output .= $headline . '</h3></div>';
			}

			if ( $dspl_cnt || $pagination ) {
				$output .= '<div class="tablenav-pages">';
				if ( $dspl_cnt ) {
					$output .= '<span class="displaying-num">' . sprintf( $cnt_txt, $count ) . '</span>';
				}
				if( $pagination ) {
					$pagination_html = paginate_links( array(
						'base' => $pagination_url,
						'format' => '&p=%#%#tbl',
						'prev_text' => $prev_text,
						'next_text' => $next_text,
						'total' => $total_pages,
						'current' => $current_page,
						'end_size' => $end_size,
						'mid_size' => $mid_size,
					));
					$pagination_html = str_replace( '%colon%', ':', str_replace( '%lcurl%', '{', str_replace( '%rcurl%', '}', $pagination_html ) ) );
					$output .= '<span class="pagination-links">' . $pagination_html . '</span>';
				}
				$output .= '</div>';
			}

			if(
				( $with_bulk && is_array( $bulk_actions ) && ! empty( $bulk_actions ) ) ||
				$dspl_cnt ||
				$pagination
			) {
				$output .= '</div>';
			}


			$output .= '<table class="wp-list widefat fixed" cellspacing="0">' .
				'<thead>'. $table_head . '</thead>'.
				'<tfoot>'. $table_head . '</tfoot>'.
				'<tbody>';

			/* BEGIN: Rows */

			foreach ( $rows as $row ) {
				$row_id = ! empty( $row['id'] ) ? ' id="row-' . $row['id'] . '"' : '';

				$output .= '<tr valign="middle" class="alternate"' . $row_id . '>';
				if ( $with_bulk && is_array( $bulk_actions ) && ! empty( $bulk_actions ) ) {
					$bulk_val = isset( $row['bulk'] ) ? $row['bulk'] : ( isset( $row['id'] ) ? $row['id'] : 0 );
					$output .= '<th class="check-column" scope="row">' .
							'<input type="checkbox" name="' . $bulk_name . '[]" value="' . $bulk_val . '">' .
						'</th>';
				}

				foreach ( $columns as $column ) {
					$capable = false;
					if (
						empty( $column['cap'] ) ||
						(
							'city' === $column['cap'] &&
							(
								$current_user->has_cap( 'vca_asm_manage_network_global' ) ||
								(
									$current_user->has_cap( 'vca_asm_manage_network_nation' ) &&
									$vca_asm_geography->has_nation( get_user_meta( $current_user->ID, 'city', true ) ) &&
									$vca_asm_geography->has_nation( get_user_meta( $current_user->ID, 'city', true ) ) === $vca_asm_geography->has_nation( $row['id'] )
								)
							)
						) ||
						(
							'cg' === $column['cap'] &&
							(
								$current_user->has_cap( 'vca_asm_manage_network_global' ) ||
								(
									$current_user->has_cap( 'vca_asm_manage_network_nation' ) &&
									$vca_asm_geography->has_nation( get_user_meta( $current_user->ID, 'city', true ) )
								)
							)
						) ||
						(
							'nation' === $column['cap'] &&
							(
								$current_user->has_cap( 'vca_asm_manage_network_global' ) ||
								(
									$current_user->has_cap( 'vca_asm_manage_network_nation' ) &&
									$vca_asm_geography->has_nation( get_user_meta( $current_user->ID, 'city', true ) ) &&
									$vca_asm_geography->has_nation( get_user_meta( $current_user->ID, 'city', true ) ) == $row['id']
								)
							)
						) ||
						(
							'ng' === $column['cap'] &&
							$current_user->has_cap( 'vca_asm_manage_network_global' )
						) ||
						(
							'slots-actions' === $column['cap'] &&
							(
								$current_user->has_cap( 'vca_asm_manage_actions_global' )
							) ||
							(
								$current_user->has_cap( 'vca_asm_manage_actions_nation' )
							) ||
							(
								$current_user->has_cap( 'vca_asm_manage_actions' )
							)
						) ||
						(
							'slots-education' === $column['cap'] &&
							(
								$current_user->has_cap( 'vca_asm_manage_education_global' )
							) ||
							(
								$current_user->has_cap( 'vca_asm_manage_education_nation' )
							) ||
							(
								$current_user->has_cap( 'vca_asm_manage_education' )
							)
						) ||
						(
							'slots-network' === $column['cap'] &&
							(
								$current_user->has_cap( 'vca_asm_manage_network_global' )
							) ||
							(
								$current_user->has_cap( 'vca_asm_manage_network_nation' )
							) ||
							(
								$current_user->has_cap( 'vca_asm_manage_network' )
							)
						)
					) {
						$capable = true;
					}

					$output .= '<td class="column-' . $column['id'];
					if( isset( $column['legacy-screen'] ) && false === $column['legacy-screen'] ) {
						$output .= ' legacy-screen-hide-pa';
					} elseif( isset( $column['tablet'] ) && false === $column['tablet'] ) {
						$output .= ' tablet-hide-pa';
					} elseif( isset( $column['mobile'] ) && false === $column['mobile'] ) {
						$output .= ' mobile-hide-pa';
					} elseif( isset( $column['legacy-mobile'] ) && false === $column['legacy-mobile'] ) {
						$output .= ' legacy-mobile-hide-pa';
					}
					$output .= '">';

					if( isset( $column['strong'] ) && true === $column['strong'] ) {
						$output .= '<strong>';
					}

					if( ! empty( $column['link'] ) && true /*$capable*/ ) {
						$title = empty( $column['link']['title_row_data'] ) ? $column['link']['title'] : sprintf( $column['link']['title'], $row[$column['link']['title_row_data']] );
						$url = empty( $column['link']['url_row_data'] ) ? $column['link']['url'] : sprintf( $column['link']['url'], $row[$column['link']['url_row_data']] );
						$output .= '<a title="' . $title . '" href="' . $url . '">';
					}

					if( ! isset( $column['conversion'] ) ) {
						$output .= $row[$column['id']];
					} else {
						$output .= $this->convert_data( $row[$column['id']], $column['conversion'], $row, $column['id'] );
					}

					if( ! empty( $column['link'] ) && $capable ) {
						$output .= '</a>';
					}

					if( isset( $column['strong'] ) && true === $column['strong'] ) {
						$output .= '</strong>';
					}

					if ( ! empty( $column['actions'] ) ) {
						$cap = ! empty( $column['cap'] ) ? $column['cap'] : '';
						$output .= $this->actions( $column['actions'], $row, $column, $cap );
					}

					$output .= '</td>';
				}

				$output .= '</tr>';
			}

			/* END: Rows */

			$output .= '</tbody></table>';

			if(
				( $with_bulk && is_array( $bulk_actions ) && ! empty( $bulk_actions ) ) ||
				$dspl_cnt ||
				$pagination
			) {
				$output .= '<div class="tablenav bottom">';
			}

			if( $with_bulk && is_array( $bulk_actions ) && ! empty( $bulk_actions ) ) {
				$output .= '<div class="alignleft actions no-js-hide">';
				if ( ! empty( $bulk_desc ) ) {
					$output .= '<span class="desc">' . $bulk_desc . ':&nbsp;</span>';
				}
				if ( 1 < count( $bulk_actions ) ) {
					$output .= '<select name="' . $bulk_param . '" id="' . $bulk_param . '" class="bulk-action simul-select">';
					foreach ( $bulk_actions as $bulk_action ) {
						$output .= '<option value="' . $bulk_action['value'] . '">' . $bulk_action['label'] . '&nbsp;</option>';
					}
					$output .= '</select>';
				} else {
					$bulk_action = $bulk_actions[0];
					$output .= '<input type="hidden" name="' . $bulk_param . '" value="' . $bulk_action['value'] . '" />';
				}
				$output .= '<input type="submit" name="" id="bulk-action-submit" class="button-secondary do-bulk-action" value="' .
						$bulk_btn . '"';
				if ( ! empty( $bulk_confirm ) ) {
					$output .= ' onclick="if ( confirm(\'' . $bulk_confirm . '\') ) { return true; } return false;"';
				}
				$output .= '/></div>';
			}

			if ( $dspl_cnt || $pagination ) {
				$output .= '<div class="tablenav-pages">';
				if ( $dspl_cnt ) {
					$output .= '<span class="displaying-num">' . sprintf( $cnt_txt, $count ) . '</span>';
				}
				if( $pagination ) {
					$output .= '<span class="pagination-links">' . $pagination_html . '</span>';
				}
				$output .= '</div>';
			}

			if(
				( $with_bulk && is_array( $bulk_actions ) && ! empty( $bulk_actions ) ) ||
				$dspl_cnt ||
				$pagination
			) {
				$output .= '</div>';
			}

			if( $with_bulk && is_array( $bulk_actions ) && ! empty( $bulk_actions ) ) {
				$output .= '</form>';
			}
		}

		if ( $with_wrap ) {
			$output .= '</div>';
		}

		if ( ! $echo ) {
			return $output;
		} else {
			echo $output;
		}
	}

	/**
	 * Is invoked if a column has action links associated with it
	 *
	 * @todo make more dynamic
	 *
	 * @since 1.3
	 * @access private
	 */
	private function actions( $actions, $row, $column, $cap = '' ) {
		global $current_user, $vca_asm_geography, $vca_asm_roles;
		get_currentuserinfo();

		$admin_city = get_user_meta( $current_user->ID, 'city', true );
		$admin_nation = $vca_asm_geography->has_nation( $admin_city );

		$output = '<br /><div class="row-actions">';

		if( ! empty( $row['first_name'] ) ) {
			$name = $row['first_name'];
		} elseif( ! empty( $row['username'] ) ) {
			$name = __( 'username', 'vca-asm' ) . ' &quot;' . $row['username'] . '&quot;';
		} elseif( ! empty( $row['name'] ) ) {
			$name = $row['name'];
		} else {
			$name = __( 'this supporter', 'vca-asm' );
		}

		if( ! empty( $row['name'] ) ) {
			$activity_title = $row['name'];
		} else {
			$activity_title = __( 'this activity', 'vca-asm' );
		}

		if( ! empty( $row['type'] ) ) {
			$activity_type = $row['type'];
		} else {
			$activity_type = __( 'this activity', 'vca-asm' );
		}

		$url = $this->args['base_url'];

		$action_count = count( $actions );
		$flipper = true;
		for ( $i = 0; $i < $action_count; $i++ ) {

			$cur_cap = '';
			if ( ! empty( $cap ) ) {
				if ( is_array( $cap ) ) {
					$cap_cnt = count( $cap );
					if ( ( $cap_cnt - 1 ) >= $i ) {
						$cur_cap = $cap[$i];
					} else {
						$cur_cap = $cap[$cap_cnt-1];
					}
				} else {
					$cur_cap = $cap;
				}
			}

			if (
				empty( $cur_cap ) ||
				(
					'city' === $cur_cap &&
					(
						$current_user->has_cap( 'vca_asm_manage_network_global' ) ||
						(
							$current_user->has_cap( 'vca_asm_manage_network_nation' ) &&
							$admin_nation &&
							$admin_nation === $vca_asm_geography->has_nation( $row['id'] )
						)
					)
				) ||
				(
					'cg' === $cur_cap &&
					$current_user->has_cap( 'vca_asm_manage_network_global' )
				) ||
				(
					'nation' === $cur_cap &&
					(
						$current_user->has_cap( 'vca_asm_manage_network_global' ) ||
						(
							$current_user->has_cap( 'vca_asm_manage_network_nation' ) &&
							$admin_nation &&
							$admin_nation == $row['id']
						)
					)
				) ||
				(
					'ng' === $cur_cap &&
					$current_user->has_cap( 'vca_asm_manage_network_global' )
				) ||
				(
					'promote' === $cur_cap &&
					(
						$current_user->has_cap( 'vca_asm_promote_supporters_global' ) ||
						(
							$current_user->has_cap( 'vca_asm_promote_supporters_nation' ) &&
							$admin_nation &&
							$vca_asm_geography->has_nation( get_user_meta( $row['id'], 'city', true ) ) &&
							$admin_nation == $vca_asm_geography->has_nation( get_user_meta( $row['id'], 'city', true ) )
						) ||
						(
							$current_user->has_cap( 'vca_asm_promote_supporters' ) &&
							$admin_city &&
							get_user_meta( $row['id'], 'city', true ) &&
							$admin_city == get_user_meta( $row['id'], 'city', true )
						)
					)
				) ||
				(
					'delete-user' === $cur_cap &&
					(
						$current_user->has_cap( 'vca_asm_delete_supporters_global' ) ||
						(
							$current_user->has_cap( 'vca_asm_delete_supporters_nation' ) &&
							$admin_nation &&
							$vca_asm_geography->has_nation( get_user_meta( $row['id'], 'city', true ) ) &&
							$admin_nation == $vca_asm_geography->has_nation( get_user_meta( $row['id'], 'city', true ) )
						) ||
						(
							$current_user->has_cap( 'vca_asm_delete_supporters' ) &&
							$admin_city &&
							get_user_meta( $row['id'], 'city', true ) &&
							$admin_city == get_user_meta( $row['id'], 'city', true )
						)
					)
				) ||
				(
					'role' === $cur_cap &&
					in_array( $current_user->roles[0], $vca_asm_roles->admin_roles ) &&
					in_array( $row['role_slug'], $vca_asm_roles->user_sub_roles() ) &&
					(
						in_array( $current_user->roles[0], $vca_asm_roles->global_admin_roles ) ||
						(
							$admin_nation &&
							$vca_asm_geography->has_nation( get_user_meta( $row['id'], 'city', true ) ) &&
							$admin_nation == $vca_asm_geography->has_nation( get_user_meta( $row['id'], 'city', true ) )
						)
					)
				) ||
				(
					'profile' === $cur_cap &&
					(
						$current_user->has_cap( 'vca_asm_view_supporters_global' ) ||
						(
							$current_user->has_cap( 'vca_asm_view_supporters_nation' ) &&
							$admin_nation &&
							$vca_asm_geography->has_nation( get_user_meta( $row['id'], 'city', true ) ) &&
							$admin_nation == $vca_asm_geography->has_nation( get_user_meta( $row['id'], 'city', true ) )
						) ||
						(
							$current_user->has_cap( 'vca_asm_view_supporters' ) &&
							$admin_city &&
							get_user_meta( $row['id'], 'city', true ) &&
							$admin_city == get_user_meta( $row['id'], 'city', true )
						)
					)
				) ||
				(
					'slots-actions' === $cur_cap &&
					(
						(
							$current_user->has_cap( 'vca_asm_manage_actions_global' )
						) ||
						(
							$current_user->has_cap( 'vca_asm_manage_actions_nation' )
						) ||
						(
							$current_user->has_cap( 'vca_asm_manage_actions' )
						)
					)
				) ||
				(
					'slots-education' === $cur_cap &&
					(
						(
							$current_user->has_cap( 'vca_asm_manage_education_global' )
						) ||
						(
							$current_user->has_cap( 'vca_asm_manage_education_nation' )
						) ||
						(
							$current_user->has_cap( 'vca_asm_manage_education' )
						)
					)
				) ||
				(
					'slots-network' === $cur_cap &&
					(
						(
							$current_user->has_cap( 'vca_asm_manage_network_global' )
						) ||
						(
							$current_user->has_cap( 'vca_asm_manage_network_nation' )
						) ||
						(
							$current_user->has_cap( 'vca_asm_manage_network' )
						)
					)
				) ||
				(
					'edit-act-actions' === $cur_cap &&
					(
						(
							$current_user->has_cap( 'vca_asm_manage_actions_global' )
						) ||
						(
							$current_user->has_cap( 'vca_asm_manage_actions_nation' ) &&
							$admin_nation === get_post_meta( $row['id'], 'nation', true )
						) ||
						(
							$current_user->has_cap( 'vca_asm_manage_actions' ) &&
							$admin_city === get_post_meta( $row['id'], 'city', true ) &&
							'delegate' === get_post_meta( $row['id'], 'delegate', true )
						)
					)
				) ||
				(
					'edit-act-education' === $cur_cap &&
					(
						(
							$current_user->has_cap( 'vca_asm_manage_education_global' )
						) ||
						(
							$current_user->has_cap( 'vca_asm_manage_actions_nation' ) &&
							$admin_nation === get_post_meta( $row['id'], 'nation', true )
						) ||
						(
							$current_user->has_cap( 'vca_asm_manage_actions' ) &&
							$admin_city === get_post_meta( $row['id'], 'city', true ) &&
							'delegate' === get_post_meta( $row['id'], 'delegate', true )
						)
					)
				) ||
				(
					'edit-act-network' === $cur_cap &&
					(
						(
							$current_user->has_cap( 'vca_asm_manage_network_global' )
						) ||
						(
							$current_user->has_cap( 'vca_asm_manage_actions_nation' ) &&
							$admin_nation === get_post_meta( $row['id'], 'nation', true )
						) ||
						(
							$current_user->has_cap( 'vca_asm_manage_actions' ) &&
							$admin_city === get_post_meta( $row['id'], 'city', true ) &&
							'delegate' === get_post_meta( $row['id'], 'delegate', true )
						)
					)
				) ||
				(
					'manage-apps' === $cur_cap &&
					true
				) ||
				(
					'manage-waiting' === $cur_cap &&
					true
				) ||
				(
					'manage-accepted' === $cur_cap &&
					true
				) ||
				(
					'view_emails' === $cur_cap &&
					(
						$current_user->has_cap( 'vca_asm_view_emails_global' ) ||
						(
							$current_user->has_cap( 'vca_asm_view_emails_nation' ) &&
							$admin_nation &&
							$admin_nation == 999 // need to pass email nation
						) ||
						(
							$current_user->has_cap( 'vca_asm_view_emails' ) &&
							$admin_city &&
							$admin_city == 999 // need to pass email city
						) || true
					)
				) ||
				(
					'edit-transaction' === $cur_cap &&
					(
						$current_user->has_cap( 'vca_asm_manage_finances_global' ) ||
						(
							$current_user->has_cap( 'vca_asm_manage_finances_nation' ) &&
							(
								(
									$admin_nation &&
									$admin_nation  === 999 // pass transaction nation
								) ||
								true
							)
						) ||
						(
							$current_user->has_cap( 'vca_asm_manage_finances' ) &&
							isset( $row['editable'] ) && 1 === $row['editable'] &&
							(
								(
									$admin_city &&
									$admin_city  === 999 // pass transaction city
								) ||
								true
							)
						)
					)
				) ||
				(
					( 'set-receipt-status' === $cur_cap || 'confirm-receipt' === $cur_cap ) &&
					(
						( 'confirm-receipt' === $cur_cap && ( 1 == $row['receipt_status'] || 2 == $row['receipt_status'] ) ) &&
						(
							(
								$current_user->has_cap( 'vca_asm_manage_finances_global' )
							) ||
							(
								$current_user->has_cap( 'vca_asm_manage_finances_nation' ) &&
								(
									(
										$admin_nation &&
										$admin_nation  === 999 // pass transaction nation
									) ||
									true
								)
							) ||
							(
								$current_user->has_cap( 'vca_asm_manage_finances' ) &&
								1 == $row['receipt_status'] &&
								(
									(
										$admin_city &&
										$admin_city  === 999 // pass transaction city
									) ||
									true
								)
							)
						)
					)
				) ||
				(
					'unconfirm-receipt' === $cur_cap &&
					(
						(
							(
								2 == $row['receipt_status'] && 'expenditure' === $row['transaction_type_plain']
							) || 3 == $row['receipt_status']
						) &&
						(
							(
								$current_user->has_cap( 'vca_asm_manage_finances_global' )
							) ||
							(
								$current_user->has_cap( 'vca_asm_manage_finances_nation' ) &&
								(
									(
										$admin_nation &&
										$admin_nation  === 999 // pass transaction nation
									) ||
									true
								)
							) ||
							(
								$current_user->has_cap( 'vca_asm_manage_finances' ) &&
								2 == $row['receipt_status'] &&
								(
									(
										$admin_city &&
										$admin_city  === 999 // pass transaction city
									) ||
									true
								)
							)
						)
					)
				) ||
				(
					'finances-meta' === $cur_cap &&
					(
						$current_user->has_cap( 'vca_asm_manage_finances_global' ) ||
						(
							$current_user->has_cap( 'vca_asm_manage_finances_nation' ) &&
							(
								(
									$admin_nation &&
									$admin_nation  === 999 // pass transaction nation
								) ||
								true
							)
						)
					)
				)
			) {
				if ( $i !== 0 && $i < $action_count ) {
					$output .= ( ! $override ) ? ( $flipper ? ' | ' : '<br />' ) : '';
					$flipper = ! $flipper;
				}
				$override = false;

				switch( $actions[$i] ) {
					case 'edit':
						$output .= '<span class="edit">' .
							'<a title="' .
								sprintf( __( 'Edit %s', 'vca-asm' ), $name ) .
								'" href="' . $url . '&todo=edit&amp;id=' . $row['id'] . '">' .
								__( 'Edit', 'vca-asm' ) .
							'</a></span>';
					break;

					case 'edit-ei':
						$output .= '<span class="edit">' .
							'<a title="' .
								sprintf( __( 'Edit %s', 'vca-asm' ), $name ) .
								'" href="' . $url . '&todo=edit-ei&amp;id=' . $row['id'] . '">' .
								__( 'Edit', 'vca-asm' ) .
							'</a></span>';
					break;

					case 'edit-occ':
						$output .= '<span class="edit">' .
							'<a title="' .
								sprintf( __( 'Edit %s', 'vca-asm' ), $name ) .
								'" href="' . $url . '&todo=edit-occ&amp;id=' . $row['id'] . '">' .
								__( 'Edit', 'vca-asm' ) .
							'</a></span>';
					break;

					case 'edit-cc':
						$output .= '<span class="edit">' .
							'<a title="' .
								sprintf( __( 'Edit %s', 'vca-asm' ), $name ) .
								'" href="' . $url . '&todo=edit-cc&amp;id=' . $row['id'] . '">' .
								__( 'Edit', 'vca-asm' ) .
							'</a></span>';
					break;

					case 'edit-tax':
						$output .= '<span class="edit">' .
							'<a title="' .
								sprintf( __( 'Edit %s', 'vca-asm' ), $name ) .
								'" href="' . $url . '&todo=edit-tax&amp;id=' . $row['id'] . '">' .
								__( 'Edit', 'vca-asm' ) .
							'</a></span>';
					break;

					case 'view-account':
						$output .= '<span class="edit">' .
							'<a title="' .
								sprintf( __( 'View %s', 'vca-asm' ), $name ) .
								'" href="' . $url . '&todo=edit-ei&amp;id=' . $row['id'] . '">' .
								__( 'View', 'vca-asm' ) .
							'</a></span>';
					break;

					case 'delete':
					case 'delete-user':
						$output .= '<span class="delete">' .
							'<a title="' .
								sprintf( __( 'Delete %s', 'vca-asm' ), $name ) .
							'" onclick="if ( confirm(\'' .
									ucfirst( sprintf( __( 'Really delete &quot;%s&quot;?', 'vca-asm' ), $name ) ) .
								'\') ) { return true; } return false;" ' .
								'href="' . $url . '&todo=delete&amp;id=' .
								$row['id'] . '" class="submitdelete">' .
								__( 'Delete', 'vca-asm' ) .
							'</a></span>';
					break;

					case 'edit-transaction':
						$output .= '<span class="edit">' .
							'<a title="' .
								sprintf( __( 'Edit %s', 'vca-asm' ), $name ) .
								'" href="' . $url . '&todo=edit&amp;id=' . $row['id'] . '">' .
								__( 'Edit', 'vca-asm' ) .
							'</a></span>';
					break;

					case 'delete-donation':
					case 'delete-transaction':
						$output .= '<span class="delete">' .
							'<a title="' .
								__( 'Delete this transaction', 'vca-asm' ) .
							'" onclick="if ( confirm(\'' .
									ucfirst( __( 'Really delete this transaction?', 'vca-asm' ) ) .
								'\') ) { return true; } return false;" ' .
								'href="' . $url . '&todo=delete&amp;id=' .
								$row['id'] . '" class="submitdelete">' .
								__( 'Delete', 'vca-asm' ) .
							'</a></span>';
					break;

					case 'confirm-receipt':
						$output .= '<span class="edit">' .
							'<a title="';
							$output .= ( 1 == $row['receipt_status'] ) ? __( 'Has been sent', 'vca-asm' ) : __( 'Confirm reception', 'vca-asm' );
						$output .= '" href="' . $url . '&todo=confirm-receipt&id=' . $row['id'] . '&step=' . $row['receipt_status'] . '&cid=' . $row['city_id'] . '">';
							$output .= ( 1 == $row['receipt_status'] ) ? __( 'Sent!', 'vca-asm' ) : __( 'Received!', 'vca-asm' );
						$output .= '</a></span>';
					break;

					case 'unconfirm-receipt':
						$output .= '<span class="edit">' .
							'<a title="';
							$output .= ( 2 == $row['receipt_status'] ) ? __( 'Ooops. Has not been sent', 'vca-asm' ) : __( 'Ooops. Has not been received', 'vca-asm' );
						$output .= '" href="' . $url . '&todo=unconfirm-receipt&amp;id=' . $row['id'] . '&step=' . $row['receipt_status'] . '&cid=' . $row['city_id'] . '">';
							$output .= ( 2 == $row['receipt_status'] ) ? __( 'Not sent...', 'vca-asm' ) : __( 'Not received...', 'vca-asm' );
						$output .= '</a></span>';
					break;

					case 'profile':
						$output .= '<span class="edit">' .
							'<a title="' .
								sprintf( __( 'View Supporter ID Card of %s', 'vca-asm' ), $name ) .
								'" href="' . $this->args['profile_url'] . '&profile=' .
								$row['id'] . '">' .
								__( 'ID Card', 'vca-asm' ) .
							'</a></span>';
					break;

					case 'role':
						$output .= '<span class="edit">' .
							'<a title="' .
								sprintf( __( 'Change the user role of %s', 'vca-asm' ), $name ) .
								'" href="' . $this->args['profile_url'] . '&profile=' .
								$row['id'] . '&change=role">' .
								__( 'Change', 'vca-asm' ) .
							'</a></span>';
					break;

					case 'send_email':
						$output .= '<span class="edit">' .
							'<a title="' .
								sprintf( __( 'Send an email to %s', 'vca-asm' ), $name ) .
								'" href="admin.php?page=vca-asm-emails&amp;uid=' . $row['id'] . '">' .
								__( 'Send Mail', 'vca-asm' ) .
							'</a></span>';
					break;

					case 'edit_membership':
						if( 0 == $row['membership_raw'] ) {
							$output .= '<span class="edit"><a title="' .
									sprintf( __( 'Promote %s', 'vca-asm' ), $name ) .
									'" onclick="if ( confirm(\'' .
											sprintf( __( 'Promote %s, even though he or she has not applied?', 'vca-asm' ), $name ) .
										'\') ) { return true; } return false;" ' .
									'" href="' . $url . '&todo=promote&amp;id=' . $row['id'] . '">' .
									__( 'Promote', 'vca-asm' ) .
								'</a></span>';
						} elseif( 1 == $row['membership_raw'] ) {
							$output .= '<span class="edit"><a title="' .
									sprintf( __( 'Accept the application of %s', 'vca-asm' ), $name ) .
									'" href="' . $url . '&todo=accept&amp;id=' . $row['id'] . '">' .
									__( 'Accept', 'vca-asm' ) .
								'</a></span>';
							$output .= $flipper ? ' | ' : '<br />';
							$flipper = ! $flipper;
							$output .= '<span class="delete"><a title="' .
									sprintf( __( 'Deny membership to %s', 'vca-asm' ), $name ) .
									'" onclick="if ( confirm(\'' .
											sprintf( __( 'Really deny the application of %s?', 'vca-asm' ), $name ) .
										'\') ) { return true; } return false;" ' .
									'" href="' . $url . '&todo=deny&amp;id=' . $row['id'] . '">' .
									__( 'Deny', 'vca-asm' ) .
								'</a></span>';
						} elseif( 2 == $row['membership_raw'] ) {
							$output .= '<span class="delete"><a title="' .
									sprintf( __( 'End membership of %s', 'vca-asm' ), $name ) .
									'" onclick="if ( confirm(\'' .
											sprintf( __( 'Really remove %s from the active members?', 'vca-asm' ), $name ) .
										'\') ) { return true; } return false;" ' .
									'" href="' . $url . '&todo=remove&amp;id=' . $row['id'] . '">' .
									__( 'End membership', 'vca-asm' ) .
								'</a></span>';
						} else {
							$flipper = ! $flipper;
						}
					break;

					case 'app_accept':
						$output .= '<span class="edit"><a title="' .
								sprintf( __( 'Accept the application by %s', 'vca-asm' ), $name ) .
								'" href="' . $url . '&todo=accept&amp;id=' . $row['id'] . '">' .
								__( 'Accept', 'vca-asm' ) .
							'</a></span>';
					break;

					case 'app_deny':
						$output .= '<span class="delete">' .
							'<a title="' .
								sprintf( __( 'Deny application by %s (Move to waiting list)', 'vca-asm' ), $name ) .
							'" onclick="if ( confirm(\'' .
									ucfirst( sprintf( __( 'Deny the application by %s and move him/her to the waiting list?', 'vca-asm' ), $name ) ) .
								'\') ) { return true; } return false;" ' .
								'href="' . $url . '&todo=deny&amp;id=' .
								$row['id'] . '" class="submitdelete">' .
								__( 'Deny', 'vca-asm' ) .
							'</a></span>';
					break;

					case 'waitinglist_accept':
						$output .= '<span class="edit"><a title="' .
								sprintf( __( 'Accept the application retrospectively and move %s to the participants', 'vca-asm' ), $name ) .
								'" href="' . $url . '&todo=accept&amp;id=' . $row['id'] . '">' .
								__( 'Accept', 'vca-asm' ) .
							'</a></span>';
					break;

					case 'revoke_accepted':
						$output .= '<span class="delete">' .
							'<a title="' .
								sprintf( __( 'Revoke accepted application by %s (Irrevocable!)', 'vca-asm' ), $name ) .
							'" onclick="if ( confirm(\'' .
									sprintf( __( 'Revoke the accepted application of %s and remove him/her from the list of participants?', 'vca-asm' ), $name ) .
									'\\n\\n' .
									__( 'Attention: This does not move the supporter to the waiting list - it removes him/her from the participants entirely!', 'vca-asm' ) .
								'\') ) { return true; } return false;" ' .
								'href="' . $url . '&todo=revoke&amp;id=' .
								$row['id'] . '" class="submitdelete">' .
								__( 'Remove', 'vca-asm' ) .
							'</a></span>';
					break;

					case 'edit_act':
						$output .= '<span class="edit">' .
							'<a title="' .
								sprintf( __( 'Edit &quot;%s&quot;', 'vca-asm' ), $activity_title ) .
								'" href="post.php?post=' . $row['id'] . '&action=edit">' .
								sprintf( __( 'Edit %s', 'vca-asm' ), $activity_type ) .
							'</a></span>';
					break;

					case 'manage_apps':
						$output .= '<span class="edit">' .
							'<a title="' .
								sprintf( __( 'Manage applications of %s', 'vca-asm' ), $name ) .
								'" href="' . $url . '&activity=' . $row['id'] . '&tab=apps">' .
								__( 'Applicants & participants', 'vca-asm' ) .
							'</a></span>';
					break;

					case 'manage_participants':
						$output .= '<span class="edit">' .
							'<a title="' .
								sprintf( __( 'Manage participants of %s', 'vca-asm' ), $name ) .
								'" href="' . $url . '&activity=' . $row['id'] . '&tab=accepted">' .
								__( 'Manage participants', 'vca-asm' ) .
							'</a></span>';
					break;

					case 'emails_read':
						$output .= '<span class="edit">' .
								'<a title="' .
									__( 'View the Email', 'vca-asm' ) .
									'" target="_blank" href="' . get_option( 'siteurl' ) . '/email?id=' . $row['id'] . '">' .
									__( 'Read', 'vca-asm' ) .
								'</a>' .
							'</span> | ' .
							'<span class="edit">' .
								'<a title="' .
									__( 'Edit the Email and forward it to another receipient group', 'vca-asm' ) .
									'" href="admin.php?page=vca-asm-compose&amp;id=' . $row['id'] . '">' .
									__( 'Forward', 'vca-asm' ) .
								'</a>' .
							'</span>';
					break;

					case 'outbox_read':
						$output .= '<span class="edit">' .
								'<a title="' .
									__( 'View the Email', 'vca-asm' ) .
									'" target="_blank" href="' . get_option( 'siteurl' ) . '/email?id=' . $row['mail_id'] . '">' .
									__( 'Read', 'vca-asm' ) .
								'</a>' .
							'</span> | ' .
							'<span class="edit">' .
								'<a title="' .
									__( 'Edit the Email and forward it to another receipient group', 'vca-asm' ) .
									'" href="admin.php?page=vca-asm-compose&amp;id=' . $row['mail_id'] . '">' .
									__( 'Forward', 'vca-asm' ) .
								'</a>' .
							'</span>';
					break;
				}
			} else {
				$override = true;
				$flipper = ! $flipper;
				$output .= $i === ( $action_count - 1 ) ? '&nbsp;' : '';
			}
		}

		$output .= '</div>';

		return $output;
	}

	/**
	 * Converts raw data to a more presentable format
	 * (Used in edge cases, *should* be avoided or done outside this class, ideally)
	 *
	 * @since 1.3
	 * @access private
	 */
	private function convert_data( $data, $conversion_type, $row, $column ) {
		$output = $data;
		switch( $conversion_type ) {
			case 'empty-to-dashes':
				if ( empty( $row[$column] ) && 0 !== $row[$column] && '0' !== $row[$column] ) {
					$output = '---';
				}
			break;

			case 'region-status':
			case 'geo-type':
				if( 'cell' === $data ) {
					$output = '<span class="cell-color">' . __( 'Cell', 'vca-asm' ) . '</span>';
				} elseif( 'lc' === $data ) {
					$output = '<span class="lc-color">' . __( 'Local Crew', 'vca-asm' ) . '</span>';
				} else {
					$output = '<span class="geo-color">' . __( 'City', 'vca-asm' ) . '</span>';
				}
			break;

			case 'membership':
				if ( isset( $row['membership_raw'] ) && 1 == $row['membership_raw'] ) {
					$output = '<strong style="color:#008fc1">' . $data . '</strong>';
				}
			break;

			case 'balance':
				if ( ! empty( $row['balance'] ) ) {
					$output = '<span';
					$output .= intval( $row['balance'] ) < 0 ? ' class="negative"' : '';
					$output .= '>' . $data . '</span>';
				}
			break;

			case 'amount':
				if ( ! empty( $row['amount'] ) ) {
					$output = '<span class="amount';
					$output .= 'transfer' === $row['transaction_type_plain'] ? ' transfer"' : ( 'donation' === $row['transaction_type_plain'] ? ( ( 1 == $row['cash'] ) ? ' cash-donation"' : ' donation"' ) : ( intval( $row['amount'] ) < 0 ? ' negative"' : ' positive"' ) );
					$output .= '>' . $data . '</span>';
				}
			break;

			case 'receipt':
				if ( ! empty( $row['receipt'] ) && '---' !== $row['receipt'] && 0 != $row['receipt_status'] ) {
					$output = '<span class="';
					$output .= 'receipt-' . $row['receipt_status'];
					$output .= '">' . $data . '</span>';
				}
			break;

			case 'receipt-status':
				if ( 0 != $row['receipt_status'] ) {
					$output = '<span class="';
					$output .= 'receipt-' . $row['receipt_status'];
					$output .= '">' . $data . '</span>';
				}
			break;

			case 'pcc':
				if ( isset( $row['country_code'] ) && ! empty( $row['country_code'] ) ) {
					$output = '+' . $data;
				}
			break;

			case 'city-finances-link':
				if ( ! empty( $row['id'] ) ) {
					$output = '<a title="' . __( 'This is, what the Finances-SPOC sees.', 'vca-asm' ) . '" href="?page=vca-asm-finances&cid=' . $row['id'] . '">' . $data . '</a>';
				}
			break;
		}

		return $output;
	}

} // class

endif; // class exists

?>