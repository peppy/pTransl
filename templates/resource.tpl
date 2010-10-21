{include file="header.tpl" title="Viewing Resource"}
<div class='resourceHeader'>
	<h1><a >&laquo;</a> Resource: {$resource.name} ({$variables|@sizeof})</h1>
	<div class='languages'>
	{section name=i loop=$languages}
		{strip}
			{if $languages[i].code == $resource.base_language_code}
				<span class='gray'>{$languages[i].name} (base)</span> 
			{elseif $languageCode == $languages[i].code}
				<span class='active'>{$languages[i].name} ({$languages[i].complete})</span> 
			{else}
				<span><a href='/p/resource?r={$resource[resource_id]}&l={$languages[i].code}'>
					{$languages[i].name} ({$languages[i].complete})
				</a></span> 
			{/if}
		{/strip}
	{/section}
	</div>
</div>

{if !$languageCode}
	Please select a language to view translations.
{else}
	<table class='resourceList'>
		<tr>
			<th width="20%">Original Content</th>
			<th>Translation</th>
		</tr>
		{foreach from=$variables item=i}
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
		{/foreach}
	</table>
{/if}
{include file="footer.tpl" title="login"}