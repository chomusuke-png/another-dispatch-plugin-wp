<?php

declare(strict_types=1);

namespace Zumito\ADP\Core;

use wpdb;

class Database
{
    private wpdb $db;
    private string $tableName;

    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
        $this->tableName = $this->db->prefix . 'adp_subscribers';
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getSubscribers(int $limit, int $offset, string $status = 'all'): array
    {
        $query = "SELECT * FROM {$this->tableName}";
        $arguments = [];

        if ($status !== 'all') {
            $query .= " WHERE status = %s";
            $arguments[] = $status;
        }

        $query .= " ORDER BY created_at DESC";

        if ($limit > 0) {
            $query .= " LIMIT %d OFFSET %d";
            $arguments[] = $limit;
            $arguments[] = $offset;
        }

        if (!empty($arguments)) {
            $query = $this->db->prepare($query, ...$arguments);
        }

        $results = $this->db->get_results($query);

        return is_array($results) ? $results : [];
    }

    public function getSubscriberCount(string $status = 'all'): int
    {
        if ($status === 'all') {
            $query = "SELECT COUNT(id) FROM {$this->tableName}";
            $count = $this->db->get_var($query);
        } else {
            $query = "SELECT COUNT(id) FROM {$this->tableName} WHERE status = %s";
            $count = $this->db->get_var($this->db->prepare($query, $status));
        }

        return intval($count);
    }

    public function getSubscriber(string $email): ?object
    {
        $query = "SELECT * FROM {$this->tableName} WHERE email = %s LIMIT 1";
        $preparedQuery = $this->db->prepare($query, $email);
        $result = $this->db->get_row($preparedQuery);

        return $result ? clone $result : null;
    }

    public function addSubscriber(string $email, string $hash, string $status = 'pending'): bool
    {
        $inserted = $this->db->insert(
            $this->tableName,
            [
                'email'  => $email,
                'hash'   => $hash,
                'status' => $status
            ],
            ['%s', '%s', '%s']
        );

        return $inserted !== false;
    }

    public function updateSubscriberHash(string $email, string $hash): bool
    {
        $updated = $this->db->update(
            $this->tableName,
            ['hash' => $hash],
            ['email' => $email],
            ['%s'],
            ['%s']
        );

        return $updated !== false;
    }

    public function updateSubscriberStatus(string $email, string $status): bool
    {
        $updated = $this->db->update(
            $this->tableName,
            ['status' => $status],
            ['email' => $email],
            ['%s'],
            ['%s']
        );

        return $updated !== false;
    }

    public function deleteSubscriberById(int $subscriberId): bool
    {
        $deleted = $this->db->delete(
            $this->tableName,
            ['id' => $subscriberId],
            ['%d']
        );

        return $deleted !== false;
    }

    public function deleteSubscriberByEmail(string $email): bool
    {
        $deleted = $this->db->delete(
            $this->tableName,
            ['email' => $email],
            ['%s']
        );

        return $deleted !== false;
    }

    public function bulkInsert(array $emails): int
    {
        if (empty($emails)) {
            return 0;
        }

        $values = [];
        $placeholders = [];
        $status = 'active'; 

        foreach ($emails as $email) {
            $hash = md5($email . time() . wp_salt());

            $values[] = $email;
            $values[] = $hash;
            $values[] = $status;

            $placeholders[] = "(%s, %s, %s)";
        }

        $query = "INSERT IGNORE INTO {$this->tableName} (email, hash, status) VALUES " . implode(', ', $placeholders);

        $preparedQuery = $this->db->prepare($query, ...$values);

        $affectedRows = $this->db->query($preparedQuery);

        return is_numeric($affectedRows) ? (int)$affectedRows : 0;
    }

    public function getAllSubscribersForExport(): array
    {
        $query = "SELECT email, status, created_at FROM {$this->tableName} ORDER BY created_at DESC";
        $results = $this->db->get_results($query, ARRAY_N);

        return is_array($results) ? $results : [];
    }

    public function deleteExpiredPending(int $daysLimit): int
    {
        if ($daysLimit <= 0) {
            return 0;
        }

        $query = "DELETE FROM {$this->tableName} WHERE status = 'pending' AND created_at < DATE_SUB(NOW(), INTERVAL %d DAY)";
        $preparedQuery = $this->db->prepare($query, $daysLimit);
        
        $deletedCount = $this->db->query($preparedQuery);

        return is_numeric($deletedCount) ? (int)$deletedCount : 0;
    }
}