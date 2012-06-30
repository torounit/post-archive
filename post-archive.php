<?php
/**
 * 
 * @package Post Archive
 * @version 0.2
 * 
 */
/*
Plugin Name: Post Archive
Description: Add Post Archive
Author: Toro_Unit
Version: 0.2
Author URI: http://torounit.com/
*/

register_activation_hook(__FILE__, 'flush_rewrite_rules');

class Post_Archive {
	
	public $deb;
	public $label;
	public $pre_str;

	public function  __construct () {

		$this->pre_str =  trim( preg_replace( "/%[a-z,_]*%/" ,"",get_option("permalink_structure")) ,'/' );

		add_action( 'init', array(&$this,'set_label'));

		add_action( 'wp_loaded', array(&$this,'set_archive_rewrite'),99);
		add_action( 'parse_query', array(&$this,'set_conditional'));

		add_filter( 'post_type_archive_title', array(&$this,'post_archive_title'));
		add_action( 'admin_menu', array(&$this,'change_post_label'));

		add_action( 'admin_init', array(&$this,'add_setting_field'));
		add_filter( 'admin_init', array(&$this,'add_whitelist') );

	}
	
	public function set_label(){
		if(!$this->label = get_option('post_label')){
			$this->label = __('Posts');
		}


	}
	
	public function set_archive_rewrite() {
		if($this->pre_str){
			add_rewrite_rule( $this->pre_str.'/?$', 'index.php?post_type=post', 'top' );
			add_rewrite_rule( $this->pre_str.'/page/?([0-9]{1,})/?$', 'index.php?post_type=post&paged=$matches[1]', 'top' );				
		}

	}
	
	public function set_conditional($arr){
		if($this->pre_str && get_query_var('post_type') =='post'):
			$arr->is_home = false;
			$arr->is_archive = true;
			$arr->is_post_type_archive = true;
		endif;
		$this->deb = $arr;
		return $arr;
	}
	
	public function change_post_label() {
	    global $menu;
		$menu[5][0] = $this->label;
	}

	public function post_archive_title($title){
		if(get_query_var('post_type') =='post'):
			$title = $this->label;
		endif;
		return $title;
	}

	public function add_setting_field(){
		add_settings_field('post_label', __('Post label'), array(&$this,'add_setting_field_callback'), 'writing', 'default', array( 'label_for' => 'post_label' ));
	}
	
	public function add_whitelist() {
		register_setting( 'writing', 'post_label' );
	}

	public function add_setting_field_callback($args){
		?>
	<input type="text" class="text" value="<?php echo $this->label;?>" id="post_label" name="post_label">	
	<?php
	}

}

 
new Post_Archive;
