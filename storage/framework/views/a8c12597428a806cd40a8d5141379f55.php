<?php
    $isDisabled = $isDisabled();
    $state = $getState();
?>

<div
    x-data="{
        error: undefined,
        state: <?php echo \Illuminate\Support\Js::from($state ?? '')->toHtml() ?>,
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

            let newState = $refs.newState.value

            if (state === newState) {
                return
            }

            state = newState
        })
    "
    <?php echo e($attributes
            ->merge($getExtraAttributes())
            ->class(['filament-tables-select-column'])); ?>

>
    <input
        type="hidden"
        value="<?php echo e(\Illuminate\Support\Str::of($state)->replace('"', '\\"')); ?>"
        x-ref="newState"
    />

    <select
        x-model="state"
        x-on:change="
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
        <?php if($isDisabled): ?>
            disabled
        <?php else: ?>
            x-bind:disabled="isLoading"
        <?php endif; ?>
        x-tooltip="error"
        <?php echo e($attributes
                ->merge($getExtraInputAttributes())
                ->merge($getExtraAttributes())
                ->class([
                    'ml-0.5 inline-block rounded-lg text-gray-900 shadow-sm outline-none transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70',
                    'dark:bg-gray-700 dark:text-white dark:focus:border-primary-500' => config('forms.dark_mode'),
                ])); ?>

        x-bind:class="{
            'border-gray-300': ! error,
            'dark:border-gray-600': ! error && <?php echo \Illuminate\Support\Js::from(config('forms.dark_mode'))->toHtml() ?>,
            'border-danger-600 ring-1 ring-inset ring-danger-600': error,
        }"
    >
        <?php if (! ($isPlaceholderSelectionDisabled())): ?>
            <option value=""><?php echo e($getPlaceholder()); ?></option>
        <?php endif; ?>

        <?php $__currentLoopData = $getOptions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option
                value="<?php echo e($value); ?>"
                <?php echo $isOptionDisabled($value, $label) ? 'disabled' : null; ?>

            >
                <?php echo e($label); ?>

            </option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
</div>
<?php /**PATH C:\pbl\vendor\filament\tables\resources\views\columns\select-column.blade.php ENDPATH**/ ?>