<?php
class class_replays {
	
function auto_load() {
	global $zone;	
	switch ($zone->ips->input['page'])
	{	
		case "replay":	
		$this->show_replay();
		break;
		case "submit":	
		$this->submit();
		break;
		case "dosubmit"	;
		$this->dosubmit();
				case 'rate':
		$this->rate();
		break;
		default	:
		$this->splash();
		break;
	}
}
	
	function splash() {
		global $zone;
		$zone->DB->query("SELECT * FROM portal_replays");
		$zone->echo->html .= $zone->skin_replays->splash_top();
		while($r = $zone->DB->fetch_row()) {
		$zone->echo->html .= $zone->skin_replays->splash(
			$rid = $r['rid'],
			$gsid = $r['gsid'],
			$num_players = $r['num_players'],
			$replay_title = $r['replay_title'],
			$replay_desc = $zone->echo->shorten($r['replay_desc'],270),
			$map = $r['map'],
			$rating = $this->displayrating($r['replay_rating'])
			);
		}
			$zone->echo->html .= $zone->skin_replays->splash_bottom();
		$zone->echo->output();
	}
	
	function submit() {
		global $zone;
		$zone->echo->html .= $zone->skin_replays->submit();
		$zone->echo->output();
	}
	function dosubmit() {
		global $zone, $_cnc3data;
		$replay_title = $zone->ips->input['replay_title'];
		$replay_desc = $zone->ips->input['replay_desc'];
		
		require ( KERNEL_PATH."class_upload.php");
$upload = new class_upload();
$upload->out_file_dir     = './replays/';
$upload->max_file_size    = '10000000';
$upload->make_script_safe = 1;
$upload->allowed_file_ext = array( 'cnc3replay' );
$upload->upload_process();
if ( $upload->error_no )
{
  switch( $upload->error_no )
  {
	  case 1:
		  // No upload
		  $zone->_error($msg="No Files selected",$url="?section=replays&amp;page=submit");
	  case 2:
	  case 5:
		  // Invalid file ext
		  $zone->_error($msg="Not a valid replay file",$url="?section=replays&amp;page=submit");
	  case 3:
		  // Too big...
		   $zone->_error($msg="File too big",$url="?section=replays&amp;page=submit");
        case 4:
		  // Cannot move uploaded file
	$zone->_error($msg="Cannont move the file",$url="?section=replays&amp;page=submit");
  }
 }
	// Load the replay libary class
	require (PORTAL_PATH.'lib/lib_replays.php' );
	
	$rep = new cnc3_replay();
		 if (!$rep->parse($upload->saved_upload_name)) {
		$zone->_error($msg="Unable to parse file, are you sure it is a valid cnc3 replay?",$url="?section=replays&amp;page=submit");
	}
  $r =& $rep->r_infos;
$num_players = count($r);

// get misc info	
	$map_get = $r['map']['fname'];
	$map = $_cnc3data['maps'][$map_get];
	$version_get = $r['misc']['version'];
	$version = $_cnc3data['versions'][$version_get];
	$length = $r['misc']['length'];
	$gsid = $r['misc']['gsid'];
	$speed = $r['options']['speed'];
	$money = $r['options']['money'];
	$delay = $r['options']['delay'];
	$crates = $r['options']['crates'];
//	Insert misc info	
	$zone->DB->query("INSERT INTO portal_replays (gsid,replay_lcoation,num_players,replay_title,replay_desc,map,length,version,speed,money,delay,crates) 
VALUES ('$gsid','$upload->saved_upload_name', '$num_players', '$replay_title','$replay_desc', '$map', '$length', '$version','$speed', '$money', '$delay', '$crates' )");

for($i = 0; $i < $num_players; $i++) {
// get player info

  	$army_get = $r['players'][$i]['army'];
	$army = $_cnc3data['armies'][$army_get];
	$clan = $r['players'][$i]['clan'];
	$name = $r['players'][$i]['name'];
	$ip = $r['players'][$i]['ip'];
	$team = $r['players'][$i]['team'];
	$uid = $r['players'][$i]['uid'];
	$human = $r['players'][$i]['human'];
	$position = $r['players'][$i]['position'];
	$handicap = $r['players'][$i]['handicap'];
	$color_get = $r['players'][$i]['color'];
	$color = $_cnc3data['colors'][$color_get];
	//	Insert player info
	$zone->DB->query("INSERT INTO portal_replays_players (name,clan,team,ip,gsid,army,human,uid,position,handicap,color) VALUES (
'$name', '$clan', '$team', '$ip', '$gsid', '$army', '$human', '$uid', '$position', '$handicap', '$color')");
}
$zone->boink_it($url="?section=replays",$msg="Replay Posted...");


}

	function show_replay() {
		global $zone;
		$this->show_replay_misc();
		$this->show_replay_player();
		$zone->echo->html .= $zone->skin_replays->replay_bottom();
		$zone->echo->output();

}

	function show_replay_player() {
		global $zone;
		$rid = $zone->ips->input['id'];
		$zone->DB->query("SELECT * FROM portal_replays WHERE rid = '$rid'");
		$r2 = $zone->DB->fetch_row($query);
		$gsid = $r2['gsid'];
		$zone->DB->query("SELECT * FROM portal_replays_players WHERE gsid = '$gsid' ORDER BY team");
	while ($r = $zone->DB->fetch_row($query)) { 
$zone->echo->html .= $zone->skin_replays->replay_player(
	$player = $r['name'],
	$army = $r['army'],
	$clan = $r['clan'],
	$team = $r['team'],
	$ip = substr_replace($r['ip'],'***',-2)
	);
}
	

}

	function show_replay_misc() {
		global $zone;
		$rid = $zone->ips->input['id'];
		$zone->DB->query("SELECT * FROM portal_replays WHERE rid = '$rid'");
		$r = $zone->DB->fetch_row($query);
$zone->echo->html .= $zone->skin_replays->replay_info(
	$rid = $r['rid'],
	$replay_title=$r['replay_title'],
	$replay_desc = $r['replay_desc'],
	$replay_location = $r['replay_location'],
	$num_players = $r['num_players'],
	$map = $r['map'],
	$length = ceil($r['length']/60),
	$version = $r['version'],
	$rating = $this->displayrating($r['replay_rating'])
	);
}

function displayrating($rating)
{
		global $zone; 
		if ($rating != 0)
		{
			$rating = $zone->skin_replays->rating_image($rating);
		}
		else
		{
			$rating = '<em>Not Rated</em>';
		}
		return $rating;
}

function rate()
{
	global $zone;
		if(!isset($zone->ips->input['rating'])){
			$zone->boink_it($url="?section=replays");
		}
		$rating = intval($zone->ips->input['rating']);
		if(!$rating) {
			$zone->boink_it($url="?section=replays");
		}
		$rid = intval($zone->ips->input['id']);
		if(!$rid){
			$zone->boink_it($url="?section=replays");
		}
		$this->updaterating($rid, $rating);
		$zone->boink_it($url="?section=replays&amp;page=replay&amp;id=$rid", $msg="Thanks for rating, <br />redirecting back to the replay");
}

function updaterating($rid, $newrating)
	{
		global $zone;
		$zone->DB->query("SELECT rid from portal_rating where rid = '$rid' ");
		if($zone->DB->get_num_rows())
		{
			$zone->Error('error_already_voted');
		}
		$zone->DB->query("SELECT replay_rating,num_votes FROM portal_replays WHERE rid = '$rid' ");
		$r = $zone->DB->fetch_row();
		$new_numvotes = $r['numv_otes'] + 1;
		$tmp_rating = ($r['replay_rating'] * $r['numvotes']);
		$new_rating = (($newrating + $tmp_rating) / ($new_numvotes));
		$new_rating = round($new_rating);
		
		$zone->DB->query("UPDATE portal_replays
		SET replay_rating = '$new_rating', num_votes = num_votes+1
		WHERE rid = '$rid'");

		$member_info = $zone->get_info();
		$member_id = $member_info['id'];
		$member_name = $zone->id2displayname($member_id);
		$zone->DB->query("INSERT INTO portal_rating 
		(rating_member, rating_memberid, rating, rating_replay) VALUES ('$member_name','$member_id','$newrating','$rid')");
	}

	

} // END
?>