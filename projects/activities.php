<?php
  /**************************************************************************\
  * phpGroupWare - projects                                                  *
  * (http://www.phpgroupware.org)                                            *
  * Written by Bettina Gille  [aeb@hansenet.de]                              * 
  *          & Jens Lentfoehr <sw@lf.shlink.de>                              *
  * --------------------------------------------------------                 *
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
  $t->set_file(array( "activities_list_t" => "listactivities.tpl",
                      "activities_list"   => "listactivities.tpl"));
  $t->set_block("activities_list_t", "activities_list", "list");

   if (isset($phpgw_info["user"]["preferences"]["common"]["currency"])) {                                                                                                               
   $currency = $phpgw_info["user"]["preferences"]["common"]["currency"];                                                                                                                
   $t->set_var("error","");                                                                                                                                               
   }                                                                                                                                                                                    
   else {                                                                                                                                                                               
   $t->set_var("error",lang("Please select your currency in preferences!"));                                                                                              
   }

  $common_hidden_vars =
   "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
 . "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
 . "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
 . "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
 . "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n";

  $t->set_var(lang_action,lang("Activities list"));
  $t->set_var(actionurl,$phpgw->link("addactivity.php"));
  $t->set_var(lang_projects,lang("Project list"));
  $t->set_var(projectsurl,$phpgw->link("index.php"));
  $t->set_var(common_hidden_vars,$common_hidden_vars);   

  if (! $start)
     $start = 0;
  if ($order)
     $ordermethod = "order by $order $sort";
  else
     $ordermethod = "order by num asc";

  if (! $filter) {
     $filter = "none";
  }

  if ($query) {
     $phpgw->db->query("select count(*) from p_activities where descr "
                    . "like '%$query%'");
     $phpgw->db->next_record();
     if ($phpgw->db->f(0) == 1)
        $t->set_var(total_matchs,lang("your search returned 1 match"));
     else
        $t->set_var(total_matchs,lang("your search returned x matchs",$phpgw->db->f(0)));
  } else {
     $phpgw->db->query("select count(*) from p_activities");
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

     $next_matchs = $phpgw->nextmatchs->show_tpl("activities.php",$start,$phpgw->db->f(0),
                   "&order=$order&filter=$filter&sort="
                 . "$sort&query=$query","85%",$phpgw_info["theme"][th_bg]);
     $t->set_var(next_matchs,$next_matchs);
     $t->set_var(total_matchs,$total_matchs);

  // ---------- end nextmatch template --------------------

  // ===========================================
  // list header variable template-declarations
  // ===========================================
  
  $t->set_var(th_bg,$phpgw_info["theme"][th_bg]);
  $t->set_var(currency,$currency);
  $t->set_var(sort_num,$phpgw->nextmatchs->show_sort_order($sort,"num",$order,"activities.php",lang("Activity ID")));
  $t->set_var(sort_descr,$phpgw->nextmatchs->show_sort_order($sort,"descr",$order,"activities.php",lang("Description")));
  $t->set_var(sort_billperae,$phpgw->nextmatchs->show_sort_order($sort,"billperae",$order,"activities.php",lang("Bill per workunit")));
  $t->set_var(sort_minperae,$phpgw->nextmatchs->show_sort_order($sort,"minperae",$order,"activities.php",lang("Minutes per workunit")));
  $t->set_var(h_lang_edit,lang("Edit"));
  $t->set_var(h_lang_delete,lang("Delete"));             

  // -------------- end header declaration -----------------


  $limit = $phpgw->nextmatchs->sql_limit($start);

  if ($query) {
     $phpgw->db->query("SELECT * FROM "
                 . "p_activities WHERE "
                 . " descr like '%$query%' $ordermethod limit $limit");
  } else {
     $phpgw->db->query("SELECT * FROM "
                 . "p_activities "
                 . "$ordermethod limit $limit");
  }

  while ($phpgw->db->next_record()) {
    $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
    $num = $phpgw->db->f("num");
    if ($num != "")
       $num = htmlentities(stripslashes($num));
    else
       $num = "&nbsp;";

    $descr = $phpgw->db->f("descr");
    $billperae = $phpgw->db->f("billperae");
    $minperae = $phpgw->db->f("minperae");
    $t->set_var(tr_color,$tr_color);

      
    // ============================================
    // template declaration for list records
    // ============================================
      
    $el = $phpgw->link("editactivity.php","id=" . $phpgw->db->f("id") 
                                         . "&sort=$sort&order=$order&"
                                         . "query=$query&start=$start&filter="
                                         . $filter);
    $dl = $phpgw->link("deleteactivity.php","id=" . $phpgw->db->f("id") 
                                         . "&sort=$sort&order=$order&"
                                         . "query=$query&start=$start&filter="
                                         . $filter);
    $t->set_var(array("num" => $num,
                      "descr" => $descr,
    		      "billperae" => $billperae,
      		      "minperae" => $minperae,
		      "edit" =>  "<a href=\"". $el
                                 . "\">". lang("Edit") . "</a>",
      		      "delete" =>  "<a href=\"". $dl
                                 . "\">". lang("Delete") . "</a>"));
       $t->parse("list", "activities_list", true);

       // -------------- end record declaration ------------------------
  }

      // ============================================
      // template declaration for Add Form
      // ============================================

       $t->set_var(lang_add,lang("Add"));
       $t->parse("out", "activities_list_t", true);
       $t->p("out");
       // -------------- end Add form declaration ------------------------


  include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");
?>