<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
   Copyright (C) 2006 Pim Snel <pim@lingewoud.nl>

   eGroupWare - http://www.eGroupware.org

   This file is part of JiNN

   JiNN is free software; you can redistribute it and/or modify it under
   the terms of the GNU General Public License as published by the Free
   Software Foundation; Version 2 of the License.

   JiNN is distributed in the hope that it will be useful,but WITHOUT ANY
   WARRANTY; without even the implied warranty of MERCHANTABILITY or
   FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
   for more details.

   You should have received a copy of the GNU General Public License
   along with JiNN; if not, write to the Free Software Foundation, Inc.,
   59 Temple Place, Suite 330, Boston, MA 02111-1307  USA
   */

   include_once(PHPGW_INCLUDE_ROOT.'/jinn/inc/class.uijinn.inc.php');

   class ui_importsite extends uijinn
   {
	  var $public_functions = Array(
		 'import_egw_jinn_site' => True,
		 'import_incompatible_egw_jinn_site' => True,
		 'import_object' => True,
		 'import_into' => True
	  );

	  var $num_objects=0;
	  var $num_fields=0;
	  var $num_reports=0;

	  function ui_importsite()
	  {
		 $this->bo = CreateObject('jinn.boadmin');
		 parent::uijinn();

		 $this->app_title = lang('Administrator Mode');

		 $this->permissionCheck();
	  }

	  /**
	  * import_egw_jinn_site 
	  * 
	  * @access public
	  * @return void
	  */
	  function import_egw_jinn_site($import_into=false)
	  {
		 if($_POST['import_into'])
		 {
			$import_into=$_POST['import_into'];
		 }
		 //	 die($import_into);

		 if (is_array($_FILES[importfile]) || is_file($_POST['newtemp']))
		 {
			if(is_file($_POST['newtemp']))
			{	
			   $filename = $_POST['newtemp'];
			}
			else
			{
			   $filename = $_FILES['importfile']['tmp_name'];
			}

			//do some simple checks and then try to import


			$dataFile = fopen( $filename, "r" ) ;
			if($dataFile)
			{
			   $buffer = fgets($dataFile, 4096);
			   if(substr(trim($buffer),0,5)=='<?'.'php') 
			   // try old unsafe import method (.JiNN)
			   {
				  if($_POST['disallow_oldjinn'])
				  {
					 $this->bo->addError(lang('The uploaded file seems to be saved in the old unsafe file format. This is not allowed.'));
					 $this->import_form($import_into);
				  }
				  else
				  {
					 $this->load_site_from_file();
				  }
			   }
			   else 
			   // else try new method (.jsxl)
			   {
				  while (!feof($dataFile)) 
				  {
					 $buffer .= fgets($dataFile, 4096);
				  }

				  $xmlObj   = CreateObject('jinn.xmltoarray',$buffer);
				  $xmlarray = $xmlObj->createArray();

				  if($this->load_site_from_xml($xmlarray,'',$import_into))
				  {
					 $this->bo->exit_and_open_screen('jinn.uiadmin.browse_egw_jinn_sites');
				  }
				  else
				  {
					 $this->import_form($import_into);

				  }

			   }
			   fclose($dataFile);
			}
		 }
		 elseif(is_array($this->bo->session['tmp']))
		 {
			$this->load_site_from_file();
		 }
		 else
		 {
			$this->import_form($import_into);
		 }

		 $this->bo->sessionmanager->save();
	  }

	  function import_into()
	  {
		 $this->import_egw_jinn_site($_GET['site_id']);
	  }

	  function select_objects($import_site_objects,$import_into=false)
	  {
		 $tmpfile=$_FILES['importfile']['tmp_name'];
		 $this->tplsav2->newtempfilename=$_FILES['importfile']['name'];

		 $this->tplsav2->newtemp = tempnam($GLOBALS['egw_info']['server']['temp_dir'],'');
		 move_uploaded_file($tmpfile,$this->tplsav2->newtemp);

		 $this->header(lang('Select objects to import'));
		 if(is_array($import_site_objects))
		 {
			$this->tplsav2->import_site_objects=$import_site_objects;
		 }

		 $this->msg_box();

		 $this->tplsav2->form_action=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.ui_importsite.import_egw_jinn_site');
		 $this->tplsav2->import_into=$import_into;
		 $this->tplsav2->display('import_site_select_objects.tpl.php');		 

		 $GLOBALS['phpgw']->common->phpgw_exit();
	  }


	  function import_form($import_into)
	  {
		 if($import_into)
		 {
			$this->header(lang('Import JiNN Site File into current Site'));
		 }
		 else
		 {
			$this->header(lang('Load JiNN Site File'));
		 }
		 $this->msg_box();

		 $this->tplsav2->form_action=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.ui_importsite.import_egw_jinn_site');
		 $this->tplsav2->import_into=$import_into;
		 $this->tplsav2->display('importsite.tpl.php');		 
	  }

	  function check_version($jinn_version,$check_versions)
	  {

		 $info = $GLOBALS['egw_info']['apps']['jinn'];

		 if(!$jinn_version)
		 {
			$jinn_version='?';
		 }

		 if(($jinn_version != $info['version']) && $check_versions)
		 {
			$this->bo->addInfo(lang('This site configuration, saved using JiNN version %1, may be incompatible with this JiNN version %2', $jinn_version, $info['version']));
			return false;
		 }
		 else
		 {
			return true;
		 }
	  }

	  function load_site_from_xml($xmlarray,$f_replace=false,$into_site_id=false)
	  {
		 if($f_replace)
		 {
			$replace=$f_replace;
			$_POST['replace_existing']=$f_replace;
		 }
		 else
		 {
			$replace=$_POST['replace_existing'];
		 }

		 $import_site_files		= $xmlarray['jinn']['site_files'];
		 $import_site_objects	= $xmlarray['jinn']['site'][0]['objects'];

		 if(!$_POST['objects_selected'])
		 {
			$this->select_objects($import_site_objects,$into_site_id);
		 }

		 if(!$into_site_id)
		 {
			$import_site			= $xmlarray['jinn']['site'][0];

			$check_versions = true;
			if(!$this->check_version($import_site['jinn_version'],$check_versions))
			{
			   //load incompatible;
			}

			$new_site_id = $this->save_site($import_site,$replace);
			if(!$new_site_id)
			{
			   $this->bo->addError(lang('An error occured while importing site data.'));
			   return false;
			}
		 }
		 else
		 {
			$new_site_id=$into_site_id;
		 }

		 if($import_site_files)
		 {
			$this->save_site_files($import_site_files,$new_site_id);
		 }


		 if(is_array($import_site_objects))
		 {
			$this->save_objects($import_site_objects,$import_obj_fields,$import_reports,$new_site_id,$replace);
		 }

		 $this->bo->addInfo(lang('%1 Site Objects have been imported.',$this->num_objects));
		 $this->bo->addInfo(lang('%1 Site Object Fields have been imported.',$this->num_fields));
		 //		 $this->bo->addInfo(lang('%1 Site Reports have been imported.',$this->num_reports));

		 $this->bo->addInfo(lang('Import was succesfull'));
		 return true;
	  }

	  /**
	  * load_site_from_file: create a new site or replace an existing from a JiNN file
	  * 
	  * @access public
	  * @return void
	  */
	  function load_site_from_file()
	  {
		 if($_POST['incompatibility_ok'] == '') //check if the admin has specifically ok-ed this import. If not, unload the loaded file
		 {
			unset($this->bo->session['tmp']);

			$this->bo->sessionmanager->save();
		 }

		 $import=$_FILES[importfile];

		 @include($import[tmp_name]);
		 $check_versions = true;

		 if(!($import_site && $checkbit))
		 {	
			if($this->bo->session['tmp']['import_site'] && $this->bo->session['tmp']['checkbit'])
			{
			   $import_site 			= $this->bo->session['tmp']['import_site'];
			   $import_site_objects 	= $this->bo->session['tmp']['import_site_objects'];
			   $import_obj_fields		= $this->bo->session['tmp']['import_obj_fields'];
			   $import_reports 			= $this->bo->session['tmp']['import_reports'];
			   $checkbit    			= $this->bo->session['tmp']['checkbit'];
			   $check_versions = false;
			   unset($this->bo->session['tmp']);
			   $this->bo->sessionmanager->save();
			}
		 }

		 if($import_site && $checkbit)
		 {
			if(!$this->check_version($import_site['jinn_version'],$check_versions))
			{
			   $this->bo->session['tmp']['file'] 				= $import[name]; 
			   $this->bo->session['tmp']['replace']				= $_POST[replace_existing];
			   $this->bo->session['tmp']['import_site'] 		= $import_site; 
			   $this->bo->session['tmp']['import_site_objects']	= $import_site_objects; 
			   $this->bo->session['tmp']['import_obj_fields'] 	= $import_obj_fields; 
			   $this->bo->session['tmp']['import_reports'] 		= $import_reports; 
			   $this->bo->session['tmp']['checkbit'] 			= $checkbit; 

			   $this->bo->exit_and_open_screen('jinn.ui_importsite.import_incompatible_egw_jinn_site');
			}

			if($new_site_id = $this->save_site($import_site,$_POST['replace_existing']))
			{
			   $proceed=true;
			   //return false;
			}

			if($proceed)
			{
			   $this->save_site_files($import_site_files,$new_site_id);

			   if(is_array($import_site_objects))
			   {
				  $this->save_objects($import_site_objects,$import_obj_fields,$import_reports,$new_site_id,$_POST['replace_existing']);
			   }

			   $this->bo->addInfo(lang('%1 Site Objects have been imported.',$this->num_objects));
			   $this->bo->addInfo(lang('%1 Site Object Fields have been imported.',$this->num_fields));
			   $this->bo->addInfo(lang('%1 Site Reports have been imported.',$this->num_reports));

			   $this->bo->exit_and_open_screen('jinn.uiadmin.browse_egw_jinn_sites');
			}
			else
			{
			   $this->bo->addError(lang('Import failed'));
			   $this->bo->exit_and_open_screen('jinn.uiadmin.browse_egw_jinn_sites');
			}
		 }

		 $this->bo->sessionmanager->save();
	  }

	  function site_name_exist($site_name)
	  {
		 $thissitename=$this->bo->so->get_sites_by_name($site_name);
		 if(count($thissitename)>=1)
		 {
			return true;
		 }
	  }

	  function save_site($import_site,$replace)
	  {
		 $validfields = $this->bo->so->phpgw_table_fields('egw_jinn_sites');
		 unset($validfields[site_id]);
		 unset($imported);

		 while(list($key, $val) = each($import_site)) 
		 {
			if($key=='objects' || $key=='uniqid')
			{
			   continue;
			}
			
			/*if($key=='uniqid')
			{
			   $val=$this->bo->so->generate_unique_id();
			}*/

			if(array_key_exists($key, $validfields))
			{
			   $imported[$key] = true;
			   $data[] = array
			   (
				  'name' => $key,
				  'value' => addslashes($val) 
			   );
			}
			else
			{
			   $this->bo->addError(lang('incompatibility result: Site <b>\'%3\'</b>, property <b>\'%1\'</b> with value <b>\'%2\'</b> could not be imported because it does not exist in this JiNN version', $key, $val, $import_site['site_name']));
			}
		 }

		 foreach($validfields as $fieldname => $yes)
		 {
			if(!array_key_exists($fieldname, $imported))
			{
			   $this->bo->addError(lang('incompatibility result: Site <b>\'%2\'</b>, property <b>\'%1\'</b> was not present in the file', $fieldname, $import_site['site_name']));
			}
		 }

		 $new_site_name=$data[0]['value'];	

		 if($replace)
		 {
			//new method: no update but remove
			$site_with_this_name_arr=$this->bo->so->get_sites_by_name($new_site_name);
			$new_site_id=$site_with_this_name_arr[0];

			$this->bo->so->delete_phpgw_data('egw_jinn_sites','site_id',$new_site_id);
			$this->bo->so->delete_phpgw_data('egw_jinn_objects','parent_site_id',$new_site_id);

			// remove all existing objects
			$this->bo->addInfo(lang('Replaced existing site named <strong>%1</strong>.',$new_site_name));

			$status=$this->bo->so->insert_new_site($data);
			$new_site_id=$status[where_value];
		 }
		 else
		 {
			if($this->site_name_exist($new_site_name))
			{
			   while($this->site_name_exist($new_site_name))
			   {
				  if(is_numeric(trim(substr($new_site_name,-2))))
				  {
					 $new_postfix=intval(trim(substr($new_site_name,-2)))+1;
					 $new_site_name = substr($new_site_name,0,strlen($new_site_name)-2); 
					 $new_site_name.=' ' . $new_postfix;
				  }
				  else
				  {
					 $new_site_name= trim($new_site_name).' 2';
				  }
			   }
			   $datanew[]=array(
				  'name'=>'site_name',
				  'value'=>$new_site_name
			   );
			}

			$status=$this->bo->so->insert_new_site($data);
			$new_site_id=$status[where_value];

			if(is_array($datanew))
			{
			   $this->bo->so->upAndValidate_phpgw_data('egw_jinn_sites',$datanew,'site_id',$new_site_id);
			}

			$this->bo->addInfo(lang('The name of the new site is <strong>%1</strong>.',$new_site_name));

		 }

		 return $new_site_id;
	  }

	  function save_site_files($import_site_files,$new_site_id)
	  {
		 if(trim($import_site_files))
		 {
			$zipinstring = base64_decode(str_replace( "\r\n", "", $import_site_files ));

			$tmpfile = tempnam("","");

			if (!$handle = fopen($tmpfile, 'a')) {
			   //error
			}

			if (!fwrite($handle, $zipinstring)) {
			   //error
			}
			fclose($handle);

			$proceed=$this->bo->site_fs->extract_archive($tmpfile,$new_site_id); 
			$this->bo->addInfo(lang('Site files succesfully extracted.'));
			unlink($tmpfile);
		 }
	  }

	  function save_objects($import_site_objects,$import_obj_fields,$import_reports,$parent_site_id,$replace)
	  {
		 $validfields = $this->bo->so->phpgw_table_fields('egw_jinn_objects');
		 $ignorefields = array('uniqid','fields','reports','parent_site_id');

		 //create renaming table for relations
		 foreach($import_site_objects as $object)
		 {
			$newid=$this->bo->so->generate_unique_id();
			$oldid=$object['object_id'];
			$object_old2new_id_arr[$oldid]=$newid;
			//$object_new2old_id_arr[$newid]=$oldid;
		 }

		 foreach($import_site_objects as $object)
		 {
			unset($data_objects);
			unset($imported);

			if($_POST['objects_selected'] && !$_POST[$object['object_id']])
			{
			   continue;
			}

			//while(list($key, $val) = each($object)) 
			foreach($object as $key => $val)
			{
			   $old_object_id=$object['object_id'];
			   if($key=='object_id' )
			   {
				  $new_id = $val = $object_old2new_id_arr[$old_object_id];
				  //				  continue;
			   }

			   //FIXME REMOVE?
			   if($replace && $key=='object_id')
			   {
				  $this->bo->so->delete_phpgw_data('egw_jinn_objects', 'object_id', $val);
			   }

			   if($key=='temp_id' || $key=='parent_site_id')
			   {
				  continue;
			   }

			   //very complex block which updates all references to objects in the relation conf
			   if($key=='relations' && trim($val))
			   {
				  if (!function_exists('array_walk_recursive'))
				  {
					 function array_walk_recursive(&$input, $funcname, $userdata = "")
					 {
						if (!is_callable($funcname))
						{
						   return false;
						}

						if (!is_array($input))
						{
						   return false;
						}

						foreach ($input AS $key => $value)
						{
						   if (is_array($input[$key]))
						   {
							  array_walk_recursive($input[$key], $funcname, $userdata);
						   }
						   else
						   {
							  $saved_value = $value;
							  if (!empty($userdata))
							  {
								 $funcname($value, $key, $userdata);
							  }
							  else
							  {
								 $funcname($value, $key);
							  }

							  if ($value != $saved_value)
							  {
								 $input[$key] = $value;
							  }
						   }
						}
						return true;
					 }
				  }

				  $_rel_arr=unserialize(base64_decode($val));

				  if (!function_exists('replace_oldid_with_newid'))
				  {
					 function replace_oldid_with_newid(&$item, &$key,$repl) 
					 {
						$key=str_replace($repl['old'],$repl['new'],$key);
						$item=str_replace($repl['old'],$repl['new'],$item);
					 }
				  }
				  
				  foreach($object_old2new_id_arr as $oldid => $newid)
				  {
					 $repl_arr['old']=$oldid;
					 $repl_arr['new']=$newid;
					 array_walk_recursive($_rel_arr, 'replace_oldid_with_newid', $repl_arr);
				  }

				  $val==base64_encode(serialize($_rel_arr));
			   }


			   if(array_key_exists($key, $validfields))
			   {
				  $imported[$key] = true;

				  $data_objects[] = array
				  (
					 'name' => $key,
					 'value' => addslashes($val) 
				  );
			   }
			   elseif(!in_array($key,$ignorefields))
			   {
				  $this->bo->addError(lang('incompatibility result: Object <b>\'%3\'</b>, property <b>\'%1\'</b> with value <b>\'%2\'</b> could not be imported because it does not exist in this JiNN version', $key, $val, $object['name']));
			   }
			}
			foreach($validfields as $fieldname => $yes)
			{
			   if(!array_key_exists($fieldname, $imported) && !in_array($fieldname,$ignorefields))
			   {
				  $this->bo->addError(lang('incompatibility result: Object <b>\'%2\'</b>, property <b>\'%1\'</b> was not present in the file', $fieldname, $object['name']));
			   }
			}

			$data_objects[] = array ( 'name' => 'parent_site_id', 'value' => $parent_site_id);

			//			$data_objects[] = array ( 'name' => 'object_id', 'value' => $object_old2new_id_arr[$old_object_id]);

			//			   $new_id = $status['where_value'];
			if($status = $this->bo->so->validateAndInsert_phpgw_data('egw_jinn_objects',$data_objects))
			{
			   //echo $new_id .' ';
			   //echo $status['where_value'].' <br/>';

			   //			   $new_id = $status['where_value'];

			   if($old_object_id)
			   {
				  $old_object_id = ($object['temp_id']?$object['temp_id']:$object['unique_id']);
			   }
			   // check if we're called from the newer load_site_from_xml function with fields etc... embedded
			   if(is_array($object['fields']))
			   {
				  $import_obj_fields = $object['fields'];
				  $import_reports = $object['reports'];
			   }

			   $this->save_fields($import_obj_fields,$old_object_id,$new_id,$object['name']); 

			   //FIXME exporting reports is disabled till it's better supported
			   //uncomment below to enable it again
			   //$this->save_reports($import_reports,$temp_id,$new_id);

			   $this->num_objects++;
			} 
		 }
		 //die();
	  }

	  // $old_parent_id is the unique id saved in object
	  function save_fields($import_obj_fields,$old_parent_id,$new_parent_id,$parent_object_name)
	  {
		 if(is_array($import_obj_fields))
		 {
			$validfields = $this->bo->so->phpgw_table_fields('egw_jinn_obj_fields');
			unset($validfields['field_id']);

			foreach($import_obj_fields as $obj_field)
			{
			   if($old_parent_id != $obj_field['field_parent_object'] 
			   && ($obj_field['unique_id']!=$old_parent_id) 
			   && ($obj_field['temp_id']!=$old_parent_id))
			   //if(!$old_parent_id || 
			   //			   ($obj_field['unique_id'] && $obj_field['unique_id']!=$old_parent_id) || 
			   //			   ($obj_field['temp_id'] && $obj_field['temp_id']!=$old_parent_id) )
			   {
				  continue;
			   }

			   $obj_field['field_parent_object'] = $new_parent_id;

			   unset($data_fields);
			   unset($imported);
			   while(list($key, $val) = each($obj_field)) 
			   {
				  if ($key == 'unique_id'|| $key == 'temp_id') 
				  {
					 continue;  
				  }

				  if(array_key_exists($key, $validfields))
				  {
					 $imported[$key] = true;
					 $data_fields[] = array
					 (
						'name' => $key,
						'value' => addslashes($val) 
					 );
				  }
				  else
				  {
					 $this->bo->addError(lang('incompatibility result: Object <b>\'%3\'</b>, field <b>\'%1\'</b>, property <b>\'%2\'</b> could not be imported because it does not exist in this JiNN version', $obj_field['field_name'], $key, $parent_object_name));
				  }
			   }
			   foreach($validfields as $fieldname => $yes)
			   {
				  if(!array_key_exists($fieldname, $imported))
				  {
					 $this->bo->addError(lang('incompatibility result: Object <b>\'%2\'</b>, field <b>\'%1\'</b>, property <b>\'%3\'</b> was not present in the file', $obj_field['field_name'], $parent_object_name, $fieldname));
				  }
			   }

			   if($this->bo->so->validateAndInsert_phpgw_data('egw_jinn_obj_fields',$data_fields))
			   {
				  $this->num_fields++;
			   }
			}
		 }
	  }

	  function save_reports($import_reports,$temp_parent_id,$new_parent_id)
	  {
		 if(is_array($import_reports))
		 {
			$validfields = $this->bo->so->phpgw_table_fields('egw_jinn_report');

			unset($validfields[report_id]);

			foreach($import_reports as $report)
			{
			   if(!$temp_parent_id || $report['temp_id']!=$temp_parent_id)
			   {
				  continue;
			   }

			   unset($data_reports);
			   unset($imported);
			   $report['report_object_id'] = $new_parent_id;
			   while(list($key, $val) = each($report))
			   {
				  if ($key == 'unique_id'|| $key == 'temp_id') 
				  {
					 continue;  
				  }

				  if(array_key_exists($key, $validfields))
				  {
					 $imported[$key] = true;
					 $data_reports[] = array
					 (
						'name' => $key,
						'value' => addslashes($val)
					 );
				  }
				  else
				  {
					 $this->bo->addError(lang('incompatibility result: Report <b>\'%3\'</b>, property <b>\'%1\'</b> with value <b>\'%2\'</b> could not be imported because it does not exist in this JiNN version', $key, $val, $report['name']));
				  }
			   }
			   foreach($validfields as $fieldname => $yes)
			   {
				  if(!array_key_exists($fieldname, $imported))
				  {
					 $this->bo->addError(lang('incompatibility result: Report <b>\'%2\'</b>, property <b>\'%1\'</b> was not present in the file', $fieldname, $report['name']));
				  }
			   }
			   if($this->bo->so->validateAndInsert_phpgw_data('egw_jinn_report',$data_reports))
			   {
				  $this->num_reports++;
			   }
			}
		 }
	  }


	  /**
	  * import_incompatible_egw_jinn_site 
	  * 
	  * @access public
	  * @return void
	  */
	  function import_incompatible_egw_jinn_site()
	  {
		 $this->header(lang('Import JiNN Site'));
		 $this->msg_box();

		 $this->tplsav2->set_var('form_action',$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.ui_importsite.import_egw_jinn_site'));
		 $this->tplsav2->set_var('loaded_file',$this->bo->session['tmp']['file']);

		 if($this->bo->session['tmp']['replace'])
		 {
			$this->tplsav2->set_var('checked', 'checked="checked"');
		 }

		 $this->tplsav2->set_var('cancel_redirect', $GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiadmin.browse_egw_jinn_sites'));

		 $this->tplsav2->display('import_incompatible.tpl.php');

		 $this->bo->sessionmanager->save();
	  }

	  /**
	  * import_object 
	  * 
	  * @access public
	  * @return void
	  */
	  function import_object()
	  {
		 if (is_array($GLOBALS[HTTP_POST_FILES][importfile]))
		 {
			$import=$GLOBALS[HTTP_POST_FILES][importfile];

			@include($import[tmp_name]);
			if ($import_object && $checkbit)
			{
			   $validfields = $this->bo->so->phpgw_table_fields('egw_jinn_objects');
			   unset($validfields[object_id]);
			   $imported = array();

			   while(list($key, $val) = each($import_object)) 
			   {
				  if(array_key_exists($key, $validfields))
				  {
					 $imported[$key] = true;
					 if ($key=='parent_site_id') $val=$_POST[parent_site_id];
					 $data[] = array
					 (
						'name' => $key,
						'value' => addslashes($val) 
					 );
				  }
				  else
				  {
					 $this->bo->addError(lang('incompatibility result: Object <b>\'%3\'</b>, property <b>\'%1\'</b> with value <b>\'%2\'</b> could not be imported because it does not exist in this JiNN version', $key, $val, $import_object['name']));
				  }
			   }
			   foreach($validfields as $fieldname => $yes)
			   {
				  if(!array_key_exists($fieldname, $imported))
				  {
					 $this->bo->addError(lang('incompatibility result: Object <b>\'%2\'</b>, property <b>\'%1\'</b> was not present in the file', $fieldname, $import_object['name']));
				  }
			   }

			   $new_object_name=$data[1][value];	
			   $thisobjectname=$this->bo->so->get_objects_by_name($new_object_name,$_POST[parent_site_id]);

			   /* insert as new object */
			   if($status=$this->bo->so->insert_phpgw_data('egw_jinn_objects',$data))
			   {
				  $new_object_id=$status[where_value];

				  if(count($thisobjectname)>=1)
				  {
					 $new_name=$new_object_name.' ('.lang('another').')';

					 $datanew[]=array(
						'name'=>'name',
						'value'=>$new_name
					 );
					 $this->bo->so->upAndValidate_phpgw_data('egw_jinn_objects',$datanew,'object_id',$new_object_id);
				  }
				  else
				  {
					 $new_name=$new_object_name;
				  }
				  $proceed=true;
				  $this->bo->addInfo(lang('Import was succesfull'));
				  $this->bo->addInfo(lang('The name of the new object is <strong>%1</strong>.',$new_name));
			   }

			   if($proceed)
			   {
				  /* objects are imported , go on with obj-fields */
				  if(is_array($import_obj_fields))
				  {
					 $validfields = $this->bo->so->phpgw_table_fields('egw_jinn_obj_fields');
					 unset($validfields['field_id']);
					 foreach($import_obj_fields as $obj_field)
					 {
						$obj_field['field_parent_object'] = $new_object_id; // no matching by unique_id is required here (like in load_site_from_file()), because only one object is imported

						unset($data_fields);
						unset($imported);
						while(list($key, $val) = each($obj_field)) 
						{
						   if ($key == 'obj_serial') 
						   {
							  continue;  
						   }
						   if ($key == 'unique_id') 
						   {
							  continue;  
						   }

						   if(array_key_exists($key, $validfields))
						   {
							  $imported[$key] = true;
							  $data_fields[] = array
							  (
								 'name' => $key,
								 'value' => addslashes($val) 
							  );
						   }
						   else
						   {
							  $this->bo->addError(lang('incompatibility result: Object <strong>\'%3\'</strong>, field <strong>\'%1\'</strong>, property <strong>\'%2\'</strong> could not be imported because it does not exist in this JiNN version', $obj_field['field_name'], $key, $import_object['name']));
						   }
						}
						foreach($validfields as $fieldname => $yes)
						{
						   if(!array_key_exists($fieldname, $imported))
						   {
							  $this->bo->addError(lang('incompatibility result: Object <strong>\'%2\'</strong>, field <strong>\'%1\'</strong>, property <strong>\'%3\'</strong> was not present in the file', $obj_field['field_name'], $import_object['name'], $fieldname));
						   }
						}

						if ($this->bo->so->validateAndInsert_phpgw_data('egw_jinn_obj_fields',$data_fields))
						{
						   $this->num_fields++;
						} 
					 }
				  }

				  $this->bo->addInfo(lang('%1 Site Objects have been imported.',1));
				  $this->bo->addInfo(lang('%1 Site Obj-fields have been imported.',$this->num_fields));
			   }
			   else
			   {
				  $this->bo->addError(lang('Import failed'));
			   }

			   $this->bo->exit_and_open_screen('jinn.uiadmin.add_edit_site&where_key=site_id&where_value='.$_POST[parent_site_id]);
			}

		 }
		 else
		 {
			$this->template->set_file(array(
			   'import_form' => 'import_object.tpl',
			));

			$this->header(lang('Import JiNN-Object'));
			$this->msg_box();

			$this->template->set_var('form_action',$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.ui_importsite.import_object'));
			$this->template->set_var('lang_Select_JiNN_site_file',lang('Select JiNN object file (*.jobj'));
			$this->template->set_var('parent_site_id',$this->bo->where_value);
			$this->template->set_var('lang_submit_and_import',lang('submit and import'));
			$this->template->pparse('out','import_form');
		 }

		 $this->bo->sessionmanager->save();
	  }

   }

