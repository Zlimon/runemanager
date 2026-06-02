<?php

namespace App\Http\Controllers;

use App\Enums\AccountTypesEnum;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Rules\AccountUsernameRule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function index(Request $request): Response
    {
        $allAccountTypes = array_values(AccountTypesEnum::returnAllAccountTypes());

        $validated = $request->validate([
            'username' => ['nullable', 'string', new AccountUsernameRule],
            'account_types' => ['nullable', 'array'],
            'account_types.*' => ['string', 'in:'.implode(',', $allAccountTypes)],
            'per_page' => ['nullable', 'integer', 'min:4', 'max:64'],
        ]);

        $query = Account::query();

        if (! empty($validated['username'] ?? null)) {
            $query->where('username', 'LIKE', '%'.$validated['username'].'%');
        }

        if (! empty($validated['account_types'] ?? [])) {
            $normalized = array_map(
                fn (string $type): string => Str::replace([' ', '-'], '_', Str::lower($type)),
                $validated['account_types'],
            );
            $query->whereIn('account_type', $normalized);
        }

        $accounts = AccountResource::collection(
            $query->orderBy('username')->paginate($validated['per_page'] ?? 16)->withQueryString(),
        );

        return Inertia::render('Accounts/Index', [
            'accountTypes' => $allAccountTypes,
            'accounts' => $accounts,
            'filters' => [
                'username' => $validated['username'] ?? '',
                'account_types' => $validated['account_types'] ?? [],
                'per_page' => $validated['per_page'] ?? 16,
            ],
        ]);
    }

    public function show(Account $account): Response
    {
        return Inertia::render('Accounts/Show', [
            'account' => (new AccountResource($account->load('equipment')->append('skills')))->resolve(),
        ]);
    }
}
