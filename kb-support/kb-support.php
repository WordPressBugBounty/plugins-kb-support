<?php

/**
 * Plugin Name: KB Support
 * Plugin URI: https://kb-support.com/
 * Description: The best help desk tool for WordPress. Simple yet effective. Feature rich.
 * Version: 1.7.4
 * Author: KB Support
 * Author URI: https://kb-support.com/
 * Text Domain: kb-support
 * Domain Path: /languages
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Tags: helpdesk, help desk, ticket system, support ticket, knowledge base
 *
 *
 * KB Support is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * KB Support is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with KB Support; if not, see https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package		KBS
 * @category	Core
 * @author		KB Support
 * @version		1.7.4
 */
// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !class_exists( 'KB_Support' ) ) {
    /**
     * Main KB_Support Class.
     *
     * @since 1.0
     */
    final class KB_Support {
        /** Singleton *************************************************************/
        /**
         * @var		KB_Support The one true KB_Support
         * @since	1.0
         */
        private static $instance;

        /**
         * KBS Roles Object.
         *
         * @var		object		KBS_Roles
         * @since	1.0
         */
        public $roles;

        /**
         * KBS Emails.
         *
         * @var		object		KBS_Emails
         * @since	1.0
         */
        public $emails;

        /**
         * KBS Email Tags.
         *
         * @var		object		KBS_Email_Template_Tags
         * @since	1.0
         */
        public $email_tags;

        /**
         * KBS HTML Elements.
         *
         * @var		object		KBS_HTML_Elements
         * @since	1.0
         */
        public $html;

        /**
         * KBS Customers.
         *
         * @var		object		KBS_DB_Customers
         * @since	1.0
         */
        public $customers;

        /**
         * KBS Customer Meta.
         *
         * @var		object		KBS_DB_Customer_Meta
         * @since	1.0
         */
        public $customer_meta;

        /**
         * KBS Knowledgebase.
         *
         * @var		object		KBS_Knowledgebase
         * @since	1.0
         */
        public $KB;

        /**
         * KBS API
         *
         * @var		object
         * @since	1.5
         */
        public $api;

        /**
         * Main KB_Support Instance.
         *
         * Insures that only one instance of KB_Support exists in memory at any one
         * time. Also prevents needing to define globals all over the place.
         *
         * @since	1.0
         * @static
         * @static	var		arr		$instance
         * @uses	KB_Support::setup_constants()	Setup the constants needed.
         * @uses	KB_Support::includes()			Include the required files.
         * @uses	KB_Support::load_textdomain()	Load the language files.
         * @see KBS()
         * @return	obj	KB_Support	The one true KB_Support
         */
        public static function instance() {
            if ( !isset( self::$instance ) && !self::$instance instanceof KB_Support ) {
                do_action( 'before_kbsupport_init' );
                self::$instance = new KB_Support();
                self::$instance->setup_constants();
                add_action( 'plugins_loaded', array(self::$instance, 'load_textdomain') );
                self::$instance->includes();
                self::$instance->roles = new KBS_Roles();
                self::$instance->api = new KBS_API();
                self::$instance->emails = new KBS_Emails();
                self::$instance->email_tags = new KBS_Email_Template_Tags();
                self::$instance->html = new KBS_HTML_Elements();
                self::$instance->customers = new KBS_DB_Customers();
                self::$instance->customer_meta = new KBS_DB_Customer_Meta();
                self::$instance->KB = new KBS_Knowledgebase();
                ks_fs()->add_action( 'after_uninstall', [self::$instance, 'uninstall'] );
                do_action( 'kbsupport_init' );
            }
            return self::$instance;
        }

        /**
         * Throw error on object clone.
         *
         * The whole idea of the singleton design pattern is that there is a single
         * object therefore, we don't want the object to be cloned.
         *
         * @since	1.0
         * @access	protected
         * @return	void
         */
        public function __clone() {
            // Cloning instances of the class is forbidden.
            _doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'kb-support' ), '1.0' );
        }

        // __clone
        /**
         * Disable unserializing of the class.
         *
         * @since	1.0
         * @access	protected
         * @return	void
         */
        public function __wakeup() {
            // Unserializing instances of the class is forbidden.
            _doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'kb-support' ), '1.0' );
        }

        // __wakeup
        /**
         * Setup plugin constants.
         *
         * @access	private
         * @since	1.0
         * @return	void
         */
        private function setup_constants() {
            if ( !defined( 'KBS_VERSION' ) ) {
                define( 'KBS_VERSION', '1.7.4' );
            }
            if ( !defined( 'KBS_PLUGIN_DIR' ) ) {
                define( 'KBS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
            }
            if ( !defined( 'KBS_PLUGIN_URL' ) ) {
                define( 'KBS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
            }
            if ( !defined( 'KBS_PLUGIN_FILE' ) ) {
                define( 'KBS_PLUGIN_FILE', __FILE__ );
            }
        }

        // setup_constants
        /**
         * Include required files.
         *
         * @access	private
         * @since	1.0
         * @return	void
         */
        private function includes() {
            global $kbs_options;
            require_once KBS_PLUGIN_DIR . 'includes/admin/settings/register-settings.php';
            $kbs_options = kbs_get_settings();
            if ( file_exists( KBS_PLUGIN_DIR . 'includes/deprecated-functions.php' ) ) {
                require_once KBS_PLUGIN_DIR . 'includes/deprecated-functions.php';
            }
            require_once KBS_PLUGIN_DIR . 'includes/ajax-functions.php';
            require_once KBS_PLUGIN_DIR . 'includes/template-functions.php';
            require_once KBS_PLUGIN_DIR . 'includes/class-kbs-cache-helper.php';
            require_once KBS_PLUGIN_DIR . 'includes/post-types.php';
            require_once KBS_PLUGIN_DIR . 'includes/post-taxonomies.php';
            require_once KBS_PLUGIN_DIR . 'includes/class-kbs-db.php';
            require_once KBS_PLUGIN_DIR . 'includes/class-kbs-stats.php';
            require_once KBS_PLUGIN_DIR . 'includes/class-kbs-roles.php';
            require_once KBS_PLUGIN_DIR . 'includes/class-kbs-cron.php';
            require_once KBS_PLUGIN_DIR . 'includes/class-kbs-logging.php';
            require_once KBS_PLUGIN_DIR . 'includes/class-kbs-knowledgebase.php';
            require_once KBS_PLUGIN_DIR . 'includes/class-kbs-register-meta.php';
            require_once KBS_PLUGIN_DIR . 'includes/article/article-actions.php';
            require_once KBS_PLUGIN_DIR . 'includes/article/class-kbs-article-stats.php';
            require_once KBS_PLUGIN_DIR . 'includes/article/class-kbs-articles-query.php';
            require_once KBS_PLUGIN_DIR . 'includes/article/article-functions.php';
            require_once KBS_PLUGIN_DIR . 'includes/article/article-restricted.php';
            require_once KBS_PLUGIN_DIR . 'includes/article/article-content.php';
            require_once KBS_PLUGIN_DIR . 'includes/article/article-search.php';
            require_once KBS_PLUGIN_DIR . 'includes/tickets/class-kbs-ticket-stats.php';
            require_once KBS_PLUGIN_DIR . 'includes/tickets/class-kbs-tickets-query.php';
            require_once KBS_PLUGIN_DIR . 'includes/tickets/class-kbs-replies-query.php';
            require_once KBS_PLUGIN_DIR . 'includes/tickets/class-kbs-ticket.php';
            require_once KBS_PLUGIN_DIR . 'includes/tickets/ticket-actions.php';
            require_once KBS_PLUGIN_DIR . 'includes/tickets/ticket-functions.php';
            require_once KBS_PLUGIN_DIR . 'includes/tickets/reply-functions.php';
            require_once KBS_PLUGIN_DIR . 'includes/departments/department-functions.php';
            require_once KBS_PLUGIN_DIR . 'includes/files.php';
            require_once KBS_PLUGIN_DIR . 'includes/formatting.php';
            require_once KBS_PLUGIN_DIR . 'includes/scripts.php';
            require_once KBS_PLUGIN_DIR . 'includes/emails/email-actions.php';
            require_once KBS_PLUGIN_DIR . 'includes/emails/class-kbs-emails.php';
            require_once KBS_PLUGIN_DIR . 'includes/emails/class-kbs-email-tags.php';
            require_once KBS_PLUGIN_DIR . 'includes/class-kbs-html-elements.php';
            require_once KBS_PLUGIN_DIR . 'includes/emails/email-functions.php';
            require_once KBS_PLUGIN_DIR . 'includes/emails/email-template.php';
            require_once KBS_PLUGIN_DIR . 'includes/forms/class-kbs-form.php';
            require_once KBS_PLUGIN_DIR . 'includes/forms/form-functions.php';
            require_once KBS_PLUGIN_DIR . 'includes/misc-functions.php';
            require_once KBS_PLUGIN_DIR . 'includes/participant-functions.php';
            require_once KBS_PLUGIN_DIR . 'includes/privacy-functions.php';
            require_once KBS_PLUGIN_DIR . 'includes/login-register.php';
            require_once KBS_PLUGIN_DIR . 'includes/customers/class-kbs-customer.php';
            require_once KBS_PLUGIN_DIR . 'includes/companies/class-kbs-company.php';
            require_once KBS_PLUGIN_DIR . 'includes/companies/class-kbs-companies-query.php';
            require_once KBS_PLUGIN_DIR . 'includes/companies/company-functions.php';
            require_once KBS_PLUGIN_DIR . 'includes/user-functions.php';
            require_once KBS_PLUGIN_DIR . 'includes/customers/customer-functions.php';
            require_once KBS_PLUGIN_DIR . 'includes/customers/customer-actions.php';
            require_once KBS_PLUGIN_DIR . 'includes/agents/class-kbs-agent.php';
            require_once KBS_PLUGIN_DIR . 'includes/agents/agent-functions.php';
            require_once KBS_PLUGIN_DIR . 'includes/agents/agent-actions.php';
            require_once KBS_PLUGIN_DIR . 'includes/customers/class-kbs-db-customers.php';
            require_once KBS_PLUGIN_DIR . 'includes/customers/class-kbs-db-customer-meta.php';
            require_once KBS_PLUGIN_DIR . 'includes/shortcodes.php';
            require_once KBS_PLUGIN_DIR . 'includes/sla.php';
            require_once KBS_PLUGIN_DIR . 'includes/widgets.php';
            require_once KBS_PLUGIN_DIR . 'includes/compatibility-functions.php';
            require_once KBS_PLUGIN_DIR . 'includes/api/class-kbs-api.php';
            require_once KBS_PLUGIN_DIR . 'includes/api/endpoints/class-kbs-agents-api.php';
            require_once KBS_PLUGIN_DIR . 'includes/api/endpoints/class-kbs-articles-api.php';
            require_once KBS_PLUGIN_DIR . 'includes/api/endpoints/class-kbs-companies-api.php';
            require_once KBS_PLUGIN_DIR . 'includes/api/endpoints/class-kbs-customers-api.php';
            require_once KBS_PLUGIN_DIR . 'includes/api/endpoints/class-kbs-tickets-api.php';
            require_once KBS_PLUGIN_DIR . 'includes/api/endpoints/class-kbs-replies-api.php';
            require_once KBS_PLUGIN_DIR . 'includes/api/endpoints/class-kbs-forms-api.php';
            require_once KBS_PLUGIN_DIR . 'includes/api/endpoints/class-kbs-form-fields-api.php';
            require_once KBS_PLUGIN_DIR . 'includes/blocks/kbs-login-block.php';
            require_once KBS_PLUGIN_DIR . 'includes/blocks/kbs-register-block.php';
            require_once KBS_PLUGIN_DIR . 'includes/blocks/kbs-profile-editor-block.php';
            require_once KBS_PLUGIN_DIR . 'includes/blocks/kbs-tickets-block.php';
            require_once KBS_PLUGIN_DIR . 'includes/blocks/kbs-search-block.php';
            require_once KBS_PLUGIN_DIR . 'includes/blocks/kbs-submit-block.php';
            if ( is_admin() ) {
                require_once KBS_PLUGIN_DIR . 'includes/admin/admin-pages.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/class-kbs-admin-notices.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/admin-plugin.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/dashboard-widgets.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/customers/customers-page.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/customers/customer-functions.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/customers/customer-actions.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/customers/contextual-help.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/tickets/tickets.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/tickets/ticket-sources.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/tickets/metaboxes.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/tickets/contextual-help.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/departments/departments.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/article/article.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/article/metaboxes.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/article/contextual-help.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/companies/company.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/companies/metaboxes.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/companies/contextual-help.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/forms/forms.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/forms/metaboxes.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/forms/form-actions.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/forms/contextual-help.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/settings/class-kbs-display-settings.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/settings/contextual-help.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/thickbox.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/tools.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/import-export/import/import-functions.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/import-export/export/export-functions.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-actions.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/upgrades/upgrades.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/welcome.php';
                require_once KBS_PLUGIN_DIR . 'includes/admin/branding.php';
            }
            require_once KBS_PLUGIN_DIR . 'includes/install.php';
        }

        // includes
        /**
         * Load the text domain for translations.
         *
         * @access	private
         * @since	1.0
         * @return	void
         */
        public function load_textdomain() {
            // Set filter for plugin's languages directory.
            $kbs_lang_dir = dirname( plugin_basename( KBS_PLUGIN_FILE ) ) . '/languages/';
            $kbs_lang_dir = apply_filters( 'kbs_languages_directory', $kbs_lang_dir );
            // Traditional WordPress plugin locale filter.
            $locale = ( is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale() );
            $locale = apply_filters( 'plugin_locale', $locale, 'kb-support' );
            load_textdomain( 'kb-support', WP_LANG_DIR . '/kb-support/kb-support-' . $locale . '.mo' );
            load_plugin_textdomain( 'kb-support', false, $kbs_lang_dir );
        }

        // load_textdomain
        public function uninstall() {
            if ( is_multisite() ) {
                global $wpdb;
                foreach ( $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" ) as $blog_id ) {
                    switch_to_blog( $blog_id );
                    $this->kbs_uninstall();
                    restore_current_blog();
                }
            } else {
                $this->kbs_uninstall();
            }
        }

        public function kbs_uninstall() {
            global $wpdb, $wp_roles;
            if ( kbs_get_option( 'remove_on_uninstall' ) ) {
                // Delete the Custom Post Types
                $kbs_taxonomies = array(
                    'ticket_category',
                    'ticket_tag',
                    'article_category',
                    'article_tag',
                    'kbs_log_type',
                    'ticket_source',
                    'department'
                );
                $kbs_post_types = array(
                    'kbs_ticket',
                    'kbs_ticket_reply',
                    'article',
                    'kbs_form',
                    'kbs_form_field',
                    'kbs_company',
                    'kbs_log'
                );
                foreach ( $kbs_post_types as $post_type ) {
                    $kbs_taxonomies = array_merge( $kbs_taxonomies, get_object_taxonomies( $post_type ) );
                    $items = get_posts( array(
                        'post_type'   => $post_type,
                        'post_status' => 'any',
                        'numberposts' => -1,
                        'fields'      => 'ids',
                    ) );
                    if ( $items ) {
                        foreach ( $items as $item ) {
                            wp_delete_post( $item, true );
                        }
                    }
                }
                // Delete Terms & Taxonomies
                foreach ( array_unique( array_filter( $kbs_taxonomies ) ) as $taxonomy ) {
                    $terms = $wpdb->get_results( $wpdb->prepare( "SELECT t.*, tt.*\n            FROM {$wpdb->terms}\n            AS t\n            INNER JOIN {$wpdb->term_taxonomy}\n            AS tt\n            ON t.term_id = tt.term_id\n            WHERE tt.taxonomy IN ('%s')\n            ORDER BY t.name ASC", $taxonomy ) );
                    // Delete Terms.
                    if ( $terms ) {
                        foreach ( $terms as $term ) {
                            $wpdb->delete( $wpdb->term_relationships, array(
                                'term_taxonomy_id' => $term->term_taxonomy_id,
                            ) );
                            $wpdb->delete( $wpdb->term_taxonomy, array(
                                'term_taxonomy_id' => $term->term_taxonomy_id,
                            ) );
                            $wpdb->delete( $wpdb->terms, array(
                                'term_id' => $term->term_id,
                            ) );
                        }
                    }
                    // Delete Taxonomies.
                    $wpdb->delete( $wpdb->term_taxonomy, array(
                        'taxonomy' => $taxonomy,
                    ), array('%s') );
                }
                // Delete Plugin Pages
                $kbs_pages = array('submission_page', 'tickets_page');
                foreach ( $kbs_pages as $kbs_page ) {
                    $page = kbs_get_option( $kbs_page, false );
                    if ( $page ) {
                        wp_delete_post( $page, true );
                    }
                }
                // Delete all Plugin Options
                delete_option( 'kbs_last_ticket_number' );
                delete_option( 'kbs_completed_upgrades' );
                delete_option( '_kbs_table_check' );
                delete_option( 'kbs_settings' );
                delete_option( 'kbs_version' );
                // Delete Custom Capabilities
                KBS()->roles->remove_caps();
                // Delete Custom Roles
                $kbs_roles = array('support_manager', 'support_agent', 'support_customer');
                foreach ( $kbs_roles as $role ) {
                    remove_role( $role );
                }
                // Remove all database tables
                $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "kbs_customers" );
                $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "kbs_customermeta" );
                // Remove any transients and options we've left behind
                $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\\_transient\\_kbs\\_%'" );
                $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\\_transient\\_timeout\\_kbs\\_%'" );
                $kbs_all_options = array(
                    'kbs_default_submission_form_created',
                    'kbs_version_upgraded_from',
                    $wpdb->prefix . 'kbs_customers_db_version',
                    $wpdb->prefix . 'kbs_customermeta_db_version',
                    'kbs_install_version',
                    'kbs_installed'
                );
                foreach ( $kbs_all_options as $kbs_all_option ) {
                    delete_option( $kbs_all_option );
                }
            }
            // kbs_uninstall
        }

    }

    // class KB_Support
}
/**
 * The main function for that returns KB_Support
 *
 * The main function responsible for returning the one true KB_Support
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $kbs = KBS(); ?>
 *
 * @since	1.0
 * @return	obj		KB_Support	The one true KB_Support Instance.
 */
if ( !function_exists( 'KBS' ) ) {
    function KBS() {
        return KB_Support::instance();
    }

    // KBS
}
if ( !function_exists( 'ks_fs' ) ) {
    function ks_fs() {
        global $ks_fs;
        if ( !isset( $ks_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/vendor/freemius/start.php';
            $ks_fs = fs_dynamic_init( array(
                'id'             => '16080',
                'slug'           => 'kb-support',
                'type'           => 'plugin',
                'public_key'     => 'pk_b98d5b8cf8e9130183ff32d378fc9',
                'premium_suffix' => 'Pro',
                'is_premium'     => false,
                'has_addons'     => false,
                'has_paid_plans' => true,
                'menu'           => array(
                    'slug'    => 'edit.php?post_type=kbs_ticket',
                    'support' => false,
                    'contact' => true,
                ),
                'is_live'        => true,
            ) );
        }
        return $ks_fs;
    }

    KBS();
    // Init Freemius.
    ks_fs();
    // Signal that SDK was initiated.
    do_action( 'ks_fs_loaded' );
}