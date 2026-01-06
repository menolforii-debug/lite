<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../bootstrap.php';

use LiteCMS\Http\Input;
use LiteCMS\Http\Response;
use LiteCMS\Security\ACL;
use LiteCMS\Security\Auth;
use LiteCMS\Security\Csrf;
use LiteCMS\Services\ItemService;

if (!Auth::check()) {
    Response::jsonError('Unauthorized', 401);
}
ACL::require('items');

$input = Input::json();
$action = $input['action'] ?? 'list';
$service = new ItemService();

try {
    if ($action === 'list') {
        $infoblockId = (int) ($input['infoblock_id'] ?? 0);
        $items = $service->listByInfoblock($infoblockId);
        Response::jsonSuccess(['items' => $items]);
    }
    if (!Csrf::verify($input['csrf_token'] ?? null)) {
        Response::jsonError('CSRF token invalid', 403);
    }
    if ($action === 'create') {
        $id = $service->create($input);
        Response::jsonSuccess(['id' => $id]);
    }
    if ($action === 'update') {
        $service->update((int) ($input['id'] ?? 0), $input);
        Response::jsonSuccess(['id' => (int) $input['id']]);
    }
    if ($action === 'delete') {
        $service->delete((int) ($input['id'] ?? 0));
        Response::jsonSuccess(['id' => (int) $input['id']]);
    }
    Response::jsonError('Unknown action');
} catch (Throwable $e) {
    Response::jsonError($e->getMessage());
}
