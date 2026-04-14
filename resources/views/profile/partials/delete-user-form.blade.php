<div x-data="{ open: {{ $errors->userDeletion->isNotEmpty() ? 'true' : 'false' }} }">

    <p class="text-sm text-gray-500 mb-4">
        Une fois supprimé, toutes les données de ce compte seront définitivement effacées.
    </p>

    <button @click="open = true"
        class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition shadow-sm">
        Supprimer le compte
    </button>

    {{-- Modal de confirmation --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/40" @click="open = false"></div>
        <div class="relative bg-white rounded-xl border border-gray-200 shadow-xl p-6 max-w-md w-full mx-4">

            <h3 class="text-base font-semibold text-gray-900 mb-1">Supprimer le compte ?</h3>
            <p class="text-sm text-gray-500 mb-5">
                Cette action est irréversible. Entrez votre mot de passe pour confirmer.
            </p>

            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')

                <input type="password" name="password" placeholder="Mot de passe"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent mb-1">
                @error('password', 'userDeletion')
                    <p class="text-xs text-red-600 mb-3">{{ $message }}</p>
                @enderror

                <div class="flex justify-end gap-3 mt-4">
                    <button type="button" @click="open = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                        Annuler
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                        Supprimer définitivement
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
