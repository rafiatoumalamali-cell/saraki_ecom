<?php

return [
    'title' => 'Questions fréquentes',
    'description' => 'Tout ce qu’il faut savoir sur les commandes, les paiements Mobile Money et carte, les livraisons, les retours et les mesures sur mesure chez SARAKI.',
    'eyebrow' => 'Aide',
    'intro' => 'Vous ne trouvez pas votre réponse ? Notre équipe est à une message WhatsApp ou un appel.',
    'contact_cta' => 'Nous contacter',
    'contact_note' => 'L’atelier répond sous 24 heures ouvrées.',
    'groups' => [
        'orders' => 'Commandes et livraisons',
        'payments' => 'Paiements',
        'returns' => 'Retours et échanges',
        'tailoring' => 'Mesures et sur mesure',
        'appointments' => 'Rendez-vous atelier',
    ],
    'items' => [
        'orders' => [
            ['q' => 'Livrez-vous partout au Niger ?', 'a' => 'Oui. Nous livrons dans tout le pays : Niamey en 2 jours ouvrés, les capitales régionales en 4 et les autres villes en 6 jours ouvrés. Les frais de livraison sont calculés selon votre zone et affichés avant le paiement.'],
            ['q' => 'Comment suivre ma commande ?', 'a' => 'Dès que votre commande est confirmée, vous recevez un e-mail de confirmation. Connectez-vous à votre compte pour suivre l’avancement de la commande et son statut de livraison depuis la page « Mes commandes ».'],
            ['q' => 'Puis-je modifier ou annuler ma commande ?', 'a' => 'Oui, tant que la commande n’a pas été coupée en atelier ni expédiée, écrivez-nous sur WhatsApp ou contactez-nous depuis la page contact. Passé ce délai, nous vous proposerons un échange ou un avoir.'],
        ],
        'payments' => [
            ['q' => 'Quels moyens de paiement acceptez-vous ?', 'a' => 'Paiement par carte bancaire et Mobile Money (MTN, Vodafone, AirtelTigo) via Paystack, paiement à la livraison en espèces à Niamey, et virement bancaire sur demande. Tous les prix sont en cedis (GHS).'],
            ['q' => 'Le paiement par Mobile Money est-il sécurisé ?', 'a' => 'Oui. Paystack est un prestataire de paiement agréé ; vous recevez une demande de confirmation sur votre téléphone et la transaction est vérifiée par notre serveur avant de confirmer la commande.'],
            ['q' => 'Que se passe-t-il si mon paiement échoue ?', 'a' => 'Votre commande reste enregistrée avec le statut « en attente ». Vous pouvez réessayer le paiement depuis la page de la commande avec un autre moyen de paiement, ou nous contacter pour vous aider.'],
        ],
        'returns' => [
            ['q' => 'Puis-je retourner un article ?', 'a' => 'Les articles non portés, dans leur emballage d’origine et avec leurs étiquettes, peuvent être retournés sous 7 jours après réception. Les pièces sur mesure et les vêtements retouchés sur mesure ne sont pas éligibles au retour sauf défaut de fabrication.'],
            ['q' => 'Comment échanger une taille ?', 'a' => 'Contactez-nous avec votre numéro de commande dans les 7 jours. Nous organisons l’échange ou un avoir selon la disponibilité, et les frais de renvoi sont à votre charge sauf erreur de notre part.'],
        ],
        'tailoring' => [
            ['q' => 'Comment passer une commande sur mesure ?', 'a' => 'Choisissez un modèle, sélectionnez la coupe « sur mesure », puis renseignez vos mesures ou réservez un rendez-vous de prise de mesures à l’atelier. Nos couturiers vérifient chaque commande avant la coupe du tissu.'],
            ['q' => 'Et si mes mesures changent après la commande ?', 'a' => 'Prévenez-nous dès que possible. Si le tissu n’a pas encore été coupé, nous pouvons ajuster vos mesures sans frais. Après la coupe, des retouches peuvent être possibles selon le modèle.'],
            ['q' => 'Combien de temps prend une pièce sur mesure ?', 'a' => 'Comptez 7 à 14 jours ouvrés après validation de vos mesures, selon la complexité du modèle et la charge de l’atelier.'],
        ],
        'appointments' => [
            ['q' => 'Comment réserver un rendez-vous ?', 'a' => 'Allez sur la page Rendez-vous, choisissez le service (consultation, prise de mesures ou essayage), le créneau disponible et confirmez. Vous recevrez un e-mail de confirmation.'],
            ['q' => 'Quels sont les horaires de l’atelier ?', 'a' => 'L’atelier est ouvert du lundi au samedi, de 9 h à 18 h. Les rendez-vous sont prioritaires pour garantir votre créneau.'],
            ['q' => 'Puis-je annuler ou déplacer mon rendez-vous ?', 'a' => 'Oui. Vous pouvez annuler ou choisir un autre créneau depuis votre espace client jusqu’à 24 heures avant l’heure prévue.'],
        ],
    ],
    'jsonld' => [
        'name' => 'Questions fréquentes SARAKI',
    ],
];
