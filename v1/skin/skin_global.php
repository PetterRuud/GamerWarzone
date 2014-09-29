<?php
class skin_global {
	var $zone;

//===========================================================================
// global_wrapper
//===========================================================================

function global_main_wrapper() {
	global $zone;

	$date = date("Y");

	$HTML = "";
	//--starthtml--//

	$HTML .= <<<EOF
	<%HEADER%>
	<%CONTENT%>
	<%FOOTER%>
EOF;

	//--endhtml--//
	return $HTML;
}

//===========================================================================
// Main Frames
//===========================================================================
function global_frame_wrapper() {
	global $zone;

	$HTML = "";
	//--starthtml--//
		$HTML .= <<<EOF
<div class="statsbg">
<div class="statsbox">
<div class="statsbox_title">Latest Forum Topics</div>
<%LATESTTOPICS%>
</div>
<div class="statsbox">
<div class="statsbox_title">Latest Files</div>
<%DOWNLOADS%>
</div>
<div class="statsad">
</div>
</div>
<div id="wrapper2"><!-- start wrapper2 -->
<div id="wrapper3"><!-- start wrapper3 -->
<br />
<%SECTIONCONTENT%>
</div><!-- end wrapper3-->
</div><!-- end wrapper2 -->
<div id="right"><!-- start right -->
<%RIGHT%>
</div><!--right end-->
<div class="clear"></div>
<br />

EOF;

	//--endhtml--//
	return $HTML;
}

//===========================================================================
// HEADER
//===========================================================================
function global_header($css="",$portal="",$portaltitle="") {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xml:lang="en" lang="en" xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
<meta http-equiv="content-type" content="text/html; charset=<%CHARSET%>" /> 
<meta name="Description" content="Tiberium wars, command and conquer, red alert, ,red alert 3, tiberian sun, cheat zone, community, forum, starcraft, marine, commander, gfx, screenshots, videos, replays, renders, general discussion" />
<meta name="Keywords" content="| Forum | Articles | Affiliates | Strategy Guides | News | Replays |" />
<meta name="Distribution" content="Global" />
<meta name="Author" content="Petter Ruud" />
<meta name="Robots" content="index,follow" />
<meta name="revisit-after" content="0 Days" />
<meta name="copyright" content="Copyright (c) 2008 gamerwarzone.com" />
<meta name="verify-v1" content="yJqT8m2/TbJ/M6a40/IWXg4UAbBmSKFMVKlNqryaAf0=" />
<title><%PAGETITLE%></title>
<link href="css/{$css}" type="text/css" rel="stylesheet" />
<link rel="shortcut icon" href="images/gwz.ico" />
<script type="text/javascript" src="js/jquery-1.2.6.js"></script>
<script type="text/javascript" src="js/jquery.cycle.all.js"></script>
<script type="text/javascript" src="js/tabber.js"></script>
<script type="text/javascript" src="js/portal.js"></script>
<script language='JavaScript' type='text/javascript'>
<!--//<![CDATA[
$(document).ready(
function() { $('#fade').cycle({ 
				    fx:     'fade', prev:   '#prev1', next:   '#next1', 
				    timeout: 5000 , pause: 2 });	}); 	
//]]>//-->

</script>
</head>
<body>

<div id="topbar">
<div class="memberbar">
<%MEMBERBAR%>
</div>
	</div>
<div id="header">
			<div class="logo"></div>
			
			<%SUBMENU%>

				
		</div>
		<div class="cleared"></div>
		
<div id="wrapper">

EOF;
	//--endhtml--//
	return $HTML;
}
//===========================================================================
// Top TABS
//===========================================================================
function global_submenu($onoff="") {
	global $zone;

	$HTML = "";
	//--starthtml--//

	$HTML .= <<<EOF
<div class="nav">
					<div class="home-{$onoff['home']}"><a href="{$zone->ips->vars['home_url']}"><span>&nbsp;</span></a></div>
					<div class="forum-{$onoff['forum']}"><a href="{$zone->ips->vars['board_url']}"><span>&nbsp;</span></a></div>
					<div class="articles-{$onoff['articles']}"><a href="{$zone->ips->vars['home_url']}/index.php?section=articles&page=categories"><span>&nbsp;</span></a></div>
					<div class="ps-{$onoff['ps']}"><a href="{$zone->ips->vars['home_url']}/index.php?section=ips"><span>&nbsp;</span></a></div>
					<div class="replays-{$onoff['replays']}"><a href="{$zone->ips->vars['home_url']}/index.php?section=replays"><span>&nbsp;</span></a></div>
					<div class="dl-{$onoff['dl']}"><a href="{$zone->ips->vars['board_url']}/index.php?automodule=downloads"><span>&nbsp;</span></a></div>
					<div class="affiliates-{$onoff['affiliates']}"><a href="{$zone->ips->vars['home_url']}/index.php?section=affiliates&amp;page=view"><span>&nbsp;</span></a></div>
					<div class="xwis-{$onoff['xwis']}"><a href="{$zone->ips->vars['home_url']}/index.php?section=xwis"><span>&nbsp;</span></a></div>


			</div>

EOF;
	//--endhtml--//
	return $HTML;
}
//===========================================================================
// SECTIONCONTENT
//===========================================================================
function global_section() {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
<div class="contentbox">
<div id="wholefade"><!-- start slideshow -->
<div id="fade" class="highlightbox"><!-- start fade -->
<div><!-- start -->
<a href="{$zone->ips->vars['board_url']}/index.php?showtopic=12953">
<div class="highlightbox" style="background-image: url(images/front/3.jpg)">
<div class="highlightbox_title">
Red Alert 3 HD Trailer</div>
<div class="highlightbox_description">Game Trailers have posted a HD trailer of Red Alert 3</div>
</div>
</a>
<div class="next_title">CNCWARZONE PORTAL OPEN</div> 
</div><!-- end -->
<div><!-- start -->
<a href="#">
<div class="highlightbox" style="background-image: url(images/front/1.jpg)">
<div class="highlightbox_title">CNCWARZONE PORTAL OPEN</div>
<div class="highlightbox_description">Take a look at the command &amp; conquer portal</div>
</div>
</a>
<div class="next_title">Red Alert 3 Announced</div> 
</div><!-- end -->
<div><!-- start -->
<a href="{$zone->ips->vars['home_url']}/index.php?section=articles&page=article&id=1">
<div class="highlightbox" style="background-image: url(images/front/2.jpg)">
<div class="highlightbox_title">RED ALERT 3</div>
<div class="highlightbox_description">Red Alert 3 Announced...</div>
</div>
</a>
<div class="next_title">Red Alert 3 HD Trailer</div>
</div><!-- end -->
</div><!-- end fade -->
<div id="controls"></div>
<div id="highlight_nav"><a id="prev1" href="#">&laquo;</a> <a id="next1" href="#">&raquo; NEXT - </a></div>
</div><!-- end slideshow -->
<div class="clear"></div>
</div>
<div class="tabber"><!-- start tabber -->
	<div class="tabbertab" title="News &amp; Updates">
	<div class="clear"></div>
	<div class="contentbox"><%NEWS%></div>
	</div>
	<div class="tabbertab" title="Gaming News">
	<div class="contentbox"><%GGNEWS%></div>
	</div>
	<div class="tabbertab" title="C&amp;C News">
	<div class="contentbox"><%CCNEWS%></div>
	</div>
	<div class="tabbertab" title="Recent Articles">
	<div class="clear"></div>
	<div class="contentbox"><%ARTICLES%></div>
	</div>
	<div class="tabbertab" title="Site Updates">
	<div class="contentbox"><%SITENEWS%></div>
	</div>
</div><!-- end tabber -->


EOF;

//--endhtml--//
return $HTML;
}

//===========================================================================
// RIGHT
//===========================================================================
function global_right($title="",$image="",$starter="",$views="",$comments="",$id="") {
	global $zone;

	$HTML = "";
	//--starthtml--//

	$HTML .= <<<EOF
<div class="online"></div>
<br />
<div id="rightmenu">
<div class="rightmenu_box">
<div class="title">Navigation</div>
<ul>
	<li><a href="{$zone->ips->vars['home_url']}">Home</a></li>
	<li><a href="{$zone->ips->vars['board_url']}">Forums</a></li>
	<li><a href="{$zone->ips->vars['home_url']}/index.php?section=articles&page=categories">Articles</a></li>
	<li><a href="{$zone->ips->vars['home_url']}/index.php?section=replays">Replays</a></li>
	<li><a href="{$zone->ips->vars['board_url']}/index.php?autocom=teams">Clans</a></li>
	<li><a href="{$zone->ips->vars['home_url']}/index.php?section=affiliates&amp;page=view">Affiliates</a></li>
	<li><a href="{$zone->ips->vars['board_url']}/index.php?autocom=downloads">Downloads</a></li>
<li><a href="{$zone->ips->vars['home_url']}/index.php?section=ips">Player Search</a></li>
<li class="{$onoff['xwis']}"><a href="{$zone->ips->vars['home_url']}/index.php?section=xwis">XWIS</a></li>

</ul>
</div>
<div class="rightmenu_box">
<div class="title">Supported Games</div>
	<ul>
<li><a href="{$zone->ips->vars['home_url']}/index.php?portal=tiberiansun&amp;section=games&amp;page=info&amp;gameid=3">Tiberian Sun</a></li>
<li><a href="{$zone->ips->vars['home_url']}/index.php?portal=redalert2&amp;section=games&amp;page=info&amp;gameid=6">Red Alert 2</a></li>
<li><a href="{$zone->ips->vars['home_url']}/index.php?portal=tiberiumwars&amp;section=games&amp;page=info&amp;gameid=2">Tiberium Wars</a></li>
<li><a href="{$zone->ips->vars['home_url']}/index.php?portal=kaneswrath&amp;section=games&amp;page=info&amp;gameid=7">kane's Wrath</a></li>
<li><a href="{$zone->ips->vars['home_url']}/index.php?portal=redalert3&amp;section=games&amp;page=info&amp;gameid=1">Red Alert 3</a></li>	
	</ul>
</div>

<div class="rightmenu_box">
<div class="title">Stats</div>
<ul>
<%STATS%>
</ul>
</div>

<div class="rightmenu_box">
<div class="title">Affiliates</div>

<table width="100%">
<tr>
<%AFFILIATES%>
</tr>
</table>
<div class="rightmenu-button"><a href="{$zone->ips->vars['home_url']}/index.php?section=affiliates&amp;page=view">View All</a></div>
</div>
<br />
<br />
<div class="rightmenu-button">
<script type="text/javascript"><!--
google_ad_client = "pub-0184265030192327";
/* 200x200, opprettet 01.10.08 */
google_ad_slot = "9208215754";
google_ad_width = 200;
google_ad_height = 200;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
<br />

<form action="http://www.google.com/cse" id="cse-search-box" target="_blank">
  <div>
    <input type="hidden" name="cx" value="partner-pub-0184265030192327:gqbm8f-c49l" />
    <input type="hidden" name="ie" value="ISO-8859-1" />
    <input type="text" name="q" size="25" />
    <input type="submit" name="sa" value="Search" />
  </div>
</form>
<script type="text/javascript" src="http://www.google.com/coop/cse/brand?form=cse-search-box&amp;lang=en"></script>
</div>
</div>
EOF;

	//--endhtml--//
	return $HTML;
}


//===========================================================================
// STATS
//===========================================================================
function stats($posts="",$members="",$last="") {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
		<li>Posts: <i>$posts</i></li>
		<li>Members: <i>$members</i></li>
		<li>Newest Member: <i>$last</i></li>
EOF;

//--endhtml--//
return $HTML;
}
//===========================================================================
// ONLINE
//===========================================================================
function online($total="",$members="",$guests="") {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
		$total
EOF;

//--endhtml--//
return $HTML;
}
function online_list($online="") {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
Online: $online
EOF;

//--endhtml--//
return $HTML;
}
//===========================================================================
// LAST TOPICS
//===========================================================================
function latest_topics($tid="",$title="",$last_poster="",$posts="") {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
<div class="statsbox_link">
<a title="{$title}" href="{$zone->ips->vars['board_url']}/index.php?showtopic={$tid}" />{$title}</a>
</div>
EOF;

//--endhtml--//
return $HTML;
}
//===========================================================================
// RANDOM DOWNLOADS
//===========================================================================
function random_downloads($file_id="",$file_name="",$category="", $posted_by="") {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
<div class="statsbox_link">
<a href="{$zone->ips->vars['board_url']}/index.php?autocom=downloads&showfile={$file_id}" title="{$file_name}"/>{$file_name}</a>
</div>
EOF;

//--endhtml--//
return $HTML;
}
//===========================================================================
// MEMBERBAR
//===========================================================================
function global_memberbar($prefix="",$member_name="",$suffix="",$flag="",$pms="") {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	if ($zone->is_loggedin()) {
				
		$HTML .= <<<EOF
		<ul class="member-links-active">
		<li class="menubutton"><img src="images/1.png" /></li>
		<li class="menubutton"><a href="#"><strong>{$prefix}{$member_name}{$suffix}</strong></a>
		<ul class="menu_down">
		<li><a href="?action=logout">Log Out</a></li>

EOF;
	if ($zone->is_admin()) {
		$HTML .= <<<EOF
			<li><a href="portal_admin/index.php?section=portal" targe="_blank"><b>Portal ACP</b></a></li>
			<li><a href="{$zone->ips->vars['board_url']}/admin/index.php"><b>Forum ACP</b></a></li>
EOF;
				}
$HTML .= <<<EOF
		<li><a href="{$zone->ips->vars['board_url']}/index.php?act=UserCP&CODE=00">My Controls</a></li>
		<li><a href="{$zone->ips->vars['board_url']}/index.php?act=Search&CODE=getnew">New Posts</a></li>
		</ul>
		</li>
EOF;

		$HTML .= <<<EOF
		<li class="menubutton"><img src="images/2.png" /></li>
	<li class="menubutton"><a href="{$zone->ips->vars['board_url']}/index.php?act=Msg&CODE=01"><b>{$pms}</b> New Messages</a></li>
	</ul>
EOF;

		} else {
		$HTML .= <<<EOF

		<form action="?action=login" method="post" name="LOGIN">
		<input type="text" size="13" name="username" value="Username" onfocus="if (this.value == 'Username') this.value = '';" />
		<input type="password" size="13" name="password" value="Password" onfocus="if (this.value == 'Password') this.value = '';"/>
		<input type="submit" name="login" class="submit" value="" />
		</form>
		
		<div class="member-links">
		<a href="{$zone->ips->vars['board_url']}/index.php?act=Reg&amp;CODE=00">Register</a> - 
		<a href="{$zone->ips->vars['board_url']}/index.php?act=Reg&CODE=10">Lost password</a>
		</div>		
EOF;
}
//--endhtml--//
return $HTML;
}


function show_error($msg="",$url="") {
$HTML = "";
//--starthtml--//
$HTML .= <<< EOF
	<div class="errorwrap">
		<h4>ERROR</h4>
		<p>$msg</p>
		<p align="center"><a href="{$url}">Back</a></p>
</div>
EOF;
//--endhtml--//
return $HTML;
}
//===========================================================================
// FOOTER
//===========================================================================
function global_footer() {
	global $zone;
	
	$date = date("Y");
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
<div id="footer"> <!-- start footer -->
	<div class="left">
	Copyright &copy; <a href="http://gamerwarzone.com"><b>GAMERWARZONE.COM</b></a> $date 
</div>
<div class="right">
Page loaded in <%TIMER%> | 
<a href="{$zone->ips->vars['home_url']}/index.php?section=about">About</a> | 
<a href="{$zone->ips->vars['home_url']}/index.php?section=contact">Contact</a> | 
<a href="javascript:scroll(0,0)">Top</a> | 
<a href="{$zone->ips->vars['board_url']}/index.php?act=rssout&id=1">RSS</a>
</div>
</div><!-- end footer -->
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-567785-2";
urchinTracker();
</script>
</div><!-- end wrapper-->
</body>
</html>
EOF;

//--endhtml--//
return $HTML;
}
//===========================================================================
// 
//===========================================================================
function pagination_current_page($page="") {
$HTML = "";
//--starthtml--//
$HTML .= "&nbsp;<span class=\"pagecurrent\">{$page}</span>";
//--endhtml--//
return $HTML;
}
//===========================================================================
// 
//===========================================================================
function pagination_next_link($catid="",$start="") {
$HTML = "";
//--starthtml--//
$HTML .= <<<EOF
&nbsp;<span class="pagelink"><a href="{$zone->ips->vars['board_url']}/index.php?section=articles&amp;page=category&amp;id=$catid&amp;start=$start" title="Next">&gt;</a></span>
EOF;
//--endhtml--//
return $HTML;
}
//===========================================================================
// 
//===========================================================================
function pagination_page_link($start="",$page="") {
$HTML = "";
//--starthtml--//
$HTML .= <<<EOF
&nbsp;<span class="pagelink"><a href="{$zone->ips->vars['board_url']}/index.php?section=articles&amp;page=category&amp;id=$catid&amp;start=$start" title="$page">$page</a></span>
EOF;
//--endhtml--//
return $HTML;
}
//===========================================================================
// 
//===========================================================================
function pagination_previous_link($catid="",$start="") {
$HTML = "";
//--starthtml--//
$HTML .= <<<EOF
&nbsp;<span class="pagelink"><a href="{$zone->ips->vars['board_url']}/index.php?section=articles&amp;page=category&amp;id=$catid&amp;start=$start" title="Previous">&lt;</a></span>
EOF;
//--endhtml--//
return $HTML;
}

function portal_offline($message="") {
	global $zone;
$HTML = "";
//--starthtml--//
$HTML .= <<<EOF
<br />
<form action="{$zone->ips->vars['board_url']}/index.{$zone->ips->vars['php_ext']}" method="post">
	<input type="hidden" name="act" value="Login" />
	<input type="hidden" name="CODE" value="01" />
	<input type="hidden" name="s" value="{$zone->ips->session_id}" />
	<input type="hidden" name="referer" value="" />
	<input type="hidden" name="CookieDate" value="1" />
	<div class="errorwrap">
		<h4>Zone Offline</h4>
		<p>$message</p>
		</div>
		<div>
<h4>Username</h4>
		<input type="text\" size="20" maxlength="64" name="UserName" />
<h4>Email</h4>
		<input type="text" size="20" maxlength="128" name="UserName" />
			<h4>Password</h4>
			<input type="password" size="20" name="PassWord" />
		</div>
		<p><input class="button" type="submit" name="submit" value="Log In" /></p>
	</div>
</form>
EOF;
//--endhtml--//
return $HTML;
}

function upload_form() {
	global $zone;
	$HTML = "";
	//--starthtml--//
	$HTML .= <<<EOF
<form enctype="multipart/form-data" method="POST" action="{$zone->ips->vars['home_url']}">
  <table align='center' width='100%' cellspacing='1' cellpadding='3'>
    <tr>
      <td align='center' class='formsubtitle' colspan='2'>{$this->ipsclass->vars['image_upload_name']}</td>
    </tr>
    <tr>
      <td align='center' class='row2' colspan='2'>{$this->ipsclass->vars['image_upload_description']}</td>
    </tr>
    <tr>
     <td class='row2' width='35%'><b>{$this->ipsclass->lang['img_name']}</b></td>
      <td class='row2' width='65%'><input type="text" size="50" maxlength="150" name="name" /></td>
    </tr>
    <tr>
      <td class='row2' width='35%'><b>{$this->ipsclass->lang['img_desc']}: </b></td>
      <td class='row2' width='65%'><textarea name="desc" id="desc" rows="10" cols="48"></textarea></td>
    </tr>
    <tr>
      <td class='row2' width='35%' valign='top'><b>{$this->ipsclass->lang['image']}: </b><br>{$this->ipsclass->lang['allowed']} {$this->ipsclass->vars['image_upload_exts']} 
<br />{$this->ipsclass->lang['max']} {$this->ipsclass->vars['image_upload_size']} KB</td>
      <td class='row2' width='65%' ><input name="image_file" type="file" size="50" /></td>
    </tr>
    <tr>
      <td class='row2' align='center' colspan='2'>
<input type="submit"  name="upload" value="Upload Now!" /></td>
    </tr>
</table>
</form>
EOF;

//--endhtml--//
return $HTML;
}
//===========================================================================
// MISC
//===========================================================================
function aboutus() {
	global $zone;
	$HTML = "";
	//--starthtml--//
	$HTML .= <<<EOF
	<p>	
CnCWarZone has really blossomed over the years. We have lost a few of the older members but gained new ones (dedicated ones who will stand by the site through good times and bad).
<br /><br />
This site has many pleasant features that has been modified and updated for our members. Some of the wonderful new things CnCWarZone has offered their members are listed below:
<br /><br />
*A strong, active Tiberian Sun section. A place where you can post your ss's, keep track of the cheaters, and get tips and suggestions on becoming a stronger Tiberian Sun player.
<br /><br />
*An active ogame section. It is nice seeing a new game blossom on CnCWarZone. A game where everyone can play free of charge. A game section where everyone can get help, tips, and a place to express their thoughts.
<br /><br />
*A large arcade section. So if your not an active poster, you can still grab a drink, lean back in your chair, and relax to a few arcade games of your choice.
<br /><br />
*Competitions for our members to join. Currently we are setting up a CnCWarZone Cup Tournament. When this gets going, I am sure it will be a success and other competitons will soon follow.
<br /><br />
*A clan section where you can create your own clan forum and passwords. A place where you are in charge of your clan section and not have to rely on others to create it/change things for you.
<br /><br />
*An active shop where you can buy things with the gold you earn through game competitions/quizzes/and posts. It's really a nice feature when you can change your own display name or member title to fit your personality.
<br /><br />
*The graphic team works with the members to provide contests and fun things for the members to be involved with. This site also provides some helpful tips/suggestions/lessons on helping members create there own signatures as well. From time to time, a member will donate their time and skills to help the other members with signatures/avatars when needed.
<br /><br />
Not only has the categories blossomed on CnCWarZone, but so has the site itself. It offers a variety of skins to choose from, a new header, new smilies, an updated front page, updated materials, and it also gives it's members a chance to let them express a few things about themselves by giving them a chance to provide their country's flag, symbols of games they play, and a way to advertise their clans. An exciting new feature just starting to form is a portal to other sites. This will bring us closer to other sites and in return will provide us with some new opportunities to expand alittle further.
<br /><br />
Even though we were hacked at one time and lost 300 accounts because of this. The members did come back and the site was back up on track in less than a day. The members and the staff didn't give up on the site. If anything, it brought everyone alittle closer together.
<br /><br />
We currently have 12 staff members who are dedicated to CnCWarZone. They volunteer their free time to make CnCWarZone a better site for all it's members.
<br /><br />
I am looking forward to a bright future for the CnCWarZone Forum. New members, new games, working side by side with other sites, and opportunities for new contests and challenges.......just naming a few. I am looking forward on being a part of this site and its members in the future months/years.
</p>
EOF;
//--endhtml--//
return $HTML;
}

function contact() {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	$HTML .= <<<EOF
	<p>Contact Us</p>
EOF;

//--endhtml--//
return $HTML;
}
// END OF SKIN GLOBAL
}
?>