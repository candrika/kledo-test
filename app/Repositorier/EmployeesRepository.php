<?php

namespace App\Repositorier;

use App\Contracts\EmployeesInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Validator;

// import model
use App\Models\Employee;
use App\Models\Overtime;
use App\Models\Setting;

// import custome rules
use App\Rules\Settings;
use Carbon\Carbon;
use Exception;

class EmployeesRepository
{
    protected $employee;
    protected $setting;

    public function __construct(Employee $employee, Setting $setting)
    {
        $this->employee = $employee;
        $this->setting  = $setting;
    }

    public function updateSetting($data)
    {
        $setting = new $this->setting;
        // dd($data);
        $validator = Validator::make($data, [
            'key' => 'required|in:overtime_method',
            'value' => ['required', 'int', new Settings]
        ]);


        if ($validator->fails()) {
            return response()->json([
                [$validator->errors()], 400
            ]);
        }

        // init data update
        $setting->key = $data['key'];
        $setting->value = $data['value'];

        DB::beginTransaction();
        try {

            $getSetting = $setting->get();

            foreach ($getSetting as $set) {
                $del = $setting::where('key', $set->key)->delete();
            }

            $setting->save();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Hooray, updating setting successfully'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Ops, Sorry failed to update setting',
                'err' => $e
            ], 400);
        }
    }

    public function getAllEmployees()
    {
        return $this->employee->all();
    }

    public function createEmployee(array $data)
    {
        // instantiation
        $employee = new $this->employee;

        // set validate parameter
        $validator = Validator::make($data, [
            'name' => 'required|string|min:2|unique:employees,name',
            'salary' => 'required|int|min:2000000|max:10000000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                $validator->errors()
            ], 400);
        }

        //init data saving
        $employee->name = $data['name'];
        $employee->salary = $data['salary'];

        try {
            DB::transaction(function () use ($employee) {
                $employee->save();
            });

            return response()->json([
                'success' => true,
                'message' => 'Hooray, saving employee successfully'
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ops, Sorry failed to save employee'
            ], 400);
        }
    }
    public function createOverTime($data)
    {
        $overtime = new Overtime;
        $validator = Validator::make($data, [
            'employee_id' => 'required',
            'date' => 'required|unique:overtimes,date',
            'time_started' => 'required|date_format:H:i|before:time_endded',
            'time_ended' => 'required|date_format:H:i|after:time_started'
        ]);

        if ($validator->fails()) {
            return response()->json([
                [$validator->errors()]
            ], 400);
        }

        $overtime->employee_id = $data['employee_id'];
        $overtime->date = $data['date'];
        $overtime->time_started = $data['time_started'];
        $overtime->time_ended = $data['time_ended'];

        try {
            DB::transaction(function () use ($overtime) {
                $overtime->save();
            });
            return response()->json([
                'success' => true,
                'message' => 'Hooray, saving overtime successfully'
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => true,
                'message' => 'Ops, Sorry failed to save overtime'
            ], 400);
        }
    }

    public function getCalcute($data)
    {

        $validator = Validator::make($data, [
            'date' => 'required|date_format:Y-m'
        ]);

        if ($validator->fails()) {
            return response()->json([
                $validator->errors()
            ], 400);
        }

        $setting = DB::table("settings")
            ->join('references', 'references.id', '=', 'settings.value')
            ->select("references.code", "references.name")->first();

        $enddate = date('Y-m-t', strtotime($data['date']));
        $startdate = date('Y-m-01', strtotime($data['date']));

        // $overtime = Overtime::whereBetween('date', [$startdate, $enddate])->get();
        $employee = Employee::all();

        $data = [];

        $i = 0;
        $j = 0;
        $sumDuration = 0;

        foreach ($employee as $value) {

            $data[$i]['id'] = $value->id;
            $data[$i]['name'] = $value->name;
            $data[$i]['salary'] = $value->salary;

            $overtime = Overtime::where('employee_id', $value->id)->whereBetween('date', [$startdate, $enddate]);
            if ($overtime->count() > 0) {

                foreach ($overtime->get() as $ot) {
                    # code...

                    $time_started = Carbon::parse($ot->time_started);
                    $time_ended = Carbon::parse($ot->time_ended);

                    $culDuration = $time_ended->diffInMinutes($time_started);

                    if ($culDuration <= 105) {
                        $duration = '1 jam';
                    } else {
                        $duration = $culDuration / 60 * 60;
                    }

                    $sumDuration += $culDuration;

                    $data[$i]['overtime'][$j]['id'] = $ot->id;
                    $data[$i]['overtime'][$j]['date'] = $ot->date;
                    $data[$i]['overtime'][$j]['time_started'] = $ot->time_started;
                    $data[$i]['overtime'][$j]['time_ended'] = $ot->time_ended;
                    $data[$i]['overtime'][$j]['duration'] = $duration;
                    $j++;
                }

                if ($setting->name == 'Fixed') {
                    $amount = 10000 * $sumDuration;
                } elseif ($setting->name == 'Salary / 173') {
                    $amount = ($value->salary / 173) * $sumDuration;
                }
                $data[$i]['overtime_duration_total'] = $sumDuration;
                $data[$i]['amount'] = $amount;
            } else {
                $data[$i]['overtime_duration_total'] = 0;
                $data[$i]['amount'] = 0;
            }

            $i++;
        }

        $i++;

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
