<?php

use Livewire\Volt\Volt;


Volt::route('/', 'offers.index')->name('offers.index');
Volt::route('offers/{offer}', 'offers.show')->name('offers.show');