<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Applicant;
use App\Models\User;

class ApiController extends Controller
{
    /**
     * Handle the checkup API endpoint
     */
    public function checkup(Request $request)
    {
        // Validate the cards in the JSON request object
        $validator = Validator::make($request->all(), [
            'cards' => 'required|array|min:1',
            'cards.*.card_number' => 'required|integer|min:1',
            'cards.*.yearPaid' => 'required|integer|min:2000|max:' . (date('Y') + 1),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        // Process each card
        $results = [];

        foreach ($request->input('cards') as $memberData) {
            $card = $memberData['card_number'];
            $yearPaid = $memberData['yearPaid'];

            try {
                // Check if member already exists
                $existingMember = DB::table('paidup')
                    ->where('Card', $card)
                    ->first();

                if ($existingMember) {
                    // Update existing record
                    DB::table('paidup')
                        ->where('Card', $card)
                        ->where('YearPaid', $yearPaid)
                        ->update(['updated_at' => now()]);

                    $results[] = [
                        'Card' => $card,
                        'YearPaid' => $yearPaid,
                        'action' => 'updated',
                        'status' => 'success'
                    ];
                } else {
                    // Insert new record
                    DB::table('paidup')->insert([
                        'Card' => $card,
                        'YearPaid' => $yearPaid,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    $results[] = [
                        'Card' => $card,
                        'YearPaid' => $yearPaid,
                        'action' => 'inserted',
                        'status' => 'success'
                    ];
                }
            } catch (\Exception $e) {
                $results[] = [
                    'Card' => $card,
                    'YearPaid' => $yearPaid,
                    'action' => 'error',
                    'status' => 'failed',
                    'message' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'message' => 'Members processed successfully',
            'results' => $results
        ]);
    }

    public function getApplicants (Request $request) {
        $collectedRecords = [];
        $res = Applicant::where('processed', 0)->whereNotNull('transaction_id')->get();
        foreach ($res as $applicant) {
            $collectedRecords[] = encrypt($applicant->toJson());
        }

        return response()->json($collectedRecords);
    }

    public function setProcessed(Request $request) {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:applicants,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        $transactionIds = $request->input('ids');

        // Update the applicants to set processed to true
        Applicant::whereIn('id', $transactionIds)->update(['processed' => 1]);

        return response()->json([
            'message' => 'Applicants marked as processed successfully',
            'processed_ids' => $transactionIds
        ]);
    }

    public function createUser(Request $request) {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:191|unique:users,UserName',
            'password' => 'required|string|min:8',
            'role' => 'sometimes|required|string|in:user,admin,calendar,news',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'UserName' => $request->input('username'),
            'Password' => $request->input('password'),
            'Role' => $request->input('role', 'user'),
        ]);

        return response()->json([
            'message' => 'User created successfully.',
            'user' => [
                'id' => $user->id,
                'username' => $user->UserName,
                'role' => $user->role,
            ],
        ], 201);
    }

    public function updateUserPassword(Request $request, User $user) {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        DB::transaction(function () use ($request, $user) {
            $user->Password = $request->input('password');
            $user->save();
            $user->tokens()->delete();
        });

        return response()->json([
            'message' => 'User password updated successfully. The user must log in again.',
        ]);
    }
}
