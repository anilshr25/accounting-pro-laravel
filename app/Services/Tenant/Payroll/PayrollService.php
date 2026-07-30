<?php

namespace App\Services\Tenant\Payroll;

use App\Models\Tenant\Salary\Salary;

class PayrollService
{
    public function getPayroll($month = null)
    {
        $query = Salary::with('employee');

        if ($month) {
            $query->where('month', $month);
        }

        $payroll = $query->get();

        return [
            'employees' => $payroll->map(function ($salary) {
                return [
                    'employee_id' => $salary->employee->id,
                    'employee_name' => $salary->employee->first_name . ' ' . $salary->employee->last_name,
                    'designation' => $salary->employee->designation,
                    'salary_month' => $salary->month,
                    'payment_method' => $salary->payment_method,
                    'payment_date' => $salary->payment_date,
                    'amount' => $salary->amount,
                    'status' => $salary->status,
                ];
            }),

            'total_payroll' => $payroll->sum('amount'),

            'total_employee' => $payroll->count(),
        ];
    }
}
