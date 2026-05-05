<?php

namespace App\Http\Resources\Api\Student;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'category' => $this->category,

            // API contract field mapped from DB column `calamity`
            'calamity_type' => $this->calamity,

            'description' => $this->description,
            'status' => $this->status,

            'location_address' => $this->location_address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,

            'submitted_at' => optional($this->submitted_at)->toDateTimeString(),
            'created_at' => optional($this->created_at)->toDateTimeString(),

            // Images with correct absolute URL including /ADMIN-WEB/public
            'images' => $this->whenLoaded('images', function () {
                return $this->images
                    ->map(function ($img) {
                        $raw = trim((string) ($img->image_url ?? ''));
                        $absolute = $raw === '' ? null : url($raw);

                        return [
                            'id' => $img->id,
                            'image_url' => $absolute,
                            'path' => $img->path,
                            'created_at' => optional($img->created_at)->toDateTimeString(),
                        ];
                    })
                    ->values();
            }),

            'first_image_url' => $this->whenLoaded('images', function () {
                $first = $this->images->first();
                $raw = $first?->image_url ? trim((string) $first->image_url) : '';
                return $raw === '' ? null : url($raw);
            }),

            'latest_action' => $this->whenLoaded('latestStudentVisibleAction', function () {
                return [
                    'recommended_action' => $this->latestStudentVisibleAction->recommended_action ?? null,
                    'recommended_note' => $this->latestStudentVisibleAction->recommended_note ?? null,
                    'recommended_at' => optional($this->latestStudentVisibleAction->recommended_at)->toDateTimeString(),
                    'public_remark' => $this->latestStudentVisibleAction->public_remark ?? null,
                    'action_note' => $this->latestStudentVisibleAction->action_taken_note ?? null,
                    'date' => optional($this->latestStudentVisibleAction->created_at)->toDateTimeString(),
                ];
            }),
        ];
    }
}