(function($,api) {
	'use strict';

	let DC={},
		DynamicCache=null,
		hidden;
		
		const CacheRequest = {},
            fieldName=tbpDynamic.field_name,
			getData = function (callback) {
                if ( DynamicCache ===null ) {
                    const name='tbp_dc',
                        key=Themify.hash(tbpDynamic.v+Object.keys(tbpDynamic.items)),
                        writeStorage=function(value){
                            try{
                                sessionStorage.setItem(name,JSON.stringify({'v':value,'h':key}));
                            }
                            catch(e){
                                return null;
                            }
                        },
                        readStorage=function () {
                            if(themifyBuilder.debug){
                                return null;
                            }
                            try{
                                let result=sessionStorage.getItem(name);
                                if(result){
                                    result = JSON.parse(result);
                                    if(result['h']===key && result['v']){
                                        return result['v'];
                                    }
                                }
                            }
                            catch(e){
                                return null;
                            }
                            return null;
                        };
                    DynamicCache = readStorage();
                    if(DynamicCache===null){
                        $.ajax({
                            type: 'POST',
                            url: themifyBuilder.ajaxurl,
                            dataType: 'json',
                            data: {
                                action: 'tpb_get_dynamic_content_fields',
                                tb_load_nonce: themifyBuilder.tb_load_nonce
                            },
                            error:function(){
                                DynamicCache=null;  
                            },
                            success: function ( data ) {
                                DynamicCache = data;
                                writeStorage(data);
                                if(callback){
                                    callback();
                                }
                            }
                        });
                    }
                } else if(callback){
                    callback();
                }
        },
        getPreviewVal = function(vals,callback){
            // get preview
            let after='',
                before='',
                req;
			const result=function(v){
                    if(before!==undefined || after!==undefined){
                        if(!v){
                            v='';
                        }
                        if(before!==undefined){
                                v=before+v;
                        }
                        if(after!==undefined){
                                v+=after;
                        }
                    }
                    if(callback){
                        callback(v);
                    }
                };
            if(typeof vals!=='string'){
                if(!vals || vals['item']===undefined){
                    return;
                }
                req = $.extend(true,{},vals);
                after =req['text_after'],
                before=req['text_before'];
                delete req['text_before'];
                delete req['text_after'];
                delete req['o'];
                req = JSON.stringify(req);
            }
            else{
                req=vals;
            }
            let postId;
            if(api.Forms.LayoutPart.id && document.body.classList.contains('tbp_app_is_edit')){
                    postId =api.Instances.Builder[api.builderIndex].el.parentNode.id;
                    if(postId){
                            postId = postId.split('-')[1];
                    }
            }
            if(!postId){
                    postId=typeof tbp_local!=='undefined' && tbp_local['id']!==undefined && !tbp_local['isArchive']?tbp_local['id']:themifyBuilder.post_ID;
            }
            const key = Themify.hash(req+postId);
            if(CacheRequest[key]===undefined){
                $.ajax({
                        type: 'POST',
                        url: themifyBuilder.ajaxurl,
                        dataType: 'json',
                        data: {
                                action: 'tpb_get_dynamic_content_preview',
                                tb_load_nonce: themifyBuilder.tb_load_nonce,
                                pid : postId,
                                values : req
                        },
                        success : function( data ) {
                                CacheRequest[key] = data['error']?data['error']:(data['value']==='' || data['value']==='false' ? null : data['value']);
                                result(CacheRequest[key]);
                        }
                } );
            }
            else{
                result(CacheRequest[key]);
            }
        },
        createOptions=function(type,values){
            const oldVals= ThemifyConstructor.values,
                options_wrap=document.createElement('div'),
                Options =Object.values($.extend(true,{}, DynamicCache)),
                dynamic=tbpDynamic.items;
                options_wrap.className='tbp_dynamic_content_options';
                for(let i=Options.length-1;i>-1;--i){
                        if(Options[i].id==='item'){
                            let group = Options[i].options;
                            for(let j in group){
                                for(let k in group[j]['options']){
                                    if(dynamic[ k ]!==undefined && dynamic[ k ]['type'].indexOf(type)===-1 ) {
                                        delete group[j]['options'][k];
                                    }
                                }
                                if(Object.keys(group[j]['options']).length===0){
                                    delete group[j];
                                }
                            }  
                            break;
                        }
                    }
            if(values===undefined){
                values={};
            }
            ThemifyConstructor.values = values;
            const form= ThemifyConstructor.create(Options),
                opt=form.querySelectorAll('.tb_lb_option');
            // prevent Builder from saving these fields individually
            for(let i=opt.length-1;i>-1;--i){
                opt[i].classList.remove('tb_lb_option','tb_lb_option_child');
            }
            ThemifyConstructor.values=oldVals;
            options_wrap.appendChild(form);
            return options_wrap;
        },
        EnableDc=function(el,init){
            
            let field = el.parentNode.closest('.tb_field'),
                pid=getRepeatId(field),
                type = getType( field ),
                parent=field.getElementsByClassName('tb_input')[0],
                values={};
                if(parent!==undefined && parent.parentNode.classList.contains('tb_has_dc')){
                    field = parent;
                    parent=null;
                }
                const id = getId(field,type);
                if(pid!==null){
                    if(DC[pid]!==undefined){
                        const index=getRepeatIndex(field);
                        if(DC[pid][index]!==undefined){
                            values=DC[pid][index];
                        }
                    }
                }
                else{
                    values = DC;
                }
                if(init===true){
                    if(values[id]!==undefined){
                        el.checked=true;
                    }
                    else{
                        return;
                    }
                }
                if(el.checked===true){
                    field.classList.add('tbp_dc_active');
                    let placeholder=field.getElementsByClassName( 'tbp_dc_input' )[0];
                    if (placeholder===undefined ) {
                        getData( function() {
                           const  wrap=document.createElement('div');
                                placeholder=document.createElement('input');
                                wrap.className='tbp_dc_wrap';
                                placeholder.className='tbp_dc_input xlarge';
                                placeholder.type='text';
                                placeholder.setAttribute('readonly',true);
                                const onTypeChange= function(el){
                                    const dcWrap = el.closest('.tbp_dc_wrap'),
                                        itemType=dcWrap.querySelector('#item'),
                                        v = itemType.value,
                                        items = itemType.closest('.tb_field').nextElementSibling.children,
                                        cl = 'field_'+v,
                                        generalCl='field_general_'+type;
										let blocks =[];
                                        placeholder.value=itemType.options[itemType.selectedIndex].text;
                                        blocks.push(itemType.parentNode);
                                    for(let i=items.length-1;i>-1;--i){
                                        if(v!=='' && (items[i].classList.contains(cl) || items[i].classList.contains(generalCl))){
                                            items[i].style['display']='block';
                                            blocks.push(items[i]);
                                        }
                                        else{
                                            items[i].style['display']='';
                                        }
                                    }
                                    ThemifyConstructor.callbacks();
                                    if(!v){
                                        blocks=null;
                                    }
                                    const vals= update_value( id, blocks,dcWrap );
                                    blocks=null;
                                    getPreviewVal(vals,function(res){

										/* fallback value in preview */
										if ( null === res ) {
											res = type === 'image'?tbpDynamic.placeholder_image:'{' + vals['item'] + '}';
										}

                                        const item = getField(itemType,id);
                                        if(item!==null){
                                            item.value=res;
                                            let obj=null;
                                            if(type==='wp_editor'){
												obj =tinymce.get( item.id );
                                                if(obj){
                                                    obj.setContent( String( res ) );
                                                    obj.fire( 'change' );
                                                }
                                            }
                                            if(!obj){
                                                Themify.triggerEvent(item,'change');
                                                if( type!=='image' && item.nodeName!=='SELECT'){
                                                    Themify.triggerEvent(item,'keyup');
                                                }
                                            }
                                        }
                                    });
                                };
                                placeholder.addEventListener('click',function(e){
                                    e.preventDefault();
                                    e.stopImmediatePropagation();
                                    let options_wrap = this.nextElementSibling;
                                    if(options_wrap===null){
                                        options_wrap = createOptions(type,values[id]);
                                        $(options_wrap).on( 'change.dc_preview', ':input',function(e){
                                            e.stopPropagation();
                                            onTypeChange(e.target);
                                        });
                                        this.parentNode.appendChild(options_wrap);
                                    }
                                    const isVisible=options_wrap.style['display']!=='block',
                                        Optitems =ThemifyBuilderCommon.Lightbox.$lightbox[0].getElementsByClassName('tbp_dynamic_content_options');
                                    for(let i=Optitems.length-1;i>-1;--i){
                                        Optitems[i].style['display']='';
                                    }
                                    if(isVisible===true){
                                        const Click = function(e){
                                            if(e.target.closest('.tbp_dc_wrap')===null){
                                                document.removeEventListener('mousedown',Click,{passive:true});
                                                if(api.mode==='visual'){
                                                    window.top.document.removeEventListener('mousedown',Click,{passive:true});
                                                }
                                                options_wrap.style['display']='';
                                            }
                                            if(api.mode==='visual'){
                                                $(document).triggerHandler('mouseup');
                                            }
                                        };
                                        document.addEventListener('mousedown',Click,{passive:true});
                                        if(api.mode==='visual'){
                                            window.top.document.addEventListener('mousedown',Click,{passive:true});
                                        }
                                        onTypeChange(this);
                                        options_wrap.style['display']='block';
                                    }
                                    else{
                                        options_wrap.style['display']='';
                                    }
                                });
                                wrap.appendChild(api.Utils.getIcon('ti-pencil'));
                                wrap.appendChild(placeholder);
                                if(values[ id ]!==undefined){
                                    const value= values[ id ]['item'],
                                        opt = DynamicCache[0]['options'];
                                    for(let i in opt){
                                        if(opt[i]['options'][value]!==undefined){
                                            placeholder.value=opt[i]['options'][value];
                                            break
                                        }
                                    }
                                    if(type==='image' && ThemifyConstructor.clicked==='styling'){
                                        const imgOptions=field.closest('.tb_tab').getElementsByClassName('tb_image_options');
                                        for(let i=imgOptions.length-1;i>-1;--i){
                                            imgOptions[i].classList.remove('_tb_hide_binding');
                                        }
                                    }
                                    else{
                                        ThemifyConstructor.callbacks();
                                    }
                                }
                                field.appendChild(wrap);
                        } );
                        if(init===undefined){
                            setOrigValue(field,id,type);
                        }
                    }
                    else if(init===undefined){
                        setOrigValue(field,id,type);
                        if(values[id]!==undefined){
                            placeholder.click();
                        }
                        toggleStylesheet(false);
                    }
                }
                else if(init===undefined){
                    revertOrigValue(field,id,type);
                    field.classList.remove('tbp_dc_active');
                    update_value( id, null,field );
                    toggleStylesheet(true);
                }
            
        },
        getRepeatIndex=function(el){
            return $(el.closest('.tb_repeatable_field')).index();
        },
        getRepeatId=function(el){
            const item= el.parentNode.closest('.tb_row_js_wrapper');
            return item!==null?item.getAttribute('id'):null;
        },
        toggleStylesheet=function(disable){
            if(api.mode==='visual' && ThemifyConstructor.clicked==='styling'){
                let el = api.liveStylingInstance.$liveStyledElmt[0].closest('.tb_active_layout_part');
				if(el===null){
					el=api.liveStylingInstance.$liveStyledElmt[0];
				}
				const styles=el.getElementsByClassName('tbp_dc_styles');
				for(let i=styles.length-1;i>-1;--i){
					styles[i].sheet.disabled =disable;
				}
            }  
        },
        getId=function(el,type){
            const cl=type==='image'?'tb_uploader_input':(el.parentNode.closest('.tb_repeatable_field_content')!==null?'tb_lb_option_child':'tb_lb_option'),
                item = el.getElementsByClassName(cl)[0];
			let id;
            if(item!==undefined){
                id=item.getAttribute('data-input-id');
            }
            if(!id){
                id=item.getAttribute('id');
            }
            return id.trim();
        },
		update_value=function ( key, val,item ) {
            let dc,
                index=null,
                pid=getRepeatId(item);
            if(pid!==null){
                index=getRepeatIndex(item);
            }
            if ( val === null ) {
				dc=hidden.value;
                dc=dc?JSON.parse(dc):{};
                if(!dc){
                    dc={};
                }
                if(index!==null){
                    let update = false;
                    if(dc[pid]!==undefined){
                        if(key===null){
                            if(dc[pid][index]!==undefined){
                                update = true;
                                delete DC[pid][index];
                                delete dc[pid][index];
                            }
                        }
                        else if( dc[pid][index]!==undefined && dc[pid][index][key]!==undefined){
                            update = true;
                            delete dc[pid][index][key];
                            const len=Object.keys(dc[pid][index]).length;
                            if(len===0 || (len===1 && dc[pid][index]['o']!==undefined)){
                                update = true;
                                delete DC[pid][index];
                                delete dc[pid][index];
                            }
                        }
                        else{
                            return;
                        }
                        if ( Object.keys( dc[pid] ).length === 0 || ( Object.keys( dc[pid] ).length === 1 && dc[pid]['repeatable'] !== undefined ) ) {
                            update = true;
                            delete DC[pid];
                            delete dc[pid];
                        }
                    }
                    if(update===false){
                        return;
                    }
                }
                else{
                    if(dc[key]!==undefined){
                        delete dc[ key ];
                    }
                    else{
                        return;
                    } 
                }
            } 
            else {
                let orig;
                if(index!==null){
                    if(DC[ pid ]===undefined){
                        DC[ pid ]={};
                    }
					DC[ pid ]['repeatable'] = 1; // flag for "builder" field type (repetable fields)
                    if(DC[pid][index]===undefined){
                        DC[pid][index]={};
                    }
                    if(DC[pid][index][key]===undefined){
                        DC[pid][index][key]={};
                    }
                    orig =DC[pid][index][key]['o']!==undefined? DC[pid][index][key]['o']:null;
                }
                else{
                    if(DC[ key ]===undefined){
                        DC[ key ]={};
                    }
                    orig =DC[key]['o']!==undefined? DC[key]['o']:null;
                }
                if(Array.isArray(val)){
                    const values={};
                    for(let i=val.length-1;i>-1;--i){
                        let items = val[i].querySelectorAll('input,textarea,select');
                        for(let j=items.length-1;j>-1;--j){
                            let v =items[j].value;
                            if(v!=='' && !items[j].parentNode.parentNode.classList.contains('_tb_hide_binding')){
                                values[items[j].id]=v;
                            }
                        }
                    }
                    val=api.Utils.clear(values);
                }
                if(index!==null){
                    DC[pid][index][key] = val;
                    if(orig!==null){
                        DC[pid][index][key]['o'] = orig;
                    }
                }
                else{
                    DC[ key ] = val;
                    if(orig!==null){
                        DC[ key ]['o'] = orig;
                    }
                }
                dc=DC;
            }
            hidden.value=JSON.stringify( dc );
            return val;
	},  
	/**
	 * Get original field
	 *
	 * @return dom element
	 */
	getField=function(field,id){
		let item = field.closest('.tb_repeatable_field_content');
		if(item!==null){
			item=item.querySelector('.tb_lb_option_child[data-input-id="'+id+'"]');
		}
		else{
			item = ThemifyBuilderCommon.Lightbox.$lightbox[0].querySelector('#'+id);
		}
		return item;
	},
	/**
	 * Set value to original field
	 *
	 * @return mixed
	 */
	setOrigValue=function ( field,id,type ) {
            let value=null,
                index=null,
                _id=id;
            if(type==='wp_editor'){
                let obj = tinymce.get( id );
                if(!obj){
					obj = tinymce.get( getField(field,id).id );
                }
                if(obj){
                    value=obj.getContent();
                }
            }
            else{
                const item = getField(field,id);
                if(item!==null){
                    value=item.value;
                }
            }
            const pid=getRepeatId(field);
            if(pid!==null){
                index=getRepeatIndex(field);
                _id = pid;
            }
            if(value!==null && value!==''){
                if(DC[_id]===undefined){
                    DC[_id]={};
                }
                if(index!==null){
                    if(DC[_id][index]===undefined){
                        DC[_id][index] = {};
                    }
                    if(DC[_id][index][id]===undefined){
                        DC[_id][index][id]={};
                    }
                    DC[_id][index][id]['o'] =value; 
                }
                else{
                    DC[_id]['o']=value;
                }
            }
            else if(DC[_id]!==undefined){
                if(index!==null){
                    if(DC[_id][index]!==undefined && DC[_id][index][id]!==undefined){
                        delete DC[_id][index][id]['o'];
                    }
                }
                else{
                    delete DC[_id]['o'];
                }
            }
            hidden.value=JSON.stringify( DC );
            return value;
	},
        /**
	 * Revert value to original field
	 *
	 * @return void
	 */
        revertOrigValue=function(field,id,type){
            let pid=getRepeatId(field),
                v='';
            if(pid!==null){
                if(DC[pid]!==undefined){
                    const index=getRepeatIndex(field);
                    if(DC[pid][index]!==undefined && DC[pid][index][id]!==undefined && DC[pid][index][id]['o']!==undefined){
                        v=DC[pid][index][id]['o'];
                    }
                }
            }
            else if(DC[id]!==undefined && DC[id]['o']!==undefined){
                v=DC[id]['o'];
            }
            if(type==='wp_editor'){
                let obj =tinymce.get( id );
                if(!obj){
					obj = tinymce.get( getField(field,id).id );
                }
                if(obj){
                    obj.setContent( String( v ) );
                    obj.fire( 'change' );
                }
            }
            else{
                const item = getField(field,id);
                if(item!==null){
                    item.value=v;
                    Themify.triggerEvent(item,'change');
                    if( type!=='image' && item.nodeName!=='SELECT'){
                        Themify.triggerEvent(item,'keyup');
                    }
                }
            }
        },
	/**
	 * Get a div.tb_field element and returns the element type
	 *
	 * @return string
	 */
	getType=function ( field ) {
		let type = field.getAttribute( 'data-type' );
		if ( type === 'imageGradient' ) {
			/* imageGradient field type is used in Styling, functions similarly as "image" */
			type = 'image';
		} else if ( type === 'title' ) {
			/* module title fields, treated as "text" */
			type = 'text';
		}
		return type;
	},

	/**
	 * Get a list of Builder field types that can accept Dynamic Content
	 *
	 * Compiled from get_type() method of all available Tbp_Dynamic_Item
	 *
	 * @return array
	 */
	getApplicableTypes = function() {
		const types = {};
		$.each( tbpDynamic.items, function( i, item ) {
			$.each( item.type, function( v, field_type ) {
				if ( types[ field_type ] === undefined ) {
					types[ field_type ] = field_type;
				}
			} );
		} );

		types['title'] = 'title';
		types['imageGradient'] = 'imageGradient';

		return Object.keys( types );
	},
	addSwitch = function ( container, types = [] ) {
		const exlclude=tbpDynamic.excludes,
			dl=tbpDynamic.d_label;

		if ( types.length === 0 ) {
			types = getApplicableTypes();
		}

		let found=false;
		for(let i=types.length-1;i>-1;--i){
			let items = container.querySelectorAll('.tb_field[data-type="' + types[i] + '"]');
			for(let j=items.length-1;j>-1;--j){
				if ( ! items[j].classList.contains( 'tb_has_dc' ) && ! items[j].classList.contains( 'tb_disable_dc' ) ) {
					found=false;
					/* certain options should not have DC enabled */
					for(let k=exlclude.length-1;k>-1;--k){
						if(items[j].classList.contains(exlclude[k])){
							found=true;
							break;
						}
					}
					if(found===false){
						items[j].className+=' tb_has_dc';
						let label = document.createElement('label'),
							input=document.createElement('input'),
							div=document.createElement('div');
						label.className='tpb_dc_toggle switch-wrapper';
						input.type='checkbox';
						input.className='toggle_switch';
						div.className='switch_label';
						div.setAttribute('data-on',dl);
						div.setAttribute('data-off',dl);
						input.addEventListener('change',function(e){
							e.stopPropagation();
							EnableDc(this);
						},{passive:true});
						label.appendChild(input);
						label.appendChild(div);
						items[j].insertBefore(label, items[j].firstChild);
						EnableDc(input,true);
					}
				}
			}
		}
	};

	$(window).one('load',function(){
		Themify.requestIdleCallback( function() {
			if(api.mode!=='visual' || Themify.is_builder_loaded===true){
				setTimeout(getData,1500);
			}
			else{
				window.top.Themify.body.one('themify_builder_ready', function (e) {
				   setTimeout(getData,2500);
				});
			}
			$( document ).on( 'tb_repeatable_add_new tb_repeatable_duplicate tb_repeatable_delete', function( e ) {
					if(e.type==='tb_repeatable_delete'){
						update_value(null,null,e.detail[0]);
					}
					else{
						addSwitch( e.detail[0] );
					}
			} ).on( 'tb_editing_module tb_editing_row tb_editing_column tb_editing_subrow', function( e ) {
					const builder = ThemifyConstructor,
						container = ThemifyBuilderCommon.Lightbox.$lightbox[0];
						hidden = builder.hidden.render({
									'type' : 'hidden',
									'class':'exclude-from-reset-field',
									'responsive':false,
									'control':false,
									'id'   : fieldName
								},builder);
						// save & store previously saved values
						if(builder.values[ fieldName ]!==undefined){
							try{
								DC = typeof builder.values[ fieldName ]==='string'?JSON.parse( builder.values[ fieldName ] ):$.extend(true,{},builder.values[ fieldName ]);
								hidden.value=JSON.stringify( DC ) ;
							}
							catch(e){
								DC={};
							}
						}
						else{
							DC={};
						}
					container.getElementsByClassName( 'tb_options_tab_content' )[0].appendChild(hidden);
					addSwitch(container);
			} );
			Themify.body.on( 'themify_builder_tabsactive tb_options_expand', function( e, id, container ) {
				if(api.activeModel!==null){
					let clicked=ThemifyConstructor.clicked;
					setTimeout(function(){
						clicked=ThemifyConstructor.clicked;
						if ( clicked !== 'animation' && clicked !== 'visibility' ) {
							if ( e.type === 'tb_options_expand' ) {
								container = id;
								id = null;
							}
							if ( clicked !== 'styling' || id===null || id.indexOf('_h') === -1 ) {
								// in Styling options, only enable on Background option
								addSwitch( container, clicked === 'styling' ? [ 'imageGradient', 'image' ] : [] );
							}
						}
					}, clicked === 'setting' ? 0 : 50 );
				}
			} );
		}, 15 );
	});

})(jQuery,tb_app);
