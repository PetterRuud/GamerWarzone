

<?php
class class_news {
/*********************************************************
						NEWS
/*********************************************************/

	function auto_load() {
		global $ipbwi;
		switch ($ipbwi->makesafe($_REQUEST['code'])) {
		case "showall":
			$this->showall();
			break;
		case "show":
			$this->show();
			break;
		default:
			$this->showall();
			break;
		}

	}
	function showall() {
		global $ipbwi;

//$limit = $ipbwi->news_display();
	if($limit == "")
	{
		$limit = 5;
	}
	
	$start = 0;
					
	$page = $ipbwi->makesafe($_REQUEST['page']);
					
					$posts = $ipbwi->topic->getList(56,array('order' => 'DESC', 'orderby' => 'pid', 'start' => $start, 'limit' => $limit),true);
					
					
    				if(isset($posts) && is_array($posts) && count($posts) > 0){
        				foreach($posts as $post){
        					$attachments = $ipbwi->attachment->getList($post['topic_firstpost'],array('type' => 'post'));

        					if ($attachments['boardURL'] != NULL) {
        						$attachments['boardURL'] = <<<EOF
        						<img src="{$attachments['boardURL']}" alt="" class="center newsimg" />
EOF;
        					}
        					
$html .= <<<EOF
    				<!-- article {$post['pid']} start -->
    				
    					<a href="{$ipbwi->getBoardVar('url')}index.php?showtopic={$post['tid']}">{$attachments['boardURL']}</a>
    					
						<h1>{$post['title']}</h1>
						<div class="byline">
							<p>Posted by: <a href="{$ipbwi->getBoardVar('url')}index.php?showuser={$post['author_id']}">{$ipbwi->member->id2displayname($post['author_id'])}</a></p>
						</div>
						<p>{$ipbwi->shorten($post['post'],500)}</p>
						<p><a href="{$ipbwi->getBoardVar('url')}index.php?showtopic={$post['tid']}">Continue reading</a></p><br />
        			

        					
EOF;
        			}
    			}
    			echo $html;
}

function show() {
	global $ipbwi;

	$post = $ipbwi->topic->info($ipbwi->makesafe($_REQUEST['id']));
	$attachments = $ipbwi->attachment->getList($post['pid'],array('type' => 'post'));
	if ($attachments['boardURL'] != NULL) {
        						$attachments['boardURL'] = <<<EOF
        						<img src="{$attachments['boardURL']}" alt="" class="center newsimg" />
EOF;
        					}

	$html .= <<<EOF
    				<!-- article {$post['pid']} start -->
    				<div class="article">
    					{$attachments['boardURL']}
						<div class="byline">
						<h1>{$post['title']}</h1>
							<span class="author">Posted by <strong><a href="{$ipbwi->getBoardVar('url')}index.php?showuser={$post['author_id']}">
        					{$ipbwi->member->id2displayname($post['author_id'])}</a></strong></span>
						</div>
        				{$post['post']}
						<div class="cleared"></div>
						<a href="{$ipbwi->getBoardVar('url')}index.php?showtopic={$post['tid']}"><img src="images/bt_more2.png" alt="more" style="float:right;" /></a>
						<!-- <a href="#comments"><img src="images/bt_comments.png" alt="comments" style="float:right;" /></a> -->	
					</div><!-- end article -->
					<div class="cleared"></div>
EOF;
echo $html;

}


} //eoc
?>