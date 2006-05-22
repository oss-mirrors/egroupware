<?php foreach($this->site_arr as $site):?>
<div style="float:left;border:solid 1px #aaaaaa; margin:5px;width:200px">
   <div style="background-color:#eeeeee; height:35px;overflow:hidden;padding:3px; font-size:120%;font-weight:bold"><a href="<?php echo $site[link]?>"><?php echo $site[name]?></a></div>
   <div style="padding:3px;">
   <?php if(is_array($site[object_arr]) && count($site[object_arr])>0):?>
   <?=lang('%1 objects available',count($site[object_arr]))?>
   <?php /*?>
   <!--   <table cellspacing="0" cellpadding="0">
	  <?php foreach($site[object_arr] as $object):?>

	  <tr>
		 <td style="padding:1px"><?php echo $object[name]?></td>
		 <td style="padding:1px"><a href="<?php echo $object[link_list]?>"><img src="<?=$this->icon_browse?>" alt="<?php echo lang('record list')?>" /></a></td>
		 <td style="padding:1px"><a href="<?php echo $object[link_new]?>"><img src="<?=$this->icon_new?>" alt="<?php echo lang('new record')?>" /></a></td>
	  </tr>
	  <?php endforeach ?>
   </table>
   -->
   <?php */?>
   <?php else:?>
   <?=lang('No objects available')?>
	<?php endif?>
	</div>

</div>
<?php endforeach ?>
