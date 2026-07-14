<?php
    $alignClass = match ($getAlignment()) {
        'center' => 'text-center',
        'right' => 'text-right',
        default => 'text-left',
    };

    $state = $getState();
?>

<div
    x-data="{
        error: undefined,
        state: <?php echo \Illuminate\Support\Js::from($state)->toHtml() ?>,
        isLoading: false,
        isEditing: false,
    }"
    x-init="
        Livewire.hook('message.processed', (component) => {
            if (component.component.id !== <?php echo \Illuminate\Support\Js::from($this->id)->toHtml() ?>) {
                return
            }

            if (isEditing) {
                return
            }

            if (! $refs.newState) {
                return
            }

            let newState = $refs.newState.value

            if (state === newState) {
                return
            }

            state = newState
        })
    "
    <?php echo e($attributes
            ->merge($getExtraAttributes())
            ->class(['filament-tables-text-input-column'])); ?>

>
    <input
        type="hidden"
        value="<?php echo e(\Illuminate\Support\Str::of($state)->replace('"', '\\"')); ?>"
        x-ref="newState"
    />

    <input
        x-model="state"
        type="<?php echo e($getType()); ?>"
        <?php echo $isDisabled() ? 'disabled' : null; ?>

        <?php echo ($inputMode = $getInputMode()) ? "inputmode=\"{$inputMode}\"" : null; ?>

        <?php echo ($placeholder = $getPlaceholder()) ? "placeholder=\"{$placeholder}\"" : null; ?>

        <?php echo ($interval = $getStep()) ? "step=\"{$interval}\"" : null; ?>

        x-on:focus="isEditing = true"
        x-on:blur="isEditing = false"
        x-on:change<?php echo e($getType() === 'number' ? '.debounce.1s' : null); ?>="
            isLoading = true
            response = await $wire.updateTableColumnState(
                <?php echo \Illuminate\Support\Js::from($getName())->toHtml() ?>,
                <?php echo \Illuminate\Support\Js::from($recordKey)->toHtml() ?>,
                $event.target.value,
            )
            error = response?.error ?? undefined
            if (! error) state = response
            isLoading = false
        "
        :readonly="isLoading"
        x-tooltip="error"
        <?php echo e($attributes
                ->merge($getExtraInputAttributes())
                ->merge($getExtraAttributes())
                ->class([
                    'ml-0.5 inline-block rounded-lg text-gray-900 shadow-sm outline-none transition duration-75 read-only:opacity-50 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70',
                    $alignClass,
                    'dark:bg-gray-700 dark:text-white dark:focus:border-primary-500' => config('forms.dark_mode'),
                ])); ?>

        x-bind:class="{
            'border-gray-300': ! error,
            'dark:border-gray-600': ! error && <?php echo \Illuminate\Support\Js::from(config('forms.dark_mode'))->toHtml() ?>,
            'border-danger-600 ring-1 ring-inset ring-danger-600': error,
        }"
    />
</div>
<?php /**PATH C:\pbl\vendor\filament\tables\resources\views\columns\text-input-column.blade.php ENDPATH**/ ?>