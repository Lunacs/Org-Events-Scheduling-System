<?php

it('compiles all Blade views without syntax errors', function (): void {
    $this->artisan('view:cache')->assertSuccessful();
});
