<?php
   /**
   JiNN - A Database Application Development Toolkit
   Author:	Pim Snel for Lingewoud
   Copyright (C) 2007 Pim Snel <pim@lingewoud.nl>
   License http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   JiNN is part of eGroupWare - http://www.egroupware.org
   */

   /**
   * bogdlib 
   * 
   * @version $Id$
   * @copyright Lingewoud B.V.
   * @author Pim Snel <pim-AT-lingewoud-DOT-nl> 
   * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   */
   class bogdlib 
   {
	  var $verbose        = false;

	  /**
	  @function FileTypeSupport
	  @abstract checks if a filetype is supported by the current gdlib
	  @param $type string eg GIFRead, GIFwrite, GIF (GIFRead and GIFWrite), PNG, JPG, etc...
	  @returns boolian
	  */
	  function FileTypeSupport($type)
	  {
		 $support_arr=gd_info();

		 switch($type)
		 {
			case 'GIF':
			   if($support_arr["GIF Read Support"] && $support_arr["GIF Create Support"])
			   {
				  return true;
			   }	 
			   break;
			case 'GIFRead':
			   if($support_arr["GIF Read Support"])
			   {
				  return true;
			   }	 
			   break;
			case 'GIFWrite':
			   if($support_arr["GIF Create Support"])
			   {
				  return true;
			   }	 
			   break;
			case 'JPG':
			   if($support_arr["JPG Support"])
			   {
				  return true;
			   }	 
			   break;
			case 'PNG':
			   if($support_arr["PNG Support"])
			   {
				  return true;
			   }	 
			   break;

			default: 
			   return false;

		 }
	  }

	  function Resize( $maxwidth=10000, $maxheight,$imagename,$filetype,$how='keep_aspect') 
	  {
		 $target_temp_file = tempnam ("jinn/temp", "gdlib_");
		 unlink($target_temp_file);
		 $target_temp_file.='.'.$filetype;

		 if(!$maxheight)$maxheight=10000;
		 if(!$maxwidth)$maxwidth=10000;
		 $qual=100;
		 $filename=$imagename;
		 $ext=$filetype;		 

		 list($curwidth, $curheight) = getimagesize($filename);

		 $factor = min( ($maxwidth / $curwidth) , ($maxheight / $curheight) );

		 $sx		= 0;
		 $sy		= 0;
		 $sw		= $curwidth;
		 $sh		= $curheight;

		 $dx		= 0;
		 $dy		= 0;
		 $dw		= $curwidth * $factor;
		 $dh		= $curheight *  $factor;

		 //die('hallo');
		 if ($ext == "JPEG") 
		 { 
			$src = ImageCreateFromJPEG($filename); 
		 }

		 // FIXME gif doesn't work
		 if ($ext == "GIF") 
		 { 
			$src = ImageCreateFromGIF($filename); 
			//echo 'hallo';
		 }

		 if ($ext == "PNG") { $src = ImageCreateFromPNG($filename); }

		 if(function_exists('ImageCreateTrueColor')) {
			$dst = ImageCreateTrueColor($dw,$dh);
		 } else {
			$dst = ImageCreate($dw,$dh);
		 }

		 if(function_exists('ImageCopyResampled'))
		 {
			imageCopyResampled($dst,$src,$dx,$dy,$sx,$sy,$dw,$dh,$sw,$sh);
		 }
		 else
		 {
			imageCopyResized($dst,$src,$dx,$dy,$sx,$sy,$dw,$dh,$sw,$sh);
		 }

		 if($ext == "JPEG") ImageJPEG($dst,$target_temp_file,$qual);
		 if($ext == "PNG") ImagePNG($dst,$target_temp_file,$qual);
		 if($ext == "GIF") ImagePNG($dst,$target_temp_file,$qual);

		 ImageDestroy($dst);

		 return $target_temp_file;
	  }

	  function Get_Imagetype($file)
	  {
		 $type=exif_imagetype($file);

		 switch($type)
		 {
			case 1: return 'GIF';
			   break;
			case 2: return 'JPEG';
			   break;
			case 3: return 'PNG';
			   break;
			case 4: return 'SWF';
			   break;
			case 5: return 'PSD';
			   break;
			case 6: return 'BMP';
			   break;
			case 7: return 'TIFF_II';
			   break;
			case 8: return 'TIFF_MM';
			   break;
			case 9: return 'JPC';
			   break;
			case 10 :return 'JP2';
			   break;
			case 11 :return 'JPX';
			   break;
			case 12 :return 'JB2';
			   break;
			case 13 :return 'SWC';
			   break;
			case 14 :return 'IFF';
			   break;
			case 15 :return 'WBMP';
			   break;
			case 16 :return 'XBM';
			   break;
			default:
			   return false;
		 }

	  }

	  /**
	  * new_image_size check if image dimension are beyond limits and return new dimensions; 
	  *  return old dimensions if image is inside allowed limits
	  * 
	  * @param string $image_file path to image file
	  * @param int $max_width 
	  * @param nt $max_height 
	  * @access public
	  * @return array new image dimensions
	  */
	  function new_image_size($image_file,$max_width=null,$max_height=null)
	  {
		 /* set user size */
		 $img_size = GetImageSize($image_file);

		 if ($max_width && $img_size[0] > $max_width)
		 {
			$ret['width']=$max_width;
		 }
		 else
		 {
			$ret['width']=$img_size[0];
		 }

		 if ($max_height && $img_size[1] > $max_height)
		 {
			$ret['height']=$max_height;
		 }
		 else
		 {
			$ret['height']=$img_size[1];
		 }
		 return $ret;
	  }
   }

?>
