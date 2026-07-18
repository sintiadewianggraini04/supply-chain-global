@extends('layouts.admin')

@section(
    'title',
    'Artikel Analisis - Admin'
)

@section('content')
    <div class="topbar">
        <div>
            <h2>Artikel Analisis</h2>

            <p>
                Kelola artikel analisis yang
                dibuat oleh administrator.
            </p>
        </div>

        <span class="badge text-bg-primary fs-6">
            {{ $articles->total() }} Articles
        </span>
    </div>

    <section class="dashboard-card mb-4">
        <h3 class="mb-3">
            {{ $editingArticle
                ? 'Edit Artikel'
                : 'Tambah Artikel'
            }}
        </h3>

        <form
            method="POST"
            action="{{
                $editingArticle
                    ? route(
                        'admin.articles.update',
                        $editingArticle
                    )
                    : route('admin.articles.store')
            }}"
        >
            @csrf

            @if ($editingArticle)
                @method('PATCH')
            @endif

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">
                        Judul
                    </label>

                    <input
                        type="text"
                        name="title"
                        class="form-control"
                        value="{{ old(
                            'title',
                            $editingArticle?->title
                        ) }}"
                        required
                    >
                </div>

                <div class="col-md-4">
                    <label class="form-label">
                        Kategori
                    </label>

                    <select
                        name="category"
                        class="form-select"
                        required
                    >
                        @foreach ([
                            'Logistics',
                            'Trade',
                            'Shipping',
                            'Economy',
                            'Geopolitics',
                            'Risk Analysis',
                        ] as $category)
                            <option
                                value="{{ $category }}"
                                @selected(
                                    old(
                                        'category',
                                        $editingArticle?->category
                                    ) === $category
                                )
                            >
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">
                        Ringkasan
                    </label>

                    <textarea
                        name="summary"
                        class="form-control"
                        rows="3"
                    >{{ old(
                        'summary',
                        $editingArticle?->summary
                    ) }}</textarea>
                </div>

                <div class="col-12">
                    <label class="form-label">
                        Isi Artikel
                    </label>

                    <textarea
                        name="content"
                        class="form-control"
                        rows="10"
                        required
                    >{{ old(
                        'content',
                        $editingArticle?->content
                    ) }}</textarea>
                </div>

                <div class="col-md-4">
                    <label class="form-label">
                        Status
                    </label>

                    <select
                        name="status"
                        class="form-select"
                        required
                    >
                        <option
                            value="draft"
                            @selected(
                                old(
                                    'status',
                                    $editingArticle?->status
                                        ?? 'draft'
                                ) === 'draft'
                            )
                        >
                            Draft
                        </option>

                        <option
                            value="published"
                            @selected(
                                old(
                                    'status',
                                    $editingArticle?->status
                                ) === 'published'
                            )
                        >
                            Published
                        </option>
                    </select>
                </div>

                <div class="col-12">
                    <button class="btn btn-primary">
                        {{ $editingArticle
                            ? 'Simpan Perubahan'
                            : 'Tambah Artikel'
                        }}
                    </button>

                    @if ($editingArticle)
                        <a
                            href="{{ route('admin.articles.index') }}"
                            class="btn btn-outline-secondary"
                        >
                            Batal
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </section>

    <section class="dashboard-card">
        <form
            method="GET"
            class="row g-2 mb-4"
        >
            <div class="col-md-10">
                <input
                    type="search"
                    name="search"
                    class="form-control"
                    value="{{ $search }}"
                    placeholder="Cari judul atau kategori"
                >
            </div>

            <div class="col-md-2">
                <button
                    class="btn btn-outline-primary w-100"
                >
                    Cari
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Artikel</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Author</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($articles as $article)
                        <tr>
                            <td>
                                <strong>
                                    {{ $article->title }}
                                </strong>

                                <br>

                                <small class="text-secondary">
                                    {{ $article->slug }}
                                </small>
                            </td>

                            <td>
                                {{ $article->category }}
                            </td>

                            <td>
                                <span
                                    class="badge {{
                                        $article->status
                                            === 'published'
                                                ? 'text-bg-success'
                                                : 'text-bg-warning'
                                    }}"
                                >
                                    {{ ucfirst(
                                        $article->status
                                    ) }}
                                </span>
                            </td>

                            <td>
                                {{ $article->author?->name
                                    ?? '-'
                                }}
                            </td>

                            <td>
                                <div class="d-flex gap-2">
                                    <a
                                        href="{{ route(
                                            'admin.articles.index',
                                            [
                                                'edit' =>
                                                    $article->id,
                                                'search' =>
                                                    $search,
                                            ]
                                        ) }}"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'admin.articles.destroy',
                                            $article
                                        ) }}"
                                        onsubmit="
                                            return confirm(
                                                'Hapus artikel ini?'
                                            );
                                        "
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            class="btn btn-sm btn-danger"
                                        >
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="5"
                                class="text-center text-secondary"
                            >
                                Artikel tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $articles->links() }}
    </section>
@endsection