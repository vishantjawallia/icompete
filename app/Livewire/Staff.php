<?php

namespace App\Livewire;

use App\Models\Admin;
use App\Traits\LivewireTrait;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Staff extends Component
{
    use LivewireTrait;

    public $admins;

    public $name;

    public $email;

    public $phone;

    public $password;

    public $editingId = null;

    public $showForm = false;

    public function mount()
    {
        $this->loadAdmins();
    }

    public function loadAdmins()
    {
        $this->admins = Admin::all();
    }

    public function createModal()
    {
        $this->resetForm();

        $this->dispatch('show-form');
    }

    public function edit($id)
    {
        $admin = Admin::findOrFail($id);
        $this->editingId = $admin->id;
        $this->name = $admin->name;
        $this->email = $admin->email;
        $this->phone = $admin->phone;
        $this->dispatch('show-form');
    }

    public function save()
    {
        $validated = $this->validate([
            'name'     => 'required|string',
            'email'    => 'required|email|unique:admins,email,' . $this->editingId,
            'phone'    => 'nullable|string',
            'password' => $this->editingId ? 'nullable|min:8' : 'required|min:8',
        ]);

        if ($validated['password']) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        Admin::updateOrCreate(['id' => $this->editingId], $validated);

        $this->dispatch('hide-form');

        $this->success($this->editingId ? 'Staff updated successfully!' : 'Staff created successfully!');

        $this->resetForm();
        $this->loadAdmins();
    }

    public function delete($id)
    {
        $admin = Admin::findOrFail($id);

        if ($admin->role == 'super') {
            $this->error('Can not delete Super Admin');
        }
        $admin->delete();
        $this->loadAdmins();
        $this->success('Admin deleted successfully!');
    }

    public function resetForm()
    {
        $this->name = $this->email = $this->phone = $this->password = '';
        $this->editingId = null;
    }

    public function render()
    {
        return view('livewire.staff'); // blade file you showed
    }
}
