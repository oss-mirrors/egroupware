
<script>
  document.doit.body.focus();
  if (document.doit.subject.value == "") {
     document.doit.subject.focus();
  }
  if (document.doit.to.value == "") {
     document.doit.to.focus();
  }

  self.name="first_Window";
  function addressbook()
  {
     Window1=window.open('{addressbook_link}',"Search","width=800,height=600,toolbar=yes,scrollbars=yes,resizable=yes");
  }

  function attach_window(url)
  {
     awin = window.open(url,"attach","width=500,height=400,toolbar=no,resizable=yes");
  }
</script>

