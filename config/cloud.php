<?php

return [
    'public_key' => env('CLOUD_PUBLIC_KEY', storage_path('oauth-public.key')),
    'encoding' => array('RS256'),
];
