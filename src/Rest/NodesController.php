<?php

declare(strict_types=1);

namespace WorldQuest\Rest;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use wpdb;

final class NodesController extends BaseController
{
    private string $table;

    public function __construct(private readonly wpdb $wpdb)
    {
        $this->table = $this->wpdb->prefix . 'world_quest_nodes';
    }

    public function list(WP_REST_Request $request): WP_REST_Response
    {
        $questId = (int) $request->get_param('quest_id');
        if ($questId > 0) {
            $sql = $this->wpdb->prepare("SELECT * FROM {$this->table} WHERE quest_id=%d ORDER BY sort_order ASC, id ASC", $questId);
            $rows = $this->wpdb->get_results($sql, ARRAY_A) ?: [];
            return new WP_REST_Response($rows);
        }

        $rows = $this->wpdb->get_results("SELECT * FROM {$this->table} ORDER BY id DESC", ARRAY_A) ?: [];
        return new WP_REST_Response($rows);
    }

    public function create(WP_REST_Request $request): WP_REST_Response|WP_Error { return $this->save($request); }
    public function createPublic(WP_REST_Request $request): WP_REST_Response|WP_Error { return $this->savePublic($request); }
    public function update(WP_REST_Request $request): WP_REST_Response|WP_Error { return $this->save($request, (int) $request['id']); }

    public function delete(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $nonceCheck = $this->enforceNonce($request);
        if ($nonceCheck instanceof WP_Error) return $nonceCheck;
        $id = (int) $request['id'];
        if (!$this->exists($id)) return $this->notFound('Node', $id);
        $ok = $this->wpdb->delete($this->table, ['id' => $id], ['%d']);
        if ($ok === false) return new WP_Error('worldquest_db_error', __('Failed to delete node.', 'worldquest'), ['status' => 500]);
        return new WP_REST_Response(['deleted' => true]);
    }

    private function save(WP_REST_Request $request, ?int $id = null): WP_REST_Response|WP_Error
    {
        $nonceCheck = $this->enforceNonce($request);
        if ($nonceCheck instanceof WP_Error) return $nonceCheck;
        if ($id !== null && !$this->exists($id)) return $this->notFound('Node', $id);

        $questId = (int) $request->get_param('quest_id');
        $nodeCode = sanitize_text_field((string) $request->get_param('node_code'));
        $content = wp_kses_post((string) $request->get_param('content'));
        $status = sanitize_key((string) ($request->get_param('status') ?: 'draft'));
        $sortOrder = (int) $request->get_param('sort_order');

        if ($questId <= 0) return $this->validationError('quest_id', __('quest_id must be greater than 0.', 'worldquest'));
        if ($nodeCode === '') return $this->validationError('node_code', __('node_code is required.', 'worldquest'));
        if (!$this->validateStatus($status)) return $this->validationError('status', __('Invalid status.', 'worldquest'));

        $data = ['quest_id' => $questId, 'node_code' => $nodeCode, 'content' => $content, 'status' => $status, 'sort_order' => $sortOrder];
        $formats = ['%d', '%s', '%s', '%s', '%d'];

        $ok = $id === null
            ? $this->wpdb->insert($this->table, $data, $formats)
            : $this->wpdb->update($this->table, $data, ['id' => $id], $formats, ['%d']);

        if ($ok === false) return new WP_Error('worldquest_db_error', __('Failed to persist node.', 'worldquest'), ['status' => 500]);

        return new WP_REST_Response($id === null ? ['id' => (int) $this->wpdb->insert_id] : ['updated' => true], $id === null ? 201 : 200);
    }

    private function savePublic(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        foreach ([$this->enforceHoneypot($request), $this->enforceRateLimit($request, 'node_create'), $this->verifyRecaptcha($request)] as $check) {
            if ($check instanceof WP_Error) return $check;
        }

        $questId = (int) $request->get_param('quest_id');
        $nodeCode = sanitize_text_field((string) $request->get_param('node_code'));
        $content = wp_kses_post((string) $request->get_param('content'));
        $sortOrder = (int) $request->get_param('sort_order');
        if ($questId <= 0) return $this->validationError('quest_id', __('quest_id must be greater than 0.', 'worldquest'));
        if ($nodeCode === '') return $this->validationError('node_code', __('node_code is required.', 'worldquest'));

        $files = $request->get_file_params();
        if (isset($files['attachment']) && is_array($files['attachment'])) {
            $check = $this->validateUploadedFile($files['attachment']);
            if ($check instanceof WP_Error) return $check;
        }

        $ok = $this->wpdb->insert($this->table, ['quest_id' => $questId, 'node_code' => $nodeCode, 'content' => $content, 'status' => 'pending_moderation', 'sort_order' => $sortOrder], ['%d', '%s', '%s', '%s', '%d']);
        if ($ok === false) return new WP_Error('worldquest_db_error', __('Failed to persist node.', 'worldquest'), ['status' => 500]);

        return new WP_REST_Response(['id' => (int) $this->wpdb->insert_id, 'status' => 'pending_moderation'], 201);
    }

    private function exists(int $id): bool
    {
        return (int) $this->wpdb->get_var($this->wpdb->prepare("SELECT COUNT(1) FROM {$this->table} WHERE id=%d", $id)) > 0;
    }
}
