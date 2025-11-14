<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\AnalyticsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:api')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Leads
    Route::get('/leads', [LeadController::class, 'index']);
    Route::post('/leads', [LeadController::class, 'store']);
    Route::get('/leads/stats', [LeadController::class, 'stats']);
    Route::get('/leads/{id}', [LeadController::class, 'show']);
    Route::put('/leads/{id}', [LeadController::class, 'update']);
    Route::delete('/leads/{id}', [LeadController::class, 'destroy']);
    Route::post('/leads/{id}/update-score', [LeadController::class, 'updateScore']);
    Route::get('/leads/{id}/timeline', [LeadController::class, 'timeline']);
    Route::post('/leads/bulk-assign', [LeadController::class, 'bulkAssign']);
    Route::post('/leads/bulk-tag', [LeadController::class, 'bulkTag']);

    // Deals & Pipeline
    Route::get('/deals', [DealController::class, 'index']);
    Route::get('/deals/kanban', [DealController::class, 'kanban']);
    Route::post('/deals', [DealController::class, 'store']);
    Route::get('/deals/{id}', [DealController::class, 'show']);
    Route::put('/deals/{id}', [DealController::class, 'update']);
    Route::delete('/deals/{id}', [DealController::class, 'destroy']);
    Route::post('/deals/{id}/move-stage', [DealController::class, 'moveStage']);
    Route::post('/deals/{id}/reorder', [DealController::class, 'reorder']);

    // WhatsApp
    Route::get('/whatsapp/status', [WhatsAppController::class, 'status']);
    Route::get('/whatsapp/qr', [WhatsAppController::class, 'getQR']);
    Route::post('/whatsapp/send', [WhatsAppController::class, 'sendMessage']);
    Route::get('/whatsapp/leads/{id}/messages', [WhatsAppController::class, 'messages']);
    Route::get('/whatsapp/leads/{id}/chat-history', [WhatsAppController::class, 'chatHistory']);
    Route::post('/whatsapp/messages/{id}/read', [WhatsAppController::class, 'markAsRead']);
    Route::get('/whatsapp/unread-count', [WhatsAppController::class, 'unreadCount']);
    Route::post('/whatsapp/disconnect', [WhatsAppController::class, 'disconnect']);
    Route::post('/whatsapp/reconnect', [WhatsAppController::class, 'reconnect']);

    // Broadcast Campaigns
    Route::get('/broadcasts', [BroadcastController::class, 'index']);
    Route::post('/broadcasts', [BroadcastController::class, 'store']);
    Route::get('/broadcasts/{id}', [BroadcastController::class, 'show']);
    Route::put('/broadcasts/{id}', [BroadcastController::class, 'update']);
    Route::delete('/broadcasts/{id}', [BroadcastController::class, 'destroy']);
    Route::get('/broadcasts/{id}/preview', [BroadcastController::class, 'preview']);
    Route::post('/broadcasts/{id}/start', [BroadcastController::class, 'start']);
    Route::post('/broadcasts/{id}/pause', [BroadcastController::class, 'pause']);
    Route::post('/broadcasts/{id}/resume', [BroadcastController::class, 'resume']);
    Route::get('/broadcasts/{id}/stats', [BroadcastController::class, 'stats']);

    // Analytics & Reports
    Route::get('/analytics/dashboard', [AnalyticsController::class, 'dashboard']);
    Route::get('/analytics/leads-by-source', [AnalyticsController::class, 'leadsBySource']);
    Route::get('/analytics/leads-by-status', [AnalyticsController::class, 'leadsByStatus']);
    Route::get('/analytics/conversion-funnel', [AnalyticsController::class, 'conversionFunnel']);
    Route::get('/analytics/revenue-over-time', [AnalyticsController::class, 'revenueOverTime']);
    Route::get('/analytics/leads-over-time', [AnalyticsController::class, 'leadsOverTime']);
    Route::get('/analytics/team-performance', [AnalyticsController::class, 'teamPerformance']);
    Route::get('/analytics/whatsapp-activity', [AnalyticsController::class, 'whatsappActivity']);
    Route::get('/analytics/broadcast-performance', [AnalyticsController::class, 'broadcastPerformance']);
    Route::get('/analytics/top-leads', [AnalyticsController::class, 'topLeads']);
    Route::get('/analytics/lost-deals-reasons', [AnalyticsController::class, 'lostDealsReasons']);
    Route::get('/analytics/export', [AnalyticsController::class, 'export']);

});

// Webhook for WhatsApp service (if needed)
Route::post('/webhooks/whatsapp', function (Request $request) {
    // Handle webhook from WhatsApp service
    // This could be used for real-time message updates
    return response()->json(['status' => 'received']);
});
