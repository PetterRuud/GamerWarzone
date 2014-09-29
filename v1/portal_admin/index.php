<?php 
require('../portal_init.php');
// ACP
require( SKIN_PATH.'admin/acp_skin_global.php');
$acpskin = new acp_skin_global;
$zone->acpskin =& $acpskin;

$zone->skin_acp_url = $zone->ips->vars['board_url']."/skin_acp/IPB2_Standard";


require( PORTAL_PATH.'classes/class_acp.php');
$acp = new class_acp;
$zone->acp =& $acp;

//$timer->startTimer();
//checks if the user is in the allowed groups
if($zone->is_ingroup(array(4,18,48,18))) {
	
$zone->acp->output();

$zone->acpskin->global_footer(date("Y"));
}
else {
$zone->acpskin->log_in_form($query_string="", $message="", $name="");
}


?>
