(function (Themify) {
    'use strict';
    /*
     * 
     * Add To Cart Module Quantity Change
     */
    function tbp_cart_icon_module_quantity(){
		let btn=this.closest('.tb_pro_add_to_cart');
		if(btn){
			btn=btn.getElementsByClassName('button')[0];
			if(btn){
				btn.setAttribute('data-quantity',parseInt(this.value));
			}
		}
    }
    
    /*
     * 
     * Add To Cart Module
     */
    function tbp_add_to_cart_module(el,type,isLazy){
        if(Themify.is_builder_active){
            const items = Themify.selectWithParent('single_add_to_cart_button',el);
            for(let i=items.length-1;i>-1;--i){
                items[i].classList.add('disabled');
            }  
        }
    }

    /*
     * Cart Icon Module
     * */
    function tbp_cart_icon_module(el,type,isLazy) {
		if(isLazy===true && !el[0].classList.contains('module-cart-icon')){
			return;
		}
		const mods=Themify.selectWithParent('tbp_slide_cart',el);
		if(mods[0]){
			// Slide cart icon
			for(let i=mods.length-1;i>-1;--i){
			   Themify.sideMenu(mods[i].previousElementSibling,{
					close: '#' + mods[i].getElementsByClassName('tbp_cart_icon_close')[0].getAttribute('id')
				});
			}
			// Show & Hide cart icon on add to cart event
			const firstItem=mods[0].previousElementSibling;
			Themify.body.on('added_to_cart', function (e) {
				firstItem.click();
			});
		}
    }

	/*
     * Search Form Module
     * */
	function tbp_search_form_module(el,type,isLazy) {
		if (!el || (isLazy === true && ! el[0].classList.contains( 'module-search-form' )) ){
			return;
		}
		const item=el[0],
			ajax_type = item.dataset['ajax'];
		if(''===ajax_type){
			return;
		}
		Themify.ajaxSearch({
			type:ajax_type,
			both:ajax_type==='dropdown',
			el:'overlay'===ajax_type?item.getElementsByClassName('tbp_icon_search')[0]:item.querySelector('input[name="s"]'),
			container:'overlay'===ajax_type?item.getElementsByClassName('search-lightbox-wrap')[0]:item,
			post_type:item.dataset['postType']
		});
	}

	function Run(){
        Themify.body.on('change','.tb_pro_add_to_cart input',tbp_cart_icon_module_quantity)
		.on('click', '.tbp_remove_from_cart_button', function (e) {// remove item ajax
			e.preventDefault();
			this.classList.remove('tf_close');
			this.classList.add('tf_loader');
		});
		Themify.on( 'builder_load_module_partial', function(el,type,isLazy){
			tbp_add_to_cart_module(el,type,isLazy);
			tbp_cart_icon_module(el,type,isLazy);
			tbp_search_form_module(el,type,isLazy);
		} );
	}
	if(window.loaded){
		Run();
	}
	else{
		window.addEventListener('load', Run, {once:true, passive:true});
	}

})(Themify);
