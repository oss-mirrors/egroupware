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

  // print any error msgs from the current page
  if (! empty($error_msg)) {
  	$bk_print_error_msg = $error_msg;
  }
   
  // print any other error msgs that haven't
  // been printed yet - like from another page
  if (! empty($sess_error_msg)) {
  	$bk_print_error_msg .= $sess_error_msg;
 	 $sess_error_msg ='';
  }
   
  // print any warn msgs from the current page
  if (! empty($warn_msg)) {
  	$bk_print_warn_msg = $warn_msg;
  }
   
  // print any other warn msgs that haven't
  // been printed yet - like from another page
  if (! empty($sess_warn_msg)) {
  	$bk_print_warn_msg .= $sess_warn_msg;
 	 $sess_warn_msg = '';
  }
   
  // print any info msgs from the current page
  if (! empty($msg)) {
 	 $bk_print_msg = $msg;
  }
  
  // print any other info msgs that haven't
  // been printed yet - like from another page
  if (! empty($sess_msg)) {
 	 $bk_print_msg .= $sess_msg;
  	$sess_msg = '';
  }
  
  if (! empty($bk_print_error_msg)) {
  	$bk_output_html = sprintf("<tr><td align=\"center\"><table cellpadding=2><tr><td><b>" . lang("error") . ":</b>%s</td></tr></table></td></tr>\n", $bk_print_error_msg);
  }
  if (! empty($bk_print_warn_msg)) {
 	 $bk_output_html .= sprintf("<tr><td align=\"center\"><table cellpadding=2><tr><td><b>" . lang("Warning") . ":</b>%s</td></tr></table></td></tr>\n", $bk_print_warn_msg);
  }
  if (! empty($bk_print_msg)) {
 	 $bk_output_html .= sprintf("<tr><td align=\"center\"><table cellpadding=2><tr><td>%s</td></tr></table></td></tr>\n", $bk_print_msg);
  }
?>