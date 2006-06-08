<?php
  /**
   * @file
   * class that extends virtual calendars with methods to import and
   * export ical data
   * @author Jan van Lieshout 
   * @package icalsrv
   * Id$
   */
   /* ------------------------------------------------------------------------ *
   * This library is free software; you can redistribute it and/or modify it  *
   * under the terms of the GNU Lesser General Public License as published by *
   * the Free Software Foundation; either version 2.1 of the License,         *
   * or any later version.                                                    *
   * This library is distributed in the hope that it will be useful, but      *
   * WITHOUT ANY WARRANTY; without even the implied warranty of               *
   * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
   * See the GNU Lesser General Public License for more details.              *
   * You should have received a copy of the GNU Lesser General Public License *
   * along with this library; if not, write to the Free Software Foundation,  *
   * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
   */

  // needed for UMM constants
  require_once EGW_SERVER_ROOT . '/egwical/inc/class.egwical_resourcehandler.inc.php';
  require_once EGW_SERVER_ROOT . '/icalsrv/inc/class.virtual_calendar.inc.php';

  /**
   * Ical data handling for Virtual Calendars
   *
   * This class extends a virtual_calendar with methods to get updated
   * by a iCalendar data (import) or export its contents as iCalendar
   * data (export).
   *
   * For its current implementation this class relies heavily
   * egwical_resourcehandlers provided by the package egwical.
   *
   * Basic extra operations supported on virtual calendars by this class are:
   * - export the data as Vcal string
   * - import an Vcal string of calendar information into the virtual calendar
   * - import calendar data in a ical Element (VElt)
   * - export all calendar data as a single VCALENDAR Element (VElt of
   * type vcalendar)
   * @uses egwical_resourcehandler
   *
   * @author jvl
   * @version 0.9.36 first version adapted to NAPI-3.1
   * @date 20060410
   */ 

   class icalvircal extends virtual_calendar
   {
	 var $ivdebug =false;

	 /** The devicetype of the kind of client that is currently exporting or importing
	  *
	  * This is needed to determine what fields are imported and exported from and to
	  * the icaldata: most devices only support a selected set of VEVENT or VTODO fields.
	  *
	  * @var string $device_type
	  * @note this should be derived from the requesting http agent info
	  */
	 var $deviceType = 'all';


	 /** Switch that determines how uid fields are used for import
	  *
	  * @var int $uid_mapping_import
	  * According to the value, on import, the uid field of a vcalendar element will be
	  * determine how the search for a matching egw element is done. The choices are:
	  * - no search for a related egw element id is done, Just a new element is added to the
	  *   bound egw resource (<code>UMM_NEWID</code>) or  
	  * - a related egw element is searched for based on a egw id decoded from the uid
	  *   field of the ical element(<code>UMM_ID2UID</code> Note: <b>Default situation!</b>). 
	  *   .
	  *   This requires of course that at some earlier (exportd) moment an actual
	  *   egw id was encoded  in this uid field.
	  * - a related egw element is searched for based on the full value of the uid field of the
	  *   ical element by searching trough the uid fields of the egw elements
	  *   (<code>UMM_UID2UID</code>
	  *   Note: <b>Strongly discouraged!</b>). 
	  *   .
	  *   This requires that the egw resource does support correct and unique manipulation
	  *   (storage etc.)   of the egw element uid fields!
	  * For more info see @ref secuidmapping
	  */
	 var $uid_mapping_import = UMM_UID2ID;
	 

	 /** Switch that determines the way uid fields are generated at export
	  *
	  * @var int $uid_mapping_export
	  * According to the value, on export, a uid will be generated:
	  * - completely unrelated to the related egw element id (<code>UMM_NEWUID</code>) or  
	  * - with the related egw element id  encoded (<code>UMM_ID2UID</code>
	      Default situation!) or  
	  * - directly copied from the uid field of the related egw element
	  *   (<code>UMM_UID2UID</code> Strongly discouraged!).
	  * For more info see @ref secuidmappping
	  */
	 var $uid_mapping_export = UMM_ID2UID;


	 /** Switch to allow reimport of gone egw elements
	  * @var boolean $reimport_missing_elements
	  * Switch that determines if events not anymore in egw are allowed to be reimported
	  *
	  * Default this is on
	  */
	 var $reimport_missing_elements = true;



	 /** Constructor
	  *
	  */
// 	 function icalvircal()
// 	 {
//
// 	 }


	 /** Export all data as a iCalendar formatted string
	  *
	  * All resources found in the vircal ($_caldef member) are queried according
	  * to the queries setting in there. The resulting egw elements are then
	  * converted according to the resourcehandler settings and their results are
	  * collected and returned as a compound iCalendar string.
	  *
	  * @param array $attribs optional hash with global iCalendar
	  * attributes settings. These attributes will passed through to
	  * the egwical_resourcehandler::render_velt2vcal() routine to
	  * possibly override the standard attributes as provided in
	  * egwical_resourcehandler::_provided_vcalendar2egwAttributes
	  * @return VcalStr a iCalendar formatted string  corresponding
	  * to the VElt data converted from egw elements referred to by
	  * all the defined queries in the virtual calendar
	  * On error: false
	  */
	  function export_vcal($attribs = null)
	  {
		$vcb =& CreateObject('egwical.icalvcb');

		foreach ($this->_caldef['rscs'] as $rsc_class => $rh_def){
		  $rsc =& CreateObject($rsc_class);
		  if (! is_object($rsc)){
			error_log('icalvircal.export_vcal: couldnot create rsc:' . $rsc_class);
			return false;
		  }
		  if($rh_def['qmeth'] == 'search'){
			if($this->ivdebug)
			  error_log('icalvircal.export_vcal-search:'. print_r($rh_def['qarg'],true));
			$ids = $rsc->search($rh_def['qarg']);
		  }else{
			error_log('icalvircal.export_vcal: no good search method found');
			return false;
//			$smeth = $rh_def['meth'] . '(' . $rh_def['qarg'] . ')';
//			$ids = eval($smeth);
		  }
		  // handle nothing found case
		  if(!is_array($ids)){
			   if($ids){
				 $ids = array($ids);
			   }else{
				 $ids = array();
			   }
		  }

		  if($this->ivdebug)
			  error_log("\n icalvircal.export: rsc:". $rsc_class . ' '. count($ids)
					  . " elms to export[");
		  //error_log('element-ids:[' . print_r($ids, true) . ']');
		  // hndnarg4 is the special for vfreebusy and ...
		  $hndarg4 = (!empty($rh_def['hndarg4'])) ? $rh_def['hndarg4'] : null;
		  $rschnd =& CreateObject($rh_def['hnd'],null,$this->deviceType, null, $hndarg4);
		  if (! is_object($rschnd)){
			error_log('icalvircal.export_vcal: couldnot create rschnd:' . $rh_def['hnd']);
			return false;
		  }

		  $rschnd->uid_mapping_export =  $this->uid_mapping_export;
		  $rschnd->set_rsc($rsc);
		  $rschnd->deviceType = $this->deviceType;
		  if(!empty($rh_def['owner_id']))
			$rschnd->set_rsc_owner_id($rh_def['owner_id']);

		  if(($velts =& $rschnd->export_velts($ids)) === False)
			return false;

		  if($this->ivdebug)
			error_log("\n icalvircal.export: rschnd:". $rh_def['hnd'] . ' '
					  . count($velts) . " elms exported");

		  $vcb->add_velts($velts);
		}

		if($this->ivdebug)
		  error_log("\n icalvircal.export: all rscs export ". $vcb->count() . " elms");

		return $vcb->render_vcal($attribs);
		
	  }



	  /**
	   * Import all suited elements  from an iCalendar string into the
	   * various bound Egw resources of the virtual calendar.
	   *
	   * This import routine parses the Vcal string and then tries to
	   * import import each of the components (VElts like VEVENT, VTODO) into an
	   * appropiate egw resource (calendar, infolog etc. instance) as
	   * determined by the virtual calendar resource handling
	   * settings. Thereby respecting the access rights of the grants
	   * settings in the virtual calendar.
	   * Import can mean here: add as new, update,  or delete an existing
	   * egw data element (events, task, ..).
	   *
	   *
	   * @param VcalStr $vcal a iCalendar formatted string with one or
	   * more VElts.
	   * @return RscUpdateTable|false a hash with for each resource
	   * (by classname) a list of ids of updated elements in that resource 
	   * On error: false
	   */
	  function import_vcal(&$vcal)
	  {
		// I1: parse $putData using the egwical_resourcehandler ical parser
		// and add results in buffer 
		$vcalelm =& egwical_resourcehandler::parse_vcal2velt($vcal);
		if(!$vcalelm){
		  error_log('icalvircal.import_vcal: error parsing iCal data');
		  return false;
		}
		if($this->ivdebug)
		  error_log("\n icalvircal.import_vcal: parsed iCalendar data:"
					. count($vcalelm->_components) . " components found");
		
		//now try to import the velts into all resources set in the caldef
		$rscupdtab = array();
		foreach ($this->_caldef['rscs'] as $rsc_class => $rh_def){

		  $rsc =& CreateObject($rsc_class);
		  if (! is_object($rsc)){
			error_log('icalvircal.import_vcal: couldnot create rsc:' . $rsc_class);
			return false;
		  }

		  // hndnarg4 is the special for vfreebusy and ...
		  $hndarg4 = (!empty($rh_def['hndarg4'])) ? $rh_def['hndarg4'] : null;
		  $rschnd =& CreateObject($rh_def['hnd'],null,$this->deviceType, null, $hndarg4);
		  if (! is_object($rschnd)){
			error_log('icalvircal.import_vcal: couldnot create rschnd:' . $rh_def['hnd']);
			return false;
		  }

		  if($this->ivdebug > 2)
			error_log('icalvircal.import_vcal: uid_mapping_import=' . $this->uid_mapping_import);

		  $rschnd->uid_mapping_import =  $this->uid_mapping_import;
		  $rschnd->reimport_missing_elements =  $this->reimport_missing_elements;
		  $rschnd->set_rsc($rsc);
		  $rschnd->deviceType = $this->deviceType;
		  if(!empty($rh_def['owner_id']))
			$rschnd->set_rsc_owner_id($rh_def['owner_id']);

		  if(($rids  =& $rschnd->import_velts($vcalelm)) === False)
			return false;

		  if($this->ivdebug)
			error_log("\n icalvircal.import_vcal rschnd:". $rh_def['hnd']
					  . ' ' . count($rids) . " elms imported");
		  
		  $rscupdtab[$rsc_class] = & $rids;
		}

		// dummy return value
		return $rscupdtab;
	  }

	 
   }


?>