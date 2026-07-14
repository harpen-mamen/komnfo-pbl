<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'unreadNotificationsCount',
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'unreadNotificationsCount',
]); ?>
<?php foreach (array_filter(([
    'unreadNotificationsCount',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php if (isset($component)) { $__componentOriginal12aab1b471f3f355b32f3364325c81f2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal12aab1b471f3f355b32f3364325c81f2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'notifications::components.modal.heading','data' => ['class' => 'relative']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('notifications::modal.heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'relative']); ?>
    <span>
        <?php echo e(__('notifications::database.modal.heading')); ?>

    </span>

    <?php if($unreadNotificationsCount): ?>
        <span
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'absolute top-0 ml-1 inline-flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-primary-500/10 text-xs text-primary-700',
                'dark:text-primary-500' => config('tables.dark_mode'),
            ]); ?>"
        >
            <?php echo e($unreadNotificationsCount); ?>

        </span>
    <?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal12aab1b471f3f355b32f3364325c81f2)): ?>
<?php $attributes = $__attributesOriginal12aab1b471f3f355b32f3364325c81f2; ?>
<?php unset($__attributesOriginal12aab1b471f3f355b32f3364325c81f2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal12aab1b471f3f355b32f3364325c81f2)): ?>
<?php $component = $__componentOriginal12aab1b471f3f355b32f3364325c81f2; ?>
<?php unset($__componentOriginal12aab1b471f3f355b32f3364325c81f2); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\notifications\resources\views\components\database\modal\heading.blade.php ENDPATH**/ ?>