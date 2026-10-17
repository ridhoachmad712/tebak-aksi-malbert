<?php

namespace App\Livewire\Guru;

use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guru')]
#[Title('Ubah Kata Sandi')]
class Akun extends Component
{
    public string $sandiLama = '';

    public string $sandiBaru = '';

    public string $sandiBaru_confirmation = '';

    public function simpan(): void
    {
        $this->validate([
            'sandiLama' => ['required', 'current_password'],
            'sandiBaru' => ['required', 'confirmed', Password::min(8)],
        ], [], ['sandiLama' => 'kata sandi lama', 'sandiBaru' => 'kata sandi baru']);

        auth()->user()->update(['password' => $this->sandiBaru]);
        $this->reset();
        $this->dispatch('notifikasi', pesan: 'Kata sandi berhasil diubah');
    }

    public function render()
    {
        return view('livewire.guru.akun');
    }
}
