<?php

	class boaccess_history
	{
		function boaccess_history()
		{
			$this->so       = createobject('admin.soaccess_history');
		}

		function list_history($account_id,$start,$order,$sort)
		{
			global $phpgw;

			$records = $this->so->list_history($account_id,$start,$order,$sort);
			while (is_array($records) && list(,$record) = each($records))
			{
				if ($record['li'] && $record['lo'])
				{
					$total = ($record['lo'] - $record['li']);
					if ($total > 86400 && $total > 172800)
					{
						$total = gmdate('z \d\a\y\s - G:i:s',$total);
					}
					else if ($total > 172800)
					{
						$total = gmdate('z \d\a\y - G:i:s',$total);
					}
					else
					{
						$total = gmdate('G:i:s',$total);
					}
				}

				if ($record['li'])
				{
					$record['li'] = $phpgw->common->show_date($record['li']);
				}

				if ($record['lo'] != '')
				{
					$record['lo'] = $phpgw->common->show_date($record['lo']);
				}

				if (ereg('@',$record['loginid']))
				{
					$t = split('@',$record['loginid']);
					$record['loginid'] = $t[0];
				}

				$_records[] = array(
					'loginid'    => $record['loginid'],
					'ip'         => $record['ip'],
					'li'         => $record['li'],
					'lo'         => $record['lo'],
					'account_id' => $record['account_id'],
					'total'      => $total
				);
			}
			return $_records;
		}

		function grab_fullname($account_id)
		{
			global $phpgw;
			$acct = createobject('phpgwapi.accounts',$account_id);
			$acct->read_repository();
			return $phpgw->common->display_fullname($acct->data['account_lid'],$acct->data['firstname'],$acct->data['lastname']);
		}

		function total($account_id)
		{
			return $this->so->total($account_id);
		}

		function return_logged_out($account_id)
		{
			return $this->so->return_logged_out($account_id);
		}
	}