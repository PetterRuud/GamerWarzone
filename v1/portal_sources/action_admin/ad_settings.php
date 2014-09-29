<?php

$ad_settings = new ad_settings;
$zone->acp->content =& $ad_settings;

class ad_settings {
/*
/********************************************************
/
/						Portal
/
/********************************************************
*/
function auto_run() {
	global $zone;
	
	switch($_GET['page']) {
		case 'set':
		$this->settings();
		break;
		case 'update_settings':
		$this->update_settings();
		break;
		case 'offline':
		$this->turn_portal_offline();
		break;
		case 'do_offline':
		$this->turn_offline();
		break;
		default:
		$this->settings();
		break;
	}
}

function settings() {
	global $zone;
	$zone->DB->query("SELECT * FROM portal_settings");
	$r = $zone->DB->fetch_row($query);
$zone->acp->html .= <<<EOF
<form action='{$zone->ips->vars['home_url']}/zone_admin/index.php?section=settings&amp;page=update_settings' method='post' name='theAdminForm'>
<div class='tableborder'>
<div class='tableheaderalt'>
<table cellpadding='0' cellspacing='0' border='0' width='100%'>
<tr>
<tr>
<td style='font-size:12px; vertical-align:middle;font-weight:bold; color:#FFF;'>General Settings</td>
</tr>
</table>
</div>
<div style='background-color:#EEF2F7;padding:5px'>
<div class='tableborder'>
<div class='tablesubheader'>Names &amp; Addresses</div>
<table cellpadding='5' cellspacing='0' border='0' width='100%'>
<tr>
<td width='30%' class='tablerow1'><b>Portal Name</b><div style='color:gray'>This is the name of the portal. It is used as the first link in the navigation menu, etc.</div></td>
<td width='55%' class='tablerow2'>
<div align='left' style='width:auto;'><input type='text' name='portal_name' value="{$r['portal_name']}" size='30' class='textinput'></div></td>
</tr>
</table>
<table cellpadding='5' cellspacing='0' border='0' width='100%'>
<tr>
<td width='30%' class='tablerow1'><b>Portal Address</b>&nbsp;&nbsp;<div style='color:gray'>This is the URL to your website. If entered, it'll appear on the board above the header by default.</div></td>
<td width='55%' class='tablerow2'>
<div align='left' style='width:auto;'>
<input type='text' name='portal_url' value="{$r['portal_url']}" size='30' class='textinput'>
</div></td>
</tr>
</table>
</div></div>
<div class='tablesubheader' align='center'>
<input type='submit' value='Update Settings' class='realdarkbutton' /></div></div></form>
EOF;
}

function update_settings() {
	global $zone;
	
	$portal_url = $zone->ips->input['portal_url'];
	$portal_name = $zone->ips->input['portal_name'];
	$zone->DB->query("UPDATE portal_settings SET portal_name='$portal_name', portal_url='$portal_url' ");
	$zone->boink_it($url="?section=settings",$msg="Thanks");
}

function turn_portal_offline() {
	global $zone;

$zone->DB->query("SELECT portal_offline, offline_msg FROM portal_settings");
$r = $zone->DB->fetch_row($query);
$zone->acp->html .= <<<EOF
<form action='{$zone->ips->vars['home_url']}/zone_admin/index.php?section=settings&amp;page=do_offline' method='post' name='theAdminForm'>
<div class='tableborder'>
<div class='tableheaderalt'>
<table cellpadding='0' cellspacing='0' border='0' width='100%'>