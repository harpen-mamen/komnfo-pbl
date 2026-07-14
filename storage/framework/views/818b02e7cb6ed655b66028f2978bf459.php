<?php
    $state = $getState();
?>

<div
    x-data="{
        error: undefined,
        state: <?php echo \Illuminate\Support\Js::from((bool) $state)->toHtml() ?>,
        isLoading: false,
    }"
    x-init="
        Livewire.hook('message.processed', (component) => {
            if (component.component.id !== <?php echo \Illuminate\Support\Js::from($this->id)->toHtml() ?>) {
                return
            }

            if (! $refs.newState) {
                return
            }

            let newState = $refs.newState.value === '1' ? true : false

            if (state === newState) {
                return
            }

            state = newState
        })
    "
    <?php echo e($attributes
            ->merge($getExtraAttributes())
            ->class([
                'filament-tables-checkbox-column',
            ])); ?>

>
    <input type="hidden" value="<?php echo e($state ? 1 : 0); ?>" x-ref="newState" />

    <input
        x-model="state"
        <?php echo $isDisabled() ? 'disabled' : null; ?>

        type="checkbox"
        x-on:change="
            isLoading = true
            response = await $wire.updateTableColumnState(
                <?php echo \Illuminate\Support\Js::from($getName())->toHtml() ?>,
                <?php echo \Illuminate\Support\Js::from($recordKey)->toHtml() ?>,
                $event.target.checked,
            )
            error = response?.error ?? undefined
            isLoading = false
        "
        x-tooltip="error"
        <?php echo e($attributes
                ->merge($getExtraInputAttributeBag()->getAttributes())
                ->class([
                    'ml-4 rounded text-primary-600 shadow-sm outline-none transition duration-75 focus:border-primary-500 focus:ring-2 focus:ring-primary-500 disabled:opacity-70',
                    'dark:bg-gray-700 dark:checked:bg-primary-500' => config('forms.dark_mode'),
                ])); ?>

        x-bind:class="{
            'opacity-70 pointer-events-none': isLoading,
            'border-gray-300': ! error,
            'dark:border-gray-600': ! error && <?php echo \Illuminate\Support\Js::from(config('forms.dark_mode'))->toHtml() ?>,
            'border-danger-600 ring-1 ring-inset ring-danger-600': error,
        }"
    />
</div>
<?php /**PATH C:\pbl\vendor\filament\tables\resources\views\columns\checkbox-column.blade.php ENDPATH**/ ?>