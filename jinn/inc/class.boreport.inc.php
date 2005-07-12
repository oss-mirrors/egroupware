<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
   Authors: Pim Snel <pim@lingewoud.nl>, 
			Lex Vogelaar <lex_vogelaar@users.sourceforge.net>
   Copyright (C)2002, 2003, 2004, 2005 Pim Snel <pim@lingewoud.nl>

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

   /* $Id$ */
   class boreport
   {
	  var $public_functions = Array(
		 'save_report' 			=> True,
		 'get_report_list' 		=> True,
		 'get_single_report' 	=> True,
		 'update_single_report' => True,
		 'delete_report'		=>True,
		);

	  var $so;
	  var $session;
	  var $sessionmanager;

	  var $site_object; 
	  var $site; 
	  var $local_bo;
	  var $magick;

	  var $current_config;
	  var $action;
	  var $common;

	  var $where_key;
	  var $where_value;

	  var $plug;
	  var $plugins;
	  var $object_events_plugin_manager;
	  var $object_events_plugins;

	  var $db_ftypes;

	  function boreport()
	  {
		 $this->common = CreateObject('jinn.bocommon');
		 $this->session 		= &$this->common->session->sessionarray;	//shortcut to session array
		 $this->sessionmanager	= &$this->common->session;					//shortcut to session manager object
		 
		 $this->current_config=$this->common->get_config();		

		 $this->so = CreateObject('jinn.sojinn');

		 $this->use_session = True; //fixme: what does this do?
		
		

		 // get array of site and object
		 $this->site = $this->so->get_site_values($this->session['site_id']);

		 if ($this->session['site_object_id'])
		 {
			$this->site_object = $this->so->get_object_values($this->session['site_object_id']);
		 }
		 
		 $this->plug = CreateObject('jinn.factory_plugins_db_fields'); 
		 $this->plug->local_bo = $this;
		 $this->plugins = $this->plug->plugins; //fixme: THIS WILL BREAK WHEN WE GET RID OF THE OLD STYLE PLUGINS


		 $this->object_events_plugin_manager = CreateObject('jinn.factory_plugins_object_events'); 
		 $this->object_events_plugin_manager->local_bo = $this;
		 $this->object_events_plugins = $this->object_events_plugin_manager->object_events_plugins;

		 $this->db_ftypes = CreateObject('jinn.dbfieldtypes');

		 global $local_bo;
		 $local_bo=$this;
	  }
	  
	  function read_preferences($key)
	  {
		 $GLOBALS['phpgw']->preferences->read_repository();

		 $prefs = array();

		 if ($GLOBALS['phpgw_info']['user']['preferences']['jinn'])
		 {
			$prefs = $GLOBALS['phpgw_info']['user']['preferences']['jinn'][$key];
		 }
		 return $prefs;
	  }

	  function save_preferences($key,$prefs)
	  {
		 $GLOBALS['phpgw']->preferences->read_repository();

		 $GLOBALS['phpgw']->preferences->change('jinn',$key,$prefs);
		 $GLOBALS['phpgw']->preferences->save_repository(True);
	  }
	  /*
	  This functions get's the values passed to it by the form and depending on tje preference-value saves it to the database or in the
	  preferences of the user
	  */
	  function save_report()
	  {	
		 if($_GET[preference] ==0)
		 {
		 	if($this->so->insert_report($_POST[name],$_POST[obj_id] ,$_POST[text1] ,$_POST[text2] ,$_POST[text3],$_POST[r_html_title],$_POST[g_html]) ==1)
		 	{
				echo(lang('Report saved succesfull').'<br><br><input type="button" onClick="self.close()" value="'.lang('close').'"/>');
			 }
			 else
			 {
				echo(lang('Report NOT saved succesfull').'<br><br><input type="button" onClick="self.close()" value="'.lang('close').'"/>');
			 }
		  }
		  else
		  {
			 $key = 'reports';
			 $pref_arr2 = $this->read_preferences($key);

			 $pref_arr['report_id'] = 'user_'.(count($pref_arr2)+1);
			 $pref_arr['report_naam'] = $_POST[name];
			 $pref_arr['report_object_id'] = $_POST[obj_id];
			 $pref_arr['report_header'] = $_POST[text1];
			 $pref_arr['report_body'] = $_POST[text2];
			 $pref_arr['report_footer'] = $_POST[text3];
			 if($_POST[g_html] == 'on')
			 {
				$pref_arr['report_html']	= 1;
			 }
			 else
			 {
				$pref_arr['report_html']= 0;
			 }
			 $pref_arr['report_html_title']= $_POST[r_html_title];
			 			 
				 $pref_arr2[] = $pref_arr;
			 $this->save_preferences($key, $pref_arr2);
			 echo('Report saved succesfull<br><br><input type="button" onClick="self.close()" value="'.lang('close').'"/>');

		  }
		 
	   }
	   /*
	   This function return a list of reports, depending on the $all opties, 
	   it will return all(0), only the globals(1) or only the reports of the user(2) 
	   */
	  function get_report_list($id, $all=1)
	  {
		 //print_r($id);
		 $output='';
		 if ($all ==1 or $all ==2)
		 {
			$report_arr = $this->so->get_report_list($id);	
	 		if(is_array($report_arr))
		 	{
		 		foreach($report_arr as $report)
		 		{
					$output .='<option value=\''.$report[id].'\'>'.$report[name].'</option>';
				}
		 	}
		 }
		 if($all == 1)
		 {
			 $output .='<option>-------------------</option>';
		 }
		 if($all == 1 or $all == 3)
		 {
			$pref_arr = $this->read_preferences('reports');	
			$i=0;
			if(is_array($pref_arr))
			{
			   foreach($pref_arr as $pref)
			   {
				  if($pref[report_object_id] == $id)
				  $output .='<option value=\''.$pref[report_id].'\'>'.$pref[report_naam].'</option>';
				  $i++;
			   }
			}
	  	 }
		 return $output;
	  }
	  /*
	  This function saves one report, depending on the id to the JiNN-database or the preferences of the user
	  */
	  function get_single_report($id)
	  {
		 if(substr($id,0,4) != 'user')
		 {
			return($this->so->get_single_report($id));
		 }
		 else
		 {
			 $pref_arr = $this->read_preferences('reports');
			 foreach($pref_arr as $pref)
			 {
				if($pref[report_id] == $id)
				{
				   $arr[r_id] = $pref[report_id];
				   $arr[r_name] = $pref[report_naam];
				   $arr[r_obj_id] = $pref[report_obj_id];
				   $arr[r_header] = $pref[report_header]; 		 
				   $arr[r_footer] = $pref[report_footer];
				   $arr[r_body] =$pref[report_body];
				   $arr[r_html] = $pref[report_html];
				   $arr[r_html_title] = $pref[report_html_title];
				
				}
			 }

			 return $arr;
		 }
	  }
	  
	  /*
	  This function updates one report, depending on the id to the JiNN-database or the preferences of the user
	  */
  	  function update_single_report()
	  {
		 if(substr($_POST['report_id'],0,4) != 'user')
		 {
		 	if($this->so->update_report($_POST[name], $_POST[obj_id], $_POST[text1], $_POST[text2], $_POST[text3],$_POST[r_html_title],$_POST[g_html], $_POST['report_id']))
		 	{
				echo('Report updated succesfull<br><br><input type="button" onClick="self.close()" value="'.lang('close').'"/>');
		 	}
		 	else
		 	{
				echo('Report NOT updated succesfull<br><br><input type="button" onClick="self.close()" value="'.lang('close').'"/>');
			 }
		  }
		  else
		  {
			 $pref_arr = $this->read_preferences('reports');
			 $i=0;
			 foreach($pref_arr as $pref)
			 {
			 	if(trim($pref[report_id]) == trim($_POST['report_id']))
			 	{
				   $pref[report_id] = $_POST['report_id'];
				   $pref[report_name]= $_POST[name];
				   $pref[report_obj_id] = $_POST[obj_id];
				   $pref[report_header] = $_POST[text1];
				   $pref[report_footer] = $_POST[text3];
				   $pref[report_body] = $_POST[text2];
				   $pref[report_html] = $_POST[g_html];
				   $pref[report_html_title] = $_POST[r_html_title];
				}
				$pref_arr[$i]= $pref;
				$i++;
			 }
			 $this->save_preferences('reports',$pref_arr);
			 echo('Report updated succesfull<br><br><input type="button" onClick="self.close()" value="'.lang('close').'"/>');
		  }
	  } 

	  /*
	  This function deletes one report from the JiNN-database
	  */
	  function delete_report()
	  {
		 $this->so->delete_report($_GET[report_id]);
		 header('location:jinn/index.php?menuaction=jinn.uiadmin.edit_this_jinn_site_object');
	  }
	 
	  /*
	  This function replaces the own php-tags from the template with the normal php-tags and makes it a valid php-file
	  */
	  function replace_tiny_php_tags($input)
	  {
		if(!function_exists('replace_php'))
		{
		   function replace_php($text)
		   {
			  $text =  preg_replace('/%%(.*?)%%/',"\$this->record['$1']",$text);
			  $x= "<?"."php "; 
			  $x .= strip_tags(str_replace('&nbsp;','',str_replace('\\','',$text)));
			  $x.= "?>";
		   return $x;
		   }
		}
		
		$output = preg_replace("#\[OPENPHP\](.*?)\[CLOSEPHP\]#ise", 'replace_php(\'$1\')', $input);
		$output = preg_replace('/&lt;/','<',$output);	
		$output = preg_replace('/&gt;/','>',$output);
		return $output;
	 }

	 }
   ?>
