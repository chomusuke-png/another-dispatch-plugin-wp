<?php

declare(strict_types=1);

namespace Zumito\ADP\Admin;

use ActionScheduler;
use Exception;

class AdminAjax
{
    public function __construct()
    {
        add_action('wp_ajax_adp_refresh_debug_stats', [$this, 'refreshDebugStats']);
        add_action('wp_ajax_adp_render_preview', [$this, 'renderPreview']);
    }

    public function renderPreview(): void
    {
        try {
            check_ajax_referer('adp_debug_refresh_nonce', 'nonce');

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Unauthorized access'], 403);
            }

            $mode = isset($_POST['mode']) ? sanitize_text_field(wp_unslash($_POST['mode'])) : 'single';
            $blogName = get_bloginfo('name');
            $isPreview = true;
            $unsubscribeLink = '#';

            ob_start();

            switch ($mode) {
                case 'digest':
                    $emailTitle = 'Resumen Semanal';
                    $postsList = [
                        (object) [
                            'ID'           => 0,
                            'post_title'   => 'Cómo optimizar WordPress en 2026',
                            'post_excerpt' => 'Descubre las nuevas técnicas de caché...',
                            'mock_link'    => '#'
                        ],
                        (object) [
                            'ID'           => 0,
                            'post_title'   => 'Tendencias de Diseño Web',
                            'post_excerpt' => 'El minimalismo extremo vuelve a estar de moda...',
                            'mock_link'    => '#'
                        ],
                    ];
                    require ADP_PATH . 'templates/emails/digest.php';
                    break;
                
                case 'welcome':
                    $emailTitle = get_option('adp_welcome_subject', '¡Bienvenido a nuestro Newsletter!');
                    $welcomeContent = wpautop(wp_kses_post(get_option('adp_welcome_content', '<p>Gracias por confirmar tu suscripción. Estamos felices de tenerte con nosotros.</p>')));
                    require ADP_PATH . 'templates/emails/welcome.php';
                    break;

                case 'verification':
                    $emailTitle = 'Confirma tu suscripción';
                    $verificationLink = '#';
                    require ADP_PATH . 'templates/emails/verification.php';
                    break;

                case 'single':
                default:
                    $postTitle = 'Bienvenido a nuestro Newsletter';
                    $postContent = '<p>Este es un ejemplo de cómo se verán tus correos.</p><p>Puedes personalizar los colores, el logo y el pie de página desde el panel de la izquierda. Los cambios se reflejan en tiempo real.</p>';
                    $postLink = '#';
                    $featuredImage = '';
                    require ADP_PATH . 'templates/emails/single.php';
                    break;
            }

            $htmlContent = ob_get_clean();

            if ($htmlContent === false) {
                throw new Exception('Failed to generate preview HTML content.');
            }

            wp_send_json_success(['html' => $htmlContent]);
        } catch (Exception $exception) {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            wp_send_json_error(['message' => $exception->getMessage()], 500);
        }
    }

    public function refreshDebugStats(): void
    {
        try {
            check_ajax_referer('adp_debug_refresh_nonce', 'nonce');

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Unauthorized access'], 403);
            }

            if (!function_exists('as_get_scheduled_actions')) {
                throw new Exception('Action Scheduler dependency is missing or not initialized.');
            }

            $statuses = ['pending', 'in-progress', 'complete', 'failed'];
            $statistics = [];

            foreach ($statuses as $status) {
                $actions = as_get_scheduled_actions(
                    [
                        'group'    => 'adp_emails',
                        'status'   => $status,
                        'per_page' => -1
                    ],
                    'ids'
                );
                $statistics[$status] = count($actions);
            }

            $recentActions = as_get_scheduled_actions([
                'group'    => 'adp_emails',
                'per_page' => 20,
                'orderby'  => 'date',
                'order'    => 'DESC'
            ]);

            ob_start();
            $this->renderTableRows($recentActions);
            $tableHtml = ob_get_clean();

            if ($tableHtml === false) {
                throw new Exception('Failed to generate debug table HTML.');
            }

            wp_send_json_success([
                'stats'      => $statistics,
                'table_html' => $tableHtml
            ]);
        } catch (Exception $exception) {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            wp_send_json_error(['message' => $exception->getMessage()], 500);
        }
    }

    private function renderTableRows(array $actions): void
    {
        if (empty($actions)) {
            echo '<tr><td colspan="5">No hay actividad reciente.</td></tr>';
            return;
        }

        foreach ($actions as $actionId => $action) {
            $hookName = $action->get_hook();
            $arguments = $action->get_args();
            $schedule = $action->get_schedule();
            $nextRunDate = $schedule->get_date();
            
            $store = ActionScheduler::store();
            $statusLabel = $store->get_status($actionId);

            $statusColor = '#72aee6';
            if ($statusLabel === 'complete') {
                $statusColor = '#00a32a';
            } elseif ($statusLabel === 'failed') {
                $statusColor = '#d63638';
            } elseif ($statusLabel === 'pending') {
                $statusColor = '#dba617';
            }

            $argumentsHtml = !empty($arguments) 
                ? '<pre style="margin:0; font-size:10px;">' . esc_html(print_r($arguments, true)) . '</pre>' 
                : '-';
            
            $nextRunHtml = $nextRunDate ? esc_html($nextRunDate->format('Y-m-d H:i:s')) : '-';
            
            $actionLinksHtml = $statusLabel === 'failed' 
                ? '<a href="' . esc_url(admin_url('tools.php?page=action-scheduler&s=' . $actionId)) . '" target="_blank">Ver error</a>' 
                : '-';

            echo '<tr>';
            echo '<td><strong>' . esc_html($hookName) . '</strong><br><small>ID: ' . esc_html((string)$actionId) . '</small></td>';
            echo '<td>' . $argumentsHtml . '</td>';
            echo '<td><span style="font-weight:bold; color:' . esc_attr($statusColor) . '; text-transform:uppercase;">' . esc_html($statusLabel) . '</span></td>';
            echo '<td>' . $nextRunHtml . '</td>';
            echo '<td>' . $actionLinksHtml . '</td>';
            echo '</tr>';
        }
    }
}