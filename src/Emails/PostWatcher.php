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

        if ($frequency === 'instant' && function_exists('as_schedule_single_action')) {
            $timeSetting = get_option('adp_delivery_time', '');
            $scheduledTimestamp = time();

            if (!empty($timeSetting)) {
                $timezone = wp_timezone();
                $now = current_datetime();
                
                $targetDateString = $now->format('Y-m-d') . ' ' . $timeSetting;
                $targetDateTime = \DateTime::createFromFormat('Y-m-d H:i', $targetDateString, $timezone);

                if ($targetDateTime !== false) {
                    if ($targetDateTime->getTimestamp() <= time()) {
                        $targetDateTime->modify('+1 day');
                    }
                    $scheduledTimestamp = $targetDateTime->getTimestamp();
                }
            }

            as_schedule_single_action(
                $scheduledTimestamp, 
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