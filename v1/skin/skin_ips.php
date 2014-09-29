<?php
class skin_ips {
	var $zone;
	
function ip_splash($players="",$searches="",$latest_ip="", $latest_player="") {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	$HTML .= <<<EOF
<div class="contentbox">
<div class="content_header">Player Search<br />
<span class="desc">Use this tool to search for players or IPs to confirms cheaters or namehiders</span></div>
<div class="content_center"><a href="{$zone->ips->vars['home_url']}/?section=ips&amp;page=add">
<img src="images/add_player.gif">
</a>
<a href="{$zone->ips->vars['home_url']}/?section=ips&amp;page=search">
<img src="images/player_search.gif">
</a>
<br />
<div class="content">
Players in database: <b>{$players}</b><br />
The feature has been used <b>{$searches}</b> times<br />
Latest player added
<table width="100%" cellspacing="1">
	<tr>
	<td class="subbar" width="1%"></td>
	<td class="subbar" width="25%">{$latest_player}</td>
	<td class="subbar" width="25%">{$latest_ip}</td>
	<td class="subbar" width="25%">Rating</td>
	<td class="subbar" width="25%">Game</td>
	<td class="subbar" width="1%"></td>
	</tr>
</table>


</div>
</div>
</div>
EOF;

//--endhtml--//
return $HTML;
}
function ip_search() {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	$HTML .= <<<EOF
<div class="contentbox">
<div class="content_header">Search<br />
<span class="desc">
Search for player name or IP
</span>
</div>
<form action="{$zone->ips->vars['home_url']}/?section=ips&amp;page=dosearch" method="post">
<div class="box">
<b>Player Name</b><br />
<div class="content_info">eg. Player</div>
<input type="text" name="pname" value="" size="35" />
<br /><br />
<b>IP Adress</b><br />
<div class="content_info">eg. 127.0.0.1)</div>
<input type="text" name="pip" value="" size="35" />
</div>
<div class="content_center"><input type="submit" value="&nbsp;&nbsp;Search...&nbsp;&nbsp;" /></div>
</form>
</div>
<div class="content_button_back"><a href="?section=ips">Back...</a></div>
EOF;

//--endhtml--//
return $HTML;
}
function ip_search_results_top($keywords="") {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
	<div class="content_header">You Searched for {$keywords}...</div>
	<table width="100%" cellspacing="1">
	<tr>
	<td class="row1" width="1%"><img src="{$zone->ips->vars['home_url']}/images/user.png" alt="" /></td>
	<td class="subbar" width="25%">Player Name</td>
	<td class="subbar" width="25%">IP Adress</td>
	<td class="subbar" width="25%">Rating</td>
	<td class="subbar" width="25%">Game</td>
	<td class="subbar" width="1%"></td>
	</tr>
EOF;

//--endhtml--//
return $HTML;
}

function ip_search_results($playername="",$ip="",$game="",$id=""){
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
	<tr>
	<td class="row1" width="1%"><img src="{$zone->ips->vars['home_url']}/images/user.png" alt="" /></td>
	<td class="row2" width="25%" align="center">{$playername}</td>
	<td class="row1" width="25%" align="center">{$ip}</td>
	<td class="row1" width="25%" align="center">{$rating}</td>
	<td class="row2" width="25%">{$game}</td>
	<td class="row2" width="1%">
EOF;
if($zone->is_ingroup(array(4,18,48))) { //4 Webmaster 18 Admin 48 Zone Manager
	$HTML .= <<<EOF
	<a href="{$zone->ips->vars['home_url']}?section=ips&amp;page=delete&amp;id={$id}">
	<img src="{$zone->ips->vars['img_url']}/aff_cross.gif" alt="X" />
	</a>
EOF;
}
$HTML .= <<<EOF
</td>
	</tr>
EOF;

//--endhtml--//
return $HTML;
}
function ip_search_results_bottom() {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
</table>
<div class="content_button_back"><a href="?section=ips">Back...</a></div>
EOF;

//--endhtml--//
return $HTML;
}

function ip_add() {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	$HTML .= <<<EOF
<div class="contentbox">
<div class="content_header">Add Player<br />
<span class="desc">Add a Player</span>
</div>
<div class="box">
<form action="{$zone->ips->vars['home_url']}/?section=ips&amp;page=doadd" method="post" name="addplayer">
Player Name<br />
<span class="desc">(eg. Player)</span><br />
<input type="text" name="pname" value="" />
<br /><br />
IP Adress<br />
<span class="desc">(eg. 127.0.0.1)</span><br />
<input type="text" name="pip" value="" /><br />
<script language='javascript'>
	function show_icon() {
	var icon_url = '{$zone->ips->vars['board_url']}/images/gameico/' + document.addplayer.pgame.value;
	document.images['iconpreview'].src = icon_url;
	}
</script>
Game<br />
<span class="desc">(eg. Tiberian Sun)</span> <br />
<select name='pgame' onChange='show_icon()' class='dropdown'>
<option value='blank.gif'>Select a Game Icon</option>
<option value='ts.gif'>Tiberian Sun</option>
<option value='ra.gif'>Red Alert</option>
<option value='ra2.gif'>Red Alert 2</option>
<option value='ra3.gif'>Red Alert 3</option>
<option value='tw.gif'>Tiberium Wars</option>
<option value='kw.gif'>Kane's Wrath</option>
<option value='ren.gif'>Renegade</option>
<option value='wc.gif'>Warcraft</option>
</select>
&nbsp;&nbsp;<img src='{$image}' name='iconpreview' border='0' />
<br />

<div class="content_center"><input type="submit" value="&nbsp;&nbsp;Add Player...&nbsp;&nbsp;" /></div>
</form>
</div>
</div>
<div class="content_button_back"><a href="?section=ips">Back...</a></div>
EOF;

//--endhtml--//
return $HTML;
}

// END OF SKIN GLOBAL

}
?>