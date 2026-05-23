<?php

declare(strict_types=1);

namespace WorldQuest\Rest;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use wpdb;

final class QuestsController extends BaseController
{
    private string $table;

    public function __construct(private readonly wpdb $wpdb)
    {
        $this->table = $this->wpdb->prefix . 'world_quests';
    }

    public function list(WP_REST_Request $request): WP_REST_Response
    {
        unset($request);
        $rows = $this->wpdb->get_results("SELECT * FROM {$this->table} ORDER BY id DESC", ARRAY_A) ?: [];
        return new WP_REST_Response($rows);
    }

    public function create(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $nonceCheck = $this->enforceNonce($request);
        if ($nonceCheck instanceof WP_Error) return $nonceCheck;

        $title = sanitize_text_field((string) $request->get_param('title'));
        $status = sanitize_key((string) ($request->get_param('status') ?: 'draft'));
        $slug = sanitize_title((string) ($request->get_param('slug') ?: $title));

        if ($title === '') return $this->validationError('title', __('Title is required.', 'world-quest'));
        if (!$this->validateStatus($status)) return $this->validationError('status', __('Invalid status.', 'world-quest'));

        $ok = $this->wpdb->insert($this->table, ['title' => $title, 'slug' => $slug, 'status' => $status], ['%s', '%s', '%s']);
        if ($ok === false) return new WP_Error('worldquest_db_error', __('Failed to create quest.', 'world-quest'), ['status' => 500]);

        return new WP_REST_Response(['id' => (int) $this->wpdb->insert_id], 201);
    }

    public function update(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $nonceCheck = $this->enforceNonce($request);
        if ($nonceCheck instanceof WP_Error) return $nonceCheck;
        $id = (int) $request['id'];
        if (!$this->exists($id)) return $this->notFound('Quest', $id);

        $title = sanitize_text_field((string) $request->get_param('title'));
        $status = sanitize_key((string) ($request->get_param('status') ?: 'draft'));
        $slug = sanitize_title((string) ($request->get_param('slug') ?: $title));
        if ($title === '') return $this->validationError('title', __('Title is required.', 'world-quest'));
        if (!$this->validateStatus($status)) return $this->validationError('status', __('Invalid status.', 'world-quest'));

        $ok = $this->wpdb->update($this->table, ['title' => $title, 'slug' => $slug, 'status' => $status], ['id' => $id], ['%s', '%s', '%s'], ['%d']);
        if ($ok === false) return new WP_Error('worldquest_db_error', __('Failed to update quest.', 'world-quest'), ['status' => 500]);

        return new WP_REST_Response(['updated' => true]);
    }

    public function delete(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $nonceCheck = $this->enforceNonce($request);
        if ($nonceCheck instanceof WP_Error) return $nonceCheck;
        $id = (int) $request['id'];
        if (!$this->exists($id)) return $this->notFound('Quest', $id);

        $ok = $this->wpdb->delete($this->table, ['id' => $id], ['%d']);
        if ($ok === false) return new WP_Error('worldquest_db_error', __('Failed to delete quest.', 'world-quest'), ['status' => 500]);

        return new WP_REST_Response(['deleted' => true]);
    }

    private function exists(int $id): bool
    {
        return (int) $this->wpdb->get_var($this->wpdb->prepare("SELECT COUNT(1) FROM {$this->table} WHERE id=%d", $id)) > 0;
    }
}
