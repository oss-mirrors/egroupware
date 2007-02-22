<?php

   function percent($p, $w) 
   { 
	  return (real)(100 * ($p / $w)); 
   } 

   function unpercent($percent, $whole) 
   { 
	  return (real)(($percent * $whole) / 100); 
   } 

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

   function make_thumbs($img) 
   {
	  global $BASE_DIR, $BASE_URL;

	  $path_info = pathinfo($img);
	  $path = $path_info['dirname']."/";
	  $img_file = $path_info['basename'];

	  $thumb = $path.'.'.$img_file;

	  if(is_file($BASE_DIR.$thumb)) 
	  {
		 $t_mtime = filemtime($BASE_DIR.$thumb);
		 $o_mtime = filemtime($BASE_DIR.$img);

		 if($t_mtime > $o_mtime) {
			header('Location: '.$BASE_URL.$path.'.'.$img_file);
			exit();		
		 }
	  }

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

	  $_tmpobj = new Image_Transform;
	  $img_thumbs = $_tmpobj->factory(IMAGE_CLASS);
	  //	  $img_thumbs = Image_Transform::factory(IMAGE_CLASS);
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

	  $_tmpobj = new Image_Transform;
	  $img_resize = $_tmpobj->factory(IMAGE_CLASS);
//	  $img_resize = Image_Transform::factory(IMAGE_CLASS);
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




?>
