<?

if ($userId != 1) exit();

$filename = $_FILES['file']['tmp_name'];

if (isset($filename))
{
	$resourceName = addslashes($_FILES['file']['name']);
	
	$splitFilename = split('\.',$resourceName);
	if (sizeof($splitFilename) == 3 && strlen($splitFilename[1]) == 2)
	{
		echo "language translation detected ($splitFilename[1])";
		$languageFile = true;
		$languageCode = $splitFilename[1];
		$resourceName = $splitFilename[0] . "." . $splitFilename[2];
	}
	
	echo "resource name: $resourceName<br/>";
	
	$resourceId = $conn->queryOne("SELECT resource_id FROM resources WHERE name = '$resourceName'");
	if ($resourceId == 0)
	{
		$conn->exec("INSERT INTO resources (name) VALUES ('$resourceName')");
		$resourceId = $conn->insert_id;
	}
	
	echo "resource id: $resourceId<br/>";
	
	$fh = fopen($filename, 'r');

	while ($line = fgets($fh))
	{
			$split = split('=',$line,2);
			
			$varName = trim(addslashes($split[0]));
			$varComment = trim(addslashes($split[1]));
			echo "$varName >> $varComment<br/>";
			$row = $conn->queryRow("SELECT * FROM variables WHERE name = '$varName' AND resource_id = $resourceId");
			print_r($row);
			if ($languageFile)
			{
				if (sizeof($row) == 0)
					continue;
					
				echo "ok<br/>";
				
				$translationId = $conn->queryOne("SELECT * FROM translations WHERE variable_id = $row[variable_id] AND language_code = '$languageCode' AND user_id = 0");
				print_r($translationId);
				if ($translationId == 0)
					$conn->exec("INSERT INTO translations (variable_id, user_id, language_code, text) VALUES ($row[variable_id], 0, '$languageCode', '$varComment')");
				else
					$conn->exec("UPDATE translations SET text = '$varComment' WHERE variable_id = $row[variable_id] AND language_code = '$languageCode' AND user_id = 0");
				
				
			}
			else
			{
				
				if (sizeof($row) > 0)
				{
					if ($row[comment] != $varComment)
						$conn->exec("UPDATE	variables SET comment = '$varComment' WHERE variable_id = $row[variable_id]");
				}
				else
				{
					$conn->exec("INSERT INTO variables (resource_id, name, comment) VALUES ($resourceId, '$varName','$varComment')");
				}
			}
			
	}
	
}

?>

<?include('header.php')?>
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
<?include('footer.php')?>