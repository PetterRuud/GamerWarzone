<?php
class skin_news {
	var $zone;
	
	//===========================================================================
// NEWS
//===========================================================================
function news($id="",$topic_image="",$title="",$author="",$flag="",$posted="",$post="",$last_reply="",$comments="") {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
<!-- Post #{$id} -->
<div class="topic_image">
EOF;
if($topic_image!= "") {
		$HTML .= <<<EOF
<img src="{$zone->ips->vars['board_url']}/uploads/{$topic_image}" height="100px" width="150px" alt="" />
EOF;
}
else {
		$HTML .= <<<EOF
<img src="{$zone->ips->vars['home_url']}/images/noimage.gif" alt="No Image" />
EOF;
}
	$HTML .= <<<EOF
</div>
<div class="content_title">
<a href="{$zone->ips->vars['home_url']}/index.php?section=news&amp;page=id&amp;id={$id}" title="{$title}">{$title}</a>
</div>
<div class="content_info">
{$author} {$flag} {$posted} Comments (<strong>{$comments}</strong>)
<em>Last Reply: {$last_reply}</em>
</div>
<div class="content_post">
{$post}<br />
<a href="{$zone->ips->vars['home_url']}/index.php?section=news&amp;page=id&amp;id={$id}" title="{$title}"><strong>Read More...</strong></a>
</div>
<div class="clear">&nbsp;</div>
<div class="dotted"></div>
<div class="clear">&nbsp;</div>


EOF;

//--endhtml--//
return $HTML;
}
function show_news_full($id="",$title="",$author="",$posted="",$comments="",$post="") {
	global $zone;
	
	$HTML = "";
	//--starthtml--//
	
	$HTML .= <<<EOF
<!-- Post #{$id} -->
<div class="contentbox">
<div class="content_title">
<a href="{$zone->ips->vars['board_url']}/index.php?showtopic={$id}" title="{$title}">{$title}</a>
</div>
<div class="content_info">
{$author} on {$posted} - Comments (<strong>{$comments}</strong>)
</div>
<div class="content_post">
{$post}<br />
<a href="{$zone->ips->vars['board_url']}/index.php?showtopic={$id}" title="{$title}"><strong>Leave a comment...</strong></a>
</div>
</div>

EOF;

//--endhtml--//
return $HTML;
}

// END OF SKIN GLOBAL

}
?>