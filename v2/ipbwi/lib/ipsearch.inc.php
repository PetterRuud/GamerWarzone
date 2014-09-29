<?php
class class_ipsearch {
	
	
function auto_load() {
	global $ipbwi;
		switch ($ipbwi->makesafe($_REQUEST['code'])) {
		case "search":	
		$this->search();					
		break;
		case "dosearch":	
		$this->dosearch();					
		break;
		case "add":	
		$this->add();					
		break;
		case "doadd":	
		$this->doadd();					
		break;
		
		case "delete":	
		$this->delete();					
		break;
		default :
		$this->splash();
		break;
	}
}

function splash() {
	global $ipbwi;
		$ipbwi->DB->query("SELECT p.*,s.player_search FROM ipbwi_players p, ipbwi_stats s ORDER BY `player_id` DESC " );
		$players = $ipbwi->DB->get_num_rows();
		$r = $ipbwi->DB->fetch_row();
			
			$searches = $r['player_search'];
			
			 
			 $html .= <<<EOF
<h1>Player Search</h1>
<p>Use this tool to search for players or IPs to confirms cheaters or namehiders</p>
<div class="center">
<a href="{$ipbwi->getBoardVar('home_url')}index.php?act=ipsearch&amp;code=add"><img src="/images/add.png" alt="add" /></a>
<a href="{$ipbwi->getBoardVar('home_url')}index.php?act=ipsearch&amp;code=search"><img src="/images/search.png" alt="search" /></a>
</div>
<div class="needspadding">
Players in database: <b>{$players}</b><br />
The feature has been used <b>{$searches}</b> times<br />
<h3>Latest player added</h3>
<table width="100%" cellspacing="1">
	<tr>
		<th class="subbar" width="1%"></th>
		<th class="subbar" width="25%">Player Nickname</th>
		<th class="subbar" width="25%">IP</th>
		<th class="subbar" width="25%">Rating</th>
		<th class="subbar" width="25%">Game</th>
	</tr>
EOF;

#for ($i=0;$i <= 5; $i++) {
	$player_ip = $this->hidedigits($r['player_ip']);
$html .= <<<EOF
	<tr>
		<td class="subbar" width="1%"></td>
		<td class="subbar" width="25%">{$r['player_nick']}</td>
		<td class="subbar" width="25%">{$player_ip}</td>
		<td class="subbar" width="25%">{$r['player_rating']}</td>
		<td class="subbar" width="25%">{$r['player_game']}</td>
	</tr>
EOF;
#}
$html .= <<<EOF
</table>

</div>
EOF;


echo $html;
}

function hidedigits($ip) {
	$ip = substr_replace($ip,"***",-2);
	return $ip;
}
function search() {
	global $ipbwi;
	$html .= <<<EOF
<h1>Search</h1>
<p>Search for player name or IP</p>
<form action="{$ipbwi->getBoardVar('home_url')}index.php?act=ipsearch&amp;code=dosearch" method="post">
<div class="needspadding">
<label for="player_nick">Player Nickname</label>
<div class="smalltext">eg. Player</div>
<input type="text" id="player_nick" name="player_nick" value="" size="35" />
<label for="player_ip">IP Adress</label>
<div class="smalltext">eg. 127.0.0.1)</div>
<input type="text" id="player_ip" name="player_ip" value="" size="35" />
<input type="submit" value="&nbsp;&nbsp;Search...&nbsp;&nbsp;" />
</form>
<div class="needspadding"><a href="?act=ipsearch">Back...</a></div>
</div>
EOF;
echo $html;
}
function dosearch() {
	global $ipbwi;
	$player_ip = $ipbwi->makesafe($_REQUEST['player_ip']);
	$player_nick = $ipbwi->makesafe($_REQUEST['player_nick']);
	if ($player_nick == "" && $player_ip == "") {
		$html .= <<<EOF
        <div style="margin: 3px 15px;" class="errorwrap">
			<h4>The error returned was:</h4>
			<p>You have to insert a name or an IP.</p>
		</div>
EOF;

	}
	else {
		if ($player_nick == "") {
		    $where = "WHERE player_ip LIKE '$player_ip%'";
		    $keywords = $player_ip;
		}
		else {
		$where = "WHERE player_nick LIKE '%$player_nick%'";
		$keywords = $player_nick;
	}	
		$ipbwi->DB->query("UPDATE ipbwi_stats SET player_search=player_search+1");
		$ipbwi->DB->query("SELECT * FROM ipbwi_players $where");
		
		$html .= <<<EOF
		<h1>You Searched for {$keywords}...</h1>
		<div class="needspadding">
		<table width="100%" cellspacing="1">
		<tr>
		<td class="row1" width="1%"></td>
		<td class="subbar" width="25%">Player Name</td>
		<td class="subbar" width="25%">IP Adress</td>
		<td class="subbar" width="25%">Rating</td>
		<td class="subbar" width="25%">Game</td>
		<td class="subbar" width="1%"></td>
		</tr>
EOF;
		
		
	while ($r = $ipbwi->DB->fetch_row($query)) {
		$player_ip = $this->hidedigits($r['player_ip']);
		$html .= <<<EOF
		<tr>
		<td class="row1" width="1%"><img src="{$ipbwi->getBoardVar('home_url')}images/user.png" alt="" /></td>
		<td class="row2" width="25%" align="center">{$r['player_nick']}</td>
		<td class="row1" width="25%" align="center">{$player_ip}</td>
		<td class="row1" width="25%" align="center">{$rating}</td>
		<td class="row2" width="25%">{$r['game']}</td>
		<td class="row2" width="1%">
EOF;
	if($ipbwi->member->isAdmin()) {
		$html .= <<<EOF
		<a href="{$ipbwi->getBoardVar('home_url')}index.php?act=ipsearch&amp;code=delete&amp;id={$r['player_id']}">
		<img src="{$ipbwi->getBoardVar('home_url')}aff_cross.gif" alt="X" />
		</a>
EOF;
	}	
	$html .= <<<EOF
	</td>
		</tr>
EOF;
	}	

	$html .= <<<EOF
</table>
</div>
EOF;
}

$html .= <<<EOF
<div class="needspadding"><a href="{$ipbwi->getBoardVar('home_url')}index.php?act=ipsearch">Back...</a></div>
EOF;

echo $html;

}
function add() {
	global $ipbwi;
			$html .= <<<EOF
<h1>Add Player</h1>
<pAdd a Player</p>
<div class="needspadding">
<form id="form" action="{$ipbwi->getBoardVar('home_url')}index.php?act=ipsearch&amp;code=doadd" method="post" name="addplayer">
<label for="player_nick">Player Nickname</label>
<div class="smalltext">eg. Player</div>
<input class="required" type="text" id="player_nick" name="player_nick" value="" size="35" />
<label for="player_ip">IP Adress</label>
<div class="smalltext">eg. 127.0.0.1)</div>
<input class="required" type="text" id="player_ip" name="player_ip" size="35" />

<label for="player_rating">Player rating</label>
<input type="text" id="player_rating" name="player_rating" />

<script language='javascript'>
	function show_icon() {
	var icon_url = '{$ipbwi->getBoardVar('url')}/images/gameico/' + document.addplayer.player_game.value;
	document.images['iconpreview'].src = icon_url;
	}
</script>
<label>Game</label>
<div class="smalltext">(eg. Tiberian Sun)</div>
<select name='player_game' onChange='show_icon()' class='dropdown'>
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
<input type="submit" value="&nbsp;&nbsp;Add Player...&nbsp;&nbsp;" />
</form>
</div>
<div class="needspadding"><a href="{$ipbwi->getBoardVar('home_url')}index.php?act=ipsearch">Back...</a></div>
EOF;

echo $html;
}
function doadd() {
	global $ipbwi;
	$regex = "'\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b'";
	$player_ip = $ipbwi->makesafe($_REQUEST['player_ip']);
	$player_nick = $ipbwi->makesafe($_REQUEST['player_nick']);
	
	if ($player_nick == "" OR $player_ip == "") {
		$html .= <<<EOF
        <div style="margin: 3px 15px;" class="errorwrap">
			<h4>The error returned was:</h4>
			<p>Both the Player nickname and IP adress fields must be filled.</p>
		</div>
EOF;
	$ipbwi->boink_it($url="?act=ipsearch&amp;code=add",$msg="Player $player_game not added..");
	}
	
	if (preg_match($regex,$player_ip)) {
		#Continue
		$ipbwi->DB->query("INSERT INTO ipbwi_players (player_ip,player_nick,player_game) VALUES('$player_ip','$player_nick','$player_game') ");
		$ipbwi->boink_it($url="?act=ipsearch&amp;code=add",$msg="Player $player_game Added..");
	}
	else {
	$html .= <<<EOF
        <div style="margin: 3px 15px;" class="errorwrap">
			<h4>The error returned was:</h4>
			<p>IP Adress isnt valid.</p>
		</div>
		
EOF;
	$ipbwi->boink_it($url="?act=ipsearch&amp;code=add",$msg="Player $player_game not added..");
	}
	
}

function delete() {
	global $ipbwi;
	$player_id = $ipbwi->makesafe($_REQUEST['player_id']);
	$ipbwi->DB->query("DELETE FROM ipbwi_players WHERE player_id = '$player_id'");
	$ipbwi->boink_it($url="?act=ipsearch&amp;code=add",$msg="Player $player_nick deleted..");
}

} // EOC

?>