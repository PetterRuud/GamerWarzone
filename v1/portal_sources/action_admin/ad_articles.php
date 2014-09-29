<?php

$ad_articles = new ad_articles;
$zone->acp->content =& $ad_articles;
class ad_articles {
/*
/********************************************************
/
/						ARTICLES
/
/********************************************************
*/
function auto_run() {
	global $zone;
	$ACP->nav[] = array($url, 'Articles' );
	
	switch($_GET['page']) {
		case 'view' :
		$this->view();
		break;
		case 'update':
		$this->update();
		case 'delete':
		$this->delete();
		default :
		$this->view();
		break;
	}
}

//-----------------------------------------------//
//				VIEW ARTICLE
//-----------------------------------------------//
	function view() {
		global $zone;

$zone->DB->query("SELECT c.*,a.* FROM portal_articles a, portal_categories c 
	WHERE a.article_validated = '0' 
	AND a.article_catid = c.cid");
$zone->acp->html .= <<<EOF
	<div class='tableborder'>
	<div class='tableheaderalt'>Waiting For Approval</div>
	<table cellpadding='4' cellspacing='0' width='100%'>
 <tr>
	<td class='tablesubheader'>ID</td>
	<td class='tablesubheader'>Image</td>
	<td class='tablesubheader'>Article Info</td>
	<td class='tablesubheader'>Validated</td>
	</tr>
EOF;
	while ($r = $zone->DB->fetch_row($query)) {
$zone->acp->html .= <<<EOF
<tr>
	<td class='tablerow2'>{$r['aid']}</td>
	<td class='tablerow2'><img src="{$r['article_image']}" width="50px" height="50px" /></td>
	<td class='tablerow2' valign="top"><strong>{$r['article_name']}</strong>
	<br />
	{$r['article_desc']}
	<br />
	Posted By: <strong>{$r['article_author']}</strong>
	<br />
	Category: <strong>{$r['cat_name']}</strong>
	</td>
	<td class='tablerow2'>
	<form action="{$zone->ips->vars['home_url']}/zone_admin/index.php?section=articles&amp;page=update" method="post">
	<input type="hidden" name="aid" value="{$r['aid']}">
	<input type="hidden" name="article_name" value="{$r['article_name']}">
	<input type="hidden" name="cid" value="{$r['cid']}">
	<select name="article_validated">
	<option value="">Validate..</option>
	<option value="1">Yes</option>
	<option value="0">No</option>
	</select>
	<input type="submit" name="submit" value="Validate" class='realbutton'>
	</td>
	<tr>
	<td colspan="4" align='center' class="tablesubheader">
	<a href="#">Edit Article</a>&nbsp;&nbsp;
	<a href="{$zone->ips->vars['home_url']}/zone_admin/index.php?section=articles&amp;page=delete&amp;aid={$r['aid']}&amp;catid={$r['cid']}">Delete Article</a>
	</td>
	</tr>
	</form>	
	</tr>		
EOF;
}
$zone->acp->html .= <<<EOF
	 </table>
	</div>
	<br />
EOF;
$zone->DB->query("SELECT c.*,a.* FROM portal_articles a, portal_categories c 
	WHERE a.article_validated = '1'
	AND a.article_catid = c.cid");
$zone->acp->html .= <<<EOF
<div class='tableborder'>
	<div class='tableheaderalt'>Articles</div>
	<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'>
 <tr>
	<td class='tablesubheader'>ID</td>
	<td class='tablesubheader'>Image</td>
	<td class='tablesubheader'>Article Info</td>
	<td class='tablesubheader'>Validated</td>
	</tr>
EOF;
	while ($r = $zone->DB->fetch_row($query)) {
$zone->acp->html .= <<<EOF
<tr>
	<td class='tablerow2'>{$r['aid']}</td>
	<td class='tablerow2'><img src="{$r['article_image']}" width="50px" height="50px" /></td>
	<td class='tablerow2' valign="top"><strong>{$r['article_name']}</strong>
	<br />
	{$r['article_desc']}
	<br />
	Posted By: <strong>{$r['article_author']}</strong>
	<br />
	Category: <strong>{$r['cat_name']}</strong>
	</td>
	<td class='tablerow2'>
	<form action="{$zone->ips->vars['home_url']}/zone_admin/index.php?section=articles&amp;page=update" method="post">
	<input type="hidden" name="aid" value="{$r['aid']}">
	<input type="hidden" name="article_name" value="{$r['article_name']}">
	<input type="hidden" name="cid" value="{$r['cid']}">
	<select name="article_validated">
	<option value="">Validate..</option>
	<option value="1">Yes</option>
	<option value="0">No</option>
	</select>
	<input type="submit" name="submit" value="Validate" class='realbutton'>
	</td>
	<tr>
	<td colspan="4" align='center' class="tablesubheader">
	<a href="#">Edit Article</a>&nbsp;&nbsp;
	<a href="{$zone->ips->vars['home_url']}/zone_admin/index.php?section=articles&amp;page=delete&amp;aid={$r['aid']}&amp;catid={$r['cid']}">Delete Article</a>
	</td>
	</tr>
	</form>	
	</tr>			
EOF;
}
$zone->acp->html .= <<<EOF
	 </table>
	</div>
	<br />
EOF;
		}
		
function update() {
	global $zone;
		$aid = $zone->ips->input['aid'];
		$article_validated = $zone->ips->input['article_validated'];
		$article_name = $zone->makesafe($zone->ips->input['article_name']);
		$cid = $zone->ips->input['cid'];
		if ($article_validated == '1'){	
			$act = + 1;
		} else { $act = -1;}
		
		$zone->DB->query("UPDATE portal_categories c,portal_articles a SET c.cat_latest_article = '$article_name', c.cat_articles = c.cat_articles $act, a.article_validated = '$article_validated' where c.cid = '$cid' AND a.aid = '$aid'");				
		$zone->boink_it($url="?section=articles",$msg="Articles updated...");
}

function delete() {
	global $zone;
		$aid = $zone->ips->input['aid'];
		$cid = $zone->ips->input['catid'];
		$zone->DB->query("DELETE FROM portal_articles WHERE aid = '$aid'");
		$zone->DB->query("UPDATE portal_categories SET cat_articles = cat_articles -1 WHERE cid = '$cid'");				
		$zone->boink_it($url="?section=articles",$msg="Article Deleted...");
}


}
?>