<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function search(Request $request, int $page = 1, int $perPage = 10): JsonResponse
    {
        $accountsQuery = $this->searchQuery($request->all());

        $perPage = $request['per_page'] ?? $perPage;

        $accounts = AccountResource::collection($accountsQuery->with('user')->paginate($perPage));

        $request['page'] = $page;

        return response()->json([
            'data' => $accounts,
        ], 200);
    }

    /**
     * @param array $request
     * @return Builder
     */
    private function searchQuery(array $request): Builder
    {
        $accounts = Account::query();

        if (isset($request['search'])) {
            $accounts->where('username', 'LIKE', '%'.$request['search'].'%');
        }

        if (isset($request['account_type'])) {
            $accountTypes = [];

            if (is_array($request['account_type'])) {
                $accountTypes = array_merge($accountTypes, $request['account_type']);
            }

            $accountTypes = array_unique($accountTypes);

            $accounts = $accounts->whereIn('account_type', $accountTypes);
        }

        if (isset($request['online']) && boolval($request['online']) === true) {
            $accounts = $accounts->where('online', boolval($request['online']))->orderByDesc('online');
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
