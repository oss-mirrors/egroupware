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

	  var $public_functions = Array
	  (
		 'openTreeLink'			=> True
	  );

	  /**
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

		 display_sidebox($appname,$menu_title,$file);

		 /*----------------*/

		 // PROJECT TREE

		 $folders=$this->setMenuTree();
		 $folderImageDir = $GLOBALS['egw_info']['server']['webserver_url'].'/jinn/templates/default/images/';

		 //$selected_folder=$this->bo->retrievePath($this->node); //'/1/4/5';
		 //$selected_folder=$_GET['link'];
		 echo $this->selected_link;

		 $select_link=$GLOBALS['egw']->link('/index.php','menuaction=jinn.uisidebox.openTreeLink&link=');

		 unset($content);
		 if(is_array($folders))	// show project-tree only if it's not empty
		 {
			$start_img = $GLOBALS['egw_info']['server']['webserver_url'].'/qproject/templates/default/images/dhtmlxtree/folderOpen.gif';
			$this->tplsav2->assign('start_img',$start_img);
			$this->tplsav2->assign('select_link',$select_link);
			$this->tplsav2->assign('tree',$GLOBALS['egw']->html->tree($folders,$this->selected_link,false,'load_project','foldertree','','folderClosed.gif',false,'/',$folderImageDir));
			$content[0] = 
			array(
			   'text' => $this->tplsav2->fetch('sidebox_treemenu.tpl.php'),
			   'no_lang' => True,
			   'link' => False,
			   'icon' => False,
			);
		 }

		 $menu_title = lang('JiNN Menu');

		 display_sidebox($appname,$menu_title,$content);

		 /*----------------*/

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
			$file['Load site conf from file'] = array(
			   'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.ui_importsite.import_egw_jinn_site'),
			   'text'=>lang('Load site conf from file'),
			   'no_lang' => True,
			   'icon'=>'fileopen'
			);

			if ($this->bo->session['site_id'])
			{
			   $file['Save site conf to XML'] = array(
				  'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.exportsite.save_site_to_xml&where_key=site_id&where_value='.$this->bo->site[site_id]),
				  'text'=>lang('Save site conf to XML'),
				  'no_lang' => True,
				  'icon'=>'filesave'
			   );
			}

			$file['Add Site'] = array(
			   'link' => $GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.add_edit_site'),
			   'text'=>lang('Add Site'),
			   'no_lang' => True,
			   'icon'=>'new'
			);
			$file['List all JiNN Sites'] = array(
			   'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.ui_listsites.browse_egw_jinn_sites'),
			   'text'=>lang('List all JiNN Sites'),
			   'no_lang' => True,
			   'icon'=>'browse'
			);

			if ($this->bo->session['site_id'])
			{
			   $file['Edit this Site'] = array(
				  'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.edit_this_jinn_site'),
				  'text'=>lang('Edit this Site'),
				  'no_lang' => True,
				  'icon'=>'edit'
			   );
			}

			if ($this->bo->session['site_object_id'])
			{
			   $devlinklist = $GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_list_records.display_dev&site_id='.$this->bo->site_object[parent_site_id].'&site_object_id='.$this->bo->session['site_object_id']);
			   $devlinkform = $GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_edit_record.dev_edit_record&site_id='.$this->bo->site_object[parent_site_id].'&site_object_id='.$this->bo->session['site_object_id']);

			   $file['Edit Object Form View'] = array(
				  'link'=>$devlinkform,
				  'text'=>lang('Edit Object Form View'),
				  'no_lang' => True,
				  'icon'=>'edit'

			   );
			   $file['Edit Object List View'] = array(
				  'link'=>$devlinklist,
				  'text'=>lang('Edit Object List View'),
				  'no_lang' => True,
				  'icon'=>'edit'

			   );
			}

			display_sidebox($appname,$menu_title,$file);
			$file=array();
		 }

	  }

	  
	  function setMenuTree($parent_id=0,$ppath='')
	  {
		 $menustruct['/s']['label']='JiNN-sites';
		 $menustruct['/s']['image']='jinn18.png';
	
		 $this->sites_allowed=$this->bo->get_sites_allowed($GLOBALS['phpgw_info']['user']['account_id']);
		 if(is_array($this->sites_allowed))
		 {
			foreach($this->sites_allowed as $site_id)
			{
			   $menustruct['/s/s.'.$site_id]['label']=$this->bo->so->get_site_name($site_id); //FIXME PERFORMANCE!
			   $menustruct['/s/s.'.$site_id]['image']='database18.png';

			   // objects
			   $objects=$this->bo->get_objects_allowed($site_id, $GLOBALS['phpgw_info']['user']['account_id']);

			   $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.o']['label']=lang('Objects'). ' ('.count($objects).')';
			   $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.o']['image']='jinn18.png';

			   if (is_array($objects) && $this->bo->session['site_id']==$site_id)
			   {
				  foreach ( $objects as $object_id) 
				  {
					 $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.o/o.'.$object_id]['label']=$this->bo->so->get_object_name($object_id); // FIXME PERFORMANCE!
					 $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.o/o.'.$object_id]['image']='object18.png';

					 $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.o/o.'.$object_id.'/o.'.$object_id.'addrec']['label']='Add Records';
					 $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.o/o.'.$object_id.'/o.'.$object_id.'addrec']['image']='addrec18.png';
					 $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.o/o.'.$object_id.'/o.'.$object_id.'listrec']['label']='List Records';
					 $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.o/o.'.$object_id.'/o.'.$object_id.'listrec']['image']='listview18.png';
					 $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.o/o.'.$object_id.'/o.'.$object_id.'import']['label']='Import';
					 $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.o/o.'.$object_id.'/o.'.$object_id.'import']['image']='import18.png';
					 $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.o/o.'.$object_id.'/o.'.$object_id.'export']['label']='Export';
					 $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.o/o.'.$object_id.'/o.'.$object_id.'export']['image']='export18.png';
					 $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.o/o.'.$object_id.'/o.'.$object_id.'objproperties']['label']='Object Properties';
					 $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.o/o.'.$object_id.'/o.'.$object_id.'objproperties']['image']='objectprop18.png';
					 $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.o/o.'.$object_id.'/o.'.$object_id.'designform']['label']='Design Form';
					 $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.o/o.'.$object_id.'/o.'.$object_id.'designform']['image']='formprop18.png';
					 $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.o/o.'.$object_id.'/o.'.$object_id.'designlist']['label']='Design List';
					 $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.o/o.'.$object_id.'/o.'.$object_id.'designlist']['image']='listviewprop18.png';
					 $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.o/o.'.$object_id.'/o.'.$object_id.'exportobject']['label']='Export Object';
					 $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.o/o.'.$object_id.'/o.'.$object_id.'exportobject']['image']='objectexport18.png';
				  }
			   }

			   //admin or owners options
			   $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.properties']['label']=lang('Site Properties');
			   $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.properties']['image']='siteprop18.png';
		   
			   $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.importobject']['label']=lang('Import Object');
			   $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.importobject']['image']='objectimport18.png';

			   $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.exportsite']['label']=lang('Export Site');
			   $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'.exportsite']['image']='siteexport18.png';

			   $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'createobject']['label']='Create Object';
			   $menustruct['/s/s.'.$site_id.'/s.'.$site_id.'createobject']['image']='createobject18.png';
			}
		 }

		 $menustruct['/s/2/createobject']['label']='Create Object';
		 $menustruct['/s/2/createobject']['image']='createobject18.png';
		 
		 $menustruct['/addsite']['label']='Create JiNN-site';
		 $menustruct['/addsite']['image']='createsite18.png';
		 $menustruct['/importsite']['label']='Import Site';
		 $menustruct['/importsite']['image']='siteimport18.png';
		 $menustruct['/acl']['label']='ACL';
		 $menustruct['/acl']['image']='acl18.png';
		 return $menustruct;
	  }

//	  function resolveSelectedLink($_GET['link'])

	  function openTreeLink()
	  {
		 $_link=explode('.',$_GET['link']);
		 if(count($_link)==1)
		 {
			$this->selected_link='/'.$_link[0];
			switch($_GET['link'])
			{
			   case 's':
			   ExecMethod('jinn.ui_listsites.browse_egw_jinn_sites');
			   break;
			   case 'addsite':
			   ExecMethod('jinn.uiadmin.add_edit_site');
			   break;
			   case 'acl':
			   ExecMethod('jinn.uiacl.main_screen');
			   break;
			   case 'importsite':
			   ExecMethod('jinn.ui_importsite.import_egw_jinn_site');
			   break;
			}
		 }
		 elseif(count($_link)==2)
		 {
			$this->bo->session['site_id']=$_link[1];
			$this->bo->session['site_object_id']=-1;
			$this->bo->sessionmanager->save();
			$this->selected_link='/s/s.'.$_link[1].'/s.'.$_link[1].'.o';
			ExecMethod('jinn.uiuser.index');
		 }
		 elseif(count($_link)==3)
		 {
			switch($_link[2])
			{
			   case 'o':
			   $this->bo->session['site_id']=$_link[1];
			   $this->bo->session['site_object_id']=-1;
			   $this->bo->sessionmanager->save();
			   $this->selected_link='/s/s.'.$_link[1].'/s.'.$_link[1].'.o';
			   ExecMethod('jinn.uiuser.index');
			   break;

			   case 'importobject'://FIXME NOT WORKING
			   $this->bo->session['site_id']=$_link[1];
			   $this->bo->session['site_object_id']=-1;
			   $this->bo->sessionmanager->save();
			   //ExecMethod('jinn.ui_importsite.import_object&where_key=site_id&where_value=4');
			   ExecMethod('jinn.ui_importsite.import_object');
			}
		 }
	  }
   }
?>
