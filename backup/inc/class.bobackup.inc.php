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
			'read_items'		=> True,
			'create_config'		=> True,
			'save_config'		=> True,
			'phpftp_connect'	=> True
		);

		function bobackup()
		{
			$this->config	= CreateObject('phpgwapi.config','backup');
			$this->config->read_repository();
		}

		function read_items()
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
				if (! $values['l_app'])
				{
					$error[] = lang('Plase select an application for transport !');					
				}
				elseif ($values['l_user'] != 'httpd' && (! $values['l_user'] || !$values['l_pwd']))
				{
					$error[] = lang('Plase enter username and password !');					
				}
				elseif ($values['l_app'] == 'ftp')
				{
					$ftp = $this->phpftp_connect('localhost',$values['l_user'],$values['l_pwd']);
					if (! $ftp)
					{
						$error[] = lang('The connection through ftp failed ! Please check your config values !');
					}
				}
			}

			if ($values['r_save'])
			{
				if (! $values['r_app'])
				{
					$error[] = lang('Plase select an application for transport !');					
				}
				elseif (! $values['r_user'] || ! $values['r_pwd'])
				{
					$error[] = lang('Plase enter username and password !');					
				}
				elseif ($values['r_app'] == 'ftp')
				{
					$ftp = $this->phpftp_connect($values['r_ip'],$values['r_user'],$values['r_pwd']);
					if (! $ftp)
					{
						$error[] = lang('The connection through ftp failed ! Please check your config values !');
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
			global $phpgw;

			$co = $this->get_config();

// --------------------------------- timeperiod -------------------------------

			$config = $phpgw->template->set_file(array('config_time_t' => 'config_time.tpl'));
			$config .= $phpgw->template->set_block('config_time_t','config_time','time');

			$this->sotimeperiod = CreateObject('netsaint.sotimeperiod');

			$time_list = $this->sotimeperiod->read_config_timeperiods();

			for ($i=0;$i<count($time_list);$i++)
			{
				$config .= $phpgw->template->set_var(array
				(
					'tp_name'	=> stripslashes($time_list[$i]['name']),
					'tp_alias'	=> stripslashes($time_list[$i]['alias']),
					'tp_sun'	=> $time_list[$i]['sun'],
					'tp_mon'	=> $time_list[$i]['mon'],
					'tp_tue'	=> $time_list[$i]['tue'],
					'tp_wed'	=> $time_list[$i]['wed'],
					'tp_thu'	=> $time_list[$i]['thu'],
					'tp_fri'	=> $time_list[$i]['fri'],
					'tp_sat'	=> $time_list[$i]['sat']
				));

				$phpgw->template->fp('time','config_time',True);
			}

			$config .= $phpgw->template->fp('out','config_time_t',True);

// --------------------------------- end timeperiod ---------------------------

// --------------------------------- host group -------------------------------

			$config .= $phpgw->template->set_file(array('config_hg_t' => 'config_hg.tpl'));
			$config .= $phpgw->template->set_block('config_hg_t','config_hg','hg');

			$this->sonshost = CreateObject('netsaint.sonshost');
			$hg_list = $this->sonshost->read_config_hgs();

			for ($i=0;$i<count($hg_list);$i++)
			{
				$config .= $phpgw->template->set_var(array
				(
					'hg_name'	=> stripslashes($hg_list[$i]['name']),
					'hg_alias'	=> stripslashes($hg_list[$i]['alias']),
					'hg_cg'		=> stripslashes($hg_list[$i]['cg']),
					'hg_hosts'	=> stripslashes($hg_list[$i]['hosts'])
				));

				$phpgw->template->fp('hg','config_hg',True);
			}

			$config .= $phpgw->template->fp('out','config_hg_t',True);

// --------------------------------- end host group -------------------------------

// --------------------------------- contact group -------------------------------

			$config .= $phpgw->template->set_file(array('config_cg_t' => 'config_cg.tpl'));
			$config .= $phpgw->template->set_block('config_cg_t','config_cg','cg');

			$this->sonscontact = CreateObject('netsaint.sonscontact');
			$cg_list = $this->sonscontact->read_config_cgs();

			for ($i=0;$i<count($cg_list);$i++)
			{
				$config .= $phpgw->template->set_var(array
				(
					'cg_name'		=> stripslashes($cg_list[$i]['name']),
					'cg_alias'		=> stripslashes($cg_list[$i]['alias']),
					'cg_contacts'	=> stripslashes($cg_list[$i]['contacts'])
				));

				$phpgw->template->fp('cg','config_cg',True);
			}

			$config .= $phpgw->template->fp('out','config_cg_t',True);

// --------------------------------- end contact group -------------------------------

// --------------------------------- beginn not host group -------------------------------

			$config .= $phpgw->template->set_file(array('config_ehost_t' => 'config_not_host.tpl'));
			$config .= $phpgw->template->set_block('config_ehost_t','config_ehost','ehost');

			$eh_list = $this->sonetsaint->read_config_escal('host',$host_name = '');

			for ($i=0;$i<count($eh_list);$i++)
			{
				$config .= $phpgw->template->set_var(array
				(
					'e_name'	=> stripslashes($eh_list[$i]['name']),
					'e_first'	=> $eh_list[$i]['first'],
					'e_last'	=> $eh_list[$i]['last'],
					'e_cg'		=> stripslashes($eh_list[$i]['cg'])
				));
				$phpgw->template->fp('ehost','config_ehost',True);
			}

			$config .= $phpgw->template->fp('out','config_ehost_t',True);

// --------------------------------- end not host group -------------------------------

			$conf_file = $co['conf_dir'] . '/hosts.cfg';
			$this->save_config($conf_file,$config);

// --------------------------------- end hosts.cfg ------------------------------------


// --------------------------------- commands -----------------------------------------

			$config = $phpgw->template->set_file(array('config_command_t' => 'config_comands.tpl'));
			$config .= $phpgw->template->set_block('config_command_t','config_command','command');

			$command_list = $this->sonetsaint->read_config_commands();

			for ($i=0;$i<count($command_list);$i++)
			{
				$config .= $phpgw->template->set_var(array
				(
					'c_name'		=> stripslashes($command_list[$i]['name']),
					'c_line'		=> stripslashes($command_list[$i]['line'])
				));

				$phpgw->template->fp('command','config_command',True);
			}

			$config .= $phpgw->template->fp('out','config_command_t',True);

			$conf_file = $co['conf_dir'] . '/commands.cfg';
			$this->save_config($conf_file,$config);

// --------------------------------- end commands -------------------------------

// --------------------------------- contacts -----------------------------------

			$config = $phpgw->template->set_file(array('config_contact_t' => 'config_contact.tpl'));
			$config .= $phpgw->template->set_block('config_contact_t','config_contact','contact');

			$contact_list = $this->sonscontact->read_config_contacts();

			for ($i=0;$i<count($contact_list);$i++)
			{
				$config .= $phpgw->template->set_var(array
				(
					'c_name'		=> stripslashes($contact_list[$i]['name']),
					'c_alias'		=> stripslashes($contact_list[$i]['alias']),
					'csv_period'	=> stripslashes($contact_list[$i]['csv_period']),
					'h_period'		=> stripslashes($contact_list[$i]['h_period']),
					's_rec'			=> $contact_list[$i]['s_rec'],
					's_crit'		=> $contact_list[$i]['s_crit'],
					's_warn'		=> $contact_list[$i]['s_warn'],
					'h_rec'			=> $contact_list[$i]['h_rec'],
					'h_down'		=> $contact_list[$i]['h_down'],
					'h_unreach'		=> $contact_list[$i]['h_unreach'],
					's_command'		=> stripslashes($contact_list[$i]['s_command']),
					'h_command'		=> stripslashes($contact_list[$i]['h_command']),
					'email'			=> stripslashes($contact_list[$i]['email']),
					'pager'			=> stripslashes($contact_list[$i]['pager'])
				));

				$phpgw->template->fp('contact','config_contact',True);
			}

			$config .= $phpgw->template->fp('out','config_contact_t',True);

			$conf_file = $co['conf_dir'] . '/contacts.cfg';
			$this->save_config($conf_file,$config);

			$netsaint_include[] = '/etc/contacts.cfg'; 

// --------------------------------- end contacts -------------------------------

// --------------------------------- hosts --------------------------------------

			$host_list = $this->sonshost->read_config_hosts();

			for ($i=0;$i<count($host_list);$i++)
			{
				$config = $phpgw->template->set_file(array('config_host'	=> 'config_host.tpl',
														'config_service'	=> 'config_service.tpl',
														'config_nservice'	=> 'config_not_service.tpl',
														'config_eservice'	=> 'config_e_service.tpl'));

				$config .= $phpgw->template->set_var('h_name',stripslashes($host_list[$i]['name']));
				$config .= $phpgw->template->set_var('h_alias',stripslashes($host_list[$i]['alias']));
				$config .= $phpgw->template->set_var('h_address',stripslashes($host_list[$i]['address']));
				$config .= $phpgw->template->set_var('h_parent',stripslashes($host_list[$i]['parent']));
				$config .= $phpgw->template->set_var('h_command',stripslashes($host_list[$i]['command']));
				$config .= $phpgw->template->set_var('h_max',$host_list[$i]['max']);
				$config .= $phpgw->template->set_var('h_intval',$host_list[$i]['intval']);
				$config .= $phpgw->template->set_var('h_period',stripslashes($host_list[$i]['period']));
				$config .= $phpgw->template->set_var('h_rec',$host_list[$i]['recover']);
				$config .= $phpgw->template->set_var('h_down',$host_list[$i]['down']);
				$config .= $phpgw->template->set_var('h_unreach',$host_list[$i]['unreach']);
				$config .= $phpgw->template->set_var('h_event',stripslashes($host_list[$i]['event']));

				$config .= $phpgw->template->fp('out','config_host',True);

				$service_list = $this->sonetsaint->read_config_services($host_list[$i]['name']);
				for ($j=0;$j<count($service_list);$j++)
				{
					$config .= $phpgw->template->set_var(array
					(
						's_host'		=> stripslashes($service_list[$j]['host']),
						's_descr'		=> stripslashes($service_list[$j]['descr']),
						's_vol'			=> $service_list[$j]['vol'],
						'check_period'	=> stripslashes($service_list[$j]['s_period']),
						's_max'			=> $service_list[$j]['max'],
						'check_intval'	=> $service_list[$j]['c_intval'],
						'retry_intval'	=> $service_list[$j]['r_intval'],
						'cg'			=> stripslashes($service_list[$j]['cg']),
						'not_intval'	=> $service_list[$j]['not_intval'],
						'not_period'	=> stripslashes($service_list[$j]['c_period']),
						'not_rec'		=> $service_list[$j]['rec'],
						'not_crit'		=> $service_list[$j]['crit'],
						'not_warn'		=> $service_list[$j]['warn'],
						's_event'		=> stripslashes($service_list[$j]['event']),
						's_command'		=> stripslashes($service_list[$j]['command'])
					));
					$config .= $phpgw->template->fp('service','config_service',True);
				}

				$config .= $phpgw->template->fp('out','config_nservice',True);

				$es_list = $this->sonetsaint->read_config_escal('serv',$host_list[$i]['name']);
				for ($k=0;$k<count($es_list);$k++)
				{
					$config .= $phpgw->template->set_var(array
					(
						'es_name'	=> stripslashes($es_list[$k]['name']),
						'es_descr'	=> stripslashes($es_list[$k]['descr']),
						'es_first'	=> $es_list[$k]['first'],
						'es_last'	=> $es_list[$k]['last'],
						'es_cg'		=> stripslashes($es_list[$k]['cg'])
					));
					$config .= $phpgw->template->fp('eservice','config_eservice',True);
				}

				$conf_file = $co['conf_dir'] . '/' . $host_list[$i]['name'] . '.cfg';
				$this->save_config($conf_file,$config);

				$netsaint_include[] = '/etc/' . $host_list[$i]['name'] . '.cfg'; 
			}

// --------------------------------- end hosts -----------------------------------

// --------------------------------- ns ------------------------------------------

			$config = $phpgw->template->set_file(array('config_ns_t' => 'config_netsaint.tpl'));
			$config .= $phpgw->template->set_block('config_ns_t','config_ns','ns');

			$config .= $phpgw->template->set_var('ns_dir',$co['ns_dir']);
			$config .= $phpgw->template->set_var('ns_user',$co['ns_user']);
			$config .= $phpgw->template->set_var('ns_group',$co['ns_group']);

			for ($i=0;$i<count($netsaint_include);$i++)
			{
				$config .= $phpgw->template->set_var(array
				(
					'include_files' => 'cfg_file=' . $co['ns_dir'] . $netsaint_include[$i]
				));	
				$phpgw->template->fp('ns','config_ns',True);
			}

			$config .= $phpgw->template->fp('out','config_ns_t',True);

			$conf_file = $co['conf_dir'] . '/netsaint.cfg';
			$this->save_config($conf_file,$config);

// --------------------------------- end ns --------------------------------------

		}
	}
?>
