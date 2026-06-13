<?php

it('redirects guests to the login page', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
