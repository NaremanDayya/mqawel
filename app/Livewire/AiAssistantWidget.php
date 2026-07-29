<?php

namespace App\Livewire;

use App\Services\Ai\AiRequestException;
use App\Services\Ai\AssistantService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AiAssistantWidget extends Component
{
    /** @var array<int, array{role: string, content: string}> */
    public array $messages = [];

    public string $input = '';

    public function sendMessage(AssistantService $assistant): void
    {
        $text = trim($this->input);

        if ($text === '') {
            return;
        }

        $this->messages[] = ['role' => 'user', 'content' => $text];
        $this->input = '';

        try {
            $reply = $assistant->respond(Auth::user()->company_id, $this->conversationForApi());
            $this->messages[] = ['role' => 'assistant', 'content' => $reply !== '' ? $reply : __('backend.ai_assistant_empty_reply')];
        } catch (AiRequestException $e) {
            Notification::make()->title(__('backend.ai_assistant_failed'))->danger()->send();
        }
    }

    public function clearConversation(): void
    {
        $this->messages = [];
    }

    /**
     * @return array<int, array{role: string, content: string}>
     */
    protected function conversationForApi(): array
    {
        return collect($this->messages)
            ->map(fn (array $message) => ['role' => $message['role'], 'content' => $message['content']])
            ->all();
    }

    public function render()
    {
        return view('livewire.ai-assistant-widget');
    }
}
