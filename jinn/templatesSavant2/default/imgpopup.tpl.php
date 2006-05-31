<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
   <head>
	  <title><?=lang('Image Popup');?></title>
	  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
   </head>
   <link href="phpgwapi/templates/idots/css/idots.css" type="text/css" rel="StyleSheet" />
   <style>
	  body
	  {
			margin:5px;
	  }

	  img
	  {
			border:solid 1px #000000;
	  }

   </style>
   <body>
	  <div align="center">
		 <img  src="<?=$this->img ?>" alt="" <?=$this->attributes ?>  />	
		 <br/>
		 <a href="javascript:self.close()"><?=lang('close this window') ?></a>
	  </div>
   </body>
</html>
