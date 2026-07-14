<?php
    $notifications = $this->getDatabaseNotifications();
    $unreadNotificationsCount = $this->getUnreadDatabaseNotificationsCount();
?>

<div
    <?php if($pollingInterval = $this->getPollingInterval()): ?>
        wire:poll.<?php echo e($pollingInterval); ?>

    <?php endif; ?>
    class="flex items-center"
>
    <?php if($databaseNotificationsTrigger = $this->getDatabaseNotificationsTrigger()): ?>
        <?php if (isset($component)) { $__componentOriginal357792a1a18b39022d731bfa6007797a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal357792a1a18b39022d731bfa6007797a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'notifications::components.database.trigger','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('notifications::database.trigger'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
            <?php echo e($databaseNotificationsTrigger->with(['unreadNotificationsCount' => $unreadNotificationsCount])); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal357792a1a18b39022d731bfa6007797a)): ?>
<?php $attributes = $__attributesOriginal357792a1a18b39022d731bfa6007797a; ?>
<?php unset($__attributesOriginal357792a1a18b39022d731bfa6007797a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal357792a1a18b39022d731bfa6007797a)): ?>
<?php $component = $__componentOriginal357792a1a18b39022d731bfa6007797a; ?>
<?php unset($__componentOriginal357792a1a18b39022d731bfa6007797a); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal5e9568079c1530d6e61de47fac1a3ff9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5e9568079c1530d6e61de47fac1a3ff9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'notifications::components.database.modal.index','data' => ['notifications' => $notifications,'unreadNotificationsCount' => $unreadNotificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('notifications::database.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['notifications' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notifications),'unread-notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($unreadNotificationsCount)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5e9568079c1530d6e61de47fac1a3ff9)): ?>
<?php $attributes = $__attributesOriginal5e9568079c1530d6e61de47fac1a3ff9; ?>
<?php unset($__attributesOriginal5e9568079c1530d6e61de47fac1a3ff9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5e9568079c1530d6e61de47fac1a3ff9)): ?>
<?php $component = $__componentOriginal5e9568079c1530d6e61de47fac1a3ff9; ?>
<?php unset($__componentOriginal5e9568079c1530d6e61de47fac1a3ff9); ?>
<?php endif; ?>
</div>
<?php /**PATH C:\pbl\vendor\filament\notifications\resources\views\components\database\index.blade.php ENDPATH**/ ?>