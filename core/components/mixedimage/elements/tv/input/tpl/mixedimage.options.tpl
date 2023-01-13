<div id="tv-input-properties-form{$tv}"></div>
{literal}
    <style>
        .mixedimageInfo {
            margin-top: 20px;
        }
        .mixedimageInfo h4 {
            margin-top: 10px;
        }
        .mixedimageInfo ul {
            margin-left:20px;
            font-size:12px;
            margin-top:5px;
            color: #666;
        }
        .mixedimageInfo ul li span {
            font-family:mono;
            font-weight:bold;
        }
    </style>
<div class="mixedimageInfo">
    {/literal}{include file="$options_desc_tpl"}{literal}
</div>

<script type="text/javascript">
    // <![CDATA[
    var params = {
        {/literal}{foreach from=$params key=k item=v name='p'}  
        {if $v|is_array}
        {foreach from=$v key=i item=j name='dd'}
        '{$i}': '{$j}',
        {/foreach}
        {else}
            '{$k}': '{$v|escape:"javascript"}'{if NOT $smarty.foreach.p.last},{/if}
        {/if}        
        {/foreach}{literal}
    };
    var oc = {'change':{fn:function(){Ext.getCmp('modx-panel-tv').markDirty();},scope:this}};  

    {/literal}
    MixedImageLex = {$tveulex};
    function __(key){
        return MixedImageLex[key];
    };
    {literal}

    console.log(params); 

    MODx.load({
        xtype: 'panel'
        ,layout: 'form'
        ,autoHeight: true
        ,cls: 'form-with-labels'
        ,border: false
        ,labelAlign: 'top'
        ,items: [{
            xtype: 'textfield',
            fieldLabel: __('mixedimage.save_path'),
            name: 'inopt_path',
            id: 'inopt_path{/literal}{$tv}{literal}',
            value: params['path'] || '',
            anchors: '98%',
            listeners: oc
        },{
            xtype: MODx.expandHelp ? 'label' : 'hidden'
            ,forId: 'inopt_path{/literal}{$tv}{literal}'
            ,html: __('mixedimage.save_path_desc')
            ,cls: 'desc-under'
        },{
            xtype: 'textfield',
            fieldLabel: __('mixedimage.file_prefix'),
            name: 'inopt_prefix',
            id: 'inopt_prefix{/literal}{$tv}{literal}',
            value: params['prefix'] || '',
            anchors: '98%',
            listeners: oc
        },{
            xtype: MODx.expandHelp ? 'label' : 'hidden'
            ,forId: 'inopt_prefix{/literal}{$tv}{literal}'
            ,html: __('mixedimage.file_prefix_desc')
            ,cls: 'desc-under'
        },{
            xtype: 'textfield',
            fieldLabel: __('mixedimage.mime_types'),
            name: 'inopt_MIME',
            id: 'inopt_MIME{/literal}{$tv}{literal}',
            value: params['MIME'] || '',
            anchors: '98%',
            listeners: oc
        },{
            xtype: MODx.expandHelp ? 'label' : 'hidden'
            ,forId: 'inopt_MIME{/literal}{$tv}{literal}'
            ,html: __('mixedimage.mime_types_desc')
            ,cls: 'desc-under'
        },{
            xtype: 'modx-combo-boolean',
            fieldLabel: __('mixedimage.prefix_filename'),
            name: 'inopt_prefixFilename',  
            id: 'inopt_prefixFilename{/literal}{$tv}{literal}',
            value: params['prefixFilename'] || 0,
            anchors: '98%',
            listeners: oc
        },{
            xtype: 'modx-combo-boolean',
            fieldLabel: __('mixedimage.show_preview'),
            name: 'inopt_showPreview',  
            id: 'inopt_showPreview{/literal}{$tv}{literal}',
            value: params['showPreview'] || 1,
            anchors: '98%',
            listeners: oc
        },{
            xtype: MODx.expandHelp ? 'label' : 'hidden'
            ,forId: 'inopt_MIME{/literal}{$tv}{literal}'
            ,html: __('mixedimage.show_preview_desc')
            ,cls: 'desc-under'
        },{
            xtype: 'modx-combo-boolean',
            fieldLabel: __('mixedimage.remove_file'),
            name: 'inopt_removeFile',
            id: 'inopt_removeFile{/literal}{$tv}{literal}',
            value: params['removeFile'] || 0,
            anchors: '98%',
            listeners: oc
        },{
            xtype: MODx.expandHelp ? 'label' : 'hidden'
            ,forId: 'inopt_removeFile{/literal}{$tv}{literal}'
            ,html: __('mixedimage.remove_file_desc')
            ,cls: 'desc-under'
        },{
            xtype: 'textfield',
            fieldLabel: __('mixedimage.resize'),
            name: 'inopt_resize',
            id: 'inopt_resize{/literal}{$tv}{literal}',
            value: params['resize'] || '',
            anchors: '98%',
            listeners: oc
        },{
            xtype: MODx.expandHelp ? 'label' : 'hidden'
            ,forId: 'inopt_resize{/literal}{$tv}{literal}'
            ,html: __('mixedimage.resize_desc')
            ,cls: 'desc-under'
        },{
            xtype: 'textfield',
            fieldLabel: __('mixedimage.triggerlist'),
            id: 'inopt_triggerlist{/literal}{$tv}{literal}', 
            anchors: '98%',
            name: 'inopt_triggerlist',
            value: params['triggerlist'] || 'clear,manager,pc',
            listeners: oc
        },{
            xtype: MODx.expandHelp ? 'label' : 'hidden'
            ,forId: 'inopt_triggerlist{/literal}{$tv}{literal}'
            ,html: __('mixedimage.triggerlist_desc')
            ,cls: 'desc-under'
        }]
        ,renderTo: 'tv-input-properties-form{/literal}{$tv}{literal}'
    });
    // ]]>
</script>
{/literal}