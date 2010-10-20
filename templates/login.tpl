{include file="header.tpl" title="login"}
<div class='loginForm'>
	<form method='post'>
		<table>
			<tr>
				<td class='left'>Username:</td>
				<td class='right'><input type='text' name='username' value='{$username}'/></td>
			</tr>
			<tr>
				<td class='left'>Password:</td>
				<td class='right'><input type='password' name='password' value='{$password}'/></td>
			</tr>
			<tr>
				<td class='left'></td>
				<td class='right'><input type='submit' value='Login &raquo;'/></td>
			</tr>
			<tr>
				<td class='left'></td>
				<td class='right'>
					<a href='#'>Register new account...</a>
				</td>
			</tr>
		</table>
	</form>
</div>
{include file="footer.tpl" title="login"}