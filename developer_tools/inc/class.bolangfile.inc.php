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
		var $src_file;
		var $tgt_file;

		function bolangfile()
		{
			$this->so = CreateObject('developer_tools.solangfile');
			settype($this->source_langarray,'string');
			settype($this->target_langarray,'string');
		}

		/* Sessions used to save state and not reread the langfile between adding/deleting phrases */
		function save_sessiondata($source,$target)
		{
			if($this->debug) { echo '<br>Save:'; _debug_array($source); }
			$GLOBALS['phpgw']->session->appsession('developer_source_lang','developer_tools',$source);
			if($this->debug) { echo '<br>Save:'; _debug_array($target); }
			$GLOBALS['phpgw']->session->appsession('developer_target_lang','developer_tools',$target);
			$GLOBALS['phpgw']->session->appsession('developer_source_file','developer_tools',$this->src_file);
			$GLOBALS['phpgw']->session->appsession('developer_target_file','developer_tools',$this->tgt_file);
		}

		function read_sessiondata()
		{
			$source = $GLOBALS['phpgw']->session->appsession('developer_source_lang','developer_tools');
			if($this->debug) { echo '<br>Read:'; _debug_array($source); }

			$target = $GLOBALS['phpgw']->session->appsession('developer_target_lang','developer_tools');
			if($this->debug) { echo '<br>Read:'; _debug_array($target); }

			$src_file = $GLOBALS['phpgw']->session->appsession('developer_source_file','developer_tools');
			$tgt_file = $GLOBALS['phpgw']->session->appsession('developer_target_file','developer_tools');

			$this->set_sessiondata($source,$target,$src_file,$tgt_file);
			return;
		}

		function set_sessiondata($source,$target,$src_file,$tgt_file)
		{
			$this->source_langarray = $source;
			$this->target_langarray = $target;
			$this->src_file = $src_file;
			$this->tgt_file = $tgt_file;
		}

		function clear_sessiondata()
		{
			$GLOBALS['phpgw']->session->appsession('developer_source_lang','developer_tools','');
			$GLOBALS['phpgw']->session->appsession('developer_target_lang','developer_tools','');
			$GLOBALS['phpgw']->session->appsession('developer_source_file','developer_tools','');
			$GLOBALS['phpgw']->session->appsession('developer_target_file','developer_tools','');
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
			/* _debug_array($this->source_langarray);exit; */
			$mess_id = $entry['message_id'];
			$this->source_langarray[$mess_id] = array(
				'message_id' => $entry['message_id'],
				'content'    => $entry['content'],
				'app_name'   => $entry['app_name'],
				'lang'       => 'en'
			);
			@ksort($this->source_langarray);
			return;
		}

		function add_app($app,$userlang='en')
		{
			if(gettype($this->source_langarray) == 'array')
			{
				return $this->source_langarray;
			}
			$this->source_langarray = $this->so->add_app($app,$userlang);
			$this->src_file = $this->so->src_file;
			$this->loaded_apps = $this->so->loaded_apps;
			return $this->source_langarray;
		}

		function load_app($app,$userlang='en')
		{
			if(gettype($this->target_langarray) == 'array')
			{
				/* return $this->target_langarray; */
			}
			$this->target_langarray = $this->so->load_app($app,$userlang);
			$this->tgt_file = $this->so->tgt_file;
			$this->loaded_apps = $this->so->loaded_apps;
			return $this->target_langarray;
		}

		function write_file($which,$app_name,$userlang)
		{
			switch ($which)
			{
				case 'source':
					$langarray = $this->source_langarray;
					break;
				case 'target':
					$langarray = $this->target_langarray;
					break;
				default:
					break;
			}
			$this->so->write_file($app_name,$langarray,$userlang);
			return;
		}

		function loaddb($app_name,$userlang)
		{
			return $this->so->loaddb($app_name,$userlang);
		}
	}
?>
