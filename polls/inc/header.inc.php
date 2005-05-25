<?php
  /**************************************************************************\
  * eGroupWare - Polls                                                       *
  * http://www.egroupware.org                                                *
  * Copyright (c) 1999 Till Gerken (tig@skv.org)                             *
  * Modified by Greg Haygood (shrykedude@bellsouth.net)                      *
  * -----------------------------------------------                          *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	/* $Id$ */

	if($GLOBALS['egw_info']['flags']['admin_header'])
	{
		$tpl = $GLOBALS['egw']->template;
		$tpl->set_file(array('admin_header' => 'admin_header.tpl'));

		$tpl->set_var('th_bg',$GLOBALS['egw_info']['theme']['th_bg']);
		$tpl->set_var('info',lang('Voting booth administration'));
		$tpl->set_var('link_list_questions','<a href="' . $GLOBALS['egw']->link('/polls/admin.php','show=questions') . '">' . lang('Show questions') . '</a>');
		$tpl->set_var('link_list_answers','<a href="' . $GLOBALS['egw']->link('/polls/admin.php','show=answers') . '">' . lang('Show answers') . '</a>');
		$tpl->set_var('link_questions','<a href="' . $GLOBALS['egw']->link('/polls/admin_addquestion.php') . '">' . lang('Add questions') . '</a>');
		$tpl->set_var('link_answers','<a href="' . $GLOBALS['egw']->link('/polls/admin_addanswer.php') . '">' . lang('Add answers') . '</a>');
		$tpl->set_var('link_settings','<a href="' . $GLOBALS['egw']->link('/polls/admin_settings.php') . '">' . lang('Poll settings') . '</a>');
		$tpl->pfp('out','admin_header');
	}
?>
