<?php
  /**************************************************************************\
  * eGroupWare - JiNN                                                        *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	/* $Id$ */

	class hook_menu
	{
		function menu()
		{
			$menu = Array();
			$menu['Records'] = Array(
			   'Add new entry'   => $GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_edit_record.display_form'),
				);
				
			$menu['Preferences'] = Array(
				);
			
			if ($GLOBALS['phpgw_info']['user']['apps']['admin']) {
				$menu['Administration'] = Array(
					);
			}
		
			return $menu;
		}
		
	}	
?>
