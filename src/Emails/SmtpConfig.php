<?php

declare(strict_types=1);

namespace Zumito\ADP\Emails;

use PHPMailer\PHPMailer\PHPMailer;

class SmtpConfig
{
    public function __construct()
    {
        add_action('phpmailer_init', [$this, 'configureSmtp']);
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
}