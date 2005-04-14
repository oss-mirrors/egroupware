<?php

// $CVSHeader: gtz_realss_com/mydms/adodb/session/adodb-cryptsession.php,v 1.1.1.1 2005/03/18 09:18:06 dawnlinux Exp $

/*
V4.01 23 Oct 2003  (c) 2000-2004 John Lim (jlim@natsoft.com.my). All rights reserved.
         Contributed by Ross Smith (adodb@netebb.com). 
  Released under both BSD license and Lesser GPL library license.
  Whenever there is any discrepancy between the two licenses,
  the BSD license will take precedence.
	  Set tabs to 4 for best viewing.
*/

/*

This file is provided for backwards compatibility purposes

*/

require_once dirname(__FILE__) . '/adodb-session.php';
require_once  ADODB_SESSION . '/adodb-encrypt-md5.php';

ADODB_Session::filter(new ADODB_Encrypt_MD5());

?>