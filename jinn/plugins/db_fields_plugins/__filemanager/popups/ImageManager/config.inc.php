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

   /* $id$ */

   // FIXME: remove imageMagick shit, we only use gdlib
   // FIXME: autodetect safe_mode
   // FIXME set current app to the calling app
   // FIXME include header nicer

   $phpgw_flags = Array(
	  'currentapp'	=>	'jinn',
	  'noheader'	=>	True,
	  'nonavbar'	=>	True,
	  'noappheader'	=>	True,
	  'noappfooter'	=>	True,
	  'nofooter'	=>	True
   );

   $GLOBALS['phpgw_info']['flags'] = $phpgw_flags;

   //fixme: this file is included twice, by parents on different levels, so the header to be included can be one of two different paths
   @include('../../../../../header.inc.php');
   @include('../../../../../../header.inc.php');

   define('IMAGE_CLASS', 'GD');  

   //In safe mode, directory creation is not permitted.
   $SAFE_MODE = false;

   $sessdata =	  $GLOBALS['phpgw']->session->appsession('UploadImage','phpgwapi');

   $bo = CreateObject('jinn.bouser');
//_debug_array($_GET[field]);
   $field_config = $bo->so->get_field_values($bo->session['site_object_id'],$_GET[field]);
   $config = unserialize(base64_decode($field_config[field_plugins]));
//_debug_array($config);
   $config = $config[conf];
//_debug_array($config);

		//for backwards compatibility:
	if($config[Image_filetype] != '')
	{
		$config[Filetype] = $config[Image_filetype];
	}

   $BASE_DIR = $sessdata[UploadImageBaseDir];
   if($BASE_DIR == '') $BASE_DIR = $bo->cur_upload_path();
   $BASE_URL = $sessdata[UploadImageBaseURL];
   if($BASE_URL == '') $BASE_URL = $bo->cur_upload_url();
   $MAX_HEIGHT = $sessdata[UploadImageMaxHeight];
   $MAX_WIDTH = $sessdata[UploadImageMaxWidth];
   if(!$MAX_HEIGHT) $MAX_HEIGHT = $config['Max_image_height'];
   if(!$MAX_WIDTH) $MAX_WIDTH = $config['Max_image_width'];
   //die();
   

   //After defining which library to use, if it is NetPBM or IM, you need to
   //specify where the binary for the selected library are. And of course
   //your server and PHP must be able to execute them (i.e. safe mode is OFF).
   //If you have safe mode ON, or don't have the binaries, your choice is
   //GD only. GD does not require the following definition.
   //define('IMAGE_TRANSFORM_LIB_PATH', '/usr/bin/netpbm/');
   //define('IMAGE_TRANSFORM_LIB_PATH', '"D:\\Program Files\\ImageMagick\\');

   $BASE_ROOT = '';
   $IMG_ROOT = $BASE_ROOT;
   if(strrpos($BASE_DIR, '/')!= strlen($BASE_DIR)-1) 
   $BASE_DIR .= '/';

/*
   if(strrpos($BASE_URL, '/')!= strlen($BASE_URL)-1) 
   $BASE_URL .= '/';
*/
   //Built in function of dirname is faulty
   //It assumes that the directory nane can not contain a . (period)
   function dir_name($dir) 
   {
	  $lastSlash = intval(strrpos($dir, '/'));
	  if($lastSlash == strlen($dir)-1){
		 return substr($dir, 0, $lastSlash);
	  }
	  else
	  return dirname($dir);
   }

?>
