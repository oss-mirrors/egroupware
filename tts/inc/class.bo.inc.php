<?php

	class bo
	{
		var $db;
		var $so;
		var $historylog;
		var $total_records;
		var $public_methods = array(
			'list_methods' => True,
			'add_ticket'   => True
		);

		function bo()
		{
			$this->db         = $GLOBALS['phpgw']->db;
			$this->so         = createobject('tts.so');
			$this->historylog = createobject('phpgwapi.historylog','tts');
			$this->historylog->types = array(
				'R' => 'Re-opened',
				'X' => 'Closed',
				'O' => 'Opened',
				'A' => 'Re-assigned',
				'P' => 'Priority changed',
				'T' => 'Category changed',
				'S' => 'Subject changed',
				'B' => 'Billing rate',
				'H' => 'Billing hours'
			);
		}

		function list_methods($_type)
		{
			if (is_array($_type))
			{
				$_type = $_type['type'];
			}

			switch($_type)
			{
				case 'xmlrpc':
					$xml_functions = array(
						'list_methods' => array(
							'function'  => 'list_methods',
							'signature' => array(array(xmlrpcStruct,xmlrpcString)),
							'docstring' => lang('Read this list of methods.')
						),
						'save_ticket' => array(
							'function'  => 'save_ticket',
							'signature' => array(array(xmlrpcStruct,xmlrpcStruct)),
							'docstring' => lang('Creates a new ticket, returns ticket_id')
						),
						'list_tickets' => array(
							'function'  => 'list_tickets',
							'signature' => array(array(xmlrpcStruct,xmlrpcStruct)),
							'docstring' => lang('Creates a struct of tickets')
						),
						'read_ticket' => array(
							'function'  => 'read_ticket',
							'signature' => array(array(xmlrpcStruct,xmlrpcStruct)),
							'docstring' => lang('Returns a struct of values of a single ticket')
						),
						'ticket_notes' => array(
							'function'  => 'ticket_notes',
							'signature' => array(array(xmlrpcStruct,xmlrpcInt)),
							'docstring' => lang('Returns the aditional notes attached to a ticket')
						),
						'ticket_history' => array(
							'function'  => 'ticket_history',
							'signature' => array(array(xmlrpcStruct,xmlrpcInt)),
							'docstring' => lang('Returns a struct of a tickets history')
						),
						'update' => array(
							'function'  => 'update',
							'signature' => array(array(xmlrpcInt,xmlrpcStruct)),
							'docstring' => lang('Updates ticket')
						)
					);
					return $xml_functions;
					break;

				case 'soap':
					return $this->soap_functions;
					break;

				default:
					return array();
					break;
			}
		}

		function cached_accounts($account_id)
		{
			global $phpgw;

			$this->accounts = CreateObject('phpgwapi.accounts',$account_id);
			$this->accounts->read_repository();

			$cached_data[$this->accounts->data['account_id']]['account_lid'] = $this->accounts->data['account_lid'];
			$cached_data[$this->accounts->data['account_id']]['firstname']   = $this->accounts->data['firstname'];
			$cached_data[$this->accounts->data['account_id']]['lastname']    = $this->accounts->data['lastname'];

			return $cached_data;
		}

		function list_tickets($params)
		{
			$db2 = $this->db;
			$this->db->query("select * from phpgw_tts_tickets $filtermethod $sortmethod",__LINE__,__FILE__);
			$this->total_records = $this->db->num_rows();

			while ($this->db->next_record())
			{
				$db2->query("select count(*) from phpgw_tts_views where view_id='" . $this->db->f('ticket_id')
					. "' and view_account_id='" . $GLOBALS['phpgw_info']['user']['account_id'] . "'",__LINE__,__FILE__);
				$db2->next_record();

				if ($db2->f(0))
				{
					$ticket_read = 'old';
				}
				else
				{
					$ticket_read = 'new';
				}

				$history_values = $this->historylog->return_array(array(),array('O'),'','',$this->db->f('ticket_id'));

				$cached_data = $this->cached_accounts($this->db->f('ticket_owner'));
				$owner = $GLOBALS['phpgw']->common->display_fullname($cached_data[$this->db->f('ticket_owner')]['account_lid'],
					$cached_data[$this->db->f('ticket_owner')]['firstname'],$cached_data[$this->db->f('ticket_owner')]['lastname']);

				$cached_data = $this->cached_accounts($this->db->f('ticket_assignedto'));
				$assignedto = $GLOBALS['phpgw']->common->display_fullname($cached_data[$this->db->f('ticket_assignedto')]['account_lid'],
					$cached_data[$this->db->f('ticket_assignedto')]['firstname'],$cached_data[$this->db->f('ticket_assignedto')]['lastname']);

				$r[] = array(
					'id'             => (int)$this->db->f('ticket_id'),
					'group'          => $this->db->f('ticket_group'),
					'priority'       => $this->db->f('ticket_priority'),
					'owner'          => $owner,
					'assignedto'     => $assignedto,
					'subject'        => $this->db->f('ticket_subject'),
					'category'       => $this->db->f('ticket_category'),
					'billable_hours' => $this->db->f('ticket_billable_hours'),
					'billable_rate'  => $this->db->f('ticket_billable_rate'),
					'status'         => $this->db->f('ticket_status'),
					'details'        => $this->db->f('ticket_details'),
					'odate'          => $GLOBALS['phpgw']->common->show_date($history_values[0]['datetime'],$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']),
					'odate_epoch'    => (int)$history_values[0]['datetime'],
					'view'           => $ticket_read
				);
			}
			return $r;
		}

		function read_ticket($params = '')
		{
			$cat = createobject('phpgwapi.categories');

			// Have they viewed this ticket before ?
			$this->db->query("select count(*) from phpgw_tts_views where view_id='" . $params['id']
					. "' and view_account_id='" . $GLOBALS['phpgw_info']['user']['account_id'] . "'",__LINE__,__FILE__);
			$this->db->next_record();

			if (! $this->db->f(0))
			{
				$this->db->query("insert into phpgw_tts_views values ('" . $params['id'] . "','"
					. $GLOBALS['phpgw_info']['user']['account_id'] . "','" . time() . "')",__LINE__,__FILE__);
			}

			$this->db->query("select * from phpgw_tts_tickets where ticket_id='" . $params['id'] . "'",__LINE__,__FILE__);
			$this->db->next_record();

			$cached_data = $this->cached_accounts($this->db->f('ticket_owner'));
			$owner = $GLOBALS['phpgw']->common->display_fullname($cached_data[$this->db->f('ticket_owner')]['account_lid'],
				$cached_data[$this->db->f('ticket_owner')]['firstname'],$cached_data[$this->db->f('ticket_owner')]['lastname']);

			$cached_data = $this->cached_accounts($this->db->f('ticket_assignedto'));
			$assignedto = $GLOBALS['phpgw']->common->display_fullname($cached_data[$this->db->f('ticket_assignedto')]['account_lid'],
				$cached_data[$this->db->f('ticket_assignedto')]['firstname'],$cached_data[$this->db->f('ticket_assignedto')]['lastname']);

			$r = array(
				'id'             => (int)$this->db->f('ticket_id'),
				'group'          => $this->db->f('ticket_group'),
				'priority'       => $this->db->f('ticket_priority'),
				'owner'          => $owner,
				'assignedto'     => $assignedto,
				'subject'        => $this->db->f('ticket_subject'),
				'category'       => $cat->id2name($this->db->f('ticket_category')),
				'billable_hours' => $this->db->f('ticket_billable_hours'),
				'billable_rate'  => $this->db->f('ticket_billable_rate'),
				'status'         => $this->db->f('ticket_status'),
				'details'        => $this->db->f('ticket_details'),
				'odate'          => $GLOBALS['phpgw']->common->show_date($history_values[0]['datetime'],$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']),
				'odate_epoch'    => (int)$history_values[0]['datetime'],
				'view'           => $this->db->f('ticket_view'),
				'history_size'   => count($this->historylog->return_array(array('C','O'),array(),'','',$params['id']))
			);
			return $r;			
		}

		function ticket_notes($params)
		{
			$history_array = $this->historylog->return_array(array(),array('C'),'','',$params[0]);

			return $history_array;
		}

		function ticket_history($params)
		{
			$cat = createobject('phpgwapi.categories');
			// This function needs to make use of the alternate handle option (jengo)
			$history_array = $this->historylog->return_array(array('C','O'),array(),'','',$params[0]);
			while (is_array($history_array) && list(,$value) = each($history_array))
			{
				$datetime = $GLOBALS['phpgw']->common->show_date($value['datetime']);
				$owner    = $value['owner'];

				switch ($value['status'])
				{
					case 'R': $type = lang('Re-opened'); break;
					case 'X': $type = lang('Closed');    break;
					case 'O': $type = lang('Opened');    break;
					case 'A': $type = lang('Re-assigned'); break;
					case 'P': $type = lang('Priority changed'); break;
					case 'T': $type = lang('Category changed'); break;
					case 'S': $type = lang('Subject changed'); break;
					case 'H': $type = lang('Billable hours changed'); break;
					case 'B': $type = lang('Billable rate changed'); break;
					default: break;
				}

				$action = ($type?$type:'');
				unset($type);

				if ($value['status'] == 'A')
				{
					if (! $value['new_value'])
					{
						$new_value = lang('None');
					}
					else
					{
						$new_value = $GLOBALS['phpgw']->accounts->id2name($value['new_value']);
					}
				}
				else if ($value['status'] == 'T')
				{
 					$new_value = $cat->id2name($value['new_value']);
				}
				else if ($value['status'] != 'O' && $value['new_value'])
				{
					$new_value = $value['new_value'];
				}
				else
				{
					$new_value = '';
				}
	
				$r[] = array(
					'owner'     => $owner,
					'action'    => $action,
					'new_value' => $new_value,
					'old_value' => '' . $old_value,
					'datetime'  => $datetime
				);
			}

			return $r;
		}

		function save_ticket($params)
		{
			$this->db->query("insert into phpgw_tts_tickets (ticket_group,ticket_priority,ticket_owner,"
				. "ticket_assignedto,ticket_subject,ticket_category,ticket_billable_hours,"
				. "ticket_billable_rate,ticket_status,ticket_details) values ('0','"
				. $params['priority'] . "','"
				. $GLOBALS['phpgw_info']['user']['account_id'] . "','"
				. $params['assignedto'] . "','"
				. $params['subject'] . "','"
				. $params['category'] . "','"
				. $params['billable_hours'] . "','"
				. $params['billable_rate'] . "','O','"
				. addslashes($params['details']) . "')",__LINE__,__FILE__);

			$ticket_id = $this->db->get_last_insert_id('phpgw_tts_tickets','ticket_id');
			$this->historylog->add('O',$ticket_id,'');
			return $ticket_id;
		}

		function update($params)
		{
			// So where on the same page with our transactions
			$this->historylog->db = &$this->db;

			$ticket    = $params;
			$ticket_id = $params['id'];
			// DB Content is fresher is always more up to date
			$this->db->query("select * from phpgw_tts_tickets where ticket_id='"
				. $params['id'] . "'",__LINE__,__FILE__);
			$this->db->next_record();
	
			$oldassigned = $this->db->f('ticket_assignedto');
			$oldpriority = $this->db->f('ticket_priority');
			$oldcategory = $this->db->f('ticket_category');
			$old_status  = $this->db->f('ticket_status');

			$this->db->transaction_begin();

			/*
			**	phpgw_tts_append.append_type - Defs
			**	R - Reopen ticket
			** X - Ticket closed
			** O - Ticket opened
			** C - Comment appended
			** A - Ticket assignment
			** P - Priority change
			** T - Category change
			** S - Subject change
			** B - Billing rate
			** H - Billing hours
			*/

			if ($old_status != $ticket['status'])
			{
				$fields_updated = True;
				$this->historylog->add($ticket['status'],$ticket_id,'');

				$this->db->query("update phpgw_tts_tickets set ticket_status='"
					. $ticket['status'] . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
			}
	
			if ($oldassigned != $ticket['assignedto'])
			{
				$fields_updated = True;
				$this->db->query("update phpgw_tts_tickets set ticket_assignedto='" . $ticket['assignedto']
					. "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
				$this->historylog->add('A',$ticket_id,$ticket['assignedto']);
			}
	
			if ($oldpriority != $ticket['priority'])
			{
				$fields_updated = True;
				$this->db->query("update phpgw_tts_tickets set ticket_priority='" . $ticket['priority']
					. "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
				$this->historylog->add('P',$ticket_id,$ticket['priority']);
			}
	
			if ($oldcategory != $ticket['category'])
			{
				$fields_updated = True;
				$this->db->query("update phpgw_tts_tickets set ticket_category='" . $ticket['category']
					. "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
				$this->historylog->add('T',$ticket_id,$ticket['category']);
			}
	
			if ($old_billable_hours != $ticket['billable_hours'])
			{
				$fields_updated = True;
				$this->db->query("update phpgw_tts_tickets set ticket_billable_hours='" . $ticket['billable_hours']
					. "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
				$this->historylog->add('H',$ticket_id,$ticket['billable_hours']);
			}
	
			if ($old_billable_rate != $ticket['billable_rate'])
			{
				$fields_updated = True;
				$this->db->query("update phpgw_tts_tickets set ticket_billable_rate='" . $ticket['billable_rate']
					. "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
				$this->historylog->add('B',$ticket_id,$ticket['billable_rate']);
			}
	
			if ($ticket['note'])
			{
				$fields_updated = True;
				$this->historylog->add('C',$ticket_id,$ticket['note']);
	
				// Do this before we go into mail_ticket()
				$this->db->transaction_commit();
	
				if ($GLOBALS['phpgw_info']['server']['tts_mailticket'])
				{
					//mail_ticket($ticket_id);
				}
			}
			else
			{
				// Only do our commit once
				$this->db->transaction_commit();
			}
			return True;
		}

	}
