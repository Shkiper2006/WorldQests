<?php

declare(strict_types=1);

namespace WorldQuest\Rest;

use WP_REST_Request;
use WP_REST_Response;

final class RestService
{
    public function registerRoutes(): void
    {
        register_rest_route('world-quest/v1', '/health', [
            'methods' => 'GET',
            'callback' => [$this, 'healthCheck'],
            'permission_callback' => '__return_true',
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
