<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GithubController extends Controller
{
    /**
     * @return RedirectResponse
     */
    public function redirect()
    {
        return Socialite::driver('github')->redirect();
    }

    /**
     * @return RedirectResponse
     */
    public function callback()
    {
        $githubUser = Socialite::driver('github')->user();

        $user = $this->findOrCreateUser($githubUser);

        Auth::login($user);

        return redirect(route('index'));
    }

    /**
     * @param \Laravel\Socialite\Contracts\User $githubUser
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|Authenticatable
     */
    private function findOrCreateUser(\Laravel\Socialite\Contracts\User $githubUser)
    {
        try {
            return User::query()
                ->where('email', $githubUser->getEmail())
                ->where('github_id', $githubUser->getId())
                ->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            return User::create([
                'name' => $githubUser->getName(),
                'email' => $githubUser->getEmail(),
                'password' => Hash::make(Str::random(32)),
                'github_id' => $githubUser->getId()
            ]);
        }
    }
}
