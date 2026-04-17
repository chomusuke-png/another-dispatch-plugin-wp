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
        add_action('adp_send_welcome_email_event', [$this, 'sendWelcomeEmail'], 10, 2);
        add_action('adp_weekly_digest_event', [$this, 'processDigestSend'], 10, 1);
        add_action('adp_monthly_digest_event', [$this, 'processDigestSend'], 10, 1);
        add_action('adp_process_retroactive_welcome_batch', [$this, 'processRetroactiveWelcomeBatch'], 10, 1);
        add_action('adp_send_single_trigger_event', [$this, 'processSingleTriggerSend'], 10, 2);
    }

    public function processSingleTriggerSend(string $email, string $type): void
    {
        $subscriber = $this->database->getSubscriber($email);
        $hash = $subscriber ? (string) $subscriber->hash : md5($email . time() . wp_salt());

        if ($type === 'welcome') {
            $this->sendWelcomeEmail($email, $hash);
        } elseif ($type === 'monthly' || $type === 'weekly') {
            $dateQueryStr = $type === 'monthly' ? '1 month ago' : '1 week ago';
            $recentPosts = get_posts([
                'post_status' => 'publish',
                'date_query'  => [['after' => $dateQueryStr]]
            ]);

            if (!empty($recentPosts)) {
                $this->dispatchDigestEmail($recentPosts, $email, $hash);
            }
        } else {
            $latestPosts = get_posts(['numberposts' => 1, 'post_status' => 'publish']);
            if (!empty($latestPosts)) {
                $this->dispatchPostEmail($latestPosts[0], $email, $hash);
            }
        }
    }

    public function processRetroactiveWelcomeBatch(int $offset): void
    {
        $batchSize = 100;
        $subscribers = $this->database->getSubscribers($batchSize, $offset, 'active');

        if (empty($subscribers)) {
            return;
        }

        foreach ($subscribers as $subscriber) {
            $hash = isset($subscriber->hash) ? (string) $subscriber->hash : '';
            
            if (function_exists('as_enqueue_async_action')) {
                as_enqueue_async_action(
                    'adp_send_welcome_email_event',
                    ['email' => $subscriber->email, 'hash' => $hash],
                    'adp_emails'
                );
            }
        }

        if (count($subscribers) === $batchSize && function_exists('as_enqueue_async_action')) {
            as_enqueue_async_action(
                'adp_process_retroactive_welcome_batch',
                ['offset' => $offset + $batchSize],
                'adp_emails'
            );
        }
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

        foreach ($subscribers as $subscriber) {
            $hash = isset($subscriber->hash) ? (string) $subscriber->hash : '';
            $this->dispatchPostEmail($post, $subscriber->email, $hash);
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
            $hash = isset($subscriber->hash) ? (string) $subscriber->hash : '';
            $this->dispatchDigestEmail($recentPosts, $subscriber->email, $hash);
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

    public function sendWelcomeEmail(string $email, string $hash): void
    {
        $subject = get_option('adp_welcome_subject', '¡Bienvenido a nuestro Newsletter!');
        $content = get_option('adp_welcome_content', '<p>Gracias por confirmar tu suscripción. Estamos felices de tenerte con nosotros.</p>');
        $unsubscribeLink = $this->generateUnsubscribeLink($email, $hash);

        $htmlContent = $this->renderTemplate('welcome', [
            'emailTitle'      => $subject,
            'welcomeContent'  => wpautop(wp_kses_post($content)),
            'unsubscribeLink' => $unsubscribeLink,
            'blogName'        => get_bloginfo('name')
        ]);

        $finalHtml = $this->injectTracking($htmlContent, $email);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'List-Unsubscribe: <' . $unsubscribeLink . '>'
        ];

        $sent = wp_mail($email, $subject, $finalHtml, $headers);

        if ($sent) {
            $this->database->updateSubscriberStatus($email, 'active');
        } else {
            error_log('ADP Error: No se pudo enviar el correo de bienvenida a ' . $email);
        }
    }

    private function dispatchPostEmail(\WP_Post $post, string $email, string $hash): void
    {
        $unsubscribeLink = $this->generateUnsubscribeLink($email, $hash);
        $featuredImage = has_post_thumbnail($post->ID) ? get_the_post_thumbnail_url($post->ID, 'large') : '';

        $htmlContent = $this->renderTemplate('single', [
            'postTitle'       => get_the_title($post->ID),
            'postContent'     => apply_filters('the_content', $post->post_content),
            'postLink'        => get_permalink($post->ID),
            'featuredImage'   => $featuredImage,
            'unsubscribeLink' => $unsubscribeLink,
            'blogName'        => get_bloginfo('name')
        ]);

        $finalHtml = $this->injectTracking($htmlContent, $email);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'List-Unsubscribe: <' . $unsubscribeLink . '>'
        ];

        wp_mail($email, get_the_title($post->ID), $finalHtml, $headers);
    }

    private function dispatchDigestEmail(array $posts, string $email, string $hash): void
    {
        $unsubscribeLink = $this->generateUnsubscribeLink($email, $hash);
        
        $htmlContent = $this->renderTemplate('digest', [
            'emailTitle'      => 'Resumen de Artículos - ' . get_bloginfo('name'),
            'postsList'       => $posts,
            'unsubscribeLink' => $unsubscribeLink,
            'blogName'        => get_bloginfo('name')
        ]);

        $finalHtml = $this->injectTracking($htmlContent, $email);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'List-Unsubscribe: <' . $unsubscribeLink . '>'
        ];

        wp_mail($email, 'Resumen de Artículos - ' . get_bloginfo('name'), $finalHtml, $headers);
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
            if (ob_get_level() > 0) ob_end_clean();
            error_log('ADP Template Error: ' . $exception->getMessage());
            return '';
        }
    }

    private function generateUnsubscribeLink(string $email, string $hash): string
    {
        return home_url('/?adp_action=unsubscribe&email=' . urlencode($email) . '&hash=' . $hash);
    }

    private function injectTracking(string $html, string $email): string
    {
        $encodedEmail = urlencode($email);

        $html = preg_replace_callback('/href=["\'](http[s]?:\/\/[^"\']+)["\']/i', function ($matches) use ($encodedEmail) {
            $originalUrl = $matches[1];
            if (strpos($originalUrl, 'adp_action=') !== false) return $matches[0];
            $trackingUrl = home_url('/?adp_action=track_click&email=' . $encodedEmail . '&url=' . urlencode($originalUrl));
            return 'href="' . esc_url($trackingUrl) . '"';
        }, $html);

        $pixelUrl = home_url('/?adp_action=track_open&email=' . $encodedEmail);
        $pixelImg = '<img src="' . esc_url($pixelUrl) . '" width="1" height="1" style="display:none !important; border:none; margin:0; padding:0;" alt="" />';
        
        if (stripos($html, '</body>') !== false) {
            $html = str_ireplace('</body>', $pixelImg . PHP_EOL . '</body>', $html);
        } else {
            $html .= $pixelImg;
        }

        return $html;
    }
}