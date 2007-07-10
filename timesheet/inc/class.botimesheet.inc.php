<?php
/**
 * TimeSheet - business object
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @package timesheet
 * @copyright (c) 2005/6 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$ 
 */

require_once(EGW_INCLUDE_ROOT.'/etemplate/inc/class.so_sql.inc.php');

if (!defined('TIMESHEET_APP'))
{
	define('TIMESHEET_APP','timesheet');
}

/**
 * Business object of the TimeSheet
 *
 * Uses eTemplate's so_sql as storage object (Table: egw_timesheet).
 */
class botimesheet extends so_sql
{
	/**
	 * Timesheets config data
	 * 
	 * @var array
	 */
	var $config = array();


	/**
	 * Timesheets config data
	 *
	 * @var array
	 */
	var $config_data = array();
	/**
	 * Should we show a quantity sum, makes only sense if we sum up identical units (can be used to sum up negative (over-)time)
	 *
	 * @var boolean
	 */
	var $quantity_sum=false;
	/**
	 * Timestaps that need to be adjusted to user-time on reading or saving
	 * 
	 * @var array
	 */
	var $timestamps = array(
		'ts_start','ts_modified'
	);
	/**
	 * Offset in secconds between user and server-time,	it need to be add to a server-time to get the user-time 
	 * or substracted from a user-time to get the server-time
	 * 
	 * @var int
	 */
	var $tz_offset_s;
	/**
	 * Current time as timestamp in user-time
	 * 
	 * @var int
	 */
	var $now;
	/**
	 * Start of today in user-time
	 * 
	 * @var int
	 */
	var $today;
	/**
	 * Filter for search limiting the date-range
	 * 
	 * @var array
	 */
	var $date_filters = array(	// Start: year,month,day,week, End: year,month,day,week
		'Today'       => array(0,0,0,0,  0,0,1,0),
		'Yesterday'   => array(0,0,-1,0, 0,0,0,0),
		'This week'   => array(0,0,0,0,  0,0,0,1),
		'Last week'   => array(0,0,0,-1, 0,0,0,0),
		'This month'  => array(0,0,0,0,  0,1,0,0),
		'Last month'  => array(0,-1,0,0, 0,0,0,0),
		'2 month ago' => array(0,-2,0,0, 0,-1,0,0),
		'This year'   => array(0,0,0,0,  1,0,0,0),
		'Last year'   => array(-1,0,0,0, 0,0,0,0),
		'2 years ago' => array(-2,0,0,0, -1,0,0,0),
		'3 years ago' => array(-3,0,0,0, -2,0,0,0),
	);
	/**
	 * Reference to the (bo)link class instanciated at $GLOBALS['egw']->link
	 * 
	 * @var bolink
	 */
	var $link;
	/**
	 * Grants: $GLOBALS['egw']->acl->get_grants(TIMESHEET_APP);
	 * 
	 * @var array
	 */	
	var $grants;
	/**
	 * Sums of the last search in keys duration and price
	 * 
	 * @var array
	 */
	var $summary;
	/**
	 * Array with boolean values in keys 'day', 'week' or 'month', for the sums to return in the search
	 * 
	 * @var array
	 */
	var $show_sums;

	var $customfields=array();
	
	function botimesheet()
	{
		$this->so_sql(TIMESHEET_APP,'egw_timesheet');

		$this->config =& CreateObject('phpgwapi.config',TIMESHEET_APP);
		$this->config->read_repository();
		$this->config_data =& $this->config->config_data;
		$this->quantity_sum = $this->config_data['quantity_sum'] == 'true';

		if (isset($this->config_data['customfields']) && is_array($this->config_data['customfields']))
		{
			$this->customfields = $this->config_data['customfields'];
		}

		if (!is_object($GLOBALS['egw']->datetime))
		{
			$GLOBALS['egw']->datetime =& CreateObject('phpgwapi.datetime');
		}
		$this->tz_offset_s = $GLOBALS['egw']->datetime->tz_offset;
		$this->now = time() + $this->tz_offset_s;	// time() is server-time and we need a user-time
		$this->today = mktime(0,0,0,date('m',$this->now),date('d',$this->now),date('Y',$this->now));

		// save us in $GLOBALS['botimesheet'] for ExecMethod used in hooks
		if (!is_object($GLOBALS['botimesheet']))
		{
			$GLOBALS['botimesheet'] =& $this;
		}
		// instanciation of link-class has to be after making us globaly availible, as it calls us to get the search_link
		if (!is_object($GLOBALS['egw']->link))
		{
			$GLOBALS['egw']->link =& CreateObject('phpgwapi.bolink');
		}
		$this->link =& $GLOBALS['egw']->link;
		
		$this->grants = $GLOBALS['egw']->acl->get_grants(TIMESHEET_APP);
	}
	
	/**
	 * get list of specified grants as uid => Username pairs
	 *
	 * @param int $required=EGW_ACL_READ
	 * @return array with uid => Username pairs
	 */
	function grant_list($required=EGW_ACL_READ)
	{
		$result = array();
		foreach($this->grants as $uid => $grant)
		{
			if ($grant & $required)
			{
				$result[$uid] = $GLOBALS['egw']->common->grab_owner_name($uid);
			}
		}
		natcasesort($result);

		return $result;
	}
	
	/**
	 * checks if the user has enough rights for a certain operation
	 *
	 * Rights are given via owner grants or role based acl
	 *
	 * @param int $required EGW_ACL_READ, EGW_ACL_WRITE, EGW_ACL_ADD, EGW_ACL_DELETE, EGW_ACL_BUDGET, EGW_ACL_EDIT_BUDGET
	 * @param array/int $data=null project or project-id to use, default the project in $this->data
	 * @return boolean true if the rights are ok, null if not found, false if no rights
	 */
	function check_acl($required,$data=null)
	{
		if (!$data)
		{
			$data =& $this->data;
		}
		if (!is_array($data))
		{
			$save_data = $this->data;
			$data = $this->read($data,true);
			$this->data = $save_data;
			
			if (!$data) return null; 	// entry not found
		}
		$rights = $this->grants[$data['ts_owner']];
		
		return $data && !!($rights & $required);
	}
	
	function date_filter($name,&$start,&$end_param)
	{
		$end = $end_param;

		if ($name == 'custom' && $start)
		{
			if ($end)
			{
				$end += 24*60*60;
			}
			else
			{
				$end = $start + 8*24*60*60;
			}
		}
		else
		{
			if (!isset($this->date_filters[$name]))
			{
				return '1=1';
			}
			$year  = (int) date('Y',$this->today);
			$month = (int) date('m',$this->today);
			$day   = (int) date('d',$this->today);
	
			list($syear,$smonth,$sday,$sweek,$eyear,$emonth,$eday,$eweek) = $this->date_filters[$name];
			
			if ($syear || $eyear)
			{
				$start = mktime(0,0,0,1,1,$syear+$year);
				$end   = mktime(0,0,0,1,1,$eyear+$year);
			}
			elseif ($smonth || $emonth)
			{
				$start = mktime(0,0,0,$smonth+$month,1,$year);
				$end   = mktime(0,0,0,$emonth+$month,1,$year);
			}
			elseif ($sday || $eday)
			{
				$start = mktime(0,0,0,$month,$sday+$day,$year);
				$end   = mktime(0,0,0,$month,$eday+$day,$year);
			}
			elseif ($sweek || $eweek)
			{
				$wday = (int) date('w',$this->today); // 0=sun, ..., 6=sat
				switch($GLOBALS['egw_info']['user']['preferences']['calendar']['weekdaystarts'])
				{
					case 'Sunday':
						$weekstart = $this->today - $wday * 24*60*60;
						break;
					case 'Saturday':
						$weekstart = $this->today - (6-$wday) * 24*60*60;
						break;
					case 'Moday':
					default:
						$weekstart = $this->today - ($wday ? $wday-1 : 6) * 24*60*60;
						break;
				}
				$start = $weekstart + $sweek*7*24*60*60;
				$end   = $weekstart + $eweek*7*24*60*60;
			}
			$end_param = $end - 24*60*60;
		}
		//echo "<p align='right'>date_filter($name,$start,$end) today=".date('l, Y-m-d H:i',$this->today)." ==> ".date('l, Y-m-d H:i:s',$start)." <= date < ".date('l, Y-m-d H:i:s',$end)."</p>\n"; 
		// convert start + end from user to servertime for the filter
		return '('.($start-$this->tz_offset_s).' <= ts_start AND ts_start < '.($end-$this->tz_offset_s).')';
	}

	/**
	 * search the timesheet
	 *
	 * reimplemented to limit result to users we have grants from
	 *
	 * @param array/string $criteria array of key and data cols, OR a SQL query (content for WHERE), fully quoted (!)
	 * @param boolean/string $only_keys=true True returns only keys, False returns all cols. comma seperated list of keys to return
	 * @param string $order_by='' fieldnames + {ASC|DESC} separated by colons ',', can also contain a GROUP BY (if it contains ORDER BY)
	 * @param string/array $extra_cols='' string or array of strings to be added to the SELECT, eg. "count(*) as num"
	 * @param string $wildcard='' appended befor and after each criteria
	 * @param boolean $empty=false False=empty criteria are ignored in query, True=empty have to be empty in row
	 * @param string $op='AND' defaults to 'AND', can be set to 'OR' too, then criteria's are OR'ed together
	 * @param mixed $start=false if != false, return only maxmatch rows begining with start, or array($start,$num)
	 * @param array $filter=null if set (!=null) col-data pairs, to be and-ed (!) into the query without wildcards
	 * @param string $join='' sql to do a join, added as is after the table-name, eg. ", table2 WHERE x=y" or 
	 *	"LEFT JOIN table2 ON (x=y)", Note: there's no quoting done on $join!
	 * @param boolean $need_full_no_count=false If true an unlimited query is run to determine the total number of rows, default false
	 * @param boolean $only_summary=false If true only return the sums as array with keys duration and price, default false
	 * @return array of matching rows (the row is an array of the cols) or False
	 */
	function &search($criteria,$only_keys=True,$order_by='',$extra_cols='',$wildcard='',$empty=False,$op='AND',$start=false,$filter=null,$join='',$need_full_no_count=false,$only_summary=false)
	{
		// postgres can't round from double precission, only from numeric ;-)
		$total_sql = $this->db->Type != 'pgsql' ? "round(ts_quantity*ts_unitprice,2)" : "round(cast(ts_quantity*ts_unitprice AS numeric),2)";

		if (!is_array($extra_cols))
		{
			$extra_cols = $extra_cols ? explode(',',$extra_cols) : array();
		}
		$extra_cols[] = $total_sql.' AS ts_total';

		if (!isset($filter['ts_owner']) || !count($filter['ts_owner']))
		{
			$filter['ts_owner'] = array_keys($this->grants);
		}
		else
		{
			if (!is_array($filter['ts_owner'])) $filter['ts_owner'] = array($filter['ts_owner']);
			
			foreach($filter['ts_owner'] as $key => $owner)
			{
				if (!isset($this->grants[$owner]))
				{
					unset($filter['ts_owner'][$key]);
				}
			}
		}
		if (!count($filter['ts_owner']))
		{
			$this->total = 0;
			$this->summary = array();
			return array();
		}
		$this->summary = parent::search($criteria,"SUM(ts_duration) AS duration,SUM($total_sql) AS price".
			($this->quantity_sum ? ",SUM(ts_quantity) AS quantity" : ''),
			'','',$wildcard,$empty,$op,false,$filter,$join);
		$this->summary = $this->summary[0];
		
		if ($only_summary) return $this->summary;

		if ($this->show_sums && strpos($order_by,'ts_start') !== false && 	// sums only make sense if ordered by ts_start
			$this->db->capabilities['union'] && ($from_unixtime_ts_start = $this->db->from_unixtime('ts_start')))
		{
			$sum_sql = array(
				'year'  => $this->db->date_format($from_unixtime_ts_start,'%Y'),
				'month' => $this->db->date_format($from_unixtime_ts_start,'%Y%m'),
				'week'  => $this->db->date_format($from_unixtime_ts_start,$GLOBALS['egw_info']['user']['preferences']['calendar']['weekdaystarts'] == 'Sunday' ? '%X%V' : '%x%v'),
				'day'   => $this->db->date_format($from_unixtime_ts_start,'%Y-%m-%d'),
			);
			foreach($this->show_sums as $type)
			{
				$extra_cols[] = $sum_sql[$type].' AS ts_'.$type;
				$extra_cols[] = '0 AS is_sum_'.$type;
				$sum_extra_cols[] = str_replace('ts_start','MIN(ts_start)',$sum_sql[$type]);	// as we dont group by ts_start
				$sum_extra_cols[$type] = '0 AS is_sum_'.$type;
			}
			// regular entries
			parent::search($criteria,$only_keys,$order_by,$extra_cols,$wildcard,$empty,$op,'UNION',$filter,$join,$need_full_no_count);
			
			$sort = substr($order_by,8);
			$union_order = array();
			$sum_ts_id = array('year' => -3,'month' => -2,'week' => -1,'day' => 0);
			foreach($this->show_sums as $type)
			{
				$union_order[] = 'ts_'.$type . ' ' . $sort;
				$union_order[] = 'is_sum_'.$type;
				$sum_extra_cols[$type]{0} = '1';
				// the $type sum
				parent::search($criteria,$sum_ts_id[$type].",'','','',MIN(ts_start),SUM(ts_duration) AS ts_duration,".
					($this->quantity_sum ? "SUM(ts_quantity) AS ts_quantity" : '0').",0,NULL,0,0,0,0,SUM($total_sql) AS ts_total",
					'GROUP BY '.$sum_sql[$type],$sum_extra_cols,$wildcard,$empty,$op,'UNION',$filter,$join,$need_full_no_count);
				$sum_extra_cols[$type]{0} = '0';
			}
			$union_order[] = 'ts_start '.$sort;
			return parent::search('','',implode(',',$union_order),'','',false,'',$start);
		}
		return parent::search($criteria,$only_keys,$order_by,$extra_cols,$wildcard,$empty,$op,$start,$filter,$join,$need_full_no_count);
	}

	/**
	 * read a timesheet entry
	 *
	 * @param int $ts_id
	 * @param boolean $ignore_acl=false should the acl be checked
	 * @return array/boolean array with timesheet entry, null if timesheet not found or false if no rights
	 */
	function read($ts_id,$ignore_acl=false)
	{
		$ret = null;
		if (!(int)$ts_id || !$ignore_acl && !($ret = $this->check_acl(EGW_ACL_READ,$ts_id)) ||
			$this->data['ts_id'] != (int)$ts_id && !parent::read((int)$ts_id))
		{
			return $ret;	// no read rights, or entry not found
		}

		//assign custom fields
		foreach($this->customfields as $name => $value) {
			$row = $this->read_extra($name);
			$this->data['#'.$name] = $row['ts_extra_value'];
		}

		return $this->data;
	}

	/**
	 * reads a timesheet extra entry of the current timesheet dataset
	 *
	 * @param int $name => name of the current timesheet extra entry
	 * @param int $value => value of the current timesheet extra entry
	 * @return array of resultset
	 */
	function read_extra($name='',$value='')
	{
		strlen($value) > 0 ? $where = ' and ts_extra_value ='.$this->db->quote($value) : '';
		strlen($name) > 0 ? $where .= ' and ts_extra_name ='.$this->db->quote($name) : '';

		$this->db->select('egw_timesheet_extra', 'ts_extra_name, ts_extra_value',$query,__LINE__,__FILE__,False,'',False,0,'where ts_id='.$this->data['ts_id'].$where);
		$row = $this->db->row(true);

		return $row;
	}
	
	/**
	 * saves a timesheet entry
	 *
	 * reimplemented to notify the link-class
	 *
	 * @param array $keys if given $keys are copied to data before saveing => allows a save as
	 * @param boolean $touch_modified=true should modification date+user be set, default yes
	 * @param boolean $ignore_acl=false should the acl be checked, returns true if no edit-rigts
	 * @return int 0 on success and errno != 0 else
	 */
	function save($keys=null,$touch_modified=true,$ignore_acl=false)
	{
		if ($keys) $this->data_merge($keys);
		
		if (!$ignore_acl && $this->data['ts_id'] && !$this->check_acl(EGW_ACL_EDIT))
		{
			return true;
		}
		if ($touch_modified)
		{
			$this->data['ts_modifier'] = $GLOBALS['egw_info']['user']['account_id'];
			$this->data['ts_modified'] = $this->now;
		}
		if (!($err = parent::save()))
		{
			//saves data of custom fields in timesheet_extra
			$this->save_extra();

			// notify the link-class about the update, as other apps may be subscribt to it
			$this->link->notify_update(TIMESHEET_APP,$this->data['ts_id'],$this->data);
		}
		return $err;
	}

	/**
	 * saves a timesheet extra entry based one the "custom fields" settings
	 *
	 * @param boolean  $updateNames => if true "change timesheet extra name", otherwise update existing datasets or insert new ones
	 * @param boolean  $oldname => original name of the timesheet extra entry
	 * @param boolean  $name => new name of the timesheet extra entry
	 * @return int true on success else false
	 */
	function save_extra($updateNames=False,$oldname='',$name='')
	{
		if($updateNames) {
			$keys = array('ts_extra_name' => $oldname);
			$fieldAssign = array('ts_extra_name' => $name);
			$this->db->update('egw_timesheet_extra',$fieldAssign,$keys,__LINE__,__FILE__);
			return true;
		}
		else {
			foreach($this->customfields as $namecf => $valuecf) {
				//if entry not exist => insert
				if(!$this->read_extra($namecf)) {
					$fieldAssign = array('ts_id' => $this->data['ts_id'],'ts_extra_name' => $namecf,'ts_extra_value' => $this->data['#'.$namecf]);
					$this->db->insert('egw_timesheet_extra',$fieldAssign,false,__LINE__,__FILE__);
				}
				//otherwise update existing dataset
				else {
					$keys = array('ts_extra_name' => $namecf, 'ts_id' => $this->data['ts_id']);
					$fieldAssign = array('ts_extra_value' => $this->data['#'.$namecf]);
					$this->db->update('egw_timesheet_extra',$fieldAssign,$keys,__LINE__,__FILE__);
				}
			}
			return true;
		}

		return false;
	}
	
	/**
	 * deletes a timesheet entry identified by $keys or the loaded one, reimplemented to notify the link class (unlink)
	 *
	 * @param array $keys if given array with col => value pairs to characterise the rows to delete
	 * @param boolean $ignore_acl=false should the acl be checked, returns false if no delete-rigts
	 * @return int affected rows, should be 1 if ok, 0 if an error
	 */
	function delete($keys=null,$ignore_acl=false)
	{
		if (!is_array($keys) && (int) $keys)
		{
			$keys = array('ts_id' => (int) $keys);
		}
		$ts_id = is_null($keys) ? $this->data['ts_id'] : $keys['ts_id'];
		
		if (!$this->check_acl(EGW_ACL_DELETE,$ts_id))
		{
			return false;
		}
		if (($ret = parent::delete($keys)) && $ts_id)
		{
			//delete custom fields entries
			$this->delete_extra($ts_id);

			// delete all links to timesheet entry $ts_id
			$this->link->unlink(0,TIMESHEET_APP,$ts_id);
		}
		return $ret;
	}


	/**
	 * deletes a timesheet extra entry identified by $ts_id and/or $ts_exra_name
	 *
	 * @param int $ts_id => number of timesheet
	 * @param string ts_extra_name => certain custom field name
	 * @return int false if an error
	 */
	function delete_extra($ts_id='',$ts_extra_name='')
	{
		strlen($ts_id) > 0 ? $where['ts_id'] = $ts_id : '';
		strlen($ts_extra_name) > 0 ? $where['ts_extra_name'] = $ts_extra_name : '';

		if(count($where) > 0) {
			return $this->db->delete('egw_timesheet_extra', $where,__LINE__,__FILE__);
		}

		return false;
	}

	/**
	 * changes the data from the db-format to your work-format
	 *
	 * reimplemented to adjust the timezone of the timestamps (adding $this->tz_offset_s to get user-time)
	 * Please note, we do NOT call the method of the parent so_sql !!!
	 *
	 * @param array $data if given works on that array and returns result, else works on internal data-array
	 * @return array with changed data
	 */
	function db2data($data=null)
	{
		if (!is_array($data))
		{
			$data = &$this->data;
		}
		foreach($this->timestamps as $name)
		{
			if (isset($data[$name]) && $data[$name]) $data[$name] += $this->tz_offset_s;
		}
		return $data;
	}

	/**
	 * changes the data from your work-format to the db-format
	 *
	 * reimplemented to adjust the timezone of the timestamps (subtraction $this->tz_offset_s to get server-time)
	 * Please note, we do NOT call the method of the parent so_sql !!!
	 *
	 * @param array $data if given works on that array and returns result, else works on internal data-array
	 * @return array with changed data
	 */
	function data2db($data=null)
	{
		if ($intern = !is_array($data))
		{
			$data = &$this->data;
		}
		foreach($this->timestamps as $name)
		{
			if (isset($data[$name]) && $data[$name]) $data[$name] -= $this->tz_offset_s;
		}
		return $data;
	}
	
	/**
	 * Get the time- and pricesum for the given timesheet entries
	 *
	 * @param array $ids array of timesheet id's
	 * @return array with keys time and price
	 */
	function sum($ids)
	{
		return $this->search(array('ts_id'=>$ids),true,'','','',false,'AND',false,null,'',false,true);
	}
	
	/**
	 * get title for an timesheet entry identified by $entry
	 * 
	 * Is called as hook to participate in the linking
	 *
	 * @param int/array $entry int ts_id or array with timesheet entry
	 * @return string/boolean string with title, null if timesheet not found, false if no perms to view it
	 */
	function link_title( $entry )
	{
		if (!is_array($entry))
		{
			$entry = $this->read( $entry );
		}
		if (!$entry)
		{
			return $entry;
		}
		$format = $GLOBALS['egw_info']['user']['preferences']['common']['dateformat'];
		if (date('H:i',$entry['ts_start']) != '00:00')	// dont show 00:00 time, as it means date only
		{
			$format .= ' '.($GLOBALS['egw_info']['user']['preferences']['common']['timeformat'] == 12 ? 'h:i a' : 'H:i');
		}
		return date($format,$entry['ts_start']).': '.$entry['ts_title'];
	}

	/**
	 * query timesheet for entries matching $pattern
	 *
	 * Is called as hook to participate in the linking
	 *
	 * @param string $pattern pattern to search
	 * @return array with ts_id - title pairs of the matching entries
	 */
	function link_query( $pattern )
	{
		$criteria = array();
		foreach(array('ts_project','ts_title','ts_description') as $col)
		{
			$criteria[$col] = $pattern;
		}
		$result = array();
		foreach((array) $this->search($criteria,false,'','','%',false,'OR') as $ts )
		{
			if ($ts) $result[$ts['ts_id']] = $this->link_title($ts);
		}
		return $result;
	}

	/**
	 * Hook called by link-class to include timesheet in the appregistry of the linkage
	 *
	 * @param array/string $location location and other parameters (not used)
	 * @return array with method-names
	 */
	function search_link($location)
	{
		return array(
			'query' => TIMESHEET_APP.'.botimesheet.link_query',
			'title' => TIMESHEET_APP.'.botimesheet.link_title',
			'view'  => array(
				'menuaction' => TIMESHEET_APP.'.uitimesheet.view',
			),
			'view_id' => 'ts_id',
			'view_popup'  => '600x400',			
			'add' => array(
				'menuaction' => TIMESHEET_APP.'.uitimesheet.edit',
			),
			'add_app'    => 'link_app',
			'add_id'     => 'link_id',		
			'add_popup'  => '600x400',			
		);
	}
	
	/**
	 * Return the timesheets linked with given project(s) AND with entries of other apps, which are also linked to the same project
	 * 
	 * Projectmanager will cumulate them in the other apps entries.
	 *
	 * @param array $param int/array $param['pm_id'] project-id(s)
	 * @return array with pm_id, pe_id, pe_app('timesheet'), pe_app_id(ts_id), other_id, other_app, other_app_id
	 */
	function cumulate($param)
	{
		$links = $this->link->get_3links(TIMESHEET_APP,'projectmanager',$param['pm_id']);
		
		$rows = array();
		foreach($links as $link)
		{
			$rows[$link['id']] = array(
				'pm_id'       => $link['id2'],
				'pe_id'       => $link['id'],
				'pe_app'      => $link['app1'],
				'pe_app_id'   => $link['id1'],
				'other_id'    => $link['link3'],
				'other_app'   => $link['app3'],
				'other_app_id'=> $link['id3'],
			);
		}
		return $rows;
	}

	/**
	 * updates the project titles in the timesheet application (called whenever a project name is changed in the project manager)
	 *
	 * Todo: implement via notification
	 *
	 * @param string $oldtitle => the origin title of the project
	 * @param string $newtitle => the new title of the project
	 * @return boolean true for success, false for invalid parameters
	 */
	 function update_ts_project($oldtitle='', $newtitle='')
	 {
		if(strlen($oldtitle) > 0 && strlen($newtitle) > 0) {
			$keys = array('ts_project' => $oldtitle);
			$fieldAssign = array('ts_project' => $newtitle,'ts_title' => $newtitle);
			$this->db->update('egw_timesheet',$fieldAssign,$keys,__LINE__,__FILE__);

			return true;
		}

		return false;
	 }

	/**
	 * returns array with relation link_id and ts_id (necessary for project-selection)
	 *
	 * @param int $pm_id ID of selected project
	 * @return array containing link_id and ts_id
	 */	
	function get_ts_links($pm_id=0) {
		$tslist = array();		
		if(strlen($pm_id) > 0) {
			if(isset($GLOBALS['egw_info']['user']['apps']['projectmanager'])) {	
				$bo_pm = CreateObject('projectmanager.boprojectmanager');
				$childs = $bo_pm->children($pm_id);
				$childs[] = $pm_id;
				$pmChilds = implode(",",$childs);
				$this->db->select(	'egw_links','link_id, link_id1',$query,
							__LINE__,__FILE__,False,
							'',False,0,
							'JOIN egw_pm_projects ON (pm_id = link_id2)
							 WHERE 
								link_app1 = \'timesheet\' AND
								link_app2 = \'projectmanager\' AND
								link_id2 IN ('.$pmChilds.')');
					
				while($row = $this->db->row(true)) {
					$tslist[$row['link_id']] = $row['link_id1'];
				}
					
			}

		} 
		return $tslist;
	}
}