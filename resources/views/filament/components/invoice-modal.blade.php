@php
    $invoice = $invoice ?? null;
@endphp

<style>
.invoice-modal-header {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 1.2rem;
    color: #1e293b;
    letter-spacing: 0.01em;
}
.invoice-modal-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1.2rem;
    background: #f8fafc;
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: 0 2px 12px 0 rgba(30,41,59,0.06);
}
.invoice-modal-table th, .invoice-modal-table td {
    padding: 0.7rem 1rem;
    text-align: left;
}
.invoice-modal-table th {
    background: #2563eb;
    color: #fff;
    font-weight: 600;
    font-size: 1em;
}
.invoice-modal-table tr:not(:last-child) td {
    border-bottom: 1px solid #e5e7eb;
}
.invoice-badge {
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
.invoice-modal-info {
    display: flex;
    flex-wrap: wrap;
    gap: 1.2rem;
    margin-bottom: 1.2rem;
}
.invoice-modal-info > div {
    min-width: 180px;
}
.invoice-modal-label {
    font-weight: 600;
    color: #2563eb;
    font-size: 1.01em;
    margin-bottom: 0.2em;
}
.invoice-modal-value {
    color: #1e293b;
    font-size: 1.05em;
}
.invoice-modal-notfound {
    color: #9ca3af;
    text-align: center;
    margin: 2em 0;
}
.invoice-table-mobile { display: none; }
@media (max-width: 1020px) {
    .invoice-modal-table { display: none; }
    .invoice-table-mobile { display: block; }
    .invoice-table-mobile-row {
        background: #f8fafc;
        border-radius: 0.75rem;
        box-shadow: 0 2px 12px 0 rgba(30,41,59,0.06);
        margin-bottom: 1.1rem;
        padding: 1rem 0.7rem;
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }
    .invoice-table-mobile-label {
        display: inline-block;
        min-width: 110px;
        font-weight: 600;
        color: #2563eb;
        font-size: 0.98em;
        margin-right: 0.7em;
    }
    .invoice-table-mobile-row > div {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.98em;
    }
}
@media (prefers-color-scheme: dark) {
    .invoice-table-mobile-row { background: #1e293b; box-shadow: 0 2px 12px 0 rgba(30,41,59,0.18); }
    .invoice-table-mobile-label { color: #60a5fa; }
}
@media (prefers-color-scheme: dark) {
    .invoice-modal-header { color: #f1f5f9; }
    .invoice-modal-table { background: #1e293b; box-shadow: 0 2px 12px 0 rgba(30,41,59,0.18); }
    .invoice-modal-table th { background: #2563eb; color: #fff; }
    .invoice-modal-table td { color: #f1f5f9; }
    .invoice-modal-table tr:not(:last-child) td { border-bottom: 1px solid #334155; }
    .invoice-badge { background: #22d3ee; color: #0f172a; }
    .invoice-modal-label { color: #60a5fa; }
    .invoice-modal-value { color: #f1f5f9; }
    .invoice-modal-notfound { color: #64748b; }
}
</style>

<div>
    @if($invoice && !empty($invoice['data']))
        @php $inv = $invoice['data'][0]; @endphp
        <div class="invoice-modal-info">
            <div>
                <div class="invoice-modal-label">Booking No</div>
                <div class="invoice-modal-value">{{ $inv['booking_no'] }}</div>
            </div>
            <div>
                <div class="invoice-modal-label">Invoice No</div>
                <div class="invoice-modal-value">{{ $inv['invoice_no'] }}</div>
            </div>
            <div>
                <div class="invoice-modal-label">Status</div>
                <div class="invoice-modal-value">
                    <span class="invoice-badge">{{ $inv['status'] }}</span>
                </div>
            </div>
            <div>
                <div class="invoice-modal-label">Due Date</div>
                <div class="invoice-modal-value">{{ $inv['due_date'] }}</div>
            </div>
            <div>
                <div class="invoice-modal-label">Invoice Date</div>
                <div class="invoice-modal-value">{{ $inv['invoice_date'] }}</div>
            </div>
            <div>
                <div class="invoice-modal-label">Currency</div>
                <div class="invoice-modal-value">{{ $inv['currency'] }}</div>
            </div>
        </div>
        <div class="invoice-table-mobile">
            @foreach($inv['data_detail'] as $detail)
                <div class="invoice-table-mobile-row">
                    <div><span class="invoice-table-mobile-label">Freight Element</span><span>{{ $detail['freight_element_name'] }}</span></div>
                    <div><span class="invoice-table-mobile-label">Basis</span><span>{{ $detail['basis_name'] }}</span></div>
                    <div><span class="invoice-table-mobile-label">Container</span><span>{{ $detail['container_type'] }}</span></div>
                    <div><span class="invoice-table-mobile-label">Qty</span><span>{{ $detail['qty'] }}</span></div>
                    <div><span class="invoice-table-mobile-label">Amount</span><span>{{ number_format($detail['amount'], 0, ',', '.') }}</span></div>
                    <div><span class="invoice-table-mobile-label">Tax</span><span>{{ $detail['tax'] }}</span></div>
                    <div><span class="invoice-table-mobile-label">Total</span><span>{{ number_format($detail['total'], 0, ',', '.') }}</span></div>
                    <div><span class="invoice-table-mobile-label">Remark</span><span>{{ $detail['remark'] }}</span></div>
                </div>
            @endforeach
        </div>
        <div style="overflow-x:auto;">
            <table class="invoice-modal-table">
                <thead>
                    <tr>
                        <th>Freight Element</th>
                        <th>Basis</th>
                        <th>Container</th>
                        <th>Qty</th>
                        <th>Amount</th>
                        <th>Tax</th>
                        <th>Total</th>
                        <th>Remark</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inv['data_detail'] as $detail)
                        <tr>
                            <td>{{ $detail['freight_element_name'] }}</td>
                            <td>{{ $detail['basis_name'] }}</td>
                            <td>{{ $detail['container_type'] }}</td>
                            <td>{{ $detail['qty'] }}</td>
                            <td>{{ number_format($detail['amount'], 0, ',', '.') }}</td>
                            <td>{{ $detail['tax'] }}</td>
                            <td>{{ number_format($detail['total'], 0, ',', '.') }}</td>
                            <td>{{ $detail['remark'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="invoice-modal-notfound">Invoice tidak ditemukan.</div>
    @endif
</div>
