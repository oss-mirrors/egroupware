<?php
	/**************************************************************************\
	* eGroupWare - Setup                                                       *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	/* $Id$ */

	$GLOBALS['phpgw_setup']->oProc->query("INSERT into phpgw_p_events (event_name,event_type,event_extra) values('hours limit','percent',90)",__LINE__,__FILE__);
	$GLOBALS['phpgw_setup']->oProc->query("INSERT into phpgw_p_events (event_name,event_type,event_extra) values('budget limit','percent',90)",__LINE__,__FILE__);
	$GLOBALS['phpgw_setup']->oProc->query("INSERT into phpgw_p_events (event_name,event_type,event_extra) values('project date due','limits',7)",__LINE__,__FILE__);
	$GLOBALS['phpgw_setup']->oProc->query("INSERT into phpgw_p_events (event_name,event_type,event_extra) values('milestone date due','limits',7)",__LINE__,__FILE__);

	$GLOBALS['phpgw_setup']->oProc->query("INSERT into phpgw_p_events (event_name,event_type) values('assignment to project','assignment')",__LINE__,__FILE__);
	$GLOBALS['phpgw_setup']->oProc->query("INSERT into phpgw_p_events (event_name,event_type) values('assignment to role','assignment')",__LINE__,__FILE__);

	$GLOBALS['phpgw_setup']->oProc->query("INSERT into phpgw_p_events (event_name,event_type) values('project dependencies','dependencies')",__LINE__,__FILE__);
	$GLOBALS['phpgw_setup']->oProc->query("INSERT into phpgw_p_events (event_name,event_type) values('changes of project data','dependencies')",__LINE__,__FILE__);
