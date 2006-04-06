<?php
   /** 
	* @file 
	* eGroupWare - iCalendar VTODOS conversion, import and export for egw infolog
	* @author Jan van Lieshout   
	*/

   /* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License.              *
	**************************************************************************/


  /** @page pageegwicalvtodofeatures Currently implemented VTODO handling in egwical
   * <PRE>
   * TODO:
   * [-] check multiple ATTENDEE import
   * [-] check multiple ATTENDEE export
   * [?] add and check 'whole day event' support
   * [?] check multiple CATEGORY export
   * [?] have multiple CATEGORY export  depent on supportedFields.
   * [?]todo: check multiple category import (do they get duplicated?
   * [-]check recur EXPORT stuff: NOT IMPLEMENTED YET in infolog
   * [? ]check recur IMPORT stuff: NOT IMPLEMENTED YET in infolog
   * [? basic] check EXPORT of VALARMS   (only time, no action selectable)
   * [? basic] check IMPORT of VALARMS     (only time, no action selectable)
   * [?+/-] todo: add switch to control import of non egw known attendees
   * [?] X-DELETED import
   * [?] test the usage and conversions of user time and server times and timezones in
   *     exported and imported ical files.
   * </PRE>
   */

	require_once EGW_SERVER_ROOT.'/calendar/inc/class.bocalupdate.inc.php';
    require_once EGW_SERVER_ROOT.'/egwical/inc/class.egwical_resourcehandler.inc.php';


    /**
	 * Concrete subclass resourcehandler for iCal vtodos import and export with a egroupware
	 * boinfolog infolog resource.
	 *
	 *@section secboivtodossynopsis Synopsis
	 * Some simple examples. Firs we need a couple of egw tasks and an instance of our
	 * (concrete) infologresource handler class:
@verbatim
  $binf =& CreateObject('infolog.boinfolog');

  $tsk1 = $binf->read(1233);                    // get 3 tasks
  $tsk2 = $binf->read(4011);
  $tsk3 = $binf->read(4012);

  $binfhnd =& CreateObject('egwical.boinfolog_vtodos',$binf);
@endverbatim
     * Now export a task as VTODO and render it as iCalendar string
@verbatim
   // alternative 1
   $vtodo1 = $binfhnd->export_vtodo($ev1,UMM_ID2UID);
   $vcalstr1 = egwical_resourcehandler::render_velt2vcal($vtodo1);

   // alternative 2 (generic for all resourcehandlers)
   $binfhnd->uid_mapping_export = UMM_ID2UID;
   $vtodo2  = $binfhnd->export_ncvelt($tsk2);
   $vcalstr2 = egwical_resourcehandler::render_velt2vcal($vtodo2);

   // alternative 3 (via baseclass, without intermediate vtodo)
   $vcalstr3 = $binfhnd->export_vcal($tsk3);
@endverbatim
     * An example for importing the vtodos
@verbatim
    // alternative 1 (via the concrete method)
    $vtodo1  = ..... a good vtodo
    $tsk_id1 = $binfhnd->import_vtodo($vtodo1, UMM_UID2ID,1);
    if ($tsk_id1 > 0)    {
      echo "imported vtodo1 as task with id $tsk_id1";
    }

    // alternative 2 (generic for all resourcehandlers)
    $binfhnd->uid_mapping_import = UMM_UID2ID;
    $tsk_id2  = $binfhnd->import_ncvelt($vtodo2);
    if ($tsk_id2 > 0)    {
      echo "imported vtodo2 as task with id $tsk_id2";
    }

    // alternative 3 (via base class, easier for multiple vtodos)
    $my_vtodos = array($vtodo1, $vtodo2,..);
    $binfhnd->uid_mapping_import = UMM_UID2ID;
    $tids = $binfhnd->import_velts($my_vtodos);
    echo "we imported" . count($tids) . "vtodos";

@endverbatim
    * And finally an example of importing all the vtodos from a vcalstring
@verbatim
    // alternative 1
    $vcalstr = .. an vcalendar format string with multiple vtodos

    $compvelt  = egwical_resourcehandler::parse_vcal2velt($vcalstr);
    if($compvelt === false) exit;

    $tids = $binfhnd->import_velts($compvelt);
    if ($tids)
    {
       echo "we imported" . count($tids) . "tasks";
    }

    // alternative 2 (using the baseclass its methods)
   $tids = $binfhnd->import_vcal($vcalstr);
   if ($tids)   echo "we imported" . count($tids) . "tasks";

@endverbatim
     * @note Warning: When importing a vtodo resulting from parsing a vcalstring
     * or any other VElt for which you do not know if it is a single vtodo
     * or possibily a compound element with multiple vtodos in it, you should preferable use
	 * the import_velts() routine of the baseclass instead of the import_ncvelt()
	 * or the import_vtodo() routines. This is because the former can handle compound VElts,
	 * while the latter two cannot!
	 *
	 * @package egwical
	 *
	 * @author Jan van Lieshout <jvl-AT-xs4all.nl> (This version. new api rewrite,
	 * refactoring, and extension).
	 * @author Lars Kneschke <lkneschke@egroupware.org> (parts from boical that are reused here)
	 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de> (parts from boical that are
	 * reused here)
	 * @version 0.9.34 updated to _ncvelt() routines and with synopsis
	 * @since 0.9.30  first version for napi3

	 * license @url  http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
	 */
    class boinfolog_vtodos extends egwical_resourcehandler
    {
	  
	  /**
	   * @private
	   * @var boolean
	   * Switch to print extra debugging about imported and exported todos to the httpd errorlog
	   * stream.
	   */
	  var $tsdebug = true;

	  /** The Bound Egw (Infolog) Resource  that we handle
	   * @private
	   * @var boinfolog $rsc
	   * Registry for the egw resource object (infolog,..) that will be used
	   * to transport ical elements from and to: The socalled <i>
	   * Bound Resource</i>
	   * This can be set by the constructor or later by set_rsc().
	   */
	  var $rsc = null;



	  /** conversion of infologtask status to vtodo status
	   * @private
	   * @var array $status_task2vtodo 
	   */
	  var $status_task2vtodo =
		array(
			  'offer'       => 'NEEDS-ACTION',
			  'not-started' => 'NEEDS-ACTION',
			  'ongoing'     => 'IN-PROCESS',
			  'done'        => 'COMPLETED',
			  'cancelled'   => 'CANCELLED',
			  'billed'      => 'DONE',
			  'call'        => 'NEEDS-ACTION',
			  'will-call'   => 'IN-PROCESS',
			  );

	  /** conversion of vtodo status to infolog status
	   * @private
	   * @var array 
	   */
	  var $status_vtodo2task =
		array(
			  'NEEDS-ACTION' => 'not-started',
			  'IN-PROCESS'   => 'ongoing',
			  'COMPLETED'    => 'done',
			  'CANCELLED'    => 'cancelled',
			  );
		

	  /** mapping from iCalendar VTODO fields to egw infolog task fields
	   * @private
	   * @var array $vtodo2taskFields
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
	  var $vtodo2taskFields = array();


	  /** Deliver the implemented vtodo to task mapping as provided by this class.
	   *
	   * @private
	   *
	   * @todo find out the correct conversion between info_owner, info_responsible,
	   *       and ORGANIZER  and ATTENDEES for tasks
	   * @todo add support for multiple2single category conversion et vice versa
	   *
	   * @todo find out if infolog supports yet ALARMS, if so do conversion
	   * @todo add routines for resources conversions
	   * @todo add routines for url conversions
	   *
	   * @note when a field is filled with a <code>array('rn' => xxx) </code> this
	   * means that for conversion the value from xxx can simply be copied to the new field.
	   * For other values the conversion is more complex and needs to be handled by 
	   * specific functions.
	   *
	   * Produce the array of vtodo to task field mappings that this class implements.
	   * These are stored on instantiation in the variable $vtodo2taskFields
	   * @return array The provided vtodo to task fields mapping.
	   */
	  function _provided_vtodo2taskFields()
	  {
		static $v2t =
		  array(
				// optional once only 
				'CLASS'		=> array('rn' => 'info_access'),
				'COMPLETED' => array('rn' =>  'info_datecompleted'),
				'CREATED'   => array('fn_TSforAction(modify)'),
				'DESCRIPTION'	=>
				               array('rn' => 'info_des'),
				'DTSTAMP'   => array('fn_time()'),
				'DTSTART'	=> array('rn' => 'info_startdate'),
				'GEO'       => array(''),
				'LAST-MODIFIED'   =>
				               array('rn' => 'info_datemodified'),
				'LOCATION'	=> array('rn' => 'info_location'),
				'ORGANIZER'	=> array('rn' => 'info_owner'),
				'PERCENT-COMPLETE' => array('rn' =>  'info_percent'),
 				'PRIORITY'  => array('rn' => 'info_priority'),
 	 			'RRULE'     => array('fn_rns' => array('recur_type','recur_interval',
 													'recur_data','recur_enddate')),
				'SEQUENCE'  => array('fn_rns' => array('info_id_parent')),
				'STATUS'    => array('fn_rns' => array('info_status'
													)),
				'SUMMARY'	=> array('rn' => 'info_subject'),
				'UID'		=> array('fn_rn' => ''),
//				'URL'       => array('fn_rns' => 'links?'),

				// optional once, but exclusive alternatives
				'DUE'	    => array('fn_rn' => 'info_enddate'),
				'DURATION'  => array('fn_rns' => array('info_startdate','info_enddate')),

				// optional multiple
//				'ATTACH'    => array('fn_rns' => array('?')),
				'ATTENDEE'	=> array('fn_rn' => 'info_responsible'),
				'CATEGORIES'=> array('fn_rn' => 'category'),
				'COMMENT'   => array('fn_rn'),
				'CONTACT'   => array('fn_rns'=> array('info_from','info-addr')),
// 				'EXDATE'    => array('fn_rn' => 'recur_exception'),
// 				'??_EXRULE' => array(),
// 				'??_RSTATUS' => array(),
// 				'??_RDATE' => array(),
// 				'RRULE'     => array(),
// 				'RELATED-TO'=> array('rn' => 'info_id_parent'),
				'RESOURCES' => array('fn_rn' => ''),
 				'TRANSP'    => array('rn' => 'non_blocking'),
// 				'VALARM'     => array('fn_cn' => 'alarms'),
// 				'VALARM/TRIGGER'     => array('fn_crn' => 'alarms/time')
				);

		return $v2t;
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
	   */
	  function boinfolog_vtodos($egwrsc = null, $devicetype='all')
	  {
		// call our abstract superclass constructor
		egwical_resourcehandler::egwical_resourcehandler($egwrsc, $prodid);
		//@todo rewrite supportedFields setting to distribute it over the egwical
		// baseclass and the subclasses cleverly
		$this->vtodo2taskFields = $this->_provided_vtodo2taskFields();
		// add reference to the base table too
		$this->ical2egwComponents['VTODO'] = $this->vtodo2taskFields;
		// default initialization
		$this->setSupportedFields($devicetype);
		$this->set_rsc($egwrsc);

		return true;		
	  }


	  /** Set the egw infolog resource  that this worker will handle.  
	   * 
	   * This worker is only capable of handling  boinfolog task objects, so it should
	   * be of that class. The $egw_rsc is registered in the $rsc variable and the supported
	   * ical element is set to be 'vtodo'. This is registered in $rsc_vtypes.
	   *
	   * @param egwobj $egw_rsc the resource object of type boinfolog that will be
	   * used to transport the ical data to and from.
	   * @return boolean false on error, true if the $egw_rsc was indeed a correct  resource
	   * of the supported type (boinfolog).
	  */
	  function set_rsc($egw_rsc)
	  {
		if(!is_a($egw_rsc,'boinfolog'))
		  return false;
		$this->rsc = $egw_rsc;
		$this->rsc_vtypes[]= 'vtodo';
		return true;
	  }

	  // -------- below only conversion and import/export stuff -----


	  /** Export infolog task from bound boinfolog resource as VTODO
	   *
	   * The eGW task in $task is exported to iCalendar VTODO (of type Horde_iCalendar_vtodo)
	   * Note that only the set of supported Fields, as indicated by the $supportedFields
	   * member variable, are exported into the VTODO.
	   *
	   * The uid field of the generated VTODO will be filled according to the setting of
	   * the $uid_mapping_export parameter. Either with the task id encoded (ID2UID) or with the
	   * task uid field copied (UID2UID) or with a completey new generated string (NEWUID).
	   * .
	   * For more info see @ref secuidmapping 
	   *
	   * The mapping is inspired on rfc 2445 -sec 4.6.2 
	   *  @bug created field is not fetched oke from db.
	   * @param TaskId|TaskData $task id or data of the eGW task that will be exported
	   * @param int $uid_mapping_export switch to set the export mode for the uid fields.
	   * Default UMM_ID2UID is used.
	   * @return VTODO|false  the iCalendar VTODO object representing the data from the egw
	   * input task. On error: false
	   * @ref $supportedFields determines which fields in the VTODO will be filled
	   */
 	  function export_vtodo(&$task, $uid_mapping_export=ID2UID)
	  {

		// decode the mode
		$euid_export = ($uid_mapping_export == ID2UID) ? false : true; 
		// auxiliary horde_iCalendar object
		$hIcal = $this->hi; 

		$veExportFields =& $this->supportedFields;
		
		  if (!is_array($task)){
		  // task was passed as an task id
			$tid = $task;
			if( !$task = $this->rsc->read($tid)){
			  // server = timestamp in server-time(!)
			  return false;	// no permission to read $task_id
			}
			// task was passed as an array of fields
		  } else {
			$tid = $task['info_id'];
			// now read it again to get all fields (including our alarms)
			//			$task = $this->rsc->read($tid);
		  }

#		  error_log('>>>>>>>>>>>' .'task to export=' . print_r($task,true));
#		  error_log('task sum:'. $task['info_subject'] . ' start:' .$task['info_startdate']);

		  // now create a UID value
		  switch ($uid_mapping_export) {
		  case UMM_UID2UID :
			error_log('boinfolog_vtodos.export_vtodo(): UMM_UID2UID NOT SUPPORTED YET: ERROR');
			return false;
			// put egw uid into VTODO, to allow client to sync with his uids
			$taskGUID = $task['uid'];
			break;
		  case UMM_NEWUID :
			// this one should not be decodable by mke_guid2id()
			$taskGUID = $this->ecu->mki_v_guid($tid,'newidcal');
		  case UMM_ID2UID :
			// fall through
		  default:
			$taskGUID = $this->ecu->mki_v_guid($tid,'infolog');
		  }

		  $vtodo = Horde_iCalendar::newComponent('VTODO',$this->hi);
		  $parameters = $attributes = array();
		  // to important to let supportedFields decide on this
		  $attributes['UID'] = $taskGUID;				

		  foreach($veExportFields as $veFieldName) {

			  switch($veFieldName) {
			  case 'UID':
				// already set
				break;

 			  case 'ATTENDEE':
				foreach((array)$task['info_responsible'] as $pid ) {
				  if (!is_numeric($pid))
					continue;
				  $propval  = $this->ecu->mki_v_CAL_ADDRESS($pid);
				  $propparams = $this->ecu->mki_p_CN($pid);
				  // NOTE: we need to add it already: multiple ATTENDEE fields may be occur 
				  $this->ecu->updi_c_addAttribute($vtodo,'ATTENDEE',$propval,$propparams);
				}
				break;

			  case 'CLASS':
				$attributes['CLASS'] = ($task['info_access'] == 'public') ? 'PUBLIC' : 'PRIVATE';
				break;

			  case 'CONTACT':
				if($task['info_from'] || $task['info_addr'])
				  $attributes['CONTACT'] = $task['info_from'] . '\,' . $task['info_addr'];
				break;

				// according to rfc, the organizer of the grouptask
			  case 'ORGANIZER':	
				if ($task['info_owner']) {
				  $attributes['ORGANIZER']  = $this->ecu->mki_v_CAL_ADDRESS($task['info_owner']);
				  $parameters['ORGANIZER']  = $this->ecu->mki_p_CN($task['info_owner']);
				}
				break;

				// Note; wholeday detection may change the DUE value later! 
			  case 'DUE':
				if($task['info_enddate'])
				  $attributes[$veFieldName]	= $this->ecu->st_dst_patch($task['info_enddate']);
				break;

			  case 'COMPLETED':
				if($task['info_datecompleted'])
				  $attributes[$veFieldName]	= $this->ecu->st_dst_patch($task['info_datecompleted']);
				break;

			  case 'PRIORITY':
				if (is_numeric($eprio = $task['priority']) && ($eprio >0) )
				  $attributes['PRIORITY'] =  $this->ecu->mki_v_prio($eprio);
				break;

				// according to rfc this is not a vtodo field!
			  case 'TRANSP':
				$attributes['TRANSP'] = $task['non_blocking'] ? 'TRANSPARENT' : 'OPAQUE';
				break;

			  case 'CATEGORIES':
				if ($catids = $task['info_cat']){ 
				  $catnamescstr = $this->ecu->cats_ids2idnamescstr(explode(',',$catids));
				  $attributes['CATEGORIES'] = $catnamescstr;
				}
				break;

				// for subtasks set the parent
				// egw2vtodo: info_id_parent => pid  -> RELATED-TO:parent_uid
			  case 'RELATED-TO':
				if ($parid = $task['info_id_parent'])
				  $attributes['RELATED-TO'] = $this->ecu->mki_v_guid($parid,'infolog');
				break;

			  case 'STATUS':	// note: custom field in task
				$attributes['STATUS'] = ( $vtodo_stat = $this->status_task2vtodo[$task['status']])
				  ? $vtodo_stat 
				  : 'NEEDS-ACTION';
				if($vtodo_stat == 'COMPLETED'){
				  $attributes['PERCENT-COMPLETE'] ='100';
				}
// 				elseif (ereg('([0-9]+)%',$task['info_status'],$matches)){
// 				  $attributes['PERCENT-COMPLETE'] = $matches[1];
// 				  $attributes['STATUS'] ='IN-PROCESS';
// 				}
				break;

				// use daylight savings time patch, for some dates
			  case 'DTSTART':
				if ($task['info_startdate']){
				  $attributes[$veFieldName]	= $this->ecu->st_dst_patch($task['info_startdate']);
				}
				break;

			  case 'CREATED':
				$created = $this->ecu->get_TSdbAdd($task['info_id'],'infolog_task');
				$attributes[$veFieldName] = ($created) ? $created : $task['info_datemodified'];
//				  $attributes[$veFieldName]	= $this->ecu->st_dst_patch($task['info_startdate']);
				break;

			  case 'LAST-MODIFIED':
//				$lastdbmod = $this->ecu->get_TSdbMod($task['info_id'],'infolog');
//				$attributes[$veFieldName] = $task['info_datemodified'];
				$attributes[$veFieldName]	= $this->ecu->st_dst_patch($task['info_datemodified']);
				break;

			  case 'DTSTAMP':
				$attributes[$veFieldName] = time();
				break;

				// unimplemented by maybe defined conversions
			  case 'RRULE':
			  case 'EXDATE':
			  case 'VALARM':
				break;

			  default:
				// only use default for level1 VTODO fields
				if(strpos($veFieldName, '/') !== false)
				  break;
				// use first related field only for the simple conversion
				$efield = $this->vtodo2taskFields[$veFieldName]['rn'];
				if ($task[$efield]) {	// dont write empty fields
					$attributes[$veFieldName]	= $task[$efield];
				}
				break;
			  }

		  } //end foreach

		  // wholeday detector (DUE =23:59:59 && DTSTART = 00:00)
		  // if detected the times will be exported in VALUE=DATE format
		  if(((date('H:i:s',$task['info_enddate']) == '23:59:59') ||
			  (date('H:i:s',$task['info_enddate']) == '00:00:00')) 
			 && (date('H:i',$task['info_startdate'] == '00:00'))){

			// only replace if supported!
			if($attributes['DTSTART']){
			  $attributes['DTSTART'] =
				$this->hi->_parseDate(date('Ymd',$task['info_startdate']));
			  $parameters['DTSTART']['VALUE'] = 'DATE';
			}
			if($attributes['DUE']){
			  $attributes['DUE'] =
				$this->hi->_parseDate(date('Ymd',$task['info_enddate']+1));
			  $parameters['DUE']['VALUE'] = 'DATE';
			}
			//	error_log('WHOLE DAY DETECTED');
		  }

		  //error_log('attributes={'  . print_r($attributes,true));

		  // add all collected attributes (not yet added) to the vtodo
		  foreach($attributes as $aname => $avalue) {
			$this->ecu->updi_c_addAttribute($vtodo,
											$aname,
											$avalue,
											$parameters[$aname]);
		  }
		
		return $vtodo; //return VTODOObj
	  }



	  /** Wrapper around export_vtodo() with simplified parameters.
	   *
	   * @note the settings of $this->uid_mapping_export is respected
	   * as to chose the method of UID field generation for the
	   * VTODO. See @ref secuidmapping in the egwical_resourcehandler
	   * documentation.
	   * @param TaskId|TaskData $tid id or arraydata of an task in the bound
	   * boinfolog resource that is to be exported.
	   * @return VTODO the exported egw task converted to a VTODO
	   * object.  on error False.
	   */
	  function export_ncvelt(&$tid)
	  {
		return $this->export_vtodo($tid, $this->uid_mapping_export);
	  }


	  /**
	   * Import a VTODO as a task into  the Egw infolog 
	   *
	   * The ical VTODO component is converted to an eGW task for the
	   * infolog resource in $rsc and then imported into this eGW infolog resource.
	   *
	   * Depending on the value of $uid_mapping_import, the conversion
	   * will either:
	   * - generate either an eGW task with a completely new id
	   * (<code>UMM_NEWID</code>) and fill that with the data. Or
	   * - search for an existing Egw task based on a id search, with an id search key
	   *   decoded from the VTODO uid field (<code>UMM_UID2ID</code>) to update with the data. Or
	   * - use the value in the VTODO uid field a search key for a uid search
	   *  amongst the Egw tasks (<code>UMM_UID2UID</code>) to use as task to update. Or finally
	   * - update a specific existing Egw task defined by the $cal_id parameter, with the data
	   *  (UMM_FIXEDID). 
	   *
	   * Default the mode <code>UMM_UID2ID</code> is used. 	 For more info see @ref secuidmapping 
	   *
	   * @ref $supportedFields    determines the VTODOS that will be used for import
	   *
	   * @todo implement ATTENDEE and ORGANIZER import for VTODOS
	   *
	   * @param  VTODO $vtodo   VTODO object (horde_iCalendar_vtodo) 
	   * @param int $uid_mapping_import uid mapping import mode used. see @ref secuidmapping Default
	   *  UMM_UID2ID.
	   * @param boolean $reimport_missing_tasks enable the import of previously exported tasks
	   * that are now gone in egw (probably deleted by someone else) Default false.
	   * @param  int $cal_id the id of the egw task that is to be updated when UMM_FIXEDID mode is
	   * is set for $uid_mapping_import. If set as -1 the uid_mapping_import will switch to
	   * UMM_NEWID mode, if set as 0 the uid_mapping_import will switch to the default
	   * UMM_UID2ID mode.
	   * @return TaskId|Errorstring the id of the imported(or updated) egw infolog task.
	   * On error: a string indicating the error: ERROR | NOACC | DELOK | NOELT | BTYPE
	   */
	  function import_vtodo(&$vtodo, $uid_mapping_import, $reimport_missing_tasks=false, $cal_id=0)
	  {
		// auxiliary horde_iCalendar object
		$hIcal = $this->hi; 

		$veImportFields =& $this->supportedFields;

//		error_log('veImportFields::'. print_r($veImportFields,true));

		$tidOk   = false;	// returning false, if file contains no components
		$user_id = $GLOBALS['egw_info']['user']['account_id'];

		  // HANDLE ONLY VTODOS HERE
		if(!is_a($vtodo, 'Horde_iCalendar_vtodo')){
		  error_log('import_vtodo called for non vtodo type');
		  return BTYPE;
		}

		$task = array('info_subject' => 'Untitled');
		$task['info_responsible'] = array();
#		$alarms = array();
		unset($owner_id);
		$evduration = false;
		$nonegw_participants = array();
		
		// handle UID field always first according to uid_matching algorithm
		$cur_tid      = false;  // current egw task id
		$cur_owner_id = false;  // current egw task owner id
		$cur_task    = false;  // and the whole array of possibly correspond egw task
		// import action description (just for fun and debug) : 
		// NEW|NEW-NONUID|NEW-FOR-MISSING
		// DEL-MISSING|DEL-READ|DEL-READ-UID|
		// UPD-MISSING|UPD-READ|UPD-READ-UID 
		$imp_action    = 'NEW-NONUID';    

		$vuid = null;
		if($uidval = $vtodo->getAttribute('UID')){
		  // ad hoc hack: egw hates slashes in a uid so we replace these anyhow with -
		  $vuid = strtr($uidval,'/','-');
		  // useless because atm task dont support a uid field!!!!
		  $task['uid'] = $vuid;
		}

		switch ($uid_mapping_import) {
		  
		case UMM_UID2ID :
		  // try to decode cur_tid from uid
		  if(!$vuid){
			$imp_action = 'NEW';
			break;
		  }
		  if (!($cur_tid = $this->ecu->mke_guid2id($vuid,'infolog'))){
			$imp_action = 'NEW';
			break;
		  }			
		  // good cur_tid, so fall through
		case UMM_FIXEDID :
		  if ( $uid_mapping_import == UMM_FIXEDID){
			if($cal_id > 0) {
			  $cur_tid = $cal_id;
			} else {
			  return VELT_IMPORT_STATUS_NOELT;
			}
		  }
		  if ($cur_task = $this->rsc->read($cur_tid)){
			// oke we can read the old task
			$cur_owner_id = $cur_task['info_owner'];
			$imp_action  = 'UPD-READ';
			$task['info_id'] = $cur_tid;
			break;
		  }
		  //  a pity couldnot read the corresponding cur_task,
		  if($reimport_missing_tasks){
			// maybe it was deleted in egw already..
			$imp_action = 'UPD-MISSING'; 
			unset($task['info_id']); // import as a new one
			$imp_action = 'NEW';
			break;
		  } 
		  // no reimport allowed and task for id not found
		  return VELT_IMPORT_STATUS_NOELT;
		  break;
		  
		case UMM_UID2UID :
		  if ((!empty($vuid)) && ($uidmatch_task = $this->rsc->read($vuid)))	{
			// go do uidmatching, search for a egw task with the vuid as uid field 
			// is this uid-search really implemented in bocal ??
			$cur_tid      = $uidmatch_task['info_id'];
			$cur_owner_id = $uidmatch_task['info_owner'];
			$imp_action = 'UPD-READ-UID';
			$task['info_id'] = $cur_tid;
		  }else{
			// uidmatch failed, insert as new
			$imp_action = 'NEW';
		  }
		  break;
		  
		case UMM_NEWID :
		  // fall through
		default:
		  error_log('boinfolog_vtodos.import_vtodo(): unknow value:' .
					$uid_mapping_import . ' for uid_mapping_import given.');
		  $imp_action = 'NEW';
		}

		// lets see what other supported veImportFields we can get from the vtodo
		foreach($vtodo->_attributes as $attr) {
		  $attrval = $GLOBALS['egw']->translation->convert($attr['value'],'UTF-8');

		  // SKIP  UNSUPPORTED VTODO FIELDS
		  if(!in_array($attr['name'],$veImportFields))
			continue;
//			error_log('cnv field:' . $attr['name'] . ' val:' . $attrval);

		  switch($attr['name']) {

		  case 'CLASS':
			$task['info_access']	= (strtolower($attrval) == 'public')
			  ? 'public' : 'private';
			break;

		  case 'DUE':
			// will be reviewed after all fields are collected
			$task['info_enddate']		= $this->ecu->mke_DDT2utime($attrval);
			break;

			// note: DURATION and DTEND are mutually exclusive
		  case 'DURATION':
			// duration after taskstart in secs
			$evduration = $attrval;
			break;

		  case 'DTSTART':
			// will be reviewed after all fields are collected
			$task['info_startdate']		= $this->ecu->mke_DDT2utime($attrval);
			break;

// 			case 'TRANSP':
// 			  $task['non_blocking'] = $attrval == 'TRANSPARENT';
// 			  break;

		  case 'PRIORITY':
			$task['info_priority'] = $this->ecu->mke_prio($attrval);
			break;

		  case 'CATEGORIES':
			$catnames = explode(',',$attrval);
			$catidcstr = $this->ecu->cats_names2idscstr($catnames,$user_id,'infolog');
			$task['info_cat'] .= (!empty($task['info_cat']))
			  ? ',' . $catidcstr 	: $catidcstr;
			break;

			// map ATTENDEE to info_responsible list
			// when we encounter an new valid cal_address but not yet in egw db
			// should we import it?
		  case 'ATTENDEE':
			if ($pid = $this->ecu->mke_CAL_ADDRESS2pid($attrval)){
			  $task['info_responsible'][] = $pid;
			  // egw unknown participant, add to nonegw_info_responsible list
			} else {
			  $nonegw_info_responsible[] =
				$this->ecu->mke_ATTENDEE2cneml($attrval,$attr['params']);
			}
			break;

		  case 'LAST-MODIFIED':	// will be written direct to the task
			$task['info_datemodified'] = $attrval;
			break;

		  case 'RELATED-TO':	// will be written direct to the task
			$task['info_id_parent'] = $this->ecu->mke_guid2id($attrval,'infolog');
			break;

		  case 'STATUS':	// note: custom field in task
			$task['status'] = ($task_stat = $this->status_vtodo2task[$attrval])
			  ? $task_stat : 'offer';
			break;

		  case 'COMPLETED':
			$task['info_datecompleted'] = $this->ecu->mke_DDT2utime($attrval);
			break;

			// collection of fields that we dont support on input
		  case 'RRULE':       // not yet implemented in egw
		  case 'EXDATE':      // not yet implemented in egw
		  case 'CREATED':     // in egw database, not in task field
		  case 'ORGANIZER':   // dont influence the ownership in egw by this
			break;

		  default:
			// only use default for level1 VTODO fields
			if(strpos($attr['name'], '/') !== false)
			  break;
			// use first related field only for the simple conversion
			$efield = $this->vtodo2taskFields[$attr['name']]['rn'];
			if($efield){
			  $task[$efield] = $attrval;
			  break;
			}

			error_log('VTODO field:' .$attr['name'] .':'
					  . $attrval . 'HAS NO CONVERSION YET');
		  }

		} // end of fields loop
	
		  // now all fields are gathered do some checking and combinations
		  
		  // build endtime from duration if dtend was not set
		  if (!isset($task['info_enddate']) && ($evduration !== false)){
			$task['info_enddate']
			  = $this->ecu->mke_DDT2utime($task['info_startdate']) + $evduration;
		  } 
		  
#		  // a trick for whole day handling or ...??
#		  if(date('H:i:s',$task['end']) == '00:00:00')
#			$task['info_enddate']--;

		  // handle no status found
		  if(!$task['info_status'])
			$task['info_status'] = 'offer';

		  // hack for infolog bug: reset info_datecompleted for a not-done status value
		  if($task['info_status'] !== 'done')
			$task['info_datecompleted'] = null;

		  // AD HOC solution: add nonegw info_responsible to the description
		  // should be controlable by class member switch
		  if (count($nonegw_info_responsible) > 0)
			$this->ecu->upde_nonegwParticipants2description($task['info_desc'],
														   $nonegw_info_responsible);

		  // handle fixed id call (for boical compatibility)
		  // @todo test boical compatibility (esp. with $cal_id>0 case) 
		  if($cal_id > 0)	{
			$task['info_id'] = $cal_id;
		  }



# error_log('<< ok <<<<' . 'task read for import=' . print_r($task,true));

		  // -- finally we come to the import into egw ---

		  if (($task['info_subject'] == 'X-DELETE')
			  || ($task['info_subject'] == '_DELETED_')){

			// -------- DELETION --------------------
			//			error_log('delete task=' . print_r($task,true));
			$imp_action = 'DEL-' . $imp_action;
			if(! $cur_tid) {
			  $this->_errorlog_evupd('task', 'ERROR: ' . $imp_action,
									 $user_id, $task, false);
			  return VELT_IMPORT_STATUS_ERROR;

			} else {
			  // task to delete is found readable
			  if($tidOk = $this->rsc->delete($cur_tid)){
				// DELETE OK
				return VELT_IMPORT_STATUS_DELOK;

			  } elseif ($user_id != $cur_owner_id){
				// DELETE BAD  but it wasnt ours anyway so skip it
				if ($this->evdebug)
				  $this->_errorlog_evupd('task',
										 'SKIPPED: ' . $imp_action . ' (INSUFFICIENT RIGHTS)',
										 $user_id, $task, $cur_task);
				return VELT_IMPORT_STATUS_NOACC;

			  } else {
				// DELETE BAD and it was ours
				$this->_errorlog_evupd('task',
									   'ERROR: ' . $imp_action . '(** INTERNAL ERROR ? **)', 
									   $user_id, $task, $cur_task);
				return VELT_IMPORT_STATUS_ERROR;
			  }

			}

			  // -------- UPDATE --------------------
		  } elseif ($tidOk = $this->rsc->write($task, true, false)){

 			  // ******** for serious debugging only.. **************
// 			  if ($this->tsdebug){
// 				$this->_errorlog_evupd('task', 'OK: ' . $imp_action, 
// 									   $user_id, $task, $cur_task);
// 				error_log('task readback dump:' . print_r($updatedTask,true));
// 			  }
 			  // ******** eof serious debugging only.. **************

			return $tidOk;

			//  ---UPDATE BAD --------
		  } elseif ($user_id != $cur_owner_id){
			// UPDATE BAD, but other ones task, so skip
			  if ($this->evdebug)
				$this->_errorlog_evupd('task',
									   'SKIPPED: ' . $imp_action . ' (INSUFFICIENT RIGHTS)',
									   $user_id, $task, $cur_task);
			  return VELT_IMPORT_STATUS_NOACC;
				
		  } else {
			// UPDATE BAD and we own it or it was a new one
			$this->_errorlog_evupd('task',
								   'ERROR: ' . $imp_action . '(** INTERNAL ERROR ? **)', 
								   $user_id, $task, $cur_task);
			return VELT_IMPORT_STATUS_ERROR;

		  }
		  error_log('CODING ERROR: SHOULDNOT GET HERE');

		  return $false;
	  }



	  /** Wrapper around import_vtodo() with simplified set of call parameters.
	   * @note this function only imports Vtodo elements!
	   *
	   * The value of the member variable $reimport_missing_elements is used to possibly allow to
	   * reimport of gone tasks in the infolog.
	   * 
	   * The value of the member variable $uid_mapping_import is used to control the set
	   * of iCalendar fields that are imported.
	   * @param  VTODO $ncvelt    VTODO object (horde_iCalendar_vtodo) 
	   * @param  int $tid  id for a selected task to be updated by the info from $velt
	   *     If left out or set to -1 then uid_mapping_import is switched back to its standard
	   *  setting as found in the member variable $uid_mapping_import.
	   *
	   * @return TaskId|errorstring the id of the imported(or updated) ege infolog task.
	   * On error: a string indicating the error: ERROR | NOACC | DELOK | NOELT
	   */
	  function import_ncvelt(&$velt,$tid=-1)
	  {
		$uid_mapping_import_sel = ($tid > 0) ? UMM_FIXEDID : $this->uid_mapping_import;
		  
		return $this->import_vtodo($velt,
								   $uid_mapping_import_sel,
								   $this->reimport_missing_elements,
								   $tid);
	  }





	  /**
	   * Set the list of ical fields that are supported during the next imports and exports.
	   *
	   * The list of iCal fields that should be converted during the following imports and exports
	   * of VTODOS is set. This is done according to a given ProductType as mostly set in the
	   * $deviceType field of the egwical_resourcehandler. See there for a further description.
	   *
	   * In a small lookup table the set of currently supported fields is searched for and then
	   * and then these are set accordingly in the class member @ref $supportedFields.
	   *
	   * @note to find the ProductType for a device (like a iCalendar, browser, a syncml client etc.)
	   * the egwical_resoucehandler class provides some handy methods, like:
	   *  icalendarProdId2devicetype(), httpUserAgent2deviceType() and product2devicetype()
	   *
	   * @param ProductType $devicetype a string indicating the
	   * communicating client his type of device
	   * @return void
	   */
	  function setSupportedFields($devicetype = 'all')
	  {
		// parse ProducType label into productManufacturer and productName
		list($_productManufacturer, $_productName) = explode('/',$devicetype);

		$defaultFields =  array('CLASS','SUMMARY','DESCRIPTION','LOCATION','DTSTART',
								'DSTAMP','CREATED','LAST-MODIFIED',
								'DUE','STATUS','COMPLETE','PRIORITY','PERCENT-COMPLETE');
		// not: 'TRANSP','ATTENDEE','ORGANIZER','CATEGORIES','URL','CONTACT'
		  
		switch(strtolower($_productManufacturer))	{
		case 'nexthaus corporation':
		  switch(strtolower($_productName)){
		  default:
			// info_responsible disabled until working correctly
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
		  // used outside of SyncML, eg. by the infolog itself ==> all possible fields
		case 'file':	
		case 'all':
		  $this->supportedFields =
			array_merge($defaultFields,
						array('ORGANIZER','CATEGORIES','COMPLETED','ATTENDEE','CONTACT',
							  'RELATED-TO'));
		  // error_log('OKE setsupportedFields (all)to:'. print_r($this->supportedFields,true));
		  break;
			
		  // the fallback for SyncML
		default:
		  error_log("boinfolog_vtodos.setSupportedFields - warning: Devicetype not found:"
					. " $devicetype");
		  $this->supportedFields = $defaultFields;
		  break;
		}

		// return true;
	  }


	}


?>
