<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Connexion - SportCash Support</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body { font-family: 'Inter', sans-serif; }
        </style>
    </head>
    <body class="antialiased">
        <div class="min-h-screen flex">
            {{-- Panneau gauche SportCash --}}
            <div class="hidden lg:flex lg:w-1/2 bg-indigo-600 flex-col items-center justify-center px-12 relative overflow-hidden">
                {{-- Fond décoratif --}}
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full -translate-y-1/2 translate-x-1/2"></div>
                    <div class="absolute bottom-0 left-0 w-80 h-80 bg-white rounded-full translate-y-1/2 -translate-x-1/2"></div>
                </div>
                {{-- Contenu --}}
                <div class="relative z-10 text-center">
                    <img src="{{ asset('images/logolonaci.png') }}" alt="SportCash" class="h-20 w-auto mx-auto mb-8 rounded-xl shadow-lg">
                    <h1 class="text-3xl font-bold text-white mb-4">Support Client</h1>
                    <p class="text-indigo-100 text-lg leading-relaxed max-w-sm">
                        Plateforme de gestion des communications clients via WhatsApp.
                    </p>
                    <div class="mt-10 flex items-center justify-center gap-6 text-indigo-200 text-sm">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Support WhatsApp
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Temps réel
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Statistiques
                        </div>
                    </div>
                </div>
            </div>

            {{-- Panneau droit formulaire --}}
            <div class="w-full lg:w-1/2 flex items-center justify-center bg-gray-50 px-6 py-12">
                <div class="w-full max-w-md">
                    {{-- Logo mobile --}}
                    <div class="lg:hidden text-center mb-8">
                        <img src="{{ asset('images/logolonaci.png') }}" alt="SportCash" class="h-14 w-auto mx-auto rounded-xl">
                    </div>
                    @yield('content')
                </div>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
