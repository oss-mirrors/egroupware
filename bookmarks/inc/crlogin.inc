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
global $bookmarker;

# every bookmarker page uses templates to generate HTML.
$login_tpl = new bktemplate;
$login_tpl->set_root(TEMPLATEDIR);
$login_tpl->set_unknowns("remove");

$login_tpl->set_file(array(
    standard    => "login.standard.tpl",
    msie_js     => "common.msie_js.tpl",
	  body        => "bkloginform.body.tpl",
	  invalid_msg => "bkloginform.invalid.tpl"
));

set_standard("login", &$login_tpl);

# if browser is MSIE, then need to add this bit
# of javascript to the page so that MSIE correctly
# brings quik-mark and mail-this-link popups to the front.
if (check_browser() == "MSIE") {
  $login_tpl->parse(MSIE_JS, "msie_js");
}

if (isset($this->auth["uname"])) {
  $default_username = $this->auth["uname"];
}

if (! isset($default_username)) {
  $default_username = "";
}

$login_tpl->set_var(array(
  FORM_ACTION            => $sess->self_url(),
  CANCEL_LOGIN           => $sess->url("index.php3?cancel_login=YES"),
  CHALLENGE              => $challenge,
  DEFAULT_USERNAME       => $default_username
));


global $username; 
if (isset($username)) {
  $login_tpl->parse(INVALID_MSG, "invalid_msg");
}

$login_tpl->parse("BODY", array("body", "standard"));
$login_tpl->p("BODY");
?>
