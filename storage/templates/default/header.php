<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?></title>
    <?php if (!empty($description)): ?>
        <meta name="description" content="<?= htmlspecialchars($description) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= \LiteCMS\Config::baseUrl('/assets/site.css') ?>">
</head>
<body>
<header>
    <h1><?= htmlspecialchars($title) ?></h1>
</header>
<main>
