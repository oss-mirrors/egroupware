<?php
  /**
   * @file system_vircal_ardb
   * class that provides an array storage for virtual calendars
   *
   *  Id$
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

	require_once( EGW_SERVER_ROOT.'/icalsrv/inc/class.vircal_ardb.inc.php');

  /**
   * Singleton class that defines some System Virtual Calendars as array storage
   *
   * This class is probably just a temporary solution to store some fixed prefabbed
   * virtual_calendar  definition in a repository.
   *
   *  @section secprovcalssystem Calendars Provided
   * By this class the following calendars are provided:
   * - None yet
   * - /uk/holidays.ics (TBI)
   * - /default.ics (ToBeImplemented)
   *   .
   *   A calendar that demands authorization and then provides the /default.ics
   *   of the logged_in user?
   * - /freebusy.ics (TBI)
   *
   * @verion 0.9.37-a1
   * @date 20060427
   * @version 0.9.36-a4 added detection of http or https for list
   * @version 0.9.36-a1 first version adapted for NAPI 3.1
   * @author jvl
   */

	class system_vircal_ardb extends vircal_ardb
	{
		/** Constructor, overwrites superclass constructor
		* A initialisation of all the $calendars member is done by calling
		* the method rebuild_calendars()
		*/
		function system_vircal_ardb()
		{
			parent::vircal_ardb();
			$this->rebuild_calendars();
		}

		/** Initialize the storage in $calendars according to user settings
		* Create all the defined standard virtual system calendars.
		* The calendars defined are:
		* - /default.ics
		* - /freebusy.ics (TBI)
		* @note at the moment the default will only be available for logged-in users
		* @return int the number of entries set in $calendars
		*/
		function rebuild_calendars()
		{
			# if (! $username = $GLOBALS['egw']->accounts->id2name($user_id)){
			#		 //error_log('personal_vircal_ardb.rebuild_calendars: couldnot find username for id'
			#		 //		   . $user_id);
			#		 return 0;
			#	   }

			// calendar /default.ics

			$this->calendars['/events.ics'] = array(
				'lpath' => '/events.ics',
				'version' => 'vc-1.0',
				'description' => 'all events visible for the currently authenticated user'
				. ' for a period of 1 month ago till 12 months later',
				'enabled' => 1,
				'auth'  => ':basic',
				'rscs'  => array(
					'calendar.calendar_boupdate' => array(
						'hnd'   => 'icalsrv.bocalupdate_vevents',
						'hndarg3' => '0',
						'qmeth' => 'search',
						'qarg' => array(
							'start' => '_fn_months_away(-1)',
							'end'   => '_fn_months_away(12)',
							'enum_recuring' => false,
							'daywise'       => false,
							'date_format'   => 'server'
						),
						'access' => 'RW'
					)
				)
			);

			// calendar /tasks.ics
			$this->calendars['/tasks.ics'] = array(
				'lpath' => '/tasks.ics',
				'version' => 'vc-1.0',
				'description' => 'all tasks visible for the currently authenticated user',
				'enabled' => 1,
				'auth'  => ':basic',
				'rscs'  => array(
					'infolog.boinfolog' => array(
						'hnd'   => 'icalsrv.boinfolog_vtodos',
						'hndarg3' => '0',
						'qmeth' => 'search',
						'qarg' => array(
							'col_filter' => array(
								'info_type' => 'task'),
								'filter' => 'my',
								'order' => 'id_parent',
								'subs' => true,
								'sort' => 'DESC'
							),
						'access' => 'RW'
					)
				)
			);

			// calendar /default.ics (combines calendar and tasks
			$this->calendars['/default.ics'] = array(
				'lpath' => '/default.ics',
				'version' => 'vc-1.0',
				'description' => 'all tasks and events for 1 month back till 1 year away'
					. ' visible for the currently authenticated user',
				'enabled' => 1,
				'auth'  => ':basic',
				'rscs'  => $this->_combine_vcdef_rscsdef(array(
					$this->calendars['/events.ics'],
					$this->calendars['/tasks.ics']
				)
			));

			return count($this->calendars);
		}

		/** Provide a html listing of all available system calendars
		*
		* @param int $detail control in how much detail the listing provides:
		* [0..1) => paths only, [1..2) => paths and description [100..) => dump
		* @return string a html page with a listing of the calendars and their
		* description.
		*/
		function listing($detail=1)
		{
			// 	   if (! $username = $GLOBALS['egw']->accounts->id2name($this->user_id)){
			// 		 $username = '.....';
			// 	   }

			$titlemsg = "system virtual calendars available ";
			$str = "<html>\n<head>\n<title>$titlemsg</title>\n"
				. "<meta-equiv=\"content-type\" content=\"text/html;\">\n</head>"
				. "<body><h2>System Virtual Calendars available</h2>\n"
				. "</p><dl>";

			$basepath = $GLOBALS['egw']->link('/icalsrv/icalsrv.php');

			foreach($this->calendars as $vcname => $vcdef)
			{
				// $str .= "\n<dt><a href=\"" . $basepath .  $vcdef['lpath'] . "\">"
				$str .= "\n<dt><a href=\"." .  $vcdef['lpath'] . "\">"
					. $vcdef['lpath'] . "</a></dt>";
				if($detail >= 1 && $detail < 100)
				{
					$str .= "\n<dd>" . $vcdef['description'] . "</dd>";
				}
				elseif ($detail >= 100)
				{
					$str .=  "\n<dd>" . print_r($vcdef, true) . "</dd>";
				}
			}
			$str .= "\n</dl>";
			$str .= "\n<p/>\nFor a list of available personal virtual calendars for a "
				. "<em>user</em>, use the url:"
				. "<p/><strong> $basepath/<em>user_x</em>/list.html</strong>";
			$str .= "\n</body></html>";
			return $str;
		}
	}
