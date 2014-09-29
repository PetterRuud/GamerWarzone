<?php
class skin_affiliates {
	var $zone;

//===========================================================================
// AFFILIATES
//===========================================================================
function add_affiliates() {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
<div class="contentbox">
<div class="box">
<div class="content_center">
<b>88x31px buttons</b><br />
			<img src="{$zone->ips->vars['home_url']}/images/buttons/button.gif" alt="Gamer Warzone" />
			<img src="{$zone->ips->vars['home_url']}/images/buttons/button2.gif" alt="Gamer Warzone" />
<br /><b>468x60px banners</b><br />
			<img src="{$zone->ips->vars['home_url']}/images/buttons/banner.gif" alt="Gamer Warzone" /><br /><br />
			<img src="{$zone->ips->vars['home_url']}/images/buttons/banner2.gif" alt="Gamer Warzone" />
		</div>
</div>
</div>
<div class="contentbox">
<div class="content_header">Affiliation Rules<br />
<span class="desc">
The site must be Gaming or Graphic oriented<br />
			The site most not contain or allow Pr0n or Warez<br />
			No free domains<br />
			You must put our button on your webpage<br />
			Your button will not show up on our site before it has been validated
</span>
</div>

		    <form action='{$zone->ips->vars['home_url']}/?section=affiliates&amp;page=doadd' method='post'>
<div class="box">
URL(include http://)</b>:&nbsp;&nbsp;<input type='text' name='url' size='40'><br /><br />
Image(button URL):&nbsp;&nbsp;<input type='text' name='button' size='40'>
<div class="content_center"><input type='submit' name='submit' value='Submit'></div>
		</form>
		</div>
		</div>
EOF;

//--endhtml--//
return $HTML;
}

function affiliates_top() {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
<table cellpadding='3' cellspacing='0' border='0' width='100%'>
EOF;

//--endhtml--//
return $HTML;
}

function affiliates($id="",$button="",$click="",$td_width="") {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
<td width='{$td_width}%' align='left' style='background-color:#F1F1F1;padding:6px;'>
<a href='{$zone->ips->vars['home_url']}/?section=affiliates&page=out&id={$id}'><img src='{$button}' border='0' height="31px" width="88px" alt="" /></a><br />
<span class="desc"><strong>Hits out ({$click})</strong></span>
</td>

EOF;

//--endhtml--//
return $HTML;
}

function affiliates_bottom() {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
</table><br />
EOF;

//--endhtml--//
return $HTML;
}

function random_affiliates($id="",$button="",$click="") {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
<td align="center"><a href='{$zone->ips->vars['home_url']}/?section=affiliates&page=out&id={$id}'>
<img src='{$button}' border='0' height="31px" width="88px"></a></td>
EOF;

//--endhtml--//
return $HTML;
}

}

?>