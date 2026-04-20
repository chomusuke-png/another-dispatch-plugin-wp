<?php

declare(strict_types=1);

namespace Zumito\ADP\Admin;

use Zumito\ADP\Core\Database;
use Zumito\ADP\Core\Logger;
use Webklex\PHPIMAP\ClientManager;

class AdminActions
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
        add_action('admin_init', [$this, 'handleRequests']);
    }

    public function handleRequests(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $this->processCsvExport();
        $this->processCsvImport();
        $this->processSubscriberDeletion();
        $this->processVerificationResend();
        $this->processSmtpTest();
        $this->processImapTest();
        $this->processUnifiedTrigger();
        $this->processEmergencyStop();
    }

    private function processEmergencyStop(): void
    {
        if (!isset($_POST['adp_emergency_stop_submit'])) {
            return;
        }

        check_admin_referer('adp_emergency_stop_action', 'adp_emergency_stop_nonce');

        if (!function_exists('as_unschedule_all_actions')) {
            $this->redirectWithMessage('test_error');
        }

        $hooksToClear = [
            'adp_process_batch_send',
            'adp_process_retroactive_welcome_batch',
            'adp_weekly_digest_event',
            'adp_monthly_digest_event',
            'adp_send_welcome_email_event',
            'adp_send_verification_email_event',
            'adp_send_single_trigger_event'
        ];

        foreach ($hooksToClear as $hook) {
            as_unschedule_all_actions($hook, [], 'adp_emails');
        }

        Logger::error('EMERGENCIA: El administrador ha activado el Botón de Pánico. Todos los envíos en cola han sido cancelados.');

        $this->redirectWithMessage('emergency_stop_success');
    }

    private function processUnifiedTrigger(): void
    {
        if (!isset($_POST['adp_unified_trigger_submit'])) {
            return;
        }

        check_admin_referer('adp_unified_trigger_action', 'adp_unified_trigger_nonce');

        if (!function_exists('as_schedule_single_action')) {
            $this->redirectWithMessage('test_error');
        }

        $type = sanitize_text_field(wp_unslash($_POST['adp_trigger_type'] ?? 'post'));
        $target = sanitize_text_field(wp_unslash($_POST['adp_trigger_target'] ?? 'single'));

        if ($target === 'single') {
            $email = sanitize_email(wp_unslash($_POST['adp_single_email'] ?? ''));
            if (!is_email($email)) {
                $this->redirectWithMessage('invalid_email');
            }

            as_schedule_single_action(time(), 'adp_send_single_trigger_event', ['email' => $email, 'type' => $type], 'adp_emails');
            $this->redirectWithMessage('single_trigger_queued');
        } else {
            if ($type === 'welcome') {
                as_enqueue_async_action('adp_process_retroactive_welcome_batch', ['offset' => 0], 'adp_emails');
                $this->redirectWithMessage('retroactive_queued');
            } elseif ($type === 'monthly') {
                as_schedule_single_action(time(), 'adp_monthly_digest_event', [0], 'adp_emails');
                $this->redirectWithMessage('digest_queued');
            } elseif ($type === 'weekly') {
                as_schedule_single_action(time(), 'adp_weekly_digest_event', [0], 'adp_emails');
                $this->redirectWithMessage('digest_queued');
            } else {
                $latestPosts = get_posts(['numberposts' => 1, 'post_status' => 'publish']);
                if (!empty($latestPosts)) {
                    as_schedule_single_action(
                        time(), 
                        'adp_process_batch_send', 
                        ['post_id' => $latestPosts[0]->ID, 'offset' => 0], 
                        'adp_emails'
                    );
                    $this->redirectWithMessage('instant_queued');
                } else {
                    $this->redirectWithMessage('no_posts');
                }
            }
        }
    }

    private function processCsvExport(): void
    {
        if (!isset($_POST['adp_action_export_csv'])) {
            return;
        }

        check_admin_referer('adp_export_csv', 'adp_export_nonce');

        $filename = 'adp-subscribers-' . gmdate('Y-m-d') . '.csv';
        $subscribersData = $this->database->getAllSubscribersForExport();

        if (ob_get_length() > 0) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $outputStream = fopen('php://output', 'w');
        
        if ($outputStream === false) {
            wp_die('Error al generar el archivo de exportación.');
        }

        fputcsv($outputStream, ['Email', 'Estado', 'Fecha Suscripción']);

        foreach ($subscribersData as $row) {
            fputcsv($outputStream, $row);
        }

        fclose($outputStream);
        exit;
    }

    private function processCsvImport(): void
    {
        if (!isset($_POST['adp_action_import_csv'])) {
            return;
        }

        check_admin_referer('adp_import_csv', 'adp_import_nonce');

        if (empty($_FILES['adp_import_file']['tmp_name'])) {
            $this->redirectWithMessage('import_error');
        }

        $fileArray = wp_unslash($_FILES['adp_import_file']);
        
        $fileType = wp_check_filetype(basename($fileArray['name']));
        if ($fileType['ext'] !== 'csv') {
            $this->redirectWithMessage('invalid_file');
        }

        $tmpName = sanitize_text_field($fileArray['tmp_name']);
        $fileStream = fopen($tmpName, 'r');
        
        if ($fileStream === false) {
            $this->redirectWithMessage('import_error');
        }

        $chunk = [];
        $totalImported = 0;
        
        while (($csvData = fgetcsv($fileStream, 2000, ',')) !== false) {
            $rawEmail = isset($csvData[0]) ? trim($csvData[0]) : '';
            $email = sanitize_email($rawEmail);
            
            if (is_email($email)) {
                $chunk[] = $email;
            }

            if (count($chunk) >= 100) {
                $totalImported += $this->database->bulkInsert($chunk);
                $chunk = []; 
            }
        }
        
        if (!empty($chunk)) {
            $totalImported += $this->database->bulkInsert($chunk);
        }

        fclose($fileStream);

        $this->redirectWithMessage('imported', ['count' => $totalImported]);
    }

    private function processSubscriberDeletion(): void
    {
        $action = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : '';
        $subscriberId = isset($_GET['subscriber_id']) ? intval($_GET['subscriber_id']) : 0;

        if ($action === 'adp_delete_subscriber' && $subscriberId > 0) {
            check_admin_referer('adp_delete_subscriber_action');
            $this->database->deleteSubscriberById($subscriberId);
            $this->redirectWithMessage('deleted');
        }
    }

    private function processVerificationResend(): void
    {
        $action = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : '';
        
        if ($action === 'adp_resend_verification' && isset($_GET['email'])) {
            check_admin_referer('adp_resend_action');
            
            $email = sanitize_email(wp_unslash($_GET['email']));
            $subscriber = $this->database->getSubscriber($email);

            if ($subscriber && $subscriber->status === 'pending') {
                $newHash = md5($email . time() . wp_salt());
                $this->database->updateSubscriberHash($email, $newHash);

                if (function_exists('as_enqueue_async_action')) {
                    as_enqueue_async_action(
                        'adp_send_verification_email_event', 
                        ['email' => $email, 'hash' => $newHash], 
                        'adp_emails'
                    );
                }
                $this->redirectWithMessage('resent_success');
            }
        }
    }

    private function processSmtpTest(): void
    {
        if (!isset($_POST['adp_test_email_submit'])) {
            return;
        }

        check_admin_referer('adp_send_test_email', 'adp_test_email_nonce');

        $currentUserEmail = wp_get_current_user()->user_email;
        $subject = 'Prueba SMTP ADP';
        $htmlMessage = '<h1>¡Funciona!</h1><p>Configuración correcta.</p>';
        
        $fromEmail = get_option('adp_sender_email') ?: get_option('admin_email');
        $blogName = get_bloginfo('name');
        $headers = [
            'Content-Type: text/html; charset=UTF-8', 
            'From: ' . $blogName . ' <' . $fromEmail . '>'
        ];
        
        $isSent = wp_mail($currentUserEmail, $subject, $htmlMessage, $headers);
        $this->redirectWithMessage($isSent ? 'test_success' : 'test_error');
    }

    private function processImapTest(): void
    {
        if (!isset($_POST['adp_test_imap_submit'])) {
            return;
        }

        check_admin_referer('adp_test_imap_action', 'adp_test_imap_nonce');

        $host = get_option('adp_imap_host');
        $user = get_option('adp_imap_user');
        $pass = get_option('adp_imap_pass');
        $port = (int) get_option('adp_imap_port', 993);

        if (empty($host) || empty($user) || empty($pass)) {
            $this->redirectWithMessage('imap_test_empty');
        }

        try {
            $clientManager = new ClientManager();
            $client = $clientManager->make([
                'host'          => $host,
                'port'          => $port,
                'encryption'    => 'ssl',
                'validate_cert' => false,
                'username'      => $user,
                'password'      => $pass,
                'protocol'      => 'imap'
            ]);

            $client->connect();
            $client->disconnect();

            $this->redirectWithMessage('imap_test_success');
        } catch (\Exception $e) {
            Logger::error('ADP IMAP Test Error: ' . $e->getMessage());
            $this->redirectWithMessage('imap_test_error');
        }
    }

    private function redirectWithMessage(string $messageCode, array $additionalArgs = []): void
    {
        $targetPage = 'another-dispatch-plugin';
        if (isset($_POST['adp_redirect_to'])) {
            $targetPage = sanitize_text_field(wp_unslash($_POST['adp_redirect_to']));
        } elseif (isset($_REQUEST['page'])) {
            $targetPage = sanitize_text_field(wp_unslash($_REQUEST['page']));
        }

        $queryArguments = array_merge([
            'page' => $targetPage, 
            'adp_msg' => $messageCode
        ], $additionalArgs);
        
        wp_safe_redirect(add_query_arg($queryArguments, admin_url('admin.php')));
        exit;
    }
}