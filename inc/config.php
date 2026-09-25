<?php
/**
 * config.php — le seul fichier à modifier pour l'envoi des messages.
 * Il est placé dans inc/ et rendu inaccessible depuis le navigateur
 * par le fichier inc/.htaccess. Ne le déplacez pas à la racine.
 */

return [

    // ── Destination ──────────────────────────────────────────────
    // Votre boîte : c'est ici que les messages arrivent.
    'destinataire'  => 'gonzalez_benjamin@outlook.com',

    // Adresse d'expédition. Elle DOIT appartenir à votre nom de domaine,
    // sinon les messages seront classés en indésirables.
    'expediteur'    => 'site@benjamin-gonzalez-design.fr',
    'nom_site'      => 'benjamin-gonzalez-design.fr',

    // ── Mode d'envoi ─────────────────────────────────────────────
    // 'mail' : fonction native de l'hébergeur, aucune installation.
    // 'smtp' : via PHPMailer, nettement plus fiable. Installation :
    //          composer require phpmailer/phpmailer
    'mode'          => 'mail',

    'smtp' => [
        'hote'      => 'smtp.votre-hebergeur.fr',
        'port'      => 587,
        'utilisateur' => 'site@benjamin-gonzalez-design.fr',
        'mot_de_passe' => 'a-remplacer',
        'chiffrement'  => 'tls',   // 'tls' ou 'ssl'
    ],

    // ── Protection contre les abus ───────────────────────────────
    // Nombre maximum de messages autorisés par adresse IP,
    // et durée de la fenêtre d'observation en secondes.
    'limite_messages' => 5,
    'limite_fenetre'  => 3600,

    // Délai minimum entre l'affichage du formulaire et son envoi.
    // Un robot répond instantanément, un humain met plusieurs secondes.
    'delai_minimum'   => 3,

    // Dossier d'écriture pour le compteur anti-abus.
    // Doit être accessible en écriture par le serveur (droits 755 ou 775).
    'dossier_travail' => __DIR__ . '/../var',

    // Journalisation des erreurs d'envoi dans var/erreurs.log
    'journaliser'     => true,
];
