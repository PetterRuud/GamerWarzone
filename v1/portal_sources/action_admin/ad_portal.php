<?php

$ad_portal = new ad_portal;
$zone->acp->content =& $ad_portal;

class ad_portal {
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
		case 'view' :
		$this->view_portal();
		break;
		case 'info' :
		$this->view_zone_info();
		break;
		default :
		$this->view_portal();
		break;
	}
}

//-----------------------------------------------//
//				VIEW Portal
//-----------------------------------------------//
function mysql_version() {
	global $zone;
	$zone->DB->query('SELECT VERSION() AS version');
	if (!$row = $zone->DB->fetch_row()) {
		$zone->DB->query("SHOW VARIABLES LIKE 'version'");
		$row = $zone->DB->fetch_row();
	}
	$version =  $row['version'];
	return $version;
}
function php_version() {
	return phpversion();
}
function members() {
	global $zone;
	return intval($zone->ips->cache['stats']['mem_count']);
}
function articles() {
	global $zone;

$zone->DB->query("SELECT COUNT(*) as articles FROM portal_articles ");
$a = $zone->DB->fetch_row( $query );
return $a['articles'];

}
function articles_waiting() {
	global $zone;

$zone->DB->query("SELECT COUNT(*) as articles FROM portal_articles where article_validated = '0'");
$a = $zone->DB->fetch_row( $query );
return $a['articles'];

}
function affiliates() {
	global $zone;

$zone->DB->query("SELECT COUNT(*) as aff FROM portal_affiliate ");
$a = $zone->DB->fetch_row( $query );
return $a['aff'];
}

function affiliates_waiting() {
	global $zone;

$zone->DB->query("SELECT COUNT(*) as aff FROM portal_affiliate WHERE affiliate_validated = '0'");
$aw = $zone->DB->fetch_row( $query );
return $aw['aff'];
}

function online() {
	global $zone;

$my_timestamp = time() - $zone->ips->vars['au_cutoff'] * 60;
$o	 = $zone->DB->simple_exec_query( array( 'select' => 'COUNT(*) as sessions', 'from' => 'sessions', 'where' => 'running_time>' . $my_timestamp ) );

return intval($o['sessions']);

}
	function view_portal() {
		global $zone;	
$zone->acp->html .= <<<EOF
	<table border=0 width=100%>
	<tr>
	<td width=49% valign='top'>
	<div class='tableborder'>
	<div class='tableheaderalt'>Stats</div>
	<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'><tr>
	</tr>
	<tr>
	<td class='tablerow1' valign='middle'><strong>Members</strong></td>
	<td class='tablerow2' valign='middle'><a href="{$zone->ips->vars['board_url']}?section=content&act=mem&code=search" target="_blank">Manage</a> (<strong>{$this->members()}</strong>)</td>
	</tr>
	<tr>
	<td class='tablerow1' valign='middle'>&nbsp;&nbsp;&#124;-<strong>Online Users</strong></td>
	<td class='tablerow2' valign='middle'><a href="{$zone->ips->vars['board_url']}?act=online" target='_blank'>View Online List</a> (<strong>{$this->online()}</strong>)</td>
	</tr>
	<tr>
	<td class='tablerow1' valign='middle'><strong>Affiliates</strong></td>
	<td class='tablerow2' valign='middle'><strong>{$this->affiliates()}</strong></td>
	</tr>
	<tr>
	<td class='tablerow1' valign='middle'>&nbsp;&nbsp;&#124;- <strong>Awaiting Validation</strong></td>
	<td class='tablerow2' valign='middle'><a href="?section=affiliates">View List</a> <strong>({$this->affiliates_waiting()})</strong></td>
	</tr>
	<tr>
	<td class='tablerow1' valign='middle'><strong>Articles</strong></td>
	<td class='tablerow2' valign='middle'><strong>{$this->articles()}</strong></td>
	</tr>
	<tr>
	<td class='tablerow1' valign='middle'>&nbsp;&nbsp;&#124;- <strong>Awaiting Validation</strong></td>
	<td class='tablerow2' valign='middle'><a href="?section=articles">View List</a> <strong>({$this->articles_waiting()})</strong></td>
	</tr>
	</table></div><br />

	</td><td width=2%><!-- -->
	</td><td width=49% valign='top'><div class='tableborder'>
	<div class='tableheaderalt'>Portal Info</div>

	<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'><tr>
	<td class='tablerow1'  width='40%'  valign='middle'><strong>IPB Version</strong></td>
	<td class='tablerow2'  width='60%'  valign='middle'><strong>{$zone->ipb_version()}</strong></td>
	</tr>
	<tr>
	<td class='tablerow1'  width='40%'  valign='middle'><strong>Zone Version</strong></td>
	<td class='tablerow2'  width='60%'  valign='middle'><a href="?section=portal&page=zone">More info</a> (<strong>$zone->zone_version</strong>)</td>
	</tr>
	<tr>
	<td class='tablerow1'  width='40%'  valign='middle'><strong>PHP Version</strong></td>
	<td class='tablerow2'  width='60%'  valign='middle'><strong>{$this->php_version()}</strong></td>
	</tr>
	<tr>
	<td class='tablerow1'  width='40%'  valign='middle'><strong>MySQL Version</strong></td>
	<td class='tablerow2'  width='60%'  valign='middle'><strong>{$this->mysql_version()}</strong></td>
	</tr>
	</table></div><br />
	</td>
	</tr>
EOF;

}

//-----------------------------------
//			zone Info
//----------------------------------

function view_zone_info() {
	global $zone;	
		$zone->acp->nav[] = array($url,'Zone Info');
		$zone->acp->html .= $zone->zone_info();		
	}
}
?>