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
  
  $phpgw->db->query("select * from phpgw_polls_settings");
  while ($phpgw->db->next_record()) {
     //echo "<br>TEST: " . $phpgw->db->f("setting_name") . " - " . $phpgw->db->f("setting_value");
     $poll_settings[$phpgw->db->f("setting_name")] = $phpgw->db->f("setting_value");
  }

  function add_template_row(&$tpl,$label,$value)
  {
     global $phpgw;

     $tpl->set_var("tr_color",$phpgw->nextmatchs->alternate_row_color());
     $tpl->set_var("td_1",$label);
     $tpl->set_var("td_2",$value);
     $tpl->parse("rows","row",True);
  }
  
  function verify_uservote($poll_id)
  {
     global $phpgw, $phpgw_info, $poll_settings;
     $db = $phpgw->db;

     if ($poll_settings["allow_multiable_votes"]) {
        return True;
     }

     $db->query("select count(*) from phpgw_polls_user where user_id='" . $phpgw_info["user"]["account_id"]
              . "' and poll_id='$poll_id'",__LINE__,__FILE__);
     $db->next_record();

     if ($db->f(0) == 0) {
        return True;
     } else {
        return False;
     }
  }
  
  function poll_viewResults($poll_id)
  {
     global $phpgw, $phpgw_info;

     $db = $phpgw->db;

     $db->query("SELECT SUM(option_count) AS sum FROM phpgw_polls_data WHERE poll_id='$poll_id'",__LINE__,__FILE__);
     $db->next_record();
     $poll_sum = (int)$db->f(0);

     $db->query("select poll_title from phpgw_polls_desc where poll_id='$poll_id'",__LINE__,__FILE__);
     $db->next_record();

     echo '<p><table border="0" align="center" width="50%">';
     echo '<tr><td colspan="3" bgcolor="' . $phpgw_info["theme"]["th_bg"] . '" align="center">'
        . $db->f("poll_title") . '</td></tr>';
     $db->query("SELECT * FROM phpgw_polls_data WHERE poll_id='$poll_id'",__LINE__,__FILE__);
     while ($db->next_record()) {
        $poll_optionText  = $db->f("option_text");
        $poll_optionCount = $db->f("option_count");

        $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
        echo '<tr bgcolor="' . $tr_color . '">';

        if ($poll_optionText != "") {
           echo "<td>$poll_optionText</td>";

           if ($poll_sum) {
              $poll_percent = 100 * $poll_optionCount / $poll_sum;
           } else {
              $poll_percent = 0;
           }

           if ($poll_percent > 0) {
              $poll_percentScale = (int)($poll_percent * 1);
              echo '<td><img src="' . $phpgw_info["server"]["webserver_url"]
                 . '/polls/images/pollbar.gif" height="12" width="' . $poll_percentScale
                 . '"></td>';
           } else {
              echo "<td>&nbsp;</td>";
           }

           printf("<td> %.2f %% (%d)</td></tr>", $poll_percent, $poll_optionCount);

           echo "</tr>";
         }

     }

     echo '<tr bgcolor="' . $phpgw_info["theme"]["bgcolor"] . '"><td>' . lang("Total votes") . ': '
        . $poll_sum . '</td></tr></table>';
  }

  
  function poll_getResults($poll_id)
  {
     global $phpgw;

     $db = $phpgw->db;
     $ret = array();
    
     $db->query("SELECT SUM(option_count) AS sum FROM phpgw_polls_data WHERE poll_id='$poll_id'",__LINE__,__FILE__);
     $db->next_record();
     $poll_sum = $db->f("sum");
    
     $db->query("SELECT poll_title FROM phpgw_polls_desc WHERE poll_id='$poll_id'",__LINE__,__FILE__);
     $db->next_record();
    
     $poll_title = $db->f("poll_title");
       
     $ret[0] = array("title" => $poll_title, "votes" => $poll_sum);
    
     // select next vote option
     $db->query("SELECT * FROM phpgw_polls_data WHERE poll_id='$poll_id'",__LINE__,__FILE__);
     while ($db->next_record()) {
        $ret[] = array("text" => $db->f("option_text"), "votes" => $db->f("option_count"));
     }

     return($ret);
  }


  function poll_generateUI($poll_id = "")
  {
     global $phpgw, $phpgw_info;
    
     $db = $phpgw->db;

     if (! $poll_id) {
        $db->query("select max(poll_id) from phpgw_polls_desc",__LINE__,__FILE__);
        $db->next_record();
        $poll_id = $db->f(0);
     }
    
     if (! verify_uservote($poll_id)) {
        return False;
     }

     $db->query("select poll_title from phpgw_polls_desc where poll_id='$poll_id'",__LINE__,__FILE__);
     $db->next_record();

     echo '<table border="0" align="center" width="50%">'
        . '<tr><td colspan="2" bgcolor="' . $phpgw_info["theme"]["th_bg"] . '" align="center">&nbsp;'
        . $db->f("poll_title") . '&nbsp;</td></tr>';
     echo '<form action="' . $phpgw->link("vote.php") . '" method="post">';
     echo '<input type="hidden" name="poll_id" value="' . $poll_id . '">';
//     echo '<input type="hidden" name="poll_forwarder" value="' . $poll_forwarder . '">';

     $db->query("SELECT * FROM phpgw_polls_data WHERE poll_id='$poll_id'",__LINE__,__FILE__);
     while ($db->next_record()) {
        $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
        echo '<tr bgcolor="' . $tr_color . '"><td align="center"><input type="radio" name="poll_voteNr" value="'
           . $db->f("vote_id") . '"></td><td>&nbsp;' . $db->f("option_text") . '</td></tr>';
     }

     echo '<tr bgcolor="' . $phpgw_info["theme"]["bgcolor"] . '"><td colspan="2">&nbsp;</td></tr>'
        . '<tr bgcolor="' . $phpgw_info["theme"]["bgcolor"] . '"><td colspan="2" align="center">'
        . '<input name="submit" type="submit" value="Vote"></td></tr>'
        . '</table></form>';
  }
  
  function display_poll()
  {
     global $poll_settings;

     if (! verify_uservote($poll_settings["currentpoll"])) {
        poll_viewResults($poll_settings["currentpoll"]);
     } else {
        poll_generateUI($poll_settings["currentpoll"]);
     }
  }
?>
