<?php

abstract class WPPluginCore 
{

	/**
	 * The domain specified for this plugin.
	 *
	 * @since    1.0.0
	 * 
	*/

	private $slug;
	private $version;

	protected $actions;
	protected $filters;

	public $stylesheets;
	public $scripts;

//--------------------------------------------------------------------------------------------------

	public function __construct( $slug, $version )
	{

		$this->slug        = $slug;
		$this->version     = $version;
		$this->stylesheets = [];
		$this->scripts     = [];
		$this->actions     = [];
		$this->filters     = [];

	}

//--------------------------------------------------------------------------------------------------

	public function __get( $name )
	{
		if ( isset($this->$name) == true )
		{
			return $this->$name;
		}
	}

//--------------------------------------------------------------------------------------------------

	public function run()
	{

		$this->add_actions();
		$this->add_filters();

	}

//--------------------------------------------------------------------------------------------------

	public function add_actions()
	{
		$action_name = ( is_admin() == true )? 'admin_enqueue_scripts' : 'wp_enqueue_scripts';

		if ( isset($this->stylesheets) == true )
		{
			add_action( $action_name, [ $this, 'enqueue_stylesheets'  ] );
		}

		if ( isset($this->scripts) == true )
		{
			add_action( $action_name, [ $this, 'enqueue_scripts' ] );
		}

		if ( isset( $this->actions ) == true )
		{
			foreach ( $this->actions as $key => $action )
			{
				$hook     = $action['name'];
				$function = [ $this, $action['name'] ];
				$priority = ( isset( $action['priority'] ) == true)? $action['priority'] : 10;
				$accepted_args = ( isset( $action['nb_args']) == true )? $action['nb_args'] : 1;

				add_action( $hook, $function, $priority, $accepted_args );
			}			
		}

	} 

//--------------------------------------------------------------------------------------------------

	public function add_filters()
	{  
		if ( isset( $this->filters ) == true )
		{
			foreach ( $this->filters as $key => $filter )
			{
				$tag      = $filter['tag'];
				$function = [ $this, $filter['tag'] ];
				$priority = ( isset( $filter['priority'] ) == true)? $filter['priority'] : 10;
				$accepted_args = ( isset( $filter['nb_args']) == true )? $filter['nb_args'] : 1;

				add_filter( $tag, $function, $priority, $accepted_args );				
			}
		}
	} 

//--------------------------------------------------------------------------------------------------

	public function enqueue_stylesheets()
	{
		if ( isset( $this->stylesheets ) == true )
		{
			foreach ($this->stylesheets as $key => $stylesheet)
			{
				$name    = $stylesheet[0];
				$src     = $stylesheet[1];
				$deps    = ( isset($stylesheet[2]) == true)? $stylesheet[2] : false;
				$version = ( isset($stylesheet[3]) == true)? $stylesheet[3] : $this->version;
				$media   = ( isset($stylesheet[4]) == true)? $stylesheet[4] : 'all';

				wp_register_style( $name, $src, $deps, $version, $media );
        wp_enqueue_style( $name );
			}
		}
	} 

//--------------------------------------------------------------------------------------------------

	public function enqueue_scripts( $hook )
	{ 
		global $post;

		if ( (isset( $this->scripts ) == true) && ($post != null ) && ($post->post_type == $this->slug) )
		{
			foreach ($this->scripts as $key => $script)
			{
				$name    = $script[0];
				$src     = $script[1];
				$deps    = ( isset($script[2]) == true )? $script[2] : false;
				$version = ( isset($script[3]) == true )? $script[3] : $this->version;
				$media   = ( isset($script[4]) == true )? $script[4] : 'all';

				wp_enqueue_script( $name, $src, $deps, $version, $media );
			}
		}
	}

//--------------------------------------------------------------------------------------------------

}
?>