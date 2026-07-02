<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNotificationRequest;
use App\Http\Requests\Admin\UpdateNotificationRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = Notification::query()
            ->with('user:id,full_name,phone')
            ->latest()
            ->paginate(12);

        $total = Notification::query()->count();
        $read = Notification::query()->where('is_read', true)->count();

        return view('admin.notifications', [
            'notifications' => $notifications,
            'customers' => User::query()->where('is_active', true)->orderBy('full_name')->get(['id', 'full_name', 'phone']),
            'notificationStats' => [
                'total' => $total,
                'order' => Notification::query()->where('type', 'order')->count(),
                'offer' => Notification::query()->where('type', 'offer')->count(),
                'readRate' => $total > 0 ? round(($read / $total) * 100) : 0,
            ],
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.notifications');
    }

    public function store(StoreNotificationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $payload = [
            'title' => $data['title'],
            'body' => $data['body'],
            'type' => $data['type'],
            'image' => $data['image'] ?? null,
            'is_read' => (bool) ($data['is_read'] ?? false),
        ];

        if ($data['audience'] === 'all') {
            User::query()->where('is_active', true)->pluck('id')->each(
                fn (int $userId) => Notification::query()->create([...$payload, 'user_id' => $userId])
            );
        } else {
            Notification::query()->create([...$payload, 'user_id' => $data['user_id']]);
        }

        return redirect()
            ->route('admin.notifications')
            ->with('status', 'Notification created successfully.');
    }

    public function show(Notification $notification): RedirectResponse
    {
        return redirect()->route('admin.notifications');
    }

    public function edit(Notification $notification): RedirectResponse
    {
        return redirect()->route('admin.notifications');
    }

    public function update(UpdateNotificationRequest $request, Notification $notification): RedirectResponse
    {
        $data = $request->validated();
        $data['is_read'] = (bool) ($data['is_read'] ?? false);

        $notification->update($data);

        return redirect()
            ->route('admin.notifications')
            ->with('status', 'Notification updated successfully.');
    }

    public function destroy(Notification $notification): RedirectResponse
    {
        $notification->delete();

        return redirect()
            ->route('admin.notifications')
            ->with('status', 'Notification deleted successfully.');
    }
}
