<?php

/**
 * VCA_ASM_Admin_Future_Feech class.
 *
 * This class contains properties and methods
 * to display a future feature notice
 *
 * @package VcA Activity & Supporter Management
 * @since 1.3
 */

if ( ! class_exists( 'VCA_ASM_Admin_Future_Feech' ) ) :

class VCA_ASM_Admin_Future_Feech {

	/**
	 * Class Properties
	 *
	 * @since 1.3
	 */
	public $default_args = array(
		'echo' => true,
		'headline' => '',
		'title' => '',
		'explanation' => '',
		'version' => '1.4'
	);
	public $args = array();

	/**
	 * PHP4 style constructor
	 *
	 * @since 1.3
	 * @access public
	 */
	public function VCA_ASM_Future_Feech( $args ) {
		$this->__construct( $args );
	}

	/**
	 * PHP5 style constructor
	 *
	 * @since 1.3
	 * @access public
	 */
	public function __construct( $args ) {

		$this->default_args['headline'] = __( 'Future Feature', 'vca-asm' );
		$this->default_args['title'] = __( 'Future Feature', 'vca-asm' );

		$this->args = wp_parse_args( $args, $this->default_args );
	}

	/**
	 * Outputs the notice
	 *
	 * @since 1.3
	 * @access public
	 */
	public function output() {

		extract( $this->args );

		$output = '<div id="poststuff"><div id="post-body" class="metabox-holder columns-1"><div id="postbox-container-1" class="postbox-container"><div id="feech-notice" class="postbox ">' .
				//'<div class="handlediv" title="' . esc_attr__('Click to toggle') . '"><br></div>' .
				'<h3 class="no-hover"><span>' . $headline . '</span></h3>' .
				'<div class="inside">' .
					'<p><strong>' . $title . '</strong></p>';
					if ( ! empty( $explanation ) ) {
						$output .= '<p>' . $explanation . '</p>';
					}
					$output .= '<p>' . sprintf( __( 'Expected to be released with version %s', 'vca-asm' ), $version ) . '</p>' .
				'</div>' .
			'</div></div></div></div>';

		if ( $echo ) {
			echo $output;
		}
		return $output;
	}

} // class

endif; // class exists

?>