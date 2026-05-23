<?php

declare(strict_types=1);

namespace WorldQuest\Rest;

use RuntimeException;
use WP_REST_Request;
use WP_REST_Response;
use wpdb;

final class RestService
{
    private QuestsController $quests;
    private NodesController $nodes;
    private ChoicesController $choices;

    public function __construct(?wpdb $database = null)
    {
        global $wpdb;
        $db = $database ?? $wpdb;
        if (!($db instanceof wpdb)) {
            throw new RuntimeException('wpdb unavailable');
        }

        $this->quests = new QuestsController($db);
        $this->nodes = new NodesController($db);
        $this->choices = new ChoicesController($db);
    }

    public function registerRoutes(): void
    {
        register_rest_route('world-quest/v1', '/health', [
            'methods' => 'GET',
            'callback' => [$this, 'healthCheck'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('worldquest/v1', '/quests', [
            [
                'methods' => 'GET',
                'callback' => [$this->quests, 'list'],
                'permission_callback' => fn () => $this->quests->canManage() ?: $this->quests->permissionDenied(),
            ],
            [
                'methods' => 'POST',
                'callback' => [$this->quests, 'create'],
                'permission_callback' => fn () => $this->quests->canManage() ?: $this->quests->permissionDenied(),
            ],
        ]);

        register_rest_route('worldquest/v1', '/public/quests', [
            [
                'methods' => 'POST',
                'callback' => [$this->quests, 'createPublic'],
                'permission_callback' => '__return_true',
            ],
        ]);

        register_rest_route('worldquest/v1', '/quests/(?P<id>\d+)', [
            [
                'methods' => ['PUT', 'PATCH'],
                'callback' => [$this->quests, 'update'],
                'permission_callback' => fn () => $this->quests->canManage() ?: $this->quests->permissionDenied(),
            ],
            [
                'methods' => 'DELETE',
                'callback' => [$this->quests, 'delete'],
                'permission_callback' => fn () => $this->quests->canManage() ?: $this->quests->permissionDenied(),
            ],
        ]);

        register_rest_route('worldquest/v1', '/nodes', [
            [
                'methods' => 'GET',
                'callback' => [$this->nodes, 'list'],
                'permission_callback' => fn () => $this->nodes->canManage() ?: $this->nodes->permissionDenied(),
            ],
            [
                'methods' => 'POST',
                'callback' => [$this->nodes, 'create'],
                'permission_callback' => fn () => $this->nodes->canManage() ?: $this->nodes->permissionDenied(),
            ],
        ]);

        register_rest_route('worldquest/v1', '/public/nodes', [
            [
                'methods' => 'POST',
                'callback' => [$this->nodes, 'createPublic'],
                'permission_callback' => '__return_true',
            ],
        ]);

        register_rest_route('worldquest/v1', '/nodes/(?P<id>\d+)', [
            [
                'methods' => ['PUT', 'PATCH'],
                'callback' => [$this->nodes, 'update'],
                'permission_callback' => fn () => $this->nodes->canManage() ?: $this->nodes->permissionDenied(),
            ],
            [
                'methods' => 'DELETE',
                'callback' => [$this->nodes, 'delete'],
                'permission_callback' => fn () => $this->nodes->canManage() ?: $this->nodes->permissionDenied(),
            ],
        ]);

        register_rest_route('worldquest/v1', '/choices', [
            [
                'methods' => 'POST',
                'callback' => [$this->choices, 'create'],
                'permission_callback' => fn () => $this->choices->canManage() ?: $this->choices->permissionDenied(),
            ],
        ]);

        register_rest_route('worldquest/v1', '/choices/(?P<id>\d+)', [
            [
                'methods' => ['PUT', 'PATCH'],
                'callback' => [$this->choices, 'update'],
                'permission_callback' => fn () => $this->choices->canManage() ?: $this->choices->permissionDenied(),
            ],
            [
                'methods' => 'DELETE',
                'callback' => [$this->choices, 'delete'],
                'permission_callback' => fn () => $this->choices->canManage() ?: $this->choices->permissionDenied(),
            ],
        ]);
    }

    public function healthCheck(WP_REST_Request $request): WP_REST_Response
    {
        unset($request);

        return new WP_REST_Response([
            'status' => 'ok',
            'plugin' => 'world-quest',
        ]);
    }
}
