<?php
	/*******************************************************************\
	* phpGroupWare - backup                                             *
	* http://www.phpgroupware.org                                       *
	*                                                                   *
	* Administration Tool for data backup                               *
	* Written by Bettina Gille [ceb@phpgroupware.org]                   *
	* -----------------------------------------------                   *
	* Copyright (C) 2001 Bettina Gille                                  *
	*                                                                   *
	* This program is free software; you can redistribute it and/or     *
	* modify it under the terms of the GNU General Public License as    *
	* published by the Free Software Foundation; either version 2 of    *
	* the License, or (at your option) any later version.               *
	*                                                                   *
	* This program is distributed in the hope that it will be useful,   *
	* but WITHOUT ANY WARRANTY; without even the implied warranty of    *
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU  *
	* General Public License for more details.                          *
	*                                                                   *
	* You should have received a copy of the GNU General Public License *
	* along with this program; if not, write to the Free Software       *
	* Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.         *
	\*******************************************************************/
	/* $Id$ */

	class uibackup
	{
		var $public_functions = array
		(
			'backup_admin'	=> True,
			'web_backup'	=> True
		);

		function uibackup()
		{
			$this->t		= $GLOBALS['phpgw']->template;
			$this->bobackup	= CreateObject('backup.bobackup');
		}

		function set_app_langs()
		{
			$this->t->set_var('bg_color',$GLOBALS['phpgw_info']['theme']['th_bg']);
			$this->t->set_var('row_on',$GLOBALS['phpgw_info']['theme']['row_on']);
			$this->t->set_var('row_off',$GLOBALS['phpgw_info']['theme']['row_off']);
			$this->t->set_var('lang_b_create',lang('Create backups of your data ?'));
			$this->t->set_var('lang_b_intval',lang('Interval'));
			$this->t->set_var('lang_select_b_intval',lang('Select interval'));
			$this->t->set_var('lang_b_data',lang('Data'));
			$this->t->set_var('lang_b_sql',lang('SQL'));
			$this->t->set_var('lang_b_ldap',lang('LDAP'));
			$this->t->set_var('lang_b_email',lang('E-MAIL'));
			$this->t->set_var('lang_r_host',lang('Operating system'));
			$this->t->set_var('lang_r_config',lang('Configuration remote host'));
			$this->t->set_var('lang_r_save',lang('Save backup to a remote host ?'));
			$this->t->set_var('lang_config_path',lang('Absolute path of the directory to store the backup script'));
			$this->t->set_var('lang_path',lang('Absolute path of the backup directory'));
			$this->t->set_var('lang_r_ip',lang('IP or hostname'));
			$this->t->set_var('lang_user',lang('User'));
			$this->t->set_var('lang_pwd',lang('Password'));
			$this->t->set_var('lang_l_config',lang('Configuration localhost'));
			$this->t->set_var('lang_l_save',lang('Save backup locally ?'));
			$this->t->set_var('lang_l_websave',lang('Show backup archives in phpGroupWare ?'));
			$this->t->set_var('lang_b_config',lang('Configuration backup'));
			$this->t->set_var('lang_b_type',lang('Archive type'));
			$this->t->set_var('lang_select_b_type',lang('Select archive type'));
			$this->t->set_var('lang_app',lang('Transport application'));
			$this->t->set_var('lang_select_app',lang('Select transport application'));
			$this->t->set_var('lang_save',lang('Save'));
			$this->t->set_var('lang_versions',lang('Number of stored backup versions'));
		}

		function backup_admin()
		{
			global $values, $submit;

			$link_data = array
			(
				'menuaction' 	=> 'backup.uibackup.backup_admin'
			);

			if ($submit)
			{
				$error = $this->bobackup->check_values($values);
				if (is_array($error))
				{
					$this->t->set_var('message',$GLOBALS['phpgw']->common->error_list($error));
				}
				else
				{
					$this->bobackup->save_items($values);
					Header('Location: ' . $GLOBALS['phpgw']->link('/admin/index.php'));
				}
			}

			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->set_app_langs();

			$this->t->set_file(array('admin_form' => 'admin_form.tpl'));

			$this->t->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('lang_action',lang('Backup administration'));

			$values = $this->bobackup->get_config();

			$this->t->set_var('b_create','<input type="checkbox" name="values[b_create]" value="True"' . ($values['b_create'] == 'yes'?' checked':'') . '>');

			switch($values['b_intval'])
			{
				case 'daily': $b_intval_sel[0]=' selected';break;
				case 'weekly': $b_intval_sel[1]=' selected';break;
				case 'monthly': $b_intval_sel[2]=' selected';break;
			}

			$intval_list = '<option value="daily"' . $b_intval_sel[0] . '>' . lang('daily') . '</option>' . "\n"
						. '<option value="weekly"' . $b_intval_sel[1] . '>' . lang('weekly') . '</option>' . "\n"
						. '<option value="monthly"' . $b_intval_sel[2] . '>' . lang('monthly') . '</option>' . "\n";

			$this->t->set_var('intval_list',$intval_list);

			switch($values['b_type'])
			{
				case 'tgz':		$b_type_sel[0]=' selected';break;
				case 'tar.bz2':	$b_type_sel[1]=' selected';break;
				case 'zip':		$b_type_sel[2]=' selected';break;
			}

			$type_list = '<option value="tgz"' . $b_type_sel[0] . '>' . lang('tar.gz') . '</option>' . "\n"
						. '<option value="tar.bz2"' . $b_type_sel[1] . '>' . lang('tar.bz2') . '</option>' . "\n"
						. '<option value="zip"' . $b_type_sel[2] . '>' . lang('zip') . '</option>' . "\n";

			$this->t->set_var('type_list',$type_list);

			switch($values['r_app'])
			{
				case 'ftp':			$r_type_sel[0]=' selected';break;
				case 'nfs':			$r_type_sel[1]=' selected';break;
				case 'smbmount':	$r_type_sel[2]=' selected';break;
			}

			$r_app_list = '<option value="ftp"' . $r_type_sel[0] . '>' . lang('ftp') . '</option>' . "\n"
						. '<option value="nfs"' . $r_type_sel[1] . '>' . lang('nfs') . '</option>' . "\n"
						. '<option value="smbmount"' . $r_type_sel[2] . '>' . lang('smbmount') . '</option>' . "\n";

			$this->t->set_var('r_app_list',$r_app_list);

			if ($values['b_sql'] == 'mysql' || $values['b_sql'] == 'pgsql')
			{
				$values['b_sql'] = 'yes';
			}

			$this->t->set_var('b_sql','<input type="checkbox" name="values[b_sql]" value="True"' . ($values['b_sql'] == 'yes'?' checked':'') . '>');
			$this->t->set_var('b_ldap','<input type="checkbox" name="values[b_ldap]" value="True"' . ($values['b_ldap'] == 'yes'?' checked':'') . '>');
			$this->t->set_var('b_email','<input type="checkbox" name="values[b_email]" value="True"' . ($values['b_email'] == 'yes'?' checked':'') . '>');

			$this->t->set_var('l_save','<input type="checkbox" name="values[l_save]" value="True"' . ($values['l_save'] == 'yes'?' checked':'') . '>');
			$this->t->set_var('l_websave','<input type="checkbox" name="values[l_websave]" value="True"' . ($values['l_websave'] == 'yes'?' checked':'') . '>');
			$this->t->set_var('r_save','<input type="checkbox" name="values[r_save]" value="True"' . ($values['r_save'] == 'yes'?' checked':'') . '>');

			$r_host = '<input type="radio" name="values[r_host]" value="unix"' . ($values['r_host'] == 'unix'?' checked':'') . '>UNIX' . "\n";
			$r_host .= '<input type="radio" name="values[r_host]" value="win"' . ($values['r_host'] == 'win'?' checked':'') . '>WIN';

			$this->t->set_var('r_host',$r_host);
			$this->t->set_var('r_path',$values['r_path']);
			$this->t->set_var('r_ip',$values['r_ip']);
			$this->t->set_var('r_user',$values['r_user']);
			$this->t->set_var('r_pwd',$values['r_pwd']);

			$this->t->set_var('script_path',$values['script_path']);
			$this->t->set_var('l_path',$values['l_path']);
			$this->t->set_var('versions',$values['versions']);

			$this->t->pfp('out','admin_form');

			$GLOBALS['phpgw']->common->phpgw_footer();
		}

		function web_backup()
		{
			global $delete, $archive;

			if ($delete && $archive)
			{
				$this->bobackup->drop_archive($archive);
			}

			$link_data = array
			(
				'menuaction' 	=> 'backup.uibackup.web_backup',
				'delete'		=> $delete,
				'archive'		=> $archive
			);

			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->set_app_langs();

			$this->t->set_file(array('archive_list_t' => 'web_form.tpl'));
			$this->t->set_block('archive_list_t','archive_list','list');

			$this->t->set_var('lang_action',lang('Backup'));

			$config = $this->bobackup->get_config();

			if ($config['l_websave'] == 'yes')
			{
				$this->nextmatchs = CreateObject('phpgwapi.nextmatchs');

				$archives = $this->bobackup->get_archives();

				if ($archives)
				{
					for ($i=0;$i<count($archives);$i++)
					{
						$this->nextmatchs->template_alternate_row_color(&$this->t);

						$this->t->set_var(array
						(
							'archive'	=> 'archives/' . $archives[$i],
							'aname'		=> $archives[$i]
						));

						$this->t->set_var('delete',$GLOBALS['phpgw']->link('/index.php','menuaction=backup.uibackup.web_backup&delete=True&archive='
											. $archives[$i]));
						$this->t->set_var('lang_delete',lang('Delete'));

						$this->t->fp('list','archive_list',True);
					}
				}
				else
				{
					$this->t->set_var('noweb',lang('No backup archives available !'));
				}
			}
			else
			{
				$this->t->set_var('noweb',lang('The backup application is not configured for showing the archives in phpGroupWare yet !'));
			}

			$this->t->pfp('out','archive_list_t',True);
			$GLOBALS['phpgw']->common->phpgw_footer();
		}
	}
?>
