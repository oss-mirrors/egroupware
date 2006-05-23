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
	  );

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
	  function import_egw_jinn_site()
	  {
		 if (is_array($_FILES[importfile]) || is_array($this->bo->session['tmp']))
		 {
			//do some simple checks and then try to import
			$filename = $_FILES['importfile']['tmp_name'];
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
					 $this->import_form();
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
				  $this->load_site_from_xml($buffer);
			   }
			   fclose($dataFile);
			}
		 }
		 else
		 {
			$this->import_form();
		 }

		 $this->bo->sessionmanager->save();
	  }

	  function import_form()
	  {
		 $this->header(lang('Import JiNN-Site'.$table));
		 $this->msg_box();

		 $this->tplsav2->form_action=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.ui_importsite.import_egw_jinn_site');
		 $this->tplsav2->display('importsite.tpl.php');		 
	  }

	  function load_site_from_xml($buffer='')
	  {
		 _debug_array($buffer);
		 echo "hallo"; 
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

		 $num_objects=0;
		 $import=$_FILES[importfile];

		 @include($import[tmp_name]);
		 $check_versions = true;
		 if (!($import_site && $checkbit))
		 {	
			if($this->bo->session['tmp']['import_site'] && $this->bo->session['tmp']['checkbit'])
			{
			   $import_site 			= $this->bo->session['tmp']['import_site'];
			   $import_site_objects 	= $this->bo->session['tmp']['import_site_objects'];
			   $import_obj_fields	= $this->bo->session['tmp']['import_obj_fields'];
			   $import_reports 		= $this->bo->session['tmp']['import_reports'];
			   $checkbit    			= $this->bo->session['tmp']['checkbit'];
			   $check_versions = false;
			   unset($this->bo->session['tmp']);
			   $this->bo->sessionmanager->save();
			}
		 }

		 if ($import_site && $checkbit)
		 {
			$info = $GLOBALS[egw_info][apps][jinn];
			if(!$import_site['jinn_version']) $import_site['jinn_version']='?';
			if(($import_site['jinn_version'] != $info['version']) && $check_versions)
			{
			   //admin must click OK to continue
			   $this->bo->addInfo(lang('This siteconfiguration, saved using JiNN version %1, may be incompatible with this JiNN version %2', $import_site['jinn_version'], $info['version']));
			   $this->bo->session['tmp']['file'] 				= $import[name]; 
			   $this->bo->session['tmp']['replace']				= $_POST[replace_existing];
			   $this->bo->session['tmp']['import_site'] 			= $import_site; 
			   $this->bo->session['tmp']['import_site_objects'] 	= $import_site_objects; 
			   $this->bo->session['tmp']['import_obj_fields'] 	= $import_obj_fields; 
			   $this->bo->session['tmp']['import_reports'] 		= $import_reports; 
			   $this->bo->session['tmp']['checkbit'] 				= $checkbit; 

			   $this->bo->exit_and_open_screen('jinn.ui_importsite.import_incompatible_egw_jinn_site');
			}

			$validfields = $this->bo->so->phpgw_table_fields('egw_jinn_sites');
			unset($validfields[site_id]);
			unset($imported);
			while(list($key, $val) = each($import_site)) 
			{
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
			$new_site_name=$data[0][value];	
			$thissitename=$this->bo->so->get_sites_by_name($new_site_name);

			if($_POST[replace_existing] && count($thissitename)>=1)
			{
			   $new_site_id=$thissitename[0];
			   $this->bo->so->upAndValidate_phpgw_data('egw_jinn_sites',$data,'site_id',$new_site_id);
			   // remove all existing objects
			   $this->bo->so->delete_phpgw_data('egw_jinn_objects',parent_site_id,$new_site_id);
			   $this->bo->addInfo(lang('Import was succesfull'));
			   $this->bo->addInfo(lang('Replaced existing site named <strong>%1</strong>.',$new_site_name));
			   $proceed=true;
			}
			/* insert as new site */
			elseif($status=$this->bo->so->insert_phpgw_data('egw_jinn_sites',$data))
			{
			   $new_site_id=$status[where_value];

			   if(count($thissitename)>=1)
			   {
				  $new_name=$new_site_name.' ('.lang('another').')';
				  $datanew[]=array(
					 'name'=>'site_name',
					 'value'=>$new_name
				  );
				  $this->bo->so->upAndValidate_phpgw_data('egw_jinn_sites',$datanew,'site_id',$new_site_id);
			   }
			   else
			   {
				  $new_name=$new_site_name;
			   }
			   $proceed=true;
			   $this->bo->addInfo(lang('Import was succesfull'));
			   $this->bo->addInfo(lang('The name of the new site is <strong>%1</strong>.',$new_name));
			}

			if($proceed)
			{
			   if(trim($import_site_files))
			   {
				  $zipinstring = base64_decode(str_replace( "\r\n", "", $import_site_files ));

				  $tmpfile = tempnam("","");
				  #$tmpfile='/tmp/tmp2.zip';

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


			/* site import has succeeded, go on with objects */
			if($proceed)
			{
			   if (is_array($import_site_objects))
			   {
				  $validfields = $this->bo->so->phpgw_table_fields('egw_jinn_objects');
				  unset($validfields[object_id]);
				  foreach($import_site_objects as $object)
				  {
					 unset($data_objects);
					 unset($imported);
					 while(list($key2, $val2) = each($object)) 
					 {
						if(array_key_exists($key2, $validfields))
						{
						   $imported[$key2] = true;
						   if ($key2 == 'parent_site_id') $val2=$new_site_id;

						   $data_objects[] = array
						   (
							  'name' => $key2,
							  'value' => addslashes($val2) 
						   );
						}
						else
						{
						   $this->bo->addError(lang('incompatibility result: Object <b>\'%3\'</b>, property <b>\'%1\'</b> with value <b>\'%2\'</b> could not be imported because it does not exist in this JiNN version', $key2, $val2, $object['name']));
						}
					 }
					 foreach($validfields as $fieldname => $yes)
					 {
						if(!array_key_exists($fieldname, $imported))
						{
						   $this->bo->addError(lang('incompatibility result: Object <b>\'%2\'</b>, property <b>\'%1\'</b> was not present in the file', $fieldname, $object['name']));
						}
					 }
					 if ($new_id = $this->bo->so->validateAndInsert_phpgw_data('egw_jinn_objects',$data_objects))
					 {
						$object_id[] = $new_id;
						$num_objects=count($object_id);
						$all_object_arr[$object['unique_id']]['id'] = $new_id;
						$all_object_arr[$object['unique_id']]['name'] = $object['name'];
					 } 
				  }
			   }

			   /* objects are imported, go on with obj-fields */
			   if(is_array($import_obj_fields))
			   {
				  $validfields = $this->bo->so->phpgw_table_fields('egw_jinn_obj_fields');
				  unset($validfields[field_id]);
				  foreach($import_obj_fields as $obj_field)
				  {
					 //new: get object from previously inserted objects
					 $obj_id = $all_object_arr[$obj_field['unique_id']]['id'];
					 if(!$obj_id) 
					 {
						continue;
					 }
					 $obj_field[field_parent_object] = $obj_id;

					 unset($data_fields);
					 unset($imported);
					 while(list($key2, $val2) = each($obj_field)) 
					 {
						if ($key2 == 'unique_id') 
						{
						   continue;  
						}

						if(array_key_exists($key2, $validfields))
						{
						   $imported[$key2] = true;
						   $data_fields[] = array
						   (
							  'name' => $key2,
							  'value' => addslashes($val2) 
						   );
						}
						else
						{
						   $this->bo->addError(lang('incompatibility result: Object <b>\'%3\'</b>, field <b>\'%1\'</b>, property <b>\'%2\'</b> could not be imported because it does not exist in this JiNN version', $obj_field['field_name'], $key2, $all_object_arr[$obj_field[obj_serial]]['name']));
						}
					 }
					 foreach($validfields as $fieldname => $yes)
					 {
						if(!array_key_exists($fieldname, $imported))
						{
						   $this->bo->addError(lang('incompatibility result: Object <b>\'%2\'</b>, field <b>\'%1\'</b>, property <b>\'%3\'</b> was not present in the file', $obj_field['field_name'], $all_object_arr[$obj_field[obj_serial]]['name'], $fieldname));
						}
					 }
					 if ($field_id[]=$this->bo->so->validateAndInsert_phpgw_data('egw_jinn_obj_fields',$data_fields))
					 {
						$num_fields=count($field_id);
					 } 
				  }
			   }
			   /*reports*/
			   /* site objects have succeeded, go on with reports */
			   if($proceed)
			   {
				  if (is_array($import_reports))
				  {
					 $validfields = $this->bo->so->phpgw_table_fields('egw_jinn_report');
					 unset($validfields[report_id]);
					 foreach($import_reports as $object)
					 {
						unset($data_objects);
						unset($imported);
						while(list($key2, $val2) = each($object))
						{
						   if(array_key_exists($key2, $validfields))
						   {
							  $imported[$key2] = true;
							  //if ($key2 == 'parent_site_id') $val2=$new_site_id;
							  $data_objects[] = array
							  (
								 'name' => $key2,
								 'value' => addslashes($val2)
							  );
						   }
						   else
						   {
							  $this->bo->addError(lang('incompatibility result: Object <b>\'%3\'</b>, property <b>\'%1\'</b> with value <b>\'%2\'</b> could not be imported because it does not exist in this JiNN version', $key2, $val2, $object['name']));
						   }
						}
						foreach($validfields as $fieldname => $yes)
						{
						   if(!array_key_exists($fieldname, $imported))
						   {
							  $this->bo->addError(lang('incompatibility result: Object <b>\'%2\'</b>, property <b>\'%1\'</b> was not present in the file', $fieldname, $object['name']));
						   }
						}
						if ($new_id = $this->bo->so->validateAndInsert_phpgw_data('egw_jinn_report',$data_objects))
						{
						   $object_id[] = $new_id;
						   $num_objects=count($object_id);
						   $all_object_arr[$object['unique_id']]['id'] = $new_id;
						   $all_object_arr[$object['unique_id']]['name'] = $object['name'];
						}
					 }
				  }	
			   }
			   /*reports*/
			   $this->bo->addInfo(lang('%1 Site Objects have been imported.',$num_objects));
			   $this->bo->addInfo(lang('%1 Site Obj-fields have been imported.',$num_fields));
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

	  /**
	  * import_incompatible_egw_jinn_site 
	  * 
	  * @access public
	  * @return void
	  */
	  function import_incompatible_egw_jinn_site()
	  {
		 $this->template->set_file(array(
			'import_form' => 'import_incompatible.tpl',
		 ));

		 $this->header(lang('Import JiNN-Site'.$table));
		 $this->msg_box();

		 $this->template->set_var('form_action',$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.ui_importsite.import_egw_jinn_site'));
		 $this->template->set_var('lang_Select_JiNN_site_file',lang('Loaded JiNN site file'));
		 $this->template->set_var('loaded_file',$this->bo->session['tmp']['file']);
		 if($this->bo->session['tmp']['replace'])
		 {
			$this->template->set_var('checked', 'checked');
		 }

		 $this->template->set_var('lang_Replace_existing_Site_with_the_same_name',lang('Replace existing site with the same name?'));
		 $this->template->set_var('lang_submit_and_import',lang('import anyway'));
		 $this->template->set_var('lang_cancel',lang('cancel'));
		 $this->template->set_var('cancel_redirect', $GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiadmin.browse_egw_jinn_sites'));


		 $this->template->pparse('out','import_form');

		 $this->bo->sessionmanager->save();
	  }

   }

