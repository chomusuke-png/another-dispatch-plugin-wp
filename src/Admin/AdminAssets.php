<?php

declare(strict_types=1);

namespace Zumito\ADP\Admin;

class AdminAssets
{
    private const ASSETS_VERSION = '2.6.0';

    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function enqueueAssets(string $hookSuffix): void
    {
        $isPluginPage = strpos($hookSuffix, 'another-dispatch-plugin') !== false || strpos($hookSuffix, 'adp-') !== false;

        if (!$isPluginPage) {
            return;
        }

        $pluginUrl = defined('ADP_URL') ? ADP_URL : plugin_dir_url(dirname(__DIR__, 2)) . '/';

        wp_enqueue_style(
            'adp-main-css', 
            $pluginUrl . 'assets/css/admin/main.css', 
            [], 
            self::ASSETS_VERSION
        );

        if (strpos($hookSuffix, 'adp-customizer') !== false) {
            $this->enqueueCustomizerAssets($pluginUrl);
        } elseif (strpos($hookSuffix, 'adp-debug') !== false) {
            $this->enqueueDebugAssets($pluginUrl);
        } else {
            $this->enqueueDashboardAssets($pluginUrl);
        }

        $isDebugPage = strpos($hookSuffix, 'adp-debug') !== false;

        wp_localize_script('adp-admin-js', 'adp_vars', [
            'ajax_url'      => admin_url('admin-ajax.php'),
            'nonce'         => wp_create_nonce('adp_debug_refresh_nonce'),
            'is_debug_page' => $isDebugPage ? '1' : '0'
        ]);
    }

    private function enqueueCustomizerAssets(string $pluginUrl): void
    {
        wp_enqueue_style(
            'adp-customizer-css', 
            $pluginUrl . 'assets/css/admin/customizer.css', 
            ['adp-main-css'], 
            self::ASSETS_VERSION
        );
        
        wp_enqueue_media();
        
        wp_register_script('adp-admin-js', '', [], self::ASSETS_VERSION); 
        wp_enqueue_script(
            'adp-customizer-js', 
            $pluginUrl . 'assets/js/admin-customizer.js', 
            ['jquery', 'adp-admin-js'], 
            self::ASSETS_VERSION, 
            false
        );
    }

    private function enqueueDebugAssets(string $pluginUrl): void
    {
        wp_enqueue_style(
            'adp-debug-css', 
            $pluginUrl . 'assets/css/admin/debug.css', 
            ['adp-main-css'], 
            self::ASSETS_VERSION
        );

        wp_enqueue_script(
            'adp-admin-js', 
            $pluginUrl . 'assets/js/admin-script.js', 
            ['jquery'], 
            self::ASSETS_VERSION, 
            true
        );
    }

    private function enqueueDashboardAssets(string $pluginUrl): void
    {
        wp_enqueue_style(
            'adp-dashboard-css', 
            $pluginUrl . 'assets/css/admin/dashboard.css', 
            ['adp-main-css'], 
            self::ASSETS_VERSION
        );

        wp_enqueue_script(
            'adp-admin-js', 
            $pluginUrl . 'assets/js/admin-script.js', 
            ['jquery'], 
            self::ASSETS_VERSION, 
            true
        );
    }
}