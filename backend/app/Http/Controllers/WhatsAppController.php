<?php

namespace App\Http\Controllers;

use App\Models\WhatsappMessage;
use App\Models\MessageQueue;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class WhatsAppController extends Controller
{
    protected $whatsappServiceUrl;

    public function __construct()
    {
        $this->whatsappServiceUrl = env('WHATSAPP_SERVICE_URL', 'http://localhost:3000');
    }

    public function status()
    {
        try {
            $response = Http::get("{$this->whatsappServiceUrl}/status");

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to connect to WhatsApp service',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getQR()
    {
        try {
            $response = Http::get("{$this->whatsappServiceUrl}/qr");

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get QR code',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_id' => 'required|exists:leads,id',
            'message' => 'required|string',
            'media_url' => 'nullable|url',
            'queue' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $lead = Lead::findOrFail($request->lead_id);
        $phone = $lead->whatsapp_number ?? $lead->phone;

        if (!$phone) {
            return response()->json([
                'success' => false,
                'error' => 'Lead does not have a phone number'
            ], 400);
        }

        // Queue message or send immediately
        if ($request->get('queue', false)) {
            MessageQueue::create([
                'to_number' => $phone,
                'message' => $request->message,
                'media_url' => $request->media_url,
                'priority' => $request->get('priority', 5),
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Message queued successfully'
            ]);
        }

        // Send immediately
        try {
            $response = Http::post("{$this->whatsappServiceUrl}/send-message", [
                'to' => $phone,
                'message' => $request->message,
                'media_url' => $request->media_url
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Save to database
                WhatsappMessage::create([
                    'lead_id' => $lead->id,
                    'message_id' => $data['message_id'] ?? null,
                    'from_number' => $data['from'] ?? env('WHATSAPP_NUMBER'),
                    'to_number' => $phone,
                    'direction' => 'outbound',
                    'type' => $request->media_url ? 'media' : 'text',
                    'content' => $request->message,
                    'media_url' => $request->media_url,
                    'status' => 'sent',
                    'sent_at' => now()
                ]);

                // Update lead last contact
                $lead->update(['last_contact_at' => now()]);

                return response()->json([
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'data' => $data
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Failed to send message'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function messages(Request $request, $leadId)
    {
        $lead = Lead::findOrFail($leadId);

        $messages = WhatsappMessage::where('lead_id', $leadId)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 50));

        return response()->json($messages);
    }

    public function chatHistory(Request $request, $leadId)
    {
        $lead = Lead::findOrFail($leadId);
        $phone = $lead->whatsapp_number ?? $lead->phone;

        try {
            $limit = $request->get('limit', 50);
            $response = Http::get("{$this->whatsappServiceUrl}/chats/{$phone}", [
                'limit' => $limit
            ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function markAsRead($messageId)
    {
        $message = WhatsappMessage::where('message_id', $messageId)->first();

        if (!$message) {
            return response()->json([
                'success' => false,
                'error' => 'Message not found'
            ], 404);
        }

        $message->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Message marked as read'
        ]);
    }

    public function unreadCount()
    {
        $count = WhatsappMessage::unread()->count();

        return response()->json([
            'count' => $count
        ]);
    }

    public function disconnect()
    {
        try {
            $response = Http::post("{$this->whatsappServiceUrl}/disconnect");

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function reconnect()
    {
        try {
            $response = Http::post("{$this->whatsappServiceUrl}/reconnect");

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
