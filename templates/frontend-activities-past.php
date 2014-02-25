<?php

	/**
	 * Template to display past activities in the supporter's activities view
	 * (i.e. activities a supporter has already participated in)
	 *
	 * This template is not satisfied with a simple array,
	 * it must be fed $activities, a WP_Query object
	 **/

	global $vca_asm_utilities;

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
			'<h4><a title="' . __( 'View activity', 'vca-asm' ) . '" href="' . get_permalink() . '">' . get_the_title() . '</a></h4>' .
			'<p>' .
				strftime( '%B %Y', intval( get_post_meta( get_the_ID(), 'start_date', true ) ) ) .
			'</p></li>';

	endwhile;

	$output .= '</ul>';

?>