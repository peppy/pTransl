<?
if (isset($_REQUEST[username]))
{
	$username = sqlstr($_REQUEST[username]);
	$password = sqlstr($_REQUEST[password]);

	$hash = md5($password);
	$userId = $conn->queryOne("SELECT user_id FROM users WHERE username = '$username' AND password = '$hash'");
	
	if ($userId > 0)
	{
		$_SESSION[userId] = $userId;
		header("location: /p/resource");
	}
}
?>

<?include("pages/header.php")?>

<div class='loginForm'>
	<form method='post'>
		<table>
			<tr>
				<td class='left'>Username:</td>
				<td class='right'><input type='text' name='username' value='<?=$username?>'/></td>
			</tr>
			<tr>
				<td class='left'>Password:</td>
				<td class='right'><input type='password' name='password' value='<?=$password?>'/></td>
			</tr>
			<tr>
				<td class='left'></td>
				<td class='right'><input type='submit' value='Login &raquo;'/></td>
			</tr>
			<tr>
				<td class='left'></td>
				<td class='right'>
					<a href='/p/register'>Register new account...</a>
				</td>
			</tr>
		</table>
	</form>
</div>

<?include("pages/footer.php")?>