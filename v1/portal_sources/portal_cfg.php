<?php

/**
 * The full qualified filesystem path to the folder of your IPB installation.
 * You must add a trailing slash.
 *
 * <b>Default path:</b> $_SERVER['DOCUMENT_ROOT'] . "/forum/"<br>
 * <b>Example path:</b> "/home/public_html/community/forums/"
 *
 * @global string
 * @see DOCUMENT_ROOT
 */
$root_path =$_SERVER['DOCUMENT_ROOT'] . "/forum/";

/**
 * The full qualified URL to your board without '/index.php'.
 * You must not add a trailing slash.
 *
 * <b>Default URL:</b> 'http://' . $_SERVER['HTTP_HOST'] . '/forum';
 * <b>Example URL:</b> "http://www.mydomain.com/community/forums";
 *
 * @global string
 */
$board_url = 'http://' . $_SERVER['HTTP_HOST'] . '/forum';

/**
 * The Default zone Language Pack.
 *
 * Language packs should be named lang_ipbzone_XX.php where 'XX' is the
 * language and be situated in the lib/ folder.
 * By default, this uses the "en" (English) language pack.
 *
 * @global string
 * @see IPBzone::set_language()
 */
$zonelang = 'en';

/**
 * Enable Caching of SQL Queries.
 *
 * It is strongly recommended you keep this on unless you want to use alot of SQL Queries :)
 *
 * @global string
 */
$allow_caching = '1';

?>