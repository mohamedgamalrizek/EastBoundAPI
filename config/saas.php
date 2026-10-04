<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SaaS Mode
    |--------------------------------------------------------------------------
    | Master switch that lets FLOW run two ways from one codebase:
    |
    |   false  → SINGLE mode. One company runs the ERP. The SaaS platform
    |            panel (tenants/plans/subscriptions) is hidden, its routes
    |            are not registered, and users never land on /saas/*.
    |
    |   true   → SAAS mode. The multi-tenant platform is active. The SaaS
    |            Super Admin (saas_read) manages tenants & plans.
    |
    | Set here rather than in .env. It decides which product the buyer is
    | running, not how one deployment is configured, so it belongs with the
    | code — and a config value survives `php artisan config:cache`, which an
    | env() call read at runtime does not.
    |
    | Switching modes: change the line below to true, then run
    | `php artisan config:clear` (and `config:cache` again if you cache config).
    */

    'enabled' => false,

];
