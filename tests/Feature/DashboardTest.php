<?php

use App\Livewire\Dashboard;
use App\Models\User;
use App\Models\Vehicle;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('authenticated user can see dashboard', function () {
    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk();
});

test('dashboard shows correct vehicle count', function () {
    Vehicle::factory()->count(3)->create();

    Livewire::actingAs($this->user)
        ->test(Dashboard::class)
        ->assertSee('3');
});

test('unauthenticated user is redirected to login', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});
