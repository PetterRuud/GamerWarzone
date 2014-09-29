<?php check_login();?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title><?=$site_name;?></title>
	<link rel="shortcut icon" href="/images/favicon.ico" />
	<link rel="shortcut icon" href="/images/favicon.png" />
	<!-- css -->
	<link rel="stylesheet" href="<?=$ipbwi->getBoardVar('url');?>/style_images/css_50.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="/style/site.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="/style/jquery.fancybox.css" type="text/css" media="screen" />
	
	<!-- js -->
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
	<script type="text/javascript" src="/jscript/jquery.validate.pack.js"></script>
	<script type="text/javascript" src="/jscript/jquery.anythingslider.js" charset="utf-8"></script>
	<script type="text/javascript" src="/jscript/tiny_mce/tiny_mce.js"></script>
	<script type="text/javascript" src="/jscript/jquery.easing.js"></script>
	<script type="text/javascript">
	
	$(function () {
        
            $('.anythingSlider').anythingSlider({
                easing: "easeInOutExpo",
                autoPlay: true,
                delay: 5000,
                startStopped: false,
                animationTime: 600,
                hashTags: true,
                buildNavigation: true,
        		pauseOnHover: true
            });
            
        });
</script>

<script type="text/javascript">
$(document).ready(function() {
	$("#form").validate();
});
</script>
<script type="text/javascript">
$(document).ready(function() {
$(".swap").focus(function() {
if( this.value == this.defaultValue ) {
this.value = "";
}
}).blur(function() {
if( !this.value.length || this.value == "" ) {
this.value = this.defaultValue;
}
});
});
</script>
</head>

<body>
<div id="outer">

	<!-- OP:HEADER -->
	<div id="header">
		<div id="logo">
			<h1>Gamerwarzone</h1>
		</div>
		<div id="login">
		<?php if($ipbwi->member->isLoggedIn()){ ?>
	<!-- if logged in -->
			<div class="userinfo">
				Logged in as <strong><?=$member['members_display_name'];?></strong></a> <span class="smalltext">(<a href="?action=logout">Log out</a>)</span> | 
				<a href="<?=$ipbwi->getBoardVar('url');?>index.php?act=Msg&amp;CODE=01&amp;VID=in"><img src="images/pmbox.gif" alt=""/><strong><?=$ipbwi->pm->numNewPMs();?></strong> New message</a>
			</div>
			<ul>
			<?php if($ipbwi->member->isAdmin()){?>
				<li><a href="/admin/" target="_blank">Site ACP</a></li>
				<li><a href="<?=$ipbwi->getBoardVar('url');?>/admin/" target="_blank">Board ACP</a></li>
				<li><a href="<?=$ipbwi->getBoardVar('url');?>" title="#">My Controls</a></li>
			<?php } ?>
				<li><a href="<?=$ipbwi->getBoardVar('url');?>index.php?act=Search&amp;CODE=getnew">New Posts</a></li>
			
			</ul>

		<?php }else{ ?>

		<!-- if not logged in -->
			<form action="<?=$ipbwi->getBoardVar('home_url');?>index.php?action=login" method="post" id="loginform">
				<input class="text-inp swap" type="text" size="20" name="username" value="Username" /><br />
				<input class="text-inp swap" type="password" size="20" name="password" value="password"/>
				<input type="image" src="./images/button_input_bg.gif" class="button" name="login" value="" />
				<input type="hidden" name="setcookie" value="1" />
			</form>
		<div class="userinfo" style="float:right;margin-top:18px;"><a href="<?=$ipbwi->getBoardVar('url');?>index.php?act=Reg&CODE=00">Join the community</a></div>
	<?php } ?>

		</div>
	</div>
	<!-- / OP:HEADER -->
	<!-- OP:INNER WRAPPER -->
<div id="wrapper">
	
	<!-- OP:NAVIGATION -->
	<div id="nav">
		<ul>
			<li><a href="<?=$ipbwi->getBoardVar('home_url');?>">Home</a></li>
			<li><a href="<?=$ipbwi->getBoardVar('url');?>">Forums</a></li>
			<li><a href="<?=$site_url;?>/index.php?act=ipsearch">Player search</a></li>
			<li><a href="<?=$ipbwi->getBoardVar('url');?>index.php?autocom=downloads">Downloads</a></li>
			<li><a href="<?=$ipbwi->getBoardVar('url');?>index.php?act=members">Members</a></li>
			<li><a href="<?=$ipbwi->getBoardVar('url');?>index.php?autocom=downloads">Downloads</a></li>
			<li><a href="<?=$site_url;?>/index.php?act=contact">Contact</a></li>
		</ul>
	</div>
	<!-- / OP:NAVIGATION -->