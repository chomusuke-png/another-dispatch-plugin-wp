<?php

declare(strict_types=1);

namespace Zumito\ADP\Emails;

use Zumito\ADP\Core\Database;
use Exception;

class EmailSender
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;

        add_action('adp_process_batch_send', [$this, 'processBatchSend'], 10, 2);
        add_action('adp_send_verification_email_event', [$this, 'sendVerificationEmail'], 10, 2);
        add_action('adp_weekly_digest_event', [$this, 'processDigestSend'], 10, 1);
        add_action('adp_monthly_digest_event', [$this, 'processDigestSend'], 10, 1);
    }

    public function processBatchSend(int $postId, int $offset): void
    {
        $post = get_post($postId);
        if (!$post || $post->post_status !== 'publish') {
            return;
        }

        $batchSize = (int) get_option('adp_batch_size', 50);
        $subscribers = $this->database->getSubscribers($batchSize, $offset, 'active');

        if (empty($subscribers)) {
            return;
        }

        $featuredImage = has_post_thumbnail($post->ID) ? get_the_post_thumbnail_url($post->ID, 'large') : '';

        foreach ($subscribers as $subscriber) {
            $unsubscribeLink = $this->generateUnsubscribeLink($subscriber->email, $subscriber->hash);
            
            $htmlContent = $this->renderTemplate('single', [
                'postTitle'       => get_the_title($post->ID),
                'postContent'     => apply_filters('the_content', $post->post_content),
                'postLink'        => get_permalink($post->ID),
                'featuredImage'   => $featuredImage,
                'unsubscribeLink' => $unsubscribeLink,
                'blogName'        => get_bloginfo('name')
            ]);

            wp_mail(
                $subscriber->email, 
                get_the_title($post->ID), 
                $htmlContent, 
                ['Content-Type: text/html; charset=UTF-8']
            );
        }

        if (count($subscribers) === $batchSize && function_exists('as_schedule_single_action')) {
            $delayMinutes = (int) get_option('adp_batch_delay', 5);
            $nextRunTime = time() + ($delayMinutes * 60);
            
            as_schedule_single_action(
                $nextRunTime, 
                'adp_process_batch_send', 
                [
                    'post_id' => $postId, 
                    'offset'  => $offset + $batchSize
                ], 
                'adp_emails'
            );
        }
    }

    public function processDigestSend(int $offset = 0): void
    {
        $batchSize = (int) get_option('adp_batch_size', 50);
        $frequency = get_option('adp_delivery_frequency', 'weekly');
        $dateQueryStr = $frequency === 'monthly' ? '1 month ago' : '1 week ago';

        $recentPosts = get_posts([
            'post_status' => 'publish',
            'date_query'  => [['after' => $dateQueryStr]]
        ]);

        if (empty($recentPosts)) {
            return;
        }

        $subscribers = $this->database->getSubscribers($batchSize, $offset, 'active');

        if (empty($subscribers)) {
            return;
        }

        foreach ($subscribers as $subscriber) {
            $unsubscribeLink = $this->generateUnsubscribeLink($subscriber->email, $subscriber->hash);
            
            $htmlContent = $this->renderTemplate('digest', [
                'emailTitle'      => 'Resumen de Artículos - ' . get_bloginfo('name'),
                'postsList'       => $recentPosts,
                'unsubscribeLink' => $unsubscribeLink,
                'blogName'        => get_bloginfo('name')
            ]);

            wp_mail(
                $subscriber->email, 
                'Resumen de Artículos - ' . get_bloginfo('name'), 
                $htmlContent, 
                ['Content-Type: text/html; charset=UTF-8']
            );
        }

        if (count($subscribers) === $batchSize && function_exists('as_schedule_single_action')) {
            $delayMinutes = (int) get_option('adp_batch_delay', 5);
            $nextRunTime = time() + ($delayMinutes * 60);
            $hookName = $frequency === 'monthly' ? 'adp_monthly_digest_event' : 'adp_weekly_digest_event';
            
            as_schedule_single_action(
                $nextRunTime, 
                $hookName, 
                ['offset' => $offset + $batchSize], 
                'adp_emails'
            );
        }
    }

    public function sendVerificationEmail(string $email, string $hash): void
    {
        $verificationLink = home_url('/?adp_action=verify&email=' . urlencode($email) . '&hash=' . $hash);

        $htmlContent = $this->renderTemplate('verification', [
            'verificationLink' => $verificationLink,
            'blogName'         => get_bloginfo('name')
        ]);

        wp_mail(
            $email, 
            'Confirma tu suscripción en ' . get_bloginfo('name'), 
            $htmlContent, 
            ['Content-Type: text/html; charset=UTF-8']
        );
    }

    private function renderTemplate(string $templateName, array $contextData): string
    {
        extract($contextData);

        ob_start();
        
        try {
            require ADP_PATH . "templates/emails/{$templateName}.php";
            $content = ob_get_clean();
            
            return $content !== false ? $content : '';
        } catch (Exception $exception) {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            error_log('ADP Template Error: ' . $exception->getMessage());
            return '';
        }
    }

    private function generateUnsubscribeLink(string $email, string $hash): string
    {
        return home_url('/?adp_action=unsubscribe&email=' . urlencode($email) . '&hash=' . $hash);
    }
}