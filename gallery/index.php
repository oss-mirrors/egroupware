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

$g2 =& CreateObject('gallery.g2_integration',false);	// no fullinit, we are going to call GalleryEmbed::handleRequest

if ($g2->error)
{
	$content = $g2->error;
}
else
{
	GalleryCapabilities::set('showSidebarBlocks', false);

	$g2_data = GalleryEmbed::handleRequest();
	
	if ($g2_data['isDone'])	// redirect, download, ...
	{
		$GLOBALS['egw']->egw_exit();
	}
	list($title,$css,$js) = GalleryEmbed::parseHead($GLOBALS['egw']->translation->convert($g2_data['headHtml'],'utf-8'));
	$GLOBALS['egw_info']['flags']['app_header'] = $title;
	$GLOBALS['egw_info']['flags']['java_script'] .= implode("\n",$js);
	
	$content = implode("\n",$css)."\n".$GLOBALS['egw']->translation->convert($g2_data['bodyHtml'],'utf-8');
}

$GLOBALS['egw']->common->egw_header();

//echo "<pre>".htmlspecialchars(print_r($g2_data['themeData'],true))."</pre>\n";

echo $content;

$GLOBALS['egw']->common->egw_footer();