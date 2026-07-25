<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiKeyController extends Controller
{
    /**
     * Display API Keys & Integrations page with tabs for API Keys and Webhooks
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'api-keys');
        
        if ($tab === 'webhooks') {
            return $this->webhooksIndex();
        }
        
        return $this->apiKeysIndex();
    }

    /**
     * Display API Keys list
     */
    protected function apiKeysIndex()
    {
        $apiKeys = ApiKey::orderBy('created_at', 'desc')->paginate(10);
        $types = ApiKey::getTypes();
        
        return view('admin.settings.api-keys.index', [
            'apiKeys' => $apiKeys,
            'types' => $types,
            'activeTab' => 'api-keys',
        ]);
    }

    /**
     * Display Webhooks list
     */
    protected function webhooksIndex()
    {
        $webhooks = Webhook::orderBy('created_at', 'desc')->paginate(10);
        $events = Webhook::getEvents();
        $apiKeys = ApiKey::orderBy('created_at', 'desc')->paginate(10);
        $types = ApiKey::getTypes();
        
        return view('admin.settings.api-keys.index', [
            'apiKeys' => $apiKeys,
            'webhooks' => $webhooks,
            'types' => $types,
            'events' => $events,
            'activeTab' => 'webhooks',
        ]);
    }

    /**
     * Store a new API Key
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'rate_limit' => 'nullable|integer|min:1|max:1000',
            'expires_at' => 'nullable|date|after:now',
            'permissions' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $apiKey = new ApiKey();
        $apiKey->name = $request->name;
        $apiKey->key = ApiKey::generateKey();
        $apiKey->secret = ApiKey::generateSecret();
        $apiKey->type = $request->type;
        $apiKey->description = $request->description;
        $apiKey->rate_limit = $request->rate_limit ?? 100;
        $apiKey->permissions = $request->permissions;
        $apiKey->expires_at = $request->expires_at;
        $apiKey->is_active = true;
        $apiKey->save();

        // Store the secret temporarily in session for display
        session()->flash('api_key_secret', $apiKey->secret);
        session()->flash('api_key_id', $apiKey->id);

        return redirect()->route('admin.api-keys.index', ['tab' => 'api-keys'])
            ->with('success', 'API Key created successfully!');
    }

    /**
     * Update an existing API Key
     */
    public function update(Request $request, $id)
    {
        $apiKey = ApiKey::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'rate_limit' => 'nullable|integer|min:1|max:1000',
            'expires_at' => 'nullable|date',
            'permissions' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('_editing_key_id', $id);
        }

        $apiKey->name = $request->name;
        $apiKey->type = $request->type;
        $apiKey->description = $request->description;
        $apiKey->rate_limit = $request->rate_limit ?? 100;
        $apiKey->permissions = $request->permissions;
        $apiKey->expires_at = $request->expires_at;
        $apiKey->is_active = $request->is_active === '1' || $request->is_active === true;
        $apiKey->save();

        return redirect()->route('admin.api-keys.index', ['tab' => 'api-keys'])
            ->with('success', 'API Key updated successfully!');
    }

    /**
     * Delete an API Key
     */
    public function destroy($id)
    {
        $apiKey = ApiKey::findOrFail($id);
        $apiKey->delete();

        return redirect()->route('admin.api-keys.index', ['tab' => 'api-keys'])
            ->with('success', 'API Key deleted successfully!');
    }

    /**
     * Regenerate an API Key
     */
    public function regenerate($id)
    {
        $apiKey = ApiKey::findOrFail($id);
        $apiKey->key = ApiKey::generateKey();
        $apiKey->secret = ApiKey::generateSecret();
        $apiKey->save();

        // Store the new secret temporarily in session for display
        session()->flash('api_key_secret', $apiKey->secret);
        session()->flash('api_key_id', $apiKey->id);

        return redirect()->route('admin.api-keys.index', ['tab' => 'api-keys'])
            ->with('success', 'API Key regenerated successfully! Save the new key - it will not be shown again.');
    }

    /**
     * Toggle API Key status
     */
    public function toggle(Request $request, $id)
    {
        $apiKey = ApiKey::findOrFail($id);
        $apiKey->is_active = !$apiKey->is_active;
        $apiKey->save();

        $status = $apiKey->is_active ? 'enabled' : 'disabled';

        return redirect()->route('admin.api-keys.index', ['tab' => 'api-keys'])
            ->with('success', "API Key {$status} successfully!");
    }

    // ==================== WEBHOOKS ====================

    /**
     * Store a new Webhook
     */
    public function storeWebhook(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'event' => 'required|string|max:50',
            'method' => 'required|in:POST,GET,PUT,PATCH,DELETE',
            'secret' => 'nullable|string|max:500',
            'timeout' => 'nullable|integer|min:5|max:300',
            'retry_count' => 'nullable|integer|min:0|max:10',
            'headers' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $webhook = new Webhook();
        $webhook->name = $request->name;
        $webhook->url = $request->url;
        $webhook->event = $request->event;
        $webhook->method = $request->method;
        $webhook->secret = $request->secret;
        $webhook->timeout = $request->timeout ?? 30;
        $webhook->retry_count = $request->retry_count ?? 3;
        $webhook->headers = $request->headers;
        $webhook->is_active = $request->is_active === '1' || $request->is_active === true;
        $webhook->save();

        return redirect()->route('admin.api-keys.index', ['tab' => 'webhooks'])
            ->with('success', 'Webhook created successfully!');
    }

    /**
     * Update an existing Webhook
     */
    public function updateWebhook(Request $request, $id)
    {
        $webhook = Webhook::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'event' => 'required|string|max:50',
            'method' => 'required|in:POST,GET,PUT,PATCH,DELETE',
            'secret' => 'nullable|string|max:500',
            'timeout' => 'nullable|integer|min:5|max:300',
            'retry_count' => 'nullable|integer|min:0|max:10',
            'headers' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('_editing_webhook_id', $id);
        }

        $webhook->name = $request->name;
        $webhook->url = $request->url;
        $webhook->event = $request->event;
        $webhook->method = $request->method;
        $webhook->secret = $request->secret;
        $webhook->timeout = $request->timeout ?? 30;
        $webhook->retry_count = $request->retry_count ?? 3;
        $webhook->headers = $request->headers;
        $webhook->is_active = $request->is_active === '1' || $request->is_active === true;
        $webhook->save();

        return redirect()->route('admin.api-keys.index', ['tab' => 'webhooks'])
            ->with('success', 'Webhook updated successfully!');
    }

    /**
     * Delete a Webhook
     */
    public function destroyWebhook($id)
    {
        $webhook = Webhook::findOrFail($id);
        $webhook->delete();

        return redirect()->route('admin.api-keys.index', ['tab' => 'webhooks'])
            ->with('success', 'Webhook deleted successfully!');
    }

    /**
     * Test a Webhook
     */
    public function testWebhook($id)
    {
        $webhook = Webhook::findOrFail($id);
        $result = $webhook->test();

        if ($result['success']) {
            return redirect()->route('admin.api-keys.index', ['tab' => 'webhooks'])
                ->with('success', 'Webhook test successful! Response code: ' . $result['status_code']);
        } else {
            return redirect()->route('admin.api-keys.index', ['tab' => 'webhooks'])
                ->with('error', 'Webhook test failed: ' . $result['message']);
        }
    }

    /**
     * Toggle Webhook status
     */
    public function toggleWebhook(Request $request, $id)
    {
        $webhook = Webhook::findOrFail($id);
        $webhook->is_active = !$webhook->is_active;
        $webhook->save();

        $status = $webhook->is_active ? 'enabled' : 'disabled';

        return redirect()->route('admin.api-keys.index', ['tab' => 'webhooks'])
            ->with('success', "Webhook {$status} successfully!");
    }

    /**
     * Show API key secret (one-time display)
     */
    public function showSecret($id)
    {
        $apiKey = ApiKey::findOrFail($id);
        
        return view('admin.settings.api-keys.show-secret', [
            'apiKey' => $apiKey,
        ]);
    }
}
