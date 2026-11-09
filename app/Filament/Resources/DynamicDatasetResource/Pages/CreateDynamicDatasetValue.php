<?php

namespace App\Filament\Resources\DynamicDatasetResource\Pages;

use App\Filament\Pages\KirimDataSektoralOpd;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\DynamicDatasetResource;
use App\Models\Dataset;
use App\Services\DataInput\DatasetValueManager;
use App\Services\DataInput\DatasetFieldResolver;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateDynamicDatasetValue extends CreateRecord
{
    protected static string $resource = DynamicDatasetResource::class;

    public ?Dataset $activeDataset = null;

    public function mount(): void
    {
        $kode = request('dataset');
        $user = Auth::user();
        if ($kode) {
            $this->activeDataset = Dataset::with(['fields', 'opd'])
                ->where('kode', $kode)
                ->when($user && ! ($user->isSuperAdmin() || $user->isWalidata()), fn ($query) => $query->where('opd_id', $user->opd_id ?? -1))
                ->first();
        }
        abort_unless($this->activeDataset && $this->activeDataset->is_active, 404);
        abort_unless($user && ($user->isSuperAdmin() || $user->isWalidata() || $user->opd_id === $this->activeDataset->opd_id), 403);
        parent::mount();
    }

    public function getTitle(): string
    {
        return 'Input: ' . ($this->activeDataset?->nama ?? 'Data Sektoral');
    }

    public function getSubheading(): ?string
    {
        if (! $this->activeDataset) return null;
        $opd = $this->activeDataset->opd?->nama ?? '-';
        $desc = $this->activeDataset->deskripsi ?? '';
        return "OPD: {$opd} | {$desc}";
    }

    public function getCancelButtonUrl(): string
    {
        $dataset = $this->activeDataset;
        if ($dataset) {
            return route('filament.admin.resources.dataset-values.index', ['dataset' => $dataset->kode]);
        }
        return parent::getCancelButtonUrl();
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Data berhasil disimpan sebagai draft';
    }

    protected function getRedirectUrl(): string
    {
        return KirimDataSektoralOpd::getUrl();
    }

    public function form(Schema $schema): Schema
    {
        abort_unless($this->activeDataset && $this->activeDataset->is_active, 404);

        return $schema->components(DatasetFieldResolver::buildFormSchema($this->activeDataset));
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Inject nilai otomatis
        $manager = app(DatasetValueManager::class);
        return $manager->createDraft($this->activeDataset, $data);
    }
}
