<?php

namespace App\Console\Commands;

use App\Ai\Agents\BookingAssistant;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Exceptions\ProviderOverloadedException;
use Laravel\Ai\Exceptions\RateLimitedException;

class PollTelegramBusiness extends Command
{
    protected $signature = 'app:poll-telegram-business';

    private const TEMPORARY_AI_ERROR_REPLY = 'we will get back to you soon';

    public function handle(): int
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        if (! $token) {
            $this->error('TELEGRAM_BOT_TOKEN is not configured.');

            return Command::FAILURE;
        }

        if (! $this->bookingLink()) {
            $this->error('BOOKING_LINK must be configured with a valid booking URL.');

            return Command::FAILURE;
        }

        $baseURL = "https://api.telegram.org/bot{$token}";
        $offset = 0;

        while (true) {
            try {
                $response = Http::timeout(35)->get("{$baseURL}/getUpdates", [
                    'offset' => $offset,
                    'timeout' => 30,
                ]);

                if (! $response->successful() || ! $response->json('ok')) {
                    sleep(2);

                    continue;
                }

                foreach ($response->json('result', []) as $update) {
                    $nextOffset = $update['update_id'] + 1;

                    try {
                        if (! isset($update['business_message'])) {
                            continue;
                        }

                        $bizMsg = $update['business_message'];
                        $chatId = $bizMsg['chat']['id'];
                        $connId = $bizMsg['business_connection_id'];
                        $customerText = $bizMsg['text'] ?? '';

                        $reply = $this->generateReply($customerText);

                        Http::post("{$baseURL}/sendMessage", [
                            'business_connection_id' => $connId,
                            'chat_id' => $chatId,
                            'text' => $reply,
                        ]);
                    } catch (Exception $e) {
                        $this->error($e->getMessage());
                    } finally {
                        $offset = $nextOffset;
                    }
                }
            } catch (Exception $e) {
                $this->error($e->getMessage());
                sleep(2);
            }

            usleep(200000);
        }
    }

    private function generateReply(string $customerText): string
    {
        // Resolved via the updated instantiation pattern
        $agent = BookingAssistant::make();

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                $aiResponse = $agent->prompt(
                    $customerText,
                    provider: Lab::Gemini,
                    model: 'gemini-2.5-flash'
                );

                return $this->prepareReply((string) $aiResponse, $customerText);
            } catch (ProviderOverloadedException|RateLimitedException $e) {
                if ($attempt === 3) {
                    $this->warn($e->getMessage().' Sending fallback reply.');

                    return $this->fallbackReply($customerText);
                }

                $this->warn($e->getMessage().' Retrying...');
                sleep($attempt);
            }
        }

        return $this->fallbackReply($customerText);
    }

    private function prepareReply(string $reply, string $customerText): string
    {
        $reply = trim($reply);
        $bookingLink = $this->bookingLink();

        if ($bookingLink) {
            $reply = str_ireplace(['[GetBookingLink]', 'GetBookingLink'], $bookingLink, $reply);

            if ($this->isBookingRequest($customerText) && ! $this->containsUrl($reply)) {
                return "No problem at all! You can book an appointment here:\n{$bookingLink}";
            }
        }

        return $reply;
    }

    private function fallbackReply(string $customerText): string
    {
        $bookingLink = $this->bookingLink();

        if ($bookingLink && $this->isBookingRequest($customerText)) {
            return "No problem at all! You can book an appointment here:\n{$bookingLink}";
        }

        return self::TEMPORARY_AI_ERROR_REPLY;
    }

    private function bookingLink(): ?string
    {
        $bookingLink = trim((string) config('services.booking.link', ''));

        return filter_var($bookingLink, FILTER_VALIDATE_URL) ? $bookingLink : null;
    }

    private function isBookingRequest(string $message): bool
    {
        return preg_match('/\b(book|booking|appointment|appointement|schedule|reserve|consultation)\b/i', $message) === 1;
    }

    private function containsUrl(string $message): bool
    {
        return preg_match('/https?:\/\/\S+/i', $message) === 1;
    }
}
