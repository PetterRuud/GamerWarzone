<?php
class class_players {
	
	var $output = "";
	
function auto_load() {
	global $zone;
	switch ($zone->ips->input['page'])
	{		
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
	global $zone;
		$zone->DB->query("SELECT p.pid,s.player_search from portal_players p, portal_stats s");
		$players = $zone->DB->get_num_rows();
		$r = $zone->DB->fetch_row();
			 	$zone->echo->html .= $zone->skin_ips->ip_splash($players,$searches=$r['player_search']);
$zone->echo->output();
}

function hidedigits($ip) {
	$ip = substr_replace($ip,"***",-2);
	return $ip;
}
function search() {
	global $zone;
			 	$zone->echo->html .= $zone->skin_ips->ip_search();
$zone->echo->output();
}
function dosearch() {
	global $zone;
	$ip = $zone->ips->input['pip'];
	$name = $zone->ips->input['pname'];
	if ($name == "" && $ip == "") {
	$zone->_error($msg="You have to insert a name or an IP",$url="?section=ips&amp;page=search");
	}
	if ($name == "") {
		$where = "WHERE pip LIKE '$ip%'";
		$keywords = $ip;
	}
	else {
	$where = "WHERE pname LIKE '$name%'";
	$keywords = $name;
}
$zone->DB->query("UPDATE portal_stats SET player_search=player_search+1");
	$zone->DB->query("SELECT * FROM portal_players $where");
	$zone->echo->html .= $zone->skin_ips->ip_search_results_top($keywords);
while ($r = $zone->DB->fetch_row($query)) {
			 	$zone->echo->html .= $zone->skin_ips->ip_search_results(
				$playername = $r['pname'],
				$ip = $this->hidedigits($r['pip']),
				$game = $r['pgame'],
				$id = $r['pid']
				);
}
$zone->echo->html .= $zone->skin_ips->ip_search_results_bottom();
$zone->echo->output();	

}
function add() {
	global $zone;
			 	$zone->echo->html .= $zone->skin_ips->ip_add();
$zone->echo->output();
}
function doadd() {
	global $zone;
	$regex = "'\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b'";
	$ip = $zone->ips->input['pip'];
	$name = $zone->ips->input['pname'];
	$game = $zone->ips->input['pgame'];
		if ($ip == "" OR $name == "") {
			$zone->_error($msg="Both the Playername and IP adress field must be filled",$url="?section=ips&amp;page=add");
	}
	if (preg_match($regex,$ip)) {
		#Continue
	}
	else {
		$zone->_error($msg="IP Adress isnt valid",$url="?section=ips&amp;page=add");
	}
	$zone->DB->query("INSERT INTO portal_players (pip,pname,pgame) VALUES('$ip','$name','$game') ");
	$zone->boink_it($url="?section=ips&amp;page=add",$msg="Player $name Added..");
}

function delete() {
	global $zone;
	$id = $zone->ips->input['id'];
	$zone->DB->query("DELETE FROM portal_players WHERE pid = '$id'");
	$zone->boink_it($url="?section=ips&amp;page=add",$msg="Player $name deleted..");
}

} // EOC

?>