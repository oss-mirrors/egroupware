<?php
  /**************************************************************************\
  * phpGroupWare - Setup                                                     *
  * http://www.eGroupWare.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$GLOBALS['phpgw_setup']->oProc->query("DELETE FROM phpgw_p_projectmembers");
	$GLOBALS['phpgw_setup']->oProc->query("DELETE FROM phpgw_p_activities");

	$GLOBALS['phpgw_setup']->oProc->query("INSERT INTO phpgw_p_projectmembers (project_id,account_id,type) VALUES (0,2,'ag')");
	$GLOBALS['phpgw_setup']->oProc->query("INSERT INTO phpgw_p_projectmembers (project_id,account_id,type) VALUES (0,2,'bg')");
	$GLOBALS['phpgw_setup']->oProc->query("INSERT INTO phpgw_p_activities (num,descr,remarkreq,minperae,billperae,category) VALUES ('0815','Exampleactivity','Y',0,'10.0',0)");
