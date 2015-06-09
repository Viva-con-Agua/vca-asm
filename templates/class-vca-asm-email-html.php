<?php

/**
 * VCA_ASM_Email_Html class.
 *
 * This class contains properties and methods
 * to create a VcA-style HTML E-Mail
 * (formerly done via procedural template)
 *
 * @package VcA Activity & Supporter Management
 * @since 1.4
 */

if ( ! class_exists( 'VCA_ASM_Email_Html' ) ) :

class VCA_ASM_Email_Html {

	/**
	 * Class Properties
	 *
	 * @since 1.3
	 */
	private $default_args = array(
		'mail_id' => 1,
		'message' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer turpis lacus, posuere id porttitor et, ultrices a nisl. In euismod, tortor nec aliquam sodales, dolor turpis tincidunt neque, sed pharetra tellus mauris vitae erat. In ut convallis tellus. Nunc porttitor luctus sem, in varius eros cursus nec. Etiam in purus quam. Curabitur eleifend facilisis orci quis cursus. Fusce consectetur urna quis nulla pharetra ac suscipit eros accumsan. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Phasellus quis leo nisi, quis dignissim leo. Aliquam ullamcorper metus semper urna rhoncus imperdiet. Morbi elementum orci enim, et fermentum lacus.</p><p>Nam vulputate neque at urna porta scelerisque. Morbi sollicitudin leo sed tellus vestibulum facilisis. Praesent eleifend nunc et enim semper eu porta lorem consectetur. Sed lacinia pharetra ultricies. Fusce rutrum, dolor quis suscipit mattis, dui erat dapibus eros, at consequat elit nisl sit amet dolor. Nulla non suscipit sapien. Donec sollicitudin lacus at risus scelerisque dignissim. Nullam tristique tincidunt metus non porttitor. Etiam egestas arcu et enim euismod lacinia. Donec rhoncus iaculis arcu vitae pellentesque.</p><p>Fusce porta leo dictum eros tempor varius. Donec magna nibh, condimentum quis mollis vel, laoreet quis magna. Cras sit amet dolor eu est rhoncus consequat. Morbi vel porta mauris. Donec pretium metus sed velit ultricies mattis. Vivamus euismod dolor non risus bibendum viverra. Pellentesque ut elit at enim tempus iaculis. Donec varius lobortis metus, in dignissim odio lobortis in. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quis mi ligula. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>',
		'subject' => 'Newsletter',
		'append' => '',
		'append_reason_switch' => true,
		'reason' => 'newsletter',
		'from_name' => 'Viva con Agua',
		'time' => 0,
		'mail_nation' => 'de',
		'for' => 'Supporters',
		'with_images' => true,
		'echo' => false,
		'auto_action' => '',
		'in_browser' => false,
		'user_id' => 0,
		'receipient_id' => 0,
		'receipient_email_address' => 'no-reply@vivaconagua.org'
	);
	private $args = array();

	/**
	 * Constructor
	 *
	 * @since 1.3
	 * @access public
	 */
	public function __construct( $args ) {
		$this->default_args['time'] = time();

		$this->args = wp_parse_args( $args, $this->default_args );
		if ( ! in_array( $this->args['mail_nation'], array( 'de', 'ch', 'at' ) ) ) {
			$this->args['mail_nation'] = 'de';
		}
	}

	/**
	 * Constructs the E-Mail Body,
	 * echoes or returns it
	 *
	 * @since 1.3
	 * @access public
	 */
	public function output() {
		extract( $this->args );
		$lf = "\n";
		$eol = "\r\n";

		switch ( $mail_nation ) {
			case 'ch':
				$logo = 'logo-ch@2x.gif';
				$link_url = 'http://' . _x( 'vivaconagua.ch', 'utility translation', 'vca-asm' );
				$organization_title = __( 'Viva con Agua Switzerland', 'vca-asm' );
			break;

			case 'at':
				$logo = 'logo@2x.gif';
				$link_url = 'http://' . _x( 'vivaconagua.org', 'utility translation', 'vca-asm' );
				$organization_title = __( 'Viva con Agua de Sankt Pauli e.V.', 'vca-asm' );
			break;

			case 'de':
			default:
				$logo = 'logo@2x.gif';
				$link_url = 'http://' . _x( 'vivaconagua.org', 'utility translation', 'vca-asm' );
				$organization_title = __( 'Viva con Agua de Sankt Pauli e.V.', 'vca-asm' );
		}

		switch ( $reason ) {
			case 'membership':
			case 'activity':
				$center_image = 'pool-logo@2x.gif';
				$center_alt = 'POOL';
			break;

			case 'newsletter':
			default:
				$center_image = 'news-logo@2x.gif';
				$center_alt = 'NEWS';
		}

		$append_reason = '';
		if ( $append_reason_switch ) {
			$pool_link = '<a ';
			if ( true === $in_browser ) {
				$pool_link .= 'onclick="preventIt(event)" ';
			}
			$pool_link .= 'title="' . __( 'To the Pool!', 'vca-asm' ) . '" href="' . get_option( 'siteurl' ) . '" style="color:#0B0B0B;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;text-decoration:none;border-bottom: 1px dotted #008fc1;"><span style="color:#0B0B0B;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;text-decoration:none;border-bottom: 1px dotted #008fc1;"><span>' . __( 'Pool', 'vca-asm' ) . '</span></span></a>';

			$direct_cancellation_link = '<a title="' . __( 'Click to cancel newsletter', 'vca-asm' ) . '" href="' . get_option( 'siteurl' ) . '/newsletter-preferences?uid=' . $receipient_id . '&hash=' . md5( $receipient_email_address ) . '" style="color:#0B0B0B;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;text-decoration:none;border-bottom: 1px dotted #008fc1;"><span style="color:#0B0B0B;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;text-decoration:none;border-bottom: 1px dotted #008fc1;"><span>';

			switch ( $reason ) {
				case 'activity':
					$reason_string = str_replace( '%POOL%', $pool_link, __( 'You are getting this message because are registered to the %POOL% and have applied to or are participating in an activity.', 'vca-asm' ) );
				break;

				case 'membership':
					$reason_string = str_replace( '%POOL%', $pool_link, __( 'You are getting this message because are registered to the %POOL% and your membership status just changed.', 'vca-asm' ) );
				break;

				case 'newsletter':
				default:
					$reason_string = str_replace( '%POOL%', $pool_link, __( 'You are getting this message because are registered to the %POOL% and have chosen to receive newsletters.', 'vca-asm' ) ) .
					'<br />' .
					str_replace( '%LINK_CLOSE%', '</span></span></a>', str_replace( '%LINK_OPEN%', $direct_cancellation_link, __( 'If you do not want receive anymore newsletters, you can %LINK_OPEN%cancel it%LINK_CLOSE% here.', 'vca-asm' ) ) );
			}

			$append_reason = '<hr style="margin-top:0;margin-right:0;margin-bottom:21px;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;"><p style="color:#0B0B0B;margin-top:0;margin-right:0;margin-bottom:21px;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;font-size:13px;line-height:21px;font-family:Verdana,Geneva,Arial,Helvetica,sans-serif;">' .
					__( 'Is this email not easily readable or otherwise obscured?', 'vca-asm' ) . '<br />' .
					'<a ';
					if ( true === $in_browser ) {
						$append_reason .= 'onclick="preventIt(event)" ';
					}
					$append_reason .= 'title="' . __( 'Read the mail in your browser', 'vca-asm' ) . '" href="' . get_option( 'siteurl' ) . '/email?id=' . $mail_id . '&hash=' . md5( $time );
			if ( ! empty( $auto_action ) && in_array( $reason, array( 'membership', 'activity' ) ) ) {
				$append_reason .= '&auto_action=' . $auto_action;
				if ( ! empty( $user_id ) ) {
					$append_reason .= '&uid=' . $user_id;
				}
			}
			$append_reason .= '"  style="color:#0B0B0B;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;text-decoration:none;border-bottom: 1px dotted #008fc1;"><span style="color:#0B0B0B;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;text-decoration:none;border-bottom: 1px dotted #008fc1;"><span>' .
						__( 'Read it in your browser.', 'vca-asm' ) .
					'</span></span></a>' .
				'</p><p style="margin-top:0;margin-right:0;margin-bottom:21px;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;font-size:13px;line-height:21px;font-family:Verdana,Geneva,Arial,Helvetica,sans-serif;">' .
					$reason_string .
				'</p>';
		}

		$output = '';

		$output .=  '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' .$lf .
			'<html style="width:100% !important;height:100% !important;">' .$lf .
			"<head>" .$lf .
			'	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' .$lf .
			'	<meta property="og:title" content="' . $subject . '" />' . $lf .
			'	<meta property="og:url" content="' . get_option( 'siteurl' ) . '" />' . $lf .
			"	<title>" . $subject . "</title>" . $lf;
		if ( true === $in_browser ) {
			$output .= '	<link rel="stylesheet" type="text/css" media="all" href="' . get_bloginfo('template_url') . '/css/reset.css?ver=1.2" />' . $lf;
		}
		$output .= "	<style>" . $lf .
			"		body {" . $lf .
			"			-webkit-overflow-scrolling: touch;" . $lf .
			"			-webkit-tap-highlight-color: rgba(226,0,122,.5);" . $lf .
			"			-webkit-text-size-adjust: none;" . $lf .
			"			-ms-text-size-adjust: none;" . $lf .
			"			width:100% !important;" . $lf .
			"		}" . $lf;
		if ( true === $in_browser ) {
			$output .= "		.wrapper {" .$lf .
			"			max-width: 1200px;" .$lf .
			"			min-height: 500px !important;" .$lf .
			"			height: 100% !important;" .$lf .
			"			margin: 0 auto;" .$lf .
			"			position: relative;" .$lf .
			"		}" .$lf .
			"		h1.subject {" .$lf .
			"			font-size: 2.8em;" .$lf .
			"			line-height: 1.5;" .$lf .
			"			margin: 0;" .$lf .
			"			padding: 0;" .$lf .
			"			position: absolute;" .$lf .
			"			top: 42px;" .$lf .
			"			left: 42px;" .$lf .
			"			font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;" .$lf .
			"			font-weight: bold;" .$lf .
			"			color: #00586c;" .$lf .
			"		}" .$lf .
			"		.message-wrapper {" .$lf .
			"			-webkit-overflow-scrolling: touch;" .$lf .
			"			overflow-y: scroll;" .$lf .
			"			padding: 0;" .$lf .
			"			margin: 126px 42px 46px 42px;" .$lf .
			"			position: absolute;" .$lf .
			"			top: 0;" .$lf .
			"			right: 0;" .$lf .
			"			bottom: 0;" .$lf .
			"			left: 0;" .$lf .
			"			border: 2px solid #00586c;" .$lf .
			"		}" .$lf .
			"		.message-wrapper > * {" .$lf .
			"			-webkit-transform: translateZ(0px);" .$lf .
			"		}" .$lf;
		} else {
			$output .=
			"		body > * {" .$lf .
			"			-webkit-transform: translateZ(0px);" .$lf .
			"		}" .$lf .
			"		.ReadMsgBody {" . $lf .
			"			width: 100%;" . $lf .
			"		}" . $lf .
			"		.ExternalClass {" . $lf .
			"			width: 100%;" . $lf .
			"		}" . $lf .
			"		#outlook a {" . $lf .
			"			padding:0;" . $lf .
			"		}" . $lf;
		}
		$output .= "		p {" . $lf .
			"			font-size: 14px;" . $lf .
			"			font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;" . $lf .
			"			line-height: 1.5;" . $lf .
			"			margin: 0 0 21px;" . $lf .
			"		}" . $lf .
			"		a {" . $lf .
			"			color: inherit;" . $lf .
			"			text-decoration: none;" . $lf .
			"		}" . $lf .
			"		p a {" . $lf .
			"			border-bottom: 1px dotted #008fc1;" . $lf .
			"		}" . $lf .
			"		.footer a {" .$lf .
			"			border-bottom: none;" . $lf .
			"		}" . $lf .
			"		a:hover," . $lf .
			"		a:active," . $lf .
			"		a:focus," . $lf .
			"		a:hover span," . $lf .
			"		a:active span," . $lf .
			"		a:focus span," . $lf .
			"		a:active span span," . $lf .
			"		a:hover span span," . $lf .
			"		a:focus span span {" . $lf .
			"			color: #008fc1;" . $lf .
			"			text-shadow: 0 0 1px #008fc1;" . $lf .
			"			border-bottom: 1px dotted #e2007a;" . $lf .
			"		}" . $lf .
			"		.header a:active," . $lf .
			"		.header a:hover," . $lf .
			"		.header a:focus," . $lf .
			"		.header a:active span," . $lf .
			"		.header a:hover span," . $lf .
			"		.header a:focus span," . $lf .
			"		.header a:active span span," . $lf .
			"		.header a:hover span span," . $lf .
			"		.header a:focus span span," . $lf .
			"		.footer a:hover," . $lf .
			"		.footer a:active," . $lf .
			"		.footer a:focus," . $lf .
			"		.footer a:hover span," . $lf .
			"		.footer a:active span," . $lf .
			"		.footer a:focus span," . $lf .
			"		.footer a:active span span," . $lf .
			"		.footer a:hover span span," . $lf .
			"		.footer a:focus span span {" . $lf .
			"			color: #ffffff;" . $lf .
			"			text-shadow: 0 0 1px #f1e6cc;" . $lf .
			"			border-bottom: none;" . $lf .
			"		}" . $lf .
			"		.header a:active img," . $lf .
			"		.header a:hover img," . $lf .
			"		.header a:focus img {" . $lf .
			"			opacity:0.8;" . $lf .
			"			filter:alpha(opacity=80);" . $lf .
			"		}" . $lf .
			"		::-moz-selection {" . $lf .
			"			background: #c4e3f0;" . $lf .
			"			color: #00586c;" . $lf .
			"			text-shadow: none;" . $lf .
			"		}" . $lf .
			"		::selection {" . $lf .
			"			background: #c4e3f0;" . $lf .
			"			color: #00586c;" . $lf .
			"			text-shadow: none;" . $lf .
			"		}" . $lf;
		if ( true === $in_browser ) {
			$output .= "		@media handheld, only screen and (max-width: 600px) {" . $lf .
			"			.mobile-hide {" . $lf .
			"				display: none;" . $lf .
			"				visibility: hidden;" . $lf .
			"			}" . $lf .
			"			.message-wrapper {" . $lf .
			"				margin: 84px 21px 21px 21px;" . $lf .
			"			}" . $lf .
			"			h1.subject {" . $lf .
			"				font-size: 1.8em;" . $lf .
			"				line-height: 1.166667;" . $lf .
			"				top: 21px;" . $lf .
			"				left: 21px;" . $lf .
			"			}" . $lf .
			"		}" . $lf .
			"		@media handheld, only screen and (max-width: 399px) {" . $lf .
			"			.mobile-legacy-hide {" . $lf .
			"				display: none;" . $lf .
			"				visibility: hidden;" . $lf .
			"			}" . $lf .
			"			.header-left[style] {" . $lf .
			"				padding-top: 12px;" . $lf .
			"				padding-bottom: 7px;" . $lf .
			"				padding-left: 12px;" . $lf .
			"			}" . $lf .
			"			.header-center[style] {" . $lf .
			"				padding-top: 12px;" . $lf .
			"				padding-bottom: 7px;" . $lf .
			"			}" . $lf .
			"			.header-right[style] {" . $lf .
			"				padding-top: 12px;" . $lf .
			"				padding-bottom: 7px;" . $lf .
			"				padding-right: 12px;" . $lf .
			"			}" . $lf .
			"			.message[style] {" . $lf .
			"				padding-left: 12px;" . $lf .
			"				padding-right: 12px;" . $lf .
			"			}" . $lf .
			"			.message-wrapper {" . $lf .
			"				margin: 75px 12px 12px 12px;" . $lf .
			"			}" . $lf .
			"			h1.subject {" . $lf .
			"				top: 12px;" . $lf .
			"				left: 12px;" . $lf .
			"			}" . $lf .
			"		}" . $lf;
		} else {
			$output .= "		@media handheld, only screen and (max-width: 479px) {" . $lf .
			"			.mobile-hide {" . $lf .
			"				display: none;" . $lf .
			"				visibility: hidden;" . $lf .
			"			}" . $lf .
			"		}" . $lf .
			"		@media handheld, only screen and (max-width: 320px) {" . $lf .
			"			.mobile-legacy-hide {" . $lf .
			"				display: none;" . $lf .
			"				visibility: hidden;" . $lf .
			"			}" . $lf .
			"			.header-left[style] {" . $lf .
			"				padding-top: 12px;" . $lf .
			"				padding-bottom: 7px;" . $lf .
			"				padding-left: 12px;" . $lf .
			"			}" . $lf .
			"			.header-center[style] {" . $lf .
			"				padding-top: 12px;" . $lf .
			"				padding-bottom: 7px;" . $lf .
			"			}" . $lf .
			"			.header-right[style] {" . $lf .
			"				padding-top: 12px;" . $lf .
			"				padding-bottom: 7px;" . $lf .
			"				padding-right: 12px;" . $lf .
			"			}" . $lf .
			"			.message[style] {" . $lf .
			"				padding-left: 12px;" . $lf .
			"				padding-right: 12px;" . $lf .
			"			}" . $lf .
			"		}" . $lf;
		}
		$output .= "	</style>" . $lf .
			"</head>" . $lf . $lf;

		if ( true === $in_browser ) {
			$output .= '<body>' . $lf .
				'<div class="wrapper">' . $lf .
				'<h1 class="subject">' . _x( 'Subject', 'E-Mail', 'vca-asm' ) . ': ' . $subject . '</h1>' . $lf .
				'<div class="message-wrapper">' . $lf;
		} else {
			$output .= '<body style="margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;">' . $lf .
			'<center>';
		}
		$output .= '<table cellspacing="0" border="0" width="100%" style="height:100% !important;width:100% !important;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;"><tbody>' . $lf .
				'<tr>' . $lf .
				'<td valign="top" align="center" bgcolor="#00A8CF" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;background-color:#00a8cf;border-collapse:collapse;vertical-align:top;" class="header">' .
				'<table cellspacing="0" border="0" width="100%" style="margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;background:-moz-linear-gradient(top, #008fc1 0%, #00a8cf 100%);background:-webkit-gradient(linear, left top, left bottom, color-stop(0%,#008fc1), color-stop(100%,#00a8cf));background:-webkit-linear-gradient(top, #008fc1 0%,#00a8cf 100%);background:-o-linear-gradient(top, #008fc1 0%,#00a8cf 100%);background:-ms-linear-gradient(top, #008fc1 0%,#00a8cf 100%);background:linear-gradient(to bottom, #008fc1 0%,#00a8cf 100%);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#008fc1\',endColorstr=\'#00a8cf\',GradientType=0);"><tbody>' .
				'<tr>';

		if ( $with_images ) {
			$output .= '<td class="header-left" width="33%" valign="bottom" style="width:33%;text-align:left;padding-top:21px;padding-right:0;padding-bottom:16px;padding-left:21px;border-collapse:collapse;vertical-align:bottom;">';
		} else {
			$output .= '<td colspan="2" valign="middle" align="center" style="text-align:center;padding-top:21px;padding-right:21px;padding-bottom:0;padding-left:21px;border-collapse:collapse;vertical-align:middle;">' .
						'<h1 style="display:block;color:#ffffff;font-family:Verdana,Geneva,Helvetica,Arial,sans-serif;font-weight:bold;font-size:32px;line-height:1;margin-top:0px;margin-right:0px;margin-bottom:0px;margin-left:0px;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px;"><span style="font-family:\'Museo Sans\',Museo,\'Gill Sans Condensed\',\'Gill Sans MT Condensed\',\'Gill Sans\',\'Gill Sans MT\',Verdana,Helvetica,Arial,sans-serif;">ViVA CON AGUA</span></h1>' .
					'</td>' .
				'</tr><tr>' .
					'<td valign="bottom" style="text-align:left;padding-top:0;padding-right:0;padding-bottom:21px;padding-left:21px;border-collapse:collapse;vertical-align:bottom;">';
		}
					$output .= '<p style="font-family:Verdana,Geneva,Helvetica,Arial,sans-serif;color:#ffffff;font-size:13px;line-height:1.230769231;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;">' .
						_x( 'from', 'Newsletter', 'vca-asm' ) . ': ' . $from_name . '<br />' .
						_x( 'to', 'Newsletter', 'vca-asm' ) . ': ' . $for;

		if ( $with_images ) {
			$output .= '<br />';
		} else {
			$output .= '</p>' .
				'</td>' .
				'<td valign="bottom" style="text-align:right;padding-top:0;padding-right:21px;padding-bottom:21px;padding-left:0;border-collapse:collapse;vertical-align:bottom;">' .
					'<p style="font-family:Verdana,Geneva,Helvetica,Arial,sans-serif;color:#ffffff;font-size:13px;line-height:1.230769231;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;">';
		}
						$output .= strftime( '%e. %B %G', $time ) .
					'</p>' .
				'</td>';

		if ( $with_images ) {
			$output .= '<td class="header-center mobile-hide" width="34%" valign="middle" style="width:34%;text-align:center;padding-top:21px;padding-right:0;padding-bottom:16px;padding-left:0;border-collapse:collapse;vertical-align:middle;">' .
					'<h1 style="display:block;color:#ffffff;font-family:Verdana,Geneva,Helvetica,Arial,sans-serif;font-weight:bold;font-size:30px;line-height:1;margin-top:0px;margin-right:0px;margin-bottom:0px;margin-left:0px;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px;"><span style="font-family:\'Museo Sans\',Museo,\'Gill Sans Condensed\',\'Gill Sans MT Condensed\',\'Gill Sans\',\'Gill Sans MT\',Verdana,Helvetica,Arial,sans-serif;"><img alt="' . $center_alt . '" src="' . get_option( 'siteurl' ) . '/email_assets/' . $center_image . '" align="middle" style="border:0;height:auto;line-height:100%;outline:none;text-decoration:none;margin-top:0;margin-right:auto;margin-bottom:0;margin-left:auto;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;display:block;vertical-align:middle;" height="36" width="153"></span></h1>' .
				'</td>' .
				'<td class="header-right mobile-legacy-hide" width="33%" valign="baseline" style="width:33%;text-align:right;padding-top:21px;padding-right:21px;padding-bottom:16px;padding-left:0;border-collapse:collapse;vertical-align:baseline;">' .
					'<a title="' . __( 'Visit the Viva con Agua website', 'vca-asm' ) . '" href="' . $link_url . '"><h1 style="display:block;color:#ffffff;font-family:Verdana,Geneva,Helvetica,Arial,sans-serif;font-weight:bold;font-size:42px;line-height:1;margin-top:0px;margin-right:0px;margin-bottom:0px;margin-left:0px;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px;"><span style="font-family:\'Museo Sans\',Museo,\'Gill Sans Condensed\',\'Gill Sans MT Condensed\',\'Gill Sans\',\'Gill Sans MT\',Verdana,Helvetica,Arial,sans-serif;"><img alt="VcA" src="' . get_option( 'siteurl' ) . '/email_assets/' . $logo . '" align="right" style="border:0;height:auto;line-height:100%;outline:none;text-decoration:none;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;display:block;" height="79" width="153"></span></h1></a>' .
				'</td>';
		}

			$output .= '</tr>' .
				'</tbody></table>' .
				'</td>' . $lf .
				'</tr>' . $lf;

		if ( $with_images ) {
			$output .= '<!--[if !(mso)]><!--><tr>' . $lf .
				'<td valign="top" align="center" style="min-height:0px;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;border-collapse:collapse;vertical-align:top;">' .
					'<div style="width:100%;height:42px;background-image:url(' . get_option( 'siteurl' ) . '/email_assets/edge-top.png);background-repeat:repeat-x;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;">&nbsp;</div>' .
				'</td>' . $lf .
				'</tr><!--<![endif]-->' . $lf;
		}

			$output .= '<tr>' . $lf;

		if ( $with_images ) {
			$output .= '<td valign="middle" align="center" style="height:80%;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;text-align:center;border-collapse:collapse;vertical-align:middle;">' .
					'<table cellspacing="0" border="0" style="display:block;max-width:800px;margin:42px auto 21px;padding-top:0;padding-right:21px;padding-bottom:0;padding-left:21px;"><tbody><tr><td valign="middle" style="max-width:800px;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;text-align:left;border-collapse:collapse;">';
		} else {
			$output .= '<td valign="middle" align="center" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;text-align:center;border-collapse:collapse;vertical-align:middle;">' .
			'<table cellspacing="0" border="0" style="display:block;max-width:800px;margin:42px auto 21px;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;"><tbody><tr><td style="max-width:800px;padding-top:0;padding-right:21px;padding-bottom:0;padding-left:21px;text-align:left;border-collapse:collapse;">';
		}
						$output .= $message . $append . $append_reason .
					'</td></tr></tbody></table>' .
				'</td>' . $lf .
				'</tr>' . $lf;

		if ( $with_images ) {
			$output .= '<!--[if !(mso)]><!--><tr>' . $lf .
				'<td valign="bottom" align="center" style="min-height:0px;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;border-collapse:collapse;vertical-align:bottom;">' .
					'<div style="width:100%;height:42px;background-image:url(' . get_option( 'siteurl' ) . '/email_assets/edge-bottom.png);background-reoeat:repeat-x;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;">&nbsp;</div>' .
				'</td>' . $lf .
				'</tr><!--<![endif]-->' . $lf;
		}

				$output .= '<tr>' . $lf .
				'<td valign="bottom" align="center" bgcolor="#00A8CF" style="background-color:#00a8cf;text-align:center;border-collapse:collapse;vertical-align:bottom;" class="footer">' .
				'<div style="width:100%;height:auto;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;text-align:center;background:-moz-linear-gradient(top, #00a8cf 0%, #008fc1 100%);background:-webkit-gradient(linear, left top, left bottom, color-stop(0%,#00a8cf), color-stop(100%,#008fc1));background:-webkit-linear-gradient(top, #00a8cf 0%,#008fc1 100%);background:-o-linear-gradient(top, #00a8cf 0%,#008fc1 100%);background:-ms-linear-gradient(top, #00a8cf 0%,#008fc1 100%);background: linear-gradient(to bottom, #00a8cf 0%,#008fc1 100%);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#00a8cf\',endColorstr=\'#008fc1\',GradientType=0 );">' .
				'<p style="color:#ffffff;font-family:Verdana,Geneva,Helvetica,Arial,sans-serif;font-size:14px;line-height:1;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:21px;padding-right:21px;padding-bottom:21px;padding-left:21px;"><a title="' . __( 'Visit the Viva con Agua website', 'vca-asm' ) . '" href="' . $link_url . '" style="color:#ffffff;font-family:Verdana,Geneva,Helvetica,Arial,sans-serif;font-size:14px;line-height:1;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;text-decoration:none;"><span style="color:#ffffff;font-family:Verdana,Geneva,Helvetica,Arial,sans-serif;font-size:14px;line-height:1;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;text-decoration:none;"><span style="font-family:\'Museo Sans\',Museo,\'Gill Sans Condensed\',\'Gill Sans MT Condensed\',\'Gill Sans\',\'Gill Sans MT\',Verdana,Helvetica,Arial,sans-serif;">' . $organization_title . '</span></a></span></p>' .
				'</div>' .
				'</td>' . $lf .
				'</tr>' . $lf .
			'</tbody></table>' . $lf;

		if ( true === $in_browser ) {
			$output .= '</div>' . $lf .
					'</div>' . $lf .
				'</body>' . $lf .
				'<script type="text/javascript">' . $lf .
				'	function preventIt(e) {' . $lf .
				'		e.preventDefault();' . $lf .
				'		alert( "' . __( 'These links do not work in Preview-Mode.', 'vca-asm' ) . '" );' . $lf .
				'	}' . $lf .
				'</script>' . $lf .
				'</html>';
		} else {
			$output .= '</center></body></html>';
		}

		if ( $echo ) {
			echo $output;
		}
		return $output;
	}

} // class

endif; // class exists

?>