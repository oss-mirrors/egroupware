<?php
	/**************************************************************************\
	* phpGroupWare - Bookmarks                                                 *
	* http://www.phpgroupware.org                                              *
	* Michael Totschnig                                                        *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

class exportbo
{
	var $t, $c, $type, $expanded;

	function exportbo()
	{
		$this->t = CreateObject('phpgwapi.Template',PHPGW_INCLUDE_ROOT . '/bookmarks/templates/export');
		$this->c = CreateObject('phpgwapi.categories','','bookmarks');
	}

	function categories_list_main()
	{
		$mains = $this->c->return_array('mains',0,False,'','cat_name','',True);

		while (is_array($mains) && $main = each($mains))
		{
			$s .= '<option value="' . $main[1]['id'] . '">' . $main[1]['name'] . '</option>';
		}
		return '<select name="bmcategory[]" size="5" multiple="multiple">' . $s . '</select>';
	}

	//$expanded = array of cat_ids to show expanded, the rest will be folded
	function export($catlist,$type,$expanded=array())
	{
		$this->type = $type;
		$this->expanded = $expanded;
		$this->t->set_file('netscape','export_' . $this->type . '.tpl');
		$this->t->set_block('netscape','catlist','categs');
		foreach  ($catlist as $catid)
		{
			$this->t->set_var('categ',$this->gencat($catid));
			$this->t->fp('categs','catlist',True);
		}
		return $this->t->fp('out','netscape');
	}

	function gencat($catid)
	{
		$db2 = $GLOBALS['phpgw']->db;

		$t = new Template(PHPGW_INCLUDE_ROOT . '/bookmarks/templates/export');
		$t->set_file('categ','export_' . $this->type . '_catlist.tpl');
		$t->set_block('categ','subcatlist','subcats');
		$t->set_block('categ','urllist','urls');
		$subcats =  $this->c->return_array('subs',0,False,'','cat_name','',True,$catid);

		if ($subcats)
		{
			foreach($subcats as $subcat)
			{
				$t->set_var('subcat',$this->gencat($subcat['id']));
				$t->fp('subcats','subcatlist',True);
			}
		}

		$t->set_var(array(
			'catname' => iconv("ISO-8859-1","UTF-8",$this->c->id2name($catid)),
			'catid' => $catid,
			'folded' => (in_array($catid,$this->expanded) ? 'no' : 'yes')
		));

		$sql = "select * from phpgw_bookmarks where (bm_subcategory='" . 
			$catid . 
			"' OR (bm_subcategory='0' AND bm_category='" . 
			$catid . 
			"')) order by bm_name, bm_url";
		$db2->query($sql,__LINE__,__FILE__);

		while ($db2->next_record())
		{
			$t->set_var(array(
				'url' => $db2->f('bm_url'),
				'name' => iconv("ISO-8859-1","UTF-8",$db2->f('bm_name'))
			));
			$t->fp('urls','urllist',True);
		}
		return $t->fp('out','categ');
	}
}