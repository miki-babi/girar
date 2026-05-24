<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetBookingLink implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Retrieve the official booking/scheduling link for the business.';
    }

    /**
     * Define the tool's input schema.
     * Only string enums are supported by Gemini — no number/integer enums.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'meeting_type' => $schema->string()
                ->description('The type of meeting: consultation, support, sales, or general.')
                ->required(),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $bookingLink = config('services.booking.link');

        return "Here is our official booking link: {$bookingLink}";
    }
}
