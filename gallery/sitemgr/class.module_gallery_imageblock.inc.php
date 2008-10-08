<?php
/**
 * eGroupWare Gallery2 integration: image-block
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
class module_gallery_imageblock extends Module
{
	function module_gallery_imageblock()
	{
		$GLOBALS['egw']->translation->add_app('gallery');

		$this->arguments = array(
			'blocks' => array(
				'type' => 'select',
				'label' => lang('One or more blocks to show'),
				'options' => array(
				),
				'multiple' => 4,
			),
			'show' => array(
				'type' => 'select',
				'label' => lang('Extra information to show'),
				'options' => array(
					'title' => lang('Title'),
					'date'  => lang('Date'),
					'views' => lang('View count'),
					'owner' => lang('Owner'),
					'heading'  => lang('Heading'),
					'fullSize' => lang('Full size'),
				),
				'multiple' => 3,
			),
			'maxSize' => array(
				'type' => 'textfield',
				'label' => lang('Size (maximum number of pixel of the longest side)'),
			),
			'itemFrame' => array(
				'type' => 'select',
				'label' => lang('Image frame (%1Samples%2)'),
				'options' => array(
				),
			),
			'albumFrame' => array(
				'type' => 'select',
				'label' => lang('Album frame (%1Samples%2)'),
				'options' => array(
				),
			),
			'block_title' => array(
				'type' => 'select',
				'label' => lang('Set block title to'),
				'options' => array(
					'' => lang('Not'),
					'heading' => lang('Heading'),
					'title'   => lang('Title'),
				)
			),
			'page_name' => array(
				'type' => 'textfield',
				'label' => lang('Pagename of an other page to link to').
					'<br />'.lang('Should contain a "gallery" block in contentarea "center".'),
			),
			'itemId' => array(
				'type' => 'textfield',
				'label' => lang('Item ID (number)').
					'<br />'.lang('Can be used to limit the block to a certain album.').
					'<br />'.lang('You can get the ID from the "g2_itemId" parameter in the URL, when you click on the item.'),
			),
			'align' => array(
				'type' => 'select',
				'label' => lang('Alignment of the block'),
				'options' => array(
					'' => lang('Left'),
					'center' => lang('Center'),
					'right'   => lang('Right'),
				)
			),
		);
		$this->properties = array();
		$this->title = lang('Imageblock');
		$this->description = lang('Imageblock of the Gallery');

		$GLOBALS['egw']->translation->add_app('gallery');
	}

	/**
	 * Reimplemented to fetch some settings from G2 at edit-time (not always, as it would be in the constructor)
	 *
	 * @return array
	 */
	function get_user_interface()
	{
		list($frames,$sample_url) = ExecMethod('gallery.g2_integration.frameTypes');
		$this->arguments['itemFrame']['options'] = $this->arguments['albumFrame']['options'] = $frames;
		$this->arguments['itemFrame']['label'] = lang('Image frame (%1Samples%2)','<a href="'.$sample_url.'" target="_blank">','</a>');
		$this->arguments['albumFrame']['label'] = lang('Album frame (%1Samples%2)','<a href="'.$sample_url.'" target="_blank">','</a>');

		$this->arguments['blocks']['options'] = ExecMethod('gallery.g2_integration.imageBlockTypes');

		return parent::get_user_interface();
	}

	function get_content(&$arguments,$properties)
	{
		if (!@$GLOBALS['egw_info']['user']['apps']['gallery'])
		{
			return lang('You have no rights to view %1 content !!!',lang('Gallery'));
		}
		if (!isset($GLOBALS['egw']->g2))
		{
			$GLOBALS['egw']->g2 = new g2_integration(true,sitemgr_link(array(
				'page_name' => $arguments['page_name'] ? $arguments['page_name'] : $GLOBALS['page']->name
			)));
			if ($GLOBALS['egw']->g2->error)
			{
				return $GLOBALS['egw']->g2->error;
			}
		}
		$params = array(
			'blocks' => $arguments['blocks'] ? implode('|',$arguments['blocks']) : 'randomImage',
			'show'   => $arguments['show']  ? implode('|',$arguments['show'])  : 'none',
		);
		foreach($arguments as $name => $value)
		{
			if (!isset($params[$name]) && $value)
			{
				$params[$name] = $value;
			}
		}
		$content = $GLOBALS['egw']->g2->imageBlock($params);

		switch($arguments['block_title'])
		{
			case 'heading':
				if (preg_match('/<h3>(.*)<\\/h3>/',$content,$matches))
				{
					$this->block->title = $matches[1];
					$content = str_replace('<h3>'.$this->block->title.'</h3>','',$content);
				}
				break;
			case 'title':
				foreach($lines=explode("\n",$content) as $n => $line)
				{
					if (strpos($line,'<h4') !== false)
					{
						$this->block->title = $lines[$n+1];
						unset($lines[$n]);unset($lines[$n+1]); unset($lines[$n+2]);
						$content = implode("\n",$lines);
						break;
					}
				}
				break;
		}

		if ($arguments['align'])
		{
			$content = '<div align="'.$arguments['align'].'">'."\n".$content."\n</div>\n";
		}
		return $content;
	}
}
