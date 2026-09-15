@include('backend.includes.header')
@include('backend.includes.Sidebar')

<main class="app-main">
    <div style="padding: 28px 32px; width: 100%; box-sizing: border-box;">

        {{-- ============ HEADER ============ --}}
        <div style="margin-bottom: 24px;">
            <div style="font-size: 13px; color: #6b7280; margin-bottom: 6px;">
                <a href="{{ route('dashboard') }}" style="color: #6b7280; text-decoration: none;">Dashboard</a>
                <span> / </span>
                <span style="color: #374151; font-weight: 500;">Documentation</span>
            </div>
            <h1 style="font-size: 26px; font-weight: 700; color: #111827; margin: 0;">Documentation</h1>
            <p style="font-size: 14px; color: #6b7280; margin: 6px 0 0 0;">
                Quick guides on how to use each module. Click any card to see steps.
            </p>
        </div>

        {{-- ============ CARDS GRID ============ --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 18px;">

            {{-- Events --}}
            <button type="button" onclick="openDocModal('events')"
                    style="text-align: left; background: white; border: 1px solid #e5e7eb; border-radius: 14px; padding: 22px; cursor: pointer; transition: all 0.15s;"
                    onmouseover="this.style.borderColor='#0ea5e9'; this.style.boxShadow='0 4px 12px rgba(14,165,233,0.12)';"
                    onmouseout="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                <div style="width: 44px; height: 44px; background: #f0f9ff; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px;">
                    <svg width="22" height="22" fill="none" stroke="#0ea5e9" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <path d="M16 2v4M8 2v4M3 10h18" stroke-linecap="round"/>
                    </svg>
                </div>
                <h3 style="font-size: 16px; font-weight: 700; color: #111827; margin: 0 0 4px 0;">Events</h3>
                <p style="font-size: 13px; color: #6b7280; margin: 0;">Create, edit, and manage your events</p>
            </button>

            {{-- Tickets --}}
            <button type="button" onclick="openDocModal('tickets')"
                    style="text-align: left; background: white; border: 1px solid #e5e7eb; border-radius: 14px; padding: 22px; cursor: pointer; transition: all 0.15s;"
                    onmouseover="this.style.borderColor='#10b981'; this.style.boxShadow='0 4px 12px rgba(16,185,129,0.12)';"
                    onmouseout="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                <div style="width: 44px; height: 44px; background: #ecfdf5; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px;">
                    <svg width="22" height="22" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M15 5v2m0 4v2m0 4v2M5 5h14a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-3a2 2 0 0 0 0-4V7a2 2 0 0 1 2-2z"/>
                    </svg>
                </div>
                <h3 style="font-size: 16px; font-weight: 700; color: #111827; margin: 0 0 4px 0;">Tickets</h3>
                <p style="font-size: 13px; color: #6b7280; margin: 0;">Create ticket types and pricing</p>
            </button>

            {{-- Payees --}}
            <button type="button" onclick="openDocModal('payees')"
                    style="text-align: left; background: white; border: 1px solid #e5e7eb; border-radius: 14px; padding: 22px; cursor: pointer; transition: all 0.15s;"
                    onmouseover="this.style.borderColor='#6366f1'; this.style.boxShadow='0 4px 12px rgba(99,102,241,0.12)';"
                    onmouseout="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                <div style="width: 44px; height: 44px; background: #eef2ff; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px;">
                    <svg width="22" height="22" fill="none" stroke="#6366f1" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h3 style="font-size: 16px; font-weight: 700; color: #111827; margin: 0 0 4px 0;">Payees</h3>
                <p style="font-size: 13px; color: #6b7280; margin: 0;">Suppliers, personnel, and creditors</p>
            </button>

            {{-- Payouts --}}
            <button type="button" onclick="openDocModal('payouts')"
                    style="text-align: left; background: white; border: 1px solid #e5e7eb; border-radius: 14px; padding: 22px; cursor: pointer; transition: all 0.15s;"
                    onmouseover="this.style.borderColor='#f59e0b'; this.style.boxShadow='0 4px 12px rgba(245,158,11,0.12)';"
                    onmouseout="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                <div style="width: 44px; height: 44px; background: #fef3c7; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px;">
                    <svg width="22" height="22" fill="none" stroke="#f59e0b" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 19V5M5 12l7 7 7-7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h3 style="font-size: 16px; font-weight: 700; color: #111827; margin: 0 0 4px 0;">Payouts</h3>
                <p style="font-size: 13px; color: #6b7280; margin: 0;">Send money to payees</p>
            </button>

            {{-- Reports --}}
            <button type="button" onclick="openDocModal('reports')"
                    style="text-align: left; background: white; border: 1px solid #e5e7eb; border-radius: 14px; padding: 22px; cursor: pointer; transition: all 0.15s;"
                    onmouseover="this.style.borderColor='#ec4899'; this.style.boxShadow='0 4px 12px rgba(236,72,153,0.12)';"
                    onmouseout="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                <div style="width: 44px; height: 44px; background: #fce7f3; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px;">
                    <svg width="22" height="22" fill="none" stroke="#ec4899" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 3v18h18"/>
                        <path d="M7 15l4-4 4 4 5-6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h3 style="font-size: 16px; font-weight: 700; color: #111827; margin: 0 0 4px 0;">Reports</h3>
                <p style="font-size: 13px; color: #6b7280; margin: 0;">Revenue, payouts, and profit</p>
            </button>

            {{-- Calendar --}}
            <button type="button" onclick="openDocModal('calendar')"
                    style="text-align: left; background: white; border: 1px solid #e5e7eb; border-radius: 14px; padding: 22px; cursor: pointer; transition: all 0.15s;"
                    onmouseover="this.style.borderColor='#14b8a6'; this.style.boxShadow='0 4px 12px rgba(20,184,166,0.12)';"
                    onmouseout="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                <div style="width: 44px; height: 44px; background: #ccfbf1; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px;">
                    <svg width="22" height="22" fill="none" stroke="#14b8a6" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <path d="M16 2v4M8 2v4M3 10h18" stroke-linecap="round"/>
                    </svg>
                </div>
                <h3 style="font-size: 16px; font-weight: 700; color: #111827; margin: 0 0 4px 0;">Calendar</h3>
                <p style="font-size: 13px; color: #6b7280; margin: 0;">See all events at a glance</p>
            </button>

            {{-- QR Code --}}
            <button type="button" onclick="openDocModal('qrcode')"
                    style="text-align: left; background: white; border: 1px solid #e5e7eb; border-radius: 14px; padding: 22px; cursor: pointer; transition: all 0.15s;"
                    onmouseover="this.style.borderColor='#8b5cf6'; this.style.boxShadow='0 4px 12px rgba(139,92,246,0.12)';"
                    onmouseout="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                <div style="width: 44px; height: 44px; background: #ede9fe; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px;">
                    <svg width="22" height="22" fill="none" stroke="#8b5cf6" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="7" height="7"/>
                        <rect x="14" y="3" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/>
                        <path d="M14 14h3v3M21 14v7h-7" stroke-linecap="round"/>
                    </svg>
                </div>
                <h3 style="font-size: 16px; font-weight: 700; color: #111827; margin: 0 0 4px 0;">QR Code</h3>
                <p style="font-size: 13px; color: #6b7280; margin: 0;">Ticket scanning &amp; check-in</p>
            </button>

        </div>

    </div>
</main>

{{-- ============ DOC MODAL ============ --}}
<div id="docModal" style="display: none; position: fixed; inset: 0; z-index: 300;">
    <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.5);" onclick="closeDocModal()"></div>

    <div style="position: relative; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 16px;">
        <div style="width: 100%; max-width: 680px; background: white; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.15); max-height: 85vh; display: flex; flex-direction: column;">

            <div style="display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; border-bottom: 1px solid #e5e7eb;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div id="docIcon" style="width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;"></div>
                    <div>
                        <h3 id="docTitle" style="font-size: 18px; font-weight: 700; color: #111827; margin: 0;"></h3>
                        <p id="docSubtitle" style="font-size: 13px; color: #6b7280; margin: 2px 0 0 0;"></p>
                    </div>
                </div>
                <button type="button" onclick="closeDocModal()"
                        style="padding: 6px; background: none; border: none; cursor: pointer; border-radius: 6px;">
                    <svg style="width: 20px; height: 20px; color: #6b7280;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M18 6 6 18M6 6l12 12" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>

            <div id="docBody" style="padding: 24px; overflow-y: auto; font-size: 14px; line-height: 1.7; color: #374151;"></div>

            <div style="display: flex; justify-content: flex-end; padding: 16px 24px; border-top: 1px solid #e5e7eb; background: #f9fafb;">
                <button type="button" onclick="closeDocModal()"
                        style="padding: 10px 20px; background: #0ea5e9; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                    Got it
                </button>
            </div>

        </div>
    </div>
</div>

<script>
const DOCS = {
    events: {
        title: 'Events',
        subtitle: 'Create, edit, and manage events',
        color: '#0ea5e9',
        bg: '#f0f9ff',
        icon: '<svg width="20" height="20" fill="none" stroke="#0ea5e9" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18" stroke-linecap="round"/></svg>',
        body: `
            <h4 style="font-size: 14px; font-weight: 700; color: #111827; margin: 0 0 8px 0;">Create a new event</h4>
            <ol style="margin: 0 0 20px 20px; padding: 0;">
                <li>Click <b>Events → Create Event</b> in the sidebar.</li>
                <li>Fill in the event name, description, date, location.</li>
                <li>Upload a cover image and banner (optional).</li>
                <li>Pick a currency and status (<i>active</i> to appear in public listings).</li>
                <li>Click <b>Save</b>.</li>
            </ol>
            <h4 style="font-size: 14px; font-weight: 700; color: #111827; margin: 0 0 8px 0;">Manage an event</h4>
            <p style="margin: 0 0 20px 0;">Go to <b>Events → All Events</b>. Each row has Edit, View, Delete options. The event page shows tickets sold, revenue, and attendees.</p>
            <h4 style="font-size: 14px; font-weight: 700; color: #111827; margin: 0 0 8px 0;">Tips</h4>
            <ul style="margin: 0 0 0 20px; padding: 0;">
                <li>Set status to <b>inactive</b> to hide an event without deleting it.</li>
                <li>The slug is auto-generated from the name — keep names unique.</li>
            </ul>
        `
    },
    tickets: {
        title: 'Tickets',
        subtitle: 'Create ticket types and pricing',
        color: '#10b981',
        bg: '#ecfdf5',
        icon: '<svg width="20" height="20" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24"><path d="M15 5v2m0 4v2m0 4v2M5 5h14a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-3a2 2 0 0 0 0-4V7a2 2 0 0 1 2-2z"/></svg>',
        body: `
            <h4 style="font-size: 14px; font-weight: 700; color: #111827; margin: 0 0 8px 0;">Create a ticket type</h4>
            <ol style="margin: 0 0 20px 20px; padding: 0;">
                <li>Open an event from <b>Events → All Events</b>.</li>
                <li>Click the <b>Tickets</b> tab.</li>
                <li>Click <b>New Ticket</b>.</li>
                <li>Set name (e.g. <i>Early Bird, VIP, Regular</i>), price, quantity, and sales window.</li>
                <li>Save — a unique ticket number is auto-generated.</li>
            </ol>
            <h4 style="font-size: 14px; font-weight: 700; color: #111827; margin: 0 0 8px 0;">Tips</h4>
            <ul style="margin: 0 0 0 20px; padding: 0;">
                <li>Set a <b>start_date</b> and <b>end_date</b> to control when a ticket is on sale.</li>
                <li>Use multiple tiers (Early Bird, Regular, VIP) to maximise revenue.</li>
                <li>Quantity <b>0</b> means unlimited sales.</li>
            </ul>
        `
    },
    payees: {
        title: 'Payees',
        subtitle: 'Suppliers, personnel, and creditors',
        color: '#6366f1',
        bg: '#eef2ff',
        icon: '<svg width="20" height="20" fill="none" stroke="#6366f1" stroke-width="2" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        body: `
            <h4 style="font-size: 14px; font-weight: 700; color: #111827; margin: 0 0 8px 0;">Add a payee</h4>
            <ol style="margin: 0 0 20px 20px; padding: 0;">
                <li>Go to <b>Finance → All Payees</b>.</li>
                <li>Click <b>Add Payee</b>.</li>
                <li>Choose a category: <b>Supplier, Personnel, Creditor, Debtor</b>, or Other.</li>
                <li>Enter name, contact info, and payment details.</li>
                <li>Save.</li>
            </ol>
            <h4 style="font-size: 14px; font-weight: 700; color: #111827; margin: 0 0 8px 0;">Categories explained</h4>
            <ul style="margin: 0 0 20px 20px; padding: 0;">
                <li><b>Supplier</b> — vendors providing goods (tents, sound, catering).</li>
                <li><b>Personnel</b> — staff/crew hired per event (ushers, security, MC).</li>
                <li><b>Creditor</b> — anyone you owe money to.</li>
                <li><b>Debtor</b> — anyone who owes you money.</li>
            </ul>
            <h4 style="font-size: 14px; font-weight: 700; color: #111827; margin: 0 0 8px 0;">Payment details</h4>
            <p style="margin: 0;">Store the M-Pesa number for B2C payouts, or bank details (name, account, branch) for bank transfers.</p>
        `
    },
    payouts: {
        title: 'Payouts',
        subtitle: 'Send money to payees',
        color: '#f59e0b',
        bg: '#fef3c7',
        icon: '<svg width="20" height="20" fill="none" stroke="#f59e0b" stroke-width="2" viewBox="0 0 24 24"><path d="M12 19V5M5 12l7 7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        body: `
            <h4 style="font-size: 14px; font-weight: 700; color: #111827; margin: 0 0 8px 0;">Create a payout</h4>
            <ol style="margin: 0 0 20px 20px; padding: 0;">
                <li>Go to <b>Finance → Payouts</b>.</li>
                <li>Click <b>New Payout</b>.</li>
                <li>Select payee, amount, method (M-Pesa, Bank, Cash, Cheque).</li>
                <li>Optionally link a service and add a description.</li>
                <li>Save.</li>
            </ol>
            <h4 style="font-size: 14px; font-weight: 700; color: #111827; margin: 0 0 8px 0;">Approval workflow</h4>
            <ul style="margin: 0 0 20px 20px; padding: 0;">
                <li><b>Under KES 50,000</b> — auto-approved. Ready to pay.</li>
                <li><b>KES 50,000 and above</b> — requires admin approval before payment.</li>
            </ul>
            <h4 style="font-size: 14px; font-weight: 700; color: #111827; margin: 0 0 8px 0;">Statuses</h4>
            <ul style="margin: 0 0 20px 20px; padding: 0;">
                <li><b>Pending</b> — waiting for approval.</li>
                <li><b>Approved</b> — ready to send.</li>
                <li><b>Processing</b> — M-Pesa B2C in flight.</li>
                <li><b>Paid</b> — money sent, transaction ref recorded.</li>
                <li><b>Failed / Reversed</b> — something went wrong.</li>
            </ul>
            <p style="margin: 0;"><b>Tip:</b> click the ✓ icon on a row to approve, 💰 to mark as paid.</p>
        `
    },
    reports: {
        title: 'Reports',
        subtitle: 'Revenue, payouts, and profit',
        color: '#ec4899',
        bg: '#fce7f3',
        icon: '<svg width="20" height="20" fill="none" stroke="#ec4899" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 15l4-4 4 4 5-6" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        body: `
            <h4 style="font-size: 14px; font-weight: 700; color: #111827; margin: 0 0 8px 0;">Reading the report</h4>
            <p style="margin: 0 0 20px 0;">The reports page shows per-event: tickets sold, revenue, payouts, profit, and margin.</p>
            <h4 style="font-size: 14px; font-weight: 700; color: #111827; margin: 0 0 8px 0;">Filters</h4>
            <ul style="margin: 0 0 20px 20px; padding: 0;">
                <li><b>From / To</b> — restrict to a date range.</li>
                <li><b>Event</b> — show one event or all.</li>
            </ul>
            <h4 style="font-size: 14px; font-weight: 700; color: #111827; margin: 0 0 8px 0;">Export</h4>
            <p style="margin: 0;">Click <b>Export CSV</b> to download the filtered data as a spreadsheet.</p>
        `
    },
    calendar: {
        title: 'Calendar',
        subtitle: 'See all events at a glance',
        color: '#14b8a6',
        bg: '#ccfbf1',
        icon: '<svg width="20" height="20" fill="none" stroke="#14b8a6" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18" stroke-linecap="round"/></svg>',
        body: `
            <h4 style="font-size: 14px; font-weight: 700; color: #111827; margin: 0 0 8px 0;">Views</h4>
            <ul style="margin: 0 0 20px 20px; padding: 0;">
                <li><b>Month</b> — default grid view.</li>
                <li><b>Week</b> — hour-by-hour view.</li>
                <li><b>List</b> — simple line-by-line list.</li>
            </ul>
            <h4 style="font-size: 14px; font-weight: 700; color: #111827; margin: 0 0 8px 0;">Click an event</h4>
            <p style="margin: 0 0 20px 0;">Click any event to open a modal with details — date, location, tickets sold, description. Then click <b>Open Event</b> to go straight to the event page.</p>
            <h4 style="font-size: 14px; font-weight: 700; color: #111827; margin: 0 0 8px 0;">Legend</h4>
            <ul style="margin: 0 0 0 20px; padding: 0;">
                <li><b style="color:#0ea5e9;">Blue</b> — active event.</li>
                <li><b style="color:#9ca3af;">Gray</b> — inactive event.</li>
            </ul>
        `
    },
    qrcode: {
        title: 'QR Code',
        subtitle: 'Ticket scanning &amp; check-in',
        color: '#8b5cf6',
        bg: '#ede9fe',
        icon: '<svg width="20" height="20" fill="none" stroke="#8b5cf6" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3M21 14v7h-7" stroke-linecap="round"/></svg>',
        body: `
            <h4 style="font-size: 14px; font-weight: 700; color: #111827; margin: 0 0 8px 0;">Generate QR codes</h4>
            <ol style="margin: 0 0 20px 20px; padding: 0;">
                <li>Go to <b>QR Code</b> in the sidebar.</li>
                <li>Select an event.</li>
                <li>Each ticket gets its own unique QR code.</li>
                <li>Download or print for physical tickets.</li>
            </ol>
            <h4 style="font-size: 14px; font-weight: 700; color: #111827; margin: 0 0 8px 0;">Check-in at the gate</h4>
            <ol style="margin: 0 0 20px 20px; padding: 0;">
                <li>Open the scan page on a phone or tablet.</li>
                <li>Point the camera at the attendee's QR code.</li>
                <li>The system marks them as checked-in.</li>
                <li>Duplicate scans are flagged.</li>
            </ol>
            <p style="margin: 0;"><b>Coming soon:</b> dedicated mobile app for scanning.</p>
        `
    }
};

function openDocModal(key) {
    const doc = DOCS[key];
    if (!doc) return;

    document.getElementById('docTitle').textContent = doc.title;
    document.getElementById('docSubtitle').innerHTML = doc.subtitle;

    const iconBox = document.getElementById('docIcon');
    iconBox.style.background = doc.bg;
    iconBox.innerHTML = doc.icon;

    document.getElementById('docBody').innerHTML = doc.body;

    document.getElementById('docModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeDocModal() {
    document.getElementById('docModal').style.display = 'none';
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeDocModal();
});
</script>

@include('backend.includes.Footer')