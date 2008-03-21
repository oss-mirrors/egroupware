<?php
/**
 * eGroupWare Gallery2 integration
 * 
 * @link http://www.egroupware.org
 * @link http://gallery.sourceforge.net/
 * @package gallery
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright 2006-8 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

$GLOBALS['egw_info'] = array(
	'flags' => array(
		'disable_Template_class' => true,
		'currentapp' => 'gallery',
		'noheader' => true,
	),
);

include('../header.inc.php');

require_once(EGW_INCLUDE_ROOT.'/gallery/inc/class.g2_integration.inc.php');
$g2 = new g2_integration(false);	// no fullinit, we are going to call handleRequest

if ($g2->error)
{
	$GLOBALS['egw']->framework->render($g2->error);
}
else
{
	$GLOBALS['egw']->framework->render($g2->handleRequest('core',$GLOBALS['egw_info']['flags']['app_header']));
}
