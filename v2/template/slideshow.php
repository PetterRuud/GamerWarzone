	<div id="banner">
	<div class="anythingSlider">
	    <div class="wrapper">
    		<ul>
    		<?php
 	    	$recent_posts = $ipbwi->topic->getList('374',array('limit' => 4), $bypassPerms = true);
 	    	foreach($recent_posts as $post){
     			if(is_array($recent_posts) && count($recent_posts)>0){
     			$attachments = $ipbwi->attachment->getList($post['topic_firstpost'],array('type' => 'post'));
 	    	?>
 	    		<li>
	    			<img src="<?=$attachments['boardURL'];?>" alt="" />
	    			<div class="text"><a href="<?=$ipbwi->getBoardVar('url');?>index.php?showtopic=<?=$post['tid'];?>" title="<?=$post['title'];?>"><?=$ipbwi->shorten($post['title'],40);?></a></div>
	    		</li>
	    	<?php
	 			} 
	 		}
	 		?>
	    	</ul>
	    </div>
	</div>
	</div>
	<!-- OP:CONTENT -->
	<div id="content">