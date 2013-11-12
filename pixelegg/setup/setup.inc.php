<?php
/**
 * EGroupware: Stylite Pixelegg template
 *
 * @link http://www.egroupware.org
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @author Stefan Reinhard <stefan.reinhard@pixelegg.de>
 * @package pixelegg
 * @version $Id$
 */

$GLOBALS['egw_info']['template']['pixelegg']['name']      = 'pixelegg';
$GLOBALS['egw_info']['template']['pixelegg']['title']     = 'Pixelegg';
$GLOBALS['egw_info']['template']['pixelegg']['version']   = '1.0';

$GLOBALS['egw_info']['template']['pixelegg']['author'] = array(
   array('name' => 'Stylite AG', 'url' => 'http://www.stylite.de/'),
   array('name' => 'Pixelegg Informationsdesign', 'url' => 'http://www.pixelegg.de/'),
);
$GLOBALS['egw_info']['template']['pixelegg']['license'] = array(
	'name' => 'Stylite EPL license',
	'url'  => 'http://www.stylite.de/EPL',
);
$GLOBALS['egw_info']['template']['pixelegg']['icon'] = "pixelegg/images/logo.png";
$GLOBALS['egw_info']['template']['pixelegg']['maintainer'] = array(
   array('name' => 'Stylite AG', 'url' => 'http://www.stylite.de/')
);
$GLOBALS['egw_info']['template']['pixelegg']['description'] = "Pixelegg EGroupware template based on Stylite template using jQuery.";
$GLOBALS['egw_info']['template']['pixelegg']['windowed'] = true;

/* Dependencies for this template to work */
$GLOBALS['egw_info']['template']['pixelegg']['depends'][] = array(
	'appname' => 'jdots',
	'versions' => Array('1.0')
);

