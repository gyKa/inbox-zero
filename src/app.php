<?php

error_reporting(0);

use Dotenv\Dotenv;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$log = new Logger('APP');
$log->pushHandler(
    new StreamHandler(
        join(DIRECTORY_SEPARATOR,[dirname(__DIR__), 'log', 'warning.log']),
        Logger::WARNING)
);
$log->pushHandler(
    new StreamHandler(
        join(DIRECTORY_SEPARATOR,[dirname(__DIR__), 'log', 'info.log']),
        Logger::INFO)
);
$log->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$environment = new Dotenv(dirname(__DIR__));
$environment->load();
$environment->required(['USERNAME', 'PASSWORD'])->notEmpty();

$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$username = getenv('USERNAME');
$password = getenv('PASSWORD');

$inbox = imap_open($hostname, $username, $password);

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

    $emailsForDeletion = [];

    $nowDate = new DateTime();
    $emailDate = new DateTime();

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

        $emailDate->setTimestamp($overview[0]->udate);
        $difference = $emailDate->diff($nowDate);
        $differenceInDays = (int)$difference->format('%a');

        if ($overview[0]->seen && $differenceInDays > 14) {
            $log->addDebug(
                sprintf('diff - %s days', $differenceInDays),
                [$email_number]
            );

            $emailsForDeletion[] = $email_number;
        }
    }

    foreach ($emailsForDeletion as $email_number) {
        $overview = imap_fetch_overview($inbox, $email_number, 0);

        $log->addInfo(
            sprintf('preparing for deletion an email from %s and having date %s', $overview[0]->from, $overview[0]->date),
            [$email_number]
        );
    }
}

imap_close($inbox);
