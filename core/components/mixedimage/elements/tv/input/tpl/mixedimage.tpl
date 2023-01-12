<input type="hidden" id="tv{$tv->id}" name="tv{$tv->id}" value="{$tv->value|escape}" /> 
<div id="mixedimage{$tv->id}" class="mixedimage"></div>

{if $showPreview === "true"}
	<div id="tv-image-preview-{$tv->id}" class="modx-tv-image-preview">
		{if $isVideo} 
		    {if $tv->value}
			    <video controls>
				  <source src="../{$source_path}{$tv->value}" type="{$current_mime}"> 
					Your browser does not support the video tag.
				</video> 
			{/if}
		{else}
		    {if $tv->value}
				<img src="{$_config.connectors_url}system/phpthumb.php?w=300&h=300&aoe=0&far=0&src={$tv->value}&source={$tv->source}" alt="" />
			{/if}
		{/if} 
	</div>
{/if}

<script type="text/javascript">
 
	mixedimage{$tv->id} = MODx.load{literal}({
		{/literal}
		xtype: 'mixedimage-panel'
		,renderTo: 'mixedimage{$tv->id}'
		,tvFieldId: 'tv{$tv->id}'
		,tvId: '{$tv->id}'
		,value: '{$tv->value}'
		,res_id: {$res_id}
		,res_alias: '{$res_alias}'
		,p_id: {$p_id}
		,p_alias: '{$p_alias}'
		,tv_id: {$tv_id}
		,ms_id: {$ms_id}
		,acceptedMIMEtypes: {$MIME_TYPES}
		,prefixFilename: {$prefixFilename}
		,triggerlist: '{$triggerlist}'
		,source: '{$tv->source}'
		,showPreview: {$showPreview}
		,removeFile: {$removeFile}
		,ctx_path: '{$source_path}'
		,onlyEdit: {$onlyEdit}
		,openPath: '{$openPath}'
		,isVideo: '{$isVideo}'
		,current_mime: '{$current_mime}'
		{literal}
	});
	{/literal}
	var field = mixedimage{$tv->id}
	MODx.makeDroppable(field, function(v){  
		var newValue = v.replace('{$source_path}', '');
		field.setValue(newValue);
		field.fireEvent('select',{ relativeUrl:newValue });
	});

</script>