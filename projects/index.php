<?php
  /**************************************************************************\
  * phpGroupWare - projects                                                  *
  * (http://www.phpgroupware.org)                                            *
  * Written by Bettina Gille  [aeb@hansenet.de]                              * 
  *          & Jens Lentfoehr <sw@lf.shlink.de>                              *
  * -------------------------------------------------------                  *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/


  $phpgw_info["flags"] = array("currentapp" => "projects", "enable_nextmatchs_class" => True);
  include("../header.inc.php");

  $t = new Template($phpgw_info["server"]["app_tpl"]);
  $t->set_file(array( "projects_list_t" => "list.tpl"));
  $t->set_block("projects_list_t", "projects_list", "list");

  $common_hidden_vars =
   "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
  . "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
  . "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
  . "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
  . "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n";

  $t->set_var(lang_action,lang("project list"));
  $t->set_var(actionurl,$phpgw->link("add.php"));
  $t->set_var(lang_activities,lang("activities list"));
  $t->set_var(activitiesurl,$phpgw->link("activities.php"));
  $t->set_var(common_hidden_vars,$common_hidden_vars);   

  $isadmin = isprojectadmin();
  
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
                      . "' OR access='public'"
                      . $phpgw->accounts->sql_search("access") . " ) ";
     }
  } else {
     $filtermethod = " coordinator='" . $phpgw_info["user"]["account_id"] . "' ";
  }  

  if ($query) {
     $phpgw->db->query("select count(*) from p_projects where $filtermethod and descr "
                    . "like '%$query%'");
     $phpgw->db->next_record();
     if ($phpgw->db->f(0) == 1)
        $t->set_var(total_matchs,lang("your search returned 1 match"));
     else
        $t->set_var(total_matchs,lang("your search returned x matchs",$phpgw->db->f(0)));
  } else {
     $phpgw->db->query("select count(*) from p_projects where $filtermethod");
    $phpgw->db->next_record();
   }

   if ($phpgw_info["apps"]["timetrack"]["enabled"]) {                                                                                                        
     $customer_sortorder = "customer.company_name";                                                                                                                 
  } else {                                                                                                                                                  
     $customer_sortorder = "ab_company";                                                                                                                     
  }
     
 //  $phpgw->db->next_record();                                                                      

  if ($phpgw->db->f(0) > $phpgw_info["user"]["preferences"]["common"]["maxmatchs"])
     $total_matchs = "<br>" . lang("showing x - x of x",($start + 1),
                           ($start + $phpgw_info["user"]["preferences"]["common"]["maxmatchs"]),
                           $phpgw->db->f(0));
  else
     $total_matchs = "<br>" . lang("showing x",$phpgw->db->f(0));
     $phpgw->db->next_record();
?>

<?php
    // ===========================================
    // nextmatch variable template-declarations
    // ===========================================

     $next_matchs = $phpgw->nextmatchs->show_tpl("index.php",$start,$phpgw->db->f(0),
                   "&order=$order&filter=$filter&sort="
                 . "$sort&query=$query","85%",$phpgw_info["theme"][th_bg]);
     $t->set_var(next_matchs,$next_matchs);
     $t->set_var(total_matchs,$total_matchs);

  // ---------- end nextmatch template --------------------

  // ===========================================
  // list header variable template-declarations
  // ===========================================
  $t->set_var(th_bg,$phpgw_info["theme"][th_bg]);
  $t->set_var(sort_num,$phpgw->nextmatchs->show_sort_order($sort,"num",$order,"index.php",lang("num")));
  $t->set_var(sort_customer,$phpgw->nextmatchs->show_sort_order($sort,"customer",$order,"index.php",lang("customer")));
  $t->set_var(sort_status,$phpgw->nextmatchs->show_sort_order($sort,"status",$order,"index.php",lang("status")));
  $t->set_var(sort_title,$phpgw->nextmatchs->show_sort_order($sort,"title",$order,"index.php",lang("title")));
  $t->set_var(sort_end_date,$phpgw->nextmatchs->show_sort_order($sort,"end_date",$order,"index.php",lang("date due")));
  $t->set_var(sort_coordinator,$phpgw->nextmatchs->show_sort_order($sort,"coordinator",$order,"index.php",lang("coordinator")));
  $t->set_var(h_lang_edit,lang("edit"));
  $t->set_var(h_lang_delete,lang("delete"));             

  // -------------- end header declaration -----------------

?>

<?php
  
  $limit = $phpgw->nextmatchs->sql_limit($start);
  
  $db2 = $phpgw->db;
  
  if ($query) {
     $phpgw->db->query("SELECT p_projects.*,account_firstname,account_lastname FROM "
                 . "p_projects,accounts WHERE $filtermethod AND account_id=p_projects.coordinator AND"
                 . " descr like '%$query%' $ordermethod limit $limit");
  } else {
     $phpgw->db->query("SELECT p_projects.*,account_firstname,account_lastname FROM "
                 . "p_projects,accounts WHERE account_id=p_projects.coordinator AND $filtermethod "
                 . "$ordermethod limit $limit");
  }

  while ($phpgw->db->next_record()) {
    
    $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
    $title = $phpgw->db->f("title");
    if ($title != "")
       $title = htmlentities(stripslashes($title));
    else
       $title = "&nbsp;";

    $num = $phpgw->db->f("num");
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
        $customerout = $db2->f("company_name")." [ ".$db2->f("ab_lastname")." ]";
	}
	else {
	$customerout = $t->set_var("customer","");
	     }
	  }		
	else {		
    $db2->query("select ab_id,ab_lastname,ab_firstname,ab_company from addressbook where "
                        . "ab_id='" .$phpgw->db->f("customer")."'");
                       
    if ($db2->next_record()) {
      $customerout = $db2->f("ab_company")." [ ".$db2->f("ab_lastname")." ]";
    }
    else {
    $customerout = $t->set_var("customer","");
    }
    }
    
    $coordinatorout = htmlentities($phpgw->db->f("account_firstname") . " "
               . $phpgw->db->f("account_lastname")); 
               
      
    if($isadmin==1) {
        $edit = $phpgw->common->check_owner($phpgw_info["user"]["account_id"],"edit.php",
                                         lang("edit"),"id=" . $phpgw->db->f("id")
                                         . "&sort=$sort&order=$order&"
                                         . "query=$query&start=$start&filter="
                                         . "$filter");
       $delete = $phpgw->common->check_owner($phpgw_info["user"]["account_id"],"delete.php",
                                           lang("delete"),"id=" . $phpgw->db->f("id")
                                         . "&sort=$sort&order=$order&"
                                         . "query=$query&start=$start&filter="
                                         . "$filter");
    } else { 
       $edit = $phpgw->common->check_owner($phpgw->db->f("coordinator"),"edit.php",
                                         lang("edit"),"id=" . $phpgw->db->f("id")
                                         . "&sort=$sort&order=$order&"
                                         . "query=$query&start=$start&filter="
                                         . "$filter");
       $delete = $phpgw->common->check_owner($phpgw->db->f("coordinator"),"delete.php",
                                           lang("delete"),"id=" . $phpgw->db->f("id")
                                         . "&sort=$sort&order=$order&"
                                         . "query=$query&start=$start&filter="
                                         . "$filter");
    } 
    
    // ============================================
    // template declaration for list records
    // ============================================
      
    $t->set_var(array("num" => $num,
                      "customer" => $customerout,
                      "status" => $status,
    		      "title" => $title,
      		      "end_date" => $end_dateout,
      		      "coordinator" => $coordinatorout,
      		      "edit" => $edit,
                      "delete" => $delete));
       $t->parse("list", "projects_list", true);

       // -------------- end record declaration ------------------------
  }

      // ============================================
      // template declaration for Add Form
      // ============================================

       $t->set_var(lang_add,lang("add"));
       $t->parse("out", "projects_list_t", true);
       $t->p("out");
       // -------------- end Add form declaration ------------------------


  include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");
?>
