<?php
   /**************************************************************************\
   * eGroupWare - Filemanager Plugin for JiNN                                 *
   * http://www.eGroupWare.org                                                *
   * Written and (c) by Xiang Wei ZHUO <wei@zhuo.org>                         *
   * Modified for eGW by and (c) by Pim Snel <pim@lingewoud.nl>               *
   * --------------------------------------------                             *
   * This program is free software; you can redistribute it and/or modify it  *
   * under the terms of the GNU General Public License as published by the    *
   * Free Software Foundation; version 2 of the License.                      *
   * --------------------------------------------                             *
   * Title.........:	Image Manager, draws the thumbnails and directies     *
   * Version.......:	1.01                                                  *
   * Author........:	Xiang Wei ZHUO <wei@zhuo.org>                         *
   * Notes.........:	Configuration in config.inc.php                       *
   *                                                                          *
   * Functions                                                                *
   * - create a new folder,                                                   *
   * - delete folder,                                                         *
   * - upload new image                                                       *
   * - use cached thumbnail views                                             *
   \**************************************************************************/

   // FIXME move all php functions to a main file
   // FIXME better directory-structure
   // FIXME: remove imageMagick shit, we only use gdlib
   // FIXME: autodetect safe_mode
   // FIXME set current app to the calling app

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
   require_once 'Transform.php';
   require_once 'class.filetypes.php';

   define('IMAGE_CLASS', 'GD');  

   //In safe mode, directory creation is not permitted.
   $SAFE_MODE = false;

   $sessdata =	$GLOBALS['phpgw']->session->appsession('UploadImage','phpgwapi');

   $bo = CreateObject('jinn.bouser');

   if($_GET[curr_obj_id])
   {
	  $field_config = $bo->so->get_field_values($_GET[curr_obj_id],$_GET[field]);
   }
   else
   {
	  $field_config = $bo->so->get_field_values($bo->session['site_object_id'],$_GET[field]);
   }
   $config = unserialize(base64_decode($field_config[field_plugins]));
   $config = $config[conf];

   $BASE_DIR = $sessdata[UploadImageBaseDir];
   if($BASE_DIR == '')
   {
	  if($config['subdir'])
	  {
		 $subdir='/'.$config['subdir']; 
	  }
	  $BASE_DIR = $bo->cur_upload_path().$subdir;
	  if(!is_dir($BASE_DIR))
	  {
		 mkdir($BASE_DIR);
	  }
   }
   $BASE_URL = $sessdata[UploadImageBaseURL];
   if($BASE_URL == '') $BASE_URL = $bo->cur_upload_url().'/'.$config['subdir'];
   $MAX_HEIGHT = $sessdata[UploadImageMaxHeight];
   $MAX_WIDTH = $sessdata[UploadImageMaxWidth];
   if(!$MAX_HEIGHT) $MAX_HEIGHT = $config['Max_image_height'];
   if(!$MAX_WIDTH) $MAX_WIDTH = $config['Max_image_width'];

   $BASE_ROOT = '';
   $IMG_ROOT = $BASE_ROOT;
   if(strrpos($BASE_DIR, '/')!= strlen($BASE_DIR)-1) 
   $BASE_DIR .= '/';

   //for thumbs funcs
   $img = $BASE_DIR.urldecode($_GET['img']);

   //   die($img);
   _debug_array($_GET);
   if(is_file($img)) {
	  make_thumbs(urldecode($_GET['img']));
	  exit;
   }
   //end for thumbs funcs
