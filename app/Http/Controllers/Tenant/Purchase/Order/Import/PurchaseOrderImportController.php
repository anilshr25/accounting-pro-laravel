<?php

namespace App\Http\Controllers\Tenant\Purchase\Order\Import;

use App\Http\Controllers\Controller;
use App\Imports\PurchaseOrderImport;
use App\Services\Tenant\Purchase\Order\PurchaseOrderService;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class PurchaseOrderImportController extends Controller
{
    protected PurchaseOrderService $purchaseOrderService;

    public function __construct(
        PurchaseOrderService $purchaseOrderService
    ) {
        $this->purchaseOrderService = $purchaseOrderService;
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new PurchaseOrderImport();

            Excel::import($import, $request->file('file'));

            $rows = $import->rows ?? collect();

            // Normalize Excel values before validation.
            $rows = $rows->map(function ($row) {
                $row = collect($row);

                $row['order_date'] = $this->normalizeExcelDate(
                    $row['order_date'] ?? null
                );

                $row['received_date'] = $this->normalizeExcelDate(
                    $row['received_date'] ?? null
                );

                $row['order_date_miti'] = $this->normalizeExcelDate(
                    $row['order_date_miti'] ?? null
                );

                $row['received_date_miti'] = $this->normalizeExcelDate(
                    $row['received_date_miti'] ?? null
                );

                $row['tax'] = $this->normalizeExcelNumber(
                    $row['tax'] ?? 0
                );

                return $row;
            });

            // Remove completely empty rows.
            $rows = $rows->filter(function ($row) {
                return collect($row)->filter(function ($value) {
                    return $value !== null &&
                        trim((string) $value) !== '';
                })->isNotEmpty();
            })->values();

            if ($rows->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The Excel file contains no data.',
                ], 422);
            }

            // Validate every row before importing anything.
            $errors = [];

            foreach ($rows as $index => $row) {
                $validator = Validator::make($row->toArray(), [
                    'supplier_id' => 'required|integer',
                    'purchase_invoice_number' => 'required|string|max:255',
                    'order_date' => 'required|date',
                    'order_date_miti' => 'nullable|string|max:20',
                    'received_date' => 'nullable|date',
                    'received_date_miti' => 'nullable|string|max:20',
                    'tax' => 'nullable|numeric|min:0',
                    'status' => 'required|string|max:50',
                    'received_by' => 'nullable|string|max:255',
                    'description' => 'required|string',
                    'quantity' => 'required|numeric|min:0.01',
                    'rate' => 'required|numeric|min:0',
                    'amount' => 'required|numeric|min:0',
                ]);

                if ($validator->fails()) {
                    $errors[] = [
                        // Excel row number (header is row 1).
                        'row' => $index + 2,
                        'invoice_number' =>
                        $row['purchase_invoice_number'] ?? null,
                        'errors' => $validator->errors()->all(),
                    ];
                }
            }

            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Excel validation failed. No purchase orders were imported.',
                    'errors' => $errors,
                ], 422);
            }

            // Group rows by invoice number.
            $groupedRows = $rows->groupBy(function ($row) {
                return trim((string) $row['purchase_invoice_number']);
            });

            $successCount = 0;
            $failed = [];

            foreach ($groupedRows as $invoiceNumber => $groupRows) {
                try {
                    DB::transaction(function () use ($groupRows) {

                        $firstRow = $groupRows->first();

                        // These fields must be identical for all rows
                        // belonging to the same purchase order.
                        $orderFields = [
                            'supplier_id',
                            'order_date',
                            'order_date_miti',
                            'received_date',
                            'received_date_miti',
                            'tax',
                            'status',
                            'received_by',
                        ];

                        foreach ($groupRows as $row) {
                            foreach ($orderFields as $field) {
                                $firstValue = $firstRow[$field] ?? null;
                                $currentValue = $row[$field] ?? null;

                                if ((string) $firstValue !== (string) $currentValue) {
                                    throw new \Exception(
                                        "Inconsistent {$field} for invoice number: " .
                                            $firstRow['purchase_invoice_number']
                                    );
                                }
                            }
                        }

                        // Build purchase order items.
                        $items = $groupRows->map(function ($row) {
                            return [
                                'description' => $row['description'],
                                'quantity' => (float) $row['quantity'],
                                'rate' => (float) $row['rate'],
                                'amount' => (float) $row['amount'],
                            ];
                        })->values()->toArray();

                        $subTotal = collect($items)->sum('amount');

                        // Tax is treated as a percentage, e.g. 13 = 13%.
                        $tax = (float) ($firstRow['tax'] ?? 0);

                        $total = $subTotal + ($subTotal * $tax / 100);

                        $data = [
                            'supplier_id' => $firstRow['supplier_id'],

                            'purchase_invoice_number' =>
                            $firstRow['purchase_invoice_number'],

                            'order_date' => $firstRow['order_date'],
                            'order_date_miti' =>
                            $firstRow['order_date_miti'] ?? null,

                            'received_date' =>
                            $firstRow['received_date'] ?? null,

                            'received_date_miti' =>
                            $firstRow['received_date_miti'] ?? null,

                            'tax' => $tax,
                            'sub_total' => $subTotal,
                            'total' => $total,

                            'status' => $firstRow['status'],

                            'received_by' =>
                            $firstRow['received_by'] ?? null,

                            // Passed to PurchaseOrderService::store()
                            // for syncing purchase order items.
                            'items' => $items,
                        ];

                        $this->purchaseOrderService->store($data);
                    });

                    $successCount++;
                } catch (Throwable $e) {
                    $failed[] = [
                        'purchase_invoice_number' => $invoiceNumber,
                        'message' => $e->getMessage(),
                    ];
                }
            }

            return response()->json([
                'success' => count($failed) === 0,
                'message' => 'Purchase order import completed.',
                'total_purchase_orders' => $groupedRows->count(),
                'imported' => $successCount,
                'failed_count' => count($failed),
                'failed' => $failed,
            ], count($failed) > 0 ? 207 : 200);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to import purchase orders.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Convert Excel date values into Y-m-d strings.
     */
    private function normalizeExcelDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject(
                    (float) $value
                )->format('Y-m-d');
            } catch (Throwable $e) {
                return (string) $value;
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (Throwable $e) {
            return (string) $value;
        }
    }

    /**
     * Convert Excel numeric values into numbers.
     */
    private function normalizeExcelNumber($value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if ($value instanceof DateTimeInterface) {
            return (float) ExcelDate::dateTimeToExcel($value);
        }

        if (is_string($value)) {
            $value = str_replace('%', '', trim($value));
        }

        return is_numeric($value) ? (float) $value : 0;
    }
}
