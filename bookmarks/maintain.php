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
  
//  $phpgw_info["flags"] = array("noheader" => True, "nonavbar" => True);
  $phpgw_info["flags"]["currentapp"] = "bookmarks";
  $phpgw_info["flags"]["enable_nextmatchs_class"] = True;
  $phpgw_info["flags"]["enable_categories_class"] = True;
  include("../header.inc.php");

  $phpgw->sbox = createobject("phpgwapi.sbox");

  function return_to()
  {
     global $returnto, $msg, $error_msg, $sess_msg, $sess_error_msg, $phpgw;

     if (!empty($returnto)) {
        list($file,$vars) = explode("----",urldecode($returnto));
        $sess_msg       = $msg;
        $sess_error_msg = $error_msg;
        header("Location: " . $phpgw->link($file,$vars));
        page_close();
        $phpgw->common->phpgw_exit();
     }
  }

  $phpgw->template->set_file(array("common" => "common.tpl",
                                   "body"   => "form.tpl",
                                   "info"   => "form_info.tpl"
                            ));

  app_header(&$phpgw->template);

  $bmark = new bmark;

  ## Check if there was a submission
  while (is_array($HTTP_POST_VARS) && list($key, $val) = each($HTTP_POST_VARS)) {
     switch ($key) {

     ## Change bookmark
//     case "bk_edit":
     case "bk_edit_x":
     if (!$bmark->update($bm_id, $url, $name, $desc, $keyw, $bookmarks_category, $bookmarks_subcategory, $bookmarks_rating, $access)) break;

     return_to();
     break;

     ## Delete the bookmark
     case "bk_delete":
     case "bk_delete_x":
     if (!$bmark->delete($id))
        break;
     return_to();
     break;
  
     ## Cancel the changes, send user back to referring page.
     case "bk_cancel":
     case "bk_cancel_x":
      $msg .= "Bookmark maintain cancelled.";
      return_to();
      break;

     default: break;
 }
}

  if (empty($error_msg)) {
     // Add ACL checks to see if they have rights to update it
     $phpgw->db->query("select * from phpgw_bookmarks where bm_id='$bm_id' and bm_owner='"
                     . $phpgw_info["user"]["account_id"] . "'",__LINE__,__FILE__);
  
     $phpgw->db->next_record();
  
     date_information(&$phpgw->template,$phpgw->db->f("bm_info"));
  
     $rs[$phpgw->db->f("bm_rating")] = " selected";
     $rating_select = '<select name="bookmarks_rating">'
                    . ' <option value="0">--</option>'
                    . ' <option value="1"' . $rs[1] . '>1 - ' . lang("Lowest") . '</option>'
                    . ' <option value="2"' . $rs[2] . '>2</option>'
                    . ' <option value="3"' . $rs[3] . '>3</option>'
                    . ' <option value="4"' . $rs[4] . '>4</option>'
                    . ' <option value="5"' . $rs[5] . '>5</option>'
                    . ' <option value="6"' . $rs[6] . '>6</option>'
                    . ' <option value="7"' . $rs[7] . '>7</option>'
                    . ' <option value="8"' . $rs[8] . '>8</option>'
                    . ' <option value="9"' . $rs[9] . '>9</option>'
                    . ' <option value="10"' . $rs[10] . '>10 - ' . lang("Highest") . '</option>'
                    . '</select>';
  
     if (!empty($returnto)) {
        $cancel_button = sprintf("<input type=\"image\" name=\"bk_cancel\" title=\"Cancel Maintain\" src=\"%scancel.%s\" border=0 width=24 height=24>", $bookmarker->image_url_prefix, $bookmarker->image_ext);
     }
  
     $phpgw->template->set_var("lang_header",lang("Edit bookmark"));
     $phpgw->template->set_var("th_bg",$phpgw_info["theme"]["th_bg"]);
     $phpgw->template->set_var("updated",$f_ts[2]);
     $phpgw->template->set_var("total_visits",$phpgw->db->f("bm_visits"));
        
     $phpgw->template->set_var("lang_added",lang("Date added"));
     $phpgw->template->set_var("lang_updated",lang("Date last updated"));
     $phpgw->template->set_var("lang_visited",lang("Date last visited"));
     $phpgw->template->set_var("lang_visits",lang("Total visits"));
        
     $phpgw->template->parse("info","info");
      
     $phpgw->template->set_var("form_action",$phpgw->link('/bookmarks/maintain.php','bm_id=' . $bm_id));
     $phpgw->template->set_var("lang_url",lang("URL"));
     $phpgw->template->set_var("lang_name",lang("Name"));
     $phpgw->template->set_var("lang_desc",lang("Description"));
     $phpgw->template->set_var("lang_keywords",lang("Keywords"));
      
     $phpgw->template->set_var("lang_category",lang("Category"));
     $phpgw->template->set_var("lang_subcategory",lang("Sub Category"));
     $phpgw->template->set_var("lang_rating",lang("Rating"));
        
		$phpgw->template->set_var('lang_access',lang('Private'));
		$phpgw->template->set_var('input_access','<input type="checkbox" name="access" value="private"' . ($bm_access=='private'?' checked':'') . '>');

     $phpgw->template->set_var("input_rating",$rating_select);
  
     $phpgw->template->set_var("input_category",'<select name="bookmarks_category">'
                                              . '<option value="0">--</option>'
                                              . $phpgw->categories->formated_list("select","mains",$phpgw->db->f("bm_category"))
                                              . '</select>');
  
     $phpgw->template->set_var("input_subcategory",'<select name="bookmarks_subcategory">'
                                                 . '<option value="0">--</option>'
                                                 . $phpgw->categories->formated_list("select","subs",$phpgw->db->f("bm_subcategory"))
                                                 . '</select>');
  
  
     $phpgw->template->set_var("input_url",'<input name="url" size="60" maxlength="255" value="' . $phpgw->db->f("bm_url") . '">');
     $phpgw->template->set_var("input_name",'<input name="name" size="60" maxlength="255" value="' . $phpgw->db->f("bm_name") . '">');
     $phpgw->template->set_var("input_desc",'<textarea name="desc" rows="3" cols="60" wrap="virtual">' . $phpgw->db->f("bm_desc") . '</textarea>');
     $phpgw->template->set_var("input_keywords",'<input type="text" name="keyw" size="60" maxlength="255" value="' . $phpgw->db->f("bm_keywords") . '">');
  
     $phpgw->template->parse(BODY, "body");

     $phpgw->template->set_var("delete_link","");
     $phpgw->template->set_var("cancel_link","");
     $phpgw->template->set_var("form_link",'<input type="image" name="bk_edit" title="'
                                         . LANG("Change Bookmark") . '" src="'
                                         . $phpgw_info["server"]["app_images"] . '/save.gif" border="0">');
  }
  $phpgw->common->phpgw_footer();
?>