(function ($,api) {
    'use strict';

	Themify.LoadCss(tbp_local.cssUrl, tbp_local.v);
	window.top.Themify.LoadCss(tbp_local.cssUrl, tbp_local.v);
	tbp_local.cssUrl=null;

	/* adds body classes required for the Pro's UI */
	if ( typeof tbp_local !== 'undefined' && tbp_local['template'] ) {
		document.body.classList.add( 'tbp_template_type_' + tbp_local['template'] );
		window.top.document.body.classList.add( 'tbp_template_type_' + tbp_local['template'] );
	}

	let settings=null;
    const RunEditAAP = function(el,model){

		/* if AAP is using Slider layout, disable the slider before editing */
		$( '.builder-posts-wrap > .themify_builder_slider', el ).each( function() {
			this.swiper.destroy();
			$( this ).find( 'article.post' ).first().prependTo( $( this ).closest( '.builder-posts-wrap' ) ).siblings().remove();
		} );

            const wrap = el.getElementsByClassName('tbp_advanchd_archive_wrap')[0],
                cl = model.get('mod_name').indexOf('product')!==-1?'_product':'',
                arhiveCL='tbp_edit'+cl+'_archive',
                singleCL='tbp_edit'+cl+'_single',
				args=['order','orderby','offset','meta_key'],
                elId=model.get('element_id'),
                vals =$.extend(true,{},model.get('mod_settings'));
            wrap.classList.add('themify_builder_content');
            wrap.classList.add('themify_builder_content-'+elId);
            wrap.classList.add('themify_builder');
            wrap.setAttribute('id','themify_builder_content-'+elId);
            wrap.setAttribute('data-postid',elId);
			let data = vals['builder_content'],
                items = $('.'+arhiveCL);
            if(typeof data==='string'){
                data =JSON.parse(data);
            }
			settings = {
				'aap' : true
			};

			if ( hasStaticQuery( model.attributes.mod_name ) === true ) {
				args.push('terms');
				args.push('term_type');
				args.push('tax');
				args.push('post_type');
			}
			for(let i=args.length-1;i>-1;--i){
				if(vals[args[i]]!==undefined && vals[args[i]]!=='' ){
					settings[args[i]]=vals[args[i]];
				}
			}
            api.Forms.LayoutPart.cache[elId]=data;
            api.activeModel =api.ActionBar.hoverCid=data=null;
			document.body.className+=' tbp_app_is_edit tbp_module_' + model.get('mod_name');
			window.top.document.body.className+=' tbp_app_is_edit tbp_module_' + model.get('mod_name');
            items = items.add($('.'+arhiveCL,window.top.document));
            for(let i=items.length-1;i>-1;--i){
                items[i].classList.remove(arhiveCL);
                items[i].classList.add(singleCL);
            }
            $(el).one('tb_layout_part_before_init',function(){
                const saveBtn=$(this).find('.tb_toolbar_save'),
                    closeBtn=$(this).find('.tb_toolbar_close_btn');
                saveBtn.on('click',function(e){
                    e.preventDefault();
                    e.stopImmediatePropagation();
					if ( api.activeModel !== null || (ThemifyBuilderCommon.Lightbox.$lightbox.length>0 && ThemifyBuilderCommon.Lightbox.$lightbox[0].classList.contains( 'tb_custom_css_lightbox' )) ) {
						const save = ThemifyBuilderCommon.Lightbox.$lightbox[0].getElementsByClassName('builder_save_button')[0];
						if (save !== undefined) {
							save.click();
						}
					}
                    const data =model.get('mod_settings');
                    data['builder_content']=api.Utils.clear(api.Mixins.Builder.toJSON(api.Instances.Builder[api.builderIndex].el));
                    model.set(data, {silent: true});
                    ThemifyBuilderCommon.showLoader('show');
                    setTimeout(function () {
                        ThemifyBuilderCommon.showLoader('hide');
                        api.Forms.LayoutPart.options=null;
                        api.Forms.LayoutPart.isSaved=true;
                    }, 100);
                });
                closeBtn.on('click',function(e){
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    const isSaved=api.Forms.LayoutPart.isSaved===true;
					settings=null;
                    api.Forms.LayoutPart.close(e);
                    if(isSaved===true || api.builderIndex===0){
                        $(this).off('click');
                        saveBtn.off('click');
                        delete api.Forms.LayoutPart.cache[elId];
                        api.activeModel =api.ActionBar.hoverCid=null;
						document.body.classList.remove( 'tbp_app_is_edit','tbp_module_' + model.get('mod_name') );
						window.top.document.body.classList.remove( 'tbp_app_is_edit', 'tbp_module_' + model.get('mod_name'));
                        for(let i=items.length-1;i>-1;--i){
                            items[i].classList.add(arhiveCL);
                            items[i].classList.remove(singleCL);
                        }
                        if(isSaved===true){
                            ThemifyBuilderCommon.showLoader('show');
                            model.trigger('custom:preview:refresh', model.get('mod_settings'));
                            setTimeout(function () {
                                api.ActionBar.hoverCid=null;
                                ThemifyBuilderCommon.showLoader('hide');
                            }, 220);
                        }
                    }
                });
                $('.tb_overlay').first().one('dblclick',function(e){
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    api.Forms.LayoutPart.options=null;
                    api.Forms.LayoutPart.isSaved=true;
                    saveBtn.triggerHandler('click');
                    closeBtn.triggerHandler('click');
                });
            });
    };

	function hasStaticQuery( mod_name ) {
		return !(( mod_name === 'advanced-posts' && tbp_local['template'] === 'archive' )
			|| ( mod_name === 'advanced-products' && tbp_local['template'] === 'product_archive' ));
	}

    api.Constructor['advanced_posts_query'] = {
        render : function( data, self ) {
            if ( hasStaticQuery( 'advanced-posts' ) === true ) {
				return self.query_posts.render({
					'type' : 'query_posts',
					'id' : 'post_type',
					'tax_id' : 'tax',
					'term_id' : 'terms',
					'slug_id' : 'slug',
					'wrap_class' : 'tbp_app_post_query'
				}, self );
            } else {
                return document.createDocumentFragment();
            }
        }
    };
    api.Constructor['advanced_products_query'] = {
        render : function( data,self ) {
            if ( hasStaticQuery( 'advanced-products' ) === true ) {
				const d = document.createDocumentFragment();
				d.appendChild( self.query_posts.render( {
					'type' : 'query_posts',
					'term_id' : 'terms',
					'taxonomy' : 'product_cat'
				}, self ) );
				return d;
            } else {
                return document.createDocumentFragment();
            }
        }
    };
    api.Constructor['tbp_advanched_layout'] = {
        render:function(data, self) {
            const f = document.createDocumentFragment();
            if(api.mode==='visual'){
                const a = document.createElement('a'),
                    run = function(e){
                        e.stopPropagation();
                        e.preventDefault();
                        this.removeEventListener('click',run,{once:true});
                        const cid = api.activeModel.cid;
                        ThemifyConstructor.saveComponent();
                        this.className+=' tb_edit';
                        api.Models.Registry.lookup(cid).trigger('edit',e,this);
                    };
                a.className='tbp_advanched_archive_edit';
                a.href='#';
                a.textContent=tbp_local.edit;
                a.addEventListener('click',run,{once:true});
                f.appendChild(a);
            }
            if(self.values[data.id]){
                self.values[data.id] = JSON.stringify(self.values[data.id])
            }
            f.appendChild(self.hidden.render(data,self));
            return f;
        }
    };
    api.Constructor['fallback'] = {
        render:function(data, self) {
            const opt = [
                {
                    'id'      : 'fallback_s',
                    'type'    : 'toggle_switch',
                    'label' : 'fall_b',
                    'options'   : {
                            'on'  : { 'name' : 'yes', 'value' : 'en' },
                            'off' : { 'name' : 'no', 'value' : 'dis' }
                        },
                    'binding' : {
                            'checked' : {
                                    'show' : ['fallback_i' ]
                            },
                            'not_checked' : {
                                    'hide' : [ 'fallback_i' ]
                                }
                            }
                },
                {
                    'id' : 'fallback_i',
                    'type' : 'image',
                    'wrap_class' : 'pushed',
                    'class' : 'xlarge'
                }];
           return self.create(opt);
        }
    };
    api.Constructor['tbp_custom_css'] = {
        render:function(data, self) {
            const opt = [
                {
                    'id'      : 'css',
                    'type'    : 'custom_css'
                },
                {
                    'type' : 'custom_css_id'
                }];
           return self.create(opt);
        }
    };
	if(api.mode==='visual'){
		if(tbp_local['id']!==undefined){
			$.ajaxPrefilter(function( options, originalOptions, jqXHR ) {
			   if(originalOptions['data'] && (originalOptions['data']['action']==='tb_render_element' || originalOptions['data']['action']==='tb_load_module_partial')){
				   options['data']+='&pageId='+tbp_local['id'];
				   let type=null;
				   if(settings!==null){
					   for(let i in settings){
						   if(i==='tax'){
							   type=settings[i];
						   }
						   else{
							   options['data']+='&'+i+'='+settings[i];
						   }
					   }
					}
					if(type===null){
					   type=tbp_local['type'];
					}
					options['data']+='&type='+type;
			   }
			});
		}
		Themify.body.on('tb_edit_advanced-posts tb_edit_advanced-products',function(e,ev,el,model){
			if(!api.Forms.LayoutPart.id && ev && (ev.type==='dblclick' || ev.target.classList.contains('tb_edit'))){
				if (api.activeModel !== null) {
					$('.builder_save_button',ThemifyBuilderCommon.Lightbox.$lightbox[0]).click();
				}
				RunEditAAP(el,model);
				return true;
			}
		});
		
	}

	/* create Dynamic Query toggle field on "query_post" field types */
	$( document ).on( 'tb_editing_module', function( e ) {
		const input = themifyBuilder.DynamicQuery.input,
			container = $( ThemifyBuilderCommon.Lightbox.$lightbox[0] );
		container.find( '.tb_field[data-type="query_posts"]' ).each( function() {
			const $this = $( this );
			/* field is already added */
			if ( $this.prev( '.tb_field.tbpdq' ).length ) {
				return;
			}

			const id = $this.find( '.tb_search_input.tb_lb_option:first' ).prop( 'id' );
			input.binding = {
				'off' : { 'show' : [ id ] },
				'on' : { 'hide' : [ id ] }
			};

			if ( tbp_local['template'] !== 'archive' ) {
				input.wrap_class = 'tf_hide';
			}

			$( ThemifyConstructor.create( [ input ] ) )
				.insertBefore( $this );
		} );
	} );
})(jQuery,tb_app);
