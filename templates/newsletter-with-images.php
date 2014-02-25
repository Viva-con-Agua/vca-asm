<?php

/**
 * Template for the text/html part of the newsletter
 *
 **/

if( empty( $message ) ) {
	$html_message = '<p>&nbsp;</p>';
} else {
	$html_message = $message;
}
if( ! isset( $mpart_message ) ) {
	$mpart_message = '';
}
if( ! isset( $subject ) ) {
	$subject = 'Newsletter';
}
if( ! isset( $from_name ) ) {
	$from_name = '?';
}
if( ! isset( $time ) ) {
	$time = time();
}
if( 2 == $membership ) {
	if ( 'region' == $receipient_group ) {
		global $vca_asm_regions;
		$for = sprintf( __( 'Active Members from %s', 'vca-asm' ), $vca_asm_regions->get_name( $receipient_id ) );
	} else {
		$for = __( 'Active Members', 'vca-asm' );
	}
} elseif( 'applicants' == $receipient_group || 'participants' == $receipient_group || 'waiting' == $receipient_group ) {
	$name = get_the_title( $receipient_id );
	$name = empty( $name ) ? __( 'Activity', 'vca-asm' ) : $name;
	if( 'applicants' == $receipient_group ) {
		$for = sprintf( __( 'Applicants to &quot;%s&quot;', 'vca-asm' ), $name );
	} elseif( 'participants' == $receipient_group ) {
		$for = sprintf( __( 'Participants of &quot;%s&quot;', 'vca-asm' ), $name );
	} elseif( 'waiting' == $receipient_group ) {
		$for = sprintf( __( 'Waiting List of &quot;%s&quot;', 'vca-asm' ), $name );
	}
} elseif( 'single' == $receipient_group ) {
	$for = trim( get_user_meta( $receipient_id, 'first_name', true ) . ' ' . get_user_meta( $receipient_id, 'last_name', true ) );
	$for = empty( $for ) ? __( 'Single Supporter', 'vca-asm' ) : $for;
} elseif( 'self' == $receipient_group ) {
	$for = __( 'Test Email', 'vca-asm' );
} elseif( 'admins' == $receipient_group ) {
	$for = __( 'Office / Administrators', 'vca-asm' );
} elseif( 'ho' == $receipient_group ) {
	$for = __( 'All Head Ofs', 'vca-asm' );
} elseif( 'region' == $receipient_group ) {
	global $vca_asm_regions;
	$for = sprintf( __( 'Supporters from %s', 'vca-asm' ), $vca_asm_regions->get_name( $receipient_id ) );
} else {
	$for = __( 'Supporters', 'vca-asm' );
}
if( ! isset( $append ) ) {
	$append = '';
}
$lf = "\n";
$eol = "\r\n";

$mpart_message .=  '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' .$lf .
	'<html style="width:100% !important;height:100% !important;">' .$lf .
	"<head>" .$lf .
	'	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' .$lf .
	'	<meta property="og:title" content="' . $subject . '" />' . $lf .
	'	<meta property="og:url" content="' . get_option( 'siteurl' ) . '" />' . $lf .
	"	<title>" . $subject . "</title>" . $lf .
	"	<style>" . $lf .
	"		body {" . $lf .
	"			-webkit-overflow-scrolling: touch;" . $lf .
	"			-webkit-tap-highlight-color: rgba(226,0,122,.5);" . $lf .
	"			-webkit-text-size-adjust: none;" . $lf .
	"			-ms-text-size-adjust: none;" . $lf .
	"			width:100% !important;" . $lf .
	"		}" . $lf .
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
	"		}" . $lf .
	"		p {" . $lf .
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
	"		}" . $lf .
	"		@media handheld, only screen and (max-width: 479px) {" . $lf .
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
	"		}" . $lf .
	"	</style>" . $lf .
	"</head>" . $lf . $lf .

	'<body style="margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;">' . $lf .
	'<center><table cellspacing="0" border="0" width="100%" style="height:100% !important;width:100% !important;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;"><tbody>' . $lf .
		'<tr>' . $lf .
		'<td valign="top" align="center" bgcolor="#00A8CF" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;background-color:#00a8cf;border-collapse:collapse;vertical-align:top;" class="header">' .
		'<table cellspacing="0" border="0" width="100%" style="margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;background:-moz-linear-gradient(top, #008fc1 0%, #00a8cf 100%);background:-webkit-gradient(linear, left top, left bottom, color-stop(0%,#008fc1), color-stop(100%,#00a8cf));background:-webkit-linear-gradient(top, #008fc1 0%,#00a8cf 100%);background:-o-linear-gradient(top, #008fc1 0%,#00a8cf 100%);background:-ms-linear-gradient(top, #008fc1 0%,#00a8cf 100%);background:linear-gradient(to bottom, #008fc1 0%,#00a8cf 100%);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#008fc1\',endColorstr=\'#00a8cf\',GradientType=0);"><tbody>' .
		'<tr>' .
		'<td class="header-left" width="33%" valign="bottom" style="width:33%;text-align:left;padding-top:21px;padding-right:0;padding-bottom:16px;padding-left:21px;border-collapse:collapse;vertical-align:bottom;">' .
			'<p style="font-family:Verdana,Geneva,Helvetica,Arial,sans-serif;color:#ffffff;font-size:13px;line-height:1.230769231;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;">' .
				_x( 'from', 'Newsletter', 'vca-asm' ) . ': ' . $from_name . '<br />' .
				_x( 'to', 'Newsletter', 'vca-asm' ) . ': ' . $for . '<br />' .
				strftime( '%e. %B %G', $time ) .
			'</p>' .
		'</td>' .
		'<td class="header-center mobile-hide" width="34%" valign="middle" style="width:34%;text-align:center;padding-top:21px;padding-right:0;padding-bottom:16px;padding-left:0;border-collapse:collapse;vertical-align:middle;">' .
			'<h1 style="display:block;color:#ffffff;font-family:Verdana,Geneva,Helvetica,Arial,sans-serif;font-weight:bold;font-size:30px;line-height:1;margin-top:0px;margin-right:0px;margin-bottom:0px;margin-left:0px;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px;"><span style="font-family:\'Gill Sans Condensed\',\'Gill Sans MT Condensed\',\'Gill Sans\',\'Gill Sans MT\',Verdana,Helvetica,Arial,sans-serif;"><img alt="NEWS" src="' . get_option( 'siteurl' ) . '/email_assets/news-logo@2x.gif" align="middle" style="border:0;height:auto;line-height:100%;outline:none;text-decoration:none;margin-top:0;margin-right:auto;margin-bottom:0;margin-left:auto;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;display:block;vertical-align:middle;" height="36" width="153"></span></h1>' .
		'</td>' .
		'<td class="header-right mobile-legacy-hide" width="33%" valign="baseline" style="width:33%;text-align:right;padding-top:21px;padding-right:21px;padding-bottom:16px;padding-left:0;border-collapse:collapse;vertical-align:baseline;">' .
			'<a title="' . __( 'Visit the Viva con Agua website', 'vca-asm' ) . '" href="http://' . __( 'vivaconagua.org', 'vca-asm' ) . '"><h1 style="display:block;color:#ffffff;font-family:Verdana,Geneva,Helvetica,Arial,sans-serif;font-weight:bold;font-size:42px;line-height:1;margin-top:0px;margin-right:0px;margin-bottom:0px;margin-left:0px;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px;"><span style="font-family:\'Gill Sans Condensed\',\'Gill Sans MT Condensed\',\'Gill Sans\',\'Gill Sans MT\',Verdana,Helvetica,Arial,sans-serif;"><img alt="VcA" src="' . get_option( 'siteurl' ) . '/email_assets/logo@2x.gif" align="right" style="border:0;height:auto;line-height:100%;outline:none;text-decoration:none;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;display:block;" height="79" width="153"></span></h1></a>' .
		'</td>' .
		'</tr>' .
		'</tbody></table>' .
		'</td>' . $lf .
		'</tr>' . $lf .
		'<!--[if !(mso)]><!--><tr>' . $lf .
		'<td valign="top" align="center" style="min-height:0px;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;border-collapse:collapse;vertical-align:top;">' .
			'<div style="width:100%;height:42px;background-image:url(' . get_option( 'siteurl' ) . '/email_assets/edge-top.png);background-repeat:repeat-x;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;">&nbsp;</div>' .
		'</td>' . $lf .
		'</tr><!--<![endif]-->' . $lf .
		'<tr>' . $lf .
		'<td valign="middle" align="center" style="height:80%;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;text-align:center;border-collapse:collapse;vertical-align:middle;">' .
			'<table cellspacing="0" border="0" style="display:block;max-width:800px;margin:42px auto 21px;padding-top:0;padding-right:21px;padding-bottom:0;padding-left:21px;"><tbody><tr><td valign="middle" style="max-width:800px;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;text-align:left;border-collapse:collapse;">' .
				$html_message . $append .
			'</td></tr></tbody></table>' .
		'</td>' . $lf .
		'</tr>' . $lf .
		'<!--[if !(mso)]><!--><tr>' . $lf .
		'<td valign="bottom" align="center" style="min-height:0px;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;border-collapse:collapse;vertical-align:bottom;">' .
			'<div style="width:100%;height:42px;background-image:url(' . get_option( 'siteurl' ) . '/email_assets/edge-bottom.png);background-reoeat:repeat-x;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;">&nbsp;</div>' .
		'</td>' . $lf .
		'</tr><!--<![endif]-->' . $lf .
		'<tr>' . $lf .
		'<td valign="bottom" align="center" bgcolor="#00A8CF" style="background-color:#00a8cf;text-align:center;border-collapse:collapse;vertical-align:bottom;" class="footer">' .
		'<div style="width:100%;height:auto;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;text-align:center;background:-moz-linear-gradient(top, #00a8cf 0%, #008fc1 100%);background:-webkit-gradient(linear, left top, left bottom, color-stop(0%,#00a8cf), color-stop(100%,#008fc1));background:-webkit-linear-gradient(top, #00a8cf 0%,#008fc1 100%);background:-o-linear-gradient(top, #00a8cf 0%,#008fc1 100%);background:-ms-linear-gradient(top, #00a8cf 0%,#008fc1 100%);background: linear-gradient(to bottom, #00a8cf 0%,#008fc1 100%);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#00a8cf\',endColorstr=\'#008fc1\',GradientType=0 );">' .
		'<p style="color:#ffffff;font-family:Verdana,Geneva,Helvetica,Arial,sans-serif;font-size:14px;line-height:1;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:21px;padding-right:21px;padding-bottom:21px;padding-left:21px;"><a title="' . __( 'Visit the Viva con Agua website', 'vca-asm' ) . '" href="http://' . __( 'vivaconagua.org', 'vca-asm' ) . '" style="color:#ffffff;font-family:Verdana,Geneva,Helvetica,Arial,sans-serif;font-size:14px;line-height:1;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;text-decoration:none;"><span style="color:#ffffff;font-family:Verdana,Geneva,Helvetica,Arial,sans-serif;font-size:14px;line-height:1;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;text-decoration:none;"><span style="font-family:\'Gill Sans Condensed\',\'Gill Sans MT Condensed\',\'Gill Sans\',\'Gill Sans MT\',Verdana,Helvetica,Arial,sans-serif;">Viva con Agua de Sankt Pauli e.V.</span></a></span></p>' .
		'</div>' .
		'</td>' . $lf .
		'</tr>' . $lf .
	'</tbody></table>' . $lf .
	'</center></body></html>';

?>