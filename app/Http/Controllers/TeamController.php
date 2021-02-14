<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Laravel\Spark\Events\Teams\DeletingTeam;
use Laravel\Spark\Events\Teams\TeamDeleted;
use Laravel\Spark\Events\Teams\TeamMemberRemoved;
use Laravel\Spark\Interactions\Settings\Teams\CreateTeam;
use Laravel\Spark\Interactions\Settings\Teams\SendInvitation;
use Laravel\Spark\Invitation;
use Laravel\Spark\Repositories\TeamRepository;
use Laravel\Spark\Spark;

class TeamController extends Controller
{
    public function show(Request $request, $teamId)
    {
        $team = Spark::interact(TeamRepository::class . '@find', [$teamId]);

        abort_unless($request->user()->onTeam($team), 404);

        if ($request->user()->ownsTeam($team)) {
            $team->load('subscriptions');

            $team->shouldHaveOwnerVisibility();
        }

        $team->invitations = $team->invitations;

        return $team;
    }

    /**
     * Remove a user from team
     *
     * @param Request $request
     * @param $teamId
     * @param $userId
     * @return array
     */
    public function deleteUser(Request $request, $teamId, $userId)
    {
        $team = Spark::interact(TeamRepository::class . '@find', [$teamId]);
        abort_unless($request->user()->ownsTeam($team), 404);
        $team->users()->detach($userId);
        $member = User::findOrFail($userId);
        event(new TeamMemberRemoved($team, $member));
        return ['message' => 'ok'];
    }

    public function destroy(Request $request, $teamId)
    {
        $team = Spark::interact(TeamRepository::class . '@find', [$teamId]);

        if (!$request->user()->ownsTeam($team)) {
            abort(404);
        }

        event(new DeletingTeam($team));

        $team->detachUsersAndDestroy();

        event(new TeamDeleted($team));
    }

    public function store(Request $request)
    {
        if (!Spark::createsAdditionalTeams()) {
            abort(404);
        }

        $params = $request->all();
        $params['slug'] = self::createSlug($request->input('name'));

        return Spark::interact(CreateTeam::class, [
            $request->user(), $params
        ]);
    }

    public static function createSlug($str, $delimiter = '-')
    {
        $slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));
        return $slug;
    }

    public function update(Request $request, $teamId)
    {
        $team = Spark::interact(TeamRepository::class . '@find', [$teamId]);

        abort_unless($request->user()->ownsTeam($team), 404);

        $this->validate($request, [
            'name' => 'required|max:255',
        ]);

        $team->forceFill([
            'name' => $request->name,
        ])->save();
    }

    public function invite(Request $request, $teamId)
    {
        $this->validate($request, [
            'email' => 'required|email'
        ]);

        $team = Spark::interact(TeamRepository::class . '@find', [$teamId]);
        $email = $request->input('email');

        abort_unless($request->user()->ownsTeam($team), 404);

        Spark::interact(SendInvitation::class, [$team, $email]);

        return ['email' => $email];
    }

    public function deleteInvite(Request $request, $teamId)
    {
        $this->validate($request, [
            'email' => 'required|email'
        ]);

        $team = Spark::interact(TeamRepository::class . '@find', [$teamId]);
        $email = $request->input('email');

        abort_unless($request->user()->ownsTeam($team), 404);

        $invitation = Invitation::where('email', $email)
            ->where('team_id', $team->id)
            ->delete();
    }
}
