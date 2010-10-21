<?
if (isset($_REQUEST[action]))
{
	$username = sqlstr($_REQUEST[username]);
	$password = sqlstr($_REQUEST[password]);
	$password2 = sqlstr($_REQUEST[password2]);
	$email = sqlstr($_REQUEST[email]);

	$hash = md5($password);
	
	$userExists = $conn->queryOne("SELECT user_id FROM users WHERE username = '$username'");
	$userLengthValid = strlen($username) >= 3;
	
	$passwordLengthValid = strlen($password) >= 6;
	$passwordMatches = $password == $password2;
	
	$emailValid = validEmail($email);
	
	switch ($_REQUEST[action])
	{
		case "checkusername":
			if ($userExists)
				echo "0|The username <b>$username</b> is already taken.  Please choose another.";
			else if (!$userLengthValid)
				echo "0|Your chosen username is too short.";
			else
				echo "1|This username is valid!";
			exit();
		case "checkpassword":
			echo $passwordLengthValid ? "1" : "0";
			echo "|";
			
			if (!$passwordLengthValid)
				echo "Your password must be at least 6 characters long.";
			else
				echo "This password is fine.";
			exit();
		case "checkemail":
			if (!$emailValid)
				echo "0|This is not a valid email address";
			else
				echo "1|Don't worry - I won't spam you";
			exit();
		case "register":
			if (!$userExists && $userLengthValid && $passwordLengthValid && $passwordMatches && $emailValid)
			{
				$conn->exec("INSERT INTO users (username, password, email) VALUES ('$username','$hash','$email')");
				
				$_SESSION[userId] = $conn->insert_id;
				header("location: /p/resource");
			}
			exit();
	}
}
?>

<?include("pages/header.php")?>

<script>

var handles = new Array();

function checkCallback(value, fieldName)
{
	clearTimeout(handles[fieldName]);
	
	var thisHandle = setTimeout(
	function (){
		$.get(window.location + "&action=check"+fieldName+"&"+fieldName+"=" + value,
			function(data) {
				if (thisHandle != handles[fieldName])
					return;

				var split = data.split('|');
	
				var success = split[0] == "1";
				$("#"+fieldName+"-error").attr('class',success ? 'valid' : 'invalid');
	
				$("#"+fieldName+"-error").html(split[1]);
	
				checkSubmission();
			});
	},100);
	
	handles[fieldName] = thisHandle;
}

function checkPassword2(password)
{
	var success = $('#password').attr('value') == $('#password2').attr('value');
	
	if ($('#password2').attr('value').length < 6)
	{
		$("#password2-error").html('');
		return;
	}
		
	$("#password2-error").attr('class',success ? 'valid' : 'invalid');
	$("#password2-error").html(success ? "Good job." : "This doesn't match what you typed above...");
	
	checkSubmission();
}

function checkSubmission()
{
	var valid = 
		$('#password-error').attr('class') == 'valid' && 
		$('#password2-error').attr('class') == 'valid' && 
		$('#username-error').attr('class') == 'valid' && 
		$('#email-error').attr('class') == 'valid' && 
		$('password').attr('value') != '';
	$("#submit").attr('disabled',valid?'':'disabled');
}

</script>

<div class='registrationForm'>
	<form method='post' action='/p/register&action=register'>
		<table>
			<tr>
				<td class='left'>Desired Username:</td>
			</tr>
			<tr>
				<td class='right'><input type='text' onkeyup='checkCallback(this.value,this.name)' name='username' value='<?=$username?>'/></td><td><span id='username-error'></span></td>
			</tr>
			<tr>
				<td class='left'>Email:</td>
			</tr>
			<tr>
				<td class='right'><input type='email' onkeyup='checkCallback(this.value,this.name)' id='email' name='email' value='<?=$email?>'/></td><td><span id='email-error'></span></td>
			</tr>
			<tr>
				<td class='left'>Password:</td>
			</tr>
			<tr>
				<td class='right'><input type='password' onkeyup='checkCallback(this.value,this.name)' id='password' name='password' value='<?=$password?>'/></td><td><span id='password-error'></span></td>
			</tr>
			<tr>
				<td class='left'>Confirm Password:</td>
			</tr>
			<tr>
				<td class='right'><input type='password' onkeyup='checkPassword2(this.value)' id='password2' name='password2'/></td><td><span id='password2-error'></span></td>
			</tr>
			<tr>
				<td class='right'><input type='submit' id='submit' value='Register &raquo;' disabled/></td>
			</tr>
		</table>
	</form>
</div>

<?include("pages/footer.php")?>