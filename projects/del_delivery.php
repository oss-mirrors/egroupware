<?php
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
  $t->set_file(array("projecthours_list_t" => "del_listhours.tpl"));
  $t->set_block("projecthours_list_t", "projecthours_list", "list");

  $t->set_var(date_hint,"");
  $t->set_var("lang_action",lang("Delivery"));

  if(($Update) or ($Delivery)) {
  if (checkdate($month,$day,$year)) {
       $date = mktime(2,0,0,$month,$day,$year);
   } else {
       if ($month && $day && $year) {
          $t->set_var(date_hint,lang("You have entered an invailed date"));
          $date=0;
          unset($Delivery);
       }
   }
   if(!$delivery_id) {
       $phpgw->db->query("SELECT customer FROM p_projects WHERE id='$project_id'");
       $phpgw->db->next_record();
       $customer = $phpgw->db->f("customer");
       $phpgw->db->query("INSERT INTO p_delivery (project_id,date,customer) VALUES ($project_id,'$date',$customer)");
       $phpgw->db->query("SELECT LAST_INSERT_ID() AS id");
       $phpgw->db->next_record();
       $delivery_id = $phpgw->db->f("id");
   } else {
       $phpgw->db->query("UPDATE p_delivery set date='$date' WHERE id=$delivery_id");
   }
     $phpgw->db->query("DELETE FROM p_deliverypos WHERE delivery_id=$delivery_id");
      while($select && $entry=each($select)) {
        $phpgw->db->query("INSERT INTO p_deliverypos (delivery_id,hours_id) VALUES ($delivery_id,$entry[0])");
    }
   }

  if($Delivery) {
  $phpgw->db->query("SELECT num FROM p_delivery WHERE num='$delivery_num' AND id!=$delivery_id");
  if($phpgw->db->next_record()) {
    $t->set_var(delivery_hint,lang("duplicate deliverynum"));
  } else {
    $phpgw->db->query("SELECT customer FROM p_projects WHERE id=$project_id");
    $phpgw->db->next_record();
    $customer=$phpgw->db->f("customer");
    $phpgw->db->query("UPDATE p_delivery set customer='$customer' WHERE id=$delivery_id");
    $t->set_var(delivery_hint,"");
    $phpgw->db->query("UPDATE p_delivery SET date='".time()."',num='$delivery_num' WHERE id=$delivery_id");
     }
    } else {
    $t->set_var(delivery_hint,"");
   }


  $common_hidden_vars =
   "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
 . "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
 . "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
 . "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n"
 . "<input type=\"hidden\" name=\"delivery_id\" value=\"$delivery_id\">\n"
 . "<input type=\"hidden\" name=\"project_id\" value=\"$project_id\">\n";

  $t->set_var(common_hidden_vars,$common_hidden_vars);   

  if (! $start)
     $start = 0;
  $ordermethod = "order by date asc";

    // ===========================================
    // nextmatch variable template-declarations
    // ===========================================

     $t->set_var(total_matchs,$total_matchs);

  // ---------- end nextmatch template --------------------

  // ===========================================
  // list header variable template-declarations
  // ===========================================
  $t->set_var(th_bg,$phpgw_info["theme"][th_bg]);

  $t->set_var(sort_activity,lang("Activity"));
  $t->set_var(sort_remark,lang("Remark"));
  $t->set_var(sort_status,lang("Status"));
  $t->set_var(sort_date,lang("Date"));
  $t->set_var(sort_aes,lang("Workunits"));
  $t->set_var(h_lang_select,lang("Select"));
  $t->set_var(h_lang_edithour,lang("Edit hours"));
  $t->set_var(lang_update,lang("Update"));
  $t->set_var(lang_createdelivery,lang("Create delivery"));
  $t->set_var(actionurl,$phpgw->link("del_delivery.php"));
  $t->set_var(lang_print_delivery,lang("Print delivery"));

  if (!$delivery_id) {                                                                                                                                                                     
  $t->set_var(print_delivery,$phpgw->link("fail.php"));                                                                                                                                    
   }                                                                                                                                                                                      
  else {
  $t->set_var(print_delivery,$phpgw->link("del_deliveryshow.php","delivery_id=$delivery_id"));
  }
  
  // -------------- end header declaration -----------------


  $limit = $phpgw->nextmatchs->sql_limit($start);

  if ($phpgw_info["apps"]["timetrack"]["enabled"]) {
  
   $phpgw->db->query("SELECT title,ab_company_id,company_name,ab_lastname,ab_firstname "
                  . "FROM p_projects,addressbook,customers "
                  . "WHERE id='$project_id' AND customers.company_id=addressbook.ab_company_id and "
                  . "p_projects.customer=ab_company_id");
    if ($phpgw->db->next_record()) {
    $t->set_var(project,$phpgw->db->f("title"));
    $t->set_var(customer,$phpgw->db->f("company_name")." [ ".
		$phpgw->db->f("ab_firstname")." ".$phpgw->db->f("ab_lastname")." ]");
    }  
    else {
    $t->set_var(project,lang("no customer selected"));
    $t->set_var(customer,lang("no customer selected"));
      }
     }
   else {
   $phpgw->db->query("SELECT title,ab_company,ab_lastname,ab_firstname "
                  . "FROM p_projects,addressbook "
                  . "WHERE id='$project_id' AND p_projects.customer=ab_id");
  if($phpgw->db->next_record()) {
    
    $title = $phpgw->strip_html($phpgw->db->f("title"));                                                                                                                               
    if (! $title)  $title  = "&nbsp;";

    $t->set_var("project",$title);
    if (!$phpgw->db->f("ab_company")) {
    $t->set_var(customer,$phpgw->db->f("ab_firstname")." ".$phpgw->db->f("ab_lastname"));
         }
    else {
    $t->set_var(customer,$phpgw->db->f("ab_company")." [ ".
		$phpgw->db->f("ab_firstname")." ".$phpgw->db->f("ab_lastname")." ]");
     }
    } 
    else {
    $t->set_var(project,lang("no customer selected"));
    $t->set_var(customer,lang("no customer selected"));
      }
     }
    $t->set_var(title_project,lang("Title"));                                                                                                                                                               
    $t->set_var(title_customer,lang("Customer"));  
    $t->set_var(title_delivery_num,lang("Delivery ID"));
  
  if(!$delivery_num) {
    $phpgw->db->query("SELECT max(num) AS max FROM p_delivery");
    if($phpgw->db->next_record()) {
      $t->set_var(delivery_num,(int)($phpgw->db->f("max"))+1);
    } else {
      $t->set_var(delivery_num,"1");
    }
  } else {
      $t->set_var(delivery_num,$phpgw->strip_html($delivery_num));
  }
  
  if(!$delivery_id) {
    $date=0;
    $phpgw->db->query("SELECT p_hours.id as id,p_hours.remark,p_activities.descr,status,date,"
                  . "end_date,minutes,p_hours.minperae,p_hours.billperae FROM "
                  . "p_hours LEFT JOIN p_activities ON p_hours.activity_id=p_activities.id "
		  . " LEFT JOIN p_deliverypos ON p_hours.id=p_deliverypos.hours_id "
		  . " WHERE p_hours.project_id='$project_id' and p_deliverypos.id IS NULL $ordermethod");
  } else {
    $phpgw->db->query("SELECT date FROM p_delivery WHERE id=$delivery_id");
    $phpgw->db->next_record();
    $date=$phpgw->db->f("date");    
    $phpgw->db->query("SELECT p_hours.id as id,p_hours.remark,p_activities.descr,status,date,"
                  . "end_date,minutes,p_hours.minperae,p_hours.billperae FROM "
                  . "p_hours LEFT JOIN p_activities ON p_hours.activity_id=p_activities.id "
		  . " LEFT JOIN p_deliverypos ON p_hours.id=p_deliverypos.hours_id "
		  . " WHERE p_hours.project_id='$project_id' and p_deliverypos.delivery_id='$delivery_id' $ordermethod");
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
  $t->set_var("lang_delivery_date",lang("Delivery date"));

  $summe=0;
  $sumaes=0;
  while ($phpgw->db->next_record()) {
    $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
    $select = "<input type=\"checkbox\" name=\"select[".$phpgw->db->f("id")."]\" value=\"True\" checked>";

    $activity = $phpgw->strip_html($phpgw->db->f("descr"));                                                                                                                               
    if (! $activity)  $activity  = "&nbsp;";    
    
    $remark = $phpgw->strip_html($phpgw->db->f("remark"));                                                                                                                               
    if (! $remark)  $remark  = "&nbsp;";

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
    
    $aes = ceil($phpgw->db->f("minutes")/$phpgw->db->f("minperae"));
    $sumaes += $aes;
    // ============================================
    // template declaration for list records
    // ============================================

    $t->set_var(array("select" => $select,
		      "activity" => $activity,
                      "remark" => $remark,
                      "status" => $status,
    		      "date" => $dateout,
      		      "aes" => $aes,
      		      "edithour" => "<a href=\"". $phpgw->link("del_edithour.php","id=" . $phpgw->db->f("id") 
                                         . "&sort=$sort&order=$order&"
                                         . "query=$query&start=$start&filter="
                                         . "$filter&status=$status")
                                 . "\">" . lang("Edit hours") . "</a>"));
    $t->parse("list", "projecthours_list", true);

    // -------------- end record declaration ------------------------
  }
    $t->set_var(sum_aes,$sumaes);

// na_list
  if($delivery_id) {
    $phpgw->db->query("SELECT p_hours.id as id,p_hours.remark,p_activities.descr,status,date,"
                  . "end_date,minutes,p_hours.minperae,p_hours.billperae FROM "
                  . "p_hours LEFT JOIN p_activities ON p_hours.activity_id=p_activities.id "
		  . " LEFT JOIN p_deliverypos ON p_hours.id=p_deliverypos.hours_id "
		  . " WHERE p_hours.project_id='$project_id' and p_deliverypos.id IS NULL $ordermethod");

    while ($phpgw->db->next_record()) {
      $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
      $select = "<input type=\"checkbox\" name=\"select[".$phpgw->db->f("id")."]\" value=\"True\">";

    $activity = $phpgw->strip_html($phpgw->db->f("descr"));                                                                                                                               
    if (! $activity)  $activity  = "&nbsp;";

    $remark = $phpgw->strip_html($phpgw->db->f("remark"));                                                                                                                               
    if (! $remark)  $remark  = "&nbsp;";
  
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
  
      $aes = ceil($phpgw->db->f("minutes")/$phpgw->db->f("minperae"));
      $sumaes += $aes;
      // ============================================
      // template declaration for list records
      // ============================================
  
      $t->set_var(array("select" => $select,
  		        "activity" => $activity,
                        "remark" => $remark,
                        "status" => $status,
      		        "date" => $dateout,
        		"aes" => $aes,
        		"edithour" => "<a href=\"". $phpgw->link("del_edithour.php","id=" . $phpgw->db->f("id") 
                                       . "&sort=$sort&order=$order&"
                                       . "query=$query&start=$start&filter="
                                       . "$filter&status=$status")
                                       . "\">". lang("Edit hours") . "</a>"));
      $t->parse("list", "projecthours_list", true);
  
      // -------------- end record declaration ------------------------
    }
  }
// na_list_end



    $t->parse("out", "projecthours_list_t", true);
    $t->p("out");
    // -------------- end Add form declaration ------------------------

  include($phpgw_info["server"]["api_inc"] . "/footer.inc.php");
?>