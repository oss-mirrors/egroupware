<?php
	/**
	* @file
	* eGroupWare - compatibility replacement for file calendar/inc/class.boical.inc.php
	* to start using the new icalsrv routines.
	*
	* http://www.egroupware.org                                                *
	* @author Jan van Lieshout                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License.              *
	* @version 0.9.30 (for use with napi3)
	\**************************************************************************/
  /* Id$ */

	/* THIS CLASS IS JUST HERE FOR BACKWARD COMPATIBILITY  */
	/* in future you should rewrite ical handling using the icalsrv_resourcehandler class */

	//	require_once EGW_SERVER_ROOT.'/icalsrvsrv/inc/calendar/class.bovevents.inc.php';

	class boical extends calendar_boupdate
	{
		/** the icalsrv resource handler object that handles  ical import and export for the calendar.
		* @var bocalupdate_vevents $calhnd 
		*/
		var $calhnd;

		function boical()
		{
			parent::__construct(); // call superclass constructor
			error_log("warning class: calendar.boical call DEPRECATED,"
				. "\nplease rewrite your code to use icalsrv.bocalupdate_vevents class"
				. "\n now temporary code fix used ");
			$this->calhnd =& CreateObject('icalsrv.bocalupdate_vevents');
			$this->calhnd->set_rsc($this);

			if($this->calhnd == false)
			{
				error_log('boical constructor: couldnot add boical resource to icalsrv rschnd: FATAL');
				return false;
			}
		}

		// now implement the compatibility methods

		/**
		* Exports calendar events as an iCalendar string
		*
		* @note -- PART OF  calendar.boical API COMPATIBILITY INTERFACE -----------
		* @todo check if the return value of exportVcal is still compatible..
		* @param int|array $events (array of) cal_id or array of the events to be exported.
		* @param string $method value for method attribute
		* @return string|boolean string with vCal or false on error
		*/
		function &exportVCal($events,$version='2.0',$method='PUBLISH')
		{
			$attribs = array('VERSION' => $version, 'METHOD' => $method);
			// alternative 1
			return $this->calhnd->export_vcal($events,$attribs);

			// alternative 2 should also work
			//		  $vevent = $this->calhnd->export_velts($events);
			//		  return $this->calhnd->render_velt2vcal($vevent,$attribs);
		}

		/** 
		* Convert VEVENT components from an iCalendar string into eGW calendar events
		* and write these to the eGW calendar as new events or changes of existing events
		*
		* @note -- PART OF  calendar.boical API COMPATIBILITY INTERFACE -----------
		*
		* @todo check if the return value of importVcal is still compatible..
		* @bug probably the $cal_id parameter is ignored atm.
		* @param string $_vcalData  ical data string to be imported 
		* @param int $cal_id  id of the eGW event to fill with the VEvent data      
		*    when -1 import the VEvent content to new EGW  events if needed) 
		* @return boolean $ok  false on failure | true on success
		*/
		function importVCal(&$_vcalData, $cal_id=-1)
		{
			// alt1 for multiple events in the vcalData
			return ($this->calhnd->import_vcal($_vcalData) !== false) ? true : false ;

			// alt2 for a single event in the vcalData
			//		$vevent_ical = $this->calhnd->parse_vcal2velt($_vcalData);
			//		return $this->calhnd->import_velt($vevent_ical, $cal_id);
		}

		function setSupportedFields($_productManufacturer='file', $_productName='')
		{
			$devicetype = implode('/',array($_productManufacturer, $_productName));
			return $this->calhnd->setSupportedFields($devicetype);
		}
	}
?>
