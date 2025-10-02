
@php
    $trackingService = app(\App\Services\Forwarder\TrackingService::class);
    $tracking = $trackingService->getTracking($record);
    $statuses = $tracking['status'] ?? [];
    // Urutkan status dari terbaru ke terlama (jika belum urut)
    // usort($statuses, fn($a, $b) => strtotime($b['status_date'].' '.$b['status_time']) <=> strtotime($a['status_date'].' '.$a['status_time']));
    $activeIndex = 0; // Status paling atas dianggap aktif/terkini
@endphp

<style>
    .tracking-modal-header {
        font-size: 1.35rem;
        font-weight: 700;
        margin-bottom: 1.2rem;
        color: #1e293b;
        letter-spacing: 0.01em;
        text-align: left;
    }
    .tracking-modal-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.2rem;
        background: #f8fafc;
        border-radius: 0.75rem;
        padding: 1.2rem 1rem;
        box-shadow: 0 2px 12px 0 rgba(30,41,59,0.06);
        margin-bottom: 1.5rem;
    }
    @media (max-width: 600px) {
        .tracking-modal-grid {
            grid-template-columns: 1fr;
        }
    }
    .tracking-label {
        font-weight: 600;
        margin-bottom: 0.25rem;
        color: #2563eb;
        font-size: 1.01em;
    }
    .tracking-section {
        margin-top: 0.5rem;
    }
    .tracking-timeline-label {
        font-weight: 700;
        margin-bottom: 0.7rem;
        color: #1e293b;
        font-size: 1.08em;
    }
    .tracking-timeline-list {
        margin: 0;
        padding: 0;
        list-style: none;
        position: relative;
    }
    .tracking-timeline-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 1.1rem;
        min-height: 2.2rem;
        position: relative;
    }
    .tracking-timeline-dot-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-right: 1.25rem;
        position: relative;
        min-width: 1.5rem;
        height: 100%;
    }
    .tracking-timeline-dot {
        width: 1.35rem;
        height: 1.35rem;
        border-radius: 50%;
        border: 3px solid #2563eb;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        position: relative;
        transition: box-shadow 0.2s, border-color 0.2s, background 0.2s;
        box-shadow: 0 2px 8px 0 rgba(37,99,235,0.08);
    }
    .tracking-timeline-dot.active {
        border-color: #22c55e;
        background: #22c55e;
        animation: pulse 1.2s infinite;
    }
    .tracking-timeline-dot.upcoming {
        border-color: #cbd5e1;
        background: #fff;
    }
    .tracking-timeline-dot-inner {
        width: 0.65rem;
        height: 0.65rem;
        border-radius: 50%;
        background: #2563eb;
    }
    .tracking-timeline-dot.active .tracking-timeline-dot-inner {
        background: #fff;
    }
    .tracking-timeline-dot.upcoming .tracking-timeline-dot-inner {
        background: #cbd5e1;
    }
    .tracking-timeline-line {
        position: absolute;
        top: 1.35rem;
        left: 50%;
        transform: translateX(-50%);
        width: 4px;
        height: 57px;
        background: linear-gradient(to bottom, #2563eb 0%, #60a5fa 100%);
        z-index: 1;
        border-radius: 2px;
        opacity: 0.85;
        transition: background 0.3s, opacity 0.3s;
        box-shadow: 0 0 8px 0 rgba(37,99,235,0.10);
    }
    .tracking-timeline-line.completed {
        background: linear-gradient(to bottom, #2563eb 0%, #22c55e 100%);
        opacity: 1;
    }
    .tracking-timeline-line.upcoming {
        background: linear-gradient(to bottom, #cbd5e1 0%, #e0e7ef 100%);
        opacity: 0.7;
    }
    .tracking-timeline-content {
        display: flex;
        flex-direction: column;
        padding: 0.25rem 0.5rem 0.25rem 0;
        margin-left: 0.25rem;
        justify-content: center;
        min-width: 0;
    }
    .tracking-timeline-status {
        font-weight: 700;
        font-size: 1.08em;
        margin-bottom: 0.1rem;
        letter-spacing: 0.01em;
        transition: color 0.2s;
        cursor: pointer;
        color: #2563eb;
    }
    .tracking-timeline-status.active {
        color: #22c55e;
    }
    .tracking-timeline-status.completed {
        color: #2563eb;
        opacity: 0.8;
    }
    .tracking-timeline-status.upcoming {
        color: #cbd5e1;
    }
    .tracking-timeline-date {
        font-size: 0.93em;
        color: #6b7280;
        margin-top: 0.1rem;
    }
    .tracking-timeline-badge {
        display: inline-block;
        background: #22c55e;
        color: #fff;
        font-size: 0.8em;
        font-weight: 600;
        border-radius: 0.5em;
        padding: 0.15em 0.7em;
        margin-left: 0.5em;
        vertical-align: middle;
        box-shadow: 0 1px 4px 0 rgba(34,197,94,0.08);
    }
    .tracking-timeline-badge.completed {
        background: #2563eb;
    }
    .tracking-timeline-badge.upcoming {
        background: #cbd5e1;
        color: #64748b;
    }
    .tracking-not-found { color: #9ca3af; }
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(34,197,94,0.4); }
        70% { box-shadow: 0 0 0 10px rgba(34,197,94,0); }
        100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
    }
</style>

<div style="display: flex; flex-direction: column; gap: 1.5rem;">
    <div class="tracking-modal-grid">
        <div>
            <div class="tracking-label">Booking Number:</div>
            <div>{{ $tracking['booking_number'] ?? '-' }}</div>
            <div class="tracking-label" style="margin-top:0.5rem;">Pickup Address:</div>
            <div>{{ $tracking['pickup_address'] ?? '-' }}</div>
        </div>
        <div>
            <div class="tracking-label">Delivery Address:</div>
            <div>{{ $tracking['delivery_address'] ?? '-' }}</div>
            <div class="tracking-label" style="margin-top:0.5rem;">Cargo Ready Date:</div>
            <div>{{ $tracking['cargo_ready_date'] ?? '-' }}</div>
        </div>
    </div>
    <div class="tracking-section">
        <div class="tracking-timeline-label">Status Timeline</div>
        @if(!empty($statuses))
            <ol class="tracking-timeline-list">
                @foreach($statuses as $i => $status)
                    @php
                        $isActive = $i === $activeIndex;
                        $isCompleted = $i < $activeIndex;
                        $isUpcoming = $i > $activeIndex;
                        $dotClass = $isActive ? 'active' : ($isCompleted ? 'completed' : 'upcoming');
                        $lineClass = $isCompleted ? 'completed' : ($isUpcoming ? 'upcoming' : '');
                        $statusClass = $isActive ? 'active' : ($isCompleted ? 'completed' : 'upcoming');
                        $badge = '';
                        if ($isActive) {
                            $badge = '<span class="tracking-timeline-badge">Terkini</span>';
                        } elseif ($isCompleted && $i === count($statuses) - 1) {
                            $badge = '<span class="tracking-timeline-badge completed">Selesai</span>';
                        }
                    @endphp
                    <li class="tracking-timeline-item">
                        <div class="tracking-timeline-dot-wrap">
                            @if($i < count($statuses) - 1)
                                <div class="tracking-timeline-line {{ $lineClass }}"></div>
                            @endif
                            <div class="tracking-timeline-dot {{ $dotClass }}">
                                <div class="tracking-timeline-dot-inner"></div>
                            </div>
                        </div>
                        <div class="tracking-timeline-content">
                            <span class="tracking-timeline-status {{ $statusClass }}">{{ $status['status_name'] }} {!! $badge !!}</span>
                            <span class="tracking-timeline-date">{{ $status['status_date'] }} {{ $status['status_time'] }}</span>
                            @if(!empty($status['status_note']))
                                <span style="font-size:0.92em;color:#64748b;">{{ $status['status_note'] }}</span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <div class="tracking-not-found">Data tidak ditemukan.</div>
        @endif
    </div>
</div>
