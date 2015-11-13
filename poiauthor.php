<?php
/*
Plugin Name: Poi author meta box
Plugin URI: http://inn-studio.com/poiauthor
Description: A better performance author meta box instead of the build-in of WordPress.
Author: INN STUDIO
Author URI: http://inn-studio.com
Version: 1.0.0
Text Domain: poiauthor
Domain Path: /languages
*/
namespace poiauthor;

class poiauthor{
	private static function tdomain(){
		\load_plugin_textdomain(
			__NAMESPACE__, 
			false, 
			dirname(\plugin_basename(__FILE__)). '/languages/'
		);
	}
	private static function update(){
		if(!class_exists('inc\updater')){
			include __DIR__ . '/inc/update.php';
		}
		$updater = new inc\updater();
		$updater->name = self::get_header_translate('plugin_name');
		$updater->basename = basename(__DIR__);
		$updater->dir = __NAMESPACE__;
		$updater->filename = basename(__FILE__);
		$updater->slug = __NAMESPACE__;
		$updater->checker_url = __('http://update.inn-studio.com') . '/?action=get_update&slug=' . __NAMESPACE__;
		$updater->init();
	}
	public static function init(){
		
		include __DIR__ . '/core/core-functions.php';
		include __DIR__ . '/core/core-options.php';
		include __DIR__ . '/core/core-features.php';

		
		\add_action('admin_enqueue_scripts', __CLASS__ . '::backend_enqueue_scripts', 999);
		\add_action('admin_enqueue_scripts', __CLASS__ . '::post_new_enqueue_scripts', 999);
		
		self::tdomain();
		
		if(self::is_admin()){
			/** update */
			self::update();
			
			/** assets */
			\add_action('admin_footer', __CLASS__ . '::admin_footer', 1);

			\add_filter('wp_dropdown_users', __CLASS__ . '::wp_dropdown_users');
			
			/** remove default metabox */
			\add_action( 'admin_menu', function(){
				foreach(['post','page','attachment'] as $v)
					\remove_meta_box('authordiv', $v, 'normal');
			});
		}
		
		/** ajax */
		\add_action('wp_ajax_' . __NAMESPACE__, __CLASS__ . '::process');
		
		/** settings */
		\add_action('plguin_base_settings_' . __NAMESPACE__, __CLASS__ . '::display_backend_base_settings');
		\add_action('plguin_help_settings_' . __NAMESPACE__, __CLASS__ . '::display_backend_help_setting');

		/** add meta box */
		\add_action('admin_init', __CLASS__ . '::meta_box_add');
	}
	public static function is_admin(){
		static $cache = null;
		if($cache === null)
			$cache = (bool)is_admin();
		return $cache;
	}
	public static function get_header_translate($key = null){
		$trs = [
			'plugin_name' => __('Poi author meta box'),
			'plugin_uri' => __('http://inn-studio.com/poi-user-meta-box'),
			'description' => __('A better performance author meta box instead of the build-in of WordPress.'),
			'author_uri' => __('http://inn-studio.com'),
		];
		if($key)
			return isset($trs[$key]) ? $trs[$key] : false;
		return $trs;
	}
	private static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = plugin_options::get_options();
		if($key)
			return isset($caches[$key]) ? $caches[$key] : false;
		return $caches;
	}
	public static function meta_box_add(){
		$screens = [
			'post',
			'page',
			'attachment'
		];
		foreach($screens as $screen){
			\add_meta_box(
				__NAMESPACE__,
				plugin_features::get_plugin_data('Name'),
				__CLASS__ . '::meta_box_display',
				$screen,
				'side'
			);
		}
	}
	public static function meta_box_display($post){
		global $user_ID;
		$author_id = empty($post->ID) ? $user_ID : $post->post_author;
		$author_display_name = \esc_html(get_the_author_meta('display_name',$author_id));
		?>
		<div id="<?= __NAMESPACE__;?>-container">
			<p class="description"><?= __('You can type the author name for searching author ID.');?></p>
			<p>
				<label for="<?= __NAMESPACE__;?>-search"><?= __('Type author name to seach');?> <span id="<?= __NAMESPACE__;?>-spinner" class="spinner"></span></label>
				<input 
					type="text" 
					class="widefat" 
					id="<?= __NAMESPACE__;?>-search" 
					value="<?= $author_display_name;?>" 
					placeholder="<?= __('Type author name to seach');?>" 
					list="<?= __NAMESPACE__;?>-search-datalist" 
					required 
					
				>
				<datalist id="<?= __NAMESPACE__;?>-search-datalist"></datalist>
			</p>
			<p>
				<label for="<?= __NAMESPACE__;?>-id"><?= __('Author ID');?></label>
				<input type="number" id="<?= __NAMESPACE__;?>-id" name="post_author_override" value="<?= $author_id;?>" class="widefat" placeholder="<?= __('Author ID');?>">
			</p>
		</div>
		<?php
	}
	
	public static function process(){
		$output = [];
		$type = isset($_GET['type']) && is_string($_GET['type']) ? $_GET['type'] : null;

		switch($type){
			case 'search-users':
				/** check is editor */
				if(!\current_user_can('edit_pages')){
					die(plugin_functions::json_format([
						'status' => 'error',
						'code' => 'invaild_permission',
						'msg' => __('Sorry, you can not search users.'),
					]));
				}
				$kw = isset($_GET['user']) && is_string($_GET['user']) ? $_GET['user'] : false;
				if(!$kw){
					die(plugin_functions::json_format([
						'status' => 'error',
						'code' => 'invaild_user_display_name',
						'msg' => __('Invaild user display name.'),
					]));
				}

				/** search start */
				$query = new \WP_User_Query([
					'search' => $kw . '*',
					'search_columns' => [
						'user_email',
						'display_name',
						'user_login'
					],
					'fields' => [
						'ID',
						'display_name',
						'user_url'
					],
					'orderby' => 'ID'
				]);
				$users = $query->get_results();
				if(empty($users)){
					die(plugin_functions::json_format([
						'status' => 'error',
						'code' => 'no_match',
						'msg' => __('Nothing to match.'),
					]));
				}
				foreach($users as $user){
					$output['users'][] = [
						'display_name' => \esc_html($user->display_name),
						'id' => $user->ID,
						'url' => \get_author_posts_url($user->ID),
					];
				}
				$output['status'] = 'success';
				
				break;
			case 'get-user':
				$user_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : false;
				if(!$user_id){
					die(plugin_functions::json_format([
						'status' => 'error',
						'code' => 'invaild_user_id',
						'msg' => __('Invaild user ID.'),
					]));
				}
				$user = get_user_by('id',$user_id);
				if(!$user){
					die(plugin_functions::json_format([
						'status' => 'error',
						'code' => 'user_not_exists',
						'msg' => __('The user does not exist.'),
					]));
				}
				die(plugin_functions::json_format([
					'status' => 'success',
					'display_name' => \esc_html($user->display_name),
					'url' => \get_author_posts_url($user_id),
				]));
				break;
		}
		die(plugin_functions::json_format($output));
	}
	
	public static function display_backend_help_setting(){
		$plugin_data = plugin_features::get_plugin_data();
		?>
		<fieldset>
			<legend><?= __('Plugin Information');?></legend>
			<table class="form-table">
				<tbody>
					<tr>
						<th><?= __('Plugin name: ');?></th>
						<td>
							<strong><?= $plugin_data['Name'];?></strong>
						</td>
					</tr>
					<tr>
						<th><?= __('Plugin version: ');?></th>
						<td>
							<?= $plugin_data['Version'];?>
						</td>
					</tr>
					<tr>
						<th><?= __('Plugin description: ');?></th>
						<td>
							<?= $plugin_data['Description'];?>
						</td>
					</tr>
					<tr>
						<th><?= __('Plugin home page: ');?></th>
						<td>
							<a href="<?= $plugin_data['PluginURI'];?>" target="_blank"><?= $plugin_data['PluginURI'];?></a>
						</td>
					</tr>
					<tr>
						<th><?= __('Author home page: ');?></th>
						<td>
							<a href="<?= $plugin_data['AuthorURI'];?>" target="_blank"><?= $plugin_data['AuthorURI'];?></a>
						</td>
					</tr>
					<tr>
						<th scope="row"><?= __('Feedback and technical support: ');?></th>
						<td>
							<p><?= __('E-Mail: ');?><a href="mailto:kmvan.com@gmail.com">kmvan.com@gmail.com</a></p>
							<p>
								<?= __('QQ (for Chinese users): ');?><a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=272778765&site=qq&menu=yes">272778765</a>
							</p>
							<p>
								<?= __('QQ Group (for Chinese users):');?>
								<a href="http://wp.qq.com/wpa/qunwpa?idkey=d8c2be0e6c2e4b7dd2c0ff08d6198b618156d2357d12ab5dfbf6e5872f34a499" target="_blank">170306005</a>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?= __('Donate a coffee: ');?></th>
						<td>
							<p>
								<!-- paypal -->
								<a data-item-name="<?= plugin_features::get_plugin_data('Name');?>" id="paypal_donate" href="javascript:;" title="<?= __('Donation by Paypal');?>">
									<img src="//ww2.sinaimg.cn/large/686ee05djw1ella1kv74cj202o011wea.jpg" alt="<?= __('Donation by Paypal');?>" width="96" height="37"/>
								</a>
								<!-- alipay -->
								<a id="alipay_donate" target="_blank" href="http://ww3.sinaimg.cn/mw600/686ee05djw1eihtkzlg6mj216y16ydll.jpg" title="<?= __('Donation by Alipay');?>">
									<img width="96" height="37" src="//ww1.sinaimg.cn/large/686ee05djw1ellabpq9euj202o011dfm.jpg" alt="<?= __('Donation by Alipay');?>"/>
								</a>
								<!-- wechat -->
								<a id="wechat_donate" target="_blank" href="http://ww4.sinaimg.cn/mw600/686ee05djw1exukpkk4fwj20fr0f940r.jpg" title="<?= __('Donation by Wechat');?>">
									<img width="96" height="37" src="//ww3.sinaimg.cn/large/686ee05djw1exul2142tvj202o0113ya.jpg" alt="<?= __('Donation by Wechat');?>"/>
								</a>
							</p>
						</td>
					</tr>
				</tbody>
			</table>		
		</fieldset>
		<?php
	}
	public static function display_backend_base_settings(){
		?>
		<fieldset>
			<legend>none</legend>
			<p class="description">none</p>
		</fieldset>
		<?php
	}
	public static function wp_dropdown_users($html){
		if(\get_current_screen()->base !== 'edit')
			return $html;
		?>
		<select style="display:none;" class="authors poiauthor-author-id" name="post_author"></select>
		<?php
	}
	public static function admin_footer(){
		if(\get_current_screen()->base !== 'post')
			return;
		?>
		<script>
		window.PLUGIN_CONFIG_<?= __NAMESPACE__;?> = <?= json_encode([
			'process_url' => plugin_features::get_process_url([
				'action' => __NAMESPACE__,
				'nonce' => \wp_create_nonce(__NAMESPACE__),
			]),
		]);?>;
		</script>
		<?php
	}
	public static function post_new_enqueue_scripts(){
		if(\get_current_screen()->base !== 'post')
			return;
		/**
		 * js
		 */
		$js = [
			'post-new' => [
				'deps' => [],
				'url' => plugin_features::get_js('post-new-entry'),
			],
			
		];
		foreach($js as $k => $v){
			\wp_enqueue_script(
				$k,
				$v['url'],
				isset($v['deps']) ? $v['deps'] : [],
				plugin_features::get_plugin_data('Version'),
				true
			);
		}
	}
	public static function backend_enqueue_scripts(){
		if(!plugin_options::is_options_page())
			return;
		
		/**
		 * css
		 */
		$css = [
			'backend' => [
				'url' =>  plugin_features::get_css('backend'),
			],
		];
		foreach($css as $k => $v){
			\wp_enqueue_style(
				$k,
				$v['url'],
				isset($v['deps']) ? $v['deps'] : [],
				plugin_features::get_plugin_data('Version')
			);
		}
		/**
		 * js
		 */
		$js = [
			'backend' => [
				'deps' => [],
				'url' => plugin_features::get_js('backend-entry'),
			],
			
		];
		foreach($js as $k => $v){
			\wp_enqueue_script(
				$k,
				$v['url'],
				isset($v['deps']) ? $v['deps'] : [],
				plugin_features::get_plugin_data('Version'),
				true
			);
		}
	}
}
function __($str){
	return \__($str,__NAMESPACE__);
}
\add_action('plugins_loaded', __NAMESPACE__ . '\poiauthor::init');