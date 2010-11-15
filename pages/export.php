<?

if ($userId != 1) exit();

$resource = $conn->queryRow("SELECT * FROM resources WHERE resource_id = 2");

$variables = $conn->queryAll("SELECT variable_id, name FROM variables WHERE resource_id = " . $resource['resource_id']);

$languages = $conn->queryAll("SELECT code FROM languages", false);

header("Content-Type: application/zip");
header('Content-Disposition: attachment; filename=output.zip');

$dir = sys_get_temp_dir();

foreach ($languages as $language)
{
	$code = $language['code'];
	
	$filename = $dir . "/" . str_replace('.txt',".$code.txt",$resource['name']);
	
	$handle = fopen($filename,"w");

	foreach ($variables as $variable)
	{
		$variableName = $variable['name'];
		$translation = $conn->queryOne("SELECT text FROM translations WHERE variable_id = $variable[variable_id] AND language_code = '$code' order by rating desc limit 1");
		fwrite($handle,$variableName . '=' . $translation . "\n");
	}
	
	fclose($handle);
}

exec("zip $dir/strings.zip $dir/Strings*");

readfile("$dir/strings.zip");

//don't output the footer
exit();


?>