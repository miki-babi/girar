<?php

namespace App\Console\Commands;

use App\Ai\Agents\BookingAssistant;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Exceptions\ProviderOverloadedException;
use Laravel\Ai\Exceptions\RateLimitedException;

class PollTelegramBusiness extends Command
{
    protected $signature = 'app:poll-telegram-business';

    public function handle(): int
    {
        Log::info('PollTelegramBusiness command started');

        $token = env('TELEGRAM_BOT_TOKEN');
        if (! $token) {
            Log::error('TELEGRAM_BOT_TOKEN is not configured');
            $this->error('TELEGRAM_BOT_TOKEN is not configured.');

            return Command::FAILURE;
        }

        Log::info('TELEGRAM_BOT_TOKEN is configured');

        if (! $this->bookingLink()) {
            Log::error('BOOKING_LINK must be configured with a valid booking URL');
            $this->error('BOOKING_LINK must be configured with a valid booking URL.');

            return Command::FAILURE;
        }

        Log::info('BOOKING_LINK is configured', ['link' => $this->bookingLink()]);

        $baseURL = "https://api.telegram.org/bot{$token}";
        $offset = 0;

        Log::info('Starting polling loop', ['base_url' => $baseURL]);

        while (true) {
            try {
                Log::debug('Fetching updates from Telegram', ['offset' => $offset]);
                $response = Http::timeout(35)->get("{$baseURL}/getUpdates", [
                    'offset' => $offset,
                    'timeout' => 30,
                ]);

                if (! $response->successful() || ! $response->json('ok')) {
                    Log::warning('Failed to fetch updates', ['status' => $response->status(), 'ok' => $response->json('ok')]);
                    sleep(2);

                    continue;
                }

                $updates = $response->json('result', []);
                Log::debug('Received updates', ['count' => count($updates), 'offset' => $offset]);

                foreach ($updates as $update) {
                    $nextOffset = $update['update_id'] + 1;
                    Log::info('Processing update', ['update_id' => $update['update_id']]);

                    try {
                        if (! isset($update['business_message'])) {
                            Log::debug('Update does not contain business_message, skipping');
                            continue;
                        }

                        $bizMsg = $update['business_message'];
                        $chatId = $bizMsg['chat']['id'];
                        $connId = $bizMsg['business_connection_id'];
                        $customerText = $bizMsg['text'] ?? '';

                        Log::info('Processing business message', [
                            'chat_id' => $chatId,
                            'connection_id' => $connId,
                            'customer_text' => $customerText,
                        ]);

                        $reply = $this->generateReply($customerText, (string) $chatId);

                        Log::info('Sending reply to Telegram', [
                            'chat_id' => $chatId,
                            'connection_id' => $connId,
                            'reply_length' => strlen($reply),
                        ]);

                        Http::post("{$baseURL}/sendMessage", [
                            'business_connection_id' => $connId,
                            'chat_id' => $chatId,
                            'text' => $reply,
                        ]);

                        Log::info('Reply sent successfully');
                    } catch (Exception $e) {
                        Log::error('Error processing update', [
                            'update_id' => $update['update_id'],
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        $this->error($e->getMessage());
                    } finally {
                        $offset = $nextOffset;
                        Log::debug('Updated offset', ['offset' => $offset]);
                    }
                }
            } catch (Exception $e) {
                Log::error('Error in polling loop', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->error($e->getMessage());
                sleep(2);
            }

            usleep(200000);
        }
    }

    private function generateReply(string $customerText, string $chatId = ''): string
    {
        Log::info('Generating reply', ['customer_text' => $customerText]);

        $agent = new BookingAssistant($chatId ?: null);
        Log::debug('BookingAssistant instantiated');

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            Log::info('AI generation attempt', ['attempt' => $attempt, 'max_attempts' => 3]);
            try {
                $aiResponse = $agent->prompt(
                    $customerText,
                    provider: Lab::Groq,
                    model: 'llama-3.3-70b-versatile'
                );

                Log::info('AI response received', [
                    'attempt' => $attempt,
                    'response_length' => strlen((string) $aiResponse),
                ]);

                return $this->prepareReply((string) $aiResponse, $customerText);
            } catch (Exception $e) {
                $errorMsg = $e->getMessage();
                if (isset($e->response)) {
                    $errorMsg .= "\nResponse: " . $e->response->body();
                }
                
                Log::warning('AI provider error', [
                    'attempt' => $attempt,
                    'error' => $errorMsg,
                    'error_type' => get_class($e),
                ]);

                if ($attempt === 3) {
                    Log::error('Max attempts reached, using fallback reply');
                    $this->warn($errorMsg.' Sending fallback reply.');

                    return $this->fallbackReply($customerText);
                }

                Log::info('Retrying AI generation', ['attempt' => $attempt, 'sleep_seconds' => $attempt]);
                $this->warn($errorMsg.' Retrying...');
                sleep($attempt);
            }
        }

        Log::warning('All attempts exhausted, using fallback reply');
        return $this->fallbackReply($customerText);
    }

    private function prepareReply(string $reply, string $customerText): string
    {
        Log::debug('Preparing reply', ['original_reply_length' => strlen($reply)]);

        // Strip leaked function-call markup from LLM responses (e.g. Llama)
        $reply = preg_replace('/<function=\w+[^>]*>.*?<\/function>/s', '', $reply);
        $reply = preg_replace('/<function=[^\/]*\/>/s', '', $reply);
        $reply = trim($reply);
        Log::debug('Stripped function markup from reply', ['stripped_length' => strlen($reply)]);

        $bookingLink = $this->bookingLink();
        Log::debug('Booking link retrieved', ['has_booking_link' => !empty($bookingLink)]);

        if ($bookingLink) {
            $reply = str_ireplace(['[GetBookingLink]', 'GetBookingLink'], $bookingLink, $reply);
            Log::debug('Replaced booking link placeholders in reply');

            if ($this->isBookingRequest($customerText) && ! $this->containsUrl($reply)) {
                Log::info('Using booking link fallback for booking request without URL');
                return "You can book an appointment here:\n{$bookingLink}";
            }
        }

        Log::debug('Reply prepared successfully', ['final_length' => strlen($reply)]);
        return $reply;
    }

    private function fallbackReply(string $customerText): string
    {
        Log::info('Using fallback reply', ['customer_text' => $customerText]);

        $bookingLink = $this->bookingLink();
        Log::debug('Fallback: booking link retrieved', ['has_booking_link' => !empty($bookingLink)]);

        if ($bookingLink && $this->isBookingRequest($customerText)) {
            Log::info('Fallback: returning booking link for booking request');
            return "You can book an appointment here:\n{$bookingLink}";
        }

        Log::info('Fallback: returning empty reply (not a booking request or no booking link)');
        return '';
    }

    private function bookingLink(): ?string
    {
        $bookingLink = trim((string) config('services.booking.link', ''));
        $isValid = filter_var($bookingLink, FILTER_VALIDATE_URL);

        Log::debug('Booking link validation', [
            'configured_link' => $bookingLink,
            'is_valid' => $isValid !== false,
        ]);

        return $isValid ? $bookingLink : null;
    }

    private function isBookingRequest(string $message): bool
    {
        $isBooking = preg_match('/\b(book|booking|appointment|appointement|schedule|reserve|consultation)\b/i', $message) === 1;

        Log::debug('Checking if booking request', [
            'message' => $message,
            'is_booking_request' => $isBooking,
        ]);

        return $isBooking;
    }

    private function containsUrl(string $message): bool
    {
        $hasUrl = preg_match('/https?:\/\/\S+/i', $message) === 1;

        Log::debug('Checking if message contains URL', [
            'message' => $message,
            'contains_url' => $hasUrl,
        ]);

        return $hasUrl;
    }
}
