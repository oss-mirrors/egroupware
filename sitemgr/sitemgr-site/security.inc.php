<?php
	// Security precaution: prevent script tags: <script>, <javascript "">, etc.
	foreach ($HTTP_GET_VARS as $secvalue) 
	{
		if (eregi("<[^>]*script*\"?[^>]*>", $secvalue)) 
		{
			die("A security breach has been attempted and refused.");
		}
	}

	// Security precaution: don't let anyone call xxx.inc.php files or
    // construct URLs with relative paths (ie, /dir1/../dir2/)
    if (eregi("\.inc\.php",$PHP_SELF) || ereg("\.\.",$PHP_SELF)) 
	{
		Header("Location: index.php");
		die();
	}
?>
