<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\ContactRequest;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class ContactController extends Controller
{
    public function show(string $locale): View
    {
        return view('shop.contact', [
            'seo' => [
                'title' => __('shop.contact.title'),
                'description' => __('shop.contact.description'),
                'canonical' => route('contact.show', $locale),
                'alternates' => collect(config('saraki.supported_locales'))
                    ->mapWithKeys(fn (string $target) => [$target => route('contact.show', $target)])
                    ->all(),
            ],
        ]);
    }

    /**
     * The enquiry is stored even if the email fails: a message the atelier can
     * read in the admin is worth more than a clean failure, so the mail is
     * attempted afterwards and its problems are only logged.
     */
    public function store(ContactRequest $request, string $locale): RedirectResponse
    {
        $data = $request->validated();

        // A filled honeypot means a script: answer exactly like a person would be
        // answered, but store and send nothing.
        if ($request->isSpam()) {
            return $this->thanks($locale);
        }

        $message = ContactMessage::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'subject' => $data['subject'],
            'message' => $data['message'],
        ]);

        $this->notify($message);

        return $this->thanks($locale);
    }

    private function thanks(string $locale): RedirectResponse
    {
        return redirect()
            ->route('contact.show', $locale)
            ->with('success', __('shop.contact.sent'));
    }

    private function notify(ContactMessage $message): void
    {
        try {
            Mail::to($message->email)
                ->locale(app()->getLocale())
                ->send(new ContactMessageReceived($message));

            Mail::to(config('saraki.admin_email'))
                ->locale(config('app.locale'))
                ->send(new ContactMessageReceived($message, forAdmin: true));
        } catch (Throwable $exception) {
            Log::error('Contact message email failed', [
                'contact_message' => $message->id,
                'reason' => $exception->getMessage(),
            ]);
        }
    }
}
