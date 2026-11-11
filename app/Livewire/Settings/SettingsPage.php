<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('الإعدادات')]
class SettingsPage extends Component
{
    public string $institutionName = '';

    public string $currency = '';

    public bool $saved = false;

    public function mount(): void
    {
        $this->institutionName = (string) Setting::get('institution_name', '');
        $this->currency = (string) Setting::get('currency', 'ل.ع');
    }

    public function save(): void
    {
        $this->validate([
            'institutionName' => ['required', 'string', 'max:200'],
            'currency' => ['required', 'string', 'max:20'],
        ]);

        Setting::set('institution_name', $this->institutionName);
        Setting::set('currency', $this->currency);

        $this->saved = true;
    }

    public function render(): View
    {
        return view('livewire.settings.settings-page');
    }
}
