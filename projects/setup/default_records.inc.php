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

	$oProc->query ("INSERT INTO phpgw_p_projectmembers (project_id,account_id,type) VALUES (0,2,'ag')");
	$oProc->query ("INSERT INTO phpgw_p_projectmembers (project_id,account_id,type) VALUES (0,2,'bg')");
	$oProc->query ("INSERT INTO phpgw_p_activities (a_number,descr,remarkreq,minperae,billperae,category) VALUES ('0815','Exampleactivity','Y',0,'10.0',0)");
