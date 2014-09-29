<?php 
require('portal_init.php');

$timer = new Timer;
$timer->startTimer();

	//--------------------------------
	//  Is the board offline?
	//--------------------------------
	$zone->zone_offline();
if ($zone->ips->input['section'] == "" OR $zone->ips->input['section'] == "home") {
	$zone->echo->html .= $zone->skin_global->global_section();
	$zone->echo->output();
}

switch ($zone->ips->input['section'])
{
	case "articles"		:
	$zone->articles->auto_load();										
  	break;
	case "affiliates"		:							
	$zone->affiliates->auto_load();				
	break;
	case "games"		:
	$zone->games->auto_load();
	break;
	case "news"		:
	$zone->news->auto_load();
	break;
	case "ips"		:
	$zone->ipsearch->auto_load();
	break;
		case "replays"		:
	$zone->replays->auto_load();
	break;
	case "about" : 	
	$zone->echo->html .= $zone->skin_global->aboutus();
	$zone->echo->output();		
	break;
	case "contact" : 	
	$zone->echo->html .= $zone->skin_global->contact();
	$zone->echo->output();		
	break;
	case "players" : 	
	$zone->players->auto_load();
	break;
	case "xwis" : 	
	$zone->xwis->auto_load();
	break;
}
?>
