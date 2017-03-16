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
            ,border: false
            ,layout: 'form'
            ,labelAlign: 'top'
            ,labelSeparator: ''
            ,width:'98%'
            ,id: 'mixedimage_form'+config.tvId
            ,anchorSize: {width:'98%', height:'auto'}
            ,items: [{
                xtype: 'modx-combo-browser'
                ,name: 'mixedimagefield'
                ,id: 'mixedimage'+config.tvId
                ,maxLength: 255
                ,width:'100%'
                ,value: config.value
                ,browserEl: 'modx-browser'
                ,source: config.source
                ,ctx_path: config.ctx_path
                ,listeners: {
                    'select': function(data){
                        MODx.fireResourceFormChange();
                        Ext.get('tv'+config.tvId).dom.value = this.getValue();

                        if(config.showPreview === true){
                            var d = Ext.get('tv-image-preview-'+config.tvId);
                            if (Ext.isEmpty(data.url)) {
                                d.update('');
                            } else {
                                d.update('<img src="'+MODx.config.connectors_url+'system/phpthumb.php?w=300&h=300&aoe=0&far=0&src='+data.url+'&wctx='+config.ctx+'&source='+config.source+'" alt="" />');
                            }
                        }

                        if(config.showValue === true){
                            var d_name = Ext.get('mixedimage_name'+config.tvId);
                            if (Ext.isEmpty(data.url)) {
                                d_name.update('');
                            } else {
                                d_name.update(data.url);
                            }

                        }

                    }
                }

                ,getTrigger: function(index) {
                    return this.triggers[index];
                }
                ,onTrigger1Click: function() {
                    this.onTriggerClick();
                }
                ,onTrigger2Click: function() {
                    Ext.get('mixedimage_desktop'+config.tvId+'-file').dom.click();
                }
                ,onTrigger3Click: function() {
                    var value = '';
                    Ext.get('tv'+config.tvId).dom.value = value;
                    Ext.get('mixedimage'+config.tvId).dom.value = value;
                    MODx.fireResourceFormChange();

                    if(config.showPreview === true){
                        Ext.get('tv-image-preview-'+config.tvId).update('');
                    }

                    if(config.showValue === true){
                        Ext.get('mixedimage_name'+config.tvId).update('');
                    }

                }
                ,triggerConfig: [{
                    tag: 'span',
                    cls: 'x-field-search-btns',
                    cn: [
                        {tag: 'div', cls: 'x-form-trigger x-field-trigger1-class', title: _('mixedimage.trigger_from_file_manager')}
                        ,{ tag: 'div', cls: 'x-form-trigger x-field-trigger2-class', title: _('mixedimage.trigger_from_desktop')}
                        ,{tag: 'div', cls: 'x-form-trigger x-field-trigger0-class', title: _('mixedimage.trigger_clear')}
                    ]
                }]
                ,initTrigger: function() {
                    var ts = this.trigger.select('.x-form-trigger', true);
                    this.wrap.setStyle('overflow', 'hidden');
                    var triggerField = this;
                    ts.each(function(t, all, index) {
                        t.hide = function() {
                            var w = triggerField.wrap.getWidth();
                            this.dom.style.display = 'none';
                            triggerField.el.setWidth(w - triggerField.trigger.getWidth());
                        };
                        t.show = function() {
                            var w = triggerField.wrap.getWidth();
                            this.dom.style.display = '';
                            triggerField.el.setWidth(w - triggerField.trigger.getWidth());
                        };
                        var triggerIndex = 'Trigger' + (index + 1);

                        if (this['hide' + triggerIndex]) {
                            t.dom.style.display = 'none';
                        }
                        t.on('click', this['on' + triggerIndex + 'Click'], this, {
                            preventDefault: true
                        });
                        t.addClassOnOver('x-form-trigger-over');
                        t.addClassOnClick('x-form-trigger-click');
                    }, this);
                    this.triggers = ts.elements;
                }
            }]
        }]
    });

    mixedimage.panel.superclass.constructor.call(this,config);

    Ext.onReady(function(){

        var mixedfileform = new Ext.FormPanel({
            id: this.uploadFormInputId
            ,renderTo: 'modx-content'
            ,isUpload: true
            ,hidden: true
            ,fileUpload: true
            ,url: MODx.config.assets_url+'components/mixedimage/connector.php'
            ,baseParams: {
                action: 'browser/file/upload'
                ,tvId: config.tvId
                ,prefixFilename: config.prefixFilename
                ,res_id: config.res_id
                ,res_alias: config.res_alias
                ,p_id: config.p_id
                ,p_alias: config.p_alias
                ,tv_id: config.tv_id
                ,ms_id: config.ms_id
                ,acceptedMIMEtypes: config.acceptedMIMEtypes
                ,lex: config.jsonlex
                ,ctx_path: config.ctx_path
                //,resize: config.resize
            }
            ,TV: this
            ,items: [{
                xtype:'fileuploadfield'
                ,TV: this
                ,id: 'mixedimage_desktop'+config.tvId
                ,listeners: {
                    'fileselected': {fn:
                        function(){
                            var UploadField = mixedfileform.items.items[0];
                            mixedfileform.form.baseParams.file = UploadField.getValue();
                            mixedfileform.form.submit({
                                waitMsg: 'Uploading...',
                                success: function(fp, o){
                                    var value = o.result.message;
                                    Ext.get('tv'+config.tvId).dom.value = value;
                                    Ext.get('mixedimage'+config.tvId).dom.value = value;
                                    MODx.fireResourceFormChange();
                                    updatePreview(value);
                                }
                                ,failure: function(fp, o) {
                                    Ext.Msg.alert(o.result.message);
                                }
                            });
                        }
                        , scope:this }
                }
            }]
        })

        var updatePreview = function(val){

            if(config.showPreview === true){
                var d = Ext.get('tv-image-preview-'+config.tvId);
                if (Ext.isEmpty(val)) {
                    d.update('');
                } else {
                    d.update('<img src="'+MODx.config.connectors_url+'system/phpthumb.php?w=300&h=300&aoe=0&far=0&src='+val+'&source='+config.source+'" alt="" />');
                }
            }

            if(config.showValue === true){
                var d_name = Ext.get('mixedimage_name'+config.tvId);
                if (Ext.isEmpty(val)) {
                    d_name.update('');
                } else {
                    d_name.update(val);
                }
            }
        }

    });

};

Ext.extend(mixedimage.panel,Ext.Container,{
//handler functions
});

Ext.reg('mixedimage-panel',mixedimage.panel); 