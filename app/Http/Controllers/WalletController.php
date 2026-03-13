<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Http\Requests\CreateWalletRequest;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function store(CreateWalletRequest $request)
    {

        $wallet = Wallet::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'currency' => $request->currency,
            'balance' => 0
        ]);

        return response()->json([
            "success" => true,
            "message" => "Wallet créé avec succès.",
            "data" => [
                "wallet" => $wallet
            ]
        ], 201);
    }
}
