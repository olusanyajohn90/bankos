@extends('layouts.app')

@section('title', 'Internal Chat')

@section('content')
{{-- Neutralise the layout's px/py padding so the chat fills the viewport --}}
<div class="-mx-4 -my-8 sm:-mx-6 lg:-mx-8" x-data="chatApp()" x-init="init()">

    <div class="flex h-[calc(100vh-4rem)] overflow-hidden bg-bankos-bg">

        {{-- ══════════════════════════════════════════════════
             LEFT PANEL — Conversation List
        ══════════════════════════════════════════════════ --}}
        <div class="w-80 flex-shrink-0 flex flex-col border-r border-bankos-border bg-white overflow-hidden"
            :class="{'hidden sm:flex': activeConversation, 'flex': !activeConversation}">

            {{-- Header --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-bankos-border bg-white">
                <h2 class="text-base font-semibold text-bankos-text">Chat</h2>
                <div class="flex items-center gap-1">
                    {{-- Search messages --}}
                    <button @click="showSearchModal = true" title="Search Messages"
                        class="p-1.5 rounded-lg text-bankos-text-sec hover:bg-gray-100 hover:text-bankos-primary transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                    {{-- Starred messages --}}
                    <button @click="openStarredPanel()" title="Starred Messages"
                        class="p-1.5 rounded-lg text-bankos-text-sec hover:bg-gray-100 hover:text-bankos-primary transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </button>
                    {{-- New DM --}}
                    <button @click="showNewDirectModal = true; userSearch = ''; filterUsers()"
                        title="New Direct Message"
                        class="p-1.5 rounded-lg text-bankos-text-sec hover:bg-gray-100 hover:text-bankos-primary transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </button>
                    {{-- New Group --}}
                    <button @click="showNewGroupModal = true; userSearch = ''; filterUsers()"
                        title="New Group"
                        class="p-1.5 rounded-lg text-bankos-text-sec hover:bg-gray-100 hover:text-bankos-primary transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Search conversations --}}
            <div class="px-3 py-2 border-b border-bankos-border">
                <div class="relative">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-bankos-muted pointer-events-none"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input x-model="convSearch" type="text" placeholder="Filter conversations..."
                        class="w-full pl-8 pr-3 py-1.5 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary">
                </div>
            </div>

            {{-- Loading spinner --}}
            <div x-show="loadingConversations" class="flex justify-center py-6">
                <svg class="animate-spin w-5 h-5 text-bankos-primary" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
            </div>

            {{-- Conversation List --}}
            <div class="flex-1 overflow-y-auto" x-show="!loadingConversations">

                {{-- Empty state --}}
                <template x-if="filteredConversations().length === 0 && !loadingConversations">
                    <div class="flex flex-col items-center justify-center h-full text-bankos-muted py-12 px-4 text-center">
                        <svg class="w-10 h-10 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <p class="text-sm">No conversations yet</p>
                        <p class="text-xs mt-1 text-bankos-muted">Start a direct message or create a group</p>
                    </div>
                </template>

                <template x-for="conv in filteredConversations()" :key="conv.id">
                    <div @click="openConversation(conv)"
                        :class="activeConversation && activeConversation.id === conv.id
                            ? 'bg-bankos-primary/5 border-l-2 border-l-bankos-primary'
                            : 'border-l-2 border-l-transparent hover:bg-gray-50'"
                        class="flex items-center gap-3 px-3 py-3 cursor-pointer transition-colors">

                        {{-- Avatar with online indicator --}}
                        <div class="relative flex-shrink-0">
                            <div :class="conv.is_group ? 'bg-purple-100 text-purple-700' : 'bg-bankos-primary/10 text-bankos-primary'"
                                class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold">
                                <template x-if="conv.is_group">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </template>
                                <template x-if="!conv.is_group">
                                    <span x-text="conv.avatar_initials"></span>
                                </template>
                            </div>
                            {{-- Online dot --}}
                            <span x-show="conv.is_online" class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-white rounded-full"></span>
                            {{-- Unread badge --}}
                            <span x-show="conv.unread_count > 0"
                                x-text="conv.unread_count > 99 ? '99+' : conv.unread_count"
                                class="absolute -top-1 -right-1 min-w-[1.1rem] h-[1.1rem] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center leading-none">
                            </span>
                        </div>

                        {{-- Text content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1">
                                <div class="flex items-center gap-1 min-w-0">
                                    <span :class="conv.unread_count > 0 ? 'font-semibold text-bankos-text' : 'font-medium text-bankos-text'"
                                        class="text-sm truncate" x-text="conv.display_name"></span>
                                    {{-- Muted icon --}}
                                    <template x-if="conv.is_muted">
                                        <svg class="w-3.5 h-3.5 text-bankos-muted flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" clip-rule="evenodd"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                                        </svg>
                                    </template>
                                </div>
                                <span class="text-[10px] text-bankos-muted flex-shrink-0" x-text="conv.last_message_at ?? ''"></span>
                            </div>
                            <p :class="conv.unread_count > 0 ? 'text-bankos-text font-medium' : 'text-bankos-text-sec'"
                                class="text-xs truncate mt-0.5" x-text="conv.last_message_preview ?? 'No messages yet'"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════
             RIGHT PANEL — Message Pane
        ══════════════════════════════════════════════════ --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden"
            :class="{'hidden sm:flex': !activeConversation, 'flex': activeConversation}">

            {{-- Empty state (no active conversation) --}}
            <template x-if="!activeConversation">
                <div class="flex-1 flex flex-col items-center justify-center text-bankos-muted">
                    <svg class="w-16 h-16 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <p class="text-base font-medium text-bankos-text-sec">Select a conversation or start a new one</p>
                    <p class="text-sm mt-1">Your messages will appear here</p>
                </div>
            </template>

            {{-- Active conversation --}}
            <template x-if="activeConversation">
                <div class="flex-1 flex flex-col min-h-0 overflow-hidden">

                    {{-- Conversation header --}}
                    <div class="flex items-center justify-between px-4 py-3 border-b border-bankos-border bg-white flex-shrink-0">
                        <div class="flex items-center gap-3 min-w-0">
                            {{-- Back button (mobile) --}}
                            <button @click="activeConversation = null" class="sm:hidden p-1 rounded-lg text-bankos-text-sec hover:bg-gray-100 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <div class="relative flex-shrink-0">
                                <div :class="activeConversation.is_group ? 'bg-purple-100 text-purple-700' : 'bg-bankos-primary/10 text-bankos-primary'"
                                    class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold">
                                    <template x-if="activeConversation.is_group">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </template>
                                    <template x-if="!activeConversation.is_group">
                                        <span x-text="activeConversation.avatar_initials"></span>
                                    </template>
                                </div>
                                {{-- Online dot --}}
                                <span x-show="activeConversation.is_online" class="absolute bottom-0 right-0 w-2 h-2 bg-green-500 border-2 border-white rounded-full"></span>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-bankos-text truncate" x-text="activeConversation.display_name"></h3>
                                <p class="text-xs text-bankos-text-sec">
                                    <template x-if="typingUsers.length > 0">
                                        <span class="text-bankos-primary italic" x-text="typingDisplay()"></span>
                                    </template>
                                    <template x-if="typingUsers.length === 0">
                                        <span x-text="activeConversation.is_group
                                            ? activeConversation.participant_count + ' members'
                                            : (activeConversation.is_online ? 'Online' : (activeConversation.last_seen ?? 'Direct message'))"></span>
                                    </template>
                                </p>
                            </div>
                        </div>
                        {{-- Header actions --}}
                        <div class="flex items-center gap-1">
                            {{-- Pinned messages --}}
                            <button @click="openPinnedPanel()" title="Pinned Messages"
                                class="p-1.5 rounded-lg text-bankos-text-sec hover:bg-gray-100 hover:text-bankos-primary transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                </svg>
                            </button>
                            {{-- Tasks --}}
                            <button @click="openTasksPanel()" title="Tasks"
                                class="p-1.5 rounded-lg text-bankos-text-sec hover:bg-gray-100 hover:text-bankos-primary transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                            </button>
                            {{-- Header dropdown --}}
                            <div class="relative" x-data="{headerMenu: false}">
                                <button @click="headerMenu = !headerMenu"
                                    class="p-1.5 rounded-lg text-bankos-text-sec hover:bg-gray-100 hover:text-bankos-primary transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"/>
                                    </svg>
                                </button>
                                <div x-show="headerMenu" @click.outside="headerMenu = false" x-transition
                                    class="absolute right-0 top-full mt-1 w-52 bg-white rounded-xl shadow-lg border border-bankos-border z-50 py-1 overflow-hidden">
                                    {{-- Mute / Unmute --}}
                                    <button @click="toggleMute(); headerMenu = false"
                                        class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-bankos-text hover:bg-gray-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                                        </svg>
                                        <span x-text="activeConversation.is_muted ? 'Unmute' : 'Mute'"></span>
                                    </button>
                                    {{-- Disappearing messages --}}
                                    <div class="relative" x-data="{disappearSub: false}">
                                        <button @click="disappearSub = !disappearSub"
                                            class="w-full flex items-center justify-between gap-2.5 px-4 py-2 text-sm text-bankos-text hover:bg-gray-50 transition-colors">
                                            <div class="flex items-center gap-2.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span>Disappearing Messages</span>
                                            </div>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </button>
                                        <div x-show="disappearSub" class="border-t border-bankos-border bg-gray-50">
                                            <template x-for="opt in [{label:'Off',val:0},{label:'5 minutes',val:5},{label:'1 hour',val:60},{label:'24 hours',val:1440},{label:'7 days',val:10080}]" :key="opt.val">
                                                <button @click="setDisappearing(opt.val); headerMenu = false; disappearSub = false"
                                                    :class="activeConversation.disappear_minutes == opt.val ? 'bg-bankos-primary/10 text-bankos-primary font-medium' : 'text-bankos-text'"
                                                    class="w-full text-left px-8 py-1.5 text-sm hover:bg-gray-100 transition-colors"
                                                    x-text="opt.label"></button>
                                            </template>
                                        </div>
                                    </div>
                                    {{-- Group settings (groups only) --}}
                                    <template x-if="activeConversation.is_group">
                                        <div>
                                            <button @click="showGroupSettings = true; headerMenu = false"
                                                class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-bankos-text hover:bg-gray-50 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                </svg>
                                                <span>Group Settings</span>
                                            </button>
                                            <button @click="generateInviteLink(); headerMenu = false"
                                                class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-bankos-text hover:bg-gray-50 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                                </svg>
                                                <span>Get Invite Link</span>
                                            </button>
                                            <button @click="showCreatePoll = true; headerMenu = false"
                                                class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-bankos-text hover:bg-gray-50 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                                </svg>
                                                <span>Create Poll</span>
                                            </button>
                                            <button @click="showCreateTask = true; headerMenu = false"
                                                class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-bankos-text hover:bg-gray-50 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                <span>Create Task</span>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Reconnect banner --}}
                    <div x-show="!pollingActive" x-transition
                        class="flex items-center justify-between px-4 py-2 bg-amber-50 border-b border-amber-200 text-amber-800 text-sm flex-shrink-0">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                            <span>Connection lost. New messages may not appear.</span>
                        </div>
                        <button @click="reconnect()"
                            class="text-xs font-semibold underline hover:no-underline ml-4 flex-shrink-0">
                            Reconnect
                        </button>
                    </div>

                    {{-- Messages area --}}
                    <div class="flex-1 overflow-y-auto px-4 py-4 space-y-1" id="messages-container"
                        x-ref="messagesContainer">

                        {{-- Load older messages --}}
                        <div x-show="hasMore" class="flex justify-center mb-3">
                            <button @click="loadOlderMessages()"
                                :disabled="loadingMessages"
                                class="text-xs text-bankos-primary hover:underline disabled:opacity-50 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                </svg>
                                Load older messages
                            </button>
                        </div>

                        {{-- Loading spinner --}}
                        <div x-show="loadingMessages" class="flex justify-center py-8">
                            <svg class="animate-spin w-6 h-6 text-bankos-primary" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                        </div>

                        {{-- Messages --}}
                        <template x-if="!loadingMessages">
                            <div>
                                <template x-for="(msg, index) in messages" :key="msg.id">
                                    <div>
                                        {{-- Date separator --}}
                                        <template x-if="index === 0 || messages[index - 1].date_label !== msg.date_label">
                                            <div class="flex items-center gap-3 my-4">
                                                <div class="flex-1 h-px bg-bankos-border"></div>
                                                <span class="text-xs text-bankos-muted font-medium px-2" x-text="msg.date_label"></span>
                                                <div class="flex-1 h-px bg-bankos-border"></div>
                                            </div>
                                        </template>

                                        {{-- Message row --}}
                                        <div :class="msg.sender_id === currentUserId ? 'flex-row-reverse' : 'flex-row'"
                                            class="flex items-end gap-2 mb-1 group"
                                            @mouseenter="hoveredMsg = msg.id" @mouseleave="hoveredMsg = null">

                                            {{-- Avatar (others only) --}}
                                            <template x-if="msg.sender_id !== currentUserId">
                                                <div class="w-7 h-7 rounded-full bg-gray-200 text-gray-600 text-xs font-semibold flex items-center justify-center flex-shrink-0 mb-0.5"
                                                    x-text="msg.sender_initials"></div>
                                            </template>

                                            {{-- Bubble --}}
                                            <div :class="msg.sender_id === currentUserId
                                                    ? 'items-end'
                                                    : 'items-start'"
                                                class="flex flex-col max-w-[70%]">

                                                {{-- Sender name (others, group only) --}}
                                                <template x-if="msg.sender_id !== currentUserId && activeConversation.is_group">
                                                    <span class="text-[11px] text-bankos-text-sec font-medium mb-0.5 ml-1"
                                                        x-text="msg.sender_name"></span>
                                                </template>

                                                {{-- Bubble body --}}
                                                <div :class="msg.sender_id === currentUserId
                                                        ? 'bg-bankos-primary text-white rounded-2xl rounded-br-sm'
                                                        : 'bg-white text-bankos-text border border-bankos-border rounded-2xl rounded-bl-sm'"
                                                    class="px-3 py-2 shadow-sm relative">

                                                    {{-- Pin indicator --}}
                                                    <template x-if="msg.is_pinned">
                                                        <div class="absolute -top-2 -right-2">
                                                            <svg class="w-3.5 h-3.5 text-amber-500" fill="currentColor" viewBox="0 0 24 24">
                                                                <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                                            </svg>
                                                        </div>
                                                    </template>

                                                    {{-- Disappearing indicator --}}
                                                    <template x-if="msg.is_disappearing">
                                                        <div :class="msg.sender_id === currentUserId ? 'text-white/50' : 'text-bankos-muted'"
                                                            class="flex items-center gap-1 mb-1">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                            <span class="text-[9px]">Disappearing</span>
                                                        </div>
                                                    </template>

                                                    {{-- Reply-to quote --}}
                                                    <template x-if="msg.reply_to">
                                                        <div :class="msg.sender_id === currentUserId
                                                                ? 'border-white/40 bg-white/10'
                                                                : 'border-bankos-primary/30 bg-bankos-primary/5'"
                                                            class="border-l-2 pl-2 mb-1.5 rounded-r-sm">
                                                            <p :class="msg.sender_id === currentUserId ? 'text-white/80' : 'text-bankos-primary'"
                                                                class="text-[10px] font-semibold" x-text="msg.reply_to.sender_name"></p>
                                                            <p :class="msg.sender_id === currentUserId ? 'text-white/70' : 'text-bankos-text-sec'"
                                                                class="text-[11px] truncate" x-text="msg.reply_to.body_preview"></p>
                                                        </div>
                                                    </template>

                                                    {{-- Deleted message --}}
                                                    <template x-if="msg.is_deleted">
                                                        <p :class="msg.sender_id === currentUserId ? 'text-white/60' : 'text-bankos-muted'"
                                                            class="text-sm italic">[Message deleted]</p>
                                                    </template>

                                                    {{-- Body text with formatting --}}
                                                    <template x-if="!msg.is_deleted && msg.body">
                                                        <p class="text-sm whitespace-pre-wrap break-words" x-html="formatMessage(msg.body)"></p>
                                                    </template>

                                                    {{-- Poll --}}
                                                    <template x-if="msg.poll">
                                                        <div class="mt-1">
                                                            <div class="font-semibold text-sm mb-2" x-text="msg.poll.question"></div>
                                                            <div class="space-y-1.5">
                                                                <template x-for="opt in msg.poll.options" :key="opt.id">
                                                                    <div>
                                                                        <button @click="votePoll(msg.poll.id, opt.id, msg.poll.allow_multiple)"
                                                                            :disabled="msg.poll.is_closed"
                                                                            :class="opt.voted
                                                                                ? (msg.sender_id === currentUserId ? 'border-white/60 bg-white/20' : 'border-bankos-primary bg-bankos-primary/10')
                                                                                : (msg.sender_id === currentUserId ? 'border-white/30 hover:bg-white/10' : 'border-bankos-border hover:bg-gray-50')"
                                                                            class="w-full text-left px-3 py-1.5 rounded-lg border text-xs transition-colors disabled:opacity-60 relative overflow-hidden">
                                                                            {{-- Progress bar --}}
                                                                            <div :class="msg.sender_id === currentUserId ? 'bg-white/15' : 'bg-bankos-primary/10'"
                                                                                class="absolute inset-y-0 left-0 transition-all duration-300"
                                                                                :style="'width:' + (msg.poll.total_votes ? Math.round(opt.votes/msg.poll.total_votes*100) : 0) + '%'"></div>
                                                                            <div class="relative flex justify-between items-center">
                                                                                <span x-text="opt.text"></span>
                                                                                <span class="ml-2 opacity-70" x-text="opt.votes + ' vote' + (opt.votes !== 1 ? 's' : '')"></span>
                                                                            </div>
                                                                        </button>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                            <div class="flex items-center justify-between mt-2">
                                                                <span :class="msg.sender_id === currentUserId ? 'text-white/60' : 'text-bankos-muted'"
                                                                    class="text-[10px]" x-text="msg.poll.total_votes + ' total votes' + (msg.poll.is_closed ? ' (closed)' : '')"></span>
                                                                <template x-if="msg.sender_id === currentUserId && !msg.poll.is_closed">
                                                                    <button @click="closePoll(msg.poll.id)"
                                                                        class="text-[10px] underline opacity-70 hover:opacity-100">Close Poll</button>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </template>

                                                    {{-- Task card --}}
                                                    <template x-if="msg.task">
                                                        <div :class="msg.sender_id === currentUserId ? 'bg-white/10 border-white/20' : 'bg-bankos-bg border-bankos-border'"
                                                            class="mt-1 p-2.5 rounded-lg border">
                                                            <div class="flex items-start justify-between gap-2">
                                                                <div class="min-w-0">
                                                                    <div class="flex items-center gap-1.5 mb-1">
                                                                        <span :class="{
                                                                            'bg-red-100 text-red-700': msg.task.priority === 'high',
                                                                            'bg-amber-100 text-amber-700': msg.task.priority === 'medium',
                                                                            'bg-green-100 text-green-700': msg.task.priority === 'low'
                                                                        }" class="text-[10px] font-medium px-1.5 py-0.5 rounded" x-text="msg.task.priority"></span>
                                                                        <span :class="{
                                                                            'bg-blue-100 text-blue-700': msg.task.status === 'pending',
                                                                            'bg-amber-100 text-amber-700': msg.task.status === 'in_progress',
                                                                            'bg-green-100 text-green-700': msg.task.status === 'completed'
                                                                        }" class="text-[10px] font-medium px-1.5 py-0.5 rounded" x-text="msg.task.status.replace('_',' ')"></span>
                                                                    </div>
                                                                    <p class="text-sm font-medium" x-text="msg.task.title"></p>
                                                                    <template x-if="msg.task.description">
                                                                        <p class="text-xs opacity-70 mt-0.5" x-text="msg.task.description"></p>
                                                                    </template>
                                                                    <div class="flex items-center gap-3 mt-1.5 text-[10px] opacity-60">
                                                                        <template x-if="msg.task.assigned_to_name">
                                                                            <span x-text="'Assigned: ' + msg.task.assigned_to_name"></span>
                                                                        </template>
                                                                        <template x-if="msg.task.due_date">
                                                                            <span x-text="'Due: ' + msg.task.due_date"></span>
                                                                        </template>
                                                                    </div>
                                                                </div>
                                                                {{-- Status toggle --}}
                                                                <div class="flex-shrink-0">
                                                                    <select @change="updateTaskStatus(msg.task.id, $event.target.value)"
                                                                        :value="msg.task.status"
                                                                        class="text-[10px] rounded border px-1 py-0.5 bg-transparent">
                                                                        <option value="pending">Pending</option>
                                                                        <option value="in_progress">In Progress</option>
                                                                        <option value="completed">Done</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>

                                                    {{-- Attachments --}}
                                                    <template x-if="msg.attachments && msg.attachments.length > 0">
                                                        <div class="mt-1.5 space-y-1.5">
                                                            <template x-for="att in msg.attachments" :key="att.id">
                                                                <div>
                                                                    {{-- Image with lightbox --}}
                                                                    <template x-if="att.is_image">
                                                                        <img :src="att.url" :alt="att.file_name"
                                                                            @click="openLightbox(att.url, att.file_name)"
                                                                            class="max-w-[240px] rounded-lg cursor-pointer hover:opacity-90 transition-opacity border border-white/20">
                                                                    </template>
                                                                    {{-- Audio (voice messages) --}}
                                                                    <template x-if="att.mime_type && att.mime_type.startsWith('audio/')">
                                                                        <div :class="msg.sender_id === currentUserId ? 'bg-white/10' : 'bg-bankos-bg'"
                                                                            class="flex items-center gap-2 px-2 py-1.5 rounded-lg">
                                                                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                                                            </svg>
                                                                            <audio :src="att.url" controls class="h-8 max-w-[200px]" preload="none"></audio>
                                                                        </div>
                                                                    </template>
                                                                    {{-- File --}}
                                                                    <template x-if="!att.is_image && !(att.mime_type && att.mime_type.startsWith('audio/'))">
                                                                        <a :href="att.url"
                                                                            :class="msg.sender_id === currentUserId
                                                                                ? 'bg-white/10 hover:bg-white/20 text-white border-white/20'
                                                                                : 'bg-bankos-bg hover:bg-gray-100 text-bankos-text border-bankos-border'"
                                                                            class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg border text-xs transition-colors"
                                                                            download>
                                                                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                                            </svg>
                                                                            <span class="truncate max-w-[160px]" x-text="att.file_name"></span>
                                                                            <span :class="msg.sender_id === currentUserId ? 'text-white/60' : 'text-bankos-muted'"
                                                                                class="flex-shrink-0"
                                                                                x-text="att.file_size_kb ? att.file_size_kb + ' KB' : ''"></span>
                                                                        </a>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </template>

                                                    {{-- Timestamp + edited + delivery status --}}
                                                    <div :class="msg.sender_id === currentUserId ? 'justify-end' : 'justify-start'"
                                                        class="flex items-center gap-1 mt-1">
                                                        <template x-if="msg.is_edited && !msg.is_deleted">
                                                            <span :class="msg.sender_id === currentUserId ? 'text-white/50' : 'text-bankos-muted'"
                                                                class="text-[10px] italic">edited</span>
                                                        </template>
                                                        <span :class="msg.sender_id === currentUserId ? 'text-white/60' : 'text-bankos-muted'"
                                                            class="text-[10px]" x-text="msg.created_at"></span>
                                                        {{-- Read receipts (own messages only) --}}
                                                        <template x-if="msg.sender_id === currentUserId && msg.delivery_status && !msg.is_deleted">
                                                            <span :class="{
                                                                'text-white/50': msg.delivery_status === 'sent',
                                                                'text-white/70': msg.delivery_status === 'delivered',
                                                                'text-blue-200': msg.delivery_status === 'read'
                                                            }" class="text-[10px] ml-0.5" x-html="msg.delivery_status === 'sent' ? '&#10003;' : '&#10003;&#10003;'"></span>
                                                        </template>
                                                    </div>
                                                </div>

                                                {{-- Reaction pills --}}
                                                <template x-if="msg.reactions && Object.keys(msg.reactions).length > 0">
                                                    <div :class="msg.sender_id === currentUserId ? 'justify-end' : 'justify-start'"
                                                        class="flex flex-wrap gap-1 mt-0.5 px-1">
                                                        <template x-for="(data, emoji) in msg.reactions" :key="emoji">
                                                            <button @click="toggleReaction(msg.id, emoji)"
                                                                :class="data.reacted
                                                                    ? 'bg-bankos-primary/15 border-bankos-primary/30'
                                                                    : 'bg-white border-bankos-border hover:bg-gray-50'"
                                                                class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full border text-xs transition-colors">
                                                                <span x-text="emoji"></span>
                                                                <span class="text-[10px] text-bankos-text-sec" x-text="data.count"></span>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </template>

                                                {{-- Action buttons (on hover) --}}
                                                <template x-if="!msg.is_deleted">
                                                    <div :class="msg.sender_id === currentUserId ? 'flex-row-reverse' : 'flex-row'"
                                                        class="flex gap-0.5 mt-0.5 opacity-0 group-hover:opacity-100 transition-opacity px-1">
                                                        {{-- Quick reactions --}}
                                                        <div class="relative" x-data="{showReactions: false}">
                                                            <button @click="showReactions = !showReactions" title="React"
                                                                class="p-1 rounded text-bankos-muted hover:text-bankos-primary hover:bg-gray-100 transition-colors">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                </svg>
                                                            </button>
                                                            <div x-show="showReactions" @click.outside="showReactions = false"
                                                                :class="msg.sender_id === currentUserId ? 'right-0' : 'left-0'"
                                                                class="absolute bottom-full mb-1 flex gap-0.5 bg-white rounded-full shadow-lg border border-bankos-border px-1.5 py-1 z-10">
                                                                <template x-for="em in quickEmojis" :key="em">
                                                                    <button @click="toggleReaction(msg.id, em); showReactions = false"
                                                                        class="text-base hover:scale-125 transition-transform px-0.5" x-text="em"></button>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        {{-- Reply --}}
                                                        <button @click="replyTo(msg)" title="Reply"
                                                            class="p-1 rounded text-bankos-muted hover:text-bankos-primary hover:bg-gray-100 transition-colors">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                                            </svg>
                                                        </button>
                                                        {{-- Forward --}}
                                                        <button @click="openForwardModal(msg)" title="Forward"
                                                            class="p-1 rounded text-bankos-muted hover:text-bankos-primary hover:bg-gray-100 transition-colors">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                                            </svg>
                                                        </button>
                                                        {{-- Star --}}
                                                        <button @click="toggleStar(msg)" :title="msg.is_starred ? 'Unstar' : 'Star'"
                                                            :class="msg.is_starred ? 'text-amber-500' : 'text-bankos-muted'"
                                                            class="p-1 rounded hover:text-amber-500 hover:bg-gray-100 transition-colors">
                                                            <svg class="w-3.5 h-3.5" :fill="msg.is_starred ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                                            </svg>
                                                        </button>
                                                        {{-- Pin --}}
                                                        <button @click="togglePin(msg)" :title="msg.is_pinned ? 'Unpin' : 'Pin'"
                                                            :class="msg.is_pinned ? 'text-bankos-primary' : 'text-bankos-muted'"
                                                            class="p-1 rounded hover:text-bankos-primary hover:bg-gray-100 transition-colors">
                                                            <svg class="w-3.5 h-3.5" :fill="msg.is_pinned ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                                            </svg>
                                                        </button>
                                                        {{-- Edit (own messages) --}}
                                                        <template x-if="msg.sender_id === currentUserId">
                                                            <button @click="editMessage(msg)" title="Edit"
                                                                class="p-1 rounded text-bankos-muted hover:text-bankos-primary hover:bg-gray-100 transition-colors">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                </svg>
                                                            </button>
                                                        </template>
                                                        {{-- Delete (own messages) --}}
                                                        <template x-if="msg.sender_id === currentUserId">
                                                            <button @click="deleteMessage(msg)" title="Delete"
                                                                class="p-1 rounded text-bankos-muted hover:text-red-500 hover:bg-red-50 transition-colors">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                </svg>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- Empty messages state --}}
                                <template x-if="messages.length === 0">
                                    <div class="flex flex-col items-center justify-center py-16 text-bankos-muted">
                                        <svg class="w-10 h-10 mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                        </svg>
                                        <p class="text-sm">No messages yet -- say hello!</p>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    {{-- Input area --}}
                    <div class="border-t border-bankos-border bg-white flex-shrink-0 px-4 py-3">

                        {{-- Reply preview --}}
                        <div x-show="replyingTo" x-transition
                            class="flex items-center gap-2 mb-2 px-3 py-1.5 bg-bankos-primary/5 border border-bankos-primary/20 rounded-lg">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-bankos-primary" x-text="replyingTo?.sender_name"></p>
                                <p class="text-xs text-bankos-text-sec truncate" x-text="replyingTo?.body || replyingTo?.attachments?.[0]?.file_name || ''"></p>
                            </div>
                            <button @click="cancelReply()" class="p-1 rounded text-bankos-muted hover:text-bankos-text transition-colors flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Edit mode indicator --}}
                        <div x-show="editingMessage" x-transition
                            class="flex items-center gap-2 mb-2 px-3 py-1.5 bg-amber-50 border border-amber-200 rounded-lg">
                            <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <span class="text-xs text-amber-800 flex-1">Editing message</span>
                            <button @click="editingMessage = null; editBody = ''"
                                class="text-xs text-amber-700 hover:text-amber-900 underline flex-shrink-0">Cancel</button>
                        </div>

                        {{-- File preview --}}
                        <div x-show="attachedFile" x-transition
                            class="flex items-center gap-2 mb-2 px-3 py-1.5 bg-bankos-bg border border-bankos-border rounded-lg">
                            <svg class="w-4 h-4 text-bankos-text-sec flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            <span class="text-xs text-bankos-text truncate flex-1" x-text="attachedFile?.name"></span>
                            <span class="text-[10px] text-bankos-muted flex-shrink-0" x-text="attachedFile ? (attachedFile.size / 1024).toFixed(0) + ' KB' : ''"></span>
                            <button @click="attachedFile = null; $refs.fileInput.value = ''"
                                class="p-0.5 rounded text-bankos-muted hover:text-red-500 transition-colors flex-shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Voice recording indicator --}}
                        <div x-show="isRecording" x-transition
                            class="flex items-center gap-2 mb-2 px-3 py-1.5 bg-red-50 border border-red-200 rounded-lg">
                            <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                            <span class="text-xs text-red-700 flex-1">Recording... <span x-text="recordingDuration + 's'"></span></span>
                            <button @click="stopRecording()" class="text-xs text-red-700 hover:text-red-900 font-medium">Stop & Send</button>
                            <button @click="cancelRecording()" class="text-xs text-red-500 hover:text-red-700">Cancel</button>
                        </div>

                        {{-- Input row --}}
                        <div class="flex items-end gap-2">
                            {{-- Attach file --}}
                            <template x-if="!editingMessage">
                                <div class="flex items-center gap-0.5">
                                    <input type="file" x-ref="fileInput" class="hidden"
                                        @change="handleFileSelect($event)">
                                    <button @click="$refs.fileInput.click()"
                                        class="p-2 rounded-lg text-bankos-text-sec hover:bg-gray-100 hover:text-bankos-primary transition-colors flex-shrink-0"
                                        title="Attach file (max 10MB)">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                        </svg>
                                    </button>
                                    {{-- Emoji picker --}}
                                    <div class="relative" x-data="{showEmoji: false}">
                                        <button @click="showEmoji = !showEmoji"
                                            class="p-2 rounded-lg text-bankos-text-sec hover:bg-gray-100 hover:text-bankos-primary transition-colors flex-shrink-0"
                                            title="Emoji">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </button>
                                        <div x-show="showEmoji" @click.outside="showEmoji = false" x-transition
                                            class="absolute bottom-full left-0 mb-2 w-72 bg-white rounded-xl shadow-lg border border-bankos-border z-50 overflow-hidden">
                                            <div class="px-3 py-2 border-b border-bankos-border">
                                                <input x-model="emojiSearch" type="text" placeholder="Search emoji..."
                                                    class="w-full px-2 py-1 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-1 focus:ring-bankos-primary/30">
                                            </div>
                                            <div class="p-2 max-h-48 overflow-y-auto">
                                                <template x-for="cat in filteredEmojiCategories()" :key="cat.name">
                                                    <div class="mb-2">
                                                        <p class="text-[10px] text-bankos-muted font-medium uppercase mb-1 px-1" x-text="cat.name"></p>
                                                        <div class="flex flex-wrap gap-0.5">
                                                            <template x-for="em in cat.emojis" :key="em">
                                                                <button @click="insertEmoji(em); showEmoji = false"
                                                                    class="w-7 h-7 flex items-center justify-center text-lg rounded hover:bg-gray-100 transition-colors"
                                                                    x-text="em"></button>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Voice record --}}
                                    <button @click="startRecording()" x-show="!isRecording"
                                        class="p-2 rounded-lg text-bankos-text-sec hover:bg-gray-100 hover:text-bankos-primary transition-colors flex-shrink-0"
                                        title="Voice message">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>

                            {{-- Textarea --}}
                            <textarea
                                x-model="editingMessage ? editBody : newMessage"
                                @keydown.enter.prevent="handleEnter($event)"
                                @input="autoResize($event.target); sendTypingIndicator()"
                                x-ref="messageInput"
                                rows="1"
                                :placeholder="editingMessage ? 'Edit your message...' : 'Type a message... (*bold* _italic_ ~strike~ `code`)'"
                                class="flex-1 resize-none px-3 py-2 text-sm rounded-xl border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary max-h-32 overflow-y-auto leading-relaxed"
                            ></textarea>

                            {{-- Send / Save button --}}
                            <button
                                @click="editingMessage ? saveEdit() : sendMessage()"
                                :disabled="editingMessage ? !editBody.trim() : (!newMessage.trim() && !attachedFile)"
                                class="p-2 rounded-xl bg-bankos-primary text-white hover:bg-bankos-primary-dark disabled:opacity-40 disabled:cursor-not-allowed transition-colors flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                            </button>
                        </div>

                        <p class="text-[10px] text-bankos-muted mt-1.5">Enter to send &middot; Shift+Enter for new line &middot; *bold* _italic_ ~strike~ `code`</p>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         MODAL — New Direct Message
    ══════════════════════════════════════════════════ --}}
    <div x-show="showNewDirectModal" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
        @keydown.escape.window="showNewDirectModal = false">
        <div @click.outside="showNewDirectModal = false" x-transition
            class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-bankos-border">
                <h3 class="text-sm font-semibold text-bankos-text">New Direct Message</h3>
                <button @click="showNewDirectModal = false"
                    class="p-1 rounded-lg text-bankos-muted hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-4 pt-3 pb-2">
                <input x-model="userSearch" @input="filterUsers()" type="text" placeholder="Search by name..."
                    class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary">
            </div>
            <div class="max-h-72 overflow-y-auto px-2 pb-3">
                <template x-if="filteredUsers.length === 0">
                    <p class="text-sm text-bankos-muted text-center py-6">No users found</p>
                </template>
                <template x-for="u in filteredUsers" :key="u.id">
                    <button @click="createDirect(u.id)"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-bankos-primary/5 transition-colors text-left">
                        <div class="w-8 h-8 rounded-full bg-bankos-primary/10 text-bankos-primary text-xs font-semibold flex items-center justify-center flex-shrink-0"
                            x-text="getInitials(u.name)"></div>
                        <span class="text-sm text-bankos-text truncate" x-text="u.name"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         MODAL — New Group
    ══════════════════════════════════════════════════ --}}
    <div x-show="showNewGroupModal" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
        @keydown.escape.window="showNewGroupModal = false">
        <div @click.outside="showNewGroupModal = false" x-transition
            class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-bankos-border">
                <h3 class="text-sm font-semibold text-bankos-text">New Group Chat</h3>
                <button @click="showNewGroupModal = false"
                    class="p-1 rounded-lg text-bankos-muted hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-4 py-3 space-y-3">
                <input x-model="groupName" type="text" placeholder="Group name..."
                    class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary">
                <input x-model="userSearch" @input="filterUsers()" type="text" placeholder="Search members..."
                    class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary">

                <p class="text-xs text-bankos-text-sec" x-show="selectedUserIds.length > 0"
                    x-text="selectedUserIds.length + ' member(s) selected'"></p>

                <div class="max-h-48 overflow-y-auto space-y-0.5">
                    <template x-if="filteredUsers.length === 0">
                        <p class="text-sm text-bankos-muted text-center py-4">No users found</p>
                    </template>
                    <template x-for="u in filteredUsers" :key="u.id">
                        <label class="flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-bankos-primary/5 transition-colors cursor-pointer">
                            <input type="checkbox" :value="u.id" x-model="selectedUserIds"
                                class="w-4 h-4 rounded text-bankos-primary border-bankos-border focus:ring-bankos-primary/30">
                            <div class="w-7 h-7 rounded-full bg-bankos-primary/10 text-bankos-primary text-xs font-semibold flex items-center justify-center flex-shrink-0"
                                x-text="getInitials(u.name)"></div>
                            <span class="text-sm text-bankos-text truncate" x-text="u.name"></span>
                        </label>
                    </template>
                </div>
            </div>
            <div class="px-4 pb-4 flex justify-end gap-2">
                <button @click="showNewGroupModal = false"
                    class="px-4 py-2 text-sm rounded-lg border border-bankos-border text-bankos-text hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button @click="createGroup()"
                    :disabled="!groupName.trim() || selectedUserIds.length === 0"
                    class="px-4 py-2 text-sm rounded-lg bg-bankos-primary text-white hover:bg-bankos-primary-dark disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                    Create Group
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         MODAL — Search Messages
    ══════════════════════════════════════════════════ --}}
    <div x-show="showSearchModal" x-transition.opacity
        class="fixed inset-0 z-50 flex items-start justify-center pt-20 p-4 bg-black/40"
        @keydown.escape.window="showSearchModal = false">
        <div @click.outside="showSearchModal = false" x-transition
            class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
            <div class="flex items-center gap-3 px-5 py-4 border-b border-bankos-border">
                <svg class="w-5 h-5 text-bankos-muted flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input x-model="searchQuery" @input.debounce.400ms="performSearch()" type="text" placeholder="Search messages..."
                    class="flex-1 text-sm border-none bg-transparent focus:outline-none text-bankos-text" autofocus>
                <button @click="showSearchModal = false"
                    class="p-1 rounded-lg text-bankos-muted hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="max-h-80 overflow-y-auto">
                <template x-if="searchLoading">
                    <div class="flex justify-center py-8">
                        <svg class="animate-spin w-5 h-5 text-bankos-primary" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </div>
                </template>
                <template x-if="!searchLoading && searchResults.length === 0 && searchQuery.trim()">
                    <p class="text-sm text-bankos-muted text-center py-8">No messages found</p>
                </template>
                <template x-for="r in searchResults" :key="r.id">
                    <button @click="goToSearchResult(r); showSearchModal = false"
                        class="w-full text-left px-5 py-3 hover:bg-gray-50 transition-colors border-b border-bankos-border last:border-b-0">
                        <div class="flex items-center justify-between mb-0.5">
                            <span class="text-xs font-semibold text-bankos-primary" x-text="r.conversation_name"></span>
                            <span class="text-[10px] text-bankos-muted" x-text="r.created_at"></span>
                        </div>
                        <p class="text-xs text-bankos-text-sec" x-text="r.sender_name"></p>
                        <p class="text-sm text-bankos-text mt-0.5 line-clamp-2" x-text="r.body"></p>
                    </button>
                </template>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         MODAL — Forward Message
    ══════════════════════════════════════════════════ --}}
    <div x-show="showForwardModal" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
        @keydown.escape.window="showForwardModal = false">
        <div @click.outside="showForwardModal = false" x-transition
            class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-bankos-border">
                <h3 class="text-sm font-semibold text-bankos-text">Forward Message</h3>
                <button @click="showForwardModal = false"
                    class="p-1 rounded-lg text-bankos-muted hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-4 pt-3 pb-2">
                <input x-model="forwardSearch" type="text" placeholder="Search conversations..."
                    class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary">
            </div>
            <div class="max-h-72 overflow-y-auto px-2 pb-3">
                <template x-for="conv in forwardFilteredConversations()" :key="conv.id">
                    <button @click="forwardMessage(conv.id)"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-bankos-primary/5 transition-colors text-left">
                        <div :class="conv.is_group ? 'bg-purple-100 text-purple-700' : 'bg-bankos-primary/10 text-bankos-primary'"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold flex-shrink-0">
                            <template x-if="conv.is_group">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </template>
                            <template x-if="!conv.is_group">
                                <span x-text="conv.avatar_initials"></span>
                            </template>
                        </div>
                        <span class="text-sm text-bankos-text truncate" x-text="conv.display_name"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         PANEL — Pinned Messages (slide-over)
    ══════════════════════════════════════════════════ --}}
    <div x-show="showPinnedPanel" x-transition.opacity
        class="fixed inset-0 z-50 flex justify-end bg-black/30"
        @keydown.escape.window="showPinnedPanel = false">
        <div @click.outside="showPinnedPanel = false" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
            class="w-80 bg-white h-full shadow-xl flex flex-col">
            <div class="flex items-center justify-between px-4 py-3 border-b border-bankos-border">
                <h3 class="text-sm font-semibold text-bankos-text">Pinned Messages</h3>
                <button @click="showPinnedPanel = false" class="p-1 rounded-lg text-bankos-muted hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                <template x-if="pinnedMessages.length === 0">
                    <p class="text-sm text-bankos-muted text-center py-8">No pinned messages</p>
                </template>
                <template x-for="pm in pinnedMessages" :key="pm.id">
                    <div class="p-3 bg-bankos-bg rounded-lg border border-bankos-border">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-semibold text-bankos-text" x-text="pm.sender_name"></span>
                            <span class="text-[10px] text-bankos-muted" x-text="pm.created_at"></span>
                        </div>
                        <p class="text-sm text-bankos-text-sec" x-text="pm.body || '[Attachment]'"></p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         PANEL — Starred Messages (slide-over)
    ══════════════════════════════════════════════════ --}}
    <div x-show="showStarredPanel" x-transition.opacity
        class="fixed inset-0 z-50 flex justify-end bg-black/30"
        @keydown.escape.window="showStarredPanel = false">
        <div @click.outside="showStarredPanel = false" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
            class="w-80 bg-white h-full shadow-xl flex flex-col">
            <div class="flex items-center justify-between px-4 py-3 border-b border-bankos-border">
                <h3 class="text-sm font-semibold text-bankos-text">Starred Messages</h3>
                <button @click="showStarredPanel = false" class="p-1 rounded-lg text-bankos-muted hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                <template x-if="starredMessages.length === 0">
                    <p class="text-sm text-bankos-muted text-center py-8">No starred messages</p>
                </template>
                <template x-for="sm in starredMessages" :key="sm.id">
                    <div class="p-3 bg-bankos-bg rounded-lg border border-bankos-border">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-semibold text-bankos-text" x-text="sm.sender_name"></span>
                            <span class="text-[10px] text-bankos-muted" x-text="sm.created_at"></span>
                        </div>
                        <p class="text-sm text-bankos-text-sec" x-text="sm.body || '[Attachment]'"></p>
                        <p class="text-[10px] text-bankos-primary mt-1" x-text="sm.conversation_name"></p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         PANEL — Tasks (slide-over)
    ══════════════════════════════════════════════════ --}}
    <div x-show="showTasksPanel" x-transition.opacity
        class="fixed inset-0 z-50 flex justify-end bg-black/30"
        @keydown.escape.window="showTasksPanel = false">
        <div @click.outside="showTasksPanel = false" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
            class="w-96 bg-white h-full shadow-xl flex flex-col">
            <div class="flex items-center justify-between px-4 py-3 border-b border-bankos-border">
                <h3 class="text-sm font-semibold text-bankos-text">Tasks</h3>
                <div class="flex items-center gap-1">
                    <button @click="showCreateTask = true" class="p-1 rounded-lg text-bankos-text-sec hover:bg-gray-100 hover:text-bankos-primary transition-colors" title="New Task">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>
                    <button @click="showTasksPanel = false" class="p-1 rounded-lg text-bankos-muted hover:bg-gray-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                <template x-if="tasksList.length === 0">
                    <p class="text-sm text-bankos-muted text-center py-8">No tasks yet</p>
                </template>
                <template x-for="t in tasksList" :key="t.id">
                    <div class="p-3 bg-bankos-bg rounded-lg border border-bankos-border">
                        <div class="flex items-center gap-1.5 mb-1">
                            <span :class="{
                                'bg-red-100 text-red-700': t.priority === 'high',
                                'bg-amber-100 text-amber-700': t.priority === 'medium',
                                'bg-green-100 text-green-700': t.priority === 'low'
                            }" class="text-[10px] font-medium px-1.5 py-0.5 rounded" x-text="t.priority"></span>
                            <select @change="updateTaskStatus(t.id, $event.target.value)"
                                :value="t.status"
                                class="text-[10px] rounded border px-1 py-0.5 bg-white">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Done</option>
                            </select>
                        </div>
                        <p class="text-sm font-medium text-bankos-text" x-text="t.title"></p>
                        <template x-if="t.description">
                            <p class="text-xs text-bankos-text-sec mt-0.5" x-text="t.description"></p>
                        </template>
                        <div class="flex items-center gap-3 mt-1.5 text-[10px] text-bankos-muted">
                            <template x-if="t.assigned_to_name">
                                <span x-text="'Assigned: ' + t.assigned_to_name"></span>
                            </template>
                            <template x-if="t.due_date">
                                <span x-text="'Due: ' + t.due_date"></span>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         MODAL — Create Poll
    ══════════════════════════════════════════════════ --}}
    <div x-show="showCreatePoll" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
        @keydown.escape.window="showCreatePoll = false">
        <div @click.outside="showCreatePoll = false" x-transition
            class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-bankos-border">
                <h3 class="text-sm font-semibold text-bankos-text">Create Poll</h3>
                <button @click="showCreatePoll = false"
                    class="p-1 rounded-lg text-bankos-muted hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-5 py-4 space-y-3">
                <input x-model="pollQuestion" type="text" placeholder="Question..."
                    class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary">
                <div class="space-y-2">
                    <template x-for="(opt, i) in pollOptions" :key="i">
                        <div class="flex items-center gap-2">
                            <input x-model="pollOptions[i]" type="text" :placeholder="'Option ' + (i+1)"
                                class="flex-1 px-3 py-1.5 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-1 focus:ring-bankos-primary/30">
                            <button x-show="pollOptions.length > 2" @click="pollOptions.splice(i, 1)"
                                class="p-1 text-bankos-muted hover:text-red-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                    <button @click="pollOptions.push('')" x-show="pollOptions.length < 10"
                        class="text-xs text-bankos-primary hover:underline">+ Add option</button>
                </div>
                <label class="flex items-center gap-2 text-sm text-bankos-text">
                    <input type="checkbox" x-model="pollAllowMultiple" class="w-4 h-4 rounded text-bankos-primary border-bankos-border focus:ring-bankos-primary/30">
                    Allow multiple answers
                </label>
                <label class="flex items-center gap-2 text-sm text-bankos-text">
                    <input type="checkbox" x-model="pollAnonymous" class="w-4 h-4 rounded text-bankos-primary border-bankos-border focus:ring-bankos-primary/30">
                    Anonymous voting
                </label>
            </div>
            <div class="px-5 pb-4 flex justify-end gap-2">
                <button @click="showCreatePoll = false"
                    class="px-4 py-2 text-sm rounded-lg border border-bankos-border text-bankos-text hover:bg-gray-50 transition-colors">Cancel</button>
                <button @click="createPoll()"
                    :disabled="!pollQuestion.trim() || pollOptions.filter(o => o.trim()).length < 2"
                    class="px-4 py-2 text-sm rounded-lg bg-bankos-primary text-white hover:bg-bankos-primary-dark disabled:opacity-40 disabled:cursor-not-allowed transition-colors">Create Poll</button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         MODAL — Create Task
    ══════════════════════════════════════════════════ --}}
    <div x-show="showCreateTask" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
        @keydown.escape.window="showCreateTask = false">
        <div @click.outside="showCreateTask = false" x-transition
            class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-bankos-border">
                <h3 class="text-sm font-semibold text-bankos-text">Create Task</h3>
                <button @click="showCreateTask = false"
                    class="p-1 rounded-lg text-bankos-muted hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-5 py-4 space-y-3">
                <input x-model="taskTitle" type="text" placeholder="Task title..."
                    class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary">
                <textarea x-model="taskDescription" placeholder="Description (optional)..." rows="2"
                    class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary resize-none"></textarea>
                <template x-if="activeConversation && activeConversation.is_group">
                    <select x-model="taskAssignedTo"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary">
                        <option value="">Assign to (optional)</option>
                        <template x-for="u in (activeConversation.participants || allUsers)" :key="u.id">
                            <option :value="u.id" x-text="u.name"></option>
                        </template>
                    </select>
                </template>
                <template x-if="activeConversation && !activeConversation.is_group">
                    <p class="text-xs text-bankos-text-sec px-1">Task will be assigned to the other participant automatically.</p>
                </template>
                <select x-model="taskPriority"
                    class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary">
                    <option value="low">Low Priority</option>
                    <option value="medium">Medium Priority</option>
                    <option value="high">High Priority</option>
                </select>
                <input x-model="taskDueDate" type="date"
                    class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary">
            </div>
            <div class="px-5 pb-4 flex justify-end gap-2">
                <button @click="showCreateTask = false"
                    class="px-4 py-2 text-sm rounded-lg border border-bankos-border text-bankos-text hover:bg-gray-50 transition-colors">Cancel</button>
                <button @click="createTask()"
                    :disabled="!taskTitle.trim()"
                    class="px-4 py-2 text-sm rounded-lg bg-bankos-primary text-white hover:bg-bankos-primary-dark disabled:opacity-40 disabled:cursor-not-allowed transition-colors">Create Task</button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         MODAL — Group Settings
    ══════════════════════════════════════════════════ --}}
    <div x-show="showGroupSettings" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
        @keydown.escape.window="showGroupSettings = false">
        <div @click.outside="showGroupSettings = false" x-transition
            class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-bankos-border">
                <h3 class="text-sm font-semibold text-bankos-text">Group Settings</h3>
                <button @click="showGroupSettings = false"
                    class="p-1 rounded-lg text-bankos-muted hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-5 py-4 space-y-3">
                <div>
                    <label class="text-xs font-medium text-bankos-text-sec mb-1 block">Group Name</label>
                    <input x-model="groupSettingsName" type="text"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary">
                </div>
                <div>
                    <label class="text-xs font-medium text-bankos-text-sec mb-1 block">Description</label>
                    <textarea x-model="groupSettingsDesc" rows="2"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary resize-none"></textarea>
                </div>
            </div>
            <div class="px-5 pb-4 flex justify-end gap-2">
                <button @click="showGroupSettings = false"
                    class="px-4 py-2 text-sm rounded-lg border border-bankos-border text-bankos-text hover:bg-gray-50 transition-colors">Cancel</button>
                <button @click="saveGroupSettings()"
                    class="px-4 py-2 text-sm rounded-lg bg-bankos-primary text-white hover:bg-bankos-primary-dark transition-colors">Save</button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         LIGHTBOX — Image viewer
    ══════════════════════════════════════════════════ --}}
    <div x-show="lightboxUrl" x-transition.opacity
        class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80"
        @click="lightboxUrl = null" @keydown.escape.window="lightboxUrl = null">
        <div class="absolute top-4 right-4 flex items-center gap-2 z-10">
            <a :href="lightboxUrl" :download="lightboxName" @click.stop
                class="p-2 rounded-full bg-white/20 hover:bg-white/30 text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>
            <button @click="lightboxUrl = null" class="p-2 rounded-full bg-white/20 hover:bg-white/30 text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <img :src="lightboxUrl" @click.stop class="max-w-[90vw] max-h-[90vh] rounded-lg shadow-2xl object-contain">
    </div>

</div>
@endsection

@push('scripts')
<script>
function chatApp() {
    return {
        // ── State ──────────────────────────────────────────────────────────
        conversations: [],
        activeConversation: null,
        messages: [],
        hasMore: false,
        loadingMessages: false,
        loadingConversations: false,
        newMessage: '',
        attachedFile: null,
        replyingTo: null,
        editingMessage: null,
        editBody: '',
        showNewDirectModal: false,
        showNewGroupModal: false,
        groupName: '',
        selectedUserIds: [],
        userSearch: '',
        filteredUsers: [],
        convSearch: '',
        consecutiveFailures: 0,
        pollingActive: true,
        lastMessageId: null,
        convListRefreshInterval: null,
        msgPollInterval: null,
        heartbeatInterval: null,
        typingTimeout: null,
        preselectedId: '{{ $preselectedId ?? "" }}',
        currentUserId: {{ auth()->id() }},
        allUsers: @json($tenantUsers),
        hoveredMsg: null,

        // ── New feature state ──────────────────────────────────────────────
        quickEmojis: ['\u{1F44D}', '\u{2764}\u{FE0F}', '\u{1F602}', '\u{1F62E}', '\u{1F622}', '\u{1F64F}'],
        showSearchModal: false,
        searchQuery: '',
        searchResults: [],
        searchLoading: false,
        showForwardModal: false,
        forwardSearch: '',
        forwardingMessage: null,
        showPinnedPanel: false,
        pinnedMessages: [],
        showStarredPanel: false,
        starredMessages: [],
        showTasksPanel: false,
        tasksList: [],
        showCreatePoll: false,
        pollQuestion: '',
        pollOptions: ['', ''],
        pollAllowMultiple: false,
        pollAnonymous: false,
        showCreateTask: false,
        taskTitle: '',
        taskDescription: '',
        taskAssignedTo: '',
        taskPriority: 'medium',
        taskDueDate: '',
        showGroupSettings: false,
        groupSettingsName: '',
        groupSettingsDesc: '',
        lightboxUrl: null,
        lightboxName: '',
        typingUsers: [],
        isRecording: false,
        mediaRecorder: null,
        recordingChunks: [],
        recordingDuration: 0,
        recordingTimer: null,
        emojiSearch: '',
        emojiCategories: [
            { name: 'Smileys', emojis: ['\u{1F600}','\u{1F603}','\u{1F604}','\u{1F601}','\u{1F606}','\u{1F605}','\u{1F602}','\u{1F923}','\u{1F60A}','\u{1F607}','\u{1F642}','\u{1F643}','\u{1F609}','\u{1F60C}','\u{1F60D}','\u{1F970}','\u{1F618}','\u{1F617}','\u{1F619}','\u{1F61A}','\u{1F60B}','\u{1F61B}','\u{1F61C}','\u{1F61D}','\u{1F911}','\u{1F917}','\u{1F914}','\u{1F910}','\u{1F928}','\u{1F610}','\u{1F611}','\u{1F636}','\u{1F644}','\u{1F60F}','\u{1F612}','\u{1F61E}','\u{1F61F}','\u{1F620}','\u{1F621}','\u{1F622}','\u{1F625}','\u{1F62E}','\u{1F631}','\u{1F633}','\u{1F634}','\u{1F637}'] },
            { name: 'Gestures', emojis: ['\u{1F44D}','\u{1F44E}','\u{1F44A}','\u{270A}','\u{1F91B}','\u{1F91C}','\u{1F44F}','\u{1F64C}','\u{1F450}','\u{1F932}','\u{1F91D}','\u{1F64F}','\u{270D}\u{FE0F}','\u{1F485}','\u{1F4AA}','\u{1F9E0}','\u{270C}\u{FE0F}','\u{1F91E}','\u{1F91F}','\u{1F918}','\u{1F448}','\u{1F449}','\u{1F446}','\u{1F447}','\u{261D}\u{FE0F}'] },
            { name: 'Hearts', emojis: ['\u{2764}\u{FE0F}','\u{1F9E1}','\u{1F49B}','\u{1F49A}','\u{1F499}','\u{1F49C}','\u{1F5A4}','\u{1F90D}','\u{1F90E}','\u{1F494}','\u{2763}\u{FE0F}','\u{1F495}','\u{1F49E}','\u{1F493}','\u{1F497}','\u{1F496}','\u{1F498}','\u{1F49D}'] },
            { name: 'Objects', emojis: ['\u{1F525}','\u{2B50}','\u{1F31F}','\u{2728}','\u{1F388}','\u{1F389}','\u{1F3C6}','\u{1F4A1}','\u{1F4B0}','\u{1F4BB}','\u{1F4F1}','\u{1F4E7}','\u{1F4C4}','\u{1F512}','\u{1F513}','\u{1F527}','\u{2699}\u{FE0F}','\u{1F6A8}','\u{2705}','\u{274C}','\u{2757}','\u{2753}','\u{1F4CC}','\u{1F4CB}'] },
        ],

        // ── Init ────────────────────────────────────────────────────────────
        async init() {
            await this.loadConversations();

            if (this.preselectedId) {
                const conv = this.conversations.find(c => c.id == this.preselectedId);
                if (conv) await this.openConversation(conv);
            }

            // Poll new messages every 8s
            this.msgPollInterval = setInterval(() => {
                if (this.pollingActive && this.activeConversation) {
                    this.pollNewMessages();
                }
            }, 8000);

            // Refresh conversation list every 15s
            this.convListRefreshInterval = setInterval(() => {
                if (this.pollingActive) {
                    this.loadConversations(false);
                }
            }, 15000);

            // Heartbeat every 30s
            this.heartbeatInterval = setInterval(() => {
                if (this.pollingActive) {
                    this.sendHeartbeat();
                }
            }, 30000);

            // Initial heartbeat
            this.sendHeartbeat();
        },

        // ── Helpers ─────────────────────────────────────────────────────────
        csrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        },

        getInitials(name) {
            if (!name) return '?';
            return name.trim().split(/\s+/).slice(0, 2).map(w => w[0].toUpperCase()).join('');
        },

        filteredConversations() {
            if (!this.convSearch.trim()) return this.conversations;
            const q = this.convSearch.toLowerCase();
            return this.conversations.filter(c =>
                c.display_name.toLowerCase().includes(q) ||
                (c.last_message_preview ?? '').toLowerCase().includes(q)
            );
        },

        filterUsers() {
            const q = this.userSearch.toLowerCase().trim();
            this.filteredUsers = q
                ? this.allUsers.filter(u => u.name.toLowerCase().includes(q))
                : [...this.allUsers];
        },

        scrollToBottom(smooth = false) {
            this.$nextTick(() => {
                const el = this.$refs.messagesContainer;
                if (el) el.scrollTo({ top: el.scrollHeight, behavior: smooth ? 'smooth' : 'auto' });
            });
        },

        autoResize(el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 128) + 'px';
        },

        handleEnter(event) {
            if (event.shiftKey) {
                if (this.editingMessage) {
                    this.editBody += '\n';
                } else {
                    this.newMessage += '\n';
                }
                return;
            }
            if (this.editingMessage) {
                this.saveEdit();
            } else {
                this.sendMessage();
            }
        },

        onFailure() {
            this.consecutiveFailures++;
            if (this.consecutiveFailures >= 5) {
                this.pollingActive = false;
            }
        },

        onSuccess() {
            this.consecutiveFailures = 0;
        },

        reconnect() {
            this.pollingActive = true;
            this.consecutiveFailures = 0;
            this.loadConversations(false);
            if (this.activeConversation) this.loadMessages();
        },

        // ── Message Formatting ──────────────────────────────────────────────
        formatMessage(body) {
            if (!body) return '';
            // Escape HTML first
            let text = body.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

            // Code blocks (``` ... ```)
            text = text.replace(/```([\s\S]*?)```/g, '<pre class="bg-black/10 rounded px-2 py-1 my-1 text-xs font-mono overflow-x-auto">$1</pre>');
            // Inline code
            text = text.replace(/`([^`]+)`/g, '<code class="bg-black/10 rounded px-1 py-0.5 text-xs font-mono">$1</code>');
            // Bold
            text = text.replace(/\*([^*]+)\*/g, '<strong>$1</strong>');
            // Italic
            text = text.replace(/_([^_]+)_/g, '<em>$1</em>');
            // Strikethrough
            text = text.replace(/~([^~]+)~/g, '<del>$1</del>');
            // URLs
            text = text.replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank" rel="noopener" class="underline hover:no-underline break-all">$1</a>');

            return text;
        },

        // ── Typing indicator ────────────────────────────────────────────────
        sendTypingIndicator() {
            if (this.typingTimeout) return;
            if (!this.activeConversation) return;
            this.typingTimeout = setTimeout(() => { this.typingTimeout = null; }, 3000);
            fetch('/chat/heartbeat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ typing_in: this.activeConversation.id }),
            }).catch(() => {});
        },

        async sendHeartbeat() {
            try {
                await fetch('/chat/heartbeat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({}),
                });
            } catch {}
        },

        typingDisplay() {
            if (this.typingUsers.length === 0) return '';
            if (this.typingUsers.length === 1) return this.typingUsers[0] + ' is typing...';
            if (this.typingUsers.length === 2) return this.typingUsers.join(' and ') + ' are typing...';
            return this.typingUsers.slice(0, 2).join(', ') + ' and others are typing...';
        },

        async fetchPresence() {
            if (!this.activeConversation) return;
            try {
                const res = await fetch(`/chat/conversations/${this.activeConversation.id}/presence`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (res.ok) {
                    const data = await res.json();
                    this.typingUsers = data.typing ?? [];
                    if (data.is_online !== undefined) {
                        this.activeConversation.is_online = data.is_online;
                    }
                    if (data.last_seen !== undefined) {
                        this.activeConversation.last_seen = data.last_seen;
                    }
                }
            } catch {}
        },

        // ── Conversations ────────────────────────────────────────────────────
        async loadConversations(showSpinner = true) {
            if (showSpinner) this.loadingConversations = true;
            try {
                const res = await fetch('{{ route("chat.conversations") }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!res.ok) throw new Error('Failed');
                const data = await res.json();
                this.conversations = data.conversations ?? [];
                this.onSuccess();
            } catch {
                this.onFailure();
            } finally {
                this.loadingConversations = false;
            }
        },

        async openConversation(conv) {
            this.activeConversation = conv;
            this.replyingTo = null;
            this.editingMessage = null;
            this.editBody = '';
            this.newMessage = '';
            this.attachedFile = null;
            this.lastMessageId = null;
            this.typingUsers = [];

            const url = new URL(window.location.href);
            url.searchParams.set('conversation_id', conv.id);
            window.history.replaceState({}, '', url.toString());

            await this.loadMessages();
            this.fetchPresence();

            const idx = this.conversations.findIndex(c => c.id === conv.id);
            if (idx !== -1) this.conversations[idx].unread_count = 0;

            this.$nextTick(() => this.$refs.messageInput?.focus());
        },

        // ── Messages ─────────────────────────────────────────────────────────
        async loadMessages() {
            if (!this.activeConversation) return;
            this.loadingMessages = true;
            this.messages = [];
            this.hasMore = false;
            try {
                const res = await fetch(`/chat/conversations/${this.activeConversation.id}/messages`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!res.ok) throw new Error('Failed');
                const data = await res.json();
                this.messages = data.messages ?? [];
                this.hasMore = data.has_more ?? false;
                if (this.messages.length) {
                    this.lastMessageId = this.messages[this.messages.length - 1].id;
                }
                this.onSuccess();
                this.scrollToBottom();
            } catch {
                this.onFailure();
            } finally {
                this.loadingMessages = false;
            }
        },

        async pollNewMessages() {
            if (!this.activeConversation) return;
            const afterId = this.lastMessageId;
            const url = afterId
                ? `/chat/conversations/${this.activeConversation.id}/messages?after_id=${afterId}`
                : `/chat/conversations/${this.activeConversation.id}/messages`;
            try {
                const res = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!res.ok) throw new Error('Failed');
                const data = await res.json();
                const newMsgs = data.messages ?? [];
                if (newMsgs.length) {
                    this.messages.push(...newMsgs);
                    this.lastMessageId = newMsgs[newMsgs.length - 1].id;
                    const last = newMsgs[newMsgs.length - 1];
                    const idx = this.conversations.findIndex(c => c.id === this.activeConversation.id);
                    if (idx !== -1) {
                        this.conversations[idx].last_message_preview = last.body || 'Attachment';
                        this.conversations[idx].last_message_at = 'Just now';
                        this.conversations[idx].unread_count = 0;
                    }
                    this.scrollToBottom(true);
                }
                this.onSuccess();
            } catch {
                this.onFailure();
            }
            // Also fetch presence during polls
            this.fetchPresence();
        },

        async loadOlderMessages() {
            if (!this.activeConversation || !this.hasMore || this.loadingMessages) return;
            const firstId = this.messages[0]?.id;
            if (!firstId) return;
            this.loadingMessages = true;
            const container = this.$refs.messagesContainer;
            const prevHeight = container?.scrollHeight ?? 0;
            try {
                const res = await fetch(
                    `/chat/conversations/${this.activeConversation.id}/messages?before_id=${firstId}`,
                    { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
                );
                if (!res.ok) throw new Error('Failed');
                const data = await res.json();
                const older = data.messages ?? [];
                this.messages.unshift(...older);
                this.hasMore = data.has_more ?? false;
                this.onSuccess();
                this.$nextTick(() => {
                    if (container) {
                        container.scrollTop = container.scrollHeight - prevHeight;
                    }
                });
            } catch {
                this.onFailure();
            } finally {
                this.loadingMessages = false;
            }
        },

        // ── Send / Edit / Delete ─────────────────────────────────────────────
        async sendMessage() {
            if (!this.activeConversation) return;
            if (!this.newMessage.trim() && !this.attachedFile) return;

            const fd = new FormData();
            if (this.newMessage.trim()) fd.append('body', this.newMessage.trim());
            if (this.attachedFile) fd.append('file', this.attachedFile);
            if (this.replyingTo) fd.append('reply_to_id', this.replyingTo.id);

            const body = this.newMessage;
            this.newMessage = '';
            this.attachedFile = null;
            this.replyingTo = null;
            if (this.$refs.fileInput) this.$refs.fileInput.value = '';
            if (this.$refs.messageInput) {
                this.$refs.messageInput.style.height = 'auto';
            }

            try {
                const res = await fetch(`/chat/conversations/${this.activeConversation.id}/messages`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: fd,
                });
                if (!res.ok) throw new Error('Failed');
                const data = await res.json();
                const msg = data.message;
                this.messages.push(msg);
                this.lastMessageId = msg.id;
                const idx = this.conversations.findIndex(c => c.id === this.activeConversation.id);
                if (idx !== -1) {
                    this.conversations[idx].last_message_preview = msg.body || 'Attachment';
                    this.conversations[idx].last_message_at = 'Just now';
                }
                this.onSuccess();
                this.scrollToBottom(true);
            } catch {
                this.onFailure();
                this.newMessage = body;
            }
        },

        editMessage(msg) {
            this.editingMessage = msg;
            this.editBody = msg.body ?? '';
            this.$nextTick(() => {
                const el = this.$refs.messageInput;
                if (el) {
                    this.autoResize(el);
                    el.focus();
                }
            });
        },

        async saveEdit() {
            if (!this.editingMessage || !this.editBody.trim()) return;
            const id = this.editingMessage.id;
            const body = this.editBody.trim();
            try {
                const res = await fetch(`/chat/messages/${id}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ body }),
                });
                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    alert(err.error ?? 'Could not edit message.');
                    return;
                }
                const idx = this.messages.findIndex(m => m.id === id);
                if (idx !== -1) {
                    this.messages[idx].body = body;
                    this.messages[idx].is_edited = true;
                }
                this.onSuccess();
            } catch {
                this.onFailure();
            } finally {
                this.editingMessage = null;
                this.editBody = '';
            }
        },

        async deleteMessage(msg) {
            if (!confirm('Delete this message?')) return;
            try {
                const res = await fetch(`/chat/messages/${msg.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!res.ok) throw new Error('Failed');
                const idx = this.messages.findIndex(m => m.id === msg.id);
                if (idx !== -1) {
                    this.messages[idx].is_deleted = true;
                    this.messages[idx].body = '';
                    this.messages[idx].attachments = [];
                }
                this.onSuccess();
            } catch {
                this.onFailure();
            }
        },

        replyTo(msg) {
            this.replyingTo = msg;
            this.$nextTick(() => this.$refs.messageInput?.focus());
        },

        cancelReply() {
            this.replyingTo = null;
        },

        // ── Reactions ────────────────────────────────────────────────────────
        async toggleReaction(msgId, emoji) {
            try {
                const res = await fetch(`/chat/messages/${msgId}/reaction`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ emoji }),
                });
                if (!res.ok) throw new Error('Failed');
                const data = await res.json();
                const idx = this.messages.findIndex(m => m.id === msgId);
                if (idx !== -1 && data.reactions) {
                    this.messages[idx].reactions = data.reactions;
                }
                this.onSuccess();
            } catch {
                this.onFailure();
            }
        },

        // ── Pin / Unpin ──────────────────────────────────────────────────────
        async togglePin(msg) {
            try {
                const method = msg.is_pinned ? 'DELETE' : 'POST';
                const res = await fetch(`/chat/messages/${msg.id}/pin`, {
                    method,
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!res.ok) throw new Error('Failed');
                const idx = this.messages.findIndex(m => m.id === msg.id);
                if (idx !== -1) {
                    this.messages[idx].is_pinned = !msg.is_pinned;
                }
                this.onSuccess();
            } catch {
                this.onFailure();
            }
        },

        async openPinnedPanel() {
            if (!this.activeConversation) return;
            this.showPinnedPanel = true;
            try {
                const res = await fetch(`/chat/conversations/${this.activeConversation.id}/pinned`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (res.ok) {
                    const data = await res.json();
                    this.pinnedMessages = data.pinned_messages ?? [];
                }
            } catch {}
        },

        // ── Star / Unstar ────────────────────────────────────────────────────
        async toggleStar(msg) {
            try {
                const res = await fetch(`/chat/messages/${msg.id}/star`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!res.ok) throw new Error('Failed');
                const idx = this.messages.findIndex(m => m.id === msg.id);
                if (idx !== -1) {
                    this.messages[idx].is_starred = !msg.is_starred;
                }
                this.onSuccess();
            } catch {
                this.onFailure();
            }
        },

        async openStarredPanel() {
            this.showStarredPanel = true;
            try {
                const res = await fetch('/chat/starred', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (res.ok) {
                    const data = await res.json();
                    this.starredMessages = data.messages ?? [];
                }
            } catch {}
        },

        // ── Mute ─────────────────────────────────────────────────────────────
        async toggleMute() {
            if (!this.activeConversation) return;
            const newVal = !this.activeConversation.is_muted;
            try {
                const res = await fetch(`/chat/conversations/${this.activeConversation.id}/mute`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ is_muted: newVal }),
                });
                if (!res.ok) throw new Error('Failed');
                this.activeConversation.is_muted = newVal;
                const idx = this.conversations.findIndex(c => c.id === this.activeConversation.id);
                if (idx !== -1) this.conversations[idx].is_muted = newVal;
                this.onSuccess();
            } catch {
                this.onFailure();
            }
        },

        // ── Disappearing messages ────────────────────────────────────────────
        async setDisappearing(minutes) {
            if (!this.activeConversation) return;
            try {
                const res = await fetch(`/chat/conversations/${this.activeConversation.id}/disappearing`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ minutes }),
                });
                if (!res.ok) throw new Error('Failed');
                this.activeConversation.disappear_minutes = minutes;
                this.onSuccess();
            } catch {
                this.onFailure();
            }
        },

        // ── Search ───────────────────────────────────────────────────────────
        async performSearch() {
            if (!this.searchQuery.trim()) {
                this.searchResults = [];
                return;
            }
            this.searchLoading = true;
            try {
                const res = await fetch(`/chat/search?q=${encodeURIComponent(this.searchQuery.trim())}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (res.ok) {
                    const data = await res.json();
                    this.searchResults = data.results ?? [];
                }
            } catch {} finally {
                this.searchLoading = false;
            }
        },

        async goToSearchResult(result) {
            if (result.conversation_id) {
                const conv = this.conversations.find(c => c.id === result.conversation_id);
                if (conv) {
                    await this.openConversation(conv);
                }
            }
        },

        // ── Forward ──────────────────────────────────────────────────────────
        openForwardModal(msg) {
            this.forwardingMessage = msg;
            this.forwardSearch = '';
            this.showForwardModal = true;
        },

        forwardFilteredConversations() {
            if (!this.forwardSearch.trim()) return this.conversations;
            const q = this.forwardSearch.toLowerCase();
            return this.conversations.filter(c => c.display_name.toLowerCase().includes(q));
        },

        async forwardMessage(conversationId) {
            if (!this.forwardingMessage) return;
            try {
                const res = await fetch(`/chat/messages/${this.forwardingMessage.id}/forward`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ conversation_id: conversationId }),
                });
                if (!res.ok) throw new Error('Failed');
                this.showForwardModal = false;
                this.forwardingMessage = null;
                this.onSuccess();
            } catch {
                this.onFailure();
            }
        },

        // ── Image Lightbox ───────────────────────────────────────────────────
        openLightbox(url, name) {
            this.lightboxUrl = url;
            this.lightboxName = name || 'image';
        },

        // ── File handling with size limit ────────────────────────────────────
        handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;
            if (file.size > 10 * 1024 * 1024) {
                alert('File size must not exceed 10 MB.');
                event.target.value = '';
                return;
            }
            this.attachedFile = file;
        },

        // ── Voice messages ───────────────────────────────────────────────────
        async startRecording() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                this.mediaRecorder = new MediaRecorder(stream);
                this.recordingChunks = [];
                this.recordingDuration = 0;
                this.isRecording = true;

                this.mediaRecorder.ondataavailable = (e) => {
                    if (e.data.size > 0) this.recordingChunks.push(e.data);
                };

                this.mediaRecorder.onstop = () => {
                    stream.getTracks().forEach(t => t.stop());
                    if (this.recordingChunks.length > 0 && this.isRecording) {
                        const blob = new Blob(this.recordingChunks, { type: 'audio/webm' });
                        const file = new File([blob], 'voice-message.webm', { type: 'audio/webm' });
                        this.attachedFile = file;
                        this.sendMessage();
                    }
                    this.isRecording = false;
                    clearInterval(this.recordingTimer);
                };

                this.mediaRecorder.start();
                this.recordingTimer = setInterval(() => { this.recordingDuration++; }, 1000);
            } catch (err) {
                alert('Could not access microphone. Please check browser permissions.');
            }
        },

        stopRecording() {
            if (this.mediaRecorder && this.mediaRecorder.state === 'recording') {
                this.mediaRecorder.stop();
            }
        },

        cancelRecording() {
            this.recordingChunks = [];
            this.isRecording = false;
            clearInterval(this.recordingTimer);
            if (this.mediaRecorder && this.mediaRecorder.state === 'recording') {
                this.mediaRecorder.stream.getTracks().forEach(t => t.stop());
                this.mediaRecorder.stop();
            }
        },

        // ── Emoji picker ─────────────────────────────────────────────────────
        filteredEmojiCategories() {
            if (!this.emojiSearch.trim()) return this.emojiCategories;
            // Simple filter: can't search by name with just emojis, so return all when searching
            return this.emojiCategories;
        },

        insertEmoji(emoji) {
            if (this.editingMessage) {
                this.editBody += emoji;
            } else {
                this.newMessage += emoji;
            }
            this.$nextTick(() => this.$refs.messageInput?.focus());
        },

        // ── Polls ────────────────────────────────────────────────────────────
        async createPoll() {
            if (!this.activeConversation) return;
            const options = this.pollOptions.filter(o => o.trim());
            if (!this.pollQuestion.trim() || options.length < 2) return;
            try {
                const res = await fetch(`/chat/conversations/${this.activeConversation.id}/poll`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        question: this.pollQuestion.trim(),
                        options: options,
                        allow_multiple: this.pollAllowMultiple,
                        is_anonymous: this.pollAnonymous,
                    }),
                });
                if (!res.ok) throw new Error('Failed');
                const data = await res.json();
                if (data.message) this.messages.push(data.message);
                this.showCreatePoll = false;
                this.pollQuestion = '';
                this.pollOptions = ['', ''];
                this.pollAllowMultiple = false;
                this.pollAnonymous = false;
                this.scrollToBottom(true);
                this.onSuccess();
            } catch {
                this.onFailure();
            }
        },

        async votePoll(pollId, optionId, allowMultiple) {
            try {
                const res = await fetch(`/chat/polls/${pollId}/vote`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ option_ids: [optionId] }),
                });
                if (!res.ok) throw new Error('Failed');
                const data = await res.json();
                // Update poll in messages
                if (data.poll) {
                    const idx = this.messages.findIndex(m => m.poll && m.poll.id === pollId);
                    if (idx !== -1) {
                        this.messages[idx].poll = data.poll;
                    }
                }
                this.onSuccess();
            } catch {
                this.onFailure();
            }
        },

        async closePoll(pollId) {
            try {
                const res = await fetch(`/chat/polls/${pollId}/close`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!res.ok) throw new Error('Failed');
                const idx = this.messages.findIndex(m => m.poll && m.poll.id === pollId);
                if (idx !== -1) {
                    this.messages[idx].poll.is_closed = true;
                }
                this.onSuccess();
            } catch {
                this.onFailure();
            }
        },

        // ── Tasks ────────────────────────────────────────────────────────────
        async createTask() {
            if (!this.activeConversation || !this.taskTitle.trim()) return;
            try {
                const payload = {
                    title: this.taskTitle.trim(),
                    priority: this.taskPriority,
                };
                if (this.taskDescription.trim()) payload.description = this.taskDescription.trim();
                // Auto-assign in DMs: assign to the other person
                if (!this.activeConversation.is_group && this.activeConversation.participants) {
                    const other = this.activeConversation.participants.find(p => p.id != {{ $user->id }});
                    if (other) payload.assigned_to = other.id;
                } else if (this.taskAssignedTo) {
                    payload.assigned_to = this.taskAssignedTo;
                }
                if (this.taskDueDate) payload.due_date = this.taskDueDate;

                const res = await fetch(`/chat/conversations/${this.activeConversation.id}/tasks`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });
                if (!res.ok) {
                    const errText = await res.text();
                    console.error('Task creation failed:', res.status, errText);
                    alert('Task creation failed: ' + res.status + ' - ' + errText.substring(0, 200));
                    return;
                }
                const data = await res.json();
                if (data.message) this.messages.push(data.message);
                this.showCreateTask = false;
                this.taskTitle = '';
                this.taskDescription = '';
                this.taskAssignedTo = '';
                this.taskPriority = 'medium';
                this.taskDueDate = '';
                this.scrollToBottom(true);
                this.onSuccess();
            } catch {
                this.onFailure();
            }
        },

        async updateTaskStatus(taskId, status) {
            try {
                const res = await fetch(`/chat/tasks/${taskId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ status }),
                });
                if (!res.ok) throw new Error('Failed');
                // Update in messages
                const idx = this.messages.findIndex(m => m.task && m.task.id === taskId);
                if (idx !== -1) {
                    this.messages[idx].task.status = status;
                }
                // Update in tasks panel
                const tIdx = this.tasksList.findIndex(t => t.id === taskId);
                if (tIdx !== -1) {
                    this.tasksList[tIdx].status = status;
                }
                this.onSuccess();
            } catch {
                this.onFailure();
            }
        },

        async openTasksPanel() {
            if (!this.activeConversation) return;
            this.showTasksPanel = true;
            try {
                const res = await fetch(`/chat/conversations/${this.activeConversation.id}/tasks`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (res.ok) {
                    const data = await res.json();
                    this.tasksList = data.tasks ?? [];
                }
            } catch {}
        },

        // ── Group Settings ───────────────────────────────────────────────────
        async saveGroupSettings() {
            if (!this.activeConversation) return;
            try {
                const res = await fetch(`/chat/conversations/${this.activeConversation.id}/settings`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        name: this.groupSettingsName.trim() || undefined,
                        description: this.groupSettingsDesc.trim() || undefined,
                    }),
                });
                if (!res.ok) throw new Error('Failed');
                if (this.groupSettingsName.trim()) {
                    this.activeConversation.display_name = this.groupSettingsName.trim();
                    const idx = this.conversations.findIndex(c => c.id === this.activeConversation.id);
                    if (idx !== -1) this.conversations[idx].display_name = this.groupSettingsName.trim();
                }
                this.showGroupSettings = false;
                this.onSuccess();
            } catch {
                this.onFailure();
            }
        },

        async generateInviteLink() {
            if (!this.activeConversation) return;
            try {
                const res = await fetch(`/chat/conversations/${this.activeConversation.id}/invite`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!res.ok) throw new Error('Failed');
                const data = await res.json();
                if (data.link) {
                    await navigator.clipboard.writeText(data.link).catch(() => {});
                    alert('Invite link copied to clipboard:\n' + data.link);
                }
                this.onSuccess();
            } catch {
                this.onFailure();
                alert('Could not generate invite link.');
            }
        },

        // ── Create conversations ─────────────────────────────────────────────
        async createDirect(userId) {
            this.showNewDirectModal = false;
            try {
                const res = await fetch('/chat/conversations', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ type: 'direct', target_user_id: userId }),
                });
                if (!res.ok) throw new Error('Failed');
                const data = await res.json();
                await this.loadConversations(false);
                const conv = this.conversations.find(c => c.id === data.conversation_id);
                if (conv) await this.openConversation(conv);
                this.onSuccess();
            } catch {
                this.onFailure();
            }
        },

        async createGroup() {
            if (!this.groupName.trim() || this.selectedUserIds.length === 0) return;
            try {
                const res = await fetch('/chat/conversations', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        type: 'group',
                        name: this.groupName.trim(),
                        user_ids: this.selectedUserIds,
                    }),
                });
                if (!res.ok) throw new Error('Failed');
                const data = await res.json();
                this.showNewGroupModal = false;
                this.groupName = '';
                this.selectedUserIds = [];
                this.userSearch = '';
                await this.loadConversations(false);
                const conv = this.conversations.find(c => c.id === data.conversation_id);
                if (conv) await this.openConversation(conv);
                this.onSuccess();
            } catch {
                this.onFailure();
            }
        },
    };
}
</script>
@endpush
