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
	 * Outputs tabular data of mail currently being send
	 *
	 * @since 1.3
	 * @access public
	 */
	public function outbox_control() {
		global $wpdb, $vca_asm_utilities,
			$vca_asm_mailer;

		$messages = array();

		if ( isset( $_GET['todo'] ) && $_GET['todo'] == 'process' ) {
			/* send it! */
			$success = $this->process();
			if ( $success ) {
				list( $mail_id, $receipients_count ) = $success;
				header( 'Location: ' . strtok( $_SERVER['REQUEST_URI'], '?' ) . '?page=vca-asm-outbox&todo=processed&id=' . $mail_id . '&cnt=' . $receipients_count );
			}
		} elseif( isset( $_GET['todo'] ) && $_GET['todo'] == 'processed' ) {
			$mail_id = isset( $_GET['id'] ) ? $_GET['id'] : '0';
			$receipients_count = isset( $_GET['cnt'] ) ? $_GET['cnt'] : 0;
			$vca_asm_mailer->packet_size;
			$messages[] = array(
				'type' => 'message',
				'message' => _x( 'The Email has been added to the sending queue.', 'Admin Email Interface', 'vca-asm' ) .
					' ' . _x( 'Be patient, it may take a while until all are sent.', 'Admin Email Interface', 'vca-asm' ) .
					'<br />' .
					sprintf(
						_x( 'It has been saved to %1$s and you can view it %2$s.', 'Admin Email Interface', 'vca-asm' ),
						'<a href="admin.php?page=vca-asm-emails" title="' . __( 'View Sent Items', 'vca-asm' ) . '">' . __( 'Sent Items', 'vca-asm' ) . '</a>',
						'<a href="' . get_site_url() . '/email/?id=' . $mail_id . '" title="' . __( 'Read the E-Mail', 'vca-asm' ) . '">' . __( 'here', 'vca-asm' ) . '</a>'
					) .
					'<br /><br />' .
					'<a title="' . _x( 'One more...', 'Admin Email Interface', 'vca-asm' ) . '" ' .
						'href="' . get_option( 'siteurl' ) . '/wp-admin/admin.php?page=vca-asm-compose">' .
							'&larr; ' . _x( 'Send further mails', 'Admin Email Interface', 'vca-asm' ) .
					'</a>'
			);
		} elseif( isset( $_GET['todo'] ) && $_GET['todo'] == 'test' ) {
			echo $vca_asm_mailer->check_outbox();
		}

		$url = "admin.php?page=vca-asm-outbox";
		$sort_url = $url;

		extract( $vca_asm_utilities->table_order( 'id' ) );

		$columns = array(
			array(
				'id' => 'to',
				'title' => __( 'To', 'vca-asm' ),
				'sortable' => false
			),
			array(
				'id' => 'subject',
				'title' => __( 'Subject', 'vca-asm' ),
				'sortable' => false,
				'actions' => array( 'outbox_read' ),
				'cap' => 'view_emails'
			),
			array(
				'id' => 'receipients_cnt',
				'title' => __( 'Queued Mails', 'vca-asm' ),
				'sortable' => false
			),
			array(
				'id' => 'total_receipients',
				'title' => __( 'Total Receipients', 'vca-asm' ),
				'sortable' => false
			)
		);

		$queued_emails = $wpdb->get_results(
			"SELECT * FROM " . $wpdb->prefix."vca_asm_emails_queue", ARRAY_A
		);

		$rows = array();
		$i = 0;
		$total_queue = 0;
		if ( ! empty( $queued_emails ) ) {
			foreach ( $queued_emails as $queued_email ) {
				$the_mail = $wpdb->get_results(
					"SELECT * FROM " . $wpdb->prefix . "vca_asm_emails " .
					"WHERE id = " . $queued_email['mail_id'] . " LIMIT 1", ARRAY_A
				);
				if ( ! empty( $the_mail ) ) {
					$the_mail = $the_mail[0];
					$rows[$i] = array();
					$rows[$i]['id'] = $queued_email['id'];
					$rows[$i]['mail_id'] = $queued_email['mail_id'];
					$rows[$i]['subject'] = $the_mail['subject'];
					$rows[$i]['to'] = $vca_asm_mailer->determine_for_field( $the_mail['receipient_group'], $the_mail['receipient_id'], $the_mail['membership'] );
					$rows[$i]['receipients_cnt'] = count( unserialize( $queued_email['receipients'] ) );
					$rows[$i]['total_receipients'] = ! empty( $queued_email['total_receipients'] ) ? $queued_email['total_receipients'] : '---';
				}
				$total_queue = $total_queue + $rows[$i]['receipients_cnt'];
				$i++;
			}
		}

		$page_args = array(
			'echo' => true,
			'icon' => 'icon-emails',
			'title' => __( 'Outbox', 'vca-asm' ),
			'url' => $url,
			'messages' => $messages
		);

		$tbl_args = array(
			'echo' => true,
			'orderby' => $orderby,
			'order' => $order,
			'toggle_order' => $toggle_order,
			'page_slug' => 'vca-asm-outbox',
			'base_url' => $url,
			'sort_url' => $url,
			'with_wrap' => false,
			'icon' => 'icon-emails',
			'headline' => '',
			'messages' => $messages,
			'headspace' => true,
			'show_empty_message' => true,
			'empty_message' => __( 'No mails queued for sending...', 'vca-asm' ),
			'dspl_cnt' => true,
			'count' => $total_queue,
			'cnt_txt' => __( '%d Mails queued', 'vca-asm' ),
			'with_bulk' => false,
			'bulk_btn' => 'Execute',
			'bulk_confirm' => '',
			'bulk_name' => 'bulk',
			'bulk_param' => 'todo',
			'bulk_desc' => '',
			'extra_bulk_html' => '',
			'bulk_actions' => array()
		);

		$the_page = new VCA_ASM_Admin_Page( $page_args );
		$the_table = new VCA_ASM_Admin_Table( $tbl_args, $columns, $rows );

		$the_page->top();
		if ( ! empty( $queued_emails ) && $vca_asm_mailer->use_packets && $vca_asm_mailer->packet_size ) {
			global $vca_asm_utilities;
			$next = wp_next_scheduled( 'vca_asm_check_outbox' );
			$time_diff = $vca_asm_utilities->date_diff( $next, time() );
			echo '<p>' .
					sprintf( __( 'The next package of %1$d e-mails will be sent in %2$d:%3$s', 'vca-asm' ), $vca_asm_mailer->packet_size, $time_diff['minute'], str_pad( strval( $time_diff['second'] ), 2, '0', STR_PAD_LEFT ) ) .
				'</p>';
		}
		$the_table->output();
		$the_page->bottom();
	}

	/**
	 * Outputs tabular data of previously sent mail
	 *
	 * @todo Rewrite to use OOP-templates
	 *
	 * @since 1.2
	 * @access public
	 */
	public function sent_control() {
		global $current_user, $wpdb, $vca_asm_geography, $vca_asm_mailer, $vca_asm_utilities;
		get_currentuserinfo();
		$admin_region = get_user_meta( $current_user->ID, 'city', true );

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
			if( 'Head' === mb_substr( $user->first_name, 0, 4 ) ) { //legacy
				$by =  $vca_asm_geography->get_status( get_user_meta( $user->ID, 'city', true ) ) . ' ' . $user->last_name;
			} else {
				$by = trim( $user->first_name . ' ' .$user->last_name );
			}
			$emails[$i]['sent_by'] = $by;

			$emails[$i]['to'] = $vca_asm_mailer->determine_for_field(
					$emails[$i]['receipient_group'],
					$emails[$i]['receipient_id'],
					$emails[$i]['membership']
			);

			$emails[$i]['from'] = preg_replace( '/<|>/', '', $emails[$i]['from'] );
			$i++;
		}
		$emails = $vca_asm_utilities->sort_by_key( $emails, $orderby, $order );

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

		$output .= '<h3 class="title title-top-pa">' . _x( 'Search', 'Admin Emails', 'vca-asm' ) . '</h3>' .
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
	 * Outputs form to send mails
	 *
	 * @since 1.0
	 * @access public
	 */
	public function compose_control() {
		global $wpdb, $current_user, $vca_asm_geography;
		get_currentuserinfo();

		if ( isset( $_GET['tab'] ) && in_array( $_GET['tab'], array( 'newsletter', 'activity' ) ) ) {
			$active_tab = $_GET['tab'];
		} else {
			$active_tab = 'newsletter';
		}

		$format = 'html';
		if( ! in_array( 'city', $current_user->roles ) ) {
			$format = ! empty( $this->emails_options['email_format_admin'] ) ? $this->emails_options['email_format_admin'] : 'html';
		} else {
			$format = ! empty( $this->emails_options['email_format_ho'] ) ? $this->emails_options['email_format_ho'] : 'plain';
		}

		$this->compose_view( array(), $active_tab, $format );
	}

	/**
	 * Outputs the form to compose an email
	 *
	 * @since 1.3.3
	 * @access public
	 */
	public function compose_view( $messages = array(), $active_tab = 'newsletter', $editor_type = 'plain' ) {
		global $current_user, $vca_asm_activities, $vca_asm_geography;
		get_currentuserinfo();

		$act_id = isset( $_GET['activity'] ) ? intval( $_GET['activity'] ) : NULL;
		$act_phase = ! empty( $act_id ) ? $vca_asm_activities->get_phase( $act_id ) : 'all';
		$act_type = ! empty( $act_id ) ? get_post_type( $act_id ) : 'all';
		$act_group = isset( $_GET['group'] ) ? $_GET['group'] : 'parts';

		wp_enqueue_script( 'vca-asm-admin-email-compose' );
		$act_sel_options = array();
		$types = array( 'all' );
		$types = array_merge( $types, $vca_asm_activities->activity_types );
		$phases = array( 'all', 'bf', 'app', 'ft', 'pst' );
		foreach ( $types as $type ) {
			$act_sel_options[$type] = array();
			foreach ( $phases as $phase ) {
				$act_sel_options[$type][$phase] = $vca_asm_activities->options_array_activities(array(
					'type' => $type,
					'phase' => $phase
				));
			}
		}
		wp_localize_script( 'vca-asm-admin-email-compose', 'selectedActivity', $act_id );
		wp_localize_script( 'vca-asm-admin-email-compose', 'actSelOptions', $act_sel_options );
		wp_localize_script( 'vca-asm-admin-email-compose', 'activeTab', array( 'name' => $active_tab ) );
		wp_localize_script( 'vca-asm-admin-email-compose', 'noActivity', array( 'string' => __( 'No activities for the currently selected criteria...', 'vca-asm' ) ) );

		$admin_city = get_user_meta( $current_user->ID, 'city', true );
		$admin_nation = get_user_meta( $current_user->ID, 'nation', true );
		$admin_city_name = $vca_asm_geography->get_name( $admin_city );
		$admin_nation_name = $vca_asm_geography->get_name( $admin_nation );

		/* form parameters */
		$url = "admin.php?page=vca-asm-compose";
		$form_action = "admin.php?page=vca-asm-outbox&todo=process&noheader=true";

		wp_enqueue_script( 'vca-asm-admin-email-preview' );
		$params = array(
			'url' => get_option( 'siteurl' ),
			'btnVal' => __( 'Preview', 'vca-asm' ),
			'action' => $form_action
		);
		wp_localize_script( 'vca-asm-admin-email-preview', 'emailParams', $params );

		$initial = array(
			'sent_by' => '',
			'message' => '',
			'subject' => ''
		);
		if ( isset( $_GET['id'] ) ) {
			$email_query = $wpdb->get_results(
				"SELECT subject, message, sent_by FROM " . $wpdb->prefix."vca_asm_emails" .
				" WHERE id = " . $_GET['id'] . " LIMIT 1", ARRAY_A
			);
			if ( isset( $email_query[0] ) ) {
				$initial['sent_by'] = $email_query[0]['sent_by'];
				if ( $current_user->has_cap( 'vca_asm_send_global_emails' ) || $current_user->has_cap( 'vca_asm_send_emails' ) && $current_user->ID == $initial['sent_by'] ) {
					$initial['message'] = $email_query[0]['message'];
					$initial['subject'] = $email_query[0]['subject'];
				}
			}
		}

		$output = '';

		switch ( $editor_type ) {
			case 'html':
				$editor_field = array(
					'id' => 'newsletter-editor',
					'type' => 'tinymce',
					'label' =>  _x( 'Message', 'Admin Email Interface', 'vca-asm' ),
					'desc' => _x( 'Message Body', 'Admin Email Interface', 'vca-asm' ),
					'value' => ! empty( $initial['message'] ) ? $initial['message'] : '',
					'args' =>array(
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
					)
				);
			break;

			case 'plain':
			default:
				$editor_field = array(
					'type' => 'textarea',
					'label' =>  _x( 'Message', 'Admin Email Interface', 'vca-asm' ),
					'id' => 'message',
					'tabindex' => 2,
					'desc' => _x( 'Message Body', 'Admin Email Interface', 'vca-asm' ),
					'value' => ! empty( $initial['message'] ) ? $initial['message'] : ''
				);
			break;
		}

		$compose_box = array(
			'title' => __( 'The E-Mail', 'vca-asm' ),
			'fields' => array(
				array(
					'type' => 'text',
					'label' =>  _x( 'Subject', 'Admin Email Interface', 'vca-asm' ),
					'id' => 'subject',
					'class' => 'wide-text',
					'tabindex' => 1,
					'desc' => _x( 'The email&apos;s subject line', 'Admin Email Interface', 'vca-asm' ),
					'value' => ! empty( $initial['subject'] ) ? $initial['subject'] : ''
				),
				$editor_field
			)
		);

		$sender_field = array(
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
		);

		if ( $current_user->has_cap('vca_asm_send_emails_global') ) {
			$rg_options = array(
				array(
					'label' => _x( 'All Pool Users', 'Admin Email Interface', 'vca-asm' ),
					'value' => 'all'
				),
				array(
					'label' => sprintf( _x( 'Pool Users from %s', 'Admin Email Interface', 'vca-asm' ), $admin_nation_name ),
					'value' => 'alln'
				),
				array(
					'label' => _x( 'All City Users', 'Admin Email Interface', 'vca-asm' ),
					'value' => 'cu'
				),
				array(
					'label' => sprintf( _x( 'City Users from %s', 'Admin Email Interface', 'vca-asm' ), $admin_nation_name ),
					'value' => 'cun'
				)
			);
		} elseif ( $current_user->has_cap('vca_asm_send_emails_nation') ) {
			$rg_options = array(
				array(
					'label' => sprintf( _x( 'All Pool Users from %s', 'Admin Email Interface', 'vca-asm' ), $admin_nation_name ),
					'value' => 'alln'
				),
				array(
					'label' => sprintf( _x( 'City Users from %s', 'Admin Email Interface', 'vca-asm' ), $admin_nation_name ),
					'value' => 'cun'
				)
			);
		}

		$rg_options[] = array(
			'label' => _x( 'by City', 'Admin Email Interface', 'vca-asm' ),
			'value' => 'city',
			'class' => 'no-js-hide'
		);
		$rg_options[] = array(
			'label' => _x( 'by City Group', 'Admin Email Interface', 'vca-asm' ),
			'value' => 'cg',
			'class' => 'no-js-hide'
		);

		if ( $current_user->has_cap('vca_asm_send_emails_global') ) {
			$rg_options[] = array(
				'label' => _x( 'by Country', 'Admin Email Interface', 'vca-asm' ),
				'value' => 'nation',
				'class' => 'no-js-hide'
			);
			$rg_options[] = array(
				'label' => _x( 'by Country Group', 'Admin Email Interface', 'vca-asm' ),
				'value' => 'ng',
				'class' => 'no-js-hide'
			);
		}

		$newsletter_meta_fields = array(
			array(
				'id' => 'mail_type',
				'type' => 'hidden',
				'value' => $active_tab
			),
			array(
				'type' => 'radio',
				'label' => _x( 'Membership?', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'membership',
				'options' => array(
					array(
						'label' => __( 'All', 'vca-asm' ),
						'value' => 0
					),
					array(
						'label' => _x( '&quot;Active Members&quot; only', 'Admin Email Interface', 'vca-asm' ),
						'value' => 2
					)
				),
				'value' => 0,
				'desc' => _x( 'Select whether to send the email to all users of the selected group or to those with &quot;active membership&quot; status only.', 'Admin Email Interface', 'vca-asm' )
			),
			array(
				'type' => 'checkbox',
				'label' => _x( 'Ignore user settings?', 'Admin Email Interface', 'vca-asm' ),
				'id' => 'ignore_switch',
				'desc' => _x( 'As you know from your own profile, users may select which news to receive - general news, regional ones, both or none. In rare cases you have a message so important, that you might want to ignore the users wishes and reach everyone within your selected group. Tick this box to do so. Please do not make use of this feature frequently!', 'Admin Email Interface', 'vca-asm' )
			),
			$sender_field
		);

		if ( $current_user->has_cap('vca_asm_send_emails_global') || $current_user->has_cap('vca_asm_send_emails_nation') ) {
			$newsletter_meta_admins = array(
				array(
					'type' => 'select',
					'label' => _x( 'Receipient Group', 'Admin Email Interface', 'vca-asm' ),
					'id' => 'receipient-group',
					'options' => $rg_options,
					'desc' => __( 'Select the type of group this mail will be addressed to.', 'vca-asm' )
				),
				array(
					'type' => 'radio',
					'label' => __( 'City', 'vca-asm' ),
					'id' => 'city-id',
					'row-class' => 'receipient-group-id',
					'options' => $vca_asm_geography->options_array( array( 'type' => 'city' )),
					'desc' => _x( 'Select the city you want to write to.', 'Admin Supporters', 'vca-asm' ),
					'cols' => 3,
					'js-only' => true
				),
				array(
					'type' => 'radio',
					'label' => __( 'City Group', 'vca-asm' ),
					'id' => 'cg-id',
					'row-class' => 'receipient-group-id',
					'options' => $vca_asm_geography->options_array( array( 'type' => 'cg' )),
					'desc' => _x( 'Select the city group you want to write to.', 'Admin Supporters', 'vca-asm' ),
					'cols' => 3,
					'js-only' => true
				)
			);
		}

		if ( $current_user->has_cap('vca_asm_send_emails_global') ) {
			$newsletter_meta_admins[] = array(
				'type' => 'radio',
				'label' => __( 'Country', 'vca-asm' ),
				'id' => 'nation-id',
				'row-class' => 'receipient-group-id',
				'options' => $vca_asm_geography->options_array( array( 'type' => 'nation' )),
				'desc' => _x( 'Select the country you want to write to', 'Admin Supporters', 'vca-asm' ),
				'cols' => 3,
				'js-only' => true
			);
			$newsletter_meta_admins[] = array(
				'type' => 'radio',
				'label' => __( 'Country Group', 'vca-asm' ),
				'id' => 'ng-id',
				'row-class' => 'receipient-group-id',
				'options' => $vca_asm_geography->options_array( array( 'type' => 'ng' )),
				'desc' => _x( 'Select the country group you want to write to', 'Admin Supporters', 'vca-asm' ),
				'cols' => 3,
				'js-only' => true
			);
		}

		if ( $current_user->has_cap('vca_asm_send_emails_global') || $current_user->has_cap('vca_asm_send_emails_nation') ) {
			$newsletter_meta_fields = array_merge( $newsletter_meta_admins, $newsletter_meta_fields );
			$newsletter_boxes = array(
				array(
					'title' => __( 'Receipients &amp; Meta Data', 'vca-asm' ),
					'fields' => $newsletter_meta_fields
				),
				$compose_box
			);
		} else {
			$newsletter_meta_fields[] = array(
				'type' => 'hidden',
				'id' => 'receipient-group',
				'value' => 'city'
			);
			$newsletter_meta_fields[] = array(
				'type' => 'hidden',
				'id' => 'city-id',
				'value' => get_user_meta( $current_user->ID, 'city', true )
			);
			$newsletter_boxes = array(
				array(
					'title' => sprintf( __( 'Newsletter to your %s', 'vca-asm' ), $vca_asm_geography->get_type( get_user_meta( $current_user->ID, 'city', true ) ) ),
					'fields' => $newsletter_meta_fields
				),
				$compose_box
			);
		}

		$activity_options_array = $vca_asm_activities->options_array_with_all;

		$activity_boxes = array(
			array(
				'title' => __( 'Receipients &amp; Meta Data', 'vca-asm' ),
				'fields' => array(
					array(
						'id' => 'mail_type',
						'type' => 'hidden',
						'value' => $active_tab
					),
					array(
						'id' => 'phases',
						'type' => 'radio',
						'label' => __( 'Phase', 'vca-asm' ),
						'options' => array(
							array(
								'label' => __( 'All', 'vca-asm' ),
								'value' => 'all',
							),
							array(
								'label' => __( 'before application phase', 'vca-asm' ),
								'value' => 'bf',
							),
							array(
								'label' => __( 'in application phase', 'vca-asm' ),
								'value' => 'app',
							),
							array(
								'label' => __( 'future activities where the application phase has ended', 'vca-asm' ),
								'value' => 'ft',
							),
							array(
								'label' => __( 'past activities', 'vca-asm' ),
								'value' => 'pst',
							)
						),
						'desc' => __( 'Narrow activity list by current phase', 'vca-asm' ),
						'value' => $act_phase,
						'cols' => 1,
						'js-only' => true,
						'class' => 'no-js-hide'
					),
					array(
						'id' => 'type',
						'type' => 'radio',
						'label' => __( 'Type', 'vca-asm' ),
						'options' => $activity_options_array,
						'desc' => __( 'Narrow activity list by its type', 'vca-asm' ),
						'value' => $act_type,
						'cols' => 1,
						'js-only' => true,
						'class' => 'no-js-hide'
					),
					array(
						'id' => 'activity',
						'type' => 'select',
						'label' => _x( 'The Activity', 'Admin Email Interface', 'vca-asm' ),
						'options' => $vca_asm_activities->options_array_activities( array(
							'phase' => $act_phase,
							'type' => $act_type,
							'check_caps' => true
						)),
						'value' => $act_id,
						'desc' => _x( 'Which activity are the users associated with?', 'Admin Email Interface', 'vca-asm' )
					),
					array(
						'id' => 'receipient-group',
						'type' => 'radio',
						'label' => _x( 'Group', 'Admin Email Interface', 'vca-asm' ),
						'options' => array(
							array(
								'label' => __( 'Applicants', 'vca-asm' ),
								'value' => 'apps'
							),
							array(
								'label' => __( 'Participants', 'vca-asm' ),
								'value' => 'parts'
							),
							array(
								'label' => __( 'Waiting List', 'vca-asm' ),
								'value' => 'waiting'
							)
						),
						'value' => $act_group,
						'desc' => _x( 'Select the status of the applications', 'Admin Email Interface', 'vca-asm' )
					),
					$sender_field
				)
			),
			$compose_box
		);

		$adminpage = new VCA_ASM_Admin_Page( array(
			'echo' => false,
			'icon' => 'icon-write',
			'title' => __( 'Send an email', 'vca-asm' ),
			'messages' => $messages,
			'url' => $url,
			'tabs' => array(
				array(
					'title' => __( 'Newsletters', 'vca-asm' ),
					'value' => 'newsletter',
					'icon' => 'icon-emails'
				),
				array(
					'title' => __( 'Activity Notifications', 'vca-asm' ),
					'value' => 'activity',
					'icon' => 'icon-activity'
				)
			),
			'active_tab' => $active_tab
		));

		$form = new VCA_ASM_Admin_Form( array(
			'echo' => false,
			'form' => true,
			'name' => 'vca-asm-groupmail-form',
			'method' => 'post',
			'metaboxes' => true,
			'js' => false,
			'url' => $url,
			'action' => $form_action,
			'nonce' => 'vca-asm',
			'id' => 0,
			'button' => __( 'Send Mail!', 'vca-asm' ),
			'button_id' => 'sendmail-submit',
			'top_button' => false,
			'confirm' => true,
			'confirm_text' => __( 'Send Email now?', 'vca-asm' ),
			'has_cap' => true,
			'fields' => 'activity' === $active_tab ? $activity_boxes : $newsletter_boxes
		));

		$mb_env = new VCA_ASM_Admin_Metaboxes( array(
			'echo' => false,
			'running' => 2,
			'id' => '',
			'title' => __( 'Tips &amp; Help', 'vca-asm' )
		));

		$output .= $adminpage->top();

		$output .= $form->output();

		$output .= $mb_env->top();
		$output .= $mb_env->mb_top();
		$output .= '<table class="form-table pool-form"><tbody><tr valign="top"><th scope="row">' .
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
			'</td></tr></tbody></table>';
		$output .= $mb_env->mb_bottom();
		$output .= $mb_env->bottom();

		$output .= $adminpage->bottom();

		echo $output;
	}

	/**
	 * Prepares groupmail for sending
	 *
	 * @since 1.0
	 * @access private
	 */
	private function process() {
		global $current_user, $wpdb,
			$vca_asm_mailer, $vca_asm_geography;

		$admin_nation = get_user_meta( $current_user->ID, 'nation', true );
		$membership = ( isset( $_POST['membership'] ) && in_array( $_POST['membership'], array( 0, 2 ) ) ) ? $_POST['membership'] : 0;
		$receipient_group = isset( $_POST['receipient-group'] ) ? $_POST['receipient-group'] : '';
		$receipient_id = 0;
		$activity_id = isset( $_POST['activity'] ) ? $_POST['activity'] : 0;
		$save = true;
		$mail_type = isset( $_POST['mail_type'] ) ? $_POST['mail_type'] : '';
		$ignore_switch = isset( $_POST['ignore_switch'] ) ? true : false;
		$receipients = array();

		if ( ! empty( $receipient_group ) ) {

			list( $receipient_id, $receipients ) = $vca_asm_mailer->receipient_id_from_group( $receipient_group, true, $ignore_switch, $membership );

			if ( ! in_array( 'city', $current_user->roles ) ) {
				$from_name = trim( $current_user->first_name . ' ' . $current_user->last_name );
				$format = ! empty( $this->emails_options['email_format_admin'] ) ? $this->emails_options['email_format_admin'] : 'html';
			} else {
				$city_id = get_user_meta( $current_user->ID, 'city', true );
				$city_name = $vca_asm_geography->get_name( $city_id );
				$from_name =  $vca_asm_geography->get_status( $city_id ) . ' ' . $city_name;
				$format = ! empty( $this->emails_options['email_format_ho'] ) ? $this->emails_options['email_format_ho'] : 'html';
			}

			$from_email = ( isset( $_POST['sender'] ) && $_POST['sender'] === 'own' ) ? $current_user->user_email : 'no-reply@vivaconagua.org';

			$queue_args = array(
				'receipients' => $receipients,
				'subject' => $_POST['subject'],
				'message' => $_POST['message'],
				'from_name' => $from_name,
				'from_email' => $from_email,
				'format' => $format,
				'save' => $save,
				'membership' => $membership,
				'receipient_group' => $receipient_group,
				'receipient_id' => $receipient_id,
				'type' => $mail_type,
				'time' => time()
			);

			$success = array(
				$vca_asm_mailer->queue( $queue_args ),
				count( $receipients )
			);

		} else {
			$success = false;
		}

		return $success;
	}

	/******************** CONSTRUCTOR ********************/

	/**
	 * Constructor
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