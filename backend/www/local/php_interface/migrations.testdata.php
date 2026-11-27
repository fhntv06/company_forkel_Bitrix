<?php
/**
 * sprint.migration: тестовые данные FORKEL (демо-товары, торговые предложения и т.п.).
 * Конфигурация подключается ТОЛЬКО при APP_ENV=local: на боевом/тестовом сервере
 * (APP_ENV=production/stage или не задан) файл возвращает null и sprint.migration его не видит.
 */
if (!class_exists('\Forkel\Env') || \Forkel\Env::get('APP_ENV') !== 'local') {
    return null;
}

return [
    'title'           => 'FORKEL: тестовые данные (только локально)',
    'sort'            => 900,
    'migration_dir'   => '/local/php_interface/migrations.testdata',
    'migration_table' => 'sprint_migration_testdata',
];
