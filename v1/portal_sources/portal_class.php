<?php
/**
 * Working Directory before everything changes
 */
if (!defined('WRK_DIR')) {
	define ('WRK_DIR', getcwd());
}
/**
 * Full qualified path to the zone class file.
 */
if (!defined('PORTAL_PATH')) {
	define ('PORTAL_PATH', dirname(__FILE__) . '/');
}

/**
 * @global string
 * @name DOCUMENT_ROOT
 * @see PORTAL_PATH
 */
if (!defined('DIRECTORY_SEPARATOR')) {
	/**
	 * DIRECTORY_SEPARATOR is undefined in some versions of PHP 4, so we define it ourselfs if necessary.
	 */
	define('DIRECTORY_SEPARATOR', (substr(PHP_OS, 0, 3) == 'WIN') ? '\\' : '/');
}
if (!isset($_SERVER['DOCUMENT_ROOT'])) {
	if(isset($_SERVER['PATH_INFO'])) {
		$_SERVER['DOCUMENT_ROOT'] = str_replace($_SERVER['PATH_INFO'], '', str_replace("\\\\", DIRECTORY_SEPARATOR, $_SERVER['PATH_TRANSLATED'] ) );
	}
}
if (!defined('PATH_SEPARATOR')) {
	/**
	 * PATH_SEPARATOR is undefined in some versions of PHP 4, so we define it ourselfs if necessary.
	 */
	define('PATH_SEPARATOR', (substr(PHP_OS, 0, 3) == 'WIN') ? ';' : ':');
}
// Add the zone Path to the include path.
// On some servers ini_set() might be disabled for some
// paranoid reasons so we try set_include_path() first
// thanx to the forxer.net for accidently pointing this out :)
if (function_exists('set_include_path')) {
	set_include_path(get_include_path() . PATH_SEPARATOR . PORTAL_PATH);
} else {
	// if this won't work, we have to live without PORTAL_PATH in the include_path
	if (strpos(@ini_get('disable_functions'), 'ini_set') === FALSE ) {
		ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . PORTAL_PATH);
	}
}

if (!defined('IN_IPB')) {
	/**
	 * Placeholder for ipsclass var
	 * @var object $ipsclass
	 */
	$ipsclass = new stdClass();
	/**
	 * Placeholder for what's in IPB's configuration file config_global.php
	 *
	 * @global array $INFO
	 */
	$INFO = array();
}

/**
 * The Main class wrapps all the functionality of the IPB zone library and extensions modules.
 *
 * Usage example:
 * <code>require_once('zone/zone_class.php');
 * $zone =& new zone();
 * $zone->zone_info();
 * </code>
 *
 * @package zone
 */
class zone {
	/**
	 * @access protected
	 * @var string
	 */
	var $zone_version = '1.7.3';
	/**
	 * Configuration settings, many comming from the config file and changed on initialization.
	 * @var array
	 */
	var $zone_settings = array();
	/**
	 * Current language code used for localization. Language files are located in the <var>lib/</var> subfolder of {@link PORTAL_PATH}
	 * @var string
	 */
	var $lang;
	/**
	 * The full qualified URL to your board without '/index.php'.
	 * It must not end with a trailing slash.
	 *
	 * <b>Default URL:</b> 'http://' . $_SERVER['HTTP_HOST'] . '/forum';
	 * @var string a backup
	 */
	var $board_url;
	/**
	 * A useful object, this will be... the internal ips superclass!
	 * @access protected
	 * @var object
	 */
	var $ips;
	/**
	 * An internal copy of the database connection from IPB. Created from the mySQL driver class.
	 * @access protected
	 * @var object
	 */
	var $DB;
	/**
	 * An internal object used for parsing any postness necessary.
	 * @access protected
	 * @var object
	 */
	var $parser;
	/**
	 * Extra libraries and object references most taken from what's in IPB /sources/.
	 * @var array
	 */
	var $extra = array();
	/**
	 * Last known member data from {@link get_info()}, {@link get_advinfo()}
	 * @var array
	 */
	var $member;
	/**
	 * Last known member ID from {@link get_info()}, {@link get_advinfo()}
	 * @var string
	 */
	var $memberid = '';
	/**
	 * Whether the member is logged in ('0', '1')
	 * @var string
	 */
	var $loggedin = '0';
	/**
	 * Modules Placeholder
	 * @access protected
	 * @var array
	 */
	var $modules = array();
	/**
	 * The working directory before the zone messes with it.
	 * @var string
	 */
	var $workingdir = '';

	// -- private properties go here -----------------
	/**#@+
 	* @access private
 	*/
	/**
	 * Runtime options
	 */
	var $_options = array();
	/**
	 * List of last error occured in both IPB and the zone.
	 */
	var $_errors = array();
	/**
	 * Last error occured in any of IPB and the zone.
	 */
	var $_lasterror;
	/**
	 * Cached SQL query results, used if {@link $allow_caching} is on.
	 */
	var $_cache = array();
	/**
	 * This is what's allowed to come via $options, other keys are ignored (see constructor)
	 */
	var $_allowedOptions = array(
			'root_path'=>'root_path',
			'board_url'=>'board_url',
			'language'=>'zonelang',
			'board_version'=>'board_version',
			'allow_caching'=>'allow_caching',
			'timer'=>'timer',
			'debug'=>'debug');
	/**
	 * This is an array of the IPB class locations
	 */
	var $_ipbModuleLocations = array(
			'print'=>'display',
			'sess'=>'session',
			'forums'=>'forums');
	/**
	 * This is an array of the IPB class names ('cause Matt has weird naming conventions)
	 */
	var $_ipbModuleNames = array(
			'print'=>'display',
			'sess'=>'session',
			'forums'=>'forum_functions');

	/**
	 * @ignore
	 * this one's temporary until support for the deprecated methods is dropped :)
	 */
	var $_depr_msg = '<b>NOTICE:</b> Use of deprecated method: "<b>%s</b>"! This method may not be supported in future versions of the IPB zone. Please update your scripts to use "<b>%s</b>" instead.';

	/**
	 * Class constructor.
	 * Does all the dirty work for IPB to run as smooth as possible.
	 *
	 * Initilaizes the database connection, loads the language pack, and does other nice tricks to easy your life as a PHP developer.
	 *
	 * @param array $options Use this to overwrite settings from the configuration file.
	 * @return object Instance of zone
	 */
	function zone($options = array('root_path' => '', 'board_url' => '', 'zonelang' => '', 'board_version' =>'', 'allow_caching'=>'', 'timer'=>'', 'debug'=>'')) {
		// Board vars, zone Settings etc. No $ibforums!!!
		global $INFO,$ipsclass;

		// save working dir before continuing.
		// load zone configuration
		$config = $this->load_config();
		foreach (array_keys($options) as $k) {
			if (empty($options[$k])) {
				$options[$k] = @$config[$k];
			}
		}

		// argh. design errors always persist ;-)
		if(array_key_exists('zonelang',$options)) {
			$this->_options['language'] = $options['zonelang'];
		}
		
		// assign IPB zone related settings
		// $zone_settings is always for the boards settings
		// Meanwhile $_options can be runtime ones, too.
		// $_options should be used everywhere else.
		$this->zone_settings['board_url'] 	= $config['board_url'];
		$this->zone_settings['root_path'] 	= $config['root_path'];
		$this->zone_settings['allow_caching'] = $config['allow_caching'];
		$this->zone_settings['zonelang']   	= $config['zonelang'];
		$this->board_url = $config['board_url'];

		// Put runtime options in $this->_options
		// E_ALL compliant ;)
		$this->_options['board_url'] 	= (isset($options['board_url'])) ? $options['board_url'] : $config['board_url'];
		$this->_options['board_path']	= (isset($options['root_path'])) ? $options['root_path'] : $config['root_path'];

		// for BC: global out some settings
		$GLOBALS['board_url'] = $this->_options['board_url'];
		$GLOBALS['root_path'] = $this->_options['board_path'];

		if (!defined('ROOT_PATH')) {
			define('ROOT_PATH', $this->_options['board_path']);
		}
		if (!defined('KERNEL_PATH')) {
			define( 'KERNEL_PATH', ROOT_PATH.'ips_kernel/' );
		}
		if (!defined('USE_SHUTDOWN')) {
			define( 'USE_SHUTDOWN', 1);
		}
		// Now: shut up!
		// swallows all the annoying notices & warnings of IPB
		// this is because Matt doesn't want to make IPB E_ALL compliant
		ob_start();

		if (defined('IN_IPB')) {
			// Reference the superclass
			$this->ips =& $ipsclass;

		} else {
			// As of beta 4, we've reverted to not using the API,
			// as it was constructed with the goal of er... only
			// being called once, which is no good for us...
			// Well, it should be good enough, but people tend
			// to instantiate the class more than once :P
			// Start loading IPB Classes

			require_once ROOT_PATH   . "init.php";
			require_once ROOT_PATH   . "sources/action_public/xmlout.php";
			require_once ROOT_PATH   . "sources/ipsclass.php";
			require_once PORTAL_PATH	 . "lib/portal_ipsclass.php";
			require_once KERNEL_PATH . "class_converge.php";
			require_once ROOT_PATH   . "conf_global.php";
			
			// board version
			$this->zone_settings['board_version'] = IPBVERSION;
			$this->_options['board_version'] = IPBVERSION;
			$this->zone_settings['board_long_version'] = IPB_LONG_VERSION;
			$this->_options['board_long_version'] = IPB_LONG_VERSION;
			
			// Initialise basics
			$this->ips   	=& new zone_ipsclass();
			$this->ips->vars = $INFO;
			$this->ips->init_db_connection();

			$this->load_ipb_module(array('sess','print','forums'));
			$this->ips->converge = new class_converge( $this->ips->DB );
			$this->ips->parse_incoming();

			// Load functionality
			$this->ips->cache_array = array('rss_calendar', 'rss_export','components','banfilters', 'settings', 'group_cache', 'systemvars', 'skin_id_cache', 'forum_cache', 'moderators', 'stats', 'languages');
			$this->ips->init_load_cache( $this->ips->cache_array );
			$this->ips->initiate_ipsclass();

			// Populate member information
			$this->ips->member 	= $this->ips->sess->authorise();
			// Show last click
			$this->ips->lastclick  = $this->ips->sess->last_click;
			// Show last location
			$this->ips->location   = $this->ips->sess->location;
			// Assign session ids
			$this->ips->session_id = $this->ips->sess->session_id;
			$this->ips->my_session = $this->ips->sess->session_id;
			// some food for IPB
			$this->ips->vars['board_url'] = $this->_options['board_url'];

			$this->base_url = $this->_options['board_url'] . '/index.' . $INFO['php_ext'];

			//$this->_errors['IPB Related'] = (ob_get_length()) ? ob_get_contents() : 'Clean Run! WOW! Now try E_ALL ;-)';
			// Do you need a stopwatch?
			// This IPB Stopwatch is very special, but we won't bother
			// loading it unless it's requested as it's a waste of time.
			$options['timer'] = 0;
			if (isset($options['timer'])) {
				// Timer's disabled for now (Pita 13/09/2005)
				//ipb_set_timer();
				$this->_options['timer'] = '0';
			}
			/*
			info as of IPB 1.2 Forum (not 1.1.2 nor any ACP <g>)
			Praise the Creator (no, not 'Homer') there's no is_subclass_of()
			in the IPB code that verifies an object's origin ;-)
			we need to use $this->ips, as PHP won't update the simple $ibforum
			when using global (and which is a copy by then) until we're done and 'return'
			from this constructor.
			but the IPB functions already need the vars and props assigned in the next
			couple of steps.
			*/

			$this->zone_settings['board_version'] = $this->ips->version;
			$this->ips->vars['mail_queue_per_blob'] = '';

			// add/fix some of the always missing input keys; they'll be empty, but they exist
			settype($this->ips->input['act'], 'string');
			settype($this->ips->input['code'], 'string');
			settype($this->ips->input['s'], 'string');
			settype($this->ips->input['Privacy'], 'string');

			// strange fix: sometimes parse_incoming doesn't parse the ip address... we'll fix that :)

			if(!$this->ips->input['IP_ADDRESS'] | !isset($this->ips->input['IP_ADDRESS'])) {
				$this->ips->input['IP_ADDRESS'] = $_SERVER['REMOTE_ADDR'];
			}

			// Sort out the skin

			$this->ips->load_skin();

			// Lang

			if ($this->ips->vars['default_language'] == "") {
				$this->ips->vars['default_language'] = 'en';
			}

			// Use the newly acquired skin info to define a emo dir

			if(!isset($this->ips->vars['EMOTICONS_URL'])) {
				$this->ips->vars['EMOTICONS_URL'] = $this->board_url . '/style_emoticons/' . $this->ips->skin['_emodir'] .'/';
			}
			
			if(!isset($this->ips->vars['img_url'])) {
				$this->ips->vars['img_url'] = $this->board_url . '/style_images/' . $this->ips->skin['_imagedir'] .'/';
			}

			// Finally, register shutdown function...
			if ( USE_SHUTDOWN ) {
				chdir( ROOT_PATH );
				$ROOT_PATH = getcwd();

				register_shutdown_function( array( &$this->ips, 'my_deconstructor') );
			}
			// Some food for ipsclass
			$this->ips->vars['forum_cache_minimum'] = !isset($this->ips->vars['forum_cache_minimum']) ? $this->ips->vars['forum_cache_minimum'] : false;
			$this->ips->vars['no_cache_forums'] = !isset($this->ips->vars['no_cache_forums']) ? $this->ips->vars['no_cache_forums'] : false;
			$this->ips->no_print_header = false;
		}
		// Reference DB for internal requests
		$this->DB =& $this->ips->DB;

		// Load the BBCode parser for kicks
		require_once ROOT_PATH . "sources/handlers/han_parse_bbcode.php";
		$this->parser =& new parse_bbcode;
		$this->parser->ipsclass =& $this->ips;
		$this->parser->check_caches();

		// back in business, errors that follow belong to us :)
		$this->_errors['IPB Related'] = (ob_get_length()) ? ob_get_contents() : 'Clean Run! WOW! Now try E_ALL ;)';
		ob_end_clean();

		if (empty($this->ips->member['mgroup']) OR $this->ips->member['mgroup'] == $this->ips->vars['guest_group'] OR $this->ips->member['id'] == '0') {
			$this->ips->loggedin = 0;
		} else {
			$this->ips->loggedin = 1;
		}
		$this->loggedin = $this->ips->loggedin;

		// See what language pack. If it's unavailable, load default
		// zone Language Pack as in Settings. This can be changed later in the
		// main script so one can change it to the members setting or whatever.
		if (isset($options['language']) && $options['language']) {
			if (!$this->zone_set_language($options['language'])) {
				$this->zone_set_language($this->zone_settings['zonelang']);
			}
		} else {
			$this->zone_set_language($this->zone_settings['zonelang']);
		}
		$this->_options['language'] = $this->zone_settings['zonelang'];

		// change back to working dir
		chdir(WRK_DIR);

		require_once ROOT_PATH . "sources/lib/func_usercp.php";
		$this->usercp  		 =  new func_usercp();
		$this->usercp->class	=& $this;
		$this->usercp->ipsclass = $this->ips;

	} // function zone


	/**
	 * The Factory method will instatiate the requested Module and
	 * returns a reference to the newly create object.
	 *
	 * @param string $obj_class The module to be created
	 * @param array $options Possible runtime options used by the module.
	 * @return object Instance of requested module.
	 */
	function factory($obj_class, $options = array()) {
		// Load the module
		require_once PORTAL_PATH . 'lib/lib_' . $obj_class . '.php';
		$cls = 'zone_' . $obj_class;
		return new $cls($this, $options);
	}

	/**
	 * This method loads the required IPB class into memory... basically
	 * automating a task that looks a bit rubbish when listed...
	 *
	 * @param array $classes The module(s) to be loaded
	 * @return bool False on failure
	 */
	function load_ipb_module($classes=array()) {
		foreach($classes as $class) {
			require_once ROOT_PATH   . "sources/classes/class_".$this->_ipbModuleLocations[$class].".php";
			$this->ips->$class =& new $this->_ipbModuleNames[$class]();
			$this->ips->$class->ipsclass  =& $this->ips;
		}
		return true;
	}

	/**#@+
	 * @group Utilities
	 */
	/**
	 * Loads the configuration file and returns its settings as an associated array.
	 * If the config files is missing, some general, well guessed defaults are returned :)
	 *
	 * This method can be also called statically in your script using <code>$config = zone::load_config().</code>
	 * @return array
	 */
	function load_config() {
		$cfg = FALSE;

		if ( @include(PORTAL_PATH . 'portal_cfg.php') ) {
			$cfg = get_defined_vars();
		}

		// If we had no config file when try and guess it or if the path is wrong

		if (!$cfg OR !realpath($cfg['root_path'])) {
			$cfg = array();
			if (@$GLOBALS['INFO'] && @$GLOBALS['INFO']['board_url'] && @$GLOBALS['INFO']['base_dir']) {
				$board_url = $GLOBALS['INFO']['board_url'];
				$root_path = $GLOBALS['INFO']['base_dir'];
			} else {
				$board_url = (isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
				foreach(array('/forum','/forums','/board','/','/Forum','/Forums') as $f) {
					if (is_dir($_SERVER['DOCUMENT_ROOT'] . $f)) {
						$root_path = $_SERVER['DOCUMENT_ROOT'] . $f . DIRECTORY_SEPARATOR;
						$board_url = 'http://' . $board_url . $f;
						break;
					}
				}
			}
			$cfg['board_url'] 	= $board_url;
			$cfg['root_path'] 	= $root_path;
			$cfg['allow_caching'] = 1;
			$cfg['zonelang']   	= 'en';
			$cfg['board_version'] = 200;
		}

		$cfg['root_path'] = realpath($cfg['root_path']) . DIRECTORY_SEPARATOR;

		return $cfg;
	}

	/**
	* Debuggin helper routine. Writes the given variables $v to the output stream.
	* This method can be also called statically in your script using <code>zone::dbg_print(arguments).</code>
	*
	* If your PHP script runs in a webpage that also loads a stylesheet you may customize the
	* default visibility of the XMP element by adding <code>pre.DbgPrint xmp.DbgPrint { display:none; }</code>
	* to this page's stylesheet. To <em>visually</em> prevent any output by default use
	* <code>pre.DbgPrint { display:none; }</code>.
	* Note that this ONLY affects the visibility of the generated XMP element, it's content will
	* still exist in this page and contain sensible information!
	*
	* @param mixed $v The variable you may wish to debug
	* @param integer $lines The height of the debug area in CSS em units applied to the XMP element.
	* @param bool $rem Optional: short remark printed above the output. Tip: Use __FILE__ .' '. __LINE_ to locate and identify a particular call of dbg_print() more easily :)
	*
	*/
	function dbg_print($v, $lines=0, $rem=FALSE) {
		// a header-like section
		$click = $title = $css = '';
		$rem   = (!$rem) ? '' : $rem;
		if ($lines>0) {
			$click = '<span title="Click here to show/hide debug output" style="color:#c00;cursor:pointer;cursor:hand;" onclick="var cn=this.parentNode.childNodes;var s=cn[cn.length-1].style;s.display=(s.display==\'none\')?\'block\':\'none\';">[+]</span>';
			$title = "title='Click the [+] to show/hide debug output'";
			$css   = "height:{$lines}em;clip:rect(0em,99%,{$lines}em,0em);overflow:auto;";
		}
		if ($lines>0) echo "<pre {$title} class='DbgPrint' style='position:relative;font-size:0.9em;line-height:auto;width:98%;background-color:#efefef;color:black;border:1px solid gray;margin:2px;padding:1px 2px;'>{$click}&nbsp;<b>{$rem}</b>";
		echo "<xmp class='DbgPrint' {$title} style='display:none;position:relative;font-size:11px;line-height:11px;width:99%;{$css}background:white;color:black;border:1px solid gray;margin:0px;padding:2px 4px;'>";
		print_r($v);
		echo '</xmp>';
		if ($lines>0) echo '</pre>';
	}


	/**
	 * Makes a string safe for usage.
	 *
	 * This method can be also called statically in your script using <code>$string = zone::makesafe($string).</code>
	 *
	 * @param string $value HTML string
	 * @return string safe version of value
	 */
	function makesafe($html) {
		$html = stripslashes($html);
		$html = str_replace ('<!--', '&lt;&#33;--', $html);
		$html = str_replace ('-->', '--&gt;', $html);
		$html = str_replace ('<', '&lt;', $html);
		$html = str_replace ('>', '&gt;', $html);
		$html = str_replace ('&#032;', ' ', $html);
		$html = str_replace ("\n", '<br />', $html);
		$html = str_replace ("'", '&#39;', $html);
		$html = str_replace ('\'', '&quot;', $html);
		$html = str_replace ('!', '&#33;', $html); // Added by Pita
		$html = preg_replace( "/\\\$/", "&#036;", $html ); // Added by Pita
		return $html;
	}

	/**
	 * Get execution time if timer enabled.
	 *
	 * @return mixed Time or flase if timer was not enabled.
	 */
	function get_exectime() {
		return ($this->_options['timer']) ? $GLOBALS['Debug']->EndTimer() : FALSE;
	}

	/**
	 * Get database query count.
	 *
	 * @return integer Amount of queries
	 */
	function get_querycount() {
		return $this->DB->query_count;
	}

	/**#@+
	 * @group Caching
	 */
	/**
	 * Gets function results cache.
	 *
	 * @access private
	 * @param string $function zone Method who's query results have been cached
	 * @param string $id Key to identify a query from the function
	 * @return mixed Cached item or FALSE if $key does not exist.
	 */
	function get_cache($function, $id) {
		if ($this->zone_settings['allow_caching'] && array_key_exists($function, $this->_cache)) {
			return (array_key_exists($id, $this->_cache[$function])) ? $this->_cache[$function][$id] : FALSE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Saves/Updates function results cache.
	 *
	 * @access private
	 * @param string $function zone Method who's query results have been cached
	 * @param string $id Key to identify a query from the function
	 * @param string $data Data being cached
	 * @return void
	 */
	function save_cache($function, $id, $data) {
		if ($this->zone_settings['allow_caching']) {
			$this->_cache[$function][$id] = $data;
		}
	}

	/**
	 * Attempts to find some value/object in the cache for cross variable assignments.
	 *
	 * @param string $function zone Method who's query results have been cached
	 * @param string $key Key to search for in this method's results
	 * @return mixed value/object whatever found in cache
	 */
	function find_cache($function, $key) {
		// Firstly see if caching is disabled
		if (!$this->zone_settings['allow_caching']) {
			return FALSE;
		}

		$data = array();
		if ($this->_cache[$function]) {
			foreach (array_keys($this->_cache[$function]) as $id) {
				$vtype = gettype($this->_cache[$function][$id]);
				if ($vtype == 'array' && isset($this->_cache[$function][$id][$key])) {
					// find array element
					$val = &$this->_cache[$function][$id][$id][$key];
				} else if ($vtype == 'object' && isset($this->_cache[$function][$id]->$key)) {
					// find object property
					$val = &$this->_cache[$function][$id]->$key;
				} else {
					// find value
					$val = &$this->_cache[$function][$id];
				}

				if (isset($val)) {
					$data[] = $val;
				}

				unset($val);
			}
		}
		return $data;
	}

	/**
	 * Attempts to sort out the weird permissions array.
	 *
	 * @param string $perm_array the permission array to be sorted
	 * @return array or permissions
	 */
	function sort_perms($perm_array) {
		# (C) IPS
		$perms = unserialize(stripslashes($perm_array));

		$fr['read_perms']   = $perms['read_perms'];
		$fr['reply_perms']  = $perms['reply_perms'];
		$fr['start_perms']  = $perms['start_perms'];
		$fr['upload_perms'] = $perms['upload_perms'];
		$fr['show_perms']   = $perms['show_perms'];
		RETURN $fr;
	}

	/**
	 * Finds out if a user has permission to do something...
	 *
	 * @param string $perm the permission to be worked out
	 * @param int	$user the user to have permissions checked
	 *					  if left blank, currently logged in user used.
	 * @return array or permissions
	 */
	function has_perms($perm,$user='') {
		if(substr($perm,0,2) != "g_") {
			$this->Error($this->lang['zone_badpermid']);
			return false;
		}
		$perm = preg_replace("#[^a-z_]#","",$perm);
		$info = $this->get_advinfo($user);
		if(!is_array($info)) {
			$this->Error($this->lang['zone_badmemid']);
			return false;
		}
		if($info[$perm]) {
			return true;
		} else {
			// Take a look at secondary groups
			$info['mgroup_others'] = substr($info['mgroup_others'],1,strlen($info['mgroup_others'])-2);
			if($info['mgroup_others'] != "") {
				$this->DB->query("SELECT ".$perm." FROM ibf_groups WHERE g_id IN(".$info['mgroup_others'].")");
				while($row = $this->DB->fetch_row()) {
					if($row[$perm]) {
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Returns the best perms a user has for something...
	 *
	 * @param string $perm the permission to be worked out
	 * @param int	$user the user to have permissions checked
	 *					  if left blank, currently logged in user used.
	 * @param bool	$zero if true, zero is best
	 * @return array or permissions
	 */
	function best_perms($perm,$user='',$zero=true) {
		if(substr($perm,0,2) != "g_") {
			$this->Error($this->lang['zone_badpermid']);
			return false;
		}
		$perm = preg_replace("#[^a-z_]#","",$perm);
		$info = $this->get_advinfo($user);
		if(!is_array($info)) {
			$this->Error($this->lang['zone_badmemid']);
			return false;
		}
		$init = $info[$perm];
		if(intval($init) == 0 && $zero) return 0;

		// Take a look at secondary groups
		$info['mgroup_others'] = substr($info['mgroup_others'],1,strlen($info['mgroup_others'])-2);

		if($info['mgroup_others'] != "") {
			$this->DB->query("SELECT ".$perm." FROM ibf_groups WHERE g_id IN(".$info['mgroup_others'].")");
			while($row = $this->DB->fetch_row()) {
				if($row[$perm] > $init) $init = $row[$perm];
				if(intval($init) == 0 && $zero) return 0;
			}
		}
		return $init;
	}

	// -----------------------------------------------
	// Main zone Functions Follow...
	// -----------------------------------------------

	// -----------------------------------------------
	// ATTACHMENT FUNCTIONS
	// -----------------------------------------------
	/**#@+
	 * @group Attachments
	 */

	/**
	 * Get a post's attachments
	 *
	 * @param integer $postid The post id to check
	 * @param boolean $override Whether to override permissions or not.
	 * @return array Post's attachments
	 */
	function get_post_attachments($postid,$override=FALSE) {
		$query = "";
		if($override == FALSE)
		{
			$query = " AND attach_approved=1";
		}
		$this->DB->query("SELECT ibf_attachments.*,attach_rel_id as attach_pid FROM ibf_attachments WHERE attach_rel_id='".$postid."'".$query);
		if($this->DB->get_num_rows() == 0) {
			return FALSE;
		}
		while ($row = $this->DB->fetch_row()) {
			$return[] = $row;
		}
		return $return;
	}

	/**
	 * Get an attachment's info.
	 *
	 * @param integer $id The attachment id to be parsed.
	 * @param boolean $override Whether to override permissions or not.
	 * @return array Attachment info
	 * @see attachment_image(), attachment_thumb()
	 */
	function attachment_info($id,$override=FALSE) {
		$query = "";
		if($override == FALSE)
		{
			$query = " AND attach_approved=1";
		}
		$this->DB->query("SELECT *,attach_rel_id as attach_pid FROM ibf_attachments WHERE attach_id='".$id."'".$query);
		if($this->DB->get_num_rows() == 0) {
		return FALSE;
		}
		$row = $this->DB->fetch_row();
		return $row;
	}

	/**
	 * Get HTML code for outputting an attachment image.
	 *
	 * @param integer $id The attachment id to be parsed.
	 * @param boolean $override Whether to override permissions or not.
	 * @return string HTML code
	 * @see attachment_info(), attachment_thumb()
	 */
	function attachment_image($id,$override=FALSE) {
		$att = $this->attachment_info($id,$override);
		if($att['attach_is_image'] == 0) {
			return FALSE;
		}
		// Still here? We have an image then...
		// Let's construct our fantabulous html.
		$out = '<img src="'.$this->board_url.'/uploads/'.$att['attach_location'].'" alt="'.$att['attach_file'].'" />';
		return $out;
	}

	/**
	 * Get HTML code for outputting an attachment thumbnail.
	 *
	 * @param integer $id The attachment id to be parsed.
	 * @param boolean $override Whether to override permissions or not.
	 * @return string HTML code
	 * @see attachment_info(), attachment_image()
	 */
	function attachment_thumb($id,$override=FALSE) {
		$att = $this->attachment_info($id,$override);
		if($att['attach_is_image'] == 0) {
			return FALSE;
		}
		// Still here? We have an image then...
		// Let's construct our fantabulous html.
		$out = '<img src="'.$this->board_url.'/uploads/'.$att['attach_thumb_location'].'" width="'.$att['attach_thumb_width'].'" height="'.$att['attach_thumb_height'].'" alt="'.$att['attach_file'].'" />';
		return $out;
	}

	// -----------------------------------------------
	// ANTI-SPAM FUNCTIONS
	// To stop bots on registration forms :)
	// -----------------------------------------------
	/**#@+
		* @group AntiSpam
	*/
		/**
	 * Checks a reg code in the anti-spam table
	 *
	 * @param key the key of the code
	 * @param code the user-submitted code to check
	 * @return bool match
	 */

	function check_anti_spam($key,$code) {
		$this->DB->query("SELECT regcode from ibf_reg_antispam WHERE regid='".$key."'");
		$var = $this->DB->fetch_row();
		if(count($var) > 0) {
			if($var['regcode'] == $code) {
				return true;
			}
			else {
				return false;
			}
		}
		$this->Error($this->lang['zone_badkey']);
		return false;
	}
	/**
	 * Adds a reg code to the anti-spam table and returns its key
	 *
	 * @return string regid
	 */
	function register_anti_spam() {
		$this->DB->query("SELECT conf_value,conf_default FROM ibf_conf_settings WHERE conf_key = 'bot_antispam'");
		$var = $this->DB->fetch_row();
		$var['conf_value'] = ($var['conf_value'] == '' ? $var['conf_default'] : $var['conf_value']);

		// Is anti-bot enabled?
		if($var['conf_value']) {
			// Set a new ID for this reg request...
			$regid = md5( uniqid(microtime()) );

			// Set a new 6 character numerical string
			mt_srand ((double) microtime() * 1000000);
			$reg_code = mt_rand(100000,999999);

			$this->DB->query("INSERT INTO ibf_reg_antispam VALUES('".$regid."','".$reg_code."','".$_SERVER['REMOTE_ADDR']."','".time()."')");

			return $regid;
		}
	}

	/**
	 * Change a GD antispam image (if it's unreadable)
	 *
	 * @return string regid
	 */
	function reload_anti_spam_image() {
		$antispam = $this->DB->query('SELECT * FROM ibf_reg_antispam WHERE regid="'.$_GET['img'].'"');
		$antispam = $this->DB->fetch_row();

		if (empty($antispam['regcode'])) return false;
		$regid = md5(uniqid(microtime()));

		if( $this->ips->vars['bot_antispam'] == 'gd') $reg_code = strtoupper(substr(md5($regid), 0, 6 ));
		else { mt_srand ((double) microtime() * 1000000); $reg_code = mt_rand(100000,999999); }
		$this->DB->query('DELETE FROM ibf_reg_antispam WHERE regid="'.$antispam['regid'].'"');
		$this->DB->query('INSERT INTO ibf_reg_antispam (regid,regcode,ip_address,ctime) VALUES ("'.$regid.'","'.$reg_code.'","'.$_SERVER["REMOTE_ADDR"].'","'.time().'")');

		return $regid;
		exit();

	}

	/**
	 * Creates an image based on the board's settings
	 *
	 * @param string regid The registration ID to show an image for
	 * @param integer p If the boards are using a GIF library, use this to specify which number to display (eg 1 for the first number, 2 for the second)
	 * @return string image
	 */
	function anti_spam_image($regid,$p=0) {
		$this->DB->query("SELECT conf_value,conf_default FROM ibf_conf_settings WHERE conf_key = 'bot_antispam'");
		$var = $this->DB->fetch_row();
		$var['conf_value'] = ($var['conf_value'] == '' ? $var['conf_default'] : $var['conf_value']);
		// Obtain code from DB
		$this->DB->query("SELECT regcode from ibf_reg_antispam WHERE regid='".$regid."'");
		// There'd better only be one...
		$info = $this->DB->fetch_row();

		if($var['conf_value'] == 'gd') {
			$this->ips->show_gd_img($info['regcode']);
			return true;
		}
		else {
			if(!$p | $p > 6 | $p < 1) {
				$this->Error('Invalid number for GIF library');
				return FALSE;
			}
			$p = $p - 1;
			$regcode = substr($info['regcode'],$p,1);
			$this->ips->show_gif_img($regcode);
			return true;
		}
	}

	/**
	 * Returns HTML <img> tags pointing to IPB's register file, to show an image.
	 *
	 * @param string regid The registration ID to return an image for
	 * @param string ajax The optional URL string where the new keycode will be returned. is required for activating ajax image reloading
	 * @return string html The returned HTML code
	 */
	function anti_spam_image_html($regid,$p=0,$ajax=false) {
		$this->DB->query("SELECT conf_value,conf_default FROM ibf_conf_settings WHERE conf_key = 'bot_antispam'");
		$var = $this->DB->fetch_row();
		$var['conf_value'] = ($var['conf_value'] == '' ? $var['conf_default'] : $var['conf_value']);
		// Obtain code from DB
		$this->DB->query("SELECT regcode from ibf_reg_antispam WHERE regid='".$regid."'");
		// There'd better only be one...
		$info = $this->DB->fetch_row();

		// ajax support for on demand reloading of anti_spam-Image
		// @author: Matthias Reuter <public@pc-intern.com> http://pc-intern.com | http://straightvisions.com
		if(isset($ajax)) {
			$ajax_code =
<<<AJAXCODE
				<script type="text/javascript">
				<!--
					var keycode_id = '{$regid}';

					function get_new_hash() {
						var url = '{$ajax}' + keycode_id;
						var xmlHttp = null;

						// Mozilla, Opera, Safari and Internet Explorer 7
						if (typeof XMLHttpRequest != 'undefined') {
							xmlHttp = new XMLHttpRequest();
						}
						if (!xmlHttp) {
							// Internet Explorer 6 and older
							try {
								xmlHttp  = new ActiveXObject('Msxml2.XMLHTTP');
							} catch(e) {
								try {
									xmlHttp  = new ActiveXObject('Microsoft.XMLHTTP');
								} catch(e) {
									xmlHttp  = null;
								}
							}
						}
						if (xmlHttp) {
							xmlHttp.open('GET', url, true);
							xmlHttp.onreadystatechange = function () {
								if (xmlHttp.readyState == 4) {
									keycode_id = xmlHttp.responseText;
									document.getElementById("anti_spam_image").src = '{$this->board_url}/index.php?act=Reg&CODE=image&rc=' + keycode_id;
									document.getElementById("anti_spam_session_id").value = keycode_id;
								}
							};
							xmlHttp.send(null);
						}
					}
				//-->
				</script>
AJAXCODE;

			$ajax_link = ' onClick="get_new_hash();" style="cursor:pointer;" title="Click here to refresh Spam-Image"';
		} else {
			$ajax_code = false;
			$ajax_link = false;
		}

		if($var['conf_value'] == 'gd') {
			$html = $ajax_code.'<img'.$ajax_link.' src="'.$this->board_url.'/index.php?act=Reg&amp;CODE=image&amp;rc='.$regid.'" alt="Code Bit" id="anti_spam_image" />';
		}
		else {
			$html = '<img src="'.$this->board_url.'/index.php?act=Reg&amp;CODE=image&amp;rc='.$regid.'&amp;p=1" alt="Code Bit" />
					&nbsp;<img src="'.$this->board_url.'/index.php?act=Reg&amp;CODE=image&amp;rc='.$regid.'&amp;p=2" alt="Code Bit" />
					&nbsp;<img src="'.$this->board_url.'/index.php?act=Reg&amp;CODE=image&amp;rc='.$regid.'&amp;p=3" alt="Code Bit" />
					&nbsp;<img src="'.$this->board_url.'/index.php?act=Reg&amp;CODE=image&amp;rc='.$regid.'&amp;p=4" alt="Code Bit" />
					&nbsp;<img src="'.$this->board_url.'/index.php?act=Reg&amp;CODE=image&amp;rc='.$regid.'&amp;p=5" alt="Code Bit" />
					&nbsp;<img src="'.$this->board_url.'/index.php?act=Reg&amp;CODE=image&amp;rc='.$regid.'&amp;p=6" alt="Code Bit" />';
		}
		return $html;
	}
	// -----------------------------------------------
	// BBCODE FUNCTIONS
	// If you like BBCode these thingys are for you :P
	// -----------------------------------------------
	/**#@+
	 * @group BBCode
	 */
	/**
	 * Converts BBCode to HTML using IPB's native
	 * parser.
	 *
	 * @return string HTML version of input
	 * @see html2bbcode(), parse_dohtml()
	 */
	function bbcode2html($input, $smilies = '1') {
		$this->parser->parse_smilies = $smilies;
		$this->parser->parse_html = 0;
		$this->parser->parse_bbcode = 1;
		$this->parser->strip_quotes = 1;
		$this->parser->parse_nl2br = 1;

		$input = $this->parser->pre_db_parse($input);
		// Leave this here in case things go pear-shaped...
		$input = $this->parser->pre_display_parse($input);

		if($smilies == '1') {
			$input = str_replace("src=\"style_emoticons/","src=\"".$this->board_url."/style_emoticons/",$input);
			$input = str_replace("src='style_emoticons/","src='".$this->board_url."/style_emoticons/",$input);
			$input = str_replace(' target="_blank"',"",$input);
			$input = preg_replace("|([A-Za-z0-9\%<]+)\#EMO_DIR\#([A-Za-z0-9\%>]+)\/|U",$this->ips->skin['_emodir']."/",$input);
		}
		return $input;
	}

	/**
	 * Converts HTML to BBCode using IPB's native parser.
	 *
	 * @return string BBCode version of input
	 * @see bbcode2html(), parse_dohtml()
	 */
	function html2bbcode($input) {
		$this->parser->parse_smilies = 1;
		$this->parser->parse_html = 0;
		$this->parser->parse_bbcode = 1;
		$this->parser->strip_quotes = 1;
		$this->parser->parse_nl2br = 1;
		$input = $this->parser->pre_edit_parse($input);

		return $input;
	}

	// -----------------------------------------------
	// CACHE STORE FUNCTIONS
	// The IPB Cache Store is something very little
	// people know about but can be very very useful.
	// -----------------------------------------------
	/**#@+
	 * @group CacheStore
	 */
	/**
	 * List all cache stores.
	 *
	 * @return array all cache store key, values and extra.
 	 * @see set_cache_store_value(), get_cache_store_value(), search_cache_store()
	 */
	function list_cache_stores() {
		if ($cache = $this->get_cache('list_cache_stores', '1')) {
			return $cache;
		} else {
			$this->DB->query ('SELECT cs_key, cs_value, cs_extra FROM ibf_cache_store');
			$cs = array();

			while ($row = $this->DB->fetch_row()) {
				$cs[$row['cs_key']] = $row;
			}

			$this->save_cache('list_cache_stores', '1', $cs);
			return $cs;
		}
	}

	/**
	 * Get the value of a cache store.
	 *
	 * @param string $key Key of the cache store
	 * @return string value of a cache store.
	 * @see set_cache_store_value(), list_cache_stores(), search_cache_store()
	 */
	function get_cache_store_value($key) {
		$cs = $this->list_cache_stores();

		if ($cs[$key]) {
			return $cs[$key]['cs_value'];
		} else {
			return FALSE;
		}
	}

	/**
	 * Sets or updates the value of a cache store.
	 *
	 * @param string $key Key of the cache store
	 * @param string $value Value to store
	 * @return bool TRUE on success.
	 * @see get_cache_store_value(), list_cache_stores(), search_cache_store()
	 */
	function set_cache_store_value($key, $value = '') {
		$cs = $this->list_cache_stores();

		if ($cs[$key]) {
			// Already exists so just use UPDATE
			$this->DB->query ("UPDATE ibf_cache_store SET cs_value='" . $value . "' WHERE cs_key='" . $key . "'");

			if ($this->DB->get_affected_rows()) {
				// And update our cached copy
				$cs[$key] = array('cs_key' => $key,
					'cs_value' => $value,
					'cs_extra' => '',
					);

				$this->save_cache('list_cache_stores', '1', $cs);

				return TRUE; // We're done.
			} else {
				// What happened? I dunno.
				return FALSE;
			}
		} else {
			// Doesn't exist so use INSERT
			$this->DB->query ("INSERT INTO ibf_cache_store (cs_key, cs_value) VALUES ('" . $key . "', '" . $value . "')");
			if ($this->DB->get_affected_rows()) {
				// And update our cached copy
				$cs[$key] = array('cs_key' => $key,
					'cs_value' => $value,
					'cs_extra' => '',
					);

				$this->save_cache('list_cache_stores', '1', $cs);
				return TRUE; // And your done...
			} else {
				// I don't know why on earth it wouldn't work but it might not
				return FALSE;
			}
		}
	}

	/**
	 * Searches the cache store.
	 *
	 * @param mixed $value Storage value to search
	 * @param bool $exactmatch Use exact matching or wildcard search
	 * @return array cache stores matching criteria
  	 * @see set_cache_store_value(), get_cache_store_value(), list_cache_stores()
	 */
	function search_cache_store($value, $exactmatch = FALSE) {
		// Do the SQL Query
		if ($exactmatch) {
			$this->DB->query ("SELECT * FROM ibf_cache_store WHERE cs_value='" . $value . "'");
		} else {
			$this->DB->query ("SELECT * FROM ibf_cache_store WHERE cs_value LIKE '%" . $value . "%'");
		}

		$cs = array();

		while ($row = $this->DB->fetch_row()) {
			$cs[$row['cs_key']] = $row;
		}

		return $cs;
	}

	// -----------------------------------------------
	// CATEGORIES FUNCTIONS
	// Do stuff with categories.
	// -----------------------------------------------
	/**#@+
	 * @group Categories
	 */
	/**
	 * List categories.
	 *
	 * @return array Board Categories
	 * @see get_category_info(), new_category()
	 */
	function list_categories() {
		if ($cache = $this->get_cache('list_categories', '1')) {
			return $cache;
		} else {
			$this->DB->query ("SELECT * FROM ibf_forums WHERE parent_id = '-1'");
			$cat = array();

			while ($row = $this->DB->fetch_row()) {
				$cat[$row['id']] = $row;
			}

			$this->save_cache('list_categories', '1', $cat);
			return $cat;
		}
	}


	/**
	 * Get Information on a Category
	 *
	 * @param integer $categoryid Unique ID of the category
	 * @return array Information on category categoryid
	 * @see list_categories(), new_category()
	 */
	function get_category_info($categoryid) {
		$cats = $this->list_categories();

		if ($cats[$categoryid]) {
			return $cats[$categoryid];
		} else {
			// Category doesn't exist.
			return FALSE;
		}
	}

	// -----------------------------------------------
	// CUSTOM FIELDS FUNCTIONS
	// You know those special fields in profiles :)
	// -----------------------------------------------
	/**#@+
	 * @group CustomFields
	 */
	/**
	 * Gets the value of a custom profile field for a given member.
	 * If $memberid is ommitted, the last known member id is used.
	 *
	 * @param integer $fieldid Field ID (number) to retrieve.
	 * @param integer $memberid Member ID to read the custom profile field from.
	 * @return string Value of memberid's custom profile field field-id
	 * @see list_customfields(), update_customfield()
	 */
	function get_customfield_value($fieldid, $memberid = '') {
		if ($memberid) {
			$info = $this->get_advinfo($memberid);
		} else {
			$info = $this->get_advinfo();
		}

		if ($info['field_' . $fieldid]) {
			$this->DB->query ("SELECT pf_content, pf_type FROM ibf_pfields_data WHERE pf_id='" . intval($fieldid) . "'");
			if ($this->DB->get_num_rows()) {
				$field_info = $this->DB->fetch_row();
				if($field_info['pf_type'] == 'drop')
				{
					$field = explode('|',$field_info['pf_content']);
					$element = array();
					foreach($field as $item) {
						$temp = explode('=',$item);
						$temp = array($temp[0] => $temp[1]);
						$element = array_merge($element,$temp);
					}
					return $element[$info['field_' . $fieldid]];
				}
				return $info['field_' . $fieldid];
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

	/**
	 * Grab a list of custom profile fields, and their properties.
	 *
	 * @return array custom profile fields and properties
	 * @see get_customfield_value(), update_customfield()
	 */
	function list_customfields() {
		// Check for cache...
		if ($cache = $this->get_cache('list_customfields', '1')) {
			return $cache;
		} else {
			$this->DB->query ('SELECT * FROM ibf_pfields_data ORDER BY pf_id');
			if ($this->DB->get_num_rows()) {
				while ($info = $this->DB->fetch_row()) {
					$fields['field_' . $info['pf_id']] = $info;
				}

				$this->save_cache('list_customfields', '1', $fields);

				return $fields;
			} else {
				return array();
			}
		}
	}

	/**
	 * Updates the value of a custom profile field.
	 *
	 * @param integer $id
	 * @param string $newvalue
	 * @param bool $bypassperms
	 * @return bool whether the action was successful
	 * @see list_customfields(), get_customfield_value()
	 */
	function update_customfield($id, $newvalue, $bypassperms = FALSE, $member_id = FALSE) {
		if(empty($member_id)) $member_id = $this->ips->member['id'];

		$bypassperms = (bool)$bypassperms;
		$fieldinfo = $this->list_customfields($member_id);
		if ($info = $fieldinfo['field_' . $id]) {
			if ($info['pf_member_edit'] OR $bypassperms) {
				if ($info['pf_type'] == 'drop') {
					$allowed = array();

					$i = explode ('|', $info['pf_content']);
					foreach ($i as $j) {
						$k = explode ('=', $j);
						$allowed[] = $k['0'];
					}

					if (!in_array($newvalue, $allowed)) {
						$this->Error($this->lang['zone_cfinvalidvalue']);
						return FALSE;
					}
				}

				if ($info['pf_not_null'] AND !$newvalue) {
					$this->Error(sprintf($this->lang['zone_cfmustfillin']), $id);
					return FALSE;
				}

				$this->DB->query ("UPDATE ibf_pfields_content SET field_" . $id . "='" . $newvalue . "' WHERE member_id='" . $member_id . "'");
				return TRUE;
			} else {
				$this->Error(sprintf($this->lang['zone_cfcantedit'], $id));
				return FALSE;
			}
		} else {
			$this->Error(sprintf($this->lang['zone_cfnotexist'], $id));
			return FALSE;
		}
	}
	// -----------------------------------------------
	// EMAIL FUNCTIONS
	// Sends an e-mail to a member.
	// -----------------------------------------------
	/**#@+
	 * @group EMail
	 */
	/**
	 * Sends an email to a member.
	 *
	 * @param integer $id Member ID
	 * @param string $subject Message subject
	 * @param string $message Message body
	 * @return bool Success
	 */
	function mail_member($id, $subject, $message) {
		if ($info = $this->get_advinfo($id)) {
			if (!isset($this->extra['emailer'])) {
				// OMG, a usable lib ;)
				require_once(ROOT_PATH.'sources/classes/class_email.php');
				// Require email class
				$this->extra['emailer'] =& new emailer(ROOT_PATH);
				$this->extra['emailer']->ipsclass =& $this->ips;
				$this->extra['emailer']->email_init();
			}

			$this->extra['emailer']->to = $info['email']; // Set to
			$this->extra['emailer']->subject = $subject;
			$this->extra['emailer']->template = $this->lang['zone_email_template']; // Oh dear

			$this->extra['emailer']->build_message(array('MESSAGE' => $message));

			$this->extra['emailer']->send_mail();
			return TRUE;
		} else {
			return FALSE;
		}
	}

	// -----------------------------------------------
	// EMOTICONS FUNCTIONS
	// Functions to do with Emoticons.
	// -----------------------------------------------
	/**#@+
	 * @group Emoticons
	 */
	/**
	 * List emoticons, optional limit the result to clickable emoticons only.
	 *
	 * @param bool $clickable Clickable emoticons only. Default: FALSE
	 * @return array Assoc array with Emoticons, keys 'typed', 'image'
	 */
	function list_emoticons($clickable = FALSE) {
		if ($clickable) {
			$this->DB->query ("SELECT typed, image FROM ibf_emoticons WHERE clickable='1'");
		} else {
			$this->DB->query ("SELECT typed, image FROM ibf_emoticons");
		}

		$emos = array();

		while ($row = $this->DB->fetch_row()) {
			$row['typed'] = str_replace(array('&lt;', '&gt;'), array('<', '>'), $row['typed']);
			$emos[$row['typed']] = $row['image'];
		}

		return $emos;
	}

	// -----------------------------------------------
	// GROUP FUNCTIONS
	// Group stuff
	// -----------------------------------------------
	/**#@+
	 * @group Groups
	 */
	/**
	 * Returns information on a group.
	 * If $group is ommited, the last known group (of the last member) is used.
	 *
	 * @param integer $group Group ID
	 * @return array Group Information
	 */
	function get_groupinfo($group = '') {
		if (!$group) {
			// No Group? Return current group info
			$group = $this->ips->member['mgroup'];
		}
		// Check for cache - if exists don't bother getting it again
		if ($cache = $this->get_cache('get_groupinfo', $group)) {
			return $cache;
		} else {
			// Return group info if group given
			$this->DB->query ("SELECT g.* FROM ibf_groups g WHERE g_id='" . intval($group) . "'");
			if ($this->DB->get_num_rows()) {
				$info = $this->DB->fetch_row();
				$this->save_cache('get_groupinfo', $group, $info);
				return $info;
			} else {
				return FALSE;
			}
		}
	}

	/**
	 * Changes Member group to delivered group-id.
	 * If no Member-ID is delivered, the currently logged in member will moved.
	 *
	 * @param integer $group Group ID
	 * @param integer $member Member ID
	 * @param array $extra secondary Group-IDs
	 * @return bool True on success
	 */
	function change_group($group, $member = false, $extra = false) {
		if (!$member) $member = $this->ips->member['id'];
		if ($extra) $sql_extra = ', SET mgroup_others="'.implode(',',$extra).'"';
		if($this->DB->query('UPDATE ibf_members SET mgroup="'.$group.'"'.$sql_extra.' WHERE id="'.intval($member).'"')) return TRUE;
		else return FALSE;
	}

	// -----------------------------------------------
	// zone FUNCTIONS
	// Misc functions which don't interact with IPB
	// but the zone.
	// -----------------------------------------------
	/**#@+
	 * @group zone
	 */
	/**
	 * Adds an error message to the list of existing zone error messages.
	 * Sets the last error property which get be retrieved by zone_error()
	 *
	 * @access private
	 * @param string $error
	 * @return void
	 * @see zone_error()
	 */
	function Error($msg="") {
		return $msg;
}
	function _error($msg="",$url="") {
		global $zone;
		$zone->echo->html .= $zone->skin_global->show_error( $msg = $msg,$url=$url );
    	$zone->echo->output();
}
	/**
	 * Returns zone version.
	 *
	 * @return string zone Version Number.
	 * @since 1.0.0 Returns a phpversion() compliant format.
	 */
	function zone_version() {
		return $this->zone_version;
	}

	/**
	 * Prints a useful page with debug information on IPB zone and the things behind the scene.
	 *
	 * @since 1.1 Out-sourced to reduce file-weight of the main class.
	 */
	function zone_info() {
		global $ACP;
		@include(PORTAL_PATH . 'lib/zone_info.php');
	}

	/**
	 * Returns textual/timestamp offsetted date by board and by
	 * member offset and DST setting.
	 *
	 * @param integer $timestamp Numeric representation of the time beeing formatted
	 * @param string $dateformat date() compliant format (see PHP manual)
	 * @param integer $noboard 1=Offset with Board Time firstly, default = 0
	 * @param integer $nomember 1=Bypass member's time offset and DST, default = 0
	 * @return mixed textual/timestamp offsetted date by board and by member offset and DST setting.
	 */
	function zone_date($timestamp = '', $dateformat = '', $noboard = '0', $nomember = '0') {
		// Strictly not IPB zone related but so what :)
		// Grab Member Settings - We rely on get_advinfo() for this :)
		$info = $this->get_advinfo();
		// If theres no timestamp make it current time using time()
		if (!$timestamp) {
			$timestamp = time();
		}
		// Offset with Board Time firstly, if enabled
		// Also Check no member offset
		if (!$noboard) {
			if (!$nomember AND !$info['time_offset']) {
				$timestamp = $timestamp + ($this->ips->vars['time_offset'] * 60);
			}
		}
		// Board Time Adjust
		if ($this->ips->vars['time_adjust']) {
			$timestamp = $timestamp + ($this->ips->vars['time_adjust'] * 60);
		}
		// This is where website integration get's really good :)
		// If member has set an indiviual offset in the User CP
		// because they may be in a totally different country
		// using DST or whatever we can make those times affect it
		// across the whole website as well :D
		if ($this->loggedin AND !$nomember) {
			if ($info['time_offset']) {
				$timestamp = $timestamp + ($info['time_offset'] * 3600);
			}

			if ($info['dst_in_use']) {
				$timestamp = $timestamp - 3600;
			}
		}

		if ($dateformat) {
			$timestamp = date($dateformat, $timestamp);
		}

		return $timestamp;
	}

	/**
	 * Changes the zone Language Pack.
	 *
	 * @param string $language Language code. Must match the code used for the language filename
	 * @return bool success
	 */
	function zone_set_language($language) {
		$lang = false;
		// Great stuff...
		if (file_exists(PORTAL_PATH . 'lib/lang_zone_' . $language . '.php')) {
			if (include(PORTAL_PATH . 'lib/lang_zone_' . $language . '.php')) {
				// Change $this->lang
				$this->lang = $lang;
				unset($lang);
				// And update _options
				$this->_options['language'] = $language;
				// Done!!!
				return TRUE;
			} else {
				// Can't include it. Return FALSE.
				return FALSE;
			}
		} else {
			// Doesn't exist. Invalid Language.
			return FALSE;
		}
	}


	// -----------------------------------------------
	// MODERATOR FUNCTIONS
	// Information on Moderators
	// -----------------------------------------------
	/**#@+
	 * @group Moderators
	 */
	/**
	 * Returns all forum moderators.
	 *
	 * @param integer $forumid ID of Forum to extract Moderators from
	 * @param integer $memid ID of Member to find Moderation Forums from
	 * @param integer $groupid ID of Group to find Moderation Forums from
	 * @return array Moderators
	 */
	function get_forum_moderators($forumid = '0', $memid = '0', $groupid = '0') {
		// Either is usable, or both, but at least one must be set.
		if(!$forumid && !$memid && !$groupid) {
			return FALSE;
		}
		// If both $forumid and $memid are set, or both $forumid and $groupid are set, $and1 will be stated.
		if((($forumid != "0") && ($memid != "0")) OR (($forumid != "0") && ($groupid != "0"))) {
			$and1 = " AND ";
		}
		// If both $memid and $groupid are set, $and2 will be stated.
		if(($memid != "0") && ($groupid != "0")) {
			$and2 = " AND ";
		}

		// Is $forumid set? Let's use it!
		if($forumid != "0") {
			$forum = "forum_id='".$forumid."'";
		}
		// Is $memid set? Let's use it!
		if($memid != "0") {
			$user = "member_id='".$memid."'";
		}
		// Is $groupid set? Let's use it!
		if($groupid != "0") {
			$group = "group_id='".$groupid."'";
		}

		$this->DB->query ("SELECT * FROM ibf_moderators WHERE " . $forum . $and1 . $user . $and2 . $group);
		while($row = $this->DB->fetch_row()) {
			$return[] = $row;
		}
		return $return;
	}


	// -----------------------------------------------
	// MEMBER FUNCTIONS
	// Functions which interact with IPB's member system.
	// -----------------------------------------------
	/**#@+
	 * @group Members
	 */
	/**
	 * Returns current member's login status.
	 *
	 * @return bool Whether the current user is logged in
	 */
	function is_loggedin() {
		return (bool) $this->loggedin;
	}

	/**
	 * Returns whether a member is a super moderator.
	 * If $memberid is ommited, the last known member id is used.
	 *	 
	 * @param integer $memberid
	 * @return bool Whether currently logged in member is a Super Moderator
	 */
	function is_supermod($memberid = '') {
		return $this->has_perms('g_is_supmod',$memberid);
	}

	/**
	 * Returns whether a member can access the board's Admin CP.
	 * If $memberid is ommited, the last known member id is used.
	 *	 
	 * @param integer $memberid
	 * @return bool Whether currently logged in member can access ACP
	 */
	function is_admin($memberid = '') {
		return $this->has_perms('g_access_cp',$memberid);
	}

	/**
	 * Returns whether a member is in the specified group(s).
	 * If $memberid is ommited, the last known member id is used.
	 *
	 * @author Cow
	 * @author DigitalisAkujin
	 * @param integer $group Group ID or array of groups-ids separated with comma: 2,5,7
	 * @param integer $member Member ID to find
	 * @param boolean $extra Include secondary groups to test against?
	 * @return mixed Whether member is in group(s)
	 */
	function is_ingroup($group, $member = '', $extra = TRUE) {
		if (!is_array($group)) $group = explode(',', $group);
		settype($group, 'array');
		if ($member) {
			$this->DB->query ("SELECT `mgroup`,`mgroup_others` FROM ibf_members WHERE id='" . $member . "'");
			if ($row = $this->DB->fetch_row()) {
				if (in_array($row['mgroup'], $group)) {
					return TRUE;
				}
				if($extra) {
					$others = explode(",",$row['mgroup_others']);
					foreach($others as $other) {
						if(in_array($other,$group)) {
							return TRUE;
						}
					}
				}
			}
			return FALSE;
		} else {
			if (in_array($this->ips->member['mgroup'], $group)) {
				return TRUE;
			} else {
				// START CHANGE
				$other = explode(",",$this->ips->member['mgroup_others']);
				if(is_array($other)) {
					foreach($other as $v) {
						if(in_array($v, $group)) {
							return TRUE;
						}
					}
				}
				// END CHANGE
				return FALSE;
			}
		}
	}

	function login($username, $password, $cookie = '1', $anon = '0', $sticky = '1') {
		$username = $this->ips->txt_stripslashes($username);
		$username = preg_replace("/&#([0-9]+);/", '-', $username);
		$username = $this->makesafe($username);
		$password = $this->ips->txt_stripslashes($password);
		$password = preg_replace("/&#([0-9]+);/", '-', $password);
		$password = $this->makesafe($password);

		$sticky = $sticky ? '1' : '0';

		if (!$username OR !$password) {
			$this->Error($this->lang['zone_login_nofields']);
			return FALSE;
		}
		if (strlen($username) > 32 OR strlen($password) > 32) {
			$this->Error($this->lang['zone_login_length']);
			return FALSE;
		}

		$username = strtolower(str_replace('|', '|', $username));
		$password = md5($password);


		//-----------------------------------------
		// NAME LOG IN
		//-----------------------------------------

		$this->DB->cache_add_query( 'login_getmember', array( 'username' => $username ) );
		$this->DB->cache_exec_query();

		if ($this->member = $this->DB->fetch_row()) {

			//-----------------------------------------
			// Got a username?
			//-----------------------------------------

			if ( ! $this->member['id'] )
			{
				$this->Error($this->lang['zone_login_memberid']);
				return FALSE;
			}

			$this->ips->converge->converge_load_member( $this->member['email'] );

			if ( ! $this->ips->converge->member['converge_id'] )
			{
				$this->Error($this->lang['zone_login_wrongpass']);
				return FALSE;
			}

			//-----------------------------------------
			// Check password...
			//-----------------------------------------

			if ( $this->ips->converge->converge_authenticate_member( $password ) != TRUE )
			{
				$this->Error($this->lang['zone_login_wrongpass']);
				return FALSE;
			}

			// Still here... Means its Okely Doke
			$sid = md5(uniqid(microtime()));

			if ($cookie) {
				$this->ips->my_setcookie('member_id', $this->member['id'], $sticky);
				$this->ips->my_setcookie('pass_hash', $this->member['member_login_key'], $sticky);
				$this->ips->my_setcookie('session_id', $sid, $sticky);
				// Set 'Cookie Expire' - one week the cookie will be saved.
				$expire_date = time()+604800;
			} else {
				$this->ips->my_setcookie('session_id', $sid, -1);
				// Set 'Cookie Expire' - this cookie will be saved temporaly and deleted after browser exit.
				$expire_date = time();
			}
			// Destroy Sessions
			$this->DB->query("DELETE FROM ibf_sessions WHERE ip_address='" . $_SERVER['REMOTE_ADDR'] . "'");
			// Create Session
			$id = $this->member['id'];
			$browser = substr($_SERVER['HTTP_USER_AGENT'], 0, 64);
			$ip = substr($_SERVER['REMOTE_ADDR'], 0, 16);

			$this->DBstring = $this->DB->compile_db_insert_string(array('id' => $sid,
					'member_name' => $this->member['name'],
					'member_id' => $this->member['id'],
					'running_time' => time(),
					'member_group' => $this->member['mgroup'],
					'ip_address' => $ip,
					'browser' => $browser,
					'login_type' => $anon ? '1' : '0'
					));

			$this->DB->query('INSERT INTO ibf_sessions (' . $this->DBstring['FIELD_NAMES'] . ') VALUES (' . $this->DBstring['FIELD_VALUES'] . ')');

			// Set 'Privacy Status'
			$this->DB->query ('UPDATE ibf_members SET login_anonymous="'.intval($anon).'&1",member_login_key_expire="'.$expire_date.'" WHERE id="'.$this->member['id'].'"');

			$this->loggedin = true;
			return $this->member;
		} else {
			$this->Error($this->lang['zone_login_nomember']);
			return FALSE;
		}
	}

	/**
	 * Logout a user.
	 *
	 * @return bool success
	 */
	function logout() {

		global $HTTP_COOKIE_VARS;

		// Are we even logged in?
		if(!$this->is_loggedin()) return true;

		// Update the DB


		$this->DB->simple_construct( array( 'select' => false,
									  'lowpro' => false,
									  'delete' => false,
									  'order' => false,
									  'limit' => false,
									  'update' => 'sessions',
									  'set'	=> "member_name='',member_id='0',login_type='0'",
									  'where'  => "id='". $this->ips->session_id ."'"
							 )	  );

		$this->DB->simple_shutdown_exec();

		list( $privacy, $loggedin ) = explode( '&', $this->ips->member['login_anonymous'] );


		$this->DB->simple_construct( array( 'select' => false,
									  'lowpro' => false,
									  'delete' => false,
									  'order' => false,
									  'limit' => false,
									  'update' => 'members',
									  'set'	=> "login_anonymous='{$privacy}&0', last_visit=".time().", last_activity=".time(),
									  'where'  => "id=".$this->ips->member['id']
							 )	  );

		$this->DB->simple_shutdown_exec();

		// Set some cookies

		$this->ips->my_setcookie( "member_id" , "0"  );
		$this->ips->my_setcookie( "pass_hash" , "0"  );
		$this->ips->my_setcookie( "anonlogin" , "-1" );

		if (is_array($HTTP_COOKIE_VARS))
 		{
 			foreach( $HTTP_COOKIE_VARS as $cookie => $value )
 			{
 				if (preg_match( "/^(".$this->ips->vars['cookie_id']."ibforum.*$)/i", $cookie, $match))
 				{
 					$this->ips->my_setcookie( str_replace( $this->ips->vars['cookie_id'], "", $match[0] ) , '-', -1 );
 				}
 			}
 		}

		return TRUE;
	}

	function name2id($names) {
		if (is_array($names)) {
			foreach ($names as $i => $j) {
				$this->DB->query ("SELECT id FROM ibf_members WHERE LOWER(name)='" . strtolower($j) . "'");
				if ($row = $this->DB->fetch_row()) {
					$ids[$i] = $row['id'];
				} else {
					$ids[$i] = FALSE;
				}
			}

			return $ids;
		} else {
			$this->DB->query ("SELECT id FROM ibf_members WHERE LOWER(name)='" . strtolower($names) . "'");
			if ($row = $this->DB->fetch_row()) {
				return $row['id'];
			} else {
				return FALSE;
			}
		}
	}

	function id2name($id) {
		if (is_array($id)) {
			foreach ($id as $i => $j) {
				if ($row = $this->get_advinfo($j)) {
					$names[$i] = $row['name'];
				} else {
					$names[$i] = FALSE;
				}
			}

			return $names;
		} else {
			if ($row = $this->get_advinfo($id)) {
				return $row['name'];
			} else {
				return FALSE;
			}
		}
	}

	function displayname2id($names) {
		if (is_array($names)) {
			foreach ($names as $i => $j) {
				$this->DB->query ("SELECT id FROM ibf_members WHERE LOWER(members_display_name)='" . strtolower($j) . "'");
				if ($row = $this->DB->fetch_row()) {
					$ids[$i] = $row['id'];
				} else {
					$ids[$i] = FALSE;
				}
			}

			return $ids;
		} else {
			$this->DB->query ("SELECT id FROM ibf_members WHERE LOWER(members_display_name)='" . strtolower($names) . "'");
			if ($row = $this->DB->fetch_row()) {
				return $row['id'];
			} else {
				return FALSE;
			}
		}
	}

	function id2displayname($id) {
		if (is_array($id)) {
			foreach ($id as $i => $j) {
				if ($row = $this->get_advinfo($j)) {
					$names[$i] = $row['members_display_name'];
				} else {
					$names[$i] = FALSE;
				}
			}

			return $names;
		} else {
			if ($row = $this->get_advinfo($id)) {
				return $row['members_display_name'];
			} else {
				return FALSE;
			}
		}
	}

	function email2id($emails) {
		if (is_array($emails)) {
			foreach ($emails as $i => $j) {
				$this->DB->query ("SELECT id FROM ibf_members WHERE LOWER(email)='" . strtolower($j) . "'");
				if ($row = $this->DB->fetch_row()) {
					$ids[$i] = $row['id'];
				} else {
					$ids[$i] = FALSE;
				}
			}

			return $ids;
		} else {
			$this->DB->query ("SELECT id FROM ibf_members WHERE LOWER(email)='" . strtolower($emails) . "'");
			if ($row = $this->DB->fetch_row()) {
				return $row['id'];
			} else {
				return FALSE;
			}
		}
	}

	function get_info($memberid = '') {
		// No caching in this function or anything good which
		// will be better then get_advinfo(). So use that if possible.
		// However I guess you could get $ibforums->member with it and
		// its good for easy backward compatibility so I'll keep it in
		// here.
		if (!$memberid) {
			// No UID? Return current user info
			return ($this->ips->member);
		} else {
			// Return user info if UID given
			$this->DB->query ("SELECT m.name, m.id, m.member_login_key, m.email, m.title, m.mgroup, m.view_sigs, m.view_img, m.view_avs, m.members_display_name, g.* FROM ibf_members m LEFT JOIN ibf_groups g ON (m.mgroup=g.g_id) WHERE id='" . intval($memberid) . "'");
			if ($this->DB->get_num_rows()) {
				$info = $this->DB->fetch_row();

				return $info;
			} else {
				$this->Error($this->lang['zone_badmemid']);
				return FALSE;
			}
		}
	}

	function get_advinfo($memberid = '') {
		if (!$memberid) {
			if($this->is_loggedin()) {
				// No UID? Return current user info
				$memberid = $this->ips->member['id'];
			} else {
				// Return guest group info
				// guests group-info fix added, @author: Matthias Reuter <public@pc-intern.com> http://pc-intern.com | http://straightvisions.com
				$this->DB->query ("SELECT * FROM ibf_groups WHERE g_id='2'");
				if ($this->DB->get_num_rows()) {
					$info = $this->DB->fetch_row();
					$this->save_cache('get_advinfo', $memberid, $info);

					return $info;
				} else {
					return FALSE;
				}
			}
		}
		// Check for cache - if exists don't bother getting it again
		if ($cache = $this->get_cache('get_advinfo', $memberid)) {
			return $cache;
		} else {
			// Return user info if UID given
			$this->DB->query ("SELECT m.*, me.signature, me.avatar_size, me.avatar_location, me.avatar_type, me.vdirs, me.location, me.msnname, me.interests, me.yahoo, me.website, me.aim_name, me.icq_number, g.*, cf.*, pp.* FROM ibf_members m LEFT JOIN ibf_groups g ON (m.mgroup=g.g_id) LEFT JOIN ibf_pfields_content cf ON (cf.member_id=m.id) LEFT JOIN ibf_member_extra me ON (me.id=m.id) LEFT JOIN ibf_profile_portal pp ON(pp.pp_member_id=m.id) WHERE m.id='" . intval($memberid) . "'");
			if ($this->DB->get_num_rows()) {
				$info = $this->DB->fetch_row();
				$this->save_cache('get_advinfo', $memberid, $info);

				return $info;
			} else {
				return FALSE;
			}
		}
	}

	function get_avatar($member = '') {
		// No Member ID specified? Go for the current users UID.
		if (!$member) {
			$member = $this->ips->member['id'];
		}
		// Get Avatar Info
		if ($row = $this->get_advinfo($member)) {
			$avatar = $this->ips->get_avatar ($row['avatar_location'], 1, $row['avatar_size'], $row['avatar_type']);
			if($row['avatar_type'] == "local" && $row['avatar_location'] != "noavatar") {
				$avatar = str_replace("<img src='","<img src='".$this->_options['board_url']."/style_avatars",$avatar);
			}
			$avatar = str_replace(" border='0'","",$avatar);
			return $avatar;
		} else {
			$this->Error($this->lang['zone_badmemid']);
			return FALSE;
		}
	}

	function get_raw_sig($memberid = '') {
		if (!$memberid) {
			$memberid = $this->ips->member['id'];
		}

		if ($info = $this->get_advinfo($memberid)) {
			$this->parser->parse_html = $this->ips->vars['sig_allow_html'];
			$this->parser->parse_bbcode = $this->ips->vars['sig_allow_ibc'];
			return $this->parser->pre_edit_parse($info['signature']);
		} else {
			return FALSE;
		}
	}
	function get_photo($memberid = '',$thumb = false) {
		if (!$memberid) {
			$memberid = $this->ips->member['id'];
		}
		$this->DB->query ("SELECT pp_main_photo, pp_main_width, pp_main_height, pp_thumb_photo, pp_thumb_width, pp_thumb_height FROM ibf_profile_portal WHERE pp_member_id='" . intval($memberid) . "'");
		if ($row = $this->DB->fetch_row()) {
			if ($row['pp_main_photo']) {
				if($thumb === true && $row['pp_thumb_photo']) {
					$photo = '<a href="'.$this->ips->vars['upload_url'].'/'.$row['pp_main_photo'].'"><img src="'.$this->ips->vars['upload_url'].'/'.$row['pp_thumb_photo'].'" width="'.$row['pp_thumb_width'].'" height="'.$row['pp_thumb_height'].'" alt="'.$this->id2displayname($memberid).'" /></a>';
				} else {
					$photo = '<img src="'.$this->ips->vars['upload_url'].'/'.$row['pp_main_photo'].'" width="'.$row['pp_main_width'].'" height="'.$row['pp_main_height'].'" alt="'.$this->id2displayname($memberid).'" />';
				}
				return $photo;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

	function get_member_pips($id = '') {
		if (!$id) {
			$id = $this->ips->member['id'];
		}

		if ($info = $this->get_advinfo($id)) {
			// Grab Pips
			$this->DB->query ('SELECT * FROM ibf_titles ORDER BY pips ASC');
			$pips = '0';
			// Loop through pip numbers checking which is good
			while ($row = $this->DB->fetch_row()) {
				if ($row['posts'] <= $info['posts']) {
					$pips = $row['pips'];
				}
			}

			return $pips;
		} else {
			$this->Error($this->lang['zone_badmemid']);
			return FALSE;
		}
	}

	/**
	 * Returns a member's icon in HTML
	 *
	 * @param integer $id
	 * @return string HTML for member's icon
	 * @see get_info(),get_advinfo(),get_avatar(),get_raw_sig(),get_photo(),get_member_pips(),get_num_new_posts(),get_skin_id()
	 */
	function get_member_icon($memberid = '') {
		if (!$memberid) {
			$memberid = $this->ips->member['id'];
		}

		if ($info = $this->get_advinfo($memberid)) {
			if ($info['g_icon']) {
				// Use Group Icon
				if(substr($info['g_icon'],0,7) == "http://")
				{
				$info['g_icon'] = '<img src="' . $info['g_icon'] . '" alt="'.$this->lang['zone_groupicon'].'" />';
				$skininfo = $this->get_skin_info($this->get_skin_id());
				$skininfo['img_dir'] = $skininfo['img_dir'] ? $skininfo['img_dir'] : '1';
				$info['g_icon'] = str_replace("<#IMG_DIR#>",$skininfo['img_dir'],$info['g_icon']);
				return $info['g_icon'];
				}
				else
				{
				$skininfo = $this->get_skin_info($this->get_skin_id());
				$skininfo['img_dir'] = $skininfo['img_dir'] ? $skininfo['img_dir'] : '1';
				$url = '<img src="' . $this->_options['board_url'] . '/' . $info['g_icon'] . '" alt="'.$this->lang['zone_groupicon'].'" />';
				$url = str_replace("<#IMG_DIR#>",$skininfo['img_dir'],$url);
				return $url;
				}
			} else {
				// Use Pips
				$pips = $this->get_member_pips($memberid);
				$pipsc = '';
				$skininfo = $this->get_skin_info($this->get_skin_id());

				while ($pips > 0) {
					$skininfo['img_dir'] = $skininfo['img_dir'] ? $skininfo['img_dir'] : '1';
					$pipsc .= '<img src="' . $this->_options['board_url'] . '/style_images/' . $skininfo['img_dir'] . '/pip.gif" alt="*" />';
					$pips = $pips - '1';
				}

				return $pipsc;
			}
		} else {
			$this->Error($this->lang['zone_badmemid']);
			return FALSE;
		}
	}

	/**
	 * Returns the number of new posts of the currently logged in member since its last visit.
	 *
	 * @author CTiga <crouchintiga@comcast.net>
	 * @return int Number of posts since last visit
	 * @see get_info(),get_advinfo(),get_avatar(),get_raw_sig(),get_photo(),get_member_pips(),get_member_icon(),get_skin_id()
	 */
	function get_num_new_posts() {
		if (!$this->is_loggedin()) {
			$this->Error($this->lang['zone_membersonly']);
			return FALSE;
		}

		$this->DB->query("SELECT COUNT(pid) AS new FROM ibf_posts WHERE post_date > '".$this->ips->member['last_visit']."'");
		if ($posts = $this->DB->fetch_row()) {
			return $posts['new'];
		}
		else {
			return FALSE;
		}
	}

	/**
	 * Lists the board's members.
	 *
	 * The following options can be used to overwrite the default query results.
	 * <br>'order' default: 'asc'
	 * <br>'start' default: '0' start with first record
	 * <br>'limit' default: '30' no. of members per page
	 * <br>'orderby' default: 'name' other keys see below
	 * <br>'group' default: '*' all groups. You can specifiy a number or list of numbers
	 *
	 * Sort keys: any field from ibf_members or ibf_groups.
	 * To avoid trouble ordering by a field 'xxx', use <b>m.XXX</b> or <b>g.XXX</b> as
	 * the full qualified fieldname, not just 'xxx'.
	 *
	 * @param array $options Overwrites default behaviour of SQL query.
	 * @return array Members
	 * @see list_online_members(), get_active_count()
	 */
	function list_members ($options = array('order' => 'asc', 'start' => '0', 'limit' => '30', 'orderby' => 'name', 'group' => '*')) {
		// Ordering
		$orders = array('id', 'name', 'posts', 'joined');
		if (!in_array($options['orderby'], $orders)) {
			$options['orderby'] = 'name';
		}
		// Order By
		$options['order'] = ($options['order'] == 'desc') ? 'DESC' : 'ASC';
		// Start and Limit
		$filter = 'LIMIT ' . intval($options['start']) . ',' . intval($options['limit']);
		// Grouping
		$where = '';
		if (is_array($options['group']) AND $options['group'] != '*') {
			foreach ($options['group'] as $i) {
				$i = (int)$i;
				if ($i > 0) {
					if ($where) {
						$where .= "OR mgroup='" . $i . "' ";
					} else {
						$where .= "mgroup='" . $i . "' ";
					}
				}
			}
		}

		if ($where) {
			$where = "WHERE m.id != '0' AND (" . $where . ')';
		} else {
			$where = "WHERE m.id != '0'";
		}

		$this->DB->query ('SELECT m.*, g.*, cf.* FROM ibf_members m LEFT JOIN ibf_groups g ON (m.mgroup=g.g_id) LEFT JOIN ibf_pfields_content cf ON (cf.member_id=m.id) ' . $where . ' ORDER BY ' . $options['orderby'] . ' ' . $options['order'] . ' ' . $filter);

		$return = array();
		while ($row = $this->DB->fetch_row()) {
			$return[$row['id']] = $row;
		}

		return $return;
	}

	/**
	 * Get an array of online members.
	 *
	 * @param bool	 $detailed - if TRUE, function returns multi-dimensional array containing the result of get_advinfo() for each member. Default FALSE - simple list.
	 * @param bool	$formatted - if TRUE, function will return an html list (string) of display names, each linked to each member's personal profile. Default FALSE - returns array.
	 * @param bool	 $show_anon - if TRUE, function will ignore logged-in member's anonymity choice. Default FALSE - normal board action.
	 * @param string	 $order_by - choose what to order the results by - choose from 'member_name', 'member_id', 'running_time', 'location'. Default "running_time".
	 * @param string	 $order - choose what order to order the results in. Options are ascending; 'ASC', or descending; 'DESC'. Default "DESC".
	 * @param string	$separator - if $formatted set to TRUE, this string will go between each linked display name. Default ", ".
	 */
	function list_online_members($detailed = FALSE, $formatted = FALSE, $show_anon = FALSE, $order_by = 'running_time', $order = 'DESC', $separator = ", ") {
		// Grab the cut-off length in minutes from the board settings
		$cutoff = $this->ips->vars['au_cutoff'] ? $this->ips->vars['au_cutoff'] : "15";
		// Create a timestamp for the current time, and subtract the cut-off length to get a timestamp in the past
		$timecutoff = time() - ($cutoff * 60);
		// if the $detailed param is TRUE, return extra info :)
		if ($detailed) {
			// if this function has already been run and has saved a cache, return the cached value from database for speed
			if ($online = $this->get_cache('list_online_members', 'nodetail')) {
				// For each key in the $online array we just read from the database, set the value to the result of get_advinfo(value)
				foreach ($online as $key => $value) {
					$online[$key] = $this->get_advinfo($value);
				}
				// Return the array which now has extra info :)
				return $online;
			}
			// if we are happy to ignore logged-in members' requests to be anonymous, we need a slightly different database query.
			if ($show_anon) {
				$this->DB->query ("SELECT member_id FROM ibf_sessions s WHERE s.member_id != '0' AND s.running_time > '{$timecutoff}' ORDER BY {$order_by} {$order}");
			} else {
				// ok so this is the normal database query which should return the member IDs of all logged-in members. It does not return guests as they have no member ID :)
				$this->DB->query ("SELECT member_id FROM ibf_sessions s WHERE s.login_type != '1' AND s.member_id != '0' AND s.running_time > '{$timecutoff}' ORDER BY {$order_by} {$order}");
			}
			// For each result from the MySQL query, add the member's ID to the $options array with the key and value both equal to the member's ID
			while ($row = $this->DB->fetch_row()) {
				$id = $row['member_id'];
				$online[$id] = $id;
			}
			// We didn't do all that just to have to do it again next time. Cache the result to the database for speed next time.
			$this->save_cache('list_online_members', 'nodetail', $online);
			
			// For each key in the $online array we just cached to the database, set the value to the result of get_advinfo(value)
			foreach ($online as $key => $value) {
				$online[$key] = $this->get_advinfo($value);
			}
			
			// Finally, return the array
			return $online;
		} elseif ($formatted) {
			// the $formatted param is TRUE, so let's return an HTML list of display name links, separated by $separator
			// if this function has already been run and has saved a cache, return the cached value from database for speed
			if ($online = $this->get_cache('list_online_members', 'formatted')) {
				// For each key in the $online array we just read from the database, set the value to the html formatted display name link
				foreach ($online as $key => $value) {
					// Grab advanced info for the member so we have the display name, prefix and suffix
					$member = $this->get_advinfo($value);
					// Create the html-formatted string
					$link = "<a href='".$this->ips->vars[board_url]."?showuser=".$value."'>".$member['prefix'].$member['members_display_name'].$member['suffix']."</a>";
					$online[$key] = $link;
				}
				// Now we have an array full of html links... But that isn't very helpful to a PHP newbie. Lets just return an html string. Implode the array with $separator
				$online = implode($separator,$online);
				return $online;
			}
			// if we are happy to ignore logged-in members' requests to be anonymous, we need a slightly different database query.
			if ($show_anon) {
				$this->DB->query ("SELECT member_id FROM ibf_sessions s WHERE s.member_id != '0' AND s.running_time > '{$timecutoff}' ORDER BY {$order_by} {$order}");
			} else {
				// ok so this is the normal database query which should return the member IDs of all logged-in members. It does not return guests as they have no member ID :)
				$this->DB->query ("SELECT member_id FROM ibf_sessions s WHERE s.login_type != '1' AND s.member_id != '0' AND s.running_time > '{$timecutoff}' ORDER BY {$order_by} {$order}");
			}
			// For each result from the MySQL query, add the member's ID to the $options array with the key and value both equal to the member's ID
			while ($row = $this->DB->fetch_row()) {
				$id = $row['member_id'];
				$online[$id] = $id;
			}
			// We didn't do all that just to have to do it again next time. Cache the result to the database for speed next time.
			$this->save_cache('list_online_members', 'formatted', $online);
			
			// For each key in the $online array we just cached to the database, set the value to the html formatted display name link
			foreach ($online as $key => $value) {
				// Grab advanced info for the member so we have the display name, prefix and suffix
				$member = $this->get_advinfo($value);
				// Create the html-formatted string
				$link = "<a href='".$this->ips->vars[board_url]."?showuser=".$value."'>".$member['prefix'].$member['members_display_name'].$member['suffix']."</a>";
				$online[$key] = $link;
			}
			// Now we have an array full of html links... But that isn't very helpful to a PHP newbie. Lets just return an html string. Implode the array with $separator
			$online = implode($separator,$online);
			
			// Finally, return the array
			return $online;
		}
				
		// neither $detailed or $formatted are TRUE, so return a simple list
		
		// if this function has already been run and has saved a cache, return the cached value from database for speed
		if ($online = $this->get_cache('list_online_members', 'simple')) {
			return $online;
		}
		// if we are happy to ignore logged-in members' requests to be anonymous, we need a slightly different database query.
		if ($show_anon) {
			$this->DB->query ("SELECT member_id FROM ibf_sessions s WHERE s.member_id != '0' AND s.running_time > '{$timecutoff}' ORDER BY {$order_by} {$order}");
		} else {
			// ok so this is the normal database query which should return the member IDs of all logged-in members. It does not return guests as they have no member ID :)
			$this->DB->query ("SELECT member_id FROM ibf_sessions s WHERE s.login_type != '1' AND s.member_id != '0' AND s.running_time > '{$timecutoff}' ORDER BY {$order_by} {$order}");
		}
		// For each result from the MySQL query, add the member's ID to the $options array with the key and value both equal to the member's ID
		while ($row = $this->DB->fetch_row()) {
			$id = $row['member_id'];
			$online[$id] = $id;
		}
		// We didn't do all that just to have to do it again next time. Cache the result to the database for speed next time.
		$this->save_cache('list_online_members', 'simple', $online);
		// Finally, return the array
		return $online;
	}

	/**
	 * Get an array of random members.
	 *
	 * @param int $limit How many Member should be returned?
	 * @return array Random Members
	 */
	function list_random_members($limit = 5,$where = false) {
		if($where)  $where = 'WHERE '.$where;
		$this->DB->query ("SELECT * FROM ibf_members m LEFT JOIN ibf_member_extra me ON (me.id=m.id)".$where." ORDER BY RAND() LIMIT ".intval($limit));
		$random = array();
		while ($row = $this->DB->fetch_row()) {
			$random[$row['id']] = $row;
		}

		return $random;
	}

	/**
	 * Returns the active user count.
	 *
	 * @return array Active User Count
	 * @see list_members(), list_online_members()
	 */
	 function get_active_count() {
		if ($cache = $this->get_cache('get_active_count', '1')) {
			return $cache;
		} else {
			// Init
			$count = array('total' => '0', 'anon' => '0', 'guests' => '0', 'members' => '0');

			$cutoff = $this->ips->vars['au_cutoff'] ? $this->ips->vars['au_cutoff'] : "15";
			$timecutoff = time() - ($cutoff * 60);

			$this->DB->query ("SELECT member_id, login_type FROM ibf_sessions WHERE running_time > '".$timecutoff."'");
			// Let's cache so we don't screw ourselves over :)
			$cached = array();
			// We need to make sure our man's in this count...
			if($this->is_loggedin()) {
				if(substr($this->ips->member['login_anonymous'],0, 1) == "1") {
					++$count['anon'];
				} else {
					++$count['members'];
				}
				$cached[$this->ips->member['id']] = 1;
			}
			while ($row = $this->DB->fetch_row()) {
				// Add up members
				if ($row['login_type'] == '1') {
					if(!array_key_exists($row['member_id'],$cached)) {
						++$count['anon'];
						$cached[$row['member_id']] = 1;
					}
				} else {
					if ($row['member_id'] == '0') {
						++$count['guests'];
					} else {
						if(!array_key_exists($row['member_id'],$cached)) {
							++$count['members'];
							$cached[$row['member_id']] = 1;
						}
					}
				}
			}

			$count['total'] = $count['anon'] + $count['guests'] + $count['members'];
			# why is "get_active_count" cached?
			$this->save_cache('get_active_count', 'detail', $count);
			return $count;
		}
	}

	/**
	 * Returns members born on the given day of a month.
	 *
	 * @param integer $day Optional. Current day is used if left as an empty string or zero.
	 * @param integer $month Optional. Current month is used if left as an empty string or zero.
	 * @return array Birthday Members
	 * @see list_members(), list_online_members()
	 */
	function get_birthday_members($day = 0, $month = 0) {
		if ((int)$day<=0) {
			$day = date('j');
		}
		if ((int)$month<=0) {
			$month = date ('n');
		}

		$this->DB->query("SELECT m.*, me.signature, me.avatar_size, me.avatar_location, me.avatar_type, me.vdirs, me.location, me.msnname, me.interests, me.yahoo, me.website, me.aim_name, me.icq_number, g.*, cf.* FROM ibf_members m LEFT JOIN ibf_groups g ON (m.mgroup=g.g_id) LEFT JOIN ibf_pfields_content cf ON (cf.member_id=m.id) LEFT JOIN ibf_member_extra me ON (m.id=me.id) WHERE m.bday_day='" . intval($day) . "' AND m.bday_month='" . intval($month) . "'");

		$return = array();
		$thisyear = date ('Y');
		while ($row = $this->DB->fetch_row()) {
			$row['age'] = $thisyear - $row['bday_year'];
			$return[] = $row;
		}

		return $return;
	}

	/**
	 * View a member's ignored users.
	 *
	 * @author Pita <peter@randomnity.com>
	 * @return array User ids
	 * @see ignore_member(), unignore_member()
	 */

	function list_ignored_members() {
		if($this->is_loggedin()) {

			$id = $this->ips->member['id'];

			if ($info = $this->DB->fetch_row($this->DB->query("SELECT ignored_users FROM ibf_members WHERE id='".$id."'"))) {
				// Slip the users into an array
				$users = explode(',',$info[0]['ignored_users']);
				// Remove empty values
				foreach($users as $value) {
					if($value) {
						$return[] = $value;
					}
				}
				// Return false if no users ignored
				if(count($return) == 0) {
					return FALSE;
				}
				// Otherwise return the user array
				else {
					return $return;
				}
			}
			else {
				return FALSE;
			}
		}
		else {
			return FALSE;
		}
	}

	/**
	 * Add a member to a user's ignore list.
	 *
	 * @param integer $ignore The user to be ignored
	 * @return array User ids
	 * @see unignore_member(), list_ignored_members()
	 */

	function ignore_member($ignore) {
		if($this->is_loggedin()) {
			$id = $this->ips->member['id'];
			$list = $this->list_ignored_members($id);
			// Check if user has already been ignored...
			if(in_array($ignore,$list)) {
				return FALSE;
			}
			// Still here? Let's put them in then...
			$string = ',';
			$list[] = $ignore;
			for($i=0;$i<count($list);$i++) {
				$string .= $list[$i] . ',';
			}
			if($this->DB->query("UPDATE ibf_members SET ignored_users='".$string."' WHERE id='".$id."'")) {
				return TRUE;
			}
			else {
				return FALSE;
			}
		}
		else {
			return FALSE;
		}
	}

	/**
	 * Add a member to a user's ignore list.
	 *
	 * @param integer $ignore The user to be unignored
	 * @return array User ids
	 * @see ignore_member(), list_ignored_members()
	 */

	function unignore_member($ignore) {
		if($this->is_loggedin()) {
			$id = $this->ips->member['id'];
			$list = $this->list_ignored_members($id);
			// Check if user has been ignored...
			if(!in_array($ignore,$list)) {
				return FALSE;
			}
			// Still here? Let's put remove them then...
			$string = ',';
			for($i=0;$i<count($list);$i++) {
				if($list[$i] != $ignore) {
					$string .= $list[$i] . ',';
				}
			}
			if($this->DB->query("UPDATE ibf_members SET ignored_users='".$string."' WHERE id='".$id."'")) {
				return TRUE;
			}
			else {
				return FALSE;
			}
		}
		else {
			return FALSE;
		}
	}

	/**
	 * Deletes a Member.
	 *
	 * @param integer $memberid Member(s) to be deleted
	 * @return bool TRUE on success, FALSE on failure
	 */
	function delete_member($memberid,$password=false) {
		// There IS a member id, right?
		if (!$memberid) {
			return FALSE;
		}

		// Are there more than one ID's?
		if(is_array($memberid)) {
			foreach($memberid as $k => $v) {
				if(!is_numeric($v)) {
					unset($memberid[$k]);
				}
			}
		}
		// The ID's gotta be numeric...
		elseif (is_numeric($memberid)) {
			$loggedin_user = $this->get_info();
		}
		else return FALSE;

		// Are YOU logged in?
		if (!$this->is_loggedin()) {
			$this->Error($this->lang['zone_membersonly']);
			return FALSE;
		}

		// Are there more than one ID's?
		if(is_array($memberid)) {
			if(!$this->is_admin()) {
				$this->Error($this->lang['zone_noperms']);
				return FALSE;
			}
			foreach($memberid as $v) {
				$user[] = $this->get_info($v);
			}
		}
		elseif(isset($memberid) && is_numeric($memberid)) {
			// Do you have permission?
			if(isset($password) && $memberid == $loggedin_user['id']) {
				$converge = $this->DB->query('SELECT converge_pass_hash,converge_pass_salt FROM ibf_members_converge WHERE converge_id = '.$memberid.'');
				$converge = $this->DB->fetch_row();
				$pw_db = $converge['converge_pass_hash'];
				$pw_delivered = md5(md5($converge['converge_pass_salt']).md5($password));
				if($pw_db != $pw_delivered) {
					$this->Error($this->lang['zone_badmempw']);
					return FALSE;
				}
			}
			elseif(!$this->is_admin()) {
				$this->Error($this->lang['zone_noperms']);
				return FALSE;
			}
			$user[] = $this->get_info($memberid);
		}

		// Let's finish the job.
		foreach($user as $v) {

			//--------------------------------------------
			// Insert: ADMIN LOGS
			//--------------------------------------------

			if($this->is_admin()) {
				$admin_logs = array( 'id' => $this->DB->get_insert_id(),
						   'act' => "mem",
						   'code' => "member_delete",
						   'member_id' => $loggedin_user['id'],
						   'ctime' => time(),
						   'note' => "Deleted Member(s) ( " .$v['id']."  )",
						   'ip_address' => $loggedin_user['ip_address']
						);

				$this->DB->force_data_type = $this->DB->no_escape_fields = array( 'id' => false,
													'act'	=> false,
													'code' => false,
													'member_id' => false,
													'ctime' => false,
													'note' => false,
																						'ip_address' => false
												);
				$this->DB->do_insert( 'admin_logs', $admin_logs );
			}

			//--------------------------------------------
			// Delete All Member Info
			//--------------------------------------------

			$this->DB->query ("DELETE FROM ibf_contacts WHERE member_id='" . $v['id'] . "' LIMIT 1");
			$this->DB->query ("DELETE FROM ibf_dnames_change WHERE dname_member_id='" . $v['id'] . "' LIMIT 1");
			$this->DB->query ("DELETE FROM ibf_members WHERE id='" . $v['id'] . "' LIMIT 1");
			$this->DB->query ("DELETE FROM ibf_members_converge WHERE converge_id='". $v['id'] ."' LIMIT 1");
			$this->DB->query ("DELETE FROM ibf_member_extra WHERE id='" . $v['id'] . "' LIMIT 1");
			$this->DB->query ("DELETE FROM ibf_message_topics WHERE mt_owner_id='" . $v['id'] . "' LIMIT 1");
			$this->DB->query ("DELETE FROM ibf_pfields_content WHERE member_id='" . $v['id'] . "' LIMIT 1");
			$this->DB->query ("DELETE FROM ibf_profile_comments WHERE comment_for_member_id='" . $v['id'] . "' LIMIT 1");
			$this->DB->query ("DELETE FROM ibf_profile_friends WHERE friends_member_id ='" . $v['id'] . "' LIMIT 1");
			$this->DB->query ("DELETE FROM ibf_profile_portal WHERE pp_member_id='" . $v['id'] . "' LIMIT 1");
			$this->DB->query ("DELETE FROM ibf_warn_logs WHERE wlog_mid='" . $v['id'] . "' LIMIT 1");

			$deleted = true;
		}
		if($deleted === true) return TRUE;
		else return FALSE;
	}

	// -----------------------------------------------
	// PRIVATE MESSAGE FUNCTIONS
	// Functions to read, send, and interact with the
	// PM and contacts system.
	// -----------------------------------------------
	/**#@+
	 * @group PrivateMessage
	 */
	/**
	 * Updates PMs-User-Cache.
	 *
	 * @author Matthias Reuter <public@pc-intern.com> http://pc-intern.com | http://straightvisions.com
	 * @return int Message owner id
	 */
	function update_pm_user_cache($owner_id) {
		$owner_id = intval($owner_id);
		$folders = $this->get_pm_folders();
		foreach($folders as $folder) {
			$SQL = 'SELECT COUNT(mt_id) AS count FROM ibf_message_topics WHERE mt_vid_folder="'.$folder['id'].'" AND mt_owner_id="'.$owner_id.'"';
			$this->DB->query($SQL);
			if ($message = $this->DB->fetch_row()) {
				$count[$folder['id']]['count'] = $message['count'];
				$count[$folder['id']]['name'] = $folder['name'];
			}
		}
		$i = 0;
		foreach($count as $id => $detail) {
			if($i > 0) $pipe = '|';
			$vdirs .= $pipe.$id.':'.$detail['name'].';'.$detail['count'];
			$i++;
		}
		if($this->DB->query('UPDATE ibf_member_extra SET vdirs="'.$vdirs.'" WHERE id="'. $owner_id.'"')) return true;
		else return false;
	}

	/**
	 * Gets total number of PMs.
	 *
	 * @return int Total Messages Count
	 */
	function get_num_total_pms() {
		if (!$this->is_loggedin() AND !$this->has_perms('g_use_pm')) {
			$this->Error($this->lang['zone_membersonly']);
			return FALSE;
		}

		$this->DB->query ("SELECT msg_total FROM ibf_members WHERE id='" . $this->ips->member['id'] . "'");
		if ($messages = $this->DB->fetch_row()) {
			return $messages['msg_total'];
		} else {
			return FALSE;
		}
	}

	/**
	 * Gets number of new PMs.
	 *
	 * @return int New Unread Messages Count
	 */
	function get_num_new_pms() {
		if (!$this->is_loggedin() AND !$this->has_perms('g_use_pm')) {
			$this->Error($this->lang['zone_membersonly']);
			return FALSE;
		}

		$this->DB->query ("SELECT new_msg FROM ibf_members WHERE id='" . $this->ips->member['id'] . "'");
		if ($messages = $this->DB->fetch_row()) {
			return (int)$messages['new_msg'];
		} else {
			return FALSE;
		}
	}

	/**
	 * Lists PMs in a folder.
	 *
	 * @param string $folder Keyname of Inbox folder, 'in', 'sent'
	 * @return array Information of PMs in folder.
	 * @see get_pm_folders()
	 */
	function list_pms($folder = 'in') {
		if (!$this->is_loggedin() AND !$this->has_perms('g_use_pm')) {
			$this->Error($this->lang['zone_membersonly']);
			return FALSE;
		}

		$pms = array();

		$this->DB->query ("SELECT m.*, t.*, s.name, r.name AS recipient_name FROM ibf_message_topics t LEFT JOIN ibf_message_text m ON (t.mt_msg_id=m.msg_id) LEFT JOIN ibf_members s ON (t.mt_from_id=s.id) LEFT JOIN ibf_members r ON (t.mt_to_id=r.id) WHERE mt_owner_id='" . $this->ips->member['id'] . "' AND mt_vid_folder='" . $folder . "' ORDER BY mt_date DESC");
		if ($this->DB->get_num_rows()) {
			$this->parser->parse_smilies = 1;
			$this->parser->parse_html = 0;
			$this->parser->parse_bbcode = 1;
			$this->parser->strip_quotes = 1;
			$this->parser->parse_nl2br = 1;
			while ($row = $this->DB->fetch_row()) {
				$row['msg_post'] = $this->parser->pre_db_parse($row['msg_post']);
				$row['msg_post'] = $this->parser->pre_display_parse($row['msg_post']);
				$row['msg_post'] = str_replace("src=\"style_emoticons/","src=\"".$this->board_url."/style_emoticons/",$row['msg_post']);
				$row['msg_post'] = str_replace("src='style_emoticons/","src='".$this->board_url."/style_emoticons/",$row['msg_post']);
				$row['msg_post'] = preg_replace("|([A-Za-z0-9\%<]+)\#EMO_DIR\#([A-Za-z0-9\%>]+)\/|U",$this->ips->skin['_emodir']."/",$row['msg_post']);
				$pms[] = $row;
			}

			return $pms;
		} else {
			return FALSE;
		}
	}

	/**
	 * Returns information on a Personal Message.
	 *
	 * @param integer $id PM record ID
	 * @param integer $markread Default: 0=keep unread, 1=mark read
	 * @param integer $convert Default: 1 convert BBCode
	 * @return array Information of a PM
	 */
	function get_pm_info($id, $markread = false, $convert = true) {
		if (!$id) {
			return FALSE;
		}
		if (!$this->is_loggedin() AND !$this->has_perms('g_use_pm')) {
			$this->Error($this->lang['zone_membersonly']);
			return FALSE;
		}

		$pminfo = array();

		$this->DB->query ("SELECT m.*, t.*, s.name, r.name AS recipient_name FROM ibf_message_topics t LEFT JOIN ibf_message_text m ON (t.mt_msg_id=m.msg_id) LEFT JOIN ibf_members s ON (t.mt_from_id=s.id) LEFT JOIN ibf_members r ON (t.mt_to_id=r.id) WHERE mt_owner_id='" . $this->ips->member['id'] . "' AND mt_id='" . intval($id) . "'");
		if ($this->DB->get_num_rows()) {
			if ($row = $this->DB->fetch_row()) {
				if ($markread AND !$row['mt_read']) {
					$this->DB->query ("UPDATE ibf_message_topics SET mt_read='1', read_date='" . time() . "' WHERE mt_msg_id='" . $id . "' AND mt_owner_id='" . $this->ips->member['id'] . "' LIMIT 1");
					if ($row['vid'] == 'in') {
						$this->DB->query ("UPDATE ibf_members SET new_msg=new_msg-1 WHERE id='" . $this->ips->member['id'] . "' AND new_msg > 0");
					}
				}

				if ($convert) {
					$this->parser->parse_smilies = 1;
					$this->parser->parse_html = 0;
					$this->parser->parse_bbcode = 1;
					$this->parser->strip_quotes = 1;
					$this->parser->parse_nl2br = 1;
					$row['msg_post'] = $this->parser->pre_db_parse($row['msg_post']);
					$row['msg_post'] = $this->parser->pre_display_parse($row['msg_post']);
					$row['msg_post'] = str_replace("src=\"style_emoticons/","src=\"".$this->board_url."/style_emoticons/",$row['msg_post']);
					$row['msg_post'] = str_replace("src='style_emoticons/","src='".$this->board_url."/style_emoticons/",$row['msg_post']);
					$row['msg_post'] = preg_replace("|([A-Za-z0-9\%<]+)\#EMO_DIR\#([A-Za-z0-9\%>]+)\/|U",$this->ips->skin['_emodir']."/",$row['msg_post']);

				}

				return $row;
			}
		} else {
			return FALSE;
		}
	}

	/**
	 * Sends a PM.
	 *
	 * @param integer $to_id MEmber ID tor eceive the message
	 * @param string $title Message title
	 * @param string $message Message body
	 * @param array $cc Array of ID for carbon copies (CC)
	 * @param integer $sentfolder Default: 0=do not save message in Sent folder, 1=save message
	 * @return bool Success.
	 * @see save_pm();
	 */
	function write_pm($to_id, $title, $message, $cc = array(), $sentfolder = '0') {
		if (!$this->is_loggedin()) {
			$this->Error($this->lang['zone_membersonly']);
			return FALSE;
		}
		if (!$to_id) {
			$this->Error($this->lang['zone_pm_no_recipient']);
			return FALSE;
		}
		if (!$title OR strlen($title) < 2) {
			$this->Error($this->lang['zone_pm_title']);
			return FALSE;
		}
		if (!$message OR strlen($message) < 2) {
			$this->Error($this->lang['zone_pm_message']);
			return FALSE;
		}

		$sendto = array();

		$this->DB->query("SELECT m.name, m.id, m.view_pop, m.mgroup, m.email_pm, m.language, m.email, m.msg_total, g.g_use_pm, g.g_max_messages FROM ibf_groups g, ibf_members m WHERE m.id='" . intval($to_id) . "' AND g.g_id=m.mgroup");
		if ($row = $this->DB->fetch_row()) {
			// Just incase
			if (!$row['id']) {
				$this->Error($this->lang['zone_pm_mem_notexist']);
				return FALSE;
			}
			// Permissions Check
			if (!$this->has_perms('g_use_pm',$row['id'])) {
				$this->Error($this->lang['zone_pm_mem_disallowed']);
				return FALSE;
			}
			// Space Check
			$space = $this->best_perms('g_max_messages',$row['id']);
			if ($row['msg_total'] >= $space AND $space > 0) {
				$this->Error($this->lang['zone_pm_mem_full']);
				return FALSE;
			}
			// Block Check
			if ($this->is_pmblocked($this->ips->member['id'], intval($to_id))) {
				$this->Error($this->lang['zone_pm_mem_blocked']);
				return FALSE;
			}
			// CC Users
			$ccusers = array();
			$max = $this->best_perms('g_max_mass_pm','',false);
			if ($max) {
				if (is_array($cc) AND count($cc) > 0) {
					if (count($cc) > $max) {
						$this->Error($this->lang['zone_pm_cclimit']);
						return FALSE;
					}

					foreach ($cc AS $i) {
						// Check CC user stuff
						// I really should clean up the code here, it uses alot of queries in some cases, which isn't good. Should really merge this with the main sending message code instead of replicating stuff for CCs.
						$this->DB->query("SELECT m.name, m.id, m.view_pop, m.mgroup, m.email_pm, m.language, m.email, m.msg_total, g.g_use_pm, g.g_max_messages FROM ibf_groups g, ibf_members m WHERE m.id='" . intval($to_id) . "' AND g.g_id=m.mgroup");
						if ($ccrow = $this->DB->fetch_row()) {
							// Permissions Check
							if (!$this->has_perms('g_use_pm',$ccrow['id'])) {
								$this->Error($this->lang['zone_pm_rec_disallowed']);
								return FALSE;
							}
							// Space Check
							$space = $this->best_perms('g_max_messages',$ccrow['id']);
							if ($ccrow['msg_total'] >= $space AND $space > 0) {
								$this->Error($this->lang['zone_pm_rec_full']);
								return FALSE;
							}
							// Block Check
							if ($this->is_pmblocked($this->ips->member['id'], intval($ccrow['id']))) {
								$this->Error($this->lang['zone_pm_rec_blocked']);
								return FALSE;
							}
						}

						$ccusers[] = intval($i);
					}
				}
			}
			// Actually send it
			// IPB is a total pain in the butt, hence we need to now change the IDs to names, and stick some <br> in it.
			if (is_array($ccusers) AND count($ccusers) > 1) {
				$ccsql = implode('<br/>', strtolower($this->id2name($ccusers)));
			} elseif (is_array($ccusers) AND count($ccusers) == '1') {
				$ccsql = strtolower($this->id2name($ccusers['0']));
			} else {
				$ccsql = '';
			}
			$ccusers[] = intval($to_id);
			$msgtxtstring = $this->DB->compile_db_insert_string(array('msg_author_id' => $this->ips->member['id'],
						'msg_date' => time(),
						'msg_post' => $this->ips->remove_tags($message),
						'msg_sent_to_count' => count($ccusers),
						'msg_deleted_count' => 0,
						'msg_post_key' => md5(microtime()),
						'msg_cc_users' => $ccsql
						));
			// Insert singular text entry
			$this->DB->query ('INSERT INTO ibf_message_text (' . $msgtxtstring['FIELD_NAMES'] . ') VALUES (' . $msgtxtstring['FIELD_VALUES'] . ')');
			$c = $this->DB->get_insert_id();

			foreach ($ccusers as $recipient) {
				$DBstring = $this->DB->compile_db_insert_string(array('mt_owner_id' => $recipient,
						'mt_date' => time(),
						'mt_read' => '0',
						'mt_title' => $title,
						'mt_from_id' => $this->ips->member['id'],
						'mt_vid_folder' => 'in',
						'mt_to_id' => $recipient,
						'mt_tracking' => '0',
						'mt_msg_id' => $c
						));
				$this->DB->query ('INSERT INTO ibf_message_topics (' . $DBstring['FIELD_NAMES'] . ') VALUES (' . $DBstring['FIELD_VALUES'] . ')');
				unset($this->DBstring);
				unset($this->msgtxtstring);

				$this->DB->query("UPDATE ibf_members SET msg_total = msg_total + 1, new_msg = new_msg + 1, show_popup='1' WHERE id='" . $recipient . "'");
			}

			if ($sentfolder) {
				$DBstring = $this->DB->compile_db_insert_string(array('mt_owner_id' => $this->ips->member['id'],
						'mt_date' => time(),
						'mt_read' => '0',
						'mt_title' => 'Sent: ' . $title,
						'mt_from_id' => $this->ips->member['id'],
						'mt_vid_folder' => 'sent',
						'mt_to_id' => $recipient,
						'mt_tracking' => '0',
						'mt_msg_id' => $c
						));
				$this->DB->query ('INSERT INTO ibf_message_topics (' . $DBstring['FIELD_NAMES'] . ') VALUES (' . $DBstring['FIELD_VALUES'] . ')');
				unset($this->DBstring);
			}
			$this->update_pm_user_cache($this->ips->member['id']);
			return TRUE;
		} else {
			$this->Error($this->lang['zone_pm_mem_notexist']);
			return FALSE;
		}
	}

	/**
	 * Saves a PM to the sent folder without sending it.
	 *
	 * @param integer $to_id Member ID to receive the message
	 * @param string $title Message title
	 * @param string $message Message body
	 * @param array $cc Array of ID for carbon copies (CC)
	 * @return bool Success.
	 * @see write_pm();
	 */
	function save_pm($to_id, $title, $message, $cc = array()) {
		// Similar to Write PM but code modified for saving
		if (!$this->is_loggedin()) {
			$this->Error($this->lang['zone_membersonly']);
			return FALSE;
		}
		if (!$to_id) {
			$this->Error($this->lang['zone_pm_no_recipient']);
			return FALSE;
		}
		if (!$title OR strlen($title) < 2) {
			$this->Error($this->lang['zone_pm_title']);
			return FALSE;
		}
		if (!$message OR strlen($message) < 2) {
			$this->Error($this->lang['zone_pm_message']);
			return FALSE;
		}

		$sendto = array();

		$this->DB->query("SELECT m.name, m.id, m.view_pop, m.mgroup, m.email_pm, m.language, m.email, m.msg_total, g.g_use_pm, g.g_max_messages FROM ibf_groups g, ibf_members m WHERE m.id='" . intval($to_id) . "' AND g.g_id=m.mgroup");
		if ($row = $this->DB->fetch_row()) {
			// Just incase
			if (!$row['id']) {
				$this->Error($this->lang['zone_pm_mem_notexist']);
				return FALSE;
			}
			// Permissions Check
			if (!$this->has_perms('g_use_pm',$row['id'])) {
				$this->Error($this->lang['zone_pm_mem_disallowed']);
				return FALSE;
			}
			// Space Check
			$space = $this->best_perms('g_max_messages',$row['id']);
			if ($row['msg_total'] >= $space AND $space > 0) {
				$this->Error($this->lang['zone_pm_mem_full']);
				return FALSE;
			}
			// Block Check
			if ($this->is_pmblocked($this->ips->member['id'], intval($to_id))) {
				$this->Error($this->lang['zone_pm_mem_blocked']);
				return FALSE;
			}
			// CC Users
			$ccusers = array();
			$max = $this->has_perms('g_max_mass_pm','',false);
			if ($max) {
				if (is_array($cc) AND count($cc) > 0) {
					if (count($cc) > $max) {
						$this->Error($this->lang['zone_pm_cclimit']);
						return FALSE;
					}

					foreach ($cc AS $i) {
						// Check CC user stuff
						// I really should clean up the code here, it uses alot of queries in some cases, which isn't good. Should really merge this with the main sending message code instead of replicating stuff for CCs.
						$this->DB->query("SELECT m.name, m.id, m.view_pop, m.mgroup, m.email_pm, m.language, m.email, m.msg_total, g.g_use_pm, g.g_max_messages FROM ibf_groups g, ibf_members m WHERE m.id='" . intval($to_id) . "' AND g.g_id=m.mgroup");
						if ($ccrow = $this->DB->fetch_row()) {
							// Permissions Check
							if (!$this->has_perms('g_use_pm',$ccrow['id'])) {
								$this->Error($this->lang['zone_pm_rec_disallowed']);
								return FALSE;
							}
							// Space Check
							$space = $this->best_perms('g_max_messages',$ccrow['id']);
							if ($ccrow['msg_total'] >= $space AND $space > 0) {
								$this->Error($this->lang['zone_pm_rec_full']);
								return FALSE;
							}
							// Block Check
							if ($this->is_pmblocked($this->ips->member['id'], intval($ccrow['id']))) {
								$this->Error($this->lang['zone_pm_rec_blocked']);
								return FALSE;
							}
						}
						$ccusers[] = intval($i);

					}
				}
			}
			// IPB is a total pain in the butt, hence we need to now change the IDs to names, and stick some <br> in it.
			if (is_array($ccusers) AND count($ccusers) > 1) {
				$ccsql = implode('\n', $this->id2name($ccusers));
			} elseif (is_array($ccusers) AND count($ccusers) == '1') {
				$ccsql = $this->id2name($ccusers['0']);
			} else {
				$ccsql = '';
			}

				$msgtxtstring = $this->DB->compile_db_insert_string(array('msg_author_id' => $this->ips->member['id'],
						'msg_date' => time(),
						'msg_post' => $this->ips->remove_tags($message),
						'msg_sent_to_count' => count($ccusers) + 1,
						'msg_deleted_count' => 0,
						'msg_post_key' => 0,
						'msg_cc_users' => $ccsql
						));
				// Insert
				$this->DB->query ('INSERT INTO ibf_message_text (' . $msgtxtstring['FIELD_NAMES'] . ') VALUES (' . $msgtxtstring['FIELD_VALUES'] . ')');
				$c = $this->DB->get_insert_id();
				$DBstring = $this->DB->compile_db_insert_string(array('mt_owner_id' => $this->ips->member['id'],
						'mt_date' => time(),
						'mt_read' => '0',
						'mt_title' => $title,
						'mt_from_id' => $this->ips->member['id'],
						'mt_vid_folder' => 'unsent',
						'mt_to_id' => $to_id,
						'mt_tracking' => '0',
						'mt_msg_id' => $c
						));
				$this->DB->query ('INSERT INTO ibf_message_topics (' . $DBstring['FIELD_NAMES'] . ') VALUES (' . $DBstring['FIELD_VALUES'] . ')');
				unset($this->DBstring);
				unset($this->msgtxtstring);
			$this->update_pm_user_cache($this->ips->member['id']);
			return TRUE;
		} else {
			$this->Error($this->lang['zone_pm_mem_notexist']);
			return FALSE;
		}
	}

	/**
	 * Deletes a Personal Message.
	 *
	 * @param integer $messageid Message to be deleted
	 * @return bool Success.
	 */
	function delete_pm($messageid) {
		if (!$messageid) {
			return FALSE;
		}

		if (!$this->is_loggedin()) {
			$this->Error($this->lang['zone_membersonly']);
			return FALSE;
		}

		$this->DB->query ("SELECT * FROM ibf_message_topics WHERE mt_owner_id='" . $this->ips->member['id'] . "' AND mt_id='" . $messageid . "'");
		if ($row = $this->DB->fetch_row()) {
			$this->DB->query ("DELETE FROM ibf_message_text WHERE msg_id='" . $row['mt_msg_id'] . "' LIMIT 1");
			$this->DB->query ("DELETE FROM ibf_message_topics WHERE mt_id='". $messageid ."' AND mt_owner_id='" . $this->ips->member['id'] . "' LIMIT 1");
			if ($row['mt_vid_folder'] != 'unsent') {
				$this->DB->query ("UPDATE ibf_members SET msg_total = msg_total - 1 WHERE id='" . $this->ips->member['id'] . "'");
			}
			$this->update_pm_user_cache($this->ips->member['id']);
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Returns whether a member has blocked another member.
	 *
	 * @param integer $by Member ID of receiver (the one who blocked)
	 * @param integer $blocked Member ID of sender (the one who is blocked)
	 * @return bool Block Status
	 */
	function is_pmblocked($by, $blocked) {
		$this->DB->query ("SELECT id, allow_msg FROM ibf_contacts WHERE contact_id='" . $blocked . "' AND member_id='" . $by . "'");
		if ($cando = $this->DB->fetch_row()) {
			if($cando['allow_msg'] == 0) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

	/**
	 * Returns number of PMs in a folder.
	 *
	 * @param integer $folder Folder ID
	 * @return int Number of PMs in Folder
	 */
	function get_num_folder_pms($folder) {
		if (!$this->is_loggedin() AND !$this->has_perms('g_use_pm')) {
			$this->Error($this->lang['zone_membersonly']);
			return FALSE;
		}
		$this->DB->query ("SELECT COUNT(mt_msg_id) AS messages FROM ibf_message_topics WHERE mt_owner_id='" . $this->ips->member['id'] . "' AND mt_vid_folder='" . $folder . "'");
		if ($messages = $this->DB->fetch_row()) {
			return $messages['messages'];
		} else {
			return FALSE;
		}
	}

	/**
	 * Returns number of unread PMs in a folder.
	 *
	 * @param integer $folder Folder ID
	 * @return int Number of unread PMs in Folder
	 */
	function get_num_folder_unread_pms($folder) {
		if ($cache = $this->get_cache('get_num_folder_unread_pms', $folder)) {
			return $cache;
		}
		if (!$this->is_loggedin() AND !$this->has_perms('g_use_pm')) {
			$this->Error($this->lang['zone_membersonly']);
			return FALSE;
		}

		$this->DB->query ("SELECT COUNT(mt_msg_id) AS messages FROM ibf_message_topics WHERE mt_owner_id='" . $this->ips->member['id'] . "' AND mt_vid_folder='" . $folder . "' AND mt_read='0'");
		if ($messages = $this->DB->fetch_row()) {
			// Save In Cache and Return
			$this->save_cache('get_num_folder_unread_pms', $folder, $messages['messages']);

			return $messages['messages'];
		} else {
			return FALSE;
		}
	}

	/**
	 * Returns PM space usage in percentage.
	 *
	 * @return int PM Space Usage in Percent
	 */
	function get_pm_space_usage() {
		$pms = $this->get_num_total_pms();
		$maximumpms = $this->best_perms('g_max_messages');
		// Remove possible division by zero...
		if($maximumpms == 0) {
			return 0;
		}
		$percent = round(($pms / $maximumpms) * 100);
		return $percent;
	}

	/**
	 * Returns the current user's PM folders.
	 *
	 * @return array Current user's PM System Folders
	 */
	function get_pm_folders() {
		if ($this->is_loggedin() AND $this->has_perms('g_use_pm')) {

			$folders = array();

			$this->DB->query ("SELECT vdirs FROM ibf_member_extra WHERE id='" . $this->ips->member['id'] . "'");

			if ($row = $this->DB->fetch_row()) {
				$row['vdirs'] = $row['vdirs'] ? $row['vdirs'] : 'in:Inbox|sent:Sent Items';
				$i = explode ('|', $row['vdirs']);
				foreach ($i as $j) {
					$folder = array();
					$k = explode (':', $j);
					$l = explode(';', $k[1]);
					$folder['id'] = $k[0];
					$folder['name'] = $l[0];
					$folder['count'] = $l[1];
					$folders[] = $folder;
				}

				return $folders;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

	/**
	 * Returns whether a PM folder exists for a given member.
	 * If $memberid is ommited, the last known member is used.
	 *
	 * @param integer $folder Folder ID
	 * @param integer $memberid
	 * @return bool Folder Existance Status
	 */
	function pm_folder_exists ($folder, $memberid = '') {
		// Inbox and Sent Items are Good
		if ($folder == 'in' OR $folder == 'sent') {
			return TRUE;
		}
		// 'unsent' should be an bad folder name anyway, but put this so as not to screw up other functions
		if ($folder == 'unsent') {
			return FALSE;
		}

		$folderids = array();

		if ($memberid) {
			$memberinfo = $this->get_advinfo($memberid);
		} else {
			$memberinfo = $this->get_advinfo();
		}

		$folders = $memberinfo['vdirs'];

		$folderslist = explode ('|', $folders);

		foreach ($folderslist as $i) {
			$j = explode (':', $i);
			$folderids[] = $j['0'];
		}

		if (in_array($folder, $folderids)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Returns folder name associated with folder id of a member.
	 * If $memberid is ommited, the last known member is used.
	 *
	 * @param integer $folder Folder ID
	 * @param integer $memberid
	 * @return string Folder Name associated with id
	 */
	function pm_folderid2name($id, $memberid = '') {
		if ($memberid) {
			$memberinfo = $this->get_advinfo($memberid);
		} else {
			$memberinfo = $this->get_advinfo();
		}

		$folders = $memberinfo['vdirs'];
		$list = explode ('|', $folders);

		foreach ($list as $i) {
			$j = explode (':', $i);
			$foldersinfo[$j['0']] = $j['1'];
		}

		if ($foldersinfo[$id]) {
			$name = explode(';',$foldersinfo[$id]);
			return $name[0];
		} else {
			return FALSE;
		}
	}

	/**
	 * Creates a personal message folder.
	 *
	 * @param string $name Foldername
	 * @return bool Success
	 */
	function add_pm_folder($name) {
		if ($this->is_loggedin()) {
			// Get Folders
			$folders = $this->get_pm_folders();
			$info = $this->get_advinfo();
			$foldersi = array();

			foreach ($folders as $i) {
				$foldersi[$i['0']] = $i['1'];
			}

			$foldersno = count($folders);
			// Just to check
			if (!$foldersi['dir_' . $foldersno]) {
				$newfolders = $info['vdirs'] . '|dir_' . $foldersno . ':' . $name;
				$this->DB->query ("UPDATE ibf_member_extra SET vdirs='" . $newfolders . "' WHERE id='" . $this->ips->member['id'] . "' LIMIT 1");
				return 'dir_' . $foldersno;
			} else {
				// Just incase
				while ($foldersno < 100) {
					if (!$foldersi['dir_' . $foldersno]) {
						$newfolders = $info['vdirs'] . '|dir_' . $foldersno . ':' . $name;
						$this->DB->query ("UPDATE ibf_member_extra SET vdirs='" . $newfolders . "' WHERE id='" . $this->ips->member['id'] . "' LIMIT 1");
						return 'dir_' . $foldersno;
					}

					++$foldersno;
				}

				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

	/**
	 * Renames a personal message folder.
	 *
	 * @param integer $folderid Folder ID
	 * @param string $newname well ...
	 * @return bool Success
	 */
	function rename_pm_folder($folderid, $newname) {
		if (!$newname) {
			return FALSE;
		}

		if ($this->is_loggedin()) {
			// Get Folders
			$folders = $this->get_pm_folders();
			$info = $this->get_advinfo();
			$foldersi = array();

			foreach ($folders as $i) {
				$foldersi[$i['id']] = $i['name'];
			}
			// Check it exists
			if ($foldersi[$folderid]) {
				$foldersi[$folderid] = $newname;

				$newf = array();

				foreach ($folders as $i => $m) {
					$newf[] = $m['id'].':'.$foldersi[$m['id']].';'.$m['count'];
				}
				$newfolders = implode ('|', $newf);

				// Rename the Folder
				$this->DB->query ("UPDATE ibf_member_extra SET vdirs='" . $newfolders . "' WHERE id='" . $this->ips->member['id'] . "'");
				$this->update_pm_user_cache($this->ips->member['id']);
				return TRUE;
			} else {
				$this->Error($this->lang['zone_pm_folder_noexist']);
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

	/**
	 * Empties PMs in a personal message folder.
	 *
	 * @param integer $folderid
	 * @param integer $keepunread Default: 0=also delete unread msgs, 1=keep unread messages
	 * @return bool Success
	 */
	function empty_pm_folder($folderid, $keepunread = '0') {
		if ($this->is_loggedin()) {
			if ($this->pm_folder_exists($folderid)) {
				if ($keepunread) {
					// Just so we can decrement total
					$this->DB->query ("SELECT COUNT(mt_id) AS messagescount FROM ibf_message_topics WHERE mt_vid_folder='" . $folderid . "' AND mt_owner_id='" . $this->ips->member['id'] . "' AND mt_read='1'");
					$row = $this->DB->fetch_row();
					$del = $row['messagescount'];
					// Get message text ids...
					$this->DB->query ("SELECT mt_msg_id FROM ibf_message_topics WHERE mt_vid_folder='" . $folderid . "' AND mt_owner_id='" . $this->ips->member['id'] . "' AND mt_read='1'");
					// Delete from text
					while($row = $this->DB->fetch_row())
					{
						$this->DB->query ("DELETE FROM ibf_message_text WHERE msg_id = '".$row['mt_msg_id']."'");
					}
					// Delete from topics
					$this->DB->query ("DELETE FROM ibf_message_topics WHERE mt_vid_folder='" . $folderid . "' AND mt_owner_id='" . $this->ips->member['id'] . "' AND mt_read='1'");
					// Update Total
					$this->DB->query("UPDATE ibf_members SET msg_total=msg_total-" . intval($del) . " WHERE id='" . $this->ips->member['id'] . "' LIMIT 1");
					// Update Cache
					$this->update_pm_user_cache($this->ips->member['id']);

					return $del;
				} else {
					// Just so we can decrement total
					$this->DB->query ("SELECT COUNT(mt_id) AS messagescount FROM ibf_message_topics WHERE mt_vid_folder='" . $folderid . "' AND mt_owner_id='" . $this->ips->member['id'] . "'");
					$row = $this->DB->fetch_row();
					$del = $row['messagescount'];
					// Get message text ids...
					$this->DB->query ("SELECT mt_msg_id FROM ibf_message_topics WHERE mt_vid_folder='" . $folderid . "' AND mt_owner_id='" . $this->ips->member['id'] . "' AND mt_read='1'");
					// Delete from text
					while($row = $this->DB->fetch_row())
					{
						$this->DB->query ("DELETE FROM ibf_message_text WHERE msg_id = '".$row['mt_msg_id']."'");
					}
					// Delete from topics
					$this->DB->query ("DELETE FROM ibf_message_topics WHERE mt_vid_folder='" . $folderid . "' AND mt_owner_id='" . $this->ips->member['id'] . "'");
					// Update Total
					$this->DB->query("UPDATE ibf_members SET msg_total=msg_total-" . intval($del) . " WHERE id='" . $this->ips->member['id'] . "' LIMIT 1");
					// Update Cache
					$this->update_pm_user_cache($this->ips->member['id']);

					return $del;
				}
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

	/**
	 * Removes a personal message folder.
	 *
	 * @param integer $folderid
	 * @return bool Success
	 */
	function remove_pm_folder($folderid) {
		// DANGER! DANGER!
		if ($this->is_loggedin()) {
			$folders = $this->get_pm_folders();
			$foldersi = array();

			if ($this->pm_folder_exists($folderid)) {
				// Check if it's Inbox or Sent Items
				if ($folderid != 'in' AND $folderid != 'sent') {
					// Good. Now, try and delete the messages firstly.
					$this->empty_pm_folder ($folderid, 0);
					// Now Delete the Folder
					foreach ($folders as $m => $i) {
						if ($i['id'] != $folderid) {
							$cur = $i['id'].':'.$i['name'].';'.$i['count'];
							$foldersi[$i['id']] = $cur;
						}
					}
					$newvids = implode ('|', $foldersi);

					$this->DB->query ("UPDATE ibf_member_extra SET vdirs='" . $newvids . "' WHERE id='" . $this->ips->member['id'] . "' LIMIT 1");

					return TRUE;
				} else {
					$this->Error($this->lang['zone_pm_folder_norem']);
					return FALSE;
				}
			} else {
				$this->Error($this->lang['zone_pm_folder_noexist']);
				return FALSE;
			}
		} else {
			$this->Error($this->lang['zone_membersonly']);
			return FALSE;
		}
	}

	/**
	 * Marks a message read/unread.
	 *
	 * @param integer $msg_id
	 * @param integer $isread Default: 1=mark read, 0=mark unread
	 * @return bool Success
	 */
	function pm_mark_message ($msg_id, $isread = '1') {
		if ($this->is_loggedin()) {
			$pm = $this->get_pm_info($msg_id);
			if ($isread && $pm['mt_read'] != 1) {
				$this->DB->query("UPDATE ibf_members SET new_msg = new_msg-1 WHERE id='".$this->ips->member['id']."'");
				$this->DB->query ("UPDATE ibf_message_topics SET mt_read='1' WHERE mt_owner_id='" . $this->ips->member['id'] . "' AND mt_id='" . intval($msg_id) . "'");
				// Return success
				if ($this->DB->get_affected_rows()) {
					return TRUE;
				} else {
					return FALSE;
				}
			} elseif(!$isread && $pm['mt_read'] == 1) {
				$this->DB->query("UPDATE ibf_members SET new_msg = new_msg+1 WHERE id='".$this->ips->member['id']."'");
				$this->DB->query ("UPDATE ibf_message_topics SET mt_read='0' WHERE mt_owner_id='" . $this->ips->member['id'] . "' AND mt_id='" . intval($msg_id) . "'");
				// Return success
				if ($this->DB->get_affected_rows()) {
					return TRUE;
				} else {
					return FALSE;
				}
			}
		} else {
			return FALSE;
		}
	}

	/**
	 * Moves a personal message to another folder.
	 *
	 * @param integer $messageid Message ID to be moved
	 * @param integer $targetid Target folder ID.
	 * @return bool Success
	 */
	function pm_move_message($messageid, $targetid) {
		if ($this->is_loggedin()) {
			// Grab PM Info
			if ($info = $this->get_pm_info($messageid, 0)) {
				// Check the Dest Folder Exists
				if ($this->pm_folder_exists($targetid)) {
					$this->DB->query ("UPDATE ibf_message_topics SET mt_vid_folder='" . $targetid . "' WHERE mt_id='" . $messageid . "' AND mt_owner_id='" . $this->ips->member['id'] . "' LIMIT 1");
					if ($this->DB->get_affected_rows()) {
						// If you move an unread message from inbox
						if ($info['vid'] == 'in' AND $info['read_state'] == '0') {
							$this->DB->query ("UPDATE ibf_members SET new_msg = new_msg - 1 WHERE id='" . $this->ips->member['id'] . "'");
						}
						// And if you move a unread message to the inbox
						else if ($targetid == 'in' AND $info['read_state'] == '0') {
							$this->DB->query ("UPDATE ibf_members SET new_msg = new_msg + 1 WHERE id='" . $this->ips->member['id'] . "'");
						}

						return TRUE;
					} else {
						$this->Error($this->lang['zone_pm_msg_no_move']);
						return FALSE;
					}
				} else {
					$this->Error($this->lang['zone_pm_folder_tnoexist']);
					return FALSE;
				}
			} else {
				$this->Error($this->lang['zone_pm_msg_no_move']);
				return FALSE;
			}
		} else {
			$this->Error($this->lang['zone_membersonly']);
			return FALSE;
		}
	}

	/**
	 * Returns information on the current user's contacts.
	 *
	 * @return array Friends Informations
	 */
	function get_friends_list($details = false,$memberid = false,$unapproved = false) {

			// check for memberid
			if(is_int($memberid)) $member = intval($memberid);
			elseif($this->is_loggedin()) $member = $this->ips->member['id'];
			else return false;

			// check if unapproved
			if(empty($unapproved)) $approved = ' AND friends_approved="1"';

			$this->DB->query ('SELECT * FROM ibf_profile_friends WHERE friends_member_id="'.$member.'"'.$approved);

			$friends = array();
			while ($row = $this->DB->fetch_row()) {
				$friends[$row['friends_id']] = $row;
			}

			// check for details
			if($details === true) {
				foreach($friends as $friend) {
					$friends[$friend['friends_id']]['details'] = $this->get_advinfo($friend['friends_friend_id']);
				}
			}

			return $friends;
	}

	/**
	 * Returns blocked members information.
	 *
	 * @return array Blocked Members Information
	 */
	function get_blocked_list() {
		if ($this->is_loggedin()) {
			$this->DB->query ("SELECT contact_id, contact_desc, contact_name FROM ibf_contacts WHERE member_id='" . $this->ips->member['id'] . "' AND allow_msg='0'");
			$blocked = array();
			while ($row = $this->DB->fetch_row()) {
				$blocked[$row['contact_id']] = $row;
			}

			return $blocked;
		} else {
			return FALSE;
		}
	}

	/**
	 * Adds a friend
	 *
	 * @param integer $userid Member ID to be added
	 * @return boolean true on success
	 */
	function add_friend($userid) {
		if ($this->is_loggedin()) {
			// Check user exists
			if (!$userid OR !$this->get_info(intval($userid))) {
				return false;
			}
			// o_O. Firstly check if there is already an entry.
			$this->DB->query ('SELECT * FROM ibf_profile_friends WHERE friends_friend_id="'.intval($userid).'"AND friends_member_id="'.$this->ips->member['id'].'"');
			if ($row = $this->DB->fetch_row()) {
				return true;
			} else {
				// We can just add an entry because theres nothing there.
				$friend = $this->get_info($userid);
				// support for moderate_friends-field have to be added (including sending confirmation message)
				//if ($friend['pp_setting_moderate_friends']) $friends_approved = 0; else $friends_approved = 1;
				$friends_approved = 1;
				if($this->DB->query('INSERT INTO ibf_profile_friends VALUES ("", "'.$this->ips->member['id'].'","'.intval($userid).'","'.$friends_approved.'", "'.time().'")')) {
					// recache
					$this->ips->pack_and_update_member_cache($this->ips->member['id'], array('friends' => $this->get_friends_list()));
					$this->ips->pack_and_update_member_cache(intval($userid), array( 'friends' => $this->get_friends_list(false,$userid)));
				}
				return true;
			}
		} else {
			return false;
		}
	}

	/**
	 * Blocks a contact.
	 *
	 * @param integer $userid Member ID to be added
	 * @param string $description Description for the 'Buddy'
	 * @return bool Success
	 */
	function block_contact($userid, $description) {
		if ($this->is_loggedin()) {
			// Check user exists
			if (!$userid OR !$this->get_info(intval($userid))) {
				return FALSE;
			}
			// o_O. Firstly check if there is already an entry.
			$this->DB->query ("SELECT * FROM ibf_contacts WHERE contact_id='" . intval($userid) . "' AND member_id='" . $this->ips->member['id'] . "'");
			if ($row = $this->DB->fetch_row()) {
				if ($row['allow_msg'] == '0' AND $row['contact_desc'] == $description) {
					// Clearly no point of doing anything.
					return TRUE;
				} else {
					// Update record
					$this->DB->query ("UPDATE ibf_contacts SET allow_msg='0', contact_desc='" . $description . "' WHERE contact_id='" . intval($userid) . "' AND member_id='" . $this->ips->member['id'] . "'");
					return TRUE;
				}
			} else {
				// We can just add an entry because theres nothing there.
				$this->DB->query ("INSERT INTO ibf_contacts VALUES ('', '" . intval($userid) . "', '" . $this->ips->member['id'] . "', '" . $this->id2name(intval($userid)) . "', '1', '" . $description . "')");
				return TRUE;
			}
		} else {
			return FALSE;
		}
	}

	/**
	 * Removes a friend
	 *
	 * @param integer $userid Member ID to be deleted
	 * @return boolean true on success
	 */
	function remove_friend($userid) {
		if ($this->is_loggedin()) {
			$this->DB->query ('DELETE FROM ibf_profile_friends WHERE friends_friend_id="'.intval($userid).'" AND friends_member_id="'.$this->ips->member['id'].'"');
			if ($this->DB->get_affected_rows()) {
				// recache
				$this->ips->pack_and_update_member_cache($this->ips->member['id'], array('friends' => $this->get_friends_list()));
				$this->ips->pack_and_update_member_cache(intval($userid), array( 'friends' => $this->get_friends_list(false,$userid)));
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	// -----------------------------------------------
	// POLL FUNCTIONS
	// -----------------------------------------------
	/**#@+
	 * @group Polls
	 */
	/**
	 * Returns whether a member has voted in the poll in a topic.
	 * If $memberid is ommitted the last known member is used.
	 *
	 * @param integer $topicid
	 * @param integer $memberid
	 * @return mixed Poll Vote Date if voted, FALSE otherwise
	 */
	function poll_voted($topicid, $memberid = '') {
		if (!$memberid) {
			$memberid = $this->ips->member['id'];
		}
		// Query
		$this->DB->query ("SELECT vote_date FROM ibf_voters WHERE tid='" . $topicid . "' AND member_id='" . $memberid . "'");
		if ($row = $this->DB->fetch_row()) {
			return $row['vote_date'];
		} else {
			return FALSE;
		}
	}

	/**
	 * Returns information on a poll.
	 *
	 * @param integer $topicid
	 * @return array Poll Information
	 */
	function get_poll_info($topicid) {
		if ($cache = $this->get_cache('get_poll_info', $topicid)) {
			return $cache;
		} else {
			// Query
			$this->DB->query ("SELECT p.pid, p.tid, p.start_date, p.choices, p.starter_id, m.name AS starter_name, p.votes, p.forum_id, p.poll_question FROM ibf_polls p LEFT JOIN ibf_members m ON (p.starter_id=m.id) WHERE p.tid='" . $topicid . "'");

			if ($row = $this->DB->fetch_row()) {
				$choices = unserialize(stripslashes($row['choices']));
				$row['choices'] = array();
				// Make choices more readable... mainly for b/w compat
				foreach ($choices as $k => $i) {
					$row['choices'][$k]['question'] = $i['question'];
					$row['choices'][$k]['multi'] = $i['multi'];
					foreach($i['choice'] as $c => $d) {
						$row['choices'][$k][$c] = array('option_id' => $c,
							'option_title' => $d,
							'votes' => $i['votes'][$c],
							'percentage' => array_sum($i['votes']) ? intval(($i['votes'][$c] / array_sum($i['votes'])) * 100) : '0',
							);
					}
				}
				// I think leaving this as 'poll_question' is silly...
				$row['title'] = $row['poll_question'];
				$this->save_cache('get_poll_info', $topicid, $row);

				return $row;
			} else {
				return FALSE;
			}
		}
	}

	/**
	 * Returns total number of votes in a poll.
	 *
	 * @param integer $topicid
	 * @return int Poll Votes
	 */
	function get_poll_total_votes($topicid) {
		if ($info = $this->get_poll_info($topicid)) {
			return $info['votes'];
		} else {
			return FALSE;
		}
	}

	/**
	 * Returns Topic ID associated with Poll ID.
	 *
	 * @param integer $pollid
	 * @return int Topic ID associated with Poll ID
	 */
	function pollid2topicid($pollid) {
		if (is_array($pollid)) {
			$topics = array();

			foreach ($pollid as $i => $j) {
				$this->DB->query ("SELECT tid FROM ibf_polls WHERE pid='" . $j . "' LIMIT 1");
				if ($row = $this->DB->fetch_row()) {
					$topics[$i] = $row['tid'];
				} else {
					$topics[$i] = FALSE;
				}
			}

			return $topics;
		} else {
			$this->DB->query ("SELECT tid FROM ibf_polls WHERE pid='" . $pollid . "' LIMIT 1");
			if ($row = $this->DB->fetch_row()) {
				return $row['tid'];
			} else {
				return FALSE;
			}
		}
	}

	/**
	 * Casts a vote in a poll.
	 *
	 * @param integer $topicid
	 * @param array $optionid In format "question number" => "option"
	 * @return bool Success
	 */
	function vote_poll($topicid, $optionid = array("1"=>""), $user_id = false) {
		// No Guests (, except when user_id is delivered) by @author Matthias Reuter <public@pc-intern.com> http://pc-intern.com | http://straightvisions.com
		if (!$this->is_loggedin() && empty($user_id)) {
			$this->Error($this->lang['zone_membersonly']);
			return FALSE;
		}
		if(empty($user_id) && isset($this->ips->member['id'])) {
			$user_id = $this->ips->member['id'];
		} elseif(empty($user_id) && empty($this->ips->member['id'])) {
			$this->Error($this->lang['zone_membersonly']);
			return FALSE;
		}
		if (!$this->has_perms('g_vote_polls',$user_id)) {
			$this->Error($this->lang['zone_noperms']);
			return FALSE;
		}
		if(!is_array($optionid)) {
			$optionid = array("1" => $optionid);
		}
		if ($this->poll_voted($topicid)) {
			$this->Error($this->lang['zone_poll_alreadyvoted']);
			return FALSE;
		} else {
			// Insert Vote into Database
			$this->DB->query ("SELECT * FROM ibf_polls WHERE tid='" . $topicid . "'");

			if ($row = $this->DB->fetch_row()) {
				$choices = unserialize(stripslashes($row['choices']));
				foreach($optionid as $q => $o) {
					if (!isset($choices[$q])) {
						$this->Error($this->lang['zone_poll_invalid_vote']);
						return FALSE;
					}
					// cound single votes (radio)
					if(!is_array($o) && (int)$o > 0) {
						if(!isset($choices[$q]['choice'][$o])) {
							$this->Error($this->lang['zone_poll_invalid_vote']);
							return FALSE;
						}
						++$choices[$q]['votes'][$o];
					// count multi votes (checkboxes), fix by @author Matthias Reuter <public@pc-intern.com> http://pc-intern.com | http://straightvisions.com
					} elseif(is_array($o) && count($o) > 0) {
						foreach($o as $s => $t) {
							if(!isset($choices[$q]['choice'][$s])) {
								$this->Error($this->lang['zone_poll_invalid_vote']);
								return FALSE;
							}
							++$choices[$q]['votes'][$s];
						}
					}
				}
				$choices = serialize($choices);

				$this->DB->query ("UPDATE ibf_polls SET choices='" . $choices . "', votes=votes+1 WHERE tid='" . $topicid . "'");
				$this->DB->query ("INSERT INTO ibf_voters (ip_address, vote_date, tid, member_id, forum_id) VALUES ('" . $_SERVER['REMOTE_ADDR'] . "', '" . time() . "', '" . $row['tid'] . "', '" . $user_id . "', '" . $row['forum_id'] . "')");

				return TRUE;
			} else {
				$this->Error($this->lang['zone_poll_noexist']);
				return FALSE;
			}
		}
	}

	/**
	 * Casts a null vote in a poll.
	 *
	 * @param integer $topicid
	 * @return bool Success
	 */
	function nullvote_poll($topicid) {
		// No Guests Please
		if (!$this->is_loggedin()) {
			$this->Error($this->lang['zone_membersonly']);
			return FALSE;
		}
		if (!$this->has_perms('g_vote_polls')) {
			$this->Error($this->lang['zone_noperms']);
			return FALSE;
		}
		if ($this->poll_voted($topicid)) {
			$this->Error($this->lang['zone_poll_alreadyvoted']);
			return FALSE;
		} else {
			// Insert Vote into Database
			$this->DB->query ("SELECT * FROM ibf_polls WHERE tid='" . $topicid . "'");

			if ($row = $this->DB->fetch_row()) {
				$this->DB->query ("INSERT INTO ibf_voters (ip_address, vote_date, tid, member_id, forum_id) VALUES ('" . $_SERVER['REMOTE_ADDR'] . "', '" . time() . "', '" . $row['tid'] . "', '" . $this->ips->member['id'] . "', '" . $row['forum_id'] . "')");

				return TRUE;
			} else {
				$this->Error($this->lang['zone_poll_noexist']);
				return FALSE;
			}
		}
	}

	/**
	 * Creates a new poll.
	 *
	 * @param integer $topicid Topic ID to associate the poll with
	 * @param array $question Questions.
	 * @param array $choices The options to vote for for each question
	 * @param string $title The title of the poll
	 * @return bool Success
	 */
	function new_poll($topicid, $questions = array(), $choices = array(), $title='',$poll_only=0,$multi=array()) {
		// Check if we can do polls
		$info = $this->get_advinfo();
		if ($this->has_perms('g_post_polls')) {
			// Check we have a good number of choices :)
			if(!is_array($questions) && strlen($questions) > 0) {
				$questions = array($questions);
			}
			if(is_array($questions) AND count($questions) > 0 AND count($questions) <= $this->ips->vars['max_poll_questions']) {
				$title = ($title=='') ? $questions[0] : $title;
				// Some last-minute checks...
				if(count($choices) > count($questions)) {
					$choices = array(0 => $choices);
				}
				$thelot = array();
				$count = '1';
				// Check our Topic exists
				if (!$topicinfo = $this->get_topic_info(intval($topicid))) {
					$this->Error($this->lang['zone_topics_notexist']);
					return FALSE;
				}

				foreach($questions as $k => $v) {
					if (is_array($choices[$k]) AND count($choices[$k]) > 1 AND count($choices[$k]) <= $this->ips->vars['max_poll_choices']) {
						if(is_array($multi) && isset($multi[$k])) $is_multi = $multi[$k]; else $is_multi = 0;
						$thechoices = array(); // Init
						$choicecount = '1';
						foreach ($choices[$k] as $i) {
							$thechoices[$choicecount] = $this->makesafe($i);
							$thevotes[$choicecount] = 0;
							$choicecount++;
						}
						$thelot[$count] = array('question' => $v,'multi' => $is_multi,'choice' => $thechoices, 'votes' => $thevotes);
						$count++;
					}
					else {
						$this->Error(sprintf($this->lang['zone_poll_invalid_opts'], $this->ips->vars['max_poll_choices']));
						return FALSE;
					}
				}

				// Now add it into the polls table
				$this->DB->query ("INSERT INTO ibf_polls VALUES ('', '".intval($topicid)."', '".time()."', '".serialize($thelot)."', '".$this->ips->member['id']."', '0', '".$topicinfo['forum_id']."','".$this->makesafe($title)."','".$poll_only."')");
				// And change the topic's poll status to open
				$this->DB->query ("UPDATE ibf_topics SET poll_state='open' WHERE tid='" . intval($topicid) . "'");
				// Return TRUE;
				return TRUE;
			} else {
				$this->Error(sprintf($this->lang['zone_poll_invalid_questions'], $this->ips->vars['max_poll_questions']));
				return FALSE;
			}
		} else {
			$this->Error($this->lang['zone_noperms']);
			return FALSE;
		}
	}

   /**
	 * Deletes Topic-Poll contains delivered poll_id
	 *
	 * @param integer $poll_id
	 */
	function delete_poll ($poll_id) {
		$output = false;

		$this->DB->query("DELETE FROM ibf_polls WHERE pid = '".intval($poll_id)."'");

		// Update the Topic
		if($this->DB->query("UPDATE ibf_topics SET poll_state='0',last_vote='0',total_votes='0' WHERE tid='".$this->pollid2topicid($poll_id)."'")) $output = true;

		return $output;
	}

	// -----------------------------------------------
	// FORUM FUNCTIONS
	// Functions relating to forums.
	// -----------------------------------------------
	/**#@+
	 * @group Forums
	 */
	/**
	 * Returns forums readable by the current member.
	 *
	 * @return array Readable Forum Details
	 */
	function get_member_readable_forums () {
		if ($cache = $this->get_cache('get_member_readable_forums', $this->ips->member['id'])) {
			return $cache;
		} else {
			$this->DB->query ('SELECT f.id, f.name, f.description, f.topics, f.posts, f.permission_array, f.parent_id, c.name AS category_name FROM ibf_forums f LEFT JOIN ibf_forums c ON (f.parent_id=c.id) ORDER BY f.position');
			$forums = array();
			while ($row = $this->DB->fetch_row()) {
				$perms = $this->sort_perms($row['permission_array']);
				if ($this->ips->check_perms($perms['read_perms'])) {
					$row['readable'] = '1';
					$forums[$row['id']] = $row;
					$forums[$row['id']]['read_perms'] = $perms['read_perms'];
					$forums[$row['id']]['start_perms'] = $perms['start_perms'];
					$forums[$row['id']]['reply_perms'] = $perms['reply_perms'];
					$forums[$row['id']]['upload_perms'] = $perms['upload_perms'];
					$forums[$row['id']]['show_perms'] = $perms['show_perms'];
				}
			}

			$this->save_cache('get_member_readable_forums', $this->ips->member['id'], $forums);

			return $forums;
		}
	}

	/**
	 * Returns whether a forum can be read by
	 * the current member.
	 *
	 * @param integer $forumid
	 * @return bool Forum Is Readable
	 */
	function is_forum_readable ($forumid) {
		$readable = $this->get_member_readable_forums();

		if (isset($readable[$forumid]) && $readable[$forumid]['readable'] == '1') {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Returns forums postable in by the current member.
	 *
	 * @return array Postable Forum Details
	 */
	function get_member_postable_forums () {
		if ($cache = $this->get_cache('get_member_postable_forums', $this->ips->member['id'])) {
			return $cache;
		} else {
			$this->DB->query ('SELECT f.id, f.name, f.description, f.topics, f.posts, f.permission_array, f.parent_id, c.name AS category_name FROM ibf_forums f LEFT JOIN ibf_forums c ON (f.parent_id=c.id) ORDER BY f.position');
			$forums = array();
			while ($row = $this->DB->fetch_row()) {
				$perms = $this->sort_perms($row['permission_array']);
				if ($this->ips->check_perms($perms['reply_perms'])) {
					$row['postable'] = '1';
					$forums[$row['id']] = $row;
					$forums[$row['id']]['read_perms'] = $perms['read_perms'];
					$forums[$row['id']]['start_perms'] = $perms['start_perms'];
					$forums[$row['id']]['reply_perms'] = $perms['reply_perms'];
					$forums[$row['id']]['upload_perms'] = $perms['upload_perms'];
					$forums[$row['id']]['show_perms'] = $perms['show_perms'];
				}
			}

			$this->save_cache('get_member_postable_forums', $this->ips->member['id'], $forums);

			return $forums;
		}
	}

	/**
	 * Returns whether a forum can be posted in by
	 * the current member.
	 *
	 * @param integer $forumid
	 * @return bool Forum Is Postable In
	 */
	function is_forum_postable ($forumid) {
		$postable = $this->get_member_postable_forums();

		if ($postable[$forumid]['postable'] == '1') {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Returns forums in which the current member
	 * can start new topics in.
	 *
	 * @return array Startable Forum Details
	 */
	function get_member_startable_forums () {
		if ($cache = $this->get_cache('get_member_startable_forums', $this->ips->member['id'])) {
			return $cache;
		} else {
			$this->DB->query ('SELECT f.id, f.name, f.description, f.topics, f.posts, f.permission_array, f.parent_id, c.name AS category_name FROM ibf_forums f LEFT JOIN ibf_forums c ON (f.parent_id=c.id) ORDER BY f.position');
			$forums = array();
			while ($row = $this->DB->fetch_row()) {
				$perms = $this->sort_perms($row['permission_array']);
				if ($this->ips->check_perms($perms['start_perms'])) {
					$row['startable'] = '1';
					$forums[$row['id']] = $row;
					$forums[$row['id']]['read_perms'] = $perms['read_perms'];
					$forums[$row['id']]['start_perms'] = $perms['start_perms'];
					$forums[$row['id']]['reply_perms'] = $perms['reply_perms'];
					$forums[$row['id']]['upload_perms'] = $perms['upload_perms'];
					$forums[$row['id']]['show_perms'] = $perms['show_perms'];
				}
			}

			$this->save_cache('get_member_startable_forums', $this->ips->member['id'], $forums);

			return $forums;
		}
	}

	/**
	 * Returns whether a forum can start topics in.
	 *
	 * @param integer $forumid
	 * @return bool Forum Is Startable In.
	 */
	function is_forum_startable ($forumid) {
		$startable = $this->get_member_startable_forums();

		if ($startable[$forumid]['startable'] == '1') {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Returns information on a forum.
	 *
	 * @param integer $forumid
	 * @return array Forum Information.
	 */
	function get_forum_info ($forumid) {
		if ($cache = $this->get_cache('get_forum_info', $forumid)) {
			return $cache;
		} else {
			$this->DB->query ("SELECT f.* from ibf_forums f WHERE f.id='" . $forumid . "'");
			if ($row = $this->DB->fetch_row()) {
				$perms = $this->sort_perms($row['permission_array']);
				$row['read_perms']   = $perms['read_perms'];
				$row['reply_perms']  = $perms['reply_perms'];
				$row['start_perms']  = $perms['start_perms'];
				$row['upload_perms'] = $perms['upload_perms'];
				$row['show_perms']   = $perms['show_perms'];
				$this->save_cache('get_forum_info', $forumid, $row);
				return $row;
			} else {
				return FALSE;
			}
		}
	}

	/**
	 * List Topics in a Forum.
	 *
	 * The following settings can be used to overwrite the default query results.
	 * <br>'order' default: 'desc'
	 * <br>'start' default: '0' start with first record
	 * <br>'limit' default: '15' no. of topics per page
	 * <br>'orderby' default: 'last_post', others see below
	 *
	 * Sort keys: any of 'tid', 'title', 'posts', 'starter_name', 'starter_id', 'start_date', 'last_post', 'views', 'post_date'
	 *
	 * @param mixed $forumid A Forum ID, the asterisk '*', or an array with IDs to show
	 * @param array $settings Query settings
	 * @param integer $bypassperms Default: 0=repect board permission, 1=bypass board permission
	 * @return array Topics in Forum.
	 */

/**
	 * List Topics in a Forum.
	 *
	 * The following settings can be used to overwrite the default query results.
	 * <br>'order' default: 'desc'
	 * <br>'start' default: '0' start with first record
	 * <br>'limit' default: '15' no. of topics per page
	 * <br>'orderby' default: 'last_post', others see below
	 *
	 * Sort keys: any of 'tid', 'title', 'posts', 'starter_name', 'starter_id', 'start_date', 'last_post', 'views', 'post_date'
	 *
	 * @param mixed $forumid A Forum ID, the asterisk '*', or an array with IDs to show
	 * @param array $settings Query settings
	 * @param integer $bypassperms Default: 0=repect board permission, 1=bypass board permission
	 * @param integer $ignoreapproval Default: 0=make sure topic is approved, 1=bypass approval
	 * @return array Topics in Forum.
	 */
	function list_forum_topics($forumid, $settings = array('order' => 'desc', 'limit' => '15', 'start' => '0', 'orderby' => 'last_post', 'linked' => false, 'ignoreapproval' => '0'), $bypassperms = '0') {
		// As of zone 1.0 this function can be used to get topics
		// from multiple forums. So heres the updated thingy :)
		$expforum = array();
		if (is_array($forumid)) {
			foreach ($forumid as $i) {
				if ($this->is_forum_readable(intval($i)) OR $bypassperms) {
					$expforum[] = intval($i);
				}
			}
		}
		elseif ($forumid == '*') {
			// Get readable forums
			$readable = $this->get_member_readable_forums();
			foreach ($readable as $j => $k) {
				$expforum[] = intval($k['id']);
			}
		}
		elseif ($this->is_forum_readable(intval($forumid)) OR $bypassperms) {
			$expforum[] = intval($forumid);
		}

		if (count($expforum) < 1) {
			$this->Error($this->lang['zone_noperms']);
			return FALSE;
		}
		else {
			// What shall I order it by guv?
			$allowedorder = array('tid', 'title', 'posts', 'starter_name', 'starter_id', 'start_date', 'last_post', 'views', 'post_date');

			if (isset($settings['orderby']) && in_array($settings['orderby'], $allowedorder)) {
				$order = $settings['orderby'] . ' ' . (($settings['order'] == 'desc') ? 'DESC' : 'ASC');
			}
			elseif (isset($settings['orderby']) && $settings['orderby'] == 'random') {
				$order = 'RAND()';
			}
			else {
				$order = 'last_post ' . ((strtolower($settings['order']) == 'desc') ? 'DESC' : 'ASC');
			}
			// Grab Posts
			$limit = isset($settings['limit']) ? intval($settings['limit']) : '15';
			$start = isset($settings['start']) ? intval($settings['start']) : '0';
			// Forum ID Code
			if (($forumid == '*') && $bypassperms) {
				$forums = '';
			}
			else {
				$forums = 'torig.forum_id IN ('.implode(',', $expforum).')';
				$and2 = ' AND ';
			}
			// Are we looking for an approval?
			if(empty($settings['ignoreapproval'])) {
				$approved = "torig.approved='1'";
				$and2 = ' AND ';
			}
			// Final step,,,
			if($forums && $approved) {
				$and1 = ' AND ';
			}

			// SUB SELECT query joins are not allowed. Add $this->ipsclass->DB->allow_sub_select=1; before any query construct to allow them
			$this->DB->allow_sub_select=1;

			// select topics
			$SQL = 'SELECT t.*, p.*, g.g_dohtml AS usedohtml FROM ibf_topics torig';
			// get linked, moved topics too if requested
			if($settings['linked'] === true) {
				$SQL .= ' LEFT JOIN ibf_topics t ON (IFNULL( (t.tid = LEFT(torig.moved_to, INSTR(torig.moved_to, "&"))),t.tid = torig.tid ))';
			}
			// else get all other topics
			else {
				$SQL .= ' LEFT JOIN ibf_topics t ON (t.tid = torig.tid)';
			}
			$SQL .= '	LEFT JOIN ibf_posts p ON (t.tid=p.topic_id)
						LEFT JOIN ibf_members m ON (p.author_id=m.id)
						LEFT JOIN ibf_groups g ON (m.mgroup=g.g_id)
						WHERE '.$forums.$and1.$approved.$and2."p.new_topic='1'
						ORDER BY $order LIMIT $start,$limit";
			$this->DB->query($SQL);

			$return = array();
			$this->parser->parse_bbcode = 1;
			$this->parser->strip_quotes = 1;
			$this->parser->parse_nl2br = 1;
			while ($row = $this->DB->fetch_row()) {
				// Parse [doHTML] taggy
				$this->parser->parse_html = $row['usedohtml'];
				$row['post'] = $this->parser->pre_display_parse($row['post']);
				$row['post'] = preg_replace("|([A-Za-z0-9\%<]+)\#EMO_DIR\#([A-Za-z0-9\%>]+)\/|U",$this->ips->skin['_emodir']."/",$row['post']);
				$row['post'] = str_replace("<img src='style_emoticons","<img src='".$this->_options['board_url']."/style_emoticons",$row['post']);
				$row['post'] = str_replace("<img src=\"style_emoticons","<img src=\"".$this->_options['board_url']."/style_emoticons",$row['post']);
				$return[] = $row;
			}

			return $return;
		}
	}

	/**
	 * Creates a new forum and returns it's ID.
	 *
	 * @param string $forumname The name of the Forum
	 * @param string $forumdesc The description
	 * @param integer $categoryid The category ID this forum belongs to
	 * @param integer $startperms Group IDs for Start posts permission
	 * @param integer $replyperms Group IDs for Reply-To posts permission
	 * @param integer $readperms Group IDs for Read posts permission
	 * @param integer $uploadperms Group IDs for Fileupload permission
	 * @param integer $showperms Group IDs for Show permission
	 * @return long New forum record ID or FALSE on failure
	 */
	function add_forum($forumname, $forumdesc, $categoryid, $perms) {

		if (!$forumname) {
			$this->Error($this->lang['zone_forum_noname']);
			return FALSE;
		}

		$forumname = $this->makesafe(trim($forumname));
		$forumdesc = $this->makesafe(trim($forumdesc));

		$this->DB->query('LOCK TABLE ibf_forums WRITE');
		$this->DB->query('SELECT MAX(id) as max FROM ibf_forums');
		$row = $this->DB->fetch_row();
		$max = $row['max'];
		$this->DB->query('UNLOCK TABLES');

		if ($max < 1) {
			$max = '0';
		}

		++$max;
		// Check Cat Exists. Meow!
		if($categoryid != '-1') {
			$this->DB->query ("SELECT * FROM ibf_forums WHERE id='" . intval($categoryid) . "'");
			if (!$this->DB->fetch_row()) {
				$this->Error($this->lang['zone_cat_notexist']);
				return FALSE;
			}
		}

		$this->DB->query("SELECT MAX(position) as pos FROM ibf_forums WHERE parent_id='" . intval($categoryid) . "'");
		$row = $this->DB->fetch_row();
		$pos = $row['pos'];

		if ($pos < 1) {
			$pos = '0';
		}

		++$pos;

		// Permissions
		$permissions = array('start_perms' => $perms['start'],
			'reply_perms' => $perms['reply'],
			'read_perms' => $perms['read'],
			'upload_perms' => $perms['upload'],
			'show_perms' => $perms['show'],
			'download_perms' => $perms['download']
			);

		$permsfinal = array();
		// Get Groups
		$groups = array();
		$this->DB->query ('SELECT perm_id FROM ibf_forum_perms');
		while ($groupsr = $this->DB->fetch_row()) {
			$groups[] = $groupsr['perm_id'];
		}

		foreach ($permissions as $i => $j) {
			// if permission is to be set for category
			if ($j == '*' && $categoryid == '-1') {
				$x = array();
				foreach ($groups as $l) {
					$x[] = intval($l);
				}
				$permsfinal[$i] = implode (',', $x);
			// if permission is to be set for forum
			} elseif ($j == '*') {
				// All Groups
				$permsfinal[$i] = '*';
			} else {
				$x = array();
				foreach ($j as $l) {
					if (in_array($l, $groups)) {
						$x[] = intval($l);
					}
				}
				$permsfinal[$i] = implode (',', $x);
			}
		}

		$perm_array = addslashes(serialize($permsfinal));

		// Finally Add it to the Database
		if($categoryid == '-1') {
			// category settings
			$DB_string = $this->DB->compile_db_insert_string(
				array(
					'id' => $max,
					'topics' => $pos,
					'posts' => 0,
					'last_post' => 0,
					'last_poster_id' => 0,
					'last_poster_name' => '',
					'name' => $forumname,
					'description' => $forumdesc,
					'position' => $max,
					'use_ibc' => 0,
					'use_html' => 0,
					'status' => 0,
					'password' => '',
					'password_override' => '',
					'last_title' => '',
					'last_id' => 0,
					'sort_key' => '',
					'sort_order' => '',
					'prune' => 0,
					'topicfilter' => '',
					'show_rules' => 'NULL',
					'preview_posts' => 0,
					'allow_poll' => 0,
					'allow_pollbump' => 0,
					'inc_postcount' => 0,
					'skin_id' => 'NULL',
					'parent_id' => intval($categoryid),
					'sub_can_post' => 0,
					'quick_reply' => 0,
					'redirect_url' => '',
					'redirect_on' => 0,
					'redirect_hits' => 0,
					'redirect_loc' => '',
					'rules_title' => '',
					'rules_text' => 'NULL',
					'topic_mm_id' => '',
					'notify_modq_emails' => '',
					'permission_custom_error' => '',
					'permission_array' => $perm_array,
					'permission_showtopic' => 1,
					'queued_topics' => 0,
					'queued_posts' => 0,
					'forum_last_deletion' => 0,
					'forum_allow_rating' => 0,
					'newest_title' => '',
					'newest_id' => 0,
				)
			);
		} else {
			// forum settings
			$DB_string = $this->DB->compile_db_insert_string(
				array(
					'id' => $max,
					'topics' => $pos,
					'posts' => 0,
					'last_post' => 0,
					'last_poster_id' => 0,
					'last_poster_name' => '',
					'name' => $forumname,
					'description' => $forumdesc,
					'position' => $max,
					'use_ibc' => 1,
					'use_html' => 0,
					'status' => 1,
					'password' => '',
					'password_override' => '',
					'last_title' => '',
					'last_id' => 0,
					'sort_key' => 'last_post',
					'sort_order' => 'Z-A',
					'prune' => 100,
					'topicfilter' => 'all',
					'show_rules' => false,
					'preview_posts' => 0,
					'allow_poll' => 1,
					'allow_pollbump' => 0,
					'inc_postcount' => 1,
					'skin_id' => false,
					'parent_id' => intval($categoryid),
					'sub_can_post' => 1,
					'quick_reply' => 1,
					'redirect_url' => '',
					'redirect_on' => 0,
					'redirect_hits' => 0,
					'redirect_loc' => '',
					'rules_title' => '',
					'rules_text' => false,
					'topic_mm_id' => '',
					'notify_modq_emails' => '',
					'permission_custom_error' => '',
					'permission_array' => $perm_array,
					'permission_showtopic' => 0,
					'queued_topics' => 0,
					'queued_posts' => 0,
					'forum_last_deletion' => 0,
					'forum_allow_rating' => 1,
					'newest_title' => '',
					'newest_id' => 0,
				)
			);
		}


		$this->DB->query('LOCK TABLE ibf_forums WRITE');
		$this->DB->query('INSERT INTO ibf_forums (' . $DB_string['FIELD_NAMES'] . ') VALUES (' . $DB_string['FIELD_VALUES'] . ')');
		$this->DB->query('UNLOCK TABLES');

		// Update ye olde forum cache

		$this->ips->update_forum_cache(); // NOTE: Buggy, ie - won't work (?)

		return $max;
	}

	/**
	 * Converts forum name to forum-ids
	 *
	 * @param integer $name
	 */
	function forum_name2id($name) {
		$this->DB->query ('SELECT id FROM ibf_forums WHERE name="' . addslashes(htmlentities($name)) . '"');
		$forums = $this->DB->fetch_row();
		if(is_array($forums) && count($forums) === 1) return  $forums['id']; // return matching forum-id
		elseif(is_array($forums) && count($forums) > 0) return $forums; // return array of matched forum-ids
		else return false;
	}

	/**
	 * Deletes the forum with delivered forum_id including all subforums, topics, polls and posts.
	 *
	 * @param int $forum_id
	 */
	function delete_forum($forum_id) {

		$forums_array = $this->get_all_subforums($forum_id);

		if(isset($forums_array) && is_array($forums_array) && count($forums_array) > 0) $forums_string = "'".implode("','",array_keys($forums_array))."'";
		else return false;

		if($this->DB->query("SELECT tid FROM ibf_topics WHERE forum_id IN (" .$forums_string.")")) {
			while ($row = $this->DB->fetch_row()) {
				$topics_array[] = $row['tid'];
			}
			if(isset($topics_array) && is_array($topics_array) && count($topics_array) > 0) $topics_string = "'".implode("','",$topics_array)."'";
		}

		// delete posts
		if(isset($topics_string)) $this->DB->query("DELETE FROM ibf_posts WHERE topic_id IN (".$topics_string.")");

		// delete polls
		if(isset($topics_string)) $this->DB->query("DELETE FROM ibf_polls WHERE tid IN (".$topics_string.")");

		// delete topics
		if(isset($forums_string)) $this->DB->query("DELETE FROM ibf_topics WHERE forum_id IN (".$forums_string.")");

		// delete all subforums
		if(isset($forums_string)) $this->DB->query("DELETE FROM ibf_forums WHERE id IN (".$forums_string.")");

		$this->ips->update_forum_cache();

		return true;
	}

	/**
	 * Returns HTML-Code with <option>-tags containg all subforums of the delivered forums.
	 *
	 * @param mixed $forums
	 * @param string $indent_string
	 * @param int $indent
	 */
	function get_all_subforums($forums,$output_type='array',$indent_string='-',$indent=false) {
		$output = false;
		if(is_string($forums)) $forums = array($this->get_forum_info($forums));

		// save original indent string
		if(isset($indent)) $orig_indent = $indent; else $orig_indent = false;

		// grab all forums from every delivered cat-id
		if(is_array($forums) && count($forums) > 0) {
			foreach ($forums as $i) {
				if($output_type == 'html_form') { // give every forum its own option-tag
					$select = 'id,name';
					$output .= '<option value="'.$i['id'].'">'.$indent.$i['name'].'</option>';
				} elseif($output_type == 'array') { // merge all forum-data in one, big array
					$select = '*';
					$output[$i['id']] = $i;
				} elseif($output_type == 'array_ids_only') { // merge all forum-data in one, big array
					$select = 'id';
					$output[$i['id']] = $i;
				}

				// grab all subforums from each delivered cat-id
				if($this->DB->query("SELECT * FROM ibf_forums WHERE parent_id = ".$i['id']." ORDER BY position ASC")) {
					// extend indent-string
					$indent = $indent.$indent_string;
					// get all subforums in an array
					while($row = $this->DB->fetch_row()) { $subforums[] = $row; }
					// make it rekursive
					if(is_array($subforums) && count($subforums) > 0) {
						if($output_type == 'html_form') // give every forum its own option-tag
						$output .= $this->get_all_subforums($subforums,$output_type,$indent_string,$indent);
						elseif($output_type == 'array' || $output_type == 'array_ids_only') // merge all forum-data in one, big array
						$output = $output+$this->get_all_subforums($subforums,$output_type,$indent_string,$indent);
					}
					// reset the temp-values
					$subforums = false;
					$indent = $orig_indent;
				}
			}
		} else $output = '<option value="">no forums available</option>';

		return $output;
	}

	/**
	 * Updates Forum-Cache and recounts Last-Count-Datas.
	 *
	 * @param integer $forum_id
	 * @param array $deleted_info An optional array with informations of deleted topic can be delivered to update the count-datas.
	 *
	 */
	function update_forum_cache ($forum_id,$count=array()) {
		$output = false;

		if(empty($count['topics'])) $count['topics'] = 0;
		if(empty($count['posts'])) $count['posts'] = 0;

		// grab data from new latest post in forum
		$last_t_info = $this->list_forum_topics($forum_id,array('limit' => 1,'orderby' => 'last_post'));
		$new_last_t_info = $last_t_info[0];

		// Finally update the forum
		if(
			$this->DB->query("
				UPDATE ibf_forums SET
				posts=posts+".$count['posts'].",
				topics=topics+".$count['topics'].",
				last_title='".$new_last_t_info['title']."',
				last_id='".$new_last_t_info['tid']."',
				newest_title='".$new_last_t_info['title']."',
				newest_id='".$new_last_t_info['tid']."',
				last_poster_name='".$this->id2displayname($new_last_t_info['last_poster_id'])."',
				last_poster_id='".$new_last_t_info['last_poster_id']."',
				last_post='".$new_last_t_info['last_post']."'
				WHERE id='".$forum_id."'")
		) $output = true;
		// and stats
		$this->ips->cache['stats']['total_topics']	+= $count['topics'];
		$this->ips->cache['stats']['total_replies']	+= $count['posts'];
		// and cache
		$this->ips->update_forum_cache();
		$this->ips->update_cache(  array( 'name' => 'stats', 'value' => false, 'donow' => false, 'array' => 1, 'deletefirst' => 0 ) );

		return $output;
	}
	// -----------------------------------------------
	// POST FUNCTIONS
	// -----------------------------------------------
	/**#@+
	 * @group Posts
	 */
	/**
	 * Adds a new post.
	 *
	 * @param integer $topicid
	 * @param string $post Message body
	 * @param integer $disableemos Default: 0=disable emoticons, 1=enable
	 * @param integer $disablesig Default: 0=disable signatures, 1=enable
	 * @param integer $bypassperms Default: 0=repect board permission, 1=bypass permissions
	 * @param string $guestname Name for Guest user, Default: "" (empty string)
	 * @return int New post ID or FALSE on failure
	 */
	function add_post ($topicid, $post, $disableemos = '0', $disablesig = '0', $bypassperms = '0', $guestname = '') {
		$post = $this->makesafe($post);

		if ($this->is_loggedin()) {
			$postname = $this->ips->member['members_display_name'];
		} else {
			if ($guestname) {
				$postname = $this->ips->vars['guest_name_pre'] . $this->makesafe($guestname) . $this->ips->vars['guest_name_suf'];
			} else {
				$postname = $this->ips->member['members_display_name'];
			}
		}
		// No Posting
		if ($this->ips->member['restrict_post']) {
			$this->Error($this->lang['zone_noperms']);
			return FALSE;
		}
		// Flooding
		if ($this->ips->vars['flood_control'] AND !$this->has_perms('g_avoid_flood')) {
			if ((time() - $this->ips->member['last_post']) < $this->ips->vars['flood_control']) {
				$this->Error(sprintf($this->lang['zone_floodcontrol'], $this->ips->vars['flood_control']));
				return FALSE;
			}
		}

		// Check some Topic Stuff
		$this->DB->query ("SELECT t.*, f.* FROM ibf_topics t LEFT JOIN ibf_forums f ON (t.forum_id=f.id) WHERE t.tid='" . intval($topicid) . "'");
		if ($row = $this->DB->fetch_row()) {
			// Check User can Post to Forum
			if ($this->is_forum_postable($row['forum_id']) OR $bypassperms) {
				// Post Queue
				if ($row['preview_posts'] OR $this->ips->member['mod_posts']) {
					$preview = '1';
				} else {
					$preview = '0';
				}
				// What if the topic is locked
				if ($row['state'] != 'open' AND !$this->has_perms('g_post_closed')) {
					$this->Error($this->lang['zone_noperms']);
					return FALSE;
				}
				// Check they can reply
				if ($row['starter_id'] == $this->ips->member['id']) {
					if (!$this->has_perms('g_reply_own_topics')) {
						$this->Error($this->lang['zone_noperms']);
						return FALSE;
					}
				} else {
					if (!$this->has_perms('g_reply_other_topics')) {
						$this->Error($this->lang['zone_noperms']);
						return FALSE;
					}
				}

				$time = time();
				// If we're still here, we should be ok to add the post
				$this->parser->parse_bbcode = $row['use_ibc'];
				$this->parser->strip_quotes = 1;
				$this->parser->parse_nl2br = 1;
				$this->parser->parse_html = $row['use_html'];
				$this->parser->parse_smilies = ($disableemos ? '0' : '1');
				$fpost = addslashes($this->parser->pre_db_parse($post));
				// POST KEY!
				$this->DB->query ("INSERT INTO ibf_posts (author_id, author_name, use_emo, use_sig, ip_address, post_date, post, queued, topic_id, post_key) VALUES ('{$this->ips->member['id']}', '{$postname}', '" . ($disableemos ? '0' : '1') . "', '" . ($disablesig ? '0' : '1') . "', '" . $_SERVER['REMOTE_ADDR'] . "', '" . $time . "', '" . $fpost . "', '{$preview}', '{$row['tid']}', '".md5(microtime())."')");

				$postid = $this->DB->get_insert_id();
				// Update the Topics
				$this->DB->query ("UPDATE ibf_topics SET last_poster_id='" . $this->ips->member['id'] . "', last_poster_name='" . $postname . "', posts=posts+1, last_post='" . $time . "' WHERE tid='" . intval($topicid) . "'");
				// Finally update the forums
				$this->DB->query ("UPDATE ibf_forums SET last_poster_id='" . $this->ips->member['id'] . "', last_poster_name='" . $postname . "', posts=posts+1, last_post='" . $time . "', last_title='" . addslashes($row['title']) . "', last_id='" . intval($topicid) . "' WHERE id='" . intval($row['forum_id']) . "'");
				// Oh yes, any update the post count for the user
				if ($this->ips->member['id'] != '0') {
					if ($row['inc_postcount']) {
						$this->DB->query ("UPDATE ibf_members SET posts=posts+1, last_post='" . time() . "' WHERE id='" . $this->ips->member['id'] . "' LIMIT 1");
					} else {
						$this->DB->query ("UPDATE ibf_members SET last_post='" . time() . "' WHERE id='" . $this->ips->member['id'] . "' LIMIT 1");
					}
				}
				// That's it - I promise ;)
				// Nooo... Wait Stats too
				//$this->DB->query ('UPDATE ibf_stats SET TOTAL_REPLIES=TOTAL_REPLIES+1');

				$this->ips->cache['stats']['total_replies']	+= 1;

				$this->ips->update_forum_cache();
				$this->ips->update_cache(  array( 'name' => 'stats', 'value' => false, 'donow' => false, 'array' => 1, 'deletefirst' => 0 ) );

				return $postid;
			} else {
				$this->Error($this->lang['zone_noperms']);
				return FALSE;
			}
		} else {
			$this->Error($this->lang['zone_topics_notexist']);
			return FALSE;
		}
	}

	/**
	 * Deletes Topic-Post contains delivered post_id
	 *
	 * @param integer $postid
	 */
	function delete_post ($postid) {
		$p_info = $this->get_post_info($postid);

		$this->DB->query("DELETE FROM ibf_posts WHERE pid = '".intval($postid)."'");

		// Update the Topics
		if($this->DB->query("UPDATE ibf_topics SET posts=posts-1 WHERE tid='".$p_info['topic_id']."'")) $output = true;
		else $output = false;

		// Finally update the forums
		if($this->update_forum_cache($p_info['forum_id'],array('posts' => -1))) $output = true;
		else $output = false;

		return $output;
	}

	/**
	 * Edits a post (adapted from add_post)
	 *
	 * @param integer $postid
	 * @param string $post Message body
	 * @param integer $disableemos Default: 0=disable emoticons, 1=enable
	 * @param integer $disablesig Default: 0=disable signatures, 1=enable
	 * @param integer $bypassperms Default: 0=respect board permission, 1=bypass permissions
	 * @param integer $appendedit Default: 1=adds the 'edited' line afer the post, 0= doesn't add
	 * @return bool TRUE on success, FALSE on failure
	 */
	function edit_post ($postid, $post, $disableemos = '0', $disablesig = '0', $bypassperms = '0', $appendedit = '1') {
		$post = $this->makesafe($post);
		// Noms et tous d'invit�.
		if ($this->is_loggedin()) {
			$postname = $this->ips->member['name'];
		} else {
			// Oh dear... not sure you can go around having guests editing posts...
			$this->Error($this->lang['zone_noperms']);
			return FALSE;
		}
		// No Posting
		if ($this->ips->member['restrict_post']) {
			$this->Error($this->lang['zone_noperms']);
			return FALSE;
		}
		// Flooding
		if ($this->ips->vars['flood_control'] AND !$this->has_perms('g_avoid_flood')) {
			if ((time() - $this->ips->member['last_post']) < $this->ips->vars['flood_control']) {
				$this->Error(sprintf($this->lang['zone_floodcontrol'], $this->ips->vars['flood_control']));
				return FALSE;
			}
		}
		// Check some Topic Stuff
		$this->DB->query ("SELECT  f.*,p.*,t.* FROM ibf_topics t LEFT JOIN ibf_forums f ON (t.forum_id=f.id) LEFT JOIN ibf_posts p ON(p.topic_id=t.tid) WHERE p.pid='" . intval($postid) . "'");
		if ($row = $this->DB->fetch_row()) {
			// Check User can Post to Forum
			if ($this->is_forum_postable($row['forum_id']) OR $bypassperms) {
				// Post Queue
				if ($row['preview_posts'] OR $this->ips->member['mod_posts']) {
					$preview = '1';
				} else {
					$preview = '0';
				}
				// What if the topic is locked
				if ($row['state'] != 'open' AND !$this->has_perms('g_post_closed')) {
					$this->Error($this->lang['zone_noperms']);
					return FALSE;
				}
				// Check they can edit posts
				if ($row['author_id'] == $this->ips->member['id']) {
					if (!$this->has_perms('g_edit_posts')) {
						$this->Error($this->lang['zone_noperms']);
						return FALSE;
					}
				} else {
					if (!$this->has_perms('g_is_supmod')) {
						$this->Error($this->lang['zone_noperms']);
						return FALSE;
					}
				}
				// Append_Edit?
				if(!$bypassperms && !$appendedit) {
					$appendedit = $this->has_perms('g_append_edit') ? 0 : 1;
				}

				$time = time();

				// If we're still here, we should be ok to edit the post
				// Whoops... maybe should have used an update instead of replace?
				// Meh
				$this->parser->parse_bbcode = $row['use_ibc'];
				$this->parser->strip_quotes = 1;
				$this->parser->parse_nl2br = 1;
				$this->parser->parse_html = $row['use_html'];
				$this->parser->parse_smilies = ($disableemos ? '0' : '1');
				$fpost = addslashes($this->parser->pre_db_parse($post));
				$this->DB->query ("REPLACE INTO ibf_posts (pid, author_id, author_name, use_emo, use_sig, ip_address, edit_time, post, queued, topic_id, append_edit, edit_name, post_date,post_parent,post_key,post_htmlstate,new_topic,icon_id) VALUES ('{$row['pid']}','".$row['author_id']."','".$row['author_name']."','" . ($disableemos ? '0' : '1') . "', '" . ($disablesig ? '0' : '1') . "', '" . $_SERVER['REMOTE_ADDR'] . "', '" . $time . "', '" . $fpost . "', '{$preview}', '{$row['tid']}','$appendedit','{$this->ips->member['name']}','{$row['post_date']}','{$row['post_parent']}','{$row['post_key']}',0,'{$row['new_topic']}','{$row['icon_id']}')");

				return TRUE;
			} else {
				$this->Error($this->lang['zone_noperms']);
				return FALSE;
			}
		} else {
			$this->Error($this->lang['zone_posts_notexist']);
			return FALSE;
		}
	}

	/**
	 * Returns information on a post.
	 *
	 * @param integer $postid
	 * @return array Post Information
	 */
	function get_post_info ($postid) {
		// Check for Post Cache
		if ($cache = $this->get_cache('get_post_info', $postid)) {
			return $cache;
		} else {
			$this->DB->query ("SELECT p.*, t.forum_id, t.title AS topic_name, g.g_dohtml AS usedohtml FROM ibf_posts p LEFT JOIN ibf_topics t ON (p.topic_id=t.tid) LEFT JOIN ibf_members m ON (p.author_id=m.id) LEFT JOIN ibf_groups g ON (m.mgroup=g.g_id) WHERE p.pid='" . $postid . "'");
			if ($row = $this->DB->fetch_row()) {
				// Parse [doHTML] taggy
				$mem = $this->get_advinfo($row['author_id']);
				$row = array_merge($row,$mem);
				$this->parser->parse_nl2br = true;
				$this->parser->parse_html = $row['usedohtml'];
				$row['post'] = $this->parser->pre_display_parse($row['post']);
				$row['post'] = preg_replace("|([A-Za-z0-9\%<]+)\#EMO_DIR\#([A-Za-z0-9\%>]+)\/|U",$this->ips->skin['_emodir']."/",$row['post']);
				$row['post'] = str_replace("<img src='style_emoticons","<img src='".$this->_options['board_url']."/style_emoticons",$row['post']);
				$row['post'] = str_replace("<img src=\"style_emoticons","<img src=\"".$this->_options['board_url']."/style_emoticons",$row['post']);
				$this->save_cache('get_post_info', $postid, $row);
				return $row;
			} else {
				return FALSE;
			}
		}
	}

	// -----------------------------------------------
	// TOPIC FUNCTIONS
	// -----------------------------------------------
	/**#@+
	 * @group Topics
	 */

	/**
	 * Creates a new topic and returns the new topic ID on success.
	 *
	 * @param integer $forumid
	 * @param string $title Topic title
	 * @param string $desc Topic description
	 * @param string $post Message body
	 * @param integer $disableemos Default: 0=disable emoticons, 1=enable
	 * @param integer $disablesig Default: 0=disable signatures, 1=enable
	 * @param integer $bypassperms Default: 0=repect board permission, 1=bypass permissions
	 * @param string $guestname Name for Guest user, Default: "" (empty string)
	 * @return long New topic ID or FALSE on failure
	 */
	function new_topic ($forumid, $title, $desc, $post, $disableemos = '0', $disablesig = '0', $bypassperms = '0', $guestname = '') {
		if (!$title) {
			$this->Error($this->lang['zone_topics_notitle']);
			return FALSE;
		}

		$title = $this->makesafe($title);
		$desc = $this->makesafe($desc);
		$post = $this->makesafe($post);
		// Noms et tous d'invit�.
		if ($this->is_loggedin()) {
			$postname = $this->ips->member['members_display_name'];
		} else {
			if ($guestname) {
				$postname = $this->ips->vars['guest_name_pre'] . $this->makesafe($guestname) . $this->ips->vars['guest_name_suf'];
			} else {
				$postname = $this->ips->member['members_display_name'];
			}
		}
		// No Posting
		if ($this->ips->member['restrict_post']) {
			$this->Error($this->lang['zone_noperms']);
			return FALSE;
		}
		// Flooding
		if ($this->ips->vars['flood_control'] AND !$this->has_perms('g_avoid_flood')) {
			if ((time() - $this->ips->member['last_post']) < $this->ips->vars['flood_control']) {
				$this->Error(sprintf($this->lang['zone_floodcontrol']), $this->ips->vars['flood_control']);
				return FALSE;
			}
		}
		// Check some Forum Stuff
		$this->DB->query ("SELECT * FROM ibf_forums WHERE id='" . intval($forumid) . "'");
		if ($row = $this->DB->fetch_row()) {
			// Check User can Post to Forum
			if ($this->is_forum_startable($row['id']) OR $bypassperms) {
				// Queuing
				if (!$this->has_perms('g_avoid_q') && ($row['preview_posts'] == 2 OR $row['preview_posts'] == 1 OR $this->ips->member['mod_posts'])) {
					$preview = '1';
				} else {
					$preview = '0';
				}

				$time = time();
				// Insert Topic Bopic
				$this->DB->query ("INSERT INTO ibf_topics (title, description, state, posts, starter_id, start_date, last_poster_id, last_post, starter_name, last_poster_name, views, forum_id, approved, author_mode, pinned) VALUES ('{$title}', '{$desc}', 'open', '0', '{$this->ips->member['id']}', '" . $time . "', '{$this->ips->member['id']}', '" . $time . "', '{$postname}', '{$postname}', '0', '{$forumid}', '" . ($preview ? '0' : '1') . "', '1', '0')");

				$topicid = $this->DB->get_insert_id();

				$this->parser->parse_bbcode = $row['use_ibc'];
				$this->parser->strip_quotes = 1;
				$this->parser->parse_nl2br = 1;
				$this->parser->parse_html = $row['use_html'];
				$this->parser->parse_smilies = ($disableemos ? '0' : '1');
				$fpost = addslashes($this->parser->pre_db_parse($post));
				$this->DB->query ("INSERT INTO ibf_posts (author_id, author_name, use_emo, use_sig, ip_address, post_date, post, queued, topic_id, new_topic, icon_id, post_htmlstate) VALUES ('{$this->ips->member['id']}', '{$postname}', '" . ($disableemos ? '0' : '1') . "', '" . ($disablesig ? '0' : '1') . "', '" . $_SERVER['REMOTE_ADDR'] . "', '" . $time . "', '" . $fpost . "', '0', '" . $topicid . "', '1', '0','{$this->parser->parse_html}')");
				// Finally update the forums
				$this->DB->query ("UPDATE ibf_forums SET last_poster_id='" . $this->ips->member['id'] . "', last_poster_name='" . $postname . "', topics=topics+1, last_post='" . $time . "', last_title='" . $title . "', last_id='" . $topicid . "' WHERE id='" . intval($forumid) . "'");
				// Oh yes, any update the post count for the user
				if ($this->ips->member['id'] != '0') {
					if ($row['inc_postcount']) {
						$this->DB->query ("UPDATE ibf_members SET posts=posts+1, last_post='" . time() . "' WHERE id='" . $this->ips->member['id'] . "' LIMIT 1");
					} else {
						$this->DB->query ("UPDATE ibf_members SET last_post='" . time() . "' WHERE id='" . $this->ips->member['id'] . "' LIMIT 1");
					}
				}
				// And stats

				$this->ips->cache['stats']['total_topics']	+= 1;

				$this->ips->update_forum_cache();
				$this->ips->update_cache(  array( 'name' => 'stats', 'array' => 1, 'deletefirst' => 0 ) );

				//$this->DB->query ('UPDATE ibf_stats SET TOTAL_TOPICS=TOTAL_TOPICS+1');
				// Return $topicid rather then TRUE as it is more use
				return $topicid;
			} else {
				$this->Error($this->lang['zone_noperms']);
				return FALSE;
			}
		} else {
			$this->Error($this->lang['zone_forum_notexist']);
			return FALSE;
		}
	}

/**
	 * Edits a topic
	 *
	 * @param integer $topicid
	 * @param string $title Topic title
	 * @param string $desc Topic description
	 * @param string $post Message body
	 * @param array $options 'close' Default: 0, 'pin' Default: 0, 'approve' Default: 1, 'disableeemos', 'bypassperms' Default: 0
	 * @param string $reason Default: "" (empty string)
	 * @return bool TRUE on success, FALSE on failure
	 */
	function edit_topic ($topicid, $title, $desc, $post, $options = array('close' => 0, 'pin' => 0, 'approve' => 1, 'disableemos' => '0', 'disablesig' => '0', 'bypassperms' => '0'), $reason = '') {
		$title = $this->makesafe($title);
		$desc = $this->makesafe($desc);
		$post = $this->makesafe($post);
		// You are logged in, right?
		if ($this->is_loggedin()) {
			$postname = $this->ips->member['name'];
		} else {
			// Drat... Sorry, but we can't have guests running around editing topics.
			$this->Error($this->lang['zone_noperms']);
			return FALSE;
		}
		// Flooding
		if ($this->ips->vars['flood_control'] AND !$this->has_perms('g_avoid_flood')) {
			if ((time() - $this->ips->member['last_post']) < $this->ips->vars['flood_control']) {
				$this->Error(sprintf($this->lang['zone_floodcontrol'], $this->ips->vars['flood_control']));
				return FALSE;
			}
		}

		$time = time();

		// Let's extract the information.
		$this->DB->query ("SELECT  f.*,t.*,p.* FROM ibf_topics t LEFT JOIN ibf_forums f ON (t.forum_id=f.id) LEFT JOIN ibf_posts p ON(t.topic_firstpost=p.pid) WHERE t.tid='" . intval($topicid) . "'");
		if ($row = $this->DB->fetch_row()) {
			if($user = $this->get_info()) {
				$forum = $this->get_forum_info($row['forum_id']);
				$group = $this->get_group_info($user['mgroup']);

				// Get permissions
				if (!$options['bypassperms']) {
					// Is the topic closed...?
					if (($row['state'] != 'open') AND !$group['g_post_closed']) {
						$this->Error($this->lang['zone_noperms']);
						return FALSE;
					}
					else {
						// Is the topic being closed?
						if($options['close'] == "1" && $group['g_open_close_posts']) {
							$state = 'closed';
							$closed = $time;
							$opened = $row['topic_open_time'];
						}
						elseif(empty($options['close']) OR !$group['g_open_close_posts']) {
								$state = 'open';
								$closed = $row['topic_close_time'];
								$opened = $time;
						}
						else {
							if($group['g_open_close_posts']) {
								$state = 'open';
								$closed = $row['topic_close_time'];
								$opened = $time;
							}
						}
					}
					// Now that this has passed by, can they edit?
					if ($row['author_id'] == $user['id']) {
						if (!$group['g_edit_topic']) {
							$this->Error($this->lang['zone_noperms']);
							return FALSE;
						}
					} else {
						if (!$group['g_is_supmod']) {
							$this->Error($this->lang['zone_noperms']);
							return FALSE;
						}
					}
				}

				// Is a new title specified...?
				if(!$title) {
					$ltitle = $forum['last_title'];
					$ptitle = $row['title'];
				}
				elseif($title == $forum['last_title']) {
					$ltitle = $forum['last_title'];
					$ptitle = $title;
				}
				else {
					$ltitle = $title;
					$ptitle = $title;
				}

				// Is there a new description?
				if(!$desc) {
					$fdesc = $row['description'];
				}
				else {
					$fdesc = $desc;
				}

				// Has the post been changed in any way?
				if(!$post) {
					$apost = $row['post'];
				}
				else {
					$apost = stripslashes($post);
				}

				// Should we update the edit settings?
				if(!$reason) {
					$reas = $row['post_edit_reason'];
					$edit = $row['edit_name'];
				}
				else {
					$reas = stripslashes($reason);
					$edit = $user['members_display_name'];
				}
				$edited = $time;

				// Ah, so everything's gone through okay. Now for the finishing touch.
				$this->parser->parse_bbcode = $row['use_ibc'];
				$this->parser->strip_quotes = 1;
				$this->parser->parse_nl2br = 1;
				$this->parser->parse_html = $row['use_html'];
				$this->parser->parse_smilies = ($options['disableemos'] ? '0' : '1');
				$fpost = addslashes($this->parser->pre_db_parse($apost));
				$this->DB->query ("UPDATE ibf_topics SET title='" . $ptitle . "', description='" . $fdesc . "', state='" . $state . "', pinned='{$options['pin']}', topic_open_time='" . $opened . "', topic_close_time='" . $closed . "', approved='{$options['approve']}' WHERE tid='" . $topicid . "'");
				$this->DB->query ("UPDATE ibf_posts SET edit_time='" . $edited . "', post='" . $fpost . "', edit_name='" . $edit . "', post_edit_reason='" . $reas . "' WHERE pid='{$row['topic_firstpost']}'");
				$this->DB->query ("UPDATE ibf_forums SET last_title='" . $ptitle . "', last_id='" . $topicid . "', last_poster_name='" . $user['members_display_name'] . "' WHERE id='{$row['forum_id']}'");

				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			$this->Error($this->lang['zone_topics_notexist']);
			return FALSE;
		}
	}

	/**
	 * Lists posts in a topic.
	 *
	 * The following settings can be used to overwrite the default query results.
	 * <br>'order' default: 'asc'
	 * <br>'start' default: '0' start with first record
	 * <br>'limit' default: '15' no. of topics per page
	 * <br>'orderby' default: 'post_date', others see below
	 *
	 * Sort keys: any of ''pid', 'author_id', 'author_name', 'post_date', 'post'
	 *
	 * @param integer $topicid
	 * @param array $settings optional query settings
	 * @param integer $bypassperms Default: 0=repect board permission, 1=bypass permissions
	 * @return array Topic Posts.
	 */
	function list_topic_posts($topicid, $settings = array('order' => 'asc', 'limit' => '15', 'start' => '0', 'orderby' => 'post_date'), $bypassperms = '0', $count_view = false) {
		/* Our little 1.0 thingy which allows you to export stuff
		from everywhere and complicated wubbly cool stuff. */

		$sqlwhere = '';

		if (is_array($topicid)) {
			// get_topic_info() is too inefficent when we have alot of topic ids.
			$topics = '';
			foreach ($topicid as $i) {
				$i = intval($i);
				if ($topics) {
					$topics .= " OR tid='$i'";
				} else {
					$topics = " tid='$i'";
				}
			}
			// Query
			$getfid = $this->DB->query ('SELECT tid, forum_id FROM ibf_topics WHERE ' . $topics);
			// Now we should how topic ids and their forum ids.
			while ($row = $this->DB->fetch_row($getfid)) {
				if ($this->is_forum_readable($row['forum_id']) OR $bypassperms) {
					if (!$sqlwhere) {
						$sqlwhere .= "(topic_id='" . $row['tid'] . "'";
					} else {
						$sqlwhere .= " OR topic_id='" . $row['tid'] . "'";
					}
				}
			}

			if ($sqlwhere) {
				$sqlwhere .= ') AND ';
				$cando = '1';
			} else {
				$this->Error($this->lang['zone_noperms']);
				return FALSE;
			}
		} elseif ($topicid == '*') {
			if ($bypassperms) {
				// Grab posts from the whole board
				$sqlwhere = '';
				$cando = '1';
			} else {
				// All topics. So we can grab them from all readable forums.
				$readable = $this->get_member_readable_forums();
				foreach ($readable as $j => $k) {
					if (!$sqlwhere) {
						$sqlwhere .= "(forum_id='" . $j . "'";
					} else {
						$sqlwhere .= " OR forum_id='" . $j . "'";
					}
				}

				if ($sqlwhere OR isset($cando)) {
					$sqlwhere .= ') AND ';
					$cando = '1';
				} else {
					$this->Error($this->lang['zone_noperms']);
					return FALSE;
				}
			}
		} else {
			// Classic Posts from Topic Export
			// Grab Topic Info then check whether forum is readable.
			$topicinfo = $this->get_topic_info($topicid,$count_view);
			if ($this->is_forum_readable($topicinfo['forum_id']) OR $bypassperms) {
				$sqlwhere = "topic_id='" . intval($topicid) . "' AND ";
				$cando = '1';
			} else {
				$this->Error($this->lang['zone_noperms']);
				return FALSE;
			}
		}
		// topic_id=''.intval($topicid).'' AND
		if ($cando) {
			// What shall I order it by guv?
			$allowedorder = array('pid', 'author_id', 'author_name', 'post_date', 'post');

			if (in_array($settings['orderby'], $allowedorder)) {
				$order = $settings['orderby'] . ' ' . (($settings['order'] == 'desc') ? 'DESC' : 'ASC');
			} elseif ($settings['orderby'] == 'random') {
				$order = 'RAND()';
			} else {
				$order = 'post_date ' . (($settings['order'] == 'desc') ? 'DESC' : 'ASC');
			}
			// Grab Posts
			$limit = $settings['limit'] ? intval($settings['limit']) : '15';
			$start = $settings['start'] ? intval($settings['start']) : '0';

			//$this->DB->query ("SELECT p.*, t.forum_id, a.attach_id, a.attach_hits, a.attach_ext, a.attach_location, a.attach_file, g.g_dohtml AS usedohtml FROM ibf_posts p LEFT JOIN ibf_members m ON (p.author_id=m.id) LEFT JOIN ibf_groups g ON (m.mgroup=g.g_id) LEFT JOIN ibf_attachments a ON(a.attach_pid=p.pid) LEFT JOIN ibf_topics t ON(p.topic_id=t.tid) WHERE " . $sqlwhere . "p.queued='0' ORDER BY " . $order . " LIMIT " . $start . "," . $limit);
			$this->DB->query ("SELECT p.*, t.forum_id, g.g_dohtml AS usedohtml FROM ibf_posts p LEFT JOIN ibf_members m ON (p.author_id=m.id) LEFT JOIN ibf_groups g ON (m.mgroup=g.g_id) LEFT JOIN ibf_topics t ON(p.topic_id=t.tid) WHERE " . $sqlwhere . "p.queued='0' ORDER BY " . $order . " LIMIT " . $start . "," . $limit);

			$return = array();
			$this->parser->parse_bbcode = 1;
			$this->parser->strip_quotes = 1;
			$this->parser->parse_nl2br = 1;

			while ($row = $this->DB->fetch_row()) {
				// Parse [doHTML] taggy
				$this->parser->parse_html = $row['usedohtml'];
				$row['post'] = $this->parser->pre_display_parse($row['post']);
				$row['post'] = preg_replace("|([A-Za-z0-9\%<]+)\#EMO_DIR\#([A-Za-z0-9\%>]+)\/|U",$this->ips->skin['_emodir']."/",$row['post']);
				$row['post'] = str_replace("<img src='style_emoticons","<img src='".$this->_options['board_url']."/style_emoticons",$row['post']);
				$row['post'] = str_replace("<img src=\"style_emoticons","<img src=\"".$this->_options['board_url']."/style_emoticons",$row['post']);
				// Add to return array
				$return[] = $row;
			}
			// Have to do attachments after
			foreach($return as $k => $row) {
				$row['attachments'] = $this->get_post_attachments($row['pid']);
				if($row['attachments'] == false) {
					$row['attachments'] = array();
				}
				$return[$k] = $row;
			}
			return $return;
		} else {
			$this->Error($this->lang['zone_noperms']);
			return FALSE;
		}
	}

	/**
	 * Returns information on a topic.
	 *
	 * @param integer $topicid
	 * @return array Topic Information.
	 */
	function get_topic_info ($topicid,$count_view = false) {
		// Check for Post Cache
		if ($cache = $this->get_cache('get_topic_info', $topicid)) {
			return $cache;
		} else {
			$this->DB->query ("SELECT t.*, p.*, g.g_dohtml AS usedohtml FROM ibf_topics t LEFT JOIN ibf_posts p ON (t.tid=p.topic_id) LEFT JOIN ibf_members m ON (p.author_id=m.id) LEFT JOIN ibf_groups g ON (m.mgroup=g.g_id) WHERE t.tid='" . intval($topicid) . "' AND p.new_topic='1'");
			if ($row = $this->DB->fetch_row()) {
				$this->parser->parse_bbcode = 1;
				$this->parser->strip_quotes = 1;
				$this->parser->parse_nl2br = 1;
				// Parse [doHTML] taggy
				$this->parser->parse_html = $row['usedohtml'];
				$row['post'] = $this->parser->pre_display_parse($row['post']);
				$row['post'] = preg_replace("|([A-Za-z0-9\%<]+)\#EMO_DIR\#([A-Za-z0-9\%>]+)\/|U",$this->ips->skin['_emodir']."/",$row['post']);
				$row['post'] = str_replace("<img src='style_emoticons","<img src='".$this->_options['board_url']."/style_emoticons",$row['post']);
				$row['post'] = str_replace("<img src=\"style_emoticons","<img src=\"".$this->_options['board_url']."/style_emoticons",$row['post']);
				$row['post'] = str_replace('target="_blank"',"",$row['post']);
				$row['attachments'] = $this->get_post_attachments($row['pid']);
				if($row['attachments'] == false) {
					$row['attachments'] = array();
				}
				
				// increase view count
				if($count_view === true) $this->DB->query ('UPDATE ibf_topics SET views = views+1 WHERE tid = '.$topicid);
				
				// Save Topic In Cache and Return
				$this->save_cache('get_topic_info', $topicid, $row);
				return $row;
			} else {
				return FALSE;
			}
		}
	}

	/**
	 * Moves a topic to a specified destination forum
	 *
	 * @param integer $topicid
	 * @param integer $destforumid
	 * @param integer $bypassperms Default: '0'
	 * @return bool TRUE on success, FALSE on failure
	 */
	function move_topic($topicid, $destforumid, $bypassperms = '0') {
		// Check params and set obvious variables
		if ($topicinfo = $this->get_topic_info($topicid)) {
			if ($foruminfo = $this->get_forum_info($topicinfo['forum_id'])) {
				if ($destforuminfo = $this->get_forum_info($destforumid)) {
					if ($memberinfo = $this->get_info()) {
						// You are logged in, right?
						if (!$this->is_loggedin()) {
							// Drat... Sorry, but we can't have guests running around moving topics.
							$this->Error($this->lang['zone_membersonly']);
							return FALSE;
						}
						// Flooding
						if ($this->ips->vars['flood_control'] AND !$this->has_perms('g_avoid_flood')) {
							if ((time() - $this->ips->member['last_post']) < $this->ips->vars['flood_control']) {
								$this->Error(sprintf($this->lang['zone_floodcontrol'], $this->ips->vars['flood_control']));
								return FALSE;
							}
						}
						// Check permissions
						if (!$bypassperms) {
							// Is the logged in member the topic author? (or are they a supermod?)
								if (!$topicinfo['author_id'] == $memberinfo['id']) {
									if (!$group['g_is_supmod']) {
										$this->Error($this->lang['zone_noperms']);
										return FALSE;
									}
								}
						}
						// Right, we're finally allowed to move the topic
						if ($this->DB->query("UPDATE ibf_topics SET forum_id = '".$destforumid."' WHERE tid ='".$topicid."' LIMIT 1")) {
							//Now the topic has been moved, clean up the cache for original forum AND destination forum
							$this->update_forum_cache($topicinfo['forum_id'],array('posts' => -$topicinfo['posts'],'topics' => -1));
							$this->update_forum_cache($destforumid,array('posts' => $topicinfo['posts'],'topics' => 1));
							RETURN TRUE;
						} else {
						// This should never be triggered, but lets leave it in as a debug aid
						$this->Error("The topic-moving DB query failed");
						RETURN FALSE;
						}
					} else {
					$this->Error($this->lang['zone_membersonly']);
					RETURN FALSE;
					}
				} else {
				$this->Error($this->lang['zone_forum_notexist']);
				RETURN FALSE;
				}
			} else {
			$this->Error($this->lang['zone_topics_notexist']);
			RETURN FALSE;
			}
		} else {
		$this->Error($this->lang['zone_topics_notexist']);
		RETURN FALSE;
		}
	}
	
	/**
	 * Rates A Topic
	 *
	 * @param integer $topicid
	 * @param integer $rating
	 * @param integer $bypassperms Default: 0=respect board permission, 1=bypass permissions
	 */
	function rate_topic ($topicid, $rating = '', $bypassperms = '0') {
		// Is the rating legit?
		if(!$rating) {
			return FALSE;
		}
		else {
			if($rating >= '6') {
				return FALSE;
			}
		}
		// Flood control, time.
		if (($this->ips->vars['flood_control'] && !$this->has_perms('g_avoid_flood')) OR !$bypassperms) {
			if ((time() - $this->ips->member['last_post']) < $this->ips->vars['flood_control']) {
				$this->Error(sprintf($this->lang['zone_floodcontrol'], $this->ips->vars['flood_control']));
				return FALSE;
			}
		}
		// Get the topic information.
		$this->DB->query ("SELECT * FROM ibf_topics WHERE tid='" . intval($topicid) . "'");
		if ($row = $this->DB->fetch_row()) {
			if($user = $this->get_info()) {
				$forum = $this->get_forum_info($row['forum_id']);
				$group = $this->get_group_info($user['mgroup']);

				// Are ratings allowed?
				if(!$forum['forum_allow_rating']) {
					return FALSE;
				}

				if(!$bypassperms) {
					// Can the member vote?
					if(!$group['g_topic_rate_setting']) {
						return FALSE;
					}
				}

				$userid = intval($user['id']);
				$this->DB->query ("SELECT * FROM ibf_topic_ratings WHERE rating_tid='" . $topicid . "' AND rating_member_id='" . $userid . "'");

				if($rate = $this->DB->fetch_row()) {
					if(($group['g_topic_rate_setting'] == "2") OR $bypassperms) {
						$this->DB->query ("UPDATE ibf_topic_ratings SET rating_value='" . $rating . "' WHERE rating_tid='" . $topicid . "' AND rating_member_id='" . $userid . "'");
						$this->DB->query ("UPDATE ibf_topics SET topic_rating_total=topic_rating_total-'{$rate['rating_value']}'+'" . $rating . "' WHERE tid='" . $topicid . "'");

						return TRUE;
					}
					else {
						return FALSE;
					}
				}
				else {
					$this->DB->query ("INSERT INTO ibf_topic_ratings (rating_tid, rating_member_id, rating_value, rating_ip_address) VALUES('" . $topicid . "', '{$user['id']}', '" . $rating . "', '{$_SERVER['REMOTE_ADDR']}')");
					$this->DB->query ("UPDATE ibf_topics SET topic_rating_total=topic_rating_total+'" . $rating . "', topic_rating_hits=topic_rating_hits+1 WHERE tid='" . $topicid . "'");

					return TRUE;
				}
			}
			else {
				return FALSE;
			}
		}
		else {
			return FALSE;
		}
	}

	/**
	 * Converts topic title to topic-ids
	 *
	 * @param integer $title
	 */
	function topic_title2id($title) {
		$this->DB->query ('SELECT tid FROM ibf_topics WHERE title="' . addslashes(htmlentities($title)) . '"');
		$topics = $this->DB->fetch_row();
		if(is_array($topics) && count($topics) === 1) return  $topics['tid']; // return matching topic-id
		elseif(is_array($topics) && count($topics) > 0) return $topics; // return array of matched topic-ids
		else return false;
	}

	/**
	 * Deletes Forum-Topics contains delivered topic_id
	 *
	 * @param integer $topicid
	 */
	function delete_topic ($topicid) {
		$output = false;
		$t_info = $this->get_topic_info($topicid);

		$this->DB->query("DELETE FROM ibf_topics WHERE tid = '".intval($topicid)."'");
		$this->DB->query("DELETE FROM ibf_posts WHERE topic_id = '".intval($topicid)."'");
		$this->DB->query("DELETE FROM ibf_polls WHERE tid = '".intval($topicid)."'");

		if($this->update_forum_cache($t_info['forum_id'],array('posts' => -$t_info['posts'],'topics' => -1))) $output = true;

		return $output;
	}

	/**
	 * Merges two topics together
	 *
	 * @param integer $topic1id ID('s) of the topic(s) to be merged
	 * @param integer $topic2id ID of topic that $topic1id will be merged into
	 * @param string $title New topic title
	 * @param string $desc New topic description
	 * @param integer $bypassperms Default: 0=respect board permission, 1=bypass permissions
	 * @return bool TRUE on success, FALSE on failure
	 */
	function merge_topics ($topic1id, $topic2id, $title, $desc, $bypassperms = '0') {
		// Is the user logged in...?
		if ($this->is_loggedin()) {
			$postname = $this->ips->member['name'];
		} else {
			// Whoops, we can't have guests activating this, can we?
			$this->Error($this->lang['zone_noperms']);
			return FALSE;
		}
		// Flood control time.
		if ($this->ips->vars['flood_control'] AND !$this->has_perms('g_avoid_flood')) {
			if ((time() - $this->ips->member['last_post']) < $this->ips->vars['flood_control']) {
				$this->Error(sprintf($this->lang['zone_floodcontrol'], $this->ips->vars['flood_control']));
				return FALSE;
			}
		}
		// Are the id(s) for the topic(s)-to-be-merged set?
		if(!isset($topic1id)) {
			return FALSE;
		}
		// We can't have the second topic id in an array, so...
		if(is_array($topic2id)) {
			return FALSE;
		}
		// Are the id's numeric?
		if(!is_numeric($topic1id) OR !is_numeric($topic2id)) {
			// If $topic1id is an array...
			if(is_array($topic1id)) {
				foreach($topic1id as $k => $v) {
					if(!is_numeric($v)) {
						unset($topic1id[$k]);
					}
				}
			}
			else {
				return FALSE;
			}
		}
		// Are the id's identical?
		if($topic1id == $topic2id) {
			return FALSE;
		}
		else {
			// If $topic1id is an array...
			if(is_array($topic1id)) {
				foreach($topic1id as $k => $v) {
					if($v == $topic2id) {
						unset($topic1id[$k]);
					}
				}
			}
		}
		// Let's get some SQL information.
		$this->DB->query ("SELECT  f.*,t.*,p.* FROM ibf_topics t LEFT JOIN ibf_forums f ON (t.forum_id=f.id) LEFT JOIN ibf_posts p ON(t.topic_firstpost=p.pid) WHERE t.tid='" . intval($topic2id) . "'");
		if ($row = $this->DB->fetch_row()) {
			if($user = $this->get_info()) {
				$forum = $this->get_forum_info($row['forum_id']);
				$group = $this->get_group_info($user['mgroup']);
				if(is_array($topic1id)) {
					foreach($topic1id as $v) {
						$topic[] = $this->get_topic_info($v);
					}
				}
				else {
					$topic[] = $this->get_topic_info($topic1id);
				}

				$time = time();

				if(!$bypassperms) {
					if(is_array($topic)) {
						foreach($topic as $k => $v) {
							if($user['id'] == $v['starter_id']) {
								if(!$group['g_edit_topic']) {
									return FALSE;
								}
							}
							else {
								if(!$this->is_supermod()) {
									$mod = $this->get_forum_moderators($forum['id'], $user['id']);
									foreach($mod as $h) {
										if($h['forum_id'] == $user['id']) {
											if(!$h['split_merge']) {
												return FALSE;
											}
										}
									}
								}
							}
						}
					}
				}

				// Is a new title specified...?
				if($title == "") {
					$ltitle = $row['title'];
					$lname = $row['last_poster_name'];
					$laid = $row['last_poster_id'];
					$ptitle = $row['title'];
				}
				elseif($title == $row['last_title']) {
					$ltitle = $row['last_title'];
					$lname = $row['last_poster_name'];
					$laid = $row['last_poster_id'];
					$ptitle = $title;
				}
				elseif($title == $forum['last_title']) {
					$ltitle = $forum['last_title'];
					$lname = $forum['last_poster_name'];
					$laid = $forum['last_poster_id'];
					$ptitle = $title;
				}
				else {
					$ltitle = $forum['last_title'];
					$lname = $forum['last_poster_name'];
					$laid = $forum['last_poster_id'];
					$ptitle = $title;
				}

				// Is a new description specified...?
				if($desc == "") {
					$fdesc = $row['description'];
				}
				else {
					$fdesc = stripslashes($desc);
				}
				// Alright, everything has passed. Time to update the database.
				$this->parser->parse_bbcode = $row['use_ibc'];
				$this->parser->strip_quotes = 1;
				$this->parser->parse_nl2br = 1;
				$this->parser->parse_html = $row['use_html'];
				$this->DB->query ("UPDATE ibf_topics SET title='" . $ptitle . "', description='" . $fdesc . "' WHERE tid='" . $topic2id . "'");
				$this->DB->query ("UPDATE ibf_forums SET last_id='" . $topic2id . "', last_title='" . $ltitle . "', last_poster_name='" . $lname . "', last_poster_id='" . $laid . "' WHERE id='{$topic['forum_id']}'");

				// If $topic is an array, multiple topics will be merged into one.
				if(is_array($topic)) {
					foreach($topic as $v) {
						$this->DB->query ("UPDATE ibf_posts SET topic_id='{$row['tid']}', new_topic='0' WHERE pid='{$v['topic_firstpost']}'");
						$this->DB->query ("UPDATE ibf_forums SET posts=posts+1, topics=topics-1 WHERE id='{$v['forum_id']}'");
						// With the database updated, we can get rid of the old stuff.
						$this->DB->query ("DELETE FROM ibf_topics WHERE tid='" . $v['tid'] . "'");

						// Now to update that post count...
						$this->DB->query ("UPDATE ibf_topics SET posts=posts+1 WHERE tid='" . $topic2id . "'");
					}
				}
				return TRUE;
			}
			else {
				$this->Error($this->lang['zone_noperms']);
				return FALSE;
			}
		} else {
			$this->Error($this->lang['zone_topics_notexist']);
			return FALSE;
		}
	}

	
	// -----------------------------------------------
	// SEARCH FUNCTIONS
	// Search things.
	// -----------------------------------------------
	/**#@+
	 * @group Search
	 */
	/**
	 * Performs a simple search and returns a search id.
	 *
	 * @param string $string Text to search for (SQL rules apply)
	 * @param string $forums Default: '*' (any), or comma-separated list of forum IDs
	 * @param integer $dateorder Default: 0, 1=Order by post_date
	 * @return string Search ID or FALSE on failure
	 */
	function simple_search ($string, $forums = '*', $dateorder = 0, $recursive = 0) {
		// Trim any whitespace off the search string, even if it doesnt exist... Nobody wants to search for whitespace!
		$string = trim($string);
		// Make sure we have been given a search string
		if (strlen($string) < 2) {
			// No search string, tell them off =]
			$this->Error("You must input a search string");
			return FALSE;
		}
		
		// get all subforum ids
		if($recursive == 1 && is_array($forums)) {
			// make proper array for get_all_subforums function
			foreach($forums as $get_sub) {
				$get_sub_forums[]['id'] = $get_sub;
			}

			$forums = array(); // reset array
			// revert array-syntax after getting all subforums
			foreach($this->get_all_subforums($get_sub_forums,'array_ids_only') as $forum) {
				$forums[] = $forum['id'];
			}
		}
		
		// Lets get all the IDs of readable topics and create a comma-separated string from them
		// Use the handy list_forum_topics function to create a multi-dimensional array of all readable topics in the given forums (or all forums if none specified)... 
		// I figured no forum will ever have more than 100000 topics :D
		$multiarray = $this->list_forum_topics($forums,array('orderby' => 'tid', 'limit' => '100000'));
		// For each topic, grab it's ID and add it to the topics string (with a comma of course)
		if(is_array($multiarray) && count($multiarray) > 0) {
			foreach ($multiarray as $topic) {
				$topics[] = $topic['tid'];
			}
			$topics = implode(',',$topics);
		}
		
		// Make sure we have a list of topics to search in (presumable greater than 2 chars :D)
		if (strlen($topics) < 2) {
			// Hmmm something went wrong, so lets output a friendly-ish error message
			$this->Error($this->lang['zone_search_noresults']);
			return FALSE;
		}
		
		// Only work out readable forums if we haven't been given a list of forums to read
		if ($forums = '*') {
			// Get rid of the '*' :D
			$forums = '';
			// Lets get all the IDs of readable forums and create a comma-separated string from them
			// Use the handy get_member_readable_forums function to create a mulit-dimensional array of all readable forums
			$multiarray = $this->get_member_readable_forums();
			// For each forum, grab it's ID and add it to the topics string (with a comma of course)
			if(is_array($multiarray) && count($multiarray) > 0) {
				foreach ($multiarray as $forum) {
					$forums[] = $forum['id'];
				}
				$forums = implode(',',$forums);
			}
			
			// Make sure we have a list of forums to search in
			if (!$forums) {
				// Hmmm something went wrong, so lets output a friendly-ish error message
				$this->Error($this->lang['zone_search_noresults']);
				return FALSE;
			}
		} else {
			if (is_array($forums)) {
				$forums = implode(',',$forums);
			}
		}
		
		// Weird thing - MySQL versions greater than 40010 make this function buggy unless we remove certain characters
		// Get (eventually!) the MySQL version 
		$this->DB->query('SELECT VERSION() AS version');
		if (!$row = $this->DB->fetch_row()) {
			$this->DB->query("SHOW VARIABLES LIKE 'version'");
			$row = $DB->fetch_row();
		}
		$version = explode('.', preg_replace('/^(.+?)[-_]?/', '\\1', $row['version']));
		$version['0'] = (!isset($version) OR !isset($version['0'])) ? '3' : $version['0'];
		$version['1'] = (!isset($version['1'])) ? '21' : $version['1'];
		$version['2'] = (!isset($version['2'])) ? '0' : $version['2'];
		$version = intval(sprintf('%d%02d%02d', $version['0'], $version['1'], intval($version['2'])));
		// We now have the mysql version in an int for later use.
		if ($version >= '40010') {
			// Remove stuff we cant have
			$string = str_replace(array ('|', '&quot;', '&gt;', '%'), array ('|', '\'', '>', ''), trim($string));
		} else {
			$string = str_replace(array ('%', '_', '|'), array ('\\%', '\\_', '|'), trim(strtolower($string)));
			$string = preg_replace('/\s+(and|or)$/' , '' , $string);
		}
		
		
		// Complicated MySQL query =] Basically this counts how many times the search string is found within any topic in any of the readable forums... 
		// Oh, and if the MySQL version is greater than 40010 we have to add "IN BOOLEAN MODE" for complicated MySQL reasons :D
		$this->DB->query("SELECT COUNT(*) as count FROM ibf_posts p WHERE p.topic_id IN ({$topics}) AND MATCH(post) AGAINST ('{$string}' " . (($version >= '40010') ? 'IN BOOLEAN MODE' : '') . ")");
		$row = $this->DB->fetch_row();
		// MySQL counted 0 matches of the search string - it isn't there...
		if ($row['count'] < '1') {
			// No results, so lets output a friendly error message
			$this->Error($this->lang['zone_search_noresults']);
			return FALSE;
		}
		
		// Ok, so we found at least one match... Lets build another complicated MySQL query to store in the database for the search_results function to query.
		$store = "SELECT MATCH(post) AGAINST ('{$string}' " . (($version >= '40010') ? 'IN BOOLEAN MODE' : '') . ") as score, t.approved, t.tid, t.posts AS topic_posts, t.title AS topic_title, t.views, t.forum_id, p.post, p.author_id, p.author_name, p.post_date, p.queued, p.pid, p.post_htmlstate, m. * , me. * , pp. * FROM ibf_posts p LEFT JOIN ibf_topics t ON ( p.topic_id = t.tid ) LEFT JOIN ibf_members m ON ( m.id = p.author_id ) LEFT JOIN ibf_member_extra me ON ( me.id = p.author_id ) LEFT JOIN ibf_profile_portal pp ON ( pp.pp_member_id = p.author_id ) WHERE t.forum_id IN ({$forums}) AND t.tid IN ({$topics}) AND t.title IS NOT NULL AND p.queued IN ( 0, 1 )  AND MATCH(post) AGAINST ('{$string}' " . (($version >= '40010') ? 'IN BOOLEAN MODE' : '') . ")";
		// Date order? 
		if ($dateorder) {
			$store .= ' ORDER BY p.post_date DESC';
		}
		// Generate a unique search id
		$searchid = md5(uniqid(microtime(), 1));
		// Insert it into the database
		// "it" being the search ID we just generated, the current date (timestamp), two topic things which I haven't worked out the point of (yet), 
		// the ID of the logged in member who did the search, their current IP address, a pointless nothing, and obviously, finally, the search results MySQL query...
		$this->DB->query ("INSERT INTO ibf_search_results (id, search_date, topic_id, topic_max, member_id, ip_address, post_id, query_cache) VALUES('{$searchid}', '" . time() . "', '{$topics}', '{$row['count']}', '" . $this->ips->member['id'] . "', '" . $this->ips->input['IP_ADDRESS'] . "', NULL, '" . addslashes($store) . "')");
		// Return the unique search id.
		return $searchid;
	}

	/**
	 * Returns the search results of a search.
	 *
	 * BBCode is stripped off the results. To hilight the search string use str_replace() in your main script.
	 *
	 * @param integer $id
	 * @return array Search Results or FALSE on failure
	 */
	function get_search_results($id) {
		// Make sure we have a search ID
		if (strlen($id) < 3) {
			// No search id, tell them off =]
			$this->Error("You must input a search ID");
			return FALSE;
		}
		// Select the correct/current search from the database
		$this->DB->query ("SELECT * FROM ibf_search_results WHERE id='{$id}'");
		// Only carry on if the above query returned a row
		if ($row = $this->DB->fetch_row()) {
			// Put all the info about the search in a handy array
			$searchinfo = $row;
			// Un-slash the search query and pop it into a variable.... wait, tomato?! =]
			$tomato = stripslashes($row['query_cache']);
			// Run the search query... Isnt that all we wanted to do all along?! tomato?!
			$this->DB->query ($tomato);
			// Create an empty array ready to put the final processed, finished product in
			$results = array();
			// For every post which is returned by the search query, we process it and add it to the results array
			while ($row = $this->DB->fetch_row()) {
				// Apparently we have to strip BBCode and stuff so we look cool...
				$row['post'] = preg_replace('#\[.+?/?\]#', '', $this->parser->pre_display_parse($row['post']));
				// Should we strip smilies here? If so, uncomment the below line.
				// $row['post'] = preg_replace('#\<[^>]+\>#u', '',$row['post']);
				// Fix smilie file paths
				$row['post'] = str_replace("src=\"style_emoticons/","src=\"".$this->board_url."/style_emoticons/",$row['post']);
				$row['post'] = str_replace("src='style_emoticons/","src='".$this->board_url."/style_emoticons/",$row['post']);
				$row['post'] = preg_replace("|([A-Za-z0-9\%<]+)\#EMO_DIR\#([A-Za-z0-9\%>]+)\/|U",$this->ips->skin['_emodir']."/",$row['post']);
				// We won't highlight the word or anything because the user can do it in their script with a simple str_replace.
				// Ok, finished processing the post, lets add the entire row to the results array
				$results[] = $row;
			}
			// Create a multi-dimensional array, with data about the search query, plus the search results... Here we add the search results.
			$searchinfo['results'] = $results;
			// Finally, everything went OK, lets give 'em what they came for! Output the search data!
			return $searchinfo;
		}
		// The search ID is either incorrect, or doesn't exist... Or, of course, something went wrong with simple_search =]
		$this->Error("The specified search ID was not found... :(");
		return FALSE;
	}

	// -----------------------------------------------
	// SKIN FUNCTIONS
	// Functions which do stuff with your skins.
	// -----------------------------------------------
	/**#@+
	 * @group Skins
	 */
	/**
	 * Returns the Skin ID of the skin used by a member.
	 *
	 * @param integer $memberid
	 * @return array Information on Skin or FALSE on failure
	 */
	function get_skin_id ($memberid = '') {
		if ($memberid) {
			$info = $this->get_advinfo($memberid);
		} else {
			$info = $this->get_advinfo();
		}

		if ($info['skin']) {
			return $info['skin'];
		} else {
			/*
			Thanks to Foxrer who reported the discrepancies
			between uid and sid and Ripper for his research
			into it :) */

			$this->DB->query ("SELECT set_skin_set_id FROM ibf_skin_sets WHERE set_default='1'");
			if ($row = $this->DB->fetch_row()) {
				return $row['set_skin_set_id'];
			} else {
				return FALSE;
			}
		}
	}

	/**
	 * Gets information on a skin.
	 *
	 * @param integer $skinid
	 * @return array Information on Skin or FALSE on failure
	 */
	function get_skin_info ($skinid) {
		// Adapted from the original function submitted by ripper
		if ($skinid >= 0) { // If they've specified a skin
			$this->DB->query ("SELECT * FROM ibf_skin_sets WHERE set_skin_set_id='" . $skinid . "'");

			if ($row = $this->DB->fetch_row()) {
				return $row;
			} else {
				return FALSE;
			}
		} else { // Or nowt
			return FALSE;
		}
	}

	/**
	 * Changes the current user's skin.
	 *
	 * @param integer $skinid
	 * @param integer $userid
	 * @return bool Success.
	 */
	function set_user_skin ($skinid, $userid = '') {
		// Check it exists
		if ($this->get_skin_info($skinid)) {
			// Grab current member id unless specified
			if (!$userid) {
				$userid = $this->ips->member['id'];
			}

			if ($this->update_member(array('skin' => $skinid), $userid)) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			$this->Error($this->lang['zone_skin_notexist']);
			return FALSE;
		}
	}

	/**
	 * Grabs the IDs of all the avaliable skins.
	 *
	 * @return array Skin IDs
	 */
	function list_skins () {
		// Grab all skins which aren't hidden
		$this->DB->query ("SELECT set_skin_set_id FROM ibf_skin_sets WHERE set_hidden='0'");
		$skins = array();

		while ($row = $this->DB->fetch_row()) {
			$skins[] = $row['set_skin_set_id'];
		}

		return $skins;
	}

	/**
	 * Pulls and displays CSS from forums depending on user's skin.
	 *
	 * Use the optional $return parameter to store the CSS in a string for
	 * later processing, instead of instantly sending it to the browser.
	 *
	 * @param bool $return Whether to return CSS instead of sending it to the browser.
	 * @param bool $add_tag Whether to wrap the CSS with the STYLE tag.
	 * @return string The Style sheet if $return was set, void else
	 * @since 1.1 $add_tag added, ID attribute for STYLE tag
	 */
	function get_css($return = FALSE, $add_tag=TRUE) {

		$skin = $this->get_skin_info($this->get_skin_id()); // there we have the $skin var now..

		$getcss = $skin['set_skin_set_id']; // heh css id please

		$this->DB->query ("SELECT set_skin_set_id FROM ibf_skin_sets WHERE set_default = 1");

		$default = $this->DB->fetch_row();

		if ($getcss == '') { // what if its 0 (guest etc)
			$getcss = $default['css_id']; // make it the default skin - change 13 to match
		}

		// now we have the table.. but now what?
		// apparently, we have to grab the cached css, as the normal field tends to disappear
		$this->DB->query ('SELECT set_cache_css FROM ibf_skin_sets WHERE set_skin_set_id = ' . $getcss . '');
		$css = $this->DB->fetch_row();
		$css = $css['set_cache_css'];
		// convert <#IMG_DIR#>
		$css = str_replace('<#IMG_DIR#>' , '' . $skin['set_image_dir'] . '', $css);
		// convert to lead to forums
		$css = str_replace('style_images' , '' . $this->board_url . '/style_images', $css);
		$img_dir = $skin['set_image_dir'];

		// and here are the awesome style tags (used for later)
		// with an ID to use client side scripting
		if ($add_tag) {
			$style = '<style type="text/css" id="css_'.$getcss.'">' . $css . '</style>';
		} else {
			$style = $css;
		}

		if ($return) {
			return $style;
		} else {
			echo $style;
		}
	}
	// -----------------------------------------------
	// STATISTICS FUNCTIONS
	// Functions which retrieve misc. stats on IPB
	// -----------------------------------------------
	/**#@+
	 * @group Statistics
	 */
	/**
	 * Gets board statistics.
	 *
	 * @return array Board Statistics
	 */
	function get_board_stats() {
		// Check for cache
		if ($cache = $this->get_cache('get_board_stats', '1')) {
			return $cache;
		} else {
			$this->DB->query ('SELECT cs_value FROM ibf_cache_store WHERE cs_key = "stats"');
			$row = $this->DB->fetch_row();
			// Because I don't like all these capitals and ugly column names
			$stats = unserialize(stripslashes($row['cs_value']));
			/*$stats = array(
				// Totals
				'total_replies' => $row['TOTAL_REPLIES'],
				'total_topics' => $row['TOTAL_TOPICS'],
				'total_members' => $row['MEM_COUNT'],
				// Members
				'newest_member_id' => $row['LAST_MEM_ID'],
				'newest_member_name' => $row['LAST_MEM_NAME'],
				// Most Online Statistics
				'most_online_count' => $row['MOST_COUNT'],
				'most_online_date' => $row['MOST_DATE'],
				);
			*/
			$this->save_cache('get_board_stats', '1', $stats);
			return $stats;
		}
	}

	/**
	 * Returns the version of the board according to the upgrade table.
	 *
	 * @return string Information on Board Version.
	 */
	function ipb_version($human=TRUE) {
		$this->DB->query("SELECT upgrade_version_id, upgrade_version_human FROM ibf_upgrade_history ORDER BY upgrade_id DESC");
		$board_version = $this->_options['board_version'];
		while($ver = $this->DB->fetch_row()) {
			$humanver[$ver['upgrade_version_id']] = $ver['upgrade_version_human'];
			if($ver['upgrade_version_id'] > $board_version) {
				$board_version = $ver['upgrade_version_id'];
			}
		}
		return ($human ? $humanver[$board_version] : $board_version);
	}
	function boink_it($url="",$msg="")
	{
			echo <<<EOF
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
			<html xml:lang="en" lang="en" xmlns="http://www.w3.org/1999/xhtml"> 
				<head>
				    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" /> 
					<title>Redirecting...</title>
					<meta http-equiv="refresh" content="2; url=$url" />
<link href="style.css" type="text/css" rel="stylesheet" />
							<script type='text/javascript'>
							//<![CDATA[
							// Fix Mozilla bug: 209020
							if ( navigator.product == 'Gecko' )
							{
								navstring = navigator.userAgent.toLowerCase();
								geckonum  = navstring.replace( /.*gecko\/(\d+)/, "$1" );

								setTimeout("moz_redirect()",1500);
							}

							function moz_redirect()
							{
								var url_bit     = "{$url}";
								window.location = url_bit.replace( new RegExp( "&amp;", "g" ) , '&' );
							}
							//>
							</script>
							</head>
							<body>
								<div id="redirectwrap">
									<h4>Redirecting...</h4>
									<p>{$msg}<br /><br />Wait while you are being redirected</p>
									<p class="redirectfoot">(<a href="$url">Click here if you dont want to wait</a>)</p>
								</div>
							</body>
						</html>
				
EOF;
}


 /*-------------------------------------------------------------------------*/
    // Show Board Offline
    /*-------------------------------------------------------------------------*/
    
    /**
	* Show board offline message
    *
	* @return	void
	* @since	2.0
	*/
    function zone_offline()
    {
	global $zone;
    	//-----------------------------------------
    	// Get offline message (not cached)
    	//-----------------------------------------	
$this->DB->query("SELECT portal_offline, offline_msg FROM portal_settings");
$row = $this->DB->fetch_row($query);
if ($row['portal_offline'] == 1) {
    	$msg = preg_replace( "/\n/", "<br />", stripslashes( $row['offline_msg'] ) );
    	
    	$zone->echo->html .= $zone->skin_global->zone_offline( $msg );
    	$zone->echo->output();
	}
}

} // class zone

?>