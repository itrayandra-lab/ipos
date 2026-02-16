<div class="hidden lg:block sticky top-24 max-h-[80vh] overflow-y-auto">
    <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center">
        <i class="fa-solid fa-tags mr-2 text-indigo-600"></i>
        Merk
    </h3>
    <ul role="list" class="space-y-2 text-sm font-medium text-gray-700 p-2">
        <li>
            <a href="{{ route('home') }}"
                class="block rounded-md px-3 py-2 transition-colors {{ !request('category') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'hover:bg-gray-100 hover:text-gray-900' }}">
                Semua Produk
            </a>
        </li>
        @foreach ($merek as $m)
            <li>
                <a href="{{ route('home', ['merek' => $m->slug]) }}"
                    class="block rounded-md px-3 py-2 transition-colors {{ request('merek') == $m->slug ? 'bg-indigo-50 text-indigo-700 font-bold' : 'hover:bg-gray-100 hover:text-gray-900' }}">
                    {{ $m->name }}
                </a>
            </li>
        @endforeach
    </ul>

    <div class="mt-6 border-t pt-4">
        <a href="{{ route('login') }}"
            class="group flex w-full items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700">
            <i class="fa-solid fa-right-to-bracket mr-2 transition-transform group-hover:translate-x-1"></i>
            @if (!auth()->user())
                Login / Masuk
            @else
                Dashboard
            @endif
        </a>
    </div>
</div>

