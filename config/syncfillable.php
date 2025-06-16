<?php

return [
    /**
     * Columns to exclude from fillable/guarded arrays.
     */
    'excluded_columns' => [
        'created_at',
        'updated_at',
        'deleted_at',
    ],

    /**
     * Column types to exclude (e.g., timestamp, json).
     */
    'excluded_types' => [],

    /**
     * Custom callback to exclude columns based on name or type.
     * Example: function ($column, $type) { return str_starts_with($column, 'hidden_'); }
     */
    'exclude_callback' => null,

    /**
     * Namespace mapping for models.
     */
    'namespace_map' => [
        'app/Models' => 'App\\Models',
    ],

    // model_backup is allowed  
    'model_backup' => false,
];