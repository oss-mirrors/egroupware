<?php
  /**************************************************************************\
  * phpGroupWare - vCalendar Parser                                          *
  * http://www.phpgroupware.org                                              *
  * Written by Mark Peters <skeeter@phpgroupware.org>                        *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	/* $Id$ */

define('FOLD_LENGTH',75);

define('VEVENT',1);
define('VTODO',2);

define('NONE',0);
define('CHAIR',1);
define('REQ_PARTICIPANT',2);
define('OPT_PARTICIPANT',3);
define('NON_PARTICIPANT',4);

define('INDIVIDUAL',1);
define('GROUP',2);
define('RESOURCE',4);
define('ROOM',8);
define('UNKNOWN',16);

define('NEEDS_ACTION',0);
define('ACCEPTED',1);
define('DECLINED',2);
define('TENTATIVE',3);
define('DELEGATED',4);
define('COMPLETED',5);
define('IN_PROCESS',6);

	/*
	 * Class
	 */
define('PRIVATE',0);
define('PUBLIC',1);
define('CONFIDENTIAL',3);

	/*
	 * Transparency
	 */
define('TRANSPARENT',0);
define('OPAQUE',1);

	/*
	 * Frequency
	 */
define('SECONDLY',1);
define('MINUTELY',2);
define('HOURLY',3);
define('DAILY',4);
define('WEEKLY',5);
define('MONTHLY',6);
define('YEARLY',7);

define('FREE',0);
define('BUSY',1);
define('BUSY_UNAVAILABLE',2);
define('BUSY_TENTATIVE',3);

define('THISANDPRIOR',0);
define('THISANDFUTURE',1);

define('START',0);
define('END',1);

define('_8BIT',0);
define('_BASE64',1);

define('OTHER',99);

class class_mailto
{
	var $user;
	var $host;
}

class class_address
{
	var $cn = 'Unknown';
	var $dir;
	var $cutype = INDIVIDUAL;
	var $role = REQ_PARTICIPANT;
	var $rsvp = 0;
	var $mailto;
	var $sent_by;
	var $delegated_from;
	var $delegated_to;
	var $member;
	var $partstat = NEEDS_ACTION;
}

class class_tzprop
{
	var $type;
	var $comment;
	var $dtstart;
	var $rdate;
	var $rrule;
	var $tzname;
	var $tzoffsetfrom;
	var $tzoffsetto;
	var $x_type = Array();
}

class class_timezone
{
	var $type;
	var $tzdata;
	var $last_modified;
	var $tzid;
	var $tzurl;
}

class class_event
{
	var $type;
	var $alarm;
	var $attach;
	var $attendee;
	var $categories;
	var $class;
	var $comment;
	var $contact;
	var $created;
	var $description;
	var $dtend;
	var $dtstamp;
	var $dtstart;
	var $duration;
	var $exdate;
	var $exrule;
	var $geo;
	var $last_modified;
	var $location;
	var $organizer;
	var $priority;
	var $rdate;
	var $recurrence_id;
	var $request_status;
	var $resources;
	var $rrule;
	var $sequence;
	var $status;
	var $summary;
	var $transp;
	var $uid;
	var $url;
	var $x_type = Array();
}

class class_todo
{
	var $type;
	var $alarm;
	var $attach;
	var $attendee;
	var $calscale;
	var $class;
	var $comment;
	var $completed;
	var $created;
	var $description;
	var $dtstamp;
	var $dtstart;
	var $due;
	var $duration;
	var $exdate;
	var $exrule;
	var $geo;
	var $last_modified;
	var $location;
	var $organizer;
	var $percent_coplete;
	var $priority;
	var $rdate;
	var $recurrence_id;
	var $related_to;
	var $request_status;
	var $resources;
	var $rrule;
	var $sequence;
	var $summary;
	var $uid;
	var $url;
	var $x_type = Array();
}

class class_journal
{
	var $type;
	var $class;
	var $created;
	var $description;
	var $dtstamp;
	var $dtstart;
	var $last_modified;
	var $organizer;
	var $recurrence_id;
	var $sequence;
	var $status;
	var $summary;
	var $uid;
	var $url;
	var $attach;
	var $attendee;
	var $categories;
	var $comment;
	var $contact;
	var $exdate;
	var $exrule;
	var $rdate;
	var $related_to;
	var $request_status;
	var $rrule;
	var $x_type = Array();
}

class class_freebusy
{
	var $type;
	var $attandee;
	var $comment;
	var $contact;
	var $dtend;
	var $dtstamp;
	var $dtstart;
	var $duration;
	var $freebusy;
	var $organizer;
	var $request_status;
	var $uid;
	var $url;
	var $x_type = Array();	
}

class class_datetime
{
	var $year;
	var $month;
	var $mday;
	var $hour;
	var $min;
	var $sec;
}

class class_date_time
{
	var $tzid;
	var $value;
	var $allday = False;
	var $x_type = Array();
}

class class_geo
{
	var $lat;
	var $lon;
}

class class_recur
{
	var $byday;
	var $byhour;
	var $byminute;
	var $bymonth;
	var $bymonthday;
	var $bysecond;
	var $bysetpos;
	var $byweekno;
	var $byyearday;
	var $count;
	var $freq;
	var $interval;
	var $until;
	var $wkst;
	var $x_type = Array();
}

class class_text
{
	var $cid;
	var $fmttype;
	var $encoding;
	var $altrep;
	var $language;
	var $value;
	var $x_type = Array();
}

class class_x_type
{
	var $name;
	var $value;
}

class class_alarm
{
	var $action;
	var $trigger;
	var $duration;
	var $repeat;
	var $attach;
	var $x_type = Array();
}

class vCal
{
	var $prodid;
	var $version;
	var $method;
	var $calscale;
	var $event = Array();
	var $todo = Array();
	var $journal = Array();
	var $freebusy = Array();
	var $timezone = Array();
}	

class vCalendar
{
	var $vcal;
	var $line = 0;
	var $event = Array();
	var $todo = Array();
	var $journal = Array();
	var $freebusy = Array();
	var $timezone = Array();
	var $property = Array();
	var $parameter = Array();
	var $debug_str = True;

	/*
	 * Base Functions
	 */

	function vCalendar()
	{
		$this->property = Array(
			'action'		=> Array(
				'type'		=> 'text',
				'to_text'	=> True,
				'valarm'		=> Array(
					'state'		=> 'required',
					'multiples'	=> False
				)
			),
			'attach'		=> Array(
				'type'		=> 'uri',
				'to_text'	=> True,
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'vjournal'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'valarm'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				)
			),
			'attendee'		=> Array(
				'type'		=> 'cal-address',
				'to_text'	=> True,
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'vjournal'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'valarm'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'vfreebusy'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				)
			),			
			'categories'		=> Array(
				'type'		=> 'text',
				'to_text'	=> True,
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vjournal'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				)
			),
			'class'			=> Array(
				'type'		=> 'text',
				'to_text'	=> True,
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vjournal'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				)
			),
			'comment'		=> Array(
				'type'		=> 'text',
				'to_text'	=> True,
				'daylight'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'standard'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'valarm'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'vfreebusy'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'vjournal'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				)
			),
			'completed'		=> Array(
				'type'		=> 'date-time',
				'to_text'	=> False,
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				)
			),
			'contact'		=> Array(
				'type'		=> 'text',
				'to_text'	=> True,
				'vevent'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'vfreebusy'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vjournal'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				)
			),
			'created'		=> Array(
				'type'		=> 'date-time',
				'to_text'	=> False,
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vjournal'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				)
			),
			'description'		=> Array(
				'type'		=> 'text',
				'to_text'	=> True,
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vjournal'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'valarm'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				)
			),
			'dtend'			=> Array(
				'type'		=> 'date-time',
				'to_text'	=> False,
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vfreebusy'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				)
			),
			'dtstamp'		=> Array(
				'type'		=> 'date-time',
				'to_text'	=> False,
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vjournal'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'vfreebusy'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				)
			),			
			'dtstart'		=> Array(
				'type'		=> 'date-time',
				'to_text'	=> False,
				'daylight'	=> Array(
					'state'		=> 'required',
					'multiples'	=> False
				),
				'standard'	=> Array(
					'state'		=> 'required',
					'multiples'	=> False
				),
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vfreebusy'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vjournal'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				)
			),
			'due'			=> Array(
				'type'		=> 'date-time',
				'to_text'	=> False,
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				)
			),
			'duration'		=> Array(
				'type'		=> 'duration',
				'to_text'	=> False,
				'valarm'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vfreebusy'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				)
			),
			'exdate'		=> Array(
				'type'		=> 'date-time',
				'to_text'	=> False,
				'vevent'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'vjournal'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				)
			),
			'exrule'		=> Array(
				'type'		=> 'recur',
				'to_text'	=> False,
				'vevent'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'vjournal'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				)
			),
			'freebusy'		=> Array(
				'type'		=> 'freebusy',
				'to_text'	=> False,
				'vfreebusy'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				)
			),
			'geo'			=> Array(
				'type'		=> 'float',
				'to_text'	=> True,
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				)
			),
			'last_modified'		=> Array(
				'type'		=> 'date-time',
				'to_text'	=> False,
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vjournal'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vtimezone'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				)
			),
			'location'		=> Array(
				'type'		=> 'text',
				'to_text'	=> True,
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				)
			),
			'organizer'		=> Array(
				'type'		=> 'cal-address',
				'to_text'	=> False,
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vjournal'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vfreebusy'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				)
			),			
			'percent_complete'	=> Array(
				'type'		=> 'integer',
				'to_text'	=> False,
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				)
			),
			'priority'		=> Array(
				'type'		=> 'integer',
				'to_text'	=> True,
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				)
			),
			'rdate'			=> Array(
				'type'		=> 'date-time',
				'to_text'	=> False,
				'daylight'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'standard'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'vjournal'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				)
			),
			'recurrence_id'		=> Array(
				'type'		=> 'date-time',
				'to_text'	=> False,
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vjournal'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vtodo'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				)
			),
			'related_to'		=> Array(
				'type'		=> 'text',
				'to_text'	=> False,
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vjournal'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				)
			),
			'request_status'	=> Array(
				'type'		=> 'text',
				'to_text'	=> True,
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vjournal'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'vfreebusy'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				)
			),
			'resources'		=> Array(
				'type'		=> 'text',
				'to_text'	=> False,
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				)
			),
			'rrule'			=> Array(
				'type'		=> 'recur',
				'to_text'	=> False,
				'daylight'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'standard'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'vjournal'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				)
			),
			'sequence'		=> Array(
				'type'		=> 'integer',
				'to_text'	=> True,
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vjournal'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				)
			),
			'status'		=> Array(
				'type'		=> 'text',
				'to_text'	=> True,
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vjournal'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				)
			),
			'summary'		=> Array(
				'type'		=> 'text',
				'to_text'	=> True,
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vtodo'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vjournal'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'valarm'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				)
			),
			'transp'		=> Array(
				'type'		=> 'text',
				'to_text'	=> True,
				'vevent'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				)
			),
			'trigger'		=> Array(
				'type'		=> 'trigger',
				'to_text'	=> True,
				'valarm'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				)
			),
			'tzid'			=> Array(
				'type'		=> 'text',
				'to_text'	=> True,
				'vtimezone'		=> Array(
					'state'		=> 'required',
					'multiples'	=> False
				)
			),
			'tzname'		=> Array(
				'type'		=> 'text',
				'to_text'	=> True,
				'daylight'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				),
				'standard'	=> Array(
					'state'		=> 'optional',
					'multiples'	=> True
				)
			),
			'tzoffsetfrom'		=> Array(
				'type'		=> 'utc-offset',
				'to_text'	=> True,
				'daylight'		=> Array(
					'state'		=> 'required',
					'multiples'	=> False
				),
				'standard'		=> Array(
					'state'		=> 'required',
					'multiples'	=> False
				)
			),
			'tzoffsetto'		=> Array(
				'type'		=> 'utc-offset',
				'to_text'	=> True,
				'daylight'		=> Array(
					'state'		=> 'required',
					'multiples'	=> False
				),
				'standard'		=> Array(
					'state'		=> 'required',
					'multiples'	=> False
				)
			),
			'tzurl'			=> Array(
				'type'		=> 'uri',
				'to_text'	=> True,
				'vtimezone'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				)
			),
			'uid'			=> Array(
				'type'		=> 'text',
				'to_text'	=> True,
				'vevent'	=> Array(
					'state'		=> 'required',
					'multiples'	=> False
				),
				'vfreebusy'		=> Array(
					'state'		=> 'required',
					'multiples'	=> False
				),
				'vjournal'		=> Array(
					'state'		=> 'required',
					'multiples'	=> False
				),
				'vtodo'		=> Array(
					'state'		=> 'required',
					'multiples'	=> False
				)
			),
			'url'			=> Array(
				'type'		=> 'text',
				'to_text'	=> True,
				'vevent'	=> Array(
					'state'		=> 'required',
					'multiples'	=> False
				),
				'vfreebusy'		=> Array(
					'state'		=> 'required',
					'multiples'	=> False
				),
				'vjournal'		=> Array(
					'state'		=> 'optional',
					'multiples'	=> False
				),
				'vtodo'	=> Array(
					'state'		=> 'required',
					'multiples'	=> False
				)
			)
		);
		$this->parameter = Array(
			'altrep'		=> Array(
				'type'		=> 'uri',
				'quoted'		=> True,
				'to_text'	=> True,
				'properties'	=> Array(
					'comment'		=> True,
					'description'	=> True,
					'location'		=> True,
					'resources'		=> True,
					'summary'		=> True,
					'contact'		=> True					
				)
			),
			'byday'		=> Array(
				'type'		=> 'text',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'rrule'		=> True
				)
			),
			'byhour'		=> Array(
				'type'		=> 'string',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'rrule'		=> True
				)
			),
			'byminute'		=> Array(
				'type'		=> 'string',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'rrule'		=> True
				)
			),
			'bymonth'		=> Array(
				'type'		=> 'string',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'rrule'		=> True
				)
			),
			'bymonthday'		=> Array(
				'type'		=> 'string',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'rrule'		=> True
				)
			),
			'bysecond'		=> Array(
				'type'		=> 'string',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'rrule'		=> True
				)
			),
			'bysetpos'		=> Array(
				'type'		=> 'string',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'rrule'		=> True
				)
			),
			'byweekno'		=> Array(
				'type'		=> 'string',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'rrule'		=> True
				)
			),
			'byyearday'		=> Array(
				'type'		=> 'string',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'rrule'		=> True
				)
			),
			'cn'			=> Array(
				'type'		=> 'text',
				'quoted'		=> True,
				'to_text'	=> False,
				'properties'	=> Array(
					'attendee'		=> True,
					'organizer'		=> True					
				)
			),
			'count'		=> Array(
				'type'		=> 'integer',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'rrule'			=> True
				)
			),
			'cu'			=> Array(
				'type'		=> 'function',
				'function'	=> 'switch_cu',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'attendee'		=> True
				)
			),
			'delegated_from'	=> Array(
				'type'		=> 'function',
				'function'	=> 'switch_mailto',
				'quoted'		=> True,
				'to_text'	=> False,
				'properties'	=> Array(
					'attendee'		=> True
				)
			),
			'delegated_to'	=> Array(
				'type'		=> 'function',
				'function'	=> 'switch_mailto',
				'quoted'		=> True,
				'to_text'	=> False,
				'properties'	=> Array(
					'attendee'		=> True
				)
			),
			'dir'			=> Array(
				'type'		=> 'dir',
				'quoted'		=> True,
				'to_text'	=> True,
				'properties'	=> Array(
					'attendee'		=> True,
					'organizer'		=> True
				)
			),
			'enocding'	=> Array(
				'type'		=> 'function',
				'function'	=> 'switch_encoding',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'attach'			=> True
				)
			),
			'fmttype'	=> Array(
				'type'		=> 'text',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'attach'			=> True
				)
			),
			'fbtype'		=> Array(
				'type'		=> 'function',
				'function'	=> 'switch_fbtype',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'attach'			=> True
				)
			),
			'freq'		=> Array(
				'type'		=> 'function',
				'function'	=> 'switch_freq',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'rrule'			=> True
				)
			),
			'interval'		=> Array(
				'type'		=> 'integer',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'rrule'		=> True
				)
			),
			'language'		=> Array(
				'type'		=> 'text',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'categories'	=> True,
					'comment'		=> True,
					'description'	=> True,
					'location'		=> True,
					'resources'		=> True,
					'summary'		=> True,
					'tzname'			=> True,
					'attendee'		=> True,
					'contact'		=> True,
					'organizer'		=> True,
					'x-type'			=> True
				)
			),
			'mailto'		=> Array(
				'type'		=> 'function',
				'function'	=> 'switch_mailto',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'attendee'		=> True,
					'organizer'		=> True
				)
			),
			'member'		=> Array(
				'type'		=> 'function',
				'function'	=> 'switch_mailto',
				'quoted'		=> True,
				'to_text'	=> False,
				'properties'	=> Array(
					'attendee'		=> True
				)
			),
			'partstat'		=> Array(
				'type'		=> 'function',
				'function'	=> 'switch_partstat',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'attendee'		=> True
				)
			),
			'range'		=> Array(
				'type'		=> 'function',
				'function'	=> 'switch_range',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'recurrence_id'	=> True
				)
			),
			'related'		=> Array(
				'type'		=> 'function',
				'function'	=> 'switch_related',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'related_to'	=> True
				)
			),
			'role'		=> Array(
				'type'		=> 'function',
				'function'	=> 'switch_role',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'attendee'		=> True
				)
			),
			'rsvp'		=> Array(
				'type'		=> 'function',
				'function'	=> 'switch_rsvp',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'attendee'		=> True
				)
			),
			'sent_by'		=> Array(
				'type'		=> 'function',
				'function'	=> 'parse_user_host',
				'quoted'		=> True,
				'to_text'	=> False,
				'properties'	=> Array(
					'attendee'		=> True,
					'organizer'		=> True
				)
			),
			'tzid'		=> Array(
				'type'		=> 'text',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'dtend'		=> True,
					'due'			=> True,
					'dtstart'	=> True,
					'recurrence_id'	=> True,
					'exdate'		=> True,
					'rdate'		=> True
				)
			),
			'until'		=> Array(
				'type'		=> 'function',
				'function'	=> 'switch_date',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'rrule'		=> True
				)
			),
			'value'		=> Array(
				'type'		=> 'value',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'calscale'	=> True,
					'method'		=> True,
					'prodid'		=> True,
					'version'	=> True,
					'attach'		=> True,
					'categories'	=> True,
					'class'		=> True,
					'comment'	=> True,
					'description'	=> True,
					'geo'		=> True,
					'location'	=> True,
					'percent'	=> True,
					'priority'	=> True,
					'resources'	=> True,
					'status'		=> True,
					'summary'	=> True,
					'completed'	=> True,
					'dtend'		=> True,
					'due'		=> True,
					'dtstart'	=> True,
					'duration'	=> True,
					'freebusy'	=> True,
					'transp'		=> True,
					'tzid'		=> True,
					'tzname'		=> True,
					'tzoffsetfrom'	=> True,
					'tzoffsetto'	=> True,
					'tzurl'		=> True,
					'attendee'	=> True,
					'contact'	=> True,
					'organizer'	=> True,
					'recurrence_id'	=> True,
					'url'		=> True,
					'uid'		=> True,
					'exdate'	=> True,
					'exrule'	=> True,
					'rdate'	=> True,
					'rrule'	=> True,
					'action'	=> True,
					'repeat'	=> True,
					'trigger'	=> True,
					'created'	=> True,
					'dtstamp'	=> True,
					'last_modified'	=> True,
					'sequence'	=> True,
					'x_type'		=> True,
					'request_status'	=> True
				)
			),
			'wkst'		=> Array(
				'type'		=> 'string',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'rrule'		=> True
				)
			),
			'x_type'		=> Array(
				'type'		=> 'x_type',
				'quoted'		=> False,
				'to_text'	=> False,
				'properties'	=> Array(
					'calscale'	=> True,
					'method'		=> True,
					'prodid'		=> True,
					'version'	=> True,
					'attach'		=> True,
					'categories'	=> True,
					'class'		=> True,
					'comment'	=> True,
					'description'	=> True,
					'geo'		=> True,
					'location'	=> True,
					'percent'	=> True,
					'priority'	=> True,
					'resources'	=> True,
					'status'		=> True,
					'summary'	=> True,
					'completed'	=> True,
					'dtend'		=> True,
					'due'		=> True,
					'dtstart'	=> True,
					'duration'	=> True,
					'freebusy'	=> True,
					'transp'		=> True,
					'tzid'		=> True,
					'tzname'		=> True,
					'tzoffsetfrom'	=> True,
					'tzoffsetto'	=> True,
					'tzurl'		=> True,
					'attendee'	=> True,
					'contact'	=> True,
					'organizer'	=> True,
					'recurrence_id'	=> True,
					'url'		=> True,
					'uid'		=> True,
					'exdate'	=> True,
					'exrule'	=> True,
					'rdate'	=> True,
					'rrule'	=> True,
					'action'	=> True,
					'repeat'	=> True,
					'trigger'	=> True,
					'created'	=> True,
					'dtstamp'	=> True,
					'last_modified'	=> True,
					'sequence'	=> True,
					'x_type'		=> True,
					'request_status'	=> True
				)
			)			
		);
	}

	function set_var(&$event,$type,$value)
	{
		$type = strtolower(str_replace('-','_',$type));
		$event->$type = $value;
	}

	function read_line_unfold($vcal_text)
	{
		if($this->line < count($vcal_text))
		{
			$str = str_replace("\r\n",'',$vcal_text[$this->line]);
			$this->line = $this->line + 1;
			while(ereg("^[[:space:]]",$vcal_text[$this->line]))
			{
				$str .= substr(str_replace("\r\n",'',$vcal_text[$this->line]),1);
				$this->line = $this->line + 1;
			}
//			$this->debug("LINE : ".$str);
			return $str;
		}
		else
		{
			return False;
		}
	}

	function fold($str)
	{
		return chunk_split($str,FOLD_LENGTH,"\r\n");
	}

	function strip_quotes($str)
	{
		return str_replace('"','',$str);
	}

	function explode_param($str,$check_equal)
	{
		$results = Array();

// [(X\-)?([[:alnum:]])*]=
		$alnum_regexp = '[:alnum:]';
		$space_regexp = '[:space:]';
		$extra_regexp = '([ \)\(\/\@\-]|((\:\/\/)?))';
// This works good...
		$param_regexp = '((X-)?['.$alnum_regexp.']*)';
		$quote_regexp = '(\")?';
		$end_regexp = '[\;\:]|$';
// Left Side (WORKS)
		$regexp_param = '('.$param_regexp.'=)';
//		$regexp_param = '('.$param_regexp.'=['.$alnum_regexp.']'.$end_regexp.')';
// Right Side (WORKS minus last char)
//		$regexp_param = '(=('.$quote_regexp.'(['.$alnum_regexp.$space_regexp.'\(\@\/\-\)])+)'.$quote_regexp.'))';
//		$regexp_param = '('.$param_regexp.'=('.$quote_regexp.'(['.$alnum_regexp.$space_regexp.'\(\-\)\.@/ (\:\/\/)*(\:[0-9])*])*)'.$quote_regexp.')*)';
//		$regexp_param = '('.$param_regexp.'=('.$quote_regexp.'(['.$alnum_regexp.$space_regexp.$extra_regexp.'(\:[0-9])*])*)'.$quote_regexp.')*)';
		$this->debug('REGEXP : '.$regexp_param);
		while(ereg($regexp_param,$str,$temp) && $str)
		{
			$this->debug('Param = '.$temp[1]);
			$str=str_replace($temp[1],'',$str);
			if(strpos(' '.$temp[1],'='))
			{
				$var = explode('=',$temp[1]);
				$results[] = $var[0];
				$results[] = $var[1];
			}
			else
			{
				$results[] = $temp[1];
			}
		}

		if($str)
		{
			$results[] = $str;
		}
		reset($results);
		return $results;
	}

	function from_text($str)
	{
		$str = str_replace("\\,",",",$str);
		$str = str_replace("\\;",";",$str);
		$str = str_replace("\\N","\n",$str);
		$str = str_replace("\\n","\n",$str);
		$str = str_replace("\\\\","\\",$str);
		return $str;
	}

	function to_text($str)
	{
		$str = str_replace("\\","\\\\",$str);
		$str = str_replace(",","\\,",$str);
		$str = str_replace(";","\\;",$str);
		$str = str_replace("\n","\\n",$str);
		return $str;
	}

	function from_dir($str)
	{
		return str_replace('=3D','=',str_replace('%20',' ',$str));
	}

	function to_dir($str)
	{
		return str_replace('=','=3D',str_replace(' ','%20',$str));
	}

	function find_parameters($property)
	{
		reset($this->parameter);
		while(list($key,$param_array) = each($this->parameter))
		{
			if($param_array['properties'][$property])
			{
				$param[] = $key;
			}
		}
		reset($param);
		return $param;
	}

	function find_properties($ical_type)
	{
		reset($this->property);
		while(list($key,$param_array) = each($this->property))
		{
			if($param_array[$ical_type])
			{
				$prop[] = $key;
			}
		}
		reset($prop);
		return $prop;
	}

	function new_vcal()
	{
		return new vCal;
	}

	/*
	 * Parse Functions
	 */

	function parse_geo(&$event,$value)
	{
		$return_value = $this->explode_param($value,True);
		if(count($return_value) == 2)
		{
			$event->lat = $return_value[0];
			$event->lon = $return_value[1];
		}
	}

	function parse_xtype(&$event,$majortype,$value)
	{
		$temp_x_type = new class_x_type;
		$temp_x_type->name = strtoupper(substr($majortype,2));
		$temp_x_type->value = $value;
		$event->x_type[] = $temp_x_type;
		unset($temp_x_type);
	}

	function parse_parameters(&$event,$majortype,$value)
	{
		$return_value = $this->explode_param($value,True);
		if(count($return_value) > 0)
		{
			for($i=0;$i<count($return_value);$i=$i + 2)
			{
				$name = $return_value[$i];
				$value = $this->strip_quotes($return_value[$i+1]);
				if(substr($name,0,2) == 'X-')
				{
					$param = 'x_type';
					$name = str_replace('-','_',$name);
				}
				else
				{
					$param = str_replace('-','_',strtolower($name));
					if(!isset($this->parameter[$param]))
					{
//						if($majortype == 'attendee' || $majortype == 'organizer')
//						{
//							$param = 'mailto';
//							$value = $name;
//							$name = $param;
//						}
//						else
//						{
							$param = 'value';
//						}
					}
				}
				$this->debug('name : '.$name.' : Param = '.$param);
				if(@$this->parameter[$param]['properties'][$majortype])
				{
					switch(@$this->parameter[$param]['type'])
					{
						case 'dir':
							$this->set_var($event,$name,$this->from_dir($value));
							break;
						case 'text':
							$this->set_var($event,$name,$value);
							break;
						case 'x_type':
							$this->parse_xtype($event,$name,$value);
							break;
						case 'function':
							$function = $this->parameter[$param]['function'];
							$this->set_var($event,$name,$this->$function($value));
							break;
						case 'uri':
							if(@$this->parameter[$param]['to_text'])
							{
								$value = $this->to_text($value);
							}
							$this->set_var($event,$name,$value);
							break;
						case 'integer':
							$this->set_var($event,$name,intval($value));
							break;
						case 'value':
							if(@$this->property[$majortype]['type'] == 'date-time')
							{
								$this->set_var($event,$param,$this->switch_date($name));
							}
							elseif($name <> "\\n")
							{
								$this->set_var($event,$param,$name);
							}
							break;
					}
				}
			}				
		}
		elseif($value <> "\\n")
		{
			$this->set_var($event,'value',$value);
		}
	}

	function parse(&$event,$majortype,$value,$class)
	{
		$var = new $class;
		$this->parse_parameters($var,$majortype,$value);
		if($multiples)
		{
			$event->{$majortype}[] = $var;
		}
		else
		{
			$this->set_var($event,$majortype,$var);
		}
		unset($var);
	}


	/*
	 * Build-Card Functions
	 */

	function build_xtype($x_type,$seperator='=')
	{
		$quote = '';
		if($seperator == '=')
		{
			$quote = '"';
		}
		
		return $this->fold('X-'.$x_type->name.$seperator.$quote.$x_type->value.$quote);
	}

	function build_parameters($event,$property)
	{
		$str = '';
		$include_mailto = False;
		$include_datetime = False;
		$param = $this->find_parameters($property);
		while(list($dumb_key,$key) = each($param))
		{
			if($key == 'mailto')
			{
				$include_mailto = True;
				continue;
			}
			$param_array = @$this->parameter[$key];
			$type = @$param_array['type'];
			if($type == 'date-time')
			{
				$include_datetime = True;
				continue;
			}
			$quote = (@$param_array['quoted']?'"':'');
			if(!empty($event->$key) && @$param_array['properties'][$property])
			{
				$change_text = @$param_array['to_text'];
				$value = $event->$key;
				if($change_text && $type == 'text')
				{
					$value = $this->to_text($value);
				}
				switch($type)
				{
					case 'dir':
						$str .= ';'.str_replace('_','-',strtoupper($key)).'='.$quote.$this->to_dir($value).$quote;
						break;
					case 'function':
						$str .= ';'.str_replace('_','-',strtoupper($key)).'=';
						$function = @$param_array['function'];
						$str .= $quote.$this->$function($value).$quote;
						break;
					case 'text':
						$str .= ';'.strtoupper($key).'='.$quote.$value.$quote;
						break;
					case 'date-time':
						$str .= ':'.date('Ymd\THms\Z',mktime($event->hour,$event->min,$event->sec,$event->month,$event->mday,$event->year));
						
				}
				unset($value);
			}
		}

		if(!empty($event->x_type))
		{
			for($j=0;$j<count($event->x_type);$j++)
			{
				$str .= ';'.$this->build_xtype($event->x_type[$j],'=');
			}
		}
		if(!empty($event->value))
		{
			if($to_text)
			{
				$event->value = $this->to_text($event->value);
			}
			$str .= ':'.$event->value;
		}
		if($include_mailto == True)
		{
			$key = 'mailto';
			$function = $this->parameter[$key]['function'];
			$str .= ':'.$this->$function($event->$key);
		}
		if($include_datetime == True || @$this->property[$property]['type'] == 'date-time')
		{
			$str .= ':'.date('Ymd\THms\Z',mktime($event->hour,$event->min,$event->sec,$event->month,$event->mday,$event->year));
		}
		return $str;
	}

	function build_text($event,$property)
	{
		$str = '';
		$param = $this->find_parameters($property);
		while(list($dumb_key,$key) = each($param))
		{
			if(!empty($event->$key))
			{
				$type = @$this->parameter[$key]['type'];
				$quote = @$this->parameter[$key]['quote'];
				if(@$this->parameter[$key]['to_text'] == True)
				{
					$value = $this->to_text($event->$key);
				}
				else
				{
					$value = $event->$key;
				}
				switch($type)
				{
					case 'text':
						$str .= ';'.strtoupper($key).'='.$quote.$value.$quote;
						break;						
				}
			}
		}
		if(!empty($event->x_type))
		{
			for($j=0;$j<count($event->x_type);$j++)
			{
				$str .= ';'.$this->build_xtype($event->x_type[$j],'=');
			}
		}
		if(!empty($event->value))
		{
			if($to_text)
			{
				$event->value = $this->to_text($event->value);
			}
			$str .= ':'.$event->value;
		}
		return $str;
	}

//	function build_recur($event)
//	{
//		$var = Array(
//			'freq',
//			'count',
//			'wkst',
//			'byday'
//		);
//		for($i=0;$i<count($var);$i++)
//		{
//			iF(!empty($event->{$var[$i]}))
//			{
//				$str[] = strtoupper($var[$i]).'='.$event->{$var[$i]};
//			}
//		}
//		$recur = ':'.implode($str,';');
//		return $recur;
//	}

//	function build_datetime($event)
//	{
//		return ':'.date('Ymd\THms\Z',mktime($event->hour,$event->min,$event->sec,$event->month,$event->mday,$event->year));
//	}

	function build_card_internals($ical_item,$event)
	{
		$prop = $this->find_properties($ical_item);
		reset($prop);
		while(list($dumb_key,$key) = each($prop))
		{
			$value  = $key;
			$varray = $this->property[$key];
			$type   = $varray['type'];
			$to_text = $varray['to_text'];
			$state  = @$varray[$ical_item]['state'];
			$multiples  = @$varray[$ical_item]['multiples'];
			switch($type)
			{
				case 'date-time':
					if(!empty($event->$value))
					{
						if($multiples)
						{
							for($i=0;$i<count($event->$value);$i++)
							{
								$str .= $this->fold(strtoupper($value).$this->build_parameters($event->{$value}[$i],$value));
							}
						}
						else
						{
							$str .= $this->fold(strtoupper($value).$this->build_parameters($event->$value,$value));
						}
					}
					elseif($value == 'dtstamp' || $value == 'created')
					{
						$str .= $this->fold(strtoupper($value).':'.gmdate('Ymd\THms\Z'));
					}								
					break;
				case 'uri':
					if(!empty($event->$value))
					{
						for($i=0;$i<count($event->$value);$i++)
						{
							$str .= $this->fold(strtoupper($value).$this->build_parameters($event->{$value}[$i],$to_text));
						}
					}
					break;
				case 'recur':
					if(!empty($event->$value))
					{
						if($multiples)
						{
							for($i=0;$i<count($event->$value);$i++)
							{
								$str .= $this->fold(strtoupper(str_replace('_','-',$value)).$this->build_parameters($event->{$value}[$i]));
							}
						}
						else
						{
							$str .= $this->fold(strtoupper(str_replace('_','-',$value)).$this->build_parameters($event->$value));
						}
					}
					break;
				case 'integer':
					if(!empty($event->$value))
					{
						$str .= $this->fold(strtoupper(str_replace('_','-',$value)).':'.$event->$value);
					}
					elseif($value == 'sequence' || $value == 'percent_complete')
					{
						$str .= $this->fold(strtoupper(str_replace('_','-',$value)).':0');
					}
					break;
				case 'float':
					if(!empty($event->$value))
					{
						$str .= $this->fold(strtoupper(str_replace('_','-',$value)).':'.$event->$value->lat.';'.$event->$value->lon);
					}
					break;
				case 'text':
//					if(empty($event->$value) && $state == 'required')
//					{
//						return '';
//					}
					if(!empty($event->$value))
					{
						if($multiples)
						{
							for($i=0;$i<count($event->$value);$i++)
							{
								$str .= $this->fold(strtoupper(str_replace('_','-',$value)).$this->build_parameters($event->{$value}[$i],$value));
							}
						}
						else
						{
							$str .= $this->fold(strtoupper(str_replace('_','-',$value)).$this->build_parameters($event->$value,$value));
						}
					}
					break;
				case 'cal-address':
					if(!empty($event->$value))
					{
						for($j=0;$j<count($event->$value);$j++)
						{
							$temp_output = $this->build_parameters($event->{$value}[$j],$value);
							if($temp_output)
							{
								$str .= $this->fold(strtoupper($value).$temp_output);
							}
						}
					}
					break;
			}
		}
		if(!empty($event->x_type))
		{
			for($i=0;$i<count($event->x_type);$i++)
			{
				$str .= $this->build_xtype($event->x_type[$i],':');
			}
		}

		if($ical_item == 'vtimezone')
		{
			if($event->tzdata)
			{
				for($k=0;$k<count($event->tzdata);$k++)
				{
					$str .= 'BEGIN:'.strtoupper($event->tzdata[$k]->type)."\r\n";
					$str .= $this->build_card_internals(strtolower($event->tzdata[$k]->type),$event->tzdata[$k]);
					$str .= 'END:'.strtoupper($event->tzdata[$k]->type)."\r\n";
				}
			}
		}
		return $str;
	}

	/*
	 * Switching Functions
	 */

	function switch_class($var)
	{
		if(is_string($var))
		{
			switch($var)
			{
				case 'PRIVATE':
					return PRIVATE;
					break;
				case 'PUBLIC':
					return PUBLIC;
					break;
				case 'CONFIDENTIAL':
					return CONFIDENTIAL;
					break;
			}
		}
		elseif(is_int($var))
		{
			switch($var)
			{
				case PRIVATE:
					return 'PRIVATE';
					break;
				case PUBLIC:
					return 'PUBLIC';
					break;
				case CONFIDENTIAL:
					return 'CONFIDENTIAL';
					break;
			}
		}
		else
		{
			return $var;
		}
	}

	function switch_cu($var)
	{
		if(gettype($var) == 'string')
		{
			switch($var)
			{
				case 'INDIVIDUAL':
					return INDIVIDUAL;
					break;
				case 'GROUP':
					return GROUP;
					break;
				case 'RESOURCE':
					return RESOURCE;
					break;
				case 'ROOM':
					return ROOM;
					break;
				case 'UNKNOWN':
					return UNKNOWN;
					break;
				default:
					return OTHER;
					break;
			}
		}
		elseif(gettype($var) == 'integer')
		{
			switch($var)
			{
				case INDIVIDUAL:
					return 'INDIVIDUAL';
					break;
				case GROUP:
					return 'GROUP';
					break;
				case RESOURCE:
					return 'RESOURCE';
					break;
				case ROOM:
					return 'ROOM';
					break;
				case UNKNOWN:
					return 'UNKNOWN';
					break;
				default:
					return 'X-OTHER';
					break;
			}
		}
		else
		{
			return $var;
		}
	}

	function switch_date($var)
	{
		if(is_string($var))
		{
			$dtime = new class_datetime;
			if(strpos($var,':'))
			{
				$pos = explode(':',$var);
				$var = $pos[1];
			}
			$this->set_var($dtime,'year',intval(substr($var,0,4)));
			$this->set_var($dtime,'month',intval(substr($var,4,2)));
			$this->set_var($dtime,'mday',intval(substr($var,6,2)));
			if(substr($var,8,1) == 'T')
			{
				$this->set_var($dtime,'hour',intval(substr($var,9,2)));
				$this->set_var($dtime,'min',intval(substr($var,11,2)));
				$this->set_var($dtime,'sec',intval(substr($var,13,2)));
				if(strlen($var) > 14)
				{
					if(substr($var,14,1) != 'Z')
					{
					}
				}
				else
				{
					/*
					 * The time provided by the vCal is considered local time.
					 *
					 * The implementor will need to consider how to convert that time to UTC.
					 */
				}
			}
			else
			{
				$this->set_var($dtime,'hour',0);
				$this->set_var($dtime,'min',0);
				$this->set_var($dtime,'sec',0);
			}
			return $dtime;
		}
		elseif(is_object($var))
		{
			return date('Ymd\THms\Z',mktime($var->hour,$var->min,$var->sec,$var->month,$var->mday,$var->year));
		}
		else
		{
			return $var;
		}
	}

	function switch_encoding($var)
	{
		if(is_string($var))
		{
			switch($var)
			{
				case '8BIT':
					return _8BIT;
					break;
				case 'BASE64':
					return _BASE64;
					break;
				default:
					return OTHER;
					break;
			}
		}
		elseif(is_int($var))
		{
			switch($var)
			{
				case _8BIT:
					return '8BIT';
					break;
				case _BASE64:
					return 'BASE64';
					break;
				case OTHER:
					return 'OTHER';
					break;
			}
		}
		else
		{
			return $var;
		}
	}

	function switch_fbtype($var)
	{
		if(is_string($var))
		{
			switch($var)
			{
				case 'FREE':
					return FREE;
					break;
				case 'BUSY':
					return BUSY;
					break;
				case 'BUSY-UNAVAILABLE':
					return BUSY_UNAVAILABLE;
					break;
				case 'BUSY-TENTATIVE':
					return BUSY_TENTATIVE;
					break;
				default:
					return OTHER;
					break;
			}
		}
		elseif(is_int($var))
		{
			switch($var)
			{
				case FREE:
					return 'FREE';
					break;
				case BUSY:
					return 'BUSY';
					break;
				case BUSY_UNAVAILABLE:
					return 'BUSY-UNAVAILABLE';
					break;
				case BUSY_TENTATIVE:
					return 'BUSY-TENTATIVE';
					break;
				default:
					return 'OTHER';
					break;
			}
		}
		else
		{
			return $var;
		}
	}	

	function switch_freq($var)
	{
		if(is_string($var))
		{
			switch($var)
			{
				case 'SECONDLY':
					return SECONDLY;
					break;
				case 'MINUTELY':
					return MINUTELY;
					break;
				case 'HOURLY':
					return HOURLY;
					break;
				case 'DAILY':
					return DAILY;
					break;
				case 'WEEKLY':
					return WEEKLY;
					break;
				case 'MONTHLY':
					return MONTHLY;
					break;
				case 'YEARLY':
					return YEARLY;
					break;
			}
		}
		elseif(gettype($var) == 'integer')
		{
			switch($var)
			{
				case SECONDLY:
					return 'SECONDLY';
					break;
				case MINUTELY:
					return 'MINUTELY';
					break;
				case HOURLY:
					return 'HOURLY';
					break;
				case DAILY:
					return 'DAILY';
					break;
				case WEEKLY:
					return 'WEEKLY';
					break;
				case MONTHLY:
					return 'MONTHLY';
					break;
				case YEARLY:
					return 'YEARLY';
					break;
			}
		}
		else
		{
			return $var;
		}
	}

	function switch_mailto($var)
	{
		if(is_string($var))
		{
			if(strpos(' '.$var,':'))
			{
				$parts = explode(':',$var);
				$var = $parts[1];
			}
		
			$parts = explode('@',$var);
			if(count($parts) == 2)
			{
				$temp_address = new class_mailto;
				$temp_address->user = $parts[0];
				$temp_address->host = $parts[1];
				return $temp_address;
			}
			else
			{
				return False;
			}
		}
		elseif(is_object($var))
		{
			$str = 'MAILTO:'.$var->user.'@'.$var->host;
			return $str;
		}
	}

	function switch_partstat($var)
	{
		if(is_string($var))
		{
			switch($var)
			{
				case 'NEEDS-ACTION':
					return NEEDS_ACTION;
					break;
				case 'ACCEPTED':
					return ACCEPTED;
					break;
				case 'DECLINED':
					return DECLINED;
					break;
				case 'TENTATIVE':
					return TENTATIVE;
					break;
				case 'DELEGATED':
					return DELEGATED;
					break;
				case 'COMPLETED':
					return COMPLETED;
					break;
				case 'IN-PROCESS':
					return IN_PROCESS;
					break;
				default:
					return OTHER;
					break;
			}
		}
		elseif(is_int($var))
		{
			switch($var)
			{
				case NEEDS_ACTION:
					return 'NEEDS-ACTION';
					break;
				case ACCEPTED:
					return 'ACCEPTED';
					break;
				case DECLINED:
					return 'DECLINED';
					break;
				case TENTATIVE:
					return 'TENTATIVE';
					break;
				case DELEGATED:
					return 'DELEGATED';
					break;
				case COMPLETED:
					return 'COMPLETED';
					break;
				case IN_PROCESS:
					return 'IN-PROCESS';
					break;
				default:
					return 'X-OTHER';
					break;
			}
		}
		else
		{
			return $var;
		}
	}

	function switch_range($var)
	{
		if(is_string($var))
		{
			switch($var)
			{
				case 'THISANDPRIOR':
					return THISANDPRIOR;
					break;
				case 'THISANDFUTURE':
					return THISANDFUTURE;
					break;
			}
		}
		elseif(is_int($var))
		{
			switch($var)
			{
				case THISANDPRIOR:
					return 'THISANDPRIOR';
					break;
				case THISANDFUTURE:
					return 'THISANDFUTURE';
					break;
			}
		}
		else
		{
			return $var;
		}
	}

	function switch_related($var)
	{
		if(is_string($var))
		{
			switch($var)
			{
				case 'START':
					return START;
					break;
				case 'END':
					return END;
					break;
			}
		}
		elseif(is_int($var))
		{
			switch($var)
			{
				case START:
					return 'START';
					break;
				case END:
					return 'END';
					break;
			}
		}
		else
		{
			return $var;
		}
	}

	function switch_reltype($var)
	{
		if(is_string($var))
		{
			switch($var)
			{
				case 'PARENT':
					return PARENT;
					break;
				case 'CHILD':
					return CHILD;
					break;
				case 'SIBLING':
					return SIBLING;
					break;
			}
		}
		elseif(is_int($var))
		{
			switch($var)
			{
				case PARENT:
					return 'PARENT';
					break;
				case CHILD:
					return 'CHILD';
					break;
				case SIBLING:
					return 'SIBLING';
					break;
			}
		}
		else
		{
			return $var;
		}
	}

	function switch_role($var)
	{
		if(is_string($var))
		{
			switch($var)
			{
				case 'NONE':
					return NONE;
					break;
				case 'CHAIR':
					return CHAIR;
					break;
				case 'REQ-PARTICIPANT':
					return REQ_PARTICIPANT;
					break;
				case 'OPT-PARTICIPANT':
					return OPT_PARTICIPANT;
					break;
				case 'NON-PARTICIPANT':
					return NON_PARTICIPANT;
					break;
			}
		}
		elseif(is_int($var))
		{
			switch($var)
			{
				case NONE:
					return 'NONE';
					break;
				case CHAIR:
					return 'CHAIR';
					break;
				case REQ_PARTICIPANT:
					return 'REQ-PARTICIPANT';
					break;
				case OPT_PARTICIPANT:
					return 'OPT-PARTICIPANT';
					break;
				case NON_PARTICIPANT:
					return 'NON-PARTICIPANT';
					break;
			}
		}
		else
		{
			return $var;
		}
	}

	function switch_rsvp($var)
	{
		if(is_string($var))
		{
			if($var == 'TRUE')
			{
				return 1;
			}
			elseif($var == 'FALSE')
			{
				return 0;
			}
		}
		elseif(is_int($var) || $var == False)
		{
			if($var == 1)
			{
				return 'TRUE';
			}
			elseif($var == 0)
			{
				return 'FALSE';
			}
		}
		else
		{
			return $var;
		}
	}

	function switch_transp($var)
	{
		if(is_string($var))
		{
			switch($var)
			{
				case 'TRANSPARENT':
					return TRANSPARENT;
					break;
				case 'OPAQUE':
					return OPAQUE;
					break;
			}
		}
		elseif(is_int($var))
		{
			switch($var)
			{
				case TRANSPARENT:
					return 'TRANSPARENT';
					break;
				case OPAQUE:
					return 'OPAQUE';
					break;
			}
		}
		else
		{
			return $var;
		}
	}

	/*
	 * The brunt of the class
	 */	

	function read($vcal_text)
	{

		$mode = 'none';
		$text = $this->read_line_unfold($vcal_text);
		while($text)
		{
//			if(strlen($vcal_text[$i]) > 75)
//			{
//				continue;
//			}

//			$this->debug('TEXT : '.$text);
//	flush();

			$colon = strpos($text,':');
			if($colon == 0)
			{
				$colon = 65535;
			}

			$semi_colon = strpos($text,';');
			if($semi_colon == 0)
			{
				$semi_colon = 65535;
			}

			if($colon == 65535 && $semi_colon == 65535)
			{
				continue;
			}
			else
			{
				$min_value = min($colon,$semi_colon);
				$majortype = str_replace('-','_',strtolower(substr($text,0,$min_value)));
				$value = chop(substr($text,$min_value + 1));
			}
			
			if($mode != 'none' && ($majortype != 'begin' && $majortype != 'end'))
			{
				if(isset($this->property[$majortype]))
				{
					$state = @$this->property[$majortype]["$mode"]['state'];
					$type = @$this->property[$majortype]['type'];
					$multiples = @$this->property[$majortype]["$mode"]['multiples'];
					$do_to_text = @$this->property[$majortype]['to_text'];
				}
				elseif(substr($majortype,0,2) == 'x_')
				{
					$state = 'optional';
					$type = 'xtype';
					$multiples = True;
					$do_to_test = True;
				}
				else
				{
					$state = '';
				}
			}
			else
			{
				$state = 'required';
			}

			if($majortype == 'begin')
			{
				$tmode = $mode;
				$mode = strtolower($value);
				switch(strtolower($value))
				{
					case 'daylight':
						$t_event = new class_timezone;
						$t_event = $event;
						unset($event);
						$event = new class_tzprop;
						break;
					case 'standard':
						$t_event = new class_timezone;
						$t_event = $event;
						unset($event);
						break;
					case 'valarm':
						if($mode == 'vevent' || $mode == 'vtodo')
						{
							$t_event = $event;
							unset($event);
							$event = new class_alarm;
						}
						else
						{
							$mode = $tmode;
						}
						break;
					case 'vcalendar':
						$vcal = $this->new_vcal();
						break;
					case 'vevent':
						$event = new class_event;
						break;
					case 'vfreebusy':
						$event = new class_freebusy;
						break;
					case 'vjournal':
						$event = new class_journal;
						break;
					case 'vtimezone':
						$event = new class_timezone;
						break;
					case 'vtodo':
						$event = new class_todo;
						break;
				}
				$event->type = strtolower($value);
			}
			elseif($majortype == 'end')
			{
				$mode = 'none';
				switch(strtolower($value))
				{
					case 'daylight':
						$tzdata[] = $event;
						unset($event);
						$event = $t_event;
						unset($t_event);
						$mode = 'vtimezone';
						break;
					case 'standard':
						$tzdata[] = $event;
						unset($event);
						$event = $t_event;
						unset($t_event);
						$mode = 'vtimezone';
						break;
					case 'valarm':
						$alarm[] = $event;
						unset($event);
						$event = $t_event;
						unset($t_event);
						$mode = $tmode;
						break;
					case 'vevent':
						if(!empty($alarm))
						{
							$event->alarm = $alarm;
							unset($alarm);
						}
						$this->event[] = $event;
						unset($event);
						break;
					case 'vfreebusy':
						$this->freebusy[] = $event;
						unset($event);
						break;
					case 'vjournal':
						$this->journal[] = $event;
						unset($event);
						break;
					case 'vtimezone':
						if(!empty($tzdata))
						{
							$event->tzdata = $tzdata;
							unset($tzdata);
						}
						$this->timezone[] = $event;
						unset($event);
						break;
					case 'vtodo':
						if(!empty($alarm))
						{
							$event->alarm = $alarm;
							unset($alarm);
						}
						$this->todo[] = $event;
						unset($event);
						break;
					case 'vcalendar':
						$this->vcal = $vcal;
						$this->vcal->event = $this->event;
						$this->vcal->freebusy = $this->freebusy;
						$this->vcal->journal = $this->journal;
						$this->vcal->timezone = $this->timezone;
						$this->vcal->todo = $this->todo;
						break 2;
				}
			}
			elseif($majortype == 'prodid' || $majortype == 'version' || $majortype == 'method' || $majortype == 'calscale')
			{
				$this->parse_parameters($vcal->$majortype,$majortype,$this->from_text($value));
			}
			elseif($state == 'optional' || $state == 'required')
			{
				if($do_to_text)
				{
					$value = $this->from_text($value);
				}
				switch($type)
				{
					case 'text':
						$class = 'class_text';
						$this->parse($event,$majortype,$value,$class);
						break;
					case 'integer':
						if($multiples)
						{
							$event->{$majortype}[] = intval($value);
						}
						else
						{
							$this->set_var($event,$majortype,intval($value));
						}
						break;
					case 'date-time':
						$class = 'class_date_time';
						$this->parse($event,$majortype,$value,$class);
						break;
					case 'float':
						$event->$majortype = new class_geo;
						$this->parse_geo($event->$majortype,$value);
						break;
					case 'utc-offset':
						$this->set_var($event,$majortype,intval($value));
						break;
					case 'cal-address':
						$class = 'class_address';
						$this->parse($event,$majortype,$value,$class);
						break;
					case 'recur':
						$class = 'class_recur';
						$this->parse($event,$majortype,$value,$class);
						break;
					case 'uri':
						$new_var = new class_text;
						$this->parse_parameters($new_var,$majortype,$value);
						if($multiples)
						{
							switch($mode)
							{
								case 'valarm':
									$alarm->attach[] = $new_var;
									break;
								default:
									$event->{$majortype}[] = $new_var;
									break;
							}
						}
						else
						{
							$event->{$majortype} = $new_var;
						}
						unset($new_var);
						break;
					case 'xtype':
						$this->parse_xtype($event,$majortype,$value);
						break;
				}
			}
			$text = $this->read_line_unfold($vcal_text);
		}
		return $this->vcal;
	}

	function build_vcal($vcal)
	{
		$var = Array(
			'timezone',
			'event',
			'todo',
			'journal',
			'freebusy'
		);

		$str = 'BEGIN:VCALENDAR'."\r\n";
		$str .= $this->fold('PRODID'.$this->build_text($vcal->prodid));
		$str .= $this->fold('VERSION'.$this->build_text($vcal->version));
		$str .= $this->fold('METHOD'.$this->build_text($vcal->method));
		while(list($key,$vtype) = each($var))
		{
			if($vcal->$vtype)
			{
				for($i=0;$i<count($vcal->$vtype);$i++)
				{
					$str .= 'BEGIN:V'.strtoupper($vtype)."\r\n";
					$str .= $this->build_card_internals('v'.$vtype,$vcal->{$vtype}[$i]);
					$str .= 'END:V'.strtoupper($vtype)."\r\n";
				}
				$this->debug('STR #'.$i.' : '.$str);
//				flush();
			}
		}
		$str .= 'END:VCALENDAR'."\r\n";

		return $str;
	}

	function debug($str='')
	{
		if($this->debug_str)
		{
			echo $str."<br>\n";
		}
	}
}
?>
