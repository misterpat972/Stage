<?php
/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d’installation. Vous n’avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en « wp-config.php » et remplir les
 * valeurs.
 *
 * Ce fichier contient les réglages de configuration suivants :
 *
 * Réglages MySQL
 * Préfixe de table
 * Clés secrètes
 * Langue utilisée
 * ABSPATH
 *
 * @link https://fr.wordpress.org/support/article/editing-wp-config-php/.
 *
 * @package WordPress
 */

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define( 'DB_NAME', 'djiboute_Patrick' );

/** Utilisateur de la base de données MySQL. */
define( 'DB_USER', 'djiboute_root' );

/** Mot de passe de la base de données MySQL. */
define( 'DB_PASSWORD', 'auuJza9I' );

/** Adresse de l’hébergement MySQL. */
define( 'DB_HOST', 'localhost' );

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/**
 * Type de collation de la base de données.
 * N’y touchez que si vous savez ce que vous faites.
 */
define( 'DB_COLLATE', '' );

/**#@+
 * Clés uniques d’authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clés secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n’importe quel moment, afin d’invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'PVT(a(=-AAK.=D)uY9;J7`NRY$DE.qxaq5~Dw7u%n2`/bWo=|3fKc^*$qwx57s:5' );
define( 'SECURE_AUTH_KEY',  'm4b|$~^/tUgq1vk%-&1b&MSit!D(l&S1A*IK 1zd>i!2F#6L50Dd].sR>zRWCeaw' );
define( 'LOGGED_IN_KEY',    'Glt<4e#Dje?+8;oH8liFO)MKYNiDvyPw`MthEj)tE_lg3p.HFeFqP>P5%jAQ=r{X' );
define( 'NONCE_KEY',        '$T5+RJN<h$6*BvxZ2J}E5{.x=Sm2Z-Z|*iNCZtkJf)MO>ClImH-86FFfN ba]lRM' );
define( 'AUTH_SALT',        '@%A.#S[zbnX~dkGbHsH*mvsqLzl0P4liqQA- ]{>XX& q~YW|e{^e-I}>fiB2I+X' );
define( 'SECURE_AUTH_SALT', ',+8IvzI)_*3G~Z;>53%]i[}!FLmwB?r$$=V$]^W%;,ka=83I8&eQI2S3I]HNrjFO' );
define( 'LOGGED_IN_SALT',   'nCI3su*F7v80M8i@!ZA84RX]S{^`_AosT4Id-rAz4Z7@#W~QtR;GsoH7`V/Z>uHW' );
define( 'NONCE_SALT',       ' +r_|_AGN7h^$d#@frz=6k/K~&8Br,|J-O*RS|8:V|t{lg)Lkm:4$<>7zBCA?A.;' );
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique.
 * N’utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés !
 */
$table_prefix = 'patest_';

/**
 * Pour les développeurs : le mode déboguage de WordPress.
 *
 * En passant la valeur suivante à "true", vous activez l’affichage des
 * notifications d’erreurs pendant vos essais.
 * Il est fortement recommandé que les développeurs d’extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de
 * développement.
 *
 * Pour plus d’information sur les autres constantes qui peuvent être utilisées
 * pour le déboguage, rendez-vous sur le Codex.
 *
 * @link https://fr.wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* C’est tout, ne touchez pas à ce qui suit ! Bonne publication. */

/** Chemin absolu vers le dossier de WordPress. */
if ( ! defined( 'ABSPATH' ) )
  define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once( ABSPATH . 'wp-settings.php' );
