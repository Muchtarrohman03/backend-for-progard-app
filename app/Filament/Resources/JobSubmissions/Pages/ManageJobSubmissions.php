<?php

namespace App\Filament\Resources\JobSubmissions\Pages;

use App\Filament\Resources\JobSubmissions\JobSubmissionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageJobSubmissions extends ManageRecords
{
    protected static string $resource = JobSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
