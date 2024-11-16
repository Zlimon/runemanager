<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccountTypesEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Rules\AccountUsernameRule;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * @throws ValidationException
     */
    public function search(Request $request): JsonResponse
    {
        $request['account_types'] = array_map(function ($item) {
            return Str::replace([' ', '-'], '_', Str::lower($item));
        }, $request->get('account_types', []) ?? []);

        try {
            $username = $request->input('username');

            $rules = [];

            // Add the AccountUsernameRule only if the username is not empty
            // This is because the rule should only be applied if a username is being searched for
            if (! empty($username)) {
                $rules['username'] = [new AccountUsernameRule];
            }

            if (! empty($request->get('account_types'))) {
                $accountTypes = implode(',', array_values(AccountTypesEnum::returnAllAccountTypes()));

                $rules['account_types'] = ['array', 'in:'.$accountTypes];
            }

            $request->validate($rules);
        } catch (ValidationException $e) {
            throw $e;
        }

        $accountsQuery = $this->searchQuery($request->all());

        $perPage = $request->get('per_page', 16);

        $accounts = AccountResource::collection($accountsQuery->paginate($perPage));

        return response()->json([
            'data' => $accounts,
        ], 200);
    }

    private function searchQuery(array $request): Builder
    {
        $accounts = Account::query();

        if (isset($request['username'])) {
            $accounts->where('username', 'LIKE', '%'.$request['username'].'%');
        }

        if (isset($request['account_types']) && is_array($request['account_types']) && count($request['account_types']) > 0) {
            $accountTypes = [];

            $accountTypes = array_unique(array_merge($accountTypes, $request['account_types']));

            $accounts = $accounts->whereIn('account_type', $accountTypes);
        }

        if (isset($request['online']) && boolval($request['online']) === true) {
            $accounts = $accounts->where('online', true)->orderByDesc('online');
        }

        $accounts->orderByDesc('created_at')->orderByDesc('id');

        return $accounts;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Account $account): JsonResponse
    {
        try {
            $compressedData = base64_decode($request['message']);

            $decompressedData = gzdecode($compressedData);

            if ($decompressedData === false) {
                throw new Exception('Failed to decompress data');
            }

            $data = json_decode($decompressedData);

            if (isset($data->equipment)) {
                $equipmentController = new EquipmentController;

                return $equipmentController->update($data->equipment, $account);
            }

            return response()->json([
                'data' => $account,
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the account. Message: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
