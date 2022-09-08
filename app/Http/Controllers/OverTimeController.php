<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResource;
use App\Contracts\EmployeesInterface;
use App\Repositorier\EmployeesRepository;


class OverTimeController extends Controller
{
    protected $employee;

    public function __construct(EmployeesRepository $employee)
    {
        $this->employee = $employee;
    }

    public function setting(Request $request)
    {
        // dd($request->all());
        $setting = $this->employee->updateSetting($request->all());
        return $setting;
    }

    public function saveEmployee(Request $request)
    {
        $employeeData = $this->employee->createEmployee($request->all());
        return $employeeData;
    }

    public function saveOvertime(Request $request)
    {
        return $this->employee->createOverTime($request->all());
    }

    public function overTimeCalculate(Request $request)
    {
        return $this->employee->getCalcute($request->all());
    }
}
