<?php
  /**************************************************************************\
  * phpGroupWare - Setup                                                     *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */


	$oProc->query("INSERT INTO phpgw_wiki_pages (title,version,time,supercede,username,author,body) VALUES ('RecentChanges',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),'setup','localhost','[[! *]]\n')");
	$oProc->query("INSERT INTO phpgw_wiki_pages (title,version,time,supercede,username,author,body) VALUES ('PhpGroupWareWiki',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),'setup','localhost','= PhpGroupWareWiki =\nWelcome to PhpGroupWareWiki the PhpGroupWare Version of WikkiTikkiTavi. Wikis are a revolutionary new form of collaboration and online community.\n')");
	$oProc->query("INSERT INTO phpgw_wiki_pages (title,version,time,supercede,username,author,body) VALUES ('PhpGroupWare',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),'setup','localhost','= PhpGroupWare =\nThis is the groupware suite you are useing right now. For further information see http://www.phpgroupware.org\n')");
	$oProc->query("INSERT INTO phpgw_wiki_pages (title,version,time,supercede,username,author,body) VALUES ('WikkiTikkiTavi',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),'setup','localhost','= WikkiTikkiTavi =\n\nWikkiTikkiTavi is the application that makes this documentation system possible.\n\nThe documentation for WikkiTikkiTavi is available here: http://tavi.sourceforge.net\nYou can learn about Wiki formatting at http://tavi.sourceforge.net/FormattingRules\n')");
	
