<?php
	/*******************************************************************\
	* eGroupWare - Projects                                             *
	* http://www.egroupware.org                                         *
	* This program is part of the GNU project, see http://www.gnu.org/  *
	*                                                                   *
	* Project Manager                                                   *
	* Written by Bettina Gille [ceb@phpgroupware.org]                   *
	* Written by Lars Kneschke [lkneschke@linux-at-work.de]             *
	* -----------------------------------------------                   *
	* Copyright 2000 - 2004 Free Software Foundation, Inc               *
	* Copyright 2004 - 2004 Lars Kneschke                               *
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
			$action			= get_var('action',array('GET'));
			$this->debug		= False;
			$this->sostatistics	= CreateObject('projects.sostatistics');
			$this->boprojects	= CreateObject('projects.boprojects',True,$action);
			$this->displayCharset	= $GLOBALS['phpgw']->translation->charset();
			$this->botranslation	= CreateObject('phpgwapi.translation');

			$this->start		= $this->boprojects->start;
			$this->query		= $this->boprojects->query;
			$this->filter		= $this->boprojects->filter;
			$this->order		= $this->boprojects->order;
			$this->sort		= $this->boprojects->sort;
			$this->cat_id		= $this->boprojects->cat_id;

			$this->date_diff	= 0;
		}

		function get_users($type, $start, $sort, $order, $query)
		{
			$pro_employees = $this->boprojects->read_projects_acl();

			//_debug_array($pro_employees);

			if(is_array($pro_employees))
			{
				$users = $GLOBALS['phpgw']->accounts->get_list('accounts', $start, $sort, $order, $query);

				if(is_array($users))
				{
					foreach($users as $user)
					{
						if(in_array($user['account_id'],$pro_employees))
						{
							$rights[] = $user;
						}
						else
						{
							$norights[] = $user;
						}
					}
				}
				$this->total_records = ($GLOBALS['phpgw']->accounts->total - count($norights));
				return $rights;
			}
			return False;
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
		
		/**
		* creates the image for the gantt chart
		*
		* @param $_params	array containing projectdata, start- and enddate
		* @author	Lars Kneschke / Bettina Gille
		* @returns	nothing - writes image to disk
		*/
		function show_graph($params)
		{
			include(PHPGW_SERVER_ROOT . '/projects/inc/jpgraph-1.5.2/src/jpgraph.php');
			include(PHPGW_SERVER_ROOT . '/projects/inc/jpgraph-1.5.2/src/jpgraph_gantt.php');

			//_debug_array($params);
			$project_array	= $params['project_array'];
			$sdate		= $params['sdate'];
			$edate		= $params['edate'];

			$this->graph = CreateObject('phpgwapi.gdgraph',$this->debug);

			//$this->boprojects->order = 'parent';
			$this->boprojects->limit		= False;
			$this->boprojects->html_output	= False;

			if(is_array($project_array))
			{
				$projects = array();
				foreach($project_array as $pro)
				{
					$project = $this->boprojects->list_projects(array('action' => 'mainsubsorted','project_id' => $pro,'mstones_stat' => True));

					if(is_array($project))
					{
						$i = count($projects);
						for($k=0;$k<count($project);$k++)
						{
							$projects[$i+$k] = $project[$k];
						}
					}
				}
			}

			if(is_array($projects))
			{
#				$num_pro = count($projects) - 1;
#
#				$k = 0;
#				for($i=$num_pro;$i>=0;--$i)
#				{
#					$sopro[$k] = $projects[$i];
#					$k++;
#				}
#			_debug_array($sopro);	
#				foreach($sopro as $pro)
#				{
#					if(is_array($pro['mstones']))
#					{
#						foreach($pro['mstones'] as $ms)
#						{
#							$spro[] = array
#							(
#								'title'			=> str_repeat(' ',$spro['level']) . '[MS]' . $ms['title'],
#								'extracolor'	=> 'yellow',
#								'sdate'			=> $pro['sdate'],
#								'edate'			=> $ms['edate'],
#								'pro_id'		=> $pro['project_id']
#							);
#						}
#
#						$color_legend['milestone'] = array('title'	=> '[MS]' . lang('milestone'),
#													'extracolor'	=> 'yellow');
#					}
#
#					$previous = '';
#					if($pro['previous'] > 0)
#					{
#						$previous = $this->boprojects->read_single_project($pro['previous']);
#						$spro[] = array
#						(
#							'title'			=> str_repeat(' ',$spro['level']) . '[!]' . $previous['title'],
#							'extracolor'	=> 'darkorange',
#							'sdate'			=> $previous['sdate'],
#							'edate'			=> $previous['edate'],
#							'pro_id'		=> $previous['project_id'],
#							'f_sdate'		=> $pro['sdate']
#						);
#
#						$color_legend['previous'] = array('title'	=> '[!]' . lang('previous project'),
#													'extracolor'	=> 'darkorange');
#					}
#
#					$spro[] = array
#					(
#						'title'		=> $pro['title'],
#						'sdate'		=> $pro['sdate'],
#						'edate'		=> $pro['edate']?$pro['edate']:mktime(0,0,0,date('m'),date('d'),date('Y')),
#						'color'		=> $pro['level'],
#						'pro_id'	=> $pro['project_id'],
#						'previous'	=> $pro['previous']
#					);
#					//set_y_text
#					$this->graph->line_captions_y[$i] = $pro['title'];
#
#					$color_legend[$pro['level']] = array('title'	=> $pro['level']==0?lang('main project'):lang('sub project level %1',$pro['level']),
#															'color'	=> $pro['level']);
#				}
#
#				$num_legend = count($color_legend);
#				$k = 0;
#				for($i=0;$i<$num_legend;++$i)
#				{
#					if(is_array($color_legend[$i]))
#					{
#						$color[$k] = $color_legend[$i];
#						$k++;
#					}
#				}
#				if(is_array($color_legend['previous']))
#				{
#					$num = count($color);
#					$color[$num] = $color_legend['previous'];
#				}
#
#				if(is_array($color_legend['milestone']))
#				{
#					$num = count($color);
#					$color[$num] = $color_legend['milestone'];
#				}
#
#				$this->graph->color_legend = $color;
#
#				//set_x_text
#				$this->graph->date_format($sdate,$edate);

				$sdate = $sdate + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
				$sdateout = $GLOBALS['phpgw']->common->show_date($sdate,$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);

				$edate = $edate + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
				$edateout = $GLOBALS['phpgw']->common->show_date($edate,$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);
#				$this->graph->title = lang('Gantt chart from %1 to %2',$sdateout,$edateout);

				// Standard calls to create a new graph
				$graph = new GanttGraph(-1,-1,"auto");
				$graph->SetShadow();
				$graph->SetBox();
				
				// For illustration we enable all headers.
				#$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HDAY | GANTT_HWEEK);
				#$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HWEEK);
				$duration = $edate - $sdate;
				
				if($duration < 5958000)
					$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HDAY | GANTT_HWEEK);
				elseif($duration < 13820400)
					$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HWEEK);
				else
					$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH);
				
				// For the week we choose to show the start date of the week
				// the default is to show week number (according to ISO 8601)
				$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);
				//$graph->scale->SetDateLocale(2);
				
				// Change the scale font
				$graph->scale->week->SetFont(FF_FONT0);
				$graph->scale->year->SetFont(FF_VERA,FS_BOLD,12);
				
				// Titles for chart
				$graph->title->Set(lang('project overview'));
				$graph->subtitle->Set(lang('from %1 to %2',$sdateout,$edateout));
				$graph->title->SetFont(FF_VERA,FS_BOLD,12);
				
				// set the start and end date
				// add one day to the end is needed internaly by jpgraph
				$graph->SetDateRange(date('Y-m-d',$sdate), date('Y-m-d',$edate+86400));
				
				foreach($projects as $pro)
				{
					$ptime_pro = $this->boprojects->return_value('ptime',$pro[project_id]);
					$acc = $this->boprojects->get_budget(array('project_id' => $pro[project_id],'ptime' => $ptime_pro));
					if($ptime_pro > 0)
						$finnishedPercent = (100/$ptime_pro)*$acc[uhours_jobs_wminutes];
					else
						$finnishedPercent = 0;
					$previous = '';
					if($pro['previous'] > 0)
					{
						$previous = $this->boprojects->read_single_project($pro['previous']);
						$spro[] = array
						(
							'title'			=> str_repeat(' ',$spro['level']) . '[!]' . $previous['title'],
							'extracolor'		=> 'darkorange',
							'sdate'			=> $previous['sdate'],
							'edate'			=> $previous['edate'],
							'pro_id'		=> $previous['project_id'],
							'f_sdate'		=> $pro['sdate']
						);

						$color_legend['previous'] = array('title'	=> '[!]' . lang('previous project'),
													'extracolor'	=> 'darkorange');
					}
					
					// add a empty row before new project
					if($pro['level'] == 0 && $counter > 0)
						$counter++;
					
					$spro = array
					(
						'title'		=> $pro['title'],
						'sdate'		=> $pro['sdate'],
						'edate'		=> $pro['edate']?$pro['edate']:mktime(0,0,0,date('m'),date('d'),date('Y')),
						'color'		=> $pro['level'],
						'pro_id'	=> $pro['project_id'],
						'previous'	=> $pro['previous']
					);
					
					// convert title to iso-8859-1
					$spro[title] = $this->botranslation->convert(
						$spro[title],
						$this->displayCharset,
						'iso-8859-1');
					
					if($spro[edate] < $sdate)
						continue;
						
					if($spro[edate] > $edate)
						$spro[edate] = $edate;
						
					if($spro[sdate] < $sdate)
						$spro[sdate] = $sdate;

					$bar = new GanttBar($counter,
						$spro[title],
						date('Y-m-d',$spro[sdate]),
						date('Y-m-d',$spro[edate]),
						round($finnishedPercent).'%',
						0.5);
					
					// mark beginn of new project bold
					if($pro['level'] == 0)
					{
						$bar->title->SetFont(FF_FONT1,FS_BOLD);
						$bar->SetPattern(BAND_SOLID,"yellow3");
					}
					else
					{
						// For illustration lets make each bar be red with yellow diagonal stripes
						$bar->SetPattern(BAND_SOLID,"#cccccc");
					}
						
					
					// To indicate progress each bar can have a smaller bar within
					// For illustrative purpose just set the progress to 50% for each bar
					$bar->progress->SetHeight(0.2);
					if($finnishedPercent > 100)
					{
						$bar->progress->Set(1);
						$bar->progress->SetPattern(GANTT_SOLID,"darkred",98);
						#$bar->SetFillColor("darkred");
						#$bar->SetPattern(BAND_SOLID,"#ff6666");
					}
					else
					{
						$bar->progress->Set($finnishedPercent/100);
						$bar->progress->SetPattern(GANTT_SOLID,"darkgreen",98);
						#$bar->SetFillColor("dimgray");
						#$bar->SetFillColor("cornflowerblue");
					}
					
					// ... and add the bar to the gantt chart
					$graph->Add($bar);
					
					$counter++;

					// check for milstones
					if(is_array($pro['mstones']))
					{
						foreach($pro['mstones'] as $ms)
						{
							$spro = array
							(
								'title'			=> str_repeat(' ',$spro['level']) . $ms['title'],
								'extracolor'	=> 'yellow',
								'sdate'			=> $pro['sdate'],
								'edate'			=> $ms['edate'],
								'pro_id'		=> $pro['project_id']
							);
							
							// Create a milestone mark
							$ms = new MileStone($counter,lang('Milestone'),date('Y-m-d',$spro[edate]),$spro['title']);
							$ms->title->SetFont(FF_FONT1);
							$graph->Add($ms);
							
							// Create a vertical line to emphasize the milestone
							$vl = new GanttVLine(date('Y-m-d',$spro[edate]),'',"darkred");
							$vl->SetDayOffset(0.5); // Center the line in the day
							$graph->Add($vl);
							
							$counter++;
						}
					}
				}

#				$this->graph->legend_title = lang('color legend'); 
#				if(is_array($spro))
#				{
#					$this->graph->data = $spro;
#				}

#				$this->graph->num_lines_y = count($spro)+2;
				#$this->graph->render();

				$graph->Stroke(PHPGW_SERVER_ROOT . SEP . 'phpgwapi' . SEP . 'images' . SEP . 'draw_tmp.png');
				$graph->Stroke();
			}
		}
	}
?>
