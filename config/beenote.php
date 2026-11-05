<?php

return [
    // Actif par défaut en production, pour bloquer les comptes créés avec l'adresse d'un tiers.
    'email_verification' => env('REQUIRE_EMAIL_VERIFICATION', env('APP_ENV') === 'production'),

    // Modèle de reconnaissance vocale. À héberger sur vos serveurs avant l'ouverture publique.
    'voice_model_url' => env('VOICE_MODEL_URL', 'https://ccoreilly.github.io/vosk-browser/models/vosk-model-small-fr-pguyot-0.3.tar.gz'),

    'corbeille_jours' => 30,

    // Sans proxy déclaré, X-Forwarded-For n'est pas cru. Adresses séparées par des virgules.
    'trusted_proxies' => env('TRUSTED_PROXIES'),

    'quota_audio_mo' => (int) env('QUOTA_AUDIO_MO', 500),
];
