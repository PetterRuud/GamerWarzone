<?php
class skin_xwis {
	var $zone;
	
function splash() {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	$HTML .= <<<EOF
<div class="contentbox">
<div class="content_header">XWIS Ladders<br />
<span class="desc">Take a look at the xwis ladder standings</span></div>

<div class="game_image">
<img src="{$zone->ips->vars['home_url']}/images/games/ts.png" alt="" />
</div>
<div class="content_title">Tiberian Sun</div>
<div class="content_post">
<a href="{$zone->ips->vars['home_url']}/?section=xwis&amp;page=ts_ladder_player">Tiberian Sun Player Ladder</a><br />
<a href="{$zone->ips->vars['home_url']}/?section=xwis&amp;page=ts_ladder_clan">Tiberian Sun Clan Ladder</a><br />
<a href="{$zone->ips->vars['home_url']}/?section=xwis&amp;page=ts_hof">Tiberian Sun Hall Of Fame</a><br />
<a href="{$zone->ips->vars['home_url']}/?section=xwis&amp;page=ts_hos">Tiberian Sun Hall Of Shame</a><br />
<a href="{$zone->ips->vars['home_url']}/?section=xwis&amp;page=ts_online">Tiberian Sun Online</a><br />
<a href="{$zone->ips->vars['home_url']}/?section=xwis&amp;page=ts_stats">Tiberian Sun Stats</a><br />
</div><br />
<div class="clear">&nbsp;</div>
<div class="dotted"></div>
<div class="clear">&nbsp;</div>

<div class="game_image">
<img src="{$zone->ips->vars['home_url']}/images/games/ra2.png" alt="" />
</div>
<div class="content_title">Red Alert 2</div>
<div class="content_post">
<a href="{$zone->ips->vars['home_url']}/?section=xwis&amp;page=ra2_ladder_player">Red Alert 2 Player Ladder</a><br />
<a href="{$zone->ips->vars['home_url']}/?section=xwis&amp;page=ra2_ladder_clan">Red Alert 2 Clan Ladder</a><br />
<a href="{$zone->ips->vars['home_url']}/?section=xwis&amp;page=ra2_hof">Red Alert 2 Hall Of Fame</a><br />
<a href="{$zone->ips->vars['home_url']}/?section=xwis&amp;page=ra2_hos">Red Alert 2 Hall Of Shame</a><br />
<a href="{$zone->ips->vars['home_url']}/?section=xwis&amp;page=ra2_online">Red Alert 2 Online</a><br />
<a href="{$zone->ips->vars['home_url']}/?section=xwis&amp;page=ra2_stats">Red Alert 2 Stats</a><br />
</div><br />
<div class="clear">&nbsp;</div>
<div class="dotted"></div>
<div class="clear">&nbsp;</div>

<div class="game_image">
<img src="{$zone->ips->vars['home_url']}/images/games/yuri.png" alt="" />
</div>
<div class="content_title">Red Alert 2 Yuris Revenge</div>
<div class="content_post">
<a href="{$zone->ips->vars['home_url']}/?section=xwis&amp;page=ra2yr_ladder_player">Red Alert 2 Yuris Revenge Player Ladder</a><br />
<a href="{$zone->ips->vars['home_url']}/?section=xwis&amp;page=ra2yr_ladder_clan">Red Alert 2 Yuris Revenge Clan Ladder</a><br />
<a href="{$zone->ips->vars['home_url']}/?section=xwis&amp;page=ra2yr_hof">Red Alert 2 Yuris Revenge Hall Of Fame</a><br />
<a href="{$zone->ips->vars['home_url']}/?section=xwis&amp;page=ra2yr_hos">Red Alert 2 Yuris Revenge Hall Of Shame</a><br />
<a href="{$zone->ips->vars['home_url']}/?section=xwis&amp;page=ra2yr_online">Red Alert 2 Yuris Revenge Online</a><br />
<a href="{$zone->ips->vars['home_url']}/?section=xwis&amp;page=ra2yr_stats">Red Alert 2 Yuris Revenge Stats</a><br />
</div><br />
<div class="clear">&nbsp;</div>
<div class="dotted"></div>
<div class="clear">&nbsp;</div>

<!-- Tiberium Wars 
<a href="{$zone->ips->vars['home_url']}/?section=xwis&amp;page=cc3_standings">Tiberium Wars</a><br />
-->
</div>
EOF;

//--endhtml--//
return $HTML;
}
function show($title="",$result="") {
	global $zone;

	$HTML = "";
	//--starthtml--//
	$HTML .= <<<EOF
<div class="contentbox">
<div class="content_header">{$title}</div>
<div class="content_center">
{$result}
</div>
</div>
<div class="content_button_back"><a href="?section=xwis">Back...</a></div>

EOF;

//--endhtml--//
return $HTML;
}

// END OF SKIN GLOBAL

}
?>