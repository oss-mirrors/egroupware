  /**************************************************************************\
  * phpGroupWare - projects/projectdelivery                                  *
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
  $t->set_file(array( "projects_list_t" => "del_listdelivery.tpl"));
  $t->set_block("projects_list_t", "projects_list", "list");

  $common_hidden_vars =
   "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
 . "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
 . "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
 . "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
 . "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n";

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

  if ($project_id) {
     $phpgw->db->query("select count(*) from p_delivery where project_id=$project_id ");
     $phpgw->db->next_record();
     if ($phpgw->db->f(0) == 1)
        $t->set_var(total_matchs,lang("your search returned 1 match"));
     else
        $t->set_var(total_matchs,lang("your search returned x matchs",$phpgw->db->f(0)));
  } else {
     $phpgw->db->query("select count(*) from p_delivery");
  }
  $phpgw->db->next_record();                                                                      

  if ($phpgw->db->f(0) > $phpgw_info["user"]["preferences"]["common"]["maxmatchs"])
     $total_matchs = "<br>" . lang("showing x - x of x",($start + 1),
                           ($start + $phpgw_info["user"]["preferences"]["common"]["maxmatchs"]),
                           $phpgw->db->f(0));
  else
     $total_matchs = "<br>" . lang("showing x",$phpgw->db->f(0));
?>

<?php
    // ===========================================
    // nextmatch variable template-declarations
    // ===========================================

     $next_matchs = $phpgw->nextmatchs->show_tpl("del_deliverylist.php",$start,$phpgw->db->f(0),
                   "&order=$order&filter=$filter&sort="
                 . "$sort&query=$query","85%",$phpgw_info["theme"][th_bg]);
     $t->set_var(next_matchs,$next_matchs);
     $t->set_var(total_matchs,$total_matchs);

  // ---------- end nextmatch template --------------------

  // ===========================================
  // list header variable template-declarations
  // ===========================================
  $t->set_var(th_bg,$phpgw_info["theme"][th_bg]);
  $t->set_var(sort_num,$phpgw->nextmatchs->show_sort_order($sort,"num",$order,"del_deliverylist.php",lang("num")));
  $t->set_var(sort_customer,$phpgw->nextmatchs->show_sort_order($sort,"customer",$order,"del_deliverylist.php",lang("customer")));
  $t->set_var(sort_title,$phpgw->nextmatchs->show_sort_order($sort,"title",$order,"del_deliverylist.php",lang("title")));
  $t->set_var(sort_date,$phpgw->nextmatchs->show_sort_order($sort,"date",$order,"del_deliverylist.php",lang("date")));
  $t->set_var(sort_sum,$phpgw->nextmatchs->show_sort_order($sort,"sum",$order,"del_deliverylist.php",lang("sum")));
  $t->set_var(h_lang_delivery,lang("delivery"));

  // -------------- end header declaration -----------------

?>

<?php
  $limit = $phpgw->nextmatchs->sql_limit($start);

  if ($phpgw_info["apps"]["timetrack"]["enabled"]) {
  if ($project_id) {
     $phpgw->db->query("SELECT p_delivery.id as id,p_delivery.num,ab_firstname,ab_lastname,ab_company_id"
		 . ",company_name,title,p_delivery.date,p_delivery.project_id as pid "
 		 . "FROM p_delivery,p_projects,addressbook,customers WHERE "
                 . "customers.company_id=addressbook.ab_company_id and "
                 . "p_delivery.customer=ab_company_id AND p_delivery.project_id=p_projects.id "
 		 . "AND p_delivery.project_id=$project_id limit $limit");   //$ordermethod limit $limit");
  } else {
     $phpgw->db->query("SELECT p_delivery.id as id,p_delivery.num,ab_firstname,ab_lastname,ab_company_id,"
		 . "company_name,title,p_delivery.date,p_delivery.project_id as pid "
 		 . "FROM p_delivery,p_projects,addressbook,customers WHERE "
                 . "customers.company_id=addressbook.ab_company_id and "
                 . "p_delivery.customer=ab_company_id AND p_delivery.project_id=p_projects.id limit $limit");
//		 . "$ordermethod limit $limit");
    }
  }
   else {
    if ($project_id) {
     $phpgw->db->query("SELECT p_delivery.id as id,p_delivery.num,ab_firstname,ab_lastname,ab_company " // as customer "
		 . ",title,p_delivery.date,p_delivery.project_id as pid "
 		 . "FROM p_delivery,p_projects,addressbook WHERE "
                 . "p_delivery.customer=ab_id AND p_delivery.project_id=p_projects.id "
 		 . "AND p_delivery.project_id=$project_id $ordermethod limit $limit");
    } else {
     $phpgw->db->query("SELECT p_delivery.id as id,p_delivery.num,ab_firstname,ab_lastname,ab_company "  //as customer "
		 . ",title,p_delivery.date,p_delivery.project_id as pid "
 		 . "FROM p_delivery,p_projects,addressbook WHERE "
                 . "p_delivery.customer=ab_id AND p_delivery.project_id=p_projects.id limit $limit");
//		 . "$ordermethod limit $limit");
     }
   }
  while ($phpgw->db->next_record()) {
    $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
    $title = $phpgw->db->f("title");
    if ($title != "")
       $title = htmlentities(stripslashes($title));
    else
       $title = "&nbsp;";
    $t->set_var(tr_color,$tr_color);

    if ($phpgw->db->f("date") == 0)
             $dateout = "&nbsp;";
    else {
      $month = $phpgw->common->show_date(time(),"n");
      $day   = $phpgw->common->show_date(time(),"d");
      $year  = $phpgw->common->show_date(time(),"Y");

      $date = (60*60) * $phpgw_info["user"]["preferences"]["common"]["tz_offset"];
        if (mktime(2,0,0,$month,$day,$year) >= $phpgw->db->f("date"))
        	$end_dateout =  "<font color=\"CC0000\">";

        $dateout =  $phpgw->common->show_date($phpgw->db->f("date"),$phpgw_info["user"]["preferences"]["common"]["dateformat"]);
        if (mktime(2,0,0,$month,$day,$year) >= $phpgw->db->f("date"))
                $dateout .= "</font>";
      }
    if ($phpgw_info["apps"]["timetrack"]["enabled"]) {
    $customerout = $phpgw->db->f("company_name")." [".
			$phpgw->db->f("ab_firstname")." ".$phpgw->db->f("ab_lastname")."]";
	}
	else {		
	$customerout = $phpgw->db->f("ab_company")." [".
	$phpgw->db->f("ab_firstname")." ".$phpgw->db->f("ab_lastname")."]";
    }
    // ============================================
    // template declaration for list records
    // ============================================

    $t->set_var(array("num" => $phpgw->db->f("num"),
                      "customer" => $customerout,
    		      "title" => $title,
      		      "date" => $dateout,
                      "delivery" => "<a href=\"". $phpgw->link("del_delivery.php","delivery_id=" . $phpgw->db->f("id") 
                                         . "&sort=$sort&order=$order&"
                                         . "query=$query&start=$start&filter="
                                         . "$filter&project_id=". $phpgw->db->f("pid")."&delivery_num=".$phpgw->db->f("num") )
                                 . "\">". lang("delivery") . "</a>"));
       $t->parse("list", "projects_list", true);

       // -------------- end record declaration ------------------------
  }

       $t->parse("out", "projects_list_t", true);
       $t->p("out");
       // -------------- end Add form declaration ------------------------

  include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");
?>
