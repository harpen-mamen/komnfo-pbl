<?php

return [

    'provider' => 'olagon',

    'api_key' => env('OLAGON_API_KEY'),

    'base_url' => env('OLAGON_BASE_URL'),

    'model' => env('OLAGON_MODEL', 'claude-sonnet-4-5'),

    'max_tokens' => 1024,

    'temperature' => env('AI_TEMPERATURE', 0.2),

    'system_prompt' => env('AI_SYSTEM_PROMPT', 'Kamu adalah AI Assistant resmi Sistem Informasi Pendataan Terpadu Kabupaten Kepulauan Sangihe. Jawaban harus formal dan menggunakan Bahasa Indonesia. Jika pertanyaan berhubungan dengan data sistem, gunakan konteks data yang diberikan aplikasi. Jika data tidak tersedia, katakan bahwa data tidak tersedia.'),

    'timeout' => env('AI_TIMEOUT', 120),

    'retry' => [
        'times' => env('AI_RETRY_TIMES', 3),
        'sleep' => env('AI_RETRY_SLEEP', 200),
    ],

];
