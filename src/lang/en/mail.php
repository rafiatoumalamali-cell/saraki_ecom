<?php

return [

    'labels' => [
        'reference' => 'Reference',
        'gateway_reference' => 'Payment reference',
        'channel' => 'Payment method',
        'placed_on' => 'Placed on',
        'paid_on' => 'Paid on',
        'payment' => 'Payment',
        'delivery' => 'Delivery',
        'notes' => 'Instructions',
        'service' => 'Service',
        'date' => 'Date',
        'time' => 'Time',
        'status' => 'Status',
        'customer' => 'Customer',
        'admin_note' => 'Message from the atelier',
    ],

    'footer' => [
        'help' => 'A question about your order? Simply reply to this email.',
    ],

    'placed' => [
        'subject' => 'Your order :number is recorded',
        'admin_subject' => 'New order :number',
        'title' => 'Order :number recorded',
        'admin_title' => 'New order',
        'intro' => 'Thank you :name, we have your order.',
        'admin_intro' => 'Order :number was just placed by :name.',
        'cta' => 'Track my order',
        'admin_cta' => 'Open the order',
    ],

    'received' => [
        'subject' => 'Payment received for order :number',
        'title' => 'Payment received',
        'intro' => 'Thank you :name, your payment of :amount has been verified.',
        'next_step' => 'We are preparing your order and will write as soon as it enters production.',
        'cta' => 'View the order',
    ],

    'failed' => [
        'subject' => 'Payment did not go through for order :number',
        'title' => 'The payment did not go through',
        'intro' => 'The payment for order :number was not confirmed. Nothing was charged.',
        'stock_note' => 'Your pieces stay reserved for :minutes minutes. After that the order is cancelled and the pieces return to stock.',
        'cta' => 'Try the payment again',
    ],

    'appointment' => [
        'requested_subject' => 'Your appointment request',
        'requested_admin_subject' => 'New appointment request',
        'requested_title' => 'Appointment request recorded',
        'requested_admin_title' => 'New appointment request',
        'requested_intro' => 'Thank you :name, we have received your request for “:service”.',
        'requested_admin_intro' => ':name requests “:service” on :date at :time.',
        'requested_pending_note' => 'The slot is held pending confirmation: we will get back to you shortly.',
        'status_subject' => 'Your appointment: :status',
        'status_admin_subject' => 'Appointment for :name: :status',
        'status_title' => 'Appointment :status',
        'status_intro' => 'Hello :name, your appointment on :date at :time is now “:status”.',
        'status_admin_intro' => 'The appointment for :name on :date at :time is now “:status”.',
        'cta' => 'View my appointments',
        'admin_cta' => 'Open the appointment',
    ],

    'contact' => [
        'subject' => 'We have received your message',
        'admin_subject' => 'New message from :name',
        'title' => 'Message received',
        'admin_title' => 'New message from :name',
        'intro' => 'Thank you :name, your message has reached the atelier: we reply within one working day.',
        'admin_intro' => 'A message was just sent from the storefront contact form.',
        'email' => 'Email',
        'phone' => 'Phone',
        'subject_label' => 'Subject',
        'admin_cta' => 'Open the message',
        'reply_note' => 'Our reply will reach you at :email.',
    ],

];
