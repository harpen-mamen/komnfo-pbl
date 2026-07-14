<?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $getFieldWrapperView()] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\DynamicComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => $getId(),'label' => $getLabel(),'label-sr-only' => $isAvatar() || $isLabelHidden(),'helper-text' => $getHelperText(),'hint' => $getHint(),'hint-action' => $getHintAction(),'hint-color' => $getHintColor(),'hint-icon' => $getHintIcon(),'required' => $isRequired(),'state-path' => $getStatePath()]); ?>
    <?php
        $imageCropAspectRatio = $getImageCropAspectRatio();
        $imageResizeTargetHeight = $getImageResizeTargetHeight();
        $imageResizeTargetWidth = $getImageResizeTargetWidth();
        $imageResizeMode = $getImageResizeMode();
        $imageResizeUpscale = $getImageResizeUpscale();
        $shouldTransformImage = $imageCropAspectRatio || $imageResizeTargetHeight || $imageResizeTargetWidth;
    ?>

    <div
        x-data="fileUploadFormComponent({
                    acceptedFileTypes: <?php echo e(json_encode($getAcceptedFileTypes())); ?>,
                    canDownload: <?php echo e($canDownload() ? 'true' : 'false'); ?>,
                    canOpen: <?php echo e($canOpen() ? 'true' : 'false'); ?>,
                    canPreview: <?php echo e($canPreview() ? 'true' : 'false'); ?>,
                    canReorder: <?php echo e($canReorder() ? 'true' : 'false'); ?>,
                    deleteUploadedFileUsing: async (fileKey) => {
                        return await $wire.deleteUploadedFile('<?php echo e($getStatePath()); ?>', fileKey)
                    },
                    getUploadedFileUrlsUsing: async () => {
                        return await $wire.getUploadedFileUrls('<?php echo e($getStatePath()); ?>')
                    },
                    imageCropAspectRatio:
                        <?php echo e($imageCropAspectRatio ? "'{$imageCropAspectRatio}'" : 'null'); ?>,
                    imagePreviewHeight:
                        <?php echo e(($height = $getImagePreviewHeight()) ? "'{$height}'" : 'null'); ?>,
                    imageResizeMode: <?php echo e($imageResizeMode ? "'{$imageResizeMode}'" : 'null'); ?>,
                    imageResizeTargetHeight:
                        <?php echo e($imageResizeTargetHeight ? "'{$imageResizeTargetHeight}'" : 'null'); ?>,
                    imageResizeTargetWidth:
                        <?php echo e($imageResizeTargetWidth ? "'{$imageResizeTargetWidth}'" : 'null'); ?>,
                    imageResizeUpscale: <?php echo e($imageResizeUpscale ? 'true' : 'false'); ?>,
                    isAvatar: <?php echo e($isAvatar() ? 'true' : 'false'); ?>,
                    loadingIndicatorPosition: '<?php echo e($getLoadingIndicatorPosition()); ?>',
                    locale: <?php echo \Illuminate\Support\Js::from(app()->getLocale())->toHtml() ?>,
                    panelAspectRatio:
                        <?php echo e(($aspectRatio = $getPanelAspectRatio()) ? "'{$aspectRatio}'" : 'null'); ?>,
                    panelLayout: <?php echo e(($layout = $getPanelLayout()) ? "'{$layout}'" : 'null'); ?>,
                    placeholder: <?php echo \Illuminate\Support\Js::from($getPlaceholder())->toHtml() ?>,
                    maxSize: <?php echo e(($size = $getMaxSize()) ? "'{$size} KB'" : 'null'); ?>,
                    minSize: <?php echo e(($size = $getMinSize()) ? "'{$size} KB'" : 'null'); ?>,
                    removeUploadedFileUsing: async (fileKey) => {
                        return await $wire.removeUploadedFile('<?php echo e($getStatePath()); ?>', fileKey)
                    },
                    removeUploadedFileButtonPosition:
                        '<?php echo e($getRemoveUploadedFileButtonPosition()); ?>',
                    reorderUploadedFilesUsing: async (files) => {
                        return await $wire.reorderUploadedFiles('<?php echo e($getStatePath()); ?>', files)
                    },
                    shouldAppendFiles: <?php echo e($shouldAppendFiles() ? 'true' : 'false'); ?>,
                    shouldOrientImageFromExif:
                        <?php echo e($shouldOrientImageFromExif() ? 'true' : 'false'); ?>,
                    shouldTransformImage: <?php echo e($shouldTransformImage ? 'true' : 'false'); ?>,
                    state: $wire.<?php echo e($applyStateBindingModifiers('entangle(\'' . $getStatePath() . '\')')); ?>,
                    uploadButtonPosition: '<?php echo e($getUploadButtonPosition()); ?>',
                    uploadProgressIndicatorPosition:
                        '<?php echo e($getUploadProgressIndicatorPosition()); ?>',
                    uploadUsing: (fileKey, file, success, error, progress) => {
                        $wire.upload(
                            `<?php echo e($getStatePath()); ?>.${fileKey}`,
                            file,
                            () => {
                                success(fileKey)
                            },
                            error,
                            (progressEvent) => {
                                progress(true, progressEvent.detail.progress, 100)
                            },
                        )
                    },
                })"
        wire:ignore
        <?php echo ($id = $getId()) ? "id=\"{$id}\"" : null; ?>

        style="
            min-height: <?php echo e($isAvatar() ? '8em' : ($getPanelLayout() === 'compact' ? '2.625em' : '4.75em')); ?>;
        "
        <?php echo e($attributes
                ->merge($getExtraAttributes())
                ->class([
                    'filament-forms-file-upload-component',
                    'mx-auto w-32' => $isAvatar(),
                ])); ?>

        <?php echo e($getExtraAlpineAttributeBag()); ?>

    >
        <input
            x-ref="input"
            <?php echo e($isDisabled() ? 'disabled' : ''); ?>

            <?php echo e($isMultiple() ? 'multiple' : ''); ?>

            type="file"
            <?php echo e($getExtraInputAttributeBag()); ?>

            dusk="filament.forms.<?php echo e($getStatePath()); ?>"
        />
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\forms\resources\views\components\file-upload.blade.php ENDPATH**/ ?>