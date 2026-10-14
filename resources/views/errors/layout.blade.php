{{-- Fallback for error codes without their own view (Laravel's 4xx/5xx views extend this). --}}
<x-error-page :code="trim($__env->yieldContent('code')) ?: 'error'"
    :title="trim($__env->yieldContent('message')) ?: 'Something went wrong'"
    message="This page couldn’t be shown." hint="Go back and try again, or return to your dashboard." />
