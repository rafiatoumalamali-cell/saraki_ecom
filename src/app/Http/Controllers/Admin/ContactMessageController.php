<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ContactStatus;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $status = ContactStatus::tryFrom((string) $request->query('status'));
        $search = trim((string) $request->query('q'));

        return view('admin.messages.index', [
            'messages' => ContactMessage::query()
                ->when($status !== null, fn ($query) => $query->where('status', $status))
                ->when($search !== '', fn ($query) => $query->where(fn ($inner) => $inner
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")))
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'counts' => collect(ContactStatus::cases())
                ->mapWithKeys(fn (ContactStatus $case) => [$case->value => ContactMessage::query()->where('status', $case)->count()])
                ->all(),
            'statuses' => ContactStatus::cases(),
            'status' => $status,
            'search' => $search,
        ]);
    }

    /**
     * Opening an enquiry marks it read, which is what the inbox pattern is for:
     * the "new" badge then means "nobody has looked at this yet".
     */
    public function show(ContactMessage $message): View
    {
        if ($message->status === ContactStatus::New) {
            $message->forceFill(['status' => ContactStatus::Read])->save();
        }

        return view('admin.messages.show', ['message' => $message]);
    }

    public function updateStatus(Request $request, ContactMessage $message): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(ContactStatus::class)],
        ]);

        $message->forceFill(['status' => ContactStatus::from($data['status'])])->save();

        return back()->with('success', __('admin.messages.status_updated', ['name' => $message->name]));
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $name = $message->name;

        $message->delete();

        return redirect()
            ->route('admin.messages.index')
            ->with('success', __('admin.messages.deleted', ['name' => $name]));
    }
}
