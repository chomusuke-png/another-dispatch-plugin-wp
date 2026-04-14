<?php

declare(strict_types=1);

namespace Zumito\ADP\PublicHooks;

class PublicAssets
{
    private const ASSETS_VERSION = '2.6.0';

    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function enqueueAssets(): void
    {
        wp_enqueue_style(
            'adp-public-css',
            ADP_URL . 'assets/css/public-style.css',
            [],
            self::ASSETS_VERSION
        );
    }
}