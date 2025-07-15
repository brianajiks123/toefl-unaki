<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportStudents implements FromCollection, WithHeadings, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return User::select('name', 'email', 'email_verified_at', 'created_at', 'updated_at')
            ->where('is_admin', 0)
            ->get()
            ->map(function ($user) {
                $user->email_verified_at = $user->email_verified_at ? $user->email_verified_at->format('d-m-Y') : '';
                $user->created_at = $user->created_at->format('d-m-Y');
                $user->updated_at = $user->updated_at->format('d-m-Y');

                return $user;
            });
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Email Verified At',
            'Created At',
            'Updated At'
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('C2:C' . ($event->sheet->getHighestRow()))
                    ->getNumberFormat()
                    ->setFormatCode('dd-mm-yyyy');
                $event->sheet->getStyle('D2:D' . ($event->sheet->getHighestRow()))
                    ->getNumberFormat()
                    ->setFormatCode('dd-mm-yyyy');
                $event->sheet->getStyle('E2:E' . ($event->sheet->getHighestRow()))
                    ->getNumberFormat()
                    ->setFormatCode('dd-mm-yyyy');
            },
        ];
    }
}
