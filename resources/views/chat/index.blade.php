@extends('layouts.app')

@section('title', 'Internal Chat')

@section('content')
{{-- Neutralise the layout's px/py padding so the chat fills the viewport --}}
<div class="-mx-4 -my-8 sm:-mx-6 lg:-mx-8" x-data="chatApp()" x-init="init()">

    <div class="flex h-[calc(100vh-4rem)] overflow-hidden bg-bankos-bg">

        {{-- ══════════════════════════════════════════════════
             LEFT PANEL — Conversation List
        ══════════════════════════════════════════════════ --}}
        <div class="w-80 flex-shrink-0 flex flex-col border-r border-bankos-border bg-white overflow-hidden">

            {{-- Header --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-bankos-border bg-white">
                <h2 class="text-base font-semibold text-bankos-text">Chat</h2>
                <div class="flex items-center gap-1">
                    <button @click="showNewDirectModal = true; userSearch = ''; filterUsers()"
                        title="New Direct Message"
                        class="p-1.5 rounded-lg text-bankos-text-sec hover:bg-gray-100 hover:text-bankos-primary transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </button>
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
                    <input x-model="convSearch" type="text" placeholder="Search conversations..."
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

                        {{-- Avatar --}}
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
                            {{-- Unread badge --}}
                            <span x-show="conv.unread_count > 0"
                                x-text="conv.unread_count > 99 ? '99+' : conv.unread_count"
                                class="absolute -top-1 -right-1 min-w-[1.1rem] h-[1.1rem] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center leading-none">
                            </span>
                        </div>

                        {{-- Text content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1">
                                <span :class="conv.unread_count > 0 ? 'font-semibold text-bankos-text' : 'font-medium text-bankos-text'"
                                    class="text-sm truncate" x-text="conv.display_name"></span>
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
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            {{-- ── Empty state (no active conversation) ── --}}
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

            {{-- ── Active conversation ── --}}
            <template x-if="activeConversation">
                <div class="flex-1 flex flex-col min-h-0 overflow-hidden">

                    {{-- Conversation header --}}
                    <div class="flex items-center justify-between px-4 py-3 border-b border-bankos-border bg-white flex-shrink-0">
                        <div class="flex items-center gap-3 min-w-0">
                            <div :class="activeConversation.is_group ? 'bg-purple-100 text-purple-700' : 'bg-bankos-primary/10 text-bankos-primary'"
                                class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold flex-shrink-0">
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
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-bankos-text truncate" x-text="activeConversation.display_name"></h3>
                                <p class="text-xs text-bankos-text-sec"
                                    x-text="activeConversation.is_group
                                        ? activeConversation.participant_count + ' members'
                                        : 'Direct message'"></p>
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
                                            class="flex items-end gap-2 mb-1 group">

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
                                                    class="px-3 py-2 shadow-sm">

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

                                                    {{-- Body text --}}
                                                    <template x-if="!msg.is_deleted && msg.body">
                                                        <p class="text-sm whitespace-pre-wrap break-words" x-text="msg.body"></p>
                                                    </template>

                                                    {{-- Attachments --}}
                                                    <template x-if="msg.attachments && msg.attachments.length > 0">
                                                        <div class="mt-1.5 space-y-1.5">
                                                            <template x-for="att in msg.attachments" :key="att.id">
                                                                <div>
                                                                    {{-- Image --}}
                                                                    <template x-if="att.is_image">
                                                                        <a :href="att.url" target="_blank" rel="noopener">
                                                                            <img :src="att.url" :alt="att.file_name"
                                                                                class="max-w-[240px] rounded-lg cursor-pointer hover:opacity-90 transition-opacity border border-white/20">
                                                                        </a>
                                                                    </template>
                                                                    {{-- File --}}
                                                                    <template x-if="!att.is_image">
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

                                                    {{-- Timestamp + edited --}}
                                                    <div :class="msg.sender_id === currentUserId ? 'justify-end' : 'justify-start'"
                                                        class="flex items-center gap-1 mt-1">
                                                        <template x-if="msg.is_edited && !msg.is_deleted">
                                                            <span :class="msg.sender_id === currentUserId ? 'text-white/50' : 'text-bankos-muted'"
                                                                class="text-[10px] italic">edited</span>
                                                        </template>
                                                        <span :class="msg.sender_id === currentUserId ? 'text-white/60' : 'text-bankos-muted'"
                                                            class="text-[10px]" x-text="msg.created_at"></span>
                                                    </div>
                                                </div>

                                                {{-- Action buttons (own messages, on hover) --}}
                                                <template x-if="!msg.is_deleted">
                                                    <div :class="msg.sender_id === currentUserId ? 'flex-row-reverse' : 'flex-row'"
                                                        class="flex gap-1 mt-0.5 opacity-0 group-hover:opacity-100 transition-opacity px-1">
                                                        <button @click="replyTo(msg)" title="Reply"
                                                            class="p-1 rounded text-bankos-muted hover:text-bankos-primary hover:bg-gray-100 transition-colors">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                                            </svg>
                                                        </button>
                                                        <template x-if="msg.sender_id === currentUserId">
                                                            <button @click="editMessage(msg)" title="Edit"
                                                                class="p-1 rounded text-bankos-muted hover:text-bankos-primary hover:bg-gray-100 transition-colors">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                </svg>
                                                            </button>
                                                        </template>
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
                                        <p class="text-sm">No messages yet — say hello!</p>
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
                            <button @click="attachedFile = null; $refs.fileInput.value = ''"
                                class="p-0.5 rounded text-bankos-muted hover:text-red-500 transition-colors flex-shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Input row --}}
                        <div class="flex items-end gap-2">
                            {{-- Attach file --}}
                            <template x-if="!editingMessage">
                                <div>
                                    <input type="file" x-ref="fileInput" class="hidden"
                                        @change="attachedFile = $event.target.files[0] ?? null">
                                    <button @click="$refs.fileInput.click()"
                                        class="p-2 rounded-lg text-bankos-text-sec hover:bg-gray-100 hover:text-bankos-primary transition-colors flex-shrink-0"
                                        title="Attach file">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>

                            {{-- Textarea --}}
                            <textarea
                                x-model="editingMessage ? editBody : newMessage"
                                @keydown.enter.prevent="handleEnter($event)"
                                @input="autoResize($event.target)"
                                x-ref="messageInput"
                                rows="1"
                                :placeholder="editingMessage ? 'Edit your message...' : 'Type a message...'"
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

                        <p class="text-[10px] text-bankos-muted mt-1.5">Enter to send &middot; Shift+Enter for new line</p>
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

                {{-- Selected count --}}
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
        preselectedId: '{{ $preselectedId ?? "" }}',
        currentUserId: {{ auth()->id() }},
        allUsers: @json($tenantUsers),

        // ── Init ────────────────────────────────────────────────────────────
        async init() {
            await this.loadConversations();

            // Open preselected conversation
            if (this.preselectedId) {
                const conv = this.conversations.find(c => c.id == this.preselectedId);
                if (conv) await this.openConversation(conv);
            }

            // Poll new messages every 8 s
            this.msgPollInterval = setInterval(() => {
                if (this.pollingActive && this.activeConversation) {
                    this.pollNewMessages();
                }
            }, 8000);

            // Refresh conversation list every 15 s
            this.convListRefreshInterval = setInterval(() => {
                if (this.pollingActive) {
                    this.loadConversations(false);
                }
            }, 15000);
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
                // Allow newline
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

            // Update URL without reload
            const url = new URL(window.location.href);
            url.searchParams.set('conversation_id', conv.id);
            window.history.replaceState({}, '', url.toString());

            await this.loadMessages();

            // Mark active conv unread count as 0 locally
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
                    // Update conversation preview
                    const last = newMsgs[newMsgs.length - 1];
                    const idx = this.conversations.findIndex(c => c.id === this.activeConversation.id);
                    if (idx !== -1) {
                        this.conversations[idx].last_message_preview = last.body || '📎 Attachment';
                        this.conversations[idx].last_message_at = 'Just now';
                        this.conversations[idx].unread_count = 0;
                    }
                    this.scrollToBottom(true);
                }
                this.onSuccess();
            } catch {
                this.onFailure();
            }
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
                // Restore scroll position
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
                // Update conv list preview
                const idx = this.conversations.findIndex(c => c.id === this.activeConversation.id);
                if (idx !== -1) {
                    this.conversations[idx].last_message_preview = msg.body || '📎 Attachment';
                    this.conversations[idx].last_message_at = 'Just now';
                }
                this.onSuccess();
                this.scrollToBottom(true);
            } catch {
                this.onFailure();
                this.newMessage = body; // Restore on failure
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
