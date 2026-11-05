<?php

return [

    'title' => 'Mensurations',
    'intro' => 'Facultatif. Notre couturière s’en sert pour couper vos pièces confectionnées sur mesure : inutile de les répéter à chaque essayage.',
    'optional_hint' => 'Laissez vides les valeurs que vous ne connaissez pas — elles seront relevées au rendez-vous mensurations.',
    'unit' => 'cm',

    'groups' => [
        'stature' => 'Stature',
        'upper' => 'Haut du corps',
        'lower' => 'Bas du corps',
        'length' => 'Longueurs',
    ],

    'labels' => [
        'height_cm' => 'Taille (debout)',
        'chest_cm' => 'Tour de poitrine',
        'underbust_cm' => 'Tour sous-poitrine',
        'waist_cm' => 'Tour de taille',
        'hips_cm' => 'Tour de hanches',
        'shoulder_cm' => 'Largeur d’épaules',
        'back_width_cm' => 'Largeur de dos',
        'neck_cm' => 'Tour de cou',
        'arm_length_cm' => 'Longueur du bras (épaule au poignet)',
        'sleeve_length_cm' => 'Longueur de manche',
        'bicep_cm' => 'Tour de biceps',
        'inseam_cm' => 'Entrejambe',
        'thigh_cm' => 'Tour de cuisse',
        'knee_cm' => 'Tour de genou',
        'calf_cm' => 'Tour de mollet',
        'dress_length_cm' => 'Longueur de robe / jupe',
        'fit_preference' => 'Coupe préférée',
        'taken_at' => 'Date de relevé',
        'notes' => 'Notes pour la couturière',
    ],

    'hints' => [
        'height_cm' => 'Pieds nus, dos droit, talons joints.',
        'chest_cm' => 'Au niveau de la forte poitrine, sous les aisselles.',
        'underbust_cm' => 'Juste sous la poitrine, là où se place la bande du soutien-gorge.',
        'waist_cm' => 'À la partie la plus fine du torse, généralement au-dessus du nombril.',
        'hips_cm' => 'À la partie la plus forte des hanches, environ 20 cm sous la taille.',
        'shoulder_cm' => 'Dans le dos, d’une pointe d’épaule à l’autre.',
        'back_width_cm' => 'Dans le dos, entre les deux aisselles.',
        'neck_cm' => 'Autour du cou, là où tombe le col d’une chemise.',
        'arm_length_cm' => 'De la pointe de l’épaule au poignet, bras légèrement plié.',
        'sleeve_length_cm' => 'Du haut de l’épaule jusqu’à l’endroit où vous voulez terminer la manche.',
        'bicep_cm' => 'À la partie la plus forte du haut du bras.',
        'inseam_cm' => 'De l’entrejambe au sol, le long de l’intérieur de la jambe.',
        'thigh_cm' => 'À la partie la plus forte de la cuisse.',
        'knee_cm' => 'Au milieu du genou.',
        'calf_cm' => 'À la partie la plus forte du mollet.',
        'dress_length_cm' => 'De l’épaule jusqu’à l’endroit où vous voulez faire tomber l’ourlet.',
    ],

    'fit' => [
        'slim' => 'Ajustée',
        'regular' => 'Droite',
        'relaxed' => 'Ample',
    ],

    'fit_placeholder' => 'Sans préférence',
    'metadata' => 'Détails de la fiche',
    'record' => 'Enregistrer les mensurations',
    'taken_on' => 'Relevées le :date',
    'not_taken' => 'Aucune date enregistrée',
    'not_recorded' => 'Non relevée',
    'empty' => 'Aucune mensuration n’est encore enregistrée pour ce compte.',
    'progress' => ':count renseignée(s) sur :total',

];
