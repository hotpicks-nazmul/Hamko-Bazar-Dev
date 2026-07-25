@extends('admin.layouts.app')

@section('title', 'Create Role Template')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Create Role Template</h4>
    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Roles
    </a>
</div>

<form action="{{ route('admin.roles.store') }}" method="POST" id="roleForm">
    @csrf
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Role Name <small class="text-muted">(slug format, e.g. order-manager)</small></label>
                <input type="text" name="name" class="form-control" placeholder="order-manager" required pattern="[a-z-]+">
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-shield-check me-2"></i>Permissions</h6>
            <div>
                <button type="button" class="btn btn-sm btn-outline-success" id="selectAllPermissions">
                    <i class="bi bi-check-all me-1"></i> Select All
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger ms-1" id="clearAllPermissions">
                    <i class="bi bi-x-circle me-1"></i> Clear All
                </button>
            </div>
        </div>
        <div class="card-body p-3">
            <div class="role-perm-modules">
                @foreach($permissionModules as $moduleKey => $module)
                    @php
                        $guardedSections = ['customers.view-financial', 'orders.view-customer', 'orders.view-pricing', 'products.view-cost', 'refund.view-customer', 'dashboard.view-revenue', 'dashboard.view-sales', 'analytics.view-revenue', 'analytics.view-sales'];
                        $modulePrefix = $module['key'];
                        $submenus = \App\Helpers\PermissionHelper::submenus()[$moduleKey] ?? [];
                        $pageSectionPerms = \App\Helpers\PermissionHelper::pageSectionPermissions();
                        $sectionActions = \App\Helpers\PermissionHelper::sectionActions();
                        $moduleSupportedActions = $moduleActions[$moduleKey] ?? [];
                        $nonSectionActions = array_values(array_filter($moduleSupportedActions, fn($a) => !in_array($a, $sectionActions)));
                        $hasAny = !empty($nonSectionActions) || !empty($submenus);
                    @endphp
                    @if($hasAny)
                    <div class="card border-0 rounded-3 mb-2 role-module-group" data-module="{{ $moduleKey }}" style="background: #fafbfc;">
                        <div class="role-module-header d-flex align-items-center gap-2 px-3 py-2" style="cursor:pointer;">
                            <span class="role-collapse-icon text-muted" style="font-size: 0.86rem; width: 16px; flex-shrink: 0;">
                                <i class="bi bi-chevron-right"></i>
                            </span>
                            <div style="flex: 0 0 130px; flex-shrink: 0; display: flex; align-items: center; gap: 6px;">
                                <i class="{{ $module['icon'] }}" style="font-size: 1rem;"></i>
                                <strong style="font-size: 0.9rem;">{{ $module['label'] }}</strong>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                @if(!empty($nonSectionActions))
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($nonSectionActions as $action)
                                        @php
                                            $permName = $modulePrefix . '.' . $action;
                                            $checked = old('permissions') ? in_array($permName, old('permissions', [])) : false;
                                        @endphp
                                        <span class="badge rounded-pill role-perm-pill {{ $checked ? 'pill-green' : 'pill-gray' }}"
                                              data-perm="{{ $permName }}"
                                              data-state="{{ $checked ? '1' : '0' }}"
                                              style="cursor:pointer; padding: 0.2em 0.65em; font-size: 0.86em; transition: all 0.15s;">
                                            {{ $action }}
                                        </span>
                                        <input type="checkbox" name="permissions[]" value="{{ $permName }}"
                                               class="d-none role-perm-checkbox"
                                               {{ $checked ? 'checked' : '' }}>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            <span class="text-muted" style="font-size: 0.76rem; flex-shrink: 0;">{{ count($submenus) }}p</span>
                        </div>
                        @if(!empty($submenus))
                        <div class="role-submenu-content" style="display: none;">
                            <div style="border-top: 1px solid #e9ecef; margin: 0 12px;"></div>
                            @php
                                $allPageComponents = \App\Helpers\PermissionHelper::pageComponents();
                            @endphp
                            @foreach($submenus as $routeName => $label)
                                @php
                                    $disabledKey = 'submenu_disabled:' . $routeName;
                                    $oldVis = old('submenu_visibility');
                                    $isSubmenuVisible = $oldVis ? !in_array($disabledKey, $oldVis) : true;
                                    $submenuState = $isSubmenuVisible ? '1' : '0';
                                    $submenuValue = $isSubmenuVisible ? 'submenu:' . $routeName : 'submenu_disabled:' . $routeName;
                                    $routeSections = $pageSectionPerms[$routeName] ?? [];
                                    $page = $allPageComponents[$routeName] ?? [];
                                    $pageItems = $page['items'] ?? [];
                                    $pageGroups = $page['groups'] ?? [];
                                @endphp
                                {{-- L0: Submenu row (page name + visibility toggle) --}}
                                <div style="padding: 5px 12px 5px 44px;">
                                    <span class="badge rounded-pill role-submenu-pill d-inline-flex align-items-center gap-1 {{ $isSubmenuVisible ? 'pill-blue' : 'pill-gray' }}"
                                          data-submenu="{{ $routeName }}"
                                          data-state="{{ $submenuState }}"
                                          style="cursor:pointer; padding: 0.25em 0.6em; font-size: 0.78rem; white-space: nowrap;">
                                        <i class="bi {{ $isSubmenuVisible ? 'bi-eye' : 'bi-eye-slash' }}\"></i> {{ $label }}
                                    </span>
                                    <input type="hidden" name="submenu_visibility[]" value="{{ $submenuValue }}" class="role-submenu-input">

                                    {{-- Section pills (purple) inline --}}
                                    @if(!empty($routeSections))
                                        @foreach($routeSections as $action)
                                            @php
                                                $sPermName = $modulePrefix . '.' . $action;
                                                if (!in_array($sPermName, $guardedSections)) continue;
                                                $sChecked = old('permissions') ? in_array($sPermName, old('permissions', [])) : false;
                                            @endphp
                                            <span class="badge rounded-pill role-perm-pill {{ $sChecked ? 'pill-purple' : 'pill-gray' }}"
                                                  data-perm="{{ $sPermName }}"
                                                  data-state="{{ $sChecked ? '1' : '0' }}"
                                                  style="cursor:pointer; padding: 0.2em 0.65em; font-size: 0.84em; vertical-align:middle; transition: all 0.15s;">
                                                {{ str_replace('view-', '', $action) }}
                                            </span>
                                            <input type="checkbox" name="permissions[]" value="{{ $sPermName }}"
                                                   class="d-none role-perm-checkbox"
                                                   {{ $sChecked ? 'checked' : '' }}>
                                        @endforeach
                                    @endif

                                    {{-- Direct page items (orange) inline --}}
                                    @if(!empty($pageItems))
                                        @foreach($pageItems as $itemLabel => $permName)
                                            @php
                                                $checked = old('permissions') ? in_array($permName, old('permissions', [])) : false;
                                            @endphp
                                            <span class="badge rounded-pill role-perm-pill role-page-action-pill {{ $checked ? 'pill-purple' : 'pill-gray' }}"
                                                  data-perm="{{ $permName }}"
                                                  data-state="{{ $checked ? '1' : '0' }}"
                                                  style="cursor:pointer; padding: 0.2em 0.65em; font-size: 0.78rem; vertical-align:middle; transition: all 0.15s;">
                                                {{ $itemLabel }}
                                            </span>
                                            <input type="checkbox" name="permissions[]" value="{{ $permName }}"
                                                   class="d-none role-perm-checkbox"
                                                   {{ $checked ? 'checked' : '' }}>
                                        @endforeach
                                    @endif
                                </div>

                                {{-- L1: Groups --}}
                                @foreach($pageGroups as $groupLabel => $group)
                                    @php
                                        $gItems = $group['items'] ?? [];
                                        $gChildren = $group['children'] ?? [];
                                        // Skip groups where all items are section permissions (already covered by purple pills)
                                        $allSection = !empty($gItems) && collect($gItems)->every(function($perm, $label) use ($sectionActions) {
                                            return collect($sectionActions)->contains(fn($a) => str_ends_with($perm, '.' . $a));
                                        });
                                    @endphp
                                    @if(!$allSection)
                                    {{-- L1: Group header --}}
                                    <div style="padding: 4px 12px 2px 60px;">
                                        <small class="text-muted" style="font-size: 0.76rem;"><i class="bi bi-layout-sidebar me-1"></i>{{ $groupLabel }}</small>
                                    </div>
                                    {{-- L2: Group items --}}
                                    @if(!empty($gItems))
                                    <div style="padding: 2px 12px 4px 76px;">
                                        @foreach($gItems as $itemLabel => $permName)
                                            @php
                                                $checked = old('permissions') ? in_array($permName, old('permissions', [])) : false;
                                                $hasChild = isset($gChildren[$itemLabel]);
                                            @endphp
                                            <span class="badge rounded-pill role-perm-pill role-page-action-pill {{ $hasChild ? 'has-child-page' : '' }} {{ $checked ? 'pill-purple' : 'pill-gray' }}"
                                                  data-perm="{{ $permName }}"
                                                  data-state="{{ $checked ? '1' : '0' }}"
                                                  style="cursor:pointer; padding: 0.2em 0.65em; font-size: 0.78rem; transition: all 0.15s;">
                                                {{ $itemLabel }}
                                            </span>
                                            <input type="checkbox" name="permissions[]" value="{{ $permName }}"
                                                   class="d-none role-perm-checkbox"
                                                   {{ $checked ? 'checked' : '' }}>
                                        @endforeach

                                        {{-- L3: Child pages attached to group items --}}
                                        @foreach($gChildren as $childItemLabel => $childInfo)
                                            @php
                                                $childRoute = $childInfo['route'] ?? '';
                                                $childLabel = $childInfo['label'] ?? '';
                                                $childPage = $childRoute ? ($allPageComponents[$childRoute] ?? []) : [];
                                                $childItems = $childPage['items'] ?? [];
                                            @endphp
                                            @if(!empty($childItems))
                                            <div style="margin-top: 4px; padding-left: 16px;">
                                                {{-- Child page header --}}
                                                <div style="padding: 2px 0;">
                                                    <span class="badge rounded-pill d-inline-flex align-items-center gap-1"
                                                          style="padding: 0.2em 0.6em; font-size: 0.74rem; white-space: nowrap; background: #e8f4fd; color: #0d6efd; border: 1px solid #b8daff;">
                                                        <i class="bi bi-file-text"></i> {{ $childLabel }}
                                                    </span>
                                                </div>
                                                {{-- Child page items --}}
                                                <div style="padding: 2px 0 2px 16px;">
                                                    @foreach($childItems as $childItemLabel => $childPermName)
                                                        @php
                                                            $checked = old('permissions') ? in_array($childPermName, old('permissions', [])) : false;
                                                        @endphp
                                                        <span class="badge rounded-pill role-perm-pill role-page-action-pill {{ $checked ? 'pill-purple' : 'pill-gray' }}"
                                                              data-perm="{{ $childPermName }}"
                                                              data-state="{{ $checked ? '1' : '0' }}"
                                                              style="cursor:pointer; padding: 0.2em 0.65em; font-size: 0.76rem; transition: all 0.15s;">
                                                            {{ $childItemLabel }}
                                                        </span>
                                                        <input type="checkbox" name="permissions[]" value="{{ $childPermName }}"
                                                               class="d-none role-perm-checkbox"
                                                               {{ $checked ? 'checked' : '' }}>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @endif
                                        @endforeach
                                    </div>
                                    @endif
                                @endif
                                @endforeach
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <div class="floating-save-container">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> Create Role
        </button>
    </div>
</form>
@endsection

@push('styles')
<style>
    .bi::before, [class*="bi bi-"]::before {
        display: inline-block !important;
        visibility: visible !important;
        opacity: 1 !important;
        font-family: 'bootstrap-icons' !important;
    }
    .role-perm-pill:hover {
        transform: scale(1.08);
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }
    /* Permission pill colors - edit here to change globally */
    .pill-green { background: #198754; color: #fff; }
    .pill-purple { background: #6f42c1; color: #fff; }
    .pill-blue { background: #0d6efd; color: #fff; }
    .pill-orange { background: #e86c00; color: #fff; }
    .pill-gray { background: #e9ecef; color: #6c757d; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ============ MODULE COLLAPSE/EXPAND ============
    document.querySelectorAll('.role-module-header').forEach(function(header) {
        header.addEventListener('click', function(e) {
            if (e.target.closest('.role-perm-pill, .role-submenu-pill')) return;
            const group = this.closest('.role-module-group');
            const content = group.querySelector('.role-submenu-content');
            const icon = this.querySelector('.role-collapse-icon i');
            if (!content) return;
            const expanded = content.style.display !== 'none';
            content.style.display = expanded ? 'none' : 'block';
            icon.className = expanded ? 'bi bi-chevron-right' : 'bi bi-chevron-down';
        });
    });

    // ============ TOGGLE PERMISSION PILLS ============
    document.querySelectorAll('.role-perm-pill').forEach(function(pill) {
        pill.addEventListener('click', function(e) {
            e.stopPropagation();
            const newState = this.dataset.state === '1' ? '0' : '1';
            this.dataset.state = newState;
            if (newState === '1') {
                // Section pills (purple) vs main action pills (green)
                const isSection = this.dataset.perm && this.dataset.perm.match(/\.(view-customer|view-pricing|view-cost|view-financial|view-revenue|view-sales|view-contact|view-address|view-orders|view-payments|view-activity|view-notes|view-pricing-detail|view-discount|view-cost-price|view-wholesale-price|view-profit)$/);
                const isPageAction = this.classList.contains('role-page-action-pill');
                this.classList.remove('pill-gray');
                this.classList.add(isSection ? 'pill-purple' : (isPageAction ? 'pill-purple' : 'pill-green'));
            } else {
                this.classList.remove('pill-green', 'pill-purple');
                this.classList.add('pill-gray');
            }
            // Sync hidden checkbox
            const form = this.closest('form');
            const cb = form.querySelector('input[value="' + this.dataset.perm + '"]');
            if (cb) cb.checked = newState === '1';
        });
    });

    // ============ TOGGLE SUBMENU VISIBILITY ============
    document.querySelectorAll('.role-submenu-pill').forEach(function(pill) {
        pill.addEventListener('click', function(e) {
            e.stopPropagation();
            const newState = this.dataset.state === '1' ? '0' : '1';
            this.dataset.state = newState;
            const icon = this.querySelector('i');
            if (newState === '1') {
                this.classList.remove('pill-gray');
                this.classList.add('pill-blue');
                icon.className = 'bi bi-eye';
            } else {
                this.classList.remove('pill-blue');
                this.classList.add('pill-gray');
                icon.className = 'bi bi-eye-slash';
            }
            // Sync hidden input
            const form = this.closest('form');
            const submenu = this.dataset.submenu;
            const input = form.querySelector('.role-submenu-input[value="submenu:' + submenu + '"], .role-submenu-input[value="submenu_disabled:' + submenu + '"]');
            if (input) {
                input.value = newState === '1' ? 'submenu:' + submenu : 'submenu_disabled:' + submenu;
            }
        });
    });

    // ============ SELECT ALL ============
    document.getElementById('selectAllPermissions')?.addEventListener('click', function() {
        document.querySelectorAll('.role-perm-pill').forEach(function(pill) {
            pill.dataset.state = '1';
            const isSection = pill.dataset.perm && pill.dataset.perm.match(/\.(view-customer|view-pricing|view-cost|view-financial|view-revenue|view-sales|view-contact|view-address|view-orders|view-payments|view-activity|view-notes|view-pricing-detail|view-discount|view-cost-price|view-wholesale-price|view-profit)$/);
            const isPageAction = pill.classList.contains('role-page-action-pill');
            pill.classList.remove('pill-gray');
            pill.classList.add(isSection ? 'pill-purple' : (isPageAction ? 'pill-purple' : 'pill-green'));
            const form = pill.closest('form');
            const cb = form.querySelector('input[value="' + pill.dataset.perm + '"]');
            if (cb) cb.checked = true;
        });
    });

    // ============ CLEAR ALL ============
    document.getElementById('clearAllPermissions')?.addEventListener('click', function() {
        document.querySelectorAll('.role-perm-pill').forEach(function(pill) {
            pill.dataset.state = '0';
            pill.classList.remove('pill-green', 'pill-purple', 'pill-orange');
            pill.classList.add('pill-gray');
            const form = pill.closest('form');
            const cb = form.querySelector('input[value="' + pill.dataset.perm + '"]');
            if (cb) cb.checked = false;
        });
    });

});
</script>
@endpush
