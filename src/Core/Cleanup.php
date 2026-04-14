<?php

declare(strict_types=1);

namespace Zumito\ADP\Core;

class Cleanup
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;

        add_action('adp_daily_cleanup_event', [$this, 'processPendingExpiration']);
    }

    public function processPendingExpiration(): void
    {
        $expirationDays = (int) get_option('adp_pending_expiration_days', 30);

        if ($expirationDays > 0) {
            $this->database->deleteExpiredPending($expirationDays);
        }
    }
}