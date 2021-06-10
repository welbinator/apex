var ThemifyBuilderCommon;
(function ($,document, Themify) {
    'use strict';
    
    let checkedItems = {},
    TBP = {
        isLoaded: null,
        options: null,
        labels: null,
        lightbox: null,
        lightboxContainer: null,
        type: null,
        conditions:null,
        pageId: null,
        id: null,
        isSaved:null,
        showLoader(show) {
            const cl = document.body.classList;
            show ? cl.add('tbp_loading') : cl.remove('tbp_loading');
        },
        init() {
            this.pageId = themifyBuilder.pageId;
            this.options = _tbp_app.options;
            this.labels = themifyBuilder.labels;
            _tbp_app.options = themifyBuilder.labels = themifyBuilder.pageId = null;
            document.body.classList.add('tbp_page',this.pageId + '_page');

            const template = document.getElementById('tmpl-tbp_builder_lightbox'),
                    btn = document.getElementsByClassName('page-title-action')[0],
                    items = document.getElementsByClassName('tbp_lightbox_edit'),
                    import_btn = document.createElement('div');

            import_btn.className = 'tbp_admin_import ' + this.pageId + '_import';
            import_btn.innerHTML = themifyBuilder.import_btn ? themifyBuilder.import_btn : '';

            document.body.appendChild(template.content);
            this.lightbox = document.getElementById('tb_lightbox_parent');
            this.lightboxContainer = this.lightbox.getElementsByClassName('tb_options_tab_wrapper')[0];
            this.lightbox.getElementsByClassName('ti-close')[0].addEventListener('click', this.close.bind(this));
            btn.addEventListener('click', this.edit.bind(this));
            if (themifyBuilder.import_btn) {
                btn.parentNode.insertBefore(import_btn, btn.nextSibling);
                themify_create_pluploader($(import_btn.firstElementChild));
                const alertLoading = document.createElement('DIV');
                alertLoading.className = 'alert';
                document.body.appendChild(alertLoading);
            }
            for (let i = items.length - 1; i > -1; --i) {
                items[i].addEventListener('click', this.edit.bind(this));
            }
            if(_tbp_app.draftBtn!==undefined){
                this.lightbox.getElementsByClassName('tbp_submit_draft_btn')[0].textContent = _tbp_app.draftBtn;
            }
            this.lightbox.getElementsByClassName('tbp_btn_save')[0].textContent = _tbp_app.publishBtn;
            ThemifyBuilderCommon = {Lightbox: {$lightbox: $(this.lightbox)}};
            setTimeout(function () {
                const link = document.createElement('link'),
                        loader = document.createElement('div');
                link.href = _tbp_app.api_base;
                link.rel = 'prefetch';
                loader.className = 'tb_busy';
                document.head.appendChild(link);
                document.body.appendChild(loader);
                this.pointerInit();
            }.bind(this), 500);
            themifyBuilder.import_btn = null;
        },
        getValue(key) {
            if (ThemifyConstructor.values[key] !== undefined) {
                return ThemifyConstructor.values[key];
            }
            for (let i = this.options.length - 1; i > -1; --i) {
                if (this.options[i].id === key) {
                    return this.options[i]['options'] === undefined ? this.options[i] : Object.keys(this.options[i]['options'])[0];
                }
            }
            return null;
        },
		/**
		 * @arg string file URL to the JSON file containing the demo data
		 * @arg string theme_id ID of the newly created theme
		 * @arg function callback function to call after all import is done
		 */
		import_sample_content( file, theme_id, callback ) {
			$.ajax( {
				url : file,
				dataType : 'json',
				success : function( resp ) {
					const queue = [],
						max_query = 5; // maximum number of requests to send simultaneously
						let count = 0; // keep track of how many requests are ongoing simultaneously

					if ( resp.terms !== undefined ) {
						$.each( resp.terms, function( term_id, term ) {
							queue.push( {
								action : 'tbp_import_term',
								term : term
							} );
						} );
					}
					if ( resp.posts !== undefined ) {
						$.each( resp.posts, function( post_id, post ) {
							queue.push( {
								action : 'tbp_import_post',
								theme_id : theme_id,
								post : post
							} );
						} );
					}

					if ( queue.length === 0 ) {
						callback();
						return;
					}

					function make_request() {
						if ( queue.length === 0 || count > max_query  ){
							return;
						}
						++count;
						$.ajax( {
							url : ajaxurl,
							dataType : 'json',
							type: 'POST',
							data : queue.shift(),
							success : function( response ) {
								--count;
								make_request();
								if ( count < 1 ) {
									callback();
									return;
								}
							}
						} );
					}
					for ( let i = 0; i < max_query; ++i ) {
						make_request();
					}
				}
			} );
		},
        createCustomTypes() {
            const editBtn = this.lightbox.getElementsByClassName('builder_button_edit')[0];
            if (this.id !== null) {
                editBtn.textContent = this.pageId==='tbp_theme' && _tbp_app.active!=this.id?_tbp_app.publishBtn:this.labels['save'];
            }
            else{
                editBtn.textContent =_tbp_app.next;
            }
            if (ThemifyConstructor['tbp_type'] !== undefined) {
                return;
            }
			var  bindings = undefined;
            const _this = this,
                    cache = {},
                    cachePredesing = {},
                    saveLightbox = function (is_draft, id, data) {
                        $.ajax({
                            type: 'POST',
                            url: themifyBuilder.ajaxurl,
                            dataType: 'json',
                            beforeSend() {
                                _this.showLoader(true);
                            },
                            data: {
                                type: _this.pageId,
                                id: id,
                                is_draft: is_draft || 0,
                                action: _this.pageId + '_saving',
                                data: data,
                                tb_load_nonce: themifyBuilder.tb_load_nonce
                            },
                            complete() {
                                _this.showLoader();
                            },
                            success(resp) {
                                if (resp) {
                                    if (resp.redirect) {
										if ( _this.pageId === 'tbp_theme' ) {
											const $theme = $( '.layout_preview_list.selected', _this.lightbox ),
												slug = $theme.attr( 'data-slug' );
											if ( slug === 'blank' ) {
												window.location = resp.redirect;
												return;
											}
											const theme_id = resp.redirect.match( /id=(\d+)/ )[1];
											setTimeout( function() {
												_this.showLoader(true);
											}, 200 );
											// import tbp_template posts for the theme
											TBP.import_sample_content( 'https://themify.me/public-api/builder-pro-demos/pro-' + slug + '-templates.json', theme_id, function() {
												if ( $( '.tbp_import_demo input', $theme ).is( ':checked' ) ) {
													TBP.import_sample_content( 'https://themify.me/public-api/builder-pro-demos/pro-' + slug + '.json', theme_id, function() {
														window.location = resp.redirect;
													} );
												} else {
													window.location = resp.redirect;
												}
											} );
										} else {
											window.location = resp.redirect;
										}
                                    }
                                    else {
                                        if ( _this.pageId === 'tbp_theme' ) {
                                            _this.lightbox.getElementsByClassName('ti-close')[0].click();
                                            window.location.reload();
                                        }
                                        else{
                                            _this.lightbox.classList.add('tbp_lightbox_is_saved');
                                            setTimeout(function(){
                                                _this.lightbox.classList.remove('tbp_lightbox_is_saved');
                                            },2000);
                                        }
                                        _this.isSaved=true;
                                    }
                                }
                            }
                        });
                    },
					masonry = function(el){
						function resizeMasonryItem(item){
							const rowGap = parseInt(window.getComputedStyle(el).getPropertyValue('grid-row-gap')),
								rowHeight = parseInt(window.getComputedStyle(el).getPropertyValue('grid-auto-rows'));
							if(isNaN(rowGap) || isNaN(rowHeight)){
								return;
							}
							const itemHeight = item.getElementsByClassName('thumbnail')[0].getBoundingClientRect().height + item.getElementsByClassName('layout_action')[0].getBoundingClientRect().height,
								rowSpan = Math.ceil((itemHeight+rowGap)/(rowHeight+rowGap));
							item.style.gridRowEnd = 'span '+rowSpan;
							if(rowSpan>5){
								item.dataset['masonry'] = 'done';
							}
						}
						const allItems = el.querySelectorAll('.layout_preview_list:not([data-masonry="done"])');
						for(let i=0,len=allItems.length;i<len;i++){
							resizeMasonryItem(allItems[i]);
						}
					},
                    setPredesgnedList = function (result) {
                        document.body.classList.add('tbp_step_2');
						let api_base = _tbp_app.api_base,
							type = result['tbp_template_type'];
                        const demo_base = _tbp_app.demo_base,
							container = _this.lightbox.getElementsByClassName('tb_options_tab_content'),
                                callback = function (data) {
                                    const f = document.createDocumentFragment(),
                                            wrap = document.createElement('div'),
                                            ul = document.createElement('ul'),
                                            selected = container[1] !== undefined ? container[1].getAttribute('data-' + type + '-selected') : null;
                                    wrap.className = 'tbp_predesigned_row_container';
                                    wrap.id = _this.pageId + '_import';
                                    ul.className = 'tbp_predesigned_theme_lists';
                                    if('footer' === type || 'header' === type){
										ul.className += ' tbp_predesigned_'+type;
									}
                                    if (data[0] === undefined || data[0].slug !== 'blank') {
                                        data.unshift({'slug': 'blank', link: '#', 'title': {rendered: _tbp_app.blank}, 'id': ''});
                                    }
                                    for (let i = 0, len = data.length; i < len; ++i) {
                                        let li = document.createElement('li'),
                                                img = document.createElement('img'),
                                                thumb = document.createElement('div'),
                                                action = document.createElement('div'),
                                                title = document.createElement('div'),
                                                aImg = document.createElement('a'),
                                                aTitle = document.createElement('a'),
                                                icon = document.createElement('i'),
                                                preview = document.createElement('div');
                                        li.className = 'layout_preview_list';
                                        li.setAttribute('data-slug', data[i].slug);
                                        if (data[i].id) {
                                            li.setAttribute('data-id', data[i].id);
                                        }
                                        preview.className = 'layout_preview';
                                        thumb.className = 'thumbnail';
                                        action.className = 'layout_action';
                                        title.className = 'layout_title';

                                        aImg.title = data[i].title.rendered;
                                        if (selected === data[i].slug || (!selected && data[i].slug === 'blank')) {
                                            li.className += ' selected';
                                        }
                                        if (data[i].slug === 'blank') {
                                            preview.className += ' layout_preview_blank';
                                        }
                                        if ( demo_base && data[i].link && data[i].link !== '#' ) {
											// for Theme demos
											aImg.href = aTitle.href = demo_base + data[i].slug;
                                            aTitle.target = '_blank';
                                            aTitle.innerHTML = data[i].title.rendered;
                                            title.appendChild(aTitle);
                                        } else {
											// for Templates; no preview page
                                            title.innerHTML = data[i].title.rendered;
                                        }
                                        img.src = data[i]['tbp_image_full'] ? data[i].tbp_image_full : themifyBuilder.ph_image;
                                        img.alt = data[i].title.rendered;
                                        thumb.appendChild(img);
                                        aImg.appendChild(icon);
                                        action.appendChild(title);
                                        action.appendChild(aImg);
                                        preview.appendChild(thumb);
                                        preview.appendChild(action);
                                        li.appendChild(preview);

										if ( _this.pageId === 'tbp_theme' ) {
											let importtick = document.createElement( 'input' ),
												importWarning = document.createElement( 'span' ),
												importlbl = document.createElement( 'label' );
											importtick.type = 'checkbox';
											importlbl.className = 'tbp_import_demo';
											importlbl.appendChild( importtick );
											importlbl.appendChild( document.createTextNode( tbpAdminVars.i18n.import ) );
											importWarning.appendChild( document.createTextNode( tbpAdminVars.i18n.import_warning ) );
											importlbl.appendChild( importWarning );
											li.appendChild( importlbl );
										}
                                        f.appendChild(li);

                                    }
                                    ul.appendChild(f);
                                    wrap.appendChild(ul);
                                    const lightboxTitle = _this.lightbox.getElementsByClassName('tbp_lightbox_title')[0],
                                            prevLink = document.createElement('a'),
                                            icon = document.createElement('i');
                                    prevLink.className = 'tbp_wizard_step_prev';
                                    prevLink.href = '#';
                                    icon.className = 'ti-arrow-left';
                                    prevLink.appendChild(icon);
                                    lightboxTitle.innerHTML = '';
                                    lightboxTitle.appendChild(prevLink);
                                    lightboxTitle.appendChild(document.createTextNode(_tbp_app.import));
                                    prevLink.addEventListener('click', function (e) {
                                        e.stopPropagation();
                                        e.preventDefault();
                                        document.body.classList.remove('tbp_step_2');
                                        container[1].style['display'] = 'none';
                                        container[0].style['display'] = '';
                                        this.parentNode.innerHTML = _tbp_app.add_template;
                                    });

                                    if (container[1] !== undefined) {
                                        container[1].innerHTML = '';
                                        container[1].appendChild(wrap);
                                        container[1].style['display'] = '';
                                    }
                                    else {
                                        const tabContent = document.createElement('div');
                                        tabContent.className = 'tb_options_tab_content';
                                        tabContent.appendChild(wrap);
                                        container[0].parentNode.insertBefore(tabContent, container[0].nextSibling);
                                    }
                                    container[0].style['display'] = 'none';
                                    ul.addEventListener('click', function (e) {
                                        if (e.target.closest('.layout_title, .tbp_import_demo') === null) {
                                            e.stopPropagation();
                                            e.preventDefault();
                                        }
                                        const el = e.target.closest('.layout_preview_list');
                                        if (el !== null && !el.classList.contains('selected')) {
                                            const childs = this.children;
                                            for (let i = childs.length - 1; i > -1; --i) {
												childs[i].classList.remove('selected');
                                            }
                                            el.classList.add('selected');
                                            container[1].setAttribute('data-' + type + '-selected', el.dataset['slug']);
                                        }

                                    });
                                    // Masonry
									if ( _this.pageId === 'tbp_template' ) {
										let img = ul.getElementsByTagName('img');
										if (img.length > 0) {
											$(img).one('load', function () {
												masonry(ul);
											});
										} else {
											masonry(ul);
										}
									}
                                    $(_this.lightbox).find('.tbp_step_2_actions').first().off('click').on('click', function (e) {
                                        if (e.target.classList.contains('tbp_submit_draft_btn') || e.target.classList.contains('tbp_btn_save')) {
                                            e.stopPropagation();
                                            e.preventDefault();
                                            result['import'] = ul.getElementsByClassName('selected')[0].getAttribute('data-slug');
                                            saveLightbox(e.target.classList.contains('tbp_submit_draft_btn'), null, result);
                                        }
                                    });
                                };
                        if (type !== undefined) {
                            api_base += type;
                        }
                        else {
                            type = 'theme';
                        }
                        if (cachePredesing[type] === undefined) {
                            container[0].classList.add('tb_busy');
                            $.getJSON(api_base, function (data) {
                                cachePredesing[type] = data;
                                callback(data);
                            })
                                    .always(function () {
                                        container[0].classList.remove('tb_busy');
                                    })
                                    .fail(function () {
                                        callback([]);
                                    });
                        }
                        else {
                            callback(cachePredesing[type]);
                        }
                    };
            ThemifyConstructor['tbp_image'] = {
                change(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const input = $(e.currentTarget).parent().children('input').first();
                    input.val('').trigger('change');
                    Themify.triggerEvent(input[0], 'change');
                },
                render(data, self) {
                    const image = self.image.render(data, self),
                            attach = {
                                id: data.id + '_id'
                            };
                    image.getElementsByClassName('tb_clear_input')[0].addEventListener('click', this.change.bind(this));
                    image.appendChild(self.hidden.render(attach, self));
                    return image;
                }
            };
            ThemifyConstructor['tbp_type'] = {
                id: null,
                render(data, self) {
                    if (bindings === undefined) {
                        bindings = data['binding'];
                    }
                    this.id=data.id;
                    TBP.type =TBP.getValue(this.id);
                    const select = self.select.render(data, self);
                    select.querySelector('select').addEventListener('change', function (e) {
                        e.stopPropagation();
                        TBP.type = this.value;
                        ThemifyConstructor['condition'].reInit();
                    }, {passive: true});
                    return select;
                }
            };
            ThemifyConstructor['condition'] = {
                id: null,
                reInit(){
                    const wrap=document.getElementById(this.id);
                    wrap.innerHTML='';
                    wrap.parentNode.replaceChild(this.render(TBP.conditions,ThemifyConstructor),wrap);
                },
                includeRender(vals, self) {
                    const wrap = document.createElement('div'),
                            select = document.createElement('select'),
                            args = {
                                id: 'include',
                                options: {
                                    'in': _tbp_app.include,
                                    'ex': _tbp_app.exclude
                                }
                            };
                    wrap.className = 'tbp_include tbp_inner_block';
                    select.appendChild(self.select.make_options(args,vals['include'], self));
                    select.setAttribute('data-id', args.id);
                    wrap.appendChild(select);
                    return wrap;
                },
                renderSelect(options,selected){
                        const f = document.createDocumentFragment(),
                            select = document.createElement('select'),
                            makeOptions = function(val,label,has_query,def){
                            const opt = document.createElement('option');
                                opt.value = val;
                                opt.textContent = label;
                                if(val===selected || (def===val && TBP.id===null)){
                                    opt.selected = true;
                                }
                                if(has_query!==undefined){
                                    opt.setAttribute('data-hasQuery',has_query?1:0);
                                }
                                return opt;
                        };
                    for (let i in options) {
                        if(i==='optgroup'){
                            for(let j=0,len=options[i].length;j<len;++j){
                                let group = document.createElement('optgroup'),
                                    groupF = document.createDocumentFragment();
                                group.label = options[i][j]['label'];
                                group.setAttribute('data-id', options[i][j].id);
                                for (let k in options[i][j]['options']) {
                                    let item=options[i][j]['options'][k];
                                    groupF.appendChild(makeOptions(k, item['label']!==undefined?item['label']:item,item['has_query'],options[i][j]['selected']));
                                }
                                group.appendChild(groupF);
                                f.appendChild(group);
                            }
                        }
                        else{
                            f.appendChild(makeOptions(i,options[i].label,options[i]['has_query'],options[i]['selected']));
                        }
                    }
                    select.appendChild(f);
                    return select;
                },
                renderGeneral(options, vals,index) {
                    const wrap = document.createDocumentFragment(),
                        key='general',
                        t = this,
                        select = this.renderSelect(options,vals[key]),
                        selectChange=function(select){
                            select.addEventListener('change', function (e) {
                                e.stopPropagation();
                                const opt = TBP.conditions['options'][TBP.type][this.value],
                                    p=this.closest('.selectwrapper'),
                                    item=this.options[this.selectedIndex];
								let	isQury=item.getAttribute('data-hasQuery')=='1'?true:null,
                                    next=p.nextSibling;
                                    if(next!==null){
                                        const queryNext = next.nextSibling;
                                        next.parentNode.removeChild(next);
                                        next=null;
                                        if(queryNext!==null){
                                            queryNext.parentNode.removeChild(queryNext);
                                        }
                                    }
                                if(opt!==undefined){
                                    if(opt['options']!==undefined){
                                        const query = t.renderSelect(opt['options'],vals['query']);
                                            selectChange(query);
                                            p.parentNode.insertBefore(t.addSelectWrapper(query, 'query'), next);
                                            if(vals['query']!==undefined ){
                                                Themify.triggerEvent(query,'change');
                                            }
                                    }
                                }
                                else if(isQury===null && !item.hasAttribute('data-hasQuery')){
                                    const group=item.parentNode,
                                        id=group.nodeName==='OPTGROUP'?group.getAttribute('data-id'):false;
                                        isQury=id && 'all_'+id!==this.value;
                                }
                                if(isQury===true){
                                    p.parentNode.insertBefore( t.renderSinlgeItems(vals['detail'],index), next);
                                }
                            }, {passive: true});
                        };
                        selectChange(select);
                        wrap.appendChild(this.addSelectWrapper(select, key));
                        Themify.triggerEvent(select,'change');
                    return wrap;
                },
                renderSinlgeItems(vals, index) {
                    
                    const template = document.getElementById('tmpl-tbp_pagination'),
						wrap = document.createElement('div'),
						th = this;
                    wrap.className = 'tbp_inner_block selectwrapper tbp_pagination_wrapper';
                    wrap.appendChild(template.content.cloneNode(true));
                    const checkbox = wrap.getElementsByClassName('tbp_pagination_all')[0],
                            header = wrap.getElementsByClassName('tbp_pagination_header')[0],
                            onChange = function (el, load) {
                                if (!el.checked) {
                                    if (load === true) {
                                        th.loadData(wrap);
                                    }
                                    header.textContent = header.getAttribute('data-select');
                                }
                                else {
                                    header.textContent = header.getAttribute('data-all');
                                }
                            };
                    header.addEventListener('click', function (e) {
                        e.preventDefault();
                        const p = this.parentNode,
                                close = function () {
                                    if (checkbox.checked !== true) {
                                        th.saveCheckboxes(wrap);
                                    }
                                    p.classList.remove('tbp_pagination_active');
                                    document.removeEventListener('click', click, {passive: true});
                                },
                                click = function (e) {
                                    if (!wrap.contains(e.target)) {
                                        close();
                                    }
                                };
                        if (p.classList.contains('tbp_pagination_active')) {
                            close();
                        }
                        else {
                            p.classList.add('tbp_pagination_active');
                            if (checkbox.checked === false && !checkbox.hasAttribute('done')) {
                                checkbox.setAttribute('done', true);
                                th.loadData(wrap);
                            }
                            document.addEventListener('click', click, {passive: true});
                        }
                    });

                    checkbox.addEventListener('change', function (e) {
                        e.stopPropagation();
                        onChange(this, true);
                    }, {passive: true});  
                    if (vals !== undefined) {
                        checkbox.checked = false;
                        onChange(checkbox, null);
                        const repeat = wrap.closest('.tbp_condition_repeat');
                        if(repeat!==null){
                            index = repeat.getAttribute('data-index');
                        }
                        checkedItems[index] = vals;
                    }
                    return wrap;
                },
                loadData(wrap, page, search, callback) {
                    let theSelected = wrap ;
                    while (theSelected !== null) {
                        theSelected = theSelected.previousElementSibling;
                        if (theSelected.classList.contains('tbp_block_item') && theSelected.offsetParent !== null) {
                            break;
                        }
                    }
                    if (theSelected !== null) {
                        page = parseInt(page);
                        if (!page) {
                            page = 1;
                        }
                        const res = wrap.getElementsByClassName('tbp_pagination_result_wrap')[0],
								self = this,
                                type = theSelected.getElementsByTagName('select')[0].value,
                                finish = function (vals) {
                                    const wrapResult = document.createElement('div'),
                                            f = document.createDocumentFragment(),
                                            data = vals.data,
                                            index = wrap.closest('.tbp_condition_repeat').getAttribute('data-index'),
                                            count = vals.count,
                                            limit = vals.limit;
                                    res.innerHTML = '';  
                                    wrapResult.className = 'tbp_pagination_result tf_scrollbar';
                                    for (let i in data) {
                                        let label = document.createElement('label'),
                                                input = document.createElement('input');
                                        input.type = 'checkbox';
                                        input.value = i;
                                        input.name = type;
                                        if (checkedItems[index] !== undefined && checkedItems[index][i] !== undefined) {
                                            input.checked = true;
                                        }
                                        label.appendChild(input);
                                        label.insertAdjacentHTML('beforeend',data[i]);
                                        f.appendChild(label);
                                    }
                                    wrapResult.appendChild(f);
                                    res.appendChild(wrapResult);
                                    if (count > limit) {
                                        const tbp_pagination_list = document.createElement('div'),
                                                pf = document.createDocumentFragment();
                                        tbp_pagination_list.className = 'tbp_pagination_list';
                                        for (let i = 1, n = Math.ceil(count / limit); i <= n; ++i) {
                                            let link = document.createElement('a');
                                            link.href = '#';
                                            link.className = 'page_numbers';
                                            link.textContent = i;
                                            if (page === i) {
                                                link.className += ' current';
                                            }
                                            link.setAttribute('data-number', i);
                                            pf.appendChild(link);
                                        }
                                        tbp_pagination_list.appendChild(pf);
                                        tbp_pagination_list.addEventListener('click', function (e) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            const p = e.target.getAttribute('data-number');
                                            self.saveCheckboxes(wrap);
                                            if (p) {
                                                self.loadData(wrap, p, search, callback);
                                            }
                                        });
                                        res.appendChild(tbp_pagination_list);
                                    }
                                    const searchInput=wrapResult.closest('.tbp_pagination_search').getElementsByClassName('tbp_pagination_search_input')[0];
                                    if(searchInput!==undefined && search === undefined){
                                        searchInput.addEventListener('search', searchItem);
                                        searchInput.addEventListener('input', searchItem);
                                    }
                                    if (callback) {
                                        callback(vals, res, page);
                                    }
                                },
                                searchItem = function (e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    search = e.target.value;
                                    self.isSearching = true;
                                    self.saveCheckboxes(wrap);
                                    if (search.length > 1) {
                                        self.loadData(wrap, 1, search, callback);
                                    } else {
                                        cache[type] = {};
                                        self.loadData(wrap, 1, null, callback);
                                    }
                                };
                        if (cache[type] === undefined || cache[type][page] === undefined || self.isSearching) {
                            $.ajax({
                                type: 'POST',
                                url: themifyBuilder.ajaxurl,
                                dataType: 'json',
                                beforeSend() {
                                    res.classList.add('tb_busy');
                                },
                                data: {
                                    action: 'tbp_load_data',
                                    p: page,
                                    s: search,
                                    type: type,
                                    tb_load_nonce: themifyBuilder.tb_load_nonce
                                },
                                complete() {
                                    res.classList.remove('tb_busy');
                                },
                                success(resp) {
                                    if (cache[type] === undefined) {
                                        cache[type] = {};
                                    }
                                    cache[type][page] = resp;
                                    if (resp && resp.count > 0) {
                                        finish(resp);
                                    }
                                    self.isSearching = false;
                                }
                            });
                        }
                        else if (cache[type][page].count > 0) {
                            finish(cache[type][page]);
                        }
                    }
                },
                saveCheckboxes(wrap) {
                    const checkboxes = wrap.getElementsByTagName('input'),
                            index = wrap.closest('.tbp_condition_repeat').getAttribute('data-index');
                    if (checkedItems[index] === undefined) {
                        checkedItems[index] = {};
                    }
                    for (let i = checkboxes.length - 1; i > -1; --i) {
                        let v = checkboxes[i].value;
                        if (checkboxes[i].checked === true) {
                            checkedItems[index][v] = true;
                        }
                        else if (checkedItems[index][v] !== undefined) {
                            delete checkedItems[index][v];
                        }
                    }
                },
                renderRepeat(options, index, vals, self) {
                    const f = document.createDocumentFragment(),
                        repeat = document.createElement('div'),
                        repeatInner = document.createElement('div'),
                        deleteBtn = document.createElement('a');
                        repeat.className = 'tbp_condition_repeat';
                        repeatInner.className = 'tbp_condition_repeat_inner';
                        index=index !== null ? index : this.setIndex();
                        repeat.setAttribute('data-index', index);
                        deleteBtn.className = 'ti-close tbp_delete_repeater';
                        deleteBtn.href = '#';
                        repeatInner.appendChild(this.includeRender(vals, self));
                        repeatInner.appendChild(this.renderGeneral(options, vals, index));
                        f.appendChild(repeatInner);
                        f.appendChild(deleteBtn);
                        repeat.appendChild(f);

                    deleteBtn.addEventListener('click', function (e) {
                        e.preventDefault();
                        const index = repeat.closest('.tbp_condition_repeat').dataset['index'];
                        if (checkedItems[index] !== undefined) {
                            delete checkedItems[index];
                        }
                        repeat.parentNode.removeChild(repeat);
                    });
                    return repeat;
                },
                render(data, self) {
                    this.id = data.id;
                    const wrap = document.createElement('div'),
                        add = document.createElement('a'),
                        type=TBP.type;
                    wrap.className = 'tb_lb_option tbp_condition_wrap';
                    wrap.id = data.id;
                    if(TBP.conditions===null){
                        TBP.conditions=data;
                    }
                    if (data['options'][type] !== undefined) {
                        add.className = 'add_new tf_plus_icon';
                        add.href = '#';
                        add.textContent = _tbp_app.add_conition;
                        const f = document.createDocumentFragment();
						let values = ThemifyConstructor.values[data.id];
                        if (values === undefined || values.length === 0) {
                            values = [];
                            values[0] = {};
                        }
                        for (let i = 0, len = values.length; i < len; ++i) {
                            f.appendChild(this.renderRepeat(data['options'][type], i, values[i], self));
                        }
                        wrap.appendChild(f);
                        add.addEventListener('click', function (e) {
                            e.preventDefault();
                            const repeat = this.renderRepeat(data['options'][type], null, {}, self);
                            e.currentTarget.before(repeat);
                        }.bind(this));
                        wrap.appendChild(add);
                    }
                    return wrap;
                },
                setIndex() {
                    const repeats = _this.lightboxContainer.getElementsByClassName('tbp_condition_repeat');
					let max = repeats[0] !== undefined ? (parseInt(repeats[0].getAttribute('data-index'))) : 0;
                    for (let i = repeats.length - 1; i > 0; --i) {
                        let index = parseInt(repeats[i].getAttribute('data-index'));
                        if (max < index) {
                            max = index;
                        }
                    }
                    return ++max;
                },
                addSelectWrapper(select, key) {
                    const wrap = document.createElement('div');
                    wrap.className = 'selectwrapper tbp_inner_block';
                    if (key !== undefined) {
                        wrap.className += ' tbp_block_item tbp_block_' + key;
                        select.setAttribute('data-id', key);
                    }
                    wrap.appendChild(select);
                    return wrap;
                }
            };
            editBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                document.body.click();
                const items = _this.lightbox.getElementsByClassName('tb_lb_option'),
                        result = {};
                for (let i = items.length - 1; i > -1; --i) {
                    if (items[i].classList.contains('tbp_condition_wrap')) {
                        let conditions = items[i].getElementsByClassName('tbp_condition_repeat'),
                                conditionsData = [];
                        for (let j = 0, len = conditions.length; j < len; ++j) {
                            conditionsData[j] = {};
                            let conditionItems = conditions[j].getElementsByClassName('tbp_inner_block');
                            for (let k = conditionItems.length - 1; k > -1; --k) {
                                if (conditionItems[k].offsetParent !== null) {
                                    let cl = conditionItems[k].classList;
                                    if (cl.contains('tbp_include')) {
                                        let select = conditionItems[k].getElementsByTagName('select')[0];
                                        if (select.value !== 'in') {
                                            conditionsData[j][select.getAttribute('data-id')] = select.value;
                                        }
                                    }
                                    else if (cl.contains('tbp_pagination_wrapper')) {
                                        if (conditionItems[k].getElementsByClassName('tbp_pagination_all')[0].checked !== true) {
                                            let index = conditions[j].getAttribute('data-index');
                                            if (checkedItems[index] !== undefined) {
                                                conditionsData[j]['detail'] = checkedItems[index];
                                            }
                                        }
                                    }
                                    else if (cl.contains('tbp_block_item')) {
                                        let select = conditionItems[k].getElementsByTagName('select')[0];
                                        conditionsData[j][select.getAttribute('data-id')] = select.value;
                                    }
                                }
                            }
                        }
                        result[items[i].getAttribute('id')] = conditionsData;
                    }
                    else if (items[i].offsetParent !== null || items[i].type === 'hidden' || items[i].classList.contains('tb_uploader_input')) {
                        result[items[i].getAttribute('id')] = items[i].value.trim();
                    }
                }
                if (_this.id === null) {
                    setPredesgnedList(result);
                }
                else {
                    saveLightbox(false, _this.id, result);
                }
            });
        },
        edit(e) {
            e.preventDefault();
            e.stopPropagation();
            this.id = e.currentTarget.getAttribute('data-post-id');
            if (this.id) {
                if (this.isLoaded === null) {
                    Themify.LoadCss(themifyBuilder.builderToolbarUrl, themifyBuilder.v);
                    Themify.LoadCss(themifyBuilder.builderCombineUrl, themifyBuilder.v);
                }
                if (document.body.classList.contains('tbp_loading')) {
                    return;
                }
                const _this = this;
                $.ajax({
                    type: 'POST',
                    url: themifyBuilder.ajaxurl,
                    dataType: 'json',
                    beforeSend() {
                        _this.showLoader(true);
                    },
                    data: {
                        id: this.id,
                        action: this.pageId + '_get_item',
                        tb_load_nonce: themifyBuilder.tb_load_nonce
                    },
                    success(resp) {
                        if (resp) {
                            _this.run(_tbp_app.edit_template, resp);
                            _this.showLoader();
                        }
                    }
                });
            }
            else {
                this.run(_tbp_app.add_template, {});
            }
        },
        close(e) {
            e.preventDefault();
            e.stopPropagation();
            if(this.isSaved===true){
                window.location.reload();
            }
            else{
                this.lightbox.classList.remove('tbp_lightbox');
                while (this.lightboxContainer.firstChild !== null) {
                    this.lightboxContainer.removeChild(this.lightboxContainer.firstChild);
                }
            }
        },
        run(title, data) {
            document.body.classList.remove('tbp_step_2');
            if (title === undefined) {
                title = '';
            }
            const self = this,
                    callback = function () {
                        ThemifyConstructor.values = data;
                        checkedItems={};
                        ThemifyConstructor.label = self.labels;
                        const args = [
                            {
                                type: 'group',
                                options: self.options,
                                wrap_class: 'tb_options_tab_content'
                            }
                        ];
                        if ('tbp_no_theme_activated' === self.options[0].id && !self.id) {
                            args[0].options = self.options.slice(0, 1);

                            self.lightbox.getElementsByClassName('builder_button_edit')[0].addEventListener('click', function (e) {
                                if (!self.id) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    window.location.href = self.options[0].theme_page_url;
                                }
                            }, {once: true});
                        } 
						else if ('tbp_no_theme_activated' === args[0].options[0].id){
							args[0].options = self.options.slice(1);
                        }
                        self.createCustomTypes();
                  
                        self.lightbox.getElementsByClassName('tbp_lightbox_title')[0].innerHTML = title;
                        self.lightboxContainer.appendChild(ThemifyConstructor.create(args));
                        ThemifyConstructor.callbacks();
                        self.lightbox.classList.add('tbp_lightbox');
                        self.lightboxContainer.classList.add( 'tf_scrollbar' );
                    };
            if (this.isLoaded === null) {
                this.isLoaded = true;
                Themify.LoadCss(themifyBuilder.builderToolbarUrl, themifyBuilder.v);
                Themify.LoadCss(themifyBuilder.builderCombineUrl, themifyBuilder.v);
                Themify.LoadAsync(themifyBuilder.tbAppUrl, function() {
					Themify.LoadAsync(themifyBuilder.constructorUrl, callback, themifyBuilder.v, null, function () {
						return typeof ThemifyConstructor !== 'undefined';
					});
				}, themifyBuilder.v, null, function () {
                    return typeof tb_app !== 'undefined';
                });
            }
            else {
                callback();
            }
        },
        pointerInit() {
            if ('undefined' !== typeof _tbp_pointers) {
                for (let i = _tbp_pointers.pointers.length - 1; i >-1; --i) {
                    this.pointerOpen(_tbp_pointers.pointers[i]);
                }
            }
        },
        pointerOpen(pointer) {
            const pointers= $(pointer.target);
            if (pointers.length===0)
                return;

            const options = $.extend(pointer.options, {
                close() {
                    if ( pointer.remember_dismiss ) {
                        $.post(ajaxurl, {
                            pointer: pointer.pointer_id,
                            action: 'dismiss-wp-pointer'
                        });
                    }
                }
            });

            pointers.pointer(options).pointer('open');
        }
    };
	if (document.readyState === 'complete') {
                TBP.init();
	} else {
		window.addEventListener('load', function(){
			TBP.init();
		}, {once:true, passive:true});
	}

})(jQuery,document, Themify);
