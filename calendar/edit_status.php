<?php
  /**************************************************************************\
  * phpGroupWare - Calendar                                                  *
  * http://www.phpgroupware.org                                              *
  * Based on Webcalendar by Craig Knudsen <cknudsen@radix.net>               *
  *          http://www.radix.net/~cknudsen                                  *
  * Modified by Mark Peters <skeeter@phpgroupware.org>                       *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	/* $Id$ */
	$phpgw_info['flags']['currentapp'] = 'calendar';
	include('../header.inc.php');

	$phpgw->calendar->open('INBOX',$owner,'');
	$event = $phpgw->calendar->fetch_event($id);

	reset($event->participants);

	if(!$event->participants[$owner])
	{
		echo '<center>The user '.$phpgw->common->grab_owner_name($owner).' is not participating in this event!</center>';
		$phpgw->common->footer();
		$phpgw->common->phpgw_exit();
	}

	if($phpgw->calendar->check_perms(PHPGW_ACL_EDIT) == False)
	{
		echo '<center>You do not have permission to edit this appointment!</center>';
		$phpgw->common->footer();
		$phpgw->common->phpgw_exit();
	}

	$freetime = $phpgw->calendar->datetime->localdates(mktime(0,0,0,$event->start->month,$event->start->mday,$event->start->year) - $phpgw->calendar->datetime->tz_offset);
	echo $phpgw->calendar->timematrix($freetime,$phpgw->calendar->splittime('000000',False),0,$event->participants);

	echo $phpgw->calendar->view_event($event);

	echo $phpgw->calendar->get_response();
	$phpgw->common->phpgw_footer();	
?>
