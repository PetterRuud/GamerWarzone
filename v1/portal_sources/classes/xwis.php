<?php
class class_xwis {
	
	var $output = "";
	
	function auto_load() {
		global $zone;
	
		switch($zone->ips->input['page']){
		case "ts_ladder_player":
		$this->get_page($title="Tiberian Sun Player Ladder",$page = "http://xwis.net/ts/ladders/ts/");
		break;
		case "ts_ladder_clan":
		$this->get_page($title="Tiberian Sun Clan Ladder",$page = "http://xwis.net/ts/ladders/ts_clan/");
		break;
		case "ts_hof":
		$this->get_page($title="Tiberian Sun Hall Of Fame",$page = "http://xwis.net/ts/ladders/hall_of_fame/");
		break;
		case "ts_hos":
		$this->get_page($title="Tiberian Sun Hall Of Shame",$page = "http://xwis.net/ts/ladders/hall_of_shame/");
		break;
		case "ts_online":
		$this->get_page($title="Tiberian Sun Online",$page = "http://xwis.net/ts/online");
		break;
		case "ts_stats":
		$this->get_page($title="Tiberian Sun Stats",$page = "http://xwis.net/ts/ladders/stats/");
		break;
		
		case "ra2_ladder_player":
		$this->get_page($title="Red Alert 2 Sun Clan Ladder",$page = "http://xwis.net/ladders/ra2/");
		break;
		case "ra2_ladder_clan":
		$this->get_page($title="Red Alert 2 Clan Ladder",$page = "http://xwis.net/ra2/ladders/ra2_clan/");
		break;
		case "ra2_hof":
		$this->get_page($title="Red Alert 2 Hall Of Fame",$page = "http://xwis.net/ra2/ladders/hall_of_fame/");
		break;
		case "ra2_hos":
		$this->get_page($title="Red alert 2 Hall Of Shame",$page = "http://xwis.net/ra2/ladders/hall_of_shame/");
		break;
		case "ra2_online":
		$this->get_page($title="Red alert 2 Online",$page = "http://xwis.net/ra2/online");
		break;
		case "ra2_stats":
		$this->get_page($title="Red Alert 2 Stats",$page = "http://xwis.net/ladders/stats/");
		break;
		
		case "ra2yr_ladder_player":
		$this->get_page($title="Red Alert 2 Yuris Revenge Sun Clan Ladder",$page = "http://xwis.net/ladders/ra2_yr/");
		break;
		case "ra2yr_ladder_clan":
		$this->get_page($title="Red Alert 2 Yuris Revenge Clan Ladder",$page = "http://xwis.net/ra2_yr/ladders/ra2_clan/");
		break;
		case "ra2yr_hof":
		$this->get_page($title="Red Alert 2 Yuris Revenge Hall Of Fame",$page = "http://xwis.net/ra2_yr/ladders/hall_of_fame/");
		break;
		case "ra2yr_hos":
		$this->get_page($title="Red alert 2 Yuris Revenge Hall Of Shame",$page = "http://xwis.net/ra2_yr/ladders/hall_of_shame/");
		break;
		case "ra2yr_online":
		$this->get_page_std($title="Red alert 2 Yuris Revenge Online",$page = "http://xwis.net/ra2_yr/online");
		break;
		case "ra2yr_stats":
		$this->get_page($title="Red Alert 2 Yuris Revenge Stats",$page = "http://xwis.net/ladders/stats/");
		break;
		case "cc3_standings":
		$this->get_page($title="Tiberium Wars",$page = "http://commandandconquer.com/portal/site/tiberium/ladders/");
		break;		
		
		
		default :
		$this->splash();
		break;
		}
	}
	function splash() {
		global $zone;
		$zone->echo->html .= $zone->skin_xwis->splash();
		$zone->echo->output();
	}
	function get_page_std($title,$page) {
		global $zone;
		$file_handle = fopen($page, 'r');
		$result = fread($file_handle,filesize($file_handle));
		fclose($file_handle); // Closing connection
		$zone->echo->html .= $zone->skin_xwis->show($title,$result);
		$zone->echo->output();
	}
	
	function get_page($title,$page) {
		global $zone;
		$ch = curl_init($page); // the target
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return the page
		$result = curl_exec($ch); // executing the cURL
		curl_close($ch); // Closing connection
		//$result = str_replace("Nod", "images/nod.png'>",$result);
		//$result = str_replace("GDI", "images/gdi.png'>",$result);
		$zone->echo->html .= $zone->skin_xwis->show($title,$result);
		$zone->echo->output();
	}
}
?>