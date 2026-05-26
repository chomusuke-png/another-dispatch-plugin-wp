<?php

declare(strict_types=1);

namespace Zumito\ADP\Admin;

use Zumito\ADP\Core\Database;

class AdminManager
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;

        add_action('admin_menu', [$this, 'addAdminMenu']);
    }

    public function addAdminMenu(): void
    {
        add_menu_page(
            'Dispatch',
            'Dispatch',
            'manage_options',
            'another-dispatch-plugin',
            [$this, 'renderDashboardPage'],
            'dashicons-email-alt',
            6
        );

        add_submenu_page(
            'another-dispatch-plugin',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'another-dispatch-plugin',
            [$this, 'renderDashboardPage']
        );

        add_submenu_page(
            'another-dispatch-plugin',
            'Estadísticas',
            'Statistics',
            'manage_options',
            'adp-statistics',
            [$this, 'renderStatisticsPage']
        );

        add_submenu_page(
            'another-dispatch-plugin',
            'Herramientas',
            'Tools',
            'manage_options',
            'adp-tools',
            [$this, 'renderToolsPage']
        );

        add_submenu_page(
            'another-dispatch-plugin',
            'Configuración Global',
            'Settings',
            'manage_options',
            'adp-settings',
            [$this, 'renderSettingsPage']
        );

        add_submenu_page(
            'another-dispatch-plugin',
            'Personalizar Diseño',
            'Mail Customizer',
            'manage_options',
            'adp-customizer',
            [$this, 'renderCustomizerPage']
        );

        add_submenu_page(
            'another-dispatch-plugin',
            'Estado del Sistema',
            'Debug / Logs',
            'manage_options',
            'adp-debug',
            [$this, 'renderDebugPage']
        );
    }

    public function renderDashboardPage(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access');
        }

        $currentFilter = isset($_GET['adp_filter']) ? sanitize_text_field(wp_unslash($_GET['adp_filter'])) : 'all';
        
        $perPage = 20;
        $currentPage = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($currentPage - 1) * $perPage;
        
        $subscribers  = $this->database->getSubscribers($perPage, $offset, $currentFilter);
        $countTotal   = $this->database->getSubscriberCount('all');
        $countActive  = $this->database->getSubscriberCount('active');
        $countPending = $this->database->getSubscriberCount('pending');
        $countBounced = $this->database->getSubscriberCount('bounced');

        $totalItems = $countTotal;
        if ($currentFilter === 'active') {
            $totalItems = $countActive;
        } elseif ($currentFilter === 'pending') {
            $totalItems = $countPending;
        } elseif ($currentFilter === 'bounced') {
            $totalItems = $countBounced;
        }

        $totalPages = (int) ceil($totalItems / $perPage);

        $message = '';
        $messageType = 'success';

        if (isset($_GET['adp_msg'])) {
            $messageCode = sanitize_text_field(wp_unslash($_GET['adp_msg']));
            $feedback = $this->getFeedbackMessage($messageCode);
            $message = $feedback['msg'];
            $messageType = $feedback['type'];
        }
        
        require ADP_PATH . 'templates/admin/dashboard.php';
    }

    public function renderStatisticsPage(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access');
        }

        $totalOpens = $this->database->getEventCount('open');
        $totalClicks = $this->database->getEventCount('click');
        $topLinks = $this->database->getTopClickedLinks(10);
        $recentEvents = $this->database->getRecentEvents(50);

        require ADP_PATH . 'templates/admin/statistics.php';
    }

    public function renderToolsPage(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access');
        }

        $message = '';
        $messageType = 'success';

        if (isset($_GET['adp_msg'])) {
            $messageCode = sanitize_text_field(wp_unslash($_GET['adp_msg']));
            $feedback = $this->getFeedbackMessage($messageCode);
            $message = $feedback['msg'];
            $messageType = $feedback['type'];
        }

        require ADP_PATH . 'templates/admin/tools.php';
    }

    public function renderSettingsPage(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access');
        }

        $message = '';
        if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
            $message = 'Configuración guardada correctamente.';
        }

        require ADP_PATH . 'templates/admin/settings.php';
    }

    public function renderCustomizerPage(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access');
        }

        $message = '';
        if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
            $message = 'Diseño actualizado correctamente.';
        }

        require ADP_PATH . 'templates/admin/customizer.php';
    }

    public function renderDebugPage(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access');
        }

        $isActionSchedulerActive = function_exists('as_get_scheduled_actions');
        $actionsSummary = []; 
        $recentActions  = [];

        if ($isActionSchedulerActive) {
            $statuses = ['pending', 'in-progress', 'complete', 'failed', 'canceled'];
            foreach ($statuses as $status) {
                $actions = as_get_scheduled_actions([
                    'group'    => 'adp_emails',
                    'status'   => $status,
                    'per_page' => -1
                ], 'ids');
                $actionsSummary[$status] = count($actions);
            }
            $recentActions = as_get_scheduled_actions([
                'group'    => 'adp_emails',
                'per_page' => 20,
                'orderby'  => 'date',
                'order'    => 'DESC'
            ]);
        }

        require ADP_PATH . 'templates/admin/debug.php';
    }

    private function getFeedbackMessage(string $code): array
    {
        $messages = [
            'deleted'                => ['msg' => 'Suscriptor eliminado.', 'type' => 'success'],
            'resent_success'         => ['msg' => 'Correo de verificación reenviado correctamente.', 'type' => 'success'],
            'test_success'           => ['msg' => 'Correo de prueba SMTP enviado.', 'type' => 'success'],
            'test_error'             => ['msg' => 'Error SMTP o Action Scheduler no activo.', 'type' => 'error'],
            'imap_test_success'      => ['msg' => 'Conexión IMAP establecida correctamente.', 'type' => 'success'],
            'imap_test_error'        => ['msg' => 'Error de conexión IMAP. Revisa las credenciales o puertos.', 'type' => 'error'],
            'imap_test_empty'        => ['msg' => 'Faltan datos para realizar la prueba IMAP.', 'type' => 'warning'],
            'digest_queued'          => ['msg' => 'Resumen encolado para envío.', 'type' => 'success'],
            'instant_queued'         => ['msg' => 'Envío inmediato encolado.', 'type' => 'success'],
            'retroactive_queued'     => ['msg' => 'Correos de bienvenida encolados masivamente.', 'type' => 'success'],
            'single_trigger_queued'  => ['msg' => 'El correo ha sido programado y enviado al destinatario.', 'type' => 'success'],
            'emergency_stop_success' => ['msg' => 'Todos los envíos en cola han sido detenidos y cancelados exitosamente.', 'type' => 'success'],
            'invalid_email'          => ['msg' => 'Debes proporcionar un correo electrónico válido.', 'type' => 'error'],
            'no_posts'               => ['msg' => 'No hay posts recientes para enviar.', 'type' => 'warning'],
            'imported'               => [
                'msg'  => 'Importación completada (' . (isset($_GET['count']) ? intval($_GET['count']) : 0) . ' registros).',
                'type' => 'success'
            ],
            'import_error'           => ['msg' => 'Error al subir el archivo.', 'type' => 'error'],
            'invalid_file'           => ['msg' => 'Archivo no válido. Solo CSV.', 'type' => 'error'],
        ];

        return $messages[$code] ?? ['msg' => 'Acción procesada.', 'type' => 'success'];
    }
}