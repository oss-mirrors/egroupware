<link href="{css_dir}/style.css" rel="stylesheet" type="text/css">
<link href="{css_dir}/htmlarea.css" rel="stylesheet" type="text/css">
<link href="{css_dir}/calendar.css" type="text/css" rel="stylesheet">


<!-- BEGIN search_results -->
    <h1>{lang_search_results}:</h1><input name="task" type="hidden">

<blockquote>
	<!-- BEGIN file -->
	<p>{filename}</p>
	<!-- END file -->
</blockquote>
<!-- END search_results -->

    <h1>{lang_search}:</h1><input name="task" type="hidden">


<form action="{form_action}" method="post">
<blockquote>
	<p>{lang_search_description}</p>
	<p><input name="keyword" type="text"></p>
</blockquote>
<div align="center"><input type="submit" value="{lang_submit}"> <input type="button" onclick="location='{location_cancel}'" value="{lang_cancel}"></div>
</form>
