{include file="header.tpl" title="login"}
<div>
	Importing can currently be done using plaintext files generated with Resgen.<br/>
	If the filename has <b>no country code</b>, <b>variables</b> will be imported.<br/>
	If the filename <b>has a country code</b> in it, <b>translations</b> will be imported (ie. Strings.fr.txt).</br>
	<br/><br/>
	Duplicates are automatically handled and updated.
</div>
<div>
	<form method='post' enctype="multipart/form-data">
		Select a plaintext file: <input name='file' type='file'>
		<input type='submit' value='Import'>
	</form>
</div>
{include file="footer.tpl"}	