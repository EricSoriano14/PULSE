<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        // Phase 3 fields
        'name',
        'first_name',
        'middle_name',
        'last_name',
        'student_id',
        'course',
        'department',
        'year',
        'email',
        'email_verified_at',
        'password',
        'address',
        'info',
        'avatar_path',
        'safety_status',

        // Phase 4 fields
        'role',
        'full_name',
        'username',
        'profile_image_url',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * User -> Reports.
     */
    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Reports assigned to this faculty by student selection.
     */
    public function assignedFacultyReports()
    {
        return $this->hasMany(Report::class, 'assigned_faculty_id');
    }

    /**
     * Reports assigned to this Co-CSS by CSS/Admin.
     */
    public function assignedCoCssReports()
    {
        return $this->hasMany(Report::class, 'assigned_co_css_id');
    }

    /**
     * User -> Student profile.
     */
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    /**
     * User -> Announcements posted.
     */
    public function announcementsPosted()
    {
        return $this->hasMany(Announcement::class, 'posted_by_user_id');
    }

    /**
     * Simple role helpers.
     */
    public function isAdmin(): bool
    {
        return strtolower((string) $this->role) === 'admin';
    }

    public function isCss(): bool
    {
        return strtolower((string) $this->role) === 'css';
    }

    public function isFaculty(): bool
    {
        return strtolower((string) $this->role) === 'faculty';
    }

    public function isCoCss(): bool
    {
        return strtolower((string) $this->role) === 'co_css';
    }

    public function isStudent(): bool
    {
        return strtolower((string) $this->role) === 'student';
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class)->latest('id');
    }

    public function unreadNotifications(): HasMany
    {
        return $this->hasMany(Notification::class)
            ->whereNull('read_at')
            ->latest('id');
    }
}