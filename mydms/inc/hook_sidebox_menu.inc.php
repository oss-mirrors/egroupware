<?php
{ 
        
        $menu_title = 'Mydms Tool';
        $file = Array(
		      'Content'                 =>  $settings->_httpRoot . "out.ViewFolder.php?folderid=1",
		      'Search'			=>  $settings->_httpRoot . "out.SearchForm.php?folderid=1"
	        );
	display_sidebox($appname,$menu_title,$file);
       
  
       if ($GLOBALS['phpgw_info']['user']['apps']['admin'])
	{
        $menu_title = 'Administration';
        $file = Array(
		      'Admin-Tools'			=>  $settings->_httpRoot . "out.AdminTools.php"
	        );
		display_sidebox($appname,$menu_title,$file);
	}
}

?>