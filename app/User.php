<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject as AuthenticatableUserContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract, AuthenticatableUserContract
{
    use Authenticatable;
    use CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_name', 'email', 'password', 'active', 'first_name', 'last_name', 'ban', 'ext', 'mobile', 'profile_pic',
        'phone_number', 'company', 'agent_sign', 'account_type', 'account_status',
        'assign_group', 'primary_dpt', 'agent_tzone', 'daylight_save', 'limit_access',
        'directory_listing', 'vacation_mode', 'role', 'internal_note', 'country_code', 'not_accept_ticket', 'is_delete', ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    public function getProfilePicAttribute($value)
    {
        $info = $this->avatar();
        $pic = null;
        if ($info) {
            $pic = $this->checkArray('avatar', $info);
        }
        if (!$pic && $value) {
            $pic = '';
            $file = public_path('uploads/profilepic/'.$value);
            if ($file && file_exists($file)) {
                $type = pathinfo($file, PATHINFO_EXTENSION);
                $data = file_get_contents($file);
                $pic = 'data:image/'.$type.';base64,'.base64_encode($data);
            }
        }
        if (!$value) {
            $pic = \Gravatar::src($this->attributes['email']);
        }

        return $pic;
    }

    public function avatar()
    {
        $related = \App\UserAdditionalInfo::class;
        $foreignKey = 'owner';

        return $this->hasMany($related, $foreignKey)->select('value')->where('key', 'avatar')->first();
    }

    public function getOrganizationRelation()
    {
        $related = \App\Model\helpdesk\Agent_panel\User_org::class;
        $user_relation = $this->hasMany($related, 'user_id');
        $relation = $user_relation->first();
        if ($relation) {
            $org_id = $relation->org_id;
            $orgs = new \App\Model\helpdesk\Agent_panel\Organization();
            $org = $orgs->where('id', $org_id);

            return $org;
        }
    }

    public function getOrganization()
    {
        $name = '';
        if ($this->getOrganizationRelation()) {
            $org = $this->getOrganizationRelation()->first();
            if ($org) {
                $name = $org->name;
            }
        }

        return $name;
    }

    public function getOrgWithLink()
    {
        $name = '';
        $org = $this->getOrganization();
        if ($org !== '') {
            $orgs = $this->getOrganizationRelation()->first();
            if ($orgs) {
                $id = $orgs->id;
                $name = '<a href='.url('organizations/'.$id).'>'.ucfirst($org).'</a>';
            }
        }

        return $name;
    }

    public function getEmailAttribute($value)
    {
        if (!$value) {
            $value = \Lang::get('lang.not-available');
        }

        return $value;
    }

    public function getExtraInfo($id = '')
    {
        if ($id === '') {
            $id = $this->attributes['id'];
        }
        $info = new UserAdditionalInfo();
        $infos = $info->where('owner', $id)->pluck('value', 'key')->toArray();

        return $infos;
    }

    public function checkArray($key, $array)
    {
        $value = '';
        if (is_array($array)) {
            if (array_key_exists($key, $array)) {
                $value = $array[$key];
            }
        }

        return $value;
    }

    public function twitterLink()
    {
        $html = '';
        $info = $this->getExtraInfo();
        $username = $this->checkArray('username', $info);
        if ($username !== '') {
            $html = "<a href='https://twitter.com/".$username."' target='_blank'><i class='fa fa-twitter'> </i> Twitter</a>";
        }

        return $html;
    }

    public function name()
    {
        $first_name = $this->first_name;
        $last_name = $this->last_name;
        $name = $this->user_name;
        if ($first_name !== '' && $first_name !== null) {
            if ($last_name !== '' && $last_name !== null) {
                $name = $first_name.' '.$last_name;
            } else {
                $name = $first_name;
            }
        }

        return $name;
    }

    public function getFullNameAttribute()
    {
        return $this->name();
    }

    public function getFirstNameAttribute($value)
    {
        return strip_tags($value);
    }

    public function getLastNameAttribute($value)
    {
        return strip_tags($value);
    }

    public function getUserNameAttribute($value)
    {
        return strip_tags($value);
    }

    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = strip_tags($value);
    }

    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = strip_tags($value);
    }

    public function setUserNameAttribute($value)
    {
        $this->attributes['user_name'] = strip_tags($value);
    }

    /**
     * Whether this account is on the ban list. Only end users are banned: an agent or admin email
     * on the list must not lock staff out of the helpdesk.
     *
     * @return bool
     */
    public function isBanned()
    {
        return (int) $this->ban === 1 && !in_array($this->role, ['agent', 'admin'], true);
    }

    /**
     * Whether an email address is on the ban list (see isBanned()).
     *
     * @param string|null $email
     *
     * @return bool
     */
    public static function isBannedEmail($email)
    {
        if (!is_string($email) || trim($email) === '') {
            return false;
        }

        return static::where('email', '=', trim($email))->where('ban', '=', 1)
            ->whereNotIn('role', ['agent', 'admin'])->exists();
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
