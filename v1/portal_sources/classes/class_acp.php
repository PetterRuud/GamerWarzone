<?php

class class_acp {
	
	var $html;
	var $errors 		= "";
	var $nav    		= array();
	var $time_offset 	= 0;
	var $jump_menu 		= "";
	var $no_jump 		= 0;
	var $page_title		= "";
	var $page_detail	= "";
	
//===============================================================
//						GENERAL
//===============================================================

function output() {
	global $zone,$timer;
	$html 		= "";
	$navigation = array();
	$message	= "";
	$help		= "";
	$member_bar	= "";
	$query_html = "";
	
		//-----------------------------------------
		// Start function proper
		//-----------------------------------------
	
		$html = str_replace( '<%CONTENT%>', $zone->acpskin->global_frame_wrapper(), $zone->acpskin->global_main_wrapper() );
	

	$navigation = array( "<a href='?section=portal'>ZONE ACP Home</a>" );
	
	if ( count($this->nav) > 0 )
	{
		foreach ( $this->nav as $links )
		{
			if ($links[0] != "")
			{
				$navigation[] = "<a href='{$zone->ips->base_url}?{$links[0]}'>{$links[1]}</a>";
			}
			else
			{
				$navigation[] = $links[1];
			}
		}
	}
	
	//--------------------------------------
	// Navigation?
	//--------------------------------------
	
	if ( count($navigation) > 0 )
	{
		$html = str_replace( "<%NAV%>", $zone->acpskin->global_wrap_nav( implode( " &gt; ", $navigation ) ), $html );
	}

	//-----------------------------------------
	// Member bar..
	//-----------------------------------------
	
	$member_bar = $zone->acpskin->global_memberbar();
	
	$html       = str_replace( "<%MEMBERBAR%>", $member_bar, $html );
	
	//-----------------------------------------
	// Debug?
	//-----------------------------------------

	
	//-----------------------------------------
	// Show queries
	//-----------------------------------------
	
	if ( IN_DEV and count( $zone->DB->obj['cached_queries']) )
	{
		$queries = "";
		
		foreach( $zone->DB->obj['cached_queries'] as $q )
		{
			if ( strlen($q) > 300 )
			{
				$q = substr( $q, 0, 300 ).'...';
			}
			
			$queries .= htmlspecialchars($q).'<hr />';
		}
		
		$query_html .= $zone->acpskin->global_query_output($queries);
	}

	//-----------------------------------------
	// Other tags...
	//-----------------------------------------
	
	$html = str_replace( "<%MENU%>"          , $this->build_menu($zone->ips->input['section'])  , $html );
	$html = str_replace( "<%TABS%>"          , $this->build_tabs($zone->ips->input['section'])  , $html );
	$html = str_replace( "<%SECTIONCONTENT%>", $this->html, $html );
	$html = str_replace( "<%MSG%>"           , $message             , $html );
	$html = str_replace( "<%HELP%>"          , $help                , $html );
	$html = str_replace( "<%QUERIES%>"       , $query_html          , $html );
	$html = str_replace( "<#IMG_DIR#>", $zone->ips->skin['_imagedir'], $html );
	$html = str_replace( "<#EMO_DIR#>", $zone->ips->skin['_emodir']  , $html );
	
	print $html;
	exit();
	
}

/*-------------------------------------------------------------------------*/
// BUILD MENU
/*-------------------------------------------------------------------------*/

function build_menu($section) {
	global $zone;

	//--------------------------------
	// PAGES That is not ready yet
	//--------------------------------
	
	if ( $section == 'replays' )
	{
		return;
	}

	
	//--------------------------------
	// Import $PAGES and $CATS
	//--------------------------------
	 
	require_once( PORTAL_PATH."acp_loaders/acp_".$section.".php" );
	
	$this->pages = isset($PAGES) 	? $PAGES : array();
	$this->cats  = isset($CATS) 	? $CATS	 : array();
	$this->desc  = isset($DESC)		? $DESC	 : array();
	
	$html = $this->build_tree();
	
	return $html;
}

function build_tree()
{
	global $zone;
	//----------------------------------
	// INIT
	//----------------------------------
	
	$html  = "";

	//----------------------------------
	// Known menu stuff
	//----------------------------------
	
	#					  Section Module CODE =>  Real Perm Key
	$menu_limits = array( 'content:mem:add'   => 'content:mem:add',
						  'content:mem:title' => 'content:mem:title-view',
				 		);
	
	foreach($this->cats as $cid => $data)
	{
		$links = "";
		
		$name  = isset($data[0]) ? $data[0] : NULL;
		$color = isset($data[1]) ? $data[1] : NULL;
		$extra = isset($data[2]) ? $data[2] : NULL;
		
		$this->menu_ids[] = $cid;
	
		$zone->ips->admin->jump_menu .= "<optgroup label='$name'>\n";
		
		foreach($this->pages[ $cid ] as $pid => $pdata)
		{
			if ( isset($pdata[2]) AND $pdata[2] != "" )
			{
				if ( ! @is_dir( ROOT_PATH.$pdata[2] ) )
				{
					continue;
				}
			}
			
			if ( isset($pdata[4]) AND $pdata[4] )
			{
				$icon      = "<img src='../forum/skin_acp/IPB2_Standard/images/menu_shortcut.gif' border='0' alt='' valign='absmiddle'>";
				$extra_css = ';font-style:italic';
			}
			else
			{
				$icon      = "<img src='../forum/skin_acp/IPB2_Standard/images/item_bullet.gif' border='0' alt='' valign='absmiddle'>";
				$extra_css = "";
			}
			
			if ( isset($pdata[3]) AND $pdata[3] == 1 )
			{
				$theurl = $zone->ips->vars['board_url'].'/index.'.$zone->ips->vars['php_ext'].'?';
			}
			else
			{
				$theurl = $zone->ips->base_url.'?';
			}
			
			if( isset($pdata[5]) AND $pdata[5] == 1 )
			{
				$theurl = "";
				$extra_css = "' target='_blank";
			}
			
			//----------------------------------
			// Got actual restrictions?
			//----------------------------------
			
			$no_access = 0;
			
			if ( $zone->ips->member['row_perm_cache'] )
			{
				//-------------------------------
				// Yup.. so extract link info
				//-------------------------------
				
				$_tmp       = str_replace( '&amp;', '&', $pdata[1] );
				$perm_child = "";
				$perm_main  = "";
				$perm_bit   = "";
				
				foreach( explode( '&', $_tmp ) as $_urlbit )
				{
					list( $k, $v ) = explode( '=', $_urlbit );
					
					if ( $k == 'page' )
					{
						$perm_child = $v;
					}
					else if ( $k == 'section' )
					{
						$perm_main = $v;
					}
					else if ( $k == 'code' )
					{
						$perm_bit = $v;
					}
					
					if ( $perm_child AND $perm_main AND $perm_bit )
					{
						break;
					}
				}
				
				if ( $perm_child AND $perm_main AND $perm_bit AND $menu_limits[ $perm_main.':'.$perm_child.':'.$perm_bit ] )
				{
					if ( ! $this->ipsclass->member['_perm_cache'][ $menu_limits[ $perm_main.':'.$perm_child.':'.$perm_bit ] ] )
					{
						$no_access = 1;
					}
				}
				else if ( $perm_child AND $perm_main )
				{
					if ( ! $this->ipsclass->member['_perm_cache'][ $perm_main .':'. $perm_child ] )
					{
						$no_access = 1;
					}
				}
			}
			
			if ( $no_access )
			{
				$extra_css .= ";color:#777";
			}
			 
			$links .= $zone->acpskin->global_menu_cat_link( $cid, $pid, $icon, $theurl, $pdata[1], $extra_css, $pdata[0] );
		}
		 
		$html .= $zone->acpskin->global_menu_cat_wrap( $name, $links, $cid, isset($this->desc[$cid]) ? $this->desc[$cid] : '' );
		
		unset($links);
		
		$zone->ips->admin->jump_menu .= "</optgroup>\n";
	}
	
	return $html;
}
/*-------------------------------------------------------------------------*/
// Show in frame
/*-------------------------------------------------------------------------*/

function show_inframe($url="", $html="")
{
	global $zone;
	if ( $url )
	{
		$this->html .= "<iframe src='$url' scrolling='auto' style='border:1px solid #000' border='0' frameborder='0' width='100%' height='500'></iframe>";
	}
	else
	{
		$this->html .= "<iframe scrolling='auto' style='border:1px solid #000' border='0' frameborder='0' width='100%' height='500'>{$html}</iframe>";
	}
	
	$this->output();
}

function build_tabs($section) {
	global $zone;
	$onoff['portal']     = 'taboff-main';
	$onoff['affiliates'] = 'taboff-main';
	$onoff['articles']       = 'taboff-main';
	$onoff['replays']  = 'taboff-main';
	$onoff['servers']  = 'taboff-main';
	$onoff['games']       = 'taboff-main';
	$onoff['help']        = 'taboff-main';
	$onoff['settings']        = 'taboff-main';
	$onoff[ $section ] = 'tabon-main';
	
	return $zone->acpskin->global_tabs( $onoff );
}

function zone_info() {
	global $zone;
	$zone->zone_info();
}


} // EOC
?>