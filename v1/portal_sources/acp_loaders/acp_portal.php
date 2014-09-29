<?php
$CATS[]  = array( 'Portal Home' );

$PAGES[] = array(
					 1 => array( 'Portal Home', 'section=portal&amp;page=view' ),
					 2 => array( 'Zone Framework Info'  , 'section=portal&amp;page=info' ),
				);			
require( PORTAL_PATH.'action_admin/ad_portal.php');

$zone->acp->content->auto_run();
?>
