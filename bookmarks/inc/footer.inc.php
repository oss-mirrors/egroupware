<?php
	/**************************************************************************\
	* phpGroupWare - Bookmarks                                                 *
	* http://www.phpgroupware.org                                              *
	* Based on Bookmarker Copyright (C) 1998  Padraic Renaghan                 *
	*                     http://www.renaghan.com/bookmarker                   *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	include(PHPGW_APP_ROOT . '/inc/messages.inc.php');
	if (isset ($bk_output_html))
	{
		echo '<b>ERROR:</b>';
		$phpgw->template->set_var('messages',$bk_output_html);
	}

	$phpgw->template->parse('body',array('body','common'));
	$phpgw->template->p('body');

	//$phpgw->common->phpgw_footer();
?>