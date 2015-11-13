var ready = require('modules/ready');
var array_merge = require('modules/array-merge');

module.exports = function(){
	if(!window.PLUGIN_CONFIG_poiauthor)
		return;

	
	var cache = {},
		config = {
			process_url : ''
		};

	config = array_merge(config, window.PLUGIN_CONFIG_poiauthor);
	
	function I(e){
		return document.getElementById(e);
	}

	ready(init);
	function init(){
		cache.users = {};
		cache.$search = I('poiauthor-search');
		cache.$author_id = I('poiauthor-id');
		cache.$datalist = I('poiauthor-search-datalist');
		cache.$loader = I('poiauthor-spinner');
		cache.last_value = '';

		if(!cache.$search || !cache.$author_id)
			return;
		
		bind();
	}
	function bind(){
		cache.$search.addEventListener('keyup', event_search_keyup);
		cache.$search.addEventListener('blur', event_search_blur);
		cache.$author_id.addEventListener('blur', event_author_id_blur);
		
	}
	function event_search_keyup(){
		if(cache.last_value == this.value)
			return;

		ajax();
		cache.last_value = this.value;
		
	}
	function event_search_blur(){
		if(this.value === '')
			return;
		if(!cache.users)
			return;
		if(!cache.users[this.value])
			return;
		cache.$author_id.value = cache.users[this.value].id;
	}
	function event_author_id_blur(){
		var user_id = this.value;
		if(user_id === '')
			return;
		/** search in cache */
		var in_cache = false;
		if(cache.users){
			for(var i in cache.users){
				if(cache.users[i].id == user_id){
					cache.$search.value = i;
					in_cache = true;
				}
			}
		}
		/** search from server */
		if(!in_cache){
			tip('show');
			var xhr = new XMLHttpRequest();
			xhr.open('get', config.process_url + '&type=get-user&id=' + user_id);
			xhr.send();
			xhr.onload = function(){
				if(xhr.status >= 200 && xhr.status < 400){
					var data;
					try{data=JSON.parse(xhr.responseText)}catch(err){data=xhr.responseText}
					
					if(data.status === 'success'){
						cache.$search.value = data.display_name;
						cache.users[data.display_name] = {
							display_name : data.display_name,
							url : data.url,
							id : user_id
						};
					}else if(data.status === 'error'){
						
					}else{
						
					}
				}else{
					
				}
				tip('hide');
			};
			xhr.onerror = function(){
				tip('hide');
			}
		}
	}
	function create_datalist(users){
		var $tmp_container = document.createElement('datalist');
		if(!cache.users)
			cache.users = {};
		
		for(var i = 0, len = users.length; i < len; i++){
			var user = users[i];
			/** set to cache */
			if(!cache.users[user.display_name])
				cache.users[user.display_name] = user;
			var $opt = document.createElement('option');
			$opt.value = user.display_name;
			$opt.innerHTML = user.id;
			$tmp_container.appendChild($opt);
		}
		cache.$datalist.innerHTML = $tmp_container.innerHTML;
	}
	function ajax(){
		/** tip */
		tip('show');
		var xhr = new XMLHttpRequest();
		xhr.open('get', config.process_url + '&type=search-users&user=' + cache.$search.value);
		xhr.send();
		xhr.onload = function(){
			if(xhr.status >= 200 && xhr.status < 400){
				var data;
				try{data=JSON.parse(xhr.responseText)}catch(err){data=xhr.responseText}
				
				if(data.status === 'success'){
					create_datalist(data.users);
				}else if(data.status === 'error'){
					
				}else{
					
				}
			}else{
				
			}
			tip('hide');
		};
		xhr.onerror = function(){
			tip('hide');
		}
	}
	function tip(t){
		if(t === 'show'){
			cache.$loader.classList.add('is-active');
		}else{
			cache.$loader.classList.remove('is-active');
		}
	}
}