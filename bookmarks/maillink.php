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

	$phpgw_info['flags'] = array(
		'currentapp'              => 'bookmarks',
		'enable_nextmatchs_class' => True,
		'enable_categories_class' => True
	);

	include('../header.inc.php');
	$phpgw->bookmarks  = createobject('bookmarks.bookmarks');
	$phpgw->send       = createobject('phpgwapi.send');

	$phpgw->template->set_file(array(
		'common'     => 'common.tpl',
		'footer'     => 'maillink_footer.tpl',
		'body'       => 'maillink.body.tpl'
	));
	app_header(&$phpgw->template);

	// if browser is MSIE, then need to add this bit
	// of javascript to the page so that MSIE correctly
	// brings quik-mark and mail-this-link popups to the front.
	if (check_browser() == 'MSIE')
	{
		$phpgw->template->parse(MSIE_JS,'msie_js');
	}

/*
	unset($from_name);
	unset($from);
	$query = sprintf("select name, email from auth_user where username = '%s'", ($auth->is_nobody()?"":$auth->auth["uname"]));
	$db->query($query);
	if ($db->Errno == 0) {
	  if ($db->next_record()){
 	   $from_name = $db->f("name");
  	  $from = $db->f("email");
  }
} */

## Check if there was a submission
while ( is_array($HTTP_POST_VARS)
     && list($key, $val) = each($HTTP_POST_VARS)) {
  switch ($key) {

  ## Send button clicked
  case "bk_send":

    ## Strip space and tab from anywhere in the To field
    $to = $validate->strip_space($to);

    ## Trim the subject
    $subject = trim($subject);

    ## Do we have all necessary data?
    if (empty($to) || empty($subject) || empty($message)) {
      $error_msg .= "<br>Please fill out <B>To E-Mail Address</B>, <B>Subject</B>, and <B>Message</B>!";
      break;
    }

    ## the To field may contain one or more email addresses
    ## separated by commas. Check each one for proper format.
    $to_array = explode(",", $to);

    while ( list( $key, $val ) = each( $to_array ) ) {
      ## Is email address in the proper format?
      if (!$validate->is_email($val))  {
        $error_msg .= "<br>To address $val invalid. Format must be <strong>user@domain</strong> and domain must exist!<br><small> $validate->ERROR </small>";
        break;
      }
    }

    if (isset ($error_msg)) {
      break;
    }

    ## add additional headers to our email
    $addl_headers = sprintf("From: %s <%s>", stripslashes($from_name), $from);

		$addl_headers = sprintf('%s\n%s',$addl_headers,$phpgw->template->parse('_footer','footer'));


    ## send the message
    mail($to, $subject, $mail_message, $addl_headers);

    $msg .= "<br>mail-this-link message sent to $to.";
    break;

  default:
    break;
 }
}

if (empty($subject)) {
  $subject = "Found a link you might like";
}

if (empty($message)) {

	$filtermethod = '( bm_owner=' . $phpgw_info['user']['account_id'];
	if (is_array($phpgw->bookmarks->grants))
	{
		$grants = $phpgw->bookmarks->grants;
		reset($grants);
		while (list($user) = each($grants))
		{
			$public_user_list[] = $user;
		}
		reset($public_user_list);
		$filtermethod .= " OR (bm_access='public' AND bm_owner in(" . implode(',',$public_user_list) . ')))';
	}
	else
	{
		$filtermethod .= ' )';
	}

	if ($mass_bm_id)
	{
		$bm_id = unserialize(stripslashes($mass_bm_id));
		echo '<pre>'; print_r($bm_id); echo '</pre>';

		while (list(,$id) = each($bm_id) && is_array($bm_id))
		{
			$phpgw->db->query("select * from phpgw_bookmarks where bm_id='$id' and $filtermethod",__LINE__,__FILE__);
			$phpgw->db->next_record();

			$links[] = array(
				'name' => $phpgw->db->f('bm_name'),
				'url'  => $phpgw->db->f('bm_url')
			);		
		}
	}
	else
	{
		$phpgw->db->query("select * from phpgw_bookmarks where bm_id='$bm_id' and $filtermethod",__LINE__,__FILE__);
		$phpgw->db->next_record();

		$links[] = array(
			'name' => $phpgw->db->f('bm_name'),
			'url'  => $phpgw->db->f('bm_url')
		);
	}

	$message = "I thought you would be interested in the following link(s):\n";
	while (list(,$link) = each($links))
	{
		$message .= sprintf("%s - %s\n",$link['name'],$link['url']);
	}
}

	$phpgw->template->set_var('th_bg',$phpgw_info['theme']['th_bg']);
	$phpgw->template->set_var('header_message',lang('Send bookmark'));
	$phpgw->template->set_var('lang_from',lang('Message from'));
	$phpgw->template->set_var('lang_to',lang('To E-Mail Addresses'));
	$phpgw->template->set_var('lang_subject',lang('Subject'));
	$phpgw->template->set_var('lang_message',lang('Message'));
	$phpgw->template->set_var('lang_send',lang('Send'));
	$phpgw->template->set_var('from_name',$phpgw->common->display_fullname());

$phpgw->template->set_var(array(
  FORM_ACTION     => $phpgw->link('/bookmarks/maillink.php'),
  FROM_NAME       => htmlspecialchars(stripslashes($from_name)),
  FROM            => $from,
  TO              => $to,
  SUBJECT         => $subject,
  MESSAGE         => $message,
  SITE_FOOTER     => nl2br($bookmarker->site_footer)
));

  $phpgw->common->phpgw_footer();
?>
