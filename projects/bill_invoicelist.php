<?php
  /**************************************************************************\
  * phpGroupWare - projects/projectbilling                                   *
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
  $t->set_file(array( "projects_list_t" => "bill_listinvoice.tpl"));
  $t->set_block("projects_list_t", "projects_list", "list");

  $t->set_var("lang_action",lang("Invoice list"));

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
     $phpgw->db->query("select count(*) from p_invoice where project_id=$project_id");
     $phpgw->db->next_record();
     if ($phpgw->db->f(0) == 1)
        $t->set_var(total_matchs,lang("your search returned 1 match"));
     else
        $t->set_var(total_matchs,lang("your search returned x matchs",$phpgw->db->f(0)));
  } else {
     $phpgw->db->query("select count(*) from p_invoice");
    $phpgw->db->next_record();
  }

  if ($phpgw->db->f(0) > $phpgw_info["user"]["preferences"]["common"]["maxmatchs"])
     $total_matchs = "<br>" . lang("showing x - x of x",($start + 1),
                           ($start + $phpgw_info["user"]["preferences"]["common"]["maxmatchs"]),
                           $phpgw->db->f(0));
  else
     $total_matchs = "<br>" . lang("showing x",$phpgw->db->f(0));
     $phpgw->db->next_record();


    // ===========================================
    // nextmatch variable template-declarations
    // ===========================================

     $next_matchs = $phpgw->nextmatchs->show_tpl("bill_invoicelist.php",$start,$phpgw->db->f(0),
                   "&order=$order&filter=$filter&sort="
                 . "$sort&query=$query","85%",$phpgw_info["theme"][th_bg]);
     $t->set_var(next_matchs,$next_matchs);
     $t->set_var(total_matchs,$total_matchs);

  // ---------- end nextmatch template --------------------

  // ===========================================
  // list header variable template-declarations
  // ===========================================
  $t->set_var(th_bg,$phpgw_info["theme"][th_bg]);
  $t->set_var(sort_num,$phpgw->nextmatchs->show_sort_order($sort,"num",$order,"bill_invoicelist.php",lang("Invoice ID")));
  $t->set_var(sort_customer,$phpgw->nextmatchs->show_sort_order($sort,"customer",$order,"bill_invoicelist.php",lang("Customer")));
  $t->set_var(sort_title,$phpgw->nextmatchs->show_sort_order($sort,"title",$order,"bill_invoicelist.php",lang("Title")));
  $t->set_var(sort_date,$phpgw->nextmatchs->show_sort_order($sort,"date",$order,"bill_invoicelist.php",lang("Date")));
  $t->set_var(sort_sum,$phpgw->nextmatchs->show_sort_order($sort,"sum",$order,"bill_invoicelist.php",lang("Sum")));
  $t->set_var(h_lang_invoice,lang("Invoice"));

  // -------------- end header declaration -----------------


  $limit = $phpgw->nextmatchs->sql_limit($start);
  
  if ($phpgw_info["apps"]["timetrack"]["enabled"]) {
  
  if ($project_id) {
     $phpgw->db->query("SELECT p_invoice.id as id,p_invoice.num,ab_firstname,ab_lastname,ab_company_id "
		 . ",company_name,title,p_invoice.date,sum,p_invoice.project_id as pid,p_invoice.customer "
 		 . "FROM p_invoice,p_projects,addressbook,customers WHERE "
                 . "p_invoice.customer=ab_company_id AND customers.company_id=addressbook.ab_company_id "
                 . "AND p_invoice.project_id=p_projects.id "
 		 . "AND p_invoice.project_id=$project_id $ordermethod limit $limit");
  } else {
     $phpgw->db->query("SELECT p_invoice.id as id,p_invoice.num,ab_firstname,ab_lastname,ab_company_id, "
		 . "company_name,title,p_invoice.date,sum,p_invoice.project_id as pid,p_invoice.customer "
 		 . "FROM p_invoice,p_projects,addressbook,customers WHERE "
                 . "p_invoice.customer=ab_company_id AND customers.company_id=addressbook.ab_company_id "
                 . "AND p_invoice.project_id=p_projects.id $ordermethod limit $limit");
  
    } 
  }
  else {
  if ($project_id) {
     $phpgw->db->query("SELECT p_invoice.id as id,p_invoice.num,ab_firstname,ab_lastname,ab_company "
		 . ",title,p_invoice.date,sum,p_invoice.project_id as pid,p_invoice.customer "
 		 . "FROM p_invoice,p_projects,addressbook WHERE "
                 . "p_invoice.customer=ab_id AND p_invoice.project_id=p_projects.id "
 		 . "AND p_invoice.project_id=$project_id $ordermethod limit $limit");
  } else {
     $phpgw->db->query("SELECT p_invoice.id as id,p_invoice.num,ab_firstname,ab_lastname,ab_company "
		 . ",title,p_invoice.date,sum,p_invoice.project_id as pid,p_invoice.customer "
 		 . "FROM p_invoice,p_projects,addressbook WHERE "
                 . "p_invoice.customer=ab_id AND p_invoice.project_id=p_projects.id " 
                 . "$ordermethod limit $limit");
  
    }
  }
  
  while ($phpgw->db->next_record()) {
    $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);

    $title = $phpgw->strip_html($phpgw->db->f("title"));                                                                                                                                   
    if (! $title)  $title  = "&nbsp;";

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
        if (!$phpgw->db->f("ab_company")) {	
        $customerout = $phpgw->db->f("ab_firstname")." ".$phpgw->db->f("ab_lastname");
        }
       else {    
       $customerout = $phpgw->db->f("ab_company")." [".
			$phpgw->db->f("ab_firstname")." ".$phpgw->db->f("ab_lastname")."]";
         }
        }
    $sum = $phpgw->db->f("sum");

    // ============================================
    // template declaration for list records
    // ============================================

    $t->set_var(array("num" => $phpgw->strip_html($phpgw->db->f("num")),
                      "customer" => $customerout,
    		      "title" => $title,
      		      "date" => $dateout,
      		      "sum" => $sum,
                      "invoice" => "<a href=\"". $phpgw->link("bill_invoice.php","invoice_id=" . $phpgw->db->f("id") 
                                         . "&sort=$sort&order=$order&"
                                         . "query=$query&start=$start&filter="
                                         . "$filter&project_id=". $phpgw->db->f("pid")."&invoice_num=".$phpgw->db->f("num") )
                                 . "\">". lang("Invoice") . "</a>"));
       $t->parse("list", "projects_list", true);

       // -------------- end record declaration ------------------------
  }

       $t->parse("out", "projects_list_t", true);
       $t->p("out");
       // -------------- end Add form declaration ------------------------

$phpgw->common->phpgw_footer();
?>
