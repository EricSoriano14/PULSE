<?php

namespace App\Http\Requests\Api\Student;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // token auth handled by middleware
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'department' => is_string($this->department) ? trim($this->department) : $this->department,
            'assigned_faculty_id' => $this->assigned_faculty_id !== null
                ? (int) $this->assigned_faculty_id
                : null,
            'title' => is_string($this->title) ? trim($this->title) : $this->title,
            'category' => is_string($this->category) ? trim($this->category) : $this->category,
            'calamity_type' => is_string($this->calamity_type) ? trim($this->calamity_type) : $this->calamity_type,
            'location_address' => is_string($this->location_address) ? trim($this->location_address) : $this->location_address,
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],

            'department' => ['required', 'string', 'max:255'],
            'assigned_faculty_id' => ['required', 'integer', 'exists:users,id'],

            'calamity_type' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],

            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'location_address' => ['nullable', 'string', 'max:255'],

            'image' => ['nullable', 'image', 'max:5120'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'department.required' => 'Department is required.',
            'assigned_faculty_id.required' => 'Instructor is required.',
            'assigned_faculty_id.exists' => 'Selected instructor is invalid.',
            'calamity_type.required' => 'Calamity type is required.',
        ];
    }

    public function attributes(): array
    {
        return [
            'assigned_faculty_id' => 'instructor',
            'calamity_type' => 'calamity type',
            'location_address' => 'location address',
        ];
    }
}