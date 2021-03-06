<?php

/**
 * Class that encapsulates theme functionality.
 */
class ScreamingFist {
	const NAME = 'ScreamingFist';

	// For reference
	const TEXTDOMAIN = 'screaming-fist';

	const VERSION = '1.0.0';

	const CSS_VERSION = 1;

	private static $instance = null;

	/**
	 * Get instance of class.
	 *
	 * @return ScreamingFist
	 */
	public static function getInstance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	// constructor
	private function __construct() {
		if ( !is_admin() ) {
			$this->_setup_styles();
		}

		$this->_clean_wordpress_head_output();
		$this->_customize_wordpress_output();
		$this->_setup_menus();
		$this->_setup_sidebars();

		if ( is_admin() ) {
			$this->_customize_editor();
			$this->_customize_admin();
		}
	}

	// ==============================================
	// Setup/Prep Functions
	// ==============================================
	// Setup hooks to add CSS to site
	private function _setup_styles() {
		add_action( 'wp_enqueue_scripts', array($this, 'enqueueStyles') );
		add_filter( 'stylesheet_uri', array($this, 'selectDevProdCss'), 10, 2 );
	}

	// Remove existing action hooks for a cleaner head
	private function _clean_wordpress_head_output() {
		//remove_action( 'wp_head', 'feed_links_extra', 3 ); // Don't display the links to the extra feeds such as category feeds
		//remove_action( 'wp_head', 'feed_links', 2 ); // Don't display the links to the general feeds: Post and Comment Feed
		remove_action( 'wp_head', 'rsd_link' ); // Don't display the link to the Really Simple Discovery service endpoint, EditURI link
		remove_action( 'wp_head', 'wlwmanifest_link' ); // Don't display the link to the Windows Live Writer manifest file.
		remove_action( 'wp_head', 'index_rel_link' ); // index link
		remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 ); // prev link
		remove_action( 'wp_head', 'start_post_rel_link', 10, 0 ); // start link
		remove_action( 'wp_head', 'adjacent_posts_rel_link', 10, 0 ); // Display relational links for the posts adjacent to the current post.
		remove_action( 'wp_head', 'wp_generator' ); // Don't display the XHTML generator that is generated on the wp_head hook, WP version
		// Get rid of emoji support added in 4.2
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
	}

	// Customize various bits of WordPress output
	private function _customize_wordpress_output() {
		add_filter( 'body_class', array($this, 'addBodyClasses'), 10, 1 );
		add_filter( 'body_class', array($this, 'prefixBodyClasses'), 20, 1 );
	}

	// Register menus
	private function _setup_menus() {
		add_action( 'init', array($this, 'registerMenus') );
	}

	// Register sidebars
	private function _setup_sidebars() {
		add_action( 'widgets_init', array($this, 'registerSidebars') );
	}

	// Setup hooks to customize the editor
	private function _customize_editor() {
		// Load custom CSS for the editor
		add_action( 'init', array($this, 'addEditorStyles') );

		// Inject a version number into the CSS file url for the editor
		add_filter( 'editor_stylesheets', array($this, 'addCssVersionForEditor') );

		// Enable the Style dropdown in the editor
		add_filter( 'mce_buttons_2', array($this, 'addEditorStyleDropdown') );

		// Add styles to the dropdown
		add_filter( 'tiny_mce_before_init', array($this, 'addStyleFormats') );
	}

	// Setup hooks to customize the admin
	private function _customize_admin() {
		add_action( 'admin_enqueue_scripts', array($this, 'addAdminStyles') );
	}


	// ==============================================
	// Work Functions
	// ==============================================

	/**
	 * Add CSS to site.
	 *
	 * @since 1.0.0
	 *
	 * @uses wp_enqueue_style()
	 *
	 * @see 'wp_enqueue_scripts'
	 *
	 * @return void
	 */
	public function enqueueStyles() {
		$handle = $this->themePrefix() . '-style';

		/**
		 * The main theme CSS URI
		 *
		 * @since 1.0.0
		 *
		 * @param  string  $css_uri
		 */
		$src = apply_filters( 'screaming_fist_theme_css_uri', get_stylesheet_uri() );

		/**
		 * Dependencies for the main theme CSS
		 *
		 * @since 1.0.0
		 *
		 * @param  array  $deps
		 */
		$css_deps = apply_filters( 'screaming_fist_theme_css_deps', array() );
		$version = $this->themeCssVersion();
		wp_enqueue_style($handle, $src, $css_deps, $version);
	}

	/**
	 * Select the Dev or Production CSS file
	 *
	 * @since 1.0.0
	 *
	 * @uses $this->debugModeActive()
	 *
	 * @see 'stylesheet_uri'
	 *
	 * @param  string  $stylesheet_uri
	 */
	public function selectDevProdCss($stylesheet_uri) {
		// Defensive programming
		if ( $this->isCssFile($stylesheet_uri) ) {
			// Default to prod
			$suffix = '-prod.css';
			if ( $this->debugModeActive() ) {
				$suffix = '-dev.css';
			}
			$stylesheet_uri = substr($stylesheet_uri, 0, strlen($stylesheet_uri) - 4) . $suffix;
		}
		return $stylesheet_uri;
	}

	/**
	 * Add CSS classes to <body>
	 *
	 * @since 1.0.0
	 *
	 * @see 'body_class'
	 *
	 * @param  array  $classes
	 * @return array
	 */
	public function addBodyClasses($classes) {
		/**
		 * Update body classes.
		 *
		 * @since 1.0.0
		 *
		 * @param  array  $classes
		 */
		return apply_filters('screaming_fist_add_body_classes', $classes);
	}

	/**
	 * Prefix body classes with 'body--' for better selectors.
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $classes
	 * @return array
	 */
	public function prefixBodyClasses($classes) {
		return array_map( array($this, 'applyBodyClassPrefix'), $classes );
	}

	/**
	 * Register menus for the theme.
	 *
	 * @since 1.0.0
	 *
	 * @uses register_nav_menus()
	 *
	 * @return void
	 */
	public function registerMenus() {
		$menus = array(
			'primary' => __('Primary Menu', 'screaming-fist'),
			'social'  => __('Social Links', 'screaming-fist'),
		);

		/**
		 * Allow child themes to change menus to register.
		 *
		 * @since 1.0.0
		 *
		 * @see register_nav_menus
		 *
		 * @param  array  $menus
		 */
		$menus = apply_filters( 'screaming_fist_register_menus', $menus );

		register_nav_menus($menus);
	}

	/**
	 * Register sidebars for the theme.
	 *
	 * @since 1.0.0
	 *
	 * @uses register_sidebar()
	 *
	 * @return void
	 */
	public function registerSidebars() {
		/**
		 * Control whether to register default sidebars for theme.
		 *
		 * @since 1.0.0
		 *
		 * @param  boolean  $register_default_sidebars
		 */
		$register_default_sidebars = apply_filters( 'screaming_fist_register_default_sidebars', true );
		if ( $register_default_sidebars ) {
			register_sidebar( array(
				'name'          => __( 'Sidebar', 'screaming-fist' ),
				'id'            => 'sidebar-1',
				'description'   => __( 'Add widgets here to appear in your sidebar.', 'screaming-fist' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			) );

			register_sidebar( array(
				'name'          => __( 'Content Bottom 1', 'screaming-fist' ),
				'id'            => 'sidebar-2',
				'description'   => __( 'Appears at the bottom of the content on posts and pages.', 'screaming-fist' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			) );

			register_sidebar( array(
				'name'          => __( 'Content Bottom 2', 'screaming-fist' ),
				'id'            => 'sidebar-3',
				'description'   => __( 'Appears at the bottom of the content on posts and pages.', 'screaming-fist' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			) );
		}

		/**
		 * Register additional sidebars.
		 *
		 * @since 1.0.0
		 */
		do_action('screaming_fist_register_sidebars');
	}

	/**
	 * Loads custom CSS for the editor
	 *
	 * @since 1.0.0
	 *
	 * @see 'init'
	 *
	 * @uses add_editor_style()
	 *
	 * @return void
	 */
	public function addEditorStyles() {
		$css_filename = $this->editorCssFilename();
		add_editor_style($css_filename);
	}

	/**
	 * Inject version number in editor CSS URL.
	 *
	 * @since 1.0.0
	 *
	 * @see 'editor_stylesheets'
	 *
	 * @param  array  $stylesheets
	 * @return array
	 */
	public function addCssVersionForEditor($stylesheets) {
		$editor_css_filename = $this->editorCssFilename();
		$editor_css_filename_length = strlen($editor_css_filename);

		for ( $i=0; $i < count($stylesheets); $i++ ) {
			if ( substr($stylesheets[$i], 0 - $editor_css_filename_length) === $editor_css_filename ) {
				$stylesheets[$i] .= '?ver=' . $this->themeCssVersion();
				break;
			}
		}

		return $stylesheets;
	}

	/**
	 * Enables Style dropdown in editor.
	 *
	 * @since 1.0.0
	 *
	 * @see 'mce_buttons_2'
	 *
	 * @uses $this->styleFormats()
	 *
	 * @param  array  $buttons
	 * @return array
	 */
	public function addEditorStyleDropdown($buttons) {
		$style_formats = $this->styleFormats();
		if ( count($style_formats) > 0 ) {
			array_unshift($buttons, 'styleselect');
		}

		return $buttons;
	}

	/**
	 * Adds classes to Style dropdown
	 *
	 * @since 1.0.0
	 *
	 * @see 'tiny_mce_before_init'
	 *
	 * @uses $this->styleFormats()
	 *
	 * @param  array  $settings
	 * @return array
	 */
	public function addStyleFormats($settings) {
		$style_formats = $this->styleFormats();
		if ( count($style_formats) > 0 ) {
			$settings['style_formats'] = json_encode($style_formats);
		}

		return $settings;
	}

	/**
	 * Add CSS file for admin styles
	 *
	 * @since 1.0.0
	 *
	 * @see 'admin_enqueue_script'
	 *
	 * @uses $this->themePrefix()
	 * @uses $this->adminCssFilename()
	 * @uses $this->CssVersion()
	 * @uses wp_enqueue_style()
	 *
	 * @return void
	 */
	public function addAdminStyles() {
		$handle = $this->themePrefix() . '-admin-style';
		$src = get_stylesheet_directory_uri() . '/' . $this->adminCssFilename();
		$version = $this->themeCssVersion();
		$deps = array('editor-buttons');
		wp_enqueue_style($handle, $src, $deps, $version);
	}

	// ==============================================
	// Accessor Functions
	// ==============================================

	/**
	 * Gets the WYSIWYG editor CSS filename
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function editorCssFilename() {
		/**
		 * The WYSIWYG editor CSS filename.
		 *
		 * @since 1.0.0
		 *
		 * @param  string  $css_filename
		 */
		return apply_filters( 'screaming_fist_editor_css_filename', 'wp-editor-style.css' );
	}

	/**
	 * Gets the wp-admin CSS filename.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function adminCssFilename() {
		/**
		 * The wp-admin CSS filename.
		 *
		 * @since 1.0.0
		 *
		 * @param  string  $css_filename
		 */
		return apply_filters( 'screaming_fist_admin_css_filename', 'wp-admin-style.css' );
	}

	/**
	 * Gets the prefix for enqueued resources.
	 *
	 * @since 1.0.0
	 *
	 * @return string;
	 */
	public function themePrefix() {
		/**
		 * The prefix to be used for enqueued resources.
		 *
		 * @since 1.0.0
		 *
		 * @param  string  $prefix
		 */
		return apply_filters( 'screaming_fist_theme_prefix', self::NAME );
	}

	/**
	 * Gets the CSS version number.
	 *
	 * If WordPress has `WP_DEBUG` enabled will use `time()` for version number so that file will never be cached.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function themeCssVersion() {
		// Always use new version in development
		if ( $this->debugModeActive() ) {
			return time();
		}

		/**
		 * The CSS version number
		 *
		 * @since 1.0.0
		 *
		 * @param  string  $version
		 */
		return apply_filters( 'screaming_fist_theme_version', self::CSS_VERSION );
	}

	/**
	 * Gets a struct of styles for WYSIWYG style pulldown.
	 *
	 * @since 1.0.0
	 *
	 * @see http://codex.wordpress.org/TinyMCE_Custom_Styles#Using_style_formats
	 *
	 * @return array
	 */
	public function styleFormats() {
		$style_formats = array();

		/**
		 * Adds/updates style formats
		 *
		 * @since 1.0.0
		 *
		 * @param  array  $style_formats
		 */
		return apply_filters( 'screaming_fist_style_formats', $style_formats );
	}

	/**
	 * Get the string to use for body class prefix.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function bodyClassPrefix() {
		$prefix = 'body--';
		/**
		 * Update the body class prefix.
		 *
		 * @since 1.0.0
		 *
		 * @param  string  $prefix
		 */
		return apply_filters( 'screaming_fist_body_class_prefix', $prefix );
	}

	// ==============================================
	// Utility Functions
	// ==============================================

	/**
	 * Check if WordPress is running in Debug mode.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	public function debugModeActive() {
		$debug_mode_active = defined('WP_DEBUG') && WP_DEBUG;

		/**
		 * Debug mode active state.
		 *
		 * @since 1.0.0
		 *
		 * @param  boolean  $debug_mode_active
		 */
		return apply_filters( 'screaming_fist_debug_mode_active', $debug_mode_active );
	}

	/**
	 * Test if a URI points to a CSS file
	 *
	 * @since 1.0.0
	 *
	 * @param  string  $uri
	 * @return boolean
	 */
	public function isCssFile($uri) {
		$is_css_file = '.css' === substr($uri, -4);

		/**
		 * Is CSS File test.
		 *
		 * @since 1.0.0
		 *
		 * @param  boolean  $is_css_file
		 * @param  string  $uri
		 */
		return apply_filters( 'screaming_fist_is_css_file', $is_css_file, $uri );
	}

	public function applyBodyClassPrefix($class_name) {
		$prefix = $this->bodyClassPrefix();
		$prefix_length = strlen($prefix);
		if ( $prefix !== substr($class_name, 0, $prefix_length) ) {
			$class_name = $prefix . $class_name;
		}
		return $class_name;
	}


}
