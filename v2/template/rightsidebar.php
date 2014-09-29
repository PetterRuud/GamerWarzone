				</div>
			</div>
			
		</div>
		<!-- / OP:MIDDLE COLUMN -->
		
		<!-- OP:RIGHT COLUMN -->
		<div id="right">
			<h2>Latest cheat reports</h2>
			<div class="cont">
				<div class="inner">
				<img src="images/side_cont_bg_top.gif" alt="" />
					<ul>
					 <?php
 	    $latest_topic = $ipbwi->topic->getList(100, array('order' => 'DESC', 'orderby' => 'pid', 'limit' => 5, 'allsubs' => true), $bypassPerms = true);
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
			<h2>Affiliates</h2>
			<div class="cont">
				<div class="inner affiliates">
				
				<?=$affiliates->shownum($num_affiliates);?>
							
				<img src="images/side_cont_bg_bottom.gif" alt="" />
				</div>
			</div>
		</div>
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
		<div class="clearfix">&nbsp;</div>
		<!-- / OP:RIGHT COLUMN -->