<tr class='{cycle values="a,b"}'>
	<td>
		<div class='resourceName'>{$i.name}</div><big>{$i.comment}</big>
	</td>
	<td id='v{$i.variable_id}'>
		{if sizeof($translations[$i.variable_id]) > 0}
			{foreach from=$translations[$i.variable_id] item=j}
				<div class='userString'>@{$j.username} ({$j.last_update}):</div>
				<div id='t{$j.translation_id}' class='text r{$j.rating}'>
					<span>{$j.text}</span>
					<div>
					{if $j.user_id == $user.user_id}
						<a onclick='return edit({$j.translation_id},{$i.variable_id},false)' >edit</a>
					{else}
						<a onclick='return edit({$j.translation_id},{$i.variable_id},true)' >revise</a> | 
						<a onclick='return vote({$j.translation_id},{$i.variable_id},true)' >correct</a> | 
						<a onclick='return vote({$j.translation_id},{$i.variable_id},false)' >wrong</a>
					{/if}
					</div>
				</div>
			{/foreach}
		{else}
			<textarea class='translationEditBox getFocus' name='{$i.variable_id}'>No translation yet.  Click to translate.</textarea>
		{/if}
	</td>
</tr>