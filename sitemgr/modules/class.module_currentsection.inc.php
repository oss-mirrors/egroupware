<?php
	/**************************************************************************\
	* eGroupWare SiteMgr - Web Content Management                              *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

class module_currentsection extends Module
{
	function module_currentsection()
	{
		$this->arguments = array(
			'suppress_current_page' => array(
				'type' => 'checkbox',
				'label' => lang('Suppress the current page')
			),
			'suppress_parent' => array(
				'type' => 'checkbox',
				'label' => lang('Suppress link to parent category')
			),
			'suppress_show_all' => array(
				'type' => 'checkbox',
				'label' => lang('Suppress link to index (show all)')
			),
		);
		$this->properties = array();
		$this->title = lang('Current Section');
		$this->description = lang('This block displays the current section\'s table of contents');
	}

	function get_content(&$arguments,$properties)
	{
		global $page;
		if ($page->cat_id == CURRENT_SITE_ID || !$page->cat_id)
		{
			return '';
		}

		$catlinks = $GLOBALS['objbo']->getCatLinks((int)$page->cat_id,False,True);
		$pagelinks = $GLOBALS['objbo']->getPageLinks($page->cat_id,False,True);
		$category = $GLOBALS['objbo']->getcatwrapper($page->cat_id);
		$this->block->title = $category->name;
		$parent = $category->parent;
		unset($category);

		$content = '';
		if ($parent && $parent != CURRENT_SITE_ID && !$arguments['suppress_parent'])	// do we have a parent?
		{
			$parentcat = $GLOBALS['objbo']->getcatwrapper($parent);
			$content .= "\n<b>".lang('Parent Section:').'</b><ul style="padding-left: 2em;"><li><a href="'.
				sitemgr_link2('/index.php','category_id='.$parent).'" title="'.$parentcat->description.'">'.$parentcat->name.
				'</a></li></ul><br>';
			unset($parentcat);
		}
		if (count($catlinks))
		{
			$content .= "\n<b>".lang('Subsections:').'</b><br><ul style="padding-left: 2em;">';
			foreach ($catlinks as $catlink)
			{
				$content .= "\n".'<li>'.$catlink['link'].'</li>';
			}
			$content .= '</ul><br>';
		}
		if (count($pagelinks)>1 || (count($pagelinks)>0 && $content))
		{
			$content .= "\n<b>".lang('Pages:').'</b>';
			if (!$arguments['suppress_show_all'])
			{
				$content .= ' (<a href="'.sitemgr_link2('/index.php','category_id='.$page->cat_id).
					'"><i>'.lang('show all').'</i></a>)';
			}
			$content .= '<ul style="padding-left: 2em;">'."\n";
			foreach($pagelinks as $pagelink_id => $pagelink)
			{
				if ($page->id && $page->id == $pagelink_id)
				{
					if (!$arguments['suppress_current_page'])
					{
						$content .= '<li><b>&gt;'.$pagelink['link'].'&lt;</b></li>';
					}
				}
				else
				{
					$content .= '<li>'.$pagelink['link']."</li>\n";
				}
			}
			$content .= "</ul>\n";
		}
		return $content;
	}
}
