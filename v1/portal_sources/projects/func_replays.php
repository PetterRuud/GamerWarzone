<?php 
function upload_replay() {
	global $SDK;
require("forum/ips_kernel/class_upload.php");	
$upload = new class_upload();
$upload->out_file_dir     = './replays';
$upload->max_file_size    = '10000000';
$upload->make_script_safe = 1;
$upload->allowed_file_ext = array( 'cnc3replay' );
$upload->upload_process();
$replay_file = $_POST['FILE_UPLOAD'];
// Apollo vs Khufu.cnc3replay
require("./portal_sources/class/class_replays.php");	
$rep = new cnc3_replay();
$rep->parse($replay_file);
	//if(!file($replay_file))
	// die('no file');
	//if (!$rep->parse($replay_file))
     //die('Unable to parse file, are you sure it is a valid cnc3 replay ?');
$r =& $rep->r_infos;
$map = $r['map']['fname'];
$players = count($r['players']);
$map = $_cnc3data['maps'][$map];

echo <<<EOF
<form action="{$PHP_SELF}" method="POST" enctype="multipart/form-data">
	<input type="file" name="FILE_UPLOAD">
	<input type="submit" name="submit">
EOF;
}
function show_replays() {
echo <<<EOF
	<html>
<head>
<link type="text/css" rel="stylesheet" href="css.css">
	</head>
	<body>
<h2>Replay Details</h2>
<div id="replay_wrapper">
	<table align="left" width="20%">
			<tr>
	<td class="row2"><img src="img/map/{$map}.png" width="120" height="120" title="{$map}" alt="{$map}" /></td>
				{$map}
				</tr>
			</table>
			<table class="teams" width="80%" align="right">
				<tr>
					<td class="row1">Faction</td>
					<td class="row1">Player</td>
					<td class="row1">Clan</td>
					</tr>
EOF;
for( $i = 0 ; $i < $players ; $i++) {
	$name = $r['players'][$i]['name'];
	$color = $r['players'][$i]['color'];
	$army_idx = $r['players'][$i]['army'];
	$span =$_cnc3data['colors'][$color];
	$faction = $_cnc3data['armies'][$army_idx];
	$clan = $r['players'][$i]['clan'];
echo <<<EOF
<tr>
<td class="row1"><img src="img/{$faction}.png" title="{$faction}" alt="{$faction}" /></td>
<td class="row1">{$name}<td>
<td class="row1">{$clan}<td>
</tr>
EOF;
}	
echo <<<EOF
</table>
</div>
</body>
</html>
EOF;
}
?>