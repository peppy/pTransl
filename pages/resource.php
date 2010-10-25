<?
$resourceId = $_REQUEST[r];
$resourceId = (int)2;

$languageCode = sqlstr($_REQUEST[l]);
//must check this is a valid language

$languages = $conn->queryAll("SELECT l.*, (select count(distinct translation_id) FROM translations WHERE variable_id in (select variable_id from variables where resource_id = $resourceId) AND language_code = l.code) as complete FROM languages l");

$languageFound = in_array_column($languageCode,'code',$languages);

if (!$languageFound)
	$languageCode = "";
else
{
	switch ($_REQUEST[a])
	{
		case "update":
			$variableId = (int)$_REQUEST[id];
			
			$text = sqlstr(str_replace('\\','\\\\',$_REQUEST[content]));
			
			$existing = $conn->queryAll("SELECT * FROM translations WHERE variable_id = $variableId AND user_id = $userId AND language_code = '$languageCode'");
			
			if (sizeof($existing) == 0)
			{
				$conn->exec("INSERT INTO translations (user_id, variable_id, language_code, text) VALUES ($userId, $variableId, '$languageCode', '$text')");
			}
			else
			{
				$conn->exec("UPDATE translations SET text = '$text', last_update = CURRENT_TIMESTAMP WHERE user_id = $userId AND language_code = '$languageCode' AND variable_id = $variableId");
			}
			
			echo "1";
			
			exit();
		case "vote":
			$weight = (int)$_REQUEST[weight];
			
			if (abs($weight) != 1) exit();
			
			$variableId = (int)$_REQUEST[id];
			$translationId = (int)$_REQUEST[tid];
			
			$conn->autocommit(FALSE);
			
			//Find whether this user has already rated this translation
			$previousVote = $conn->queryOne("SELECT vote FROM votes WHERE translation_id = $translationId AND user_id = $userId");
			
			if (isset($previousVote) && $previousVote == $weight)
			{
				//New vote is the same as old vote -- disregard.
				$conn->rollback();
				echo "0";
				exit();
			}
			
			if ($userId == $conn->queryOne("SELECT user_id FROM translations WHERE translation_id = $translationId"))
			{
				if ($weight > 0)
					$weight = 0; //don't allow a positive vote for your own translation
				else
					$weight = -5; //allow super down-voting for your own entries (basically revoke).
			}
			
			$addWeight = $weight - $previousVote;
			
			$conn->exec("UPDATE translations SET rating = rating + $addWeight WHERE translation_id = $translationId");
			
			if ($weight != 0)
				$conn->exec("INSERT INTO votes VALUES ($translationId, $userId, $weight) ON DUPLICATE KEY UPDATE vote = $weight");
			else
				$conn->exec("DELETE FROM votes WHERE translation_id = $translationId AND user_id = $userId");
			
			$rating = $conn->queryOne("SELECT rating FROM translations WHERE translation_id = $translationId");
			
			if ($rating > 3)
				$conn->exec("UPDATE variables SET accepted_translation_id = $translationId WHERE variable_id = $variableId");
			
			$conn->commit();
			$conn->autocommit(TRUE);
			
			echo "1";
			exit();
		case "redraw":
			$variableId = (int)$_REQUEST[id];
			$variables = loadTranslations($resourceId, $languageCode,"v.variable_id = $variableId");
			echo displayVariable($variableId, $variables[$variableId]);
			exit();
	}
	
	
}

$resource = $conn->queryRow("SELECT * FROM resources WHERE resource_id = $resourceId");

$variables = loadTranslations($resourceId, $languageCode);

function loadTranslations($resourceId, $languageCode, $condition = "")
{
	global $conn;
	global $user;

	if (strlen($condition) > 0) $condition .= " and";
	$variables = $conn->queryAllRekey("SELECT v.* FROM variables v WHERE $condition resource_id = $resourceId order by name");

	$query = "SELECT t.*, u.*, vo.vote as uservote FROM variables v JOIN translations t USING (variable_id) JOIN users u USING (user_id) LEFT JOIN votes vo ON vo.translation_id = t.translation_id AND vo.user_id = '".$user['user_id']."' WHERE $condition t.language_code = '$languageCode' AND v.resource_id = $resourceId ORDER BY t.variable_id, t.last_update";
	$translations = $conn->queryAll($query);
	foreach ($translations as $translation)
		$variables[$translation [variable_id]][translations][] = $translation;

	return $variables;
}

function displayVariable($id, $variable)
{
	global $user;
	
	$output = "";
	$class = "";
	
	if (sizeof($variable[translations]) > 0)
	{
		$hasAccepted = false;
		foreach ($variable[translations] as $translation)
		{
			if ($translation[rating] > 2)
			{
				$hasAccepted = true;
				$variable[accepted_translation_id] = $translation[translation_id];
				break;
			}
		}
		
		foreach ($variable[translations] as $translation)
		{
			if ($translation[rating] < -2)
			{
				$hiddenCount++;
				if ($translation['user_id'] == $user['user_id'])
					$hiddenIsUserOwned = $translation;
				continue;
			}
			
			if ($hasAccepted && $variable[accepted_translation_id] != $translation[translation_id])
				continue;
			
			$output .= "<div class='userString'>@$translation[username] (".nicedate($translation[last_update])."):</div>
						<div id='t$translation[translation_id]' class='text r$translation[rating]'>
							<span>$translation[text]</span>";
			if ($hasAccepted)
			{
				$output .= " <span class='green'>Accepted Translation</span>";
				if (!isset($translation[uservote]) || $translation[uservote] == 1)
					$output .= "<div class='options'><a onclick='return vote($translation[translation_id],$translation[variable_id],false)' >mark as wrong</a></div>";
			}
			else
			{
				$output .= "<div class='options'>";
				if ($translation[user_id] == $user[user_id])
				{
					$output .= "<a onclick='return edit($translation[translation_id],$translation[variable_id],false)' >edit</a> | ";
					$output .= "<a onclick='return revoke($translation[translation_id],$translation[variable_id])' >revoke</a>";
				}
				else
				{
					$output .= "<a onclick='return edit($translation[translation_id],$translation[variable_id],true)' >revise</a> | ";
					if (!isset($translation[uservote]) || $translation[uservote] == -1)
						$output .= "<a onclick='return vote($translation[translation_id],$translation[variable_id],true)' >correct</a> | ";
					else
						$output .= "<b>correct</b> | ";
					if (!isset($translation[uservote]) || $translation[uservote] == 1)
						$output .= "<a onclick='return vote($translation[translation_id],$translation[variable_id],false)' >wrong</a>";
					else
						$output .= "<b>wrong</b>";
				}
				$output .= "</div>";
			}
			$output .= "</div>";
		}
	}
	
	if ($hiddenCount > 0)
	{
		$output .= "<div class='gray'>$hiddenCount translation".($hiddenCount != 1 ? "s were" : " was")." hidden due to low ratings.</div>";
		if ($hiddenIsUserOwned != null)
		{
			$output .= "<div class='gray'>Your translation is currently hidden. You may be able to restore this if you previously revoked it. ";
			$output .= "<a onclick='return vote($hiddenIsUserOwned[translation_id],$hiddenIsUserOwned[variable_id],true)' >Cancel Revoke</a></div>";
		}
	}
	
	if (sizeof($variable[translations]) == 0 || sizeof($variable[translations]) == $hiddenCount)
	{
		$output .= "<textarea class='translationEditBox getFocus' name='$id'>No translation yet.  Click to translate.</textarea>";
	}
	
	if ($hasAccepted)
		$class .= "accepted";

	$output .= "</td>";
	
	//add the opening clause (so we can figure class name in the loop
	$output = "<td><div class='resourceName'>$variable[name]</div><big>$variable[comment]</big></td><td id='v$id' class='$class'>" . $output;
	
	return $output;
}
?>

<?include("pages/header.php")?>

<div class='resourceHeader'>
	<h1><a >&laquo;</a> Resource: <?=$resource[name]?> (<?=sizeof($variables)?>)</h1>
	<div class='languages'>
	<?foreach ($languages as $language):?>
			<?if ($language[code] == $resource[base_language_code]):?>
				<span class='gray'><?=$language[name]?> (base)</span> 
			<?elseif ($languageCode == $language[code]):?>
				<span class='active'><?=$language[name]?> (<?=$language[complete]?>)</span> 
			<?else:?>
				<span><a href='/p/resource?r=<?=$resource[resource_id]?>&l=<?=$language[code]?>'>
					<?=$language[name]?> (<?=$language[complete]?>)
				</a></span> 
			<?endif;?>
	<?endforeach;?>
	</div>
	<div>
		<div>
			<input type='checkbox' id='showAccepted' onchange='updateFilters()'/> <label for='showAccepted'>show accepted translations</label>
		</div>
		<div id='filterInfo'></div>
	</div>
</div>

<?if (!$languageCode):?>
	Please select a language to view translations.
<?else:?>
	<table class='resourceList'>
		<tr>
			<th width="20%">Original Content</th>
			<th>Translation</th>
		</tr>
		<?
		foreach($variables as $i => $variable):?>
			<tr class='translationRow <?=($c++ % 2 ==0 ? "a" : "b")?>' id='r<?=$i?>'>
				<?=displayVariable($i, $variable);?>
			</tr>
		<?endforeach;?>
	</table>
<?endif;?>

<?include("pages/footer.php")?>