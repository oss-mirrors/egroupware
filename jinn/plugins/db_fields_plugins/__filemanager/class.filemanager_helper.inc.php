<?php
   class filemanager_helper
   {
	  function filemanager_helper()
	  {}

	  function get_file_info($path)
	  {
		 if(!is_file($path))
		 {
			return $file_info_array['not_exist'] = true;
		 }

		 $image_info = getimagesize($path);

		 if(is_array($image_info))
		 {
			$file_info_array['simple_mime']='img';		
			$file_info_array['img_width']=$image_info[0];		
			$file_info_array['img_height']=$image_info[1];		
			$file_info_array['mime']=$image_info['mime'];	
			$file_info_array['img_info_arr']=$image_info;	
			if($image_info[2]<=3) //is gif,jpg or png
			{
			   $file_info_array['type_gifjpgpng']=true;		
			}
			elseif($file_info_array['mime'] == 'application/x-shockwave-flash')
			{
			   $file_info_array['type_flash']=true;	
			}
		 }
		 else
		 {
			$file_info_array['type_unknown']=true;	
		 }

		 return $file_info_array;
	  }

	  function  create_thumb($max_width=100,$max_height=100)
	  {}

   }
