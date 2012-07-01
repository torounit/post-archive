<?php
/**
 * 
 * @package Post Archive
 * @version 0.3
 * 
 */
/*
Plugin Name: Post Archive
Description: Add Post Archive
Author: Toro_Unit
Version: 0.3
Author URI: http://torounit.com/
*/

register_activation_hook( __FILE__, 'flush_rewrite_rules' );

class Post_Archive {
	
	public $label;
	public $pre_str;

	public function  __construct() {
		add_action( 'init', array( &$this, 'set_label' ) );
		add_action( 'init', array( &$this, 'set_archive_rewrite'), 99 );
		add_action( 'parse_query', array( &$this, 'set_conditional' ) );
		add_filter( 'post_type_archive_title', array( &$this, 'post_archive_title' ) );
		add_action( 'admin_menu', array( &$this, 'change_post_label' ) );
		add_action( 'admin_init', array( &$this, 'add_setting_field' ) );
		add_filter( 'admin_init', array( &$this, 'add_whitelist' ) );
		add_action( 'update_option_permalink_structure', array(&$this, 'update_rules'), 100, 2);
	}

	/**
	 *　パーマリンク構造を読み込み
	 * @since 0.3
	 * 
	 */
	private function get_structure( $structure ) {
		$str = trim( $structure, '/' );
		$str_arr = explode( '/', $str );
		$pre_str = "";
		foreach ($str_arr as $str_peace) {
			if( !preg_match( "/%[a-z,_]*%/", $str_peace ) ) {
				$pre_str .= $str_peace.'/';
			}
		}

		 return rtrim( $pre_str, '/');		
	}

	/**
	 *　投稿のラベルを変更
	 * @since 0.2
	 * 
	 */
	public function set_label() {
		if(!$this->label = get_option( 'post_label' ) ){
			$this->label = __( 'Posts' );
		}
		$this->pre_str = $this->get_structure( get_option( "permalink_structure" ) );
	}
	
	/**
	 *　Rewrite Ruleの登録 アーカイブの追加
	 * @since 0.1
	 * 
	 */
	public function set_archive_rewrite() {
		if( $this->pre_str ) {
			add_rewrite_rule( $this->pre_str.'/?$', 'index.php?post_type=post', 'top' );
			add_rewrite_rule( $this->pre_str.'/page/?([0-9]{1,})/?$', 'index.php?post_type=post&paged=$matches[1]', 'top' );				
		}
	}
	
	/**
	 *　アーカイブページのクエリーの設定
	 * @since 0.1
	 * 
	 */
	public function set_conditional( $arr ) {
		if( get_query_var( 'post_type' ) =='post' ){
			$arr->is_home = false;
			$arr->is_archive = true;
			$arr->is_post_type_archive = true;
		}
		return $arr;
	}
	
	/**
	 *　メニューのラベルの変更
	 * @since 0.2
	 * 
	 */
	public function change_post_label() {
	    global $menu;
		$menu[5][0] = $this->label;
	}

	/**
	 *　投稿のアーカイブのときのwp_title
	 * @since 0.2
	 * 
	 */
	public function post_archive_title( $title ) {
		if( get_query_var( 'post_type' ) =='post' )
			$title = $this->label;
		return $title;
	}

	/**
	 *　管理画面にフィールドを追加
	 * @since 0.2
	 * 
	 */
	public function add_setting_field() {
		add_settings_field( 'post_label', __( 'Post label' ), array( &$this, 'add_setting_field_callback' ), 'writing', 'default', array( 'label_for' => 'post_label' ));
	}

	/**
	 *　管理画面にフィールドを追加
	 * @since 0.2
	 * 
	 */	
	public function add_setting_field_callback( $args ) {
	?>
	<input type="text" class="text" value="<?php echo $this->label; ?>" id="post_label" name="post_label" />	
	<?php
	}

	/**
	 *　ホワイトリストにフィールドを追加
	 * @since 0.2
	 * 
	 */
	public function add_whitelist() {
		register_setting( 'writing', 'post_label' );
	}
	
	/**
	 * add_rewrite_rule実行後にリライトルールを更新
	 * @since 0.3
	 * 
	 */
	public function update_rules($oldstr ,$newstr) {
		$this->pre_str = $this->get_structure($newstr);
		$this->set_archive_rewrite();
		flush_rewrite_rules();
	}

}

 
new Post_Archive;
