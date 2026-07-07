<?php

declare(strict_types=1);

namespace Zumito\ADP\Bounces;

class BounceParser
{
    public static function findBouncedEmail($message): ?string
    {
        $body = $message->getTextBody() ?: '';
        $found = self::extractEmailFromBounce($body);
        if ($found) {
            return $found;
        }

        $htmlBody = $message->getHTMLBody() ?: '';
        $found = self::extractEmailFromBounce(strip_tags($htmlBody));
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
                $found = self::extractEmailFromBounce($partContent);
                if ($found) {
                    return $found;
                }
            }
        } catch (\Exception $e) {
            // Algunos mensajes no exponen adjuntos legibles; se ignora y se sigue con otras señales.
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
            // Sin header utilizable.
        }

        return null;
    }

    public static function extractEmailFromBounce(string $body): ?string
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
