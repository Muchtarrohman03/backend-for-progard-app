<?php

namespace App\Filament\Imports;

use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserImporter extends Importer
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('email')
                ->label('Email')
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255'])
                ->example('user@example.com'),

            ImportColumn::make('password')
                ->label('Password')
                ->rules(['nullable', 'min:8'])
                ->example('password123')
                ->fillRecordUsing(function (User $record, ?string $state): void {
                    $record->password = Hash::make($state ?? '123456');
                }),

            // Kolom-kolom profile: fillRecordUsing kosong agar tidak error
            // Data akan disimpan manual di afterSave()
            ImportColumn::make('name')
                ->label('Nama Pengguna')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('Budi Santoso')
                ->fillRecordUsing(fn() => null), // skip, tangani di afterSave

            ImportColumn::make('division_id')
                ->label('ID Divisi')
                ->requiredMapping()
                ->integer()
                ->rules(['required', 'exists:divisions,id'])
                ->example('1')
                ->fillRecordUsing(fn() => null), // skip, tangani di afterSave

            ImportColumn::make('gender')
                ->label('Jenis Kelamin')
                ->requiredMapping()
                ->rules(['required', 'in:male,female'])
                ->example('male')
                ->fillRecordUsing(fn() => null), // skip, tangani di afterSave

            ImportColumn::make('role')
                ->label('Peran')
                ->requiredMapping()
                ->rules(['required', 'exists:roles,name'])
                ->example('admin')
                ->fillRecordUsing(fn() => null), // skip, tangani di afterSave
        ];
    }

    public function resolveRecord(): ?User
    {
        return User::firstOrNew(['email' => $this->data['email']]);
    }

    protected function afterSave(): void
    {
        $this->record->profile()->updateOrCreate(
            ['user_id' => $this->record->id],
            [
                'name'        => $this->data['name'],
                'division_id' => $this->data['division_id'],
                'gender'      => $this->data['gender'],
            ]
        );

        if (!empty($this->data['role'])) {
            $role = \Spatie\Permission\Models\Role::where('name', $this->data['role'])->firstOrFail();
            $this->record->syncRoles([$role]);
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import pengguna selesai. ' . number_format($import->successful_rows) . ' pengguna berhasil diimpor.';

        if ($failedRows = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRows) . ' baris gagal.';
        }

        return $body;
    }
}
