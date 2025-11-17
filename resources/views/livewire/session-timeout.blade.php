{{-- Load script before Alpine.js tries to use it --}}
@assets
    <script src="{{ asset('js/session-timeout-handler.js') }}"></script>
@endassets

<div x-data="sessionTimeout({
    lifetime: {{ config('session.lifetime') }},
    warningTime: {{ config('session.lifetime') - config('session.warning_time', 5) }},
    logoutUrl: '{{ route('logout') }}',
    csrfToken: '{{ csrf_token() }}'
})" x-init="init()">
    <!-- Warning Modal -->
    @if ($showWarning)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <!-- Modal Content -->
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div
                    class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <div class="bg-white dark:bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <!-- Warning Icon -->
                            <div
                                class="mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-clock text-yellow-600 text-xl"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left flex-1">
                                <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white"
                                    id="modal-title">
                                    Session Expiring Soon
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Your session will expire in <span
                                            class="font-bold text-yellow-600 dark:text-yellow-500"
                                            x-text="formatTime(countdown)"></span> due to
                                        inactivity.
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                        Click "Stay Logged In" to continue your session, or you'll be automatically
                                        logged out.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-3">
                        <button type="button" wire:click="keepAlive"
                            class="inline-flex w-full justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:w-auto">
                            <i class="fas fa-check-circle mr-2"></i>
                            Stay Logged In
                        </button>
                        <button type="button" @click="logout()"
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-600 px-4 py-2 text-sm font-semibold text-gray-900 dark:text-gray-200 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-500 hover:bg-gray-50 dark:hover:bg-gray-500 sm:mt-0 sm:w-auto">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Logout Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
