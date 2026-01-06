<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use LiteCMS\DB;

$pdo = DB::pdo();

$schema = [
    'CREATE TABLE IF NOT EXISTS sites (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        domain TEXT NOT NULL,
        is_active INTEGER NOT NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )',
    'CREATE TABLE IF NOT EXISTS sections (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        site_id INTEGER NOT NULL,
        parent_id INTEGER NULL,
        title TEXT NOT NULL,
        slug TEXT NOT NULL,
        description TEXT NOT NULL,
        seo_title TEXT NOT NULL,
        seo_description TEXT NOT NULL,
        is_active INTEGER NOT NULL,
        sort_order INTEGER NOT NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL,
        UNIQUE(site_id, parent_id, slug)
    )',
    'CREATE TABLE IF NOT EXISTS components (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        site_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        class_name TEXT NOT NULL,
        storage_path TEXT NOT NULL,
        is_active INTEGER NOT NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )',
    'CREATE TABLE IF NOT EXISTS infoblocks (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        site_id INTEGER NOT NULL,
        section_id INTEGER NOT NULL,
        component_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        slug TEXT NOT NULL,
        settings_json TEXT NOT NULL,
        is_active INTEGER NOT NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL,
        UNIQUE(section_id, slug)
    )',
    'CREATE TABLE IF NOT EXISTS section_infoblocks (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        section_id INTEGER NOT NULL,
        infoblock_id INTEGER NOT NULL,
        sort_order INTEGER NOT NULL,
        position TEXT NULL
    )',
    'CREATE TABLE IF NOT EXISTS infoblock_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        site_id INTEGER NOT NULL,
        infoblock_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        slug TEXT NULL,
        content_html TEXT NOT NULL,
        data_json TEXT NOT NULL,
        is_active INTEGER NOT NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )',
    'CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        login TEXT NOT NULL,
        password_hash TEXT NOT NULL,
        role TEXT NOT NULL,
        is_active INTEGER NOT NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )',
];

foreach ($schema as $sql) {
    $pdo->exec($sql);
}

$now = date('c');

$site = DB::run('SELECT id FROM sites WHERE id = 1');
if ($site === []) {
    DB::exec('INSERT INTO sites (id, name, domain, is_active, created_at, updated_at) VALUES (1, :name, :domain, 1, :created_at, :updated_at)', [
        'name' => 'LiteCMS',
        'domain' => 'localhost',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
}

$component = DB::run('SELECT id FROM components WHERE id = 1');
if ($component === []) {
    DB::exec('INSERT INTO components (id, site_id, name, class_name, storage_path, is_active, created_at, updated_at)
        VALUES (1, 1, :name, :class_name, :storage_path, 1, :created_at, :updated_at)', [
        'name' => 'Basic List',
        'class_name' => 'BasicListComponent',
        'storage_path' => __DIR__ . '/../storage/components/basic_list',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
}

$sections = DB::run('SELECT id FROM sections WHERE id = 1');
if ($sections === []) {
    DB::exec('INSERT INTO sections (id, site_id, parent_id, title, slug, description, seo_title, seo_description, is_active, sort_order, created_at, updated_at)
        VALUES (1, 1, NULL, :title, :slug, :description, :seo_title, :seo_description, 1, 0, :created_at, :updated_at)', [
        'title' => 'Главная',
        'slug' => 'home',
        'description' => '',
        'seo_title' => 'Главная',
        'seo_description' => '',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
}

$infoblocks = DB::run('SELECT id FROM infoblocks WHERE id = 1');
if ($infoblocks === []) {
    DB::exec('INSERT INTO infoblocks (id, site_id, section_id, component_id, title, slug, settings_json, is_active, created_at, updated_at)
        VALUES (1, 1, 1, 1, :title, :slug, :settings_json, 1, :created_at, :updated_at)', [
        'title' => 'Новости',
        'slug' => 'news',
        'settings_json' => '{}',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    DB::exec('INSERT INTO section_infoblocks (section_id, infoblock_id, sort_order, position) VALUES (1, 1, 0, NULL)');
}

$users = DB::run('SELECT id FROM users WHERE id = 1');
if ($users === []) {
    DB::exec('INSERT INTO users (id, login, password_hash, role, is_active, created_at, updated_at)
        VALUES (1, :login, :password_hash, :role, 1, :created_at, :updated_at)', [
        'login' => 'admin',
        'password_hash' => password_hash('admin', PASSWORD_DEFAULT),
        'role' => 'admin',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
}

echo "Init complete\n";
