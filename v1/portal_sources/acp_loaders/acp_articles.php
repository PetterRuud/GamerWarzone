<?php
//===========================================================================
// Simple library that holds all the links for the admin cp
//===========================================================================

// CAT_ID => array(  PAGE_ID  => (PAGE_NAME, URL ) )

// $PAGES[ $cat_id ][$page_id][0] = Page name
// $PAGES[ $cat_id ][$page_id][1] = Url
// $PAGES[ $cat_id ][$page_id][2] = Look for folder before showing
// $PAGES[ $cat_id ][$page_id][3] = URL type: 1 = Board URL 0 = ACP URL
// $PAGES[ $cat_id ][$page_id][4] = Item icon: 1= redirect 0 = Normal

$CATS[]  = array( 'Articles' );

$PAGES[] = array(
					 1 => array( 'View All Articles', 'section=articles&amp;page=view' ),
					 2 => array( 'Add Article'  , 'section=articles&amp;page=add' ),
				);			
require( PORTAL_PATH.'action_admin/ad_articles.php');
$zone->acp->content->auto_run();

?>