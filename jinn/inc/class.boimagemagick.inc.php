<?
/*
JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for phpGroupWare
Copyright (C)2002, 2003 Pim Snel <pim@lingewoud.nl>

phpGroupWare - http://www.phpgroupware.org

This file is part of JiNN

JiNN is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free
Software Foundation; either version 2 of the License, or (at your 
option) any later version.

JiNN is distributed in the hope that it will be useful,but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or 
FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
for more details.

You should have received a copy of the GNU General Public License 
along with JiNN; if not, write to the Free Software Foundation, Inc.,
59 Temple Place, Suite 330, Boston, MA 02111-1307  USA
*/



class boimagemagick extends bojinn
{

	function boimagemagick() {

		$this->so = CreateObject('jinn.sojinn');
		$this->get_config();
		var_dump($this->current_config);
	}

	/*
	Resize(int value, int value, string string)
	Resize the image to given size
	possible values:
	arg1 > x-size, unsigned int
	arg2 > y-size, unsigned int
	arg3 > resize method;
	'keep_aspect' > changes only width or height of image
	'fit' > fit image to given size
	*/

	function Resize($x_size, $y_size, $how='keep_aspect') {

		if($this->verbose == TRUE) {
			echo "Resize:\n";
		}

		$method = $how=='keep_aspect'?'>':($how=='fit'?'!':'');

		if($this->verbose == TRUE) {
			echo "  Resize method: {$how}\n";
		}

		$command = "{$this->imagemagickdir}/convert -geometry '{$x_size}x{$y_size}{$method}' '{$this->temp_dir}/tmp{$this->count}_{$this->temp_file}' '{$this->temp_dir}/tmp".++$this->count."_{$this->temp_file}'";

		if($this->verbose == TRUE) {
			echo "  Command: {$command}\n";
		}

		exec($command, $returnarray, $returnvalue);

		if($returnvalue) {
			$this->error .= "ImageMagick: Resize failed\n";
			if($this->verbose == TRUE) {
				echo "Resize failed\n";
			}
		} else {
			$this->file_history[] = $this->temp_dir.'/tmp'.$this->count.'_'.$this->temp_file;
		}
	}





}

?>
