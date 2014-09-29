<?php
$ad_affiliates = new ad_affiliates;
$zone->acp->content =& $ad_affiliates;

class ad_affiliates {
/*
/********************************************************
/
/						AFFILIATES
/
/********************************************************
*/
function auto_run() {
	global $zone;
	
	$zone->acp->nav[] = array( $url,'Affiliates' );
		
	switch($_GET['page']) {
		case 'view' :
		$this->mod_aff();
		break;
		case "delete": 
		$this->delete_aff();
		break;
		case "edit": 
		$this->edit_aff();
		break;
		default :
		$this->mod_aff();
		break;
	}
}

//--------------------------------------------
//				DELETE AFFILIATE
//--------------------------------------------

function delete_aff () {
		global $zone;

		if ($zone->is_admin()) {

		if(isset($_POST['submit'])) {
		$affiliate_id=$_POST['affiliate_id'];
		$zone->DB->query("DELETE from portal_affiliate where affiliate_id='$affiliate_id'");
		print "Button deleted. <a href='?section=affiliates'>back</a>";
		}else{
		   $affiliate_id=$_GET['id']; //gets the id from URL
		   $zone->acp->html .= <<<EOF
			<div class='tableborder'>
			<div class='tableheaderalt'>Delete Affiliate</div>
				       	<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'>
				<tr>
				<form action='?section=affiliates&page=delete' method='post'>
				<input type='hidden' name='affiliate_id' value='$affiliate_id'>
				  <td class='tablesubheader'>Are you sure you want to delete this affiliate?</td>
				 <td colspan="6" align='center' class="tablesubheader">
				<input type="submit" name="submit" class='realbutton' value="DELETE">
				</td>
				</tr>
				</form>
				 </table>
				</div>
EOF;
		}
	} else { $zone->acp->html .= "no permissions";}
}

//--------------------------------------------
//				EDIT AFFILIATE
//--------------------------------------------

function edit_aff () {
		global $zone;

		if ($zone->is_admin()) {

		if(isset($_POST['submit']))	{
		   $affiliate_url=$_POST['affiliate_url'];
		   $affiliate_button=$_POST['affiliate_button'];
		   $affiliate_validated=$_POST['affiliate_validated'];
		   $affiliate_id=$_POST['affiliate_id'];
		   if(strlen($affiliate_url)<1) { 
		      $zone->acp->html .= "You did not enter a url.";
		   } else if(strlen($affiliate_button)<1) { 
		      $zone->acp->html .= "You did not enter an image.";
		    }  else {
				$zone->DB->query("UPDATE portal_affiliate SET affiliate_url='$affiliate_url', affiliate_button='$affiliate_button', affiliate_validated='$affiliate_validated' where affiliate_id='$affiliate_id'");
		      $zone->boink_it($url="?section=affiliates",$msg="Affiliate Edited...");
		    } } else {
		   $affiliate_id=$_GET['id']; //gets the id from URL
		$zone->DB->query("SELECT * from portal_affiliate where affiliate_id='$affiliate_id'");
		$r = $zone->DB->fetch_row($query);		
$zone->acp->html .= <<<EOF
	<div class='tableborder'>
	<div class='tableheaderalt'>Edit Affiliate</div>
		       	<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'>
		<tr>
		<form action="?section=affiliates&page=edit" method="post">
		   <input type="hidden" name="affiliate_id" value="{$affiliate_id}">
			         <td class='tablesubheader'>ID</td>
						<td class='tablesubheader'>Link</td>
						<td class='tablesubheader'>Button</td>
						
							<td class='tablesubheader'>Button link</td>
						<td class='tablesubheader'>Click</td>
						<td class='tablesubheader'>Validated</td>
						</tr>
						<tr>
						<td class='tablerow1'>{$r['affiliate_id']}</td>
						<td class='tablerow1'><input type="text" name="affiliate_url" value="{$r['affiliate_url']}" size="40"></td>
<td class='tablerow1'><img src="{$r['affiliate_button']}"></td>
						<td class='tablerow1' align="center"><input type="text" name="affiliate_button" value="{$r['affiliate_button']}" size="40"></td>
						<td class='tablerow1'>{$r['affiliate_hits']}</td>
						<td class='tablerow1'>
							<SELECT name="affiliate_validated">
							<OPTION value="{$r['affiliate_validated']}">Yes / No</OPTION>
							<OPTION value="0">No</OPTION>
							<OPTION value="1">Yes</OPTION>
							</SELECT>
							</td>
						</tr>
						<tr>
		   <td colspan="6" align='center' class="tablesubheader">
		<input type="submit" name="submit" class='realbutton' value="UPDATE">
		</td>
		</tr>
		</form>
		 </table>
		</div>
		<br />
		
EOF;
		 } 
		} else { $zone->acp->html .= "no permissions";}
	}
	
//--------------------------------------------
//				MOD AFFILIATE
//--------------------------------------------

	function mod_aff() {
		global $zone;
		if ($zone->is_admin()) {
		$aff = $zone->DB->query("SELECT * from portal_affiliate WHERE affiliate_validated='1' order by affiliate_hits desc");
		$waiting = $zone->DB->query("SELECT * from portal_affiliate WHERE affiliate_validated='0'");
		$zone->acp->html .= <<<EOF
			<div class='tableborder'>
		
			<div class='tableheaderalt'>Waiting For Approval</div>
			<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'>
				   <tr>
				   <td class='tablesubheader'>ID</td>
					<td class='tablesubheader'>Image</td>
					<td class='tablesubheader'>Link</td>
					<td class='tablesubheader'>Edit</td>
					<td class='tablesubheader'>Delete</td>
					</tr>
EOF;
		while($w = $zone->DB->fetch_row($wating)){ 
$zone->acp->html .= <<<EOF
	<tr>
	<td class='tablerow2'>{$w['affiliate_id']}</td>
	<td class='tablerow2' align="center">
	<img src="{$w['affiliate_button']}" width="88px" height="31px" alt="No Image"></td>
	<td class='tablerow2'>{$w['affiliate_url']}</td>
	<td class='tablerow2'><a href='?section=affiliates&page=edit&id={$w['affiliate_id']}'>Edit</a></td>
	<td class='tablerow2'><a href='?section=affiliates&page=delete&id={$w['affiliate_id']}'>Delete</a></td>
	</tr>
EOF;
		}
		$zone->acp->html .= <<<EOF
			 </table>
			</div>
			<br />
EOF;
$zone->acp->html .= <<<EOF
<div class='tableborder'>
<div class='tableheaderalt'>Current Affiliates</div>
	       <table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'>

	        <tr>
	         <td class='tablesubheader'>ID</td>
			<td class='tablesubheader'>Image</td>
			<td class='tablesubheader'>Link</td>
			<td class='tablesubheader'>Hits</td>
			<td class='tablesubheader'>Edit</td>
			<td class='tablesubheader'>Delete</td>
			</tr>
EOF;
	while($r = $zone->DB->fetch_row($aff)){ 
		
$zone->acp->html .= <<<EOF

	<tr>
	<td class='tablerow2'>{$r['affiliate_id']}</td>
	<td class='tablerow2' align="center">
	<img src='{$r['affiliate_button']}' border='0' width="88px" height="31px" alt="No Image"></td>
	<td class='tablerow2'>{$r['affiliate_url']}</td>
	<td class='tablerow2'>{$r['affiliate_hits']}</td>
	<td class='tablerow2'><a href='?section=affiliates&page=edit&id={$r['affiliate_id']}'>Edit</a></td>
	<td class='tablerow2'><a href='?section=affiliates&page=delete&id={$r['affiliate_id']}'>Delete</a></td>
	</tr>

EOF;
		 }
$zone->acp->html .= <<<EOF
	 </table>
	</div>
	<br />
EOF;
		}
		else { $zone->acp->html .= "no permissions"; }
	}
	
}
?>