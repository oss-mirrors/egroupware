<?php
  /**************************************************************************\
  * phpGroupWare - projects/projectstatistics                                *
  * (http://www.phpgroupware.org)                                            *
  * Written by Bettina Gille  [aeb@hansenet.de]                              *
  *          & Jens Lentfoehr <sw@lf.shlink.de>                              *
  *---------------------------------------------------------                 *
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
  $t->set_file(array( "project_list_t" => "stats_projectlist.tpl",
                      "project_list" => "stats_projectlist.tpl"));
                      
  $t->set_block("project_list_t", "project_list", "list");

  $common_hidden_vars =
   "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
 . "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
 . "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
 . "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
 . "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n";

  $t->set_var(lang_action,lang("Project statistics"));  
  $t->set_var(lang_userlist,lang("User statistics"));
  $t->set_var(userlisturl,$phpgw->link("stats_userlist.php"));  
  $t->set_var(common_hidden_vars,$common_hidden_vars);   
  
  if (! $start)
     $start = 0;
  if ($order)
     $ordermethod = "order by $order $sort";
  else
     $ordermethod = "order by date asc";

  if (! $filter) {
     $filter = "none";
  }


   if ($filter != "private") {
     if ($filter != "none") {
        $filtermethod = " access like '%,$filter,%' ";
     } else {
        $filtermethod = " (coordinator='" . $phpgw_info["user"]["account_id"] 
                      . "' OR owner='" . $phpgw_info["user"]["account_id"] 
                      . "' OR access='public' "
                      . $phpgw->accounts->sql_search("access") . " ) ";
     }
  } else {
     $filtermethod = " coordinator='" . $phpgw_info["user"]["account_id"] . "' ";
  }  


  if ($query) {
     $phpgw->db->query("select count(*) from p_projects where $filtermethod and (title "
                     . "like '%$query%' OR descr like '%$query%')");
     $phpgw->db->next_record();
     if ($phpgw->db->f(0) == 1)
        $t->set_var(total_matchs,lang("your search returned 1 match"));
     else
        $t->set_var(total_matchs,lang("your search returned x matchs",$phpgw->db->f(0)));
  } else {
     $phpgw->db->query("select count(*) from p_projects where $filtermethod");
     $phpgw->db->next_record();                                                                      

     if ($phpgw->db->f(0) > $phpgw_info["user"]["preferences"]["common"]["maxmatchs"])
     $total_matchs = "<br>" . lang("showing x - x of x",($start + 1),
                           ($start + $phpgw_info["user"]["preferences"]["common"]["maxmatchs"]),
                           $phpgw->db->f(0));
  else
     $total_matchs = "<br>" . lang("showing x",$phpgw->db->f(0));
     $t->set_var(total_matchs,$total_matchs);
        }
     if ($phpgw_info["apps"]["timetrack"]["enabled"]) {                                                                                                   
      $customer_sortorder = "customer.company_name";                                                                                                      
      }                                                                                                                                                   
     else {                                                                                                                                               
      $customer_sortorder = "ab_company";                                                                                                                 
     }

    // ===========================================
    // nextmatch variable template-declarations
    // ===========================================

     $next_matchs = $phpgw->nextmatchs->show_tpl("stats_projectlist.php",$start,$phpgw->db->f(0),
                   "&order=$order&filter=$filter&sort="
                 . "$sort&query=$query","85%",$phpgw_info["theme"][th_bg]);
     $t->set_var(next_matchs,$next_matchs);

  // ---------- end nextmatch template --------------------

  // ===========================================
  // list header variable template-declarations
  // ===========================================
  $t->set_var(th_bg,$phpgw_info["theme"][th_bg]);
  $t->set_var(sort_num,$phpgw->nextmatchs->show_sort_order($sort,"num",$order,"stats_projectlist.php",lang("Project ID")));
  $t->set_var(sort_customer,$phpgw->nextmatchs->show_sort_order($sort,"customer",$order,"stats_projectlist.php",lang("Customer")));
  $t->set_var(sort_status,$phpgw->nextmatchs->show_sort_order($sort,"status",$order,"stats_projectlist.php",lang("Status")));
  $t->set_var(sort_title,$phpgw->nextmatchs->show_sort_order($sort,"title",$order,"stats_projectlist.php",lang("Title")));
  $t->set_var(sort_end_date,$phpgw->nextmatchs->show_sort_order($sort,"end_date",$order,"stats_projectlist.php",lang("Date due")));
  $t->set_var(sort_coordinator,$phpgw->nextmatchs->show_sort_order($sort,"coordinator",$order,"stats_projectlist.php",lang("Coordinator")));
  $t->set_var(h_lang_stat,lang("Statistic"));

  // -------------- end header declaration -----------------

    $limit = $phpgw_info["user"]["preferences"]["common"]["maxmatchs"];
//  $limit = $phpgw->db->limit($start);
  
  $db2 = $phpgw->db;
  
  if ($query) {
     $phpgw->db->query("SELECT p_projects.*,accounts.account_firstname,accounts.account_lastname,accounts.account_lid FROM "
                 . "p_projects,accounts WHERE $filtermethod AND accounts.account_id=p_projects.coordinator AND "
                 . "(title like '%$query%' OR descr like '%$query%') $ordermethod limit $limit");
     } 
    else {
     $phpgw->db->query("SELECT p_projects.*,accounts.account_firstname,accounts.account_lastname,accounts.account_lid FROM "
                 . "p_projects,accounts WHERE accounts.account_id=p_projects.coordinator AND $filtermethod "
                 . "$ordermethod limit $limit");
    }

  while ($phpgw->db->next_record()) {
    
    $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
    $title = $phpgw->strip_html($phpgw->db->f("title"));                                                                                                                                   
        if (! $title)  $title  = "&nbsp;";

    $num = $phpgw->strip_html($phpgw->db->f("num"));
    $status = lang($phpgw->db->f("status"));
    $t->set_var(tr_color,$tr_color);

    if ($phpgw->db->f("end_date") == 0)
             $end_dateout = "&nbsp;";
    else {
      $month = $phpgw->common->show_date(time(),"n");
      $day   = $phpgw->common->show_date(time(),"d");
      $year  = $phpgw->common->show_date(time(),"Y");

      $end_date = (60*60) * $phpgw_info["user"]["preferences"]["common"]["tz_offset"];
        if (mktime(2,0,0,$month,$day,$year) >= $phpgw->db->f("end_date"))
        	$end_dateout =  "<font color=\"CC0000\">";

        $end_dateout =  $phpgw->common->show_date($phpgw->db->f("end_date"),$phpgw_info["user"]["preferences"]["common"]["dateformat"]);
        if (mktime(2,0,0,$month,$day,$year) >= $phpgw->db->f("end_date"))
                $end_dateout .= "</font>";
      }
      
     if ($phpgw_info["apps"]["timetrack"]["enabled"]) {
    $db2->query("select ab_id,ab_lastname,ab_firstname,ab_company_id,company_name from "
                        . "addressbook,customers where customers.company_id=addressbook.ab_company_id and "
                        . "addressbook.ab_company_id='" .$phpgw->db->f("customer")."'");
      if ($db2->next_record()) {
        $customerout = $db2->f("company_name")." [ ".$db2->f("ab_firstname"). " " .$db2->f("ab_lastname")." ]";
	}
	else {
	$customerout = $t->set_var("customer","");
	     }
	  }		
	else {		
    $db2->query("select ab_id,ab_lastname,ab_firstname,ab_company from addressbook where "
                        . "ab_id='" .$phpgw->db->f("customer")."'");
                       
    if ($db2->next_record()) {
        if (!$phpgw->db->f("ab_company")) {      
       $customerout = $db2->f("ab_firstname"). " " .$db2->f("ab_lastname");
        }
      else {
       $customerout = $db2->f("ab_company")." [ ".$db2->f("ab_firstname"). " " .$db2->f("ab_lastname")." ]";
       }
     }
    else {
    $customerout = $t->set_var("customer","");
    }
    }
        
    
    $coordinatorout = $phpgw->db->f("account_lid") . " [ ". $phpgw->db->f("account_firstname"). " " 
               . $phpgw->db->f("account_lastname"). " ]"; 
               
      
    // ============================================
    // template declaration for list records
    // ============================================

    $el = $phpgw->link("stats_projectstat.php","id=" . $phpgw->db->f("id") 
                                         . "&sort=$sort&order=$order&"
                                         . "query=$query&start=$start&filter="
                                         . $filter);
      
    $t->set_var(array("num" => $num,
                      "customer" => $customerout,
                      "status" => $status,
    		      "title" => $title,
      		      "end_date" => $end_dateout,
      		      "coordinator" => $coordinatorout,
      		      "stat" =>  "<a href=\"". $el
                                 . "\">". lang("Statistic") . "</a>"));
       $t->parse("list", "project_list", true);

       // -------------- end record declaration ------------------------
  
}

       $t->parse("out", "project_list_t", true);
       $t->p("out");
       // -------------- end Add form declaration ------------------------

$phpgw->common->phpgw_footer();
?>
