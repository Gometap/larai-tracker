<?php

use Gometap\LaraiTracker\Models\LaraiSetting;

uses(\Gometap\LaraiTracker\Tests\TestCase::class);

test('it can set and get settings', function () {
    LaraiSetting::set('test_key', 'test_value');
    
    expect(LaraiSetting::get('test_key'))->toBe('test_value');
});

test('it can update existing settings', function () {
    LaraiSetting::set('test_key', 'initial_value');
    LaraiSetting::set('test_key', 'updated_value');
    
    expect(LaraiSetting::get('test_key'))->toBe('updated_value');
});

test('it returns default value if setting does not exist', function () {
    expect(LaraiSetting::get('non_existent', 'default'))->toBe('default');
});
