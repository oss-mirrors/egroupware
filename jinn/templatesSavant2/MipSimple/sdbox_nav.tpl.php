
<form method="POST" action="<?=$this->action?>">
   <div style="color:white;padding:5px;margin-bottom:5px;">
	  <?php if($this->site_name):?>
	  <?php echo lang('Site');?>
	  <?php else:?>
	  <?php echo lang('Please select a site')?>
	  <?php endif?>
	  <br/>
	  <select name="site_id" onChange="this.form.submit()">
		 <?php echo $this->sites_options?>
	  </select>
   </div>
   <div style="border-top:solid 1px #ffffff;"></div>
   <?php if($this->objects_arr):?>
   <div style="margin-top:5px;padding:2px;">
	  <img src="<?=$GLOBALS[phpgw]->common->image('jinn','database_small')?>" alt="" style="margin-right:2px;"><a style="color:white;" href="<?=$GLOBALS[egw]->link('/index.php','menuaction=jinn.uiuser.index&site_object_id=-1')?>"><?php echo $this->site_name?></a>
	  <ul style="padding:0px;margin:0;list-style-type: none;">
	<?php foreach($this->objects_arr as $obj):?>
	<li style="margin:2px;vertical-align:middle;padding:0px 2px 0px 7px;margin:0;width:162px;overflow:hidden;white-space:nowrap;"><img src="<?=$GLOBALS[phpgw]->common->image('jinn','mini_navbar')?>" alt="" style="margin-right:2px;"><a style="color:white;" href="<?=$GLOBALS[egw]->link('/index.php','menuaction=jinn.uiuser.index&site_object_id='.$obj[value])?>"><?php echo $obj[name]?></a><?php //echo _debug_array($obj,false)?></li>	
	<?php endforeach?>
 </ul>
 <?php
	/*
	  
	  <?php echo lang('select_object');?><br/>
	  <select name="site_object_id" onChange="this.form.submit()">
		 <?php #echo $this->object_options?>
	  </select>
   </div>
   */
   ?>
   <?php endif?>
</form>
