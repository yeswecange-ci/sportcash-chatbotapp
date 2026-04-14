@extends('layouts.app')

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-6">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('bot-tracking.clients.show', $client->id) }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">&larr; Retour au profil</a>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h1 class="text-xl font-bold text-gray-900 mb-6">Modifier le contact</h1>
            <form method="POST" action="{{ route('bot-tracking.clients.update', $client->id) }}">
                @csrf
                @method('PUT')
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
                        <input type="text" name="client_full_name" value="{{ old('client_full_name', $client->client_full_name) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 bg-white @error('client_full_name') border-red-400 @enderror">
                        @error('client_full_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Profil WhatsApp (non modifiable)</label>
                        <input type="text" name="whatsapp_profile_name" value="{{ $client->whatsapp_profile_name }}" readonly class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-500 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telephone <span class="text-red-500">*</span></label>
                        <input type="text" name="phone_number" value="{{ old('phone_number', $client->phone_number) }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 bg-white @error('phone_number') border-red-400 @enderror">
                        @error('phone_number')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $client->email) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 bg-white @error('email') border-red-400 @enderror">
                        @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type de client</label>
                        <select name="is_client" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="" {{ old('is_client', $client->is_client) === null ? 'selected' : '' }}>Non defini</option>
                            <option value="1" {{ (string)old('is_client', $client->is_client) === '1' ? 'selected' : '' }}>Client SportCash</option>
                            <option value="0" {{ (string)old('is_client', $client->is_client) === '0' ? 'selected' : '' }}>Non-client</option>
                        </select>
                        @error('is_client')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition shadow-sm">Enregistrer</button>
                        <a href="{{ route('bot-tracking.clients.show', $client->id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Annuler</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
