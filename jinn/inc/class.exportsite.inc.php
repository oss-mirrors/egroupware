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
		 'save_site_to_file'=>True,
		 'save_object_to_file'=>True
	  );

	  function exportsite()
	  {
		 $this->bo = CreateObject('jinn.boadmin');
		 parent::uijinn();

		 //$this->app_title = lang('Administrator Mode');

		 $this->permissionCheck();
	  }

	  function embed_site_files($site_id)
	  {
		 $sitefilespath=$this->bo->site_fs->get_jinn_sitefile_path($site_id);
		 if(is_dir($sitefilespath))
		 {
			$tempfile=$this->bo->site_fs->create_archive($site_id); 
			// Encode the file ready to send it off
			$handle = fopen($tempfile,'rb');
			$file_content = fread($handle,filesize($tempfile));
			fclose($handle);
			return chunk_split(base64_encode($file_content));

			unlink($tempfile);
		 }
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

		 if($site_files=$this->embed_site_files($site_data[0]['site_id']))
		 {
			$xml_arr['site_files']='<![CDATA['.$site_files.']]>';
		 }

		 $site_object_data=$this->bo->so->get_phpgw_record_values('egw_jinn_objects','parent_site_id', $this->bo->where_value ,'','','name');

		 if(is_array($site_object_data))
		 {
			foreach($site_object_data as $object)
			{
			   //to prevent confusion we now generate the unique_id field 
			   //so we can use this as temporary identifier when we import 
			   //the file. After this, this field has no function and it 
			   //is not necesary to store it in the database
			   $temp_id=uniqid('');

			   while (list ($key, $val) = each ($object)) 
			   { 
				  $obj_arr['temp_id']=$temp_id;

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
					 while (list ($key, $val) = each ($field)) 
					 { 
						if ($key != 'field_id' && $key !='field_parent_object') 
						{
						   // fix problem with wrong storage of null values causing a 0 value with mean something different
						   
						   if($val==null)
						   {
							  //$val='null';
							  //$val= "'".ereg_replace("'","\'",$val)."'";
							  //$val = $val;
						   }
						   //else
						   //{
						   //}
						   $field_arr[$key]=$val;
						}
					 }
					 //$field_arr['unique_id']=$temp_id;
					 $field_arr['temp_id']=$temp_id;
					 $obj_arr['fields'][]=$field_arr;
				  }
			   }

			   /*reports*/
			   $object_reports=$this->bo->so->get_phpgw_record_values('egw_jinn_report','report_object_id', $object['object_id'],'','','name');
			   if(is_array($object_reports))
			   {
				  foreach($object_reports as $report_single)
				  {
					 while (list ($key, $val) = each ($report_single))
					 {
						if ($key != 'report_id' && $key != 'report_object_id')
						{
						   $report_arr[$key]=$val;
						   //$report_arr[$key]='<![CDATA['.$val.']]>';
						}
					 }
					 $report_arr['temp_id']=$temp_id;
				
					 //FIXME exporting reports is disabled till it's better supported
					 //uncomment below to enable it again
					 //$obj_arr['reports'][]=$report_arr;
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

	  /**
	  * save_site_to_file: save site-, objects- and fields-configuration to a JiNN-file
	  * 
	  * @access public
	  * @return void
	  */
	  function save_site_to_file()
	  {
		 $GLOBALS['phpgw_info']['flags']['noheader']=True;
		 $GLOBALS['phpgw_info']['flags']['nonavbar']=True;
		 $GLOBALS['phpgw_info']['flags']['noappheader']=True;
		 $GLOBALS['phpgw_info']['flags']['noappfooter']=True;
		 $GLOBALS['phpgw_info']['flags']['nofooter']=True;

		 $site_data=$this->bo->so->get_phpgw_record_values('egw_jinn_sites',$this->bo->where_key,$this->bo->where_value,'','','name');
		 $filename=ereg_replace(' ','_',$site_data[0][site_name]).'.JiNN';
		 $date=date("d-m-Y",time());

		 header("Content-type: text");
		 header("Content-Disposition:attachment; filename=$filename");

		 $out='<'.'?p'.'hp'."\n\n"; 
		 /* strange but for nice vim indent file */
		 $out.='	/***************************************************************************'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.="	** JiNN Site Export : ".$filename."\n";
		 $out.="	** Date             : ".$date."\n";
		 $out.="	** JiNN Version     : ".$GLOBALS[phpgw_info][apps][jinn][version]."\n";
		 $out.='	** ---------------------------------------------------------------------- **'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.='	** JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare   **'."\n";
		 $out.='	** Copyright (C)2002, 2004 Pim Snel <pim.jinn@lingewoud.nl>               **'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.='	** JiNN - http://linuxstart.nl/jinn                                       **'."\n";
		 $out.='	** eGroupWare - http://www.egroupware.org                                 **'."\n";
		 $out.='	** This file is part of JiNN                                              **'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.='	** JiNN is free software; you can redistribute it and/or modify it under  **'."\n";
		 $out.='	** the terms of the GNU General Public License as published by the Free   **'."\n";
		 $out.='	** Software Foundation; either version 2 of the License.                  **'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.='	** JiNN is distributed in the hope that it will be useful,but WITHOUT ANY **'."\n";
		 $out.='	** WARRANTY; without even the implied warranty of MERCHANTABILITY or      **'."\n";
		 $out.='	** FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License  **'."\n";
		 $out.='	** for more details.                                                      **'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.='	** You should have received a copy of the GNU General Public License      **'."\n";
		 $out.='	** along with JiNN; if not, write to the Free Software Foundation, Inc.,  **'."\n";
		 $out.='	** 59 Temple Place, Suite 330, Boston, MA 02111-1307  USA                 **'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.='	***************************************************************************/'."\n";
		 $out.="\n";

		 $out.= "/* SITE ARRAY */\n";

		 $out.= '$import_site=array('."\n";

		 while (list ($key, $val) = each($site_data[0])) 
		 {
			if($key!='site_id') $out.= "	'$key' => '$val',\n";
		 }
		 $out.=");\n\n";


		 if($site_files=$this->embed_site_files($site_data[0]['site_id']))
		 {
			$out.= '$import_site_files="'."\n";
			$out.=$site_files;
			$out.='";'."\n\n";
		 }

		 $site_object_data=$this->bo->so->get_phpgw_record_values('egw_jinn_objects','parent_site_id', $this->bo->where_value ,'','','name');
		 $out.= "\n/* SITE_OBJECT ARRAY */\n";

		 if(is_array($site_object_data))
		 {
			foreach($site_object_data as $object)
			{
			   //to prevent confusion we now generate the unique_id field 
			   //so we can use this as temporary identifier when we import 
			   //the file. After this, this field has no function and it 
			   //is not necesary to store it in the database
			   $temp_id=uniqid('');
			   //$temp_id=$object['unique_id']=uniqid('');
			   
			   $out.= '$import_site_objects[]=array('."\n";

			   while (list ($key, $val) = each ($object)) 
			   { 
				  $field[value]=$serial;

				  if ($key != 'object_id')
				  {
					 $out .= "	'$key' => '".ereg_replace("'","\'",$val)."',\n"; 
				  }
			   }

			   //$out .= "	'unique_id' => '$temp_id',\n";  //depreciated
			   $out .= "	'temp_id' => '$temp_id',\n"; 

			   $out.=");\n\n";

			   /*
			   get array whith fielddata
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
							  $val= "'".ereg_replace("'","\'",$val)."'";
						   }
						   else
						   {
							  $val='null';
						   }
						   $out .= "	'$key' => $val,\n"; 
						}
					 }
					 $out .= "	'unique_id' => '".$temp_id."',\n"; //depreciated
					 $out .= "	'temp_id' => '".$temp_id."',\n"; 
					 $out.=");\n\n";
				  }
			   }
			   /*reports*/
			   $object_reports=$this->bo->so->get_phpgw_record_values('egw_jinn_report','report_object_id', $object['object_id'],'','','name');
			   if(is_array($object_reports))
			   {
				  foreach($object_reports as $report_single)
				  {
					 //print_r($report);
					 $report.= '$import_reports[]=array('."\n";

					 while (list ($key, $val) = each ($report_single))
					 {
						if ($key != 'report_id' && $key != 'report_object_id')
						{
						   $report .= "    '$key' => '".ereg_replace("'","\'",$val)."',\n";
						}
					 }
					 $report_arr['temp_id']=$temp_id;
					 $report .= "    'temp_id' => '$temp_id',\n";
					 $report .=");\n\n";
				  }
			   }
			   /*reports*/

			}
		 }
		 $out .= $report;
		 $out.='$checkbit=true;'."\n";
		 $out.='?>';
		 echo $out;
	  }

	  /**
	   * save_object_to_file 
	   * 
	   * @access public
	   * @return void
	   */
	  function save_object_to_file()
	  {
		 $GLOBALS['phpgw_info']['flags']['noheader']=True;
		 $GLOBALS['phpgw_info']['flags']['nonavbar']=True;
		 $GLOBALS['phpgw_info']['flags']['noappheader']=True;
		 $GLOBALS['phpgw_info']['flags']['noappfooter']=True;
		 $GLOBALS['phpgw_info']['flags']['nofooter']=True;

		 $object_data=$this->bo->so->get_phpgw_record_values('egw_jinn_objects',$this->bo->where_key,$this->bo->where_value,'','','name');

		 $filename=ereg_replace(' ','_',$object_data[0][name]).'.jobj';
		 $date=date("d-m-Y",time());
		 $version=$GLOBALS[egw_info][apps][jinn][version];
		 header("Content-type: text");
		 header("Content-Disposition:attachment; filename=$filename");

		 for($s=0;$s<(50-strlen($filename));$s++)
		 {
			$spaces1.=' ';
		 }
		 $spaces1.='**'."\n";

		 for($s=0;$s<(50-strlen($date));$s++)
		 {
			$spaces2.=' ';
		 }
		 $spaces2.='**'."\n";

		 for($s=0;$s<(50-strlen($version));$s++)
		 {
			$spaces3.=' ';
		 }
		 $spaces3.='**'."\n";

		 $out='<'.'?p'.'hp'."\n\n"; 
		 /* strange but for nice vim indent file */
		 $out.='	/***************************************************************************'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.="	** JiNN Object Export : ".$filename.$spaces1;
		 $out.="	** Date               : ".$date.$spaces2;
		 $out.="	** JiNN Version       : ".$version.$spaces3;
		 $out.='	** ---------------------------------------------------------------------- **'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.='	** JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare   **'."\n";
		 $out.='	** Copyright (C)2002, 2004 Pim Snel <pim.jinn@lingewoud.nl>               **'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.='	** JiNN - http://linuxstart.nl/jinn                                       **'."\n";
		 $out.='	** eGroupWare - http://www.egroupware.org                                 **'."\n";
		 $out.='	** This file is part of JiNN                                              **'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.='	** JiNN is free software; you can redistribute it and/or modify it under  **'."\n";
		 $out.='	** the terms of the GNU General Public License as published by the Free   **'."\n";
		 $out.='	** Software Foundation; either version 2 of the License.                  **'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.='	** JiNN is distributed in the hope that it will be useful,but WITHOUT ANY **'."\n";
		 $out.='	** WARRANTY; without even the implied warranty of MERCHANTABILITY or      **'."\n";
		 $out.='	** FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License  **'."\n";
		 $out.='	** for more details.                                                      **'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.='	** You should have received a copy of the GNU General Public License      **'."\n";
		 $out.='	** along with JiNN; if not, write to the Free Software Foundation, Inc.,  **'."\n";
		 $out.='	** 59 Temple Place, Suite 330, Boston, MA 02111-1307  USA                 **'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.='	***************************************************************************/'."\n";
		 $out.="\n";

		 $out.= "\n/* OBJECT ARRAY */\n";

		 if(is_array($object_data))
		 {
			foreach($object_data as $object)
			{
			   $out.= '$import_object=array('."\n";

			   while (list ($key, $val) = each ($object)) 
			   { 
				  $field[value]=$serial;

				  if ($key != 'object_id')
				  {
					 $out .= "	'$key' => '".ereg_replace("'","\'",$val)."',\n"; 
				  }
			   }
			   $out.=");\n\n";

			   /*
			   get array whith fielddata
			   store them as array
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
							  $val= "'".ereg_replace("'","\'",$val)."'";
						   }
						   else
						   {
							  $val='null';
						   }
						   $out .= "	'$key' => $val,\n"; 
						}
					 }
					 $out.=");\n\n";
				  }
			   }
			}
		 }

		 $out.='$checkbit=true;'."\n";
		 $out.='?>';
		 echo $out;

	  }





   }
