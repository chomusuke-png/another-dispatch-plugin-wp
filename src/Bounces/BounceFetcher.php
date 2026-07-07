<?php

declare(strict_types=1);

namespace Zumito\ADP\Bounces;

use Zumito\ADP\Core\Crypto;
use Zumito\ADP\Core\Database;
use Zumito\ADP\Core\Logger;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\ClientManager;

class BounceFetcher
{
    // Buzón donde se guardan los correos que no se pudieron identificar automáticamente
    // como un rebote, para que un administrador los revise manualmente.
    public const UNPROCESSED_FOLDER = 'ADP-Sin-Procesar';

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
        $pass = Crypto::decrypt((string) get_option('adp_imap_pass'));
        $port = (int) get_option('adp_imap_port', 993);

        if (empty($host) || empty($user) || empty($pass)) {
            return;
        }

        try {
            $client = self::connect($host, $port, $user, $pass);
            $folder = $client->getFolder('INBOX');

            self::ensureFolderExists($client, self::UNPROCESSED_FOLDER);

            $folder->query()->all()->chunk(25, function ($messages) {
                foreach ($messages as $message) {
                    try {
                        $bouncedEmail = BounceParser::findBouncedEmail($message);

                        if ($bouncedEmail) {
                            $subscriber = $this->database->getSubscriber($bouncedEmail);
                            if ($subscriber !== null && $subscriber->status !== 'bounced') {
                                $this->database->updateSubscriberStatus($bouncedEmail, 'bounced');
                                Logger::info("Bounce procesado: $bouncedEmail");
                            }

                            $message->delete();
                        } else {
                            $subject = $message->getSubject() ?? '(sin asunto)';
                            Logger::warning("Mensaje IMAP no reconocido como bounce. Movido a '" . self::UNPROCESSED_FOLDER . "' para revisión manual. Asunto: $subject");
                            $message->move(self::UNPROCESSED_FOLDER);
                        }

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

    public static function connect(string $host, int $port, string $user, string $pass): Client
    {
        $clientManager = new ClientManager();
        $client = $clientManager->make([
            'host'          => $host,
            'port'          => $port,
            'encryption'    => 'ssl',
            'validate_cert' => true,
            'username'      => $user,
            'password'      => $pass,
            'protocol'      => 'imap'
        ]);

        $client->connect();

        return $client;
    }

    public static function ensureFolderExists(Client $client, string $folderPath): void
    {
        if ($client->getFolder($folderPath) === null) {
            $client->createFolder($folderPath);
        }
    }
}
