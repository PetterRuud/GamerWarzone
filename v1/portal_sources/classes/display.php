<?php

//================================================================
//						GENERAL
//================================================================

class class_display {

	var $html;
	var $output   = "";
	var $site_forum_id = 56; // Site News forum id
	var $cc_forum_id = 319; // Command & Conquer News forum id
	var $gg_forum_id = 320; // Site News forum id
    
//-----------------------------------------
// CONSTRUCTOR
//-----------------------------------------

function class_display()
{
}


//===========================================================================
// ECHO ECHO ECHO 
//===========================================================================
function output () {
	global $zone,$timer;
	
		//-----------------------------------------
		// Start function proper
		//-----------------------------------------
		$html = str_replace( '<%CONTENT%>', $zone->skin_global->global_frame_wrapper(), $zone->skin_global->global_main_wrapper() );
			//-----------------------------------------
			// Other tags...
			//-----------------------------------------
			$html = str_replace( "<%SECTIONCONTENT%>", $this->html, $html );
			$html = str_replace( "<%HEADER%>", $zone->skin_global->global_header($this->css(),$this->logoshifter(),$this->h1()), $html );
			$html = str_replace( "<%FOOTER%>", $zone->skin_global->global_footer(), $html );
			$html  = str_replace( "<%RIGHT%>", $zone->skin_global->global_right(), $html );
			$html  = str_replace( "<%PAGETITLE%>", $this->pagetitle(), $html );
			$html  = str_replace( "<%NAV%>", $this->nav(), $html );
			
			//-----------------------------------------
			// Special tags...
			//-----------------------------------------
			$submenu 	= $this->build_submenu();
			$html 		= str_replace( "<%SUBMENU%>", $submenu  , $html );
			$stats  	= $this->do_stats() ;	
			$html  		= str_replace( "<%STATS%>", $stats, $html );
			$online  	= $this->do_online() ;
			$html  		= str_replace( "<%ONLINE%>", $online, $html );
			$all_news  = $this->all_news() ;
			$html  		= str_replace( "<%NEWS%>", $all_news, $html );
			$cc_news  	= $this->cc_news() ;
			$site_news  = $this->site_news() ;
			$html  		= str_replace( "<%SITENEWS%>", $site_news, $html );
			$cc_news  	= $this->cc_news() ;
			$html  		= str_replace( "<%CCNEWS%>", $cc_news, $html );
			$gg_news  	= $this->gg_news() ;
			$html  		= str_replace( "<%GGNEWS%>", $gg_news, $html );
			$news  		= $zone->articles->latest() ;
			$html  		= str_replace( "<%ARTICLES%>", $news, $html );
			$memberbar  = $this->memberbar() ;
			$html  		= str_replace( "<%MEMBERBAR%>", $memberbar, $html );
			$latest_topics  = $this->do_latest_topics() ;
			$html  		= str_replace( "<%LATESTTOPICS%>", $latest_topics, $html );
			$latest_downloads  = $this->do_latest_downloads() ;
			$html  		= str_replace( "<%DOWNLOADS%>", $latest_downloads, $html );
			$html 		= str_replace( "<%CHARSET%>" , $zone->ips->vars['gb_char_set'], $html );
			$affiliates = $zone->affiliates->shownum();
			$html  		= str_replace( "<%AFFILIATES%>", $affiliates, $html );
			$html 		= str_replace( "<%TIMER%>", $timer->endTimer(), $html);
			
	$this->__finish();
	print $html;
	exit();
}

/*------------------------------------------------------------------------*/
// finish
/*-------------------------------------------------------------------------*/

function __finish()
{
	//-----------------------------------------
	// Do shutdown
	//-----------------------------------------
	
	if ( ! USE_SHUTDOWN )
    {
    	$zone->ips->my_deconstructor();
    	$zone->DB->close_db();
    }
    
	//-----------------------------------------
	// Start GZIP compression
    //-----------------------------------------
    
    if ($zone->ips->vars['disable_gzip'] != 1 )
    {
        $buffer = "";
        if ( count( ob_list_handlers() ) )
        {
    		$buffer = ob_get_contents();
    		ob_end_clean();
		}
    	@ob_start('ob_gzhandler');
    	print $buffer;
    }
    
    //-----------------------------------------
    // Print, plop and part
    //-----------------------------------------
    
    $this->do_headers();
}
function do_headers()
{
	if ( $zone->ips->vars['print_headers'] )
	{
		$zone->ips->vars['gb_char_set'] = $zone->ips->vars['gb_char_set'] ? $zone->ips->vars['gb_char_set'] : 'iso-8859-1';
		
		header("HTTP/1.0 200 OK");
		header("HTTP/1.1 200 OK");
		header( "Content-type: text/html;charset={$zone->ips->vars['gb_char_set']}" );
		
		if ( $zone->ips->vars['nocache'] )
		{
			header("Cache-Control: no-cache, must-revalidate, max-age=0");
			//header("Expires:" . gmdate("D, d M Y H:i:s") . " GMT");
			header("Expires: 0");
			header("Pragma: no-cache");
		}
    }
}

function get_attachment_location($pid) {
	global $zone;
	$attach_rel_id = $pid;
	$attachments = $zone->get_post_attachments($postid=$attach_rel_id,"");
	if ($attachments != "") {
	foreach($attachments as $attach) {
	$location = $attach['attach_location'];
	}
}
	return $location;
}
//===========================================================================
// NEWS 
//===========================================================================
function all_news() {
	global $zone;
		$topics = $zone->list_forum_topics(array($this->site_forum_id,$this->cc_forum_id,$this->gg_forum_id),array('order' => 'desc', 'limit' => '5', 'start' => '0', 'orderby' => 'last_post', 'linked' => false, 'ignoreapproval' => '0'), $bypassperms = '0');
		foreach($topics as $topic) {
			$f = $zone->get_customfield_value(1, $memberid = $topic['author_id']);
			$ft = trim($f);
						
			$return .= $zone->skin_news->news(
				$id=$topic['tid'],
				$topic_image = $this->get_attachment_location($topic['topic_firstpost']),
				$title=$topic['title'],
				$author=$zone->id2displayname($topic['author_id']),
				$flag="<img src=\"".$zone->ips->vars['board_url']."/images/flags/".$ft.".gif\">",
				$date=date('j F Y',$topic['start_date']),
				$post=$this->shorten($topic['post'],150),
				$last_reply = $topic['last_poster_name'],
				$comments = $topic['posts']
			);
		}
		return $return;
}
function site_news() {
	global $zone;
		$topics = $zone->list_forum_topics($this->site_forum_id,$settings = array('order' => 'desc', 'limit' => '5', 'start' => '0', 'orderby' => 'last_post', 'linked' => false, 'ignoreapproval' => '0'), $bypassperms = '0');
		foreach($topics as $topic) {
			$f = $zone->get_customfield_value(1, $memberid = $topic['author_id']);
			$ft = trim($f);
						
			$return .= $zone->skin_news->news(
				$id=$topic['tid'],
				$topic_image = $this->get_attachment_location($topic['topic_firstpost']),
				$title=$topic['title'],
				$author=$zone->id2displayname($topic['author_id']),
				$flag="<img src=\"".$zone->ips->vars['board_url']."/images/flags/".$ft.".gif\">",
				$date=date('j F Y',$topic['start_date']),
				$post=$this->shorten($topic['post'],150),
				$last_reply = $topic['last_poster_name'],
				$comments = $topic['posts']
			);
		}
		return $return;
}

//===========================================================================
// COMMAND & CONQUER NEWS 
//===========================================================================
function cc_news() {
	global $zone;
		$topics = $zone->list_forum_topics($this->cc_forum_id,$settings = array('order' => 'desc', 'limit' => '5', 'start' => '0', 'orderby' => 'last_post', 'linked' => false, 'ignoreapproval' => '0'), $bypassperms = '0');
		foreach($topics as $topic) {
			$f = $zone->get_customfield_value(1, $memberid = $topic['author_id']);
			$ft = trim($f);
						
			$return .= $zone->skin_news->news(
				$id=$topic['tid'],
				$attachment = $this->get_attachment_location($topic['pid']),
				$title=$topic['title'],
				$author=$zone->id2displayname($topic['author_id']),
				$flag="<img src=\"".$zone->ips->vars['board_url']."/images/flags/".$ft.".gif\">",
				$date=date('j F Y',$topic['start_date']),
				$post=$this->shorten($topic['post'],150),
				$last_reply = $topic['last_poster_name'],
				$comments = $topic['posts']
			);
		}
		return $return;
}

//===========================================================================
// GENERAL GAMING NEWS 
//===========================================================================
function gg_news() {
	global $zone;
		$topics = $zone->list_forum_topics($this->gg_forum_id,$settings = array('order' => 'desc', 'limit' => '5', 'start' => '0', 'orderby' => 'last_post', 'linked' => false, 'ignoreapproval' => '0'), $bypassperms = '0');
		foreach($topics as $topic) {
			$f = $zone->get_customfield_value(1, $memberid = $topic['author_id']);
			$ft = trim($f);
						
			$return .= $zone->skin_news->news(
				$id=$topic['tid'],
				$attachment = $this->get_attachment_location($topic['pid']),
				$title=$topic['title'],
				$author=$zone->id2displayname($topic['author_id']),
				$flag="<img src=\"".$zone->ips->vars['board_url']."/images/flags/".$ft.".gif\">",
				$date=date('d M Y',$topic['start_date']),
				$post=$this->shorten($topic['post'],150),
				$last_reply = $topic['last_poster_name'],
				$comments = $topic['posts']
			);
		}
		return $return;
}


function shorten($text,$length) {
	$text = strip_tags($text);
$replacer = "...";
  if(strlen($text) > $length) {
  $text = preg_match('/^(.*)\W.*$/', substr($text, 0, $length+1), $matches) ? $matches[1] : substr($text, 0, $length) . $replacer;
 }

//$text = preg_replace("/\<img.+?src=\"(.+?)\".+?\/>/","",$text);
//$text = preg_replace("/--/","-->",$text);

  return $text;

}
//===========================================================================
// STATS 
//===========================================================================
function do_stats() {
	global $zone;
	$stats = $zone->get_board_stats();
	return $zone->skin_global->stats($posts=($stats['total_replies']+$stats['total_topics']),$members=$stats['mem_count'],$last=$stats['last_mem_name']);
}

//===========================================================================
// ONLINE 
//===========================================================================
function do_online_total() {
	global $zone;
	$on = array();
	$on[] = $zone->get_active_count();
	foreach ($on as $online ) {
	$online_total = $online['total'];
	}
	return $online_total;
}
function do_online() {
	global $zone;
	$on = array();
	$on[] = $zone->get_active_count();
	foreach ($on as $online ) {
	}
	return $zone->skin_global->online($total = $this->do_online_total(), $members = $online['members'], $guests = $online['guests']);
}

//===========================================================================
// WHO AM I...? 
//===========================================================================
function memberbar() {
	global $zone;
	if($zone->ips->input['action'] == 'login') {
     $zone->login($zone->ips->input['username'],$zone->ips->input['password'],$zone->ips->input['setcookie'],$zone->ips->input['anonlogin']);
		header('location: '.$_SERVER['PHP_SELF']);
    }
		$member = $zone->get_advinfo();
		$group = $zone->get_groupinfo();
		$pslv = $zone->get_num_new_posts();
		$f = $zone->get_customfield_value(1, $memberid = $topic['author_id']);
		$ft = trim($f);
		$flag = "<img src=\"".$zone->ips->vars['board_url']."/images/flags/".$ft.".gif\">";
		$info = $zone->get_info();
		$pms = $zone->get_num_new_pms();
		
		if ($zone->ips->input['action'] == "logout") {
		$zone->logout();
		header('location: '.$_SERVER['PHP_SELF']);
		//$zone->boink_it($_SERVER['PHP_SELF']);
		}	
		return $zone->skin_global->global_memberbar($prefix=$member['prefix'],$memer_name=$info['members_display_name'],$suffix=$member['suffix'],$flag=$flag,$pms=$pms);
}

//===========================================================================
// LATEST TOPICS 
//===========================================================================
function do_latest_topics() {
	global $zone;
	
	$topics = $zone->list_forum_topics ("*", array("limit" => "3", "start" => "0", "order" => "desc", "orderby" => "post_date"), "0");
	$f = $zone->get_customfield_value(1, $memberid = $topic['author_id']);
	$ft = trim($f);
	$_flag="<img src=\"".$zone->ips->vars['board_url']."/images/flags/".$ft.".gif\">";
	$flag = $_flag;
	foreach ($topics as $topic) {
	
	$return .= $zone->skin_global->latest_topics(
	$tid = $topic['tid'],
	$title = $this->shorten($topic['title'],25),
	$last_poster = $topic['last_poster_name'],
	$posts = $topic['posts']
		);	
	}
	return $return;
}
//===========================================================================
// LATEST Files
//===========================================================================
function do_latest_downloads() {
	global $zone;
	$zone->DB->allow_sub_select=1;
$zone->DB->query("SELECT f.*, c.cname 
	FROM ibf_downloads_files f
	LEFT JOIN ibf_downloads_categories c
	ON c.cid=f.file_cat
	ORDER BY f.file_submitted DESC
	LIMIT 0, 3
	");

while ($r = $zone->DB->fetch_row($query) ) {
	$return .= $zone->skin_global->random_downloads(
	$file_id = $r['file_id'],
	$file_name = substr($r['file_name'],0,40),
	$category = $r['cname']	
	);	
	}
	return $return;
}
function build_submenu() {
	global $zone;
	$section = $zone->ips->input['section'];
	if ($section  == '' OR $section == 'news') {
		$section = 'home';
	}
	$onoff['home']     = 'off';
	$onoff['articles'] = 'off';
	$onoff['forum']     = 'off';
	$onoff['affiliates']     = 'off';
	$onoff['dl'] = 'off';
	$onoff['clans']     = 'off';
	$onoff['ps']     = 'off';
	$onoff['replays']     = 'off';
	$onoff['xwis']     = 'off';
	$onoff[ $section ] = 'on';
	return $zone->skin_global->global_submenu( $onoff );
}

//===========================================================================
// WHO IS ONLINE, PEEKY PEEKY 
//===========================================================================
function whois_online() {
	global $zone;
$online = $zone->list_online_members(0,1);
return $online;
}

//===========================================================================
// NAV 
//===========================================================================
function nav() {
	global $zone;
	$section = $zone->ips->input['section'];
	$page = $zone->ips->input['page'];
	$pagetitle = "";
	// will add some replaces
	$section 	= str_replace("articles","Articles",$section);
	$page 		= str_replace("categories","Categories",$page);
	$page 		= str_replace("id",$zone->articles->get_category_name($catid = $zone->ips->input['catid']),$page);
	$page 		= str_replace("id",$zone->articles->get_article_name($aid = $zone->ips->input['aid']),$page);
	
$nav .=  <<<EOF
<a href="{$zone->ips->vars['home_url']}"><strong>Home</strong></a>
EOF;

if ($section != "") {
	$nav .= <<<EOF
	-> $section
EOF;
}
if ($page != "") {
	$nav .= <<<EOF
	-> {$page}
EOF;
}
return $nav;
}
//===========================================================================
// PAGETITLE 
//===========================================================================
function pagetitle() {
	global $zone;
	$section = $zone->ips->input['section'];
	$page = $zone->ips->input['page'];
	$portal = $zone->ips->input['portal'];
	$pagetitle = "";
	// will add some replaces
	$portal = str_replace("tiberiansun","Tiberian Sun",$portal);
	$portal = str_replace("redalert2","Red Alert 2",$portal);
	$portal = str_replace("tiberiumwars","Tiberium Wars",$portal);
	$portal = str_replace("kaneswrath","Kane's Wrath",$portal);
	$portal = str_replace("redalert3","Red Alert 3",$portal);
	$section = str_replace("articles","Articles",$section);
	$section = str_replace("affiliates","Affiliates",$section);
	$section = str_replace("ips","Player Search",$section);
	$page = str_replace("categories","Categories",$page);
	$page = str_replace("category",$zone->articles->get_category_name(),$page);
	$page = str_replace("article",$zone->articles->get_article_name(),$page);
	$page = str_replace("view","Affiliates",$page);
	$page = str_replace("add","Add Player",$page);
	$page = str_replace("search","Search",$page);
	
$pagetitle .= PORTAL_NAME;

if ($portal != "") {
	$pagetitle .= <<<EOF
	-> {$portal}
EOF;
}

if ($section != "") {
	$pagetitle .= <<<EOF
	-> {$section}
EOF;
}
if ($page != "") {
	$pagetitle .= <<<EOF
	-> {$page}
EOF;
}
return $pagetitle;
}
//===========================================================================
// CSS 
//===========================================================================
function css() {
	global $zone;
	
	//$skinid = $zone->get_skin_id();
	//$css = <<<EOF
	//@import url({$zone->ips->vars['board_url']}/style_images/css_$skinid.css);
//EOF;
$css = 'style.css';
	return $css;
}

//===========================================================================
// logoshifter 
//===========================================================================
function logoshifter() {
	global $zone;
	$portal = $zone->ips->input['portal'];
	return $portal;
}

//===========================================================================
// h1 
//===========================================================================
function h1() {
	global $zone;
	switch($zone->ips->input['portal']) {
		case 'tiberiansun':
		$h1 = '<em>Command &amp; Conquer<br /></em>Tiberian Sun';
		break;
		case 'redalert2':
		$h1 = '<em>Command &amp; Conquer<br /></em>Red Alert 2';
		break;
		case 'tiberiumwars':
		$h1 = '<em>Command &amp; Conquer<br /></em>Tiberium Wars';
		break;
		case 'kaneswrath':
		$h1 = '<em>Command &amp; Conquer<br /></em>Kane\'s Wrath';
		break;
		case 'redalert3':
		$h1 = '<em>Command &amp; Conquer<br /></em>Red Alert 3';
		break;
	}
	return $h1;
}

/*===========================	END OF CLASS	===========================*/
} 
?>