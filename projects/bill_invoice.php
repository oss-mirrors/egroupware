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
  $t->set_file(array( "projecthours_list_t" => "bill_listhours.tpl"));
  $t->set_block("projecthours_list_t", "projecthours_list", "list");
  
  $t->set_var("lang_action",lang("Invoice"));
  $t->set_var(date_hint,"");
  
   if (isset($phpgw_info["user"]["preferences"]["common"]["currency"])) {                                                                                                        
   $currency = $phpgw_info["user"]["preferences"]["common"]["currency"];                                                                                                         
   $phpgw->template->set_var("error","");                                                                                                                                        
   }                                                                                                                                                                             
   else {                                                                                                                                                                        
   $phpgw->template->set_var("error",lang("Please select your currency in preferences!"));                                                                                       
   }  

  $db2 = $phpgw->db;
  
  if(($Update) or ($Invoice)) {
   if (checkdate($month,$day,$year)) {
       $date = mktime(2,0,0,$month,$day,$year);
   } else {
       if ($month && $day && $year) {
          $t->set_var(date_hint,lang("You have entered an invailed date"));
          $date=0;
          unset($Invoice);
       }
   }
   if(!$invoice_id) {
       $phpgw->db->query("INSERT INTO p_invoice (sum,project_id,date) VALUES (0,$project_id,'date')");
       $phpgw->db->query("SELECT LAST_INSERT_ID() AS id");
       $phpgw->db->next_record();
       $invoice_id = $phpgw->db->f("id");
      } 
     else {
       $phpgw->db->query("UPDATE p_invoice set date='$date' WHERE id=$invoice_id");
   }
   $db2->query("SELECT hours_id FROM p_invoicepos WHERE invoice_id=$invoice_id");
   while ($db2->next_record()) {
     $phpgw->db->query("UPDATE p_hours SET status='done' WHERE id=".$db2->f("hours_id"));
   }
   $phpgw->db->query("DELETE FROM p_invoicepos WHERE invoice_id=$invoice_id");
   while($select && $entry=each($select)) {
        $phpgw->db->query("INSERT INTO p_invoicepos (invoice_id,hours_id) VALUES ($invoice_id,$entry[0])");
        $phpgw->db->query("UPDATE p_hours SET status='billed' WHERE id=$entry[0]");
      }
    }

   if($Invoice) {
    $phpgw->db->query("SELECT num FROM p_invoice WHERE num='$invoice_num' AND id!=$invoice_id");
    if($phpgw->db->next_record()) {
    $t->set_var(invoice_hint,lang("duplicate invoicenum"));
    } else {
    $phpgw->db->query("SELECT customer FROM p_projects WHERE id=$project_id");
    $phpgw->db->next_record();
    $customer=$phpgw->db->f("customer");
    $phpgw->db->query("UPDATE p_invoice set customer='$customer' WHERE id=$invoice_id");
    $t->set_var(invoice_hint,"");
    $phpgw->db->query("UPDATE p_invoice SET date='".time()."',num='$invoice_num' WHERE id=$invoice_id");
    $phpgw->db->query("SELECT sum(billperae*ceiling(minutes/minperae)) as sum FROM p_hours,p_invoicepos "
                     ."WHERE p_invoicepos.invoice_id=$invoice_id AND p_hours.id=p_invoicepos.hours_id");
    $phpgw->db->next_record();
    $db2->query("UPDATE p_invoice SET sum='".$phpgw->db->f("sum")."' WHERE id=$invoice_id");

  }
} else {
    $t->set_var(invoice_hint,"");
}


  $common_hidden_vars =
   "<input type=\"hidden\" name=\"invoice_id\" value=\"$invoice_id\">\n"
 . "<input type=\"hidden\" name=\"project_id\" value=\"$project_id\">\n";

  $t->set_var(common_hidden_vars,$common_hidden_vars);   

  if (! $start)
     $start = 0;
  $ordermethod = "order by date asc";


//-------------- list header variable template-declarations------------------------

  $t->set_var(th_bg,$phpgw_info["theme"][th_bg]);

  $t->set_var(currency,$currency);
  $t->set_var(sort_activity,lang("Activity"));
  $t->set_var(sort_remark,lang("Remark"));
  $t->set_var(sort_status,lang("Status"));
  $t->set_var(sort_date,lang("Date"));
  $t->set_var(sort_aes,lang("Workunits"));
  $t->set_var(sort_billperae,lang("Bill per workunit"));
  $t->set_var(sort_sum,lang("Sum"));
  $t->set_var(h_lang_select,lang("Select"));
  $t->set_var(h_lang_edithour,lang("Edit hours"));
  $t->set_var(lang_update,lang("Update"));
  $t->set_var(lang_createinvoice,lang("Create invoice"));
  $t->set_var(actionurl,$phpgw->link("bill_invoice.php"));
  $t->set_var(lang_print_invoice,lang("Print invoice"));
  $t->set_var(print_invoice,$phpgw->link("bill_invoiceshow.php","invoice_id=$invoice_id"));

// ------------------------ end header declaration ------------------------------------


  if ($phpgw_info["apps"]["timetrack"]["enabled"]) {
    $phpgw->db->query("select title,ab_company_id,ab_lastname,ab_firstname,company_name from "
                        . "p_projects,addressbook,customers where id='$project_id' AND "
			. "customers.company_id=addressbook.ab_company_id and p_projects.customer=ab_company_id");
       if ($phpgw->db->next_record()) {
       $t->set_var(project,$phpgw->db->f("title"));
       $t->set_var(customer,$phpgw->db->f("company_name")." [ ".$phpgw->db->f("ab_firstname")." ".
                                                                $phpgw->db->f("ab_lastname")." ]");
      }
      else {
      $t->set_var(project,lang("no customer selected"));
      $t->set_var(customer,lang("no customer selected"));
           }
      }
      else {
      $phpgw->db->query("SELECT title,ab_company,ab_lastname,ab_firstname FROM p_projects,addressbook "
                  . "WHERE id='$project_id' AND p_projects.customer=ab_id");
  
  if($phpgw->db->next_record()) {
    $t->set_var(project,$phpgw->db->f("title"));
    $t->set_var(customer,$phpgw->db->f("ab_company")." [".
		$phpgw->db->f("ab_firstname")." ".$phpgw->db->f("ab_lastname")."]");
  } else {
    $t->set_var(project,lang("no customer selected"));
    $t->set_var(customer,lang("no customer selected"));
  }
  }
  $t->set_var(title_project,lang("title"));
  $t->set_var(title_customer,lang("customer"));
  
  $t->set_var(title_invoice_num,lang("Invoice ID"));
  if(!$invoice_num) {
    $phpgw->db->query("SELECT max(num) AS max FROM p_invoice");
    $t->set_var(title_invoice_num,lang("Invoice ID"));
    if($phpgw->db->next_record()) {
      $t->set_var(invoice_num,(int)($phpgw->db->f("max"))+1);
    } else {
      $t->set_var(invoice_num,"1");
    }
  } else {
      $t->set_var(invoice_num,$invoice_num);
  }

  if(!$invoice_id) {
    $date=0;
    $phpgw->db->query("SELECT p_hours.id as id,p_hours.remark,p_activities.descr,status,date,"
                  . "end_date,minutes,p_hours.minperae,p_hours.billperae FROM "
                  . "p_activities,p_hours,p_projectactivities WHERE p_hours.status='done' AND "
                  . "p_hours.activity_id=p_activities.id AND p_projectactivities.project_id='$project_id' "
                  . "AND p_projectactivities.billable='Y' AND "
                  . "p_projectactivities.activity_id=p_hours.activity_id $ordermethod");
  } else {
    $phpgw->db->query("SELECT date FROM p_invoice WHERE id=$invoice_id");
    $phpgw->db->next_record();
    $date=$phpgw->db->f("date");    
    $phpgw->db->query("SELECT p_hours.id as id,p_hours.remark,p_activities.descr,status,date,"
                  . "end_date,minutes,p_hours.minperae,p_hours.billperae FROM "
                  . "p_activities,p_hours,p_projectactivities,p_invoicepos WHERE status='billed' AND "
                  . "p_hours.activity_id=p_activities.id AND p_projectactivities.project_id='$project_id' "
                  . "AND p_projectactivities.billable='Y' AND p_invoicepos.hours_id=p_hours.id AND "
                  . "p_projectactivities.activity_id=p_hours.activity_id AND p_invoicepos.invoice_id=$invoice_id $ordermethod");
  }

  if ($date != 0) {
    	$n_month[$phpgw->common->show_date($date,"n")] = " selected";
	$n_day			 = $phpgw->common->show_date($date,"d");
	$n_year			 = $phpgw->common->show_date($date,"Y");
  } else {
        $n_month[date("n",time())]		 = " selected";
	$n_day			 = date("j",time());
	$n_year			 = date("Y",time());
  }
  $date_formatorder ="<select name=month>\n"
               . "<option value=\"\"$n_month[0]> </option>\n"
               . "<option value=\"1\"$n_month[1]>" . lang("January") . "</option>\n" 
               . "<option value=\"2\"$n_month[2]>" . lang("February") . "</option>\n"
               . "<option value=\"3\"$n_month[3]>" . lang("March") . "</option>\n"
               . "<option value=\"4\"$n_month[4]>" . lang("April") . "</option>\n"
               . "<option value=\"5\"$n_month[5]>" . lang("May") . "</option>\n"
               . "<option value=\"6\"$n_month[6]>" . lang("June") . "</option>\n" 
               . "<option value=\"7\"$n_month[7]>" . lang("July") . "</option>\n"
               . "<option value=\"8\"$n_month[8]>" . lang("August") . "</option>\n"
               . "<option value=\"9\"$n_month[9]>" . lang("September") . "</option>\n"
               . "<option value=\"10\"$n_month[10]>" . lang("October") . "</option>\n"
               . "<option value=\"11\"$n_month[11]>" . lang("November") . "</option>\n"
               . "<option value=\"12\"$n_month[12]>" . lang("December") . "</option>\n"
               . "</select>";
  $date_formatorder  .= "<input maxlength=2 name=day value=\"$n_day\" size=2>\n";
  $date_formatorder .= "<input maxlength=4 name=year value=\"$n_year\" size=4>";
  $t->set_var("date_formatorder",$date_formatorder);
  $t->set_var("lang_invoice_date",lang("invoice date"));

  $summe=0;
  $sumaes=0;
  while ($phpgw->db->next_record()) {
    $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
    $select = "<input type=\"checkbox\" name=\"select[".$phpgw->db->f("id")."]\" value=\"True\" checked>";

    $activity = $phpgw->db->f("descr");
    if ($activity != "")
       $activity = htmlentities(stripslashes($activity));
    else
       $activity = "&nbsp;";
    
   $remark = $phpgw->db->f("remark");
    if ($remark != "")
       $remark = htmlentities(stripslashes($remark));
    else
       $remark = "&nbsp;";

    $status = lang($phpgw->db->f("status"));
    $t->set_var(tr_color,$tr_color);

    if ($phpgw->db->f("date") == 0)
             $end_dateout = "&nbsp;";
    else {
      $month = $phpgw->common->show_date(time(),"n");
      $day   = $phpgw->common->show_date(time(),"d");
      $year  = $phpgw->common->show_date(time(),"Y");

      $date = (60*60) * $phpgw_info["user"]["preferences"]["common"]["tz_offset"];
      $dateout =  $phpgw->common->show_date($phpgw->db->f("date"),$phpgw_info["user"]["preferences"]["common"]["dateformat"]);
    }

/*    if ($phpgw->db->f("end_date") == 0)
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
*/    
    
    $aes = ceil($phpgw->db->f("minutes")/$phpgw->db->f("minperae"));
    $sumaes += $aes;
    $summe += (float)($phpgw->db->f("billperae")*$aes);


// -------------------- declaration for list records ---------------------------

    $t->set_var(array("select" => $select,
		      "activity" => $activity,
                      "remark" => $remark,
                      "status" => $status,
    		      "date" => $dateout,
      		      "aes" => $aes,
      		      "billperae" => $phpgw->db->f("billperae"),
      		      "sum" => sprintf ("%01.2f", (float)$phpgw->db->f("billperae")*$aes),
      		      "edithour" => "<a href=\"". $phpgw->link("bill_edithour.php","id=" . $phpgw->db->f("id") 
                                         . "&sort=$sort&order=$order&"
                                         . "query=$query&start=$start&filter="
                                         . "$filter&status=$status") 
					 . "\">" . lang("Edit hours") . "</a>"));
    $t->parse("list", "projecthours_list", true);

// ------------------------ end record declaration ------------------------
  }
    $t->set_var(sum_sum,sprintf("%01.2f",$summe));
    $t->set_var(sum_aes,$sumaes);
    $t->set_var(title_netto,lang("net"));

// na_list
  if($invoice_id) {
    $phpgw->db->query("SELECT p_hours.id as id,p_hours.remark,p_activities.descr,status,date,"
                  . "end_date,minutes,p_hours.minperae,p_hours.billperae FROM "
                  . "p_activities,p_hours,p_projectactivities WHERE status='done' AND "
                  . "p_hours.activity_id=p_activities.id AND p_projectactivities.project_id='$project_id' "
                  . "AND p_projectactivities.billable='Y' AND "
                  . "p_projectactivities.activity_id=p_hours.activity_id $ordermethod");

    while ($phpgw->db->next_record()) {
      $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
      $select = "<input type=\"checkbox\" name=\"select[".$phpgw->db->f("id")."]\" value=\"True\">";

      $activity = $phpgw->db->f("descr");
      if ($activity != "")
         $activity = htmlentities(stripslashes($activity));
      else
         $activity = "&nbsp;";
      
      $remark = $phpgw->db->f("remark");
      if ($remark != "")
         $remark = htmlentities(stripslashes($remark));
      else
         $remark = "&nbsp;";
  
      $status = lang($phpgw->db->f("status"));
      $t->set_var(tr_color,$tr_color);

      if ($phpgw->db->f("date") == 0)
               $end_dateout = "&nbsp;";
      else {
        $month = $phpgw->common->show_date(time(),"n");
        $day   = $phpgw->common->show_date(time(),"d");
        $year  = $phpgw->common->show_date(time(),"Y");

        $date = (60*60) * $phpgw_info["user"]["preferences"]["common"]["tz_offset"];
        $dateout =  $phpgw->common->show_date($phpgw->db->f("date"),$phpgw_info["user"]["preferences"]["common"]["dateformat"]);
      }
  
/*      if ($phpgw->db->f("end_date") == 0)
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
*/      

      $aes = ceil($phpgw->db->f("minutes")/$phpgw->db->f("minperae"));
      $sumaes += $aes;
      $summe += (float)($phpgw->db->f("billperae")*$aes);


// ------------------------- template declaration for list records ----------------------------
  
      $t->set_var(array("select" => $select,
  		        "activity" => $activity,
                        "remark" => $remark,
                        "status" => $status,
      		        "date" => $dateout,
        		"aes" => $aes,
        		"billperae" => $phpgw->db->f("billperae"),
        		"sum" => sprintf ("%01.2f", (float)$phpgw->db->f("billperae")*$aes),
        		"edithour" => "<a href=\"". $phpgw->link("bill_edithour.php","id=" . $phpgw->db->f("id") 
                                       . "&sort=$sort&order=$order&"
                                       . "query=$query&start=$start&filter="
                                       . "$filter&status=$status")
                                       . "\">". lang("Edit hours") . "</a>"));
      $t->parse("list", "projecthours_list", true);
  
// ---------------------------------- end record declaration -------------------------------------
    }
  }
// na_list_end



    $t->parse("out", "projecthours_list_t", true);
    $t->p("out");
    // -------------- end Add form declaration ------------------------

  include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");
?>
