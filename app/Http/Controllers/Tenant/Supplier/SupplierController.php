<?php

namespace App\Http\Controllers\Tenant\Supplier;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Supplier\SupplierRequest;
use App\Services\Tenant\Supplier\SupplierService;
use Barryvdh\DomPDF\Facade\Pdf;

class SupplierController extends Controller
{
    protected $supplier;

    public function __construct(SupplierService $supplier)
    {
        $this->supplier = $supplier;
    }

    public function index(Request $request)
    {
        return $this->supplier->paginate($request, 25);
    }

    public function search(Request $request)
    {
        return $this->supplier->search($request, 10);
    }

    public function store(SupplierRequest $request)
    {
        $supplier = $this->supplier->store($request->validated());
        if ($supplier)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function show(Request $request, $id)
    {
        $supplier = $this->supplier->find($id, true, $request->input('fiscal_year'));
        return response(['data' => $supplier], 200);
    }

    public function update(SupplierRequest $request, $id)
    {
        $supplier = $this->supplier->update($id, $request->validated());
        if ($supplier)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->supplier->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function exportPdf(Request $request)
    {
        $request->validate([
            'fiscal_year' => 'required|string',
            'supplier_ids' => 'required|array|min:1',
            'supplier_ids.*' => 'integer',
        ]);

        $url = route('supplier.export.pdf', [
            'fiscal_year' => $request->fiscal_year,
            'supplier_ids' => implode(',', $request->supplier_ids),
        ]);

        return response()->json([
            'message' => 'Supplier PDF URL generated successfully',
            'url' => $url,
        ], 200);
    }

    public function downloadExportPdf(Request $request)
    {
        $fiscalYear = $request->query('fiscal_year');

        $supplierIds = array_filter(
            explode(',', $request->query('supplier_ids', ''))
        );

        if (!$fiscalYear || empty($supplierIds)) {
            abort(422, 'fiscal_year and supplier_ids are required');
        }

        $data = $this->supplier->getExportData(
            $fiscalYear,
            $supplierIds
        );

        if (empty($data)) {
            abort(404, 'No supplier data found');
        }

        $pdf = Pdf::loadView('pdf.supplier-export', [
            'fiscalYear' => $fiscalYear,
            'suppliers' => $data,
        ]);

        $fileName = 'supplier_export_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->stream($fileName);
    }
}
