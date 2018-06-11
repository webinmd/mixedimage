mixedimage = {};

mixedimage.panel = function(config) {
    config = config || {};

    if (!config.source) {config.source = MODx.config.default_media_source;}
    if (!config.ctx) {config.ctx = 'web';}

    Ext.apply(config,{
        border:false
        ,listeners: {}
        ,items:[{
            xtype: 'container' 
            ,layout: 'column'
            ,border: false   
            ,width: '98%' 
            ,id: 'mixedimage_container'+config.tvId
            ,anchorSize: {width:'98%', height:'auto'}
            ,items: this.getItems(config)
        }]
    });

    mixedimage.panel.superclass.constructor.call(this,config);

    this.previewTpl=new Ext.XTemplate('<tpl for=".">'
            +'<img src="'+MODx.config.connectors_url+'system/phpthumb.php?w={width}&h={height}&f=png&src={value}&source='+this.source+'" alt="" />'
        +'</tpl>', {
        compiled: true
    });

    Ext.onReady(function(){this.loadFileForm();},this);
    
    this.on('onFileUploadSuccess',this.onFileUploadSuccess,this);
};

Ext.extend(mixedimage.panel,Ext.Container,{
    getItems:function(config){
        return [this.getImageContainer(config),this.getTriggerField(config)];
    }
    ,getImageContainer:function(config){
        return{
            xtype:'container' 
            ,hidden: true
            ,id: 'mixedimage_media_container'+config.tvId
            ,items: [{ 
                xtype:'modx-combo-browser'
                ,browserEl: 'modx-browser'
                ,TV: this
                ,id: 'mixedimage_media'+config.tvId
                ,source: config.source
                ,ctx_path: config.ctx_path
                ,openTo: config.openPath
                ,listeners: {
                    'select':{fn:this.onBrowserSelect,scope:this}
                }
            }]
        };
    }
    ,getDefaultTriggerConfig:function(config){
        return{
            xtype: 'mixedimage-trigger' 
            ,name: 'mixedimage_input'+config.tvId
            ,value: config.value
            ,id: 'mixedimage_input'+config.tvId
            ,emptyText: ''
            ,tvId: config.tvId
            ,source: config.source
            ,showPreview: config.showPreview
            ,ctx_path: config.ctx_path
            ,removeFile: config.removeFile
            ,listeners:{
                change: function(data){
                    this.saveValue(data.el.getValue());
                }
            }
        };
    }
    ,getTriggerField:function(config){
        return this.getDefaultTriggerConfig(config);
    }
    ,getCustomPath:function(){
        return this.custompath||'';
    }
    ,onFileUploadSuccess:function(r){
        
    }
    ,onBrowserSelect:function(data,field){ 
        //var value = field.getValue();
        var value = data.url;
        this.setValue(value);
    }
    ,setValue:function(value){
        this.getInput().setValue(value); 
        this.getTVField().dom.value = value;
        this.el.dom.value = value;
        this.updatePreview(value);
        MODx.fireResourceFormChange();
    }
    ,getInput:function(){
        this.input = this.input||Ext.getCmp('mixedimage_input'+this.tvId);
        return this.input;
    }
    ,getTVField:function(){
        this.tvfield = this.tvfield||Ext.get('tv'+this.tvId);
        return this.tvfield;
    }
    ,getPreview:function(){
        this.preview = this.preview||Ext.get('tv-image-preview-'+this.tvId);
        return this.preview;
    }
    ,updatePreview:function(value){
        if(this.showPreview === true){
            var d = this.getPreview();
            var content = '';
            if(!Ext.isEmpty(value))content = this.previewTpl.apply({width:200,height:100,value:value});
            d.update(content);
        }
    }
    ,loadFileForm:function(){
        this.fileform=new mixedimage.fileform({
            id: this.uploadFormInputId
            ,renderTo: 'modx-content'
            ,TV:this
        });
        this.fileform.on('onFileUploadSuccess',function(r){ this.fireEvent('onFileUploadSuccess',r);},this);
    }
});
Ext.reg('mixedimage-panel',mixedimage.panel); 


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
mixedimage.fileform = function(config){
    config = config||{};
    Ext.applyIf(config,{
        isUpload: true
        ,hidden: true
        ,fileUpload: true
        ,url: MODx.config.assets_url+'components/mixedimage/connector.php'
        ,baseParams:this.getBaseParams(config)
        ,items:this.getItems(config)
    });
    mixedimage.fileform.superclass.constructor.call(this,config);
};
Ext.extend(mixedimage.fileform,Ext.FormPanel,{
    getBaseParams:function(config){
        return {
            action: 'browser/file/upload'
            ,tvId: config.TV.tvId
            ,prefixFilename: config.TV.prefixFilename
            ,res_id: config.TV.res_id
            ,res_alias: config.TV.res_alias
            ,p_id: config.TV.p_id
            ,p_alias: config.TV.p_alias
            ,tv_id: config.TV.tv_id
            ,ms_id: config.TV.ms_id
            ,acceptedMIMEtypes: config.TV.acceptedMIMEtypes
            ,lex: config.TV.jsonlex
            ,ctx_path: config.TV.ctx_path
            //,resize: config.resize
        };
    }
    ,getItems:function(config){
        var items=[];
        items.push(
            {
                xtype:'fileuploadfield'
                ,panel: config.TV
                ,id: 'mixedimage_desktop'+config.TV.tvId
                ,listeners: {
                    'fileselected': {fn:this.onFileSelected,scope:this}
                }
            }
        );
        return items;
    }
    ,onFileSelected:function(field,value){
        this.form.baseParams.file = field.getValue();
        
        var params = {};
        params.custompath=this.TV.getCustomPath()||'';
        params.formdata = Ext.util.JSON.encode(Ext.getCmp('modx-panel-resource').getForm().getValues()||{});
        
        this.form.submit({
            waitMsg: 'Uploading...',
            params:params,
            success: function(fp, o){
                var value = o.result.message;
                this.TV.setValue(value);
                this.fireEvent('onFileUploadSuccess',o.result);
            }
            ,failure: function(fp, o) { 
                Ext.Msg.alert('Error', _('mixedimage.err_save_resource'));
            }
            ,scope:this
        });
    }
});
Ext.reg('mixedimage-fileform',mixedimage.fileform);

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
mixedimage.trigger = function(config){
    config = config||{};
    
    config.triggerWidth=config.triggerWidth||30;
    config.triggerConfig=this.getTriggerConfig(config);
    config.width=(config.width||350)+config.triggerConfig.rightOffset;
    config.style=config.style||{};
    config.style.paddingRight=config.triggerConfig.rightOffset+'px';
    
    Ext.applyIf(config,{
        
    });
    mixedimage.trigger.superclass.constructor.call(this,config);
};
Ext.extend(mixedimage.trigger,Ext.form.TriggerField,{
    getTriggerConfig: function(config){
        var btn_remove = config.removeFile?_('mixedimage.trigger_remove'):_('mixedimage.trigger_clear');
        var __triggerConfig={
            tag: 'span',
            cls: 'x-field-combo-btns',
            cn: [
                {tag: 'div', cls: 'x-form-trigger x-field-trigger0-class', trigger: 'clear', title: btn_remove},
                {tag: 'div', cls: 'x-form-trigger x-field-trigger1-class', trigger: 'manager', title: _('mixedimage.trigger_from_file_manager')},
                {tag: 'div', cls: 'x-form-trigger x-field-trigger2-class', trigger: 'pc', title: _('mixedimage.trigger_from_desktop')}
            ]
        };
        var triggerConfig = config.triggerConfig||{};
        triggerConfig.cn = triggerConfig.cn||[];
        triggerConfig.rightOffset=0;
        
        triggerConfig.cn = __triggerConfig.cn.concat(triggerConfig.cn);
        Ext.applyIf(triggerConfig,__triggerConfig);

        for(var i=triggerConfig.cn.length-1;i>=0;i--){
            if(!triggerConfig.cn.hasOwnProperty(i))continue;
            var width = triggerConfig.cn[i].width||config.triggerWidth;
            var style = 'right: '+triggerConfig.rightOffset+'px !important;';
            style += 'width:'+width+'px;';
            triggerConfig.cn[i].style=style;
            triggerConfig.rightOffset += width+2;
        }

        return triggerConfig;
    }
    ,onTriggerClick: function(event, el){
        // Проверяем какой триггер нажат.
        f=this['handleTrigger__'+el.getAttribute('trigger')];
        if(typeof(f)=='function')f.call(this,this,el);
        if(typeof(f)=='object'&&typeof(f.fn)=='function'){
            f.fn.call(f.scope||this,this,el);
        }
    }
    ,handleTrigger__clear:function(field,el){
    	this.clearField();
    }
    ,handleTrigger__manager:function(field,el){
    	var parent = Ext.get('mixedimage_media_container'+this.tvId);
        var elems = parent.select(".x-form-file-trigger").elements; 
        elems[0].click();
    }
    ,handleTrigger__pc:function(field,el){
    	Ext.get('mixedimage_desktop'+this.tvId+'-file').dom.click();
    }
    ,saveValue: function(value){  
        Ext.getCmp('mixedimage_input'+this.tvId).setValue(value); 
        Ext.get('tv'+this.tvId).dom.value = value;
        Ext.get('mixedimage'+this.tvId).dom.value = value;
        MODx.fireResourceFormChange(); 
        this.updateView();
    }
    ,updateView: function(){   
        var value = Ext.get('tv'+this.tvId).dom.value;

        if(this.showPreview === true){
            var d = Ext.get('tv-image-preview-'+this.tvId);
            if (Ext.isEmpty(value)) {
                d.update('');
            } else {
                d.update('<img src="'+MODx.config.connectors_url+'system/phpthumb.php?w=200&h=100&f=png&src='+value+'&source='+this.source+'" alt="" />');
            }
        } 

    }
    ,clearField: function(){  
        var value = Ext.get('tv'+this.tvId).dom.value;

        if(this.ctx_path){
            value = this.ctx_path+value;
        }

        if(this.removeFile){
            Ext.Ajax.request({
                url: MODx.config.assets_url+'components/mixedimage/connector.php',
                success: function(data){                                
                    Ext.Msg.alert('Remove', _('mixedimage.success_removed'));
                }
                ,failure: function(data) {
                    Ext.Msg.alert('Error', _('mixedimage.error_remove'));                                
                    console.log(data);
                }
                ,params: { value: value, action: 'removeFile' }
            });
        } 
        Ext.getCmp('mixedimage_input'+this.tvId).setValue(''); 
        Ext.get('tv'+this.tvId).dom.value = '';
        Ext.get('mixedimage'+this.tvId).dom.value = '';

        MODx.fireResourceFormChange(); 
        this.updateView();
    }
});
Ext.reg('mixedimage-trigger',mixedimage.trigger);
