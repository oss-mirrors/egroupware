<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail                                                    *
  * http://www.phpgroupware.org                                              *
  * Based on Aeromail by Mark Cushman <mark@cushman.net>                     *
  *          http://the.cushman.net/                                         *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  class mail_dcom_base
  { 
	var $msg_struct;
	var $err = array("code","msg","desc");
	var $msg_info = Array(Array());

	var $tempfile;
	//var $att_files_dir;
	var $force_check;

	var $boundary,
	   $got_structure;

	function mail_dcom_base()
	{
		global $phpgw_info;

		$this->err["code"] = " ";
		$this->err["msg"]  = " ";
		$this->err["desc"] = " ";
		$this->tempfile = $phpgw_info['server']['temp_dir'].SEP.$phpgw_info['user']['sessionid'].'.mhd';
		$this->force_check = false;
		$this->got_structure = false;
	}

	function get_flag($stream,$msg_num,$flag)
	{
		$header = $this->fetchheader($stream,$msg_num);
		$flag = strtolower($flag);
		for ($i=0;$i<count($header);$i++)
		{
			$pos = strpos($header[$i],":");
			if (is_int($pos) && $pos)
			{
				$keyword = trim(substr($header[$i],0,$pos));
				$content = trim(substr($header[$i],$pos+1));
				if (strtolower($keyword) == $flag)
				{
					return $content;
				}
			}
		}
		return false;
	}

  } // end of class mail_dcom
?>
