<?php
  /**************************************************************************\
  * phpGroupWare - administration                                            *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  $phpgw_info["flags"]["currentapp"] = "admin";
  $phpgw_info["flags"]["disable_message_class"] = True;
  $phpgw_info["flags"]["disable_send_class"] = True;
  include("../header.inc.php");
  include($phpgw_info["server"]["server_root"] . "/admin/inc/accounts_"
        . $phpgw_info["server"]["auth_type"] . ".inc.php");

  $t = new Template($phpgw_info["server"]["template_dir"]);
  $t->set_file(array( "header"	=> "accounts.tpl",
			       "row"		=> "accounts.tpl",
			       "footer"	=> "accounts.tpl" ));

  $t->set_block("header","row","footer");

  $total = account_total();

  $t->set_var("bg_color",$phpgw_info["theme"]["bg_color"]);
  $t->set_var("th_bg",$phpgw_info["theme"]["th_bg"]);

  $t->set_var("left_next_matchs",$phpgw->nextmatchs->left("accounts.php",$start,$total));
  $t->set_var("lang_user_accounts",lang("user accounts"));
  $t->set_var("right_next_matchs",$phpgw->nextmatchs->right("accounts.php",$start,$total));

  $t->set_var("lang_lastname",$phpgw->nextmatchs->show_sort_order($sort,"account_lastname",$order,"accounts.php",lang("last name")));
  $t->set_var("lang_firstname",$phpgw->nextmatchs->show_sort_order($sort,"account_firstname",$order,"accounts.php",lang("first name")));

  $t->set_var("lang_edit",lang("Edit"));
  $t->set_var("lang_delete",lang("Delete"));
  $t->set_var("lang_view",lang("View"));

  $t->parse("out","header");

  $account_info = account_read($method,$start,$sort,$order);

  while (list($null,$account) = each($account_info)) {
    echo "<br>" . $account[1];
    $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
    $t->set_var("tr_color",$tr_color);

    $lastname  = $account["account_lastname"];
    $firstname = $account["account_firstname"];

    if (! $lastname)  $lastname  = '&nbsp;';
    if (! $firstname) $firstname = '&nbsp;';

    $t->set_var("row_firstname",$firstname);
    $t->set_var("row_lastname",$lastname);
    $t->set_var("row_edit",'<a href="'.$phpgw->link("editaccount.php","account_id="
				     . $account["account_id"]) . '"> ' . lang("Edit") . ' </a>');

    if ($phpgw_info["user"]["userid"] != $account["account_lid"]) {
       $t->set_var("row_delete",'<a href="' . $phpgw->link("deleteaccount.php",'account_id='
						. $account["account_id"]) . '"> '.lang("Delete").' </a>');
    } else {
       $t->set_var("row_delete","&nbsp;");
    }

    $t->set_var("row_view",'<a href="' . $phpgw->link("viewaccount.php", "account_id="
				     . $account["account_id"]) . '"> ' . lang("View") . ' </a>');

    if ($phpgw->db->num_rows() == 1) {
       $t->set_var("output","");
    }
    if ($phpgw->db->num_rows() != ++$i) {
       $t->parse("output","row",True);
    }
  }

  $t->set_var("actionurl",$phpgw->link("newaccount.php"));
  $t->set_var("lang_add",lang("add"));
  $t->set_var("lang_search",lang("search"));

  $t->pparse("out","footer");

  $phpgw->common->phpgw_footer();
  
  account_close();
?>
