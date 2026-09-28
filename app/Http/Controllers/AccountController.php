<?php

namespace App\Http\Controllers;

use App\Enums\AccountTypesEnum;
use App\Http\Requests\FilterAccountsRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function index(FilterAccountsRequest $request): Response
    {
        $allAccountTypes = array_values(AccountTypesEnum::returnAllAccountTypes());
        $validated = $request->validated();

        $query = Account::query()->with('user')->searchUsername($validated['username'] ?? null);

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
}
