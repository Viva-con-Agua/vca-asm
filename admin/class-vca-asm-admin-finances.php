<?php

/**
 * VCA_ASM_Admin_Finances class.
 *
 * This class contains properties and methods for
 * the activities management
 *
 * @package VcA Activity & Supporter Management
 * @since 1.5
 */

if ( ! class_exists( 'VCA_ASM_Admin_Finances' ) ) :

class VCA_ASM_Admin_Finances
{

	/**
	 * Class Properties
	 *
	 * @since 1.4
	 */
	private $per_page = 50;
	private $has_cap = false;
	private $cap_lvl = '';
	private $admin_city = 0;
	private $admin_nation = 0;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 * @access public
	 */
	public function __construct()
	{
		global $current_user;

		/* check capabilities */
		if ( $current_user->has_cap( 'vca_asm_view_finances_global' ) ) {
			$this->has_cap = $current_user->has_cap( 'vca_asm_manage_finances_global' ) ? true : false;
			$this->cap_lvl = 'global';
		} elseif ( $current_user->has_cap( 'vca_asm_view_finances_nation' ) ) {
			$this->has_cap = $current_user->has_cap( 'vca_asm_manage_finances_nation' ) ? true : false;
			$this->cap_lvl = 'nation';
		} elseif ( $current_user->has_cap( 'vca_asm_view_finances' ) ) {
			$this->has_cap = $current_user->has_cap( 'vca_asm_manage_finances' ) ? true : false;
			$this->cap_lvl = 'city';
		}

		$this->admin_city = get_user_meta( $current_user->ID, 'city', true );
		$this->admin_nation = get_user_meta( $current_user->ID, 'nation', true );
	}

	/******************** OVERVIEW ********************/

	/**
	 * Controller for the  Overview Menu
	 *
	 * @since 1.5
	 * @access public
	 */
	public function overview_control()
	{
		global $wpdb,
			$vca_asm_finances;

		if ( isset( $_GET['todo'] ) ) {
			switch ( $_GET['todo'] ) {
				case 'balance':
					$city = ! empty( $_POST['city'] ) ? $_POST['city'] : $this->admin_city;
					if ( isset( $_POST['month_econ'] ) ) {
						$wpdb->update(
							$wpdb->prefix . 'vca_asm_finances_accounts',
							array( 'balanced_month' => $_POST['month_econ'] ),
							array( 'city_id' => $city, 'type' => 'econ' ),
							array( '%s' ),
							array( '%d', '%s' )
						);
					}
					if ( isset( $_POST['month_don'] ) ) {
						$wpdb->update(
							$wpdb->prefix . 'vca_asm_finances_accounts',
							array( 'balanced_month' => $_POST['month_don'] ),
							array( 'city_id' => $city, 'type' => 'donations' ),
							array( '%s' ),
							array( '%d', '%s' )
						);
					}
				break;

				case 'download-data':
					$args = array(
						'scope' => $this->cap_lvl,
						'account' => isset( $_POST['account'] ) ? $_POST['account'] : 'econ',
						'id' => $this->admin_nation,
						'format' => isset( $_POST['format'] ) ? $_POST['format'] : 'month',
						'year' => isset( $_POST['year'] ) ? $_POST['year'] : date( 'Y' ),
						'month' => isset( $_POST['month'] ) ? $_POST['month'] : date( 'm' ),
						'type' => isset( $_POST['type'] ) ? $_POST['type'] : 'city',
						'format' => isset( $_POST['format'] ) ? $_POST['format'] : 'xlsx',
						'gridlines' => ( isset( $_POST['gridlines'] ) && 2 == $_POST['gridlines'] ) ? false : true
					);
					$data = new VCA_ASM_Workbook_Finances(
						array(
							'scope' => $this->cap_lvl,
							'account' => isset( $_POST['account'] ) ? $_POST['account'] : 'econ',
							'id' => $this->admin_nation,
							'timeframe' => isset( $_POST['timeframe'] ) ? $_POST['timeframe'] : 'month',
							'year' => isset( $_POST['year'] ) ? $_POST['year'] : date( 'Y' ),
							'month' => isset( $_POST['month'] ) ? $_POST['month'] : date( 'm', strtotime( date( 'm' ) . ' -1 month' ) ),
							'type' => isset( $_POST['type'] ) ? $_POST['type'] : 'city',
							'format' => isset( $_POST['format'] ) ? $_POST['format'] : 'xlsx',
							'gridlines' => ( isset( $_POST['gridlines'] ) && 2 == $_POST['gridlines'] ) ? false : true
						)
					);
					$data->output();
				break;
			}
		}

		if ( 'city' === $this->cap_lvl || isset( $_GET['cid'] ) ) {
			$city_id = isset( $_GET['cid'] ) ? $_GET['cid'] : 0;
			$this->overview_city( $city_id );
		} else {
			$this->overview_global();
		}
	}

	private function overview_city( $id = NULL, $messages = array() )
	{		
		$active_tab = 'summary';
		if ( isset( $_GET['tab'] ) && in_array( $_GET['tab'], array( 'summary', 'download' ) ) ) {
			$active_tab = $_GET['tab'];
		}

		$tabs = array(
			array(
				'title' => _x( 'Summary', ' Admin Menu', 'vca-asm' ),
				'value' => 'summary',
				'icon' => 'icon-summary'
			),
			array(
				'title' => _x( 'Download Data', ' Admin Menu', 'vca-asm' ),
				'value' => 'download',
				'icon' => 'icon-print'
			)
		);
		
		$city = ! empty( $id ) ? $id : ( ! empty( $_GET['city'] ) && in_array( $this->cap_lvl, array( 'global', 'national' ) ) ? $_GET['city'] : $this->admin_city );

		$url = 'admin.php?page=vca-asm-finances&tab=' . $active_tab;

		$adminpage = new VCA_ASM_Admin_Page( array(
			'icon' => 'icon-finances',
			'title' => _x( 'Finances', 'Admin Menu', 'vca-asm' ) . ' | ' . _x( 'Overview', 'Admin Menu', 'vca-asm' ),
			'messages' => $messages,
			'url' => $url,
			'tabs' => $tabs,
			'active_tab' => $active_tab
		));

		$output = $adminpage->top();

		switch ( $active_tab ) {
			
			case 'download':
				$output .= $this->overview_city_download( $city );
			break;

			case 'summary':
			default:
				$output .= $this->overview_city_summary( $city );
			break;
		}

		$output .= $adminpage->bottom();

		echo $output;
	}
	
	private function overview_city_summary( $city = NULL )
	{
		global $vca_asm_finances, $vca_asm_geography;
		
		$active_tab = 'summary';
		if ( isset( $_GET['tab'] ) && in_array( $_GET['tab'], array( 'summary', 'download' ) ) ) {
			$active_tab = $_GET['tab'];
		}

		$url = 'admin.php?page=vca-asm-finances&tab=' . $active_tab;
		
		$output = '';

		$the_city_finances = new VCA_ASM_City_Finances( $city );

		$start_date = strtotime( strftime( '%Y' ) . '/' . strftime( '%m' ) . '/01 -1 month' );
		$end_date_econ = strtotime(
			strftime( '%Y', $the_city_finances->balanced_month_econ_threshold_stamp ) . '/' .
			strftime( '%m', $the_city_finances->balanced_month_econ_threshold_stamp ) . '/01'
		);
		$end_date_don = strtotime(
			strftime( '%Y', $the_city_finances->balanced_month_don_threshold_stamp ) . '/' .
			strftime( '%m', $the_city_finances->balanced_month_don_threshold_stamp ) . '/01'
		);

		$fields = array();
		$balanced = 0;

		if ( $end_date_econ < $start_date ) {
			$current_date = $start_date;
			$options = array();
			while ( $current_date >= $end_date_econ ) {
				$options[] = array(
					'label' => strftime( '%B %Y', $current_date ),
					'value' => strftime( '%Y-%m', $current_date )
				);
				$current_date = strtotime( date( 'Y/m/01/', $current_date ) . ' -1 month' );
			}
			$fields[] = array(
				'type' => 'select',
				'id' => 'month_econ',
				'options' => $options,
				'label' => _x( 'Structural Account', 'short', 'vca-asm' ),
				'desc' => __( 'You hereby confirm to have entered all transactions until the end of the selected month.', 'vca-asm' )
			);
			$balanced_econ = false;
		} else {
			$fields[] = array(
				'type' => 'note',
				'label' => _x( 'Structural Account', 'short', 'vca-asm' ),
				'value' => __( 'The account is up to date.', 'vca-asm' )
			);
			$balanced_econ = true;
			$balanced++;
		}

		if ( $end_date_don < $start_date ) {
			$current_date = $start_date;
			$options = array();
			while ( $current_date >= $end_date_don ) {
				$options[] = array(
					'label' => strftime( '%B %Y', $current_date ),
					'value' => strftime( '%Y-%m', $current_date )
				);
				$current_date = strtotime( date( 'Y/m/01/', $current_date ) . ' -1 month' );
			}
			$fields[] = array(
				'type' => 'select',
				'id' => 'month_don',
				'options' => $options,
				'label' => _x( 'Donations Account', 'short', 'vca-asm' ),
				'desc' => __( 'You hereby confirm to have entered all donations until the end of the selected month.', 'vca-asm' )
			);
			$balanced_don = false;
		} else {
			$fields[] = array(
				'type' => 'note',
				'label' => _x( 'Donations Account', 'short', 'vca-asm' ),
				'value' => __( 'The account is up to date.', 'vca-asm' )
			);
			$balanced_don = true;
			$balanced++;
		}

		$fields[] = array(
			'type' => 'hidden',
			'id' => 'city',
			'value' => $city
		);

		$mbs = new VCA_ASM_Admin_Metaboxes( array(
			'echo' => false,
			'columns' => 1,
			'running' => 1,
			'id' => '',
			'title' => __( 'Accounts', 'vca-asm' ),
			'js' => false
		));

		if ( $balanced !== 2 ) {
			$form = new VCA_ASM_Admin_Form( array(
				'echo' => false,
				'form' => true,
				'name' => 'vca-asm-form',
				'method' => 'post',
				'metaboxes' => false,
				'js' => false,
				'url' => $url,
				'action' => $url . '&todo=balance',
				'nonce' => 'vca-asm',
				'id' => 0,
				'button' => __( 'Balance now!', 'vca-asm' ),
				'button_id' => 'submit-balance',
				'top_button' => false,
				'confirm' => true,
				'confirm_text' => __( 'Are you sure? This process is irreversible...', 'vca-asm' ),
				'confirm_button_affirmative' => __( 'Balance it!', 'vca-asm' ),
				'confirm_button_negative' => __( 'Not sure...', 'vca-asm' ),
				'loading_text' => __( 'Months are being balanced...', 'vca-asm' ),
				'back' => false,
				'has_cap' => true,
				'fields' => $fields
			));
		}

		$output .= $mbs->top();

		$output .= $mbs->mb_top();

		$output .= '<table>' .
			'<tr>' .
				'<td>' . __( 'Structural Account', 'vca-asm' ) . ':</td><td class="right-aligned-tcell"><strong>' . number_format( $the_city_finances->balance_econ/100, 2, ',', '.' ) . ' &euro;</strong></td>' .
			'</tr>';

		if ( $the_city_finances->has_econ_surplus ) {
			$output .= '<tr>' .
					'<td colspan="2"><span class="warning">' . __( 'Attention: Surplus!', 'vca-asm' ) . '</span></td>' .
				'</tr>';
		}

		$output .= '<tr><td>' . __( 'Donations (Cash) Account', 'vca-asm' ) . ':</td><td class="right-aligned-tcell"><strong>' . number_format( $the_city_finances->balance_don/100, 2, ',', '.' ) . ' &euro;</strong></td>';
		foreach ( $the_city_finances->donations_by_years as $year => $amount ) {
			if ( 'total' !== $year ) {
				$output .= '</tr><tr><td>' . sprintf( _x( 'Donations in %s', 'Placeholder is a year', 'vca-asm' ), $year ) . ':</td><td class="right-aligned-tcell"><strong>' . number_format( $amount/100, 2, ',', '.' ) . ' &euro;</strong></td>';
			}
		}
		$output .= '</tr><tr><td>' . __( 'Donations, total', 'vca-asm' ) . ':</td><td class="right-aligned-tcell"><strong>' . number_format( $the_city_finances->donations_total/100, 2, ',', '.' ) . ' &euro;</strong></td>' .
			'</tr></table>';
		$output .= $mbs->mb_bottom();

		$output .= $mbs->mb_top( array( 'title' => __( 'Transfers', 'vca-asm' ) ) );

		$output .= '<table>' .
			'<tr>' .
				'<td>' . __( 'Donation transfers to be confirmed', 'vca-asm' ) . '</td><td>&nbsp;&nbsp;';
				if ( ! empty( $the_city_finances->confirmable_don_transfers ) ) {
					$j = 1;
					foreach ( $the_city_finances->confirmable_don_transfers as $transfer ) {
						$output .= '<a title="' . __( 'See details', 'vca-asm' ) . '"' .
							' href="?page=vca-asm-finances-accounts-don&tab=transfer&referrer=overview-city&cid=' . $the_city_finances->id . '#row-' . $transfer['id'] . '">' .
								number_format( abs( $transfer['amount'] )/100, 2, ',', '.' ) . ' ' .
								$the_city_finances->currency_symbol .
								' (' . strftime( '%d.%m.%y', $transfer['transaction_date'] ) . ')' .
							'</a><span>';
						if ( $j < count( $the_city_finances->confirmable_don_transfers ) ) {
							$output .= '<br />';
						}
						$j++;
					}
				} else {
					$output .= '<em>' . _x( 'None', 'Transfers', 'vca-asm' ) . '</em>';
				}
				$output .= '</td>' .
			'</tr>' .
			'<tr>' .
				'<td>' . __( 'External donation transfers to be confirmed', 'vca-asm' ) . '</td><td>&nbsp;&nbsp;';
				if ( ! empty( $the_city_finances->confirmable_external_transfers ) ) {
					$j = 1;
					foreach ( $the_city_finances->confirmable_external_transfers as $transfer ) {
						$output .= '<a title="' . __( 'See details', 'vca-asm' ) . '"' .
							' href="?page=vca-asm-finances-accounts-don&tab=transfer&referrer=overview-city&cid=' . $the_city_finances->id . '#row-' . $transfer['id'] . '">' .
								number_format( abs( $transfer['amount'] )/100, 2, ',', '.' ) . ' ' .
								$the_city_finances->currency_symbol .
								' (' . strftime( '%d.%m.%y', $transfer['transaction_date'] ) . ')' .
							'</a><span>';
						if ( $j < count( $the_city_finances->confirmable_external_transfers ) ) {
							$output .= '<br />';
						}
						$j++;
					}
				} else {
					$output .= '<em>' . _x( 'None', 'Transfers', 'vca-asm' ) . '</em>';
				}
				$output .= '</td>' .
			'</tr>' .
			'<tr>' .
				'<td>' . __( 'Structural transfers to be confirmed', 'vca-asm' ) . '</td><td>&nbsp;&nbsp;';
				if ( ! empty( $the_city_finances->confirmable_econ_transfers ) ) {
					$j = 1;
					foreach ( $the_city_finances->confirmable_econ_transfers as $transfer ) {
						$output .= '<a title="' . __( 'See details', 'vca-asm' ) . '"' .
							' href="?page=vca-asm-finances-accounts-econ&tab=transfer&referrer=overview-city&cid=' . $the_city_finances->id . '#row-' . $transfer['id'] . '">' .
								number_format( abs( $transfer['amount'] )/100, 2, ',', '.' ) . ' ' .
								$the_city_finances->currency_symbol .
								' (' . strftime( '%d.%m.%y', $transfer['transaction_date'] ) . ')' .
							'</a><span>';
						if ( $j < count( $the_city_finances->confirmable_econ_transfers ) ) {
							$output .= '<br />';
						}
						$j++;
					}
				} else {
					$output .= '<em>' . _x( 'None', 'Transfers', 'vca-asm' ) . '</em>';
				}
				$output .= '</td>' .
			'</tr></table>';

		if ( $the_city_finances->balance_don > 0 ) {
			$output .= '<table><tr>' .
					'<td colspan="2"><em>' .
						sprintf(
							__( '%s of donations are present in cash.', 'vca-asm' ),
							$the_city_finances->balance_don_formatted
						) .
						' ' . __( 'Please transfer / deposit them to:', 'vca-asm' ) .
					'</em></td>' .
				'</tr>' .
				'<tr>' .
					'<td>' . __( 'Institute', 'vca-asm' ) . '</td><td>&nbsp;&nbsp;' . 'Commerzbank' . '</td>' .
				'</tr>' .
				'<tr>' .
					'<td>' . __( 'IBAN', 'vca-asm' ) . '</td><td>&nbsp;&nbsp;' . 'DE72200400000362273500' . '</td>' .
				'</tr>' .
				'<tr>' .
					'<td>' . __( 'BIC', 'vca-asm' ) . '</td><td>&nbsp;&nbsp;' . 'COBADEFFXXX' . '</td>' .
				'</tr>' .
			'</table>';
		}

		if ( $the_city_finances->has_econ_surplus ) {
			$output .= '<table><tr>' .
					'<td colspan="2"><em>' .
						sprintf(
							__( 'You have a structural cash surplus of %s.', 'vca-asm' ),
							$the_city_finances->econ_surplus_formatted
						) .
						' ' . __( 'Please transfer it to:', 'vca-asm' ) .
					'</em></td>' .
				'</tr>' .
				'<tr>' .
					'<td>' . __( 'Institute', 'vca-asm' ) . '</td><td>&nbsp;&nbsp;' . 'Commerzbank' . '</td>' .
				'</tr>' .
				'<tr>' .
					'<td>' . __( 'IBAN', 'vca-asm' ) . '</td><td>&nbsp;&nbsp;' . 'DE72200400000362273500' . '</td>' .
				'</tr>' .
				'<tr>' .
					'<td>' . __( 'BIC', 'vca-asm' ) . '</td><td>&nbsp;&nbsp;' . 'COBADEFFXXX' . '</td>' .
				'</tr>' .
				'<tr>' .
					'<td>' . __( 'Transaction Reference', 'vca-asm' ) . '</td><td>&nbsp;&nbsp;' . __( 'Excess Funds', 'vca-asm' ) . ' / ' . $vca_asm_geography->get_name( $city ) . '</td>' .
				'</tr>' .
			'</table>';
		}

		$output .= $mbs->mb_bottom();

		$output .= $mbs->mb_top( array( 'title' => __( 'Receipts', 'vca-asm' ) ) );
		$output .= '<table><tr><td style="vertical-align:top;">' . __( 'need to be sent', 'vca-asm' ) . ':</td><td class="right-aligned-tcell">';
		if ( ! empty( $the_city_finances->sendable_receipts_full ) ) {
			$j = 1;
			foreach ( $the_city_finances->sendable_receipts_full as $receipt ) {
				$output .= '<a title="' . __( 'See details', 'vca-asm' ) . '"' .
					' href="?page=vca-asm-finances-accounts-econ&tab=expenditure&referrer=overview-city&cid=' . $the_city_finances->id . '#row-' . $receipt['id'] . '">' .
						$receipt['receipt_id'] .
						' (' . strftime( '%d.%m.%y', $receipt['receipt_date'] ) . ')' .
					'</a><span>';
				if ( $j < count( $the_city_finances->sendable_receipts_full ) ) {
					$output .= '<br />';
				}
				$j++;
			}
		} else {
			$output .= '<em>' . __( 'No receipts that could be sent...', 'vca-asm' ) . '</em>';
		}
		$output .= '</td></tr>';
		$output .= '<tr><td style="vertical-align:top;">' . __( 'have been sent', 'vca-asm' ) . ':</td><td class="right-aligned-tcell">';
				if ( ! empty( $the_city_finances->sent_receipts_full ) ) {
			$j = 1;
			foreach ( $the_city_finances->sent_receipts_full as $receipt ) {
				$output .= '<a title="' . __( 'See details', 'vca-asm' ) . '"' .
					' href="?page=vca-asm-finances-accounts-econ&tab=expenditure&referrer=overview-city&cid=' . $the_city_finances->id . '#row-' . $receipt['id'] . '">' .
						$receipt['receipt_id'] .
						' (' . strftime( '%d.%m.%y', $receipt['receipt_date'] ) . ')' .
					'</a><span>';
				if ( $j < count( $the_city_finances->sent_receipts_full ) ) {
					$output .= '<br />';
				}
				$j++;
			}
		} else {
			$output .= '<em>' . __( 'No receipts waiting for confirmation...', 'vca-asm' ) . '</em>';
		}
		$output .= '</td></tr></table>';

		$output .= $mbs->mb_bottom();

		$output .= $mbs->mb_top( array( 'title' => __( 'Monthly Balancing', 'vca-asm' ) ) );
		$output .= '<table><tr><td>' . __( 'Last balanced month, Structural', 'vca-asm' ) . ':</td><td class="right-aligned-tcell"><strong>' . $the_city_finances->balanced_month_econ_name . '</strong>';
		if ( $the_city_finances->action_required_econ_balance ) {
			$output .= '</td><td class="right-aligned-tcell"><span class="warning">(' . __( 'Needs Balancing!', 'vca-asm' ) . ')</span>';
		}
		$output .= '</td></tr>';
		$output .= '<tr><td>' . __( 'Last balanced month, Donations', 'vca-asm' ) . ':</td><td class="right-aligned-tcell"><strong>' . $the_city_finances->balanced_month_don_name . '</strong>';
		if ( $the_city_finances->action_required_don_balance ) {
			$output .= '</td><td class="right-aligned-tcell"><span class="warning">(' . __( 'Needs Balancing!', 'vca-asm' ) . ')</span>';
		}
		$output .= '</td></tr>';
		if ( $balanced !== 2 ) {
			$output .= '</table>' . $form->output();
		} else {
			$output .= '<tr><td><em>' .
					__( 'The account is up to date!', 'vca-asm' ) .
				'</em></td></tr></table>';
		}

		$output .= $mbs->mb_bottom();

		$output .= $mbs->bottom();
		
		return $output;
	}
	
	private function overview_city_download( $city = NULL )
	{
		wp_enqueue_script( 'vca-asm-admin-finances-spreadsheet-form' );

		$url = '?page=vca-asm-finances&tab=download';

		$output = '';

		$mbs = new VCA_ASM_Admin_Metaboxes( array(
			'echo' => false,
			'columns' => 1,
			'running' => 1,
			'id' => '',
			'title' => __( 'Download Account Statement', 'vca-asm' ),
			'js' => true
		));

		$years = array();
		for ( $y = date( 'Y' ); $y >= 2014; $y-- ) {
			$years[] = array(
				'value' => $y,
				'label' => $y
			);
		}
		$months = array();
		for ( $m = 1; $m <= 12; $m++ ) {
			$months[] = array(
				'value' => $m,
				'label' => strftime( '%B', strtotime( '01.' . $m . '.2014' ) )
			);
		}

		$fields = array(
			array(
				'type' => 'select',
				'id' => 'account',
				'options' => array(
					array(
						'label' => __( 'Donations', 'vca-asm' ),
						'value' => 'donations'
					),
					array(
						'label' => __( 'Structural Funds', 'vca-asm' ),
						'value' => 'econ'
					)
				),
				'label' => __( 'Type of Account', 'vca-asm' ),
				'desc' => __( 'Output statement for this kind of account.', 'vca-asm' )
			),
			array(
				'type' => 'select',
				'id' => 'timeframe',
				'options' => array(
					array(
						'label' => __( 'Monthly', 'vca-asm' ),
						'value' => 'month'
					),
					array(
						'label' => __( 'Annually', 'vca-asm' ),
						'value' => 'year'
					),
					array(
						'label' => __( 'Total', 'vca-asm' ),
						'value' => 'total'
					)
				),
				'label' => __( 'Timeframe', 'vca-asm' ),
				'desc' => __( 'Output statement for this kind of timeframe.', 'vca-asm' )
			),
			array(
				'type' => 'select',
				'id' => 'year',
				'options' => $years,
				'label' => __( 'Year', 'vca-asm' ),
				'desc' => __( 'Data from this year.', 'vca-asm' ),
				'default' => date( 'Y' )
			),
			array(
				'type' => 'select',
				'id' => 'month',
				'options' => $months,
				'label' => __( 'Month', 'vca-asm' ),
				'desc' => __( 'Data from this month.', 'vca-asm' ),
				'default' => date( 'm', mktime( 0, 0, 1, ( date( 'n' ) - 1 ), 1, '2014' ) )
			),
			array(
				'type' => 'select',
				'id' => 'format',
				'options' => array(
					array(
						'value' => 'xlsx',
						'label' => __( 'Office 2007 (.xlsx)', 'vca-asm' )
					),
					array(
						'value' => 'xlsx2003',
						'label' => __( 'Office 2003, (.xlsx)', 'vca-asm' )
					),
					array(
						'value' => 'xls',
						'label' => __( 'Office 95 to XP (.xls)', 'vca-asm' )
					)/*,
					array(
						'value' => 'csv',
						'label' => __( 'Plain text, single sheet (.csv)', 'vca-asm' )
					)*/
				),
				'label' => _x( 'Format', 'Excel File Format', 'vca-asm' ),
				'desc' => __( 'Download as this kind of file format.', 'vca-asm' ) . '<br />' .
					__( 'Choose &quot;Office 2007&quot; for best results.', 'vca-asm' )/* . '<br />' .
					__( 'Note that &quot;.csv&quot; files do not support sheets/tabs.', 'vca-asm' )*/
			),
			array(
				'type' => 'radio',
				'id' => 'gridlines',
				'options' => array(
					array(
						'value' => 1,
						'label' => __( 'Show', 'vca-asm' )
					),
					array(
						'value' => 2,
						'label' => __( 'Hide', 'vca-asm' )
					)
				),
				'default' => 1,
				'label' => __( 'Gridlines', 'vca-asm' ),
				'desc' => __( 'Do you want the file to show gridlines?', 'vca-asm' )
			)
		);

		$form_args = array(
			'echo' => false,
			'form' => true,
			'method' => 'post',
			'metaboxes' => false,
			'js' => false,
			'url' => $url,
			'action' => $url . '&todo=download-data&noheader=true',
			'top_button' => false,
			'button' => __( 'Download Spreadsheet', 'vca-asm' ),
			'back' => false,
			'button_id' => 'submit',
			'fields' => $fields
		);

		$output .= $mbs->top();

		$output .= $mbs->mb_top();

		$the_form = new VCA_ASM_Admin_Form( $form_args );
		$output .= $the_form->output();

		$output .= $mbs->mb_bottom();

		$output .= $mbs->bottom();

		return $output;
	}

	private function overview_global( $messages = array() )
	{
		$active_tab = 'summary';
		if ( isset( $_GET['tab'] ) && in_array( $_GET['tab'], array( 'summary', 'tabular', 'cities', 'download' ) ) ) {
			$active_tab = $_GET['tab'];
		}

		$tabs = array(
			array(
				'title' => _x( 'Summary', ' Admin Menu', 'vca-asm' ),
				'value' => 'summary',
				'icon' => 'icon-summary'
			),
			array(
				'title' => _x( 'Cities', ' Admin Menu', 'vca-asm' ) . ' (' . _x( 'tabular', ' Admin Menu', 'vca-asm' ) . ')',
				'value' => 'tabular',
				'icon' => 'icon-city'
			),
			array(
				'title' => _x( 'Download Data', ' Admin Menu', 'vca-asm' ),
				'value' => 'download',
				'icon' => 'icon-print'
			)
			//,
			//array(
			//	'title' => _x( 'Cities', ' Admin Menu', 'vca-asm' ) . ' (' . _x( 'Details', ' Admin Menu', 'vca-asm' ) . ')',
			//	'value' => 'cities',
			//	'icon' => 'icon-cg'
			//)
		);

		$adminpage = new VCA_ASM_Admin_Page( array(
			'icon' => 'icon-finances',
			'title' => _x( 'Finances', 'Admin Menu', 'vca-asm' ) . ' | ' . _x( 'Overview', 'Admin Menu', 'vca-asm' ),
			'messages' => $messages,
			'url' => '?page=vca-asm-finances',
			'tabs' => $tabs,
			'active_tab' => $active_tab
		));

		$output = $adminpage->top();

		switch ( $active_tab ) {
			case 'cities':
				$output .= $this->overview_global_cities( $messages );
			break;

			case 'tabular':
				$output .= $this->overview_global_tabular( $messages );
			break;

			case 'download':
				$output .= $this->overview_global_download( $messages );
			break;

			case 'summary':
			default:
				$output .= $this->overview_global_summary( $messages );
			break;
		}

		$output .= $adminpage->bottom();

		echo $output;
	}

	private function overview_global_download( $messages = array() )
	{
		wp_enqueue_script( 'vca-asm-admin-finances-spreadsheet-form' );

		$url = '?page=vca-asm-finances&tab=download';

		$output = '';

		$mbs = new VCA_ASM_Admin_Metaboxes( array(
			'echo' => false,
			'columns' => 1,
			'running' => 1,
			'id' => '',
			'title' => __( 'Download Account Statement', 'vca-asm' ),
			'js' => true
		));

		$years = array();
		for ( $y = date( 'Y' ); $y >= 2014; $y-- ) {
			$years[] = array(
				'value' => $y,
				'label' => $y
			);
		}
		$months = array();
		for ( $m = 1; $m <= 12; $m++ ) {
			$months[] = array(
				'value' => $m,
				'label' => strftime( '%B', strtotime( '01.' . $m . '.2014' ) )
			);
		}

		$fields = array(
			array(
				'type' => 'select',
				'id' => 'account',
				'options' => array(
					array(
						'label' => __( 'Structural Funds', 'vca-asm' ),
						'value' => 'econ'
					),
					array(
						'label' => __( 'Donations', 'vca-asm' ),
						'value' => 'donations'
					)
				),
				'label' => __( 'Type of Account', 'vca-asm' ),
				'desc' => __( 'Output statement for this kind of account.', 'vca-asm' )
			),
			array(
				'type' => 'select',
				'id' => 'timeframe',
				'options' => array(
					array(
						'label' => __( 'Monthly', 'vca-asm' ),
						'value' => 'month'
					),
					array(
						'label' => __( 'Annually', 'vca-asm' ),
						'value' => 'year'
					),
					array(
						'label' => __( 'Total', 'vca-asm' ),
						'value' => 'total'
					)
				),
				'label' => __( 'Timeframe', 'vca-asm' ),
				'desc' => __( 'Output statement for this kind of timeframe.', 'vca-asm' )
			),
			array(
				'type' => 'select',
				'id' => 'year',
				'options' => $years,
				'label' => __( 'Year', 'vca-asm' ),
				'desc' => __( 'Data from this year.', 'vca-asm' ),
				'default' => date( 'Y' )
			),
			array(
				'type' => 'select',
				'id' => 'month',
				'options' => $months,
				'label' => __( 'Month', 'vca-asm' ),
				'desc' => __( 'Data from this month.', 'vca-asm' ),
				'default' => date( 'm', mktime( 0, 0, 1, ( date( 'n' ) - 1 ), 1, '2014' ) )
			),
			array(
				'type' => 'select',
				'id' => 'type',
				'options' => array(
					array(
						'label' => __( 'City', 'vca-asm' ),
						'value' => 'city'
					),
					array(
						'label' => __( 'Country', 'vca-asm' ),
						'value' => 'nation'
					)
				),
				'label' => __( 'Type', 'vca-asm' ),
				'desc' => __( 'Group data by geography.', 'vca-asm' )
			),
			array(
				'type' => 'select',
				'id' => 'format',
				'options' => array(
					array(
						'value' => 'xlsx',
						'label' => __( 'Office 2007 (.xlsx)', 'vca-asm' )
					),
					array(
						'value' => 'xlsx2003',
						'label' => __( 'Office 2003, (.xlsx)', 'vca-asm' )
					),
					array(
						'value' => 'xls',
						'label' => __( 'Office 95 to XP (.xls)', 'vca-asm' )
					)/*,
					array(
						'value' => 'csv',
						'label' => __( 'Plain text, single sheet (.csv)', 'vca-asm' )
					)*/
				),
				'label' => _x( 'Format', 'Excel File Format', 'vca-asm' ),
				'desc' => __( 'Download as this kind of file format.', 'vca-asm' ) . '<br />' .
					__( 'Choose &quot;Office 2007&quot; for best results.', 'vca-asm' )/* . '<br />' .
					__( 'Note that &quot;.csv&quot; files do not support sheets/tabs.', 'vca-asm' )*/
			),
			array(
				'type' => 'radio',
				'id' => 'gridlines',
				'options' => array(
					array(
						'value' => 1,
						'label' => __( 'Show', 'vca-asm' )
					),
					array(
						'value' => 2,
						'label' => __( 'Hide', 'vca-asm' )
					)
				),
				'default' => 1,
				'label' => __( 'Gridlines', 'vca-asm' ),
				'desc' => __( 'Do you want the file to show gridlines?', 'vca-asm' )
			)
		);

		$form_args = array(
			'echo' => false,
			'form' => true,
			'method' => 'post',
			'metaboxes' => false,
			'js' => false,
			'url' => $url,
			'action' => $url . '&todo=download-data&noheader=true',
			'top_button' => false,
			'button' => __( 'Download Spreadsheet', 'vca-asm' ),
			'back' => false,
			'button_id' => 'submit',
			'fields' => $fields
		);

		$output .= $mbs->top();

		$output .= $mbs->mb_top();

		$the_form = new VCA_ASM_Admin_Form( $form_args );
		$output .= $the_form->output();

		$output .= $mbs->mb_bottom();

		$output .= $mbs->bottom();

		return $output;
	}

	private function overview_global_summary( $messages = array() )
	{
		global $vca_asm_geography;

		$output = '';

		$cities = $vca_asm_geography->get_all( 'name', 'ASC', 'city' );

		$surplus = array();
		$transfers_econ_confirm = array();
		$receipts_late = array();
		$receipts_current = array();
		$receipts_confirm = array();
		$transfers_don_required = array();
		$transfers_don_confirm = array();
		$transfers_external_confirm = array();

		foreach ( $cities as $city ) {
			if (
				'global' === $this->cap_lvl ||
				( 'nation' === $this->cap_lvl &&  $this->admin_nation == $vca_asm_geography->has_nation( $city['id'] ) )
			) {
				$the_city_finances = new VCA_ASM_City_Finances( $city['id'] );

				$anchor_city_overview = '<a title="' . __( 'See details', 'vca-asm' ) . '" href="?page=vca-asm-finances&cid=' . $city['id'] . '&referrer=overview-summary">';
				$anchor_econ_revenue = '<a title="' . __( 'See details', 'vca-asm' ) . '" href="?page=vca-asm-finances-accounts-econ&acc_type=econ&tab=revenue&cid=' . $city['id'] . '&referrer=overview-summary">';
				$anchor_econ_expenditure = '<a title="' . __( 'See details', 'vca-asm' ) . '" href="?page=vca-asm-finances-accounts-econ&acc_type=econ&tab=expenditure&cid=' . $city['id'] . '&referrer=overview-summary">';
				$anchor_econ_transfer = '<a title="' . __( 'See details', 'vca-asm' ) . '" href="?page=vca-asm-finances-accounts-econ&acc_type=econ&tab=transfer&cid=' . $city['id'] . '&referrer=overview-summary">';
				$anchor_don_donation = '<a title="' . __( 'See details', 'vca-asm' ) . '" href="?page=vca-asm-finances-accounts-donations&acc_type=donations&tab=donation&cid=' . $city['id'] . '&referrer=overview-summary">';
				$anchor_don_transfer = '<a title="' . __( 'See details', 'vca-asm' ) . '" href="?page=vca-asm-finances-accounts-donations&acc_type=donations&tab=transfer&cid=' . $city['id'] . '&referrer=overview-summary">';

				if ( $the_city_finances->has_econ_surplus ) {
					$surplus[] = $anchor_econ_transfer . $the_city_finances->name . '</a>';
				}
				if ( $the_city_finances->action_required_econ_confirm_transfer ) {
					$transfers_econ_confirm[] = $anchor_econ_transfer . $the_city_finances->name . '</a>';
				}
				if ( $the_city_finances->action_required_econ_send_receipts_late ) {
					$receipts_late[] = $anchor_econ_expenditure . $the_city_finances->name . '</a>';
				}
				if ( $the_city_finances->action_required_econ_send_receipts ) {
					$receipts_current[] = $anchor_econ_expenditure . $the_city_finances->name . '</a>';
				}
				if ( $the_city_finances->action_required_econ_confirm_receipts ) {
					$receipts_confirm[] = $anchor_econ_expenditure . $the_city_finances->name . '</a>';
				}
				if ( $the_city_finances->action_required_econ_balance_several ) {
					$econ_unbalanced_several[] = $anchor_city_overview . $the_city_finances->name . '</a>';
				} elseif ( $the_city_finances->action_required_econ_balance ) {
					$econ_unbalanced[] = $anchor_city_overview . $the_city_finances->name . '</a>';
				}
				if ( $the_city_finances->action_required_don_transfer ) {
					$transfers_don_required[] = $anchor_don_transfer . $the_city_finances->name . '</a>';
				}
				if ( $the_city_finances->action_required_don_confirm_transfer ) {
					$transfers_don_confirm[] = $anchor_don_transfer . $the_city_finances->name . '</a>';
				}
				if ( $the_city_finances->action_required_don_confirm_external_transfer ) {
					$transfers_external_confirm[] = $anchor_don_transfer . $the_city_finances->name . '</a>';
				}
				if ( $the_city_finances->action_required_don_balance_several ) {
					$don_unbalanced_several[] = $anchor_city_overview . $the_city_finances->name . '</a>';
				} elseif ( $the_city_finances->action_required_don_balance ) {
					$don_unbalanced[] = $anchor_city_overview . $the_city_finances->name . '</a>';
				}
			}
		}

		$surplus_string = ( ! empty( $surplus ) ) ? implode( ', ', $surplus ) : _x( 'None...', 'Cities', 'vca-asm' );
		$transfers_econ_confirm_string = ( ! empty( $transfers_econ_confirm ) ) ? implode( ', ', $transfers_econ_confirm ) : _x( 'None...', 'Cities', 'vca-asm' );
		$receipts_late_string = ( ! empty( $receipts_late ) ) ? implode( ', ', $receipts_late ) : _x( 'None...', 'Cities', 'vca-asm' );
		$receipts_current_string = ( ! empty( $receipts_current ) ) ? implode( ', ', $receipts_current ) : _x( 'None...', 'Cities', 'vca-asm' );
		$receipts_confirm_string = ( ! empty( $receipts_confirm ) ) ? implode( ', ', $receipts_confirm ) : _x( 'None...', 'Cities', 'vca-asm' );
		$econ_unbalanced_several_string = ( ! empty( $econ_unbalanced_several ) ) ? implode( ', ', $econ_unbalanced_several ) : _x( 'None...', 'Cities', 'vca-asm' );
		$econ_unbalanced_string = ( ! empty( $econ_unbalanced ) ) ? implode( ', ', $econ_unbalanced ) : _x( 'None...', 'Cities', 'vca-asm' );
		$transfers_don_required_string = ( ! empty( $transfers_don_required ) ) ? implode( ', ', $transfers_don_required ) : _x( 'None...', 'Cities', 'vca-asm' );
		$transfers_don_confirm_string = ( ! empty( $transfers_don_confirm ) ) ? implode( ', ', $transfers_don_confirm ) : _x( 'None...', 'Cities', 'vca-asm' );
		$transfers_external_confirm_string = ( ! empty( $transfers_external_confirm ) ) ? implode( ', ', $transfers_external_confirm ) : _x( 'None...', 'Cities', 'vca-asm' );
		$don_unbalanced_several_string = ( ! empty( $don_unbalanced_several ) ) ? implode( ', ', $don_unbalanced_several ) : _x( 'None...', 'Cities', 'vca-asm' );
		$don_unbalanced_string = ( ! empty( $don_unbalanced ) ) ? implode( ', ', $don_unbalanced ) : _x( 'None...', 'Cities', 'vca-asm' );

		$mbs = new VCA_ASM_Admin_Metaboxes( array(
			'echo' => false,
			'columns' => 1,
			'running' => 1,
			'id' => '',
			'title' => __( 'Structural Accounts', 'vca-asm' ),
			'js' => false
		));

		$output .= $mbs->top();

		$output .= $mbs->mb_top();
		$output .= '<table>' .
				'<tr>' .
					'<td>' . __( 'With surplus', 'vca-asm' ) . ':</td><td class="some-ctrl-h-class">' . $surplus_string . '</td>' .
				'</tr>' .
				'<tr>' .
					'<td>' . __( 'Transfers to be confirmed', 'vca-asm' ) . ':</td><td class="some-ctrl-h-class">' . $transfers_econ_confirm_string . '</td>' .
				'</tr>' .
				'<tr>' .
					'<td>' . __( 'Receipts to be sent, previous month', 'vca-asm' ) . ':</td><td class="some-ctrl-h-class">' . $receipts_late_string . '</td>' .
				'</tr>' .
				'<tr>' .
					'<td>' . __( 'Receipts to be sent, current month', 'vca-asm' ) . ':</td><td class="some-ctrl-h-class">' . $receipts_current_string . '</td>' .
				'</tr>' .
				'<tr>' .
					'<td>' . __( 'Receipts to be confirmed', 'vca-asm' ) . ':</td><td class="some-ctrl-h-class">' . $receipts_confirm_string . '</td>' .
				'</tr>' .
				'<tr>' .
					'<td>' . __( 'Last month not yet balanced', 'vca-asm' ) . ':</td><td class="some-ctrl-h-class">' . $econ_unbalanced_several_string . '</td>' .
				'</tr>' .
				'<tr>' .
					'<td>' . __( 'Multiple months not yet balanced', 'vca-asm' ) . ':</td><td class="some-ctrl-h-class">' . $econ_unbalanced_string . '</td>' .
				'</tr>' .
			'</table>';
		$output .= $mbs->mb_bottom();

		$output .= $mbs->mb_top( array( 'title' => __( 'Donation Accounts', 'vca-asm' ) ) );
		$output .= '<table>' .
				'<tr>' .
					'<td>' . __( 'Transfers required', 'vca-asm' ) . ':</td><td class="some-ctrl-h-class">' . $transfers_don_required_string . '</td>' .
				'</tr>' .
				'<tr>' .
					'<td>' . __( 'Transfers to be confirmed', 'vca-asm' ) . ':</td><td class="some-ctrl-h-class">' . $transfers_don_confirm_string . '</td>' .
				'</tr>' .
				'<tr>' .
					'<td>' . __( 'External Donations to be confirmed', 'vca-asm' ) . ':</td><td class="some-ctrl-h-class">' . $transfers_external_confirm_string . '</td>' .
				'</tr>' .
				'<tr>' .
					'<td>' . __( 'Last month not yet balanced', 'vca-asm' ) . ':</td><td class="some-ctrl-h-class">' . $don_unbalanced_string . '</td>' .
				'</tr>' .
				'<tr>' .
					'<td>' . __( 'Multiple months not yet balanced', 'vca-asm' ) . ':</td><td class="some-ctrl-h-class">' . $don_unbalanced_several_string . '</td>' .
				'</tr>' .
			'</table>';
		$output .= $mbs->mb_bottom();

		$output .= $mbs->bottom();

		return $output;
	}

	private function overview_global_tabular( $messages = array() )
	{
		global $vca_asm_finances, $vca_asm_geography;

		if ( 'global' === $this->cap_lvl ) {
			$cities = $vca_asm_geography->get_all( 'name', 'ASC', 'city' );
		} else {
			$cities = $vca_asm_geography->get_descendants(
				$this->admin_nation,
				array(
					'data' => 'all',
					'sorted' => true,
					'type' => 'city'
				)
			);
		}

		$attention = '<span class="tbl-icon tbl-icon-warning"></span>';

		$columns = array(
			array(
				'id' => 'name',
				'title' => __( 'City', 'vca-asm' ),
				'sortable' => false,//true,
				'conversion' => 'city-finances-link'
			),
			array(
				'id' => 'balance_econ',
				'title' => __( 'Structural Funds', 'vca-asm' ) . ' (' . __( 'cash in stock', 'vca-asm' ) . ')',
				'sortable' => false//true
			),
			array(
				'id' => 'balanced_month_econ',
				'title' => __( 'Last balanced month', 'vca-asm' ) . ' (' . __( 'Structural Funds', 'vca-asm' ) . ')',
				'sortable' => false//true
			),
			array(
				'id' => 'balance_don',
				'title' => __( 'Donations', 'vca-asm' ) . ' (' . __( 'cash', 'vca-asm' ) . ')',
				'sortable' => false//true
			),
			array(
				'id' => 'balanced_month_don',
				'title' => __( 'Last balanced month', 'vca-asm' ) . ' (' . __( 'Donations', 'vca-asm' ) . ')',
				'sortable' => false//true
			),
			array(
				'id' => 'donations_current_year',
				'title' => sprintf( _x( 'Donations in %s', 'Placeholder is a year', 'vca-asm' ), date( 'Y' ) ),
				'sortable' => false//true
			),
			array(
				'id' => 'tasks',
				'title' => __( 'Tasks', 'vca-asm' ),
				'sortable' => false
			)
		);

		$i = 0;
		$rows = array();
		foreach ( $cities as $city ) {
			$econ_link = '<a style="text-decoration:none;" title="' . __( 'Further Details', 'vca-asm' ) . '" href="admin.php?page=vca-asm-finances-accounts-econ&cid=' . $city['id'] . '">&rarr; ' . __( 'View Transactions', 'vca-asm' ) . '</a>';
			$don_link = '<a style="text-decoration:none;" title="' . __( 'Further Details', 'vca-asm' ) . '" href="admin.php?page=vca-asm-finances-accounts-donations&cid=' . $city['id'] . '">&rarr; ' . __( 'View Transactions', 'vca-asm' ) . '</a>';

			$the_city_finances = new VCA_ASM_City_Finances( $city['id'], array( 'short' => true, 'formatted' => true, 'linked' => true ) );

			$rows[$i]['id'] = $city['id'];
			$rows[$i]['name'] = $the_city_finances->name;

			$rows[$i]['balance_econ_plain'] = $the_city_finances->balance_econ;
			$rows[$i]['balance_econ'] = $the_city_finances->balance_econ_formatted . '<br /><div class="row-actions">' . $econ_link . '</div>';

			$rows[$i]['balance_don_plain'] = $the_city_finances->balance_don;
			$rows[$i]['balance_don'] = $the_city_finances->balance_don_formatted . '<br /><div class="row-actions">' . $don_link . '</div>';

			$rows[$i]['balanced_month_econ'] = $the_city_finances->balanced_month_econ_name;
			$rows[$i]['balanced_month_don'] = $the_city_finances->balanced_month_don_name;

			$rows[$i]['econ_annual_revenue'] = $the_city_finances->econ_annual_revenue_formatted;
			$rows[$i]['econ_annual_expenses'] = $the_city_finances->econ_annual_expenses_formatted;
			$rows[$i]['donations_current_year'] = $the_city_finances->donations_current_year_formatted;

			$rows[$i]['tasks'] = '';

			if ( ! empty( $the_city_finances->messages_city ) ) {
				$j = 1;
				foreach ( $the_city_finances->messages_city as $message ) {
					$rows[$i]['tasks'] .= '<span class="warning">' . $message . '</span>';
					if ( $j < count( $the_city_finances->messages_city ) ) {
						$rows[$i]['tasks'] .= '<br />';
					}
					$j++;
				}
				if ( ! empty( $the_city_finances->messages_office ) ) {
					$rows[$i]['tasks'] .= '<br />-----<br />';
				}
			}
			if ( ! empty( $the_city_finances->messages_office ) ) {
				$j = 1;
				foreach ( $the_city_finances->messages_office as $message ) {
					$rows[$i]['tasks'] .= '<span class="warning">' . $message . '</span>';
					if ( $j < count( $the_city_finances->messages_office ) ) {
						$rows[$i]['tasks'] .= '<br />';
					}
					$j++;
				}
			}

			$i++;
		}

		$the_table = new VCA_ASM_Admin_Table(
			array(
				'echo' => false,
				'orderby' => 'name',
				'order' => 'ASC',
				'toggle_order' => 'DESC',
				'page_slug' => 'vca-asm-finances',
				'base_url' => '',
				'sort_url' => '',
				'show_empty_message' => true,
				'empty_message' => ''
			),
			$columns,
			$rows
		);

		$output = $the_table->output();

		return $output;
	}

	private function overview_global_cities( $messages = array() )
	{
		global $vca_asm_finances, $vca_asm_geography;

		if ( 'gobal' === $this->cap_lvl ) {
			$cities = $vca_asm_geography->get_all( 'name', 'ASC', 'city' );
		} else {
			$cities = $vca_asm_geography->get_descendants(
				$this->admin_nation,
				array(
					'data' => 'all',
					'sorted' => true,
					'type' => 'city'
				)
			);
		}

		$output = '';

		$mbs = new VCA_ASM_Admin_Metaboxes( array(
			'echo' => false,
			'columns' => 1,
			'running' => 1,
			'id' => '',
			'title' => __( 'Whatever', 'vca-asm' ),
			'js' => true
		));

		$output .= $mbs->top();
		foreach ( $cities as $city ) {
			$the_city_finances = new VCA_ASM_City_Finances( $city['id'] );

			$attention = ' &nbsp;<span class="tbl-icon tbl-icon-warning"></span>';
			$title_spot = '';
			$econ_spot = '';
			$don_spot = '';
			if (
				'city' === $this->cap_lvl && $the_city_finances->action_required_city ||
				$the_city_finances->action_required_office
			) {
				$title_spot = $attention;
			}
			if (
				'city' === $this->cap_lvl && $the_city_finances->action_required_econ_city ||
				$the_city_finances->action_required_econ_office
			) {
				$econ_spot = $attention;
			}
			if (
				'city' === $this->cap_lvl && $the_city_finances->action_required_don_city ||
				$the_city_finances->action_required_don_office
			) {
				$don_spot = $attention;
			}

			$output .= $mbs->mb_top( array( 'title' => $city['name'] . $title_spot, 'id' => 'city-' . $city['id'] ) );

			$output .= '<h4><span style="text-decoration:underline;">' . __( 'Structural Funds', 'vca-asm' ) . '</span>' . $econ_spot . '</h4>' .
				'<table>' .
					'<tr>' .
						'<td colspan="2"><a style="text-decoration:none;" title="' . __( 'Further Details', 'vca-asm' ) . '" href="admin.php?page=vca-asm-finances-accounts-econ&cid=' . $city['id'] . '">&rarr; ' . __( 'View Transactions', 'vca-asm' ) . '</a></td>' .
					'</tr>' .
					'<tr>' .
						'<td>' . __( 'Account Balance', 'vca-asm' ) . ':</td><td class="right-aligned-tcell"><strong>' . $the_city_finances->balance_econ_formatted . '</strong></td>' .
					'</tr>' .
					'<tr>' .
						'<td>' . __( 'Revenue this year', 'vca-asm' ) . ':</td><td class="right-aligned-tcell"><strong>' . $the_city_finances->econ_annual_revenue_formatted . '</strong></td>' .
					'</tr>' .
					'<tr>' .
						'<td>' . __( 'Expenses this year', 'vca-asm' ) . ':</td><td class="right-aligned-tcell"><strong>' . $the_city_finances->econ_annual_expenses_formatted . '</strong></td>' .
					'</tr>' .
					'<tr>' .
						'<td>' . __( 'Account balanced until the end of', 'vca-asm' ) . ':</td><td class="right-aligned-tcell"><strong>' . $the_city_finances->balanced_month_econ_name . '</strong></td>' .
					'</tr>';

			if ( $the_city_finances->action_required_econ_balance ) {
				$output .= '<tr>' .
					'<td colspan="2" class="warning">' . __( 'The city needs to balance a previous month', 'vca-asm' ) . '...</td>' .
				'</tr>';
			}

			if ( $the_city_finances->action_required_econ_send_receipts ) {
				$output .= '<tr>' .
					'<td colspan="2" class="warning">' . __( 'The city needs to send receipts from last month', 'vca-asm' ) . '...</td>' .
				'</tr>';
			}

			if ( $the_city_finances->action_required_econ_confirm_receipts ) {
				$output .= '<tr>' .
					'<td colspan="2" class="warning">' . __( 'The reception of sent receipts needs to be confirmed by the office', 'vca-asm' ) . '!</td>' .
				'</tr>';
			}

			if ( $the_city_finances->action_required_econ_confirm_transfer ) {
				$output .= '<tr>' .
					'<td colspan="2" class="warning">' . __( 'The reception of transfer(s) from the city itsself needs confirmation', 'vca-asm' ) . '!</td>' .
				'</tr>';
			}

			$output .= '</table>' .
				'<h4 style="margin-top:1em;"><span style="text-decoration:underline;">' . __( 'Donations', 'vca-asm' ) . '</span>' . $don_spot . '</h4>' .
				'<table>' .
					'<tr>' .
						'<td colspan="2"><a style="text-decoration:none;" title="' . __( 'Further Details', 'vca-asm' ) . '" href="admin.php?page=vca-asm-finances-accounts-donations&cid=' . $city['id'] . '">&rarr; ' . __( 'View Transactions', 'vca-asm' ) . '</a></td>' .
					'</tr>' .
					'<tr>' .
						'<td>' . __( 'Cash money', 'vca-asm' ) . ':</td><td class="right-aligned-tcell"><strong>' . $the_city_finances->balance_don_formatted . '</strong></td>' .
					'</tr>' .
					'<tr>' .
						'<td>' . __( 'Total Donations this year', 'vca-asm' ) . ':</td><td class="right-aligned-tcell"><strong>' . $the_city_finances->donations_current_year_formatted . '</strong></td>' .
					'</tr>' .
					'<tr>' .
						'<td>' . __( 'Account balanced until the end of', 'vca-asm' ) . ':</td><td class="right-aligned-tcell"><strong>' . $the_city_finances->balanced_month_don_name . '</strong></td>' .
					'</tr>';

			if ( $the_city_finances->action_required_don_balance ) {
				$output .= '<tr>' .
					'<td colspan="2" class="warning">' . __( 'The city needs to balance a previous month', 'vca-asm' ) . '...</td>' .
				'</tr>';
			}

			if ( $the_city_finances->action_required_don_transfer ) {
				$output .= '<tr>' .
					'<td colspan="2" class="warning">' . __( 'The city needs to transfer donations it gathered in cash', 'vca-asm' ) . '...</td>' .
				'</tr>';
			}

			if ( $the_city_finances->action_required_don_confirm_transfer ) {
				$output .= '<tr>' .
					'<td colspan="2" class="warning">' . __( 'The reception of transfer(s) from the city itsself needs confirmation', 'vca-asm' ) . '!</td>' .
				'</tr>';
			}

			if ( $the_city_finances->action_required_don_confirm_external_transfer ) {
				$output .= '<tr>' .
					'<td colspan="2" class="warning">' . __( 'The reception of donations transferred to the office directly by third parties needs confirmation', 'vca-asm' ) . '!</td>' .
				'</tr>';
			}

			$output .= '</table>';

			$output .= $mbs->mb_bottom();
		}
		$output .= $mbs->bottom();

		return $output;
	}

	/******************** ACCOUNTS ********************/

	/**
	 * Controller for the  Admin Menu
	 *
	 * @since 1.5
	 * @access public
	 */
	public function accounts_control()
	{
		global $current_user, $wpdb,
			$vca_asm_finances, $vca_asm_geography;

		$validation = new VCA_ASM_Validation();

		$messages = array();
		$page = isset( $_GET['page'] ) ? $_GET['page'] : 'vca-asm-finances-accounts-donations';
		$page_components = explode( '-', $page );
		$acc_type = array_pop( $page_components );
		$acc_type = isset( $_GET['acc_type'] ) ? $_GET['acc_type'] : $acc_type;
		$type = isset( $_GET['type'] ) ? $_GET['type'] : '';
		$cid = isset( $_GET['cid'] ) ? $_GET['cid'] : $this->admin_city;
		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'all';

		if ( isset( $_GET['todo'] ) ) {

			switch ( $_GET['todo'] ) {

				case "delete":
					$has_cap = false;
					if ( isset( $_GET['id'] ) && $_GET['id'] != NULL ) {
						$cid = $vca_asm_finances->get_transaction_city( $_GET['id'] );
						$nid = $vca_asm_geography->has_nation( $cid );
						if (
							$current_user->has_cap( 'vca_asm_manage_finances_global' ) ||
							(
								$current_user->has_cap( 'vca_asm_manage_finances_nation' ) &&
								(
									is_numeric( $nid ) &&
									$nid == $this->admin_nation
								)
							) ||
							(
								$current_user->has_cap( 'vca_asm_manage_finances' ) &&
								(
									$cid == $this->admin_city
								)
							)
						) {
							$has_cap = true;
						}
						if ( ! $has_cap ) {
							$messages[] = array(
								'type' => 'error-pa',
								'message' => __( 'You cannot delete this data. Sorry.', 'vca-asm' )
							);
						} else {
							$success = $wpdb->query(
								"DELETE FROM " .
								$wpdb->prefix . "vca_asm_finances_transactions " .
								"WHERE id = " . $_GET['id']
							);
							if ( $success ) {
								$messages[] = array(
									'type' => 'message',
									'message' => __( 'The selected transaction has been successfully deleted.', 'vca-asm' )
								);
							} else {
								$messages[] = array(
									'type' => 'error-pa',
									'message' => __( 'There was an error deleting this data. Sorry.', 'vca-asm' )
								);
							}
						}
					} else {
						$messages[] = array(
							'type' => 'error-pa',
							'message' => __( 'There was an error deleting this data. Sorry.', 'vca-asm' )
						);
					}
					unset($_GET['todo'], $_GET['id']);
					$this->single_view( array(
						'messages' => $messages,
						'account_type' => $acc_type,
						'city_id' => $cid,
						'page' => $page,
						'active_tab' => $active_tab
					));
				break;

				case "save":
					$insert = isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ? false : true;
					$fields = $this->create_fields( $acc_type.'-'.$type, 0, $cid );
					if ( $insert ) {
						$data = array(
							'account_type' => $acc_type,
							'transaction_type' => $type,
							'city_id' => $cid,
							'entry_time' => time()
						);
						$format = array( '%s', '%s', '%d', '%d' );
					} else {
						$data = array();
						$format = array();
					}

					foreach ( $fields as $box ) {
						foreach ( $box['fields'] as $field ) {
							switch ( $field['type'] ) {
								case 'cash_amount':
									$sum = abs( intval( $_POST[$field['id'].'_major'] . str_pad( $_POST[$field['id'].'_minor'], 2, '0', STR_PAD_LEFT ) ) );
									if (
										'expenditure' === $type ||
										( 'transfer' === $type && empty( $_POST['direction'] ) )
									) {
										$sum = -1 * $sum;
									}
									$data[$field['id']] = $sum;
									$format[] = '%d';
									$validation->is_valid( $_POST[$field['id'].'_major'], array( 'type' => 'numbers' ) );
									$validation->is_valid( $_POST[$field['id'].'_minor'], array( 'type' => 'numbers' ) );
								break;

								case 'date':
									$sanitized = $validation->is_date( $_POST[$field['id']] );
									$data[$field['id']] = false === $sanitized ? strftime( '%d.%m.%Y', time() ) : $sanitized;
									$format[] = '%d';
								break;

								case 'radio':
									if ( 'cash' === $field['id'] ) {
										$data[$field['id']] = $_POST[$field['id']];
										$format[] = '%d';
									} elseif ( 'direction' !== $field['id'] ) {
										$data[$field['id']] = $_POST[$field['id']];
										$format[] = '%s';
									}
								break;

								case 'text':
									if ( 'meta_3' === $field['id'] ) {
										if ( ! empty( $_POST[$field['id']] ) ) {
											$data[$field['id']] = $_POST[$field['id']];
											$format[] = '%s';
										}
									} else{
										$data[$field['id']] = $_POST[$field['id']];
										$format[] = '%s';
									}
								break;

								case 'note':
									// do nothing
								break;

								case 'hidden':
									if ( 'receipt_status' === $field['id'] ) {
										if ( $insert ) {
											if (
												'city' === $this->cap_lvl &&
												'expenditure' === $type
											) {
												$data[$field['id']] = 1;
											} elseif (
												'city' === $this->cap_lvl &&
												(
													'transfer' === $type ||
													(
														'donation' === $type &&
														isset( $_POST['cash'] ) &&
														(
															0 === $_POST['cash'] ||
															'0' === $_POST['cash']
														)
													)
												)
											) {
												$data[$field['id']] = 2;
											} elseif (
												'city' !== $this->cap_lvl &&
												(
													(
														'transfer' === $type &&
														(
															(
																isset( $_POST['direction'] ) &&
																0 == $_POST['direction']
															) ||
															'donations' === $acc_type
														)
													)
													||
													'expenditure' === $type
												)
											) {
												$data[$field['id']] = 3;
												$format[] = '%d';
											};
										}
									} elseif ( 'direction' !== $field['id'] ) {
										$data[$field['id']] = $_POST[$field['id']];
										$format[] = '%d';
									}
								break;

								default:
									$data[$field['id']] = $_POST[$field['id']];
									$format[] = '%s';
							}
							if ( $field['type'] !== 'cash_amount' && isset( $field['validation'] ) ) {
								$validation->is_valid( $_POST[$field['id']], array( 'type' => $field['validation'], 'id' => $field['id'] ) );
							}
						}
					}

					if ( ! $validation->has_errors ) {
						if ( $insert ) {
							$id = $wpdb->insert(
								$wpdb->prefix . 'vca_asm_finances_transactions',
								$data,
								$format
							);
							if ( ! empty( $_POST['receipt_id'] ) ) {
								$wpdb->update(
									$wpdb->prefix . 'vca_asm_finances_accounts',
									array( 'last_receipt' => $_POST['receipt_id'] ),
									array( 'city_id' => $cid, 'type' => $acc_type ),
									array( '%s' ),
									array( '%d', '%s' )
								);
							}
						} else {
							$id = $_GET['id'];
							$wpdb->update(
								$wpdb->prefix . 'vca_asm_finances_transactions',
								$data,
								array( 'id' => $id ),
								$format,
								array( '%d' )
							);
						}
						header( 'Location: ' . strtok( $_SERVER['REQUEST_URI'], '?' ) . '?page=' . $page . '&todo=saved&id=' . $id . '&type=' . $type . '&tab=' . $active_tab . '&cid=' . $cid );
					} else {
						$id = $_GET['id'];
						$_SESSION['the_post'] = array_merge( $_POST );
						header( 'Location: ' . strtok( $_SERVER['REQUEST_URI'], '?' ) . '?page=' . $page . '&todo=errors&id=' . $id . '&type=' . $type . '&tab=' . $active_tab . '&cid=' . $cid . '&errors=' . serialize( $validation->errors ) );
					}
				break;

				case "errors":
						$_POST = isset( $_SESSION['the_post'] ) ? array_merge( $_POST, $_SESSION['the_post'] ) : $_POST;
						$messages = array_merge( $messages, $validation->set_errors( false, unserialize( stripslashes( $_GET['errors'] ) ) ) );
						unset( $_SESSION['the_post'] );
						$this->edit( array(
							'id' => $id,
							'active_tab' => $active_tab,
							'type' => $type,
							'city_id' => $cid,
							'account_type' => $acc_type,
							'page' => $page,
							'messages' => $messages,
							'populate' => true
						));
				break;

				case "saved":
					$messages[] = array(
						'type' => 'message',
						'message' => sprintf( __( '%s saved.', 'vca-asm' ), $vca_asm_finances->types_to_nicenames[$type] )
					);
					$this->single_view( array(
						'messages' => $messages,
						'account_type' => $acc_type,
						'city_id' => $cid,
						'page' => $page,
						'type' => $type,
						'active_tab' => $active_tab
					));
				break;

				case "confirm-receipt":
					if ( isset( $_GET['step'] ) ) {
						switch ( $_GET['step'] ) {
							case 1:
								$wpdb->update(
									$wpdb->prefix . 'vca_asm_finances_transactions',
									array( 'receipt_status' => 2 ),
									array( 'id' => $_GET['id'] ),
									array( '%d' ),
									array( '%d' )
								);
								$messages[] = array(
									'type' => 'message',
									'message' => __( 'Receipt sent to the office.', 'vca-asm' )
								);
							break;

							case 2:
								$wpdb->update(
									$wpdb->prefix . 'vca_asm_finances_transactions',
									array( 'receipt_status' => 3 ),
									array( 'id' => $_GET['id'] ),
									array( '%d' ),
									array( '%d' )
								);
								$messages[] = array(
									'type' => 'message',
									'message' => __( 'Reception confirmed.', 'vca-asm' )
								);
							break;
						}
					}
					$this->single_view( array(
						'messages' => $messages,
						'account_type' => $acc_type,
						'city_id' => $cid,
						'page' => $page,
						'type' => $type,
						'active_tab' => $active_tab
					));
				break;

				case "unconfirm-receipt":
					if ( isset( $_GET['step'] ) ) {
						switch ( $_GET['step'] ) {
							case 2:
								$wpdb->update(
									$wpdb->prefix . 'vca_asm_finances_transactions',
									array( 'receipt_status' => 1 ),
									array( 'id' => $_GET['id'] ),
									array( '%d' ),
									array( '%d' )
								);
								$messages[] = array(
									'type' => 'message',
									'message' => __( 'Receipt was not sent yet.', 'vca-asm' )
								);
							break;

							case 3:
								$wpdb->update(
									$wpdb->prefix . 'vca_asm_finances_transactions',
									array( 'receipt_status' => 2 ),
									array( 'id' => $_GET['id'] ),
									array( '%d' ),
									array( '%d' )
								);
								$messages[] = array(
									'type' => 'message',
									'message' => __( 'Reception confirmation canceled.', 'vca-asm' )
								);
							break;
						}
					}
					$this->single_view( array(
						'messages' => $messages,
						'account_type' => $acc_type,
						'city_id' => $cid,
						'page' => $page,
						'type' => $type,
						'active_tab' => $active_tab
					));
				break;

				case "edit":
					$this->edit( array(
						'id' => $_GET['id'],
						'active_tab' => $active_tab,
						'city_id' => $cid,
						'account_type' => $acc_type,
						'page' => $page
					));
				break;

				case "new":
					$this->edit( array(
						'city_id' => $cid,
						'active_tab' => $active_tab,
						'account_type' => $acc_type,
						'type' => $type,
						'page' => $page
					));
				break;

				default:
					$this->single_view( array(
						'messages' => $messages,
						'account_type' => $acc_type,
						'city_id' => $cid,
						'page' => $page,
						'type' => $type,
						'active_tab' => $active_tab
					));
			}
		} else {
			if ( in_array( $this->cap_lvl, array( 'nation', 'global' ) ) && ! isset( $_GET['cid'] ) ) {
				$this->accounts_list(
					array(
						'account_type' => $acc_type,
						'page' => $page
					)
				);
			} else {
				$this->single_view(
					array(
						'city_id' => $cid,
						'account_type' => $acc_type,
						'type' => $type,
						'page' => $page
					)
				);
			}
		}
	}

	/**
	 * A single account overview
	 *
	 * @since 1.0
	 * @access public
	 */
	public function accounts_list( $args = array() )
	{
		global $current_user,
			$vca_asm_finances, $vca_asm_geography;

		$default_args = array(
			'account_type' => 'donations',
			'messages' => array(),
			'page' => 'vca-asm-finances-accounts-donations'
		);
		$args = wp_parse_args( $args, $default_args );
		extract( $args );

		if ( 'donations' === $account_type ) {
			$title = _x( 'Finances', 'Admin Menu', 'vca-asm' ) . ' | ' . _x( 'Donation Accounts', ' Admin Menu', 'vca-asm' );
		} else {
			$title = _x( 'Finances', 'Admin Menu', 'vca-asm' ) . ' | ' . _x( 'Economical Accounts', ' Admin Menu', 'vca-asm' );
		}

		$nation_id = 'global' === $this->cap_lvl ? 0 : $this->admin_nation;
		$accounts = $vca_asm_finances->get_accounts( $account_type, $nation_id, true, true );

		$adminpage = new VCA_ASM_Admin_Page( array(
			'icon' => 'icon-finances',
			'title' => $title,
			'messages' => $messages,
			'url' => '?page=' . $page
		));

		$i = 0;
		$rows = array();
		foreach ( $accounts as $account ) {
			$the_city_finances = new VCA_ASM_City_Finances( $account['city_id'] );
			$rows[$i] = $account;
			$rows[$i]['balanced_month'] = $the_city_finances->{'balanced_month_' . ( 'donations' === $account_type ? 'don' : 'econ' ) . '_name'};
			$rows[$i]['annual_out'] = 'donations' === $account_type ? 0 : $the_city_finances->econ_annual_expenses_formatted;
			$rows[$i]['annual_in'] = 'donations' === $account_type ? $the_city_finances->donations_current_year_formatted : $the_city_finances->econ_annual_revenue_formatted;
			$i++;
		}

		$columns = array(
			array(
				'id' => 'name',
				'title' => __( 'City', 'vca-asm' ),
				'sortable' => false,//true,
				'link' => array(
					'title' => __( 'View %s', 'vca-asm' ),
					'title_row_data' => 'name',
					'url' => '?page=' . $page . '&cid=%d',
					'url_row_data' => 'city_id'
				),
				'actions' => array( 'view_account' ),
				'cap' => 'view_finances'
			),
			array(
				'id' => 'balance',
				'title' => __( 'Balance', 'vca-asm' ),
				'sortable' => false,//true,
				'conversion' => 'balance'
			),
			array(
				'id' => 'balanced_month',
				'title' => __( 'Balanced until', 'vca-asm' ),
				'sortable' => false//true
			)
		);

		if ( 'donations' === $account_type ) {
			$columns[] = array(
				'id' => 'annual_in',
				'title' => sprintf( __( 'Donations', 'vca-asm' ) . '  %d', date( 'Y' ) ),
				'sortable' => false//true
			);
		} else {
			$columns[] = array(
				'id' => 'annual_in',
				'title' => sprintf( __( 'Revenues', 'vca-asm' ) . '  %d', date( 'Y' ) ),
				'sortable' => false//true
			);
			$columns[] = array(
				'id' => 'annual_out',
				'title' => sprintf( __( 'Expenditures', 'vca-asm' ) . '  %d', date( 'Y' ) ),
				'sortable' => false//true
			);
		}

		$the_table = new VCA_ASM_Admin_Table(
			array(
				'echo' => false,
				'orderby' => 'name',
				'order' => 'ASC',
				'toggle_order' => 'DESC',
				'page_slug' => $page,
				'base_url' => '',
				'sort_url' => '',
				'show_empty_message' => true,
				'empty_message' => ''
			),
			$columns,
			$rows
		);

		$output = $adminpage->top();

		$output .= $the_table->output();

		$output .= $adminpage->bottom();

		echo $output;
	}

	/**
	 * A single account overview
	 *
	 * @since 1.0
	 * @access public
	 */
	public function single_view( $args = array() )
	{
		global $current_user,
			$vca_asm_finances, $vca_asm_geography;

		$default_args = array(
			'city_id' => 0,
			'account_type' => 'donations',
			'type' => 'donation',
			'messages' => array(),
			'active_tab' => 'all',
			'page' => 'vca-asm-finances-accounts-donations'
		);
		$args = wp_parse_args( $args, $default_args );
		extract( $args );

		$back = in_array( $this->cap_lvl, array( 'global', 'nation' ) ) ? true : false;

		$donation_tabs = array( 'all', 'donation', 'transfer' );
		$econ_tabs = array( 'all', 'revenue', 'expenditure', 'transfer' );
		$possible_tabs = 'donations' === $account_type ? $donation_tabs : $econ_tabs;
		if ( isset( $_GET['tab'] ) && in_array( $_GET['tab'], $possible_tabs ) ) {
			$active_tab = $_GET['tab'];
		} elseif ( ! in_array( $active_tab, $possible_tabs ) ) {
			$active_tab = 'all';
		}

		$output = '';

		$city_name = $vca_asm_geography->get_name( $city_id );
		$tabs = array(
			array(
				'title' => _x( 'All entries', ' Admin Menu', 'vca-asm' ),
				'value' => 'all',
				'icon' => 'icon-summary'
			)
		);
		if ( 'donations' === $account_type ) {
			$title = sprintf( _x( 'Donation Account of %s', ' Admin Menu', 'vca-asm' ), $city_name );
			$tabs[] = array(
				'title' => _x( 'Donations', ' Admin Menu', 'vca-asm' ),
				'value' => 'donation',
				'icon' => 'icon-finances'
			);
		} else {
			$title = sprintf( _x( 'Economical Account of %s', ' Admin Menu', 'vca-asm' ), $city_name );
			$tabs[] = array(
				'title' => _x( 'Revenues', ' Admin Menu', 'vca-asm' ),
				'value' => 'revenue',
				'icon' => 'icon-revenue'
			);
			$tabs[] = array(
				'title' => _x( 'Expenditures', ' Admin Menu', 'vca-asm' ),
				'value' => 'expenditure',
				'icon' => 'icon-expenditure'
			);
		}
		$tabs[] = array(
			'title' => _x( 'Transfers', ' Admin Menu', 'vca-asm' ),
			'value' => 'transfer',
			'icon' => 'icon-transfer'
		);
		$button = '';

		if ( $this->has_cap ) {
			if ( $active_tab !== 'all' ) {
				$button .= '<form method="post" action="admin.php?page=' . $page . '&todo=new&tab=' . $active_tab . '&type=' . $active_tab . '&acc_type=' . $account_type . '&cid=' . $city_id . '">' .
					'<input type="submit" class="button-secondary" value="+ ' . sprintf( __( 'add %s', 'vca-asm' ), $vca_asm_finances->types_to_nicenames[$tab] ) . '" />' .
				'</form>';
			} else {
				$button .= '<div>';
				foreach ( $possible_tabs as $tab ) {
					if ( 'all' !== $tab ) {
						$button .= '<form method="post" style="display:inline" action="admin.php?page=' . $page . '&todo=new&tab=' . $active_tab . '&type=' . $tab . '&acc_type=' . $account_type . '&cid=' . $city_id . '">' .
							'<input type="submit" class="button-secondary margin" value="+ ' . sprintf( __( 'add %s', 'vca-asm' ), $vca_asm_finances->types_to_nicenames[$tab] ) . '" />' .
						'</form>';
					}
				}
				$button .= '</div>';
			}
		}

		$list_args = array(
			'city_id' => $city_id,
			'account_type' => $account_type,
			'transaction_type' => $active_tab,
			'page' => $page,
			'active_tab' => $active_tab
		);

		$back_url = '?page=' . $page;
		if ( isset( $_GET['referrer'] ) ) {
			switch ( $_GET['referrer'] ) {
				case 'overview-summary':
					$back_url = '?page=vca-asm-finances&tab=summary';
				break;

				case 'overview-tabular':
					$back_url = '?page=vca-asm-finances&tab=tabular';
				break;

				case 'overview-city':
					$back_url = '?page=vca-asm-finances';
					if ( ! empty( $_GET['cid'] ) ) {
						$back_url .= '&cid=' . $_GET['cid'];
					}
				break;

				case 'overview-cities': // deprecated
					$back_url = '?page=vca-asm-finances&tab=cities';
				break;

				case 'tasks':
					$back_url = '?page=vca-asm-home&tab=tasks';
				break;

				default:
					$back_url = '?page=' . $page;
				break;
			}
		}

		$adminpage = new VCA_ASM_Admin_Page( array(
			'icon' => 'icon-finances',
			'title' => $title,
			'messages' => $messages,
			'url' => '?page=' . $page . '&cid=' . $city_id,
			'back' => $back,
			'back_url' => $back_url,
			'tabs' => $tabs,
			'active_tab' => $active_tab
		));

		$output .= $adminpage->top();

		$output .= '<br />' . $button . '<br />';

		$output .= $this->list_entries( $list_args );

		$output .= '<br />' . $button;

		$output .= $adminpage->bottom();

		echo $output;
	}

	/**
	 * Lists entries of a single account
	 *
	 * @since 1.5
	 * @access private
	 */
	private function list_entries( $args = array() )
	{
		global $current_user,
			$vca_asm_finances, $vca_asm_geography, $vca_asm_utilities;

		$default_args = array(
			'city_id' => 0,
			'account_type' => 'donations',
			'transaction_type' => 'all',
			'page' => 'vca-asm-finances-accounts-donations',
			'active_tab' => 'all'
		);
		$args = wp_parse_args( $args, $default_args );
		extract( $args );

		$admin_nation = get_user_meta( $current_user->ID, 'nation', true );

		$url = '?page=' . $page;

		extract( $vca_asm_utilities->table_order() );

		$columns = array(
			array(
				'id' => 'transaction_date',
				'title' => __( 'Date', 'vca-asm' ),
				'sortable' => false,//true
			),
			array(
				'id' => 'amount',
				'title' => __( 'Amount', 'vca-asm' ),
				'sortable' => false,//true,
				'link' => array(
					'title' => __( 'Edit %s', 'vca-asm' ),
					'title_row_data' => 'name',
					'url' => '?page=' . $page . '&todo=edit&id=%d&cid=' . $city_id,
					'url_row_data' => 'id'
				),
				'actions' => array( 'edit-transaction', 'delete-transaction' ),
				'cap' => 'edit-transaction',
				'conversion' => 'amount'
			)
		);

		if (
			(
				! in_array( $transaction_type, $vca_asm_finances->donations_transactions ) &&
				! in_array( $transaction_type, $vca_asm_finances->econ_transactions )
			) ||
			'donation' === $transaction_type ||
			( 'transfer' === $transaction_type && 'donations' === $account_type )
		) {
			$columns[] = array(
				'id' => 'transaction_type',
				'title' => __( 'Type', 'vca-asm' ),
				'sortable' => false//true
			);
		}

		if (
			'expenditure' === $transaction_type ||
			(
				! in_array( $transaction_type, $vca_asm_finances->econ_transactions ) &&
				'econ' === $account_type
			)
		) {
			$columns[] = array(
				'id' => 'receipt',
				'title' => __( 'Receipt', 'vca-asm' ),
				'sortable' => false,//true,
				'conversion' => 'receipt'
			);
		}

		if (
			in_array( $transaction_type, array( 'expenditure', 'transfer' ) ) ||
			(
				! in_array( $transaction_type, $vca_asm_finances->econ_transactions ) &&
				'econ' === $account_type
			) ||
			(
				'all' === $transaction_type &&
				'donations' === $account_type
			)
		) {
			$columns[] = array(
				'id' => 'status',
				'title' => __( 'Status', 'vca-asm' ),
				'sortable' => false,//true,
				'actions' => array( 'confirm-receipt', 'unconfirm-receipt' ),
				'cap' => array( 'confirm-receipt', 'unconfirm-receipt' ),
				'conversion' => 'receipt-status'
			);
		}

		if (
			in_array( $transaction_type, array( 'expenditure', 'revenue', 'donation' ) ) ||
			(
				! in_array( $transaction_type, $vca_asm_finances->donations_transactions ) &&
				! in_array( $transaction_type, $vca_asm_finances->econ_transactions )
			)
		) {
			$columns[] = array(
				'id' => 'meta_1',
				'title' => __( 'Occasion', 'vca-asm' ),
				'sortable' => false
			);
		}

		if (
			'donation' === $transaction_type ||
			( 'transfer' === $transaction_type && 'donations' === $account_type )
		) {
			$columns[] = array(
				'id' => 'meta_3',
				'title' => __( 'From whom?', 'vca-asm' ),
				'sortable' => false,//true,
				'conversion' => 'empty-to-dashes'
			);
		}

		$transactions = $vca_asm_finances->get_transactions( array(
			'city_id' => $city_id,
			'account_type' => $account_type,
			'transaction_type' => $transaction_type,
			'orderby' => 'transaction_date',
			'order' => 'DESC'
		));

		if ( 'transfer' === $transaction_type && 'donations' === $account_type ) {
			$more_transactions = $vca_asm_finances->get_transactions( array(
				'city_id' => $city_id,
				'account_type' => $account_type,
				'transaction_type' => 'donation',
				'orderby' => 'transaction_date',
				'order' => 'DESC'
			));
			$to_merge_transactions = array();
			foreach ( $more_transactions as $more_transaction ) {
				if ( 0 === $more_transaction['cash'] || '0' === $more_transaction['cash'] ) {
					$to_merge_transactions[] = $more_transaction;
				}
			}
			$merged_transactions = array_merge( $transactions, $to_merge_transactions );
			$transactions = $vca_asm_utilities->sort_by_key( $merged_transactions, 'transaction_date', 'DESC' );
		}

		$transaction_count = count( $transactions );
		if ( $transaction_count > $this->per_page ) {
			$cur_page = isset( $_GET['p'] ) ? $_GET['p'] : 1;
			$pagination_offset = $this->per_page * ( $cur_page - 1 );
			$total_pages = ceil( $transaction_count / $this->per_page );
			$cur_end = $total_pages == $cur_page ? $pagination_offset + ( $transaction_count % $this->per_page ) : $pagination_offset + $this->per_page;

			$pagination_args = array(
				'pagination' => true,
				'total_pages' => $total_pages,
				'current_page' => $cur_page
			);
		} else {
			$cur_page = 1;
			$pagination_offset = 0;
			$cur_end = $transaction_count;
			$pagination_args = array( 'pagination' => false );
		}

		$the_city_finances = new VCA_ASM_City_Finances( $city_id );
		$rows = array();
		$i = 0;
		foreach ( $transactions as $transaction ) {
			$rows[$i] = $transaction;
			$rows[$i]['transaction_type_plain'] = $transaction['transaction_type'];

			$rows[$i]['editable'] = ( $the_city_finances->{'balanced_month_'. ( $account_type === 'donations' ? 'don' : $account_type ) .'_threshold_stamp'} < $transaction['transaction_date'] ) ? 1 : 0;

			if ( 'donation' !== $transaction_type ) {
				$rows[$i]['transaction_type'] = $vca_asm_finances->types_to_nicenames[$transaction['transaction_type']];
				$rows[$i]['transaction_type'] .= ( 1 == $transaction['cash'] ) ? ' (' . __( 'cash', 'vca-asm' ) . ')' : ( ( 'donation' == $transaction['transaction_type'] ) ? ' (' . __( 'external transfer', 'vca-asm' ) . ')' : '' );
				$rows[$i]['transaction_type'] .= ( 'transfer' === $transaction['transaction_type'] && 'econ' === $account_type ) ? ( 0 < $transaction['amount'] ? ' (' . __( 'to city', 'vca-asm' ) . ')' : ' (' . __( 'to office', 'vca-asm' ) . ')' ) : '';
			} else {
				$rows[$i]['transaction_type'] = ( 1 == $transaction['cash'] ) ? __( 'Cash money', 'vca-asm' ) : __( 'external transfer', 'vca-asm' );
			}

			$rows[$i]['entry_time_plain'] = $transaction['entry_time'];
			$rows[$i]['entry_time'] = strftime( '%d.%m.%Y %H:%M', $transaction['entry_time'] );

			$rows[$i]['transaction_date_plain'] = $transaction['transaction_date'];
			$rows[$i]['transaction_date'] = strftime( '%d.%m.%Y', $transaction['transaction_date'] );

			$rows[$i]['amount_plain'] = $transaction['amount'];
			$rows[$i]['amount'] = number_format( $transaction['amount']/100, 2, ',', '.' );
			$rows[$i]['receipt'] = ! empty( $transaction['receipt_id'] ) ? $transaction['receipt_id'] : '---';

			switch ( $transaction['receipt_status'] ) {
				case 1:
				case '1':
					if ( 'expenditure' === $transaction['transaction_type'] ) {
						$rows[$i]['status'] = __( 'Receipt not sent', 'vca-asm' );
					} else {
						$rows[$i]['status'] = __( 'Unconfirmed', 'vca-asm' );
					}
				break;

				case 2:
				case '2':
					if ( 'expenditure' === $transaction['transaction_type'] ) {
						$rows[$i]['status'] = __( 'Receipt sent', 'vca-asm' );
					} else {
						$rows[$i]['status'] = __( 'Unconfirmed', 'vca-asm' );
					}
				break;

				case 3:
				case '3':
					$rows[$i]['status'] = __( 'Confirmed', 'vca-asm' );
				break;

				case 0:
				case '0':
				default:
					$rows[$i]['status'] = '---';
				break;
			}
			$i++;
		}

		$tbl_args = array(
			'base_url' => '?page=' . $page . '&tab=' . $active_tab,
			'sort_url' => '?page=' . $page . '&tab=' . $active_tab,
			'echo' => false,
			'orderby' => 'transaction_date_plain',
			'order' => 'DESC'
		);
		$tbl_args = array_merge( $tbl_args, $pagination_args );

		$the_table = new VCA_ASM_Admin_Table( $tbl_args, $columns, $rows );
		return $the_table->output();
	}

	/**
	 * Edit a transaction
	 *
	 * @since 1.5
	 * @access public
	 */
	public function edit( $args = array() )
	{
		global $current_user, $vca_asm_finances;

		$default_args = array(
			'id' => NULL,
			'type' => 'donation',
			'active_tab' => 'all',
			'city_id' =>  0,
			'account_type' => 'donations',
			'messages' => array(),
			'page' => 'vca-asm-finances-accounts-donations',
			'populate' => false
		);
		extract( wp_parse_args( $args, $default_args ), EXTR_SKIP );

		wp_enqueue_script( 'vca-asm-admin-validation' );
		$validation_params = array(
			'errors' => array(
				'required' => _x( 'You have not filled out all the required fields', 'Validation Error', 'vca-asm' ),
				'numbers' => _x( 'Some fields only accept numeric values', 'Validation Error', 'vca-asm' ),
				'phone' => _x( 'A phone number you have entered is not valid', 'Validation Error', 'vca-asm' ),
				'end_app' => _x( 'The end of the application phase must come after its beginning', 'Validation Error', 'vca-asm' ),
				'date' => _x( 'Some of the entered dates are in an invalid order', 'Validation Error', 'vca-asm' )
			)
		);
		wp_localize_script( 'vca-asm-admin-validation', 'validationParams', $validation_params );

		$url = '?page=' . $page . '&acc_type=' . $account_type;
		if ( ! empty( $id ) ) {
			$type = $vca_asm_finances->get_transaction_type( $id );
			$form_action = $url . '&todo=save&noheader=true&id=' . $id . '&type=' . $type . '&tab=' . $active_tab . '&cid=' . $city_id;
		} else {
			$form_action = $url . '&todo=save&noheader=true&type=' . $type . '&tab=' . $active_tab . '&cid=' . $city_id;
		}

		if( empty( $id ) && ! $populate ) {
			$fields = $this->create_fields( $account_type.'-'.$type, 0, $city_id );
			$title = sprintf( __( 'Add New %s', 'vca-asm' ), $vca_asm_finances->types_to_nicenames[$type] );
			$transaction_city = ! empty( $_GET['cid'] ) ? $_GET['cid'] : 0;
		} else {
			if( ! empty( $id ) ) {
				$fields = $this->populate_fields( $id );
				$title = sprintf( __( 'Edit %s', 'vca-asm' ), $vca_asm_finances->types_to_nicenames[$type] );
				$type = $vca_asm_finances->get_transaction_type( $id, false );
				$transaction_city = $vca_asm_finances->get_transaction_city( $id );
			} else {
				$fields = $this->populate_fields( false, $account_type.'-'.$type );
				$title = sprintf( __( 'Add New %s', 'vca-asm' ), $vca_asm_finances->types_to_nicenames[$type] );
				$transaction_city = ! empty( $_GET['cid'] ) ? $_GET['cid'] : 0;
			}
		}

		if( empty( $id ) && $this->has_cap ) {
			$extra_html = '<ul class="horiz-list">';
			if ( 'donations' === $account_type ) {
					$extra_html .= '<li>' .
						__( 'Add new', 'vca-asm' ) . ': ' .
					'</li>' .
					'<li>' .
						'<a title="' . __( 'New Donation', 'vca-asm' ) . '" ' .
						'href="admin.php?page=' . $page . '&todo=new&type=donation&acc_type=donations">' .
							__( 'Donation', 'vca-asm' ) .
						'</a>' .
					'</li>' .
					'<li>' .
						'<a title="' . __( 'New Transfer', 'vca-asm' ) . '" ' .
						'href="admin.php?page=' . $page . '&todo=new&type=transfer&acc_type=donations">' .
							__( 'Transfer', 'vca-asm' ) .
						'</a>' .
					'</li>';
			} else {
					$extra_html .= '<li>' .
						__( 'Add new', 'vca-asm' ) . ': ' .
					'</li>' .
					'<li>' .
						'<a title="' . __( 'New Revenue', 'vca-asm' ) . '" ' .
						'href="admin.php?page=' . $page . '&todo=new&type=revenue&acc_type=econ">' .
							__( 'Revenue', 'vca-asm' ) .
						'</a>' .
					'</li>' .
					'<li>' .
						'<a title="' . __( 'New Expenditure', 'vca-asm' ) . '" ' .
						'href="admin.php?page=' . $page . '&todo=new&type=expenditure&acc_type=econ">' .
							__( 'Expenditure', 'vca-asm' ) .
						'</a>' .
					'</li>' .
					'<li>' .
						'<a title="' . __( 'New Transfer', 'vca-asm' ) . '" ' .
						'href="admin.php?page=' . $page . '&todo=new&type=transfer&acc_type=econ">' .
							__( 'Transfer', 'vca-asm' ) .
						'</a>' .
					'</li>';
			}
			$extra_html .= '</ul><br />';
		}

		if (
			$this->has_cap &&
			(
				empty( $id ) ||
				$this->cap_lvl === 'global' ||
				( $this->cap_lvl === 'nation' /*&& $admin_nation === $vca_asm_geography->has_nation( $transaction_city )*/ ) ||
				( $this->cap_lvl === 'city' && $this->admin_city === $transaction_city )
			)
		) {
			$has_cap = true;
		} else {
			$has_cap = false;
			$messages[] = array(
				'type' => 'error',
				'message' => __( 'You do not have the rights to edit this. Sorry.', 'vca-asm' )
			);
		}

		$the_page = new VCA_ASM_Admin_Page( array(
			'echo' => true,
			'icon' => 'icon-finances',
			'title' => $title,
			'url' => $url,
			'extra_head_html' => '',
			'messages' => $messages,
			'back' => false
		));

		$the_page->top();
		if ( $has_cap ) {
			$form_args = array(
				'echo' => true,
				'form' => true,
				'metaboxes' => true,
				'action' => $form_action,
				'id' => $id,
				'back' => true,
				'back_url' => $url . '&tab=' . $active_tab . '&cid=' . $transaction_city,
				'button_id' => 'submit-validate',
				'fields' => $fields
			);
			$the_form = new VCA_ASM_Admin_Form( $form_args );
			$the_form->output();
		}
		$the_page->bottom();
	}

	/**
	 * Returns an array of fields for a transactions
	 *
	 * @since 1.5
	 * @access private
	 */
	private function create_fields( $type = 'donations-donation', $nation = 0, $city = 0 ) {
		global $current_user,
			$vca_asm_finances, $vca_asm_geography;

		$nation = empty( $nation ) ? $this->admin_nation : $nation;
		$city = empty( $city ) ? $this->admin_city : $city;

		$currency_major = $vca_asm_geography->get_currency( $nation, 'name' );
		$currency_minor = $vca_asm_geography->get_currency( $nation, 'minor_name' );

		switch ( $type ) {
			case 'donations-donation':
				wp_enqueue_script( 'vca-asm-admin-finances' );
				$fields = array(
					array(
						'title' => __( 'The Donation', 'vca-asm' ),
						'fields' => array(
							array(
								'type' => 'cash_amount',
								'label' => __( 'Amount', 'vca-asm' ),
								'id' => 'amount',
								'currency_major' => $currency_major,
								'currency_minor' => $currency_minor,
								'desc' => __( 'How much was gathered?', 'vca-asm' ),
								'validation' => 'cash_amount',
								'required' => true
							),
							array(
								'type' => 'date',
								'label' => __( 'Date', 'vca-asm' ),
								'id' => 'transaction_date',
								'desc' => __( 'When did you receive the donation?', 'vca-asm' ),
								'validation' => 'date',
								'required' => true,
								'maxdate' => time()
							),
							array(
								'type' => 'radio',
								'label' => __( 'Category of activity', 'vca-asm' ),
								'id' => 'meta_2',
								'options' => $vca_asm_finances->occasions_options_array( array(
									'nocat' => true,
									'nation' => $nation,
									'appended_id' => 'value'
								)),
								'required' => true,
								'desc' => __( 'How can the activity be categorized?', 'vca-asm' )
							),
							array(
								'type' => 'text',
								'label' => __( 'Name of activity', 'vca-asm' ),
								'id' => 'meta_1',
								'desc' => __( 'What was the name of the activity the donation was gathered at?', 'vca-asm' ) . ' (' . __( 'Name of the concert, festival or party, for instance', 'vca-asm' ) . ')',
								'validation' => 'required',
								'class' => 'required',
								'required' => true
							),
							array(
								'type' => 'radio',
								'label' => __( 'Cash flow', 'vca-asm' ),
								'id' => 'cash',
								'desc' => __( 'Did you receive cash or was the donation transferred to the office directly?', 'vca-asm' ),
								'options' => array(
									array(
										'label' => __( 'Cash', 'vca-asm' ),
										'value' => 1
									),
									array(
										'label' => __( 'Donation was transferred', 'vca-asm' ),
										'value' => 0
									)
								),
								'value' => 1,
								'validation' => 'required',
								'required' => true
							),
							array(
								'type' => 'text',
								'label' => __( 'From whom?', 'vca-asm' ),
								'id' => 'meta_3',
								'desc' => __( 'Who will transfer the donation?', 'vca-asm' ) . ' (' . __( 'Enter the name of the transferring (legal) party; The company name of a venue for instance.', 'vca-asm' ) . ')',
								'class' => 'required'
							),
							array(
								'type' => 'hidden',
								'id' => 'receipt_status',
								'value' => 2
							)
						)
					)
				);
			break;

			case 'econ-revenue':
				$fields = array(
					array(
						'title' => __( 'The Revenue', 'vca-asm' ),
						'fields' => array(
							array(
								'type' => 'cash_amount',
								'label' => __( 'Amount', 'vca-asm' ),
								'id' => 'amount',
								'currency_major' => $currency_major,
								'currency_minor' => $currency_minor,
								'desc' => __( 'How much was gained?', 'vca-asm' ),
								'validation' => 'cash_amount',
								'required' => true
							),
							array(
								'type' => 'date',
								'label' => __( 'Date', 'vca-asm' ),
								'id' => 'transaction_date',
								'desc' => __( 'When did you gain the revenue?', 'vca-asm' ),
								'validation' => 'date',
								'required' => true,
								'maxdate' => time()
							),
							array(
								'type' => 'text',
								'label' => __( 'Item(s)', 'vca-asm' ),
								'id' => 'meta_4',
								'desc' => __( 'How did you gain the income?', 'vca-asm' ) . ' ' . __( 'What did you sell?', 'vca-asm' ),
								'validation' => 'required',
								'class' => 'required',
								'required' => true
							),
							array(
								'type' => 'radio',
								'label' => __( 'Kind of revenue', 'vca-asm' ),
								'id' => 'ei_account',
								'options' => $vca_asm_finances->ei_options_array( array(
									'type' => 'income',
									'unclear' => true,
									'nation' => $nation
								)),
								'required' => true,
								'desc' => __( 'Of what category is this revenue?', 'vca-asm' ),
								'validation' => 'required'
							),
							array(
								'type' => 'radio',
								'label' => __( 'Category of activity', 'vca-asm' ),
								'id' => 'meta_2',
								'options' => $vca_asm_finances->occasions_options_array( array(
									'nocat' => true,
									'nation' => $nation
								)),
								'required' => true,
								'desc' => __( 'How can the activity be categorized?', 'vca-asm' ),
								'validation' => 'required'
							),
							array(
								'type' => 'text',
								'label' => __( 'Name of activity', 'vca-asm' ),
								'id' => 'meta_1',
								'desc' => __( 'What was the name of the activity the money was earned at?', 'vca-asm' ) . ' (' . __( 'Name of the concert, festival or party, for instance', 'vca-asm' ) . ')',
								'validation' => 'required',
								'class' => 'required',
								'required' => true
							)
						)
					)
				);
			break;

			case 'econ-expenditure':
				$fields = array(
					array(
						'title' => __( 'The Expenditure', 'vca-asm' ),
						'fields' => array(
							array(
								'type' => 'cash_amount',
								'label' => __( 'Amount', 'vca-asm' ),
								'id' => 'amount',
								'currency_major' => $currency_major,
								'currency_minor' => $currency_minor,
								'desc' => __( 'How much was spent?', 'vca-asm' ),
								'validation' => 'cash_amount',
								'required' => true
							),
							array(
								'type' => 'date',
								'label' => __( 'Date', 'vca-asm' ),
								'id' => 'transaction_date',
								'desc' => __( 'When did you take the money from your cities structural account?', 'vca-asm' ),
								'validation' => 'date',
								'required' => true,
								'maxdate' => time()
							),
							array(
								'type' => 'date',
								'label' => __( 'Date of Receipt', 'vca-asm' ),
								'id' => 'receipt_date',
								'desc' => __( 'When was the money spent? (What is the date on the rerceipt?)', 'vca-asm' ),
								'validation' => 'date',
								'required' => true,
								'maxdate' => time()
							),
							array(
								'type' => 'note_hidden',
								'label' => __( 'Receipt ID', 'vca-asm' ),
								'id' => 'receipt_id',
								'value' => $vca_asm_finances->generate_receipt( $city ),
								'desc' => __( 'Please mark the receipt belonging to this expenditure with the above ID before sending it. Thanks!', 'vca-asm' )
							),
							array(
								'type' => 'radio',
								'label' => __( 'Kind of expense', 'vca-asm' ),//__( 'Expense Account', 'vca-asm' ),
								'id' => 'ei_account',
								'options' =>  $vca_asm_finances->ei_options_array( array(
									'type' => 'expense',
									'unclear' => true,
									'nation' => $nation
								)),
								'required' => true,
								'desc' => __( 'Of what category is this expenditure?', 'vca-asm' )//__( 'Under what category should this expense be booked?', 'vca-asm' )
							),
							array(
								'type' => 'text',
								'label' => __( 'Item(s)', 'vca-asm' ),
								'id' => 'meta_4',
								'desc' => __( 'What did you buy?', 'vca-asm' ),
								'validation' => 'required',
								'class' => 'required',
								'required' => true
							),
							array(
								'type' => 'radio',
								'label' => __( 'Tax Rate', 'vca-asm' ),
								'id' => 'meta_3',
								'options' => $vca_asm_finances->tax_options_array( array(
									'notax' => true,
									'nation' => $nation
								)),
								'required' => true,
								'desc' => __( 'How much revenue tax did you have top pay?', 'vca-asm' ) . ' (' . __( 'Check the receipt...', 'vca-asm' ) . ')',
								'default' => $vca_asm_finances->get_default_tax_rate( $nation )
							),
							array(
								'type' => 'radio',
								'label' => __( 'Category of activity', 'vca-asm' ),
								'id' => 'meta_2',
								'options' => $vca_asm_finances->occasions_options_array( array(
									'nocat' => true,
									'nation' => $nation
								)),
								'required' => true,
								'desc' => __( 'How can the activity be categorized?', 'vca-asm' )
							),
							array(
								'type' => 'text',
								'label' => __( 'Name of activity', 'vca-asm' ),
								'id' => 'meta_1',
								'desc' => __( 'What was the name of the activity the money was spent for?', 'vca-asm' ) . ' (' . __( 'Name of the concert, festival or party, for instance', 'vca-asm' ) . ')',
								'validation' => 'required',
								'class' => 'required',
								'required' => true
							),
							array(
								'type' => 'hidden',
								'id' => 'receipt_status',
								'value' => 1
							)
						)
					)
				);
			break;

			case 'donations-transfer':
				$fields = array(
					array(
						'title' => __( 'The Transfer', 'vca-asm' ),
						'fields' => array(
							array(
								'type' => 'cash_amount',
								'label' => __( 'Amount', 'vca-asm' ),
								'id' => 'amount',
								'currency_major' => $currency_major,
								'currency_minor' => $currency_minor,
								'desc' => __( 'How much did you transfer?', 'vca-asm' ),
								'validation' => 'cash_amount',
								'required' => true
							),
							array(
								'type' => 'date',
								'label' => __( 'Date', 'vca-asm' ),
								'id' => 'transaction_date',
								'desc' => __( 'When did you make the transfer?', 'vca-asm' ),
								'validation' => 'date',
								'required' => true,
								'maxdate' => time()
							),
							array(
								'type' => 'hidden',
								'id' => 'receipt_status',
								'value' => 2
							)
						)
					)
				);
			break;

			case 'econ-transfer':
				$fields = array(
					array(
						'title' => __( 'The Transfer', 'vca-asm' ),
						'fields' => array(
							array(
								'type' => 'cash_amount',
								'label' => __( 'Amount', 'vca-asm' ),
								'id' => 'amount',
								'currency_major' => $currency_major,
								'currency_minor' => $currency_minor,
								'desc' => __( 'How much did you transfer?', 'vca-asm' ),
								'validation' => 'cash_amount',
								'required' => true
							),
							array(
								'type' => 'date',
								'label' => __( 'Date', 'vca-asm' ),
								'id' => 'transaction_date',
								'desc' => __( 'When did you make the transfer?', 'vca-asm' ),
								'validation' => 'date',
								'required' => true,
								'maxdate' => time()
							),
							array(
								'type' => 'hidden',
								'id' => 'receipt_status',
								'value' => 2
							)
						)
					)
				);
				if ( in_array( $this->cap_lvl, array( 'global', 'nation' ) ) ) {
					$fields[0]['fields'][] = array(
						'type' => 'radio',
						'label' => __( 'Direction?', 'vca-asm' ),
						'id' => 'direction',
						'desc' => __( 'Was money transferred from a city to the office or the other way around?', 'vca-asm' ),
						'options' => array(
							array(
								'label' => __( 'from city to office', 'vca-asm' ),
								'value' => 0
							),
							array(
								'label' => __( 'from office to city', 'vca-asm' ),
								'value' => 1
							)
						)
					);
				} else {
					$fields[0]['fields'][] = array(
						'type' => 'hidden',
						'id' => 'direction',
						'value' => 0
					);
				}
			break;
		}

		return $fields;
	}

	/**
	 * Populates region fields with values
	 *
	 * @since 1.0
	 * @access private
	 */
	private function populate_fields( $id = false, $type = 'donations-donation' ) {
		global $current_user, $wpdb, $vca_asm_finances;

		if ( is_numeric( $id ) ) {
			$type = $vca_asm_finances->get_transaction_type( $id, true );
			/* fill fields with existing data */
			$data = $wpdb->get_results(
				"SELECT * FROM " .
				$wpdb->prefix . "vca_asm_finances_transactions " .
				"WHERE id = " . $id . " LIMIT 1", ARRAY_A
			);

			$data = $data[0];
		}

		$fields = $this->create_fields( $type );

		$bcount = count( $fields );
		for ( $i = 0; $i < $bcount; $i++ ) {
			$fcount = count( $fields[$i]['fields'] );
			for ( $j = 0; $j < $fcount; $j++ ) {
				if ( empty( $_POST['submitted'] ) && is_numeric( $id ) ) {
					if ( 'direction' === $fields[$i]['fields'][$j]['id'] ) {
						$fields[$i]['fields'][$j]['value'] = 0 > $data['amount'] ? 0 : 1;
					} else {
						$fields[$i]['fields'][$j]['value'] = $data[$fields[$i]['fields'][$j]['id']];
					}
				} else {
					if ( 'cash_amount' === $fields[$i]['fields'][$j]['type'] ) {
						$fields[$i]['fields'][$j]['value'] = isset( $_POST[$fields[$i]['fields'][$j]['id'].'_major'] ) ? intval( $_POST[$fields[$i]['fields'][$j]['id'].'_major'] ) * 100 : 0;
						if ( isset( $_POST[$fields[$i]['fields'][$j]['id'].'_minor'] ) ) {
							$fields[$i]['fields'][$j]['value'] += intval( $_POST[$fields[$i]['fields'][$j]['id'].'_minor'] );
						}
					} else {
						if ( isset( $_POST[$fields[$i]['fields'][$j]['id']] ) || ! empty( $fields[$i]['fields'][$j]['value'] ) ) {
							$fields[$i]['fields'][$j]['value'] = isset( $_POST[$fields[$i]['fields'][$j]['id']] ) ? $_POST[$fields[$i]['fields'][$j]['id']] : $fields[$i]['fields'][$j]['value'];
						}
					}
				}
			}
		}

		return $fields;
	}

	/******************** SETTINGS ********************/

	/**
	 *  Settings
	 *
	 * @since 1.5
	 * @access public
	 */
	public function settings_control()
	{
		global $current_user, $wpdb,
			$vca_asm_finances, $vca_asm_geography;

		$messages = array();

		if ( isset( $_GET['tab'] ) && in_array( $_GET['tab' ], array( 'general', 'cash-accs', 'cost-centers', 'ei-accs', 'tax-rates', 'occasions' ) ) ) {
			$active_tab = $_GET['tab'];
		} else {
			$active_tab = 'general';
		}

		if ( isset( $_GET['todo'] ) ) {
			switch ( $_GET['todo'] ) {

				case "delete":
					if ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) {
						$meta_nation = $vca_asm_finances->get_related_id( $_GET['id'] );
						$admin_nation = get_user_meta( $current_user->ID, 'nation', true );
						if (
							$this->has_cap &&
							(
								'global' === $this->cap_lvl ||
								( 'nation' === $this->cap_lvl && $meta_nation === $admin_nation )
							)
						) {
							$success = $wpdb->delete(
								$wpdb->prefix.'vca_asm_finances_meta',
								array( 'id'=> $_GET['id'] ),
								array( '%d' )
							);
							if ( $success ) {
								$messages[] = array(
									'type' => 'message',
									'message' => __( 'The selected metadata has been successfully deleted.', 'vca-asm' )
								);
							} else {
								$messages[] = array(
									'type' => 'error-pa',
									'message' => __( 'There was an error deleting this data. Sorry.', 'vca-asm' )
								);
							}
						} else {
							$messages[] = array(
								'type' => 'error-pa',
								'message' => __( 'You cannot delete this data. Sorry.', 'vca-asm' )
							);
						}
					} else {
						$messages[] = array(
							'type' => 'error-pa',
							'message' => __( 'There was an error deleting this data. Sorry.', 'vca-asm' )
						);
					}
					unset( $_GET['todo'], $_GET['id'] );
					$this->settings_view( $messages, $active_tab );
				break;

				case "save-general":
					$nations = $vca_asm_geography->get_all( 'name', 'ASC', 'nation' );

					foreach ( $nations as $nation ) {
						foreach( array( 'city', 'lc', 'cell' ) as $type ) {
							if ( isset( $_POST['limit-' . $type . '-'.$nation['id']] ) ) {
								$value_exists = $vca_asm_finances->get_limit( $nation['id'], $type );
								if ( false !== $value_exists ) {
									$wpdb->update(
										$wpdb->prefix.'vca_asm_finances_meta',
										array( 'value' => $_POST['limit-' . $type . '-'.$nation['id']] ),
										array( 'related_id'=> $nation['id'], 'type' => 'limit-' . $type ),
										array( '%s' ),
										array( '%d', '%s' )
									);
								} else {
									$wpdb->insert(
										$wpdb->prefix.'vca_asm_finances_meta',
										array(
											'value' => $_POST['limit-' . $type . '-'.$nation['id']],
											'related_id' => $nation['id'],
											'type' => 'limit-' . $type
										),
										array( '%s', '%d', '%s' )
									);
								}
							}
						}
					}

					header( 'Location: ' . strtok( $_SERVER['REQUEST_URI'], '?' ) . '?page=vca-asm-finances-settings&tab=general&todo=updated-general' );
				break;

				case "edit-tax":
					$nation = isset( $_POST['ctr'] ) ? $_POST['ctr'] : ( isset( $_GET['ctr'] ) ? $_GET['ctr'] : $this->admin_nation );
					$id = isset( $_GET['id'] ) ? $_GET['id'] : NULL;
					$this->settings_edit_tax_rate( array( 'id' => $id, 'nation' => $nation ) );
				break;

				case "save-tax":
					if ( isset( $_GET['id'] ) && $_GET['id'] != NULL ) {
						$wpdb->update(
							$wpdb->prefix.'vca_asm_finances_meta',
							array(
								'value' => $_POST['value'],
								'name' => $_POST['name'],
								'description' => $_POST['description'],
								'type' => 'tax-rate',
								'related_id' => $_POST['related_id']
							),
							array( 'id'=> $_GET['id'] ),
							array( '%s', '%s', '%s', '%s', '%d' ),
							array( '%d' )
						);
						header( 'Location: ' . strtok( $_SERVER['REQUEST_URI'], '?' ) . '?page=vca-asm-finances-settings&tab=tax-rates&todo=updated-tax&id=' . $_GET['id'] );
					} else {
						$wpdb->insert(
							$wpdb->prefix.'vca_asm_finances_meta',
							array(
								'value' => $_POST['value'],
								'name' => $_POST['name'],
								'description' => $_POST['description'],
								'type' => 'tax-rate',
								'related_id' => $_POST['related_id']
							),
							array( '%s', '%s', '%s', '%s', '%d' )
						);
						header( 'Location: ' . strtok( $_SERVER['REQUEST_URI'], '?' ) . '?page=vca-asm-finances-settings&tab=tax-rates&todo=saved-tax&id=' . $wpdb->insert_id );
					}

					$this->settings_view( $messages, $active_tab );
				break;

				case "save-tax-default":
					$wpdb->insert(
						$wpdb->prefix.'vca_asm_finances_meta',
						array(
							'value' => $_POST['value'],
							'type' => 'default-tax-rate',
							'related_id' => $_POST['related_id']
						),
						array( '%s', '%s', '%d' )
					);
					header( 'Location: ' . strtok( $_SERVER['REQUEST_URI'], '?' ) . '?page=vca-asm-finances-settings&tab=tax-rates&todo=saved-default-tax&id=' . $wpdb->insert_id );

					$this->settings_view( $messages, $active_tab );
				break;

				case "update-tax-default":
					$wpdb->update(
						$wpdb->prefix.'vca_asm_finances_meta',
						array(
							'value' => $_POST['value']
						),
						array( 'type'=> 'default-tax-rate', 'related_id' => $_POST['related_id'] ),
						array( '%s' ),
						array( '%s', '%d' )
					);
					header( 'Location: ' . strtok( $_SERVER['REQUEST_URI'], '?' ) . '?page=vca-asm-finances-settings&tab=tax-rates&todo=updated-default-tax&id=' . $wpdb->insert_id );

					$this->settings_view( $messages, $active_tab );
				break;

				case "edit-occ":
					$nation = isset( $_POST['ctr'] ) ? $_POST['ctr'] : ( isset( $_GET['ctr'] ) ? $_GET['ctr'] : $this->admin_nation );
					$id = isset( $_GET['id'] ) ? $_GET['id'] : NULL;
					$this->settings_edit_occasion( array( 'id' => $id, 'nation' => $nation ) );
				break;

				case "save-occ":
					if ( isset( $_GET['id'] ) && $_GET['id'] != NULL ) {
						$wpdb->update(
							$wpdb->prefix.'vca_asm_finances_meta',
							array(
								'value' => $_POST['value'],
								'name' => $_POST['name'],
								'description' => $_POST['description'],
								'type' => 'occasion',
								'related_id' => $_POST['related_id']
							),
							array( 'id'=> $_GET['id'] ),
							array( '%s', '%s', '%s', '%s', '%d' ),
							array( '%d' )
						);
						header( 'Location: ' . strtok( $_SERVER['REQUEST_URI'], '?' ) . '?page=vca-asm-finances-settings&tab=occasions&todo=updated-occ&id=' . $_GET['id'] );
					} else {
						$wpdb->insert(
							$wpdb->prefix.'vca_asm_finances_meta',
							array(
								'value' => $_POST['value'],
								'name' => $_POST['name'],
								'description' => $_POST['description'],
								'type' => 'occasion',
								'related_id' => $_POST['related_id']
							),
							array( '%s', '%s', '%s', '%s', '%d' )
						);
						header( 'Location: ' . strtok( $_SERVER['REQUEST_URI'], '?' ) . '?page=vca-asm-finances-settings&tab=occasions&todo=saved-occ&id=' . $wpdb->insert_id );
					}

					$this->settings_view( $messages, $active_tab );
				break;

				case "save-ca":
					$cities = $vca_asm_geography->get_all( 'name', 'ASC', 'city' );

					foreach ( $cities as $city ) {
						if ( isset( $_POST['city-'.$city['id']] ) ) {
							$value_exists = $vca_asm_finances->get_cash_account( $city['id'] );
							if ( false !== $value_exists ) {
								$wpdb->update(
									$wpdb->prefix.'vca_asm_finances_meta',
									array( 'value' => $_POST['city-'.$city['id']] ),
									array( 'related_id'=> $city['id'], 'type' => 'cash-acc' ),
									array( '%s' ),
									array( '%d', '%s' )
								);
							} else {
								$wpdb->insert(
									$wpdb->prefix.'vca_asm_finances_meta',
									array(
										'value' => $_POST['city-'.$city['id']],
										'related_id' => $city['id'],
										'type' => 'cash-acc'
									),
									array( '%s', '%d', '%s' )
								);
							}
						}
					}

					header( 'Location: ' . strtok( $_SERVER['REQUEST_URI'], '?' ) . '?page=vca-asm-finances-settings&tab=cash-accs&todo=updated-ca&id=' . $_GET['id'] );
				break;

				case "edit":
				case "edit-cc":
					$nation = isset( $_POST['ctr'] ) ? $_POST['ctr'] : ( isset( $_GET['ctr'] ) ? $_GET['ctr'] : $this->admin_nation );
					$id = isset( $_GET['id'] ) ? $_GET['id'] : NULL;
					$this->settings_edit_cc( array( 'id' => $id, 'nation' => $nation ) );
				break;

				case "save-cc":
					if ( isset( $_GET['id'] ) && $_GET['id'] != NULL ) {
						$wpdb->update(
							$wpdb->prefix.'vca_asm_finances_meta',
							array(
								'value' => $_POST['value'],
								'name' => $_POST['name'],
								'description' => $_POST['description'],
								'type' => 'cost-center',
								'related_id' => $_POST['related_id']
							),
							array( 'id'=> $_GET['id'] ),
							array( '%s', '%s', '%s', '%s', '%d' ),
							array( '%d' )
						);
						header( 'Location: ' . strtok( $_SERVER['REQUEST_URI'], '?' ) . '?page=vca-asm-finances-settings&tab=cost-centers&todo=updated-cc&id=' . $_GET['id'] );
					} else {
						$wpdb->insert(
							$wpdb->prefix.'vca_asm_finances_meta',
							array(
								'value' => $_POST['value'],
								'name' => $_POST['name'],
								'description' => $_POST['description'],
								'type' => 'cost-center',
								'related_id' => $_POST['related_id']
							),
							array( '%s', '%s', '%s', '%s', '%d' )
						);
						header( 'Location: ' . strtok( $_SERVER['REQUEST_URI'], '?' ) . '?page=vca-asm-finances-settings&tab=cost-centers&todo=saved-cc&id=' . $wpdb->insert_id );
					}

					$this->settings_view( $messages, $active_tab );
				break;

				case "edit-ei":
					$nation = isset( $_POST['ctr'] ) ? $_POST['ctr'] : ( isset( $_GET['ctr'] ) ? $_GET['ctr'] : $this->admin_nation );
					$id = isset( $_GET['id'] ) ? $_GET['id'] : NULL;
					$type = isset( $_GET['type'] ) ? $_GET['type'] : 'income';
					$this->settings_edit_ei_account( array( 'id' => $id, 'type' => $type, 'nation' => $nation ) );
				break;

				case "save-ei":
					$type = isset( $_GET['type'] ) ? $_GET['type'] : 'income';
					if ( isset( $_GET['id'] ) && $_GET['id'] != NULL ) {
						$id = $_GET['id'];
						$wpdb->update(
							$wpdb->prefix.'vca_asm_finances_meta',
							array(
								'value' => $_POST['value'],
								'name' => $_POST['name'],
								'description' => $_POST['description'],
								'type' => isset( $_POST['type'] ) ? $_POST['type'] : $type,
								'related_id' => $_POST['related_id']
							),
							array( 'id'=> $id ),
							array( '%s', '%s', '%s', '%s', '%d' ),
							array( '%d' )
						);
						header( 'Location: ' . strtok( $_SERVER['REQUEST_URI'], '?' ) . '?page=vca-asm-finances-settings&tab=ei-accs&todo=updated-ei&id=' . $id );
					} else {
						$wpdb->insert(
							$wpdb->prefix.'vca_asm_finances_meta',
							array(
								'value' => $_POST['value'],
								'name' => $_POST['name'],
								'description' => $_POST['description'],
								'type' => isset( $_POST['type'] ) ? $_POST['type'] : $type,
								'related_id' => $_POST['related_id']
							),
							array( '%s', '%s', '%s', '%s', '%d' )
						);
						header( 'Location: ' . strtok( $_SERVER['REQUEST_URI'], '?' ) . '?page=vca-asm-finances-settings&tab=ei-accs&todo=saved-ei&id=' . $wpdb->insert_id );
					}

					$this->settings_view( $messages, $active_tab );
				break;

				case "updated-general":
					$messages[] = array(
						'type' => 'message',
						'message' => __( 'Saved.', 'vca-asm' )
					);
					$this->settings_view( $messages, $active_tab );
				break;

				case "updated-ca":
					$messages[] = array(
						'type' => 'message',
						'message' => __( 'Cash Accounts successfully updated.', 'vca-asm' )
					);
					$this->settings_view( $messages, $active_tab );
				break;

				case "updated-cc":
					$messages[] = array(
						'type' => 'message',
						'message' => __( 'Cost center successfully updated.', 'vca-asm' )
					);
					$this->settings_view( $messages, $active_tab );
				break;

				case "saved-cc":
					$messages[] = array(
						'type' => 'message',
						'message' => __( 'Cost center successfully added.', 'vca-asm' )
					);
					$this->settings_view( $messages, $active_tab );
				break;

				case "updated-ei":
					$messages[] = array(
						'type' => 'message',
						'message' => __( 'Account successfully updated.', 'vca-asm' )
					);
					$this->settings_view( $messages, $active_tab );
				break;

				case "saved-ei":
					$messages[] = array(
						'type' => 'message',
						'message' => __( 'Account successfully added.', 'vca-asm' )
					);
					$this->settings_view( $messages, $active_tab );
				break;

				case "updated-tax":
					$messages[] = array(
						'type' => 'message',
						'message' => __( 'Revenue Tax Rates successfully updated.', 'vca-asm' )
					);
					$this->settings_view( $messages, $active_tab );
				break;

				case "saved-tax":
					$messages[] = array(
						'type' => 'message',
						'message' => __( 'Revenue Tax Rate successfully added.', 'vca-asm' )
					);
					$this->settings_view( $messages, $active_tab );
				break;

				case "saved-default-tax":
				case "updated-default-tax":
					$messages[] = array(
						'type' => 'message',
						'message' => __( 'Default Revenue Tax Rates successfully updated.', 'vca-asm' )
					);
					$this->settings_view( $messages, $active_tab );
				break;

				case "updated-occ":
					$messages[] = array(
						'type' => 'message',
						'message' => __( 'Occasion successfully updated.', 'vca-asm' )
					);
					$this->settings_view( $messages, $active_tab );
				break;

				case "saved-occ":
					$messages[] = array(
						'type' => 'message',
						'message' => __( 'Occasion successfully added.', 'vca-asm' )
					);
					$this->settings_view( $messages, $active_tab );
				break;
			}
		} else {
			$this->settings_view( $messages, $active_tab );
		}
	}

	/**
	 * Settings, Nation Selector
	 *
	 * @since 1.5
	 * @access public
	 */
	private function settings_nation_selector( $tab )
	{
		global $vca_asm_geography;

		$form = new VCA_ASM_Admin_Form( array(
			'echo' => false,
			'form' => true,
			'name' => 'vca-asm-finances-settings-nation-selector',
			'method' => 'post',
			'metaboxes' => false,
			'js' => false,
			'url' => '?page=vca-asm-finances-settings&tab=' . $tab,
			'action' => '?page=vca-asm-finances-settings&tab=' . $tab,
			'button' => __( 'Switch Country', 'vca-asm' ),
			'button_id' => 'submit',
			'top_button' => false,
			'has_cap' => true,
			'fields' => array(
				array(
					'type' => 'select',
					'id' => 'ctr',
					'options' => $vca_asm_geography->options_array( array( 'type' => 'nation' )),
					'value' => isset( $_POST['ctr'] ) ? $_POST['ctr'] : ( isset( $_GET['ctr'] ) ? $_GET['ctr'] : $this->admin_nation ),
					'label' => __( 'The country this data belongs to', 'vca-asm' )
				)
			)
		));

		return $form->output();
	}

	/**
	 * Settings View
	 *
	 * @since 1.5
	 * @access public
	 */
	public function settings_view( $messages = array(), $active_tab = 'general' )
	{
		$url = '?page=vca-asm-finances-settings';
		$adminpage = new VCA_ASM_Admin_Page( array(
			'icon' => 'icon-finances',
			'title' => _x( 'Finances', 'Admin Menu', 'vca-asm' ) . ' | ' . __( 'Settings' , 'vca-asm' ),
			'messages' => $messages,
			'url' => $url,
			'back' => false,
			'tabs' => array(
				array(
					'title' => _x( 'General', 'Finances Admin Menu', 'vca-asm' ),
					'value' => 'general',
					'icon' => 'icon-settings'
				),
				array(
					'title' => _x( 'Revenue Tax', 'Finances Admin Menu', 'vca-asm' ),
					'value' => 'tax-rates',
					'icon' => 'icon-finances'
				),
				array(
					'title' => _x( 'Occasions / Activities', 'Finances Admin Menu', 'vca-asm' ),
					'value' => 'occasions',
					'icon' => 'icon-actions'
				),
				array(
					'title' => _x( 'Income/Expense Accounts', 'Finances Admin Menu', 'vca-asm' ),
					'value' => 'ei-accs',
					'icon' => 'icon-stats'
				),
				array(
					'title' => _x( 'Cash Accounts', 'Finances Admin Menu', 'vca-asm' ),
					'value' => 'cash-accs',
					'icon' => 'icon-stats'
				),
				array(
					'title' => _x( 'Cost Centers', 'Finances Admin Menu', 'vca-asm' ),
					'value' => 'cost-centers',
					'icon' => 'icon-stats'
				)
			),
			'active_tab' => $active_tab
		));

		$output = $adminpage->top();

		switch ( $active_tab ) {
			case 'cash-accs':
				$output .= $this->settings_cash_accounts();
			break;

			case 'cost-centers':
				$output .= $this->settings_cost_centers();
			break;

			case 'ei-accs':
				$output .= $this->settings_income_expense_accounts();
			break;

			case 'tax-rates':
				$output .= $this->settings_tax_rates();
			break;

			case 'occasions':
				$output .= $this->settings_occasions();
			break;

			case 'general':
			default:
				$output .= $this->settings_general();
		}

		$output .= $adminpage->bottom();

		echo $output;
	}

	private function settings_general()
	{
		global $vca_asm_finances, $vca_asm_geography;

		$url = '?page=vca-asm-finances-settings&tab=general';
		$form_action = $url . '&todo=save-general&noheader=true';

		$fields = array();

		if ( 'global' === $this->cap_lvl ) {
			$nations = $vca_asm_geography->get_all( 'name', 'ASC', 'nation' );
			foreach ( $nations as $nation ) {
				if (
					'global' === $this->cap_lvl ||
					( 'nation' === $this->cap_lvl &&  $this->admin_nation == $nation['id'] )
				) {
					$fields[] = array(
						'title' => $nation['name'],
						'fields' => array(
							array(
								'type' => 'text',
								'label' => __( 'Structural Cash, City', 'vca-asm' ),
								'id' => 'limit-city-' . $nation['id'],
								'value' => $vca_asm_finances->get_limit( $nation['id'], 'city' ),
								'unit' => $vca_asm_geography->get_currency( $nation['id'], 'name' )
							),
							array(
								'type' => 'text',
								'label' => __( 'Structural Cash, Crew', 'vca-asm' ),
								'id' => 'limit-lc-' . $nation['id'],
								'value' => $vca_asm_finances->get_limit( $nation['id'], 'lc' ),
								'unit' => $vca_asm_geography->get_currency( $nation['id'], 'name' )
							),
							array(
								'type' => 'text',
								'label' => __( 'Structural Cash, Cell', 'vca-asm' ),
								'id' => 'limit-cell-' . $nation['id'],
								'value' => $vca_asm_finances->get_limit( $nation['id'], 'cell' ),
								'unit' => $vca_asm_geography->get_currency( $nation['id'], 'name' )
							)
						)
					);
				}
			}
		} else {
			$fields[] = array(
				'title' => __( 'Maximum Structural Cash', 'vca-asm' ),
				'fields' => array(
					array(
						'type' => 'text',
						'label' => __( 'City', 'vca-asm' ),
						'id' => 'limit-city-' . $this->admin_nation,
						'value' => $vca_asm_finances->get_limit( $this->admin_nation, 'city' ),
						'unit' => $vca_asm_geography->get_currency( $this->admin_nation, 'name' )
					),
					array(
						'type' => 'text',
						'label' => __( 'Crew', 'vca-asm' ),
						'id' => 'limit-lc-' . $this->admin_nation,
						'value' => $vca_asm_finances->get_limit( $this->admin_nation, 'lc' ),
						'unit' => $vca_asm_geography->get_currency( $this->admin_nation, 'name' )
					),
					array(
						'type' => 'text',
						'label' => __( '(old-school) Cell', 'vca-asm' ),
						'id' => 'limit-cell-' . $this->admin_nation,
						'value' => $vca_asm_finances->get_limit( $this->admin_nation, 'cell' ),
						'unit' => $vca_asm_geography->get_currency( $this->admin_nation, 'name' )
					)
				)
			);
		}

		$form = new VCA_ASM_Admin_Form( array(
			'echo' => false,
			'form' => true,
			'name' => 'vca-asm-finances-settings-form',
			'method' => 'post',
			'metaboxes' => true,
			'js' => false,
			'url' => $url,
			'action' => $form_action,
			'button' => __( 'Save', 'vca-asm' ),
			'button_id' => 'submit',
			'top_button' => true,
			'has_cap' => true,
			'fields' => $fields
		));

		return $form->output();
	}

	private function settings_tax_rates()
	{
		global $vca_asm_finances, $vca_asm_geography, $vca_asm_utilities;

		$nation = isset( $_POST['ctr'] ) ? $_POST['ctr'] : ( isset( $_GET['ctr'] ) ? $_GET['ctr'] : $this->admin_nation );

		$output = '';
		$extra_string = '';
		if ( 'global' === $this->cap_lvl ) {
			$output .= $this->settings_nation_selector( 'tax-rates' );
			$extra_string = ' (' .
				sprintf(
					_x( 'for %s', '%s is a country', 'vca-asm' ),
					$vca_asm_geography->get_name( $nation )
				) .
				')';
		}

		$button = '';
		if ( $this->has_cap ) {
			$button = '<form method="post" action="?page=vca-asm-finances-settings&tab=tax-rates&todo=edit-tax&ctr=' . $nation . '">' .
				'<input type="submit" class="button-secondary" value="+ ' . __( 'add tax rate', 'vca-asm' ) . '" />' .
			'</form>';
		}

		$output .= '<br />' . $button . '<br />';

		$output .= $this->settings_list_tax_rates( $nation );

		$output .= '<br />' . $button;

		extract( $vca_asm_utilities->table_order( 'value' ) );
		$test = $vca_asm_finances->get_tax_rates( $orderby, $order, $nation );

		if ( ! empty( $test ) && 1 < count( $test ) ) {

			$mbs = new VCA_ASM_Admin_Metaboxes( array(
				'echo' => false,
				'columns' => 1,
				'running' => 1,
				'id' => '',
				'title' => __( 'Default rate', 'vca-asm' ),
				'js' => false
			));

			$output .= $mbs->top();
			$output .= $mbs->mb_top();

			$data = $vca_asm_finances->get_default_tax_rate( $nation );

			$url = '?page=vca-asm-finances-settings&tab=tax-rates&ctr=' . $nation;
			$form_action = $url . '&todo=';
			$form_action .= ! empty( $data ) ? 'update' : 'save';
			$form_action .= '-tax-default&noheader=true';

			$fields = array(
				array(
					'type' => 'select',
					'label' => __( 'Default value', 'vca-asm' ),
					'id' => 'value',
					'value' => ! empty( $data ) ? $data : '',
					'desc' => __( 'Which of the above rates should be selected by default, when the SPOC has to choose?', 'vca-asm' ),
					'options' => $vca_asm_finances->tax_options_array( array( 'nation' => $nation ) )
				),
				array(
					'type' => 'hidden',
					'id' => 'related_id',
					'value' => $nation
				)
			);

			$form = new VCA_ASM_Admin_Form( array(
				'echo' => false,
				'form' => true,
				'name' => 'vca-asm-finances-settings-form',
				'method' => 'post',
				'metaboxes' => false,
				'js' => false,
				'url' => $url,
				'action' => $form_action,
				'button' => __( 'Save', 'vca-asm' ),
				'button_id' => 'submit',
				'top_button' => false,
				'has_cap' => true,
				'fields' => $fields,
				'back' => false
			));

			$output .= $form->output();

			$output .= $mbs->mb_bottom();
			$output .= $mbs->bottom();
		}

		return $output;
	}

	private function settings_list_tax_rates( $nation = 0 )
	{
		global $current_user, $vca_asm_finances, $vca_asm_utilities;

		$url = '?page=vca-asm-finances-settings&tab=tax-rates&ctr=' . $nation;

		extract( $vca_asm_utilities->table_order( 'value' ) );
		$rows = $vca_asm_finances->get_tax_rates( $orderby, $order, $nation );

		$columns = array(
			array(
				'id' => 'value',
				'title' => __( 'Percentage Points', 'vca-asm' ),
				'sortable' => false,
				'strong' => true,
				'link' => array(
					'title' => __( 'Edit %s', 'vca-asm' ),
					'title_row_data' => 'value',
					'url' => '?page=vca-asm-finances-settings&todo=edit-tax&id=%d',
					'url_row_data' => 'id'
				),
				'actions' => array( 'edit-tax', 'delete' ),
				'cap' => 'finances-meta'
			),
			array(
				'id' => 'name',
				'title' => _x( 'Name', 'Tax Rates', 'vca-asm' ),
				'sortable' => false
			),
			array(
				'id' => 'description',
				'title' => __( 'Description', 'vca-asm' ),
				'sortable' => false
			)
		);

		$args = array(
			'base_url' => $url,
			'sort_url' => $url,
			'echo' => false
		);
		$the_table = new VCA_ASM_Admin_Table( $args, $columns, $rows );

		return $the_table->output();
	}

	private function settings_edit_tax_rate( $args = array() )
	{
		global $current_user,
			$vca_asm_finances, $vca_asm_geography;

		$default_args = array(
			'id' => NULL,
			'messages' => array(),
			'nation' => $this->admin_nation
		);
		extract( wp_parse_args( $args, $default_args ), EXTR_SKIP );

		$url = '?page=vca-asm-finances-settings&tab=tax-rates&ctr=' . $nation;

		if ( ! empty( $id ) ) {
			$form_action = $url . '&todo=save-tax&noheader=true&id=' . $id;
			$data = $vca_asm_finances->get_tax_rate( $id );
			$title = sprintf( __( 'Edit &quot;%s&quot;', 'vca-asm' ), $data['name'] );
		} else {
			$form_action = $url . '&todo=save-tax&noheader=true';
			$title = __( 'Add new tax rate', 'vca-asm' );
		}

		$adminpage = new VCA_ASM_Admin_Page( array(
			'icon' => 'icon-finances',
			'title' => $title,
			'messages' => $messages,
			'url' => $url
		));

		$output = $adminpage->top();

		$fields = array(
			array(
				'title' => __( 'The Revenue Tax Rate', 'vca-asm' ),
				'fields' => array(
					array(
						'type' => 'text',
						'label' => __( 'Percentage Points', 'vca-asm' ),
						'id' => 'value',
						'value' => ! empty( $data['value'] ) ? $data['value'] : '',
						'desc' => __( 'The tax rate itsself', 'vca-asm' )
					),
					array(
						'type' => 'text',
						'label' => _x( 'Name', 'Tax Rates', 'vca-asm' ),
						'id' => 'name',
						'value' => ! empty( $data['name'] ) ? $data['name'] : '',
						'desc' => __( 'The (short-)name of the tax rate', 'vca-asm' )
					),
					array(
						'type' => 'text',
						'label' => __( 'Description', 'vca-asm' ),
						'id' => 'description',
						'value' => ! empty( $data['description'] ) ? $data['description'] : '',
						'desc' => __( 'A description of what is taxed at this rate', 'vca-asm' )
					),
					array(
						'type' => 'hidden',
						'id' => 'related_id',
						'value' => $nation
					)
				)
			)
		);

		$form = new VCA_ASM_Admin_Form( array(
			'echo' => false,
			'form' => true,
			'name' => 'vca-asm-finances-settings-form',
			'method' => 'post',
			'metaboxes' => true,
			'js' => false,
			'url' => $url,
			'action' => $form_action,
			'button' => __( 'Save', 'vca-asm' ),
			'button_id' => 'submit',
			'top_button' => true,
			'has_cap' => true,
			'fields' => $fields,
			'back' => true,
			'back_url' => $url
		));

		$output .= $form->output();

		$output .= $adminpage->bottom();

		echo $output;
	}

	private function settings_occasions()
	{
		global $vca_asm_geography;

		$nation = isset( $_POST['ctr'] ) ? $_POST['ctr'] : ( isset( $_GET['ctr'] ) ? $_GET['ctr'] : $this->admin_nation );

		$output = '';
		$extra_string = '';
		if ( 'global' === $this->cap_lvl ) {
			$output .= $this->settings_nation_selector( 'occasions' );
			$extra_string = ' (' .
				sprintf(
					_x( 'for %s', '%s is a country', 'vca-asm' ),
					$vca_asm_geography->get_name( $nation )
				) .
				')';
		}

		$button = '';
		if ( $this->has_cap ) {
			$button = '<form method="post" action="?page=vca-asm-finances-settings&tab=occasions&todo=edit-occ">' .
				'<input type="submit" class="button-secondary" value="+ ' . __( 'add occasion', 'vca-asm' ) . '" />' .
			'</form>';
		}

		$output .= '<br />' . $button . '<br />';

		$output .= $this->settings_list_occasions( $nation );

		$output .= '<br />' . $button;

		return $output;
	}

	private function settings_list_occasions( $nation = 0 )
	{
		global $current_user, $vca_asm_finances, $vca_asm_utilities;

		$url = '?page=vca-asm-finances-settings&tab=occasions&ctr=' . $nation;

		extract( $vca_asm_utilities->table_order( 'name' ) );
		$rows = $vca_asm_finances->get_occasions( $orderby, $order, $nation );

		$columns = array(
			array(
				'id' => 'name',
				'title' => _x( 'Name', 'Occasions', 'vca-asm' ),
				'sortable' => false,
				'strong' => true,
				'link' => array(
					'title' => __( 'Edit %s', 'vca-asm' ),
					'title_row_data' => 'value',
					'url' => '?page=vca-asm-finances-settings&todo=edit-occ&id=%d',
					'url_row_data' => 'id'
				),
				'actions' => array( 'edit-occ', 'delete' ),
				'cap' => 'finances-meta'
			),
			array(
				'id' => 'value',
				'title' => __( 'Description for Donations', 'vca-asm' ),
				'sortable' => false
			),
			array(
				'id' => 'description',
				'title' => __( 'Description for Structural Funds', 'vca-asm' ),
				'sortable' => false
			)
		);

		$args = array(
			'base_url' => $url,
			'sort_url' => $url,
			'echo' => false
		);
		$the_table = new VCA_ASM_Admin_Table( $args, $columns, $rows );

		return $the_table->output();
	}

	private function settings_edit_occasion( $args = array() )
	{
		global $current_user,
			$vca_asm_finances, $vca_asm_geography;

		$default_args = array(
			'id' => NULL,
			'messages' => array(),
			'nation' => $this->admin_nation
		);
		extract( wp_parse_args( $args, $default_args ), EXTR_SKIP );

		$url = '?page=vca-asm-finances-settings&tab=occasions&ctr=' . $nation;

		if ( ! empty( $id ) ) {
			$form_action = $url . '&todo=save-occ&noheader=true&id=' . $id;
			$data = $vca_asm_finances->get_occasion( $id );
			$title = sprintf( __( 'Edit &quot;%s&quot;', 'vca-asm' ), $data['name'] );
		} else {
			$form_action = $url . '&todo=save-occ&noheader=true';
			$title = __( 'Add new occasion', 'vca-asm' );
		}

		$adminpage = new VCA_ASM_Admin_Page( array(
			'icon' => 'icon-finances',
			'title' => $title,
			'messages' => $messages,
			'url' => $url
		));

		$output = $adminpage->top();

		$fields = array(
			array(
				'title' => __( 'The Occasion', 'vca-asm' ),
				'fields' => array(
					array(
						'type' => 'text',
						'label' => _x( 'Name', 'Occasions', 'vca-asm' ),
						'id' => 'name',
						'value' => ! empty( $data['name'] ) ? $data['name'] : '',
						'desc' => __( 'The (short-)name of the occasion', 'vca-asm' ) . ' (' . __( 'This is, what the Finances-SPOC sees.', 'vca-asm' ) . ')'
					),
					array(
						'type' => 'text',
						'label' => __( 'Description for Donations', 'vca-asm' ),
						'id' => 'value',
						'value' => ! empty( $data['value'] ) ? $data['value'] : '',
						'desc' => __( 'A description of what qualifies as this category of occasion', 'vca-asm' )
					),
					array(
						'type' => 'text',
						'label' => __( 'Description for Structural Funds', 'vca-asm' ),
						'id' => 'description',
						'value' => ! empty( $data['description'] ) ? $data['description'] : '',
						'desc' => __( 'A description of what qualifies as this category of occasion', 'vca-asm' )
					),
					array(
						'type' => 'hidden',
						'id' => 'related_id',
						'value' => $nation
					)
				)
			)
		);

		$form = new VCA_ASM_Admin_Form( array(
			'echo' => false,
			'form' => true,
			'name' => 'vca-asm-finances-settings-form',
			'method' => 'post',
			'metaboxes' => true,
			'js' => false,
			'url' => $url,
			'action' => $form_action,
			'button' => __( 'Save', 'vca-asm' ),
			'button_id' => 'submit',
			'top_button' => true,
			'has_cap' => true,
			'fields' => $fields,
			'back' => true,
			'back_url' => $url
		));

		$output .= $form->output();

		$output .= $adminpage->bottom();

		echo $output;
	}

	private function settings_cash_accounts()
	{
		global $vca_asm_finances, $vca_asm_geography;

		$url = '?page=vca-asm-finances-settings&tab=cash-accs';
		$form_action = $url . '&todo=save-ca&noheader=true';

		$fields = array(
			array(
				'title' => __( 'Cash Accounts per City', 'vca-asm' ),
				'fields' => array()
			)
		);

		$cities = $vca_asm_geography->get_all( 'name', 'ASC', 'city' );

		foreach ( $cities as $city ) {
			if (
				'global' === $this->cap_lvl ||
				( 'nation' === $this->cap_lvl &&  $this->admin_nation == $vca_asm_geography->has_nation( $city['id'] ) )
			) {
				$value = $vca_asm_finances->get_cash_account( $city['id'] );
				$fields[0]['fields'][] = array(
					'type' => 'text',
					'label' => $city['name'],
					'id' => 'city-' . $city['id'],
					'value' => $value !== false ? $value : ''
				);
			}
		}

		$form = new VCA_ASM_Admin_Form( array(
			'echo' => false,
			'form' => true,
			'name' => 'vca-asm-finances-settings-form',
			'method' => 'post',
			'metaboxes' => true,
			'js' => false,
			'url' => $url,
			'action' => $form_action,
			'button' => __( 'Save', 'vca-asm' ),
			'button_id' => 'submit',
			'top_button' => true,
			'has_cap' => true,
			'fields' => $fields
		));

		return $form->output();
	}

	private function settings_cost_centers()
	{
		global $vca_asm_geography;

		$nation = isset( $_POST['ctr'] ) ? $_POST['ctr'] : ( isset( $_GET['ctr'] ) ? $_GET['ctr'] : $this->admin_nation );

		$output = '';
		$extra_string = '';
		if ( 'global' === $this->cap_lvl ) {
			$output .= $this->settings_nation_selector( 'cost-centers' );
			$extra_string = ' (' .
				sprintf(
					_x( 'for %s', '%s is a country', 'vca-asm' ),
					$vca_asm_geography->get_name( $nation )
				) .
				')';
		}

		$button = '';
		if ( $this->has_cap ) {
			$button = '<form method="post" action="?page=vca-asm-finances-settings&tab=cost-centers&todo=edit-cc">' .
				'<input type="submit" class="button-secondary" value="+ ' . __( 'add cost center', 'vca-asm' ) . '" />' .
			'</form>';
		}

		$output .= '<br />' . $button . '<br />';

		$output .= $this->settings_list_ccs();

		$output .= '<br />' . $button;

		return $output;
	}

	private function settings_list_ccs( $nation = 0 ) //todo extend beyond Germany
	{
		global $current_user, $vca_asm_finances, $vca_asm_utilities;

		$url = '?page=vca-asm-finances-settings&tab=cost-centers&ctr=' . $nation;

		extract( $vca_asm_utilities->table_order( 'value' ) );
		$rows = $vca_asm_finances->get_cost_centers( $orderby, $order, $nation );

		$columns = array(
			array(
				'id' => 'name',
				'title' => _x( 'Name', 'Cost Centers', 'vca-asm' ),
				'sortable' => true,
				'strong' => true,
				'link' => array(
					'title' => __( 'Edit %s', 'vca-asm' ),
					'title_row_data' => 'value',
					'url' => '?page=vca-asm-finances-settings&todo=edit-cc&id=%d',
					'url_row_data' => 'id'
				),
				'actions' => array( 'edit-cc', 'delete' ),
				'cap' => 'finances-meta'
			),
			array(
				'id' => 'value',
				'title' => __( 'Cost Center', 'vca-asm' ),
				'sortable' => true
			),
			array(
				'id' => 'description',
				'title' => __( 'Description', 'vca-asm' ),
				'sortable' => false
			)
		);

		$args = array(
			'base_url' => $url,
			'sort_url' => $url,
			'echo' => false
		);
		$the_table = new VCA_ASM_Admin_Table( $args, $columns, $rows );

		return $the_table->output();
	}

	private function settings_edit_cc( $args = array() )
	{
		global $current_user,
			$vca_asm_finances, $vca_asm_geography;

		$default_args = array(
			'id' => NULL,
			'messages' => array(),
			'nation' => $this->admin_nation
		);
		extract( wp_parse_args( $args, $default_args ), EXTR_SKIP );

		$url = '?page=vca-asm-finances-settings&tab=cost-centers&ctr=' . $nation;

		if ( ! empty( $id ) ) {
			$form_action = $url . '&todo=save-cc&noheader=true&id=' . $id;
			$data = $vca_asm_finances->get_cost_center( $id );
			$title = sprintf( __( 'Edit &quot;%s&quot;', 'vca-asm' ), $data['name'] );
		} else {
			$form_action = $url . '&todo=save-cc&noheader=true';
			$title = __( 'Add new cost center', 'vca-asm' );
		}

		$adminpage = new VCA_ASM_Admin_Page( array(
			'icon' => 'icon-finances',
			'title' => $title,
			'messages' => $messages,
			'url' => $url
		));

		$output = $adminpage->top();

		$fields = array(
			array(
				'title' => __( 'The Cost Center', 'vca-asm' ),
				'fields' => array(
					array(
						'type' => 'text',
						'label' => _x( 'Name', 'Cost Centers', 'vca-asm' ),
						'id' => 'name',
						'value' => ! empty( $data['name'] ) ? $data['name'] : '',
						'desc' => __( 'The (short-)name of the cost center', 'vca-asm' ) . ' ' . __( 'for the tax statement', 'vca-asm' )
					),
					array(
						'type' => 'text',
						'label' => __( 'Cost Center', 'vca-asm' ),
						'id' => 'value',
						'value' => ! empty( $data['value'] ) ? $data['value'] : '',
						'desc' => __( 'The ID of the cost center, usually numeric', 'vca-asm' )
					),
					array(
						'type' => 'text',
						'label' => __( 'Description', 'vca-asm' ),
						'id' => 'description',
						'value' => ! empty( $data['description'] ) ? $data['description'] : '',
						'desc' => __( 'A description of what all belongs in this category of costs', 'vca-asm' ) . ' ' . __( 'for city users', 'vca-asm' )
					),
					array(
						'type' => 'hidden',
						'id' => 'related_id',
						'value' => $nation
					)
				)
			)
		);

		$form = new VCA_ASM_Admin_Form( array(
			'echo' => false,
			'form' => true,
			'name' => 'vca-asm-finances-settings-form',
			'method' => 'post',
			'metaboxes' => true,
			'js' => false,
			'url' => $url,
			'action' => $form_action,
			'button' => __( 'Save', 'vca-asm' ),
			'button_id' => 'submit',
			'top_button' => true,
			'has_cap' => true,
			'fields' => $fields,
			'back' => true,
			'back_url' => $url
		));

		$output .= $form->output();

		$output .= $adminpage->bottom();

		echo $output;
	}

	private function settings_income_expense_accounts()
	{
		global $vca_asm_geography;

		$nation = isset( $_POST['ctr'] ) ? $_POST['ctr'] : ( isset( $_GET['ctr'] ) ? $_GET['ctr'] : $this->admin_nation );

		$output = '';
		$extra_string = '';
		if ( 'global' === $this->cap_lvl ) {
			$output .= $this->settings_nation_selector( 'ei-accs' );
			$extra_string = ' (' .
				sprintf(
					_x( 'for %s', '%s is a country', 'vca-asm' ),
					$vca_asm_geography->get_name( $nation )
				) .
				')';
		}

		$button = '';
		if ( $this->has_cap ) {
			$button = '<form method="post" style="display:inline" action="?page=vca-asm-finances-settings&tab=ei-accs&todo=edit-ei&type=expense">' .
				'<input type="submit" class="button-secondary margin" value="+ ' . __( 'add Expense Account', 'vca-asm' ) . '" />' .
			'</form>' .
			'<form method="post" style="display:inline" action="?page=vca-asm-finances-settings&tab=ei-accs&todo=edit-ei&type=income">' .
				'<input type="submit" class="button-secondary margin" value="+ ' . __( 'add Income Account', 'vca-asm' ) . '" />' .
			'</form>';
		}

		$output .= '<br />' . $button . '<br />';

		$output .= '<h3>' . __( 'Expense Accounts', 'vca-asm' ) . '</h3>';
		$output .= $this->settings_list_ei_accs( 'expense' );

		$output .= '<h3>' . __( 'Income Accounts', 'vca-asm' ) . '</h3>';
		$output .= $this->settings_list_ei_accs( 'income' );

		$output .= '<br />' . $button;

		return $output;
	}

	private function settings_list_ei_accs( $type = 'income', $nation = 0 )
	{
		global $current_user,
			$vca_asm_finances, $vca_asm_utilities;

		$url = '?page=vca-asm-finances-settings&tab=ei-accs&ctr=' . $nation;

		extract( $vca_asm_utilities->table_order( 'value' ) );
		$rows = $vca_asm_finances->get_ei_accounts( $orderby, $order, $type, $nation );

		$columns = array(
			array(
				'id' => 'name',
				'title' => _x( 'Name', 'Name', 'vca-asm' ),
				'sortable' => true,
				'strong' => true,
				'link' => array(
					'title' => __( 'Edit %s', 'vca-asm' ),
					'title_row_data' => 'value',
					'url' => '?page=vca-asm-finances-settings&todo=edit-ei&id=%d',
					'url_row_data' => 'id'
				),
				'actions' => array( 'edit-ei', 'delete' ),
				'cap' => 'finances-meta'
			),
			array(
				'id' => 'value',
				'title' => __( 'Account Number', 'vca-asm' ),
				'sortable' => true
			),
			array(
				'id' => 'description',
				'title' => __( 'Description', 'vca-asm' ),
				'sortable' => false
			)
		);

		$args = array(
			'base_url' => $url,
			'sort_url' => $url,
			'echo' => false
		);
		$the_table = new VCA_ASM_Admin_Table( $args, $columns, $rows );

		return $the_table->output();
	}

	private function settings_edit_ei_account( $args = array() )
	{
		global $current_user,
			$vca_asm_finances, $vca_asm_geography;

		$default_args = array(
			'id' => NULL,
			'type' => 'income',
			'messages' => array(),
			'nation' => $this->admin_nation
		);
		extract( wp_parse_args( $args, $default_args ), EXTR_SKIP );
		$type = ! empty( $_GET['type'] ) ? $_GET['type'] : $type;

		$url = '?page=vca-asm-finances-settings&tab=ei-accs';

		if ( ! empty( $id ) ) {
			$form_action = $url . '&todo=save-ei&noheader=true&id=' . $id . '&type=' . $type;
			$data = $vca_asm_finances->get_ei_account( $id );
			$title = sprintf( __( 'Edit &quot;%1$s&quot; (%2$s)', 'vca-asm' ), $data['name'], $vca_asm_finances->types_to_nicenames[$type] );
			$type = ! empty( $data['type'] ) ? $data['type'] : $type;
		} else {
			$form_action = $url . '&todo=save-ei&noheader=true&type=' . $type;
			$title = sprintf( __( 'Add new %s', 'vca-asm' ), $vca_asm_finances->types_to_nicenames[$type] );
		}

		$adminpage = new VCA_ASM_Admin_Page( array(
			'icon' => 'icon-finances',
			'title' => $title,
			'messages' => $messages,
			'url' => $url
		));

		$output = $adminpage->top();

		$fields = array(
			array(
				'title' => sprintf( _x( 'The %s', 'The xyz Account', 'vca-asm' ), $vca_asm_finances->types_to_nicenames[$type] ),
				'fields' => array(
					array(
						'type' => 'hidden',
						'id' => 'type',
						'value' => $type
					),
					array(
						'type' => 'text',
						'label' => _x( 'Name', 'Income/Expense Accounts', 'vca-asm' ),
						'id' => 'name',
						'value' => ! empty( $data['name'] ) ? $data['name'] : '',
						'desc' => __( 'The (short-)name of the account', 'vca-asm' )
					),
					array(
						'type' => 'text',
						'label' => __( 'Account ID', 'vca-asm' ),
						'id' => 'value',
						'value' => ! empty( $data['value'] ) ? $data['value'] : '',
						'desc' => __( 'The ID of the account, usually numeric', 'vca-asm' )
					),
					array(
						'type' => 'text',
						'label' => __( 'Description', 'vca-asm' ),
						'id' => 'description',
						'value' => ! empty( $data['description'] ) ? $data['description'] : '',
						'desc' => __( 'A description of what all should be booked under this account', 'vca-asm' ) . ' (' . __( 'This is, what the Finances-SPOC sees.', 'vca-asm' ) . ')'
					),
					array(
						'type' => 'hidden',
						'id' => 'related_id',
						'value' => $this->admin_nation
					)
				)
			)
		);

		$form = new VCA_ASM_Admin_Form( array(
			'echo' => false,
			'form' => true,
			'name' => 'vca-asm-finances-settings-form',
			'method' => 'post',
			'metaboxes' => true,
			'js' => false,
			'url' => $url,
			'action' => $form_action,
			'button' => __( 'Save', 'vca-asm' ),
			'button_id' => 'submit',
			'top_button' => true,
			'has_cap' => true,
			'fields' => $fields,
			'back' => true,
			'back_url' => $url
		));

		$output .= $form->output();

		$output .= $adminpage->bottom();

		echo $output;
	}


} // class

endif; // class exists

?>