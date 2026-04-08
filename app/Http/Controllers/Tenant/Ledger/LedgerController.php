<?php

namespace App\Http\Controllers\Tenant\Ledger;

use App\Services\Tenant\Ledger\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Barryvdh\DomPDF\Facade\Pdf;

class LedgerController extends Controller
{
    protected $ledger;

    public function __construct(LedgerService $ledger)
    {
        $this->ledger = $ledger;
    }

    public function index(Request $request)
    {
        return $this->ledger->paginate($request, 25);
    }

    public function exportPdf(Request $request)
    {
        if (!$request->filled('party_type') || !$request->filled('party_id')) {
            return response()->json([
                'message' => 'party_type and party_id are required'
            ], 422);
        }

        $url = route('ledger.pdf', [
            'party' => $request->party_id,
            'party_type' => $request->party_type,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
        ]);

        return response()->json([
            'message' => 'PDF URL generated successfully',
            'url' => $url
        ]);
    }

    public function downloadPdf(Request $request, $partyId, $partyType)
    {
        $filters = [
            'party_id' => $partyId,
            'party_type' => $partyType,
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ];

        $ledgers = $this->ledger->getLedger((object)$filters);

        if ($ledgers->isEmpty()) {
            abort(404, 'No ledger data found');
        }

        $party = $ledgers->first()->party;

        $pdf = Pdf::loadView('pdf.ledger', [
            'ledgers' => $ledgers,
            'party' => $party,
            'partyType' => $partyType,
            'dateFrom' => $filters['date_from'],
            'dateTo' => $filters['date_to'],
        ]);

        $fileName = 'ledger_' . $party->id . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->stream($fileName);
    }
}
