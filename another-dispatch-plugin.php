<?php
/**
 * Plugin Name: Another Dispatch Plugin Re
 * Plugin URI:  https://github.com/chomusuke-png/another-dispatch-plugin-wp
 * Description: Sistema de envíos masivos y automatizados de correos electrónicos.
 * Version:     2.6.12
 * Author:      Zumito
 * Text Domain: another-dispatch-plugin
 *
 * @package Zumito\ADP
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('ADP_VERSION', '2.6.12');
define('ADP_PATH', plugin_dir_path(__FILE__));
define('ADP_URL', plugin_dir_url(__FILE__));

$composerAutoloader = ADP_PATH . 'vendor/autoload.php';

if (!file_exists($composerAutoloader)) {
    add_action('admin_notices', function (): void {
        echo '<div class="notice notice-error"><p><strong>Another Dispatch Plugin Re:</strong> Faltan dependencias. Por favor ejecuta <code>composer install --no-dev -o</code> en el directorio del plugin.</p></div>';
    });
    return;
}

require_once $composerAutoloader;

$actionSchedulerBootstrap = ADP_PATH . 'vendor/woocommerce/action-scheduler/action-scheduler.php';
if (file_exists($actionSchedulerBootstrap)) {
    require_once $actionSchedulerBootstrap;
}

use Zumito\ADP\Core\Database;
use Zumito\ADP\Core\Activator;
use Zumito\ADP\Core\Cleanup;

use Zumito\ADP\Admin\AdminManager;
use Zumito\ADP\Admin\AdminActions;
use Zumito\ADP\Admin\AdminAjax;
use Zumito\ADP\Admin\AdminAssets;
use Zumito\ADP\Admin\AdminSettings;

use Zumito\ADP\Emails\SmtpConfig;
use Zumito\ADP\Emails\PostWatcher;
use Zumito\ADP\Emails\EmailSender;

use Zumito\ADP\PublicHooks\PublicAssets;
use Zumito\ADP\PublicHooks\Shortcode;
use Zumito\ADP\PublicHooks\SubscribeHandler;
use Zumito\ADP\PublicHooks\UnsubscribeHandler;
use Zumito\ADP\PublicHooks\VerificationHandler;
use Zumito\ADP\PublicHooks\Widget;
use Zumito\ADP\PublicHooks\TrackingHandler;

use Zumito\ADP\Bounces\BounceFetcher;

register_activation_hook(__FILE__, [Activator::class, 'activate']);

add_action('plugins_loaded', function (): void {

    if (!function_exists('as_enqueue_async_action')) {
        add_action('admin_notices', function (): void {
            echo '<div class="notice notice-error"><p><strong>Another Dispatch Plugin Re:</strong> Este plugin requiere tener instalado y activo el motor <strong>Action Scheduler</strong> (incluido en WooCommerce o disponible como plugin independiente). Ocurrió un error al intentar cargarlo desde la carpeta vendor.</p></div>';
        });
        return;
    }

    try {
        $database = new Database();

        new Cleanup($database);

        if (is_admin()) {
            new AdminManager($database);
            new AdminActions($database);
            new AdminAjax();
            new AdminAssets();
            new AdminSettings();
        }

        new SmtpConfig();
        new PostWatcher();
        new EmailSender($database);

        new BounceFetcher($database);

        new PublicAssets();
        new Shortcode();
        new SubscribeHandler($database);
        new UnsubscribeHandler($database);
        new VerificationHandler($database);
        new Widget();
        new TrackingHandler($database);

    } catch (\Exception $exception) {
        error_log('Another Dispatch Plugin Error de Inicialización: ' . $exception->getMessage());
    }
});