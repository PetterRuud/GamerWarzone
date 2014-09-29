<?php
class skin_replays {
	var $zone;

//===========================================================================
// REPLAYS
//===========================================================================

function splash_top() {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
<div class="content_header">Replay List</div>
EOF;

//--endhtml--//
return $HTML;
}

function splash($rid="",$gsid="",$num_players="",$replay_title="",$replay_desc="",$map="",$rating="") {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
<!-- Replay #{$rid} -->
<div class="contentbox">
<div class="replay_map">
</div>
<div class="content_title">
<a href="#">View All Command and Conquer Replays</a>
</div>
<div class="replay_map">
<img src="{$zone->ips->vars['home_url']}/replays/maps/{$map}.jpg" width="40px" height="40px" alt="$map" />

</div>
<div class="content_info">
Players: {$num_players} | Rating: {$rating}
</div>
<div class="content_post">
<a href="{$zone->ips->vars['home_url']}?section=replays&amp;page=replay&amp;id={$rid}" title="{$replay_title}">{$replay_title}</a>
<br /><br />
</div>
</div>
EOF;

//--endhtml--//
return $HTML;
}
function splash_bottom() {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
<div class="content_button_back"><a href="?section=replays&amp;page=submit">Submit Replay</a></div>
EOF;

//--endhtml--//
return $HTML;
}


function submit() {
	global $zone;
		$textarea='replay_desc';
	$form_name='replay_submit';
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
	<div class="contentbox">
<div class="content_header">Submit Replay<br />
<span class="desc">
Submit a Command &amp; Conquer Tiberium Wars Replay
</span>
</div>
<form enctype="multipart/form-data" action="{$zone->ips->vars['home_url']}/?section=replays&amp;page=dosubmit" method="post" name="replay_submit">
<div class="box">
<b>Replay Title</b><br />
<input type="text" name="replay_title" value="" size="40"/>
<br /><br />
<b>Replay Descriptiom</b><br />
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
<br /><br />
<b>Replay File</b><br />
<input type="file" name="FILE_UPLOAD" size="50"/>
</div>
<div class="content_center"><input type="submit" value="&nbsp;&nbsp;Upload Now!&nbsp;&nbsp;" /></div>
</form>
</div>
<div class="content_button_back"><a href="?section=replays">Back...</a></div>
EOF;

//--endhtml--//
return $HTML;
}


function replay_player($player="",$army="",$clan="",$team="",$ip="") {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
<div class="content_title">{$player} <img src="{$zone->ips->vars['home_url']}/replays/factions/{$army}.png" alt="{$army}" /></div>
<div class="content_info">
EOF;
if ($clan != "") {
	$HTML .= <<<EOF
Clan: {$clan} 
EOF;
}
	$HTML .= <<<EOF
Team: {$team}
IP: {$ip}
</div>
EOF;

//--endhtml--//
return $HTML;
}

function replay_info($rid="",$replay_title="",$replay_location="",$replay_desc="",$num_players="",$map="",$length="",$version="",$rating="") {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
	<!-- Replay #{$rid} -->
<div class="content_header">{$replay_title}</div>
<div class="contentbox">
<div class="replay_map">
<img src="{$zone->ips->vars['home_url']}/replays/maps/{$map}.jpg" alt="$map" /><br />
<div class="replay_info">
<div class="mapname">{$map}</div>
<b>Players:</b> {$num_players}<br />
<b>Version:</b> {$version}<br />
<b>Length:</b> {$length}
</div>
</div>
<div class="content_post">
<div class="downloadbox"><a href="./replays/{$replay_location}" title="Download {$replay_title}">Download</a></div>
<div class="ratebox">Rating: {$rating}</div>
<ul id="rate">
<li class="menubutton"><a href="#">Rate</a>
<ul class="menu_down">
<li>
<form action="{$zone->ips->vars['home_url']}/?section=replays&amp;page=rate&amp;id={$rid}" method="post">
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
<div align="center"><input type="submit" value="Rate Replay" /></div>
</form>
</ul>
</li>
</li>
</ul><!-- end rate -->
<div class="clear"></div>
{$replay_desc}<br />
EOF;

//--endhtml--//
return $HTML;
}

function replay_bottom() {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
</div>
</div>
<div class="content_button_back"><a href="?section=replays">Back...</a></div>
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

} // END OF CLASS

?>