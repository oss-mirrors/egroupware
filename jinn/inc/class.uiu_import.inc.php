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
   * uiu_import 
   * 
   * @uses uijinn
   * @package 
   * @version $Id$
   * @copyright Lingewoud B.V.
   * @author Pim Snel <pim-AT-lingewoud-DOT-nl> 
   * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   */
   class uiu_import extends uijinn
   {
	  var $public_functions = Array
	  (
		 'import'		=> True,
		 'post'		    => True
	  );
	  var $filter;
	  var $profilestore;

	  /**
	  * uiu_import 
	  * 
	  * @access public
	  * @return void
	  */
	  function uiu_import()
	  {
		 $this->bo = CreateObject('jinn.bouser');
		 parent::uijinn();

		 //fixme betere structuur
		 $this->filter = CreateObject('jinn.uiu_filter');
		 $this->filter->init_bo(&$this->bo);
		 $this->profilestore = $this->bo->read_preferences('importcsvprofilestore'.$this->bo->site_object[unique_id]);

		 /*
		 todo:
		 1. mapping preview
		 2. meer status en foutmeldingen
		 3. bewaar geuploade bestand met normale bestandsnaam
		 4. vertel hoeveel records geimporteerd zijn
		 5. doe wat met de seperators
		 6. on_import event
		 */
	  }

	  /**
	  * import: public function to generate the GUI for importing object data to a CSV file 
	  * 
	  * @access public
	  * @return void
	  */
	  function import()
	  {
		 // prevent ugly error
		 if(!$this->bo->site_object['object_id'])
		 {
			$this->bo->exit_and_open_screen($this->japielink.'jinn.uiuser.index');
		 }

		 //_debug_array($_POST);
		 $this->header(lang('Import CSV-file in %1',$this->bo->site_object['name']));
		 $this->msg_box();

		 //if we are here for the first time, or if we have reset the profile form, set some default values
		 if($_POST[field_terminator] == '') // FIXME strange test
		 {
			$_POST[field_names_row] = 'true';
			$_POST[field_terminator] = ',';
			$_POST[field_wrapper] = '"';
			$_POST[escape_character] = '\\';
			$_POST[row_terminator] = '\n';
		 }

		 $this->tplsav2->set_var('objectname',$this->bo->site_object['name']);
		 $this->tplsav2->set_var('action',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_import.post'));

		 if($_POST[field_names_row] == 'true')
		 {
			$this->tplsav2->set_var('field_names_row_checked','checked');
		 }
		 if($_POST[recreatetab] == 'true')
		 {
			$this->tplsav2->set_var('recreatetab','checked');
		 }
		 if($_POST[flushrecs] == 'true')
		 {
			$this->tplsav2->set_var('flushrecs','checked');
		 }

		 $this->tplsav2->set_var('field_terminator',htmlspecialchars($_POST[field_terminator]));
		 $this->tplsav2->set_var('field_wrapper',htmlspecialchars($_POST[field_wrapper]));
		 $this->tplsav2->set_var('escape_character',htmlspecialchars($_POST[escape_character]));
		 $this->tplsav2->set_var('row_terminator',htmlspecialchars($_POST[row_terminator]));

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

		 $this->tplsav2->set_var('profiles',$this->getProfiles());
		 $this->tplsav2->set_var('save_profile',$_POST[save_profile]);

		 $this->tplsav2->display('frm_import_object_data.tpl.php');

		 $this->bo->sessionmanager->save();
	  }

	  /**
	  * save_profilestore 
	  * 
	  * @access public
	  * @return void
	  */
	  function save_profilestore()
	  {
		 $this->bo->save_preferences('importcsvprofilestore'.$this->bo->site_object[unique_id], $this->profilestore); 
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
			default: return false;
			break;
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
			   // reset some necessary stuff
			   unset($_POST[field_names_row]);

			   //load the selected profile by replacing the POST var with the selected one
			   $profilename = $_POST[load_profile];
			   $profile = $this->profilestore[$profilename];
			   $_POST = $profile;
			   //_debug_array($_POST);
			   $_POST[save_profile] = $profilename;
			   $_POST[load_profile] = $profilename;
			}
		 }

		 //restore the kept post vars
		 $_POST[source] = $source;

		 //build the gui
		 $this->import();
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

		 $this->bo->addInfo(lang('Saved profile settings.'));

		 //the profile selectbox must have this new profile selected
		 $_POST[load_profile] = $_POST[save_profile];

		 //build the gui
		 $this->import();
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
		 return($fw.$value.$fw.$ft);
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
			default: $filter_where = 'all';
			break;
		 }

		 if($_POST[columns]=='select')
		 {
			$columns_arr = $this->bo->filter_array_with_prefix($_POST,'col_');
			break;

		 }
		 else
		 {
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
			default:
			break;
		 }

		 switch($ft)
		 {
			case '\t':
			$ft = "\t";
			break;
			default:
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
			//EVENT ON import	
			while(list($key, $val) = each($row)) 
			{
			   $_row['FLDXXX'.$key]=$val; 
			}
			$status[eventstatus] = $this->bo->run_event_plugins('on_import', $_row);
			//END EVENT CODE

			foreach($row as $value)
			{
			   echo($this->get_csv_field($fw, $ft, $rt, $ec, stripslashes($value)));
			}
			echo($rt);
		 }
	  }

	  function get_csv($fp)
	  {
		 /*
		 <input name="field_names_row" value="true" type="checkbox">
		 <input size="1" name="field_terminator" value=";" type="text">
		 <input size="1" name="field_wrapper" value="" type="text">
		 <input size="1" name="escape_character" value="\" type="text">
		 <input size="1" name="row_terminator" value="\n" type="text"
		 */
		 $bufsize=1000;
		 $seperator=$_POST['field_terminator'];
		 if($seperator=='\t') $seperator = chr(9);

		 return fgetcsv($fp, $bufsize, $seperator);
	  }

	  function preview_import($tmpfile)
	  {
		 //read 1st 6 lines and try to split them with our settings
	//	 if($_FILES['importfile'])
		 if($tmpfile)
		 {
			$row = 1;
			$fp = fopen ($tmpfile,"r");
//			while ($data = fgetcsv($fp, 1000, ",")) 
			while ($data = $this->get_csv($fp)) 
			{
			   $num = count($data);
			   if($row==1)  
			   {
				  if($_POST['field_names_row'])
				  {
					 $this->tplsav2->importpreview_head_arr=$data;
				  }
				  else
				  {
					 $this->tplsav2->importpreview_arr[]=$data;
					 for($idx=1;$idx<=$num;$idx++) 
					 {
						$this->tplsav2->importpreview_head_arr[]=$idx;
					 }
				  }
			   }
			   elseif($row==6)
			   {
				  break;
			   }
			   else
			   {
				  $this->tplsav2->importpreview_arr[]=$data;
			   }
			   $row++;			  
			}

			fclose ($fp);
		 }
	  }

	  function final_import()
	  {
		 if($_POST['csvfile'])
		 {
			$tmpfile=$_POST['csvfile'];
			$row = 0;
			$fp = fopen ($tmpfile,"r");

			//exec SQL;
			if($_POST['flushrecs']=='true')
			{
			   $sql_arr[]="TRUNCATE TABLE {$this->bo->site_object['table_name']} ;";
			}

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

			// Make table fields array
			$tbl_flds_arr = $this->bo->filter_array_with_prefix($_POST,'csvfld_',true);
			$tbl_flds_arr=$this->bo->strip_prefix_from_keys($tbl_flds_arr,'csvfld_');

			foreach($columns_arr as $col)
			{
			   if($tbl_flds_arr[$col]=='--ignore--')
			   {
				  continue;
			   }
			   else
			   {
				  if($field_str) $field_str.=',';
				  $field_str.=''.$col.'';
			   }
			}
			/*
			foreach($tbl_flds_arr as $tbl_fld)
			{
			   if($tbl_fld=='--ignore--') continue;
			   if($tbl_fld=='--empty--') continue;
			   $_tbl_flds_arr[$tbl_fld];

			   if($field_str) $field_str.=',';
			   $field_str.=''.$columns_arr[$tbl_fld].'';
			}
			*/

			//while ($data = fgetcsv ($fp, 1000, ",")) 
			while ($data = $this->get_csv ($fp)) 
			{
			   $row++;			  
			   $num = count($data);
			   if($row==1 && $_POST['field_names_row'])
			   {
				  $this->tplsav2->importpreview_arr[]=$data;
				  continue; 
			   }
			   else
			   {
				  unset($val_str);
				  foreach($tbl_flds_arr as $tbl_fld)
				  {
					 if($tbl_fld=='--ignore--')
					 {
						$i++;
						continue;
					 }

					 if($val_str)$val_str.=',';
					 $val_str.="'$data[$tbl_fld]'";
				  }

				  $sql_arr[]="
				  INSERT INTO {$this->bo->site_object['table_name']}
				  ( $field_str )
				  VALUES 
				  ( $val_str );
				  ";
			   }
			}

			fclose ($fp);

			foreach($sql_arr as $sql)
			{
			   $this->bo->so->site_db->query($sql,__LINE__,__FILE__);
			   $this->tplsav2->insertprev.=$sql;
			}


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
		 if($_POST['do_csv'])
		 {
			//$this->do_csv();
		 }
		 elseif($_POST['do_upload'])
		 {
			$tmpfile=$_FILES['importfile']['tmp_name'];
			$this->tplsav2->newtempfilename=$_FILES['importfile']['name'];

			$this->tplsav2->newtemp = tempnam('','');
			move_uploaded_file($tmpfile,$this->tplsav2->newtemp);

			$this->preview_import($this->tplsav2->newtemp);
			$this->import();	
		 }
		 elseif($_POST['do_reload_preview'])
		 {
			$this->tplsav2->newtemp=$_POST['csvfile'];
			$this->tplsav2->newtempfilename=$_POST['newtempfilename'];

			$this->preview_import($this->tplsav2->newtemp);
			$this->import();	
		 }
		 elseif($_POST['do_import'])
		 {
			$this->final_import();
			$this->import();	
		 }
		 elseif($_POST['do_profile'])
		 {
			$this->tplsav2->newtemp=$_POST['csvfile'];
			$this->tplsav2->newtempfilename=$_POST['newtempfilename'];
			
			if($_POST[load] == 'true')
			{
			   $this->load_profile();
			}
			elseif($_POST['save_profile'] != '')
			{
			   $this->save_profile();
			}		
			
			$this->preview_import($this->tplsav2->newtemp);
		 }

/*		 else
		 {
			$this->import();	//fixme should I even be here?
		 }
		 */
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
		 $result .= $this->formatOption('openoffice', lang('Open Office Spreadsheet'), $_POST[load_profile]);
		 $result .= $this->formatOption('excel', lang('MS Excel'), $_POST[load_profile]);

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
