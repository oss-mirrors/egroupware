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
	// $Source$

	class soprojects
	{
		var $db;
		var $grants;

		function soprojects()
		{
			$this->db		= $GLOBALS['phpgw']->db;
			$this->db2		= $this->db;
			$this->grants	= $GLOBALS['phpgw']->acl->get_grants('projects');
			$this->account	= $GLOBALS['phpgw_info']['user']['account_id'];
			$this->currency = $GLOBALS['phpgw_info']['user']['preferences']['common']['currency'];
			$this->year		= $GLOBALS['phpgw']->common->show_date(time(),'Y');
			$this->member	= $this->get_acl_projects();
		}

		function project_filter($type)
		{
			switch ($type)
			{
				case 'subs':			$s = ' and parent != 0'; break;
				case 'mains':			$s = ' and parent = 0'; break;
				default: return False;
            }
			return $s;
		}

		function bill_lang()
		{
			switch ($GLOBALS['phpgw_info']['user']['preferences']['projects']['bill'])
			{
				case 'wu':	$l = lang('per workunit'); break;
				case 'h':	$l = lang('per hour'); break;
				default:	$l = lang('per hour/workunit');
			}
			return $l;
		}

		function db2projects()
		{
			while ($this->db->next_record())
			{
				$projects[] = array
				(
					'project_id'	=> $this->db->f('id'),
					'parent'		=> $this->db->f('parent'),
					'number'		=> $this->db->f('num'),
					'access'		=> $this->db->f('access'),
					'cat'			=> $this->db->f('category'),
					'sdate'			=> $this->db->f('start_date'),
					'edate'			=> $this->db->f('end_date'),
					'coordinator'	=> $this->db->f('coordinator'),
					'customer'		=> $this->db->f('customer'),
					'status'		=> $this->db->f('status'),
					'descr'			=> $this->db->f('descr'),
					'title'			=> $this->db->f('title'),
					'budget'		=> $this->db->f('budget'),
					'ptime'			=> $this->db->f('time_planned'),
					'owner'			=> $this->db->f('owner'),
					'cdate'			=> $this->db->f('date_created'),
					'processor'		=> $this->db->f('processor'),
					'udate'			=> $this->db->f('entry_date'),
					'investment_nr'	=> $this->db->f('investment_nr'),
					'pcosts'		=> $this->db->f('pcosts'),
					'main'			=> $this->db->f('main'),
					'level'			=> $this->db->f('level'),
					'previous'		=> $this->db->f('previous')
				);
			}
			return $projects;
		}


		function read_projects($values)
		{
			$start	= intval($values['start']);
			$limit	= (isset($values['limit'])?$values['limit']:True);
			$filter	= (isset($values['filter'])?$values['filter']:'none');
			$sort	= (isset($values['sort'])?$values['sort']:'ASC');
			$order	= $values['order'];
			$status	= $values['status'];
			$type	= (isset($values['type'])?$values['type']:'mains');

			$cat_id	= intval($values['cat_id']);
			$main	= intval($values['main']);
			$parent	= intval($values['parent']);

			$query	= $this->db->db_addslashes($values['query']);

			if ($status)
			{
				$statussort = " AND status = '" . $status . "' ";
			}
			else
			{
				$statussort = " AND status != 'archive' ";
			}

			if ($order)
			{
				$ordermethod = " order by $order $sort";
			}
			else
			{
				$ordermethod = ' order by start_date asc';
			}

			if ($filter == 'none')
			{
				if ($this->isprojectadmin('pad') || $this->isbookkeeper('pbo'))
				{
					$filtermethod = " ( access != 'private' OR coordinator = " . $this->account . ' )';
				}
				else
				{
					$filtermethod = ' ( coordinator=' . $this->account;
					if (is_array($this->grants))
					{
						$grants = $this->grants;
						while (list($user) = each($grants))
						{
							$public_user_list[] = $user;
						}
						reset($public_user_list);
						$filtermethod .= " OR (access != 'private' AND coordinator in(" . implode(',',$public_user_list) . '))';
					}

					if (is_array($this->member))
					{
						$filtermethod .= " OR (access != 'private' AND id in(" . implode(',',$this->member) . '))';
					}
					$filtermethod .= ' )';
				}
			}
			elseif ($filter == 'yours')
			{
				$filtermethod = ' coordinator=' . $this->account;
			}
			elseif ($filter == 'public')
			{
				$filtermethod = " access = 'anonym' ";
			}
			else
			{
				$filtermethod = ' coordinator=' . $this->account . " AND access='private'";
			}

			if ($cat_id > 0)
			{
				$filtermethod .= ' AND category=' . $cat_id;
			}

			switch($type)
			{
				case 'all':
				case 'amains':
				case 'mains':		$parent_select = ' AND parent=0'; break;
				case 'asubs':
				case 'subs':		$parent_select = ' AND (parent=' . $parent . ' AND parent != 0)'; break;
				case 'mainandsubs':	$parent_select = ' AND main=' . $main; break;
			}

			if ($query)
			{
				$querymethod = " AND (title like '%$query%' OR num like '%$query%' OR descr like '%$query%') ";
			}

			$sql = "SELECT * from phpgw_p_projects WHERE $filtermethod $statussort $querymethod";

			$this->db2->query($sql . $parent_select,__LINE__,__FILE__);
			$total = $this->db2->num_rows();

			if ($limit)
			{
				$this->db->limit_query($sql . $parent_select . $ordermethod,$start,__LINE__,__FILE__);
			}
			else
			{
				$this->db->query($sql . $parent_select . $ordermethod,__LINE__,__FILE__);
			}

			$pro = $this->db2projects();

			if ($main == 0 && $type != 'mains' && $type != 'amains')
			{
				$num_pro = count($pro);
				for ($i=0;$i < $num_pro;$i++)
				{
					$sub_select = ' AND parent=' . $pro[$i]['project_id'] . ' AND level=' . ($pro[$i]['level']+1);

					$this->db->query($sql . $sub_select . $ordermethod,__LINE__,__FILE__);
					$total += $this->db->num_rows();
					$subpro = $this->db2projects();

					$num_subpro = count($subpro);
					if ($num_subpro != 0)
					{
						$newpro = array();
						for ($k = 0; $k <= $i; $k++)
						{
							$newpro[$k] = $pro[$k];
						}
						for ($k = 0; $k < $num_subpro; $k++)
						{
							$newpro[$k+$i+1] = $subpro[$k];
						}
						for ($k = $i+1; $k < $num_pro; $k++)
						{
							$newpro[$k+$num_subpro] = $pro[$k];
						}
						$pro = $newpro;
						$num_pro = count($pro);
					}
				}
			}

			$this->total_records = $total;
			return $pro;
		}

		function read_single_project($project_id)
		{
			$this->db->query('SELECT * from phpgw_p_projects WHERE id=' . $project_id,__LINE__,__FILE__);
	
			list($project) = $this->db2projects();
			return $project;
		}

		function select_project_list($values)
		{
			$pro = $this->read_projects(array
						(
							'limit'		=> False,
							'status'	=> $values['status'],
							'type'		=> (isset($values['type'])?$values['type']:'mains'),
							'main'		=> $values['main'],
						));

			if($values['self'])
			{
				for ($i=0;$i<count($pro);$i++)
				{
					if ($pro[$i]['project_id'] == $values['self'])
					{
						unset($pro[$i]);
					}
				}
			}

			while (is_array($pro) && list(,$p) = each($pro))
			{
				$s .= '<option value="' . $p['project_id'] . '"';
				if ($p['project_id'] == $values['selected'])
				{
					$s .= ' selected';
				}
				$s .= '>';

				for ($j=0;$j<$p['level'];$j++)
				{
					$s .= '&nbsp;.&nbsp;';
				}

				if ($p['title'])
				{
					$s .= $GLOBALS['phpgw']->strip_html($p['title']) . ' [ '
								. $GLOBALS['phpgw']->strip_html($p['number']) . ' ]';
				}
				else
				{
					$s .= $GLOBALS['phpgw']->strip_html($p['number']);
				}
				$s .= '</option>';
			}
			return $s;
		}

		function add_project($values, $book_activities, $bill_activities)
		{
			$values['descr']			= $this->db->db_addslashes($values['descr']);
			$values['title']			= $this->db->db_addslashes($values['title']);
			$values['number']			= $this->db->db_addslashes($values['number']);
			$values['investment_nr']	= $this->db->db_addslashes($values['investment_nr']);

			$values['budget']			= $values['budget'] + 0.0;
			$values['pcosts']			= $values['pcosts'] + 0.0;

			if ($values['parent'] && $values['parent'] != 0)
			{
				$values['main']		= intval($this->id2item(array('project_id' => $values['parent'],'item' => 'main')));
				$values['level']	= intval($this->id2item(array('project_id' => $values['parent'],'item' => 'level'))+1);
			}

			$table = 'phpgw_p_projects';
			$this->db->lock($table);

			$this->db->query('INSERT into phpgw_p_projects (owner,access,category,entry_date,start_date,end_date,coordinator,customer,status,'
							. 'descr,title,budget,num,parent,time_planned,date_created,processor,investment_nr,pcosts,main,level,previous) VALUES (' . $this->account
							. ",'" . (isset($values['access'])?$values['access']:'public') . "'," . intval($values['cat']) . ',' . time() . ',' . intval($values['sdate']) . ','
							. intval($values['edate']) . ',' . intval($values['coordinator']) . ',' . intval($values['customer']) . ",'" . $values['status']
							. "','" . $values['descr'] . "','" . $values['title'] . "'," . $values['budget'] . ",'" . $values['number'] . "',"
							. intval($values['parent']) . ',' . intval($values['ptime']) . ',' . time() . ',' . $this->account . ",'" . $values['investment_nr']
							. "'," . $values['pcosts'] . ',' . intval($values['main']) . ',' . intval($values['level']) . ',' . intval($values['previous']) . ')',__LINE__,__FILE__);

			$p_id = $this->db->get_last_insert_id($table,'id');
			$this->db->unlock();

			if ($p_id && ($p_id != 0))
			{
				if (!$values['parent'] || $values['parent'] == 0)
				{
					$this->db->query('UPDATE phpgw_p_projects SET main=' . $p_id . ' WHERE id=' . $p_id,__LINE__,__FILE__);
				}

				if (is_array($book_activities))
				{
					while($activ=each($book_activities))
					{
						$this->db->query('insert into phpgw_p_projectactivities (project_id,activity_id,billable) values (' . $p_id . ','
										. $activ[1] . ",'N')",__LINE__,__FILE__);
					}
				}

				if (is_array($bill_activities))
				{
					while($activ=each($bill_activities))
					{
						$this->db->query('insert into phpgw_p_projectactivities (project_id,activity_id,billable) values (' . $p_id . ','
										. $activ[1] . ",'Y')",__LINE__,__FILE__);
					}
				}

				if ($values['pcosts'] && $values['pcosts'] != 0)
				{
					$this->db->query('INSERT into phpgw_p_pcosts (project_id,month,pcosts) VALUES (' . $p_id . ',' . $values['monthdate'] . ','
									. $values['pcosts'] . ')',__LINE__,__FILE__);
				}
				return $p_id;
			}
			return False;
		}

		function subs($parent,&$subs,&$main)
		{
			if (!is_array($main))
			{
				$this->db->query('SELECT * from phpgw_p_projects WHERE main=' . $main,__LINE__,__FILE__);
				$main = $this->db2projects();
				//echo "main: "; _debug_array($main);
			}
			reset($main);
			for ($n = 0; $n < count($main); $n++)
			{
				$pro = $main[$n];
				if ($pro['parent'] == $parent)
				{
					//echo "Adding($pro[project_id])<br>";
					$subs[$pro['project_id']] = $pro;
					$this->subs($pro['project_id'],$pro,$main);
				}
			}
		}

		function reparent($values)
		{
			$id = $values['project_id'];
			$parent = $values['parent'];
			$old_parent = $values['old_parent'];
			$main = $old_parent ? intval($this->id2item(array('project_id' => $old_parent))) : $id;
			//echo "<p>reparent: $id/$main: $old_parent --> $parent</p>\n";

			$subs = array();
			$this->subs($id,$subs,$main);
         //echo "<p>subs($id) = "; _debug_array($subs);

			if (isset($subs[$parent]))
			{
				//echo "<p>new parent $parent is sub of $id</p>\n";
				$parent = $subs[$parent];
				$parent['old_parent'] = $parent['parent'];
				$parent['parent'] = intval($values['old_parent']);
				$this->reparent($parent);

				unset($parent['old_parent']);
				unset($parent['main']);

				$this->edit_project($parent);
				$this->reparent($values);
				return;
			}

			$new_main = $parent ? $this->id2item(array('project_id' => $parent)) : $id;
			$new_parent_level = $parent ? $this->id2item(array('project_id' => $parent,'item' => 'level')) : -1;
			$old_parent_level = $old_parent ? $this->id2item(array('project_id' => $old_parent,'item' => 'level')) : -1;
			$level_adj = $old_parent_level - $new_parent_level;
			reset($subs);
         //echo "new_main=$new_main,level_adj = $level_adj<br>";
			while (list($n) = each($subs))
			{
				$subs[$n]['main'] = $new_main;
				$subs[$n]['level'] -= $level_adj;
				//echo "<p>$n: id=".$subs[$n]['project_id']." set main to $new_main, subs[$n] = \n"; _debug_array($subs[$n]);
				$this->edit_project($subs[$n]);
			}
		}

		function edit_project($values, $book_activities = 0, $bill_activities = 0)
		{
			if (is_array($book_activities))
			{
				$this->db2->query('delete from phpgw_p_projectactivities where project_id=' . $values['project_id']
								. " and billable='N'",__LINE__,__FILE__);

				while($activ=each($book_activities))
				{
					$this->db->query('insert into phpgw_p_projectactivities (project_id, activity_id, billable) values (' . $values['project_id']
									. ',' . $activ[1] . ",'N')",__LINE__,__FILE__);
				}
			}

			if (is_array($bill_activities))
			{
				$this->db2->query('delete from phpgw_p_projectactivities where project_id=' . $values['project_id']
								. " and billable='Y'",__LINE__,__FILE__);

				while($activ=each($bill_activities))
				{
					$this->db->query('insert into phpgw_p_projectactivities (project_id, activity_id, billable) values (' . $values['project_id']
									. ',' . $activ[1] . ",'Y')",__LINE__,__FILE__);
				}
			}

			$values['descr']			= $this->db->db_addslashes($values['descr']);
			$values['title']			= $this->db->db_addslashes($values['title']);
			$values['number']			= $this->db->db_addslashes($values['number']);
			$values['investment_nr']	= $this->db->db_addslashes($values['investment_nr']);
			$values['project_id']		= intval($values['project_id']);
			$values['parent']			= intval($values['parent']);

			$values['budget']			= $values['budget'] + 0.0;
			$values['pcosts']			= $values['pcosts'] + 0.0;

			if (isset($values['old_parent']) && $values['old_parent'] != $values['parent'])
			{
				$this->reparent($values);
			}
			if (!isset($values['main']) || !isset($values['level']))
			{
				if ($values['parent'] > 0)
				{
					$values['main']		= intval($this->id2item(array('project_id' => $values['parent'],'item' => 'main')));
					$values['level']	= intval($this->id2item(array('project_id' => $values['parent'],'item' => 'level'))+1);
				}
			}

			$this->db->query("UPDATE phpgw_p_projects set access='" . (isset($values['access'])?$values['access']:'public') . "', category=" . intval($values['cat']) . ", entry_date="
							. time() . ", start_date=" . intval($values['sdate']) . ", end_date=" . intval($values['edate']) . ", coordinator="
							. intval($values['coordinator']) . ", customer=" . intval($values['customer']) . ", status='" . $values['status'] . "', descr='"
							. $values['descr'] . "', title='" . $values['title'] . "', budget=" . $values['budget'] . ", num='"
							. $values['number'] . "', time_planned=" . intval($values['ptime']) . ', processor=' . $this->account . ", investment_nr='"
							. $values['investment_nr'] . "', pcosts=" . $values['pcosts'] . ', parent=' . $values['parent']
							. ', level=' . intval($values['level']) . ', previous=' . intval($values['previous']) . ' where id=' . $values['project_id'],__LINE__,__FILE__);

			$this->db->query('SELECT max(month) FROM phpgw_p_pcosts where project_id=' . $values['project_id'],__LINE__,__FILE__);
			if($this->db->next_record())
			{
				$month = $this->db->f(0);
			}

			if($values['monthdate'] > intval($month))
			{
				$this->db->query('INSERT into phpgw_p_pcosts (project_id,month,pcosts) VALUES (' . $values['project_id'] . ',' . $values['monthdate'] . ','
									. $values['pcosts'] . ')',__LINE__,__FILE__);
			}
			else
			{
				$this->db->query('UPDATE phpgw_p_pcosts set pcosts=' . $values['pcosts'] . ' WHERE project_id=' . $values['project_id']
								. ' AND month=' . $values['monthdate'],__LINE__,__FILE__);
			}

			if ($values['status'] == 'archive')
			{
				$this->db->query("Update phpgw_p_projects set status='archive' WHERE parent=" . $values['project_id'],__LINE__,__FILE__);
			}
			
			if($values['oldstatus'] && $values['oldstatus'] == 'archive' && $values['status'] != 'archive')
			{
				$this->db->query("Update phpgw_p_projects set status='" . $values['status'] . "' WHERE parent=" . $values['project_id'],__LINE__,__FILE__);
			}

			if (isset($values['old_edate']) && $values['old_edate'] != $values['edate'])
			{
				$this->db->query('SELECT id,start_date,end_date from phpgw_p_projects where previous=' . $values['project_id'],__LINE__,__FILE__);

				while($this->db->next_record())
				{
					$following[] = array
					(
						'id'	=> $this->db->f('id'),
						'sdate'	=> $this->db->f('start_date'),
						'edate'	=> $this->db->f('end_date')
					);
				};

				if (is_array($following))
				{
					$diff = abs($values['edate']-$values['old_edate']);

					if ($values['old_edate'] > $values['edate'])
					{
						$op = 'sub';
					}
					else
					{
						$op = 'add';
					}

					while (list(,$fol) = each($following))
					{
						switch($op)
						{
							case 'add':
								$nsdate = $fol['sdate']+$diff;
								$nedate = $fol['edate']+$diff;
								break;
							case 'sub':
								$nsdate = $fol['sdate']-$diff;
								$nedate = $fol['edate']-$diff;
								break;
						}
						$this->db->query('UPDATE phpgw_p_projects set start_date=' . $nsdate . ', end_date=' . $nedate . ', entry_date=' . time()
										. ', processor=' . $this->account . ' WHERE id=' . $fol['id'],__LINE__,__FILE__);

						$this->db->query('SELECT s_id, edate from phpgw_p_mstones WHERE project_id=' . $fol['id'],__LINE__,__FILE__);

						while($this->db->next_record())
						{
							$stones[] = array
							(
								's_id'	=> $this->db->f('s_id'),
								'edate'	=> $this->db->f('edate')
							);
						};

						while(is_array($stones) && list(,$stone) = each($stones))
						{
							switch($op)
							{
								case 'add':
									$sedate = $stone['edate']+$diff;
									break;
								case 'sub':
									$sedate = $stone['edate']-$diff;
									break;
							}
							$this->db->query('UPDATE phpgw_p_mstones set edate=' . $sedate . ' WHERE s_id=' . $stone['s_id'],__LINE__,__FILE__);
						}
					}
				}
			}
		}

		function activities_list($project_id = '',$billable = False)
		{
			if ($billable)
			{
				$bill_filter = " AND billable='Y'";
			}
			else
			{
				$bill_filter = " AND billable='N'";
			}

			$this->db->query('SELECT phpgw_p_activities.id,num,descr,billperae,activity_id from phpgw_p_activities,phpgw_p_projectactivities '
							. 'WHERE phpgw_p_projectactivities.project_id=' . $project_id . ' AND phpgw_p_activities.id='
							. 'phpgw_p_projectactivities.activity_id' . $bill_filter,__LINE__,__FILE__);

			while ($this->db->next_record())
			{
				$act[] = array
				(
					'num'		=> $this->db->f('num'),
					'descr'		=> $this->db->f('descr'),
					'billperae'	=> $this->db->f('billperae')
				);
			}
			return $act;
		}

		function select_activities_list($project_id = '',$billable = False)
		{
			if ($billable)
			{
				$bill_filter = " AND billable='Y'";
			}
			else
			{
				$bill_filter = " AND billable='N'";
			}

			$this->db2->query('SELECT activity_id from phpgw_p_projectactivities WHERE project_id=' . intval($project_id) . $bill_filter,__LINE__,__FILE__);
			while ($this->db2->next_record())
			{
				$selected[] = array('activity_id' => $this->db2->f('activity_id'));
			}

			$this->db->query('SELECT id,num,descr,billperae FROM phpgw_p_activities ORDER BY descr asc');
			while ($this->db->next_record())
			{
				$activities_list .= '<option value="' . $this->db->f('id') . '"';
				for ($i=0;$i<count($selected);$i++)
				{
					if($selected[$i]['activity_id'] == $this->db->f('id'))
					{
						$activities_list .= ' selected';
					}
				}
				$activities_list .= '>' . $GLOBALS['phpgw']->strip_html($this->db->f('descr')) . ' ['
										. $GLOBALS['phpgw']->strip_html($this->db->f('num')) . ']';
				if($billable)
				{
					$activities_list .= ' ' . $this->currency . ' ' . $this->db->f('billperae') . ' ' . $this->bill_lang();
				}

				$activities_list .= '</option>' . "\n";
			}
			return $activities_list;
		}

		function select_pro_activities($project_id = '', $pro_parent, $billable = False)
		{
			if ($billable)
			{
				$bill_filter = " AND billable='Y'";
			}
			else
			{
				$bill_filter = " AND billable='N'";
			}

			$this->db2->query('SELECT activity_id from phpgw_p_projectactivities WHERE project_id=' . intval($project_id) . $bill_filter,__LINE__,__FILE__);
			while ($this->db2->next_record())
			{
				$selected[] = array('activity_id' => $this->db2->f('activity_id'));
			}

			$this->db->query('SELECT a.id, a.num, a.descr, a.billperae, pa.activity_id FROM phpgw_p_activities as a, phpgw_p_projectactivities as pa'
							. ' WHERE pa.project_id=' . intval($pro_parent) . $bill_filter . ' AND pa.activity_id=a.id ORDER BY a.descr asc');
			while ($this->db->next_record())
			{
				$activities_list .= '<option value="' . $this->db->f('id') . '"';
				for ($i=0;$i<count($selected);$i++)
				{
					if($selected[$i]['activity_id'] == $this->db->f('id'))
					{
						$activities_list .= ' selected';
					}
				}

				if (! is_array($selected))
				{
					$activities_list .= ' selected';
				}

				$activities_list .= '>' . $GLOBALS['phpgw']->strip_html($this->db->f('descr')) . ' ['
										. $GLOBALS['phpgw']->strip_html($this->db->f('num')) . ']';

				if($billable)
				{
					$activities_list .= ' ' . $this->currency . ' ' . $this->db->f('billperae') . ' ' . $this->bill_lang();
				}

				$activities_list .= '</option>' . "\n";
			}
			return $activities_list;
		}

		function select_hours_activities($project_id, $activity = '')
		{
			$this->db->query('SELECT activity_id,num, descr,billperae,billable FROM phpgw_p_projectactivities,phpgw_p_activities WHERE project_id ='
							. intval($project_id) . ' AND phpgw_p_projectactivities.activity_id=phpgw_p_activities.id order by descr asc',__LINE__,__FILE__);

			while ($this->db->next_record())
			{
				$hours_act .= '<option value="' . $this->db->f('activity_id') . '"';
				if($this->db->f('activity_id') == intval($activity))
				{
					$hours_act .= ' selected';
				}
				$hours_act .= '>' . $GLOBALS['phpgw']->strip_html($this->db->f('descr')) . ' ['
									. $GLOBALS['phpgw']->strip_html($this->db->f('num')) . ']';

				if($this->db->f('billable') == 'Y')
				{
					$hours_act .= ' ' . $this->currency . ' ' . $this->db->f('billperae') . ' ' . $this->bill_lang();
				}
				$hours_act .= '</option>' . "\n";
			}
			return $hours_act;
		}

		function return_value($action,$pro_id)
		{
			$pro_id = intval($pro_id);
			if ($action == 'act')
			{			
				$this->db->query('SELECT num,descr from phpgw_p_activities where id=' . $pro_id,__LINE__,__FILE__);
				if ($this->db->next_record())
				{
					$bla = $GLOBALS['phpgw']->strip_html($this->db->f('descr')) . ' [' . $GLOBALS['phpgw']->strip_html($this->db->f('num')) . ']';
				}
			}
			elseif ($action == 'co')
			{
				$this->db->query('SELECT coordinator from phpgw_p_projects where id=' . $pro_id,__LINE__,__FILE__);
				if ($this->db->next_record())
				{
					$bla = $this->db->f('coordinator');
				}
			}
			else
			{
				switch ($action)
				{
					case 'pro':			$column = 'num,title'; break;
					case 'edate':		$column = 'end_date'; break;
					case 'sdate':		$column = 'start_date'; break;
					case 'ptime':		$column = 'time_planned'; break;
					case 'invest':		$column = 'investment_nr'; break;
					case 'budget':		$column = 'budget'; break;
					case 'previous':	$column = 'previous'; break;
				}

				$this->db->query('SELECT ' . $column . ' from phpgw_p_projects where id=' . $pro_id,__LINE__,__FILE__);
				if ($this->db->next_record())
				{
					if ($action == 'pro')
					{
						$bla = $GLOBALS['phpgw']->strip_html($this->db->f('title')) . ' ['
								. $GLOBALS['phpgw']->strip_html($this->db->f('num')) . ']';
					}
					else
					{
						$bla = $GLOBALS['phpgw']->strip_html($this->db->f($column));
					}
				}
			}
			return $bla;
		}

		function exists($action, $check = 'number', $num = '', $pa_id = '')
		{
			switch ($action)
			{
				case 'act'	: $p_table = ' phpgw_p_activities '; break;
				default		: $p_table = ' phpgw_p_projects'; break;
			}

			if ($check == 'number')
			{
				if ($pa_id && ($pa_id != 0))
				{
					$editexists = ' and id !=' . $pa_id;
				}

				$this->db->query("select count(*) from $p_table where num='$num'" .  $editexists,__LINE__,__FILE__);
			}

			if ($check == 'par')
			{
				$this->db->query('select count(*) from phpgw_p_projects where parent=' . $pa_id,__LINE__,__FILE__);
			}
			$this->db->next_record();

			if ($this->db->f(0))
			{
				return True;
			}
			else
			{
				return False;
			}
		}

		function return_admins($action, $type = 'all')
		{
			if ($action == 'pad')
			{
				switch ($type)
				{
					case 'all':	$filter = " type='aa' or type='ag'"; break;
					case 'aa':	$filter = " type='aa'"; break;
					case 'ag':	$filter = " type='ag'"; break;
				}
			}
			else
			{
				switch ($type)
				{
					case 'all':	$filter = " type='ba' or type='bg'"; break;
					case 'aa':	$filter = " type='ba'"; break;
					case 'ag':	$filter = " type='bg'"; break;
				}
			}

			$sql = 'select account_id,type from phpgw_p_projectmembers WHERE ' . $filter;
			$this->db->query($sql);
			$this->total_records = $this->db->num_rows();
			while ($this->db->next_record())
			{
				$admins[] = array('account_id' => $this->db->f('account_id'),
										'type' => $this->db->f('type'));
			}
			return $admins;
		}

		function isprojectadmin($action)
		{
			$admin_groups = $GLOBALS['phpgw']->accounts->membership($this->account);
			$admins = $this->return_admins($action);

			for ($i=0;$i<count($admins);$i++)
			{
				if ($admins[$i]['type']=='aa')
				{
					if ($admins[$i]['account_id'] == $this->account)
					return True;
				}
				elseif ($admins[$i]['type']=='ag')
				{
					if (is_array($admin_groups))
					{
						for ($j=0;$j<count($admin_groups);$j++)
						{
							if ($admin_groups[$j]['account_id'] == $admins[$i]['account_id'])
							return True;
						}
					}
				}
				else
				{
					return False;
				}
			}
		}

		function isbookkeeper($action)
		{
			$admin_groups = $GLOBALS['phpgw']->accounts->membership($this->account);
			$admins = $this->return_admins($action);

			for ($i=0;$i<count($admins);$i++)
			{
				if ($admins[$i]['type']=='ba')
				{
					if ($admins[$i]['account_id'] == $this->account)
					return True;
				}
				elseif ($admins[$i]['type']=='bg')
				{
					if (is_array($admin_groups))
					{
						for ($j=0;$j<count($admin_groups);$j++)
						{
							if ($admin_groups[$j]['account_id'] == $admins[$i]['account_id'])
							return True;
						}
					}
				}
				else
				{
					return False;
				}
			}
		}

		function edit_admins($action, $users = '', $groups = '')
		{
			if ($action == 'pad')
			{
				$ag = 'ag';
				$aa = 'aa';
			}
			else
			{
				$ag = 'bg';
				$aa = 'ba';
			}

			$this->db->query("DELETE from phpgw_p_projectmembers WHERE type='" . $aa . "' OR type='" . $ag . "'",__LINE__,__FILE__);

			if (is_array($users))
			{
				while($activ=each($users))
				{
					$this->db->query('insert into phpgw_p_projectmembers (project_id, account_id,type) values (0,' . $activ[1] . ",'"
									. $aa . "')",__LINE__,__FILE__);
				}
			}

			if (is_array($groups))
			{
				while($activ=each($groups))
				{
					$this->db->query('insert into phpgw_p_projectmembers (project_id, account_id,type) values (0,' . $activ[1] . ",'"
									. $ag . "')",__LINE__,__FILE__);
				}
			}
		}

// returns project-,invoice- and delivery-ID

		function add_leading_zero($num)  
		{
/*			if ($id_type == "hex")
			{
				$num = hexdec($num);
				$num++;
				$num = dechex($num);
			}
			else
			{
				$num++;
			} */

			$num++;

			if (strlen($num) == 4)
				$return = $num;
			if (strlen($num) == 3)
				$return = "0$num";
			if (strlen($num) == 2)
				$return = "00$num";
			if (strlen($num) == 1)
				$return = "000$num";
			if (strlen($num) == 0)
				$return = "0001";

			return strtoupper($return);
		}

		function create_projectid()
		{
			$prefix = 'P-' . $this->year . '-';

			$this->db->query("select max(num) from phpgw_p_projects where num like ('$prefix%') and parent=0");
			$this->db->next_record();
			$max = $this->add_leading_zero(substr($this->db->f(0),-4));

			return $prefix . $max;
		}

		function create_jobid($pro_parent)
		{
			$this->db->query('select num from phpgw_p_projects where id=' . $pro_parent);
			$this->db->next_record();
			$prefix = $this->db->f('num') . '/';

			$this->db->query("select max(num) from phpgw_p_projects where num like ('$prefix%')");
			$this->db->next_record();
			$max = $this->add_leading_zero(substr($this->db->f(0),-4));

			return $prefix . $max;
		}

		function create_activityid()
		{
			$prefix = 'A-' . $this->year . '-';

			$this->db->query("select max(num) from phpgw_p_activities where num like ('$prefix%')");
			$this->db->next_record();
			$max = $this->add_leading_zero(substr($this->db->f(0),-4));

			return $prefix . $max;
		}

		function create_deliveryid()
		{
			$prefix = 'D-' . $this->year . '-';
			$this->db->query("select max(num) from phpgw_p_delivery where num like ('$prefix%')");
			$this->db->next_record();
			$max = $this->add_leading_zero(substr($this->db->f(0),-4));

			return $prefix . $max;
		}

		function create_invoiceid()
		{
			$prefix = 'I-' . $this->year . '-';
			$this->db->query("select max(num) from phpgw_p_invoice where num like ('$prefix%')");
			$this->db->next_record();
			$max = $this->add_leading_zero(substr($this->db->f(0),-4));

			return $prefix . $max;
		}

		function read_activities($start, $limit = True, $query = '', $sort = '', $order = '', $cat_id = '')
		{
			if ($order)
			{
				$ordermethod = " order by $order $sort";
			}
			else
			{
				$ordermethod = " order by num asc";
			}

			if ($query)
			{
				$filtermethod = " where (descr like '%$query%' or num like '%$query%' or minperae like '%$query%' or billperae like '%$query%')";

				if ($cat_id)
				{
					$filtermethod .= ' AND category=' . $cat_id;
				}
			}
			else
			{
				if ($cat_id)
				{
					$filtermethod = ' WHERE category=' . $cat_id;
				}
			}

			$sql = 'select * from phpgw_p_activities' . $filtermethod;
			$this->db2->query($sql,__LINE__,__FILE__);
			$this->total_records = $this->db2->num_rows();

			if ($limit)
			{
				$this->db->limit_query($sql . $ordermethod,$start,__LINE__,__FILE__);
			}
			else
			{
				$this->db->query($sql . $ordermethod,__LINE__,__FILE__);
			}

			$i = 0;
			while ($this->db->next_record())
			{
				$act[$i]['activity_id']	= $this->db->f('id');
				$act[$i]['cat']			= $this->db->f('category');
				$act[$i]['number']		= $this->db->f('num');
				$act[$i]['descr']		= $this->db->f('descr');
				$act[$i]['remarkreq']	= $this->db->f('remarkreq');
				$act[$i]['billperae']	= $this->db->f('billperae');
				$act[$i]['minperae']	= $this->db->f('minperae');
				$i++;
			}
			return $act;
		}

		function read_single_activity($activity_id)
		{
			$this->db->query('SELECT * from phpgw_p_activities WHERE id=' . intval($activity_id),__LINE__,__FILE__);
	
			if ($this->db->next_record())
			{
				$act['activity_id']	= $this->db->f('id');
				$act['cat']			= $this->db->f('category');
				$act['number']		= $this->db->f('num');
				$act['descr']		= $this->db->f('descr');
				$act['remarkreq']	= $this->db->f('remarkreq');
				$act['billperae']	= $this->db->f('billperae');
				$act['minperae']	= $this->db->f('minperae');
				return $act;
			}
		}

		function add_activity($values)
		{
			$values['number']		= $this->db->db_addslashes($values['number']);
			$values['descr'] 		= $this->db->db_addslashes($values['descr']);
			$values['billperae']	= $values['billperae'] + 0.0;

			$this->db->query("insert into phpgw_p_activities (num,category,descr,remarkreq,billperae,minperae) values ('"
							. $values['number'] . "'," . intval($values['cat']) . ",'" . $values['descr'] . "','" . $values['remarkreq'] . "',"
							. $values['billperae'] . ','  . intval($values['minperae']) . ')',__LINE__,__FILE__);
		}

		function edit_activity($values)
		{
			$values['number']		= $this->db->db_addslashes($values['number']);
			$values['descr']		= $this->db->db_addslashes($values['descr']);
			$values['billperae']	= $values['billperae'] + 0.0;

			$this->db->query("update phpgw_p_activities set num='" . $values['number'] . "', category=" . intval($values['cat'])
							. ",remarkreq='" . $values['remarkreq'] . "',descr='" . $values['descr'] . "',billperae="
							. $values['billperae'] . ',minperae=' . intval($values['minperae']) . ' where id=' . intval($values['activity_id']),__LINE__,__FILE__);
		}

		function delete_pa($action, $pa_id, $subs = False)
		{
			$pa_id = intval($pa_id);

			switch ($action)
			{
				case 'mains': $p_table = ' phpgw_p_projects'; break;
				case 'subs'	: $p_table = ' phpgw_p_projects'; break;
				case 'act'	: $p_table = ' phpgw_p_activities '; break;
			}

			if ($subs)
			{
				$subdelete = ' OR parent =' . $pa_id;
			}

			$this->db->query("DELETE from $p_table where id=" . $pa_id . $subdelete,__LINE__,__FILE__);

			if ($action == 'act')
			{
				$this->db->query('DELETE from phpgw_p_projectactivities where activity_id=' . $pa_id,__LINE__,__FILE__); 
			}

			if ($action == 'mains' || $action == 'subs')
			{
				if ($subs)
				{
					$subdelete = ' or pro_parent=' . $pa_id;
				}

				$this->db->query('DELETE from phpgw_p_hours where project_id=' . $pa_id . $subdelete,__LINE__,__FILE__); 

				$this->db->query('select id from phpgw_p_delivery where project_id=' . $pa_id,__LINE__,__FILE__);

				while ($this->db->next_record())
				{
					$del[] = array
					(
						'id'	=> $this->db->f('id')
					);
				}

				if (is_array($del))
				{
					for ($i=0;$i<=count($del);$i++)
					{
						$this->db->query('Delete from phpgw_p_deliverypos where delivery_id=' . intval($del[$i]['id']),__LINE__,__FILE__);
					}
					$this->db->query('DELETE from phpgw_p_delivery where project_id=' . $pa_id,__LINE__,__FILE__);
				}

				$this->db->query('select id from phpgw_p_invoice where project_id=' . $pa_id,__LINE__,__FILE__);

				while ($this->db->next_record())
				{
					$inv[] = array
					(
						'id'	=> $this->db->f('id')
					);
				}

				if (is_array($inv))
				{
					for ($i=0;$i<=count($inv);$i++)
					{
						$this->db->query('Delete from phpgw_p_invoicepos where invoice_id=' . intval($inv[$i]['id']),__LINE__,__FILE__);
					}
					$this->db->query('DELETE from phpgw_p_invoice where project_id=' . $pa_id,__LINE__,__FILE__);
				}
			}
		}

		function delete_account_project_data($account_id)
		{
			if ($account_id && $account_id > 0)
			{
				$this->db->query('delete from phpgw_p_hours where employee=' . intval($account_id),__LINE__,__FILE__);
				$this->db->query('select id from phpgw_p_projects where coordinator=' . intval($account_id),__LINE__,__FILE__);

				while ($this->db->next_record())
				{
					$drop_list[] = $this->db->f('id');
				}

				if (is_array($drop_list))
				{
					reset($drop_list);
//					_debug_array($drop_list);
//					exit;

					$subdelete = ' OR parent in (' . implode(',',$drop_list) . ')';

					$this->db->query('DELETE from phpgw_p_projects where id in (' . implode(',',$drop_list) . ')'
									. $subdelete,__LINE__,__FILE__);

					$this->db->query('select id from phpgw_p_delivery where project_id in (' . implode(',',$drop_list) . ')',__LINE__,__FILE__);

					while ($this->db->next_record())
					{
						$del[] = array
						(
							'id'	=> $this->db->f('id')
						);
					}

					if (is_array($del))
					{
						for ($i=0;$i<=count($del);$i++)
						{
							$this->db->query('Delete from phpgw_p_deliverypos where delivery_id=' . intval($del[$i]['id']),__LINE__,__FILE__);
						}

						$this->db->query('DELETE from phpgw_p_delivery where project_id in (' . implode(',',$drop_list) . ')',__LINE__,__FILE__);
					}


					$this->db->query('select id from phpgw_p_invoice where project_id in (' . implode(',',$drop_list) . ')',__LINE__,__FILE__);

					while ($this->db->next_record())
					{
						$inv[] = array
						(
							'id'	=> $this->db->f('id')
						);
					}

					if (is_array($inv))
					{
						for ($i=0;$i<=count($inv);$i++)
						{
							$this->db->query('Delete from phpgw_p_invoicepos where invoice_id=' . intval($inv[$i]['id']),__LINE__,__FILE__);
						}

						$this->db->query('DELETE from phpgw_p_invoice where project_id in (' . implode(',',$drop_list) . ')',__LINE__,__FILE__);
					}
				}
			}
		}

		function change_owner($old, $new)
		{
			$old = intval($old);
			$new = intval($new);

			$this->db->query('UPDATE phpgw_p_projects set coordinator=' . $new . ' where coordinator=' . $old,__LINE__,__FILE__);
			$this->db->query('UPDATE phpgw_p_hours set employee=' . $new . ' where employee=' . $old,__LINE__,__FILE__);
			$this->db->query('UPDATE phpgw_p_projectmembers set account_id=' . $new . ' where (account_id=' . $old
							. " AND type='aa')",__LINE__,__FILE__);
		}

		function sum_budget($action = 'budget')
		{
			$this->db->query('SELECT SUM(' . $action . ") as sumvalue from phpgw_p_projects where ( parent=0 AND status!='archive' )",__LINE__,__FILE__);
			if ($this->db->next_record())
			{
				return $this->db->f('sumvalue');
			}
		}

		function list_pcosts($project_id)
		{
			$this->db->query('SELECT month,pcosts from phpgw_p_pcosts where project_id=' . intval($project_id) . ' order by month DESC',__LINE__,__FILE__);

			while ($this->db->next_record())
			{
				$costs[] = array
				(
					'month'		=> $this->db->f('month'),
					'pcosts'	=> $this->db->f('pcosts')
				);
			}
			return $costs;
		}

		function get_planned_value($option)
		{
			$action		= (isset($option['action'])?$option['action']:'main');
			$project_id	= (isset($option['project_id'])?$option['project_id']:0);
			$parent_id	= (isset($option['parent_id'])?$option['parent_id']:0);

			$project_id = intval($project_id);
			$parent_id = intval($parent_id);

			switch($action)
			{
				case 'tmain':
				case 'bmain':	$filter = 'main=' . $parent_id . ' and id !=' . $parent_id; break;
				case 'tparent':
				case 'bparent':	$filter = 'parent=' . $parent_id; break;
			}

			switch($action)
			{
				case 'bmain':
				case 'bparent':	$column = 'budget'; break;
				case 'tmain':
				case 'tparent':	$column = 'time_planned'; break;
			}

			if($project_id > 0)
			{
				$editfilter = ' and id !=' . $project_id;
			}

			$this->db->query('SELECT SUM(' . $column . ') as sumvalue from phpgw_p_projects where (' . $filter . $editfilter . ')',__LINE__,__FILE__);
			if ($this->db->next_record())
			{
				return $this->db->f('sumvalue');
			}
		}

		function id2item($data)
		{
			if(is_array($data))
			{
				$project_id	= $data['project_id'];
				$item		= (isset($data['item'])?$data['item']:'main');
			}

			$this->db->query("SELECT $item FROM phpgw_p_projects WHERE id=" . $project_id,__LINE__,__FILE__);
			$this->db->next_record();

			if ($this->db->f($item))
			{
				return $this->db->f(0);
			}
		}

		function get_mstones($project_id = '')
		{
			$this->db->query("SELECT * FROM phpgw_p_mstones WHERE project_id=" . intval($project_id),__LINE__,__FILE__);

			while($this->db->next_record())
			{
				$stones[] = array
				(
					's_id'	=> $this->db->f('s_id'),
					'title'	=> $this->db->f('title'),
					'edate'	=> $this->db->f('edate')
				);
			}
			return $stones;
		}

		function get_single_mstone($s_id = '')
		{
			$this->db->query("SELECT * FROM phpgw_p_mstones WHERE s_id=" . intval($s_id),__LINE__,__FILE__);

			if($this->db->next_record())
			{
				$stone = array
				(
					's_id'	=> $this->db->f('s_id'),
					'title'	=> $this->db->f('title'),
					'edate'	=> $this->db->f('edate')
				);
			}
			return $stone;
		}

		function add_mstone($values)
		{
			$this->db->query('INSERT into phpgw_p_mstones (project_id,title,edate) VALUES (' . intval($values['project_id']) . ",'"
							. $this->db->db_addslashes($values['title']) . "'," . intval($values['edate']) . ')',__LINE__,__FILE__);
		}

		function edit_mstone($values)
		{
			$this->db->query('UPDATE phpgw_p_mstones set edate=' . intval($values['edate']) . ", title='" . $this->db->db_addslashes($values['title']) . "' "
							. 'WHERE s_id=' . intval($values['s_id']),__LINE__,__FILE__);
		}

		function delete_mstone($s_id = '')
		{
			$this->db->query('DELETE from phpgw_p_mstones where s_id=' . intval($s_id),__LINE__,__FILE__);
		}

		function delete_acl($project_id)
		{
			$this->db->query("DELETE from phpgw_acl where acl_appname='projects' AND acl_location=" . $project_id
							. ' AND acl_rights=7',__LINE__,__FILE__);
		}

		function get_acl_projects()
		{
			$this->db->query("SELECT acl_location from phpgw_acl where acl_appname = 'projects' and acl_rights=7 and acl_account="
								. $this->account,__LINE__,__FILE__);

			while($this->db->next_record())
			{
				$projects[] = $this->db->f(0);
			}
			return $projects;
		}

		function member($project_id)
		{
			$this->db->query("SELECT acl_account from phpgw_acl where acl_appname = 'projects' and acl_rights=7 and acl_location="
								. intval($project_id),__LINE__,__FILE__);

			while($this->db->next_record())
			{
				$members[] = $this->db->f(0);
			}

			if (is_array($members) && in_array($this->account,$members))
			{
				return True;
			}
			return False;
		}
	}
?>
