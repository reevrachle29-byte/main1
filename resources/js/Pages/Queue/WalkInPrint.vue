<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    ticket: {
        type: Object,
        required: true,
    },
});

const printReceipt = () => {
    window.print();
};

const formatIssuedAt = () => new Date().toLocaleString([], {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
});
</script>

<template>
    <Head title="Walk-in Ticket Receipt" />

    <div class="print-sheet">
        <div class="receipt-card">
            <div class="brand">CPAC Queue</div>
            <div class="title">Walk-in Receipt</div>

            <div class="ticket-box">
                <div class="label">Ticket Number</div>
                <div class="ticket-no">#{{ ticket.queue_number }}</div>
            </div>

            <div class="details">
                <div class="row">
                    <span>Office</span>
                    <strong>{{ ticket.office }}</strong>
                </div>
                <div class="row">
                    <span>Service</span>
                    <strong>{{ ticket.service }}</strong>
                </div>
                <div class="row">
                    <span>Position</span>
                    <strong>{{ ticket.position }}</strong>
                </div>
                <div class="row">
                    <span>Est. Wait</span>
                    <strong>~{{ ticket.estimated_wait }} min</strong>
                </div>
            </div>

            <div class="tracking-block">
                <div class="label">Tracking Code</div>
                <div class="tracking-code">{{ ticket.tracking_code }}</div>
            </div>

            <div class="issued">Issued: {{ formatIssuedAt() }}</div>

            <button type="button" class="print-button" @click="printReceipt">
                Print Ticket
            </button>
        </div>
    </div>
</template>

<style scoped>
* {
    box-sizing: border-box;
}

html, body {
    margin: 0;
    padding: 0;
    background: #fff;
}

.print-sheet {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    padding: 0;
}

.receipt-card {
    width: 80mm;
    min-height: 120mm;
    padding: 12px;
    border: 1px solid #111827;
    background: #ffffff;
    color: #111827;
    font-family: Arial, Helvetica, sans-serif;
}

.brand {
    text-align: center;
    font-size: 10px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: #475569;
    font-weight: 700;
}

.title {
    margin-top: 8px;
    text-align: center;
    font-size: 18px;
    font-weight: 800;
}

.ticket-box {
    margin-top: 14px;
    background: #f8fafc;
    border: 1px dashed #475569;
    padding: 10px 8px;
    text-align: center;
}

.label {
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #475569;
    font-weight: 700;
}

.ticket-no {
    margin-top: 8px;
    font-size: 30px;
    font-weight: 900;
    line-height: 1;
}

.details {
    margin-top: 14px;
    padding: 10px 0;
    border-top: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
}

.row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    font-size: 11px;
    line-height: 1.7;
}

.row span {
    color: #475569;
    font-weight: 600;
}

.row strong {
    text-align: right;
    max-width: 60%;
    font-weight: 800;
}

.tracking-block {
    margin-top: 12px;
    text-align: center;
}

.tracking-code {
    margin-top: 4px;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 0.18em;
}

.issued {
    margin-top: 14px;
    text-align: center;
    font-size: 9px;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #475569;
}

.print-button {
    display: block;
    width: 100%;
    margin-top: 16px;
    border: none;
    border-radius: 8px;
    background: #f59e0b;
    color: #111827;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 10px 12px;
    cursor: pointer;
}

@media print {
    @page {
        margin: 0;
        size: 80mm auto;
    }

    html, body {
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
    }

    .print-sheet {
        display: block !important;
        min-height: auto !important;
        background: #fff !important;
        padding: 0 !important;
    }

    .receipt-card {
        width: 80mm !important;
        min-height: auto !important;
        margin: 0 !important;
        border: 1px solid #111827 !important;
        box-shadow: none !important;
    }

    .print-button {
        display: none !important;
    }
}
</style>
