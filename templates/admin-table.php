<?php

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
	if( isset( $column['sortable'] ) && $column['sortable'] === true ) {
		$table_head .= ' sortable ' . strtolower( $order );  
	}
	$table_head .= '" style="" scope="col">';
	if( isset( $column['sortable'] ) && $column['sortable'] === true ) {
		$table_head .= '<a href="' . get_bloginfo( 'url' ) . '/wp-admin/' . $url .
			'&amp;orderby=' . $column['id'] . '&amp;order=' . $toggle_order . '">';
	}
	$table_head .= '<span>' . $column['title'] . '</span>';
	if( isset( $column['sortable'] ) && $column['sortable'] === true ) {
		$table_head .= '<span class="sorting-indicator"></span></a>';
	}
	$table_head .= '</th>';
}
$table_head .= '</tr>';

if ( ! isset( $skip_wrap ) || true !== $skip_wrap ) {
	$output .= '<div class="wrap">' .
		'<h2>';
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
		$output .= '<tr valign="middle" class="alternate">';
		foreach( $columns as $column ) {
			if( isset( $column['check'] ) && $column['check'] === true ) {
				$output .= '<th class="check-column" scope="row">' .
						'<input type="checkbox" name="' . $column['name'] . '[]" value="' . $row['check'] . '">'.
					'</th>';
				continue;
			}	
			$output .= '<td class="column-' . $column['id'] . '">';
			if( isset( $column['strong'] ) && $column['strong'] === true ) {
				$output .= '<strong>';
			}
			if( isset( $column['editable'] ) && $column['editable'] === true ) {
				$output .= '<a title="' .
					sprintf( __( 'Edit %s', 'hhh-mgmt' ), $row[$column['id']] ) .
						'" href="' . $url . '&amp;todo=edit&amp;id='.$row['id'] . '">';
			}
			if( isset( $column['promotable'] ) && $column['promotable'] === true ) {
				$output .= '<a title="' .
					sprintf( __( 'Promote %s', 'hhh-mgmt' ), $row[$column['id']] ) .
						'" href="' . $url . '&amp;todo=promote&amp;id='.$row['id'] . '">';
			}
			if( ! isset( $column['conversion'] ) ) {
				$output .= $row[$column['id']];
			} else {
				switch( $column['conversion'] ) {
					case 'region-status':
						if( $row[$column['id']] == 'cell' ) {
							$output .= '<span class="cell-color">' . __( 'Cell', 'hhh-mgmt' ) . '</span>';
						} elseif( $row[$column['id']] == 'lc' ) {
							$output .= '<span class="lc-color">' . __( 'Local Crew', 'hhh-mgmt' ) . '</span>';
						} else {
							$output .= '<span class="geo-color">' . __( 'Geographical Only', 'hhh-mgmt' ) . '</span>';
						}
					break;
				}
			}
			if( ( isset( $column['editable'] ) && $column['editable'] === true ) || ( isset( $column['promotable'] ) && $column['promotable'] === true ) ) {
				$output .= '</a>';
			}
			if( isset( $column['strong'] ) && $column['strong'] === true ) {
				$output .= '</strong>';
			}
			if( isset( $column['editable'] ) && $column['editable'] === true ) {
				$output .= '<br/>' .
					'<div class="row-actions">' .
						'<span class="edit"><a title="' .
							sprintf( __( 'Edit %s', 'hhh-mgmt' ), $row[$column['id']] ) .
							'" href="' . $url . '&amp;todo=edit&amp;id=' . $row['id'] . '">' .
							__( 'Edit', 'hhh-mgmt' ) .
						'</a></span> | ' .
						'<span class="delete">' .
							'<a title="' .
								sprintf( __( 'Delete %s', 'hhh-mgmt' ), $row[$column['id']] ) .
							'" onclick="if ( confirm(\'' .
									sprintf( __( 'Really delete &quot;%s&quot;?', 'hhh-mgmt' ), $row[$column['id']] ) .
								'\') ) { return true; } return false;" ' .
								'href="' . $url . '&amp;todo=delete&amp;id=' .
								$row['id'] . '" class="submitdelete">' .
								__( 'Delete', 'hhh-mgmt' ) .	
							'</a>' .
						'</span>' .
					'</div>';
			}
			$output .= '</td>';
		}
	}
}
$output .= '</tbody></table>';

if ( ! isset( $skip_wrap ) || true !== $skip_wrap ) {
	$output .= '</div>';
	echo $output;
}
	
?>