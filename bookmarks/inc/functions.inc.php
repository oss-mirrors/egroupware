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

  define ("LIBDIR", dirname(__FILE__)."/");
  define ("DBDIR", LIBDIR."db/");
  //define ("PHPLIBDIR", LIBDIR."phplib/");
  // the session auto_init file include uses this PHPLIB path
  $_PHPLIB["libdir"] = LIBDIR;
  //define ("TEMPLATEDIR", LIBDIR."templates");
  # the following files are required for the correct
  # operation of PHPLIB and bookmarker

  //require(PHPLIBDIR . "ct_sql.inc");
  //require(PHPLIBDIR . "sqlquery.inc");
  require(LIBDIR    . "class.Validator.inc");
  //require(LIBDIR    . "bklocal.inc");
  //require(PHPLIBDIR . "page.inc");
  require(LIBDIR    . "bmark.inc");
  require(LIBDIR    . "bookmarker.inc");
  require(LIBDIR    . "bkshared.inc");

class bktemplate extends Template {
  var $classname = "bktemplate";
  
  /* if set, echo assignments */
  /* 1 = debug set, 2 = debug get, 4 = debug internals */
  var $debug     = false;
  
  /* "yes" => halt, "report" => report error, continue, 
   * "no" => ignore error quietly 
  */
  var $halt_on_error  = "yes";
# 
# override the finish function to better handle with javascript.
# we don't have whitespace in our var names, so no need to be
# so all encompassing with the remove.

  function finish($str) {
    switch ($this->unknowns) {
      case "keep":
      break;
      
      case "remove":
        $str = preg_replace("/\{[-_a-zA-Z0-9]+\}/", "", $str);
      break; 
      
      case "comment":
        $str = preg_replace("/\{([-_a-zA-Z0-9]+)\}/", "<!-- Template $handle: Variable \\1 undefined -->", $str);
      break; 
    } 
    return $str;
  } 
}

?>
