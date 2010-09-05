<?php
/**
 * EGroupware Gallery2 integration
 *
 * @link http://www.egroupware.org
 * @link http://gallery.sourceforge.net/
 * @package gallery
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright 2006-8 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/* Basic information about this app */
$setup_info['gallery']['name']      = 'gallery';
$setup_info['gallery']['title']     = 'Gallery';
$setup_info['gallery']['version']   = '1.8';
$setup_info['gallery']['app_order'] = 4;
$setup_info['gallery']['enable']    = 1;

$setup_info['gallery']['author'] = 'Gallery project';
$setup_info['gallery']['author_url'] = 'http://gallery.sourceforge.net';

$setup_info['gallery']['license']  = 'GPL';
$setup_info['gallery']['description'] = 'Gallery2 integration into eGroupWare';
$setup_info['gallery']['note'] = '';

$setup_info['gallery']['maintainer'] = 'Ralf Becker';
$setup_info['gallery']['maintainer_email'] = 'RalfBecker@outdoor-training.de';

$setup_info['gallery']['tables']  = array();	// hangled by g2 itself atm.

$setup_info['gallery']['hooks']['addaccount']		= 'gallery.g2_integration.addAccount';
$setup_info['gallery']['hooks']['deleteaccount']	= 'gallery.g2_integration.deleteAccount';
$setup_info['gallery']['hooks']['editaccount']		= 'gallery.g2_integration.editAccount';
$setup_info['gallery']['hooks']['logout']			= 'gallery.g2_integration.logout';
$setup_info['gallery']['hooks']['sidebox_menu']		= 'gallery.g2_integration.menus';
$setup_info['gallery']['hooks']['admin']			= 'gallery.g2_integration.menus';

/* Dependencies for this app to work */
$setup_info['gallery']['depends'][] = array(
	'appname' => 'phpgwapi',
	'versions' => Array('1.7','1.8','1.9')
);
