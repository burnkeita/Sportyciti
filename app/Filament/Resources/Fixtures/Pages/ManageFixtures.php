<?php

namespace App\Filament\Resources\Fixtures\Pages;

use App\Filament\Resources\Fixtures\FixtureResource;
use Filament\Resources\Pages\ManageRecords;

class ManageFixtures extends ManageRecords
{
    protected static string $resource = FixtureResource::class;
}
