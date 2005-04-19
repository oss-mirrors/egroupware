<?php
/**************************************************************************\
* eGroupWare - ProjectManager - Elements business object                   *
* http://www.egroupware.org                                                *
* Written and (c) 2005 by Ralf Becker <RalfBecker@outdoor-training.de>     *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

/* $Id$ */

include_once(EGW_INCLUDE_ROOT.'/projectmanager/inc/class.soprojectelements.inc.php');

/**
 * Elements business object of the projectmanager
 *
 * @package projectmanager
 * @author RalfBecker-AT-outdoor-training.de
 * @copyright (c) 2005 by RalfBecker-AT-outdoor-training.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 */
class boprojectelements extends soprojectelements
{
	/**
	 * @var boolean $debug switch debug-messates on or off
	 */
	var $debug=false;
	/**
	 * @var bolink-object $link instance of the link-class
	 */
	var $link;
	/**
	 * @var boprojectmanager-object $link instance of the boprojectmanager-class
	 */
	var $project;
	/**
	 * @var array $datasources instances of the different datasources
	 */
	var $datasources = array();
	/**
	 * @var array $timestamps timestaps that need to be adjusted to user-time on reading or saving
	 */
	var $timestamps = array(
		'pm_synced','pe_modified'
	);
	/**
	 * @var int $tz_offset_s offset in secconds between user and server-time,
	 *	it need to be add to a server-time to get the user-time or substracted from a user-time to get the server-time
	 */
	var $tz_offset_s;
	/**
	 * @var int $now_su is the time as timestamp in user-time
	 */
	var $now_su;
	/**
	 * @var array $status_filter translates filter-values to allowed stati
	 */
	var $status_filter = array(
		'all'     => false,
		'used'    => array('new','regular'),
		'new'     => 'new',
		'ignored' => 'ignore',
	);

	/**
	 * Constructor, class the constructor of the extended class
	 * 
	 * @param int $pm_id pm_id of the project to use, default null
	 * @param int $pe_id pe_id of the project-element to load, default null
	 */
	function boprojectelements($pm_id=null,$pe_id=null)
	{
		if (!is_object($GLOBALS['egw']->datetime))
		{
			$GLOBALS['egw']->datetime =& CreateObject('phpgwapi.datetime');
		}
		$this->tz_offset_s = $GLOBALS['egw']->datetime->tz_offset;
		$this->now_su = time() + $this->tz_offset_s;
		
		$this->soprojectelements($pm_id,$pe_id);
		
		if (!is_object($GLOBALS['egw']->link))
		{
			$GLOBALS['egw']->link =& CreateObject('infolog.bolink');
		}
		$this->link =& $GLOBALS['egw']->link;

		$this->project =& CreateObject('projectmanager.boprojectmanager',$pm_id);

		if ($this->debug) $this->debug_message(function_backtrace()."\nboprojectelements::boprojectelements($pm_id,$pe_id) data=".print_r($this->data,true));

		// save us in $GLOBALS['boprojectselements'] for ExecMethod used in hooks
		if (!is_object($GLOBALS['boprojectselements']))
		{
			$GLOBALS['boprojectselements'] =& $this;
		}
	}
	
	/**
	 * receives notifications from the link-class: new, deleted links to pm entries, or updated content of linked entries
	 *
	 * We only process link- & update-notifications to parent-projects!
	 * A project P is the parent of an other project C, if link_id1=P.pm_id and link_id2=C.pm_id !
	 *
	 * @param array $data array with keys type, id, target_app, target_id, link_id, data
	 */
	function notify($data)
	{
		if ($this->debug) $this->debug_message("boprojectelements::notify(link_id=$data[link_id], type=$data[type], target=$data[target_app]-$data[target_id])");

		switch($data['type'])
		{
			case 'link':
			case 'update':
				// for projectmanager we need to check the direction of the link
				if ($data['target_app'] == 'projectmanager')
				{
					$link = $this->link->get_link($data['link_id']);
					if ($link['link_id2'] == $data['id'])
					{
						return;	// this is a notification to a child / subproject --> ignore it
					}
					// for new links we need to make sure the new child is not an ancestor of us
					if ($data['type'] == 'link')
					{
						if (($ancestors = $this->project->ancestors($data['id'])) && in_array($data['target_id'],$ancestors))
						{
							if ($this->debug) $this->debug_message("boprojectelements::notify: cant use pm_id=$data[target_id] as child as it's one of our (pm_id=$data[id]) ancestors=".print_r($ancestors,true));
							return;	// the link is not used as an project-element, thought it's still a regular link
						}
						if ($this->debug) $this->debug_message("boprojectelements::notify: ancestors($data[id])=".print_r($ancestors,true));
					}
				}
				$this->update($data['target_app'],$data['target_id'],$data['link_id'],$data['id']);
				break;

			case 'unlink':
				$this->delete(array('pm_id' => $data['id'],'pe_id' => $data['link_id']));
				break;
				
		}
	}

	/**
	 * Updates / creates a project-element with the data of it's datasource
	 *
	 * @param string $app appname
	 * @param string $id id of $app as used by the link-class and the datasource
	 * @param int $pe_id=0 element- / link-id or 0 to only read and return the entry, but not save it!
	 * @param int $pm_id=null project-id, default $this->pm_id
	 * @return array/boolean the updated project-element or false on error
	 */
	function &update($app,$id,$pe_id=0,$pm_id=null)
	{
		if (!$pm_id) $pm_id = $this->pm_id;
		
		if ($this->debug) $this->debug_message("boprojectelements::update(app='$app',id='$id',pe_id=$pe_id,pm_id=$pm_id)");

		$datasource =& 	$this->datasource($app);
		$data = $datasource->read($id);

		if (!$app || !(int) $id || !(int) $pm_id ||
			!($data = $datasource->read($id)))
		{
			return false;
		}
		$this->init();

		// check if entry already exists and set basic values if not
		if (!$pe_id || !$this->read(array('pm_id'=>$pm_id,'pe_id'=>$pe_id)))
		{
			$this->data['pm_id'] = $pm_id;
			$this->data['pe_id'] = $pe_id;
			$this->data['pe_status'] = 'new';
			$this->data['pe_overwrite'] = 0;		// none set so far
		}
		foreach($data as $name => $value)
		{
			if (isset($datasource->name2id[$name]) && !($this->data['pe_overwrite'] & $datasource->name2id[$name]))
			{
				$this->data[$name] = $value;
			}
		}
		$this->data['pe_title'] = $data['pe_title'];
		$this->data['pe_synced'] = $this->now_su;
		
		if((int) $pe_id) $this->save(null,false);	// dont set modified, only synced
		
		return $this->data;
	}

	/**
	 * sync all project-elements
	 *
	 * @param int $pm_id=null id of project to use, default null=use $this->pm_id
	 * @return int number of updated elements
	 */
	function &sync_all($pm_id=null)
	{
		if (!$pm_id && !($pm_id = $this->pm_id)) return 0;

		$updated = 0;
		foreach((array) $this->search(array('pm_id'=>$pm_id,"pe_status != 'ignore'"),false) as $data)
		{
			$this->data = $data;
			
			$datasource =& $this->datasource($data['pe_app']);
			$ds = $datasource->read($data['pe_app_id']);
			
			$update_necessary = 0;
			foreach($datasource->name2id as $name => $id)
			{
				if (!($data['pe_overwrite'] & $id) && $data[$name] != $ds[$name])
				{
					$this->data[$name] = $ds[$name];
					$update_necessary |= $id;
				}
			}
			$this->debug_message("boprojectemlements::sync_all($pm_id): element $data[pe_app]-$data[pe_app_id]: update_necessary=$update_necessary");

			if ($update_necessary) 
			{
				$this->save(null,false,0);	// update the project after all elements are synced
				$updated++;
			}
		}
		//if ($updated)
		{
			$this->project->update($pm_id);
		}
		return $updated;
	}

	/**
	 * checks if the user has enough rights for a certain operation
	 *
	 * @param int $required EGW_ACL_READ, EGW_ACL_WRITE, EGW_ACL_ADD, EGW_ACL_DELETE
	 * @param array/int $data=null project-element or pe_id to use, default the project-element in $this->data
	 * @return boolean true if the rights are ok, false if not
	 */
	function check_acl($required,$data=0)
	{
		if ($data)
		{
			if (!is_array($data))
			{
				$data_backup =& $this->data; unset($this->data);
				$data =& $this->read($data);
				$this->data =& $data_backup; unset($data_backup);
			
				if (!$data) return false;	// $pm_id not found ==> no rights
			}
		}
		else
		{
			$data =& $this->data;
		}
		// ToDo: concept and implementation of PM ACL !!!
		return $required != EGW_ACL_DELETE || $data['pe_id'];	// only false if trying to delete a not saved project
	}
	
	/**
	 * Get reference to instance of the datasource used for $app
	 *
	 * @param string $app appname
	 * @return object
	 */
	function &datasource($app)
	{
		if (!isset($this->datasources[$app]))
		{		
			if(!file_exists($classfile = EGW_INCLUDE_ROOT.'/projectmanager/inc/class.'.($class='datasource_'.$app).'.inc.php'))
			{
				$classfile = EGW_INCLUDE_ROOT.'/projectmanager/inc/class.'.($class='datasource').'.inc.php';
			}
			include_once($classfile);
			$this->datasources[$app] =& new $class($app);
		}
		return $this->datasources[$app];	
	}

	/**
	 * changes the data from the db-format to your work-format
	 *
	 * reimplemented to adjust the timezone of the timestamps (adding $this->tz_adjust_s to get user-time)
	 * Please note, we do NOT call the method of the parent or so_sql !!!
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
			if (isset($data[$name]) && $data[$name]) $data[$name] += $this->tz_adjust_s;
		}
		if (is_numeric($data['pe_completion'])) $data['pe_completion'] .= '%';
		if ($data['pe_app']) $data['pe_icon'] = $data['pe_app'].'/navbar';
		// convert time from min => sec
		if ($data['pe_used_time']) $data['pe_used_time'] *= 60;
		if ($data['pe_planed_time']) $data['pe_planed_time'] *= 60;

		return $data;
	}

	/**
	 * changes the data from your work-format to the db-format
	 *
	 * reimplemented to adjust the timezone of the timestamps (subtraction $this->tz_adjust_s to get server-time)
	 * Please note, we do NOT call the method of the parent or so_sql !!!
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
			if (isset($data[$name]) && $data[$name]) $data[$name] -= $this->tz_adjust_s;
		}
		if (substr($data['pe_completition'],-1) == '%') $data['pe_completition'] = (int) substr($data['pe_completition'],0,-1);
		// convert time from sec => min
		if ($data['pe_used_time']) $data['pe_used_time'] /= 60;
		if ($data['pe_planed_time']) $data['pe_planed_time'] /= 60;

		return $data;
	}
	
	/**
	 * saves an project-element, reimplemented from SO, to save the remark in the link, if $keys['update_remark']
	 *
	 * @param array $keys=null if given $keys are copied to data before saveing => allows a save as
	 * @param boolean $touch_modified=true should modification date+user be set, default yes
	 * @param int $update_project=-1 update the data in the project (or'ed PM_ id's), default -1=everything
	 * @return int 0 on success and errno != 0 else
	 */
	function save($keys=null,$touch_modified=true,$update_project=-1)
	{
		if ($keys['update_remark'] || $this->data['update_remark'])
		{
			unset($keys['update_remark']);
			unset($this->data['update_remark']);
			$this->link->update_remark($this->data['pe_id'],$this->data['pe_remark']);
		}
		if ($this->debug) $this->debug_message("boprojectelements::save(".print_r($keys,true).','.(int)$touch_modified.",$update_project) data=".print_r($this->data,true));
		if (!($err = parent::save($keys,$touch_modified)) && $update_project)
		{
			$this->project->update($this->data['pm_id'],$update_project,$this->data);
		}
		return $err;
	}

	/**
	 * deletes a project-element or all project-elements of a project, reimplemented to remove the link too
	 *
	 * @param array/int $keys if given array with pm_id and/or pe_id or just an integer pe_id
	 * @return int affected rows, should be 1 if ok, 0 if an error
	 */
	function delete($keys=null)
	{
		if (!is_array($keys) && (int) $keys)
		{
			$keys = array('pe_id' => (int) $keys);
		}
		if (!is_null($keys))
		{
			$pm_id = $keys['pm_id'];
			$pe_id = $keys['pe_id'];
		}
		else
		{
			$pe_id = $this->data['pe_id'];
			$pm_id = $this->data['pm_id'];
		}
		$ret = parent::delete($keys);
		
		if ($pe_id)
		{
			// delete one link
			$this->link->unlink($pe_id);
			// update the project
			$this->project->update($pm_id);
		}
		elseif ($pm_id)
		{
			// delete all links to project $pm_id
			$this->link->unlink(0,'projectmanager',$pm_id);
		}		
		return $ret;
	}
	
	/**
	 * echos a (preformatted / no-html) debug-message and evtl. log it to a file
	 *
	 * It uses the debug_message method of boprojectmanager
	 *
	 * @param string $msg
	 */
	function debug_message($msg)
	{
		$this->project->debug_message($msg);
	}		
}