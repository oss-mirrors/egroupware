<?php
// $Id$

require('parse/html.php');
require(TemplateDir . '/edit.php');

// Edit a page (possibly an archive version).
function action_edit()
{
  global $page, $pagestore, $ParseEngine, $version, $ErrorPageLocked;

  $pg = $pagestore->page($page);
  $pg->read();

  if(!$pg->mutable)
    { die($ErrorPageLocked); }

  $archive = 0;
  if($version != '')
  {
    $pg->version = $version;
    $pg->read();
    $archive = 1;
  }

  template_edit(array('page'      => $page,
                      'text'      => $pg->text,
                      'timestamp' => $pg->time,
                      'nextver'   => $pg->version + 1,
                      'archive'   => $archive));
}
?>
