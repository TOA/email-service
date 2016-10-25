<?php
/**
 * Initialize the app
 */
$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
        'db' => [
            'host'      => "mysql",
            'user'      => "email_mysql",
            'pass'      => "mysql4Email",
            'dbname'    => "email_service",
        ]
    ]
]);
$container = $app->getContainer();

/**
 * Database dependency
 *
 * @param $container
 * @return PDO
 */
$container['DB'] = function ($container) {
    $db = $container['settings']['db'];

    $pdo = new PDO(
        "mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
        $db['user'], $db['pass']
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return $pdo;
};


/**
 * Mailer Dependency
 * @return Swift_Mailer
 */
$container['Mailer'] = function ($container) {
    $transport = new \Swift_SmtpTransport(
        getenv('MAIL_HOST'),
        getenv('MAIL_PORT')
    );
    $transport->setUsername(getenv('MAIL_USERNAME'));
    $transport->setPassword(getenv('MAIL_PASSWORD'));

    $mailer = new \Swift_Mailer($transport);
    return $mailer;
};

/**
 * Include the routes
 */
require 'app.php';