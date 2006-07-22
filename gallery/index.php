<?php
/**
 * eGroupWare Gallery2 integration
 * 
 * @link http://www.egroupware.org
 * @link http://gallery.sourceforge.net/
 * @package gallery
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright 2006 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
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

$GLOBALS['egw']->g2 =& CreateObject('gallery.g2_integration',false);	// no fullinit, we are going to call handleRequest

if ($GLOBALS['egw']->g2->error)
{
	$content = $GLOBALS['egw']->g2->error;
}
else
{
	$content = $GLOBALS['egw']->g2->handleRequest('core',$GLOBALS['egw_info']['flags']['app_header']);
}

$GLOBALS['egw']->common->egw_header();

echo $content;

$GLOBALS['egw']->common->egw_footer();