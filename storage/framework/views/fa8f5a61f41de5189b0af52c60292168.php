<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'notifications',
    'unreadNotificationsCount',
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'notifications',
    'unreadNotificationsCount',
]); ?>
<?php foreach (array_filter(([
    'notifications',
    'unreadNotificationsCount',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php if (isset($component)) { $__componentOriginal412ecf71fbbcc0b5f423545b216d5710 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal412ecf71fbbcc0b5f423545b216d5710 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'notifications::components.modal.index','data' => ['id' => 'database-notifications','closeButton' => true,'slideOver' => true,'width' => 'md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('notifications::modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'database-notifications','close-button' => true,'slide-over' => true,'width' => 'md']); ?>
    <?php if($notifications->count()): ?>
         <?php $__env->slot('header', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginalf08bbb5de3eb9de575b80ec6192ec62a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf08bbb5de3eb9de575b80ec6192ec62a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'notifications::components.database.modal.heading','data' => ['unreadNotificationsCount' => $unreadNotificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('notifications::database.modal.heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['unread-notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($unreadNotificationsCount)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf08bbb5de3eb9de575b80ec6192ec62a)): ?>
<?php $attributes = $__attributesOriginalf08bbb5de3eb9de575b80ec6192ec62a; ?>
<?php unset($__attributesOriginalf08bbb5de3eb9de575b80ec6192ec62a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf08bbb5de3eb9de575b80ec6192ec62a)): ?>
<?php $component = $__componentOriginalf08bbb5de3eb9de575b80ec6192ec62a; ?>
<?php unset($__componentOriginalf08bbb5de3eb9de575b80ec6192ec62a); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal14ad95ad0e46a30f6df7295e61b14db8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal14ad95ad0e46a30f6df7295e61b14db8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'notifications::components.database.modal.actions','data' => ['notifications' => $notifications,'unreadNotificationsCount' => $unreadNotificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('notifications::database.modal.actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['notifications' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notifications),'unread-notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($unreadNotificationsCount)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal14ad95ad0e46a30f6df7295e61b14db8)): ?>
<?php $attributes = $__attributesOriginal14ad95ad0e46a30f6df7295e61b14db8; ?>
<?php unset($__attributesOriginal14ad95ad0e46a30f6df7295e61b14db8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal14ad95ad0e46a30f6df7295e61b14db8)): ?>
<?php $component = $__componentOriginal14ad95ad0e46a30f6df7295e61b14db8; ?>
<?php unset($__componentOriginal14ad95ad0e46a30f6df7295e61b14db8); ?>
<?php endif; ?>
         <?php $__env->endSlot(); ?>

        <div class="mt-[calc(-1rem-1px)]">
            <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        '-mx-6 border-b',
                        'border-t' => $notification->unread(),
                        'dark:border-gray-700' => (! $notification->unread()) && config('notifications.dark_mode'),
                        'dark:border-gray-800' => $notification->unread() && config('notifications.dark_mode'),
                    ]); ?>"
                >
                    <div
                        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'py-2 pl-4 pr-2',
                            '-mb-px bg-primary-50' => $notification->unread(),
                            'dark:bg-gray-700' => $notification->unread() && config('notifications.dark_mode'),
                        ]); ?>"
                    >
                        <?php echo e($this->getNotificationFromDatabaseRecord($notification)->inline()); ?>

                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php else: ?>
        <?php if (isset($component)) { $__componentOriginal82c19574c1880231e3ca10957a66f8c5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal82c19574c1880231e3ca10957a66f8c5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'notifications::components.database.modal.empty-state','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('notifications::database.modal.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal82c19574c1880231e3ca10957a66f8c5)): ?>
<?php $attributes = $__attributesOriginal82c19574c1880231e3ca10957a66f8c5; ?>
<?php unset($__attributesOriginal82c19574c1880231e3ca10957a66f8c5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal82c19574c1880231e3ca10957a66f8c5)): ?>
<?php $component = $__componentOriginal82c19574c1880231e3ca10957a66f8c5; ?>
<?php unset($__componentOriginal82c19574c1880231e3ca10957a66f8c5); ?>
<?php endif; ?>
    <?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal412ecf71fbbcc0b5f423545b216d5710)): ?>
<?php $attributes = $__attributesOriginal412ecf71fbbcc0b5f423545b216d5710; ?>
<?php unset($__attributesOriginal412ecf71fbbcc0b5f423545b216d5710); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal412ecf71fbbcc0b5f423545b216d5710)): ?>
<?php $component = $__componentOriginal412ecf71fbbcc0b5f423545b216d5710; ?>
<?php unset($__componentOriginal412ecf71fbbcc0b5f423545b216d5710); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\notifications\resources\views\components\database\modal\index.blade.php ENDPATH**/ ?>