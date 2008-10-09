<?php
	/**
	 * @file
	* eGroupWare - compatibility replacement for file infolog/inc/class.vcalinfolog.inc.php
	* to start using the new icalsrv routines.
	*
	* http://www.egroupware.org                                                *
	* @author Jan van Lieshout                                                 *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License.              *
	* @package icalsrv
	* @version 0.9.30-ng-a1 first version for icalsrv napi3
	\**************************************************************************/

	/* THIS CLASS IS JUST HERE FOR BACKWARD COMPATIBILITY  */
	/* in future you should rewrite ical handling using the icalsrv class */

	//require_once EGW_SERVER_ROOT.'/icalsrv/inc/infolog/class.bovtodos.inc.php';

	class vcalinfolog extends infolog_bo
	{
		/** the icalsrv resource handler object that handles  ical import and export for the infolog
		* @var boinfolog_vtodos $infhnd
		*/
		var $infhnd;

		function vcalinfolog()
		{
			bovtodos::bovtodos(); // call superclass constructor
			error_log("warning class: infolog.vcalinfolog call DEPRECATED,"
				. "\nplease rewrite your code to use icalsrv.boinfolog_vtodos class"
				. "\n now temporary code fix used ");
			$this->infhnd =& CreateObject('icalsrv.boinfolog_vtodos');
			$this->infhnd->set_rsc($this);

			if($this->infhnd == false)
			{
				error_log('vcalinfolog constructor: couldnot add boinfolog resource to icalsrv rschnd: FATAL');
				return false;
			}
		}

		// now implement the compatibility methods
		/** Export infolog tasks as an iCalendar string
		*
		* @note -- PART OF  boinfolog.vcalinfolog API COMPATIBILITY INTERFACE -----------
		* @todo check if the return value of exportVcal is still compatible..
		* @param int|array $_taskID id of the task to be exported.
		* @param string $_version version to set for the iCalendar to be exported
		* @return string|boolean string with vCal or false on error
		*/
		function exportVTODO($_taskID, $_version)
		{
			$attribs = array('VERSION' => $_version);
			return $this->calhnd->export_vcal($_taskID,$attribs);
		}

		/**  Convert VTODO components from an iCalendar string into eGW infolog tasks
		* and write these to the eGW infolog rsc as new tasks or changes of existing tasks
		*
		* @note -- PART OF  calendar.boical API COMPATIBILITY INTERFACE -----------
		*
		* @todo check if the return value of importVcal is still compatible..
		* @param string $_vcalData  ical data string to be imported
		* @param int $_taskID fixed id of the eGW task to fill with the VTODO data
		*    when -1 import the VTODO content to new EGW  according to the Uid Matching Mode
		*    set in the handler.
		* @return boolean $ok  false on failure | true on success
		*/
		function importVCal(&$_vcalData, $_taskID=-1)
		{
			return ($this->calhnd->import_vcal($_vcalData) !== false) ? true : false ;
		}

		function setSupportedFields($_productManufacturer='file', $_productName='')
		{
			$devicetype = implode('/',array($_productManufacturer, $_productName));
			return $this->calhnd->setSupportedFields($devicetype);
		}
	}
?>
