<?php
  echo "<p>\n";                                                                                                                                                                        
  $imgfile = $phpgw->common->get_image_dir("forum")."/" . $appname .".gif";                                                                                                            
  if (file_exists($imgfile)) {                                                                                                                                                         
    $imgpath = $phpgw->common->get_image_path("forum")."/" . $appname .".gif";                                                                                                         
  } else {                                                                                                                                                                             
    $imgfile = $phpgw->common->get_image_dir("forum")."/navbar.gif";                                                                                                                   
    if (file_exists($imgfile)) {                                                                                                                                                       
      $imgpath = $phpgw->common->get_image_path("forum")."/navbar.gif";                                                                                                                
    } else {                                                                                                                                                                           
      $imgpath = "";                                                                                                                                                                   
    }                                                                                                                                                                                  
  }
  section_start("Forum",$imgpath);
  echo '<a href="' . $phpgw->link('/forum/admin/index.php') . '">';
  echo lang('Change Forum settings') . '</a>';
  section_end();
?>
