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
   * --------------------------------------------                             *
   * Title.........:	Thumbnail generator, with cache.                      *
   * Version.......:	1.01                                                  *
   * Author........:	Xiang Wei ZHUO <wei@zhuo.org>                         *
   * Notes.........:	Configuration in config.inc.php                       *
   *                                                                          *
   * Functions                                                                *
   * - if the thumbnail does not exists or the source file is newer, create a *
   * new thumbnail.                                                           *
   \**************************************************************************/

   /* $id */

   require_once 'config.inc.php';
   require_once 'functions.php';
   require_once 'Transform.php';

   $img = $BASE_DIR.urldecode($_GET['img']);

   if(is_file($img)) {
	  make_thumbs(urldecode($_GET['img']));
   }

   function make_thumbs($img) 
   {
	  global $BASE_DIR, $BASE_URL;

	  $path_info = pathinfo($img);
	  $path = $path_info['dirname']."/";
	  $img_file = $path_info['basename'];

	  $thumb = $path.'.'.$img_file;

	  $img_info = getimagesize($BASE_DIR.$path.$img_file);
	  $w = $img_info[0]; $h = $img_info[1];

	  $nw = 96; $nh = 96;

	  if($w <= $nw && $h <= $nh) {
		 header('Location: '.$BASE_URL.$path.$img_file);
		 exit();		
	  }

	  if(is_file($BASE_DIR.$thumb)) {

		 $t_mtime = filemtime($BASE_DIR.$thumb);
		 $o_mtime = filemtime($BASE_DIR.$img);

		 if($t_mtime > $o_mtime) {
			//echo $BASE_URL.$path.'.'.$img_file;
			header('Location: '.$BASE_URL.$path.'.'.$img_file);
			exit();		
		 }
	  }

	  $img_thumbs = Image_Transform::factory(IMAGE_CLASS);
	  $img_thumbs->load($BASE_DIR.$path.$img_file);

	  if ($w > $h) 
	  $nh = unpercent(percent($nw, $w), $h);          
	  else if ($h > $w) 
	  $nw = unpercent(percent($nh, $h), $w); 

	  $img_thumbs->resize($nw, $nh);

	  $img_thumbs->save($BASE_DIR.$thumb);
	  $img_thumbs->free();

	  chmod($BASE_DIR.$thumb, 0666);

	  if(is_file($BASE_DIR.$thumb)) {
		 header('Location: '.$BASE_URL.$path.'.'.$img_file);
		 exit();
	  }
   }

?>
