<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
   Copyright (C)2006 Pim Snel <pim@lingewoud.nl>

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

   class exportsite extends uijinn
   {
	  var $public_functions = Array(
		 'save_site_to_xml'=>True,
	  );

	  function exportsite()
	  {
		 $this->bo = CreateObject('jinn.boadmin');
		 parent::uijinn();

		 //$this->app_title = lang('Administrator Mode');

		 $this->permissionCheck();
	  }

	  /**
	  * successor of save site to file using xml 
	  */
	  function save_site_to_xml()
	  {
		 //must object id's be null

		 $GLOBALS['phpgw_info']['flags']['noheader']=True;
		 $GLOBALS['phpgw_info']['flags']['nonavbar']=True;
		 $GLOBALS['phpgw_info']['flags']['noappheader']=True;
		 $GLOBALS['phpgw_info']['flags']['noappfooter']=True;
		 $GLOBALS['phpgw_info']['flags']['nofooter']=True;

		 $site_data=$this->bo->so->get_phpgw_record_values('egw_jinn_sites',$this->bo->where_key,$this->bo->where_value,'','','name');
		 $filename=ereg_replace(' ','_',$site_data[0]['site_name']).'.jsxl';
		 $date=date("d-m-Y",time());

		 header("Content-type: text");
		 header("Content-Disposition:attachment; filename=$filename");

		 $xml_arr['export_filename']=$filename;
		 $xml_arr['date']=$date;
		 $xml_arr['jinn_version']=$GLOBALS['phpgw_info']['apps']['jinn']['version'];
		 $xml_arr['site']=array();

		 while (list ($key, $val) = each($site_data[0])) 
		 {
			if($key!='site_id')
			{
			   $xml_arr['site'][$key]=$val;
			}
		 }

		 $sitefilespath=$this->bo->site_fs->get_jinn_sitefile_path($site_data[0][site_id]);
		 if(is_dir($sitefilespath))
		 {
			$tempfile=$this->bo->site_fs->create_archive($site_data[0][site_id]); 
			// Encode the file ready to send it off
			$handle = fopen($tempfile,'rb');
			$file_content = fread($handle,filesize($tempfile));
			fclose($handle);
			$xml_arr['site_files']=chunk_split(base64_encode($file_content));
			unlink($tempfile);
		 }

		 $site_object_data=$this->bo->so->get_phpgw_record_values('egw_jinn_objects','parent_site_id', $this->bo->where_value ,'','','name');

		 if(is_array($site_object_data))
		 {
			foreach($site_object_data as $object)
			{
			   while (list ($key, $val) = each ($object)) 
			   { 
				  if($key != 'object_idxxx' && $key != 'parent_site_id') // keep if needed for static egroupware apps
				  {
					 $obj_arr[$key]=$val;
				  }
			   }

			   /*
			   get array whith fielddata
			   store them as array with unique_id as parent object identifier
			   */
			   $object_field_data=$this->bo->so->get_phpgw_record_values('egw_jinn_obj_fields','field_parent_object', $object['object_id'],'','','name');
			   if(is_array($object_field_data))
			   {
				  foreach ($object_field_data as $field)
				  {
					 $out.= '$import_obj_fields[]=array('."\n";

					 while (list ($key, $val) = each ($field)) 
					 { 
						if ($key != 'field_id' && $key !='field_parent_object') 
						{
						   // fix problem with wrong storage of null values causing a 0 value with mean something different
						   if($val!=null)
						   {
							  //$val= "'".ereg_replace("'","\'",$val)."'";
							  //$val = $val;
						   }
						   else
						   {
							  $val='null';
						   }
						   $field_arr[$key]=$val;
						}
					 }
					 $field_arr['unique_id']=$object['unique_id'];
					 $obj_arr['fields'][]=$field_arr;
				  }
			   }

			   /*reports*/
			   $object_reports=$this->bo->so->get_phpgw_record_values('egw_jinn_report','report_object_id', $object['unique_id'],'','','name');
			   if(is_array($object_reports))
			   {
				  foreach($object_reports as $report_single)
				  {
					 while (list ($key, $val) = each ($report_single))
					 {
						if ($key != 'report_id' )
						{
						   $report_arr[$key]=$val;
						}
					 }
					 $obj_arr['reports'][]=$report_arr;
				  }
			   }
			   /*reports*/

			   $xml_arr['site']['objects'][]=$obj_arr;
			}
			//endforeach
		 }

		 $jinn_site['jinn']=$xml_arr;
		 
		 //$humanread=true;

		 $this->xmlversion = '1.0';
		 echo '<?xml version="'.$this->xmlversion.'"?>'.($humanread?"\n":'');
		 echo $this->s_array2xml($jinn_site,array('objects','fields'),0,false,($humanread?'  ':''),$humanread);
	  }

	  /**  
	  * s_array2xml: Converts an array into XML using array keys as the XML tokens. 
	  * 
	  * @param mixed     $p_array       multi-dimentional array to 
	  * @param mixed     $p_lists       array of keys that should have their contents treated as lists 
	  * @param integer   $p_iteration   recursive iteration count (do not send) 
	  * @param bool      $p_list        list key when working on list contents (do not send) 
	  * @return mixed                   string of xml values 
	  */ 
	  function s_array2xml($p_array, $p_lists = array(), $p_iteration = 0,  $p_list = false, $indent_string='   ',$line_breaks=true) 
	  {
		 $l_xml = ''; 
		 if($line_breaks) $l_break_str="\n";

		 foreach ($p_array as $l_key => $l_value) 
		 { 
			// check if this is a list 
			$l_list = false; 
			if (in_array($l_key, $p_lists) && ($l_key != '0')) 
			{
			   $l_list = $l_key; 
			}

			// set indent string 
			$l_indent = ''; 
			for ($l_count = 0; $l_count < ($p_iteration - ($p_list ? 1 : 0)); $l_count++) 
			{ 
			   $l_indent .= $indent_string; 
			} 

			$l_key = (($p_list !== false) ? $p_list  : (($l_list === $l_key) ? false : $l_key) ); 

			//$l_this_xml = (is_array($l_value)  ? ($l_list ? '' : $l_break_str)  . $this->s_array2xml($l_value, $p_lists,  $p_iteration + (($p_list === false)  ? 1 : 0),  $l_list, $indent_string,$line_breaks)  . ($l_list ? '' : $l_indent)  : $l_value); 

			if(is_array($l_value))
			{
			   $l_this_xml  = ($l_list ? '' : $l_break_str);
			   $l_this_xml .= $this->s_array2xml($l_value, $p_lists,  $p_iteration + (($p_list === false)  ? 1 : 0),  $l_list, $indent_string,$line_breaks);
			   $l_this_xml .= ($l_list ? '' : $l_indent);
			}
			else
			{
			   $l_this_xml=$l_value;
			}
			
			if ($l_key !== false)
			{
			   $l_xml .= $l_indent . "<$l_key>$l_this_xml</$l_key>$l_break_str"; 
			}
			else
			{
			   $l_xml .= $l_this_xml; 
			}
		 } 

		 return $l_xml; 

	  } 





   }
