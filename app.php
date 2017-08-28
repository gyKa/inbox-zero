<?php

use Dotenv\Dotenv;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once __DIR__ . '/vendor/autoload.php';

$log = new Logger('APP');
$log->pushHandler(new StreamHandler('inbox-zero.log', Logger::WARNING));
$log->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$environment = new Dotenv(__DIR__);
$environment->load();
$environment->required(['USERNAME', 'PASSWORD'])->notEmpty();

$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$username = getenv('USERNAME');
$password = getenv('PASSWORD');

$inbox = imap_open($hostname, $username.'s', $password);

if ($inbox === false) {
    $log->addError('cannot connect to gmail', [imap_last_error()]);

    return;
}

$emails = imap_search($inbox, 'ALL');

$log->addInfo(
    sprintf('found %s emails', count($emails))
);

if ($emails) {
    rsort($emails); // put the newest emails on top

    foreach ($emails as $email_number) {
        $overview = imap_fetch_overview($inbox, $email_number, 0);

        $log->addDebug(
            sprintf('status - %s', $overview[0]->seen ? 'read' : 'unread'),
            [$email_number]
        );

        $log->addDebug(
            sprintf('from - %s', $overview[0]->from),
            [$email_number]
        );

        $log->addDebug(
            sprintf('date - %s', $overview[0]->date),
            [$email_number]
        );

        $log->addDebug(
            sprintf('udate - %s', $overview[0]->udate),
            [$email_number]
        );
    }
}

imap_close($inbox);
