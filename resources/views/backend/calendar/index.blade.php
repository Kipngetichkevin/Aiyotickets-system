@include('backend.includes.header')
@include('backend.includes.Sidebar')

{{-- FullCalendar CSS --}}
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">

<main class="app-main">
    <div style="padding: 28px 32px; width: 100%; box-sizing: border-box;">

        {{-- ============ HEADER ============ --}}
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="font-size: 13px; color: #6b7280; margin-bottom: 6px;">
                    <a href="{{ route('dashboard') }}" style="color: #6b7280; text-decoration: none;">Dashboard</a>
                    <span> / </span>
                    <span style="color: #374151; font-weight: 500;">Calendar</span>
                </div>
                <h1 style="font-size: 26px; font-weight: 700; color: #111827; margin: 0;">Calendar</h1>
                <p style="font-size: 14px; color: #6b7280; margin: 6px 0 0 0;">
                    All your events on one view. Click any event for details.
                </p>
            </div>

            <a href="{{ route('backend.events.create') }}"
               style="display: inline-flex; align-items: center; gap: 8px; padding: 11px 20px; background: #0ea5e9; color: white; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 14px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                New Event
            </a>
        </div>

        {{-- ============ LEGEND ============ --}}
        <div style="display: flex; gap: 16px; margin-bottom: 20px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: #6b7280;">
                <span style="width: 10px; height: 10px; border-radius: 50%; background: #0ea5e9;"></span>
                Active
            </div>
            <div style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: #6b7280;">
                <span style="width: 10px; height: 10px; border-radius: 50%; background: #9ca3af;"></span>
                Inactive
            </div>
        </div>

        {{-- ============ CALENDAR ============ --}}
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 14px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div id="calendar"></div>
        </div>

    </div>
</main>

{{-- ============ EVENT DETAILS MODAL ============ --}}
<div id="eventModal" style="display: none; position: fixed; inset: 0; z-index: 300;">
    <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.5);" onclick="closeEventModal()"></div>

    <div style="position: relative; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 16px;">
        <div style="width: 100%; max-width: 520px; background: white; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.15); overflow: hidden;">

            {{-- Header --}}
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 18px 24px; border-bottom: 1px solid #e5e7eb;">
                <h3 id="modalTitle" style="font-size: 17px; font-weight: 700; color: #111827; margin: 0;">Event</h3>
                <button type="button" onclick="closeEventModal()"
                        style="padding: 6px; background: none; border: none; cursor: pointer; border-radius: 6px;">
                    <svg style="width: 20px; height: 20px; color: #6b7280;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M18 6 6 18M6 6l12 12" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>

            {{-- Image --}}
            <div id="modalImageWrapper" style="display: none; width: 100%; height: 180px; background: #f3f4f6; overflow: hidden;">
                <img id="modalImage" src="" alt="" style="width: 100%; height: 100%; object-fit: cover;">
            </div>

            {{-- Body --}}
            <div style="padding: 24px;">

                <div style="margin-bottom: 16px;">
                    <span id="modalStatus" style="display: inline-block; padding: 3px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;"></span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px 24px; font-size: 14px;">
                    <div>
                        <span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Date</span>
                        <span id="modalDate" style="color: #111827; font-weight: 500;">—</span>
                    </div>
                    <div>
                        <span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Location</span>
                        <span id="modalLocation" style="color: #111827; font-weight: 500;">—</span>
                    </div>
                    <div>
                        <span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Tickets Sold</span>
                        <span id="modalTickets" style="color: #111827; font-weight: 600;">—</span>
                    </div>
                    <div>
                        <span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Currency</span>
                        <span id="modalCurrency" style="color: #111827; font-weight: 500;">—</span>
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Description</span>
                        <span id="modalDescription" style="color: #374151; line-height: 1.5;">—</span>
                    </div>
                </div>

            </div>

            {{-- Footer --}}
            <div style="display: flex; justify-content: flex-end; gap: 8px; padding: 16px 24px; border-top: 1px solid #e5e7eb; background: #f9fafb;">
                <button type="button" onclick="closeEventModal()"
                        style="padding: 10px 18px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; font-weight: 500; color: #374151; background: white; cursor: pointer;">
                    Close
                </button>
                <a id="modalOpenBtn" href="#"
                   style="display: inline-block; padding: 10px 20px; background: #0ea5e9; color: white; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600;">
                    Open Event
                </a>
            </div>

        </div>
    </div>
</div>

{{-- FullCalendar JS --}}
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<style>
    /* ========== FullCalendar Tailwind-ish overrides ========== */
    #calendar {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }
    .fc {
        --fc-border-color: #e5e7eb;
        --fc-today-bg-color: #f0f9ff;
        --fc-event-bg-color: #0ea5e9;
        --fc-event-border-color: #0ea5e9;
        --fc-event-text-color: #ffffff;
        --fc-page-bg-color: #ffffff;
        --fc-neutral-bg-color: #f9fafb;
        font-size: 14px;
    }
    .fc .fc-toolbar-title {
        font-size: 18px !important;
        font-weight: 700 !important;
        color: #111827;
    }
    .fc .fc-button {
        background: #ffffff !important;
        border: 1px solid #e5e7eb !important;
        color: #374151 !important;
        padding: 7px 14px !important;
        border-radius: 8px !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        text-transform: capitalize !important;
        box-shadow: none !important;
    }
    .fc .fc-button:hover {
        background: #f9fafb !important;
        border-color: #d1d5db !important;
    }
    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:not(:disabled):active {
        background: #0ea5e9 !important;
        border-color: #0ea5e9 !important;
        color: #ffffff !important;
    }
    .fc .fc-daygrid-day-number,
    .fc .fc-col-header-cell-cushion {
        color: #374151;
        text-decoration: none;
        font-weight: 500;
    }
    .fc .fc-col-header-cell-cushion {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        padding: 8px 0;
    }
    .fc .fc-daygrid-event {
        border-radius: 6px;
        padding: 3px 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }
    .fc .fc-daygrid-event:hover {
        filter: brightness(0.92);
    }
    .fc .fc-event-inactive {
        background: #9ca3af !important;
        border-color: #9ca3af !important;
    }
    .fc .fc-daygrid-day.fc-day-today {
        background: #f0f9ff !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    const calendarEvents = @json($calendarEvents);

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,timeGridWeek,listMonth'
        },
        height: 'auto',
        dayMaxEvents: 3,
        events: calendarEvents,
        eventClassNames: function (arg) {
            return arg.event.extendedProps.status !== 'active' ? ['fc-event-inactive'] : [];
        },
        eventClick: function (info) {
            info.jsEvent.preventDefault();
            openEventModal(info.event);
        }
    });

    calendar.render();

    // Expose for modal
    window.__eventsById = {};
    calendarEvents.forEach(function (e) {
        window.__eventsById[e.id] = e;
    });
});

/* ================= MODAL ================= */
function openEventModal(event) {
    const ev = window.__eventsById[event.id];
    if (!ev) return;

    document.getElementById('modalTitle').textContent = ev.title || 'Event';

    // Image
    const wrapper = document.getElementById('modalImageWrapper');
    const img = document.getElementById('modalImage');
    if (ev.extendedProps.image) {
        img.src = ev.extendedProps.image;
        wrapper.style.display = 'block';
    } else {
        wrapper.style.display = 'none';
    }

    // Status badge
    const badge = document.getElementById('modalStatus');
    const isActive = ev.extendedProps.status === 'active';
    badge.textContent = (ev.extendedProps.status || '').toUpperCase();
    badge.style.background = isActive ? '#d1fae5' : '#f3f4f6';
    badge.style.color      = isActive ? '#065f46' : '#6b7280';

    // Fields
    const start = new Date(ev.start);
    const end = ev.end ? new Date(ev.end) : null;

    const dateStr = start.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    const timeStr = start.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
    const endStr  = end ? ' — ' + end.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }) : '';

    document.getElementById('modalDate').textContent = dateStr + ' · ' + timeStr + endStr;
    document.getElementById('modalLocation').textContent = ev.extendedProps.location || '—';
    document.getElementById('modalTickets').textContent = ev.extendedProps.tickets_sold ?? 0;
    document.getElementById('modalCurrency').textContent = ev.extendedProps.currency || '—';
    document.getElementById('modalDescription').textContent = ev.extendedProps.description || '—';
    document.getElementById('modalOpenBtn').href = ev.url || '#';

    document.getElementById('eventModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeEventModal() {
    document.getElementById('eventModal').style.display = 'none';
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeEventModal();
});
</script>

@include('backend.includes.Footer')