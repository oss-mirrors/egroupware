<?php
/**
 * eGroupWare Gallery2 integration: embeding the whole gallery
 *
 * @link http://www.egroupware.org
 * @link http://gallery.sourceforge.net/
 * @package gallery
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright 2006 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

include_once(EGW_INCLUDE_ROOT.'/gallery/inc/class.g2_integration.inc.php');

/**
 * extends SiteMgr's Module class and instanciates the g2_integration class to view pages
 */
class module_gallery extends Module
{
	function module_gallery()
	{
		$GLOBALS['egw']->translation->add_app('gallery');

		$this->arguments = array(
			'type' => array(
				'type' => 'select',
				'label' => lang('Type of the embeded Gallery'),
				'options' => array(
					'complete' => lang('Complete Gallery'),
					'core'     => lang('Gallery without sidebar'),
					'sidebar'  => lang('Gallery sidebar'),
				)
			),
			'set_page_title' => array(
				'type' => 'checkbox',
				'label' => lang('Set Gallery title as page-title?'),
			),
			'itemId' => array(
				'type' => 'textfield',
				'label' => lang('Item ID (number)').
					'<br />'.lang('Default item to show in the Gallery.').
					'<br />'.lang('You can get the ID from the "g2_itemId" parameter in the URL, when you click on the item.'),
			),
		);
		$this->properties = array();
		$this->title = lang('Gallery');
		$this->description = lang('Use this module to display the embeded Gallery');

		$GLOBALS['egw']->translation->add_app('gallery');
	}

	function get_content(&$arguments,$properties)
	{
		if (!@$GLOBALS['egw_info']['user']['apps']['gallery'])
		{
			return lang('You have no rights to view %1 content !!!',lang('Gallery'));
		}
		if (!isset($GLOBALS['egw']->g2))
		{
			$GLOBALS['egw']->g2 = new g2_integration(false,sitemgr_link(array('page_name' => $GLOBALS['page']->name)));

			if ($GLOBALS['egw']->g2->error)
			{
				return $GLOBALS['egw']->g2->error;
			}
		}
		$content = '';
		if ($arguments['type'] != 'complete')
		{
			$content = '<link rel="stylesheet" type="text/css" href="'.$GLOBALS['egw_info']['server']['webserver_url'].
			'/gallery/templates/default/app.css"/>'."\n";
		}
		if ($arguments['itemId'] && !isset($_GET['g2_itemId']))
		{
			$_GET['g2_itemId'] = $arguments['itemId'];
			foreach($GLOBALS['egw']->g2->get_before_g2 as $name => $value)
			{
				if (substr($name,0,3) == 'g2_')
				{
					unset($_GET['g2_itemId']);
					break;
				}
			}
		}
		$content .= $GLOBALS['egw']->g2->handleRequest($arguments['type'],$title);

		if ($arguments['set_page_title'])
		{
			$GLOBALS['page']->title = $title;
		}
		return $content;
	}
}
