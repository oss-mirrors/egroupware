<?php
	// Security precaution: prevent script tags: <script>, <javascript "">, etc.
	foreach ($_GET as $secvalue)
	{
		if (eregi("<[^>]*script*\"?[^>]*>", $secvalue)) 
		{
			die("A security breach has been attempted and refused.");
		}
	}

	// Security precaution: don't let anyone call xxx.inc.php files or
    // construct URLs with relative paths (ie, /dir1/../dir2/)
	// also deny direct access to blocks.
    if (eregi("\.inc\.php",$_SERVER['PHP_SELF']) || eregi("block-.*\.php",$_SERVER['PHP_SELF']) || ereg("\.\.",$_SERVER['PHP_SELF'])) 
	{
		die("Invalid URL");
	}
?>
