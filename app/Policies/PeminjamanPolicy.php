<?php

namespace App\Policies;

use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class PeminjamanPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Peminjaman $peminjaman): bool
    {
        return $user->id === $peminjaman->user_id || $user->role === 'librarian';
    }

    public function create(User $user): bool
    {
        return $user->role === 'borrower' || $user->role === 'librarian';
    }

    public function update(User $user, Peminjaman $peminjaman): bool
    {
        return $user->role === 'librarian';
    }

    public function delete(User $user, Peminjaman $peminjaman): bool
    {
        return $user->role === 'librarian';
    }

    public function deleteAny(User $user): bool
    {
        return $user->role === 'librarian';
    }

    public function forceDelete(User $user, Peminjaman $peminjaman): bool
    {
        return $user->role === 'librarian';
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->role === 'librarian';
    }

    public function restore(User $user, Peminjaman $peminjaman): bool
    {
        return $user->role === 'librarian';
    }

    public function restoreAny(User $user): bool
    {
        return $user->role === 'librarian';
    }

    public function replicate(User $user, Peminjaman $peminjaman): bool
    {
        return $user->role === 'librarian';
    }

    public function reorder(User $user): bool
    {
        return $user->role === 'librarian';
    }
}
