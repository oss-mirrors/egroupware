<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
   Copyright (C)2002, 2003 Pim Snel <pim@lingewoud.nl>

   eGroupWare - http://www.egroupware.org

   This file is part of JiNN

   JiNN is free software; you can redistribute it and/or modify it under
   the terms of the GNU General Public License as published by the Free
   Software Foundation; either version 2 of the License, or (at your 
   option) any later version.

   JiNN is distributed in the hope that it will be useful,but WITHOUT ANY
   WARRANTY; without even the implied warranty of MERCHANTABILITY or 
   FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
   for more details.

   You should have received a copy of the GNU General Public License 
   along with JiNN; if not, write to the Free Software Foundation, Inc.,
   59 Temple Place, Suite 330, Boston, MA 02111-1307  USA
   */

   /* $id$ */

   /* main ui class for user as administrator */


   class uicommon
   {

	  var $app_title;
	  var $template;
	  var $message;
	  var $bo;

	  function uicommon($bo=false)
	  {
		 $this->template = $GLOBALS['phpgw']->template;
		 $this->bo = $bo;
	  }

	  /**
	  * header renders the app & screen title,
	  * 
	  * @param string $screen_title 
	  */
	  function header($screen_title,$phpgw_header=true)
	  {
		 unset($GLOBALS['phpgw_info']['flags']['noheader']);
		 unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
		 unset($GLOBALS['phpgw_info']['flags']['noappheader']);
		 unset($GLOBALS['phpgw_info']['flags']['noappfooter']);
		 if($this->app_title) $extra_title =' ('.$this->app_title.')';
		 $GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['jinn']['title']. ' - '.$screen_title . $extra_title;

		 if($phpgw_header)$GLOBALS['phpgw']->common->phpgw_header();
	  }


	  /**
	  @function msg_box  
	  @abstract format a standard msg_box print errors in a red font and info messages in green
	  @param array $msg_arr ['info'] contains the info msg and ['error'] the error msg
	  */
	  function msg_box($msg_arr)
	  {


		 if ($msg_arr['info'])
		 {
			if(is_array($msg_arr['info']))
			{
			   foreach($msg_arr['info'] as $info_str)
			   {
				  $info.='<div style="margin:3px;color:green;clear:both"><img style="margin:0px 5px 0px 0px;float:left"  src="'.$GLOBALS[phpgw]->common->image('phpgwapi','dialog_info').'" alt="'.lang('info').'"/> '.$info_str.'</div><br/>';
			   }
			}
			else
			{
			   $info='<div style="margin:3px;color:green;clear:both"><img style="margin:0px 5px 0px 0px;float:left" src="'.$GLOBALS[phpgw]->common->image('phpgwapi','dialog_info').'" alt="'.lang('info').'"/> '.$msg_arr['info'].'</div><br/>';
			}
		 }

		 if ($msg_arr['help'])
		 {
			if(is_array($msg_arr['help']))
			{
			   foreach($msg_arr['help'] as $help_str)
			   {
				  $help.='<div style="margin:3px;color:blue;clear:both"><img style="margin:0px 5px 0px 0px;float:left" src="'.$GLOBALS[phpgw]->common->image('phpgwapi','dialog_help').'" alt="'.lang('help').'"/> '.$help_str.'</div><br/>';
			   }
			}
			else
			{
			   $help='<div style="margin:3px;color:blue;clear:both"><img style="margin:0px 5px 0px 0px;float:left"   src="'.$GLOBALS[phpgw]->common->image('phpgwapi','dialog_help').'" alt="'.lang('help').'"/> '.$msg_arr['help'].'</div><br/>';
			}
		 }

		 if ($msg_arr['error'])
		 {
			if($msg_arr['error_code'])
			{
			   $error_code=' ( '.lang('ERROR CODE %1',$msg_arr['error_code']).')';
			}

			if(is_array($msg_arr['error']))
			{
			   foreach($msg_arr['error'] as $error_str)
			   {
				  $error.='<div style="margin:3px;color:red;clear:both"><img style="margin:0px 5px 0px 0px;float:left"   src="'.$GLOBALS[phpgw]->common->image('phpgwapi','dialog_error').'" alt="'.lang('error').'"/> '.$error_str.'</div>'.$error_code.'<br/>';
			   }
			}
			else
			{
			   $error='<div style="margin:3px;color:red;clear:both"><img style="margin:0px 5px 0px 0px;float:left"   src="'.$GLOBALS[phpgw]->common->image('phpgwapi','dialog_error').'" alt="'.lang('error').'"/> '.$msg_arr['error'].'</div>'.$error_code.'<br/>';
			}
		 }

		 if ($msg_arr['debug'])
		 {

			if(is_array($msg_arr['debug']))
			{
			   foreach($msg_arr['debug'] as $error_str)
			   {
				  $debug.='<div style="margin:3px;color:#561800;clear:both"><img style="margin:0px 5px 0px 0px;float:left" src="'.$GLOBALS[phpgw]->common->image('phpgwapi','dialog_debug').'" alt="'.lang('debug').'"/> '.$error_str.'</div>';
			   }
			}
			else
			{
			   $debug='<div style="margin:3px;color:#561800;clear:both"><img style="margin:0px 5px 0px 0px;float:left" src="'.$GLOBALS[phpgw]->common->image('phpgwapi','dialog_debug').'" alt="'.lang('debug').'"/> '.$msg_arr['debug'].'</div><br/>';
			}
		 }
		 if($info || $error || $help || $debug)
		 {
			$this->template->set_file(array(
			   'msg_box' => 'msg_box.tpl'
			));

			$this->template->set_var('help',$help);
			$this->template->set_var('error',$error);
			$this->template->set_var('info',$info);
			$this->template->set_var('debug',$debug);
			$this->template->pparse('out','msg_box');
		 }


	  }


	  /**
	  * returns the options of a selectbox
	  * 
	  * @return string html formatted options 
	  * @param array $list_array array with values and names for the options 
	  * @param mixed $selected_value value that must be selected
	  * @param boolean $allow_empty allow emty options
	  */
	  function select_options($list_array,$selected_value,$allow_empty=false)
	  {
		 if($allow_empty) $options.="<option value=\"\">------------------</option>\n";

		 if(is_array($list_array))
		 {
			foreach ( $list_array as $array ) 
			{
			   unset($SELECTED);
			   if ($array[value]==$selected_value)
			   {
				  //echo $array[value].'='.$selected_value.'<br/>';

				  $SELECTED='selected="selected"';
			   }				
			   if ($array[name]) $name = $array[name];
			   else $name = $array[value];


			   $options.="<option value=\"".$array[value]."\" $SELECTED>".stripslashes($name)."</option>\n";
			}

		 }
		 return $options;
	  }

	  /**
	  * this function parses the main moderators menu
	  * with this menu you can scroll through sites and objects
	  */
	  function main_menu()
	  {
		 $this->template->set_file(array(
			'main_menu' => 'main_menu.tpl'));

			// get sites for user and group and make options
			$sites=$this->bo->common->get_sites_allowed($GLOBALS['phpgw_info']['user']['account_id']);

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

			$site_options=$this->select_options($site_arr,$this->bo->site_id,true);


			if ($this->bo->site_id)
			{
			   $objects=$this->bo->common->get_objects_allowed($this->bo->site_id, $GLOBALS['phpgw_info']['user']['account_id']);

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

			   $object_options=$this->select_options($objects_arr,$this->bo->site_object_id,true);

			}
			else
			{
			   unset($this->bo->site_object_id);
			}

			$this->template->set_var('jinn_main_menu',lang('JiNN Main Menu'));

			// set menu
			$this->template->set_var('site_objects',$object_options);
			$this->template->set_var('site_options',$site_options);

			$this->template->set_var('main_form_action',$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.index'));
			$this->template->set_var('select_site',lang('select site'));
			$this->template->set_var('select_object',lang('select_object'));
			$this->template->set_var('go',lang('go'));

			/* set admin shortcuts */
			// if site if site admin
			if($this->bo->site_id && $userisadmin)
			{
			   $admin_site_link='<br><a href="'.$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiadminaddedit.').'">'.
				  lang('admin:: edit site').'</a>';
			}
			$this->template->set_var('admin_site_link',$admin_site_link);
			$this->template->set_var('admin_object_link',$admin_object_link);

			$this->template->pparse('out','main_menu');

		 }


	  }		

   ?>
