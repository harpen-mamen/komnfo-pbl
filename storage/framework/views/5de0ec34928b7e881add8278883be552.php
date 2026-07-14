<?php
    use Filament\Tables\Actions\Position as ActionsPosition;
    use Filament\Tables\Actions\RecordCheckboxPosition;
    use Filament\Tables\Filters\Layout as FiltersLayout;

    $actions = $getActions();
    $actionsPosition = $getActionsPosition();
    $actionsColumnLabel = $getActionsColumnLabel();
    $columns = $getColumns();
    $collapsibleColumnsLayout = $getCollapsibleColumnsLayout();
    $content = $getContent();
    $contentGrid = $getContentGrid();
    $contentFooter = $getContentFooter();
    $filterIndicators = collect($getFilters())
        ->mapWithKeys(fn (\Filament\Tables\Filters\BaseFilter $filter): array => [$filter->getName() => $filter->getIndicators()])
        ->filter(fn (array $indicators): bool => count($indicators))
        ->all();
    $hasColumnsLayout = $hasColumnsLayout();
    $header = $getHeader();
    $headerActions = $getHeaderActions();
    $heading = $getHeading();
    $description = $getDescription();
    $isReorderable = $isReorderable();
    $isReordering = $isReordering();
    $isColumnSearchVisible = $isSearchableByColumn();
    $isGlobalSearchVisible = $isSearchable();
    $isSelectionEnabled = $isSelectionEnabled();
    $recordCheckboxPosition = $getRecordCheckboxPosition();
    $isStriped = $isStriped();
    $isLoaded = $isLoaded();
    $hasFilters = $isFilterable();
    $filtersLayout = $getFiltersLayout();
    $hasFiltersPopover = $hasFilters && ($filtersLayout === FiltersLayout::Popover);
    $hasFiltersAboveContent = $hasFilters && in_array($filtersLayout, [FiltersLayout::AboveContent, FiltersLayout::AboveContentCollapsible]);
    $hasFiltersAboveContentCollapsible = $hasFilters && ($filtersLayout === FiltersLayout::AboveContentCollapsible);
    $hasFiltersAfterContent = $hasFilters && ($filtersLayout === FiltersLayout::BelowContent);
    $isColumnToggleFormVisible = $hasToggleableColumns();
    $records = $isLoaded ? $getRecords() : null;
    $allSelectableRecordsCount = $isLoaded ? $getAllSelectableRecordsCount() : null;
    $columnsCount = count($columns);

    if (count($actions) && (! $isReordering)) {
        $columnsCount++;
    }

    if ($isSelectionEnabled || $isReordering) {
        $columnsCount++;
    }

    $getHiddenClasses = function (Filament\Tables\Columns\Column $column): ?string {
        if ($breakpoint = $column->getHiddenFrom()) {
            return match ($breakpoint) {
                'sm' => 'sm:hidden',
                'md' => 'md:hidden',
                'lg' => 'lg:hidden',
                'xl' => 'xl:hidden',
                '2xl' => '2xl:hidden',
            };
        }

        if ($breakpoint = $column->getVisibleFrom()) {
            return match ($breakpoint) {
                'sm' => 'hidden sm:table-cell',
                'md' => 'hidden md:table-cell',
                'lg' => 'hidden lg:table-cell',
                'xl' => 'hidden xl:table-cell',
                '2xl' => 'hidden 2xl:table-cell',
            };
        }

        return null;
    };
?>

<div
    x-data="{
        hasHeader: true,

        isLoading: false,

        selectedRecords: [],

        shouldCheckUniqueSelection: true,

        init: function () {
            $wire.on('deselectAllTableRecords', () => this.deselectAllRecords())

            $watch('selectedRecords', () => {
                if (! this.shouldCheckUniqueSelection) {
                    this.shouldCheckUniqueSelection = true

                    return
                }

                this.selectedRecords = [...new Set(this.selectedRecords)]

                this.shouldCheckUniqueSelection = false
            })
        },

        mountBulkAction: function (name) {
            $wire.mountTableBulkAction(name, this.selectedRecords)
        },

        toggleSelectRecordsOnPage: function () {
            let keys = this.getRecordsOnPage()

            if (this.areRecordsSelected(keys)) {
                this.deselectRecords(keys)

                return
            }

            this.selectRecords(keys)
        },

        getRecordsOnPage: function () {
            let keys = []

            for (checkbox of $el.getElementsByClassName(
                'filament-tables-record-checkbox',
            )) {
                keys.push(checkbox.value)
            }

            return keys
        },

        selectRecords: function (keys) {
            for (key of keys) {
                if (this.isRecordSelected(key)) {
                    continue
                }

                this.selectedRecords.push(key)
            }
        },

        deselectRecords: function (keys) {
            for (key of keys) {
                let index = this.selectedRecords.indexOf(key)

                if (index === -1) {
                    continue
                }

                this.selectedRecords.splice(index, 1)
            }
        },

        selectAllRecords: async function () {
            this.isLoading = true

            this.selectedRecords = await $wire.getAllSelectableTableRecordKeys()

            this.isLoading = false
        },

        deselectAllRecords: function () {
            this.selectedRecords = []
        },

        isRecordSelected: function (key) {
            return this.selectedRecords.includes(key)
        },

        areRecordsSelected: function (keys) {
            return keys.every((key) => this.isRecordSelected(key))
        },
    }"
    class="filament-tables-component"
    <?php if(! $isLoaded): ?>
        wire:init="loadTable"
    <?php endif; ?>
>
    <?php if (isset($component)) { $__componentOriginaldbe408be6b5fd2d95479478edae3b0a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldbe408be6b5fd2d95479478edae3b0a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.container','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::container'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <div
            class="filament-tables-header-container"
            x-show="hasHeader = <?php echo \Illuminate\Support\Js::from($renderHeader = ($header || $heading || ($headerActions && (! $isReordering)) || $isReorderable || $isGlobalSearchVisible || $hasFilters || $isColumnToggleFormVisible))->toHtml() ?> || selectedRecords.length"
            <?php echo ! $renderHeader ? 'x-cloak' : null; ?>

        >
            <?php if($header): ?>
                <?php echo e($header); ?>

            <?php elseif($heading || $headerActions): ?>
                <div
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'px-2 pt-2',
                        'hidden' => ! $heading && $isReordering,
                    ]); ?>"
                >
                    <?php if (isset($component)) { $__componentOriginalf61a3824e9f59808a5868485e5ef6761 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf61a3824e9f59808a5868485e5ef6761 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.header.index','data' => ['actions' => $isReordering ? [] : $headerActions,'class' => 'mb-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isReordering ? [] : $headerActions),'class' => 'mb-2']); ?>
                         <?php $__env->slot('heading', null, []); ?> 
                            <?php echo e($heading); ?>

                         <?php $__env->endSlot(); ?>

                         <?php $__env->slot('description', null, []); ?> 
                            <?php echo e($description); ?>

                         <?php $__env->endSlot(); ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf61a3824e9f59808a5868485e5ef6761)): ?>
<?php $attributes = $__attributesOriginalf61a3824e9f59808a5868485e5ef6761; ?>
<?php unset($__attributesOriginalf61a3824e9f59808a5868485e5ef6761); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf61a3824e9f59808a5868485e5ef6761)): ?>
<?php $component = $__componentOriginalf61a3824e9f59808a5868485e5ef6761; ?>
<?php unset($__componentOriginalf61a3824e9f59808a5868485e5ef6761); ?>
<?php endif; ?>

                    <?php if (isset($component)) { $__componentOriginal1c6b9867004d1a406262ace2c95ab783 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1c6b9867004d1a406262ace2c95ab783 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.hr','data' => ['xShow' => \Illuminate\Support\Js::from($isReorderable || $isGlobalSearchVisible || $hasFilters || $isColumnToggleFormVisible) . ' || selectedRecords.length']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::hr'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-show' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Illuminate\Support\Js::from($isReorderable || $isGlobalSearchVisible || $hasFilters || $isColumnToggleFormVisible) . ' || selectedRecords.length')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1c6b9867004d1a406262ace2c95ab783)): ?>
<?php $attributes = $__attributesOriginal1c6b9867004d1a406262ace2c95ab783; ?>
<?php unset($__attributesOriginal1c6b9867004d1a406262ace2c95ab783); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1c6b9867004d1a406262ace2c95ab783)): ?>
<?php $component = $__componentOriginal1c6b9867004d1a406262ace2c95ab783; ?>
<?php unset($__componentOriginal1c6b9867004d1a406262ace2c95ab783); ?>
<?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if($hasFiltersAboveContent): ?>
                <div
                    class="px-2 pt-2"
                    x-data="{ areFiltersOpen: <?php echo \Illuminate\Support\Js::from(! $hasFiltersAboveContentCollapsible)->toHtml() ?> }"
                >
                    <?php if($hasFiltersAboveContentCollapsible): ?>
                        <div class="flex w-full justify-end">
                            <?php if (isset($component)) { $__componentOriginal7917fea7221db3198ac6d08be6e10451 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7917fea7221db3198ac6d08be6e10451 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.filters.trigger','data' => ['xOn:click' => 'areFiltersOpen = ! areFiltersOpen']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::filters.trigger'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-on:click' => 'areFiltersOpen = ! areFiltersOpen']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7917fea7221db3198ac6d08be6e10451)): ?>
<?php $attributes = $__attributesOriginal7917fea7221db3198ac6d08be6e10451; ?>
<?php unset($__attributesOriginal7917fea7221db3198ac6d08be6e10451); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7917fea7221db3198ac6d08be6e10451)): ?>
<?php $component = $__componentOriginal7917fea7221db3198ac6d08be6e10451; ?>
<?php unset($__componentOriginal7917fea7221db3198ac6d08be6e10451); ?>
<?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="mb-2 p-4" x-show="areFiltersOpen">
                        <?php if (isset($component)) { $__componentOriginal3b3fa8ec7d71109f7bdf00def1db315e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3b3fa8ec7d71109f7bdf00def1db315e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.filters.index','data' => ['form' => $getFiltersForm()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::filters'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['form' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getFiltersForm())]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3b3fa8ec7d71109f7bdf00def1db315e)): ?>
<?php $attributes = $__attributesOriginal3b3fa8ec7d71109f7bdf00def1db315e; ?>
<?php unset($__attributesOriginal3b3fa8ec7d71109f7bdf00def1db315e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3b3fa8ec7d71109f7bdf00def1db315e)): ?>
<?php $component = $__componentOriginal3b3fa8ec7d71109f7bdf00def1db315e; ?>
<?php unset($__componentOriginal3b3fa8ec7d71109f7bdf00def1db315e); ?>
<?php endif; ?>
                    </div>

                    <?php if (isset($component)) { $__componentOriginal1c6b9867004d1a406262ace2c95ab783 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1c6b9867004d1a406262ace2c95ab783 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.hr','data' => ['xShow' => \Illuminate\Support\Js::from($isReorderable || $isGlobalSearchVisible || $isColumnToggleFormVisible) . ' || selectedRecords.length']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::hr'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-show' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Illuminate\Support\Js::from($isReorderable || $isGlobalSearchVisible || $isColumnToggleFormVisible) . ' || selectedRecords.length')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1c6b9867004d1a406262ace2c95ab783)): ?>
<?php $attributes = $__attributesOriginal1c6b9867004d1a406262ace2c95ab783; ?>
<?php unset($__attributesOriginal1c6b9867004d1a406262ace2c95ab783); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1c6b9867004d1a406262ace2c95ab783)): ?>
<?php $component = $__componentOriginal1c6b9867004d1a406262ace2c95ab783; ?>
<?php unset($__componentOriginal1c6b9867004d1a406262ace2c95ab783); ?>
<?php endif; ?>
                </div>
            <?php endif; ?>

            <div
                x-show="<?php echo \Illuminate\Support\Js::from($shouldRenderHeaderDiv = ($isReorderable || $isGlobalSearchVisible || $hasFiltersPopover || $isColumnToggleFormVisible))->toHtml() ?> || selectedRecords.length"
                <?php echo ! $shouldRenderHeaderDiv ? 'x-cloak' : null; ?>

                class="filament-tables-header-toolbar flex h-14 items-center justify-between p-2"
                x-bind:class="{
                    'gap-2': <?php echo \Illuminate\Support\Js::from($isReorderable)->toHtml() ?> || selectedRecords.length,
                }"
            >
                <div class="flex items-center gap-2">
                    <?php if($isReorderable): ?>
                        <?php if (isset($component)) { $__componentOriginalc0181334864fb71d91a10cbdcaecfd50 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc0181334864fb71d91a10cbdcaecfd50 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.reorder.trigger','data' => ['enabled' => $isReordering]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::reorder.trigger'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['enabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isReordering)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc0181334864fb71d91a10cbdcaecfd50)): ?>
<?php $attributes = $__attributesOriginalc0181334864fb71d91a10cbdcaecfd50; ?>
<?php unset($__attributesOriginalc0181334864fb71d91a10cbdcaecfd50); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc0181334864fb71d91a10cbdcaecfd50)): ?>
<?php $component = $__componentOriginalc0181334864fb71d91a10cbdcaecfd50; ?>
<?php unset($__componentOriginalc0181334864fb71d91a10cbdcaecfd50); ?>
<?php endif; ?>
                    <?php endif; ?>

                    <?php if(! $isReordering): ?>
                        <?php if (isset($component)) { $__componentOriginal56f6ab899e86af4645d8ee58db6f126e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56f6ab899e86af4645d8ee58db6f126e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.bulk-actions.index','data' => ['xShow' => 'selectedRecords.length','actions' => $getBulkActions()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::bulk-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-show' => 'selectedRecords.length','actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getBulkActions())]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56f6ab899e86af4645d8ee58db6f126e)): ?>
<?php $attributes = $__attributesOriginal56f6ab899e86af4645d8ee58db6f126e; ?>
<?php unset($__attributesOriginal56f6ab899e86af4645d8ee58db6f126e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56f6ab899e86af4645d8ee58db6f126e)): ?>
<?php $component = $__componentOriginal56f6ab899e86af4645d8ee58db6f126e; ?>
<?php unset($__componentOriginal56f6ab899e86af4645d8ee58db6f126e); ?>
<?php endif; ?>
                    <?php endif; ?>
                </div>

                <?php if($isGlobalSearchVisible || $hasFiltersPopover || $isColumnToggleFormVisible): ?>
                    <div
                        class="flex w-full items-center justify-end gap-2 md:max-w-md"
                    >
                        <?php if($isGlobalSearchVisible): ?>
                            <div
                                class="filament-tables-search-container flex flex-1 items-center justify-end"
                            >
                                <?php if (isset($component)) { $__componentOriginal4b04fa758b77ab5f3a62cdb93e6ff6e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4b04fa758b77ab5f3a62cdb93e6ff6e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.search-input','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::search-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4b04fa758b77ab5f3a62cdb93e6ff6e9)): ?>
<?php $attributes = $__attributesOriginal4b04fa758b77ab5f3a62cdb93e6ff6e9; ?>
<?php unset($__attributesOriginal4b04fa758b77ab5f3a62cdb93e6ff6e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4b04fa758b77ab5f3a62cdb93e6ff6e9)): ?>
<?php $component = $__componentOriginal4b04fa758b77ab5f3a62cdb93e6ff6e9; ?>
<?php unset($__componentOriginal4b04fa758b77ab5f3a62cdb93e6ff6e9); ?>
<?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if($hasFiltersPopover): ?>
                            <?php if (isset($component)) { $__componentOriginal8126634305cb1a54df600d54efbfd138 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8126634305cb1a54df600d54efbfd138 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.filters.popover','data' => ['form' => $getFiltersForm(),'maxHeight' => $getFiltersFormMaxHeight(),'width' => $getFiltersFormWidth(),'indicatorsCount' => count(\Illuminate\Support\Arr::flatten($filterIndicators)),'class' => 'shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::filters.popover'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['form' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getFiltersForm()),'max-height' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getFiltersFormMaxHeight()),'width' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getFiltersFormWidth()),'indicators-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(count(\Illuminate\Support\Arr::flatten($filterIndicators))),'class' => 'shrink-0']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8126634305cb1a54df600d54efbfd138)): ?>
<?php $attributes = $__attributesOriginal8126634305cb1a54df600d54efbfd138; ?>
<?php unset($__attributesOriginal8126634305cb1a54df600d54efbfd138); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8126634305cb1a54df600d54efbfd138)): ?>
<?php $component = $__componentOriginal8126634305cb1a54df600d54efbfd138; ?>
<?php unset($__componentOriginal8126634305cb1a54df600d54efbfd138); ?>
<?php endif; ?>
                        <?php endif; ?>

                        <?php if($isColumnToggleFormVisible): ?>
                            <?php if (isset($component)) { $__componentOriginal59e66082a5ae391e26bfaff0cae2e27d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal59e66082a5ae391e26bfaff0cae2e27d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.toggleable.index','data' => ['form' => $getColumnToggleForm(),'maxHeight' => $getColumnToggleFormMaxHeight(),'width' => $getColumnToggleFormWidth(),'class' => 'shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::toggleable'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['form' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getColumnToggleForm()),'max-height' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getColumnToggleFormMaxHeight()),'width' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getColumnToggleFormWidth()),'class' => 'shrink-0']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal59e66082a5ae391e26bfaff0cae2e27d)): ?>
<?php $attributes = $__attributesOriginal59e66082a5ae391e26bfaff0cae2e27d; ?>
<?php unset($__attributesOriginal59e66082a5ae391e26bfaff0cae2e27d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal59e66082a5ae391e26bfaff0cae2e27d)): ?>
<?php $component = $__componentOriginal59e66082a5ae391e26bfaff0cae2e27d; ?>
<?php unset($__componentOriginal59e66082a5ae391e26bfaff0cae2e27d); ?>
<?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if($isReordering): ?>
            <?php if (isset($component)) { $__componentOriginal9a459b3d216a804614ab211a15955ec4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9a459b3d216a804614ab211a15955ec4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.reorder.indicator','data' => ['colspan' => $columnsCount,'class' => 
                    \Illuminate\Support\Arr::toCssClasses([
                        'border-t',
                        'dark:border-gray-700' => config('tables.dark_mode'),
                    ])
                ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::reorder.indicator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($columnsCount),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(
                    \Illuminate\Support\Arr::toCssClasses([
                        'border-t',
                        'dark:border-gray-700' => config('tables.dark_mode'),
                    ])
                )]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9a459b3d216a804614ab211a15955ec4)): ?>
<?php $attributes = $__attributesOriginal9a459b3d216a804614ab211a15955ec4; ?>
<?php unset($__attributesOriginal9a459b3d216a804614ab211a15955ec4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9a459b3d216a804614ab211a15955ec4)): ?>
<?php $component = $__componentOriginal9a459b3d216a804614ab211a15955ec4; ?>
<?php unset($__componentOriginal9a459b3d216a804614ab211a15955ec4); ?>
<?php endif; ?>
        <?php elseif($isSelectionEnabled && $isLoaded): ?>
            <?php if (isset($component)) { $__componentOriginalec4e40df6f0d418ae452b0cfbf7e0938 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalec4e40df6f0d418ae452b0cfbf7e0938 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.selection-indicator','data' => ['allSelectableRecordsCount' => $allSelectableRecordsCount,'colspan' => $columnsCount,'xShow' => 'selectedRecords.length','class' => 
                    \Illuminate\Support\Arr::toCssClasses([
                        'border-t',
                        'dark:border-gray-700' => config('tables.dark_mode'),
                    ])
                ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::selection-indicator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['all-selectable-records-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($allSelectableRecordsCount),'colspan' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($columnsCount),'x-show' => 'selectedRecords.length','class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(
                    \Illuminate\Support\Arr::toCssClasses([
                        'border-t',
                        'dark:border-gray-700' => config('tables.dark_mode'),
                    ])
                )]); ?>
                 <?php $__env->slot('selectedRecordsCount', null, []); ?> 
                    <span x-text="selectedRecords.length"></span>
                 <?php $__env->endSlot(); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalec4e40df6f0d418ae452b0cfbf7e0938)): ?>
<?php $attributes = $__attributesOriginalec4e40df6f0d418ae452b0cfbf7e0938; ?>
<?php unset($__attributesOriginalec4e40df6f0d418ae452b0cfbf7e0938); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalec4e40df6f0d418ae452b0cfbf7e0938)): ?>
<?php $component = $__componentOriginalec4e40df6f0d418ae452b0cfbf7e0938; ?>
<?php unset($__componentOriginalec4e40df6f0d418ae452b0cfbf7e0938); ?>
<?php endif; ?>
        <?php endif; ?>

        <?php if (isset($component)) { $__componentOriginal7dd593227f23104fb8f7af508dd8da51 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7dd593227f23104fb8f7af508dd8da51 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.filters.indicators','data' => ['indicators' => $filterIndicators,'class' => 
                \Illuminate\Support\Arr::toCssClasses([
                    'border-t',
                    'dark:border-gray-700' => config('tables.dark_mode'),
                ])
            ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::filters.indicators'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['indicators' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filterIndicators),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(
                \Illuminate\Support\Arr::toCssClasses([
                    'border-t',
                    'dark:border-gray-700' => config('tables.dark_mode'),
                ])
            )]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7dd593227f23104fb8f7af508dd8da51)): ?>
<?php $attributes = $__attributesOriginal7dd593227f23104fb8f7af508dd8da51; ?>
<?php unset($__attributesOriginal7dd593227f23104fb8f7af508dd8da51); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7dd593227f23104fb8f7af508dd8da51)): ?>
<?php $component = $__componentOriginal7dd593227f23104fb8f7af508dd8da51; ?>
<?php unset($__componentOriginal7dd593227f23104fb8f7af508dd8da51); ?>
<?php endif; ?>

        <div
            <?php if($pollingInterval = $getPollingInterval()): ?>
                wire:poll.<?php echo e($pollingInterval); ?>

            <?php endif; ?>
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'filament-tables-table-container relative overflow-x-auto',
                'dark:border-gray-700' => config('tables.dark_mode'),
                'overflow-x-auto' => $content || $hasColumnsLayout,
                'rounded-t-xl' => ! $renderHeader,
                'border-t' => $renderHeader,
            ]); ?>"
            x-bind:class="{
                'rounded-t-xl': ! hasHeader,
                'border-t': hasHeader,
            }"
        >
            <?php if(($content || $hasColumnsLayout) && ($records !== null) && count($records)): ?>
                <?php if(($content || $hasColumnsLayout) && (! $isReordering)): ?>
                    <?php
                        $sortableColumns = array_filter(
                            $columns,
                            fn (\Filament\Tables\Columns\Column $column): bool => $column->isSortable(),
                        );
                    ?>

                    <div
                        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'flex items-center gap-4 border-b bg-gray-500/5 px-4',
                            'dark:border-gray-700' => config('tables.dark_mode'),
                            'hidden' => (! $isSelectionEnabled) && (! count($sortableColumns)),
                        ]); ?>"
                    >
                        <?php if($isSelectionEnabled): ?>
                            <?php if (isset($component)) { $__componentOriginalcf5e41131e15ad2a36f9d4e05dd93409 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcf5e41131e15ad2a36f9d4e05dd93409 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.checkbox.index','data' => ['label' => __('tables::table.fields.bulk_select_page.label'),'xOn:click' => 'toggleSelectRecordsOnPage','xBind:checked' => '
                                    let recordsOnPage = getRecordsOnPage()

                                    if (recordsOnPage.length && areRecordsSelected(recordsOnPage)) {
                                        $el.checked = true

                                        return \'checked\'
                                    }

                                    $el.checked = false

                                    return null
                                ','class' => 
                                    \Illuminate\Support\Arr::toCssClasses([
                                        'hidden' => $isReordering,
                                    ])
                                ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::checkbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('tables::table.fields.bulk_select_page.label')),'x-on:click' => 'toggleSelectRecordsOnPage','x-bind:checked' => '
                                    let recordsOnPage = getRecordsOnPage()

                                    if (recordsOnPage.length && areRecordsSelected(recordsOnPage)) {
                                        $el.checked = true

                                        return \'checked\'
                                    }

                                    $el.checked = false

                                    return null
                                ','class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(
                                    \Illuminate\Support\Arr::toCssClasses([
                                        'hidden' => $isReordering,
                                    ])
                                )]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcf5e41131e15ad2a36f9d4e05dd93409)): ?>
<?php $attributes = $__attributesOriginalcf5e41131e15ad2a36f9d4e05dd93409; ?>
<?php unset($__attributesOriginalcf5e41131e15ad2a36f9d4e05dd93409); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcf5e41131e15ad2a36f9d4e05dd93409)): ?>
<?php $component = $__componentOriginalcf5e41131e15ad2a36f9d4e05dd93409; ?>
<?php unset($__componentOriginalcf5e41131e15ad2a36f9d4e05dd93409); ?>
<?php endif; ?>
                        <?php endif; ?>

                        <?php if(count($sortableColumns)): ?>
                            <div
                                x-data="{
                                    column: $wire.entangle('tableSortColumn'),
                                    direction: $wire.entangle('tableSortDirection'),
                                }"
                                x-init="
                                    $watch('column', function (newColumn, oldColumn) {
                                        if (! newColumn) {
                                            direction = null

                                            return
                                        }

                                        if (oldColumn) {
                                            return
                                        }

                                        direction = 'asc'
                                    })
                                "
                                class="flex flex-wrap items-center gap-1 py-1 text-xs sm:text-sm"
                            >
                                <label>
                                    <span class="mr-1 font-medium">
                                        <?php echo e(__('tables::table.sorting.fields.column.label')); ?>

                                    </span>

                                    <select
                                        x-model="column"
                                        style="
                                            background-position: right 0.2rem
                                                center;
                                        "
                                        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                            'rounded-lg border-0 border-gray-300 bg-gray-500/5 py-1 pl-2 pr-6 text-xs font-medium focus:border-primary-500 focus:ring-0 focus:ring-primary-500 sm:text-sm',
                                            'dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500' => config('tables.dark_mode'),
                                        ]); ?>"
                                    >
                                        <option value="">-</option>

                                        <?php $__currentLoopData = $sortableColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option
                                                value="<?php echo e($column->getName()); ?>"
                                            >
                                                <?php echo e($column->getLabel()); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </label>

                                <label>
                                    <span class="sr-only">
                                        <?php echo e(__('tables::table.sorting.fields.direction.label')); ?>

                                    </span>

                                    <select
                                        x-show="column"
                                        x-model="direction"
                                        style="
                                            background-position: right 0.2rem
                                                center;
                                        "
                                        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                            'rounded-lg border-0 border-gray-300 bg-gray-500/5 py-1 pl-2 pr-6 text-xs font-medium focus:border-primary-500 focus:ring-0 focus:ring-primary-500 sm:text-sm',
                                            'dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500' => config('tables.dark_mode'),
                                        ]); ?>"
                                    >
                                        <option value="asc">
                                            <?php echo e(__('tables::table.sorting.fields.direction.options.asc')); ?>

                                        </option>

                                        <option value="desc">
                                            <?php echo e(__('tables::table.sorting.fields.direction.options.desc')); ?>

                                        </option>
                                    </select>
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if($content): ?>
                    <?php echo e($content->with(['records' => $records])); ?>

                <?php else: ?>
                    <?php if (isset($component)) { $__componentOriginal619b5702a4dba2316335176c43ec06a2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal619b5702a4dba2316335176c43ec06a2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-support::components.grid.index','data' => ['wire:sortable' => true,'wire:end.stop' => 'reorderTable($event.target.sortable.toArray())','wire:sortable.options' => '{ animation: 100 }','default' => $contentGrid['default'] ?? 1,'sm' => $contentGrid['sm'] ?? null,'md' => $contentGrid['md'] ?? null,'lg' => $contentGrid['lg'] ?? null,'xl' => $contentGrid['xl'] ?? null,'twoXl' => $contentGrid['2xl'] ?? null,'class' => 
                            \Illuminate\Support\Arr::toCssClasses([
                                'divide-y' => ! $contentGrid,
                                'p-2 gap-2' => $contentGrid,
                                'dark:divide-gray-700' => config('tables.dark_mode'),
                            ])
                        ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament-support::grid'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:sortable' => true,'wire:end.stop' => 'reorderTable($event.target.sortable.toArray())','wire:sortable.options' => '{ animation: 100 }','default' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($contentGrid['default'] ?? 1),'sm' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($contentGrid['sm'] ?? null),'md' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($contentGrid['md'] ?? null),'lg' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($contentGrid['lg'] ?? null),'xl' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($contentGrid['xl'] ?? null),'two-xl' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($contentGrid['2xl'] ?? null),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(
                            \Illuminate\Support\Arr::toCssClasses([
                                'divide-y' => ! $contentGrid,
                                'p-2 gap-2' => $contentGrid,
                                'dark:divide-gray-700' => config('tables.dark_mode'),
                            ])
                        )]); ?>
                        <?php $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $recordAction = $getRecordAction($record);
                                $recordKey = $getRecordKey($record);
                                $recordUrl = $getRecordUrl($record);

                                $collapsibleColumnsLayout?->record($record);
                                $hasCollapsibleColumnsLayout = $collapsibleColumnsLayout && (! $collapsibleColumnsLayout->isHidden());
                            ?>

                            <div
                                <?php if($hasCollapsibleColumnsLayout): ?>
                                    x-data="{ isCollapsed: <?php echo \Illuminate\Support\Js::from($collapsibleColumnsLayout->isCollapsed())->toHtml() ?> }"
                                    x-init="$dispatch('collapsible-table-row-initialized')"
                                    x-on:expand-all-table-rows.window="isCollapsed = false"
                                    x-on:collapse-all-table-rows.window="isCollapsed = true"
                                <?php endif; ?>
                                wire:key="<?php echo e($this->id); ?>.table.records.<?php echo e($recordKey); ?>"
                                <?php if($isReordering): ?>
                                    wire:sortable.item="<?php echo e($recordKey); ?>"
                                    wire:sortable.handle
                                <?php endif; ?>
                            >
                                <div
                                    x-bind:class="{
                                        'bg-gray-50 <?php echo e(config('tables.dark_mode') ? 'dark:bg-gray-500/10' : ''); ?>':
                                            isRecordSelected('<?php echo e($recordKey); ?>'),
                                    }"
                                    class="<?php echo \Illuminate\Support\Arr::toCssClasses(array_merge(
                                        [
                                            'relative h-full px-4 transition',
                                            'hover:bg-gray-50' => $recordUrl || $recordAction,
                                            'dark:hover:bg-gray-500/10' => ($recordUrl || $recordAction) && config('tables.dark_mode'),
                                            'dark:border-gray-600' => (! $contentGrid) && config('tables.dark_mode'),
                                            'group' => $isReordering,
                                            'rounded-xl border border-gray-200 shadow-sm' => $contentGrid,
                                            'dark:border-gray-700 dark:bg-gray-700/40' => $contentGrid && config('tables.dark_mode'),
                                        ],
                                        $getRecordClasses($record),
                                    )); ?>"
                                >
                                    <div
                                        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                            'items-center gap-4 md:mr-0 md:flex rtl:md:ml-0' => (! $contentGrid),
                                            'mr-6 rtl:ml-6 rtl:mr-0' => $isSelectionEnabled || $hasCollapsibleColumnsLayout || $isReordering,
                                        ]); ?>"
                                    >
                                        <?php if (isset($component)) { $__componentOriginal14cbf6a08ae3fc068141590b309751c7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal14cbf6a08ae3fc068141590b309751c7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.reorder.handle','data' => ['class' => 
                                                \Illuminate\Support\Arr::toCssClasses([
                                                    'absolute top-3 right-3 rtl:right-auto rtl:left-3',
                                                    'md:relative md:top-0 md:right-0 rtl:md:left-0' => ! $contentGrid,
                                                    'hidden' => ! $isReordering,
                                                ])
                                            ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::reorder.handle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(
                                                \Illuminate\Support\Arr::toCssClasses([
                                                    'absolute top-3 right-3 rtl:right-auto rtl:left-3',
                                                    'md:relative md:top-0 md:right-0 rtl:md:left-0' => ! $contentGrid,
                                                    'hidden' => ! $isReordering,
                                                ])
                                            )]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal14cbf6a08ae3fc068141590b309751c7)): ?>
<?php $attributes = $__attributesOriginal14cbf6a08ae3fc068141590b309751c7; ?>
<?php unset($__attributesOriginal14cbf6a08ae3fc068141590b309751c7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal14cbf6a08ae3fc068141590b309751c7)): ?>
<?php $component = $__componentOriginal14cbf6a08ae3fc068141590b309751c7; ?>
<?php unset($__componentOriginal14cbf6a08ae3fc068141590b309751c7); ?>
<?php endif; ?>

                                        <?php if($isSelectionEnabled): ?>
                                            <?php if (isset($component)) { $__componentOriginalcf5e41131e15ad2a36f9d4e05dd93409 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcf5e41131e15ad2a36f9d4e05dd93409 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.checkbox.index','data' => ['xModel' => 'selectedRecords','value' => $recordKey,'label' => __('tables::table.fields.bulk_select_record.label', ['key' => $recordKey]),'class' => 
                                                    \Illuminate\Support\Arr::toCssClasses([
                                                        'filament-tables-record-checkbox absolute top-3 right-3 rtl:right-auto rtl:left-3',
                                                        'md:relative md:top-0 md:right-0 rtl:md:left-0' => ! $contentGrid,
                                                        'hidden' => $isReordering,
                                                    ])
                                                ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::checkbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-model' => 'selectedRecords','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recordKey),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('tables::table.fields.bulk_select_record.label', ['key' => $recordKey])),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(
                                                    \Illuminate\Support\Arr::toCssClasses([
                                                        'filament-tables-record-checkbox absolute top-3 right-3 rtl:right-auto rtl:left-3',
                                                        'md:relative md:top-0 md:right-0 rtl:md:left-0' => ! $contentGrid,
                                                        'hidden' => $isReordering,
                                                    ])
                                                )]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcf5e41131e15ad2a36f9d4e05dd93409)): ?>
<?php $attributes = $__attributesOriginalcf5e41131e15ad2a36f9d4e05dd93409; ?>
<?php unset($__attributesOriginalcf5e41131e15ad2a36f9d4e05dd93409); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcf5e41131e15ad2a36f9d4e05dd93409)): ?>
<?php $component = $__componentOriginalcf5e41131e15ad2a36f9d4e05dd93409; ?>
<?php unset($__componentOriginalcf5e41131e15ad2a36f9d4e05dd93409); ?>
<?php endif; ?>
                                        <?php endif; ?>

                                        <?php if($hasCollapsibleColumnsLayout): ?>
                                            <div
                                                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                                    'absolute right-1 rtl:left-1 rtl:right-auto',
                                                    'top-10' => $isSelectionEnabled,
                                                    'top-1' => ! $isSelectionEnabled,
                                                    'md:relative md:right-0 md:top-0 rtl:md:left-0' => ! $contentGrid,
                                                    'hidden' => $isReordering,
                                                ]); ?>"
                                            >
                                                <?php if (isset($component)) { $__componentOriginalefe22f8cc3ec165cd36d597b30b8510b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalefe22f8cc3ec165cd36d597b30b8510b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.icon-button','data' => ['icon' => 'heroicon-s-chevron-down','color' => 'secondary','size' => 'sm','xOn:click' => 'isCollapsed = ! isCollapsed','xBind:class' => 'isCollapsed || \'-rotate-180\'','class' => 'transition']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::icon-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-s-chevron-down','color' => 'secondary','size' => 'sm','x-on:click' => 'isCollapsed = ! isCollapsed','x-bind:class' => 'isCollapsed || \'-rotate-180\'','class' => 'transition']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalefe22f8cc3ec165cd36d597b30b8510b)): ?>
<?php $attributes = $__attributesOriginalefe22f8cc3ec165cd36d597b30b8510b; ?>
<?php unset($__attributesOriginalefe22f8cc3ec165cd36d597b30b8510b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalefe22f8cc3ec165cd36d597b30b8510b)): ?>
<?php $component = $__componentOriginalefe22f8cc3ec165cd36d597b30b8510b; ?>
<?php unset($__componentOriginalefe22f8cc3ec165cd36d597b30b8510b); ?>
<?php endif; ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if($recordUrl): ?>
                                            <a
                                                href="<?php echo e($recordUrl); ?>"
                                                class="filament-tables-record-url-link block flex-1 py-3"
                                            >
                                                <?php if (isset($component)) { $__componentOriginald1b6d0c0005f6495fd81d3884781d290 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald1b6d0c0005f6495fd81d3884781d290 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.columns.layout','data' => ['components' => $getColumnsLayout(),'record' => $record,'recordKey' => $recordKey,'rowLoop' => $loop]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::columns.layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['components' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getColumnsLayout()),'record' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($record),'record-key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recordKey),'row-loop' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($loop)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald1b6d0c0005f6495fd81d3884781d290)): ?>
<?php $attributes = $__attributesOriginald1b6d0c0005f6495fd81d3884781d290; ?>
<?php unset($__attributesOriginald1b6d0c0005f6495fd81d3884781d290); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald1b6d0c0005f6495fd81d3884781d290)): ?>
<?php $component = $__componentOriginald1b6d0c0005f6495fd81d3884781d290; ?>
<?php unset($__componentOriginald1b6d0c0005f6495fd81d3884781d290); ?>
<?php endif; ?>
                                            </a>
                                        <?php elseif($recordAction): ?>
                                            <?php
                                                if ($this->getCachedTableAction($recordAction)) {
                                                    $recordWireClickAction = "mountTableAction('{$recordAction}', '{$recordKey}')";
                                                } else {
                                                    $recordWireClickAction = "{$recordAction}('{$recordKey}')";
                                                }
                                            ?>

                                            <button
                                                wire:click="<?php echo e($recordWireClickAction); ?>"
                                                wire:target="<?php echo e($recordWireClickAction); ?>"
                                                wire:loading.attr="disabled"
                                                wire:loading.class="cursor-wait opacity-70"
                                                type="button"
                                                class="filament-tables-record-action-button block flex-1 py-3"
                                            >
                                                <?php if (isset($component)) { $__componentOriginald1b6d0c0005f6495fd81d3884781d290 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald1b6d0c0005f6495fd81d3884781d290 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.columns.layout','data' => ['components' => $getColumnsLayout(),'record' => $record,'recordKey' => $recordKey,'rowLoop' => $loop]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::columns.layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['components' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getColumnsLayout()),'record' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($record),'record-key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recordKey),'row-loop' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($loop)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald1b6d0c0005f6495fd81d3884781d290)): ?>
<?php $attributes = $__attributesOriginald1b6d0c0005f6495fd81d3884781d290; ?>
<?php unset($__attributesOriginald1b6d0c0005f6495fd81d3884781d290); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald1b6d0c0005f6495fd81d3884781d290)): ?>
<?php $component = $__componentOriginald1b6d0c0005f6495fd81d3884781d290; ?>
<?php unset($__componentOriginald1b6d0c0005f6495fd81d3884781d290); ?>
<?php endif; ?>
                                            </button>
                                        <?php else: ?>
                                            <div class="flex-1 py-3">
                                                <?php if (isset($component)) { $__componentOriginald1b6d0c0005f6495fd81d3884781d290 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald1b6d0c0005f6495fd81d3884781d290 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.columns.layout','data' => ['components' => $getColumnsLayout(),'record' => $record,'recordKey' => $recordKey,'rowLoop' => $loop]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::columns.layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['components' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getColumnsLayout()),'record' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($record),'record-key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recordKey),'row-loop' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($loop)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald1b6d0c0005f6495fd81d3884781d290)): ?>
<?php $attributes = $__attributesOriginald1b6d0c0005f6495fd81d3884781d290; ?>
<?php unset($__attributesOriginald1b6d0c0005f6495fd81d3884781d290); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald1b6d0c0005f6495fd81d3884781d290)): ?>
<?php $component = $__componentOriginald1b6d0c0005f6495fd81d3884781d290; ?>
<?php unset($__componentOriginald1b6d0c0005f6495fd81d3884781d290); ?>
<?php endif; ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if(count($actions)): ?>
                                            <?php if (isset($component)) { $__componentOriginal9a8b72bf300c2fa6dda80f8a829adc94 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9a8b72bf300c2fa6dda80f8a829adc94 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.actions.index','data' => ['actions' => $actions,'alignment' => $actionsPosition === ActionsPosition::AfterContent ? 'left' : 'left md:right','record' => $record,'wrap' => '-md','class' => 
                                                    \Illuminate\Support\Arr::toCssClasses([
                                                        'absolute bottom-1 right-1 rtl:right-auto rtl:left-1' => $actionsPosition === ActionsPosition::BottomCorner,
                                                        'md:relative md:bottom-0 md:right-0 rtl:md:left-0' => $actionsPosition === ActionsPosition::BottomCorner && (! $contentGrid),
                                                        'mb-3' => $actionsPosition === ActionsPosition::AfterContent,
                                                        'md:mb-0' => $actionsPosition === ActionsPosition::AfterContent && (! $contentGrid),
                                                        'hidden' => $isReordering,
                                                    ])
                                                ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($actions),'alignment' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($actionsPosition === ActionsPosition::AfterContent ? 'left' : 'left md:right'),'record' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($record),'wrap' => '-md','class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(
                                                    \Illuminate\Support\Arr::toCssClasses([
                                                        'absolute bottom-1 right-1 rtl:right-auto rtl:left-1' => $actionsPosition === ActionsPosition::BottomCorner,
                                                        'md:relative md:bottom-0 md:right-0 rtl:md:left-0' => $actionsPosition === ActionsPosition::BottomCorner && (! $contentGrid),
                                                        'mb-3' => $actionsPosition === ActionsPosition::AfterContent,
                                                        'md:mb-0' => $actionsPosition === ActionsPosition::AfterContent && (! $contentGrid),
                                                        'hidden' => $isReordering,
                                                    ])
                                                )]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9a8b72bf300c2fa6dda80f8a829adc94)): ?>
<?php $attributes = $__attributesOriginal9a8b72bf300c2fa6dda80f8a829adc94; ?>
<?php unset($__attributesOriginal9a8b72bf300c2fa6dda80f8a829adc94); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9a8b72bf300c2fa6dda80f8a829adc94)): ?>
<?php $component = $__componentOriginal9a8b72bf300c2fa6dda80f8a829adc94; ?>
<?php unset($__componentOriginal9a8b72bf300c2fa6dda80f8a829adc94); ?>
<?php endif; ?>
                                        <?php endif; ?>
                                    </div>

                                    <?php if($hasCollapsibleColumnsLayout): ?>
                                        <div
                                            x-show="! isCollapsed"
                                            x-collapse
                                            x-cloak
                                            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                                '-mx-2 pb-2',
                                                'md:pl-20 rtl:md:pl-0 rtl:md:pr-20' => (! $contentGrid) && $isSelectionEnabled,
                                                'md:pl-12 rtl:md:pl-0 rtl:md:pr-12' => (! $contentGrid) && (! $isSelectionEnabled),
                                                'hidden' => $isReordering,
                                            ]); ?>"
                                        >
                                            <?php echo e($collapsibleColumnsLayout->viewData(['recordKey' => $recordKey])); ?>

                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal619b5702a4dba2316335176c43ec06a2)): ?>
<?php $attributes = $__attributesOriginal619b5702a4dba2316335176c43ec06a2; ?>
<?php unset($__attributesOriginal619b5702a4dba2316335176c43ec06a2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal619b5702a4dba2316335176c43ec06a2)): ?>
<?php $component = $__componentOriginal619b5702a4dba2316335176c43ec06a2; ?>
<?php unset($__componentOriginal619b5702a4dba2316335176c43ec06a2); ?>
<?php endif; ?>
                <?php endif; ?>

                <?php if(($content || $hasColumnsLayout) && $contentFooter): ?>
                    <?php echo e($contentFooter->with(['columns' => $columns, 'records' => $records])); ?>

                <?php endif; ?>
            <?php elseif(($records !== null) && count($records)): ?>
                <?php if (isset($component)) { $__componentOriginale8791752ab9231dc4280afc867183d68 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale8791752ab9231dc4280afc867183d68 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.table','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                     <?php $__env->slot('header', null, []); ?> 
                        <?php if($isReordering): ?>
                            <th></th>
                        <?php else: ?>
                            <?php if(count($actions) && $actionsPosition === ActionsPosition::BeforeCells): ?>
                                <?php if($actionsColumnLabel): ?>
                                    <?php if (isset($component)) { $__componentOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.header-cell','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::header-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                                        <?php echo e($actionsColumnLabel); ?>

                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b)): ?>
<?php $attributes = $__attributesOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b; ?>
<?php unset($__attributesOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b)): ?>
<?php $component = $__componentOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b; ?>
<?php unset($__componentOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b); ?>
<?php endif; ?>
                                <?php else: ?>
                                    <th class="w-5"></th>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if($isSelectionEnabled && $recordCheckboxPosition === RecordCheckboxPosition::BeforeCells): ?>
                                <?php if (isset($component)) { $__componentOriginalc41e0c64e80f29562171958a154e5195 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc41e0c64e80f29562171958a154e5195 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.checkbox.cell','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::checkbox.cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                                    <?php if (isset($component)) { $__componentOriginalcf5e41131e15ad2a36f9d4e05dd93409 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcf5e41131e15ad2a36f9d4e05dd93409 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.checkbox.index','data' => ['label' => __('tables::table.fields.bulk_select_page.label'),'xOn:click' => 'toggleSelectRecordsOnPage','xBind:checked' => '
                                            let recordsOnPage = getRecordsOnPage()

                                            if (recordsOnPage.length && areRecordsSelected(recordsOnPage)) {
                                                $el.checked = true

                                                return \'checked\'
                                            }

                                            $el.checked = false

                                            return null
                                        ']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::checkbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('tables::table.fields.bulk_select_page.label')),'x-on:click' => 'toggleSelectRecordsOnPage','x-bind:checked' => '
                                            let recordsOnPage = getRecordsOnPage()

                                            if (recordsOnPage.length && areRecordsSelected(recordsOnPage)) {
                                                $el.checked = true

                                                return \'checked\'
                                            }

                                            $el.checked = false

                                            return null
                                        ']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcf5e41131e15ad2a36f9d4e05dd93409)): ?>
<?php $attributes = $__attributesOriginalcf5e41131e15ad2a36f9d4e05dd93409; ?>
<?php unset($__attributesOriginalcf5e41131e15ad2a36f9d4e05dd93409); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcf5e41131e15ad2a36f9d4e05dd93409)): ?>
<?php $component = $__componentOriginalcf5e41131e15ad2a36f9d4e05dd93409; ?>
<?php unset($__componentOriginalcf5e41131e15ad2a36f9d4e05dd93409); ?>
<?php endif; ?>
                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc41e0c64e80f29562171958a154e5195)): ?>
<?php $attributes = $__attributesOriginalc41e0c64e80f29562171958a154e5195; ?>
<?php unset($__attributesOriginalc41e0c64e80f29562171958a154e5195); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc41e0c64e80f29562171958a154e5195)): ?>
<?php $component = $__componentOriginalc41e0c64e80f29562171958a154e5195; ?>
<?php unset($__componentOriginalc41e0c64e80f29562171958a154e5195); ?>
<?php endif; ?>
                            <?php endif; ?>

                            <?php if(count($actions) && $actionsPosition === ActionsPosition::BeforeColumns): ?>
                                <?php if($actionsColumnLabel): ?>
                                    <?php if (isset($component)) { $__componentOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.header-cell','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::header-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                                        <?php echo e($actionsColumnLabel); ?>

                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b)): ?>
<?php $attributes = $__attributesOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b; ?>
<?php unset($__attributesOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b)): ?>
<?php $component = $__componentOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b; ?>
<?php unset($__componentOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b); ?>
<?php endif; ?>
                                <?php else: ?>
                                    <th class="w-5"></th>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if (isset($component)) { $__componentOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.header-cell','data' => ['extraAttributes' => $column->getExtraHeaderAttributes(),'isSortColumn' => $getSortColumn() === $column->getName(),'name' => $column->getName(),'alignment' => $column->getAlignment(),'sortable' => $column->isSortable() && (! $isReordering),'sortDirection' => $getSortDirection(),'class' => 'filament-table-header-cell-'.e(\Illuminate\Support\Str::of($column->getName())->camel()->kebab()).' '.e($getHiddenClasses($column)).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::header-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['extra-attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($column->getExtraHeaderAttributes()),'is-sort-column' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getSortColumn() === $column->getName()),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($column->getName()),'alignment' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($column->getAlignment()),'sortable' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($column->isSortable() && (! $isReordering)),'sort-direction' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getSortDirection()),'class' => 'filament-table-header-cell-'.e(\Illuminate\Support\Str::of($column->getName())->camel()->kebab()).' '.e($getHiddenClasses($column)).'']); ?>
                                <?php echo e($column->getLabel()); ?>

                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b)): ?>
<?php $attributes = $__attributesOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b; ?>
<?php unset($__attributesOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b)): ?>
<?php $component = $__componentOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b; ?>
<?php unset($__componentOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b); ?>
<?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <?php if(! $isReordering): ?>
                            <?php if(count($actions) && $actionsPosition === ActionsPosition::AfterColumns): ?>
                                <?php if($actionsColumnLabel): ?>
                                    <?php if (isset($component)) { $__componentOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.header-cell','data' => ['alignment' => 'right']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::header-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['alignment' => 'right']); ?>
                                        <?php echo e($actionsColumnLabel); ?>

                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b)): ?>
<?php $attributes = $__attributesOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b; ?>
<?php unset($__attributesOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b)): ?>
<?php $component = $__componentOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b; ?>
<?php unset($__componentOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b); ?>
<?php endif; ?>
                                <?php else: ?>
                                    <th class="w-5"></th>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if($isSelectionEnabled && $recordCheckboxPosition === RecordCheckboxPosition::AfterCells): ?>
                                <?php if (isset($component)) { $__componentOriginalc41e0c64e80f29562171958a154e5195 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc41e0c64e80f29562171958a154e5195 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.checkbox.cell','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::checkbox.cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                                    <?php if (isset($component)) { $__componentOriginalcf5e41131e15ad2a36f9d4e05dd93409 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcf5e41131e15ad2a36f9d4e05dd93409 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.checkbox.index','data' => ['label' => __('tables::table.fields.bulk_select_page.label'),'xOn:click' => 'toggleSelectRecordsOnPage','xBind:checked' => '
                                            let recordsOnPage = getRecordsOnPage()

                                            if (recordsOnPage.length && areRecordsSelected(recordsOnPage)) {
                                                $el.checked = true

                                                return \'checked\'
                                            }

                                            $el.checked = false

                                            return null
                                        ']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::checkbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('tables::table.fields.bulk_select_page.label')),'x-on:click' => 'toggleSelectRecordsOnPage','x-bind:checked' => '
                                            let recordsOnPage = getRecordsOnPage()

                                            if (recordsOnPage.length && areRecordsSelected(recordsOnPage)) {
                                                $el.checked = true

                                                return \'checked\'
                                            }

                                            $el.checked = false

                                            return null
                                        ']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcf5e41131e15ad2a36f9d4e05dd93409)): ?>
<?php $attributes = $__attributesOriginalcf5e41131e15ad2a36f9d4e05dd93409; ?>
<?php unset($__attributesOriginalcf5e41131e15ad2a36f9d4e05dd93409); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcf5e41131e15ad2a36f9d4e05dd93409)): ?>
<?php $component = $__componentOriginalcf5e41131e15ad2a36f9d4e05dd93409; ?>
<?php unset($__componentOriginalcf5e41131e15ad2a36f9d4e05dd93409); ?>
<?php endif; ?>
                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc41e0c64e80f29562171958a154e5195)): ?>
<?php $attributes = $__attributesOriginalc41e0c64e80f29562171958a154e5195; ?>
<?php unset($__attributesOriginalc41e0c64e80f29562171958a154e5195); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc41e0c64e80f29562171958a154e5195)): ?>
<?php $component = $__componentOriginalc41e0c64e80f29562171958a154e5195; ?>
<?php unset($__componentOriginalc41e0c64e80f29562171958a154e5195); ?>
<?php endif; ?>
                            <?php endif; ?>

                            <?php if(count($actions) && $actionsPosition === ActionsPosition::AfterCells): ?>
                                <?php if($actionsColumnLabel): ?>
                                    <?php if (isset($component)) { $__componentOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.header-cell','data' => ['alignment' => 'right']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::header-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['alignment' => 'right']); ?>
                                        <?php echo e($actionsColumnLabel); ?>

                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b)): ?>
<?php $attributes = $__attributesOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b; ?>
<?php unset($__attributesOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b)): ?>
<?php $component = $__componentOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b; ?>
<?php unset($__componentOriginal8ce2b21d94be4b2ac32ad24ff6dfc93b); ?>
<?php endif; ?>
                                <?php else: ?>
                                    <th class="w-5"></th>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                     <?php $__env->endSlot(); ?>

                    <?php if($isColumnSearchVisible): ?>
                        <?php if (isset($component)) { $__componentOriginal919ddf55d1edf7e6d8ca3bedc1e9b76c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal919ddf55d1edf7e6d8ca3bedc1e9b76c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.row','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                            <?php if($isReordering): ?>
                                <td></td>
                            <?php else: ?>
                                <?php if(count($actions) && in_array($actionsPosition, [ActionsPosition::BeforeCells, ActionsPosition::BeforeColumns])): ?>
                                    <td></td>
                                <?php endif; ?>

                                <?php if($isSelectionEnabled && $recordCheckboxPosition === RecordCheckboxPosition::BeforeCells): ?>
                                    <td></td>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if (isset($component)) { $__componentOriginalfb906351acaa93ff5a874f5e70fd8ff6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfb906351acaa93ff5a874f5e70fd8ff6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.cell','data' => ['class' => 'filament-table-individual-search-cell-'.e(\Illuminate\Support\Str::of($column->getName())->camel()->kebab()).' px-4 py-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'filament-table-individual-search-cell-'.e(\Illuminate\Support\Str::of($column->getName())->camel()->kebab()).' px-4 py-1']); ?>
                                    <?php if($column->isIndividuallySearchable()): ?>
                                        <?php if (isset($component)) { $__componentOriginal4b04fa758b77ab5f3a62cdb93e6ff6e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4b04fa758b77ab5f3a62cdb93e6ff6e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.search-input','data' => ['wireModel' => 'tableColumnSearchQueries.'.e($column->getName()).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::search-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire-model' => 'tableColumnSearchQueries.'.e($column->getName()).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4b04fa758b77ab5f3a62cdb93e6ff6e9)): ?>
<?php $attributes = $__attributesOriginal4b04fa758b77ab5f3a62cdb93e6ff6e9; ?>
<?php unset($__attributesOriginal4b04fa758b77ab5f3a62cdb93e6ff6e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4b04fa758b77ab5f3a62cdb93e6ff6e9)): ?>
<?php $component = $__componentOriginal4b04fa758b77ab5f3a62cdb93e6ff6e9; ?>
<?php unset($__componentOriginal4b04fa758b77ab5f3a62cdb93e6ff6e9); ?>
<?php endif; ?>
                                    <?php endif; ?>
                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfb906351acaa93ff5a874f5e70fd8ff6)): ?>
<?php $attributes = $__attributesOriginalfb906351acaa93ff5a874f5e70fd8ff6; ?>
<?php unset($__attributesOriginalfb906351acaa93ff5a874f5e70fd8ff6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfb906351acaa93ff5a874f5e70fd8ff6)): ?>
<?php $component = $__componentOriginalfb906351acaa93ff5a874f5e70fd8ff6; ?>
<?php unset($__componentOriginalfb906351acaa93ff5a874f5e70fd8ff6); ?>
<?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            <?php if(! $isReordering): ?>
                                <?php if(count($actions) && in_array($actionsPosition, [ActionsPosition::AfterColumns, ActionsPosition::AfterCells])): ?>
                                    <td></td>
                                <?php endif; ?>

                                <?php if($isSelectionEnabled && $recordCheckboxPosition === RecordCheckboxPosition::AfterCells): ?>
                                    <td></td>
                                <?php endif; ?>
                            <?php endif; ?>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal919ddf55d1edf7e6d8ca3bedc1e9b76c)): ?>
<?php $attributes = $__attributesOriginal919ddf55d1edf7e6d8ca3bedc1e9b76c; ?>
<?php unset($__attributesOriginal919ddf55d1edf7e6d8ca3bedc1e9b76c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal919ddf55d1edf7e6d8ca3bedc1e9b76c)): ?>
<?php $component = $__componentOriginal919ddf55d1edf7e6d8ca3bedc1e9b76c; ?>
<?php unset($__componentOriginal919ddf55d1edf7e6d8ca3bedc1e9b76c); ?>
<?php endif; ?>
                    <?php endif; ?>

                    <?php if(($records !== null) && count($records)): ?>
                        <?php $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $recordAction = $getRecordAction($record);
                                $recordKey = $getRecordKey($record);
                                $recordUrl = $getRecordUrl($record);
                            ?>

                            <?php if (isset($component)) { $__componentOriginal919ddf55d1edf7e6d8ca3bedc1e9b76c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal919ddf55d1edf7e6d8ca3bedc1e9b76c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.row','data' => ['recordAction' => $recordAction,'recordUrl' => $recordUrl,'wire:key' => $this->id . '.table.records.' . $recordKey,'wire:sortable.item' => $isReordering ? $recordKey : null,'wire:sortable.handle' => $isReordering,'striped' => $isStriped,'xBind:class' => '{
                                    \'bg-gray-50 '.e(config('tables.dark_mode') ? 'dark:bg-gray-500/10' : '').'\': isRecordSelected(\''.e($recordKey).'\'),
                                }','class' => 
                                    \Illuminate\Support\Arr::toCssClasses(array_merge(
                                        [
                                            'group cursor-move' => $isReordering,
                                        ],
                                        $getRecordClasses($record),
                                    ))
                                ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['record-action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recordAction),'record-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recordUrl),'wire:key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->id . '.table.records.' . $recordKey),'wire:sortable.item' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isReordering ? $recordKey : null),'wire:sortable.handle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isReordering),'striped' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isStriped),'x-bind:class' => '{
                                    \'bg-gray-50 '.e(config('tables.dark_mode') ? 'dark:bg-gray-500/10' : '').'\': isRecordSelected(\''.e($recordKey).'\'),
                                }','class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(
                                    \Illuminate\Support\Arr::toCssClasses(array_merge(
                                        [
                                            'group cursor-move' => $isReordering,
                                        ],
                                        $getRecordClasses($record),
                                    ))
                                )]); ?>
                                <?php if (isset($component)) { $__componentOriginal45695ae72a548a93b3cb755c034d3bee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal45695ae72a548a93b3cb755c034d3bee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.reorder.cell','data' => ['class' => 
                                        \Illuminate\Support\Arr::toCssClasses([
                                            'hidden' => ! $isReordering,
                                        ])
                                    ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::reorder.cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(
                                        \Illuminate\Support\Arr::toCssClasses([
                                            'hidden' => ! $isReordering,
                                        ])
                                    )]); ?>
                                    <?php if($isReordering): ?>
                                        <?php if (isset($component)) { $__componentOriginal14cbf6a08ae3fc068141590b309751c7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal14cbf6a08ae3fc068141590b309751c7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.reorder.handle','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::reorder.handle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal14cbf6a08ae3fc068141590b309751c7)): ?>
<?php $attributes = $__attributesOriginal14cbf6a08ae3fc068141590b309751c7; ?>
<?php unset($__attributesOriginal14cbf6a08ae3fc068141590b309751c7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal14cbf6a08ae3fc068141590b309751c7)): ?>
<?php $component = $__componentOriginal14cbf6a08ae3fc068141590b309751c7; ?>
<?php unset($__componentOriginal14cbf6a08ae3fc068141590b309751c7); ?>
<?php endif; ?>
                                    <?php endif; ?>
                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal45695ae72a548a93b3cb755c034d3bee)): ?>
<?php $attributes = $__attributesOriginal45695ae72a548a93b3cb755c034d3bee; ?>
<?php unset($__attributesOriginal45695ae72a548a93b3cb755c034d3bee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal45695ae72a548a93b3cb755c034d3bee)): ?>
<?php $component = $__componentOriginal45695ae72a548a93b3cb755c034d3bee; ?>
<?php unset($__componentOriginal45695ae72a548a93b3cb755c034d3bee); ?>
<?php endif; ?>

                                <?php if(count($actions) && $actionsPosition === ActionsPosition::BeforeCells): ?>
                                    <?php if (isset($component)) { $__componentOriginal174052115c30c7ca400e766c0151d81e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal174052115c30c7ca400e766c0151d81e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.actions.cell','data' => ['class' => 
                                            \Illuminate\Support\Arr::toCssClasses([
                                                'hidden' => $isReordering,
                                            ])
                                        ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::actions.cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(
                                            \Illuminate\Support\Arr::toCssClasses([
                                                'hidden' => $isReordering,
                                            ])
                                        )]); ?>
                                        <?php if (isset($component)) { $__componentOriginal9a8b72bf300c2fa6dda80f8a829adc94 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9a8b72bf300c2fa6dda80f8a829adc94 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.actions.index','data' => ['actions' => $actions,'record' => $record]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($actions),'record' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($record)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9a8b72bf300c2fa6dda80f8a829adc94)): ?>
<?php $attributes = $__attributesOriginal9a8b72bf300c2fa6dda80f8a829adc94; ?>
<?php unset($__attributesOriginal9a8b72bf300c2fa6dda80f8a829adc94); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9a8b72bf300c2fa6dda80f8a829adc94)): ?>
<?php $component = $__componentOriginal9a8b72bf300c2fa6dda80f8a829adc94; ?>
<?php unset($__componentOriginal9a8b72bf300c2fa6dda80f8a829adc94); ?>
<?php endif; ?>
                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal174052115c30c7ca400e766c0151d81e)): ?>
<?php $attributes = $__attributesOriginal174052115c30c7ca400e766c0151d81e; ?>
<?php unset($__attributesOriginal174052115c30c7ca400e766c0151d81e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal174052115c30c7ca400e766c0151d81e)): ?>
<?php $component = $__componentOriginal174052115c30c7ca400e766c0151d81e; ?>
<?php unset($__componentOriginal174052115c30c7ca400e766c0151d81e); ?>
<?php endif; ?>
                                <?php endif; ?>

                                <?php if($isSelectionEnabled && $recordCheckboxPosition === RecordCheckboxPosition::BeforeCells): ?>
                                    <?php if($isRecordSelectable($record)): ?>
                                        <?php if (isset($component)) { $__componentOriginalc41e0c64e80f29562171958a154e5195 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc41e0c64e80f29562171958a154e5195 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.checkbox.cell','data' => ['class' => 
                                                \Illuminate\Support\Arr::toCssClasses([
                                                    'hidden' => $isReordering,
                                                ])
                                            ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::checkbox.cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(
                                                \Illuminate\Support\Arr::toCssClasses([
                                                    'hidden' => $isReordering,
                                                ])
                                            )]); ?>
                                            <?php if (isset($component)) { $__componentOriginalcf5e41131e15ad2a36f9d4e05dd93409 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcf5e41131e15ad2a36f9d4e05dd93409 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.checkbox.index','data' => ['xModel' => 'selectedRecords','value' => $recordKey,'label' => __('tables::table.fields.bulk_select_record.label', ['key' => $recordKey]),'class' => 'filament-tables-record-checkbox']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::checkbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-model' => 'selectedRecords','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recordKey),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('tables::table.fields.bulk_select_record.label', ['key' => $recordKey])),'class' => 'filament-tables-record-checkbox']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcf5e41131e15ad2a36f9d4e05dd93409)): ?>
<?php $attributes = $__attributesOriginalcf5e41131e15ad2a36f9d4e05dd93409; ?>
<?php unset($__attributesOriginalcf5e41131e15ad2a36f9d4e05dd93409); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcf5e41131e15ad2a36f9d4e05dd93409)): ?>
<?php $component = $__componentOriginalcf5e41131e15ad2a36f9d4e05dd93409; ?>
<?php unset($__componentOriginalcf5e41131e15ad2a36f9d4e05dd93409); ?>
<?php endif; ?>
                                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc41e0c64e80f29562171958a154e5195)): ?>
<?php $attributes = $__attributesOriginalc41e0c64e80f29562171958a154e5195; ?>
<?php unset($__attributesOriginalc41e0c64e80f29562171958a154e5195); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc41e0c64e80f29562171958a154e5195)): ?>
<?php $component = $__componentOriginalc41e0c64e80f29562171958a154e5195; ?>
<?php unset($__componentOriginalc41e0c64e80f29562171958a154e5195); ?>
<?php endif; ?>
                                    <?php else: ?>
                                        <?php if (isset($component)) { $__componentOriginalfb906351acaa93ff5a874f5e70fd8ff6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfb906351acaa93ff5a874f5e70fd8ff6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.cell','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfb906351acaa93ff5a874f5e70fd8ff6)): ?>
<?php $attributes = $__attributesOriginalfb906351acaa93ff5a874f5e70fd8ff6; ?>
<?php unset($__attributesOriginalfb906351acaa93ff5a874f5e70fd8ff6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfb906351acaa93ff5a874f5e70fd8ff6)): ?>
<?php $component = $__componentOriginalfb906351acaa93ff5a874f5e70fd8ff6; ?>
<?php unset($__componentOriginalfb906351acaa93ff5a874f5e70fd8ff6); ?>
<?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if(count($actions) && $actionsPosition === ActionsPosition::BeforeColumns): ?>
                                    <?php if (isset($component)) { $__componentOriginal174052115c30c7ca400e766c0151d81e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal174052115c30c7ca400e766c0151d81e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.actions.cell','data' => ['class' => 
                                            \Illuminate\Support\Arr::toCssClasses([
                                                'hidden' => $isReordering,
                                            ])
                                        ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::actions.cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(
                                            \Illuminate\Support\Arr::toCssClasses([
                                                'hidden' => $isReordering,
                                            ])
                                        )]); ?>
                                        <?php if (isset($component)) { $__componentOriginal9a8b72bf300c2fa6dda80f8a829adc94 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9a8b72bf300c2fa6dda80f8a829adc94 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.actions.index','data' => ['actions' => $actions,'record' => $record]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($actions),'record' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($record)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9a8b72bf300c2fa6dda80f8a829adc94)): ?>
<?php $attributes = $__attributesOriginal9a8b72bf300c2fa6dda80f8a829adc94; ?>
<?php unset($__attributesOriginal9a8b72bf300c2fa6dda80f8a829adc94); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9a8b72bf300c2fa6dda80f8a829adc94)): ?>
<?php $component = $__componentOriginal9a8b72bf300c2fa6dda80f8a829adc94; ?>
<?php unset($__componentOriginal9a8b72bf300c2fa6dda80f8a829adc94); ?>
<?php endif; ?>
                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal174052115c30c7ca400e766c0151d81e)): ?>
<?php $attributes = $__attributesOriginal174052115c30c7ca400e766c0151d81e; ?>
<?php unset($__attributesOriginal174052115c30c7ca400e766c0151d81e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal174052115c30c7ca400e766c0151d81e)): ?>
<?php $component = $__componentOriginal174052115c30c7ca400e766c0151d81e; ?>
<?php unset($__componentOriginal174052115c30c7ca400e766c0151d81e); ?>
<?php endif; ?>
                                <?php endif; ?>

                                <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $column->record($record);
                                        $column->rowLoop($loop->parent);
                                    ?>

                                    <?php if (isset($component)) { $__componentOriginalfb906351acaa93ff5a874f5e70fd8ff6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfb906351acaa93ff5a874f5e70fd8ff6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.cell','data' => ['wire:key' => ''.e($this->id).'.table.record.'.e($recordKey).'.column.'.e($column->getName()).'','wire:loading.remove.delay' => true,'wire:target' => ''.e(implode(',', \Filament\Tables\Table::LOADING_TARGETS)).'','class' => 'filament-table-cell-'.e(\Illuminate\Support\Str::of($column->getName())->camel()->kebab()).' '.e($getHiddenClasses($column)).'','attributes' => \Filament\Support\prepare_inherited_attributes($column->getExtraCellAttributeBag())]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:key' => ''.e($this->id).'.table.record.'.e($recordKey).'.column.'.e($column->getName()).'','wire:loading.remove.delay' => true,'wire:target' => ''.e(implode(',', \Filament\Tables\Table::LOADING_TARGETS)).'','class' => 'filament-table-cell-'.e(\Illuminate\Support\Str::of($column->getName())->camel()->kebab()).' '.e($getHiddenClasses($column)).'','attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Filament\Support\prepare_inherited_attributes($column->getExtraCellAttributeBag()))]); ?>
                                        <?php if (isset($component)) { $__componentOriginal346e6785e5346e5a118bddf927a02e9b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal346e6785e5346e5a118bddf927a02e9b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.columns.column','data' => ['column' => $column,'record' => $record,'recordAction' => $recordAction,'recordKey' => $recordKey,'recordUrl' => $recordUrl,'isClickDisabled' => $column->isClickDisabled() || $isReordering]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::columns.column'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['column' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($column),'record' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($record),'record-action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recordAction),'record-key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recordKey),'record-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recordUrl),'is-click-disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($column->isClickDisabled() || $isReordering)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal346e6785e5346e5a118bddf927a02e9b)): ?>
<?php $attributes = $__attributesOriginal346e6785e5346e5a118bddf927a02e9b; ?>
<?php unset($__attributesOriginal346e6785e5346e5a118bddf927a02e9b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal346e6785e5346e5a118bddf927a02e9b)): ?>
<?php $component = $__componentOriginal346e6785e5346e5a118bddf927a02e9b; ?>
<?php unset($__componentOriginal346e6785e5346e5a118bddf927a02e9b); ?>
<?php endif; ?>
                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfb906351acaa93ff5a874f5e70fd8ff6)): ?>
<?php $attributes = $__attributesOriginalfb906351acaa93ff5a874f5e70fd8ff6; ?>
<?php unset($__attributesOriginalfb906351acaa93ff5a874f5e70fd8ff6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfb906351acaa93ff5a874f5e70fd8ff6)): ?>
<?php $component = $__componentOriginalfb906351acaa93ff5a874f5e70fd8ff6; ?>
<?php unset($__componentOriginalfb906351acaa93ff5a874f5e70fd8ff6); ?>
<?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                <?php if(count($actions) && $actionsPosition === ActionsPosition::AfterColumns): ?>
                                    <?php if (isset($component)) { $__componentOriginal174052115c30c7ca400e766c0151d81e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal174052115c30c7ca400e766c0151d81e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.actions.cell','data' => ['class' => 
                                            \Illuminate\Support\Arr::toCssClasses([
                                                'hidden' => $isReordering,
                                            ])
                                        ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::actions.cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(
                                            \Illuminate\Support\Arr::toCssClasses([
                                                'hidden' => $isReordering,
                                            ])
                                        )]); ?>
                                        <?php if (isset($component)) { $__componentOriginal9a8b72bf300c2fa6dda80f8a829adc94 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9a8b72bf300c2fa6dda80f8a829adc94 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.actions.index','data' => ['actions' => $actions,'record' => $record]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($actions),'record' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($record)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9a8b72bf300c2fa6dda80f8a829adc94)): ?>
<?php $attributes = $__attributesOriginal9a8b72bf300c2fa6dda80f8a829adc94; ?>
<?php unset($__attributesOriginal9a8b72bf300c2fa6dda80f8a829adc94); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9a8b72bf300c2fa6dda80f8a829adc94)): ?>
<?php $component = $__componentOriginal9a8b72bf300c2fa6dda80f8a829adc94; ?>
<?php unset($__componentOriginal9a8b72bf300c2fa6dda80f8a829adc94); ?>
<?php endif; ?>
                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal174052115c30c7ca400e766c0151d81e)): ?>
<?php $attributes = $__attributesOriginal174052115c30c7ca400e766c0151d81e; ?>
<?php unset($__attributesOriginal174052115c30c7ca400e766c0151d81e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal174052115c30c7ca400e766c0151d81e)): ?>
<?php $component = $__componentOriginal174052115c30c7ca400e766c0151d81e; ?>
<?php unset($__componentOriginal174052115c30c7ca400e766c0151d81e); ?>
<?php endif; ?>
                                <?php endif; ?>

                                <?php if($isSelectionEnabled && $recordCheckboxPosition === RecordCheckboxPosition::AfterCells): ?>
                                    <?php if($isRecordSelectable($record)): ?>
                                        <?php if (isset($component)) { $__componentOriginalc41e0c64e80f29562171958a154e5195 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc41e0c64e80f29562171958a154e5195 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.checkbox.cell','data' => ['class' => 
                                                \Illuminate\Support\Arr::toCssClasses([
                                                    'hidden' => $isReordering,
                                                ])
                                            ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::checkbox.cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(
                                                \Illuminate\Support\Arr::toCssClasses([
                                                    'hidden' => $isReordering,
                                                ])
                                            )]); ?>
                                            <?php if (isset($component)) { $__componentOriginalcf5e41131e15ad2a36f9d4e05dd93409 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcf5e41131e15ad2a36f9d4e05dd93409 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.checkbox.index','data' => ['xModel' => 'selectedRecords','value' => $recordKey,'label' => __('tables::table.fields.bulk_select_record.label', ['key' => $recordKey]),'class' => 'filament-tables-record-checkbox']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::checkbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-model' => 'selectedRecords','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recordKey),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('tables::table.fields.bulk_select_record.label', ['key' => $recordKey])),'class' => 'filament-tables-record-checkbox']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcf5e41131e15ad2a36f9d4e05dd93409)): ?>
<?php $attributes = $__attributesOriginalcf5e41131e15ad2a36f9d4e05dd93409; ?>
<?php unset($__attributesOriginalcf5e41131e15ad2a36f9d4e05dd93409); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcf5e41131e15ad2a36f9d4e05dd93409)): ?>
<?php $component = $__componentOriginalcf5e41131e15ad2a36f9d4e05dd93409; ?>
<?php unset($__componentOriginalcf5e41131e15ad2a36f9d4e05dd93409); ?>
<?php endif; ?>
                                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc41e0c64e80f29562171958a154e5195)): ?>
<?php $attributes = $__attributesOriginalc41e0c64e80f29562171958a154e5195; ?>
<?php unset($__attributesOriginalc41e0c64e80f29562171958a154e5195); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc41e0c64e80f29562171958a154e5195)): ?>
<?php $component = $__componentOriginalc41e0c64e80f29562171958a154e5195; ?>
<?php unset($__componentOriginalc41e0c64e80f29562171958a154e5195); ?>
<?php endif; ?>
                                    <?php else: ?>
                                        <?php if (isset($component)) { $__componentOriginalfb906351acaa93ff5a874f5e70fd8ff6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfb906351acaa93ff5a874f5e70fd8ff6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.cell','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfb906351acaa93ff5a874f5e70fd8ff6)): ?>
<?php $attributes = $__attributesOriginalfb906351acaa93ff5a874f5e70fd8ff6; ?>
<?php unset($__attributesOriginalfb906351acaa93ff5a874f5e70fd8ff6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfb906351acaa93ff5a874f5e70fd8ff6)): ?>
<?php $component = $__componentOriginalfb906351acaa93ff5a874f5e70fd8ff6; ?>
<?php unset($__componentOriginalfb906351acaa93ff5a874f5e70fd8ff6); ?>
<?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if(count($actions) && $actionsPosition === ActionsPosition::AfterCells): ?>
                                    <?php if (isset($component)) { $__componentOriginal174052115c30c7ca400e766c0151d81e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal174052115c30c7ca400e766c0151d81e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.actions.cell','data' => ['class' => 
                                            \Illuminate\Support\Arr::toCssClasses([
                                                'hidden' => $isReordering,
                                            ])
                                        ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::actions.cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(
                                            \Illuminate\Support\Arr::toCssClasses([
                                                'hidden' => $isReordering,
                                            ])
                                        )]); ?>
                                        <?php if (isset($component)) { $__componentOriginal9a8b72bf300c2fa6dda80f8a829adc94 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9a8b72bf300c2fa6dda80f8a829adc94 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.actions.index','data' => ['actions' => $actions,'record' => $record]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($actions),'record' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($record)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9a8b72bf300c2fa6dda80f8a829adc94)): ?>
<?php $attributes = $__attributesOriginal9a8b72bf300c2fa6dda80f8a829adc94; ?>
<?php unset($__attributesOriginal9a8b72bf300c2fa6dda80f8a829adc94); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9a8b72bf300c2fa6dda80f8a829adc94)): ?>
<?php $component = $__componentOriginal9a8b72bf300c2fa6dda80f8a829adc94; ?>
<?php unset($__componentOriginal9a8b72bf300c2fa6dda80f8a829adc94); ?>
<?php endif; ?>
                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal174052115c30c7ca400e766c0151d81e)): ?>
<?php $attributes = $__attributesOriginal174052115c30c7ca400e766c0151d81e; ?>
<?php unset($__attributesOriginal174052115c30c7ca400e766c0151d81e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal174052115c30c7ca400e766c0151d81e)): ?>
<?php $component = $__componentOriginal174052115c30c7ca400e766c0151d81e; ?>
<?php unset($__componentOriginal174052115c30c7ca400e766c0151d81e); ?>
<?php endif; ?>
                                <?php endif; ?>

                                <?php if (isset($component)) { $__componentOriginal356997689ee9d8d9e4fb771427222250 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal356997689ee9d8d9e4fb771427222250 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.loading-cell','data' => ['colspan' => $columnsCount,'wire:loading.class.remove.delay' => 'hidden','class' => 'hidden','wire:key' => $this->id . '.table.records.' . $recordKey . '.loading-cell','wire:target' => ''.e(implode(',', \Filament\Tables\Table::LOADING_TARGETS)).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::loading-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($columnsCount),'wire:loading.class.remove.delay' => 'hidden','class' => 'hidden','wire:key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->id . '.table.records.' . $recordKey . '.loading-cell'),'wire:target' => ''.e(implode(',', \Filament\Tables\Table::LOADING_TARGETS)).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal356997689ee9d8d9e4fb771427222250)): ?>
<?php $attributes = $__attributesOriginal356997689ee9d8d9e4fb771427222250; ?>
<?php unset($__attributesOriginal356997689ee9d8d9e4fb771427222250); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal356997689ee9d8d9e4fb771427222250)): ?>
<?php $component = $__componentOriginal356997689ee9d8d9e4fb771427222250; ?>
<?php unset($__componentOriginal356997689ee9d8d9e4fb771427222250); ?>
<?php endif; ?>
                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal919ddf55d1edf7e6d8ca3bedc1e9b76c)): ?>
<?php $attributes = $__attributesOriginal919ddf55d1edf7e6d8ca3bedc1e9b76c; ?>
<?php unset($__attributesOriginal919ddf55d1edf7e6d8ca3bedc1e9b76c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal919ddf55d1edf7e6d8ca3bedc1e9b76c)): ?>
<?php $component = $__componentOriginal919ddf55d1edf7e6d8ca3bedc1e9b76c; ?>
<?php unset($__componentOriginal919ddf55d1edf7e6d8ca3bedc1e9b76c); ?>
<?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <?php if($contentFooter): ?>
                             <?php $__env->slot('footer', null, []); ?> 
                                <?php echo e($contentFooter->with(['columns' => $columns, 'records' => $records])); ?>

                             <?php $__env->endSlot(); ?>
                        <?php endif; ?>
                    <?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale8791752ab9231dc4280afc867183d68)): ?>
<?php $attributes = $__attributesOriginale8791752ab9231dc4280afc867183d68; ?>
<?php unset($__attributesOriginale8791752ab9231dc4280afc867183d68); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale8791752ab9231dc4280afc867183d68)): ?>
<?php $component = $__componentOriginale8791752ab9231dc4280afc867183d68; ?>
<?php unset($__componentOriginale8791752ab9231dc4280afc867183d68); ?>
<?php endif; ?>
            <?php elseif($records === null): ?>
                <div
                    class="filament-tables-defer-loading-indicator flex items-center justify-center p-6"
                >
                    <div
                        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'flex h-16 w-16 items-center justify-center rounded-full bg-primary-50 text-primary-500',
                            'dark:bg-gray-700' => config('tables.dark_mode'),
                        ]); ?>"
                    >
                        <?php if (isset($component)) { $__componentOriginal68a3024fa61352b757a05bc2899e1852 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal68a3024fa61352b757a05bc2899e1852 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-support::components.loading-indicator','data' => ['class' => 'h-6 w-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament-support::loading-indicator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-6 w-6']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal68a3024fa61352b757a05bc2899e1852)): ?>
<?php $attributes = $__attributesOriginal68a3024fa61352b757a05bc2899e1852; ?>
<?php unset($__attributesOriginal68a3024fa61352b757a05bc2899e1852); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal68a3024fa61352b757a05bc2899e1852)): ?>
<?php $component = $__componentOriginal68a3024fa61352b757a05bc2899e1852; ?>
<?php unset($__componentOriginal68a3024fa61352b757a05bc2899e1852); ?>
<?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <?php if($emptyState = $getEmptyState()): ?>
                    <?php echo e($emptyState); ?>

                <?php else: ?>
                    <div class="flex items-center justify-center p-4">
                        <?php if (isset($component)) { $__componentOriginalaf605259d054ed4913ba4ce9aa727e24 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaf605259d054ed4913ba4ce9aa727e24 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.empty-state.index','data' => ['icon' => $getEmptyStateIcon(),'actions' => $getEmptyStateActions(),'columnSearches' => $hasColumnSearches()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getEmptyStateIcon()),'actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getEmptyStateActions()),'column-searches' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hasColumnSearches())]); ?>
                             <?php $__env->slot('heading', null, []); ?> 
                                <?php echo e($getEmptyStateHeading()); ?>

                             <?php $__env->endSlot(); ?>

                             <?php $__env->slot('description', null, []); ?> 
                                <?php echo e($getEmptyStateDescription()); ?>

                             <?php $__env->endSlot(); ?>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalaf605259d054ed4913ba4ce9aa727e24)): ?>
<?php $attributes = $__attributesOriginalaf605259d054ed4913ba4ce9aa727e24; ?>
<?php unset($__attributesOriginalaf605259d054ed4913ba4ce9aa727e24); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalaf605259d054ed4913ba4ce9aa727e24)): ?>
<?php $component = $__componentOriginalaf605259d054ed4913ba4ce9aa727e24; ?>
<?php unset($__componentOriginalaf605259d054ed4913ba4ce9aa727e24); ?>
<?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php if($records instanceof \Illuminate\Contracts\Pagination\Paginator &&
             ((! $records instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) || $records->total())): ?>
            <div
                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'filament-tables-pagination-container border-t p-2',
                    'dark:border-gray-700' => config('tables.dark_mode'),
                ]); ?>"
            >
                <?php if (isset($component)) { $__componentOriginale04db629476c4856f818ec08ae7d1dbe = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale04db629476c4856f818ec08ae7d1dbe = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.pagination.index','data' => ['paginator' => $records,'recordsPerPageSelectOptions' => $getRecordsPerPageSelectOptions()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($records),'records-per-page-select-options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getRecordsPerPageSelectOptions())]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale04db629476c4856f818ec08ae7d1dbe)): ?>
<?php $attributes = $__attributesOriginale04db629476c4856f818ec08ae7d1dbe; ?>
<?php unset($__attributesOriginale04db629476c4856f818ec08ae7d1dbe); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale04db629476c4856f818ec08ae7d1dbe)): ?>
<?php $component = $__componentOriginale04db629476c4856f818ec08ae7d1dbe; ?>
<?php unset($__componentOriginale04db629476c4856f818ec08ae7d1dbe); ?>
<?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if($hasFiltersAfterContent): ?>
            <div class="px-2 pb-2">
                <?php if (isset($component)) { $__componentOriginal1c6b9867004d1a406262ace2c95ab783 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1c6b9867004d1a406262ace2c95ab783 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.hr','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::hr'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1c6b9867004d1a406262ace2c95ab783)): ?>
<?php $attributes = $__attributesOriginal1c6b9867004d1a406262ace2c95ab783; ?>
<?php unset($__attributesOriginal1c6b9867004d1a406262ace2c95ab783); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1c6b9867004d1a406262ace2c95ab783)): ?>
<?php $component = $__componentOriginal1c6b9867004d1a406262ace2c95ab783; ?>
<?php unset($__componentOriginal1c6b9867004d1a406262ace2c95ab783); ?>
<?php endif; ?>

                <div class="mt-2 p-4">
                    <?php if (isset($component)) { $__componentOriginal3b3fa8ec7d71109f7bdf00def1db315e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3b3fa8ec7d71109f7bdf00def1db315e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.filters.index','data' => ['form' => $getFiltersForm()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::filters'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['form' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getFiltersForm())]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3b3fa8ec7d71109f7bdf00def1db315e)): ?>
<?php $attributes = $__attributesOriginal3b3fa8ec7d71109f7bdf00def1db315e; ?>
<?php unset($__attributesOriginal3b3fa8ec7d71109f7bdf00def1db315e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3b3fa8ec7d71109f7bdf00def1db315e)): ?>
<?php $component = $__componentOriginal3b3fa8ec7d71109f7bdf00def1db315e; ?>
<?php unset($__componentOriginal3b3fa8ec7d71109f7bdf00def1db315e); ?>
<?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldbe408be6b5fd2d95479478edae3b0a1)): ?>
<?php $attributes = $__attributesOriginaldbe408be6b5fd2d95479478edae3b0a1; ?>
<?php unset($__attributesOriginaldbe408be6b5fd2d95479478edae3b0a1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldbe408be6b5fd2d95479478edae3b0a1)): ?>
<?php $component = $__componentOriginaldbe408be6b5fd2d95479478edae3b0a1; ?>
<?php unset($__componentOriginaldbe408be6b5fd2d95479478edae3b0a1); ?>
<?php endif; ?>

    <form wire:submit.prevent="callMountedTableAction">
        <?php
            $action = $getMountedAction();
        ?>

        <?php if (isset($component)) { $__componentOriginal80bcb2ba5ecc9d2854343e3b086e6055 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal80bcb2ba5ecc9d2854343e3b086e6055 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.modal.index','data' => ['id' => $this->id . '-table-action','wire:key' => $action ? $this->id . '.table.actions.' . $action->getName() . '.modal' : null,'visible' => filled($action),'width' => $action?->getModalWidth(),'slideOver' => $action?->isModalSlideOver(),'closeByClickingAway' => $action?->isModalClosedByClickingAway(),'displayClasses' => 'block','xInit' => 'livewire = $wire.__instance','xOn:modalClosed.stop' => '
                if (\'mountedTableAction\' in livewire?.serverMemo.data) {
                    livewire.set(\'mountedTableAction\', null)
                }

                if (\'mountedTableActionRecord\' in livewire?.serverMemo.data) {
                    livewire.set(\'mountedTableActionRecord\', null)
                }
            ']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->id . '-table-action'),'wire:key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action ? $this->id . '.table.actions.' . $action->getName() . '.modal' : null),'visible' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(filled($action)),'width' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action?->getModalWidth()),'slide-over' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action?->isModalSlideOver()),'close-by-clicking-away' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action?->isModalClosedByClickingAway()),'display-classes' => 'block','x-init' => 'livewire = $wire.__instance','x-on:modal-closed.stop' => '
                if (\'mountedTableAction\' in livewire?.serverMemo.data) {
                    livewire.set(\'mountedTableAction\', null)
                }

                if (\'mountedTableActionRecord\' in livewire?.serverMemo.data) {
                    livewire.set(\'mountedTableActionRecord\', null)
                }
            ']); ?>
            <?php if($action): ?>
                <?php if($action->isModalCentered()): ?>
                    <?php if($heading = $action->getModalHeading()): ?>
                         <?php $__env->slot('heading', null, []); ?> 
                            <?php echo e($heading); ?>

                         <?php $__env->endSlot(); ?>
                    <?php endif; ?>

                    <?php if($subheading = $action->getModalSubheading()): ?>
                         <?php $__env->slot('subheading', null, []); ?> 
                            <?php echo e($subheading); ?>

                         <?php $__env->endSlot(); ?>
                    <?php endif; ?>
                <?php else: ?>
                     <?php $__env->slot('header', null, []); ?> 
                        <?php if($heading = $action->getModalHeading()): ?>
                            <?php if (isset($component)) { $__componentOriginalfec2efb2a3a6d7053c08364d8a2bccd4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfec2efb2a3a6d7053c08364d8a2bccd4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.modal.heading','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::modal.heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                                <?php echo e($heading); ?>

                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfec2efb2a3a6d7053c08364d8a2bccd4)): ?>
<?php $attributes = $__attributesOriginalfec2efb2a3a6d7053c08364d8a2bccd4; ?>
<?php unset($__attributesOriginalfec2efb2a3a6d7053c08364d8a2bccd4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfec2efb2a3a6d7053c08364d8a2bccd4)): ?>
<?php $component = $__componentOriginalfec2efb2a3a6d7053c08364d8a2bccd4; ?>
<?php unset($__componentOriginalfec2efb2a3a6d7053c08364d8a2bccd4); ?>
<?php endif; ?>
                        <?php endif; ?>

                        <?php if($subheading = $action->getModalSubheading()): ?>
                            <?php if (isset($component)) { $__componentOriginal8fdb4fc6d2bb857caf7ef0c59466a270 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8fdb4fc6d2bb857caf7ef0c59466a270 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.modal.subheading','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::modal.subheading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                                <?php echo e($subheading); ?>

                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8fdb4fc6d2bb857caf7ef0c59466a270)): ?>
<?php $attributes = $__attributesOriginal8fdb4fc6d2bb857caf7ef0c59466a270; ?>
<?php unset($__attributesOriginal8fdb4fc6d2bb857caf7ef0c59466a270); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8fdb4fc6d2bb857caf7ef0c59466a270)): ?>
<?php $component = $__componentOriginal8fdb4fc6d2bb857caf7ef0c59466a270; ?>
<?php unset($__componentOriginal8fdb4fc6d2bb857caf7ef0c59466a270); ?>
<?php endif; ?>
                        <?php endif; ?>
                     <?php $__env->endSlot(); ?>
                <?php endif; ?>

                <?php echo e($action->getModalContent()); ?>


                <?php if($action->hasFormSchema()): ?>
                    <?php echo e($getMountedActionForm()); ?>

                <?php endif; ?>

                <?php echo e($action->getModalFooter()); ?>


                <?php if(count($action->getModalActions())): ?>
                     <?php $__env->slot('footer', null, []); ?> 
                        <?php if (isset($component)) { $__componentOriginal5a5413fc570472af81d8170aa3892507 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5a5413fc570472af81d8170aa3892507 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.modal.actions','data' => ['fullWidth' => $action->isModalCentered()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::modal.actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['full-width' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action->isModalCentered())]); ?>
                            <?php $__currentLoopData = $action->getModalActions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modalAction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php echo e($modalAction); ?>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5a5413fc570472af81d8170aa3892507)): ?>
<?php $attributes = $__attributesOriginal5a5413fc570472af81d8170aa3892507; ?>
<?php unset($__attributesOriginal5a5413fc570472af81d8170aa3892507); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5a5413fc570472af81d8170aa3892507)): ?>
<?php $component = $__componentOriginal5a5413fc570472af81d8170aa3892507; ?>
<?php unset($__componentOriginal5a5413fc570472af81d8170aa3892507); ?>
<?php endif; ?>
                     <?php $__env->endSlot(); ?>
                <?php endif; ?>
            <?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal80bcb2ba5ecc9d2854343e3b086e6055)): ?>
<?php $attributes = $__attributesOriginal80bcb2ba5ecc9d2854343e3b086e6055; ?>
<?php unset($__attributesOriginal80bcb2ba5ecc9d2854343e3b086e6055); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal80bcb2ba5ecc9d2854343e3b086e6055)): ?>
<?php $component = $__componentOriginal80bcb2ba5ecc9d2854343e3b086e6055; ?>
<?php unset($__componentOriginal80bcb2ba5ecc9d2854343e3b086e6055); ?>
<?php endif; ?>
    </form>

    <form wire:submit.prevent="callMountedTableBulkAction">
        <?php
            $action = $getMountedBulkAction();
        ?>

        <?php if (isset($component)) { $__componentOriginal80bcb2ba5ecc9d2854343e3b086e6055 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal80bcb2ba5ecc9d2854343e3b086e6055 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.modal.index','data' => ['id' => $this->id . '-table-bulk-action','wire:key' => $action ? $this->id . '.table.bulk-actions.' . $action->getName() . '.modal' : null,'visible' => filled($action),'width' => $action?->getModalWidth(),'slideOver' => $action?->isModalSlideOver(),'closeByClickingAway' => $action?->isModalClosedByClickingAway(),'displayClasses' => 'block','xInit' => 'livewire = $wire.__instance','xOn:modalClosed.stop' => 'if (\'mountedTableBulkAction\' in livewire?.serverMemo.data) livewire.set(\'mountedTableBulkAction\', null)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->id . '-table-bulk-action'),'wire:key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action ? $this->id . '.table.bulk-actions.' . $action->getName() . '.modal' : null),'visible' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(filled($action)),'width' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action?->getModalWidth()),'slide-over' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action?->isModalSlideOver()),'close-by-clicking-away' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action?->isModalClosedByClickingAway()),'display-classes' => 'block','x-init' => 'livewire = $wire.__instance','x-on:modal-closed.stop' => 'if (\'mountedTableBulkAction\' in livewire?.serverMemo.data) livewire.set(\'mountedTableBulkAction\', null)']); ?>
            <?php if($action): ?>
                <?php if($action->isModalCentered()): ?>
                    <?php if($heading = $action->getModalHeading()): ?>
                         <?php $__env->slot('heading', null, []); ?> 
                            <?php echo e($heading); ?>

                         <?php $__env->endSlot(); ?>
                    <?php endif; ?>

                    <?php if($subheading = $action->getModalSubheading()): ?>
                         <?php $__env->slot('subheading', null, []); ?> 
                            <?php echo e($subheading); ?>

                         <?php $__env->endSlot(); ?>
                    <?php endif; ?>
                <?php else: ?>
                     <?php $__env->slot('header', null, []); ?> 
                        <?php if($heading = $action->getModalHeading()): ?>
                            <?php if (isset($component)) { $__componentOriginalfec2efb2a3a6d7053c08364d8a2bccd4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfec2efb2a3a6d7053c08364d8a2bccd4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.modal.heading','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::modal.heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                                <?php echo e($heading); ?>

                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfec2efb2a3a6d7053c08364d8a2bccd4)): ?>
<?php $attributes = $__attributesOriginalfec2efb2a3a6d7053c08364d8a2bccd4; ?>
<?php unset($__attributesOriginalfec2efb2a3a6d7053c08364d8a2bccd4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfec2efb2a3a6d7053c08364d8a2bccd4)): ?>
<?php $component = $__componentOriginalfec2efb2a3a6d7053c08364d8a2bccd4; ?>
<?php unset($__componentOriginalfec2efb2a3a6d7053c08364d8a2bccd4); ?>
<?php endif; ?>
                        <?php endif; ?>

                        <?php if($subheading = $action->getModalSubheading()): ?>
                            <?php if (isset($component)) { $__componentOriginal8fdb4fc6d2bb857caf7ef0c59466a270 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8fdb4fc6d2bb857caf7ef0c59466a270 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.modal.subheading','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::modal.subheading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                                <?php echo e($subheading); ?>

                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8fdb4fc6d2bb857caf7ef0c59466a270)): ?>
<?php $attributes = $__attributesOriginal8fdb4fc6d2bb857caf7ef0c59466a270; ?>
<?php unset($__attributesOriginal8fdb4fc6d2bb857caf7ef0c59466a270); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8fdb4fc6d2bb857caf7ef0c59466a270)): ?>
<?php $component = $__componentOriginal8fdb4fc6d2bb857caf7ef0c59466a270; ?>
<?php unset($__componentOriginal8fdb4fc6d2bb857caf7ef0c59466a270); ?>
<?php endif; ?>
                        <?php endif; ?>
                     <?php $__env->endSlot(); ?>
                <?php endif; ?>

                <?php echo e($action->getModalContent()); ?>


                <?php if($action->hasFormSchema()): ?>
                    <?php echo e($getMountedBulkActionForm()); ?>

                <?php endif; ?>

                <?php echo e($action->getModalFooter()); ?>


                <?php if(count($action->getModalActions())): ?>
                     <?php $__env->slot('footer', null, []); ?> 
                        <?php if (isset($component)) { $__componentOriginal5a5413fc570472af81d8170aa3892507 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5a5413fc570472af81d8170aa3892507 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.modal.actions','data' => ['fullWidth' => $action->isModalCentered()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::modal.actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['full-width' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action->isModalCentered())]); ?>
                            <?php $__currentLoopData = $action->getModalActions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modalAction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php echo e($modalAction); ?>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5a5413fc570472af81d8170aa3892507)): ?>
<?php $attributes = $__attributesOriginal5a5413fc570472af81d8170aa3892507; ?>
<?php unset($__attributesOriginal5a5413fc570472af81d8170aa3892507); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5a5413fc570472af81d8170aa3892507)): ?>
<?php $component = $__componentOriginal5a5413fc570472af81d8170aa3892507; ?>
<?php unset($__componentOriginal5a5413fc570472af81d8170aa3892507); ?>
<?php endif; ?>
                     <?php $__env->endSlot(); ?>
                <?php endif; ?>
            <?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal80bcb2ba5ecc9d2854343e3b086e6055)): ?>
<?php $attributes = $__attributesOriginal80bcb2ba5ecc9d2854343e3b086e6055; ?>
<?php unset($__attributesOriginal80bcb2ba5ecc9d2854343e3b086e6055); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal80bcb2ba5ecc9d2854343e3b086e6055)): ?>
<?php $component = $__componentOriginal80bcb2ba5ecc9d2854343e3b086e6055; ?>
<?php unset($__componentOriginal80bcb2ba5ecc9d2854343e3b086e6055); ?>
<?php endif; ?>
    </form>

    <?php if(! $this instanceof \Filament\Tables\Contracts\RendersFormComponentActionModal): ?>
        <?php echo e($this->modal); ?>

    <?php endif; ?>
</div>
<?php /**PATH C:\pbl\vendor\filament\tables\resources\views\index.blade.php ENDPATH**/ ?>