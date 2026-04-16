<?php

declare(strict_types=1);

namespace Zumito\ADP\PublicHooks;

use Zumito\ADP\Core\Database;

class TrackingHandler
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
        add_action('init', [$this, 'handleTrackingRequests']);
    }

    public function handleTrackingRequests(): void
    {
        if (empty($_GET['adp_action'])) {
            return;
        }

        $action = sanitize_text_field(wp_unslash($_GET['adp_action']));

        if ($action === 'track_open') {
            $this->processOpenEvent();
        } elseif ($action === 'track_click') {
            $this->processClickEvent();
        }
    }

    private function processOpenEvent(): void
    {
        if (!empty($_GET['email'])) {
            $email = sanitize_email(wp_unslash($_GET['email']));
            $this->database->logEvent($email, 'open');
        }

        header('Content-Type: image/gif');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==');
        exit;
    }

    private function processClickEvent(): void
    {
        $targetUrl = home_url();

        if (!empty($_GET['url'])) {
            $rawUrl = wp_unslash($_GET['url']);
            $targetUrl = esc_url_raw($rawUrl);

            if (!empty($_GET['email'])) {
                $email = sanitize_email(wp_unslash($_GET['email']));
                $this->database->logEvent($email, 'click', $targetUrl);
            }
        }

        wp_redirect($targetUrl);
        exit;
    }
}