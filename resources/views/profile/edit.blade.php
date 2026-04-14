@extends('layouts.app')
@section('title', 'Profil')

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-8">

        {{-- Header --}}
        <div class="flex items-center gap-4 mb-8">
            <div class="w-14 h-14 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
                <span class="text-xl font-semibold text-indigo-600">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            </div>
            <div>
                <h1 class="text-lg font-semibold text-gray-900">{{ auth()->user()->name }}</h1>
                <p class="text-sm text-gray-500">{{ auth()->user()->email }}</p>
            </div>
        </div>

        {{-- Informations --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4">
            <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-5">Informations du compte</h2>
            @include('profile.partials.update-profile-information-form')
        </div>

        {{-- Mot de passe --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4">
            <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-5">Mot de passe</h2>
            @include('profile.partials.update-password-form')
        </div>

        {{-- Zone danger --}}
        <div class="bg-white rounded-xl border border-red-100 p-6">
            <h2 class="text-xs font-semibold text-red-400 uppercase tracking-widest mb-5">Zone de danger</h2>
            @include('profile.partials.delete-user-form')
        </div>

    </div>
</div>
@endsection
