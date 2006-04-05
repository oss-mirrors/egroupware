<?php
  /**
   * @file vircal_ardb
   * class that provides an array storage for virtual calendars
   *
   * $Id$
   * @author Jan van Lieshout                                                *
   * @package icalsrv
   * ------------------------------------------------------------------------ *
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

  //   require_once EGW_SERVER_ROOT.'/phpgwapi/inc/horde/Horde/iCalendar.php';

  /**
   * Virtual Calendar Array Storage Database
   *
   * This class is probably just a temporary solution to store some fixed prefabbed
   * virtual_calendar  definition in a repository.
   * It uses the technique of singleton classes: for each specific virtual calendar
   * a specific subclass of vircal_ardb is defined. When used it should a single
   * instance of it can be initialized and used to build calendars from.
   * 
   * To learn about the definitions of virtual calendar defs see
   * @ref pagvircalarraydef
   *
   * @section secvircalardbsynopsis Synopsis
   *
   * To build a virtual_calendar named <code>/uk/holidays.ics</code> from a typical available
   * vircal_ardb subclass named <code>ukstd_vircal_ardb </code> we could use
   * use the following code:
 @verbatim
 // search the uk_vircal_db class to see if it provides a virtual calendar 
 // called /uk/holidays.ics
 $uk_vc_ardb =& new uk_vircal_ardb();

 if(! $ukholidays_vc_arstore = $uk_vc_ardb->calendars['/uk/holidays.ics'])
 {
     echo 'couldnot find virtual calendar /uk/holidays.ics';
	 exit();
 } 
 
 // create a virtual calendar  and restore from the found array    
 $ukholidays_vircal =& new virtual_calendar;
 $ukholidays_vircal->fromArray($ukholiday_vc_arstore);

@endverbatim

   *  @section secprovcals Calendars Provided
   * By this class the following calendars are provided:
   * - NONE because only subclasses of vircal_ardb define calendars!
   *
   * @author jvl
   * @version 0.9.30-a1 first version
   * @date 20060322 
   */ 

   class vircal_ardb
   {
	 /** The hash that holds all the defined calendar storage arrays
	  * 
	  * This hash stores all the defined calendars as calpathname => array pairs.
	  * At initialization time it is filled using the method rebuild_calendars()
	  * At any time later it can be rebuild to its initial definitions again
	  *  using the same method.
	  * @var array calendars
	  */
	 var $calendars;

	 /** Constructor
	  * A initialisation of all the $calendars member is done by calling
	  * the method rebuild_calendars()
	  */
	 function vircal_ardb()
	 {
	   $this->rebuild_calendars();
	 }

	 /** Initialize the storage in $calendars 
	  * This method should be overwritten in subclasses!
	  * @virtual
	  */
	 function rebuild_calendars()
	 {
	   $this->calendars = array();
	 }



	 /** Combine the resources of multiple vcdef s into a new rscs list
	  * @private
	  * @param array_of_VCaldDefAR $vcdefs a list of vircal definitions 
	  * @return array a list of the combined rscs found in the input vcdefs
	  */
	  function _combine_vcdef_rscsdef($vcdefs)
	  {
		$rscs_def = array();
		foreach($vcdefs as $cd) {
		  foreach($cd['rscs'] as $rsc => $rh_def){
			$rscs_def[$rsc] = $rh_def; 
		  }
		}
		return $rscs_def;
	  }


   }



?>