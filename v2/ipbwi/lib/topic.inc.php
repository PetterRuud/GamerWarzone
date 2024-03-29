<?php
	/**
	 * @author			Matthias Reuter ($LastChangedBy: matthias $)
	 * @version			$LastChangedDate: 2009-08-26 19:19:41 +0200 (Mi, 26 Aug 2009) $
	 * @package			topic
	 * @copyright		2007-2009 IPBWI development team
	 * @link			http://ipbwi.com/examples/topic.php
	 * @since			2.0
	 * @license			http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
	 */
	class ipbwi_topic extends ipbwi {
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
		 * @desc			creates a new topic
		 * @param	int		$forumID Forum-ID where topic will be created
		 * @param	string	$title Topic-Title
		 * @param	string	$post Topic-Post
		 * @param	string	$desc Topic-Description
		 * @param	bool	$useEmo set to true to enable emoticons in post
		 * @param	bool	$useSig set to true to enable signature in post
		 * @param	bool	$bypassPerms set to true to bypass permissions
		 * @param	string	$guestName Author's name for Guest
		 * @return	int		topic ID if topic was created, otherwise false
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->topic->create(55,'topic title','topic post');
		 * $ipbwi->topic->create(55,'topic title','topic post','topic description',false,false,true,true);
		 * </code>
		 * @since			2.0
		 */
		public function create($forumID,$title,$post,$desc=false,$useEmo=true,$useSig=true,$bypassPerms=false,$guestName=false){
			if(!$title){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('topicNoTitle'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			if($this->ipbwi->member->isLoggedIn()){
				$postName = self::$ips->member['members_display_name'];
			}else{
				if($guestName){
					$postName = self::$ips->vars['guest_name_pre'].$guestName.self::$ips->vars['guest_name_suf'];
				}else{
					$postName = self::$ips->member['members_display_name'];
				}
			}
			// No Posting
			if(self::$ips->member['restrict_post']){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			// Flooding
			if(self::$ips->vars['flood_control'] AND !$this->ipbwi->permissions->has('g_avoid_flood')){
				if((time() - self::$ips->member['last_post']) < self::$ips->vars['flood_control']){
					$this->ipbwi->addSystemMessage('Error',sprintf($this->ipbwi->getLibLang('floodControl'), $this->ips->vars['flood_control']),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
			}
			// Check some Forum Stuff
			self::$ips->DB->query('SELECT * FROM ibf_forums WHERE id="'.intval($forumID).'"');
			if($row = self::$ips->DB->fetch_row()){
				// Check User can Post to Forum
				if($this->ipbwi->forum->isStartable($row['id']) OR $bypassPerms){
					// Queuing
					if(!$this->ipbwi->permissions->has('g_avoid_q') && ($row['preview_posts'] == 2 OR $row['preview_posts'] == 1 OR self::$ips->member['mod_posts'])){
						$preview = 1;
					}else{
						$preview = 0;
					}
					$time = time();
					// Insert Topic Bopic
					self::$ips->DB->query('INSERT INTO ibf_topics (title, description, state, posts, starter_id, start_date, last_poster_id, last_post, starter_name, last_poster_name, views, forum_id, approved, author_mode, pinned) VALUES ("'.$title.'", "'.$desc.'", "open", "0", "'.self::$ips->member['id'].'", "'.$time.'", "'.self::$ips->member['id'].'", "'.$time.'", "'.$postName.'", "'.$postName.'", "0", "'.$forumID.'", "'.($preview ? '0' : '1').'", "1", "0")');
					$topicID = self::$ips->DB->get_insert_id();
					self::$ips->parser->parse_bbcode	= $row['use_ibc'];
					self::$ips->parser->strip_quotes	= 1;
					self::$ips->parser->parse_nl2br		= 1;
					self::$ips->parser->parse_html		= $row['use_html'];
					self::$ips->parser->parse_smilies	= ($useEmo ? 1 : 0);
					$post	= self::$ips->parser->pre_db_parse($post);
					$post	= $this->ipbwi->makeSafe($post);
					self::$ips->DB->query('INSERT INTO ibf_posts (author_id, author_name, use_emo, use_sig, ip_address, post_date, post, queued, topic_id, new_topic, icon_id, post_htmlstate) VALUES ("'.self::$ips->member['id'].'", "'.$postName.'", "'.($useEmo ? 1 : 0).'", "'.($useSig ? 1 : 0).'", "'.$_SERVER['REMOTE_ADDR'].'", "'.$time.'", "'.$post.'", "0", "'.$topicID.'", "1", "0", "'.self::$ips->parser->parse_html.'")');
					// Finally update the forums
					self::$ips->DB->query('UPDATE ibf_forums SET last_poster_id="'.self::$ips->member['id'].'", last_poster_name="'.$postName.'", topics=topics+1, last_post="'.$time.'", last_title="'.$title.'", last_id="'.$topicID.'" WHERE id="'.intval($forumID).'"');
					// Oh yes, any update the post count for the user
					if(self::$ips->member['id'] != 0){
						if($row['inc_postcount']){
							self::$ips->DB->query('UPDATE ibf_members SET posts=posts+1, last_post="'.time().'" WHERE id="'.self::$ips->member['id'].'" LIMIT 1');
						}else{
							self::$ips->DB->query('UPDATE ibf_members SET last_post="'.time().'" WHERE id="'.self::$ips->member['id'].'" LIMIT 1');
						}
					}
					// And stats
					$this->ipbwi->cache->updateForum(intval($forumID),array('topics' => 1));
					return $topicID;
				}else{
					$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
			}else{
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('forumNotExist'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
		}
		/**
		 * @desc			edits a topic
		 * @param	int		$topicID Topic-ID which has to be edited
		 * @param	string	$title Topic-Title
		 * @param	string	$post Topic-Post
		 * @param	string	$desc Topic-Description
		 * @param	string	$reason reason for editing
		 * @param	bool	$close Close the topic
		 * @param	bool	$pin Pin the topic
		 * @param	bool	$approve Make the topic approved or not
		 * @param	bool	$useEmo set to true to enable emoticons in post
		 * @param	bool	$useSig set to true to enable signature in post
		 * @param	bool	$bypassPerms set to true to bypass permissions
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->topic->edit(55,'topic title','topic post');
		 * $ipbwi->topic->edit(55,'topic title','topic post','topic description','Reason',true,true,true,true,false,false,true);
		 * </code>
		 * @since			2.0
		 */
		public function edit($topicID, $title, $post, $desc=false, $reason=false, $close=false, $pin=false, $approve=true, $useEmo=true, $useSig=true, $bypassPerms=false){
			$title	= $this->ipbwi->makeSafe($title);
			$post	= $this->ipbwi->makeSafe($post);
			$desc	= $this->ipbwi->makeSafe($desc);
			$reason	= $this->ipbwi->makeSafe($reason);
			// You are logged in, right?
			if($this->ipbwi->member->isLoggedIn()){
				$postName = self::$ips->member['name'];
			}else{
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			// Flooding
			if(self::$ips->vars['flood_control'] && !$this->ipbwi->permissions->has('g_avoid_flood') && (time() - self::$ips->member['last_post']) < self::$ips->vars['flood_control']){
				$this->ipbwi->addSystemMessage('Error',sprintf($this->ipbwi->getLibLang('floodControl'),self::$ips->vars['flood_control']),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			$time = time();
			// Let's extract the information.
			self::$ips->DB->query('SELECT  f.*,t.*,p.* FROM ibf_topics t LEFT JOIN ibf_forums f ON (t.forum_id=f.id) LEFT JOIN ibf_posts p ON(t.topic_firstpost=p.pid) WHERE t.tid="'.intval($topicID).'"');
			if($row = self::$ips->DB->fetch_row()){
				if($user = $this->ipbwi->member->info()){
					$forum = $this->ipbwi->forum->info($row['forum_id']);
					$group = $this->ipbwi->group->info($user['mgroup']);
					// Get permissions
					if(empty($bypassPerms)){
						// Is the topic closed...?
						if(($row['state'] != 'open') AND !$group['g_post_closed']){
							$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
							return false;
						}elseif(isset($close) && $close == 1 && $group['g_open_close_posts']){
							// Is the topic being closed?
							$state = 'closed';
							$closed = $time;
							$opened = $row['topic_open_time'];
						}elseif(empty($close) OR !$group['g_open_close_posts']){
							$state = 'open';
							$closed = $row['topic_close_time'];
							$opened = $time;
						}elseif($group['g_open_close_posts']){
							$state = 'open';
							$closed = $row['topic_close_time'];
							$opened = $time;
						}
						// Now that this has passed by, can they edit?
						if($row['author_id'] == $user['id'] && !$group['g_edit_topic']){
							$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
							return false;
						}elseif(!$group['g_is_supmod']){
							$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
							return false;
						}
					}
					// Is a new title specified...?
					if(!$title){
						$ptitle = $forum['last_title'];
					}elseif($title == $forum['last_title']){
						$ptitle = $forum['last_title'];
					}else{
						$ptitle = $title;
					}
					// Is there a new description?
					if(!$desc){
						$fdesc = $row['description'];
					}else{
						$fdesc = $desc;
					}
					// Has the post been changed in any way?
					if(!$post){
						$apost = $row['post'];
					}else{
						$apost = stripslashes($post);
					}
					// Should we update the edit settings?
					if(!$reason){
						$reason = $row['post_edit_reason'];
						$edit = $row['edit_name'];
					}else{
						$edit = $user['members_display_name'];
					}
					$edited = $time;
					// Ah, so everything's gone through okay. Now for the finishing touch.
					self::$ips->parser->parse_bbcode	= $row['use_ibc'];
					self::$ips->parser->strip_quotes	= 1;
					self::$ips->parser->parse_nl2br		= 1;
					self::$ips->parser->parse_html		= $row['use_html'];
					self::$ips->parser->parse_smilies	= ($useEmo ? 1 : 0);
					$post = self::$ips->parser->pre_db_parse($apost);
					$post	= $this->ipbwi->makeSafe($post);
					self::$ips->DB->query('UPDATE ibf_topics SET title="'.$ptitle.'", description="'.$fdesc.'", state="'.$state.'", pinned="'.($pin ? 1 : 0).'", topic_open_time="'.$opened.'", topic_close_time="'.$closed.'", approved="'.($approve ? 1 : 0).'" WHERE tid="'.$topicID.'"');
					self::$ips->DB->query('UPDATE ibf_posts SET edit_time="'.$edited.'", post="'.$post.'", edit_name="'.$edit.'", post_edit_reason="'.$reason.'", use_emo="'.($useEmo ? 1 : 0).'", use_sig="'.($useSig ? 1 : 0).'" WHERE pid="'.$row['topic_firstpost'].'"');
					self::$ips->DB->query('UPDATE ibf_forums SET last_title="'.$ptitle.'", last_id="'.$topicID.'", last_poster_name="'.$user['members_display_name'].'" WHERE id="'.$row['forum_id'].'"');
					return true;
				}else{
					return false;
				}
			}else{
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('topicsNotExist'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
		}
		/**
		 * @desc			deletes a topic
		 * @param	int		$topicID Topic-ID which has to be deleted
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->topic->delete(55);
		 * </code>
		 * @since			2.0
		 */
		public function delete($topicID){
			if($info = $this->info($topicID)){
				self::$ips->DB->query('DELETE FROM ibf_topics WHERE tid = "'.intval($topicID).'"');
				self::$ips->DB->query('DELETE FROM ibf_posts WHERE topic_id = "'.intval($topicID).'"');
				self::$ips->DB->query('DELETE FROM ibf_polls WHERE tid = "'.intval($topicID).'"');
				if($this->ipbwi->cache->updateForum($info['forum_id'],array('posts' => -$info['posts'],'topics' => -1))){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		/**
		 * @desc			get's topic informations
		 * @param	int		$topicID Topic-ID
		 * @param	bool	$countView set to true to count topic views
		 * @param	bool	$replacePostVars replace attachment post vars with attachment-code
		 * @param	string	$ipbwiLink If you want to use IPBWI for attachment-downloading, you are able to define the attachment-link here. The var %id% is required and will be replaced with the attachment-ID.
		 * @return	array	Topic-Informations as array
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->topic->info(55);
		 * $ipbwi->topic->info(55,true,true,download.php?id=%id%');
		 * </code>
		 * @since			2.0
		 */
		public function info($topicID, $countView = false,$replacePostVars = false, $ipbwiLink = false){
			self::$ips->DB->query('SELECT t.*, p.*, g.g_dohtml AS usedohtml FROM ibf_topics t LEFT JOIN ibf_posts p ON (t.tid=p.topic_id) LEFT JOIN ibf_members m ON (p.author_id=m.id) LEFT JOIN ibf_groups g ON (m.mgroup=g.g_id) WHERE t.tid="'.intval($topicID).'" AND p.new_topic="1"');
			if($row = self::$ips->DB->fetch_row()){
				self::$ips->parser->parse_bbcode	= 1;
				self::$ips->parser->strip_quotes	= 1;
				self::$ips->parser->parse_nl2br		= 1;
				// make proper XHTML
				$row['post_bbcode']			= $this->ipbwi->properXHTML($this->ipbwi->bbcode->html2bbcode($row['post']));
				$row['post']				= self::$ips->parser->pre_display_parse($row['post']);
				$row['post']				= $this->ipbwi->properXHTML($row['post']);
				$row['title']				= $this->ipbwi->properXHTML($row['title']);
				$row['description']			= $this->ipbwi->properXHTML($row['description']);
				$row['post_edit_reason']	= $this->ipbwi->properXHTML($row['post_edit_reason']);
				$row['starter_name']		= $this->ipbwi->properXHTML($row['starter_name']);
				$row['last_poster_name']	= $this->ipbwi->properXHTML($row['last_poster_name']);
				$row['author_name']			= $this->ipbwi->properXHTML($row['author_name']);
				// increase view count
				if($countView === true){
					self::$ips->DB->query('UPDATE ibf_topics SET views = views+1 WHERE tid = '.$topicID);
				}
				// replace attachment post vars with attachment-code
				if($replacePostVars === true){
					$attachInfo = $this->ipbwi->attachment->getList($topicID,array('type' => 'topic', 'ipbwiLink' => $ipbwiLink));
					if(isset($attachInfo['defaultHTML'])){
						$row['post'] = preg_replace('/\[attachment=([!0-9]*):([!a-zA-Z0-9_\-.]*)\]/smeU','$attachInfo["defaultHTML"]',$row['post']);
					}else{
						$row['post'] = preg_replace('/\[attachment=([!0-9]*):([!a-zA-Z0-9_\-.]*)\]/smeU','$attachInfo["\1"]["defaultHTML"]',$row['post']);
					}
				}
				// Save Topic In Cache and Return
				$this->ipbwi->cache->save('TopicInfo', $topicID, $row);
				return $row;
			}else{
				return false;
			}
		}
		/**
		 * @desc			array with topic IDs of the given Forums
		 * @param	mixed	$forumIDs The forum IDs where the topic IDs should be retrieved from (array-list, int or '*' for topics from all forums)
		 * @param	int		$start For performance purposes, you are able to set a start parameter to set a section of the attachment list. This is used for forum-attachment-list only.
		 * @param	int		$limit For performance purposes, you are able to set a limit parameter to set a section of the attachment list. This is used for forum-attachment-list only.
		 * @param	bool	$bypassPerms set to true, to ignore permissions
		 * @return	array	Topic-Informations as array
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->topic->getListIDs(55);
		 * $ipbwi->topic->getListIDs(array(55,77,99));
		 * $ipbwi->topic->getListIDs('*',10,20,true);
		 * </code>
		 * @since			2.0
		 */
		public function getListIDs($forumIDs,$start=false,$limit=false,$bypassPerms=false){
			if($start != '' || $limit != ''){
				$startLimit = ' LIMIT '.intval($start).','.intval($limit);
			}else{
				$startLimit = false;
			}
			// forum permissions
			$expForum = array();
			if(is_array($forumIDs)){
				foreach($forumIDs as $forumID){
					if($this->ipbwi->forum->isReadable(intval($forumID)) OR $bypassPerms){
						$expForum[] = intval($forumID);
					}
				}
			}elseif($forumIDs == '*'){
				// Get readable forums
				$readable = $this->ipbwi->forum->getReadable();
				foreach($readable as $i){
					$expForum[] = intval($i['id']);
				}
			}elseif($this->ipbwi->forum->isReadable(intval($forumIDs)) OR $bypassPerms){
				$expForum[] = intval($forumIDs);
			}
			if(count($expForum) < 1){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			// get topics
			$query = self::$ips->DB->query('SELECT tid FROM ibf_topics WHERE forum_id IN ('.implode(',', $expForum).') AND topic_hasattach!="0"'.$startLimit);
			if(self::$ips->DB->get_num_rows() == 0) return false;
			while($row = self::$ips->DB->fetch_row($query)){
				$topicIDs[] = $row['tid'];
			}
			return $topicIDs;
		}
		/**
		 * @desc			lists topics from several forums and get topic-informations
		 * @param	mixed	$forumIDs The forum IDs where the topics should be retrieved from (array-list, int or '*' for topics from all forums)
		 * @param	array	$settings optional query settings. Settings allowed: order, orderby limit and start
		 * + string order = ASC or DESC, default ASC
		 * + string orderby = 'tid', 'title', 'posts', 'starter_name', 'starter_id', 'start_date', 'last_post', 'views', 'post_date'. Default: start_date
		 * + int start = Default: 0
		 * + int limit = Default: 15
		 * + bool linked = Get moved & linked topics, too. Default: false
		 * + bool ignoreapproval = Do not get topics which need to be approved. Default: false
		 * + int memberid = Default: false. Deliver Member ID to get topics of this member only
		 * + bool allsubs = Default: false. Retrieve topics from all subforums of delivered Forum-IDs
		 * @param	bool	$bypassPerms set to true to bypass permissions
		 * @return	array	Topic-Informations as multidimensional array
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->topic->getList(55);
		 * $ipbwi->topic->getList(55,array('memberid' => 66));
		 * $ipbwi->topic->getList(array(55,22,77,99));
		 * $ipbwi->topic->getList('*');
		 * $ipbwi->topic->getList(55,array('order' => 'DESC', 'orderby' => 'pid', 'start' => 10, 'limit' => 20, 'linked' => true, 'ignoreapproval' => true));
		 * </code>
		 * @since			2.0
		 */
		public function getList($forumIDs,$settings=array(),$bypassPerms=false){
			if(empty($settings['order'])){
				$settings['order'] = 'desc';
			}
			if(empty($settings['orderby'])){
				$settings['orderby'] = 'start_date';
			}
			if(empty($settings['start'])){
				$settings['start'] = 0;
			}
			if(empty($settings['limit'])){
				$settings['limit'] = 15;
			}
			if(empty($settings['linked'])){
				$settings['linked'] = false;
			}
			if(empty($settings['ignoreapproval'])){
				$settings['ignoreapproval'] = false;
			}
			if(empty($settings['memberid'])){
				$settings['memberid'] = false;
			}
			if(empty($settings['allsubs'])){
				$settings['allsubs'] = false;
			}

			// get all subforums
			if($settings['allsubs'] === true){
				$forumIDs = $this->forum->getAllSubs($forumIDs,'array_ids_only');
			}

			$expforum = array();
			if(is_array($forumIDs)){
				foreach($forumIDs as $i){
					if($this->ipbwi->forum->isReadable(intval($i)) OR $bypassPerms){
						$expforum[] = intval($i);
					}
				}
			}elseif($forumIDs == '*'){
				// Get readable forums
				$readable = $this->ipbwi->forum->getReadable();
				foreach($readable as $k){
					$expforum[] = intval($k['id']);
				}
			}elseif($this->ipbwi->forum->isReadable(intval($forumIDs)) OR $bypassPerms){
				$expforum[] = intval($forumIDs);
			}
			if(count($expforum) < 1){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}else{
				// What shall I order it by guv?
				$allowedorder = array('tid', 'title', 'posts', 'starter_name', 'starter_id', 'start_date', 'last_post', 'views', 'post_date');
				if(isset($settings['orderby']) && in_array($settings['orderby'], $allowedorder)){
					$order = $settings['orderby'].' '.(($settings['order'] == 'desc') ? 'DESC' : 'ASC');
				}elseif(isset($settings['orderby']) && $settings['orderby'] == 'random'){
					$order = 'RAND()';
				}else{
					$order = 'last_post '.((strtolower($settings['order']) == 'desc') ? 'DESC' : 'ASC');
				}
				// Grab Posts
				$limit = isset($settings['limit']) ? intval($settings['limit']) : '15';
				$start = isset($settings['start']) ? intval($settings['start']) : '0';
				// Forum ID Code
				if(($forumIDs == '*') && $bypassPerms){
					$forums = '';
				}else{
					$forums = ' AND torig.forum_id IN ('.implode(',', $expforum).')';
				}
				// Are we looking for an approval?
				if(empty($settings['ignoreapproval'])){
					$approved = ' AND torig.approved="1"';
				}
				// get data from a specific user
				if($settings['memberid']){
					$specificMember = ' AND t.starter_id = "'.intval($settings['memberid']).'"';
				}else{
					$specificMember = false;
				}
				// allow SUB SELECT query joins
				self::$ips->DB->allow_sub_select=1;
				// select topics
				$SQL = 'SELECT t.*, p.*, g.g_dohtml AS usedohtml FROM ibf_topics torig';
				// get linked, moved topics too if requested
				if(isset($settings['linked']) && $settings['linked'] === true){
					$SQL .= ' LEFT JOIN ibf_topics t ON (IFNULL( (t.tid = LEFT(torig.moved_to, INSTR(torig.moved_to, "&"))),t.tid = torig.tid ))';
				}else{
					// else get all other topics
					$SQL .= ' LEFT JOIN ibf_topics t ON (t.tid = torig.tid)';
				}
				$SQL .= '	LEFT JOIN ibf_posts p ON (t.tid=p.topic_id)
							LEFT JOIN ibf_members m ON (p.author_id=m.id)
							LEFT JOIN ibf_groups g ON (m.mgroup=g.g_id)
							WHERE p.new_topic="1"'.$forums.$approved.$specificMember.'
							ORDER BY '.$order.' LIMIT '.$start.','.$limit;
				$query = self::$ips->DB->query($SQL);
				$topics = array();
				self::$ips->parser->parse_bbcode = 1;
				self::$ips->parser->strip_quotes = 1;
				self::$ips->parser->parse_nl2br = 1;
				while($row = self::$ips->DB->fetch_row($query)){
					$row['post_bbcode']			= $this->ipbwi->bbcode->html2bbcode($row['post']);
					// make proper XHTML
					$row['post']				= self::$ips->parser->pre_display_parse($row['post']);
					$row['post']				= $this->ipbwi->properXHTML($row['post']);
					$row['title']				= $this->ipbwi->properXHTML($row['title']);
					$row['description']			= $this->ipbwi->properXHTML($row['description']);
					$row['starter_name']		= $this->ipbwi->properXHTML($row['starter_name']);
					$row['last_poster_name']	= $this->ipbwi->properXHTML($row['last_poster_name']);
					$row['author_name']			= $this->ipbwi->properXHTML($row['author_name']);
					$topics[] = $row;
				}
				return $topics;
			}
		}
		/**
		 * @desc			rates a topic
		 * @param	int		$topicID ID of the topic which has to be rated
		 * @param	int		$rating	 rating for the topic
		 * @param	bool	$bypassPerms set to true to bypass permissions
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->topic->rate(55,4);
		 * $ipbwi->topic->rate(55,5,true);
		 * </code>
		 * @since			2.0
		 */
		public function rate($topicID,$rating,$bypassPerms=false){
			if($rating >= '6'){
				return false;
			}
			// Flood control, time.
			if((self::$ips->vars['flood_control'] && !$this->has_perms('g_avoid_flood')) OR !$bypassPerms){
				if((time() - self::$ips->member['last_post']) < self::$ips->vars['flood_control']){
					$this->ipbwi->addSystemMessage('Error',sprintf($this->ipbwi->getLibLang('floodControl'), self::$ips->vars['flood_control']),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
			}
			// Get the topic information.
			self::$ips->DB->query('SELECT * FROM ibf_topics WHERE tid="'.intval($topicID).'"');
			if($row = self::$ips->DB->fetch_row()){
				if($user = $this->ipbwi->member->info()){
					$forum = $this->ipbwi->forum->info($row['forum_id']);
					$group = $this->ipbwi->group->info($user['mgroup']);
					// Are ratings allowed?
					if(!$forum['forum_allow_rating']) return false;
					// Can the member vote?
					if(!$bypassPerms && !$group['g_topic_rate_setting']) return false;
					$userid = intval($user['id']);
					self::$ips->DB->query('SELECT * FROM ibf_topic_ratings WHERE rating_tid="'.$topicID.'" AND rating_member_id="'.$userid.'"');
					if($rate = self::$ips->DB->fetch_row()){
						if(($group['g_topic_rate_setting'] == "2") OR $bypassPerms){
							self::$ips->DB->query('UPDATE ibf_topic_ratings SET rating_value="'.$rating.'" WHERE rating_tid="'.$topicID.'" AND rating_member_id="'.$userid.'"');
							self::$ips->DB->query('UPDATE ibf_topics SET topic_rating_total=topic_rating_total-"'.$rate['rating_value'].'"+"'.$rating.'" WHERE tid="'.$topicID.'"');
							return true;
						}else{
							return false;
						}
					}else{
						self::$ips->DB->query('INSERT INTO ibf_topic_ratings (rating_tid, rating_member_id, rating_value, rating_ip_address) VALUES("'.$topicID.'", "'.$user['id'].'", "'.$rating.'", "'.$_SERVER['REMOTE_ADDR'].'")');
						self::$ips->DB->query('UPDATE ibf_topics SET topic_rating_total=topic_rating_total+"'.$rating.'", topic_rating_hits=topic_rating_hits+1 WHERE tid="'.$topicID.'"');
						return true;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		/**
		 * @desc			merges several topics
		 * @param	int		$topic1id ID('s) of the topic(s) to be merged
		 * @param	int		$topic2id ID of topic that $topic1id will be merged into
		 * @param	string	$desc Topic-Description
		 * @param	bool	$bypassPerms set to true to bypass permissions
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->topic->rate(55,77,'merged topic title', 'merged topic description');
		 * $ipbwi->topic->rate(55,77,'merged topic title', 'merged topic description', true);
		 * </code>
		 * @since			2.0
		 */
		public function merge($topic1id, $topic2id, $title, $desc, $bypassPerms=false){
			// Is the user logged in...?
			if($this->member->isLoggedIn()){
				$postName = self::$ips->member['name'];
			}else{
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			// Flood control time.
			if(self::$ips->vars['flood_control'] AND !$this->ipbwi->permissions->has('g_avoid_flood')){
				if((time() - self::$ips->member['last_post']) < self::$ips->vars['flood_control']){
					$this->ipbwi->addSystemMessage('Error',sprintf($this->ipbwi->getLibLang('floodControl'), self::$ips->vars['flood_control']),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
			}
			// Are the id(s) for the topic(s)-to-be-merged set?
			if(!isset($topic1id)){
				return false;
			}
			// We can't have the second topic id in an array, so...
			if(is_array($topic2id)){
				return false;
			}
			// Are the id's numeric?
			if(!is_numeric($topic1id) OR !is_numeric($topic2id)){
				// If $topic1id is an array...
				if(is_array($topic1id)){
					foreach($topic1id as $k => $v){
						if(!is_numeric($v)){
							unset($topic1id[$k]);
						}
					}
				}else{
					return false;
				}
			}
			// Are the id's identical?
			if($topic1id == $topic2id){
				return false;
			}else{
				// If $topic1id is an array...
				if(is_array($topic1id)){
					foreach($topic1id as $k => $v){
						if($v == $topic2id){
							unset($topic1id[$k]);
						}
					}
				}
			}
			// Let's get some SQL information.
			self::$ips->DB->query('SELECT  f.*,t.*,p.* FROM ibf_topics t LEFT JOIN ibf_forums f ON (t.forum_id=f.id) LEFT JOIN ibf_posts p ON(t.topic_firstpost=p.pid) WHERE t.tid="'.intval($topic2id).'"');
			if($row = self::$ips->DB->fetch_row()){
				if($user = $this->ipbwi->member->info()){
					$forum = $this->ipbwi->forum->info($row['forum_id']);
					$group = $this->ipbwi->group->info($user['mgroup']);
					if(is_array($topic1id)){
						foreach($topic1id as $v){
							$topic[] = $this->info($v);
						}
					}else{
						$topic[] = $this->info($topic1id);
					}
					$time = time();
					if(!$bypassPerms && is_array($topic)){
						foreach($topic as $k => $v){
							if($user['id'] == $v['starter_id'] && !$group['g_edit_topic']){
								return false;
							}elseif(!$this->ipbwi->member->isSuperMod()){
								$mod = $this->ipbwi->forum->getModerators($forum['id'], $user['id']);
								foreach($mod as $h){
									if($h['forum_id'] == $user['id'] && !$h['split_merge']){
										return false;
									}
								}
							}
						}
					}
					// Is a new title specified...?
					if($title == false){
						$ltitle = $row['title'];
						$lname = $row['last_poster_name'];
						$laid = $row['last_poster_id'];
						$ptitle = $row['title'];
					}elseif($title == $row['last_title']){
						$ltitle = $row['last_title'];
						$lname = $row['last_poster_name'];
						$laid = $row['last_poster_id'];
						$ptitle = $title;
					}elseif($title == $forum['last_title']){
						$ltitle = $forum['last_title'];
						$lname = $forum['last_poster_name'];
						$laid = $forum['last_poster_id'];
						$ptitle = $title;
					}else{
						$ltitle = $forum['last_title'];
						$lname = $forum['last_poster_name'];
						$laid = $forum['last_poster_id'];
						$ptitle = $title;
					}
					// Is a new description specified...?
					if($desc == false){
						$fdesc = $row['description'];
					}else{
						$fdesc = stripslashes($desc);
					}
					// Alright, everything has passed. Time to update the database.
					self::$ips->parser->parse_bbcode = $row['use_ibc'];
					self::$ips->parser->strip_quotes = 1;
					self::$ips->parser->parse_nl2br = 1;
					self::$ips->parser->parse_html = $row['use_html'];
					self::$ips->DB->query('UPDATE ibf_topics SET title="'.$ptitle.'", description="'.$fdesc.'" WHERE tid="'.$topic2id.'"');
					self::$ips->DB->query('UPDATE ibf_forums SET last_id="'.$topic2id.'", last_title="'.$ltitle.'", last_poster_name="'.$lname.'", last_poster_id="'.$laid.'" WHERE id="'.$topic['forum_id'].'"');
					// If $topic is an array, multiple topics will be merged into one.
					if(is_array($topic)){
						foreach($topic as $v){
							self::$ips->DB->query('UPDATE ibf_posts SET topic_id="'.$row['tid'].'", new_topic="0" WHERE pid="'.$v['topic_firstpost'].'"');
							self::$ips->DB->query('UPDATE ibf_forums SET posts=posts+1, topics=topics-1 WHERE id="'.$v['forum_id'].'"');
							// With the database updated, we can get rid of the old stuff.
							self::$ips->DB->query('DELETE FROM ibf_topics WHERE tid="'.$v['tid'].'"');

							// Now to update that post count...
							self::$ips->DB->query('UPDATE ibf_topics SET posts=posts+1 WHERE tid="'.$topic2id.'"');
						}
					}
					return true;
				}else{
					$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
			}else{
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('topicsNotExist'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
		}
		/**
		 * @desc			Converts topic title to topic-IDs
		 * @param	string	$title Topic Title
		 * @return	mixed	int Topic ID, array Topic IDs or false
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->topic->title2id('topic title');
		 * </code>
		 * @since			2.0
		 */
		public function title2id($title){
			self::$ips->DB->query('SELECT tid FROM ibf_topics WHERE title="' . addslashes(htmlentities($title)) . '"');
			$topics = self::$ips->DB->fetch_row();
			if(is_array($topics) && count($topics) === 1){
				return $topics['tid']; // return matching topic-id
			}elseif(is_array($topics) && count($topics) > 0){
				return $topics; // return array of matched topic-ids
			}else{
				return false;
			}
		}
		/**
		 * @desc			Converts topic ID to topic-title
		 * @param	int		$id Topic ID
		 * @return	string	Topic Title
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->topic->title2id('topic title');
		 * </code>
		 * @since			2.0
		 */
		public function id2title($id){
			self::$ips->DB->query('SELECT title FROM ibf_topics WHERE tid="'.intval($id).'"');
			$topics = self::$ips->DB->fetch_row();
			if(is_array($topics) && count($topics) === 1){
				return $this->ipbwi->properXHTML($topics['title']); // return matching topic-title
			}else{
				return false;
			}
		}
		/**
		 * @desc			Moves a topic to a specified destination forum
		 * @param	int		$topicID Topic ID
		 * @param	int		$destforumid Destination Forum ID
		 * @param	int		$bypassPerms set to true to bypass permissions
		 * @return	bool	true on success, false on failure
		 * @author			Matthias Reuter
		 * @author			Andrew Beveridge <andrewthecoder@googlemail.com>
		 * @sample
		 * <code>
		 * $ipbwi->topic->move(55,33);
		 * $ipbwi->topic->move(55,33,true);
		 * </code>
		 * @since			2.0
		 */
		public function move($topicID, $destForumID, $bypassPerms = false){
			// Check params and set obvious variables
			if($topicInfo = $this->info($topicID)){
				if($this->ipbwi->forum->info($topicInfo['forum_id'])){
					if($this->ipbwi->forum->info($destForumID)){
						if($memberInfo = $this->ipbwi->member->info()){
							// You are logged in, right?
							if(!$this->ipbwi->member->isLoggedIn()){
								// Drat... Sorry, but we can't have guests running around moving topics.
								$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('membersOnly'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
								return false;
							}
							// Flooding
							if(self::$ips->vars['flood_control'] AND !$this->has_perms('g_avoid_flood')){
								if((time() - self::$ips->member['last_post']) < self::$ips->vars['flood_control']){
									$this->ipbwi->addSystemMessage('Error',sprintf($this->ipbwi->getLibLang('floodControl'), self::$ips->vars['flood_control']),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
									return false;
								}
							}
							// Check permissions
							if(!$bypassPerms && !$topicInfo['author_id'] == $memberInfo['id'] && !$group['g_is_supmod']){
								// Is the logged in member the topic author? (or are they a supermod?)
								$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
								return false;
							}
							// Right, we're finally allowed to move the topic
							if(self::$ips->DB->query('UPDATE ibf_topics SET forum_id = "'.$destForumID.'" WHERE tid ="'.$topicID.'" LIMIT 1')){
								//Now the topic has been moved, clean up the cache for original forum AND destination forum
								$this->ipbwi->cache->updateForum($topicInfo['forum_id'],array('posts' => -$topicInfo['posts'],'topics' => -1));
								$this->ipbwi->cache->updateForum($destForumID,array('posts' => $topicInfo['posts'],'topics' => 1));
								return true;
							}else{
								return false;
							}
						}else{
							$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('membersOnly'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
							return false;
						}
					}else{
						$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('forumNotExist'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
						return false;
					}
				}else{
					$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('topicsNotExist'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
			}else{
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('topicsNotExist'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
		}
	}
?>