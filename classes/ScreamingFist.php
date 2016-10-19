<?php

/**
 * Class that encapsulates theme functionality.
 */
class ScreamingFist {
  const NAME = 'ScreamingFist';

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
    add_action( 'admin_enqueue_script', array($this, 'addAdminStyles') );
  }


  // ==============================================
  // Work Functions
  // ==============================================
  
  /**
   * Add CSS to site.
   *
   * @since 1.0.0
   *
   * @uses wp_enqueue_style
   * 
   * @see 'wp_enqueue_scripts' hook
   *
   * @return void
   */
  public function enqueueStyles() {
    $handle = $this->themePrefix . '-style';

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
   * Loads custom CSS for the editor
   *
   * @since 1.0.0
   * 
   * @see 'init' hook
   *
   * @uses add_editor_style
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
   * @see 'editor_stylesheets' hook
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
   * @see 'mce_buttons_2' hook
   *
   * @uses $this->getStyleFormats
   *
   * @param  array  $buttons
   * @return array
   */
  public function addEditorStyleDropdown($buttons) {
    $style_formats = $this->getStyleFormats();
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
   * @see 'tiny_mce_before_init' hook
   *
   * @uses $this->getStyleFormats
   *
   * @param  array  $settings
   * @return array
   */
  public function addStyleFormats($settings) {
    $style_formats = $this->getStyleFormats();
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
   * @see 'admin_enqueue_script' hook
   *
   * @uses $this->themePrefix, $this->adminCssFilename, $this->CssVersion, wp_enqueue_style
   *
   * @return void
   */
  public function addAdminStyles() {
    $handle = $this->themePrefix() . '-admin-style';
    $src = get_stylesheet_directory_uri() . '/' . $this->adminCssFilename();
    $version = $this->themeCssVersion();
    wp_enqueue_style($handle, $src, array(), $version);
  }

  // ==============================================
  // Accessor Functions
  // ==============================================

  /**
   * Gets the WYSIWYG editor CSS filename
   *
   * @since 1.0.0
   *
   * @return  string
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
   * @return  string;
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
   * @return  string
   */
  public function themeCssVersion() {
    // Always use new version in development
    if ( defined('WP_DEBUG') && WP_DEBUG ) {
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
   * @return  array
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
}
