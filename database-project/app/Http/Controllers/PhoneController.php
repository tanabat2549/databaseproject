<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PhoneController extends Controller
{
    /**
     * Add a phone number to the logged-in user's profile.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone_no' => [
                'required',
                'regex:/^0[0-9]{8,9}$/',
                // Same user cannot add the same number twice (PK is uid + phone_no)
                Rule::unique('user_phones', 'phone_no')->where('uid', Auth::id()),
            ],
        ], [
            'phone_no.regex' => 'Phone number must be 9-10 digits starting with 0.',
            'phone_no.unique' => 'You already added this phone number.',
        ]);

        Auth::user()->phones()->create($data);

        return redirect()->route('profile.show')->with('status', 'Phone number added.');
    }

    /**
     * Remove one of the logged-in user's phone numbers.
     */
    public function destroy(string $phoneNo): RedirectResponse
    {
        Auth::user()->phones()->where('phone_no', $phoneNo)->delete();

        return redirect()->route('profile.show')->with('status', 'Phone number removed.');
    }
}
