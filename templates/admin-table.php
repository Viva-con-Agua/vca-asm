<?php

/**
 * Template for tabular data used in the backend
 *
 * DEPRECATED -- DO NOT USE FOR FUTURE MODULES
 *
 **/

if( ! isset( $output ) ) {
	$output = '';
}

$table_head = '<tr>';
foreach( $columns as $column ) {
	$table_head .= '<th id="' . $column['id'] . '" class="manage-column column-' . $column['id'];
	if( isset( $column['check'] ) && $column['check'] === true ) {
		$table_head .= ' check-column" style="" scope="col"><input type="checkbox"></th>';
		continue;
	}
	if( isset( $column['legacy-screen'] ) && $column['legacy-screen'] === false ) {
		$table_head .= ' legacy-screen-hide-pa';
	} elseif( isset( $column['tablet'] ) && $column['tablet'] === false ) {
		$table_head .= ' tablet-hide-pa';
	} elseif( isset( $column['mobile'] ) && $column['mobile'] === false ) {
		$table_head .= ' screen-pa';
	} elseif( isset( $column['legacy-mobile'] ) && $column['legacy-mobile'] === false ) {
		$table_head .= ' legacy-mobile-hide-pa';
	}
	if( isset( $column['sortable'] ) && $column['sortable'] === true ) {
		if( $column['id'] !== $orderby ) {
			$col_order = 'DESC';
			$col_toggle_order = 'ASC';
		} else {
			$col_order = $order;
			$col_toggle_order = $toggle_order;
		}
		$table_head .= ' sortable ' . strtolower( $col_order );
	}
	$table_head .= '" style="" scope="col">';
	if( isset( $column['sortable'] ) && $column['sortable'] === true ) {
		$table_head .= '<a href="' . get_bloginfo( 'url' ) . '/wp-admin/';
		if( isset( $sort_url ) ) {
			$table_head .= $sort_url;
		} else {
			$table_head .= $url;
		}
		$table_head .= '&amp;orderby=' . $column['id'] . '&amp;order=' . $col_toggle_order . '">';
	}
	$table_head .= '<span>' . $column['title'] . '</span>';
	if( isset( $column['sortable'] ) && $column['sortable'] === true ) {
		$table_head .= '<span class="sorting-indicator"></span></a>';
	}
	$table_head .= '</th>';
}
$table_head .= '</tr>';

if ( ! isset( $skip_wrap ) || true !== $skip_wrap ) {
	$output .= '<div class="wrap">';
	if( isset( $icon ) ) {
		$output .= $icon;
	} else {
		$output .= '<div id="icon-edit" class="icon32 icon32-posts-post"><br></div>';
	}
	$output .= '<h2>';
	if( isset( $headline ) && ! empty( $headline ) ) {
		$output .= $headline;
	} else {
		$output .= 'Data Table';
	}
	$output .= '</h2><br />';
}

	$output .= '<table class="wp-list widefat fixed" cellspacing="0">'.
	'<thead>'. $table_head . '</thead>'.
	'<tfoot>'. $table_head . '</tfoot>'.
	'<tbody>';

if ( ! empty( $rows ) ) {
	foreach ( $rows as $row ) {

		if( ! empty( $row['first_name'] ) ) {
			$name = $row['first_name'];
		} elseif( ! empty( $row['username'] ) ) {
			$name = __( 'username', 'vca-asm' ) . ' &quot;' . $row['username'] . '&quot;';
		} else {
			$name = __( 'this supporter', 'vca-asm' );
		}
		if( ! isset( $profile_url ) ) {
			$profile_url = $url;
		}
		$profile_action = '<span class="edit">' .
				'<a title="' .
					sprintf( __( 'View Supporter ID Card of %s', 'vca-asm' ), $name ) .
					'" href="' . $profile_url . '&amp;profile=' .
					$row['id'] . '">' .
					__( 'ID Card', 'vca-asm' ) .
				'</a>' .
			'</span>';
		if( isset( $list_type ) && 'waiting' === $list_type ) {
			$accept_title = sprintf( __( 'Accept the application retrospectively and move %s to the participants', 'vca-asm' ), $name );
		} else {
			$accept_title = sprintf( __( 'Accept the application by %s', 'vca-asm' ), $name );
		}
		$accept_action = '<span class="edit"><a title="' .
				$accept_title .
				'" href="' . $url . '&amp;todo=accept&amp;id=' . $row['id'] . '">' .
				__( 'Accept', 'vca-asm' ) .
			'</a></span>';
		$deny_action = '<span class="delete">' .
				'<a title="' .
					sprintf( __( 'Deny application by %s (Move to waiting list)', 'vca-asm' ), $name ) .
				'" onclick="if ( confirm(\'' .
						ucfirst( sprintf( __( 'Deny the application by %s and move him/her to the waiting list?', 'vca-asm' ), $name ) ) .
					'\') ) { return true; } return false;" ' .
					'href="' . $url . '&amp;todo=deny&amp;id=' .
					$row['id'] . '" class="submitdelete">' .
					__( 'Deny', 'vca-asm' ) .
				'</a>' .
			'</span>';
		$revoke_action = '<span class="delete">' .
				'<a title="' .
					sprintf( __( 'Revoke accepted application by %s (Irrevocable!)', 'vca-asm' ), $name ) .
				'" onclick="if ( confirm(\'' .
						sprintf( __( 'Revoke the accepted application of %s and remove him/her from the list of participants?', 'vca-asm' ), $name ) .
						'\\n\\n' .
						__( 'Attention: This does not move the supporter to the waiting list - it removes him/her from the participants entirely!', 'vca-asm' ) .
					'\') ) { return true; } return false;" ' .
					'href="' . $url . '&amp;todo=revoke&amp;id=' .
					$row['id'] . '" class="submitdelete">' .
					__( 'Remove', 'vca-asm' ) .
				'</a>' .
			'</span>';


		$output .= '<tr valign="middle" class="alternate">';
		foreach( $columns as $column ) {
			if( isset( $column['check'] ) && $column['check'] === true ) {
				$output .= '<th class="check-column" scope="row">' .
						'<input type="checkbox" name="' . $column['name'] . '[]" value="' . $row['check'] . '">'.
					'</th>';
				continue;
			}
			$output .= '<td class="column-' . $column['id'];
			if( isset( $column['legacy-screen'] ) && $column['legacy-screen'] === false ) {
				$output .= ' legacy-screen-hide-pa';
			} elseif( isset( $column['tablet'] ) && $column['tablet'] === false ) {
				$output .= ' tablet-hide-pa';
			} elseif( isset( $column['mobile'] ) && $column['mobile'] === false ) {
				$output .= ' screen-pa';
			} elseif( isset( $column['legacy-mobile'] ) && $column['legacy-mobile'] === false ) {
				$output .= ' legacy-mobile-hide-pa';
			}
			$output .= '">';
			if( isset( $column['strong'] ) && $column['strong'] === true ) {
				$output .= '<strong>';
			}
			if( isset( $column['editable'] ) && $column['editable'] === true ) {
				$output .= '<a title="' .
					sprintf( __( 'Edit %s', 'vca-asm' ), $row[$column['id']] ) .
						'" href="' . $url . '&amp;todo=edit&amp;id='.$row['id'] . '">';
			}
			if( ! isset( $column['conversion'] ) ) {
				$output .= $row[$column['id']];
			} else {
				switch( $column['conversion'] ) {
					case 'region-status':
						if( $row[$column['id']] == 'cell' ) {
							$output .= '<span class="cell-color">' . __( 'Cell', 'vca-asm' ) . '</span>';
						} elseif( $row[$column['id']] == 'lc' ) {
							$output .= '<span class="lc-color">' . __( 'Local Crew', 'vca-asm' ) . '</span>';
						} else {
							$output .= '<span class="geo-color">' . __( 'Geographical Only', 'vca-asm' ) . '</span>';
						}
					break;

					case 'membership':
						if( isset( $row['membership_raw'] ) && 1 == $row['membership_raw'] ) {
							$output .= '<strong style="color:#008fc1">' . $row[$column['id']] . '</strong>';
						} else {
							$output .= $row[$column['id']];
						}
					break;
				}
			}
			if( ( isset( $column['editable'] ) && $column['editable'] === true ) ) {
				$output .= '</a>';
			}
			if( isset( $column['strong'] ) && $column['strong'] === true ) {
				$output .= '</strong>';
			}
			if( isset( $column['editable'] ) && $column['editable'] === true ) {
				$output .= '<br/>' .
					'<div class="row-actions">' .
						'<span class="edit"><a title="' .
							sprintf( __( 'Edit %s', 'vca-asm' ), $name ) .
							'" href="' . $url . '&amp;todo=edit&amp;id=' . $row['id'] . '">' .
							__( 'Edit', 'vca-asm' ) .
						'</a></span> | ' .
						'<span class="delete">' .
							'<a title="' .
								sprintf( __( 'Delete %s', 'vca-asm' ), $row[$column['id']]) .
							'" onclick="if ( confirm(\'' .
									ucfirst( sprintf( __( 'Really delete &quot;%s&quot;?', 'vca-asm' ), $row[$column['id']]) ) .
								'\') ) { return true; } return false;" ' .
								'href="' . $url . '&amp;todo=delete&amp;id=' .
								$row['id'] . '" class="submitdelete">' .
								__( 'Delete', 'vca-asm' ) .
							'</a>' .
						'</span>' .
					'</div>';
			}
			if( isset( $column['deletable-user'] ) && $column['deletable-user'] === true && true !== $column['profileable'] ) {
				$output .= '<br/>' .
					'<div class="row-actions">' .
						'<span class="delete">' .
							'<a title="' .
								sprintf( __( 'Delete %s', 'vca-asm' ), $name ) .
							'" onclick="if ( confirm(\'' .
									sprintf( __( 'Really delete %s?', 'vca-asm' ), $name ) .
								'\') ) { return true; } return false;" ' .
								'href="' . $url . '&amp;todo=delete&amp;id=' .
								$row['id'] . '" class="submitdelete">' .
								__( 'Delete', 'vca-asm' ) .
							'</a>' .
						'</span>' .
					'</div>';
			}
			if( isset( $column['deletable-user'] ) && $column['deletable-user'] === true && isset( $column['profileable'] ) && $column['profileable'] === true ) {
				$output .= '<br/>' .
					'<div class="row-actions">' .
						$profile_action .
						' | <span class="delete">' .
							'<a title="' .
								sprintf( __( 'Delete %s', 'vca-asm' ), $name ) .
							'" onclick="if ( confirm(\'' .
									sprintf( __( 'Really delete %s?', 'vca-asm' ), $name ) .
								'\') ) { return true; } return false;" ' .
								'href="' . $url . '&amp;todo=delete&amp;id=' .
								$row['id'] . '" class="submitdelete">' .
								__( 'Delete', 'vca-asm' ) .
							'</a>' .
						'</span>' .
					'</div>';
			}
			if( isset( $column['profileable'] ) && $column['profileable'] === true && true !== $column['deletable-user'] ) {
				$output .= '<br/>' .
					'<div class="row-actions">' .
						$profile_action .
					'</div>';
			}
			if( isset( $column['mailable'] ) && $column['mailable'] === true && is_email( $row[$column['id']] ) ) {
				$output .= '<br/>' .
					'<div class="row-actions">' .
						'<span class="edit">' .
							'<a title="' .
								sprintf( __( 'Send an email to %s', 'vca-asm' ), $name ) .
								'" href="admin.php?page=vca-asm-emails&amp;uid=' . $row['id'] . '">' .
								__( 'Send Mail', 'vca-asm' ) .
							'</a>' .
						'</span>' .
					'</div>';
			}
			if( isset( $column['promotable'] ) && $column['promotable'] === true ) {
				$output .= '<br/>' .
					'<div class="row-actions">';
				if( 0 == $row['membership_raw'] ) {
					$output .= '<span class="edit"><a title="' .
							sprintf( __( 'Promote %s', 'vca-asm' ), $name ) .
							'" onclick="if ( confirm(\'' .
									sprintf( __( 'Promote %s, even though he or she has not applied?', 'vca-asm' ), $name ) .
								'\') ) { return true; } return false;" ' .
							'" href="' . $url . '&amp;todo=promote&amp;id=' . $row['id'] . '">' .
							__( 'Promote', 'vca-asm' ) .
						'</a></span>';
				} elseif( 1 == $row['membership_raw'] ) {
					$output .= '<span class="edit"><a title="' .
							sprintf( __( 'Accept the application of %s', 'vca-asm' ), $name ) .
							'" href="' . $url . '&amp;todo=accept&amp;id=' . $row['id'] . '">' .
							__( 'Accept', 'vca-asm' ) .
						'</a></span> | <span class="delete"><a title="' .
							sprintf( __( 'Deny membership to %s', 'vca-asm' ), $name ) .
							'" onclick="if ( confirm(\'' .
									sprintf( __( 'Really deny the application of %s?', 'vca-asm' ), $name ) .
								'\') ) { return true; } return false;" ' .
							'" href="' . $url . '&amp;todo=deny&amp;id=' . $row['id'] . '">' .
							__( 'Deny', 'vca-asm' ) .
						'</a></span>';
				} elseif( 2 == $row['membership_raw'] ) {
					$output .= '<span class="delete"><a title="' .
							sprintf( __( 'End membership of %s', 'vca-asm' ), $name ) .
							'" onclick="if ( confirm(\'' .
									sprintf( __( 'Really remove %s from the active members?', 'vca-asm' ), $name ) .
								'\') ) { return true; } return false;" ' .
							'" href="' . $url . '&amp;todo=remove&amp;id=' . $row['id'] . '">' .
							__( 'End membership', 'vca-asm' ) .
						'</a></span>';
				}
				$output .= '</div>';
			}
			if( isset( $column['actions'] ) ) {
				switch( $column['actions'] ) {
					case "emails":
						$output .= '<br/>' .
							'<div class="row-actions">' .
								'<span class="edit">' .
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
								'</span>' .
							'</div>';
					break;
				}
			}
			if( isset( $column['app_handling'] ) ) {
				$output .= '<br/>' .
					'<div class="row-actions">';
				switch( $column['app_handling'] ) {
					case "apps":
						$output .= $accept_action . ' | ' . $deny_action . '<br />' .
							$profile_action;
					break;

					case "waiting":
						$output .= $accept_action . '<br />' .
							$profile_action;
					break;

					case "accepted":
						$output .= $revoke_action . '<br />' .
							$profile_action;
					break;

					default:
						$profile_action;
					break;
				}
				$output .= '</div>';
			}
			$output .= '</td>';
		}
		$output .= '</tr>';
	}
}
$output .= '</tbody></table>';

if ( ! isset( $skip_wrap ) || true !== $skip_wrap ) {
	$output .= '</div>';
	echo $output;
}

?>