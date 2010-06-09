<?php
/**
 * eGroupWare - Setup
 * http://www.egroupware.org
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package polls
 * @subpackage setup
 * @copyright 1999 by Till Gerken <tig@skv.org>
 * @author Till Gerken <tig@skv.org>
 * @author Greg Haygood <shrykedude@bellsouth.net>
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @version $Id$
 */

/* Basic information about this app */
$setup_info['polls']['name']      = 'polls';
$setup_info['polls']['title']     = 'Polls';
$setup_info['polls']['version']   = '1.4';
$setup_info['polls']['app_order'] = 17;
$setup_info['polls']['enable']    = 1;
$setup_info['polls']['license']   = 'GPL';
$setup_info['polls']['index']     = 'polls.uipolls.index';

/* The tables this app creates */
$setup_info['polls']['tables']    = array('egw_polls','egw_polls_answers','egw_polls_votes');

$setup_info['polls']['hooks'][]   = 'admin';
$setup_info['polls']['hooks'][]   = 'sidebox_menu';

/* Dependencies for this app to work */
$setup_info['polls']['depends'][] = array(
	'appname' => 'phpgwapi',
	'versions' => Array('1.2','1.3','1.4','1.5','1.6','1.7')
);
