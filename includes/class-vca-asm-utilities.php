<?php

/**
 * VcA_ASM_Utilities class.
 * 
 * This class contains utility methods used here and there.
 *
 * @package VcA Activity & Supporter Management
 * @since 1.0
 */

if ( ! class_exists( 'VcA_ASM_Utilities' ) ) :

class VcA_ASM_Utilities {
		
	/**
	 * Calculates age,
	 * i.e. the difference between two Unix Timestamps
	 *
	 * @since 1.0
	 * @access public
	 */
	public function date_diff( $d1, $d2 ) {
		if( $d1 < $d2 ) {
				$temp = $d2;
				$d2 = $d1;
				$d1 = $temp;
		} else {
				$temp = $d1;
		}
		$d1 = date_parse( date( "Y-m-d H:i:s", $d1 ) );
		$d2 = date_parse( date( "Y-m-d H:i:s", $d2 ) );
		//seconds
		if ( $d1['second'] >= $d2['second'] ){
				$diff['second'] = $d1['second'] - $d2['second'];
		} else {
				$d1['minute']--;
				$diff['second'] = 60-$d2['second']+$d1['second'];
		}
		//minutes
		if ( $d1['minute'] >= $d2['minute'] ){
				$diff['minute'] = $d1['minute'] - $d2['minute'];
		} else {
				$d1['hour']--;
				$diff['minute'] = 60-$d2['minute']+$d1['minute'];
		}
		//hours
		if ( $d1['hour'] >= $d2['hour'] ){
				$diff['hour'] = $d1['hour'] - $d2['hour'];
		} else {
				$d1['day']--;
				$diff['hour'] = 24-$d2['hour']+$d1['hour'];
		}
		//days
		if ( $d1['day'] >= $d2['day'] ){
				$diff['day'] = $d1['day'] - $d2['day'];
		} else {
				$d1['month']--;
				$diff['day'] = date("t",$temp)-$d2['day']+$d1['day'];
		}
		//months
		if ( $d1['month'] >= $d2['month'] ){
				$diff['month'] = $d1['month'] - $d2['month'];
		} else {
				$d1['year']--;
				$diff['month'] = 12-$d2['month']+$d1['month'];
		}
		//years
		$diff['year'] = $d1['year'] - $d2['year'];
		return $diff;   
	}
	
	/**
	 * Replaces in-text URLs with working Links
	 *
	 * @since 1.0
	 * @access public
	 */
	public function urls_to_links( $string ) {
		/* make sure there is an http:// on all URLs */
		$string = preg_replace( "/([^\w\/])(www\.[a-z0-9\-]+\.[a-z0-9\-]+)/i", "$1http://$2", $string );
		/* create links */
		$string = preg_replace( "/([\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/i", "<a target=\"_blank\" title=\"" . __( 'Visit Site', 'vca-asm' ) . "\" href=\"$1\">$1</A>",$string);
		
		return $string;   
	}
	
} // class

endif; // class exists

?>