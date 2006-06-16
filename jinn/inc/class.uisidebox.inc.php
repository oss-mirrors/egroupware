<?php
   /**************************************************************************\
   * eGroupWare - Sidebox-Menu for idots-template                             *
   * http://www.egroupware.org                                                *
   * Written by Pim Snel <pim@lingewoud.nl>                                   *
   * --------------------------------------------                             *
   *  This program is free software; you can redistribute it and/or modify it *
   *  under the terms of the GNU General Public License as published by the   *
   *  Free Software Foundation; either version 2 of the License, or (at your  *
   *  option) any later version.                                              *
   \**************************************************************************/

   include_once(PHPGW_INCLUDE_ROOT.'/jinn/inc/class.uijinn.inc.php');

   /**
   * uisidebox 
   * 
   * @uses uijinn
   * @package 
   * @version $Id$
   * @copyright Lingewoud B.V.
   * @author Pim Snel <pim-AT-lingewoud-DOT-nl> 
   * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   */
   class uisidebox extends uijinn
   {

	  var $bo;
	  var $ui;
	  var $tplsav2;

	  /**
	  * uisidebox 
	  * 
	  * @access public
	  * @return void
	  */
	  function uisidebox() 
	  {
		 $this->bo = &CreateObject('jinn.bojinn');
		 parent::uijinn();
	  }

	  /**
	  * sidebox_menu 
	  * 
	  * @access public
	  * @return void
	  */
	  function sidebox_menu()
	  {
		 // get sites for user and group and make options
		 $appname='jinn';

		 $sites=$this->bo->get_sites_allowed($GLOBALS['phpgw_info']['user']['account_id']);

		 if(is_array($sites))
		 {
			foreach($sites as $site_id)
			{
			   $site_arr[]=array(
				  'value'=>$site_id,
				  'name'=>$this->bo->so->get_site_name($site_id)
			   );
			}
		 }

		 $site_options=$this->select_options($site_arr,$this->bo->session['site_id'],true,'-1');
		 $this->tplsav2->assign('sites_options',$site_options);
		 $this->tplsav2->site_name=$this->bo->so->get_site_name($this->bo->session['site_id']);

		 // get objects for user and group and make options
		 if ($this->bo->session['site_id'])
		 {
			$objects=$this->bo->get_objects_allowed($this->bo->session['site_id'], $GLOBALS['phpgw_info']['user']['account_id']);

			if (is_array($objects))
			{
			   foreach ( $objects as $object_id) 
			   {
				  $objects_arr[]=array(
					 'value'=>$object_id,
					 'name'=>$this->bo->so->get_object_name($object_id)
				  );
			   }
			}

			$object_options=$this->select_options($objects_arr,$this->bo->session['site_object_id'],true,'-1');
			$this->tplsav2->assign('object_options',$object_options);
			$this->tplsav2->assign('objects_arr',$objects_arr);

		 }
		 else
		 {
			unset($this->bo->session['site_object_id']);
		 }

		 $this->tplsav2->assign('action',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiuser.index'));

		 $nav_form=$this->tplsav2->fetch('sdbox_nav.tpl.php');

		 $menu_title = lang('JiNN Navigator');

		 $file['JiNN Nav'] = array(
			'text'=>$nav_form,
			'icon'=>false,
			'no_lang' => True,
			'link' => False,
		 );

		 /*if($this->bo->session['site_id'])
		 {
			$file3 = Array(
			   'Site Main' => array(
				  'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiuser.list_all_objects'),
				  'icon'=>'mini_navbar',
				  'no_lang' => True,
				  'text'=>lang('Site Main')
			   ),
			);
		 }
		 */

		 display_sidebox($appname,$menu_title,$file);

		 $file=array();
		 $conf=array();

		 if($this->bo->session['site_id'] && $this->bo->session['site_object_id'])
		 {
			$menu_title = $this->bo->site_object[name];

			//if list, if more then one record
			if($this->bo->site_object[max_records]!=1)
			{
			   $file['Browse current object'] = array(
				  'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_list_records.display'),
				  'icon'=>'browse',
				  'no_lang' => True,
				  'text'=>lang('List records')
			   );
			}

			//if import
			if($this->bo->objectelements['enable_import']) 
			{
			   $file['ImportCSV'] = array(
				  'text'=>lang('Import CSV'),
				  'icon'=>'filesave',
				  'no_lang' => True,
				  'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_import.import')
			   );
			}

			//if export
			if($this->bo->objectelements['enable_export']) 
			{
			   $file['Export current object'] = array(
				  'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_export.export'),
				  'icon'=>'filesave',
				  'no_lang' => True,
				  'text'=>lang('Export CSV')
			   );
			}

			//if list, if more then one record
/*			$file['Configure this Object List View']= Array(
			   'link'=>$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.config_objects'),
			   'no_lang' => True,
			   'text'=>lang('Configure List'),
			   'icon'=>'configure_toolbars'
			);
*/
		 }

		 //if create 
		 if($this->bo->objectelements['enable_create_rec'] && $this->bo->session['site_id'] 
		 && $this->bo->site_object['object_id'] && $this->bo->site_object['max_records']!=1)
		 {
			$file['Add new entry'] = Array(
			   'text'=>lang('New record(s)'),
			   'no_lang' => True,
			   'icon'=>'new',
			   'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_edit_record.new_record')
			);
		 }

		 if(count($file)>0)
		 {
			display_sidebox($appname,$menu_title,$file);
			$file=array();
		 }

		 // NOTE removed preferences link as these are not very helpfull, technically prefs are still enabled and accessible via the main prefs screen
		 /*		 $menu_title = lang('JiNN Preferences');
		 $file = Array(
			'General Preferences' => array(
			   'link'=>$GLOBALS['phpgw']->link('/preferences/preferences.php','appname=jinn'),
			   'icon'=>'configure',
			   'no_lang' => True,
			   'text'=>lang('General Preferences')
			),
		 );
		 */

		 display_sidebox($appname,$menu_title,$file);
		 $file=array();
		 // if admin or side-admin show access rights
		 if (!$GLOBALS['phpgw_info']['user']['apps']['admin'] && $this->bo && count($this->bo->so->get_sites_for_user2($GLOBALS['phpgw_info']['user']['account_id']))>0)
		 {
			$menu_title = lang('Administration');
			$file = Array(
			   'Access Rights' => array(
				  'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiacl.main_screen'),
				  'text'=>lang('Access Rights'),
				  'no_lang' => True,
				  'icon'=>'groupevent'
			   )
			);
			display_sidebox($appname,$menu_title,$file);
			$file=array();
		 }
		 elseif($GLOBALS['phpgw_info']['user']['apps']['admin'])
		 {
			$menu_title = lang('Administration');
			$file['Global Configuration'] = array(
			   'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiconfig.index&appname=' . $appname),
			   'text'=>lang('Global Configuration'),
			   'no_lang' => True,
			   'icon'=>'configure'
			);
			$file['Access Rights'] = array(
			   'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiacl.main_screen'),
			   'text'=>lang('Access Rights'),
			   'no_lang' => True,
			   'icon'=>'groupevent'
			);
			$file['Add Site'] = array(
			   'link' => $GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.add_edit_site'),
			   'text'=>lang('Add Site'),
			   'no_lang' => True,
			   'icon'=>'new'
			);
			$file['Browse through sites'] = array(
			   'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.browse_egw_jinn_sites'),
			   'text'=>lang('Browse through sites'),
			   'no_lang' => True,
			   'icon'=>'browse'
			);
			$file['Load site conf from file'] = array(
			   'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.ui_importsite.import_egw_jinn_site'),
			   'text'=>lang('Load site conf from file'),
			   'no_lang' => True,
			   'icon'=>'fileopen'
			);

			if ($this->bo->session['site_id'])
			{
			   $file['Save site conf to file'] = array(
				  'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.exportsite.save_site_to_file&where_key=site_id&where_value='.$this->bo->site[site_id]),
				  'text'=>lang('Save site conf to file'),
				  'no_lang' => True,
				  'icon'=>'filesave'
			   );
			}

			if ($this->bo->session['site_id'])
			{
			   /* $file['_NewLine_']=array(
				  'text'=>'',
				  'no_lang' => True,
				  'link'=>false
			   );
			   */
			   $file['Edit this Site'] = array(
				  'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.edit_this_jinn_site'),
				  'text'=>lang('Edit this Site'),
				  'no_lang' => True,
				  'icon'=>'edit'
			   );
			}

			if ($this->bo->session['site_object_id'])
			{
			   if($_GET['menuaction']=='jinn.uiu_list_records.display') 
			   {
				  $devlink = $GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_list_records.display_dev&site_id='.$this->bo->site_object[parent_site_id].'&site_object_id='.$this->bo->session['site_object_id']);
			   }
			   else
			   {
				  $devlink = $GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_edit_record.dev_edit_record&site_id='.$this->bo->site_object[parent_site_id].'&site_object_id='.$this->bo->session['site_object_id']);
			   }

			   $file['Edit this Site Object'] = array(
				  'link'=>$devlink,
				  'text'=>lang('Edit this Site Object'),
				  'no_lang' => True,
				  'icon'=>'edit'

			   );
			}

			display_sidebox($appname,$menu_title,$file);
			$file=array();
		 }

	  }

   }
?>
