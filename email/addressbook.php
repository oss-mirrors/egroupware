<?php
  /**************************************************************************\
  * phpGroupWare - email/addressbook                                         *
  * http://www.phpgroupware.org                                              *
  * Written by Bettina Gille [aeb@hansenet.de]                               *
  * ------------------------------------------------------                   *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  $phpgw_info["flags"] = array("noheader" => True, 
                               "nonavbar" => True, 
                               "currentapp" => "email", 
                               "enable_message_class" => True,
                               "enable_addressbook_class" => True,
                               "enable_nextmatchs_class" => True);
  include("../header.inc.php");

  $t = new Template($phpgw_info["server"]["app_tpl"]);
  $t->set_file(array("addressbook_list_t" => "addressbook.tpl",
                     "addressbook_list"   => "addressbook.tpl"));
  $t->set_block("addressbook_list_t","addressbook_list","list");
  
  $charset = $phpgw->translation->translate("charset");                                                                                                    
  $t->set_var("charset",$charset);
  $t->set_var(title,$phpgw_info["site_title"]);
  $t->set_var(bg_color,$phpgw_info["theme"]["bg_color"]);
  $t->set_var(lang_addressbook_action,lang("Address book"));
  $t->set_var("font",$phpgw_info["theme"]["font"]);  

  if (! $start) {
     $start = 0;
       }
  
  if ($order)
     $ordermethod = "order by $order $sort";
  else
     $ordermethod = "order by ab_email,ab_lastname,ab_firstname asc";
   
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
     $phpgw->db->query("SELECT count(*) "
       . "from addressbook "
       . "WHERE $filtermethod AND (ab_lastname like '"
       . "%$query%' OR ab_firstname like '%$query%' OR ab_email like '%$query%')");
    
    $phpgw->db->next_record();

     if ($phpgw->db->f(0) == 1)
       $t->set_var(total_matchs,lang("your search returned 1 match"));
     
      else
       $t->set_var(total_matchs,lang("your search returned x matchs",$phpgw->db->f(0)));
      }
     else {
     $phpgw->db->query("select count(*) from addressbook where $filtermethod");
     $phpgw->db->next_record();
     }

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
   $t->set_var(sort_firstname,$phpgw->nextmatchs->show_sort_order($sort,"ab_firstname",$order,"addressbook.php",lang("firstname")));
   $t->set_var(sort_lastname,$phpgw->nextmatchs->show_sort_order($sort,"ab_lastname",$order,"addressbook.php",lang("lastname")));
   $t->set_var(lang_email,lang("select email address"));
   
// ---------------- end header declaration ---------

?>

<?php
 
  $limit = $phpgw->nextmatchs->sql_limit($start);  


if ($query) {
     $phpgw->db->query("SELECT ab_id,ab_owner,ab_firstname,ab_lastname,ab_email "
       . "from addressbook "
       . "WHERE $filtermethod AND (ab_lastname like '"
       . "%$query%' OR ab_firstname like '%$query%' OR ab_email like '%$query%') "
       . "$ordermethod limit $limit");
  } else {
     $phpgw->db->query("SELECT ab_id,ab_owner,ab_firstname,ab_lastname,ab_email "
       . "from addressbook "
       . "WHERE $filtermethod $ordermethod limit $limit");
  }

  while ($phpgw->db->next_record()) {
    $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
    $t->set_var(tr_color,$tr_color);
    
    $firstname	= $phpgw->db->f("ab_firstname");
    $lastname 	= $phpgw->db->f("ab_lastname");
    $email      = $phpgw->db->f("ab_email");
    $con        = $phpgw->db->f("ab_id");    
  

    if ($firstname == "") $firstname = "&nbsp;";
    if ($lastname  == "") $lastname  = "&nbsp;";


// ==================================================
// template declaration for list records
// ==================================================


  $t->set_var(array("email" => $email,
                    "firstname" => $firstname,
		    "lastname" => $lastname));
  
  $t->set_var(lang_select_email,lang("select email address"));
  $t->set_var("con",$con);
  $t->set_var("email",$email);
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

?>