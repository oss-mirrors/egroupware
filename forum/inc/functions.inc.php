<?php
	/*****************************************************************************\
	* eGroupWare - Forums                                                         *
	* http://www.egroupware.org                                                 *
	* Written by Jani Hirvinen <jpkh@shadownet.com>                               *
	* -------------------------------------------                                 *
	*  This program is free software; you	can redistribute it and/or modify it   *
	*  under the terms of	the GNU	General	Public License as published by the  *
	*  Free Software Foundation; either version 2	of the License,	or (at your *
	*  option) any later version.                                                 *
	\*****************************************************************************/

	/* $Id$ */

	// Keep track of what they are doing
	$session_info = $GLOBALS['egw']->session->appsession('session_data','forum');
	if (! is_array($session_info))
	{
		$session_info = array(
			'view'     => $GLOBALS['egw_info']['user']['preferences']['forum']['default_view'],
			'location' => '',		// Not used ... yet
			'cat_id'   => $cat_id,
			'forum_id' => $forum_id
		);
	}

	if ($view)
	{
		$session_info['view'] = $view;
	}

	if ($cat_id)
	{
		$session_info['cat_id'] = $cat_id;
	}

	if ($forum_id)
	{
		$session_info['forum_id'] = $forum_id;
	}


	$GLOBALS['egw']->session->appsession('session_data','forum',$session_info);


// Global functions for phpgw forums

//
// showthread shows thread in threaded mode :)
//  params are: $thread = id from master message, father of all messages in this thread
//          $current = maybe NULL or message number where we are at the moment,
//         used only in reply (read.php) section to show our current
//         message with little different color ($GLOBALS['egw_info']["theme"]["bg05"])
//
function showthread ($cat) {
		global $tr_color;

		while($GLOBALS['egw']->db->next_record()) {
			$tr_color = $GLOBALS['egw']->nextmatchs->alternate_row_color($tr_color);

			if($GLOBALS['egw']->db->f("id") == $current) $tr_color = $GLOBALS['egw_info']["theme"]["bg05"];
			echo "<tr bgcolor=\"$tr_color\">";

			$move = "";
			for($tmp = 1;$tmp <= $GLOBALS['egw']->db->f("depth"); $tmp++)
					$move .= "&nbsp;&nbsp;";

			$pos = $GLOBALS['egw']->db->f("pos");
			$cat = $GLOBALS['egw']->db->f("cat_id");
			$for = $GLOBALS['egw']->db->f("for_id");
			$subject = $GLOBALS['egw']->db->f("subject");
			if (! $subject) {
				 $subject = "[ No subject ]";
			}
			echo "<td>" . $move . "<a href=" . $GLOBALS['egw']->link("read.php","cat=$cat&for=$for&pos=$pos&col=1&msg=" . $GLOBALS['egw']->db->f("id")) .">"
				 . $subject . "</a></td>\n";

			echo "<td align=left valign=top>" . ($GLOBALS['egw']->db->f('thread_owner')?$GLOBALS['egw']->accounts->id2name($GLOBALS['egw']->db->f('thread_owner')):lang('Unknown')) ."</td>\n";
			echo "<td align=left valign=top>" . $GLOBALS['egw']->common->show_date($GLOBALS['egw']->db->from_timestamp($GLOBALS['egw']->db->f('postdate'))) ."</td>\n";

			if($debug) echo "<td>" . $GLOBALS['egw']->db->f("id")." " . $GLOBALS['egw']->db->f("parent") ." "
										. $GLOBALS['egw']->db->f("depth") ." " . $GLOBALS['egw']->db->f("pos") ."</td>";

		}
}


function show_topics($cat,$for) {

		global $tr_color;

		while($GLOBALS['egw']->db->next_record())
		{
			$tr_color = $GLOBALS['egw']->nextmatchs->alternate_row_color($tr_color);
			echo "<tr bgcolor=\"$tr_color\">";
			$subject = $GLOBALS['egw']->db->f("subject");
			if (! $subject) {
				 $subject = "[ No subject ]";
			}
			echo "<td><a href=" . $GLOBALS['egw']->link("read.php","cat=$cat&for=$for&msg=$msg" . $GLOBALS['egw']->db->f("id")) .">" . $subject . "</a></td>\n";
			$lastreply = $GLOBALS['egw']->db->f("postdate");
			echo "<td align=left valign=top>" . ($GLOBALS['egw']->db->f('thread_owner')?$GLOBALS['egw']->accounts->id2name($GLOBALS['egw']->db->f('thread_owner')):lang('Unknown')) . "</td>\n";
			$msgid = $GLOBALS['egw']->db->f("id");
			$mainid = $GLOBALS['egw']->db->f("main");

			echo "<td align=left valign=top>" . $GLOBALS['egw']->db->f("n_replies") . "</td>\n";
			echo "<td align=left valign=top>$lastreply</td>\n";
		}

	echo "</tr>\n";

}

