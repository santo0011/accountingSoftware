<?php

namespace App\Services;

use App\Models\Service;
use App\Support\Html;
use App\Support\SiteCache;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Saves a service together with its documents, form fields, FAQs and process steps. */
class ServiceCatalogService
{
    public function save(Service $service, array $data): Service
    {
        return DB::transaction(function () use ($service, $data) {
            $attributes = Arr::except($data, ['documents', 'fields', 'faqs', 'steps', 'who_needs_text', 'benefits_text', 'image', 'remove_image']);

            // Card / hero image on the public disk.
            if (($data['image'] ?? null) instanceof UploadedFile) {
                if ($service->image) {
                    Storage::disk('public')->delete($service->image);
                }
                $attributes['image'] = $data['image']->store('services', 'public');
            } elseif (! empty($data['remove_image']) && $service->image) {
                Storage::disk('public')->delete($service->image);
                $attributes['image'] = null;
            }
            $attributes['full_description'] = Html::clean($attributes['full_description'] ?? null);
            $attributes['who_needs'] = $this->lines($data['who_needs_text'] ?? '');
            $attributes['benefits'] = $this->lines($data['benefits_text'] ?? '');
            $attributes['recurring_interval'] = ($attributes['billing_type'] ?? 'one_time') === 'recurring' ? ($attributes['recurring_interval'] ?? 'monthly') : null;
            $attributes['compliance_type_id'] = $attributes['billing_type'] === 'recurring' ? ($attributes['compliance_type_id'] ?? null) : null;
            $attributes['slug'] = Str::slug($attributes['slug'] ?: $attributes['name']);
            $attributes['sort_order'] ??= 0;

            $service->fill($attributes)->save();

            $this->syncRows($service, 'documents', $data['documents'] ?? [], fn ($row, $i) => [
                'name' => $row['name'], 'description' => $row['description'] ?? null,
                'is_mandatory' => (bool) ($row['is_mandatory'] ?? false), 'sort_order' => $i,
            ]);

            $this->syncRows($service, 'fields', $data['fields'] ?? [], fn ($row, $i) => [
                'label' => $row['label'],
                'name' => Str::snake(Str::slug($row['name'] ?: $row['label'], '_')),
                'type' => $row['type'],
                'options' => $row['type'] === 'select' ? $this->lines(str_replace(',', "\n", $row['options'] ?? '')) : null,
                'is_required' => (bool) ($row['is_required'] ?? false),
                'placeholder' => $row['placeholder'] ?? null,
                'sort_order' => $i,
            ]);

            $this->syncRows($service, 'faqs', $data['faqs'] ?? [], fn ($row, $i) => [
                'question' => $row['question'], 'answer' => $row['answer'], 'sort_order' => $i,
            ]);

            $this->syncRows($service, 'steps', $data['steps'] ?? [], fn ($row, $i) => [
                'title' => $row['title'], 'description' => $row['description'] ?? null,
                'duration' => $row['duration'] ?? null, 'sort_order' => $i,
            ]);

            SiteCache::flush();

            return $service;
        });
    }

    /**
     * Update existing child rows by id, create new ones, delete removed ones.
     * Keeping ids stable preserves links from uploaded application documents.
     */
    private function syncRows(Service $service, string $relation, array $rows, callable $map): void
    {
        $keep = [];
        foreach (array_values($rows) as $i => $row) {
            $values = $map($row, $i);
            $model = ! empty($row['id']) ? $service->$relation()->find($row['id']) : null;
            $model ? $model->update($values) : $model = $service->$relation()->create($values);
            $keep[] = $model->id;
        }

        $service->$relation()->whereNotIn('id', $keep)->delete();
    }

    private function lines(string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $text))->map(fn ($l) => trim($l))->filter()->values()->all();
    }
}
