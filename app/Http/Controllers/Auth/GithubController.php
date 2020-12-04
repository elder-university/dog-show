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
        $githubUser = Socialite::driver('github')->user();

        try {
            $user = $this->findGithubUser($githubUser);
        } catch (ModelNotFoundException $exception) {
            try {
                $user = $this->addGithubIdToExistingUser($githubUser);
            } catch (ModelNotFoundException $exception) {
                $user = $this->createNewUser($githubUser);
            }
        }

        Auth::login($user);

        return redirect(route('index'));
    }

    /**
     * @param SocialiteUser $githubUser
     * @return User
     */
    private function createNewUser(SocialiteUser $githubUser): User
    {
        return User::create([
            'name' => $githubUser->getName(),
            'email' => $githubUser->getEmail(),
            'password' => Hash::make(Str::random(32)),
            'github_id' => $githubUser->getId()
        ]);
    }

    /**
     * @param SocialiteUser $githubUser
     * @throws ModelNotFoundException
     * @return User
     */
    private function addGithubIdToExistingUser(SocialiteUser $githubUser): User
    {
        /** @var User $user */
        $user = User::query()->where('email', $githubUser->getEmail())->firstOrFail();

        $user->update(['github_id' => $githubUser->getId()]);

        return $user;
    }

    /**
     * @param SocialiteUser $githubUser
     * @throws ModelNotFoundException
     * @return User|Model
     */
    private function findGithubUser(SocialiteUser $githubUser): User
    {
        return User::query()
            ->where('email', $githubUser->getEmail())
            ->where('github_id', $githubUser->getId())
            ->firstOrFail();
    }
}
