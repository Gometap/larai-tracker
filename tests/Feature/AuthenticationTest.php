<?php

use Gometap\LaraiTracker\Models\LaraiSetting;
use Gometap\LaraiTracker\Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;

uses(TestCase::class);

test('it allows access in local environment without password', function () {
    app()->detectEnvironment(fn () => 'local');
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
    config()->set('larai-tracker.setup_token', 'owner-controlled-token');
    config()->set('larai-tracker.session_lifetime', 120);
    $this->post(route('larai.auth.login.submit'), [
        'setup_token' => 'owner-controlled-token',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertRedirect(route('larai.dashboard'));

    expect(LaraiSetting::get('dashboard_password'))->not->toBeNull();
    expect(Hash::check('new-password', LaraiSetting::get('dashboard_password')))->toBeTrue();
    expect(Session::get('larai_authenticated'))->toBeTrue();
});

test('it validates password setup', function () {
    config()->set('app.env', 'production');
    config()->set('larai-tracker.setup_token', 'owner-controlled-token');
    $this->post(route('larai.auth.login.submit'), [
        'setup_token' => 'owner-controlled-token',
        'password' => 'short',
        'password_confirmation' => 'mismatch',
    ])->assertSessionHasErrors(['password']);
});

test('it prevents an unauthenticated visitor from claiming production setup', function () {
    config()->set('app.env', 'production');
    config()->set('larai-tracker.setup_token', 'owner-controlled-token');

    $this->post(route('larai.auth.login.submit'), [
        'setup_token' => 'attacker-token',
        'password' => 'attacker-password',
        'password_confirmation' => 'attacker-password',
    ])->assertSessionHasErrors(['setup_token']);

    expect(LaraiSetting::get('dashboard_password'))->toBeNull();
});

test('it refuses production setup when no owner setup token is configured', function () {
    config()->set('app.env', 'production');
    config()->set('larai-tracker.setup_token', null);

    $this->post(route('larai.auth.login.submit'), [
        'password' => 'attacker-password',
        'password_confirmation' => 'attacker-password',
    ])->assertForbidden();

    expect(LaraiSetting::get('dashboard_password'))->toBeNull();
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

    $this->post(route('larai.auth.logout'))
        ->assertRedirect(route('larai.auth.login'));

    expect(Session::has('larai_authenticated'))->toBeFalse();
});

test('logout cannot be triggered by a get request', function () {
    $this->get('/larai-tracker/logout')->assertMethodNotAllowed();
});

test('it throttles repeated failed logins', function () {
    config()->set('app.env', 'production');
    config()->set('larai-tracker.password', 'correct-password');

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->post(route('larai.auth.login.submit'), ['password' => 'wrong-password']);
    }

    $this->post(route('larai.auth.login.submit'), ['password' => 'wrong-password'])
        ->assertStatus(429);
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
        ],
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
            ],
        ])
        ->assertRedirect(route('larai.settings'))
        ->assertSessionHas('password_error');

    expect(Hash::check('old-password', LaraiSetting::get('dashboard_password')))->toBeTrue();
});

test('it locks out after too many failed attempts', function () {
    config()->set('app.env', 'production');
    config()->set('larai-tracker.password', 'correct-password');
    config()->set('larai-tracker.max_attempts', 3);
    config()->set('larai-tracker.lockout_minutes', 15);

    // Clear any previous rate limiter state
    RateLimiter::clear('larai_login|127.0.0.1');

    // Fail 3 times (max_attempts)
    for ($i = 0; $i < 3; $i++) {
        $this->post(route('larai.auth.login.submit'), [
            'password' => 'wrong-password',
        ]);
    }

    // 4th attempt should be locked out
    $this->post(route('larai.auth.login.submit'), [
        'password' => 'correct-password',
    ])->assertSessionHasErrors(['password']);

    // Ensure still not authenticated
    expect(Session::get('larai_authenticated'))->toBeNull();
});

test('it clears rate limiter on successful login', function () {
    config()->set('app.env', 'production');
    config()->set('larai-tracker.password', 'correct-password');
    config()->set('larai-tracker.max_attempts', 5);
    config()->set('larai-tracker.session_lifetime', 120);

    RateLimiter::clear('larai_login|127.0.0.1');

    // Fail twice
    $this->post(route('larai.auth.login.submit'), ['password' => 'wrong']);
    $this->post(route('larai.auth.login.submit'), ['password' => 'wrong']);

    expect(RateLimiter::attempts('larai_login|127.0.0.1'))->toBe(2);

    // Successful login should clear the counter
    $this->post(route('larai.auth.login.submit'), [
        'password' => 'correct-password',
    ])->assertRedirect(route('larai.dashboard'));

    expect(RateLimiter::attempts('larai_login|127.0.0.1'))->toBe(0);
});

test('it isolates rate limiting by IP', function () {
    config()->set('app.env', 'production');
    config()->set('larai-tracker.password', 'secret');
    config()->set('larai-tracker.max_attempts', 1);

    RateLimiter::clear('larai_login|1.1.1.1');
    RateLimiter::clear('larai_login|2.2.2.2');

    // IP 1.1.1.1 fails and gets locked
    $this->withServerVariables(['REMOTE_ADDR' => '1.1.1.1'])
        ->post(route('larai.auth.login.submit'), ['password' => 'wrong']);

    $this->withServerVariables(['REMOTE_ADDR' => '1.1.1.1'])
        ->post(route('larai.auth.login.submit'), ['password' => 'secret'])
        ->assertSessionHasErrors(['password']);

    // IP 2.2.2.2 should still be able to login
    $this->withServerVariables(['REMOTE_ADDR' => '2.2.2.2'])
        ->post(route('larai.auth.login.submit'), ['password' => 'secret'])
        ->assertRedirect(route('larai.dashboard'));
});

test('it shows remaining attempts in error message', function () {
    config()->set('app.env', 'production');
    config()->set('larai-tracker.password', 'secret');
    config()->set('larai-tracker.max_attempts', 5);

    RateLimiter::clear('larai_login|127.0.0.1');

    $this->post(route('larai.auth.login.submit'), ['password' => 'wrong'])
        ->assertSessionHasErrors(['password' => 'The password is incorrect. 4 attempt(s) remaining.']);

    $this->post(route('larai.auth.login.submit'), ['password' => 'wrong'])
        ->assertSessionHasErrors(['password' => 'The password is incorrect. 3 attempt(s) remaining.']);
});
