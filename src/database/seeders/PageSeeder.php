<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * The five pages the storefront links to from the footer and the About menu. They
 * ship with real copy so the site is never showing empty placeholders, and the
 * admin edits them from Content → Pages afterwards.
 */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'about',
                'title_fr' => 'La maison SARAKI',
                'title_en' => 'The SARAKI house',
                'body_fr' => "SARAKI est un atelier de mode africaine contemporaine installé à Niamey. Chaque pièce commence par une fiche de mesures et se termine par une couture finie à la main.\n\nNous travaillons le boubou, le caftan, l'ensemble wax et la tenue de soirée dans des tissus choisis un à un chez nos fournisseurs de Niamey. Les coupes restent sobres : le tissu et la main du tailleur font le travail.\n\nL'atelier reçoit sur rendez-vous, du lundi au samedi. Réservez un créneau pour une consultation, une prise de mesures ou un essayage.",
                'body_en' => "SARAKI is a contemporary African fashion atelier based in Niamey. Every piece starts as a measurement sheet and ends as a hand-finished seam.\n\nWe make boubous, caftans, wax sets and evening wear in fabrics chosen one by one from our suppliers in Niamey. The cuts stay quiet: the cloth and the tailor's hand do the work.\n\nThe atelier receives visitors by appointment, Monday to Saturday. Book a slot for a consultation, a fitting or a measurement session.",
            ],
            [
                'slug' => 'shipping',
                'title_fr' => 'Livraison',
                'title_en' => 'Shipping',
                'body_fr' => "Nous livrons partout au Niger. Les frais dépendent de la zone choisie au moment de la commande et s'affichent avant le paiement.\n\nNiamey : 2 jours ouvrés. Capitales régionales : 4 jours. Autres villes : 6 jours.\n\nLes pièces sur mesure demandent un délai de confection supplémentaire, confirmé par l'atelier après la commande. Vous recevez un message à chaque étape, de la confirmation à la remise au livreur.\n\nLa livraison est offerte à partir de 1 500 GH₵ d'achat à Niamey.",
                'body_en' => "We deliver across Niger. Fees depend on the zone chosen at checkout and are shown before payment.\n\nNiamey: 2 working days. Regional capitals: 4 days. Other cities: 6 days.\n\nMade-to-measure pieces need additional production time, which the atelier confirms after your order. You get a message at every step, from confirmation to handover to the courier.\n\nDelivery is free above GH₵1,500 within Niamey.",
            ],
            [
                'slug' => 'returns',
                'title_fr' => 'Retours et retouches',
                'title_en' => 'Returns and alterations',
                'body_fr' => "Une pièce sur mesure ne se revend pas : nous préférons la retoucher. Les retouches de coupe et d'ourlet sont offertes dans les trente jours suivant la livraison.\n\nPour une pièce de la collection en taille standard, vous pouvez la retourner non portée et non lavée dans les sept jours, avec son étiquette. Les frais de retour restent à votre charge.\n\nSignalez tout défaut de fabrication à contact@saraki.test avec une photo : nous prenons en charge le retour et la réparation.",
                'body_en' => "A made-to-measure piece cannot be resold, so we would rather alter it. Cut and hem alterations are free within thirty days of delivery.\n\nA ready-sized piece from the collection can be returned unworn and unwashed within seven days, with its label. Return shipping is at your cost.\n\nReport any manufacturing fault to contact@saraki.test with a photo: we cover the return and the repair.",
            ],
            [
                'slug' => 'privacy',
                'title_fr' => 'Confidentialité',
                'title_en' => 'Privacy',
                'body_fr' => "Nous gardons uniquement ce qui sert à vous livrer : votre nom, vos coordonnées, votre adresse de livraison et votre fiche de mesures.\n\nLes paiements sont traités par Paystack. Aucune donnée de carte n'est stockée sur nos serveurs.\n\nVos mesures ne sont visibles que par l'atelier. Vous pouvez demander la suppression de votre compte et de vos données en écrivant à contact@saraki.test : nous répondons sous trente jours.",
                'body_en' => "We keep only what is needed to deliver to you: your name, contact details, delivery address and measurement sheet.\n\nPayments are processed by Paystack. No card data is stored on our servers.\n\nYour measurements are visible to the atelier only. You can ask for your account and data to be deleted by writing to contact@saraki.test: we reply within thirty days.",
            ],
            [
                'slug' => 'terms',
                'title_fr' => 'Conditions de vente',
                'title_en' => 'Terms of sale',
                'body_fr' => "Prix affichés en cedis ghanéens (GH₵), taxes comprises. Le montant total, livraison incluse, apparaît avant la validation de la commande.\n\nUne commande non réglée est annulée après le délai indiqué au paiement et les pièces sont remises en vente.\n\nLes pièces sur mesure sont fabriquées selon les mesures que vous fournissez. Un écart entre la fiche et les mesures réelles peut demander une retouche, que nous réalisons dans les conditions décrites sur la page Retours.\n\nLe droit applicable est celui du Niger.",
                'body_en' => "Prices are shown in Ghanaian cedis (GH₵), taxes included. The total, delivery included, appears before you confirm the order.\n\nAn unpaid order is cancelled after the window shown at payment, and the pieces go back on sale.\n\nMade-to-measure pieces are cut to the measurements you provide. A gap between the sheet and your actual measurements may need an alteration, which we make under the conditions described on the Returns page.\n\nNigerien law applies.",
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(['slug' => $page['slug']], [...$page, 'is_published' => true]);
        }
    }
}
