<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="<?=$this->lang?>" xmlns="http://www.w3.org/1999/xhtml">
   <head>
	  <title><?=lang('Walk Records')?></title>

	  <meta http-equiv="content-type" content="text/html; charset=<?=$this->charset?>" />
	  <meta name="keywords" content="eGroupWare" />
	  <meta name="description" content="eGroupware" />
	  <meta name="keywords" content="eGroupWare" />
	  <meta name="copyright" content="eGroupWare http://www.egroupware.org (c) 2005" />
	  <meta name="author" content="eGroupWare http://www.egroupware.org" />
	  <meta name="robots" content="none" />
	  <META HTTP-EQUIV="Refresh" CONTENT="1;URL=<?=$GLOBALS[phpgw]->link('/index.php',"menuaction=jinn.uiuser.do_loop_walk_events&start={$this->number}&where={$this->where}");?>&amount=<?=$this->amount?>">
	  <link rel="icon" href="<?=$this->img_icon?>" type="image/x-ico" />
	  <link rel="shortcut icon" href="<?=$this->img_shortcut?>" />
	  <link href="<?=$this->theme_css?>" type="text/css" rel="StyleSheet" />
   </head>
   <body>
		 <div id="divMain">
			<div id="divAppboxHeader"><?=lang('Walk Records')?></div>
			<div id="divAppbox">
			   <?php if($this->number+$this->items > $this->amount):?>
			   <?=$this->number?> - <?=($this->amount)?> of <?=$this->amount?><br>
			   <?php else:?>
			   <?=$this->number?> - <?=($this->number+$this->items)?> of <?=$this->amount?><br>
			   <?php endif?>
			   <br>
			   <?=lang('DO NOT CLOSE ME');?><br>
			   <br>
			   <br>
			   <font color="red"><?=$this->time_spend?></font>

			</div>
		 </div>
   </body>
</html>
