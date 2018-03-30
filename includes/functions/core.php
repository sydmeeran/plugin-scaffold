<?php
namespace TenUp\TenUpScaffold\Core;

/**
 * Default setup routine
 *
 * @uses add_action()
 * @uses do_action()
 *
 * @return void
 */
function setup() {
	$n = function( $function ) {
		return __NAMESPACE__ . "\\$function";
	};

	add_action( 'init', $n( 'i18n' ) );
	add_action( 'init', $n( 'init' ) );
	add_action( 'wp_enqueue_scripts', $n( 'scripts' ) );
	add_action( 'wp_enqueue_scripts', $n( 'styles' ) );
	
	// Editor styles. add_editor_style() doesn't work outside of a theme.
	add_filter( 'mce_css', $n( 'mce_css' ) );

	do_action( 'tenup_scaffold_loaded' );
}

/**
 * Registers the default textdomain.
 *
 * @uses apply_filters()
 * @uses get_locale()
 * @uses load_textdomain()
 * @uses load_plugin_textdomain()
 * @uses plugin_basename()
 *
 * @return void
 */
function i18n() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'tenup-scaffold' );
	load_textdomain( 'tenup-scaffold', WP_LANG_DIR . '/tenup-scaffold/tenup-scaffold-' . $locale . '.mo' );
	load_plugin_textdomain( 'tenup-scaffold', false, plugin_basename( TENUP_SCAFFOLD_PATH ) . '/languages/' );
}

/**
 * Initializes the plugin and fires an action other plugins can hook into.
 *
 * @uses do_action()
 *
 * @return void
 */
function init() {
	do_action( 'tenup_scaffold_init' );
}

/**
 * Activate the plugin
 *
 * @uses init()
 * @uses flush_rewrite_rules()
 *
 * @return void
 */
function activate() {
	// First load the init scripts in case any rewrite functionality is being loaded
	init();
	flush_rewrite_rules();
}

/**
 * Deactivate the plugin
 *
 * Uninstall routines should be in uninstall.php
 *
 * @return void
 */
function deactivate() {

}

/**
 * Enqueue scripts for front-end.
 *
 * @uses wp_enqueue_script() to load front end scripts.
 *
 * @since 0.1.0
 *
 * @return void
 */
function scripts() {

	/**
	 * Generate an URL to a script, taking into account whether SCRIPT_DEBUG is enabled.
	 * 
	 * @param string $script Script file name (no .js extension)
	 * @param string $context Context for the script ('admin', 'frontend', or 'shared')
	 * 
	 * @return string|WP_Error URL
	 */
	function script_url( $script, $context ) {

		if( !in_array( $context, ['admin', 'frontend', 'shared'], true) ) {
			error_log('Invalid $context specfied in TenUpScaffold script loader.');
			return '';
		}

		return ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ?
			TENUP_SCAFFOLD_URL . "assets/js/${context}/{$script}.js" :
			TENUP_SCAFFOLD_URL . "dist/js/${context}.min.js" ;
		
	}

	wp_enqueue_script(
		'tenup_scaffold_shared',
		script_url( 'shared', 'shared' ),
		[],
		TENUP_SCAFFOLD_VERSION,
		true
	);

	if( is_admin() ) {
		wp_enqueue_script(
			'tenup_scaffold_admin',
			script_url( 'admin', 'admin' ),
			[],
			TENUP_SCAFFOLD_VERSION,
			true
		);
	}
	else {
		wp_enqueue_script(
			'tenup_scaffold_frontend',
			script_url( 'frontend', 'frontend' ),
			[],
			TENUP_SCAFFOLD_VERSION,
			true
		);
	}

}

/**
 * Enqueue styles for front-end.
 *
 * @uses wp_enqueue_style() to load front end styles.
 *
 * @since 0.1.0
 *
 * @return void
 */
function styles() {

	/**
	 * Generate an URL to a stylesheet, taking into account whether SCRIPT_DEBUG is enabled.
	 * 
	 * @param string $stylesheet Stylesheet file name (no .css extension)
	 * @param string $context Context for the script ('admin', 'frontend', or 'shared')
	 * 
	 * @return string|WP_Error URL
	 */
	function style_url( $stylesheet, $context ) {

		if( !in_array( $context, ['admin', 'frontend', 'shared'], true) ) {
			error_log('Invalid $context specfied in TenUpScaffold stylesheet loader.');
			return '';
		}

		return ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ?
			TENUP_SCAFFOLD_URL . "assets/css/${context}/{$stylesheet}.css" :
			TENUP_SCAFFOLD_URL . "dist/css/${stylesheet}.min.css" ;
		
	}

	wp_enqueue_style(
		'tenup_scaffold_shared',
		style_url( 'shared-style', 'shared' ),
		[],
		TENUP_SCAFFOLD_VERSION
	);

	if( is_admin() ) {
		wp_enqueue_script(
			'tenup_scaffold_admin',
			style_url( 'admin-style', 'admin' ),
			[],
			TENUP_SCAFFOLD_VERSION,
			true
		);
	}
	else {
		wp_enqueue_script(
			'tenup_scaffold_frontend',
			style_url( 'style', 'frontend' ),
			[],
			TENUP_SCAFFOLD_VERSION,
			true
		);
	}
	
}

/**
 * Enqueue editor styles
 * 
 * @since 0.1.0
 *
 * @return string
 */
function mce_css( $stylesheets ) {

	function style_url() {

		return TENUP_SCAFFOLD_URL . ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ?  
			"assets/css/admin/editor-style.css" :
			"dist/css/editor-style.min.css" );
			
	}

	return $stylesheets . ',' . style_url();
}