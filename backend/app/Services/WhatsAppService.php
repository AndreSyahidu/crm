<?php

namespace App\Services;

use App\Models\WhatsappMessage;
use App\Models\Lead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('WHATSAPP_SERVICE_URL', 'http://localhost:3000');
    }

    public function sendMessage(Lead $lead, string $message, ?string $mediaUrl = null)
    {
        $phone = $lead->whatsapp_number ?? $lead->phone;

        if (!$phone) {
            throw new \Exception('Lead does not have a phone number');
        }

        try {
            $response = Http::timeout(30)->post("{$this->baseUrl}/send-message", [
                'to' => $phone,
                'message' => $message,
                'media_url' => $mediaUrl
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Save to database
                $whatsappMessage = WhatsappMessage::create([
                    'lead_id' => $lead->id,
                    'message_id' => $data['message_id'] ?? null,
                    'from_number' => $data['from'] ?? env('WHATSAPP_NUMBER'),
                    'to_number' => $phone,
                    'direction' => 'outbound',
                    'type' => $mediaUrl ? 'media' : 'text',
                    'content' => $message,
                    'media_url' => $mediaUrl,
                    'status' => 'sent',
                    'sent_at' => now()
                ]);

                // Update lead last contact
                $lead->update(['last_contact_at' => now()]);

                return $whatsappMessage;
            }

            throw new \Exception('Failed to send message: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('WhatsApp send error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getStatus()
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/status");
            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('WhatsApp status check error: ' . $e->getMessage());
            return null;
        }
    }

    public function isConnected()
    {
        $status = $this->getStatus();
        return $status && $status['status'] === 'connected';
    }

    public function getQRCode()
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/qr");
            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('WhatsApp QR fetch error: ' . $e->getMessage());
            return null;
        }
    }
}
