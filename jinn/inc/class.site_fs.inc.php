<?php
   class site_fs
   {
	  /**
	  * constructor
	  */
	  function site_fs()
	  {}

	  function create_archive($site_id)
	  {
		 $tmpname = tempnam("","");
		 #$tmpname='/tmp/tmp.zip';
		 $archive = CreateObject('phpgwapi.PclZip',$tmpname);
		 $v_list = $archive->create($this->get_jinn_sitefile_path($site_id), PCLZIP_OPT_REMOVE_PATH, $this->get_jinn_sitefile_path($site_id));
		 if ($v_list == 0) {
			//die("Error : ".$archive->errorInfo(true));
			return false;
		 }
		 else
		 {
			return $tmpname;
		 }
	  }
	  
	  function extract_archive($tmpfile,$site_id)
	  {
		 $archive = CreateObject('phpgwapi.PclZip',$tmpfile);
		 if($archive->extract(PCLZIP_OPT_PATH, $this->get_jinn_sitefile_path($site_id)) == 0) 
		 {
			die("Error : ".$archive->errorInfo(true));
		 }
		 
		 /*		 $v_list = $archive->create($this->get_jinn_sitefile_path($site_id), PCLZIP_OPT_REMOVE_PATH, $this->get_jinn_sitefile_path($site_id));
		 if ($v_list == 0) {
			die("Error : ".$archive->errorInfo(true));
		 }*/
		 else
		 {
			return true;
		 }
	  }

	  function remove_site_files($dir,$headdir=false) 
	  {
		 if(!$dh = @opendir($dir)) return;

		 while (($obj = readdir($dh))) 
		 {
			if($obj=='.' || $obj=='..') continue;

			if (!@unlink($dir.'/'.$obj)) 
			{
			   $this->remove_site_files($dir.'/'.$obj);
			} 
			else 
			{
			   $file_deleted++;
			}
		 }
		 if (@rmdir($dir)) $dir_deleted++;

		 if($headdir)
		 {
			return true;
		 }
	  }

	  /**
	  * gets the path to the site sys dir by site id
	  *
	  * @param mixed site_id
	  * $return string complete path to the site sys dir
	  */
	  function get_jinn_sitefile_path($site_id)
	  {
		 return $current_site_dir = EGW_SERVER_ROOT . SEP . 'jinn' . SEP . 'files_sites'. SEP . $site_id;
	  }
	  
	  function get_jinn_sitefile_url($site_id)
	  {
		 return $current_site_url = $GLOBALS[egw_info][server][webserver_url] . '/jinn/files_sites/' . $site_id;
	  }
	  /**
	  * save file to directory dedicated for a particular site
	  *
	  * @param $site_id the dir is named to this id
	  * @param files_key the key which refers to the file in the super array $_FILES
	  * @param $subdir optional subdir  to store files in. If it does not exist it will be created
	  * @return array contains error code and message
	  */
	  function save_site_file_from_post($site_id,$files_key,$subdir='',$multiName='',$name='')
	  {
		 $jinn_sites_dir = EGW_SERVER_ROOT . SEP . 'jinn' . SEP . 'files_sites';
		 $current_site_dir = $this->get_jinn_sitefile_path($site_id);

		 if($subdir)
		 {
			$current_store_dir = $current_site_dir.SEP.$subdir;
		 }
		 else
		 {
			$current_store_dir = $current_site_dir;
		 }

		 if(!is_writable($jinn_sites_dir))
		 {
			$ret_arr[err_code]=1; //fix me give normal error code
			$ret_arr[error_msg]=lang('Can\'t write in sites directory');
		 }

		 if(!is_dir($current_site_dir))
		 {
			mkdir($current_site_dir);
		 }

		 if(!is_dir($current_store_dir))
		 {
			mkdir($current_store_dir);
		 }
		 $ftmp = $_FILES[$files_key]['tmp_name'];
		 if(!$ftmp)
		 {
			$ftmp=$_FILES['PLGXXX'.$multiName][tmp_name][1][$name][value];
		 }
		 if($_FILES[$files_key]['name'])
		 {
			$fname = $current_store_dir.SEP.$_FILES[$files_key]['name'];
		 }
		 else
		 {
			$fname=$current_store_dir.SEP.$_FILES['PLGXXX'.$multiName][name][1][$name][value];
		 }
		 //echo $files_key;
		 //_debug_array($_FILES);
		 //_debug_array($fname);
		 move_uploaded_file($ftmp, $fname);
	  }

	  /**
	  * remove file from site directory
	  * @param int site_id refers to sites own subdir
	  * @param string filename to delete
	  * @param string subdir in which the file resides
	  * @todo create essential error return codes
	  */
	  function remove_file($site_id,$filename,$subdir='')
	  {
		 $current_site_dir = $this->get_jinn_sitefile_path($site_id);

		 if($subdir)
		 {
			$current_store_dir = $current_site_dir.SEP.$subdir;
		 }
		 else
		 {
			$current_store_dir = $current_site_dir;
		 }
		 //		 die($current_store_dir.SEP.$filename);
		 _debug_array($filename);

		 @unlink($current_store_dir.SEP.$filename);
	  }
   }
