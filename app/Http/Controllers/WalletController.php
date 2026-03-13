<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Http\Requests\CreateWalletRequest;
use Illuminate\Http\Request;

class WalletController extends Controller
{

    public function index()
    {

        $wallets = auth()->user()->wallets;

        return response()->json([
            "success" => true,
            "message" => "Liste des wallets récupérée.",
            "data" => [
                "wallets" => $wallets
            ]
        ], 200);
    }
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
    public function show($id)
    {

        $wallet = Wallet::find($id);

        if (!$wallet) {
            return response()->json([
                "success" => false,
                "message" => "Wallet introuvable."
            ], 404);
        }

        if ($wallet->user_id != auth()->id()) {
            return response()->json([
                "success" => false,
                "message" => "Vous n'êtes pas autorisé à accéder à ce wallet."
            ], 403);
        }

        return response()->json([
            "success" => true,
            "message" => "Détail du wallet récupéré.",
            "data" => [
                "wallet" => $wallet
            ]
        ], 200);
    }
}
