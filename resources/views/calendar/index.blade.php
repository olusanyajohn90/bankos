<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
            {{ __('Calendar') }}
        </h2>
        <p class="text-sm text-bankos-text-sec mt-1">Manage events, meetings, and schedules</p>
    </x-slot>

    {{-- FullCalendar CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>

    {{-- FullCalendar bankOS theme overrides --}}
    <style>
        /* Primary button colors */
        .fc .fc-button-primary {
            background-color: var(--bankos-primary) !important;
            border-color: var(--bankos-primary) !important;
        }
        .fc .fc-button-primary:hover {
            background-color: var(--bankos-secondary) !important;
            border-color: var(--bankos-secondary) !important;
        }
        .fc .fc-button-primary:not(:disabled).fc-button-active,
        .fc .fc-button-primary:not(:disabled):active {
            background-color: var(--bankos-secondary) !important;
            border-color: var(--bankos-secondary) !important;
        }

        /* Day numbers and text */
        .fc .fc-daygrid-day-number {
            color: var(--fc-text, inherit);
        }
        .fc .fc-col-header-cell-cushion,
        .fc .fc-list-day-cushion {
            color: var(--fc-text, inherit);
        }

        /* Dark mode */
        .dark .fc {
            --fc-border-color: rgb(55 65 81);
            --fc-page-bg-color: transparent;
            --fc-neutral-bg-color: rgb(31 41 55);
            --fc-list-event-hover-bg-color: rgb(55 65 81);
            --fc-today-bg-color: rgba(37, 99, 235, 0.08);
            --fc-text: rgb(229 231 235);
        }
        .dark .fc .fc-daygrid-day-number,
        .dark .fc .fc-col-header-cell-cushion,
        .dark .fc .fc-list-day-cushion,
        .dark .fc .fc-list-event td,
        .dark .fc .fc-toolbar-title {
            color: rgb(229 231 235);
        }
        .dark .fc .fc-button-primary {
            background-color: var(--bankos-primary) !important;
            border-color: var(--bankos-primary) !important;
        }
        .dark .fc .fc-scrollgrid {
            border-color: rgb(55 65 81);
        }

        /* Now indicator */
        .fc .fc-timegrid-now-indicator-line {
            border-color: #ef4444;
        }
        .fc .fc-timegrid-now-indicator-arrow {
            border-color: #ef4444;
            border-top-color: transparent;
            border-bottom-color: transparent;
        }

        /* Event styling */
        .fc .fc-event {
            border-radius: 4px;
            font-size: 0.8rem;
            padding: 1px 4px;
            cursor: pointer;
        }
        .fc .fc-daygrid-event-dot {
            display: none;
        }
        .fc .fc-daygrid-more-link {
            color: var(--bankos-primary);
            font-weight: 600;
        }

        /* Responsive: hide sidebar on small screens by default */
        @media (max-width: 768px) {
            .calendar-sidebar {
                display: none;
            }
            .calendar-sidebar.sidebar-open {
                display: block;
                position: fixed;
                top: 0;
                left: 0;
                width: 280px;
                height: 100vh;
                z-index: 50;
                overflow-y: auto;
            }
        }
    </style>

    {{-- Page wrapper with Alpine state --}}
    <div x-data="calendarPage()" x-init="init()" class="flex gap-6">

        {{-- Mobile sidebar toggle --}}
        <button @click="mobileSidebar = !mobileSidebar"
                class="md:hidden fixed bottom-4 left-4 z-40 btn btn-primary rounded-full w-12 h-12 flex items-center justify-center shadow-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        {{-- Mobile sidebar backdrop --}}
        <div x-show="mobileSidebar" @click="mobileSidebar = false"
             class="fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm md:hidden"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             style="display: none;"></div>

        {{-- Left Sidebar --}}
        <div class="calendar-sidebar w-64 flex-shrink-0"
             :class="{ 'sidebar-open bg-bankos-surface dark:bg-bankos-dark-surface p-4': mobileSidebar }">
            <div class="card p-5 space-y-6 sticky top-24">

                {{-- New Event Button --}}
                <button @click="openCreateModal(null, null, false)"
                        class="btn btn-primary w-full justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Event
                </button>

                {{-- My Calendars --}}
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-bankos-muted mb-3">My Calendars</h3>
                    <div class="space-y-2">
                        @foreach($calendars->where('type', 'personal') as $cal)
                        <label class="flex items-center gap-2.5 cursor-pointer group">
                            <input type="checkbox"
                                   checked
                                   value="{{ $cal->id }}"
                                   @change="toggleCalendar('{{ $cal->id }}')"
                                   class="rounded border-gray-300 dark:border-gray-600 text-{{ $cal->color ?? 'blue' }}-500 focus:ring-bankos-primary w-4 h-4"
                                   style="accent-color: {{ $cal->color ?? '#3B82F6' }}">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $cal->color ?? '#3B82F6' }}"></span>
                            <span class="text-sm text-bankos-text dark:text-bankos-dark-text group-hover:text-bankos-primary truncate">{{ $cal->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Other Calendars (shared / system) --}}
                @if($calendars->whereIn('type', ['shared', 'system', 'team'])->count())
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-bankos-muted mb-3">Other Calendars</h3>
                    <div class="space-y-2">
                        @foreach($calendars->whereIn('type', ['shared', 'system', 'team']) as $cal)
                        <label class="flex items-center gap-2.5 cursor-pointer group">
                            <input type="checkbox"
                                   checked
                                   value="{{ $cal->id }}"
                                   @change="toggleCalendar('{{ $cal->id }}')"
                                   class="rounded border-gray-300 dark:border-gray-600 focus:ring-bankos-primary w-4 h-4"
                                   style="accent-color: {{ $cal->color ?? '#6B7280' }}">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $cal->color ?? '#6B7280' }}"></span>
                            <span class="text-sm text-bankos-text dark:text-bankos-dark-text group-hover:text-bankos-primary truncate">{{ $cal->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Legend --}}
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-bankos-muted mb-3">Legend</h3>
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            <span class="text-xs text-bankos-text-sec">Meeting</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <span class="text-xs text-bankos-text-sec">Task</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                            <span class="text-xs text-bankos-text-sec">Leave</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                            <span class="text-xs text-bankos-text-sec">Holiday</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                            <span class="text-xs text-bankos-text-sec">Loan Maturity</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span>
                            <span class="text-xs text-bankos-text-sec">Appointment</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                            <span class="text-xs text-bankos-text-sec">Reminder</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-gray-500"></span>
                            <span class="text-xs text-bankos-text-sec">Custom</span>
                        </div>
                    </div>
                </div>

                {{-- Create Calendar --}}
                <div class="pt-4 border-t border-bankos-border dark:border-bankos-dark-border">
                    <button @click="showNewCalendar = !showNewCalendar"
                            class="text-xs text-bankos-primary hover:underline flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Calendar
                    </button>
                    <div x-show="showNewCalendar" x-transition class="mt-3 space-y-2" style="display: none;">
                        <input x-model="newCalendar.name" type="text" placeholder="Calendar name"
                               class="input-field text-sm py-1.5 px-2.5">
                        <div class="flex gap-1.5">
                            <template x-for="c in presetColors" :key="c">
                                <button @click="newCalendar.color = c"
                                        class="w-6 h-6 rounded-full border-2 transition-all"
                                        :style="'background-color:' + c"
                                        :class="newCalendar.color === c ? 'border-bankos-text dark:border-white scale-110' : 'border-transparent'">
                                </button>
                            </template>
                        </div>
                        <select x-model="newCalendar.type" class="input-field text-sm py-1.5 px-2.5">
                            <option value="personal">Personal</option>
                            <option value="team">Team</option>
                            <option value="shared">Shared</option>
                        </select>
                        <button @click="createCalendar()" class="btn btn-primary text-xs py-1.5 w-full">Create</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Calendar Area --}}
        <div class="flex-1 min-w-0">
            <div class="card p-4 md:p-6">
                <div id="calendar"></div>
            </div>
        </div>

        {{-- ===== CREATE / EDIT EVENT MODAL ===== --}}
        <div x-show="showCreateModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display: none;">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showCreateModal = false"></div>

            {{-- Modal content --}}
            <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto bg-bankos-surface dark:bg-bankos-dark-surface rounded-xl shadow-2xl border border-bankos-border dark:border-bankos-dark-border"
                 @click.stop>
                <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-bankos-text dark:text-bankos-dark-text"
                        x-text="editingEventId ? 'Edit Event' : 'New Event'"></h3>
                    <button @click="showCreateModal = false" class="text-bankos-muted hover:text-bankos-text dark:hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    {{-- Title --}}
                    <div>
                        <label class="form-label">Title <span class="text-red-500">*</span></label>
                        <input x-model="form.title" type="text" class="input-field" placeholder="Event title" required>
                    </div>

                    {{-- Date/Time Row --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Start</label>
                            <input x-model="form.start_at" :type="form.all_day ? 'date' : 'datetime-local'" class="input-field">
                        </div>
                        <div>
                            <label class="form-label">End</label>
                            <input x-model="form.end_at" :type="form.all_day ? 'date' : 'datetime-local'" class="input-field">
                        </div>
                    </div>

                    {{-- All Day --}}
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input x-model="form.all_day" type="checkbox"
                               class="rounded border-gray-300 dark:border-gray-600 text-bankos-primary focus:ring-bankos-primary">
                        <span class="text-sm text-bankos-text dark:text-bankos-dark-text">All day event</span>
                    </label>

                    {{-- Type & Calendar Row --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Type</label>
                            <select x-model="form.type" class="input-field">
                                <option value="meeting">Meeting</option>
                                <option value="appointment">Appointment</option>
                                <option value="reminder">Reminder</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Calendar</label>
                            <select x-model="form.calendar_id" class="input-field">
                                <option value="">-- Select --</option>
                                @foreach($calendars as $cal)
                                <option value="{{ $cal->id }}">{{ $cal->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="form-label">Description</label>
                        <textarea x-model="form.description" class="input-field" rows="3" placeholder="Optional description"></textarea>
                    </div>

                    {{-- Location --}}
                    <div>
                        <label class="form-label">Location</label>
                        <input x-model="form.location" type="text" class="input-field" placeholder="e.g. Conference Room A">
                    </div>

                    {{-- Color --}}
                    <div>
                        <label class="form-label">Color</label>
                        <div class="flex gap-2 flex-wrap">
                            <template x-for="c in presetColors" :key="c">
                                <button type="button" @click="form.color = c"
                                        class="w-8 h-8 rounded-full border-2 transition-all hover:scale-110"
                                        :style="'background-color:' + c"
                                        :class="form.color === c ? 'border-bankos-text dark:border-white ring-2 ring-offset-2 ring-bankos-primary' : 'border-transparent'">
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Attendees --}}
                    <div>
                        <label class="form-label">Attendees</label>
                        <div class="border border-bankos-border dark:border-bankos-dark-border rounded-md p-3 max-h-40 overflow-y-auto space-y-1.5">
                            @foreach($tenantUsers as $user)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" value="{{ $user->id }}"
                                       x-model="form.attendee_ids"
                                       class="rounded border-gray-300 dark:border-gray-600 text-bankos-primary focus:ring-bankos-primary">
                                <span class="text-sm text-bankos-text dark:text-bankos-dark-text">{{ $user->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Recurring --}}
                    <div class="border border-bankos-border dark:border-bankos-dark-border rounded-lg p-4 space-y-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input x-model="form.is_recurring" type="checkbox"
                                   class="rounded border-gray-300 dark:border-gray-600 text-bankos-primary focus:ring-bankos-primary">
                            <span class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text">Recurring event</span>
                        </label>
                        <div x-show="form.is_recurring" x-transition class="space-y-3 pl-6" style="display: none;">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="form-label">Frequency</label>
                                    <select x-model="recurrence.frequency" class="input-field text-sm">
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Repeat every</label>
                                    <div class="flex items-center gap-2">
                                        <input x-model="recurrence.interval" type="number" min="1" max="99"
                                               class="input-field text-sm w-20">
                                        <span class="text-sm text-bankos-text-sec" x-text="recurrence.frequency === 'daily' ? 'day(s)' : recurrence.frequency === 'weekly' ? 'week(s)' : 'month(s)'"></span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Ends on</label>
                                <input x-model="form.recurrence_end" type="date" class="input-field text-sm">
                            </div>
                        </div>
                    </div>

                    {{-- Reminders --}}
                    <div>
                        <label class="form-label">Reminders</label>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="(r, idx) in form.reminder_minutes" :key="idx">
                                <span class="inline-flex items-center gap-1 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 text-xs font-medium px-2.5 py-1 rounded-full">
                                    <span x-text="formatReminder(r)"></span>
                                    <button @click="form.reminder_minutes.splice(idx, 1)" class="hover:text-red-500">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </span>
                            </template>
                        </div>
                        <div class="flex gap-2 mt-2">
                            <select x-model="reminderToAdd" class="input-field text-sm flex-1">
                                <option value="">Add reminder...</option>
                                <option value="5">5 minutes before</option>
                                <option value="15">15 minutes before</option>
                                <option value="30">30 minutes before</option>
                                <option value="60">1 hour before</option>
                                <option value="1440">1 day before</option>
                            </select>
                            <button @click="addReminder()" type="button"
                                    class="btn btn-secondary text-xs px-3"
                                    :disabled="!reminderToAdd">Add</button>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 border-t border-bankos-border dark:border-bankos-dark-border flex items-center justify-end gap-3">
                    <button @click="showCreateModal = false" class="btn btn-secondary">Cancel</button>
                    <button @click="saveEvent()" class="btn btn-primary" :disabled="saving">
                        <svg x-show="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="saving ? 'Saving...' : (editingEventId ? 'Update Event' : 'Create Event')"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ===== EVENT DETAIL MODAL ===== --}}
        <div x-show="showDetailModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display: none;">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showDetailModal = false"></div>

            {{-- Modal content --}}
            <div class="relative w-full max-w-lg bg-bankos-surface dark:bg-bankos-dark-surface rounded-xl shadow-2xl border border-bankos-border dark:border-bankos-dark-border"
                 @click.stop>
                {{-- Colored header bar --}}
                <div class="h-2 rounded-t-xl" :style="'background-color:' + (detail.color || '#3B82F6')"></div>

                <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-semibold text-bankos-text dark:text-bankos-dark-text truncate"
                            x-text="detail.title"></h3>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="badge text-xs"
                                  :class="typeBadgeClass(detail.type)"
                                  x-text="detail.type"></span>
                            <span x-show="detail.status"
                                  class="badge text-xs"
                                  :class="detail.status === 'confirmed' ? 'badge-active' : detail.status === 'tentative' ? 'badge-pending' : 'badge-danger'"
                                  x-text="detail.status"></span>
                        </div>
                    </div>
                    <button @click="showDetailModal = false" class="text-bankos-muted hover:text-bankos-text dark:hover:text-white ml-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    {{-- Date/Time --}}
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-bankos-muted mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <div class="text-sm text-bankos-text dark:text-bankos-dark-text">
                            <div x-text="formatEventTime(detail.start, detail.end, detail.allDay)"></div>
                        </div>
                    </div>

                    {{-- Location --}}
                    <div x-show="detail.location" class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-bankos-muted mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="text-sm text-bankos-text dark:text-bankos-dark-text" x-text="detail.location"></span>
                    </div>

                    {{-- Description --}}
                    <div x-show="detail.description" class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-bankos-muted mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                        </svg>
                        <p class="text-sm text-bankos-text-sec dark:text-bankos-dark-text-sec" x-text="detail.description"></p>
                    </div>

                    {{-- Calendar name --}}
                    <div x-show="detail.calendar_name" class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-bankos-muted mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <span class="text-sm text-bankos-text-sec" x-text="detail.calendar_name"></span>
                    </div>

                    {{-- Created by --}}
                    <div x-show="detail.created_by" class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-bankos-muted mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="text-sm text-bankos-text-sec">Created by <span class="font-medium text-bankos-text dark:text-bankos-dark-text" x-text="detail.created_by"></span></span>
                    </div>

                    {{-- Source-specific info --}}
                    <template x-if="detail.source === 'chat_task'">
                        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3">
                            <p class="text-xs font-semibold text-amber-700 dark:text-amber-300 mb-1">Chat Task</p>
                            <p class="text-sm text-amber-800 dark:text-amber-200">This event was created from a chat task.</p>
                        </div>
                    </template>
                    <template x-if="detail.source === 'loan'">
                        <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-3">
                            <p class="text-xs font-semibold text-purple-700 dark:text-purple-300 mb-1">Loan Event</p>
                            <p class="text-sm text-purple-800 dark:text-purple-200">This event is linked to a loan record.</p>
                        </div>
                    </template>
                    <template x-if="detail.source === 'leave_request'">
                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3">
                            <p class="text-xs font-semibold text-green-700 dark:text-green-300 mb-1">Leave Request</p>
                            <p class="text-sm text-green-800 dark:text-green-200">This event represents an approved leave request.</p>
                        </div>
                    </template>

                    {{-- Attendees --}}
                    <div x-show="detail.attendees && detail.attendees.length > 0">
                        <h4 class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-2">Attendees</h4>
                        <div class="space-y-1.5">
                            <template x-for="a in detail.attendees" :key="a.user_id">
                                <div class="flex items-center justify-between py-1">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-bankos-primary/10 text-bankos-primary flex items-center justify-center text-xs font-semibold"
                                             x-text="a.name ? a.name.charAt(0).toUpperCase() : '?'"></div>
                                        <span class="text-sm text-bankos-text dark:text-bankos-dark-text" x-text="a.name"></span>
                                    </div>
                                    <span class="text-xs px-2 py-0.5 rounded-full"
                                          :class="{
                                              'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': a.status === 'accepted',
                                              'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': a.status === 'declined',
                                              'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400': a.status === 'tentative',
                                              'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400': a.status === 'pending' || !a.status,
                                          }"
                                          x-text="a.status || 'pending'"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- RSVP Buttons (if user is an attendee) --}}
                    <div x-show="detail.isAttendee" class="flex items-center gap-2 pt-2 border-t border-bankos-border dark:border-bankos-dark-border">
                        <span class="text-sm text-bankos-text-sec mr-2">Your RSVP:</span>
                        <button @click="respondToEvent('accepted')"
                                class="btn text-xs py-1.5 px-3"
                                :class="detail.myRsvp === 'accepted' ? 'btn-primary' : 'btn-secondary'">
                            Accept
                        </button>
                        <button @click="respondToEvent('tentative')"
                                class="btn text-xs py-1.5 px-3"
                                :class="detail.myRsvp === 'tentative' ? 'bg-yellow-500 text-white border-yellow-500' : 'btn-secondary'">
                            Tentative
                        </button>
                        <button @click="respondToEvent('declined')"
                                class="btn text-xs py-1.5 px-3"
                                :class="detail.myRsvp === 'declined' ? 'bg-red-500 text-white border-red-500' : 'btn-secondary'">
                            Decline
                        </button>
                    </div>
                </div>

                {{-- Footer actions --}}
                <div x-show="detail.canEdit" class="px-6 py-4 border-t border-bankos-border dark:border-bankos-dark-border flex items-center justify-between">
                    <button @click="deleteEvent()" class="btn text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 text-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete
                    </button>
                    <button @click="editFromDetail()" class="btn btn-primary text-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Event
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Alpine.js Component + FullCalendar Integration ===== --}}
    <script>
    function calendarPage() {
        return {
            // State
            calendar: null,
            csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            showCreateModal: false,
            showDetailModal: false,
            showNewCalendar: false,
            mobileSidebar: false,
            editingEventId: null,
            saving: false,
            reminderToAdd: '',
            hiddenCalendarIds: [],

            // Current user ID for RSVP check
            currentUserId: {{ auth()->id() }},

            // Preset colors
            presetColors: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#06B6D4', '#F97316', '#6366F1', '#14B8A6'],

            // Form data
            form: {
                title: '',
                start_at: '',
                end_at: '',
                all_day: false,
                type: 'meeting',
                description: '',
                location: '',
                color: '#3B82F6',
                calendar_id: '',
                is_recurring: false,
                recurrence_rule: '',
                recurrence_end: '',
                attendee_ids: [],
                reminder_minutes: [],
            },

            recurrence: {
                frequency: 'weekly',
                interval: 1,
            },

            // New calendar form
            newCalendar: {
                name: '',
                color: '#3B82F6',
                type: 'personal',
            },

            // Detail modal data
            detail: {
                id: null,
                title: '',
                start: '',
                end: '',
                allDay: false,
                color: '#3B82F6',
                type: '',
                source: '',
                description: '',
                location: '',
                status: '',
                created_by: '',
                attendees: [],
                calendar_name: '',
                event_id: null,
                canEdit: false,
                isAttendee: false,
                myRsvp: '',
            },

            // Initialize FullCalendar
            init() {
                const self = this;
                const calendarEl = document.getElementById('calendar');

                this.calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                    },
                    events: function(info, successCallback, failureCallback) {
                        fetch('/calendar/events?' + new URLSearchParams({
                            start: info.startStr,
                            end: info.endStr,
                        }), {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': self.csrfToken,
                            },
                        })
                        .then(r => {
                            if (!r.ok) throw new Error('HTTP ' + r.status);
                            return r.json();
                        })
                        .then(data => {
                            console.log('Calendar events received:', data);
                            // Handle error response
                            if (data.error) { console.error('Calendar API error:', data.error); successCallback([]); return; }
                            const events = Array.isArray(data) ? data : [];
                            // Filter out hidden calendars
                            const filtered = events.filter(e => {
                                if (self.hiddenCalendarIds.length === 0) return true;
                                const calId = e.extendedProps?.calendar_id;
                                return !calId || !self.hiddenCalendarIds.includes(String(calId));
                            });
                            successCallback(filtered);
                        })
                        .catch(err => {
                            console.error('Failed to load calendar events:', err);
                            successCallback([]);
                        });
                    },
                    editable: true,
                    selectable: true,
                    selectMirror: true,
                    dayMaxEvents: true,
                    nowIndicator: true,
                    height: 'auto',

                    // Click empty date to create event
                    select: function(info) {
                        self.openCreateModal(info.startStr, info.endStr, info.allDay);
                    },

                    // Click event to view details
                    eventClick: function(info) {
                        info.jsEvent.preventDefault();
                        self.openEventDetail(info.event);
                    },

                    // Drag to reschedule
                    eventDrop: function(info) {
                        self.updateEvent(info.event.extendedProps.event_id, {
                            start_at: info.event.startStr,
                            end_at: info.event.endStr,
                        });
                    },

                    // Resize to change duration
                    eventResize: function(info) {
                        self.updateEvent(info.event.extendedProps.event_id, {
                            start_at: info.event.startStr,
                            end_at: info.event.endStr,
                        });
                    },
                });

                this.calendar.render();
            },

            // Toggle calendar visibility
            toggleCalendar(calId) {
                const idx = this.hiddenCalendarIds.indexOf(String(calId));
                if (idx > -1) {
                    this.hiddenCalendarIds.splice(idx, 1);
                } else {
                    this.hiddenCalendarIds.push(String(calId));
                }
                this.calendar.refetchEvents();
            },

            // Reset form
            resetForm() {
                this.form = {
                    title: '',
                    start_at: '',
                    end_at: '',
                    all_day: false,
                    type: 'meeting',
                    description: '',
                    location: '',
                    color: '#3B82F6',
                    calendar_id: '',
                    is_recurring: false,
                    recurrence_rule: '',
                    recurrence_end: '',
                    attendee_ids: [],
                    reminder_minutes: [],
                };
                this.recurrence = { frequency: 'weekly', interval: 1 };
                this.editingEventId = null;
                this.reminderToAdd = '';
            },

            // Open create modal
            openCreateModal(start, end, allDay) {
                this.resetForm();
                this.mobileSidebar = false;
                if (start) {
                    if (allDay) {
                        this.form.all_day = true;
                        this.form.start_at = start;
                        this.form.end_at = end || start;
                    } else {
                        this.form.start_at = this.toLocalDatetime(start);
                        this.form.end_at = end ? this.toLocalDatetime(end) : this.toLocalDatetime(start);
                    }
                }
                this.showCreateModal = true;
            },

            // Open event detail
            openEventDetail(event) {
                const props = event.extendedProps || {};
                this.detail = {
                    id: event.id,
                    title: event.title,
                    start: event.startStr,
                    end: event.endStr,
                    allDay: event.allDay,
                    color: event.backgroundColor || event.borderColor || '#3B82F6',
                    type: props.type || '',
                    source: props.source || 'manual',
                    description: props.description || '',
                    location: props.location || '',
                    status: props.status || '',
                    created_by: props.created_by || '',
                    attendees: props.attendees || [],
                    calendar_name: props.calendar_name || '',
                    event_id: props.event_id || event.id,
                    canEdit: props.source === 'manual',
                    isAttendee: false,
                    myRsvp: '',
                };

                // Check if current user is an attendee
                if (this.detail.attendees && this.detail.attendees.length) {
                    const myAttendee = this.detail.attendees.find(a => a.user_id === this.currentUserId);
                    if (myAttendee) {
                        this.detail.isAttendee = true;
                        this.detail.myRsvp = myAttendee.status || 'pending';
                    }
                }

                this.showDetailModal = true;
            },

            // Edit from detail modal
            editFromDetail() {
                this.showDetailModal = false;
                this.resetForm();
                this.editingEventId = this.detail.event_id;
                this.form.title = this.detail.title;
                this.form.all_day = this.detail.allDay;
                this.form.start_at = this.detail.allDay ? this.detail.start : this.toLocalDatetime(this.detail.start);
                this.form.end_at = this.detail.allDay ? this.detail.end : this.toLocalDatetime(this.detail.end);
                this.form.type = this.detail.type;
                this.form.description = this.detail.description;
                this.form.location = this.detail.location;
                this.form.color = this.detail.color;
                if (this.detail.attendees) {
                    this.form.attendee_ids = this.detail.attendees.map(a => String(a.user_id));
                }
                this.showCreateModal = true;
            },

            // Build RRULE from recurrence settings
            buildRecurrenceRule() {
                if (!this.form.is_recurring) return '';
                const freqMap = { daily: 'DAILY', weekly: 'WEEKLY', monthly: 'MONTHLY' };
                let rule = 'FREQ=' + (freqMap[this.recurrence.frequency] || 'WEEKLY');
                if (this.recurrence.interval > 1) {
                    rule += ';INTERVAL=' + this.recurrence.interval;
                }
                if (this.form.recurrence_end) {
                    rule += ';UNTIL=' + this.form.recurrence_end.replace(/-/g, '') + 'T235959Z';
                }
                return rule;
            },

            // Save (create or update) event
            async saveEvent() {
                if (!this.form.title.trim()) {
                    alert('Please enter an event title.');
                    return;
                }

                this.saving = true;
                this.form.recurrence_rule = this.buildRecurrenceRule();

                const url = this.editingEventId
                    ? '/calendar/events/' + this.editingEventId
                    : '/calendar/events';
                const method = this.editingEventId ? 'PATCH' : 'POST';

                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                        },
                        body: JSON.stringify(this.form),
                    });

                    if (!response.ok) {
                        const err = await response.json();
                        throw new Error(err.message || 'Failed to save event');
                    }

                    this.showCreateModal = false;
                    this.calendar.refetchEvents();
                    this.resetForm();
                } catch (e) {
                    alert(e.message);
                } finally {
                    this.saving = false;
                }
            },

            // Update event (drag/drop, resize)
            async updateEvent(eventId, data) {
                try {
                    const response = await fetch('/calendar/events/' + eventId, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                        },
                        body: JSON.stringify(data),
                    });

                    if (!response.ok) {
                        this.calendar.refetchEvents(); // revert on failure
                    }
                } catch (e) {
                    console.error('Update failed:', e);
                    this.calendar.refetchEvents();
                }
            },

            // Delete event
            async deleteEvent() {
                if (!confirm('Are you sure you want to delete this event?')) return;

                try {
                    const response = await fetch('/calendar/events/' + this.detail.event_id, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                        },
                    });

                    if (response.ok) {
                        this.showDetailModal = false;
                        this.calendar.refetchEvents();
                    } else {
                        const err = await response.json();
                        alert(err.message || 'Failed to delete event');
                    }
                } catch (e) {
                    alert('Failed to delete event');
                }
            },

            // RSVP to event
            async respondToEvent(status) {
                try {
                    const response = await fetch('/calendar/events/' + this.detail.event_id + '/respond', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                        },
                        body: JSON.stringify({ status: status }),
                    });

                    if (response.ok) {
                        this.detail.myRsvp = status;
                        // Update attendee status in detail view
                        const attendee = this.detail.attendees.find(a => a.user_id === this.currentUserId);
                        if (attendee) attendee.status = status;
                        this.calendar.refetchEvents();
                    }
                } catch (e) {
                    alert('Failed to update RSVP');
                }
            },

            // Create new calendar
            async createCalendar() {
                if (!this.newCalendar.name.trim()) return;

                try {
                    const response = await fetch('/calendar/calendars', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                        },
                        body: JSON.stringify(this.newCalendar),
                    });

                    if (response.ok) {
                        window.location.reload();
                    } else {
                        const err = await response.json();
                        alert(err.message || 'Failed to create calendar');
                    }
                } catch (e) {
                    alert('Failed to create calendar');
                }
            },

            // Add reminder
            addReminder() {
                if (!this.reminderToAdd) return;
                const val = parseInt(this.reminderToAdd);
                if (!this.form.reminder_minutes.includes(val)) {
                    this.form.reminder_minutes.push(val);
                }
                this.reminderToAdd = '';
            },

            // Format reminder label
            formatReminder(minutes) {
                if (minutes < 60) return minutes + ' min before';
                if (minutes < 1440) return (minutes / 60) + ' hr before';
                return (minutes / 1440) + ' day before';
            },

            // Format event time for detail view
            formatEventTime(start, end, allDay) {
                if (!start) return '';
                const opts = allDay
                    ? { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
                    : { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: '2-digit' };

                const startDate = new Date(start);
                let result = startDate.toLocaleDateString(undefined, opts);

                if (allDay) {
                    if (end && end !== start) {
                        const endDate = new Date(end);
                        // FullCalendar exclusive end for all-day
                        endDate.setDate(endDate.getDate() - 1);
                        if (endDate.getTime() !== startDate.getTime()) {
                            result += ' - ' + endDate.toLocaleDateString(undefined, opts);
                        }
                    }
                    result += ' (All day)';
                } else if (end) {
                    const endDate = new Date(end);
                    if (startDate.toDateString() === endDate.toDateString()) {
                        result += ' - ' + endDate.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' });
                    } else {
                        result += ' - ' + endDate.toLocaleDateString(undefined, opts);
                    }
                }

                return result;
            },

            // Convert ISO string to local datetime-local input value
            toLocalDatetime(isoStr) {
                if (!isoStr) return '';
                const d = new Date(isoStr);
                if (isNaN(d.getTime())) return isoStr;
                const pad = n => String(n).padStart(2, '0');
                return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate())
                    + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
            },

            // Type badge CSS class
            typeBadgeClass(type) {
                const map = {
                    meeting: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                    task: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
                    leave: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                    holiday: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                    loan_maturity: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
                    appointment: 'bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-400',
                    reminder: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
                    custom: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400',
                };
                return map[type] || map.custom;
            },
        };
    }
    </script>
</x-app-layout>
