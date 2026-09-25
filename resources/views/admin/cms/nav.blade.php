<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}" href="{{ route('admin.pages.index') }}">Pages</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}" href="{{ route('admin.faqs.index') }}">FAQs</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.testimonials.*') ? 'active' : '' }}" href="{{ route('admin.testimonials.index') }}">Customer Reviews</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('admin.categories.index') }}">Categories</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('admin.settings.edit') }}#homepage">Homepage stats</a></li>
</ul>
