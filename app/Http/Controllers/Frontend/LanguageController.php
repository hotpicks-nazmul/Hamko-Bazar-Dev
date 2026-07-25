<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Switch language and redirect back
     */
    public function switch(Request $request)
    {
        $languageCode = $request->query('lang', 'en');
        
        // Find the language
        $language = Language::where('code', $languageCode)
            ->where('is_active', true)
            ->first();
        
        if (!$language) {
            // Fallback to default language
            $language = Language::getDefault();
        }
        
        if ($language) {
            // Store in session
            Session::put('locale', $language->code);
            Session::put('locale_rtl', $language->is_rtl);
        }
        
        // Redirect to home or previous page with fresh request
        return redirect()->to('/')->with('language_changed', true);
    }
    
    /**
     * Get available languages for API/frontend
     */
    public function available()
    {
        $languages = Language::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        
        return response()->json([
            'languages' => $languages,
            'current' => Session::get('locale', 'en')
        ]);
    }
}
