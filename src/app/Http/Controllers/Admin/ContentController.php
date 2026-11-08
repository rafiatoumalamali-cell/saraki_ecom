<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ContactStatus;
use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\ContactMessage;
use App\Models\GalleryItem;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Landing page of the content area: what exists, what is still a draft, and the
 * enquiries that have not been answered yet.
 */
class ContentController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.content.index', [
            'counts' => [
                'gallery' => GalleryItem::query()->count(),
                'gallery_live' => GalleryItem::query()->published()->count(),
                'posts' => BlogPost::query()->count(),
                'posts_live' => BlogPost::query()->where('status', 'published')->count(),
                'pages' => Page::query()->count(),
                'pages_live' => Page::query()->published()->count(),
                'messages' => ContactMessage::query()->count(),
                'messages_new' => ContactMessage::query()->where('status', ContactStatus::New)->count(),
            ],
            'latestPosts' => BlogPost::query()->latest()->limit(5)->get(),
            'latestMessages' => ContactMessage::query()
                ->whereIn('status', [ContactStatus::New, ContactStatus::Read])
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
