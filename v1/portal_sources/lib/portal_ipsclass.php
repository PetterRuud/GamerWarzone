<?php

if(!class_exists("ipsclass")) {
	die("Bad call to zone_ipsclass");
}

class zone_ipsclass extends ipsclass {

	// Override error function
	function Error($error)
	{
		// Do nothing for us!
	}

}
