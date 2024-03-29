<?php
	/**
	 * @author			Matthias Reuter ($LastChangedBy: matthias $)
	 * @version			$LastChangedDate: 2009-08-26 19:19:41 +0200 (Mi, 26 Aug 2009) $
	 * @package			stats
	 * @copyright		2007-2009 IPBWI development team
	 * @link			http://ipbwi.com/examples/stats.php
	 * @since			2.0
	 * @license			http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
	 */
	class ipbwi_stats extends ipbwi {
		private $ipbwi			= null;
		/**
		 * @desc			Loads and checks different vars when class is initiating
		 * @author			Matthias Reuter
		 * @since			2.0
		 * @ignore
		 */
		public function __construct($ipbwi){
			// loads common classes
			$this->ipbwi = $ipbwi;
		}
		/**
		 * @desc			Gets board statistics.
		 * @return	array	Board Statistics
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->stats->board();
		 * </code>
		 * @since			2.0
		 */
		public function board(){
			// Check for cache
			if($cache = $this->ipbwi->cache->get('statsBoard', '1')){
				return $cache;
			}else{
				self::$ips->DB->query('SELECT cs_value FROM ibf_cache_store WHERE cs_key = "stats"');
				$row = self::$ips->DB->fetch_row();
				$stats = unserialize(stripslashes($row['cs_value']));
				$this->ipbwi->cache->save('statsBoard', 1, $stats);
				return $stats;
			}
		}
		/**
		 * @desc			Returns the active user count.
		 * @return	array	Active User Count
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->stats->activeCount();
		 * </code>
		 * @since			2.01
		 */
		 function activeCount() {
			if($cache = $this->ipbwi->cache->get('activeCount', '1')){
				return $cache;
			}else{
				// Init
				$count = array('total' => '0', 'anon' => '0', 'guests' => '0', 'members' => '0');
				$cutoff = self::$ips->vars['au_cutoff'] ? self::$ips->vars['au_cutoff'] : '15';
				$timecutoff = time() - ($cutoff * 60);
				self::$ips->DB->query('SELECT member_id, login_type FROM ibf_sessions WHERE running_time > "'.$timecutoff.'"');
				// Let's cache so we don't screw ourselves over :)
				$cached = array();
				// We need to make sure our man's in this count...
				if($this->ipbwi->member->isLoggedIn()){
					if(substr(self::$ips->member['login_anonymous'],0, 1) == '1'){
						++$count['anon'];
					}else{
						++$count['members'];
					}
					$cached[self::$ips->member['id']] = 1;
				}
				while($row = self::$ips->DB->fetch_row()){
					// Add up members
					if($row['login_type'] == '1' && !array_key_exists($row['member_id'],$cached)){
						++$count['anon'];
						$cached[$row['member_id']] = 1;
					}elseif($row['member_id'] == '0'){
						++$count['guests'];
					}elseif(!array_key_exists($row['member_id'],$cached)){
						++$count['members'];
						$cached[$row['member_id']] = 1;
					}
				}
				$count['total'] = $count['anon'] + $count['guests'] + $count['members'];
				$this->ipbwi->cache->save('activeCount', 'detail', $count);
				return $count;
			}
		}
		/**
		 * @desc			Returns members born on the given day of a month.
		 * @param	int		$day Optional. Current day is used if left as an empty string or zero.
		 * @param	int		$month Optional. Current month is used if left as an empty string or zero.
		 * @return	array	Birthday Members
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->stats->birthdayMembers();
		 * $ipbwi->stats->birthdayMembers(22,7);
		 * </code>
		 * @since			2.01
		 */
		function birthdayMembers($day = 0, $month = 0) {
			if((int)$day<=0){
				$day = date('j');
			}
			if((int)$month<=0){
				$month = date ('n');
			}
			self::$ips->DB->query('SELECT m.*, me.signature, me.avatar_size, me.avatar_location, me.avatar_type, me.vdirs, me.location, me.msnname, me.interests, me.yahoo, me.website, me.aim_name, me.icq_number, g.*, cf.* FROM ibf_members m LEFT JOIN ibf_groups g ON (m.mgroup=g.g_id) LEFT JOIN ibf_pfields_content cf ON (cf.member_id=m.id) LEFT JOIN ibf_member_extra me ON (m.id=me.id) WHERE m.bday_day="'.intval($day).'" AND m.bday_month="'.intval($month).'"');
			$return = array();
			$thisyear = date ('Y');
			while($row = self::$ips->DB->fetch_row()){
				$row['age'] = $thisyear - $row['bday_year'];
				$return[] = $row;
			}
			return $return;
		}
	}
?>