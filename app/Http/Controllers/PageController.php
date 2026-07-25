<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Page;
use App\Models\Faq;
use App\Models\Setting;
use App\Models\Store;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Mail;

class PageController extends Controller
{
    /**
     * Display about page.
     */
    public function about()
    {
        $page = Page::where('slug', 'about')->first();
        return view('themes.general.pages.about', compact('page'));
    }

    /**
     * Display contact page.
     */
    public function contact()
    {
        $settings = Setting::whereIn('key', [
            'top_bar_phone', 'top_bar_email', 'footer_phone', 'footer_email',
            'site_phone', 'site_email', 'contact_address'
        ])->pluck('value', 'key');
        
        // Ensure contact_address has a fallback
        if (!isset($settings['contact_address'])) {
            $settings['contact_address'] = '123 Green Market Road, Dhaka-1205, Bangladesh';
        }
        
        // Default store provides the map coordinates
        $store = Store::getDefault();
        
        return view('themes.general.pages.contact', compact('settings', 'store'));
    }

    /**
     * Send contact form.
     */
    public function sendContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        $contactEmail = Setting::whereIn('key', ['top_bar_email', 'footer_email', 'site_email'])->value('value');

        if ($contactEmail) {
            Mail::raw($request->message, function ($mail) use ($request, $contactEmail) {
                $mail->to($contactEmail)
                    ->from($request->email, $request->name)
                    ->subject($request->subject);
            });
        }

        return back()->with('success', 'Thank you for your message. We will get back to you soon.');
    }

    /**
     * Display FAQ page.
     */
    public function faq()
    {
        $page = Page::where('slug', 'faq')->first();
        $faqs = Faq::active()->ordered()->get();
        return view('themes.general.pages.faq', compact('page', 'faqs'));
    }

    /**
     * Display terms page.
     */
    public function terms()
    {
        $page = Page::where('slug', 'terms')->first();
        return view('themes.general.pages.terms', compact('page'));
    }

    /**
     * Display privacy page.
     */
    public function privacy()
    {
        $page = Page::where('slug', 'privacy')->first();
        return view('themes.general.pages.privacy', compact('page'));
    }

    /**
     * Display a dynamic page by slug.
     */
    public function show($slug)
    {
        $page = Page::where('slug', $slug)->where('status', 'published')->firstOrFail();
        return view('themes.general.pages.show', compact('page'));
    }

    /**
     * Subscribe to newsletter.
     */
    public function newsletterSubscribe(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255', 'regex:/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*\.[a-zA-Z]{2,}$/'],
        ]);

        $email = $request->email;

        // Check if already subscribed
        $existing = Subscriber::where('email', $email)->first();

        if ($existing) {
            if ($existing->isUnsubscribed()) {
                $existing->resubscribe();
                $message = 'Welcome back! You have been resubscribed to our newsletter.';
                $type = 'success';
            } else {
                $message = 'You are already subscribed to our newsletter.';
                $type = 'info';
            }
        } else {
            // Save new subscriber
            Subscriber::create([
                'email' => $email,
                'status' => 'active',
            ]);
            $message = 'Thank you for subscribing to our newsletter!';
            $type = 'success';
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['type' => $type, 'message' => $message]);
        }

        return back()->with($type, $message);
    }

    /**
     * Unsubscribe from newsletter.
     */
    public function newsletterUnsubscribe(Request $request)
    {
        $email = $request->email;

        if (!$email) {
            return view('themes.general.pages.unsubscribe', [
                'message' => 'Invalid unsubscribe request.',
                'success' => false
            ]);
        }

        $subscriber = \App\Models\Subscriber::where('email', $email)->first();

        if ($subscriber) {
            $subscriber->unsubscribe();
            return view('themes.general.pages.unsubscribe', [
                'message' => 'You have been successfully unsubscribed from our newsletter.',
                'success' => true
            ]);
        }

        return view('themes.general.pages.unsubscribe', [
            'message' => 'Email not found in our subscription list.',
            'success' => false
        ]);
    }
}
