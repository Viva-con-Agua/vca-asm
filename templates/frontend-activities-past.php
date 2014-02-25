<?php
	
	/**
	 * Template to display past activities in the supporter's activities view
	 * (i.e. activities a supporter has already participated in)
	 *
	 * This template is not satisfied with a simple array,
	 * it must be fed $activities, a WP_Query object
	 **/
	
	global $vca_asm_utilities;
	
	setlocale ( LC_ALL , 'de_DE' ); 
	
	if( ! isset( $output ) ) {
		$output = '';
	}
	if( ! isset( $list_class ) ) {
		$list_class = '';
	}
	if( empty( $activities ) ) {
		return;
	}
	
	/* list & loop through posts (activities) */
	$output .=  '<ul class="' . $list_class . '">';
	
	while ( $activities->have_posts() ) : $activities->the_post();
		
		$output .= '<li class="past-activity">' .
			'<h4>' . get_the_title() . '</h4>' .
			'<p>' .
				strftime( '%A, %e.%m.%Y', intval( get_post_meta( get_the_ID(), 'start_date', true ) ) ) .
				' ' . __( 'until', 'vca-asm' ) . ' ' .
				strftime( '%A, %e.%m.%Y', intval( get_post_meta( get_the_ID(), 'end_date', true ) ) ) .
			'</p></li>';
			
	endwhile;
	
	$output .= '</ul>';
  
?>