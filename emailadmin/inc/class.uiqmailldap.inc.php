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
			'listServers'	=> True,
			'editServer'	=> True,
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

		function editServer()
		{
			global $phpgw, $phpgw_info, $serverid, $pagenumber;

			$menu = array
			(
				'0'	=> array
					   (
					   	'name'		=> lang('domain names'),
					   	'template'	=> 'domainnames.tpl'
					   ),
				'10'	=> array
					   (
					   	'name'		=> lang('virtual domains'),
					   	'template'	=> 'defaultpage.tpl'
					   ),
				'20'	=> array
					   (
					   	'name'		=> lang('LDAP settings'),
					   	'template'	=> 'defaultpage.tpl'
					   )
			);
			
			$this->display_app_header();
			
			$this->t->set_file(array("body" => $menu[$pagenumber]['template']));
			$this->t->set_block('body','main');
			$this->t->set_block('body','menu_row');
			$this->t->set_block('body','menu_row_bold');
			
			$this->translate();
			
			reset($menu);
			$i=0;
			while (list($key,$value) = each($menu))
			{
				$this->t->set_var('menu_description',$value['name']);
				$linkData = array
				(
					'menuaction'	=> 'qmailldap.uiqmailldap.editServer',
					'pagenumber'	=> $key,
					'serverid'	=> $serverid
				);
				$this->t->set_var('menu_link',$phpgw->link('/index.php',$linkData));
				$this->t->set_var('menu_row_color',$this->rowColor[$i%2]);
				if ($pagenumber == $key)
				{
					$this->t->parse('menu_rows','menu_row_bold',True);
				}
				else
				{
					$this->t->parse('menu_rows','menu_row',True);
				}
				$i++;
			}
			
			$this->t->set_var('done_row_color',$this->rowColor[($i)%2]);
			$linkData = array
			(
				'menuaction'	=> 'qmailldap.uiqmailldap.listServers',
			);
			$this->t->set_var('done_link',$phpgw->link('/index.php',$linkData));
			$this->t->set_var('th_bg',$phpgw_info["theme"]["th_bg"]);
			$this->t->set_var('bg_01',$phpgw_info["theme"]["bg01"]);
			$this->t->set_var('bg_02',$phpgw_info["theme"]["bg02"]);
			
			switch($serverid)
			{
				case "0":
					$this->t->set_var('rcpt_selectbox',
						"<b>".lang("We don't accept any email!")."</b>");
					$this->t->set_var('locals_selectbox',
						"<b>".lang("We don't deliver any email local!")."</b>");
					break;
			}
			
			$this->t->parse("out","main");
			print $this->t->get('out','main');
			
			$phpgw->common->phpgw_footer();
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
					'pagenumber'	=> '0',
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
			$this->t->set_var('lang_add',lang('add'));
			$this->t->set_var('lang_remove',lang('remove'));
			$this->t->set_var('lang_add_to_local',lang('add also to local domains '));
		}
	}
?>
