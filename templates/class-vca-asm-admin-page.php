<?php

/**
 * VCA_ASM_Admin_Page class.
 *
 * This class contains properties and methods
 * to display the very basic elements of every backend page
 *
 * @package VcA Activity & Supporter Management
 * @since 1.3
 */

if ( ! class_exists( 'VCA_ASM_Admin_Page' ) ) :

class VCA_ASM_Admin_Page {

	/**
	 * Class Properties
	 *
	 * @since 1.3
	 */
	private $default_args = array(
		'echo' => false,
		'icon' => 'icon-party',
		'title' => 'Admin Page',
		'active_tab' => '',
		'url' => '?page=admin.php',
		'extra_head_html' => '',
		'tabs' => array(),
		'messages' => array(),
		'back' => false,
		'back_url' => '#'
	);
	private $args = array();

	/**
	 * PHP4 style constructor
	 *
	 * @since 1.3
	 * @access public
	 */
	public function VCA_ASM_Admin_Form( $args = array() ) {
		$this->__construct( $args = array() );
	}

	/**
	 * PHP5 style constructor
	 *
	 * @since 1.3
	 * @access public
	 */
	public function __construct( $args = array() ) {
		$this->args = wp_parse_args( $args, $this->default_args );
	}

	/**
	 * Constructs HTML,
	 * echoes or returns it
	 *
	 * @since 1.3
	 * @access private
	 */
	private function output( $type ) {
		global $vca_asm_admin;

		extract( $this->args );

		$output = '';

		switch ( $type ) {
			case 'top':
				$output .= '<div class="wrap">' .
					'<div id="' . $icon . '" class="icon32-pa"></div>' .
					'<h2>' . $title . '</h2><br />';

				if ( $back ) {
					$output .= '<a href="' . $back_url . '" class="button-secondary margin-bottom" title="' . __( 'Back to where you came from...', 'vca-asm' ) . '">' .
							'&larr; ' . __( 'back', 'vca-asm' ) .
						'</a>';
				}

				if( ! empty( $messages ) ) {
					$output .= $vca_asm_admin->convert_messages( $messages );
				}

				if( ! empty( $extra_head_html ) ) {
					$output .= $extra_head_html;
				}

				if( ! empty( $tabs ) && is_array( $tabs ) ) {
					$output .= '<h2 class="nav-tab-wrapper">';
					$i = 0;
					foreach ( $tabs as $tab ) {
						$output .= '<a href="' . $url . '&tab=' . $tab['value'] . '" class="nav-tab ' . ( $tab['value'] === $active_tab || ( $tab['value'] === '' && 0 === $i ) ? 'nav-tab-active' : '' ) . '">' .
								'<div class="nav-tab-icon nt-' . $tab['icon'] . '"></div>' .
								$tab['title'].
							'</a>';
						$i++;
					}
					$output .= '</h2>';
				}
			break;

			case 'bottom':
				$output .= '</div>';
			default:

			break;
		}

		if ( $echo ) {
			echo $output;
		}
		return $output;
	}

	/**
	 * Wrapper for top HTML
	 *
	 * @since 1.3
	 * @access public
	 */
	public function top() {
		return $this->output( 'top' );
	}

	/**
	 * Wrapper for bottom HTML
	 *
	 * @since 1.3
	 * @access public
	 */
	public function bottom() {
		return $this->output( 'bottom' );
	}

} // class

endif; // class exists

?>