<div class="py-12 bg-base-100 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-extrabold text-base-content sm:text-5xl mb-6">
                Meet our team of <span class="text-primary italic">creators</span>, <span
                    class="text-secondary italic">designers</span>, and world-class <span
                    class="text-accent italic">problem solvers</span>
            </h2>
            <p class="mt-4 max-w-2xl text-xl text-base-content/70 mx-auto leading-relaxed">
                To be the company our customers want us to be, it takes an eclectic group of passionate operators. Get
                to know the people leading the way at Untitled.
            </p>

            {{-- Decorative element --}}
            <div class="mt-8 flex justify-center">
                <div class="w-24 h-1 bg-gradient-to-r from-primary via-secondary to-accent rounded-full"></div>
            </div>
        </div>

        <div class="grid gap-10 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($developers as $dev)
                <div
                    class="group flex flex-col items-center text-center p-6 rounded-xl hover:bg-base-200/50 transition-all duration-300">
                    <div class="relative w-64 h-64 mb-6">
                        {{-- Background decorative circle --}}
                        <div
                            class="absolute inset-0 rounded-full border-2 border-dashed border-base-300 animate-spin-slow opacity-0 group-hover:opacity-100 transition-opacity duration-700">
                        </div>

                        {{-- Image container --}}
                        <div
                            class="w-full h-full rounded-full overflow-hidden shadow-xl ring-4 ring-base-200 group-hover:ring-primary transition-all duration-500 transform group-hover:scale-105">
                            <img class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                                src="{{ asset($dev['image']) }}" alt="{{ $dev['name'] }}">
                        </div>
                    </div>

                    <h3 class="text-2xl font-bold text-base-content mb-1 group-hover:text-primary transition-colors">
                        {{ $dev['name'] }}</h3>
                    <p class="text-primary font-medium mb-4">{{ $dev['role'] }}</p>
                    <p class="text-base-content/60 text-sm mb-6 max-w-xs">
                        Passionate about building great software and solving complex problems.
                    </p>

                    <div class="flex space-x-4 opacity-80 group-hover:opacity-100 transition-opacity">
                        <a href="{{ $dev['facebook'] }}"
                            class="btn btn-circle btn-outline btn-primary hover:btn-active transform hover:-translate-y-1 transition-transform duration-200">
                            <i class="fab fa-facebook-f text-xl"></i>
                        </a>
                        <a href="#"
                            class="btn btn-circle btn-outline btn-secondary hover:btn-active transform hover:-translate-y-1 transition-transform duration-200 delay-75">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="#"
                            class="btn btn-circle btn-outline btn-accent hover:btn-active transform hover:-translate-y-1 transition-transform duration-200 delay-150">
                            <i class="fab fa-linkedin-in text-xl"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <style>
        .animate-spin-slow {
            animation: spin 10s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }
    </style>
</div>
