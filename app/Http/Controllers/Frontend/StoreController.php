<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    /**
     * Switch the current store.
     */
    public function switch(Request $request, $storeId = null)
    {
        if ($storeId) {
            $store = Store::active()->find($storeId);
            
            if (!$store) {
                return redirect()->back()->with('error', 'Store not found or inactive.');
            }
            
            session(['current_store_id' => $store->id]);
        } else {
            // Clear store session
            session()->forget('current_store_id');
        }
        
        return redirect()->back()->with('success', 'Store switched successfully!');
    }

    /**
     * Get current store info (API).
     */
    public function currentStore(Request $request)
    {
        $storeId = session('current_store_id');
        $store = null;
        
        if ($storeId) {
            $store = Store::active()->find($storeId);
        }
        
        // If no store selected or store not found, get default
        if (!$store) {
            $store = Store::getDefault();
        }
        
        return response()->json([
            'success' => true,
            'store' => $store,
            'all_stores' => Store::getActiveStores()
        ]);
    }

    /**
     * Get all active stores (API).
     */
    public function getStores(Request $request)
    {
        $stores = Store::getActiveStores();
        
        return response()->json([
            'success' => true,
            'stores' => $stores
        ]);
    }
}
