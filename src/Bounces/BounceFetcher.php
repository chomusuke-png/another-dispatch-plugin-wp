<?php

declare(strict_types=1);

namespace Zumito\ADP\Bounces;

use Zumito\ADP\Core\Database;
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

            $messages = $folder->query()->all()->get();

            foreach ($messages as $message) {
                $body = $message->getTextBody() ?: $message->getHTMLBody();
                
                $bouncedEmail = $this->extractEmailFromBounce($body);

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
            }

            $client->expunge();
            $client->disconnect();

        } catch (\Exception $e) {
            error_log('ADP IMAP Bounce Error: ' . $e->getMessage());
        }
    }

    private function extractEmailFromBounce(string $body): ?string
    {
        $pattern = '/Final-Recipient:\s*rfc822;\s*([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/i';
        if (preg_match($pattern, $body, $matches)) {
            return sanitize_email($matches[1]);
        }

        $fallbackPattern = '/(?:failed to deliver to|could not be delivered to|delivery failed.*?)\s*<?([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})>?/i';
        if (preg_match($fallbackPattern, $body, $matches)) {
            return sanitize_email($matches[1]);
        }

        return null;
    }
}