<?php
  /**************************************************************************\
  * phpGroupWare - projects/projectstatistics                                *
  * (http://www.phpgroupware.org)                                            *
  * Written by Bettina Gille  [aeb@hansenet.de]                              *
  *          & Jens Lentfoehr <sw@lf.shlink.de>                              * 
  * -------------------------------------------------------                  *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
/* $Id$ */

  $phpgw_info["flags"] = array("currentapp" => "projects", 
                               "enable_nextmatchs_class" => True);
  include("../header.inc.php");

  $t = new Template($phpgw_info["server"]["app_tpl"]);
  $t->set_file(array( "user_list_t" => "stats_userlist.tpl"));
  $t->set_block("user_list_t", "user_list", "list");

  $common_hidden_vars =
   "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
 . "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
 . "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
 . "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
 . "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n";


  $t->set_var(lang_action,lang("User statistics"));
  $t->set_var(common_hidden_vars,$common_hidden_vars);   

  if (! $start)
     $start = 0;
  if ($order)
     $ordermethod = "order by $order $sort";
  else
     $ordermethod = "order by account_lid asc";

  if (! $filter) {
     $filter = "none";
  }

  $filtermethod = "account_status='A'";

  if ($query) {
     $phpgw->db->query("select count(*) from accounts where $filtermethod");
     $phpgw->db->next_record();
     if ($phpgw->db->f(0) == 1)
        $t->set_var(total_matchs,lang("your search returned 1 match"));
     else
        $t->set_var(total_matchs,lang("your search returned x matchs",$phpgw->db->f(0)));
  } else {
     $phpgw->db->query("select count(*) from accounts where $filtermethod");
  }

  $phpgw->db->next_record();                                                                      

  if ($phpgw->db->f(0) > $phpgw_info["user"]["preferences"]["common"]["maxmatchs"])
     $total_matchs = "<br>" . lang("showing x - x of x",($start + 1),
                           ($start + $phpgw_info["user"]["preferences"]["common"]["maxmatchs"]),
                           $phpgw->db->f(0));
  else
     $total_matchs = "<br>" . lang("showing x",$phpgw->db->f(0));


    // ===========================================
    // nextmatch variable template-declarations
    // ===========================================

     $next_matchs = $phpgw->nextmatchs->show_tpl("stats_userlist.php",$start,$phpgw->db->f(0),
                   "&order=$order&filter=$filter&sort="
                 . "$sort&query=$query","85%",$phpgw_info["theme"][th_bg]);
     $t->set_var(next_matchs,$next_matchs);
     $t->set_var(total_matchs,$total_matchs);

  // ---------- end nextmatch template --------------------

  // ===========================================
  // list header variable template-declarations
  // ===========================================
  $t->set_var(th_bg,$phpgw_info["theme"][th_bg]);
  $t->set_var(sort_lid,$phpgw->nextmatchs->show_sort_order($sort,"account_lid",$order,"stats_userlist.php",lang("Username")));
  $t->set_var(sort_firstname,$phpgw->nextmatchs->show_sort_order($sort,"account_firstname",$order,"stats_userlist.php",lang("Firstname")));
  $t->set_var(sort_lastname,$phpgw->nextmatchs->show_sort_order($sort,"account_lastname",$order,"stats_userlist.php",lang("Lastname")));
  $t->set_var(h_lang_stat,lang("Statistic"));

  // -------------- end header declaration -----------------


  $limit = $phpgw->nextmatchs->sql_limit($start);

     $phpgw->db->query("SELECT account_id,account_lid,accounts.account_firstname,accounts.account_lastname FROM "
                 . "accounts WHERE $filtermethod $ordermethod limit $limit");

     while ($phpgw->db->next_record()) {
     $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
     $t->set_var(tr_color,$tr_color);

    // ============================================
    // template declaration for list records
    // ============================================

    $el = $phpgw->link("stats_userstat.php","account_id=" . $phpgw->db->f("account_id") 
                                         . "&sort=$sort&order=$order&"
                                         . "query=$query&start=$start&filter="
                                         . $filter);
      
    $t->set_var(array("lid" => $phpgw->db->f("account_lid"),
                      "firstname" => $phpgw->db->f("account_firstname"),
                      "lastname" => $phpgw->db->f("account_lastname"),
      		      "stat" =>  "<a href=\"". $el
                                 . "\">". lang("Statistic") . "</a>"));
       $t->parse("list", "user_list", true);

       // -------------- end record declaration ------------------------
  }

       $t->parse("out", "user_list_t", true);
       $t->p("out");
       // -------------- end Add form declaration ------------------------

  include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");
?>
