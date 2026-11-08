<?php

return [

    'order' => [
        'pending' => 'En attente',
        'confirmed' => 'Confirmée',
        'in_production' => 'En confection',
        'ready' => 'Prête',
        'delivered' => 'Livrée',
        'cancelled' => 'Annulée',
    ],

    'payment' => [
        'unpaid' => 'Impayée',
        'pending' => 'Paiement en attente',
        'paid' => 'Payée',
        'failed' => 'Échoué',
        'refunded' => 'Remboursée',
        'partially_refunded' => 'Partiellement remboursée',
    ],

    // State of a single gateway attempt, as opposed to the order's payment status.
    'payment_state' => [
        'initialized' => 'Initié',
        'pending' => 'En attente',
        'success' => 'Réussi',
        'failed' => 'Échoué',
        'abandoned' => 'Abandonné',
        'refunded' => 'Remboursé',
        'partially_refunded' => 'Partiellement remboursé',
    ],

    'refund' => [
        'pending' => 'En cours',
        'success' => 'Effectué',
        'failed' => 'Échoué',
    ],

    'appointment' => [
        'pending' => 'En attente',
        'approved' => 'Approuvé',
        'rescheduled' => 'Reporté',
        'cancelled' => 'Annulé',
        'completed' => 'Terminé',
    ],

    'post' => [
        'draft' => 'Brouillon',
        'published' => 'Publié',
    ],

    'contact' => [
        'new' => 'Nouveau',
        'read' => 'Lu',
        'replied' => 'Répondu',
        'archived' => 'Archivé',
    ],

];
