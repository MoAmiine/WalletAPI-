<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

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

    public function transfer(Request $request, $id)
    {
        $senderWallet = Wallet::find($id);
        $receiverWallet = Wallet::find($request->receiver_wallet_id);

        if (!$senderWallet) {
            return response()->json([
                "success" => false,
                "message" => "Wallet introuvable."
            ], 404);
        }

        if (!$receiverWallet) {
            return response()->json([
                "success" => false,
                "message" => "Le wallet destinataire est introuvable."
            ], 404);
        }

        if ($senderWallet->currency !== $receiverWallet->currency) {
            return response()->json([
                "success" => false,
                "message" => "Transfert impossible : les deux wallets doivent avoir la même devise."
            ], 400);
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

        if ($senderWallet->balance < $amount) {
            return response()->json([
                "success" => false,
                "message" => "Solde insuffisant. Solde actuel : {$senderWallet->balance} {$senderWallet->currency}."
            ], 400);
        }

        DB::transaction(function () use ($senderWallet, $receiverWallet, $amount, $request) {

            $senderWallet->balance -= $amount;
            $senderWallet->save();

            $receiverWallet->balance += $amount;
            $receiverWallet->save();

            Transaction::create([
                'wallet_id' => $senderWallet->id,
                'type' => 'transfer_out',
                'amount' => $amount,
                'description' => $request->description,
                'receiver_wallet_id' => $receiverWallet->id,
                'balance_after' => $senderWallet->balance
            ]);

            Transaction::create([
                'wallet_id' => $receiverWallet->id,
                'type' => 'transfer_in',
                'amount' => $amount,
                'description' => $request->description,
                'sender_wallet_id' => $senderWallet->id,
                'balance_after' => $receiverWallet->balance
            ]);
        });

        return response()->json([
            "success" => true,
            "message" => "Transfert effectué avec succès.",
            "data" => [
                "wallet" => [
                    "id" => $senderWallet->id,
                    "name" => $senderWallet->name,
                    "currency" => $senderWallet->currency,
                    "balance" => $senderWallet->balance
                ]
            ]
        ]);
    }
}
