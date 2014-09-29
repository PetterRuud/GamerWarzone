<?php
$ad_games = new ad_games;
$zone->acp->content =& $ad_games;

class ad_games {
/*
/********************************************************
/
/						Games
/
/********************************************************
*/
function auto_run() {
	global $zone;
	
	$zone->acp->nav[] = array( $url,'Games' );
	
	switch($_GET['page']) {
	 	case "addgame"			: 	
		$this->add_game();						
		break;
		case "editgame"			:
		$this->edit_game($_GET['gameid']);
		break;
		case "delete"			:	
		$this->delete_game();						
		break;
		case "factions"			:	
		$this->show_factions();					
		break;
		case "addfaction"		:	
		$this->add_faction();						
		break;
		case "editfaction"			:
		$this->edit_faction($_GET['factionid']);
		break;
		case "units"			: 	
		$this->show_units($_GET['gameid']);	
		$this->add_unit($_GET['gameid']);			
		break;
		case "deleteunit"		: 	
		$this->delete_unit($_GET['unitid']);		
		break;
		case "editunit"		: 	
		$this->edit_unit($_GET['unitid']);		
		break;
		case "structures"		: 	
		$this->show_structures($_GET['gameid']);
		$this->add_structure($_GET['gameid']);	
		break;
		case "editstructure"		: 	
		$this->edit_structure($_GET['structureid']);		
		break;
	  	default					:	
		$this->show_games();	
		break;
	}
}

//--------------------------------------------
//				List the games
//--------------------------------------------
	
function show_games() {
	global $zone;

	$zone->acp->html .= <<<EOF
			<div class='tableborder'>
			 <div class='tableheaderalt'>Games</div>
			 <table cellpadding='4' cellspacing='0' border='0' width='100%'>
EOF;
	$zone->DB->query("SELECT * FROM portal_games");
		while ($r = $zone->DB->fetch_row($query)) {
			$desc = $r['gamedesc'];
			$req = $r['gamereq'];
			$bbgamedesc = $zone->html2bbcode($desc);
			$bbgamereq = $zone->html2bbcode($req);

$zone->acp->html .= <<<EOF
<tr>
				<td width='{$td_width}%' align='left' style='background-color:#F1F1F1;padding:6px;'>
				  <fieldset>
				  	<legend><strong>{$r['gamename']}</strong></legend>
				  	<div style='border:1px solid #BBB;background-color:#EEE;margin:2px;padding:1px'>
				  	<table cellpadding='4' cellspacing='0' border='0' width='100%'>
				  	<tr>
				  	 <td width='1%' align='center'><img src="{$r['gameimage']}" width='50' height='25' /></td>
				  	 <td width='99%'>
				  	  <a style='font-size:12px;font-weight:bold' title='View this members profile' href='?section=games&page=editgame&gameid={$r['gameid']}'>{$r['gamename']}</a>
				  	  &nbsp;(<strong>
					<a href="?section=games&page=editgame&gameid={$r['gameid']}">Edit</a></strong>)
				  	 </td>
				  	</tr>
				    </table>
				   </div>
		 </td>
</tr>
EOF;
		}
$zone->acp->html .= <<<EOF
	    </table>
	   </div>
EOF;
}

function edit_game($gameid) {
	global $zone;
	
	$zone->acp->nav[] = array( $url,'Games','Editing game' );
	
	if(isset($_POST['submit'])) {
		$gamename = $zone->makesafe($_POST['gamename']);
		$gamedev = $zone->makesafe($_POST['gamedev']);
		$gamereq = $zone->makesafe($_POST['gamereq']);
		$gamepublisher = $zone->makesafe($_POST['gamepublisher']);
		$gameweb = $zone->makesafe($_POST['gameweb']);
		$gamerelease = $zone->makesafe($_POST['gamerelease']);
		$gameimage = $_POST['gameimage'];
		$gsafe = $zone->makesafe($_POST['gamedesc']);
		$gamedesc = $zone->bbcode2html($gsafe);
		$gameid = $_POST['gameid'];
		$zone->DB->query("UPDATE portal_games 
			SET 
			gamename = '$gamename', 
			gamedesc = '$gamedesc',
			gameimage = '$gameimage',
			gamepublisher = '$gamepublisher',
			gamerelease = '$gamerelease',
			gameweb	=	'$gameweb',
			gamedev	=	'$gamedev',
			gamereq = 	'$gamereq'
			WHERE gameid = '$gameid'");
		$zone->boink_it($url="?section=games&page=editgame&gameid=$gameid",$msg="Game edited...");
	}
	else {
		$zone->DB->query("SELECT * FROM portal_games WHERE gameid = '$gameid' ");
			$r = $zone->DB->fetch_row($query);
				$desc = $r['gamedesc'];
				$req = $r['gamereq'];
				$bbgamedesc = $zone->html2bbcode($desc);
				$bbgamereq = $zone->html2bbcode($req);
$zone->acp->html .= <<<EOF
	<form name="updategame" method="POST" action="{$PHP_SELF}">
		<input type="hidden" name="gameid" value="{$r['gameid']}" />
	<div class='tabclear'>Editing: {$r['gamename']} <span style='font-weight:normal'>(ID: {$r['gameid']})</span></div>

	<div class='tableborder'>
	<div class='formmain-background'>

	 	<table cellpadding='0' cellspacing='0' border='0' width='100%'>
		 <tr>
		   <td>
			<fieldset class='formmain-fieldset'>

			    <legend><strong>{$r['gamename']}</strong></legend>
				<table cellpadding='0' cellspacing='0' border='0' width='100%'>
				 <tr>
					<td width='1%' class='tablerow1'>

						<div style='border:1px solid #000;background:#FFF;width:180px; padding:10px'>
							<img src="{$r['gameimage']}"/>	
						</div>
						<br />
						<input type="text" name="gameimage" value="{$r['gameimage']}" />
					</td>
					<td>
						     <table cellpadding='0' cellspacing='0' border='0' width='100%'>
							 <tr>
							   <td width='40%' class='tablerow1'><strong>Game Title</strong></td>
							   <td width='60%' class='tablerow2'>
								<input type="text" name="gamename" value="{$r['gamename']}" />	
							   </td>
							  </tr>
							  <tr>
								<td width='40%' class='tablerow1'><strong>Developer</strong></td>
								<td width='60%' class='tablerow2'>
								<input type="text" name="gamedev" value="{$r['gamedev']}" />
								</td>
							  </tr>
							  <tr>
								<td width='40%' class='tablerow1'><strong>Publisher</strong></td>
								<td width='60%' class='tablerow2'>
								<input type="text" name="gamepublisher" value="{$r['gamepublisher']}" />
								</td>
							  </tr>
							  <tr>
								<td width='40%' class='tablerow1'><strong>Offical Website</strong></td>
								<td width='60%' class='tablerow2'>
								<input type="text" name="gameweb" value="{$r['gameweb']}" />
								</td>
							  </tr>
							  <tr>
								<td width='40%' class='tablerow1'><strong>Release Date</strong></td>
								<td width='60%' class='tablerow2'>
								<input type="text" name="gamerelease" value="{$r['gamerelease']}" />
								</td>
							 </tr>
							 </table>
					</td>
				 </tr>
				</table>
			</fieldset>
			<br />

			<fieldset class='formmain-fieldset'>
			    <legend><strong>Game Info</strong></legend>			
			<table cellpadding='0' cellspacing='0' border='0' width='100%'>
				 <tr>
				   <td width='40%' class='tablerow1'><strong>System Requirements</strong><div style='color:gray'></div></td>
				   <td width='60%' class='tablerow2'>
				<textarea name="gamereq" cols="70" rows="5">{$bbgamereq}</textarea></td>

				  </tr>
				 <tr>
				   <td width='40%' class='tablerow1'><strong>Game Description</strong><div style='color:gray'></div></td>
				   <td width='60%' class='tablerow2'>
				<textarea name="gamedesc" cols="70" rows="15">{$bbgamedesc}</textarea>
				</td>
				  </tr>
				 </table>		
				</fieldset>
			</td>

		</tr>
		</table>
	</div>

	<div align='center' class='tablefooter'>
	 	<div class='formbutton-wrap'>
	 		<div id='button-save'>
	<input type="submit" name="submit" value="UPDATE" class='realbutton' />
	</div>

		</div>
	</div>
	</div>
	</form>
EOF;
		}
}

//--------------------------------------------
//				List the factions
//--------------------------------------------

function show_factions() {
		global $zone;

$zone->acp->nav[] = array( $url,'Factions' );

			$zone->acp->html .= <<<EOF
					<div class='tableborder'>
					 <div class='tableheaderalt'>Factions</div>
					 <table cellpadding='4' cellspacing='0' border='0' width='100%'>
EOF;
		$zone->DB->query("SELECT * FROM portal_game_factions");
			while ($r = $zone->DB->fetch_row($query)) {

			$zone->acp->html .= <<<EOF
				<tr>
								<td width='{$td_width}%' align='left' style='background-color:#F1F1F1;padding:6px;'>
								  <fieldset>
								  	<legend><strong>{$r['factiongamename']}</strong></legend>
								  	<div style='border:1px solid #BBB;background-color:#EEE;margin:2px;padding:1px'>
								  	<table cellpadding='4' cellspacing='0' border='0' width='100%'>
								  	<tr>
								  	 <td width='1%' align='center'><img src="{$r['factionimage']}" width='50' height='25' /></td>
								  	 <td width='99%'>
								  	  <a style='font-size:12px;font-weight:bold' title='View this members profile' href='?section=games&page=editgame&gameid={$r['gameid']}'>{$r['factionname']}</a>
								  	  &nbsp;(<strong>
									<a href="?section=games&page=editfaction&factionid={$r['factionid']}">Edit</a></strong>)
								  	 </td>
								  	</tr>
								    </table>
								   </div>
						 </td>
				</tr>
EOF;
	}
$zone->acp->html .= <<<EOF
		</table>
	</div>
EOF;
}

function edit_faction($factionid) {
		global $zone;

		if(isset($_POST['submit'])) {
			$factionname = $zone->makesafe($_POST['factionname']);
			$factionimage = $_POST['factionimage'];
			$factiongameid = $_POST['factiongameid'];
			$fsafe = $zone->makesafe($_POST['factiondesc']);
			$factiondesc = $zone->bbcode2html($fsafe);
			$factionid = $_POST['factionid'];
			$zone->DB->query("UPDATE portal_game_factions 
				SET 
				factionname = '$factionname', 
				factionimage = '$factionimage',
				factiondesc = '$factiondesc',
				factiongameid = '$factiongameid'
				WHERE factionid = '$factionid'");
				$zone->boink_it($url="?section=games&page=editfaction&factionid=$factionid",$msg="Faction edited...");
		}
		else {
			$zone->DB->query("SELECT * FROM portal_game_factions WHERE factionid='$factionid'");
				$r = $zone->DB->fetch_row($query);
					$a = $r['factiondesc'];
					$bbfactiondesc = $zone->html2bbcode($a);
					
			$zone->acp->html .= <<<EOF
				<form name="updatefaction" method="POST" action="{$PHP_SELF}">
					<input type="hidden" name="factionid" value="{$r['factionid']}" />
				<div class='tabclear'>Editing: {$r['factionname']} 
				<span style='font-weight:normal'>(ID: {$r['factionid']})</span></div>

				<div class='tableborder'>
				<div class='formmain-background'>

				 	<table cellpadding='0' cellspacing='0' border='0' width='100%'>
					 <tr>
					   <td>
						<fieldset class='formmain-fieldset'>

						    <legend><strong>{$r['factionname']}</strong></legend>
							<table cellpadding='0' cellspacing='0' border='0' width='100%'>
							 <tr>
								<td width='1%' class='tablerow1'>

									<div style='border:1px solid #000;background:#FFF;width:180px; padding:10px'>
										<img src="{$r['factionimage']}"/>	
									</div>
									<br />
									<input type="text" name="factionimage" value="{$r['factionimage']}" />
								</td>
								<td>
									     <table cellpadding='0' cellspacing='0' border='0' width='100%'>
										 <tr>
										   <td width='40%' class='tablerow1'><strong>Faction Title</strong></td>
										   <td width='60%' class='tablerow2'>
											<input type="text" name="factionname" value="{$r['factionname']}" />	
										   </td>
										  </tr>
										  <tr>
											<td width='40%' class='tablerow1'><strong>Game</strong></td>
											<td width='60%' class='tablerow2'>
											<select name="factiongameid">
											<option value="{$r['factiongameid']}">{$r['factiongamename']}</option>
											</td>
										 </tr>
										 </table>
								</td>
							 </tr>
							</table>
						</fieldset>
						<br />

						<fieldset class='formmain-fieldset'>
						    <legend><strong>Faction Description</strong></legend>			
						<table cellpadding='0' cellspacing='0' border='0' width='100%'>
							 <tr>
							   <td width='100%' class='tablerow2'>
							<textarea name="factiondesc" cols="70" rows="15">{$bbfactiondesc}</textarea>
							</td>
							  </tr>
							 </table>		
							</fieldset>
						</td>

					</tr>
					</table>
				</div>

				<div align='center' class='tablefooter'>
				 	<div class='formbutton-wrap'>
				 		<div id='button-save'>
				<input type="submit" name="submit" value="UPDATE" class='realbutton' />
				</div>
					</div>
				</div>
				</div>
				</form>
EOF;
	}
}

//==================================================
//				List the units
//==================================================
function show_units($gameid) {
	global $zone;
	
	$units .= <<<EOF
			<div class='tableborder'>
			 <div class='tableheaderalt'>Units</div>
			 <table cellpadding='4' cellspacing='0' border='0' width='100%'>
EOF;
	$per_row  = 3;
	$td_width = 100 / $per_row;
	$count    = 0;
	$units   .= "<tr align='center'>\n";

	$zone->DB->query("SELECT *
		FROM portal_game_units
		WHERE unitgameid = '$gameid'
		");
		while ($r = $zone->DB->fetch_row($query)) {
			$count++;
			
			$unitdesc = $r['unitdesc'];
			$bbunitdesc = $zone->html2bbcode($unitdesc);

$units .= <<<EOF
				<td width='{$td_width}%' align='left' style='background-color:#F1F1F1;padding:6px;'>
				  <fieldset>
				  	<legend><strong>{$r['unitname']}</strong></legend>
				  	<div style='border:1px solid #BBB;background-color:#EEE;margin:2px;padding:1px'>
				  	<table cellpadding='4' cellspacing='0' border='0' width='100%'>
				  	<tr>
				  	 <td width='1%' align='center'><img src="{$r['unitimage']}" width="36px"/></td>
				  	 <td width='99%'>
				  	  <a style='font-size:12px;font-weight:bold' title='Edit Unit' href='?section=games&page=editunit&unitid={$r['unitid']}'>{$r['unitname']}</a>
				  	  &nbsp;(<strong>
					<a href="?section=games&page=editunit&unitid={$r['unitid']}">Edit</a></strong>)
				  	 </td>
				  	</tr>
				    </table>
				   </div>
EOF;
		if ($count == $per_row )
		{
			$units .= "</tr>\n\n<tr align='center'>";
			$count   = 0;
		}
	}
	
	if ( $count > 0 and $count != $per_row )
	{
		for ($i = $count ; $i < $per_row ; ++$i)
		{
			$units .= "<td class='tablerow2'>&nbsp;</td>\n";
		}
		
		$units .= "</tr>";
	}
	$zone->acp->html .= $units;
	$zone->acp->html .= "</table></div><br />";
}

//=================================================
//				Edit unit
//=================================================
function edit_unit($unitid) {
	global $zone;
	if(isset($_POST['submit'])) {
		$unitname = $zone->makesafe($_POST['unitname']);
		$unitimage = $_POST['unitimage'];
		$unitgameid = $_POST['unitgameid'];
		$usafe = $zone->makesafe($_POST['unitdesc']);
		$unitdesc = $zone->bbcode2html($usafe);
		$unitid = $_POST['unitid'];
		$factionid = $_POST['factionid'];
		$zone->DB->query("UPDATE portal_game_units
			SET 
			unitname = '$unitname', 
			unitimage = '$unitimage',
			unitdesc = '$unitdesc',
			unitgameid = '$unitgameid',
			factionid = '$factionid'
			WHERE unitid = '$unitid'");
			$zone->boink_it($url="?section=games&page=editunit&unitid=$unitid",$msg="Unit edited...");
		}
		else {	
	$zone->DB->query("SELECT *
		FROM portal_game_units
		WHERE unitid = '$unitid'
			");
	while ($r = $zone->DB->fetch_row($query)) {
	$bbunitdesc = $zone->html2bbcode($r['unitdesc']);
	$zone->acp->html .= <<<EOF
		<form name="updatefaction" method="POST" action="{$PHP_SELF}">
		<input type="hidden" name="unitid" value="{$r['unitid']}" />
		<div class='tabclear'>Editing: {$r['unitname']} 
		<span style='font-weight:normal'>(ID: {$r['unitid']})</span></div>
		<div class='tableborder'>
		<div class='formmain-background'>
			<table cellpadding='0' cellspacing='0' border='0' width='100%'>
			 <tr>
		   <td>
			<fieldset class='formmain-fieldset'>
		    <legend><strong>{$r['unitname']}</strong></legend>
			<table cellpadding='0' cellspacing='0' border='0' width='100%'>
			 <tr>
			<td width='1%' class='tablerow1'>
			<div style='border:1px solid #000;background:#FFF;width:150px; padding:10px'>
			<img src="{$r['unitimage']}"/>	
			</div>
			<br />
			<input type="text" name="unitimage" value="{$r['unitimage']}" />
			</td>
			<td>
			  <table cellpadding='0' cellspacing='0' border='0' width='100%'>
				<tr>
				  <td width='40%' class='tablerow1'><strong>Unit Name</strong></td>
				   <td width='60%' class='tablerow2'>
					<input type="text" name="unitname" value="{$r['unitname']}" />	
					  </td>
				  </tr>
				  <tr>
				<td width='40%' class='tablerow1'><strong>Game</strong></td>
				<td width='60%' class='tablerow2'>
				<select name="unitgameid">
				<option value="{$r['unitgameid']}">{$r['unitgamename']}</option>
				</td>
				 </tr>
				  <tr>
					<td width='40%' class='tablerow1'><strong>Faction</strong></td>
					<td width='60%' class='tablerow2'>
					<select name='factionid'  class='dropdown'>
					<option value=''>Select faction</option>
					<option value=''>Red Alert - Allies</option>
					<option value=''>Red Alert - Soviet</option>
					<option value='6'>Tiberian Sun - NOD</option>
					<option value='7'>Tiberian Sun - GDI</option>
					<option value='10'>Red Alert 2 - Allies</option>
					<option value='9'>Red Alert 2 - Soviet</option>
					<option value='8'>Red Alert 2 - Yuri</option>
					<option value=''>Renegade - NOD</option>
					<option value=''>Renegade - GDI</option>
					<option value='4'>Tiberium Wars - GDI</option>
					<option value='3'>Tiberium Wars - NOD</option>
					<option value='5'>Tiberium Wars - Scrin</option>
					<option value='1'>Red Alert 3 - Allies</option>
					<option value='2'>Red Alert 3 - Soviet</option>
					</select>
					</td>
					</tr>
					</table>
					</td>
				 </tr>
			</table>
		</fieldset>
	<br />
<fieldset class='formmain-fieldset'>
	    <legend><strong>Unit Description</strong></legend>			
		<table cellpadding='0' cellspacing='0' border='0' width='100%'>
		 <tr>
		   <td width='100%' class='tablerow2'>
			<textarea name="unitdesc" cols="70" rows="15">{$bbunitdesc}</textarea>
			</td>
			  </tr>
			 </table>		
			</fieldset>
			</td>
			</tr>
			</table>
			</div>
			<div align='center' class='tablefooter'>
		 	<div class='formbutton-wrap'>
	 		<div id='button-save'>
		<input type="submit" name="submit" value="UPDATE" class='realbutton' />
		</div>
		</div>
		</div>
		</div>
		</form>
EOF;
			}
		}
}
//=================================================
//				List the structures
//=================================================

function show_structures($gameid) {
	global $zone;
	
	$units .= <<<EOF
			<div class='tableborder'>
			 <div class='tableheaderalt'>Structures</div>
			 <table cellpadding='4' cellspacing='0' border='0' width='100%'>
EOF;
	$per_row  = 3;
	$td_width = 100 / $per_row;
	$count    = 0;
	$units   .= "<tr align='center'>\n";

	$zone->DB->query("SELECT *
		FROM portal_game_structures
		WHERE structuregameid = '$gameid'
		");
		while ($r = $zone->DB->fetch_row($query)) {
			$count++;
			
			$structuresdesc = $r['structuresdesc'];
			$bbstructuresdesc = $zone->html2bbcode($structuresdesc);

$units .= <<<EOF
				<td width='{$td_width}%' align='left' style='background-color:#F1F1F1;padding:6px;'>
				  <fieldset>
				  	<legend><strong>{$r['structurename']}</strong></legend>
				  	<div style='border:1px solid #BBB;background-color:#EEE;margin:2px;padding:1px'>
				  	<table cellpadding='4' cellspacing='0' border='0' width='100%'>
				  	<tr>
				  	 <td width='1%' align='center'><img src="{$r['structureimage']}" width="36px"/></td>
				  	 <td width='99%'>
				  	  <a style='font-size:12px;font-weight:bold' title='Edit Unit' href='?section=games&page=editunit&unitid={$r['structureid']}'>{$r['structurename']}</a>
				  	  &nbsp;(<strong>
					<a href="?section=games&page=editstructure&structureid={$r['structureid']}">Edit</a></strong>)
				  	 </td>
				  	</tr>
				    </table>
				   </div>
EOF;
		if ($count == $per_row )
		{
			$units .= "</tr>\n\n<tr align='center'>";
			$count   = 0;
		}
	}
	
	if ( $count > 0 and $count != $per_row )
	{
		for ($i = $count ; $i < $per_row ; ++$i)
		{
			$units .= "<td class='tablerow2'>&nbsp;</td>\n";
		}
		
		$units .= "</tr>";
	}
	$zone->acp->html .= $units;
	$zone->acp->html .= "</table></div><br />";
}


//===================================================
//				edit structures
//===================================================
function edit_structure($structureid) {
	global $zone;
	if(isset($_POST['submit'])) {
		$structurename = $zone->makesafe($_POST['structurename']);
		$structureimage = $_POST['structureimage'];
		$structuregameid = $_POST['structuregameid'];
		$ssafe = $zone->makesafe($_POST['structuredesc']);
		$structuredesc = $zone->bbcode2html($ssafe);
		$structureid = $_POST['structureid'];
		$factionid = $_POST['factionid'];
		$zone->DB->query("UPDATE portal_game_structures
			SET 
			structurename = '$structurename', 
			structureimage = '$structureimage',
			structuredesc = '$structuredesc',
			structuregameid = '$structuregameid',
			factionid = '$factionid'
			WHERE unitid = '$unitid'");
			$zone->boink_it($url="?section=games&page=editstructure&structureid=$structureid",$msg="Structure edited...");
		}
		else {	
	$zone->DB->query("SELECT *
		FROM portal_game_structures
		WHERE structureid = '$structureid'
			");
	while ($r = $zone->DB->fetch_row($query)) {
	$bbstructuredesc = $zone->html2bbcode($r['structuredesc']);
	$zone->acp->html .= <<<EOF
		<form name="updatestructure" method="POST" action="{$PHP_SELF}">
		<input type="hidden" name="structureid" value="{$r['structureid']}" />
		<div class='tabclear'>Editing: {$r['structurename']} 
		<span style='font-weight:normal'>(ID: {$r['structureid']})</span></div>
		<div class='tableborder'>
		<div class='formmain-background'>
			<table cellpadding='0' cellspacing='0' border='0' width='100%'>
			 <tr>
		   <td>
			<fieldset class='formmain-fieldset'>
		    <legend><strong>{$r['structurename']}</strong></legend>
			<table cellpadding='0' cellspacing='0' border='0' width='100%'>
			 <tr>
			<td width='1%' class='tablerow1'>
			<div style='border:1px solid #000;background:#FFF;width:150px; padding:10px'>
			<img src="{$r['structureimage']}"/>	
			</div>
			<br />
			<input type="text" name="structureimage" value="{$r['structureimage']}" />
			</td>
			<td>
			  <table cellpadding='0' cellspacing='0' border='0' width='100%'>
				<tr>
				  <td width='40%' class='tablerow1'><strong>Unit Name</strong></td>
				   <td width='60%' class='tablerow2'>
					<input type="text" name="structurename" value="{$r['structurename']}" />	
					  </td>
				  </tr>
				  <tr>
				<td width='40%' class='tablerow1'><strong>Game</strong></td>
				<td width='60%' class='tablerow2'>
				<select name="structuregameid">
				<option value="{$r['structuregameid']}">{$r['structuregamename']}</option>
				</td>
				 </tr>
				  <tr>
					<td width='40%' class='tablerow1'><strong>Faction</strong></td>
					<td width='60%' class='tablerow2'>
					<select name='factionid'  class='dropdown'>
					<option value=''>Select faction</option>
					<option value=''>Red Alert - Allies</option>
					<option value=''>Red Alert - Soviet</option>
					<option value='6'>Tiberian Sun - NOD</option>
					<option value='7'>Tiberian Sun - GDI</option>
					<option value='10'>Red Alert 2 - Allies</option>
					<option value='9'>Red Alert 2 - Soviet</option>
					<option value='8'>Red Alert 2 - Yuri</option>
					<option value=''>Renegade - NOD</option>
					<option value=''>Renegade - GDI</option>
					<option value='4'>Tiberium Wars - GDI</option>
					<option value='3'>Tiberium Wars - NOD</option>
					<option value='5'>Tiberium Wars - Scrin</option>
					<option value='1'>Red Alert 3 - Allies</option>
					<option value='2'>Red Alert 3 - Soviet</option>
					</select>
					</td>
					</tr>
					</table>
					</td>
				 </tr>
			</table>
		</fieldset>
	<br />
<fieldset class='formmain-fieldset'>
	    <legend><strong>Unit Description</strong></legend>			
		<table cellpadding='0' cellspacing='0' border='0' width='100%'>
		 <tr>
		   <td width='100%' class='tablerow2'>
			<textarea name="structuredesc" cols="70" rows="15">{$bbstructuredesc}</textarea>
			</td>
			  </tr>
			 </table>		
			</fieldset>
			</td>
			</tr>
			</table>
			</div>
			<div align='center' class='tablefooter'>
		 	<div class='formbutton-wrap'>
	 		<div id='button-save'>
		<input type="submit" name="submit" value="UPDATE" class='realbutton' />
		</div>
		</div>
		</div>
		</div>
		</form>
EOF;
			}
		}
}
//--------------------------------------------
//				Add unit
//--------------------------------------------

		function add_unit($gameid) {
					global $zone;
					if(isset($_POST['submit'])) {
						$unitname = $zone->makesafe($_POST['unitname']);
						$unitimage = $_POST['unitimage'];
						$unitdesc = $zone->makesafe($_POST['unitdesc']);
						$unitgameid = $_POST['unitgameid'];
						$factionid = $_POST['factionid'];
	$zone->DB->query("INSERT INTO portal_game_units (unitname,unitdesc,unitimage,unitgameid,factionid) 
					VALUES('$unitname','$unitdesc','$unitimage','$unitgameid','$factionid')");
					$zone->boink_it($url=$_SERVER['HTTP_referer'],$msg="Unit Added...");
					}
					else {
			$zone->acp->html .= <<<EOF
	<div class='tableborder'>
<form action="{$PHP_SELF}" method="POST">
<input type="hidden" name="unitgameid" value="{$gameid}">
<div class='tableheaderalt'>Add Unit</div>
	<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'>
	<tr>
<td class='tablerow1'  width='40%'  valign='middle'><b>Unit Name</b></td>
<td class='tablerow2'  width='60%'  valign='middle'>
<input type="text" name="unitname" size='30' class='textinput'>
</td>
	</tr>
	<tr>
	<td class='tablerow1' width='40%'  valign='middle'><b>Unit Image</b></td>
	<td class='tablerow2' width='60%'  valign='middle'>
	<input type="text" name="unitimage" size='30' class='textinput'>
	</td>
	</tr>
					
					<tr>
					<td class='tablerow1'  width='40%'  valign='middle'><b>Unit Description</b><div style='color:gray;'></div></td>
					<td class='tablerow2'  width='60%'  valign='middle'>
					<textarea name='unitdesc' cols='60' rows='5' wrap='soft' class='multitext'></textarea></td>
					</tr>
					<tr>
					<td class='tablerow1'  width='40%'  valign='middle'><b>Faction</b><div style='color:gray;'></div></td>
					<td class='tablerow2'  width='60%'  valign='middle'>
					<select name='factionid'  class='dropdown'>
					<option value=''>Select faction</option>
					<option value=''>Red Alert - Allies</option>
					<option value=''>Red Alert - Soviet</option>
					<option value='6'>Tiberian Sun - NOD</option>
					<option value='7'>Tiberian Sun - GDI</option>
					<option value='10'>Red Alert 2 - Allies</option>
					<option value='9'>Red Alert 2 - Soviet</option>
					<option value='8'>Red Alert 2 - Yuri</option>
					<option value=''>Renegade - NOD</option>
					<option value=''>Renegade - GDI</option>
					<option value='4'>Tiberium Wars - GDI</option>
					<option value='3'>Tiberium Wars - NOD</option>
					<option value='5'>Tiberium Wars - Scrin</option>
					<option value='1'>Red Alert 3 - Allies</option>
					<option value='2'>Red Alert 3 - Soviet</option>
					</select>
					</td>
					</tr>
					</table></div><br />
					<br /><div class='tableborder'><div align='center' class='tablesubheader'>
					<input type='submit' name="submit" value='Add Unit' class='realbutton'></div></div>
					</form>

EOF;
	}
}

//--------------------------------------------
//				Add game
//--------------------------------------------

function add_game() {
	global $zone;
	if(isset($_POST['submit'])) {
		$gamename = $zone->makesafe($_POST['gamename']);
		$gameimage = $_POST['gameimage'];
		$gamedesc = $zone->makesafe($_POST['gamedesc']);
		$gamepublisher = $_POST['gamepublisher'];
		$gamerelease = $_POST['gamerelease'];
		$gamereq = $_POST['gamereq'];
		$gameweb = $_POST['gameweb'];
		
			$zone->DB->query("INSERT INTO portal_games (gamename,gamedesc,gameimage,gamepublisher,gamerelease,gamereq,gameweb) 									VALUES('$gamename','$gamedesc','$gameimage','$gamepublisher','$gamerelease','$gamereq','$gameweb')");
$zone->boink_it($url="?section=games",$msg="Game Added...");
	}
	else {
$zone->acp->html .= <<<EOF
	<div class='tableborder'>
	<form action="{$PHP_SELF}" method="POST">
		<div class='tableheaderalt'>Add Game</div>
		<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'>
		<tr>
		<td class='tablerow1'  width='40%'  valign='middle'><b>Game Title</b></td>
		<td class='tablerow2'  width='60%'  valign='middle'>
		<input type="text" name="gamename" size='30' class='textinput'>
		</td>
		</tr>
		<tr>
		<td class='tablerow1' width='40%'  valign='middle'><b>Game Image</b></td>
		<td class='tablerow2' width='60%'  valign='middle'>
		<input type="text" name="gameimage" size='30' class='textinput'>
		</td>
		</tr>
		<tr>
		<td class='tablerow1' width='40%'  valign='middle'><b>Offical Website</b></td>
		<td class='tablerow2' width='60%'  valign='middle'>
		<input type="text" name="gameweb" size='30' class='textinput'>
		</td>
		</tr>
		<tr>
		<td class='tablerow1' width='40%'  valign='middle'><b>Game Publisher</b></td>
		<td class='tablerow2' width='60%'  valign='middle'>
		<input type="text" name="gamepublisher" size='30' class='textinput'>
		</td>
		</tr>
		<tr>
		<td class='tablerow1' width='40%'  valign='middle'><b>Game Release date</b></td>
		<td class='tablerow2' width='60%'  valign='middle'>
		<input type="text" name="gamerelease" size='30' class='textinput'>
		</td>
		</tr>
		<tr>
		<td class='tablerow1'  width='40%'  valign='middle'><b>Game Description</b><div style='color:gray;'></div></td>
		<td class='tablerow2'  width='60%'  valign='middle'>
		<textarea name='gamedesc' cols='60' rows='5' wrap='soft' class='multitext'></textarea></td>
		</tr>
		<tr>
		<td class='tablerow1'  width='40%'  valign='middle'><b>Game Requirements</b><div style='color:gray;'></div></td>
		<td class='tablerow2'  width='60%'  valign='middle'>
		<textarea name='gamereq' cols='60' rows='5' wrap='soft' class='multitext'></textarea></td>												
		</tr>
		</table></div><br />
		<br /><div class='tableborder'><div align='center' class='tablesubheader'>
		<input type='submit' name="submit" value='Add Game' class='realbutton'></div></div>
	</form>
EOF;
	}
}

//--------------------------------------------
//				Add faction
//--------------------------------------------

function add_faction() {
global $zone;
	if(isset($_POST['submit'])) {
		$factionname = $zone->makesafe($_POST['factionname']);
		$factionimage = $_POST['factionimage'];
		$factiondesc = $zone->makesafe($_POST['factiondesc']);
		$factiongameid = $_POST['factiongameid'];
		
		$zone->DB->query("SELECT gamename FROM portal_games WHERE gameid='$factiongameid' ");
		$r = $zone->DB->fetch_row($query);
		$factiongamename = $r['gamename'];
		$zone->DB->query("INSERT INTO portal_game_factions (factionname,factiondesc,factionimage,factiongamename,factiongameid) 																	VALUES('$factionname','$factiondesc','$factionimage','$factiongamename','$factiongameid')");
		$zone->boink_it($url="?section=games&page=factions",$msg="Faction Added...");	
	}
	else {
	$zone->acp->html .= <<<EOF
		<div class='tableborder'>
	
<form action="{$PHP_SELF}" method="POST">
		<div class='tableheaderalt'>Add Faction</div>
		<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'>
					<tr>
			<td class='tablerow1'  width='40%'  valign='middle'><b>Faction Name</b></td>
			<td class='tablerow2'  width='60%'  valign='middle'>
			<input type="text" name="factionname" size='30' class='textinput'>
			</td>
			</tr>
			<tr>
			<td class='tablerow1' width='40%'  valign='middle'><b>Faction Image</b></td>
			<td class='tablerow2' width='60%'  valign='middle'>
			<input type="text" name="factionimage" size='30' class='textinput'>
			</td>
			</tr>

			<tr>
			<td class='tablerow1'  width='40%'  valign='middle'><b>Faction Description</b><div style='color:gray;'></div></td>
			<td class='tablerow2'  width='60%'  valign='middle'>
			<textarea name='factiondesc' cols='60' rows='5' wrap='soft' class='multitext'></textarea></td>
			</tr>
			<tr>
			<td class='tablerow1'  width='40%'  valign='middle'><b>Game</b><div style='color:gray;'></div></td>
			<td class='tablerow2'  width='60%'  valign='middle'>
			<select name="factiongameid">
EOF;
$zone->DB->query("SELECT gameid,gamename FROM portal_games");
while ($r = $zone->DB->fetch_row($query)) {
$zone->acp->html .= <<<EOF
<option value="{$r['gameid']}">{$r['gamename']}</option>
EOF;
}
$zone->acp->html .= <<<EOF
</select>
	</td>
	</tr>
		</table></div><br />
		<br /><div class='tableborder'><div align='center' class='tablesubheader'>
		<input type='submit' name="submit" value='Add Faction' class='realbutton'></div></div>
		</form>
EOF;
		}
}
//--------------------------------------------
//				Add structure
//--------------------------------------------

function add_structure($gameid) {
	global $zone;
	if(isset($_POST['submit'])) {
	$structurename = $zone->makesafe($_POST['structurename']);
	$structureimage = $_POST['structureimage'];
	$structuredesc = $zone->makesafe($_POST['structuredesc']);
	$structuregameid = $_POST['structuregameid'];
	$factionid = $_POST['factionid'];
	$zone->DB->query("INSERT INTO portal_game_structures (structurename,structuredesc,structureimage,structuregameid,factionid) 							VALUES('$structurename','$structuredesc','$structureimage','$structuregameid','$factionid')");
$zone->boink_it($url="?section=games&page=structures&gameid=$gameid",$msg="Structure Added...");
		}
		else {
$zone->acp->html .= <<<EOF
	<div class='tableborder'>

	<form action="{$PHP_SELF}" method="POST">
	<input type="hidden" name="structuregameid" value="{$gameid}">
	<div class='tableheaderalt'>Add structure</div>
	<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'>
	<tr>
	<td class='tablerow1'  width='40%'  valign='middle'><b>structure Name</b></td>
	<td class='tablerow2'  width='60%'  valign='middle'>
	<input type="text" name="structurename" size='30' class='textinput'>
	</td>
	</tr>
	<tr>
	<td class='tablerow1' width='40%'  valign='middle'><b>structure Image</b></td>
	<td class='tablerow2' width='60%'  valign='middle'>
	<input type="text" name="structureimage" size='30' class='textinput'>
	</td>
	</tr>
	<tr>
	<td class='tablerow1'  width='40%'  valign='middle'><b>structure Description</b><div style='color:gray;'></div></td>
	<td class='tablerow2'  width='60%'  valign='middle'>
	<textarea name='structuredesc' cols='60' rows='5' wrap='soft' class='multitext'></textarea></td>
	</tr>
	<tr>
	<td class='tablerow1'  width='40%'  valign='middle'><b>Faction</b><div style='color:gray;'></div></td>
	<td class='tablerow2'  width='60%'  valign='middle'>
	<select name='factionid'  class='dropdown'>
	<option value=''>Select faction</option>
	<option value=''>Red Alert - Allies</option>
	<option value=''>Red Alert - Soviet</option>
	<option value='6'>Tiberian Sun - NOD</option>
	<option value='7'>Tiberian Sun - GDI</option>
	<option value='10'>Red Alert 2 - Allies</option>
	<option value='9'>Red Alert 2 - Soviet</option>
	<option value='8'>Red Alert 2 - Yuri</option>
	<option value=''>Renegade - NOD</option>
	<option value=''>Renegade - GDI</option>
	<option value='4'>Tiberium Wars - GDI</option>
	<option value='3'>Tiberium Wars - NOD</option>
	<option value='5'>Tiberium Wars - Scrin</option>
	<option value='1'>Red Alert 3 - Allies</option>
	<option value='2'>Red Alert 3 - Soviet</option>
	</select>
	</td>
	</tr>
	</table></div><br />
	<br /><div class='tableborder'><div align='center' class='tablesubheader'>
	<input type='submit' name="submit" value='Add structure' class='realbutton'></div></div>
	</form>
EOF;
	}
}

}
?>