{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($urls as [$loc, $modified, $priority])
    <url>
        <loc>{{ $loc }}</loc>
        <lastmod>{{ ($modified ?? now())->toAtomString() }}</lastmod>
        <priority>{{ $priority }}</priority>
    </url>
@endforeach
</urlset>
