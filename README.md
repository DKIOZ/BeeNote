# BeeNote

Le carnet de ruches qui fonctionne sans réseau, à la voix ou au toucher.

Au rucher, dire une phrase revient à toucher le bouton qui la porte. Tout est gardé sur le téléphone, puis envoyé au retour du réseau. Le registre (visites, traitements, récoltes, déplacements) se télécharge en PDF ou pour un tableur.

## Pile technique

Laravel 12, Inertia, Vue 3, Tailwind, Vosk (reconnaissance vocale dans le navigateur), Workbox (service worker).

## Installer

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
php artisan serve
```

Données d'essai, jamais en production :

```bash
DEMO_PASSWORD=choisissez-un-mot-de-passe php artisan db:seed --class=DemoSeeder
```

Le compte créé est `demo@beenote.test`.

## Tester

```bash
php artisan test
npm test
```

## Mode terrain

- `resources/js/terrain/useTerrain.js` : état de la visite, file d'envoi, voix.
- `resources/js/terrain/voice/engine.js` : moteur vocal, remplaçable (`load`, `start`, `stop`).
- `resources/js/terrain/voice/phrases.js` : phrases reconnues. Une phrase doit être dite seule.
- `app/Http/Controllers/TerrainController.php` : envoi par lots. Chaque visite porte un identifiant généré sur le téléphone, donc un envoi répété ne crée pas de doublon.

Le micro et le service worker exigent HTTPS, sauf sur `localhost`. `tools/voice-test` contient un banc d'essai de la reconnaissance vocale.

## Avant une mise en production

- `APP_ENV=production`, `APP_DEBUG=false`, HTTPS, serveur de mail configuré : la vérification d'e-mail s'active en production.
- Installer sans les outils de développement : `composer install --no-dev --optimize-autoloader`.
- Derrière un proxy, renseigner `TRUSTED_PROXIES` avec ses adresses exactes. Jamais `*` : n'importe qui pourrait alors se faire passer pour une autre adresse et contourner les limites.
- Héberger le modèle vocal sur vos serveurs et renseigner `VOICE_MODEL_URL`. L'adresse par défaut est celle de la démo de vosk-browser, un tiers dont le contenu s'exécute sur le téléphone. Vérifier l'empreinte du fichier avant de le publier (`sha256sum vosk-model-small-fr-pguyot-0.3.tar.gz`).
- Faire valider `resources/markdown/policy.md` et `resources/markdown/terms.md`.
- Vérifier les champs légaux du registre d'élevage.
- Lancer le planificateur (`php artisan schedule:run` chaque minute) : il vide la corbeille après 30 jours.
- Sauvegarder la base et `storage/app/private`, qui contient les notes vocales.

## À valider sur le terrain

Mesuré jusqu'ici sur PC uniquement. Restent à tester sur de vrais téléphones : reconnaissance en mode avion, écran verrouillé, voile, vent et bourdonnement.
