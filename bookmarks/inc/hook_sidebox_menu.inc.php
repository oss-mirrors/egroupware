<?php
        /**************************************************************************\
         * eGroupWare - Bookmarks                                                   *
         * http://www.egroupware.org                                                *
         * --------------------------------------------                             *
         *  This program is free software; you can redistribute it and/or modify it *
         *  under the terms of the GNU General Public License as published by the   *
         *  Free Software Foundation; either version 2 of the License, or (at your  *
         *  option) any later version.                                              *
        \**************************************************************************/

        /* $Id$ */
        /*
           This hookfile is for generating an app-specific side menu used in the idots
           template set.

           $menu_title speaks for itself
           $file is the array with link to app functions

           display_sidebox can be called as much as you like
        */
         
		$file = Array(
			'tree view'        => $GLOBALS['phpgw']->link('/index.php','menuaction=bookmarks.ui.tree'),
			'list view'        => $GLOBALS['phpgw']->link('/index.php','menuaction=bookmarks.ui._list'),
			'new bookmark'     => $GLOBALS['phpgw']->link('/index.php','menuaction=bookmarks.ui.create'),
			'Search'           => $GLOBALS['phpgw']->link('/index.php','menuaction=bookmarks.ui.search'),
			'Import Bookmarks' => $GLOBALS['phpgw']->link('/index.php','menuaction=bookmarks.ui.import'),
			'Export Bookmarks' => $GLOBALS['phpgw']->link('/index.php','menuaction=bookmarks.ui.export'),
		);
		display_sidebox($appname,$GLOBALS['phpgw_info']['apps'][$appname]['title'],$file);
		
        if($GLOBALS['phpgw_info']['user']['apps']['preferences'])
        {
	    $menu_title = lang('Preferences');
            $file = array(
			  'Grant Access'  => $GLOBALS['phpgw']->link('/index.php','menuaction=preferences.uiaclprefs.index&acl_app='.$appname),
			  'Edit Categories' => $GLOBALS['phpgw']->link('/index.php','menuaction=preferences.uicategories.index&cats_app=' . $appname . '&cats_level=True&global_cats=True')
			  );
	    display_sidebox('bookmarks',$menu_title,$file);
	}    
        
        if ($GLOBALS['phpgw_info']['user']['apps']['admin'])
        {
	    $menu_title = lang('Administration');
            $file = Array(
			 'Site Configuration' => $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiconfig.index&appname=' .
							      $appname),
			 'Global Categories' => $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uicategories.index&appname=bookmarks')
	      );

	    display_sidebox('bookmarks',$menu_title,$file);
	}      


