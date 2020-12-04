<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
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
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function callback()
    {
        $githubUser = Socialite::driver('github')->user();

        $user = $this->findUser($githubUser);

        Auth::login($user);

        return redirect(route('index'));
    }

    /**
     * @param SocialiteUser $githubUser
     * @return User|Model
     */
    private function findUser(SocialiteUser $githubUser): User
    {
        try {
            return User::query()
                ->where('email', $githubUser->getEmail())
                ->where('github_id', $githubUser->getId())
                ->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            try {
                $user = User::query()
                    ->where('email', $githubUser->getEmail())
                    ->firstOrFail();

                $user->update(['github_id' => $githubUser->getId()]);

                return $user;
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
}
