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

# print any error msgs from the current page
if (! empty($error_msg)) {
	$bk_print_error_msg = $error_msg;
}

# print any other error msgs that haven't
# been printed yet - like from another page
if (! empty($sess_error_msg)) {
	$bk_print_error_msg .= $sess_error_msg;
	$sess_error_msg ='';
}

# print any warn msgs from the current page
if (! empty($warn_msg)) {
	$bk_print_warn_msg = $warn_msg;
}

# print any other warn msgs that haven't
# been printed yet - like from another page
if (! empty($sess_warn_msg)) {
	$bk_print_warn_msg .= $sess_warn_msg;
	$sess_warn_msg = '';
}

# print any info msgs from the current page
if (! empty($msg)) {
	$bk_print_msg = $msg;
}

# print any other info msgs that haven't
# been printed yet - like from another page
if (! empty($sess_msg)) {
	$bk_print_msg .= $sess_msg;
	$sess_msg = '';
}

if (! empty($bk_print_error_msg)) {
	$bk_output_html = sprintf("<tr><td align=\"center\"><table bgcolor=red cellpadding=2><tr><td><font color=white size=1>%s</font></td></tr></table></td></tr>\n", $bk_print_error_msg);
}
if (! empty($bk_print_warn_msg)) {
	$bk_output_html .= sprintf("<tr><td align=\"center\"><table bgcolor=blue cellpadding=2><tr><td><font color=white size=1>%s</font></td></tr></table></td></tr>\n", $bk_print_warn_msg);
}
if (! empty($bk_print_msg)) {
	$bk_output_html .= sprintf("<tr><td align=\"center\"><table bgcolor=green cellpadding=2><tr><td><font color=white size=1>%s</font></td></tr></table></td></tr>\n", $bk_print_msg);
}
?>
