<?php
  /**************************************************************************\
  * phpGroupWare - projects/addressbook                                      *
  * http://www.phpgroupware.org                                              *
  * Written by Bettina Gille [aeb@hansenet.de]                               *
  * ------------------------------------------------------                   *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
/* $Id$ */
  
  $phpgw_info["flags"] = array("noheader" => True, 
                               "nonavbar" => True, 
                               "currentapp" => "projects", 
                               "enable_nextmatchs_class" => True,
                               "enable_addressbook_class" => True);
  include("../header.inc.php");

  $t = new Template($phpgw_info["server"]["app_tpl"]);
  $t->set_file(array("addressbook_list_t" => "addressbook.tpl",
                     "addressbook_list"   => "addressbook.tpl"));
  $t->set_block("addressbook_list_t","addressbook_list","list");
  
  $t->set_var(title,$phpgw_info["site_title"]);
  $t->set_var(bg_color,$phpgw_info["theme"]["bg_color"]);
  $t->set_var(lang_addressbook_action,lang("Address book"));
  $charset = $phpgw->translation->translate("charset");                                                                                                                           
  $t->set_var("charset",$charset);  
   
  if (! $start) {
     $start = 0;
//     $query = "";
    }
  
  if ($order)
     $ordermethod = "order by $order $sort";
  else
    if ($phpgw_info["apps"]["timetrack"]["enabled"]){
     $ordermethod = "order by company_name,ab_lastname,ab_firstname asc";
     }
     else {
   $ordermethod = "order by ab_company,ab_lastname,ab_firstname asc";
       }
   if (! $filter) {
     $filter = "none";
  }

  if ($filter != "private") {
     if ($filter != "none") {
        $filtermethod = " ab_access like '%,$filter,%' ";
     } else {
        $filtermethod = " (ab_owner='" . $phpgw_info["user"]["account_id"] ."' OR ab_access='public' "
		            . $phpgw->accounts->sql_search("ab_access") . " ) ";
     }
  } else {
     $filtermethod = " ab_owner='" . $phpgw_info["user"]["account_id"] . "' ";
  }

  if ($query) {
    if ($phpgw_info["apps"]["timetrack"]["enabled"]){
     $phpgw->db->query("SELECT count(*) "
       . "from addressbook as a, customers as c where a.ab_company_id = c.company_id "
       . "AND $filtermethod AND (a.ab_lastname like '"
       . "%$query%' OR a.ab_firstname like '%$query%' OR c.company_name like '%$query%')");
     } else {
     $phpgw->db->query("SELECT count(*) "
       . "from addressbook "
       . "WHERE $filtermethod AND (ab_lastname like '"
       . "%$query%' OR ab_firstname like '%$query%' OR ab_company like '%$query%')");
      }

    $phpgw->db->next_record();
     
     if ($phpgw->db->f(0) == 1)
       $t->set_var(total_matchs,lang("your search returned 1 match"));
     else
       $t->set_var(total_matchs,lang("your search returned x matchs",$phpgw->db->f(0)));
  } else {
     $phpgw->db->query("select count(*) from addressbook where $filtermethod");
     $phpgw->db->next_record();
  }
  if ($phpgw_info["apps"]["timetrack"]["enabled"]) {
     $company_sortorder = "c.company_name";
  } else {
     $company_sortorder = "ab_company";
  }

  //$phpgw->db->next_record();

  if ($phpgw->db->f(0) > $phpgw_info["user"]["preferences"]["common"]["maxmatchs"])
     $total_matchs = "<br>" . lang("showing x - x of x",($start + 1),
			   ($start + $phpgw_info["user"]["preferences"]["common"]["maxmatchs"]),
			   $phpgw->db->f(0));
  else
     $total_matchs = "<br>" . lang("showing x",$phpgw->db->f(0)); 
?>

<?php
 
// =================================================
// nextmatch variable template-declaration
// =================================================

  $next_matchs = $phpgw->nextmatchs->show_tpl("addressbook.php",$start,$phpgw->db->f(0),
                "&order=$order&filter=$filter&sort="
	      . "$sort&query=$query", "85%", $phpgw_info["theme"]["th_bg"]);
  $t->set_var(next_matchs,$next_matchs);
  $t->set_var(total_matchs,$total_matchs);

  
// ----------- end nextmatch template --------------
  
// =================================================
// list header variable template-declaration
// =================================================    


   $t->set_var(th_bg,$phpgw_info["theme"]["th_bg"]);
   $t->set_var(sort_company,$phpgw->nextmatchs->show_sort_order($sort,"ab_company",$order,"addressbook.php",lang("Company")));
   $t->set_var(sort_firstname,$phpgw->nextmatchs->show_sort_order($sort,"ab_firstname",$order,"addressbook.php",lang("Firstname")));
   $t->set_var(sort_lastname,$phpgw->nextmatchs->show_sort_order($sort,"ab_lastname",$order,"addressbook.php",lang("Lastname")));
   $t->set_var(lang_customer,lang("Select customer"));
   
// ---------------- end header declaration ---------

 
  $limit = $phpgw->nextmatchs->sql_limit($start);  
  

if ($query) {
   if($phpgw_info["apps"]["timetrack"]["enabled"]){
     $phpgw->db->query("SELECT a.ab_id,a.ab_owner,a.ab_firstname,a.ab_lastname,a.ab_company_id,"
       . "c.company_name "
       . "from addressbook as a, customers as c where a.ab_company_id = c.company_id "
       . "AND $filtermethod AND (a.ab_lastname like '"
      . "%$query%' OR a.ab_firstname like '%$query%' OR c.company_name like '%$query%') "
       . "$ordermethod limit $limit");
   } else {
     $phpgw->db->query("SELECT ab_id,ab_owner,ab_firstname,ab_lastname,ab_company "
       . "from addressbook "
       . "WHERE $filtermethod AND (ab_lastname like '"
       . "%$query%' OR ab_firstname like '%$query%' OR ab_company like '%$query%') "
       . "$ordermethod limit $limit");
   }
  } else { 
   if($phpgw_info["apps"]["timetrack"]["enabled"]){
     $phpgw->db->query("SELECT a.ab_id,a.ab_owner,a.ab_firstname,a.ab_lastname,a.ab_company_id,"
       . "c.company_name "
       . "from addressbook as a, customers as c where a.ab_company_id = c.company_id "
       . "AND $filtermethod $ordermethod limit $limit");
   } else {
     $phpgw->db->query("SELECT ab_id,ab_owner,ab_firstname,ab_lastname,ab_company "
       . "from addressbook "
       . "WHERE $filtermethod $ordermethod limit $limit");
   }
  }

  while ($phpgw->db->next_record()) {
    $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
    $t->set_var(tr_color,$tr_color);
    
    $firstname	= $phpgw->db->f("ab_firstname");
    $lastname 	= $phpgw->db->f("ab_lastname");
    if ($phpgw_info["apps"]["timetrack"]["enabled"]) {
      $company   = $phpgw->db->f("company_name");
      $id        = $phpgw->db->f("ab_company_id");
    } else {
      $company   = $phpgw->db->f("ab_company");
      $id        = $phpgw->db->f("ab_id");    
   }

    if ($firstname == "") $firstname = "&nbsp;";
    if ($lastname  == "") $lastname  = "&nbsp;";
    if ($company   == "") $company   = "&nbsp;";


// ==================================================
// template declaration for list records
// ==================================================


  $t->set_var(array("company" => $company,
                    "firstname" => $firstname,
		    "lastname" => $lastname));
 
  $t->set_var(lang_select_customer,lang("Select customer"));
  $t->set_var("id",$id);
  $t->set_var("company",$company);
  $t->set_var("firstname",$firstname);
  $t->set_var("lastname",$lastname);
 
  $t->parse("list","addressbook_list", true);

}

// --------- end record declaration ----------------

// ==================================================
// template declaration for Done Form
// ==================================================

 $t->set_var(lang_done,lang("done"));
 $t->parse("out","addressbook_list_t", true);
 $t->p("out");

// ----------- end Done form declaration ------------
  include($phpgw_info["server"]["api_inc"] . "/footer.inc.php");
?>