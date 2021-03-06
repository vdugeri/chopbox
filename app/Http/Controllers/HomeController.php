<?php

namespace ChopBox\Http\Controllers;

use ChopBox\ChopBox\Repository\CommentsRepository;
use Validator;
use ChopBox\User;
use ChopBox\Http\Requests;
use Illuminate\Support\Facades\Auth;
use ChopBox\Http\Requests\ProfileRequest;
use ChopBox\ChopBox\Repository\UserRepository;
use ChopBox\ChopBox\Repository\ChopsRepository;

class HomeController extends Controller
{

    /**
     * Show the dashboard to a logged in user
     *
     * @param UserRepository $userRepository
     * @param ChopsRepository $repository
     * @param CommentsRepository $commentRepo
     * @return \Illuminate\View\View
     */
    public function index(UserRepository $userRepository, ChopsRepository $chopsRepo, CommentsRepository $commentRepo)
    {
        $user = Auth::user();
        // Find and order the users that have the highest number of chops
        $topTen = $userRepository->topUsers();

        // Find followee ids
        $followeeIds = $userRepository->getFolloweeIds($user->id);

        // Get chops of logged-in user and that of those (s)he follows
        $chops = $chopsRepo->getChops($user, $followeeIds);

        return view('pages.homepage', compact('user', 'chops', 'topTen', 'chopsRepo', 'commentRepo'));
    }

    /**
     * Check if a user has completed the profile details.
     *
     * @param ProfileRequest $request
     *
     * @param UserRepository $userRepository
     *
     * @param ChopsRepository $chopRepository
     *
     * @return \Illuminate\View\View
     */
    public function firstProfile(ProfileRequest $request, UserRepository $userRepository, ChopsRepository $chopRepository)
    {
        $user = Auth::user();

        $this->saveUser($user, $request);

        // Find and order the users that have the highest number of chops
        $topTen = $userRepository->topUsers();

        // Find followee ids
        $followeeIds = $userRepository->getFolloweeIds($user->id);

        // Get chops of logged-in user and that of those (s)he follows
        $chops = $chopRepository->getChops($user, $followeeIds);

        //return view('homepage', compact('user', 'chops', 'topTen'));
        return redirect('/')->with(compact('user', 'chops', 'topTen'));
    }

    /**
     * Update user's profile immediately after registration
     *
     * @param User $user
     * @param ProfileRequest $request
     */
    protected function saveUser(User $user, ProfileRequest $request)
    {
        $user->profile_state = true;
        $user->firstname = trim($request ['firstname']);
        $user->lastname = trim($request ['lastname']);
        $user->location = trim($request ['location']);
        $user->about = trim($request ['about']);
        $user->gender = trim($request ['gender']);
        $user->best_food = trim($request ['best_food']);

        if (is_null($user->image_uri)) {
            $user->image_uri = trim($this->getGravatar($user));
        }

        $user->save();
    }

    /**
     * Get image from gravatar.com
     *
     * @param User $user
     * @return string
     */
    protected function getGravatar(User $user)
    {
        return "http://www.gravatar.com/avatar/" . md5(strtolower(trim($user->email))) . "?d=mm&s=120";
    }
}