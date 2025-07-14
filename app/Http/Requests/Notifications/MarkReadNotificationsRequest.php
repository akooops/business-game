<?php

namespace App\Http\Requests\Notifications;

use App\Models\Notification;
use Illuminate\Foundation\Http\FormRequest;

class MarkReadNotificationsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        $notification = Notification::find($this->route('notification'));

        return $user->id === $notification->user_id;
    }
} 