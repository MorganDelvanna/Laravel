<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Applicant;

class ApiController extends Controller
{
    /**
     * Handle the checkup API endpoint
     */
    public function checkup(Request $request)
    {
        // Validate that input is a JSON array
        $validator = Validator::make($request->all(), [
            '*.Card' => 'required|integer|min:1',
            '*.YearPaid' => 'required|integer|min:2000|max:' . (date('Y') + 1),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        // Process each member in the array
        $results = [];

        foreach ($request->all() as $memberData) {
            $card = $memberData['Card'];
            $yearPaid = $memberData['YearPaid'];

            try {
                // Check if member already exists
                $existingMember = DB::table('paidup')
                    ->where('Card', $card)
                    ->where('YearPaid', $yearPaid)
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
}
