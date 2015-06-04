<?php

/**
 * VCA_ASM_Admin_Update class.
 *
 * This class contains properties and methods to
 * update the data structure of the Pool
 *
 * This is subject to constant change
 *
 * @package VcA Activity & Supporter Management
 * @since 1.3
 */

if ( ! class_exists( 'VCA_ASM_Admin_Update' ) ) :

class VCA_ASM_Admin_Update {

	/**
	 * Admin Menu
	 *
	 * @since 1.3
	 * @access public
	 */
	public function admin_menu() {
		add_submenu_page(
			'vca-asm-settings',
			'Update',
			'Update',
			'vca_asm_set_mode',
			'vca-asm-update',
			array( $this, 'control' )
		);
	}

	function remote_file_exists($url, $followRedirects = true)
	{
	   $url_parsed = parse_url($url);
	   extract($url_parsed);
	   if (!@$scheme) $url_parsed = parse_url('http://'.$url);
	   extract($url_parsed);
	   if(!@$port) $port = 80;
	   if(!@$path) $path = '/';
	   if(@$query) $path .= '?'.$query;
	   $out = "HEAD $path HTTP/1.0\r\n";
	   $out .= "Host: $host\r\n";
	   $out .= "Connection: Close\r\n\r\n";
	   if(!$fp = @fsockopen($host, $port, $es, $en, 5)){
		   return false;
	   }
	   fwrite($fp, $out);
	   while (!feof($fp)) {
		   $s = fgets($fp, 128);
		   if(($followRedirects) && (preg_match('/^Location:/i', $s) != false)){
			   fclose($fp);
			   return http_file_exists(trim(preg_replace("/Location:/i", "", $s)));
		   }
		   if(preg_match('/^HTTP(.*?)200/i', $s)){
			   fclose($fp);
			   return true;
		   }
	   }
	   fclose($fp);
	   return false;
	}

	/**
	 * Update Routine and executing page & button
	 *
	 * @since 1.3
	 * @access public
	 */
	public function control() {
		global $wpdb,
			$vca_asm_activities, $vca_asm_finances, $vca_asm_geography, $vca_asm_mailer,
			$vca_asm_admin, $vca_asm_admin_settings;

		$messages = array();

		if ( isset( $_GET['todo'] ) && 'update' === $_GET['todo'] ) {
			$updated = true;

			$nations = $vca_asm_geography->get_all( 'name', 'ASC', 'nation' );
			
			foreach( $nations as $nation ) {
				$vca_asm_admin_settings->insert_autoresponses( $nation['id'], 'ge' );
			}

			//$users = get_users();
			//
			//$cases = 0;
			//$cases2 = 0;
			//$vals = array();
			//$vals2 = array();
			//
			//foreach ( $users as $user ) {
			//	$result = get_user_meta( $user->ID, 'simple_local_avatar', true );
			//	if ( empty( $result ) ) {
			//		continue;
			//	}
			//	$new = array();
			//	$write_it = false;
			//	foreach ( $result as $key => $value ) {
			//		$url = $value;
			//
			//		if ( $this->remote_file_exists( $url ) ) {
			//			$new[$key] = $value;
			//		} else {
			//			$write_it = true;
			//		}
			//	}
			//	if ( true === $write_it ) {
			//		$cases2++;
			//		update_user_meta( $user->ID, 'simple_local_avatar', $new );
			//	} else {
			//		$cases++;
			//	}
			//}
			//
			//print '<pre>EXIST = '
			//	. htmlspecialchars( print_r( $cases, TRUE ), ENT_QUOTES, 'utf-8', FALSE )
			//	. "</pre>\n";
			//
			//print '<pre>DOES NOT = '
			//	. htmlspecialchars( print_r( $cases2, TRUE ), ENT_QUOTES, 'utf-8', FALSE )
			//	. "</pre>\n";

			//$activities = get_posts(
			//	array(
			//		'post_type' => $vca_asm_activities->activity_types,
			//		'posts_per_page' => -1
			//	)
			//);
			//
			//foreach ( $activities as $activity ) {
			//	$city = get_post_meta( $activity->ID, 'geo', true );
			//	update_post_meta( $activity->ID, 'city', $city );
			//	$nation = $vca_asm_geography->has_nation( $city );
			//	if ( $nation ) {
			//		update_post_meta( $activity->ID, 'nation', $nation );
			//	} else {
			//		update_post_meta( $activity->ID, 'nation', 40 );
			//	}
			//}

		}

		if ( ! empty( $updated ) && true === $updated ) {
			$messages = array(
				array(
					'type' => 'message-pa',
					'message' => 'Done!'//'Updated ' . $cases . ' Data Sets.'
				)
			);
		}

		$admin_page = new VCA_ASM_Admin_Page( array(
			'echo' => true,
			'icon' => 'icon-settings',
			'title' => 'Datenstruktur anpassen / Funktionen Testen',
			'url' => '?page=vca-asm-update',
			'messages' => $messages
		));
		$admin_page->top();

		$update_form = new VCA_ASM_Admin_Form( array(
			'echo' => true,
			'form' => true,
			'metaboxes' => false,
			'action' => '?page=vca-asm-update&todo=update',
			'button' => 'Let\'s do it!',
			'top_button' => false
		));
		$update_form->output();

		$admin_page->bottom();
	}

	/**
	 * Constructor
	 *
	 * @since 1.3
	 * @access public
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 19 );
	}

} // class

endif; // class exists

?>