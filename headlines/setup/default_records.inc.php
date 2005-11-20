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

	$oProc->query("INSERT INTO egw_headlines_sites (display,base_url,newsfile,lastread,newstype,cachetime,listings) VALUES ('Linux&nbsp;Today','http://linuxtoday.com','/backend/linuxtoday.xml',0,'lt',60,20)");
	$oProc->query("INSERT INTO egw_headlines_sites (display,base_url,newsfile,lastread,newstype,cachetime,listings) VALUES ('Linux&nbsp;Game&nbsp;Tome','http://happypenguin.org','/html/news.rdf',0,'rdf',60,20)");
	$oProc->query("INSERT INTO egw_headlines_sites (display,base_url,newsfile,lastread,newstype,cachetime,listings) VALUES ('MozillaZine','http://www.mozillazine.org','/contents.rdf',0,'rdf',60,20)");
	$oProc->query("INSERT INTO egw_headlines_sites (display,base_url,newsfile,lastread,newstype,cachetime,listings) VALUES ('Security Forums','http://www.security-forums.com','/forum/rdf.php',0,'rdf',60,20)");
?>
