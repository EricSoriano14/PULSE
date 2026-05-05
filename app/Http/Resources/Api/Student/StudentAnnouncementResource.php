<?php

namespace App\Http\Resources\Api\Student;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class StudentAnnouncementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // "/storage/announcements/..."
        $relative = $this->image_path
            ? Storage::disk('public')->url($this->image_path)
            : null;

        // ✅ Use url() so APP_URL base path (e.g. /ADMIN-WEB/public) is included
        $imageUrl = $relative ? url($relative) : null;

        return [
            'id' => $this->id,
            'department' => $this->department,
            'description' => $this->description,

            'image_url' => $imageUrl,
            'image_path' => $this->image_path,

            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
