<?php

namespace App\Services\Ai;

use App\Services\CompanyInsights;

class AssistantService
{
    protected int $maxToolRoundTrips = 4;

    public function __construct(protected AnthropicClient $client, protected CompanyInsights $insights)
    {
    }

    /**
     * @param  array<int, array{role: string, content: mixed}>  $conversation
     */
    public function respond(int $companyId, array $conversation): string
    {
        $messages = $conversation;

        for ($i = 0; $i < $this->maxToolRoundTrips; $i++) {
            $response = $this->client->send(
                messages: $messages,
                system: $this->systemPrompt(),
                tools: $this->tools(),
                maxTokens: 1024,
            );

            $toolUses = $this->client->toolUsesFrom($response);

            if (empty($toolUses)) {
                return $this->client->textFrom($response) ?? '';
            }

            $messages[] = ['role' => 'assistant', 'content' => $response['content']];

            $messages[] = [
                'role' => 'user',
                'content' => array_map(fn (array $toolUse) => [
                    'type' => 'tool_result',
                    'tool_use_id' => $toolUse['id'],
                    'content' => json_encode($this->runTool($companyId, $toolUse['name'], $toolUse['input'] ?? [])),
                ], $toolUses),
            ];
        }

        return __('backend.ai_assistant_max_steps');
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    protected function runTool(int $companyId, string $name, array $input): array
    {
        return match ($name) {
            'get_projects_summary' => $this->insights->projectsSummary($companyId),
            'get_project_details' => $this->insights->projectDetails($companyId, $input['query'] ?? '') ?? ['error' => 'not_found'],
            'get_worker_alerts' => $this->insights->alertCounts($companyId),
            'get_expiring_documents' => ['documents' => $this->insights->expiringDocuments($companyId, (int) ($input['days'] ?? 30))],
            default => ['error' => 'unknown_tool'],
        };
    }

    protected function systemPrompt(): string
    {
        return 'You are the in-app assistant for Mqawel+, a construction-company management platform. '
            .'Answer only using data returned by the provided tools — never invent numbers or project details. '
            .'Respond in the same language the user writes in (Arabic or English). Be concise and actionable.';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function tools(): array
    {
        return [
            [
                'name' => 'get_projects_summary',
                'description' => 'Get a summary of the company\'s projects: total count, counts grouped by status, and the names of currently active (pending/processing) projects.',
                'input_schema' => ['type' => 'object', 'properties' => new \stdClass, 'required' => []],
            ],
            [
                'name' => 'get_project_details',
                'description' => 'Look up a specific project by name or ID: its status, address, budget, and completion percentage.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => ['type' => 'string', 'description' => 'The project name (or part of it) or numeric ID to look up.'],
                    ],
                    'required' => ['query'],
                ],
            ],
            [
                'name' => 'get_worker_alerts',
                'description' => 'Get counts of items needing attention: expired documents, documents about to expire, incomplete document records, and workers with incomplete profiles.',
                'input_schema' => ['type' => 'object', 'properties' => new \stdClass, 'required' => []],
            ],
            [
                'name' => 'get_expiring_documents',
                'description' => 'List the company\'s documents expiring soon (files attached to workers, projects, etc.), soonest first.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'days' => ['type' => 'integer', 'description' => 'How many days ahead to check. Defaults to 30.'],
                    ],
                    'required' => [],
                ],
            ],
        ];
    }
}
