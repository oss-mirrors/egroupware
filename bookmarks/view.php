<?php 
  /**************************************************************************\
  * phpGroupWare - Bookmarks                                                 *
  * http://www.phpgroupware.org                                              *
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
  
  $phpgw->template->set_file(array("body"     => "maintain.body.tpl",
                                   "standard" => "common.standard.tpl"
                            ));
  set_standard("create", &$phpgw->template);
  
  $phpgw->db->query("select * from bookmarks where username='" . $phpgw_info["user"]["account_id"]
                  . "'",__LINE__,__FILE__);
  $phpgw->db->next_record();

  $ts = explode(",",$bm_timestamps_raw);
  $f_ts[0] = $phpgw->common->show_date($ts[0]);

  if ($ts[1]) {
     $f_ts[1] = $phpgw->common->show_date($ts[1]);
  } else {
     $f_ts[1] = lang("Never");
  }

  if ($ts[2]) {
     $f_ts[2] = $phpgw->common->show_date($ts[2]);
  } else {
     $f_ts[2] = lang("Never");
  }

  $phpgw->template->set_var(array(
    DEFAULT_URL            => $default_url,
    DEFAULT_NAME           => htmlspecialchars(stripslashes($default_name)),
    DEFAULT_DESC           => htmlspecialchars(stripslashes($default_desc)),
    DEFAULT_KEYW           => htmlspecialchars(stripslashes($default_keyw)),
    DEFAULT_CATEGORY       => $default_category,
    DEFAULT_SUBCATEGORY    => $default_subcategory,
    DEFAULT_RATING         => $default_rating,
    RATING_SELECT          => $rating_select,
    CATEGORY_SELECT        => $category_select,
    SUBCATEGORY_SELECT     => $subcategory_select,
    RATING_SELECT          => $rating_select,
    DEFAULT_PUBLIC         => $default_public
                                        ADDED              => $f_ts[0],
                                        VISTED             => $f_ts[1],
                                        UPDATED            => $f_ts[2],
                                        ADDED_VALUE        => $ts[0],
                                        VISTED_VALUE       => $ts[1],
                                        TOTAL_VISTS        => $total_vists,

  ));

  include($phpgw_info["server"]["server_root"] . "/bookmarks/inc/footer.inc.php");
?>