<?php
	/**************************************************************************\
	* AngleMail - E-Mail Module for phpGroupWare - Debugging Functions	*
	* http://www.anglemail.org									*
	* http://www.phpgroupware.org									* 
	*/
	/**************************************************************************\
	* AngleMail - E-Mail Debugging Functions						*
	* This file written by "Angles" Angelo Puglisi <angles@aminvestments.com>	*
	* Copyright (C) 2003 Angelo Tony Puglisi (Angles)					*
	* -------------------------------------------------------------------------		*
	* This library is free software; you can redistribute it and/or modify it		*
	* under the terms of the GNU Lesser General Public License as published by	*
	* the Free Software Foundation; either version 2.1 of the License,			*
	* or any later version.											*
	* This library is distributed in the hope that it will be useful, but			*
	* WITHOUT ANY WARRANTY; without even the implied warranty of	*
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	*
	* See the GNU Lesser General Public License for more details.			*
	* You should have received a copy of the GNU Lesser General Public License	*
	* along with this library; if not, write to the Free Software Foundation,		*
	* Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA			*
	\**************************************************************************/
	
	/* $Id$ */
	
	/*!
	@class svc_debug
	@abstract debugging running code utility functions
	@discussion ?
	@author Angles 
	*/
	
	class svc_debug
	{
		// DEBUG OUTPUT TO **UNDER DEVELOPMENT** where to show any debug information
		// UNDER DEVELOPMENT debug info can be stored in array for later retrieval
		var $debugdata=array();
		
		// available debug output types
		var $available_debug_outputs=array('echo_out','fill_array','fill_array__another_window','FUTURE');
		
		// this is your dedired debug output type
		//var $debugoutput_to='echo_out';
		var $debugoutput_to='fill_array__another_window';
		
		
		/*!
		@function svc_debug
		@abstract CONSTRUCTOR 
		*/
		function svc_debug()
		{
			// do nothing here
		}
		
		/*!
		@function out
		@abstract wraps debugging output to various devices
		@param $str (string) the message to display as the debug output
		@param $dump_obj (mixed) (optional) if dumping object data, make a reference to it here
		@param $output_to (known string) (optional) can be "echo_out" or "FUTURE", default is "echo_out"
		@discussion This will eventually allow output to various places or pass to the phpgw api, right now 
		it is under development. The ref_dump_obj param is use for those debug statements wanting to 
		dump entire object data, it is optional. Determination whether or not to output debug data is not 
		included here, decide that before calling this function.  NOTE that when you pass variable to a function 
		by reference, as we do here, it is only necessary to put the ampersand in the param area, the call to this 
		function requires no ampersand there, as per PHP docs.  Available outputs are "echo_out", 
		or "fill_array", or "fill_array__another_window", or FUTURE. 
		@author Angles
		*/
		function out($str='', $dump_obj='', $output_to='')
		{			
			// normalize some params
			if ((!$output_to)
			|| ( ($output_to) && (in_array($output_to, $this->available_debug_outputs) == False) ) )
			{
				$output_to = $this->debugoutput_to;
			}
			$output_to = $this->debugoutput_to;
			
			if (!$str)
			{
				$str = 'mail_msg_display: out: no debug message provided';
			}
			// output the debug info
			if ($output_to == 'echo_out')
			{
				echo $str;
				if ((isset($dump_obj))
				&& ($dump_obj))
				{
					echo '<pre>';
					print_r($dump_obj);
					echo '</pre>';
				}
			}
			elseif (($output_to == 'fill_array')
			|| ($output_to == 'fill_array__another_window'))
			{
				// do this for simple "fill_array" and for "fill_array__another_window"
				$this->debugdata[] = $str;
				if ((isset($dump_obj))
				&& ($dump_obj))
				{
					//$this->debugdata[] = '<pre>'.serialize($dump_obj).'</pre>';
					$this->debugdata[] = '<br />'.serialize($dump_obj).'<br />';
					//$this->debugdata[] = '<br />'.$this->htmlspecialchars_encode(serialize($dump_obj)).'<br />';
				}
			}
			else
			{
				echo 'mail_msg_display: out: unknown value for param $output_to ['.serialize($output_to).'] <br>';
			}
		}
		
		/*!
		@function notice_pagedone
		@abstract Generic function a UI page can call, then this conditionally outputs debug info if it should. 
		@result empty string if nothing is being debugged, otherwise a string, typically in javascript form, of debug data. 
		@discussion when finished making a page a UI class can call this when it has no awareness if debug 
		data should be output or not, it signifies the event of a page being finished with the UI class then this 
		function only takes action if it thinks it should, Therefor the calling process can call this without caring 
		if anything needs to be done or not, that decision is made in this function. Usuallyonly takes action like a call 
		to "get_debugdata_stack" when "fill_array__another_window" is being used. At this point, any other 
		conditions do not trigger any action be this function. Since empty string is returned if nothing is being 
		debugged, this can set a template var to empty in a template system, supressing output and at least 
		filling a known nvar with an empty value, in cases where no output us desired. 
		@author Angles
		*/
		function notice_pagedone()
		{
			if (($this->debugoutput_to == 'fill_array__another_window')
			&& ($this->debugdata))
			{
				return $this->get_debugdata_stack();
			}
			else
			{
				return '';
			}
		}
		
		/*!
		@function get_debugdata_stack
		@abstract if debug data is being put into an array, this function will return that array and optionally clear it.
		@param $clear_array (boolean) if TRUE the debug array stack is cleared before exit, this is the default. 
		@param $as_string (empty or not empty) if FALSE or empty data is returned as an array, otherwise 
		data this-debugdata[] array is imploded and this function returns a string. Default is return as string. 
		@discussion ?
		@author Angles
		*/
		function get_debugdata_stack($as_string='yes', $clear_array=True)
		{
			if ($this->debugoutput_to == 'fill_array__another_window')
			{
				// this actually returns a 2 element array, 
				// one says that "debugoutput_to" => "fill_array__another_window"
				// so the xslt template can make the js that makes the output window
				$temp_data = '';
				//$temp_data = ' > '.implode("\r\n".'> ', $this->debugdata);
				$loops = count($this->debugdata);
				for ($i = 0; $i < $loops; $i++)
				{
					$this_line = $this->debugdata[$i];
					$this_line = str_replace("<br>", ' __LINEBREAK_BR__ ', $this_line);
					$this_line = str_replace("<br />", ' __LINEBREAK_BR__ ', $this_line);
					$this_line = str_replace("\r\n", ' __LINEBREAK__ ', $this_line);
					$this_line = str_replace("\r", ' __LINEBREAK__ ', $this_line);
					$this_line = str_replace("\n", ' __LINEBREAK__ ', $this_line);
					$this_line = htmlentities($this_line,ENT_QUOTES);
					// some debug has font color tags needs to be restored
					//$this_line = preg_replace('/&lt;font color=.*\/font&gt;/','FONTREPLACEMENT',$this_line);
					//$this_line = preg_replace('/(&lt;font color=&quot;)(.*)(&quot;&gt;)(.*)(&lt;\/font&gt;)/','FONTREPLACEMENT \2 \4 FONTREPLACEMENT',$this_line);
					$this_line = preg_replace('/(&lt;font color=&quot;)(.*)(&quot;&gt;)(.*)(&lt;\/font&gt;)/U','<font color="\2"> \4 </font>',$this_line);
					$this_line = str_replace(' __LINEBREAK_BR__ ', '<br />', $this_line);
					$temp_data .= '<br />+ '.$this_line;
				}
				
				if ($as_string)
				{
					$this->debugdata = $this->js_another_window($temp_data);
					////$this->debugdata = $this->js_another_window('Dude this is an example'."<br />".'2nd line');
				}
				else
				{
					// even if not returning a string, the data array is still reduced to 2 elements because 
					// using JS to show data in another window simply requires the debug msg to be a string
					$this->debugdata = array();
					$this->debugdata['debugoutput_to'] = $this->debugoutput_to;
					$this->debugdata['js_another_window'] = $this->js_another_window($temp_data);
				}
				$temp_data = '';
			}
			elseif ($as_string)
			{
				// returning a string not designed to be put into another window via JS, so it is a simple string, no JS surrounds it
				$this->debugdata = htmlspecialchars(' > '.implode("\r\n".'>', $this->debugdata));
				// some debug has font color tags needs to be restored
				$this->debugdata = preg_replace('/(&lt;font color=&quot;)(.*)(&quot;&gt;)(.*)(&lt;\/font&gt;)/U','<font color="\2"> \4 </font>',$this->debugdata);
			}
			
			if ($clear_array == True)
			{
				if ($as_string)
				{
					$temp_data = '';
				}
				else
				{
					$temp_data = array();
				}
				$temp_data = $this->debugdata;
				$this->debugdata = array();
				return $temp_data;
			}
			else
			{
				return $this->debugdata;
			}
		}
		
		/*!
		@function js_another_window
		@abstract javascript text that surrounds the debug data to be displayed in another window
		@discussion ?
		@author Angles
		*/
		function js_another_window($msg_to_show='test msg')
		{
				// I think indenting screws this up 
$other_window_js = <<<EOD

<script type="text/javascript">
var _console = null;
var _did_output = 0;
function do_debug(msg)
{
	if ((_console == null) || (_console.closed)) {
		_console = window.open("","console","width=750,height=400,resizable");
		//_console.document.open("text/plain");
		_console.document.open("text/html");
	}
	if (_console.document.closed) {
		// this is only called if you use the close method below but the same popup window is reused for next pageview debug data 
		//_console.document.open("text/plain");
		_console.document.open("text/html");
	}
	
	//_console.document.writeln(msg);
	_console.document.write(msg);
	// calling close will end the page and the next page starts a new page
	// or not calling close will add the next page view debug data to the existing text here
	// ALSO calling close requires the open statement check above
	//_console.document.close();
	_did_output = 1;
}
</script>

EOD;
			$other_window_js .= "\r\n"
				.'<script type="text/javascript">'."\r\n"
				.'	if (_did_output == 0) { '."\r\n"
				//."		do_debug('".nl2br(htmlentities($msg_to_show,ENT_QUOTES))."'); \r\n"
				//."		do_debug('".nl2br(htmlspecialchars($msg_to_show,ENT_QUOTES))."'); \r\n"
				//.'		do_debug(\'<html>\n<head>\n<title>Debug Data</title>\n<style>\n BODY { font-family: Arial,Helvetica,san-serif; font-size: 8px; } \n</style>\n</head>\n<body style="font-family: Arial,Helvetica,san-serif; font-size: 8px;">\n'
				.'		do_debug(\'<html>\n<head>\n<title>Debug Data</title>\n<style>\n .out { font-family: Arial,Helvetica,san-serif; font-size: 8px; } \n</style>\n</head>\n<body>\n<div class="out">'
						.$msg_to_show.'\n'
						."<br /><font color=\"darkgreen\"> = * = * = * = * = * random: ".rand()." = * = * = * = * = * </font></div></body></html>'); \r\n"
				.'	}'."\r\n"
				.'</script>'."\r\n";
			
			return $other_window_js;
		}
		
	}
?>
