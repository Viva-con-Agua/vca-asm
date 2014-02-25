<?php

/**
 * VCA_ASM_Admin_Emails class.
 *
 * This class contains properties and methods for
 * the email/newsletter interface in the administration backend.
 *
 * Attention: It does not actually handle the sending of emails.
 * @see class VCA_ASM_Mailer for that,
 * contained in /includes/vca-asm-mailer.php
 *
 * @since 1.0
 */

if ( ! class_exists( 'VCA_ASM_Admin_Emails' ) ) :

class VCA_ASM_Admin_Emails {

	/**
	 * Class Properties
	 *
	 * @since 1.2
	 * @access public
	 */
	public $emails_options = array();

	/**
	 * Outputs form to send mails
	 *
	 * @since 1.0
	 * @access public
	 */
	public function sent_control() {
		global $current_user, $wpdb, $vca_asm_geography;
		get_currentuserinfo();
		$admin_region = get_user_meta( $current_user->ID, 'city', true );

		if( isset( $_GET['todo'] ) && $_GET['todo'] == 'send' ) {
			/* send it! */
			$message = $this->mail_send();
		} else {
			$message = '';
		}

		$url = "admin.php?page=vca-asm-emails";
		$sort_url = $url;

		/* table order */
		if( isset( $_GET['orderby'] ) ) {
			$orderby = $_GET['orderby'];
		} else {
			$orderby = 'time';
		}
		if( isset( $_GET['order'] ) ) {
			$order = $_GET['order'];
			if( $order == 'ASC') {
				$toggle_order = 'DESC';
			} else {
				$toggle_order = 'ASC';
			}
		} else {
			$order = 'DESC';
			$toggle_order = 'ASC';
		}

		/* query arguments */
		$where = '';
		if( ! ( $current_user->has_cap('vca_asm_view_emails_global') || $current_user->has_cap('vca_asm_view_emails_nation') ) ) {
			$where = ' WHERE sent_by = ' . $current_user->ID;
		}

		if( isset( $_GET['search'] ) &&
		   1 ==  $_GET['search'] &&
		   ( isset( $_POST['term'] ) || isset( $_GET['term'] ) )
		) {
			if( isset( $_POST['term'] ) ) {
				$term = $_POST['term'];
			} else {
				$term = $_GET['term'];
			}
			$sort_url .= '&amp;search=1&amp;term=' . $term;
			if( empty( $where ) ) {
				$where = ' WHERE subject LIKE "%' . $term . '%"';
			} else {
				$where .= ' AND subject LIKE "%' . $term . '%"';
			}
		}

		if( isset( $_GET['filter'] ) &&
		   1 ==  $_GET['filter'] &&
		   ( isset( $_POST['sent-by-filter'] ) || isset( $_GET['sent-by-filter'] ) )
		) {
			if( isset( $_POST['sent-by-filter'] ) ) {
				$sbf = $_POST['sent-by-filter'];
			} else {
				$sbf = $_GET['sent-by-filter'];
			}
			$sort_url .= '&amp;filter=1&amp;sent-by-filter=' . $sbf;
			if( $sbf === 'own' ) {
				if( empty( $where ) ) {
					$where = ' WHERE sent_by = ' . $current_user->ID;
				} else {
					$where .= ' AND sent_by = ' . $current_user->ID;
				}
			}
		}

		$emails_raw = $wpdb->get_results(
			"SELECT * FROM " . $wpdb->prefix."vca_asm_emails" .
			$where, ARRAY_A
		);

		$emails = array();
		$i = 0;
		foreach ( $emails_raw as $key => $mail ) {
			$emails[$i] = $emails_raw[$key];
			$user = new WP_User( $mail['sent_by'] );
			if( 'Head' === mb_substr( $user->first_name, 0, 4 ) ) {
				$by =  $vca_asm_geography->get_status( get_user_meta( $user->ID, 'city', true ) ) . ' ' . $user->last_name;
			} else {
				$by = trim( $user->first_name . ' ' .$user->last_name );
			}
			$emails[$i]['sent_by'] = $by;
			if( $emails[$i]['membership'] == 2 ) {
				$mem = __( 'active members', 'vca-asm' );
			} else {
				$mem = __( 'all', 'vca-asm' );
			}
			if( $emails[$i]['receipient_group'] == 'region' ) {
				$receipient = $vca_asm_geography->get_name( $emails[$i]['receipient_id'] );
				$emails[$i]['to'] = $receipient . ' (' . $mem . ')';
			} elseif( $emails[$i]['receipient_group'] == 'all' ) {
				$receipient = __( 'All Supporters', 'vca-asm' );
				$emails[$i]['to'] = $receipient . ' (' . $mem . ')';
			} elseif( $emails[$i]['receipient_group'] == 'ho' ) {
				$emails[$i]['to'] = __( 'All Head Ofs', 'vca-asm' );
			} elseif( $emails[$i]['receipient_group'] == 'admins' ) {
				$emails[$i]['to'] = __( 'Office / Administrators', 'vca-asm' );
			} else {
				$emails[$i]['to'] = __( 'Selection', 'vca-asm' );
			}
			$emails[$i]['from'] = preg_replace( '/<|>/', '', $emails[$i]['from'] );
			$i++;
		}
		$emails = $this->sort_by_key( $emails, $orderby, $order );

		$email_count = count( $emails );


		if( isset( $_GET['search'] ) && $_GET['search'] == 1 ) {
			$table_headline = str_replace( '%results%', $email_count, str_replace( '%term%', $term, _x( 'Showing %results% search results for &quot;%term%&quot;', 'Admin Supporters', 'vca-asm' ) ) );
		}
		if( isset( $_GET['filter'] ) && $_GET['filter'] == 1 && $sbf === 'own' ) {
			$table_headline = empty( $table_headline ) ? _x( 'Showing all Emails sent by you', 'Admin Supporters', 'vca-asm' ) : $table_headline . ' ' . _x( 'in Emails sent by you', 'Admin Supporters', 'vca-asm' );
		}
		$table_headline = empty( $table_headline ) ? _x( 'All Sent Items', 'Admin Emails', 'vca-asm' ) : $table_headline;

		if( $email_count > 100 ) {
			$cur_page = isset( $_GET['p'] ) ? $_GET['p'] : 1;
			$pagination_offset = 100 * ( $cur_page - 1 );
			$total_pages = ceil( $email_count / 100 );
			$cur_end = $total_pages == $cur_page ? $pagination_offset + ( $email_count % 100 ) : $pagination_offset + 100;
			$pagination_url =
				str_replace( '{', '%lcurl%',
					str_replace( '}', '%rcurl%',
						str_replace( ':', '%colon%',
							$sort_url
						)
					)
				) .
				'&orderby=' . $orderby . '&order=' . $order . '%_%';

			$pagination_html = paginate_links( array(
				'base' => $pagination_url,
				'format' => '&p=%#%#tbl',
				'prev_text' => __( '&laquo; Previous', 'vca-asm' ),
				'next_text' => __( 'Next &raquo;', 'vca-asm' ),
				'total' => $total_pages,
				'current' => $cur_page,
				'end_size' => 1,
				'mid_size' => 2,
			));
			$pagination_html = str_replace( '%colon%', ':', str_replace( '%lcurl%', '{', str_replace( '%rcurl%', '}', $pagination_html ) ) );

		} else {
			$cur_page = 1;
			$pagination_offset = 0;
			$pagination_html = '';
			$cur_end = $email_count;
		}

		$rows = array();
		for ( $i = $pagination_offset; $i < $cur_end; $i++ ) {
			$rows[$i]['id'] = $emails[$i]['id'];
			$rows[$i]['time'] = strftime( '%d. %B %G, %H:%M', $emails[$i]['time'] );
			$rows[$i]['sent_by'] = $emails[$i]['sent_by'];
			$rows[$i]['from'] = $emails[$i]['from'];
			$rows[$i]['to'] = $emails[$i]['to'];
			$rows[$i]['subject'] = $emails[$i]['subject'];
		}

		$columns = array(
			array(
				'id' => 'time',
				'title' => __( 'Date &amp; Time', 'vca-asm' ),
				'sortable' => true
			)
		);
		if( $current_user->has_cap('vca_asm_send_global_emails') && 'own' !== $sbf ) {
			$columns[] = array(
				'id' => 'sent_by',
				'title' => __( 'Sent by', 'vca-asm' ),
				'sortable' => true
			);
		}
		$columns[] = array(
			'id' => 'from',
			'title' => __( 'From', 'vca-asm' ),
			'sortable' => true
		);
		$columns[] = array(
			'id' => 'to',
			'title' => __( 'To', 'vca-asm' ),
			'sortable' => true
		);
		$columns[] = array(
			'id' => 'subject',
			'title' => __( 'Subject', 'vca-asm' ),
			'sortable' => true,
			'actions' => 'emails'
		);

		$search_fields = array(
			array(
				'type' => 'text',
				'label' =>  _x( 'Search Subjects', 'Admin Emails', 'vca-asm' ),
				'id' => 'term',
				'desc' => _x( "You can search the sent mails by subject.", 'Admin Emails', 'vca-asm' )
			)
		);
		$filter_fields = array(
			array(
				'type' => 'radio',
				'label' => _x( 'Sent by', 'Admin Emails', 'vca-asm' ),
				'id' => 'sent-by-filter',
				'options' => array(
					array(
						'label' => _x( 'All', 'Admin Emails', 'vca-asm' ),
						'value' => 'all'
					),
					array(
						'label' => _x( 'Only own', 'Admin Emails', 'vca-asm' ),
						'value' => 'own'
					)
				),
				'desc' => _x( "Limit to your own mails.", 'Admin Emails', 'vca-asm' ),
				'value' => isset( $sbf ) ? $sbf : 'all'
			)
		);

		$skip_wrap = true;

		$output = '<div class="wrap">' .
				'<div id="icon-emails" class="icon32-pa"></div>' .
				'<h2>' . __( 'Sent Items', 'vca-asm' ) . '</h2>';

		$output .= $message .
			'<h3 class="title title-top-pa">' . _x( 'Search', 'Admin Emails', 'vca-asm' ) . '</h3>' .
			'<form name="vca_asm_email_search" method="post" action="'.$url .'&amp;search=1';
						if( isset( $sbf ) ) {
							$output .= '&amp;filter=1';
						}
					$output .= '">' .
				'<input type="hidden" name="search-submitted" value="y"/>';
				if( isset( $sbf ) ) {
					$output .= '<input type="hidden" name="sent-by-filter" value="' . $sbf . '"/>';
				}
				$fields = $search_fields;
				require( VCA_ASM_ABSPATH . '/templates/admin-form.php' );
				$output .= '<input type="submit" name="submit" id="submit" class="button-primary"' .
						' value="' . _x( 'Search', 'Admin Emails', 'vca-asm' ) .
					'"></form>';

				if( current_user_can( 'vca_asm_send_global_emails' ) ) {
					$output .= '<h3 class="title title-top-pa">' . _x( 'Filter', 'Admin Supporters', 'vca-asm' ) . '</h3>' .
					'<form name="vca_asm_supporter_filter" method="post" action="'.$url .'&amp;filter=1';
						if( isset( $term ) ) {
							$output .= '&amp;search=1';
						}
					$output .= '">' .
						'<input type="hidden" name="filter-submitted" value="y"/>';
					if( isset( $term ) ) {
						$output .= '<input type="hidden" name="term" value="' . $term . '"/>';
					}
					$fields = $filter_fields;
					require( VCA_ASM_ABSPATH . '/templates/admin-form.php' );
					$output .= '<input type="submit" name="submit" id="submit" class="button-primary"' .
						' value="' . _x( 'Filter', 'Admin Supporters', 'vca-asm' ) .
						'"></form>';
				}

				$output .= '<h3 id="tbl" class="title title-top-pa">' . $table_headline . '</h3>' .
					'<form action="" class="bulk-action-form" method="get">' .
					'<input type="hidden" name="page" value="vca-asm-emails" />' .
					'<div class="tablenav top">' .
						'<div class="tablenav-pages">' .
						'<span class="displaying-num">' . sprintf( __( '%d Sent Items', 'vca-asm' ), $email_count ) . '</span>' .
						'<span class="pagination-links">' . $pagination_html . '</span></div>' .
					'</div>';
				require( VCA_ASM_ABSPATH . '/templates/admin-table.php' );
				$output .= '<div class="tablenav bottom">' .
						'<div class="tablenav-pages">' .
						'<span class="displaying-num">' . sprintf( __( '%d Sent Items', 'vca-asm' ), $email_count ) . '</span>' .
						'<span class="pagination-links">' . $pagination_html . '</span></div>' .
					'</div></form>';

		$output .= '</div>';

		if( isset( $term ) || isset( $sbf ) ) {
			$output .= '<form name="vca_asm_amails_all" method="post" action="admin.php?page=vca-asm-emails">' .
					'<input type="hidden" name="submitted" value="y"/>' .
					'<p class="submit">' .
						'<input type="submit" name="submit" id="submit" class="button-primary"' .
							' value="' . _x( 'Show all Emails', 'Admin Supporters', 'vca-asm' ) .
				'"></p></form>';
		}

		echo $output;
	}

	/**
	 * Sorting Methods
	 *
	 * @since 1.0
	 * @access private
	 */
	private function sort_by_key( $arr, $key, $order ) {
	    global $vca_asm_key2sort;
		$vca_asm_key2sort = $key;
		if( $order == 'DESC' ) {
			usort( $arr, array(&$this, 'sbk_cmp_desc') );
		} else {
			usort( $arr, array(&$this, 'sbk_cmp_asc') );
		}
		return ( $arr );
	}
	private function sbk_cmp_asc( $a, $b ) {
		global $vca_asm_key2sort;
		$encoding = mb_internal_encoding();
		return strcmp( mb_strtolower( $a[$vca_asm_key2sort], $encoding ), mb_strtolower( $b[$vca_asm_key2sort], $encoding ) );
	}
	private function sbk_cmp_desc( $b, $a ) {
		global $vca_asm_key2sort;
		$encoding = mb_internal_encoding();
		return strcmp( mb_strtolower( $a[$vca_asm_key2sort], $encoding ), mb_strtolower( $b[$vca_asm_key2sort], $encoding ) );
	}

	/**
	 * Outputs form to send mails
	 *
	 * @since 1.0
	 * @access public
	 */
	public function compose_control() {
		global $wpdb, $current_user, $vca_asm_geography;
		get_currentuserinfo();

		$admin_region = get_user_meta( $current_user->ID, 'city', true );

		wp_enqueue_script( 'vca-asm-admin-email-preview' );
		$params = array(
			'url' => get_option( 'siteurl' ),
			'btnVal' => __( 'Preview', 'vca-asm' )
		);
		wp_localize_script( 'vca-asm-admin-email-preview', 'emailParams', $params );

		$initial = array();
		if ( isset( $_GET['id'] ) ) {
			$email_query = $wpdb->get_results(
				"SELECT subject, message, sent_by FROM " . $wpdb->prefix."vca_asm_emails" .
				" WHERE id = " . $_GET['id'] . " LIMIT 1", ARRAY_A
			);
			$initial['sent_by'] = $email_query[0]['sent_by'];
			if ( $current_user->has_cap( 'vca_asm_send_global_emails' ) || $current_user->has_cap( 'vca_asm_send_emails' ) && $current_user->ID == $initial['sent_by'] ) {
				$initial['message'] = $email_query[0]['message'];
				$initial['subject'] = $email_query[0]['subject'];
			}
		}

		/* form parameters */
		$url = "admin.php?page=vca-asm-emails";
		$form_action = $url . "&amp;todo=send";

		if( isset( $_GET['uid'] ) ) {
			$user_obj = new WP_User( intval( $_GET['uid'] ) );
			$name = $user_obj->first_name . ' ' . $user_obj->last_name;
			$receipient_field = array(
				'type' => 'hidden',
				'label' => _x( 'Receipient', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'receipient',
				'value' => 'single'.$user_obj->user_email,
				'desc' => sprintf( _x( 'You are writing to a single supporter: %s.', 'Admin Email Interface', 'vca-asm' ), $name )
			);
		} elseif( isset( $_GET['email'] ) ) {
			$user_obj = get_user_by( 'email', $_GET['email'] );
			$name = $user_obj->first_name . ' ' . $user_obj->last_name;
			$receipient_field = array(
				'type' => 'hidden',
				'label' => _x( 'Receipient', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'receipient',
				'value' => 'single'.$_GET['email'],
				'desc' => sprintf( _x( 'You are writing to a single supporter: %s.', 'Admin Email Interface', 'vca-asm' ), $name )
			);
		} elseif( isset( $_GET['sids'] ) ) {
			$receipient_field = array(
				'type' => 'hidden',
				'label' => _x( 'Receipient', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'receipient',
				'value' => 'selection'.$_GET['sids'],
				'desc' => _x( 'You are writing to a selected group of supporters.', 'Admin Email Interface', 'vca-asm' )
			);
		} elseif( isset( $_GET['group'] ) && ( $_GET['group'] == 'participants' || $_GET['group'] == 'applicants' || $_GET['group'] == 'applicants_global' || $_GET['group'] == 'waiting' ) && isset( $_GET['activity'] ) ) {
			$name = get_the_title( $_GET['activity'] );
			$receipient_field = array(
				'type' => 'hidden',
				'label' => _x( 'Receipient', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'receipient',
				'value' => 'actpart'.$_GET['activity'],
				'desc' => sprintf( _x( 'You are writing to supporters with accepted applications to &quot;%s&quot;.', 'Admin Email Interface', 'vca-asm' ), $name )
			);
			if( $_GET['group'] == 'applicants' ) {
				$receipient_field['desc'] = sprintf( _x( 'You are writing to supporters currently applying to &quot;%s&quot;.', 'Admin Email Interface', 'vca-asm' ), $name );
				$receipient_field['value'] = 'actappl'.$_GET['activity'];
			}
			if( $_GET['group'] == 'applicants_global' ) {
				$receipient_field['desc'] = sprintf( _x( 'You are writing to supporters currently applying to &quot;%s&quot; via the global contingent.', 'Admin Email Interface', 'vca-asm' ), $name );
				$receipient_field['value'] = 'actappg'.$_GET['activity'];
			}
			if( $_GET['group'] == 'waiting' ) {
				$receipient_field['desc'] = sprintf( _x( 'You are writing to supporters currently on the waiting list for &quot;%s&quot;.', 'Admin Email Interface', 'vca-asm' ), $name );
				$receipient_field['value'] = 'actwait'.$_GET['activity'];
			}
		} else {
			/* receipients (regions) array for select */
			$nations = $vca_asm_geography->get_all( 'name', 'ASC', 'nation' );
			$cities = $vca_asm_geography->get_all( 'name', 'ASC', 'city' );
			$receipients = array();
			if( in_array( 'administrator', $current_user->roles ) || 3 === $current_user->ID ) {
				$receipients[0] = array(
					'label' => __( 'Testmail to yourself', 'vca-asm' ),
					'value' => 'tm'
				);
			} else {
				$receipients[0] = array(
					'label' => __( 'Please select...', 'vca-asm' ),
					'value' => 'please-select'
				);
			}
			if( current_user_can( 'vca_asm_send_emails_global' ) || current_user_can( 'vca_asm_send_emails_nation' ) ) {
				$receipients[1] = array(
					'label' => __( 'All users of the Pool', 'vca-asm' ),
					'value' => 'all'
				);
				$receipients[2] = array(
					'label' => __( 'All City Users', 'vca-asm' ),
					'value' => 'ho'
				);
				$receipients[3] = array(
					'label' => __( 'Admin Users (besides City Users)', 'vca-asm' ),
					'value' => 'admins'
				);
				$receipients[4] = array(
					'label' => __( 'Supporters with no specific region', 'vca-asm' ),
					'value' => 0
				);
				foreach( $nations as $region ) {
					$receipients[] = array(
						'label' => $region['name'],
						'value' => 'nat' . $region['id']
					);
				}
				foreach( $cities as $region ) {
					switch( $region['type'] ) {
						case 'cell':
							$receipients[] = array(
								'label' =>  $region['name'] . ' (' . __( 'Cell', 'vca-asm' ) . ')',
								'value' => $region['id']
							);
						break;
						case 'lc':
							$receipients[] = array(
								'label' => $region['name'] . ' (' . __( 'Local Crew', 'vca-asm' ) . ')',
								'value' => $region['id']
							);
						break;
						default:
							$receipients[] = array(
								'label' => $region['name'],
								'value' => $region['id']
							);
						break;
					}
				}
			} else {
				$receipients[] = array(
					'label' => sprintf( __( 'Supporters from %s', 'vca-asm' ), $vca_asm_geography->get_name( $admin_region ) ),
					'value' => $admin_region
				);
			}
			$mem_field = array(
				'type' => 'radio',
				'label' => _x( 'Membership?', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'membership',
				'options' => array(
					array(
						'label' => __( 'Active Members', 'vca-asm' ),
						'value' => 2
					),
					array(
						'label' => _x( 'All', 'Admin Email Interface', 'vca-asm' ),
						'value' => 0
					)
				),
				'desc' => _x( 'Select whether to send the email to all supporters of the selected group or to active members only.', 'Admin Email Interface', 'vca-asm' )
			);
			$receipient_field = array(
				'type' => 'select',
				'label' => _x( 'Receipient', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'receipient',
				'options' => $receipients,
				'desc' => _x( 'Select who receives the email. Choose the &quot;Testmail to yourself&quot; to see how it will look in your own inbox.', 'Admin Email Interface', 'vca-asm' )
			);
			$extra_selection = array(
				'type' => 'checkbox',
				'label' => _x( 'Ignore user settings?', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'ignore_switch',
				'desc' => _x( 'As you know from your own profile, users may select which news to receive - general news, regional ones, both or none. In rare cases you have a message so important, that you might want to ignore the users wishes and reach everyone within your selected group. Tick this box to do so. Please do not make use of this feature frequently!', 'Admin Email Interface', 'vca-asm' )
			);
		}

		$fields = array(
			$receipient_field,
			array(
				'type' => 'select',
				'label' => _x( 'Sender', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'sender',
				'options' => array(
					array(
						'label' => 'no-reply@vivaconagua.org',
						'value' => 'nr'
					),
					array(
						'label' => _x( 'Your own email address', 'Admin Email Interface', 'vca-asm' ),
						'value' => 'own'
					)
				),
				'desc' => _x( 'Send the email either from your personal email address or select the generic no-reply.', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'text',
				'label' =>  _x( 'Subject', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'subject',
				'tabindex' => 1,
				'desc' => _x( "The email's subject line", 'Admin Email Interface', 'vca-asm' ),
				'value' => ! empty( $initial['subject'] ) ? $initial['subject'] : ''
			)
		);

		/* this is somewhat legacy, needs improvement */
		if( isset( $extra_selection ) ) {
			$first = array_shift($fields);
			array_unshift( $fields, $first, $extra_selection );
		}
		if( isset( $mem_field ) ) {
			$first = array_shift($fields);
			array_unshift( $fields, $first, $mem_field );
		}

		$format = 'html';
		if( ! in_array( 'city', $current_user->roles ) ) {
			$format = ! empty( $this->emails_options['email_format_admin'] ) ? $this->emails_options['email_format_admin'] : 'html';
		} else {
			$format = ! empty( $this->emails_options['email_format_ho'] ) ? $this->emails_options['email_format_ho'] : 'plain';
		}

		if ( $format !== 'html' ) {
			$fields[] = array(
				'type' => 'textarea',
				'label' =>  _x( 'Message', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'message',
				'tabindex' => 2,
				'desc' => _x( 'Message Body', 'Admin Email Interface', 'vca-asm' ),
				'value' => ! empty( $initial['message'] ) ? $initial['message'] : ''
			);
		}

		$output = '<div class="wrap">' .
				'<div id="icon-write" class="icon32-pa"></div>' .
				'<h2>' . __( 'Send an email', 'vca-asm' ) . '</h2>';

		$output .= '<form name="vca_asm_groupmail_form" method="post" action="' . $form_action . '">' .
					'<input type="hidden" name="submitted" value="y"/>';
						require( VCA_ASM_ABSPATH . '/templates/admin-form.php' );

		echo $output;

		if ( $format === 'html' ) {
			echo '<table class="form-table pool-form"><tbody><tr valign="top"><th scope="row">' .
					'<label for="message">' . _x( 'Message', 'Admin Email Interface', 'vca-asm' ) . '</label>' .
				'</th><td>';

			/* Rich-Text Editor */

			add_filter( 'wp_default_editor', create_function( '', 'return "tinymce";' ) );
			$editor_args = array(
				'media_buttons' => false,
				'textarea_name' => 'message',
				'textarea_rows' => 20,
				'tabindex' => 2,
				'quicktags' => false,
				'tinymce' => array(
					'plugins' => 'fullscreen, paste, spellchecker, tabfocus',
					'content_css' =>  VCA_ASM_RELPATH . 'css/tinymce.css?ver=' . time(),
					'theme_advanced_buttons1' => 'bold,italic,underline,strikethrough,separator,styleselect,formatselect,separator,link,unlink,separator,forecolor',
					'theme_advanced_buttons2' => 'justifyleft,justifycenter,justifyright,justifyfull,separator,bullist,numlist,separator,outdent,indent',
					'theme_advanced_buttons3' => 'charmap,hr,separator,pastetext,pasteword,removeformat,separator,undo,redo,separator,spellchecker,separator,fullscreen',
					'theme_advanced_blockformats' => 'p,h1,h2',
					'theme_advanced_text_colors' => '000000,646567,8F9092,B6B7B9,C5C6C8,D5D6D7,E3E4E5,FFFFFF,008FC1,00A8CF,7EC5E0,C4E3F0,E2007A,E9619C,F19FC1,F9D3E3,002A3D,00586C,588B9B,A9C4CE,A4D8E3,BEE3EB,D5ECF1,EBF7F9,584619,857043,B09E79,D9CDB8,BBA259,CCB882,DDD0AC,EEE7D5,E3CF9A,EBDBB3,F1E6CC,F9F3E6',
					'theme_advanced_more_colors' => false,
					'invalid_elements' => 'form,frame,iframe,object,video',
					'force_hex_style_colors' => true,
					'theme_advanced_path' => false,
					'theme_advanced_resizing' => true,
					'theme_advanced_styles' => 'VcA Link=vca-link',
					'style_formats' => '[{title:"VcA ' . _x( 'Headline', 'Editor Styles', 'vca-asm' ) . '",block:"h1",styles:{color:"#008FC1",background:"transparent",fontSize:"28px",fontWeight:"bold",lineHeight:"1",marginTop:"0",marginRight:"0",marginBottom:"14px",marginLeft:"0",paddingTop:"0",paddingRight:"0",paddingBottom:"0",paddingLeft:"0",fontFamily:"Verdana,Geneva,Arial,Helvetica,sans-serif;-webkit-text-size-adjust:none;"}},' .
						'{title:"VcA ' . _x( 'Subline', 'Editor Styles', 'vca-asm' ) . '",block:"h2",styles:{color:"#002A3D",background:"transparent",fontSize:"18px",fontWeight:"bold",lineHeight:"1.1666667",marginTop:"0",marginRight:"0",marginBottom:"21px",marginLeft:"0",paddingTop:"0",paddingRight:"0",paddingBottom:"0",paddingLeft:"0",fontFamily:"Verdana,Geneva,Arial,Helvetica,sans-serif;-webkit-text-size-adjust:none;"}},' .
						'{title:"VcA ' . _x( 'Lead Paragraph', 'Editor Styles', 'vca-asm' ) . '",block:"p",styles:{color:"#00586C",background:"transparent",fontSize:"14px",fontWeight:"bold",lineHeight:"1.5",marginTop:"0",marginRight:"0",marginBottom:"21px",marginLeft:"0",paddingTop:"0",paddingRight:"0",paddingBottom:"0",paddingLeft:"0",fontFamily:"Verdana,Geneva,Arial,Helvetica,sans-serif;-webkit-text-size-adjust:none;"}},' .
						'{title:"VcA ' . _x( 'regular Paragraph', 'Editor Styles', 'vca-asm' ) . '",block:"p",styles:{color:"#0B0B0B",background:"transparent",fontWeight:"400",fontSize:"14px",lineHeight:"1.5",marginTop:"0",marginRight:"0",marginBottom:"21px",marginLeft:"0",paddingTop:"0",paddingRight:"0",paddingBottom:"0",paddingLeft:"0",fontFamily:"Verdana,Geneva,Arial,Helvetica,sans-serif;-webkit-text-size-adjust:none;"}},' .
						'{title:"VcA ' . _x( 'Callout, blue', 'Editor Styles', 'vca-asm' ) . '",block:"p",styles:{color:"#002A3D",fontWeight:"normal",fontSize:"14px",lineHeight:"1.5",background:"#C4E3F0",marginTop:"10px",marginRight:"0",marginBottom:"32px",marginLeft:"0",paddingTop:"21px",paddingRight:"21px",paddingBottom:"21px",paddingLeft:"21px",borderRadius:"22px",fontFamily:"Verdana,Geneva,Arial,Helvetica,sans-serif;-webkit-text-size-adjust:none;"}},' .
						'{title:"VcA ' . _x( 'Callout, magenta', 'Editor Styles', 'vca-asm' ) . '",block:"p",styles:{color:"#00586c",fontWeight:"bold",fontSize:"14px",lineHeight:"1.5",background:"#F19FC1",marginTop:"10px",marginRight:"0",marginBottom:"32px",marginLeft:"0",paddingTop:"21px",paddingRight:"21px",paddingBottom:"21px",paddingLeft:"21px",borderRadius:"22px",fontFamily:"Verdana,Geneva,Arial,Helvetica,sans-serif;-webkit-text-size-adjust: none;"}}]',
					'setup' => 'function(ed){ed.onPostProcess.add(function(ed,o){' .
						'o.content=o.content.replace(/<p>/gi,\'<p style="line-height:1.5;margin-top:0;margin-right:0;margin-bottom:21px;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;color:#0B0B0B;font-size:14px;font-family:Verdana,Geneva,Arial,Helvetica,sans-serif">\');' .
						'o.content=o.content.replace(/<h1>/gi,\'<h1 style="display:block;line-height:1.5;margin-top:0;margin-right:0;margin-bottom:21px;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;color:#0B0B0B;font-size:28px;font-family:Verdana,Geneva,Arial,Helvetica,sans-serif">\');' .
						'o.content=o.content.replace(/<h2>|<h3>|<h4>|<h5>|<h6>/gi,\'<h2 style="display:block;line-height:1.16666667;margin-top:0;margin-right:0;margin-bottom:21px;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;color:#0B0B0B;font-size:18px;font-family:Verdana,Geneva,Arial,Helvetica,sans-serif">\');' .
						'o.content=o.content.replace(/<\/h3>|<\/h4>|<\/h5>|<\/h6>/gi,"<\/h2>");' .
						'o.content=o.content.replace(/<a([^>]*?)>(?:(?!\s*?<span))/gi,"<a$1 style=\"color:inherit;text-decoration:none;border-bottom:1px dotted #008fc1;\"><span style=\"color:inherit;text-decoration:none;border-bottom:1px dotted #008fc1;\"><span>");' .
						'o.content=o.content.replace(/<\/a[^>]*?>(?:(?!\s*?&#8288;))/gi,"<\/span><\/span><\/a>&#8288;");' .
						'o.content=o.content.replace(/<ul(?:[^>]*?)>/gi,"<ul style=\"margin-top:0;margin-right:0;margin-bottom:21px;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:42px;\">");' .
						'o.content=o.content.replace(/<hr(?:[^>]*?)>/gi,"<hr style=\"margin-top:0;margin-right:0;margin-bottom:21px;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;\">");' .
						'o.content=o.content.replace(/<li(?:[^>]*?)>/gi,"<li style=\"color:#0B0B0B;font-size:13px;line-height:21px;font-family:Verdana,Geneva,Arial,Helvetica,sans-serif;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;\">");' .
						'o.content=o.content.replace(/(<h[1-6][^>]*?>)((?:.(?!<\/h))*?)(<\/h[1-6]>)/gi,"$1<span style=\"font-family:\'Gill Sans Bold Condensed\',\'Gill Sans Condensed\',\'Gill Sans Bold\',\'Gill Sans\',\'Gill Sans MT\'\">$2</span>$3");});}'
				)
			);
			$initial['message'] = isset( $initial['message'] ) ? $initial['message'] : '';
			wp_editor( $initial['message'], 'newsletter-editor', $editor_args );

			echo '<br />' .
				'<span class="description">' . _x( 'Message Body', 'Admin Email Interface', 'vca-asm' ) . '</span>' .
				'</td></tr></tbody></table>';
		}
		echo '<p class="submit">' .
					'<input type="submit" name="mail_submit" id="submit" class="button-primary "' .
					' onclick="
						if ( jQuery(\'select#receipient option:selected\').val() == \'please-select\' ) {' .
							'alert(\'' . __( 'Please choose a receipient...', 'vca-asm' ) . '\'); return false;' .
						'} else if ( confirm(\'' .
							__( 'Send Email now?', 'vca-asm' ) .
						'\') ) { return true; } return false;"' .
					' value="' .
					__( 'Send Mail!', 'vca-asm' ) .
					'"></p></form>' .
				'<table class="form-table pool-form"><tbody><tr valign="top"><th scope="row">' .
					'<label>' . _x( 'Help / Tips', 'Admin Email Interface', 'vca-asm' ) . '</label>' .
				'</th><td>' .
					'<p><strong>' . _x( '&quot;Styles&quot; Dropdown Menu', 'Admin Email Interface', 'vca-asm' ) . '</strong><br />' .
					_x( 'Of course you may use all of the above editor&apos;s formatting options, but for almost all cases, the &quot;Styles&quot; Dropdown should suffice. Try to accomplish what you want using these preset styles first.', 'Admin Email Interface', 'vca-asm' ) .
					'</p>' .
					'<p><strong>' . _x( 'Inserting Links', 'Admin Email Interface', 'vca-asm' ) . '</strong><br />' .
					_x( 'You <em>can</em> simply copy and paste URLs (Internet-Addresses) into the Editor, but it looks much nicer, if you write a paragraph first, then select the word(s) or sentence you want to use as a link and then use the &quot;Insert Link&quot; Button (3rd from the right, top row).', 'Admin Email Interface', 'vca-asm' ) .
					'</p>' .
					'<p><strong>' . _x( 'Copy and Pasting from somewhere else', 'Admin Email Interface', 'vca-asm' ) . '</strong><br />' .
					_x( 'You <em>can</em> copy and paste from pretty much anywhere and the previously selected formatting will be preserved. Most times that is not what you want though, as it is not inline with the VcA-specific styling used by default. Hence when copying text from somewhere else, please use the &quot;Copy without formatting&quot; Button (3rd from the left, bottom row). If you must copy formatted text from a Word document, it is safest to use the &quot;Copy from Word&quot; Button (4th from the left, bottom row).', 'Admin Email Interface', 'vca-asm' ) .
					'</p>' .
			'</td></tr></tbody></table></div>';
	}

	/**
	 * Prepares groupmail for sending
	 *
	 * @since 1.0
	 * @access private
	 */
	private function mail_send() {
		global $current_user, $wpdb, $vca_asm_mailer, $vca_asm_geography;
		get_currentuserinfo();

		$membership = 0;
		$receipient_group = 'all';
		$receipient_id = 0;
		$save = true;
		if( isset( $_POST['receipient'] ) && $_POST['receipient'] == 'all' ) {
			$metaqueries = array( 'relation' => 'AND' );
			if( ! isset( $_POST['ignore_switch'] ) ) {
				$metaqueries[] = array(
					'key' => 'mail_switch',
					'value' => array( 'all', 'global' ),
					'compare' => 'IN'
				);
			}
			if( 2 == $_POST['membership'] ) {
				$metaqueries[] = array(
					'key' => 'membership',
					'value' => 2
				);
				$membership = 2;
			}
			$args = array(
				'meta_query' => $metaqueries
			);
			$users = get_users( $args );
			$to = array();
			foreach( $users as $user ) {
				if( ! in_array( 'pending', $user->roles ) ) {
					$to[] = $user->user_email;
				}
			}
		} elseif( isset( $_POST['receipient'] ) && substr( $_POST['receipient'], 0, 6 ) === 'single' ) {
			$to = substr( $_POST['receipient'], 6 );
			$user_obj = get_user_by( 'email', $to );
			$receipient_group = 'single';
			$receipient_id = $user_obj->ID;
		} elseif( isset( $_POST['receipient'] ) && substr( $_POST['receipient'], 0, 9 ) === 'selection' ) {
			$users = unserialize( substr( $_POST['receipient'], 9 ) );
			foreach( $users as $user_id ) {
				$user = new WP_User( $user_id );
				$to[] = $user->user_email;
			}
			$receipient_group = 'selection';
		} elseif( isset( $_POST['receipient'] ) && $_POST['receipient'] == 'tm' ) {
			$receipient_group = 'self';
			$to = $current_user->user_email;
			$save = false;
		} elseif( isset( $_POST['receipient'] ) && $_POST['receipient'] == 'ho' ) {
			$args = array(
				'role' => 'head_of'
			);
			$supporters = get_users( $args );
			$to = array();
			foreach( $supporters as $supporter ) {
				$to[] = $supporter->user_email;
			}
			$receipient_group = 'ho';
		} elseif( isset( $_POST['receipient'] ) && $_POST['receipient'] == 'admins' ) {
			$supporters = array_merge(
				get_users( array( 'role' => 'administrator' ) ),
				get_users( array( 'role' => 'content_admin' ) ),
				get_users( array( 'role' => 'activities' ) ),
				get_users( array( 'role' => 'education' ) ),
				get_users( array( 'role' => 'network' ) )
			);
			$to = array();
			foreach( $supporters as $supporter ) {
				$to[] = $supporter->user_email;
			}
			$receipient_group = 'admins';
		} elseif( isset( $_POST['receipient'] ) && substr( $_POST['receipient'], 0, 3 ) === 'act' ) {
			$subgroup = substr( $_POST['receipient'], 3, 4 );
			$activity_id = intval( substr( $_POST['receipient'], 7 ) );
			$to = array();
			if( $subgroup == 'part' ) {
				$supporters = $wpdb->get_results(
					"SELECT supporter FROM " .
					$wpdb->prefix . "vca_asm_registrations " .
					"WHERE activity = " . $activity_id, ARRAY_A
				);
			} elseif( $subgroup == 'appl' || 'appg' == $subgroup ) {
				$supporters = $wpdb->get_results(
					"SELECT supporter FROM " .
					$wpdb->prefix . "vca_asm_applications " .
					"WHERE activity = " . $activity_id . " AND state = 0", ARRAY_A
				);
			} elseif( $subgroup == 'wait' ) {
				$supporters = $wpdb->get_results(
					"SELECT supporter FROM " .
					$wpdb->prefix . "vca_asm_applications " .
					"WHERE activity = " . $activity_id . " AND state = 1", ARRAY_A
				);
			}
			$to = array();
			foreach( $supporters as $supporter ) {
				$supp_id = $supporter['supporter'];
				if( $subgroup == 'appg' ) {
					$slots_arr = get_post_meta( $activity_id, 'slots', true );
					$user_region = get_user_meta( $supp_id, 'city', true );
					if( $user_region != 0 && array_key_exists( $user_region, $slots_arr ) ) {
						continue;
					}
				}
				$user_obj = new WP_User( $supp_id );
				$to[] = $user_obj->user_email;
			}
			var_dump($to);
			$type = substr( $_POST['receipient'], 3, 4 );
			if( 'appl' === $type || 'appg' === $type ) {
				$receipient_group = 'applicants';
			} elseif( 'wait' === $type ) {
				$receipient_group = 'waiting';
			} else {
				$receipient_group = 'participants';
			}
			$receipient_id = substr( $_POST['receipient'], 7 );
		} elseif( isset( $_POST['receipient'] ) && substr( $_POST['receipient'], 0, 3 ) === 'nat' ) {

			$metaqueries = array( 'relation' => 'AND' );

			if( ! isset( $_POST['ignore_switch'] ) ) {
				$metaqueries[] = array(
					'key' => 'mail_switch',
					'value' => array( 'all', 'regional' ),
					'compare' => 'IN'
				);
			}
			if( 2 == $_POST['membership'] ) {
				$metaqueries[] = array(
					'key' => 'membership',
					'value' => 2
				);
				$membership = 2;
			}
			$metaqueries[] = array(
				'key' => 'nation',
				'value' => intval( substr( $_POST['receipient'], 3 ) )
			);
			$args = array(
				'meta_query' => $metaqueries
			);
			$supporters = get_users( $args );

			$to = array();
			foreach( $supporters as $supporter ) {
				if ( ! in_array( 'city', $supporter->roles ) && ! in_array( 'head_of', $supporter->roles ) ) {
					$to[] = $supporter->user_email;
				}
			}
			$receipient_group = 'region';
			$receipient_id = intval( substr( $_POST['receipient'], 3 ) );

		} elseif( isset( $_POST['receipient'] ) ) {
			$metaqueries = array( 'relation' => 'AND' );
			if( ! isset( $_POST['ignore_switch'] ) ) {
				$metaqueries[] = array(
					'key' => 'mail_switch',
					'value' => array( 'all', 'regional' ),
					'compare' => 'IN'
				);
			}
			if( 2 == $_POST['membership'] ) {
				$metaqueries[] = array(
					'key' => 'membership',
					'value' => 2
				);
				$membership = 2;
			}
			$metaqueries[] = array(
				'key' => 'city',
				'value' => $_POST['receipient']
			);
			$args = array(
				'meta_query' => $metaqueries
			);
			$supporters = get_users( $args );

			$to = array();
			foreach( $supporters as $supporter ) {
				if ( ! in_array( 'city', $supporter->roles ) && ! in_array( 'head_of', $supporter->roles ) ) {
					$to[] = $supporter->user_email;
				}
			}
			$receipient_group = 'region';
			$receipient_id = $_POST['receipient'];
		}

		$format = 'html';
		if( ! in_array( 'head_of', $current_user->roles ) && ! in_array( 'city', $current_user->roles ) ) {
			$from_name = trim( $current_user->first_name . ' ' . $current_user->last_name );
			$format = ! empty( $this->emails_options['email_format_admin'] ) ? $this->emails_options['email_format_admin'] : 'html';
		} else {
			$region_id = get_user_meta( $current_user->ID, 'city', true );
			$region_name = $vca_asm_geography->get_name( $region_id );
			$from_name =  $vca_asm_geography->get_status( $region_id ) . ' ' . $region_name;
			$format = ! empty( $this->emails_options['email_format_ho'] ) ? $this->emails_options['email_format_ho'] : 'plain';
		}

		if( isset( $_POST['sender'] ) && $_POST['sender'] === 'own' ) {
			if ( ! in_array( 'head_of', $current_user->roles ) && ! in_array( 'city', $current_user->roles ) ) {
				$from_email = $current_user->user_email;
			} else {
				$from_email = $current_user->user_email;
			}
		} else {
			$from_email = NULL;
		}

		list( $total_count, $success_count, $fail_count, $insert_id ) = $vca_asm_mailer->send( $to, $_POST['subject'], $_POST['message'], $from_name, $from_email, $format, $save, $membership, $receipient_group, $receipient_id );

		$success = '<div class="message"><p>' .
			sprintf(
				_x( 'The Email titled "%1$s" has been successfully sent to %2$s out of %3$s recipients.', 'Admin Email Interface', 'vca-asm' ),
				$_POST['subject'], $success_count, $total_count
			) .
			'</p><p>' .
				sprintf(
					_x( 'The Email has been saved to %1$s and you can view it %2$s.', 'Admin Email Interface', 'vca-asm' ),
					'<a href="admin.php?page=vca-asm-emails" title="' . __( 'View Sent Items', 'vca-asm' ) . '">' . __( 'Sent Items', 'vca-asm' ) . '</a>',
					'<a href="' . get_site_url() . '/email/?id=' . $insert_id . '" title="' . __( 'Read the E-Mail', 'vca-asm' ) . '">' . __( 'here', 'vca-asm' ) . '</a>'
				) .
			'</p><p>' .
				'<a title="' . _x( 'One more...', 'Admin Email Interface', 'vca-asm' ) . '" ' .
					'href="' . get_option( 'siteurl' ) . '/wp-admin/admin.php?page=vca-asm-compose">' .
						'&larr; ' . _x( 'Send further mails', 'Admin Email Interface', 'vca-asm' ) .
					'</a>' .
			'</p></div>';

		return $success;
	}

	/******************** CONSTRUCTORS ********************/

	/**
	 * PHP4 style constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function VCA_ASM_Admin_Emails() {
		$this->__construct();
	}

	/**
	 * PHP5 style constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
		$this->emails_options = get_option( 'vca_asm_emails_options' );
	}

} // class

endif; // class exists

?>