<?php
class WPPlugin
{
	protected $slug;
	protected $version;
//	protected $i18n;
	protected $plugin_dir;
	protected $core;

//--------------------------------------------------------------------------------------------------

	public function __construct( $slug, $version ) 
	{

		$this->slug       = $slug;
		$this->version    = $version;
//		$this->i18n       = $i18n;
		$this->plugin_dir = ABSPATH . 'wp-content/plugins/'.$this->slug.'/';

	}

//--------------------------------------------------------------------------------------------------

	public function _register_post_type()
	{

		$this->post_args[ 'query_var' ]       = $this->slug;
		$this->post_args[ "rewrite" ]["slug"] = $this->slug;

		register_post_type( $this->slug, $this->post_args);

	}

//--------------------------------------------------------------------------------------------------

	public function run()
	{
		$class_name        = str_replace( " ", "_", ucwords( str_replace("-", " ", $this->slug) ) );
		$admin_class_name  = $class_name."_Admin"; 
		$public_class_name = $class_name."_Public";
		$this->init_i18n();

		$core_type = ( is_admin() == true )? "admin" : "public";

		require_once $this->plugin_dir."$core_type/".$this->slug."-$core_type.class.php";
		$class_name .= "_".ucwords($core_type); 
		$this->core = new $class_name( $this->slug, $this->version );

		$stylesheet_path = $this->plugin_dir."$core_type/".$this->slug."-$core_type.css"; 
		$stylesheet_url  = "/wp-content/plugins/".$this->slug."/$core_type/".$this->slug."-$core_type.css"; 
		if (file_exists( $stylesheet_path ) == true)
		{
			$this->core->stylesheets[] = [ $this->slug, $stylesheet_url ];
		}

		$script_path = $this->plugin_dir."$core_type/".$this->slug."-$core_type.js";
		$script_url  = "/wp-content/plugins/".$this->slug."/$core_type/".$this->slug."-$core_type.js";
		if (file_exists( $script_path ) == true)
		{
			$this->core->scripts[] = [ $this->slug, $script_url, ['jquery'], $this->version, 'all' ];
		}

		$this->core->run();

	}

//--------------------------------------------------------------------------------------------------

	public function init_i18n()
	{
		load_plugin_textdomain( $this->slug, false, $this->plugin_dir. '/languages/' );
	}

//--------------------------------------------------------------------------------------------------

	public function register_post_type( $args ) 
	{
		$this->post_args = $args;

		add_action( 'init', [$this, '_register_post_type'] );
	}

//--------------------------------------------------------------------------------------------------

}
?>