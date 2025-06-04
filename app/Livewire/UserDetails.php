<?php

namespace App\Livewire;

use Livewire\Component;

class UserDetails extends Component
{
    public $user;

    public $activeTab = 'overview';

    public function mount($user)
    {
        $this->user = $user;
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.user-details');
    }
}
