<?php
# ---------------------------------------------------------------
# bookmarker
# A WWW based bookmark management, retrieval, and search tool.
# Copyright (C) 1998  Padraic Renaghan
# Licensed under terms of GNU General Public License
# (see http://www.renaghan.com/bookmarker/source/LICENSE)
# ---------------------------------------------------------------
# $Id$
# ---------------------------------------------------------------

# directory where bkprepend is. All included files
# are either in this dir, or one of its subdirs.
define ("LIBDIR", dirname(__FILE__)."/");
define ("DBDIR", LIBDIR."db/");
define ("PHPLIBDIR", LIBDIR."phplib/");
# the session auto_init file include uses this PHPLIB path
$_PHPLIB["libdir"] = LIBDIR;
//define ("TEMPLATEDIR", LIBDIR."templates");

# the following files are required for the correct
# operation of PHPLIB and bookmarker

/*# Change this to match your database.
#require(PHPLIBDIR . "db_mysql.inc"); 
#require(PHPLIBDIR . "db_pgsql.inc");
#require(PHPLIBDIR . "db_odbc.inc"); */

#require(PHPLIBDIR . "ct_sql.inc");
//require(PHPLIBDIR . "session.inc");
//require(PHPLIBDIR . "auth.inc");
//require(PHPLIBDIR . "perm.inc");
//require(PHPLIBDIR . "user.inc");
#require(PHPLIBDIR . "sqlquery.inc");
//require(PHPLIBDIR . "template.inc");
require(LIBDIR    . "class.Validator.inc");
require(LIBDIR    . "bklocal.inc");
#require(PHPLIBDIR . "page.inc");
require(LIBDIR    . "bmark.inc");
require(LIBDIR    . "bookmarker.inc");
require(LIBDIR    . "bkshared.inc");
?>
