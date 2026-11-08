<?php

return [

    'labels' => [
        'reference' => 'Référence',
        'gateway_reference' => 'Référence de paiement',
        'channel' => 'Moyen de paiement',
        'placed_on' => 'Passée le',
        'paid_on' => 'Payée le',
        'payment' => 'Paiement',
        'delivery' => 'Livraison',
        'notes' => 'Instructions',
        'service' => 'Prestation',
        'date' => 'Date',
        'time' => 'Heure',
        'status' => 'Statut',
        'customer' => 'Cliente',
        'admin_note' => 'Message de l’atelier',
    ],

    'footer' => [
        'help' => 'Une question sur votre commande ? Répondez simplement à cet e-mail.',
    ],

    'placed' => [
        'subject' => 'Votre commande :number est enregistrée',
        'admin_subject' => 'Nouvelle commande :number',
        'title' => 'Commande :number enregistrée',
        'admin_title' => 'Nouvelle commande',
        'intro' => 'Merci :name, nous avons bien reçu votre commande.',
        'admin_intro' => 'La commande :number vient d’être passée par :name.',
        'cta' => 'Suivre ma commande',
        'admin_cta' => 'Ouvrir la commande',
    ],

    'received' => [
        'subject' => 'Paiement reçu pour la commande :number',
        'title' => 'Paiement reçu',
        'intro' => 'Merci :name, votre paiement de :amount a été vérifié.',
        'next_step' => 'Nous préparons votre commande ; vous recevrez un message dès qu’elle entre en confection.',
        'cta' => 'Voir la commande',
    ],

    'failed' => [
        'subject' => 'Paiement non abouti pour la commande :number',
        'title' => 'Le paiement n’a pas abouti',
        'intro' => 'Le paiement de la commande :number n’a pas été confirmé. Aucun montant n’a été débité.',
        'stock_note' => 'Vos pièces restent réservées :minutes minutes. Passé ce délai, la commande sera annulée et les pièces remises en vente.',
        'cta' => 'Réessayer le paiement',
    ],

    'appointment' => [
        'requested_subject' => 'Votre demande de rendez-vous',
        'requested_admin_subject' => 'Nouvelle demande de rendez-vous',
        'requested_title' => 'Demande de rendez-vous enregistrée',
        'requested_admin_title' => 'Nouvelle demande de rendez-vous',
        'requested_intro' => 'Merci :name, nous avons bien reçu votre demande de « :service ».',
        'requested_admin_intro' => ':name demande « :service » le :date à :time.',
        'requested_pending_note' => 'Votre créneau est réservé en attente de confirmation : nous vous répondons rapidement.',
        'status_subject' => 'Votre rendez-vous : :status',
        'status_admin_subject' => 'Rendez-vous de :name : :status',
        'status_title' => 'Rendez-vous :status',
        'status_intro' => 'Bonjour :name, votre rendez-vous du :date à :time est désormais « :status ».',
        'status_admin_intro' => 'Le rendez-vous de :name du :date à :time est désormais « :status ».',
        'cta' => 'Voir mes rendez-vous',
        'admin_cta' => 'Ouvrir le rendez-vous',
    ],

    'contact' => [
        'subject' => 'Nous avons bien reçu votre message',
        'admin_subject' => 'Nouveau message de :name',
        'title' => 'Message reçu',
        'admin_title' => 'Nouveau message de :name',
        'intro' => 'Merci :name, votre message est arrivé à l’atelier : nous vous répondons sous 24 heures ouvrées.',
        'admin_intro' => 'Un message vient d’être envoyé depuis le formulaire de contact de la boutique.',
        'email' => 'E-mail',
        'phone' => 'Téléphone',
        'subject_label' => 'Sujet',
        'admin_cta' => 'Ouvrir le message',
        'reply_note' => 'Notre réponse vous parviendra à l’adresse :email.',
    ],

];
