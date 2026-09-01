<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EditProfile extends Page
{
    protected static ?string $title = 'Pengaturan Profil';
    protected static string $view = 'filament.pages.edit-profile';
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?int $navigationSort = 999;

    public string $name = '';
    public string $email = '';
    public $foto = null;
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user) { abort(403); }
        $this->name = $user->name;
        $this->email = $user->email;
        $this->foto = $user->foto;
    }

    protected function getViewData(): array
    {
        $user = Auth::user();

        return [
            'user' => $user,
            'avatarUrl' => $user?->getFilamentAvatarUrl(),
            'initials' => $user?->initials ?? 'U',
        ];
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore(Auth::id())],
            'current_password' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        // Handle password change
        if (filled($this->password)) {
            if (! Hash::check($this->current_password, $user->password)) {
                $this->addError('current_password', 'Password saat ini tidak cocok.');

                return;
            }
            $user->password = Hash::make($this->password);
        }

        // Handle foto upload
        if ($this->foto instanceof UploadedFile) {
            // Delete old foto if exists
            if ($user->foto) {
                Storage::disk('public')->delete($user->foto);
            }
            $path = $this->foto->store('avatars', 'public');
            $user->foto = $path;
        }

        $user->name = $this->name;
        $user->email = $this->email;
        $user->save();

        // Refresh foto after save for avatar display
        $this->foto = $user->foto;

        $this->notify('success', 'Profil berhasil diperbarui.');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getNavigationLabel(): string
    {
        return 'Profil Saya';
    }
}
