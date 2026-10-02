<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\JsonResponse;

/**
 * One random catalogue item in the item picker's `{id, name, icon}` shape,
 * for its "random icon" button on the register and profile pages.
 */
class RandomItemController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(Item::forPicker(Item::randomItemId()));
    }
}
