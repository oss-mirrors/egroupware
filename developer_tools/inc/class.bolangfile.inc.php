<?php
  /**************************************************************************\
  * phpGroupWare - Translation Editor                                        *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */


	class bolangfile
	{
		var $total;
		var $debug = False;
		var $public_functions = array(
			'index' => True
		);
		var $so;
		var $loaded_apps = array();
		var $source_langarray = '';
		var $target_langarray = '';

		function bolangfile()
		{
			$this->so = CreateObject('developer_tools.solangfile');
			settype($this->source_langarray,'string');
			settype($this->target_langarray,'string');
		}

		/* Sessions used to save state and not reread the langfile between adding/deleting phrases */
		function save_sessiondata($source,$target)
		{
			global $phpgw;
			if($this->debug) { echo '<br>Save:'; _debug_array($source); }
			$phpgw->session->appsession('session_data','developer_source_lang',$source);
			if($this->debug) { echo '<br>Save:'; _debug_array($target); }
			$phpgw->session->appsession('session_data','developer_target_lang',$target);
		}

		function read_sessiondata()
		{
			global $phpgw;

			$source = $phpgw->session->appsession('session_data','developer_source_lang');
			if($this->debug) { echo '<br>Read:'; _debug_array($source); }

			$target = $phpgw->session->appsession('session_data','developer_target_lang');
			if($this->debug) { echo '<br>Read:'; _debug_array($target); }

			$this->set_sessiondata($source,$target);
			return;
		}

		function set_sessiondata($source,$target)
		{
			$this->source_langarray = $source;
			$this->target_langarray = $target;
		}

		function list_apps()
		{
			$apps = $this->so->list_apps();
			$this->total = $this->so->total;
			return $apps;
		}

		function list_langs()
		{
			return $this->so->list_langs();
		}

		function addphrase($entry)
		{
			_debug_array($this->source_langarray);exit;
			$this->source_langarray[] = array(
				'message_id', array(
					'message_id' => $entry['message_id'],
					'content'    => $entry['content'],
					'app_name'   => $entry['app_name'],
					'lang'       => 'en'
				)
			);
			return;
		}

		function add_app($app,$userlang='en')
		{
			if(gettype($this->source_langarray) == 'array')
			{
				return $this->source_langarray;
			}
			$langarray = $this->so->add_app($app,$userlang);
			$this->loaded_apps = $this->so->loaded_apps;
			return $langarray;
		}

		function load_app($app,$userlang='en')
		{
			if(gettype($this->target_langarray) == 'array')
			{
				return $this->target_langarray;
			}
			$langarray = $this->so->add_app($app,$userlang);
			$this->loaded_apps = $this->so->loaded_apps;
			return $langarray;
		}
	}
?>
