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

	$GLOBALS['phpgw']->db->query("select * from phpgw_polls_settings");
	while ($GLOBALS['phpgw']->db->next_record())
	{
		$GLOBALS['poll_settings'][$GLOBALS['phpgw']->db->f('setting_name')] = $GLOBALS['phpgw']->db->f('setting_value');
	}

	function add_template_row(&$tpl,$label,$value)
	{
		$GLOBALS['phpgw']->nextmatchs->template_alternate_row_color($tpl);
		$tpl->set_var('td_1',$label);
		$tpl->set_var('td_2',$value);
		$tpl->parse('rows','row',True);
	}

	function verify_uservote($poll_id)
	{
		if ($GLOBALS['poll_settings']['allow_multiable_votes'])
		{
			return True;
		}

		$poll_id = intval($poll_id);
		$GLOBALS['phpgw']->db->query("select count(*) from phpgw_polls_user where user_id='" . intval($GLOBALS['phpgw_info']['user']['account_id'])
			. "' and poll_id='".intval($poll_id)."'",__LINE__,__FILE__);
		$GLOBALS['phpgw']->db->next_record();

		if ($GLOBALS['phpgw']->db->f(0) == 0)
		{
			return True;
		}
		else
		{
			return False;
		}
//		return ($db->f(0)?True:False);
	}

	function poll_getResultsTable($poll_id,$showtitle=true,$showtotal=true)
	{
		$output = '';
		$poll_id = intval($poll_id);
		$GLOBALS['phpgw']->db->query("SELECT SUM(option_count) AS sum FROM phpgw_polls_data WHERE poll_id='$poll_id'",__LINE__,__FILE__);
		$GLOBALS['phpgw']->db->next_record();
		$poll_sum = (int)$GLOBALS['phpgw']->db->f(0);

		$GLOBALS['phpgw']->db->query("select poll_title from phpgw_polls_desc where poll_id='$poll_id'",__LINE__,__FILE__);
		$GLOBALS['phpgw']->db->next_record();

		$output .= '<p><table border="0" align="center" width="400">';
		if($showtitle)
		{
		$output .= ' <tr>' . "\n"
			. '  <td colspan="3" bgcolor="' . $GLOBALS['phpgw_info']['theme']['th_bg'] . '" align="center">'
			. $GLOBALS['phpgw']->db->f('poll_title') . '</td>' . "\n"
			. '</tr>' . "\n";
		}

		$GLOBALS['phpgw']->db->query("SELECT * FROM phpgw_polls_data WHERE poll_id='$poll_id'",__LINE__,__FILE__);
		while ($GLOBALS['phpgw']->db->next_record())
		{
			$poll_optionText  = $GLOBALS['phpgw']->db->f('option_text');
			$poll_optionCount = $GLOBALS['phpgw']->db->f('option_count');

			$tr_color = $GLOBALS['phpgw']->nextmatchs->alternate_row_color($tr_color);
			$output .= ' <tr bgcolor="' . $tr_color . '">' . "\n";

			if ($poll_optionText != '')
			{
				$output .= "  <td>$poll_optionText</td>\n";

				if ($poll_sum)
				{
					$poll_percent = 100 * $poll_optionCount / $poll_sum;
				}
				else
				{
					$poll_percent = 0;
				}

				if ($poll_percent > 0)
				{
					$poll_percentScale = (int)($poll_percent * 1);
					$output .= '  <td><img src="' . $GLOBALS['phpgw_info']['server']['webserver_url']
						. '/polls/images/pollbar.gif" height="12" width="' . $poll_percentScale
						. '"></td>' . "\n";
				}
				else
				{
					$output .= '  <td>&nbsp;</td>' . "\n";
				}

				$output .= sprintf('  <td> %.2f %% (%d)</td>' . "\n" . ' </tr>' . "\n", $poll_percent, $poll_optionCount);

				$output .= ' </tr>' . "\n";
			}
		}

		if($showtotal)
		{
			$output .= ' <tr bgcolor="' . $GLOBALS['phpgw_info']['theme']['bgcolor'] . '">' . "\n"
				. '  <td>' . lang('Total votes') . ': ' . $poll_sum . '</td>' . "\n"
				. ' </tr>' . "\n";
		}
		
		$output .= '</table>' . "\n";

		return $output;
	}

	function poll_viewResults($poll_id)
	{
		echo poll_getResultsTable($poll_id);
	}

	function poll_getResults($poll_id)
	{
		$ret = array();
		$poll_id = intval($poll_id);

		$GLOBALS['phpgw']->db->query("SELECT SUM(option_count) AS sum FROM phpgw_polls_data WHERE poll_id='$poll_id'",__LINE__,__FILE__);
		$GLOBALS['phpgw']->db->next_record();
		$poll_sum = $GLOBALS['phpgw']->db->f('sum');

		$GLOBALS['phpgw']->db->query("SELECT poll_title FROM phpgw_polls_desc WHERE poll_id='$poll_id'",__LINE__,__FILE__);
		$GLOBALS['phpgw']->db->next_record();

		$poll_title = $GLOBALS['phpgw']->db->f('poll_title');

		$ret[0] = array(
			'title' => $poll_title,
			'votes' => $poll_sum
		);

		// select next vote option
		$GLOBALS['phpgw']->db->query("SELECT * FROM phpgw_polls_data WHERE poll_id='$poll_id'",__LINE__,__FILE__);
		while ($GLOBALS['phpgw']->db->next_record())
		{
			$ret[] = array(
				'text' => $GLOBALS['phpgw']->db->f('option_text'),
				'votes' => $GLOBALS['phpgw']->db->f('option_count')
			);
		}

		return $ret;
	}

	function poll_generateUI($poll_id = '')
	{
		if (! $poll_id)
		{
			$GLOBALS['phpgw']->db->query("select max(poll_id) from phpgw_polls_desc",__LINE__,__FILE__);
			$GLOBALS['phpgw']->db->next_record();
			$poll_id = $GLOBALS['phpgw']->db->f(0);
		}

		if (! verify_uservote($poll_id))
		{
			return False;
		}

		$poll_id = intval($poll_id);
		$GLOBALS['phpgw']->db->query("select poll_title from phpgw_polls_desc where poll_id='$poll_id'",__LINE__,__FILE__);
		$GLOBALS['phpgw']->db->next_record();

		echo "\n";
		echo '<form action="' . $GLOBALS['phpgw']->link('/polls/vote.php') . '" method="post">' . "\n";
		echo '<input type="hidden" name="poll_id" value="' . $poll_id . '">' . "\n";
//		echo '<input type="hidden" name="poll_forwarder" value="' . $poll_forwarder . '">';
		echo '<table border="0" align="center" width="50%">' . "\n"
			. ' <tr>' . "\n"
			. '  <td colspan="2" bgcolor="' . $GLOBALS['phpgw_info']['theme']['th_bg'] . '" align="center">&nbsp;'
			. $GLOBALS['phpgw']->db->f('poll_title') . '&nbsp;</td>' . "\n"
			. ' </tr>' . "\n";

		$GLOBALS['phpgw']->db->query("SELECT * FROM phpgw_polls_data WHERE poll_id='$poll_id'",__LINE__,__FILE__);
		while ($GLOBALS['phpgw']->db->next_record())
		{
			$tr_color = $GLOBALS['phpgw']->nextmatchs->alternate_row_color($tr_color);
			echo ' <tr bgcolor="' . $tr_color . '">' . "\n"
				. '  <td align="center"><input type="radio" name="poll_voteNr" value="'
				. $GLOBALS['phpgw']->db->f('vote_id') . '"></td>' . "\n"
				. '  <td>&nbsp;' . $GLOBALS['phpgw']->db->f('option_text') . '</td>' . "\n"
				. ' </tr>' . "\n";
		}

		echo ' <tr bgcolor="' . $GLOBALS['phpgw_info']['theme']['bgcolor'] . '">' . "\n"
			. '  <td colspan="2">&nbsp;</td>' . "\n"
			. ' </tr>' . "\n"
			. ' <tr bgcolor="' . $GLOBALS['phpgw_info']['theme']['bgcolor'] . '">' . "\n"
			. '  <td colspan="2" align="center">'
			. '   <input name="submit" type="submit" value="' . lang('Vote') . '"></td>' . "\n"
			. ' </tr>' . "\n"
			. '</table>' . "\n" . '</form>' . "\n";
	}

	function display_poll()
	{
		if (! verify_uservote($GLOBALS['poll_settings']['currentpoll']))
		{
			poll_viewResults($GLOBALS['poll_settings']['currentpoll']);
		}
		else
		{
			poll_generateUI($GLOBALS['poll_settings']['currentpoll']);
		}
	}
?>
