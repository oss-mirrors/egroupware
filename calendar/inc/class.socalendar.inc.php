<?php
  /**************************************************************************\
  * phpGroupWare - Calendar                                                  *
  * http://www.phpgroupware.org                                              *
  * Based on Webcalendar by Craig Knudsen <cknudsen@radix.net>               *
  *          http://www.radix.net/~cknudsen                                  *
  * Modified by Mark Peters <skeeter@phpgroupware.org>                       *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	class socalendar
	{
		var $debug = False;
		var $cal;
		var $db;
		var $owner;
		var $datetime;
		var $filter;
		var $cat_id;

		function socalendar($owner=0,$filter='',$cat_id='')
		{
			global $phpgw, $phpgw_info;

			$this->db = $phpgw->db;
			$this->datetime = CreateObject('phpgwapi.datetime');
			if($owner == 0)
			{
				$this->owner = $phpgw_info['user']['account_id'];
			}
			else
			{
				$this->owner = $owner;
			}

			if($filter != '')
			{
				$this->filter = $filter;
			}

			if($cat_id != '')
			{
				$this->cat_id = $cat_id;
			}
			if($this->debug)
			{
				echo 'SO Filter : '.$this->filter."<br>\n";
				echo 'SO cat_id : '.$this->cat_id."<br>\n";
			}
		}

		function makeobj()
		{
			if (!is_object($this->cal))
			{
				$this->cal = CreateObject('calendar.socalendar_');
				$this->cal->open('INBOX',intval($this->owner));
			}
			return;
		}

		function read_entry($id)
		{
			$this->makeobj();
			return $this->cal->fetch_event($id);
		}

		function list_events($startYear,$startMonth,$startDay,$endYear=0,$endMonth=0,$endDay=0)
		{
			$this->makeobj();

			$extra = '';
			if(strpos($this->filter,'private'))
			{
				$extra .= 'AND phpgw_cal.is_public=0 ';
			}

			if($this->cat_id)
			{
				$extra .= 'AND phpgw_cal.category = '.$this->cat_id.' ';
			}
			return $this->cal->list_events($startYear,$startMonth,$startDay,$endYear,$endMonth,$endDay,$extra,$this->datetime->tz_offset);
		}

		function list_repeated_events($syear,$smonth,$sday,$eyear,$emonth,$eday)
		{
			global $phpgw, $phpgw_info;
			
			if($phpgw_info['server']['calendar_type'] != 'sql')
			{
				return Array();
			}

			$this->makeobj();
			$starttime = mktime(0,0,0,$smonth,$sday,$syear) - $this->datetime->tz_offset;
			$endtime = mktime(23,59,59,$emonth,$eday,$eyear) - $this->datetime->tz_offset;
//			$starttime = mktime(0,0,0,$smonth,$sday,$syear);
//			$endtime = mktime(23,59,59,$emonth,$eday,$eyear);
			$sql = "AND (phpgw_cal.cal_type='M') "
				. 'AND (phpgw_cal_user.cal_login='.$this->owner.' '
//				. 'AND (phpgw_cal.datetime <= '.$starttime.') '
				. 'AND (((phpgw_cal_repeats.recur_enddate >= '.$starttime.') AND (phpgw_cal_repeats.recur_enddate <= '.$endtime.')) OR (phpgw_cal_repeats.recur_enddate=0))) ';

			if(strpos($this->filter,'private'))
			{
				$sql .= 'AND phpgw_cal.is_public=0 ';
			}

			if($this->cat_id)
			{
				$sql .= 'AND phpgw_cal.category = '.$this->cat_id.' ';
			}

			$sql .= 'ORDER BY phpgw_cal.datetime ASC, phpgw_cal.edatetime ASC, phpgw_cal.priority ASC';

			if($this->debug)
			{
				echo "SO list_repeated_events : SQL : ".$sql."<br>\n";
			}

			return $this->get_event_ids(True,$sql);
		}

		function list_events_keyword($keywords)
		{
			$this->makeobj();
			
			$sql = 'AND (phpgw_cal_user.cal_login='.$this->owner.') ';

			$words = split(' ',$keywords);
			for ($i=0;$i<count($words);$i++)
			{
				if($i==0)
				{
					$sql .= ' AND (';
				}
				if($i>0)
				{
					$sql .= ' OR ';
				}
				$sql .= "(UPPER(phpgw_cal.title) LIKE UPPER('%".$words[$i]."%') OR "
						. "UPPER(phpgw_cal.description) LIKE UPPER('%".$words[$i]."%'))";
						
				if($i==count($words) - 1)
				{
					$sql .= ') ';
				}
			}

			if(strpos($this->filter,'private'))
			{
				$sql .= 'AND phpgw_cal.is_public=0 ';
			}

			if($this->cat_id)
			{
				$sql .= 'AND phpgw_cal.category = '.$this->cat_id.' ';
			}

			$sql .= 'ORDER BY phpgw_cal.datetime ASC, phpgw_cal.edatetime ASC, phpgw_cal.priority ASC';
			return $this->get_event_ids(False,$sql);
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

		function get_event_ids($include_repeats=False, $sql='')
		{
			$this->makeobj();
			return $this->cal->get_event_ids($include_repeats,$sql);
		}

		function add_entry(&$event)
		{
			$this->makeobj();
			$this->cal->store_event($event);
		}

		function delete_entry($id)
		{
			$this->makeobj();
			$this->cal->delete_event($id);
		}

		function expunge()
		{
			$this->makeobj();
			$this->cal->expunge();
		}

		function set_status($id,$status)
		{
			$this->makeobj();
			$this->cal->set_status($id,$this->owner,$status);
		}

		function get_lastid()
		{
			$this->makeobj();
		 	$entry = $this->cal->read_last_entry();
			$ab_id = $entry[0]['id'];
			return $ab_id;
		}

		function update_entry($userid,$fields)
		{
			$this->makeobj();
			if ($this->rights & PHPGW_ACL_EDIT)
			{
				$this->cal->update($fields['ab_id'],$userid,$fields,$fields['access'],$fields['cat_id']);
			}
			return;
		}

		/* Begin mcal equiv functions */
		function get_cached_event()
		{
			return $this->cal->event;
		}
		
		function add_attribute($var,$value)
		{
			$this->cal->add_attribute($var,$value);
		}

		function event_init()
		{
			$this->makeobj();
			$this->cal->event_init();
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
