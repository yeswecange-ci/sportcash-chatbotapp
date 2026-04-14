@extends('layouts.guest')

@section('title', 'Connexion')

@section('content')

    {{-- Header --}}
    <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-2">Bon retour !</h2>
        <p class="text-gray-500 text-sm">Connectez-vous pour accéder à votre espace</p>
    </div>

    {{-- Alert succès --}}
    @if (session('status'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl flex items-center gap-3 mb-5">
            <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-sm font-medium">{{ session('status') }}</span>
        </div>
    @endif

    {{-- Alert erreurs --}}
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl flex items-start gap-3 mb-5">
            <svg class="w-4 h-4 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <span class="text-sm font-medium">{{ $errors->first() }}</span>
        </div>
    @endif

    {{-- Card formulaire --}}
    <div class="bg-white shadow-sm rounded-2xl p-8 border border-gray-100">
        <form class="space-y-5" action="{{ route('login') }}" method="POST" id="loginForm">
            @csrf

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Adresse email</label>
                <input id="email" name="email" type="email" autocomplete="username" required autofocus
                       value="{{ old('email') }}"
                       class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white transition-all text-sm placeholder:text-gray-400"
                       placeholder="votre.email@exemple.com">
            </div>

            {{-- Password --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="password" class="block text-sm font-semibold text-gray-700">Mot de passe</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 transition-colors">
                            Mot de passe oublié ?
                        </a>
                    @endif
                </div>
                <input id="password" name="password" type="password" autocomplete="current-password" required
                       class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white transition-all text-sm placeholder:text-gray-400"
                       placeholder="••••••••">
            </div>

            {{-- Remember me --}}
            <div class="flex items-center">
                <input id="remember" name="remember" type="checkbox" {{ old('remember') ? 'checked' : '' }}
                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded transition-colors">
                <label for="remember" class="ml-2 block text-sm text-gray-600 font-medium">Se souvenir de moi</label>
            </div>

            {{-- Submit --}}
            <button type="submit" id="submitBtn"
                    class="w-full flex justify-center items-center gap-3 py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all disabled:opacity-70 disabled:cursor-not-allowed">
                <svg id="btnLoader" class="hidden w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <span id="btnText">Se connecter</span>
            </button>
        </form>
    </div>

    <p class="text-center text-xs text-gray-400 mt-6">© 2026 Yeswecange · Tous droits réservés</p>

@endsection

@push('scripts')
<script>
    const loginForm = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');
    const btnLoader = document.getElementById('btnLoader');
    const btnText   = document.getElementById('btnText');

    loginForm.addEventListener('submit', function() {
        submitBtn.disabled = true;
        btnLoader.classList.remove('hidden');
        btnText.textContent = 'Connexion...';
    });

    window.addEventListener('pageshow', function(event) {
        if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
            submitBtn.disabled = false;
            btnLoader.classList.add('hidden');
            btnText.textContent = 'Se connecter';
        }
    });
</script>
@endpush
