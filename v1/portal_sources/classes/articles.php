<?php

class class_articles {
	
	var $output = "";
	
function auto_load() {
	global $zone;
	switch ($zone->ips->input['page'])
	{		
		case "categories":	
		$this->categories();					
		break;
	  	case "category": 	
		$this->category();		
		break;
	  	case "add": 	
		$this->add();							
		break;
		case "doadd":
		$this->doadd();
	  	case "edit": 	
		$this->edit();				
		break;
		case 'rate':
		$this->rate();
		break;
		case 'doaddcomment':
		$this->doaddcomment();
		break;
	  	case "article": 	
		$this->article();				
		break;
	}
}
//-----------------------------------------------//
//				VIEW ARTICLE CATEGORIES
//-----------------------------------------------//
function categories() {
	global $zone;

$zone->DB->query("SELECT * FROM portal_categories ORDER BY cat_order");
$zone->echo->html .= $zone->skin_articles->view_article_categories_top();
	while ($r = $zone->DB->fetch_row($query)) {
		if ($r['cat_parentid'] > 0) {
		$sub_category=$r['cat_name'];
		$sub_catid=$r['catid'];
		}
	$zone->echo->html .= $zone->skin_articles->view_article_categories(
	$catid=$r['cid'],
	$category=$r['cat_name'],
	$sub_catid,
	$sub_category,
	$image=$r['cat_image'],
	$description=$r['cat_desc'],
	$latest=$this->displaylatest($r['cat_latest_article']),
	$num_articles = $r['cat_articles']
	);
		}	
$zone->echo->html .= $zone->skin_articles->view_article_categories_bottom();
$zone->echo->output();
}

function displaylatest($latest) {
	if( empty($latest)) {
		$latest = "No Articles";
	}
	return $latest;
}

function sub_cats() {
	global $zone;
	$catid = $zone->ips->input['id'];
	$zone->DB->query("SELECT * FROM portal_categories WHERE cat_parentid='$catid' ORDER BY cat_order");
	if($zone->DB->get_num_rows($query) != 0) {
		$zone->echo->html .= $zone->skin_articles->view_article_categories_top();
		while ($r = $zone->DB->fetch_row($query)) { 
	$zone->echo->html .= $zone->skin_articles->view_article_categories(
	$image=$r['cat_image'],
	$catid=$r['cid'],
	$category=$r['cat_name'],
	$description=$r['cat_desc'],
	$latest=$this->displaylatest($r['cat_latest_article']),
	$num_articles = $r['cat_articles']
	);
}
	$zone->echo->html .= $zone->skin_articles->view_article_categories_bottom();

	}
} 
//-----------------------------------------------//
//				VIEW CATEGORY
//-----------------------------------------------//
function category() {
	global $zone;

$zone->echo->html .= $this->sub_cats();


	$catid = $zone->ips->input['id'];
	$start = $zone->ips->input['start'];
	if ($start == '') {
		$start = 0;
	}
	$per_row = 5;
	$pre = $start - $per_row;
	$next = $start + $per_row;
	if($pre < 0) {
		$pre = 0;
	}
	$page = ceil(($start/$per_row)+1);
	$max = 'LIMIT ' .($start).', ' .($start + $per_row); 

  $query = $zone->DB->query("SELECT article_author, article_image, aid, article_name, article_desc, article_posted, article_rating FROM portal_articles WHERE article_catid = '$catid' AND article_validated = '1' ORDER BY article_posted DESC $max");
$last = $zone->DB->get_num_rows($query);
if ($page > 1) {
$zone->echo->html .= $zone->skin_global->pagination_previous_link($catid=$catid,$start=$pre);
}
$zone->echo->html .= $zone->skin_global->pagination_current_page($page);

if ($last > ($start + $per_row)) {
$zone->echo->html .= $zone->skin_global->pagination_next_link($catid=$catid,$start=$next);
}
$zone->echo->html .= $zone->skin_articles->view_article_category_top();

	while ($r = $zone->DB->fetch_row($query)) {
		if (empty($r)) {
	$return .=  "No Articles";
	}
	
$zone->echo->html .= $zone->skin_articles->view_article_category(
	$image = $r['article_image'],
	$articleid = $r['aid'],
	$title = $r['article_name'],
	$category = $r['cat_name'],
	$authorid = $r['article_authorid'],
	$author = $r['article_author'],
	$description = $r['article_desc'],
	$rating = $this->displayrating($r['article_rating']),
	$posted = $zone->zone_date($r['article_posted'],'d M Y'),
	$comments = $r['article_comments']
	);	
	}
	$zone->echo->html .= $zone->skin_articles->view_article_category_bottom();

$zone->echo->output( );
}

//-----------------------------------------------//
//				SHOW LATEST ARTICLES
//-----------------------------------------------//
function latest() {
	global $zone;
	
	$zone->DB->query("SELECT * FROM portal_articles WHERE article_validated='1' order by article_posted DESC LIMIT 0,5");
	while($r = $zone->DB->fetch_row($query)) {	
	$return .= $zone->skin_articles->latest_articles(
	$image=$r['article_image'],
	$title=$r['article_name'],
	$author=$r['article_author'],
	$posted = $zone->zone_date($r['article_posted'],'d M Y'),
	$comments=$r['article_comments'],
	$description=$r['article_desc'],
	$articleid=$r['aid'],
	$rating = $this->displayrating($r['article_rating'])
	);
	}
	return $return;
}
//-----------------------------------------------//
//				VIEW ARTICLE
//-----------------------------------------------//
function article() {
	global $zone;
	if (empty($id)) {
		$articleid = $zone->ips->input['id'];
		}
$zone->DB->query("UPDATE portal_articles SET article_views = article_views + 1 WHERE aid = '$articleid'");
$zone->DB->query("SELECT c.*,a.* FROM portal_articles a, portal_categories c 
	WHERE article_validated = '1' 
	AND a.aid = '$articleid' AND article_catid = c.cid");
	while ($r = $zone->DB->fetch_row($query)) { 
	$zone->echo->html .= $zone->skin_articles->article(
			$author = $r['article_author'],
			$title = $r['article_name'],
			$authorid = $r['article_authorid'],
			$cat_name = $r['cat_name'],
			$posted = $zone->zone_date($r['article_posted'],'d M Y'),
			$views = $r['article_views'],
			$desc = $r['article_desc'],
			$article = $r['article'],
			$articleid = $articleid,
			$rating = $this->displayrating($r['article_rating'])
			);						
	}
	//if ($zone->article_comments == TRUE) {
	$this->comments();
	$this->addcomment();
	//}
	$zone->echo->output();
}
	
function displayrating($rating)
{
		global $zone; 
		if ($rating != 0)
		{
			$rating = $zone->skin_articles->rating_image($rating);
		}
		else
		{
			$rating = '<em>Not Rated</em>';
		}
		return $rating;
}
function rate()
{
	global $zone;
		if(!isset($zone->ips->input['rating'])){
			$zone->boink_it($url="?section=articles");
		}
		$rating = intval($zone->ips->input['rating']);
		if(!$rating) {
			$zone->boink_it($url="?section=articles");
		}
		$articleid = intval($zone->ips->input['id']);
		if(!$aid){
			$zone->boink_it($url="?section=articles");
		}
		$this->updaterating($articleid, $rating);
		$zone->boink_it($url="?section=articles&amp;page=article&amp;id=$articleid", $msg="Thanks for rating, <br />redirecting back to the article");
}
function updaterating($articleid, $newrating)
	{
		global $zone;
		$zone->DB->query("SELECT rid from portal_rating where rid = '$articleid' ");
		if($zone->DB->get_num_rows())
		{
			$zone->Error('error_already_voted');
		}
		$zone->DB->query("SELECT article_rating,article_numvotes FROM portal_articles WHERE aid = '$articleid' ");
		$r = $zone->DB->fetch_row();
		$new_numvotes = $r['article_numvotes'] + 1;
		$tmp_rating = ($r['article_rating'] * $r['article_numvotes']);
		$new_rating = (($newrating + $tmp_rating) / ($new_numvotes));
		$new_rating = round($new_rating);
		
		$zone->DB->query("UPDATE portal_articles
		SET article_rating = '$new_rating', article_numvotes = article_numvotes+1
		WHERE aid = '$articleid'");

		$member_info = $zone->get_info();
		$member_id = $member_info['id'];
		$member_name = $zone->id2displayname($member_id);
		$zone->DB->query("INSERT INTO portal_rating 
		(rating_member, rating_memberid, rating, rating_aid) VALUES ('$member_name','$member_id','$newrating','$articleid')");
	}
//-----------------------------------------------//
//				POST COMMENT
//-----------------------------------------------//
function addcomment() {
	global $zone;
$zone->echo->html .= $zone->skin_articles->addcomment(); 
}

function doaddcomment() {
	global $zone;
	
$articleid = $zone->ips->input['id'];
if(empty($articleid)) {
$articleid = $zone->ips->input['id'];
}
	echo $articleid;
	if ($zone->ips->input['comment'] == '') {
		return;
	}
	$posted = time();
	$user_info = $zone->get_info();
	$commment_authorid = $user_info['id'];
	$comment = $zone->bbcode2html($zone->makesafe($zone->ips->input['comment']));
	$title = $zone->bbcode2html($zone->makesafe($zone->ips->input['title']));
	$comment_author = $zone->id2displayname($comment_authorid);
	$comment_ip = getenv(REMOTE_ADDR);
	$zone->DB->query("INSERT INTO portal_comments (comment_authorid,comment_author,comment_article,comment_posted,comment,comment_title,comment_ip) 
				VALUES ('$comment_authorid','$comment_author','$articleid','$posted','$comment','$title','$comment_ip')");
	$zone->DB->query("UPDATE portal_articles SET article_comments = article_comments + 1 where aid = '$articleid'");
	$zone->boink_it($url="?section=articles&page=article&id=$articleid",$msg="Comment has been posted...");
}
//-----------------------------------------------//
//				VIEW COMMENT
//-----------------------------------------------//

function comments() {
	global $zone;
	$articleid = $zone->ips->input['id'];
	$zone->DB->query("SELECT * FROM portal_comments WHERE comment_article = '$articleid' ORDER BY comment_posted DESC");
		while ($r = $zone->DB->fetch_row($query)) {	
		$zone->echo->html .= $zone->skin_articles->comment(
			$author = $r['commment_author'],
			$title = $r['comment_title'],
			$posted = $zone->zone_date($r['comment_posted'],'d M Y'),
			$comment = $r['comment']
			);						
	}
}

//-----------------------------------------------//
//				POST ARTICLE
//-----------------------------------------------//
function add() {
	global $zone;
		if ($zone->ips->member['id'] == 0)
		{
			$zone->Error("");
		}
		$zone->DB->query("SELECT cid, cat_name FROM portal_categories");
		$cats = '';
		while($r = $zone->DB->fetch_row())
		{
		$cats .= <<< EOF
		<option value="{$r['cid']}">{$r['cat_name']}</option>
EOF;
		}

$zone->echo->html .= $zone->skin_articles->post_article($cats=$cats);
$zone->echo->output();
}

function doadd() {
global $zone;

		if (!isset($zone->ips->input['category']) OR $zone->ips->input['category'] == '')
		{
			$zone->Error("");
			return;
		}
		$user_info = $zone->get_info();
		$article_authorid = $user_info['id'];
		$article_author = $zone->id2displayname($article_authorid);
		$article_catid = $zone->ips->input['category'];
		$article_posted = time();
		$article_image = $zone->ips->input['article_image'];
		$article_strip_tags = $zone->makesafe($zone->ips->input['article']);
		$article = $zone->bbcode2html($article_strip_tags);
		$strip_tags_article_name = $zone->makesafe($zone->ips->input['article_name']);
		$article_name = $zone->bbcode2html($strip_tags_article_name);
		$desc_strip_tags = $zone->makesafe($zone->ips->input['article_desc']);
		$article_desc = $zone->bbcode2html($desc_strip_tags);
		
		$zone->DB->query("INSERT INTO portal_articles( article_authorid, article_author, article_catid, article_posted, article_image, article_name, article_desc, article,article_validated ) VALUES ( '$article_authorid','$article_author','$article_catid','$article_posted','$article_image','$article_name','$article_desc','$article','0')");
		
$zone->boink_it($url="?section=articles&amp;page=categories",$msg="Article Posted...");
}
//-----------------------------------------------//
//				EDIT ARTICLE
//-----------------------------------------------//
function edit()
	{
	if ($zone->is_admin()) { 
			$zone->Error("");
		}
		$articleid = intval($this->ipsclass->input['id']);
		if (empty($id))
		{
			$zone->Error("");
		}

		$zone->DB->query("SELECT aid,article_name,article_author,article,article_catid
		FROM portyal_articles
		WHERE aid='$articleid' ");

		$article = $zone->DB->fetch_row();

		if($article['article_authorid'])
		{
			if ($article['article_authorid'] != $this->ipsclass->member['id'] AND $this->ipsclass->member['g_is_supmod'] != 1)
			{
				$zone->Error("");
			}
		}
		else
		{

			if ($article['article_author'] != $this->ipsclass->member['members_display_name'] AND $article['article_author'] != $this->ipsclass->member['name'] AND $this->ipsclass->member['g_is_supmod'] != 1)
			{
				$zone->Error("");

			}
		}

		$cats = '';

		foreach($this->ipsclass->cache['article_cats'] as $key => $value)
		{
			$selected = '';
			if($key == intval($article['article_catid']))
			{
				$selected = ' selected="selected" ';
			}

			$extra = '';
			$sub_cats = '';
			foreach($this->ipsclass->cache['article_cats'] as $k => $v)
			{
				if($k == intval($article['article_catid']))
				{
					$selected = ' selected="selected" ';
				}

				if($v['cat_parentid'] != $key OR $v['cat_parentid'] == 0)
				{
					continue;
				}

				$sub_cats .= "<option value='{$k}'{$selected}>{$v['name']}</option>";

			}
			if($value['cat_parentid'] == 0)
			{
				$cats .= "<option value='{$key}'{$selected} style='font-weight:bold'>{$value['name']}</option>";
			}
			$cats .= '<optgroup>'.$sub_cats.'</optgroup>';
		}

			$raw_post = $this->parser->pre_edit_parse( $article['article'] );
			$showeditor = $this->han_editor->show_editor($raw_post, 'Post');
			$this->output .= $this->ipsclass->compiled_templates['skin_articles']->editarticle($showeditor, $article);
		
		$this->page_title = $this->ipsclass->lang['string_editingarticle'];
		$this->nav = array("<a href='{$this->ipsclass->base_url}autocom=articles'>{$this->ipsclass->lang['string_articlesystem']}</a>", $this->ipsclass->lang['string_editingarticle']);
		$this->output = str_replace('<!--CATEGORIES-->', $cats, $this->output);
}

function get_category_name($catid="") {
global $zone;
$catid = $zone->ips->input['id'];
$zone->DB->query("SELECT cat_name FROM portal_categories WHERE cid='$catid' ");
$r = $zone->DB->fetch_row($query);
return $r['cat_name'];
}
function get_article_name() {
global $zone;
$articleid=$zone->ips->input['id'];
$zone->DB->query("SELECT article_name FROM portal_articles WHERE aid='$articleid' ");
$r = $zone->DB->fetch_row($query);
return $r['article_name'];
}




} // EOC
?>