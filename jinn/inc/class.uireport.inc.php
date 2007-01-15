<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
   Copyright (C)2002, 2007 Pim Snel <pim@lingewoud.nl>
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

   include_once(PHPGW_INCLUDE_ROOT.'/jinn/inc/class.uijinn.inc.php');

   /**
    * uireport 
    * 
    * @uses uijinn
    * @package 
    * @version $Id$
    * @copyright Lingewoud B.V.
	* @author Rob van Kraanen <rob-AT-lingewoud-DOT-nl> 
	* @author Pim Snel <pim-AT-lingewoud-DOT-nl> 
    * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
    */
   class uireport extends uijinn
   {
	  var $public_functions = Array
	  (
		 'add_report_popup'			=>True,
		 'edit_report_popup' 		=>True,
		 'delete_report_popup'		=>True,
		 'merge_report'				=>True,
		 'merge'				=>True,
		 'show_merged_report'		=>True,
		 'save_merged_report'		=>True,
		 'print_merged_report'		=>True,
		 'add_report_user'			=>True,
		 'list_reports'				=>True,
		 'addedit_report_ng'		=>True,
		 'add_report_from_selected'	=>True
	  );

	  /**
	   * uireport: class contructor that set header and inits bo
	   * 
	   * @access public
	   * @return void
	   */
	  function uireport($session_name='jinnitself')
	  {
		 $this->bo = CreateObject('jinn.boreport',$session_name);
		 parent::uijinn();
	  }

	  function extra_plugin_config($report_type_name,$fld_plug_conf_arr)
	  {
		 $plug_reg_conf_arr = $this->bo->registry->report_plugins[$report_type_name]['config'];
		 $_fld_plug_conf_arr['conf']=$fld_plug_conf_arr;

		 if(is_array($plug_reg_conf_arr))
		 {
			$temp ='';
			$configuration_widget= CreateObject('jinn.plg_conf_widget');
			foreach($plug_reg_conf_arr as $cval)
			{
			   $temp .= $configuration_widget->display_plugin_widget($cval['type'],$this->tplsav2, $cval,$_fld_plug_conf_arr);
			}
			return $temp;
		 }
	  }


	  /**
	   * create_report_ng: new report editor screen 
	   * 
	   * @access public
	   * @return void
	   */
	  function addedit_report_ng($action)
	  {
		 if(!is_null($_POST['reportsubmit']))
		 {
			$this->bo->save_report($_POST['report_id']);
		 }
		 
		 switch($action)
		 {
			case 'newsite':
			$this->tplsav2->assign('title',lang('JiNN - New Report'));	
			break;
			case 'newuser':
			$this->tplsav2->assign('title',lang('JiNN - New Private Report'));	
			break;
			case 'newcopy':
			$this->tplsav2->assign('title',lang('Design Report - New from Copy'));	
			$val = $this->bo->get_single_report($_GET['report_id']);
			$val['report_id'] = 0;
			$val['report_name'] =$val['report_name'].'_copy';
			break;
			case 'edit':
			$this->tplsav2->assign('title',lang('Design Report - Edit'));	
			$val = $this->bo->get_single_report($_GET['report_id']);
			break;
		 }

		 //if not get of save type first ask to set a report type
		 if(is_null($_POST['report_type_name']) && empty($val['report_type_name']))
		 {
			$this->ask_report_type();
		 }
		 else
		 {
			$this->design_report_form($val);	
		 }
	  }

	  function ask_report_type()
	  {
		 $this->header('Design Report - Choose Type');
		 $this->msg_box();

		 //TODO get available types

		 $this->tplsav2->assign('returnlink',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_list_records.display'));
		 $this->tplsav2->display('choosereporttype.tpl.php');
	  }

	  /**
	   * design_report_form 
	   * 
	   * @param mixed $val 
	   * @access public
	   * @return void
	   */
	  function design_report_form($val)
	  {
		 //make plugin object

		 $report_type_name =($val['report_type_name']?$val['report_type_name']:$_POST['report_type_name']);

		 $report_type_object = $this->bo->createtypeobject($report_type_name);
		 $report_type_confdata=unserialize($val['report_type_confdata']);
		 $extra_config = $this->extra_plugin_config($report_type_name,$report_type_confdata);
		 
		 $fields=$this->bo->so->site_table_metadata($_GET['parent_site_id'], $_GET['table_name']);
		 foreach($fields as $field)
		 {
			$attrib.= '<option value="'.$field['name'].'">'.$field['name'].'</option>';
		 }	 

		 // get and render other config options from plugin
		 
		 // TODO also allow import content from uploaded text file
		 
		 $report_insertfield_js=$report_type_object->insertfield_javascript();
		 $report_header=$report_type_object->report_header_input($val['report_header']);
		 $report_body=$report_type_object->report_body_input($val['report_body']);
		 $report_footer=$report_type_object->report_footer_input($val['report_footer']);

		 $this->tplsav2->assign('extra_config', $extra_config);
		 $this->tplsav2->assign('report_insertfield_js', $report_insertfield_js);
		 $this->tplsav2->assign('report_header', $report_header);
		 $this->tplsav2->assign('report_body', $report_body);
		 $this->tplsav2->assign('report_footer', $report_footer);

		 /*****/

		 $this->tplsav2->assign('obj_id', $_GET['obj_id']);

		 $this->tplsav2->assign('attibutes',$attrib);
		 $this->tplsav2->assign('val',$val);
		 $this->tplsav2->assign('report_type_name',$report_type_name);
		 $this->tplsav2->assign('returnlink',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_list_records.display'));

		 /* Render */

		 $this->header($this->tplsav2->title);
		 $this->msg_box();

		 $this->tplsav2->display('pop_add_report.tpl.php');
	  }

	  /**
	   * add_report_popup 
	   * 
	   * @access public
	   * @return void
	   */
	  function add_report_popup()
	  {
		 $this->addedit_report_ng('newsite');
		 exit;
	  }
	  
	  /**
	   * add_report_user 
	   *
	   * Shows the pop-up for adding data, with the preferences set on the user-specific templates
	   * 
	   * @access public
	   * @return void
	   */
	  function add_report_user()
	  {
		 $this->addedit_report_ng('newuser');
		 exit;
	  }
 
	  /**
	   * add_report_from_selected 
	   *
	   * sets the variables to make a new erport, but with the excisting data will be used for this report
	   *
	   * @access public
	   * @return void
	   */
	  function add_report_from_selected()
	  {
		 $this->addedit_report_ng('newcopy');
		 exit;
	  }

	  /**
	  * edit_report_popup 
	  *
	  * This function shows the popup to edit a saved record 
	  *
	  * @access public
	  * @return void
	  */
	  function edit_report_popup()
	  {
		 $this->addedit_report_ng('edit');
		 exit;
	  }

	  function merge()
	  {
		 if($_GET['dest']=='save')
		 {
			$this->save_merged_report();
			exit;
		 }
		 elseif($_GET['dest']=='screen')
		 {
		 	$this->show_merged_report();
			exit;
		 }
		 $this->tplsav2->assign('sel_val',$_GET['selvalues']);
		 $this->tplsav2->assign('obj_id', $_GET['obj_id']);
		 $this->tplsav2->assign('report_id', $_GET['report_id']);
		 $this->tplsav2->assign('sel_values',$_GET['selvalues']);

		 $this->tplsav2->assign('returnlink',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_list_records.display'));

		 $this->header('Reports - Merge');
		 $this->msg_box();

		 $this->tplsav2->display('frm_merge_report_ng.tpl.php');
	  }
	 
	  /**
	   * merge_report 
	   * 
	   * This funciton shows the popup witch will show the merged report
	   *
	   * @access public
	   * @return void
	   */
	  function merge_report()
	  {
		 $GLOBALS['phpgw_info']['flags']['nonavbar']=True;
		 $GLOBALS['phpgw_info']['flags']['noappheader']=True;
		 $GLOBALS['phpgw_info']['flags']['noappfooter']=True;
		 $GLOBALS['egw']->common->phpgw_header();
		 $this->tplsav2->assign('sel_val',$_GET['selvalues']);
		 $this->tplsav2->assign('obj_id', $_GET['obj_id']);
		 $this->tplsav2->assign('report_id', $_GET['report_id']);
		 $this->tplsav2->assign('sel_values',$_GET['selvalues']);
		 $this->tplsav2->display('frm_merge_report.tpl.php');
	  }

	  
	  /**
	  * show_merged_report 
	  * 
	  * This function parses the template, with depending on the setting the selected records, or the files from theapplied filter, 
	  * or just all records.
	  * It inserts the right php-code
	  * It replaces the %%var%% with the php-code
	  *
	  * @param string $bodytags 
	  * @access public
	  * @return void
	  */
	  function show_merged_report( $bodytags = '')
	  {
		 if(!$_GET['selection'])
		 {
			return;
		 }
		 if($_GET['selection'] == 'firstrec')
		 {
			$records=$this->bo->get_records($this->bo->site_object['table_name'],'','',0,1,'name',$orderby,'*',$where_condition);
		 }
		 elseif($_GET['selection'] == 'all')
		 {
			$records=$this->bo->get_records($this->bo->site_object['table_name'],'','',0,0,'name',$orderby,'*',$where_condition);
		 }
		 
		 if($_GET['selection'] == 'selection')
		 {
			$sel_arr=split(',',$_GET['sel_values']);	
			foreach($sel_arr as $sel)
			{
			   if($sel)
			   {
			   	$arr2[]=base64_decode($sel);
			   }
			}
			if(trim($where_condition) !='')
			{
			   $where_condition .=implode($arr2,' OR ');
			}
			else
			{
			   $where_condition = implode($arr2,' OR ');
			}
			  
			$records=$this->bo->get_records($this->bo->site_object['table_name'],'','',0,0,'name',$orderby,'*',$where_condition);
		 }
		 
		 if($_GET['selection'] == 'filtered')
		 {
		 	$this->filtermanager = CreateObject('jinn.uiu_filter');
		 	//$this->filtermanager->init_bo(&$this->bo);
		 	$filter_where = $this->filtermanager->get_filter_where();
			if(trim($where_condition) !='')
			{
			   $where_condition .=$filter_where;
			}
			else
			{
			   $where_condition = $filter_where;
			}

			$records=$this->bo->get_records($this->bo->site_object['table_name'],'','',0,0,'name',$orderby,'*',$where_condition);
		 }
		 
		 $report_arr = $this->bo->get_single_report($_GET['report_id']);
		 $report_type_object = $this->bo->createtypeobject($report_arr['report_type_name']);
		 $report_type_confdata=unserialize($report_arr['report_type_confdata']);

		 $output= $report_type_object->pre_show_merged_report($records,$report_arr);
		 $output.= $this->bo->parse_records_through_header_source($records,$report_arr);
		 $output.= $this->bo->parse_records_through_body_source($records,$report_arr);
		 $output.= $this->bo->parse_records_through_footer_source($records,$report_arr);
		 
		 $report_type_object->show_merged_report($records,$report_arr,$output);

		 //echo $output;
	  }
	  
	  /**
	   * save_merged_report 
	   *
	   * This functions runs the same merge script, and the headers for the save option will be set
	   *
	   * @access public
	   * @return void
	   */
	  function save_merged_report()
	  {
		 $id = $_GET['report_id'];
		 $link = $GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uireport.show_merged_report').'&report_id='.$_GET['report_id'];

		 $report_arr = $this->bo->get_single_report($_GET['report_id']);
		 $report_type_object = $this->bo->createtypeobject($report_arr['report_type_name']);

		 $report_type_object->send_save_headers($report_arr);

		 $this->show_merged_report();
	  }
	
  	  /**
  	   * print_merged_report 
	   *
  	   * This functions runs the same merge script, but now with the script to print
	   *
  	   * @access public
  	   * @return void
  	   */
  	  function print_merged_report()
	  {
		 $this->show_merged_report('onload="javascript:window.print()"');
	  }
   }
?>
