<?php
   /**
	* @file
	* eGroupWare - iCalendar VEVENTS conversion, import and export for egw calendar.
	* @author Jan van Lieshout
	* @package Egwical
	*/

   /* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License.              *
	****************************************************************************/


  /** @page pageicalsrvveventfeatures Currently implemented VEVENT handling in icalsrv
   * <PRE>
   * TODO:
   * [+] check multiple ATTENDEE import
   * [+] check multiple ATTENDEE export
   * [+] add and check 'whole day event' support
   * [+] check multiple CATEGORY export
   * [+?]todo: check multiple category import (do they get duplicated?
   * [+]check recur EXPORT stuff
   *    [+] ENDDATE egw has other definition of 'end on' than KO3.5
   *    [+] by DAY, [+]UNTIL [+]INTERVAL
   *        NOTE BYDAY was bugged in boical!!!
   *    [+] more tests RRULE export for by [+]MONTH, [+]YEAR,[+] WEEKLY (probably works?)
   *    [+] EXDATE fields export
   * [ ]check recur IMPORT stuff
   *    [+] by day, recur_interval
   *    [+] RRULE import by month, interval
   *    [+] recur_exception (content gets into event correctly, but..)
   *    [+] COUNT converted (partially) to UNTIL
   *    [+] EDIT/DELETE in non owned calendars (>=NAPI-3.1)
   *    [ ] ADD into non owned calendar (>=NAPI-3.1)
   * [+basic] check EXPORT of VALARMS   (only time, no action selectable)
   * [+basic] check IMPORT of VALARMS     (only time, no action selectable)
   * [+/-] todo: add switch to control import of non egw known attendees
   * [+] X-DELETED import
   * [ ] todo find a nicer way to provide a safe importmode parameter usage (was $cal_id==0)
   * [+] test the usage and conversions of user time and server times and timezones in
   *     exported and imported ical files.
   * </PRE>
   * the todos on a row:
   * @todo check multiple category import (do they get duplicated?
   */

	require_once EGW_SERVER_ROOT.'/calendar/inc/class.calendar_boupdate.inc.php';
    require_once EGW_SERVER_ROOT.'/icalsrv/inc/class.icalsrv_resourcehandler.inc.php';


    /**
	 * Concrete subclass resourcehandler for iCal vevents import and export with a egroupware
	 * bocalupdate calendar resource.
	 *
	 *@section secbocveventssynopsis Synopsis
	 * Some simple examples. Firs we need a couple of egw events and an instance of our
	 * (concrete) calendarresource handler class:
@verbatim
  $boc =& CreateObject('calendar.bocalupdate');

  $ev1 = $boc->read(1233);                    // get two events
  $ev2 = $boc->read(4011);

  $calhnd =& CreateObject('icalsrv.bocalupdate_vevents',$boc);
@endverbatim
     * Now export an event as VEVENT and render it as iCalendar string
@verbatim
   // alternative 1
   $vevent1 = $calhnd->export_vevent($ev1,UMM_ID2UID);
   $vcalstr1 = icalsrv_resourcehandler::render_velt2vcal($vevent1);

   // alternative 2 (generic for all resourcehandlers)
   $calhnd->uid_mapping_export = UMM_ID2UID;
   $vevent1  = $calhnd->export_ncvelt($ev1);
   $vcalstr1 = icalsrv_resourcehandler::render_velt2vcal($vevent1);

   // alternative 3 (via baseclass, without intermediate vevent)
   $vcalstr1 = $calhnd->export_vcal($ev1);
@endverbatim
     * An example for importing the vevents
@verbatim
    // alternative 1 (via the concrete method)
    $vevent1  = ..... a good vevent
    $ev_id1 = $calhnd->import_vevent($vevent1, UMM_UID2ID,1);
    if ($ev_id1 > 0)    {
      echo "imported vevent1 as event with id $ev_id1";
    }

    // alternative 2 (generic for all resourcehandlers)
    $calhnd->uid_mapping_import = UMM_UID2ID;
    $ev_id1  = $calhnd->import_ncvelt($vevent1);
    if ($ev_id1 > 0)    {
      echo "imported vevent1 as event with id $ev_id1";
    }

    // alternative 3 (via base class, easier for multiple vevents)
    $my_vevents = array($vevent1,$event2,..);
    $calhnd->uid_mapping_import = UMM_UID2ID;
    $eids = $calhnd->import_velts($my_vevents);
    echo "we imported" . count($eids) . "vevents";

@endverbatim
    * And finally an example of importing all the vevents from a vcalstring
@verbatim
    // alternative 1
    $vcalstr = .. an vcalendar format string with multiple vevents

    $compvelt  = icalsrv_resourcehandler::parse_vcal2velt($vcalstr);
    if($compvelt === false) exit;

    $eids = $calhnd->import_velts($compvelt);
    if ($eids)
    {
       echo "we imported" . count($eids) . "vevents";
    }

    // alternative 2 (using the baseclass its methods)
   $eids = $calhnd->import_vcal($vcalstr);
   if ($eids)   echo "we imported" . count($eids) . "vevents";

@endverbatim
     * @note Warning: When importing a vevent resulting from parsing a vcalstring
     * or any other VElt for which you do not know if it is a single vevent
     * or possibily a compound element with multiple vevents in it, you should preferable use
	 * the import_velts(0 routine of the baseclass instead of the import_ncvelt()
	 * or the import_vevent() routines. This is because the former can handle compound VElts,
	 * while the latter two cannot!
	 *
	 * @package icalsrv
	 *
	 * @todo add Lars his VERSION=1.0 handling of recur events in here.
	 *
	 * @author Jan van Lieshout <jvl-AT-xs4all.nl> (This version. new api rewrite,
	 * refactoring, and extension).
	 * @author Lars Kneschke <lkneschke@egroupware.org> (parts from boical that are reused here)
	 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de> (parts from boical that are
	 * reused here)
	 * @version 0.9.37-ng-a6 empty summary fields fix (semi)
	 * @date 20060508
	 * @since 0.9.37-ng-a2 removed double charset translation
	 * @since 0.9.36  update does not change owner anymore
	 * @since 0.9.36  first version with NAPI-3.1 (rsc_owner_id parameter)

	 * @since 0.9.30  first version for napi3
	 * license @url  http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
	 */
    class bocalupdate_vevents extends icalsrv_resourcehandler
    {

	  /**
	   * @private
	   * @var boolean
	   * Switch to print extra debugging about imported and exported events to the httpd errorlog
	   * stream.
	   */
	  var $evdebug = false; // true;

	  /** The Bound Egw Resource that we handle
	   * @private
	   * @var bocalupdate $rsc
	   * Registry for the egw resource object (calendar, infolog,..) that will be used
	   * to transport ical elements from and to: The socalled <i>
	   * Bound Resource</i>
	   * This can be set by the constructor or later by set_rsc().
	   */
	  var $rsc = null;



	  /** mapping from iCalendar VEVENT fields to egw calendar fields
	   * @private
	   * @var array $vevent2eventFields
	   * An array containing roughly the mapping from iCalendar
	   * to egw fields. Set by constructor.
	   * example entry (<i>rn</i> stands for "Resourced_Name"):
	   * <PRE>
			'SUMMARY'	=> array('rn' => 'title'),
  	    </PRE>
	   * Here <i>rn</i> stands for "Resourced  Name", to indicate the name of the related
	   * related field in the bound egw resource
	   * @todo integrate this with the  icalsrv base $ical2egw conversion table
	   */
	  var $vevent2eventFields = array();


	  /** Deliver the implemented vevent to event mapping as provided by this class.
	   *
	   * @private
	   *
	   * Produce the array of vevent to event field mappings that this class implements.
	   * These are stored on instantiation in the variable $vevent2eventFields
	   * @return array The provided vevent to event fields mapping.
	   */
	  function _provided_vevent2eventFields()
	  {
		return
		  array(
				'UID'		=> array('rn' => 'uid'),
				'CLASS'		=> array('rn' => 'public'),
				'SUMMARY'	=> array('rn' => 'title'),
				'DESCRIPTION'	=> array('rn' => 'description'),
				'LOCATION'	=> array('rn' => 'location'),
				'DTSTART'	=> array('rn' => 'start'),
				'DTEND'		=> array('rn' => 'end'),
				'DURATION'  => array('rn' => 'end-duration'),
				'ORGANIZER'	=> array('rn' => 'owner'),
				'ATTENDEE'	=> array('rn' => 'participants'),
				'RRULE'     => array('rns' => array('recur_type','recur_interval',
													'recur_data','recur_enddate')),
				'EXDATE'    => array('rn' => 'recur_exception'),
 				'PRIORITY'  => array('rn' => 'priority'),
 				'TRANSP'    => array('rn' => 'non_blocking'),
				'CATEGORIES'=> array('rn' => 'category'),
				'URL'       => array(''),
				'CONTACT'   => array(''),
				'GEO'       => array(''),
				'CREATED'   => array(''),
				'AALARM'     => array('rn' => 'alarms'), // NON RFC2445!!
				'DALARM'     => array('rn' => 'alarms'), // NON RFC2445!!
				'VALARM'     => array('rn' => 'alarms'),
				'VALARM/TRIGGER'     => array('rn' => 'alarms/time')
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
	   * the transport the ical data to and from. This is used to set the supportedFields already.
	   * @note These can also later be set using the setSupportedFields() method.
	   * @param string $rscownid the id of the calendar owner. This is only needed for import
	   * in calendars not owned by the authenticated user. Default (0) the id of the
	   * authenticated user is used.
	   */
	  function bocalupdate_vevents($egwrsc = null, $devicetype='all',$rscownid='0')
	  {
		// call our abstract superclass constructor
		icalsrv_resourcehandler::icalsrv_resourcehandler($egwrsc, $prodid, $rscownid);
		//@todo rewrite supportedFields setting to distribute it over the icalsrv
		// baseclass and the subclasses cleverly
		$this->vevent2eventFields = $this->_provided_vevent2eventFields();
		// add reference to the base table too
		$this->ical2egwComponents['VEVENT'] = $this->vevent2eventFields;
		// default initialization
		$this->setSupportedFields($devicetype);
		$this->set_rsc($egwrsc);

		return true;
	  }


	  /** Set the egw calendar resource  that this class will handle.
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
		if(!is_a($egw_rsc,'calendar_boupdate'))
		  return false;
		$this->rsc = $egw_rsc;
		$this->rsc_vtypes[]= 'vevent';
		return true;
	  }

	  // -------- below only conversion and import/export stuff -----


	  /** Export calendar event from bound bocalupdate resource as VEVENT
	   *
	   * The eGW event in $event is exported to iCalendar VEVENT (of type Horde_iCalendar_vevent)
	   * Note that only the set of supported Fields, as indicated by the $supportedFields
	   * member variable, are exported into the VEVENT.
	   *
	   * The uid field of the generated VEVENT will be filled according to the setting of
	   * the $uid_mapping_export parameter. Either with the event id encoded (ID2UID) or with the
	   * event uid field copied (UID2UID) or with a completey new generated string (NEWUID).
	   * .
	   * For more info see @ref secuidmapping
	   *
	   * @param EventId|EventData $event array data (or id) of the eGW event that will be exported
	   * @param int $uid_mapping_export switch to set the export mode for the uid fields.
	   * Default UMM_ID2UID is used.
	   * @return VEVENT|false  the iCalendar VEVENT object representing the data from the egw
	   * input event. On error: false
	   * @ref $supportedFields determines which fields in the VEVENT will be filled
	   */
 	  function export_vevent(&$event, $uid_mapping_export=ID2UID)
	  {
		// decode the mode
		$euid_export = ($uid_mapping_export == ID2UID) ? false : true;
		// auxiliary horde_iCalendar object
		$hIcal = $this->hi;

		$veExportFields =& $this->supportedFields;

		  if (!is_array($event)){
		  // event was passed as an event id
			$eid = $event;
			if( !$event = $this->rsc->read($eid,null,false,'server')){
			  // server = timestamp in server-time(!)
			  return false;	// no permission to read $cal_id
			}
			// event was passed as an array of fields
		  } else {
			$eid = $event['id'];
			// now read it again to get all fields (including our alarms)
			$event = $this->rsc->read($eid,null,false,'server');
		  }

		  //error_log('>>>>>>>>>>>' .'event to export=' . print_r($event,true));
		  //error_log('event sum:'. $event['title'] . ' start:' .$event['start']);

		  // now create a UID value
		  switch ($uid_mapping_export) {
		  case UMM_UID2UID :
			// put egw uid into VEVENT, to allow client to sync with his uids
			$eventGUID = $event['uid'];
			break;
		  case UMM_NEWUID :
			// this one should not be decodable by mke_guid2id()
			$eventGUID = $this->ecu->mki_v_guid($eid,'newidcal');
		  case UMM_ID2UID :
			// fall through
		  default:
			$eventGUID = $this->ecu->mki_v_guid($eid,'calendar');
		  }

		  $vevent = Horde_iCalendar::newComponent('VEVENT',$this->hi);
		  $parameters = $attributes = array();
		  // to important to let supportedFields decide on this
		  $attributes['UID'] = $eventGUID;

		  foreach($veExportFields as $veFieldName) {

			  switch($veFieldName) {
			  case 'UID':
				// already set
				break;

			  case 'ATTENDEE':
				foreach((array)$event['participants'] as $pid => $partstat) {
					if (!is_numeric($pid) && $pid{0} != 'c') continue;	// neither account nor contact

					list($propval,$propparams) =
						$this->ecu->mki_vp_4ATTENDEE($pid,$partstat,$event['owner']);
					// NOTE: we need to add it already: multiple ATTENDEE fields may be occur
					$this->ecu-> updi_c_addAttribute($vevent,'ATTENDEE',$propval,$propparams);
				}
				break;

			  case 'CLASS':
				$attributes['CLASS'] = $event['public'] ? 'PUBLIC' : 'PRIVATE';
				break;

				// according to rfc, ORGANIZER not used for events in the own calendar
			  case 'ORGANIZER':
				if (!isset($event['participants'][$event['owner']])
					|| count($event['participants']) > 1) {
				  $attributes['ORGANIZER']  = $this->ecu->mki_v_CAL_ADDRESS($event['owner']);
				  $parameters['ORGANIZER']  = $this->ecu->mki_p_CN($event['owner']);
				}
				break;

				// Note; wholeday detection may change the DTEND value later!
// we will do it together with DTSTART
// 			  case 'DTEND':
// 				//				if(date('H:i:s',$event['end']) == '23:59:59')
// 				// $event['end']++;
// 				$attributes[$veFieldName]	= $event['end'];
// 				break;

			  case 'RRULE':
				if ($event['recur_type'] == MCAL_RECUR_NONE)
				  break;		// no recuring event
				$attributes['RRULE'] = $this->ecu->mki_v_RECUR($event['recur_type'],
															  $event['recur_data'],
															  $event['recur_interval'],
															  $event['start'],
															  $event['recur_enddate']);
				break;

			  case 'EXDATE':
				if ($event['recur_exception'])	{
				  list(	$attributes['EXDATE'], $parameters['EXDATE'])=
					$this->ecu->mki_vp_4EXDATE($event['recur_exception'],false);
				}
				break;

			  case 'PRIORITY':
				if (is_numeric($eprio = $event['priority']) && ($eprio >0) )
				  $attributes['PRIORITY'] =  $this->ecu->mki_v_prio($eprio);
				break;

			  case 'TRANSP':
				$attributes['TRANSP'] = $event['non_blocking'] ? 'TRANSPARENT' : 'OPAQUE';
				break;

			  case 'CATEGORIES':
				if ($catids = $event['category']){
				  $catnamescstr = $this->ecu->cats_ids2idnamescstr(explode(',',$catids));
				  $attributes['CATEGORIES'] = $catnamescstr;
				}
				break;

				// @todo find out about AALARM, DALARM, Is this in the RFC !?
			  case 'AALARM':
				foreach($event['alarm'] as $alarmID => $alarmData) {
				  $attributes['AALARM'] = $hIcal->_exportDateTime($alarmData['time']);
				  // lets take only the first alarm
				  break;
				}
				break;

			  case 'DALARM':
			  	if (is_array($event['alarm']))
				foreach($event['alarm'] as $alarmID => $alarmData) {
				  $attributes['DALARM'] = $hIcal->_exportDateTime($alarmData['time']);
				  // lets take only the first alarm
				  break;
				}
				break;

			  case 'VALARM':
			  	if (is_array($event['alarm']))
				foreach($event['alarm'] as $alarmID => $alarmData) {
				  $this->ecu->mki_c_VALARM($alarmData, $vevent,
										  $event['start'], $veExportFields);
				}
				break;

			  case 'STATUS':	// note: custom field in event
				if (! $evstat = strtoupper($event['status']))
				  $evstat = 'CONFIRMED'; //default..
				$attributes['STATUS'] = $evstat;
				break;

				// use daylight savings time patch, for some dates
			  case 'DTSTART':
			  case 'DTEND':
				$efield = $this->vevent2eventFields[$veFieldName]['rn'];
				if ($event[$efield]){
				  $attributes[$veFieldName]	= $this->ecu->st_dst_patch($event[$efield]);
				}
				break;

			  default:
				// only use default for level1 VEVENT fields
				if(strpos($veFieldName, '/') !== false)
				  break;
				// use first related field only for the simple conversion
				$efield = $this->vevent2eventFields[$veFieldName]['rn'];
				if ($event[$efield]) {	// dont write empty fields
					$attributes[$veFieldName]	= $event[$efield];
				}
				break;
			  }

		  } //end foreach

		  // wholeday detector (DTEND =23:59:59 && DTSTART = 00:00)
		  // if detected the times will be exported in VALUE=DATE format
		  if(((date('H:i:s',$event['end']) == '23:59:59') ||
			  (date('H:i:s',$event['end']) == '00:00:00'))
			 && (date('H:i',$event['start'] == '00:00'))){
			$attributes['DTSTART'] =
			  $this->hi->_parseDate(date('Ymd',$event['start']));
			$attributes['DTEND'] =
			  $this->hi->_parseDate(date('Ymd',$event['end']+1));
			$parameters['DTEND']['VALUE'] = 'DATE';
			$parameters['DTSTART']['VALUE'] = 'DATE';
			//	error_log('WHOLE DAY DETECTED');
		  }

		  // handle created and modified field setting
		  $created = $this->ecu->get_TSdbAdd($event['id'],'calendar');
		  if (!$created && !$modified)
			$created = $event['modified'];
		  if ($created)
			$attributes['CREATED'] = $created;
		  if (!$modified)
			$modified = $event['modified'];
		  if ($modified)
			$attributes['LAST-MODIFIED'] = $modified;

		  // add all collected attributes (not yet added) to the vevent
		  foreach($attributes as $aname => $avalue) {
			$this->ecu->updi_c_addAttribute($vevent,
											$aname,
											$avalue,
											$parameters[$aname]);
		  }

		return $vevent; //return VEVENTObj
	  }



	  /** Wrapper around export_vevent() with simplified parameters.
	   *
	   * @note the settings of $this->uid_mapping_export is respected
	   * as to chose the method of UID field generation for the
	   * VEVENT. See @ref secuidmapping in the icalsrv_resourcehandler
	   * documentation.
	   * @param EventId|EventData $eid eventdata or event id in the
	   * bound bocalupdate resource that is to be exported.
	   * @return VEVENT the exported egw event converted to a VEVENT object.
	   * on error False.
	   */
	  function export_ncvelt(&$eid)
	  {
		return $this->export_vevent($eid, $this->uid_mapping_export);
	  }


	  /**
	   * Import a VEVENT as a event into the bound Egw calendar resource
	   *
	   * The ical VEVENT component is converted to an eGW event for the
	   * calendar resource in $rsc and then imported into this eGW calendar resource.
	   *
	   * Depending on the value of $uid_mapping_import, the conversion
	   * will either:
	   * - generate either an eGW event with a completely new id
	   * (<code>UMM_NEWID</code>) and fill that with the data. Or
	   * - search for an existing Egw event based on a id search, with an id search key
	   *   decoded from the VEVENT uid field (<code>UMM_UID2ID</code>) to update with the data. Or
	   * - use the value in the VEVENT uid field a search key for a uid search
	   *  amongst the Egw events (<code>UMM_UID2UID</code>) to use as event to update. Or finally
	   * - update a specific existing Egw event defined by the $cal_id parameter, with the data
	   *  (UMM_FIXEDID).
	   *
	   * Default the mode <code>UMM_UID2ID</code> is used. 	 For more info see @ref secuidmapping
	   *
	   * @ref $supportedFields    determines the VEVENTS that will be used for import
	   *
	   * For importing vevents the calendar of $this->rsc_owner_id is taken. That is the ownership
	   * of the new event is set to that owner. For editing(and deleting) of existing events
	   * the id of the current owner is always (re-) used.
	   *
	   * @param  VEVENT $vevent   VEVENT object (horde_iCalendar_vevent)
	   * @param int $uid_mapping_import uid mapping import mode used. see @ref secuidmapping Default
	   *  UMM_UID2ID.
	   * @param boolean $reimport_missing_events enable the import of previously exported events
	   * that are now gone in egw (probably deleted by someone else) Default false.
	   * @param  int $cal_id the id of the egw event that is to be updated when UMM_FIXEDID mode is
	   * is set for $uid_mapping_import. If set as -1 the uid_mapping_import will switch to
	   * UMM_NEWID mode, if set as 0 the uid_mapping_import will switch to the default
	   * UMM_UID2ID mode.
	   * @return EventId|Errorstring the id of the imported(or updated) egw calendar event.
	   * On error: a string indicating the error: ERROR | NOACC | DELOK | NOELT | BTYPE
	   */
	  function import_vevent(&$vevent, $uid_mapping_import, $reimport_missing_events=false, $cal_id=0)
	  {
		// auxiliary horde_iCalendar object
		$hIcal = $this->hi;

		$veImportFields =& $this->supportedFields;

//		error_log('veImportFields::'. print_r($veImportFields,true));

		$eidOk   = false;	// returning false, if file contains no components
		$user_id = $GLOBALS['egw_info']['user']['account_id'];

		  // HANDLE ONLY VEVENTS HERE
		if(!is_a($vevent, 'Horde_iCalendar_vevent')){
		  error_log('import_vevent called for non vevent type');
		  return BTYPE;
		}
//		  $event = array('participants' => array());
		$event = array('title' => 'Untitled');
		$alarms = array();
		unset($owner_id);
		$evduration = false;
		$nonegw_participants = array();

		// handle UID field always first according to uid_matching algorithm
		$cur_eid      = false;  // current egw event id
		$cur_owner_id = false;  // current egw event owner id
		$cur_event    = false;  // and the whole array of possibly correspond egw event
		// import action description (just for fun and debug) :
		// NEW|NEW-NONUID|NEW-FOR-MISSING
		// DEL-MISSING|DEL-READ|DEL-READ-UID|
		// UPD-MISSING|UPD-READ|UPD-READ-UID
		$imp_action    = 'NEW-NONUID';

		$vuid = null;
		if($uidval = $vevent->getAttribute('UID')){
		  // ad hoc hack: egw hates slashes in a uid so we replace these anyhow with -
		  $vuid = strtr($uidval,'/','-');
		  $event['uid'] = $vuid;
		}

		switch ($uid_mapping_import) {

		case UMM_UID2ID :
		  // try to decode cur_eid from uid
		  if(!$vuid){
			$imp_action = 'NEW';
			break;
		  }
		  if (!($cur_eid = $this->ecu->mke_guid2id($vuid,'calendar'))){
			$imp_action = 'NEW';
			break;
		  }
		  // good cur_eid, so fall through
		case UMM_FIXEDID :
		  if ( $uid_mapping_import == UMM_FIXEDID){
			if($cal_id > 0) {
			  $cur_eid = $cal_id;
			} else {
			  return VELT_IMPORT_STATUS_NOELT;
			}
		  }
		  if ($cur_event = $this->rsc->read($cur_eid)){
			// oke we can read the old event
			$cur_owner_id = $cur_event['owner'];
			$imp_action  = 'UPD-READ';
			$event['id'] = $cur_eid;
			break;
		  }
		  //  a pity couldnot read the corresponding cur_event,
		  if($reimport_missing_events){
			// maybe it was deleted in egw already..
			$imp_action = 'UPD-MISSING';
			unset($event['id']); // import as a new one
			$imp_action = 'NEW';
			break;
		  }
		  // no reimport allowed and event for id not found
		  return VELT_IMPORT_STATUS_NOELT;
		  break;

		case UMM_UID2UID :
		  if ((!empty($vuid)) && ($uidmatch_event = $this->rsc->read($vuid)))	{
			// go do uidmatching, search for a egw event with the vuid as uid field
			// is this uid-search really implemented in bocal ??
			$cur_eid      = $uidmatch_event['id'];
			$cur_owner_id = $uidmatch_event['owner'];
			$imp_action = 'UPD-READ-UID';
			$event['id'] = $cur_eid;
		  }else{
			// uidmatch failed, insert as new
			$imp_action = 'NEW';
		  }
		  break;

		case UMM_NEWID :
		  // fall through
		default:
		  error_log('bocalupdate_vevents.import_vevent(): unknow value:' .
					$uid_mapping_import . ' for uid_mapping_import given.');
		  $imp_action = 'NEW';
		}

		// lets see what other supported veImportFields we can get from the vevent
		foreach($vevent->_attributes as $attr) {
		  $attrval = $attr['value'];

		  // SKIP  UNSUPPORTED VEVENT FIELDS
		  if(!in_array($attr['name'],$veImportFields))
			continue;
//			error_log('cnv field:' . $attr['name'] . ' val:' . $attrval);

		  switch($attr['name']) {
			  // oke again these strange ALARM properties...
		    case 'AALARM':
			case 'DALARM':
			  if (preg_match('/.*Z$/',$attrval,$matches))	{
				$alarmTime = $hIcal->_parseDateTime($attrval);
				$alarms[$alarmTime] = array('time' => $alarmTime);
			  }
			  break;

			case 'CLASS':
			  $event['public']		= (int)(strtolower($attrval) == 'public');
			  break;

			case 'DESCRIPTION':
			  $event['description']	= $attrval;
			  break;

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

			case 'LOCATION':
			  $event['location']	= $attrval;
			  break;

			case 'RRULE':
			  // we may need to find a startdate first so delegate to later
			  // by putting it in event['RECUR']
			  $event['RECUR'] = $attrval;
			  break;
			case 'EXDATE':
			  if (($exdays = $this->ecu->mke_EXDATEpv2udays($attr['params'], $attrval))
				  !== false ){
				foreach ($exdays as $day){
				  $event['recur_exception'][] = $day;
				}
			  }
			  break;

			case 'SUMMARY':
			  $event['title'] = ($attrval) ? $attrval : 'Untitled';
			  break;

			case 'TRANSP':
			  $event['non_blocking'] = $attrval == 'TRANSPARENT';
			  break;
			  // JVL: rewrite!
			case 'PRIORITY':
			  $event['priority'] = $this->ecu->mke_prio($attrval);
			  break;

			case 'CATEGORIES':
			  $catnames = explode(',',$attrval);
			  $catidcstr = $this->ecu->cats_names2idscstr($catnames,$user_id,'calendar');
			  $event['category'] .= (!empty($event['category']))
				? ',' . $catidcstr 	: $catidcstr;
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

			case 'CREATED':		// will be written direct to the event
			  if ($event['modified']) break;
			  // fall through

			case 'LAST-MODIFIED':	// will be written direct to the event
			  $event['modified'] = $attrval;
			  break;

			case 'STATUS':	// note: custom field in event
			  $event['status'] = strtoupper($attrval);
			  break;

			default:
			error_log('VEVENT field:' .$attr['name'] .':'
					  . $attrval . 'HAS NO CONVERSION YET');
			}
		  } // end of fields loop

		  // now all fields are gathered do some checking and combinations

		  // we may have a RECUR value set? Then convert to egw recur def
		  if ($recurval = $event['RECUR']){
			if(!($recur = $this->ecu->mke_RECUR2rar($recurval,$event['start'])) == false){
			  foreach($recur as $rf => $rfval){
				$event[$rf] = $rfval;
			  }
			}
			unset($event['RECUR']);
		  }

		  // build endtime from duration if dtend was not set
		  if (!isset($event['end']) && ($evduration !== false)){
			$event['end'] = $this->ecu->mke_DDT2utime($event['start']) + $evduration;
		  }

		  // a trick for whole day handling or ...??
		  if(date('H:i:s',$event['end']) == '00:00:00')
			$event['end']--;

		  // check vevent for subcomponents (VALARM only at the moment)
		  // maybe some day  do it recursively... (would be better..)
		  foreach($vevent->getComponents() as $valarm) {
			// SKIP anything but a VALARM
			if(!is_a($valarm, 'Horde_iCalendar_valarm'))
			  continue;
			$this->ecu->upde_c_VALARM2alarms($alarms,$valarm,$user_id,$veImportFields);
		  }

		  // AD HOC solution: add nonegw participants to the description
		  // should be controlable by class member switch
		  if (count($nonegw_participants) > 0)
			$this->ecu->upde_nonegwParticipants2description($event['description'],
														   $nonegw_participants);

		  // handle fixed id call (for boical compatibility)
		  // @todo test boical compatibility (esp. with $cal_id>0 case)
		  if(is_numeric($cal_id) && $cal_id > 0)	{
			$event['id'] = $cal_id;
		  }

 // error_log('<< ok <<<<' . 'event read for import=' . print_r($event,true));

		  // handle the ownersettings for virtual calendars
		  // nothing set would do update as current auth user
		  if($cur_owner_id){
			// UPD-READ or UPD-READ-UID
			$event['owner'] = $cur_owner_id;
		  } elseif($this->rsc_owner_id > 0){
			// to accomodate NEW in non owned calendars
			$event['owner'] = $this->rsc_owner_id;
			// just do our own calendar
		  } else {
			$event['owner'] = $user_id;
		  }

		  // SORRY THE PARTICPANTS HANDLING OF EGW IS NOT YET CLEAR TO ME (JVL)
		  // for now the bold solution to add the event owner always to the participants
		  // list if not yet on it
		  if(!isset($event['participants'][$event['owner']]))
			$event['participants'][$event['owner']] =  'A';


		  // -- finally we come to the import into egw ---

		  // NEW RalfBecker Aug 2007
		  // This type of delete handing is no longer necessary - thought it does no harm at the moment.
		  // I think it can be removed in furture, as it's hard to explain to any user
		  // Deleting events is now detected by tracking the requests and submited events.
/*
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
		  } else*/if ($eidOk = $this->rsc->update($event, TRUE)){
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

			  foreach($updatedEvent['alarm'] as $alarmID => $alarmData)	{
				$this->rsc->delete_alarm($alarmID);
			  }
			  //  set new alarms
			  foreach($alarms as $alarm) {
				if(!isset($alarm['offset'])){
				  $alarm['offset'] = $event['start'] - $alarm['time'];
				} elseif (!isset($alarm['time'])){
				  $alarm['time'] = $event['start'] - $alarm['offset'];
				}
				$alarm['owner'] = $user_id;
//				error_log('setting egw alarm as:' . print_r($alarm,true));
				$this->rsc->save_alarm($eidOk, $alarm);
			  }
			}
			return $eidOk;

			//  ---UPDATE BAD --------
		  } elseif ($user_id != $cur_owner_id){

// NEW RalfBecker Aug 2007
// ToDo: check the participant status of the current user and update only it

			// UPDATE BAD, but other ones event, so skip
			  if ($this->evdebug)
				$this->_errorlog_evupd('event', 'SKIPPED: ' . $imp_action . " (INSUFFICIENT RIGHTS: $user_id!=$cur_owner_id)",
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
	  }



	  /** Wrapper around import_vevent() with simplified set of call parameters.
	   * @note this function only imports VEvent elements!
	   *
	   * The value of the member variable $reimport_missing_elements is used to possibly allow to
	   * reimport of gone events in the calendar.
	   *
	   * The value of the member variable $uid_mapping_import is used to control the set
	   * of iCalendar fields that are imported.
	   * @param  VEVENT $ncvelt    VEVENT object (horde_iCalendar_vevent)
	   * @param  int $eid  id for a selected event to be updated by the info from $velt
	   *     If left out or set to -1 then uid_mapping_import is switched back to its standard
	   *  setting as found in the member variable $uid_mapping_import.
	   *
	   * @return EventId|errorstring the id of the imported(or updated) ege calendar event.
	   * On error: a string indicating the error: ERROR | NOACC | DELOK | NOELT
	   */
	  function import_ncvelt(&$ncvelt,$eid=-1)
	  {
		$uid_mapping_import_sel = ($eid > 0) ? UMM_FIXEDID : $this->uid_mapping_import;

		return $this->import_vevent($ncvelt,
									$uid_mapping_import_sel,
									$this->reimport_missing_elements,
									$eid);
	  }

	/**
	 * Delete or reject (for $user) an event specified by the id
	 *
	 * @param int $id event-id
	 * @param int $user account_id
	 * @return boolean true on success, false otherwise
	 */
	function delete_ncvelt($id,$user)
	{
		if (!($event = $this->rsc->read($id))) return false;

		if ($event['owner'] == $user && $this->rsc->delete($id))
		{
			return true;
		}
		elseif($this->rsc->set_status($id,$user,'R'))
		{
			return true;
		}
		return false;
	}

	  /**
	   * Set the list of ical fields that are supported during the next imports and exports.
	   *
	   * The list of iCal fields that should be converted during the following imports and exports
	   * of VEVENTS is set. This is done according to a given ProductType as mostly set in the
	   * $deviceType field of the icalsrv_resourcehandler. See there for a further description.
	   *
	   * In a small lookup table the set of currently supported fields is searched for and then
	   * and then these are set accordingly in the class member @ref $supportedFields.
	   *
	   * @note to find the ProductType for a device (like a iCalendar, browser, a syncml client etc.)
	   * the icalsrv_resoucehandler class provides some handy methods, like:
	   *  icalendarProdId2devicetype(), httpUserAgent2deviceType() and product2devicetype()
	   *
	   * @param ProductType $devicetype a string indicating the communicating client his type of device
	   * @return void
	   */
	  function setSupportedFields($devicetype = 'all')
	  {
		// parse ProducType label into productManufacturer and productName
		list($_productManufacturer, $_productName) = explode('/',$devicetype);

		$defaultFields =  array('CLASS','SUMMARY','DESCRIPTION','LOCATION','DTSTART',
								  'DTEND','RRULE','EXDATE','PRIORITY');
		  // not: 'TRANSP','ATTENDEE','ORGANIZER','CATEGORIES','URL','CONTACT'

		  switch(strtolower($_productManufacturer))	{
		  case 'nexthaus corporation':
		  case 'nexthaus corp':
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
		  case 'sony ericsson':
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
						  array('ATTENDEE','ORGANIZER','TRANSP','CATEGORIES',
								'DURATION','VALARM','VALARM/TRIGGER'));
//			error_log('OKE setsupportedFields (all)to:'. print_r($this->supportedFields,true));
			break;

			// the fallback for SyncML
		  default:
			error_log("Client not found: $_productManufacturer $_productName");
			$this->supportedFields = $defaultFields;
			break;
		  }
	  }


	}


?>
