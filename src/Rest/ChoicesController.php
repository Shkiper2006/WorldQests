<?php

declare(strict_types=1);

namespace WorldQuest\Rest;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use wpdb;

final class ChoicesController extends BaseController
{
    private string $table;

    public function __construct(private readonly wpdb $wpdb)
    {
        $this->table = $this->wpdb->prefix . 'world_quest_choices';
    }

    public function create(WP_REST_Request $request): WP_REST_Response|WP_Error { return $this->save($request); }
    public function update(WP_REST_Request $request): WP_REST_Response|WP_Error { return $this->save($request, (int) $request['id']); }

    public function delete(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $nonceCheck = $this->enforceNonce($request);
        if ($nonceCheck instanceof WP_Error) return $nonceCheck;
        $id = (int) $request['id'];
        if (!$this->exists($id)) return $this->notFound('Choice', $id);
        $ok = $this->wpdb->delete($this->table, ['id' => $id], ['%d']);
        if ($ok === false) return new WP_Error('worldquest_db_error', __('Failed to delete choice.', 'world-quest'), ['status' => 500]);
        return new WP_REST_Response(['deleted' => true]);
    }

    private function save(WP_REST_Request $request, ?int $id = null): WP_REST_Response|WP_Error
    {
        $nonceCheck = $this->enforceNonce($request);
        if ($nonceCheck instanceof WP_Error) return $nonceCheck;
        if ($id !== null && !$this->exists($id)) return $this->notFound('Choice', $id);

        $questId = (int) $request->get_param('quest_id');
        $parentNodeId = (int) $request->get_param('parent_node_id');
        $targetNodeCode = sanitize_text_field((string) $request->get_param('target_node_code'));
        $label = sanitize_text_field((string) $request->get_param('label'));
        $status = sanitize_key((string) ($request->get_param('status') ?: 'draft'));
        $sortOrder = (int) $request->get_param('sort_order');

        if ($questId <= 0) return $this->validationError('quest_id', __('quest_id must be greater than 0.', 'world-quest'));
        if ($parentNodeId <= 0) return $this->validationError('parent_node_id', __('parent_node_id must be greater than 0.', 'world-quest'));
        if ($targetNodeCode === '') return $this->validationError('target_node_code', __('target_node_code is required.', 'world-quest'));
        if ($label === '') return $this->validationError('label', __('label is required.', 'world-quest'));
        if (!$this->validateStatus($status)) return $this->validationError('status', __('Invalid status.', 'world-quest'));

        $data = ['quest_id' => $questId, 'parent_node_id' => $parentNodeId, 'target_node_code' => $targetNodeCode, 'label' => $label, 'status' => $status, 'sort_order' => $sortOrder];
        $formats = ['%d', '%d', '%s', '%s', '%s', '%d'];

        $ok = $id === null
            ? $this->wpdb->insert($this->table, $data, $formats)
            : $this->wpdb->update($this->table, $data, ['id' => $id], $formats, ['%d']);

        if ($ok === false) return new WP_Error('worldquest_db_error', __('Failed to persist choice.', 'world-quest'), ['status' => 500]);

        return new WP_REST_Response($id === null ? ['id' => (int) $this->wpdb->insert_id] : ['updated' => true], $id === null ? 201 : 200);
    }

    private function exists(int $id): bool
    {
        return (int) $this->wpdb->get_var($this->wpdb->prepare("SELECT COUNT(1) FROM {$this->table} WHERE id=%d", $id)) > 0;
    }
}
