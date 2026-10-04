<x-error-page code="403" icon="o-no-symbol" title="You don’t have access to this page"
    message="{{ $exception->getMessage() ?: 'Your account does not have permission for this page or action.' }}"
    hint="If you think you should have access, contact the Office of Student Affairs." />
