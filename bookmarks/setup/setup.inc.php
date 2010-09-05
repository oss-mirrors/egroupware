<?php
/**
 * EGroupware - Bookmarks
 *
 * Based on Bookmarker Copyright (C) 1998  Padraic Renaghan
 *                     http://www.renaghan.com/bookmarker
 *
 * @link http://www.egroupware.org
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package admin
 * @subpackage setup
 * @version $Id$
 */

/* Basic information about this app */
$setup_info['bookmarks']['name']      = 'bookmarks';
$setup_info['bookmarks']['title']     = 'Bookmarks';
$setup_info['bookmarks']['version']   = '1.9.001';
$setup_info['bookmarks']['app_order'] = '12';
$setup_info['bookmarks']['enable']    = 1;

$setup_info['bookmarks']['author'] = 'Joseph Engo';
$setup_info['bookmarks']['license']  = 'GPL';
$setup_info['bookmarks']['description'] =
	'Manage your bookmarks with EGroupware.  Has Netscape plugin.';
$setup_info['bookmarks']['maintainer'] = array(
	'name' => 'eGroupWare Developers',
	'email' => 'egroupware-developers@lists.sourceforge.net'
);

/* The tables this app creates */
$setup_info['bookmarks']['tables'][] = 'egw_bookmarks';
$setup_info['bookmarks']['tables'][] = 'egw_bookmarks_extra';

/* The hooks this app includes, needed for hooks registration */
$setup_info['bookmarks']['hooks']['preferences'] = 'bookmarks_hooks::all_hooks';
$setup_info['bookmarks']['hooks']['settings'] = 'bookmarks_hooks::settings';
$setup_info['bookmarks']['hooks']['admin'] = 'bookmarks_hooks::all_hooks';
$setup_info['bookmarks']['hooks']['sidebox_menu'] = 'bookmarks_hooks::all_hooks';
$setup_info['bookmarks']['hooks']['search_link'] = 'bookmarks_hooks::search_link';


/* Dependencies for this app to work */
$setup_info['bookmarks']['depends'][] = array(
	'appname'  => 'phpgwapi',
	'versions' => Array('1.7','1.8','1.9')
);
