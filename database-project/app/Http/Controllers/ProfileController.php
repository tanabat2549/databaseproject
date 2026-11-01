<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Member info page: name, email, join date, shortcuts and phone numbers.
     */
    public function show(): View
    {
        $user = Auth::user()->loadCount(['orders', 'reviews', 'wishlist']);

        $phones = $user->phones()->orderBy('created_at')->get();

        return view('profile.show', compact('user', 'phones'));
    }
}
