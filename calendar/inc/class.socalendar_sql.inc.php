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

if (@$phpgw_info['flags']['included_classes']['socalendar_'])
{
	return;
}

$phpgw_info['flags']['included_classes']['socalendar_'] = True;

class socalendar_ extends socalendar__
{
	var $deleted_events = Array();
	
	var $cal_event;
	var $today = Array('raw','day','month','year','full','dow','dm','bd');

	function open($calendar='',$user='',$passwd='',$options='')
	{
		global $phpgw, $phpgw_info;

		if($user=='')
		{
			settype($user,'integer');
			$user = $phpgw_info['user']['account_id'];
		}
		elseif(is_int($user)) 
		{
			$this->user = $user;
		}
		elseif(is_string($user))
		{
			$this->user = $phpgw->accounts->name2id($user);
		}

		$this->stream = $phpgw->db;
		return $this->stream;
	}

	function popen($calendar='',$user='',$passwd='',$options='')
	{
		return $this->open($calendar,$user,$passwd,$options);
	}

	function reopen($calendar,$options='')
	{
		return $this->stream;
	}

	function close($options='')
	{
		return True;
	}

	function create_calendar($calendar='')
	{
		return $calendar;
	}

	function rename_calendar($old_name='',$new_name='')
	{
		return $new_name;
	}
    
	function delete_calendar($calendar='')
	{
		$this->stream->query('SELECT cal_id FROM phpgw_cal WHERE owner='.intval($calendar),__LINE__,__FILE__);
		if($this->stream->num_rows())
		{
			while($this->stream->next_record())
			{
				$this->delete_event(intval($this->stream->f('cal_id')));
			}
			$this->expunge();
		}
		$this->stream->lock(array('phpgw_cal_user'));
		$this->stream->query('DELETE FROM phpgw_cal_user WHERE cal_login='.intval($calendar),__LINE__,__FILE__);
		$this->stream->unlock();
			
		return $calendar;
	}

	function fetch_event($event_id,$options='')
	{
		global $phpgw;
		
		if(!isset($this->stream))
		{
			return False;
		}

		$this->stream->lock(array('phpgw_cal','phpgw_cal_user','phpgw_cal_repeats'));

		$this->stream->query('SELECT * FROM phpgw_cal WHERE cal_id='.$event_id,__LINE__,__FILE__);
		
		if($this->stream->num_rows() > 0)
		{
			$this->event_init();
			
			$this->stream->next_record();
			// Load the calendar event data from the db into $event structure
			// Use http://www.php.net/manual/en/function.mcal-fetch-event.php as the reference
			$this->add_attribute('owner',intval($this->stream->f('owner')));
			$this->add_attribute('id',intval($this->stream->f('cal_id')));
			$this->set_class(intval($this->stream->f('is_public')));
			$this->set_category(intval($this->stream->f('category')));
			$this->set_title($phpgw->strip_html($this->stream->f('title')));
			$this->set_description($phpgw->strip_html($this->stream->f('description')));
			
			// This is the preferred method once everything is normalized...
			//$this->event->alarm = intval($this->stream->f('alarm'));
			// But until then, do it this way...
		//Legacy Support (New)
			$this->event->alarm = 0;

			$this->add_attribute('datetime',intval($this->stream->f('datetime')));
			$datetime = $this->datetime->localdates($this->stream->f('datetime'));
			$this->set_start($datetime['year'],$datetime['month'],$datetime['day'],$datetime['hour'],$datetime['minute'],$datetime['second']);

			$datetime = $this->datetime->localdates($this->stream->f('mdatetime'));
			$this->event->mod->year	= $datetime['year'];
			$this->event->mod->month	= $datetime['month'];
			$this->event->mod->mday	= $datetime['day'];
			$this->event->mod->hour	= $datetime['hour'];
			$this->event->mod->min	= $datetime['minute'];
			$this->event->mod->sec	= $datetime['second'];
			$this->event->mod->alarm	= 0;

			$this->add_attribute('edatetime',intval($this->stream->f('edatetime')));
			$datetime = $this->datetime->localdates($this->stream->f('edatetime'));
			$this->set_end($datetime['year'],$datetime['month'],$datetime['day'],$datetime['hour'],$datetime['minute'],$datetime['second']);

		//Legacy Support
			$this->add_attribute('priority',intval($this->stream->f('priority')));
			if($this->stream->f('cal_group') || $this->stream->f('groups') != 'NULL')
			{
				$groups = explode(',',$this->stream->f('groups'));
				for($j=1;$j<count($groups) - 1;$j++)
				{
					$this->event->groups[] = $groups[$j];
				}
			}
			
			$this->stream->query('SELECT * FROM phpgw_cal_repeats WHERE cal_id='.$event_id,__LINE__,__FILE__);
			if($this->stream->num_rows())
			{
				$this->stream->next_record();

				$this->event->recur_type = intval($this->stream->f('recur_type'));
				$this->event->recur_interval = intval($this->stream->f('recur_interval'));
				$enddate = $this->stream->f('recur_enddate');
				if($enddate != 0 && $enddate != Null)
				{
					$datetime = $this->datetime->localdates($enddate);
					$this->event->recur_enddate->year	= $datetime['year'];
					$this->event->recur_enddate->month	= $datetime['month'];
					$this->event->recur_enddate->mday	= $datetime['day'];
					$this->event->recur_enddate->hour	= $datetime['hour'];
					$this->event->recur_enddate->min	= $datetime['minute'];
					$this->event->recur_enddate->sec	= $datetime['second'];
					$this->event->recur_enddate->alarm	= 0;
				}
				else
				{
					$this->event->recur_enddate->year	= 0;
					$this->event->recur_enddate->month	= 0;
					$this->event->recur_enddate->mday	= 0;
					$this->event->recur_enddate->hour	= 0;
					$this->event->recur_enddate->min	= 0;
					$this->event->recur_enddate->sec	= 0;
					$this->event->recur_enddate->alarm	= 0;
				}
//	echo 'Event ID#'.$this->event->id.' : Enddate = '.$enddate."<br>\n";
				$this->event->recur_data = $this->stream->f('recur_data');
			}
			
		//Legacy Support
			$this->stream->query('SELECT * FROM phpgw_cal_user WHERE cal_id='.$event_id,__LINE__,__FILE__);
			if($this->stream->num_rows())
			{
				while($this->stream->next_record())
				{
					if(intval($this->stream->f('cal_login')) == intval($this->user))
					{
						$this->event->users_status = $this->stream->f('cal_status');
					}
//					$this->event->participants[$this->stream->f('cal_login')] = $this->stream->f('cal_status');
//					$this->add_attribute('participants',$this->stream->f('cal_status'),intval($this->stream->f('cal_login')));
					$this->add_attribute('participants['.intval($this->stream->f('cal_login')).']',$this->stream->f('cal_status'));
				}
			}
		}
		else
		{
			$this->event = False;
		}
      
		$this->stream->unlock();

		return $this->event;
	}

	function list_events($startYear,$startMonth,$startDay,$endYear='',$endMonth='',$endDay='',$extra='',$tz_offset=0)
	{
		if(!isset($this->stream))
		{
			return False;
		}

		$datetime = mktime(0,0,0,$startMonth,$startDay,$startYear) - $tz_offset;
		$user_where = ' AND (phpgw_cal_user.cal_login = '.$this->user.') ';
		$startDate = 'AND (phpgw_cal.datetime >= '.$datetime.') ';
	  
		if($endYear != '' && $endMonth != '' && $endDay != '')
		{
			$edatetime = mktime(23,59,59,intval($endMonth),intval($endDay),intval($endYear)) - $tz_offset;
			$endDate = 'AND (phpgw_cal.edatetime <= '.$edatetime.') ';
		}
		else
		{
			$endDate = '';
		}

		$order_by = 'ORDER BY phpgw_cal.datetime ASC, phpgw_cal.edatetime ASC, phpgw_cal.priority ASC';
		return $this->get_event_ids(False,$user_where.$startDate.$endDate.$extra.$order_by);
	}

	function append_event()
	{
		$this->save_event($this->event);
		$this->send_update(MSG_ADDED,$this->event->participants,'',$this->event);
		return $this->event->id;
	}

	function store_event()
	{
		if($this->event->id != 0)
		{
			$new_event = $this->event;
			$old_event = $this->fetch_event($new_event->id);
			$this->prepare_recipients($new_event,$old_event);
			$this->event = $new_event;
		}
		else
		{
			while(list($key,$value) = each($this->event->participants))
			{
				$this->add_attribute('participants['.intval($key).']','U');
			}
			$this->send_update(MSG_ADDED,$this->event->participants,'',$this->event);
		}
		return $this->save_event($this->event);
	}

	function delete_event($event_id)
	{
		$this->deleted_events[] = $event_id;
	}

	function snooze($event_id)
	{
	//Turn off an alarm for an event
	//Returns true. 
	}

	function list_alarms($begin_year='',$begin_month='',$begin_day='',$end_year='',$end_month='',$end_day='')
	{
	//Return a list of events that has an alarm triggered at the given datetime
	//Returns an array of event ID's
	}

	// The function definition doesn't look correct...
	// Need more information for this function
	function next_recurrence($weekstart,$next)
	{
//		return next_recurrence (int stream, int weekstart, array next);
	}

	function expunge()
	{
		if(count($this->deleted_events) <= 0)
		{
			return 1;
		}
		$this_event = $this->event;
		$locks = Array(
			'phpgw_cal',
			'phpgw_cal_user',
			'phpgw_cal_repeats'
		);
		$this->stream->lock($locks);
		for($i=0;$i<count($this->deleted_events);$i++)
		{
			$event_id = $this->deleted_events[$i];

			$event = $this->fetch_event($event_id);
			$this->send_update(MSG_DELETED,$event->participants,$event);

			for($k=0;$k<count($locks);$k++)
			{
				$this->stream->query('DELETE FROM '.$locks[$k].' WHERE cal_id='.$event_id,__LINE__,__FILE__);
			}
		}
		$this->stream->unlock();
		$this->event = $this_event;
		return 1;
	}
	
	/***************** Local functions for SQL based Calendar *****************/

	function get_event_ids($search_repeats=False,$extra='')
	{
		if($search_repeats == True)
		{
			$repeats_from = ', phpgw_cal_repeats ';
			$repeats_where = 'AND (phpgw_cal_repeats.cal_id = phpgw_cal.cal_id) ';
		}
		else
		{
			$repeats_from = ' ';
			$repeats_where = '';
		}
		
		$sql = 'SELECT DISTINCT phpgw_cal.cal_id,'
				. 'phpgw_cal.datetime,phpgw_cal.edatetime,'
				. 'phpgw_cal.priority '
				. 'FROM phpgw_cal, phpgw_cal_user'
				. $repeats_from
				. 'WHERE (phpgw_cal_user.cal_id = phpgw_cal.cal_id) '
				. $repeats_where . $extra;
		$this->stream->query($sql,__LINE__,__FILE__);

		$retval = Array();
		if($this->stream->num_rows() == 0)
		{
			return $retval;
		}

		while($this->stream->next_record())
		{
			$retval[] = intval($this->stream->f('cal_id'));
		}
		return $retval;
	}

	function save_event(&$event)
	{
		global $phpgw_info;

		$locks = Array(
			'phpgw_cal',
			'phpgw_cal_user',
			'phpgw_cal_repeats'
		);
		$this->stream->lock($locks);
		if($event->id == 0)
		{
			$temp_name = tempnam($phpgw_info['server']['temp_dir'],'cal');
			$this->stream->query('INSERT INTO phpgw_cal(title,owner,priority,is_public) '
				. "values('".$temp_name."',".$event->owner.",".$event->priority.",".$event->public.")");
			$this->stream->query("SELECT cal_id FROM phpgw_cal WHERE title='".$temp_name."'");
			$this->stream->next_record();
			$event->id = $this->stream->f('cal_id');
		}

		$date = mktime($event->start->hour,$event->start->min,$event->start->sec,$event->start->month,$event->start->mday,$event->start->year) - $this->datetime->tz_offset;
		$enddate = mktime($event->end->hour,$event->end->min,$event->end->sec,$event->end->month,$event->end->mday,$event->end->year) - $this->datetime->tz_offset;
		$today = time() - $this->datetime->tz_offset;
//		$today = time();

		if($event->recur_type != MCAL_RECUR_NONE)
		{
			$type = 'M';
		}
		else
		{
			$type = 'E';
		}

		$cat = '';
		if($event->category != 0)
		{
			$cat = 'category='.$event->category.', ';
		}

		$sql = 'UPDATE phpgw_cal SET '
				. 'owner='.$event->owner.', '
				. 'datetime='.$date.', '
				. 'mdatetime='.$today.', '
				. 'edatetime='.$enddate.', '
				. 'priority='.$event->priority.', '
				. $cat
				. "cal_type='".$type."', "
				. 'is_public='.$event->public.', '
				. "title='".addslashes($event->title)."', "
				. "description='".addslashes($event->description)."' "
				. 'WHERE cal_id='.$event->id;
				
		$this->stream->query($sql,__LINE__,__FILE__);
		
		$this->stream->query('DELETE FROM phpgw_cal_user WHERE cal_id='.$event->id,__LINE__,__FILE__);

		reset($event->participants);
		while (list($key,$value) = each($event->participants))
		{
			if(intval($key) == intval($this->user))
			{
				$value = 'A';
			}
			$this->stream->query('INSERT INTO phpgw_cal_user(cal_id,cal_login,cal_status) '
				. 'VALUES('.$event->id.','.intval($key).",'".$value."')",__LINE__,__FILE__);
		}

		if($event->recur_type != MCAL_RECUR_NONE)
		{
			if($event->recur_enddate->month != 0 && $event->recur_enddate->mday != 0 && $event->recur_enddate->year != 0)
			{
				$end = mktime($event->recur_enddate->hour,$event->recur_enddate->min,$event->recur_enddate->sec,$event->recur_enddate->month,$event->recur_enddate->mday,$event->recur_enddate->year) - $this->datetime->tz_offset;
			}
			else
			{
				$end = 0;
			}

			$this->stream->query('SELECT count(cal_id) FROM phpgw_cal_repeats WHERE cal_id='.$event->id,__LINE__,__FILE__);
			$this->stream->next_record();
			$num_rows = $this->stream->f(0);
			if($num_rows == 0)
			{
				$this->stream->query('INSERT INTO phpgw_cal_repeats(cal_id,recur_type,recur_enddate,recur_data,recur_interval) '
					.'VALUES('.$event->id.','.$event->recur_type.','.$end.','.$event->recur_data.','.$event->recur_interval.')',__LINE__,__FILE__);
			}
			else
			{
				$this->stream->query('UPDATE phpgw_cal_repeats '
					.'SET recur_type='.$event->recur_type.', '
					.'recur_enddate='.$end.', '
					.'recur_data='.$event->recur_data.', recur_interval='.$event->recur_interval.' '
					.'WHERE cal_id='.$event->id,__LINE__,__FILE__);
			}
		}
		else
		{
			$this->stream->query('DELETE FROM phpgw_cal_repeats WHERE cal_id='.$event->id,__LINE__,__FILE__);
		}
		
		$this->stream->unlock();
		return True;
	}

	function set_status($id,$owner,$status)
	{
		$status_code_short = Array(
			REJECTED =>	'R',
			NO_RESPONSE	=> 'U',
			TENTATIVE	=>	'T',
			ACCEPTED	=>	'A'
		);
		$temp_event = $this->event;
		$old_event = $this->fetch_event($id);
		switch($status)
		{
			case REJECTED:
				$this->send_update(MSG_REJECTED,$old_event->participants,$old_event);
				$this->stream->query("DELETE FROM phpgw_cal_user WHERE cal_id=".$id." AND cal_login=".$owner,__LINE__,__FILE__);
				break;
			case TENTATIVE:
				$this->send_update(MSG_TENTATIVE,$old_event->participants,$old_event);
				$this->stream->query("UPDATE phpgw_cal_user SET cal_status='".$status_code_short[$status]."' WHERE cal_id=".$id." AND cal_login=".$owner,__LINE__,__FILE__);
				break;
			case ACCEPTED:
				$this->send_update(MSG_ACCEPTED,$old_event->participants,$old_event);
				$this->stream->query("UPDATE phpgw_cal_user SET cal_status='".$status_code_short[$status]."' WHERE cal_id=".$id." AND cal_login=".$owner,__LINE__,__FILE__);
				break;
		}
		$this->event = $temp_event;
		return True;
	}
	
// End of ICal style support.......

	function group_search($owner=0)
	{
		global $phpgw, $phpgw_info;
      
		$owner = $owner==$phpgw_info['user']['account_id']?0:$owner;
		$groups = substr($phpgw->common->sql_search('phpgw_cal.groups',intval($owner)),4);
		if (!$groups)
		{
			return '';
		}
		else
		{
			return "(phpgw_cal.is_public=2 AND (". $groups .')) ';
		}
	}

	function splittime_($time)
	{
		global $phpgw_info;

		$temp = array('hour','minute','second','ampm');
		$time = strrev($time);
		$second = (int)strrev(substr($time,0,2));
		$minute = (int)strrev(substr($time,2,2));
		$hour   = (int)strrev(substr($time,4));
		$temp['second'] = (int)$second;
		$temp['minute'] = (int)$minute;
		$temp['hour']   = (int)$hour;
		$temp['ampm']   = '  ';

		return $temp;
	}

	function date_to_epoch($d)
	{
		return $this->localdates(mktime(0,0,0,intval(substr($d,4,2)),intval(substr($d,6,2)),intval(substr($d,0,4))));
	}
}
?>
