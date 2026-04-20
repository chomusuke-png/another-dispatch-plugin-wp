<?php

declare(strict_types=1);

namespace Zumito\ADP\Emails;

use PHPMailer\PHPMailer\PHPMailer;
use Zumito\ADP\Core\Logger;
use WP_Error;

class SmtpConfig
{
    public function __construct()
    {
        add_action('phpmailer_init', [$this, 'configureSmtp']);
        add_action('wp_mail_failed', [$this, 'logSmtpError']);
    }

    public function configureSmtp(PHPMailer $phpmailer): void
    {
        $smtpHost = get_option('adp_smtp_host');

        if (empty($smtpHost)) {
            return;
        }

        $phpmailer->isSMTP();
        $phpmailer->Host = $smtpHost;
        $phpmailer->SMTPAuth = true;
        $phpmailer->Port = (int) get_option('adp_smtp_port', 587);
        $phpmailer->Username = get_option('adp_smtp_user');
        $phpmailer->Password = get_option('adp_smtp_pass');
        $phpmailer->SMTPSecure = get_option('adp_smtp_secure', 'tls');

        $senderEmail = get_option('adp_sender_email');
        if (!empty($senderEmail)) {
            $phpmailer->From = $senderEmail;
            $phpmailer->FromName = get_bloginfo('name');
        }

        $bounceEmail = get_option('adp_bounce_email', $senderEmail);
        if (!empty($bounceEmail)) {
            $phpmailer->Sender = $bounceEmail; 
        }
    }

    public function logSmtpError(WP_Error $error): void
    {
        $errorMessage = $error->get_error_message();
        $errorData = $error->get_error_data('wp_mail_failed');
        
        $logDetails = "Error Interno SMTP: " . $errorMessage;
        
        if (!empty($errorData) && is_array($errorData)) {
            if (isset($errorData['phpmailer_exception_code'])) {
                $logDetails .= " (Código PHPMailer: " . $errorData['phpmailer_exception_code'] . ")";
            }
        }

        Logger::error($logDetails);
    }
}