mixedimage = {};

mixedimage.panel = function(config) {
    config = config || {};

    if (!config.source) {config.source = MODx.config.default_media_source;}
    if (!config.ctx) {config.ctx = 'web';}

    if(config.removeFile){
        var btn_remove = _('mixedimage.trigger_remove');
    }else{        
        var btn_remove = _('mixedimage.trigger_clear');
    }

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
    		,items: [{
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
	                    'select': function(data){
	                    	var value = this.getValue();  
						    Ext.getCmp('mixedimage_input'+config.tvId).setValue(value); 
		                    Ext.get('tv'+config.tvId).dom.value = value;
		                    Ext.get('mixedimage'+config.tvId).dom.value = value;

		                    if(config.showPreview === true){
				                var d = Ext.get('tv-image-preview-'+config.tvId);
				                if (Ext.isEmpty(value)) {
				                    d.update('');
				                } else {
				                    d.update('<img src="'+MODx.config.connectors_url+'system/phpthumb.php?w=300&h=300&aoe=0&far=0&src='+value+'&source='+config.source+'" alt="" />');
				                }
				            } 

		                    MODx.fireResourceFormChange(); 
	                    }
	                }
                }]
    		},{
		    	xtype: 'trigger' 
				,name: 'mixedimage_input'+config.tvId
				,width: 200
        		,value: config.value
				,id: 'mixedimage_input'+config.tvId
				,emptyText: ''
				,triggerConfig: {
				    tag: 'span',
				    cls: 'x-field-combo-btns',
				    cn: [
				        {tag: 'div', cls: 'x-form-trigger x-field-trigger0-class', trigger: 'clear', title: btn_remove},
				        {tag: 'div', cls: 'x-form-trigger x-field-trigger1-class', trigger: 'manager', title: _('mixedimage.trigger_from_file_manager')},
				        {tag: 'div', cls: 'x-form-trigger x-field-trigger2-class', trigger: 'pc', title: _('mixedimage.trigger_from_desktop')}
					]
				}
				,listeners:{
					change: function(data){
						this.saveValue(data.el.getValue());
					}
				}
				,onTriggerClick: function(event, el){
				    // Проверяем какой триггер нажат.
				    switch (el.getAttribute('trigger')){
				        case 'clear': 
		                    this.clearField(config); 
				            break;
				        case 'manager':
				        	var parent = Ext.get('mixedimage_media_container'+config.tvId);
							var elems = parent.select(".x-form-file-trigger").elements; 
                    		elems[0].click();
				            break;
				        case 'pc':				            
                    		Ext.get('mixedimage_desktop'+config.tvId+'-file').dom.click();
				            break;
				        default: 
				            //alert('Нажата кнопка 3');
				    }
				}
				,saveValue: function(value){  
				    Ext.getCmp('mixedimage_input'+config.tvId).setValue(value); 
                    Ext.get('tv'+config.tvId).dom.value = value;
                    Ext.get('mixedimage'+config.tvId).dom.value = value;
                    MODx.fireResourceFormChange(); 
				    this.updateView(config);
				}
				,updateView: function(config){   
					var value = Ext.get('tv'+config.tvId).dom.value;

					if(config.showPreview === true){
		                var d = Ext.get('tv-image-preview-'+config.tvId);
		                if (Ext.isEmpty(value)) {
		                    d.update('');
		                } else {
		                    d.update('<img src="'+MODx.config.connectors_url+'system/phpthumb.php?w=300&h=300&aoe=0&far=0&src='+value+'&source='+config.source+'" alt="" />');
		                }
		            } 

				}
				,clearField: function(config){  
                    var value = Ext.get('tv'+config.tvId).dom.value;

                    if(config.ctx_path){
                    	value = config.ctx_path+value;
                    }

                    if(config.removeFile){
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
					Ext.getCmp('mixedimage_input'+config.tvId).setValue(''); 
		            Ext.get('tv'+config.tvId).dom.value = '';
		            Ext.get('mixedimage'+config.tvId).dom.value = '';
 
                    MODx.fireResourceFormChange(); 
				    this.updateView(config);
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

                            if(!config.res_id && config.onlyEdit == 1){
                            	Ext.Msg.alert('Error', _('mixedimage.err_save_resource'));
                            	return;
                            }  

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
                                    Ext.Msg.alert('Error', _('mixedimage.err_save_resource'));
                                }
                            });
                        }
                        , scope:this }
                }
            }]
        })

        var updatePreview = function(value){
 
			Ext.getCmp('mixedimage_input'+config.tvId).setValue(value); 

            if(config.showPreview === true){
                var d = Ext.get('tv-image-preview-'+config.tvId);
                if (Ext.isEmpty(value)) {
                    d.update('');
                } else {
                    d.update('<img src="'+MODx.config.connectors_url+'system/phpthumb.php?w=300&h=300&aoe=0&far=0&src='+value+'&source='+config.source+'" alt="" />');
                }
            }
 
        } 
 
    });

};

Ext.extend(mixedimage.panel,Ext.Container,{

	//handler functions
	

});

Ext.reg('mixedimage-panel',mixedimage.panel); 