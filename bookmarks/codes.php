<?php 
  /**************************************************************************\
  * phpGroupWare - Bookmarks                                                 *
  * http://www.phpgroupware.org                                              *
  * Based on Bookmarker Copyright (C) 1998  Padraic Renaghan                 *
  *                     http://www.renaghan.com/bookmarker                   *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  $phpgw_info["flags"]["currentapp"] = "bookmarks";
  $phpgw_info["flags"]["enable_nextmatchs_class"] = True;
  include("../header.inc.php");

  $phpgw->template->set_file(array(standard    => "common.standard.tpl",
                                   code_list   => "codes.codelist.tpl",
                                   select_form  => "codes.select.tpl",
                                   update_form  => "codes.update.tpl",
                                   create_form  => "codes.create.tpl",
                                   delete_form  => "codes.delete.tpl"
                            ));

  set_standard("code tables", &$phpgw->template);

  $username = $phpgw_info["user"]["account_id"];

  // Check if there was a submission
  while (is_array($HTTP_POST_VARS) && list($key, $val) = each($HTTP_POST_VARS)) {
  switch ($key) {

  // update canceled
  case "bk_cancel_update":
    $mode = "S";
  break;

  // create canceled
  case "bk_cancel_create":
    $mode = "S";
  break;

  // delete canceled
  case "bk_cancel_delete":
    $mode = "S";
  break;

  // maintain code table
  case "bk_code_update":
/*    ## Do we have permission to do so?
    if (!$perm->have_perm("editor")) {
      $error_msg .= "<br>You do not have permission to update code tables.";
      break;
    } */

    // Trim space from begining and end of fields
    $name = trim($name);

    // Do we have all necessary data?
    if (empty($name)) {
      $error_msg = "<br>" . lang("You need to enter a name.");
      break;
    }
    
    // Update information
    $query = sprintf("update %s set name='%s' where id=%s and username='%s'", $codetable, addslashes($name), $id, $username);
  
    $phpgw->db->query($query,__LINE__,__FILE__);
    if ($phpgw->db->Errno != 0) break;

    $mode = "S";
    $msg .= sprintf("<br>%s %s changed.", lang(ereg_replace("bookmarks_","",$codetable)), htmlspecialchars(stripslashes($name)));
  break;

  // Delete the codes
  case "bk_code_delete":
    // Do we have permission to do so?
    if (!$perm->have_perm("editor")) {
      $error_msg .= "<br>You do not have permission to delete codes.";
      break;
    }
    
    // when deleting a category or subcategory, we need to
    // update related tables to maintain data integrity.
    if ($codetable == "category" || $codetable == "subcategory") {
       $query = sprintf("update bookmarks set %s_id=0 where %s_id=%s and username='%s'",$codetable,
                        $codetable,$id,$username);
      $phpgw->db->query($query,__LINE__,__FILE__);
      if ($phpgw->db->Errno != 0) break;
      $msg .= "<br>bookmarks with $codetable $id changed to $codetable 0.";
    }

    // Delete that code
    $query = sprintf("delete from %s where id=%s and username='%s'", $codetable, $id, $username);
    $phpgw->db->query($query,__LINE__,__FILE__);
    if ($phpgw->db->Errno != 0) break;

    $mode = "S";
    $msg .= "<br>" . lang("$codetable x deleted.",$name);
  break;

  // Create a code
  case "bk_code_create":

    // Do we have permission to do so?
/*    if (!$perm->have_perm("editor")) {
      $error_msg .= "<br>You do not have permission to create codes.";
      break;
    } */

    // Trim space from begining and end of fields
    $name = trim($name);
    
    // Do we have all necessary data?
    if (empty($name)) {
      $error_msg .= "<br>You need to enter a name";
      break;
    }
        
    // Does the code already exist?
    $phpgw->db->query("select name from $codename where name='" . addslashes($name)
                    . "' and username='" . $phpgw_info["user"]["account_id"] . "'",__LINE__,__FILE__);

    if ($phpgw->db->nf() > 0) {
       $error_msg .= "<br>$name already exists.";
       break;
    }

    // Insert the code
    $phpgw->db->query("insert into $codetable (name, username) values ('" . addslashes($name)
                    . "','" . $phpgw_info["user"]["account_id"] . "')",__LINE__,__FILE__);

    $mode = "S";
    $msg .= sprintf("<br>%s %s created.", ereg_replace("bookmarks_","",$codetable), htmlspecialchars(stripslashes($name)));
  break;
  
  default:
  break;
 } /* end switch */
} /* end while */

$phpgw->template->set_var(CODETABLE, $codetable);
$phpgw->template->set_var(th_bg, $phpgw_info["theme"]["th_bg"]);
$phpgw->template->set_var(sort_name,$phpgw->nextmatchs->show_sort_order($sort,"name",$order,"codes.php",lang("Name")));
$phpgw->template->set_var(lang_edit,lang("Edit"));
$phpgw->template->set_var(lang_delete,lang("Delete"));
$phpgw->template->set_var(header_message,lang("Bookmark " . ereg_replace("bookmarks_","",$codetable) . "s"));

$phpgw->template->set_var(FORM_ACTION, $phpgw->link("codes.php","mode=$mode&codetable=$codetable&id=$id"));

// if no mode specified, or mode is S (Select)
// then print html to allow user to select from
// the possible options and data on this page.

if (!isset($mode) || $mode=="S") {
  $body_tpl_name = "select_form";

  // get records to update
  $phpgw->db->query("select id, name from $codetable where username='$username' order by name",__LINE__,__FILE__);
  while ($phpgw->db->next_record()) {
     if ($phpgw->db->f("name") != "--") {
        $id = $phpgw->db->f("id");
        $url = $phpgw->link("codes.php","codetable=$codetable&mode=U&id=$id");
   
        $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
        $phpgw->template->set_var(TR_COLOR, $tr_color);
   
        $phpgw->template->set_var(EDIT, '<a href="' . $phpgw->link("codes.php","codetable=$codetable&mode=U&id=$id") . '"> Edit </a>');
        $phpgw->template->set_var(DELETE, '<a href="' . $phpgw->link("codes.php","codetable=$codetable&mode=D&id=$id") . '"> Delete </a>');
 
        $phpgw->template->set_var(URL, $url);
        $phpgw->template->set_var(NAME, htmlspecialchars(stripslashes($phpgw->db->f("name"))));
        $phpgw->template->set_var(ID, $id);
        $phpgw->template->parse(CODE_LIST, "code_list", TRUE);
   
     }
     $phpgw->template->set_var(CREATE_LINK, '<a href="' . $phpgw->link("codes.php","mode=C&codetable=$codetable") . '">' . lang("Create new " . ereg_replace("bookmarks_","",$codetable)) . '</a>');
     $phpgw->template->parse(BODY, "select_form");
  }

// if mode is U, present the update form
} elseif ($mode=="U") {
  $body_tpl_name = "update_form";

  // get record to update
  $phpgw->db->query("select * from $codetable where id='$id' and username='"
                  . $phpgw_info["user"]["account_id"] . "'",__LINE__,__FILE__);
  $phpgw->db->next_record();
  $phpgw->template->set_var(array(ID   => $phpgw->db->f("id"),
                                  NAME => htmlspecialchars(stripslashes($phpgw->db->f("name")))
                                 ));

  $phpgw->template->set_var(table_header_message, lang("Update " . ereg_replace("bookmarks_","",$codetable)));
  $phpgw->template->set_var(lang_cancel, lang("Cancel"));
  $phpgw->template->set_var(lang_update, lang("Update"));
  $phpgw->template->parse(BODY, "update_form");
 
// if mode is C, present the create form
} elseif ($mode=="C") {
  $body_tpl_name = "create_form";

  // get the max used ID so that we can default for the new row
  $phpgw->db->query("select max(id) as max_id from $codetable where username='"
                  . $phpgw_info["user"]["account_id"] . "'",__LINE__,__FILE__);

  $phpgw->db->next_record();
  $default_id = $phpgw->db->f("max_id") + 1;
  if (! $default_id) {
     $default_id = 0;
  }

  $phpgw->template->set_var(DEFAULT_ID, $default_id);
  $phpgw->template->set_var(th_bg, $phpgw_info["theme"]["th_bg"]);
  $phpgw->template->set_var(lang_cancel, lang("Cancel"));
  $phpgw->template->set_var(lang_create, lang("Create"));
  $phpgw->template->set_var(table_header_message, lang("create new " . ereg_replace("bookmarks_","",$codetable)));

// if mode is D, present the are you sure delete form
} elseif ($mode=="D") {
  $body_tpl_name = "delete_form";
  $phpgw->template->set_var(ID, $id);
}

  // NOTE: we can't use messages.inc.php here since we don't have a static name for the body template.
  // standard error message, and message handler.
  include($phpgw_info["server"]["server_root"] . "/bookmarks/inc/messages.inc.php");
  if (isset ($bk_output_html)) {
     $phpgw->template->set_var(MESSAGES, $bk_output_html);
  }

  $phpgw->template->parse("BODY", array($body_tpl_name, "standard"));
  $phpgw->template->p("BODY");
?>
