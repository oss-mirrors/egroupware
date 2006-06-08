<?php
  /**
   * @file
   * class that provides virtual calendars across egw infolog and calendar resources
   * @author Jan van Lieshout                                                *
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

  //   require_once EGW_SERVER_ROOT.'/phpgwapi/inc/horde/Horde/iCalendar.php';

  /**
   * Virtual Calendar across egw resources
   *
   * This class provides structures to define searches on egroupware
   * calendar and infolog like resources and associate access grants/restrictions for
   * these resources. A combination of such a structure will define a
   * set of events, todos found in these resources together with
   * rights to access these and to possibly add new ones to these
   * resources: a so called <i>Virtual Calendar</i>.
   *
   * These virtual calendars can be used to access the data contained
   * in them via for example icalsrv or other egw services.
   * Users can create new Virtual Calendars by defining queries on and
   * access rights restrictions to egw resources. 
   *
   * Basic operations supported on virtual calendars are:
   * - create one
   * - combine two
   * - add resource search definitions and access grants
   * - store to database (to array) and retrieve/build from database (array)
   *
   * @author jvl
   * @since 0.9.36-a1 first version adapted to NAPi-3.1
   * @version 0.9.37-a2 added some php4 compatibility
   * @date 20060427
   */ 

   class virtual_calendar
   {
	 /** The container of the calendar definition.
	  * @private
	  * @var array $_caldef
	  * @note at the moment is the calendar definition stored as in the  arraystorage
	  */
	 var $_caldef = null;

	 /** Authentication demanded
	  * @var string $auth
	  */
	 var $auth = 'NOT SET';

	 /** Password if demanded
	  * @var string $auth
	  */
	 var $pw = null;

	 /** Calendar Owner Id
	  * @var int $_cal-owner_id
	  */
	 var $cal_owner_id = null;



	 // PREP.01 get available virtual calendars in vircals
	 /** Convert encode definitions of the virtual calendar in an array
	  *
	  * See @ref pagvircalarraydef for a description of the array format
	  * @return VCalDefAr the virtual calendar definition as an array
	  */
	 function toArray()
	 {
	   if( is_array($this->_caldef)){
		 return $this->_caldef;
	   }else{
		 return array();
	   }
	 }
	 
	 /** Retrieve definitions of the virtual calendar from an array
	  *  and set object accordingly.
	  * Note that the special directives (starting with _fn_) are tried to be evaluated,
	  * using private member funcs(starting with _fn_)
 	  * See @ref pagvircalarraydef for description of the array format
	  * @param VCalDefAr $vcar the array with all definitions of the virtual calendar.
	  * @return int number of calendars restored. On error false
	  */
	 function fromArray(&$vcar) 
	 {
	   $this->_caldef = $vcar;
//		 error_log('virtual_calendar.fromArray(): starting rewriting caldef'
//				   . print_r($this->_caldef, true));

	   // use the recursive rewriter
	   $this->_caldef = $this->rewrite_directives($this->_caldef, $oke, $this->rwdirtable);
	   if(!$oke) {
		 error_log('virtual_calendar.fromArray(): found problems in rewriting caldef'
				   . print_r($this->_caldef, true));
		 return false;
	   }
	   $this->auth = (!empty( $this->_caldef['auth'])) ? $this->_caldef['auth'] : 'NOT SET';
	   $this->cal_owner_id = (!isset( $this->_caldef['pw'])) ? $this->_caldef['pw'] : null;
	   $this->pw = (!isset( $this->_caldef['pw'])) ? $this->_caldef['pw'] : null;
	   return count($vcar);
	 }


	 /** Table of allowed rewrite directives (tags)
	  * @private
	  * @var array $rwdirtable
	  */
     var $rwdirtable =
	   array(
			 '_fn_week_start',
			 '_fn_week_end',
			 '_fn_month_start',
			 '_fn_month_end',
			 '_fn_months_away', 
			 '_cal_owner_id'
			 );

	 /** Rewrite recursively any array value string starting fn_ into a eval
	  * of a namelike member function.
	  * If a no good eval function is found an error is returned
	  * @param mixed $field field or array to rewrite
	  * @param boolean $oke error status of rewrite (on problems: false)
	  * @param array $rwdirtable table of allowed rewrite directives
	  * @return array rewritten input if recursive rewrite went ok. False on error
	  */
	 function rewrite_directives(&$field, &$oke, &$rwdirtable )
	 {
	   if (!isset($rwdirtable))
		 $rwdirtable = $this->rwdirtable;

	   // string handling
	   if(is_string($field)){
		 if( strpos($field,'_fn_') === false) {
		   $oke = true;
		   return $field;
		 }elseif(preg_match('/^(_fn_[^(]+)\(\S*\)$/',$field,$matches)){
		   // directive found
		   $fndir = $matches[1];
		   $evarg = $matches[0];
		   //		   error_log('fndir===' . $fndir);		 
		   if (in_array($fndir, $rwdirtable)){
			 //enabled directive found
			 $oke = true;
			 return  eval('return $this->'. $field . ';');
		   } else{
			 // non valid directive
			 $oke =false;
			 return 'NON_ENABLED_DIRDEF(' . $field . ')';
		   }
		 }else {
		   $oke =false;
		   return 'BADFORMED_DIRDEF(' . $field . ')';
		 }
	   }

	   if(is_array($field)){
		 // recurse into $field and return inner result
		 $resfield = array();
		 foreach($field as $key => $val){
			 $resfield[$key] =& $this->rewrite_directives($val, $oke, $rwdirtable);
		   if(!$oke)
			 return $resfield;
		 }
		 return $resfield;
	   } 

	   // unknow field type: just copy
	   $oke = true;
	   return $field;
	 }



	 /** @name Directive definitions
	  * @{
	  */


	 /** give start of week, relative to today, in Ymd time
	  * the start is only roughly calculated, mostly a day before the first day
	  * of the week.
	  * @bug week_start currently implemented as  4days before today
	  * @param int $offset offset (+ ro -) from this week (in weeks) 
	  * (so ex. lastmonth -> offset=-1 etc)
	  * @return string Ymd time start of current week
	  */
	 function _fn_week_start($offset = 0)
	 {
	   $a6ms = $this->utimetoa6(time());
	   $a6ms['mday'] -= 4;
	   return date('Ymd',$this->a6toutime($a6ms));
	 }

	 /** give end of week, relative to today, in Ymd time
	  * the end is only roughly calculated, mostly a day or so after the last day
	  * of the week.
	  * @bug week_end currently implemented as  4days before today
	  * @param int $offset offset (+ ro -) from this month (in months) 
	  * (so ex. lastmonth -> offset=-1 etc)
	  * @return string Ymd time end of current month
	  */
	 function _fn_week_end($offset = 0)
	 {
	   $a6ms = $this->utimetoa6(time());
	   $a6ms['mday'] += 4;
	   return date('Ymd',$this->a6toutime($a6ms));

	 }

	 /** give start of month, relative to today, in Ymd time
	  * the start is only roughly calculated, mostly a day before the first day
	  * of the month.
	  * @param int $offset offset (+ ro -) from this month (in months) 
	  * (so ex. lastmonth -> offset=-1 etc)
	  * @return string Ymd time start of current month
	  */
	 function _fn_month_start($offset = 0)
	 {
	   $a6ms = $this->utimetoa6(time());
	   $a6ms['mday'] = 0;
	   $a6ms['month'] += $offset;
	   return date('Ymd',$this->a6toutime($a6ms));
	 }

	 /** give end of month, relative to today, in Ymd time
	  * the end is only roughly calculated, mostly a day or so after the last day
	  * of the month.
	  * @param int $offset offset (+ ro -) from this month (in months) 
	  * (so ex. lastmonth -> offset=-1 etc)
	  * @return string Ymd time end of current month
	  */
	 function _fn_month_end($offset = 0)
	 {
	   $a6ms = $this->utimetoa6(time());
	   $a6ms['mday'] = 32;
	   $a6ms['month'] += $offset;
	   return date('Ymd',$this->a6toutime($a6ms));

	 }

	 /** give n  months away , relative to today, in Ymd time
	  * the date is only roughly calculated... 
	  * @param int $n number of months away from today (+ or -)
	  * @return string date calculated n months from now
	  */
	 function _fn_months_away($n = -1)
	 {
	   $a6ms = $this->utimetoa6(time());
	   $a6ms['month'] += $n;
	   return date('Ymd',$this->a6toutime($a6ms));
	 }

	 /** deliver calendar owner id (int)
	  * This is best taken from a the member var $cal_owner_id
	  * @return int id of the calendar owner if provided else 0
	  */
	 function _fn_cal_owner_id()
	 {
	   return $this->cal_owner_id;

	 }

	 //@}
	 // end of directivedefs group

	 
	 /** @name Auxiliary functions
	  * @{
	  */

	  /** 
 	   * Convert a unix timestamp to a 6 field hash array in the current active timezone
 	   * 
	   *  This is basically alike the php getdate() function but with different field names
	   * 
	   *  The a6date array has fields as in the php getdate() function:
	   * - <code>year</code> four digit year field
	   * - <code>month</code> integer month number
	   * - <code>mday</code> integer day of month number 
	   * - <code>hour</code> integer hour
	   * - <code>minute</code> integer minutes
	   * - <code>second</code> integer seconds
	   * 
	   * @param int  $utime   a unixtimestamp assumed in utc timezone
	   * @return array The date in a6date in local timezone format.
	   */
 	  function utimetoa6($utime)
 	  {
		$t=getdate($utime);
 		return array('hour' => $t['hours'], 'minute' => $t['minutes'],
					 'second' => $t['seconds'],'month' => $t['mon'],
					 'mday' => $t['mday'],'year' => $t['year']);
 	  }


	  /** 
 	   * Convert  a 6 field hash array in the current active timezone to a unix timestamp.
 	   * 
	   *  This is basically the inverseof php getdate() function.
	   * 
	   *  The a6date array has fields as in the php getdate() function:
	   * - <code>year</code> four digit year field
	   * - <code>month</code> integer month number <b> note: mon, not month!! </b> 
	   * - <code>mday</code> integer day of month number 
	   * - <code>hour</code> integer hour
	   * - <code>minute</code> integer minutes
	   * - <code>second</code> integer seconds
	   *
	   * @param array  $a6 The date in a6date in local timezone format.
	   * @return int  a unixtimestamp assumed in utc timezone
	   */
	  function a6toutime ($a6)
	  {
		return mktime($a6['hour'],$a6['minute'],$a6['second'],
					  $a6['month'],$a6['mday'],$a6['year']);
	  }

	  //@}
	  // end of auxfunc group




/**
 * @page pagvircalarraydef Array Encoding of the Virtual Calendar Definitions


The virtual calendar is encoded into an array following the structure:
version VC-0.2

@verbatim

 $vcdef = array('lpath' => $lpcname,
                'auth'  => $auth_needed,
                'description'  => $descriptive_string,
                'enabled' => $enabled,
                'version' => 'vc-0.2',
                'rscs'  => array($rsc_class => array(
                                                     'hnd' => $rschnd,
                                                     'owner_id' => $cal_owner_id,
                                                     'hndarg4' => $hnd_argument4,
                                                     'qmeth' => $qmeth,
                                                     'qarg' => $qarg,
                                                     'access' => $rights,
                                                     ),
                                 ....                  ,
                                 ....                  ,                                    
                                 )
                );
@endverbatim

Below a simple example. Note that in this example three rewritable
directives are used: <code> _fn_month_start()</code> and
<code>_fn_month_end()</code>, that will expand to specific dates on
load time of the definitions. That is when some virtual calendar
<code>$vc</code> does a <code>$vc->fromArray($my_vcdef)</code>

example:

@verbatim

$vcdef =
  array('lpath' => 'demoical/personal.ics',
        'auth'  => ':basic',
        'description'  => 'a calendar with personal events',
        'enabled' => 1,
        'version' => 'vc-0.2',
        'rscs'  =>
        array('calendar.bocalupdate' =>
              array(
                    'hnd'   => 'bocalupdate_vevents',
                    'owner_id' => $user_id,
                    'qmeth' => 'search',
                    'qarg' =>
                    array(
                          'start' => '_fn_month_start()',
                          'end'   => '_fn_month_end()',
                          'enum_recuring' => false,
                          'daywise'       => false,
                          'owner'         => $user_id,
                          'date_format'   => 'server'
                          )
                    'access' => 'RW'
                    )
              'infolog.boinfolog' =>
              array(
                    'hnd'   => 'boinfolog_vtodos',
                    'owner_id' => $user_id,
                    'qmeth' => 'search',
                    'qarg' =>
                    array(
                          'start' => $last_year . "-01-01",
                          'end'   => $next_year . "-12-31",
                          'enum_recuring' => false,
                          'daywise'       => false,
                          'owner'         => '_fn_authuser',
                          'date_format'   => 'server'
                          )
                    'access' => 'R'
                    )
              )
        )
@endverbatim

Just so will every directive <code>'_fn_keyword()'</code> field be
evaluated (via lookup table for security) by a private class function from
the @ref directivedefs group. 

For example: <code>fn_authuser</code> will be executed by:
@verbatim
   _fn_authuser()
   {
        return $GLOBALS['egw_info']['user']['account_id'];
   }
@endverbatim


	 */

   }


?>