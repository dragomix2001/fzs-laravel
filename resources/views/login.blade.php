@extends ('layouts.plane')
@section ('body')
<div class="min-h-screen flex items-center justify-center bg-secondary-100 px-4">
    <div class="w-full max-w-sm">
        <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-secondary-200 bg-secondary-50">
                <h4 class="text-lg font-semibold text-secondary-900 text-center">Please Sign In</h4>
            </div>
            <div class="p-6">
                <form role="form">
                    <fieldset class="space-y-4">
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-secondary-700">E-mail</label>
                            <input class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" placeholder="E-mail" name="email" type="email" autofocus>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-secondary-700">Password</label>
                            <input class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" placeholder="Password" name="password" type="password">
                        </div>
                        <label class="flex items-center gap-2">
                            <input name="remember" type="checkbox" class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500">
                            <span class="text-sm text-secondary-700">Remember Me</span>
                        </label>
                        <x-button variant="success" size="md" href="{{ url('') }}">Login</x-button>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
