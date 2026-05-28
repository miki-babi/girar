<?php

namespace App\Ai\Agents;

use App\Ai\Tools\GetBookingLink;
use App\Ai\Tools\ServiceAssistant;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;

class BookingAssistant implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): string
    {
        return 'You are an elite, professional virtual receptionist representing our business over Telegram. '.
               'For questions about business services, policies, delivery, pricing, availability, or other business details, '.
               'use the ServiceAssistant tool before answering. If they want to book an appointment, reserve a spot, '.
               'or schedule a consultation, you MUST use the GetBookingLink tool to fetch our official calendar URL. '.
               'Never print tool names such as [GetBookingLink] or [ServiceAssistant]. Always answer naturally and clearly.';
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
            app(ServiceAssistant::class),
            app(GetBookingLink::class),
        ];
    }
}
