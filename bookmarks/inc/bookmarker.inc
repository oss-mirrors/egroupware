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
  
# the following class sets various configuration variables
# used throughout the application.
class bookmarker_class  {
  var $version        = "2.8.0";

# directory where templates are located on this server
  var $template_dir   = "./lib/templates";

# image URL - string added to the begining of an image file
# (for example, I set this to "./images/" which makes bookmarker
# build image URLs like <img src="./images/mailto.png"...)
  var $image_url_prefix = "./images/";

# URL format checking. bookmarker can check the format of
# URLs entered on the create/maintain pages. This option
# lets you control this checking. Possible values:
#  0 = no checking of URL format
#  1 = URL format is checked, problems reported as warnings
#  2 = URL format is checked, problems reported as errors
  var $url_format_check = 2;

# URL response checking. bookmarker can check that the URL
# responds to a request and show a warning if it does not
# respond.
  var $url_responds_check = true;

# how many URLs to show per page on the plain list page
# NOTE: If your database doesn't support the "LIMIT offset, rows"
# statement, set this to zero. In that case the LIMIT clause
# won't be added to the SQL. You will get all bookmarks on a
# single plain list page.
  var $urls_per_page  = 10;

# how many characters after the scheme(http://) and hostname
# (www.mydomain.com) to match when checking for possible
# duplicates on the create page.
# Zero means to just match on scheme and hostname - this is
# what I prefer.
  var $possible_dup_chars = 0;

# level of access required for user to use the mail-this-link
# page. The default is to only allow registered users to send
# email using bookmarker - anything else is asking for abuse!
# if you allow guest, you may want to bcc yourself by using the
# site header variable below.
  var $mail_this_link_permission_required = "editor";

# this var controls if the bookmarker links (start, create, search...)
# are displayed in the tree view. NOTE: these links are only displayed
# if 'group by category/subcategory' is also selected.
  var $show_bk_in_tree = 0; # set to 0 for 'off' 1 for 'on'

# for how many seconds is the "permanent" login cookie good
# for. Defaulted here to 100 days.
  var $perm_cookie_lifetime=8640000;

# class constructor for bookmarker_class
# setup a few more variables that are based on variables.
  function bookmarker_class () {
    global $SERVER_NAME;
    global $SERVER_ADMIN;
    global $REMOTE_ADDR;
    global $PHP_SELF;

    $where_am_i = sprintf("http://%s%s/", $SERVER_NAME, dirname($PHP_SELF));

# used for quik-mark bookmark
    $this->create_url   = $where_am_i . "create.php";

# used for mail-this-link bookmark
    $this->maillink_url = $where_am_i . "maillink.php";

# the following wording is automatically added to all outgoing
# mail-this-link email messages
    $this->site_footer  = sprintf("--\nThis message sent from the bookmarker bookmark manager\nat %s\nPlease contact the server administrator at\n%s to report abuse of this service.", $where_am_i, $SERVER_ADMIN);

# this var controls the headers that are added to the mail-this-link
# email message. You may choose to bcc: yourself, record the senders IP...
# the headers should be separated by a newline ("\n")
    $this->site_headers = sprintf("X-Sender: bookmarker at %s\nX-Sender-IP: $REMOTE_ADDR", $SERVER_NAME);
  }
     
}

# instantiate the bookmarker class so we can access
# the variables.
$bookmarker = new bookmarker_class ();

# if the user's browser is a 5.0 or later version, then
# use PNG images. otherwise use GIF images.
$bookmarker->image_ext="gif";

if (ereg( "MSIE ([0-9]+)",$HTTP_USER_AGENT,$version)) {
  $ver=(int)$version[1];
  if ($ver>=5) $bookmarker->image_ext="png";

} elseif (ereg( "Opera/([0-9]+)",$HTTP_USER_AGENT,$version)) {
    # $opera=true;

} elseif (ereg( "Mozilla/([0-9]+)",$HTTP_USER_AGENT,$version)) {
  $ver=(int)$version[1];
  if ($ver>=5) $bookmarker->image_ext="png";
}

# every bookmarker page uses templates to generate HTML.
//$tpl = new bktemplate;
//$tpl->set_root(TEMPLATEDIR);
#$phpgw->template->set_unknowns("remove");

# create an instance of the data validation class
$validate = new Validator ();
?>
