<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class GithubController extends Controller
{
    /**
     * @return SymfonyRedirectResponse
     */
    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('github')->redirect();
    }

    /**
     * @return RedirectResponse
     */
    public function callback(): RedirectResponse
    {
        $socialiteUser = Socialite::driver('github')->user();

        try {
            $user = $this->findGithubUser($socialiteUser);
        } catch (ModelNotFoundException $exception) {
            try {
                $user = $this->addGithubIdToExistingUser($socialiteUser);
            } catch (ModelNotFoundException $exception) {
                $user = $this->createNewUser($socialiteUser);
            }
        }

        Auth::login($user);

        return redirect(route('index'));
    }

    /**
     * @param SocialiteUser $socialiteUser
     * @return User
     */
    private function createNewUser(SocialiteUser $socialiteUser): User
    {
        return User::create([
            'name' => $socialiteUser->getName(),
            'email' => $socialiteUser->getEmail(),
            'password' => Hash::make(Str::random(32)),
            'github_id' => $socialiteUser->getId()
        ]);
    }

    /**
     * @param SocialiteUser $socialiteUser
     * @return User
     *@throws ModelNotFoundException
     */
    private function addGithubIdToExistingUser(SocialiteUser $socialiteUser): User
    {
        /** @var User $user */
        $user = User::query()->where('email', $socialiteUser->getEmail())->firstOrFail();

        $user->update(['github_id' => $socialiteUser->getId()]);

        return $user;
    }

    /**
     * @param SocialiteUser $socialiteUser
     * @return User|Model
     *@throws ModelNotFoundException
     */
    private function findGithubUser(SocialiteUser $socialiteUser): User
    {
        return User::query()
            ->where('email', $socialiteUser->getEmail())
            ->where('github_id', $socialiteUser->getId())
            ->firstOrFail();
    }
}
