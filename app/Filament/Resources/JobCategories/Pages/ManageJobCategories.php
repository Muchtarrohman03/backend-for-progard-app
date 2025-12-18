<?php

namespace App\Filament\Resources\JobCategories\Pages;

use App\Filament\Resources\JobCategories\JobCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageJobCategories extends ManageRecords
{
    protected static string $resource = JobCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
