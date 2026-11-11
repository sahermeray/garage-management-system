<?php

test('root route redirects to dashboard', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('dashboard'));
});
