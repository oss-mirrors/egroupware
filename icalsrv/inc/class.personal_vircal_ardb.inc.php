<?php
  /**
   * @file personal_vircal_ardb
   * class that provides an array storage for virtual calendars
   *
   * $Id$
   * @author Jan van Lieshout                                                *
   * @package icalsrv
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

  require_once EGW_SERVER_ROOT . '/icalsrv/inc/class.vircal_ardb.inc.php';

  /**
   * Singleton class that defines some Personal Virtual Calendars as array storage
   *
   * This class is probably just a temporary solution to store some fixed prefabbed
   * virtual_calendar  definition in a repository.
   *
   *  @section secprovcalspersonal Calendars Provided
   * By this class the following personal calendars are currently provided:
   * - /tasks.ics
   * - /calendar.ics
   * - /default.ics
   * - /freebusy.ifb
   *
   * @author jvl
   * @version 0.9.30-a3 second version
   * @date 20060323
   */ 

   class personal_vircal_ardb extends vircal_ardb
   {

	 /** Constructor, overwrites superclass constructor
	  * A initialisation of all the $calendars member is done by calling
	  * the method rebuild_calendars()
	  * @param int $user_id the user for which the virtual calendars are created
	  */
	 function personal_vircal_ardb($user_id = null)
	 {
#	   parent::vircal_ardb();

	   if (is_numeric($user_id)){
		 $this->rebuild_calendars($user_id);
		 $this->user_id = $user_id;
	   } else {
		 $this->calendars = array();
	   }
	   
	 }

	 /** The (numerical) egw user id to who these personal calendars belong
	  * This should be set by constructor
	  * @var string $user_id
	  */
	 var $user_id;

	 /** Prototype of a freebusy calendar
	  * @note at the moment only freebusies from egw calendar are used
	  * @var VCalDefAR $_freebusy_proto
	  */
	 var $_freebusy_proto
       = array('lpath' => '_s_calname',
               'version' => 'vc-1.0',
               'description' => 'a proto for a personal freebusy calendar',
               'enabled' => 1,
               'auth'  => ':basic',
               'rscs'  =>
               array('calendar.bocalupdate' =>
                     array(
                           'hnd'   => 'egwical.bocalupdate_vfreebusy',
                           'hndarg3' => array(
                                             'url'=> '_s_calname',
                                             'start' => '_fn_month_start()',
                                             'end'   => '_fn_month_end()'
                                             ),
                           'qmeth' => 'search',
                           'qarg' =>
                           array(
                                 'start' => '_fn_month_start()',
                                 'end'   => '_fn_month_end()',
                                 'enum_recuring' => true,
                                 'daywise'       => false,
                                 'users'         => '_fn_cal_owner_id()',
                                 'date_format'   => 'server'
                                 ),
                           'access' => 'R'
                           )
                     )
               );

	 /** Prototype of a events calendar
	  * @note events are entries from egw calendar
	  * @var VCalDefAR $_events_proto
	  */
     var $_events_proto
       = array('lpath' => '_s_calname',
               'version' => 'vc-1.0',
               'description' => 'a proto for a personal events calendar',
               'enabled' => 1,
               'auth'  => ':basic',
               'rscs'  =>
               array('calendar.bocalupdate' =>
                     array(
                           'hnd'   => 'egwical.bocalupdate_vevents',
                           'qmeth' => 'search',
						   'hndarg3' => null,
                           'qarg' =>
                           array(
                                 'start' => '_fn_month_start()',
                                 'end'   => '_fn_month_end()',
                                 'enum_recuring' => false,
                                 'daywise'       => false,
                                 'users'         => '_fn_cal_owner_id()',
                                 'date_format'   => 'server'
                                 ),
                           'access' => 'RW'
                           )
                     )
               );

	 /** Prototype of a tasks calendar
	  * @note tasks are task entries from egw infolog
	  * @var VCalDefAR $_tasks_proto
	  */
     var $_tasks_proto
       =  array('lpath' =>  '_s_calname',
               'version' => 'vc-1.0',
               'description' => 'a proto for a personal tasks calendar',
               'enabled' => 1,
               'auth'  => ':basic',
               'rscs'  =>
               array('infolog.boinfolog' =>
                     array(
                           'hnd'   => 'egwical.boinfolog_vtodos',
                           'qmeth' => 'search',
                           'qarg' =>
                           array(
                                 'col_filter' =>
                                 array('info_type' => 'task',
                                       'info_status' => '',
                                       'info_responsible' => '_fn_cal_owner_id()',
                                       'info_owner' => '',
                                       ),
                                 'filter' => 'own',
                                 'order' => 'id_parent',
                                 'subs' => true,
                                 'sort' => 'DESC'
                                 ),
                           'access' => 'RW'
                           )
                     )
                );

	 /** Prototype of a calendar.ics: events and tasks calendar
	  * In this calendar a egw bocal is searched for events and an egw
	  * boinfolog resource is used for tasks
	  * Use rebuild_calendars() to initialize it.
	  * @var VCalDefAR $_calendar_proto
	  */
	 var $_calendar_proto = array();


	 /** Initialize the storage in $calendars according to user settings 
	  * Create all the defined standard virtual calendars for the user in $user_id
	  * The calendars defined are:
	  * - /events.ics the events (appointments) from -1 month till +1 year
	  *  - /week/events.ics   the events from the current week
	  * - /tasks.ics
	  * - /default.ics combined info from /events.ics and /tasks.ics
	  * - /freebusy.ifb
	  *
	  * @param int $user_id the user (as id) whose personal virtual calendars will 
	  * are set up.
	  * @return int the number of entries set in $calendars
	  */
	 function rebuild_calendars($user_id)
	 {

	   // first build the combined calendar proto
	   $this->_calendar_proto
		 = array('lpath' => '_s_calname',
				 'version' => 'vc-1.0',
				 'description' => 'a proto for a personal combined events and tasks calendar',
				 'enabled' => 1,
				 'auth'  => ':basic',
				 'rscs'  =>
				 $this->_combine_vcdef_rscsdef(array($this->_events_proto,
													  $this->_tasks_proto))
				 );

	   if (! $username = $GLOBALS['egw']->accounts->id2name($user_id)){
		 error_log('personal_vircal_ardb.rebuild_calendars: couldnot find username for id'
				   . $user_id);
		 return false;
	   }
	   
	   $rwrule_stdperiod_stduser = array('start' => '_fn_months_away(-2)',
										 'end' => '_fn_months_away(12)',
										 'users' => $user_id
										 );
	   $rwrule_opentasks_stduser = array('info_status' => '',
										 'info_responsible' => $user_id
										 );

	   // events from 1 month back till 12 months after today
	   $this->calendars['/events.ics']
		 =& $this->_cprw_vcdef($this->_events_proto,
							   $username . '/events.ics',
							   "events for $username from 1 month back till 1 year from now", 
							   $rwrule_stdperiod_stduser
							   );

	   // tasks from 1 month back till 12 months after today
	   $this->calendars['/tasks.ics']
		 =& $this->_cprw_vcdef($this->_tasks_proto,
							   $username . '/tasks.ics',
							   "open tasks for $username", 
							   $rwrule_opentasks_stduser
							   );


	   //  /default.ics (combines events and tasks
	   $this->calendars['/default.ics']
		 =& $this->_cprw_vcdef($this->_calendar_proto,
							  $username . '/default.ics',
						 "events and tasks for $username from 1 month back till 1 year from now", 
							   array_merge($rwrule_stdperiod_stduser,
										   $rwrule_opentasks_stduser)
							   );

		 // freebusy from 1 month back till 12 months after today
	   $this->calendars['/freebusy.ifb']
		 =& $this->_cprw_vcdef($this->_freebusy_proto,
							  $username . '/freebusy.ifb',
							 "freebusy times for $username , based on events calendar  from 1 month back till 1 year from now", 
							   $rwrule_stdperiod_stduser
							   );

	   // -- now some weekly calendars
	   $rwrule_weekperiod_stduser = array('start' => '_fn_week_start()',
										  'end' =>  '_fn_week_end()',
										 'users' => $user_id
										 );

		 // this weeks events
	   $this->calendars['/week/events.ics']
		 =& $this->_cprw_vcdef($this->_events_proto,
							   $username . '/week/events.ics',
							   "events in this week for $username", 
							   $rwrule_weekperiod_stduser
							   );

// 		 // this weeks tasks
// 	   $this->calendars['/week/tasks.ics']
// 		 =& $this->_cprw_vcdef($this->_tasks_proto,
// 							  $username . '/week/tasks.ics',
// 							 "tasks in this week for $username", 
// 							  '_fn_week_start()', '_fn_week_end()',
// 							  $user_id);

		 // this weeks defaults
	   $this->calendars['/week/default.ics']
		 =& $this->_cprw_vcdef($this->calendars['/default.ics'],
							  $username . '/week/default.ics',
							 "events in this week and open tasks for $username", 
							   $rwrule_weekperiod_stduser
							   );

	   // -- now some monthly calendars
	   $rwrule_month_stduser = array('start' =>'_fn_month_start()',
									 'end' =>  '_fn_month_end()',
									 'users' => $user_id
									 );
		 // this months events
	   $this->calendars['/month/events.ics']
		 =& $this->_cprw_vcdef($this->_events_proto,
							   $username . '/month/events.ics',
							   "events in this month for $username", 
							   $rwrule_month_stduser
							   );

// 		 // this months tasks
// 	   $this->calendars['/month/tasks.ics']
// 		 =& $this->_cprw_vcdef($this->_tasks_proto,
// 							  $username . '/month/tasks.ics',
// 							 "tasks in this month for $username", 
// 							   $rwrule_month_stduser
// 							   );


		 // this weeks defaults
	   $this->calendars['/month/default.ics']
		 =& $this->_cprw_vcdef($this->calendars['/default.ics'],
							   $username . '/month/default.ics',
							   "events and tasks in this month for $username", 
							   $rwrule_month_stduser
							   );
	   
		 // this months freebusy
	   $this->calendars['/month/freebusy.ifb']
		 =& $this->_cprw_vcdef($this->_freebusy_proto,
							  $username . '/month/freebusy.ifb',
							 "freebusy times for $username in this month (based on events calendar)",
							   $rwrule_month_stduser
							   );


	   // some next months calendars
	   $rwrule_nextmonth_stduser = array('start' =>'_fn_month_start(1)',
										 'end' =>  '_fn_month_end(1)',
										 'users' => $user_id
										 );

		 // next months events
	   $this->calendars['/nextmonth/events.ics']
		 =& $this->_cprw_vcdef($this->_events_proto,
							   $username . '/nextmonth/events.ics',
							   "events in next month for $username", 							 
							   $rwrule_nextmonth_stduser
							   );

// 		 // next months tasks
// 	   $this->calendars['/nextmonth/tasks.ics']
// 		 =& $this->_cprw_vcdef($this->_tasks_proto,
// 							  $username . '/nextmonth/tasks.ics',
// 							 "tasks in next month for $username", 							 
// 							  '_fn_month_start(1)', '_fn_month_end(1)',
// 							  $user_id);

		 // next months freebusy
	   $this->calendars['/nextmonth/freebusy.ifb']
		 =& $this->_cprw_vcdef($this->_freebusy_proto,
							  $username . '/nextmonth/freebusy.ifb',
							 "freebusy times for $username in next month (based on events calendar)",
							   $rwrule_nextmonth_stduser
							   );


	   return count($this->calendars);

	 }

	 /** Provide a html listing of all available personal calendars
	  * 
	  * @param int $detail control in how much detail the listing provides:
	  * [0..1) => paths only, [1..2) => paths and description [100..) => dump
	  * @return string a html page with a listing of the calendars and their
	  * description.
	  */
	 function listing($detail=1)
	 {

	   if (! $username = $GLOBALS['egw']->accounts->id2name($this->user_id)){
		 $username = '.....';
	   }

	   $titlemsg = "personal virtual calendars available for $username";
	   $str= "<html>\n<head>\n<title>$titlemsg</title>\n"
		 . "<meta-equiv=\"content-type\" content=\"text/html;\">\n</head>"
		 . "<body><h2>Personal Virtual Calendars defined for</h2>\n"
		 . "</p><h1>&nbsp;&nbsp; $username </h1><dl>";

	   $basepath = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME']; 
	   foreach($this->calendars as $vcname => $vcdef){
		 $str .= "\n<dt><a href=\"" . $basepath . '/'. $vcdef['lpath'] . "\">"
		   . $vcdef['lpath'] . "</a></dt>";
		 if($detail >= 1 && $detail < 100){
		   $str .= "\n<dd>" . $vcdef['description'] . "</dd>";
		 }elseif ($detail >= 100){
		   $str .=  "\n<dd>" . print_r($vcdef, true) . "</dd>";
		 }
	   }
	   $str .= "\n</dl>";
	   $str .= "\n<p/>\nFor a list of available system calendars see <a href=\""
		 . $basepath . "/list.html\">/list.html</a>";
	   $str .= "\n</body></html>";
	   return $str;
	 }




	 /** deep copy-rewrite a personal calendar vcdef with new name, start and end fields
	  * do a recursive copy
	  * @private
	  * @param VCalDefAr $oldcdf original vcdef array
	  * @param string $name name for the new calendar
	  * @param string $desc short description of the new calendar
	  * @param string $rwrules rewrite rules, a hash of keys and new values for the associated
	  * content fields.
	  * @return VCalDefAr new deepcoy with some fields changed of $oldcdf 
	  */
	  function _cprw_vcdef(&$ofield,
						   $name,
						   $desc,
						   &$rwrules)
	 {
	   if (is_array($ofield)){
		 $nfield = array();
		 foreach($ofield as $key => $val){
		   if ($key == 'lpath' || $key == 'url'){
			 $nfield[$key] = $name;
		   }elseif($key == 'description'){
			 $nfield[$key] = $desc;
		   }elseif($nv = $rwrules[$key]){
			 $nfield[$key] = $nv;
		   }else{
			 $nfield[$key] = $this->_cprw_vcdef($val,$name, $desc, $rwrules);
		   }
		 }
		 return $nfield;
	   }

	   return $ofield;
	 }


   }

?>