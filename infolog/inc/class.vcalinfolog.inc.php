<?php
/**
 * InfoLog - iCalendar Parser
 *
 * @link http://www.egroupware.org
 * @author Lars Kneschke <lkneschke@egroupware.org>
 * @package infolog
 * @subpackage syncml
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

require_once EGW_SERVER_ROOT.'/infolog/inc/class.boinfolog.inc.php';
require_once EGW_SERVER_ROOT.'/phpgwapi/inc/horde/Horde/iCalendar.php';

class vcalinfolog extends boinfolog
{
	var $egw_priority2vcal_priority = array(
		0	=> 3,
		1	=> 2,
		2	=> 1,
		3	=> 1,
	);

	var $vcal_priority2egw_priority = array(
		1       => 2,
		2       => 1,
		3       => 0,
	);

	/**
	 * Exports one InfoLog tast to an iCalendar VTODO
	 *
	 * @param int $_taskID info_id
	 * @param string $version='2.0' could be '1.0' too
	 * @param boolean $force_own_uid=true ignore the stored and maybe from the client transfered uid and generate a new one
	 * RalfBecker: GroupDAV/CalDAV requires to switch that non RFC conform behavior off, dont know if SyncML still needs it
	 * @param boolean $extra_charset_attribute=true GroupDAV/CalDAV dont need the charset attribute and some clients have problems with it
	 * @return string/boolean string with vCal or false on error (eg. no permission to read the event)
	 */
	function exportVTODO($_taskID, $_version='2.0',$force_own_uid=true,$extra_charset_attribute=true)
	{
		$taskData = $this->read($_taskID);

		$taskData = $GLOBALS['egw']->translation->convert($taskData, $GLOBALS['egw']->translation->charset(), 'UTF-8');

		$taskGUID = $GLOBALS['egw']->common->generate_uid('infolog_task',$_taskID);

		$vcal = &new Horde_iCalendar;
		$vcal->setAttribute('VERSION',$_version);
		$vcal->setAttribute('METHOD','PUBLISH');

		$vevent = Horde_iCalendar::newComponent('VTODO',$vcal);

		// set fields that may contain non-ascii chars and encode them if necessary
		foreach(array(
			'SUMMARY'     => $taskData['info_subject'],
			'DESCRIPTION' => $taskData['info_des'],
			'LOCATION'    => $taskData['info_location'],
		) as $field => $value)
		{
			$vevent->setAttribute($field,$value);
			$options = array();
			if(preg_match('/([\000-\012\015\016\020-\037\075])/',$value))
			{
				$options['ENCODING'] = 'QUOTED-PRINTABLE';
			}
			if($extra_charset_attribute && preg_match('/([\177-\377])/',$value))
			{
				$options['CHARSET'] = 'UTF-8';
			}
			if ($options) $vevent->setParameter($field, $options);
		}
		if($taskData['info_startdate'])
			$vevent->setAttribute('DTSTART',$taskData['info_startdate']);
		if($taskData['info_enddate'])
			$vevent->setAttribute('DUE',$taskData['info_enddate']);
		if($taskData['info_datecompleted'])
			$vevent->setAttribute('COMPLETED',$taskData['info_datecompleted']);
		$vevent->setAttribute('DTSTAMP',time());
		$vevent->setAttribute('CREATED',$GLOBALS['egw']->contenthistory->getTSforAction($eventGUID,'add'));
		$vevent->setAttribute('LAST-MODIFIED',$GLOBALS['egw']->contenthistory->getTSforAction($eventGUID,'modify'));
		$vevent->setAttribute('UID',$force_own_uid ? $taskGUID : $taskData['info_uid']);
		$vevent->setAttribute('CLASS',$taskData['info_access'] == 'public' ? 'PUBLIC' : 'PRIVATE');
		$vevent->setAttribute('STATUS',$this->status2vtodo($taskData['info_status']));
		// we try to preserv the original infolog status as X-INFOLOG-STATUS, so we can restore it, if the user does not modify STATUS
		$vevent->setAttribute('X-INFOLOG-STATUS',$taskData['info_status']);
		$vevent->setAttribute('PERCENT-COMPLETE',$taskData['info_percent']);
		$vevent->setAttribute('PRIORITY',$this->egw_priority2vcal_priority[$taskData['info_priority']]);

		if (!empty($taskData['info_cat']))
		{
			$cats = $this->get_categories(array($taskData['info_cat']));
			$vevent->setAttribute('CATEGORIES', $cats[0]);
		}
		$vcal->addComponent($vevent);

		return $vcal->exportvCalendar();
	}

	/**
	 * Import a VTODO component of an iCal
	 *
	 * @param string $_vcalData
	 * @param int $_taskID=-1 info_id, default -1 = new entry
	 * @return int|boolean integer info_id or false on error
	 */
	function importVTODO(&$_vcalData, $_taskID=-1)
	{
		if(!$taskData = $this->vtodotoegw($_vcalData,$_taskID))
		{
			return false;
		}
		// we suppose that a not set status in a vtodo means that the task did not started yet
		if(empty($taskData['info_status']))
		{
			$taskData['info_status'] = 'not-started';
		}
		return $this->write($taskData);
	}

	function searchVTODO($_vcalData)
	{
		if(!$egwData = $this->vtodotoegw($_vcalData)) {
			return false;
		}

		#unset($egwData['info_priority']);

		$filter = array('col_filter' => $egwData);
		if($foundItems = $this->search($filter)) {
			if(count($foundItems) > 0) {
				$itemIDs = array_keys($foundItems);
				return $itemIDs[0];
			}
		}

		return false;
	}

	function vtodotoegw($_vcalData,$_taskID=-1)
	{
		$vcal = &new Horde_iCalendar;
		if(!$vcal->parsevCalendar($_vcalData))
		{
			return FALSE;
		}

		$components = $vcal->getComponents();

		if(count($components) > 0)
		{
			$component = $components[0];
			if(is_a($component, 'Horde_iCalendar_vtodo'))
			{
				$taskData = array();
				if($_taskID > 0)
				{
					$taskData['info_id'] = $_taskID;
				}
				foreach($component->_attributes as $attributes)
				{
					switch($attributes['name'])
					{
						case 'CLASS':
							$taskData['info_access']		= strtolower($attributes['value']);
							break;
						case 'DESCRIPTION':
							$taskData['info_des']			= $attributes['value'];
							break;
						case 'LOCATION':
							$taskData['info_location']		= $attributes['value'];
							break;
						case 'DUE':
							$taskData['info_enddate']		= $attributes['value'];
							break;
						case 'COMPLETED':
							$taskData['info_datecompleted']	= $attributes['value'];
							break;
						case 'DTSTART':
							$taskData['info_startdate']		= $attributes['value'];
							break;
						case 'PRIORITY':
							if (1 <= $attributes['value'] && $attributes['value'] <= 3)
							{
								$taskData['info_priority']	= $this->vcal_priority2egw_priority[$attributes['value']];
							}
							else
							{
								$taskData['info_priority']	= 1;	// default = normal
							}
							break;
						case 'STATUS':
							// check if we (still) have X-INFOLOG-STATUS set AND it would give an unchanged status (no change by the user)
							foreach($component->_attributes as $attr)
							{
								if ($attr['name'] == 'X-INFOLOG-STATUS') break;
							}
							$taskData['info_status'] = $this->vtodo2status($attributes['value'],
								$attr['name'] == 'X-INFOLOG-STATUS' ? $attr['value'] : null);
							break;
						case 'SUMMARY':
							$taskData['info_subject']		= $attributes['value'];
							break;

						case 'CATEGORIES':
							$cats = $this->find_or_add_categories(explode(',', $attributes['value']));
							$taskData['info_cat'] = $cats[0];
							break;
						case 'UID':
							$taskData['info_uid'] = $attributes['value'];
							if ($_taskID <= 0 && !empty($attributes['value']) && ($uid_task = $this->read($attributes['value'])))
							{
								$taskData['info_id'] = $uid_task['id'];
								unset($uid_task);
							}
							break;
						case 'PERCENT-COMPLETE':
							$taskData['info_percent'] = (int) $attributes['value'];
							break;
					}
				}
				# the horde ical class does already convert in parsevCalendar
				# do NOT convert here
				#$taskData = $GLOBALS['egw']->translation->convert($taskData, 'UTF-8');

				return $taskData;
			}
		}
		return FALSE;
	}

	function exportVNOTE($_noteID, $_type)
	{
		$note = $this->read($_noteID);
		$note = $GLOBALS['egw']->translation->convert($note, $GLOBALS['egw']->translation->charset(), 'UTF-8');

		switch($_type)
		{
			case 'text/plain':
				$txt = $note['info_subject']."\n\n".$note['info_des'];
				return $txt;
				break;

			case 'text/x-vnote':
				$noteGUID = $GLOBALS['egw']->common->generate_uid('infolog_note',$_noteID);
				$vnote = &new Horde_iCalendar_vnote();
				$vNote->setAttribute('VERSION', '1.1');
				$vnote->setAttribute('SUMMARY',$note['info_subject']);
				$vnote->setAttribute('BODY',$note['info_des']);
				if($note['info_startdate'])
					$vnote->setAttribute('DCREATED',$note['info_startdate']);
				$vnote->setAttribute('DCREATED',$GLOBALS['egw']->contenthistory->getTSforAction($eventGUID,'add'));
				$vnote->setAttribute('LAST-MODIFIED',$GLOBALS['egw']->contenthistory->getTSforAction($eventGUID,'modify'));
				if (!empty($note['info_cat']))
				{
					$cats = $this->get_categories(array($note['info_cat']));
					$vnote->setAttribute('CATEGORIES', $cats[0]);
				}

				#$vnote->setAttribute('UID',$noteGUID);
				#$vnote->setAttribute('CLASS',$taskData['info_access'] == 'public' ? 'PUBLIC' : 'PRIVATE');

				#$options = array('CHARSET' => 'UTF-8','ENCODING' => 'QUOTED-PRINTABLE');
				#$vnote->setParameter('SUMMARY', $options);
				#$vnote->setParameter('DESCRIPTION', $options);

				return $vnote->exportvCalendar();
				break;
		}
		return false;
	}

	function importVNOTE(&$_vcalData, $_type, $_noteID = -1)
	{
		if(!$note = $this->vnotetoegw($_vcalData, $_type))
		{
			return false;
		}

		if($_noteID > 0)
		{
			$note['info_id'] = $_noteID;
		}

		if(empty($note['info_status'])) {
			$note['info_status'] = 'done';
		}

		#_debug_array($taskData);exit;
		return $this->write($note);
	}

	function searchVNOTE($_vcalData, $_type)
	{
		if(!$note = $this->vnotetoegw($_vcalData)) {
			return false;
		}

		$filter = array('col_filter' => $egwData);
		if($foundItems = $this->search($filter)) {
			if(count($foundItems) > 0) {
				$itemIDs = array_keys($foundItems);
				return $itemIDs[0];
			}
		}

		return false;
	}

	function vnotetoegw($_data, $_type)
	{
		switch($_type)
		{
			case 'text/plain':
				$note = array();
				$note['info_type'] = 'note';
				$botranslation  =& CreateObject('phpgwapi.translation');
				$txt = $botranslation->convert($_data, 'utf-8');
				$txt = str_replace("\r\n", "\n", $txt);

				if (preg_match("/^(^\n)\n\n(.*)$/", $txt, $match))
				{
					$note['info_subject'] = $match[0];
					$note['info_des'] = $match[1];
				}
				else
				{
					$note['info_des'] = $txt;
				}

				return $note;
				break;

			case 'text/x-vnote':
				$vnote = &new Horde_iCalendar;
				if (!$vcal->parsevCalendar($_data))
				{
					return FALSE;
				}
				$components = $vnote->getComponent();
				if(count($components) > 0)
				{
					$component = $components[0];
					if(is_a($component, 'Horde_iCalendar_vnote'))
					{
						$note = array();
						$note['info_type'] = 'note';

						foreach($component->_attributes as $attribute)
						{
							switch ($attribute['name'])
							{
								case 'BODY':
									$note['info_des'] = $attribute['value'];
									break;
								case 'SUMMARY':
									$note['info_subject'] = $attribute['value'];
									break;
								case 'CATEGORIES':
									{
										$cats = $this->find_or_add_categories(explode(',', $attribute['value']));
										$note['info_cat'] = $cats[0];
									}
									break;
							}
						}
					}
					return $note;
				}
		}
		return FALSE;
	}
}

