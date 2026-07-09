<?php

return [
    // Domyślnie wymuszone (bezpieczne domyślnie). Ustaw MFA_REQUIRED=false
    // w .env (np. na środowisku testowym), żeby wyłączyć wymuszanie MFA/2FA.
    'mfa_required' => env('MFA_REQUIRED', true),
];
