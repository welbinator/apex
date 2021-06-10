(function($,document) {
    'use strict';
    const TB_Themes ={
        themes:{},
        overlay:null,
        init(){
            const items = document.getElementsByClassName('tb_more_details');
            for(let i=items.length-1;i>-1;--i){
                items[i].addEventListener('click',this.details.bind(this));
            }
            this.themes=_tbpThemeSettings;
            _tbpThemeSettings=null;
            this.overlay=document.getElementsByClassName('theme-overlay')[0];
            this.activateImportedTheme();
        },
        details(e){
            e.preventDefault();
            e.stopPropagation();
            const id = parseInt(e.currentTarget.getAttribute('data-id')),
                tpl = wp.template('tbp-theme-single'),
                self=this;
			let settings=null,
                next,
                prev;
                for(let i=this.themes.length-1;i>-1;--i){
                    if(this.themes[i].theme_id===id){
                        settings=this.themes[i];
                        next = this.themes[i+1];
                        prev=this.themes[i-1];
                        break;
                    }
                }
                if(settings!==null){
                    settings['next']=next!==undefined;
                    settings['prev']=prev!==undefined;
                    settings['active']?this.overlay.classList.add('active'):this.overlay.classList.remove('active');
                    this.overlay.innerHTML=tpl(settings);
                    let left,
                        right,
                        deleteBtn,
                        closeBtn,
                        editBtn;
					const close = function(e){
                            e.preventDefault();
                            e.stopPropagation();
                            self.overlay.innerHTML='';
                            self.overlay.classList.add('active');
                            removeEvents();
                       },
                       change=function(e){
                            e.preventDefault();
                            e.stopPropagation();
                            if(!this.classList.contains('disabled')){
                                const nextId = this.classList.contains('left')?prev.theme_id:next.theme_id,
                                    items = document.getElementsByClassName('tb_more_details');
                                for(let i=items.length-1;i>-1;--i){
                                    if(items[i].getAttribute('data-id')==nextId){
                                        removeEvents();
                                        items[i].click();
                                        break;
                                    }
                                }
                               
                            }
                       },
                       _delete=function(e){
                            e.stopPropagation();
                            e.preventDefault();
                            if(confirm(_tbp_app.confirmDelete)){
                                window.location.href=this.href;
                            } 
                             
                       },
                        edit=function(e){
                            e.stopPropagation();
                            e.preventDefault();
                            document.getElementsByClassName('tbp_themes')[0].querySelector('.tbp_lightbox_edit[data-post-id="'+this.getAttribute('data-post-id')+'"]').click();
                            close(e);
                        },
                        removeEvents=function(){
                            left.removeEventListener('click',change);
                            right.removeEventListener('click',change);
                            closeBtn.removeEventListener('click',close,{once:true});
                            if(deleteBtn!==undefined){
                                deleteBtn.removeEventListener('click',_delete);
                            }
                            if(editBtn!==undefined){
                                editBtn.removeEventListener('click',edit);
                            }
                       };
                    closeBtn = this.overlay.getElementsByClassName('close')[0];
                    left = this.overlay.getElementsByClassName('left')[0];
                    right = this.overlay.getElementsByClassName('right')[0];
                    deleteBtn = this.overlay.getElementsByClassName('delete-theme')[0];
                    editBtn = this.overlay.getElementsByClassName('tbp_lightbox_edit')[0];
                    if(deleteBtn!==undefined){
                        deleteBtn.addEventListener('click',_delete);
                    }
                    if(editBtn!==undefined){
                        editBtn.addEventListener('click',edit);
                    }
                    closeBtn.addEventListener('click',close,{once:true});
                    left.addEventListener('click',change);
                    right.addEventListener('click',change);
                }
        },
        activateImportedTheme() {
            $( 'body' ).on( 'themify_plupload_selected', function( e, $el, json ){
                if(null!==json.active){
                    if(confirm(json.msg)){
                        window.location.href = json.active;
                    }else{
                        window.location.reload(true);
                    }
                }
            });
        }
    };
    if (document.readyState === 'complete') {
                TB_Themes.init();
	} else {
		window.addEventListener('load', function(){
			TB_Themes.init();
		}, {once:true, passive:true});
	}
})( jQuery,document);
