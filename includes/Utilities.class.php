<?php

/**
 * Created by PhpStorm.
 * User: tobias
 * Date: 27.09.2017
 * Time: 16:02
 */
class Utilities
{

    public static function convertString( $string )
    {
        if( $string === 'male' ) {
            $string = __( 'male', 'vca-asm' );
        } elseif( $string === 'female' ) {
            $string = __( 'female', 'vca-asm' );
        } elseif( $string === 0 || $string === '0' ) {
            $string = __( 'No', 'vca-asm' );
        } elseif( $string == 1 ) {
            $string = __( 'has applied...', 'vca-asm' );
        } elseif( $string == 2 ) {
            $string = __( 'Active Member', 'vca-asm' );
        } elseif ( $string === 'Switzerland' ) {
            $string = __( 'Switzerland', 'vca-asm' );
        } elseif ( $string === 'Germany' ) {
            $string = __( 'Germany', 'vca-asm' );
        } elseif ( $string === 'Austria' ) {
            $string = __( 'Austria', 'vca-asm' );
        } elseif( empty( $string ) ) {
            $string = __( 'not set', 'vca-asm' );
        }

        return $string;
    }


    /**
     * Returns a phone number without whitespaces, zeroes or a plus sign
     *
     * @param int|string $number		the phone number
     * @param array $args				(optional) parameters determining how to format the generated output
     * @return string $number
     *
     * @global object $vca_asm_geography
     *
     * @since 1.2
     * @access public
     */
    public function normalizePhoneNNumber( $number, $args = array() )
    {
        global $vca_asm_geography;

        $default_args = array(
            'nice' => false,
            'ext' => '49',
            'nat_id' => 0
        );
        extract( wp_parse_args( $args, $default_args ), EXTR_SKIP );

        if ( is_numeric( $nat_id ) && 0 != $nat_id ) {
            $ext = $vca_asm_geography->get_phone_extension( $nat_id );
        }

        $number = preg_replace( "/[^0-9+]/", "", $number );

        if( ! empty( $number ) ) {

            if( mb_substr( $number, 0, 2 ) == '00' ) {
                $number = mb_substr( $number, 2 );
            } elseif( mb_substr( $number, 0, 1 ) == '+' ) {
                $number = mb_substr( $number, 1 );
            } elseif( mb_substr( $number, 0, 1 ) == '0' ) {
                $number = $ext . mb_substr( $number, 1 );
            }

            if( $nice === true ) {
                $number = '+' . mb_substr( $number, 0, 2 ) . ' ' . mb_substr( $number, 2, 3 ) . ' ' . mb_substr( $number, 5, 3 ) . ' ' . mb_substr( $number, 8, 3 ) . ' ' . mb_substr( $number, 11, 3 ) . ' ' . mb_substr( $number, 14 );
            }
        } else {
            $number = __( 'not set', 'vca-asm' );
        }
        return $number;
    }

}