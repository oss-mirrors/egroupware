<?php
	/**************************************************************************\
	* AngleMail	http://www.anglemail.org								*
	*/
	/**************************************************************************\
	* AngleMail - E-Mail Data Communications Class Core Functions			*
	* This file written by Angelo "Angles" Puglisi <angles@aminvestments.com>	*
	* Copyright (C) 2001-2002 Angelo "Angles" Puglisi						*
	* -------------------------------------------------------------------------						*
	* Most funtions have an authors line which attempts to identify and credit	*
	* any previous authors and maintainers.								*
	* AngleMail appreciates the hard work of previous authors and maintainers. 	*
	* -------------------------------------------------------------------------				*
	* This file designed to work as part of a drop in email module for 		*
	* phpGroupWare  http://www.phpgroupware.org					*
	* -------------------------------------------------------------------------				*
	* This library is free software; you can redistribute it and/or modify it		*
	* under the terms of the GNU Lesser General Public License as published by	*
	* the Free Software Foundation; either version 2.1 of the License,			*
	* or any later version.								*
	* This library is distributed in the hope that it will be useful, but			*
	* WITHOUT ANY WARRANTY; without even the implied warranty of		*
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	*
	* See the GNU Lesser General Public License for more details.			*
	* You should have received a copy of the GNU Lesser General Public License 	*
	* along with this library; if not, write to the Free Software Foundation,		*
	* Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA			*
	\**************************************************************************/

	/* $Id$ */

	/*!
	@class mail_dcom_base 
	@abstract E-Mail Data Communications API
	@discussion Part of a group of files which together comprise the "Data Communications" 
	API for AngleMail. Certain files are loaded if IMAP is compiled into php, while other 
	files are loaded if the sockets replacement functions are needed. 
	@author Each function has an authors line. 
	*/
	class mail_dcom_base
	{
		var $msg_struct;
		var $err = array("code","msg","desc");
		var $msg_info = Array(Array());
		
		var $debug_utf7=0;
		//var $debug_utf7=3;

		var $tempfile;
		var $folder_list_changed = False;
		var $enable_utf7 = False;
		var $imap_builtin = True;
		var $force_msg_uids = False;
		//var $att_files_dir;
		var $force_check;

		var $boundary,
		   $got_structure;

		/*!
		@function mail_dcom_base
		@abstract *constructor*
		@author Angles
		@access private
		*/
		function mail_dcom_base()
		{
			$this->err["code"] = " ";
			$this->err["msg"]  = " ";
			$this->err["desc"] = " ";
			$this->tempfile = $GLOBALS['phpgw_info']['server']['temp_dir'].SEP.$GLOBALS['phpgw_info']['user']['sessionid'].'.mhd';
			$this->force_check = false;
			$this->got_structure = false;
		}

		/*!
		@function utf7_encode
		@abstract ?
		@author Angles
		@access private
		*/
		function utf7_encode($data, $called_by='not_provided')
		{
			if ($this->debug_utf7 > 0) { echo 'mail_dcom_base: utf7_encode ('.__LINE__.'): ENTERING, $called_by ['.$called_by.']<br>'; } 
			if ($this->debug_utf7 > 1) { echo 'mail_dcom_base: utf7_encode ('.__LINE__.'): $data ['.serialize($data).']<br>'; } 
			// handle utf7 encoding of folder names, if necessary
			if (($this->enable_utf7 == False)
			|| (function_exists('imap_utf7_encode') == False)
			|| (!isset($data)))
			{
				if ($this->debug_utf7 > 0) { echo 'mail_dcom_base: utf7_encode ('.__LINE__.'): LEAVING on error, returning param unmodified. Check if .. then to see why we exited here, $called_by ['.$called_by.']<br>'; } 
				return $data;
			}

			// data to and from the server can be either array or string
			if (gettype($data) == 'array')
			{
				// array data
				$return_array = Array();
				for ($i=0; $i<count($data);$i++)
				{
					$return_array[$i] = $this->utf7_encode_string($data[$i]);
				}
				if ($this->debug_utf7 > 0) { echo 'mail_dcom_base: utf7_encode ('.__LINE__.'): LEAVING, returning $return_array ['.serialize($return_array).'], $called_by ['.$called_by.']<br>'; } 
				return $return_array;
			}
			elseif (gettype($data) == 'string')
			{
				// string data
				$return_string = $this->utf7_encode_string($data);
				if ($this->debug_utf7 > 0) { echo 'mail_dcom_base: utf7_encode ('.__LINE__.'): LEAVING, returning $return_string ['.serialize($return_string).'], $called_by ['.$called_by.']<br>'; } 
				return $return_string;
			}
			else
			{
				// ERROR
				if ($this->debug_utf7 > 0) { echo 'mail_dcom_base: utf7_encode ('.__LINE__.'): LEAVING with ERROR, returning param unmodified. Data was not string nor array, $called_by ['.$called_by.']<br>'; } 
				return $data;
			}
		}

		/*!
		@function utf7_encode_string
		@abstract ?
		@author Angles
		@access private
		*/
		function utf7_encode_string($data_str)
		{
			$name = Array();
			$name['folder_before'] = '';
			$name['folder_after'] = '';
			$name['translated'] = '';
			
			if (strstr($data_str,'}'))
			{
				// folder name at this stage is  {SERVER_NAME:PORT}FOLDERNAME
				// get everything to the right of the bracket "}", INCLUDES the bracket itself
				$name['folder_before'] = strstr($data_str,'}');
				// get rid of that 'needle' "}"
				$name['folder_before'] = substr($name['folder_before'], 1);
				// translate
				$name['folder_after'] = imap_utf7_encode($name['folder_before']);
				// replace old folder name with new folder name
				$name['translated'] = str_replace($name['folder_before'], $name['folder_after'], $data_str);
			}
			else
			{
				// folder name at this stage is  FOLDERNAME
				// there is NO {SERVER} part in this name, this is OK some commands do not require it (mail_move same acct)
				$name['folder_before'] = $data_str;
				// translate
				$name['folder_after'] = imap_utf7_encode($name['folder_before']);
				$name['translated'] = $name['folder_after'];
			}
			if ($this->debug_utf7 > 1) { echo ' _ mail_dcom_base: utf7_encode_string ('.__LINE__.'): $name DUMP: ['.htmlspecialchars(serialize($name)).']<br>'; } 
			return $name['translated'];
		}

		/*!
		@function utf7_decode
		@abstract ?
		@author Angles
		@access private
		*/
		function utf7_decode($data)
		{
			if ($this->debug_utf7 > 0) { echo 'mail_dcom_base: utf7_decode ('.__LINE__.'): ENTERING<br>'; } 
			if ($this->debug_utf7 > 1) { echo 'mail_dcom_base: utf7_decode ('.__LINE__.'): $data ['.serialize($data).']<br>'; } 
			// handle utf7 decoding of folder names, if necessary
			if (($this->enable_utf7 == False)
			|| (function_exists('imap_utf7_decode') == False)
			|| (!isset($data)))
			{
				if ($this->debug_utf7 > 0) { echo 'mail_dcom_base: utf7_decode ('.__LINE__.'): LEAVING on error, returning param unmodified. Check if .. then to see why we exited here<br>'; } 
				return $data;
			}

			// data to and from the server can be either array or string
			if (gettype($data) == 'array')
			{
				// array data
				$return_array = Array();
				for ($i=0; $i<count($data);$i++)
				{
					$return_array[$i] = $this->utf7_decode_string($data[$i]);
				}
				if ($this->debug_utf7 > 0) { echo 'mail_dcom_base: utf7_decode ('.__LINE__.'): LEAVING, returning $return_array ['.serialize($return_array).']<br>'; } 
				return $return_array;
			}
			elseif (gettype($data) == 'string')
			{
				// string data
				$return_string = $this->utf7_decode_string($data);
				if ($this->debug_utf7 > 0) { echo 'mail_dcom_base: utf7_decode ('.__LINE__.'): LEAVING, returning $return_string ['.serialize($return_string).']<br>'; } 
				return $return_string;
			}
			else
			{
				// ERROR
				if ($this->debug_utf7 > 0) { echo 'mail_dcom_base: utf7_decode ('.__LINE__.'): LEAVING with ERROR, returning param unmodified. Data was not string nor array.<br>'; } 
				return $data;
			}
		}

		/*!
		@function utf7_decode_string
		@abstract ?
		@author Angles
		@access private
		*/
		/*
		function utf7_decode_string($data_str)
		{
			$name = Array();
			$name['folder_before'] = '';
			$name['folder_after'] = '';
			$name['translated'] = '';
			
			// folder name at this stage is  {SERVER_NAME:PORT}FOLDERNAME
			// get everything to the right of the bracket "}", INCLUDES the bracket itself
			$name['folder_before'] = strstr($data_str,'}');
			// get rid of that 'needle' "}"
			$name['folder_before'] = substr($name['folder_before'], 1);
			// translate
			$name['folder_after'] = imap_utf7_decode($name['folder_before']);
			// "imap_utf7_decode" returns False if no translation occured
			if ($name['folder_after'] == False)
			{
				// no translation occured
				return $data_str;
			}
			else
			{
				// replace old folder name with new folder name
				$name['translated'] = str_replace($name['folder_before'], $name['folder_after'], $data_str);
				return $name['translated'];
			}
		}
		*/
		function utf7_decode_string($data_str)
		{
			$name = Array();
			$name['folder_before'] = '';
			$name['folder_after'] = '';
			$name['translated'] = '';
			
			if (strstr($data_str,'}'))
			{
				// folder name at this stage is  {SERVER_NAME:PORT}FOLDERNAME
				// get everything to the right of the bracket "}", INCLUDES the bracket itself
				$name['folder_before'] = strstr($data_str,'}');
				// get rid of that 'needle' "}"
				$name['folder_before'] = substr($name['folder_before'], 1);
				// translate
				$name['folder_after'] = imap_utf7_decode($name['folder_before']);
				// "imap_utf7_decode" returns False if no translation occured (supposed to, can return identical string too)
				if ( ($name['folder_after'] == False)
				|| ($name['folder_before'] == $name['folder_after']) )
				{
					// no translation occured
					if ($this->debug_utf7 > 0) { echo ' _ mail_dcom_base: utf7_decode_string ('.__LINE__.'): returning unmodified name, NO decoding needed, returning feed $data_str: ['.htmlspecialchars(serialize($data_str)).']<br>'; } 
					return $data_str;
				}
				else
				{
					// replace old folder name with new folder name
					$name['translated'] = str_replace($name['folder_before'], $name['folder_after'], $data_str);
					if ($this->debug_utf7 > 0) { echo ' _ mail_dcom_base: utf7_decode_string ('.__LINE__.'): returning decoded name, $name[] DUMP: ['.htmlspecialchars(serialize($name)).']<br>'; } 
					return $name['translated'];
				}
			}
			else
			{
				// folder name at this stage is  FOLDERNAME
				// there is NO {SERVER} part in this name, 
				// DOES THIS EVER HAPPEN comming *from* the server? I DO NOT THINK SO, but just in case
				// translate
				$name['translated'] = imap_utf7_decode($data_str);
				// "imap_utf7_decode" returns False if no translation occured
				if (($name['translated'] == False)
				|| ($name['folder_before'] == $data_str) )
				{
					// no translation occured
					if ($this->debug_utf7 > 0) { echo ' _ mail_dcom_base: utf7_decode_string ('.__LINE__.'): returning unmodified name, NO decoding needed, returning feed $data_str: ['.htmlspecialchars(serialize($data_str)).']<br>'; } 
					return $data_str;
				}
				else
				{
					if ($this->debug_utf7 > 0) { echo ' _ mail_dcom_base: utf7_decode_string ('.__LINE__.'): returning decoded name, $name[] DUMP: ['.htmlspecialchars(serialize($name)).']<br>'; } 
					return $name['translated'];
				}
			}
		}

		/*!
		@function get_flag
		@abstract ?
		*/
		function get_flag($stream,$msg_num,$flag)
		{
			// ralfbecker patch dated 021124
			$header = explode("\n",$this->fetchheader($stream,$msg_num));
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
