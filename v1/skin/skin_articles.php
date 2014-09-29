<?php
class skin_articles {
	var $zone;
//===========================================================================
// ARTICLE CATEGORIES
//===========================================================================
function view_article_categories_top() {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
	<div align="right">
<a href="?section=articles&amp;page=add"><img src="images/t_newarticle.gif" /></a>
</div>
	<table width="100%" cellspacing="1">
	<tr>
	<td class="subbar" width="1%"></td>
	<td class="subbar" width="50%">Category Name</td>
	<td class="subbar" width="10%">Articles</td>
	<td class="subbar" width="39%">Latest Article</td>
	</tr>
EOF;

//--endhtml--//
return $HTML;
}

//===========================================================================
// ARTICLE CATEGORIES
//===========================================================================
function view_article_categories($catid="",$category="",$sub_catid="",$sub_category="",$image="",$description="",$latest="",$num_articles="") {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
	<tr>
	<td class="row1" width="1%">

	<img src="images/cat_articlenonew.gif" alt="No new" />

	</td>
	<td class="row2" width="50%" valign="top">
	<a href="?section=articles&amp;page=category&amp;id={$catid}"><strong>{$category}</strong></a><br />
	<span class="desc">{$description}</span>
	<!--<br />
<span class="desc">Subcategories: <a href="?section=articles&amp;page=category&amp;id={$sub_catid}">$sub_category</a></span> -->
</td>
	<td class="row1" width="10%" align="center"><span class="desc"><i>{$num_articles}</i></span></td>
	<td class="row2" width="39%"><span class="desc">Latest: <strong>{$latest}</strong></span></td>
	</tr>
EOF;

//--endhtml--//
return $HTML;
}

//===========================================================================
// ARTICLE CATEGORIES
//===========================================================================
function view_article_categories_bottom() {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
</table>
	<div align="right">
<a href="?section=articles&amp;page=add"><img src="images/t_newarticle.gif" /></a>
</div>
EOF;

//--endhtml--//
return $HTML;
}
//===========================================================================
// ARTICLE CATEGORY
//===========================================================================

function view_article_category_top() {
	global $zone;
				
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
	<div class="contentbox">
EOF;
	
	//--endhtml--//
return $HTML;
}
function view_article_category($image="",$articleid="",$title="",$category="",$authorid="",$author="",$description="",$rating="",$posted="",$comments="") {
	global $zone;
				
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
<!-- Article cat #{$id} -->
<div class="topic_image">
<img src="{$image}" alt="" height="100px" width="100px" /><br />
</div>
<div class="content_title">
<a href="?section=articles&page=article&id={$articleid}">{$title}</a>
</div>
<div class="content_info">
<a href="{$zone->ips->vars['board_url']}/index.php?showuser={$authorid}">{$author}</a> {$posted} - <em>{$rating}</em>
</div>
<div class="content_post">
{$description}<br />
<a href="?section=articles&page=article&id={$articleid}"><b>Read More...</b></a>
</div>
<div class="clear">&nbsp;</div>
<div class="dotted"></div>
<div class="clear">&nbsp;</div>

EOF;

//--endhtml--//
return $HTML;
}

function view_article_category_bottom() {
	global $zone;
				
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
	</div>
EOF;
	
	//--endhtml--//
return $HTML;
}
//===========================================================================
// Rating Image
//===========================================================================
function rating_image($rating=0) {
	global $zone;
$HTML = "";
//--starthtml--//

$HTML .= "" . (($rating > 0) ? ("
	<img src='{$zone->ips->vars['img_url']}/folder_topic_view/rating_{$rating}.gif' id='topic-rating-img-main' border='0' alt='{$rating_id}' />
") : ("
	<img src='{$zone->ips->vars['img_url']}/blank.gif' id='topic-rating-img-main' border='0' alt='' />
")) . "";
//--endhtml--//
return $HTML;
}

//===========================================================================
// LATEST ARTICLES
//===========================================================================
function latest_articles($image="",$title="",$author="",$posted="",$comments="",$description="",$articleid="", $rating="") {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
	<!-- Article #{$articleid} -->
	<div class="topic_image">
EOF;
if ($image != "") {
$HTML .= <<<EOF
<img src="{$image}" alt="" height="100px" width="100px" />
EOF;
}
$HTML .= <<<EOF
	</div>
<div class="content_title">
<a href="{$zone->ips->vars['home_url']}/?section=articles&amp;page=article&amp;id={$articleid}" title="{$title}">{$title}</a>
</div>
<div class="content_info">
$author $posted Comments (<strong>$comments</strong>) - <em>{$rating}</em>
</div>
<div class="content_post">
{$description}<br />
<a href="?section=articles&page=article&amp;id=$articleid" title="{$title}"><strong>Read More...</strong></a>
</div>
<div class="clear">&nbsp;</div>
<div class="dotted"></div>
<div class="clear">&nbsp;</div>
EOF;

//--endhtml--//
return $HTML;
}
//===========================================================================
// ARTICLE
//===========================================================================
function article($auhtor="",$title="",$authorid="",$cat_name="",$posted="",$views="",$desc="",$article="",$articleid="",$rating="") {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF

<!-- Article #{$aid} -->
<div class="topic_image">
EOF;
if ($image != "") {
$HTML .= <<<EOF
<img src="{$image}" alt="" height="100px" width="100px" />
EOF;
}
$HTML .= <<<EOF
</div>
<div class="contentbox">
<div class="content_title">
{$title}
</div>
<div class="content_info">
<a href="{$zone->ips->vars['board_url']}/index.php?showuser={$authorid}">{$auhtor}</a> {$posted} Category: <em>{$cname}</em><br />
Views: <em>{$views}</em> - {$rating}
<ul id="rate">
<li class="menubutton"><a href="#">Rate</a>
<ul class="menu_down">
<li>
<form action="{$zone->ips->vars['home_url']}/?section=articles&amp;page=rate&amp;id={$articleid}" method="post">
<input type="radio" name="rating" id="r5" value="5" />
<label for="r5">
<img src="{$zone->ips->vars['img_url']}/rating_5.gif" alt="*****" />
</label><br />
<input type="radio" name="rating" id="r4" value="4">
<label for="r4">
<img src="{$zone->ips->vars['img_url']}/rating_4.gif" alt="****" />
</label><br />
<input type="radio" name="rating" id="r3" value="3">
<label for="r3">
<img src="{$zone->ips->vars['img_url']}/rating_3.gif" alt="***" />
</label><br />
<input type="radio" name="rating" id="r2" value="2">
<label for="r2">
<img src="{$zone->ips->vars['img_url']}/rating_2.gif" alt="**" />
</label><br />
<input type="radio" name="rating" id="r1" value="1">
<label for="r1">
<img src="{$zone->ips->vars['img_url']}/rating_1.gif" alt="*" />
</label><br />
<div align="center"><input type="submit" value="Rate Article" /></div>
</form>
</ul>
</li>
</li>
</ul><!-- end rate -->
</div>
<div style="margin: 5px;">
$article<br />
</div>
</div>
EOF;
//--endhtml--//
return $HTML;
}


//===========================================================================
// ARTICLE
//===========================================================================
function post_article($cats="") {
	global $zone;
	$textarea='article';
	$form_name='article';
	
	$HTML = "";
	//--starthtml--//
	if($zone->is_loggedin()) {

	$HTML .= <<<EOF
<div class="bighead">Add Article</div>
<table width="100%">
<form action="{$zone->ips->vars['home_url']}?section=articles&page=doadd" name="article" method="post">
<tr>
<td><strong>Category:</strong></td>
<td><select name="category">
<option value="">Select Category</option>
{$cats}
</select></td>
</tr><tr>
<td><strong>Article Title:</strong></td>
<td><input type="text" name="article_name" size="35" /></td>
</tr><tr>
<td><strong>Description:</strong></td>
<td><input type="text" name="article_desc" size="35" /></td>
</tr><tr>
<td><strong>Thumbnail Image:</strong></td>
<td><input type="text" name="article_image" size="35" /></td>
</tr>
<tr>
<td colspan="2"><input type='button' class="button" value='Bold' onclick="ubbc(document.{$form_name}.{$textarea},'[b]','[/b]')" />
<input type='button' class="button" value='Italic' onclick="ubbc(document.{$form_name}.{$textarea},'[i]','[/i]')" />
<input type='button' class="button" value='Make List' onclick="ubbc(document.{$form_name}.{$textarea},'[ul]', '[/ul]')" />
<input type='button' class="button" value='Quote' onclick="ubbc(document.{$form_name}.{$textarea},'[quote]','[/quote]')" />
<input type='button' class="button" value='Add Link' onclick="ubbc(document.{$form_name}.{$textarea},'link','[/a]')" />
<input type='button' class="button" value='Size' 
onclick="ubbc(document.{$form_name}.{$textarea},'size' ,4'')" />
<input type='button' class="button" value='IMG' onclick="ubbc(document.{$form_name}.{$textarea},'img','[/img]')" />
</td>
</tr>
<tr>
<td colspan="2"><textarea style='padding:4px;width:98%;height:300px' name="{$textarea}"></textarea>
</td>
</tr>
<tr>
<td colspan="2" align="center">
<input class="button" type="submit" name="submit" value="Submit Article!"/>
</tr>
</form>
</table>
EOF;
}
else {
$HTML .= <<<EOF
Login
EOF;
}

//--endhtml--//
return $HTML;
}

//===========================================================================
// Comment
//===========================================================================

function comment($author="",$title="",$posted="",$comment="") {
	global $zone;

	$HTML = "";
	//--starthtml--//

	$HTML .= <<<EOF
<!-- Comment #{$commentid} -->
<div class="commentbox">
<div class="datebox">
<div class="author">{$author}</div>
{$posted}
</div>
<div style="border-bottom: 1px solid #CCCCCC; font-weight: bold; font-size: 16px; padding-bottom: 5px;">
{$title}
</div>
<div style="margin: 5px;">
$comment
</div>
</div>
EOF;

	//--endhtml--//
	return $HTML;
}

function addcomment() {
	global $zone;
	$textarea='comment';
	$form_name='comment';
	$HTML = "";
	//--starthtml--//
	if($zone->is_loggedin()) { 

	$HTML .= <<<EOF
<div class="contentbox">
<div class="content_header">Add Comment</div>
<form method="post" action="{$zone->ips->vars['home_url']}/?section=articles&amp;page=doaddcomment" name="$form_name">
<input type="hidden" name="id" value="{$zone->ips->input['id']}" />
<div class="box">
<b>Title</b><br /><input type="text" name="title" />
<table>
<tr>
<td colspan="2"><input type='button' class="button" value='Bold' onclick="ubbc(document.{$form_name}.{$textarea},'[b]','[/b]')" />
<input type='button' class="button" value='Italic' onclick="ubbc(document.{$form_name}.{$textarea},'[i]','[/i]')" />
<input type='button' class="button" value='Make List' onclick="ubbc(document.{$form_name}.{$textarea},'[ul]', '[/ul]')" />
<input type='button' class="button" value='Quote' onclick="ubbc(document.{$form_name}.{$textarea},'[quote]','[/quote]')" />
<input type='button' class="button" value='Add Link' onclick="ubbc(document.{$form_name}.{$textarea},'link','[/a]')" />
<input type='button' class="button" value='Size' 
onclick="ubbc(document.{$form_name}.{$textarea},'size' ,4'')" />
<input type='button' class="button" value='IMG' onclick="ubbc(document.{$form_name}.{$textarea},'img','[/img]')" />
</td>
</tr>
<tr>
<td colspan="2"><textarea style='padding:4px;width:98%;height:100px' name="{$textarea}"></textarea>
</table>
</td>
</tr>
</div>
<div class="content_center"><input type="submit" value="&nbsp;&nbsp;Add Comment!&nbsp;&nbsp;" /></div>
</form>
</div>
<div class="content_button_back"><a href="?section=articles&page=categories">Back...</a></div>
EOF;
} 		
else { 
			$HTML .= <<<EOF
			<p>Login to add a comment.</p>
EOF;
		}

	//--endhtml--//
	return $HTML;
}

}

?>