<?php namespace App\Observers;

use App\User;
use App\UserSettings;

/**
 * Class UserObserver
 * @package App\Observers
 */
class UserObserver
{
    /**
     * Created model
     *
     * @param User $user
     */
    public function created(User $user)
    {
        UserSettings::createDefaultSettings($user);
    }

    /**
     * Updated model
     *
     * @param User $user
     */
    public function updated(User $user)
    {

    }

    /**
     * Deleted model
     *
     * @param User $user
     */
    public function deleted(User $user)
    {

    }

}
