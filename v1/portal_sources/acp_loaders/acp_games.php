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

$CATS[]  = array( 'Games' );

$PAGES[] = array(
					 1 => array( 'Game Overview', 'section=games' ),
					 2 => array( 'Add Game'  , 'section=games&page=addgame' ),
				);
$CATS[]  = array( 'Factions' );

$PAGES[] = array(
					 1 => array( 'Factions Overview', 'section=games&page=factions' ),
					 2 => array( 'Add Faction'  , 'section=games&page=addfaction' ),
				);
						
$CATS[]  = array( 'Units/structures' );

$PAGES[] = array(
				 1 => array( 'Tiberian Sun Units', 'section=games&page=units&gameid=3' ),
				 2 => array( 'Tiberian Sun Structures'  , 'section=games&page=structures&gameid=3' ),
				
				3 => array( 'Red Alert 2 Units'  , 'section=games&page=units&gameid=6' ),
				4 => array( 'Red Alert 2 Structures'  , 'section=games&page=structures&gameid=6' ),
				
				5 => array( 'Tiberium Wars Units'  , 'section=games&page=units&gameid=2' ),
				6 => array( 'Tiberium Wars Structures'  , 'section=games&page=structures&gameid=2' ),
				
				7 => array( 'Red Alert 3 Units'  , 'section=games&page=units&gameid=1' ),
				8 => array( 'Red Alert 3 Structures'  , 'section=games&page=structures&gameid=1' ),
			);			
require( PORTAL_PATH.'action_admin/ad_games.php');
$zone->acp->content->auto_run();

?>
