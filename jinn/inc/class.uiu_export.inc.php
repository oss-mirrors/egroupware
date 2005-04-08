<?php
   /*
   JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
   
   Authors Lex Vogelaar, Pim Snel
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

   /* $Id$ */

   /**
   @package jinn_users_classes
   */
   class uiu_export
   {
	  var $public_functions = Array
	  (
		 'export'		=> True,
		 'post'		    => True
	  );
	  var $bo;
	  var $template;
	  var $ui;
	  var $filter;
	  var $profilestore;

	  /**
	  @function uiu_filter
	  @abstract class contructor that set header and inits bo
	  */
	  function uiu_export()
	  {
		 $this->bo = CreateObject('jinn.bouser');
		 $this->template = $GLOBALS['phpgw']->template;
		 $this->ui = CreateObject('jinn.uicommon',$this->bo);
		 $this->filter = CreateObject('jinn.uiu_filter');
		 $this->filter->init_bo(&$this->bo);
		 $this->profilestore = $this->bo->read_preferences('profilestore'.$this->bo->site_object[unique_id]);
		 
		 if($this->bo->so->config[server_type]=='dev')
		 {
			$dev_title_string='<font color="red">'.lang('Development Server').'</font> ';
		 }
		 $this->ui->app_title=$dev_title_string;//.lang('Moderator Mode');
	  }

	  function save_profilestore()
	  {
		$this->bo->save_preferences('profilestore'.$this->bo->site_object[unique_id], $this->profilestore); 
	  }

  	  function load_factory_preset($preset)
	  {
		switch($preset)
		{
			case 'openoffice':
				$_POST[field_names_row] = 'true';
				$_POST[field_terminator] = ',';
				$_POST[field_wrapper] = '"';
				$_POST[escape_character] = '"';
				$_POST[row_terminator] = '\r\n';
				return true;
			case 'excel':
				$_POST[field_names_row] = 'true';
				$_POST[field_terminator] = ',';
				$_POST[field_wrapper] = '"';
				$_POST[escape_character] = '"';
				$_POST[row_terminator] = '\r\n';
				return true;
			default:
				return false;
		}
	  }
	  
	  function load_profile()
	  {
			//some post vars must be kept
		$source = $_POST[source];
		
		if($_POST[load_profile] == '')
		{
				//loading nothing means resetting the form
			$_POST[field_names_row] = '';
			$_POST[field_terminator] = '';
			$_POST[field_wrapper] = '';
			$_POST[escape_character] = '';
			$_POST[row_terminator] = '';
/*			foreach($_POST as $key => $value)
			{
				unset($_POST[$key]);
			}*/
		}
		else
		{
			if($this->load_factory_preset($_POST[load_profile], $columns))
			{
				//do nothing. a factory preset is available.
			}
			else
			{
					//load the selected profile by replacing the POST var with the selected one
				$profilename = $_POST[load_profile];
				$profile = $this->profilestore[$profilename];
				$_POST = $profile;
				$_POST[save_profile] = $profilename;
				$_POST[load_profile] = $profilename;
			}
		}
		
			//restore the kept post vars
		$_POST[source] = $source;
		
			//build the gui
		$this->export();
	  }
		
	  function save_profile()
	  {
			//compile the new profile
		$profile = $_POST;
		unset($profile[do_profile]);
		unset($profile[load_profile]);
		unset($profile[load]);
		unset($profile[save_profile]);
		unset($profile[source]);
			//add to the store and save
		$this->profilestore[$_POST[save_profile]] = $profile;
		$this->save_profilestore(); 
			//the profile selectbox must have this new profile selected
		$_POST[load_profile] = $_POST[save_profile];
			//build the gui
		$this->export();
	  }

	  function get_csv_field($fw, $ft, $rt, $ec, $value)
	  {
		$value = str_replace($fw, $ec.$fw, $value);
		$value = str_replace($rt, $ec.$rt, $value);
		return($fw.$value.$fw.$ft);
	  }
	  
	  function do_csv()
	  {
//_debug_array($this->bo->session['mult_where_array']);
		switch($_POST[source])
		{
			case 'filtered':
				$filter_where = $this->filter->get_filter_where();
				break;
			case 'unfiltered':
				$filter_where = 'all';
				break;
			case 'selected':
				if(is_array($this->bo->session['mult_where_array']))
				{
					$filter_where = '(';
					foreach($this->bo->session['mult_where_array'] as $filter)
					{
						if($filter_where!='(') $filter_where .= ' OR ';
						$filter_where .= "$filter";
					}
						$filter_where .= ')';
				}
				else
				{
					$filter_where = 'all';
				}
				break;
			default:
				$filter_where = 'all';
				break;
		}
		
		switch($_POST[columns])
		{
			case 'select':
				$columns_arr = $this->bo->common->filter_array_with_prefix($_POST,'col_');
				break;
			default:
				 $columns = $this->bo->so->site_table_metadata($this->bo->session['site_id'], $this->bo->site_object['table_name']);
				 if(is_array($columns))
				 {
					$columns_arr = array();
					foreach($columns as $column)
					{
						$columns_arr[] = $column[name];
					}
				 }
				break;
		}

//_debug_array($columns_arr);
//_debug_array($filter_where);
		
			//get data
		$data = $this->bo->get_data($columns_arr, $filter_where);
//_debug_array($data);

		header("Content-type: text");
		$filename=ereg_replace(' ','_',$this->bo->site_object['name']).'.csv';
		header("Content-Disposition:attachment; filename=$filename");
//echo("<pre>");
		//csv
		
		$rt = $_POST[row_terminator];
		$fw = $_POST[field_wrapper];
		$ft = $_POST[field_terminator];
		$ec = $_POST[escape_character];
		
			//todo: special case all other special character strings (\b etc..)
		switch($rt)
		{
			case '\n':
				$rt = "\n";
				break;
			case '\r\n':
				$rt = "\r\n";
				break;
		}
		switch($ft)
		{
			case '\t':
				$ft = "\t";
				break;
		}

		if($_POST[field_names_row] == 'true')
		{
			foreach($data[0] as $colname => $val)
			{
				echo($this->get_csv_field($fw, $ft, $rt, $ec, $colname));
			}
			echo($rt);
		}

			//now for the data rows
		reset($data);
		foreach($data as $row)
		{
			foreach($row as $value)
			{
				echo($this->get_csv_field($fw, $ft, $rt, $ec, $value));
			}
			echo($rt);
		}
//echo("</pre>");
	  }
	  
  	  /**
	  @function post
	  @abstract public function. Dispatches what to do after the post
	  */
	  function post()
	  {
		if($_POST[do_csv])
		{
			$this->do_csv();
		}
		elseif($_POST[do_profile])
		{
			if($_POST[load] == 'true')
			{
				$this->load_profile();
			}
			elseif($_POST[save_profile] != '')
			{
				$this->save_profile();
			}			
		}
		else
		{
			$this->export();	//fixme should I even be here?
		}
	  }

	  /**
	  @function export
	  @abstract public function to generate the GUI for exporting object data to a CSV file
	  */
	  function export()
	  {
		 $this->ui->msg_box($this->bo->session['message']);
		 unset($this->bo->session['message']);

		 $this->ui->header('export object data');
		 
		 $this->template->set_file(array('frm_export' => 'frm_export_object_data.tpl'));

 		 $this->template->set_block('frm_export','first_block','');
 		 $this->template->set_block('frm_export','columns','');
 		 $this->template->set_block('frm_export','second_block','');

			//if we are here for the first time, or if we have reset the profile form, set some default values
		 if($_POST[field_names_row] == '')
		 {
			$_POST[field_names_row] = 'true';
			$_POST[field_terminator] = ',';
			$_POST[field_wrapper] = '"';
			$_POST[escape_character] = '\\';
			$_POST[row_terminator] = '\n';
		 }

			//if we are here for the first time set the source default
		 if($_POST[source] == '')
		 {
			$_POST[columns] = 'all';
			if(is_array($this->bo->session['mult_where_array']))
			{
				$_POST[source] = 'selected';
			}
			else
			{
				$_POST[source] = 'filtered';
			}
		 }
		 
 		 /////////////////////////
		 //process the first block
		 /////////////////////////
		 
  		 $this->template->set_var('title',lang('Exporteer Object'));
  		 $this->template->set_var('objectname',$this->bo->site_object['name']);

		 $this->template->set_var('action',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_export.post'));
		 
  		 $this->template->set_var('export',lang('export source'));
  		 $this->template->set_var('source_1_label',lang('filtered list'));
  		 $this->template->set_var('source_2_label',lang('all records (unfiltered)'));
  		 $this->template->set_var('source_3_label',lang('selected records'));

		 $this->template->set_var('source_1_checked','');
		 $this->template->set_var('source_2_checked','');
		 $this->template->set_var('source_3_checked','');
		 
		 $this->template->set_var('source_1_disabled','');
		 $this->template->set_var('source_2_disabled','');
		 if(is_array($this->bo->session['mult_where_array']))
		 {
			$this->template->set_var('source_3_disabled','');
		 }
		 else
		 {
			$this->template->set_var('source_3_disabled','disabled');
		 }
		 
		 switch($_POST[source])
		 {
			case 'filtered':
				$this->template->set_var('source_1_checked','checked');
				break;
			case 'unfiltered':
				$this->template->set_var('source_2_checked','checked');
				break;
			case 'selected':
				$this->template->set_var('source_3_checked','checked');
				break;				
		 }

  		 $this->template->set_var('submit',lang('submit'));
  		 $this->template->set_var('cancel',lang('cancel'));

  		 $this->template->set_var('field_names_row_label',lang('first row has field names'));
		 if($_POST[field_names_row] == 'true')
		 {
			$this->template->set_var('field_names_row_checked','checked');
		 }
		 else
		 {
			$this->template->set_var('field_names_row_checked','');
		 }

  		 $this->template->set_var('field_terminator_label',lang('fields terminated by'));
  		 $this->template->set_var('field_terminator',htmlspecialchars($_POST[field_terminator]));

  		 $this->template->set_var('field_wrapper_label',lang('fields enclosed by'));
  		 $this->template->set_var('field_wrapper',htmlspecialchars($_POST[field_wrapper]));

  		 $this->template->set_var('escape_character_label',lang('fields escaped by'));
  		 $this->template->set_var('escape_character',htmlspecialchars($_POST[escape_character]));

  		 $this->template->set_var('row_terminator_label',lang('lines terminated by'));
  		 $this->template->set_var('row_terminator',htmlspecialchars($_POST[row_terminator]));
		 
  		 $this->template->set_var('all_columns_label',lang('export all columns'));
  		 $this->template->set_var('select_columns_label',lang('export selection below'));
		 
		 
  		 $this->template->set_var('all_checked','');
  		 $this->template->set_var('select_checked','');
		 switch($_POST[columns])
		 {
			case 'all':
				$this->template->set_var('all_checked','checked');
				break;
			case 'select':
				$this->template->set_var('select_checked','checked');
				break;
		 }

  		 $this->template->parse('pre','first_block');	//parses the right argument block into the left argument variable ('fetch')
		 $this->template->pparse('out','pre'); 			//prints the right argument into the left argument buffer ('parse')

 		 /////////////////////////
		 //process the columns
		 /////////////////////////

		 $fields_arr=$this->bo->so->site_table_metadata($this->bo->session['site_id'], $this->bo->site_object['table_name']);
		 foreach($fields_arr as $field)
		 {
			if($this->bo->field_is_enabled($this->bo->site_object[object_id], $field[name]))
			{
				 if($_POST['col_'.$field[name]])
				 {
					$this->template->set_var('checked','checked');
				 }
				 else
				 {
					$this->template->set_var('checked','');
				 }
				 $this->template->set_var('column_label',$field[name]);
				 $this->template->set_var('column',$field[name]);
				 
				 $this->template->parse('column','columns');	//parses the right argument block into the left argument variable ('fetch')
				 $this->template->pparse('out','column'); 			//prints the right argument into the left argument buffer ('parse')
			}
		 }
		 
		 
 		 /////////////////////////
		 //process the second block
		 /////////////////////////
		 
  		 $this->template->set_var('load_profile_label',lang('load export profile'));
  		 $this->template->set_var('save_profile_label',lang('save export profile'));
  		 $this->template->set_var('profiles',$this->getProfiles());
  		 $this->template->set_var('save_profile',$_POST[save_profile]);
  		 $this->template->set_var('save_as',lang('save profile'));
		 
  		 $this->template->parse('post','second_block');	//parses the right argument block into the left argument variable ('fetch')
		 $this->template->pparse('out','post'); 			//prints the right argument into the left argument buffer ('parse')


		 
		 $this->bo->save_sessiondata();
		 
	  }
	  
	  function formatOption($value, $display, $profile)
	  {
		if($profile == $value)
		{
			return '<option value="'.$value.'" selected>'.$display.'</option>';
		}
		else
		{
			return '<option value="'.$value.'">'.$display.'</option>';
		}
	  }
	  
	  function getProfiles()
	  {
		$result='';
		$result .= '<option value=""></option>';
		$result .= $this->formatOption('openoffice', lang('preset: Open Office Spreadsheet'), $_POST[load_profile]);
		$result .= $this->formatOption('excel', lang('preset: MS Excel'), $_POST[load_profile]);
		
		if(is_array($this->profilestore))
		{
			foreach($this->profilestore as $name => $profile)
			{
				$result .= $this->formatOption($name, $name, $_POST[load_profile]);
			}
		}
		
		return $result;
	  }

   }

?>
