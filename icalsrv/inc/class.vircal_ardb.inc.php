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
   * @sec secvircal_ardbsynopsis Synopsis
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





	 //	  function _set_stdvircals_dummy1 (){
// 	//   Get events from 3 years (last year, current, next), rather silly..  */
// 	$last_year = date("Y")-1;
// 	$next_year = date("Y")+1;

// 	// E1.1: get period to be exported for events 
// 	// For productivity this should be user configurable e.g. to be set via some sort of user
// 	// preferences to be set via eGW. (config remote-iCalendar...)
// 	$events_query = array('start' => $last_year . "-01-01",
// 						  'end'   => $next_year . "-12-31",
// 						  'enum_recuring' => false,
// 						  'daywise'       => false,
// 						  'owner'         => $GLOBALS['egw_info']['user']['account_id'],
// 						  // timestamp in server time for boical class
// 						  'date_format'   => 'server'
// 						  );
// 	$todos_query = array('col_filter' => array('type' => 'task'),
// 						 'filter' => 'none',
// 						 'order' => 'id_parent'
// 						 );


   }



/**
 * @page pagvircalarraydef Array Encoding of the Virtual Calendar Definitions


The virtual calendar is encoded into an array following the structure:
version VCAE-v0.2

@verbatim

 $vcdef = array('lpath' => $lpcname,
                'auth'  => $auth_needed,
                'rscs'  => array($rsc_class => array(
                                                     'hnd' => $rschnd,
                                                     'qmeth' => $qmeth,
                                                     'qarg' => $qarg,
                                                     'access' => $rights,
                                                     ),
                                 ....                  ,
                                 ....                  ,                                    
                                 )
                );
@endverbatim

example:

@verbatim

$vcdef =
  array('lpath' => 'demoical/personal.ics',
        'auth'  => ':basic',
        'rscs'  =>
        array('calendar.bocalupdate' =>
              array(
                    'hnd'   => 'bocalupdate_vevents',
                    'qmeth' => 'search',
                    'qarg' =>
                    array(
                          'start' => $last_year . "-01-01",
                          'end'   => $next_year . "-12-31",
                          'enum_recuring' => false,
                          'daywise'       => false,
                          'owner'         => $GLOBALS['egw_info']['user']['account_id'],
                          'date_format'   => 'server'
                          )
                    'access' => 'RW'
                    )
              'infolog.boinfolog' =>
              array(
                    'hnd'   => 'boinfolog_vtodos',
                    'qmeth' => 'search',
                    'qarg' =>
                    array(
                          'start' => $last_year . "-01-01",
                          'end'   => $next_year . "-12-31",
                          'enum_recuring' => false,
                          'daywise'       => false,
                          'owner'         => '%fn_authuser',
                          'date_format'   => 'server'
                          )
                    'access' => 'R'
                    )
              )
        )
@endverbatim

Each <code>'%fn_keyword()'</code> field will be evaluated (via lookup table for security)
by a private class function.

For example: <code>fn_authuser</code> will be executed by:
@verbatim
   _fn_authuser()
   {
        return $GLOBALS['egw_info']['user']['account_id'];
   }
@endverbatim


@todo check how access rights in ACL terms are encoded and handled and use this in
 the virtual_calendar definitions

	 */




?>