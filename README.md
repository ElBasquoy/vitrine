# Benjamin Gonzalez — Site complet français / espagnol

## Version livrée
Site intégral avec sélecteur FR / ES, traductions, logo, galerie, onglets Matières et dernier portrait validé dans le décor de l’agence basque. Les images sont incluses dans img/. Le choix de langue est mémorisé sur le navigateur ; ?lang=fr et ?lang=es permettent de choisir la langue au chargement. La version française reste accessible sans JavaScript.

## Mise à jour de l’hébergement
1. Télécharger une sauvegarde du site actuellement en ligne.
2. Décompresser site-benjamin-moderne.zip sur votre ordinateur.
3. Transférer le CONTENU de l’archive dans le dossier public du site (souvent www, public_html ou htdocs), à la place des fichiers existants. index.html doit se trouver directement à la racine publique, sans dossier intermédiaire.
4. Transférer les dossiers assets/, img/, inc/ et var/ en conservant leur structure. Inclure les fichiers .htaccess présents à la racine et dans inc/ et var/ ; certains logiciels FTP les masquent.
5. Si inc/config.php est déjà configuré sur l’hébergement, conserver ses réglages réels d’envoi au lieu de les remplacer par les paramètres fournis. Conserver aussi un éventuel dossier vendor/ existant si SMTP est utilisé. Ne pas supprimer les autres fichiers de votre hébergement.
6. Actualiser le navigateur (Ctrl + F5). Si l’hébergeur dispose d’un cache, le purger.
7. Vérifier le portrait, les boutons FR / ES, le menu mobile, la galerie et les onglets Matières. Envoyer un message de test depuis le site en ligne puis vérifier sa réception dans la boîte destinataire.

## Hébergement et formulaire
Prévu pour Apache avec PHP 8.1 ou supérieur. Un hébergement uniquement statique ne pourra pas envoyer le formulaire contact.php. Sous Nginx, faire adapter les règles .htaccess par l’hébergeur.

Le fichier inc/config.php contient la configuration :
- destinataire : gonzalez_benjamin@outlook.com ;
- expéditeur : site@benjamin-gonzalez-design.fr, à vérifier auprès de l’hébergeur ;
- mode fourni : mail (fonction d’envoi PHP de l’hébergeur).

Le mode SMTP facultatif nécessite PHPMailer installé via Composer et les véritables paramètres SMTP. Les paramètres SMTP fournis sont des exemples, pas une configuration opérationnelle. Le dossier var/ doit être accessible en écriture par PHP pour le compteur anti-abus et les journaux. Ne pas rendre inc/ et var/ accessibles publiquement.

Le domaine indiqué dans les métadonnées, robots.txt, sitemap.xml et la configuration d’envoi est benjamin-gonzalez-design.fr. Adapter ces valeurs si le domaine de destination est différent.

## Repères pour modifier le site
- index.html : contenu français, structure, prix et références des images.
- assets/css/style.css : mise en page, couleurs et affichage mobile.
- assets/js/main.js : traductions espagnoles, choix de langue, menu, galerie, onglets et formulaire. Si un texte français change, mettre à jour sa correspondance dans le dictionnaire de traduction.
- img/portrait-benjamin-agence-basque.png : portrait actuellement affiché.
- inc/config.php : envoi des messages.

Les polices Google nécessitent une connexion Internet ; des polices de remplacement sont prévues. L’aperçu HTML autonome remis séparément sert à consulter la maquette : il ne remplace pas ces fichiers et son formulaire n’envoie pas de messages.

## Vérifications effectuées
Ressources locales et structure de l’archive vérifiées. Syntaxe JavaScript vérifiée ; bascule FR / ES vérifiée au niveau du contrôleur. Aucun envoi réel n’a été effectué. La réception des messages et le rendu sur l’hébergement doivent être vérifiés après transfert. PHP n’est pas disponible dans l’environnement de préparation pour une vérification d’exécution.
