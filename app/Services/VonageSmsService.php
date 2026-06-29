<?php

namespace App\Services;

use App\Contracts\SmsServiceInterface;
use RuntimeException;
use Vonage\Client;
use Vonage\Client\Credentials\Basic;
use Vonage\SMS\Message\SMS;

class VonageSmsService implements SmsServiceInterface
{
    public function send(string $phone, string $message): void
    {
        $key = config('services.vonage.key');
        $secret = config('services.vonage.secret');
        $from = config('services.vonage.from');

        if (! $key || ! $secret || ! $from) {
            throw new RuntimeException('Vonage credentials are not configured.');
        }

        $client = new Client(new Basic($key, $secret));
        $response = $client->sms()->send(new SMS($phone, $from, $message));
        $sentMessage = $response->current();

        if ($sentMessage->getStatus() !== 0) {
            throw new RuntimeException('Vonage SMS failed with status '.$sentMessage->getStatus().'.');
        }
    }
}
