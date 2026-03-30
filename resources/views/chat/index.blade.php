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

            {{-- Header with tabs --}}
            <div class="border-b border-bankos-border bg-white flex-shrink-0">
                <div class="flex items-center justify-between px-4 py-2">
                    <h2 class="text-base font-semibold text-bankos-text">Chat</h2>
                    <div class="flex items-center gap-1">
                        {{-- User status indicator --}}
                        <button @click="showStatusModal = true" title="Set Status"
                            class="relative p-1.5 rounded-lg text-bankos-text-sec hover:bg-gray-100 hover:text-bankos-primary transition-colors">
                            <template x-if="isDnd">
                                <span class="text-sm">&#128308;</span>
                            </template>
                            <template x-if="!isDnd && currentUserStatus && currentUserStatus.emoji">
                                <span class="text-sm" x-text="currentUserStatus.emoji"></span>
                            </template>
                            <template x-if="!isDnd && (!currentUserStatus || !currentUserStatus.emoji)">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </template>
                        </button>
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
                {{-- Tab bar: Chats | Channels | Mentions --}}
                <div class="flex border-t border-bankos-border">
                    <button @click="leftPanelTab = 'chats'"
                        :class="leftPanelTab === 'chats' ? 'text-bankos-primary border-b-2 border-bankos-primary bg-bankos-primary/5' : 'text-bankos-text-sec hover:text-bankos-text hover:bg-gray-50'"
                        class="flex-1 text-xs font-medium py-2 text-center transition-colors">Chats</button>
                    <button @click="leftPanelTab = 'channels'; loadChannels()"
                        :class="leftPanelTab === 'channels' ? 'text-bankos-primary border-b-2 border-bankos-primary bg-bankos-primary/5' : 'text-bankos-text-sec hover:text-bankos-text hover:bg-gray-50'"
                        class="flex-1 text-xs font-medium py-2 text-center transition-colors">Channels</button>
                    <button @click="leftPanelTab = 'mentions'; loadMentions()"
                        :class="leftPanelTab === 'mentions' ? 'text-bankos-primary border-b-2 border-bankos-primary bg-bankos-primary/5' : 'text-bankos-text-sec hover:text-bankos-text hover:bg-gray-50'"
                        class="flex-1 text-xs font-medium py-2 text-center transition-colors">Mentions</button>
                </div>
            </div>

            {{-- ═══ CHATS TAB ═══ --}}
            <div x-show="leftPanelTab === 'chats'" class="flex-1 flex flex-col min-h-0 overflow-hidden">
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

            {{-- ═══ CHANNELS TAB ═══ --}}
            <div x-show="leftPanelTab === 'channels'" class="flex-1 flex flex-col min-h-0 overflow-hidden">
                <div class="px-3 py-2 border-b border-bankos-border flex items-center gap-2">
                    <button @click="showNewChannelModal = true; userSearch = ''; filterUsers()"
                        class="flex-1 px-3 py-1.5 text-xs font-medium text-bankos-primary bg-bankos-primary/5 rounded-lg hover:bg-bankos-primary/10 transition-colors text-center">
                        + New Channel
                    </button>
                    <button @click="loadBrowseChannels(); showBrowseChannels = true"
                        class="flex-1 px-3 py-1.5 text-xs font-medium text-bankos-text-sec bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors text-center">
                        Browse
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto">
                    <template x-if="channels.length === 0">
                        <div class="flex flex-col items-center justify-center py-12 px-4 text-center text-bankos-muted">
                            <svg class="w-10 h-10 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                            </svg>
                            <p class="text-sm">No channels yet</p>
                            <p class="text-xs mt-1">Create or browse channels to get started</p>
                        </div>
                    </template>
                    <template x-for="ch in channels" :key="ch.id">
                        <div @click="openConversation(ch)"
                            :class="activeConversation && activeConversation.id === ch.id
                                ? 'bg-bankos-primary/5 border-l-2 border-l-bankos-primary'
                                : 'border-l-2 border-l-transparent hover:bg-gray-50'"
                            class="flex items-center gap-3 px-3 py-3 cursor-pointer transition-colors">
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1">
                                    <span class="text-sm font-medium text-bankos-text truncate" x-text="'#' + ch.display_name"></span>
                                    <template x-if="ch.is_private">
                                        <svg class="w-3 h-3 text-bankos-muted flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </template>
                                </div>
                                <p class="text-xs text-bankos-text-sec truncate mt-0.5" x-text="ch.topic || ch.last_message_preview || 'No messages yet'"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- ═══ MENTIONS TAB ═══ --}}
            <div x-show="leftPanelTab === 'mentions'" class="flex-1 flex flex-col min-h-0 overflow-hidden">
                <div class="flex-1 overflow-y-auto">
                    <div x-show="loadingMentions" class="flex justify-center py-6">
                        <svg class="animate-spin w-5 h-5 text-bankos-primary" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </div>
                    <template x-if="mentionsList.length === 0 && !loadingMentions">
                        <div class="flex flex-col items-center justify-center py-12 px-4 text-center text-bankos-muted">
                            <svg class="w-10 h-10 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                            <p class="text-sm">No mentions</p>
                            <p class="text-xs mt-1">When someone @mentions you, it will appear here</p>
                        </div>
                    </template>
                    <template x-for="mention in mentionsList" :key="mention.id">
                        <div @click="goToMention(mention)"
                            class="px-3 py-3 hover:bg-gray-50 cursor-pointer transition-colors border-b border-bankos-border">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-semibold text-bankos-primary" x-text="mention.conversation_name"></span>
                                <span class="text-[10px] text-bankos-muted" x-text="mention.created_at"></span>
                            </div>
                            <p class="text-xs text-bankos-text-sec" x-text="mention.sender_name"></p>
                            <p class="text-sm text-bankos-text mt-0.5 line-clamp-2" x-text="mention.body"></p>
                        </div>
                    </template>
                </div>
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
                <div class="flex-1 flex min-h-0 overflow-hidden">
                <div class="flex-1 flex flex-col min-h-0 overflow-hidden"
                    @dragenter.prevent="isDraggingFile = true"
                    @dragover.prevent="isDraggingFile = true"
                    @dragleave.prevent="isDraggingFile = false"
                    @drop.prevent="handleFileDrop($event)">

                    {{-- Drag & Drop overlay --}}
                    <div x-show="isDraggingFile" x-transition
                        class="absolute inset-0 z-40 bg-bankos-primary/10 border-2 border-dashed border-bankos-primary rounded-lg flex items-center justify-center pointer-events-none">
                        <div class="text-center">
                            <svg class="w-12 h-12 text-bankos-primary mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-sm font-medium text-bankos-primary">Drop file to upload</p>
                        </div>
                    </div>

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
                            {{-- Audio Call --}}
                            <button @click="startCall('audio')" title="Audio Call"
                                class="p-1.5 rounded-lg text-bankos-text-sec hover:bg-gray-100 hover:text-green-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </button>
                            {{-- Video Call --}}
                            <button @click="startCall('video')" title="Video Call"
                                class="p-1.5 rounded-lg text-bankos-text-sec hover:bg-gray-100 hover:text-green-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </button>
                            {{-- Canvas Docs --}}
                            <button @click="openCanvasPanel()" title="Docs"
                                class="p-1.5 rounded-lg text-bankos-text-sec hover:bg-gray-100 hover:text-bankos-primary transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </button>
                            {{-- Workflows --}}
                            <button @click="openWorkflowPanel()" title="Workflows"
                                class="p-1.5 rounded-lg text-bankos-text-sec hover:bg-gray-100 hover:text-bankos-primary transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </button>
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
                                    {{-- Notification preferences --}}
                                    <div class="relative" x-data="{notifySub: false}">
                                        <button @click="notifySub = !notifySub"
                                            class="w-full flex items-center justify-between gap-2.5 px-4 py-2 text-sm text-bankos-text hover:bg-gray-50 transition-colors">
                                            <div class="flex items-center gap-2.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                                </svg>
                                                <span>Notifications</span>
                                            </div>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </button>
                                        <div x-show="notifySub" class="border-t border-bankos-border bg-gray-50">
                                            <template x-for="opt in [{label:'All messages',val:'all'},{label:'Mentions only',val:'mentions'},{label:'None',val:'none'}]" :key="opt.val">
                                                <button @click="setNotifyLevel(opt.val); headerMenu = false; notifySub = false"
                                                    :class="activeConversation.notify_level == opt.val ? 'bg-bankos-primary/10 text-bankos-primary font-medium' : 'text-bankos-text'"
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

                    {{-- Incoming call banner --}}
                    <div x-show="incomingCall" x-transition
                        class="flex items-center justify-between px-4 py-3 bg-green-500 text-white border-b border-green-600 flex-shrink-0 animate-pulse">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <span class="text-sm font-medium" x-text="(incomingCall?.caller_name || 'Someone') + ' is calling...'"></span>
                            <span class="text-xs opacity-80" x-text="incomingCall?.type === 'video' ? '(Video)' : '(Audio)'"></span>
                        </div>
                        <div class="flex gap-2">
                            <button @click="joinCall()"
                                class="bg-white text-green-600 px-3 py-1 rounded-full text-sm font-semibold hover:bg-green-50 transition-colors">Accept</button>
                            <button @click="declineCall()"
                                class="bg-red-500 text-white px-3 py-1 rounded-full text-sm font-semibold hover:bg-red-600 transition-colors">Decline</button>
                        </div>
                    </div>

                    {{-- Bookmarks bar --}}
                    <div x-show="bookmarks.length > 0" class="flex items-center gap-1.5 px-4 py-1.5 border-b border-bankos-border bg-gray-50 flex-shrink-0 overflow-x-auto">
                        <svg class="w-3.5 h-3.5 text-bankos-muted flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                        <template x-for="bm in bookmarks" :key="bm.id">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-white rounded border border-bankos-border text-[11px] text-bankos-text hover:bg-gray-100 cursor-pointer flex-shrink-0 group">
                                <a :href="bm.url || '#'" :title="bm.title" target="_blank" class="truncate max-w-[120px]" x-text="bm.title" @click.stop></a>
                                <button @click.stop="deleteBookmark(bm.id)" class="text-bankos-muted hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </span>
                        </template>
                        <button @click="showAddBookmark = true" title="Add Bookmark"
                            class="p-0.5 rounded text-bankos-muted hover:text-bankos-primary hover:bg-gray-100 transition-colors flex-shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Scheduled messages banner --}}
                    <div x-show="scheduledMessages.length > 0"
                        class="flex items-center justify-between px-4 py-1.5 bg-blue-50 border-b border-blue-200 flex-shrink-0">
                        <button @click="showScheduledList = !showScheduledList"
                            class="text-xs text-blue-700 font-medium flex items-center gap-1 hover:underline">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-text="scheduledMessages.length + ' scheduled message' + (scheduledMessages.length !== 1 ? 's' : '')"></span>
                        </button>
                    </div>
                    {{-- Scheduled messages list (expandable) --}}
                    <div x-show="showScheduledList && scheduledMessages.length > 0" x-transition
                        class="border-b border-blue-200 bg-blue-50/50 px-4 py-2 space-y-1.5 flex-shrink-0 max-h-40 overflow-y-auto">
                        <template x-for="sm in scheduledMessages" :key="sm.id">
                            <div class="flex items-center justify-between gap-2 p-2 bg-white rounded-lg border border-blue-200">
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs text-bankos-text truncate" x-text="sm.body"></p>
                                    <p class="text-[10px] text-blue-600" x-text="'Scheduled: ' + sm.scheduled_at"></p>
                                </div>
                                <button @click="cancelScheduledMessage(sm.id)" title="Cancel"
                                    class="p-1 rounded text-bankos-muted hover:text-red-500 transition-colors flex-shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
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
                                                        <div>
                                                            <p class="text-sm whitespace-pre-wrap break-words" x-html="formatMessage(msg.body)"></p>
                                                            {{-- Link unfurl preview --}}
                                                            <template x-if="unfurledLinks[msg.id]">
                                                                <div :class="msg.sender_id === currentUserId ? 'bg-white/10 border-white/20' : 'bg-bankos-bg border-bankos-border'"
                                                                    class="mt-2 p-2.5 rounded-lg border flex gap-2.5 max-w-[320px]">
                                                                    <template x-if="unfurledLinks[msg.id].image">
                                                                        <img :src="unfurledLinks[msg.id].image" class="w-14 h-14 rounded object-cover flex-shrink-0" alt="">
                                                                    </template>
                                                                    <div class="min-w-0">
                                                                        <p :class="msg.sender_id === currentUserId ? 'text-white' : 'text-bankos-text'"
                                                                            class="text-xs font-semibold truncate" x-text="unfurledLinks[msg.id].title"></p>
                                                                        <p :class="msg.sender_id === currentUserId ? 'text-white/70' : 'text-bankos-text-sec'"
                                                                            class="text-[11px] line-clamp-2 mt-0.5" x-text="unfurledLinks[msg.id].description"></p>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                        </div>
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

                                                {{-- Thread reply count --}}
                                                <template x-if="msg.thread_reply_count && msg.thread_reply_count > 0">
                                                    <button @click="openThread(msg)"
                                                        :class="msg.sender_id === currentUserId ? 'text-blue-200 hover:text-white' : 'text-bankos-primary hover:text-bankos-primary-dark'"
                                                        class="text-[11px] font-medium mt-0.5 ml-1 flex items-center gap-1 transition-colors">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                                        </svg>
                                                        <span x-text="msg.thread_reply_count + ' repl' + (msg.thread_reply_count === 1 ? 'y' : 'ies')"></span>
                                                    </button>
                                                </template>

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
                                                        {{-- Reply in Thread --}}
                                                        <button @click="openThread(msg)" title="Reply in Thread"
                                                            class="p-1 rounded text-bankos-muted hover:text-bankos-primary hover:bg-gray-100 transition-colors">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
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
                                                {{-- Custom Emoji tab --}}
                                                <div class="mb-2 border-t border-bankos-border pt-2 mt-2">
                                                    <div class="flex items-center justify-between mb-1 px-1">
                                                        <p class="text-[10px] text-bankos-muted font-medium uppercase">Custom</p>
                                                        <button @click="showAddCustomEmoji = true; showEmoji = false"
                                                            class="text-[10px] text-bankos-primary hover:underline">+ Add</button>
                                                    </div>
                                                    <div class="flex flex-wrap gap-0.5">
                                                        <template x-for="ce in customEmojis" :key="ce.id">
                                                            <button @click="insertEmoji(':' + ce.shortcode + ':'); showEmoji = false"
                                                                :title="':' + ce.shortcode + ':'"
                                                                class="w-7 h-7 flex items-center justify-center rounded hover:bg-gray-100 transition-colors">
                                                                <img :src="ce.image_url" :alt="ce.shortcode" class="w-5 h-5 object-contain">
                                                            </button>
                                                        </template>
                                                        <template x-if="customEmojis.length === 0">
                                                            <p class="text-[10px] text-bankos-muted px-1">No custom emoji yet</p>
                                                        </template>
                                                    </div>
                                                </div>
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

                            {{-- Textarea wrapper with @mention dropdown --}}
                            <div class="flex-1 relative">
                                {{-- @Mention dropdown --}}
                                <div x-show="showMentionDropdown" x-transition
                                    class="absolute bottom-full left-0 mb-1 w-64 max-h-48 overflow-y-auto bg-white rounded-xl shadow-lg border border-bankos-border z-50">
                                    <template x-for="candidate in mentionCandidates" :key="candidate.id || candidate.handle">
                                        <button @click="insertMention(candidate)"
                                            class="w-full flex items-center gap-2 px-3 py-2 text-sm text-bankos-text hover:bg-bankos-primary/5 transition-colors text-left">
                                            <div class="w-6 h-6 rounded-full bg-bankos-primary/10 text-bankos-primary text-[10px] font-semibold flex items-center justify-center flex-shrink-0"
                                                x-text="candidate.initials || '@'"></div>
                                            <span class="truncate" x-text="candidate.name || candidate.handle"></span>
                                        </button>
                                    </template>
                                    <template x-if="mentionCandidates.length === 0">
                                        <p class="px-3 py-2 text-xs text-bankos-muted">No matches</p>
                                    </template>
                                </div>
                                <textarea
                                    x-model="editingMessage ? editBody : newMessage"
                                    @keydown.enter.prevent="handleEnter($event)"
                                    @input="autoResize($event.target); sendTypingIndicator(); checkMentionTrigger($event)"
                                    @keydown.escape="showMentionDropdown = false"
                                    x-ref="messageInput"
                                    rows="1"
                                    :placeholder="editingMessage ? 'Edit your message...' : 'Type a message... (*bold* _italic_ ~strike~ `code`)'"
                                    class="w-full resize-none px-3 py-2 text-sm rounded-xl border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary max-h-32 overflow-y-auto leading-relaxed"
                                ></textarea>
                            </div>

                            {{-- Schedule button --}}
                            <template x-if="!editingMessage">
                                <div class="relative" x-data="{showSched: false}">
                                    <button @click="showSched = !showSched" title="Schedule Message"
                                        class="p-2 rounded-xl text-bankos-text-sec hover:bg-gray-100 hover:text-bankos-primary transition-colors flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </button>
                                    <div x-show="showSched" @click.outside="showSched = false" x-transition
                                        class="absolute bottom-full right-0 mb-2 w-64 bg-white rounded-xl shadow-lg border border-bankos-border z-50 p-3">
                                        <p class="text-xs font-medium text-bankos-text mb-2">Schedule Message</p>
                                        <input x-model="scheduleDateTime" type="datetime-local"
                                            class="w-full px-2 py-1.5 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-1 focus:ring-bankos-primary/30 mb-2">
                                        <button @click="scheduleMessage(); showSched = false"
                                            :disabled="!scheduleDateTime || (!newMessage.trim() && !attachedFile)"
                                            class="w-full px-3 py-1.5 text-xs font-medium rounded-lg bg-bankos-primary text-white hover:bg-bankos-primary-dark disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                                            Schedule
                                        </button>
                                    </div>
                                </div>
                            </template>

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

                        <p class="text-[10px] text-bankos-muted mt-1.5">Enter to send &middot; Shift+Enter for new line &middot; *bold* _italic_ ~strike~ `code` &middot; @mention users</p>
                    </div>
                </div>{{-- end left column of conversation --}}

                {{-- ═══ THREAD PANEL (right side) ═══ --}}
                <div x-show="showThreadPanel" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="translate-x-0 opacity-100" x-transition:leave-end="translate-x-full opacity-0"
                    class="w-80 flex-shrink-0 flex flex-col border-l border-bankos-border bg-white overflow-hidden">
                    {{-- Thread header --}}
                    <div class="flex items-center justify-between px-4 py-3 border-b border-bankos-border">
                        <h3 class="text-sm font-semibold text-bankos-text">Thread</h3>
                        <button @click="showThreadPanel = false" class="p-1 rounded-lg text-bankos-muted hover:bg-gray-100 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Parent message --}}
                    <div x-show="threadParentMessage" class="px-4 py-3 border-b border-bankos-border bg-bankos-bg">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-6 h-6 rounded-full bg-gray-200 text-gray-600 text-[10px] font-semibold flex items-center justify-center"
                                x-text="threadParentMessage?.sender_initials"></div>
                            <span class="text-xs font-semibold text-bankos-text" x-text="threadParentMessage?.sender_name"></span>
                            <span class="text-[10px] text-bankos-muted" x-text="threadParentMessage?.created_at"></span>
                        </div>
                        <p class="text-sm text-bankos-text whitespace-pre-wrap break-words" x-html="formatMessage(threadParentMessage?.body || '')"></p>
                    </div>

                    {{-- Thread replies --}}
                    <div class="flex-1 overflow-y-auto px-4 py-3 space-y-2" x-ref="threadContainer">
                        <div x-show="loadingThread" class="flex justify-center py-6">
                            <svg class="animate-spin w-5 h-5 text-bankos-primary" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                        </div>
                        <template x-if="threadReplies.length === 0 && !loadingThread">
                            <p class="text-xs text-bankos-muted text-center py-4">No replies yet</p>
                        </template>
                        <template x-for="reply in threadReplies" :key="reply.id">
                            <div class="flex gap-2">
                                <div class="w-6 h-6 rounded-full bg-gray-200 text-gray-600 text-[10px] font-semibold flex items-center justify-center flex-shrink-0 mt-0.5"
                                    x-text="reply.sender_initials"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1 mb-0.5">
                                        <span class="text-xs font-semibold text-bankos-text" x-text="reply.sender_name"></span>
                                        <span class="text-[10px] text-bankos-muted" x-text="reply.created_at"></span>
                                    </div>
                                    <p class="text-sm text-bankos-text whitespace-pre-wrap break-words" x-html="formatMessage(reply.body || '')"></p>
                                    <template x-if="reply.attachments && reply.attachments.length > 0">
                                        <div class="mt-1">
                                            <template x-for="att in reply.attachments" :key="att.id">
                                                <a :href="att.url" class="text-xs text-bankos-primary hover:underline flex items-center gap-1" download>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                                    <span x-text="att.file_name"></span>
                                                </a>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Thread reply input --}}
                    <div class="border-t border-bankos-border px-3 py-2 flex items-end gap-2">
                        <input type="file" x-ref="threadFileInput" class="hidden" @change="threadReplyFile = $event.target.files[0]">
                        <button @click="$refs.threadFileInput.click()" class="p-1.5 rounded text-bankos-text-sec hover:bg-gray-100 transition-colors flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                        </button>
                        <textarea x-model="threadReplyBody"
                            @keydown.enter.prevent="if (!$event.shiftKey) sendThreadReply()"
                            rows="1" placeholder="Reply in thread..."
                            class="flex-1 resize-none px-2.5 py-1.5 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-1 focus:ring-bankos-primary/30 max-h-20 overflow-y-auto"></textarea>
                        <button @click="sendThreadReply()"
                            :disabled="!threadReplyBody.trim() && !threadReplyFile"
                            class="p-1.5 rounded-lg bg-bankos-primary text-white hover:bg-bankos-primary-dark disabled:opacity-40 transition-colors flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </button>
                    </div>
                </div>{{-- end thread panel --}}

                </div>{{-- end flex row for conversation + thread --}}
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
         MODAL — New Channel
    ══════════════════════════════════════════════════ --}}
    <div x-show="showNewChannelModal" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
        @keydown.escape.window="showNewChannelModal = false">
        <div @click.outside="showNewChannelModal = false" x-transition
            class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-bankos-border">
                <h3 class="text-sm font-semibold text-bankos-text">New Channel</h3>
                <button @click="showNewChannelModal = false"
                    class="p-1 rounded-lg text-bankos-muted hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-4 py-3 space-y-3">
                <input x-model="channelName" type="text" placeholder="Channel name..."
                    class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary">
                <input x-model="channelTopic" type="text" placeholder="Topic (optional)..."
                    class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary">
                <label class="flex items-center gap-2 text-sm text-bankos-text">
                    <input type="checkbox" x-model="channelIsPrivate" class="w-4 h-4 rounded text-bankos-primary border-bankos-border focus:ring-bankos-primary/30">
                    Private channel
                </label>
                <input x-model="userSearch" @input="filterUsers()" type="text" placeholder="Add members..."
                    class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary">
                <p class="text-xs text-bankos-text-sec" x-show="channelUserIds.length > 0"
                    x-text="channelUserIds.length + ' member(s) selected'"></p>
                <div class="max-h-36 overflow-y-auto space-y-0.5">
                    <template x-for="u in filteredUsers" :key="u.id">
                        <label class="flex items-center gap-3 px-2 py-1.5 rounded-xl hover:bg-bankos-primary/5 transition-colors cursor-pointer">
                            <input type="checkbox" :value="u.id" x-model="channelUserIds"
                                class="w-4 h-4 rounded text-bankos-primary border-bankos-border focus:ring-bankos-primary/30">
                            <span class="text-sm text-bankos-text truncate" x-text="u.name"></span>
                        </label>
                    </template>
                </div>
            </div>
            <div class="px-4 pb-4 flex justify-end gap-2">
                <button @click="showNewChannelModal = false"
                    class="px-4 py-2 text-sm rounded-lg border border-bankos-border text-bankos-text hover:bg-gray-50 transition-colors">Cancel</button>
                <button @click="createChannel()"
                    :disabled="!channelName.trim()"
                    class="px-4 py-2 text-sm rounded-lg bg-bankos-primary text-white hover:bg-bankos-primary-dark disabled:opacity-40 disabled:cursor-not-allowed transition-colors">Create Channel</button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         MODAL — Browse Channels
    ══════════════════════════════════════════════════ --}}
    <div x-show="showBrowseChannels" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
        @keydown.escape.window="showBrowseChannels = false">
        <div @click.outside="showBrowseChannels = false" x-transition
            class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-bankos-border">
                <h3 class="text-sm font-semibold text-bankos-text">Browse Channels</h3>
                <button @click="showBrowseChannels = false"
                    class="p-1 rounded-lg text-bankos-muted hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="max-h-80 overflow-y-auto">
                <template x-if="browseChannels.length === 0">
                    <p class="text-sm text-bankos-muted text-center py-8">No public channels to join</p>
                </template>
                <template x-for="ch in browseChannels" :key="ch.id">
                    <div class="flex items-center justify-between px-5 py-3 border-b border-bankos-border last:border-b-0 hover:bg-gray-50">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-bankos-text" x-text="'#' + ch.name"></p>
                            <p class="text-xs text-bankos-text-sec truncate" x-text="ch.topic || 'No topic set'"></p>
                            <p class="text-[10px] text-bankos-muted" x-text="(ch.member_count || 0) + ' members'"></p>
                        </div>
                        <button @click="joinChannel(ch.id)"
                            class="px-3 py-1 text-xs font-medium rounded-lg bg-bankos-primary text-white hover:bg-bankos-primary-dark transition-colors flex-shrink-0 ml-3">
                            Join
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         MODAL — User Status
    ══════════════════════════════════════════════════ --}}
    <div x-show="showStatusModal" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
        @keydown.escape.window="showStatusModal = false">
        <div @click.outside="showStatusModal = false" x-transition
            class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-bankos-border">
                <h3 class="text-sm font-semibold text-bankos-text">Set Status</h3>
                <button @click="showStatusModal = false"
                    class="p-1 rounded-lg text-bankos-muted hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-5 py-4 space-y-3">
                {{-- Quick emoji select --}}
                <div>
                    <label class="text-xs font-medium text-bankos-text-sec mb-1 block">Emoji</label>
                    <div class="flex gap-1.5 flex-wrap">
                        <template x-for="em in ['\u{1F4AC}','\u{1F3E0}','\u{1F912}','\u{1F3DD}\u{FE0F}','\u{1F4BB}','\u{1F680}','\u{1F3AF}','\u{2615}']" :key="em">
                            <button @click="userStatusEmoji = em"
                                :class="userStatusEmoji === em ? 'ring-2 ring-bankos-primary bg-bankos-primary/10' : 'hover:bg-gray-100'"
                                class="w-8 h-8 flex items-center justify-center text-lg rounded-lg transition-all" x-text="em"></button>
                        </template>
                    </div>
                </div>
                <input x-model="userStatusText" type="text" placeholder="What's your status?"
                    class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary">
                <div>
                    <label class="text-xs font-medium text-bankos-text-sec mb-1 block">Clear after</label>
                    <select x-model="userStatusUntil"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary">
                        <option value="">Don't clear</option>
                        <option value="30m">30 minutes</option>
                        <option value="1h">1 hour</option>
                        <option value="4h">4 hours</option>
                        <option value="today">Today</option>
                    </select>
                </div>

                {{-- DND toggle --}}
                <div class="flex items-center justify-between pt-2 border-t border-bankos-border">
                    <div>
                        <p class="text-sm font-medium text-bankos-text">Do Not Disturb</p>
                        <p class="text-xs text-bankos-text-sec">Pause notifications</p>
                    </div>
                    <button @click="toggleDnd()"
                        :class="isDnd ? 'bg-red-500' : 'bg-gray-300'"
                        class="relative w-10 h-5 rounded-full transition-colors flex-shrink-0">
                        <span :class="isDnd ? 'translate-x-5' : 'translate-x-0.5'"
                            class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform"></span>
                    </button>
                </div>
            </div>
            <div class="px-5 pb-4 flex justify-between gap-2">
                <button @click="clearUserStatus()"
                    class="px-4 py-2 text-sm rounded-lg border border-bankos-border text-bankos-text hover:bg-gray-50 transition-colors">Clear Status</button>
                <button @click="setUserStatus()"
                    class="px-4 py-2 text-sm rounded-lg bg-bankos-primary text-white hover:bg-bankos-primary-dark transition-colors">Save</button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         MODAL — Add Bookmark
    ══════════════════════════════════════════════════ --}}
    <div x-show="showAddBookmark" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
        @keydown.escape.window="showAddBookmark = false">
        <div @click.outside="showAddBookmark = false" x-transition
            class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-bankos-border">
                <h3 class="text-sm font-semibold text-bankos-text">Add Bookmark</h3>
                <button @click="showAddBookmark = false"
                    class="p-1 rounded-lg text-bankos-muted hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-5 py-4 space-y-3">
                <input x-model="bookmarkTitle" type="text" placeholder="Bookmark title..."
                    class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary">
                <input x-model="bookmarkUrl" type="url" placeholder="URL (optional)..."
                    class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary">
            </div>
            <div class="px-5 pb-4 flex justify-end gap-2">
                <button @click="showAddBookmark = false"
                    class="px-4 py-2 text-sm rounded-lg border border-bankos-border text-bankos-text hover:bg-gray-50 transition-colors">Cancel</button>
                <button @click="addBookmark()"
                    :disabled="!bookmarkTitle.trim()"
                    class="px-4 py-2 text-sm rounded-lg bg-bankos-primary text-white hover:bg-bankos-primary-dark disabled:opacity-40 disabled:cursor-not-allowed transition-colors">Add</button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         MODAL — Add Custom Emoji
    ══════════════════════════════════════════════════ --}}
    <div x-show="showAddCustomEmoji" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
        @keydown.escape.window="showAddCustomEmoji = false">
        <div @click.outside="showAddCustomEmoji = false" x-transition
            class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-bankos-border">
                <h3 class="text-sm font-semibold text-bankos-text">Add Custom Emoji</h3>
                <button @click="showAddCustomEmoji = false"
                    class="p-1 rounded-lg text-bankos-muted hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-5 py-4 space-y-3">
                <input x-model="customEmojiShortcode" type="text" placeholder=":shortcode: (e.g. party_parrot)"
                    class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary">
                <div>
                    <label class="text-xs font-medium text-bankos-text-sec mb-1 block">Emoji Image</label>
                    <input type="file" x-ref="customEmojiInput" accept="image/*"
                        @change="customEmojiFile = $event.target.files[0]"
                        class="w-full text-sm text-bankos-text file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border file:border-bankos-border file:bg-bankos-bg file:text-sm file:text-bankos-text hover:file:bg-gray-100">
                </div>
            </div>
            <div class="px-5 pb-4 flex justify-end gap-2">
                <button @click="showAddCustomEmoji = false"
                    class="px-4 py-2 text-sm rounded-lg border border-bankos-border text-bankos-text hover:bg-gray-50 transition-colors">Cancel</button>
                <button @click="uploadCustomEmoji()"
                    :disabled="!customEmojiShortcode.trim() || !customEmojiFile"
                    class="px-4 py-2 text-sm rounded-lg bg-bankos-primary text-white hover:bg-bankos-primary-dark disabled:opacity-40 disabled:cursor-not-allowed transition-colors">Upload</button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         IN-CALL OVERLAY (LiveKit)
    ══════════════════════════════════════════════════ --}}
    <div x-show="inCall" x-transition.opacity class="fixed inset-0 z-[70] bg-gray-900 flex flex-col">
        {{-- Top bar --}}
        <div class="flex items-center justify-between px-6 py-4 text-white">
            <div class="flex items-center gap-3">
                <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></div>
                <span class="text-sm font-medium" x-text="'Call with ' + (activeConversation?.display_name || 'Unknown')"></span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-white/10" x-text="callType === 'video' ? 'Video' : 'Audio'"></span>
            </div>
            <span x-text="callDuration" class="font-mono text-sm text-white/80"></span>
        </div>

        {{-- Center: Video/Audio grid --}}
        <div class="flex-1 flex items-center justify-center gap-4 px-8 relative">
            {{-- Remote participants --}}
            <div id="remote-video-container" class="flex flex-wrap gap-4 justify-center"></div>
            {{-- Audio-only state --}}
            <div x-show="callType === 'audio'" class="text-center">
                <div class="w-24 h-24 rounded-full bg-white/10 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-12 h-12 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                    </svg>
                </div>
                <p class="text-white/60 text-sm">Audio call in progress</p>
            </div>
            {{-- Local video (small, bottom-right) --}}
            <div id="local-video-container" x-show="callType === 'video' && callVideoOn"
                class="absolute bottom-4 right-8 w-48 h-36 rounded-lg overflow-hidden border-2 border-white/30 shadow-xl bg-gray-800"></div>
        </div>

        {{-- Bottom: Controls --}}
        <div class="flex items-center justify-center gap-4 py-6 bg-gray-800/80">
            {{-- Mute mic --}}
            <button @click="toggleCallMute()" :class="callMuted ? 'bg-red-500' : 'bg-gray-600 hover:bg-gray-500'"
                class="w-12 h-12 rounded-full flex items-center justify-center text-white transition-colors" title="Toggle Microphone">
                <template x-if="!callMuted">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                    </svg>
                </template>
                <template x-if="callMuted">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                    </svg>
                </template>
            </button>
            {{-- Toggle camera (video calls only) --}}
            <button x-show="callType === 'video'" @click="toggleCallVideo()"
                :class="!callVideoOn ? 'bg-red-500' : 'bg-gray-600 hover:bg-gray-500'"
                class="w-12 h-12 rounded-full flex items-center justify-center text-white transition-colors" title="Toggle Camera">
                <template x-if="callVideoOn">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </template>
                <template x-if="!callVideoOn">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728A9 9 0 015.636 5.636"/>
                    </svg>
                </template>
            </button>
            {{-- Screen share --}}
            <button @click="toggleScreenShare()" :class="callScreenSharing ? 'bg-blue-500' : 'bg-gray-600 hover:bg-gray-500'"
                class="w-12 h-12 rounded-full flex items-center justify-center text-white transition-colors" title="Screen Share">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </button>
            {{-- End call --}}
            <button @click="endCurrentCall()" class="w-14 h-14 rounded-full bg-red-600 hover:bg-red-700 flex items-center justify-center text-white transition-colors" title="End Call">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-3.28a1 1 0 00-.684-.948l-4.493-1.498a1 1 0 00-1.21.502l-1.13 2.257a11.042 11.042 0 01-5.516-5.517l2.257-1.128a1 1 0 00.502-1.21L9.228 3.683A1 1 0 008.279 3H5z"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         CANVAS / DOCS — Slide-over Panel
    ══════════════════════════════════════════════════ --}}
    <div x-show="showCanvasPanel" x-transition.opacity
        class="fixed inset-0 z-50 flex justify-end bg-black/30"
        @keydown.escape.window="showCanvasPanel = false">
        <div @click="showCanvasPanel = false" class="flex-1"></div>
        <div x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
            class="w-96 bg-white shadow-xl flex flex-col overflow-hidden" @click.stop>
            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-bankos-border">
                <h3 class="text-sm font-semibold text-bankos-text">Docs</h3>
                <div class="flex items-center gap-2">
                    <button @click="createCanvas()" class="px-3 py-1 text-xs font-medium rounded-lg bg-bankos-primary text-white hover:bg-bankos-primary-dark transition-colors">
                        + New Doc
                    </button>
                    <button @click="showCanvasPanel = false" class="p-1 rounded-lg text-bankos-muted hover:bg-gray-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            {{-- Canvas list --}}
            <div class="flex-1 overflow-y-auto">
                <div x-show="canvasLoading" class="flex justify-center py-8">
                    <svg class="animate-spin w-5 h-5 text-bankos-primary" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </div>
                <template x-if="canvasList.length === 0 && !canvasLoading">
                    <div class="flex flex-col items-center justify-center py-12 px-4 text-center text-bankos-muted">
                        <svg class="w-10 h-10 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-sm">No docs yet</p>
                        <p class="text-xs mt-1">Create a doc to collaborate with your team</p>
                    </div>
                </template>
                <template x-for="doc in canvasList" :key="doc.id">
                    <div @click="openCanvasEditor(doc)"
                        class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors border-b border-bankos-border group">
                        <div class="w-9 h-9 rounded-lg bg-bankos-primary/10 text-bankos-primary flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-bankos-text truncate" x-text="doc.title || 'Untitled'"></p>
                            <p class="text-[10px] text-bankos-muted" x-text="'by ' + (doc.created_by_name || 'Unknown') + (doc.updated_at ? ' &middot; ' + doc.updated_at : '')"></p>
                        </div>
                        <button @click.stop="deleteCanvas(doc.id)" class="p-1 rounded text-bankos-muted hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all" title="Delete">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         CANVAS EDITOR — Modal
    ══════════════════════════════════════════════════ --}}
    <div x-show="showCanvasEditor" x-transition.opacity
        class="fixed inset-0 z-[55] flex items-center justify-center p-4 bg-black/40"
        @keydown.escape.window="showCanvasEditor = false">
        <div @click.outside="saveCanvasContent(); showCanvasEditor = false" x-transition
            class="bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[85vh] flex flex-col overflow-hidden">
            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-3 border-b border-bankos-border">
                <input x-model="canvasTitle" type="text" placeholder="Document title..."
                    @blur="saveCanvasContent()"
                    class="text-base font-semibold text-bankos-text bg-transparent border-none focus:outline-none focus:ring-0 flex-1 mr-4 placeholder-bankos-muted">
                <div class="flex items-center gap-2 flex-shrink-0">
                    <span x-show="canvasSaving" class="text-[10px] text-bankos-muted">Saving...</span>
                    <span x-show="canvasSaved && !canvasSaving" class="text-[10px] text-green-600">Saved</span>
                    <button @click="saveCanvasContent()" class="px-3 py-1 text-xs font-medium rounded-lg bg-bankos-primary text-white hover:bg-bankos-primary-dark transition-colors">
                        Save
                    </button>
                    <button @click="saveCanvasContent(); showCanvasEditor = false" class="p-1 rounded-lg text-bankos-muted hover:bg-gray-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            {{-- Toolbar --}}
            <div class="flex items-center gap-1 px-4 py-2 border-b border-bankos-border bg-gray-50">
                <button @click="canvasExec('bold')" class="p-1.5 rounded hover:bg-gray-200 text-sm font-bold text-bankos-text" title="Bold">B</button>
                <button @click="canvasExec('italic')" class="p-1.5 rounded hover:bg-gray-200 text-sm italic text-bankos-text" title="Italic">I</button>
                <button @click="canvasExec('underline')" class="p-1.5 rounded hover:bg-gray-200 text-sm underline text-bankos-text" title="Underline">U</button>
                <button @click="canvasExec('strikeThrough')" class="p-1.5 rounded hover:bg-gray-200 text-sm line-through text-bankos-text" title="Strikethrough">S</button>
                <div class="w-px h-5 bg-bankos-border mx-1"></div>
                <button @click="canvasExec('formatBlock', '<h2>')" class="p-1.5 rounded hover:bg-gray-200 text-xs font-bold text-bankos-text" title="Heading">H2</button>
                <button @click="canvasExec('formatBlock', '<h3>')" class="p-1.5 rounded hover:bg-gray-200 text-xs font-bold text-bankos-text" title="Subheading">H3</button>
                <div class="w-px h-5 bg-bankos-border mx-1"></div>
                <button @click="canvasExec('insertUnorderedList')" class="p-1.5 rounded hover:bg-gray-200 text-bankos-text" title="Bullet List">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <button @click="canvasExec('insertOrderedList')" class="p-1.5 rounded hover:bg-gray-200 text-xs font-medium text-bankos-text" title="Numbered List">1.</button>
                <div class="w-px h-5 bg-bankos-border mx-1"></div>
                <button @click="canvasExec('formatBlock', '<pre>')" class="p-1.5 rounded hover:bg-gray-200 text-xs font-mono text-bankos-text" title="Code Block">&lt;/&gt;</button>
            </div>
            {{-- Editor --}}
            <div contenteditable="true" id="canvas-editor"
                @blur="saveCanvasContent()"
                class="flex-1 overflow-y-auto p-5 min-h-[300px] prose prose-sm max-w-none focus:outline-none text-bankos-text"
                style="line-height: 1.7;"></div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         WORKFLOW BUILDER — Slide-over Panel
    ══════════════════════════════════════════════════ --}}
    <div x-show="showWorkflowPanel" x-transition.opacity
        class="fixed inset-0 z-50 flex justify-end bg-black/30"
        @keydown.escape.window="showWorkflowPanel = false">
        <div @click="showWorkflowPanel = false" class="flex-1"></div>
        <div x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
            class="w-[420px] bg-white shadow-xl flex flex-col overflow-hidden" @click.stop>
            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-bankos-border">
                <h3 class="text-sm font-semibold text-bankos-text">Workflows</h3>
                <div class="flex items-center gap-2">
                    <button @click="openWorkflowEditor(null)" class="px-3 py-1 text-xs font-medium rounded-lg bg-bankos-primary text-white hover:bg-bankos-primary-dark transition-colors">
                        + New Workflow
                    </button>
                    <button @click="showWorkflowPanel = false" class="p-1 rounded-lg text-bankos-muted hover:bg-gray-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            {{-- Workflow list --}}
            <div class="flex-1 overflow-y-auto">
                <div x-show="workflowsLoading" class="flex justify-center py-8">
                    <svg class="animate-spin w-5 h-5 text-bankos-primary" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </div>
                <template x-if="workflowsList.length === 0 && !workflowsLoading">
                    <div class="flex flex-col items-center justify-center py-12 px-4 text-center text-bankos-muted">
                        <svg class="w-10 h-10 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <p class="text-sm">No workflows yet</p>
                        <p class="text-xs mt-1">Automate repetitive tasks with workflows</p>
                    </div>
                </template>
                <template x-for="wf in workflowsList" :key="wf.id">
                    <div class="px-4 py-3 border-b border-bankos-border hover:bg-gray-50 transition-colors group">
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                <svg class="w-4 h-4 flex-shrink-0" :class="wf.is_active ? 'text-green-500' : 'text-bankos-muted'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                <span class="text-sm font-medium text-bankos-text truncate" x-text="wf.name"></span>
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                {{-- Toggle active --}}
                                <button @click="toggleWorkflow(wf)"
                                    :class="wf.is_active ? 'bg-green-500' : 'bg-gray-300'"
                                    class="relative w-8 h-4 rounded-full transition-colors flex-shrink-0" title="Toggle active">
                                    <span :class="wf.is_active ? 'translate-x-4' : 'translate-x-0.5'"
                                        class="absolute top-0.5 w-3 h-3 bg-white rounded-full shadow transition-transform"></span>
                                </button>
                                {{-- Run --}}
                                <button @click="runWorkflow(wf.id)" class="p-1 rounded text-bankos-muted hover:text-green-600 hover:bg-green-50 transition-colors" title="Run now">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </button>
                                {{-- Edit --}}
                                <button @click="openWorkflowEditor(wf)" class="p-1 rounded text-bankos-muted hover:text-bankos-primary hover:bg-gray-100 transition-colors" title="Edit">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                {{-- Delete --}}
                                <button @click="deleteWorkflow(wf.id)" class="p-1 rounded text-bankos-muted hover:text-red-500 hover:bg-red-50 opacity-0 group-hover:opacity-100 transition-all" title="Delete">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 text-[10px] text-bankos-muted">
                            <span x-text="'Trigger: ' + (wf.trigger?.type || 'Unknown').replace('_', ' ')"></span>
                            <span x-text="(wf.steps?.length || 0) + ' step(s)'"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         WORKFLOW EDITOR — Modal
    ══════════════════════════════════════════════════ --}}
    <div x-show="showWorkflowEditor" x-transition.opacity
        class="fixed inset-0 z-[55] flex items-center justify-center p-4 bg-black/40"
        @keydown.escape.window="showWorkflowEditor = false">
        <div @click.outside="showWorkflowEditor = false" x-transition
            class="bg-white rounded-2xl shadow-xl w-full max-w-xl max-h-[85vh] flex flex-col overflow-hidden">
            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-bankos-border">
                <h3 class="text-sm font-semibold text-bankos-text" x-text="wfEditId ? 'Edit Workflow' : 'New Workflow'"></h3>
                <button @click="showWorkflowEditor = false" class="p-1 rounded-lg text-bankos-muted hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            {{-- Body --}}
            <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
                {{-- Name --}}
                <div>
                    <label class="text-xs font-medium text-bankos-text-sec mb-1 block">Name</label>
                    <input x-model="wfEditName" type="text" placeholder="Workflow name..."
                        class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary">
                </div>
                {{-- Description --}}
                <div>
                    <label class="text-xs font-medium text-bankos-text-sec mb-1 block">Description (optional)</label>
                    <textarea x-model="wfEditDescription" rows="2" placeholder="What does this workflow do?"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary resize-none"></textarea>
                </div>
                {{-- Trigger --}}
                <div>
                    <label class="text-xs font-medium text-bankos-text-sec mb-1 block">Trigger</label>
                    <select x-model="wfEditTriggerType"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary">
                        <option value="keyword">Message contains keyword</option>
                        <option value="new_member">New member joins</option>
                        <option value="scheduled">Scheduled (cron)</option>
                        <option value="user_message">Message from specific user</option>
                    </select>
                    {{-- Trigger config --}}
                    <div class="mt-2">
                        <template x-if="wfEditTriggerType === 'keyword'">
                            <input x-model="wfEditTriggerConfig" type="text" placeholder="Keyword to match..."
                                class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30">
                        </template>
                        <template x-if="wfEditTriggerType === 'scheduled'">
                            <input x-model="wfEditTriggerConfig" type="text" placeholder="Cron expression (e.g. 0 9 * * 1-5)"
                                class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30">
                        </template>
                        <template x-if="wfEditTriggerType === 'user_message'">
                            <select x-model="wfEditTriggerConfig"
                                class="w-full px-3 py-2 text-sm rounded-lg border border-bankos-border bg-bankos-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary/30">
                                <option value="">Select user...</option>
                                <template x-for="u in allUsers" :key="u.id">
                                    <option :value="u.id" x-text="u.name"></option>
                                </template>
                            </select>
                        </template>
                    </div>
                </div>
                {{-- Steps --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-medium text-bankos-text-sec">Steps</label>
                        <button @click="wfEditSteps.push({action: 'send_message', config: ''})"
                            class="text-xs text-bankos-primary hover:underline">+ Add Step</button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(step, idx) in wfEditSteps" :key="idx">
                            <div class="flex items-start gap-2 p-3 bg-bankos-bg rounded-lg border border-bankos-border">
                                <span class="text-[10px] font-bold text-bankos-muted mt-2" x-text="(idx + 1) + '.'"></span>
                                <div class="flex-1 space-y-2">
                                    <select x-model="step.action"
                                        class="w-full px-2 py-1.5 text-xs rounded-lg border border-bankos-border bg-white focus:outline-none focus:ring-1 focus:ring-bankos-primary/30">
                                        <option value="send_message">Send message</option>
                                        <option value="create_task">Create task</option>
                                        <option value="add_reaction">Add reaction</option>
                                        <option value="send_notification">Send notification</option>
                                        <option value="webhook">Webhook</option>
                                    </select>
                                    {{-- Config based on action --}}
                                    <template x-if="step.action === 'send_message'">
                                        <textarea x-model="step.config" rows="2" placeholder="Message body..."
                                            class="w-full px-2 py-1.5 text-xs rounded-lg border border-bankos-border bg-white focus:outline-none focus:ring-1 focus:ring-bankos-primary/30 resize-none"></textarea>
                                    </template>
                                    <template x-if="step.action === 'create_task'">
                                        <input x-model="step.config" type="text" placeholder="Task title..."
                                            class="w-full px-2 py-1.5 text-xs rounded-lg border border-bankos-border bg-white focus:outline-none focus:ring-1 focus:ring-bankos-primary/30">
                                    </template>
                                    <template x-if="step.action === 'add_reaction'">
                                        <input x-model="step.config" type="text" placeholder="Emoji (e.g. thumbs up)"
                                            class="w-full px-2 py-1.5 text-xs rounded-lg border border-bankos-border bg-white focus:outline-none focus:ring-1 focus:ring-bankos-primary/30">
                                    </template>
                                    <template x-if="step.action === 'send_notification'">
                                        <input x-model="step.config" type="text" placeholder="Notification message..."
                                            class="w-full px-2 py-1.5 text-xs rounded-lg border border-bankos-border bg-white focus:outline-none focus:ring-1 focus:ring-bankos-primary/30">
                                    </template>
                                    <template x-if="step.action === 'webhook'">
                                        <input x-model="step.config" type="url" placeholder="Webhook URL..."
                                            class="w-full px-2 py-1.5 text-xs rounded-lg border border-bankos-border bg-white focus:outline-none focus:ring-1 focus:ring-bankos-primary/30">
                                    </template>
                                </div>
                                <div class="flex flex-col gap-0.5 flex-shrink-0">
                                    <button @click="if (idx > 0) { let t = wfEditSteps[idx]; wfEditSteps[idx] = wfEditSteps[idx-1]; wfEditSteps[idx-1] = t; wfEditSteps = [...wfEditSteps]; }"
                                        :disabled="idx === 0" class="p-0.5 rounded text-bankos-muted hover:text-bankos-text disabled:opacity-30" title="Move up">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                    </button>
                                    <button @click="if (idx < wfEditSteps.length - 1) { let t = wfEditSteps[idx]; wfEditSteps[idx] = wfEditSteps[idx+1]; wfEditSteps[idx+1] = t; wfEditSteps = [...wfEditSteps]; }"
                                        :disabled="idx === wfEditSteps.length - 1" class="p-0.5 rounded text-bankos-muted hover:text-bankos-text disabled:opacity-30" title="Move down">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <button @click="wfEditSteps.splice(idx, 1)" class="p-0.5 rounded text-bankos-muted hover:text-red-500" title="Remove">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <template x-if="wfEditSteps.length === 0">
                            <p class="text-xs text-bankos-muted text-center py-3">No steps added yet. Click "+ Add Step" above.</p>
                        </template>
                    </div>
                </div>
            </div>
            {{-- Footer --}}
            <div class="px-5 py-4 border-t border-bankos-border flex justify-end gap-2">
                <button @click="showWorkflowEditor = false"
                    class="px-4 py-2 text-sm rounded-lg border border-bankos-border text-bankos-text hover:bg-gray-50 transition-colors">Cancel</button>
                <button @click="saveWorkflow()"
                    :disabled="!wfEditName.trim()"
                    class="px-4 py-2 text-sm rounded-lg bg-bankos-primary text-white hover:bg-bankos-primary-dark disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                    <span x-text="wfEditId ? 'Update' : 'Create'"></span>
                </button>
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

        // ── Channels / Tabs state ─────────────────────────────────────────────
        leftPanelTab: 'chats',    // 'chats' | 'channels' | 'mentions'
        channels: [],
        browseChannels: [],
        showBrowseChannels: false,
        showNewChannelModal: false,
        channelName: '',
        channelTopic: '',
        channelIsPrivate: false,
        channelUserIds: [],

        // ── Threads state ─────────────────────────────────────────────────────
        showThreadPanel: false,
        threadParentMessage: null,
        threadReplies: [],
        threadReplyBody: '',
        threadReplyFile: null,
        loadingThread: false,

        // ── Mentions state ───────────���────────────────────────────────────────
        mentionsList: [],
        loadingMentions: false,
        showMentionDropdown: false,
        mentionQuery: '',
        mentionCandidates: [],
        mentionCaretPos: 0,

        // ── User Status / DND state ───────────────────────────────────────────
        showStatusModal: false,
        userStatusEmoji: '',
        userStatusText: '',
        userStatusUntil: '',
        currentUserStatus: null,
        isDnd: false,
        dndUntil: '',

        // ── Scheduled Messages state ────────────��─────────────────────────────
        showSchedulePicker: false,
        scheduleDateTime: '',
        scheduledMessages: [],
        showScheduledList: false,

        // ── Bookmarks state ───────────���───────────────────────────────────────
        bookmarks: [],
        showAddBookmark: false,
        bookmarkTitle: '',
        bookmarkUrl: '',

        // ── Link Unfurling state ──────��───────────────────────────────────────
        unfurledLinks: {},

        // ── Custom Emoji state ────────────────���───────────────────────────────
        customEmojis: [],
        showAddCustomEmoji: false,
        customEmojiShortcode: '',
        customEmojiFile: null,

        // ── Reminders state ───────────────────────────────────────────────────
        reminders: [],
        showReminders: false,
        reminderNote: '',
        reminderAt: '',
        reminderConvId: null,
        reminderMsgId: null,

        // ── User Groups state ─────────────────────────────────────────────────
        userGroups: [],

        // ── Drag & Drop state ───────────────────��─────────────────────────────
        isDraggingFile: false,

        // ── Call (LiveKit) state ─────────────────────────────────────────────
        inCall: false,
        callId: null,
        callType: 'audio',
        callMuted: false,
        callVideoOn: false,
        callScreenSharing: false,
        callDuration: '00:00',
        callTimer: null,
        incomingCall: null,
        livekitRoom: null,

        // ── Canvas / Docs state ──────────────────────────────────────────────
        showCanvasPanel: false,
        showCanvasEditor: false,
        canvasList: [],
        canvasLoading: false,
        canvasActiveId: null,
        canvasTitle: '',
        canvasSaving: false,
        canvasSaved: false,

        // ── Workflow Builder state ────────────────────────────────────────────
        showWorkflowPanel: false,
        showWorkflowEditor: false,
        workflowsList: [],
        workflowsLoading: false,
        wfEditId: null,
        wfEditName: '',
        wfEditDescription: '',
        wfEditTriggerType: 'keyword',
        wfEditTriggerConfig: '',
        wfEditSteps: [],

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

            // Poll for incoming calls every 5s
            setInterval(() => {
                if (this.pollingActive) this.checkIncomingCall();
            }, 5000);

            // Load supplementary data
            this.loadCustomEmojis();
            this.loadUserGroups();
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
            let convos = this.conversations.filter(c => c.type !== 'channel');
            if (!this.convSearch.trim()) return convos;
            const q = this.convSearch.toLowerCase();
            return convos.filter(c =>
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

            // Code blocks with language (```lang\ncode\n```) — dark background with copy button
            text = text.replace(/```(\w*)\n([\s\S]*?)```/g, function(match, lang, code) {
                const langLabel = lang ? '<span class="text-[9px] text-gray-400 uppercase">' + lang + '</span>' : '';
                const codeId = 'code-' + Math.random().toString(36).substr(2, 9);
                return '<div class="relative group/code my-1">' +
                    '<div class="flex items-center justify-between px-2 py-1 bg-gray-800 rounded-t-lg">' +
                    langLabel +
                    '<button onclick="navigator.clipboard.writeText(document.getElementById(\'' + codeId + '\').textContent)" class="text-[9px] text-gray-400 hover:text-white transition-colors opacity-0 group-hover/code:opacity-100">Copy</button>' +
                    '</div>' +
                    '<pre id="' + codeId + '" class="bg-gray-900 text-gray-100 rounded-b-lg px-3 py-2 text-xs font-mono overflow-x-auto whitespace-pre">' + code.trim() + '</pre></div>';
            });
            // Simple code blocks (``` ... ```)
            text = text.replace(/```([\s\S]*?)```/g, function(match, code) {
                const codeId = 'code-' + Math.random().toString(36).substr(2, 9);
                return '<div class="relative group/code my-1">' +
                    '<div class="flex items-center justify-end px-2 py-1 bg-gray-800 rounded-t-lg">' +
                    '<button onclick="navigator.clipboard.writeText(document.getElementById(\'' + codeId + '\').textContent)" class="text-[9px] text-gray-400 hover:text-white transition-colors opacity-0 group-hover/code:opacity-100">Copy</button>' +
                    '</div>' +
                    '<pre id="' + codeId + '" class="bg-gray-900 text-gray-100 rounded-b-lg px-3 py-2 text-xs font-mono overflow-x-auto whitespace-pre">' + code.trim() + '</pre></div>';
            });
            // Inline code
            text = text.replace(/`([^`]+)`/g, '<code class="bg-black/10 rounded px-1 py-0.5 text-xs font-mono">$1</code>');
            // Bold
            text = text.replace(/\*([^*]+)\*/g, '<strong>$1</strong>');
            // Italic
            text = text.replace(/_([^_]+)_/g, '<em>$1</em>');
            // Strikethrough
            text = text.replace(/~([^~]+)~/g, '<del>$1</del>');
            // @mentions — highlight with blue pill
            text = text.replace(/@(\w+(?:\.\w+)*)/g, '<span class="inline-block bg-blue-100 text-blue-700 text-xs font-medium px-1.5 py-0 rounded-full">@$1</span>');
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
            this.loadBookmarks();
            this.loadScheduledMessages();
            this.showThreadPanel = false;

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
                // Auto-unfurl links in loaded messages
                this.messages.forEach(m => {
                    if (m.body) {
                        const urlMatch = m.body.match(/(https?:\/\/[^\s]+)/);
                        if (urlMatch) this.unfurlLink(urlMatch[1], m.id);
                    }
                });
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

                // Auto-unfurl links
                if (msg.body) {
                    const urlMatch = msg.body.match(/(https?:\/\/[^\s]+)/);
                    if (urlMatch) {
                        this.unfurlLink(urlMatch[1], msg.id);
                    }
                }
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
        // ── Channels ────────────────────────────────────────────────────────
        async loadChannels() {
            // Channels are conversations that are channels — filter from loaded convos or fetch separately
            this.channels = this.conversations.filter(c => c.type === 'channel');
        },

        async loadBrowseChannels() {
            try {
                const res = await fetch('/chat/channels/browse', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (res.ok) {
                    const data = await res.json();
                    this.browseChannels = data.channels ?? [];
                }
            } catch {}
        },

        async createChannel() {
            if (!this.channelName.trim()) return;
            try {
                const res = await fetch('/chat/channels', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        name: this.channelName.trim(),
                        topic: this.channelTopic.trim() || undefined,
                        is_private: this.channelIsPrivate,
                        user_ids: this.channelUserIds,
                    }),
                });
                if (!res.ok) throw new Error('Failed');
                this.showNewChannelModal = false;
                this.channelName = '';
                this.channelTopic = '';
                this.channelIsPrivate = false;
                this.channelUserIds = [];
                await this.loadConversations(false);
                this.loadChannels();
                this.onSuccess();
            } catch {
                this.onFailure();
            }
        },

        async joinChannel(conversationId) {
            try {
                const res = await fetch(`/chat/channels/${conversationId}/join`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!res.ok) throw new Error('Failed');
                this.showBrowseChannels = false;
                await this.loadConversations(false);
                this.loadChannels();
                this.onSuccess();
            } catch {
                this.onFailure();
            }
        },

        // ── Threads ────────────────────────────────────────────────────────
        async openThread(msg) {
            this.showThreadPanel = true;
            this.threadParentMessage = msg;
            this.threadReplies = [];
            this.threadReplyBody = '';
            this.threadReplyFile = null;
            this.loadingThread = true;
            try {
                const res = await fetch(`/chat/messages/${msg.id}/thread`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (res.ok) {
                    const data = await res.json();
                    this.threadReplies = data.replies ?? data.messages ?? [];
                }
            } catch {} finally {
                this.loadingThread = false;
            }
        },

        async sendThreadReply() {
            if (!this.threadParentMessage) return;
            if (!this.threadReplyBody.trim() && !this.threadReplyFile) return;

            const fd = new FormData();
            if (this.threadReplyBody.trim()) fd.append('body', this.threadReplyBody.trim());
            if (this.threadReplyFile) fd.append('file', this.threadReplyFile);

            this.threadReplyBody = '';
            this.threadReplyFile = null;
            if (this.$refs.threadFileInput) this.$refs.threadFileInput.value = '';

            try {
                const res = await fetch(`/chat/messages/${this.threadParentMessage.id}/thread`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: fd,
                });
                if (!res.ok) throw new Error('Failed');
                const data = await res.json();
                if (data.message) {
                    this.threadReplies.push(data.message);
                }
                // Update thread count on parent message
                const idx = this.messages.findIndex(m => m.id === this.threadParentMessage.id);
                if (idx !== -1) {
                    this.messages[idx].thread_reply_count = (this.messages[idx].thread_reply_count || 0) + 1;
                }
                this.$nextTick(() => {
                    if (this.$refs.threadContainer) {
                        this.$refs.threadContainer.scrollTop = this.$refs.threadContainer.scrollHeight;
                    }
                });
                this.onSuccess();
            } catch {
                this.onFailure();
            }
        },

        // ── Mentions ──────────────────────────────────────────────────────
        async loadMentions() {
            this.loadingMentions = true;
            try {
                const res = await fetch('/chat/mentions', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (res.ok) {
                    const data = await res.json();
                    this.mentionsList = data.mentions ?? [];
                }
            } catch {} finally {
                this.loadingMentions = false;
            }
        },

        async goToMention(mention) {
            if (mention.conversation_id) {
                const conv = this.conversations.find(c => c.id === mention.conversation_id);
                if (conv) {
                    this.leftPanelTab = 'chats';
                    await this.openConversation(conv);
                }
            }
        },

        checkMentionTrigger(event) {
            const textarea = event.target;
            const text = textarea.value;
            const cursorPos = textarea.selectionStart;

            // Find the @ before cursor
            const textBefore = text.substring(0, cursorPos);
            const atMatch = textBefore.match(/@(\w*)$/);

            if (atMatch) {
                this.mentionQuery = atMatch[1].toLowerCase();
                this.mentionCaretPos = cursorPos;

                // Filter users and user groups
                let candidates = [];
                const users = (this.activeConversation?.participants || this.allUsers).map(u => ({
                    id: u.id,
                    name: u.name,
                    initials: this.getInitials(u.name),
                    type: 'user',
                }));
                const groups = this.userGroups.map(g => ({
                    id: 'group-' + g.id,
                    name: g.name,
                    handle: g.handle,
                    initials: '@',
                    type: 'group',
                }));
                candidates = [...users, ...groups];

                if (this.mentionQuery) {
                    candidates = candidates.filter(c =>
                        (c.name || '').toLowerCase().includes(this.mentionQuery) ||
                        (c.handle || '').toLowerCase().includes(this.mentionQuery)
                    );
                }
                this.mentionCandidates = candidates.slice(0, 8);
                this.showMentionDropdown = candidates.length > 0;
            } else {
                this.showMentionDropdown = false;
            }
        },

        insertMention(candidate) {
            const textarea = this.$refs.messageInput;
            const text = this.editingMessage ? this.editBody : this.newMessage;
            const beforeCursor = text.substring(0, this.mentionCaretPos);
            const afterCursor = text.substring(this.mentionCaretPos);

            // Replace the @query with @name
            const atIdx = beforeCursor.lastIndexOf('@');
            const name = candidate.handle || candidate.name.replace(/\s+/g, '.');
            const newText = beforeCursor.substring(0, atIdx) + '@' + name + ' ' + afterCursor;

            if (this.editingMessage) {
                this.editBody = newText;
            } else {
                this.newMessage = newText;
            }
            this.showMentionDropdown = false;
            this.$nextTick(() => textarea?.focus());
        },

        // ── User Status & DND ─────────────────────────────────────────────
        async setUserStatus() {
            const untilMap = {
                '30m': new Date(Date.now() + 30 * 60000).toISOString(),
                '1h': new Date(Date.now() + 60 * 60000).toISOString(),
                '4h': new Date(Date.now() + 240 * 60000).toISOString(),
                'today': new Date(new Date().setHours(23, 59, 59, 999)).toISOString(),
            };
            try {
                const res = await fetch('/chat/status', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        emoji: this.userStatusEmoji,
                        text: this.userStatusText,
                        until: untilMap[this.userStatusUntil] || undefined,
                    }),
                });
                if (res.ok) {
                    this.currentUserStatus = {
                        emoji: this.userStatusEmoji,
                        text: this.userStatusText,
                    };
                }
                this.showStatusModal = false;
                this.onSuccess();
            } catch {
                this.onFailure();
            }
        },

        async clearUserStatus() {
            try {
                await fetch('/chat/status', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                this.currentUserStatus = null;
                this.userStatusEmoji = '';
                this.userStatusText = '';
                this.userStatusUntil = '';
                this.showStatusModal = false;
            } catch {}
        },

        async toggleDnd() {
            this.isDnd = !this.isDnd;
            if (this.isDnd) {
                try {
                    await fetch('/chat/dnd', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken(),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            until: new Date(Date.now() + 60 * 60000).toISOString(),
                        }),
                    });
                } catch {}
            } else {
                try {
                    await fetch('/chat/status', {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken(),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                } catch {}
            }
        },

        // ── Scheduled Messages ────────────────────────────────────────────
        async scheduleMessage() {
            if (!this.activeConversation || !this.scheduleDateTime) return;
            if (!this.newMessage.trim() && !this.attachedFile) return;

            try {
                const res = await fetch(`/chat/conversations/${this.activeConversation.id}/schedule`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        body: this.newMessage.trim(),
                        scheduled_at: this.scheduleDateTime,
                    }),
                });
                if (!res.ok) throw new Error('Failed');
                const data = await res.json();
                if (data.message) {
                    this.scheduledMessages.push(data.message);
                }
                this.newMessage = '';
                this.scheduleDateTime = '';
                this.onSuccess();
            } catch {
                this.onFailure();
            }
        },

        async loadScheduledMessages() {
            if (!this.activeConversation) return;
            try {
                const res = await fetch(`/chat/conversations/${this.activeConversation.id}/scheduled`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (res.ok) {
                    const data = await res.json();
                    this.scheduledMessages = data.messages ?? [];
                }
            } catch {}
        },

        async cancelScheduledMessage(messageId) {
            try {
                const res = await fetch(`/chat/messages/${messageId}/schedule`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (res.ok) {
                    this.scheduledMessages = this.scheduledMessages.filter(m => m.id !== messageId);
                }
            } catch {}
        },

        // ── Bookmarks ─────────────────────────────────────────────────────
        async loadBookmarks() {
            if (!this.activeConversation) return;
            try {
                const res = await fetch(`/chat/conversations/${this.activeConversation.id}/bookmarks`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (res.ok) {
                    const data = await res.json();
                    this.bookmarks = data.bookmarks ?? [];
                }
            } catch {}
        },

        async addBookmark() {
            if (!this.activeConversation || !this.bookmarkTitle.trim()) return;
            try {
                const res = await fetch(`/chat/conversations/${this.activeConversation.id}/bookmarks`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        title: this.bookmarkTitle.trim(),
                        url: this.bookmarkUrl.trim() || undefined,
                    }),
                });
                if (!res.ok) throw new Error('Failed');
                const data = await res.json();
                if (data.bookmark) {
                    this.bookmarks.push(data.bookmark);
                }
                this.showAddBookmark = false;
                this.bookmarkTitle = '';
                this.bookmarkUrl = '';
                this.onSuccess();
            } catch {
                this.onFailure();
            }
        },

        async deleteBookmark(bookmarkId) {
            try {
                await fetch(`/chat/bookmarks/${bookmarkId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                this.bookmarks = this.bookmarks.filter(b => b.id !== bookmarkId);
            } catch {}
        },

        // ── Notification Level ────────────────────────────────────────────
        async setNotifyLevel(level) {
            if (!this.activeConversation) return;
            try {
                const res = await fetch(`/chat/conversations/${this.activeConversation.id}/notify-level`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ level }),
                });
                if (res.ok) {
                    this.activeConversation.notify_level = level;
                }
                this.onSuccess();
            } catch {
                this.onFailure();
            }
        },

        // ── Link Unfurling ────────────────────────────────────────────────
        async unfurlLink(url, msgId) {
            if (this.unfurledLinks[msgId]) return;
            try {
                const res = await fetch('/chat/unfurl', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ url }),
                });
                if (res.ok) {
                    const data = await res.json();
                    const unfurl = data.unfurl ?? data;
                    if (unfurl.title || unfurl.description) {
                        this.unfurledLinks[msgId] = unfurl;
                    }
                }
            } catch {}
        },

        // ── Custom Emoji ──────────────────────────────────────────────────
        async loadCustomEmojis() {
            try {
                const res = await fetch('/chat/emoji', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (res.ok) {
                    const data = await res.json();
                    this.customEmojis = data.emojis ?? [];
                }
            } catch {}
        },

        async uploadCustomEmoji() {
            if (!this.customEmojiShortcode.trim() || !this.customEmojiFile) return;
            const fd = new FormData();
            fd.append('shortcode', this.customEmojiShortcode.trim());
            fd.append('image', this.customEmojiFile);
            try {
                const res = await fetch('/chat/emoji', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: fd,
                });
                if (!res.ok) throw new Error('Failed');
                const data = await res.json();
                if (data.emoji) {
                    this.customEmojis.push(data.emoji);
                }
                this.showAddCustomEmoji = false;
                this.customEmojiShortcode = '';
                this.customEmojiFile = null;
                if (this.$refs.customEmojiInput) this.$refs.customEmojiInput.value = '';
                this.onSuccess();
            } catch {
                this.onFailure();
                alert('Could not upload emoji.');
            }
        },

        // ── User Groups ───────────────────────────────────────────────────
        async loadUserGroups() {
            try {
                const res = await fetch('/chat/user-groups', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (res.ok) {
                    const data = await res.json();
                    this.userGroups = data.groups ?? [];
                }
            } catch {}
        },

        // ── Drag & Drop ──────────────────────────────────────────────────
        handleFileDrop(event) {
            this.isDraggingFile = false;
            const files = event.dataTransfer?.files;
            if (files && files.length > 0) {
                const file = files[0];
                if (file.size > 10 * 1024 * 1024) {
                    alert('File size must not exceed 10 MB.');
                    return;
                }
                this.attachedFile = file;
            }
        },

        // ── Reminders ─────────────────────────────────────────────────────
        async loadReminders() {
            try {
                const res = await fetch('/chat/reminders', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (res.ok) {
                    const data = await res.json();
                    this.reminders = data.reminders ?? [];
                }
            } catch {}
        },

        async dismissReminder(reminderId) {
            try {
                await fetch(`/chat/reminders/${reminderId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                this.reminders = this.reminders.filter(r => r.id !== reminderId);
            } catch {}
        },

        // ── Calls (LiveKit) ─────────────────────────────────────────────────
        async startCall(type) {
            if (!this.activeConversation) return;
            try {
                const res = await fetch(`/chat/conversations/${this.activeConversation.id}/call`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken(), 'Accept': 'application/json' },
                    body: JSON.stringify({ type }),
                });
                if (!res.ok) { const e = await res.text(); alert('Failed to start call: ' + e.substring(0, 200)); return; }
                const data = await res.json();
                await this.connectToLiveKit(data.token, data.ws_url, type);
                this.callId = data.call_id;
                this.inCall = true;
                this.callType = type;
                this.startCallTimer();
                this.startCallStatusPoll();
            } catch (e) { console.error('startCall error', e); }
        },

        async joinCall(callId) {
            callId = callId || this.incomingCall?.id;
            if (!callId) return;
            try {
                const res = await fetch(`/chat/calls/${callId}/join`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken(), 'Accept': 'application/json' },
                });
                if (!res.ok) { alert('Failed to join call'); return; }
                const data = await res.json();
                await this.connectToLiveKit(data.token, data.ws_url, data.type || 'audio');
                this.callId = callId;
                this.inCall = true;
                this.callType = data.type || 'audio';
                this.incomingCall = null;
                this.startCallTimer();
                this.startCallStatusPoll();
            } catch (e) { console.error('joinCall error', e); }
        },

        async declineCall() {
            if (!this.incomingCall) return;
            try {
                await fetch(`/chat/calls/${this.incomingCall.id}/decline`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken() },
                });
            } catch {}
            this.incomingCall = null;
        },

        async connectToLiveKit(token, wsUrl, type) {
            if (typeof LivekitClient === 'undefined') {
                console.error('LiveKit SDK not loaded');
                alert('Call feature is not available - LiveKit SDK failed to load.');
                return;
            }
            const room = new LivekitClient.Room({ adaptiveStream: true, dynacast: true });

            room.on(LivekitClient.RoomEvent.TrackSubscribed, (track, publication, participant) => {
                const container = document.getElementById('remote-video-container');
                if (!container) return;
                const el = track.attach();
                el.setAttribute('data-participant', participant.identity);
                el.style.maxWidth = '400px';
                el.style.borderRadius = '8px';
                container.appendChild(el);
            });

            room.on(LivekitClient.RoomEvent.TrackUnsubscribed, (track) => {
                track.detach().forEach(el => el.remove());
            });

            room.on(LivekitClient.RoomEvent.Disconnected, () => {
                this.cleanupCall();
            });

            await room.connect(wsUrl, token);

            if (type === 'video') {
                await room.localParticipant.enableCameraAndMicrophone();
                this.callVideoOn = true;
            } else {
                await room.localParticipant.setMicrophoneEnabled(true);
            }

            // Attach local video
            room.localParticipant.videoTrackPublications.forEach(pub => {
                if (pub.track) {
                    const el = pub.track.attach();
                    el.style.width = '100%';
                    el.style.borderRadius = '8px';
                    const localContainer = document.getElementById('local-video-container');
                    if (localContainer) localContainer.appendChild(el);
                }
            });

            this.livekitRoom = room;
        },

        async toggleCallMute() {
            if (!this.livekitRoom) return;
            this.callMuted = !this.callMuted;
            await this.livekitRoom.localParticipant.setMicrophoneEnabled(!this.callMuted);
        },

        async toggleCallVideo() {
            if (!this.livekitRoom) return;
            this.callVideoOn = !this.callVideoOn;
            await this.livekitRoom.localParticipant.setCameraEnabled(this.callVideoOn);
        },

        async toggleScreenShare() {
            if (!this.livekitRoom) return;
            this.callScreenSharing = !this.callScreenSharing;
            await this.livekitRoom.localParticipant.setScreenShareEnabled(this.callScreenSharing);
        },

        async endCurrentCall() {
            if (this.callId) {
                try {
                    await fetch(`/chat/calls/${this.callId}/end`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': this.csrfToken() },
                    });
                } catch {}
            }
            if (this.livekitRoom) {
                this.livekitRoom.disconnect();
                this.livekitRoom = null;
            }
            this.cleanupCall();
        },

        cleanupCall() {
            document.getElementById('remote-video-container')?.replaceChildren();
            document.getElementById('local-video-container')?.replaceChildren();
            this.inCall = false;
            this.callId = null;
            this.callMuted = false;
            this.callVideoOn = false;
            this.callScreenSharing = false;
            clearInterval(this.callTimer);
            this.callDuration = '00:00';
        },

        startCallStatusPoll() {
            this._callStatusPoll = setInterval(async () => {
                if (!this.callId || !this.inCall) { clearInterval(this._callStatusPoll); return; }
                try {
                    const res = await fetch(`/chat/conversations/${this.activeConversation.id}/active-call`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (!data.call || data.call.status === 'ended' || data.call.status === 'declined') {
                            if (this.livekitRoom) { this.livekitRoom.disconnect(); this.livekitRoom = null; }
                            this.cleanupCall();
                            clearInterval(this._callStatusPoll);
                        }
                    }
                } catch {}
            }, 3000);
        },

        startCallTimer() {
            let seconds = 0;
            clearInterval(this.callTimer);
            this.callTimer = setInterval(() => {
                seconds++;
                const m = Math.floor(seconds / 60).toString().padStart(2, '0');
                const s = (seconds % 60).toString().padStart(2, '0');
                this.callDuration = `${m}:${s}`;
            }, 1000);
        },

        async checkIncomingCall() {
            if (!this.activeConversation || this.inCall) return;
            try {
                const res = await fetch(`/chat/conversations/${this.activeConversation.id}/active-call`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.call && data.call.status === 'ringing' && data.call.initiated_by != this.currentUserId) {
                        this.incomingCall = { id: data.call.id, type: data.call.type, caller_name: data.call.initiated_by_name ?? 'Someone' };
                    } else {
                        this.incomingCall = null;
                    }
                }
            } catch {}
        },

        // ── Canvas / Docs ────────────────────────────────────────────────────
        async openCanvasPanel() {
            this.showCanvasPanel = true;
            await this.loadCanvasList();
        },

        async loadCanvasList() {
            if (!this.activeConversation) return;
            this.canvasLoading = true;
            try {
                const res = await fetch(`/chat/conversations/${this.activeConversation.id}/canvas`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (res.ok) {
                    const data = await res.json();
                    this.canvasList = data.canvases ?? [];
                }
            } catch {}
            this.canvasLoading = false;
        },

        async createCanvas() {
            if (!this.activeConversation) return;
            try {
                const res = await fetch(`/chat/conversations/${this.activeConversation.id}/canvas`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken(), 'Accept': 'application/json' },
                    body: JSON.stringify({ title: 'Untitled Document', content: '' }),
                });
                if (res.ok) {
                    const data = await res.json();
                    await this.loadCanvasList();
                    if (data.canvas) this.openCanvasEditor(data.canvas);
                }
            } catch (e) { console.error('createCanvas error', e); }
        },

        async openCanvasEditor(doc) {
            this.canvasActiveId = doc.id;
            this.canvasTitle = doc.title || 'Untitled';
            this.canvasSaved = false;
            this.showCanvasEditor = true;
            // Load full content
            try {
                const res = await fetch(`/chat/canvas/${doc.id}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (res.ok) {
                    const data = await res.json();
                    const editor = document.getElementById('canvas-editor');
                    if (editor) editor.innerHTML = data.canvas?.content || '';
                    this.canvasTitle = data.canvas?.title || 'Untitled';
                }
            } catch {}
        },

        canvasExec(command, value = null) {
            document.execCommand(command, false, value);
            document.getElementById('canvas-editor')?.focus();
        },

        async saveCanvasContent() {
            if (!this.canvasActiveId) return;
            const editor = document.getElementById('canvas-editor');
            if (!editor) return;
            this.canvasSaving = true;
            this.canvasSaved = false;
            try {
                const res = await fetch(`/chat/canvas/${this.canvasActiveId}`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken(), 'Accept': 'application/json' },
                    body: JSON.stringify({ title: this.canvasTitle, content: editor.innerHTML }),
                });
                if (res.ok) this.canvasSaved = true;
            } catch {}
            this.canvasSaving = false;
        },

        async deleteCanvas(canvasId) {
            if (!confirm('Delete this document?')) return;
            try {
                await fetch(`/chat/canvas/${canvasId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken(), 'X-Requested-With': 'XMLHttpRequest' },
                });
                this.canvasList = this.canvasList.filter(c => c.id !== canvasId);
                if (this.canvasActiveId === canvasId) {
                    this.showCanvasEditor = false;
                    this.canvasActiveId = null;
                }
            } catch {}
        },

        // ── Workflow Builder ─────────────────────────────────────────────────
        async openWorkflowPanel() {
            this.showWorkflowPanel = true;
            await this.loadWorkflows();
        },

        async loadWorkflows() {
            this.workflowsLoading = true;
            try {
                const res = await fetch('/chat/workflows', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (res.ok) {
                    const data = await res.json();
                    this.workflowsList = data.workflows ?? [];
                }
            } catch {}
            this.workflowsLoading = false;
        },

        openWorkflowEditor(wf) {
            if (wf) {
                this.wfEditId = wf.id;
                this.wfEditName = wf.name || '';
                this.wfEditDescription = wf.description || '';
                this.wfEditTriggerType = wf.trigger?.type || 'keyword';
                this.wfEditTriggerConfig = wf.trigger?.config || '';
                this.wfEditSteps = (wf.steps || []).map(s => ({ action: s.action, config: s.config || '' }));
            } else {
                this.wfEditId = null;
                this.wfEditName = '';
                this.wfEditDescription = '';
                this.wfEditTriggerType = 'keyword';
                this.wfEditTriggerConfig = '';
                this.wfEditSteps = [{ action: 'send_message', config: '' }];
            }
            this.showWorkflowEditor = true;
        },

        async saveWorkflow() {
            if (!this.wfEditName.trim()) return;
            const payload = {
                name: this.wfEditName,
                description: this.wfEditDescription,
                trigger: { type: this.wfEditTriggerType, config: this.wfEditTriggerConfig },
                steps: this.wfEditSteps.map(s => ({ action: s.action, config: s.config })),
                conversation_id: this.activeConversation?.id || null,
            };
            try {
                let res;
                if (this.wfEditId) {
                    res = await fetch(`/chat/workflows/${this.wfEditId}`, {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken(), 'Accept': 'application/json' },
                        body: JSON.stringify(payload),
                    });
                } else {
                    res = await fetch('/chat/workflows', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken(), 'Accept': 'application/json' },
                        body: JSON.stringify(payload),
                    });
                }
                if (res.ok) {
                    this.showWorkflowEditor = false;
                    await this.loadWorkflows();
                } else {
                    const e = await res.text();
                    alert('Failed to save workflow: ' + e.substring(0, 300));
                }
            } catch (e) { console.error('saveWorkflow error', e); }
        },

        async toggleWorkflow(wf) {
            try {
                const res = await fetch(`/chat/workflows/${wf.id}/toggle`, {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken(), 'Accept': 'application/json' },
                });
                if (res.ok) {
                    wf.is_active = !wf.is_active;
                }
            } catch {}
        },

        async runWorkflow(wfId) {
            try {
                const res = await fetch(`/chat/workflows/${wfId}/run`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken(), 'Accept': 'application/json' },
                });
                if (res.ok) {
                    alert('Workflow run started successfully');
                } else {
                    alert('Failed to run workflow');
                }
            } catch {}
        },

        async deleteWorkflow(wfId) {
            if (!confirm('Delete this workflow?')) return;
            try {
                await fetch(`/chat/workflows/${wfId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken(), 'X-Requested-With': 'XMLHttpRequest' },
                });
                this.workflowsList = this.workflowsList.filter(w => w.id !== wfId);
            } catch {}
        },
    };
}
</script>

{{-- LiveKit JS SDK --}}
<script src="https://cdn.jsdelivr.net/npm/livekit-client@2.9.1/dist/livekit-client.umd.min.js"></script>
@endpush
