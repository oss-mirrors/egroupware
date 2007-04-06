<?php
   /**
   JiNN - A Database Application Development Toolkit
   Author:	Pim Snel for Lingewoud
   Copyright (C) 2007 Pim Snel <pim@lingewoud.nl>
   License http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   JiNN is part of eGroupWare - http://www.egroupware.org

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
			//_debug_array($config);
			$this->_initVars($config);
			$_file1 = tempnam('/tmp','FOO');
			$cmd=$this->wv." -b img -d '".$this->upload_path ."' ".$_FILES['W2HTM'.substr(0,6,$key)]['tmp_name'];
			exec($cmd,&$f);
			foreach($f as $output) {
			   $ret .= "$output\n";
			}

			$pattern = "/<img([^>]*) src=\"(?!http|ftp|https)([^\"]*)\"/";
			$replace = "<img\${1} src=\"" . $this->upload_url.'/' . "\${2}\"";
			$ret = preg_replace($pattern, $replace, $ret); 

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
