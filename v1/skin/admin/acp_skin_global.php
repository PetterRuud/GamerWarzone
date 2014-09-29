<?php

class acp_skin_global {
	
//===========================================================================
// global_wrapper
//===========================================================================

function global_main_wrapper() {
	global $zone;

	$date = date("Y");

	$HTML = "";
	//--starthtml--//

	$HTML .= <<<EOF
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		    <html>
		    <head>
		     <title>Admin Panel</title>
		      <link rel="StyleSheet" href="{$zone->skin_acp_url}/acp_css.css" type="text/css" />
		<style>
		/* Loading message */
		div#loading {
			font-size: 11px; 
			color: #222;
			position: fixed;
			z-index: 100;
			width: 300px;
			left: 50%; top: 50%;
			margin-left: -166px; 
			margin-top: -56px;
			text-align: center;
			padding: 15px;
			border: 1px solid #5D789C;
			background-color: #ACBFD9; /* #eee; */
			cursor: pointer; cursor: hand;
		}
		</style>
		    <script type="text/javascript" src="../js/portal.js"></script>
		    <script type="text/javascript" src="../js/tooltips.js"></script>
			<script type="text/javascript" src="../js/ips_menu_html.js"></script>
		      </head>
		      <body>
		<!-- onload="hideLoadingPage();
		  <div id="loading" align="center" onclick="hideLoadingPage()">
		    <b>Page Loading... please wait!</b><br /><br />
		    <script type="text/javascript" src="../js/loadtimerbar.js"></script><br />
		    This message not going away?<br />Ensure Javascript is on and click the box
		  </div> -->
	<div id='ipdwrapper'><!-- WRAPPER -->
	<%CONTENT%>
	<br />
	</div><!-- / WRAPPER -->
	</body>
	</html>
EOF;

	//--endhtml--//
	return $HTML;
	}

//===========================================================================
// Main Frames
//===========================================================================
function global_frame_wrapper() {
	global $zone;
	$year = date( 'Y' );

	$HTML = "";
	//--starthtml--//

	$HTML .= <<<EOF
	<!-- TOP TABS -->
	<div class='tabwrap-main'>
	<%TABS%>
	<div class='logoright'><img src='acp-logo.png' alt='Portal ACP' border='0' /></div>
	</div>
	<!-- / TOP TABS -->
	<div class='sub-tab-strip'>
	    <%MEMBERBAR%>
		<%NAV%>
	</div>
	<div class='outerdiv' id='global-outerdiv'><!-- OUTERDIV -->
	<table cellpadding='0' cellspacing='8' width='100%' id='tablewrap'>
	<tr>
	 <td width='22%' valign='top' id='leftblock'>
	 <div>
	 <!-- LEFT CONTEXT SENSITIVE MENU -->
	 <%MENU%>
	 <!-- / LEFT CONTEXT SENSITIVE MENU -->
	 </div>
	 </td>
	 <td width='78%' valign='top' id='rightblock'>
	 <div><!-- RIGHT CONTENT BLOCK -->
	 <%HELP%>
	 <%MSG%>
	 <%SECTIONCONTENT%>
	 </div><!-- / RIGHT CONTENT BLOCK -->
	 </td>
	</tr>
	</table>
	</div><!-- / OUTERDIV -->
	<%QUERIES%>
EOF;

	//--endhtml--//
	return $HTML;
	}

//===========================================================================
// Information box...
//===========================================================================
function information_box($title="", $content) {
	global $zone;
	$HTML = "";
	//--starthtml--//

	$HTML .= <<<EOF
	<div class='information-box'>
	 <img src='{$zone->skin_acp_url}/images/icon_information.png' alt='information' />
	 <h2>$title</h2>
	 <p>
	 	<br />
	 	$content
	 </p>
	</div>
EOF;

	//--endhtml--//
	return $HTML;
}


//===========================================================================
// Information box...
//===========================================================================
function warning_box($title="", $content) {
	global $zone;
	$HTML = "";
	//--starthtml--//

	$HTML .= <<<EOF
	<div class='warning-box'>
	 <img src='{$zone->skin_acp_url}/images/icon_warning.png' alt='information' />
	 <h2>$title</h2>
	 <p>
	 	<br />
	 	$content
	 </p>
	</div>
EOF;

	//--endhtml--//
	return $HTML;
	}


//===========================================================================
// Help box...
//===========================================================================
function help_box( $show=array(), $title="", $content) {
	global $zone;
	$HTML = "";
	//--starthtml--//

	$HTML .= <<<EOF
	<div class='help-box' style="display:{$show['div_fc']}" id="fc_{$show['div_key']}">
		<h2>
			<div style='float:right;'><a href="javascript:togglecategory('{$show['div_key']}', 0);"><img style='margin: 4px 4px 4px 2px;' src='{$zone->skin_acp_url}/images/arrow_down.png' alt='Show' /></a></div>
			<div><a href="javascript:togglecategory('{$show['div_key']}', 0);" style='text-decoration:none;'>{$title}</a></div>
		</h2>
	</div>
	<div class='help-box' style="display:{$show['div_fo']}" id="fo_{$show['div_key']}">
	 <img src='{$zone->skin_acp_url}/images/icon_help.png' alt='help' />
	  <h2>
	  	<div style='float:right;'><a href="javascript:togglecategory('{$show['div_key']}', 1);"><img style='margin: 4px 4px 4px 2px;' src='{$zone->ips->skin_acp_url}/images/arrow_up.png' alt='Hide' /></a></div>
	  	<div><a href="javascript:togglecategory('{$show['div_key']}', 1);" style='text-decoration:none;'>{$title}</a></div>
	  </h2>
	 <p>
	 	<br />
	 	$content
	 </p>
	</div>
EOF;

	//--endhtml--//
	return $HTML;
}
//===========================================================================
// Top TABS
//===========================================================================
function global_tabs($onoff="") {
	global $zone;

	$HTML = "";
	//--starthtml--//

	$HTML .= <<<EOF
		<div class='{$onoff['portal']}'>
		<img src='{$zone->skin_acp_url}/images/tabs_main/dashboard.png' style='vertical-align:middle' width='24' height='24' alt='' /> 
		<a href='?section=portal'>PORTAL</a></div>
		
		<div class='{$onoff['settings']}'>
		<img src='{$zone->skin_acp_url}/images/tabs_main/tools.png' style='vertical-align:middle' width='24' height='24' alt='' /> 
		<a href='?section=settings'>SETTINGS</a></div>
		
		<div class='{$onoff['affiliates']}'>
		<img src='{$zone->skin_acp_url}/images/folder_components/index/validating.png' style='vertical-align:middle' width='24' height='24' alt='' /> 
		<a href='?section=affiliates'>AFFILIATES</a></div>
		<div class='{$onoff['games']}'><img src='{$zone->skin_acp_url}/images/folder_components/index/emos.png' style='vertical-align:middle' width='24' height='24' alt='' /> 
		<a href='?section=games'>GAMES</a></div>
		<div class='{$onoff['articles']}'><img src='{$zone->skin_acp_url}/images/folder_components/index/settings.png' style='vertical-align:middle' width='24' height='24' alt='' /> 
		<a href='?section=articles'>ARTICLES</a></div>
		<div class='{$onoff['replays']}'><img src='{$zone->skin_acp_url}/images/tabs_main/content.png' style='vertical-align:middle' width='24' height='24' alt='' /> 
		<a href='?section=replays'>REPLAYS</a></div>
		<div class='{$onoff['servers']}'><img src='{$zone->skin_acp_url}/images/tabs_main/system.png' style='vertical-align:middle' width='24' height='24' alt='' /> 
		<a href='?section=servers'>SERVERS</a></div>

EOF;

	//--endhtml--//
	return $HTML;
}

//===========================================================================
// Query HTML
//===========================================================================
function global_query_output($queries="") {
	global $zone;
$HTML = "";
//--starthtml--//

$HTML .= <<<EOF
<br /><br />
<div align='center' style='margin-left:auto;margin-right:auto'>
<div class='tableborder' style='vertical-align:bottom;text-align:left;width:75%;color:#555'>
 <div style='padding:5px'><b>Queries</b></div>
 <div class='tablerow1' style='padding:6px;color:#555;font-size:10px'>$queries</div>
</div>
</div>
EOF;

//--endhtml--//
return $HTML;
}

//===========================================================================
// Information box...
//===========================================================================
	function global_memberbar() {
		global $zone;;

	$HTML = "";
	//--starthtml--//

	$HTML .= <<<EOF
	<div class='global-memberbar'>
	 Welcome <strong>{$zone->ips->member['members_display_name']}</strong> [
	 <a href="{$zone->ips->vars['home_url']}" target='_blank'>Portal</a> &middot;
	 <a href='{$zone->ips->vars['board_url']}/index.php' target='_blank'>Forum</a> &middot;
	 <a href='{$zone->ips->base_url}&amp;act=login&amp;code=login-out'>Log Out</a>
	 ]
	</div>
EOF;

	//--endhtml--//
	return $HTML;
}

//===========================================================================
// GLOBAL MENU CAT LINK
//===========================================================================

function global_menu_cat_link( $cid, $pid, $icon, $theurl, $url, $extra_css, $name ) {
	global $zone;
	$HTML = "";
	//--starthtml--//

	$HTML .= <<<EOF
	<div class='menulinkwrap'>&nbsp;{$icon}&nbsp;<a href='{$theurl}$url' style='text-decoration:none{$extra_css}'>$name</a></div>
EOF;

	//--endhtml--//
	return $HTML;
}

//===========================================================================
// GLOBAL WRAP NAV
//===========================================================================

function global_menu_cat_wrap($name="", $links="", $id = "", $desc) {
	global $zone;
	$HTML = "";
	//--starthtml--//

	$HTML .= <<<EOF
	<div class='menuouterwrap'>
	  <div class='menucatwrap'><img src='{$zone->skin_acp_url}/images/menu_title_bullet.gif' style='vertical-align:bottom' border='0' /> $name</div>
	  $links
	</div>
	<br />
EOF;

	//--endhtml--//
	return $HTML;
}

//===========================================================================
// GLOBAL WRAP NAV
//===========================================================================
function global_wrap_nav($links="") {
		global $zone;

$HTML = "";
	//--starthtml--//

	$HTML .= <<<EOF
	<div class='navwrap'>$links</div>
EOF;

	//--endhtml--//
	return $HTML;
}
		

//===========================================================================
// Log in form
//===========================================================================
function log_in_form($query_string="", $message="", $name="") {
	global $zone;

$HTML = "";
//--starthtml--//

$HTML .= <<<EOF
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<div align='center'>
<div style='width:500px'>
<div class='outerdiv' id='global-outerdiv'><!-- OUTERDIV -->
<table cellpadding='0' cellspacing='8' width='100%' id='tablewrap'>
<tr>
 <td id='rightblock'>
 <div>
 <form id='loginform' action='{$zone->ips->base_url}&amp;act=login&amp;code=login-complete' method='post'>
 <input type='hidden' name='qstring' value='$query_string' />
  <table width='100%' cellpadding='0' cellspacing='0' border='0'>
  <tr>
   <td width='200' class='tablerow1' valign='top' style='border:0px;width:200px'>
   <div style='text-align:center;padding-top:20px'>
   	<img src='{$zone->skin_acp_url}/images/acp-login-lock.gif' alt='Portal' border='0' />
   </div>
   <br />
   <div class='desctext' style='font-size:10px'>
   <div align='center'><strong>Welcome to Zone Portal</strong></div>
   <br />
  	<div style='font-size:9px;color:gray'>&copy; Zone, Inc.
	This program is protected by international copyright laws as described in the license agreement.</div>
   </div>
   </td>
   <td width='300' style='width:300px' valign='top'>
	 <table width='100%' cellpadding='5' cellspacing='0' border='0'>
	 <tr>
	  <td colspan='2' align='center'>
		 <br /><img src='{$zone->skin_acp_url}/images/acp-login-logo.gif' alt='Portal' border='0' />
		 <div style='font-weight:bold;color:red'>$message</div>
	  </td>
	 </tr>
	 <tr>

		<td align='right'><strong>User Name</strong></td>

	  <td><input style='border:1px solid #AAA' type='text' size='20' name='username' id='namefield' value='$name' /></td>
	 </tr>
	 <tr>
	  <td align='right'><strong>Password</strong></td>
	  <td><input style='border:1px solid #AAA' type='password' size='20' name='password' value='' /></td>
	 </tr>
	 <tr>
	  <td colspan='2' align='center'><input type='submit' style='border:1px solid #AAA' value='Log In' /></td>
	 </tr>
	 <tr>
	  <td colspan='2'><br />
		  
	  </td>
	 </tr>
	</table>
   </td>
  </tr>
  </table>
 </form>
 
 </div>
 </td>
</tr>
</table>
</div><!-- / OUTERDIV -->

</div>
</div>
<script type='text/javascript'>
<!--
  if (top.location != self.location) { top.location = self.location }
  
  try
  {
  	window.onload = function() { document.getElementById('namefield').focus(); }
  }
  catch(error)
  {
  	alert(error);
  }
  
//-->
</script>
EOF;

//--endhtml--//
return $HTML;
}


//===========================================================================
// GLOBAL MAIN ACP FOOTER
//===========================================================================

function global_footer($date="") {
	global $zone;

$HTML = "";
//--starthtml--//

$HTML .= <<<EOF
<br />
 <div align='right' id='jwrap'><strong>Quick Jump</strong> <!--JUMP--></div>
<!-- <div class='copy' align='center'>Zone Portal &copy $date</div>-->
</div><!-- / WRAPPER -->
</body>
</html>
EOF;

//--endhtml--//
return $HTML;
}
	
//===========================================================================
// GLOBAL MAIN ACP HEADER
//===========================================================================

function global_header() {
	global $zone;

$HTML = "";
//--starthtml--//

$HTML .= <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xml:lang="en" lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset={$zone->ips->vars['gb_char_set']}" /> 
<title><%TITLE%></title>
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Cache-Control" content="no-cache" />
<meta http-equiv="Expires" content="Mon, 06 May 1996 04:57:00 GMT" />
<link rel="shortcut icon" href="favicon.ico" />
<style type='text/css' media="all">
@import url( "{$zone->skin_acp_url}/acp_css.css" );
</style>
 <script type="text/javascript">
 <!--
  var ipb_var_st            = "{$zone->ips->input['st']}";
  var ipb_lang_tpl_q1       = "{$zone->ips->lang['tpl_q1']}";
  var ipb_var_phpext        = "{$zone->ips->vars['php_ext']}";
  var ipb_var_base_url      = "{$zone->ips->base_url}";
  var ipb_var_cookieid      = "{$zone->ips->vars['cookie_id']}";
  var ipb_var_cookie_domain = "{$zone->ips->vars['cookie_domain']}";
  var ipb_var_cookie_path   = "{$zone->ips->vars['cookie_path']}";
  var ipb_skin_url          = "{$zone->ips->skin_acp_url}";
  var ipb_var_image_url		= "{$zone->ips->skin_acp_url}/images";
  var ipb_md5_check         = "{$zone->ips->md5_check}";
  var use_enhanced_js       = {$zone->ips->can_use_fancy_js};
  var ipb_is_acp            = 1;
  //-->
 </script>
 <script type="text/javascript" src='{$zone->ips->vars['board_url']}/jscripts/ips_ipsclass.js'></script>
 <script type="text/javascript" src='{$zone->ips->vars['board_url']}/jscripts/ipb_global.js'></script>
 <script type="text/javascript" src='{$zone->ips->vars['board_url']}/jscripts/ips_menu.js'></script>
 <script type="text/javascript" src='{$zone->skin_acp_url}}/acp_js.js'></script>
 <script type="text/javascript" src='{$zone->skin_acp_url}/acp_js_skin/ips_menu_html.js'></script>
 <script type="text/javascript" src='{$zone->ips->vars['board_url']}/jscripts/ips_xmlhttprequest.js'></script>
 <script type="text/javascript" src='{$zone->ips->vars['board_url']}/jscripts/dom-drag.js'></script>
 <script type="text/javascript">
 //<![CDATA[
 var ipsclass = new ipsclass();
 ipsclass.init();
 // Validate form to be overwritten
 function ValidateForm() { }
 //]]>
 </script>
</head>
<body>
<div id='loading-layer' style='display:none'>
	<div id='loading-layer-shadow'>
	   <div id='loading-layer-inner' >
		   <img src='{$zone->skin_acp_url}/images/loading_anim.gif' style='vertical-align:middle' border='0' alt='Loading...' />
		   <span style='font-weight:bold' id='loading-layer-text'>Loading Data. Please Wait...</span>
	   </div>
	</div>
</div>
<div id='ipdwrapper'><!-- IPDWRAPPER -->
EOF;

//--endhtml--//
return $HTML;
}


//===========================================================================
// Pagination Wrapper
//===========================================================================
function pagination_compile($start="",$previous_link="",$start_dots="",$pages="",$end_dots="",$next_link="",$total_pages="",$per_page="",$base_link="") {
	global $zone;
$HTML = "";
//--starthtml--//


$HTML .= <<<EOF
{$start}{$start_dots}{$previous_link}{$pages}{$next_link}{$end_dots}
<script type="text/javascript">
//<![CDATA[
ipb_pages_shown++;
var pgjmp = document.getElementById( 'page-jump' );
pgjmp.id  = 'page-jump-'+ipb_pages_shown;
ipb_pages_array[ ipb_pages_shown ] = new Array( '{$base_link}', $per_page, $total_pages );

// Change out CSS
css_mainwrap = 'popupmenu-pagelinks';

menu_build_menu(
	pgjmp.id,
	"<div onmouseover='pages_st_focus("+ipb_pages_shown+")' align='center'>{$zone->ips->lang['global_page_jump']}</div><input type='hidden' id='st-type-"+ipb_pages_shown+"' value='{$st}' /><input type='text' size='5' name='st' id='st-"+ipb_pages_shown+"' /> <input type='button' class='button' onclick='do_multi_page_jump("+ipb_pages_shown+");' value='{$zone->ips->lang['jmp_go']}' />",
	1 );
	
css_mainwrap = 'popupmenu';
//]]>
</script>
EOF;

//--endhtml--//
return $HTML;
}

//===========================================================================
// Pagination: Current Page
//===========================================================================
function pagination_current_page($page="") {
	global $zone;
$HTML = "";
//--starthtml--//


$HTML .= <<<EOF
&nbsp;<span class="pagecurrent">{$page}</span>
EOF;

//--endhtml--//
return $HTML;
}

//===========================================================================
// Pagination: End Dots
//===========================================================================
function pagination_end_dots($url="") {
$HTML = "";
//--starthtml--//


$HTML .= <<<EOF
&nbsp;<span class="pagelinklast"><a href="$url" title="Go to last">&raquo;</a></span>&nbsp;
EOF;

//--endhtml--//
return $HTML;
}

//===========================================================================
// Pagination: Make jump menu
//===========================================================================
function pagination_make_jump($pages=1) {
	global $zone;
$HTML = "";
//--starthtml--//


$HTML .= <<<EOF
<span class="pagelink" id='page-jump'>$pages {$zone->ips->lang['tpl_pages']} <img src='{$zone->skin_acp_url}/images/menu_action_down.gif' alt='V' title='{$zone->ips->lang['global_open_menu']}' border='0' /></span>&nbsp;
EOF;

//--endhtml--//
return $HTML;
}

//===========================================================================
// Pagination: Next Link
//===========================================================================
function pagination_next_link($url="") {
	global $zone;
$HTML = "";
//--starthtml--//


$HTML .= <<<EOF
&nbsp;<span class="pagelink"><a href="$url" title="Next">&gt;</a></span>
EOF;

//--endhtml--//
return $HTML;
}

//===========================================================================
// Pagination: Regular Link
//===========================================================================
function pagination_page_link($url="",$page="") {
	global $zone;
$HTML = "";
//--starthtml--//


$HTML .= <<<EOF
&nbsp;<span class="pagelink"><a href="$url" title="$page">$page</a></span>
EOF;

//--endhtml--//
return $HTML;
}

//===========================================================================
// Pagination: Previous Link
//===========================================================================
function pagination_previous_link($url="") {
	global $zone;
$HTML = "";
//--starthtml--//


$HTML .= <<<EOF
<span class="pagelink"><a href="$url" title="Previous">&lt;</a></span>
EOF;

//--endhtml--//
return $HTML;
}

//===========================================================================
// Pagination: Start Dots
//===========================================================================
function pagination_start_dots($url="") {
	global $zone;
$HTML = "";
//--starthtml--//


$HTML .= <<<EOF
<span class="pagelinklast"><a href="$url" title="Go to first">&laquo;</a></span>&nbsp;
EOF;

//--endhtml--//
return $HTML;
}


//===========================================================================
// Index
//===========================================================================
function acp_onlineadmin_row( $r ) {
	global $zone;

$HTML = "";
//--starthtml--//

$HTML .= <<<EOF
<tr>
 <td class='tablerow1' align='center'>
	 <div><img src='{$r['pp_thumb_photo']}' width='{$r['pp_thumb_width']}' height='{$r['pp_thumb_height']}' style='border:1px solid #000000; background-color:#FFFFFF; padding:6px' /></div>
</td>
 <td class='tablerow2'>
	<strong style='font-size:12px'><a href='{$zone->ips->vars['board_url']}/index.php?showuser={$r['session_member_id']}' target='_blank'>{$r['members_display_name']}</a></strong>
	<div style='margin-top:6px'>Logged in: {$r['_log_in']}</div>
	<div class='desctext'>IP: {$r['session_ip_address']}</div>
	<div class='desctext'>Using: {$r['session_location']}</div>
	<div class='desctext'>Last click: {$r['_click']}</div>
</td>
</tr>
EOF;

//--endhtml--//
return $HTML;
}

//===========================================================================
// Index
//===========================================================================
function acp_onlineadmin_wrapper($content) {
	global $zone;

$HTML = "";
//--starthtml--//

$HTML .= <<<EOF
<div class='homepage_border'>
 <div class='homepage_sub_header'>Administrators Using ACP</div>
 <table width='100%' cellpadding='4' cellspacing='0'>
 $content
 </table>
</div>
EOF;

//--endhtml--//
return $HTML;
}
}// EOC
?>