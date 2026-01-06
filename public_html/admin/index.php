<?php

declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';

use LiteCMS\Config;
use LiteCMS\Security\Auth;
use LiteCMS\Security\Csrf;

$baseUrl = Config::baseUrl('/admin');
$csrf = Csrf::token();
$user = Auth::user();
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LiteCMS Admin</title>
    <link rel="stylesheet" href="<?= Config::baseUrl('/vendor/sow/sow.vendor.min.css') ?>">
    <link rel="stylesheet" href="<?= Config::baseUrl('/vendor/sow/sow.core.min.css') ?>">
    <link rel="stylesheet" href="<?= Config::baseUrl('/admin/vendor/codemirror/codemirror.css') ?>">
</head>
<body>
<div class="container-fluid py-3" id="app" data-base-url="<?= htmlspecialchars($baseUrl) ?>" data-csrf="<?= htmlspecialchars($csrf) ?>">
    <?php if ($user === null): ?>
        <div class="row justify-content-center">
            <div class="col-12 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5>Вход</h5>
                        <form id="login-form">
                            <div class="mb-3">
                                <label class="form-label">Логин</label>
                                <input class="form-control" name="login" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Пароль</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Войти</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-3">
                <div class="card">
                    <div class="card-body">
                        <h6>Разделы</h6>
                        <div id="sections-tree" class="sow-tree"></div>
                    </div>
                </div>
            </div>
            <div class="col-9">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 id="panel-title">Инфоблоки</h6>
                            <div class="d-flex gap-2">
                                <div class="btn-group" id="entity-switch">
                                    <button class="btn btn-primary" data-entity="infoblocks">Инфоблоки</button>
                                    <button class="btn btn-outline-primary" data-entity="sections">Разделы</button>
                                    <button class="btn btn-outline-primary" data-entity="items" disabled>Элементы</button>
                                </div>
                                <button class="btn btn-primary" id="create-button">Создать</button>
                            </div>
                        </div>
                        <table class="table table-striped" id="entity-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Название</th>
                                    <th>Ключевое слово</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="entity-modal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-title">Создать</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="entity-form">
                            <input type="hidden" name="id">
                            <input type="hidden" name="section_id">
                            <input type="hidden" name="infoblock_id">
                            <input type="hidden" name="parent_id">
                            <div class="mb-3">
                                <label class="form-label">Название</label>
                                <input class="form-control" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ключевое слово</label>
                                <div class="input-group">
                                    <input class="form-control" name="slug">
                                    <button class="btn btn-outline-secondary" type="button" id="slug-generate">Сгенерировать из названия</button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <small>URL: <span id="slug-preview"></span></small>
                            </div>
                            <div class="mb-3" id="content-wrapper">
                                <label class="form-label">Контент</label>
                                <textarea class="form-control" name="content_html" rows="4"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                        <button type="button" class="btn btn-primary" id="save-button">Сохранить</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="<?= Config::baseUrl('/vendor/sow/sow.vendor.min.js') ?>"></script>
<script src="<?= Config::baseUrl('/vendor/sow/sow.core.min.js') ?>"></script>
<script src="<?= Config::baseUrl('/admin/vendor/codemirror/codemirror.js') ?>"></script>
<script src="<?= Config::baseUrl('/admin/vendor/tinymce/tinymce.min.js') ?>"></script>
<script src="<?= Config::baseUrl('/admin/assets/helpers.js') ?>"></script>
<script src="<?= Config::baseUrl('/admin/assets/api.js') ?>"></script>
<script src="<?= Config::baseUrl('/admin/assets/app-ui.js') ?>"></script>
<script src="<?= Config::baseUrl('/admin/assets/app-core.js') ?>"></script>
<script src="<?= Config::baseUrl('/admin/assets/app.js') ?>"></script>
</body>
</html>
