<x-guest-layout>
    <div class="space-y-6">
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Auth Routes Frontend</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Quick frontend page to open and test all authentication routes.
            </p>
        </div>

        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Guest Routes</h2>
            <div class="mt-3 flex flex-wrap gap-2">
                <a class="rounded bg-gray-900 px-3 py-1.5 text-xs text-white dark:bg-gray-100 dark:text-gray-900" href="{{ route('register') }}">GET /register</a>
                <a class="rounded bg-gray-900 px-3 py-1.5 text-xs text-white dark:bg-gray-100 dark:text-gray-900" href="{{ route('login') }}">GET /login</a>
                <a class="rounded bg-gray-900 px-3 py-1.5 text-xs text-white dark:bg-gray-100 dark:text-gray-900" href="{{ route('password.request') }}">GET /forgot-password</a>
            </div>
            <p class="mt-3 text-xs text-gray-600 dark:text-gray-400">
                `GET /reset-password/{token}` is visited from the reset link sent to email.
            </p>
        </div>

        @auth
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Authenticated Routes</h2>
                <div class="mt-3 flex flex-wrap gap-2">
                    <a class="rounded bg-gray-900 px-3 py-1.5 text-xs text-white dark:bg-gray-100 dark:text-gray-900" href="{{ route('verification.notice') }}">GET /verify-email</a>
                    <a class="rounded bg-gray-900 px-3 py-1.5 text-xs text-white dark:bg-gray-100 dark:text-gray-900" href="{{ route('password.confirm') }}">GET /confirm-password</a>
                </div>

                <div class="mt-4 space-y-3">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <x-primary-button>POST /email/verification-notification</x-primary-button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-secondary-button>POST /logout</x-secondary-button>
                    </form>
                </div>
            </div>
        @else
            <div class="rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-200">
                Login first to test auth-only routes: verify email, confirm password, send verification, logout.
            </div>
        @endauth
    </div>
</x-guest-layout>
