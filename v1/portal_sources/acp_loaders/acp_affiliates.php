<?php
$CATS[]  = array( 'Affiliates' );

$PAGES[] = array(
					 1 => array( 'View All Affiliates', 'section=affiliates&amp;page=view' ),
					 2 => array( 'Add Affiliates'  , 'section=affiliates&amp;page=add' ),
				);			
require( PORTAL_PATH.'action_admin/ad_affiliates.php');

$zone->acp->content->auto_run();
?>
