<?php
	class filetypes
	{
	
			// the type_id_??? vars are strings used by the filemanager plugin's different classes to communicate the filetype between them
			// for each filetype we add support for we must create an entry here, and refer to it in the images.php file (???selected javascript functions, line 633), and class.filemanager file (javascript on_save, line 536)
		var $type_id_image = 'image';
		var $type_id_other = 'other';
		
			//returns an array of all allowed extensions the filemanager may return
			//the filetype can be set in the filemanager configuration dialog
			//any new Filetype options must get a matching entry here.
		function get_extensions($filetype)
		{
			$extensions = array();
			switch($filetype)
			{
			case 'all':
			default:
				$extensions['*'] = 1;
				break;
			case 'image':
				$extensions['png'] = 1;
				$extensions['jpg']  = 1;
				$extensions['jpeg'] = 1;
				$extensions['gif'] = 1;
				break;
			}
			return $extensions;
		}
		
		function GD_type($type) //returns a filetype string that is compatible with the GD lib.
		{
		switch($type)
			{
			case 'jpg':
				return 'jpeg';
				break;
			default:
				return $type;
				break;
			}
		}
	}
?>