<?php

declare(strict_types=1);

namespace Zumito\ADP\PublicHooks;

use Zumito\ADP\Core\Database;

class SubscribeHandler
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;

        add_action('admin_post_nopriv_adp_subscribe_action', [$this, 'handleSubscription']);
        add_action('admin_post_adp_subscribe_action', [$this, 'handleSubscription']);
    }

    public function handleSubscription(): void
    {
        if (!empty($_POST['adp_honey_check'])) {
            $this->redirectBackWithSuccess('subscribed');
        }

        if (!isset($_POST['adp_subscribe_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['adp_subscribe_nonce'])), 'adp_subscribe_action')) {
            wp_die('Error de seguridad. La petición no es válida.', 'Error', ['response' => 403]);
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