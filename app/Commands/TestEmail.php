<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestEmail extends BaseCommand
{
    protected $group = 'Lume';
    protected $name = 'email:test';
    protected $description = 'Send a test email using current SMTP/.env configuration.';
    protected $usage = 'email:test <recipient_email>';
    protected $arguments = [
        'recipient_email' => 'Email address that should receive the test message.',
    ];

    public function run(array $params)
    {
        $to = strtolower(trim((string) ($params[0] ?? '')));
        if ($to === '' || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            CLI::error('Usage: php spark email:test you@example.com');
            return;
        }

        $email = service('email');
        $cfg = config('Email');

        if ($cfg->fromEmail !== '') {
            $email->setFrom($cfg->fromEmail, $cfg->fromName);
        }

        $email->setTo($to);
        $email->setSubject('Lume SMTP Test');
        $email->setMessage(
            "This is a test email from Lume.\n\n" .
            'Sent at: ' . date('Y-m-d H:i:s') . "\n" .
            'Environment: ' . ENVIRONMENT . "\n"
        );

        try {
            $sent = $email->send();
            if ($sent) {
                CLI::write('Test email sent successfully to ' . $to, 'green');
                return;
            }

            CLI::error('Email send failed. Debug output below:');
            CLI::write((string) $email->printDebugger(['headers', 'subject']));
        } catch (\Throwable $e) {
            CLI::error('Email exception: ' . $e->getMessage());
        }
    }
}
