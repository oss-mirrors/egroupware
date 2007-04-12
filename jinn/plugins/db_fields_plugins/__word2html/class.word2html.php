<?php
   /**
   JiNN - A Database Application Development Toolkit
   Author:	Pim Snel for Lingewoud
   Copyright (C) 2007 Pim Snel <pim@lingewoud.nl>
   License http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   JiNN is part of eGroupWare - http://www.egroupware.org
   */

   /**
   * db_fields_plugin_word2html 
   * 
   * @package 
   * @version $Id$
   * @copyright Lingewoud B.V.
   * @author Pim Snel <pim-AT-lingewoud-DOT-nl> 
   * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   */
   class db_fields_plugin_word2html
   {
	  function formview_edit($field_name, $value, $config,$attr_arr)
	  {
		 $this->_initVars($config);
		 if($err=$this->_requirementsCheck($config))
		 {
			return $err;
		 }

		 if($value)
		 {
			$display_value=lang('Current text will be replaced.');
		 }
		 $input=lang('Select word document').'<br/><input type="hidden"  name="'.$field_name.'" value="1"/><input type="file" name="W2HTM'.substr(0,6,$field_name).'">';
		 return $input;
	  }


	  function listview_read($value, $config,$attr_arr)
	  {
		 return;
	  }

	  function on_save_filter($key, $HTTP_POST_VARS,$HTTP_POST_FILES,$config)
	  {
		 if(is_array($_FILES['W2HTM'.substr(0,6,$key)]))
		 {
			$this->_initVars($config);
			$newdir='WORD2HTML'.uniqid('');

			mkdir($this->upload_path.'/'.$newdir);
			$cmd=$this->wv." -b img -d '".$this->upload_path ."/$newdir' ".$_FILES['W2HTM'.substr(0,6,$key)]['tmp_name'];
			exec($cmd,&$f);
			foreach($f as $output) {
			   $ret .= "$output\n";
			}

			//make links exact
			$pattern = "/<img([^>]*) src=\"(?!http|ftp|https)([^\"]*)\"/";
			$replace = "<img\${1} src=\"" . $this->upload_url."/$newdir/" . "\${2}\"";
			$ret = preg_replace($pattern, $replace, $ret); 

			if($config['imgwidth'] || $config['imgheight'])
			{
			   $graphic=CreateObject('jinn.bogdlib');

			   //walk through directory and resize images
			   if ($handle = opendir($this->upload_path.'/'.$newdir)) 
			   {
				  while (false !== ($file = readdir($handle))) 
				  {
					 if ($file != "." && $file != "..") 
					 {
						$fpath=$this->upload_path.'/'.$newdir.'/'.$file;
						$filetype=$graphic->Get_Imagetype($fpath);	

						$newsize=$graphic->new_image_size($fpath,$config['imgwidth'],$config['imgheight']);

						$newtempfile=$graphic->Resize($newsize['width'],$newsize['height'],$fpath,$filetype);
						rename($newtempfile,$fpath);
					 }
				  }
				  closedir($handle);
			   }

			   //remove width and height attributes
			   $pattern = "/<img([^>]*) (width=\"[0-9]*\")/";
			   $replace = "<img\${1} ";
			   $ret = preg_replace($pattern, $replace, $ret); 

			   $pattern = "/<img([^>]*) (height=\"[0-9]*\")/";
			   $replace = "<img\${1} ";
			   $ret = preg_replace($pattern, $replace, $ret); 
			}

			return $ret;
		 }

		 return $output;
	  }

	  function _initVars($config)
	  {
		 $this->wv=($config['wvpath']?$config['wvpath']:'/usr/bin').'/wvWare';

		 if($config['subdir'])
		 {
			$extrasubdir=SEP.$config['subdir'];
		 }
		 $this->upload_url=$this->local_bo->cur_upload_url().$extrasubdir;
		 $this->upload_path=$this->local_bo->cur_upload_path().$extrasubdir;
	  }

	  function _requirementsCheck($config)
	  {
		 if(!is_executable($this->wv))
		 {
			return lang('Error: wvWare executable not found. check %1',$this->wv);
		 }

		 if(!is_writable($this->local_bo->cur_upload_path()))
		 {
			return lang('Error: upload path not writable. Check %1',$this->local_bo->cur_upload_path());
		 }

		 if(!is_dir($this->upload_path))
		 {
			mkdir($this->upload_path);
		 }
	  }

   }
?>
