<?php

if (!defined('ROOT')) {
	define ('ROOT', dirname(__FILE__) . '/');
}

if (!defined('SKIN_PATH')) {
	define ('SKIN_PATH', dirname(__FILE__) . '/skin/');
}

define('PORTAL_NAME', 'Gamer Warzone');

//======================================================//
//					ZONE MAIN FRAMEWORK
//======================================================//
require( ROOT.'portal_sources/portal_class.php');
$zone =& new zone;
//======================================================//
//					DISPLAY
//======================================================//
require( PORTAL_PATH.'classes/display.php');
$echo = new class_display;
$zone->echo =& $echo;
//======================================================//
//					SKIN
//======================================================//
require( SKIN_PATH.'skin_global.php');
$skin_global = new skin_global;
$zone->skin_global =& $skin_global;
//
require( SKIN_PATH.'skin_news.php');
$skin_news = new skin_news;
$zone->skin_news =& $skin_news;
//
require( SKIN_PATH.'skin_articles.php');
$skin_articles = new skin_articles;
$zone->skin_articles =& $skin_articles;
//
require( SKIN_PATH.'skin_xwis.php');
$skin_xwis = new skin_xwis;
$zone->skin_xwis =& $skin_xwis;
//
require( SKIN_PATH.'skin_affiliates.php');
$skin_affiliates = new skin_affiliates;
$zone->skin_affiliates =& $skin_affiliates;
//
require( SKIN_PATH.'skin_games.php');
$skin_games = new skin_games;
$zone->skin_games =& $skin_games;
//
require( SKIN_PATH.'skin_ips.php');
$skin_ips = new skin_ips;
$zone->skin_ips =& $skin_ips;
//
require( SKIN_PATH.'skin_replays.php');
$skin_replays = new skin_replays;
$zone->skin_replays =& $skin_replays;
//======================================================//
//					ARTICLES
//======================================================//
require( PORTAL_PATH.'classes/articles.php');
$articles = new class_articles();
$zone->articles =& $articles;

//======================================================//
//					Replays
//======================================================//
require( PORTAL_PATH.'classes/replays.php');
$replays = new class_replays();
$zone->replays =& $replays;

//======================================================//
//					NEWS
//======================================================//
require( PORTAL_PATH.'classes/news.php');
$news = new class_news();
$zone->news =& $news;
//======================================================//
//					NEWS
//======================================================//
require( PORTAL_PATH.'classes/ipsearch.php');
$ipsearch = new class_ipsearch();
$zone->ipsearch =& $ipsearch;
//======================================================//
//					AFFILIATES
//======================================================//
require( PORTAL_PATH.'classes/affiliates.php');
$affiliates = new class_affiliates();
$zone->affiliates =& $affiliates;
//======================================================//
//					BATTLE
//======================================================//
require( PORTAL_PATH.'classes/battle.php'); 
$battle = new class_battle();
$zone->battle =& $battle;
//======================================================//
//					GAMES
//======================================================//
require( PORTAL_PATH.'classes/games.php');
$games = new class_games();
$zone->games =& $games;
//======================================================//
//					XWIS
//======================================================//
require( PORTAL_PATH.'classes/xwis.php');
$xwis = new class_xwis();
$zone->xwis =& $xwis;
//======================================================//
//					Players
//======================================================//
require( PORTAL_PATH.'classes/players.php');
$players = new class_players();
$zone->players =& $players;
//===========================================================================
// DEBUG 
//===========================================================================
class Timer {
	
	function startTimer(){
    global $starttime;
    $mtime = microtime ();
    $mtime = explode (' ', $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $starttime = $mtime;
	}
	function endTimer(){
    global $starttime;
    $mtime = microtime ();
    $mtime = explode (' ', $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $endtime = $mtime;
    $totaltime = round (($endtime - $starttime), 5);
    return $totaltime;
	}

}
?>