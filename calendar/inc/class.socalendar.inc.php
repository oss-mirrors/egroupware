<?php
	/**************************************************************************\
	* eGroupWare - Calendar                                                    *
	* http://www.eGroupWare.org                                                *
	* Maintained and further developed by RalfBecker@outdoor-training.de       *
	* Based on Webcalendar by Craig Knudsen <cknudsen@radix.net>               *
	*          http://www.radix.net/~cknudsen                                  *
	* Originaly modified by Mark Peters <skeeter@phpgroupware.org>             *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	
	/* $Id$ */

	class socalendar
	{
//		var $debug = True;
		var $debug = False;
		var $cal;
		var $db;
		var $owner;
		var $g_owner;
		var $is_group = False;
		var $datetime;
		var $filter;
		var $cat_id;

		function socalendar($param=False)
		{
			$this->db = $GLOBALS['phpgw']->db;
			if(!is_object($GLOBALS['phpgw']->datetime))
			{
				$GLOBALS['phpgw']->datetime = createobject('phpgwapi.datetime');
			}

			$this->owner = (!isset($param['owner']) || $param['owner'] == 0?$GLOBALS['phpgw_info']['user']['account_id']:$param['owner']);
			$this->filter = (isset($param['filter']) && $param['filter'] != ''?$param['filter']:$this->filter);
			$this->cat_id = (isset($param['category']) && $param['category'] != ''?$param['category']:$this->cat_id);
			if(isset($param['g_owner']) && is_array($param['g_owner']))
			{
				$this->is_group = True;
				$this->g_owner = $param['g_owner'];
			}
			if($this->debug)
			{
				echo '<!-- SO Filter : '.$this->filter.' -->'."\n";
				echo '<!-- SO cat_id : '.$this->cat_id.' -->'."\n";
			}
			$this->cal = CreateObject('calendar.socalendar_');
			$this->db = &$this->db;

			foreach($this->cal->all_tables as $name => $table)
			{
				$this->$name = $table;
			}
			$this->open_box($this->owner);
		}

		function open_box($owner)
		{
			$this->cal->open('INBOX',(int)$owner);
		}

		function maketime($time)
		{
			return mktime($time['hour'],$time['min'],$time['sec'],$time['month'],$time['mday'],$time['year']);
		}

		function read_entry($id)
		{
			return $this->cal->fetch_event($id);
		}

		function cat_filter($cat_id)
		{
			$extra = '';
			if ($cat_id)
			{
				if (!is_array($cat_ids) && !@$GLOBALS['phpgw_info']['user']['preferences']['common']['cats_no_subs'])
				{
					if (!is_object($GLOBALS['phpgw']->categories))
					{
						$GLOBALS['phpgw']->categories = CreateObject('phpgwapi.categories');
					}
					$cats = $GLOBALS['phpgw']->categories->return_all_children($cat_id);
				}
				else
				{
					$cats = is_array($cat_id) ? $cat_id : array($cat_id);
				}
				array_walk($cats,create_function('&$val,$key','$val = (int) $val;'));

				$extra .= "($this->table.cal_category".(count($cats) > 1 ? ' IN ('.implode(',',$cats).')' : '='.(int)$cat_id);
				foreach($cats as $cat)
				{
					$extra .= " OR $this->table.cal_category LIKE '$cat,%' OR $this->table.cal_category LIKE '%,$cat,%' OR $this->table.cal_category LIKE '%,$cat'";
				}
				$extra .= ') ';
			}
			return $extra;
		}
			
		function list_events($startYear,$startMonth,$startDay,$endYear=0,$endMonth=0,$endDay=0,$owner_id=0)
		{
			$extra = '';
			$extra .= strpos($this->filter,'private') ? "AND $this->table.cal_public=0 " : '';

			if ($this->cat_id)
			{
				$extra .= ' AND '.$this->cat_filter($this->cat_id);
			}
			if($owner_id)
			{
				return $this->cal->list_events($startYear,$startMonth,$startDay,$endYear,$endMonth,$endDay,$extra,$GLOBALS['phpgw']->datetime->tz_offset,$owner_id);
			}
			else
			{
				return $this->cal->list_events($startYear,$startMonth,$startDay,$endYear,$endMonth,$endDay,$extra,$GLOBALS['phpgw']->datetime->tz_offset);
			}
		}

		/**
		 * Returns the id's of all repeating events started after s{year,month,day} AND still running at e{year,month,day}
		 *
		 * The startdate of an repeating events is the regular event-startdate.
		 * Events are "still running" if no recur-enddate is set or its after e{year,month,day}
		 */
		function list_repeated_events($syear,$smonth,$sday,$eyear,$emonth,$eday,$owner_id=0)
		{
			if(!$owner_id)
			{
				$owner_id = $this->is_group ? $this->g_owner : $this->owner;
			}
			if($GLOBALS['phpgw_info']['server']['calendar_type'] != 'sql' ||
				!count($owner_id))	// happens with empty groups
			{
				return Array();
			}
			$starttime = mktime(0,0,0,$smonth,$sday,$syear) - $GLOBALS['phpgw']->datetime->tz_offset;
			$endtime = mktime(23,59,59,$emonth,$eday,$eyear) - $GLOBALS['phpgw']->datetime->tz_offset;

			$sql = "AND $this->table.cal_type='M' AND $this->user_table.cal_user_id IN (".
				(is_array($owner_id) ? implode(',',$owner_id) : $owner_id).')';
/* why ???
			$member_groups = $GLOBALS['phpgw']->accounts->membership($this->user);
			@reset($member_groups);
			while(list($key,$group_info) = each($member_groups))
			{
				$member[] = $group_info['account_id'];
			}
			@reset($member);
			$sql .= ','.implode(',',$member).') ';
			$sql .= "AND ($this->table.cal_starttime <= '.$starttime.') ';
			$sql .= "AND ((($this->recur_table.recur_enddate >= $starttime) AND ($this->recur_table.recur_enddate <= $endtime)) OR ($this->recur_table.recur_enddate=0))) "
*/
			$sql .= " AND ($this->recur_table.recur_enddate >= $starttime OR $this->recur_table.recur_enddate=0) "
				. (strpos($this->filter,'private')? "AND $this->table.cal_public=0 " : '')
				. ($this->cat_id ? 'AND '.$this->cat_filter($this->cat_id) : '')
				. "ORDER BY $this->table.cal_starttime ASC, $this->table.cal_endtime ASC, $this->table.cal_priority ASC";

			if($this->debug)
			{
				echo '<!-- SO list_repeated_events : SQL : '.$sql.' -->'."\n";
			}

			return $this->get_event_ids(True,$sql);
		}

		function list_events_keyword($keywords,$members='')
		{
			if (!$members)
			{
				$members[] = $this->owner;
			}
			array_walk($members,create_function('&$val,$key','$val = (int) $val;'));

			$sql = "AND ($this->user_table.cal_user_id IN (".implode(',',$members).')) AND '.
				"($this->user_table.cal_user_id=" . (int) $this->owner . " OR $this->table.cal_public=1) AND (";

			$words = split(' ',$keywords);
			foreach($words as $i => $word)
			{
				$sql .= $i > 0 ? ' OR ' : '';
				$word = $GLOBALS['phpgw']->db->quote('%'.$word.'%');
				$sql .= "(UPPER($this->table.cal_title) LIKE UPPER($word) OR ".
					"UPPER($this->table.cal_description) LIKE UPPER($word) OR ".
					"UPPER($this->table.cal_location) LIKE UPPER($word) OR ".
					"UPPER($this->extra_table.cal_extra_value) LIKE UPPER($word))";
			}
			$sql .= ') ';

			$sql .= strpos($this->filter,'private') ? "AND $this->table.cal_public=0 " : '';
			$sql .= $this->cat_id ? 'AND '.$this->cat_filter($this->cat_id) : '';
			$sql .= " ORDER BY $this->table.cal_starttime DESC, $this->table.cal_endtime DESC, $this->table.cal_priority ASC";

			return $this->get_event_ids(False,$sql,True);
		}

		function read_from_store($startYear,$startMonth,$startDay,$endYear='',$endMonth='',$endDay='')
		{
			$events = $this->list_events($startYear,$startMonth,$startDay,$endYear,$endMonth,$endDay);
			$events_cached = Array();
			for($i=0;$i<count($events);$i++)
			{
				$events_cached[] = $this->read_entry($events[$i]);
			}
			return $events_cached;
		}

		function get_event_ids($search_repeats=False, $sql='',$search_extra=False)
		{
			return $this->cal->get_event_ids($search_repeats,$sql,$search_extra);
		}

		function find_uid($uid)
		{
			$sql = " AND ($this->table.cal_uid=".(int)$uid.' )';

			$found = $this->cal->get_event_ids(False,$sql);
			if(!$found)
			{
				$found = $this->cal->get_event_ids(True,$sql);
			}
			if(is_array($found))
			{
				return $found[0];
			}
			else
			{
				return False;
			}
		}

		function add_entry(&$event)
		{
			return $this->cal->save_event($event);
		}

		function save_alarm($cal_id,$alarm,$id=0)
		{
			return $this->cal->save_alarm($cal_id,$alarm,$id);
		}

		function delete_alarm($id)
		{
			return $this->cal->delete_alarm($id);
		}

		function delete_alarms($cal_id)
		{
			return $this->cal->delete_alarms($cal_id);
		}

		function delete_entry($id)
		{
			return $this->cal->delete_event($id);
		}

		function expunge()
		{
			$this->cal->expunge();
		}

		function delete_calendar($owner)
		{
			$this->cal->delete_calendar($owner);
		}

		function change_owner($account_id,$new_owner)
		{
			if($GLOBALS['phpgw_info']['server']['calendar_type'] == 'sql')
			{
				$db2 = $this->db;
				$this->db->select($this->user_table,'cal_id'.array('cal_user_id'=>$account_id),__LINE__,__FILE__);
				while($this->db->next_record())
				{
					$id = $this->db->f('cal_id');
					$db2->select($this->user_table,'count(*)',$where = array(
						'cal_id' => $id,
						'cal_user_id'	=> $new_owner,
					),__LINE__,__FILE__);
					$db2->next_record();
					if($db2->f(0) == 0)
					{
						$db2->update($this->user_table,array('cal_user_id' => $new_owner),$where,__LINE__,__FILE__);
					}
					else
					{
						$db2->delete($this->user_table,$where,__LINE__,__FILE__);
					}
				}
				$this->db->update($this->table,array('cal_owner'=>$new_owner),array('cal_owner'=>$account_id),__LINE__,__FILE__);
			}
		}

		function set_status($id,$status)
		{
			$this->cal->set_status($id,$this->owner,$status);
		}

		function get_alarm($cal_id)
		{
			if (!method_exists($this->cal,'get_alarm'))
			{
				return False;
			}
			return $this->cal->get_alarm($cal_id);
		}

		function read_alarm($id)
		{
			if (!method_exists($this->cal,'read_alarm'))
			{
				return False;
			}
			return $this->cal->read_alarm($id);
		}

		function read_alarms($cal_id)
		{
			if (!method_exists($this->cal,'read_alarms'))
			{
				return False;
			}
			return $this->cal->read_alarms($cal_id);
		}

		function find_recur_exceptions($event_id)
		{
			if($GLOBALS['phpgw_info']['server']['calendar_type'] == 'sql')
			{
				$arr = Array();
				$this->db->select($this->table,'cal_starttime',array('cal_reference'=>$event_id),__LINE__,__FILE__);
				if($this->cal->num_rows())
				{
					while($this->cal->next_record())
					{
						$arr[] = (int)$this->cal->f('cal_starttime');
					}
				}
				if(count($arr) == 0)
				{
					return False;
				}
				else
				{
					return $arr;
				}
			}
			else
			{
				return False;
			}
		}

		/* Begin mcal equiv functions */
		function get_cached_event()
		{
			return $this->cal->event;
		}
		
		function add_attribute($var,$value,$element='**(**')
		{
			$this->cal->add_attribute($var,$value,$element);
		}

		function event_init()
		{
			$this->cal->event_init();
		}

		function set_date($element,$year,$month,$day=0,$hour=0,$min=0,$sec=0)
		{
			$this->cal->set_date($element,$year,$month,$day,$hour,$min,$sec);
		}

		function set_start($year,$month,$day=0,$hour=0,$min=0,$sec=0)
		{
			$this->cal->set_start($year,$month,$day,$hour,$min,$sec);
		}

		function set_end($year,$month,$day=0,$hour=0,$min=0,$sec=0)
		{
			$this->cal->set_end($year,$month,$day,$hour,$min,$sec);
		}

		function set_title($title='')
		{
			$this->cal->set_title($title);
		}

		function set_description($description='')
		{
			$this->cal->set_description($description);
		}

		function set_class($class)
		{
			$this->cal->set_class($class);
		}

		function set_category($category='')
		{
			$this->cal->set_category($category);
		}

		function set_alarm($alarm)
		{
			$this->cal->set_alarm($alarm);
		}

		function set_recur_none()
		{
			$this->cal->set_recur_none();
		}

		function set_recur_daily($year,$month,$day,$interval)
		{
			$this->cal->set_recur_daily($year,$month,$day,$interval);
		}

		function set_recur_weekly($year,$month,$day,$interval,$weekdays)
		{
			$this->cal->set_recur_weekly($year,$month,$day,$interval,$weekdays);
		}

		function set_recur_monthly_mday($year,$month,$day,$interval)
		{
			$this->cal->set_recur_monthly_mday($year,$month,$day,$interval);
		}

		function set_recur_monthly_wday($year,$month,$day,$interval)
		{
			$this->cal->set_recur_monthly_wday($year,$month,$day,$interval);
		}

		function set_recur_yearly($year,$month,$day,$interval)
		{
			$this->cal->set_recur_yearly($year,$month,$day,$interval);
		}
		
		/* End mcal equiv functions */
	}
?>
