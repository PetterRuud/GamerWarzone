<?php
/**
 * Prints a useful page with debug information on IPB zone and the things behind the scene.
 *
 * This extension is loaded from within IPBzone::zone_info() and can't be used by itself.
 * @see IPBzone::zone_info()
 */

if (!$this) return 'Homer bad -- Portal Czone good!';

// removed spaces in <style>, added a, width to .c1 and .c2, .ct, .lt
$info .= '';

$mysqlinfo = array();
$this->DB->query ('SHOW VARIABLES;');
while ($row = $this->DB->fetch_row()) {
	$mysqlinfo[$row['Variable_name']] = $row['Value'];
}
// anonymous functions to do the HTML ... this is 'hardcore PHP' ;-)
$tbl_op = create_function('
$sect,$c1="Name",$c2="Value"', 'static $cnt;$cnt++;return "
<div class=\"tableborder\">
 <div class=\"tableheaderalt\" id=\"zoneinfo_{$cnt}\">$sect</div>
	<table width=\"100%\" cellspacing=\"0\" cellpadding=\"5\" align=\"center\" border=\"0\">
		<tr>
<td class=\"tablesubheader\">$c1</td>
<td class=\"tablesubheader\">$c2</td>
</tr>";
');
$tbl_tr = create_function('$lbl,$val', 'return "
<tr>
<td class=\"tablerow1\">$lbl</td>
<td class=\"tablerow2\">$val</td>
</tr>";');
$tbl_cl = create_function('$t=TRUE', 'return (!$t)?"
</table>
</div>
<br />":"
<tr>
<td colspan=\"2\" align=\"center\" class=\"tablesubheader\"><a href=\"#top\">[ top ]</a></td>
</tr>
</table></div><br />";');
// the headlines of all sections. reduce repetitions by using this array
$sections = array('CDSK Configuration', 'Cookies', '$ipsclass->input', 'Current Member Details', 'Invision Power Board Configuration', 'mySQL System Variables', 'Debug Information');
// General IPB zone Information
$info .= "<div class='tableborder'>
<div class='tableheaderalt'>Quick Links</div>
	       	<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'>
	<tr>
<td class=\"tablerow1\">";
// loop thru $sections to build the TOC
for($x = 0, $y = count($sections); $x < $y; $x++) {
	$info .= '&nbsp;&middot;&nbsp;<a href="#zoneinfo_' . ($x + 1) . '">' . $sections[$x] . '</a><br />';
}
// updated (c) years ;)
$info .= '</td></tr></div><br />';
$info .= $tbl_cl(false);
// IPB zone Config
$info .= $tbl_op(array_shift($sections));
// I think this better fits here
$info .= $tbl_tr('zone Version', $this->zone_version) . $tbl_tr('IPB Version', $this->ips->version) . $tbl_tr('PHP Version', phpversion()) . $tbl_tr('Zend Engine Version', zend_version()) . $tbl_tr('mySQL Version', $mysqlinfo['version']) . $tbl_tr('Database Queries Used', $this->DB->query_count);

foreach ($this->ipbzone_settings as $x => $y) {
	$info .= $tbl_tr($x, $y);
}
$info .= $tbl_cl();
// $_COOKIE
$info .= $tbl_op(array_shift($sections));
foreach ($_COOKIE as $x => $y) {
	if (strstr($x, 'sql_') !== false) continue;
	// deserialize arrays for better reading
	if (version_compare(phpversion(), '4.3.0', '>=') && ($i = @unserialize($y))) eval('$y=print_r($i, 1);');
	$info .= $tbl_tr($x, $y);
}
$info .= $tbl_cl();
// $ibforums->input
$info .= $tbl_op(array_shift($sections));
foreach ($this->ips->input as $x => $y) {
	$info .= $tbl_tr($x, $y);
}
$info .= $tbl_cl();
// $GLOBALS['ibforums']->member
$info .= $tbl_op(array_shift($sections));
foreach ($this->ips->member as $x => $y) {
	$info .= $tbl_tr($x, $y);
}
$info .= $tbl_cl();
// IPB Config
$info .= $tbl_op(array_shift($sections));
foreach ($this->ips->vars as $x => $y) {
	if (strstr($x, 'sql_') === false) $info .= $tbl_tr($x, $y);
}
$info .= $tbl_cl();
// mySQL Details
$info .= $tbl_op(array_shift($sections));
foreach ($mysqlinfo as $x => $y) {
	if (strstr($x, 'sql_') === false) $info .= $tbl_tr($x, $y);
}
$info .= $tbl_cl();
// Other Debug Information
$dbqueries = '';
$errors = '';
foreach ($this->DB->obj['cached_queries'] as $x) {
	$dbqueries .= $x . '<br />';
}
foreach ($this->_errors as $x => $y) {
	$errors .= '<strong>' . $x . '</strong> ' . $y . '<br />';
}
$info .= $tbl_op(array_shift($sections));
$info .= $tbl_tr('zone Errors', count($this->_errors)) . $tbl_tr('Last zone Error', $this->_lasterror) . $tbl_tr('Errors Generated', $errors) . $tbl_tr('Database Queries Count', $this->DB->query_count) . $tbl_tr('SQL Queries Run', $dbqueries);
$info .= $tbl_cl();

$ACP->html .= $info;

return 'Homer bad -- Czone good!';

?>