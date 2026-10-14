<?php

namespace App\Livewire\Forms;

use App\Models\Roles;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Validate;
use Livewire\Form;

class CreateUserForm extends Form
{
    #[Validate('required|string|min:3|max:100|regex:/^[a-zA-Z\s]+$/', as: 'name')]
    public $name = '';

    public $email = '';

    public $password = '';

    #[Validate(rule: 'required', as: 'password confirmation')]
    public $password_confirmation = '';

    #[Validate('required|exists:roles,role_name', as: 'role')]
    public $role = '';

    public $org_name = '';

    public $position = '';

    public $phone = '';

    public function rules()
    {
        $rules = [
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'phone' => 'required|regex:/^09\d{9}$/',
            'org_name' => [Rule::requiredIf(fn (): bool => $this->portal() === 'student-org'), 'exists:student__organizations,org_id'],
            'position' => [Rule::requiredIf(fn (): bool => $this->portal() === 'student-org'), 'exists:positions,position_id'],
        ];

        if ($this->portal() === 'student-org') {
            $rules['email'] .= '|ends_with:plv.edu.ph';
        }

        return $rules;
    }

    /**
     * The portal of the selected role, which decides the role-specific fields.
     */
    public function portal(): ?string
    {
        return Roles::portalForName($this->role);
    }

    public function messages()
    {
        return [
            'name.regex' => 'Name must only contain letters and spaces.',
            'email.ends_with' => 'student-org email must end with @plv.edu.ph',
            'phone.regex' => 'Contact number must be 11 digits and start with 09.',

            'name.required' => 'Name is required.',
            'name.string' => 'Name must be a string.',
            'name.min' => 'Name must be at least 3 characters.',
            'name.max' => 'Name cannot exceed 100 characters.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered.',

            'password.required' => 'Password is required.',
            'password confirmation.required' => 'Password confirmation is required.',
            'password confirmation.same' => 'Password confirmation does not match.',

            'role.required' => 'Role is required.',
            'role.exists' => 'Selected role is invalid.',
            'org_name.required' => 'Organization is required for student org role.',
            'org_name.exists' => 'Selected organization is invalid.',
            'position.required' => 'Position is required for student org role.',
            'position.exists' => 'Selected position is invalid.',
            'phone.required' => 'Contact number is required.',
        ];
    }
}
