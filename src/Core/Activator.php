<?php

declare(strict_types=1);

namespace Zumito\ADP\Core;

class Activator
{
    public static function activate(): void
    {
        self::createDatabaseTables();
        self::setDefaultOptions();
        self::scheduleMaintenanceTasks();
    }

    private static function createDatabaseTables(): void
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();
        $subscribersTable = $wpdb->prefix . 'adp_subscribers';
        $logsTable = $wpdb->prefix . 'adp_event_logs';

        $sql = "CREATE TABLE {$subscribersTable} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            email varchar(100) NOT NULL,
            hash varchar(64) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY email (email)
        ) {$charsetCollate};";

        $sqlLogs = "CREATE TABLE {$logsTable} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            email varchar(100) NOT NULL,
            event_type varchar(20) NOT NULL,
            url text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY email (email),
            KEY event_type (event_type)
        ) {$charsetCollate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        dbDelta($sqlLogs);
    }

    private static function setDefaultOptions(): void
    {
        $defaultOptions = [
            'adp_delivery_frequency'      => 'instant',
            'adp_sender_email'            => get_option('admin_email'),
            'adp_batch_size'              => 50,
            'adp_batch_delay'             => 5,
            'adp_pending_expiration_days' => 30,
            'adp_color_header_bg'         => '#2271b1',
            'adp_color_header_text'       => '#ffffff',
            'adp_body_bg'                 => '#f1f1f1',
            'adp_color_btn_bg'            => '#2271b1',
            'adp_color_btn_text'          => '#ffffff',
            'adp_color_links'             => '#2271b1',
        ];

        foreach ($defaultOptions as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }

    private static function scheduleMaintenanceTasks(): void
    {
        if (function_exists('as_next_scheduled_action')) {
            $nextSchedule = as_next_scheduled_action('adp_daily_cleanup_event');
            if ($nextSchedule === false) {
                as_schedule_recurring_action(time(), DAY_IN_SECONDS, 'adp_daily_cleanup_event', [], 'adp_emails');
            }
        }
    }
}