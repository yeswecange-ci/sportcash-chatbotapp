<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Dashboard') - SportCash Support</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body { font-family: 'Inter', sans-serif; }
            @keyframes slideInRight {
                from { opacity: 0; transform: translateX(100%); }
                to { opacity: 1; transform: translateX(0); }
            }
            .animate-slide-in { animation: slideInRight 0.3s ease-out; }
            
            /* Submenu styles */
            .submenu {
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.3s ease-out;
            }
            .submenu.open {
                max-height: 600px;
            }
            .menu-chevron {
                transition: transform 0.3s ease;
            }
            .menu-chevron.rotated {
                transform: rotate(180deg);
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="h-screen bg-gray-50 flex overflow-hidden">

            {{-- Sidebar --}}
            <aside class="hidden md:flex md:flex-col md:w-64 bg-white border-r border-gray-200 relative">
                {{-- Logo --}}
                <div class="h-16 flex items-center px-6 border-b border-gray-100">
                    <div class="flex items-center">
                        <img src="{{ asset('images/logolonaci.png') }}" alt="SportCash" class="h-10 w-auto">
                    </div>
                </div>

                {{-- Navigation --}}
                <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                    {{-- Menu déroulant Chat --}}
                    <div>
                        <button onclick="toggleChatMenu()" id="chat-menu-btn"
                                class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                                       {{ request()->routeIs(['dashboard', 'conversations.*', 'agents.*', 'teams.*', 'contacts.*', 'canned-responses.*', 'statistics.*']) ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <span>Support Client</span>
                            </div>
                            <svg class="w-4 h-4 menu-chevron {{ request()->routeIs(['dashboard', 'conversations.*', 'agents.*', 'teams.*', 'contacts.*', 'canned-responses.*', 'statistics.*']) ? 'rotated' : '' }}" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        {{-- Sous-menu --}}
                        <div id="chat-submenu" class="submenu {{ request()->routeIs(['dashboard', 'conversations.*', 'agents.*', 'teams.*', 'contacts.*', 'canned-responses.*', 'statistics.*']) ? 'open' : '' }} pl-4 mt-1 space-y-1">
                            @if(auth()->user()?->isAdmin())
                            <a href="{{ route('dashboard') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                                      {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                Dashboard
                            </a>
                            @endif

                            <a href="{{ route('conversations.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                                      {{ request()->routeIs('conversations.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                Conversations
                            </a>

                            @if(auth()->user()?->isAdmin())
                            <a href="{{ route('agents.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                                      {{ request()->routeIs('agents.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Agents
                            </a>

                            <a href="{{ route('teams.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                                      {{ request()->routeIs('teams.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                Equipes
                            </a>
                            @endif

                            <!-- <a href="{{ route('contacts.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                                      {{ request()->routeIs('contacts.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Contacts
                            </a> -->

                            <a href="{{ route('canned-responses.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                                      {{ request()->routeIs('canned-responses.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                                Reponses rapides
                            </a>

                            @if(auth()->user()?->isAdmin())
                            <a href="{{ route('statistics.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                                      {{ request()->routeIs('statistics.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                Rapports
                            </a>
                            @endif
                        </div>
                    </div>

                    {{-- Menu déroulant Campagnes --}}
                    <div>
                        <button onclick="toggleCampaignMenu()" id="campaign-menu-btn"
                                class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                                       {{ request()->routeIs(['campagnes.*']) ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                                </svg>
                                <span>Campagnes</span>
                            </div>
                            <svg class="w-4 h-4 menu-chevron {{ request()->routeIs(['campagnes.*']) ? 'rotated' : '' }}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div id="campaign-submenu" class="submenu {{ request()->routeIs(['campagnes.*']) ? 'open' : '' }} pl-4 mt-1 space-y-1">
                            <a href="{{ route('campagnes.dashboard') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                                      {{ request()->routeIs('campagnes.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                Dashboard
                            </a>

                            <a href="{{ route('campagnes.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                                      {{ request()->routeIs('campagnes.index') || request()->routeIs('campagnes.show') || request()->routeIs('campagnes.create') || request()->routeIs('campagnes.edit') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                Campagnes
                            </a>

                            <a href="{{ route('campagnes.contacts.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                                      {{ request()->routeIs('campagnes.contacts.index') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Contacts
                            </a>

                            <a href="{{ route('campagnes.contacts.import') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                                      {{ request()->routeIs('campagnes.contacts.import') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                Import CSV
                            </a>
                        </div>
                    </div>

                    {{-- Menu déroulant Bot Tracking --}}
  <div>
      <button onclick="toggleBotTrackingMenu()" id="bot-tracking-menu-btn"
              class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                     {{ request()->routeIs('bot-tracking.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
          <div class="flex items-center gap-3">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0
  012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              <span>Bot Tracking</span>
          </div>
          <svg class="w-4 h-4 menu-chevron {{ request()->routeIs('bot-tracking.*') ? 'rotated' : '' }}"
               fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
      </button>

      <div id="bot-tracking-submenu" class="submenu {{ request()->routeIs('bot-tracking.*') ? 'open' : '' }} pl-4 mt-1 space-y-1">

          {{-- Sous-menu : Bot --}}
          <a href="{{ route('bot-tracking.index') }}"
             class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                    {{ request()->routeIs('bot-tracking.index') || request()->routeIs('bot-tracking.active') ||
  request()->routeIs('bot-tracking.conversations*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863
  9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
              </svg>
              Bot
          </a>

          {{-- Sous-menu : Clients --}}
          <a href="{{ route('bot-tracking.clients.index') }}"
             class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                    {{ request()->routeIs('bot-tracking.clients.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
  }}">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10
  0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016
   0z"/>
              </svg>
              Clients
          </a>

          {{-- Sous-menu : Analytics --}}
          <a href="{{ route('bot-tracking.statistics') }}"
             class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                    {{ request()->routeIs('bot-tracking.statistics') || request()->routeIs('bot-tracking.search') ? 'bg-indigo-50 text-indigo-700' :
  'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0
  002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
              Analytics
          </a>
      </div>
  </div>

                    {{-- Menu déroulant Kash Bot --}}
                    <div>
                        <button onclick="toggleKashMenu()" id="kash-menu-btn"
                                class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                                       {{ request()->routeIs('kash.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span>Kash Bot</span>
                            </div>
                            <svg class="w-4 h-4 menu-chevron {{ request()->routeIs('kash.*') ? 'rotated' : '' }}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div id="kash-submenu" class="submenu {{ request()->routeIs('kash.*') ? 'open' : '' }} pl-4 mt-1 space-y-1">
                            <a href="{{ route('kash.dashboard') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                                      {{ request()->routeIs('kash.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                Dashboard
                            </a>
                            <a href="{{ route('kash.reclamations.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                                      {{ request()->routeIs('kash.reclamations.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                                Réclamations
                            </a>
                            <a href="{{ route('kash.escalades.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                                      {{ request()->routeIs('kash.escalades.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Escalades
                            </a>
                        </div>
                    </div>

                    {{-- Menu déroulant Gamification --}}
                    <div>
                        <button onclick="toggleGamificationMenu()" id="gamification-menu-btn"
                                class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                                       {{ request()->routeIs('gamification.*') ? 'bg-violet-50 text-violet-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Gamification</span>
                            </div>
                            <svg class="w-4 h-4 menu-chevron {{ request()->routeIs('gamification.*') ? 'rotated' : '' }}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div id="gamification-submenu" class="submenu {{ request()->routeIs('gamification.*') ? 'open' : '' }} pl-4 mt-1 space-y-1">
                            <a href="{{ route('gamification.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                                      {{ request()->routeIs('gamification.*') ? 'bg-violet-50 text-violet-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Jeux
                            </a>
                        </div>
                    </div>

                    {{-- Separator --}}
                    <div class="my-3 border-t border-gray-100"></div>

                    {{-- Notifications --}}
                    <!-- <button onclick="toggleNotifications()" id="notif-btn"
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                        <div class="relative">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span id="notif-badge" class="hidden absolute -top-1.5 -right-1.5 w-4 h-4 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center">0</span>
                        </div>
                        Notifications
                    </button> -->
                </nav>

                {{-- Notification panel (overlay) --}}
                <div id="notif-panel" class="hidden absolute left-64 bottom-0 top-0 w-80 bg-white border-r border-gray-200 shadow-xl z-40 flex flex-col">
                    <div class="h-14 px-4 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
                        <h3 class="font-semibold text-gray-900 text-sm">Notifications</h3>
                        <div class="flex items-center gap-2">
                            <button onclick="markAllNotifRead()" class="text-[11px] text-primary-600 hover:text-primary-700 font-medium">Tout marquer lu</button>
                            <button onclick="toggleNotifications()" class="w-7 h-7 flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                    <div id="notif-list" class="flex-1 overflow-y-auto scrollbar-thin">
                        <div class="p-8 text-center text-gray-400 text-sm">Chargement...</div>
                    </div>
                    <div id="notif-load-more" class="hidden border-t border-gray-100 flex-shrink-0">
                        <button onclick="loadMoreNotifications()" class="w-full py-2.5 text-xs text-primary-600 hover:text-primary-700 hover:bg-gray-50 font-medium transition">
                            Charger plus
                        </button>
                    </div>
                </div>

                {{-- User profile button (ouvre le modal compte) --}}
                <div class="p-3 border-t border-gray-100">
                    <button onclick="openProfileModal()"
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gray-50 transition-all duration-200 group text-left">
                        <div class="relative flex-shrink-0">
                            <div class="w-9 h-9 bg-indigo-100 rounded-full flex items-center justify-center ring-2 ring-white">
                                <span class="text-sm font-bold text-indigo-700 sidebar-user-initials">
                                    {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 2)) }}
                                </span>
                            </div>
                            <span id="avail-dot" class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white bg-green-500 transition-colors"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate sidebar-user-name">{{ auth()->user()?->name ?? 'Agent' }}</p>
                            <p id="avail-label" class="text-[11px] font-medium text-green-600 transition-colors">En ligne</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 flex-shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </aside>

            {{-- Mobile header --}}
            <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
                <header class="md:hidden h-14 bg-white border-b border-gray-200 flex items-center justify-between px-4 flex-shrink-0">
                    <div class="flex items-center">
                        <img src="{{ asset('images/logolonaci.png') }}" alt="SportCash" class="h-8 w-auto rounded">
                    </div>
                    <div class="flex items-center gap-3">
                        @if(auth()->user()?->isAdmin())
                        <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        </a>
                        @endif
                        <a href="{{ route('conversations.index') }}" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-500 hover:text-red-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            </button>
                        </form>
                    </div>
                </header>

                {{-- Page content --}}
                <main class="flex-1 flex flex-col overflow-hidden">
                    @yield('content')
                </main>
            </div>

        </div>

        {{-- Toast notifications container (WhatsApp-style) --}}
        <div id="msg-toasts" class="fixed bottom-6 right-6 z-50 flex flex-col gap-2 max-w-sm"></div>

        {{-- ══════════════════════════════════════════
             Modal — Mon compte
        ══════════════════════════════════════════ --}}
        @auth
        <div id="profile-modal" class="fixed inset-0 z-50 hidden items-end md:items-center justify-center">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeProfileModal()"></div>
            {{-- Panel --}}
            <div class="relative w-full md:w-[400px] bg-white md:rounded-2xl rounded-t-2xl shadow-2xl flex flex-col max-h-[92vh] md:max-h-[82vh] overflow-hidden">

                {{-- Header --}}
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 flex-shrink-0">
                    <h2 class="text-sm font-semibold text-gray-900">Mon compte</h2>
                    <button onclick="closeProfileModal()" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Identité --}}
                <div class="px-5 py-4 flex items-center gap-4 bg-white border-b border-gray-100 flex-shrink-0">
                    <div class="relative flex-shrink-0">
                        <div class="w-14 h-14 bg-indigo-100 rounded-full flex items-center justify-center ring-4 ring-white shadow-sm">
                            <span class="text-xl font-bold text-indigo-700 sidebar-user-initials">{{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 2)) }}</span>
                        </div>
                        <span id="modal-avail-dot" class="absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-full border-2 border-white bg-green-500 transition-colors"></span>
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900 sidebar-user-name">{{ auth()->user()?->name ?? 'Agent' }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ auth()->user()?->email ?? '' }}</p>
                        <p id="modal-avail-label" class="text-xs font-medium text-green-600 mt-0.5">En ligne</p>
                    </div>
                </div>

                {{-- Onglets --}}
                <div class="flex border-b border-gray-100 px-5 flex-shrink-0">
                    <button onclick="switchProfileTab('status')"  id="tab-status"   class="profile-tab active   py-3 px-1 mr-6 text-xs font-medium border-b-2 border-indigo-600 text-indigo-600 transition-colors">Statut</button>
                    <button onclick="switchProfileTab('profil')"  id="tab-profil"   class="profile-tab inactive py-3 px-1 mr-6 text-xs font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-colors">Profil</button>
                    <button onclick="switchProfileTab('password')" id="tab-password" class="profile-tab inactive py-3 px-1      text-xs font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-colors">Mot de passe</button>
                </div>

                {{-- Contenu onglets --}}
                <div class="flex-1 overflow-y-auto">

                    {{-- ── Statut ── --}}
                    <div id="tab-content-status" class="p-5 space-y-2.5">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-3">Changer le statut</p>

                        <button onclick="setAvailability('online')" data-status="online"
                                class="status-option status-option-online w-full flex items-center gap-4 px-4 py-3.5 rounded-xl border-2 border-green-200 bg-green-50 transition-all hover:border-green-300">
                            <span class="w-3.5 h-3.5 rounded-full bg-green-500 flex-shrink-0 shadow-sm shadow-green-200"></span>
                            <div class="text-left flex-1">
                                <p class="text-sm font-semibold text-green-800">En ligne</p>
                                <p class="text-[11px] text-green-600 opacity-75">Disponible pour les conversations</p>
                            </div>
                            <svg id="check-online" class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </button>

                        <button onclick="setAvailability('busy')" data-status="busy"
                                class="status-option status-option-busy w-full flex items-center gap-4 px-4 py-3.5 rounded-xl border-2 border-transparent bg-gray-50 transition-all hover:border-amber-200 hover:bg-amber-50">
                            <span class="w-3.5 h-3.5 rounded-full bg-amber-500 flex-shrink-0"></span>
                            <div class="text-left flex-1">
                                <p class="text-sm font-semibold text-gray-700">Occupé</p>
                                <p class="text-[11px] text-gray-500">Ne pas déranger</p>
                            </div>
                            <svg id="check-busy" class="w-4 h-4 text-amber-600 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </button>

                        <button onclick="setAvailability('offline')" data-status="offline"
                                class="status-option status-option-offline w-full flex items-center gap-4 px-4 py-3.5 rounded-xl border-2 border-transparent bg-gray-50 transition-all hover:border-gray-200 hover:bg-gray-100">
                            <span class="w-3.5 h-3.5 rounded-full bg-gray-400 flex-shrink-0"></span>
                            <div class="text-left flex-1">
                                <p class="text-sm font-semibold text-gray-700">Hors ligne</p>
                                <p class="text-[11px] text-gray-500">Apparaître hors ligne</p>
                            </div>
                            <svg id="check-offline" class="w-4 h-4 text-gray-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </button>
                    </div>

                    {{-- ── Profil ── --}}
                    <div id="tab-content-profil" class="p-5 hidden">
                        <form id="profile-form" class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Nom complet</label>
                                <input type="text" name="name" id="profile-name"
                                       value="{{ auth()->user()?->name ?? '' }}"
                                       class="w-full px-3.5 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition placeholder-gray-400"
                                       placeholder="Votre nom complet">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Adresse email</label>
                                <input type="email" name="email" id="profile-email"
                                       value="{{ auth()->user()?->email ?? '' }}"
                                       class="w-full px-3.5 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition placeholder-gray-400"
                                       placeholder="votre@email.com">
                            </div>
                            <div id="profile-msg" class="hidden text-xs py-2.5 px-3.5 rounded-lg font-medium"></div>
                            <button type="submit"
                                    class="w-full py-2.5 px-4 bg-indigo-600 text-white text-sm font-medium rounded-xl hover:bg-indigo-700 active:bg-indigo-800 transition shadow-sm flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Enregistrer les modifications
                            </button>
                        </form>
                    </div>

                    {{-- ── Mot de passe ── --}}
                    <div id="tab-content-password" class="p-5 hidden">
                        <form id="password-form" class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Mot de passe actuel</label>
                                <input type="password" name="current_password"
                                       class="w-full px-3.5 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                       placeholder="••••••••" autocomplete="current-password">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Nouveau mot de passe</label>
                                <input type="password" name="password"
                                       class="w-full px-3.5 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                       placeholder="••••••••" autocomplete="new-password">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Confirmer le mot de passe</label>
                                <input type="password" name="password_confirmation"
                                       class="w-full px-3.5 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                       placeholder="••••••••" autocomplete="new-password">
                            </div>
                            <div id="password-msg" class="hidden text-xs py-2.5 px-3.5 rounded-lg font-medium"></div>
                            <button type="submit"
                                    class="w-full py-2.5 px-4 bg-indigo-600 text-white text-sm font-medium rounded-xl hover:bg-indigo-700 active:bg-indigo-800 transition shadow-sm flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Changer le mot de passe
                            </button>
                        </form>
                    </div>

                </div>

                {{-- Pied — Déconnexion --}}
                <div class="px-5 py-4 border-t border-gray-100 flex-shrink-0">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2.5 px-4 py-2.5 rounded-xl bg-red-50 text-red-600 text-sm font-medium hover:bg-red-100 active:bg-red-200 transition border border-red-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Se déconnecter
                        </button>
                    </form>
                </div>

            </div>
        </div>
        @endauth

        @stack('scripts')

        @auth
        <script>
        (function() {
            const TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;
            let notifOpen = false;
            let notifPage = 1;
            let notifTotalPages = 1;
            let notifLoading = false;
            let lastUnreadCount = 0;

            // ═══ Toggle Chat Menu ═══
            window.toggleCampaignMenu = function() {
                const submenu = document.getElementById('campaign-submenu');
                const chevron = document.querySelector('#campaign-menu-btn .menu-chevron');
                if (submenu.classList.contains('open')) {
                    submenu.classList.remove('open');
                    chevron.classList.remove('rotated');
                } else {
                    submenu.classList.add('open');
                    chevron.classList.add('rotated');
                }
            };

            window.toggleChatMenu = function() {
                const submenu = document.getElementById('chat-submenu');
                const chevron = document.querySelector('#chat-menu-btn .menu-chevron');
                
                if (submenu.classList.contains('open')) {
                    submenu.classList.remove('open');
                    chevron.classList.remove('rotated');
                } else {
                    submenu.classList.add('open');
                    chevron.classList.add('rotated');
                }
            };

            window.toggleBotTrackingMenu = function() {
                const submenu = document.getElementById('bot-tracking-submenu');
                const chevron = document.querySelector('#bot-tracking-menu-btn .menu-chevron');
                if (submenu.classList.contains('open')) {
                    submenu.classList.remove('open');
                    chevron.classList.remove('rotated');
                } else {
                    submenu.classList.add('open');
                    chevron.classList.add('rotated');
                }
            };

            window.toggleGamificationMenu = function() {
                const submenu = document.getElementById('gamification-submenu');
                const chevron = document.querySelector('#gamification-menu-btn .menu-chevron');
                if (submenu.classList.contains('open')) {
                    submenu.classList.remove('open');
                    chevron.classList.remove('rotated');
                } else {
                    submenu.classList.add('open');
                    chevron.classList.add('rotated');
                }
            };

            window.toggleKashMenu = function() {
                const submenu = document.getElementById('kash-submenu');
                const chevron = document.querySelector('#kash-menu-btn .menu-chevron');
                if (submenu.classList.contains('open')) {
                    submenu.classList.remove('open');
                    chevron.classList.remove('rotated');
                } else {
                    submenu.classList.add('open');
                    chevron.classList.add('rotated');
                }
            };

            // Poll notification count every 20s
            async function pollNotifCount() {
                try {
                    const r = await fetch('/ajax/notifications?page=1');
                    const data = await r.json();
                    const count = data.meta?.unread_count || 0;
                    const badge = document.getElementById('notif-badge');
                    if (badge) {
                        if (count > 0) {
                            badge.textContent = count > 99 ? '99+' : count;
                            badge.classList.remove('hidden');
                            badge.classList.add('flex');
                        } else {
                            badge.classList.add('hidden');
                            badge.classList.remove('flex');
                        }
                    }
                    // Son si nouveau non-lu detecte
                    if (count > lastUnreadCount && lastUnreadCount > 0 && window._playNotifSound) {
                        window._playNotifSound();
                    }
                    lastUnreadCount = count;
                    // Refresh la liste si le panel est ouvert
                    if (notifOpen && notifPage === 1) {
                        loadNotifications(false);
                    }
                } catch(e) {}
            }

            window.toggleNotifications = function() {
                const panel = document.getElementById('notif-panel');
                notifOpen = !notifOpen;
                if (notifOpen) {
                    panel.classList.remove('hidden');
                    panel.classList.add('flex');
                    notifPage = 1;
                    loadNotifications(true);
                } else {
                    panel.classList.add('hidden');
                    panel.classList.remove('flex');
                }
            };

            function getNotifIcon(type) {
                const icons = {
                    conversation_creation: {
                        bg: 'bg-green-100 text-green-600',
                        svg: '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'
                    },
                    conversation_assignment: {
                        bg: 'bg-blue-100 text-blue-600',
                        svg: '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'
                    },
                    conversation_mention: {
                        bg: 'bg-purple-100 text-purple-600',
                        svg: '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>'
                    },
                };
                // Default for message types (assigned_conversation_new_message, participating_conversation_new_message, etc.)
                const def = {
                    bg: 'bg-primary-100 text-primary-600',
                    svg: '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>'
                };
                return icons[type] || def;
            }

            function renderNotifItem(n) {
                const isRead = !!n.read_at;
                const convId = n.primary_actor?.id || n.primary_actor_id;
                const type = n.notification_type || '';
                const icon = getNotifIcon(type);

                const timeStr = n.created_at ? new Date(typeof n.created_at === 'number' ? n.created_at * 1000 : n.created_at).toLocaleString('fr-FR', {day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit'}) : '';

                // Build description from push_message_title or fallback
                let title = n.push_message_title || '';
                if (!title) {
                    const typeLabels = {
                        conversation_creation: 'Nouvelle conversation',
                        conversation_assignment: 'Conversation assignee',
                        assigned_conversation_new_message: 'Nouveau message',
                        participating_conversation_new_message: 'Nouveau message',
                        conversation_mention: 'Mention',
                    };
                    title = typeLabels[type] || 'Notification';
                    if (convId) title += ' #' + convId;
                }

                const div = document.createElement('div');
                div.className = `px-4 py-3 cursor-pointer hover:bg-gray-50 transition border-b border-gray-50 ${isRead ? '' : 'bg-indigo-50/40'}`;
                div.dataset.notifId = n.id;
                div.innerHTML = `
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-7 h-7 rounded-full ${icon.bg} flex items-center justify-center mt-0.5">${icon.svg}</div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] text-gray-800 leading-snug ${isRead ? '' : 'font-medium'}">${escH(title)}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[10px] text-gray-400">${timeStr}</span>
                                ${!isRead ? '<span class="notif-unread-dot w-1.5 h-1.5 rounded-full bg-primary-500 flex-shrink-0"></span>' : ''}
                                ${convId ? '<span class="text-[10px] text-gray-300 font-mono">#' + convId + '</span>' : ''}
                            </div>
                        </div>
                    </div>`;
                div.addEventListener('click', async () => {
                    if (!isRead) {
                        try { await fetch(`/ajax/notifications/${n.id}/read`, { method: 'POST', headers: { 'X-CSRF-TOKEN': TOKEN } }); } catch(e) {}
                        div.classList.remove('bg-indigo-50/40');
                        const dot = div.querySelector('.notif-unread-dot');
                        if (dot) dot.remove();
                        pollNotifCount();
                    }
                    if (convId && window.loadConversation) {
                        toggleNotifications();
                        window.loadConversation(convId);
                    } else if (convId) {
                        window.location.href = `/conversations/${convId}`;
                    }
                });
                return div;
            }

            async function loadNotifications(showLoading) {
                if (notifLoading) return;
                notifLoading = true;
                const list = document.getElementById('notif-list');
                const loadMoreBtn = document.getElementById('notif-load-more');

                if (showLoading) {
                    list.innerHTML = '<div class="p-8 text-center text-gray-400 text-sm">Chargement...</div>';
                }

                try {
                    const r = await fetch(`/ajax/notifications?page=${notifPage}`);
                    const data = await r.json();
                    const notifs = data.data?.payload || [];
                    const meta = data.meta || {};
                    notifTotalPages = Math.ceil((meta.count || 0) / 15) || 1;

                    if (notifPage === 1 && !notifs.length) {
                        list.innerHTML = `<div class="p-10 text-center">
                            <svg class="w-12 h-12 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <p class="text-sm font-medium text-gray-400">Aucune notification</p>
                            <p class="text-xs text-gray-300 mt-1">Vous serez notifie des nouveaux evenements</p>
                        </div>`;
                        loadMoreBtn.classList.add('hidden');
                        notifLoading = false;
                        return;
                    }

                    if (notifPage === 1) list.innerHTML = '';

                    notifs.forEach(n => {
                        // Skip if already rendered (dedup on refresh)
                        if (list.querySelector(`[data-notif-id="${n.id}"]`)) return;
                        list.appendChild(renderNotifItem(n));
                    });

                    // Show/hide load more button
                    if (notifPage < notifTotalPages && notifs.length > 0) {
                        loadMoreBtn.classList.remove('hidden');
                    } else {
                        loadMoreBtn.classList.add('hidden');
                    }
                } catch(e) {
                    if (notifPage === 1) {
                        list.innerHTML = '<div class="p-8 text-center text-red-400 text-sm">Erreur de chargement</div>';
                    }
                }
                notifLoading = false;
            }

            window.loadMoreNotifications = function() {
                notifPage++;
                loadNotifications(false);
            };

            window.markAllNotifRead = async function() {
                try {
                    await fetch('/ajax/notifications/read-all', { method: 'POST', headers: { 'X-CSRF-TOKEN': TOKEN } });
                    // Update UI immediately
                    document.querySelectorAll('#notif-list > div').forEach(div => {
                        div.classList.remove('bg-indigo-50/40');
                        const dot = div.querySelector('.notif-unread-dot');
                        if (dot) dot.remove();
                        // Remove font-medium from title
                        const title = div.querySelector('.font-medium');
                        if (title) title.classList.remove('font-medium');
                    });
                    pollNotifCount();
                } catch(e) {}
            };

            function escH(t) { const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

            // Initial + periodic poll
            pollNotifCount();
            if (window._notifPollTimer) clearInterval(window._notifPollTimer);
            window._notifPollTimer = setInterval(pollNotifCount, 20000);

            // ═══ Toast WhatsApp-style pour nouveaux messages ═══
            const _prevMsgs = {};
            window._showMsgToast = function(convId, contactName, contactThumb, message) {
                const container = document.getElementById('msg-toasts');
                if (!container) return;
                const toast = document.createElement('div');
                toast.className = 'bg-white border border-gray-200 rounded-xl shadow-2xl p-3 flex items-start gap-3 cursor-pointer hover:bg-gray-50 transition-all duration-300 animate-slide-in';
                toast.style.animation = 'slideInRight 0.3s ease-out';
                const initial = (contactName || '?').charAt(0).toUpperCase();
                const avatarHtml = contactThumb
                    ? `<img src="${contactThumb}" class="w-10 h-10 rounded-full flex-shrink-0" alt="">`
                    : `<div class="w-10 h-10 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-semibold text-sm flex-shrink-0">${initial}</div>`;
                const msgPreview = (message || '').length > 60 ? message.substring(0, 60) + '...' : (message || 'Nouveau message');
                toast.innerHTML = `
                    ${avatarHtml}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">${escH(contactName || 'Contact')}</p>
                        <p class="text-xs text-gray-500 truncate mt-0.5">${escH(msgPreview)}</p>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600 flex-shrink-0 mt-1" onclick="event.stopPropagation(); this.parentElement.remove();">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>`;
                toast.addEventListener('click', () => {
                    toast.remove();
                    if (window.loadConversation) {
                        window.loadConversation(convId);
                    } else {
                        window.location.href = '/conversations/' + convId;
                    }
                });
                container.appendChild(toast);
                // Auto-dismiss apres 8s
                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateX(100%)';
                    setTimeout(() => toast.remove(), 300);
                }, 8000);
            };

            // ═══ Son de notification (Web Audio API) ═══
            let audioCtx = null;
            function playNotifSound() {
                try {
                    if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(880, audioCtx.currentTime);
                    osc.frequency.setValueAtTime(1100, audioCtx.currentTime + 0.08);
                    gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.3);
                    osc.start(audioCtx.currentTime);
                    osc.stop(audioCtx.currentTime + 0.3);
                } catch(e) {}
            }
            // Expose globally pour les pollings
            window._playNotifSound = playNotifSound;

            // ═══ Availability ═══
            const _availCfg = {
                online:  { dot: 'bg-green-500',  label: 'text-green-600',  text: 'En ligne',   btnActive: 'status-option status-option-online  w-full flex items-center gap-4 px-4 py-3.5 rounded-xl border-2 border-green-200 bg-green-50 transition-all hover:border-green-300', btnInactive: 'status-option status-option-online  w-full flex items-center gap-4 px-4 py-3.5 rounded-xl border-2 border-transparent bg-gray-50 transition-all hover:border-green-200 hover:bg-green-50' },
                busy:    { dot: 'bg-amber-500',  label: 'text-amber-600',  text: 'Occupé',     btnActive: 'status-option status-option-busy    w-full flex items-center gap-4 px-4 py-3.5 rounded-xl border-2 border-amber-200 bg-amber-50 transition-all hover:border-amber-300', btnInactive: 'status-option status-option-busy    w-full flex items-center gap-4 px-4 py-3.5 rounded-xl border-2 border-transparent bg-gray-50 transition-all hover:border-amber-200 hover:bg-amber-50' },
                offline: { dot: 'bg-gray-400',   label: 'text-gray-400',   text: 'Hors ligne', btnActive: 'status-option status-option-offline w-full flex items-center gap-4 px-4 py-3.5 rounded-xl border-2 border-gray-200 bg-gray-100 transition-all', btnInactive: 'status-option status-option-offline w-full flex items-center gap-4 px-4 py-3.5 rounded-xl border-2 border-transparent bg-gray-50 transition-all hover:border-gray-200 hover:bg-gray-100' },
            };

            function updateAvailUI(status) {
                const cfg = _availCfg[status] || _availCfg.offline;

                // Sidebar dot + label
                const dot = document.getElementById('avail-dot');
                const label = document.getElementById('avail-label');
                if (dot) dot.className = `absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white ${cfg.dot} transition-colors`;
                if (label) { label.textContent = cfg.text; label.className = `text-[11px] font-medium ${cfg.label} transition-colors`; }

                // Modal dot + label
                const mDot = document.getElementById('modal-avail-dot');
                const mLabel = document.getElementById('modal-avail-label');
                if (mDot) mDot.className = `absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-full border-2 border-white ${cfg.dot} transition-colors`;
                if (mLabel) { mLabel.textContent = cfg.text; mLabel.className = `text-xs font-medium ${cfg.label} mt-0.5`; }

                // Modal status buttons
                ['online', 'busy', 'offline'].forEach(s => {
                    const c = _availCfg[s];
                    const btn = document.querySelector(`.status-option-${s}`);
                    const chk = document.getElementById(`check-${s}`);
                    if (btn) btn.className = s === status ? c.btnActive : c.btnInactive;
                    if (chk) chk.classList.toggle('hidden', s !== status);
                });
            }

            window.setAvailability = async function(status) {
                updateAvailUI(status);
                try {
                    await fetch('/ajax/profile/availability', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                        body: JSON.stringify({ availability: status }),
                    });
                } catch(e) { console.error('Availability:', e); }
            };

            // Chargement initial (défaut : online = vert)
            (async function() {
                try {
                    const r = await fetch('/ajax/profile/availability');
                    const d = await r.json();
                    updateAvailUI(d.availability || 'online');
                } catch(e) { updateAvailUI('online'); }
            })();

            // ═══ Raccourcis clavier globaux ═══
            document.addEventListener('keydown', function(e) {
                // Ne pas intercepter si on est dans un input/textarea (sauf Ctrl combos)
                const inInput = ['INPUT','TEXTAREA','SELECT'].includes(document.activeElement?.tagName);
                const ctrl = e.ctrlKey || e.metaKey;

                // Ctrl+Shift+Enter → Note privee
                if (ctrl && e.shiftKey && e.key === 'Enter') {
                    e.preventDefault();
                    if (window.sendPrivateNote) window.sendPrivateNote();
                    return;
                }

                // Alt+R → Resoudre conversation active
                if (e.altKey && e.key === 'r') {
                    e.preventDefault();
                    if (window.toggleStatus) window.toggleStatus('resolve');
                    return;
                }

                // Alt+O → Reouvrir
                if (e.altKey && e.key === 'o') {
                    e.preventDefault();
                    if (window.toggleStatus) window.toggleStatus('reopen');
                    return;
                }

                // Alt+N → Focus note/message input
                if (e.altKey && e.key === 'n') {
                    e.preventDefault();
                    const input = document.getElementById('message-input');
                    if (input) input.focus();
                    return;
                }

                // Alt+I → Toggle sidebar contact
                if (e.altKey && e.key === 'i') {
                    e.preventDefault();
                    if (window.toggleContactSidebar) window.toggleContactSidebar();
                    return;
                }

                // Alt+K → Afficher aide raccourcis
                if (e.altKey && e.key === 'k') {
                    e.preventDefault();
                    toggleShortcutsHelp();
                    return;
                }
            });

            // ═══ Modal — Mon compte ═══
            window.openProfileModal = function() {
                const m = document.getElementById('profile-modal');
                if (!m) return;
                m.classList.remove('hidden');
                m.classList.add('flex');
            };
            window.closeProfileModal = function() {
                const m = document.getElementById('profile-modal');
                if (!m) return;
                m.classList.add('hidden');
                m.classList.remove('flex');
            };
            window.switchProfileTab = function(tab) {
                ['status', 'profil', 'password'].forEach(t => {
                    const btn = document.getElementById(`tab-${t}`);
                    const panel = document.getElementById(`tab-content-${t}`);
                    if (!btn || !panel) return;
                    const isActive = t === tab;
                    btn.className = `profile-tab ${isActive ? 'active' : 'inactive'} py-3 px-1 ${t !== 'password' ? 'mr-6' : ''} text-xs font-medium border-b-2 ${isActive ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'} transition-colors`;
                    panel.classList.toggle('hidden', !isActive);
                });
            };

            // Fermer sur Escape
            document.addEventListener('keydown', function(ev) {
                if (ev.key === 'Escape') {
                    const m = document.getElementById('profile-modal');
                    if (m && m.classList.contains('flex')) { closeProfileModal(); ev.stopPropagation(); }
                }
            }, true);

            // Formulaire — Profil
            document.getElementById('profile-form')?.addEventListener('submit', async function(e) {
                e.preventDefault();
                const form = e.target;
                const msg  = document.getElementById('profile-msg');
                const name  = form.querySelector('[name=name]').value.trim();
                const email = form.querySelector('[name=email]').value.trim();
                try {
                    const r = await fetch('/ajax/profile/update', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                        body: JSON.stringify({ name, email }),
                    });
                    const res = await r.json();
                    if (res.success) {
                        msg.textContent = 'Profil mis à jour avec succès';
                        msg.className = 'text-xs py-2.5 px-3.5 rounded-lg font-medium bg-green-50 text-green-700';
                        msg.classList.remove('hidden');
                        // Mettre à jour les éléments affichant le nom
                        document.querySelectorAll('.sidebar-user-name').forEach(el => el.textContent = name);
                        document.querySelectorAll('.sidebar-user-initials').forEach(el => el.textContent = name.substring(0, 2).toUpperCase());
                    } else {
                        const errs = res.errors ? Object.values(res.errors).flat().join(' ') : (res.message || 'Erreur');
                        msg.textContent = errs;
                        msg.className = 'text-xs py-2.5 px-3.5 rounded-lg font-medium bg-red-50 text-red-700';
                        msg.classList.remove('hidden');
                    }
                } catch(_) {
                    msg.textContent = 'Erreur réseau';
                    msg.className = 'text-xs py-2.5 px-3.5 rounded-lg font-medium bg-red-50 text-red-700';
                    msg.classList.remove('hidden');
                }
                setTimeout(() => msg.classList.add('hidden'), 4500);
            });

            // Formulaire — Mot de passe
            document.getElementById('password-form')?.addEventListener('submit', async function(e) {
                e.preventDefault();
                const form = e.target;
                const msg  = document.getElementById('password-msg');
                const data = {
                    current_password:      form.querySelector('[name=current_password]').value,
                    password:              form.querySelector('[name=password]').value,
                    password_confirmation: form.querySelector('[name=password_confirmation]').value,
                };
                try {
                    const r = await fetch('/ajax/profile/password', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                        body: JSON.stringify(data),
                    });
                    const res = await r.json();
                    if (res.success) {
                        msg.textContent = 'Mot de passe changé avec succès';
                        msg.className = 'text-xs py-2.5 px-3.5 rounded-lg font-medium bg-green-50 text-green-700';
                        msg.classList.remove('hidden');
                        form.reset();
                    } else {
                        const errs = res.errors ? Object.values(res.errors).flat().join(' ') : (res.message || 'Erreur');
                        msg.textContent = errs;
                        msg.className = 'text-xs py-2.5 px-3.5 rounded-lg font-medium bg-red-50 text-red-700';
                        msg.classList.remove('hidden');
                    }
                } catch(_) {
                    msg.textContent = 'Erreur réseau';
                    msg.className = 'text-xs py-2.5 px-3.5 rounded-lg font-medium bg-red-50 text-red-700';
                    msg.classList.remove('hidden');
                }
                setTimeout(() => msg.classList.add('hidden'), 4500);
            });

            // ═══ Shortcuts help modal ═══
            window._shortcutsOpen = false;
            function toggleShortcutsHelp() {
                let modal = document.getElementById('shortcuts-modal');
                if (!modal) {
                    modal = document.createElement('div');
                    modal.id = 'shortcuts-modal';
                    modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black/40';
                    modal.innerHTML = `
                        <div class="bg-white rounded-2xl shadow-2xl w-96 max-w-[90vw] overflow-hidden">
                            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                                <h3 class="font-semibold text-gray-900 text-sm">Raccourcis clavier</h3>
                                <button onclick="toggleShortcutsHelp()" class="w-7 h-7 flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div class="px-5 py-4 space-y-2.5 text-sm">
                                <div class="flex items-center justify-between"><span class="text-gray-600">Envoyer le message</span><div class="flex gap-1"><kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-[10px] font-mono font-semibold text-gray-600">Enter</kbd></div></div>
                                <div class="flex items-center justify-between"><span class="text-gray-600">Note privee</span><div class="flex gap-1"><kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-[10px] font-mono font-semibold text-gray-600">Ctrl+Shift+Enter</kbd></div></div>
                                <div class="flex items-center justify-between"><span class="text-gray-600">Resoudre</span><div class="flex gap-1"><kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-[10px] font-mono font-semibold text-gray-600">Alt+R</kbd></div></div>
                                <div class="flex items-center justify-between"><span class="text-gray-600">Reouvrir</span><div class="flex gap-1"><kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-[10px] font-mono font-semibold text-gray-600">Alt+O</kbd></div></div>
                                <div class="flex items-center justify-between"><span class="text-gray-600">Focus message</span><div class="flex gap-1"><kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-[10px] font-mono font-semibold text-gray-600">Alt+N</kbd></div></div>
                                <div class="flex items-center justify-between"><span class="text-gray-600">Infos contact</span><div class="flex gap-1"><kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-[10px] font-mono font-semibold text-gray-600">Alt+I</kbd></div></div>
                                <div class="flex items-center justify-between"><span class="text-gray-600">Cette aide</span><div class="flex gap-1"><kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-[10px] font-mono font-semibold text-gray-600">Alt+K</kbd></div></div>
                            </div>
                            <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
                                <p class="text-[10px] text-gray-400 text-center">Sur Mac, utilisez Cmd au lieu de Ctrl</p>
                            </div>
                        </div>`;
                    modal.addEventListener('click', (e) => { if (e.target === modal) toggleShortcutsHelp(); });
                    document.body.appendChild(modal);
                    window._shortcutsOpen = true;
                    return;
                }
                if (window._shortcutsOpen) {
                    modal.remove();
                    window._shortcutsOpen = false;
                } else {
                    document.body.appendChild(modal);
                    window._shortcutsOpen = true;
                }
            }
            window.toggleShortcutsHelp = toggleShortcutsHelp;
        })();
        </script>
        @endauth
    </body>
</html>