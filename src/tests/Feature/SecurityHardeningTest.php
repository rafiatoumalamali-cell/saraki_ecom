<?php

test('every page carries the baseline security headers', function () {
    $this->get('/fr')
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
});

test('repeated login attempts are rate limited', function () {
    $payload = ['email' => 'nobody@example.test', 'password' => 'wrong-password'];

    for ($i = 0; $i < 6; $i++) {
        $this->post('/login', $payload)->assertRedirect();
    }

    $this->post('/login', $payload)->assertStatus(429);
});
