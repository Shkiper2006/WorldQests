<?php

declare(strict_types=1);

namespace WorldQuest\Rest;

use WP_Error;
use WP_REST_Request;

abstract class BaseController
{
    protected const PUBLIC_RATE_LIMIT = 10;
    protected const PUBLIC_RATE_WINDOW = 300;

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
        return in_array((string) $value, ['draft', 'published', 'archived', 'pending_moderation'], true);
    }

    protected function enforceHoneypot(WP_REST_Request $request, string $field = 'website'): true|WP_Error
    {
        if (trim((string) $request->get_param($field)) !== '') {
            return new WP_Error('worldquest_spam_detected', __('Spam detected.', 'world-quest'), ['status' => 400]);
        }

        return true;
    }

    protected function enforceRateLimit(WP_REST_Request $request, string $action): true|WP_Error
    {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $token = sanitize_key((string) ($request->get_header('x-worldquest-token') ?: $request->get_param('token') ?: 'anon'));
        $key = 'wq_rl_' . md5($action . '|' . $ip . '|' . $token);
        $count = (int) get_transient($key);
        if ($count >= self::PUBLIC_RATE_LIMIT) {
            return new WP_Error('worldquest_rate_limited', __('Too many requests. Please try again later.', 'world-quest'), ['status' => 429]);
        }

        set_transient($key, $count + 1, self::PUBLIC_RATE_WINDOW);
        return true;
    }

    protected function verifyRecaptcha(WP_REST_Request $request): true|WP_Error
    {
        $settings = get_option('world_quest_security', []);
        $secret = (string) ($settings['recaptcha_secret'] ?? '');
        if ($secret === '') {
            return true;
        }

        $token = (string) $request->get_param('recaptcha_token');
        if ($token === '') {
            return new WP_Error('worldquest_recaptcha_required', __('reCAPTCHA token is required.', 'world-quest'), ['status' => 400]);
        }

        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'timeout' => 10,
            'body' => [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
            ],
        ]);
        if (is_wp_error($response)) {
            return new WP_Error('worldquest_recaptcha_unavailable', __('reCAPTCHA verification failed.', 'world-quest'), ['status' => 503]);
        }

        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        if (!is_array($body) || empty($body['success'])) {
            return new WP_Error('worldquest_recaptcha_invalid', __('reCAPTCHA validation failed.', 'world-quest'), ['status' => 400]);
        }

        return true;
    }

    protected function validateUploadedFile(array $file): true|WP_Error
    {
        $allowed = [
            'jpg|jpeg|jpe' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        ];

        $check = wp_check_filetype_and_ext((string) ($file['tmp_name'] ?? ''), (string) ($file['name'] ?? ''), $allowed);
        if (empty($check['ext']) || empty($check['type'])) {
            return new WP_Error('worldquest_invalid_upload', __('Invalid file type.', 'world-quest'), ['status' => 400]);
        }

        return true;
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
