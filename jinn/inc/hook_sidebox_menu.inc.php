<?php
   /**************************************************************************\
   * eGroupWare - Calendar's Sidebox-Menu for idots-template                  *
   * http://www.egroupware.org                                                *
   * Written by Pim Snel <pim@lingewoud.nl>                                   *
   * --------------------------------------------                             *
   *  This program is free software; you can redistribute it and/or modify it *
   *  under the terms of the GNU General Public License as published by the   *
   *  Free Software Foundation; either version 2 of the License, or (at your  *
   *  option) any later version.                                              *
   \**************************************************************************/

   /* $Id$ */
   {
	  $menu_title = lang('JiNN Editors Menu');

	  $file = Array(
		 'JiNN Main' => array(
			'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiuser.index'),
			'icon'=>'mini_navbar',
			'text'=>'JiNN Main'
		 ),
	  );

	  if ($GLOBALS[local_bo]->site[site_id] && $GLOBALS[local_bo]->site_object[object_id])
	  {
		 $object = Array(
			'Browse current object' => array(
			   'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_list_records.display'),
			   'icon'=>'browse',
			   'text'=>'List record(s) in current object'
			),
		 );
		 $file=array_merge($file,$object);
	  }

	  if ($GLOBALS[local_bo]->site[site_id] && $GLOBALS[local_bo]->site_object[object_id] && $GLOBALS[local_bo]->site_object[max_records]!=1)
	  {
		 $object = Array(
			'Add multiple records' => array(
			   'text'=>'Add multiple records',
			   'icon'=>'new',
			   'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_edit_record.multiple_entries&insert=yes')
			),
			'Add new entry' => array(
			   'text'=>'Add new entry',
			   'icon'=>'new',
			   'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_edit_record.display_form')
			)
		 );
		 $file=array_merge($file,$object);
	  }

	  if($GLOBALS[local_bo]->last_where_string)
	  {
		 $last_record=Array(
			'Last edited record' => array
			(
			   'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_edit_record.display_form&where_string='.$GLOBALS[local_bo]->last_where_string),
			   'text'=>'Last edited record',
			   'icon'=>'edit'
			)
		 );

		 $file=array_merge($file,$last_record);
	  }

	  
	  $file['_NewLine_']='_NewLine_'; // give a newline


	  if($GLOBALS[uiuser]->bo->site[website_url])
	  {
		 $file['Preview Website']=array(
			'link'=>$GLOBALS[uiuser]->bo->site[website_url],
			'text'=>'Preview Website',
			'target'=>'_blank',
			'icon'=>'view'
		 );

	  }
	  elseif($GLOBALS[local_bo]->site[website_url])
	  {
		 $file['_NewLine_']='_NewLine_'; // give a newline
		 $file['Preview Website']=array(
			'link'=>$GLOBALS[local_bo]->site[website_url],
			'text'=>'Preview Website',
			'icon'=>'view',
			'target'=>'_blank'
		 );
	  }

	  display_sidebox($appname,$menu_title,$file);

	  $menu_title = lang('JiNN Preferences');
	  $file = Array(
		 'General Preferences' => array(
			'link'=>$GLOBALS['phpgw']->link('/preferences/preferences.php','appname=jinn'),
			'icon'=>'configure',
			'text'=>'General Preferences'
		 ),
	  );

	  if ($GLOBALS[local_bo]->site_object[object_id])
	  {
		 $conf = Array(
			'Configure this Object List View'=> array(
			   'link'=>$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.config_objects'),
			   'text'=>'Configure this Object List View',
			   'icon'=>'configure_toolbars'
			)
		 );
		 $file=array_merge($file,$conf);
	  }

	  display_sidebox($appname,$menu_title,$file);


	  // if admin or side-admin show access rights
	  if (!$GLOBALS['phpgw_info']['user']['apps']['admin'] && $GLOBALS[local_bo] && count($GLOBALS[local_bo]->so->get_sites_for_user2($GLOBALS['phpgw_info']['user']['account_id']))>0)
	  {
		 $menu_title = lang('Administration');
		 $file = Array(
			'Access Rights' => array(
			   'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiacl.main_screen'),
			   'text'=>'Access Rights',
			   'icon'=>'groupevent'
			)
		 );
		 display_sidebox($appname,$menu_title,$file);

	  }
	  elseif($GLOBALS['phpgw_info']['user']['apps']['admin'])
	  {
		 $menu_title = lang('Administration');
		 $file = Array(
			'Global Configuration' => array(
			   'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiconfig.index&appname=' . $appname),
			   'text'=>'Global Configuration',
			   'icon'=>'configure'
			),
			'Access Rights' => array(
			   'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiacl.main_screen'),
			   'text'=>'Access Rights',
			   'icon'=>'groupevent'
			),
			'Add Site' => array(
			   'link' => $GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.add_edit_site'),
			   'text'=>'Add Site',
			   'icon'=>'new'
			),
			'Browse through sites' => array(
			   'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.browse_egw_jinn_sites'),
			   'text'=>'Browse through sites',
			   'icon'=>'browse'
			),
			'Load site conf from file' => array(
			   'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.import_egw_jinn_site'),
			   'text'=>'Load site conf from file',
			   'icon'=>'fileopen'
			),
		 );

		 if ($GLOBALS[local_bo]->site[site_id])
		 {
			$site = Array(
			   'Save site conf to file' => array(
				  'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.export_site&where_key=site_id&where_value='.$GLOBALS[uiuser]->bo->site[site_id]),
				  'text'=>'Save site conf to file',
				  'icon'=>'filesave'
			   )

			);
			$file=array_merge($file,$site);
		 }

		 if ($GLOBALS[local_bo]->site[site_id])
		 {
			$site = Array(
			   '_NewLine_', // give a newline
			   'Edit this Site' => array(
				  'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.edit_this_jinn_site'),
				  'text'=>'Edit this Site',
				  'icon'=>'edit'
			   ),
			);
			$file=array_merge($file,$site);
		 }

		 if ($GLOBALS[local_bo]->site_object[object_id])
		 {
			$object = Array(
			   'Edit this Site Object' => array(
				  'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.edit_this_jinn_site_object'),
				  'text'=>'Edit this Site Object',
				  'icon'=>'edit'
			   )

			);
			$file=array_merge($file,$object);
		 }


		 display_sidebox($appname,$menu_title,$file);

		 if($GLOBALS[local_bo]->common->prefs['experimental']=='yes')
		 {
			$menu_title = lang('Developer Links');
			$file = Array(
			   'Site Media and Documents' => array
			   (
				  'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiumedia.index'),
				  'text'=>'Site Media and Documents',
				  'icon'=>'thumbnail'
			   ),
			);
			display_sidebox($appname,$menu_title,$file);
		 }


	  }

   }
?>
