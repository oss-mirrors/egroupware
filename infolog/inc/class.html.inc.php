<?php
	/**************************************************************************\
	* phpGroupWare - InfoLog                                                   *
	* http://www.phpgroupware.org                                              *
	* Written by Ralf Becker <RalfBecker@outdoor-training.de>                  *
	* originaly based on todo written by Joseph Engo <jengo@phpgroupware.org>  *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

class html
{
	function input_hidden($vars,$value='')
	{
		if (!is_array($vars))
		{
			$vars = array( $vars => $value );
		}
		while (list($name,$value) = each($vars))
		{
			if ($value != '')               // dont need to send all the empty vars
			{
				$html .= "<input type=hidden name=\"$name\" value=\"$value\">\n";
			}
		}
		return $html;
	}

	function submit_button($name,$lang)
	{
		return "<input type=\"submit\" name=\"$name\" value=\"".lang($lang)."\">\n";
	}

	function link($url,$vars='')
	{
		global $phpgw;
		if (is_array( $vars ))
		{
			$v = array( );
			while(list($name,$value) = each($vars))
			{
				if ($value != '')            // dont need to send all the empty vars
				{
					$v[] = "$name=$value";
				}
			}
			$vars = implode('&',$v);
		}
		return $phpgw->link($url,$vars);
	}

	function checkbox($name,$value='')
	{
		return "<input type=\"checkbox\" name=\"$name\" value=\"True\"" .($value ? ' checked' : '') . ">\n";
	}

	function form($content,$hidden_vars,$url,$url_vars='',$method='POST')
	{
		$html = "<form method=\"$method\" action=\"".$this->link($url,$url_vars)."\">\n";
		$html .= $this->input_hidden($hidden_vars);

		if ($content) {
			$html .= $content;
			$html .= "</form>\n";
		}
		return $html;
	}

	function form_1button($name,$lang,$hidden_vars,$url,$url_vars='',$method='POST')
	{
		return $this->form($this->submit_button($name,$lang),
								 $hidden_vars,$url,$url_vars,$method);
	}

	/*
	 * Example: $rows = array ( '1'	=> array( 1 => 'cell1', '.1' => 'colspan=3',
	 * 														 2 => 'cell2',
	 *														 3 => '3,, '.3' => 'width="10%"' ),
 	 *									 '.1'	=> 'bgcolor="#0000FF"' );
	 * table($rows,'width="100%"');
	 */
	function table($rows,$params = '')
	{
		$html = "<table $params>\n";

		while (list($key,$row) = each($rows)) {
			if (!is_array($row))
				continue;					// parameter
			$html .= "\t<tr ".$rows['.'.$key].">\n";
			while (list($key,$cell) = each($row)) {
				if ($key[0] == '.')
					continue;				// parameter
				$html .= "\t\t<td ".$row['.'.$key].">$cell</td>\n";
			}
			$html .= "\t</tr>\n";
		}
		$html .= "</table>\n";
		
		return $html;
	}
	
	function sbox_submit( $sbox,$no_script=0 )
	{
		$html = str_replace('<select','<select onChange="this.form.submit()" ',
								  $sbox);
		if ($no_script) {
			$html .= '<noscript>'.$this->submit_button('send','>').'</noscript>';
		}
		return $html;
	}

	function image( $app,$name,$alt='',$opts='' )
	{
		global $phpgw;

		$html = '<img src="'.$phpgw->common->image($app,$name).'"';
		if ($alt) $html .= ' alt="'.$alt.'"';
		if ($opts) $html .= " $opts";
		return $html . '>';
	}
	function a_href( $content,$url,$vars='') {
		return '<a href="'.$this->link($url,$vars).'">'.$content.'</a>';
	}
}
