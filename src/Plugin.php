<?php

declare(strict_types=1);

namespace WorldQuest;

use WorldQuest\Admin\AdminService;
use WorldQuest\Frontend\FrontendService;
use WorldQuest\Rest\RestService;

final class Plugin
{
    public function __construct(
        private readonly string $pluginFile,
        private ?AdminService $adminService = null,
        private ?FrontendService $frontendService = null,
        private ?RestService $restService = null,
    ) {
        $this->adminService = $adminService ?? new AdminService($pluginFile);
        $this->frontendService = $frontendService ?? new FrontendService($pluginFile);
        $this->restService = $restService ?? new RestService();
    }

    public function boot(): void
    {
        add_action('init', [$this, 'onInit']);
        add_action('rest_api_init', [$this, 'onRestApiInit']);
        add_action('admin_menu', [$this, 'onAdminMenu']);
        add_action('enqueue_block_editor_assets', [$this, 'onEnqueueBlockEditorAssets']);
    }

    public function onInit(): void
    {
        load_plugin_textdomain(
            'world-quest',
            false,
            dirname(plugin_basename($this->pluginFile)) . '/languages'
        );

        $this->frontendService?->register();
    }

    public function onRestApiInit(): void
    {
        $this->restService?->registerRoutes();
    }

    public function onAdminMenu(): void
    {
        $this->adminService?->registerMenu();
    }

    public function onEnqueueBlockEditorAssets(): void
    {
        $this->adminService?->enqueueBlockEditorAssets();
    }
}
