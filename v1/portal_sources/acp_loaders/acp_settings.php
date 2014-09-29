<?php
$CATS[]  = array( 'Settings' );

$PAGES[] = array(
					 1 => array( 'General Settings', 'section=settings&amp;page=set' ),
					 2 => array( 'Turn Portal Offline', 'section=settings&amp;page=offline' ),
				);			
require( PORTAL_PATH.'action_admin/ad_settings.php');

$zone->acp->content->auto_run();
?>
