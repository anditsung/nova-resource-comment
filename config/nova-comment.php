<?php

return [
    'nova' => Anditsung\NovaResourceComment\Nova\Comment::class,

    'commenter' => \App\Nova\System\User::class,

    'available-for-navigation' => true,

    'index-limit-length' => 40,
];
