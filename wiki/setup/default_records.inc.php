<?php
  /**************************************************************************\
  * eGroupWare - Setup                                                       *
  * http://www.eGroupWare.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */


	$time = time();
	$GLOBALS['phpgw_setup']->oProc->query("DELETE FROM phpgw_wiki_pages");
	$GLOBALS['phpgw_setup']->oProc->query("INSERT INTO phpgw_wiki_pages (title,version,time,supercede,username,author,body,comment) VALUES ('RecentChanges',1,$time,$time,'setup','localhost','[[! *]]\n','')");
	$GLOBALS['phpgw_setup']->oProc->query("INSERT INTO phpgw_wiki_pages (title,version,time,supercede,username,author,body,comment) VALUES ('eGroupWare',1,$time,$time,'setup','localhost','Welcome to \\'\\'\\'Wiki\\'\\'\\' - the eGroupWare Version of \\'\\'\\'WikkiTikkiTavi\\'\\'\\'. Wikis are a revolutionary new form of collaboration and online community.

\\'\\'\\'eGroupWare\\'\\'\\' is the groupware suite you are useing right now. For further information see http://www.eGroupWare.org','')");
	
	$GLOBALS['phpgw_setup']->oProc->query("INSERT INTO phpgw_wiki_pages (title,version,time,supercede,username,author,body,comment) VALUES ('WikkiTikkiTavi',1,$time,$time,'setup','localhost','= WikkiTikkiTavi =

WikkiTikkiTavi is the original version this documentation system.
Their documentation is usable for the ((eGroupWare)) \\'\\'\\'Wiki\\'\\'\\' too.

The documentation of WikkiTikkiTavi is online availible at: http://tavi.sourceforge.net
You can learn about Wiki formatting at http://tavi.sourceforge.net/FormattingRules\n','')");
	
