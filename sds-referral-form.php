<?php
/**
 * Plugin Name: SDS Referral Form
 * Plugin URI: https://sydneydisabilitysupport.com
 * Description: NDIS Referral Form for Sydney Disability Support with PDF generation, email notifications, and admin settings.
 * Version: 1.0.8
 * Author: Noman E Jawad
 * Author URI: https://nomanjawad.vercel.app/
 * License: GPL-2.0+
 * Text Domain: sds-referral-form
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SDS_VERSION', '1.0.8' );
define( 'SDS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SDS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SDS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'SDS_GITHUB_REPO', 'nomanjawad/refarrel-form-plugin' );

// Autoload classes
require_once SDS_PLUGIN_DIR . 'includes/class-sds-activator.php';
require_once SDS_PLUGIN_DIR . 'includes/class-sds-loader.php';
require_once SDS_PLUGIN_DIR . 'includes/class-sds-github-updater.php';

// Activation / Deactivation
register_activation_hook( __FILE__, array( 'SDS_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'SDS_Activator', 'deactivate' ) );

// Boot the plugin
function sds_referral_form_init() {
    $loader = new SDS_Loader();
    $loader->run();

    // GitHub updater - checks for new releases
    new SDS_GitHub_Updater( SDS_PLUGIN_BASENAME, SDS_GITHUB_REPO );
}
add_action( 'plugins_loaded', 'sds_referral_form_init' );
