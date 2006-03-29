<?php
   /** 
	* @file 
	* eGroupWare - iCalendar VFREEBUSY conversion,  export and import for egw calendar.
	*
	* http://www.egroupware.org                                                *
	* @author Jan van Lieshout   
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License.              *
	**************************************************************************/


  /** @page pageegwicalvfreebusyfeatures Currently implemented VFREEBUSY handling in egwical
   * <PRE>
   * TODO:
   * @todo [+] export
   *    [-] export calendar owner as ATTENDEE
   *    [+] export ... type events as FBTYPE= ...
   *    [+/-] export url as call adres for this calendark
   *    [-] report the requestor as ORGANIZER (we should have his id then..)
   *  
   * @todo [-] import
   *    [-] parse the import VFEEBUSY data, 
   *    [-] determine a set of request period from this
   *    [-] generate VFREEBUSY elements for each of these fb_periods
   *    [-] deliver a compound VCALENDAR with these VFREEBUSY elements
   * </PRE>
   */

	require_once EGW_SERVER_ROOT.'/calendar/inc/class.bocalupdate.inc.php';
    require_once EGW_SERVER_ROOT.'/egwical/inc/class.egwical_resourcehandler.inc.php';


    /**
	 * Concrete subclass resourcehandler for iCal vfreebusy import and export with a egroupware
	 * bocalupdate calendar resource.
	 *
	 * @todo Here should come some text about the workings of this class
	 * @todo get import of freebusy working....
	 * @package egwical
	 * @author Jan van Lieshout <jvl-AT-xs4all.nl> (This version. new api rewrite,
	 * refactoring, and extension).
	 * @version 0.9.30a1  first version for napi3
	 * license @url  http://opensource.org/licenses/gpl-license.php GPL -
	 * GNU General Public License
	 */
    class bocalupdate_vfreebusy extends egwical_resourcehandler
    {
	  
	  /**
	   * @private
	   * @var boolean
	   * Switch to print extra debugging about imported and exported elements to the httpd errorlog
	   * stream.
	   */
	  var $vfdebug = true;

	  /** The Bound Egw Resource that we handle
	   * @private
	   * @var bocalupdate $rsc
	   * Registry for the egw resource object (calendar, infolog,..) that will be used
	   * to transport ical elements from and to: The socalled <i>
	   * Bound Resource</i>
	   * This can be set by the constructor or later by set_rsc().
	   */
	  var $rsc = null;


	  /** Request Info hash - some of the fields from the request
	   * This is to be set by the constructor of this handler.
	   * Fields that can be set in this hash are:
	   <PRE>
	    $fb_req = array( 'start'     => <Start time of the request period for the freebusies>, 
		                 'end'       => <End time of the request period for the freebusies>,
						 'requestor' => <Requestor of the freebusy output (by egw id)>,
						 'cal_owner' => <Owner of the calendar (by egw id)>,
						 );
      </PRE>
	   * @var array $fb_req
	   */
	  var $fb_req = array();
	   

	  /** mapping from iCalendar VFREEBUSY fields to egw calendar fields
	   * @private
	   * @var array $vfreebusy2eventFields
	   * An array containing roughly the mapping from iCalendar
	   * to egw fields. Set by constructor.
	   * example entry (<i>rn</i> stands for "Resourced_Name"):
	   * <PRE>
			'SUMMARY'	=> array('rn' => 'title'),
  	    </PRE>
	   * Here <i>rn</i> stands for "Resourced  Name", to indicate the name of the related
	   * related field in the bound egw resource
	   * @todo integrate this with the  egwical base $ical2egw conversion table
	   */
	  var $vfreebusy2eventFields = array();


	  /** Deliver the implemented vfreebusy to event mapping as provided by this class.
	   *
	   * @private
	   *
	   * Produce the array of vfreebusy to event field mappings that this class implements.
	   * These are stored on instantiation in the variable $vfreebusy2eventFields
	   * @note this is just a vague indication of fields handled...
	   * @return array The provided vfreebusy to event fields mapping.
	   */
	  function _provided_vfreebusy2eventFields()
	  {
		return
		  array(
				// optional, occur only once
				'CONTACT'   => array('fn_contact' => ''),
				'DTSTART'	=> array('fn_fb' => 'fb_req[start]'),
				'DTEND'		=> array('fn_fb' => 'fb_req[end]'),
				'DURATION'  => array('fn_dur' => 'end-duration'),
				'DTSTAMP'   => array('fn_dtstamp' => ''),
				'ORGANIZER'	=> array('fn_fb' => 'fb_req[requestor_id]'),

				'UID'		=> array('fn_uid' => 'uid'),
				'URL'	    => array('fn_url' => 'fb_req[url]'),

				// optional, may occur multiple
				'ATTENDEE'	=> array('fn_fb' => 'fb_req[cal_owner_id]'),
				'COMMENT'	=> array('fn_rn' => ''),
				'FREEBUSY'  => array('fn_rns'=> array('start','end','recur_type',
												   'recur_exception')
									 ),
				);
	  }



	  /**
	   * Our Constructor, if given it sets the egw resource $egwrsc is set as
	   * so called <i>bound egw resource</i>. And $prodid, the product id of the client that
	   * will use or produce ical data is set to determine which fields to use in coming
	   * import and exportd conversions between vcalendar and egw data.
	   * @param egwobj $egwrsc Egroupware data resource object that
	   * will be used to transport (i.e. import and export) the
	   * vcalendar elements to and from. This can also later be set
	   * using the set_rsc() method.
	   * @param ProductType $devicetype The type identification of the device that is used to
	   * @param array $fb_req hash with e.g. start, end and requestor for the freebusy request.
	   * @param Utime $fb_end  end time of the request period for the freebusies.
	   * the transport the ical data to and from. This is used to set the supportedFields already.
	   * @note These can also later be set using the setSupportedFields() method. 
	   */
	  function bocalupdate_vfreebusy($egwrsc = null, $devicetype='all',
									 &$fb_req = null)
		
	  {
		// call our abstract superclass constructor
		egwical_resourcehandler::egwical_resourcehandler($egwrsc, $prodid);
		//@todo rewrite supportedFields setting to distribute it over the egwical
		// baseclass and the subclasses cleverly
		$this->vfreebusy2eventFields = $this->_provided_vfreebusy2eventFields();
		// add reference to the base table too
		$this->ical2egwComponents['VFREEBUSY'] = $this->vfreebusy2eventFields;
		// default initialization
		$this->setSupportedFields($devicetype);
		if($fb_req !== null)
		  $this->fb_req = $fb_req;

		return true;		
	  }


	  /** Set the egw calendar resource  that this worker will handle.  
	   * 
	   * This worker is only capable of handling  bocalupdate calendar objects, so it should
	   * be of that class. The $egw_rsc is registered in the $rsc variable and the supported
	   * ical element is set to be 'vevent'. This is registered in $rsc_vtypes.
	   *
	   * @param egwobj $egw_rsc the resource object of type bocalupdate that will be
	   * used to transport the ical data to and from.
	   * @return boolean false on error, true if the $egw_rsc was indeed a correct  resource
	   * of the supported type (bocalupdate).
	  */
	  function set_rsc($egw_rsc)
	  {
		if(!is_a($egw_rsc,'bocalupdate'))
		  return false;
		$this->rsc = $egw_rsc;
		$this->rsc_vtypes[]= 'vfreebusy';
		return true;
	  }

	  // -------- below only conversion and import/export stuff -----


	  /** Export calendar event from bound bocalupdate resource as FREEBUSY field
	   *
	   * The eGW events in $eids are exported to a iCalendar FREEBUSY (of type
	   * Horde_iCalendar_vfreebusyd). For each event a FREEBUSY field is set.
	   * Note that only the set of supported Fields, as indicated by the $supportedFields
	   * member variable, are considered for the VFREEBUSY.
	   * 
	   * The end and start of the freebusy period are set from the member variables
	   * $fb_start and $fb-end.
	   * @todo should the VFREEBUSY element get a uid? and if so: what and why?
	   * @bug export_vfreebusy is currently very rudimentary....
	   * @bug export_vfreebusy doesnot incorporate busytimes resulting from recurring events yet
	   *
	   * @param array_of_EventId $eids list of the eGW events by id, whose freebusy times will
	   * be exported.
	   * @return VFREEBUSY|false  an iCalendar VFREEBUSY object that collects all the freebusy
	   * times from the events in $eids. With a period set according the $fb_start and $fb_end
	   * member vars.
	   * On error: false
	   */
 	  function export_vfreebusy(&$events)
	  {

		if($this->vfdebug)
		  error_log('fb_req=' . print_r($this->fb_req,true));
		// auxiliary horde_iCalendar object
		$hIcal = $this->hi; 

		$vfExportFields =& $this->supportedFields;
		if($this->vfdebug)
		  error_log('vfExportFields:'. print_r($vfExportFields,true));

		$vfreebusy = Horde_iCalendar::newComponent('VFREEBUSY',$this->hi);
		$parameters = $attributes = array();

		// first  create the events independent fields
		foreach($vfExportFields as $vfFieldName) {

		  switch($vfFieldName) {

		  case 'UID':
			// not yet implemented
			//$eventGUID = $this->ecu->mki_v_guid($eid,'freebusy');
			//$attributes['UID'] = $eventGUID;				
			break;

		  case 'URL':
			if ($v = $this->fb_req['url'])
			  $attributes[$vfFieldName]	= $v;
			break;

			// use daylight savings time patch, for some dates
			// they are expected in Ymd format
		  case 'DTSTART':
			if ($v = $this->fb_req['start']){
			  $u = $this->ecu->hi->_parseDateTime($v);
			  $attributes[$vfFieldName]	= $this->ecu->st_dst_patch($u);
			}
			break;
		  case 'DTEND':
			if ($v = $this->fb_req['end']){
			  $u = $this->ecu->hi->_parseDateTime($v);
			  $attributes[$vfFieldName]	= $this->ecu->st_dst_patch($u);
			}
			break;

		  case 'ORGANIZER':	
			// according to rfc the organizer is the requestor of the freebusy export
			// possibly set as id in member var $fb_req
			if ($pid = $this->fb_req['requestor']){
			  $attributes['ORGANIZER']  = $this->ecu->mki_v_CAL_ADDRESS($pid);
			  $parameters['ORGANIZER']  = $this->ecu->mki_p_CN($pid);
			}
			break;

		  case 'DTSTAMP':
			$attributes[$vfFieldName]	= date('Ymd');
			break; 
			
		  case 'ATTENDEE':	
			// according to rfc the attendee is the owner of  freebusy data
			// possibly set as id in member var $fb_req
			if ($pid = $this->fb_req['cal_owner']){
			  $attributes['vfFielName']  = $this->ecu->mki_v_CAL_ADDRESS($pid);
			  $parameters['vfFielName']  = $this->ecu->mki_p_CN($pid);
			}
			break;

		  case 'FREEBUSY':	
			// do this later in the event export loop
			break;

		  default:
			error_log('bocalupdate_vfreebusy.export: ' . $vfFieldName
					  . ' export not implemented yet');
			break;
			
		  }
		}  // end of foreach vfExportFields
			
		// add all collected attributes sofar (not yet added) to the vevent
		foreach($attributes as $aname => $avalue) {
		  $this->ecu->updi_c_addAttribute($vfreebusy,
										  $aname,
										  $avalue,
										  $parameters[$aname]);
		}

		// should we export FREEBUSY fields ?
		if (in_array('FREEBUSY',$vfExportFields)) {

		  // next add FREEBUSY fields for each event
		  // we still allow events or eventeids here should be changed when search is 
		  // in search probably
		  foreach ($events as $event){
			if (!is_array($event)){
			  // event was passed as an event id
			  $eid = $event;
			  if( !$event = $this->rsc->read($eid,null,false,'server')){
				// server = timestamp in server-time(!)
				error_log('bocalupdate_vfreebusy.export: warning: no permission to read '
						  . 'bocalupdate event with id:' . $eid);
				//return false;	// no permission to read $cal_id
				continue;
			  }
			  // event was passed as an array of fields
			} else {
			  $eid = $event['id'];
			  // now read it again to get all fields (including our alarms)
			  //$event = $this->rsc->read($eid);
			}

			if($this->vfdebug)
			  error_log('exporting event id: ' . $eid);

			$avalue = $this->ecu->mki_v_FREEBUSY($this->ecu->st_dst_patch($event['start']),
												 $this->ecu->st_dst_patch($event['end'])
												 );
			$aparma = $this->ecu->mki_p_FBTYPE();
			$this->ecu->updi_c_addAttribute($vfreebusy,
										  'FREEBUSY',
										  $avalue,
										  $parameters[$aname]);

//    maybe whole day detector can be used for special busy setting
// 		  // wholeday detector (DTEND =23:59:59 && DTSTART = 00:00)
// 		  // if detected the times will be exported in VALUE=DATE format
// 		  if(((date('H:i:s',$event['end']) == '23:59:59') ||
// 			  (date('H:i:s',$event['end']) == '00:00:00')) 
// 			 && (date('H:i',$event['start'] == '00:00'))){
// 			$attributes['DTSTART'] =
// 			  $this->hi->_parseDate(date('Ymd',$event['start']));
// 			$attributes['DTEND'] =
// 			  $this->hi->_parseDate(date('Ymd',$event['end']+1));
// 			$parameters['DTEND']['VALUE'] = 'DATE';
// 			$parameters['DTSTART']['VALUE'] = 'DATE';
// 			//	error_log('WHOLE DAY DETECTED');
// 		  }

		  } //end of foreach($eids as $id) loop

		}
		
		return $vfreebusy; //return VFREEBUSYObj

	  }



	  /** Export all events as a VFREEBUSY element --overwrites baseclass method--
	   *
	   * @param array_of_EventId $eids list of event id in the bound bocalupdate resource
	   * that are to be exported in VFREEBUSY elements. 
	   * @return VFREEBUSY the exported egw events converted to one or more VFREEBUSY objects.
	   * on error False.
	   */
	  function export_velts(&$eids)
	  {
		return array($this->export_vfreebusy($eids));
	  }


	  /**
	   * Export events from bocal as vfreebusy iCalendar string. --overwrites base method--
	   *  
	   * All the egw elements in the bound egw bocalendar resource, refered to by
	   * the ids in $eids are exported as Vfreebusy Vcalendar formatted string.
	   * Specific global attributes settings  for this string are
	   * taken from .....
	   *
	   * @param  array_of_EventId $eids a list of events id s for
	   * the bound calendar resource whose freebusy time are to be exported.
	   * @param array $attribs optional hash with global iCalendar
	   * attributes settings. These attributes will be added and
	   * possibly override the standard attributes as found in $this->vcalendar2egwAttributes
	   * @return VcalStr a iCalendar formatted string  corresponding
	   * to the VFREEBUSY data converted from the egw events refered to by $eids
	   * On error: false
	   */
	  function export_vcal(&$eids, $attribs = null)
	  {
		// be tolerant towards a single eid
		if(!is_array($eids))
		  $eids = array($eids);

		if(($velt = $this->export_vfreebusy($eids)) !== false){
		  return $this->render_velt2vcal($myhi, $attribs);			
		}

		return false;

	  }




	  /** Import a VFREEBUSY element, i.e.: respond to the freebusy request-periods
	   * with an exported VFREEBUSY element.
	   *
	   * The ical VFREEBUSY component is converted to an set of dtstart, dtend pairs.
	   * On the calendar resource in $rsc is then and export_vfreebusy() done for these
	   * periods and this is return as result.
	   *
	   * @ref $supportedFields    determines the VFREEBUSY fields that will be used considered
	   * @todo this needs to be implemented ....
	   *
	   * @param  VFREEBUSY $vfbelt   VFREEBUSY object (horde_iCalendar_vfreebusy) with a 
	   * requestperiods for freebusy info from our bocalupdate resource in $rsc
	   * @return VFREEBUSY|False an vfreebusy element that delivers freebusy settings for
	   * the requested periods in the $vfbelt argument.
	   * On error: False
	   */
	  function import_vfreebusy(&$vfreebusy)
	  {
		return false;
//-----------------------------------------to be recoded start ---------------
		// auxiliary horde_iCalendar object
		$hIcal = $this->hi; 

		$veImportFields =& $this->supportedFields;

//		error_log('veImportFields::'. print_r($veImportFields,true));

		$eidOk   = false;	// returning false, if file contains no components
		$user_id = $GLOBALS['egw_info']['user']['account_id'];

		  // HANDLE ONLY VEVENTS HERE
		if(!is_a($vevent, 'Horde_iCalendar_vevent')){
		  error_log('import_vevent called for non vevent type');
		  return false;
		}
//		  $event = array('participants' => array());
		$event = array('title' => 'Untitled');
		$alarms = array();
		unset($owner_id);
		$evduration = false;
		$nonegw_participants = array();


		// lets see what other supported veImportFields we can get from the vevent
		foreach($vevent->_attributes as $attr) {
		  $attrval = $GLOBALS['egw']->translation->convert($attr['value'],'UTF-8');

		  // SKIP  UNSUPPORTED VEVENT FIELDS
		  if(!in_array($attr['name'],$veImportFields))
			continue;
//			error_log('cnv field:' . $attr['name'] . ' val:' . $attrval);

		  switch($attr['name']) {

			case 'DTEND':
			  // will be reviewed after all fields are collected
			  $event['end']		= $this->ecu->mke_DDT2utime($attrval);
			  //			  $event['end']		= $attrval;
			  break;

			  // note: DURATION and DTEND are mutually exclusive
			case 'DURATION':
			  // duration after eventstart in secs
			  $evduration = $attrval;
			  break;

			case 'DTSTART':
			  // will be reviewed after all fields are collected
			  $event['start']		= $attrval;
			  break;

			  // when we encounter an new valid cal_address but not yet in egw db
			  // should we import it?
			case 'ATTENDEE':
			  if ($pid = $this->ecu->mke_CAL_ADDRESS2pid($attrval)){
				if( $epartstat = $this->ecu->mke_params2partstat($attr['params'])){
				  $event['participants'][$pid] = $epartstat;
				} elseif ($pid == $event['owner']){
				  $event['participants'][$pid] = 'A';
				} else {
				  $event['participants'][$pid] = 'U';
				}
				// egw unknown participant, add to nonegw_participants list
			  } else {
				$nonegw_participants[] =
				  $this->ecu->mke_ATTENDEE2cneml($attrval,$attr['params']);
			  }
			  break;

			  // make organizer into a accepting participant
			case 'ORGANIZER':	// make him 
			  if ($pid = $this->ecu->mke_CAL_ADDRESS2pid($attrval))
				  $event['participants'][$pid] = 'A';
			      //$event['owner'] = $pid;
			  break;

			default:
			error_log('VEVENT field:' .$attr['name'] .':'
					  . $attrval . 'HAS NO CONVERSION YET');
			}
		  } // end of fields loop
	
		  // now all fields are gathered do some checking and combinations
		  

		  // build endtime from duration if dtend was not set
		  if (!isset($event['end']) && ($evduration !== false)){
			$event['end'] = $this->ecu->mke_DDT2utime($event['start']) + $evduration;
		  } 
		  
		  // a trick for whole day handling or ...??
		  if(date('H:i:s',$event['end']) == '00:00:00')
			$event['end']--;


		  // handle fixed id call (for boical compatibility)
		  // @todo test boical compatibility (esp. with $cal_id>0 case) 
		  if($cal_id > 0)	{
			$event['id'] = $cal_id;
		  }

		  // SORRY THE PARTICPANTS HANDLING OF EGW IS NOT YET CLEAR TO ME (JVL)
		  // so I do the bold solution to add ourself to participants list if we are not on yet
		  if(!isset($event['participants'][$user_id]))
			$event['participants'][$user_id] =  'A';
 // error_log('<< ok <<<<' . 'event read for import=' . print_r($event,true));

		  // -- finally we come to the import into egw ---

		  if (($event['title'] == 'X-DELETE') || ($event['title'] == '_DELETED_')){

			// -------- DELETION --------------------
			//			error_log('delete event=' . print_r($event,true));
			$imp_action = 'DEL-' . $imp_action;
			if(! $cur_eid) {
			  $this->_errorlog_evupd('event', 'ERROR: ' . $imp_action,
									 $user_id, $event, false);
			  return VELT_IMPORT_STATUS_ERROR;

			} else {
			  // event to delete is found readable
			  if($eidOk = $this->rsc->delete($cur_eid)){
				// DELETE OK
				return VELT_IMPORT_STATUS_DELOK;

				// ASSUME Alarms are deleted by egw on delete of the event...
				// otherwise we should use this code:
				//  delete the old alarms
				//foreach($cur_event['alarm'] as $alarmID => $alarmData)	{
				//  $this->delete_alarm($alarmID);
				//}

			  } elseif ($user_id != $cur_owner_id){
				// DELETE BAD  but it wasnt ours anyway so skip it
				if ($this->evdebug)
				  $this->_errorlog_evupd('event', 'SKIPPED: ' . $imp_action . ' (INSUFFICIENT RIGHTS)',
										 $user_id, $event, $cur_event);
				return VELT_IMPORT_STATUS_NOACC;

			  } else {
				// DELETE BAD and it was ours
				$this->_errorlog_evupd('event', 'ERROR: ' . $imp_action . '(** INTERNAL ERROR ? **)', 
									   $user_id, $event, $cur_event);
				return VELT_IMPORT_STATUS_ERROR;
			  }

			}

			  // -------- UPDATE --------------------
		  } elseif ($eidOk = $this->rsc->update($event, TRUE)){
			// UPDATE OKE ,now update alarms

			if(in_array('VALARM',$veImportFields)){
			  // delete the old alarms for the event, note: we could also have used $cur_event
			  // but jus to be sure
			  if(!$updatedEvent = $this->rsc->read($eidOk)){
				error_log('ERROR reading event for Alarm update, will skip update..');
				continue;
			  }

			  // ******** for serious debugging only.. **************
			  //			  if ($this->evdebug){
			  //				$this->_errorlog_evupd('OK: ' . $imp_action, 
			  //									   $user_id, $event, $cur_event);
			  //error_log('event readback dump:' . print_r($updatedEvent,true));
			  //			  }
			  // ******** eof serious debugging only.. **************

			}
			return $eidOk;

			//  ---UPDATE BAD --------
		  } elseif ($user_id != $cur_owner_id){
			// UPDATE BAD, but other ones event, so skip
			  if ($this->evdebug)
				$this->_errorlog_evupd('event', 'SKIPPED: ' . $imp_action . ' (INSUFFICIENT RIGHTS)',
									   $user_id, $event, $cur_event);
			  return VELT_IMPORT_STATUS_NOACC;
				
		  } else {
			// UPDATE BAD and we own it or it was a new one
			$this->_errorlog_evupd('event', 'ERROR: ' . $imp_action . '(** INTERNAL ERROR ? **)', 
								   $user_id, $event, $cur_event);
			return VELT_IMPORT_STATUS_ERROR;

		  }
		  error_log('CODING ERROR: SHOULDNOT GET HERE');

		  return $false;
//-----------------------------------------to be recoded end ---------------
	  }



	  /** Wrapper around import_vevent() with simplified set of call parameters.
	   * @note this function only imports VEvent elements!
	   *
	   * The value of the member variable $reimport_missing_events is used to possibly allow to
	   * reimport of gone events in the calendar.
	   * @deprecated I guess this code can be deleted??/
	   *
	   * The value of the member variable $uid_mapping_import is used to control the set
	   * of iCalendar fields that are imported.
	   * @param  VEVENT $velt    VEVENT object (horde_iCalendar_vevent) 
	   * @param  int $eid  id for a selected event to be updated by the info from $velt
	   *     If left out or set to -1 then uid_mapping_import is switched back to its standard
	   *  setting as found in the member variable $uid_mapping_import.
	   *
	   * @return EventId|errorstring the id of the imported(or updated) ege calendar event.
	   * On error: a string indicating the error: ERROR | NOACC | DELOK | NOELT
	   */
	  function import_velt($velt,$eid=-1)
	  {
//-----------------------------------------to be recoded start ---------------

		$uid_mapping_import_sel = ($eid > 0) ? UMM_FIXEDID : $this->uid_mapping_import;
		  
		return $this->import_vevent($velt,
									$uid_mapping_import_sel,
									$this->reimport_missing_events,
									$eid);
//-----------------------------------------to be recoded end ---------------
	  }





	  /**
	   * Set the list of ical fields that are supported during the next imports and exports.
	   *
	   * The list of iCal fields that should be converted during the
	   * following imports and exports of VFREEBUSY s is set. This is
	   * done according to a given ProductType as mostly set in the
	   * $deviceType field of the egwical_resourcehandler. See there
	   * for a further description.
	   *
	   * In a small lookup table the set of currently supported fields
	   * is searched for and then and then these are set accordingly
	   * in the class member @ref $supportedFields.
	   *
	   * @todo review this code to cater for vfreebusy usage!!
	   *
	   * @note to find the ProductType for a device (like a iCalendar,
	   * browser, a syncml client etc.)  the egwical_resoucehandler
	   * class provides some handy methods, like:
	   * icalendarProdId2devicetype(), httpUserAgent2deviceType() and
	   * product2devicetype()
	   *
	   * @param ProductType $devicetype a string indicating the
	   * communicating client his type of device @return void
	   */
	  function setSupportedFields($devicetype = 'all')
	  {

		// parse ProducType label into productManufacturer and productName
		list($_productManufacturer, $_productName) = explode('/',$devicetype);

		$defaultFields =  array('FREEBUSY','DTSTART','DTEND','URL');
		//								  'ORGANIZER','ATTENDEE');
		  // not: 'TRANSP','ATTENDEE','ORGANIZER','CATEGORIES','URL','CONTACT'
		  
		  switch(strtolower($_productManufacturer))	{
		  case 'nexthaus corporation':
			switch(strtolower($_productName)){
			default:
			  // participants disabled until working correctly
			  // $this->supportedFields = array_merge($defaultFields,array('ATTENDEE'));
			  $this->supportedFields = $defaultFields;
			  break;
			}
			break;
			
			// multisync does not provide anymore information then the manufacturer
			// we suppose multisync with evolution
		  case 'the multisync project':
			switch(strtolower($_productName)) {
			case 'd750i':
			default:
			  $this->supportedFields = $defaultFields;
			  break;
			}
			break;
		  case 'sonyericsson':
			switch(strtolower($_productName)){
			default:
			  $this->supportedFields = $defaultFields;
			  break;
			}
			break;
			
		  case 'synthesis ag':
			switch(strtolower($_productName)){
			default:
			  $this->supportedFields = $defaultFields;
			  break;
			}
			break;
			// used outside of SyncML, eg. by the calendar itself ==> all possible fields
		  case 'file':	
		  case 'all':
			$this->supportedFields =
			  array_merge($defaultFields,
						  array('ORGANIZER','ATTENDEE'));
//			error_log('OKE setsupportedFields (all)to:'. print_r($this->supportedFields,true));
			break;
			
			// the fallback for SyncML
		  default:
			error_log("Client not found: $_productManufacturer $_productName");
			$this->supportedFields = $defaultFields;
			break;
		  }
	  }

//-----------------------------------------to be recoded end ---------------

	}


?>
