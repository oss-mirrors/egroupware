<?php
	/*******************************************************************\
	* phpGroupWare - Projects                                           *
	* http://www.phpgroupware.org                                       *
	* This program is part of the GNU project, see http://www.gnu.org/	*
	*                                                                   *
	* Project Manager                                                   *
	* Written by Bettina Gille [ceb@phpgroupware.org]                   *
	* -----------------------------------------------                   *
	* Copyright 2000 - 2003 Free Software Foundation, Inc               *
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
	/* $Source$ */

	class bostatistics
	{
		var $debug;
		var $start;
		var $query;
		var $order;
		var $sort;
		var $type;

		var $public_functions = array
		(
			'get_userstat_pro'	=> True,
			'get_stat_hours'	=> True,
			'get_userstat_all'	=> True,
			'get_users'			=> True,
			'get_employees'		=> True
		);

		function bostatistics()
		{
			$this->debug		= False;
			$this->sostatistics	= CreateObject('projects.sostatistics');
			$this->boprojects	= CreateObject('projects.boprojects');

			$this->date_diff	= 0;
		}

		function get_users($type, $start, $sort, $order, $query)
		{
			$users = $GLOBALS['phpgw']->accounts->get_list($type, $start, $sort, $order, $query);
			$this->total_records = $GLOBALS['phpgw']->accounts->total;
			return $users;
		}

		function get_userstat_pro($account_id, $values)
		{
			return $this->sostatistics->user_stat_pro($account_id, $values);
		}

		function get_stat_hours($type, $account_id, $project_id, $values)
		{
			return $this->sostatistics->stat_hours($type, $account_id, $project_id, $values);
		}

		function get_employees($project_id, $values)
		{
			return $this->sostatistics->pro_stat_employees($project_id, $values);
		}

		function set_x_text($smonth,$syear)
		{
			$graph_sdate = mktime(0,0,0,$smonth,1,$syear);
			$graph_edate = mktime(0,0,0,$smonth,date('t',$graph_sdate),$syear);


			$graph_sdateout	= $GLOBALS['phpgw']->common->show_date($graph_sdate,$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);
			$graph_edateout	= $GLOBALS['phpgw']->common->show_date($graph_edate,$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);

			$diff				= date('t',$smonth);
			$this->date_diff	= $diff;

			$this->graph->line_captions_x[0]['date_formatted']	= $GLOBALS['phpgw']->common->show_date($graph_sdate,'m/d');
			$this->graph->line_captions_x[0]['date']			= $graph_sdate;

			for ($i=1;$i<($diff-1);$i++)
			{
				$temp_date = '';
				$temp_date = mktime(0,0,0,date('m',$graph_sdate),date('d',$graph_sdate)+$i,date('Y',$graph_sdate));

				$this->graph->line_captions_x[$i]['date_formatted']	= $GLOBALS['phpgw']->common->show_date($temp_date,'d');
				$this->graph->line_captions_x[$i]['date']			= $temp_date;
			}

			$this->graph->line_captions_x[$diff]['date_formatted']	= $GLOBALS['phpgw']->common->show_date($graph_edate,'m/d');
			$this->graph->line_captions_x[$diff]['date']			= $graph_edate;

			$this->graph->title = lang('Gantt chart from %1 to %2', $graph_sdateout,$graph_edateout);
		}

		function set_y_text($pro = 0)
		{
			for($i=0;$i<count($pro);$i++)
			{
				$this->graph->line_captions_y[$i] = $pro[$i]['title'];
			}
		}

		function show_graph($params)
		{
			$project_id	= $params['project_id'];
			$syear		= (isset($params['syear'])?$params['syear']:date('Y'));
			$smonth		= (isset($params['smonth'])?$params['smonth']:date('m'));

			$this->graph = CreateObject('phpgwapi.gdgraph',$this->debug);

			$this->boprojects->order = 'level';
			$this->boprojects->sort = 'DESC';
			$pro = $this->boprojects->list_projects(array('type' => 'mainandsubs','main' => $project_id,'mstones_stat' => True));

			while(is_array($pro) && list(,$p) = each($pro))
			{
				while(is_array($p['mstones']) && list(,$s) = each($p['mstones']))
				{
					$spro[] = array
					(
						'title'			=> $s['title'],
						'extracolor'	=> 'yellow',
						'sdate'			=> $p['sdate'],
						'edate'			=> $s['edate'],
						'pro_id'		=> $p['project_id']
					);
				}

				$spro[] = array
				(
					'title'		=> $p['title'],
					'sdate'		=> $p['sdate'],
					'edate'		=> $p['edate']?$p['edate']:mktime(0,0,0,date('m'),date('d'),date('Y')),
					'color'		=> $p['level'],
					'pro_id'	=> $p['project_id']
				);
			}

			if(is_array($spro))
			{
				$this->graph->data = $spro;
			}

			$this->set_x_text($smonth,$syear);

			$this->set_y_text($spro);

			$this->graph->num_lines_y = count($spro)+2;
			$this->graph->num_lines_x = $this->date_diff;
			$this->graph->render();
		}
	}
?>
