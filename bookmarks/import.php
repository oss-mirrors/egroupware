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
  $phpgw_info["flags"]["enabled_nextmatchs_class"] = True;
  include("../header.inc.php");
  $debug = False;

  #
  # possible enhancements:
  #  give option, that if url already exists, update existing row
  #  give option, to load from csv file
  #  give option, to load all urls into unassigned unassigned
  #  give option, to delete bookmarks,cat,subcat before import
  #

  # find existing category matching name, or
  # create a new one. return id.
  function getCategory ($name)
  {
     global $phpgw, $phpgw_info, $cat, $catNext, $default_category;

     $db = $phpgw->db;
     $upperName = strtoupper($name);
     
     if (! $name) {
        $cat[$upperName] = $default_category;
        return $default_category;
     }

     if (isset($cat[$upperName])) {
//        echo "<br>Category - $name exsists";
        return $cat[$upperName];
     } else {
        $q  = "INSERT INTO bookmarks_category (name, username) ";
        $q .= "VALUES ('" . addslashes($name) . "', '" . $phpgw_info["user"]["account_id"] . "') ";

        $db->query($q,__LINE__,__FILE__);
        if ($db->Errno != 0) {
           $error_msg .= "<br>Error adding category ".$name." - ".$catNext;
           return -1;
        }

        $db->query("select id from bookmarks_category where name='" . addslashes($name) . "' and username='"
                 . $phpgw_info["user"]["account_id"] . "'",__LINE__,__FILE__);
        $db->next_record();

//        echo "<br>Category - $name does <b>not</b> exsists - Creating with id: " . $db->f("id");

        $cat[$upperName] = $db->f("id");
        $catNext++;
        return $db->f("id");
     }
  }

  # find existing subcategory matching name, or
  # create a new one. return id.
  function getSubCategory ($name)
  {
     global $phpgw,$phpgw_info,$subcat,$subcatNext,$default_subcategory;

     $db = $phpgw->db;
     $upperName = strtoupper($name);
     
     if (! $name) {
        $subcat[$upperName] = $default_subcategory;
        return $default_subcategory;
     }

     if (isset($subcat[$upperName])) {
        return $subcat[$upperName];
     } else {
        $q  = "INSERT INTO bookmarks_subcategory (name, username) ";
        $q .= "VALUES ('" . addslashes($name) . "', '" . $phpgw_info["user"]["account_id"] . "') ";

        $db->query($q,__LINE__,__FILE__);
        if ($db->Errno != 0) {
           $error_msg .= "<br>Error adding subcategory ".$name." - ".$subcatNext;
           return -1;
        }
        
        $db->query("select id from bookmarks_subcategory where name='" . addslashes($name) . "' and username='"
                 . $phpgw_info["user"]["account_id"] . "'",__LINE__,__FILE__);
        $db->next_record();
        
        $subcat[$upperName] = $db->f("id");
        $subcatNext++;
        return $db->f("id");
     }
  }


  $phpgw->template->set_file(array(standard            => "common.standard.tpl",
                                   body                => "import.body.tpl"
                            ));

  set_standard("import", &$phpgw->template);

  ## Check if there was a submission
  while (is_array($HTTP_POST_VARS) && list($key, $val) = each($HTTP_POST_VARS)) {
     switch ($key) {

     ## import bookmarks
     case "bk_import":
     if (!$debug) {
        print ("\n<!--\n");
     }
     $bmark = new bmark;

     print ("<p><b>DEBUG OUTPUT:</b>\n");
     print ("<br>file: " . $bkfile . "\n");
     print ("<br>file_name: " . $bkfile_name . "\n");
     print ("<br>file_size: " . $bkfile_size . "\n");
     print ("<br>file_type: " . $bkfile_type . "\n<p><b>URLs:</b>\n");

     if (empty($bkfile) || $bkfile == "none") {
        $error_msg .= "<br>Netscape bookmark filename is required!";
        break;
     }
     $default_rating = 0;

     $phpgw->db->query("select id from bookmarks_category where name='--' and username='"
                     . $phpgw_info["user"]["account_id"] . "'",__LINE__,__FILE__);
     $phpgw->db->next_record();
     $default_category = $phpgw->db->f("id");

     $phpgw->db->query("select id from bookmarks_subcategory where name='--' and username='"
                     . $phpgw_info["user"]["account_id"] . "'",__LINE__,__FILE__);
     $phpgw->db->next_record();
     $default_subcategory = $phpgw->db->f("id");

     $fd = @fopen($bkfile, "r");
     if ($fd) {
        # read current categories into an array
        $catNext = -1;
        $query = sprintf("select id, name from bookmarks_category where username='%s' order by id",$phpgw_info["user"]["account_id"]);
        $phpgw->db->query($query,__LINE__,__FILE__);
        if ($phpgw->db->Errno != 0)
           break;
        while ($phpgw->db->next_record()) {
           $cat[strtoupper($phpgw->db->f("name"))] = $phpgw->db->f("id");
           $catNext = $phpgw->db->f("id");
        }
        $catNext++;
    
        # read current subcategories into an array
        $subcatNext = -1;
        $query = sprintf("select id, name from bookmarks_subcategory where username='%s' order by id",$phpgw_info["user"]["account_id"]);
        $phpgw->db->query($query,__LINE__,__FILE__);
       if ($phpgw->db->Errno != 0)
          break;
       while ($phpgw->db->next_record()) {
          $subcat[strtoupper($phpgw->db->f("name"))] = $phpgw->db->f("id");
          $subcatNext = $phpgw->db->f("id");
       }
       $subcatNext++;

       $inserts = 0;
       $folder_index = -1;
       $cat_index = -1;
       $scat_index = -1;
       $bookmarker->url_format_check = 0;
       $bookmarker->url_responds_check = false;
   
       while ($line = @fgets($fd, 2048)) {
         ## URLs are recognized by A HREF tags in the NS file.
         if (eregi('<A HREF="([^"]*)[^>]*>(.*)</A>', $line, $match)) {
   
           $url_parts = @parse_url($match[1]);
           if ($url_parts[scheme] == "http"
             || $url_parts[scheme] == "https"
             || $url_parts[scheme] == "ftp"
             || $url_parts[scheme] == "news") {
   
             reset($folder_stack);
             unset($error_msg);
             $cid = $default_category;
             $scid = $default_subcategory;
             $i = 0;
             $keyw = '';
             while ($i <= $folder_index) {
               if ($i == 0) {
                  $cid = getCategory($folder_name_stack[$i]);
               } elseif ($i == 1) {
                  $scid = getSubCategory($folder_name_stack[$i]);
               }
               $keyw .= ' ' . $folder_name_stack[$i];
               $i++;
             }
   
          $bid = -1;
          if (!$bmark->add(&$bid, trim(addslashes($match[1])), trim(addslashes($match[2])), 
                 trim(addslashes($match[2])), trim($keyw), $cid, $scid, $default_rating, $public)) {
            print("<br>" . $error_msg . "\n");
            $all_errors .= $error_msg;
          }

          printf("<br>%s,%s,%s,%s,<i>%s</i>\n",$cid,$scid,$match[2],$match[1],$bid);
          if (! $error_msg) {
             $inserts++;
          }
        }
      }

      ## folders start with the folder name inside an <H3> tag,
      ## and end with the close </DL> tag.
      ## we use a stack to keep track of where we are in the
      ## folder hierarchy.
      elseif (eregi('<H3[^>]*>(.*)</H3>', $line, $match)) {
        $folder_index ++;
        $id = -1;

        if ($folder_index == 0) {
          $cat_index ++;
          $cat_array [$cat_index] = $match[1];
          $id = $cat_index + $cat_start;

        } elseif ($folder_index == 1) {
          $scat_index ++;
          $scat_array [$scat_index] = $match[1];
          $id = $scat_index + $scat_start;
        }
        $folder_stack [$folder_index] = $id;
        $folder_name_stack [$folder_index] = $match[1];

      }
      elseif (eregi('</DL>', $line)) {
        $folder_index-- ;
      }
    }

    @fclose($fd);

  } else {
    $error_msg .= "<br>Unable to open temp file " . $bkfile . " for import.";
  }
    unset($msg);
    $msg .= sprintf("<br>%s bookmarks imported from %s successfully.", $inserts, $bkfile_name);
    if (!$debug) print ("\n-->\n");
    $error_msg = $all_errors;
    break;

  default:
    break;
 }
}

  $phpgw->template->set_var(array(FORM_ACTION => $phpgw->link()));
  include($phpgw_info["server"]["server_root"] . "/bookmarks/inc/footer.inc.php");
?>
