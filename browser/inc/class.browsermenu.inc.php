<?php
  /**************************************************************************\
  * eGroupWare - Calendar                                                    *
  * http://www.egroupware.org                                                *
  * Based on Webcalendar by Craig Knudsen <cknudsen@radix.net>               *
  *          http://www.radix.net/~cknudsen                                  *
  * Modified by Mark Peters <skeeter@phpgroupware.org>                       *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	/* $Id$ */

	class browsermenu
	{
		function browsermenu()
		{

			$menu = Array();
			$menu['File'] = Array(
				'Main'   => $GLOBALS['phpgw']->link('/index.php','menuaction=browser.ui.index')
				);
				
			
			$db;
			$table = 'phpgw_bookmarks';
			$owner;
			$this->db    = $GLOBALS['phpgw']->db;
			$this->owner = $GLOBALS['phpgw_info']['user']['account_id'];
			$config = CreateObject('phpgwapi.config');
			$config->read_repository();
			unset($config);
			$count = 0;
			$time = time();
			$date_new = time()+ 604800;
		
			$this->db->limit_query('SELECT * FROM `'. $table .'` WHERE bm_owner = '. $this->owner,"",__LINE__,__FILE__);
			$arr = Array(
			'eGroupWare'=>'javascript:setUrl(\'http://www.egroupware.org\');'
			);
			while($this->db->next_record())
			{
				$arr[$this->db->f('bm_name')] = 'javascript:setUrl(\''. $this->db->f('bm_url') . '\');';
			}
			
			$menu['Bookmarks'] = $arr;
				
			$menu['Preferences'] = Array(
				'Browser preferences'=>$GLOBALS['phpgw']->link('/preferences/preferences.php','appname=browser'),
				);
			
		
		
			return $menu;
		}
		
		
	}	
?>
