<?php

namespace App\Services;

use App\Models\SmsTemplate;
use App\Models\Provider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EskizTemplateService
{
    private string $baseUrl = 'https://notify.eskiz.uz/api';

    /**
     * Get all templates from Eskiz API.
     */
    public function getTemplatesFromEskiz(string $token): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get($this->baseUrl . '/template');

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Failed to get templates from Eskiz', [
                'response' => $response->json(),
                'status' => $response->status(),
            ]);

            return [];

        } catch (\Exception $e) {
            Log::error('Eskiz template fetch exception', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Submit a template to Eskiz for approval.
     */
    public function submitTemplateToEskiz(SmsTemplate $template, string $token): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->asForm()->post($this->baseUrl . '/template', [
                'name' => $template->name,
                'text' => $template->content,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Update template with provider ID
                $template->update([
                    'provider_template_id' => $data['id'] ?? null,
                    'status' => 'pending',
                ]);

                Log::info('Template submitted to Eskiz successfully', [
                    'template_id' => $template->id,
                    'provider_template_id' => $data['id'] ?? null,
                ]);

                return [
                    'status' => 'success',
                    'provider_template_id' => $data['id'] ?? null,
                    'message' => 'Template submitted successfully',
                ];
            }

            $errorMessage = $response->json()['message'] ?? 'Unknown error';
            
            Log::error('Eskiz template submission failed', [
                'template_id' => $template->id,
                'response' => $response->json(),
                'status' => $response->status(),
            ]);

            return [
                'status' => 'failed',
                'error' => $errorMessage,
            ];

        } catch (\Exception $e) {
            Log::error('Eskiz template submission exception', [
                'template_id' => $template->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Sync templates from Eskiz to local database.
     */
    public function syncTemplatesFromEskiz(): array
    {
        $results = [];
        $eskizProvider = Provider::where('display_name', 'eskiz')->first();

        if (!$eskizProvider) {
            return ['status' => 'failed', 'error' => 'Eskiz provider not found'];
        }

        $token = $eskizProvider->accessToken;
        if (!$token || !$token->isValid()) {
            return ['status' => 'failed', 'error' => 'No valid Eskiz token found'];
        }

        $templates = $this->getTemplatesFromEskiz($token->token_value);

        if (empty($templates)) {
            return ['status' => 'success', 'message' => 'No templates found', 'synced' => 0];
        }

        $synced = 0;
        foreach ($templates as $templateData) {
            $existingTemplate = SmsTemplate::where('provider_id', $eskizProvider->id)
                ->where('provider_template_id', $templateData['id'] ?? null)
                ->first();

            if (!$existingTemplate) {
                SmsTemplate::create([
                    'provider_id' => $eskizProvider->id,
                    'name' => $templateData['name'] ?? 'Unknown Template',
                    'content' => $templateData['text'] ?? '',
                    'status' => $this->mapEskizStatus($templateData['status'] ?? 'pending'),
                    'provider_template_id' => $templateData['id'] ?? null,
                    'approved_at' => $templateData['status'] === 'approved' ? now() : null,
                ]);
                $synced++;
            }
        }

        Log::info('Templates synced from Eskiz', [
            'provider' => 'eskiz',
            'synced_count' => $synced,
            'total_templates' => count($templates),
        ]);

        return [
            'status' => 'success',
            'message' => "Synced {$synced} templates from Eskiz",
            'synced' => $synced,
        ];
    }

    /**
     * Map Eskiz template status to our status.
     */
    private function mapEskizStatus(string $eskizStatus): string
    {
        return match ($eskizStatus) {
            'approved' => 'approved',
            'pending' => 'pending',
            'rejected' => 'rejected',
            default => 'pending',
        };
    }
}
