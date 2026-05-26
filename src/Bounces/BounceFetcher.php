<?php

declare(strict_types=1);

namespace Zumito\ADP\Bounces;

use Zumito\ADP\Core\Database;
use Zumito\ADP\Core\Logger;
use Webklex\PHPIMAP\ClientManager;

class BounceFetcher
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
        add_action('adp_process_bounces_event', [$this, 'processInbox']);
    }

    public function processInbox(): void
    {
        $host = get_option('adp_imap_host');
        $user = get_option('adp_imap_user');
        $pass = get_option('adp_imap_pass');
        $port = (int) get_option('adp_imap_port', 993);

        if (empty($host) || empty($user) || empty($pass)) {
            return;
        }

        try {
            $cm = new ClientManager();
            $client = $cm->make([
                'host'          => $host,
                'port'          => $port,
                'encryption'    => 'ssl',
                'validate_cert' => false,
                'username'      => $user,
                'password'      => $pass,
                'protocol'      => 'imap'
            ]);

            $client->connect();
            $folder = $client->getFolder('INBOX');

            $folder->query()->all()->setFetchBody(false)->chunk(25, function ($messages) {
                foreach ($messages as $message) {
                    try {
                        $bouncedEmail = $this->findBouncedEmail($message);

                        if ($bouncedEmail) {
                            $subscriber = $this->database->getSubscriber($bouncedEmail);
                            if ($subscriber !== null && $subscriber->status !== 'bounced') {
                                $this->database->updateSubscriberStatus($bouncedEmail, 'bounced');
                                Logger::info("Bounce procesado: $bouncedEmail");
                            }
                        } else {
                            $subject = $message->getSubject() ?? '(sin asunto)';
                            Logger::warning("Mensaje IMAP no reconocido como bounce. Asunto: $subject");
                        }

                        $message->delete();

                    } catch (\Exception $e) {
                        Logger::error("Error procesando mensaje IMAP individual: " . $e->getMessage());
                    }
                }
            });

            $client->expunge();
            $client->disconnect();

        } catch (\Exception $e) {
            Logger::error('ADP IMAP Bounce Error: ' . $e->getMessage());
        }
    }

    private function findBouncedEmail($message): ?string
    {
        $body = $message->getTextBody() ?: '';
        $found = $this->extractEmailFromBounce($body);
        if ($found) {
            return $found;
        }

        $htmlBody = $message->getHTMLBody() ?: '';
        $found = $this->extractEmailFromBounce(strip_tags($htmlBody));
        if ($found) {
            return $found;
        }

        try {
            $attachments = $message->getAttachments();
            foreach ($attachments as $attachment) {
                $partContent = $attachment->getContent();
                if (empty($partContent)) {
                    continue;
                }
                $found = $this->extractEmailFromBounce($partContent);
                if ($found) {
                    return $found;
                }
            }
        } catch (\Exception $e) {
            Logger::warning("No se pudieron leer attachments del mensaje: " . $e->getMessage());
        }

        try {
            $headers = $message->getHeader();
            if ($headers) {
                $xFailed = $headers->get('x-failed-recipients');
                if ($xFailed) {
                    $raw = is_object($xFailed) ? $xFailed->first() : (string) $xFailed;
                    $email = sanitize_email(trim((string) $raw));
                    if (is_email($email)) {
                        return $email;
                    }
                }
            }
        } catch (\Exception $e) {
            // e
        }

        return null;
    }

    private function extractEmailFromBounce(string $body): ?string
    {
        if (empty($body)) {
            return null;
        }

        if (preg_match('/Final-Recipient:\s*rfc822;\s*([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})/i', $body, $matches)) {
            return sanitize_email($matches[1]);
        }

        if (preg_match('/Original-Recipient:\s*rfc822;\s*([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})/i', $body, $matches)) {
            return sanitize_email($matches[1]);
        }

        if (preg_match('/(?:failed to deliver(?:y)? to|could not be delivered to|delivery failed.*?|no such user.*?|usuario desconocido.*?|buzón no encontrado.*?)\s*<?([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})>?/i', $body, $matches)) {
            return sanitize_email($matches[1]);
        }

        return null;
    }
}