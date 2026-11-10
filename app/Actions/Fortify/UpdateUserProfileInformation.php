<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        $nouvelEmail = mb_strtolower((string) ($input['email'] ?? '')) !== mb_strtolower($user->email);

        // Changer d'adresse permet de reprendre le compte par « mot de passe oublié » :
        // un appareil resté connecté ne doit pas y suffire.
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'current_password' => $nouvelEmail ? ['required', 'string', 'current_password:web'] : ['nullable'],
            // Pour le registre d'élevage : facultatif, repris tel quel dans le PDF.
            'adresse' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+().\s-]+$/'],
            'napi' => ['nullable', 'string', 'max:20', 'regex:/^[A-Za-z0-9\s-]+$/'],
            'siret' => ['nullable', 'string', 'max:20', 'regex:/^[A-Za-z0-9\s]+$/'],
        ], [
            'current_password.required' => 'Indiquez votre mot de passe pour changer d\'adresse.',
            'current_password.current_password' => 'Mot de passe incorrect.',
            'telephone.regex' => 'Seulement des chiffres, espaces et +.',
            'napi.regex' => 'Lettres et chiffres seulement, par exemple A1234567.',
            'siret.regex' => 'Lettres et chiffres seulement.',
        ])->validateWithBag('updateProfileInformation');

        $identite = collect(['adresse', 'telephone', 'napi', 'siret'])
            ->mapWithKeys(fn ($champ) => [$champ => trim((string) ($input[$champ] ?? '')) ?: null])
            ->all();
        $identite['napi'] = $identite['napi'] ? mb_strtoupper($identite['napi']) : null;
        $user->forceFill($identite);

        if ($nouvelEmail && $user instanceof MustVerifyEmail && config('beenote.email_verification')) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'email' => $input['email'],
            ])->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
