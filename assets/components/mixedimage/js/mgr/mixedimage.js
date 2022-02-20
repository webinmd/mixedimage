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
            ,triggerlist: config.triggerlist
            ,listeners:{
                change:{fn:function(data){
                    this.setValue(data.el.getValue());
                },scope:this}
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
    ,getExtension:function(value){
        var ext = value.split('.').pop(); 
        var isVideo = false;

        if(ext == 'mp4'){
            var mime_type = 'video/mp4';
            isVideo = true;
        }
        if(ext == 'ogg'){
            var mime_type = 'video/ogg';
            isVideo = true;
        }

        return{
            ext: ext,
            mime_type: mime_type,
            isVideo: isVideo
        } 
    }
    ,updatePreview:function(value){
        if(this.showPreview === true){
            var d = this.getPreview();
            var content = '';
            
            if(!Ext.isEmpty(value)){
                
                var file_info = this.getExtension(value); 

                if(file_info.isVideo){

                    if(this.ctx_path){
                        var path = this.ctx_path;
                    }else{
                        var path = '';
                    }

                    this.previewTpl = new Ext.XTemplate('<tpl for=".">'
                            +'<video controls>'
                            +'<source src="../'+path+value+'" type="'+file_info.mime_type+'">'
                            + '</video>'
                        +'</tpl>', {
                        compiled: true
                    });

                }else{
                    this.previewTpl = new Ext.XTemplate('<tpl for=".">'
                            +'<img src="'+MODx.config.connectors_url+'system/phpthumb.php?w={width}&h={height}&f=png&src={value}&source='+this.source+'" alt="" />'
                        +'</tpl>', {
                        compiled: true
                    });
                } 

                content = this.previewTpl.apply({width:200,height:100,value:value});  

            }     
            d.update(content);
        }
    }
    ,loadFileForm:function(){
        this.uploadFormInputId = 'mixedimage_fileform'+this.tvId;
        this.fileform=new mixedimage.fileform({
            id: this.uploadFormInputId
            ,renderTo: 'modx-content'
            ,TV:this
        });
        this.fileform.on('onFileUploadSuccess',function(r){ this.fireEvent('onFileUploadSuccess',r);},this);
    }
});
Ext.reg('mixedimage-panel',mixedimage.panel); 


//////////////////////////////////////////////////////////


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
            action: 'file/upload'
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
            ,source: config.TV.ms_id
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
        params.custompath = this.TV.getCustomPath()||'';
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
                MODx.msg.alert('Error', o.result.message);
            }
            ,scope:this
        });
    }
    ,ajaxUpload: function(file) {
        var fp = this;
        var params = {};
        Ext.apply(params,this.baseParams);
        params.custompath = this.TV.getCustomPath()||'';
        params.formdata = Ext.util.JSON.encode(Ext.getCmp('modx-panel-resource').getForm().getValues()||{});
        params.HTTP_MODAUTH = MODx.siteId;

        FileAPI.upload({
            url: this.url
            ,data: params
            ,files: { file: file }
            ,beforeupload: function () {
                Ext.MessageBox.wait('Uploading...')
            }
            ,complete: function(err, xhr){
                Ext.MessageBox.updateProgress(1);
                Ext.MessageBox.hide();
                if( !err ){
                    var response = Ext.util.JSON.decode(xhr.response);
                    if(response.success){
                        var value = response.message;
                        fp.TV.setValue(value);
                        fp.fireEvent('onFileUploadSuccess',response);
                        fp.form.reset();
                    }
                    else{
                        MODx.msg.alert('Error', response.message);
                    }
                }
                else if(xhr.status !== 401){
                    MODx.msg.alert('Error', err);
                }

            }
        });
    }
});
Ext.reg('mixedimage-fileform',mixedimage.fileform);


//////////////////////////////////////////////////////////////


mixedimage.window = function (config) {
    config = config || {}; 
 
    config.formdata = Ext.util.JSON.encode(Ext.getCmp('modx-panel-resource').getForm().getValues()||{}); 

    Ext.applyIf(config, {
         url: MODx.config.assets_url+'components/mixedimage/connector.php'  
        ,fields: this.getFields(config)
        ,keys: this.getKeys(config)
        ,width: 400
        ,layout: 'anchor'
        ,autoHeight: false 
        ,baseParams:this.getBaseParams(config) 
    });
    mixedimage.window.superclass.constructor.call(this, config);
};



Ext.extend(mixedimage.window, MODx.Window, {

    getKeys: function (config) {
        return [{
            key: Ext.EventObject.ENTER,
            shift: true,
            fn: this.submit,
            scope: this
        }];
    },

    getFields: function (config) {
        return [{
            xtype: 'textfield',
            fieldLabel: _('mixedimage.link'),
            name: 'url',
            allowBlank: false,
            anchor: '99% -210'
        }];
    },

    getBaseParams:function(config){   

        var fields = window['mixedimage'+config.params.tvId];  

        return {
            action: 'file/upload'
            ,tv_id: fields.tvId  
            ,tvId: fields.tv_id  
            ,prefixFilename: fields.prefixFilename
            ,res_id: fields.res_id
            ,res_alias: fields.res_alias
            ,p_id: fields.p_id
            ,p_alias: fields.p_alias 
            ,ms_id: fields.ms_id
            ,acceptedMIMEtypes: fields.acceptedMIMEtypes
            ,lex: fields.jsonlex
            ,ctx_path: fields.ctx_path 
            ,formdata: config.formdata
        };
    }

});
Ext.reg('mixedimage-window-getfromurl', mixedimage.window);


//////////////////////////////////////////////////////////


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

    this.on('afterrender',function(){
        this.initDD();
    },this);
};

Ext.extend(mixedimage.trigger,Ext.form.TriggerField,{
    getTriggerConfig: function(config){  

        var btn_remove = config.removeFile?_('mixedimage.trigger_remove'):_('mixedimage.trigger_clear');
        var __triggerConfig={
            tag: 'span',
            cls: 'x-field-combo-btns',
            cn: []
        };
 
        var triggers = config.triggerlist.split(",");   
        var trList = []; 

        for (var i = 0; i < triggers.length; i++) { 
           trList.push({
                tag: 'div', 
                cls: 'x-form-trigger x-field-trigger-'+triggers[i]+'-class', 
                trigger: triggers[i], 
                title:  _('mixedimage.trigger_btn_'+triggers[i])
            }); 
        } 
       
        var triggerConfig = config.triggerConfig||{};
        triggerConfig.cn = trList||[];
        triggerConfig.rightOffset=0;
        triggerConfig.cn = __triggerConfig.cn.concat(triggerConfig.cn); 
        Ext.applyIf(triggerConfig,__triggerConfig);

        for(var i=triggerConfig.cn.length-1;i>=0;i--){
            if(!triggerConfig.cn.hasOwnProperty(i))continue;
            var width = triggerConfig.cn[i].width||config.triggerWidth;
            var style = 'right: '+triggerConfig.rightOffset+'px !important;';
            style += 'width:'+width+'px;';
            triggerConfig.cn[i].style=style;
            triggerConfig.rightOffset += width + 2;
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
    ,handleTrigger__url:function(field,el){
        this.getFromUrl(field,el);
    }
    ,clearField: function(){     

        if(this.removeFile){
            Ext.Ajax.request({ 
                url: MODx.config.assets_url+'components/mixedimage/connector.php'
                ,params: { 
                     file: this.value 
                    ,action: 'file/remove' 
                    ,source: this.source
                }
                ,success: function(data){                                
                    MODx.msg.alert('Remove', _('mixedimage.success_removed'));
                }
                ,failure: function(data) {
                    MODx.msg.alert('Error', _('mixedimage.error_remove'));              
                }
            });
        } 
        
        this.setValue('');
        this.fireEvent('change',this);
    }

    ,getExtension:function(value){
        var ext = value.split('.').pop(); 
        var isVideo = false;

        if(ext == 'mp4'){
            var mime_type = 'video/mp4';
            isVideo = true;
        }
        if(ext == 'ogg'){
            var mime_type = 'video/ogg';
            isVideo = true;
        }

        return{
            ext: ext,
            mime_type: mime_type,
            isVideo: isVideo
        } 
    }
    ,getFromUrl: function(btn, e){  

        if (!this.window) {
            this.window= MODx.load({
                 xtype: 'mixedimage-window-getfromurl'
                ,id: Ext.id()
                ,title: _('mixedimage.window_url')
                ,saveBtnText:  _('mixedimage.upload_file')  
                ,params: btn            
                ,listeners: {
                    success: {
                        fn: function (data) {  

                            var value = data.a.result.message;

                            var input = btn.input||Ext.getCmp('mixedimage_input'+btn.tvId);
                            input.setValue(value);

                            var tvfield = btn.tvfield||Ext.get('tv'+this.tvId);
                            tvfield.dom.value = value;
                            btn.el.dom.value = value;

                            if(btn.showPreview === true){ 
                                var d = btn.preview||Ext.get('tv-image-preview-'+btn.tvId);
                                var content = '';
                                if(!Ext.isEmpty(value)){ 
                                    var file_info = this.getExtension(value);  

                                    if(file_info.isVideo){

                                        if(this.ctx_path){
                                            var path = this.ctx_path;
                                        }else{
                                            var path = '';
                                        }

                                        this.previewTpl = new Ext.XTemplate('<tpl for=".">'
                                                +'<video controls>'
                                                +'<source src="../'+path+value+'" type="'+file_info.mime_type+'">'
                                                + '</video>'
                                            +'</tpl>', {
                                            compiled: true
                                        });

                                    }else{
                                        this.previewTpl=new Ext.XTemplate('<tpl for=".">'
                                                +'<img src="'+MODx.config.connectors_url+'system/phpthumb.php?w={width}&h={height}&f=png&src={value}&source='+this.source+'" alt="" />'
                                            +'</tpl>', {
                                            compiled: true
                                        });
                                    } 
                                }
                                
                                content = this.previewTpl.apply({width:200,height:100,value:value});  
                                d.update(content);
                            } 

                            MODx.fireResourceFormChange();  
                            this.window.hide();
                        }, scope: this
                    }
                    ,failure: function(fp, o) {  
                        MODx.msg.alert('Error', o.result.message);
                    }
                }
            });
        }

        this.window.reset();
        this.window.setValues({active: true});
        this.window.show(e.target);
    }
    ,initDD: function () {
        if(!this._initializedDD) {
            this.el.on('drag', this.onDDrag, this);
            this.el.on('dragstart', this.onDDrag, this);
            this.el.on('dragend', this.onDDrag, this);
            this.el.on('dragover', this.onDDrag, this);
            this.el.on('dragenter', this.onDDrag, this);
            this.el.on('dragleave', this.onDDrag, this);
            this.el.on('drop', this.onDDrop, this);
            this._initializedDD = true;
        }

    }
    ,onDDrag: function (event) {
        event && event.preventDefault();
        if(event.type == "dragover") {
            if(!this.el.hasClass('x-form-field-trigger-dd')) this.addClass('x-form-field-trigger-dd');
        } else {
            if(this.el.hasClass('x-form-field-trigger-dd')) this.removeClass('x-form-field-trigger-dd');
        }

    }
    ,onDDrop: function (event) {
        event && event.preventDefault();
        if(this.el.hasClass('x-form-field-trigger-dd')) this.removeClass('x-form-field-trigger-dd');
        var dt = event.browserEvent.dataTransfer;
        var files = dt.files;
        if(files.length) {
            var form = Ext.getCmp('mixedimage_fileform'+this.tvId);
            if(form) {
                form.ajaxUpload(files[0]);
            }
        }
    }
});
Ext.reg('mixedimage-trigger',mixedimage.trigger);


///////////////////////////////////////////////////////