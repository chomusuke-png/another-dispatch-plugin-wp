<?php

declare(strict_types=1);

namespace Zumito\ADP\PublicHooks;

use Zumito\ADP\Core\Database;

class SubscribeHandler
{
    private Database $database;
    private const MAX_ATTEMPTS = 3;
    private const RATE_LIMIT_MINUTES = 15;

    public function __construct(Database $database)
    {
        $this->database = $database;

        add_action('admin_post_nopriv_adp_subscribe_action', [$this, 'handleSubscription']);
        add_action('admin_post_adp_subscribe_action', [$this, 'handleSubscription']);
    }

    public function handleSubscription(): void
    {
        if ($this->isSpamTrapTriggered()) {
            $this->redirectBackWithSuccess('subscribed');
        }

        if (!isset($_POST['adp_subscribe_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['adp_subscribe_nonce'])), 'adp_subscribe_action')) {
            wp_die('Error de seguridad. La petición no es válida.', 'Error', ['response' => 403]);
        }

        if ($this->isRateLimited()) {
            $this->redirectBackWithError('rate_limited');
        }

        if (empty($_POST['adp_email'])) {
            $this->redirectBackWithError('empty_email');
        }

        $email = sanitize_email(wp_unslash($_POST['adp_email']));

        if (!is_email($email)) {
            $this->redirectBackWithError('invalid_email');
        }

        $existingSubscriber = $this->database->getSubscriber($email);

        if ($existingSubscriber !== null) {
            if ($existingSubscriber->status === 'active') {
                $this->redirectBackWithError('already_subscribed');
            } else {
                $this->redirectBackWithError('already_pending');
            }
        }

        $hash = md5($email . time() . wp_salt());
        $inserted = $this->database->addSubscriber($email, $hash, 'pending');

        if (!$inserted) {
            $this->redirectBackWithError('db_error');
        }

        if (function_exists('as_enqueue_async_action')) {
            as_enqueue_async_action(
                'adp_send_verification_email_event', 
                ['email' => $email, 'hash' => $hash], 
                'adp_emails'
            );
        }

        $this->redirectBackWithSuccess('subscribed');
    }

    private function isSpamTrapTriggered(): bool
    {
        if (!empty($_POST['adp_honey_check'])) {
            return true;
        }

        if (!isset($_POST['adp_time_trap'])) {
            return true;
        }

        $formGenerationTime = (int) $_POST['adp_time_trap'];
        $currentTime = time();
        $timeDifference = $currentTime - $formGenerationTime;

        if ($timeDifference < 3) {
            return true;
        }

        return false;
    }

    private function isRateLimited(): bool
    {
        $ipAddress = $this->getClientIp();
        $transientKey = 'adp_rl_' . md5($ipAddress);
        $attempts = (int) get_transient($transientKey);

        if ($attempts >= self::MAX_ATTEMPTS) {
            return true;
        }

        set_transient($transientKey, $attempts + 1, self::RATE_LIMIT_MINUTES * MINUTE_IN_SECONDS);
        
        return false;
    }

    private function getClientIp(): string
    {
        $ipAddress = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ipAddress = trim($ipList[0]);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }

        return sanitize_text_field($ipAddress);
    }

    private function redirectBackWithError(string $errorCode): void
    {
        $referer = wp_get_referer() ?: home_url('/');
        $redirectUrl = add_query_arg('adp_msg', $errorCode, $referer);
        wp_safe_redirect($redirectUrl);
        exit;
    }

    private function redirectBackWithSuccess(string $successCode): void
    {
        $referer = wp_get_referer() ?: home_url('/');
        $redirectUrl = add_query_arg('adp_msg', $successCode, $referer);
        wp_safe_redirect($redirectUrl);
        exit;
    }
}