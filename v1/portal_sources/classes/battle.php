<?php

class class_battle {

	/*
	/********************************************************
	/
	/						TOURNAMENT
	/
	/********************************************************
	*/
	function do_battle_list() {
		global $zone;
		$zone->DB->query("SELECT * FROM portal_battle_tournament");
		while ($r = $zone->DB->fetch_row($query)) {
echo <<<EOF
			<table class="row2b" width="100%">
			<tr>
			<td rowspan="4" width="1%" valign="top">{$r['bimage']}</td>
			</tr>
			<tr>
			<td class="row4" align="left">
			<a href="?section=articles&articles=aid&aid={$r['aid']}">{$r['bname']}</a></td>
			<td class="row4" align="right">Rules:<br />
			Max Players : {$r['bmaxplayers']}<br>
			Min Players : {$r['bminplayers']}<br>
			Number of Players: {$r['bplayers']}
			</td>
			</tr>
			<td class="row2" colspan="2">{$r['bdetails']}</td>
			</tr>
			<td class="centerbox"><a href="?section=battle&battle=signup&signup={$r['bid']}">Sign Up</a></td>
			<td class="centerbox"><a href="?section=battle&battle=bracket&bracket={$r['bid']}">View standings</a></td>
			</table><br />
EOF;
		}
	}
	function battle_signup($bid) {
		global $zone;
		$info = $zone->get_info();

			if(isset($_POST['signup']) && $_POST['signup'] != '' && isset($_POST['nick']) && $_POST['nick'] != '') {
				$date = time();
				$commemberid = $user_info['id'];
				$c = $_POST['post'];
				$comment = safehtml($c);
				$t = $_POST['title'];
				$title = safehtml($t);
				$comname = $zone->id2displayname($commemberid);
				if($zone->DB->query("INSERT INTO portal_comments 
					(commemberid,comname,comarticle,comdate,comtext,comtitle) 
					VALUES ('$commemberid','$comname','$aid','$date','$comment','$title')")) {
					$zone->DB->query("UPDATE portal_articles SET alast_comment = '$title', acomments = acomments + 1 where aid = '$aid'");
					$system_info = "<meta http-equiv=\"Refresh\" content=\"0; url=".$PHP_SELF."\">";
				} else {system_info == $zone->zone_error();}
			}

			// Error Output
			if(isset($system_info) && $system_info != '') { echo '<div class="info"><div class="i_system">'.$system_info.'</div></div>'; }

	if($zone->is_loggedin()) { 
echo <<<EOF
	<h2>Sign up</h2>		
				<form action="{$PHP_SELF}" name="post" method="POST">
					Game Nickname: <input type="text" name="nick" /><br />
					Your Forum  Displayname: {$info['members_display_name']}<br />
					<input type="hidden" name="bid" />
					<input type="hidden" name="memberid" />
					<input type="submit" name="signup" value="Sign Up!" />
				</form>

EOF;
		 } else { 
				echo"<p>Login to sign up.</p>";
		} 
	}
	function do_battle_bracket($bid) {
		global $zone;
		$zone->DB->query("SELECT * FROM  portal_battle, portal_battle_players");
		$r = $zone->DB->fetch_row($query);
echo <<<EOF
		<table width='100%' cellspacing='0' cellpadding='2'>
	    <tr>
	    <td width='100%' valign='top' align='center'>
	    <h3>{$r['bname']} Brackets</h3>
EOF;
		while ($r = $zone->DB->fetch_row($query)) {
echo <<<EOF
	    </td></tr></table>
	    <br><br>
	    <table border='0' width='100%' cellspacing='0' cellpadding='0'>
	    <tr>
		 <td class="centerbox" width="30%"><b>{$r['bpname']}</b></td>
		</tr>
		<tr>
		 <td class="centerbox" width="30%"><b>{$r['bpname']}</b></td>

	    </tr>
	</table>
	<br />
EOF;
		}
	}
} // EOC
?>