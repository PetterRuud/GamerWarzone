<?php

class class_games {

//=========================================================//
//				VIEW GAME UNITS
//========================================================//

function auto_load() {
	global $zone;
switch ($zone->ips->input['page']){
	  	case "units"		: 	
	$this->game_units();		
		break;
	  	case "structures"	: 	
	$this->game_structures();									
		break;
	  	case "factioninfo"	: 	
	$this->game_info();				
		break;
		case "info"			:	
		$this->game_info();					
		break;
	  	default		:	
		$this->game_info();					
		break;
	}
}

function game_info($gameid) {
	global $zone;
	//$gameid = $zone->ips->input['gameid'];
	$gameid = $gameid;
	$factionid = $zone->ips->input['factionid'];
	//$portal = $zone->ips->input['portal'];
	$zone->DB->query("SELECT * FROM portal_games WHERE gamename = '$gameid'");
	$r1 = $zone->DB->fetch_row($query);
	
		$zone->echo->html .= $zone->skin_games->game_info(
			$gametitle = $r1['gamename'],
			$gameimage = $r1['gameimage'],
			$publisher = $r1['gamepublisher'],
			$release = $r1['gamerelease'],
			$website = $r1['gameweb'],
			$req = $r1['gamereq'],
			$gamedesc = $r1['gamedesc']
			);
$zone->DB->query("SELECT * FROM portal_game_factions WHERE factiongameid = '$gameid'");
while($r2 = $zone->DB->fetch_row($query)){
	$zone->echo->html .= $zone->skin_games->game_factions(
			$factiontitle = $r2['factionname'],
			$factionimage = $r2['factionimage'],
			$factiondesc = $r2['factiondesc']
			);
}
	$zone->echo->output();
}
//=========================================================//
//				VIEW GAME UNITS
//========================================================//
	function game_units() {
		global $zone;
		$gameid = $zone->ips->input['gameid'];
		$factionid = $zone->ips->input['factionid'];
	
		$zone->DB->query("SELECT gu.*,gf.* 
			FROM portal_game_units gu, portal_game_factions gf
			WHERE gu.unitgameid = '$gameid'
			AND gu.factionid = '$factionid'
			AND gf.factionid = '$factionid' 
			");
	while ($r = $zone->DB->fetch_row($query)) {
		if ($zone->DB->get_num_rows($query) == 0) {
			$zone->echo->html .= "No Units";
			return;
		}
$zone->echo->html .= $zone->skin_games->stuff(
$name = $r['unitname'],
$image = $r['unitimage'],
$desc = $r['unitdesc'],
$factionname = $r['factionname'],
$factionimage = $r['factionimage']
);
	}
	$zone->echo->output();
}

//=========================================================//
//				VIEW GAME STRUCTURES
//========================================================//

function game_structures() {
	global $zone;
		$gameid = $zone->ips->input['gameid'];
		$factionid = $zone->ips->input['factionid'];
	$zone->DB->query("SELECT gs.*,gf.* 
		FROM portal_game_structures gs, portal_game_factions gf
		WHERE gs.structuregameid = '$gameid'
		AND gs.factionid = '$factionid'
		AND gf.factionid = '$factionid' 
		");
	while ($r = $zone->DB->fetch_row($query)) {
		if ($zone->DB->get_num_rows($query) == 0) {
			$zone->echo->html .= "No Units";
			return;
		}
$zone->echo->html .= $zone->skin_games->stuff(
$name = $r['unitname'],
$image = $r['unitimage'],
$desc = $r['unitdesc'],
$factionname = $r['factionname'],
$factionimage = $r['factionimage']
);
	}
	$zone->echo->output();
}
} // EOC
?>