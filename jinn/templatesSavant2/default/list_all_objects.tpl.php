<?php foreach($this->object_arr as $object):?>
<div style="float:left;border:solid 1px #aaaaaa; margin:5px;width:220px;height:120px;">
   <div style="background-color:#eeeeee; padding:3px; height:35px;overflow:hidden;font-size:120%;font-weight:bold"><?php echo $object[name]?></div>
<div style="padding:3px;">
<a href="<?php echo $object[link_list]?>"><?php echo lang('record list')?></a>
<br/>
<a href="<?php echo $object[link_new]?>"><?php echo lang('new record')?></a>
</div>
<div style="padding:3px; height:35px;overflow:hidden;font-weight:normal;font-size:100%;"><?php echo $object[help_information]?></div>
</div>
<?php endforeach ?>
