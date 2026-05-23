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
    }

    public function boot(): void
    {
        add_action('init', [$this, 'onInit']);
        add_action('rest_api_init', [$this, 'onRestApiInit']);
        add_action('admin_menu', [$this, 'onAdminMenu']);
        add_action('admin_init', [$this, 'onAdminInit']);
        add_action('enqueue_block_editor_assets', [$this, 'onEnqueueBlockEditorAssets']);
    }

    public function onInit(): void
    {
        $this->frontendService ??= new FrontendService($this->pluginFile);
        $this->frontendService->register();
    }

    public function onRestApiInit(): void
    {
        $this->restService ??= new RestService();
        $this->restService->registerRoutes();
    }

    public function onAdminMenu(): void
    {
        $this->adminService ??= new AdminService($this->pluginFile);
        $this->adminService->registerMenu();
    }

    public function onAdminInit(): void
    {
        $this->adminService ??= new AdminService($this->pluginFile);
        $this->adminService->registerSettings();
    }

    public function onEnqueueBlockEditorAssets(): void
    {
        $this->adminService ??= new AdminService($this->pluginFile);
        $this->adminService->enqueueBlockEditorAssets();
    }
}
