<div>
    <div
        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'filament-notifications pointer-events-none fixed inset-4 z-50 mx-auto flex gap-3',
            match (config('notifications.layout.alignment.horizontal')) {
                'left' => 'items-start',
                'center' => 'items-center',
                'right' => 'items-end',
            },
            match (config('notifications.layout.alignment.vertical')) {
                'top' => 'flex-col-reverse justify-end',
                'bottom' => 'flex-col justify-end',
                'center' => 'flex-col justify-center'
            },
        ]); ?>"
        role="status"
    >
        <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php echo e($notification); ?>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <?php if($this->hasDatabaseNotifications()): ?>
        <?php if (isset($component)) { $__componentOriginal2505a0245d8b90a0fb09c1fcf29ed484 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2505a0245d8b90a0fb09c1fcf29ed484 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'notifications::components.database.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('notifications::database'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2505a0245d8b90a0fb09c1fcf29ed484)): ?>
<?php $attributes = $__attributesOriginal2505a0245d8b90a0fb09c1fcf29ed484; ?>
<?php unset($__attributesOriginal2505a0245d8b90a0fb09c1fcf29ed484); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2505a0245d8b90a0fb09c1fcf29ed484)): ?>
<?php $component = $__componentOriginal2505a0245d8b90a0fb09c1fcf29ed484; ?>
<?php unset($__componentOriginal2505a0245d8b90a0fb09c1fcf29ed484); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if($broadcastChannel = $this->getBroadcastChannel()): ?>
        <?php if (isset($component)) { $__componentOriginala28e46f2dff8368ce4f4683d152a4532 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala28e46f2dff8368ce4f4683d152a4532 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'notifications::components.echo','data' => ['channel' => $broadcastChannel]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('notifications::echo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['channel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($broadcastChannel)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala28e46f2dff8368ce4f4683d152a4532)): ?>
<?php $attributes = $__attributesOriginala28e46f2dff8368ce4f4683d152a4532; ?>
<?php unset($__attributesOriginala28e46f2dff8368ce4f4683d152a4532); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala28e46f2dff8368ce4f4683d152a4532)): ?>
<?php $component = $__componentOriginala28e46f2dff8368ce4f4683d152a4532; ?>
<?php unset($__componentOriginala28e46f2dff8368ce4f4683d152a4532); ?>
<?php endif; ?>
    <?php endif; ?>
</div>
<?php /**PATH C:\pbl\vendor\filament\notifications\resources\views/notifications.blade.php ENDPATH**/ ?>