<?php
  /**************************************************************************\
  * phpGroupWare - Polls                                                     *
  * http://www.phpgroupware.org                                              *
  *  The file is based on phpPolls                                           *
  *  Copyright (c) 1999 Till Gerken (tig@skv.org)                            *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	if ($HTTP_POST_VARS['submit'])
	{
		$GLOBALS['phpgw_info']['flags'] = array(
			'noheader' => True,
			'nonavbar' => True
		);
	}

	$GLOBALS['phpgw_info']['flags']['currentapp'] = 'polls';
	$GLOBALS['phpgw_info']['flags']['enable_nextmatchs_class'] = True;
	include('../header.inc.php');

	if ($HTTP_POST_VARS['submit'])
	{
		if (verify_uservote($HTTP_POST_VARS['poll_id']))
		{
			//$GLOBALS['phpgw']->db->lock(array("phpgw_polls_data","phpgw_polls_user"));
			$GLOBALS['phpgw']->db->query("UPDATE phpgw_polls_data SET option_count=option_count+1 WHERE "
				. "poll_id='" . $HTTP_POST_VARS['poll_id'] . "' AND vote_id='" . $HTTP_POST_VARS['poll_voteNr'] . "'",__LINE__,__FILE__);
			$GLOBALS['phpgw']->db->query("insert into phpgw_polls_user values ('" . $HTTP_POST_VARS['poll_id'] . "','','"
				. $GLOBALS['phpgw_info']['user']['account_id'] . "','" . time() . "')",__LINE__,__FILE__);
			//$GLOBALS['phpgw']->db->unlock();
		}
		Header('Location: ' . $GLOBALS['phpgw']->link('/polls/vote.php','show_results=' . $HTTP_POST_VARS['poll_id']));
		$GLOBALS['phpgw']->common->phpgw_exit();
	}
	if ($HTTP_GET_VARS['show_results'])
	{
		poll_viewResults($HTTP_GET_VARS['show_results']);
	}
	$GLOBALS['phpgw']->common->phpgw_footer();
?>
