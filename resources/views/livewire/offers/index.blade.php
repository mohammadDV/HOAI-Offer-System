<?php

declare(strict_types=1);

use App\Models\Offer;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] #[Title('Offers')] class extends Component
{
    public string $newTitle = '';

    public string $newClientName = '';

    public string $newNotes = '';

    /**
     * Get the offers
     *
     * @return Collection<int, Offer>
     */
    #[Computed]
    public function offers()
    {
        return Offer::query()->latest('id')->get();
    }

    /**
     * Create a new offer from the header form
     */
    public function createOffer(): void
    {
        $this->validate([
            'newTitle' => ['required', 'string', 'max:255'],
            'newClientName' => ['nullable', 'string', 'max:255'],
            'newNotes' => ['nullable', 'string', 'max:10000'],
        ], [
            'newTitle.required' => 'Title is required',
        ], [
            'newTitle' => 'title',
            'newClientName' => 'client name',
            'newNotes' => 'notes',
        ]);

        Offer::query()->create([
            'title' => $this->newTitle,
            'client_name' => $this->newClientName !== '' ? $this->newClientName : null,
            'notes' => $this->newNotes !== '' ? $this->newNotes : null,
        ]);

        $this->reset(['newTitle', 'newClientName', 'newNotes']);
        unset($this->offers);
    }
}; ?>

<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="xl">Offers</flux:heading>
            <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">Manage renovation and planning offers.</flux:text>
        </div>
    </div>

    <flux:card class="mb-8 p-6">
        <flux:heading size="lg" class="mb-4">Create offer</flux:heading>
        <form wire:submit="createOffer" class="space-y-4">
            <flux:field>
                <flux:label>Title</flux:label>
                <flux:input wire:model="newTitle" placeholder="Enter offer title" :invalid="$errors->has('newTitle')" />
                <flux:error name="newTitle" />
            </flux:field>
            <flux:field>
                <flux:label>Client name</flux:label>
                <flux:input wire:model="newClientName" placeholder="Client name (optional)" :invalid="$errors->has('newClientName')" />
                <flux:error name="newClientName" />
            </flux:field>
            <flux:field>
                <flux:label>Notes</flux:label>
                <flux:textarea wire:model="newNotes" placeholder="Internal notes (optional)" rows="3" :invalid="$errors->has('newNotes')" />
                <flux:error name="newNotes" />
            </flux:field>
            <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="createOffer">
                <span wire:loading.remove wire:target="createOffer">Create offer</span>
                <span wire:loading wire:target="createOffer">Saving…</span>
            </flux:button>
        </form>
    </flux:card>

    <div class="space-y-3">
        @forelse ($this->offers as $offer)
            <flux:card class="p-4" wire:key="offer-{{ $offer->id }}">
                <a href="{{ route('offers.show', $offer) }}" wire:navigate class="block rounded-lg outline-none ring-zinc-400 focus-visible:ring-2">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <flux:heading size="md">{{ $offer->title }}</flux:heading>
                            @if ($offer->client_name)
                                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ $offer->client_name }}</flux:text>
                            @endif
                        </div>
                        <flux:text class="text-sm text-zinc-500">Open →</flux:text>
                    </div>
                </a>
            </flux:card>
        @empty
            <flux:callout variant="neutral" icon="information-circle">
                No offers yet. Create one above.
            </flux:callout>
        @endforelse
    </div>
</div>
