<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Testing\Fluent\Concerns\Has;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'password',
        'role_id',
        'user_name',
        'remember_token',
        'status',
        'timezone',
        'profile_step',
        'is_profile_complete'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class)->select('id','title');
    }

    public function verify()
    {
        return !is_null($this->email_verified_at);
    }

    public function information()
    {
        return $this->hasOne(UserInformation::class);
    }

    public function qualification()
    {
        return $this->hasMany(UserQualification::class);
    }

    public function billingInformation()
    {
        return $this->hasMany(UserBillingInformation::class);
    }

    public function categories()
    {
        return $this->hasMany(UserCategory::class, 'user_id', 'id');
    }

    public function course()
    {
        return $this->hasMany(Course::class);
    }
   
    // Add the following relationship methods to your User model
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedChats()
    {
        // Chats where this user is the receiver_id
        return $this->hasMany(Chat::class, 'receiver_id');
    }
    
    public function lastEmailChangeRequest()
    {
        return $this->hasOne(EmailChangeRequest::class)->latest();
    }

    public function lastIdProof()
    {
        return $this->hasOne(UserVerification::class)->where('type','IDProof')->latest();
    }

    public function lastFaceProof()
    {
        return $this->hasOne(UserVerification::class)->where('type','Face')->latest();
    }
    
    public function tutor()
    {
        return $this->hasOne(Tutor::class);
    }

    public function school()
    {
        return $this->hasOne(School::class);
    }

    public function schoolUser()
    {
        return $this->hasOne(SchoolUser::class, 'user_id');
    }
    
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'updated_at',
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
            'created_at' => 'datetime:Y-m-d H:i:s',
            'updated_at' => 'datetime:Y-m-d H:i:s',
        ];
    }
}
