<?php

declare(strict_types=1);

namespace Zumito\ADP\Bounces;

use Zumito\ADP\Core\Crypto;
use Zumito\ADP\Core\Database;
use Zumito\ADP\Core\Logger;
use Webklex\PHPIMAP\Client;

class BounceReviewer
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function listPendingMessages(int $limit = 50): array
    {
        $client = $this->connect();

        if ($client === null) {
            return [];
        }

        $items = [];

        try {
            $folder = $client->getFolder(BounceFetcher::UNPROCESSED_FOLDER);

            if ($folder !== null) {
                $messages = $folder->query()->all()->get();
                $count = 0;

                foreach ($messages as $message) {
                    if ($count >= $limit) {
                        break;
                    }

                    $items[] = [
                        'uid'          => (int) $message->getUid(),
                        'subject'      => $this->readHeaderValue($message, 'subject') ?: '(sin asunto)',
                        'from'         => $this->readHeaderValue($message, 'from'),
                        'date'         => $this->readHeaderValue($message, 'date'),
                        'snippet'      => $this->buildSnippet($message),
                        'guessedEmail' => BounceParser::findBouncedEmail($message) ?? '',
                    ];

                    $count++;
                }
            }

            $client->disconnect();
        } catch (\Exception $e) {
            Logger::error('ADP Bounce Review Error: ' . $e->getMessage());
        }

        return $items;
    }

    public function resolveMessage(int $uid, string $decision, string $email): bool
    {
        if ($uid <= 0 || !in_array($decision, ['bounced', 'active', 'discard'], true)) {
            return false;
        }

        $client = $this->connect();

        if ($client === null) {
            return false;
        }

        $resolved = false;

        try {
            $folder = $client->getFolder(BounceFetcher::UNPROCESSED_FOLDER);

            if ($folder !== null) {
                $message = $folder->query()->whereUid($uid)->get()->first();

                if ($message !== null) {
                    if ($decision === 'bounced') {
                        $this->database->updateSubscriberStatus($email, 'bounced');
                        Logger::info("Bounce confirmado manualmente desde revisión: $email");
                    } elseif ($decision === 'active') {
                        $this->database->updateSubscriberStatus($email, 'active');
                        Logger::info("Suscriptor reactivado manualmente desde revisión de bounces: $email");
                    }

                    $message->delete();
                    $resolved = true;
                }
            }

            $client->expunge();
            $client->disconnect();
        } catch (\Exception $e) {
            Logger::error('ADP Bounce Review Error: ' . $e->getMessage());
        }

        return $resolved;
    }

    private function connect(): ?Client
    {
        $host = get_option('adp_imap_host');
        $user = get_option('adp_imap_user');
        $pass = Crypto::decrypt((string) get_option('adp_imap_pass'));
        $port = (int) get_option('adp_imap_port', 993);

        if (empty($host) || empty($user) || empty($pass)) {
            return null;
        }

        try {
            return BounceFetcher::connect($host, $port, $user, $pass);
        } catch (\Exception $e) {
            Logger::error('ADP Bounce Review IMAP Error: ' . $e->getMessage());
            return null;
        }
    }

    private function readHeaderValue($message, string $name): string
    {
        try {
            $headers = $message->getHeader();

            if (!$headers) {
                return '';
            }

            $value = $headers->get($name);

            if (!$value) {
                return '';
            }

            $raw = is_object($value) ? $value->first() : (string) $value;

            return sanitize_text_field((string) $raw);
        } catch (\Exception $e) {
            return '';
        }
    }

    private function buildSnippet($message): string
    {
        $text = $message->getTextBody() ?: strip_tags((string) $message->getHTMLBody());
        $text = trim((string) preg_replace('/\s+/', ' ', (string) $text));

        return mb_substr($text, 0, 300);
    }
}
