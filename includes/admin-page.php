<?php
/**
 * Admin settings page for Sikorix Image Upload Manager.
 *
 * @package SikorixImageUploadManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Parent menu slug shared by Sikorix plugins. */
define( 'SIKORIX_IUM_PARENT_MENU_SLUG', 'sikorix' );

/** This plugin's admin page slug. */
define( 'SIKORIX_IUM_PAGE_SLUG', 'sikorix-image-upload-manager' );

/** Admin screen / hook id for this plugin's settings page. */
define( 'SIKORIX_IUM_PAGE_HOOK', 'sikorix_page_sikorix-image-upload-manager' );

/**
 * Shared args for registered integer settings.
 *
 * @param string $sanitize_callback Sanitize callback name.
 * @param int    $default           Default value.
 * @return array<string, mixed>
 */
function sikorix_ium_get_setting_args( $sanitize_callback, $default ) {
	return array(
		'type'              => 'integer',
		'sanitize_callback' => $sanitize_callback,
		'default'           => $default,
		'capability'        => 'manage_options',
		'show_in_rest'      => false,
	);
}

/**
 * Register settings, sections, and fields.
 */
function sikorix_ium_register_admin_settings() {
	register_setting(
		'sikorix_ium_settings_group',
		SIKORIX_IUM_OPTION_JPEG_QUALITY,
		sikorix_ium_get_setting_args( 'sikorix_ium_sanitize_quality', SIKORIX_IUM_DEFAULT_QUALITY )
	);

	register_setting(
		'sikorix_ium_settings_group',
		SIKORIX_IUM_OPTION_JPEG_PRIORITY,
		sikorix_ium_get_setting_args( 'sikorix_ium_sanitize_filter_priority', SIKORIX_IUM_DEFAULT_FILTER_PRIORITY )
	);

	register_setting(
		'sikorix_ium_settings_group',
		SIKORIX_IUM_OPTION_EDITOR_QUALITY,
		sikorix_ium_get_setting_args( 'sikorix_ium_sanitize_quality', SIKORIX_IUM_DEFAULT_QUALITY )
	);

	register_setting(
		'sikorix_ium_settings_group',
		SIKORIX_IUM_OPTION_EDITOR_PRIORITY,
		sikorix_ium_get_setting_args( 'sikorix_ium_sanitize_filter_priority', SIKORIX_IUM_DEFAULT_FILTER_PRIORITY )
	);

	add_settings_section(
		'sikorix_ium_jpeg_section',
		__( 'JPEG Quality', 'sikorix-image-upload-manager' ),
		'sikorix_ium_render_jpeg_section_intro',
		'sikorix-image-upload-manager'
	);

	add_settings_section(
		'sikorix_ium_editor_section',
		__( 'WordPress Editor Quality', 'sikorix-image-upload-manager' ),
		'sikorix_ium_render_editor_section_intro',
		'sikorix-image-upload-manager'
	);

	add_settings_field(
		'sikorix_ium_jpeg_controls',
		'',
		'sikorix_ium_render_jpeg_controls',
		'sikorix-image-upload-manager',
		'sikorix_ium_jpeg_section'
	);

	add_settings_field(
		'sikorix_ium_editor_controls',
		'',
		'sikorix_ium_render_editor_controls',
		'sikorix-image-upload-manager',
		'sikorix_ium_editor_section'
	);
}
add_action( 'admin_init', 'sikorix_ium_register_admin_settings' );

/**
 * Add the plugin under the Sikorix admin menu.
 */
function sikorix_ium_add_settings_page() {
	global $admin_page_hooks;

	if ( ! isset( $admin_page_hooks[ SIKORIX_IUM_PARENT_MENU_SLUG ] ) ) {
		add_menu_page(
			__( 'Sikorix', 'sikorix-image-upload-manager' ),
			__( 'Sikorix', 'sikorix-image-upload-manager' ),
			'manage_options',
			SIKORIX_IUM_PARENT_MENU_SLUG,
			'sikorix_ium_render_sikorix_menu_fallback',
			'dashicons-admin-plugins',
			58
		);
	}

	add_submenu_page(
		SIKORIX_IUM_PARENT_MENU_SLUG,
		__( 'Sikorix Image Upload Manager', 'sikorix-image-upload-manager' ),
		__( 'Image Upload Manager', 'sikorix-image-upload-manager' ),
		'manage_options',
		SIKORIX_IUM_PAGE_SLUG,
		'sikorix_ium_render_settings_page'
	);
}
add_action( 'admin_menu', 'sikorix_ium_add_settings_page' );

/**
 * Add a Settings link on the Plugins screen.
 *
 * @param array<string, string> $links Existing plugin action links.
 * @return array<string, string>
 */
function sikorix_ium_add_plugin_action_links( $links ) {
	$settings_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'admin.php?page=' . SIKORIX_IUM_PAGE_SLUG ) ),
		esc_html__( 'Settings', 'sikorix-image-upload-manager' )
	);

	array_unshift( $links, $settings_link );

	return $links;
}
add_filter(
	'plugin_action_links_' . plugin_basename( SIKORIX_IUM_PLUGIN_FILE ),
	'sikorix_ium_add_plugin_action_links'
);

/**
 * Remove the duplicate parent submenu entry WordPress auto-adds.
 */
function sikorix_ium_remove_parent_submenu_duplicate() {
	remove_submenu_page( SIKORIX_IUM_PARENT_MENU_SLUG, SIKORIX_IUM_PARENT_MENU_SLUG );
}
add_action( 'admin_menu', 'sikorix_ium_remove_parent_submenu_duplicate', 999 );

/**
 * Redirect the bare Sikorix parent slug to this plugin's settings page.
 */
function sikorix_ium_render_sikorix_menu_fallback() {
	wp_safe_redirect( admin_url( 'admin.php?page=' . SIKORIX_IUM_PAGE_SLUG ) );
	exit;
}

/**
 * Enqueue inline admin assets on the settings screen only.
 *
 * @param string $hook_suffix Current admin page hook suffix.
 */
function sikorix_ium_enqueue_admin_assets( $hook_suffix ) {
	if ( SIKORIX_IUM_PAGE_HOOK !== $hook_suffix ) {
		return;
	}

	wp_register_script( 'sikorix-ium-admin-settings', false, array(), SIKORIX_IUM_PLUGIN_VERSION, true );
	wp_enqueue_script( 'sikorix-ium-admin-settings' );
	wp_add_inline_script( 'sikorix-ium-admin-settings', sikorix_ium_get_admin_settings_script() );

	wp_register_style( 'sikorix-ium-admin-settings', false, array(), SIKORIX_IUM_PLUGIN_VERSION );
	wp_enqueue_style( 'sikorix-ium-admin-settings' );
	wp_add_inline_style( 'sikorix-ium-admin-settings', sikorix_ium_get_admin_settings_styles() );
}
add_action( 'admin_enqueue_scripts', 'sikorix_ium_enqueue_admin_assets' );

/**
 * Inline CSS for the settings page layout.
 *
 * @return string
 */
function sikorix_ium_get_admin_settings_styles() {
	return '
		.wrap h1.sikorix-ium-page-title {
			font-weight: 700;
			font-size: 23px;
		}
		.sikorix-ium-page-title__version {
			font-size: 13px;
			font-weight: 400;
			margin-left: 0.4em;
		}
		.sikorix-ium-control-label {
			display: block;
			font-weight: 600;
			margin: 16px 0 8px;
		}
		.sikorix-ium-control-row {
			display: flex;
			align-items: center;
			flex-wrap: wrap;
			gap: 12px;
			margin-bottom: 6px;
		}
		.sikorix-ium-control-row input[type="range"] {
			width: min(320px, 100%);
			max-width: 100%;
		}
		.sikorix-ium-default-note {
			margin: 0 0 8px;
			color: #646970;
		}
		.sikorix-ium-file-types {
			color: #646970;
			font-size: 13px;
		}
		.sikorix-ium-developed-by {
			margin: 12px 12px 12px 0;
			font-size: 14px;
			line-height: 1.4;
		}
		#wpfooter .sikorix-ium-developed-by {
			margin: 12px 12px 12px 0;
			padding-left: 0;
		}
	';
}

/**
 * Inline JavaScript that keeps sliders and number fields in sync.
 *
 * @return string
 */
function sikorix_ium_get_admin_settings_script() {
	return <<<'JS'
( function () {
	'use strict';

	/**
	 * Keep every slider and matching number input in a control row in sync.
	 *
	 * @param {HTMLElement} row Control row containing range and number inputs.
	 */
	function bindSyncedRow( row ) {
		var slider = row.querySelector( 'input[type="range"]' );
		var input = row.querySelector( 'input[type="number"]' );

		if ( ! slider || ! input ) {
			return;
		}

		var min = parseInt( input.min, 10 );
		var max = parseInt( input.max, 10 );

		if ( Number.isNaN( min ) ) {
			min = parseInt( slider.min, 10 ) || 1;
		}
		if ( Number.isNaN( max ) ) {
			max = parseInt( slider.max, 10 ) || 100;
		}

		function clamp( value ) {
			var n = parseInt( value, 10 );
			if ( Number.isNaN( n ) ) {
				n = min;
			}
			return Math.min( max, Math.max( min, n ) );
		}

		function syncFromSlider() {
			input.value = String( clamp( slider.value ) );
		}

		function syncFromInput( force ) {
			if ( ! force && '' === input.value ) {
				return;
			}
			var value = clamp( input.value );
			input.value = String( value );
			slider.value = String( value );
		}

		slider.addEventListener( 'input', syncFromSlider );
		slider.addEventListener( 'change', syncFromSlider );
		input.addEventListener( 'input', function () {
			syncFromInput( false );
		} );
		input.addEventListener( 'change', function () {
			syncFromInput( true );
		} );
		input.addEventListener( 'blur', function () {
			syncFromInput( true );
		} );

		syncFromInput( true );
	}

	function init() {
		document.querySelectorAll( '.sikorix-ium-control-row' ).forEach( bindSyncedRow );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
JS;
}

/**
 * Short intro under the JPEG section heading.
 */
function sikorix_ium_render_jpeg_section_intro() {
	echo '<p class="description">';
	esc_html_e(
		'Controls the jpeg_quality filter used when WordPress generates JPEG images. This affects JPEG images.',
		'sikorix-image-upload-manager'
	);
	echo '</p>';
}

/**
 * Short intro under the editor section heading.
 */
function sikorix_ium_render_editor_section_intro() {
	echo '<p class="description">';
	esc_html_e(
		'Controls the wp_editor_set_quality filter used when the WordPress image editor saves images. This affects JPEG, WebP, AVIF, and PNG images.',
		'sikorix-image-upload-manager'
	);
	echo '</p>';
}

/**
 * Render a synced slider and number field row.
 *
 * @param string $label       Visible label before the slider.
 * @param string $slider_id   HTML id for the range input.
 * @param string $input_id    HTML id for the number input.
 * @param string $option_name Option name used as the number input name attribute.
 * @param int    $value       Current value.
 * @param int    $min         Minimum allowed value.
 * @param int    $max         Maximum allowed value.
 * @param string $trailing    Optional plain text shown after the number field.
 */
function sikorix_ium_render_slider_number_row( $label, $slider_id, $input_id, $option_name, $value, $min, $max, $trailing = '' ) {
	?>
	<label class="sikorix-ium-control-label" for="<?php echo esc_attr( $input_id ); ?>">
		<?php echo esc_html( $label ); ?>
	</label>
	<div class="sikorix-ium-control-row">
		<label for="<?php echo esc_attr( $slider_id ); ?>" class="screen-reader-text">
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: control name, e.g. JPEG Quality. */
					__( '%s slider', 'sikorix-image-upload-manager' ),
					$label
				)
			);
			?>
		</label>
		<input
			type="range"
			id="<?php echo esc_attr( $slider_id ); ?>"
			min="<?php echo esc_attr( (string) $min ); ?>"
			max="<?php echo esc_attr( (string) $max ); ?>"
			step="1"
			value="<?php echo esc_attr( (string) $value ); ?>"
		/>
		<input
			type="number"
			id="<?php echo esc_attr( $input_id ); ?>"
			name="<?php echo esc_attr( $option_name ); ?>"
			min="<?php echo esc_attr( (string) $min ); ?>"
			max="<?php echo esc_attr( (string) $max ); ?>"
			step="1"
			value="<?php echo esc_attr( (string) $value ); ?>"
			class="small-text"
		/>
		<?php if ( '' !== $trailing ) : ?>
			<span class="sikorix-ium-file-types"><?php echo esc_html( $trailing ); ?></span>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render JPEG quality and filter priority controls.
 */
function sikorix_ium_render_jpeg_controls() {
	$quality  = sikorix_ium_get_jpeg_quality();
	$priority = sikorix_ium_get_jpeg_filter_priority();

	$default_quality = sprintf(
		/* translators: %d: WordPress default JPEG quality. */
		__( 'WordPress default: %d', 'sikorix-image-upload-manager' ),
		absint( SIKORIX_IUM_WP_DEFAULT_QUALITY )
	);

	sikorix_ium_render_slider_number_row(
		__( 'JPEG Quality', 'sikorix-image-upload-manager' ),
		'sikorix-ium-jpeg-quality-slider',
		'sikorix-ium-jpeg-quality-input',
		SIKORIX_IUM_OPTION_JPEG_QUALITY,
		$quality,
		SIKORIX_IUM_MIN_QUALITY,
		SIKORIX_IUM_MAX_QUALITY,
		$default_quality
	);

	$default_priority = sprintf(
		/* translators: %d: WordPress default filter priority. */
		__( 'WordPress default: %d', 'sikorix-image-upload-manager' ),
		absint( SIKORIX_IUM_WP_DEFAULT_FILTER_PRIORITY )
	);

	sikorix_ium_render_slider_number_row(
		__( 'JPEG Filter Priority', 'sikorix-image-upload-manager' ),
		'sikorix-ium-jpeg-priority-slider',
		'sikorix-ium-jpeg-priority-input',
		SIKORIX_IUM_OPTION_JPEG_PRIORITY,
		$priority,
		SIKORIX_IUM_MIN_PRIORITY,
		SIKORIX_IUM_MAX_PRIORITY,
		$default_priority
	);
}

/**
 * Render WordPress editor quality and filter priority controls.
 */
function sikorix_ium_render_editor_controls() {
	$quality  = sikorix_ium_get_editor_quality();
	$priority = sikorix_ium_get_editor_filter_priority();

	$default_quality = sprintf(
		/* translators: %d: WordPress default editor quality. */
		__( 'WordPress default: %d', 'sikorix-image-upload-manager' ),
		absint( SIKORIX_IUM_WP_DEFAULT_QUALITY )
	);

	sikorix_ium_render_slider_number_row(
		__( 'WordPress Editor Quality', 'sikorix-image-upload-manager' ),
		'sikorix-ium-editor-quality-slider',
		'sikorix-ium-editor-quality-input',
		SIKORIX_IUM_OPTION_EDITOR_QUALITY,
		$quality,
		SIKORIX_IUM_MIN_QUALITY,
		SIKORIX_IUM_MAX_QUALITY,
		$default_quality
	);

	$default_priority = sprintf(
		/* translators: %d: WordPress default filter priority. */
		__( 'WordPress default: %d', 'sikorix-image-upload-manager' ),
		absint( SIKORIX_IUM_WP_DEFAULT_FILTER_PRIORITY )
	);

	sikorix_ium_render_slider_number_row(
		__( 'WordPress Editor Filter Priority', 'sikorix-image-upload-manager' ),
		'sikorix-ium-editor-priority-slider',
		'sikorix-ium-editor-priority-input',
		SIKORIX_IUM_OPTION_EDITOR_PRIORITY,
		$priority,
		SIKORIX_IUM_MIN_PRIORITY,
		SIKORIX_IUM_MAX_PRIORITY,
		$default_priority
	);
}

/**
 * Whether the current admin screen is this plugin's settings page.
 *
 * @return bool
 */
function sikorix_ium_is_settings_screen() {
	if ( ! function_exists( 'get_current_screen' ) ) {
		return false;
	}

	$screen = get_current_screen();

	return ( $screen && SIKORIX_IUM_PAGE_HOOK === $screen->id );
}

/**
 * Print the developer credit on its own line above the WordPress footer text.
 */
function sikorix_ium_render_admin_footer_credit() {
	if ( ! sikorix_ium_is_settings_screen() ) {
		return;
	}
	?>
	<div class="sikorix-ium-developed-by">
		<?php esc_html_e( 'Developed by', 'sikorix-image-upload-manager' ); ?>
		<a
			href="<?php echo esc_url( 'https://sikorix.com/' ); ?>"
			target="_blank"
			rel="noopener noreferrer"
		>
			<?php esc_html_e( 'Sikorix', 'sikorix-image-upload-manager' ); ?>
		</a>
	</div>
	<?php
}
add_action( 'in_admin_footer', 'sikorix_ium_render_admin_footer_credit' );

/**
 * Render the full settings page.
 */
function sikorix_ium_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1 class="sikorix-ium-page-title">
			<?php esc_html_e( 'Sikorix Image Upload Manager', 'sikorix-image-upload-manager' ); ?>
			<span class="sikorix-ium-page-title__version"><?php echo esc_html( SIKORIX_IUM_PLUGIN_VERSION ); ?></span>
		</h1>

		<form action="options.php" method="post">
			<?php
			settings_fields( 'sikorix_ium_settings_group' );
			do_settings_sections( 'sikorix-image-upload-manager' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}
