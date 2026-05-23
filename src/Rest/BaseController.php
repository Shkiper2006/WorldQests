<?php

declare(strict_types=1);

namespace WorldQuest\Rest;

use WP_Error;
use WP_REST_Request;

abstract class BaseController
{
    public function canManage(): bool
    {
        return current_user_can('manage_options') || current_user_can('worldquest_moderate');
    }

    public function permissionDenied(): WP_Error
    {
        return new WP_Error('worldquest_forbidden', __('You do not have permissions to perform this action.', 'world-quest'), ['status' => 403]);
    }

    protected function enforceNonce(WP_REST_Request $request): true|WP_Error
    {
        if (wp_doing_ajax() || is_admin()) {
            $nonce = (string) ($request->get_header('x_wp_nonce') ?: $request->get_param('_wpnonce'));
            if ($nonce === '' || !wp_verify_nonce($nonce, 'wp_rest')) {
                return new WP_Error('worldquest_invalid_nonce', __('Invalid security token.', 'world-quest'), ['status' => 403]);
            }
        }

        return true;
    }

    protected function validateStatus(mixed $value): bool
    {
        return in_array((string) $value, ['draft', 'published', 'archived'], true);
    }

    protected function validationError(string $field, string $message, int $status = 400): WP_Error
    {
        return new WP_Error('worldquest_validation_error', $message, ['status' => $status, 'field' => $field]);
    }

    protected function notFound(string $entity, int $id): WP_Error
    {
        return new WP_Error('worldquest_not_found', sprintf(__('%s with ID %d was not found.', 'world-quest'), $entity, $id), ['status' => 404]);
    }
}
