<?php
class skin_games {
	var $zone;



function game_info($gametitle="",$gameimage="",$publisher="",$release="",$website="",$req="",$gamedesc="") {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
<!-- Game {$gametitle} -->
<div class="topic_image">
EOF;
if($gameimage!= "") {
		$HTML .= <<<EOF

<img src="{$gameimage}" alt="{$gametitle}" />
EOF;
}
	$HTML .= <<<EOF
</div>
<div class="contentbox">
<div class="content_title">{$gametitle}</div>
<div class="content_info">
Published by {$publisher}<br />
Released on {$release}<br />
<a href="{$website}">Offical website</a><br />
</div>

<div style="margin: 5px;">
{$gamedesc}<br /><br />
<a name="req"></a>
<b>System Requirements</b><br />
{$req}
</div>
</div>
EOF;

//--endhtml--//
return $HTML;
}

function game_factions($factiontitle="",$factionimage="",$factiondesc="") {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
<a name="factions"></a>
<!-- Faction {$factiontitle} -->
<div class="topic_image">
EOF;
if($factionimage!= "") {
		$HTML .= <<<EOF

<img src="{$factionimage}" alt="{$factiontitle}" />
EOF;
}
	$HTML .= <<<EOF
</div>
<div class="contentbox">
<div class="content_title">{$factiontitle}</div>
<div class="content_info"><img src="{$factionimage}" alt="{$factiontitle}"><br /><br />
</div>
<div style="margin: 5px;">
{$factiondesc}
</div>
</div>
EOF;

//--endhtml--//
return $HTML;
}
function stuff($name="",$image="",$desc="",$factionname="",$factionimage="") {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
<table width="100%">
<tr>
<td rowspan="2" valign="top">
<img src="{$image}" alt="{$name}" />
</td>
<td align="center" class="centerbox">{$name}</td>
<td align="center" class="centerbox">
<img src="{$factionimage}" alt="{$factionname}" width="30px"></td>
</tr>
<tr>
<td valign="top" class="rowside" colspan="2">{$desc}</td>
</tr>
</table>
EOF;

//--endhtml--//
return $HTML;
}

}

?>