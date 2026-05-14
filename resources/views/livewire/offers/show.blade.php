<?php

declare(strict_types=1);

use App\Actions\CreateHoaiPositionAction;
use App\Actions\CreateManualPositionAction;
use App\Actions\UpdateHoaiPositionAction;
use App\Actions\UpdateManualPositionAction;
use App\Enums\HoaiRate;
use App\Enums\HoaiZone;
use App\Models\HoaiPosition;
use App\Models\Offer;
use App\Models\OfferGroup;
use App\Models\Position;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] #[Title('Offer')] class extends Component
{
    public Offer $offer;

    public string $offerTitle = '';

    public string $offerClientName = '';

    public string $offerNotes = '';

    public string $newGroupTitle = '';

    public int $newGroupSortOrder = 0;

    /** @var array<int, array{title: string, quantity: string, unit_price: string}> */
    public array $positionForms = [];

    /** @var array<int, array{title: string, costs: string, zone: string, rate: string, phases: array<int|string>, construction_markup: string, additional_costs: string, vat: string}> */
    public array $hoaiForms = [];

    /** @var array<int, array{title: string, quantity: string, unit_price: string}> */
    public array $manualPositions = [];

    /** @var array<int, array{title: string, costs: string, zone: string, rate: string, phases: array<int|string>, construction_markup: string, additional_costs: string, vat: string}> */
    public array $hoaiRows = [];

    /**
     * Mount the component.
     */
    public function mount(Offer $offer): void
    {
        $this->offer = $offer->load([
            'offerGroups' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')->with(['positions', 'hoaiPositions']),
        ]);
        $this->fillHeaderFromOffer();
        $this->initializeGroupForms();
        $this->populateRowForms();
    }

    /**
     * Fill the header from the offer.
     */
    public function fillHeaderFromOffer(): void
    {
        $this->offerTitle = (string) $this->offer->title;
        $this->offerClientName = (string) ($this->offer->client_name ?? '');
        $this->offerNotes = (string) ($this->offer->notes ?? '');
    }

    /**
     * @return array{title: string, quantity: string, unit_price: string}
     */
    private function emptyPositionDraft(): array
    {
        return [
            'title' => '',
            'quantity' => '1',
            'unit_price' => '',
        ];
    }

    /**
     * @return array{title: string, costs: string, zone: string, rate: string, phases: array<int|string>, construction_markup: string, additional_costs: string, vat: string}
     */
    private function emptyHoaiDraft(): array
    {
        return [
            'title' => '',
            'costs' => '',
            'zone' => HoaiZone::II->value,
            'rate' => HoaiRate::Middle->value,
            'phases' => [1],
            'construction_markup' => '0',
            'additional_costs' => '0',
            'vat' => '19',
        ];
    }

    /**
     * Initialize the group forms.
     */
    public function initializeGroupForms(): void
    {
        foreach ($this->offer->offerGroups as $group) {
            if (! isset($this->positionForms[$group->id])) {
                $this->positionForms[$group->id] = $this->emptyPositionDraft();
            }
            if (! isset($this->hoaiForms[$group->id])) {
                $this->hoaiForms[$group->id] = $this->emptyHoaiDraft();
            }
        }
    }

    /**
     * Populate the row forms.
     */
    public function populateRowForms(): void
    {
        $this->manualPositions = [];
        $this->hoaiRows = [];
        foreach ($this->offer->offerGroups as $group) {
            foreach ($group->positions as $position) {
                $this->manualPositions[$position->id] = [
                    'title' => (string) $position->title,
                    'quantity' => (string) $position->quantity,
                    'unit_price' => (string) $position->unit_price,
                ];
            }
            foreach ($group->hoaiPositions as $hoai) {
                $phases = $hoai->phases ?? [];
                $this->hoaiRows[$hoai->id] = [
                    'title' => (string) $hoai->title,
                    'costs' => (string) $hoai->costs,
                    'zone' => (string) $hoai->zone,
                    'rate' => (string) $hoai->rate,
                    'phases' => array_map('intval', (array) $phases),
                    'construction_markup' => (string) $hoai->construction_markup,
                    'additional_costs' => (string) $hoai->additional_costs,
                    'vat' => (string) $hoai->vat,
                ];
            }
        }
    }

    /**
     * Reload the offer.
     */
    public function reloadOffer(): void
    {
        $this->offer = $this->offer->fresh([
            'offerGroups' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')->with(['positions', 'hoaiPositions']),
        ]);
        $this->initializeGroupForms();
        $this->populateRowForms();
    }

    /**
     * Save the offer header.
     */
    public function saveOfferHeader(): void
    {
        $this->validate([
            'offerTitle' => ['required', 'string', 'max:255'],
            'offerClientName' => ['nullable', 'string', 'max:255'],
            'offerNotes' => ['nullable', 'string', 'max:10000'],
        ], [
            'offerTitle.required' => 'Title is required',
        ], [
            'offerTitle' => 'title',
            'offerClientName' => 'client name',
            'offerNotes' => 'notes',
        ]);

        $this->offer->update([
            'title' => $this->offerTitle,
            'client_name' => $this->offerClientName !== '' ? $this->offerClientName : null,
            'notes' => $this->offerNotes !== '' ? $this->offerNotes : null,
        ]);

        $this->reloadOffer();
        $this->fillHeaderFromOffer();
    }

    /**
     * Create a new group.
     */
    public function createGroup(): void
    {
        $this->validate([
            'newGroupTitle' => ['required', 'string', 'max:255'],
            'newGroupSortOrder' => ['required', 'integer', 'min:0'],
        ], [
            'newGroupTitle.required' => 'Title is required',
        ], [
            'newGroupTitle' => 'title',
            'newGroupSortOrder' => 'sort order',
        ]);

        OfferGroup::query()->create([
            'offer_id' => $this->offer->id,
            'title' => $this->newGroupTitle,
            'sort_order' => $this->newGroupSortOrder,
        ]);

        $this->reset(['newGroupTitle', 'newGroupSortOrder']);
        $this->reloadOffer();
    }

    /**
     * Delete a group.
     */
    public function deleteGroup(int $groupId): void
    {
        OfferGroup::query()->where('offer_id', $this->offer->id)->whereKey($groupId)->delete();
        unset($this->positionForms[$groupId], $this->hoaiForms[$groupId]);
        $this->reloadOffer();
    }

    /**
     * Create a new manual position.
     *
     * @param int $groupId
     * @param CreateManualPositionAction $createManualPosition
     */
    public function createPosition(int $groupId, CreateManualPositionAction $createManualPosition): void
    {
        $group = OfferGroup::query()->where('offer_id', $this->offer->id)->whereKey($groupId)->firstOrFail();

        $this->validate([
            "positionForms.$groupId.title" => ['required', 'string', 'max:255'],
            "positionForms.$groupId.quantity" => ['required', 'numeric', 'min:0'],
            "positionForms.$groupId.unit_price" => ['required', 'numeric', 'min:0'],
        ], [
            "positionForms.$groupId.title.required" => 'Title is required',
            "positionForms.$groupId.quantity.numeric" => 'Quantity must be a number',
            "positionForms.$groupId.unit_price.numeric" => 'Unit price must be a number',
        ], [
            "positionForms.$groupId.title" => 'title',
            "positionForms.$groupId.quantity" => 'quantity',
            "positionForms.$groupId.unit_price" => 'unit price',
        ]);

        $createManualPosition->execute($group, $this->positionForms[$groupId]);

        $this->positionForms[$groupId] = $this->emptyPositionDraft();
        $this->reloadOffer();
    }

    /**
     * Update a manual position.
     *
     * @param int $positionId
     * @param UpdateManualPositionAction $updateManualPosition
     */
    public function updateManualPosition(int $positionId, UpdateManualPositionAction $updateManualPosition): void
    {
        $position = Position::query()
            ->whereKey($positionId)
            ->whereHas('offerGroup', fn ($q) => $q->where('offer_id', $this->offer->id))
            ->firstOrFail();

        if (! isset($this->manualPositions[$positionId])) {
            $this->reloadOffer();

            return;
        }

        $this->validate([
            "manualPositions.$positionId.title" => ['required', 'string', 'max:255'],
            "manualPositions.$positionId.quantity" => ['required', 'numeric', 'min:0'],
            "manualPositions.$positionId.unit_price" => ['required', 'numeric', 'min:0'],
        ], [
            "manualPositions.$positionId.title.required" => 'Title is required',
        ], [
            "manualPositions.$positionId.title" => 'title',
            "manualPositions.$positionId.quantity" => 'quantity',
            "manualPositions.$positionId.unit_price" => 'unit price',
        ]);

        $updateManualPosition->execute($position, $this->manualPositions[$positionId]);

        $this->reloadOffer();
    }

    /**
     * Delete a manual position.
     *
     * @param int $positionId
     */
    public function deletePosition(int $positionId): void
    {
        Position::query()
            ->whereKey($positionId)
            ->whereHas('offerGroup', fn ($q) => $q->where('offer_id', $this->offer->id))
            ->delete();
        unset($this->manualPositions[$positionId]);
        $this->reloadOffer();
    }

    /**
     * Create a new HOAI position.
     *
     * @param int $groupId
     * @param CreateHoaiPositionAction $createHoaiPosition
     */
    public function createHoai(int $groupId, CreateHoaiPositionAction $createHoaiPosition): void
    {
        $group = OfferGroup::query()->where('offer_id', $this->offer->id)->whereKey($groupId)->firstOrFail();

        $this->validate([
            "hoaiForms.$groupId.title" => ['required', 'string', 'max:255'],
            "hoaiForms.$groupId.costs" => ['required', 'numeric', 'min:0'],
            "hoaiForms.$groupId.zone" => ['required', Rule::enum(HoaiZone::class)],
            "hoaiForms.$groupId.rate" => ['required', Rule::enum(HoaiRate::class)],
            "hoaiForms.$groupId.phases" => ['required', 'array', 'min:1'],
            "hoaiForms.$groupId.phases.*" => ['integer', 'between:1,9'],
            "hoaiForms.$groupId.construction_markup" => ['required', 'numeric', 'min:0'],
            "hoaiForms.$groupId.additional_costs" => ['required', 'numeric', 'min:0'],
            "hoaiForms.$groupId.vat" => ['required', 'numeric', 'between:0,100'],
        ], [
            "hoaiForms.$groupId.title.required" => 'Title is required',
            "hoaiForms.$groupId.costs.numeric" => 'Costs must be a number',
            "hoaiForms.$groupId.phases.required" => 'Select at least one phase',
            "hoaiForms.$groupId.phases.min" => 'Select at least one phase',
        ], [
            "hoaiForms.$groupId.title" => 'title',
            "hoaiForms.$groupId.costs" => 'costs',
            "hoaiForms.$groupId.zone" => 'zone',
            "hoaiForms.$groupId.rate" => 'rate',
            "hoaiForms.$groupId.phases" => 'phases',
            "hoaiForms.$groupId.vat" => 'VAT',
        ]);

        $createHoaiPosition->execute($group, $this->hoaiForms[$groupId]);

        $this->hoaiForms[$groupId] = $this->emptyHoaiDraft();
        $this->reloadOffer();
    }

    /**
     * Update a HOAI position.
     *
     * @param int $hoaiId
     * @param UpdateHoaiPositionAction $updateHoaiPosition
     */
    public function updateHoai(int $hoaiId, UpdateHoaiPositionAction $updateHoaiPosition): void
    {
        $hoai = HoaiPosition::query()
            ->whereKey($hoaiId)
            ->whereHas('offerGroup', fn ($q) => $q->where('offer_id', $this->offer->id))
            ->firstOrFail();

        if (! isset($this->hoaiRows[$hoaiId])) {
            $this->reloadOffer();

            return;
        }

        $this->validate([
            "hoaiRows.$hoaiId.title" => ['required', 'string', 'max:255'],
            "hoaiRows.$hoaiId.costs" => ['required', 'numeric', 'min:0'],
            "hoaiRows.$hoaiId.zone" => ['required', Rule::enum(HoaiZone::class)],
            "hoaiRows.$hoaiId.rate" => ['required', Rule::enum(HoaiRate::class)],
            "hoaiRows.$hoaiId.phases" => ['required', 'array', 'min:1'],
            "hoaiRows.$hoaiId.phases.*" => ['integer', 'between:1,9'],
            "hoaiRows.$hoaiId.construction_markup" => ['required', 'numeric', 'min:0'],
            "hoaiRows.$hoaiId.additional_costs" => ['required', 'numeric', 'min:0'],
            "hoaiRows.$hoaiId.vat" => ['required', 'numeric', 'between:0,100'],
        ], [
            "hoaiRows.$hoaiId.title.required" => 'Title is required',
            "hoaiRows.$hoaiId.costs.numeric" => 'Costs must be a number',
            "hoaiRows.$hoaiId.phases.required" => 'Select at least one phase',
            "hoaiRows.$hoaiId.phases.min" => 'Select at least one phase',
        ], [
            "hoaiRows.$hoaiId.title" => 'title',
            "hoaiRows.$hoaiId.costs" => 'costs',
            "hoaiRows.$hoaiId.zone" => 'zone',
            "hoaiRows.$hoaiId.rate" => 'rate',
            "hoaiRows.$hoaiId.phases" => 'phases',
            "hoaiRows.$hoaiId.vat" => 'VAT',
        ]);

        $updateHoaiPosition->execute($hoai, $this->hoaiRows[$hoaiId]);

        $this->reloadOffer();
    }

    /**
     * Delete a HOAI position.
     *
     * @param int $hoaiId
     */
    public function deleteHoai(int $hoaiId): void
    {
        HoaiPosition::query()
            ->whereKey($hoaiId)
            ->whereHas('offerGroup', fn ($q) => $q->where('offer_id', $this->offer->id))
            ->delete();
        unset($this->hoaiRows[$hoaiId]);
        $this->reloadOffer();
    }

    /**
     * Calculate the subtotal of a group.
     *
     * @param OfferGroup $group
     * @return string
     */
    public function groupSubtotal(OfferGroup $group): string
    {
        $sum = '0';
        foreach ($group->positions as $position) {
            $sum = bcadd($sum, (string) $position->total, 2);
        }
        foreach ($group->hoaiPositions as $hoai) {
            $sum = bcadd($sum, (string) $hoai->total, 2);
        }

        return bcadd($sum, '0', 2);
    }

    /**
     * Calculate the grand total of the offer.
     *
     * @return string
     */
    public function offerGrandTotal(): string
    {
        $sum = '0';
        foreach ($this->offer->offerGroups as $group) {
            $sum = bcadd($sum, $this->groupSubtotal($group), 2);
        }

        return bcadd($sum, '0', 2);
    }
}; ?>
<div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ route('offers.index') }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
            <flux:icon name="arrow-left" class="size-4 shrink-0" />
            All offers
        </a>
    </div>

    <div class="relative mb-8">
        <div wire:loading.delay class="absolute inset-0 z-10 flex flex-col gap-3 rounded-xl border border-zinc-200 bg-white/90 p-6 dark:border-white/10 dark:bg-zinc-950/90" wire:target="saveOfferHeader">
            <flux:skeleton class="h-8 w-2/3" />
            <flux:skeleton class="h-10 w-full" />
            <flux:skeleton class="h-10 w-full" />
            <flux:skeleton class="h-24 w-full" />
        </div>
        <flux:card class="p-6">
            <flux:heading size="lg" class="mb-4">Offer</flux:heading>
            <form wire:submit="saveOfferHeader" class="space-y-4">
                <flux:field>
                    <flux:label>Title</flux:label>
                    <flux:input wire:model="offerTitle" placeholder="Enter offer title" :invalid="$errors->has('offerTitle')" />
                    <flux:error name="offerTitle" />
                </flux:field>
                <flux:field>
                    <flux:label>Client name</flux:label>
                    <flux:input wire:model="offerClientName" placeholder="Client name (optional)" :invalid="$errors->has('offerClientName')" />
                    <flux:error name="offerClientName" />
                </flux:field>
                <flux:field>
                    <flux:label>Notes</flux:label>
                    <flux:textarea wire:model="offerNotes" placeholder="Internal notes" rows="3" :invalid="$errors->has('offerNotes')" />
                    <flux:error name="offerNotes" />
                </flux:field>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="saveOfferHeader">
                    <span wire:loading.remove wire:target="saveOfferHeader">Save offer</span>
                    <span wire:loading wire:target="saveOfferHeader">Saving…</span>
                </flux:button>
            </form>
        </flux:card>
    </div>

    <flux:card class="mb-8 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <flux:heading size="md">Totals</flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">Group subtotals and offer total update after each change.</flux:text>
            </div>
            <div class="text-right">
                <flux:text class="text-sm text-zinc-500">Offer total</flux:text>
                <flux:heading size="xl">€ {{ $this->offerGrandTotal() }}</flux:heading>
            </div>
        </div>
    </flux:card>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end">
        <flux:heading size="lg">Groups</flux:heading>
    </div>

    <flux:card class="mb-8 p-6">
        <flux:heading size="md" class="mb-4">Add group</flux:heading>
        <form wire:submit="createGroup" class="flex flex-col gap-4 sm:flex-row sm:items-end">
            <flux:field class="min-w-0 flex-1">
                <flux:label>Title</flux:label>
                <flux:input wire:model="newGroupTitle" placeholder="Group title" :invalid="$errors->has('newGroupTitle')" />
                <flux:error name="newGroupTitle" />
            </flux:field>
            <flux:field class="w-full sm:w-32">
                <flux:label>Sort</flux:label>
                <flux:input type="number" wire:model="newGroupSortOrder" placeholder="0" :invalid="$errors->has('newGroupSortOrder')" />
                <flux:error name="newGroupSortOrder" />
            </flux:field>
            <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="createGroup">
                <span wire:loading.remove wire:target="createGroup">Add group</span>
                <span wire:loading wire:target="createGroup">Adding…</span>
            </flux:button>
        </form>
    </flux:card>

    <div class="space-y-8">
        @foreach ($this->offer->offerGroups as $group)
            <div class="relative" wire:key="group-{{ $group->id }}">
                <div wire:loading.delay class="absolute inset-0 z-10 flex flex-col gap-2 rounded-xl border border-zinc-200 bg-white/90 p-4 dark:border-white/10 dark:bg-zinc-950/90" wire:target="createPosition({{ $group->id }}), createHoai({{ $group->id }}), deleteGroup({{ $group->id }})">
                    <flux:skeleton class="h-6 w-1/2" />
                    <flux:skeleton class="h-20 w-full" />
                    <flux:skeleton class="h-20 w-full" />
                </div>
                <flux:card class="overflow-hidden p-0">
                    <div class="flex flex-col gap-2 border-b border-zinc-200 bg-zinc-50 px-6 py-4 dark:border-white/10 dark:bg-white/5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <flux:heading size="md">{{ $group->title }}</flux:heading>
                            <flux:text class="text-sm text-zinc-500">Sort order {{ $group->sort_order }}</flux:text>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <flux:badge color="zinc">Group total € {{ $this->groupSubtotal($group) }}</flux:badge>
                            <flux:button size="sm" variant="danger" wire:click="deleteGroup({{ $group->id }})" wire:confirm="Delete this group and all its positions?" wire:loading.attr="disabled" wire:target="deleteGroup({{ $group->id }})">
                                Delete group
                            </flux:button>
                        </div>
                    </div>

                    <div class="space-y-8 p-6">
                        <div>
                            <flux:heading size="sm" class="mb-3">Manual positions</flux:heading>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead>
                                        <tr class="border-b border-zinc-200 text-zinc-500 dark:border-white/10">
                                            <th class="pb-2 pe-4 font-medium">Title</th>
                                            <th class="pb-2 pe-4 font-medium">Qty</th>
                                            <th class="pb-2 pe-4 font-medium">Unit €</th>
                                            <th class="pb-2 pe-4 font-medium">Total €</th>
                                            <th class="pb-2 font-medium"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($group->positions as $position)
                                            <tr class="border-b border-zinc-100 dark:border-white/5" wire:key="pos-{{ $position->id }}">
                                                <td class="py-2 pe-4 align-top">
                                                    <flux:field>
                                                        <flux:input size="sm" wire:model="manualPositions.{{ $position->id }}.title" placeholder="Position title" :invalid="$errors->has('manualPositions.'.$position->id.'.title')" />
                                                        <flux:error name="manualPositions.{{ $position->id }}.title" />
                                                    </flux:field>
                                                </td>
                                                <td class="py-2 pe-4 align-top">
                                                    <flux:field>
                                                        <flux:input size="sm" wire:model="manualPositions.{{ $position->id }}.quantity" placeholder="Qty" :invalid="$errors->has('manualPositions.'.$position->id.'.quantity')" />
                                                        <flux:error name="manualPositions.{{ $position->id }}.quantity" />
                                                    </flux:field>
                                                </td>
                                                <td class="py-2 pe-4 align-top">
                                                    <flux:field>
                                                        <flux:input size="sm" wire:model="manualPositions.{{ $position->id }}.unit_price" placeholder="Unit price (€)" :invalid="$errors->has('manualPositions.'.$position->id.'.unit_price')" />
                                                        <flux:error name="manualPositions.{{ $position->id }}.unit_price" />
                                                    </flux:field>
                                                </td>
                                                <td class="py-2 pe-4 align-middle font-medium">€ {{ $position->total }}</td>
                                                <td class="py-2 align-top">
                                                    <div class="flex flex-wrap gap-2">
                                                        <flux:button size="sm" wire:click="updateManualPosition({{ $position->id }})" wire:loading.attr="disabled" wire:target="updateManualPosition({{ $position->id }})">
                                                            <span wire:loading.remove wire:target="updateManualPosition({{ $position->id }})">Save</span>
                                                            <span wire:loading wire:target="updateManualPosition({{ $position->id }})">…</span>
                                                        </flux:button>
                                                        <flux:button size="sm" variant="ghost" wire:click="deletePosition({{ $position->id }})" wire:confirm="Delete this line?" wire:target="deletePosition({{ $position->id }})">Delete</flux:button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="relative mt-4 rounded-lg border border-dashed border-zinc-300 p-4 dark:border-white/20">
                                <div wire:loading.delay class="absolute inset-0 z-10 flex items-center gap-2 rounded-lg bg-white/90 p-3 dark:bg-zinc-950/90" wire:target="createPosition({{ $group->id }})">
                                    <flux:skeleton class="h-8 flex-1" />
                                    <flux:skeleton class="h-8 w-24" />
                                </div>
                                <flux:text class="mb-3 text-sm font-medium text-zinc-600 dark:text-zinc-400">New manual position</flux:text>
                                <form wire:submit="createPosition({{ $group->id }})" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                    <flux:field>
                                        <flux:label>Title</flux:label>
                                        <flux:input wire:model="positionForms.{{ $group->id }}.title" placeholder="Position title" :invalid="$errors->has('positionForms.'.$group->id.'.title')" />
                                        <flux:error name="positionForms.{{ $group->id }}.title" />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>Quantity</flux:label>
                                        <flux:input wire:model="positionForms.{{ $group->id }}.quantity" placeholder="Qty" :invalid="$errors->has('positionForms.'.$group->id.'.quantity')" />
                                        <flux:error name="positionForms.{{ $group->id }}.quantity" />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>Unit price</flux:label>
                                        <flux:input wire:model="positionForms.{{ $group->id }}.unit_price" placeholder="Unit price (€)" :invalid="$errors->has('positionForms.'.$group->id.'.unit_price')" />
                                        <flux:error name="positionForms.{{ $group->id }}.unit_price" />
                                    </flux:field>
                                    <div class="flex items-end">
                                        <flux:button type="submit" class="w-full sm:w-auto" wire:loading.attr="disabled" wire:target="createPosition({{ $group->id }})">
                                            <span wire:loading.remove wire:target="createPosition({{ $group->id }})">Add position</span>
                                            <span wire:loading wire:target="createPosition({{ $group->id }})">Adding…</span>
                                        </flux:button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div>
                            <flux:heading size="sm" class="mb-3">HOAI positions</flux:heading>
                            @foreach ($group->hoaiPositions as $hoai)
                                <div class="relative mb-6 rounded-lg border border-zinc-200 p-4 dark:border-white/10" wire:key="hoai-{{ $hoai->id }}">
                                    <div wire:loading.delay class="absolute inset-0 z-10 flex flex-col gap-2 rounded-lg bg-white/90 p-4 dark:bg-zinc-950/90" wire:target="updateHoai({{ $hoai->id }})">
                                        <flux:skeleton class="h-8 w-full" />
                                        <flux:skeleton class="h-8 w-full" />
                                        <flux:skeleton class="h-12 w-full" />
                                    </div>
                                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                        <flux:text class="font-medium text-zinc-700 dark:text-zinc-300">€ {{ $hoai->total }}</flux:text>
                                        <flux:button size="sm" variant="ghost" wire:click="deleteHoai({{ $hoai->id }})" wire:confirm="Delete this HOAI line?">Delete</flux:button>
                                    </div>
                                    <div class="grid gap-4 lg:grid-cols-2">
                                        <flux:field>
                                            <flux:label>Title</flux:label>
                                            <flux:input wire:model="hoaiRows.{{ $hoai->id }}.title" placeholder="HOAI position title" :invalid="$errors->has('hoaiRows.'.$hoai->id.'.title')" />
                                            <flux:error name="hoaiRows.{{ $hoai->id }}.title" />
                                        </flux:field>
                                        <flux:field>
                                            <flux:label>Construction costs</flux:label>
                                            <flux:input wire:model="hoaiRows.{{ $hoai->id }}.costs" placeholder="Construction costs (€)" :invalid="$errors->has('hoaiRows.'.$hoai->id.'.costs')" />
                                            <flux:error name="hoaiRows.{{ $hoai->id }}.costs" />
                                        </flux:field>
                                        <flux:field>
                                            <flux:label>Zone</flux:label>
                                        <flux:select wire:model="hoaiRows.{{ $hoai->id }}.zone" placeholder="Select zone" :invalid="$errors->has('hoaiRows.'.$hoai->id.'.zone')">
                                            @foreach (HoaiZone::cases() as $z)
                                                <flux:select.option :value="$z->value">{{ $z->label() }}</flux:select.option>
                                            @endforeach
                                        </flux:select>
                                            <flux:error name="hoaiRows.{{ $hoai->id }}.zone" />
                                        </flux:field>
                                        <flux:field>
                                            <flux:label>Rate</flux:label>
                                        <flux:select wire:model="hoaiRows.{{ $hoai->id }}.rate" placeholder="Select rate" :invalid="$errors->has('hoaiRows.'.$hoai->id.'.rate')">
                                            @foreach (HoaiRate::cases() as $r)
                                                <flux:select.option :value="$r->value">{{ $r->label() }}</flux:select.option>
                                            @endforeach
                                        </flux:select>
                                            <flux:error name="hoaiRows.{{ $hoai->id }}.rate" />
                                        </flux:field>
                                        <flux:field class="lg:col-span-2">
                                            <flux:label>Phases</flux:label>
                                            <flux:description>Select phases (1–9)</flux:description>
                                            <flux:checkbox.group wire:model="hoaiRows.{{ $hoai->id }}.phases" :invalid="$errors->has('hoaiRows.'.$hoai->id.'.phases')">
                                                <div class="flex flex-wrap gap-3">
                                                    @foreach (range(1, 9) as $phaseNum)
                                                        <flux:checkbox :value="$phaseNum" :label="(string) $phaseNum" />
                                                    @endforeach
                                                </div>
                                            </flux:checkbox.group>
                                            <flux:error name="hoaiRows.{{ $hoai->id }}.phases" />
                                        </flux:field>
                                        <flux:field>
                                            <flux:label>Construction markup %</flux:label>
                                            <flux:input wire:model="hoaiRows.{{ $hoai->id }}.construction_markup" :invalid="$errors->has('hoaiRows.'.$hoai->id.'.construction_markup')" />
                                            <flux:error name="hoaiRows.{{ $hoai->id }}.construction_markup" />
                                        </flux:field>
                                        <flux:field>
                                            <flux:label>Additional costs %</flux:label>
                                            <flux:input wire:model="hoaiRows.{{ $hoai->id }}.additional_costs" :invalid="$errors->has('hoaiRows.'.$hoai->id.'.additional_costs')" />
                                            <flux:error name="hoaiRows.{{ $hoai->id }}.additional_costs" />
                                        </flux:field>
                                        <flux:field>
                                            <flux:label>VAT %</flux:label>
                                            <flux:input wire:model="hoaiRows.{{ $hoai->id }}.vat" :invalid="$errors->has('hoaiRows.'.$hoai->id.'.vat')" />
                                            <flux:error name="hoaiRows.{{ $hoai->id }}.vat" />
                                        </flux:field>
                                    </div>
                                    <div class="mt-4">
                                        <flux:button wire:click="updateHoai({{ $hoai->id }})" variant="primary" wire:loading.attr="disabled" wire:target="updateHoai({{ $hoai->id }})">
                                            <span wire:loading.remove wire:target="updateHoai({{ $hoai->id }})">Save HOAI line</span>
                                            <span wire:loading wire:target="updateHoai({{ $hoai->id }})">Calculating…</span>
                                        </flux:button>
                                    </div>
                                </div>
                            @endforeach

                            <div class="relative mt-4 rounded-lg border border-dashed border-zinc-300 p-4 dark:border-white/20">
                                <div wire:loading.delay class="absolute inset-0 z-10 flex flex-col gap-2 rounded-lg bg-white/90 p-4 dark:bg-zinc-950/90" wire:target="createHoai({{ $group->id }})">
                                    <flux:skeleton class="h-8 w-full" />
                                    <flux:skeleton class="h-12 w-full" />
                                </div>
                                <flux:text class="mb-3 text-sm font-medium text-zinc-600 dark:text-zinc-400">New HOAI position</flux:text>
                                <form wire:submit="createHoai({{ $group->id }})" class="space-y-4">
                                <div class="grid gap-4 lg:grid-cols-2">
                                    <flux:field>
                                        <flux:label>Title</flux:label>
                                        <flux:input wire:model="hoaiForms.{{ $group->id }}.title" placeholder="HOAI position title" :invalid="$errors->has('hoaiForms.'.$group->id.'.title')" />
                                        <flux:error name="hoaiForms.{{ $group->id }}.title" />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>Construction costs</flux:label>
                                        <flux:input wire:model="hoaiForms.{{ $group->id }}.costs" placeholder="Construction costs (€)" :invalid="$errors->has('hoaiForms.'.$group->id.'.costs')" />
                                        <flux:error name="hoaiForms.{{ $group->id }}.costs" />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>Zone</flux:label>
                                        <flux:select wire:model="hoaiForms.{{ $group->id }}.zone" placeholder="Select zone" :invalid="$errors->has('hoaiForms.'.$group->id.'.zone')">
                                            @foreach (HoaiZone::cases() as $z)
                                                <flux:select.option :value="$z->value">{{ $z->label() }}</flux:select.option>
                                            @endforeach
                                        </flux:select>
                                        <flux:error name="hoaiForms.{{ $group->id }}.zone" />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>Rate</flux:label>
                                        <flux:select wire:model="hoaiForms.{{ $group->id }}.rate" placeholder="Select rate" :invalid="$errors->has('hoaiForms.'.$group->id.'.rate')">
                                            @foreach (HoaiRate::cases() as $r)
                                                <flux:select.option :value="$r->value">{{ $r->label() }}</flux:select.option>
                                            @endforeach
                                        </flux:select>
                                        <flux:error name="hoaiForms.{{ $group->id }}.rate" />
                                    </flux:field>
                                    <flux:field class="lg:col-span-2">
                                        <flux:label>Phases</flux:label>
                                        <flux:description>Select phases (1–9)</flux:description>
                                        <flux:checkbox.group wire:model="hoaiForms.{{ $group->id }}.phases" :invalid="$errors->has('hoaiForms.'.$group->id.'.phases')">
                                            <div class="flex flex-wrap gap-3">
                                                @foreach (range(1, 9) as $phaseNum)
                                                    <flux:checkbox :value="$phaseNum" :label="(string) $phaseNum" />
                                                @endforeach
                                            </div>
                                        </flux:checkbox.group>
                                        <flux:error name="hoaiForms.{{ $group->id }}.phases" />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>Construction markup %</flux:label>
                                        <flux:input wire:model="hoaiForms.{{ $group->id }}.construction_markup" :invalid="$errors->has('hoaiForms.'.$group->id.'.construction_markup')" />
                                        <flux:error name="hoaiForms.{{ $group->id }}.construction_markup" />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>Additional costs %</flux:label>
                                        <flux:input wire:model="hoaiForms.{{ $group->id }}.additional_costs" :invalid="$errors->has('hoaiForms.'.$group->id.'.additional_costs')" />
                                        <flux:error name="hoaiForms.{{ $group->id }}.additional_costs" />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>VAT %</flux:label>
                                        <flux:input wire:model="hoaiForms.{{ $group->id }}.vat" :invalid="$errors->has('hoaiForms.'.$group->id.'.vat')" />
                                        <flux:error name="hoaiForms.{{ $group->id }}.vat" />
                                    </flux:field>
                                </div>
                                <div class="mt-4">
                                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="createHoai({{ $group->id }})">
                                        <span wire:loading.remove wire:target="createHoai({{ $group->id }})">Add HOAI position</span>
                                        <span wire:loading wire:target="createHoai({{ $group->id }})">Calculating…</span>
                                    </flux:button>
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </flux:card>
            </div>
        @endforeach

        @if ($this->offer->offerGroups->isEmpty())
            <flux:callout variant="neutral" icon="information-circle">
                No groups yet. Add a group above to start building this offer.
            </flux:callout>
        @endif
    </div>
</div>
