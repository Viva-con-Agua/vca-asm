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
	 * Controller for the Finances Admin Menu
	 *
	 * @since 1.2
	 * @access public
	 */
	public function ff_control() {
		echo '<div class="wrap">' .
			'<div id="icon-finances" class="icon32-pa"></div><h2>(Zellen-)Finanzen</h2>';
		$feech = new VCA_ASM_Admin_Future_Feech( array(
			'title' => '(Zellen-)Finanzen',
			'version' => '1.5.1',
			'explanation' => 'Hier werden in Zukunft die Spenden- und Wirtschaftskonten der Zellen verwaltet werden können.'
		));
		$feech->output();
		echo '</div>';
	}

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

		if ( isset( $_GET['todo'] ) && 'balance' === $_GET['todo'] ) {
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
		}

		if ( 'city' === $this->cap_lvl ) {
			$this->overview_city();
		} else {
			$this->overview_city();//$this->overview_global();
		}
	}

	private function overview_city( $messages = array() )
	{
		global $vca_asm_finances;

		$url = '?page=vca-asm-finances';

		$city = ! empty( $_GET['city'] ) && in_array( $this->cap_lvl, array( 'global', 'national' ) ) ? $_GET['city'] : $this->admin_city;

		$the_city_finances = new VCA_ASM_City_Finances( $city );

		$balanced_time_econ = explode( '-', $vca_asm_finances->get_balanced_month( $city, 'econ' ) );
		$balanced_time_don = explode( '-', $vca_asm_finances->get_balanced_month( $city, 'donations' ) );
		$balanced_year_econ = $balanced_time_econ[0];
		$balanced_month_econ = $balanced_time_econ[1];
		$balanced_year_don = $balanced_time_don[0];
		$balanced_month_don = $balanced_time_don[1];
		$balanced_stamp_econ = strtotime( $balanced_year_econ . '/' . $balanced_month_econ . '/01' );
		$balanced_stamp_don = strtotime( $balanced_year_don . '/' . $balanced_month_don . '/01' );
		$donations_total = $vca_asm_finances->get_donations( $city );
		$donations_current_year = 0;//$vca_asm_finances->get_donations( $city, true );

		$start_date = strtotime( strftime( '%Y' ) . '/' . strftime( '%m' ) . '/01 -1 month' );
		$end_date_econ = strtotime( $balanced_year_econ . '/' . $balanced_month_econ . '/01' );
		$end_date_don = strtotime( $balanced_year_don . '/' . $balanced_month_don . '/01' );

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

		$adminpage = new VCA_ASM_Admin_Page( array(
			'icon' => 'icon-finances',
			'title' => _x( 'Finances Overview', 'Admin Menu', 'vca-asm' ),
			'messages' => $messages,
			'url' => $url
		));

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
				'button_id' => 'submit',
				'top_button' => false,
				'confirm' => true,
				'confirm_text' => __( 'Are you sure? This process is irreversible...', 'vca-asm' ),
				'back' => false,
				'has_cap' => true,
				'fields' => $fields
			));
		}

		$output = $adminpage->top();

		$output .= $mbs->top();

		$output .= $mbs->mb_top();

		$output .= '<p>' . __( 'Structural Account', 'vca-asm' ) . ': <strong>' . number_format( $the_city_finances->balance_econ/100, 2, ',', '.' ) . ' &euro;</strong></p>' .
			'<p>' . __( 'Donations (Cash) Account', 'vca-asm' ) . ': <strong>' . number_format( $the_city_finances->balance_don/100, 2, ',', '.' ) . ' &euro;</strong></p>';
		foreach ( $the_city_finances->donations_years as $year => $amount ) {
			if ( 'total' !== $year ) {
				$output .= '<p>' . sprintf( _x( 'Donations in %s', 'Placeholder is a year', 'vca-asm' ), $year ) . ': <strong>' . number_format( $amount/100, 2, ',', '.' ) . ' &euro;</strong></p>';
			}
		}
		$output .= '<p>' . __( 'Donations, total', 'vca-asm' ) . ': <strong>' . number_format( $the_city_finances->donations_total/100, 2, ',', '.' ) . ' &euro;</strong></p>';
		$output .= $mbs->mb_bottom();

		$output .= $mbs->mb_top( array( 'title' => __( 'Receipts', 'vca-asm' ) ) );
		$output .= '<p>' . __( 'need to be sent', 'vca-asm' ) . ': ';
		$output .= ! empty( $the_city_finances->late_receipts ) ? '<strong>' . implode( ', ', $the_city_finances->late_receipts ) . '</strong>' : '<em>' . __( 'No late receipts...', 'vca-asm' ) . '</em>';
		$output .= '</p>';
		$output .= '<p>' . __( 'this month', 'vca-asm' ) . ': ';
		$output .= ! empty( $the_city_finances->current_receipts ) ? '<strong>' . implode( ', ', $the_city_finances->current_receipts ) . '</strong>' : '<em>' . __( 'No current receipts...', 'vca-asm' ) . '</em>';
		$output .= '</p>';
		$output .= '<p>' . __( 'have been sent', 'vca-asm' ) . ': ';
		$output .= ! empty( $the_city_finances->sent_receipts ) ? '<strong>' . implode( ', ', $the_city_finances->sent_receipts ) . '</strong>' : '<em>' . __( 'No receipts waiting for confirmation...', 'vca-asm' ) . '</em>';
		$output .= '</p>';

		$output .= $mbs->mb_bottom();

		$output .= $mbs->mb_top( array( 'title' => __( 'Monthly Balancing', 'vca-asm' ) ) );
		$output .= '<p>' . __( 'Last balanced month, Structural', 'vca-asm' ) . ': <strong>' . strftime( '%B %Y', $balanced_stamp_econ );
		if ( ! $balanced_econ ) {
			$output .= ' <span style="color:red">(' . __( 'Needs Balancing!', 'vca-asm' ) . ')</span>';
		}
		$output .= '</strong></p>';
		$output .= '<p>' . __( 'Last balanced month, Donations', 'vca-asm' ) . ': <strong>' . strftime( '%B %Y', $balanced_stamp_don );
		if ( ! $balanced_don ) {
			$output .= ' <span style="color:red">(' . __( 'Needs Balancing!', 'vca-asm' ) . ')</span>';
		}
		$output .= '</strong></p>';
		if ( $balanced !== 2 ) {
			$output .= $form->output();
		} else {
			$output .= '<p><em>' .
					__( 'The account is up to date!', 'vca-asm' ) .
				'</em></p>';
		}

		$output .= $mbs->mb_bottom();

		$output .= $mbs->bottom();

		$output .= $adminpage->bottom();

		echo $output;
	}

	private function overview_global( $messages = array() )
	{
		$adminpage = new VCA_ASM_Admin_Page( array(
			'icon' => 'icon-finances',
			'title' => _x( 'Finances Overview', 'Admin Menu', 'vca-asm' ),
			'messages' => $messages,
			'url' => '?page=vca-asm-finances'
		));

		$mbs = new VCA_ASM_Admin_Metaboxes( array(
			'echo' => false,
			'columns' => 1,
			'running' => 1,
			'id' => '',
			'title' => __( 'Whatever', 'vca-asm' ),
			'js' => false
		));

		$output = $adminpage->top();

		$output .= $mbs->mb_top();
		$output .= '<p>Whatever indeed.</p>';
		$output .= $mbs->mb_bottom();

		$output .= $mbs->bottom();

		$output .= $adminpage->bottom();

		echo $output;
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
		global $wpdb,
			$vca_asm_finances, $vca_asm_geography;

		$validation = new VCA_ASM_Validation();

		$messages = array();
		$page = isset( $_GET['page'] ) ? $_GET['page'] : 'vca-asm-finances-accounts-donations';
		$acc_type = array_pop( explode( '-', $page ) );
		$acc_type = isset( $_GET['acc_type'] ) ? $_GET['acc_type'] : $acc_type;
		$type = isset( $_GET['type'] ) ? $_GET['type'] : 'donation';
		$cid = isset( $_GET['cid'] ) ? $_GET['cid'] : $this->admin_city;

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
						'page' => $page
					));
				break;

				case "save":
					$insert = isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ? false : true;
					$fields = $this->create_fields( $acc_type.'-'.$type );
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
								break;

								case 'date':
									$data[$field['id']] = $validation->is_date( $_POST[$field['id']] );
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

								case 'note':
									// do nothing
								break;

								default:
									$data[$field['id']] = $_POST[$field['id']];
									$format[] = '%s';
							}
						}
					}


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
					header( 'Location: ' . strtok( $_SERVER['REQUEST_URI'], '?' ) . '?page=' . $page . '&todo=saved&id=' . $id );
				break;

				case "saved":
					$messages[] = array(
						'type' => 'message',
						'message' => __( 'Entry booked.', 'vca-asm' )
					);
					$this->single_view( array(
						'messages' => $messages,
						'account_type' => $acc_type,
						'city_id' => $cid,
						'page' => $page
					));
				break;

				case "edit":
					$this->edit( array(
						'id' => $_GET['id'],
						'account_type' => $acc_type,
						'page' => $page
					));
				break;

				case "new":
					$this->edit( array(
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
						'page' => $page
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
			$title = _x( 'Donation Accounts', ' Admin Menu', 'vca-asm' );
		} else {
			$title = _x( 'Economical Accounts', ' Admin Menu', 'vca-asm' );
		}

		$nation_id = 'global' === $this->cap_lvl ? 0 : $this->admin_nation;
		$accounts = $vca_asm_finances->get_accounts( $account_type, $nation_id );

		$adminpage = new VCA_ASM_Admin_Page( array(
			'icon' => 'icon-finances',
			'title' => $title,
			'messages' => $messages,
			'url' => '?page=' . $page
		));

		$columns = array(
			array(
				'id' => 'name',
				'title' => __( 'City', 'vca-asm' ),
				'sortable' => true,
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
				'sortable' => true,
				'conversion' => 'balance'
			)
		);

		$rows = array();
		$i = 0;
		foreach ( $accounts as $account ) {
			$rows[$i] = $account;
			$rows[$i]['name'] = $vca_asm_geography->get_name( $account['city_id'] );
			$rows[$i]['balance'] = number_format( intval( $vca_asm_finances->get_balance( $account['city_id'], $account_type ) )/100, 2, ',', '.' );
			$i++;
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
				'icon' => 'icon-finances'
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
				'icon' => 'icon-plus'
			);
			$tabs[] = array(
				'title' => _x( 'Expenditures', ' Admin Menu', 'vca-asm' ),
				'value' => 'expenditure',
				'icon' => 'icon-minus'
			);
		}
		$tabs[] = array(
			'title' => _x( 'Transfers', ' Admin Menu', 'vca-asm' ),
			'value' => 'transfer',
			'icon' => 'icon-transfers'
		);

		$button = '';
		if ( $this->has_cap ) {
			if ( $active_tab !== 'all' ) {
				$button .= '<form method="post" action="admin.php?page=' . $page . '&todo=new&type=' . $active_tab . '&acc_type=' . $account_type . '">' .
					'<input type="submit" class="button-secondary" value="+ ' . sprintf( __( 'add %s', 'vca-asm' ), $vca_asm_finances->types_to_nicenames[$tab] ) . '" />' .
				'</form>';
			} else {
				$button .= '<div>';
				foreach ( $possible_tabs as $tab ) {
					if ( 'all' !== $tab ) {
						$button .= '<form method="post" style="display:inline" action="admin.php?page=' . $page . '&todo=new&type=' . $tab . '&acc_type=' . $account_type . '">' .
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
			'page' => $page
		);

		$adminpage = new VCA_ASM_Admin_Page( array(
			'icon' => 'icon-finances',
			'title' => $title,
			'messages' => $messages,
			'url' => '?page=' . $page . '&cid=' . $city_id,
			'back' => $back,
			'back_url' => '?page=' . $page,
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
		global $current_user, $vca_asm_finances, $vca_asm_geography, $vca_asm_utilities;

		$default_args = array(
			'city_id' => 0,
			'account_type' => 'donations',
			'transaction_type' => 'all',
			'page' => 'vca-asm-finances-accounts-donations'
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
				'sortable' => true
			),
			array(
				'id' => 'amount',
				'title' => __( 'Amount', 'vca-asm' ),
				'sortable' => true,
				'link' => array(
					'title' => __( 'Edit %s', 'vca-asm' ),
					'title_row_data' => 'name',
					'url' => '?page=' . $page . '&todo=edit&id=%d',
					'url_row_data' => 'id'
				),
				'actions' => array( 'edit', 'delete' ),
				'cap' => 'manage-',
				'conversion' => 'amount'
			)
		);

		if ( ! in_array( $transaction_type, $vca_asm_finances->donations_transactions ) && ! in_array( $transaction_type, $vca_asm_finances->econ_transactions ) ) {
			$columns[] = array(
				'id' => 'transaction_type',
				'title' => __( 'Type', 'vca-asm' ),
				'sortable' => true
			);
		}

		$transactions = $vca_asm_finances->get_transactions( array(
			'city_id' => $city_id,
			'account_type' => $account_type,
			'transaction_type' => $transaction_type,
			'orderby' => 'transaction_date',
			'order' => 'DESC'
		));

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

		$rows = array();
		$i = 0;
		foreach ( $transactions as $transaction ) {
			$rows[$i] = $transaction;
			$rows[$i]['transaction_type_plain'] = $transaction['transaction_type'];
			$rows[$i]['transaction_type'] = $vca_asm_finances->types_to_nicenames[$rows[$i]['transaction_type_plain']];
			$rows[$i]['entry_time_plain'] = $transaction['entry_time'];
			$rows[$i]['entry_time'] = strftime( '%d.%m.%Y %H:%M', $transaction['entry_time'] );
			$rows[$i]['transaction_date_plain'] = $transaction['transaction_date'];
			$rows[$i]['transaction_date'] = strftime( '%d.%m.%Y', $transaction['transaction_date'] );
			$rows[$i]['amount_plain'] = $transaction['amount'];
			$rows[$i]['amount'] = number_format( $transaction['amount']/100, 2, ',', '.' );
			$i++;
		}

		$tbl_args = array(
			'base_url' => '?page=' . $page,
			'sort_url' => '?page=' . $page,
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
			'account_type' => 'donations',
			'messages' => array(),
			'page' => 'vca-asm-finances-accounts-donations'
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

		$url = 'admin.php?page=' . $page . '&acc_type=' . $account_type;
		if ( ! empty( $id ) ) {
			$form_action = $url . '&todo=save&noheader=true&id=' . $id;
			$type = $vca_asm_finances->get_transaction_type( $id );
		} else {
			$form_action = $url . '&todo=save&noheader=true&type=' . $type;
		}

		if( empty( $id ) ) {
			$fields = $this->create_fields( $account_type.'-'.$type );
			$title = sprintf( __( 'Add New %s', 'vca-asm' ), $vca_asm_finances->types_to_nicenames[$type] );
			$transaction_city = 0;
		} else {
			$fields = $this->populate_fields( $id );
			$title = sprintf( __( 'Edit %s', 'vca-asm' ), $vca_asm_finances->types_to_nicenames[$type] );
			$type = $vca_asm_finances->get_transaction_type( $id, false );
			$transaction_city = $vca_asm_finances->get_transaction_city( $id );
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
				( $this->cap_lvl === 'city' && $admin_city === $transaction_city && ! $vca_asm_finances->is_locked( $id ) )
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
			'extra_head_html' => $this->has_cap ? $extra_html : '',
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
				'back_url' => $url . '&tab=' . $type,
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
								'desc' => __( 'How much was gathered?', 'vca-asm' )
							),
							array(
								'type' => 'date',
								'label' => __( 'Date', 'vca-asm' ),
								'id' => 'transaction_date',
								'desc' => __( 'When did you receive the donation?', 'vca-asm' )
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
								'value' => 1
							),
							array(
								'type' => 'text',
								'label' => __( 'Occasion', 'vca-asm' ),
								'id' => 'meta_1',
								'desc' => __( 'On what occasion did you receive the donation?', 'vca-asm' ) . ' (' . __( 'Name of the event, for instance', 'vca-asm' ) . ')'
							),
							array(
								'type' => 'manual_radio',
								'label' => __( 'How?', 'vca-asm' ),
								'id' => 'meta_2',
								'desc' => __( 'In what way was the donation gathered?', 'vca-asm' ),
								'options' => array(
									array(
										'label' => __( 'Cup Deposits', 'vca-asm' ),
										'value' => 'cups'
									),
									array(
										'label' => __( 'Donation Box', 'vca-asm' ),
										'value' => 'can'
									)
								)
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
								'desc' => __( 'How much was gained?', 'vca-asm' )
							),
							array(
								'type' => 'date',
								'label' => __( 'Date', 'vca-asm' ),
								'id' => 'transaction_date',
								'desc' => __( 'When did you gain the revenue?', 'vca-asm' )
							),
							array(
								'type' => 'radio',
								'label' => __( 'Kind of revenue', 'vca-asm' ),//__( 'Income Account', 'vca-asm' ),
								'id' => 'ei_account',
								'options' => $vca_asm_finances->ei_options_array( array(
									'type' => 'income',
									'unclear' => true
								)),
								'desc' => __( 'Of what category is this revenue?', 'vca-asm' )//__( 'Under what category should this revenue be booked?', 'vca-asm' )
							),
							array(
								'type' => 'text',
								'label' => __( 'Occasion', 'vca-asm' ),
								'id' => 'meta_1',
								'desc' => __( 'What was the occasion?', 'vca-asm' ) . ' (' . __( 'Name of the event, for instance', 'vca-asm' ) . ')'
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
								'desc' => __( 'How much was spent?', 'vca-asm' )
							),
							array(
								'type' => 'date',
								'label' => __( 'Date', 'vca-asm' ),
								'id' => 'transaction_date',
								'desc' => __( 'When did you take the money from your cities structural account?', 'vca-asm' )
							),
							array(
								'type' => 'date',
								'label' => __( 'Date of Receipt', 'vca-asm' ),
								'id' => 'receipt_date',
								'desc' => __( 'When was the money spent? (What is the date on the rerceipt?)', 'vca-asm' )
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
									'unclear' => true
								)),
								'desc' => __( 'Of what category is this expenditure?', 'vca-asm' )//__( 'Under what category should this expense be booked?', 'vca-asm' )
							),
							array(
								'type' => 'text',
								'label' => __( 'Occasion', 'vca-asm' ),
								'id' => 'meta_1',
								'desc' => __( 'What occasion did you spend the money for?', 'vca-asm' ) . ' (' . __( 'Name of the event, for instance', 'vca-asm' ) . ')'
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
								'desc' => __( 'How much did you transfer?', 'vca-asm' )
							),
							array(
								'type' => 'date',
								'label' => __( 'Date', 'vca-asm' ),
								'id' => 'transaction_date',
								'desc' => __( 'When did you make the transfer?', 'vca-asm' )
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
								'desc' => __( 'How much did you transfer?', 'vca-asm' )
							),
							array(
								'type' => 'date',
								'label' => __( 'Date', 'vca-asm' ),
								'id' => 'transaction_date',
								'desc' => __( 'When did you make the transfer?', 'vca-asm' )
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
						'value' => 'office'
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
	private function populate_fields( $id ) {
		global $current_user, $wpdb, $vca_asm_finances;

		$type = $vca_asm_finances->get_transaction_type( $id, true );
		$fields = $this->create_fields( $type );

		/* fill fields with existing data */
		$data = $wpdb->get_results(
			"SELECT * FROM " .
			$wpdb->prefix . "vca_asm_finances_transactions " .
			"WHERE id = " . $id . " LIMIT 1", ARRAY_A
		);

		$data = $data[0];

		$bcount = count( $fields );
		for ( $i = 0; $i < $bcount; $i++ ) {
			$fcount = count( $fields[$i]['fields'] );
			for ( $j = 0; $j < $fcount; $j++ ) {
				if ( empty( $_POST['submitted'] ) ) {
					if ( 'direction' === $fields[$i]['fields'][$j]['id'] ) {
						$fields[$i]['fields'][$j]['value'] = 0 > $data['amount'] ? 0 : 1;
					} else {
						$fields[$i]['fields'][$j]['value'] = $data[$fields[$i]['fields'][$j]['id']];
					}
				} else {
					$fields[$i]['fields'][$j]['value'] = $_POST[$fields[$i]['fields'][$j]['id']];
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

		if ( isset( $_GET['tab'] ) && in_array( $_GET['tab' ], array( 'cash-accs', 'cost-centers', 'general', 'ei-accs' ) ) ) {
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
						foreach( array( 'lc', 'cell' ) as $type ) {
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
					$id = isset( $_GET['id'] ) ? $_GET['id'] : NULL;
					$this->settings_edit_cc( array( 'id' => $id ) );
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
								'related_id' => get_user_meta( $current_user->ID, 'nation', true )
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
								'related_id' => get_user_meta( $current_user->ID, 'nation', true )
							),
							array( '%s', '%s', '%s', '%s', '%d' )
						);
						header( 'Location: ' . strtok( $_SERVER['REQUEST_URI'], '?' ) . '?page=vca-asm-finances-settings&tab=cost-centers&todo=saved-cc&id=' . $wpdb->insert_id );
					}

					$this->settings_view( $messages, $active_tab );
				break;

				case "edit-ei":
					$id = isset( $_GET['id'] ) ? $_GET['id'] : NULL;
					$type = isset( $_GET['type'] ) ? $_GET['type'] : 'income';
					$this->settings_edit_ei_account( array( 'id' => $id ) );
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
								'related_id' => get_user_meta( $current_user->ID, 'nation', true )
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
								'related_id' => get_user_meta( $current_user->ID, 'nation', true )
							),
							array( '%s', '%s', '%s', '%s', '%d' )
						);
						header( 'Location: ' . strtok( $_SERVER['REQUEST_URI'], '?' ) . '?page=vca-asm-finances-settings&tab=ei-accs&todo=saved-ei&id=' . $wpdb->insert_id );
					}
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
			}
		} else {
			$this->settings_view( $messages, $active_tab );
		}
	}

	/**
	 *  Settings
	 *
	 * @since 1.5
	 * @access public
	 */
	public function settings_view( $messages = array(), $active_tab = 'general' )
	{
		$url = '?page=vca-asm-finances-settings';
		$adminpage = new VCA_ASM_Admin_Page( array(
			'icon' => 'icon-finances',
			'title' => __( 'Finances Settings' , 'vca-asm' ),
			'messages' => $messages,
			'url' => $url,
			'back' => false,
			'tabs' => array(
				array(
					'title' => _x( 'General', 'Finances Admin Menu', 'vca-asm' ),
					'value' => 'general',
					'icon' => 'icon-finances'
				),
				array(
					'title' => _x( 'Cash Accounts', 'Finances Admin Menu', 'vca-asm' ),
					'value' => 'cash-accs',
					'icon' => 'icon-finances'
				),
				array(
					'title' => _x( 'Cost Centers', 'Finances Admin Menu', 'vca-asm' ),
					'value' => 'cost-centers',
					'icon' => 'icon-finances'
				),
				array(
					'title' => _x( 'Income/Expense Accounts', 'Finances Admin Menu', 'vca-asm' ),
					'value' => 'ei-accs',
					'icon' => 'icon-finances'
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
								'label' => __( 'Structural Cash, Local Crew', 'vca-asm' ),
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
						'label' => __( 'Local Crew', 'vca-asm' ),
						'id' => 'limit-lc-' . $this->admin_nation,
						'value' => $vca_asm_finances->get_limit( $this->admin_nation, 'lc' ),
						'unit' => $vca_asm_geography->get_currency( $this->admin_nation, 'name' )
					),
					array(
						'type' => 'text',
						'label' => __( 'Cell', 'vca-asm' ),
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
		$button = '';
		if ( $this->has_cap ) {
			$button = '<form method="post" action="?page=vca-asm-finances-settings&tab=cost-centers&todo=edit-cc">' .
				'<input type="submit" class="button-secondary" value="+ ' . __( 'add cost center', 'vca-asm' ) . '" />' .
			'</form>';
		}

		$output = '<br />' . $button . '<br />';

		$output .= $this->settings_list_ccs();

		$output .= '<br />' . $button;

		return $output;
	}

	private function settings_list_ccs( $nation = 0 ) //todo extend beyond Germany
	{
		global $current_user, $vca_asm_finances, $vca_asm_utilities;

		$url = '?page=vca-asm-finances-settings&tab=cost-centers';

		$default_order = 'global' === $this->cap_lvl ? 'related_id' : 'value';
		extract( $vca_asm_utilities->table_order( $default_order ) );
		$rows = $vca_asm_finances->get_cost_centers( $orderby, $order );

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
				'actions' => array( 'edit', 'delete' ),
				'cap' => 'finances'
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

		if ( 'global' === $this->cap_lvl ) {
			array_unshift( $columns, array(
				'id' => 'related_id',
				'title' => __( 'Country', 'vca-asm' ),
				'sortable' => true
			));
		}

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
			'messages' => array()
		);
		extract( wp_parse_args( $args, $default_args ), EXTR_SKIP );

		$url = '?page=vca-asm-finances-settings&tab=cost-centers';

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
					)
				)
			)
		);

		if ( 'global' === $this->cap_lvl ) {
			$fields[0]['fields'][] =  array(
				'type' => 'select',
				'id' => 'related_id',
				'label' => __( 'Country', 'vca-asm' ),
				'options' => $vca_asm_geography->options_array(array(
					'orderby' => 'name',
					'order' => 'ASC',
					'type' => 'nation'
				)),
				'value' => $this->admin_nation,
				'desc' => __( 'This cost center is for the books of this country', 'vca-asm' )
			);
		} else {
			$fields[0]['fields'][] = array(
				'type' => 'hidden',
				'id' => 'related_id',
				'value' => $this->admin_nation
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

		$button = '';
		if ( $this->has_cap ) {
			$button = '<form method="post" style="display:inline" action="?page=vca-asm-finances-settings&tab=ei-accs&todo=edit-ei&type=expense">' .
				'<input type="submit" class="button-secondary margin" value="+ ' . __( 'add Expense Account', 'vca-asm' ) . '" />' .
			'</form>' .
			'<form method="post" style="display:inline" action="?page=vca-asm-finances-settings&tab=ei-accs&todo=edit-ei&type=income">' .
				'<input type="submit" class="button-secondary margin" value="+ ' . __( 'add Income Account', 'vca-asm' ) . '" />' .
			'</form>';
		}

		$output = '<br />' . $button . '<br />';

		$output .= '<h3>' . __( 'Expense Accounts', 'vca-asm' ) . '</h3>';
		$output .= $this->settings_list_ei_accs( 'expense' );

		$output .= '<h3>' . __( 'Income Accounts', 'vca-asm' ) . '</h3>';
		$output .= $this->settings_list_ei_accs( 'income' );

		$output .= '<br />' . $button;

		return $output;
	}

	private function settings_list_ei_accs( $type = 'income', $nation = 0 ) //todo extend beyond Germany
	{
		global $current_user, $vca_asm_finances, $vca_asm_utilities;

		$url = '?page=vca-asm-finances-settings&tab=ei-accs';

		$default_order = 'global' === $this->cap_lvl ? 'related_id' : 'value';
		extract( $vca_asm_utilities->table_order( $default_order ) );
		$rows = $vca_asm_finances->get_ei_accounts( $orderby, $order, $type );

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
				'cap' => 'finances'
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

		if ( 'global' === $this->cap_lvl ) {
			array_unshift( $columns, array(
				'id' => 'related_id',
				'title' => __( 'Country', 'vca-asm' ),
				'sortable' => true
			));
		}

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
			'messages' => array()
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
						'desc' => __( 'A description of what all should be booked under this account', 'vca-asm' )
					)
				)
			)
		);

		if ( 'global' === $this->cap_lvl ) {
			$fields[0]['fields'][] =  array(
				'type' => 'select',
				'id' => 'related_id',
				'label' => __( 'Country', 'vca-asm' ),
				'options' => $vca_asm_geography->options_array(array(
					'orderby' => 'name',
					'order' => 'ASC',
					'type' => 'nation'
				)),
				'value' => $this->admin_nation,
				'desc' => __( 'This cost center is for the books of this country', 'vca-asm' )
			);
		} else {
			$fields[0]['fields'][] = array(
				'type' => 'hidden',
				'id' => 'related_id',
				'value' => $this->admin_nation
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