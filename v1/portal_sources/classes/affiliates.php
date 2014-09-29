<?php

class class_affiliates {
	/*
	/********************************************************
	/
	/						AFFILIATES
	/
	/********************************************************
	*/
	
function auto_load() {
		global $zone;
		
		switch($zone->ips->input['page']){ 
		case "add" : 
		$this->addlink();
		break;
		case "doadd" : 
		$this->doadd();
		break;
		case "view": 
		$this->viewall();
		break;
		case "out": 
		$this->out();
		break;
		 }
		
}
function add() {
		global $zone;
$zone->echo->html .= $zone->skin_affiliates->add_affiliates();
//$zone->echo->output();
}

function doadd() {
	global $zone;
	$affiliate_url=$zone->ips->input['url'];
	$affiliate_button=$zone->ips->input['button'];
	if(strlen($affiliate_url)<1) {
		      echo "You did not enter a URL.";
			return;
	} 
	if (strlen($affiliate_button)<1) {
		      echo "You did not enter a button url.";
		return;
	}
	$zone->DB->query("INSERT into portal_affiliate (affiliate_url, affiliate_button,affiliate_validated) values('$affiliate_url','$affiliate_button','0')");
	$zone->boink_it($zone->ips->vars['home_url'],$msg="Your Request has been submitted, the request must be validated by a high staff before your button will show.");
	
	
}

function viewall() {
		global $zone;
		$zone->echo->html .= $zone->skin_affiliates->affiliates_top();
	$per_row  = 3;
	$td_width = ceil(100 / $per_row);
	$count    = 0;
	$zone->echo->html .= "<tr align='center'>\n";
	
		$zone->DB->query("SELECT * FROM portal_affiliate WHERE affiliate_validated='1' order by affiliate_hits desc");
		while ($r = $zone->DB->fetch_row($query)) {
			$count++;
			//if (@fclose(@fopen($r['button'], "r"))) {
			//if (@GetImageSize($r['button'])) {
				$affiliate_button = $r['affiliate_button'];
			//}
			//else {
			//	$button = "images/noimage.gif";
			//}
$zone->echo->html .= $zone->skin_affiliates->affiliates(
	$id=$r['affiliate_id'],
	$button=$affiliate_button,
	$click=$r['affiliate_hits'],
	$td_witdh = $td_width
	);
		if ($count == $per_row )
		{
			$zone->echo->html .= "</tr>\n\n<tr align='center'>";
			$count   = 0;
		}
	}
	
	if ( $count > 0 and $count != $per_row )
	{
		for ($i = $count ; $i < $per_row ; ++$i)
		{
			$zone->echo->html .= "<td class='row2'>&nbsp;</td>\n";
		}
		
		$zone->echo->html .= "</tr>";
	}
	$zone->echo->html .= $zone->skin_affiliates->affiliates_bottom();
		$this->add();
$zone->echo->output();
}

function shownum() {
		global $zone;
		$num=2; // number of affiliates
		$zone->DB->query("SELECT * FROM portal_affiliate WHERE affiliate_validated='1' ORDER BY RAND() LIMIT 2");
		while ($r = $zone->DB->fetch_row($query)) { 
			$return .= $zone->skin_affiliates->random_affiliates(
			$id=$r['affiliate_id'],
			$button=$r['affiliate_button'],
			$click=$r['affiliate_hits']
				);
		    } 
		return $return;
}

function out() {
		global $zone;
		$id = $zone->ips->input['id'];
		$query = $zone->DB->query("SELECT affiliate_url from portal_affiliate where affiliate_id='$id'");
		$r = $zone->DB->fetch_row($query);
		$zone->DB->query("UPDATE portal_affiliate set affiliate_hits=affiliate_hits+1 where affiliate_id='$id'");
 		$zone->boink_it($url=$r['affiliate_url'],$msg="You will now be transferred...");
}



} // EOC
?>