<?php

namespace App\Ai\Agents;

use App\Ai\Tools\GetBookingLink;
use App\Ai\Tools\ServiceAssistant;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Promptable;

#[Temperature(0.1)]
class BookingAssistant implements Agent, Conversational, HasTools
{
    use Promptable;

    public function __construct(private readonly ?string $chatId = null) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): string
    {
        return 'You are an elite, professional virtual receptionist representing our business over Telegram. '.
               'For questions about business services, policies, delivery, pricing, availability, or other business details, '.
               'you must call the ServiceAssistant tool to search the knowledge base , the knowledge base has topics and answer based on the descriptions on the topics subtopic descriptions. If the customer wants to book '.
               'an appointment, reserve a spot, or schedule a consultation, you must call the GetBookingLink tool '.
               'to fetch the official calendar URL. Always answer naturally and clearly. '.
               'CRITICAL: Always use the native tool-calling mechanism to execute these tools. '.
               'Never print or output raw XML tags, custom tags like <function=...>, or JSON blocks in your text response.';
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): array
    {
        return [
            new ServiceAssistant($this->chatId),
            app(GetBookingLink::class),
        ];
    }
}
