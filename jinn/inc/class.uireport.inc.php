<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
   Copyright (C)2002, 2003 Pim Snel <pim@lingewoud.nl>
   Copyright (C)2002, 2003 Rob van Kraanen <rob@lingewoud.nl>

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

   /**
   @package jinn_users_classes
   */
   class uireport
   {
	  var $public_functions = Array
	  (
		 'add_report_popup'			=>True,
		 'edit_report_popup' 		=>True,
		 'delete_report_popup'		=>True,
		 'merge_report'				=>True,
		 'show_merged_report'		=>True,
		 'save_merged_report'		=>True,
		 'print_merged_report'		=>True,
		 'add_report_user'			=>True,
		 'add_report_from_selected'	=>True
	  );
	  var $bo;
	  var $ui;
	  var $record_id_val;
	  var $report_id;
	  var $init_options;

	  /**
	  @function uiu_edit_record
	  @abstract class contructor that set header and inits bo
	  */

	  function uireport()
	  {
		 $this->bo = CreateObject('jinn.bouser');
		 $this->tplsav2 = CreateObject('phpgwapi.tplsavant2');
		 $this->boreport = CreateObject('jinn.boreport');
		 $this->ui = CreateObject('jinn.uicommon',$this->bo);
		 if($this->bo->so->config[server_type]=='dev')
		 {
			$dev_title_string='<font color="red">'.lang('Development Server').'</font> ';
		 }
		 $this->ui->app_title=$dev_title_string;//.lang('Moderator Mode');

		 $this->db_ftypes = CreateObject('jinn.dbfieldtypes');
		 $this->report_id ='';
		 $this->init_options = 'cleanup: "false"';
	  }

	  /*
	  Shows the popup to add reports to the JiNN tables
	  */
	  function add_report_popup()
	  {
		 $tplsav2 = CreateObject('phpgwapi.tplsavant2');
		 //	 $GLOBALS['phpgw_info']['flags']['noheader']=True;
		 $GLOBALS['phpgw_info']['flags']['nonavbar']=True;
		 $GLOBALS['phpgw_info']['flags']['noappheader']=True;
		 $GLOBALS['phpgw_info']['flags']['noappfooter']=True;
		 //		 $GLOBALS['phpgw_info']['flags']['nofooter']=True;
		 $fields=$this->boreport->so->site_table_metadata($_GET[parent_site_id], $_GET[table_name]);
		 foreach($fields as $field)
		 {
			$attrib.= '<option value="'.$field[name].'">'.$field[name].'</option>';
		 }	 
		 if (!is_object($GLOBALS['phpgw']->html))
		 {
			$GLOBALS['phpgw']->html = CreateObject('phpgwapi.html');
		 }
		 $text1=$GLOBALS['phpgw']->html->tinymce('text1','','',$this->init_options);
		 $text2=$GLOBALS['phpgw']->html->tinymce('text2','','',$this->init_options);
		 $text3=$GLOBALS['phpgw']->html->tinymce('text3','','',$this->init_options);
		 $GLOBALS['egw']->common->phpgw_header();
		 $tplsav2->assign('text1', $text1);
		 $tplsav2->assign('text2', $text2);
		 $tplsav2->assign('text3', $text3);
		 $tplsav2->assign('obj_id', $_GET[obj_id]);
		 $tplsav2->assign('css', $theme_css);
		 $tplsav2->assign('server_url',$GLOBALS['phpgw_info']['server']['webserver_url']);
		 $tplsav2->assign('title',lang('JiNN - Add Report'));	
		 $tplsav2->assign('form_action',$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.boreport.save_report').'&parent_site_id='.$_get['parent_site_id'].'&table_name='.$_get['table_name'].'&preference=0');
		 $tplsav2->assign('attibutes',$attrib);

		 $tplsav2->display('pop_add_report.tpl.php');
		 $GLOBALS['egw']->common->phpgw_footer();
	  }
	  /*
	  Shows the pop-up for adding data, with the preferences set on the user-specific templates
	  */
	  function add_report_user()
	  {
		 $tplsav2 = CreateObject('phpgwapi.tplsavant2');
		 //	 $GLOBALS['phpgw_info']['flags']['noheader']=True;
		 $GLOBALS['phpgw_info']['flags']['nonavbar']=True;
		 $GLOBALS['phpgw_info']['flags']['noappheader']=True;
		 $GLOBALS['phpgw_info']['flags']['noappfooter']=True;
		 //		 $GLOBALS['phpgw_info']['flags']['nofooter']=True;
		 $fields=$this->boreport->so->site_table_metadata($_GET[parent_site_id], $_GET[table_name]);
		 foreach($fields as $field)
		 {
			$attrib.= '<option value="'.$field[name].'">'.$field[name].'</option>';
		 }	 

		 if (!is_object($GLOBALS['phpgw']->html))
		 {
			$GLOBALS['phpgw']->html = CreateObject('phpgwapi.html');
		 }
		 $text1=$GLOBALS['phpgw']->html->tinymce('text1','','',$this->init_options);
		 $text2=$GLOBALS['phpgw']->html->tinymce('text2','','',$this->init_options);
		 $text3=$GLOBALS['phpgw']->html->tinymce('text3','','',$this->init_options);
		 $GLOBALS['egw']->common->phpgw_header();
		 $tplsav2->assign('text1', $text1);
		 $tplsav2->assign('text2', $text2);
		 $tplsav2->assign('text3', $text3);
		 $tplsav2->assign('obj_id', $_GET[obj_id]);
		 $tplsav2->assign('css', $theme_css);
		 $tplsav2->assign('server_url',$GLOBALS['phpgw_info']['server']['webserver_url']);
		 $tplsav2->assign('title',lang('JiNN - Add Report'));	
		 $tplsav2->assign('form_action',$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.boreport.save_report').'&parent_site_id='.$_get['parent_site_id'].'&table_name='.$_get['table_name'].'&preference=1');
		 $tplsav2->assign('attibutes',$attrib);

		 $tplsav2->display('pop_add_report.tpl.php');
		 $GLOBALS['egw']->common->phpgw_footer();

	  }
	  /*
	  sets the variables to make a new erport, but with the excisting data will be used for this report
	  */
	  function add_report_from_selected()
	  {
		 $tplsav2 = CreateObject('phpgwapi.tplsavant2');
		 $GLOBALS['phpgw_info']['flags']['nonavbar']=True;
		 $GLOBALS['phpgw_info']['flags']['noappheader']=True;
		 $GLOBALS['phpgw_info']['flags']['noappfooter']=True;
		 $fields=$this->bo->so->site_table_metadata($_GET[parent_site_id], $_GET[table_name]);
		 foreach($fields as $field)
		 {
			$attrib.= '<option value="'.$field[name].'">'.$field[name].'</option>';
		 }	 
		 if (!is_object($GLOBALS['phpgw']->html))
		 {
			$GLOBALS['phpgw']->html = CreateObject('phpgwapi.html');
		 }
		 $val = $this->boreport->get_single_report($_GET[report_id]);
		 $text1=$GLOBALS['phpgw']->html->tinymce('text1',trim($val[r_header]),'',$this->init_options);
		 $text2=$GLOBALS['phpgw']->html->tinymce('text2',trim($val[r_body]),'',$this->init_options);
		 $text3=$GLOBALS['phpgw']->html->tinymce('text3',trim($val[r_footer]),'',$this->init_options);
		 $GLOBALS['egw']->common->phpgw_header();	 
		 $tplsav2->assign('text1', $text1);
		 $tplsav2->assign('text2', $text2);
		 $tplsav2->assign('text3', $text3);
		 $tplsav2->assign('obj_id', $_GET[obj_id]);
		 $tplsav2->assign('title',lang('JiNN - Add Report from Selected'));	
		  $tplsav2->assign('form_action',$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.boreport.save_report').'&parent_site_id='.$_get['parent_site_id'].'&table_name='.$_get['table_name'].'&preference=1');
		  $tplsav2->assign('attibutes',$attrib);
		  $val[r_id] = 0;
		  $val[r_name] =$val[r_name].'_copy';
		 $tplsav2->assign('val',$val);
		 $tplsav2->display('pop_add_report.tpl.php');
	  }
	  /*
	  This function shows the popup to edit a saved record
	  */
	  function edit_report_popup()
	  {
		 $tplsav2 = CreateObject('phpgwapi.tplsavant2');
		 //	 $GLOBALS['phpgw_info']['flags']['noheader']=True;
		 $GLOBALS['phpgw_info']['flags']['nonavbar']=True;
		 $GLOBALS['phpgw_info']['flags']['noappheader']=True;
		 $GLOBALS['phpgw_info']['flags']['noappfooter']=True;
		 //	 $GLOBALS['phpgw_info']['flags']['nofooter']=True;
		 $fields=$this->bo->so->site_table_metadata($_GET[parent_site_id], $_GET[table_name]);
		 foreach($fields as $field)
		 {
			$attrib.= '<option value="'.$field[name].'">'.$field[name].'</option>';
		 }	 
		 if (!is_object($GLOBALS['phpgw']->html))
		 {
			$GLOBALS['phpgw']->html = CreateObject('phpgwapi.html');
		 }
		 $val = $this->boreport->get_single_report($_GET[report_id]);
		 $text1=$GLOBALS['phpgw']->html->tinymce('text1',trim($val[r_header]),'',$this->init_options);
		 $text2=$GLOBALS['phpgw']->html->tinymce('text2',trim($val[r_body]),'',$this->init_options);
		 $text3=$GLOBALS['phpgw']->html->tinymce('text3',trim($val[r_footer]),'',$this->init_options);
		 $GLOBALS['egw']->common->phpgw_header();	 
		 $tplsav2->assign('text1', $text1);
		 $tplsav2->assign('text2', $text2);
		 $tplsav2->assign('text3', $text3);
		 $tplsav2->assign('obj_id', $_GET[obj_id]);
		 $tplsav2->assign('title',lang('JiNN - Add Report'));	
		 $tplsav2->assign('form_action',$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.boreport.update_single_report').'&parent_site_id='.$_get['parent_site_id'].'&table_name='.$_get['table_name']);
		 $tplsav2->assign('attibutes',$attrib);
		 $tplsav2->assign('val',$val);
		 $tplsav2->display('pop_add_report.tpl.php');
	  }
	  /*
 	  This funciton shows the popup witch will show the merged report
	  */
	 
	  function merge_report()
	  {
		 $tplsav2 = CreateObject('phpgwapi.tplsavant2');
		 //	 $GLOBALS['phpgw_info']['flags']['noheader']=True;
		 $GLOBALS['phpgw_info']['flags']['nonavbar']=True;
		 $GLOBALS['phpgw_info']['flags']['noappheader']=True;
		 $GLOBALS['phpgw_info']['flags']['noappfooter']=True;
		 //		 $GLOBALS['phpgw_info']['flags']['nofooter']=True;
		 $GLOBALS['egw']->common->phpgw_header();
		 $this->tplsav2->assign('sel_val',$_GET[selvalues]);
		 $this->tplsav2->assign('obj_id', $_GET[obj_id]);
		 $this->tplsav2->assign('report_id', $_GET[report_id]);
		 $this->tplsav2->assign('sel_values',$_GET[selvalues]);
		 $this->tplsav2->display('frm_merge_report.tpl.php');
	  }
	  
	  /*
	  This function parses the template, with depending on the setting the selected records, or the files from theapplied filter, 
	  or just all records.
	  It inserts the right php-code
	  It replaces the %%var%% with the php-code
	  */				   
	  function show_merged_report( $bodytags = '')
	  {
		 if($_GET[selection] == 'all' or $_GET[selection] == '')
		 {
			$records=$this->bo->get_records($this->bo->site_object[table_name],'','',0,0,'name',$orderby,'*',$where_condition);
		 }
		 
		 if($_GET[selection] == 'selection')
		 {
			$sel_arr=split(',',$_GET[sel_values]);	
			foreach($sel_arr as $sel)
			{
			   if($sel)
			   {
			   	$arr2[]=base64_decode($sel);
			   }
			}
		//	print_r($arr2);
			if(trim($where_condition) !='')
			{
			   $where_condition .=implode($arr2,' OR ');
			}
			else
			{
			   $where_condition = implode($arr2,' OR ');
			}
			  
			$records=$this->bo->get_records($this->bo->site_object[table_name],'','',0,0,'name',$orderby,'*',$where_condition);
		 }
		 
		 if($_GET[selection] == 'filtered')
		 {
		 	$this->filtermanager = CreateObject('jinn.uiu_filter');
		 	$this->filtermanager->init_bo(&$this->bo);
		 	$filter_where = $this->filtermanager->get_filter_where();
			if(trim($where_condition) !='')
			{
			   $where_condition .=$filter_where;
			}
			else
			{
			   $where_condition = $filter_where;
			}
			$records=$this->bo->get_records($this->bo->site_object[table_name],'','',0,0,'name',$orderby,'*',$where_condition);
		 }
		 
		 $report_arr = $this->boreport->get_single_report($_GET[report_id]);
		 if($report_arr[r_html]==1)
		 {
			$output='
			<html>
			   <head>

				  <title>'.$report_arr[r_html_title].'</title>
			   </head>
			   <body '.$bodytags.'>
				  ';
			   }
			   else
			   {
				  $output ='';
			   }
			   $header=$report_arr[r_header];
			   $header = $this->boreport->replace_tiny_php_tags($header);
			   $header = preg_replace('/%%(.*?)%%/',"<?=\$this->record['$1'];?>",$header.'<br>');
			   $output .=  $this->tplsav2->fetch_string($header);  			 
			   foreach($records as $record)
			   {
				  $input = $report_arr[r_body].'<br>';
				  $this->tplsav2->assign('record',$record);
				  $input = $this->boreport->replace_tiny_php_tags($input);
				  $input = preg_replace('/%%(.*?)%%/',"<?=\$this->record['$1'];?>",$input);
				  $output .= $this->tplsav2->fetch_string($input);

			   }
			   $footer = $report_arr[r_footer];
			   $footer = $this->boreport->replace_tiny_php_tags($footer);
			   $footer = preg_replace('/%%(.*?)%%/',"<?=\$this->record['$1'];?>",$footer );

			   $output .=  $this->tplsav2->fetch_string($footer);
			   if($report_arr[r_html]==1)
			   {
				  $output.='
			   </body>
			</html>
			';
		 }
		 echo $output;
	  }
	  
	  /*
	  This functions runs the same merge script, and the headers for the save option will be set
	  */
	  function save_merged_report()
	  {
		 $id = $_GET[report_id];
		 $link = $GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uireport.show_merged_report').'&report_id='.$_GET[report_id];
		 header ("Content-Type: html; name=\"".$link."\"");
		 // ask for download
		 header ("Content-Disposition: attachment; filename=\"report.html");
		 header("Expires: 0");
		 // the next headers are for IE and SSL
		 header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		 header("Pragma: public");
		 $this->show_merged_report();
	  }
	
	  /*
	  This functions runs the same merge script, but now with the script to print
	  */
  	  function print_merged_report()
	  {
		 $this->show_merged_report('onload="javascript:window.print()"');
	  }
   }
?>
