<?php
class class_news {
	
	var $output = "";
	
function auto_load() {
	global $zone;
	switch ($zone->ips->input['page'])
	{		
		case "id":	
		$this->show_news_full();					
		break;
	}
}

function show_news_full() {
	global $zone;
	$id = $zone->ips->input['id'];
	$post = $zone->get_topic_info($id);

			 	$zone->echo->html .= $zone->skin_news->show_news_full(
				$id = $post['tid'],
				$title = $post['title'],
				$author = $post['starter_name'],
				$posted = date('j F Y',$post['post_date']),
				$comments = $post['posts'],
				$post = $post['post']
				);
$zone->echo->output();
}

} // EOC

?>