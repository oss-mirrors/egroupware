<?php
	/*******************************************************************\
	* phpGroupWare - Backup                                             *
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

	class bobackup
	{
		var $public_functions = array
		(
			'check_values'		=> True,
			'save_items'		=> True,
			'get_config'		=> True,
			'create_config'		=> True,
			'save_config'		=> True,
			'phpftp_connect'	=> True
		);

		function bobackup()
		{
			$this->config	= CreateObject('phpgwapi.config','backup');
			$this->config->read_repository();
		}

		function get_config()
		{
			if ($this->config->config_data)
			{
				$items = $this->config->config_data;
			}
			return $items;
		}

		function phpftp_connect($host,$user,$pass)
		{
			// echo "connecting to $host with $user and $pass\n";
			$ftp = ftp_connect($host);
			if ($ftp)
			{
				if (ftp_login($ftp,$user,$pass))
				{
					return $ftp;
				}
			}
		}

		function check_values($values)
		{
			if ($values['l_save'])
			{
				if (! $values['l_path'] && ! $values['l_websave'])
				{
					$error[] = lang('Plase enter the path of the backup dir and/or enable showing archives in phpGroupWare !');					
				}
			}

			if ($values['r_save'])
			{
				if (! $values['r_app'])
				{
					$error[] = lang('Plase select an application for transport to the remote host !');					
				}
				elseif (! $values['r_user'] || ! $values['r_pwd'])
				{
					$error[] = lang('Plase enter username and password for remote connection !');					
				}
				elseif ($values['r_app'] == 'ftp')
				{
					$ftp = $this->phpftp_connect($values['r_ip'],$values['r_user'],$values['r_pwd']);
					if (! $ftp)
					{
						$error[] = lang('The ftp connection failed ! Please check your configuration !');
					}
				}
			}

			if (is_array($error))
			{
				return $error;
			}
		}

		function save_items($values)
		{
			if ($values['b_create'])
			{
				$values['b_create'] = 'yes';
			}
			else
			{
				$values['b_create'] = 'no';
			}

			if ($values['b_sql'])
			{
				$values['b_sql'] = 'yes';
			}
			else
			{
				$values['b_sql'] = 'no';
			}

			if ($values['b_ldap'])
			{
				$values['b_ldap'] = 'yes';
			}
			else
			{
				$values['b_ldap'] = 'no';
			}

			if ($values['b_email'])
			{
				$values['b_email'] = 'yes';
			}
			else
			{
				$values['b_email'] = 'no';
			}

			if ($values['r_save'])
			{
				$values['r_save'] = 'yes';
			}
			else
			{
				$values['r_save'] = 'no';
			}

			if ($values['l_save'])
			{
				$values['l_save'] = 'yes';
			}
			else
			{
				$values['l_save'] = 'no';
			}

			if ($values['l_websave'])
			{
				$values['l_websave'] = 'yes';
			}
			else
			{
				$values['l_websave'] = 'no';
			}


			while (list($key,$config) = each($values))
			{
				if ($config)
				{
					$this->config->config_data[$key] = $config;
				}
				else
				{
					unset($config->config_data[$key]);
				}
			}
			$this->config->save_repository(True);
			$this->create_config();
		}


		function save_config($conf_file, $config)
		{
			$file = fopen($conf_file,'w+');
 //			ftruncate($file,0);
			fwrite($file,$config);
			fclose($file);
		}

		function create_config()
		{
			$co = $this->get_config();

			$co['db_type'] = $GLOBALS['phpgw_info']['server']['db_type'];
			$co['db_name'] = $GLOBALS['phpgw_info']['server']['db_name'];
			$co['server_root'] = PHPGW_SERVER_ROOT;


			$check_exists = $co['server_root'] . '/backup/phpgw_check_for_backup';
			if (file_exists($check_exists) == False)
			{
				$check = $GLOBALS['phpgw']->template->set_file(array('check' => 'check_form.tpl'));
				$check .= $GLOBALS['phpgw']->template->set_var('server_root',$co['server_root']);
				$check .= $GLOBALS['phpgw']->template->fp('out','check',True);
				$conf_file = $co['server_root'] . '/backup/phpgw_check_for_backup';
				$this->save_config($conf_file,$check);
			}

			$config = $GLOBALS['phpgw']->template->set_file(array('config' => 'backup_form.tpl'));

			if ($co['db_type'] == 'mysql')
			{
				$bsqlin = 'cd /var/lib/mysql' . "\n";
			}					

			$config .= $GLOBALS['phpgw']->template->set_var('bsqlin',$bsqlin);

			$bdate = time();
			$bdate = $bdate + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
			$month  = $GLOBALS['phpgw']->common->show_date(time(),'n');
			$day    = $GLOBALS['phpgw']->common->show_date(time(),'d');
			$year   = $GLOBALS['phpgw']->common->show_date(time(),'Y');
			$bdateout = $day . '_' . $month . '_' . $year;

			if ($co['b_type'] == 'tgz')
			{
				$sql_comp = 'tar -czf ' . $co['server_root'] . '/backup/' . $bdateout . '_backup_' . $co['db_type'] . '.tar.gz ' . $co['db_name'];
				$config .= $GLOBALS['phpgw']->template->set_var('sql_comp',$sql_comp);
			}

			$config .= $GLOBALS['phpgw']->template->fp('out','config',True);

			$conf_file = $co['server_root'] . '/backup/phpgw_data_backup.' . $co['b_intval'];
			$this->save_config($conf_file,$config);
		}
	}
?>
