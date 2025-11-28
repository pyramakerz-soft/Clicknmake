<?php

namespace App\Imports;

use App\Models\Group;
use App\Models\Student;
use Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

use Str;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;


class StudentsImport implements ToModel, WithHeadingRow, SkipsOnFailure
{
    use SkipsFailures;

    protected $class;
    public $duplicateUsernames = [];

    public function __construct($classId)
    {
        $this->class = Group::findOrFail($classId);
    }

    public function model(array $row)
    {
        if (empty($row['asm_altalb_ballgh_alanglyzy'])) {
            return null;
        }
        $rawName = $row['asm_altalb_ballgh_alanglyzy'];
        $username = str_replace(' ', '_', preg_replace('/^=IFERROR\(.*?,\"(.*?)\"\)$/', '$1', $rawName));
        $existingStudent = Student::where('username', $username)->first();

        // if ($existingStudent) {
        //     if ($existingStudent->class_id) {
        //         $this->duplicateUsernames[] = $username;
        //         return null;
        //     } else {
        //         $existingStudent->update(['class_id' => $this->class->id]);
        //         return null;
        //     }
        // }


        $genderArabic = $row['algns'] ?? null;
        $gender = null;
        if ($genderArabic == 'ذكر') {
            $gender = 'Boy';
        } elseif ($genderArabic == 'انثى') {
            $gender = 'Girl';
        }

        $arabicName = $row['asm_altalb_ballgh_alaarby'] ?? null;
        $phone = $row['rkm_almobayl'] ?? null;

        $birthDate = null;


        if (!empty($row['tarykh_almylad']) && is_numeric($row['tarykh_almylad'])) {
            $birthDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tarykh_almylad'])->format('Y-m-d');
        }

        if ($existingStudent) {
            $this->duplicateUsernames[] = $username;
            $existingStudent->update([
                'class_id' => $this->class->id,
                'school_id' => $this->class->school_id,
                'stage_id' => $this->class->stage_id,
                'arabic_name' => $arabicName,
                'phone' => $phone,
                'birth_date' => $birthDate,
                'gender' => $gender,
            ]);
            return null;
        }
        $password = Str::random(8);
        if (Student::where('username', $username)->exists()) {
            return null;
        }
        $student = Student::create([
            'username' => $username,
            'password' => Hash::make($password),
            'plain_password' => $password,
            'gender' => $gender,
            'school_id' => $this->class->school_id,
            'stage_id' => $this->class->stage_id,
            'is_active' => 1,
            'image' => null,
            'class_id' => $this->class->id,
            'arabic_name' => $arabicName,
            'phone' => $phone,
            'birth_date' => $birthDate,
        ]);
        return $student;
    }
}
