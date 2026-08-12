<?php

namespace App\Services\Tenant\Supplier;

use App\Models\Tenant\Supplier\Supplier;
use App\Http\Resources\Tenant\Supplier\SupplierResource;
use App\Models\Tenant\Ledger\Ledger;

class SupplierService
{
    protected $supplier;
    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }
    public function paginate($request, $limit = 25)
    {
        $fiscalYear = $request->input('fiscal_year');

        if (!$fiscalYear) {
            $todayMiti = now()->format('Y-m-d');

            $year = (int) substr($todayMiti, 0, 4);
            $month = (int) substr($todayMiti, 5, 2);

            if ($month >= 4) {
                $fiscalYear = $year . '/' . substr($year + 1, -2);
            } else {
                $fiscalYear = ($year - 1) . '/' . substr($year, -2);
            }
        }

        [$startYear] = explode('/', $fiscalYear);

        $startYear = (int) trim($startYear);

        $mitiFrom = "{$startYear}-04-01";
        $mitiUpto = ($startYear + 1) . "-03-31";
        $suppliers = $this->supplier
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($sub) use ($request) {
                    $info = $request->search;
                    $sub->where('name', 'like', "%{$info}%")
                        ->orWhere('email', 'like', "%{$info}%")
                        ->orWhere('phone', 'like', "%{$info}%")
                        ->orWhere('address', 'like', "%{$info}%")
                        ->orWhere('pan', 'like', "%{$info}%");
                });
            })
            ->when($request->filled('address'), function ($query) use ($request) {
                $query->where('address', 'like', "%{$request->address}%");
            })
            ->when($request->filled('pan'), function ($query) use ($request) {
                $query->where('pan', 'like', "%{$request->pan}%");
            })
            ->paginate($request->limit ?? $limit);

        $suppliers->getCollection()->transform(function ($supplier) use ($mitiFrom, $mitiUpto) {

            $openingLedger = Ledger::query()
                ->where('party_type', 'supplier')
                ->where('party_id', $supplier->id)
                ->whereNull('deleted_at')
                ->where('miti', '<', $mitiFrom)
                ->orderByDesc('miti')
                ->orderByDesc('id')
                ->first();

            $openingBalance = $openingLedger
                ? (float) $openingLedger->balance
                : (float) ($supplier->opening_balance ?? 0);

            $closingLedger = Ledger::query()
                ->where('party_type', 'supplier')
                ->where('party_id', $supplier->id)
                ->whereNull('deleted_at')
                ->whereBetween('miti', [$mitiFrom, $mitiUpto])
                ->orderByDesc('miti')
                ->orderByDesc('id')
                ->first();

            $closingBalance = $closingLedger
                ? (float) $closingLedger->balance
                : $openingBalance;

            $supplier->fiscal_opening_balance = number_format(
                $openingBalance,
                2,
                '.',
                ''
            );

            $supplier->fiscal_closing_balance = number_format(
                $closingBalance,
                2,
                '.',
                ''
            );

            return $supplier;
        });
        return SupplierResource::collection($suppliers);
    }

    public function search($request, $limit = 10)
    {
        $supplier = $this->supplier
            ->when($request->filled('info'), function ($query) use ($request) {
                $query->where(function ($sub) use ($request) {
                    $info = $request->info;
                    $sub->where('name', 'like', "%{$info}%")
                        ->orWhere('email', 'like', "%{$info}%")
                        ->orWhere('phone', 'like', "%{$info}%");
                });
            })
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get();
        return SupplierResource::collection($supplier);
    }

    public function store($data)
    {
        try {
            return $this->supplier->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false, $fiscalYear = null)
    {
        $supplier = $this->supplier->find($id);

        if (!$supplier) {
            return null;
        }

        if ($resource) {
            if (!$fiscalYear) {
                $todayMiti = now()->format('Y-m-d');

                $year = (int) substr($todayMiti, 0, 4);
                $month = (int) substr($todayMiti, 5, 2);

                if ($month >= 4) {
                    $fiscalYear = $year . '/' . substr($year + 1, -2);
                } else {
                    $fiscalYear = ($year - 1) . '/' . substr($year, -2);
                }
            }

            [$startYear] = explode('/', $fiscalYear);
            $startYear = (int) trim($startYear);

            $mitiFrom = "{$startYear}-04-01";
            $mitiUpto = ($startYear + 1) . "-03-31";

            $openingLedger = Ledger::query()
                ->where('party_type', 'supplier')
                ->where('party_id', $supplier->id)
                ->whereNull('deleted_at')
                ->where('miti', '<', $mitiFrom)
                ->orderByDesc('miti')
                ->orderByDesc('id')
                ->first();

            $openingBalance = $openingLedger
                ? (float) $openingLedger->balance
                : (float) ($supplier->opening_balance ?? 0);

            $closingLedger = Ledger::query()
                ->where('party_type', 'supplier')
                ->where('party_id', $supplier->id)
                ->whereNull('deleted_at')
                ->whereBetween('miti', [$mitiFrom, $mitiUpto])
                ->orderByDesc('miti')
                ->orderByDesc('id')
                ->first();

            $closingBalance = $closingLedger
                ? (float) $closingLedger->balance
                : $openingBalance;

            $supplier->fiscal_opening_balance = number_format(
                $openingBalance,
                2,
                '.',
                ''
            );

            $supplier->fiscal_closing_balance = number_format(
                $closingBalance,
                2,
                '.',
                ''
            );
        }

        return $resource
            ? new SupplierResource($supplier)
            : $supplier;
    }

    public function update($id, $data)
    {
        try {
            $supplier = $this->find($id);
            if (!$supplier) {
                return false;
            }
            return $supplier->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $supplier = $this->find($id);
            if (!$supplier) {
                return false;
            }
            return $supplier->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function getExportData($fiscalYear, $supplierIds)
    {
        [$startYear] = explode('/', $fiscalYear);

        $startYear = (int) trim($startYear);

        $mitiFrom = "{$startYear}-04-01";
        $mitiUpto = ($startYear + 1) . "-03-31";

        $suppliers = $this->supplier
            ->whereIn('id', $supplierIds)
            ->get();

        $data = [];

        foreach ($suppliers as $supplier) {

            $lastLedger = Ledger::query()
                ->where('party_type', 'supplier')
                ->where('party_id', $supplier->id)
                ->whereBetween('miti', [$mitiFrom, $mitiUpto])
                ->orderByDesc('miti')
                ->orderByDesc('id')
                ->first();

            $data[] = [
                'party_id' => $supplier->id,
                'party_name' => $supplier->name,
                'date' => $lastLedger?->date?->format('Y-m-d'),
                'miti' => $lastLedger?->miti
                    ? \Carbon\Carbon::parse($lastLedger->miti)->format('Y-m-d')
                    : null,
                'amount' => $lastLedger?->balance ?? '0.00',
            ];
        }

        return $data;
    }
}
