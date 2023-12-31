<?php

namespace App\Http\Livewire;

use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;



class EmployeeCreateForm extends Component
{
    public $employees;
    public Collection $roles;
    public Collection $positions;

    public function mount()
    {
        $this->positions = Position::all();
        $this->roles = Role::all();
        $this->employees = [
            ['name' => '', 'email' => '', 'phone' => '', 'password' => '', 'role_id' => User::USER_ROLE_ID, 'position_id' => $this->positions->first()->id]
        ];
    }

    public function addEmployeeInput(): void
    {
        $this->employees[] = ['name' => '', 'email' => '', 'phone' => '', 'password' => '', 'role_id' => User::USER_ROLE_ID, 'position_id' => $this->positions->first()->id];
    }

    public function removeEmployeeInput(int $index): void
    {
        unset($this->employees[$index]);
        $this->employees = array_values($this->employees);
    }

    public function saveEmployees()
{
    $this->validate([
        'employees.*.name' => 'required',
        'employees.*.email' => 'required|email|unique:users,email',
        'employees.*.phone' => 'required|min:11|max:13|unique:users,phone',
        'employees.*.password' => '',
        'employees.*.role_id' => [
            'required',
            function ($attribute, $value, $fail) {
                $role = Role::find($value);
                $position = Position::find($this->employees[$this->findIndexOfAttribute($attribute)]['position_id']);

                if ($position && $position->name === 'Anggota' && $role && in_array($role->name, ['admin', 'operator'])) {
                    $fail('The selected role cannot be assigned for the "Anggota" position.');
                }

                if ($position && $position->name === 'Operator' && $role && in_array($role->name, ['tamu'])) {
                    $fail('The "tamu" role cannot be assigned for the "operator" position.');
                }
            },
        ],
        'employees.*.position_id' => 'required',
    ]);
    

    
        // cek apakah no. telp yang diinput unique
        $phoneNumbers = array_map(function ($employee) {
            return trim($employee['phone']);
        }, $this->employees);
        $uniquePhoneNumbers = array_unique($phoneNumbers);

        if (count($phoneNumbers) != count($uniquePhoneNumbers)) {
            // layar browser ke paling atas agar user melihat alert error
            $this->dispatchBrowserEvent('livewire-scroll', ['top' => 0]);
            return session()->flash('failed', 'Pastikan input No. Telp tidak mangandung nilai yang sama.');
        }

        // alasan menggunakan create alih2 mengunakan ::insert adalah karena tidak looping untuk menambahkan created_at dan updated_at
        $affected = 0;
        foreach ($this->employees as $employee) {
            if (trim($employee['password']) === '') $employee['password'] = '123';
            $employee['password'] = Hash::make($employee['password']);
            User::create($employee);
            $affected++;
        }

        redirect()->route('employees.index')->with('success', "Ada ($affected) data anggota yang berhasil ditambahkan.");
    }
    private function findIndexOfAttribute($attribute)
{
    preg_match('/\d+/', $attribute, $matches);
    return $matches[0];
}
    public function render()
    {
        return view('livewire.employee-create-form');
    }
}
