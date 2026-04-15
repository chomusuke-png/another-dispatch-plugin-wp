<?php

declare(strict_types=1);

namespace Zumito\ADP\PublicHooks;

use Zumito\ADP\Core\Database;

class VerificationHandler
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
        add_action('init', [$this, 'handleVerification']);
    }

    public function handleVerification(): void
    {
        if (!isset($_GET['adp_action']) || $_GET['adp_action'] !== 'verify') {
            return;
        }

        if (empty($_GET['email']) || empty($_GET['hash'])) {
            $this->redirectWithResponse('verification_invalid');
        }

        $email = sanitize_email(wp_unslash($_GET['email']));
        $hash = sanitize_text_field(wp_unslash($_GET['hash']));

        $subscriber = $this->database->getSubscriber($email);

        if ($subscriber === null || $subscriber->hash !== $hash) {
            $this->redirectWithResponse('verification_invalid');
        }

        if ($subscriber->status === 'active') {
            $this->redirectWithResponse('already_active');
        }

        if ($subscriber->status === 'pending') {
            $this->database->updateSubscriberStatus($email, 'new');

            if (function_exists('as_enqueue_async_action')) {
                as_enqueue_async_action(
                    'adp_send_welcome_email_event',
                    ['email' => $email, 'hash' => $hash],
                    'adp_emails'
                );
            } else {
                do_action('adp_send_welcome_email_event', $email, $hash);
            }

            $this->redirectWithResponse('verified');
        }
    }

    private function redirectWithResponse(string $responseCode): void
    {
        $redirectUrl = add_query_arg('adp_msg', $responseCode, home_url('/'));
        wp_safe_redirect($redirectUrl);
        exit;
    }
}