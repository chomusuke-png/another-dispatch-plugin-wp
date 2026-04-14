<?php

declare(strict_types=1);

namespace Zumito\ADP\PublicHooks;

use Zumito\ADP\Core\Database;

class UnsubscribeHandler
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;

        add_action('template_redirect', [$this, 'handlePublicActions']);
    }

    public function handlePublicActions(): void
    {
        if (empty($_GET['adp_action'])) {
            return;
        }

        $action = sanitize_text_field(wp_unslash($_GET['adp_action']));

        if ($action !== 'verify' && $action !== 'unsubscribe') {
            return;
        }

        if (empty($_GET['email']) || empty($_GET['hash'])) {
            wp_die('Faltan parámetros requeridos.', 'Error de Parámetros', ['response' => 400]);
        }

        $email = sanitize_email(wp_unslash($_GET['email']));
        $hash  = sanitize_text_field(wp_unslash($_GET['hash']));

        $subscriber = $this->database->getSubscriber($email);

        if ($subscriber === null || $subscriber->hash !== $hash) {
            wp_die('El enlace de verificación es inválido o ha expirado.', 'Error de Verificación', ['response' => 403]);
        }

        if ($action === 'verify') {
            $this->processVerification($email, $subscriber);
        }

        if ($action === 'unsubscribe') {
            $this->processUnsubscribe($email);
        }
    }

    private function processVerification(string $email, object $subscriber): void
    {
        if ($subscriber->status === 'active') {
            wp_die('Tu correo electrónico ya se encuentra verificado y activo.', 'Ya estás suscrito');
        }

        $this->database->updateSubscriberStatus($email, 'active');

        $newHash = md5($email . time() . wp_salt());
        $this->database->updateSubscriberHash($email, $newHash);

        wp_die('¡Gracias! Tu suscripción ha sido confirmada exitosamente. Ya puedes cerrar esta ventana.', 'Suscripción Confirmada');
    }

    private function processUnsubscribe(string $email): void
    {
        $this->database->deleteSubscriberByEmail($email);

        wp_die('Te has dado de baja correctamente. No volverás a recibir correos de nuestra parte.', 'Baja Completada');
    }
}