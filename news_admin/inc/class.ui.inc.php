<?php
	/**************************************************************************\
	* phpGroupWare - News                                                      *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	* --------------------------------------------                             *
	* This program was sponsered by Golden Glair productions                   *
	* http://www.goldenglair.com                                               *
	\**************************************************************************/

	/* $Id$ */

	class ui
	{
		var $start = 0;
		var $query = '';
		var $sort  = '';
		var $order = ''; 
		var $cat_id;
		var $template;
		var $bo;
		var $news_data;
		var $news_id;
		var $sbox;
		var $public_functions = array(
			'write_news' 	=> True,
			'add'       	=> True,
			'edit'      	=> True,
			'delete'    	=> True,
			'delete_item'	=> True,
			'read_news'      => True,
			'show_news_home' => True
		);

		function ui()
		{
			$this->nextmatchs = createobject('phpgwapi.nextmatchs');
			$this->template = $GLOBALS['phpgw']->template;
			$this->bo   = CreateObject('news_admin.bo',True);
			$this->sbox = createObject('phpgwapi.sbox');
			$this->start = $this->bo->start;
			$this->query = $this->bo->query;
			$this->order = $this->bo->order;
			$this->sort = $this->bo->sort;
			$this->cat_id = $this->bo->cat_id;
		}

		//with $default, we are called from the news form
		function selectlist($type,$default=false)
		{
			$link_data['menuaction'] = ($type == 'read') ? 'news_admin.ui.read_news' : 'news_admin.ui.write_news';
			$link_data['start'] = 0;
			$right = ($type == 'read') ? PHPGW_ACL_READ : PHPGW_ACL_ADD;
			reset($this->bo->cats);
			$selectlist = ($default === false) ? ('<option>' . lang($type . ' news') . '</option>') : '';
			while(list(,$cat) = @each($this->bo->cats))
			{
				if($this->bo->acl->is_permitted($cat['id'],$right))
				{
					$cat_id = (int) $cat['id'];
					$link_data['cat_id'] = $cat_id;
					$selectlist .= '<option value="';
					$selectlist .= $default ? $cat_id : $GLOBALS['phpgw']->link('/index.php',$link_data);
					$selectlist .= '"';
					$selectlist .= ($default === $cat_id) ? ' selected="selected"' : ''; 
					$selectlist .= '>' . $cat['name'] . '</option>' . "\n";
				}
			}
			return $selectlist;
		}

		function read_news()
		{
			$limit = 5;

			$news_id = get_var('news_id',Array('GET'));

			$news = $news_id ? array($news_id => $this->bo->get_news($news_id)) :  
				$this->bo->get_newslist($this->cat_id,$this->start,'','',$limit,True);
			$total = $this->bo->total($this->cat_id,True);

			$this->template->set_file(array(
				'main' => 'read.tpl'
			));
			$this->template->set_block('main','news_form');
			$this->template->set_block('main','row');
			$this->template->set_block('main','row_empty');

			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
			$this->template->set_block('main','category');
			$var['lang_read'] = lang('Read');
			$var['lang_write'] = lang('Write');
			$var['readable'] = $this->selectlist('read');
			$var['maintainlink'] = $this->bo->acl->is_permitted($this->cat_id,PHPGW_ACL_ADD) ? 
				('<a href="' . $GLOBALS['phpgw']->link('/index.php','menuaction=news_admin.ui.write_news&start=0&cat_id='.$this->cat_id) .
					'">' . lang('Maintain') . '</a>') :
				'';
			$var['cat_name'] = $this->cat_id ? $this->bo->catbo->id2name($this->cat_id) : lang('Global news');
			$this->template->set_var($var);
			$this->template->parse('_category','category');

			$this->template->set_var('icon',$GLOBALS['phpgw']->common->image('news_admin','news-corner.gif'));

			foreach($news as $newsitem)
			{
				$var = Array(
					'subject'	=> $newsitem['subject'],
					'submitedby'	=> 'Submitted by ' . $GLOBALS['phpgw']->accounts->id2name($newsitem['submittedby']) . ' on ' . $GLOBALS['phpgw']->common->show_date($newsitem['date']),
					'content'	=> nl2br($newsitem['content'])
				);

				$this->template->set_var($var);
				$this->template->parse('rows','row',True);
			}
			if ($this->start)
			{
				$link_data['menuaction'] = 'news_admin.ui.read_news';
				$link_data['start'] = $this->start - $limit;
				$this->template->set_var('lesslink',
					'<a href="' . $GLOBALS['phpgw']->link('/index.php',$link_data) . '">&lt;&lt;&lt;</a>'
				);
			}
			if ($total > $this->start + $limit)
			{
				$link_data['menuaction'] = 'news_admin.ui.read_news';
				$link_data['start'] = $this->start + $limit;
				$this->template->set_var('morelink',
					'<a href="' . $GLOBALS['phpgw']->link('/index.php',$link_data) . '">' . lang('More news') . '</a>'
				);
			}
			if (! $total)
			{
				$this->template->set_var('row_message',lang('No entries found'));
				$this->template->parse('rows','row_empty',True);
			}

			$this->template->pfp('_out','news_form');
		}

		function show_news_home()
		{
			$title = '<font color="#FFFFFF">'.lang('News Admin').'</font>';
			$portalbox = CreateObject('phpgwapi.listbox',array(
				'title'     => $title,
				'primary'   => $GLOBALS['phpgw_info']['theme']['navbar_bg'],
				'secondary' => $GLOBALS['phpgw_info']['theme']['navbar_bg'],
				'tertiary'  => $GLOBALS['phpgw_info']['theme']['navbar_bg'],
				'width'     => '100%',
				'outerborderwidth' => '0',
				'header_background_image' => $GLOBALS['phpgw']->common->image('phpgwapi/templates/default','bg_filler')
			));

			$app_id = $GLOBALS['phpgw']->applications->name2id('news_admin');
			$GLOBALS['portal_order'][] = $app_id;

			$var = Array(
				'up'       => Array('url' => '/set_box.php', 'app' => $app_id),
				'down'     => Array('url' => '/set_box.php', 'app' => $app_id),
				'close'    => Array('url' => '/set_box.php', 'app' => $app_id),
				'question' => Array('url' => '/set_box.php', 'app' => $app_id),
				'edit'     => Array('url' => '/set_box.php', 'app' => $app_id)
			);

			while(list($key,$value) = each($var))
			{
				$portalbox->set_controls($key,$value);
			}

			$total = $this->bo->total(0,True);

			$newslist = $this->bo->get_newslist($cat_id);

			$image_path = $GLOBALS['phpgw']->common->get_image_path('news_admin');

			if(is_array($newslist))
			{
			foreach($newslist as $newsitem)
			{
				$portalbox->data[] = array(
					'text' => $newsitem['subject'] . ' - ' . lang('Submitted by') . ' ' . $GLOBALS['phpgw']->accounts->id2name($newsitem['submittedby']) . ' ' . lang('on') . ' ' . $GLOBALS['phpgw']->common->show_date($newsitem['date']),
					'link' => $GLOBALS['phpgw']->link('/index.php','menuaction=news_admin.ui.show_news&news_id=' . $newsitem['id'])
				);
			}
			}
			else
			{
				$portalbox->data[] = array('text' => lang('no news'));
			}

			$tmp = "\r\n"
				. '<!-- start News Admin -->' . "\r\n"
				. $portalbox->draw()
				. '<!-- end News Admin -->'. "\r\n";
			$this->template->set_var('phpgw_body',$tmp,True);
		}

		//the following function is unmaintained
		function show_news_website($section='mid')
		{
			$cat_id = $_GET['cat_id'];
			$start = $_GET['start'];
			$oldnews = $_GET['oldnews'];
			$news_id = $_GET['news_id'];

			if (! $cat_id)
			{
				$cat_id = 0;
			}

			$this->template->set_file(array(
				'_news' => 'news_' . $section . '.tpl'
			));
			$this->template->set_block('_news','news_form');
			$this->template->set_block('_news','row');
			$this->template->set_block('_news','category');


			if($news_id)
			{
				$news = array($news_id => $this->bo->get_news($news_id));
			}
			else
			{
				$news = $this->bo->get_NewsList($cat_id,$oldnews,$start,$total);
			}


			$total = $this->bo->total($cat_id,True);

			$var = Array();

			$this->template->set_var('icon',$GLOBALS['phpgw']->common->image('news_admin','news-corner.gif'));

			foreach($news as $newsitem)
			{
				$var = Array(
					'subject'=> $newsitem['subject'],
					'submitedby' => 'Submitted by ' . $GLOBALS['phpgw']->accounts->id2name($newsitem['submittedby']) . ' on ' . $GLOBALS['phpgw']->common->show_date($newsitem['date']),
					'content'    => nl2br($newsitem['content'])
				);

				$this->template->set_var($var);
				$this->template->parse('rows','row',True);
			}

			$out = $this->template->fp('out','news_form');

			if ($total > 5 && ! $oldnews)
			{
				$link_values = array(
					'menuaction'    => 'news_admin.ui.show_news',
					'oldnews'       => 'True',
					'cat_id'        => $cat_id,
					'category_list' => 'True'
				);

				$out .= '<center><a href="' . $GLOBALS['phpgw']->link('/index.php',$link_values) . '">View news archives</a></center>';
			}
			return $out;
		}

		function add()
		{
			if($_POST['cancel'])
			{
				Header('Location: ' . $GLOBALS['phpgw']->link('/index.php','menuaction=news_admin.ui.write_news'));
				return;
			}
			if($_POST['submitit'])
			{
				$this->news_data = $_POST['news'];
				
				if (! $this->news_data['subject'])
				{
					$errors[] = lang('The subject is missing');
				}
				if (! $this->news_data['content'])
				{
					$errors[] = lang('The news content is missing');
				}
				if (!is_array($errors))
				{
					$this->news_id = $this->bo->add($this->news_data);
					$this->message = lang('Message has been added');
					//after having added, we must switch to edit mode instead of stay in add
					$this->modify('edit');
					return;
				}
				else
				{
					$this->message = $errors;
				}
			}
			else
			{
				$this->news_data = array('date_d' => date('j'),
										 'date_m' => date('n'),
										 'date_y' => date('Y'),
										 'category' => $this->cat_id,
									);
			}
			$this->modify('add');
		}

		function delete()
		{
			$news_id = $_POST['news_id'] ? $_POST['news_id'] : $_GET['news_id'];

			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
			$this->template->set_file(array(
				'form' => 'admin_delete.tpl'
			));
			$this->template->set_var('lang_message',lang('Are you sure you want to delete this entry ?'));
			$this->template->set_var('lang_yes',lang('Yes'));
			$this->template->set_var('lang_no',lang('No'));

			$this->template->set_var('link_yes',$GLOBALS['phpgw']->link('/index.php','menuaction=news_admin.ui.delete_item&news_id=' . $news_id));
			$this->template->set_var('link_no',$GLOBALS['phpgw']->link('/index.php','menuaction=news_admin.ui.write_news'));

			$this->template->pfp('_out','form');
		}

		function delete_item()
		{
			$item = intval(get_var('news_id'));
			if($item)
			{
				$this->bo->delete($item);
				$msg = lang('Item has been deleted');
			}
			else
			{
				$msg = lang('Item not found');
			}
			$this->write_news($msg);
		}

		function edit()
		{
			$this->news_data	= $_POST['news'];
			$this->news_id		= (isset($_GET['news_id']) ? $_GET['news_id'] 
										: $_POST['news']['id']);

			if($_POST['cancel'])
			{
				Header('Location: ' . $GLOBALS['phpgw']->link('/index.php','menuaction=news_admin.ui.write_news'));
				return;
			}
			if(is_array($this->news_data))
			{
				if(! $this->news_data['subject'])
				{
					$errors[] = lang('The subject is missing');
				}
				if(! $this->news_data['content'])
				{
					$errors[] = lang('The news content is missing');
				}

				if(!is_array($errors))
				{
					$this->bo->edit($this->news_data);
					$this->message = lang('News item has been updated');
				}
				else
				{
					$this->message = $errors;
				}
			}
			else
			{
				$this->news_data = $this->bo->get_news($this->news_id,True);
				$this->news_data['date_d'] = date('j',$this->news_data['date']);
				$this->news_data['date_m'] = date('n',$this->news_data['date']);
				$this->news_data['date_y'] = date('Y',$this->news_data['date']);
			}
			$this->modify();
		}

		function modify($type = 'edit')
		{
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->template->set_file(array(
				'form' => 'admin_form.tpl'
			));

			if (is_array($this->message))
			{
				$this->template->set_var('errors',$GLOBALS['phpgw']->common->error_list($this->message));
			}
			elseif($this->message)
			{
				$this->template->set_var('errors',$this->message);
			}

			$this->template->set_var('lang_header',lang($type . ' news item'));
			$this->template->set_var('form_action',$GLOBALS['phpgw']->link('/index.php',
				array('menuaction'	=> 'news_admin.ui.'.$type,
				 		'news_id'	=> $this->news_id
					)
				)
			);
			$this->template->set_var('form_button','<input type="submit" name="submitit" value="' . lang('save') . '">');
			$this->template->set_var('value_id',$this->news_id);
			$this->template->set_var('done_button','<input type="submit" name="cancel" value="' . lang('Done') . '">');

			$this->template->set_var('label_subject',lang('subject') . ':');
			$this->template->set_var('value_subject','<input name="news[subject]" size="60" value="' . $this->news_data['subject'] . '">');

			$this->template->set_var('label_teaser',lang('teaser') . ':');
			$this->template->set_var('value_teaser','<input name="news[teaser]" size="60" value="' . stripslashes($this->news_data['teaser']) . '" maxLength=100>');

			$this->template->set_var('label_content',lang('Content') . ':');
			$this->template->set_var('value_content','<textarea cols="60" rows="6" name="news[content]" wrap="virtual">' . stripslashes($this->news_data['content']) . '</textarea>');

			$this->template->set_var('label_category',lang('Category') . ':');
			$this->template->set_var('value_category','<select name="news[category]">' . $this->selectlist('write', (int)$this->news_data['category']) . '</select>');

			$this->template->set_var('label_status',lang('Status') . ':');
			$this->template->set_var('value_status','<select name="news[status]"><option value="Active"'
				. (($this->news_data['status'] == 'Active') ? ' selected="selected"' : '')
				. '>' . lang('Active')
				. '</option><option value="Disabled"'
				. (($this->news_data['status'] == 'Disabled') ? ' selected="selected"' : '')
				. '>' . lang('Disabled') . '</option></select>');

			$this->template->set_var('label_date', $GLOBALS['phpgw']->lang('Publish Date') . ':');
			$this->template->set_var('value_date_d', $this->sbox->getDays('news[date_d]', $this->news_data['date_d']) );
			$this->template->set_var('value_date_m', $this->sbox->getMonthText('news[date_m]', $this->news_data['date_m']) );
			$this->template->set_var('value_date_y', $this->sbox->getYears('news[date_y]', $this->news_data['date_y']) );
			
			$this->template->pfp('out','form');
		}
		
		function write_news($message = '')
		{
			$this->template->set_file(array(
				'main' => 'write.tpl'
			));
			$this->template->set_block('main','list');
			$this->template->set_block('main','row');
			$this->template->set_block('main','row_empty');

			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
			$this->template->set_block('main','category');
			$var['lang_read'] = lang('Read');
			$var['lang_write'] = lang('Write');
			$var['readable'] = $this->selectlist('read');
			$var['cat_name'] = $this->cat_id ? $this->bo->catbo->id2name($this->cat_id) : lang('Global news');

			$this->template->set_var($var);
			$this->template->parse('_category','category');

			if ($message)
			{
				$this->template->set_var('message',$message);
			}

			$this->template->set_var('header_date',$this->nextmatchs->show_sort_order($this->sort,'news_date',$this->order,'/index.php',lang('Date'),'&menuaction=news_admin.ui.write_news'));
			$this->template->set_var('header_subject',$this->nextmatchs->show_sort_order($this->sort,'news_subject',$this->order,'/index.php',lang('Subject'),'&menuaction=news_admin.ui.write_news'));
			$this->template->set_var('header_status',$this->nextmatchs->show_sort_order($this->sort,'news_status',$this->order,'/index.php',lang('Status'),'&menuaction=news_admin.ui.write_news'));
			$this->template->set_var('header_edit','edit');
			$this->template->set_var('header_delete','delete');
			$this->template->set_var('header_view','view');

			$total      = $this->bo->total($this->cat_id);
			$items      = $this->bo->get_newslist($this->cat_id,$this->start,$this->order,$this->sort);

			$left  = $this->nextmatchs->left('/index.php',$this->start,$total,'menuaction=news_admin.ui.write_news');
			$right = $this->nextmatchs->right('/index.php',$this->start,$total,'menuaction=news_admin.ui.write_news');
			
			$this->template->set_var(array(
				'left' => $left,
				'right' => $right,
				'lang_showing' => $this->nextmatchs->show_hits($total,$this->start),
			));

			while ((list(,$item) = @each($items)))
			{
				$this->nextmatchs->template_alternate_row_color(&$this->template);
				$this->template->set_var('row_date',$GLOBALS['phpgw']->common->show_date($item['date']));
				if (strlen($item['news_subject']) > 40)
				{
					$subject = $GLOBALS['phpgw']->strip_html(substr($item['subject'],40,strlen($item['subject'])));
				}
				else
				{
					$subject = $GLOBALS['phpgw']->strip_html($item['subject']);
				}
				$this->template->set_var('row_subject',$subject);
				$this->template->set_var('row_status',lang($item['status']));

				$this->template->set_var('row_view','<a href="' . $GLOBALS['phpgw']->link('/index.php','menuaction=news_admin.ui.read_news&news_id=' . $item['id']) . '">' . lang('view') . '</a>');
				$this->template->set_var('row_edit','<a href="' . $GLOBALS['phpgw']->link('/index.php','menuaction=news_admin.ui.edit&news_id=' . $item['id']) . '">' . lang('edit') . '</a>');
				$this->template->set_var('row_delete','<a href="' . $GLOBALS['phpgw']->link('/index.php','menuaction=news_admin.ui.delete&news_id=' . $item['id']) . '">' . lang('Delete') . '</a>');

				$this->template->parse('rows','row',True);
			}

			if (! $total)
			{
				$this->nextmatchs->template_alternate_row_color(&$this->template);
				$this->template->set_var('row_message',lang('No entries found'));
				$this->template->parse('rows','row_empty',True);
			}

			$this->template->set_var('link_add',$GLOBALS['phpgw']->link('/index.php','menuaction=news_admin.ui.add'));
			$this->template->set_var('lang_add',lang('Add new news'));

			$this->template->pfp('out','list');
		}
	}
?>
