<?php
/**
 * contact.php — réception du formulaire de contact.
 *
 * Ce fichier n'a normalement pas besoin d'être modifié :
 * tous les réglages se trouvent dans inc/config.php.
 *
 * Protections en place :
 *   - champ piège invisible (honeypot)
 *   - délai minimum de remplissage
 *   - limitation du nombre d'envois par adresse IP
 *   - validation stricte et blocage de l'injection d'en-têtes
 *   - aucun détail technique renvoyé au visiteur en cas d'erreur
 */

declare(strict_types=1);

// Aucune erreur PHP ne doit s'afficher dans la réponse.
ini_set('display_errors', '0');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');

$config = require __DIR__ . '/inc/config.php';

/** Réponse d'erreur, sans jamais exposer la cause technique. */
function refuser(int $code, string $message): never {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

/** Écrit une ligne dans le journal si la journalisation est active. */
function journaliser(array $config, string $texte): void {
    if (empty($config['journaliser'])) return;
    $dossier = $config['dossier_travail'];
    if (!is_dir($dossier)) @mkdir($dossier, 0755, true);
    @file_put_contents(
        $dossier . '/erreurs.log',
        '[' . date('Y-m-d H:i:s') . '] ' . $texte . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
}

// ── 1. Méthode ───────────────────────────────────────────────────
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    refuser(405, 'Méthode non autorisée.');
}

// ── 2. Champ piège : un robot le remplit, pas un humain ──────────
if (trim((string)($_POST['site'] ?? '')) !== '') {
    // On répond « envoyé » pour ne pas renseigner le robot.
    echo json_encode(['ok' => true]);
    exit;
}

// ── 3. Délai de remplissage ──────────────────────────────────────
$ouvert = (int)($_POST['ouvert_a'] ?? 0);
if ($ouvert > 0) {
    $secondes = (int)((microtime(true) * 1000 - $ouvert) / 1000);
    if ($secondes < (int)$config['delai_minimum']) {
        refuser(429, 'Message envoyé trop vite. Merci de réessayer.');
    }
}

// ── 4. Limitation par adresse IP ─────────────────────────────────
$ip = (string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
$dossier = $config['dossier_travail'];
if (!is_dir($dossier)) @mkdir($dossier, 0755, true);

$compteur = $dossier . '/envois-' . hash('sha256', $ip) . '.txt';
$maintenant = time();
$horodatages = [];

if (is_readable($compteur)) {
    $brut = (string)@file_get_contents($compteur);
    foreach (explode(',', $brut) as $valeur) {
        $valeur = (int)$valeur;
        if ($valeur > $maintenant - (int)$config['limite_fenetre']) {
            $horodatages[] = $valeur;
        }
    }
}
if (count($horodatages) >= (int)$config['limite_messages']) {
    refuser(429, 'Trop de messages envoyés. Réessayez dans une heure ou appelez-moi.');
}
$horodatages[] = $maintenant;
@file_put_contents($compteur, implode(',', $horodatages), LOCK_EX);

// ── 5. Lecture et validation des champs ──────────────────────────
/** Nettoie une valeur reçue : chaîne, sans espaces superflus, longueur bornée. */
function champ(string $nom, int $max): string {
    $valeur = (string)($_POST[$nom] ?? '');
    $valeur = trim($valeur);
    if (!mb_check_encoding($valeur, 'UTF-8')) return '';
    return mb_substr($valeur, 0, $max);
}

$nom     = champ('nom', 120);
$email   = champ('email', 180);
$tel     = champ('tel', 40);
$type    = champ('type', 80);
$message = champ('message', 5000);

if ($nom === '' || $email === '' || $message === '') {
    refuser(422, 'Nom, e-mail et message sont nécessaires.');
}
if (mb_strlen($message) < 10) {
    refuser(422, 'Merci de décrire un peu votre projet.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    refuser(422, "L'adresse e-mail n'est pas valide.");
}
// Blocage de l'injection d'en-têtes : aucun retour à la ligne toléré.
foreach ([$nom, $email, $tel, $type] as $valeur) {
    if (preg_match('/[\r\n\0]/', $valeur)) {
        refuser(422, 'Contenu invalide.');
    }
}
if ($type === '') $type = 'Non précisé';

// ── 6. Composition du message ────────────────────────────────────
$sujet = 'Nouveau message du site — ' . $nom;
$corps = "Nom       : $nom\n"
       . "E-mail    : $email\n"
       . 'Téléphone : ' . ($tel !== '' ? $tel : '—') . "\n"
       . "Projet    : $type\n"
       . 'Reçu le   : ' . date('d/m/Y à H:i') . "\n"
       . str_repeat('-', 46) . "\n\n"
       . $message . "\n";

// ── 7. Envoi ─────────────────────────────────────────────────────
try {
    if ($config['mode'] === 'smtp') {
        require __DIR__ . '/vendor/autoload.php';

        $courriel = new PHPMailer\PHPMailer\PHPMailer(true);
        $courriel->isSMTP();
        $courriel->Host       = $config['smtp']['hote'];
        $courriel->SMTPAuth   = true;
        $courriel->Username   = $config['smtp']['utilisateur'];
        $courriel->Password   = $config['smtp']['mot_de_passe'];
        $courriel->SMTPSecure = $config['smtp']['chiffrement'] === 'ssl'
            ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $courriel->Port    = (int)$config['smtp']['port'];
        $courriel->CharSet = 'UTF-8';

        $courriel->setFrom($config['expediteur'], $config['nom_site']);
        $courriel->addAddress($config['destinataire']);
        $courriel->addReplyTo($email, $nom);
        $courriel->Subject = $sujet;
        $courriel->Body    = $corps;
        $courriel->send();

    } else {
        $entetes = [
            'From: ' . $config['nom_site'] . ' <' . $config['expediteur'] . '>',
            'Reply-To: ' . $nom . ' <' . $email . '>',
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ];
        $envoye = mail(
            $config['destinataire'],
            '=?UTF-8?B?' . base64_encode($sujet) . '?=',
            $corps,
            implode("\r\n", $entetes),
            '-f' . $config['expediteur']
        );
        if (!$envoye) {
            throw new RuntimeException('La fonction mail() a renvoyé false.');
        }
    }
} catch (Throwable $e) {
    journaliser($config, 'Envoi impossible : ' . $e->getMessage());
    refuser(500, "L'envoi a échoué. Écrivez-moi directement par e-mail.");
}

echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
