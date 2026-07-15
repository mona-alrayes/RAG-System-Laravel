<?php

return [
    /*
     * File extensions accepted by the document upload endpoint.
     */
    'allowed_extensions' => [
        'pdf',
        'docx',
        'txt',
    ],

    /*
     * Maximum document size in kilobytes.
     *
     * The default value is 20 MB.
     */
    'max_upload_size_kb' => (int) env(
        'DOCUMENT_MAX_UPLOAD_SIZE_KB',
        20 * 1024
    ),
];