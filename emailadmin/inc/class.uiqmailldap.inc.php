<?php
	/***************************************************************************\
	* phpGroupWare - Notes                                                      *
	* http://www.phpgroupware.org                                               *
	* Written by : Bettina Gille [ceb@phpgroupware.org]                         *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; either version 2 of the License, or (at your    *
	* option) any later version.                                                *
	\***************************************************************************/
	/* $Id$ */

	class uiqmailldap
	{
		#var $grants;
		#var $cat_id;
		#var $start;
		#var $search;
		#var $filter;

		var $public_functions = array
		(
			'list_servers'	=> True,
			'view'		=> True,
			'add'		=> True,
			'edit'		=> True,
			'delete'	=> True,
			'preferences'	=> True
		);

		function uiqmailldap()
		{
			global $phpgw, $phpgw_info;

			$this->cats			= CreateObject('phpgwapi.categories');
			#$this->nextmatchs		= CreateObject('phpgwapi.nextmatchs');
			#$this->account			= $phpgw_info['user']['account_id'];
			$this->t			= CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			#$this->grants			= $phpgw->acl->get_grants('notes');
			#$this->grants[$this->account]	= PHPGW_ACL_READ + PHPGW_ACL_ADD + PHPGW_ACL_EDIT + PHPGW_ACL_DELETE;
			$this->boqmailldap		= CreateObject('qmailldap.boqmailldap');
			
			$this->rowColor[0] = $phpgw_info["theme"]["bg01"];
			$this->rowColor[1] = $phpgw_info["theme"]["bg02"];
			                 
		}
	
		function display_app_header()
		{
			global $phpgw, $phpgw_info;
			
			$phpgw->common->phpgw_header();
			echo parse_navbar();
			
		}
		
		function listServers()
		{
			global $phpgw, $phpgw_info;
			
			$this->display_app_header();
			
			$this->t->set_file(array("body" => "listservers.tpl"));
			$this->t->set_block('body','main','main');
			$this->t->set_block('body','row','row');
			
			$this->translate();

			$serverList = $this->boqmailldap->getServerList();
			
			for ($i=0; $i < count($serverList); $i++)
			{
				$this->t->set_var('server_name',$serverList[$i]['servername']);
				$this->t->set_var('server_description',$serverList[$i]['description']);
				$linkData = array
				(
					'menuaction'	=> 'qmailldap.uiqmailldap.editServer',
					'serverid'	=> $serverList[$i]['id']
				);
				$this->t->set_var('edit_link',$phpgw->link('/index.php',$linkData));
				$linkData = array
				(
					'menuaction'	=> 'qmailldap.uiqmailldap.deleteServer',
					'serverid'	=> $serverList[$i]['id']
				);
				$this->t->set_var('delete_link',$phpgw->link('/index.php',$linkData));
				$this->t->set_var('row_color',$this->rowColor[$i%2]);
				$this->t->parse('rows','row',True);
			}

			$this->t->parse("out","main");
			
			print $this->t->get('out','main');
			
			$phpgw->common->phpgw_footer();
		}

		function translate()
		{
			global $phpgw_info;			

			$this->t->set_var('th_bg',$phpgw_info['theme']['th_bg']);

			$this->t->set_var('lang_server_list',lang('server list'));
			$this->t->set_var('lang_server_name',lang('server name'));
			$this->t->set_var('lang_server_description',lang('description'));
			$this->t->set_var('lang_edit',lang('edit'));
			$this->t->set_var('lang_delete',lang('delete'));
		}
	}
?>
