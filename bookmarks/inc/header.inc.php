<?php

  function app_header(&$tpl)
  {
     global $phpgw, $PHP_SELF;

     $tabs[1]["label"] = "Tree view";
     $tabs[1]["link"]  = $phpgw->link("tree.php");
     if (ereg("tree.php",$PHP_SELF)) {
        $selected = 1;
     }
   
     $tabs[2]["label"] = "List";
     $tabs[2]["link"]  = $phpgw->link("list.php");
     if (ereg("list.php",$PHP_SELF)) {
        $selected = 2;
     }

     if ($phpgw->acl->check("anonymous",1,"bookmarks")) {   
        $tabs[3]["label"] = "New";
        $tabs[3]["link"]  = $phpgw->link("create.php");
        if (ereg("create.php",$PHP_SELF)) {
           $selected = 3;
        }
     }

     $tabs[4]["label"] = "Search";
     $tabs[4]["link"]  = $phpgw->link("search.php");
     if (ereg("search.php",$PHP_SELF)) {
        $selected = 4;
     }
   
     $tpl->set_var("app_navbar",$phpgw->common->create_tabs($tabs,$selected));
  }
