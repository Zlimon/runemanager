<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminUserResource;
use App\Models\Account;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Jetstream\Contracts\DeletesUsers;
use Laravel\Sanctum\PersonalAccessToken;

class UserController extends Controller
{
    /** The list's status filters; anything else falls back to the first. */
    private const STATUSES = ['all', 'active', 'suspended', 'owners'];

    /**
     * Every website user, searchable by name or email. Counts and the last
     * token use come from subqueries, so a page costs the same few queries
     * however many users it lists. The filters are optional GET params read
     * directly: a bad value falls back to the default instead of failing.
     */
    public function index(Request $request): Response
    {
        $search = is_string($request->query('search')) ? mb_substr(trim($request->query('search')), 0, 255) : '';
        $status = in_array($request->query('status'), self::STATUSES, true) ? $request->query('status') : 'all';

        $users = User::query()
            ->with('roles:id,name')
            ->withCount(['account', 'tokens'])
            ->withMax('tokens', 'last_used_at')
            // lower() on both sides: LIKE is case-sensitive on PostgreSQL but
            // not on the SQLite the tests run on.
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->whereRaw('lower(name) like ?', ['%'.mb_strtolower($search).'%'])
                ->orWhereRaw('lower(email) like ?', ['%'.mb_strtolower($search).'%'])))
            ->when($status === 'active', fn (Builder $query) => $query->notSuspended())
            ->when($status === 'suspended', fn (Builder $query) => $query->suspended())
            ->when($status === 'owners', fn (Builder $query) => $query->owners())
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => AdminUserResource::collection($users),
            'filters' => ['search' => $search, 'status' => $status],
            'statuses' => self::STATUSES,
        ]);
    }

    public function show(Request $request, User $user): Response
    {
        $user->load(['roles:id,name', 'suspendedBy:id,name']);

        return Inertia::render('Admin/Users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_photo_url' => $user->profile_photo_url,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'two_factor_enabled' => $user->two_factor_confirmed_at !== null,
                'created_at' => $user->created_at?->toIso8601String(),
                'roles' => $user->roles->pluck('name')->all(),
                'suspension' => $user->isSuspended() ? [
                    'at' => $user->suspended_at->toIso8601String(),
                    'reason' => $user->suspension_reason,
                    'by' => $user->suspendedBy?->name,
                ] : null,
            ],
            'accounts' => $user->account()->orderBy('username')->get(['id', 'username', 'account_type'])
                ->map(fn (Account $account): array => [
                    'id' => $account->id,
                    'username' => $account->username,
                    'account_type' => $account->account_type?->value ?? $account->account_type,
                ])->all(),
            'tokens' => $user->tokens()->latest('id')->get(['id', 'name', 'last_used_at', 'created_at'])
                ->map(fn (PersonalAccessToken $token): array => [
                    'id' => $token->id,
                    'name' => $token->name,
                    'last_used_at' => $token->last_used_at?->toIso8601String(),
                    'created_at' => $token->created_at?->toIso8601String(),
                ])->all(),
            'roleOptions' => [Roles::OWNER, Roles::USER],
            'isSelf' => $user->is($request->user()),
            'isLastActiveOwner' => $user->isLastActiveOwner(),
        ]);
    }

    public function destroy(Request $request, User $user, DeletesUsers $deleter): RedirectResponse
    {
        abort_if($user->is($request->user()), 403, 'You cannot delete your own account here.');

        $deleter->delete($user);

        return redirect()->route('admin.users')->banner("Deleted {$user->name}.");
    }
}
