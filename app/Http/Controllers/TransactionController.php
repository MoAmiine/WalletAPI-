<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function deposit(Request $request, $id)
    {
        $wallet = Wallet::find($id);

        if (!$wallet) {
            return response()->json([
                "success" => "false",
                "message" => "Wallet introuvable."
            ], 404);
        }

        $amount = $request->amount;
        if ($amount <= 0) {
            return response()->json([
                "success" => false,
                "message" => "Erreur de validation.",
                "errors" => [
                    "amount" => ["Le montant doit être supérieur à 0."]
                ]
            ], 422);
        }
        $wallet->balance += $amount;

        $wallet->save();

        $transaction = Transaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'deposit',
            'amount' => $amount,
            'description' => $request->description,
            'balance_after' => $wallet->balance
        ]);

        return response()->json([
            "success" => true,
            "message" => "Dépôt effectué avec succès.",
            "data" => [
                "transaction" => $transaction,
                "wallet" => [
                    "id" => $wallet->id,
                    "name" => $wallet->name,
                    "currency" => $wallet->currency,
                    "balance" => $wallet->balance
                ]
            ]
        ]);
    }

    public function withdraw(Request $request, $id)
    {
        $wallet = Wallet::find($id);

        if (!$wallet) {
            return response()->json([
                "success" => false,
                "message" => "Wallet introuvable."
            ], 404);
        }
        $amount = $request->amount;

        if ($amount <= 0) {
            return response()->json([
                "success" => false,
                "message" => "Erreur de validation.",
                "errors" => [
                    "amount" => ["Le montant doit être supérieur à 0."]
                ]
            ], 422);
        }

        if ($wallet->balance < $amount) {
            return response()->json([
                "success" => false,
                "message" => "Solde insuffisant. Solde actuel : {$wallet->balance} {$wallet->currency}."
            ], 400);
        }

        $wallet->balance -= $amount;
        $wallet->save();

        $transaction = Transaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'withdraw',
            'amount' => $amount,
            'description' => $request->description,
            'balance_after' => $wallet->balance
        ]);

        return response()->json([
        "success" => true,
        "message" => "Retrait effectué avec succès.",
        "data" => [
            "transaction" => $transaction,
            "wallet" => [
                "id" => $wallet->id,
                "name" => $wallet->name,
                "currency" => $wallet->currency,
                "balance" => $wallet->balance
            ]
        ]
    ]);
}
}