@props([
    'variant' => 'default', // default, osa, gso, student-org, superadmin
])

@php
    $portalNames = [
        'osa' => 'OSA Admin',
        'gso' => 'GSO Admin',
        'student-org' => 'Student Organization Portal',
        'superadmin' => 'SuperAdmin',
        'default' => '',
    ];

    $portalName = $portalNames[$variant] ?? $portalNames['default'];
@endphp

<footer class="bg-base-200 border-t border-base-300 mt-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Main Footer Content --}}
        <div class="py-8 grid grid-cols-1 md:grid-cols-3 gap-8">
            {{-- Brand Section --}}
            <div class="flex flex-col items-start">
                <div class="flex items-center gap-3 mb-4">
                    <img src="{{ asset('images/osa-logo.jpg') }}" alt="PLV Logo" class="h-12 w-12" loading="lazy">
                    <div>
                        <h3 class="font-heading font-bold text-lg text-base-content">PLV Event Scheduling</h3>
                        @if ($portalName)
                            <span class="text-sm text-base-content/70">{{ $portalName }}</span>
                        @endif
                    </div>
                </div>
                <p class="text-sm text-base-content/60 max-w-xs">
                    A digital event scheduling and approval system for Pamantasan ng Lungsod ng Valenzuela student
                    organizations.
                </p>
            </div>

            {{-- Quick Links --}}
            <div>
                <h4 class="font-heading font-semibold text-base-content mb-4">Quick Links</h4>
                <ul class="space-y-2 text-sm">
                    @if ($variant === 'student-org')
                        <li><a href="/student-org/dashboard" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">Dashboard</a></li>
                        <li><a href="/student-org/submit-ticket" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">Submit Ticket</a></li>
                        <li><a href="/student-org/calendar" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">Event Calendar</a>
                        </li>
                        <li><a href="{{ route('faq') }}" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">FAQ</a></li>
                        <li><a href="{{ route('about-us') }}" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">About Us</a></li>
                        <li><a href="{{ route('data-privacy') }}" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">Data Privacy</a></li>
                    @elseif($variant === 'osa')
                        <li><a href="/admin/dashboard" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">Dashboard</a></li>
                        <li><a href="/admin/ticket-review" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">Ticket Review</a></li>
                        <li><a href="/admin/calendar" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">Event Calendar</a>
                        </li>
                        <li><a href="{{ route('faq') }}" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">FAQ</a></li>
                        <li><a href="{{ route('about-us') }}" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">About Us</a></li>
                        <li><a href="{{ route('data-privacy') }}" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">Data Privacy</a></li>
                    @elseif($variant === 'gso')
                        <li><a href="{{ route('gso.dashboard') }}" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">Dashboard</a></li>
                        <li><a href="{{ route('gso.ticket-review') }}" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">Ticket Review</a></li>
                        <li><a href="{{ route('gso.calendar') }}" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">Event Calendar</a>
                        </li>
                        <li><a href="{{ route('faq') }}" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">FAQ</a></li>
                        <li><a href="{{ route('about-us') }}" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">About Us</a></li>
                        <li><a href="{{ route('data-privacy') }}" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">Data Privacy</a></li>
                    @elseif($variant === 'superadmin')
                        <li><a href="{{ route('superadmin.dashboard') }}" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">Dashboard</a></li>
                        <li><a href="{{ route('superadmin.users') }}" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">User Management</a>
                        </li>
                        <li><a href="{{ route('superadmin.calendar') }}" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">Event Calendar</a>
                        </li>
                        <li><a href="{{ route('faq') }}" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">FAQ</a></li>
                        <li><a href="{{ route('about-us') }}" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">About Us</a></li>
                        <li><a href="{{ route('data-privacy') }}" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">Data Privacy</a></li>
                    @else
                        <li><a href="/" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">Home</a></li>
                        <li><a href="{{ route('faq') }}" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">FAQ</a></li>
                        <li><a href="{{ route('about-us') }}" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">About Us</a></li>
                        <li><a href="{{ route('data-privacy') }}" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">Data Privacy</a></li>
                        <li><a href="/login" wire:navigate
                                class="text-base-content/70 hover:text-primary transition-colors">Login</a></li>
                    @endif
                </ul>
            </div>

            {{-- Contact & Support --}}
            <div>
                <h4 class="font-heading font-semibold text-base-content mb-4">Contact & Support</h4>
                <ul class="space-y-2 text-sm text-base-content/70">
                    <li class="flex items-center gap-2">
                        <x-ui.icon name="o-building-office-2" class="w-4 h-4" />
                        <span>Office of Student Affairs</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-ui.icon name="o-map-pin" class="w-4 h-4" />
                        <span>Pamantasan ng Lungsod ng Valenzuela</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-ui.icon name="o-envelope" class="w-4 h-4" />
                        <span>plv.osa.official@gmail.com</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Bottom Bar --}}
        <div class="border-t border-base-300 py-4">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-sm text-base-content/60">
                    © {{ date('Y') }} PLV Event Scheduling System. All
                    rights reserved.
                </p>
                <div class="flex items-center gap-4 text-sm text-base-content/60">
                    <span class="flex items-center gap-1">
                        <x-ui.icon name="o-code-bracket" class="w-4 h-4" />
                        Built with TALL Stack
                    </span>
                </div>
            </div>
        </div>
    </div>
</footer>
