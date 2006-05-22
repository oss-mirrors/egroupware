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

   include_once(PHPGW_INCLUDE_ROOT.'/jinn/inc/class.uijinn.inc.php');

   /**
   * uiu_export 
   * 
   * @uses uijinn
   * @package 
   * @version $Id$
   * @copyright Lingewoud B.V.
   * @author Pim Snel <pim-AT-lingewoud-DOT-nl> 
   * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   */
   class uiu_export extends uijinn
   {
	  var $public_functions = Array
	  (
		 'export'		=> True,
		 'post'		    => True
	  );
	  var $filter;
	  var $profilestore;

	  /**
	  * uiu_export 
	  * 
	  * @access public
	  * @return void
	  */
	  function uiu_export()
	  {
		 $this->bo = CreateObject('jinn.bouser');
		 parent::uijinn();

		 //fixme betere structuur
		 $this->filter = CreateObject('jinn.uiu_filter');
		 $this->filter->init_bo(&$this->bo);
		 $this->profilestore = $this->bo->read_preferences('profilestore'.$this->bo->site_object[unique_id]);
	  }

	  /**
	  * save_profilestore 
	  * 
	  * @access public
	  * @return void
	  */
	  function save_profilestore()
	  {
		 $this->bo->save_preferences('profilestore'.$this->bo->site_object[unique_id], $this->profilestore); 
	  }

	  /**
	  * load_factory_preset 
	  * 
	  * @param mixed $preset 
	  * @access public
	  * @return void
	  */
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

	  /**
	  * load_profile 
	  * 
	  * @access public
	  * @return void
	  */
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

	  /**
	  * save_profile 
	  * 
	  * @access public
	  * @return void
	  */
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

	  function fputcsv3($filePointer, $dataArray, $delimiter, $enclosure){
		 // Write a line to a file
		 // $filePointer = the file resource to write to
		 // $dataArray = the data to write out
		 // $delimeter = the field separator

		 // Build the string
		 $string = "";
		 $writeDelimiter = FALSE;
		 foreach($dataArray as $dataElement){
			if($writeDelimiter) $string .= $delimiter;
			$string .= $enclosure . $dataElement . $enclosure;
			$writeDelimiter = TRUE;
		 } // end foreach($dataArray as $dataElement)

		 // Append new line
		 $string .= "\n";

		 // Write the string to the file
		 fwrite($filePointer, $string);

	  } // end function fputcsv($filePointer, $dataArray, $delimiter)

	  function fputcsv2($filePointer,$dataArray,$delimiter,$enclosure)
	  {
		 // Write a line to a file
		 // $filePointer = the file resource to write to
		 // $dataArray = the data to write out
		 // $delimeter = the field separator

		 // Build the string
		 $string = "";

		 // No leading delimiter
		 $writeDelimiter = FALSE;
		 foreach($dataArray as $dataElement)
		 {
			// Replaces a double quote with two double quotes
			$dataElement=str_replace("\"", "\"\"", $dataElement);

			// Adds a delimiter before each field (except the first)
			if($writeDelimiter) $string .= $delimiter;

			// Encloses each field with $enclosure and adds it to the string
			$string .= $enclosure . $dataElement . $enclosure;

			// Delimiters are used every time except the first.
			$writeDelimiter = TRUE;
		 } // end foreach($dataArray as $dataElement)

		 // Append new line
		 $string .= "\n";

		 // Write the string to the file
		 fwrite($filePointer,$string);
	  }

	  function fputcsv($filePointer,$dataArray,$delimiter=",",$enclosure="\""){
		 // Write a line to a file
		 // $filePointer = the file resource to write to
		 // $dataArray = the data to write out
		 // $delimeter = the field separator

		 // Build the string
		 $string = "";

		 // for each array element, which represents a line in the csv file...
		 foreach($dataArray as $line){

			// No leading delimiter
			$writeDelimiter = FALSE;

			foreach($line as $dataElement){ 
			   // Replaces a double quote with two double quotes
			   $dataElement=str_replace("\"", "\"\"", $dataElement);

			   // Adds a delimiter before each field (except the first)
			   if($writeDelimiter) $string .= $delimiter;

			   // Encloses each field with $enclosure and adds it to the string
			   $string .= $enclosure . $dataElement . $enclosure;

			   // Delimiters are used every time except the first.
			   $writeDelimiter = TRUE;
			}
			// Append new line
			$string .= "\n";

		 } // end foreach($dataArray as $line)

		 // Write the string to the file
		 fwrite($filePointer,$string);
	  }


	  /**
	  * get_csv_field 
	  * 
	  * @param mixed $fw 
	  * @param mixed $ft 
	  * @param mixed $rt 
	  * @param mixed $ec 
	  * @param mixed $value 
	  * @access public
	  * @return void
	  */
	  function get_csv_field($fw, $ft, $rt, $ec, $value)
	  {
		 $value = str_replace($fw, $ec.$fw, $value);
		 $value = str_replace($rt, $ec.$rt, $value);
		 return($fw.$value.$fw);
	  }

	  /**
	  * do_csv 
	  * 
	  * @access public
	  * @return void
	  */
	  function do_csv()
	  {
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
			$columns_arr = $this->bo->filter_array_with_prefix($_POST,'col_');
			break;
			default:
			$columns = $this->bo->so->site_table_metadata($this->bo->session['site_id'], $this->bo->site_object['table_name']);
			if(is_array($columns))
			{
			   $columns_arr = array();
			   foreach($columns as $column)
			   {
				  if($this->bo->field_is_enabled($this->bo->site_object[object_id], $column[name]))
				  {
					 $columns_arr[] = $column[name];
				  }
			   }
			}
			break;
		 }


		 //get data
		 $data = $this->bo->get_data($columns_arr, $filter_where);



		 // replace all unvalid characters
		 $filename=ereg_replace(' ','_',$this->bo->site_object['name']).'.csv';
		 $filename=ereg_replace('\?','',$this->bo->site_object['name']).'.csv';

		 header("Content-Type: text/comma-separated-values");
		 header("Content-Disposition:attachment; filename=$filename");
		 header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); // fix me kan nooit goed zijn
		 header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );

		 $IE=true;
		 //do IE check
		 if($IE)
		 {
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		 }
		 else
		 {
			header( "Pragma: no-cache" );
		 }


		 #header( "Cache-Control: no-cache, must-revalidate" );

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
			$ii=0;
			foreach($data[0] as $colname => $val)
			{
			   $ii++;
			   echo($this->get_csv_field($fw, $ft, $rt, $ec, $colname));
			   if($ii<count($data[0]))
			   {
				  echo $ft;
			   }
			}
			echo($rt);
		 }

		 //now for the data rows
		 reset($data);
		 foreach($data as $row)
		 {
			//EVENT ON EXPORT	
			while(list($key, $val) = each($row)) 
			{
			   $_row['FLDXXX'.$key]=$val; 
			}
			$status[eventstatus] = $this->bo->run_event_plugins('on_export', $_row);
			//END EVENT CODE

			$ii=0;
			foreach($row as $value)
			{
			   $ii++;
			   echo($this->get_csv_field($fw, $ft, $rt, $ec, stripslashes($value)));
			   if($ii<count($data[0]))
			   {
				  echo $ft;
			   }
	 }
			echo($rt);
		 }
	  }

	  /**
	  * post: public function. Dispatches what to do after the post
	  * 
	  * @access public
	  * @return void
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
	  * export: public function to generate the GUI for exporting object data to a CSV file 
	  * 
	  * @access public
	  * @return void
	  */
	  function export()
	  {
		 $this->msg_box();

		 $this->header('export object data');

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

		 $this->tplsav2->set_var('title',lang('Exporteer Object'));
		 $this->tplsav2->set_var('objectname',$this->bo->site_object['name']);

		 $this->tplsav2->set_var('action',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_export.post'));

		 $this->tplsav2->set_var('export',lang('export source'));
		 $this->tplsav2->set_var('source_1_label',lang('filtered list'));
		 $this->tplsav2->set_var('source_2_label',lang('all records (unfiltered)'));
		 $this->tplsav2->set_var('source_3_label',lang('selected records'));

		 $this->tplsav2->set_var('source_1_checked','');
		 $this->tplsav2->set_var('source_2_checked','');
		 $this->tplsav2->set_var('source_3_checked','');

		 $this->tplsav2->set_var('source_1_disabled','');
		 $this->tplsav2->set_var('source_2_disabled','');
		 if(is_array($this->bo->session['mult_where_array']))
		 {
			$this->tplsav2->set_var('source_3_disabled','');
		 }
		 else
		 {
			$this->tplsav2->set_var('source_3_disabled','disabled');
		 }

		 switch($_POST[source])
		 {
			case 'filtered':
			$this->tplsav2->set_var('source_1_checked','checked');
			break;
			case 'unfiltered':
			$this->tplsav2->set_var('source_2_checked','checked');
			break;
			case 'selected':
			$this->tplsav2->set_var('source_3_checked','checked');
			break;				
		 }

		 $this->tplsav2->set_var('submit',lang('submit'));
		 $this->tplsav2->set_var('cancel',lang('cancel'));

		 $this->tplsav2->set_var('field_names_row_label',lang('first row has field names'));
		 if($_POST[field_names_row] == 'true')
		 {
			$this->tplsav2->set_var('field_names_row_checked','checked');
		 }
		 else
		 {
			$this->tplsav2->set_var('field_names_row_checked','');
		 }

		 $this->tplsav2->set_var('field_terminator_label',lang('fields terminated by'));
		 $this->tplsav2->set_var('field_terminator',htmlspecialchars($_POST[field_terminator]));

		 $this->tplsav2->set_var('field_wrapper_label',lang('fields enclosed by'));
		 $this->tplsav2->set_var('field_wrapper',htmlspecialchars($_POST[field_wrapper]));

		 $this->tplsav2->set_var('escape_character_label',lang('fields escaped by'));
		 $this->tplsav2->set_var('escape_character',htmlspecialchars($_POST[escape_character]));

		 $this->tplsav2->set_var('row_terminator_label',lang('lines terminated by'));
		 $this->tplsav2->set_var('row_terminator',htmlspecialchars($_POST[row_terminator]));

		 $this->tplsav2->set_var('all_columns_label',lang('export all columns'));
		 $this->tplsav2->set_var('select_columns_label',lang('export selection below'));


		 $this->tplsav2->set_var('all_checked','');
		 $this->tplsav2->set_var('select_checked','');

		 switch($_POST[columns])
		 {
			case 'all':
			$this->tplsav2->set_var('all_checked','checked');
			break;
			case 'select':
			$this->tplsav2->set_var('select_checked','checked');
			break;
		 }

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
				  $col_arr['checked']='checked';
			   }
			   else
			   {
				  $col_arr['checked']='';
			   }
			   $col_arr['column_label']=$field[name];
			   $col_arr['column']=$field[name];
			   
			   $cols_arr[]=$col_arr;
			}
		 }
		 $this->tplsav2->cols_arr=$cols_arr;

		 /////////////////////////
		 //process the second block
		 /////////////////////////

		 $this->tplsav2->set_var('load_profile_label',lang('load export profile'));
		 $this->tplsav2->set_var('save_profile_label',lang('save export profile'));
		 $this->tplsav2->set_var('profiles',$this->getProfiles());
		 $this->tplsav2->set_var('save_profile',$_POST[save_profile]);
		 $this->tplsav2->set_var('save_as',lang('save profile'));

		 $this->tplsav2->display('frm_export_object_data.tpl.php');

		 $this->bo->sessionmanager->save();

	  }

	  function export_old()
	  {
		 $this->msg_box();

		 $this->header('export object data');

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


		 
		 $this->tplsav2->display('frm_export_object_data.tpl.php');



		 $this->bo->sessionmanager->save();


	  }

	  /**
	  * formatOption 
	  * 
	  * @param mixed $value 
	  * @param mixed $display 
	  * @param mixed $profile 
	  * @access public
	  * @return void
	  */
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

	  /**
	  * getProfiles 
	  * 
	  * @access public
	  * @return void
	  */
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
