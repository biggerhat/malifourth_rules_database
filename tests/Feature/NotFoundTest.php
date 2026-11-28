<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders a 404 for unknown urls when the error page is rendered with Inertia', function () {
    // The Inertia error page is only used outside local/testing.
    $this->app['env'] = 'production';

    $this->get('/definitely-not-a-page')->assertNotFound();
});
