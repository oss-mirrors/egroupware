<?php

	class module_lang_block extends Module
	{
		function module_lang_block()
		{
			$this->arguments = array();
			$this->properties = array();
			$this->title = "Choose language";
			$this->description = "This module lets users choose language";
		}
	
		function get_content(&$arguments,$properties)
		{
			if ($GLOBALS['sitemgr_info']['sitelanguages'])
			{
				$content = '<form name="langselect" action="'.$_SERVER['REQUEST_URI'].'" method="post">';
				$content .= '<select onChange="this.form.submit()" name="language">';
				foreach ($GLOBALS['sitemgr_info']['sitelanguages'] as $lang)
				{
					$selected='';
					if ($lang == $GLOBALS['sitemgr_info']['userlang'])
					{
						$selected = 'selected="selected" ';
					}
					$content .= '<option ' . $selected . 'value="' . $lang . '">'. $GLOBALS['Common_BO']->getlangname($lang) . '</option>';
				}
				$content .= '</select>';
				$content .= '</form>';

				return $content;
			}
			else
			{
				$content = lang('No sitelanguages configured');
			}
		}
	}