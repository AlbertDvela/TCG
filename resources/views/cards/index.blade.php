<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MTG Explorer - Mi Colección</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Estilo para asegurar que las imágenes mantengan la proporción de una carta real */
        .card-aspect {
            aspect-ratio: 63 / 88;
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen">

    <nav class="bg-gray-800 border-b border-gray-700 p-4 mb-8">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-extrabold tracking-tighter text-blue-400">
                MTG<span class="text-white">DATABASE</span>
            </h1>
            <span class="text-xs text-gray-400 uppercase tracking-widest">542,279 Cartas Cargadas</span>
        </div>
    </nav>

    <div class="container mx-auto px-4 pb-12">
        <div class="flex flex-col lg:flex-row gap-8">
            
            <aside class="w-full lg:w-1/4">
                <div class="bg-gray-800 p-6 rounded-2xl shadow-xl border border-gray-700 sticky top-4">
                    <h2 class="text-xl font-bold mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        Filtros
                    </h2>

                    <form action="{{ route('cards.index') }}" method="GET" class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Nombre de carta</label>
                            <input type="text" name="name" value="{{ request('name') }}" 
                                   class="w-full bg-gray-900 border border-gray-600 rounded-lg p-2.5 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
                                   placeholder="Ej: Black Lotus...">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Código del Set</label>
                            <input type="text" name="set_code" value="{{ request('set_code') }}" 
                                   class="w-full bg-gray-900 border border-gray-600 rounded-lg p-2.5 text-white focus:ring-2 focus:ring-blue-500 outline-none uppercase"
                                   placeholder="Ej: ZNR, M21...">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Rareza</label>
                            <select name="rarity" class="w-full bg-gray-900 border border-gray-600 rounded-lg p-2.5 text-white focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="">Todas las rarezas</option>
                                <option value="common" {{ request('rarity') == 'common' ? 'selected' : '' }}>Común</option>
                                <option value="uncommon" {{ request('rarity') == 'uncommon' ? 'selected' : '' }}>Infrecuente</option>
                                <option value="rare" {{ request('rarity') == 'rare' ? 'selected' : '' }}>Rara</option>
                                <option value="mythic" {{ request('rarity') == 'mythic' ? 'selected' : '' }}>Mítica</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-lg transition duration-200 shadow-lg shadow-blue-900/20">
                            Actualizar Resultados
                        </button>

                        @if(request()->anyFilled(['name', 'set_code', 'rarity']))
                            <a href="{{ route('cards.index') }}" class="block text-center text-sm text-gray-500 hover:text-gray-300 underline mt-2">
                                Limpiar filtros
                            </a>
                        @endif
                    </form>
                </div>
            </aside>

            <main class="w-full lg:w-3/4">
                
                <div class="mb-6 flex justify-between items-center text-gray-400 text-sm">
                    <p>Mostrando {{ $cards->firstItem() ?? 0 }} a {{ $cards->lastItem() ?? 0 }} de {{ number_format($cards->total()) }} cartas</p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-6">
                    @forelse($cards as $card)
                        <div class="group">
                            <div class="relative overflow-hidden rounded-xl bg-gray-800 card-aspect shadow-lg border border-gray-700 transition duration-300 transform group-hover:-translate-y-2 group-hover:shadow-blue-500/10">
                                {{-- Imagen de Scryfall con Fallback --}}
                                <img src="{{ $card->image_url }}" 
                                     alt="{{ $card->name }}" 
                                     loading="lazy"
                                     class="w-full h-full object-cover"
                                     onerror="this.src='