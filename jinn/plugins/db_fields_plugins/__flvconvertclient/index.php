<?php
   $phpgw_flags = Array(
	  'currentapp'	=>	'jinn',
	  'noheader'	=>	True,
	  'nonavbar'	=>	True,
	  'noappheader'	=>	True,
	  'noappfooter'	=>	True,
	  'nofooter'	=>	True
   );

   $GLOBALS['phpgw_info']['flags'] = $phpgw_flags;

   //require('../../../../header.inc.php');

   //$bo = CreateObject('jinn.bouser');
   //_debug_array($bo);

   //   ns.play( _phpURL + "/index.php?exec=flvfeed.getPos&file=" + _vidURL +  
   // "&position=" + positions[i]);]
   //touch( '/tmp/hallo2222');
   if(!$_GET['exec'])
   {
	  die();
   }

   else
   {
 	  function getPos()
	  {
		 $seekat = $_GET["position"];

		 $file =  ereg_replace('http://'.$_SERVER['HTTP_HOST'].'/', '',$_GET["file"]);
		 if(!file_exists($file))
		 {
			substr($file,1);
		 }
		 $ext= strrchr($file, ".");

//		 die($file);
		 $file= '/var/www/'.$file;

		 if((file_exists($file)) && ($ext==".flv"))
		 {
			//echo "hallo:1";
			header("Content-Type: video/x-flv");
			//die();
			if($seekat != 0)
			{
			   print("FLV");
			   print(pack('C', 1 ));
			   print(pack('C', 1 ));
			   print(pack('N', 9 ));
			   print(pack('N', 9 ));
			}

			$fh = fopen($file, "rb");
			fseek($fh, $seekat);

			while (!feof($fh)) 
			{
			   print (fread($fh, filesize($file))); 
			}

			fclose($fh);
		 }
		 else
		 {
			print("ERROR: The file does not exist");
		 }
	  }

	  getPos();

   }
?>

