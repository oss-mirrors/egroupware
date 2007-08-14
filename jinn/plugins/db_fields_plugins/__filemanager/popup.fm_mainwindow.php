<?php
   /**************************************************************************\
   * eGroupWare - UploadImage-plugin for htmlArea                             *
   * http://www.eGroupWare.org                                                *
   * Written and (c) by Xiang Wei ZHUO <wei@zhuo.org>                         *
   * Modified for eGW by and (c) by Pim Snel <pim@lingewoud.nl>               *
   * --------------------------------------------                             *
   * This program is free software; you can redistribute it and/or modify it  *
   * under the terms of the GNU General Public License as published by the    *
   * Free Software Foundation; version 2 of the License.                      *
   \**************************************************************************/

   // FIXME: remove imageMagick shit, we only use gdlib
   // FIXME: autodetect safe_mode
   // FIXME set current app to the calling app

   define('IMAGE_CLASS', 'GD');  

   $phpgw_flags = Array(
	  'currentapp'	=>	'jinn',
	  'noheader'	=>	True,
	  'nonavbar'	=>	True,
	  'noappheader'	=>	True,
	  'noappfooter'	=>	True,
	  'nofooter'	=>	True
   );

   $GLOBALS['phpgw_info']['flags'] = $phpgw_flags;
   require_once('../../../../header.inc.php');
   require_once('function.inc.php');

   $sessdata =	$GLOBALS['phpgw']->session->appsession('UploadImage','phpgwapi');
   
   $sessiondata = $GLOBALS['phpgw']->session->appsession('session_data','jinn');

   $session_name = $sessiondata['JAPIESESS'];

   if($session_name)
   {
	  $bo = CreateObject('jinn.bouser',$session_name);
   }
   else
   {
	  $bo = CreateObject('jinn.bouser');
   }

   $plug_root= EGW_SERVER_ROOT.'/jinn/plugins/db_fields_plugins/__filemanager';
   $tplsav2 = CreateObject('phpgwapi.tplsavant2');
   $tplsav2->addPath('template',$plug_root.'/tpl');

   $tplsav2->SAFE_MODE = false;	//In safe mode, directory creation is not permitted.

   if(ereg("UNIQ[a-zA-Z0-9]{13}SOURCE", $_GET[field]))
   {
	  $_t1=explode('UNIQ',$_GET[field]);
	  $_t2=explode('SOURCE',$_t1[1]);
	  $_GET[field]=$_t2[0];
   }

   if($_GET[curr_obj_id])
   {
	  $field_config = $bo->so->get_field_values($_GET[curr_obj_id],$_GET[field]);
   }
   else
   {
	  $field_config = $bo->so->get_field_values($bo->session['site_object_id'],$_GET[field]);
   }

   //   _debug_array($field_config);
   
   $tplsav2->config = unserialize(base64_decode($field_config[field_plugins]));
   $tplsav2->config = $tplsav2->config[conf];

   $BASE_DIR = $sessdata[UploadImageBaseDir];
   if($BASE_DIR == '')
   {
	  if($tplsav2->config['subdir'])
	  {
		 $subdir='/'.$tplsav2->config['subdir']; 
	  }
	  $BASE_DIR = $bo->cur_upload_path().$subdir;
	  if(!is_dir($BASE_DIR))
	  {
		 mkdir($BASE_DIR);
	  }
   }
   $BASE_URL = $sessdata[UploadImageBaseURL];
   if($BASE_URL == '') $BASE_URL = $bo->cur_upload_url().'/'.$tplsav2->config['subdir'];
   $MAX_HEIGHT = $sessdata[UploadImageMaxHeight];
   $MAX_WIDTH = $sessdata[UploadImageMaxWidth];
   if(!$MAX_HEIGHT) $MAX_HEIGHT = $tplsav2->config['Max_image_height'];
   if(!$MAX_WIDTH) $MAX_WIDTH = $tplsav2->config['Max_image_width'];

   $BASE_ROOT = '';
   $IMG_ROOT = $BASE_ROOT;
   if(strrpos($BASE_DIR, '/')!= strlen($BASE_DIR)-1) 
   $BASE_DIR .= '/';

   $tplsav2->no_dir = false;

   if(!is_dir($BASE_DIR)) 
   {
	  $tplsav2->no_dir = true;
   }

   $tplsav2->BASE_DIR = &$BASE_DIR;

   function dirs2($dir,$abs_path) 
   {
	  $d = dir($dir);
	  $dirs = array();
	  while (false !== ($entry = $d->read())) 
	  {
		 if(is_dir($dir.'/'.$entry) && substr($entry,0,1) != '.') 
		 {
			$path['path'] = $dir.'/'.$entry;
			$path['name'] = $entry;
			$dirs[$entry] = $path;
		 }
	  }
	  $d->close();

	  ksort($dirs);
	  for($i=0; $i<count($dirs); $i++) 
	  {
		 $name = key($dirs);
		 $current_dir = $abs_path.'/'.$dirs[$name]['name'];
		 echo "<option value=\"$current_dir\">$current_dir</option>\n";
		 dirs2($dirs[$name]['path'],$current_dir);
		 next($dirs);
	  }
   }

   $tplsav2->display('filemanager.popup_main.tpl.php');
?>
