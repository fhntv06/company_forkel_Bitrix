<?php
/**
 * sprint.migration: основная конфигурация FORKEL.
 * Миграции структуры и настроек (инфоблоки, свойства, опции модулей, оплата/доставка).
 * Применяются везде: локально, на тестовом и боевом сайте. Регламент: docs/development/deployment.md.
 */
return [
    'title'           => 'FORKEL: структура и настройки',
    'sort'            => 100,
    'migration_dir'   => '/local/php_interface/migrations',
    'migration_table' => 'sprint_migration_versions',
];
