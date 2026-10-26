#! /usr/bin/env php
<?php

/**
 * mail.php
 * @Auteur: Christophe Dufour
 * 
 * Mail permet l'envoi d'un email simple et rapide en ligne de commande
 */

// Est-on bien en mode console ?
if(php_sapi_name() !== "cli") {
    fwrite(STDERR, basename(__FILE__) . ": Ce script ne peut être utilisé qu'en ligne de commande." . PHP_EOL);
    exit(1);
}

/**
 * Constantes
 * ----------
 */

// Version de l'application
define("VERSION", "1.0");
// Dossier racine
define("ROOT_DIR", dirname(__DIR__));
// Pour le serveur smtp
define("HOST", "relay.proximus.be");
define("USER", "todolist@proximus.be");
define("PASSWORD", "V7mYI0rYPhmT8Pp");

// Autoload de Composer
require ROOT_DIR . str_replace('/', DIRECTORY_SEPARATOR, "/vendor/autoload.php");

// Classe à utiliser
use Blackfox\GetOptions\GetOptions;
use Blackfox\Simplemail\Simplemail;

// Variables
$shortOptions = "t:s:j:";
$longOptions = [
    "help",
    "join:",
    "to:",
    "subject:",
    "version"
];

$mail = new Simplemail(HOST, USER, PASSWORD, false);
$mail->setForm("todolist@proximus.be", "Todolist");

/**
 * Afficher l'aide et quitter
 * 
 * @return void
 */
function usage(): void
{
    echo "mail.php
Utilisation: mail --to DESTINATAIRE --subject \"SUJET\" \"MESSAGE\"
Envoie un mail au DESTINATAIRE avec comme sujet SUJET comme message MESSAGE

Options requise:
    -t, --to        : le destinataire du message.
    -s, --subject   : le sujet du message.

Options:
    -j, --join      : Pièce jointe à ajouter.
    --help          : afficher l'aide et quitter.
    --version       : afficher des informations de version et quitter.
";
    exit(0);
}

/**
 * Afficher des informations de version et quitter
 * 
 * @return void
 */
function version(): void
{
    echo "mail.php version " . VERSION . "
Licence GPLv3+ : GNU GPL version 3 ou ultérieure <https://gnu.org/licenses/gpl.html>
Logiciel libre : vous êtes libre de le modifier ou de le redistribuer.
Il n'y a AUCUNE GARANTIE, dans les limites permises par la loi.

Ecrit par Christophe Dufour
";
    exit(0);
}

try {
    // Récupération des options
    $options = GetOptions::options($shortOptions, $longOptions);
    // Récupération des arguments positionnels
    $argPos = GetOptions::positional();

    foreach($options as $opts) {
        if(array_key_exists("help", $opts)) {
            usage();
        }

        if(array_key_exists("version", $opts)) {
            version();
        }

        foreach($opts as $opt => $value) {
            switch($opt) {
                case "t":
                case "to": {
                    $to = isset($opts['t']) ? $opts['t'] : $opts['to'];

                    if(!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception("Format d'adresse email invalide.");
                    }

                    $mail->addAddress($to);
                } break;

                case "s":
                case "subject": {
                    if(isset($subject)) {
                        throw new Exception("L'option suivante doit être unique -- subject.");
                    }

                    $subject = isset($opts['s']) ? $opts['s'] : $opts['subject'];
                } break;

                case "j":
                case "join": {
                    //
                } break;
            }
        }
    }

    if(!isset($to)) {
        throw new Exception("Options requise manquante -- to");
    }

    if(!isset($subject)) {
        throw new Exception("Options requise manquante -- subject");
    }

    if(empty($argPos)) {
        throw new Exception("Veuillez entrer un message à envoyer.");
    }

    $msg = implode(' ', $argPos);
    $mail->send($subject, $msg);

}
catch(Exception $e) {
    fwrite(STDERR, basename(__FILE__) . ": " . $e->getMessage() . PHP_EOL);
    fwrite(STDERR, "Saisissez « mail.php --help » pour plus d'informations." . PHP_EOL);
    exit(1);
}

// Fin du script
exit(0);
