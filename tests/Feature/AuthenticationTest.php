<?php

use Gometap\LaraiTracker\Models\LaraiSetting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

uses(\Gometap\LaraiTracker\Tests\TestCase::class);

test('it allows access in local environment without password', function () {
    app()->detectEnvironment(fn() => 'local');
    config()->set('larai-tracker.password', null);
    
    $this->get(route('larai.dashboard'))
        ->assertStatus(200);
});

test('it redirects to login if setup is required in non-local', function () {
    config()->set('app.env', 'production');
    config()->set('larai-tracker.password', null);
    
    $this->get(route('larai.dashboard'))
        ->assertRedirect(route('larai.auth.login'))
        ->assertSessionHas('setup_required', true);
});

test('it can set up initial password', function () {
    config()->set('app.env', 'production');
    config()->set('larai-tracker.session_lifetime', 120);
    $this->post(route('larai.auth.login.submit'), [
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertRedirect(route('larai.dashboard'));

    expect(LaraiSetting::get('dashboard_password'))->not->toBeNull();
    expect(Hash::check('new-password', LaraiSetting::get('dashboard_password')))->toBeTrue();
    expect(Session::get('larai_authenticated'))->toBeTrue();
});

test('it validates password setup', function () {
    config()->set('app.env', 'production');
    $this->post(route('larai.auth.login.submit'), [
        'password' => 'short',
        'password_confirmation' => 'mismatch',
    ])->assertSessionHasErrors(['password']);
});

test('it can login with correct password from config', function () {
    config()->set('app.env', 'production');
    config()->set('larai-tracker.password', 'config-secret');
    config()->set('larai-tracker.session_lifetime', 120);
    $this->post(route('larai.auth.login.submit'), [
        'password' => 'config-secret',
    ])->assertRedirect(route('larai.dashboard'));
    
    expect(Session::get('larai_authenticated'))->toBeTrue();
});

test('it can login with correct password from database', function () {
    config()->set('app.env', 'production');
    LaraiSetting::set('dashboard_password', Hash::make('db-secret'));
    config()->set('larai-tracker.session_lifetime', 120);
    $this->post(route('larai.auth.login.submit'), [
        'password' => 'db-secret',
    ])->assertRedirect(route('larai.dashboard'));
    
    expect(Session::get('larai_authenticated'))->toBeTrue();
});

test('it fails login with wrong password', function () {
    config()->set('app.env', 'production');
    config()->set('larai-tracker.password', 'correct-password');
    $this->post(route('larai.auth.login.submit'), [
        'password' => 'wrong-password',
    ])->assertSessionHasErrors(['password']);
    
    expect(Session::get('larai_authenticated'))->toBeNull();
});

test('it can logout', function () {
    Session::put('larai_authenticated', true);
    
    $this->get(route('larai.auth.logout'))
        ->assertRedirect(route('larai.auth.login'));
    
    expect(Session::has('larai_authenticated'))->toBeFalse();
});

test('it protects dashboard routes from unauthenticated users', function () {
    config()->set('app.env', 'production');
    config()->set('larai-tracker.password', 'secret');
    $this->get(route('larai.dashboard'))->assertRedirect(route('larai.auth.login'));
    $this->get(route('larai.settings'))->assertRedirect(route('larai.auth.login'));
    $this->get(route('larai.logs'))->assertRedirect(route('larai.auth.login'));
});

test('it can change password from settings', function () {
    config()->set('app.env', 'production');
    LaraiSetting::set('dashboard_password', Hash::make('old-password'));
    config()->set('larai-tracker.session_lifetime', 120);
    Session::put('larai_authenticated', true);
    Session::put('larai_auth_time', time());
    
    $this->post(route('larai.settings.update'), [
        'security' => [
            'current_password' => 'old-password',
            'new_password' => 'brand-new-password',
            'new_password_confirmation' => 'brand-new-password',
        ]
    ])->assertSessionHas('password_success');

    expect(Hash::check('brand-new-password', LaraiSetting::get('dashboard_password')))->toBeTrue();
});

test('it fails password change if current password is wrong', function () {
    config()->set('app.env', 'production');
    LaraiSetting::set('dashboard_password', Hash::make('old-password'));
    config()->set('larai-tracker.session_lifetime', 120);
    Session::put('larai_authenticated', true);
    Session::put('larai_auth_time', time());
    
    $this->from(route('larai.settings'))
        ->post(route('larai.settings.update'), [
            'security' => [
                'current_password' => 'wrong-old-password',
                'new_password' => 'new-password',
                'new_password_confirmation' => 'new-password',
            ]
        ])
        ->assertRedirect(route('larai.settings'))
        ->assertSessionHas('password_error');

    expect(Hash::check('old-password', LaraiSetting::get('dashboard_password')))->toBeTrue();
});
