<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GitHub-based plugin updater.
 *
 * Checks a public GitHub repository for new releases and enables
 * one-click updates from the WordPress admin Plugins page.
 *
 * Tag releases as "v1.0.3" etc. The tag version (stripped of "v")
 * is compared against the plugin header Version.
 */
class SDS_GitHub_Updater {

    private $slug;
    private $plugin_file;
    private $github_repo;
    private $github_api_url;
    private $plugin_data;
    private $github_response;

    /**
     * @param string $plugin_file  Plugin basename (e.g. 'rafarrel-form/sds-referral-form.php').
     * @param string $github_repo  GitHub repo in "owner/repo" format.
     */
    public function __construct( $plugin_file, $github_repo ) {
        $this->plugin_file    = $plugin_file;
        $this->github_repo    = $github_repo;
        $this->github_api_url = 'https://api.github.com/repos/' . $github_repo;
        $this->slug           = dirname( $plugin_file );

        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
        add_filter( 'plugins_api', array( $this, 'plugin_info' ), 20, 3 );
        add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );

        // Clear cached release data when plugin version changes (fresh install/manual update)
        $cached_version = get_option( 'sds_updater_current_version', '' );
        if ( $cached_version !== SDS_VERSION ) {
            delete_transient( 'sds_github_release_' . md5( $this->github_repo ) );
            update_option( 'sds_updater_current_version', SDS_VERSION );
        }
    }

    /**
     * Fetch the latest release from GitHub (cached for 3 hours).
     */
    private function get_github_release() {
        if ( ! empty( $this->github_response ) ) {
            return $this->github_response;
        }

        $transient_key = 'sds_github_release_' . md5( $this->github_repo );
        $cached = get_transient( $transient_key );

        if ( $cached !== false ) {
            $this->github_response = $cached;
            return $cached;
        }

        $url = $this->github_api_url . '/releases/latest';

        $response = wp_remote_get( $url, array(
            'headers' => array(
                'Accept'     => 'application/vnd.github.v3+json',
                'User-Agent' => 'SDS-Referral-Form-Plugin/' . SDS_VERSION,
            ),
            'timeout' => 15,
        ) );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code !== 200 ) {
            return false;
        }

        $release = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( empty( $release ) || ! isset( $release['tag_name'] ) ) {
            return false;
        }

        $this->github_response = $release;
        set_transient( $transient_key, $release, 3 * HOUR_IN_SECONDS );

        return $release;
    }

    /**
     * Get the plugin header data.
     */
    private function get_plugin_data() {
        if ( empty( $this->plugin_data ) ) {
            if ( ! function_exists( 'get_plugin_data' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $this->plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $this->plugin_file );
        }
        return $this->plugin_data;
    }

    /**
     * Compare versions and inject update into WP transient.
     */
    public function check_update( $transient ) {
        $release = $this->get_github_release();
        if ( ! $release ) {
            return $transient;
        }

        $remote_version = ltrim( $release['tag_name'], 'vV' );
        $local_version  = SDS_VERSION;

        if ( version_compare( $remote_version, $local_version, '>' ) ) {
            $download_url = $release['zipball_url'];

            // Prefer an uploaded .zip asset if available
            if ( ! empty( $release['assets'] ) ) {
                foreach ( $release['assets'] as $asset ) {
                    if ( substr( $asset['name'], -4 ) === '.zip' ) {
                        $download_url = $asset['browser_download_url'];
                        break;
                    }
                }
            }

            $item = (object) array(
                'slug'        => $this->slug,
                'plugin'      => $this->plugin_file,
                'new_version' => $remote_version,
                'url'         => 'https://github.com/' . $this->github_repo,
                'package'     => $download_url,
                'icons'       => array(),
                'banners'     => array(),
                'tested'      => '6.8',
                'requires'    => '5.6',
            );

            $transient->response[ $this->plugin_file ] = $item;
        }

        return $transient;
    }

    /**
     * Provide plugin info for the "View Details" popup.
     */
    public function plugin_info( $result, $action, $args ) {
        if ( $action !== 'plugin_information' ) {
            return $result;
        }

        if ( ! isset( $args->slug ) || $args->slug !== $this->slug ) {
            return $result;
        }

        $release     = $this->get_github_release();
        $plugin_data = $this->get_plugin_data();

        if ( ! $release ) {
            return $result;
        }

        $download_url = $release['zipball_url'];
        if ( ! empty( $release['assets'] ) ) {
            foreach ( $release['assets'] as $asset ) {
                if ( substr( $asset['name'], -4 ) === '.zip' ) {
                    $download_url = $asset['browser_download_url'];
                    break;
                }
            }
        }

        return (object) array(
            'name'            => $plugin_data['Name'],
            'slug'            => $this->slug,
            'version'         => ltrim( $release['tag_name'], 'vV' ),
            'author'          => $plugin_data['AuthorName'],
            'homepage'        => $plugin_data['PluginURI'],
            'requires'        => '5.6',
            'tested'          => '6.8',
            'requires_php'    => '7.4',
            'downloaded'      => 0,
            'last_updated'    => isset( $release['published_at'] ) ? $release['published_at'] : '',
            'sections'        => array(
                'description' => $plugin_data['Description'],
                'changelog'   => isset( $release['body'] ) ? nl2br( esc_html( $release['body'] ) ) : '',
            ),
            'download_link'   => $download_url,
        );
    }

    /**
     * After install, rename the extracted folder to match the plugin slug.
     * GitHub zipball extracts to "owner-repo-hash/" — this renames it.
     */
    public function after_install( $response, $hook_extra, $result ) {
        if ( ! isset( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->plugin_file ) {
            return $result;
        }

        global $wp_filesystem;

        $proper_destination = WP_PLUGIN_DIR . '/' . $this->slug;
        $source             = $result['destination'];

        // Only move if the source is different from the expected destination
        if ( $source !== $proper_destination ) {
            $wp_filesystem->move( $source, $proper_destination );
            $result['destination'] = $proper_destination;
        }

        // Clear the release cache so next check picks up the new version
        delete_transient( 'sds_github_release_' . md5( $this->github_repo ) );

        // Re-activate if it was active
        if ( is_plugin_active( $this->plugin_file ) ) {
            activate_plugin( $this->plugin_file );
        }

        return $result;
    }
}
