<?php

declare(strict_types=1);

namespace Zumito\ADP\Emails;

class PostWatcher
{
    public function __construct()
    {
        add_action('transition_post_status', [$this, 'handlePostPublished'], 10, 3);
    }

    public function handlePostPublished(string $newStatus, string $oldStatus, object $post): void
    {
        if ($newStatus !== 'publish' || $oldStatus === 'publish' || $post->post_type !== 'post') {
            return;
        }

        $frequency = get_option('adp_delivery_frequency', 'instant');

        if ($frequency === 'instant' && function_exists('as_enqueue_async_action')) {
            as_enqueue_async_action(
                'adp_process_batch_send', 
                [
                    'post_id' => $post->ID, 
                    'offset'  => 0
                ], 
                'adp_emails'
            );
        }
    }
}