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

	$GLOBALS['phpgw_info']['flags']['currentapp'] = 'bookmarks';
	include('../header.inc.php');
?>
<center>Not avaiable</center>
<?php
	$GLOBALS['phpgw']->common->phpgw_exit();

	// Not sure if this is gonna be used, but its here for reference

	include(dirname(__FILE__) . '/lib/bkprepend.inc');
	page_open(array( 'sess' => 'bk_sess_cache'));

	$tpl->set_file(array(
		'standard' => 'common.standard.tpl',
		'body'     => 'faq.body.tpl',
		'msie_qm'  => 'faq.msie.quik-mark.tpl',
		'ns_qm'    => 'faq.ns.quik-mark.tpl',
		'msie_ml'  => 'faq.msie.mail-this-link.tpl',
		'ns_ml'    => 'faq.ns.mail-this-link.tpl'
	));

	set_standard("faq", &$tpl);

	$tpl->set_var(array(
		'CREATE_URL'         => $bookmarker->create_url,
		'MAIL_THIS_LINK_URL' => $bookmarker->maillink_url,
		'IMAGE_URL_PREFIX'   => $bookmarker->image_url_prefix,
		'USER_AGENT'         => $HTTP_USER_AGENT
	));

	if (check_browser() == 'MSIE')
	{
		$tpl->parse('QUIK_MARK_LINK', 'msie_qm');
		$tpl->parse('MAIL_THIS_LINK', 'msie_ml');
	}
	else
	{
		$tpl->parse('QUIK_MARK_LINK', 'ns_qm');
		$tpl->parse('MAIL_THIS_LINK', 'ns_ml');
	}

	include(LIBDIR . 'bkend.inc');
	$GLOBALS['phpgw']->common->phpgw_footer();
?>
