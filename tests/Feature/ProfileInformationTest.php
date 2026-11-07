<?php

use App\Models\User;

test('profile information can be updated', function () {
    $this->actingAs($user = User::factory()->create());

    $this->put('/user/profile-information', [
        'name' => 'Test Name',
        'email' => 'test@example.com',
        'current_password' => 'password',
    ]);

    expect($user->fresh())
        ->name->toEqual('Test Name')
        ->email->toEqual('test@example.com');
});


test('changing the email requires the current password', function () {
    $this->actingAs($user = User::factory()->create());

    $this->put('/user/profile-information', ['name' => 'Test Name', 'email' => 'pirate@example.com'])
        ->assertSessionHasErrorsIn('updateProfileInformation', 'current_password');

    expect($user->fresh()->email)->not->toBe('pirate@example.com');
});

test('the name can change without the password', function () {
    $this->actingAs($user = User::factory()->create());

    $this->put('/user/profile-information', ['name' => 'Nouveau nom', 'email' => $user->email])->assertSessionHasNoErrors();

    expect($user->fresh()->name)->toBe('Nouveau nom');
});
