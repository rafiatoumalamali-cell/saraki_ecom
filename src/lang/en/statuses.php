<?php

return [

    'order' => [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'in_production' => 'In production',
        'ready' => 'Ready',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ],

    'payment' => [
        'unpaid' => 'Unpaid',
        'pending' => 'Pending payment',
        'paid' => 'Paid',
        'failed' => 'Failed',
        'refunded' => 'Refunded',
        'partially_refunded' => 'Partially refunded',
    ],

    // State of a single gateway attempt, as opposed to the order's payment status.
    'payment_state' => [
        'initialized' => 'Started',
        'pending' => 'Pending',
        'success' => 'Successful',
        'failed' => 'Failed',
        'abandoned' => 'Abandoned',
        'refunded' => 'Refunded',
        'partially_refunded' => 'Partially refunded',
    ],

    'refund' => [
        'pending' => 'In progress',
        'success' => 'Done',
        'failed' => 'Failed',
    ],

    'appointment' => [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rescheduled' => 'Rescheduled',
        'cancelled' => 'Cancelled',
        'completed' => 'Completed',
    ],

    'post' => [
        'draft' => 'Draft',
        'published' => 'Published',
    ],

    'contact' => [
        'new' => 'New',
        'read' => 'Read',
        'replied' => 'Replied',
        'archived' => 'Archived',
    ],

];
