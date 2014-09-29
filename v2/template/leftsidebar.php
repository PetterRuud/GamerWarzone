
		<!-- OP:LEFT COLUMN -->
		<div id="left">
		
			<h2>Quick Links</h2>
			<div class="cont">
				<div class="inner">
				<img src="images/side_cont_bg_top.gif" alt="" />
					<ul>
						<li><a href="<?=$ipbwi->getBoardVar('home_url');?>">Home</a></li>
						<li><a href="<?=$ipbwi->getBoardVar('url');?>">Forums</a></li>
						<li><a href="<?=$site_url;?>/index.php?act=ipsearch">Player search</a></li>
						<li><a href="<?=$ipbwi->getBoardVar('url');?>index.php?autocom=downloads">Downloads</a></li>
						<li><a href="<?=$ipbwi->getBoardVar('url');?>index.php?act=members">Members</a></li>
						<li><a href="<?=$ipbwi->getBoardVar('url');?>index.php?autocom=downloads">Downloads</a></li>
						<li><a href="<?=$ipbwi->getBoardVar('url');?>index.php?autocom=market">Shop</a></li>
						<li><a href="<?=$ipbwi->getBoardVar('url');?>index.php?autocom=casino">Casino</a></li>
						<li><a href="<?=$ipbwi->getBoardVar('url');?>index.php?autocom=arcade">Arcade</a></li>
						<li><a href="<?=$site_url;?>/index.php?act=contact">Contact</a></li>
					</ul>
				<img src="images/side_cont_bg_bottom.gif" alt="" />
				</div>
			</div>
			
			<h2><?=$online;?> Online</h2>
			<div class="cont">
				<div class="inner">
				<img src="images/side_cont_bg_top.gif" alt="" />
					<p><?=$onlinelist;?></p>
				<img src="images/side_cont_bg_bottom.gif" alt="" />
				
				</div>
			</div>
			
			<h2>Search</h2>
			<div class="cont">
				<div class="inner">
				<img src="images/side_cont_bg_top.gif" alt="" />
				<form action="<?=$ipbwi->getBoardVar('url');?>index.php?act=Search&amp;CODE=01" method="post">
					<input type='hidden' name='forums' id="earch-forums-top" value='all' /> 
					<input type="text" size="16" name="keywords" id="search-box-top" class="search swap" />
					<input class="go" type="submit" value="Search" />
				</form>

				<img src="images/side_cont_bg_bottom.gif" alt="" />
				
				</div>
			</div>
			
			<h2>Latest Topics</h2>
			<div class="cont">
				<div class="inner">
				<img src="images/side_cont_bg_top.gif" alt="" />
					<ul>
							    <?php
 	    $latest_topic = $ipbwi->topic->getList('*', array('order' => 'DESC', 'orderby' => 'pid', 'limit' => 5, 'allsubs' => true), $bypassPerms = false);
    	foreach($latest_topic as $topic){

    	 	if(is_array($latest_topic) && count($latest_topic)>0){
 	    ?>
	    		<li><a href="<?=$ipbwi->getBoardVar('url');?>index.php?showtopic=<?=$topic['tid'];?>"><?=$ipbwi->shorten($topic['title'],22); ?></a></li>
	    <?php
	     	}
	     }
	     ?>
					</ul>
				<img src="images/side_cont_bg_bottom.gif" alt="" />
				
				</div>
			</div>
			<!-- OP:EDIT FLAG 1 -->
			
			<!-- / OP: EDIT FLAG 1 -->
			<div class="ad">
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
</div>
		</div>
		<!-- / OP:LEFT COLUMN -->
		
		<!-- OP:MIDDLE COLUMN -->
		<div id="middle">
			<h2>Main Content</h2>
			<div class="main">
				<div class="inner">
					<img src="images/main_cont_bg_top.gif" alt="" />