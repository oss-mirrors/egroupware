<?php
{ 
// Only Modify the $file and $title variables.....
	$title = $appname;
	$file = Array(
		'view system informations'	=> $phpgw->link('/phpsysinfo/index.php')
	);
//Do not modify below this line
	display_section($appname,$title,$file);
}
?>
