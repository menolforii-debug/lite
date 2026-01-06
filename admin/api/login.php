<?php

declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';

use LiteCMS\Http\Input;
use LiteCMS\Http\Response;
use LiteCMS\Security\Auth;
use LiteCMS\Security\Csrf;

$input = Input::json();
if (!Csrf::verify($input['csrf_token'] ?? null)) {
    Response::jsonError('CSRF token invalid', 403);
}
$login = (string) ($input['login'] ?? '');
$password = (string) ($input['password'] ?? '');
if ($login === '' || $password === '') {
    Response::jsonError('Нужен логин и пароль');
}
if (!Auth::login($login, $password)) {
    Response::jsonError('Неверные данные', 401);
}
Response::jsonSuccess(['user' => Auth::user()]);
