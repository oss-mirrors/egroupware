<?php
/**
 * eGroupWare - Polls
 * http://www.egroupware.org 
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package polls
 * @copyright 1999 by Till Gerken <tig@skv.org>
 * @author Till Gerken <tig@skv.org>
 * @author Greg Haygood <shrykedude@bellsouth.net>
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @version $Id$
 */

$GLOBALS['egw_info'] = array(
	'flags' => array(
		'currentapp'              => 'polls',
		'noheader'                => True,
		'nonavbar'                => True,
	),
);
include('../header.inc.php');

ExecMethod('polls.uipolls.index');

$GLOBALS['egw']->common->egw_footer();
