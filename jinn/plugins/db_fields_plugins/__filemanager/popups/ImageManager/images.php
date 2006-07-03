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

   /* $Id */

   // FIXME move all php functions to a main file
   // FIXME better directory-structure

   include 'config.inc.php';
   require_once 'std_functions.inc.php';
   require_once '../ImageEditor/Transform.php';
   require_once '../../class.filetypes.php';

 
   //for thumbs funcs
   $img = $BASE_DIR.urldecode($_GET['img']);

   if(is_file($img)) {
	  make_thumbs(urldecode($_GET['img']));
	  exit;
   }
   //end for thumbs funcs

   $filetypes = new filetypes();
   $extensions = $filetypes->get_extensions($config['Filetype']);

   if(isset($_GET['dir'])) {
	  $dirParam = $_GET['dir'];

	  if(strlen($dirParam) > 0) 
	  {
		 if(substr($dirParam,0,1)=='/') 
		 $IMG_ROOT .= $dirParam;		
		 else
		 $IMG_ROOT = $dirParam;			
	  }	
   }

   $refresh_dirs = false;
   $clearUploads = false;
   $select_image_after_upload = '';
   $select_other_after_upload = '';

   if(strrpos($IMG_ROOT, '/')!= strlen($IMG_ROOT)-1) 
   $IMG_ROOT .= '/';

   if(isset($_GET['create']) && isset($_GET['dir']) && $SAFE_MODE == false) 
   {
	  create_folder();	
   }

   if(isset($_GET['delFile']) && isset($_GET['dir'])) 
   {
	  delete_file($_GET['delFile']);	
   }

   if(isset($_GET['delFolder']) && isset($_GET['dir'])) 
   {
	  delete_folder($_GET['delFolder']);	
   }

   if(isset($_FILES['upload']) && is_array($_FILES['upload']) && isset($_POST['dirPath'])) 
   {

	  $dirPathPost = $_POST['dirPath'];

	  if(strlen($dirPathPost) > 0) 
	  {
		 if(substr($dirPathPost,0,1)=='/') 
		 $IMG_ROOT = $dirPathPost;		
		 else
		 $IMG_ROOT .= $dirPathPost;			
	  }

	  if(strrpos($IMG_ROOT, '/')!= strlen($IMG_ROOT)-1) 
	  $IMG_ROOT .= '/';

	  do_upload($_FILES['upload'], $BASE_DIR.$dirPathPost.'/');
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

	  //createminithumb

	  if($w <= $nw && $h <= $nh) 
	  {
		 if(!is_file($BASE_PATH.$path.'.'.$img_file));
		 {
			copy($BASE_DIR.$path.$img_file,$BASE_DIR.$path.'.'.$img_file);
		 }
		 header('Location: '.$BASE_URL.$path.'.'.$img_file);
		 exit();		
	  }

	  if(is_file($BASE_DIR.$thumb)) {

		 $t_mtime = filemtime($BASE_DIR.$thumb);
		 $o_mtime = filemtime($BASE_DIR.$img);

		 if($t_mtime > $o_mtime) {
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

   function do_upload($file, $dest_dir) 
   {
	  global $clearUploads, $config, $select_image_after_upload, $select_other_after_upload, $dirPathPost, $BASE_URL;

	  //_debug_array($config);
	  //die();
	  //_debug_array($file);
	  if(is_file($file['tmp_name'])) 
	  {

		 if($config['Filetype']=='all')
		 {
			move_uploaded_file($file['tmp_name'], $dest_dir.$file['name']);	
			chmod($dest_dir.$file['name'], 0666);
			$select_other_after_upload = $BASE_URL.$dirPathPost.'/'.$file['name'];
		 }
		 else
		 {
			$img_info = getimagesize($file['tmp_name']);

			if(is_array($img_info) && $img_info['mime'] != 'application/x-shockwave-flash') 
			//this is a valid image
			{
			   global $filetypes;
			   $type = $filetypes->GD_type($config['Image_filetype']);
			   //_debug_array($config);
			   //echo $config['Image_filetype'];
			   //die( $type);

			   $w = $img_info[0]; 
			   $h = $img_info[1];

			   $dest_file = match_extension($dest_dir.'..'.$file['name'], $type);
			   process_and_save_image($file['tmp_name'], $dest_file, 30, 20, $type); //JiNN list view mini thumbnails

			   if($_POST[thumb]=='true')
			   {
				  if(!$_POST[thumbwidth]) $_POST[thumbwidth] = 10000;
				  if(!$_POST[thumbheight]) $_POST[thumbheight] = 10000;
				  $prefix='.thumb_01_';
				  $dest_file = match_extension($dest_dir.$prefix.$file['name'], $type);
				  process_and_save_image($file['tmp_name'], $dest_file, $_POST[thumbwidth], $_POST[thumbheight], $type);
			   }

			   if(!$_POST[width]) $_POST[width] = 10000;
			   if(!$_POST[height]) $_POST[height] = 10000;
			   if( $w > $_POST[width] || $h > $_POST[height] )
			   {
				  $dest_file = match_extension($dest_dir.$file['name'], $type);
				  process_and_save_image($file['tmp_name'], $dest_file, $_POST[width], $_POST[height], $type);
				  $select_image_after_upload = match_extension($BASE_URL.$dirPathPost.'/'.$file['name'], $type);
			   }
			   else
			   {
				  $dest_file = match_extension($dest_dir.$file['name'], $type);
				  process_and_save_image($file['tmp_name'], $dest_file, $w, $h, $type);
				  $select_image_after_upload = match_extension($BASE_URL.$dirPathPost.'/'.$file['name'], $type);
			   }
			}
			elseif($img_info['mime']=='application/x-shockwave-flash')
			// flash files
			{
			   move_uploaded_file($file['tmp_name'], $dest_dir.$file['name']);
			   chmod($dest_dir.$file['name'], 0666);
			   $select_other_after_upload = $BASE_URL.$dirPathPost.'/'.$file['name'];
			}
			else 
			//other unknown filetype
			{
			   move_uploaded_file($file['tmp_name'], $dest_dir.$file['name']);	
			   chmod($dest_dir.$file['name'], 0666);
			   $select_other_after_upload = $BASE_URL.$dirPathPost.'/'.$file['name'];
			}
		 }
	  }
	  $clearUploads = true;
   }


   function process_and_save_image($img,$dest_file, $nw, $nh, $type) 
   {
	  global $BASE_DIR, $BASE_URL;

	  $path_info = pathinfo($img);
	  $path = $path_info['dirname']."/";
	  $img_file = $path_info['basename'];

	  $img_info = getimagesize($path.$img_file);
	  $w = $img_info[0]; $h = $img_info[1];

	  $img_resize = Image_Transform::factory(IMAGE_CLASS);
	  $img_resize->load($path.$img_file);

	  $pw = (real)percent($nw, $w);
	  $ph = (real)percent($nh, $h);

	  if($pw < $ph)
	  {
		 $nh = round(unpercent($pw, $h));
		 $nw = round(unpercent($pw, $w)); 
	  }
	  else
	  {
		 $nh = round(unpercent($ph, $h));          
		 $nw = round(unpercent($ph, $w)); 
	  }

	  if($w != $nw || $h != $nh)
	  {
		 $img_resize->resize($nw, $nh);
	  }
	  $img_resize->save($dest_file, $type);
	  $img_resize->free();

	  chmod($dest_file, 0666);
   }

   function match_extension($filename, $type)
   {
	  //change the file extension so it matches the filetype
	  $exploded = explode('.', $filename);
	  $exploded[count($exploded)-1] = $type;
	  return (implode('.', $exploded));
   }

   function delete_folder($folder) 
   {
	  global $BASE_DIR, $refresh_dirs;

	  $del_folder = dir_name($BASE_DIR).$folder;
	  //_debug_array($del_folder);	  
	  if(is_dir($del_folder) && num_files($del_folder) <= 0) 
	  {
		 rm_all_dir($del_folder);
		 $refresh_dirs = true;
	  }
   }

   function rm_all_dir($dir) 
   {
	  //$dir = dir_name($dir);
	  //echo "OPEN:".$dir.'<Br>';
	  if(is_dir($dir)) 
	  {
		 $d = @dir($dir);

		 while (false !== ($entry = $d->read())) 
		 {
			//echo "#".$entry.'<br>';
			if($entry != '.' && $entry != '..') 
			{
			   $node = $dir.'/'.$entry;
			   //echo "NODE:".$node;
			   if(is_file($node)) {
				  //echo " - is file<br>";
				  unlink($node);
			   }
			   else if(is_dir($node)) {
				  //echo " -	is Dir<br>";
				  rm_all_dir($node);
			   }
			}
		 }
		 $d->close();

		 rmdir($dir);
	  }
	  //echo "RM: $dir <br>";
   }

   function delete_file($file) 
   {
	  global $BASE_DIR,$IMG_ROOT;

	  $del_image = dir_name($BASE_DIR).$IMG_ROOT.$file;
	  $del_thumb = dir_name($BASE_DIR).$IMG_ROOT.'.'.$file;
	  $del_mini = dir_name($BASE_DIR).$IMG_ROOT.'..'.$file;
	  $del_thumb_01 = dir_name($BASE_DIR).$IMG_ROOT.'.thumb_01_'.$file;

	  if(is_file($del_image)) 
	  {
		 unlink($del_image);	
	  }

	  if(is_file($del_thumb)) 
	  {
		 unlink($del_thumb);	
	  }

	  if(is_file($del_mini)) 
	  {
		 unlink($del_mini);	
	  }

	  if(is_file($del_thumb_01)) 
	  {
		 unlink($del_thumb_01);	
	  }
   }

   function create_folder() 
   {
	  global $BASE_DIR, $IMG_ROOT, $refresh_dirs;

	  $folder_name = $_GET['foldername'];

	  if(strlen($folder_name) >0) 
	  {
		 $folder = $BASE_DIR.$IMG_ROOT.$folder_name;

		 if(!is_dir($folder) && !is_file($folder))
		 {
			mkdir($folder,0777);	
			chmod($folder,0777);
			$refresh_dirs = true;
		 }
	  }
   }

   function num_files($dir) 
   {
	  $total = 0;

	  if(is_dir($dir)) 
	  {
		 $d = @dir($dir);

		 while (false !== ($entry = $d->read())) 
		 {
			//echo $entry."<br>";
			if(substr($entry,0,1) != '.') {
			   $total++;
			}
		 }
		 $d->close();
	  }
	  return $total;
   }

   function dirs($dir,$abs_path) 
   {
	  $d = dir($dir);
	  //echo "Handle: ".$d->handle."<br>\n";
	  //echo "Path: ".$d->path."<br>\n";
	  $dirs = array();
	  while (false !== ($entry = $d->read())) {
		 if(is_dir($dir.'/'.$entry) && substr($entry,0,1) != '.') 
		 {
			//dirs($dir.'/'.$entry, $prefix.$prefix);
			//echo $prefix.$entry."<br>\n";
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
		 echo ", \"$current_dir\"\n";
		 dirs($dirs[$name]['path'],$current_dir);
		 next($dirs);
	  }
   }

   function parse_size($size) 
   {
	  if($size < 1024) 
	  return $size.' btyes';	
	  else if($size >= 1024 && $size < 1024*1024) 
	  {
		 return sprintf('%01.2f',$size/1024.0).' Kb';	
	  }
	  else
	  {
		 return sprintf('%01.2f',$size/(1024.0*1024)).' Mb';	
	  }
   }

   function show_image($img, $file, $info, $size) 
   {
	  global $BASE_DIR, $BASE_URL, $newPath, $extensions;

	  $img_path = dir_name($img);
	  $img_file = basename($img);
	  $thumb_image = 'images.php?img='.urlencode($img);
	  $img_url = $BASE_URL.$img_path.'/'.$img_file;
	  $filesize = parse_size($size);
	  $file_arr = explode('.', $file);
	  $file_ext = $file_arr[count($file_arr)-1];
	  if($extensions[$file_ext] || $extensions['*'])
	  {
	  ?>
	  <td>
		 <table width="102" border="0" cellpadding="0" cellspacing="2">
			<tr> 
			   <td align="center" class="imgBorder" onMouseOver="pviiClassNew(this,'imgBorderHover')" onMouseOut="pviiClassNew(this,'imgBorder')">
				  <a href="javascript:;" onClick="javascript:imageSelected('<? echo $img_url; ?>', <? echo $info[0];?>, <? echo $info[1]; ?>,'<? echo $file; ?>');"><img src="<? echo $thumb_image; ?>" alt="<? echo $file; ?> - <? echo $filesize; ?>" border="0"></a></td>
			</tr>
			<tr> 
			   <td><table width="100%" border="0" cellspacing="0" cellpadding="2">
					 <tr> 
						<td width="1%" class="buttonOut" onMouseOver="pviiClassNew(this,'buttonHover')" onMouseOut="pviiClassNew(this,'buttonOut')">
						   <a href="javascript:;" onClick="javascript:preview('<? echo $img_url; ?>', '<? echo $file; ?>', ' <? echo $filesize; ?>',<? echo $info[0].','.$info[1]; ?>);"><img src="edit_pencil.gif" width="15" height="15" border="0"></a></td>
						<td width="1%" class="buttonOut" onMouseOver="pviiClassNew(this,'buttonHover')" onMouseOut="pviiClassNew(this,'buttonOut')">
						   <a href="images.php?field=<?php echo($_GET['field']); ?>&curr_obj_id=<?=$_GET[curr_obj_id]?>&delFile=<? echo $file; ?>&dir=<? echo $newPath; ?>" onClick="return deleteImage('<? echo $file; ?>');"><img src="edit_trash.gif" width="15" height="15" border="0"></a></td>
						<td width="98%" class="imgCaption"><? echo $info[0].'x'.$info[1]; ?> <? //echo $file_ext; ?></td>
					 </tr>
			   </table></td>
			</tr>
		 </table>
	  </td>
	  <?php
	  }
   }

   function show_flash($img, $file, $info, $size)
   {
	  global $BASE_DIR, $BASE_URL, $newPath, $extensions;

	  $img_path = dir_name($img);
	  $img_url = $BASE_URL.$img_path.'/'.$file;
	  $thumb_image = 'flash.png';
	  $file_arr = explode('.', $file);
	  $file_ext = $file_arr[count($file_arr)-1];
	  $file_spec = @GetImageSize($BASE_DIR.$file);
	  $file_width = ($file_spec[0]>=$file_spec[1]) ? 96 : round($file_spec[0]/($file_spec[1]/96)) ;
	  $file_height = ($file_spec[1]>=$file_spec[0]) ? 96 : round($file_spec[1]/($file_spec[0]/96)) ;
	  if($extensions[$file_ext] || $extensions['*'])
	  {
	  ?>
	  <td>
		 <table width="102" border="0" cellpadding="0" cellspacing="2">
			<tr> 
			   <td align="center" class="imgBorder" onMouseOver="pviiClassNew(this,'imgBorderHover')" onMouseOut="pviiClassNew(this,'imgBorder')">
				  <a href="javascript:;" onClick="javascript:otherSelected('<? echo $img_url; ?>');"><div>
						<script language="JavaScript" type="text/JavaScript" src="../flash.js"></script>
						<script language="JavaScript" type="text/JavaScript">
						   <!--
						   if(flashcompattest()==true)
						   {
								 document.write('<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"'+
									'codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0"'+
									'width="<? echo $file_width; ?>"'+
									'height="<? echo $file_height; ?>"'+
									'id="<? echo $file; ?>">'+
									'<param name="movie"   	value="<? echo $img_url; ?>">'+
									'<param name="menu"    	value="false">'+
									'<param name="quality" 	value="high">'+
									'<param name="loop" 	value="true">'+
									'<param name="scale" 	value="exactfit">'+
									'<param name"wmode" 	value="transparent">'+
									'<param name="play" 	value="false">'+
									'<embed src="<? echo $img_url; ?>"'+
									'menu="false"'+
									'quality="high"'+
									'loop="true"'+
									'scale="exactfit"'+
									'wmode="transparent"'+
									'play="false"'+								 
									'swLiveConnect="true"'+
									'width="<? echo $file_width; ?>"'+
									'height="<? echo $file_height; ?>"'+
									'name="<? echo $file; ?>"'+
									'type="application/x-shockwave-flash"'+
									'pluginspage="http://www.macromedia.com/go/getflashplayer">'+
									'</embed>'+
									'</object>');
						   }
						   else
						   {
								 document.write('<img src="<? echo $thumb_image; ?>" alt="<? echo $file; ?> - <? echo $filesize; ?>" border="0">');
						   }
						   -->
					 </script></div>
				  </a>
			   </td>
			</tr>
			<tr> 
			   <td><table width="100%" border="0" cellspacing="0" cellpadding="2">
					 <tr> 
						<td width="1%" class="buttonOut" onMouseOver="pviiClassNew(this,'buttonHover')" onMouseOut="pviiClassNew(this,'buttonOut')">
						   <script language="JavaScript" type="text/JavaScript">
							  <!--
							  if(flashcompattest()==true)
							  {
									document.write('<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"'+
									   'codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0"'+
									   'width="15"'+
									   'height="15"'+
									   'id="startstop">'+
									   '<param name="movie"   	value="startstop.swf?movieName=<? echo $file; ?>">'+
									   '<param name="menu"    	value="false">'+
									   '<param name="quality" 	value="high">'+
									   '<param name="loop" 	value="false">'+
									   '<param name="scale" 	value="exactfit">'+
									   '<param name="wmode" 	value="transparent">'+
									   '<param name="play" 	value="false">'+
									   '<embed src="startstop.swf?movieName=<? echo $file; ?>"'+
									   'menu="false"'+
									   'quality="high"'+
									   'loop="false"'+
									   'scale="exactfit"'+
									   'wmode="transparent"'+
									   'play="false"'+																		
									   'width="15"'+
									   'height="15"'+
									   'name="startstop"'+
									   'type="application/x-shockwave-flash"'+
									   'pluginspage="http://www.macromedia.com/go/getflashplayer">'+
									   '</embed>'+
									   '</object>');
							  }
							  -->
						   </script>
						</td>
						<td width="1%" class="buttonOut" onMouseOver="pviiClassNew(this,'buttonHover')" onMouseOut="pviiClassNew(this,'buttonOut')">
						   <a href="images.php?field=<?php echo($_GET['field']); ?>&curr_obj_id=<?=$_GET[curr_obj_id]?>&delFile=<? echo $file; ?>&dir=<? echo $newPath; ?>" onClick="return deleteImage('<? echo $file; ?>');"><img src="edit_trash.gif" width="15" height="15" border="0"></a></td>
						<td width="98%" class="imgCaption"><a href="javascript:;" onClick="javascript:otherSelected('<? echo $img_url; ?>');"><? echo $file; ?> <? //echo $file_ext; ?></a></td>
					 </tr>
			   </table></td>
			</tr>
		 </table>
	  </td>
	  <?php
	  }
   }



   function show_other($img, $file, $info, $size) 
   {
	  global $BASE_DIR, $BASE_URL, $newPath, $extensions;

	  $img_path = dir_name($img);
	  $img_url = $BASE_URL.$img_path.'/'.$file;
	  $thumb_image = 'unknown.png';
	  $file_arr = explode('.', $file);
	  $file_ext = $file_arr[count($file_arr)-1];
	  if($extensions[$file_ext] || $extensions['*'])
	  {
	  ?>
	  <td>
		 <table width="102" border="0" cellpadding="0" cellspacing="2">
			<tr> 
			   <td align="center" class="imgBorder" onMouseOver="pviiClassNew(this,'imgBorderHover')" onMouseOut="pviiClassNew(this,'imgBorder')">
				  <a href="javascript:;" onClick="javascript:otherSelected('<? echo $img_url; ?>');"><img src="<? echo $thumb_image; ?>" alt="<? echo $file; ?> - <? echo $filesize; ?>" border="0"></a></td>
			</tr>
			<tr> 
			   <td><table width="100%" border="0" cellspacing="0" cellpadding="2">
					 <tr> 
						<!--td width="1%" class="buttonOut" onMouseOver="pviiClassNew(this,'buttonHover')" onMouseOut="pviiClassNew(this,'buttonOut')">
						<a href="javascript:;" onClick="javascript:preview('<? echo $img_url; ?>', '<? echo $file; ?>', ' <? echo $filesize; ?>',<? echo $info[0].','.$info[1]; ?>);"><img src="edit_pencil.gif" width="15" height="15" border="0"></a></td-->
						<td width="1%" class="buttonOut" onMouseOver="pviiClassNew(this,'buttonHover')" onMouseOut="pviiClassNew(this,'buttonOut')">
						   <a href="images.php?field=<?php echo($_GET['field']); ?>&curr_obj_id=<?=$_GET[curr_obj_id]?>&delFile=<? echo $file; ?>&dir=<? echo $newPath; ?>" onClick="return deleteImage('<? echo $file; ?>');"><img src="edit_trash.gif" width="15" height="15" border="0"></a></td>
						<td width="98%" class="imgCaption"><? echo $file; ?> <? //echo $file_ext; ?></td>
					 </tr>
			   </table></td>
			</tr>
		 </table>
	  </td>
	  <?php
	  }
   }

   function show_dir($path, $dir) 
   {
	  global $newPath, $BASE_DIR, $BASE_URL;

	  $num_files = num_files($BASE_DIR.$path);
   ?>
   <td>
	  <table width="102" border="0" cellpadding="0" cellspacing="2">
		 <tr> 
			<td align="center" class="imgBorder" onMouseOver="pviiClassNew(this,'imgBorderHover')" onMouseOut="pviiClassNew(this,'imgBorder')">
			   <a href="images.php?field=<?php echo($_GET['field']); ?>&curr_obj_id=<?=$_GET[curr_obj_id]?>&dir=<? echo $path; ?>" onClick="changeLoadingStatus('load')">
				  <img src="folder.gif" width="80" height="80" border=0 alt="<? echo $dir; ?>">
			   </a>
			</td>
		 </tr>
		 <tr> 
			<td><table width="100%" border="0" cellspacing="1" cellpadding="2">
				  <tr> 
					 <td width="1%" class="buttonOut" onMouseOver="pviiClassNew(this,'buttonHover')" onMouseOut="pviiClassNew(this,'buttonOut')">
						<a href="images.php?field=<?php echo($_GET['field']); ?>&curr_obj_id=<?=$_GET[curr_obj_id]?>&delFolder=<? echo $path; ?>&dir=<? echo $newPath; ?>" onClick="return deleteFolder('<? echo $dir; ?>', <? echo $num_files; ?>);"><img src="edit_trash.gif" width="15" height="15" border="0"></a></td>
					 <td width="99%" class="imgCaption"><? echo $dir; ?></td>
				  </tr>
			</table></td>
		 </tr>
	  </table>
   </td>
   <?	
}

function draw_no_results() 
{
?>
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
   <tr>
	  <td><div align="center" style="font-size:large;font-weight:bold;color:#CCCCCC;font-family: Helvetica, sans-serif;">No Images Found</div></td>
   </tr>
</table>
<?	
}

function draw_no_dir() 
{
   global $BASE_DIR;
?>
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
   <tr>
	  <td><div align="center" style="font-size:small;font-weight:bold;color:#CC0000;font-family: Helvetica, sans-serif;">Configuration Problem: &quot;<? echo $BASE_DIR; ?>&quot; does not exist.</div></td>
   </tr>
</table>
<?	
}


function draw_table_header() 
{
   echo '<table border="0" cellpadding="0" cellspacing="2">';
	  echo '<tr>';
	  }

	  function draw_table_footer() 
	  {
		 echo '</tr>';
	  echo '</table>';
}

?>
<html>
   <head>
	  <title>Image Browser</title>
	  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	  <style type="text/css">
		 <!--
		 .imgBorder {
			   height: 96px;
			   border: 1px solid threedface;
			   vertical-align: middle;
		 }
		 .imgBorderHover {
			   height: 96px;
			   border: 1px solid threedface;
			   vertical-align: middle;
			   background: #FFFFCC;
			   cursor: hand;
		 }

		 .buttonHover {
			   border: 1px solid;
			   border-color: ButtonHighlight ButtonShadow ButtonShadow ButtonHighlight;
			   cursor: hand;
			   background: #FFFFCC;
		 }
		 .buttonOut
		 {
			   border: 1px solid;
			   border-color: white;
		 }

		 .imgCaption {
			   font-size: 9pt;
			   font-family: "MS Shell Dlg", Helvetica, sans-serif;
			   text-align: center;
		 }
		 .dirField {
			   font-size: 9pt;
			   font-family: "MS Shell Dlg", Helvetica, sans-serif;
			   width:110px;
		 }

		 -->
	  </style>
	  <?php
		 //		  $dirPath = eregi_replace($BASE_ROOT,'',$IMG_ROOT);
		 $dirPath=$IMG_ROOT;

		 $paths = explode('/', $dirPath);
		 $upDirPath = '/';
		 for($i=0; $i<count($paths)-2; $i++) 
		 {
			$path = $paths[$i];
			if(strlen($path) > 0) 
			{
			   $upDirPath .= $path.'/';
			}
		 }

		 $slashIndex = strlen($dirPath);
		 $newPath = $dirPath;
		 if($slashIndex > 1 && substr($dirPath, $slashIndex-1, $slashIndex) == '/')
		 {
			$newPath = substr($dirPath, 0,$slashIndex-1);
		 }
	  ?>
	  <script type="text/javascript" src="../popup.js"></script>
	  <script type="text/javascript" src="../../../../dialog.js"></script>
	  <script language="JavaScript" type="text/JavaScript">
		 <!--
		 function pviiClassNew(obj, new_style) { //v2.6 by PVII
			obj.className=new_style;
	  }

	  function goUp() 
	  {
			location.href = "ImageManager/images.php?field=<?php echo($_GET['field']); ?>&curr_obj_id=<?=$_GET[curr_obj_id]?>&dir=<? echo $upDirPath; ?>";
	  }

	  function changeDir(newDir) 
	  {
			location.href = "ImageManager/images.php?field=<?php echo($_GET['field']); ?>&curr_obj_id=<?=$_GET[curr_obj_id]?>&dir="+newDir;
	  }

	  function newFolder(oldDir, newFolder) 
	  {
			//location.href = "ImageManager/images.php?dir="+oldDir+'&create=folder&foldername='+newFolder;
			location.href = "images.php?field=<?php echo($_GET['field']); ?>&curr_obj_id=<?=$_GET[curr_obj_id]?>&dir="+oldDir+'&create=folder&foldername='+newFolder;
	  }

	  function updateDir() 
	  {

			<?php if($select_image_after_upload!='') : ?>
			imageSelected("<?php echo $select_image_after_upload; ?>",0,0,0);
			<?php endif ?>
			<?php if($select_other_after_upload!='') : ?>
			otherSelected("<?php echo $select_other_after_upload; ?>");
			<?php endif ?>

			var newPath = "<?php echo $newPath; ?>";
			//alert('<?php echo $newPath; ?>');
			if(window.top.document.forms[0] != null) {

				  var allPaths = window.top.document.forms[0].dirPath.options;
				  //alert("new:"+newPath);
				  for(i=0; i<allPaths.length; i++) 
				  {
						//alert(allPaths.item(i).value);
						allPaths.item(i).selected = false;
						if((allPaths.item(i).value)==newPath) 
						{
							  allPaths.item(i).selected = true;
						}
				  }

				  <?
				  if($clearUploads) {
				  ?>
				  var topDoc = window.top.document.forms[0];
				  topDoc.upload.value = null;
				  //topDoc.upload.disabled = true;
				  <?
			   }
			?>

	  }

}

<? if ($refresh_dirs) { ?>
function refreshDirs() 
{
   var allPaths = window.top.document.forms[0].dirPath.options;
   var fields = ["/" <? dirs($BASE_DIR,'');?>];

   var newPath = "<? echo $newPath; ?>";

   allPaths.length=0;

   for(i=0; i<fields.length; i++) 
   {
		 var newElem =	document.createElement("OPTION");
		 var newValue = fields[i];
		 newElem.text = newValue;
		 newElem.value = newValue;

		 if(newValue == newPath) 
		 newElem.selected = true;	
		 else
		 newElem.selected = false;

		 allPaths.add(newElem);
   }
	  }
	  refreshDirs();
	  <? } ?>

	  function imageSelected(filename, width, height, alt) 
	  {
			var topDoc = window.top.document.forms[0];
			topDoc.f_url.value = filename;
			topDoc.f_type.value = '<?php echo $filetypes->type_id_image; ?>';
			//topDoc.f_width.value= width;
			//topDoc.f_height.value = height;
			//topDoc.f_alt.value = alt;
			//topDoc.orginal_width.value = width;
			//topDoc.orginal_height.value = height;

	  }
	  function otherSelected(filename) 
	  {
			var topDoc = window.top.document.forms[0];
			topDoc.f_url.value = filename;
			topDoc.f_type.value = '<?php echo $filetypes->type_id_other; ?>';
	  }

	  function preview(file, image, size, width, height) 
	  {


			alert('Not implemented yet,sorry');
			return;	


			/*
			var predoc = '<img src="'+file+'" alt="'+image+' ('+width+'x'+height+', '+size+')">';
			var w = 450;
			var h = 400;
			var LeftPosition=(screen.width)?(screen.width-w)/2:100;
			var TopPosition=(screen.height)?(screen.height-h)/2:100;

			var win = window.open('','image_preview','toolbar=no,location=no,menubar=no,status=yes,scrollbars=yes,resizable=yes,width='+w+',height='+h+',top='+TopPosition+',left='+LeftPosition);
			var doc=win.document.open();

			doc.writeln('<html>\n<head>\n<title>Image Preview - '+image+' ('+width+'x'+height+', '+size+')</title>');
				  doc.writeln('</head>\n<body>');
				  doc.writeln(predoc);
				  doc.writeln('</body>\n</html>\n');
			doc=win.document.close();
			win.focus();*/
			//alert(file);
			Dialog("../ImageEditor/ImageEditor.php?img="+escape(file), function(param) {
				  if (!param) {	// user must have pressed Cancel
					 return false;
			   }
		 }, null);
		 return;
   }

   function deleteImage(file) 
   {
		 if(confirm("Delete image \""+file+"\"?")) 
		 return true;

		 return false;
   }

   function deleteFolder(folder, numFiles) 
   {
		 if(numFiles > 0) {
			   alert("There are "+numFiles+" files/folders in \""+folder+"\".\n\nPlease delete all files/folder in \""+folder+"\" first.");
			   return false;
		 }

		 if(confirm("Delete folder \""+folder+"\"?")) 
		 return true;

		 return false;
   }

   function MM_findObj(n, d) { //v4.01
	  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
			d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
		 if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
		 for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
		 if(!x && d.getElementById) x=d.getElementById(n); return x;
   }

   function MM_showHideLayers() { //v6.0
	  var i,p,v,obj,args=MM_showHideLayers.arguments;
	  for (i=0; i<(args.length-2); i+=3) if ((obj=MM_findObj(args[i],window.top.document))!=null) { v=args[i+2];
		 if (obj.style) { obj=obj.style; v=(v=='show')?'visible':(v=='hide')?'hidden':v; }
		 obj.visibility=v; }
   }

   function changeLoadingStatus(state) 
   {
		 var statusText = null;
		 if(state == 'load') {
			   statusText = 'Loading Images';	
		 }
		 else if(state == 'upload') {
			   statusText = 'Uploading Files';
		 }
		 if(statusText != null) {
			   var obj = MM_findObj('loadingStatus', window.top.document);
			   //alert(obj.innerHTML);
			   if (obj != null && obj.innerHTML != null)
			   obj.innerHTML = statusText;
			   MM_showHideLayers('loading','','show')		
		 }
	  }

	  //-->
   </script>
</head>
<body onLoad="updateDir();" bgcolor="#FFFFFF">

   <?php
	  $d = @dir($BASE_DIR.$IMG_ROOT);

	  if($d) 
	  {
		 $images = array();
		 $folders = array();
		 $other = array();
		 $flash = array();
		 while (false !== ($entry = $d->read())) 
		 {
			//echo $img_file;
			$img_file = $IMG_ROOT.$entry; 

			if(is_dir($BASE_DIR.$img_file) && substr($entry,0,1) != '.') 
			{
			   $folders[$entry] = $img_file;
			   //show_dir($img_file, $entry);	
			}
			elseif(is_file($BASE_DIR.$img_file) && substr($entry,0,1) != '.') 
			{
			   $image_info = @getimagesize($BASE_DIR.$img_file);
			   if(is_array($image_info) && $image_info['mime'] != 'application/x-shockwave-flash') 
			   {
				  $file_details['file'] = $img_file;
				  $file_details['img_info'] = $image_info;
				  $file_details['size'] = filesize($BASE_DIR.$img_file);
				  $images[$entry] = $file_details;
				  //show_image($img_file, $entry, $image_info);
			   }
			   elseif($image_info['mime']=='application/x-shockwave-flash')
			   // flash files
			   {
				  $flash[$entry] = $img_file;

			   }
			   else
			   {
				  $other[$entry] = $img_file;
			   }
			}
		 }
		 
		 $d->close();	

		 if(count($images) > 0 || count($folders) > 0 || count($other) > 0) 
		 {	
			//now sort the folders and images by name.
			ksort($images);
			ksort($folders);
			ksort($other);

			//_debug_array($other);
			//die();
			draw_table_header();

			for($i=0; $i<count($folders); $i++) 
			{
			   $folder_name = key($folders);		
			   show_dir($folders[$folder_name], $folder_name);
			   next($folders);
			}
			for($i=0; $i<count($images); $i++) 
			{
			   $image_name = key($images);
			   show_image($images[$image_name]['file'], $image_name, $images[$image_name]['img_info'], $images[$image_name]['size']);
			   next($images);
			}
			for($i=0; $i<count($flash); $i++) 
			{
			   $name = key($flash);
			   show_flash($flash[$name], $name, $flash[$name]['img_info'], $flash[$name]['size']);
			   next($flash);
			}
			for($i=0; $i<count($other); $i++) 
			{
			   $name = key($other);
			   show_other($other[$name], $name, $other[$name]['img_info'], $other[$name]['size']);
			   next($other);
			}
			draw_table_footer();
		 }
		 else
		 {
			draw_no_results();
		 }
	  }
	  else
	  {
		 draw_no_dir();
	  }

   ?>
   <script language="JavaScript" type="text/JavaScript">
	  MM_showHideLayers('loading','','hide')
   </script>
</body>
				  </html>
