<?php

class lib_replays
{
	/**
	 * Internal buffer, false or up to 1 kb of binary data.
	 * This size is subject to change depending on the value submitted
	 * to the read_head method.
	 *
	 * @var string|bool
     * @access protected
	 */
	var $buf = false;
	/**
	 * Size of file being read in bytes.
	 * Storing here since path is not transmitted anymore to fetch_infos()
	 *
	 * @var int
     * @access protected
	 */
	var $size = 0;
	/**
	 * Array containing the replay informations after parsing.
	 * You fill this, external code reads it.
	 * Only valid if the parse() method returned true.
	 *
	 * @var array
     * @access public
	 */
	var $r_infos = array();

	/**
	 * Ping back the return value after cleaning the buffer.
	 * This method is used to make sure the buffer is cleaned whatever happens.
	 *
	 * @param bool $status Return value to ping back
	 * @return bool $status
	 */
	function full_return($status = false)
	{
		$this->buf = false;
		$this->size = 0;
		return $status;
	}

	/**
	 * Read the file top $size bytes into the buffer for later parsing.
	 * Read the first $size bytes of the file $path into the class buffer
	 * Does not read using an incremential buffer, we only grab the first X
	 * bytes from the file and hope we find the header there.
	 * Okay for all Sage engine game, but you should probably change that for
	 * other games.
	 *
	 * @param string $path Path to the file (must be php readable)
	 * @param int $size Quantity of data to read, in bytes. Defaulting to 1024.
	 * @return bool false if any error occurs, true otherwise
	 */
	function read_head($path, $size = 1024)
	{
		if (!($handle = @fopen($path, "r"))) // (supress warning since most php users display errors ...)
			return false;
		$this->buf = fread($handle, $size); // 1024 should be enough
		fclose($handle);
		if (!$this->buf)
			return false;
		$this->size = filesize($path);
		return true;
	}

	/**
	 * Check if file header is valid.
	 * Dummy, childs implements it (returning false here for poor abstract
	 * class alternative).
	 *
	 * @abstract
	 * @return bool false
	 */
	function check_head()
	{
		return false;
	}

	/**
	 * Initialize the $r_infos array.
	 * Not used here, given for convenience of child classes.
	 * Feel free to override.
	 */
	function init_infos()
	{
		$this->r_infos = array(
			'players' => array(),
			'map' => array(),
			'options' => array(),
			'misc' => array()
			);
	}

	/**
	 * Fill $r_infos with the data in buf.
	 * Dummy, childs implements it (returning false here for poor abstract
	 * class alternative).
	 *
	 * @abstract
	 * @return bool false
	 */
	function fetch_infos()
	{
		return false;
	}

	/**
	 * Parsing file given as argument, filling $r_infos with values.
	 * Will return false if any error is encountered, true otherwise. If true
	 * is returned, the replay details can be read in $r_infos.
	 *
	 * @param string $path
	 * @return bool true if success, false otherwise
	 */
	function parse($path)
	{
		if (!$this->read_head($path))
			return $this->full_return();
		if (!$this->check_head())
			return $this->full_return();
		if (!$this->fetch_infos())
			return $this->full_return();
		return $this->full_return(true);
	}
}

/**
 * CnC3 datas conversion array
 * You can get elements' name from their index here.
 * @global array $GLOBALS['_cnc3data']
 * @name $_cnc3data
 */
$GLOBALS['_cnc3data'] = array(
	'armies' => array(
		1 => 'Random',
		2 => 'Observer',
		3 => 'Commentator',
		/* 4 and 5 ? */
		6 => 'GDI',
		7 => 'NOD',
		8 => 'Scrin'
		),
	'versions' => array(
		'1.2.2613.21264' => '1.02',
		'1.3.2615.35899' => '1.03',
		'1.4.2620.25554' => '1.04',
		'1.5.2674.29882' => '1.05',
		'1.9.2801.21826' => '1.09'
		),
	'maps' => array(
		/* official maps */
		'data/maps/official/map_mp_2_simon' => 'Action river',
		'data/maps/official/map_mp_2_black2' => 'Arena tournament',
		'data/maps/official/map_mp_2_black6' => 'Great battle Black',
		'data/maps/official/map_mp_2_black5' => 'Small Town USA',
		'data/maps/official/map_mp_2_black9' => 'Pipeline Problems',
		'data/maps/official/map_mp_2_black10' => 'Sertão deadly',
		'data/maps/official/map_mp_2_black3' => 'Territories sorry Barstow',
		'data/maps/official/map_mp_2_black7' => 'Tournament Tower',
		'data/maps/official/map_mp_2_bass1' => 'Tournament Desert',
		'data/maps/official/map_mp_3_black1' => 'Advantage unbalanced',
		'data/maps/official/map_mp_3_black2' => 'Triple menace',
		'data/maps/official/map_mp_4_black1' => 'The crater of carnage',
		'data/maps/official/map_mp_4_bass' => 'Desert devic',
		'data/maps/official/map_mp_4_bender' => 'The battle for the land of Egypt',
		'data/maps/official/map_mp_4_rao' => 'Redzone Rampage',
		'data/maps/official/map_mp_4_black6' => 'Rixe tumultuous',
		'data/maps/official/map_mp_6_hayes' => 'Six feet below ground',
		'data/maps/official/map_mp_6_black2' => 'Symphony explosive',
		'data/maps/official/map_mp_8_bass' => 'The Rocktogone',
		'data/maps/official/map_mp_8_black1' => 'Massacre boundary',
		/* 1.05 */
		'data/maps/official/map_mp_2_black12' => 'Schlachtfeld Stuttgart',
		'data/maps/official/map_mp_2_chuck1' => 'Tournament coast',
		'data/maps/official/map_mp_2_will1' => 'Tournament Fault',
		'data/maps/official/map_mp_4_chuck1' => 'Chaos on the coast',
		/* semi-official maps (from EB/BestBuy pre-orders */
		'data/maps/internal/map_mp_2_black11' => 'The lethal weapons',
		'data/maps/internal/map_mp_4_black5' => 'Valley of Death',
		'data/maps/internal/map_mp_6_black1' => 'Backwater Brawl',
		/* unofficial maps */
		'data/maps/internal/fallen_empire_classic' => 'Fallen Empire Classic'
		),
	'colors' => array(
		-1 => '#000000',	// Random
		0 => '#2B2BB3',		// Navy
		1 => '#FCE953',		// Yellow
		2 => '#00A744',		// Green
		3 => '#FD7602',		// Orange
		4 => '#FB7FD3',		// Pink
		5 => '#8301FC',		// Purple
		6 => '#D50000',		// Red
		7 => '#04DAFA'		// Cyan
		),
	'ia_name' => array(
		'CE' => 'IA Easy',
		'CM' => 'IA Medium',
		'CH' => 'IA Difficult',
		'CB' => 'IA Brutal'
		),
	'ia_mode' => array(
		-2 => 'Random',
		/* -1 ? */
		0 => 'Balanced',
		1 => 'Fast Attack',
		2 => 'Development quiet',
		3 => 'Guerilla',
		4 => 'Rouleau compresseur'
		)
	);
/**
 * Generals
 *
 * @package Replay parsing
 * @subpackage Generals Replay
 */
class generals_replay extends lib_replays
{
function stReplayFile()
{
	
	$filename;
	$filesize;
	$year;
	$month;
	$dayofweek;
	$day;
	$our;
	$minute;
	$ec;
	$msec;
	$version;
	$versionDate;
	$majorVersion;
	$minorVersion;
	$mapName;

}

function stPlayerInfo()
{
	$playerName;
	$playerUID;
	$var3;
	$FTTT;
	$colorChosen;
	$sideChosen;
	$mapPosChosen;
	$teamChosen;
	$var9;
	$isObserver;

}


}

/**
 * CNC 3 Tiberium Wars class.
 *
 * @package Replay parsing
 * @subpackage CnC3 Replay
 */
class cnc3_replay extends lib_replays
{
	/**
	 * Check the file's head against CnC3 header.
	 * There are all kinds of symbols everywhere to check for replay validity
	 * and even a footer, but I honestly think we can limit ourself to the
	 * header. If a file has a valid header but isn't a cnc3 replay it will
	 * fail at regex check anyway.
	 *
	 * @return bool false if any error occurs, true otherwise
	 */
	function check_head()
	{
		if (substr($this->buf, 0, 18) == 'C&C3 REPLAY HEADER')
			return true;
		return false;
	}

	/**
	 * Fetch the data about the replay from the buffer to the $r_info array.
	 * Kind of simple: we check if the buffer contains the data fields,
	 * if yes we parse it.
	 *
	 * @return bool false if any error occurs, true otherwise
	 */
	function fetch_infos()
	{
		$regex = '#M=([a-zA-Z/0-9_]+);'   // map
				.'MC=([0-9A-Z]+);'		  // map crc ?
				.'MS=([0-9]+);'			  // ?
				.'SD=([0-9]+);'			  // Seed, could be used to resolve random to army/color/...
				.'GSID=([0-9A-Z]+);'	  // gsid = battlecast id ?
				.'GT=(-?[0-9]+);'		  // ?
				.'PC=(-?[0-9]+);'		  // post commentator (-1 when details added) ?
				.'RU=([0-9 -]+);'		  // options
				.'S=(([^:]+:){8});'		  // players
				.'.+'				  	  // garbage (contains game name in local language)
				.'\x0E\x00\x00\x00'		  // version header
				.'(\d\.\d\.\d{4}\.\d{5})' // version value
        		.'#Us';                   // regex options
		if (preg_match($regex, $this->buf, $matches) == 0)
			return false;
		$this->init_infos();
		if (!$this->read_players($matches))
			return false;
		if (!$this->read_map($matches))
			return false;
		if (!$this->read_misc($matches))
			return false;
		if (!$this->read_options($matches))
			return false;
		return true;
	}

	/**
	 * Read various replay informations.
	 * As for now: Length, gsid, version, commented.
	 *
	 * @param array &$matches Matches array from the regex matching
	 * @return bool false if any error occurs, true otherwise
	 */
	function read_misc(&$matches)
	{
		$this->r_infos['misc'] = array(
			'gsid' => $matches[5],		// gsid = battlecast id ?
			'version' => $matches[11],
			'commented' => ($matches[7] == '-1' ? true : false), // unsure -- need check
			/**
			 * Length computing is not-that-perfect: this value could be
			 * entirely wrong. The only way to get a valid amount would be by
			 * parsing the whole file's blocks (but it represent an estimated
			 * amount of seconds elapsed during the game)
			 */
			'length' => round((($this->size / 1024) / (0.18 * count($this->r_infos['players'])))
							  - (($this->size / 1536) - (104 * count($this->r_infos['players']))))
			);
		if ($this->r_infos['misc']['commented']) {
			/**
			 * Length couldn't really be trusted, this one is even worse :)
			 * Unsure about wether commentator detection is valid in the first
			 * place, then there is no way to estimate how much voice and
			 * signals are stored in the replay so we roughly do an estimation.
			 */
			$this->r_infos['misc']['length']
				= round($this->r_infos['misc']['length'] / 2.20);
		}
		return true;
	}

	/**
	 * Read the game options
	 *
	 * @param array &$matches Matches array from the regex matching
	 * @return bool false if any error occurs, true otherwise
	 */
	function read_options(&$matches)
	{
		$o = explode(' ', $matches[8]);
		/**
		 * There are plenty of things here (every options of multiplayer games
		 * are stored here), but we only extract some usefull information.
		 * If you need more, look at what is stored into $o.
		 */
		$this->r_infos['options'] = array(
			'speed' => $o[1], // %
			'money' => $o[2], // $
			'delay' => $o[5], // minutes -- battlecast delay
			'crates' => ($o[5] == 1 ? true : false)
			);
		return true;
	}

	/**
	 * Read the map internal path
	 *
	 * @param array &$matches Matches array from the regex matching
	 * @return bool false if any error occurs, true otherwise
	 */
	function read_map(&$matches)
	{
		$this->r_infos['map'] = array(
			'fname' => substr($matches[1], 3)
			);
		return true;
	}

	/**
	 * Converting IP (so-called "uid" ...) from hex to dec
	 *
	 * @param string $uid A player's UID
	 * @return string Player's IPv4
	 */
	function uid2ip($uid)
	{
		$ip = '';
		for ($i = 0; $i < 4; $i++)
			$ip .= hexdec(substr($uid, $i * 2, 2)).'.';
		return substr($ip, 0, -1);
	}

	/**
	 * Fetching one player data into it's row
	 *
	 * @param array &$player One row of the players' array
	 */
	function read_player(&$player)
	{
		$p = explode(',', $player);
		switch ($p[0]{0}) {
			case 'H': 	// human
				if ($p[0] == 'Hpost Commentator') // hard coded ?
					return;
				$this->r_infos['players'][] = array(
					'clan' => $p[11],
					'army' => $p[5],
					'color' => $p[4],
					'position' => $p[6],			// N/A => 0, else pos number
					'handicap' => $p[8],			// %, afaik only negatives or 0 can be found
					'human' => true,
					'team' => $p[7] + 1, 			// N/A => 0, else team number
					'uid' => $p[1],					// Player's IP in hex base
					'ip' => $this->uid2ip($p[1]),
					'name' => substr($p[0], 1)		// Remove H at start
					);
				break;
			case 'C':	// computer
				$this->r_infos['players'][] = array(
					'army' => $p[2],
					'color' => $p[1],
					'handicap' => $p[5],
					'human' => false,
					'team' => $p[4] + 1,
					'fname' => $p[0],
					'ia_mode' => $p[6],
					'position' => $p[3]
					);
				break;
			case 'X': return; break;  // empty slot written as X
		}
	}

	/**
	 * Read through the whole players' data and extract one player's
	 * informations at a time
	 *
	 * @param array &$matches Matches array from the regex matching
	 * @return bool false if any error occurs, true otherwise
	 */
	function read_players(&$matches)
	{
		$p = explode(':', $matches[9]);
		unset($p[count($p) - 1]); // explode produces dummy at end
		foreach ($p as $player)
			$this->read_player($player);
		if (!count($this->r_infos['players']))
			return false;
		return true;
	}
}
?>