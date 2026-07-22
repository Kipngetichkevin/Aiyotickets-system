<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            width: 5.83in; /* DL size width */
            height: 4.13in; /* DL size height */
            border: 1px solid #000;
        }
        h1, h2, h3 {
            margin: 0;
        }
        .ticket-info {
            margin-top: 20px;
        }
        .terms {
            margin-top: 20px;
            font-size: 0.8em;
        }
        a {
            color: blue; /* Ensure links are visible */
            text-decoration: underline; /* Underline links */
        }
    </style>
</head>
<body>
    <h1>Event: {{ $event }}</h1>
    <h2>Venue: {{ $venue }}</h2>
    <h3>Date: {{ $date }}</h3>
    <h3>Time: {{ $time }}</h3>

<div class="ticket-info">
    <h3>ATTENDEE</h3>
    <p>{{ $attendeeName }}</p>
    <h3>TICKET TYPE</h3>
    <p>{{ $ticketType }}</p>
    <p>Don’t share with anyone</p>
    <h3>Ticket No.</h3>
    <p>{{ $ticketNumber }}</p>
</div>


    <div class="terms">
        <p>More information:</p>
        <p>This ticket is valid for the {{ $event }} edition on {{ $date }}.</p>
        <p>BY PURCHASING THIS TICKET YOU AGREE TO AIYO TICKETS AND THE EVENT ORGANIZER'S TERMS. PLEASE REVIEW THE TERMS BEFORE PURCHASING.</p>
        <p><a href="{{ $termsUrl }}" target="_blank">{{ $termsUrl }}</a></p>
        <p>NB: Please note that depending on the activities of the year, these dates might be subject to change. All information will be shared before any changes are made.</p>
        <p>All tickets are purchased via aiyotickets.com</p>
        <p>All tickets purchased for {{ $event }} are non-refundable.</p>
        <p>It is the responsibility of the ticket holder to keep their ticket secure. Each ticket is valid for a single scan only; individuals attempting to use a ticket that has already been scanned will be denied entry.</p>
    </div>
</body>
</html>