<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GithubController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('github')->redirect();
    }

    public function callback()
    {
        $githubUser = Socialite::driver('github')->user();

        try {
            $user = User::query()
                ->where('email', $githubUser->getEmail())
                ->where('github_id', $githubUser->getId())
                ->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            try {
                $user = User::query()->where('email', $githubUser->getEmail())->firstOrFail();
                $user->update(['github_id' => $githubUser->getId()]);
            } catch (ModelNotFoundException $exception) {
                $user = User::create([
                    'name' => $githubUser->getName(),
                    'email' => $githubUser->getEmail(),
                    'password' => Hash::make(Str::random(32)),
                    'github_id' => $githubUser->getId()
                ]);
            }
        }

        Auth::login($user);

        return redirect(route('index'));
    }
}
