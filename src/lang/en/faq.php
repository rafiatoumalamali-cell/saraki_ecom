<?php

return [
    'title' => 'Frequently asked questions',
    'description' => 'Everything you need to know about orders, card and mobile money payments, delivery, returns and made-to-measure tailoring at SARAKI.',
    'eyebrow' => 'Help',
    'intro' => 'Can’t find your answer? Our team is a WhatsApp message or a call away.',
    'contact_cta' => 'Contact us',
    'contact_note' => 'The atelier replies within one working day.',
    'groups' => [
        'orders' => 'Orders and delivery',
        'payments' => 'Payments',
        'returns' => 'Returns and exchanges',
        'tailoring' => 'Measurements and made-to-measure',
        'appointments' => 'Atelier appointments',
    ],
    'items' => [
        'orders' => [
            ['q' => 'Do you deliver across Niger?', 'a' => 'Yes. We deliver nationwide: Niamey in 2 working days, regional capitals in 4, and other cities in 6 working days. Delivery fees depend on your zone and are shown before payment.'],
            ['q' => 'How do I track my order?', 'a' => 'Once your order is confirmed, you receive a confirmation email. Sign in to your account to follow the order status and delivery progress from the “My orders” page.'],
            ['q' => 'Can I change or cancel my order?', 'a' => 'Yes, as long as the order has not been cut in the atelier or shipped. Message us on WhatsApp or use the contact page. After that, we will offer an exchange or store credit.'],
        ],
        'payments' => [
            ['q' => 'Which payment methods do you accept?', 'a' => 'Card payments and mobile money (MTN, Vodafone, AirtelTigo) through Paystack, cash on delivery in Niamey, and bank transfer on request. All prices are in cedis (GHS).'],
            ['q' => 'Is mobile money payment secure?', 'a' => 'Yes. Paystack is a licensed payment provider; you confirm the transaction on your phone and our server verifies it with Paystack before confirming your order.'],
            ['q' => 'What happens if my payment fails?', 'a' => 'Your order stays registered as “pending”. You can retry payment from the order page with another method, or contact us and we will help.'],
        ],
        'returns' => [
            ['q' => 'Can I return an item?', 'a' => 'Unworn items in their original packaging with tags attached can be returned within 7 days of delivery. Made-to-measure pieces and garments altered to your measurements are not eligible unless they have a manufacturing defect.'],
            ['q' => 'How do I exchange a size?', 'a' => 'Contact us with your order number within 7 days. We arrange an exchange or store credit subject to availability; return shipping is at your cost unless the error was ours.'],
        ],
        'tailoring' => [
            ['q' => 'How do I place a made-to-measure order?', 'a' => 'Choose a design, select the “made-to-measure” option, then enter your measurements or book a measurement appointment at the atelier. Our tailors review every order before cutting the fabric.'],
            ['q' => 'What if my measurements change after ordering?', 'a' => 'Tell us as soon as possible. If the fabric has not been cut yet, we can adjust your measurements free of charge. After cutting, alterations may be possible depending on the design.'],
            ['q' => 'How long does a made-to-measure piece take?', 'a' => 'Allow 7 to 14 working days after your measurements are confirmed, depending on the design’s complexity and the atelier’s workload.'],
        ],
        'appointments' => [
            ['q' => 'How do I book an appointment?', 'a' => 'Go to the Appointments page, choose the service (consultation, measurement or fitting), pick an available slot and confirm. You will receive a confirmation email.'],
            ['q' => 'What are the atelier opening hours?', 'a' => 'The atelier is open Monday to Saturday, 9:00 to 18:00. Appointments take priority to guarantee your slot.'],
            ['q' => 'Can I cancel or reschedule my appointment?', 'a' => 'Yes. You can cancel or pick another slot from your account up to 24 hours before the appointment.'],
        ],
    ],
    'jsonld' => [
        'name' => 'SARAKI frequently asked questions',
    ],
];
